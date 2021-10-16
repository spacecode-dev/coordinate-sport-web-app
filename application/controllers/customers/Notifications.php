<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notifications extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any customer types
		if (!$this->auth->has_features('customers_schools') && !$this->auth->has_features('customers_schools_prospects') && !$this->auth->has_features('customers_orgs') && !$this->auth->has_features('customers_orgs_prospects')) {
			show_403();
		}
	}

	/**
	 * show list of notifications
	 * @return void
	 */
	public function index($org_id = NULL) {

		if ($org_id == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $org_id,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'bell';
		$tab = 'notifications';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $org_id] = $org_info->name;
		$page_base = 'customers/notifications/' . $org_id;
		$section = 'customers';
		$title = 'Notifications';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'orgs_notifications.orgID' => $org_id,
			'orgs_notifications.accountID' => $this->auth->user->accountID
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

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-customer-notifications'))) {

			foreach ($this->session->userdata('search-customer-notifications') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-customer-notifications', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("orgs_notifications") . "`.`added` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("orgs_notifications") . "`.`added` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['contact_id'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("orgs_notifications") . "`.`contactID` = " . $this->db->escape($search_fields['contact_id']);
			}

			if ($search_fields['message'] != '') {
				$search_where[] = "(`" . $this->db->dbprefix("orgs_notifications") . "`.`contentText` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%' OR `" . $this->db->dbprefix("orgs_notifications") . "`.`contentHTML` LIKE '%" . $this->db->escape_like_str($search_fields['message']) . "%')";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('orgs_notifications.*, orgs_contacts.name as contact')->from('orgs_notifications')->join('orgs_contacts', 'orgs_notifications.contactID = orgs_contacts.contactID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('orgs_notifications.added desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('orgs_notifications.*, orgs_contacts.name as contact')->from('orgs_notifications')->join('orgs_contacts', 'orgs_notifications.contactID = orgs_contacts.contactID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('orgs_notifications.added desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$where = array(
			'orgID' => $org_id,
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$contacts = $this->db->from('orgs_contacts')->where($where)->order_by('name ASC')->get();

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
			'org_id' => $org_id,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/notifications', $data);
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
			'orgs_notifications.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('orgs_notifications.*, orgs_contacts.name as contact, CONCAT(' . $this->db->dbprefix('staff') . ".first, ' ', " . $this->db->dbprefix('staff') . ".surname) as staff", FALSE)->from('orgs_notifications')->join('orgs_contacts', 'orgs_notifications.contactID = orgs_contacts.contactID', 'left')->join('staff', 'orgs_notifications.byID = staff.staffID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$notification_info = $row;
			$orgID = $notification_info->orgID;
		}

		// required
		if ($orgID == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs')->where($where)->limit(1)->get();

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
		$return_to = 'customers/notifications/' . $orgID;
		$icon = 'bell';
		$tab = 'notifications';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$breadcrumb_levels['customers/notifications/' . $orgID] = 'Notifications';
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// get attachments
		$attachments = array();

		// notification attachments
		$where = array(
			'notificationID' => $notificationID,
			'accountID' => $this->auth->user->accountID
		);
		$attachments_resource = $this->db->from('orgs_notifications_attachments')->where($where)->order_by('name asc')->get();

		if ($attachments_resource->num_rows() > 0) {
			foreach ($attachments_resource->result() as $row) {
				$attachments['attachment/customernotification/' . $row->path] = $row->name;
			}
		}

		// customer attachments
		$where = array(
			'notificationID' => $notificationID,
			'orgs_attachments.accountID' => $this->auth->user->accountID
		);
		$attachments_customer = $this->db->select('orgs_attachments.*')->from('orgs_notifications_attachments_customers')->join('orgs_attachments', 'orgs_notifications_attachments_customers.attachmentID = orgs_attachments.attachmentID', 'inner')->where($where)->order_by('orgs_attachments.name asc')->get();

		if ($attachments_customer->num_rows() > 0) {
			foreach ($attachments_customer->result() as $row) {
				$attachments['attachment/customer/' . $row->path] = $row->name;
			}
		}

		// booking attachments
		$where = array(
			'notificationID' => $notificationID,
			'bookings_attachments.accountID' => $this->auth->user->accountID
		);
		$attachments_bookings = $this->db->select('bookings_attachments.*')->from('orgs_notifications_attachments_bookings')->join('bookings_attachments', 'orgs_notifications_attachments_bookings.attachmentID = bookings_attachments.attachmentID', 'inner')->where($where)->order_by('bookings_attachments.name asc')->get();

		if ($attachments_bookings->num_rows() > 0) {
			foreach ($attachments_bookings->result() as $row) {
				$attachments['attachment/event/' . $row->path] = $row->name;
			}
		}

		// resource attachments
		$where = array(
			'notificationID' => $notificationID,
			'files.accountID' => $this->auth->user->accountID
		);
		$attachments_resource = $this->db->select('files.*')->from('orgs_notifications_attachments_resources')->join('files', 'orgs_notifications_attachments_resources.attachmentID = files.attachmentID', 'inner')->where($where)->order_by('files.name asc')->get();

		if ($attachments_resource->num_rows() > 0) {
			foreach ($attachments_resource->result() as $row) {
				$attachments['attachment/files/' . $row->path] = $row->name;
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
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'return_to' => $return_to,
			'notification_info' => $notification_info,
			'attachments' => $attachments,
			'org_id' => $orgID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/notification', $data);
	}

}

/* End of file notifications.php */
/* Location: ./application/controllers/customers/notifications.php */
