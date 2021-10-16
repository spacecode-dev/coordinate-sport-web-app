<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Availabilitycals extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings', 'availability_cals'));
	}

	/**
	 * show list of calendars
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'calendar-alt';
		$current_page = 'availabilitycals';
		$page_base = 'settings/availabilitycals';
		$section = 'settings';
		$title = 'Availability Calendars';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/availabilitycals/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
		);

		// set where
		$where = array(
			'availability_cals.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-availabilitycals'))) {

			foreach ($this->session->userdata('search-availabilitycals') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-availabilitycals', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = $this->db->dbprefix('availability_cals') . ".`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('availability_cals')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('availability_cals.*, brands.name as brand, brands.colour, GROUP_CONCAT(' . $this->db->dbprefix('activities') . '.name ORDER BY ' . $this->db->dbprefix('activities') . '.name ASC SEPARATOR ", ") as activities')->from('availability_cals')->join('brands', 'availability_cals.brandID = brands.brandID', 'left')->join('availability_cals_activities', 'availability_cals_activities.calID = availability_cals.calID', 'left')->join('activities', 'activities.activityID = availability_cals_activities.activityID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('availability_cals.name asc')->group_by('availability_cals.calID')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'availability_cals' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/availabilitycals', $data);
	}

	/**
	 * edit a calendar
	 * @param  int $calID
	 * @return void
	 */
	public function edit($calID = NULL)
	{

		$cal_info = new stdClass();

		// check if editing
		if ($calID != NULL) {

			// check if numeric
			if (!ctype_digit($calID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'calID' => $calID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('availability_cals')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$cal_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Availability Calendar';
		if ($calID != NULL) {
			$submit_to = 'settings/availabilitycals/edit/' . $calID;
			$title = $cal_info->name;
		} else {
			$submit_to = 'settings/availabilitycals/new/';
		}
		$return_to = 'settings/availabilitycals';
		$icon = 'calendar-alt';
		$current_page = 'availabilitycals';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/availabilitycals' => 'Availability Calendars'
		);

		// get list of activities
		$activities = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('activities')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$activities[$row->activityID] = [
					'name' => $row->name,
					'active' => $row->active
				];
			}
		}

		// slots
		$slots = array();

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('brandID', $this->settings_library->get_label('brand'), 'trim|xss_clean|required');

			$slots = $this->input->post('slots');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// check at least one activity
				if (!$this->input->post('activities')) {
					$errors[] = 'At least one activity is required';
				}

				// check slots for errors
				$posted_slots = $this->input->post('slots');
				if (!is_array($posted_slots) || count($posted_slots) == 0) {
					$errors[] = 'At least one slot is required';
				} else {
					$slot_errors = array();
					$slot_ranges = array();
					foreach ($posted_slots as $slot) {
						$start_float = floatval($slot['startTimeH'] . '.' . $slot['startTimeM']);
						$end_float = floatval($slot['endTimeH'] . '.' . $slot['endTimeM']);
						$slot_ranges[] = array(
							'start' => $start_float,
							'end' => $end_float
						);
						// check for valid fields
						if (!isset($slot['name'], $slot['startTimeH'], $slot['startTimeM'], $slot['endTimeH'], $slot['endTimeM']) || empty($slot['name']) || !is_numeric($slot['startTimeH']) || !is_numeric($slot['startTimeM']) || !is_numeric($slot['endTimeH']) || !is_numeric($slot['endTimeM'])) {
							$slot_errors['missing_data'] = 'At least one slot with missing/invalid data';
						} else if ($start_float >= $end_float) {
							$slot_errors['end_before_start'] = 'At least one slot has an end time before start time';
						}
					}

					// prdered slot ranges in order
					$slot_ranges = array_orderby($slot_ranges, 'start', SORT_ASC, 'end', SORT_ASC);
					$start = 0;
					$end = 0;
					foreach ($slot_ranges as $range) {
						if (($start < $range['end']) && ($range['start'] < $end)) {
							$slot_errors['overlap'] = 'Slots should not overlap';
						}
						$start = $range['start'];
						$end = $range['end'];
					}

					if (count($slot_errors) > 0) {
						$errors = array_merge($errors, $slot_errors);
					}
				}

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'brandID' => set_value('brandID'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($calID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($calID == NULL) {
						// insert
						$query = $this->db->insert('availability_cals', $data);

						$calID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'calID' => $calID
						);

						// update
						$query = $this->db->update('availability_cals', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// add/update staff activities
						$activities_posted = $this->input->post('activities');
						if (!is_array($activities_posted)) {
							$activities_posted = array();
						}
						foreach ($activities as $activityID => $activity) {
							$where = array(
								'calID' => $calID,
								'activityID' => $activityID,
								'accountID' => $this->auth->user->accountID
							);
							if (!in_array($activityID, $activities_posted)) {
								// not set, remove
								$this->db->delete('availability_cals_activities', $where);
							} else {
								// look up, see if already exists
								$res = $this->db->from('availability_cals_activities')->where($where)->get();

								$data = array(
									'calID' => $calID,
									'activityID' => $activityID,
									'accountID' => $this->auth->user->accountID,
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);

								if ($res->num_rows() > 0) {
									$this->db->update('availability_cals_activities', $data, $where);
								} else {
									$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
									$this->db->insert('availability_cals_activities', $data);
								}
							}
						}

						// delete existing slots
						$where = array(
							'calID' => $calID,
							'accountID' => $this->auth->user->accountID
						);
						$res = $this->db->delete('availability_cals_slots', $where);

						// add new
						if (count($slots) > 0) {
							foreach ($slots as $slot) {
								$data = array(
									'calID' => $calID,
									'accountID' => $this->auth->user->accountID,
									'name' => $slot['name'],
									'startTime' => $slot['startTimeH'] . ':' . $slot['startTimeM'],
									'endTime' => $slot['endTimeH'] . ':' . $slot['endTimeM'],
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
								$this->db->insert('availability_cals_slots', $data);
							}
						}

						if (isset($just_added)) {
							$this->session->set_flashdata('success', set_value('name') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
						}

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		} else {
			if ($calID != NULL) {
				// get exisitng slots
				$where = array(
					'calID' => $calID,
					'accountID' => $this->auth->user->accountID
				);

				// run query
				$query = $this->db->from('availability_cals_slots')->where($where)->order_by('startTime asc, endTime asc, name asc')->get();

				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$slots[] = array(
							'name' => $row->name,
							'startTimeH' => substr($row->startTime, 0, 2),
							'startTimeM' => substr($row->startTime, 3, 2),
							'endTimeH' => substr($row->endTime, 0, 2),
							'endTimeM' => substr($row->endTime, 3, 2)
						);
					}
				}
			}
		}

		// if no slots, add one
		if (count($slots) == 0) {
			$slots[] = array(
				'name' => NULL,
				'startTimeH' => 6,
				'startTimeM' => 0,
				'endTimeH' => 7,
				'endTimeM' => 0
			);
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_where = [
			'`active` = 1'
		];
		if ($calID != NULL) {
			$or_where[] = '`brandID` = ' . $this->db->escape($cal_info->brandID);
		}
		$where['(' . implode(' OR ', $or_where) . ')'] = NULL;
		$brands = $this->db->from('brands')->where($where, NULL, FALSE)->order_by('name asc')->get();

		// cal activities
		$activities_array = array();
		$where = array(
			'calID' => $calID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('availability_cals_activities')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$activities_array[] = $row->activityID;
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
			'cal_info' => $cal_info,
			'brands' => $brands,
			'activities' => $activities,
			'activities_array' => $activities_array,
			'slots' => $slots,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/availabilitycal', $data);
	}

	/**
	 * delete a calendar
	 * @param  int $calID
	 * @return mixed
	 */
	public function remove($calID = NULL) {

		// check params
		if (empty($calID)) {
			show_404();
		}

		$where = array(
			'calID' => $calID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('availability_cals')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$cal_info = $row;

			// all ok, delete
			$query = $this->db->delete('availability_cals', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $cal_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $cal_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/availabilitycals';

			redirect($redirect_to);
		}
	}
}

/* End of file availabilitycals.php */
/* Location: ./application/controllers/settings/availabilitycals.php */
