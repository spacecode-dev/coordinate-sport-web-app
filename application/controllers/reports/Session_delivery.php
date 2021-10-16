<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Session_delivery extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports'));

		if (!$this->auth->has_features('session_delivery')) {
			show_404();
		}

		$this->load->library('reports_library');
		$this->load->model('Staff/StaffModel');
		$this->load->model('Orgs/OrgsContactsModel');
		$this->load->model('Settings/ActivitiesModel');
		$this->load->model('Settings/LessonTypesModel');
		$this->load->model('Settings/RegionsModel');
		$this->load->model('Settings/AreasModel');
		$this->load->model('Settings/Brands');
	}

	public function index($action = FALSE) {
		// set defaults
		$icon = 'book';
		$current_page = 'session_delivery';
		$section = 'reports';
		$page_base = 'reports/session_delivery';
		$title = 'Session Delivery Report';
		$buttons = '<a class="btn btn-primary" href="' . site_url('reports/session_delivery/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$export = FALSE;

		if ($action == 'export') {
			$export = true;
		}

		$search_fields = [
			'date_from' => date('d/m/Y', strtotime("- 1 month")),
			'date_to' => date('d/m/Y'),
			'staff_id' => null,
			'org' => null,
			'class_size' => null,
			'name' => null,
			'postcode' => null,
			'main_contact' => null,
			'activity_id' => null,
			'type_id' => null,
			'region_id' => null,
			'area_id' => null,
			'day' => null,
			'staffing_type' => null,
			'brand_id' => null,
			'search' => null
		];

		$str = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : NULL;

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		if ($this->input->get('search')) {
			$buttons = '<a class="btn btn-primary" href="' . site_url('reports/session_delivery/export?' . $str) . '" target="_blank"><i class="far fa-save"></i> Export</a>';
			parse_str($str, $url_query_array);

			$this->load->library('form_validation');
			$this->form_validation->set_data($url_query_array);

			$this->form_validation->set_rules('date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('date_from', 'Date To', 'trim|xss_clean');
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

			if( $this->form_validation->run() == FALSE ) {
				$errors = $this->form_validation->error_array();
			} else {
				$search_fields['date_from'] = $this->input->get('date_from');
				$search_fields['date_to'] = $this->input->get('date_to');
				$search_fields['staff_id'] = $this->input->get('search_staff_id');
				$search_fields['type_id'] = $this->input->get('search_type_id');
				$search_fields['name'] = $this->input->get('search_name');
				$search_fields['activity_id'] = $this->input->get('search_activity_id');
				$search_fields['day'] = $this->input->get('search_day');
				$search_fields['staffing_type'] = $this->input->get('search_staffing_type');
				$search_fields['brand_id'] = $this->input->get('search_brand_id');
				$search_fields['search'] = $this->input->get('search');
				$search_fields['postcode'] = $this->input->get('search_postcode');
				$search_fields['org'] = $this->input->get('search_org');
				$search_fields['class_size'] = $this->input->get('search_class_size');
				$search_fields['region_id'] = $this->input->get('search_region_id');
				$search_fields['area_id'] = $this->input->get('search_area_id');
				$search_fields['main_contact'] = $this->input->get('search_main_contact');
				$is_search = TRUE;
			}
		}

		$where['accountID'] = $this->auth->user->accountID;
		if (!empty($search_fields['date_from'])) {
			$where['date_from'] = uk_to_mysql_date($search_fields['date_from']);
		}

		if (!empty($search_fields['date_to'])) {
			$where['date_to'] = uk_to_mysql_date($search_fields['date_to']);
		}

		foreach ($search_fields as $field => $value) {
			if (!in_array($field, ['date_from', 'date_to'])) {
				if (!empty($value)) {
					$where[$field] = $value;
				}
			}
		}

		$lessons = $this->reports_library->getSessionDeliveryData($where);

		$staff = $this->StaffModel->getList($this->auth->user->accountID, 1);

		$contacts = $this->OrgsContactsModel->getMainList($this->auth->user->accountID);

		$activities = $this->ActivitiesModel->getList($this->auth->user->accountID, 1);

		$lessonTypes = $this->LessonTypesModel->getList($this->auth->user->accountID, 1);

		$regions = $this->RegionsModel->getList($this->auth->user->accountID);

		$areas = $this->AreasModel->getList($this->auth->user->accountID);

		$brands = $this->Brands->getList($this->auth->user->accountID, 1, 'name asc');

		$days = array(
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday',
			'sunday'
		);

		$data = array(
			'brands' => $brands,
			'days' => $days,
			'areas' => $areas,
			'regions' => $regions,
			'lesson_types' => $lessonTypes,
			'activities' => $activities,
			'contacts' => $contacts,
			'staff' => $staff,
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'search_fields' => $search_fields,
			'lessons' => $lessons
		);

		if ($export === TRUE) {
			$table = $this->load->view('reports/session-delivery-table', $data, true);
			createXlsDocument($table, 'session-delivery-' . uk_to_mysql_date($search_fields['date_from']) .
				'-to-' . uk_to_mysql_date($search_fields['date_to']), 1);
		} else {
			$this->crm_view('reports/session-delivery', $data);
		}
	}

}

/* End of file projects.php */
/* Location: ./application/controllers/reports/projects.php */
