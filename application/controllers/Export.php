<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Export extends MY_Controller {
	
	public function __construct() {
		// directors and dpo only
		
		parent::__construct(FALSE, array(), array(), array('export'));
		
		if($this->auth->user->department != 'directors'){
			$where = array(
				'accountID' => $this->auth->user->accountID,
				'key' => 'data_protection_officer'
			);
			$data_protection_officer = array();
			$res = $this->db->from('accounts_settings')->where($where)->get();
			if($res->num_rows() > 0){
				foreach($res->result() as $result){
					$data_protection_officer = explode(",",$result->value);
				}
			}
			
			if(!in_array($this->auth->user->staffID, $data_protection_officer)){
				show_403();
				return FALSE;
			}
		}
	}

	/**
	 * export data
	 * @return void
	 */
	public function index()
	{

		$area_info = new stdClass;

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Export Data';
		$submit_to = 'export';
		$icon = 'download';
		$current_page = 'export';
		$section = 'export';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$error = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('export', 'Export To', 'trim|xss_clean|required');
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			$this->form_validation->set_rules('schools', 'Schools', 'trim|xss_clean');
			$this->form_validation->set_rules('organisations', 'Organisations', 'trim|xss_clean');
			$this->form_validation->set_rules('customers', $this->settings_library->get_label('customers'), 'trim|xss_clean');
			$this->form_validation->set_rules('prospects', 'Prospects', 'trim|xss_clean');
			$this->form_validation->set_rules('main_contact_only', 'Main Contact Only', 'trim|xss_clean');
			$this->form_validation->set_rules('bookings_from', 'Bookings From', 'trim|xss_clean|callback_check_date');
			$this->form_validation->set_rules('bookings_to', 'Bookings To', 'trim|xss_clean|callback_check_date|callback_after_start[' . $this->input->post('bookings_from') . ']');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				//load csv helper
				$this->load->helper('csv_helper');

				$search_where = array();
				
				// send notification_staff
				$where = array(
					'accountID' => $this->auth->user->accountID,
					'key' => 'data_protection_officer_send_notification'
				);
				$dpo_notification = array();
				$query = $this->db->from('accounts_settings')->where($where)->get();
				if($query->num_rows() > 0){
					foreach($query->result() as $result){
						$dpo_notification = explode(",",$result->value);
					}
				}

				switch (set_value('type')) {
					case 'customers':
						// schools only
						if (set_value('schools') == 1 && set_value('organisations') != 1) {
							$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`type` = 'school'";
						}
						// orgs only
						if (set_value('schools') != 1 && set_value('organisations') == 1) {
							$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`type` = 'organisation'";
						}
						// none - will never return anything
						if (set_value('schools') != 1 && set_value('organisations') != 1) {
							$search_where[] = "'1' = '2'";
						}
						// customers only
						if (set_value('customers') == 1 && set_value('prospects') != 1) {
							$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`prospect` != '1'";
						}
						// customerss only
						if (set_value('customers') != 1 && set_value('prospects') == 1) {
							$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`prospect` = '1'";
						}
						// none - will never return anything
						if (set_value('customers') != 1 && set_value('prospects') != 1) {
							$search_where[] = "'1' = '2'";
						}
						// if main contact only
						if (set_value('main_contact_only') == '1') {
							$search_where[] = '`' . $this->db->dbprefix("orgs_contacts") . "`.`isMain` = '1'";
						}
						// if newsletter, check if subscribed
						if (set_value('type') == 'newsletter') {
							$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`newsletter` = '1'";
						}

						if (count($search_where) > 0) {
							$search_where = '(' . implode(' AND ', $search_where) . ')';
						}
						
						// check dpo notification available
						if(count($dpo_notification) > 0){
							$this->crm_library->export_data_notification($dpo_notification, set_value('type'));
						}

						// run query
						$where = array(
							'orgs_addresses.type' => 'main',
							'orgs_addresses.accountID' => $this->auth->user->accountID
						);
						$res = $this->db->select('orgs.name as org, orgs.type as org_type, orgs_contacts.*, orgs_addresses.town')->from('orgs')->join('orgs_contacts', 'orgs.orgID = orgs_contacts.orgID', 'inner')->join('orgs_addresses', 'orgs.orgID = orgs_addresses.orgID', 'inner')->where($where)->where($search_where, NULL, FALSE)->group_by('orgs_contacts.contactID')->order_by('orgs_contacts.name asc')->get();

						// if some results
						if ($res->num_rows() > 0) {

							// output
							switch (set_value('export')) {
								case 'newsletter':
									// build data
									$csv_data = array();

									// set headings
									$headings = array(
										'Email',
										'Name',
										'Position',
										'Type',
										'Town',
										'Organisation'
									);

									// add to csv
									$csv_data[] = $headings;

									foreach ($res->result() as $row) {
										// check email valid
										if (empty($row->email) || !filter_var($row->email, FILTER_VALIDATE_EMAIL)) {
											// skip
											continue;
										}

										// build row
										$row = array(
											'email' => $row->email,
											'name' => $row->name,
											'position' => $row->position,
											'type' => ucwords($row->org_type),
											'town' => $row->town,
											'org' => $row->org
										);

										// add to csv
										$csv_data[] = $row;
									}
									break;
								case 'sms':
									// build data
									$csv_data = array();

									// set headings
									$headings = array(
										'Mobile',
										'Name',
										'Type',
										'Town',
										'Organisation'
									);

									// add to csv
									$csv_data[] = $headings;

									foreach ($res->result() as $row) {
										// normalise
										$row->mobile = $this->crm_library->normalise_mobile($row->mobile, $row->accountID);

										// check mobile valid
										if (empty($row->mobile) || !$this->crm_library->check_mobile($row->mobile, $row->accountID)) {
											// skip
											continue;
										}

										// build row
										$row = array(
											'mobile' => $row->mobile,
											'name' => $row->name,
											'type' => ucwords($row->org_type),
											'town' => $row->town,
											'org' => $row->org
										);

										// add to csv
										$csv_data[] = $row;
									}
									break;
								default:
									show_404();
									break;
							}

							if (count($csv_data) <= 1) {
								$error = 'No records found.';
							} else {
								array_to_csv($csv_data, 'export.csv');
								return TRUE;
							}

						} else {
							$error = 'No records found.';
						}

						break;
					case 'participants':

						// assume not booking search
						$booking_search = FALSE;
						
						// Check Filter Value
						$filters = $this->input->post('filter');
						if(empty($filters)){
							$filters = array();
						}
						
						// if main contact only
						if (set_value('main_contact_only') == '1') {
							$search_where[] = "`main` = '1'";
						}
						// if newsletter, check if subscribed
						if (set_value('type') == 'newsletter') {
							$search_where[] = "`newsletter` = '1'";
						}

						if (set_value('bookings_from') != '') {
							$bookings_from = uk_to_mysql_date(set_value('bookings_from'));
							if ($bookings_from !== FALSE) {
								$search_where[] = '`' . $this->db->dbprefix("bookings_cart") . "`.`booked` >= " . $this->db->escape($bookings_from);
								$booking_search = TRUE;
							}
						}

						if (set_value('bookings_to') != '') {
							$bookings_to = uk_to_mysql_date(set_value('bookings_to'));
							if ($bookings_to !== FALSE) {
								$search_where[] = '`' . $this->db->dbprefix("bookings_cart") . "`.`booked` <= " . $this->db->escape($bookings_to);
								$booking_search = TRUE;
							}
						}

						if (count($search_where) > 0) {
							$search_where = '(' . implode(' AND ', $search_where) . ')';
						}

						// run query
						$where = array(
							'family_contacts.accountID' => $this->auth->user->accountID,
							'family_contacts.marketing_consent' => 1
						);
						if ($booking_search === TRUE) {
							$where['bookings_cart.type'] = 'booking';
							$res = $this->db->select('family_contacts.*')->from('family_contacts')->join('bookings_cart', 'bookings_cart.familyID = family_contacts.familyID', 'inner')->where($where)->where($search_where, NULL, FALSE)->order_by('first_name asc, last_name asc')->group_by('family_contacts.familyID')->get();
						} else {
							$res = $this->db->from('family_contacts')->where($where)->where($search_where, NULL, FALSE)->order_by('first_name asc, last_name asc')->get();
						}
						// if some results
						if ($res->num_rows() > 0) {
							
							// check dpo notification available
							if(count($dpo_notification) > 0){
								$this->crm_library->export_data_notification($dpo_notification, set_value('type'), $filters);
							}
							
							//Disability Information By Child and Account Holders
							
							$disabilities = array();
							if(in_array('Disability', $filters)){
								$where = array("accountID" => $this->auth->user->accountID);
								$query = $this->db->from("family_disabilities")->where($where)->get();
								if($query->num_rows() > 0){
									foreach($query->result() as $result){
										if($result->contactID != NULL){
											$info = array();
											foreach ($this->settings_library->disabilities as $key => $value) {
												if($result->$key == 1)
													$info[] = $value;
											}
											if(!isset($disabilities['contact'][$result->contactID])){
												$disabilities['contact'][$result->contactID] = 0;
											}
											$disabilities['contact'][$result->contactID] = ((count($info) > 0)?implode(", ",$info):'');
										}else{
											$info = array();
											foreach ($this->settings_library->disabilities as $key => $value) {
												if($result->$key == 1)
													$info[] = $value;
											}
											if(!isset($disabilities['child'][$result->childID])){
												$disabilities['child'][$result->childID] = 0;
											}
											$disabilities['child'][$result->childID] = ((count($info) > 0)?implode(", ",$info):'');
										}
									}
								}
							}
							
							// Check Children Available
							$familyIDs = array();
							foreach($res->result() as $result){
								if(!in_array($result->familyID, $familyIDs)){
									$familyIDs[] = $result->familyID;
								}
							}
							
							$where_in = array("familyID" => implode(",",$familyIDs));
							
							$where = array("family_children.accountID" => $this->auth->user->accountID,
							"family_contacts.main" => 1);
							
							$query = $this->db->select('family_children.*, family_contacts.email, family_contacts.mobile, family_contacts.town, family_contacts.ethnic_origin')
							->from("family_children")
							->join("family_contacts", "family_contacts.familyID = family_children.familyID", "left")
							->where($where)
							->where_in($where_in)
							->get();
							
							// output
							switch (set_value('export')) {
								case 'newsletter':
									// build data
									$csv_data = array();

									// set headings
									$headings = array(
										'Email',
										'First Name',
										'Last Name',
										'Town'
									);
									if(count($filters) > 0){
										$headings = array_merge($headings, $filters);
									}
									
									// add to csv
									$csv_data[] = $headings;

									foreach ($res->result() as $row) {
										// check email valid
										if (empty($row->email) || !filter_var($row->email, FILTER_VALIDATE_EMAIL)) {
											// skip
											continue;
										}

										// build row
										$rowdata = array(
											'email' => $row->email,
											'first_name' => $row->first_name,
											'last_name' => $row->last_name,
											'town' => $row->town
										);
										
										// Add Ethnicity
										if(in_array('Ethnicity', $headings)){
											$rowdata['ethnicity'] = $row->ethnic_origin;
										}
										
										// Add Gender
										if(in_array('Gender', $headings)){
											$rowdata['gender'] = ucfirst($row->gender);
											if($row->gender == 'please_specify'){
												$rowdata['gender'] = $row->gender_specify;
											}else if($row->gender == 'other'){
												$rowdata['gender'] = "Prefer not to say";
											}
										}
										
										// Add Age
										if(in_array('Age', $headings)){
											$rowdata['age'] = calculate_age($row->dob);
										}
										
										// Add Disabilities
										if(in_array('Disability', $headings)){
											$rowdata['disability'] = '';
											if(isset($disabilities['contact'][$row->contactID]))
												$rowdata['disability'] = $disabilities['contact'][$row->contactID];
										}


										// add to csv
										$csv_data[] = $rowdata;
									}
									
									// Childern rows
									if($query->num_rows() > 0){
										foreach($query->result() as $result){
											// check email valid
											if (empty($result->email) || !filter_var($result->email, FILTER_VALIDATE_EMAIL)) {
												// skip
												continue;
											}

											// build row
											$rowdata = array(
												'email' => $result->email,
												'first_name' => $result->first_name,
												'last_name' => $result->last_name,
												'town' => $result->town
											);
											
											// Add Ethnicity
											if(in_array('Ethnicity', $headings)){
												$rowdata['ethnicity'] = $result->ethnic_origin;
											}
											
											// Add Gender
											if(in_array('Gender', $headings)){
												$rowdata['gender'] = ucfirst($result->gender);
												if($result->gender == 'please_specify'){
													$rowdata['gender'] = $result->gender_specify;
												}else if($result->gender == 'other'){
													$rowdata['gender'] = "Prefer not to say";
												}
											}
											
											// Add Age
											if(in_array('Age', $headings)){
												$rowdata['age'] = calculate_age($result->dob);
											}
											
											// Add Disabilities
											if(in_array('Disability', $headings)){
												$rowdata['disability'] = '';
												if(isset($disabilities['child'][$result->childID]))
													$rowdata['disability'] = $disabilities['child'][$result->childID];
											}
											
											// add to csv
											$csv_data[] = $rowdata;
										}
									}
									
									break;
								case 'sms':
									// build data
									$csv_data = array();

									// set headings
									$headings = array(
										'Mobile',
										'First Name',
										'Last Name',
										'Town'
									);
									if(count($filters) > 0){
										$headings = array_merge($headings, $filters);
									}
									// add to csv
									$csv_data[] = $headings;

									foreach ($res->result() as $row) {
										// normalise
										$row->mobile = $this->crm_library->normalise_mobile($row->mobile, $row->accountID);

										// check mobile valid
										if (empty($row->mobile) || !$this->crm_library->check_mobile($row->mobile, $row->accountID)) {
											// skip
											continue;
										}

										// build row
										$rowdata = array(
											'mobile' => $row->mobile,
											'first_name' => $row->first_name,
											'last_name' => $row->last_name,
											'town' => $row->town
										);
										
										// Add Ethnicity
										if(in_array('Ethnicity', $headings)){
											$rowdata['ethnicity'] = $row->ethnic_origin;
										}
										
										// Add Gender
										if(in_array('Gender', $headings)){
											$rowdata['gender'] = ucfirst($row->gender);
											if($row->gender == 'please_specify'){
												$rowdata['gender'] = $row->gender_specify;
											}else if($row->gender == 'other'){
												$rowdata['gender'] = "Prefer not to say";
											}
										}
										
										// Add Age
										if(in_array('Age', $headings)){
											$rowdata['age'] = calculate_age($row->dob);
										}
										
										// Add Disabilities
										if(in_array('Disability', $headings)){
											$rowdata['disability'] = '';
											if(isset($disabilities['contact'][$row->contactID]))
												$rowdata['disability'] = $disabilities['contact'][$row->contactID];
										}

										// add to csv
										$csv_data[] = $rowdata;
									}
									
									// Childern rows
									if($query->num_rows() > 0){
										foreach($query->result() as $result){
											// normalise
											$result->mobile = $this->crm_library->normalise_mobile($result->mobile);

											// check mobile valid
											if (empty($result->mobile) || !$this->crm_library->check_mobile($result->mobile, $result->accountID)) {
												// skip
												continue;
											}

											// build row
											$rowdata = array(
												'mobile' => $result->mobile,
												'first_name' => $result->first_name,
												'last_name' => $result->last_name,
												'town' => $result->town
											);
											
											// Add Ethnicity
											if(in_array('Ethnicity', $headings)){
												$rowdata['ethnicity'] = $result->ethnic_origin;
											}
											
											// Add Gender
											if(in_array('Gender', $headings)){
												$rowdata['gender'] = ucfirst($result->gender);
												if($result->gender == 'please_specify'){
													$rowdata['gender'] = $result->gender_specify;
												}else if($result->gender == 'other'){
													$rowdata['gender'] = "Prefer not to say";
												}
											}
											
											// Add Age
											if(in_array('Age', $headings)){
												$rowdata['age'] = calculate_age($result->dob);
											}
											
											// Add Disabilities
											if(in_array('Disability', $headings)){
												$rowdata['disability'] = '';
												if(isset($disabilities['child'][$result->childID]))
													$rowdata['disability'] = $disabilities['child'][$result->childID];
											}
											
											// add to csv
											$csv_data[] = $rowdata;
										}
									}
									
									
									break;
								default:
									show_404();
									break;
							}

							if (count($csv_data) <= 1) {
								$error = 'No records found.';
							} else {
								array_to_csv($csv_data, 'export.csv');
								return TRUE;
							}

						} else {
							$error = 'No records found.';
						}
						break;
					default:
						show_404();
						break;
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
			'submit_to' => $submit_to,
			'success' => $success,
			'errors' => $errors,
			'error' => $error,
			'info' => $info,
			'tab' => ''
		);

		// load view
		$this->crm_view('export', $data);
	}
	
	public function dataprotection(){
		
		// set defaults
		$title = 'Data Protection Officer';
		$submit_to = 'export/dataprotection';
		$icon = '';
		$current_page = 'export/dataprotection';
		$section = 'export';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$error = NULL;
		$page_base = 'export/dataprotection';
		
		$where = array(
			"accountID" => $this->auth->user->accountID
		);
		$staff = $this->db->select("*")->from("staff")->where($where)->order_by("first, surname")->get();
		
		$dpo_key = array(
			"data_protection_officer" => "data_protection",
			"data_protection_officer_send_notification" => "notification_staff"
		);
		
		if ($this->input->post()) {
			
			$this->load->library('form_validation');
			$this->form_validation->set_rules('data_protection_officer', 'Data Protection Officer(s)', 'trim|xss_clean');
			$this->form_validation->set_rules('data_protection_officer_send_notification', 'Send Notification to', 'trim|xss_clean');
			
			$data_protection = $this->input->post('data_protection_officer');
			
			$notification_staff = $this->input->post('data_protection_officer_send_notification');
			
			$data_protection = ($data_protection != "")?implode(",",$data_protection):"";
			$notification_staff = ($notification_staff != "")?implode(",",$notification_staff):"";
		
			foreach($dpo_key as $key => $dpo){
				$where = array(
					"accountID" => $this->auth->user->accountID,
					"key" => $key
				);
				$query = $this->db->select("*")->from("accounts_settings")->where($where)->get();
				if ($query->num_rows() > 0) {
					$where = array("accountID" => $this->auth->user->accountID,
					"key" => $key);
					
					$data["value"] = $$dpo;
					$data['updated_at'] = mdate('%Y-%m-%d %H:%i:%s');
					$query = $this->db->update('accounts_settings', $data, $where);
				}else{
					$data["value"] = $$dpo;
					$data["accountID"] = $this->auth->user->accountID;
					$data["key"] = $key;
					$data['created_at'] = mdate('%Y-%m-%d %H:%i:%s');
					$data['updated_at'] = mdate('%Y-%m-%d %H:%i:%s');
					$query = $this->db->insert('accounts_settings', $data);
				}
			}
			$success = 'Data Updated Successfully.';
		}
		$dpo_data = array();
		foreach($dpo_key as $key => $dpo){
			$dpo_data[$key] = array();
			$query = $this->db->select("*")->from("accounts_settings")->where("accountID", $this->auth->user->accountID)->where("key",$key)->get();
			if ($query->num_rows() > 0) {
				foreach($query->result() as $result){
					if($result->value != null && $result->value != ""){
						$dpo_data[$key] = explode(",", $result->value);
					}
				}
			}
		}
		
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'submit_to' => $submit_to,
			'success' => $success,
			'errors' => $errors,
			'error' => $error,
			'info' => $info,
			'staff' => $staff,
			'page_base' => $page_base,
			'tab' => 'dataprotection',
			'dpo_data' => $dpo_data
		);

		// load view
		$this->crm_view('dataprotection', $data);
		
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

}

/* End of file export.php */
/* Location: ./application/controllers/export.php */
