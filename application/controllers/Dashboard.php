<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends MY_Controller {

	private $switchDay;
	private $nextWeek;
	private $nextYear;
	private $weekNo;
	private $yearNo;

	public function __construct() {
		parent::__construct();

		// day to reset timetable confirmation
		$this->switchDay = 4;

		// what week number is next week?
		if (date("W") == 52) {
			$this->nextWeek = 1;
			$this->nextYear = date("Y") + 1;
		} else {
			$this->nextWeek = date("W") + 1;
			$this->nextYear = date("Y");
		}

		if (date("N") < $this->switchDay) {
			$this->weekNo = date("W");
			$this->yearNo = date("Y");
		} else {
			$this->weekNo = $this->nextWeek;
			$this->yearNo = $this->nextYear;
		}
	}

	public function index() {

		// load libraries
		$this->load->library('reports_library');

		// check if is birthday
		$is_birthday = FALSE;
		if (!empty($this->auth->user->dob) && date('j F', strtotime($this->auth->user->dob)) == date('j F')) {
			$is_birthday = TRUE;
		}

		// get unread messages
		$where = array(
			'forID' => $this->auth->user->staffID,
			'status' => 0,
			'accountID' => $this->auth->user->accountID
		);
		$unread_messages = $this->db->from('messages')->where($where)->get()->num_rows();

		// has confirmed timetable?
		$confirmed_timetable = FALSE;
		$confirmed_timetable_week = 'this';
		$confirmed_timetable_link = 'bookings/timetable';
		$week = date('W');
		$year = date('Y');

		if (date("N") > $this->switchDay) {

			$confirmed_timetable_week = 'next';
			$confirmed_timetable_link = 'bookings/timetable/' . $this->nextYear . '/' . $this->nextWeek;

			$week = $this->nextWeek;
			$year = $this->nextYear;
		}

		// if non delivery, dont show
		if ($this->auth->user->non_delivery == 1) {
			$confirmed_timetable = TRUE;
		} else {
			$where = array(
				'staffID' => $this->auth->user->staffID,
				'week' => $week,
				'year' => $year,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('timetable_read')->where($where)->limit(1)->get();

			if ($res->num_rows() == 1) {
				$confirmed_timetable = TRUE;
			}
		}

		// has unsubmitted timesheets?
		$unsubmitted_timesheets = FALSE;
		$where = array(
			'staffID' => $this->auth->user->staffID,
			'status' => 'unsubmitted',
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('timesheets')->where($where)->limit(1)->get();
		if ($res->num_rows() > 0) {
			$unsubmitted_timesheets = TRUE;
		}

		// has unnaproved timesheet items?
		$unapproved_timesheet_items = FALSE;
		$where = array(
			'approverID' => $this->auth->user->staffID,
			'status' => 'submitted',
			'accountID' => $this->auth->user->accountID
		);
		$res_1 = $this->db->from('timesheets_items')->where($where)->limit(1)->get();
		$res_2 = $this->db->from('timesheets_expenses')->where($where)->limit(1)->get();
		if ($res_1->num_rows() > 0 || ($this->auth->has_features('expenses') && $res_2->num_rows() > 0)) {
			$unapproved_timesheet_items = TRUE;
		}

		// has unsubmitted evaluations
		$unsubmitted_evaluations = FALSE;
		$where = array(
			'byID' => $this->auth->user->staffID,
			'accountID' => $this->auth->user->accountID,
			'type' => 'evaluation',
			'date <=' => mdate('%Y-%m-%d')
		);
		$where_in = array(
			'unsubmitted',
			'rejected'
		);
		$res = $this->db->from('bookings_lessons_notes')->where($where)->where_in('status', $where_in)->limit(1)->get();
		if ($res->num_rows() > 0) {
			$unsubmitted_evaluations = TRUE;
		}

		// has unsubmitted evaluations
		$unapproved_evaluations = FALSE;
		if (in_array($this->auth->user->department, array('headcoach'))) {
			$where = array(
				'bookings_lessons_notes.accountID' => $this->auth->user->accountID,
				'bookings_lessons_notes.type' => 'evaluation',
				'bookings_lessons_notes.status' => 'submitted'
			);
			if ($this->auth->user->department == 'headcoach') {
				$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
			}
			$res = $this->db->select('staff.*,bookings_lessons_notes.*')->from('bookings_lessons_notes')
			->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'inner')
			->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
			->where($where)->limit(1)->get();
			if ($res->num_rows() > 0) {
				$unapproved_evaluations = TRUE;
			}
		}


		// has unread policies?
		$unread_policies = FALSE;
		$where = array(
			'files.accountID' => $this->auth->user->accountID,
			'settings_resources.policies' => 1
		);

		$policies = $this->db->select('files.*, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('files_brands') . '.brandID SEPARATOR \',\') AS brands')
			->from('files')
			->join('settings_resourcefile_map', 'files.attachmentID = settings_resourcefile_map.attachmentID', 'inner')
			->join('settings_resources', 'settings_resourcefile_map.resourceID = settings_resources.resourceID', 'inner')
			->join('files_brands', 'files.attachmentID = files_brands.attachmentID', 'left')
			->where($where)
			->order_by('files.name asc')
			->group_by('files.attachmentID')
			->get();

		if ($this->auth->user->accept_policies == "0000-00-00 00:00:00" || empty($this->auth->user->accept_policies)) {
			if ($policies->num_rows()>0) {
				$unread_policies = TRUE;
			}
		} else {
			foreach ($policies->result() as $policy) {
				if (strtotime($policy->modified)>strtotime($this->auth->user->accept_policies)) {
					$unread_policies = TRUE;
					break;
				}
			}
		}

		// has unread safety docs?
		$unread_safety = FALSE;
		$safety_unread_own = $this->safety_unread_own();
		if (isset($safety_unread_own['items']) && count($safety_unread_own['items']) > 0) {
			$unread_safety = TRUE;
		}

		// late equipment?
		$late_equipment = 0;
		$where = array(
			'dateIn <' => mdate('%Y-%m-%d %H:%i:%s'),
			'status' => 1,
			'equipment_bookings.type' => 'staff',
			'equipment_bookings.staffID' => $this->auth->user->staffID,
			'equipment_bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('equipment_bookings.*, staff.first, staff.surname, equipment.name')->from('equipment_bookings')->join('equipment', 'equipment_bookings.equipmentID = equipment.equipmentID', 'inner')->join('staff', 'equipment_bookings.staffID = staff.staffID', 'inner')->where($where)->group_by('equipment_bookings.bookingID')->order_by('dateIn asc')->get();

		$late_equipment = $res->num_rows();

		// get session offers
		$session_offers = 0;
		if ($this->auth->has_features('offer_accept') || $this->auth->has_features('offer_accept_manual')) {
			$where = array(
				'staffID' => $this->auth->user->staffID,
				'status' => 'offered',
				'accountID' => $this->auth->user->accountID
			);
			$session_offers = $this->db->from('offer_accept')->where($where)->get()->num_rows();
		}

		// upcoming events
		$where = array(
			'show_dashboard' => 1,
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$lesson_types = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();
		$upcoming_events = array();
		if ($lesson_types->num_rows() > 0) {
			foreach ($lesson_types->result() as $type) {
				$where = array(
					'bookings_lessons.typeID' => $type->typeID,
					'bookings.type' => 'event',
					'bookings_blocks.provisional !=' => 1,
					'bookings.endDate >' => mdate('%Y-%m-%d %H:%i:%s'),
					'bookings.accountID' => $this->auth->user->accountID
				);
				$upcoming = $this->db->select('bookings.name, bookings_blocks.startDate, bookings_blocks.endDate, bookings_lessons.startTime, bookings_lessons.endTime, bookings_lessons.day')->from('bookings')->join('bookings_blocks', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')->where($where)->order_by('name asc')->limit(10)->get();
				$upcoming_events[$type->typeID] = array(
					'label' => Inflect::pluralize($type->name),
					'data' => $upcoming
				);
			}
		}

		// employee of month
		$employee_of_month = FALSE;
		$employeeID = $this->settings_library->get('employee_of_month');

		// look up if set
		if (!empty($employeeID)) {
			$where = array(
				'staffID' => $employeeID,
				'active' => 1,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->select('first, surname, id_photo_path')->from('staff')->where($where)->limit(1)->get();

			if ($res->num_rows()) {
				foreach ($res->result() as $row) {
					$employee_of_month = $row;
				}
			}
		}

		// tasks
		$where = array(
			'tasks.staffID' => $this->auth->user->staffID,
			'tasks.accountID' => $this->auth->user->accountID
		);
		$tasks = $this->db->select('staff.first, staff.surname, tasks.*')->from('tasks')->join('staff', 'tasks.staffID = staff.staffID', 'inner')->where($where)->order_by('complete asc, added asc')->get();

		// other's tasks
		$where = array(
			'tasks.staffID !=' => $this->auth->user->staffID,
			'tasks.accountID' => $this->auth->user->accountID
		);
		$tasks_others = $this->db->select('staff.first, staff.surname, tasks.*')->from('tasks')->join('staff', 'tasks.staffID = staff.staffID', 'inner')->where($where)->order_by('complete asc, added asc')->get();

		// dashboard config
		$dashboard_config = $this->auth->user->dashboard_config;

		// check for flash data
		$success = NULL;
		$info = NULL;
		$error = NULL;
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => 'Dashboard',
			'icon' => 'tachometer-alt',
			'section' => 'dashboard',
			'current_page' => 'dashboard',
			'unread_messages' => $unread_messages,
			'unsubmitted_timesheets' => $unsubmitted_timesheets,
			'unapproved_timesheet_items' => $unapproved_timesheet_items,
			'unsubmitted_evaluations' => $unsubmitted_evaluations,
			'unapproved_evaluations' => $unapproved_evaluations,
			'unread_policies' => $unread_policies,
			'unread_safety' => $unread_safety,
			'is_birthday' => $is_birthday,
			'late_equipment' => $late_equipment,
			'session_offers' => $session_offers,
			'confirmed_timetable' => $confirmed_timetable,
			'confirmed_timetable_week' => $confirmed_timetable_week,
			'confirmed_timetable_link' => $confirmed_timetable_link,
			'employee_of_month' => $employee_of_month,
			'policies' => $policies,
			'upcoming_events' => $upcoming_events,
			'tasks' => $tasks,
			'tasks_others' => $tasks_others,
			'dashboard_config' => $dashboard_config,
			'success' => $success,
			'info' => $info,
			'error' => $error
		);

		$this->crm_view('dashboard/main', $data);
	}

	public function save_state() {
		$json_string = $this->input->post('json');

		// decode
		$json = json_decode($json_string);

		// check for error
		if (json_last_error() != JSON_ERROR_NONE) {
			echo 'INVALID_JSON';
			return FALSE;
		}

		// validate json
		$validated_json = array();
		if (count($json) > 0) {
			foreach ($json as $key => $widget_area) {
				foreach ($widget_area as $box_key => $box) {
					$id = NULL;
					$state = NULL;
					if (isset($box->id) && !empty($box->id)) {
						$id = $box->id;
					}
					if (isset($box->state) && in_array($box->state, array('open', 'collapsed'))) {
						$state = $box->state;
					}
					if (!empty($id) && !empty($state)) {
						$validated_json[$key][$box_key] = new stdClass();
						$validated_json[$key][$box_key]->id = $id;
						$validated_json[$key][$box_key]->state = $state;
					}
				}
			}
		}

		// save
		$data = array(
			'dashboard_config' => json_encode($validated_json),
			'modified' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'staffID' => $this->auth->user->staffID
		);
		$this->db->update('staff', $data, $where, 1);
		if ($this->db->affected_rows() > 0) {
			echo 'OK';
			return TRUE;
		}
		echo 'ERROR_SAVING';
		return FALSE;
	}

	public function ajax($type = 'section', $section = NULL) {
		// close session write as not required
		session_write_close();

		$sections = array();
		switch ($this->auth->user->department) {
			case 'management':
			case 'directors':
				$sections['highlights'] = array(
					'bookings_no_staff',
					'bookings_inactive_staff',
					'bookings_availability_exceptions',
					'staff_mandatory_required',
					'staff_timetables',
					'staff_probations'
				);
				break;
			case 'office':
				$sections['highlights'] = array(
					'bookings_unconfirmed',
					'bookings_no_sessions',
					'families_outstanding',
					'equipment_late',
					'safety_risk',
					'safety_school_inductions',
				);
				break;
			case 'headcoach':
				$sections['highlights'] = array(
					'staff_timetables',
					'safety_unread',
					'safety_risk',
					'safety_school_inductions',
					'equipment_late'
				);
				break;
		}
		$sections['bookings'] = array(
			'bookings_no_staff',
			'bookings_inactive_staff',
			'bookings_availability_exceptions',
			'bookings_unconfirmed',
			'bookings_provisional_blocks',
			'bookings_renewaldue',
			'bookings_renewaldue_nomeeting',
			'bookings_uninvoiced',
			'bookings_unsent_invoices',
			'bookings_no_sessions',
			'bookings_unassigned_customers'
		);
		$sections['staff'] = array(
			'staff_utilisation_week',
			'staff_utilisation_month',
			'staff_utilisation_quarter',
			'staff_coach_id',
			'staff_recruitment',
			'staff_mandatory_expiring',
			'staff_mandatory_required',
			'staff_additional_expiring',
			'staff_unconfirmed',
			'staff_availability_exceptions',
			'staff_timetables',
			'staff_probations',
			'staff_driving',
			'staff_website_due'
		);
		$sections['participants'] = array(
			'families_outstanding'
		);
		$sections['safety'] = array(
			'safety_risk',
			'safety_school_inductions',
			'safety_camp_inductions',
			'safety_unread',
			'safety_unread_own'
		);
		$sections['equipment'] = array(
			'equipment_late',
			'equipment_late_orgs',
			'equipment_late_contacts',
			'equipment_late_children'
		);
		$sections['birthdays'] = array(
			'staff_birthdays'
		);

		// if type is highlights, set section to same
		if ($type == 'highlights')  {
			$section = 'highlights';
		}

		if (!empty($section)) {
			if (!array_key_exists($section, $sections)) {
				show_404();
			} else {
				if (count($sections[$section]) > 0) {
					switch ($type) {
						case 'section':
							foreach ($sections[$section] as $item) {
								$res = $this->$item();

								if (!is_array($res) || $res === FALSE) {
									continue;
								}

								// check permission
								if (array_key_exists('exclude', $res) && is_array($res['exclude']) && in_array($this->auth->user->department, $res['exclude'])) {
									continue;
								}

								$data['id'] = $res['id'];
								$data['name'] = $res['name'];
								$data['items'] = $res['items'];

								$this->load->view('dashboard/items', $data);
							}
							break;
						case 'summary':
						case 'highlights':
							$data['items'] = array();

							foreach ($sections[$section] as $item) {
								$res = $this->$item();

								// if not access, skip
								if ($res === FALSE || !is_array($res)) {
									continue;
								}

								// check permission
								if (array_key_exists('exclude', $res) && is_array($res['exclude']) && in_array($this->auth->user->department, $res['exclude'])) {
									continue;
								}

								$item_data = array();
								$item_data['text'] = $res['name'];
								$item_data['status'] = 'green';
								$item_data['link'] = site_url('dashboard/' . $section . '#' . $item);
								if (array_key_exists('override_link', $res)) {
									$item_data['link'] = $res['override_link'];
								}
								$item_data['count'] = 0;

								// count up non-green items
								foreach ($res['items'] as $i) {
									if ($i['status'] != 'green') {
										$item_data['count']++;
									}
								}

								if (array_key_exists('override_count', $res)) {
									$item_data['count'] = $res['override_count'];
								}

								if ($type == 'highlights' && isset($res['short_name']) && !empty($res['short_name'])) {
									$item_data['text'] = $res['short_name'];
								}

								// show most urgent status
								foreach ($res['items'] as $i) {
									switch ($i['status']) {
										case 'amber':
											$item_data['status'] = 'amber';
											break;
										case 'red':
											$item_data['status'] = 'red';
											break 2;
									}
								}

								$data['items'][] = $item_data;
							}

							$view = 'dashboard/items';
							if ($type == 'highlights') {
								$view = 'dashboard/highlights';
							}
							$this->load->view($view, $data);
							break;
					}
				}
			}
		}
	}

	/**
	 * highlights dashboard
	 * @return void
	 */
	public function highlights() {

		$data['title'] = 'Highlights';
		$data['icon'] = 'star';
		$data['current_page'] = 'dashboard';
		$data['buttons'] = '<a class="btn" href="' . site_url('/') . '"><i class="far fa-angle-left"></i> Return to Dashboard</a>';
		$data['section'] = 'dashboard';
		$data['request'] = site_url('dashboard/ajax/section/highlights');

		$this->crm_view('dashboard/detail', $data);
	}

	/**
	 * bookings dashboard
	 * @return void
	 */
	public function bookings() {

		if (!$this->auth->has_features('dashboard_bookings')) {
			show_403();
		}

		$data['title'] = 'Bookings';
		$data['icon'] = 'calendar';
		$data['current_page'] = 'dashboard';
		$data['buttons'] = '<a class="btn" href="' . site_url('/') . '"><i class="far fa-angle-left"></i> Return to Dashboard</a>';
		$data['section'] = 'dashboard';
		$data['request'] = site_url('dashboard/ajax/section/bookings');

		if($this->input->is_ajax_request()){
			$this->load->view('dashboard/detail', $data);
			exit();
		}
		$this->crm_view('dashboard/detail', $data);
	}

	/**
	 * staff dashboard
	 * @return void
	 */
	public function staff() {

		if (!$this->auth->has_features('dashboard_staff')) {
			show_403();
		}

		$data['title'] = 'Staff';
		$data['icon'] = 'user';
		$data['current_page'] = 'dashboard';
		$data['buttons'] = '<a class="btn" href="' . site_url('/') . '"><i class="far fa-angle-left"></i> Return to Dashboard</a>';
		$data['section'] = 'dashboard';
		$data['request'] = site_url('dashboard/ajax/section/staff');

		$this->crm_view('dashboard/detail', $data);
	}

	/**
	 * families
	 * @return void
	 */
	public function participants() {

		if (!$this->auth->has_features(array('dashboard_participants', 'participants'))) {
			show_403();
		}

		$data['title'] = $this->settings_library->get_label('participants');
		$data['icon'] = 'users';
		$data['current_page'] = 'dashboard';
		$data['buttons'] = '<a class="btn" href="' . site_url('/') . '"><i class="far fa-angle-left"></i> Return to Dashboard</a>';
		$data['section'] = 'dashboard';
		$data['request'] = site_url('dashboard/ajax/section/participants');

		$this->crm_view('dashboard/detail', $data);
	}

	/**
	 * H&S
	 * @return void
	 */
	public function safety() {

		if (!$this->auth->has_features(array('dashboard_health_safety', 'safety'))) {
			show_403();
		}

		$data['title'] = 'Health & Safety';
		$data['icon'] = 'book';
		$data['current_page'] = 'dashboard';
		$data['buttons'] = '<a class="btn" href="' . site_url('/') . '"><i class="far fa-angle-left"></i> Return to Dashboard</a>';
		$data['section'] = 'dashboard';
		$data['request'] = site_url('dashboard/ajax/section/safety');

		$brands = $this->db->from('brands')->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])->get();

		$search_fields = [
			'brand_id' => NULL
		];

		if ($this->input->post()) {
			$data['request'] = site_url('dashboard/ajax/section/safety?brand=' . set_value('search_brand_id'));
			$search_fields['brand_id'] = set_value('search_brand_id');
		}

		$data['search'] = [
			'brands' => $brands,
			'search_fields' => $search_fields
		];

		$this->crm_view('dashboard/detail', $data);
	}

	/**
	 * equipment
	 * @return void
	 */
	public function equipment() {

		if (!$this->auth->has_features(array('dashboard_equipment', 'equipment'))) {
			show_403();
		}

		$data['title'] = 'Equipment';
		$data['icon'] = 'futbol';
		$data['current_page'] = 'dashboard';
		$data['buttons'] = '<a class="btn" href="' . site_url('/') . '"><i class="far fa-angle-left"></i> Return to Dashboard</a>';
		$data['section'] = 'dashboard';
		$data['request'] = site_url('dashboard/ajax/section/equipment');

		$this->crm_view('dashboard/detail', $data);
	}

	/**
	 * list of sessions with no staff or only partially staffed
	 * @return void
	 */
	private function bookings_no_staff() {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_no_staff',
			'name' => 'Sessions with No Staff',
			'short_name' => 'No Staff',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		// get staff
		$lesson_staff = array();
		$where = array(
			'bookings_blocks.endDate >' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);
		$res_staff = $this->db->select('bookings_lessons_staff.*')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where)->get();
		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $staff) {
				$lesson_staff[$staff->lessonID][] = $staff;
			}
		}

		$staffed_from = NULL;
		$staffed_to = NULL;

		// get bookings
		$where = array(
			'bookings_blocks.endDate >' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.cancelled !=' => 1,
			'bookings_blocks.provisional !=' => 1,
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings.bookingID, bookings.type as booking_type, bookings.name as event, bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd, bookings_lessons.lessonID, bookings_lessons.startTime, bookings_lessons.day, bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, orgs.name as org')->from('bookings')->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->group_by('bookings_lessons.lessonID')->order_by('bookings_blocks.startDate asc, day asc, startTime asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				$staffed_from = NULL;
				$staffed_to = NULL;

				if (array_key_exists($row->lessonID, $lesson_staff)) {
					foreach ($lesson_staff[$row->lessonID] as $staff) {
						// if first staff, set start and end
						if (empty($staffed_from)) {
							$staffed_from = strtotime($staff->startDate);
						}
						if (empty($staffed_to)) {
							$staffed_to = strtotime($staff->endDate);
						}

						// expand start and end
						if (strtotime($staff->startDate) < $staffed_from) {
							$staffed_from = strtotime($staff->startDate);
						}
						if (strtotime($staff->endDate) > $staffed_to) {
							$staffed_to = strtotime($staff->endDate);
						}
					}
				}

				// switch between block and session dates depending on if session dates set
				if (!empty($row->lessonStart) && !empty($row->lessonEnd)) {
					$startDate = $row->lessonStart;
					$endDate = $row->lessonEnd;
				} else {
					$startDate = $row->blockStart;
					$endDate = $row->blockEnd;
				}

				if ($staffed_from > strtotime($startDate) || $staffed_to < strtotime($endDate)) {
					// get name
					$name = NULL;
					if ($row->booking_type == 'event' && !empty($row->event)) {
						$name = $row->event;
					} else if (!empty($row->org)) {
						$name = $row->org;
					}

					// get status
					$status = 'amber';
					if (strtotime($startDate) <= strtotime('+' . $triggers['red'])) {
						$status = 'red';
					}

					$data['items'][] = array(
						'text' => mysql_to_uk_date($startDate) . ' - ' . anchor('sessions/staff/' . $row->lessonID, $name) . ' - ' . ucwords($row->day) . 's @ ' . substr($row->startTime, 0, 5),
						'status' => $status
					);
				}
			}
		}

		return $data;

	}

	/**
	 * list of sessions with inactive staff
	 * @return void
	 */
	private function bookings_inactive_staff() {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_inactive_staff',
			'name' => 'Sessions with Inactive Staff',
			'short_name' => 'Inactive Staff',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		// session staff
		$where = array(
			'bookings_blocks.endDate >' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.cancelled !=' => 1,
			'bookings_blocks.provisional !=' => 1,
			'staff.active !=' => 1,
			'bookings_lessons_staff.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings.bookingID, bookings.type as booking_type, bookings.name as event, bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd, bookings_lessons.lessonID, bookings_lessons.startTime, bookings_lessons.day, bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, orgs.name as org, staff.staffID, staff.first as staff_first, staff.surname as staff_last, bookings_lessons_staff.startDate as staff_startDate, bookings_lessons_staff.endDate as staff_endDate')->from('bookings')->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->where($where)->group_by('bookings_lessons.lessonID, staff.staffID')->order_by('bookings_blocks.startDate asc, day asc, startTime asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get name
				$name = NULL;
				if ($row->booking_type == 'event' && !empty($row->event)) {
					$name = $row->event;
				} else if (!empty($row->org)) {
					$name = $row->org;
				}

				// get status
				$status = 'amber';
				if (strtotime($row->staff_startDate) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$key = $row->blockStart . $row->day . $row->startTime . $row->staffID . $row->lessonID;

				$data['items'][$key] = array(
					'text' => anchor('sessions/staff/' . $row->lessonID, $name) . ' - ' . anchor('staff/edit/' . $row->staffID, $row->staff_first . ' ' . $row->staff_last) . ' - ' . ucwords($row->day) . 's @ ' . substr($row->startTime, 0, 5) . ' (' . mysql_to_uk_date($row->staff_startDate) . '-' . mysql_to_uk_date($row->staff_endDate) . ')',
					'status' => $status
				);
			}
		}

		// on session by exception
		$where = array(
			'bookings_lessons_exceptions.type' => 'staffchange',
			'bookings_lessons_exceptions.date >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_lessons_exceptions.date <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.cancelled !=' => 1,
			'bookings_blocks.provisional !=' => 1,
			'staff.active !=' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings.bookingID, bookings.type as booking_type, bookings.name as event, bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd, bookings_lessons.lessonID, bookings_lessons.startTime, bookings_lessons.day, bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, orgs.name as org, staff.staffID, staff.first as staff_first, staff.surname as staff_last, bookings_lessons_exceptions.date, bookings_lessons_exceptions.exceptionID')->from('bookings')->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('bookings_lessons_exceptions', 'bookings_lessons.lessonID = bookings_lessons_exceptions.lessonID', 'inner')->join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'inner')->where($where)->group_by('bookings_lessons.lessonID, staff.staffID')->order_by('bookings_blocks.startDate asc, day asc, startTime asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get name
				$name = NULL;
				if ($row->booking_type == 'event' && !empty($row->event)) {
					$name = $row->event;
				} else if (!empty($row->org)) {
					$name = $row->org;
				}

				// get status
				$status = 'amber';
				if (strtotime($row->date) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$key = $row->date . $row->day . $row->startTime . $row->staffID . $row->lessonID;

				$data['items'][$key] = array(
					'text' => anchor('sessions/exceptions/' . $row->lessonID, $name) . ' - ' . anchor('staff/edit/' . $row->staffID, $row->staff_first . ' ' . $row->staff_last) . ' - ' . ucwords($row->day) . ' @ ' . substr($row->startTime, 0, 5) . ' (' . mysql_to_uk_date($row->date) . ') ',
					'status' => $status
				);
			}
		}

		ksort($data['items']);

		return $data;

	}

	/**
	 * list of sessions with availability exception conflicts
	 * @return void
	 */
	private function bookings_availability_exceptions() {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_availability_exceptions',
			'name' => 'Availability Exception Conflicts',
			'short_name' => 'Exception Conflicts',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		// check against session staff
		$where = array(
			'bookings_blocks.endDate >' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'staff_availability_exceptions.to >' => mdate('%Y-%m-%d %H:%i:%s'),
			'staff_availability_exceptions.from <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.cancelled !=' => 1,
			'bookings_blocks.provisional !=' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings_lessons_staff.recordID, bookings_blocks.startDate as block_startDate, bookings_blocks.endDate as block_endDate, bookings.bookingID, bookings_lessons.day, bookings_lessons.startTime as lesson_startTime, bookings_lessons.endTime as lesson_endTime, bookings_lessons.lessonID, bookings_lessons.startDate as lesson_startDate, bookings_lessons.endDate as lesson_endDate, bookings_lessons_staff.staffID, bookings_lessons_staff.startTime as staff_startTime, bookings_lessons_staff.endTime as staff_endTime, staff_availability_exceptions.from as exception_from, staff_availability_exceptions.to as exception_to, staff_availability_exceptions.exceptionsID, staff.first, staff.surname, bookings_lessons_staff.startDate as staff_startDate, bookings_lessons_staff.endDate as staff_endDate')->from('bookings')->join('bookings_blocks', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')->join('bookings_lessons', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('bookings_lessons_staff', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('staff_availability_exceptions', 'staff_availability_exceptions.staffID = bookings_lessons_staff.staffID', 'inner')->join('staff', 'staff.staffID = staff_availability_exceptions.staffID', 'inner')->where($where)->order_by('bookings_blocks.startDate asc, day asc, bookings_lessons.startTime asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get vars
				$exceptionFrom = $row->exception_from;
				$exceptionTo = $row->exception_to;
				$startDate = $row->staff_startDate;
				$endDate = $row->staff_endDate;
				$startTime = $row->lesson_startTime;
				$endTime = $row->lesson_endTime;
				$day = $row->day;
				$lessonID = $row->lessonID;

				// get session dates from block
				$lesson_startDate = $row->block_startDate;
				$lesson_endDate = $row->block_endDate;

				// if dates overridden in lesson, use instead
				if (!empty($row->lesson_startDate)) {
					$lesson_startDate = $row->lesson_startDate;
				}
				if (!empty($row->lesson_endDate)) {
					$lesson_endDate = $row->lesson_endDate;
				}

				// if staff staffed longer than session dates, reduce
				if (strtotime($startDate) < strtotime($lesson_startDate)) {
					$startDate = $lesson_startDate;
				}
				if (strtotime($endDate) > strtotime($lesson_endDate)) {
					$endDate = $lesson_endDate;
				}

				// if staff not on full lesson, use their times
				if (!empty($row->staff_startTime)) {
					$startTime = $row->staff_startTime;
				}
				if (!empty($row->staff_endTime)) {
					$endTime = $row->staff_endTime;
				}

				// only want future exceptions
				if (strtotime($startDate) < strtotime(mdate('%Y-%m-%d'))) {
					$startDate = mdate('%Y-%m-%d');
				}

				// only want 45 days in advance
				if (strtotime($endDate) > strtotime("+" . $triggers['amber'])) {
					$endDate = mdate('%Y-%m-%d', strtotime("+" . $triggers['amber']));
				}

				while(strtotime($startDate) <= strtotime($endDate)) {

					$checkDay = strtolower(date('l', strtotime($startDate)));

					if ($day == $checkDay) {

						$lessonFrom = $startDate.' '.$startTime;
						$lessonTo = $startDate.' '.$endTime;

						// check if exception intercept with lesson
						if(strtotime($lessonFrom) <= strtotime($exceptionTo) && strtotime($lessonTo) >= strtotime($exceptionFrom)) {

							// check to see if staff change has been added to resolve conflict or session is cancelled so doesn't need any staff
							$where = array(
								'lessonID' => $lessonID,
								'date' => $startDate,
								'accountID' => $this->auth->user->accountID
							);
							$res_staff = $this->db->from('bookings_lessons_exceptions')->where($where)->get();

							if ($res_staff->num_rows() == 0) {

								// get status
								$status = 'amber';
								if (strtotime($startDate) <= strtotime('+' . $triggers['red'])) {
									$status = 'red';
								}

								// add key for sorting
								$key = $startDate . $startTime . $row->staffID . 'staffed_' . $row->recordID;

								$data['items'][$key] = array(
									'text' => anchor('staff/availability/' . $row->staffID . '/exceptions', $row->first . ' ' . $row->surname) . ' - ' . anchor('sessions/exceptions/' . $row->lessonID, ucwords($row->day) . ' ' . mysql_to_uk_date($startDate) . ' @ ' . substr($startTime, 0, 5)),
									'status' => $status
								);
							}
						}
					}

					$startDate = date('Y-m-d', strtotime("+1 day", strtotime($startDate)));
				}
			}
		}

		// check against session exceptions
		$where = array(
			'bookings_lessons_exceptions.date >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_lessons_exceptions.date <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings_lessons_exceptions.type' => 'staffchange',
			'staff_availability_exceptions.to >' => mdate('%Y-%m-%d %H:%i:%s'),
			'staff_availability_exceptions.from <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.cancelled !=' => 1,
			'bookings_blocks.provisional !=' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings_lessons_exceptions.exceptionID, bookings_lessons_exceptions.date, bookings_lessons_exceptions.staffID, bookings_lessons.lessonID, bookings_lessons.day, bookings_lessons.startDate as lesson_startDate, bookings_lessons.endDate as lesson_endDate, bookings_lessons.startTime as lesson_startTime, bookings_lessons.endTime as lesson_endTime, bookings_blocks.startDate as block_startDate, bookings_blocks.endDate as block_endDate, staff_availability_exceptions.from as exception_from, staff_availability_exceptions.to as exception_to, staff.first, staff.surname')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')->join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'inner')->join('staff_availability_exceptions', 'bookings_lessons_exceptions.staffID = staff_availability_exceptions.staffID', 'inner')->where($where)->order_by('bookings_lessons_exceptions.date asc, bookings_lessons.startTime asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get vars
				$exceptionFrom = $row->exception_from;
				$exceptionTo = $row->exception_to;
				$exceptionDate = $row->date;
				$startTime = $row->lesson_startTime;
				$endTime = $row->lesson_endTime;
				$day = $row->day;
				$lessonID = $row->lessonID;

				// get session dates from block
				$lesson_startDate = $row->block_startDate;
				$lesson_endDate = $row->block_endDate;

				// if dates overridden in lesson, use instead
				if (!empty($row->lesson_startDate)) {
					$lesson_startDate = $row->lesson_startDate;
				}
				if (!empty($row->lesson_endDate)) {
					$lesson_endDate = $row->lesson_endDate;
				}

				// if exception date outside of session dates, skip
				if (strtotime($exceptionDate) < strtotime($lesson_startDate) || strtotime($exceptionDate) > strtotime($lesson_endDate)) {
					continue;
				}

				$lessonFrom = $exceptionDate . ' ' . $startTime;
				$lessonTo = $exceptionDate . ' ' . $endTime;

				// check if exception intercept with lesson
				if(strtotime($lessonFrom) <= strtotime($exceptionTo) && strtotime($lessonTo) >= strtotime($exceptionFrom)) {

					// get status
					$status = 'amber';
					if (strtotime($exceptionDate) <= strtotime('+' . $triggers['red'])) {
						$status = 'red';
					}

					// add key for sorting
					$key = $exceptionDate . $startTime . $row->staffID . 'exception_' . $row->exceptionID;

					$data['items'][$key] = array(
						'text' => anchor('staff/availability/' . $row->staffID . '/exceptions', $row->first . ' ' . $row->surname) . ' - ' . anchor('sessions/exceptions/' . $row->lessonID, ucwords($row->day) . ' ' . mysql_to_uk_date($exceptionDate) . ' @ ' . substr($startTime, 0, 5)) . ' (Exception)',
						'status' => $status
					);
				}
			}
		}

		// sort
		ksort($data['items']);

		return $data;

	}

	/**
	 * list of unconfirmed bookings
	 * @return void
	 */
	private function bookings_unconfirmed() {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_unconfirmed',
			'name' => 'Unconfirmed Bookings',
			'short_name' => 'Unconfirmed',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'bookings.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.cancelled !=' => 1,
			'bookings.confirmed !=' => 1,
			'bookings_blocks.provisional !=' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings.bookingID, bookings.startDate, bookings.name as event, bookings.type as booking_type, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('bookings_blocks', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')->group_by('bookings.bookingID')->where($where)->order_by('startDate asc, org asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get name
				$name = NULL;
				if ($row->booking_type == 'event' && !empty($row->event)) {
					$name = $row->event;
				} else if (!empty($row->org)) {
					$name = $row->org;
				}

				// get status
				$status = 'amber';
				if (strtotime($row->startDate) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => mysql_to_uk_date($row->startDate) . ' - ' . anchor('bookings/jumpto/' . $row->bookingID, $name),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of provisional blocks
	 * @return void
	 */
	private function bookings_provisional_blocks() {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_provisional_blocks',
			'name' => 'Provisional Blocks',
			'short_name' => 'Provisional',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.cancelled !=' => 1,
			'bookings_blocks.provisional' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings_blocks.blockID, bookings_blocks.name as block_name, bookings.bookingID, bookings_blocks.startDate, bookings.name as event, bookings.type as booking_type, orgs.name as org')->from('bookings_blocks')->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->group_by('bookings_blocks.blockID')->where($where)->order_by('startDate asc, org asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get name
				$name = NULL;
				if ($row->booking_type == 'event' && !empty($row->event)) {
					$name = $row->event;
				} else if (!empty($row->org)) {
					$name = $row->org;
				}

				$name .= ' (' . $row->block_name . ')';

				// get status
				$status = 'amber';
				if (strtotime($row->startDate) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => mysql_to_uk_date($row->startDate) . ' - ' . anchor('bookings/blocks/edit/' . $row->blockID, $name),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of bookings due for renewal
	 * @return void
	 */
	private function bookings_renewaldue($no_meeting_only = FALSE) {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_renewaldue',
			'name' => 'Bookings due for Renewal',
			'short_name' => 'Renewal Due',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		if ($no_meeting_only == TRUE) {
			$data['id'] .= '_nomeeting';
			$data['name'] .= ' (No Meeting)';
		}

		$where = array(
			'bookings.type' => 'booking',
			'bookings.renewalDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.contract_renewal' => 1,
			'bookings.contract_renewed IS NULL' => NULL,
			'bookings_blocks.provisional !=' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// if no meeting only
		if ($no_meeting_only == TRUE) {
			$where['renewalMeetingDate IS NULL'] = NULL;
		}

		$res = $this->db->select('bookings.bookingID, bookings.renewalDate, bookings.name as event, bookings.type as booking_type, orgs.name as org')->from('bookings')->join('bookings_blocks', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->group_by('bookings.bookingID')->order_by('renewalDate asc, org asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get name
				$name = NULL;
				if ($row->booking_type == 'event' && !empty($row->event)) {
					$name = $row->event;
				} else if (!empty($row->org)) {
					$name = $row->org;
				}

				// get status
				$status = 'amber';
				if (strtotime($row->renewalDate) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => mysql_to_uk_date($row->renewalDate) . ' - ' . anchor('bookings/edit/' . $row->bookingID, $name),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of bookings due for renewal (no meeting set)
	 * @return void
	 */
	private function bookings_renewaldue_nomeeting() {
		return $this->bookings_renewaldue(TRUE);
	}

	/**
	 * list of uninvoiced bookings since 01/09/2014 (new invoice system launch date)
	 * @return void
	 */
	private function bookings_uninvoiced() {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_uninvoiced',
			'name' => 'Uninvoiced Bookings',
			'short_name' => 'Uninvoiced',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		// cache lookups
		$excluded_blocks = array();
		$booking_blocks = array();

		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		$res_blocks = $this->db->from('bookings_blocks')->where($where)->get();
		if ($res_blocks->num_rows() > 0) {
			foreach($res_blocks->result() as $row) {
				$booking_blocks[$row->bookingID][] = $row->blockID;
			}
		}

		// get invoiced blocks
		$where = array(
			'bookings_invoices.accountID' => $this->auth->user->accountID
		);
		$res_invoices = $this->db->from('bookings_invoices')->join('bookings_invoices_blocks', 'bookings_invoices.invoiceID = bookings_invoices_blocks.invoiceID', 'inner')->where($where)->group_by('bookings_invoices_blocks.blockID')->get();
		if ($res_invoices->num_rows() > 0) {
			foreach($res_invoices->result() as $row) {
				$excluded_blocks[$row->blockID] = TRUE;
			}
		}

		// get uninvoiced blocks over 1 month in advance
		$where = array(
			'bookings_blocks.startDate >=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings_invoices_blocks.invoiceID IS NULL' => NULL,
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);
		$res_invoices = $this->db->from('bookings_blocks')->join('bookings_invoices_blocks', 'bookings_blocks.blockID = bookings_invoices_blocks.blockID', 'left')->group_by('bookings_blocks.blockID')->where($where)->get();
		if ($res_invoices->num_rows() > 0) {
			foreach($res_invoices->result() as $row) {
				$excluded_blocks[$row->blockID] = TRUE;
			}
		}

		$where = array(
			'bookings.startDate >=' => '2014-09-01',
			'bookings.type' => 'booking',
			'bookings_blocks.provisional !=' => 1,
			'bookings.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('bookings_blocks', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->group_by('bookings.bookingID')->order_by('startDate asc, org asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				if (array_key_exists($row->bookingID, $booking_blocks) && count($booking_blocks[$row->bookingID]) > 0) {
					foreach ($booking_blocks[$row->bookingID] as $blockID) {
						if (!array_key_exists($blockID, $excluded_blocks)) {

							// get status
							$status = 'amber';
							if (strtotime($row->startDate) <= strtotime('+' . $triggers['red'])) {
								$status = 'red';
							}

							$data['items'][] = array(
								'text' => mysql_to_uk_date($row->startDate) . ' - ' . anchor('bookings/finances/invoices/' . $row->bookingID, $row->org),
								'status' => $status
							);
							break;
						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * list of unsent invoices
	 * @return void
	 */
	private function bookings_unsent_invoices() {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_unsent_invoices',
			'name' => 'Unsent Invoices',
			'short_name' => 'Unsent Invoices',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'invoiceDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'is_invoiced !=' => 1,
			'bookings_blocks.provisional !=' => 1,
			'bookings_invoices.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings_invoices.invoiceID, bookings_invoices.invoiceDate, bookings.bookingID, bookings.startDate, bookings.name as event, bookings.type as booking_type, orgs.name as org')->from('bookings_invoices')->join('bookings', 'bookings_invoices.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')->where($where)->group_by('bookings_invoices.invoiceID')->order_by('invoiceDate asc, org asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get name
				$name = NULL;
				if ($row->booking_type == 'event' && !empty($row->event)) {
					$name = $row->event;
				} else if (!empty($row->org)) {
					$name = $row->org;
				}

				// get status
				$status = 'amber';
				if (strtotime($row->invoiceDate) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => mysql_to_uk_date($row->invoiceDate) . ' - ' . anchor('bookings/finances/invoices/' . $row->bookingID, $name),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of bookings with no lessons
	 * @return void
	 */
	private function bookings_no_sessions() {

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_no_sessions',
			'name' => 'Bookings with No Sessions',
			'short_name' => 'No Sessions',
			'icon' => 'calendar',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'bookings.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings.cancelled !=' => 1,
			'bookings.confirmed !=' => 1,
			'bookings_blocks.provisional !=' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('bookings.bookingID, bookings.startDate, bookings.name as event, bookings.type as booking_type, orgs.name as org, COUNT(lessonID) as lesson_count, COUNT(' . $this->db->dbprefix('bookings_blocks') . '.blockID) as block_count')->from('bookings')->join('bookings_lessons', 'bookings_lessons.bookingID = bookings.bookingID', 'left')->join('bookings_blocks', 'bookings_blocks.bookingID = bookings.bookingID', 'left')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->having('lesson_count = 0')->order_by('bookings.startDate asc, org asc')->group_by('bookings.bookingID')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get name
				$name = NULL;
				if ($row->booking_type == 'event' && !empty($row->event)) {
					$name = $row->event;
				} else if (!empty($row->org)) {
					$name = $row->org;
				}

				// get status
				$status = 'amber';
				if (strtotime($row->startDate) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				// get link
				$link = 'bookings/sessions/' . $row->bookingID;
				if ($row->block_count == 0) {
					$link = 'bookings/blocks/' . $row->bookingID;
				}

				$data['items'][] = array(
					'text' => mysql_to_uk_date($row->startDate) . ' - ' . anchor($link, $name),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of customers not assigned to areas
	 * @return void
	 */
	private function bookings_unassigned_customers() {

		if (!$this->auth->has_features('customers_schools') && !$this->auth->has_features('customers_schools_prospects') && !$this->auth->has_features('customers_orgs') && !$this->auth->has_features('customers_orgs_prospects')) {
			return FALSE;
		}

		$data = array(
			'id' => 'bookings_unassigned_customers',
			'name' => 'Unassigned ' . $this->settings_library->get_label('customers'),
			'short_name' => 'unassigned ' . $this->settings_library->get_label('customers'),
			'icon' => 'laptop',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$where = "(regionID IS NULL OR areaID IS NULL) AND prospect = 0 AND accountID = " . $this->auth->user->accountID;

		$res = $this->db->select('orgs.orgID, orgs.name')->from('orgs')->where($where)->order_by('name asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get status
				$status = 'amber';

				$data['items'][] = array(
					'text' => anchor('customers/edit/' . $row->orgID, $row->name),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of staff with missing coach id info
	 * @return void
	 */
	private function staff_utilisation_week() {
		return $this->staff_utilisation('week');
	}
	private function staff_utilisation_month() {
		return $this->staff_utilisation('month');
	}
	private function staff_utilisation_quarter() {
		return $this->staff_utilisation('quarter');
	}
	private function staff_utilisation($period = 'week') {

		if (!$this->auth->has_features('reports')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_utilisation_' . $period,
			'name' => 'Utilisation (' . ucwords($period) . ')',
			'short_name' => 'Utilisation (' . ucwords($period) . ')',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach',
				'office'
			),
			'items' => array(),
			'override_count' => 0,
			'override_link' => 'reports/utilisation/' . $period
		);

		$search_fields = array(
			'date_from' => NULL,
			'date_to' => date('d/m/Y'),
			'is_active' => 'yes',
			'search' => 'true'
		);

		switch ($period) {
			case 'week':
			default:
				$search_fields['date_from'] = date('d/m/Y', strtotime('-6 days', strtotime(uk_to_mysql_date($search_fields['date_to']))));
				break;
			case 'month':
				$search_fields['date_from'] = date('d/m/Y', strtotime('-1 month', strtotime(uk_to_mysql_date($search_fields['date_to']))));
				break;
			case 'quarter':
				$search_fields['date_from'] = date('d/m/Y', strtotime('-3 months', strtotime(uk_to_mysql_date($search_fields['date_to']))));
				break;
		}

		$this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy', 'key_prefix' => $this->auth->user->accountID . '_'));

		// check if cached
		$cache_name = 'utilisation-' . $period;
		if (!$utilisation_averages = $this->cache->get($cache_name)) {
			// if not, process
			// load library
			$this->load->library('reports_library');
			// calc
			$utilisation_averages = $this->reports_library->calc_utilisation('averages', $search_fields);
			// save into the cache for 1 hour
			$this->cache->save($cache_name, $utilisation_averages, 3600);
		}

		if ($utilisation_averages['utilisation'] > $utilisation_averages['target_utilisation']) {
			$status = 'green';
		} else {
			$status= 'red';
		}

		$data['items'][] = array(
			'text' => anchor('reports/utilisation/' . $period, 'Average Utilisation: ' . round($utilisation_averages['utilisation'], 2) . '%'),
			'status' => $status
		);

		$data['override_count'] = round($utilisation_averages['utilisation'], 1) . '%';

		return $data;
	}

	/**
	 * list of staff with missing coach id info
	 * @return void
	 */
	private function staff_coach_id() {

		if (!$this->auth->has_features('staff_id')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_coach_id',
			'name' => 'Coach ID',
			'short_name' => 'Coach ID',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);

		$where_or = array(
			"`id_personalStatement` = ''",
			"`id_specialism` = ''",
			"`id_favQuote` = ''",
			"`id_sportingHero` = ''",
			"`profile_pic` = ''",
			"`id_personalStatement` IS NULL",
			"`id_specialism` IS NULL",
			"`id_favQuote` IS NULL",
			"`id_sportingHero` IS NULL",
			"`profile_pic` IS NULL"
		);

		if (count($where_or) > 0) {
			$where_or = '(' . implode(' OR ', $where_or) . ')';
		} else {
			$where_or = array();
		}

		$res = $this->db->from('staff')->where($where)->where($where_or, NULL, FALSE)->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				$data['items'][] = array(
					'text' => anchor('staff/id/' . $row->staffID, $row->first . ' ' . $row->surname),
					'status' => 'amber'
				);

			}
		}

		return $data;
	}

	/**
	 * list of staff with missing recruitment info
	 * @return void
	 */
	private function staff_recruitment() {

		if (!$this->auth->has_features('staff_management') || !check_tab_availability_on_dashboard('staff_recruitment')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_recruitment',
			'name' => 'Recruitment Checklist',
			'short_name' => 'Recruitment',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);

		$fields = $this->db
			->select()
			->from('accounts_fields')
			->where([
				'section' => 'staff_recruitment',
				'accountID' => $this->auth->user->accountID,
			])->get();

		$result_fields = [];
		$proofid_fields = 0;
		if ($fields->num_rows() > 0) {
			foreach ($fields->result() as $row) {
				$result_fields[$row->field] = $row->show;
			}
			foreach ($result_fields as $key => $value) {
				if (in_array($key, ['passport', 'ni_card', 'drivers_licence', 'birth_certificate', 'utility_bill', 'other'])
					&& $value == 1) {
					$proofid_fields += 1;
				}
			}
		}

		$where_or = array(
			"proofid_passport + proofid_nicard + proofid_driving + proofid_birth + proofid_utility + proofid_other < 3",
			"`proof_quals` != 1",
			"`proof_permit` != 1",
			"`checklist_idcard` != 1",
			"`checklist_paydates` != 1",
			"`checklist_timesheet` != 1",
			"`checklist_policy` != 1",
			"`checklist_travel` != 1",
			"`checklist_equal` != 1",
			"`checklist_contract` != 1",
			"`checklist_p45` != 1",
			"`checklist_crb` != 1",
			"`checklist_policies` != 1",
			"`checklist_details` != 1",
			"`checklist_tshirt` != 1"
		);

		if (count($where_or) > 0) {
			$where_or = '(' . implode(' OR ', $where_or) . ')';
		} else {
			$where_or = array();
		}

		$res = $this->db->from('staff')->where($where)->where($where_or, NULL, FALSE)->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get list of missing items
				$missing_items = array();

				if ((((isset($result_fields['passport']) && $result_fields['passport'] == 1) ? intval($row->proofid_passport) : 0) +
						((isset($result_fields['ni_card']) && $result_fields['ni_card'] == 1) ? intval($row->proofid_nicard) : 0) +
						((isset($result_fields['drivers_licence']) && $result_fields['drivers_licence'] == 1) ? intval($row->proofid_driving) : 0) +
						((isset($result_fields['birth_certificate']) && $result_fields['birth_certificate'] == 1) ? intval($row->proofid_birth) : 0) +
						((isset($result_fields['utility_bill']) && $result_fields['utility_bill'] == 1) ? intval($row->proofid_utility) : 0) +
						((isset($result_fields['other']) && $result_fields['other'] == 1) ? intval($row->proofid_other) : 0)) < 3 && $proofid_fields >= 3) {
					$missing_items[] = '3 Proofs of ID';
				}

				if ((isset($result_fields['proof_of_qualifications']) && $result_fields['proof_of_qualifications'] == 1) && $row->proof_quals != 1) {
					$missing_items[] = 'Qualifications/DBS';
				}

				if ((isset($result_fields['valid_working_permit']) && $result_fields['valid_working_permit'] == 1) && $row->proof_permit != 1) {
					$missing_items[] = 'Permit';
				}

				if ((isset($result_fields['id_card']) && $result_fields['id_card'] == 1) && $row->checklist_idcard != 1) {
					$missing_items[] = 'ID Card';
				}

				if ((isset($result_fields['pay_dates']) && $result_fields['pay_dates'] == 1) && $row->checklist_paydates != 1) {
					$missing_items[] = 'Pay Dates';
				}

				if ((isset($result_fields['timesheet']) && $result_fields['timesheet'] == 1) && $row->checklist_timesheet != 1) {
					$missing_items[] = 'Timesheet';
				}

				if ((isset($result_fields['policy_agreement']) && $result_fields['policy_agreement'] == 1) && $row->checklist_policy != 1) {
					$missing_items[] = 'Policy Agreement';
				}

				if ((isset($result_fields['travel_expenses']) && $result_fields['travel_expenses'] == 1) && $row->checklist_travel != 1) {
					$missing_items[] = 'Travel Expenses';
				}

				if ((isset($result_fields['equal_opportunities']) && $result_fields['equal_opportunities'] == 1) && $row->checklist_equal != 1) {
					$missing_items[] = 'Equal Opportunities';
				}

				if ((isset($result_fields['employment_contract']) && $result_fields['employment_contract'] == 1) && $row->checklist_contract != 1) {
					$missing_items[] = 'Employment Contract ';
				}

				if ((isset($result_fields['p45']) && $result_fields['p45'] == 1) && $row->checklist_p45 != 1) {
					$missing_items[] = 'P45/P46/P38';
				}

				if ((isset($result_fields['dbs']) && $result_fields['dbs'] == 1) && $row->checklist_crb != 1) {
					$missing_items[] = 'DBS';
				}

				if ((isset($result_fields['policies']) && $result_fields['policies'] == 1) && $row->checklist_policies != 1) {
					$missing_items[] = 'Policies';
				}

				if ((isset($result_fields['details_updated']) && $result_fields['details_updated'] == 1) && $row->checklist_details != 1) {
					$missing_items[] = 'Details Updated';
				}

				if ((isset($result_fields['tshirt']) && $result_fields['tshirt'] == 1) && $row->checklist_tshirt != 1) {
					$missing_items[] = 'T-shirt';
				}

				$data['items'][] = array(
					'text' => anchor('staff/recruitment/' . $row->staffID, $row->first . ' ' . $row->surname) . ' - ' . implode(', ', $missing_items),
					'status' => 'red'
				);

			}
		}

		return $data;
	}

	/**
	 * list of staff with expiring/expired mandatory qualifcations
	 * @return void
	 */
	private function staff_mandatory_expiring() {

		if (!$this->auth->has_features('staff_management')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_mandatory_expiring',
			'name' => 'Expired/Expiring Mandatory Qualifications',
			'short_name' => 'Expired/Expiring Mandatory Qualifications',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);

		$where_or = array(
			"(`qual_first` = '1' AND `qual_first_expiry_date` IS NOT NULL AND `qual_first_expiry_date` != '0000-00-00' AND `qual_first_expiry_date` <= DATE_ADD(UTC_TIMESTAMP(), INTERVAL " . ucwords($triggers['amber']) . "))",
			"(`qual_child` = '1' AND `qual_child_expiry_date` IS NOT NULL AND `qual_child_expiry_date` != '0000-00-00' AND `qual_child_expiry_date` <= DATE_ADD(UTC_TIMESTAMP(), INTERVAL " . ucwords($triggers['amber']) . "))",
			"(`qual_fsscrb` = '1' AND `qual_fsscrb_expiry_date` IS NOT NULL AND `qual_fsscrb_expiry_date` != '0000-00-00' AND `qual_fsscrb_expiry_date` <= DATE_ADD(UTC_TIMESTAMP(), INTERVAL " . ucwords($triggers['amber']) . "))",
			"(`qual_fsscrb` = '0' AND `qual_othercrb` = '1' AND `qual_othercrb_expiry_date` IS NOT NULL AND `qual_othercrb_expiry_date` != '0000-00-00' AND `qual_othercrb_expiry_date` <= DATE_ADD(UTC_TIMESTAMP(), INTERVAL " . ucwords($triggers['amber']) . "))"
		);

		if (count($where_or) > 0) {
			$where_or = '(' . implode(' OR ', $where_or) . ')';
		} else {
			$where_or = array();
		}

		$res = $this->db->from('staff')->where($where)->where($where_or, NULL, FALSE)->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get list of expiring items
				$expiring_items = array();

				$status = 'amber';

				if ($row->qual_first == 1 && $row->qual_first_not_required != 1 && !empty($row->qual_first_expiry_date) && strtotime($row->qual_first_expiry_date) <= strtotime('+' . $triggers['amber'])) {
					$expiring_items[] = 'First Aid';
					if (strtotime($row->qual_first_expiry_date) <= strtotime('+' . $triggers['red'])) {
						$status = 'red';
					}
				}

				if ($row->qual_child == 1 && $row->qual_child_not_required != 1 && !empty($row->qual_child_expiry_date) && strtotime($row->qual_child_expiry_date) <= strtotime('+' . $triggers['amber'])) {
					$expiring_items[] = 'Child Protection';
					if (strtotime($row->qual_child_expiry_date) <= strtotime('+' . $triggers['red'])) {
						$status = 'red';
					}
				}

				if ($row->qual_fsscrb == 1 && $row->qual_fsscrb_not_required != 1 && !empty($row->qual_fsscrb_expiry_date) && strtotime($row->qual_fsscrb_expiry_date) <= strtotime('+' . $triggers['amber'])) {
					$expiring_items[] = 'Company DBS';
					if (strtotime($row->qual_fsscrb_expiry_date) <= strtotime('+' . $triggers['red'])) {
						$status = 'red';
					}
				}

				if ($row->qual_othercrb == 1 && $row->qual_othercrb_not_required != 1 && !empty($row->qual_othercrb_expiry_date) && strtotime($row->qual_othercrb_expiry_date) <= strtotime('+' . $triggers['amber'])) {
					$expiring_items[] = 'Other DBS';
					if (strtotime($row->qual_othercrb_expiry_date) <= strtotime('+' . $triggers['red'])) {
						$status = 'red';
					}
				}

				if (count($expiring_items) > 0) {
					$data['items'][] = array(
						'text' => anchor('staff/quals/' . $row->staffID, $row->first . ' ' . $row->surname) . ' - ' . implode(', ', $expiring_items),
						'status' => $status
					);
				}

			}
		}

		return $data;
	}

	/**
	 * list of staff with missing mandatory qualifcations
	 * @return void
	 */
	private function staff_mandatory_required() {

		if (!$this->auth->has_features('staff_management')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_mandatory_required',
			'name' => 'Required Mandatory Qualifications',
			'short_name' => 'Mandatory Qualifications',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$staff_missing_quals = array();

		// get missing staff quals
		$where = array(
			'staff.active' => 1,
			'staff.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('staff')->where($where)->get();
		$brand_quals = [];
		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get list of missing items
				$missing_items = array();

				if ($row->qual_first != 1 && $row->qual_first_not_required != 1) {
					$missing_items[] = 'First Aid';
				}

				if ($row->qual_child != 1 && $row->qual_child_not_required != 1) {
					$missing_items[] = 'Child Protection';
				}

				if ($row->qual_fsscrb_not_required != 1) {
					if ($row->qual_fsscrb != 1) {
						$missing_items[] = 'Company DBS';
					}

					if (empty($row->qual_fsscrb_ref)) {
						$missing_items[] = 'Company DBS - Reference';
					}
				}

				// store if some missing quals
				if (count($missing_items) > 0) {
					if (!array_key_exists($row->staffID, $staff_missing_quals)) {
						$staff_missing_quals[$row->staffID] = array(
							'key' => $row->first . ' ' . $row->surname . ' ' . $row->staffID,
							'link' => anchor('staff/quals/' . $row->staffID, $row->first . ' ' . $row->surname),
							'missing_items' => array()
						);
					}

					foreach ($missing_items as $item) {
						$staff_missing_quals[$row->staffID]['missing_items'][] = $item;
					}
				}
			}
		}

		// get required cols
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$required_quals = $this->db->from('mandatory_quals')->where($where)->get();
		$required_quals_list = array();
		if ($required_quals->num_rows() > 0) {
			foreach ($required_quals->result() as $row) {
				$required_quals_list[$row->qualID] = $row->name;
			}
		}

		// check
		$where = array(
			'staff.active' => 1,
			'staff.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('staff.*, GROUP_CONCAT(' . $this->db->dbprefix('staff_quals_mandatory') . '.qualID SEPARATOR ",") AS qualIDs, GROUP_CONCAT(not_required.qualID SEPARATOR ",") AS qualIDs_not_required')
			->from('staff')
			->join('staff_quals_mandatory', 'staff.staffID = staff_quals_mandatory.staffID and staff_quals_mandatory.valid = 1', 'left')->join('staff_quals_mandatory as not_required', 'staff.staffID = not_required.staffID and not_required.not_required = 1', 'left')
			->where($where)->group_by('staff.staffID')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				$brand_quals = [];
				if ($row->brandID !== NULL) {
					$res = $this->db->from('brands_quals')->where(['brandID' => $row->brandID])->get();
					foreach ($res->result() as $qualRow) {
						$brand_quals[$qualRow->qualID] = $qualRow;
					}
				}

				$department_required_quals = $required_quals_list;
				if (count($brand_quals) > 0) {
					foreach ($department_required_quals as $key => $value) {
						if (!isset($brand_quals[$key])) {
							unset($department_required_quals[$key]);
						}
					}
				}

				// get list of missing items
				$missing_items = array();

				$qualIDs = explode(',', $row->qualIDs);
				$qualIDs_not_required = explode(',', $row->qualIDs_not_required);
				$qualIDs = array_merge($qualIDs, $qualIDs_not_required);
				if (count($department_required_quals) > 0) {
					foreach ($department_required_quals as $qualID => $label) {
						if (!in_array($qualID, $qualIDs)) {
							$missing_items[] = $label;
						}
					}
				}

				// store if some missing quals
				if (count($missing_items) > 0) {
					if (!array_key_exists($row->staffID, $staff_missing_quals)) {
						$staff_missing_quals[$row->staffID] = array(
							'key' => $row->first . ' ' . $row->surname . ' ' . $row->staffID,
							'link' => anchor('staff/quals/' . $row->staffID, $row->first . ' ' . $row->surname),
							'missing_items' => array()
						);
					}

					foreach ($missing_items as $item) {
						$staff_missing_quals[$row->staffID]['missing_items'][] = $item;
					}
				}
			}
		}

		// show alert
		if (count($staff_missing_quals) > 0) {
			foreach($staff_missing_quals as $staffID => $details) {
				sort($details['missing_items']);
				$data['items'][$details['key']] = array(
					'text' => $details['link'] . ' - ' . implode(', ', $details['missing_items']),
					'status' => 'red'
				);
			}
			ksort($data['items']);
		}

		return $data;
	}

	/**
	 * list of staff with expiring/expired additional qualifcations
	 * @return void
	 */
	private function staff_additional_expiring() {

		if (!$this->auth->has_features('staff_management')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_additional_expiring',
			'name' => 'Expired/Expiring Additional Qualifications',
			'short_name' => 'Expired/Expiring Additional Qualifications',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'active' => 1,
			'expiry_date IS NOT NULL' => NULL,
			'expiry_date <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'staff.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('staff.*, GROUP_CONCAT(' . $this->db->dbprefix('staff_quals') . '.name) as items, GROUP_CONCAT(' . $this->db->dbprefix('staff_quals') . '.expiry_date) as expiry_dates')->from('staff_quals')->join('staff', 'staff_quals.staffID = staff.staffID', 'inner')->where($where)->group_by('staff.staffID')->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				$status = 'amber';

				$expiry_dates = explode(",", $row->expiry_dates);
				if (count($expiry_dates) > 0) {
					foreach ($expiry_dates as $expiry) {
						if (strtotime(trim($expiry)) <= strtotime('+' . $triggers['red'])) {
							$status = 'red';
						}
					}
				}

				$data['items'][] = array(
					'text' => anchor('staff/quals/additional/' . $row->staffID, $row->first . ' ' . $row->surname) . ' - ' . str_replace(',', ', ', $row->items),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of staff with unconfirmed policies
	 * @return void
	 */
	private function staff_unconfirmed() {

		if (!$this->auth->has_features('resources')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_unconfirmed',
			'name' => 'Unconfirmed Policies',
			'short_name' => 'Unconfirmed Policies',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);


		// staff policies
		$where = array(
			'files.accountID' => $this->auth->user->accountID,
			'settings_resources.policies' => 1
		);
		$policies = $this->db->select('files.*')
			->from('files')
			->join('settings_resourcefile_map', 'files.attachmentID = settings_resourcefile_map.attachmentID', 'inner')
			->join('settings_resources', 'settings_resourcefile_map.resourceID = settings_resources.resourceID', 'inner')
			->where($where)
			->order_by('files.name asc')
			->group_by('files.attachmentID')
			->get();

		// if some policies
		if ($policies->num_rows() > 0) {
			$where = array(
				'active' => 1,
				'accountID' => $this->auth->user->accountID
			);

			$search_where = "(`accept_policies` IS NULL OR `accept_policies` = '0000-00-00 00:00:00')";

			$res = $this->db->from('staff')->where($where)->where($search_where, NULL, FALSE)->order_by('first asc, surname asc')->get();

			if ($res->num_rows() > 0) {
				foreach($res->result() as $row) {

					$data['items'][] = array(
						'text' => anchor('staff/edit/' . $row->staffID, $row->first . ' ' . $row->surname),
						'status' => 'red'
					);

				}
			}
		}

		return $data;
	}

	/**
	 * list of staff with upcoming availability exceptions
	 * @return void
	 */
	private function staff_availability_exceptions() {

		$data = array(
			'id' => 'staff_availability_exceptions',
			'name' => 'Upcoming Availability Exceptions (Holidays, etc)',
			'short_name' => 'Upcoming Availability Exceptions (Holidays, etc)',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'active' => 1,
			'from <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'to >' => mdate('%Y-%m-%d %H:%i:%s'),
			'staff_availability_exceptions.accountID' => $this->auth->user->accountID
		);

		$res = $this->db->select('staff_availability_exceptions.*, staff.first, staff.surname')->from('staff_availability_exceptions')->join('staff', 'staff_availability_exceptions.staffID = staff.staffID', 'inner')->where($where)->group_by('staff_availability_exceptions.exceptionsID')->order_by('staff_availability_exceptions.from ASC, staff_availability_exceptions.to asc, first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get status
				$status = 'amber';
				if (strtotime($row->from) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => anchor('staff/availability/' . $row->staffID . '/exceptions/edit/' . $row->exceptionsID, $row->first . ' ' . $row->surname) . ' - ' . mysql_to_uk_datetime($row->from) . ' to ' . mysql_to_uk_datetime($row->to) . ' (' . $row->reason . ')',
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of staff with if read timetable this week
	 * @return void
	 */
	private function staff_timetables() {

		if (!$this->auth->has_features(array('bookings_timetable', 'bookings_timetable_own', 'bookings_timetable_confirmation'))) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_timetables',
			'name' => 'Unread Timetables for Week ' . $this->weekNo,
			'short_name' => 'Unread Timetables',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'fulltimecoach'
			),
			'items' => array()
		);

		$where = array(
			'staff.active' => 1,
			'staff.non_delivery !=' => 1,
			'staff.accountID' => $this->auth->user->accountID
		);

		// if head coach, limit to people in their team
		if ($this->auth->user->department == 'headcoach') {
			$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}

		$res = $this->db->select('staff.staffID, staff.first, staff.surname, timetable_read.week, timetable_read.year')
		->from('staff')
		->join('timetable_read', 'staff.staffID = timetable_read.staffID and ' . $this->db->dbprefix('timetable_read') . '.week = ' . $this->weekNo . ' and ' . $this->db->dbprefix('timetable_read') . '.year = ' . $this->yearNo, 'left')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)->group_by('staff.staffID')->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {
				$status = 'unconfirm';
				$label = ucwords($status) . '?';
				$icon = 'green';

				if (empty($row->week) && empty($row->year)) {
					$status = 'confirm';
					$label = ucwords($status) . '?';
					$icon = 'red';
				}

				$data['items'][] = array(
					'text' => '<span class="' . $status . '">' . $row->first . ' ' . $row->surname . '</span> - ' . anchor('dashboard/timetable_read/' . $row->staffID . '/' . $status, $label, 'class="timetable_read '.($status=="confirm" ? " text-danger" : " text-success").'"'),
					'status' => $icon
				);
			}
		}

		return $data;
	}

	/**
	 * list of staff with upcoming probations
	 * @return void
	 */
	private function staff_probations() {

		if (!$this->auth->has_features('staff_management')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_probations',
			'name' => 'Probations Due',
			'short_name' => 'Probations Due',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'active' => 1,
			'employment_probation_date <=' =>  mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'employment_probation_complete !=' => 1,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get status
				$status = 'amber';
				if (strtotime($row->employment_probation_date) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => anchor('staff/recruitment/' . $row->staffID, $row->first . ' ' . $row->surname) . ' - ' . mysql_to_uk_date($row->employment_probation_date),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of staff with upcoming driving expiry dates
	 * @return void
	 */
	private function staff_driving() {

		if (!$this->auth->has_features('staff_management')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_driving',
			'name' => 'Driving Expiring/Missing Declaration',
			'short_name' => 'Driving Expiring/Missing Declaration',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);

		$where_or = array(
			"(driving_mot = 1 AND driving_mot_expiry <= '" . mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])) . "')",
			"(driving_insurance = 1 AND driving_insurance_expiry <= '" . mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])) . "')",
			"((driving_mot = 1 OR driving_insurance = 1) AND driving_declaration != 1)"
		);

		if (count($where_or) > 0) {
			$where_or = '(' . implode(' OR ', $where_or) . ')';
		} else {
			$where_or = array();
		}

		$res = $this->db->from('staff')->where($where)->where($where_or, NULL, FALSE)->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get status
				$status = 'amber';
				if ((strtotime($row->driving_mot_expiry) <= strtotime('+' . $triggers['red'])) || (strtotime($row->driving_insurance_expiry) <= strtotime('+' . $triggers['red'])) || (($row->driving_mot == 1 || $row->driving_insurance == 1) && $row->driving_declaration != 1)) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => anchor('staff/recruitment/' . $row->staffID, $row->first . ' ' . $row->surname),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of staff not published on site after 6 months
	 * @return void
	 */
	private function staff_website_due() {

		if (!$this->auth->has_features(array('staff_management', 'online_booking'))) {
			return FALSE;
		}

		// don't show if show onsite field turned off
		if (!show_field('onsite', get_fields('staff'))) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_website_due',
			'name' => 'Staff Due For Web Site',
			'short_name' => 'Staff Due For Web Site',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'active' => 1,
			'onsite !=' => 1,
			'employment_start_date <=' =>  mdate('%Y-%m-%d %H:%i:%s', strtotime('-' . $triggers['amber'])),
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get status
				$status = 'amber';
				if (strtotime($row->employment_start_date) <= strtotime('-' . $triggers['red'])) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => anchor('staff/edit/' . $row->staffID, $row->first . ' ' . $row->surname),
					'status' => $status
				);

			}
		}

		return $data;
	}

	/**
	 * list of staff with upcoming birthdays
	 * @return void
	 */
	private function staff_birthdays() {

		if (!$this->auth->has_features('dashboard_staff_birthdays')) {
			return FALSE;
		}

		$data = array(
			'id' => 'staff_birthdays',
			'name' => NULL,
			'short_name' => NULL,
			'icon' => 'user',
			'exclude' => array(),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);

		$where_and = array(
			"`dob` != 0",
			"(CONCAT(YEAR(CURRENT_DATE())+1,'-',DATE_FORMAT( dob, '%m-%d' )) BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL " . ucwords($triggers['amber']) . ") OR CONCAT(YEAR(CURRENT_DATE()),'-',DATE_FORMAT( dob, '%m-%d' )) BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL " . ucwords($triggers['amber']) . "))"
		);

		if (count($where_and) > 0) {
			$where_and = '(' . implode(' AND ', $where_and) . ')';
		} else {
			$where_and = array();
		}

		$res = $this->db->from('staff')->where($where)->where($where_and, NULL, FALSE)->order_by("MONTH(`dob`) ASC, DAY(`dob`) ASC")->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				if (empty($row->week) && empty($row->year)) {

					// get status
					$status = 'amber';
					if (strtotime(date('Y') . '-' . date('m-d', strtotime($row->dob))) <= strtotime('+' . $triggers['red'])) {
						$status = 'red';
					}

					$data['items'][] = array(
						'text' => $row->first . ' ' . $row->surname . ' (' . date('d/m', strtotime($row->dob)) . ')',
						'status' => $status
					);
				}
			}
		}

		return $data;
	}

	/**
	 * list of families with outstanding balances
	 * @return void
	 */
	private function families_outstanding() {

		if (!$this->auth->has_features(array('participants'))) {
			return FALSE;
		}

		$data = array(
			'id' => 'families_outstanding',
			'name' => 'Bookings with Outstanding Balances',
			'short_name' => 'Outstanding Balances',
			'icon' => 'user',
			'exclude' => array(
				'coaching',
				'headcoach',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		// flip values for use in strtotime
		if (substr($triggers['amber'], 0, 1) === '-') {
			$triggers['amber'] = '+' . substr($triggers['amber'], 1);
		} else {
			$triggers['amber'] = '-' . $triggers['amber'];
		}
		if (substr($triggers['red'], 0, 1) === '-') {
			$triggers['red'] = '+' . substr($triggers['red'], 1);
		} else {
			$triggers['red'] = '-' . $triggers['red'];
		}

		$where = array(
			'bookings_cart_sessions.accountID' => $this->auth->user->accountID,
			'bookings_cart.type' => 'booking',
			'bookings_blocks.endDate <=' =>  mdate('%Y-%m-%d %H:%i:%s', strtotime($triggers['amber']))
		);
		$having = array(
			'balance >' => 0
		);

		$res = $this->db->select('bookings_cart_sessions.cartID, bookings_cart.familyID, bookings_blocks.startDate, bookings_blocks.endDate, bookings.name as event, bookings_blocks.name as block, family_contacts.first_name, family_contacts.last_name, SUM(' . $this->db->dbprefix('bookings_cart_sessions') . '.balance) AS balance')
			->from('bookings_cart_sessions')
			->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
			->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
			->join('bookings_blocks', 'bookings_cart_sessions.blockID = bookings_blocks.blockID', 'inner')
			->join('family_contacts', 'family_contacts.contactID = bookings_cart.contactID', 'inner')
			->where($where)
			->group_by('bookings_cart_sessions.cartID, bookings_blocks.blockID')
			->having($having)
			->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate asc')
			->get();

		$outstanding_amount = 0;

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get status
				$status = 'amber';
				if (strtotime($row->startDate) <= strtotime($triggers['red'])) {
					$status = 'red';
				}

				$text = mysql_to_uk_date($row->startDate);
				if (strtotime($row->startDate) < strtotime($row->endDate)) {
					$text .= ' to ' . mysql_to_uk_date($row->endDate);
				}
				$text .= ' - ' . anchor('participants/bookings/view/' . $row->cartID, $row->block) . ' (' . $row->event . ')';
				$text .= ' - ' . currency_symbol() . $row->balance;
				$text .= ' - ' . anchor('participants/bookings/' . $row->familyID, $row->first_name . ' ' . $row->last_name);

				$data['items'][] = array(
					'text' => $text,
					'status' => $status
				);

				$outstanding_amount += $row->balance;
			}
		}

		$data['override_count'] = currency_symbol() . number_format($outstanding_amount, 2);

		return $data;
	}

	/**
	 * list of expired or missing safety docs
	 * @return void
	 */
	private function safety_docs($type = NULL) {

		if (!in_array($type, array('risk', 'school_inductions', 'camp_inductions'))) {
			return FALSE;
		}

		$data = array(
			'id' => 'safety_' . $type,
			'name' => 'Expired or Missing',
			'short_name' => NULL,
			'icon' => 'book',
			'exclude' => array(
				'coaching',
				'fulltimecoach'
			),
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		switch ($type) {
			case 'risk':
				$name = 'Risk Assessments';
				$type = 'risk assessment';
				break;
			case 'school_inductions':
				$name = 'School Inductions';
				$type = 'school induction';
				break;
			case 'camp_inductions':
				$name = 'Event/Project Induction';
				$type = 'camp induction';
				break;
		}

		$data['name'] .= ' ' . $name;
		$data['short_name'] = $name;

		$where = array(
			'bookings.endDate >' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+' . $triggers['amber'])),
			'bookings_blocks.provisional !=' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		$where_custom = ' (' . $this->db->dbprefix('orgs_safety') . '.type = ' . $this->db->escape($type) . ' OR ' . $this->db->dbprefix('orgs_safety') . '.type IS NULL)';
		$where_custom .= ' AND (' . $this->db->dbprefix('orgs_safety') . '.expiry < ' . $this->db->escape(mdate('%Y-%m-%d %H:%i:%s')) . ' OR ' . $this->db->dbprefix('orgs_safety') . '.expiry IS NULL)';

		// if event/project inductions, limit to events or projects
		if ($type == 'camp induction') {
			$where_custom .= ' AND (' . $this->db->dbprefix('bookings.type') . ' = ' . $this->db->escape('event') . ' OR ' . $this->db->dbprefix('bookings.project') . ' = ' . $this->db->escape(1) . ')';
		} else if ($type == 'school induction') {
			$where_custom .= ' AND (' . $this->db->dbprefix('bookings.type') . ' = ' . $this->db->escape('booking') . ' AND ' . $this->db->dbprefix('bookings.project') . ' = ' . $this->db->escape(0) . ')';
		}

		if (intval($this->input->get('brand')) > 0) {
			$where['orgs_safety.brandID'] = $this->input->get('brand');
		}

		$res = $this->db->select('orgs_safety.docID, orgs_safety.renewed, orgs_addresses.*, orgs.name as org,
			orgs_safety.expiry, bookings_lessons.lessonID, brands.colour, orgs_safety.brandID')
			->from('orgs_addresses')
			->join('orgs_safety', 'orgs_safety.addressID = orgs_addresses.addressID and ' . $this->db->dbprefix('orgs_safety') . '.type = \'' . $type .'\'', 'left outer')
			->join('bookings_lessons', 'orgs_addresses.addressID = bookings_lessons.addressID', 'inner')
			->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('orgs', 'orgs_addresses.orgID = orgs.orgID', 'inner')
			->join('brands', 'brands.brandID = orgs_safety.brandID', 'left')
			->where($where)
			->where($where_custom, NULL, FALSE)
			->group_by('orgs_addresses.addressID')
			->order_by('address1 asc, address2 asc, address3 asc, org asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// if renewed, skip
				if ($row->renewed == 1) {
					continue;
				}

				// work out address
				$address = NULL;
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
					$address = implode(', ', $address_parts);
				}

				if (!empty($row->docID)) {
					if ($row->colour) {
						$text = anchor('customers/safety/jumpto/' . $row->docID, $address, ['style' => 'color:' . $row->colour]);
					} else {
						$text = anchor('customers/safety/jumpto/' . $row->docID, $address);
					}
				} else {
					$text = anchor('customers/safety/' . $row->orgID, $address);
				}

				$text .= ' (' . $row->org . ')';

				if($row->expiry != NULL) {
					$text .= " - <span style=\"color:red;\">" .  mysql_to_uk_date($row->expiry) . "</span>";
				}

				// get status
				$status = 'amber';
				if (empty($row->expiry) || strtotime($row->expiry) <= strtotime('+' . $triggers['red'])) {
					$status = 'red';
				}

				$data['items'][] = array(
					'text' => $text,
					'status' => $status,
					'color' => $row->colour
				);
			}
		}

		return $data;
	}

	/**
	 * risk assessments
	 * @return void
	 */
	private function safety_risk() {
		return $this->safety_docs('risk');
	}

	/**
	 * school inductions
	 * @return void
	 */
	private function safety_school_inductions() {
		return $this->safety_docs('school_inductions');
	}

	/**
	 * camp inductions
	 * @return void
	 */
	private function safety_camp_inductions() {
		return $this->safety_docs('camp_inductions');
	}

	/**
	 * list of unread safety documents
	 * @return void
	 */
	private function safety_unread($only_own = FALSE) {

		if (!$this->auth->has_features(array('safety'))) {
			return FALSE;
		}

		$data = array(
			'id' => 'safety_unread',
			'name' => 'Unread Safety Documents',
			'short_name' => NULL,
			'icon' => 'book',
			'exclude' => array(
				'coaching',
				'fulltimecoach'
			),
			'items' => array()
		);

		if ($only_own == TRUE) {
			$data['id'] .= '_own';
			$data['name'] .= ' (Own)';
		}

		$staff_where = array(
			'staff.active' => 1,
			'staff.accountID' => $this->auth->user->accountID
		);

		// if head coach, limit to people in their team, if not just viewing own
		if ($this->auth->user->department == 'headcoach' && $only_own !== TRUE) {
			$staff_where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}

		// if only own, limit to self
		if ($only_own == TRUE) {
			$staff_where['staff.staffID'] = $this->auth->user->staffID;
			$data['exclude'] = array();
		}

		$res = $this->db->select("staff.*")->from('staff')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($staff_where)->get();

		if ($res->num_rows() > 0) {

			$where = array(
				'orgs_safety.expiry >=' => mdate('%Y-%m-%d %H:%i:%s'),
				'orgs_safety.renewed !=' => 1,
				'orgs_safety.accountID' => $this->auth->user->accountID
			);

			foreach($res->result() as $row) {

				// get upcoming addressess
				$upcoming_addresses = $this->crm_library->get_upcoming_addresses($row->staffID);

				// only need to check if teaching coming up
				if (is_array($upcoming_addresses) && count($upcoming_addresses) > 0) {

					$where_custom = ' (' . $this->db->dbprefix('orgs_safety_read') . '.date IS NULL OR (' . $this->db->dbprefix('orgs_safety_read') . '.outdated = 1 AND ' . $this->db->dbprefix('orgs_safety_read') . '.readID = (SELECT `readID` FROM ' . $this->db->dbprefix('orgs_safety_read') . ' AS `read` WHERE `read`.`staffID` = ' . $this->db->escape($row->staffID) . ' AND `read`.`docID` = ' . $this->db->dbprefix('orgs_safety_read') . '.`docID` ORDER BY `read`.`date` DESC LIMIT 1)))';

					$res_docs = $this->db->select('orgs_safety.docID, orgs_safety.type as doc_type, orgs_addresses.*, orgs_safety_read.outdated, orgs_safety.details')->from('orgs_safety')->join('orgs_safety_read', 'orgs_safety.docID = orgs_safety_read.docID and ' . $this->db->dbprefix('orgs_safety_read') . '.staffID= \'' . $row->staffID .'\'', 'left outer')->join('orgs_addresses', 'orgs_addresses.addressID = orgs_safety.addressID', 'inner')->join('orgs', 'orgs.orgID = orgs_addresses.orgID', 'inner')->where($where)->where($where_custom, NULL, FALSE)->where_in('orgs_safety.addressID', $upcoming_addresses)->get();

					if ($res_docs->num_rows() > 0) {

						// if only own, show doc list
						if ($only_own === TRUE) {

							foreach ($res_docs->result() as $row_doc) {

								$label = NULL;

								$address_parts = array();
								if (!empty($row_doc->address1)) {
									$address_parts[] = $row_doc->address1;
								}
								if (!empty($row_doc->address2)) {
									$address_parts[] = $row_doc->address2;
								}
								if (!empty($row_doc->address3)) {
									$address_parts[] = $row_doc->address3;
								}
								if (!empty($row_doc->town)) {
									$address_parts[] = $row_doc->town;
								}
								if (!empty($row_doc->county)) {
									$address_parts[] = $row_doc->county;
								}
								if (!empty($row_doc->postcode)) {
									$address_parts[] = $row_doc->postcode;
								}
								if (count($address_parts) > 0) {
									$label .= implode(", ", $address_parts);
								}

								$row_doc->details = @unserialize($row_doc->details);
								if (!is_array($row_doc->details)) {
									$row_doc->details = array();
								}

								if (array_key_exists("location", $row_doc->details) && !empty($row_doc->details['location'])) {
									$label .= " (" . $row_doc->details['location'] . ")";
								}

								$text = anchor('customers/safety/view/' . $row_doc->docID, ucwords($row_doc->doc_type), 'target="_blank"') . ' - ' . $label;

								if ($row_doc->outdated == 1) {
									$text .= ' (Updated)';
								}

								$data['items'][] = array(
									'text' => $text,
									'status' => 'red'
								);

							}

						} else {

							$text = anchor('staff/safety/' . $row->staffID, $row->first . ' ' . $row->surname);

							$data['items'][] = array(
								'text' => $text,
								'status' => 'red'
							);

						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * unread safety documents (own)
	 * @return void
	 */
	private function safety_unread_own() {
		return $this->safety_unread(TRUE);
	}

	/**
	 * list of late equipment - orgs
	 * @return void
	 */
	private function equipment_late_orgs() {
		return $this->equipment_late('orgs');
	}

	/**
	 * list of late equipment - contacts
	 * @return void
	 */
	private function equipment_late_contacts() {
		return $this->equipment_late('contacts');
	}

	/**
	 * list of late equipment - children
	 * @return void
	 */
	private function equipment_late_children() {
		return $this->equipment_late('children');
	}

	/**
	 * list of late equipment
	 * @return void
	 */
	private function equipment_late($type = 'staff') {

		if (!$this->auth->has_features('equipment')) {
			return FALSE;
		}

		$data = array(
			'id' => 'equipment_late',
			'name' => 'Late Equipment',
			'short_name' => 'Late Equipment',
			'icon' => 'user',
			'items' => array()
		);

		$triggers = $this->settings_library->get_dashboard_trigger($data['id']);

		if ($type != 'staff') {
			$data['exclude'] = array(
				'coaching',
				'fulltimecoach'
			);
		}

		switch ($type) {
			case 'staff':
			default:
				$data['name'] .= ' (Staff)';
				$data['short_name'] .= ' (Staff)';
				$type_field = 'staff';
				$name_field = 'staff_label';
				break;
			case 'orgs':
				$data['name'] .= ' (' . $this->settings_library->get_label('customers') . ')';
				$data['short_name'] .= ' (' . $this->settings_library->get_label('customers') . ')';
				$type_field = 'org';
				$name_field = 'org_label';
				break;
			case 'contacts':
				$data['name'] .= ' (Parents/Contacts)';
				$data['short_name'] .= ' (Parents/Contacts)';
				$type_field = 'contact';
				$name_field = 'contact_label';
				break;
			case 'children':
				$data['name'] .= ' (Children)';
				$data['short_name'] .= ' (Children)';
				$type_field = 'child';
				$name_field = 'child_label';
				break;
		}

		$where = array(
			'dateIn <' => mdate('%Y-%m-%d %H:%i:%s', strtotime('-' . $triggers['amber'])),
			'status' => 1,
			'equipment_bookings.type' => $type_field,
			'equipment_bookings.accountID' => $this->auth->user->accountID
		);

		// if coaching or full time coach, only show their outstanding equipment
		if ($type == 'staff' && in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			$where['equipment_bookings.staffID'] = $this->auth->user->staffID;
		}

		// if head coach, limit to people in their team
		if ($type == 'staff' && $this->auth->user->department == 'headcoach') {
			$where_custom = '(' . $this->db->dbprefix('staff_recruitment_approvers') . '.approverID = ' . $this->db->escape($this->auth->user->staffID) . ' OR ' . $this->db->dbprefix('equipment_bookings') . '.staffID = ' . $this->db->escape($this->auth->user->staffID) . ')';
		} else {
			// custom where not required
			$where_custom = '1 = 1';
		}

		$res = $this->db->select('equipment_bookings.*, equipment.name, CONCAT_WS(\' \', ' . $this->db->dbprefix('staff') . '.first, ' . $this->db->dbprefix('staff') . '.surname) AS staff_label, orgs.name AS org_label, CONCAT_WS(\' \', ' . $this->db->dbprefix('family_contacts') . '.first_name, ' . $this->db->dbprefix('family_contacts') . '.last_name) AS contact_label, CONCAT_WS(\' \', ' . $this->db->dbprefix('family_children') . '.first_name, ' . $this->db->dbprefix('family_children') . '.last_name) AS child_label')->from('equipment_bookings')->
		join('equipment', 'equipment_bookings.equipmentID = equipment.equipmentID', 'inner')
		->join('staff', 'equipment_bookings.staffID = staff.staffID', 'left')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->join('orgs', 'equipment_bookings.orgID = orgs.orgID', 'left')
		->join('family_contacts', 'equipment_bookings.contactID = family_contacts.contactID', 'left')
		->join('family_children', 'equipment_bookings.childID = family_children.childID', 'left')
		->where($where)->where($where_custom, NULL, FALSE)->group_by('equipment_bookings.bookingID')->order_by('dateIn asc')->get();

		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {

				// get status
				$status = 'amber';
				if (strtotime($row->dateIn) <= strtotime('-' . $triggers['red'])) {
					$status = 'red';
				}

				$text = mysql_to_uk_date($row->dateIn);
				// no links for coaching
				if ($this->auth->user->department == 'coaching') {
					$text .= ' - ' . $row->name . ' (' . $row->quantity . ')';
				} else {
					$text .= ' - ' . anchor('equipment/bookings/edit/' . $row->bookingID, $row->name . ' (' . $row->quantity . ')');
				}

				// no links for headcoach, fulltime and coaching
				if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach', 'headcoach')) || $type != 'staff') {
					$text .= ' - ' . $row->$name_field;
				} else {
					$text .= ' - ' . anchor('staff/equipment/' . $row->staffID, $row->$name_field);
				}

				$data['items'][] = array(
					'text' => $text,
					'status' => $status
				);
			}
		}

		return $data;
	}

	/**
	 * accept staff policies
	 * @return void
	 */
	public function acceptpolicies() {

		if (!$this->auth->has_features(array('resources'))) {
			return FALSE;
		}

		$data = array(
			'accept_policies' => mdate('%Y-%m-%d %H:%i:%s')
		);

		$where = array(
			'staffID' => $this->auth->user->staffID,
			'accountID' => $this->auth->user->accountID
		);

		$this->db->update('staff', $data, $where);

		// all ok
		if ($this->db->affected_rows() == 1) {

			$this->session->set_flashdata('success', 'Your policies have now been confirmed.');

			redirect('/');

			return TRUE;

		} else {

			show_404();

		}

		return FALSE;

	}

	/**
	 * toggle read timetable
	 * @return void
	 */
	public function timetable_read($staffID = NULL, $status = 'confirm') {

		if (!$this->auth->has_features(array('bookings_timetable', 'bookings_timetable_own', 'bookings_timetable_confirmation'))) {
			return FALSE;
		}

		// check params
		if (empty($staffID) || !in_array($status, array('confirm', 'unconfirm'))) {
			show_404();
		}

		// check permissions
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		// check staff exists
		$where = array(
			'staff.staffID' => $staffID,
			'staff.active' => 1,
			'staff.accountID' => $this->auth->user->accountID
		);

		// if head coach, limit to people in their team
		if ($this->auth->user->department == 'headcoach') {
			$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}

		$res = $this->db->select("staff.*")->from('staff')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		// check if already exists
		$where = array(
			'staffID' => $staffID,
			'week' => $this->weekNo,
			'year' => $this->yearNo,
			'accountID' => $this->auth->user->accountID
		);

		// if confirming
		if ($status == 'confirm') {

			$res = $this->db->from('timetable_read')->where($where)->limit(1)->get();

			if ($res->num_rows() == 0) {
				// not confirmed, confirm
				$data = $where;
				$data['byID'] = $this->auth->user->staffID;
				$data['fromdash'] = 1;
				$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

				$res = $this->db->insert('timetable_read', $data);

				echo 'CONFIRMED';

				return TRUE;
			}

		} else {

			// remove
			$res = $this->db->delete('timetable_read', $where);

			if ($this->db->affected_rows() == 1) {
				echo 'UNCONFIRMED';
				return TRUE;
			}
		}

		show_404();

		return FALSE;

	}

	/**
	 * availability checker
	 * @return mixed
	 */
	public function availability() {

		// check permission
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach')) || !$this->auth->has_features('dashboard_availability')) {
			show_404();
		}

		// allow posts only
		if (!$this->input->post()) {
			show_404();
		}

		$this->load->library('form_validation');

		// set validation rules
		$this->form_validation->set_rules('date', 'Date', 'trim|xss_clean|required|callback_check_date');
		$this->form_validation->set_rules('startTimeH', 'Start Time - Hour', 'trim|xss_clean|required');
		$this->form_validation->set_rules('startTimeM', 'Start Time - Minutes', 'trim|xss_clean|required');
		$this->form_validation->set_rules('endTimeH', 'End Time', 'trim|xss_clean|required|callback_lesson_datetime');
		$this->form_validation->set_rules('endTimeM', 'End Time - Minutes', 'trim|xss_clean|required');
		$this->form_validation->set_rules('postcode', 'Postcode', 'trim|xss_clean|callback_check_postcode');

		$items = array();

		if ($this->form_validation->run() == FALSE) {
			$errors = $this->form_validation->error_array();

			foreach ($errors as $error) {
				$items[] = array(
					'status' => 'red',
					'text' => 'Error: ' . $error
				);
			}
		} else {
			// work out start and end time, add 30 mins either side for travel
			$startTime = date('H:i', strtotime('-30 minutes', strtotime(set_value('startTimeH') . ':' . set_value('startTimeM'))));
			$endTime = date('H:i', strtotime('+30 minutes', strtotime(set_value('endTimeH') . ':' . set_value('endTimeM'))));
			$endTimeArr = explode(":", $endTime);
			if($endTimeArr[0] == "0"){
				$endTime = "24:".$endTimeArr[1];
			}

			// reformat date
			$date = uk_to_mysql_date(set_value('date'));

			// get active staff
			$where = array(
				'staff.active' => 1,
				'staff.accountID' => $this->auth->user->accountID,
				'staff.non_delivery !=' => 1
			);

			$res = $this->db->select('staff.*, staff_addresses.postcode as postcode')->from('staff')->join('staff_addresses', 'staff.staffID = staff_addresses.staffID AND type = "main"', 'left')->where($where)->order_by('first asc, surname asc')->get();

			if ($res->num_rows() > 0) {

				foreach ($res->result() as $row) {

					$conflicts = array();

					// assume travelling to/from home
					$prev_postcode = $row->postcode;
					$next_postcode = $row->postcode;
					$prev_time = NULL;
					$next_time = NULL;

					// check for conflicts with another lesson
					$where = array(
						'bookings_lessons.day' => date('l', strtotime($date)),
						'bookings_lessons_staff.startDate <=' => $date,
						'bookings_lessons_staff.endDate >=' => $date,
						'bookings_lessons.startTime <=' => $endTime,
						'bookings_lessons.endTime >=' => $startTime,
						'bookings_lessons_staff.staffID' => $row->staffID,
						'bookings_lessons.accountID' => $this->auth->user->accountID
					);

					$where_custom = $this->db->dbprefix('bookings_lessons_staff') . '.startDate >= SUBDATE(' . $this->db->escape($date) . ',  INTERVAL DATEDIFF(' . $this->db->dbprefix('bookings_lessons_staff') . '.`endDate`, ' . $this->db->dbprefix('bookings_lessons_staff') . '.`startDate`) DAY)';
					$where_custom .= ' AND ' . $this->db->dbprefix('bookings_lessons_staff') . '.endDate <= ADDDATE(' . $this->db->escape($date) . ',  INTERVAL DATEDIFF(' . $this->db->dbprefix('bookings_lessons_staff') . '.`endDate`, ' . $this->db->dbprefix('bookings_lessons_staff') . '.`startDate`) DAY)';
					$where_custom .= ' AND ' . $this->db->dbprefix('bookings_lessons') . '.startTime >= SUBTIME(CAST(' . $this->db->escape($startTime) . ' AS TIME),  SUBTIME(' . $this->db->dbprefix('bookings_lessons') . '.`endTime`, ' . $this->db->dbprefix('bookings_lessons') . '.`startTime`))';
					$where_custom .= ' AND ' . $this->db->dbprefix('bookings_lessons') . '.endTime <= ADDTIME(CAST(' . $this->db->escape($endTime) . ' AS TIME),  SUBTIME(' . $this->db->dbprefix('bookings_lessons') . '.`endTime`, ' . $this->db->dbprefix('bookings_lessons') . '.`startTime`))';

					$res_check = $this->db->select('bookings.bookingID, bookings_lessons.lessonID, bookings_lessons_staff.startDate as staffStartDate, bookings_lessons_staff.endDate as staffEndDate')->from('bookings_lessons')->join('bookings', 'bookings.bookingID = bookings_lessons.bookingID', 'inner')->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID')->where($where)->where($where_custom, NULL, FALSE)->get();

					if ($res_check->num_rows() > 0) {

						foreach ($res_check->result() as $row_check) {

							// add conflict
							$conflicts[$row_check->lessonID] = array(
								'bookingID' => $row_check->bookingID,
								'lessonID' => $row_check->lessonID,
								'exception' => FALSE,
								'dates' => mysql_to_uk_date($row_check->staffStartDate)
							);

							if (strtotime($row_check->staffEndDate) > strtotime($row_check->staffStartDate)) {
								$conflicts[$row_check->lessonID]['dates'] .= ' to ' . mysql_to_uk_date($row_check->staffEndDate);
							}

							// check for cancelled session exception, if so, ok to carry on
							$where = array(
								'lessonID' => $row_check->lessonID,
								'date' => $date,
								'type' => 'cancellation',
								'accountID' => $this->auth->user->accountID
							);

							$res_exception_check = $this->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

							if ($res_exception_check->num_rows() > 0) {
								if (array_key_exists($row_check->lessonID, $conflicts)) {
									unset($conflicts[$row_check->lessonID]);
								}
							}

							// check for staffchange session exception, if so, ok to carry on
							$where['type'] = 'staffchange';
							$where['fromID'] = $row->staffID;

							$res_exception_check = $this->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

							if ($res_exception_check->num_rows() > 0) {
								if (array_key_exists($row_check->lessonID, $conflicts)) {
									unset($conflicts[$row_check->lessonID]);
								}
							}

						}

					}

					// if no conflicts, check to see if staff been changed to another lesson
					if (count($conflicts) == 0) {

						$where = array(
							'bookings_lessons_exceptions.staffID' => $row->staffID,
							'bookings_lessons_exceptions.type' => 'staffchange',
							'bookings_lessons_exceptions.date' => $date,
							'bookings_lessons.day' => date('l', strtotime($date)),
							'bookings_lessons.startTime <' => $endTime,
							'bookings_lessons.endTime >' => $startTime,
							'bookings_lessons_exceptions.accountID' => $this->auth->user->accountID
						);

						$where_custom = $this->db->dbprefix('bookings_lessons') . '.startTime > SUBTIME(CAST(' . $this->db->escape($startTime) . ' AS TIME),  SUBTIME(' . $this->db->dbprefix('bookings_lessons') . '.`endTime`, ' . $this->db->dbprefix('bookings_lessons') . '.`startTime`))';
						$where_custom .= ' AND ' . $this->db->dbprefix('bookings_lessons') . '.endTime < ADDTIME(CAST(' . $this->db->escape($endTime) . ' AS TIME),  SUBTIME(' . $this->db->dbprefix('bookings_lessons') . '.`endTime`, ' . $this->db->dbprefix('bookings_lessons') . '.`startTime`))';

						$res_check = $this->db->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID')->where($where)->where($where_custom, NULL, FALSE)->get();

						if ($res_check->num_rows() > 0) {
							foreach ($res_check->result() as $row_check) {
								// add conflict
								$conflicts[$row_check->lessonID] = array(
									'bookingID' => $row_check->bookingID,
									'lessonID' => $row_check->lessonID,
									'exception' => TRUE,
									'dates' => mysql_to_uk_date($row_check->date)
								);
							}
						}
					}

					// if no conflicts, continue
					if (count($conflicts) == 0) {

						// determine week number from shift pattern
						$week =  $this->crm_library->week_number_from_shift_pattern($date);

						$endTimeArr = explode(":", $endTime);
						if($endTimeArr[0] == "24"){
							$endTime = date('H:i', strtotime(set_value('endTimeH') . ':' . set_value('endTimeM')));
						}

						// check availability
						$where = array(
							'staffID' => $row->staffID,
							'week' => $week,
							'day' => strtolower(date('l', strtotime($date))),
							'from <=' => $startTime,
							'to >=' => $endTime,
							'accountID' => $this->auth->user->accountID
						);

						$result_check = $this->db->from('staff_availability')->where($where)->limit(1)->get();

						if ($result_check->num_rows() == 1) {

							// check availability exceptions
							$where = array(
								'staffID' => $row->staffID,
								'accountID' => $this->auth->user->accountID
							);

							$where_custom = '((`from` < \'' . $date . ' ' . $startTime . '\' AND `to` > \'' . $date . ' ' . $startTime . '\')';
							$where_custom .= ' OR (`from` < \'' . $date . ' ' . $endTime . '\' AND `to` > \'' . $date . ' ' . $endTime . '\'))';

							$result_check = $this->db->from('staff_availability_exceptions')->where($where)->where($where_custom, NULL, FALSE)->limit(1)->get();

							if ($result_check->num_rows() == 0) {
								$status = 'green';
								$label = 'Available';
							} else {
								$status = 'red';
								$label = 'Unavailable (Availability Exception)';
							}

						} else {
							$status = 'red';
							$label = 'Unavailable';
						}

					} else {
						$status = 'red';
						$label = 'Unavailable (Conflicting Session';
						if (count($conflicts) > 1) {
							$label .= 's';
						}
						$label .= ')';
					}

					// process conflicts
					$conflicts_html = NULL;
					if (count($conflicts) > 0) {
						$conflicts_html = "<ul>";
						foreach ($conflicts as $conflict) {
							$conflicts_html .= "<li>";
							if (!empty($conflict['bookingID'])) {
								// get booking
								$where = array(
									'bookingID' => $conflict['bookingID'],
									'accountID' => $this->auth->user->accountID
								);
								$res_booking = $this->db->from('bookings')->where($where)->get();
								if ($res_booking->num_rows() > 0) {
									foreach ($res_booking->result() as $booking_info) {}

									if ($booking_info->type == "event") {
										$conflicts_html .= $booking_info->name;
									} else {
										// get booking
										$where = array(
											'orgID' => $booking_info->orgID,
											'accountID' => $this->auth->user->accountID
										);
										$res_org = $this->db->from('orgs')->where($where)->get();
										if ($res_org->num_rows() > 0) {
											foreach ($res_org->result() as $org_info) {}
											$conflicts_html .= $org_info->name;
										}
									}
									$conflicts_html .= " - " . $conflict['dates'];
								}
							}
							if (!empty($conflict['lessonID'])) {
								// get lesson
								$where = array(
									'lessonID' => $conflict['lessonID'],
									'accountID' => $this->auth->user->accountID
								);
								$res_lesson = $this->db->from('bookings_lessons')->where($where)->get();
								if ($res_lesson->num_rows() > 0) {
									foreach ($res_lesson->result() as $lesson_info) {}
									$conflicts_html .= " (" . substr($lesson_info->startTime,0,5) . "-" . substr($lesson_info->endTime,0,5) . ")";
								}
							}
							if ($conflict['exception'] == TRUE) {
								$conflicts_html .= " (As Exception)";
							}
							$conflicts_html .= "</li>";
						}
						$conflicts_html .= "</ul>";
					}

					// get prev lesson
					$prev_lesson = $this->crm_library->get_prev_lesson(uk_to_mysql_date(set_value('date')), set_value('startTimeH') . ':' . set_value('startTimeM'), $row->staffID);
					if ($prev_lesson !== FALSE) {
						$prev_time = $prev_lesson->endTime;
						$prev_postcode = $prev_lesson->postcode;
					}

					// get next lesson
					$next_lesson = $this->crm_library->get_next_lesson(uk_to_mysql_date(set_value('date')), set_value('endTimeH') . ':' . set_value('endTimeM'), $row->staffID);
					if ($next_lesson !== FALSE) {
						$next_time = $next_lesson->startTime;
						$next_postcode = $next_lesson->postcode;
					}

					$items[] = array(
						'text' => $row->first . ' ' . $row->surname.' - <span class="text-'. $status . '">' . $label . '</span>' . $conflicts_html . '<div class="travel"><span class="travel_from"></span><span class="travel_to"></span></div>',
						'status' => $status,
						'prev_postcode' => $prev_postcode,
						'prev_time' => $prev_time,
						'next_postcode' => $next_postcode,
						'next_time' => $next_time
					);

				}

			}
		}

		// get a list of sort columns and their data to pass to array_multisort
		$sort = array();
		foreach($items as $k => $v) {
			$sort['status'][$k] = $v['status'];
			$sort['text'][$k] = $v['text'];
		}
		// sort by status, then first name (text)
		array_multisort($sort['status'], SORT_ASC, $sort['text'], SORT_ASC, $items);

		// prepare data for view
		$data = array(
			'title' => 'Availability Checker',
			'name' => 'Results',
			'icon' => 'calendar',
			'items' => $items,
			'type' => 'availability',
			'id' => 'availability',
			'postcode' => set_value('postcode'),
			'date' => set_value('date'),
			'startTime' => set_value('startTimeH') . ':' . set_value('startTimeM'),
			'endTime' => set_value('endTimeH') . ':' . set_value('endTimeM')
		);

		// load view
		$this->crm_view('dashboard/items', $data);
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
	 * remove account/staff override
	 * @return mixed
	 */
	public function removeoverride() {

		// decide where to redirect
		$redirect_to = '/accounts';

		// if user overridden, return to staff
		if ($this->auth->user_overridden === TRUE) {
			$redirect_to = '/staff';
			$this->session->unset_userdata('user_id_override');
		} else {
			$this->session->unset_userdata('user_id_override');
			$this->session->unset_userdata('account_id_override');
		}
		$this->session->unset_userdata('search-timetable');

		// redirect
		redirect($redirect_to);
	}

	/**
	 * format postcode and check is correct
	 * @param  string $postcode
	 * @return mixed
	 */
	public function check_postcode($postcode) {

		// allow empty postcodes
		if (empty($postcode)) {
			return TRUE;
		}

		return $this->crm_library->check_postcode($postcode);

	}

	public function checkout() {
		// check for feature
		if (!$this->auth->has_features('lesson_checkins')) {
			return FALSE;
		}

		if (in_array($this->crm_library->get_checkin_status(), ['not_checked_in', 'checked_out'])) {
			return FALSE;
		}

		if ($this->input->method() == 'post') {
			$current = date('Y-m-d');
			$data = [
				'bookings_lessons_checkins.date' => $current,
				'bookings_lessons_checkins.staffID' => $this->auth->user->staffID,
				'bookings_lessons_checkins.accountID' => $this->auth->user->accountID,
				'not_checked_in' => 0
			];

			$query = $this->db->select('bookings_lessons_checkins.*,
			bookings_lessons_staff.startTime,
			bookings_lessons_staff.endTime')
				->from('bookings_lessons_checkins')
				->join('bookings_lessons_staff', 'bookings_lessons_checkins.lessonID = bookings_lessons_staff.lessonID', 'left')
				->where($data)
				->order_by('logID desc')
				->limit(1)
				->get();

			$checkin = null;
			foreach ($query->result() as $row) {
				$checkin = $row;
			}

			$minutes = $this->settings_library->get('email_not_checkout_staff_threshold_time', $this->auth->user->accountID);

			//allow to checkout 10 more minutes
			$minutes += 10;

			//staff not able to checkout if in thresholdtime
			if (time() - strtotime(date('Y-m-d') . ' ' . $checkin->endTime) > $minutes*60) {
				return false;
			}

			$data = array(
				'accountID' => $this->auth->user->accountID,
				'staffID' => $this->auth->user->staffID,
				'lessonID' => $checkin->lessonID,
				'date' => date('Y-m-d'),
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->insert('bookings_lessons_checkouts', $data);

			$this->db->delete('bookings_lessons_checkins', [
				'staffID' => $this->auth->user->staffID,
				'logID>' => $checkin->logID
			]);
		}

		echo 'OK';
		return TRUE;
	}

	/**
	 * session check in
	 * @return mixed
	 */
	public function checkin() {

		// check for feature
		if (!$this->auth->has_features('lesson_checkins')) {
			return FALSE;
		}

		$current_lesson = $this->crm_library->get_current_lesson();

		if (!$current_lesson) {
			return FALSE;
		}

		if ($current_lesson->provisional == 1) {
			return FALSE;
		}

		//not allow to checkin if checked in already or checked out in current lesson
		if (in_array($this->crm_library->get_current_checkin_status($current_lesson), ['checked_in', 'checked_out'])) {
			return FALSE;
		}

		if ($this->input->post()) {
			$query = $this->db->select(
				'bookings_lessons_staff.startTime,
				 bookings_lessons_staff.endTime,
				 bookings_lessons.lessonID')
				->from('bookings_lessons_staff')
				->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')
				->where([
					'bookings_lessons.accountID' => $this->auth->user->accountID,
					'bookings_lessons.addressID' => $current_lesson->addressID,
					'bookings_lessons.day' => $current_lesson->day,
					'bookings_lessons_staff.staffID' => $this->auth->user->staffID,
					'bookings_lessons_staff.startDate <=' => mdate('%Y-%m-%d'),
					'bookings_lessons_staff.endDate >=' => mdate('%Y-%m-%d')
				])->get();

			$lessons = [];
			foreach ($query->result() as $row) {
				$lessons[] = $row;
			}

			function cmp($a, $b) {
				return ($a->endTime <= $b->startTime) ? -1 : 1;
			}

			usort($lessons, 'cmp');

			$data = array(
				'accountID' => $this->auth->user->accountID,
				'staffID' => $this->auth->user->staffID,
				'lessonID' => $current_lesson->lessonID,
				'date' => date('Y-m-d'),
				'lat' => $this->input->post('lat'),
				'lng' => $this->input->post('lng'),
				'accuracy' => $this->input->post('accuracy'),
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if (!empty($data['lat']) && !empty($data['lng'])) {
				$distance = 0;
				$res = $this->db->select('bookings.type as booking_type, CONCAT_WS(\',\', ST_X(address.location), ST_Y(address.location)) as lesson_coords, CONCAT_WS(\',\', ST_X(event_address.location), ST_Y(event_address.location)) as event_coords')
					->from('bookings_lessons')
					->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
					->join('orgs_addresses as address', 'bookings_lessons.addressID = address.addressID', 'left')
					->join('orgs_addresses as event_address', 'bookings.addressID = event_address.addressID', 'left')
					->where([
						'bookings_lessons.accountID' => $this->auth->user->accountID,
						'bookings_lessons.lessonID' => $current_lesson->lessonID
					])
					->get();

				if ($res->num_rows() > 0) {
					$geokit = new Geokit\Math();

					foreach ($res->result() as $item) {
						$coords_var = 'booking_coords';
						if ($item->booking_type == 'event') {
							$coords_var = 'event_coords';
						}
						if (!empty($item->$coords_var)) {
							$coords = explode(",", $item->$coords_var);

							$distance = $geokit->distanceHaversine([
								$data['lat'], $data['lng']
							], $coords);
							$distance = $distance->meters();
						}
					}
				}

				if ($distance > 1000) {
					$to = [];
					if (!empty($this->settings_library->get('email', $this->auth->user->accountID))) {
						$to[] = $this->settings_library->get('email', $this->auth->user->accountID);
					}

					if (!empty($this->settings_library->get('email_from', $this->auth->user->accountID))) {
						$to[] = $this->settings_library->get('email_from', $this->auth->user->accountID);
					}

					if (!empty($to)) {

						$smart_tags = array(
							'staff_name' => $this->auth->user->first . ' ' . $this->auth->user->surname
						);

						$subject = $this->settings_library->get('email_checkin_wrong_location_account_subject', $this->auth->user->accountID);
						$email_html = $this->settings_library->get('email_checkin_wrong_location_account_body', $this->auth->user->accountID);


						// replace smart tags in email
						foreach ($smart_tags as $key => $value) {
							$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
							$email_html = str_replace('{' . $key . '}', $value, $email_html);
						}

						// get html email and convert to plain text
						$this->load->helper('html2text');
						$html2text = new \Html2Text\Html2Text($email_html);
						$email_plain = $html2text->get_text();

						foreach ($to as $recipient) {
							$this->crm_library->send_email($recipient, $subject, $email_html, array(), TRUE, $this->auth->user->accountID);
						}
					}
				}

				$this->db->insert('bookings_lessons_checkins', $data);
				$this->db->delete('bookings_lessons_checkins', [
					'staffID' => $this->auth->user->staffID,
					'lessonID' => $current_lesson->lessonID,
					'not_checked_in' => 1
				]);
			}

			$endTime = $current_lesson->staff_end_time;
			foreach ($lessons as $lesson) {
				if (strtotime(date('Y-m-d') . ' ' . $lesson->startTime) >= strtotime(date('Y-m-d') . ' ' . $current_lesson->staff_end_time)) {
					if (strtotime(date('Y-m-d') . ' ' . $lesson->startTime) - strtotime(date('Y-m-d') . ' ' . $endTime) <= 3600) {
						$data['lessonID'] = $lesson->lessonID;
						$this->db->insert('bookings_lessons_checkins', $data);
						$endTime = $lesson->endTime;
					}
				}
			}

			echo 'OK';
			return TRUE;
		}

		echo 'FAIL';
		return TRUE;

	}

}

/* End of file dashboard.php */
/* Location: ./application/controllers/dashboard.php */
