<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends Online_Booking_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function index($type = NULL, $year = NULL, $month = NULL) {

		// if no view specified, redirect to default view from account
		if($type == NULL){
			redirect($this->online_booking->account->default_view);
		}

		// set defaults
		$title = 'Event Search';
		$body_class = 'events-list';
		$page_base = '';
		$prev_month = NULL;
		$prev_year = NULL;
		$next_month = NULL;
		$next_year = NULL;
		$month_year = NULL;
		$blocks = array();
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$search_submit_url = current_url();

		// load libraries
		$this->load->library('user_agent');

		// set pagination vars
		$this->pagination_library->amount = 10;
		$this->pagination_library->fa_weight = 'fas';

		// set where
		$where = array(
			'bookings_blocks.endDate >=' => date('Y-m-d')
		);
		$where_custom = '';

		// if calendar
		switch ($type) {
			case 'list':
				$page_base = 'list';
				break;
			case 'calendar':
				// if empty month/year, use current
				if (empty($month)) {
					$month = date('m');
				}
				if (empty($year)) {
					$year = date('Y');
				}

				// check date is valid and after 1st of this month
				if (!checkdate($month, 1, $year) || mktime(0, 0, 0, $month, 1, $year) < mktime(0, 0, 0, date('n'), 1, date('Y'))) {
					show_404();
				}

				// work out next/prev month/year
				$prev_month = $month - 1;
				$prev_year = $year;
				if ($prev_month == 0) {
					$prev_month = 12;
					$prev_year--;
				}
				$next_month = $month + 1;
				$next_year = $year;
				if ($next_month == 13) {
					$next_month = 1;
					$next_year++;
				}

				// if prev in past, set null
				if (mktime(0, 0, 0, $prev_month, 1, $prev_year) < mktime(0, 0, 0, date('n'), 1, date('Y'))) {
					$prev_month = NULL;
					$prev_year = NULL;
				}

				// set label
				$month_year = date("F Y", strtotime($year . "-" . $month . "-1"));
				$title = $month_year;

				// set where
				$where_custom .= ' AND ((MONTH(`' . $this->db->dbprefix("bookings_blocks") . '`.`startDate`) = ' . $this->db->escape($month) . ' AND YEAR(`' . $this->db->dbprefix("bookings_blocks") . '`.`startDate`) = ' . $this->db->escape($year) . ') OR (MONTH(`' . $this->db->dbprefix("bookings_blocks") . '`.`endDate`) = ' . $this->db->escape($month) . ' AND YEAR(`' . $this->db->dbprefix("bookings_blocks") . '`.`endDate`) = ' . $this->db->escape($year) . '))';

				// disable paging
				$this->pagination_library->is_search();
				break;
			case 'map':
				// disable paging
				$this->pagination_library->is_search();
				break;
		}

		// set up search
		$search_fields = array(
			'location' => NULL,
			'location_coordinates' => NULL,
			'age' => NULL,
			'activityID' => NULL,
			'typeID' => NULL,
			'brandID' => NULL,
			'name' => NULL,
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('location', 'Location', 'trim|xss_clean');
			$this->form_validation->set_rules('age', 'Participants Age', 'trim|xss_clean');
			$this->form_validation->set_rules('activityID', 'Activity', 'trim|xss_clean');
			$this->form_validation->set_rules('typeID', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('brandID', $this->settings_library->get_label('brand', $this->online_booking->accountID), 'trim|xss_clean');
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['location'] = set_value('location');
			$search_fields['age'] = set_value('age');
			$search_fields['activityID'] = set_value('activityID');
			$search_fields['typeID'] = set_value('typeID');
			$search_fields['brandID'] = set_value('brandID');
			$search_fields['name'] = set_value('name');

			$is_search = TRUE;

		}

		if (!$this->input->post()) {
			// if coming on page from an internal site, remember search
			if (!$this->agent->is_referral() && is_array($this->session->userdata('event-search'))) {
				foreach ($this->session->userdata('event-search') as $key => $value) {
					$search_fields[$key] = $value;
				}
				// don't remember location as could be sensitive?
				$search_fields['location'] = NULL;

				$is_search = TRUE;
			}

			// if user logged in, take location from profile if on map page
			if ($this->online_booking->user !== FALSE && $this->uri->segment(1) == 'map') {
				$search_fields['location'] = $this->online_booking->user->postcode;
				$is_search = TRUE;
			}
		}

		if (isset($is_search) && $is_search === TRUE) {
			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('event-search', $search_fields);

			if ($search_fields['activityID'] != '') {
				$where['bookings_lessons.activityID'] = $search_fields['activityID'];
			}

			if ($search_fields['typeID'] != '') {
				$where['bookings_lessons.typeID'] = $search_fields['typeID'];
			}

			if ($search_fields['brandID'] != '') {
				$where['bookings.brandID'] = $search_fields['brandID'];
			}

			if ($search_fields['name'] != '') {
				$where[$this->db->dbprefix("bookings") . ".name LIKE"] = "%" . $this->db->escape_like_str($search_fields['name']) . "%";
			}

			// if location entered, find coordinates
			if (isset($search_fields['location']) && $search_fields['location'] != '') {
				$search_fields['location_coordinates'] = geocode($search_fields['location']);

				// show map if location search
				$type = 'map';
			}
		}

		// if filtering by dept/brand
		if ($type == 'dept' && is_numeric($year)) {
			$search_fields['brandID'] = $year; // actually type ID
			$where['bookings.brandID'] = $search_fields['brandID']; // actually dept ID
			$body_class .= ' dept dept-' . $search_fields['brandID'];

			// disable paging
			$this->pagination_library->is_search();

			// set search submit url
			if ($this->settings_library->get('onlinebooking_search_brand', $this->online_booking->accountID) == 1) {
				$search_submit_url = site_url();
			}
		}

		// if filtering by session type
		if ($type == 'type' && is_numeric($year)) {
			$search_fields['typeID'] = $year; // actually type ID
			$where['bookings_lessons.typeID'] = $search_fields['typeID'];
			$body_class .= ' type type-' . $search_fields['typeID'];

			// disable paging
			$this->pagination_library->is_search();

			// set search submit url
			if ($this->settings_library->get('onlinebooking_search_type', $this->online_booking->accountID) == 1) {
				$search_submit_url = site_url();
			}
		}

		// if filtering by activity
		if ($type == 'activity' && is_numeric($year)) {
			$search_fields['activityID'] = $year; // actually activity ID
			$where['bookings_lessons.activityID'] = $search_fields['activityID'];
			$body_class .= ' activity activity-' . $search_fields['activityID'];

			// disable paging
			$this->pagination_library->is_search();

			// set search submit url
			if ($this->settings_library->get('onlinebooking_search_activity', $this->online_booking->accountID) == 1) {
				$search_submit_url = site_url();
			}
		}

		// get blocks
		$search_fields['future_only'] = true; // future sessions only
		$blocks = $this->cart_library->get_blocks($where, $search_fields, $where_custom);

		// workout pagination
		$total_items = count($blocks);
		$pagination = $this->pagination_library->calc($total_items);

		// slice blocks array by paging
		$blocks = array_slice($blocks, $this->pagination_library->start, $this->pagination_library->amount, TRUE);

		// get session types
		$where = array(
			'accountID' => $this->online_booking->accountID,
			'active' => 1,
			'exclude_online_booking_search !=' => 1
		);
		$lesson_types = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();

		// get activities
		$where = array(
			'accountID' => $this->online_booking->accountID,
			'active' => 1,
			'exclude_online_booking_search !=' => 1
		);
		$activities = $this->db->from('activities')->where($where)->order_by('name asc')->get();

		// get brands
		$where = array(
			'accountID' => $this->online_booking->accountID,
			'active' => 1,
			'hide_online !=' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// check for flashdata
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// output
		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'page_base' => $page_base,
			'lesson_types' => $lesson_types,
			'search_fields' => $search_fields,
			'activities' => $activities,
			'brands' => $brands,
			'view_type' => $type,
			'month_year' => $month_year,
			'prev_month' => $prev_month,
			'prev_year' => $prev_year,
			'next_month' => $next_month,
			'next_year' => $next_year,
			'blocks' => $blocks,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'search_submit_url' => $search_submit_url
		);
		$this->booking_view('online-booking/events', $data);
	}

	public function event($blockID) {
		// set defaults
		$title = 'Event';
		$body_class = 'book';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// look up block
		$where = array(
			'bookings_blocks.blockID' => $blockID
		);
		$blocks = $this->cart_library->get_blocks($where);

		// if doesn't exist, 404
		if (count($blocks) == 0) {
			show_404();
		}

		// get first result
		foreach ($blocks as $block_info) {
			break;
		}

		// set title
		$title = $block_info->booking . ' - ' . $block_info->block;

		// check for flashdata
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// output
		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'blockID' => $blockID,
			'block' => $block_info,
			'success' => $success,
			'error' => $error,
			'info' => $info,
		);
		$this->booking_view('online-booking/event', $data);
	}
}

/* End of file Main.php */
/* Location: ./application/controllers/online-booking/Main.php */
