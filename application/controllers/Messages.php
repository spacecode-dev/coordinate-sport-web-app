<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends MY_Controller {

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array('messages'));
	}

	/**
	 * show list of messages
	 * @return void
	 */
	public function index($folder = 'inbox', $recipient = 'staff') {

		// check params
		if (!in_array($folder, array('inbox', 'sent', 'archive')) && !in_array($recipient, array('staff', 'schools', 'organisations'))) {
			show_404();
		}

		//validate user
		if (($this->auth->user->department == 'headcoach' || $this->auth->user->department == 'fulltimecoach' || $this->auth->user->department == 'coaching') && $recipient != 'staff') {
			show_403();
		}

		// set defaults
		$icon = 'inbox';
		$section = 'messages';
		$current_page = $recipient;
		$page_base = 'messages/'.$folder.'/'.$recipient;
		$add_url = 'messages/'.$folder.'/'.$recipient.'/new';
		$title = ucwords($recipient);
		$buttons = '<a class="btn btn-success" href="' . site_url($add_url) . '"><i class="far fa-plus"></i> Create New</a>';
		if($folder == "archive"){
			$buttons = '';
		}
		$success = NULL;
		$errors = array();
		$error = NULL;
		$info = NULL;
		$bulk_messages = array();
		$breadcrumb_levels = array(
			$page_base.'/' => 'Messages',
			$page_base => ucwords($folder)
		);


		//Schools or Org
		$search_to=array();
		switch ($recipient){
			case 'schools':
			case 'organisations':
				$where = array(
					'orgs.type' => rtrim($recipient,"s"),
					'orgs.accountID' => $this->auth->user->accountID
				);
				$search_to = $this->db->select('orgs.orgID as ID, orgs.name')
					->from('orgs')
					->where($where)
					->order_by('orgs.name asc')
					->get();

				break;
			case 'participants':
				$where = array(
					'family_contacts.active' => 1,
					'family_contacts.accountID' => $this->auth->user->accountID
				);
				$search_to = $this->db->select("family_contacts.contactID as ID, CONCAT(" . $this->db->dbprefix('family_contacts') . ".first_name, ' ', " . $this->db->dbprefix('family_contacts') . ".last_name) as name" )
					->from('family_contacts')
					->where($where)
					->order_by('name asc')
					->get();
				break;
			case "staff":
				$where = array(
					'active' => 1,
					'accountID' => $this->auth->user->accountID
				);
				$search_to = $this->db->select("CONCAT(" . $this->db->dbprefix('staff') . ".first, ' ', " . $this->db->dbprefix('staff') . ".surname) as name, staffID as ID")->from('staff')->where($where)->order_by('name asc')->get();
				break;
		}


		// set where
		$where = array(
			'folder' => $folder,
			'group' => $recipient,
			'isArchived' => 'no'
		);
		if($recipient == "archive"){
			unset($where['group']);
			$where['isArchived'] = 'yes';
		}
		$recipient_field = '';
		switch($recipient){
			case "staff":
				$recipient_field = "forID";
				break;
			case "participants":
				$recipient_field = "for_participantID";
				break;
			case "schools":
			case "organisations":
				$recipient_field = "for_orgID";
				break;
		}

		$where_archive = array();
		switch ($folder) {
			case 'inbox':
				$where['messages.'.$recipient_field] = $this->auth->user->staffID;
				break;
			case 'sent':
				$where['messages.byID'] = $this->auth->user->staffID;
				break;
			case 'archive':
				unset($where['folder']);
				$where_archive['('.$this->db->dbprefix("messages").'.byID = '.$this->auth->user->staffID.' OR '.$this->db->dbprefix("messages").'.'.$recipient_field.' = '.$this->auth->user->staffID.')'] = NULL;
				$where['isArchived'] = 'yes';
				break;
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'from_id' => NULL,
			'to_id' => NULL,
			'subject' => NULL,
			'message' => NULL,
			'search' => NULL,
			'date' => NULL
		);

		// if bulk action
		if ($this->input->post('bulk')) {
			// load libraries
			$this->load->library('form_validation');

			// validate
			$this->form_validation->set_rules('action', 'Bulk Action', 'trim|xss_clean|required');

			// run validation
			$this->form_validation->run();

			// get messages
			$bulk_messages = $this->input->post('bulk_messages');
			if (!is_array($bulk_messages)) {
				$bulk_messages = array();
			}
			$bulk_messages = array_filter($bulk_messages);

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				if (count($bulk_messages) == 0) {
					$error = 'Please select at least one message';
				} else {
					$where = [];
					// check permission
					switch ($folder) {
						case 'inbox':
							$where['messages.'.$recipient_field] = $this->auth->user->staffID;
							break;
						case 'sent':
							$where['messages.byID'] = $this->auth->user->staffID;
							break;
					}

					// run query
					$query = $this->db->select('messages.*')
						->from('messages')
						->join('accounts', 'messages.accountID = accounts.accountID', 'inner')
						->where($where)
						->where($where_archive, NULL, FALSE)
						// messages sent from own account or admin account
						->where('(messages.accountID = ' . $this->db->escape($this->auth->user->accountID) . ' OR accounts.admin = 1)')
						->where_in('messages.messageID', $bulk_messages)
						->get();

					// no match
					if ($query->num_rows() == 0) {
						$error = 'Please select at least one message';
					} else {
						// matches
						$actioned = 0;
						foreach ($query->result() as $message_info) {
							switch (set_value('action')) {
								case 'delete':
									$where = array(
										'messageID' => $message_info->messageID
									);
									$query = $this->db->delete('messages', $where, 1);
									$actioned += $this->db->affected_rows();
									break;
								case 'read':
									if ($message_info->folder == 'sent') {
										break;
									}
									$where = array(
										'messageID' => $message_info->messageID
									);
									$data = array(
										'status' => 1,
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);
									$query = $this->db->update('messages', $data, $where, 1);
									$actioned += $this->db->affected_rows();
									break;
								case 'unread':
									if ($message_info->folder == 'sent') {
										break;
									}
									$where = array(
										'messageID' => $message_info->messageID
									);
									$data = array(
										'status' => 0,
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);
									$query = $this->db->update('messages', $data, $where, 1);
									$actioned += $this->db->affected_rows();
									break;
								case 'archive':
									$where = array(
										'messageID' => $message_info->messageID
									);
									$data = array(
										'isArchived' => 'yes',
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);
									$query = $this->db->update('messages', $data, $where, 1);
									$actioned += $this->db->affected_rows();
									break;
							}
						}
						if ($actioned == 1) {
							$this->session->set_flashdata('success', $actioned . ' message has been actioned successfully.');
						} else {
							$this->session->set_flashdata('success', $actioned . ' messages have been actioned successfully.');
						}

						redirect(current_url());
						exit();
					}
				}
			}
		}
		// if search
		if ($this->input->post('search')) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_from_id', 'From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_to_id', 'To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_subject', 'Subject', 'trim|xss_clean');
			$this->form_validation->set_rules('search_message', 'Message', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			if($recipient == "archive"){
				$this->form_validation->set_rules('search_date', 'Date', 'trim|xss_clean');
			}

			// run validation
			$this->form_validation->run();

			$search_fields['from_id'] = set_value('search_from_id');
			$search_fields['to_id'] = set_value('search_to_id');
			$search_fields['subject'] = set_value('search_subject');
			$search_fields['message'] = set_value('search_message');
			$search_fields['search'] = set_value('search');
			if($recipient == "archive"){
				$search_fields['date'] = set_value('search_date');
			}

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-messages'))) {

			foreach ($this->session->userdata('search-messages') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-messages', $search_fields);

			if ($search_fields['from_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("messages") . "`.`byID` = " . $this->db->escape($search_fields['from_id']);
			}

			if ($search_fields['to_id'] != '') {
				if($recipient == "archive"){
					$search_where[] = '(`' . $this->db->dbprefix("messages") . "`.`forID` = " . $this->db->escape($search_fields['to_id']).' OR `' . $this->db->dbprefix("messages") . "`.`for_orgID` = " . $this->db->escape($search_fields['to_id']).' OR `' . $this->db->dbprefix("messages") . "`.`for_org_cusID` = " . $this->db->escape($search_fields['to_id']).' OR `' . $this->db->dbprefix("messages") . "`.`for_participantID` = " . $this->db->escape($search_fields['to_id']).')';
				}else{
					$search_where[] = '`' . $this->db->dbprefix("messages") . "`.`".$recipient_field."` = " . $this->db->escape($search_fields['to_id']);
				}
			}

			if ($search_fields['subject'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("messages") . "`.`subject` LIKE '%" . $this->db->escape_like_str($search_fields['subject']) . "%'";
			}

			if ($search_fields['message'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("messages") . "`.`message` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%'";
			}

			if ($search_fields['date'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date']);
				if ($date_from !== FALSE) {
					$search_where[] = 'date(`' . $this->db->dbprefix("messages") . "`.`added`) = " . $this->db->escape($date_from);
				}
			}
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$where['messages.accountID'] = $this->auth->user->accountID;
		$res = $this->db->from('messages')->where($where)->where($where_archive, NULL, FALSE)->where($search_where, NULL, FALSE)->order_by('added desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// switch staff join depending on folder
		$res = $this->db->from('messages')->join('messages_attachments', 'messages.messageID = messages_attachments.messageID', 'left')->where($where)->where($search_where, NULL, FALSE)->group_by('messages.messageID')->order_by('messages.added desc')->limit($this->pagination_library->amount, $this->pagination_library->start);
		switch ($folder) {
			case 'inbox':
				switch($recipient) {
					case "staff":
						$res = $this->db->select('messages.*, staff.first, staff.surname, COUNT(' . $this->db->dbprefix("messages_attachments") . '.attachmentID) as attachments')
							->join('staff', 'messages.forID = staff.staffID', 'inner')->get();
						break;
					default:
						$res = $this->db->get();
						break;
				}
				break;
			case 'archive':
			case 'sent':
				switch($recipient){
					case "staff":
						$res = $this->db->select('messages.*, staff.first, staff.surname, COUNT(' . $this->db->dbprefix("messages_attachments") . '.attachmentID) as attachments')
							->join('staff', 'messages.forID = staff.staffID', 'inner')->get();
						break;
					case "participants":
						$res = $this->db->select('messages.*, family_contacts.first_name, family_contacts.last_name, COUNT(' . $this->db->dbprefix("messages_attachments") . '.attachmentID) as attachments')
							->join('family_contacts', 'messages.for_participantID = family_contacts.contactID', 'inner')->get();
						break;
					case 'schools':
					case 'organisations':
						$res = $this->db->select('messages.*, orgs.name, COUNT(' . $this->db->dbprefix("messages_attachments") . '.attachmentID) as attachments, orgs_contacts.name as contact_name')
							->join('orgs', 'messages.for_orgID = orgs.orgID', 'inner')
							->join('orgs_contacts', 'messages.for_org_cusID = orgs_contacts.contactID', 'left')
							->get();
						break;
				}
				break;
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'folder' => $folder,
			'page_base' => $page_base,
			'submit_to' => current_url(),
			'messages' => $res,
			'group' => $recipient,
			'search_to' => $search_to,
			'bulk_messages' => $bulk_messages,
			'add_url' => $add_url,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('messages/main', $data);
	}

	/**
	 * view a message
	 * @param  int $messageID
	 * @return void
	 */
	public function view($recipient = "staff", $messageID = NULL)
	{

		// check if numeric
		if (!ctype_digit($messageID)) {
			show_404();
		}

		//validate user
		if (($this->auth->user->department == 'headcoach' || $this->auth->user->department == 'fulltimecoach' || $this->auth->user->department == 'coaching') && $recipient != 'staff') {
			show_403();
		}

		// if so, check exists
		$where = array(
			'messageID' => $messageID
		);

		// run query
		$query = $this->db->from('messages')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$message_info = $row;
		}

		// check permission
		switch ($message_info->folder) {
			case 'inbox':
				 if ($message_info->forID !== $this->auth->user->staffID) {
					show_404();
				 }
				break;
			case 'sent':
				 if ($message_info->byID !== $this->auth->user->staffID) {
					show_404();
				 }
				break;
		}

		// mark read
		$data = array(
			'status' => 1
		);

		$this->db->update('messages', $data, $where);

		// set defaults
		$title = $message_info->subject;
		$return_to = 'messages';
		if ($message_info->folder == 'sent') {
			$return_to .= '/sent/'.$recipient;
		}
		$icon = 'inbox';
		$current_page = $message_info->folder.'_'.$recipient;
		$section = 'messages';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		if ($message_info->folder == 'inbox') {
			$buttons .= ' <a class="btn btn-success" href="' . site_url('messages/reply/'.$recipient .'/'. $messageID) . '"><i class="far fa-mail-reply"></i> Reply</a>';
		}
		$breadcrumb_levels = array();

		// get from/to
		$where = [];
		switch ($message_info->folder) {
			case 'inbox':
				$where['staffID'] = $message_info->byID;
				$staff = $this->db->from('staff')->where($where)->get();
				$breadcrumb_levels['messages/inbox/'.$recipient.'/'] = 'Messages';
				$breadcrumb_levels['messages/inbox/'.$recipient] = 'Inbox';
				break;
			case 'sent':
				$recipient_origin = $recipient;
				if($recipient == "archive"){
					$recipient_origin = $message_info->group;
				}
				switch($recipient_origin){
					case "staff":
						$where['staffID'] = $message_info->forID;
						$staff = $this->db->from('staff')->where($where)->get();
						break;
					case "participants":
						$where['contactID'] = $message_info->for_participantID;
						$staff = $this->db->from('family_contacts')->where($where)->get();
						break;
					default:
						if(!empty($message_info->for_org_cusID)){
							$where['contactID'] = $message_info->for_org_cusID;
							$staff = $this->db->from('orgs_contacts')->where($where)->get();
						}else {
							$where['orgID'] = $message_info->for_orgID;
							$where['type'] = rtrim($recipient, "s");
							$staff = $this->db->from('orgs')->where($where)->get();
						}
						break;
				}
				$breadcrumb_levels['messages/sent/'.$recipient.'/'] = 'Messages';
				$breadcrumb_levels['messages/sent/'.$recipient] = 'Sent';
				break;
		}

		foreach ($staff->result() as $row) {
			$staff_info = $row;
		}

		$where = array(
			'messageID' => $messageID,
			'messages_attachments.accountID' => $this->auth->user->accountID
		);
		$attachments = $this->db->from('messages_attachments')->where($where)->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'return_to' => $return_to,
			'message_info' => $message_info,
			'attachments' => $attachments,
			'staff_info' => $staff_info,
			'messageID' => $messageID,
			'group' => $recipient,
			'breadcrumb_levels' => $breadcrumb_levels
		);

		// load view
		$this->crm_view('messages/message', $data);
	}

	/**
	 * new message
	 * @return void
	 */
	public function new_message($folder = 'inbox', $recipient = 'staff', $messageID = NULL, $recipientID = NULL)
	{
		$message_info = new stdClass;

		//validate user
		if (($this->auth->user->department == 'headcoach' || $this->auth->user->department == 'fulltimecoach' || $this->auth->user->department == 'coaching') && $recipient != 'staff') {
			show_403();
		}

		// check if editing
		if ($messageID != NULL) {

			// check if numeric
			if (!ctype_digit($messageID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'messageID' => $messageID,
				'forID' => $this->auth->user->staffID
			);

			// run query
			$query = $this->db->from('messages')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$message_info = $row;
			}
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Message';
		$submit_to = 'messages/'.$folder.'/'.$recipient.'/new';
		if ($recipientID != NULL) {
			$submit_to = 'messages/'.$folder.'/'.$recipient.'/new/'. $recipientID;
		}
		if ($messageID != NULL) {
			$submit_to = 'messages/reply/'.$recipient .'/'. $messageID;
		}
		$return_to = 'messages/'.$folder.'/'.$recipient;
		$icon = 'inbox';
		$current_page = $recipient;
		$section = 'messages';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		$orgs = array();$staff = array();$participants = array();
		switch($recipient){
			case 'staff':
				//Staff
				$where = [
					'staff.active' => 1,
					'staff.accountID' => $this->auth->user->accountID
				];

				$staff = $this->db->select('staff.*, staff_groups.groupID')->from('staff')->join('staff_groups', 'staff.staffID = staff_groups.staffID', 'left')->where($where)->order_by('first asc, surname asc')->get();

				if ($messageID != NULL) {
					$where = "active=1 AND (staff.accountID='". $this->auth->user->accountID ."' OR staff.staffID=". $message_info->byID .")";
					$staff = $this->db
						->select('staff.*, staff_groups.groupID')
						->from('staff')
						->join('staff_groups', 'staff.staffID = staff_groups.staffID', 'left')
						->where($where)
						->order_by('first asc, surname asc')->get();
				}
				break;
			case 'schools':
			case 'organisations':
				if($recipient == "schools" || $recipient == "organisations"){
					$where = array(
						'orgs.type' => rtrim($recipient, "s"),
						'orgs.accountID' => $this->auth->user->accountID
					);
					if(!empty($recipientID)){
						$where['orgs.orgID'] = $recipientID;
						$where['orgs_contacts.isMain'] = '1';
						$where['orgs_contacts.active'] = '1';
						$orgs = $this->db->select('orgs.schoolType,orgs.isPrivate,orgs.prospect, orgs_contacts.contactID as orgID, orgs_contacts.name')
							->from('orgs')
							->join('orgs_contacts', 'orgs.orgID = orgs_contacts.orgID','inner')
							->where($where)
							->order_by('orgs.name asc')
							->get();
						if($orgs->num_rows() == 0){
							$errors[] = "There is no primary contact found for this organisation. Please assign primary contact from <a href='".site_url('customers/contacts/'.$orgID)."'>here</a>.";
						}
					}else {
						$orgs = $this->db->select('orgs.*')
							->from('orgs')
							->where($where)
							->order_by('orgs.name')
							->get();
					}
				}
				break;
			case 'participants':
				$where = array(
					'family_contacts.accountID' => $this->auth->user->accountID
				);
				$where['family_contacts.main'] = '1';
				$where['family_contacts.active'] = '1';
				$participants = $this->db->select('family_contacts.contactID, CONCAT_WS(" ", '. $this->db->dbprefix('family_contacts') . '.first_name, '. $this->db->dbprefix('family_contacts') . '.last_name) as name')
					->from('family_contacts')
					->where($where)
					->order_by('name asc')
					->get();
				if($participants->num_rows() == 0){
					$errors[] = "There is no primary contact found for this organisation. Please create participant from <a href='".site_url('participants/new-account/')."'>here</a>.";
				}
				break;

		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('to', 'To', 'trim|xss_clean|callback_multiple_select['.$recipient.']');
			$this->form_validation->set_rules('subject', 'Subject', 'trim|xss_clean|required');
			$this->form_validation->set_rules('message', 'Message', 'trim|required');
			$this->form_validation->set_rules('template', 'Template', 'trim');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			}

			if (empty($errors)) {
				$this->load->library('upload');
				$upload_res = $this->crm_library->handle_multi_upload();

				if (!empty($this->upload->display_errors())) {
					$errors[] = strip_tags($this->upload->display_errors());
				}
			}

			if (empty($errors)) {
				// all ok
				$to = array();
				if($recipient === "participants"){
					$where_participant = array(
						"bookings_cart.accountID" => $this->auth->user->accountID,
						"bookings_cart.type" => "booking",
					);
					$where_add = "";
					if($this->input->post("date_from") || $this->input->post("date_to")){
						$date_from = uk_to_mysql_date($this->input->post("date_from"));
						$date_to = uk_to_mysql_date($this->input->post("date_to"));
						$where_add .= "(".$this->db->dbprefix("bookings_blocks").".startDate >= '".$date_from."'  AND ".$this->db->dbprefix("bookings_blocks").".startDate <= '".$date_to."')";
					}
					if($this->input->post("typeIDs") && count($this->input->post("typeIDs")) > 0){
						$typeIDs = implode(",", $this->input->post("typeIDs"));
						if(!empty($where_add)){
							$where_add .= " AND ";
						}
						$where_add .= $this->db->dbprefix("bookings_lessons").".typeID IN (".$typeIDs.")";
					}
					if($this->input->post("activities") && count($this->input->post("activities")) > 0){
						$activities = implode(",", $this->input->post("activities"));
						if(!empty($where_add)){
							$where_add .= " AND ";
						}
						$where_add .= $this->db->dbprefix("bookings_lessons").".activityID IN (".$activities.")";
					}

					$to_recipents = array();
					if(!empty($where_add)){
						$to_recipents = $this->db->select('bookings_cart_sessions.contactID, bookings_cart_sessions.childID')
							->from('bookings_cart')
							->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
							->join('bookings_blocks', 'bookings_cart_sessions.blockID = bookings_blocks.blockID','left')
							->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID','left')
							->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID','left')
							->join('family_contacts', 'bookings_cart_sessions.contactID IS NOT NULL and '.$this->db->dbprefix("bookings_cart_sessions").'.contactID = '.$this->db->dbprefix("family_contacts").'.contactID','left')
							->where($where_participant)
							->where($where_add)
							->group_by("bookings_cart_sessions.contactID, bookings_cart_sessions.childID")
							->get();
					}

					if(!is_array($to_recipents) && $to_recipents->num_rows() > 0){
						foreach($to_recipents->result() as $row){
							if(!empty($row->childID)){
								$contacts = $this->db->select('contactID')
									->from("family_children")
									->join("family_contacts", "family_children.familyID = family_contacts.familyID", "inner")
									->where(array("family_children.childID" => $row->childID, "family_contacts.main" => 1))
									->limit(1)
									->get();
								if($contacts->num_rows() > 0){
									foreach($contacts->result() as $contacts_data) break;
									$to[] = $contacts_data->contactID;
								}
							}else{
								$to[] = $row->contactID;
							}
						}
					}

					if($this->input->post('to')){
						$to = array_merge($to, $this->input->post('to'));
					}
					if(count($to) > 0){
						$to = array_unique($to);
					}else{
						$errors[] = 'No users found with the provided configurations.';
					}
				}else{
					$to = $this->input->post('to');
				}

				// prepare data
				$data = array(
					'byID' => $this->auth->user->staffID,
					'forID' => NULL,
					'folder' => 'inbox',
					'subject' => set_value('subject'),
					'message' => $this->input->post('message', FALSE),
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($messageID != NULL) {
					$data['replyTo'] = $messageID;
				}

				$template = $this->input->post('template');

				$attachments = array();
				$upload_res = $this->crm_library->handle_multi_upload();

				if (is_array($upload_res)) {
					$attachments = $upload_res;
				}

				$templateAttachments = [];
				//find attachments in templates
				if ($template) {
					$query = $this->db->from('message_templates_attachments')->where([
						'templateID' => (int)$template
					])->get();

					if ($query->num_rows() > 0) {
						foreach ($query->result() as $row) {
							$templateAttachments[] = $row;
						}
					}
				}

				// final check for errors
				if (count($errors) == 0) {

					$sent = 0;

					foreach ($to as $forID) {

						$data['group'] = $this->input->post('group');
						if($recipient == "schools" || $recipient == "organisations"){
							$data['for_orgID'] = $forID;
							if($this->input->post('org_contact')){
								$data['for_orgID'] = $this->input->post('org_contact');
								$data['for_org_cusID'] = $forID;
							}
						}else if($recipient == "participants"){
							$data['for_participantID'] = $forID;
						}else{
							$data['forID'] = $forID;
							$data['folder'] = 'inbox';
							$data['status'] = 0;

							// insert
							$query = $this->db->insert('messages', $data);
						}

						// if inserted
						if ($this->db->affected_rows() == 1 || $recipient !== "staff") {

							$sent++;

							$messageID = $this->db->insert_id();

							// save attachment if set
							if (count($attachments) > 0) {
								foreach ($attachments as $attachment) {
									$attachment_data = array(
										'byID' => $this->auth->user->staffID,
										'accountID' => $this->auth->user->accountID,
										'name' => $attachment['client_name'],
										'path' => $attachment['raw_name'],
										'type' => $attachment['file_type'],
										'size' => $attachment['file_size']*1024,
										'ext' => substr($attachment['file_ext'], 1),
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'messageID' => $messageID
									);

									// insert
									$query = $this->db->insert('messages_attachments', $attachment_data);
								}
							}

							// attach template attachments to email
							if (count($templateAttachments) > 0) {
								foreach ($templateAttachments as $attachment) {
									$attachment_data = array(
										'byID' => $this->auth->user->staffID,
										'accountID' => $this->auth->user->accountID,
										'name' => $attachment->name,
										'path' => $attachment->path,
										'type' => $attachment->type,
										'size' => $attachment->size,
										'ext' => $attachment->ext,
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'messageID' => $messageID
									);

									// insert
									$query = $this->db->insert('messages_attachments', $attachment_data);
								}
							}

							// look up staff/Orgs
							$table_name = "staff";
							$field_name = "staffID";

							if($recipient == "schools" || $recipient == "organisations"){
								$table_name = "orgs";
								$field_name = "orgID";
								if($this->input->post('recipientID')){
									$table_name = "orgs_contacts";
									$field_name = "contactID";
								}
							}else if($recipient == "participants"){
								$table_name = "family_contacts";
								$field_name = "contactID";
							}
							$where = array(
								$field_name => $forID,
							);
							$res = $this->db->from($table_name)->where($where)->get();

							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									if (!empty($row->email)) {
										if($recipient == "staff") {
											$message = "<p>Hi " . $row->first . ",</p>";
										}else if($recipient == "participants"){
											$message = "<p>Hi " . $row->first_name . ",</p>";
										}else{
											$message = "<p>Hi " . $row->name . ",</p>";
										}
										$message .= "<p>You have received a new message from " . $this->auth->user->first . ' ' . $this->auth->user->surname . ":</p>";
										$message .= $this->input->post('message', FALSE);
										if($recipient == "staff") {
											$message .= "<p>To reply to this message, please visit: " . anchor('messages/reply/staff/' . $messageID) . "</p>";
										}

										$email_attachments = array();
										if (count($attachments) > 0) {
											foreach ($attachments as $attachment) {
												$path = UPLOADPATH . $attachment['raw_name'];
												$email_attachments[$path] = $attachment['client_name'];
											}
										}

										if (count($templateAttachments) > 0) {
											foreach ($templateAttachments as $attachment) {
												$path = UPLOADPATH . $attachment->path;
												$email_attachments[$path] = $attachment->name;
											}
										}

										// send email
										$this->crm_library->send_email($row->email, set_value('subject', NULL, FALSE), $message, $email_attachments, FALSE, $this->auth->user->accountID);
									}
								}
							}

							// copy to sent
							$data['folder'] = 'sent';
							$data['status'] = 1;
							$data['group'] = $this->input->post('group');

							$query = $this->db->insert('messages', $data);

							$messageID = $this->db->insert_id();

							// save attachment if set
							if (count($attachments) > 0) {
								foreach ($attachments as $attachment) {
									$attachment_data = array(
										'byID' => $this->auth->user->staffID,
										'accountID' => $this->auth->user->accountID,
										'name' => $attachment['client_name'],
										'path' => $attachment['raw_name'],
										'type' => $attachment['file_type'],
										'size' => $attachment['file_size']*1024,
										'ext' => substr($attachment['file_ext'], 1),
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'messageID' => $messageID
									);

									// insert
									$query = $this->db->insert('messages_attachments', $attachment_data);
								}
							}

							// attach template attachments to email
							if (count($templateAttachments) > 0) {
								foreach ($templateAttachments as $attachment) {
									$attachment_data = array(
										'byID' => $this->auth->user->staffID,
										'accountID' => $this->auth->user->accountID,
										'name' => $attachment->name,
										'path' => $attachment->path,
										'type' => $attachment->type,
										'size' => $attachment->size,
										'ext' => $attachment->ext,
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'messageID' => $messageID
									);

									// insert
									$query = $this->db->insert('messages_attachments', $attachment_data);
								}
							}
						}

					}

					if ($sent > 0) {
						$this->session->set_flashdata('success', set_value('subject') . ' has been sent successfully.');
						redirect('messages/'.$folder.'/'.$recipient);
						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		//Get Groups
		$where = [
			'staff_groups.accountID' => $this->auth->user->accountID
		];

		$groups = $this->db
			->select('groups.name, groups.groupID')
			->from('staff_groups')
			->join('groups', 'staff_groups.groupID = groups.groupID', 'inner')
			->join('staff', 'staff_groups.staffID = staff.staffID', 'inner')
			->where($where)
			->group_by('groups.groupID')
			->order_by('groups.name asc')->get();

		$adminStaffListing = $this->db->from('staff')->where([
			'active' => 1,
		])->where('staffID !=', $this->auth->user->staffID, FALSE)->order_by('first asc, surname asc')->get();
		$staffListingByDep = [];
		if ($adminStaffListing->num_rows() > 0) {
			foreach ($adminStaffListing->result() as $item) {
				$staffListingByDep[$item->department][] = $item->staffID;
			}
		}

		$where = [
			'accountID' => $this->auth->user->accountID,
			'staffID'   => $this->auth->user->staffID
		];

		$query = $this->db->from('message_templates')->where($where)->get();

		$templates = [];
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $item) {
				$templates[$item->id] = $item->name;
			}
		}

		// get list of session types
		$lesson_types = array();
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$res = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_types[$row->typeID] = $row->name;
			}
		}

		// get list of activities
		$activities = [];
		$res = $this->db->from('activities')
			->where([
				'accountID' => $this->auth->user->accountID,
				'active' => 1
			])
			->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$activities[$row->activityID] = $row->name;
			}
		}

		$departments = $this->settings_library->getDepartments($this->auth->user->accountID);

		$staff_array = array();
		// get staff_recruitment_approvers data
		$where = array("accountID" => $this->auth->user->accountID);
		$query = $this->db->from("staff_recruitment_approvers")->where($where)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				if(!isset($staff_array[$result->staffID])){
					$staff_array[$result->staffID] = array();
				}
				$staff_array[$result->staffID][] = $result->approverID;
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'message_info' => $message_info,
			'messageID' => $messageID,
			'staff' => $staff,
			'participants' => $participants,
			'departments' => $departments,
			'group' => $recipient,
			'groups' => $groups,
			'recipientID' => $recipientID,
			'orgs' => $orgs,
			'lesson_types' => $lesson_types,
			'activities' => $activities,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'staff_array' => $staff_array,
			'staff_listing_by_dep' => $staffListingByDep,
			'templates' => $templates
		);

		// load view
		$this->crm_view('messages/new', $data);
	}

	public function multiple_select($recipient)
	{
		$to = $this->input->post('to');

		if (empty($to) && $recipient != 'participants') {
			$this->form_validation->set_message('multiple_select', 'To is required');
			return false;
		} else {
			return true;
		}
	}

	public function forward_to_support($messageID = null) {
		$where = [
			'accountID' => $this->auth->user->accountID,
			'messageID' => $messageID
		];

		$query = $this->db->from('messages')->where($where)->get();

		if ($query->num_rows() < 1) {
			show_404();
		}

		$message = [];
		foreach ($query->result() as $row) {
			$message = $row;
		}

		$query = $this->db->from('messages_attachments')->where($where)->get();

		$attachments = [];
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$attachments[] = $row;
			}
		}

		$email_attachments = array();
		if (count($attachments) > 0) {
			foreach ($attachments as $attachment) {
				$path = UPLOADPATH . $attachment->path;
				$email_attachments[$path] = $attachment->name;
			}
		}

		$this->crm_library->send_email('support@coordinate.cloud',
			$message->subject,
			$message->message,
			$email_attachments,
			FALSE,
			$this->auth->user->accountID);

		$this->session->set_flashdata('success', $message->subject . ' has been successfully sent to support.');

		redirect('messages');
	}

	/**
	 * delete a message
	 * @param  int $messageID
	 * @return mixed
	 */
	public function remove($recipient="staff", $messageID = NULL) {

		// check params
		if (empty($messageID)) {
			show_404();
		}

		//validate user
		if (($this->auth->user->department == 'headcoach' || $this->auth->user->department == 'fulltimecoach' || $this->auth->user->department == 'coaching') && $recipient != 'staff') {
			show_403();
		}

		$where = array(
			'messageID' => $messageID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('messages')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$message_info = $row;

			// check permission
			switch ($message_info->folder) {
				case 'inbox':
					 if ($message_info->forID !== $this->auth->user->staffID) {
					 	show_404();
					 }
					break;
				case 'sent':
					 if ($message_info->byID !== $this->auth->user->staffID) {
						show_404();
					 }
					break;
			}

			// all ok, delete
			$query = $this->db->delete('messages', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $message_info->subject . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $message_info->subject . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'messages/inbox/'.$recipient;

			if ($message_info->folder == 'sent') {
				$redirect_to = 'messages/sent/'.$recipient;
			}

			redirect($redirect_to);
		}
	}

	/**
	 * new template
	 * @return void
	 */
	public function template($templateID = NULL)
	{
		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Template';
		$submit_to = 'messages/template';
		if ($templateID) {
			$submit_to = 'messages/template/view/' . $templateID;
		}
		$return_to = 'messages/templates';
		$icon = 'inbox';
		$current_page = 'inbox_templates';
		$section = 'messages';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		$templateInfo = null;
		$templateAttachments = null;
		if ($templateID != null) {

			$where = [
				'id' => (int)$templateID
			];

			$query = $this->db->from('message_templates')->where($where)->get();

			if ($query->num_rows() > 0) {
				foreach ($query->result() as $row) {
					$templateInfo = $row;
				}
			}

			if ($templateInfo) {
				$where = [
					'templateID' => $templateID,
					'message_templates_attachments.accountID' => $this->auth->user->accountID
				];

				$query = $this->db->from('message_templates_attachments')->where($where)->get();

				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$templateAttachments[] = $row;
					}
				}

			}
		}

		// if posted
		if ($this->input->post()) {
			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('subject', 'Subject', 'trim|xss_clean|required');
			$this->form_validation->set_rules('message', 'Message', 'trim|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok
				// prepare data
				$data = array(
					'accountID'     => $this->auth->user->accountID,
					'staffID'       => $this->auth->user->staffID,
					'name'          => set_value('name'),
					'subject'       => set_value('subject'),
					'message'       => $this->input->post('message', FALSE)
				);

				$attachments = array();
				$upload_res = $this->crm_library->handle_multi_upload();

				if (is_array($upload_res)) {
					$attachments = $upload_res;
				}

				if (!$templateID) {
					$this->db->insert('message_templates', $data);
					$templateID = $this->db->insert_id();
					$redirect = 'messages/templates';



				} else {
					$where = [
						'accountID'     => $this->auth->user->accountID,
						'staffID'       => $this->auth->user->staffID,
						'id'            => $templateID
					];
					$this->db->update('message_templates', $data, $where);
					$redirect = 'messages/template/view/' . $templateID;
				}

				if ($templateID > 0) {
					foreach ($attachments as $attachment) {

						$attachment_data = array(
							'accountID'     => $this->auth->user->accountID,
							'name'          => $attachment['client_name'],
							'path'          => $attachment['raw_name'],
							'type'          => $attachment['file_type'],
							'size'          => $attachment['file_size'] * 1024,
							'ext'           => substr($attachment['file_ext'], 1),
							'templateID'    => $templateID
						);

						// insert
						$this->db->insert('message_templates_attachments', $attachment_data);
					}
				}


				if ((int)$templateID > 0) {
					$this->session->set_flashdata('success', 'Template saved successfully.');
					redirect($redirect);
					return TRUE;
				} else {
					$this->session->set_flashdata('info', 'Error saving data, please try again.');
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// prepare data for view
		$data = array(
			'title'                 => $title,
			'icon'                  => $icon,
			'current_page'          => $current_page,
			'section'               => $section,
			'buttons'               => $buttons,
			'submit_to'             => $submit_to,
			'return_to'             => $return_to,
			'templateID'            => $templateID,
			'success'               => $success,
			'errors'                => $errors,
			'info'                  => $info,
			'template_info'         => $templateInfo,
			'template_attachments'  => $templateAttachments
		);

		// load view
		$this->crm_view('messages/template', $data);
	}

	public function remove_template_attachment($attachmentID = null) {
		$attachmentInfo = null;

		$where = [
			'accountID' => $this->auth->user->accountID,
			'id' => $attachmentID
		];

		$query = $this->db->from('message_templates_attachments')->where($where)->get();

		if ($query->num_rows() < 1) {
			show_404();
		}

		foreach ($query->result() as $row) {
			$attachmentInfo = $row;
		}

		$query = $this->db->delete('message_templates_attachments', $where, 1);

		if ($this->db->affected_rows() > 0) {
			$path = UPLOADPATH . $attachmentInfo->path;

			if (file_exists($path)) {
				unlink($path);
			}
		}

		$this->session->set_flashdata('success', 'Attachment has been removed successfully.');

		redirect('messages/template/view/' . $attachmentInfo->id);
	}


	public function templates($folder = 'inbox') {
		// set defaults
		$title = 'templates';
		$return_to = 'messages';
		$icon = 'inbox';
		$current_page = 'templates';
		$section = 'messages';
		$page_base = 'messages/templates';
		$add_url = '/messages/template';
		$buttons = '<a class="btn btn-success" href="' . site_url($add_url) . '"><i class="far fa-plus"></i> Create New</a>';

		$breadcrumb_levels = array(
			'messages' => 'Messages'
		);

		$errors = array();
		$success = NULL;
		$info = NULL;

		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'subject' => NULL,
			'message' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post('search')) {
			// load libraries
			$this->load->library('form_validation');

			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_subject', 'Subject', 'trim|xss_clean');
			$this->form_validation->set_rules('search_message', 'Message', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['subject'] = set_value('search_subject');
			$search_fields['name'] = set_value('search_name');
			$search_fields['message'] = set_value('search_message');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-templates'))) {

			foreach ($this->session->userdata('search-templates') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-templates', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("message_templates") . "`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['subject'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("message_templates") . "`.`subject` LIKE '%" . $this->db->escape_like_str($search_fields['subject']) . "%'";
			}

			if ($search_fields['message'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("message_templates") . "`.`message` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%'";
			}

		}

		// if bulk action
		if ($this->input->post('bulk')) {
			// load libraries
			$this->load->library('form_validation');

			// validate
			$this->form_validation->set_rules('action', 'Bulk Action', 'trim|xss_clean|required');

			// run validation
			$this->form_validation->run();

			// get messages
			$bulk_templates = $this->input->post('bulk_templates');
			if (!is_array($bulk_templates)) {
				$bulk_templates = array();
			}
			$bulk_templates = array_filter($bulk_templates);

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				if (count($bulk_templates) == 0) {
					$error = 'Please select at least one message';
				} else {
					$where = array(
						'staffID' => $this->auth->user->staffID
					);

					// run query
					$query = $this->db->from('message_templates')->where($where)->where_in('id', $bulk_templates)->get();

					// no match
					if ($query->num_rows() == 0) {
						$error = 'Please select at least one message';
					} else {
						// matches
						$actioned = 0;
						foreach ($query->result() as $template) {

							switch (set_value('action')) {
								case 'delete':
									$where = array(
										'staffID' => $this->auth->user->staffID,
										'id' => $template->id
									);
									$query = $this->db->delete('message_templates', $where, 1);
									$actioned += $this->db->affected_rows();
									break;
							}
						}
						if ($actioned == 1) {
							$this->session->set_flashdata('success', $actioned . ' template has been actioned successfully.');
						} else {
							$this->session->set_flashdata('success', $actioned . ' templates have been actioned successfully.');
						}

						redirect(current_url());
						exit();
					}
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}


		$templates = $this->db->from('message_templates')->where([
			'staffID' => $this->auth->user->staffID,
			'accountID' => $this->auth->user->accountID
		])->where($search_where, NULL, FALSE)->order_by('id desc')->get();

		$bulk_templates = [];

		// prepare data for view
		$data = array(
			'title'             => $title,
			'icon'              => $icon,
			'current_page'      => $current_page,
			'section'           => $section,
			'add_url'           => $add_url,
			'return_to'         => $return_to,
			'templates'         => $templates,
			'folder'            => $folder,
			'buttons'           => $buttons,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success'           => $success,
			'info'              => $info,
			'errors'            => $errors,
			'page_base'         => $page_base,
			'bulk_templates'    => $bulk_templates,
			'search_fields'     => $search_fields,
			'group'     		=> 'templates',
			'submit_to'         => current_url()
		);

		// load view
		$this->crm_view('messages/templates', $data);
	}

	/**
	 * delete a message
	 * @param  int $messageID
	 * @return mixed
	 */
	public function template_remove($templateID = NULL) {
		// check params
		if (empty($templateID)) {
			show_404();
		}

		$where = array(
			'id' => $templateID,
			'staffID' => $this->auth->user->staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('message_templates')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$template = $row;

			// all ok, delete
			$query = $this->db->delete('message_templates', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $template->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $template->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'messages/templates';

			redirect($redirect_to);
		}
	}

	/**
	 * check at least one
	 * @return bool
	 */
	public function at_least_one() {

		$to = $this->input->post('to');

		if (is_array($to) && count($to) > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function get_template($id) {
		$where = [
			'id' => $id,
			'staffID' => $this->auth->user->staffID,
			'accountID' => $this->auth->user->accountID
		];

		$query = $this->db->from('message_templates')->where($where)->get();

		if ($query->num_rows() < 1) {
			show_404();
		}

		$templateInfo = [];
		foreach ($query->result() as $row) {
			$templateInfo = $row;
		}

		$where = [
			'accountID' => $this->auth->user->accountID,
			'templateID' => $id
		];

		$query = $this->db->from('message_templates_attachments')->where($where)->get();

		$templateAttachments = [];
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$templateAttachments[] = $row;
			}
		}

		header("Content-type:application/json");
		echo json_encode([
			'template'      => $templateInfo,
			'attachments'    => $templateAttachments
		]);
	}

}

/* End of file messages.php */
/* Location: ./application/controllers/messages.php */
