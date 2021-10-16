<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notifications extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants'));
	}

	/**
	 * show list of notifications
	 * @return void
	 */
	public function index($familyID = NULL) {

		if ($familyID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('family')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'bell';
		$tab = 'notifications';
		$current_page = 'participants';
		$page_base = 'participants/notifications/' . $familyID;
		$section = 'participants';
		$title = 'Notifications';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account'
 		);

		// set where
		$where = array(
			'family_notifications.familyID' => $familyID,
			'family_notifications.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
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

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-family-notifications', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("family_notifications") . "`.`added` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("family_notifications") . "`.`added` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['contact_id'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("family_notifications") . "`.`contactID` = " . $this->db->escape($search_fields['contact_id']);
			}

			if ($search_fields['message'] != '') {
				$search_where[] = "(`" . $this->db->dbprefix("family_notifications") . "`.`contentText` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%' OR `" . $this->db->dbprefix("family_notifications") . "`.`contentHTML` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%')";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('family_notifications.*, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last')->from('family_notifications')->join('family_contacts', 'family_notifications.contactID = family_contacts.contactID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('family_notifications.added desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('family_notifications.*, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last')->from('family_notifications')->join('family_contacts', 'family_notifications.contactID = family_contacts.contactID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('family_notifications.added desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('name ASC')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'contacts' => $contacts,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'notifications' => $res,
			'familyID' => $familyID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('participants/notifications', $data);
	}

	/**
	 * view a notification
	 * @param  int $notificationID
	 * @return void
	 */
	public function view($notificationID = NULL)
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
			'family_notifications.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('family_notifications.*, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, CONCAT(' . $this->db->dbprefix('staff') . ".first, ' ', " . $this->db->dbprefix('staff') . ".surname) as staff", FALSE)->from('family_notifications')->join('family_contacts', 'family_notifications.contactID = family_contacts.contactID', 'left')->join('staff', 'family_notifications.byID = staff.staffID', 'left')->where($where)->limit(1)->get();

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

		// match
		foreach ($query->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$title = 'New Notification';
		if ($notificationID != NULL) {
			$title = $notification_info->subject;
		}
		$return_to = 'participants/notifications/' . $familyID;
		$icon = 'bell';
		$tab = 'notifications';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account',
			'participants/notifications/' . $familyID => 'Notifications'
 		);

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		$where = array(
			'notificationID' => $notificationID,
			'bookings_attachments.accountID' => $this->auth->user->accountID
		);
		$attachments = $this->db->select('bookings_attachments.*')->from('family_notifications_attachments')->join('bookings_attachments', 'family_notifications_attachments.attachmentID = bookings_attachments.attachmentID', 'inner')->where($where)->order_by('bookings_attachments.name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'return_to' => $return_to,
			'notification_info' => $notification_info,
			'familyID' => $familyID,
			'attachments' => $attachments,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('participants/notification', $data);
	}

}

/* End of file notifications.php */
/* Location: ./application/controllers/participants/notifications.php */
