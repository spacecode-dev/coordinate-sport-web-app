<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Blocks extends MY_Controller {

	private $booking_info;

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * show list of blocks
	 * @return void
	 */
	public function index($bookingID = NULL) {

		if ($bookingID == NULL) {
			show_404();
		}

		// if so, check user exists
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
		$icon = 'calendar-alt';
		$tab = 'blocks';
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
		$page_base = 'bookings/blocks/' . $bookingID;
		$section = 'bookings';
		$title = 'Blocks';
		$buttons = '<a class="btn btn-success" href="' . site_url('bookings/blocks/' . $bookingID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'bookings_blocks.bookingID' => $bookingID,
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'start_from' => NULL,
			'start_to' => NULL,
			'end_from' => NULL,
			'end_to' => NULL,
			'name' => NULL,
			'search' => NULL,
			'start_date_order' => 'desc'
		);

		$form_submitted = false;
		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_start_from', 'Start From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_start_to', 'Start To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_end_from', 'End From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_end_to', 'End To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('start_date_order', 'Order Start Date', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['start_from'] = set_value('search_start_from');
			$search_fields['start_to'] = set_value('search_start_to');
			$search_fields['end_from'] = set_value('search_end_from');
			$search_fields['end_to'] = set_value('search_end_to');
			$search_fields['name'] = set_value('search_name');
			$search_fields['search'] = set_value('search');
			$search_fields['start_date_order'] = set_value('start_date_order');

			$is_search = TRUE;
			$form_submitted = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings-blocks'))) {

			foreach ($this->session->userdata('search-bookings-blocks') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if ($this->input->get('order')) {
			$order = $this->crm_library->set_order('bookings_blocks', ['startDate'], ['asc', 'desc'],
				$this->input->get('order'), 'bookings_blocks.startDate desc');
		} else {
			$order = $this->crm_library->set_order('bookings_blocks', ['startDate'], ['asc', 'desc'],
				['startDate' => $search_fields['start_date_order']], 'bookings_blocks.startDate desc');
		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-bookings-blocks', $search_fields);

			if ($search_fields['start_from'] != '') {
				$start_from = uk_to_mysql_date($search_fields['start_from']);
				if ($start_from !== FALSE) {
					$search_where[] = $this->db->dbprefix('bookings_blocks').".`startDate` >= " . $this->db->escape($start_from);
				}
			}

			if ($search_fields['start_to'] != '') {
				$start_to = uk_to_mysql_date($search_fields['start_to']);
				if ($start_to !== FALSE) {
					$search_where[] = $this->db->dbprefix('bookings_blocks').".`startDate` <= " . $this->db->escape($start_to);
				}
			}

			if ($search_fields['end_from'] != '') {
				$end_from = uk_to_mysql_date($search_fields['end_from']);
				if ($end_from !== FALSE) {
					$search_where[] = $this->db->dbprefix('bookings_blocks').".`endDate` >= " . $this->db->escape($end_from);
				}
			}

			if ($search_fields['end_to'] != '') {
				$end_to = uk_to_mysql_date($search_fields['end_to']);
				if ($end_to !== FALSE) {
					$search_where[] = $this->db->dbprefix('bookings_blocks').".`endDate` <= " . $this->db->escape($end_to);
				}
			}

			if ($search_fields['name'] != '') {
				$search_where[] = $this->db->dbprefix('bookings_blocks').".`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('bookings_blocks.*, orgs.name as org')
			->from('bookings_blocks')
			->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')
			->where($where)
			->where($search_where, NULL, FALSE)
			->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate asc, bookings_blocks.name asc')
			->get();

		// workout pagination
		$total_items = $res->num_rows();


		if ($booking_info->register_type == 'numbers') {
			foreach ($res->result() as $item) {
				$attendance = $this->db->select()->from('bookings_attendance_numbers')->where([
					'blockID' => $item->blockID
				])->get();

				// adding zero attendance to the projects with non existed attendance
				if ($attendance->num_rows() < 1) {
					$lessons = $this->db->select()->from('bookings_lessons')->where([
						'blockID' => $item->blockID
					])->get();

					if ($lessons->num_rows() > 0) {

						foreach ($lessons->result() as $lesson) {
							$date = $item->startDate;

							while (strtotime($date) <= strtotime($item->endDate)) {
								$day = strtolower(date('l', strtotime($date)));
								if ($day == $lesson->day) {
									$data = [
										'bookingID' => $item->bookingID,
										'blockID' => $item->blockID,
										'lessonID' => $lesson->lessonID,
										'date' => $date,
										'attended' => 0,
										'byID' => $this->auth->user->staffID,
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'accountID' => $this->auth->user->accountID
									];
									$this->db->insert('bookings_attendance_numbers', $data);
									$this->db->update('bookings_blocks',
										['targets_missed' => 'Session Participants'],
										['blockID' => $item->blockID]);
								}
								$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
							}
						}
					}
				}
			}
		}


		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('bookings_blocks.*, orgs.name as org')->from('bookings_blocks')->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by($order)->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'blocks' => $res,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'form_submitted' => $form_submitted
		);

		// load view
		$this->crm_view('bookings/blocks', $data);
	}

	/**
	 * edit a block
	 * @param  int $blockID
	 * @param int $bookingID
	 * @return void
	 */
	public function edit($blockID = NULL, $bookingID = NULL)
	{

		$block_info = new stdClass();

		// check if editing
		if ($blockID != NULL) {

			// check if numeric
			if (!ctype_digit($blockID)) {
				show_404();
			}

			// if so, check user exists
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

			// match
			foreach ($query->result() as $row) {
				$block_info = $row;
				$bookingID = $block_info->bookingID;
			}

		}

		// required
		if ($bookingID == NULL) {
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
			// save in class to access in validation functions
			$this->booking_info = $booking_info;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Block';
		if ($blockID != NULL) {
			$submit_to = 'bookings/blocks/edit/' . $blockID;
			$title = $block_info->name;
		} else {
			$submit_to = 'bookings/blocks/' . $bookingID . '/new/';
		}
		$return_to = 'bookings/blocks/' . $bookingID;
		$icon = 'book';
		$tab = 'blocks';
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
		$breadcrumb_levels['bookings/blocks/' . $bookingID] = 'Blocks';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;



		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('startDate', 'Start Date', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('endDate', 'End Date', 'trim|xss_clean|required|callback_check_date|callback_after_start[' . $this->input->post('startDate') . ']|callback_check_block_length[' . $this->input->post('startDate') . ']');

			if ($booking_info->type == 'event' || $booking_info->project == 1) {
				$this->form_validation->set_rules('misc_income', 'Misc. Income', 'trim|xss_clean|greater_than[-1]');
			}
			$this->form_validation->set_rules('staffing_notes', 'Staffing Notes', 'trim|xss_clean');
			if ($booking_info->type == 'event' || $booking_info->project == 1) {
				$this->form_validation->set_rules('thanksemail', 'Send Thanks Email', 'trim|xss_clean');
				$this->form_validation->set_rules('thanksemail_text', 'Thanks Email', 'trim|callback_required_if_checked[' . $this->input->post('thanksemail') . ']');
			}
			$this->form_validation->set_rules('provisional', 'Provisional', 'trim|xss_clean');
			$this->form_validation->set_rules('terms_accepted', 'Terms & Conditions', 'trim|xss_clean');
			if ($booking_info->type == 'event' || $booking_info->project == 1) {
				$this->form_validation->set_rules('min_age', 'Minimum Age', 'trim|xss_clean|numeric');
				$this->form_validation->set_rules('max_age', 'Maximum Age', 'trim|xss_clean|numeric');
			}
			if ($booking_info->type == 'booking') {
				$this->form_validation->set_rules('org_bookable', $this->settings_library->get_label('customer') . ' Bookable', 'trim|xss_clean');
				$this->form_validation->set_rules('website_description', 'Web Site Description', 'trim|xss_clean');
			}
			if ($booking_info->type == 'event' || $booking_info->project == 1) {
				$this->form_validation->set_rules('public', 'Show on Bookings Site', 'trim|xss_clean');
				$this->form_validation->set_rules('require_all_sessions', 'Require All Sessions to be Booked', 'trim|xss_clean');
				$this->form_validation->set_rules('block_price', 'Block Price', 'trim|xss_clean|is_numeric');
				$this->form_validation->set_rules('target_profit', 'Profit', 'trim|xss_clean|greater_than[-1]');
				$this->form_validation->set_rules('target_costs', 'Costs', 'trim|xss_clean|greater_than[-1]');
				$this->form_validation->set_rules('target_weekly', 'Weekly ' . $this->settings_library->get_label('participants'), 'trim|xss_clean|greater_than[-1]');
				$this->form_validation->set_rules('target_total', $this->settings_library->get_label('participant') . ' Sessions', 'trim|xss_clean|greater_than[-1]');
				$this->form_validation->set_rules('target_unique', 'Unique ' . $this->settings_library->get_label('participants'), 'trim|xss_clean|greater_than[-1]');
				if ($this->input->post('target_retention') > 0 || $this->input->post('target_retention_weeks') > 0) {
					$this->form_validation->set_rules('target_retention', 'Retained ' . $this->settings_library->get_label('participants'), 'trim|xss_clean|required|greater_than[-1]');
					$this->form_validation->set_rules('target_retention_weeks', 'Retained Weeks', 'trim|xss_clean|required|greater_than[-1]');
				} else {
					$this->form_validation->set_rules('target_retention', 'Retained ' . $this->settings_library->get_label('participants'), 'trim|xss_clean|greater_than[-1]');
					$this->form_validation->set_rules('target_retention_weeks', 'Retained Weeks', 'trim|xss_clean|greater_than[-1]');
				}
			}

			if ($booking_info->type == 'booking') {
				$this->form_validation->set_rules('orgID', 'Customer or Venue', 'trim|xss_clean|required');
				$this->form_validation->set_rules('addressID', 'Delivery Address', 'trim|xss_clean|required');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'orgID' => NULL,
					'addressID' => NULL,
					'name' => set_value('name'),
					'startDate' => uk_to_mysql_date(set_value('startDate')),
					'endDate' => uk_to_mysql_date(set_value('endDate')),
					'staffing_notes' => set_value('staffing_notes'),
					'provisional' => 0,
					'terms_accepted' => 0,
					'org_bookable' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($booking_info->type == 'booking') {
					// if org different to booking, store
					if (set_value('orgID') != '' && set_value('orgID') != $booking_info->orgID) {
						$data['orgID'] = set_value('orgID');
					}

					// switch prospect to customer if changed and setting allows
					if ($this->settings_library->get('disable_prospects_automation') != 1 && ($blockID == NULL || ($blockID !== NULL && $block_info->orgID != set_value('orgID')))) {
						$data_prospect = array(
							'prospect' => 0
						);
						$where_prospect = array(
							'orgID' => set_value('orgID'),
							'prospect' => 1,
							'accountID' => $this->auth->user->accountID
						);
						$res_prospect = $this->db->update('orgs', $data_prospect, $where_prospect, 1);
					}

					if (set_value('addressID') != '') {
						$data['addressID'] = set_value('addressID');
					}

					if (set_value('org_bookable') == 1) {
						$data['org_bookable'] = 1;
						if (set_value('website_description') != '') {
							$data['website_description'] = set_value('website_description');
						}
					}
				}

				if (set_value('provisional') == 1) {
					$data['provisional'] = 1;
				}

				if (set_value('terms_accepted') == 1) {
					$data['terms_accepted'] = 1;
				}

				if ($booking_info->type == 'event' || $booking_info->project == 1) {
					if ($booking_info->public == 1) {
						$data['public'] = 0;
						if (set_value('public') == 1) {
							$data['public'] = 1;
						}
					}

					$data['require_all_sessions'] = 0;
					if (set_value('require_all_sessions') == 1) {
						$data['require_all_sessions'] = 1;
					}
					$data['block_price'] = null_if_empty(set_value('block_price'));

					$data['thanksemail'] = 0;
					$data['thanksemail_text'] = $this->input->post('thanksemail_text', FALSE);
					if (set_value('thanksemail') == 1) {
						$data['thanksemail'] = 1;
					}

					$data['min_age'] = NULL;
					if (set_value('min_age') !== "") {
						$data['min_age'] = set_value('min_age');
					}
					$data['max_age'] = NULL;
					if (set_value('max_age') !== "") {
						$data['max_age'] = set_value('max_age');
					}

					$data['target_profit'] = floatval(set_value('target_profit'));
					$data['misc_income'] = floatval(set_value('misc_income'));
					$data['target_costs'] = floatval(set_value('target_costs'));
					$data['target_weekly'] = intval(set_value('target_weekly'));
					$data['target_total'] = intval(set_value('target_total'));
					$data['target_unique'] = intval(set_value('target_unique'));
					$data['target_retention'] = intval(set_value('target_retention'));
					$data['target_retention_weeks'] = intval(set_value('target_retention_weeks'));
				}

				if ($blockID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['bookingID'] = $bookingID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					$flag = set_value("flag");

					$redirect_to = $return_to;

					if ($blockID == NULL) {
						// insert
						$query = $this->db->insert('bookings_blocks', $data);

						if ($this->db->affected_rows() == 1) {
							$just_added = TRUE;
							$something_updated = TRUE;
							$blockID = $this->db->insert_id();
							$redirect_to = site_url('bookings/sessions/' . $bookingID . '/' . $blockID);
						}

					} else {
						$where = array(
							'blockID' => $blockID
						);

						// Get Old Startdate and enddate of current block
						$old_block_start_date = $old_block_end_date = "";
						$new_block_start_date = $data["startDate"];
						$new_block_end_date = $data["endDate"];
						$old_blocks = $this->db->from("bookings_blocks")->get();
						if($old_blocks->num_rows() > 0){
							foreach($old_blocks->result() as $old_block){
								$old_block_start_date = $old_block->startDate;
								$old_block_end_date = $old_block->endDate;
							}
						}

						$where = array(
							'blockID' => $blockID,
							'accountID' => $this->auth->user->accountID
						);
						// Find unique lessonID from booking_cart_session
						$lessonArray = array();
						$res = $this->db->from("bookings_cart_sessions")->where($where)->group_by("lessonID, childID, contactID")->get();
						if($res->num_rows() > 0){
							foreach($res->result() as $result){
								if($result->childID != null)
									$lessonArray[$result->childID] = $result;
								else
									$lessonArray[$result->contactID] = $result;
							}
						}


						if($data['require_all_sessions'] == 1 && count($lessonArray) > 0){
							if(strtotime($old_block_start_date) != strtotime($new_block_start_date)){
								foreach($lessonArray as $ids => $lesson_info){
									$day = date("l", strtotime($lesson_info->date));
									if(strtotime($old_block_start_date) > strtotime($new_block_start_date)){
										$endDate = strtotime($old_block_start_date);
										for($i = strtotime($day, strtotime($new_block_start_date)); $i <= $endDate; $i = strtotime('+1 week', $i)){
											$new_lesson_date = date('Y-m-d', $i);
											$where = array("accountID" => $this->auth->user->accountID,
											"lessonID" => $lesson_info->lessonID,
											"blockID" => $lesson_info->blockID,
											"date" => $new_lesson_date);
											if($lesson_info->childID != null){
												$where["childID"] = $ids;
											}else{
												$where["contactID"] = $ids;
											}
											$query = $this->db->from("bookings_cart_sessions")->where($where)->get();
											if($query->num_rows() == 0){
												// Add Data in Table
												$data_info = array("accountID" => $this->auth->user->accountID,
												"cartID" => $lesson_info->cartID,
												"bookingID" => $lesson_info->bookingID,
												"blockID" => $lesson_info->blockID,
												"lessonID" => $lesson_info->lessonID,
												"contactID" => $lesson_info->contactID,
												"childID" => $lesson_info->childID,
												"date" => $new_lesson_date,
												"added" => mdate('%Y-%m-%d %H:%i:%s'),
												"modified" => mdate('%Y-%m-%d %H:%i:%s')
												);
												$this->db->insert("bookings_cart_sessions", $data_info);
											}
										}
									}else if(strtotime($old_block_start_date) < strtotime($new_block_start_date)){
										$endDate = strtotime($new_block_start_date);
										for($i = strtotime($day, strtotime($old_block_start_date)); $i <= $endDate; $i = strtotime('+1 week', $i)){
											$new_lesson_date = date('Y-m-d', $i);
											$where = array("accountID" => $this->auth->user->accountID,
											"lessonID" => $lesson_info->lessonID,
											"blockID" => $lesson_info->blockID,
											"date" => $new_lesson_date);
											if($lesson_info->childID != null){
												$where["childID"] = $ids;
											}else{
												$where["contactID"] = $ids;
											}
											$query = $this->db->from("bookings_cart_sessions")->where($where)->get();
											if($query->num_rows() > 0){
												// Remove data
												$this->db->delete("bookings_cart_sessions", $where);
											}
										}
									}
								}
							}

							if(strtotime($old_block_end_date) != strtotime($new_block_end_date)){
								foreach($lessonArray as $ids => $lesson_info){
									$day = date("l", strtotime($lesson_info->date));
									if(strtotime($old_block_end_date) < strtotime($new_block_end_date)){
										// Add Record
										$endDate = strtotime($new_block_end_date);
										for($i = strtotime($day, strtotime($old_block_end_date)); $i <= $endDate; $i = strtotime('+1 week', $i)){
											$new_lesson_date = date('Y-m-d', $i);
											$where = array("accountID" => $this->auth->user->accountID,
											"lessonID" => $lesson_info->lessonID,
											"blockID" => $lesson_info->blockID,
											"date" => $new_lesson_date);
											if($lesson_info->childID != null){
												$where["childID"] = $ids;
											}else{
												$where["contactID"] = $ids;
											}
											$query = $this->db->from("bookings_cart_sessions")->where($where)->get();
											if($query->num_rows() == 0){
												// Add Data in Table
												$data_info = array("accountID" => $this->auth->user->accountID,
												"cartID" => $lesson_info->cartID,
												"bookingID" => $lesson_info->bookingID,
												"blockID" => $lesson_info->blockID,
												"lessonID" => $lesson_info->lessonID,
												"contactID" => $lesson_info->contactID,
												"childID" => $lesson_info->childID,
												"date" => $new_lesson_date,
												"added" => mdate('%Y-%m-%d %H:%i:%s'),
												"modified" => mdate('%Y-%m-%d %H:%i:%s')
												);
												$this->db->insert("bookings_cart_sessions", $data_info);
											}
										}
									}else if(strtotime($old_block_end_date) > strtotime($new_block_end_date)){
										// Remove Record
										$endDate = strtotime($old_block_end_date);
										for($i = strtotime($day, strtotime($new_block_end_date)); $i <= $endDate; $i = strtotime('+1 week', $i)){
											$new_lesson_date = date('Y-m-d', $i);
											$where = array("accountID" => $this->auth->user->accountID,
											"lessonID" => $lesson_info->lessonID,
											"blockID" => $lesson_info->blockID,
											"date" => $new_lesson_date);
											if($lesson_info->childID != null){
												$where["childID"] = $ids;
											}else{
												$where["contactID"] = $ids;
											}
											$query = $this->db->from("bookings_cart_sessions")->where($where)->get();
											if($query->num_rows() > 0){
												// Remove data
												$this->db->delete("bookings_cart_sessions", $where);
											}
										}
									}
								}
							}
						}


						$where = array(
							'blockID' => $blockID
						);

						// update
						$query = $this->db->update('bookings_blocks', $data, $where);

						if($flag == 1){
							if ($this->db->affected_rows() == 1) {
								$something_updated = TRUE;

								// if org id changed (and booking type = booking), set sessions to address of new default
								if ($booking_info->type == 'booking') {
									$lesson_data = array(
										'addressID' => $data['addressID'],
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);
									$where = array(
										'blockID' => $blockID,
										'accountID' => $this->auth->user->accountID
									);
									$res = $this->db->update('bookings_lessons', $lesson_data, $where);
								}
							}
						}
						elseif ($flag==2) {
							//Address shouldn't be updated for all sessions. But dont display an error if nothing else was updated.
							$something_updated = TRUE;
						}
					}

					// if inserted/updated
					if (isset($something_updated)) {

						if (isset($just_added)) {
							$this->session->set_flashdata('success', set_value('name') . ' has been created successfully, continue to add sessions below.');
						} else {
							$this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
						}

						// calc targets
						$this->crm_library->calc_targets($blockID);

						redirect($redirect_to);

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

		// orgs
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$orgs = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// addresses
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc, town asc, county asc, postcode asc')->get();

		//Get number of sessions the current block. If this is a new block (e.g. no block ID) assume 0
		$numberOfSessions = 0;
		if (!is_null($blockID)) {
			$where = array(
				'blockID' => $blockID
			);
			$sessions = $this->db->from('bookings_lessons')->where($where)->get();
			$numberOfSessions = $sessions->num_rows();
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
			'block_info' => $block_info,
			'orgs' => $orgs,
			'addresses' => $addresses,
			'bookingID' => $bookingID,
			'blockID' => $blockID,
			'number_of_sessions' => $numberOfSessions,
			'booking_info' => $booking_info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'type' => $booking_info->type,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/block', $data);
	}

	/**
	 * delete a block
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function remove($blockID = NULL) {

		// check params
		if (empty($blockID)) {
			show_404();
		}

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

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings_blocks', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $block_info->name . ' has been removed successfully.');
			} else {
				// try to force delete if required
				if ($this->crm_library->last_segment() === 'force') {
					force_delete_db_dependants('bookings_blocks', $blockID);
					// redirect to normal delete to delete parent
					redirect(str_replace('/force', '', current_url()));
					exit();
				}
				// get dependant table conflicts
				if ($db_error = get_friendly_db_error('bookings_blocks', $blockID, $block_info)) {
					$this->session->set_flashdata('error', $db_error);
				}
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/blocks/' . $block_info->bookingID;

			redirect($redirect_to);
		}
	}

	/**
	 * duplicate a block
	 * @param  int $blockID
	 * @return mixed
	 */
	public function duplicate($blockID = NULL) {

		// check params
		if (empty($blockID)) {
			show_404();
		}

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

		// match
		foreach ($query->result_array() as $row) {
			$block_info = $row;

			// copy block info
			$data = $block_info;

			// update vars
			unset($data['blockID']);
			$data['name'] .= ' (Copy)';
			$data['byID'] = $this->auth->user->staffID;
			$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
			$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

			$res = $this->db->insert('bookings_blocks', $data);

			if ($this->db->affected_rows() == 1) {

				$new_block = $this->db->insert_id();

				// get lessons
				$lessons = array();

				$where = array(
					'blockID' => $blockID
				);

				$res = $this->db->from('bookings_lessons')->where($where)->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $lesson_info) {
						$lessons[$lesson_info->lessonID] = $lesson_info;
					}
				}

				// duplicate sessions to new block
				$this->crm_library->duplicate_lessons($lessons, $new_block);

				$this->session->set_flashdata('success', $block_info['name'] . ' has been duplicated successfully.');
			} else {
				$this->session->set_flashdata('error', $block_info['name'] . ' could not be duplicated.');
			}

			// calc targets
			$this->crm_library->calc_targets($new_block);

			// determine which page to send the user back to
			$redirect_to = 'bookings/blocks/' . $block_info['bookingID'];

			redirect($redirect_to);
		}
	}

	/**
	 * jump to exceptions for block
	 * @param  int $blockID
	 * @return mixed
	 */
	public function exceptions($blockID = NULL) {

		// check params
		if (empty($blockID)) {
			show_404();
		}

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

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;

			// build search
			$search_fields['blockID'] = $blockID;
			$search_fields['search'] = TRUE;

			// store search fields
			$this->session->set_userdata('search-bookings-exceptions', $search_fields);

			// go to
			$redirect_to = 'bookings/exceptions/' . $block_info->bookingID . '/recall';

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

	/**
	 * check a date is after start date
	 * @param  string $endDate
	 * @param  string $startDate
	 * @return boolean
	 */
	public function after_start($endDate, $startDate) {

		$startDate = strtotime(uk_to_mysql_date($startDate));
		$endDate = strtotime(uk_to_mysql_date($endDate));

		if ($endDate >= $startDate) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check an event block isn't more than 1 week
	 * @param  string $endDate
	 * @param  string $startDate
	 * @return boolean
	 */
	public function check_block_length($endDate, $startDate) {

		// booking can be any length
		if ($this->booking_info->type == 'booking') {
			return TRUE;
		}

		$startDate = strtotime(uk_to_mysql_date($startDate));
		$endDate = strtotime('-1 week', strtotime(uk_to_mysql_date($endDate)));

		if ($endDate < $startDate) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check if either this field filled in if value of another field is checked
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function required_if_checked($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// check
		if ($value2 == 1 && empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

}

/* End of file blocks.php */
/* Location: ./application/controllers/bookings/blocks.php */
