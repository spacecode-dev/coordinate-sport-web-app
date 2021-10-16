<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Costs extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * show list of costs
	 * @return void
	 */
	public function index($bookingID = NULL) {

		if ($bookingID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$block_res = $this->db->from('bookings_blocks')->where($where)->get();

		if ($block_res->num_rows() == 0) {
			show_404();
		}

		$block_info = array();
		foreach ($block_res->result() as $row) {
			$block_info[] = $row->blockID;
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

		// set defaults
		$icon = 'sack-dollar';
		$tab = 'costs';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels= array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$page_base = 'bookings/costs/' . $bookingID;
		$section = 'bookings';
		$title = 'Costs';
		$buttons = '<a class="btn btn-success" href="' . site_url('bookings/costs/' . $bookingID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'bookings_costs.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'note' => NULL,
			'blocks' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_note', 'Note', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('blocks', 'Blocks', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['note'] = set_value('search_note');
			$search_fields['blocks'] = set_value('search_blocks');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings-costs'))) {

			foreach ($this->session->userdata('search-bookings-costs') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-bookings-costs', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`date` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['note'] != '') {
				$search_where[] = "`note` LIKE '%" . $this->db->escape_like_str($search_fields['note']) . "%'";
			}

			if ($search_fields['blocks'] != '') {
				$search_where[] = "`".$this->db->dbprefix('bookings_costs')."`.`blockID` = '" . $this->db->escape_like_str($search_fields['blocks']) . "'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('bookings_costs.*, bookings_blocks.name')->from('bookings_costs')->join('bookings_blocks','bookings_costs.blockID = bookings_blocks.blockID','inner')->where($where)->where_in($this->db->dbprefix('bookings_costs').'.blockID', $block_info)->where($search_where, NULL, FALSE)->order_by('date asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('bookings_costs.*, bookings_blocks.name')->from('bookings_costs')->join('bookings_blocks','bookings_costs.blockID = bookings_blocks.blockID','inner')->where($where)->where_in($this->db->dbprefix('bookings_costs').'.blockID', $block_info)->where($search_where, NULL, FALSE)->order_by('date asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'search_blocks' => $block_res,
			'page_base' => $page_base,
			'costs' => $res,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/costs', $data);
	}

	/**
	 * edit a cost
	 * @param  int $costID
	 * @param int $bookingID
	 * @return void
	 */
	public function edit($costID = NULL, $bookingID = NULL)
	{

		$cost_info = new stdClass();

		// check if editing
		if ($costID != NULL) {

			// check if numeric
			if (!ctype_digit($costID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'costID' => $costID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('bookings_costs')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$cost_info = $row;
				$blockID = $cost_info->blockID;
			}

		}

		// required
		if ($bookingID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$block_info = $this->db->from('bookings_blocks')->where($where)->get();

		if ($block_info->num_rows() == 0) {
			show_404();
		}

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

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Cost';
		if ($costID != NULL) {
			$submit_to = 'bookings/costs/edit/' . $costID.'/'.$bookingID;
			$title = mysql_to_uk_date($cost_info->date);
		} else {
			$submit_to = 'bookings/costs/' . $bookingID . '/new';
		}
		$return_to = 'bookings/costs/' . $bookingID;
		$icon = 'sack-dollar';
		$tab = 'costs';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels= array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$breadcrumb_levels['bookings/costs/' . $bookingID] = 'Costs';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('date', 'Date', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('note', 'Item', 'trim|xss_clean|required');
			$this->form_validation->set_rules('blockID', 'Block Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('category', 'Category', 'trim|xss_clean|required');
			$this->form_validation->set_rules('amount', 'Amount', 'trim|xss_clean|required|is_numeric|greater_than[0]');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'date' => uk_to_mysql_date(set_value('date')),
					'amount' => set_value('amount'),
					'note' => set_value('note'),
					'category' => set_value('category'),
					'blockID' => set_value('blockID'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($costID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($costID == NULL) {
						// insert
						$query = $this->db->insert('bookings_costs', $data);

					} else {
						$where = array(
							'costID' => $costID
						);

						// update
						$query = $this->db->update('bookings_costs', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($bookingID == NULL) {
							$this->session->set_flashdata('success', set_value('date') . ' Cost has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('date') . ' Cost has been updated successfully.');
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
			'cost_info' => $cost_info,
			'bookingID' => $bookingID,
			'block_info' => $block_info,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/cost', $data);
	}

	/**
	 * delete a cost
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function remove($costID = NULL, $bookingID = NULL) {

		// check params
		if (empty($costID) || empty($bookingID)) {
			show_404();
		}

		$where = array(
			'costID' => $costID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_costs')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$cost_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings_costs', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', mysql_to_uk_date($cost_info->date) . ' Cost has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', mysql_to_uk_date($cost_info->date) . ' Cost could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/costs/' . $bookingID;

			redirect($redirect_to);
		}
	}

	/**
	 * check date is correct
	 * @param  string $date
	 * @return bool
	 */
	public function check_date($date) {

		// date not required
		if (empty($date)) {
			return TRUE;
		}

		// if set, check
		if (check_uk_date($date)) {
			return TRUE;
		}

		return FALSE;

	}

}

/* End of file costs.php */
/* Location: ./application/controllers/bookings/costs.php */
