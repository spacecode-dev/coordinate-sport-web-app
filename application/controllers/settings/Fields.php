<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fields extends MY_Controller {

	public function __construct() {
		// directors only
		parent::__construct(FALSE, array(), array('directors'));
	}

	/**
	 * show list of fields
	 * @return void
	 */
	public function index($type = 'staff') {

		//validate
		if ($this->auth->user->department != 'directors') {
			show_403();
		}

		// set defaults
		$icon = 'tv';
		$section = 'settings';
		$current_page = 'fields_' . $type;
		$title = $type;
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$redirect_to = 'settings/fields/' . $type;
		$url = "settings/fields/display/participants";
		if($type == "staff" || $type == "staff_recruitment" ){
			$url = "settings/fields/display/staff";
		}
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			$url => 'Display'
		);

		// override titles
		$titles = [
			'staff' => 'Staff Personal',
			'staff_recruitment' => 'Staff Recruitment',
			'account_holder' => 'Account Holder Profile',
			'participant' => 'Participant Profile'
		];

		// switch type for prettier titles
		if (array_key_exists($title, $titles)) {
			$title = $titles[$title];
		} else {
			$title = ucwords($title);
		}

		// set where
		$where = array(
			'settings_fields.section' => $type
		);
		
		$mileage_section = 0;
		$mileage_account = $this->db->select("*")->from("accounts")->where("accountID", $this->auth->user->accountID)->get();
		foreach($mileage_account->result() as $result){
			$mileage_section = $result->addon_mileage;
		}

		// run query
		$fields = $this->db->select('settings_fields.*, accounts_fields.show as account_show, accounts_fields.required as account_required')->from('settings_fields')->join('accounts_fields', 'settings_fields.section = accounts_fields.section and settings_fields.field = accounts_fields.field and accounts_fields.accountID = ' . $this->auth->user->accountID, 'left')->where($where)->order_by('settings_fields.section asc, settings_fields.order asc, settings_fields.field asc')->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// if posted
		if ($this->input->post()) {
			if ($fields->num_rows() > 0) {
				foreach ($fields->result() as $field) {
					// all ok

					// if locked, skip
					if ($field->locked == 1) {
						continue;
					}

					// prepare data
					$data = array(
						'show' => 0,
						'required' => 0,
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);

					if ($this->input->post('show[' . $field->field . ']') == 1) {
						$data['show'] = 1;
					}

					if ($this->input->post('required[' . $field->field . ']') == 1 && $field->required != 2) {
						$data['required'] = 1;
					}

					$where = array(
						'section' => $field->section,
						'field' => $field->field,
						'accountID' => $this->auth->user->accountID
					);

					// check if exists
					$res = $this->db->from('accounts_fields')->where($where)->get();

					if ($res->num_rows() > 0) {
						// update
						$query = $this->db->update('accounts_fields', $data, $where);
					} else {
						// insert
						$data['accountID'] = $this->auth->user->accountID;
						$data['section'] = $field->section;
						$data['field'] = $field->field;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$query = $this->db->insert('accounts_fields', $data);
					}
				}
			}

			$this->session->set_flashdata('success', $title . ' field settings have been updated successfully');

			redirect('settings/fields/' . $type);

			return TRUE;

		}

		// prepare data for view
		$data = array(
			'title' => $title . ' Fields',
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'type' => $type,
			'buttons' => $buttons,
			'fields' => $fields,
			'breadcrumb_levels' => $breadcrumb_levels,
			'mileage_section' => $mileage_section,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/fields', $data);
	}

	/**
	 * show list of fields
	 * @return void
	 */
	public function display($type="staff"){

		//validate
		if ($this->auth->user->department != 'directors') {
			show_403();
		}

		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
		);

		// prepare data for view
		$data = array(
			'title' => 'Display',
			'breadcrumb_levels' => $breadcrumb_levels,
			'tab' => $type
		);

		// load view
		$this->crm_view('settings/fields_display', $data);
	}
}

/* End of file settings.php */
/* Location: ./application/controllers/settings.php */
