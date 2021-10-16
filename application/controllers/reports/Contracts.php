<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contracts extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports', 'contracts'));
		$this->load->library('reports_library');
		$this->load->library('orgs_library');
		$this->load->library('staff_library');
	}

	public function index($action = FALSE) {

		// set defaults
		$icon = 'book';
		$current_page = 'contracts';
		$section = 'reports';
		$page_base = 'reports/contracts';
		$title = 'Projects & Contracts Report';
		$buttons = ' <a class="btn btn-primary" href="' . site_url('reports/contracts/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$export = FALSE;
		$period = 'quarter';

		$export = FALSE;

		if ($action == 'export') {
			$export = TRUE;
		}

		$search_fields = [
			'search_academic_year' => date('Y'),
			'date_from' => NULL,
			'date_to' => NULL,
			'search_by' => 'dates_period',
			'search_booking_type' => NULL,
			'search_orgs' => NULL,
			'search_staff' => NULL,
			'search_session_types' => NULL,
			'search_activity' => NULL,
			'search_department' => NULL,
			'show_blocks' => false,
			'search' => NULL
		];

		$search_where = [];

		$is_search = FALSE;

		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_academic_year', 'Academic Year', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_by', 'Search By', 'trim|xss_clean');
			$this->form_validation->set_rules('search_booking_type', 'Search Booking Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_orgs', 'Search Customer', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff', 'Search Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_session_types', 'Search Session Types', 'trim|xss_clean');
			$this->form_validation->set_rules('search_activity', 'Search Session Types', 'trim|xss_clean');
			$this->form_validation->set_rules('search_department', 'Search Session Types', 'trim|xss_clean');
			$this->form_validation->set_rules('show_blocks', 'Show Blocks', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['search_academic_year'] = set_value('search_academic_year');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['search_by'] = set_value('search_by');
			$search_fields['search_booking_type'] = set_value('search_booking_type');
			$search_fields['search_orgs'] = set_value('search_orgs');
			$search_fields['search_staff'] = set_value('search_staff');
			$search_fields['search_session_types'] = set_value('search_session_types');
			$search_fields['search_activity'] = set_value('search_activity');
			$search_fields['search_department'] = set_value('search_department');
			$search_fields['show_blocks'] = set_value('show_blocks');

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			$is_search = TRUE;
		} else if ($export == TRUE && is_array($this->session->userdata('search-reports-contracts'))) {
			foreach ($this->session->userdata('search-reports-contracts') as $key => $value) {
				$search_fields[$key] = $value;
			}
		}

		$search_where = [];

		// calc offset
		switch ($period) {
			case 'week':
			default:
				$offset = '-1 week';
				break;
			case 'month':
				$offset = '-1 month';
				break;
			case 'quarter':
				$offset = '-3 months';
				break;
		}
		// if dates empty, add default
		if (empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime($offset));
		}
		if (empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		switch ($search_fields['search_by']) {
			case 'dates_period':
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				$date_to = uk_to_mysql_date($search_fields['date_to']);

				if ($search_fields['show_blocks']) {
					$search_where[] = $this->db->dbprefix('bookings_blocks').".`startDate` <= '" . $date_to . "'";
					$search_where[] = $this->db->dbprefix('bookings_blocks').".`endDate` >= '" . $date_from . "'";
				} else {
					$search_where[] = $this->db->dbprefix('bookings').".`startDate` <= '" . $date_to . "'";
					$search_where[] = $this->db->dbprefix('bookings').".`endDate` >= '" . $date_from . "'";
				}
				break;
			default:
				if ($search_fields['search_academic_year'] != '') {
					$start_date = date('Y-m-d', strtotime('first day of September ' . $search_fields['search_academic_year']));
					$end_date = date('Y-m-d', strtotime('last day of August ' . ($search_fields['search_academic_year'] + 1)));

					if ($search_fields['show_blocks']) {
						$search_where[] = $this->db->dbprefix('bookings_blocks').".`startDate` <= '" . $end_date . "'";
						$search_where[] = $this->db->dbprefix('bookings_blocks').".`endDate` >= '" . $start_date . "'";
					} else {
						$search_where[] = $this->db->dbprefix('bookings').".`startDate` <= '" . $end_date . "'";
						$search_where[] = $this->db->dbprefix('bookings').".`endDate` >= '" . $start_date . "'";
					}

					$date_from = $start_date;
					$date_to = $end_date;
				}
				break;
		}

		if (!empty($search_fields['search_booking_type'])) {
			switch ($search_fields['search_booking_type']) {
				case 'projects':
					$search_where[] = $this->db->dbprefix('bookings').".`project` = 1";
					break;
				default:
					$search_where[] = $this->db->dbprefix('bookings').".`project` <> 1";
					break;
			}
		}

		if ($is_search) {
			$this->session->set_userdata('search-reports-contracts', $search_fields);
		}

		$where = array(
			'bookings.accountID' => $this->auth->user->accountID,
		);

		if (!empty($search_fields['search_orgs'])) {
			if ($search_fields['show_blocks']) {
				$search_where[] = '(`' . $this->db->dbprefix("orgs") . "`.`orgID` = " . $this->db->escape_like_str($search_fields['search_orgs']) . " OR `block_orgs`.`orgID` = " . $this->db->escape_like_str($search_fields['search_orgs']) . ")";
			} else {
				$where['bookings.orgID'] = $search_fields['search_orgs'];
			}
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		if (!empty($search_fields['search_staff'])) {
			$where['bookings_lessons_staff.staffID'] = $search_fields['search_staff'];
		}

		if (!empty($search_fields['search_session_types'])) {
			$where['bookings_lessons.typeID'] = $search_fields['search_session_types'];
		}

		if (!empty($search_fields['search_activity'])) {
			$where['bookings_lessons.activityID'] = $search_fields['search_activity'];
		}

		if (!empty($search_fields['search_department'])) {
			$where['bookings.brandID'] = $search_fields['search_department'];
		}

		$res = $this->reports_library->get_contracts_data($where, $search_where);

		$bookingIDs = [];
		$income = [];
		$costs = [];
		$total_profit = [];
		$customerHours = [];
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// if filter dates are less, adjust
				$row->startDate_filter = $row->startDate;
				if (isset($date_from) && strtotime($date_from) > strtotime($row->startDate)) {
					$row->startDate_filter = $date_from;
				}
				$row->endDate_filter = $row->endDate;
				if (isset($date_to) && strtotime($date_to) < strtotime($row->endDate)) {
					$row->endDate_filter = $date_to;
				}
				$row->startDate = mysql_to_uk_date($row->startDate);
				$row->endDate = mysql_to_uk_date($row->endDate);
				$bookingIDs[] = $row->bookingID;
				$income[$row->bookingID] = $this->crm_library->count_income($row);
				$costs[$row->bookingID] = $this->crm_library->count_profit_costs($row);
				$total_profit[$row->bookingID] = $income[$row->bookingID]['total'] - $costs[$row->bookingID]['total'];

				foreach ($costs[$row->bookingID]['customer_hours'] as $customer => $hours) {
					isset($customerHours[$customer]) ? $customerHours[$customer] += $hours : $customerHours[$customer] = $hours;
				}
			}
		}

		$booking_data = [];
		if (count($bookingIDs) > 0) {
			// get types and activties
			$res_types = $this->db
				->select('bookings_lessons.bookingID, bookings_lessons.blockID, bookings_blocks.name as block_name,
				orgs.name as org_name, orgs.orgID as block_org_id, bookings.orgID as booking_org_id,
				bookings_blocks.startDate, bookings_blocks.endDate,
				GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('activities') . '.name SEPARATOR \', \') AS activities,
				GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('bookings_lessons') . '.activity_other SEPARATOR \', \') AS activities_other,
				GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('bookings_lessons') . '.type_other SEPARATOR \', \') AS type_other,
				GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('lesson_types') . '.name SEPARATOR \', \') AS session_types,
				GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('staff') . '.staffID SEPARATOR \', \') AS staff_ids,
				GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('lesson_types') . '.typeID SEPARATOR \', \') AS type_ids,
				GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('activities') . '.activityID SEPARATOR \', \') AS activity_ids,
				GROUP_CONCAT(DISTINCT CONCAT(' .
				$this->db->dbprefix('staff') . '.first, \' \', ' . $this->db->dbprefix('staff') . '.surname) ORDER BY ' .
				$this->db->dbprefix('staff') . '.first ASC,' .
				$this->db->dbprefix('staff') . '.surname ASC SEPARATOR \', \') AS staff', FALSE)
				->from('bookings_lessons')
				->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
				->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
				->join('bookings_lessons_staff', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'left')
				->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'left')
				->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'left')
				->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')
				->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'left')
				->where_in('bookings_lessons.bookingID', $bookingIDs);

			if ($search_fields['show_blocks']) {
				$res_types = $res_types->group_by('bookings_lessons.blockID');
			} else {
				$res_types = $res_types->group_by('bookings_lessons.bookingID');
			}

			$res_types = $res_types->get();

			if ($res_types->num_rows() > 0) {
				foreach ($res_types->result() as $row) {
					if ($search_fields['show_blocks']) {
						// check filters by staff,activities and session_types
						if (!empty($search_fields['search_staff'])) {
							if (empty($row->staff_ids)){
								continue;
							}
							if (!in_array($search_fields['search_staff'], explode(',',$row->staff_ids))) {
								continue;
							}
						}

						if (!empty($search_fields['search_activity'])) {
							if (empty($row->activity_ids)){
								continue;
							}
							if (!in_array($search_fields['search_activity'], explode(',',$row->activity_ids))) {
								continue;
							}
						}

						if (!empty($search_fields['search_session_types'])) {
							if (empty($row->type_ids)){
								continue;
							}
							if (!in_array($search_fields['search_session_types'], explode(',',$row->type_ids))) {
								continue;
							}
						}

						if (strtotime($date_from) > strtotime($row->startDate)) {
							continue;
						}

						if (strtotime($date_to) < strtotime($row->endDate)) {
							continue;
						}

						if (!empty($search_fields['search_orgs'])) {
							if (!empty($row->block_org_id) && $row->block_org_id != $search_fields['search_orgs']){
								continue;
							}
							if (empty($row->block_org_id) && $row->booking_org_id != $search_fields['search_orgs']){
								continue;
							}
						}

						$booking_data[$row->bookingID][$row->blockID] = [
							'block_org_id' => $row->block_org_id,
							'org_name' => $row->org_name,
							'block_name' => $row->block_name,
							'start_date' => $row->startDate,
							'end_date' => $row->endDate,
							'activities' =>  str_replace('!SEPARATOR!', ', ', $row->activities),
							'activities_other' =>  str_replace('!SEPARATOR!', ', ', $row->activities_other),
							'type_other' =>  str_replace('!SEPARATOR!', ', ', $row->type_other),
							'staff' => $row->staff,
							'session_types' => $row->session_types
						];
					} else {
						$booking_data[$row->bookingID] = [
							'activities' =>  str_replace('!SEPARATOR!', ', ', $row->activities),
							'activities_other' =>  str_replace('!SEPARATOR!', ', ', $row->activities_other),
							'type_other' =>  str_replace('!SEPARATOR!', ', ', $row->type_other),
							'staff' => $row->staff,
							'session_types' => $row->session_types
						];
					}
				}
			}
		}

		$orgs = $this->orgs_library->getAllOrgs($this->auth->user->accountID);
		$staff = $this->staff_library->getAllStaff($this->auth->user->accountID);
		$sessionTypes = $this->settings_library->getSessionTypes($this->auth->user->accountID);
		$activities = $this->settings_library->getActivities($this->auth->user->accountID);
		$departments = $this->settings_library->getDepartments($this->auth->user->accountID);

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'page_base' => $page_base,
			'contracts' => $res,
			'booking_data' => $booking_data,
			'income' => $income,
			'costs' => $costs,
			'total_profit' => $total_profit,
			'search_fields' => $search_fields,
			'buttons' => $buttons,
			'orgs' => $orgs,
			'staff_list' => $staff,
			'session_types' => $sessionTypes,
			'activities' => $activities,
			'departments' => $departments,
			'customer_hours' => $customerHours
		);


		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');
			$this->load->view('reports/contracts-export', $data);
		} else {
			$this->crm_view('reports/contracts', $data);
		}
	}

}
