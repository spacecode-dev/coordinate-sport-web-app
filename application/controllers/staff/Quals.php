<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Quals extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach'), array(), array('staff_management'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}

		$this->load->library('attachment_library');
	}

	/**
	 * edit quals
	 * @param  int $staffID
	 * @return void
	 */
	public function index($staffID = NULL)
	{
		$this->load->library('reports_library');
		$staff_info = new stdClass;

		// check
		if ($staffID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($staffID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Qualifications';
		$submit_to = 'staff/quals/' . $staffID;
		$return_to = $submit_to;
		$buttons = NULL;
		$icon = 'certificate';
		$tab = 'quals';
		$current_page = 'staff';
		$section = 'staff';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
		);

		$account_info = $this->db->from('accounts')->where(['accountID' => $this->auth->user->accountID])->get();

		$payroll_enabled = $account_info->row()->addon_payroll;

		$brandID = $staff_info->brandID;

		$brand_quals = [];
		if ($brandID !== NULL) {
			$res = $this->db->from('brands_quals')->where(['brandId' => $brandID])->get();
			foreach ($res->result() as $row) {
				$brand_quals[$row->qualID] = $row;
			}
		}


		// get list of mandatory quals
		$mandatory_quals = array();
		$quals_rates = [];
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		// we need here qrid => qrname + rate

		$res = $this->db->from('mandatory_quals')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {

				$rate = $this->reports_library->get_qualification_rate($staff_info, $row);

				$quals_rates[$row->qualID] = $row->name . ' (' . currency_symbol() . $rate . ')';

				$mandatory_quals[$row->qualID] = $row;
			}
		}

		if (count($brand_quals) > 0) {
			foreach ($mandatory_quals as $key => $value) {
				if (!isset($brand_quals[$key])) {
					unset($mandatory_quals[$key]);
					unset($quals_rates[$key]);
				}
			}
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			// mandatory
			$this->form_validation->set_rules('qual_first', 'First Aid', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_first_issue_date', 'First Aid - Issue Date', 'trim|xss_clean|callback_check_date');
			$this->form_validation->set_rules('qual_first_expiry_date', 'First Aid - Expiry Date', 'trim|xss_clean|callback_check_date|callback_after_issue[' . $this->input->post('qual_first_issue_date') . ']');
			$this->form_validation->set_rules('qual_first_not_required', 'First Aid - Not Required', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_child', 'Child Protection', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_child_issue_date', 'Child Protection  - Issue Date', 'trim|xss_clean|callback_check_date');
			$this->form_validation->set_rules('qual_child_expiry_date', 'Child Protection  - Expiry Date', 'trim|xss_clean|callback_check_date|callback_after_issue[' . $this->input->post('qual_child_issue_date') . ']');
			$this->form_validation->set_rules('qual_child_not_required', 'Child Protection - Not Required', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_fsscrb', 'Company DBS', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_fsscrb_issue_date', 'Company DBS - Issue Date', 'trim|xss_clean|callback_check_date');
			$this->form_validation->set_rules('qual_fsscrb_expiry_date', 'Company DBS - Expiry Date', 'trim|xss_clean|callback_check_date|callback_after_issue[' . $this->input->post('qual_fsscrb_issue_date') . ']');
			$this->form_validation->set_rules('qual_fsscrb_ref', 'Company DBS - Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_fsscrb_not_required', 'Company DBS - Not Required', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_othercrb', 'Other DBS', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_othercrb_issue_date', 'Other DBS - Issue Date', 'trim|xss_clean|callback_check_date');
			$this->form_validation->set_rules('qual_othercrb_expiry_date', 'Other DBS - Expiry Date', 'trim|xss_clean|callback_check_date|callback_after_issue[' . $this->input->post('qual_othercrb_issue_date') . ']');
			$this->form_validation->set_rules('qual_othercrb_ref', 'Other DBS - Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_othercrb_not_required', 'Other DBS - Not Required', 'trim|xss_clean');
			$this->form_validation->set_rules('qual_preferred_for_pay_rate', 'Pay Rate Level', 'trim|xss_clean|intval');

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					if($row->require_issue_expiry_date == 1){
						$this->form_validation->set_rules('mandatory_qual_'.$row->qualID.'_issue_date', $row->name.' - Issue Date', 'trim|xss_clean|callback_check_date');
						$this->form_validation->set_rules('mandatory_qual_'.$row->qualID.'_expiry_date', $row->name.' - Expiry Date', 'trim|xss_clean|callback_check_date|callback_after_issue['.$this->input->post('mandatory_qual_'.$row->qualID.'_issue_date').']');
					}
				}
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// dates
				$qual_first_issue_date = NULL;
				if (set_value('qual_first_issue_date') != '') {
					$qual_first_issue_date = uk_to_mysql_date(set_value('qual_first_issue_date'));
				}
				$qual_first_expiry_date = NULL;
				if (set_value('qual_first_expiry_date') != '') {
					$qual_first_expiry_date = uk_to_mysql_date(set_value('qual_first_expiry_date'));
				}

				$qual_child_issue_date = NULL;
				if (set_value('qual_child_issue_date') != '') {
					$qual_child_issue_date = uk_to_mysql_date(set_value('qual_child_issue_date'));
				}
				$qual_child_expiry_date = NULL;
				if (set_value('qual_child_expiry_date') != '') {
					$qual_child_expiry_date = uk_to_mysql_date(set_value('qual_child_expiry_date'));
				}

				$qual_fsscrb_issue_date = NULL;
				if (set_value('qual_fsscrb_issue_date') != '') {
					$qual_fsscrb_issue_date = uk_to_mysql_date(set_value('qual_fsscrb_issue_date'));
				}
				$qual_fsscrb_expiry_date = NULL;
				if (set_value('qual_fsscrb_expiry_date') != '') {
					$qual_fsscrb_expiry_date = uk_to_mysql_date(set_value('qual_fsscrb_expiry_date'));
				}

				$qual_othercrb_issue_date = NULL;
				if (set_value('qual_othercrb_issue_date') != '') {
					$qual_othercrb_issue_date = uk_to_mysql_date(set_value('qual_othercrb_issue_date'));
				}
				$qual_othercrb_expiry_date = NULL;
				if (set_value('qual_othercrb_expiry_date') != '') {
					$qual_othercrb_expiry_date = uk_to_mysql_date(set_value('qual_othercrb_expiry_date'));
				}

				// all ok, prepare data
				$data = array(
					'qual_first' => 0,
					'qual_first_issue_date' => $qual_first_issue_date,
					'qual_first_expiry_date' => $qual_first_expiry_date,
					'qual_first_not_required' => 0,
					'qual_child' => 0,
					'qual_child_issue_date' => $qual_child_issue_date,
					'qual_child_expiry_date' => $qual_child_expiry_date,
					'qual_child_not_required' => 0,
					'qual_fsscrb' => 0,
					'qual_fsscrb_issue_date' => $qual_fsscrb_issue_date,
					'qual_fsscrb_expiry_date' => $qual_fsscrb_expiry_date,
					'qual_fsscrb_ref' => set_value('qual_fsscrb_ref'),
					'qual_fsscrb_not_required' => 0,
					'qual_othercrb' => 0,
					'qual_othercrb_issue_date' => $qual_othercrb_issue_date,
					'qual_othercrb_expiry_date' => $qual_othercrb_expiry_date,
					'qual_othercrb_ref' => set_value('qual_othercrb_ref'),
					'qual_othercrb_not_required' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if (set_value('qual_first') == 1) {
					$data['qual_first'] = 1;
				}
				if (set_value('qual_first_not_required') == 1) {
					$data['qual_first_not_required'] = 1;
				}
				if (set_value('qual_child') == 1) {
					$data['qual_child'] = 1;
				}
				if (set_value('qual_child_not_required') == 1) {
					$data['qual_child_not_required'] = 1;
				}
				if (set_value('qual_fsscrb') == 1) {
					$data['qual_fsscrb'] = 1;
				}
				if (set_value('qual_fsscrb_not_required') == 1) {
					$data['qual_fsscrb_not_required'] = 1;
				}
				if (set_value('qual_othercrb') == 1) {
					$data['qual_othercrb'] = 1;
				}
				if (set_value('qual_othercrb_not_required') == 1) {
					$data['qual_othercrb_not_required'] = 1;
				}


                $file_count = 0;
                foreach ($_FILES['files']['size'] as $size) {
                    if ($size > 0) {
                        $file_count += 1;
                    }
                }

                $files_data = [];
                if ($file_count > 0) {
                    $upload_res = $this->crm_library->handle_multi_upload_custom_names();

                    if (count($upload_res) != $file_count) {
                        $errors[] = 'Some of files are invalid.';
                    } else {
                        foreach ($upload_res as $result) {
                            $files_data[] = [
                                'name' => $result['client_name'],
                                'path' => $result['raw_name'],
                                'type' => $result['file_type'],
                                'size' => $result['file_size']*1024,
                                'ext' => substr($result['file_ext'], 1),
                                'area' => 'mandatory_quals',
                                'belongs_to' => $result['belongs_to'],
                                'byID' => $this->auth->user->staffID,
                                'staffID' => $staffID,
                                'added' => mdate('%Y-%m-%d %H:%i:%s'),
                                'accountID' => $this->auth->user->accountID
                            ];
                        }
                    }
                }

				// final check for errors
				if (count($errors) == 0) {

					$where = array(
						'staffID' => $staffID,
						'accountID' => $this->auth->user->accountID
					);

					// update
					$query = $this->db->update('staff', $data, $where);

					// if updated
					if ($this->db->affected_rows() == 1) {

						// add/update mandatory quals
						$mandatory_quals_posted = $this->input->post('mandatory_quals');
						if (!is_array($mandatory_quals_posted)) {
							$mandatory_quals_posted = array();
						}
						$mandatory_quals_not_required_posted = $this->input->post('mandatory_quals_not_required');
						if (!is_array($mandatory_quals_not_required_posted)) {
							$mandatory_quals_not_required_posted = array();
						}
						foreach ($mandatory_quals as $qualID => $qual) {
							$where = array(
								'staffID' => $staffID,
								'qualID' => $qualID,
								'accountID' => $this->auth->user->accountID
							);

							// look up, see if already exists
							$res = $this->db->from('staff_quals_mandatory')->where($where)->get();

							if ($qualID === $this->input->post('qual_preferred_for_pay_rate')) {
								$is_preferred = 1;
							} else {
								$is_preferred = 0;
							}

							if (!$payroll_enabled) {
								$is_preferred = 0;
							}

							$data = array(
								'staffID' => $staffID,
								'qualID' => $qualID,
								'accountID' => $this->auth->user->accountID,
								'valid' => 0,
								'not_required' => 0,
								'preferred_for_pay_rate' => $is_preferred,
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);

							if($qual->require_issue_expiry_date == 1){
								$issue_date = NULL;
								$expiry_date = NULL;
								if (set_value('mandatory_qual_'.$qualID.'_issue_date') != '') {
									$issue_date = uk_to_mysql_date(set_value('mandatory_qual_'.$qualID.'_issue_date'));
								}
								if (set_value('mandatory_qual_'.$qualID.'_expiry_date') != '') {
									$expiry_date = uk_to_mysql_date(set_value('mandatory_qual_'.$qualID.'_expiry_date'));
								}
								$data["issue_date"] = $issue_date;
								$data["expiry_date"] = $expiry_date;
							}
							if($qual->require_reference == 1){
								$reference_num = NULL;
								if (set_value('mandatory_qual_'.$qualID.'_ref') != '') {
									$reference_num = set_value('mandatory_qual_'.$qualID.'_ref');
								}
								$data["reference"] = $reference_num;
							}

							if (in_array($qualID, $mandatory_quals_posted)) {
								$data['valid'] = 1;
							}

							if (in_array($qualID, $mandatory_quals_not_required_posted)) {
								$data['not_required'] = 1;
							}

							if ($res->num_rows() > 0) {
								$this->db->update('staff_quals_mandatory', $data, $where);
							} else {
								$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
								$this->db->insert('staff_quals_mandatory', $data);
							}
						}

						if (!empty($files_data)) {
						    foreach ($files_data as $data) {
                                $this->db->insert('staff_attachments', $data);
                            }
                        }

						$this->session->set_flashdata('success', 'Qualifications have been updated successfully.');

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

		// staff mandatory quals
		$mandatory_quals_array = array();
		$mandatory_quals_not_required_array = array();
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('staff_quals_mandatory')->where($where)->get();

		$qual_preferred_for_pay_rate = NULL;

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$mandatory_quals_array[$row->qualID] = $row;
				if ((bool)$row->preferred_for_pay_rate) {
					$qual_preferred_for_pay_rate = $row->qualID;
				}
			}
		}

		$attachments = $this->attachment_library->getQualAttachments($staffID, 'mandatory_quals', $this->auth->user->accountID);

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'staff_info' => $staff_info,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'mandatory_quals' => $mandatory_quals,
			'mandatory_quals_array' => $mandatory_quals_array,
			'mandatory_quals_not_required_array' => $mandatory_quals_not_required_array,
			'mandatory_quals_rates' => $quals_rates, // for dropdown,
			'mandatory_qual_preferred_for_pay_rate' => $qual_preferred_for_pay_rate,
			'payroll_enabled' => $payroll_enabled,
            'attachments' => $attachments
		);

		// load view
		$this->crm_view('staff/quals', $data);
	}

	/**
	 * edit quals
	 * @param  int $staffID
	 * @return void
	 */
	public function deliver($staffID = NULL)
	{
		$this->load->library('reports_library');
		$staff_info = new stdClass;

		// check
		if ($staffID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($staffID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Qualifications';
		$submit_to = 'staff/quals/abl-deliver/' . $staffID;
		$return_to = $submit_to;
		$buttons = NULL;
		$icon = 'certificate';
		$tab = 'quals';
		$current_page = 'staff';
		$section = 'staff';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
		);

		$account_info = $this->db->from('accounts')->where(['accountID' => $this->auth->user->accountID])->get();

		$payroll_enabled = $account_info->row()->addon_payroll;


		// get list of activities
		$activities = array();
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$res = $this->db->from('activities')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$activities[$row->activityID] = $row->name;
			}
		}

		$brandID = $staff_info->brandID;

		$brand_activities = [];
		if ($brandID !== NULL) {
			$res = $this->db->from('brands_activities')->where(['brandId' => $brandID])->get();
			foreach ($res->result() as $row) {
				$brand_activities[$row->activityID] = $row;
			}
		}

		// we need here qrid => qrname + rate

		if (count($brand_activities) > 0) {
			foreach ($activities as $key => $value) {
				if (!isset($brand_activities[$key])) {
					unset($activities[$key]);
				}
			}
		}

		// if posted
		if ($this->input->post()) {

			// add/update staff activities
			$activities_posted = $this->input->post('activities');
			if (!is_array($activities_posted)) {
				$activities_posted = array();
			}
			foreach ($activities as $activityID => $activity) {
				$where = array(
					'staffID' => $staffID,
					'activityID' => $activityID,
					'accountID' => $this->auth->user->accountID
				);
				if (!isset($activities_posted[$activityID])) {
					// not set, remove
					$this->db->delete('staff_activities', $where);
				} else {
					// look up, see if already exists
					$res = $this->db->from('staff_activities')->where($where)->get();

					$data = array(
						'staffID' => $staffID,
						'activityID' => $activityID,
						'head' => 0,
						'lead' => 0,
						'assistant' => 0,
						'accountID' => $this->auth->user->accountID,
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);

					if (isset($activities_posted[$activityID]['head']) && $activities_posted[$activityID]['head'] == 1) {
						$data['head'] = 1;
					}
					if (isset($activities_posted[$activityID]['lead']) && $activities_posted[$activityID]['lead'] == 1) {
						$data['lead'] = 1;
					}
					if (isset($activities_posted[$activityID]['assistant']) && $activities_posted[$activityID]['assistant'] == 1) {
						$data['assistant'] = 1;
					}

					if ($res->num_rows() > 0) {
						$this->db->update('staff_activities', $data, $where);
					} else {
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$this->db->insert('staff_activities', $data);
					}
				}
			}
			redirect($return_to);
			return TRUE;

		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// staff activities
		$activities_array = array();
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('staff_activities')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$activities_array[$row->activityID] = array(
					'head' => $row->head,
					'lead' => $row->lead,
					'assistant' => $row->assistant
				);
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'staff_info' => $staff_info,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'activities' => $activities,
			'activities_array' => $activities_array,
			'payroll_enabled' => $payroll_enabled
		);

		// load view
		$this->crm_view('staff/abl-deliver', $data);
	}

	/**
	 * show list of additional qualifciations
	 * @return void
	 */
	public function additional($staffID = NULL) {

		if ($staffID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('staff')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$staff_info = $row;
		}

		// set defaults
		$icon = 'certificate';
		$tab = 'quals';
		$current_page = 'staff';
		$page_base = 'staff/quals/additional/' . $staffID;
		$section = 'staff';
		$title = 'Additional Qualifications';
		$buttons = '<a class="btn" href="' . site_url('staff/quals/' . $staffID) . '"><i class="far fa-angle-left"></i> Return</a> <a class="btn btn-success" href="' . site_url('staff/quals/' . $staffID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
			'staff/quals/' . $staffID => 'Qualifications'
		);

		// set where
		$where = array(
			'staff_quals.staffID' => $staffID,
			'staff_quals.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'issue_from' => NULL,
			'issue_to' => NULL,
			'expiry_from' => NULL,
			'expiry_to' => NULL,
			'name' => NULL,
			'level' => NULL,
			'ref' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_issue_from', 'Issue Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_issue_to', 'Issue Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_expiry_from', 'Expiry Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_expiry_to', 'Expiry Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_level', 'Level', 'trim|xss_clean');
			$this->form_validation->set_rules('search_ref', 'Qualification No.', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['issue_from'] = set_value('search_issue_from');
			$search_fields['issue_to'] = set_value('search_issue_to');
			$search_fields['expiry_from'] = set_value('search_expiry_from');
			$search_fields['expiry_to'] = set_value('search_expiry_to');
			$search_fields['name'] = set_value('search_name');
			$search_fields['level'] = set_value('search_level');
			$search_fields['ref'] = set_value('search_ref');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-staff-quals'))) {

			foreach ($this->session->userdata('search-staff-quals') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-staff-quals', $search_fields);

			if ($search_fields['issue_from'] != '') {
				$issue_from = uk_to_mysql_date($search_fields['issue_from']);
				if ($issue_from !== FALSE) {
					$search_where[] = "`issue_date` IS NOT NULL";
					$search_where[] = "`issue_date` >= " . $this->db->escape($issue_from);
				}
			}

			if ($search_fields['issue_to'] != '') {
				$issue_to = uk_to_mysql_date($search_fields['issue_to']);
				if ($issue_to !== FALSE) {
					$search_where[] = "`issue_date` IS NOT NULL";
					$search_where[] = "`issue_date` <= " . $this->db->escape($issue_to);
				}
			}

			if ($search_fields['expiry_from'] != '') {
				$expiry_from = uk_to_mysql_date($search_fields['expiry_from']);
				if ($expiry_from !== FALSE) {
					$search_where[] = "`expiry_date` IS NOT NULL";
					$search_where[] = "`expiry_date` >= " . $this->db->escape($expiry_from);
				}
			}

			if ($search_fields['expiry_to'] != '') {
				$expiry_to = uk_to_mysql_date($search_fields['expiry_to']);
				if ($expiry_to !== FALSE) {
					$search_where[] = "`expiry_date` IS NOT NULL";
					$search_where[] = "`expiry_date` <= " . $this->db->escape($expiry_to);
				}
			}

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['level'] != '') {
				$search_where[] = "`level` LIKE '%" . $this->db->escape_like_str($search_fields['level']) . "%'";
			}

			if ($search_fields['ref'] != '') {
				$search_where[] = "`reference` LIKE '%" . $this->db->escape_like_str($search_fields['ref']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('staff_quals')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc, expiry_date desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('staff_quals')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc, expiry_date desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$attachments = $this->attachment_library->getQualAttachments($staffID, 'additional_quals', $this->auth->user->accountID);

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'quals' => $res,
			'staff_info' => $staff_info,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
            'attachments' => $attachments
		);

		// load view
		$this->crm_view('staff/quals-additional', $data);
	}

	/**
	 * edit a qualification
	 * @param  int $qualID
	 * @param int $staffID
	 * @return void
	 */
	public function edit($qualID = NULL, $staffID = NULL)
	{

		$qual_info = new stdClass();

		// check if editing
		if ($qualID != NULL) {

			// check if numeric
			if (!ctype_digit($qualID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'qualID' => $qualID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('staff_quals')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$qual_info = $row;
				$staffID = $qual_info->staffID;
			}

		}

		// required
		if ($staffID == NULL) {
			show_404();
		}

		// look up staff
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Qualification';
		if ($qualID != NULL) {
			$submit_to = 'staff/quals/edit/' . $qualID;
			$title = $qual_info->name;
		} else {
			$submit_to = 'staff/quals/' . $staffID . '/new/';
		}
		$return_to = 'staff/quals/additional/' . $staffID;
		$icon = 'certificate';
		$tab = 'quals';
		$current_page = 'staff';
		$section = 'staff';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
			'staff/quals/' . $staffID => 'Qualifications',
			'staff/quals/additional/' . $staffID => 'Additional Qualifications'
		);

		$attachmentInfo = null;
		if ($qualID) {
            $attachmentInfo = $this->attachment_library->getAttachmentInfoByQualification($qualID, 'additional_quals', $this->auth->user->accountID);
        }


		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('level', 'Level', 'trim|xss_clean');
			$this->form_validation->set_rules('reference', 'Qualification No.', 'trim|xss_clean');
			$this->form_validation->set_rules('issue_date', 'Expiry', 'trim|xss_clean|callback_check_date');
			$this->form_validation->set_rules('expiry_date', 'Expiry', 'trim|xss_clean|callback_check_date|callback_after_issue[' . $this->input->post('issue_date') . ']');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'level' => set_value('level'),
					'reference' => set_value('reference'),
					'issue_date' => NULL,
					'expiry_date' => NULL,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (set_value('issue_date') != '') {
					$data['issue_date'] = uk_to_mysql_date(set_value('issue_date'));
				}

				if (set_value('expiry_date') != '') {
					$data['expiry_date'] = uk_to_mysql_date(set_value('expiry_date'));
				}

				if ($qualID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['staffID'] = $staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($qualID == NULL) {
						// insert
						$query = $this->db->insert('staff_quals', $data);

					} else {
						$where = array(
							'qualID' => $qualID
						);

						// update
						$query = $this->db->update('staff_quals', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

					    $errors = null;
                        if (!empty($_FILES['file']['name'])) {
                            $upload_res = $this->crm_library->handle_upload();

                            $data = [];
                            if ($upload_res === NULL) {
                                // on edit, might just be changing comment
                                $errors[] = 'A valid file is required.';
                            } else {

                                if (!$qualID) {
                                    $qualID = $this->db->insert_id();
                                }

                                if (!empty($attachmentInfo)) {
                                    // delete previous file, if exists
                                    $path = UPLOADPATH;
                                    if (file_exists($path . $attachment_info->path)) {
                                        unlink($path . $attachment_info->path);
                                    }
                                }

                                $this->attachment_library->addAttachement([
                                    'name' => $upload_res['client_name'],
                                    'path' => $upload_res['raw_name'],
                                    'type' => $upload_res['file_type'],
                                    'size' => $upload_res['file_size']*1024,
                                    'ext' => substr($upload_res['file_ext'], 1),
                                    'area' => 'additional_quals',
                                    'belongs_to' => $qualID,
                                    'byID' => $this->auth->user->staffID,
                                    'staffID' => $staffID,
                                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                                    'accountID' => $this->auth->user->accountID
                                ]);


                            }

                        }
                        if ($errors) {
//                            $this->session->set_flashdata('info', $errors[0]);
                        } else {
                            if ($staffID == NULL) {
                                $this->session->set_flashdata('success', set_value('name') . ' has been created successfully.');
                            } else {
                                $this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
                            }

                            redirect($return_to);

                            return TRUE;
                        }
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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'qual_info' => $qual_info,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
            'attachment_info' => $attachmentInfo
		);

		// load view
		$this->crm_view('staff/qual', $data);
	}

	/**
	 * delete a qual
	 * @param  int $staffID
	 * @return mixed
	 */
	public function remove($qualID = NULL) {

		// check params
		if (empty($qualID)) {
			show_404();
		}

		$where = array(
			'qualID' => $qualID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff_quals')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$qual_info = $row;

			// all ok, delete
			$query = $this->db->delete('staff_quals', $where);

			if ($this->db->affected_rows() == 1) {
			    //remove attachment
                $attachmentInfo = $this->attachment_library->getAttachmentInfoByQualification($qualID, 'additional_quals', $this->auth->user->accountID);

                if (!empty($attachmentInfo)) {
                    $this->attachment_library->removeAttachment($attachmentInfo->attachmentID);
                }

				$this->session->set_flashdata('success', $qual_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $qual_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'staff/quals/additional/' . $qual_info->staffID;

			redirect($redirect_to);
		}
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
	public function after_issue($endDate, $startDate) {

		if (empty($endDate) || empty($startDate)) {
			return TRUE;
		}

		$startDate = strtotime(uk_to_mysql_date($startDate));
		$endDate = strtotime(uk_to_mysql_date($endDate));

		if ($endDate >= $startDate) {
			return TRUE;
		}

		return FALSE;

	}

}

/* End of file quals.php */
/* Location: ./application/controllers/staff/quals.php */
