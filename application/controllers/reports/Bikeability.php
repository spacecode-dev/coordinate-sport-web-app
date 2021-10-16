<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bikeability extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports', 'bikeability'));
	}

	public function index($action = FALSE) {

		// set defaults
		$icon = 'book';
		$current_page = 'bikeability';
		$section = 'reports';
		$page_base = 'reports/bikeability';
		$title = 'Bikeability Report';
		$buttons = ' <a class="btn btn-primary" href="' . site_url('reports/bikeability/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
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

		// set up search
		$search_fields = array(
			'type' => 'children_individuals',
			'date_from' => NULL,
			'date_to' => NULL,
			'org_id' => NULL,
			'brand_id' => NULL,
			'search' => NULL,
			'project_id' => NULL
		);
		$is_search = FALSE;
		$search_where = array();

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_type', 'Register Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_org_id', 'School', 'trim|xss_clean');
			$this->form_validation->set_rules('search_brand_id', 'Department', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['type'] = set_value('search_type');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['org_id'] = set_value('search_org_id');
			$search_fields['brand_id'] = set_value('search_brand_id');
			$search_fields['search'] = set_value('search');
			$search_fields['project_id'] = set_value('search_project_id');

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

		if (isset($is_search) && $is_search === TRUE) {
			// store search fields
			$this->session->set_userdata('search-reports', $search_fields);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// get report data
		$report_data = array();

		switch ($search_fields['type']) {
			case 'children_individuals':
			default:
				// get data
				$where = array(
					'bookings_lessons.accountID' => $this->auth->user->accountID,
					'bookings_cart_sessions.date <=' => uk_to_mysql_date($search_fields['date_to']),
					'bookings_cart_sessions.date >=' => uk_to_mysql_date($search_fields['date_from']),
					'bookings_cart.type' => 'booking'
				);
				if (!empty($search_fields['brand_id'])) {
					$where['bookings.brandID'] = $search_fields['brand_id'];
				}
				if (!empty($search_fields['project_id'])) {
					$where['bookings.bookingID'] = $search_fields['project_id'];
				}
				$res = $this->db->select('bookings_cart_sessions.date, bookings_cart_sessions.bikeability_level, bookings_lessons.lessonID, bookings_lessons.startTime, bookings_lessons.endTime, bookings_lessons.day, bookings_lessons.startDate as lesson_startDate, bookings_lessons.endDate as lesson_endDate, bookings_blocks.startDate, bookings_blocks.endDate, bookings.register_type, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, family_contacts.postcode as contact_postcode, family_contacts.email as contact_email, family_contacts.gender as contact_gender, family_contacts.dob as contact_dob, family_children.first_name as child_first, family_children.last_name as child_last, family_children.gender as child_gender, booker.email as booker_email, booker.postcode as booker_postcode, brands.name as brand, family_children.dob as child_dob')
					->from('bookings_cart_sessions')
					->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
					->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
					->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
					->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
					->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
					->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
					->join('family_contacts as booker', 'bookings_cart.contactID = booker.contactID', 'left')
					->join('brands', 'bookings.brandID = brands.brandID', 'left')
					->where($where)
					->where_in('bookings.register_type', array('children_bikeability', 'individuals_bikeability'))
					->group_by('bookings_cart_sessions.sessionID')
					->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {

						$report_row = array(
							'instructor' => NULL,
							'date' => mysql_to_uk_date($row->date),
							'startTime' => substr($row->startTime, 0, 5),
							'endTime' => substr($row->endTime, 0, 5),
							'session_type' => $row->brand,
							'trainee_name' => NULL,
							'trainee_email' => NULL,
							'trainee_postcode' => NULL,
							'level_at_start' => NULL,
							'level_at_end' => $row->bikeability_level,
							'lesson_outcome' => NULL,
							'gender' => NULL,
							'age' => NULL
						);

						// if no date, work out from lesson/block and day
						if (empty($row->date)) {
							// loop through lesson/block to find date
							$start_date = $row->startDate;
							$end_date = $row->endDate;
							if (!empty($row->lesson_startDate)) {
								$start_date = $row->lesson_startDate;
							}
							if (!empty($row->lesson_endDate)) {
								$end_date = $row->lesson_endDate;
							}
							$date = $start_date;
							while (strtotime($date) <= strtotime($end_date)) {
								if (strtolower(date("l", strtotime($date))) == $row->day) {
									$row->date = $date;
									$report_row['date'] = mysql_to_uk_date($date);
									break;
								}
								$date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
							}
							// if no date, don't process
							if (empty($row->date)) {
								continue;
							}
						}

						// switch fields
						$dob = NULL;
						switch ($row->register_type) {
							case 'children_bikeability':
								$report_row['trainee_name'] = $row->child_first . ' ' . $row->child_last;
								$report_row['trainee_email'] = $row->booker_email;
								$report_row['trainee_postcode'] = $row->booker_postcode;
								$report_row['gender'] = ucwords($row->child_gender);
								$dob = $row->child_dob;
								break;
							case 'individuals_bikeability':
								$report_row['trainee_name'] = $row->contact_first . ' ' . $row->contact_last;
								$report_row['trainee_email'] = $row->contact_email;
								$report_row['trainee_postcode'] = $row->contact_postcode;
								$report_row['gender'] = ucwords($row->contact_gender);
								$dob = $row->contact_dob;
								break;
						}

						// calc age range
						if (!empty($dob)) {
							$age = calculate_age($dob);
							if ($age  !== FALSE) {
								if ($age < 5) {
									$report_row['age'] = 'Under 5';
								} else if ($age <= 8) {
									$report_row['age'] = '5-8';
								} else if ($age <= 12) {
									$report_row['age'] = '9-12';
								} else if ($age <= 15) {
									$report_row['age'] = '13-15';
								} else if ($age <= 17) {
									$report_row['age'] = '16-17';
								} else if ($age <= 24) {
									$report_row['age'] = '18-24';
								} else if ($age <= 34) {
									$report_row['age'] = '25-34';
								} else if ($age <= 44) {
									$report_row['age'] = '35-44';
								} else if ($age <= 54) {
									$report_row['age'] = '45-54';
								} else if ($age <= 64) {
									$report_row['age'] = '55-64';
								} else if ($age >=65 ) {
									$report_row['age'] = '65+';
								}
							}
						}

						// look up staff
						$where = array(
							'bookings_lessons_staff.accountID' => $this->auth->user->accountID,
							'bookings_lessons_staff.lessonID' => $row->lessonID,
							'bookings_lessons_staff.startDate <=' => $row->date,
							'bookings_lessons_staff.endDate >=' => $row->date,
						);
						$res_staff = $this->db->select('staff.first, staff.surname')->from('bookings_lessons_staff')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->where($where)->get();
						if ($res_staff->num_rows() > 0) {
							$lesson_staff = array();
							foreach ($res_staff->result() as $staff) {
								$lesson_staff[] = $staff->first . ' ' . $staff->surname;
							}
							if (count($lesson_staff) > 0) {
								sort($lesson_staff);
								$report_row['instructor'] = implode(", ", $lesson_staff);
							}
						}

						// save
						$key = $row->date . ' ' . $row->startTime . ' ' . $row->endTime . ' ' . md5(serialize($report_row));
						$report_data[$key] = $report_row;
					}
				}
				// sort by date
				ksort($report_data);
				break;
			case 'names':
				// get data
				$where = array(
					'bookings_lessons.accountID' => $this->auth->user->accountID,
					'bookings.register_type' => 'bikeability',
					'bookings_blocks.startDate <=' => uk_to_mysql_date($search_fields['date_to']),
					'bookings_blocks.endDate >=' => uk_to_mysql_date($search_fields['date_from']),
				);

				if (!empty($search_fields['brand_id'])) {
					$where['bookings.brandID'] = $search_fields['brand_id'];
				}
				if (!empty($search_fields['project_id'])) {
					$where['bookings.bookingID'] = $search_fields['project_id'];
				}
				$res = $this->db->select('bookings_lessons.lessonID, bookings_lessons.startTime, bookings_lessons.endTime,
						bookings_lessons.group, bookings_lessons.group_other, bookings_lessons.day,
						bookings_lessons.startDate as lesson_startDate, bookings_lessons.endDate as lesson_endDate,
						orgs.name as org, block_orgs.name as block_org, bookings.orgID, bookings_blocks.name as block_name,
						bookings_blocks.blockID, bookings_blocks.orgID as block_orgID, bookings_blocks.startDate,
						bookings_blocks.endDate, bookings.monitoring1 as bookings_monitoring1,
						bookings.monitoring2 as bookings_monitoring2, bookings.monitoring3 as bookings_monitoring3,
						bookings.monitoring4 as bookings_monitoring4, bookings.monitoring5 as bookings_monitoring5')
					->from('bookings_lessons')
					->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
					->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
					->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
					->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
					->group_by('bookings_lessons.blockID')
					->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate asc')
					->where($where)->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						// if searching by org, limit
						if (!empty($search_fields['org_id'])) {
							if ((!empty($row->block_orgID) && $search_fields['org_id'] != $row->block_orgID) || (empty($row->block_orgID) && $search_fields['org_id'] != $row->orgID)) {
								continue;
							}
						}

						$report_row = array(
							'school' => $row->org,
							'dates' => mysql_to_uk_date($row->startDate),
							'startTime' => substr($row->startTime, 0, 5),
							'endTime' => substr($row->endTime, 0, 5),
							'block' => $row->block_name,
							'level_taught' => NULL,
							'girls_participating' => 0,
							'girls_send' => 0,
							'girls_achieved_l1' => 0,
							'girls_achieved_l2' => 0,
							'girls_achieved_l3' => 0,
							'boys_participating' => 0,
							'boys_send' => 0,
							'boys_achieved_l1' => 0,
							'boys_achieved_l2' => 0,
							'boys_achieved_l3' => 0
						);
						if (!empty($row->block_org)) {
							$report_row['school'] = $row->block_org;
						}
						if ($row->startDate != $row->endDate) {
							$report_row['dates'] .= '-' . mysql_to_uk_date($row->endDate);
						}

						// get gender and SEND monitoring fields
						$gender_field = NULL;
						$send_field = NULL;
						for ($i = 1; $i <= 5; $i++) {
							$monitoring_field = 'bookings_monitoring' . $i;
							if (empty($gender_field) && strtolower($row->$monitoring_field) == 'gender') {
								$gender_field = 'monitoring' . $i;
							}
							if (empty($send_field) && in_array(strtolower($row->$monitoring_field), array('sen', 'send'))) {
								$send_field = 'monitoring' . $i;
							}
						}

						if (!empty($gender_field)) {
							// look up attendees
							$where = array(
								'bookings_attendance_names.accountID' => $this->auth->user->accountID,
								'bookings_attendance_names.blockID' => $row->blockID
							);
							$res_attendees = $this->db->from('bookings_attendance_names')->where($where)->get();
							if ($res_attendees->num_rows() > 0) {
								foreach ($res_attendees->result() as $attendee) {
									if (strtolower($attendee->$gender_field) == 'male' || strtolower($attendee->$gender_field) == 'm') {
										$key = 'boys';
									} else if (strtolower($attendee->$gender_field) == 'female' || strtolower($attendee->$gender_field) == 'f') {
										$key = 'girls';
									} else {
										continue;
									}
									// increase gender count
									$report_row[$key . '_participating']++;
									// increase level achieved
									switch (intval($attendee->bikeability_level)) {
										case 1:
											$report_row[$key . '_achieved_l1']++;
											break;
										case 2:
											$report_row[$key . '_achieved_l1']++;
											$report_row[$key . '_achieved_l2']++;
											break;
										case 3:
											$report_row[$key . '_achieved_l3']++;
											break;
									}
									// if send
									if (!empty($send_field) && !empty($attendee->$send_field) && strtolower($attendee->$send_field) !== 'no' && strtolower($attendee->$send_field) !== 'n') {
										$report_row[$key . '_send']++;
									}
								}
							}
						}
						$report_data[] = $report_row;
					}
				}
				break;
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get schools
		$where = array(
			'orgs.accountID' => $this->auth->user->accountID,
			'orgs.type' => 'school'
		);
		$orgs = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// get brands
		$where = array(
			'brands.accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		$projects = $this->db->from('bookings')->where([
			'bookings.accountID' => $this->auth->user->accountID,
			'project' => 1
		])->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'report_data' => $report_data,
			'orgs' => $orgs,
			'brands' => $brands,
			'page_base' => $page_base,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'projects' => $projects
		);

		// load view
		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');
			if ($search_fields['type'] == 'names') {
				$this->load->view('reports/bikeability-export-names', $data);
			} else {
				$this->load->view('reports/bikeability-export', $data);
			}
		} else {
			$this->crm_view('reports/bikeability', $data);
		}
	}

}

/* End of file bikeability.php */
/* Location: ./application/controllers/reports/bikeability.php */
