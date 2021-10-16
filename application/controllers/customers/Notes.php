<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notes extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any customer types
		if (!$this->auth->has_features('customers_schools') && !$this->auth->has_features('customers_schools_prospects') && !$this->auth->has_features('customers_orgs') && !$this->auth->has_features('customers_orgs_prospects')) {
			show_403();
		}
	}

	/**
	 * show list of notes
	 * @return void
	 */
	public function index($org_id = NULL) {

		if ($org_id == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $org_id,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'book';
		$tab = 'notes';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $org_id] = $org_info->name;
		$page_base = 'customers/notes/' . $org_id;
		$section = 'customers';
		$title = 'Notes';
		$buttons = '<a class="btn btn-success" href="' . site_url('customers/notes/' . $org_id . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'orgs_notes.orgID' => $org_id,
			'orgs_notes.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'summary' => NULL,
			'content' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_summary', 'Summary', 'trim|xss_clean');
			$this->form_validation->set_rules('search_content', 'Content', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['summary'] = set_value('search_summary');
			$search_fields['content'] = set_value('search_content');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-customer-notes'))) {

			foreach ($this->session->userdata('search-customer-notes') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-customer-notes', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`added` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`added` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['summary'] != '') {
				$search_where[] = "`summary` LIKE '%" . $this->db->escape_like_str($search_fields['summary']) . "%'";
			}

			if ($search_fields['content'] != '') {
				$search_where[] = "`content` LIKE '%" . $this->db->escape_like_str($search_fields['content']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('orgs_notes')->where($where)->where($search_where, NULL, FALSE)->order_by('added desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('orgs_notes')->where($where)->where($search_where, NULL, FALSE)->order_by('added desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'notes' => $res,
			'org_id' => $org_id,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/notes', $data);
	}

	/**
	 * edit a note
	 * @param  int $noteID
	 * @param int $orgID
	 * @return void
	 */
	public function edit($noteID = NULL, $orgID = NULL)
	{

		$note_info = new stdClass();

		// check if editing
		if ($noteID != NULL) {

			// check if numeric
			if (!ctype_digit($noteID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'noteID' => $noteID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('orgs_notes')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$note_info = $row;
				$orgID = $note_info->orgID;
			}

		}

		// required
		if ($orgID == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$org_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Note';
		if ($noteID != NULL) {
			$submit_to = 'customers/notes/edit/' . $noteID;
			$title = $note_info->summary;
		} else {
			$submit_to = 'customers/notes/' . $orgID . '/new/';
		}
		$return_to = 'customers/notes/' . $orgID;
		$icon = 'book';
		$tab = 'notes';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$breadcrumb_levels['customers/notes/' . $orgID] = 'Notes';
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('summary', 'Summary', 'trim|xss_clean|required');
			$this->form_validation->set_rules('content', 'Details', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'summary' => set_value('summary'),
					'content' => $this->input->post('content'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($noteID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['orgID'] = $orgID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($noteID == NULL) {
						// insert
						$query = $this->db->insert('orgs_notes', $data);

					} else {
						$where = array(
							'noteID' => $noteID
						);

						// update
						$query = $this->db->update('orgs_notes', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($orgID == NULL) {
							$this->session->set_flashdata('success', set_value('summary') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('summary') . ' has been updated successfully.');
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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'note_info' => $note_info,
			'org_id' => $orgID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/note', $data);
	}

	/**
	 * delete a note
	 * @param  int $orgID
	 * @return mixed
	 */
	public function remove($noteID = NULL) {

		// check params
		if (empty($noteID)) {
			show_404();
		}

		$where = array(
			'noteID' => $noteID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_notes')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$note_info = $row;

			// all ok, delete
			$query = $this->db->delete('orgs_notes', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $note_info->summary . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $note_info->summary . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'customers/notes/' . $note_info->orgID;

			redirect($redirect_to);
		}
	}

}

/* End of file notes.php */
/* Location: ./application/controllers/customers/notes.php */
