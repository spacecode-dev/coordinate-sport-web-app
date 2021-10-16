<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mandatoryquals extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));

		$this->load->library('attachment_library');
		$this->load->library('qualifications_library');
	}

	/**
	 * show list of mandatory_quals
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'mandatory_quals';
		$page_base = 'settings/mandatoryquals';
		$section = 'settings';
		$title = 'Mandatory Qualifications';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/mandatoryquals/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
		);

		// set where
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'search' => NULL,
			'name' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-mandatory_quals'))) {

			foreach ($this->session->userdata('search-mandatory_quals') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-mandatory_quals', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('mandatory_quals')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('mandatory_quals')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'mandatory_quals' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/mandatoryquals', $data);
	}

	/**
	 * edit a qual
	 * @param  int $qualID
	 * @return void
	 */
	public function edit($qualID = NULL)
	{
		$qual_info = new stdClass();

		// check if editing
		if ($qualID != NULL) {

			// check if numeric
			if (!ctype_digit($qualID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'qualID' => $qualID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('mandatory_quals')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$qual_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Qualification';
		if ($qualID != NULL) {
			$submit_to = 'settings/mandatoryquals/edit/' . $qualID;
			$title = $qual_info->name;
		} else {
			$submit_to = 'settings/mandatoryquals/new/';
		}
		$return_to = 'settings/mandatoryquals';
		$icon = 'cog';
		$current_page = 'mandatory_quals';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/mandatoryquals' => 'Mandatory Qualifications'
		);

		$session_types = $this->db->from('lesson_types')->where([
			'accountID' => $this->auth->user->accountID
		])->get()->result();

		$session_rates = $this->db->from('session_qual_rates')->where([
			'accountID' => $this->auth->user->accountID,
			'qualTypeID' => $qualID
		])->get()->result();

		$rates = [];
		if (count($session_rates) > 0) {
			foreach ($session_rates as $key => $value) {
				$rates[$value->lessionTypeID]['pay_rate'] = $value->pay_rate;
				$rates[$value->lessionTypeID]['increased_pay_rate'] = $value->increased_pay_rate;
			}
		}

		if (count($rates) < 1) {
			foreach ($session_types as $type) {
				$rates[$type->typeID]['pay_rate'] = 0;
				$rates[$type->typeID]['increased_pay_rate'] = 0;
			}
		}

		foreach ($session_types as $type) {
			$rates[$type->typeID]['session_rate_overwrite'] = false;
			if ($type->hourly_rate > 0) {
				$rates[$type->typeID]['session_rate_overwrite'] = true;
			}
		}

		//get tags for validation
		$tags = $this->qualifications_library->getAllTags($this->auth->user->accountID);

		if (isset($qual_info->tag) && ($key = array_search($qual_info->tag, $tags)) !== false) {
			unset($tags[$key]);
		}
		$tags = array_filter(array_merge($tags, $this->settings_library->get_tags('new_booking_email')));
		$tags = implode(',', $tags);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('tag', 'Tag', 'trim|xss_clean|not_in_list[' . $tags . ']');
			$this->form_validation->set_rules('hourly_rate', 'Standard Hourly Rate', 'trim|xss_clean|greater_than[-1]');
			$this->form_validation->set_rules('length_increment', 'Use Increased Rate After X Months Service', 'trim|xss_clean|greater_than[-1]|integer');
			$this->form_validation->set_rules('incremental_rate', 'Increased Rate', 'trim|xss_clean|greater_than[-1]');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'tag' => set_value('tag'),
					'require_issue_expiry_date' => set_value('require_issue_expiry_date'),
					'require_reference' => set_value('require_reference'),
					'hourly_rate' => set_value('hourly_rate'),
					'length_increment' => set_value('length_increment'),
					'incremental_rate' => set_value('incremental_rate'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				$session_override_rate = set_value('session_override_rate');
				$session_increased_override_rate = set_value('session_increased_override_rate');

				if ($qualID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($qualID == NULL) {
						// insert
						$query = $this->db->insert('mandatory_quals', $data);

						$qualID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'qualID' => $qualID
						);

						// update
						$query = $this->db->update('mandatory_quals', $data, $where);
					}

					$this->db->delete('session_qual_rates', ['qualTypeID' => $qualID]);

					foreach ($session_types as $type) {
						if (isset($session_override_rate[$type->typeID]) &&
							isset($session_increased_override_rate[$type->typeID])) {
								foreach($session_override_rate[$type->typeID] as $key => $value){
									if(strlen(trim($value)) == 0){ 									 
										$session_override_rate[$type->typeID][$key]  ='0.00' ;
									} 
								}

								foreach($session_increased_override_rate[$type->typeID] as $key => $value){
									if(strlen(trim($value)) == 0){ 									 
										$session_increased_override_rate[$type->typeID][$key]  ='0.00' ;
									}									 
								}								 
						    
							$this->db->insert('session_qual_rates', [
								'accountID' => $this->auth->user->accountID,
								'lessionTypeID' => $type->typeID,
								'qualTypeID' => $qualID,
								'pay_rate' => json_encode($session_override_rate[$type->typeID]),
								'increased_pay_rate' => json_encode($session_increased_override_rate[$type->typeID]),
							]);
						}
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if (isset($just_added)) {
							$this->session->set_flashdata('success', set_value('name') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
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

		$staff_types = [
			'head' => $this->settings_library->get_staffing_type_label('head'),
			'lead' => $this->settings_library->get_staffing_type_label('lead'),
			'assistant' => $this->settings_library->get_staffing_type_label('assistant'),
		];
		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'qual_info' => $qual_info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'session_types' => $session_types,
			'session_rates' => $rates,
			'staffing_types' => $staff_types
		);

		// load view
		$this->crm_view('settings/mandatoryqual', $data);
	}

	/**
	 * delete a qual
	 * @param  int $qualID
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
		$query = $this->db->from('mandatory_quals')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$qual_info = $row;

			// all ok, delete
			$query = $this->db->delete('mandatory_quals', $where);

			if ($this->db->affected_rows() == 1) {
				//remove attachment
				$attachmentInfo = $this->attachment_library->getAttachmentInfoByQualification($qualID, 'mandatory_quals', $this->auth->user->accountID);

				if (!empty($attachmentInfo)) {
					$this->attachment_library->removeAttachment($attachmentInfo->attachmentID);
				}

				$this->session->set_flashdata('success', $qual_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $qual_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/mandatoryquals';

			redirect($redirect_to);
		}
	}
}

/* End of file mandatory_quals.php */
/* Location: ./application/controllers/settings/mandatoryquals.php */
