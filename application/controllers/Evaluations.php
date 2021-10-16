<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Evaluations extends MY_Controller {

	private $allowed_departments = array(
		'directors',
		'management',
		'headcoach'
	);

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array('session_evaluations'));
		$this->load->model('LessonsExceptionsModel');
	}

	/**
	 * show evaluations for logged in user
	 * @param $show_all boolean
	 * @return void
	 */
	public function index($show_all = FALSE) {
		// convert var to bool
		if ($show_all === 'true') {
			$show_all = TRUE;
		}

		// only allow certain people to view all
		if ($show_all == TRUE && !in_array($this->auth->user->department, $this->allowed_departments)) {
			$show_all = FALSE;
		}

		// set defaults
		$icon = 'clipboard';
		$current_page = 'evaluations';
		$section = 'evaluations';
		$page_base = 'evaluations';
		$title = 'Session Evaluations';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$action = NULL;
		$breadcrumb_levels = array();

		// set where
		$where = array(
			'bookings_lessons_notes.accountID' => $this->auth->user->accountID,
			'bookings_lessons_notes.type' => 'evaluation'
		);

		// if showing all
		if ($show_all === TRUE) {
			$title .= ' (All)';
			$page_base .= '/all';
			$current_page .= '_all';
			$breadcrumb_levels['evaluations'] = 'Session Evaluations';
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'staff_id' => NULL,
			'status' => NULL,
			'date_from' => NULL,
			'date_to' => NULL,
			'search' => NULL,
			'org' => NULL,
			'brand_id' => NULL,
			'session_type' => NULL,
			'activity' => NULL
		);

		$str = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : NULL;

		$is_search = false;
		if ($str !== null) {
			parse_str($str, $url_query_array);

			$this->load->library('form_validation');
			$this->form_validation->set_data($url_query_array);

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_status', 'Status', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_org', 'Search Organization', 'trim|xss_clean');
			$this->form_validation->set_rules('search_brand_id', 'Search Brand', 'trim|xss_clean');
			$this->form_validation->set_rules('search_session_type', 'Search Session Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_activity', 'Search Activity', 'trim|xss_clean');

			// run validation
			if( $this->form_validation->run() == FALSE ) {
				$errors = $this->form_validation->error_array();
			} else {
				$search_fields['staff_id'] = $this->input->get('search_staff_id');
				$search_fields['status'] = $this->input->get('search_status');
				$search_fields['date_from'] = $this->input->get('search_date_from');
				$search_fields['date_to'] = $this->input->get('search_date_to');
				$search_fields['search'] = $this->input->get('search');
				$search_fields['org'] = $this->input->get('search_org');
				$search_fields['brand_id'] = $this->input->get('search_brand_id');
				$search_fields['session_type'] = $this->input->get('search_session_type');
				$search_fields['activity'] = $this->input->get('search_activity');

				$is_search = TRUE;
			}
		}

			// load libraries

		if (isset($is_search) && $is_search === TRUE) {

			// store search fields
			$this->session->set_userdata('search-session_evaluations', $search_fields);

			if ($search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_notes") . "`.`byID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if ($search_fields['status'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_notes") . "`.`status` = " . $this->db->escape($search_fields['status']);
			}

			if ($search_fields['org'] != '') {
				$search_where[] = "((`" . $this->db->dbprefix("orgs") . "`.`orgID` = " . $this->db->escape($search_fields['org']) . ") OR (`orgs_blocks`.`orgID` = ". $this->db->escape($search_fields['org']) ."))";
			}

			if ($search_fields['brand_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`brandID` = " . $this->db->escape($search_fields['org']);
			}

			if ($search_fields['session_type'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons") . "`.`typeID` = " . $this->db->escape($search_fields['session_type']);
			}

			if ($search_fields['activity'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons") . "`.`activityID` = " . $this->db->escape($search_fields['activity']);
			}

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_notes") . "`.`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_notes") . "`.`date` <= " . $this->db->escape($date_to);
				}
			}
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		$lessons = [];

		//default staff filter
		if (!in_array($this->auth->user->department, array('directors', 'management', 'headcoach')) && !isset($_GET['search_staff_id'])) {
			$where['bookings_lessons_notes.byID'] = $this->auth->user->staffID;
			$search_fields['staff_id'] = $this->auth->user->staffID;
		}

		if (in_array($this->auth->user->department, array('directors', 'management', 'headcoach')) && !$show_all) {
			$where['bookings_lessons_notes.byID'] = $this->auth->user->staffID;
		}

		//getting all block where staff is assigned to display evaluations to all staff inside same blocks
		if ((!in_array($this->auth->user->department, array('directors', 'management', 'headcoach'))
				&& !$show_all && $is_search) || (in_array($this->auth->user->department, array('headcoach')) && $show_all)) {

			$staffChangeExceptions = $this->LessonsExceptionsModel->getStaffChangeExceptionsByStaff($this->auth->user->staffID);

			$query = $this->db->select('bookings_lessons.blockID')
				->from('bookings_lessons_staff')
				->where([
					'staffID' => $this->auth->user->staffID
				])
				->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID')
				->group_by('bookings_lessons.blockID')->get();

			$blocks = [];
			if ($query->num_rows() > 0) {
				foreach ($query->result() as $row) {
					$blocks[] = $row->blockID;
				}
			}

			if (!empty($blocks)) {
				$query = $this->db->select('bookings_lessons.lessonID')
					->from('bookings_lessons')
					->where_in('bookings_lessons.blockID', $blocks)
					->group_by('bookings_lessons.lessonID')->get();

				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$lessons[] = $row->lessonID;
					}
				}
			}

			if (!empty($staffChangeExceptions)) {
				foreach ($staffChangeExceptions as $exception) {
					if (!in_array($exception->lessonID, $lessons)) {
						$lessons[] = $exception->lessonID;
					}
				}
			}
		}

		// run query
		$res = $this->db->select('bookings_lessons_notes.*')->from('bookings_lessons_notes')
			->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_notes.lessonID', 'inner')
			->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'inner')
			->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
			->join('bookings', 'bookings_lessons_notes.bookingID = bookings.bookingID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('orgs as orgs_blocks', 'bookings_blocks.orgID = orgs_blocks.orgID', 'left')
			->where($where)->where($search_where, NULL, FALSE);

		// if head coach, limit to those that are a team leader for an blocks
		if ($this->auth->user->department == 'headcoach' && $show_all) {
			if (!empty($lessons)) {
				$lessons_in = implode(',', $lessons);
				$res->where('(staff_recruitment_approvers.approverID = ' . $this->auth->user->staffID . ' OR bookings_lessons_notes.lessonID IN (' . $lessons_in . ')) ');
			} else {
				$res->where('staff_recruitment_approvers.approverID', $this->auth->user->staffID);
			}
		}

		if (!in_array($this->auth->user->department, array('directors', 'management', 'headcoach')) && !empty($lessons)) {
			$res->where_in('bookings_lessons_notes.lessonID', $lessons);
		}

		$res = $res->get();

		$evaluations = [];
		foreach ($res->result() as $row) {
			$evaluations[$row->noteID] = $row;
		}

		$where_exceptions = array(
			'accountID' => $this->auth->user->accountID,
			'type' => 'cancellation'
		);
		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
			if ($date_from !== FALSE) {
				$where_exceptions['date >='] = $this->db->escape($date_from);
			}
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
			if ($date_to !== FALSE) {
				$where_exceptions['date <='] = $this->db->escape($date_to);
			}
		}

		//get session exceptions
		$lesson_exceptions = array();

		$res_staff = $this->db->from('bookings_lessons_exceptions')->where($where_exceptions)->get();

		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $row) {
				$lesson_exceptions[$row->lessonID][] = array(
					'date' => $row->date,
					'fromID' => $row->fromID,
					'staffID' => $row->staffID,
					'type' => $row->type
				);
			}
		}

		$skip_notes = [];
		foreach ($evaluations as $key => $evaluation) {
			if (isset($lesson_exceptions[$evaluation->lessonID])) {
				foreach ($lesson_exceptions[$evaluation->lessonID] as $exception) {
					if ($exception['date'] == $evaluation->date) {
						$skip_notes[] = $evaluation->noteID;
						unset($evaluations[$key]);
					}
				}
			}
		}

		// workout pagination
		$total_items = count($evaluations);
		$pagination = $this->pagination_library->calc_by_url($total_items);

		// run again, but limited
		$res = $this->db->select('bookings_lessons_notes.*, staff.first, staff.surname, bookings_lessons.startTime,
		 	bookings_lessons.day, bookings_lessons.location, bookings_lessons.type_other,
		  	bookings_lessons.activity_other, bookings_lessons.activity_desc, bookings_lessons.group,
		   	bookings_lessons.group_other, bookings_lessons.class_size, bookings_lessons.endTime,
		    bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end,
	 		bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, activities.name as activity,
	  		lesson_types.name as lesson_type, orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3,
		   	orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode, bookings.name as event_name, orgs.name as booking_org,
			orgs_blocks.name as block_org')
			->from('bookings_lessons_notes')
			->join('bookings_lessons', 'bookings_lessons_notes.lessonID = bookings_lessons.lessonID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'inner')
			->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
			->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as orgs_blocks', 'bookings_blocks.orgID = orgs_blocks.orgID', 'left')
			->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
			->where($where)
			->where($search_where, NULL, FALSE)
			->order_by('bookings_lessons_notes.date desc')
			->group_by('bookings_lessons_notes.noteID')
			->limit($this->pagination_library->amount, $this->pagination_library->start);


		// if head coach, limit to those that are a team leader for and blocks
		if ($this->auth->user->department == 'headcoach' && $show_all) {
			if (!empty($lessons)) {
				$lessons_in = implode(',', $lessons);
				$res->where('(staff_recruitment_approvers.approverID = ' . $this->auth->user->staffID . ' OR bookings_lessons_notes.lessonID IN (' . $lessons_in . ')) ');
			} else {
				$res->where('staff_recruitment_approvers.approverID', $this->auth->user->staffID);
			}
		}

		if (!in_array($this->auth->user->department, array('directors', 'management', 'headcoach')) && !empty($lessons)) {
			$res->where_in('bookings_lessons_notes.lessonID', $lessons);
		}

		if (!empty($skip_notes)) {
			$res->where('bookings_lessons_notes.noteID NOT IN (' . implode(',', $skip_notes) . ')');
		}

		$res = $res->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// staff
		$where = array(
			'staff.active' => 1,
			'staff.accountID' => $this->auth->user->accountID
		);
		// if head coach, limit to those that are a team leader for
		if ($this->auth->user->department == 'headcoach') {
			$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}
		$staff_list = $this->db->select("staff.*")
		->from('staff')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)->order_by('first asc, surname asc')->get();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$orgs_list = $this->db->from('orgs')->where($where)->order_by('name asc')->get();
		$where['active'] = 1;
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();
		$session_types = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();
		$activity_types = $this->db->from('activities')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'evaluations' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'staff_list' => $staff_list,
			'show_all' => $show_all,
			'orgs_list' => $orgs_list,
			'brands' => $brands,
			'session_types' => $session_types,
			'activities' => $activity_types
		);

		// load view
		$this->crm_view('evaluations/list', $data);
	}

	/**
	 * show approvals
	 * @return void
	 */
	public function approvals() {

		// only allow certain people to approve
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			show_404();
		}

		// set defaults
		$icon = 'clipboard';
		$current_page = 'evaluations_approvals';
		$section = 'evaluations';
		$page_base = 'evaluations/approvals';
		$title = 'Session Evaluation (Approvals)';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$selected_evaluations = array();
		$action = NULL;
		$breadcrumb_levels = array(
			'evaluations' => 'Session Evaluations',
		);

		// set where
		$where = array(
			'bookings_lessons_notes.accountID' => $this->auth->user->accountID,
			'bookings_lessons_notes.status' => 'submitted'
		);

		// if head coach, limit to those that are a team leader for
		if ($this->auth->user->department == 'headcoach') {
			$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'staff_id' => NULL,
			'date_from' => NULL,
			'date_to' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post('search')) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings_lessons_notes'))) {

			foreach ($this->session->userdata('search-bookings_lessons_notes') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-bookings_lessons_notes', $search_fields);

			if ($search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_notes") . "`.`byID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_notes") . "`.`added` >= " . $this->db->escape($date_from . ' 00:00:00');
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_notes") . "`.`added` <= " . $this->db->escape($date_to . ' 23:59:59');
				}
			}
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// bulk actions
		if ($this->input->post('bulk') == 1) {
			if (is_array($this->input->post('selected_evaluations'))) {
				$selected_evaluations = $this->input->post('selected_evaluations');
			}
			$action = $this->input->post('action');
			$bulk_successful = 0;
			$bulk_failed = 0;
			if (count($selected_evaluations) > 0) {
				foreach ($selected_evaluations as $noteID) {
					switch ($action) {
						case 'approve':
							$res = $this->approve($noteID, TRUE);
							if ($res === TRUE) {
								$bulk_successful++;
							} else {
								$bulk_failed++;
							}
							break;
						case 'reject':
							$res = $this->reject($noteID, TRUE);
							if ($res === TRUE) {
								$bulk_successful++;
							} else {
								$bulk_failed++;
							}
							break;
					}
				}
				// tell user
				if ($bulk_successful > 0 && $bulk_failed == 0) {
					$pl_sq = 's have';
					if ($bulk_successful == 1) {
						$pl_sq = ' has';
					}
					$this->session->set_flashdata('success', $bulk_successful . ' evaluation' . $pl_sq . ' been processed successfully.');
				} else if ($bulk_successful == 0 && $bulk_failed > 0) {
					$this->session->set_flashdata('error', $bulk_failed . ' evaluation(s) could not be processed.');
				} else if ($bulk_successful > 0 && $bulk_failed > 0) {
					$pl_sq = 's have been';
					if ($bulk_successful == 1) {
						$pl_sq = ' has been';
					}
					$this->session->set_flashdata('info', $bulk_successful . ' evaluation' . $pl_sq . ' been processed successfully, however ' .  $bulk_failed . ' evaluation(s) could not be processed.');
				}
				$redirect_to = 'evaluations/approvals';
				redirect($redirect_to);
				exit();
			} else {
				$error = 'Please select at least one evaluation';
			}
		}

		// run query
		$res = $this->db->select('staff.*,bookings_lessons_notes.*')->from('bookings_lessons_notes')
		->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'inner')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)->where($search_where, NULL, FALSE)->get();

		// workout pagination
		$total_items = $res->num_rows();
		$pagination = $this->pagination_library->calc($total_items);

		// run again, but limited
		$res = $this->db->select('bookings_lessons_notes.*, staff.first, staff.surname, bookings_lessons.startTime, bookings_lessons.day, bookings_lessons.location, bookings_lessons.type_other, bookings_lessons.activity_other, bookings_lessons.activity_desc, bookings_lessons.group, bookings_lessons.group_other, bookings_lessons.class_size, bookings_lessons.endTime, bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end, bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, activities.name as activity, lesson_types.name as lesson_type, orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode, bookings.name as event_name, orgs.name as booking_org, orgs_blocks.name as block_org')->from('bookings_lessons_notes')->join('bookings_lessons', 'bookings_lessons_notes.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
		->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'inner')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
		->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
		->join('orgs as orgs_blocks', 'bookings_blocks.orgID = orgs_blocks.orgID', 'left')
		->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
		->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
		->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
		->where($where)->where($search_where, NULL, FALSE)->order_by('bookings_lessons_notes.date desc')->group_by('bookings_lessons_notes.noteID')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();


		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// staff
		$where = array(
			'staff.active' => 1,
			'staff.accountID' => $this->auth->user->accountID
		);
		// if head coach, limit to those that are a team leader for
		if ($this->auth->user->department == 'headcoach') {
			$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}
		$staff_list = $this->db->select("staff.*")
		->from('staff')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)->order_by('first asc, surname asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'evaluations' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'staff_list' => $staff_list,
			'selected_evaluations' => $selected_evaluations,
			'action' => $action,
		);

		// load view
		$this->crm_view('evaluations/approvals', $data);
	}

	/**
	 * approve an evaluation
	 * @param  int $noteID
	 * @param boolean $bulk
	 * @return mixed
	 */
	public function approve($noteID = NULL, $bulk = FALSE) {

		// only allow certain people to approve
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			show_404();
		}

		// check params
		if (empty($noteID)) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		$where = array(
			'bookings_lessons_notes.noteID' => $noteID,
			'bookings_lessons_notes.status' => 'submitted',
			'bookings_lessons_notes.accountID' => $this->auth->user->accountID
		);

		// if head coach, limit to those that are a team leader for
		if ($this->auth->user->department == 'headcoach') {
			$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}

		// run query
		$query = $this->db->select('bookings_lessons_notes.*')
		->from('bookings_lessons_notes')
		->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'inner')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$evaluation_info = $row;

			// all ok, process
			$data = array(
				'status' => 'approved',
				'approverID' => $this->auth->user->staffID,
				'approved' => mdate('%Y-%m-%d %H:%i:%s')
			);
			if ($this->auth->user->department == 'headcoach') {
				unset($where['staff_recruitment_approvers.approverID']);
			}
			$res = $this->db->update('bookings_lessons_notes', $data, $where, 1);

			if ($this->db->affected_rows() > 0) {
				if ($bulk == TRUE) {
					return TRUE;
				}

				$this->session->set_flashdata('success', 'Evaluation has been approved successfully.');

				$redirect_to = 'evaluations/approvals';
				redirect($redirect_to);
			} else {
				if ($bulk == TRUE) {
					return FALSE;
				}
				show_404();
			}
		}
	}

	/**
	 * reject an evaluation
	 * @param  int $noteID
	 * @param boolean $bulk
	 * @return mixed
	 */
	public function reject($noteID = NULL, $bulk = FALSE) {

		// only allow certain people to reject
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			show_404();
		}

		// check params
		if (empty($noteID)) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		$where = array(
			'bookings_lessons_notes.noteID' => $noteID,
			'bookings_lessons_notes.status' => 'submitted',
			'bookings_lessons_notes.accountID' => $this->auth->user->accountID
		);

		// if head coach, limit to those that are a team leader for
		if ($this->auth->user->department == 'headcoach') {
			$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}

		// run query
		$query = $this->db->select('bookings_lessons_notes.*')
		->from('bookings_lessons_notes')
		->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'inner')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$evaluation_info = $row;

			// all ok, process
			$data = array(
				'status' => 'rejected',
				'approverID' => $this->auth->user->staffID,
				'rejected' => mdate('%Y-%m-%d %H:%i:%s')
			);
			if ($this->auth->user->department == 'headcoach') {
				unset($where['staff_recruitment_approvers.approverID']);
			}
			$res = $this->db->update('bookings_lessons_notes', $data, $where, 1);

			if ($this->db->affected_rows() > 0) {
				if ($bulk == TRUE) {
					return TRUE;
				}

				$this->session->set_flashdata('success', 'Evaluation has been rejected successfully.');

				$redirect_to = 'evaluations/approvals';
				redirect($redirect_to);
			} else {
				if ($bulk == TRUE) {
					return FALSE;
				}
				show_404();
			}
		}
	}
}

/* End of file Evaluations.php */
/* Location: ./application/controllers/Evaluations.php */
