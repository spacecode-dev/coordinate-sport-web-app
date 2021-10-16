<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property Auth $auth
 * @property Reports_library $reports_library
 * @property CI_DB $db
 * @property PayrollReport $PayrollReport
 */
class Payroll extends MY_Controller
{

	/**
	 * Payroll constructor.
	 */
	public function __construct()
	{
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports'));
		// load library
		$this->load->library('reports_library');
		$this->load->library('staff_library');
		$this->load->model('Reports/PayrollReport');
	}

	public function index($action = FALSE)
	{
		$page_base = 'reports/payroll';
		$section = 'reports';
		$current_page = 'payroll';
		$title = 'Payroll Report';
		$icon = 'book';
		$buttons = ' <a class="btn btn-primary" href="' . site_url('reports/payroll/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => NULL,
			'staff_is_active' => NULL,
			'type_id' => NULL,
			'search' => NULL
		);

		$export = FALSE;

		if ($action == 'export') {
			$export = TRUE;
			$this->pagination_library->is_search();
		}

		$is_search = FALSE;

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type_id', 'Session Type', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['department'] = set_value('search_department');
			$search_fields['type_id'] = set_value('search_type_id');

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

		// set default dates
		if (empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-1 month'));
		}
		if (empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		if ($is_search) {
			$this->session->set_userdata('search-reports', $search_fields);
		}

		// get all staff
		$staff = $this->reports_library->get_staff(
			$this->auth->user->accountID,
			null,
			true,
			true
		);

		$quals = $this->reports_library->get_qualifications($this->auth->user->accountID);

		// get payroll data
		$payroll_data = $this->reports_library->calc_payroll(
			$search_fields,
			'payroll',
			$staff
		);
		
		// filter only staff with payroll data
		$staff_with_payroll_data = [];
		foreach ($staff as $row) {
			if (!array_key_exists($row->staffID, $payroll_data)) {
				continue;
			}
			$personal_mileage_cost = $this->reports_library->calc_personal_mileage_cost(
				$search_fields,
				$row->staffID
			);
			$row->personal_mileage_cost = $personal_mileage_cost["overall_personal_cost"];
			$row->personal_mileage = $personal_mileage_cost["overall_mileage"];
			$staff_with_payroll_data[$row->staffID] = $row;
		}
		// if not exporting
		if ($export !== TRUE) {
			// calc pagination
			$this->pagination_library->calc(count($staff_with_payroll_data));
			// slice staff with payroll data depending on pagination
			$offset = ($this->pagination_library->current_page - 1) * $this->pagination_library->amount;
			$staff_with_payroll_data = array_slice($staff_with_payroll_data, $offset, $this->pagination_library->amount);
		}

		$sessionTypes = $this->settings_library->getSessionTypes($this->auth->user->accountID);

		$allSessionTypes = $sessionTypes;

		if (!empty($search_fields['type_id'])) {
			$sessionTypes = [];
			$sessionTypes[] = $this->settings_library->getSessionTypeInfo($search_fields['type_id']);
		}

		// get staff for search
		$searchStaffFields = [
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		];
		$staff_list = $this->StaffModel->search($searchStaffFields, [], NULL, NULL, 'first asc, surname asc');
		
		//Check Mileage Section in accounts
		$mileage_section = $this->auth->has_features("mileage");
		
		$data = [
			'page_base' => $page_base,
			'section' => $section,
			'current_page' => $current_page,
			'search_fields' => $search_fields,
			'staff_list' => $staff_list,
			'staff_with_payroll_data' => $staff_with_payroll_data,
			'quals' => $quals,
			'title' => $title,
			'icon' => $icon,
			'payroll_data' => $payroll_data,
			'buttons' => $buttons,
			'session_types' => $sessionTypes,
			'all_session_types' => $allSessionTypes,
			'mileage_section' => $mileage_section,
			'tab' => 'report'
		];
		if ($export === TRUE) {
			$table = $this->load->view('reports/payroll-table', $data, true);

			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
			$spreadsheet = $reader->loadFromString($table);

			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

			// style for header
			$styleArray = [
				'font' => [
					'bold' => true,
				]
			];

			$worksheet = $spreadsheet->getActiveSheet();

			$highestColumn = $worksheet->getHighestColumn();

			$lastHeaderIndex = 3;
			if (!empty($search_fields['type_id'])) {
				$lastHeaderIndex = 2;
			}

			$worksheet->getStyle('A1:' . $highestColumn . $lastHeaderIndex)
				->applyFromArray($styleArray);


			// style for borders
			$styleArray = array(
				'borders' => array(
					'outline' => array(
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
					),
				),
			);
			foreach ($worksheet->getRowIterator() as $row) {
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
				foreach ($cellIterator as $cell) {
					$worksheet->getStyle($cell->getCoordinate())
						->applyFromArray($styleArray);
				}
			}

			// reset cursor to top left
			$worksheet->setSelectedCell('A1');
			
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="payroll-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.xlsx"');
			header('Cache-Control: max-age=0');
			$writer->save('php://output');
			exit();
		} else {
			$this->PayrollReport->log($this->auth->user, 'payroll', json_encode($search_fields));
			$this->crm_view('reports/payroll', $data);
		}
	}

	public function history()
	{

		if (!in_array($this->auth->user->department, array('directors', 'management'))) {
			show_404();
		}

		$page_base = 'reports/payroll-history';
		$section = 'reports';
		$current_page = 'payroll';
		$title = 'Payroll Report History';
		$icon = 'book';
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => NULL,
			'search' => NULL
		);

		$str = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : NULL;
		// if search
		if ($str !== null) {
			parse_str($str, $url_query_array);
			// load libraries
			$this->load->library('form_validation');
			$this->form_validation->set_data($url_query_array);

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = $this->input->get('search_date_from');
			$search_fields['date_to'] = $this->input->get('search_date_to');
			$search_fields['staff_id'] = $this->input->get('search_staff_id');

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}
		}

		// if dates empty, add default
		if (empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime('-1 week'));
		}
		if (empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		$pagination = $this->pagination_library->calc_by_url($this->PayrollReport->getLogs('payroll', [
			'staff_id' => $search_fields['staff_id'],
			'from' => strtotime(str_replace('/', '-', $search_fields['date_from'])),
			'to' => strtotime(str_replace('/', '-', $search_fields['date_to'])) + 86399,
			'accountId' => $this->auth->user->accountID
		], true));

		$historyData = $this->PayrollReport->getLogs('payroll', [
			'staff_id' => $search_fields['staff_id'],
			'from' => strtotime(str_replace('/', '-', $search_fields['date_from'])),
			'to' => strtotime(str_replace('/', '-', $search_fields['date_to'])) + 86399,
			'accountId' => $this->auth->user->accountID
		], false, $this->pagination_library->amount, $this->pagination_library->start);

		$staff = $this->staff_library->getAllStaff($this->auth->user->accountID, true);

		$this->session->set_userdata('search-reports', $search_fields);

		$data = [
			'page_base' => $page_base,
			'section' => $section,
			'current_page' => $current_page,
			'search_fields' => $search_fields,
			'staff_list' => $staff,
			'title' => $title,
			'icon' => $icon,
			'tab' => 'history',
			'history_data' => $historyData
		];

		$this->crm_view('reports/payroll-history', $data);
	}
}
