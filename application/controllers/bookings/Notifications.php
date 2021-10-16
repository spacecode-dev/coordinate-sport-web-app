<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notifications extends MY_Controller {

	private $bookingID;
	private $booking_info;
	private $return_to;
	private $errors = array();
	private $contacts;

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * send event notifications
	 * @param  int $bookingID
	 * @return void
	 */
	public function index($bookingID = NULL)
	{

		$booking_info = new stdClass;

		// check
		if ($bookingID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($bookingID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$booking_info = $row;
			// save in class for other methods
			$this->bookingID = $bookingID;
			$this->booking_info = $booking_info;
		}

		// if not project or valid register type, redirect to customers tab
		if ($booking_info->project != 1 || in_array($booking_info->register_type, array('numbers', 'names', 'bikeability', 'shapeup'))) {
			redirect('bookings/confirmation/' . $bookingID);
			exit();
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Messaging';
		$submit_to = 'bookings/messaging/' . $bookingID;
		$this->return_to = $submit_to;
		$buttons = NULL;
		$icon = 'envelope-alt';
		$tab = 'messaging';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$section = 'bookings';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// get blocks
		$where = array(
			'bookings_blocks.bookingID' => $bookingID,
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('bookings_blocks.blockID, bookings_blocks.name')
			->from('bookings_blocks')
			->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')
			->where($where)
			->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate asc, bookings_blocks.name asc')
			->get();

		$block_list = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$block_list[$row->blockID] = $row->name;
			}
		}

		// get contacts for booking
		$where = array(
			'bookings_cart_sessions.bookingID' => $bookingID,
			'family_contacts.accountID' => $this->auth->user->accountID,
			'bookings_cart.type' => 'booking'
		);
		$blocks = $this->input->post('blocks');
		if (!is_array($blocks)) {
			$blocks = array();
		}
		if (count($blocks) > 0) {
			if(!in_array("all", $blocks)){
				$this->db->where_in('blockID', $blocks);
			}
		}

		$this->contacts = $this->db->select('family_contacts.*')
		->from('bookings_cart_sessions')
		->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
		->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'inner')
		->where($where)
		->group_by('family_contacts.contactID')
		->get();

		// if posted
		if ($this->input->post()) {
			switch ($this->input->post('type')) {
				case 'sms':
					if ($this->auth->has_features('sms')) {
						$this->sms();
					}
					break;
				case 'email':
					$this->email();
					break;
			}
		}
		// if an error, keep blocks in list that are not already stored
		if (count($errors) > 0 && isset($_POST)) {
			$blocks = $this->input->post('blocks');
			if (is_array($blocks)) {
				$block_list = array_merge($block_list, $blocks);
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		}
		if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}
		if ($this->session->flashdata('error')) {
			$this->errors[] = $this->session->flashdata('error');
		}

		// get event attachments
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$attachments = $this->db->from('bookings_attachments')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $this->return_to,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'block_list' => $block_list,
			'attachments' => $attachments,
			'type' => $booking_info->type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $this->errors,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/notifications', $data);
	}

	/**
	 * send event notifications
	 * @param  int $bookingID
	 * @return void
	 */
	public function history($bookingID = NULL){
		// check
		if ($bookingID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($bookingID)) {
			show_404();
		}

		// if so, check user exists
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings.*, orgs.name as org, orgs.type as org_type')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();
		foreach ($query->result() as $booking_info) break;

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// set defaults
		$tab = 'messaging';
		$current_page = 'participants';
		$page_base = 'bookings/history/' . $bookingID;
		$section = 'bookings';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
			$breadcrumb_levels['bookings/notifications/' . $bookingID] = 'Messaging';
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
			$breadcrumb_levels['bookings/notifications/' . $bookingID] = 'Messaging';
		}

		// set up search
		$search_where_org = '';
		$search_where_family = '';
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'contact_id' => NULL,
			'message' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_contact_id', 'To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_message', 'Message', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['contact_id'] = set_value('search_contact_id');
			$search_fields['message'] = set_value('search_message');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-family-notifications'))) {

			foreach ($this->session->userdata('search-family-notifications') as $key => $value) {
				$search_fields[$key] = $value;
			}
			$is_search = TRUE;
		}

		if (isset($is_search) && $is_search === TRUE) {

			$search_where_org = array();
			$search_where_family = array();
			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-family-notifications', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where_org[] = "`on`.`added` >= " . $this->db->escape($date_from);
					$search_where_family[] = "`fn`.`added` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where_org[] = "`on`.`added` <= " . $this->db->escape($date_to);
					$search_where_family[] = "`fn`.`added` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['contact_id'] != '') {
				$search_where_org[] = "`on`.`contactID` = " . $this->db->escape($search_fields['contact_id']);
				$search_where_family[] = "`fn`.`contactID` = " . $this->db->escape($search_fields['contact_id']);
			}

			if ($search_fields['message'] != '') {
				$search_where_org[] = "(`on`.`contentText` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%' OR `on`.`contentHTML` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%')";
				$search_where_family[] = "(`fn`.`contentText` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%' OR `fn`.`contentHTML` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%')";
			}

			if (count($search_where_org) > 0) {
				$search_where_org = ' AND (' . implode(' AND ', $search_where_org) . ')';
			}else{
				$search_where_org = '';
			}

			if (count($search_where_family) > 0) {
				$search_where_family = ' AND (' . implode(' AND ', $search_where_family) . ')';
			}else{
				$search_where_family='';
			}
		}

		// run query again, but limited
		$res = $this->db->query("select  `name`,`added`, `destination`, `type`, `contentText`, `subject`, `notificationID`  FROM
				(SELECT  `name`, `on`.`added`, `destination`, `type`, `contentText`, `subject`, `notificationID`
				FROM ".$this->db->dbprefix('orgs_notifications')." `on`
				LEFT JOIN ".$this->db->dbprefix('orgs_contacts')." oc ON `on`.`contactID` = `oc`.`contactID`
				WHERE `on`.`bookingID` =  '" . $bookingID . "'
				AND `on`.`accountID` =  '" . $this->auth->user->accountID . "' ".$search_where_org."
				ORDER BY `on`.added desc) tb1
				UNION ALL
				(SELECT  CONCAT(fc.`first_name`, ' ', fc.`last_name`) as `name`, `fn`.`added`, `email` as `destination`, `type`, `contentText`, `subject`, `notificationID`
				FROM ".$this->db->dbprefix('family_notifications')." `fn`
				LEFT JOIN ".$this->db->dbprefix('family_contacts')." `fc` ON `fn`.`contactID` = `fc`.`contactID`
				WHERE `fn`.`bookingID` =  '" . $bookingID . "'
				AND `fn`.`accountID` =  '" . $this->auth->user->accountID . "' ".$search_where_family."
				ORDER BY `fn`.added desc)");
		// workout pagination
		$total_items = $res->num_rows();
		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->query("select  `name`,`added`, `destination`, `type`, `contentText`, `subject`, `notificationID`, `origin`  FROM
				(SELECT  `name`, `on`.`added`, `destination`, `type`, `contentText`, `subject`, `notificationID`, 'org' as `origin`
				FROM ".$this->db->dbprefix('orgs_notifications')." `on`
				LEFT JOIN ".$this->db->dbprefix('orgs_contacts')." oc ON `on`.`contactID` = `oc`.`contactID`
				WHERE `on`.`bookingID` =  '" . $bookingID . "'
				AND `on`.`accountID` =  '" . $this->auth->user->accountID . "' ".$search_where_org."
				ORDER BY `on`.added desc) tb1
				UNION ALL
				(SELECT  CONCAT(fc.`first_name`, ' ', fc.`last_name`) as `name`, `fn`.`added`, `email` as `destination`, `type`, `contentText`, `subject`, `notificationID`, 'family' as `origin`
				FROM ".$this->db->dbprefix('family_notifications')." `fn`
				LEFT JOIN ".$this->db->dbprefix('family_contacts')." `fc` ON `fn`.`contactID` = `fc`.`contactID`
				WHERE `fn`.`bookingID` =  '" . $bookingID . "'
				AND `fn`.`accountID` =  '" . $this->auth->user->accountID . "' ".$search_where_family."
				ORDER BY `fn`.added desc) limit ".$this->pagination_library->start.", ".$this->pagination_library->amount);

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$contacts = $this->db->query('SELECT `name`, `contactID` FROM
			( SELECT `name`, `contactID`
			FROM '.$this->db->dbprefix('orgs_contacts').'
			WHERE '.$this->db->dbprefix('orgs_contacts').'.accountID = '.$this->auth->user->accountID.') tb1
			UNION ALL
			( SELECT CONCAT('.$this->db->dbprefix('family_contacts').'.first_name, " ",'.$this->db->dbprefix('family_contacts').'.last_name) as `name`, `contactID`
			FROM '.$this->db->dbprefix('family_contacts').'
			WHERE '.$this->db->dbprefix('family_contacts').'.accountID = '.$this->auth->user->accountID.')
			ORDER BY name');

		// prepare data for view
		$data = array(
			'tab' => $tab,
			'current_page' => $current_page,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'section' => $section,
			'buttons' => $buttons,
			'contacts' => $contacts,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'notifications' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/notification_history', $data);

	}


	/**
	 * view a notification
	 * @param  int $notificationID
	 * @return void
	 */
	public function view_org($notificationID = NULL)
	{

		if ($notificationID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($notificationID)) {
			show_404();
		}

		// if so, check user exists
		$where = array(
			'notificationID' => $notificationID,
			'orgs_notifications.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('orgs_notifications.*, orgs_contacts.name, CONCAT(' . $this->db->dbprefix('staff') . ".first, ' ', " . $this->db->dbprefix('staff') . ".surname) as staff", FALSE)->from('orgs_notifications')->join('orgs_contacts', 'orgs_notifications.contactID = orgs_contacts.contactID', 'left')->join('staff', 'orgs_notifications.byID = staff.staffID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$notification_info = $row;
		}

		// look up org
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		// set defaults
		$title = 'New Notification';
		if ($notificationID != NULL) {
			$title = $notification_info->subject;
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		$where = array(
			'notificationID' => $notificationID,
			'orgs_attachments.accountID' => $this->auth->user->accountID
		);
		$attachments = $this->db->select('orgs_attachments.*')->from('orgs_notifications_attachments_bookings')->join('orgs_attachments', 'orgs_notifications_attachments_bookings.attachmentID = orgs_attachments.attachmentID', 'inner')->where($where)->order_by('orgs_attachments.name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'notification_info' => $notification_info,
			'attachments' => $attachments
		);

		// load view
		$this->load->view('bookings/notification_slide_out', $data);
	}

	/**
	 * view a notification
	 * @param  int $notificationID
	 * @return void
	 */
	public function view_family($notificationID = NULL){
		if ($notificationID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($notificationID)) {
			show_404();
		}

		// if so, check user exists
		$where = array(
			'notificationID' => $notificationID,
			'family_notifications.accountID' => $this->auth->user->accountID
		);

		// run query

		$query = $this->db->select('family_notifications.*, CONCAT(' . $this->db->dbprefix('family_contacts') . '.first_name, " ", ' . $this->db->dbprefix('family_contacts') . '.last_name) as name, CONCAT(' . $this->db->dbprefix('staff') . ".first, ' ', " . $this->db->dbprefix('staff') . ".surname) as staff", FALSE)->from('family_notifications')->join('family_contacts', 'family_notifications.contactID = family_contacts.contactID', 'left')->join('staff', 'family_notifications.byID = staff.staffID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$notification_info = $row;
			$familyID = $notification_info->familyID;
		}

		// required
		if ($familyID == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		$where = array(
			'notificationID' => $notificationID,
			'bookings_attachments.accountID' => $this->auth->user->accountID
		);
		$attachments = $this->db->select('bookings_attachments.*')->from('family_notifications_attachments')->join('bookings_attachments', 'family_notifications_attachments.attachmentID = bookings_attachments.attachmentID', 'inner')->where($where)->order_by('bookings_attachments.name asc')->get();

		// prepare data for view
		$data = array(
			'notification_info' => $notification_info,
			'attachments' => $attachments
		);

		// load view
		$this->load->view('bookings/notification_slide_out', $data);
	}

	/**
	 * handle sms notification
	 * @return mixed
	 */
	private function sms() {

		if (!$this->auth->has_features('sms')) {
			show_403();
			return FALSE;
		}

		// set validation rules
		$this->form_validation->set_rules('message', 'Message', 'trim|xss_clean|required|max_length[160]');

		// validate blocks
		$blocks = $this->input->post('blocks');
		if (!is_array($blocks)) {
			$blocks = array();
		}

		if (count($blocks) > 0) {
			if(in_array("all", $blocks) && count($blocks) > 1){
				$this->errors[] = 'In order to send a notification select either All or a block name(s)';
			}
		}else{
			$this->errors[] = 'In order to send a notification select either All or a block name(s)';
		}

		if ($this->form_validation->run() == FALSE) {
			$this->errors = $this->form_validation->error_array();
		} else {

			if (count($this->errors) === 0) {
				// log contacts who could not be contacted
				$notifications_failed = array();

				// log successful
				$notifications_sent = array();

				if ($this->contacts->num_rows() > 0) {
					foreach ($this->contacts->result() as $contact) {

						// if contact has no mobile
						if (empty($contact->mobile)) {
							// log
							$notifications_failed[$contact->contactID] = $contact;
						} else {

							// get message
							$message = set_value('message');

							// smart tags
							$smart_tags = array(
								'contact_first' => $contact->first_name,
								'contact_last' => $contact->last_name,
								'event_name' => $this->booking_info->name
							);

							// replace smart tags
							if (count($smart_tags) > 0) {
								foreach ($smart_tags as $tag => $value) {
									$message = str_replace('{' . $tag . '}', $value, $message);
								}
							}

							// normalise mobile
							$contact->mobile = $this->crm_library->normalise_mobile($contact->mobile, $contact->accountID);

							// check if valid number
							if (!$this->crm_library->check_mobile($contact->mobile, $contact->accountID)) {
								// log
								$notifications_failed[$contact->contactID] = $contact;
							} else {

								// prepare data
								$data = array(
									'familyID' => $contact->familyID,
									'contactID' => $contact->contactID,
									'type' => 'sms',
									'destination' => $contact->mobile,
									'contentText' => $message,
									'status' => 'pending',
									'byID' => $this->auth->user->staffID,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID,
									'bookingID' => $this->bookingID
								);

								// insert
								$this->db->insert('family_notifications', $data);

								// all ok, log
								$notifications_sent[$contact->contactID] = $contact;
							}
						}
					}
				}

				if (count($notifications_failed) + count($notifications_sent) == 0) {
					$info = "There are no participants in this booking to send a message to";
				} else if (count($notifications_failed) > 0 && count($notifications_sent) == 0) {
					$error = "Message failed to send to all participants";
				} else if (count($notifications_failed) == 0 && count($notifications_sent) > 0) {
					$success = "Message successfully scheduled to send to all participants";
				} else {
					// some failed and some sent
					$success = "Message successfully scheduled to send to " . count($notifications_sent) . " participant";
					if (count($notifications_sent) != 1) {
						$success .= "s";
					}
					$error = "Message could not be sent to ";
					$list_failed = array();
					foreach ($notifications_failed as $contactID => $contactInfo) {
						$list_failed[] = trim(ucwords($contactInfo->title) . " " . $contactInfo->first_name . " " . $contactInfo->last_name);
					}
					$error .= implode(", ", $list_failed);
				}

				// save outcome
				if (isset($error) && !empty($error)) {
					$this->session->set_flashdata('error', $error);
				}

				if (isset($info) && !empty($info)) {
					$this->session->set_flashdata('info', $info);
				}

				if (isset($success) && !empty($success)) {
					$this->session->set_flashdata('success', $success);
					redirect($this->return_to);
				}
			}
		}
	}

	/**
	 * handle email notification
	 * @return mixed
	 */
	private function email() {
		// set validation rules
		$this->form_validation->set_rules('subject', 'Subject', 'trim|xss_clean|required');
		$this->form_validation->set_rules('email', 'Message', 'trim|required');
		$this->form_validation->set_rules('attachmentID', 'Attachment', 'trim|xss_clean');
		// validate blocks
		$blocks = $this->input->post('blocks');
		if (!is_array($blocks)) {
			$blocks = array();
		}
		if (count($blocks) > 0) {
			if(in_array("all", $blocks) && count($blocks) > 1){
				$this->errors[] = 'In order to send a notification select either All or a block name(s)';
			}
		}else{
			$this->errors[] = 'In order to send a notification select either All or a block name(s)';
		}

		if ($this->form_validation->run() == FALSE) {
			$this->errors = $this->form_validation->error_array();
		} else {
			if (count($this->errors) === 0) {

				// log contacts who could not be contacted
				$notifications_failed = array();

				// log successful
				$notifications_sent = array();

				if ($this->contacts->num_rows() > 0) {
					foreach ($this->contacts->result() as $contact) {

						// if contact has no email or is invalid
						if (empty($contact->email) || !filter_var($contact->email, FILTER_VALIDATE_EMAIL)) {
							// log
							$notifications_failed[$contact->contactID] = $contact;
						} else {

							// get subject and message
							$subject = set_value('subject', NULL, FALSE);
							$message = $this->input->post('email', FALSE);

							// smart tags
							$smart_tags = array(
								'contact_first' => $contact->first_name,
								'contact_last' => $contact->last_name,
								'event_name' => $this->booking_info->name
							);

							// replace smart tags
							if (count($smart_tags) > 0) {
								foreach ($smart_tags as $tag => $value) {
									$subject = str_replace('{' . $tag . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
									$message = str_replace('{' . $tag . '}', $value, $message);
								}
							}

							// html email
							$email_html = $message;

							// get html email and convert to plain text
							$this->load->helper('html2text');
							$html2text = new \Html2Text\Html2Text($email_html);
							$email_plain = $html2text->get_text();

							// prepare data
							$data = array(
								'familyID' => $contact->familyID,
								'contactID' => $contact->contactID,
								'type' => 'email',
								'destination' => $contact->email,
								'subject' => $subject,
								'contentText' => $email_plain,
								'contentHTML' => $email_html,
								'status' => 'sent',
								'byID' => $this->auth->user->staffID,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID,
								'bookingID' => $this->bookingID
							);

							// insert
							$this->db->insert('family_notifications', $data);

							$notificationID = $this->db->insert_id();

							// handle attachments
							$attachments = array();

							// look up selected event attachment
							if (set_value('attachmentID') != '') {
								$where = array(
									'bookingID' => $this->bookingID,
									'attachmentID' => set_value('attachmentID'),
									'accountID' => $this->auth->user->accountID
								);

								$res_attachment = $this->db->from('bookings_attachments')->where($where)->get();

								if ($res_attachment->num_rows() > 0) {
									foreach ($res_attachment->result() as $attachment) {

										$path = UPLOADPATH . $attachment->path;
										$attachments[$path] = $attachment->name;

										// add to db
										$data = array(
											'notificationID' => $notificationID,
											'attachmentID' => $attachment->attachmentID,
											'accountID' => $this->auth->user->accountID
										);

										$res_insert = $this->db->insert('family_notifications_attachments', $data);
									}
								}
							}

							// send email
							if ($this->crm_library->send_email($contact->email, $subject, $email_html, $attachments, TRUE, $this->booking_info->accountID, $this->booking_info->brandID)) {
								// log
								$notifications_sent[$contact->contactID] = $contact;
							} else {
								// log
								$notifications_failed[$contact->contactID] = $contact;
							}
						}
					}
				}

				if (count($notifications_failed) + count($notifications_sent) == 0) {
					$info = "There are no participants in this booking to send a message to";
				} else if (count($notifications_failed) > 0 && count($notifications_sent) == 0) {
					$error = "Message failed to send to all participants";
				} else if (count($notifications_failed) == 0 && count($notifications_sent) > 0) {
					$success = "Message successfully sent to all participants";
				} else {
					// some failed and some sent
					$success = "Message successfully sent to " . count($notifications_sent) . " participant";
					if (count($notifications_sent) != 1) {
						$success .= "s";
					}
					$error = "Message could not be sent to ";
					$list_failed = array();
					foreach ($notifications_failed as $contactID => $contactInfo) {
						$list_failed[] = trim(ucwords($contactInfo->title) . " " . $contactInfo->first_name . " " . $contactInfo->last_name);
					}
					$error .= implode(", ", $list_failed);
				}

				// save outcome
				if (isset($error) && !empty($error)) {
					$this->session->set_flashdata('error', $error);
				}

				if (isset($info) && !empty($info)) {
					$this->session->set_flashdata('info', $info);
				}

				if (isset($success) && !empty($success)) {
					$this->session->set_flashdata('success', $success);
					redirect($this->return_to);
				}
			}
		}
	}

}

/* End of file notifications.php */
/* Location: ./application/controllers/bookings/notifications.php */
