<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property Auth $auth
 * @property Reports_library $reports_library
 * @property CI_DB $db
 */
class Project_code extends MY_Controller {

	/**
	 * Payroll constructor.
	 */
	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports'));
		// load library
		$this->load->library('reports_library');
	}

	public function index($action = FALSE) {

		$page_base = 'reports/project_code';
		$section = 'reports';
		$current_page = 'project_code';
		$title = 'Project Code Costs Report';
		$icon = 'book';
		$buttons = ' <a class="btn btn-primary" href="' . site_url('reports/project_code/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'project_code' => NULL,
			'search' => NULL
		);

		$export = FALSE;

		if ($action == 'export') {
			$export = TRUE;
		}

		$is_search = FALSE;
		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['project_code'] = set_value('search_project_code');

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			$is_search = TRUE;
			$this->pagination_library->is_search();
		} else if (($export == TRUE || $this->crm_library->last_segment() == 'recall') && is_array($this->session->userdata('search-reports'))) {

			foreach ($this->session->userdata('search-reports') as $key => $value) {
				$search_fields[$key] = $value;
			}
		}

		$project_codes = $this->crm_library->get_project_codes();

		// if dates empty, add default
		if (empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-1 week'));
		}
		if (empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		if ($is_search) {
			$this->session->set_userdata('search-reports', $search_fields);
		}

		$data = $this->reports_library->calc_payroll($search_fields, 'project_code');

		$data = [
			'page_base' => $page_base,
			'section' => $section,
			'current_page' => $current_page,
			'search_fields' => $search_fields,
			'title' => $title,
			'icon' => $icon,
			'buttons' => $buttons,
			'total' => $data,
			'project_codes' => $project_codes
		];

		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');
			$this->load->view('reports/project-code-export', $data);
		} else {
			$this->crm_view('reports/project-code', $data);
		}
	}
}
