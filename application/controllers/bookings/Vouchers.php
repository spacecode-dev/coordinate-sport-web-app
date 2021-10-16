<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vouchers extends MY_Controller {

	private $bookingID;

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * show list of vouchers
	 * @return void
	 */
	public function index($bookingID = NULL) {

		if ($bookingID == NULL) {
			show_404();
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$res = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		if ($booking_info->type != 'event' && $booking_info->project != 1) {
			show_404();
		}

		// set defaults
		$icon = 'sack-dollar';
		$tab = 'vouchers';
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
		$page_base = 'bookings/vouchers/' . $bookingID;
		$section = 'bookings';
		$title = 'Vouchers';
		$buttons = '<a class="btn btn-success" href="' . site_url('bookings/vouchers/' . $bookingID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'code' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_code', 'Code', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['code'] = set_value('search_code');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings-vouchers'))) {

			foreach ($this->session->userdata('search-bookings-vouchers') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-bookings-vouchers', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['code'] != '') {
				$search_where[] = "`code` LIKE '%" . $this->db->escape_like_str($search_fields['code']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('bookings_vouchers')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('bookings_vouchers')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'vouchers' => $res,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/vouchers', $data);
	}

	/**
	 * edit a voucher
	 * @param  int $voucherID
	 * @param int $bookingID
	 * @return void
	 */
	public function edit($voucherID = NULL, $bookingID = NULL)
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
			$query = $this->db->from('bookings_vouchers')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$voucher_info = $row;
				$bookingID = $voucher_info->bookingID;
			}
		}

		// required
		if ($bookingID == NULL) {
			show_404();
		}

		// save booking ID
		$this->bookingID = $bookingID;

		// look up booking
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
		}

		if ($booking_info->type != 'event' && $booking_info->project != 1) {
			show_404();
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Voucher';
		if ($voucherID != NULL) {
			$submit_to = 'bookings/vouchers/edit/' . $voucherID;
			$title = $voucher_info->name;
		} else {
			$submit_to = 'bookings/vouchers/' . $bookingID . '/new/';
		}
		$return_to = 'bookings/vouchers/' . $bookingID;
		$icon = 'sack-dollar';
		$tab = 'vouchers';
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
		$breadcrumb_levels['bookings/vouchers/' . $bookingID] = 'Vouchers';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// get list of lessons
		$lesson_list = array();
		// get sessions within booking
		$where = array(
			'bookings_lessons.bookingID' => $bookingID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('bookings_lessons.*, bookings_blocks.name, bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd, activities.name as activity, lesson_types.name as type')->from('bookings_lessons')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->where($where)->order_by('bookings_blocks.startDate, bookings_blocks.endDate, bookings_blocks.blockID asc, day asc, startTime asc, endTime asc, lessonID asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_desc = ucwords($row->day) . " (" . substr($row->startTime, 0, 5) . " to " . substr($row->endTime, 0, 5) . ")";
				if (!empty($row->activity)) {
					$lesson_desc .= " - " . $row->activity;
				} else if (!empty($row->activity_other)) {
					$lesson_desc .= " - " . $row->activity_other;
				}
				if (!empty($row->type)) {
					$lesson_desc .= " - " . $row->type;
				} else if (!empty($row->type_other)) {
					$lesson_desc .= " - " . $row->type_other;
				}

				// get block name
				$block_desc = $row->name . ' - ' . mysql_to_uk_date($row->blockStart);
				if (strtotime($row->blockEnd) > strtotime($row->blockStart)) {
					$block_desc .= ' to ' . mysql_to_uk_date($row->blockEnd);
				}

				$lesson_list[$row->lessonID] = array(
					'block' => $block_desc,
					'name' => $lesson_desc
				);
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
			$this->form_validation->set_rules('lesson_list', 'Applies To', 'required|callback_at_least_one[lesson_list]');

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
					$data['bookingID'] = $bookingID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($voucherID == NULL) {
						// insert
						$query = $this->db->insert('bookings_vouchers', $data);

						$voucherID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'voucherID' => $voucherID
						);

						// update
						$query = $this->db->update('bookings_vouchers', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// add/update org attachments
							$lesson_list_posted = $this->input->post('lesson_list');
							if (!is_array($lesson_list_posted)) {
								$lesson_list_posted = array();
							}
							foreach ($lesson_list as $lessonID => $lesson) {
								$where = array(
									'voucherID' => $voucherID,
									'lessonID' => $lessonID,
									'accountID' => $this->auth->user->accountID
								);
								if (!in_array($lessonID, $lesson_list_posted)) {
									// not set, remove
									$this->db->delete('bookings_lessons_vouchers', $where);
								} else {
									// look up, see if site record already exists
									$res = $this->db->from('bookings_lessons_vouchers')->where($where)->get();

									$data = array(
										'voucherID' => $voucherID,
										'lessonID' => $lessonID,
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'accountID' => $this->auth->user->accountID
									);

									if ($res->num_rows() > 0) {
										$this->db->update('bookings_lessons_vouchers', $data, $where);
									} else {
										$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
										$this->db->insert('bookings_lessons_vouchers', $data);
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

		// lessons
		$lesson_list_array = array();
		$where = array(
			'voucherID' => $voucherID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_lessons_vouchers')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_list_array[] = $row->lessonID;
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'voucher_info' => $voucher_info,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'lesson_list' => $lesson_list,
			'lesson_list_array' => $lesson_list_array,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/voucher', $data);
	}

	/**
	 * delete a voucher
	 * @param  int $bookingID
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
		$query = $this->db->from('bookings_vouchers')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$voucher_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings_vouchers', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $voucher_info->name . ' Voucher has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $voucher_info->name . ' Voucher could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/vouchers/' . $voucher_info->bookingID;

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
		$query = $this->db->from('bookings_vouchers')->where($where)->limit(1)->get();

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

			$query = $this->db->update('bookings_vouchers', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $voucher_info->name . ' has been marked as active.');
			} else {
				$this->session->set_flashdata('error', $voucher_info->name . ' could not be marked as active.');
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/vouchers/' . $voucher_info->bookingID;

			redirect($redirect_to);
		}
	}

	/**
	 * unactivate a voucher
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
		$query = $this->db->from('bookings_vouchers')->where($where)->limit(1)->get();

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

			$query = $this->db->update('bookings_vouchers', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $voucher_info->name . ' has been marked as inactive.');
			} else {
				$this->session->set_flashdata('error', $voucher_info->name . ' could not be marked as inactive.');
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/vouchers/' . $voucher_info->bookingID;

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
			'bookingID' => $this->bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// if editing, exclude current
		if (!empty($voucherID)) {
			$where['voucherID !='] = $voucherID;
		}

		$res = $this->db->from('bookings_vouchers')->where($where)->get();

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
/* Location: ./application/controllers/bookings/vouchers.php */
