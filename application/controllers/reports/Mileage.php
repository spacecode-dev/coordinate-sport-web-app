<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mileage extends MY_Controller {

	private $categories;

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports'));

		// load library
		$this->load->library('reports_library');
		$this->load->model('Staff/StaffModel');
		$this->load->model('Settings/Brands');
		$this->load->model('Settings/ActivitiesModel');
	}
	
	public function index($action = FALSE) {
		
		// set defaults
		$icon = 'book';
		$current_page = 'mileage';
		$section = 'reports';
		$page_base = 'reports/mileage';
		$title = 'Mileage Report';
		$buttons = ' <a class="btn btn-primary" href="' . site_url('reports/mileage/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$export = FALSE;
		$period = 'week';
		
		// check if exporting
		if ($action == 'export') {
			$export = TRUE;
		} else {
			switch ($action) {
				case 'week':
				case 'month':
				case 'quarter':
					$period = $action;
					break;
			}
		}
		
		$mileage_activate_fuel_cards = 0;
		$activate_fuel_card = NULL;
		$where = array("accountID" => $this->auth->user->accountID,
		"key" => 'mileage_activate_fuel_cards');
		$query = $this->db->from("accounts_settings")->where($where)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$mileage_activate_fuel_cards = $result->value;
			}
		}
		
		if($mileage_activate_fuel_cards == 1)
			$activate_fuel_card = 1;
		
		// set up search
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => NULL,
			'filter_by_mode_of_transport' => 1,
			'filter_by_activate_fuel_card' => $activate_fuel_card,
			'search' => NULL,
		);
		$is_search = FALSE;
		
		
		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_filter_by_mode_of_transport', 'Default mode of transport', 'trim|xss_clean');
			if($mileage_activate_fuel_cards == 1)
				$this->form_validation->set_rules('search_filter_by_activate_fuel_card', 'Activate Fuel Card', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['filter_by_mode_of_transport'] = set_value('filter_by_mode_of_transport');
			if($mileage_activate_fuel_cards == 1)
				$search_fields['filter_by_activate_fuel_card'] = set_value('filter_by_activate_fuel_card');
			
			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			$is_search = TRUE;

		} else if (($export == TRUE || $this->crm_library->last_segment() == 'recall') && is_array($this->session->userdata('search-reports'))) {
			foreach ($this->session->userdata('search-reports') as $key => $value) {
				$search_fields[$key] = $value;
			}

		}
		
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
		
		// if from after to, reset
		if (strtotime(uk_to_mysql_date($search_fields['date_from'])) > strtotime(uk_to_mysql_date($search_fields['date_to']))) {
			$search_fields['date_from'] = date('d/m/Y', strtotime($offset, strtotime(uk_to_mysql_date($search_fields['date_to']))));
		}
		$searchStaffFields = [];
		
		if (isset($is_search) && $is_search === TRUE) {

			// store search fields
			$this->session->set_userdata('search-reports', $search_fields);

		}

		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		if ($search_fields['staff_id'] != '') {
			$searchStaffFields['staffID'] = (int)($search_fields['staff_id']);
		}
		
		$mileage_data = $this->reports_library->calc_mileage($search_fields);
	
		$searchStaffFields['accountID'] = $this->auth->user->accountID;
		
		$staff = $this->StaffModel->search($searchStaffFields);
		
		$searchStaffFields1 = array();
		$searchStaffFields1['accountID'] = $this->auth->user->accountID;
		
		$staff_All = $this->StaffModel->search($searchStaffFields1);
		$personal_mileage_cost = array();
		if($mileage_activate_fuel_cards == 1){
			foreach($staff as $row){
				$p = $this->reports_library->calc_personal_mileage_cost($search_fields, $row->staffID);
				$personal_mileage_cost[$row->staffID] = $p;
			}
		}
		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}
		
		$where = array("accountID" => $this->auth->user->accountID);
		
		$mileage_mode = $this->db->select("*")->from("mileage")->where($where)->get();
		
		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'staff' => $staff,
			'mileage_data' => $mileage_data,
			'mileage_activate_fuel_cards' => $mileage_activate_fuel_cards,
			'personal_mileage_cost' => $personal_mileage_cost,
			'mileage_mode' => $mileage_mode,
			'staff_All' => $staff_All,
			'page_base' => $page_base,
			'success' => $success,
			'error' => $error,
			'info' => $info,
		);
		
		// load view
		if ($export === TRUE) {
			$table = $this->load->view('reports/mileage-table', $data, true);

			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
			$spreadsheet = $reader->loadFromString($table);

			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');

			// style for header
			$styleArray = [
				'font' => [
					'bold' => true,
				]
			];

			$worksheet = $spreadsheet->getActiveSheet();

			$highestColumn = $worksheet->getHighestColumn();

			$lastHeaderIndex = 2;

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

			header('Content-Type: application/vnd.ms-excel"');
			header('Content-Disposition: attachment; filename=mileage-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.xls');
			$writer->save('php://output');
			exit();
		} else {
			$this->crm_view('reports/mileage', $data);
		}
		
	}
	
}