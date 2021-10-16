<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dataconflicts extends MY_Controller {

	public function __construct() {
		// directors and dpo only
		parent::__construct(FALSE, array(), array(), array('export'));
		
		if($this->auth->user->department != 'directors'){
			$where = array(
				'accountID' => $this->auth->user->accountID,
				'key' => 'data_protection_officer'
			);
			$dataprotection = array();
			$res = $this->db->from('accounts_settings')->where($where)->get();
			if($res->num_rows() > 0){
				foreach($res->result() as $result){
					$dataprotection = explode(",",$result->value);
				}
			}
			if(!in_array($this->auth->user->staffID, $dataprotection)){
				show_403();
				return FALSE;
			}
		}
	}

	/**
	 * export data
	 * @return void
	 */
	public function index(){

		$valid_fields = array(
			'child_first' => array(
				'name' => "Child First Name",
				'sql' => 'c.first_name'
			),
			'child_last' => array(
				'name' => "Child Last Name",
				'sql' => 'c.last_name'
			),
			'dob' => array(
				'name' => "Date of Birth",
				'sql' => 'c.dob'
			),
			'orgID' => array(
				'name' => "School",
				'sql' => 'c.orgID'
			),
			'parent_title' => array(
				'name' => "Parent Title",
				'sql' => 'p.title'
			),
			'parent_first' => array(
				'name' => "Parent First Name",
				'sql' => 'p.first_name'
			),
			'parent_last' => array(
				'name' => "Parent Last Name",
				'sql' => 'p.last_name'
			),
			'postcode' => array(
				'name' => "Post Code",
				'sql' => 'p.postcode'
			),
			'county' => array(
				'name' => localise('county'),
				'sql' => 'p.county'
			),
			'mobile' => array(
				'name' => "Mobile",
				'sql' => 'p.mobile'
			),
			'phone' => array(
				'name' => "Other Phone",
				'sql' => 'p.phone'
			),
			'workPhone' => array(
				'name' => "Work Phone",
				'sql' => 'p.workPhone'
			),
			'email' => array(
				'name' => "Email",
				'sql' => 'p.email'
			),
		);

		// set defaults
		$icon = 'cog';
		$current_page = 'dataconflicts';
		$page_base = 'dataconflicts';
		$section = 'export';
		$title = 'Data Conflicts';
		$buttons = '';
		$res = FALSE;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array();

		// set where
		$where = array(
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'field1' => NULL,
			'field2' => NULL,
			'field3' => NULL,
			'message' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_field1', 'Field 1', 'trim|xss_clean');
			$this->form_validation->set_rules('search_field2', 'Field 2', 'trim|xss_clean');
			$this->form_validation->set_rules('search_field3', 'Field 3', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['field1'] = set_value('search_field1');
			$search_fields['field2'] = set_value('search_field2');
			$search_fields['field3'] = set_value('search_field3');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-family-tools'))) {

			foreach ($this->session->userdata('search-family-tools') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-family-tools', $search_fields);

			if ($search_fields['field1'] != '' && array_key_exists($search_fields['field1'], $valid_fields)) {
				$search_where[] = $valid_fields[$search_fields['field1']]['sql'];;
			}

			if ($search_fields['field2'] != '' && array_key_exists($search_fields['field2'], $valid_fields)) {
				$search_where[] = $valid_fields[$search_fields['field2']]['sql'];;
			}

			if ($search_fields['field3'] != '' && array_key_exists($search_fields['field3'], $valid_fields)) {
				$search_where[] = $valid_fields[$search_fields['field3']]['sql'];;
			}

			if (count($search_where) == 0) {
				$error = 'Please select at least one field to compare';
			} else {
				$search_where = ' GROUP BY CONCAT(' . implode(', ', $search_where) . ')';

				// run query
				$sql = "SELECT COUNT(DISTINCT c.`childID`) AS cnt, GROUP_CONCAT(c.`childID` SEPARATOR ',') AS duplicate_ids, p.contactID, c.childID, p.familyID, p.title AS parent_title, p.first_name AS parent_first, p.last_name AS parent_last, c.first_name AS child_first, c.last_name AS child_last, c.orgID, p.postcode, p.county, p.phone, p.mobile, p.workPhone, p.email, c.dob FROM `" . $this->db->dbprefix("family_children") . "` AS c LEFT OUTER JOIN `" . $this->db->dbprefix("family_contacts") . "` AS p ON p.familyID = c.familyID WHERE c.`accountID` = '" . $this->auth->user->accountID . "'{$search_where} HAVING cnt >1 ORDER BY `cnt` DESC, `parent_first` ASC, `parent_last` ASC, `child_first` ASC, `child_last` ASC";

				$res = $this->db->query($sql);

				// workout pagination
				$total_items = $res->num_rows();

				$pagination = $this->pagination_library->calc($total_items);

				// run query again, but limited
				$res = $this->db->query($sql . " LIMIT " . $this->pagination_library->start . ',' . $this->pagination_library->amount);
			}

		}
		
		//get list dataprotection officers
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'key' => 'data_protection_officer'
		);
		$dataprotection = array();
		$query = $this->db->from('accounts_settings')->where($where)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$dataprotection = explode(",",$result->value);
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


		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'valid_fields' => $valid_fields,
			'dataprotection' => $dataprotection,
			'page_base' => $page_base,
			'res' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'tab' => 'dataconflicts'
		);

		$this->crm_view('dataconflicts', $data);
	}

	public function combine() {

		$intoID = $this->input->post('intoID');
		$duplicate_ids = $this->input->post('duplicate_ids');

		// check params
		if (empty($duplicate_ids)) {
			show_404();
		}

		if (empty($intoID)) {
			$this->session->set_flashdata('error', 'You must select a record to merge into.');
			redirect('dataconflicts/recall');
		}

		$from_ids = explode(",", $duplicate_ids);

		// remove intoID from
		if(($key = array_search($intoID, $from_ids)) !== false) {
			unset($from_ids[$key]);
		}

		if (count($from_ids) > 0) {

			//echo "Merging " . implode(",", $from_ids) . " into " . $intoID;

			// look up family from target child
			$where = array(
				'childID' => $intoID,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('family_children')->where($where)->get();
			if ($res->num_rows() == 0) {
				show_404();
			}
			foreach ($res->result() as $child_info) {
				$familyID = $child_info->familyID;
			}

			// get main contact
			$where = array(
				'familyID' => $familyID,
				'main' => 1,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('family_contacts')->where($where)->get();
			if ($res->num_rows() == 0) {
				// none found, so assign a main contact
				$data = array(
					'main' => 1
				);
				$where_update = array(
					'familyID' => $familyID,
					'accountID' => $this->auth->user->accountID
				);
				$this->db->update('family_contacts', $data, $where_update, 1);
				$res = $this->db->from('family_contacts')->where($where)->get();
				if ($res->num_rows() == 0) {
					show_404();
				}
			}
			foreach ($res->result() as $row) {
				$contactID = $row->contactID;
			}

			$merged = 0;

			foreach ($from_ids as $fromID) {

				if ($fromID == $intoID) {
					continue;
				}

				// get family of source child
				$where = array(
					'childID' => $fromID,
					'accountID' => $this->auth->user->accountID
				);

				$res = $this->db->from('family_children')->where($where)->limit(1)->get();

				if ($res->num_rows() == 0) {
					continue;
				}

				foreach ($res->result() as $child_info) {}

				// if not same family
				if ($child_info->familyID != $familyID) {
					// update records with new contact id
					$where = array(
						'familyID' => $child_info->familyID,
						'accountID' => $this->auth->user->accountID
					);
					$data = array(
						'contactID' => $contactID
					);
					$res = $this->db->update('family_notifications', $data, $where);
					$res = $this->db->update('family_payments', $data, $where);
					$res = $this->db->update('family_payments_plans', $data, $where);
					$res = $this->db->update('bookings_cart', $data, $where);

					// merge family - update all tables to new family ID
					$where = array(
						'familyID' => $child_info->familyID,
						'accountID' => $this->auth->user->accountID
					);
					$data = array(
						'familyID' => $familyID
					);
					$res = $this->db->update('family_children', $data, $where);
					$res = $this->db->update('family_contacts', $data, $where);
					$res = $this->db->update('family_notes', $data, $where);
					$res = $this->db->update('family_notifications', $data, $where);
					$res = $this->db->update('family_payments', $data, $where);
					$res = $this->db->update('family_payments_plans', $data, $where);
					$res = $this->db->update('bookings_cart', $data, $where);

					// recalc balances
					$this->crm_library->recalc_family_balance($familyID);
				}

				// update cart sessions with new child id
				$where = array(
					'childID' => $fromID,
					'accountID' => $this->auth->user->accountID
				);
				$data = array(
					'childID' => $intoID
				);
				$res = $this->db->update('bookings_cart_sessions', $data, $where);
				$res = $this->db->update('bookings_cart_monitoring', $data, $where);
				$res = $this->db->update('bookings_cart_bikeability', $data, $where);

				// delete original child
				$res = $this->db->delete('family_children', $where);

				// if not same family
				if ($child_info->familyID != $familyID) {
					// delete original family
					$where = array(
						'familyID' => $child_info->familyID,
						'accountID' => $this->auth->user->accountID
					);
					$res = $this->db->delete('family', $where, 1);
				}

				if ($this->db->affected_rows() == 1) {
					$merged++;
				}

			}

			// check if all ok
			if ($merged == 1) {
				$pl = NULL;
			} else {
				$pl = 'ren';
			}
			$success = "Merged " . $merged . " duplicate child" . $pl . " and their family into " . $child_info->first_name . " " . $child_info->last_name . " successfully";
			$this->session->set_flashdata('success', $success);

			redirect('dataconflicts/recall');
		}
	}


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

}

/* End of file export.php */
/* Location: ./application/controllers/export.php */
