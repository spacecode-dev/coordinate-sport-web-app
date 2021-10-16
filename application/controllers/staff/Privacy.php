<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Privacy extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach + office
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach', 'office'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
	}

	/**
	 * show list of privacy logs
	 * @return void
	 */
	public function index($staffID = NULL) {

		if ($staffID == NULL) {
			show_404();
		}

		// look up staff
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('staff')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$staff_info = $row;
		}

		// set defaults
		$icon = 'eye';
		$tab = 'privacy';
		$current_page = 'staff';
		$page_base = 'staff/privacy/' . $staffID;
		$section = 'staff';
		$title = $staff_info->first . ' ' . $staff_info->surname;
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname
 		);

		// set where
		$where = array(
			'staff_notes.staffID' => $staffID,
			'staff_notes.accountID' => $this->auth->user->accountID,
			'staff_notes.type' => 'privacy'
		);

		// run query
		$res = $this->db->from('staff_notes')->where($where)->order_by('added desc')->get();

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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'logs' => $res,
			'staffID' => $staffID,
			'staff_info' => $staff_info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/privacy', $data);
	}
}

/* End of file privacy.php */
/* Location: ./application/controllers/staff/privacy.php */