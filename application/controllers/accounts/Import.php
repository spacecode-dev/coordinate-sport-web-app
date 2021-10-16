<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import extends MY_Controller {

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array('accounts'));
	}

	/**
	 * import data
	 * @param  int $accountID
	 * @return void
	 */
	public function index($accountID)
	{

		$account_info = new stdClass;

		// check if empty
		if (empty($accountID)) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($accountID)) {
			show_404();
		}

		// check exists
		$where = array(
			'accountID' => $accountID,
		);

		// run query
		$query = $this->db->from('accounts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$account_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Import Data';
		$submit_to = 'accounts/import/' . $accountID;
		$return_to = 'accounts';
		$icon = 'server';
		$current_page = 'accounts';
		$section = 'accounts';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'accounts' => 'Accounts'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('import_type', 'Import Type', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
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

					$sheet_name = set_value('import_type');
					$initial_row = 1;
					$fields = array();
					$imported = 0;

					switch (set_value('import_type')) {
						case 'Participants':
							// has no first row
							$initial_row = 2;
							// set fields map and default values
							$fields = array(
								'Title' => 'title',
								'First Name' => 'first_name',
								'Last Name' => 'last_name',
								'Gender' => 'gender',
								'DOB' => 'dob',
								'Medical Notes' => 'medical',
								'Relationship to Participants' => 'relationship',
								'Address 1' => 'address1',
								'Address 2' => 'address2',
								'Address 3' => 'address3',
								'Town' => 'town',
								'County' => 'county',
								'Post Code' => 'postcode',
								'Phone' => 'phone',
								'Mobile' => 'mobile',
								'Work Phone' => 'workPhone',
								'Email' => 'email',
								'School' => 'school',
								'Photo Consent' => 'photoConsent',
								'Notes' => 'notes',
								'Account Balance' => 'balance'
							);
							$relationships = array(
								'individual' => 'Individual',
								'parent' => 'Parent',
								'grandparent' => 'Grandparent',
								'guardian' => 'Guardian',
								'parents friend' => 'Parents Friend',
								'other' => 'Other'
							);
							$schools = array();
							$where = array(
								'accountID' => $accountID
							);
							$res = $this->db->from('orgs')->where($where)->get();
							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$schools[$row->orgID] = $row->name;
								}
							}
							break;
						case 'Customers':
							// has no first row
							$initial_row = 2;
							// set fields map and default values
							$fields = array(
								'Type' => 'type',
								'Name' => 'name',
								'Private/Local Authority' => 'localauthority',
								'School Type' => 'schoolType',
								'Email' => 'email',
								'Web Site' => 'website',
								'Rate/Charge' => 'rate',
								'Invoice Frequency' => 'invoiceFrequency',
								'Prospect' => 'prospect',
								'Region' => 'region',
								'Area' => 'area',
								'Address 1' => 'address1',
								'Address 2' => 'address2',
								'Address 3' => 'address3',
								'Town' => 'town',
								'County' => 'county',
								'Postcode' => 'postcode',
								'Phone' => 'phone',
								// contacts
								'Contact Name' => 'name',
								'Position' => 'position',
								// additional addresses
								'Address Type' => 'type'
							);
							$customer_types = array(
								'school' => 'School',
								'organisation' => 'Organisation'
							);
							$school_types = array(
								'infant' => 'Infant',
								'junior' => 'Junior',
								'primary' => 'Primary',
								'secondary' => 'Secondary',
								'college' => 'College',
								'special' => 'Special',
								'other' => 'Other'
							);
							$invoice_freqs = array(
								'weekly' => 'Weekly',
								'monthly' => 'Monthly',
								'half termly' => 'Half Termly',
								'termly' => 'Termly',
								'annually' => 'Annually'
							);
							$address_types = array(
								'delivery' => 'Delivery',
								'billing' => 'Billing',
								'other' => 'Other'
							);
							break;
						case 'Equipment':
							// has no first row
							$initial_row = 2;
							// set fields map and default values
							$fields = array(
								'Name' => 'name',
								'Location' => 'location',
								'Notes' => 'notes',
								'Quantity' => 'quantity',
							);
							break;
						case 'Staff':
							// has no first row
							$initial_row = 2;
							// set fields map and default values
							$fields = array(
								'Title' => 'title',
								'First Name' => 'first',
								'Middle Name' => 'middle',
								'Last Name' => 'surname',
								'Job Title' => 'jobTitle',
								'Permission Level' => 'department',
								'NI Number' => 'nationalInsurance',
								'DOB' => 'dob',
								'Email Address' => 'email',
								'Address 1' => 'address1',
								'Address 2' => 'address2',
								'Town' => 'town',
								'County' => 'county',
								'Post Code' => 'postcode',
								'At Address from' => 'from',
								'Phone' => 'phone',
								'Mobile' => 'mobile',
								'Work Mobile' => 'mobile_work',
								'Ethnic Origin' => 'equal_ethnic',
								'Disability Information' => 'equal_disability',
								'Where did you find us?' => 'equal_source',
								'Medical Information' => 'medical',
								'T-Shirt Size' => 'tshirtSize',
								'Show profile on web site' => 'onsite',
								'Type of contact' => 'type',
								'Name' => 'name', // contacts
								'Type' => 'type', // contacts
								'Relationship' => 'relationship', // contacts
								'First Aid' => 'qual_first',
								'First Aid - Expiry' => 'qual_first_expiry_date',
								'Child Protection' => 'qual_child',
								'Child Protection - Expiry' => 'qual_child_expiry_date',
								'Company DBS' => 'qual_fsscrb',
								'Company DBS - Expiry' => 'qual_fsscrb_expiry_date',
								'Company DBS - Reference' => 'qual_fsscrb_ref',
								'Passport' => 'proofid_passport',
								'Passport - Date' => 'proofid_passport_date',
								'Passport - Reference' => 'proofid_passport_ref',
								'NI Card' => 'proofid_nicard',
								'NI Card - Reference' => 'proofid_nicard_ref',
								'Driver\'s Licence' => 'proofid_driving',
								'Driver\'s Licence - Date' => 'proofid_driving_date',
								'Driver\'s Licence - Reference' => 'proofid_driving_ref',
								'Birth Certificate' => 'proofid_birth',
								'Birth Certificate - Date' => 'proofid_birth_date',
								'Birth Certificate - Reference' => 'proofid_birth_ref',
								'Utility Bill' => 'proofid_utility',
								'Other' => 'proofid_other',
								'Other - Please Specify' => 'proofid_other_specify',
								'MOT' => 'driving_mot',
								'MOT - Date' => 'driving_mot_expiry',
								'Insurance' => 'driving_insurance',
								'Insurance - Date' => 'driving_insurance_expiry',
								'Declaration' => 'driving_declaration',
								'Proof of Address' => 'proof_address',
								'Proof of National Insurance' => 'proof_nationalinsurance',
								'Proof of Qualifications/DBS' => 'proof_quals',
								'Valid working permit or visa/UK resident' => 'proof_permit',
								'ID Card' => 'checklist_idcard',
								'Pay Dates' => 'checklist_paydates',
								'Timesheet' => 'checklist_timesheet',
								'Policy Agreement' => 'checklist_policy',
								'Travel Expenses' => 'checklist_travel',
								'Equal Opportunities' => 'checklist_equal',
								'Employment Contract' => 'checklist_contract',
								'P45/P46/P38' => 'checklist_p45',
								'Policies' => 'checklist_policies',
								'Details updated on system' => 'checklist_details',
								'T-shirt' => 'checklist_tshirt',
								'Start Date' => 'employment_start_date',
								'End Date' => 'employment_end_date',
								'Probation Date' => 'employment_probation_date',
								'Probation Complete' => 'employment_probation_complete',
								'Salaried Hours' => 'target_hours',
								'Target Utilisation (%)' => 'target_utilisation',
								'Team Leader' => 'team_leader',
								'Head Coach - Per Hour' => 'payments_scale_head',
								'Assistant Coach - Per Hour' => 'payments_scale_assist',
								'Salaried Staff' => 'payments_scale_salaried',
								'Salary' => 'payments_scale_salary',
								'Bank Name' => 'payments_bankName',
								'Sort Code' => 'payments_sortCode',
								'Account Number' => 'payments_accountNumber',
								// additional quals
								'Qualification Name' => 'name',
								'Level' => 'level',
								'Qualification No.' => 'reference',
								'Expiry' => 'expiry_date'
							);
							// track activities
							$activities = array();
							$where = array(
								'accountID' => $accountID
							);
							$res = $this->db->from('activities')->where($where)->get();
							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$activities[$row->activityID] = $row->name;
								}
							}
							// track mandatory quals
							$mandatory_quals = array();
							$where = array(
								'accountID' => $accountID
							);
							$res = $this->db->from('mandatory_quals')->where($where)->get();
							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$mandatory_quals[$row->qualID] = $row->name;
								}
							}
							$departments = array(
								'directors' => 'Super User',
								'management' => 'Management',
								'office' => 'Office',
								'headcoach' => 'Team Leader',
								'fulltimecoach' => 'Salaried Coach',
								'coaching' => 'Coaches'
							);
							$contact_types = array(
								'additional' => 'Additional',
								'emergency' => 'Emergency'
							);
							break;
					}

					if (count($fields) == 0) {
						$errors[] = 'Import type not found';
					} else {

						try {
							$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($upload_data['full_path']); // identify the file
							$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType); // creating the reader
							$objReader->setReadDataOnly(true);
							$objReader->setLoadSheetsOnly($sheet_name);
							$objPHPExcel = $objReader->load($upload_data['full_path']); // loading the file

							// get worksheet dimensions
							$sheet = $objPHPExcel->getSheet(0); // selecting sheet 0
							$highestRow = $sheet->getHighestRow(); // getting number of rows
							$highestColumn = $sheet->getHighestColumn(); // getting number of columns

							$i = 0;

							$headings = array();
							$rows = array();

							// loop through each row of the worksheet in turn
							for ($row = $initial_row; $row <= $highestRow; $row++) {

								// read a row of data into an array
								$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
								$data = $rowData[0];

								// first row, get col headings
								if ($i == 0) {
									$x = 0;
									if (is_array($data) && count($data) > 0) {
										$last_preheader = NULL;
										foreach ($data as $heading) {
											$heading = trim($heading);

											// track last preheader value for merge fields
											if ($initial_row > 0) {
												$preheader_val = $sheet->getCellByColumnAndRow(($x+1), 1)->getValue();
												if (!empty($preheader_val)) {
													$last_preheader = $preheader_val;
												}
											}

											// skip cell if no heading
											if (empty($heading)) {
												$x++;
												continue;
											}

											switch (set_value('import_type')) {
												default:
													if (array_key_exists($heading, $fields) && !empty($fields[$heading])) {
														$headings[$x] = $fields[$heading];
													}
													break;
												case 'Customers':
													switch ($last_preheader) {
														default:
															// use field map
															if (array_key_exists($heading, $fields) && !empty($fields[$heading])) {
																$headings[$x] = $fields[$heading];
															}
															break;
														case 'Contact 1':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'contact_1_' . $fields[$heading];
															}
															break;
														case 'Contact 2':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'contact_2_' . $fields[$heading];
															}
															break;
														case 'Contact 3':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'contact_3_' . $fields[$heading];
															}
															break;
														case 'Additional Address 1':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'additional_address_1_' . $fields[$heading];
															}
															break;
														case 'Additional Address 2':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'additional_address_2_' . $fields[$heading];
															}
															break;
														case 'Additional Address 3':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'additional_address_3_' . $fields[$heading];
															}
															break;
													}
													break;
												case 'Participants':
													switch ($last_preheader) {
														default:
															// use field map
															if (array_key_exists($heading, $fields) && !empty($fields[$heading])) {
																$headings[$x] = $fields[$heading];
															}
															break;
														case 'Participant 1':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'child_1_' . $fields[$heading];
															}
															break;
														case 'Participant 2':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'child_2_' . $fields[$heading];
															}
															break;
														case 'Participant 3':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'child_3_' . $fields[$heading];
															}
															break;
													}
													break;
												case 'Staff':
													switch ($last_preheader) {
														default:
															// use field map
															if (array_key_exists($heading, $fields) && !empty($fields[$heading])) {
																$headings[$x] = $fields[$heading];
															}
															break;
														case 'Able to Deliver':
															// create if doesn't exist
															if (!in_array($heading, $activities)) {
																$activity_data = array(
																	'name' => $heading,
																	'modified' => mdate('%Y-%m-%d %H:%i:%s'),
																	'accountID' => $accountID,
																	'imported' => 1,
																	'byID' => $this->auth->user->staffID,
																	'added' => mdate('%Y-%m-%d %H:%i:%s'),
																);
																$this->db->insert('activities', $activity_data);
																if ($this->db->affected_rows() > 0) {
																	$activityID = $this->db->insert_id();
																	$activities[$activityID] = $heading;
																}
															} else {
																$activityID = array_search($heading, $activities);
															}
															$fields[$heading] = 'deliver_' . $activityID;
															$headings[$x] = 'deliver_' . $activityID;
															break;
														case 'Custom Mandatory Qualifications':
															// create if doesn't exist
															if (!in_array($heading, $mandatory_quals)) {
																$qual_data = array(
																	'name' => $heading,
																	'modified' => mdate('%Y-%m-%d %H:%i:%s'),
																	'accountID' => $accountID,
																	'imported' => 1,
																	'byID' => $this->auth->user->staffID,
																	'added' => mdate('%Y-%m-%d %H:%i:%s'),
																);
																$this->db->insert('mandatory_quals', $qual_data);
																if ($this->db->affected_rows() > 0) {
																	$qualID = $this->db->insert_id();
																	$mandatory_quals[$qualID] = $heading;
																}
															} else {
																$qualID = array_search($heading, $mandatory_quals);
															}
															$fields[$heading] = 'mandatory_' . $qualID;
															$headings[$x] = 'mandatory_' . $qualID;
															break;
														case 'Additional Contact 1':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'contact_1_' . $fields[$heading];
															}
															break;
														case 'Additional Contact 2':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'contact_2_' . $fields[$heading];
															}
															break;
														case 'Additional Qualification 1':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'additional_qual_1_' . $fields[$heading];
															}
															break;
														case 'Additional Qualification 2':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'additional_qual_2_' . $fields[$heading];
															}
															break;
														case 'Additional Qualification 3':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'additional_qual_3_' . $fields[$heading];
															}
															break;
														case 'Additional Qualification 4':
															// use field map
															if (array_key_exists($heading, $fields)) {
																$headings[$x] = 'additional_qual_4_' . $fields[$heading];
															}
															break;
													}
													break;
											}
											$x++;
										}
									}
								} else {

									// loop through headings
									$row_data = array();
									foreach ($headings as $key => $value) {
										// assume null
										$row_data[$value] = NULL;
										if (isset($data[$key]) && !empty($data[$key])) {
											// get date in usable format
											if (stripos($value, 'dob') !== FALSE || (stripos($value, 'date') !== FALSE && stripos($value, 'date') === FALSE) || stripos($value, 'expiry') !== FALSE || stripos($value, 'from') !== FALSE) {
												if (is_numeric($data[$key]) && $data[$key] > 1) {
													if ($excel_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($data[$key])) {
														$row_data[$value] = date("Y-m-d", $excel_date);
													}
												}
												// if excel date not found, might be a normal string, try to convert
												if (empty($row_data[$value])) {
													if ($date = uk_to_mysql_date(trim($data[$key]))) {
														$row_data[$value] = $date;
													}
												}
											} else {
												$row_data[$value] = trim(strip_tags($data[$key]));
											}
										}
									}

									$rows[] = $row_data;
								}
								$i++;
							}
						} catch (Exception $e) {
							$errors[] = 'File could not be read' . $e->getMessage();
						}

						if (count($rows) == 0) {
							$errors[] = 'No rows found';
						} else {
							switch (set_value('import_type')) {
								case 'Participants':
									foreach ($rows as $row) {
										// if no data in row, skip
										if (count(array_filter($row)) == 0 || empty($row['first_name']) || empty($row['last_name'])) {
											continue;
										}
										$familyID = NULL;
										$contactID = NULL;
										// look up contact by email to see if family exists
										$where = array(
											'accountID' => $accountID,
											'email' => $row['email'],
											'email !=' => NULL,
											'email !=' => ''
										);
										$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();
										if ($res->num_rows() > 0) {
											foreach ($res->result() as $contact) {
												$familyID = $contact->familyID;
												$contactID = $contact->contactID;
											}
										}
										// if family doesn't exist, create
										if (empty($familyID)) {
											$data = array(
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'accountID' => $accountID
											);
											$res = $this->db->insert('family', $data);
											if ($this->db->affected_rows() == 1) {
												$familyID = $this->db->insert_id();
											}
										}
										// transform fields
										foreach ($row as $key => $val) {
											switch ($key) {
												case 'child_1_photoConsent':
												case 'child_2_photoConsent':
												case 'child_3_photoConsent':
													if (strtolower($val) == 'yes' || $val == 1) {
														$row[$key] = 1;
													} else {
														$row[$key] = 0;
													}
													break;
												case 'phone':
												case 'mobile':
												case 'workPhone':
													if (!empty($val) && substr($val, 0, 1) !== '0') {
														$row[$key] = '0' . $val;
													}
													break;
											}
										}
										// if contact doesn't exist, create
										if (empty($contactID)) {
											$data = array(
												'familyID' => $familyID,
												'main' => 1,
												'title' => strtolower($row['title']),
												'first_name' => $row['first_name'],
												'last_name' => $row['last_name'],
												'relationship' => strtolower($row['relationship']),
												'address1' => $row['address1'],
												'address2' => $row['address2'],
												'address3' => $row['address3'],
												'town' => $row['town'],
												'county' => $row['county'],
												'postcode' => $row['postcode'],
												'phone' => $row['phone'],
												'mobile' => $row['mobile'],
												'workPhone' => $row['workPhone'],
												'email' => $row['email'],
												'gender' => $row['gender'],
												'dob' => $row['dob'],
												'medical' => $row['medical'],
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'accountID' => $accountID
											);
											// check min fields
											if (!empty($data['first_name']) && !empty($data['last_name'])) {
												$res = $this->db->insert('family_contacts', $data);
												if ($this->db->affected_rows() == 1) {
													$contactID = $this->db->insert_id();
													$something_imported = TRUE;

													// geocode address
													if ($res_geocode = geocode_address($data['address1'], $data['town'], $data['postcode'])) {
														$where = array(
															'contactID' => $contactID,
															'accountID' => $accountID
														);
														$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('family_contacts');
													}

													// add notes
													if (isset($row['notes']) && !empty($row['notes'])) {
														$data = array(
															'familyID' => $familyID,
															'summary' => 'Imported Note',
															'content' => $row['notes'],
															'added' => mdate('%Y-%m-%d %H:%i:%s'),
															'modified' => mdate('%Y-%m-%d %H:%i:%s'),
															'accountID' => $accountID
														);
														$res_insert = $this->db->insert('family_notes', $data);
													}

													// add account balance
													if (isset($row['balance']) && !empty($row['balance'])) {
														// remove all except numbers and dots
														$row['balance'] = preg_replace('/[^\\d.]+/', '', $row['balance']);
														// if sitll nto empty
														if (!empty($row['balance'])) {
															$data = array(
																'contactID' => $contactID,
																'familyID' => $familyID,
																'amount' => $row['balance'],
																'method' => 'other',
																'note' => 'Imported Balance',
																'added' => mdate('%Y-%m-%d %H:%i:%s'),
																'modified' => mdate('%Y-%m-%d %H:%i:%s'),
																'accountID' => $accountID,
																'internal' => 1
															);
															$res_insert = $this->db->insert('family_payments', $data);

															// calc balance
															$this->crm_library->recalc_family_balance($familyID);
														}
													}
												}
											}
										}

										// children
										for ($i=1; $i <= 3; $i++) {
											$orgID = NULL;
											// create school if doesn't exist
											if (!empty($row['child_' . $i . '_school']) && !in_array($row['child_' . $i . '_school'], $schools)) {
												$school_data = array(
													'name' => $row['child_' . $i . '_school'],
													'type' => 'school',
													'modified' => mdate('%Y-%m-%d %H:%i:%s'),
													'accountID' => $accountID,
													'imported' => 1,
													'byID' => $this->auth->user->staffID,
													'added' => mdate('%Y-%m-%d %H:%i:%s'),
												);
												$this->db->insert('orgs', $school_data);
												if ($this->db->affected_rows() > 0) {
													$orgID = $this->db->insert_id();
													$schools[$orgID] = $heading;
												}
											} else if (array_search($row['child_' . $i . '_school'], $schools)){
												$orgID = array_search($row['child_' . $i . '_school'], $schools);
											}
											// prepare
											$data = array(
												'first_name' => $row['child_' . $i . '_first_name'],
												'last_name' => $row['child_' . $i . '_last_name'],
												'dob' => $row['child_' . $i . '_dob'],
												'medical' => $row['child_' . $i . '_medical'],
												'photoConsent' => $row['child_' . $i . '_photoConsent'],
												'accountID' => $accountID,
												'familyID' => $familyID,
												'orgID' => $orgID,
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s')
											);
											// check min fields
											if (!empty($data['first_name']) && !empty($data['last_name'])) {
												$res = $this->db->insert('family_children', $data);
												$something_imported = TRUE;
											}
										}
										if ($something_imported) {
											$imported++;
										}
									}
									break;
								case 'Customers':
									foreach ($rows as $row) {
										$something_imported = FALSE;
										// if no data in row, skip
										if (count(array_filter($row)) == 0) {
											continue;
										}
										$orgID = NULL;
										// look up org by name and email to see if exists
										$where = array(
											'accountID' => $accountID,
											'name' => $row['name'],
											'email' => $row['email']
										);
										$res = $this->db->from('orgs')->where($where)->limit(1)->get();
										if ($res->num_rows() > 0) {
											foreach ($res->result() as $org) {
												$orgID = $org->orgID;
											}
										}

										// if org doesn't exist, create
										if (empty($orgID)) {
											// get region
											$regionID = NULL;
											if (!empty($row['region'])) {
												$where = array(
													'accountID' => $accountID,
													'name' => $row['region']
												);
												$res = $this->db->from('settings_regions')->where($where)->limit(1)->get();
												if ($res->num_rows() > 0) {
													foreach ($res->result() as $item) {
														// exists
														$regionID = $item->regionID;
													}
												} else {
													// create region
													$data = array(
														'name' => $row['region'],
														'added' => mdate('%Y-%m-%d %H:%i:%s'),
														'modified' => mdate('%Y-%m-%d %H:%i:%s'),
														'accountID' => $accountID,
														'byID' => $this->auth->user->staffID,
													);
													$res = $this->db->insert('settings_regions', $data);
													if ($this->db->affected_rows() == 1) {
														$regionID = $this->db->insert_id();
													}
												}
											}
											// get area
											$areaID = NULL;
											if (!empty($row['area']) && !empty($regionID)) {
												$where = array(
													'accountID' => $accountID,
													'regionID' => $regionID,
													'name' => $row['area']
												);
												$res = $this->db->from('settings_areas')->where($where)->limit(1)->get();
												if ($res->num_rows() > 0) {
													foreach ($res->result() as $item) {
														// exists
														$areaID = $item->areaID;
													}
												} else {
													// create area
													$data = array(
														'name' => $row['area'],
														'added' => mdate('%Y-%m-%d %H:%i:%s'),
														'modified' => mdate('%Y-%m-%d %H:%i:%s'),
														'regionID' => $regionID,
														'accountID' => $accountID,
														'byID' => $this->auth->user->staffID,
													);
													$res = $this->db->insert('settings_areas', $data);
													if ($this->db->affected_rows() == 1) {
														$areaID = $this->db->insert_id();
													}
												}
											}
											// transform fields
											foreach ($row as $key => $val) {
												switch ($key) {
													case 'localauthority':
														$row['isPrivate'] = 0;
														if (strtolower($val) == 'private') {
															$row['isPrivate'] = 1;
														}
														break;
													case 'type':
														$row[$key] = NULL;
														if ($customer_type = array_search(strtolower($val), array_map('strtolower', $customer_types))) {
															$row[$key] = $customer_type;
														}
														break;
													case 'schoolType':
														$row[$key] = NULL;
														if ($school_type = array_search(strtolower($val), array_map('strtolower', $school_types))) {
															$row[$key] = $school_type;
														}
														break;
													case 'invoiceFrequency':
														$row[$key] = NULL;
														if ($invoice_freq = array_search(strtolower($val), array_map('strtolower', $invoice_freqs))) {
															$row[$key] = $invoice_freq;
														}
														break;
													case 'prospect':
														if (strtolower($val) == 'yes' || $val == 1) {
															$row[$key] = 1;
														} else {
															$row[$key] = 0;
														}
														break;
													case 'phone':
														if (!empty($val) && substr($val, 0, 1) !== '0') {
															$row[$key] = '0' . $val;
														}
														break;
												}
											}
											// prepare
											$data = array(
												'name' => $row['name'],
												'type' => strtolower($row['type']),
												'schoolType' => strtolower($row['schoolType']),
												'email' => $row['email'],
												'website' => array_key_exists('website', $row) ? $row['website'] : NULL,
												'rate' => $row['rate'],
												'invoiceFrequency' => array_key_exists('invoiceFreqency', $row) ? strtolower($row['invoiceFreqency']) : NULL,
												'isPrivate' => $row['isPrivate'],
												'regionID' => $regionID,
												'areaID' => $areaID,
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'accountID' => $accountID,
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
											);
											if (array_key_exists('prospect', $row)) {
												$data['prospect'] = $row['prospect'];
											}
											// check min fields
											if (empty($data['name']) || empty($data['type']) || empty($row['address1'])) {
												// skip
												continue;
											}
											$res = $this->db->insert('orgs', $data);
											if ($this->db->affected_rows() == 1) {
												$orgID = $this->db->insert_id();
												$something_imported = TRUE;
											}
										}

										// main address

										// check if exists
										$where = array(
											'accountID' => $accountID,
											'orgID' => $orgID,
											'type' => 'main',
											'address1' => $row['address1'],
											'postcode' => $row['postcode']
										);
										$res = $this->db->from('orgs_addresses')->where($where)->limit(1)->get();
										if ($res->num_rows() == 0) {
											// prepare
											$data = array(
												'address1' => $row['address1'],
												'address2' => $row['address2'],
												'address3' => $row['address3'],
												'town' => $row['town'],
												'county' => $row['county'],
												'postcode' => $row['postcode'],
												'phone' => $row['phone'],
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'accountID' => $accountID,
												'type' => 'main',
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
												'orgID' => $orgID,
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
											);

											// check min fields
											if (!empty($data['address1']) && !empty($data['type'])) {
												$res = $this->db->insert('orgs_addresses', $data);
												$addressID = $this->db->insert_id();
												$something_imported = TRUE;

												// geocode address
												if ($res_geocode = geocode_address($data['address1'], $data['town'], $data['postcode'])) {
													$where = array(
														'addressID' => $addressID,
														'accountID' => $accountID
													);
													$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('orgs_addresses');
												}
											}
										}

										// contacts
										for ($i=1; $i <= 3; $i++) {
											// check if exists
											$where = array(
												'accountID' => $accountID,
												'orgID' => $orgID,
												'name' => array_key_exists('contact_' . $i . '_name', $row) ? $row['contact_' . $i . '_name'] : NULL,
												'position' => array_key_exists('contact_' . $i . '_position', $row) ? $row['contact_' . $i . '_position'] : NULL
											);
											$res = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();
											if ($res->num_rows() == 0) {
												// prepare
												$data = array(
													'name' => array_key_exists('contact_' . $i . '_name', $row) ? $row['contact_' . $i . '_name'] : NULL,
													'position' => array_key_exists('contact_' . $i . '_position', $row) ? $row['contact_' . $i . '_position'] : NULL,
													'tel' => array_key_exists('contact_' . $i . '_phone', $row) ? $row['contact_' . $i . '_phone'] : NULL,
													'email' => array_key_exists('contact_' . $i . '_email', $row) ? $row['contact_' . $i . '_email'] : NULL,
													'modified' => mdate('%Y-%m-%d %H:%i:%s'),
													'accountID' => $accountID,
													'byID' => $this->auth->user->staffID,
													'imported' => 1,
													'orgID' => $orgID,
													'added' => mdate('%Y-%m-%d %H:%i:%s'),
													'isMain' => 0
												);
												if ($i == 1) {
													$data['isMain'] = 1;
												}
												// check min fields
												if (!empty($data['name'])) {
													$res = $this->db->insert('orgs_contacts', $data);
													$something_imported = TRUE;
												}
											}
										}

										// additional addresses
										for ($i=1; $i <= 3; $i++) {
											// check if exists
											$where = array(
												'accountID' => $accountID,
												'orgID' => $orgID,
												'type' => $row['additional_address_' . $i . '_type'],
												'address1' => $row['additional_address_' . $i . '_address1']
											);
											$res = $this->db->from('orgs_addresses')->where($where)->limit(1)->get();
											if ($res->num_rows() == 0) {
												// prepare
												$data = array(
													'type' => $row['additional_address_' . $i . '_type'],
													'address1' => $row['additional_address_' . $i . '_address1'],
													'address2' => $row['additional_address_' . $i . '_address2'],
													'address3' => $row['additional_address_' . $i . '_address3'],
													'town' => $row['additional_address_' . $i . '_town'],
													'county' => $row['additional_address_' . $i . '_county'],
													'postcode' => $row['additional_address_' . $i . '_postcode'],
													'phone' => $row['additional_address_' . $i . '_phone'],
													'modified' => mdate('%Y-%m-%d %H:%i:%s'),
													'accountID' => $accountID,
													'byID' => $this->auth->user->staffID,
													'imported' => 1,
													'orgID' => $orgID,
													'added' => mdate('%Y-%m-%d %H:%i:%s')
												);
												// check min fields
												if (!empty($data['type']) && !empty($data['address1'])) {
													$res = $this->db->insert('orgs_addresses', $data);
													$addressID = $this->db->insert_id();
													$something_imported = TRUE;

													// geocode address
													if ($res_geocode = geocode_address($data['address1'], $data['town'], $data['postcode'])) {
														$where = array(
															'addressID' => $addressID,
															'accountID' => $accountID
														);
														$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('orgs_addresses');
													}
												}
											}
										}
										if ($something_imported === TRUE) {
											$imported++;
										}
									}
									break;
								case 'Equipment':
									foreach ($rows as $row) {
										// if no data in row, skip
										if (count(array_filter($row)) == 0) {
											continue;
										}
										// look up equipment by name to see if exists
										$where = array(
											'accountID' => $accountID,
											'name' => $row['name'],
										);
										$res = $this->db->from('equipment')->where($where)->limit(1)->get();
										if ($res->num_rows() == 0) {
											// doesn't exist, create
											$data = array(
												'name' => $row['name'],
												'location' => $row['location'],
												'notes' => $row['notes'],
												'quantity' => intval($row['quantity']),
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'accountID' => $accountID,
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
											);
											// check min fields
											if (empty($data['name'])) {
												// skip
												continue;
											}
											$res = $this->db->insert('equipment', $data);
											if ($this->db->affected_rows() == 1) {
												$imported++;
											}
										}
									}
									break;
								case 'Staff':
									$team_leader_map = array();
									$mandatory_quals = array();
									$where = array(
										'accountID' => $accountID
									);
									$res = $this->db->from('mandatory_quals')->where($where)->get();
									if ($res->num_rows() > 0) {
										foreach ($res->result() as $row) {
											$mandatory_quals[$row->name] = $row->qualID;
										}
									}

									foreach ($rows as $row) {
										$something_imported = FALSE;
										// if no data in row, skip
										if (count(array_filter($row)) == 0) {
											continue;
										}
										$staffID = NULL;
										// look up staff by name and email to see if exists
										$where = array(
											'accountID' => $accountID,
											'first' => $row['first'],
											'surname' => $row['surname'],
											'email' => $row['email']
										);
										$res = $this->db->from('staff')->where($where)->limit(1)->get();
										if ($res->num_rows() > 0) {
											foreach ($res->result() as $staff) {
												$staffID = $staff->staffID;
											}
										}

										// if staff doesn't exist, create
										if (empty($staffID)) {
											// transform fields
											foreach ($row as $key => $val) {
												switch ($key) {
													case 'title':
													case 'tshirtSize':
														$row[$key] = strtolower($val);
														break;
													case 'department':
														$row[$key] = 'coaching';
														if ($department = array_search(strtolower($val), array_map('strtolower', $departments))) {
															$row[$key] = $department;
														}
														break;
													case 'contact_1_type':
													case 'contact_2_type':
														$row[$key] = NULL;
														if ($contact_type = array_search(strtolower($val), array_map('strtolower', $contact_types))) {
															$row[$key] = $contact_type;
														}
														break;
													case 'onsite':
													case 'qual_first':
													case 'qual_child':
													case 'qual_fsscrb':
													case 'proofid_passport':
													case 'proofid_nicard':
													case 'proofid_driving':
													case 'proofid_birth':
													case 'proofid_utility':
													case 'proofid_other':
													case 'driving_mot':
													case 'driving_insurance':
													case 'driving_declaration':
													case 'proof_address':
													case 'proof_nationalinsurance':
													case 'proof_quals':
													case 'proof_permit':
													case 'checklist_idcard':
													case 'checklist_paydates':
													case 'checklist_timesheet':
													case 'checklist_policy':
													case 'checklist_travel':
													case 'checklist_equal':
													case 'checklist_contract':
													case 'checklist_p45':
													case 'checklist_policies':
													case 'checklist_details':
													case 'checklist_tshirt':
													case 'checklist_idcard':
													case 'employment_probation_complete':
													case 'payments_scale_salaried':
														if (strtolower($val) == 'yes' || $val == 1) {
															$row[$key] = 1;
														} else {
															$row[$key] = 0;
														}
														break;
													case 'payments_scale_head':
													case 'payments_scale_assist':
													case 'payments_scale_salary':
													case 'target_hours':
													case 'target_utilisation';
														$row[$key] = floatval($val);
														break;
													case 'phone':
													case 'mobile':
													case 'mobile_work':
														if (!empty($val) && substr($val, 0, 1) !== '0') {
															$row[$key] = '0' . $val;
														}
														break;
												}
											}
											// prepare
											$data = array(
												'title' => $row['title'],
												'first' => $row['first'],
												'middle' => $row['middle'],
												'surname' => $row['surname'],
												'jobTitle' => $row['jobTitle'],
												'department' => $row['department'],
												'nationalInsurance' => $row['nationalInsurance'],
												'dob' => $row['dob'],
												'email' => $row['email'],
												'equal_ethnic' => $row['equal_ethnic'],
												'equal_disability' => $row['equal_disability'],
												'equal_source' => $row['equal_source'],
												'medical' => $row['medical'],
												'tshirtSize' => $row['tshirtSize'],
												'onsite' => $row['onsite'],
												'qual_first' => $row['qual_first'],
												'qual_first_expiry_date' => $row['qual_first_expiry_date'],
												'qual_child' => $row['qual_child'],
												'qual_child_expiry_date' => $row['qual_child_expiry_date'],
												'qual_fsscrb' => $row['qual_fsscrb'],
												'qual_fsscrb_expiry_date' => $row['qual_fsscrb_expiry_date'],
												'qual_fsscrb_ref' => $row['qual_fsscrb_ref'],
												'proofid_passport' => $row['proofid_passport'],
												'proofid_passport_date' => $row['proofid_passport_date'],
												'proofid_passport_ref' => $row['proofid_passport_ref'],
												'proofid_nicard' => $row['proofid_nicard'],
												'proofid_nicard_ref' => $row['proofid_nicard_ref'],
												'proofid_driving' => $row['proofid_driving'],
												'proofid_driving_date' => $row['proofid_driving_date'],
												'proofid_driving_ref' => $row['proofid_driving_ref'],
												'proofid_birth' => $row['proofid_birth'],
												'proofid_birth_date' => $row['proofid_birth_date'],
												'proofid_birth_ref' => $row['proofid_birth_ref'],
												'proofid_utility' => $row['proofid_utility'],
												'proofid_other' => $row['proofid_other'],
												'proofid_other_specify' => $row['proofid_other_specify'],
												'driving_mot' => $row['driving_mot'],
												'driving_mot_expiry' => $row['driving_mot_expiry'],
												'driving_insurance' => $row['driving_insurance'],
												'driving_insurance_expiry' => $row['driving_insurance_expiry'],
												'driving_declaration' => $row['driving_declaration'],
												'proof_address' => $row['proof_address'],
												'proof_nationalinsurance' => $row['proof_nationalinsurance'],
												'proof_quals' => $row['proof_quals'],
												'proof_permit' => $row['proof_permit'],
												'checklist_idcard' => $row['checklist_idcard'],
												'checklist_paydates' => $row['checklist_paydates'],
												'checklist_timesheet' => $row['checklist_timesheet'],
												'checklist_policy' => $row['checklist_policy'],
												'checklist_travel' => $row['checklist_travel'],
												'checklist_equal' => $row['checklist_equal'],
												'checklist_contract' => $row['checklist_contract'],
												'checklist_p45' => $row['checklist_p45'],
												'checklist_policies' => $row['checklist_policies'],
												'checklist_details' => $row['checklist_details'],
												'checklist_tshirt' => $row['checklist_tshirt'],
												'employment_start_date' => $row['employment_start_date'],
												'employment_end_date' => $row['employment_end_date'],
												'employment_probation_date' => $row['employment_probation_date'],
												'employment_probation_complete' => $row['employment_probation_complete'],
												'target_hours' => $row['target_hours'],
												'target_utilisation' => $row['target_utilisation'],
												'payments_scale_head' => $row['payments_scale_head'],
												'payments_scale_assist' => $row['payments_scale_assist'],
												'payments_scale_salaried' => $row['payments_scale_salaried'],
												'payments_scale_salary' => $row['payments_scale_salary'],
												'payments_bankName' => $row['payments_bankName'],
												'payments_sortCode' => $row['payments_sortCode'],
												'payments_accountNumber' => $row['payments_accountNumber'],
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'accountID' => $accountID,
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
											);
											// check min fields
											if (empty($data['first']) || empty($data['surname'])) {
												// skip
												continue;
											}
											$res = $this->db->insert('staff', $data);
											if ($this->db->affected_rows() == 1) {
												$staffID = $this->db->insert_id();
												$something_imported = TRUE;
												if (!empty($row['team_leader'])) {
													$team_leader_map[$staffID] = $row['team_leader'];
												}
											}

											// main address
											$mobile_work = NULL;
											if (isset($row['mobile_work'])) {
												$mobile_work = $row['mobile_work'];
											}
											$data = array(
												'type' => 'main',
												'address1' => $row['address1'],
												'address2' => $row['address2'],
												'town' => $row['town'],
												'county' => $row['county'],
												'postcode' => $row['postcode'],
												'from' => $row['from'],
												'phone' => $row['phone'],
												'mobile' => $row['mobile'],
												'mobile_work' => $mobile_work,
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'accountID' => $accountID,
												'staffID' => $staffID,
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
											);
											$res = $this->db->insert('staff_addresses', $data);

											// contact address
											for ($i=1; $i <= 2; $i++) {
												$data = array(
													'type' => $row['contact_' . $i . '_type'],
													'name' => $row['contact_' . $i . '_name'],
													'relationship' => $row['contact_' . $i . '_relationship'],
													'address1' => $row['contact_' . $i . '_address1'],
													'address2' => $row['contact_' . $i . '_address2'],
													'town' => $row['contact_' . $i . '_town'],
													'county' => $row['contact_' . $i . '_county'],
													'postcode' => $row['contact_' . $i . '_postcode'],
													'phone' => $row['contact_' . $i . '_phone'],
													'mobile' => $row['contact_' . $i . '_mobile'],
													'added' => mdate('%Y-%m-%d %H:%i:%s'),
													'modified' => mdate('%Y-%m-%d %H:%i:%s'),
													'accountID' => $accountID,
													'staffID' => $staffID,
													'byID' => $this->auth->user->staffID,
													'imported' => 1,
												);
												// check min fields
												if (!empty($data['name'])) {
													$res = $this->db->insert('staff_addresses', $data);
												}
											}
										}

										// able to deliver fields
										foreach ($row as $key => $val) {
											if ((strtolower($val) == 'yes' || $val == 1) && substr($key, 0, 8) == 'deliver_') {
												$data = array(
													'activityID' => substr($key, 8),
													'staffID' => $staffID,
													'accountID' => $accountID,
													'head' => 1,
													'added' => mdate('%Y-%m-%d %H:%i:%s'),
													'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												);
												$this->db->insert('staff_activities', $data);
											}
										}

										// mandatory qual fields
										foreach ($row as $key => $val) {
											if ((strtolower($val) == 'yes' || $val == 1) && substr($key, 0, 10) == 'mandatory_') {
												$data = array(
													'qualID' => substr($key, 10),
													'staffID' => $staffID,
													'accountID' => $accountID,
													'valid' => 1,
													'added' => mdate('%Y-%m-%d %H:%i:%s'),
													'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												);
												$this->db->insert('staff_quals_mandatory', $data);
											}
										}

										// additional quals
										for ($i = 1; $i <= 4; $i++) {
											// prepare
											$data = array(
												'name' => array_key_exists('additional_qual_' . $i . '_name', $row) ? $row['additional_qual_' . $i . '_name'] : NULL,
												'level' => array_key_exists('additional_qual_' . $i . '_level', $row) ? $row['additional_qual_' . $i . '_level'] : NULL,
												'reference' => array_key_exists('additional_qual_' . $i . '_reference', $row) ? $row['additional_qual_' . $i . '_reference'] : NULL,
												'expiry_date' => array_key_exists('additional_qual_' . $i . '_expiry_date', $row) ? $row['additional_qual_' . $i . '_expiry_date'] : NULL,
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'accountID' => $accountID,
												'byID' => $this->auth->user->staffID,
												'imported' => 1,
												'staffID' => $staffID,
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
											);
											// check min fields
											if (!empty($data['name'])) {
												$res = $this->db->insert('staff_quals', $data);
												$something_imported = TRUE;
											}
										}

										if ($something_imported === TRUE) {
											$imported++;
										}
									}

									// associate team leaders
									if (count($team_leader_map) > 0) {
										foreach ($team_leader_map as $staffID => $team_leader) {
											$leader_name = explode(" ", $team_leader);
											if (count($leader_name) == 2) {
												$where = array(
													'accountID' => $accountID,
													'first' => trim($leader_name[0]),
													'surname' => trim($leader_name[1])
												);
												$res = $this->db->from('staff')->where($where)->limit(1)->get();
												if ($res->num_rows() > 0) {
													foreach ($res->result() as $row) {
														// Insert in staff_recruitment_approvers table
														$data = array("staffID" => $staffID,
														"approverID" => $row->staffID,
														"accountID" => $accountID,
														"added" => mdate('%Y-%m-%d %H:%i:%s'),
														'modified' => mdate('%Y-%m-%d %H:%i:%s'));
														
														$this->db->insert("staff_recruitment_approvers", $data);
													}
												}
											}
										}
									}
									break;
							}
							if ($imported == 0) {
								$errors[] = 'No rows to import';
							} else {
								$success = $imported . ' row(s) imported';
							}
						}
					}

					// delete tmp file
					@unlink($upload_data['full_path']);

				} else {
					$errors[] = trim(strip_tags($this->upload->display_errors()));
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
			'account_info' => $account_info,
			'accountID' => $accountID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('accounts/import', $data);
	}

}

/* End of file Import.php */
/* Location: ./application/controllers/accounts/Import.php */
