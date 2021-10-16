<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sessions extends MY_Controller {

	private $staff_required_for_session;

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}

		$this->staff_required_for_session = $this->settings_library->staffing_types_required_for_sessions;

		$this->load->library('qualifications_library');
		$this->load->library('orgs_library');
		$this->load->library('notifications_library');
		$this->load->library('attachment_library');
	}

	/**
	 * show list of lessons
	 * @return void
	 */
	public function index($bookingID = NULL, $blockID = NULL, $ajaxFlag = NULL) {

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

		$staff_names = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$staff = $this->db->from('staff')->where($where)->get();

		if ($staff->num_rows() > 0) {
			foreach ($staff->result() as $s) {
				$staff_names[$s->staffID] = $s->first . ' ' . $s->surname;
			}
		}

		// get first block if not set
		if (empty($blockID)) {
			// look for first block from current/future
			$where = array(
				'bookings_blocks.bookingID' => $bookingID,
				'bookings_blocks.accountID' => $this->auth->user->accountID,
				'bookings_blocks.endDate >=' => mdate('%Y-%m-%d')
			);
			$res = $this->db->select('bookings_blocks.*, bookings.orgID as booking_orgID, block_orgs.name as block_org')->from('bookings_blocks')->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')->where($where)->order_by('bookings_blocks.startDate asc')->limit(1)->get();

			// if none, get from all blocks
			if ($res->num_rows() == 0) {
				$where = array(
					'bookings_blocks.bookingID' => $bookingID,
					'bookings_blocks.accountID' => $this->auth->user->accountID,
				);
				$res = $this->db->select('bookings_blocks.*, bookings.orgID as booking_orgID, block_orgs.name as block_org')->from('bookings_blocks')->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')->where($where)->order_by('bookings_blocks.startDate asc')->limit(1)->get();
			}

			if ($res->num_rows() == 0) {
				show_404();
			}

			foreach ($res->result() as $row) {
				$block_info = $row;
				$blockID = $block_info->blockID;
			}
		} else {
			// look up block
			$where = array(
				'bookings_blocks.bookingID' => $bookingID,
				'bookings_blocks.blockID' => $blockID,
				'bookings_blocks.accountID' => $this->auth->user->accountID
			);
			$res = $this->db->select('bookings_blocks.*, bookings.orgID as booking_orgID, block_orgs.name as block_org')->from('bookings_blocks')->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')->where($where)->limit(1)->get();

			if ($res->num_rows() == 0) {
				show_404();
			}

			foreach ($res->result() as $row) {
				$block_info = $row;
			}
		}

		// get customer
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID
		);
		if (!empty($block_info->orgID)) {
			$where['orgID'] = $block_info->orgID;
		}
		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'calendar-alt';
		$tab = 'lessons';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$page_base = 'bookings/sessions/' . $bookingID . '/' . $blockID;
		$section = 'bookings';
		$title = 'Sessions';
		if ($block_info->orgID != $block_info->booking_orgID && !empty($block_info->block_org)) {
			$title .= ' (' . $block_info->block_org . ')';
		}
		$buttons = '<a class="btn btn-success" href="' . site_url('bookings/sessions/' . $blockID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		if ($this->auth->has_features('participants') && ($booking_info->type == 'event' || $booking_info->project == 1)) {
			$buttons .= ' <a class="btn btn-primary" href="' . site_url('bookings/participants/' . $blockID) . '"><i class="far fa-user"></i> ' . $this->settings_library->get_label('participants') . '</a>';
		}
		$success = NULL;
		$error = NULL;
		$errors = [];
		$info = NULL;

		// set where
		$where = array(
			'bookings_lessons.bookingID' => $bookingID,
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'day' => NULL,
			'activity_id' => NULL,
			'group' => NULL,
			'type_id' => NULL,
			'staff_id' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_day', 'Day', 'trim|xss_clean');
			$this->form_validation->set_rules('search_activity_id', 'Activity', 'trim|xss_clean');
			$this->form_validation->set_rules('search_group', 'Group/Class', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type_id', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['day'] = set_value('search_day');
			$search_fields['activity_id'] = set_value('search_activity_id');
			$search_fields['group'] = set_value('search_group');
			$search_fields['type_id'] = set_value('search_type_id');
			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings-lessons'))) {

			foreach ($this->session->userdata('search-bookings-lessons') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-bookings-lessons', $search_fields);

			if ($search_fields['day'] != '') {
				$search_where[] = "`day` = " . $this->db->escape($search_fields['day']);
			}

			if ($search_fields['activity_id'] != '') {
				if ($search_fields['activity_id'] == 'other') {
					$search_where[] = $this->db->dbprefix('bookings_lessons').".`activityID` IS NULL";
				} else {
					$search_where[] = $this->db->dbprefix('bookings_lessons').".`activityID` = " . $this->db->escape($search_fields['activity_id']);
				}
			}

			if ($search_fields['group'] != '') {
				$search_where[] = "`group` = " . $this->db->escape($search_fields['group']);
			}

			if ($search_fields['type_id'] != '') {
				if ($search_fields['type_id'] == 'other') {
					$search_where[] = $this->db->dbprefix('bookings_lessons').".`typeID` IS NULL";
				} else {
					$search_where[] = $this->db->dbprefix('bookings_lessons').".`typeID` = " . $this->db->escape($search_fields['type_id']);
				}
			}

			if ($search_fields['staff_id'] > 0) {
				$search_where[] = $this->db->dbprefix('bookings_lessons_staff').".`staffID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('bookings_lessons.*, SUM(' . $this->db->dbprefix('bookings_attendance_numbers') . '.attended) as participants_numbers, COUNT(DISTINCT ' . $this->db->dbprefix('bookings_attendance_names_sessions') . '.attendanceID) as participants_names, activities.name as activity, lesson_types.name as type, orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode')
		->from('bookings_lessons')
		->join('bookings_attendance_numbers', 'bookings_lessons.lessonID = bookings_attendance_numbers.lessonID', 'left')
		->join('bookings_attendance_names_sessions', 'bookings_lessons.lessonID = bookings_attendance_names_sessions.lessonID', 'left')
		->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
		->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
		->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left');

		if ($search_fields['staff_id'] > 0) {
			$res = $res->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner');
		}

		$res = $res->where($where)->where($search_where, NULL, FALSE)->group_by('bookings_lessons.lessonID')->order_by('day asc, startTime asc, activity asc, lessonID asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('bookings_lessons.*, SUM(' . $this->db->dbprefix('bookings_attendance_numbers') . '.attended) as participants_numbers,
		 COUNT(DISTINCT ' . $this->db->dbprefix('bookings_attendance_names_sessions') . '.attendanceID) as participants_names, activities.name as activity,
		 lesson_types.name as type, orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3,
		 orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode, offer_accept.offer_type')
		->from('bookings_lessons')
		->join('bookings_attendance_numbers', 'bookings_lessons.lessonID = bookings_attendance_numbers.lessonID', 'left')
		->join('bookings_attendance_names_sessions', 'bookings_lessons.lessonID = bookings_attendance_names_sessions.lessonID', 'left')
		->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
		->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
		->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
		->join('offer_accept', 'bookings_lessons.lessonID = offer_accept.lessonID', 'left');

		if ($search_fields['staff_id'] > 0) {
			$res = $res->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner');
		}

		$res = $res->where($where)->where($search_where, NULL, FALSE)->group_by('bookings_lessons.lessonID')->order_by('day asc, startTime asc, activity asc, lessonID asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		}
		if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}
		if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		} else if ($this->session->flashdata('errors')) {
			$errors = $this->session->flashdata('errors');
		}

		// extend flash data for bulk session actions for one more request
		$this->session->set_flashdata('bulk_action', $this->session->flashdata('bulk_action'));
		$this->session->set_flashdata('bulk_lessons', $this->session->flashdata('bulk_lessons'));

		// get blocks
		$where = array(
			'bookings_blocks.bookingID' => $bookingID,
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);
		$blocks = $this->db->select('bookings_blocks.*, orgs.name as org_name')->from('bookings_blocks')->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')->where($where)->order_by('bookings_blocks.startDate asc')->get();

		// staff
		$where = array(
			'active' => 1,
			'non_delivery !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// contacts
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		if (!empty($block_info->orgID) && $block_info->orgID != $booking_info->orgID) {
			$where['orgID'] = $block_info->orgID;
		}
		$contacts = $this->db->from('orgs_contacts')->where($where)->order_by('isMain desc, name asc')->get();

		// addresses
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID
		);
		// if block has customer override, get addresses from that customer
		if (!empty($block_info->orgID)) {
			$where['orgID'] = $block_info->orgID;
		}
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc, town asc, county asc, postcode asc')->get();

		// session staff
		$lesson_staff = array();
		$where = array(
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);
		$res_lesson_staff = $this->db->select('staff.*')->from('staff')->join('bookings_lessons_staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner')->where($where)->order_by('staff.first asc, staff.surname asc')->group_by('staff.staffID')->get();
		if ($res_lesson_staff->num_rows() > 0) {
			foreach ($res_lesson_staff->result() as $row) {
				$lesson_staff[$row->staffID] = $row->first . ' ' . $row->surname;
			}
		}

		// get list of resources
		$resources_attachments = array();
		$where = array(
			'files.accountID' => $this->auth->user->accountID,
			'settings_resources.session_attachments' => 1
		);
		$res_plans = $this->db->select('files.*')
			->from('files')
			->join('settings_resourcefile_map', 'files.attachmentID = settings_resourcefile_map.attachmentID', 'inner')
			->join('settings_resources', 'settings_resourcefile_map.resourceID = settings_resources.resourceID', 'inner')
			->where($where)
			->order_by('files.name asc')
			->group_by('files.attachmentID')
			->get();
		if ($res_plans->num_rows() > 0) {
			foreach ($res_plans->result() as $row) {
				$resources_attachments[$row->attachmentID] = $row->name;
			}
		}

		// get list of coach access org attachments
		$coach_access = array();
		$where = array(
			'orgID' => $booking_info->orgID,
			'coachaccess' => 1,
			'accountID' => $this->auth->user->accountID
		);
		// if block has customer override, get from that customer
		if (!empty($block_info->orgID)) {
			$where['orgID'] = $block_info->orgID;
		}
		$res_coachaccess = $this->db->from('orgs_attachments')->where($where)->order_by('name asc')->get();
		if ($res_coachaccess->num_rows() > 0) {
			foreach ($res_coachaccess->result() as $row) {
				$coach_access[$row->attachmentID] = $row->name;
			}
		}

		// activities
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$activities = $this->db->from('activities')->where($where)->order_by('name asc')->get();

		// session types
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$lesson_types = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();

		// get list of session requirements
		$lesson_requirements = array(
			'req_ppa_playground' => 'PPA - Playground collection',
			'req_ppa_classroom' => 'PPA - Classroom collection',
			'req_ppa_meet' => 'PPA - Meet in Hall',
			'req_ppa_reg' => 'PPA - Registration',
			'req_ppa_changed_before' => 'PPA - Children changed before',
			'req_ppa_changed_after' => 'PPA - Children changed after',
			'req_ppa_dismissed' => 'PPA - Children dismissed by coach',
			'req_ppa_assist' => 'PPA - Coach assist with dismissal',
			'req_extra_perf' => 'Extra-curricular - Performance',
			'req_extra_cert' => 'Extra-curricular - Certificates',
			'req_extra_reg' => 'Extra-curricular - Registration',
			'req_extra_money' => 'Extra-curricular - Money collections',
			'req_extra_children' => 'Extra-curricular - Children signed out'
		);

		// retrieve bulk data
		$bulk_data = array();
		if ($this->session->flashdata('bulk_data')) {
			$bulk_data = $this->session->flashdata('bulk_data');
			if (!is_array($bulk_data)) {
				$bulk_data = array();
			}
		}

		// check for invalid lessons
		$invalid_lessons = array();
		if ($this->session->flashdata('invalid_lessons')) {
			$invalid_lessons = $this->session->flashdata('invalid_lessons');
			if (!is_array($invalid_lessons)) {
				$invalid_lessons = array();
			}
		}

		// get staff on sessions by type
		$lesson_staff_by_type = array();
		$lesson_staff_by_type_name = array();
		$where = array(
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons_staff.accountID' => $this->auth->user->accountID
		);
		$res_staff_by_type = $this->db->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->group_by('bookings_lessons_staff.recordID')->get();
		if ($res_staff_by_type->num_rows() > 0) {
			foreach ($res_staff_by_type->result() as $row) {
				$lesson_staff_by_type[$row->lessonID][$row->type][$row->staffID] = $row->staffID;
			}
		}

		if(count($lesson_staff_by_type) > 0){
			foreach($lesson_staff_by_type as $lessonid => $lessonIDs){
				if(count($lessonIDs) > 0){
					foreach($lessonIDs as $staff_type => $staff_ids){
						foreach($staff_ids as $staff_id){
							switch ($staff_type) {
								case 'head':
									$lesson_staff_by_type_name[$lessonid]['headcoaches'][] = $staff_names[$staff_id];
									break;
								case 'lead':
									$lesson_staff_by_type_name[$lessonid]['leadcoaches'][] = $staff_names[$staff_id];
									break;
								case 'assistant':
								default:
									$lesson_staff_by_type_name[$lessonid]['assistantcoaches'][] = $staff_names[$staff_id];
									break;
								case 'observer':
									$lesson_staff_by_type_name[$lessonid]['observers'][] = $staff_names[$staff_id];
									break;
								case 'participant':
									$lesson_staff_by_type_name[$lessonid]['participants'][] = $staff_names[$staff_id];
									break;
							}
						}
					}
				}
			}
		}



		// get participant counts
		$participants = array();
		$where = array(
			'bookings_lessons.accountID' => $this->auth->user->accountID,
			'bookings_lessons.blockID' => $blockID,
			'bookings_cart.type' => 'booking'
		);
		$res_participants = $this->db->select('bookings_lessons.lessonID, COUNT(DISTINCT ' . $this->db->dbprefix('bookings_cart_sessions') . '.contactID) as participants, COUNT(DISTINCT ' . $this->db->dbprefix('bookings_cart_sessions') . '.childID) as participants_children')
		->from('bookings_lessons')
		->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
		->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
		->where($where)
		->group_by('bookings_lessons.lessonID')
		->get();
		if ($res_participants->num_rows() > 0) {
			foreach ($res_participants->result() as $row) {
				$participants[$row->lessonID]['children'] = intval($row->participants_children);
				$participants[$row->lessonID]['individuals'] = intval($row->participants);
			}
		}

		$required_staff_for_session = $this->settings_library->get_required_staff_for_session();

		$groups = $this->settings_library->getGroups([
			'accountID' => $this->auth->user->accountID
		]);
		$modalflag = 0;
		$message = "";
		if($success != ""){
			$message = '<div class="alert alert-custom alert-success" style="margin:0" role="alert"><div class="alert-icon"><i class="far fa-check-circle fa-2x"></i></div><div class="alert-text">'. $success.'</div></div>';
			$success = "";
			$modalflag = 1;
		}if($error != ""){
			$message = '<div class="alert alert-custom alert-danger" style="margin:0" role="alert"><div class="alert-icon"><i class="far fa-exclamation-circle fa-2x"></i></div><div class="alert-text">'. $error.'</div></div>';
			$error = "";
			$modalflag = 2;
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
			'lessons' => $res,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'staff_list' => $staff_list,
			'addresses' => $addresses,
			'blocks' => $blocks,
			'contacts' => $contacts,
			'type' => $booking_info->type,
			'bulk_data' => $bulk_data,
			'resources_attachments' => $resources_attachments,
			'lesson_staff' => $lesson_staff,
			'lesson_staff_by_type' => $lesson_staff_by_type,
			'coach_access' => $coach_access,
			'lesson_requirements' => $lesson_requirements,
			'activities' => $activities,
			'lesson_types' => $lesson_types,
			'invalid_lessons' => $invalid_lessons,
			'breadcrumb_levels' => $breadcrumb_levels,
			'participants' => $participants,
			'org_info' => $org_info,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'required_staff_for_session' => $required_staff_for_session,
			'modalflag' => $modalflag,
			'message' => $message,
			'ajaxFlag' => $ajaxFlag,
			'lesson_staff_by_type_name' => $lesson_staff_by_type_name,
			'groups' => $groups
		);

		// load view
		if($ajaxFlag == 1)
			$this->load->view('bookings/sessions', $data);
		else
			$this->crm_view('bookings/sessions', $data);
	}

	/**
	 * edit a lesson
	 * @param  int $lessonID
	 * @param int $blockID
	 * @param int $ajaxFlag
	 * @return void
	 */
	public function edit($lessonID = NULL, $blockID = NULL, $ajaxFlag = NULL)
	{

		$lesson_info = new stdClass();

		// check if editing
		if ($lessonID != NULL) {

			// check if numeric
			if (!ctype_digit($lessonID)) {
				show_404();
			}

			// if so, check user exists
			// run query
			$query = $this->db->select('bookings_lessons.*, lesson_types.session_evaluations')->from('bookings_lessons')->where([
				'lessonID' => $lessonID,
				'bookings_lessons.accountID' => $this->auth->user->accountID
			])->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$lesson_info = $row;
				$bookingID = $lesson_info->bookingID;
				$blockID = $lesson_info->blockID;
			}

		}

		// get booking id from block if not found
		if (!isset($bookingID) || empty($bookingID)) {
			// look up block
			$where = array(
				'blockID' => $blockID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			foreach ($query->result() as $row) {
				$block_info = $row;
				$bookingID = $block_info->bookingID;
			}
		}

		// required
		if ($bookingID == NULL || $blockID == NULL) {
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

		// look up block
		$where = array(
			'blockID' => $blockID,
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Session';
		if ($lessonID != NULL) {
			$submit_to = 'bookings/sessions/edit/' . $lessonID;
			$title = ucwords($lesson_info->day) . ' - ' . substr($lesson_info->startTime, 0, 5);
		} else {
			$submit_to = 'bookings/sessions/' . $blockID . '/new/';
		}
		$return_to = 'bookings/sessions/' . $bookingID . '/' . $blockID;
		$icon = 'book';
		$tab = 'lessons';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $blockID] = 'Sessions';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$add_address = 0;
		$errors = array();
		$success = NULL;
		$info = NULL;

		// get list of resources
		$resources_attachments = array();
		//session plans ID is 2
		$where = array(
			'files.accountID' => $this->auth->user->accountID,
			'settings_resourcefile_map.resourceID' => '2'
		);

		$res = $this->db->select('files.*, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('files_brands') . '.brandID SEPARATOR \',\') AS brands')
			->from('files')
			->join('files_brands', 'files.attachmentID = files_brands.attachmentID', 'left')
			->join('settings_resourcefile_map', 'files.attachmentID = settings_resourcefile_map.attachmentID', 'inner')
			->where($where)
			->order_by('files.name asc')
			->group_by('files.attachmentID')
			->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$resources_attachments[$row->attachmentID] = $row->name;
			}
		}

		// get list of org attachments
		$org_attachments = array();
		$where = array(
			'orgID' => $booking_info->orgID,
			'coachaccess' => 1,
			'accountID' => $this->auth->user->accountID
		);
		// if block has customer override, get from that customer
		if (!empty($block_info->orgID)) {
			$where['orgID'] = $block_info->orgID;
		}
		$res = $this->db->from('orgs_attachments')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$org_attachments[$row->attachmentID] = $row->name;
			}
		}

		// if posted
		if ($this->input->post()) {

			if ($this->input->post('add_address') == 1) {
				$add_address = 1;
			}

			// set validation rules
			$this->form_validation->set_rules('day', 'Day', 'trim|xss_clean|required');
			$this->form_validation->set_rules('startTimeH', 'Start Time - Hour', 'trim|xss_clean|required');
			$this->form_validation->set_rules('startTimeM', 'Start Time - Minutes', 'trim|xss_clean|required');
			$this->form_validation->set_rules('endTimeH', 'End Time', 'trim|xss_clean|required|callback_lesson_datetime');
			$this->form_validation->set_rules('endTimeM', 'End Time - Minutes', 'trim|xss_clean|required');
			$this->form_validation->set_rules('adjust_staff_times', 'Adjust Staff Times', 'trim|xss_clean');
			$this->form_validation->set_rules('startDate', 'Start Date', 'trim|xss_clean|callback_check_date|callback_within_block[' . $blockID . ',TRUE]');
			$this->form_validation->set_rules('endDate', 'End Date', 'trim|xss_clean|callback_end_required[' . $this->input->post('startDate') . ']|callback_check_date|callback_after_start[' . $this->input->post('startDate') . ']|callback_within_block[' . $blockID . ',TRUE]|callback_valid_lesson_dates[' . $this->input->post('startDate') . ',' . $this->input->post('day') . ']');
			if ($booking_info->type == 'booking') {
				if ($add_address != 1) {
					$this->form_validation->set_rules('addressID', 'Session Delivery Address', 'trim|xss_clean|required');
				} else {
					$this->form_validation->set_rules('address_address1', 'Address 1', 'trim|xss_clean|required');
					$this->form_validation->set_rules('address_address2', 'Address 2', 'trim|xss_clean');
					$this->form_validation->set_rules('address_address3', 'Address 3');
					$this->form_validation->set_rules('address_town', 'Town', 'trim|xss_clean|required');
					$this->form_validation->set_rules('address_county', localise('county'), 'trim|xss_clean|required');
					$this->form_validation->set_rules('address_postcode', 'Postcode', 'trim|xss_clean|required|callback_check_postcode');
				}
			}
			$this->form_validation->set_rules('location', 'Location', 'trim|xss_clean');
			if ($this->settings_library->get('require_session_type')) {
				$this->form_validation->set_rules('typeID', 'Type', 'trim|xss_clean|required');
			} else {
				$this->form_validation->set_rules('typeID', 'Type', 'trim|xss_clean');
			}
			$this->form_validation->set_rules('type_other', 'Type - Other', 'trim|xss_clean|callback_required_if_other[' . $this->input->post('typeID') . ']');
			switch ($booking_info->type) {
				case 'booking':
					$this->form_validation->set_rules('activityID', 'Activity', 'trim|xss_clean|required');
					break;
				case 'event':
					$this->form_validation->set_rules('activityID', 'Activity', 'trim|xss_clean');
					break;
			}
			$this->form_validation->set_rules('activity_other', 'Activity - Other', 'trim|xss_clean|callback_required_if_other[' . $this->input->post('activityID') . ']');
			$this->form_validation->set_rules('activity_desc', 'Activity Description', 'trim|xss_clean');
			switch ($booking_info->type) {
				case 'booking':
					$this->form_validation->set_rules('group', 'Group/Class', 'trim|xss_clean');
					break;
				case 'event':
					$this->form_validation->set_rules('group', 'Group/Class', 'trim|xss_clean');
					break;
			}
			$this->form_validation->set_rules('group_other', 'Group/Class - Other', 'trim|xss_clean');
			$this->form_validation->set_rules('class_size', 'Class Size', 'trim|xss_clean');
			//$this->form_validation->set_rules('resources_attachments', 'Scheme of Work', 'required');
			switch ($booking_info->type) {
				case 'booking':
					// head coach can't change this
					if ($this->auth->user->department != 'headcoach') {
						$this->form_validation->set_rules('charge', 'Charge', 'trim|xss_clean|required');
						$this->form_validation->set_rules('charge_other', 'Charge - Other', 'trim|xss_clean|callback_required_if_other[' . $this->input->post('charge') . ']');
					}

					$this->form_validation->set_rules('req_ppa_playground', 'Playground collection', 'trim|xss_clean');
					$this->form_validation->set_rules('req_ppa_classroom', 'Classroom collection', 'trim|xss_clean');
					$this->form_validation->set_rules('req_ppa_meet', 'Meet in Hall', 'trim|xss_clean');
					$this->form_validation->set_rules('req_ppa_reg', 'Registration', 'trim|xss_clean');
					$this->form_validation->set_rules('req_ppa_changed_before', 'Children changed before', 'trim|xss_clean');
					$this->form_validation->set_rules('req_ppa_changed_after', 'Children changed after', 'trim|xss_clean');
					$this->form_validation->set_rules('req_ppa_dismissed', 'Children dismissed by coach', 'trim|xss_clean');
					$this->form_validation->set_rules('req_ppa_assist', 'Coach assist with dismissal', 'trim|xss_clean');

					$this->form_validation->set_rules('req_extra_perf', 'Performance', 'trim|xss_clean');
					$this->form_validation->set_rules('req_extra_cert', 'Certificates', 'trim|xss_clean');
					$this->form_validation->set_rules('req_extra_reg', 'Registration', 'trim|xss_clean');
					$this->form_validation->set_rules('req_extra_money', 'Money collections', 'trim|xss_clean');
					$this->form_validation->set_rules('req_extra_children', 'Children signed out', 'trim|xss_clean');
					break;
			}
			if ($booking_info->type == 'event' || $booking_info->project == 1) {
				$this->form_validation->set_rules('price', 'Price', 'trim|xss_clean|is_numeric');
				$this->form_validation->set_rules('target_participants', 'Target ' . $this->settings_library->get_label('participants'), 'trim|xss_clean|greater_than[-1]');
			}
			$this->form_validation->set_rules('staff_required_head', Inflect::pluralize($this->settings_library->get_staffing_type_label('head')) . ' Required', 'trim|xss_clean|greater_than[-1]');
			$this->form_validation->set_rules('staff_required_lead', Inflect::pluralize($this->settings_library->get_staffing_type_label('lead')) . ' Required', 'trim|xss_clean|greater_than[-1]');
			$this->form_validation->set_rules('staff_required_assistant', Inflect::pluralize($this->settings_library->get_staffing_type_label('assistant')) . ' Required', 'trim|xss_clean|greater_than[-1]');
			$this->form_validation->set_rules('staff_required_observer', Inflect::pluralize($this->settings_library->get_staffing_type_label('observer')) . ' Required', 'trim|xss_clean|greater_than[-1]');
			$this->form_validation->set_rules('staff_required_participant', Inflect::pluralize($this->settings_library->get_staffing_type_label('participant')) . ' Required', 'trim|xss_clean|greater_than[-1]');
			if ($booking_info->type == 'event' || $booking_info->project == 1) {
				$this->form_validation->set_rules('booking_cutoff', 'Online Booking Cut Off', 'trim|xss_clean|numeric');
				$this->form_validation->set_rules('min_age', 'Minimum Age', 'trim|xss_clean|numeric');
				$this->form_validation->set_rules('max_age', 'Maximum Age', 'trim|xss_clean|numeric');
			}
			if ($this->form_validation->run() == FALSE) {
				if($ajaxFlag == 1){
					$this->error = 1;
					if(count($this->form_validation->error_array()) > 1){
						$this->message = '
								<p>Please correct the following errors:</p><ul>';
								$errorsData = $this->form_validation->error_array();
								if(count($errorsData) > 0){
									foreach($errorsData as $errorsDatas){
										$this->message.= '<li>'.$errorsDatas."</li>";
									}
								}
						$this->message .= '</ul>';
					}else{
						$this->message = '<p>'.implode(",",$this->form_validation->error_array())."</p>";
					}
				}else{
					$errors = $this->form_validation->error_array();
				}
			} else {

				// work out start and end time
				$startTime = set_value('startTimeH') . ':' . set_value('startTimeM');
				$endTime = set_value('endTimeH') . ':' . set_value('endTimeM');

				// all ok, prepare data
				$data = array(
					'day' => set_value('day'),
					'startDate' => NULL,
					'endDate' => NULL,
					'startTime' => $startTime,
					'endTime' => $endTime,
					'location' => set_value('location'),
					'typeID' => NULL,
					'type_other' => NULL,
					'activityID' => NULL,
					'activity_other' => NULL,
					'activity_desc' => set_value('activity_desc'),
					'group' => NULL,
					'group_other' => set_value('group_other'),
					'class_size' => set_value('class_size'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (set_value('startDate') != '') {
					$data['startDate'] = uk_to_mysql_date(set_value('startDate'));
				}

				if (set_value('endDate') != '') {
					$data['endDate'] = uk_to_mysql_date(set_value('endDate'));
				}

				if (set_value('typeID') != '' && set_value('typeID') != 'other') {
					$data['typeID'] = set_value('typeID');
				} else {
					$data['type_other'] = set_value('type_other');
				}
				if (set_value('activityID') != '' && set_value('activityID') != 'other') {
					$data['activityID'] = set_value('activityID');
				} else {
					$data['activity_other'] = set_value('activity_other');
				}
				if (set_value('group') != '') {
					$data['group'] = set_value('group');
				}

				switch ($booking_info->type) {
					case 'booking';
						$data['addressID'] = set_value('addressID');

						// head coach can't change this
						if ($this->auth->user->department != 'headcoach') {
							$data['charge'] = NULL;
							$data['charge_other'] = set_value('charge_other');
							if (set_value('charge') != '') {
								$data['charge'] = set_value('charge');
							}

						}

						$data['req_ppa_playground'] = 0;
						$data['req_ppa_classroom'] = 0;
						$data['req_ppa_meet'] = 0;
						$data['req_ppa_reg'] = 0;
						$data['req_ppa_changed_before'] = 0;
						$data['req_ppa_changed_after'] = 0;
						$data['req_ppa_dismissed'] = 0;
						$data['req_ppa_assist'] = 0;

						if (set_value('req_ppa_playground') == 1) {
							$data['req_ppa_playground'] = 1;
						}
						if (set_value('req_ppa_classroom') == 1) {
							$data['req_ppa_classroom'] = 1;
						}
						if (set_value('req_ppa_meet') == 1) {
							$data['req_ppa_meet'] = 1;
						}
						if (set_value('req_ppa_reg') == 1) {
							$data['req_ppa_reg'] = 1;
						}
						if (set_value('req_ppa_changed_before') == 1) {
							$data['req_ppa_changed_before'] = 1;
						}
						if (set_value('req_ppa_changed_after') == 1) {
							$data['req_ppa_changed_after'] = 1;
						}
						if (set_value('req_ppa_dismissed') == 1) {
							$data['req_ppa_dismissed'] = 1;
						}
						if (set_value('req_ppa_assist') == 1) {
							$data['req_ppa_assist'] = 1;
						}

						$data['req_extra_perf'] = 0;
						$data['req_extra_cert'] = 0;
						$data['req_extra_reg'] = 0;
						$data['req_extra_money'] = 0;
						$data['req_extra_children'] = 0;

						if (set_value('req_extra_perf') == 1) {
							$data['req_extra_perf'] = 1;
						}
						if (set_value('req_extra_cert') == 1) {
							$data['req_extra_cert'] = 1;
						}
						if (set_value('req_extra_reg') == 1) {
							$data['req_extra_reg'] = 1;
						}
						if (set_value('req_extra_money') == 1) {
							$data['req_extra_money'] = 1;
						}
						if (set_value('req_extra_children') == 1) {
							$data['req_extra_children'] = 1;
						}

						break;
				}

				if ($booking_info->type == 'event' || $booking_info->project == 1) {
					$data['price'] = NULL;

					if (set_value('price') > 0) {
						$data['price'] = set_value('price');
					}
					$data['target_participants'] = 0;

					if (set_value('target_participants') > 0) {
						$data['target_participants'] = set_value('target_participants');
					}

					$data['booking_cutoff'] = NULL;
					if (set_value('booking_cutoff') !== "") {
						$data['booking_cutoff'] = set_value('booking_cutoff');
					}

					$data['min_age'] = NULL;
					if (set_value('min_age') !== "") {
						$data['min_age'] = set_value('min_age');
					}

					$data['max_age'] = NULL;
					if (set_value('max_age') !== "") {
						$data['max_age'] = set_value('max_age');
					}
				}

				$data['staff_required_head'] = 0;
				$data['staff_required_lead'] = 0;
				$data['staff_required_assistant'] = 0;
				$data['staff_required_participant'] = 0;
				$data['staff_required_observer'] = 0;

				if (set_value('staff_required_head') > 0) {
					$data['staff_required_head'] = set_value('staff_required_head');
				}
				if (set_value('staff_required_lead') > 0) {
					$data['staff_required_lead'] = set_value('staff_required_lead');
				}
				if (set_value('staff_required_assistant') > 0) {
					$data['staff_required_assistant'] = set_value('staff_required_assistant');
				}
				if (set_value('staff_required_participant') > 0) {
					$data['staff_required_participant'] = set_value('staff_required_participant');
				}
				if (set_value('staff_required_observer') > 0) {
					$data['staff_required_observer'] = set_value('staff_required_observer');
				}

				if ($lessonID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['bookingID'] = $bookingID;
					$data['blockID'] = $blockID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					// insert contact
					if ($add_address == 1) {
						$address_data = array(
							'orgID' => $booking_info->orgID,
							'byID' => $this->auth->user->staffID,
							'address1' => set_value('address_address1'),
							'address2' => set_value('address_address2'),
							'address3' => set_value('address_address3'),
							'town' => set_value('address_town'),
							'county' => set_value('address_county'),
							'postcode' => set_value('address_postcode'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);

						// if block has customer override, use addresses from that customer
						if (!empty($block_info->orgID)) {
							$address_data['orgID'] = $block_info->orgID;
						}

						$this->db->insert('orgs_addresses', $address_data);

						$data['addressID'] = $this->db->insert_id();

					}

					if ($lessonID == NULL) {

						// insert
						$query = $this->db->insert('bookings_lessons', $data);

						$lessonID = $this->db->insert_id();

						$date = $block_info->startDate;
						while (strtotime($date) <= strtotime($block_info->endDate)) {
							$day = strtolower(date('l', strtotime($date)));
							if ($day == set_value('day')) {
								$data = array(
									'bookingID' => $bookingID,
									'blockID' => $blockID,
									'lessonID' => $lessonID,
									'date' => $date,
									'attended' => 0,
									'byID' => $this->auth->user->staffID,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);

								$this->db->insert('bookings_attendance_numbers', $data);
							}
							$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
						}

						$affected_rows = $this->db->affected_rows();

						$just_added = TRUE;

					} else {

						$where = array(
							'lessonID' => $lessonID
						);

						// update
						$query = $this->db->update('bookings_lessons', $data, $where);

						$affected_rows = $this->db->affected_rows();

						// check if adjusting staff times
						if (set_value('adjust_staff_times') == 1 && (substr($lesson_info->startTime, 0, 5) != $data['startTime'] || substr($lesson_info->endTime, 0, 5) != $data['endTime'])) {
							// get session staff that will be effected
							$where = array(
								'lessonID' => $lessonID,
								'accountID' => $this->auth->user->accountID
							);
							$or_where = array();
							if (substr($lesson_info->startTime, 0, 5) != $data['startTime']) {
								$or_where[] = "`startTime` = " . $this->db->escape($lesson_info->startTime);
							}
							if (substr($lesson_info->endTime, 0, 5) != $data['endTime']) {
								$or_where[] = "`endTime` = " . $this->db->escape($lesson_info->endTime);
							}
							// if times not changed, skip
							if (count($or_where) == 0) {
								$or_where[] = "1 = 2";
							}
							$query = $this->db->from('bookings_lessons_staff')->where($where)->where('(' . implode(" OR ", $or_where) . ')', NULL, FALSE)->get();
							$affected_lesson_staff = array();
							$lesson_prev_times = array();
							if ($query->num_rows() > 0) {
								foreach ($query->result() as $row) {
									$affected_lesson_staff[$row->recordID] = $row->recordID;
									// store previous times
									$lesson_prev_times[$row->recordID] = array(
										'start' => $row->startTime,
										'end' => $row->endTime
									);
								}
							}

							// update start times if same as previous
							$where = array(
								'lessonID' => $lessonID,
								'startTime' => $lesson_info->startTime,
								'accountID' => $this->auth->user->accountID
							);
							$update_data = array(
								'startTime' => $data['startTime'],
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							// run query
							$query = $this->db->update('bookings_lessons_staff', $update_data, $where);

							// update end times if same as previous
							$where = array(
								'lessonID' => $lessonID,
								'endTime' => $lesson_info->endTime,
								'accountID' => $this->auth->user->accountID
							);
							$update_data = array(
								'endTime' => $data['endTime'],
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							// run query
							$query = $this->db->update('bookings_lessons_staff', $update_data, $where);

							$staff_times_adjusted = TRUE;

							// tell staff times have changed
							if ($this->settings_library->get('send_staff_changed_sessions') == 1 && set_value('notify_staff') == 1 && $this->crm_library->notify_staff_changed_sessions($affected_lesson_staff, $lesson_prev_times)) {
								$staff_notified = TRUE;
							}
						}
					}

					// add/update resources attachments
					$resources_attachments_posted = $this->input->post('resources_attachments');
					if (!is_array($resources_attachments_posted)) {
						$resources_attachments_posted = array();
					}
					foreach ($resources_attachments as $attachmentID => $attachment) {
						$where = array(
							'bookingID' => $bookingID,
							'lessonID' => $lessonID,
							'attachmentID' => $attachmentID,
							'accountID' => $this->auth->user->accountID
						);
						if (!in_array($attachmentID, $resources_attachments_posted)) {
							// not set, remove
							$this->db->delete('bookings_lessons_resources_attachments', $where);
						} else {
							// look up, see if site record already exists
							$res = $this->db->from('bookings_lessons_resources_attachments')->where($where)->get();

							$data = array(
								'bookingID' => $bookingID,
								'lessonID' => $lessonID,
								'attachmentID' => $attachmentID,
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);

							if ($res->num_rows() > 0) {
								$this->db->update('bookings_lessons_resources_attachments', $data, $where);
							} else {
								$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
								$this->db->insert('bookings_lessons_resources_attachments', $data);
							}
						}
					}

					// add/update org attachments
					$org_attachments_posted = $this->input->post('org_attachments');
					if (!is_array($org_attachments_posted)) {
						$org_attachments_posted = array();
					}
					foreach ($org_attachments as $attachmentID => $attachment) {
						$where = array(
							'bookingID' => $bookingID,
							'lessonID' => $lessonID,
							'attachmentID' => $attachmentID,
							'accountID' => $this->auth->user->accountID
						);
						if (!in_array($attachmentID, $org_attachments_posted)) {
							// not set, remove
							$this->db->delete('bookings_lessons_orgs_attachments', $where);
						} else {
							// look up, see if site record already exists
							$res = $this->db->from('bookings_lessons_orgs_attachments')->where($where)->get();

							$data = array(
								'bookingID' => $bookingID,
								'lessonID' => $lessonID,
								'attachmentID' => $attachmentID,
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);

							if ($res->num_rows() > 0) {
								$this->db->update('bookings_lessons_orgs_attachments', $data, $where);
							} else {
								$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
								$this->db->insert('bookings_lessons_orgs_attachments', $data);
							}
						}
					}

					// if inserted/updated
					if ($affected_rows == 1) {

						if (isset($just_added) && $just_added == TRUE) {
							if($ajaxFlag == 1){
								$this->message = ucwords(set_value('day')) . ' (' . $startTime . '-' . $endTime . ') has been created successfully';
							}else{
								$this->session->set_flashdata('success', ucwords(set_value('day')) . ' (' . $startTime . '-' . $endTime . ') has been created successfully');
							}
						} else {
							$success = ucwords(set_value('day')) . ' (' . $startTime . '-' . $endTime . ') has been updated successfully';
							if (isset($staff_times_adjusted)) {
								$success .= ' and staff times adjusted';
								if (isset($staff_notified)) {
									$success .= ' and notified';
								}
							}
							if($ajaxFlag == 1){
								$this->message = $success;
							}else{
								$this->session->set_flashdata('success', $success);
							}
						}

						// calc targets
						$this->crm_library->calc_targets($blockID);
						if($ajaxFlag == NULL){
							redirect($return_to);
						}
						return TRUE;
					} else {
						if($ajaxFlag == 1){
							$this->message = 'Error saving data, please try again.';
							$this->error = 1;
						}else{
							$this->session->set_flashdata('info', 'Error saving data, please try again.');
						}
					}
				}
			}
		}

		// check for flash data
		if($ajaxFlag == NULL){
			if ($this->session->flashdata('success')) {
				$success = $this->session->flashdata('success');
			} else if ($this->session->flashdata('info')) {
				$info = $this->session->flashdata('info');
			}
		}
		// orgs
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$orgs = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// addresses
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID
		);
		// if block has customer override, get addresses from that customer
		if (!empty($block_info->orgID)) {
			$where['orgID'] = $block_info->orgID;
		}
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc, town asc, county asc, postcode asc')->get();

		// activities
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_where = [
			'`active` = 1'
		];
		if ($lessonID != NULL) {
			$or_where[] = '`activityID` = ' . $this->db->escape($lesson_info->activityID);
		}
		$where['(' . implode(' OR ', $or_where) . ')'] = NULL;
		$activities = $this->db->from('activities')->where($where, NULL, FALSE)->order_by('name asc')->get();

		// session types
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_where = [
			'`active` = 1'
		];
		if ($lessonID != NULL) {
			$or_where[] = '`typeID` = ' . $this->db->escape($lesson_info->typeID);
		}
		$where['(' . implode(' OR ', $or_where) . ')'] = NULL;
		$lesson_types = $this->db->from('lesson_types')->where($where, NULL, FALSE)->order_by('name asc')->get();

		// resources attachments
		$resources_attachments_array = array();
		$where = array(
			'bookingID' => $bookingID,
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_lessons_resources_attachments')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$resources_attachments_array[] = $row->attachmentID;
			}
		}

		// org attachments
		$org_attachments_array = array();
		$where = array(
			'bookingID' => $bookingID,
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_lessons_orgs_attachments')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$org_attachments_array[] = $row->attachmentID;
			}
		}

		$required_staff_for_session = $this->settings_library->get_required_staff_for_session();

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
			'lesson_info' => $lesson_info,
			'block_info' => $block_info,
			'orgs' => $orgs,
			'addresses' => $addresses,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'lessonID' => $lessonID,
			'booking_type' => $booking_info->type,
			'add_address' => $add_address,
			'org_attachments' => $org_attachments,
			'org_attachments_array' => $org_attachments_array,
			'resources_attachments' => $resources_attachments,
			'resources_attachments_array' => $resources_attachments_array,
			'activities' => $activities,
			'lesson_types' => $lesson_types,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'required_staff_for_session' => $required_staff_for_session
		);

		// load view
		if($ajaxFlag == NULL){
			$this->crm_view('bookings/session', $data);
		}
	}

	/**
	 * delete a lesson
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function remove($lessonID = NULL) {

		// check params
		if (empty($lessonID)) {
			show_404();
		}

		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_lessons')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$lesson_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings_lessons', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ') has been removed successfully.');
			} else {
				// try to force delete if required
				if ($this->crm_library->last_segment() === 'force') {
					force_delete_db_dependants('bookings_lessons', $lessonID);
					// redirect to normal delete to delete parent
					redirect(str_replace('/force', '', current_url()));
					exit();
				}
				// get dependant table conflicts
				if ($db_error = get_friendly_db_error('bookings_lessons', $lessonID, $lesson_info, ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ')')) {
					$this->session->set_flashdata('error', $db_error);
				}
			}

			// calc targets
			$this->crm_library->calc_targets($lesson_info->blockID);

			// determine which page to send the user back to
			$redirect_to = 'bookings/sessions/' . $lesson_info->bookingID . '/' . $lesson_info->blockID;

			redirect($redirect_to);
		}
	}

	// bulk actions
	private $blockID;
	private $block_info;
	private $bookingID;
	private $booking_info;
	private $bulk_data;
	private $bulk_action;
	private $lessons = array();

	/**
	 * bulk actions for lessons
	 * @param  integer $blockID
	 * @return mixed
	 */
	public function bulk($blockID = NULL) {

		// check params
		if (empty($blockID)) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($blockID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		$this->error = 0;
		$this->message = '';
		$this->exclude_actions = array('cancellation','staffchange','confirmation','dbs','staff', 'note');

		// match
		foreach ($query->result() as $row) {
			$this->block_info = $row;
			$bookingID = $row->bookingID;
		}

		// save
		$this->blockID = $blockID;

		// look up
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);
		$query = $this->db->select('bookings.*, brands.name as brand, orgs.name as org')->from('bookings')->join('brands', 'bookings.brandID = brands.brandID', 'left')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$this->booking_info = $row;
		}

		// save
		$this->bookingID = $bookingID;

		// if adding lesson, pass on to edit controller
		if ($this->input->post('day') != '' && empty($this->input->post('action'))) {
			$this->edit(NULL, $blockID, 1);
			$array = array("error" => $this->error,
			"message" => $this->message);
			echo json_encode($array);
			die();
		}

		// save bulk data
		$this->bulk_data['lessons'] = array();
		$this->bulk_data['action'] = $this->input->post('action');
		$this->bulk_data['staffID'] = $this->input->post('staffID');
		$this->bulk_data['replacementID'] = $this->input->post('replacementID');
		$this->bulk_data['staff_type'] = $this->input->post('staff_type');
		$this->bulk_data['change_addressID'] = $this->input->post('change_addressID');
		$this->bulk_data['resources_attachments'] = $this->input->post('resources_attachments');
		$this->bulk_data['coach_access'] = $this->input->post('coach_access');
		$this->bulk_data['lesson_requirements'] = $this->input->post('lesson_requirements');
		$this->bulk_data['from'] = $this->input->post('from');
		$this->bulk_data['to'] = $this->input->post('to');
		$this->bulk_data['from_date'] = $this->input->post('from_date');
		$this->bulk_data['to_date'] = $this->input->post('to_date');
		$this->bulk_data['price'] = $this->input->post('price');
		$this->bulk_data['target_participants'] = $this->input->post('bulk_target_participants');
		$this->bulk_data['booking_cutoff'] = $this->input->post('booking_cutoff');
		$this->bulk_data['min_age'] = $this->input->post('min_age');
		$this->bulk_data['max_age'] = $this->input->post('max_age');
		$this->bulk_data['charge'] = $this->input->post('newcharge');
		$this->bulk_data['charge_other'] = $this->input->post('newcharge_other');
		$this->bulk_data['activityID'] = $this->input->post('newactivityID');
		$this->bulk_data['activity_other'] = $this->input->post('newactivity_other');
		$this->bulk_data['typeID'] = $this->input->post('newtypeID');
		$this->bulk_data['type_other'] = $this->input->post('newtype_other');
		$this->bulk_data['group'] = $this->input->post('newgroup');
		$this->bulk_data['group_other'] = $this->input->post('newgroup_other');
		$this->bulk_data['activity_desc'] = $this->input->post('newactivity_desc');
		$this->bulk_data['remove_staff'] = $this->input->post('remove_staff');
		$this->bulk_data['minstaff_head'] = $this->input->post('minstaff_head');
		$this->bulk_data['minstaff_lead'] = $this->input->post('minstaff_lead');
		$this->bulk_data['minstaff_assistant'] = $this->input->post('minstaff_assistant');
		$this->bulk_data['minstaff_participant'] = $this->input->post('minstaff_participant');
		$this->bulk_data['minstaff_observer'] = $this->input->post('minstaff_observer');
		$this->bulk_data['location'] = $this->input->post('newlocation');
		$this->bulk_data['class_size'] = $this->input->post('newclass_size');
		$this->bulk_data['day'] = $this->input->post('newday');
		$this->bulk_data['startTimeH'] = $this->input->post('newstartTimeH');
		$this->bulk_data['startTimeM'] = $this->input->post('newstartTimeM');
		$this->bulk_data['endTimeH'] = $this->input->post('newendTimeH');
		$this->bulk_data['endTimeM'] = $this->input->post('newendTimeM');
		$this->bulk_data['startDate'] = $this->input->post('newstartDate');
		$this->bulk_data['endDate'] = $this->input->post('newendDate');
		$this->bulk_data['offer_accept_grouped'] = $this->input->post('offer_accept_grouped');
		$this->bulk_data['bulk_contactID'] = $this->input->post('bulk_contactID');
		$this->bulk_data['dbs_contactID'] = $this->bulk_data['bulk_contactID'];
		$this->bulk_data['adjust_staff_times'] = $this->input->post('adjust_staff_times');
		$this->bulk_data['notify_staff'] = $this->input->post('notify_staff');
		$this->bulk_data['combine_sessions'] = $this->input->post('combine-sessions');
		$this->bulk_data['salaried'] = $this->input->post('salaried');
		$this->bulk_data['offer_accept_salaried'] = $this->input->post('offer_accept_salaried');

		// check lessons
		$lessons = $this->input->post('lessons');

		// check for data from session (if not posted)
		if (!$this->input->post() && $this->crm_library->last_segment() === 'force') {
			$this->bulk_data['action'] = $this->session->flashdata('bulk_action');
			$lessons = $this->session->flashdata('bulk_lessons');
		}

		// check if array
		if (!is_array($lessons)) {
			if(!in_array($this->bulk_action["action"], $this->exclude_actions)){
				$this->error = 1;
				$this->message = 'Please select at least one session.';
				$array = array('message' => $this->message,
				"error" => $this->error);
				echo json_encode($array);
				exit;
			}else{
				$this->session->set_flashdata('error', 'Please select at least one session.');
				$this->session->set_flashdata('bulk_data', $this->bulk_data);
				redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
			}
		}

		// save action and lessons
		$this->bulk_action = $this->bulk_data['action'];
		$this->lessons = $lessons;

		// loop through all
		$verified_lessons = array();

		foreach ($lessons as $lessonID) {
			$where = array(
				'bookings_lessons.bookingID' => $bookingID,
				'bookings_lessons.lessonID' => $lessonID,
				'bookings_lessons.accountID' => $this->auth->user->accountID
			);

			$res = $this->db->select('bookings_lessons.*, activities.name as activity')->from('bookings_lessons')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->where($where)->limit(1)->get();

			if ($res->num_rows() == 1) {
				foreach ($res->result() as $lesson_info) {
					$verified_lessons[$lessonID] = $lesson_info;
				}
			}
		}

		// save
		$this->lessons = $verified_lessons;

		if (count($this->lessons) == 0) {
			if(!in_array($this->bulk_action["action"], $this->exclude_actions)){
				$this->error = 1;
				$this->message = 'Please select at least one session.';
				$array = array('message' => $this->message,
				"error" => $this->error);
				echo json_encode($array);
				exit;
			}else{
				$this->session->set_flashdata('error', 'Please select at least one session.');
				$this->session->set_flashdata('bulk_data', $this->bulk_data);
				redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
			}
		}

		// store lessons
		$this->bulk_data['lessons'] = $this->lessons;

		// switch action
		switch ($this->bulk_data['action']) {
			case 'changeaddress':
				$this->bulk_address();
				break;
			case 'lessonplans':
				$this->bulk_plans();
				break;
			case 'coachaccess':
				$this->bulk_coachaccess();
				break;
			case 'requirements':
				$this->bulk_requirements();
				break;
			case 'staff':
				if (!check_uk_date($this->bulk_data['from_date']) || !check_uk_date($this->bulk_data['to_date'])) {
					$this->session->set_flashdata('error', 'From and to dates are required.');
					$this->session->set_flashdata('bulk_data', $this->bulk_data);
					redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
				}
				if (strtotime(uk_to_mysql_date($this->bulk_data['from_date'])) > strtotime(uk_to_mysql_date($this->bulk_data['to_date']))) {
					$this->session->set_flashdata('error', 'To must be on or after from date.');
					$this->session->set_flashdata('bulk_data', $this->bulk_data);
					redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
				}
				if (strtotime(uk_to_mysql_date($this->bulk_data['from_date'])) < strtotime($this->block_info->startDate) || strtotime(uk_to_mysql_date($this->bulk_data['to_date'])) > strtotime($this->block_info->endDate)) {
					$this->session->set_flashdata('error', 'Dates must be within block dates.');
					$this->session->set_flashdata('bulk_data', $this->bulk_data);
					redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
				}
				$this->bulk_staff();
				break;
			case 'removestaff':
				$this->bulk_removestaff();
				break;
			case 'remove':
				$this->bulk_remove();
				break;
			case 'removedates':
				$this->bulk_removedates();
				break;
			case 'duplicate':
				$this->bulk_duplicate();
				break;
			case 'note':
				$this->bulk_note();
				break;
			case 'dbs':
				$this->bulk_dbs();
				break;
			case 'confirmation':
				$this->bulk_confirmation();
				break;
			case 'cancellation':
			case 'staffchange';
				if (!check_uk_date($this->bulk_data['from']) || !check_uk_date($this->bulk_data['to'])) {
					$this->session->set_flashdata('error', 'From and to dates are required.');
					$this->session->set_flashdata('bulk_data', $this->bulk_data);
					redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
				}
				if (strtotime(uk_to_mysql_date($this->bulk_data['from'])) > strtotime(uk_to_mysql_date($this->bulk_data['to']))) {
					$this->session->set_flashdata('error', 'To must be on or after from date.');
					$this->session->set_flashdata('bulk_data', $this->bulk_data);
					redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
				}

				if (strtotime(uk_to_mysql_date($this->bulk_data['from'])) < strtotime($this->block_info->startDate) || strtotime(uk_to_mysql_date($this->bulk_data['to'])) > strtotime($this->block_info->endDate)) {
					$this->session->set_flashdata('error', 'Dates must be within block dates.');
					$this->session->set_flashdata('bulk_data', $this->bulk_data);
					redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
				}
				$this->bulk_exception($this->bulk_data['action']);
				break;
			case 'price':
				$this->bulk_price();
				break;
			case 'target_participants':
				$this->bulk_target_participants();
				break;
			case 'booking_cutoff':
				$this->bulk_booking_cutoff();
				break;
			case 'min_age':
				$this->bulk_min_age();
				break;
			case 'max_age':
				$this->bulk_max_age();
				break;
			case 'charge':
				$this->bulk_charge();
				break;
			case 'activity':
				$this->bulk_activity();
				break;
			case 'activity_desc':
				$this->bulk_activity_desc();
				break;
			case 'type':
				$this->bulk_type();
				break;
			case 'group':
				$this->bulk_group();
				break;
			case 'day':
				$this->bulk_day();
				break;
			case 'dates':
				$this->bulk_dates();
				break;
			case 'times':
				$this->bulk_times();
				break;
			case 'location':
				$this->bulk_location();
				break;
			case 'class_size':
				$this->bulk_class_size();
				break;
			case 'minstaff':
				$this->bulk_minstaff();
				break;
			case 'offer_accept':
				$this->bulk_offer_accept();
				break;
			case 'offer_accept_manual':
				$this->bulk_offer_accept_manual();
				break;
			default:
				$this->session->set_flashdata('error', 'Please select an action.');
				$this->session->set_flashdata('bulk_data', $this->bulk_data);
				redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
				break;
		}

		if(!in_array($this->bulk_data["action"], $this->exclude_actions)){
			$array = array("message" => $this->message,
			"error" => $this->error);

			echo json_encode($array);
		}

	}

	/**
	 * bulk add staff
	 * @return mixed
	 */
	private function bulk_staff() {

		// get params
		$blockID = $this->blockID;
		$block_info = $this->block_info;
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$lessons = $this->lessons;

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Staff';
		$submit_to = 'sessions/bulk/' . $blockID;
		$return_to = 'bookings/sessions/' . $bookingID . '/' . $blockID;
		$icon = 'user';
		$tab = 'lessons';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $blockID] = 'Sessions';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// load library
		$this->load->library('offer_accept_library');

		// if posted
		if ($this->input->post('process') == 1) {

			// set validation rules
			foreach ($lessons as $lessonID => $lesson_info) {
				$lesson_desc = ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ')';

				$this->form_validation->set_rules('from_' . $lessonID, $lesson_desc . ' - Date From', 'trim|xss_clean|required|callback_check_date|callback_within_block[' . $blockID . ']');
				$this->form_validation->set_rules('fromH_' . $lessonID, $lesson_desc . ' - Time From - Hours', 'trim|xss_clean|required');
				$this->form_validation->set_rules('fromM_' . $lessonID, $lesson_desc . ' - Time From - Minutes', 'trim|xss_clean|required');
				$this->form_validation->set_rules('to_' . $lessonID, $lesson_desc . ' - Date To', 'trim|xss_clean|required|callback_check_date|callback_exception_datetime|callback_within_block[' . $blockID . ']');
				$this->form_validation->set_rules('toH_' . $lessonID, $lesson_desc . ' - Time To - Hours', 'trim|xss_clean|required');
				$this->form_validation->set_rules('toM_' . $lessonID, $lesson_desc . ' - Time To - Minutes', 'trim|xss_clean|required');
				$this->form_validation->set_rules('type_' . $lessonID, $lesson_desc . ' - Type', 'trim|xss_clean|required');
				$this->form_validation->set_rules('staffID_' . $lessonID, $lesson_desc . ' - Staff', 'trim|xss_clean|required|callback_check_staff_not_already_on[' . $lessonID . ']');
				$this->form_validation->set_rules('comment_' . $lessonID, $lesson_desc . ' - Comment', 'trim|xss_clean');
				$this->form_validation->set_rules('salaried_' . $lessonID, $lesson_desc . ' - Salaried', 'trim|xss_clean');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				$staff_inserted = array();

				foreach ($lessons as $lessonID => $lesson_info) {
					// work out from and to
					$fromTime = set_value('fromH_' . $lessonID) . ':' . set_value('fromM_' . $lessonID);
					$toTime = set_value('toH_' . $lessonID) . ':' . set_value('toM_' . $lessonID);

					// all ok, prepare data
					$data = array(
						'bookingID' => $bookingID,
						'lessonID' => $lessonID,
						'startDate' => uk_to_mysql_date(set_value('from_' . $lessonID)),
						'endDate' => uk_to_mysql_date(set_value('to_' . $lessonID)),
						'startTime' => $fromTime,
						'endTime' => $toTime,
						'staffID' => set_value('staffID_' . $lessonID),
						'type' => set_value('type_' . $lessonID),
						'comment' => set_value('comment_' . $lessonID),
						'salaried' => 0,
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'byID' => $this->auth->user->staffID,
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID
					);

					if ($this->settings_library->get('salaried_sessions') == 1 && $this->auth->has_features('payroll') && set_value('salaried_' . $lessonID) == 1) {
						$data['salaried'] = 1;
					}

					// final check for errors
					if (count($errors) == 0) {

						// insert
						$query = $this->db->insert('bookings_lessons_staff', $data);

						if ($this->db->affected_rows() == 1) {
							$staff_inserted[] = $this->db->insert_id();

							// run offer/accept in case still offering
							if ($this->auth->has_features(array('offer_accept'))) {
								$this->offer_accept_library->offer($lessonID);
							}
						}
					}
				}

				if (count($staff_inserted) == 0) {

					$this->session->set_flashdata('error', 'No staff could be assigned to any lessons.');

				} else {

					$success = count($staff_inserted) . ' staff';
					if(count($staff_inserted) == 1) {
						$success .= ' has';
					} else {
						$success .= ' have';
					}
					$success .= ' been assigned';
					if ($this->settings_library->get('send_staff_new_sessions') == 1 && set_value('notify_staff') == 1 && $this->crm_library->notify_staff_new_sessions($staff_inserted)) {
						$success .= ' and notified';
					}
					$success .= ' successfully';

					$this->session->set_flashdata('success', $success);
				}

				// calc targets
				$this->crm_library->calc_targets($blockID);

				redirect('bookings/sessions/' . $bookingID . '/' . $blockID);

			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// staff
		$where = array(
			'active' => 1,
			'non_delivery !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// get staff on sessions by type
		$lesson_staff_by_type = array();
		$where = array(
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons_staff.accountID' => $this->auth->user->accountID
		);
		$res_staff_by_type = $this->db->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->group_by('bookings_lessons_staff.recordID')->get();
		if ($res_staff_by_type->num_rows() > 0) {
			foreach ($res_staff_by_type->result() as $row) {
				$lesson_staff_by_type[$row->lessonID][$row->type][$row->staffID] = $row->staffID;
			}
		}

		// get vals from bulk form
		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');
		$staffID = $this->input->post('staffID');
		$staff_type = $this->input->post('staff_type');
		$salaried = $this->input->post('salaried');

		$required_staff_for_session = $this->settings_library->get_required_staff_for_session();

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
			'staff' => $staff_list,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'lessons' => $lessons,
			'staffID' => $staffID,
			'staff_type' => $staff_type,
			'salaried' => $salaried,
			'breadcrumb_levels' => $breadcrumb_levels,
			'lesson_staff_by_type' => $lesson_staff_by_type,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'required_staff_for_session' => $required_staff_for_session
		);

		// load view
		$this->crm_view('sessions/bulk-staff', $data);
	}

	/**
	 * bulk add note
	 * @return mixed
	 */
	private function bulk_note() {

		// get params
		$blockID = $this->blockID;
		$block_info = $this->block_info;
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$lessons = $this->lessons;

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Note';
		$submit_to = 'sessions/bulk/' . $blockID;
		$return_to = 'bookings/sessions/' . $bookingID . '/' . $blockID;
		$icon = 'book';
		$tab = 'lessons';
		$current_page = $booking_info->type . 's';
		if ($booking_info->project == 1) {
			$current_page = 'projects';
		}
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post('process') == 1) {

			// set validation rules
			$this->form_validation->set_rules('summary', 'Summary', 'trim|xss_clean|required');
			$this->form_validation->set_rules('content', 'Details', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				$notes_added = 0;

				foreach ($lessons as $lessonID => $lesson_info) {

					// all ok, prepare data
					$data = array(
						'bookingID' => $bookingID,
						'lessonID' => $lessonID,
						'byID' => $this->auth->user->staffID,
						'summary' => set_value('summary'),
						'content' => $this->input->post('content', FALSE),
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID
					);

					// final check for errors
					if (count($errors) == 0) {

						// insert
						$query = $this->db->insert('bookings_lessons_notes', $data);

						if ($this->db->affected_rows() == 1) {
							$notes_added++;
						}
					}
				}

				if ($notes_added == 0) {

					$this->session->set_flashdata('error', 'No notes could be added to any lessons.');

				} else {

					$staff_text = $notes_added . ' notes';

					if($notes_added == 1) {
						$staff_text .= ' has';
					} else {
						$staff_text .= ' have';
					}

					$this->session->set_flashdata('success', $staff_text . ' have been added successfully.');
				}

				// calc targets
				$this->crm_library->calc_targets($blockID);

				redirect('bookings/sessions/' . $bookingID . '/' . $blockID);

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
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'lessons' => $lessons,
			'type' => 'note',
			'read_only' => FALSE,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('sessions/note', $data);
	}

	/**
	 * bulk send dbs
	 * @return mixed
	 */
	private function bulk_dbs() {

		// check if allowed
		if ($this->settings_library->get('send_dbs') != 1) {
			show_403();
		}

		// get params
		$blockID = $this->blockID;
		$block_info = $this->block_info;
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$lessons = $this->lessons;

		$contactID = $this->bulk_data['dbs_contactID'];

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Send DBS';
		$submit_to = 'sessions/bulk/' . $blockID;
		$return_to = 'bookings/sessions/' . $bookingID . '/' . $blockID;
		$icon = 'envelope';
		$tab = 'lessons';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $blockID] = 'Sessions';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// look up contact
		$where = array(
			'contactID' => $contactID,
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		if (!empty($block_info->orgID) && $block_info->orgID != $booking_info->orgID) {
			$where['orgID'] = $block_info->orgID;
		}
		$res = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->session->set_flashdata('error', 'Please specify a contact.');
			redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
			return;
		}

		foreach ($res->result() as $contact_info) {}

		// look up staff on lessons
		$lesson_ids = array_keys($lessons);

		$where = array(
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);
		$res_lesson_staff = $this->db->select('staff.*')->from('staff')->join('bookings_lessons_staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner')->where($where)->where_in('bookings_lessons.lessonID',$lesson_ids)->order_by('staff.first asc, staff.surname asc')->group_by('staff.staffID')->get();

		$lesson_staff = array();
		$all_valid = TRUE;

		if ($res_lesson_staff->num_rows() > 0) {
			foreach ($res_lesson_staff->result() as $row) {
				$lesson_staff[$row->staffID] = array(
					'name' => $row->first . ' ' . $row->surname,
					'dbs_no' => 'Unknown',
					'dbs_issue_date' => 'Unknown',
					'dbs_expiry_date' => 'Unknown'
				);

				if ($row->qual_fsscrb == 1) {
					if (!empty($row->qual_fsscrb_ref)) {
						$lesson_staff[$row->staffID]['dbs_no'] = $row->qual_fsscrb_ref;
					}
					if (!empty($row->qual_fsscrb_issue_date)) {
						$lesson_staff[$row->staffID]['dbs_issue_date'] = mysql_to_uk_date($row->qual_fsscrb_issue_date);
					}
					if (!empty($row->qual_fsscrb_expiry_date)) {
						$lesson_staff[$row->staffID]['dbs_expiry_date'] = mysql_to_uk_date($row->qual_fsscrb_expiry_date);
						// check expired
						if (strtotime($row->qual_fsscrb_expiry_date) < time()) {
							$all_valid = FALSE;
						}
					}
				} else if ($row->qual_othercrb == 1) {
					if (!empty($row->qual_othercrb_ref)) {
						$lesson_staff[$row->staffID]['dbs_no'] = $row->qual_othercrb_ref;
					}
					if (!empty($row->qual_othercrb_issue_date)) {
						$lesson_staff[$row->staffID]['dbs_issue_date'] = mysql_to_uk_date($row->qual_othercrb_issue_date);
					}
					if (!empty($row->qual_othercrb_expiry_date)) {
						$lesson_staff[$row->staffID]['dbs_expiry_date'] = mysql_to_uk_date($row->qual_othercrb_expiry_date);
						// check expired
						if (strtotime($row->qual_othercrb_expiry_date) < time()) {
							$all_valid = FALSE;
						}
					}
				}

				// if still unknown, not valid
				if ($lesson_staff[$row->staffID]['dbs_no'] == 'Unknown' || $lesson_staff[$row->staffID]['dbs_issue_date'] == 'Unknown' || $lesson_staff[$row->staffID]['dbs_expiry_date'] == 'Unknown') {
					$all_valid = FALSE;
				}
			}
		} else {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->session->set_flashdata('error', 'No staff in selected sessions');
			redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
		}

		// if posted
		if ($this->input->post('process') == 1) {

			// set validation rules
			$this->form_validation->set_rules('subject', 'Subject', 'trim|xss_clean|required');
			$this->form_validation->set_rules('content', 'Content', 'trim|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// get html email and convert to plain text
				$email_html = $this->input->post('content', FALSE);
				$this->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				if ($this->crm_library->send_email($contact_info->email, set_value('subject', NULL, FALSE), $email_html, array(), FALSE, $booking_info->accountID, $booking_info->brandID)) {
					// save
					$data = array(
						'orgID' => $booking_info->orgID,
						'contactID' => $contact_info->contactID,
						'byID' => $this->auth->user->staffID,
						'type' => 'email',
						'destination' => $contact_info->email,
						'subject' => set_value('subject'),
						'contentHTML' => $email_html,
						'contentText' => $email_plain,
						'status' => 'sent',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID
					);
					// if block has customer override, use org from that customer
					if (!empty($block_info->orgID)) {
						$where['orgID'] = $block_info->orgID;
					}

					$this->db->insert('orgs_notifications', $data);

					$this->session->set_flashdata('success', 'DBS has been sent successfully.');
				} else {
					$this->session->set_flashdata('error', 'DBS could not be sent');
				}

				redirect('bookings/sessions/' . $bookingID . '/' . $blockID);

			}
		} else if ($all_valid !== TRUE) {
			$info = 'Some DBSs are missing information or expired. Please check before sending.';
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// set defaults
		$subject = $this->settings_library->get('email_senddbs_subject');
		$content = $this->settings_library->get('email_senddbs');

		// set smart tags
		$smart_tags = array(
			'main_contact' => $contact_info->name,
			'contact_name' => $contact_info->name,
			'block_name' => $block_info->name,
			'details' => NULL
		);

		// get details
		$smart_tags['details'] = '<table width="100%" border="1">
		<tr>
			<th scope="col">Staff Name</th>
			<th scope="col">DBS No.</th>
			<th scope="col">Issue Date</th>
			<th scope="col">Expiry Date</th>
		</tr>';

		foreach ($lesson_staff as $staff) {

			$smart_tags['details'] .= '<tr>
				<td>' . $staff['name'] . '</td>
				<td>' . $staff['dbs_no'] . '</td>
				<td>' . $staff['dbs_issue_date'] . '</td>
				<td>' . $staff['dbs_expiry_date'] . '</td>
			</tr>';
		}

		$smart_tags['details'] .= '</table>';

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$content = str_replace('<p>{' . $key . '}</p>', $value, $content);
			$content = str_replace('{' . $key . '}', $value, $content);
		}

		// replace smart tags in subject
		unset($smart_tags['details']);
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
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
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'lessons' => $lessons,
			'subject' => $subject,
			'content' => $content,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'bulk_contactID' => $contactID,
		);

		// load view
		$this->crm_view('sessions/bulk-dbs', $data);
	}

	/**
	 * bulk send confirmation
	 * @return mixed
	 */
	private function bulk_confirmation() {

		// check if allowed
		if ($this->settings_library->get('send_new_booking') != 1) {
			show_403();
		}

		// get params
		$blockID = $this->blockID;
		$block_info = $this->block_info;
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$lessons = $this->lessons;

		$contactID = $this->bulk_data['bulk_contactID'];

		// look up contact
		$where = array(
			'contactID' => $contactID,
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID
		);
		if (!empty($block_info->orgID) && $block_info->orgID != $booking_info->orgID) {
			$where['orgID'] = $block_info->orgID;
		}
		$res = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->session->set_flashdata('error', 'Please specify a contact.');
			redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
			return;
		}

		foreach ($res->result() as $contact_info) {}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Send Confirmation';
		$submit_to = 'sessions/bulk/' . $blockID;
		$return_to = 'bookings/sessions/' . $bookingID . '/' . $blockID;
		$icon = 'envelope';
		$tab = 'lessons';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $blockID] = 'Sessions';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// look up org
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID
		);
		// if block has customer override, get from that customer
		if (!empty($block_info->orgID)) {
			$where['orgID'] = $block_info->orgID;
		}
		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			// this should never happen
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->session->set_flashdata('error', 'No organisation associated with this block');
			redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
		}

		foreach ($res->result() as $org_info) {}

		// if posted
		if ($this->input->post('process') == 1) {

			// set validation rules
			$this->form_validation->set_rules('subject', 'Subject', 'trim|xss_clean|required');
			$this->form_validation->set_rules('content', 'Content', 'trim|required');
			$this->form_validation->set_rules('cc[]', 'CC', 'trim|xss_clean');
			$this->form_validation->set_rules('bcc[]', 'BCC', 'trim|xss_clean');
			$this->form_validation->set_rules('extra-cc[]', 'CC', 'trim|xss_clean');
			$this->form_validation->set_rules('extra-bcc[]', 'BCC', 'trim|xss_clean');
			$this->form_validation->set_rules('addition_attachment[]', 'Attachments', 'trim|xss_clean');

			$extraCC = set_value('extra-cc');
			$extraBCC = set_value('extra-bcc');

			$emailNames = ['extraCC', 'extraBCC'];
			$emailsToValidate = [];

			foreach ($emailNames as $name) {
				if (!empty(${$name})) {
					foreach (${$name} as $email) {
						if (!empty($email)){
							$emailsToValidate[] = $email;
						}
					}
				}
			}

			$customErrors = $this->crm_library->validateArrayEmails($emailsToValidate);

			if (!empty($customErrors)) {
				$customErrors = ['A valid email address must be entered in the CC or BCC fields'];
			}

			$attachments = array();
			$attachment_data = array();
			if (!empty($_FILES['files']['name'][0])) {
				$this->load->library('upload');
				// check for upload
				$upload_res = $this->crm_library->handle_multi_upload();

				if ($upload_res == NULL) {
					$customErrors[] = strip_tags($this->upload->display_errors());
				} else {
					foreach ($upload_res as $res) {
						// prepare table
						$attachment_data[] = [
							'byID' => $this->auth->user->staffID,
							'name' => $res['client_name'],
							'path' => $res['raw_name'],
							'type' => $res['file_type'],
							'size' => $res['file_size']*1024,
							'ext' => substr($res['file_ext'], 1),
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s')
						];

						// attach to email
						$attachments[UPLOADPATH . $res['raw_name']] = $res['client_name'];
					}
				}
			}

			if ($this->form_validation->run() == FALSE || !empty($customErrors)) {
				$errors = !empty($customErrors) ? $customErrors : $this->form_validation->error_array();
			} else {

				// get html email and convert to plain text
				$email_html = $this->input->post('content', FALSE);
				$this->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				$additional_attachments = set_value('addition_attachment');

				if ($additional_attachments) {
					foreach ($additional_attachments as $attachment) {
						$file = $this->attachment_library->getAttachmentInfo($attachment);
						if (!empty($file)) {
							$attachment_data[] = [
								'byID' => $this->auth->user->staffID,
								'name' => $file->name,
								'path' => $file->path,
								'type' => $file->type,
								'size' => $file->size*1024,
								'ext' => substr($file->ext, 1),
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),

							];
							$attachments[UPLOADPATH . $file->path] = $file->name;
						}
					}
				}

				// get customer attachments
				$customerAttachmentIDs = array();
				$where = array(
					'orgID' => $org_info->orgID,
					'sendwithconfirmation' => 1,
					'accountID' => $this->auth->user->accountID
				);
				$res = $this->db->from('orgs_attachments')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$attachments[UPLOADPATH . $row->path] = $row->name;
						$customerAttachmentIDs[] = $row->attachmentID;
					}
				}

				// get booking attachments
				$bookingAttachmentIDs = array();
				$where = array(
					'bookings_attachments.accountID' => $this->auth->user->accountID,
					'bookings_attachments_blocks.blockID' => $blockID
				);
				$res = $this->db->select('bookings_attachments.*')
				->from('bookings_attachments')
				->join('bookings_attachments_blocks', 'bookings_attachments.attachmentID = bookings_attachments_blocks.attachmentID', 'inner')
				->where($where)
				->group_by('bookings_attachments.attachmentID')
				->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$attachments[UPLOADPATH . $row->path] = $row->name;
						$bookingAttachmentIDs[] = $row->attachmentID;
					}
				}

				// send different files depending on brand
				$resourceAttachmentIDs = array();
				if ($this->auth->has_features('resources')) {
					$where = array(
						'files.accountID' => $this->auth->user->accountID,
						'files_brands.brandID' => $booking_info->brandID,
					);

					$res = $this->db->select('files.*')->from('files')->join('files_brands', 'files.attachmentID = files_brands.attachmentID', 'left')->where($where)->group_by('files.attachmentID')->get();

					if ($res->num_rows() > 0) {
						foreach ($res->result() as $row) {
							$attachments[UPLOADPATH . $row->path] = $row->name;
							$resourceAttachmentIDs[] = $row->attachmentID;
						}
					}
				}

				$cc = set_value('cc');
				$bcc = set_value('bcc');

				$ccEmails = [];
				if (!empty($cc)) {
					// look up contact
					foreach ($cc as $recipient) {
						$contact_info = $this->orgs_library->findContactById($recipient);

						if (empty($contact_info)) {
							continue;
						}

						$ccEmails[] = $contact_info->email;
					}
				}

				$extraCC = set_value('extra-cc');

				if (!empty($extraCC)) {
					foreach ($extraCC as $email) {
						$ccEmails[] = $email;
					}
				}

				$bccEmails = [];
				if (!empty($bcc)) {
					// look up contact
					foreach ($bcc as $recipient) {
						$contact_info = $this->orgs_library->findContactById($recipient);

						if (empty($contact_info)) {
							continue;
						}

						$bccEmails[] = $contact_info->email;
					}
				}

				$extraBCC = set_value('extra-bcc');

				if (!empty($extraBCC)) {
					foreach ($extraBCC as $email) {
						$bccEmails[] = $email;
					}
				}

				if ($this->crm_library->send_email($contact_info->email, set_value('subject', NULL, FALSE), $email_html, $attachments, FALSE, $booking_info->accountID, $booking_info->brandID, $bccEmails, $ccEmails)) {
					// save
					$this->notifications_library->addCustomerEmailRecord(
						$contactID,
						$org_info->orgID,
						$contact_info->email,
						$email_html,
						$email_plain,
						$attachment_data,
						$customerAttachmentIDs,
						$resourceAttachmentIDs,
						$bookingAttachmentIDs
					);

					$this->session->set_flashdata('success', 'Confirmation has been sent successfully.');
				} else {
					$this->session->set_flashdata('error', 'Confirmation could not be sent');
				}
			}

			if (empty($errors)) {
				redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// set defaults
		$subject = $this->settings_library->get('email_new_booking_subject');
		$content = $this->settings_library->get('email_new_booking');

		// set smart tags
		$smart_tags = array(
			'main_contact' => $contact_info->name,
			'contact_name' => $contact_info->name,
			'org_name' => $org_info->name,
			'brand' => $booking_info->brand,
			'date_description' => ' between ' . mysql_to_uk_date($block_info->startDate) . ' and ' . mysql_to_uk_date($block_info->endDate),
			'details' => NULL
		);

		if ($block_info->startDate == $block_info->endDate) {
			$smart_tags['date_description'] = ' on ' . mysql_to_uk_date($block_info->startDate);
		}

		$show_participant_column = FALSE;
		// get participant counts
		$participants = array();
		$where = array(
			'bookings_lessons.accountID' => $this->auth->user->accountID,
			'bookings_lessons.blockID' => $block_info->blockID,
			'bookings_cart.type' => 'booking'
		);
		$res_participants = $this->db->select('bookings_lessons.lessonID, COUNT(DISTINCT ' . $this->db->dbprefix('bookings_cart_sessions') . '.contactID) as participants, COUNT(DISTINCT ' . $this->db->dbprefix('bookings_cart_sessions') . '.childID) as participants_children')
			->from('bookings_lessons')
			->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
			->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
			->where($where)
			->where_in('bookings_lessons.lessonID', array_keys($lessons))
			->group_by('bookings_lessons.lessonID')
			->get();
		if ($res_participants->num_rows() > 0) {
			foreach ($res_participants->result() as $row) {
				if($booking_info->type == 'event' || $booking_info->project == 1){
					switch ($booking_info->register_type) {
						case 'numbers':
							$participants[$row->lessonID]['count'] = $row->participants_numbers;
							break;
						case 'names':
						case 'bikeability':
							$participants[$row->lessonID]['count'] = $row->participants_names;
							break;
						case 'children':
						case 'children_bikeability':
						case 'children_shapeup':
							$participants[$row->lessonID]['count'] = intval($row->participants_children);
							break;
						case 'individuals':
						case 'individuals_bikeability':
						case 'individuals_shapeup':
							$participants[$row->lessonID]['count'] = intval($row->participants);
							break;
						case 'adults_children':
							$participants[$row->lessonID]['count'] = (intval($row->participants) + intval($row->participants_children));
							break;
					}
					if($participants[$row->lessonID]['count'] > 0){
						$show_participant_column = TRUE;
					}
				}
			}
		}

		// get details
		$where = array(
			'bookings_lessons.blockID' => $block_info->blockID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);
		$lessons_res = $this->db->select('bookings_lessons.*, activities.name as activity')->from('bookings_lessons')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->where($where)->where_in('bookings_lessons.lessonID', array_keys($lessons))->order_by('bookings_lessons.startDate, bookings_lessons.endDate, bookings_lessons.day asc, bookings_lessons.startTime asc, bookings_lessons.endTime asc')->get();

		if ($lessons_res->num_rows() > 0) {

			// block header
			$smart_tags['details'] .= '<p><strong>' . $block_info->name . ' (' . mysql_to_uk_date($block_info->startDate);
			if (strtotime($block_info->endDate) > strtotime($block_info->startDate)) {
				$smart_tags['details'] .= ' to ' . mysql_to_uk_date($block_info->endDate);
			}
			$smart_tags['details'] .= ')</strong></p>';

			$participant_column = ($show_participant_column)?"<th scope='col'>Participants</th>":"";
			// get lessons
			$smart_tags['details'] .= '<table width="100%" border="1">
			<tr>
				<th scope="col">Day</th>
				<th scope="col">Start</th>
				<th scope="col">End</th>
				<th scope="col">Group</th>
				<th scope="col">Activity</th>
				'.$participant_column.'
			</tr>';

			foreach ($lessons_res->result() as $lesson) {
				$group = 'Unknown';
				$activity = 'Unknown';

				if ($lesson->group == 'other') {
					$group = $lesson->group_other;
				} else if (!empty($lesson->group)) {
					$group = $this->crm_library->format_lesson_group($lesson->group);
				}

				if (!empty($lesson->activity)) {
					$activity = $lesson->activity;
				} else if (!empty($lesson->activity_other)) {
					$activity = $lesson->activity_other;
				}

				$smart_tags['details'] .= '<tr>
					<td>' . ucwords($lesson->day);
					if (!empty($lesson->startDate)) {
						$smart_tags['details'] .= ' (' . mysql_to_uk_date($lesson->startDate);
						if (!empty($lesson->endDate) && strtotime($lesson->endDate) > strtotime($lesson->startDate)) {
							$smart_tags['details'] .= '-' . mysql_to_uk_date($lesson->endDate);
						}
						$smart_tags['details'] .= ')';
					}
					$participant_data_column = "";
					if($show_participant_column){
						if(isset($participants[$lesson->lessonID])) {
							$participant_data_column = '<td>' . $participants[$lesson->lessonID]['count'] . '</td>';
						}else{
							$participant_data_column = '<td>0</td>';
						}
					}
					$smart_tags['details'] .= '</td>
					<td>' . substr($lesson->startTime, 0, 5) . '</td>
					<td>' . substr($lesson->endTime, 0, 5) . '</td>
					<td>' . $group . '</td>
					<td>' . $activity;
					if (!empty($lesson->activity_desc)) {
						$smart_tags['details'] .= ': ' . $lesson->activity_desc;
					}
					$smart_tags['details'] .= '</td>'.$participant_data_column.'
				</tr>';
			}

			$smart_tags['details'] .= '</table>';

			// exceptions
			$where = array(
				'bookings_lessons.blockID' => $block_info->blockID,
				'bookings_lessons_exceptions.type' => 'cancellation',
				'bookings_lessons.accountID' => $this->auth->user->accountID
			);
			$exceptions = $this->db->select('bookings_lessons.*, bookings_lessons_exceptions.date, bookings_lessons_exceptions.reason_select, bookings_lessons_exceptions.reason, activities.name as activity')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->where($where)->where_in('bookings_lessons.lessonID', array_keys($lessons))->order_by('date asc, bookings_lessons.startTime, bookings_lessons.endTime')->get();

			if ($exceptions->num_rows() > 0) {

				// get exceptions
				$smart_tags['details'] .= '<p>You have informed us of the following dates where you would not like the sessions to take place:</p>
				<table width="100%" border="1">
				<tr>
					<th scope="col">Date</th>
					<th scope="col">Start</th>
					<th scope="col">End</th>
					<th scope="col">Group</th>
					<th scope="col">Activity</th>
					<th scope="col">Reason</th>
				</tr>';

				foreach ($exceptions->result() as $exception) {
					$group = 'Unknown';
					$activity = 'Unknown';
					$reason = 'Unknown';

					if ($exception->group == 'other') {
						$group = $exception->group_other;
					} else if (!empty($exception->group)) {
						$group = $this->crm_library->format_lesson_group($exception->group);
					}

					if (!empty($exception->activity)) {
						$activity = $exception->activity;
					} else if (!empty($exception->activity_other)) {
						$activity = $exception->activity_other;
					}

					if ($exception->reason_select == 'other') {
						$reason = $exception->reason;
					} else if (!empty($exception->reason_select)) {
						$reason = ucwords($exception->reason_select);
					}

					$smart_tags['details'] .= '<tr>
						<td>' . mysql_to_uk_date($exception->date) . '</td>
						<td>' . substr($exception->startTime, 0, 5) . '</td>
						<td>' . substr($exception->endTime, 0, 5) . '</td>
						<td>' . $group . '</td>
						<td>' . $activity . '</td>
						<td>' . $reason . '</td>
					</tr>';
				}

				$smart_tags['details'] .= '</table>';

			}

		}

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$content = str_replace('<p>{' . $key . '}</p>', $value, $content);
			$content = str_replace('{' . $key . '}', $value, $content);
		}

		// replace smart tags in subject
		unset($smart_tags['details']);
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
		}

		// look up contacts
		$where = array(
			'orgID' => $org_info->orgID,
			'contactID != ' => $contact_info->contactID,
			'accountID' => $this->auth->user->accountID,
			'email !=' => '',
			'email IS NOT NULL' => NULL
		);

		$contacts = $this->db->from('orgs_contacts')->where($where)->order_by('isMain desc, name asc')->get();

		$qualifications = $this->qualifications_library->getMandatoryQuals($this->auth->user->accountID, true);

		$lesson_ids = [];

		foreach ($lessons as $lesson) {
			$lesson_ids[] = $lesson->lessonID;
		}

		$tagsToReplace = $this->qualifications_library->getDefaultQualsTags();

		$qualifications_data = $this->qualifications_library->qualificationsDataByLesson($lesson_ids);

		$mandatoryQualsTags = array_filter($this->qualifications_library->getAllTags($this->auth->user->accountID));

		$mandatoryQualsAttachments = [];
		foreach ($tagsToReplace as $qualName => $tag) {
			if (stripos($content, $tag) !== false) {
				$table = $this->qualifications_library->createQualificationsTable($qualifications_data['data'], $qualName);
				$content = str_replace($tag, $table, $content);
				unset($qualifications[$qualName]);

				//set attachments based on tags
				foreach ($qualifications_data['data'] as $data) {
					foreach ($data['attachments'] as $id => $attachment) {
						if ($qualName == $id) {
							$mandatoryQualsAttachments[$qualName][] = $attachment;
						}
					}
				}
			}
		}

		foreach ($mandatoryQualsTags as $qualId => $qualTag) {
			if (stripos($content, '{' . $qualTag . '}') !== false) {
				$content = str_replace('{' . $qualTag . '}', '', $content);
				unset($qualifications[$qualId]);

				//set attachments based on tags
				foreach ($qualifications_data['data'] as $data) {
					foreach ($data['attachments'] as $id => $attachment) {
						if ($qualId == $id) {
							$mandatoryQualsAttachments[$id][] = $attachment;
						}
					}
				}
			}
		}

		// prepare data for view
		$data = array(
			'contacts' => $contacts,
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'lessons' => $lessons,
			'subject' => $subject,
			'content' => $content,
			'attachment_field' => TRUE,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'bulk_contactID' => $contactID,
			'info' => $info,
			'qualifications' => $qualifications,
			'quals_attachment' => $mandatoryQualsAttachments
		);

		// load view
		$this->crm_view('sessions/bulk-confirmation', $data);
	}

	/**
	 * bulk remove lessons
	 * @return mixed
	 */
	private function bulk_remove() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;

		// remove
		$lessons_removed = 0;
		$errors = [];

		foreach ($lessons as $lessonID => $lesson_info) {
			$where = array(
				'blockID' => $blockID,
				'lessonID' => $lessonID,
				'accountID' => $this->auth->user->accountID
			);

			$res = $this->db->delete('bookings_lessons', $where);

			if ($this->db->affected_rows() > 0) {
				$lessons_removed++;
			} else {
				// try to force delete if required
				if ($this->crm_library->last_segment() === 'force') {
					force_delete_db_dependants('bookings_lessons', $lessonID);
				}
				// get dependant table conflicts
				if ($db_error = get_friendly_db_error('bookings_lessons', $lessonID, $lesson_info, ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ')')) {
					$errors[] =  $db_error;
					$this->message .= $db_error;
				}
			}
		}

		// if force deleting, try to delete again
		if ($this->crm_library->last_segment() === 'force') {
			// reset errors
			$errors = [];

			// lopp lessons again
			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$res = $this->db->delete('bookings_lessons', $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_removed++;
				} else {
					// get dependant table conflicts
					if ($db_error = get_friendly_db_error('bookings_lessons', $lessonID, $lesson_info, ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ')')) {
						$errors[] =  $db_error;
						$this->message .= $db_error;
					}
				}
			}
		}

		// tell user
		if ($lessons_removed > 0) {
			$lessons_text = $lessons_removed . ' session';

			if($lessons_removed == 1) {
				$lessons_text .= ' has';
			} else {
				$lessons_text .= 's have';
			}
			$this->message .= $lessons_text . ' been removed successfully.';
		}

		// show errors if any
		if (count($errors) > 0) {
			$this->error = 1;

			// store data in flashdata in case force deleting later
			$this->session->set_flashdata('bulk_action', $this->bulk_action);
			$this->session->set_flashdata('bulk_lessons', array_keys($this->lessons));
		}
	}

	/**
	 * bulk duplicate sessions - only core lesson, org attachments, resource attachments and vouchers
	 * @return mixed
	 */
	private function bulk_duplicate() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;

		// duplicate
		$lessons_duplicated = $this->crm_library->duplicate_lessons($lessons);

		// tell user
		if ($lessons_duplicated == 0) {
			$this->error = 1;
			$this->message = "No sessions could be duplicated.";
		} else {

			$lessons_text = $lessons_duplicated . ' session';

			if($lessons_duplicated == 1) {
				$lessons_text .= ' has';
			} else {
				$lessons_text .= 's have';
			}
			$this->message = $lessons_text . ' been duplicated successfully.';
		}

		// calc targets
		$this->crm_library->calc_targets($blockID);
	}

	/**
	 * bulk change address
	 * @return mixed
	 */
	private function bulk_address() {
		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;

		$addressID = $this->bulk_data['change_addressID'];

		// look up address
		$where = array(
			'addressID' => $addressID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('orgs_addresses')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			$this->error = 1;
			$this->message = 'Please specify an address';
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'addressID' => $addressID,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->error = 1;
				$this->message = 'No sessions have been updated.';
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . " been updated successfully.";
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);

	}

	/**
	 * bulk change price
	 * @return mixed
	 */
	private function bulk_price() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$price = $this->bulk_data['price'];

		if (!is_numeric($price)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->error = 1;
			$this->message = 'Price must be numeric.';
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'price' => $price,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->error = 1;
				$this->message = 'No sessions have been updated.';
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}

				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);

	}

	/**
	 * bulk change target participants
	 * @return mixed
	 */
	private function bulk_target_participants() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$target_participants = $this->bulk_data['target_participants'];

		if (!is_numeric($target_participants)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Target Participants must be numeric.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'target_participants' => $target_participants,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}

				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);

	}

	/**
	 * bulk change booking cut off
	 * @return mixed
	 */
	private function bulk_booking_cutoff() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$booking_cutoff = $this->bulk_data['booking_cutoff'];

		if (!is_numeric($booking_cutoff)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Online booking cut off must be numeric.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'booking_cutoff' => NULL,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if ($booking_cutoff !== '') {
					$data['booking_cutoff'] = $booking_cutoff;
				}

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}

				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

	}

	/**
	 * bulk change min age
	 * @return mixed
	 */
	private function bulk_min_age() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$min_age = $this->bulk_data['min_age'];

		if (!is_numeric($min_age)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Minimum age must be numeric.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'min_age' => NULL,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if ($min_age !== '') {
					$data['min_age'] = $min_age;
				}

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->error = 1;
				$this->message = 'No sessions have been updated.';
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}

				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

	}

	/**
	 * bulk change max age
	 * @return mixed
	 */
	private function bulk_max_age() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$max_age = $this->bulk_data['max_age'];

		if (!is_numeric($max_age)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Maximum age must be numeric.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'max_age' => NULL,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if ($max_age !== '') {
					$data['max_age'] = $max_age;
				}

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->error = 1;
				$this->message = 'No sessions have been updated.';
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk change charge
	 * @return mixed
	 */
	private function bulk_charge() {

		// head coach can't change this
		if ($this->auth->user->department == 'headcoach') {
			return FALSE;
		}

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$charge = $this->bulk_data['charge'];
		$charge_other = $this->bulk_data['charge_other'];

		if (empty($charge)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Charge is required.';
			$this->error = 1;
		} else if ($charge == 'other' && !is_numeric($charge_other)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Charge - Other must be numeric.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'charge' => $charge,
					'charge_other' => $charge_other,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if ($charge != 'other') {
					$data['charge_other'] = NULL;
				}

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->error = 1;
				$this->message = 'No sessions have been updated.';
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}

				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);

	}

	/**
	 * bulk activity
	 * @return mixed
	 */
	private function bulk_activity() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$activityID = $this->bulk_data['activityID'];
		$activity_other = $this->bulk_data['activity_other'];

		if (empty($activityID)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Activity is required';
			$this->error = 1;
		} else if ($activityID == 'other' && empty($activity_other)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Activity - Other is required.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'activityID' => NULL,
					'activity_other' => NULL,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if (!empty($activityID) && $activityID != 'other') {
					$data['activityID'] = $activityID;
				} else {
					$data['activity_other'] = $activity_other;
				}

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->error = 1;
				$this->message = 'No sessions have been updated.';
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk type
	 * @return mixed
	 */
	private function bulk_type() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$typeID = $this->bulk_data['typeID'];
		$type_other = $this->bulk_data['type_other'];

		if (empty($typeID)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Type is required.';
			$this->error = 1;
		} else if ($typeID == 'other' && empty($type_other)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Type - Other is required.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'typeID' => NULL,
					'type_other' => NULL,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if (!empty($typeID) && $typeID != 'other') {
					$data['typeID'] = $typeID;
				} else {
					$data['type_other'] = $type_other;
				}

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk group
	 * @return mixed
	 */
	private function bulk_group() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$group = $this->bulk_data['group'];
		$group_other = $this->bulk_data['group_other'];

		if (empty($group)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Group is required.';
			$this->error = 1;
		} else if ($group == 'other' && empty($group_other)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Group - Other is required.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'group' => $group,
					'group_other' => NULL,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if ($group == 'other') {
					$data['group_other'] = $group_other;
				}

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk activity description
	 * @return mixed
	 */
	private function bulk_activity_desc() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$activity_desc = $this->bulk_data['activity_desc'];

		if (empty($activity_desc)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Activity Description is required.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'activity_desc' => $activity_desc,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk location
	 * @return mixed
	 */
	private function bulk_location() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$location = $this->bulk_data['location'];

		if (empty($location)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Location is required.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'location' => $location,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk class size
	 * @return mixed
	 */
	private function bulk_class_size() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$class_size = $this->bulk_data['class_size'];

		if (empty($class_size)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Class Size is required.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'class_size' => $class_size,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk day
	 * @return mixed
	 */
	private function bulk_day() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$day = $this->bulk_data['day'];

		if (empty($day)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Day is required.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'day' => $day,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk dates
	 * @return mixed
	 */
	private function bulk_dates() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$startDate = $this->bulk_data['startDate'];
		$endDate = $this->bulk_data['endDate'];

		if (!check_uk_date($startDate) || !check_uk_date($endDate)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Start and end dates are required.';
			$this->error = 1;
		} else if (strtotime(uk_to_mysql_date($startDate)) > strtotime(uk_to_mysql_date($endDate))) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'End date must be on or after start date.';
			$this->error = 1;
		} else 	if (strtotime(uk_to_mysql_date($startDate)) < strtotime($this->block_info->startDate) || strtotime(uk_to_mysql_date($endDate)) > strtotime($this->block_info->endDate)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Dates must be within block dates.';
			$this->error = 1;
		} else{
			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'startDate' => uk_to_mysql_date($startDate),
					'endDate' => uk_to_mysql_date($endDate),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}
	}

	/**
	 * bulk remove dates
	 * @return mixed
	 */
	private function bulk_removedates() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;

		// update
		$lessons_changed = 0;

		foreach ($lessons as $lessonID => $lesson_info) {
			$where = array(
				'blockID' => $blockID,
				'lessonID' => $lessonID,
				'accountID' => $this->auth->user->accountID
			);

			$data = array(
				'startDate' => NULL,
				'endDate' => NULL,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$res = $this->db->update('bookings_lessons', $data, $where);

			if ($this->db->affected_rows() > 0) {
				$lessons_changed++;
			}
		}

		// tell user
		if ($lessons_changed == 0) {
			$this->message = 'No sessions have been updated.';
			$this->error = 1;
		} else {

			$lessons_text = $lessons_changed . ' session';

			if($lessons_changed == 1) {
				$lessons_text .= ' has';
			} else {
				$lessons_text .= 's have';
			}
			$this->message = $lessons_text . ' been updated successfully.';
		}
	}

	/**
	 * bulk times
	 * @return mixed
	 */
	private function bulk_times() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$startTimeH = $this->bulk_data['startTimeH'];
		$startTimeM = $this->bulk_data['startTimeM'];
		$endTimeH = $this->bulk_data['endTimeH'];
		$endTimeM = $this->bulk_data['endTimeM'];
		$adjust_staff_times = $this->bulk_data['adjust_staff_times'];
		$notify_staff = $this->bulk_data['notify_staff'];

		if (empty($startTimeH) || empty($startTimeM)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Start time is required.';
			$this->error = 1;
		} else if (empty($endTimeH) || empty($endTimeM)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'End time is required.';
			$this->error = 1;
		} else if (strtotime(date('Y-m-d') . ' ' . $endTimeH . ':' . $endTimeM) <= strtotime(date('Y-m-d') . ' ' . $startTimeH . ':' . $startTimeM)) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'End time must be after start time.';
			$this->error = 1;
		} else{
			// update
			$lessons_changed = 0;
			$affected_lesson_staff = array();
			$lesson_prev_times = array();

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'startTime' => $startTimeH . ':' . $startTimeM,
					'endTime' => $endTimeH . ':' . $endTimeM,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}

				// adjust times
				if ($adjust_staff_times == 1) {
					// get session staff that will be effected
					$where = array(
						'lessonID' => $lessonID,
						'accountID' => $this->auth->user->accountID
					);
					$or_where = array();
					if (substr($lesson_info->startTime, 0, 5) != $startTimeH . ':' . $startTimeM) {
						$or_where[] = "`startTime` = " . $this->db->escape($lesson_info->startTime);
					}
					if (substr($lesson_info->endTime, 0, 5) != $endTimeH . ':' . $endTimeM) {
						$or_where[] = "`endTime` = " . $this->db->escape($lesson_info->endTime);
					}
					// if times not changed, skip
					if (count($or_where) == 0) {
						$or_where[] = "1 = 2";
					}
					$query = $this->db->from('bookings_lessons_staff')->where($where)->where('(' . implode(" OR ", $or_where) . ')', NULL, FALSE)->get();
					if ($query->num_rows() > 0) {
						foreach ($query->result() as $row) {
							$affected_lesson_staff[$row->recordID] = $row->recordID;
							$lesson_prev_times[$row->recordID] = array(
								'start' => $row->startTime,
								'end' => $row->endTime
							);
						}
					}

					// update start times if same as previous
					$where = array(
						'lessonID' => $lessonID,
						'startTime' => $lesson_info->startTime,
						'accountID' => $this->auth->user->accountID
					);
					$update_data = array(
						'startTime' => $startTimeH . ':' . $startTimeM,
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					// run query
					$query = $this->db->update('bookings_lessons_staff', $update_data, $where);

					// update end times if same as previous
					$where = array(
						'lessonID' => $lessonID,
						'endTime' => $lesson_info->endTime,
						'accountID' => $this->auth->user->accountID
					);
					$update_data = array(
						'endTime' => $endTimeH . ':' . $endTimeM,
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					// run query
					$query = $this->db->update('bookings_lessons_staff', $update_data, $where);

					$staff_times_adjusted = TRUE;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}

				$success = $lessons_text . ' been updated successfully';
				if (isset($staff_times_adjusted)) {
					$success .= ' and staff times adjusted';

					// tell staff times have changed
					if ($this->settings_library->get('send_staff_changed_sessions') == 1 && $notify_staff && $this->crm_library->notify_staff_changed_sessions($affected_lesson_staff, $lesson_prev_times)) {
						$success .= ' and notified';
					}
				}
				$this->message = $success;
			}

		}
	}

	/**
	 * bulk minimum staff
	 * @return mixed
	 */
	private function bulk_minstaff() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$minstaff_head = $this->bulk_data['minstaff_head'];
		$minstaff_lead = $this->bulk_data['minstaff_lead'];
		$minstaff_assistant = $this->bulk_data['minstaff_assistant'];
		$minstaff_participant = $this->bulk_data['minstaff_participant'];
		$minstaff_observer = $this->bulk_data['minstaff_observer'];

		if ((empty($minstaff_head) && $minstaff_head !== "0") || $minstaff_head < 0) {
			$minstaff_head = NULL;
		}
		if ((empty($minstaff_lead) && $minstaff_lead !== "0") || $minstaff_lead < 0) {
			$minstaff_lead = NULL;
		}
		if ((empty($minstaff_assistant) && $minstaff_assistant !== "0") || $minstaff_assistant < 0) {
			$minstaff_assistant = NULL;
		}
		if ((empty($minstaff_participant) && $minstaff_participant !== "0") || $minstaff_participant < 0) {
			$minstaff_participant = NULL;
		}
		if ((empty($minstaff_observer) && $minstaff_observer !== "0") || $minstaff_observer < 0) {
			$minstaff_observer = NULL;
		}

		$valid_values = TRUE;
		if ($minstaff_head === NULL && $minstaff_lead === NULL && $minstaff_assistant === NULL && $minstaff_participant === NULL && $minstaff_observer === NULL) {
			$valid_values = FALSE;
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'At least one valid required staff field is required.';
			$this->error = 1;
		}

		if ($valid_values === TRUE) {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {
				$where = array(
					'blockID' => $blockID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$data = array(
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if ($minstaff_head !== NULL) {
					$data['staff_required_head'] = $minstaff_head;
				}
				if ($minstaff_lead !== NULL) {
					$data['staff_required_lead'] = $minstaff_lead;
				}
				if ($minstaff_assistant !== NULL) {
					$data['staff_required_assistant'] = $minstaff_assistant;
				}
				if ($minstaff_participant !== NULL) {
					$data['staff_required_participant'] = $minstaff_participant;
				}
				if ($minstaff_observer !== NULL) {
					$data['staff_required_observer'] = $minstaff_observer;
				}

				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}
			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);
	}

	private function bulk_offer_accept_manual() {

		$bookingID = $this->bookingID;
		$blockID = $this->blockID;
		$lessons = $this->lessons;
		$block_info = $this->block_info;

		$combineLessons = $this->bulk_data['combine_sessions'];
		$salaried = $this->bulk_data['offer_accept_salaried'];

		$redirect = 'bookings/sessions/' . $bookingID . '/' . $blockID;

		$type = $this->input->post('offer_accept_type');

		$type = ($type == 'groups' ? 'group' : $type);

		if (empty($type)) {
			$this->message = 'Type is required to select.';
			$this->error = 1;
			die();
		}

		$group = null;
		$staffId = null;
		$staffType = null;
		if ($type == 'group') {
			$group = (int)$this->input->post('offer_accept_group');
			$staffType = $this->input->post('staff_type_offer');
			if ($group < 1 || empty($staffType)) {
				$this->message = 'Group and Type fields are required to select.';
				$this->error = 1;
				die();
			}
		} else {
			$staffId = (int)$this->input->post('staff_id_offer');
			$staffType = $this->input->post('staff_type_offer');

			if (empty($staffId) || empty($staffType)) {
				$this->message = 'Staff and Type fields are required to select.';
				$this->error = 1;
				die();
			}
		}

		$valid_lessons = $lessons;
		$invalid_lessons = array();

		// load library
		$this->load->library('offer_accept_library');

		// check lesson/block start
		foreach ($valid_lessons as $lessonID => $lesson_info) {
			$lesson_start = $lesson_info->startDate;
			if (empty($lesson_start)) {
				$lesson_start = $block_info->startDate;
			}

			if (strtotime($lesson_start) <= strtotime(date('Y-m-d'))) {
				unset($valid_lessons[$lessonID]);
				$invalid_lessons[] = $lessonID;
			}
		}
		if (count($invalid_lessons) > 0) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Sessions can\'t be offered if they have already begun.';
			$this->error = 1;
		}

		// check staff requirements and if met
		if (count($invalid_lessons) == 0) {
			foreach ($valid_lessons as $lessonID => $lesson_info) {
				if ($this->offer_accept_library->check_if_fully_staffed_by_role($lesson_info, $staffType)) {
					$invalid_lessons[] = $lessonID;
				}
			}
			if (count($invalid_lessons) > 0) {
				$this->session->set_flashdata('bulk_data', $this->bulk_data);
				$this->message .= 'Some of the selected sessions have already met their staffing requirements.';
				$this->error = 1;
			}
		}

		// if some valid sessions and no errors
		if (count($invalid_lessons) == 0 && count($valid_lessons) > 0) {

			$combinedLessons = [];
			if ($combineLessons) {
				foreach ($valid_lessons as $lessonID => $lesson_info) {
					$combinedLessons[] = $lessonID;
				}
			}
			// all, ok, start process
			$lessons_offered = 0;

			foreach ($valid_lessons as $lessonID => $lesson_info) {
				$where = array(
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);
				$data = array(
					'offer_accept_status' => 'offering',
					'offer_accept_reason' => NULL,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$res = $this->db->update('bookings_lessons', $data, $where);

				if ($this->db->affected_rows() > 0) {
					// offer lesson
					if ($type == 'group') {
						$success = $this->offer_accept_library->offer_to_group($lessonID, $group, $staffType, $combinedLessons, $salaried);
					} else {
						$success = $this->offer_accept_library->offer_to_individual($lessonID, $staffId, $staffType, $combinedLessons, $salaried);
					}

					if ($success) {
						$lessons_offered++;
					}
				}
			}

			// tell user
			if ($lessons_offered == 0) {
				$errors = $this->offer_accept_library->getErrors();
				if (empty($errors)) {
					$this->message = 'No sessions have been offered.';
					$this->error = 1;
				} else {
					foreach ($errors as $error) {
						$this->message .= $error;
						$this->error = 1;
					}
				}
			} else {

				$lessons_text = $lessons_offered . ' session';

				if($lessons_offered == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been offered successfully.';
			}
		}
	}

	/**
	 * bulk offer/accept
	 * @return mixed
	 */
	private function bulk_offer_accept() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$block_info = $this->block_info;
		$lessons = $this->lessons;
		$offer_accept_grouped = $this->bulk_data['offer_accept_grouped'];

		//if (empty($offer_accept_grouped)) {
			//$this->session->set_flashdata('bulk_data', $this->bulk_data);
			//$this->session->set_flashdata('error', 'Acceptance rule is required.');
		//} else {
		if (1 == 1) {
			$valid_lessons = $lessons;
			$invalid_lessons = array();


			// check lesson/block start
			foreach ($valid_lessons as $lessonID => $lesson_info) {
				$lesson_start = $lesson_info->startDate;
				if (empty($lesson_start)) {
					$lesson_start = $block_info->startDate;
				}
				if (strtotime($lesson_start) <= strtotime(date('Y-m-d'))) {
					unset($valid_lessons[$lessonID]);
					$invalid_lessons[] = $lessonID;
				}
			}
			if (count($invalid_lessons) > 0) {
				$this->session->set_flashdata('bulk_data', $this->bulk_data);
				$this->message = 'Sessions can\'t be offered if they have already begun.';
				$this->error = 1;
			}

			// check sessions to seee if have some where offer/accept not already in process
			if (count($invalid_lessons) == 0) {
				foreach ($valid_lessons as $lessonID => $lesson_info) {
					if ($lesson_info->offer_accept_status == 'offering') {
						unset($valid_lessons[$lessonID]);
						$invalid_lessons[] = $lessonID;
					}
				}
				if (count($invalid_lessons) > 0) {
					$this->session->set_flashdata('bulk_data', $this->bulk_data);
					$this->message .= 'Some of the selected sessions are already in the process of being offered out.';
					$this->error = 1;
				}
			}

			// load library
			$this->load->library('offer_accept_library');

			// check staff requirements and if met
			if (count($invalid_lessons) == 0) {
				foreach ($valid_lessons as $lessonID => $lesson_info) {
					// check if staff requirements, already met
					if ($this->offer_accept_library->check_if_fully_staffed($lesson_info)) {
						$invalid_lessons[] = $lessonID;
					}
				}
				if (count($invalid_lessons) > 0) {
					$this->session->set_flashdata('bulk_data', $this->bulk_data);
					$this->message .= 'Some of the selected sessions have already met their staffing requirements.';
					$this->error = 1;
				}
			}

			// if some valid sessions and no errors
			if (count($invalid_lessons) == 0 && count($valid_lessons) > 0) {
				// all, ok, start process
				$lessons_offered = 0;
				$groupID = NULL;

				// if staff must accept all session and at least one session requested, group
				if ($offer_accept_grouped == 'yes' && count($valid_lessons) > 0) {
					$data = array(
						'accountID' => $this->auth->user->accountID,
						'byID' => $this->auth->user->staffID,
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$res = $this->db->insert('offer_accept_groups', $data);
					if ($this->db->affected_rows() > 0) {
						$groupID = $this->db->insert_id();
					}
				}

				foreach ($valid_lessons as $lessonID => $lesson_info) {
					$where = array(
						'lessonID' => $lessonID,
						'accountID' => $this->auth->user->accountID
					);
					$data = array(
						'offer_accept_status' => 'offering',
						'offer_accept_groupID' => $groupID,
						'offer_accept_reason' => NULL,
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$res = $this->db->update('bookings_lessons', $data, $where);

					if ($this->db->affected_rows() > 0) {
						// offer lesson
						$this->offer_accept_library->offer($lessonID);
						$lessons_offered++;
					}
				}

				// tell user
				if ($lessons_offered == 0) {
					$this->message = 'No sessions have been offered.';
					$this->error = 1;
				} else {

					$lessons_text = $lessons_offered . ' session';

					if($lessons_offered == 1) {
						$lessons_text .= ' has';
					} else {
						$lessons_text .= 's have';
					}
					$this->message = $lessons_text . ' been offered successfully.';
				}
			}
		}

		if (count($invalid_lessons) > 0) {
			$this->session->set_flashdata('invalid_lessons', $invalid_lessons);
		}
	}

	/**
	 * bulk add session plans
	 * @return mixed
	 */
	private function bulk_plans() {

		if (!$this->auth->has_features('resources')) {
			show_403();
			return FALSE;
		}

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;

		$resources_attachments = $this->bulk_data['resources_attachments'];

		if (!is_array($resources_attachments) || count($resources_attachments) == 0) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Please choose at least one document.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {

				foreach ($resources_attachments as $attachmentID) {
					// check if already exists
					$where = array(
						'bookingID' => $bookingID,
						'lessonID' => $lessonID,
						'attachmentID' => $attachmentID,
						'accountID' => $this->auth->user->accountID
					);

					$res = $this->db->from('bookings_lessons_resources_attachments')->where($where)->get();

					if ($res->num_rows() == 0) {
						$data = array(
							'bookingID' => $bookingID,
							'lessonID' => $lessonID,
							'attachmentID' => $attachmentID,
							'byID' => $this->auth->user->staffID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);
						$this->db->insert('bookings_lessons_resources_attachments', $data);

						if ($this->db->affected_rows() > 0) {
							$lessons_changed++;
						}
					}
				}

			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);
	}

	/**
	 * bulk add coach access to customer attachments
	 * @return mixed
	 */
	private function bulk_coachaccess() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;

		$coach_access = $this->bulk_data['coach_access'];

		if (!is_array($coach_access) || count($coach_access) == 0) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Please choose at least one attachment.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			foreach ($lessons as $lessonID => $lesson_info) {

				foreach ($coach_access as $attachmentID) {
					// check if already exists
					$where = array(
						'bookingID' => $bookingID,
						'lessonID' => $lessonID,
						'attachmentID' => $attachmentID,
						'accountID' => $this->auth->user->accountID
					);

					$res = $this->db->from('bookings_lessons_orgs_attachments')->where($where)->get();

					if ($res->num_rows() == 0) {
						$data = array(
							'bookingID' => $bookingID,
							'lessonID' => $lessonID,
							'attachmentID' => $attachmentID,
							'byID' => $this->auth->user->staffID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);
						$this->db->insert('bookings_lessons_orgs_attachments', $data);

						if ($this->db->affected_rows() > 0) {
							$lessons_changed++;
						}
					}
				}

			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);
	}

	/**
	 * bulk add session requirements
	 * @return mixed
	 */
	private function bulk_requirements() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;

		$lesson_requirements = $this->bulk_data['lesson_requirements'];

		if (!is_array($lesson_requirements) || count($lesson_requirements) == 0) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Please choose at least one requirement.';
			$this->error = 1;
		} else {

			// update
			$lessons_changed = 0;

			// build update data
			$data = array(
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			foreach ($lesson_requirements as $field_name) {
				$data[$field_name] = 1;
			}

			foreach ($lessons as $lessonID => $lesson_info) {

				// update
				$where = array(
					'bookingID' => $bookingID,
					'lessonID' => $lessonID,
					'accountID' => $this->auth->user->accountID
				);

				$res = $this->db->update('bookings_lessons', $data, $where, 1);

				if ($this->db->affected_rows() > 0) {
					$lessons_changed++;
				}

			}

			// tell user
			if ($lessons_changed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $lessons_changed . ' session';

				if($lessons_changed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);
	}

	/**
	 * bulk remove staff
	 * @return mixed
	 */
	private function bulk_removestaff() {

		// get params
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$blockID = $this->blockID;
		$lessons = $this->lessons;

		$remove_staff = $this->bulk_data['remove_staff'];

		if (!is_array($remove_staff) || count($remove_staff) == 0) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->message = 'Please choose at least one staff member.';
			$this->error = 1;
		} else {

			// update
			$staff_removed = 0;

			// load library
			$this->load->library('offer_accept_library');

			foreach ($lessons as $lessonID => $lesson_info) {

				foreach ($remove_staff as $staffID) {
					// check if already exists
					$where = array(
						'bookingID' => $bookingID,
						'lessonID' => $lessonID,
						'staffID' => $staffID,
						'accountID' => $this->auth->user->accountID
					);

					$res = $this->db->delete('bookings_lessons_staff', $where, 1);

					if ($this->db->affected_rows() > 0) {
						$staff_removed++;

						// delete any session evaluations for this staff/session
						$where = array(
							'lessonID' => $lessonID,
							'byID' => $staffID,
							'type' => 'evaluation'
						);
						$this->db->delete('bookings_lessons_notes', $where);

						// run offer/accept in case still offering
						if ($this->auth->has_features(array('offer_accept'))) {
							$this->offer_accept_library->offer($lessonID);
						}
					}
				}
			}

			// tell user
			if ($staff_removed == 0) {
				$this->message = 'No sessions have been updated.';
				$this->error = 1;
			} else {

				$lessons_text = $staff_removed . ' session';

				if($staff_removed == 1) {
					$lessons_text .= ' has';
				} else {
					$lessons_text .= 's have';
				}
				$this->message = $lessons_text . ' been updated successfully.';
			}

		}

		// calc targets
		$this->crm_library->calc_targets($blockID);
	}

	/**
	 * bulk add exception
	 * @return mixed
	 */
	private function bulk_exception($type) {

		if (!$this->auth->has_features('bookings_exceptions')) {
			show_403();
			return FALSE;
		}

		// get params
		$blockID = $this->blockID;
		$block_info = $this->block_info;
		$bookingID = $this->bookingID;
		$booking_info = $this->booking_info;
		$lessons = $this->lessons;

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New ';
		if ($type == 'staffchange') {
			$title .= ' Staff Change';
		} else {
			$title .= ' Cancellation';
		}
		$submit_to = 'sessions/bulk/' . $blockID;
		$return_to = 'bookings/sessions/' . $bookingID . '/' . $blockID;
		$icon = 'user';
		$tab = 'lessons';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $blockID] = 'Sessions';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// get session dates
		$lesson_count = 0;

		foreach ($lessons as $lessonID => $lesson_info) {

			$lessons[$lessonID]->dates = array();
			$lessons[$lessonID]->staff = array();
			$from = strtotime(uk_to_mysql_date($this->bulk_data['from']));
			$to = strtotime(uk_to_mysql_date($this->bulk_data['to']));
			// check if dates overridden on session level
			if (!empty($lesson_info->startDate) && strtotime($lesson_info->startDate) > $from) {
				$from = strtotime($lesson_info->startDate);
			}
			if (!empty($lesson_info->endDate) && strtotime($lesson_info->endDate) < $to) {
				$to = strtotime($lesson_info->endDate);
			}

			// loop through dates and store any which match day
			while ($from <= $to) {
				$weekday = strtolower(date("l", $from));
				if ($weekday == $lesson_info->day) {
					$lessons[$lessonID]->dates[] = date('Y-m-d', $from);
					$lesson_count++;
				}
				$from += 60*60*24;
			}

			// get staff
			$where = array(
				'lessonID' => $lessonID,
				'accountID' => $this->auth->user->accountID
			);
			$res_staff = $this->db->from('bookings_lessons_staff')->where($where)->get();

			if ($res_staff->num_rows() > 0) {
				foreach ($res_staff->result() as $row) {
					$lessons[$lessonID]->staff[$row->staffID] = $row->type;
				}
			}
		}

		if ($lesson_count == 0) {
			$this->session->set_flashdata('error', 'There are no matching sessions within the dates entered.');
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			redirect('bookings/sessions/' . $bookingID . '/' . $blockID);
		}

		// if posted
		if ($this->input->post('process') == 1) {

			// set validation rules
			foreach ($lessons as $lessonID => $lesson_info) {
				foreach ($lesson_info->dates as $date) {
					$lesson_desc = ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ')' . ' - ' . mysql_to_uk_date($date);

					if ($type == 'staffchange') {
						$this->form_validation->set_rules('fromID_' . $lessonID . '_' . $date, $lesson_desc . ' - Staff', 'trim|xss_clean|required');
						$this->form_validation->set_rules('staffID_' . $lessonID . '_' . $date, $lesson_desc . ' - Replacement Staff', 'trim|xss_clean');
					}
					$this->form_validation->set_rules('assign_to_' . $lessonID . '_' . $date, $lesson_desc . ' - Assign To', 'trim|xss_clean|required');
					$this->form_validation->set_rules('reason_select_' . $lessonID . '_' . $date, $lesson_desc . ' - Reason', 'trim|xss_clean|required');
					$this->form_validation->set_rules('reason_' . $lessonID . '_' . $date, $lesson_desc . ' - Reason - Other', 'trim|xss_clean|callback_required_if_other[' . $this->input->post('reason_select_' . $lessonID . '_' . $date) . ']');
				}
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				$exception_added = 0;
				$exception_no_replacement_count = 0;
				$exceptionIDs = array();
				$lessonIDs = array();

				foreach ($lessons as $lessonID => $lesson_info) {
					foreach ($lesson_info->dates as $date) {

						// all ok, prepare data
						$data = array(
							'bookingID' => $bookingID,
							'lessonID' => $lessonID,
							'date' => $date,
							'type' => $type,
							'assign_to' => set_value('assign_to_' . $lessonID . '_' . $date),
							'reason_select' => set_value('reason_select_' . $lessonID . '_' . $date),
							'reason' => set_value('reason_' . $lessonID . '_' . $date),
							'byID' => $this->auth->user->staffID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);

						if ($type == 'staffchange') {
							$data['fromID'] = set_value('fromID_' . $lessonID . '_' . $date);
							if (set_value('staffID_' . $lessonID . '_' . $date) != ''
							&& set_value('staffID_' . $lessonID . '_' . $date) != 0) {
								$data['staffID'] = set_value('staffID_' . $lessonID . '_' . $date);
							} else {
								$data['staffID'] = NULL;
							}
						} else {
							$data['fromID'] = NULL;
							$data['staffID'] = NULL;
						}

						// final check for errors
						if (count($errors) == 0) {

							// insert
							$query = $this->db->insert('bookings_lessons_exceptions', $data);

							if ($type == 'staffchange' && empty($data['staffID'])) {
								$exception_no_replacement_count++;
							}

							if ($this->db->affected_rows() == 1) {
								$exception_added++;
								$lessonIDs[] = $lessonID;
								$exceptionIDs[] = $this->db->insert_id();
							}
						}
					}
				}

				if ($exception_added == 0) {

					$this->session->set_flashdata('error', 'No exceptions could be added to any lessons.');

					// calc targets
					$this->crm_library->calc_targets($blockID);

					redirect('bookings/sessions/' . $bookingID . '/' . $blockID);

				} else {

					$staff_text = $exception_added . ' exception';

					if($exception_added == 1) {
						$staff_text .= ' has';
					} else {
						$staff_text .= 's have';
					}

					$staff_text .= ' now been created';

					if ($this->settings_library->get('send_staff_cancelled_sessions') == 1 && set_value('notify_staff') == 1 && $this->crm_library->notify_staff_new_exceptions($exceptionIDs)) {
						$staff_text .= ' and staff notified';
					}

					$redirect_to = 'bookings/sessions/' . $bookingID . '/' . $blockID;
					if ($this->settings_library->get('send_exceptions') == 1) {
						$staff_text .= '. Review and send the customer notification below.';
						$redirect_to = 'sessions/exceptions/notify';
						$this->session->set_flashdata('bulk_data', $this->bulk_data);
					}

					if ($type == 'staffchange' && $exception_added == $exception_no_replacement_count) {
						$redirect_to = 'bookings/sessions/' . $bookingID . '/' . $blockID;
					}

					$this->session->set_flashdata('success', $staff_text);
					$this->session->set_userdata('notify_exceptions', $exceptionIDs);
					$this->session->set_userdata('notify_exceptions_lessons', $lessonIDs);

					// calc targets
					$this->crm_library->calc_targets($blockID);

					redirect($redirect_to);
				}

			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// staff
		$where = array(
			'active' => 1,
			'non_delivery !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// get vals from bulk form
		$from = $this->input->post('from');
		$to = $this->input->post('to');
		$staffID = $this->input->post('staffID');
		$replacementID = $this->input->post('replacementID');

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
			'staff' => $staff_list,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'lessons' => $lessons,
			'from' => $from,
			'to' => $to,
			'staffID' => $staffID,
			'replacementID' => $replacementID,
			'type' => $type,
			'lesson_count' => $lesson_count,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('sessions/bulk-exception', $data);
	}

	// validation helpers
	public function get_staff_on_session($bookingID = NULL, $sessionIDs = NULL) {
		// check if numeric
		if (empty($bookingID) || !ctype_digit($bookingID) || empty($sessionIDs)) {
			show_404();
		}

		$sessionIDs = explode(",",urldecode($sessionIDs));
		foreach ($sessionIDs as $id) {
			if (!ctype_digit($id)) {
				show_404();
			}
		}

		$where = array(
			'bookings_lessons_staff.bookingID' => $bookingID,
			'bookings_lessons_staff.accountID' => $this->auth->user->accountID
		);

		$staffIDs = $this->db->select("bookings_lessons_staff.staffID, staff.first, staff.surname")->from('bookings_lessons_staff')
			->join("staff", "bookings_lessons_staff.staffID = staff.staffID AND bookings_lessons_staff.accountID=staff.accountID", "left")
			->group_by("staff.staffID")
			->where($where)->where_in('lessonID',$sessionIDs)->get();

		if ($staffIDs->num_rows()>0) {
			echo json_encode($staffIDs->result());
		}
		else {
			echo "{}";
		}
	}

	/**
	 * check if either this field filled in if value of another field is other
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function required_if_other($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// check
		if ($value2 == 'other' && empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check date is after start
	 * @param  string $date
	 * @return bool
	 */
	public function lesson_datetime($date = NULL) {

		// check fields - all required
		if ($this->input->post('startTimeH') == '' || $this->input->post('startTimeM') == '' || $this->input->post('endTimeH') == '' || $this->input->post('endTimeM') == '') {
			return TRUE;
		}

		// work out from and to
		$from = date('Y-m-d') . ' ' . $this->input->post('startTimeH') . ':' . $this->input->post('startTimeM');
		$to = date('Y-m-d') . ' ' . $this->input->post('endTimeH') . ':' . $this->input->post('endTimeM');

		if (strtotime($to) > strtotime($from)) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * format postcode and check is correct
	 * @param  string $postcode
	 * @return mixed
	 */
	public function check_postcode($postcode) {

		return $this->crm_library->check_postcode($postcode);

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

	/**
	 * check a date is after start date
	 * @param  string $endDate
	 * @param  string $startDate
	 * @return boolean
	 */
	public function after_start($endDate, $startDate) {

		// date not required
		if (empty($endDate)) {
			return TRUE;
		}

		$startDate = strtotime(uk_to_mysql_date($startDate));
		$endDate = strtotime(uk_to_mysql_date($endDate));

		if ($endDate >= $startDate) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check if end date is required
	 * @param  string $endDate
	 * @param  string $startDate
	 * @return boolean
	 */
	public function end_required($endDate, $startDate) {

		// date not required
		if (empty($startDate)) {
			return TRUE;
		}

		// end date specified
		if (!empty($endDate)) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check if session day occurs at least once within dates
	 * @param  string $endDate
	 * @param  string $startDate
	 * @return boolean
	 */
	public function valid_lesson_dates($endDate, $startDate) {

		$day = NULL;

		if (strpos($startDate, ',') !== FALSE) {
			$parts = explode(',', $startDate);
			if (array_key_exists(0, $parts)) {
				$startDate = $parts[0];
			}
			if (array_key_exists(1, $parts)) {
				$day = $parts[1];
			}
		}

		// check params
		if (empty($startDate) || empty($endDate) || empty($day)) {
			return TRUE;
		}

		// loop though dates
		$date = uk_to_mysql_date($startDate);
		$end_date = uk_to_mysql_date($endDate);

		// check session day is within dates
		while (strtotime($date) <= strtotime($end_date)) {
			if (strtolower(date('l', strtotime($date))) == $day) {
				return TRUE;
			}
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}

		return FALSE;

	}

	/**
	 * check date is after start
	 * @param  string $date
	 * @return bool
	 */
	public function exception_datetime($date = NULL) {

		// check fields - all required
		if ($this->input->post('from') == '' || $this->input->post('fromH') == '' || $this->input->post('fromM') == '' || $this->input->post('to') == '' || $this->input->post('toH') == '' || $this->input->post('toM') == '') {
			return TRUE;
		}

		// work out from and to
		$from = uk_to_mysql_date($this->input->post('from')) . ' ' . $this->input->post('fromH') . ':' . $this->input->post('fromM');
		$to = uk_to_mysql_date($this->input->post('to')) . ' ' . $this->input->post('toH') . ':' . $this->input->post('toM');

		if (strtotime($to) > strtotime($from)) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check date is within block
	 * @param  string $date
	 * @param  integer $blockID
	 * @return bool
	 */
	public function within_block($date = NULL, $blockID = NULL) {

		$optional = FALSE;

		if (strpos($blockID, ',') !== FALSE) {
			$parts = explode(',', $blockID);
			if (array_key_exists(0, $parts)) {
				$blockID = $parts[0];
			}
			if (array_key_exists(1, $parts) && $parts[1] == 'TRUE') {
				$optional = TRUE;
			}
		}

		// not required
		if ($optional === TRUE && empty($date)) {
			return TRUE;
		}

		// check params
		if (empty($date) || empty($blockID)) {
			return FALSE;
		}

		// look up block
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;
		}

		// convert
		$date = uk_to_mysql_date($date);

		// check within block
		if (strtotime($date) >= strtotime($block_info->startDate) && strtotime($date) <= strtotime($block_info->endDate)) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check staff not already on block
	 * @param  integer $staffID
	 * @param  integer $lessonID
	 * @return bool
	 */
	public function check_staff_not_already_on($staffID = NULL, $lessonID = NULL) {

		$date_from = uk_to_mysql_date($this->input->post('from_' . $lessonID));
		$date_to = uk_to_mysql_date($this->input->post('to_' . $lessonID));

		// check params
		if (empty($staffID) || empty($lessonID) || empty($date_from) || empty($date_to)) {
			// dont show error
			return TRUE;
		}

		// look up staff
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// look up lesson
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_lessons')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// match
		foreach ($query->result() as $row) {
			$lesson_info = $row;
		}

		$lesson_desc = ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ')';

		// check if already on session
		$where = array(
			'lessonID' => $lessonID,
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_lessons_staff')->where($where)->get();

		// no match, no conflicts
		if ($query->num_rows() == 0) {
			return TRUE;
		}

		// check if conflict if already on for part of the sessions
		foreach ($query->result() as $row) {
			// if conflict, stop
			if (strtotime($date_from) <= strtotime($row->endDate) && strtotime($row->startDate) <= strtotime($date_to)) {
				$this->form_validation->set_message('check_staff_not_already_on', $staff_info->first . ' ' . $staff_info->surname . ' is already staffed on ' . $lesson_desc . ' overlapping with these dates');
				return FALSE;
			}
		}

		return TRUE;
	}
}

/* End of file lessons.php */
/* Location: ./application/controllers/bookings/sessions.php */
