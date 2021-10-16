<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Projectcodes extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings', 'projectcode'));

		$this->load->model('Settings/ProjectCodesModel');
	}

	/**
	 * show list of project codes
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'projectcodes';
		$page_base = 'settings/projectcodes';
		$section = 'settings';
		$title = 'Project Codes';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/projectcodes/new') . '"><i class="far fa-plus"></i> Create New</a> <a class="btn" href="' . site_url('settings/projectcodes/import') . '"><i class="far fa-upload"></i> Import</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings'
		);

		// set where
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'search' => NULL,
			'code' => NULL,
			'active' => 1
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_code', 'Project Code', 'trim|xss_clean');
			$this->form_validation->set_rules('active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['code'] = set_value('search_code');
			$search_fields['active'] = set_value('active');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-projectcodes'))) {

			foreach ($this->session->userdata('search-projectcodes') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		$search_like = [];
		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-projectcodes', $search_fields);


			if ($search_fields['code'] != '') {
				$search_like['code'] = $search_fields['code'];
			}
		}

		if ($search_fields['active'] != '') {
			$where['active'] = $search_fields['active'];
		}

		$items = $this->ProjectCodesModel->search($where, $search_like);

		// workout pagination
		$total_items = count($items);

		$pagination = $this->pagination_library->calc($total_items);

		$items = $this->ProjectCodesModel->search($where, $search_like, $this->pagination_library->amount, $this->pagination_library->start, 'code asc');
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
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'project_codes' => $items,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/projectcodes', $data);
	}

	public function active($codeId, $active) {
		$active = $active == 'yes' ? 1 : 0;
		$codeId = (int)$codeId;

		$this->ProjectCodesModel->update($codeId, [
			'active' => $active
		], $this->auth->user->accountID);

		echo 'OK';
	}

	public function updateAjax() {
		$codes = set_value('codes');

		$disableCodes = [];
		$enableCodes = [];
		if (is_array($codes) && !empty($codes)) {
			foreach ($codes as $id => $value) {
				$active = $value == 'true' ? 1 : 0;
				if ($active) {
					$enableCodes[] = $id;
				} else {
					$disableCodes[] = $id;
				}
			}
		}

		$this->ProjectCodesModel->update($disableCodes, [
			'active' => 0
		]);

		$this->ProjectCodesModel->update($enableCodes, [
			'active' => 1
		]);
	}

	/**
	 * edit a project code
	 * @param  int $codeID
	 * @return void
	 */
	public function edit($codeID = NULL)
	{

		$code_info = new stdClass();

		// check if editing
		if ($codeID != NULL) {

			// check if numeric
			if (!ctype_digit($codeID)) {
				show_404();
			}

			$code_info = $this->ProjectCodesModel->getById($codeID);

			// no match
			if (!$code_info) {
				show_404();
			}
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Project Code';
		if ($codeID != NULL) {
			$submit_to = 'settings/projectcodes/edit/' . $codeID;
			$title = $code_info->code;
		} else {
			$submit_to = 'settings/projectcodes/new/';
		}
		$return_to = 'settings/projectcodes';
		$icon = 'cog';
		$current_page = 'projectcodes';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/projectcodes' => 'Project Codes'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('code', 'Project Code', 'trim|xss_clean|required');
			$this->form_validation->set_rules('desc', 'Description', 'trim|xss_clean');
			$this->form_validation->set_rules('active', 'Active', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'code' => set_value('code'),
					'desc' => set_value('desc'),
					'active' => set_value('active'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($codeID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($codeID == NULL) {
						$codeID = $this->ProjectCodesModel->createNew($data);

						$just_added = TRUE;
					} else {
						$this->ProjectCodesModel->update($codeID, $data, $this->auth->user->accountID);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if (isset($just_added)) {
							$this->session->set_flashdata('success', set_value('code') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('code') . ' has been updated successfully.');
						}

						redirect($return_to);

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


		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'code_info' => $code_info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/projectcode', $data);
	}

	/**
	 * import data
	 * @return void
	 */
	public function import() {

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Import Names';
		$submit_to = 'settings/projectcodes/import';
		$return_to = 'settings/projectcodes';
		$icon = 'user';
		$current_page = 'projectcodes';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/projectcodes' => 'Project Codes'
		);

		// if posted
		if ($this->input->post()) {

			// do the import
			$config = array(
				'upload_path' => sys_get_temp_dir(),
				'allowed_types' => 'xlsx',
				'max_size' => '6144'
			);

			$this->load->library('upload', $config);

			// attempt upload
			if ($this->upload->do_upload('excel_file'))	{

				// upload ok
				$upload_data = $this->upload->data();
				$imported = 0;

				if ($upload_data === NULL) {
					$errors[] = 'A valid file is required';
				} else {
					try {
						$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($upload_data['full_path']); // identify the file
						$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType); // creating the reader
						$objReader->setReadDataOnly(true);
						$objPHPExcel = $objReader->load($upload_data['full_path']); // loading the file

						// get worksheet dimensions
						$sheet = $objPHPExcel->getSheet(0); // selecting sheet 0
						$highestRow = $sheet->getHighestRow(); // getting number of rows
						$highestColumn = $sheet->getHighestColumn(); // getting number of columns

						// loop through each row of the worksheet in turn, skip first
						for ($row = 2; $row <= $highestRow; $row++) {

							// read a row of data into an array
							$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
							$data = $rowData[0];

							// require at least first cell (code)
							if (!isset($data[0]) || empty($data[0])) {
								continue;
							}

							// add participants
							$db_data = array(
								'code' => trim($data[0]),
								'byID' => $this->auth->user->staffID,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);

							// check for desc
							if (isset($data[1]) && !empty($data[1])) {
								$db_data['desc'] = $data[1];
							}

							$this->db->insert('project_codes', $db_data);
							if ($this->db->affected_rows() > 0) {
								$imported++;
							}
						}
						if ($imported == 0) {
							$errors[] = 'No project codes to import';
						} else {
							$this->session->set_flashdata('success', $imported . ' project codes(s) imported');
							redirect($return_to);
							return TRUE;
						}
					} catch (Exception $e) {
   						$errors[] = 'File could not be read' . $e->getMessage();
   					}
				}

				// delete tmp file
				@unlink($upload_data['full_path']);

			} else {
				$errors[] = trim(strip_tags($this->upload->display_errors()));
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
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'breadcrumb_levels' => $breadcrumb_levels
		);

		// load view
		$this->crm_view('settings/projectcodes-import', $data);
	}

	/**
	 * delete a project code
	 * @param  int $codeID
	 * @return mixed
	 */
	public function remove($codeID = NULL) {

		// check params
		if (empty($codeID)) {
			show_404();
		}

		$where = array(
			'codeID' => $codeID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('project_codes')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$code_info = $row;

			// all ok, delete
			$query = $this->db->delete('project_codes', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $code_info->code . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $code_info->code . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/projectcodes';

			redirect($redirect_to);
		}
	}
}

/* End of file projectcodes.php */
/* Location: ./application/controllers/settings/projectcodes.php */
