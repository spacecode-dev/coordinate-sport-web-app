<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vouchers extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
	}

	/**
	 * show list of vouchers
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'sack-dollar';
		$current_page = 'vouchers';
		$page_base = 'settings/vouchers';
		$section = 'settings';
		$title = 'Vouchers';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/vouchers/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings'
		);

		// set where
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'code' => NULL,
			'inactive' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_code', 'Code', 'trim|xss_clean');
			$this->form_validation->set_rules('search_inactive', 'Show Inactive', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['code'] = set_value('search_code');
			$search_fields['inactive'] = set_value('search_inactive');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-vouchers'))) {

			foreach ($this->session->userdata('search-vouchers') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-vouchers', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['code'] != '') {
				$search_where[] = "`code` LIKE '%" . $this->db->escape_like_str($search_fields['code']) . "%'";
			}

			if ($search_fields['inactive'] == 1) {
				unset($where['active']);
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('vouchers')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('vouchers')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'page_base' => $page_base,
			'vouchers' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/vouchers', $data);
	}

	/**
	 * edit a voucher
	 * @param  int $voucherID
	 * @return void
	 */
	public function edit($voucherID = NULL)
	{

		$voucher_info = new stdClass();

		// check if editing
		if ($voucherID != NULL) {

			// check if numeric
			if (!ctype_digit($voucherID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'voucherID' => $voucherID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('vouchers')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$voucher_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Voucher';
		if ($voucherID != NULL) {
			$submit_to = 'settings/vouchers/edit/' . $voucherID;
			$title = $voucher_info->name;
		} else {
			$submit_to = 'settings/vouchers/new/';
		}
		$return_to = 'settings/vouchers';
		$icon = 'sack-dollar';
		$current_page = 'vouchers';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/vouchers' => 'Vouchers'
		);

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

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('code', 'Code', 'trim|xss_clean|required|callback_format_code|callback_check_voucher_code[' . $voucherID . ']');
			$this->form_validation->set_rules('discount_type', 'Discount Type', 'trim|xss_clean|required');
			if ($this->input->post('discount_type') == 'percentage') {
				$this->form_validation->set_rules('discount', 'Discount', 'trim|xss_clean|required|numeric|greater_than[0]|less_than[101]');
			} else {
				$this->form_validation->set_rules('discount', 'Discount', 'trim|xss_clean|required|numeric|greater_than[0]|less_than[10000]');
			}
			$this->form_validation->set_rules('comment', 'Comment', 'trim|xss_clean');
			$this->form_validation->set_rules('siblingdiscount', 'Sibling Discount', 'trim|xss_clean|intval');
			$this->form_validation->set_rules('lesson_types', 'Applies To', 'required|callback_at_least_one[lesson_types]');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'code' => set_value('code'),
					'discount_type' => set_value('discount_type'),
					'discount' => set_value('discount'),
					'comment' => set_value('comment'),
					'siblingdiscount' => set_value('siblingdiscount'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($voucherID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($voucherID == NULL) {
						// insert
						$query = $this->db->insert('vouchers', $data);

						$voucherID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'voucherID' => $voucherID
						);

						// update
						$query = $this->db->update('vouchers', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// add/update session types
						$lesson_types_posted = $this->input->post('lesson_types');
						if (!is_array($lesson_types_posted)) {
							$lesson_types_posted = array();
						}
						foreach ($lesson_types as $typeID => $type) {
							$where = array(
								'voucherID' => $voucherID,
								'typeID' => $typeID,
								'accountID' => $this->auth->user->accountID
							);
							if (!in_array($typeID, $lesson_types_posted)) {
								// not set, remove
								$this->db->delete('vouchers_lesson_types', $where);
							} else {
								// look up, see if already exists
								$res = $this->db->from('vouchers_lesson_types')->where($where)->get();

								$data = array(
									'voucherID' => $voucherID,
									'typeID' => $typeID,
									'accountID' => $this->auth->user->accountID,
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);

								if ($res->num_rows() > 0) {
									$this->db->update('vouchers_lesson_types', $data, $where);
								} else {
									$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
									$this->db->insert('vouchers_lesson_types', $data);
								}
							}
						}

						if (isset($just_added)) {
							$this->session->set_flashdata('success', set_value('name') . ' Voucher has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('name') . ' Voucher has been updated successfully.');
						}

						redirect($return_to);

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

		// session types
		$lesson_types_array = array();
		if ($voucherID != NULL) {
			$where = array(
				'voucherID' => $voucherID,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('vouchers_lesson_types')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$lesson_types_array[] = $row->typeID;
				}
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
			'voucher_info' => $voucher_info,
			'lesson_types' => $lesson_types,
			'success' => $success,
			'errors' => $errors,
			'breadcrumb_levels' => $breadcrumb_levels,
			'info' => $info,
			'lesson_types' => $lesson_types,
			'lesson_types_array' => $lesson_types_array
		);

		// load view
		$this->crm_view('settings/voucher', $data);
	}

	/**
	 * delete a voucher
	 * @param  int $voucherID
	 * @return mixed
	 */
	public function remove($voucherID = NULL) {

		// check params
		if (empty($voucherID)) {
			show_404();
		}

		$where = array(
			'voucherID' => $voucherID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('vouchers')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$voucher_info = $row;

			// all ok, delete
			$query = $this->db->delete('vouchers', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $voucher_info->name . ' Voucher has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $voucher_info->name . ' Voucher could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/vouchers';

			redirect($redirect_to);
		}
	}

	/**
	 * activate a voucher
	 * @param  int $voucherID
	 * @return mixed
	 */
	public function activate($voucherID = NULL) {

		// check params
		if (empty($voucherID)) {
			show_404();
		}

		$where = array(
			'voucherID' => $voucherID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('vouchers')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$voucher_info = $row;

			// all ok, update
			$data= array(
				'active' => 1,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('vouchers', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $voucher_info->name . ' has been marked as active.');
			} else {
				$this->session->set_flashdata('error', $voucher_info->name . ' could not be marked as active.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/vouchers';

			redirect($redirect_to);
		}
	}

	/**
	 * deactivate a voucher
	 * @param  int $voucherID
	 * @return mixed
	 */
	public function deactivate($voucherID = NULL) {

		// check params
		if (empty($voucherID)) {
			show_404();
		}

		$where = array(
			'voucherID' => $voucherID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('vouchers')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$voucher_info = $row;

			// all ok, update
			$data= array(
				'active' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('vouchers', $data, $where);

			echo $this->db->last_query();
			exit();

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $voucher_info->name . ' has been marked as inactive.');
			} else {
				$this->session->set_flashdata('error', $voucher_info->name . ' could not be marked as inactive.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/vouchers';

			redirect($redirect_to);
		}
	}

	/**
	 * format a code
	 * @param  string $code
	 * @return string
	 */
	public function format_code($code = NULL) {
		return strtoupper(preg_replace("/[^A-Za-z0-9]/", '', $code));
	}


	/**
	 * check voucher code
	 * @param  string $code
	 * @param  string $voucherID
	 * @return bool
	 */
	public function check_voucher_code($code = NULL, $voucherID = NULL) {

		// check parameters
		if (empty($code)) {
			return FALSE;
		}

		$where = array(
			'code' => $code,
			'accountID' => $this->auth->user->accountID
		);

		// if editing, exclude current
		if (!empty($voucherID)) {
			$where['voucherID !='] = $voucherID;
		}

		$res = $this->db->from('vouchers')->where($where)->get();

		if ($res->num_rows() == 0) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * check at one in array chosen
	 * @return bool
	 */
	public function at_least_one($value, $field) {

		$values = $this->input->post($field);

		if (is_array($values) && count($values) > 0) {
			return TRUE;
		}
		return FALSE;
	}

}

/* End of file vouchers.php */
/* Location: ./application/controllers/settings/vouchers.php */
