<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timetable extends MY_Controller {

	public $switch_day;
	private $accountID;
	private $staffID;

	public function __construct() {
		// check if calendar feed
		if (strpos($_SERVER['REQUEST_URI'], '/feed/') === 0) {
			// allow public access
			parent::__construct(TRUE);
		} else {
			// all can view
			parent::__construct(FALSE, array(), array(), array('bookings_timetable'));
		}

		$this->switch_day = 4;

		$this->load->library('user_agent');
	}

	/**
	 * show list of bookings
	 * @return void
	 */
	public function index($show_year = NULL, $show_week = NULL, $only_own = FALSE, $return = FALSE) {

		// set defaults
		$icon = 'calendar-alt';
		$type = 'booking';
		$current_page = 'timetable';
		$section = 'bookings';
		$page_base = 'bookings/timetable';
		$timetable_base = $page_base;
		$title = 'Timetable';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$lessons = array();
		$list_lessons = [];
		$lesson_count = 0;
		$lesson_hours = '00:00';
		$lesson_seconds = 0;
		$day_seconds = array();
		$day_hours = array();
		$week = date('W');
		$year = date('Y');
		$switch_day = $this->switch_day;
		$view = 'standard';

		// get possible views
		$possible_views = [
			'standard' => [
				'label' => 'Standard',
				'icon' => 'calendar'
			]
		];
		if (!$only_own) {
			// add global to front of array
			$possible_views = [
				'global' => [
					'label' => 'Global',
					'icon' => 'globe'
				]
			] + $possible_views;
			// add checkins to end of array (if on)
			if ($this->auth->has_features('lesson_checkins')) {
				$possible_views['map'] = [
					'label' => 'Check-in (Map)',
					'icon' => 'map-marked-alt'
				];
				$possible_views['details'] = [
					'label' => 'Check-in (Details)',
					'icon' => 'map-marker-alt'
				];
			}
		}

		// check for view - first param
		if (array_key_exists($show_year, $possible_views)) {
			$view = $show_year;
		}

		// use logged in accountID if not set
		if (empty($this->accountID)) {
			$this->accountID = $this->auth->user->accountID;
		}

		// use logged in staffID if not set
		if (empty($this->staffID)) {
			$this->staffID = $this->auth->user->staffID;
		}

		// if coach or full time, always shown just own
		if (!isset($this->auth->user->department) || in_array($this->auth->user->department, array('fulltimecoach', 'coaching'))) {
			$only_own = 'true';
		}

		// check if only own
		if ($only_own === 'true') {
			$only_own = TRUE;
			$icon = 'calendar-check';
			$current_page = 'timetable_own';
			$section = 'timetable_own';
			$page_base = 'timetable';
			$timetable_base = $page_base;
			$title = 'Your Timetable';
			$buttons = '<a class="btn" href="' . site_url('timetable/feed') . '"><i class="far fa-rss"></i> Calendar Feed</a> ';
			// check permission if not using feed
			if (!$this->auth->has_features('bookings_timetable_own') && strpos($_SERVER['REQUEST_URI'], '/feed/') !== 0) {
				show_403();
			}
		}

		// check if view valid
		if (!array_key_exists($view, $possible_views)) {
			show_404();
		}

		// add buttons for different views (if more than 1)
		if (count($possible_views) > 1) {
			$buttons .= '<div class="btn-group" role="group">';
			foreach ($possible_views as $key => $view_details) {
				$link = $page_base;
				if ($key !== 'standard') {
					$link .= '/' . $key;
				}
				$extra_class = '';
				if ($key === $view) {
					$extra_class = 'btn-primary';
				} else {
					$extra_class = 'btn-light';
				}
				$buttons .= '<a class="btn ' . $extra_class . '" href="' . site_url($link) . '" title="' . $view_details['label'] . ' View"><i class="far fa-' . $view_details['icon'] . ' fa-fw"></i></a>';
			}
			$buttons .= '</div>';
		}

		// if global view, pass to another method
		if ($view === 'global') {
			$data = [
				'title' => $title,
				'icon' => $icon,
				'section' => $section,
				'current_page' => $current_page,
				'buttons' => $buttons,
				'page_base' => $page_base,
				'blockID' => $show_week // second param in method
			];
			return $this->global($data);
		}

		// update page base
		if ($view != 'standard') {
			$page_base .= '/' . $view;
		}

		// check if jumping
		if ($this->input->post('week') != '' && $this->input->post('year') != '') {
			$show_week = $this->input->post('week');
			$show_year = $this->input->post('year');
		}

		// In ISO-8601 specification, it says that December 28th is always in the last week of its year
		$max_weeks = gmdate("W", strtotime("28 December " . $show_year));

		// check for override from next/prev
		if (is_numeric($show_week) && $show_week >= 1 && $show_week <= $max_weeks) {
			$week = $show_week;
		}

		if (is_numeric($show_year) && strlen($show_year) == 4) {
			$year = $show_year;
		}

		// double check max weeks if above valid from override
		$max_weeks = gmdate("W", strtotime("28 December " . $year));

		$dt = new DateTime;
		if ($view == 'standard') {
			$title .= ' - Week ' . $week . ' ' . $year . ' (' . $dt->setISODate($year, $week, 1)->format('jS M') . ')';
			$page_base .= '/' . $year . '/' . $week;
		}

		$dto = new DateTime();
		$dto->setISODate($year, $week);
		$start_date = $dto->format('Y-m-d');
		$dto->modify('+6 days');
		$end_date = $dto->format('Y-m-d');

		// time slots
		$time_slots = array(
			6 => '06:00',
			7 => '07:00',
			8 => '08:00',
			9 => '09:00',
			10 => '10:00',
			11=> '11:00',
			12 => '12:00',
			13 => '13:00',
			14 => '14:00',
			15 => '15:00',
			16 => '16:00',
			17 => '17:00',
			18 => '18:00',
			19 => '19:00',
			20 => '20:00',
			21 => '21:00',
			22 => '22:00',
			23 => '23:00'
		);

		// get day numbers
		$day_numbers = array(
			'monday' => 1,
			'tuesday' => 2,
			'wednesday' => 3,
			'thursday' => 4,
			'friday' => 5,
			'saturday' => 6,
			'sunday' => 7,
		);

		// what week number is next + prev
		if ($week == $max_weeks) {
			$next_week = 1;
			$next_year = $year + 1;
		} else {
			$next_week = $week + 1;
			$next_year = $year;
		}
		if ($week == 1) {
			$prev_week = gmdate("W", strtotime("28 December " . ($year - 1)));;
			$prev_year = $year - 1;
		} else {
			$prev_week = $week - 1;
			$prev_year = $year;
		}

		// work out dates
		$dates = array();
		$days = array(
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday',
			'sunday'
		);
		$day_date = $start_date;
		foreach ($days as $day) {
			$dates[$day] = $day_date;
			$day_date = date("Y-m-d", strtotime($day_date) + 60*60*24);

			// add base value for times
			$day_seconds[$day] = '0';
			$day_hours[$day] = '00:00';
		}

		// get staff names
		$staff_names = array();
		$where = array(
			'accountID' => $this->accountID
		);
		$staff = $this->db->from('staff')->where($where)->get();

		if ($staff->num_rows() > 0) {
			foreach ($staff->result() as $s) {
				$staff_names[$s->staffID] = $s->first . ' ' . $s->surname;
			}
		}

		$where = [
			'accountID' => $this->accountID,
			'isMain' => 1
		];

		$contacts = $this->db->from('orgs_contacts')->where($where)->get();

		$search_fields = array(
			'staff_id' => NULL,
			'org' => NULL,
			'type_id' => NULL,
			'name' => NULL,
			'region_id' => NULL,
			'area_id' => NULL,
			'activity_id' => NULL,
			'day' => NULL,
			'staffing_type' => NULL,
			'brand_id' => NULL,
			'search' => NULL,
			'date_from' => date('d/m/Y', strtotime("this week")),
			'date_to' => date('d/m/Y', strtotime("this week + 6 days")),
			'postcode' => NULL,
			'class_size' => NULL,
			'main_contact' => NULL,
			'checkin_status' => NULL,
			'bookings_site' => NULL,
			'search' => NULL
		);

		if ($this->input->post('search')) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_start_from', 'Start From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_start_to', 'Start To', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();


			$search_fields['date_from'] = set_value('search_start_from');
			$search_fields['date_to'] = set_value('search_start_to');
		}

		$date_from_search = $dates['monday'];
		$date_to_search = $dates['sunday'];

		if ($view != 'standard') {
			$date_from_search = uk_to_mysql_date($search_fields['date_from']);
			$date_to_search = uk_to_mysql_date($search_fields['date_to']);
		}

		// get session staff
		$lesson_staff = array();
		$where = array(
			'bookings_lessons_staff.startDate <=' => $date_to_search,
			'bookings_lessons_staff.endDate >=' => $date_from_search,
			'bookings_lessons_staff.accountID' => $this->accountID
		);
		$res_staff = $this->db->select('bookings_lessons_staff.*, bookings_lessons.day')->from('bookings_lessons_staff')
			->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			if ($view == 'standard') {
				foreach ($res_staff->result() as $row) {
					// verify actual on session as above just got all staff for whole week
					if (strtotime($row->startDate) <= strtotime($dates[$row->day]) && strtotime($row->endDate) >= strtotime($dates[$row->day])) {
						$lesson_staff[$row->lessonID][$row->staffID] = $row->type;
					}
				}
			} else {
				// custom dates
				foreach ($res_staff->result() as $row) {
					$lesson_staff[$row->lessonID][$row->staffID] = $row->type;
				}
			}
		}

		// get session exceptions
		$lesson_exceptions = array();
		$where = array(
			'date >=' => $date_from_search,
			'date <=' => $date_to_search,
			'accountID' => $this->accountID
		);
		$res_staff = $this->db->from('bookings_lessons_exceptions')->where($where)->get();

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

		// get session offers
		$offered_to = [];
		if ($this->auth->has_features('offer_accept') || $this->auth->has_features('offer_accept_manual')) {
			$where = array(
				'bookings_blocks.endDate >=' => $date_from_search,
				'bookings_blocks.startDate <=' => $date_to_search,
				'offer_accept.accountID' => $this->accountID,
				'offer_accept.status' => 'offered'
			);
			$res_offered = $this->db->select('offer_accept.lessonID, staff.first, staff.surname')
			->from('offer_accept')
			->join('staff', 'offer_accept.staffID = staff.staffID', 'inner')
			->join('bookings_lessons', 'offer_accept.lessonID = bookings_lessons.lessonID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->where($where)
			->group_by('offer_accept.staffID')
			->get();

			if ($res_offered->num_rows() > 0) {
				foreach ($res_offered->result() as $row) {
					$offered_to[$row->lessonID][] = $row->first . ' ' . $row->surname;
				}
			}
		}

		// get participants
		$lesson_participants = array();
		// only super users for now (and not feed)
		if (strpos($_SERVER['REQUEST_URI'], '/feed/') === FALSE) {
			// get individual bookings
			$register_types = array(
				'children',
				'individuals',
				'children_bikeability',
				'individuals_bikeability',
				'children_shapeup',
				'individuals_shapeup'
			);
			$where = array(
				'bookings_cart_sessions.date >=' => $date_from_search,
				'bookings_cart_sessions.date <=' => $date_to_search,
				'bookings_cart_sessions.accountID' => $this->accountID,
				'bookings.type' => 'booking',
				'bookings_cart.type' => 'booking'
			);
			$res_participants = $this->db->select('bookings_cart_sessions.*')
			->from('bookings_cart_sessions')
			->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
			->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
			->where($where)
			->where_in('bookings.register_type', $register_types)
			->get();
			if ($res_participants->num_rows() > 0) {
				foreach ($res_participants->result() as $row) {
					if (!isset($lesson_participants[$row->lessonID][$row->date])) {
						$lesson_participants[$row->lessonID][$row->date] = 0;
					}
					$lesson_participants[$row->lessonID][$row->date]++;
				}
			}

			// get individual events
			$where = array(
				'bookings_blocks.endDate >=' => $date_from_search,
				'bookings_blocks.startDate <=' => $date_to_search,
				'bookings_cart_sessions.accountID' => $this->accountID,
				'bookings.type' => 'event',
				'bookings_cart.type' => 'booking'
			);
			$res_participants = $this->db->select('bookings_cart_sessions.*, bookings_lessons.day')
			->from('bookings_cart_sessions')
			->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
			->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
			->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->where($where)
			->where_in('bookings.register_type', $register_types)
			->get();
			if ($res_participants->num_rows() > 0) {
				foreach ($res_participants->result() as $row) {
					if (!isset($lesson_participants[$row->lessonID][$row->date])) {
						$lesson_participants[$row->lessonID][$row->date] = 0;
					}
					$lesson_participants[$row->lessonID][$row->date]++;
				}
			}

			// get names
			$register_types = array(
				'names',
				'bikeability',
				'shapeup'
			);
			$where = array(
				'bookings_attendance_names_sessions.date >=' => $date_from_search,
				'bookings_attendance_names_sessions.date <=' => $date_to_search,
				'bookings_attendance_names_sessions.accountID' => $this->accountID
			);
			$res_participants = $this->db->select('bookings_attendance_names_sessions.*')->from('bookings_attendance_names_sessions')->join('bookings', 'bookings_attendance_names_sessions.bookingID = bookings.bookingID', 'inner')->where($where)->where_in('bookings.register_type', $register_types)->get();
			if ($res_participants->num_rows() > 0) {
				foreach ($res_participants->result() as $row) {
					if (!isset($lesson_participants[$row->lessonID][$row->date])) {
						$lesson_participants[$row->lessonID][$row->date] = 0;
					}
					$lesson_participants[$row->lessonID][$row->date]++;
				}
			}

			// get numbers
			$register_types = array(
				'numbers'
			);
			$where = array(
				'bookings_attendance_numbers.date >=' => $date_from_search,
				'bookings_attendance_numbers.date <=' => $date_to_search,
				'bookings_attendance_numbers.accountID' => $this->accountID
			);
			$res_participants = $this->db->select('bookings_attendance_numbers.*')->from('bookings_attendance_numbers')->join('bookings', 'bookings_attendance_numbers.bookingID = bookings.bookingID', 'inner')->where($where)->where_in('bookings.register_type', $register_types)->get();
			if ($res_participants->num_rows() > 0) {
				foreach ($res_participants->result() as $row) {
					$lesson_participants[$row->lessonID][$row->date]= $row->attended;
				}
			}
		}

		// set where
		$where = array(
			$this->db->dbprefix('bookings') . '.cancelled !=' => 1,
			$this->db->dbprefix('bookings_blocks') . '.startDate <=' => $date_to_search,
			$this->db->dbprefix('bookings_blocks') . '.endDate >=' => $date_from_search,
			$this->db->dbprefix('bookings') . '.accountID' => $this->accountID
		);

		// if only own, exclude provisional unless turned on at account level
		if ($this->settings_library->get('provisional_own_timetable') != 1 && $only_own === TRUE) {
			$where[$this->db->dbprefix('bookings_blocks') . '.provisional !='] = 1;
		}

		// set up search
		$search_where = [];

		$is_search = FALSE;

		// if search
		if ($this->input->post('s')=="cancel") {
			$this->session->unset_userdata('search-timetable');
		}else if ($this->input->post()) {

			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_main_contact', 'Main Contact', 'trim|xss_clean');
			$this->form_validation->set_rules('search_org', $this->settings_library->get_label('customer'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_type_id', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_name', 'Project', 'trim|xss_clean');
			$this->form_validation->set_rules('search_postcode', 'Post Code', 'trim|xss_clean');
			$this->form_validation->set_rules('search_class_size', 'Class Size', 'trim|xss_clean');
			$this->form_validation->set_rules('search_region_id', 'Region', 'trim|xss_clean');
			$this->form_validation->set_rules('search_area_id', 'Area', 'trim|xss_clean');
			$this->form_validation->set_rules('search_activity_id', 'Activity', 'trim|xss_clean');
			$this->form_validation->set_rules('search_day', 'Day', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staffing_type', 'Staffing Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_brand_id', $this->settings_library->get_label('brand'), 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('checkin_status', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_bookings_site', 'Bookings Site', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['type_id'] = set_value('search_type_id');
			$search_fields['name'] = set_value('search_name');
			$search_fields['activity_id'] = set_value('search_activity_id');
			$search_fields['day'] = set_value('search_day');
			$search_fields['staffing_type'] = set_value('search_staffing_type');
			$search_fields['brand_id'] = set_value('search_brand_id');
			$search_fields['bookings_site'] = set_value('search_bookings_site');
			$search_fields['search'] = set_value('search');

			if ($view != 'map') {
				$search_fields['postcode'] = set_value('search_postcode');
				$search_fields['org'] = set_value('search_org');
				$search_fields['class_size'] = set_value('search_class_size');
				$search_fields['region_id'] = set_value('search_region_id');
				$search_fields['area_id'] = set_value('search_area_id');
				if ($view != 'details') {
					$search_fields['main_contact'] = set_value('search_main_contact');
				}
			}

			$search_fields['checkin_status'] = set_value('checkin_status');

			$is_search = TRUE;

		}
		elseif (is_array($this->session->userdata('search-timetable'))) {
			foreach ($this->session->userdata('search-timetable') as $key => $value) {
				$search_fields[$key] = $value;
			}
		}
		
		
		//get Exceptions Data for staff only
		$availability_exceptions = array();
		if(($search_fields["staff_id"] != "" && $search_fields["staff_id"] != NULL) || $only_own === TRUE){
			$where_exception = array("accountID" => $this->auth->user->accountID,
			"staffID" => $search_fields["staff_id"]);
			if($only_own === TRUE)
				$where_exception["staffID"] = $this->auth->user->staffID;
			$res = $this->db->from("staff_availability_exceptions")->where($where_exception)->get();
			if($res->num_rows() > 0){
				foreach($res->result() as $row){
					$availability_exceptions[$row->exceptionsID] = $row;
				}
			}
		}


		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-timetable', $search_fields);

		}


		if ($search_fields['org'] != '') {
			$search_where[] = '(`' . $this->db->dbprefix("orgs") . "`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['org']) . "%' OR `block_orgs`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['org']) . "%')";
		}

		if ($search_fields['type_id'] != '') {
			if ($search_fields['type_id'] == 'other') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons") . "`.`typeID` IS NULL";
			} else {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons") . "`.`typeID` = " . $this->db->escape($search_fields['type_id']);
			}
		}

		if ($search_fields['name'] != '') {
			$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
		}

		if ($search_fields['activity_id'] != '') {
			if ($search_fields['activity_id'] == 'other') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons") . "`.`activityID` IS NULL";
			} else {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons") . "`.`activityID` = " . $this->db->escape($search_fields['activity_id']);
			}
		}

		if ($search_fields['day'] != '') {
			$search_where[] = '`' . $this->db->dbprefix("bookings_lessons") . "`.`day` = " . $this->db->escape($search_fields['day']);
		}

		if ($search_fields['brand_id'] != '') {
			$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`brandID` = " . $this->db->escape($search_fields['brand_id']);
		}

		if ($search_fields['postcode'] != '') {
			$search_where[] = '(`' . $this->db->dbprefix("orgs_addresses") . "`.`postcode` = " . $this->db->escape($search_fields['postcode']) . ' OR `event_address`.`postcode` = ' . $this->db->escape($search_fields["postcode"]) . ')';
		}

		if ($search_fields['class_size'] != '') {
			$search_where[] = '`' . $this->db->dbprefix("bookings_lessons") . "`.`class_size` = " . $this->db->escape($search_fields['class_size']);
		}

		if ($view == 'standard' && $only_own !== TRUE && $search_fields['bookings_site'] != '' && $this->auth->has_features('online_booking')) {
			if ($search_fields['bookings_site'] == 'on') {
				$search_where[] = '(`' . $this->db->dbprefix("bookings") . '`.`public` = 1 AND `' . $this->db->dbprefix("bookings_blocks") . '`.`public` = 1)';
			} else {
				$search_where[] = '(`' . $this->db->dbprefix("bookings") . '`.`public` != 1 OR `' . $this->db->dbprefix("bookings_blocks") . '`.`public` != 1)';
			}

		}

		if ($search_fields['main_contact'] != '') {
			$search_where[] = "`contacts`.`contactID` = " . $this->db->escape($search_fields['main_contact']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('orgs.name as org, orgs.type as org_type, block_orgs.name as block_org,
		 	block_orgs.type as block_org_type, orgs_addresses.*, bookings_lessons.*, bookings.name, bookings.project,
		  	bookings_blocks.orgID as block_orgID, bookings_blocks.provisional, bookings_blocks.name as block,
		   	bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd,
			bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, bookings.brandID,
		 	brands.colour as brand_colour, bookings.type as booking_type, event_address.address1 as event_address1,
		  	event_address.address2 as event_address2, event_address.address3 as event_address3,
		   	event_address.town as event_town, event_address.county as event_county,
			event_address.postcode as event_postcode, orgs.regionID, orgs.areaID,
		 	block_orgs.regionID as block_regionID, block_orgs.areaID as block_areaID, activities.name as activity,
		  	types.name as type_name, contacts.name as main_contact, contacts.tel as main_tel, bookings_lessons.bookingID')
			->from('bookings')
			->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
			->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
			->join('orgs_contacts as contacts', 'orgs.orgID = contacts.orgID and contacts.isMain = 1', 'left')
			->join('brands', 'bookings.brandID = brands.brandID', 'left')
			->join('orgs_addresses as event_address', 'bookings.addressID = event_address.addressID', 'left')
			->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
			->join('lesson_types as types', 'types.typeID = bookings_lessons.typeID', 'left')
			->where($where)
			->where($search_where, NULL, FALSE)
			->group_by('bookings_lessons.lessonID')->get();


		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {

				$gendate = new DateTime();
				$gendate->setISODate($year, $week, $day_numbers[$row->day]);
				$lesson_date = $gendate->format('Y-m-d');

				// add to array
				$lessons[$row->lessonID] = array(
					'id' => $row->lessonID,
					'day' => $row->day,
					'date' => $dates[$row->day],
					'block' => $row->block,
					'startDate' => $row->blockStart,
					'endDate' => $row->blockEnd,
					'booking_type' => $row->booking_type,
					'project' => $row->project,
					'link' => site_url('coach/session/' . $row->lessonID . '/' . $lesson_date),
					'event' => $row->name,
					'org' => $row->org,
					'org_type' => $row->org_type,
					'address' => NULL,
					'label_classes' => '',
					'colour' => $row->brand_colour,
					'brandID' => $row->brandID,
					'region' => $row->regionID,
					'area' => $row->areaID,
					'activityID' => $row->activityID,
					'activity_group' => NULL,
					'time' => NULL,
					'time_start' => NULL,
					'time_end' => NULL,
					'length' => NULL,
					'has_block_org' => FALSE,
					'staff_ids' => array(),
					'headcoaches' => array(),
					'leadcoaches' => array(),
					'assistantcoaches' => array(),
					'observers' => array(),
					'participants' => array(),
					'offer_accept_status' => NULL,
					'offered_to' => NULL,
					'participants_actual' => 0,
					'participants_target' => $row->target_participants,
					'type_name' => $row->type_name,
					'activity_name' => $row->activity,
					'post_code' => $row->postcode,
					'class_size' => $row->class_size,
					'main_contact' => $row->main_contact,
					'main_tel' => $row->main_tel,
					'booking_id' => $row->bookingID,
					'lesson_start' => $row->lessonStart,
					'lesson_end' => $row->lessonEnd
				);

				// override org if set on block level
				if (!empty($row->block_orgID)) {
					$lessons[$row->lessonID]['has_block_org'] = TRUE;
					$lessons[$row->lessonID]['org'] = $row->block_org;
					$lessons[$row->lessonID]['org_type'] = $row->block_org_type;
				}

				// not checking week dates because of custom date range
				if ($view == 'standard') {
					// double check is within week dates and block dates and session dates (if set)
					if (strtotime($dates[$row->day]) >= strtotime($start_date) && strtotime($dates[$row->day]) <= strtotime($end_date) && strtotime($dates[$row->day]) >= strtotime($row->blockStart) && strtotime($dates[$row->day]) <= strtotime($row->blockEnd) && ((empty($row->lessonStart) && empty($row->lessonEnd)) || strtotime($dates[$row->day]) >= strtotime($row->lessonStart) && strtotime($dates[$row->day]) <= strtotime($row->lessonEnd))) {
						// all ok
					} else {
						unset($lessons[$row->lessonID]);
						continue;
					}
				}

				// filter by region
				if ($search_fields['region_id'] != '') {
					// if has block org
					if (!empty($row->block_orgID)) {
						if ($row->block_regionID != $search_fields['region_id']) {
							unset($lessons[$row->lessonID]);
							continue;
						}
					} else {
						if ($row->regionID != $search_fields['region_id']) {
							unset($lessons[$row->lessonID]);
							continue;
						}
					}
				}

				// filter by area
				if ($search_fields['area_id'] != '') {
					// if has block org
					if (!empty($row->block_orgID)) {
						if ($row->block_areaID != $search_fields['area_id']) {
							unset($lessons[$row->lessonID]);
							continue;
						}
					} else {
						if ($row->areaID != $search_fields['area_id']) {
							unset($lessons[$row->lessonID]);
							continue;
						}
					}
				}

				// get staff
				if (array_key_exists($row->lessonID, $lesson_staff) && is_array($lesson_staff[$row->lessonID])) {
					foreach ($lesson_staff[$row->lessonID] as $staffID => $staffType) {
						$lessons[$row->lessonID]['staff_ids'][$staffID] = $staffType;
					}
				}

				// check for session exceptions (for custom dates we need to handle exceptions in other way)
				if ($view == 'standard') {
					if (array_key_exists($row->lessonID, $lesson_exceptions) && is_array($lesson_exceptions[$row->lessonID])) {
						foreach ($lesson_exceptions[$row->lessonID] as $exception_info) {
							// if cancellation, remove
							if ($exception_info['type'] == 'cancellation') {
								unset($lessons[$row->lessonID]);
								continue 2;
							}

							// staff change
							if (array_key_exists($exception_info['fromID'], $lessons[$row->lessonID]['staff_ids'])) {
								// swap if moved to another staff
								if (!empty($exception_info['staffID'])) {
									$lessons[$row->lessonID]['staff_ids'][$exception_info['staffID']] = $lessons[$row->lessonID]['staff_ids'][$exception_info['fromID']];
								}
								if (isset($lessons[$row->lessonID]['staff_ids'][$exception_info['fromID']])) {
									unset($lessons[$row->lessonID]['staff_ids'][$exception_info['fromID']]);
								}
							}
						}
					}
				}

				// if only showing own, skip, if not in lesson
				if ($only_own === TRUE && !array_key_exists($this->staffID, $lessons[$row->lessonID]['staff_ids'])) {
					unset($lessons[$row->lessonID]);
					continue;
				}

				// loop through staff
				foreach ($lessons[$row->lessonID]['staff_ids'] as $staff_id => $type) {

					// map staff ids to staff names and add to head coach, etc arrays
					if (!array_key_exists($staff_id, $staff_names)) {
						unset($lessons[$row->lessonID]['staff_ids'][$staff_id]);
						continue;
					}

					switch ($type) {
						case 'head':
							$lessons[$row->lessonID]['headcoaches'][] = $staff_names[$staff_id];
							break;
						case 'lead':
							$lessons[$row->lessonID]['leadcoaches'][] = $staff_names[$staff_id];
							break;
						case 'assistant':
						default:
							$lessons[$row->lessonID]['assistantcoaches'][] = $staff_names[$staff_id];
							break;
						case 'observer':
							$lessons[$row->lessonID]['observers'][] = $staff_names[$staff_id];
							break;
						case 'participant':
							$lessons[$row->lessonID]['participants'][] = $staff_names[$staff_id];
							break;
					}
				}

				// sort all staff
				sort($lessons[$row->lessonID]['headcoaches']);
				sort($lessons[$row->lessonID]['leadcoaches']);
				sort($lessons[$row->lessonID]['assistantcoaches']);
				sort($lessons[$row->lessonID]['observers']);
				sort($lessons[$row->lessonID]['participants']);

				// filtering by stafffing type
				if (!empty($search_fields['staffing_type'])) {
					switch ($search_fields['staffing_type']) {
						case 'head':
						case 'lead':
						case 'assistant':
							$staffing_type_key = $search_fields['staffing_type'] . 'coaches';
							break;
						default:
							$staffing_type_key =  $search_fields['staffing_type'] . 's';
							break;
					}
					// if none of such staff, skip lesson
					if (count($lessons[$row->lessonID][$staffing_type_key]) == 0) {
						unset($lessons[$row->lessonID]);
						continue;
					}
				}

				// check for min staff
				$staff_reqs_met = TRUE;
				$staff_type_map = [
					'head' => 'headcoaches',
					'lead' => 'leadcoaches',
					'assistant' => 'assistantcoaches',
					'observer' => 'observers',
					'participant' => 'participants'
				];
				$required_staff_for_session = $this->settings_library->get_required_staff_for_session();
				foreach ($required_staff_for_session as $type => $label) {
					$staff_count = 0;
					$field = 'staff_required_' . $type;
					if (isset($lessons[$row->lessonID][$staff_type_map[$type]])) {
						$staff_count = count($lessons[$row->lessonID][$staff_type_map[$type]]);
					}
					if ($row->$field > 0 && $staff_count < $row->$field) {
						$staff_reqs_met = FALSE;
					}
				}
				// min 1 staff
				if (count($lessons[$row->lessonID]['staff_ids']) == 0) {
					$staff_reqs_met = FALSE;
				}
				// if not, add class
				if ($staff_reqs_met !== TRUE) {
					$lessons[$row->lessonID]['label_classes'] .= ' label-nostaff nostaff';
					$lessons[$row->lessonID]['colour'] = "pink";
				}

				// filtering by staff ID
				// able to filter only on standard view, because of exceptions (we can not remove the whole lesson from search in case of custom dates)
				if (!empty($search_fields['staff_id']) && $view == 'standard') {
					if (!array_key_exists($search_fields['staff_id'], $lessons[$row->lessonID]['staff_ids'])) {
						unset($lessons[$row->lessonID]);
						continue;
					}
				}

				// admin link
				if ($only_own !== TRUE) {
					$lessons[$row->lessonID]['link'] = site_url('bookings/sessions/edit/' . $row->lessonID);
				}

				// address
				if ($row->booking_type == 'booking') {

					// booking address (from lesson)
					$address_parts = array();
					if (!empty($row->address1)) {
						$address_parts[] = $row->address1;
					}
					if (!empty($row->address2)) {
						$address_parts[] = $row->address2;
					}
					if (!empty($row->address3)) {
						$address_parts[] = $row->address3;
					}
					if (!empty($row->town)) {
						$address_parts[] = $row->town;
					}
					if (!empty($row->county)) {
						$address_parts[] = $row->county;
					}
					if (!empty($row->postcode)) {
						$address_parts[] = $row->postcode;
					}

					if (count($address_parts)) {
						$lessons[$row->lessonID]['address'] = implode(', ', $address_parts);
					}

				} else {

					// event address (from event)
					$event_address_parts = array();
					if (!empty($row->event_address1)) {
						$event_address_parts[] = $row->event_address1;
					}
					if (!empty($row->event_address2)) {
						$event_address_parts[] = $row->event_address2;
					}
					if (!empty($row->event_address3)) {
						$event_address_parts[] = $row->event_address3;
					}
					if (!empty($row->town)) {
						$event_address_parts[] = $row->town;
					}
					if (!empty($row->county)) {
						$event_address_parts[] = $row->county;
					}
					if (!empty($row->postcode)) {
						$event_address_parts[] = $row->postcode;
					}

					if (count($event_address_parts)) {
						$lessons[$row->lessonID]['address'] = implode(', ', $event_address_parts);
					}

				}

				// activity and group
				$activity_group_parts = array();

				if (!empty($row->activity)) {
					$activity_group_parts['activity'] = $row->activity;
				} else if (!empty($row->activity_other)) {
					$activity_group_parts['activity'] = $row->activity_other;
				}
				if (!empty($row->activity_desc)) {
					if (!array_key_exists('activity', $activity_group_parts)) {
						$activity_group_parts['activity'] = NULL;
					}
					$activity_group_parts['activity'] .= ' - ' . $row->activity_desc;
				}

				if (!empty($row->group)) {
					if ($row->group == 'other') {
						$activity_group_parts['group'] = $row->group_other;
					} else {
						$activity_group_parts['group'] = $this->crm_library->format_lesson_group($row->group);
					}
				}

				if (count($activity_group_parts)) {
					$lessons[$row->lessonID]['activity_group'] = implode(', ', $activity_group_parts);
				}

				// if label empty, set to group
				if (empty($lessons[$row->lessonID]['colour'])) {
					$lessons[$row->lessonID]['colour'] = 'light-blue';
				}

				// if viewing own, or searching for specific, change times shown to match theirs
				if ($only_own || !empty($search_fields['staff_id'])) {
					if (!empty($search_fields['staff_id'])) {
						$staffID = $search_fields['staff_id'];
					} else {
						$staffID = $this->staffID;
					}

					// if on this lesson, look up
					if (array_key_exists($staffID, $lessons[$row->lessonID]['staff_ids'])) {
						$where_times = array(
							'lessonID' => $row->lessonID,
							'staffID' => $staffID,
							'startDate <=' => $date_to_search,
							'endDate >=' => $date_from_search,
							'accountID' => $this->accountID
						);
						$res_times = $this->db->from('bookings_lessons_staff')->where($where_times)->get();
						if ($res_times->num_rows() > 0) {
							foreach ($res_times->result() as $row_time) {
								$row->startTime = $row_time->startTime;
								$row->endTime = $row_time->endTime;
							}
						}
					}
				}

				// if provisional, add stripe class
				if ($row->provisional == 1) {
					$lessons[$row->lessonID]['label_classes'] .= ' striped';
				}

				// show offer accept status
				if ($this->auth->has_features('offer_accept') || $this->auth->has_features('offer_accept_manual')) {
					$offer_accept_status = $row->offer_accept_status;
					switch ($offer_accept_status) {
						case 'offering':
							$offer_accept_status = 'offered';
							if (array_key_exists($row->lessonID, $offered_to)) {
								sort($offered_to[$row->lessonID]);
								$lessons[$row->lessonID]['offered_to'] = implode(', ', $offered_to[$row->lessonID]);
							}
							break;
						case 'exhausted':
							$offer_accept_status = 'declined';
							break;
					}
					$lessons[$row->lessonID]['offer_accept_status'] = ucwords($offer_accept_status);
					if (!empty($row->offer_accept_reason)) {
						$lessons[$row->lessonID]['offer_accept_status'] .= ' (' . $row->offer_accept_reason . ')';
					}
				}

				$lessons[$row->lessonID]['time'] = substr($row->startTime, 0, 5) . '-' . substr($row->endTime, 0 ,5);
				$lessons[$row->lessonID]['time_start'] = substr($row->startTime, 0, 5);
				$lessons[$row->lessonID]['time_end'] = substr($row->endTime, 0 ,5);
				$lessons[$row->lessonID]['length'] = strtotime($row->endTime) - strtotime($row->startTime);

				// get participants
				if (isset($lesson_participants[$row->lessonID][$dates[$row->day]])) {
					$lessons[$row->lessonID]['participants_actual'] = $lesson_participants[$row->lessonID][$dates[$row->day]];
				}
				
				//check exceptions
				$flag = 0;
				if($search_fields["staff_id"] != null && $search_fields["staff_id"] != ""){
					foreach($availability_exceptions as $exceptions){
						$newdate = $dates[$row->day]." ".$row->startTime;
						if($exceptions->from <= $newdate && $exceptions->to > $newdate){
							$flag = 1;
						}
					}
				}
				
				// work out times
				if($flag == 0){
					$lesson_seconds += $lessons[$row->lessonID]['length'];
					$day_seconds[$row->day] += $lessons[$row->lessonID]['length'];

					// increase session count
					$lesson_count++;
				}

				// re-arrange keys for session so can add to correct places and sort
				$slot = intval(substr($row->startTime, 0, 2));
				$startTime = substr($row->startTime, 0, 5);
				$list_lessons[$row->lessonID] = $lessons[$row->lessonID];
				$lessons[$row->day][$slot][$startTime . '-' . $row->lessonID] = $lessons[$row->lessonID];
				unset($lessons[$row->lessonID]);
			}
		}

		if ($return === TRUE) {
			return $lessons;
		}

		// convert session seconds into time format
		$lesson_hours = sprintf("%02d%s%02d%s", floor($lesson_seconds/3600), 'h', ($lesson_seconds/60)%60, 'm');
		foreach ($days as $day) {
			$day_hours[$day] = sprintf("%02d%s%02d%s", floor($day_seconds[$day]/3600), 'h', ($day_seconds[$day]/60)%60, 'm');
		}

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
			'accountID' => $this->accountID,
			'non_delivery !=' => 1
		);
		// include single inactive staff if searching from staff Timetable tab
		$where_or = [
			"`active` = 1"
		];
		if (isset($search_fields['staff_id']) && !empty($search_fields['staff_id'])) {
			$where_or[] = "`staffID` = " . $this->db->escape($search_fields['staff_id']);
		}
		$where2 = '(' . implode(' OR ', $where_or) . ')';
		$staff = $this->db->from('staff')->where($where)->where($where2, NULL, FALSE)->order_by('first asc, surname asc')->get();

		// regions and areas
		$where = array(
			'accountID' => $this->accountID
		);
		$regions = $this->db->from('settings_regions')->where($where)->order_by('name asc')->get();
		$areas = $this->db->from('settings_areas')->where($where)->order_by('name asc')->get();

		// brands
		$where = array(
			'accountID' => $this->accountID,
			'active' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// activities
		$where = array(
			'accountID' => $this->accountID,
			'active' => 1
		);
		$activities = $this->db->from('activities')->where($where)->order_by('name asc')->get();

		// session types
		$where = array(
			'accountID' => $this->accountID,
			'active' => 1
		);
		$lesson_types = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();

		$markers = [];
		$details_data = [];
		switch ($view) {
			case 'map':
			case 'details':
				if ($only_own) {
					show_404();
				}
				if (!$this->auth->has_features('lesson_checkins')) {
					show_404();
				}
				$search_where = [];
				if (count($list_lessons) > 0) {

					foreach ($list_lessons as $key => $lesson) {
						$dates_between = $this->generateDatesForSearch($lesson, $date_from_search, $date_to_search);

						if (isset($dates_between[$lesson['day']])) {
							foreach ($dates_between[$lesson['day']] as $date) {
								$lesson['date'] = $date;

								if (strtotime($date) >= strtotime($date_from_search) && strtotime($date) <= strtotime($date_to_search) && strtotime($date) >= strtotime($lesson['startDate']) && strtotime($date) <= strtotime($lesson['endDate']) && ((empty($lesson['lesson_start']) && empty($lesson['lesson_end'])) || strtotime($date) >= strtotime($lesson['lesson_start']) && strtotime($date) <= strtotime($lesson['lesson_end']))) {
									// all ok
								} else {
									unset($list_lessons[$key]);
									continue;
								}

								//check exceptions
								if (array_key_exists($lesson['id'], $lesson_exceptions) && is_array($lesson_exceptions[$lesson['id']])) {
									foreach ($lesson_exceptions[$lesson['id']] as $exception_info) {
										// if cancellation, remove
										if ($exception_info['type'] == 'cancellation' && $exception_info['date'] == $date) {
											unset($list_lessons[$key]);
											continue;
										}

										// staff change
										if (array_key_exists($exception_info['fromID'], $lesson['staff_ids'])) {
											// swap if moved to another staff
											if (!empty($exception_info['staffID'])) {
												$lesson['staff_ids'][$exception_info['staffID']] = $lesson['staff_ids'][$exception_info['fromID']];
											}
											if (isset($lesson['staff_ids'][$exception_info['fromID']])) {
												unset($lesson['staff_ids'][$exception_info['fromID']]);
											}
										}
									}

									$lesson['headcoaches'] = [];
									$lesson['leadcoaches'] = [];
									$lesson['assistantcoaches'] = [];
									$lesson['observers'] = [];
									$lesson['participants'] = [];

									//rewrite headcoaches, etc.
									foreach ($lesson['staff_ids'] as $staff_id => $type) {

										// map staff ids to staff names and add to head coach, etc arrays
										if (!array_key_exists($staff_id, $staff_names)) {
											unset($lesson['staff_ids'][$staff_id]);
											continue;
										}

										switch ($type) {
											case 'head':
												$lesson['headcoaches'][] = $staff_names[$staff_id];
												break;
											case 'lead':
												$lesson['leadcoaches'][] = $staff_names[$staff_id];
												break;
											case 'assistant':
											default:
												$lesson['assistantcoaches'][] = $staff_names[$staff_id];
												break;
											case 'observer':
												$lesson['observers'][] = $staff_names[$staff_id];
												break;
											case 'participant':
												$lesson['participants'][] = $staff_names[$staff_id];
												break;
										}
									}
								}
							}
						}
					}


					$ids = array_keys($list_lessons);

					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_checkins") . "`.`lessonID`
					IN (" . implode(',', $ids) . ")";

					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_checkins") . "`.`date` >= " . $this->db->escape($date_from_search);

					$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_checkins") . "`.`date` <= " . $this->db->escape($date_to_search);

					$search_where = '(' . implode(' AND ', $search_where) . ')';

					$markers = $this->crm_library->get_checkins([
						'bookings_lessons_checkins.accountID' => $this->accountID
					], $search_where, [
						'date_from' => $date_from_search,
						'date_to' => $date_to_search
					], false);

					$markers = array_values($markers);

					$markers = $this->crm_library->prepare_markers($markers, [
						'date_from' => $date_from_search,
						'date_to' => $date_to_search
					]);

					foreach ($markers as $key => $marker) {
						if (!empty($search_fields['staffing_type'])) {
							if ($marker['role'] != $search_fields['staffing_type']) {
								unset($markers[$key]);
								continue;
							}
						}
						if (!empty($search_fields['staff_id'])) {
							if ($marker['staff_id'] != $search_fields['staff_id']) {
								unset($markers[$key]);
								continue;
							}
						}

						//filtering by checkin status
						if (!empty($search_fields['checkin_status'])) {
							if ($marker['colour'] != $search_fields['checkin_status']) {
								continue;
							}
						}

						$details_data[] = [
							'staff' => $marker['staff'],
							'first_lesson_org' => $marker['orgs'][0],
							'first_lesson_time' => $marker['lesson_times'][0],
							'check_in_times' => $marker['checkin_times'],
							'check_in_status' => $marker['colour'],
							'last_lesson_ord' => array_pop($marker['orgs']),
							'last_lesson_time' => array_pop($marker['lesson_times']),
							'check_out_times' => $marker['checkout_times'],
							'not_checked_in' => $marker['not_checked_in']
						];
					}
				}
				break;
		}


		$markers = array_values($markers);
		

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'section' => $section,
			'current_page' => $current_page,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'timetable_base' => $timetable_base,
			'lessons' => $lessons,
			'week' => $week,
			'year' => $year,
			'days' => $days,
			'time_slots' => $time_slots,
			'next_week' => $next_week,
			'next_year' => $next_year,
			'prev_week' => $prev_week,
			'prev_year' => $prev_year,
			'lesson_count' => $lesson_count,
			'lesson_hours' => $lesson_hours,
			'day_hours' => $day_hours,
			'is_search' => $is_search,
			'staff' => $staff,
			'regions' => $regions,
			'areas' => $areas,
			'brands' => $brands,
			'activities' => $activities,
			'switch_day' => $switch_day,
			'only_own' => $only_own,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'lesson_types' => $lesson_types,
			'view' => $view,
			'markers' => $markers,
			'details_data' => $details_data,
			'contacts' => $contacts,
			'max_weeks' => $max_weeks,
			'availability_exceptions' => $availability_exceptions,
			'search' => true
		);
		

		// load view
		$this->crm_view('bookings/timetable', $data);
	}

	private function generateDatesForSearch($lesson, $date_from_search, $date_to_search) {
		$date_from = strtotime($date_from_search);
		if (strtotime($lesson['startDate']) > strtotime($date_from_search)) {
			$date_from = strtotime($lesson['startDate']);
		}

		$date_to = strtotime($date_to_search);
		if (strtotime($lesson['endDate']) < $date_to) {
			$date_to = strtotime($lesson['endDate']);
		}

		$dates_between = [];
		while (true) {
			$dates_between[strtolower(date('l', $date_from))][] = date('Y-m-d', $date_from);

			if ($date_from >= $date_to)
				break;

			$date_from += 86400;
		}

		return $dates_between;
	}


	/**
	 * calendar feed
	 * @return void
	 */
	public function feed()
	{

		// set defaults
		$title = 'Calendar Feed';
		$submit_to = 'timetable/feed';
		$return_to = 'timetable';
		$icon = 'rss';
		$current_page = 'feed';
		$section = 'timetable_own';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to Timetable</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// set validation rules
			$this->form_validation->set_rules('action', 'Action', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				switch (set_value('action')) {
					case 'enable':
						$data = array(
							'feed_enabled' => 1,
							'feed_key' => $this->crm_library->generate_feed_key()
						);
						$where = array(
							'staffID' => $this->auth->user->staffID
						);
						$this->db->update('staff', $data, $where, 1);
						if ($this->db->affected_rows() > 0) {
							$this->session->set_flashdata('success', 'Calendar feed has been enabled successfully.');
						} else {
							$this->session->set_flashdata('info', 'Error saving data, please try again.');
						}
						break;
					case 'disable':
						$data = array(
							'feed_enabled' => 0,
							'feed_key' => NULL
						);
						$where = array(
							'staffID' => $this->auth->user->staffID
						);
						$this->db->update('staff', $data, $where, 1);
						if ($this->db->affected_rows() > 0) {
							$this->session->set_flashdata('success', 'Calendar feed has been disabled successfully.');
						} else {
							$this->session->set_flashdata('info', 'Error saving data, please try again.');
						}
						break;
				}

				redirect($submit_to);
				exit();
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/feed', $data);
	}

	/**
	 * global view
	 * @param array $data
	 * @return mixed
	 */
	 private function global($data) {
		// add additional data
		$data['title'] = 'Global View';
		$data['icon'] = 'globe';
		$data['page_base'] .= '/global';
		$data['search'] = FALSE;

		// if viewing block, pass to another method
		if (!empty($data['blockID'])) {
			return $this->global_sessions($data['blockID'], $data);
		}

		$data['search_fields'] = [
			'date_from' => NULL,
			'date_to' => NULL,
			'booking_id' => NULL,
			'brand_id' => NULL,
			'org_id' => NULL,
			'activity_id' => NULL,
			'staff_id' => NULL,
			'unstaffed' => NULL,
			'terms_accepted' => NULL
		];

		// if search
		$search_store = 'search-timetable';
		$search_where = [];
		$block_search_where = [];
		if ($this->input->get()) {

			$data['search_fields']['date_from'] = $this->input->get('date_from');
			$data['search_fields']['date_to'] = $this->input->get('date_to');
			$data['search_fields']['booking_id'] = $this->input->get('booking_id');
			$data['search_fields']['brand_id'] = $this->input->get('brand_id');
			$data['search_fields']['org_id'] = $this->input->get('org_id');
			$data['search_fields']['activity_id'] = $this->input->get('activity_id');
			$data['search_fields']['staff_id'] = $this->input->get('staff_id');
			$data['search_fields']['unstaffed'] = $this->input->get('unstaffed');
			$data['search_fields']['terms_accepted'] = $this->input->get('terms_accepted');

			$data['search'] = TRUE;
		} else if (($this->crm_library->last_segment() == 'recall' || (stripos($this->agent->referrer(), '/timetable') !== FALSE && $this->agent->referrer() != current_url())) && is_array($this->session->userdata($search_store))) {
			foreach ($this->session->userdata($search_store) as $key => $value) {
				$data['search_fields'][$key] = $value;
			}
			$data['search'] = TRUE;
		} else {
			$this->session->unset_userdata($search_store);
		}

		// default dates
		$date_from = date('Y-m-d');
		$date_to = date('Y-m-d', strtotime('+3 months'));

		// set date vars for easier use
		if ($this->input->get('date_from') != '') {
			$start_from = uk_to_mysql_date($data['search_fields']['date_from']);
			if ($start_from !== FALSE) {
				$date_from = $start_from;
			}
		}
		if ($this->input->get('date_to') != '') {
			$end_to = uk_to_mysql_date($data['search_fields']['date_to']);
			if ($end_to !== FALSE) {
				$date_to = $end_to;
			}
		}

		// save dates to search
		$data['search_fields']['date_from'] = mysql_to_uk_date($date_from);
		$data['search_fields']['date_to'] = mysql_to_uk_date($date_to);

		if ($data['search'] === TRUE) {
			// store search fields
			$this->session->set_userdata($search_store, $data['search_fields']);

			if ($data['search_fields']['booking_id'] != '') {
				$search_where['bookings.bookingID'] = $data['search_fields']['booking_id'];
			}

			if ($data['search_fields']['brand_id'] != '') {
				$search_where['bookings.brandID'] = $data['search_fields']['brand_id'];
			}

			if ($data['search_fields']['org_id'] != '') {
				$search_where['bookings.orgID'] = $data['search_fields']['org_id'];
				$block_search_where['bookings_blocks.orgID'] = $data['search_fields']['org_id'];
			}

			if ($data['search_fields']['activity_id'] != '') {
				$search_where['bookings_lessons.activityID'] = $data['search_fields']['activity_id'];
				$block_search_where['bookings_lessons.activityID'] = $data['search_fields']['activity_id'];
			}

			if ($data['search_fields']['staff_id'] != '') {
				$block_search_where['staff_filter.staffID'] = $data['search_fields']['staff_id'];
			}

			switch ($data['search_fields']['terms_accepted']) {
				case 'yes':
					$block_search_where['bookings_blocks.terms_accepted'] = 1;
					break;
				case 'no':
					$block_search_where['bookings_blocks.terms_accepted !='] = 1;
					break;
			}
		}

		// loop up projects/contracts
		$data['booking_types'] = [
			'projects' => 'Projects',
			'contracts' => 'Contracts'
		];
		$bookingIDs = [];
		$data['bookings_participants'] = [];
		foreach ($data['booking_types'] as $type => $label) {
			$where = [
				'bookings.cancelled !=' => 1,
				'bookings_blocks.startDate <=' => $date_to,
				'bookings_blocks.endDate >=' => $date_from,
				'bookings.accountID' => $this->accountID
			];

			if ($type == 'projects') {
				$where['bookings.project'] = 1;
				// projects list for search dropdown
				$data[$type . '_list'] = $this->db->select('bookings.bookingID, bookings.name')
					->from('bookings')
					->join('bookings_blocks', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')
					->where($where)
					->order_by('bookings.name asc')
					->get();
			} else {
				$where['bookings.project !='] = 1;
			}
			$data[$type] = $this->db->select("bookings.bookingID, bookings.project, bookings.name,
			 	bookings.startDate, bookings.endDate, brands.name as brand,
			  	brands.colour as brand_colour, orgs.name as org,
			   	GROUP_CONCAT(DISTINCT " . $this->db->dbprefix('activities') . ".name) as activities,
				GROUP_CONCAT(DISTINCT " . $this->db->dbprefix('bookings_lessons') . ".activity_other) as activities_other")
				->from('bookings')
				->join('bookings_blocks', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')
				->join('brands', 'bookings.brandID = brands.brandID', 'left')
				->join('orgs', 'bookings.orgID = orgs.orgID', 'left')
				->join('bookings_lessons', 'bookings.bookingID = bookings_lessons.bookingID', 'left')
				->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
				->where($where)
				->where($search_where)
				->group_by('bookings.bookingID')
				->order_by('bookings.endDate, bookings.startDate')
				->get();
			// store booking IDs for blocks query
			foreach ($data[$type]->result() as $row) {
				$bookingIDs[$row->bookingID] = $row->bookingID;
				$data['bookings_participants'][$row->bookingID] = 0;
			}
		}

		// get blocks
		$data['blocks'] = [];
		$blockIDs = [];
		if (count($bookingIDs) > 0) {
			$where = [
				'bookings_blocks.startDate <=' => $date_to,
				'bookings_blocks.endDate >=' => $date_from,
				'bookings_blocks.accountID' => $this->accountID
			];

			$blocks = $this->db->select("bookings_blocks.bookingID, bookings_blocks.orgID, bookings_blocks.blockID,
			 	bookings_blocks.name, bookings_blocks.startDate, bookings_blocks.endDate, bookings_blocks.terms_accepted,
			  	bookings.project, bookings.register_type, orgs.name as org, orgs_contacts.name as contact_name,
			   	orgs_contacts.tel as contact_tel, orgs_contacts.mobile as contact_mobile, orgs_contacts.email as contact_email,
				bookings_orgs_contacts.name as booking_contact_name, bookings_orgs_contacts.tel as booking_contact_tel,
			 	bookings_orgs_contacts.mobile as booking_contact_mobile, bookings_orgs_contacts.email as booking_contact_email,
			 	bookings_lessons_staff.recordID, bookings_lessons.lessonID,
			  	GROUP_CONCAT(DISTINCT " . $this->db->dbprefix('activities') . ".name) as activities,
			   	GROUP_CONCAT(DISTINCT " . $this->db->dbprefix('bookings_lessons') . ".activity_other) as activities_other,
				GROUP_CONCAT(DISTINCT CONCAT(" . $this->db->dbprefix('staff') . ".first, ' ', " . $this->db->dbprefix('staff') . ".surname)) as staff")
				->from('bookings_blocks')
				->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')
				->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'left')
				->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID', 'left')
				->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'left')
				->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'left')
				->join('bookings_lessons_staff as staff_filter', 'bookings_lessons.lessonID = staff_filter.lessonID', 'left') // another lessons_staff join so still get full list in staff column while filtering by staff ID
				->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
				->join('orgs_contacts', 'bookings_blocks.orgID = orgs_contacts.orgID and orgs_contacts.isMain = 1', 'left')
				->join('orgs_contacts as bookings_orgs_contacts', 'bookings.orgID = bookings_orgs_contacts.orgID and bookings_orgs_contacts.isMain = 1', 'left')
				->where($where)
				->where_in('bookings_blocks.bookingID', $bookingIDs)
				->where($block_search_where)
				->group_by('bookings_blocks.blockID')
				->order_by('bookings_blocks.endDate, bookings_blocks.startDate')
				->get();

			$data['unstaffed_lessons'] = [];
			// loop blocks
			if ($blocks->num_rows() > 0) {
				foreach ($blocks->result() as $block) {
					// get participants (projects only)
					$block->participants = 0;
					if ($block->project == 1) {
						if ($block->register_type === 'numbers') {
							// numbers
							$where = [
								'accountID' => $this->accountID,
								'blockID' => $block->blockID,
								'date >=' => $date_from,
								'date <=' => $date_to
							];
							$res = $this->db->select("attendanceID, SUM(attended) AS count")
								->from('bookings_attendance_numbers')
								->where($where)
								->group_by('blockID')
								->get();
							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$block->participants = $row->count;
								}
							}
						} else if (in_array($block->register_type, ['names', 'bikeability', 'shapeup'])) {
							// names (attended only)
							$where = [
								'accountID' => $this->accountID,
								'blockID' => $block->blockID,
								'date >=' => $date_from,
								'date <=' => $date_to
							];
							$block->participants = $this->db->select("attendanceID")
								->from('bookings_attendance_names_sessions')
								->where($where)
								->group_by('participantID')
								->get()->num_rows();
						} else {
							// individuals or children (attended only)
							$where = [
								'bookings_cart_sessions.accountID' => $this->accountID,
								'bookings_cart_sessions.blockID' => $block->blockID,
								'bookings_cart_sessions.attended' => 1,
								'bookings_cart.type' => 'booking',
								'bookings_cart_sessions.date >=' => $date_from,
								'bookings_cart_sessions.date <=' => $date_to
							];
							$block->participants = $this->db->select("sessionID")
								->from('bookings_cart_sessions')
								->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
								->where($where)
								->group_by('bookings_cart_sessions.contactID, bookings_cart_sessions.childID')
								->get()->num_rows();
						}
						// add to bookings count
						$data['bookings_participants'][$block->bookingID] += $block->participants;
					}

					// assign block to bookings
					$data['blocks'][$block->bookingID][] = $block;
					$blockIDs[] = $block->blockID;

					if (empty($block->recordID)) {
						$data['unstaffed_lessons'][$block->bookingID][$block->blockID][$block->lessonID] = $block->lessonID;
					}
				}
			}
		}

		// brands
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$data['brands'] = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// orgs
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$data['orgs'] = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// activities
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$data['activities'] = $this->db->from('activities')->where($where)->order_by('name asc')->get();

		// staff
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$data['staff'] = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// load view
 		$this->crm_view('bookings/timetable_global', $data);
	}

	/**
	 * global sessions view
	 * @param integer $blockID
	 * @param array $data
	 * @return mixed
	 */
	private function global_sessions($blockID, $data) {
		$data['page_base'] .= '/' . $blockID;

		// look up block
		$where = array(
			'bookings_blocks.blockID' => $blockID,
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);
		$res = $this->db
			->select('bookings_blocks.*, bookings.project, bookings.name as project_name, bookings.register_type')
			->from('bookings_blocks')
			->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
			->where($where)
			->limit(1)
			->get();
		if ($res->num_rows() === 0) {
			show_404();
		}
		foreach ($res->result() as $data['block']) {}

		// set title
		$title_bits = [];
		if ($data['block']->project == 1) {
			$title_bits[] = $data['block']->project_name;
		}
		$title_bits[] = $data['block']->name;
		$data['title'] = implode(' > ', $title_bits);

		// add breadcrumb
		$data['breadcrumb_levels'] = [
			'bookings/timetable/global' => 'Global View'
		];

		// add additional data
		$data['search_fields'] = [
			'date_from' => NULL,
			'date_to' => NULL,
			'activity_id' => NULL,
			'type_id' => NULL,
			'staff_id' => NULL,
			'day' => NULL
		];

		// if search
		$search_store = 'search-timetable';
		$search_where = [];
		if ($this->input->get()) {
			$data['search_fields']['date_from'] = $this->input->get('date_from');
			$data['search_fields']['date_to'] = $this->input->get('date_to');
			$data['search_fields']['activity_id'] = $this->input->get('activity_id');
			$data['search_fields']['type_id'] = $this->input->get('type_id');
			$data['search_fields']['staff_id'] = $this->input->get('staff_id');
			$data['search_fields']['day'] = $this->input->get('day');

			$data['search'] = TRUE;
		} else if (($this->crm_library->last_segment() == 'recall' || (stripos($this->agent->referrer(), '/timetable') !== FALSE && $this->agent->referrer() != current_url())) && is_array($this->session->userdata($search_store))) {
			foreach ($this->session->userdata($search_store) as $key => $value) {
				$data['search_fields'][$key] = $value;
			}
			$data['search'] = TRUE;
		} else {
			$this->session->unset_userdata($search_store);
		}

		// default dates
		$date_from = $data['block']->startDate;
		$date_to = $data['block']->endDate;

		// set date vars for easier use
		if ($this->input->get('date_from') != '') {
			$start_from = uk_to_mysql_date($data['search_fields']['date_from']);
			if ($start_from !== FALSE) {
				$date_from = $start_from;
			}
		}
		if ($this->input->get('date_to') != '') {
			$end_to = uk_to_mysql_date($data['search_fields']['date_to']);
			if ($end_to !== FALSE) {
				$date_to = $end_to;
			}
		}

		// save dates to search
		$data['search_fields']['date_from'] = mysql_to_uk_date($date_from);
		$data['search_fields']['date_to'] = mysql_to_uk_date($date_to);

		if ($data['search'] === TRUE) {
			// store search fields
			$this->session->set_userdata($search_store, $data['search_fields']);

			if ($data['search_fields']['activity_id'] != '') {
				$search_where['bookings_lessons.activityID'] = $data['search_fields']['activity_id'];
			}

			if ($data['search_fields']['type_id'] != '') {
				$search_where['bookings_lessons.typeID'] = $data['search_fields']['type_id'];
			}

			if ($data['search_fields']['staff_id'] != '') {
				$search_where['staff_filter.staffID'] = $data['search_fields']['staff_id'];
			}

			if ($data['search_fields']['day'] != '') {
				$search_where['bookings_lessons.day'] = $data['search_fields']['day'];
			}
		}

		// get sessions
		$data['sessions'] = [];
		$where = [
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons.accountID' => $this->accountID
		];
		$data['sessions'] = $this->db->select("bookings_lessons.*, activities.name as activity, lesson_types.name as type, COUNT(staffed_head.recordID) as head_count, COUNT(staffed_lead.recordID) as lead_count, COUNT(staffed_assistant.recordID) as assistant_count, GROUP_CONCAT(DISTINCT CONCAT(" . $this->db->dbprefix('staff') . ".first, ' ', " . $this->db->dbprefix('staff') . ".surname)) as staff")
			->from('bookings_lessons')
			->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'left')
			->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'left')
			->join('bookings_lessons_staff as staff_filter', 'bookings_lessons.lessonID = staff_filter.lessonID', 'left') // another lessons_staff join so still get full list in staff column while filtering by staff ID
			->join('bookings_lessons_staff as staffed_head', 'bookings_lessons.lessonID = staffed_head.lessonID', 'left') // calc staffed
			->join('bookings_lessons_staff as staffed_lead', 'bookings_lessons.lessonID = staffed_lead.lessonID', 'left') // calc staffed
			->join('bookings_lessons_staff as staffed_assistant', 'bookings_lessons.lessonID = staffed_assistant.lessonID', 'left') // calc staffed
			->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->where($where)
			->where($search_where)
			->where('(' . $this->db->dbprefix('bookings_lessons') .'.startDate >= ' . $this->db->escape($date_from) . ' OR ' . $this->db->dbprefix('bookings_lessons') . '.startDate IS NULL) AND (' . $this->db->dbprefix('bookings_lessons') .'.endDate <= ' . $this->db->escape($date_to) . ' OR ' . $this->db->dbprefix('bookings_lessons') . '.endDate IS NULL)', NULL, FALSE)
			->group_by('bookings_lessons.lessonID')
			->order_by('bookings_lessons.day, bookings_lessons.startTime, bookings_lessons.endTime')
			->get();

		// loop blocks
		$data['participants'] = [];
		if ($data['sessions']->num_rows() > 0) {
			foreach ($data['sessions']->result() as $session) {
				// get participants (projects only)
				$data['participants'][$session->lessonID] = 0;
				if ($data['block']->project == 1) {
					if ($data['block']->register_type === 'numbers') {
						// numbers
						$where = [
							'accountID' => $this->accountID,
							'blockID' => $blockID,
							'lessonID' => $session->lessonID,
							'date >=' => $date_from,
							'date <=' => $date_to
						];
						$res = $this->db->select("lessonID, SUM(attended) AS count")
							->from('bookings_attendance_numbers')
							->where($where)
							->group_by('lessonID')
							->get();
						if ($res->num_rows() > 0) {
							foreach ($res->result() as $row) {
								$data['participants'][$session->lessonID] = $row->count;
							}
						}
					} else if (in_array($data['block']->register_type, ['names', 'bikeability', 'shapeup'])) {
						// names (attended only)
						$where = [
							'accountID' => $this->accountID,
							'blockID' => $blockID,
							'lessonID' => $session->lessonID,
							'date >=' => $date_from,
							'date <=' => $date_to
						];
						$data['participants'][$session->lessonID] = $this->db->select("attendanceID")
							->from('bookings_attendance_names_sessions')
							->where($where)
							->group_by('participantID')
							->get()->num_rows();
					} else {
						// individuals or children (attended only)
						$where = [
							'bookings_cart_sessions.accountID' => $this->accountID,
							'bookings_cart_sessions.blockID' => $blockID,
							'bookings_cart_sessions.lessonID' => $session->lessonID,
							'bookings_cart_sessions.attended' => 1,
							'bookings_cart.type' => 'booking',
							'bookings_cart_sessions.date >=' => $date_from,
							'bookings_cart_sessions.date <=' => $date_to
						];
						$data['participants'][$session->lessonID] = $this->db->select("sessionID")
							->from('bookings_cart_sessions')
							->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
							->where($where)
							->group_by('bookings_cart_sessions.contactID, bookings_cart_sessions.childID')
							->get()->num_rows();
					}
				}
			}
		}

		// activities
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$data['activities'] = $this->db->from('activities')->where($where)->order_by('name asc')->get();

		// types
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$data['types'] = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();

		// staff
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$data['staff'] = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// load view
 		$this->crm_view('bookings/timetable_global_sessions', $data);
	}

	/**
	 * calendar feed
	 * @param  string $key
	 * @return mixed
	 */
	public function ics_feed($key = NULL) {
		if (empty($key)) {
			show_404();
			exit();
		}

		// look up key
		$where = array(
			'accounts.active' => 1,
			'staff.active' => 1,
			'staff.feed_enabled' => 1,
			'staff.feed_key' => $key
		);

		$res = $this->db->select('staff.*, accounts.company')->from('staff')->join('accounts', 'staff.accountID = accounts.accountID', 'inner')->where($where)->get();

		// not found or inactive
		if ($res->num_rows() == 0) {
			show_404();
			exit();
		}

		foreach ($res->result() as $staff_info) {}

		// get lessons
		$this->staffID = $staff_info->staffID;
		$this->accountID = $staff_info->accountID;
		$lessons = array();
		$week = date('W')-1;
		$year = date('Y');

		// calc max weeks in current year
		$max_weeks = gmdate("W", strtotime("28 December " . $year));

		// show 1 past week, and 12 future weeks
		for ($i=0; $i < 13; $i++) {
			if ($week < 0) {
				$week = gmdate("W", strtotime("28 December " . ($year - 1)));
				$year--;
			} else if ($week > $max_weeks) {
				$week = 1;
				$year++;
			}
			$lessons[$week] = $this->index($year, $week, 'true', TRUE);
			$week++;
		}

		// get default timezone
		$dtz = new \DateTimeZone(date_default_timezone_get());

		// create calendar
		$vCalendar = new \Eluceo\iCal\Component\Calendar($key);
		$vCalendar->setName($staff_info->first . ' ' . $staff_info->surname . "'s Timetable");
		$vCalendar->setDescription($staff_info->company)->setPublishedTTL('PT15M');

		// create timezone rule object for Daylight Saving Time
		$vTimezoneRuleDst = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
		$vTimezoneRuleDst->setTzName(date_default_timezone_get());
		$vTimezoneRuleDst->setDtStart(new \DateTime('1981-03-29 02:00:00', $dtz));
		$vTimezoneRuleDst->setTzOffsetFrom('+0000');
		$vTimezoneRuleDst->setTzOffsetTo('+0100');
		$dstRecurrenceRule = new \Eluceo\iCal\Property\Event\RecurrenceRule();
		$dstRecurrenceRule->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY);
		$dstRecurrenceRule->setByMonth(3);
		$dstRecurrenceRule->setByDay('-1SU');
		$vTimezoneRuleDst->setRecurrenceRule($dstRecurrenceRule);
		// create timezone rule object for Standard Time
		$vTimezoneRuleStd = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_STANDARD);
		$vTimezoneRuleStd->setTzName(date_default_timezone_get());
		$vTimezoneRuleStd->setDtStart(new \DateTime('1996-10-27 03:00:00', $dtz));
		$vTimezoneRuleStd->setTzOffsetFrom('+0100');
		$vTimezoneRuleStd->setTzOffsetTo('+0000');
		$stdRecurrenceRule = new \Eluceo\iCal\Property\Event\RecurrenceRule();
		$stdRecurrenceRule->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY);
		$stdRecurrenceRule->setByMonth(10);
		$stdRecurrenceRule->setByDay('-1SU');
		$vTimezoneRuleStd->setRecurrenceRule($stdRecurrenceRule);
		// create timezone definition and add rules
		$vTimezone = new \Eluceo\iCal\Component\Timezone(date_default_timezone_get());
		$vTimezone->addComponent($vTimezoneRuleDst);
		$vTimezone->addComponent($vTimezoneRuleStd);
		$vCalendar->setTimezone($vTimezone);

		if (count($lessons) > 0) {
			foreach ($lessons as $week) {
				foreach ($week as $slots) {
					foreach($slots as $slot) {
						foreach($slot as $lesson) {
							$title = $lesson['event'];
							if ($lesson['booking_type'] == 'booking' && $lesson['project'] != 1) {
								$title = $lesson['org'];
							}
							if ($lesson['has_block_org'] == TRUE) {
								$title .= ' (' . $lesson['org'] . ')';
							}
							$desc_bits = array();
							if ($lesson['booking_type'] == 'booking') {
								$desc_bits[] = ucwords($lesson['org_type']) . ': ' . $lesson['org'];
							} else {
								$desc_bits[] = 'Venue: ' . $lesson['org'];
							}
							if (!empty($lesson['activity_group'])) {
								$desc_bits[] = 'Activity/Group: ' . $lesson['activity_group'];
							}
							$desc_bits[] = 'Full Details: ' . $lesson['link'];
							$desc = implode("\n", $desc_bits);

							$vEvent = new \Eluceo\iCal\Component\Event();
							$vEvent->setDtStart(new \DateTime($lesson['date'] . ' ' . $lesson['time_start'], $dtz))->setDtEnd(new \DateTime($lesson['date'] . ' ' . $lesson['time_end'], $dtz))->setUseTimezone(TRUE)->setSummary($title)->setUrl($lesson['link'])->setDescription($desc)->setLocation($lesson['address'])->setUniqueId(md5($lesson['date'] . '-' . $lesson['id']));
							$vCalendar->addComponent($vEvent);
						}
					}
				}
			}
		}

		header('Content-Type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename="webcal.ics"');
		echo $vCalendar->render();
		exit();
	}

	/**
	 * confirm timetable
	 * @param  int $year
	 * @param int $week
	 * @return mixed
	 */
	public function confirm($year = NULL, $week = NULL, $only_own = FALSE) {

		if (!$this->auth->has_features(array('bookings_timetable_own', 'bookings_timetable_confirmation'))) {
			return FALSE;
		}

		// check params
		if (empty($year) || empty($week)) {
			show_404();
		}

		// check if can confirm
		$can_confirm = FALSE;
		$timetable_confirm_weeks = intval($this->settings_library->get('timetable_confirm_weeks'));
		if ($timetable_confirm_weeks < 1) {
			$timetable_confirm_weeks = 1;
		}

		// if is current week
		if ($week == date("W") && $year == date("Y")) {
			// if before switch day, can confirm
			if (date("N") < $this->switch_day) {
				$can_confirm = TRUE;
			}
		} else if (strtotime(get_date_from_week($week, $year)) <= strtotime(get_date_from_week((date("W")+$timetable_confirm_weeks), date('Y')))) {
			// check if can confirm for future weeks
			$can_confirm = TRUE;
		}

		if ($can_confirm !== TRUE) {
			show_404();
		}

		$where = array(
			'week' => $week,
			'year' => $year,
			'staffID' => $this->auth->user->staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('timetable_read')->where($where)->limit(1)->get();

		// already done
		if ($query->num_rows() > 0) {
			show_404();
		}

		// confirm
		$data = $where;
		$data['byID'] = $data['staffID'];
		$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
		$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

		$this->db->insert('timetable_read', $data);

		// redirect
		if ($only_own === FALSE) {
			redirect('bookings/timetable/' . $year . '/' . $week . '#confirmation');
		} else {
			redirect('timetable/' . $year . '/' . $week . '#confirmation');
		}

	}
}

/* End of file timetable.php */
/* Location: ./application/controllers/bookings/timetable.php */
