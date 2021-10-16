<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notes extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach'), array(), array('staff_management'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
	}

	/**
	 * show list of notes
	 * @return void
	 */
	public function index($staffID = NULL) {

		if ($staffID == NULL) {
			show_404();
		}

		// look up staff
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
		$icon = 'book';
		$tab = 'notes';
		$current_page = 'staff';
		$page_base = 'staff/notes/' . $staffID;
		$section = 'staff';
		$title = 'Development';
		$buttons = '<a class="btn btn-success" href="' . site_url('staff/notes/' . $staffID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname
 		);

		// set where
		$where = array(
			'staff_notes.staffID' => $staffID,
			'staff_notes.accountID' => $this->auth->user->accountID,
			'staff_notes.type !=' => 'privacy'
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'summary' => NULL,
			'content' => NULL,
			'type' => NULL,
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
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['summary'] = set_value('search_summary');
			$search_fields['content'] = set_value('search_content');
			$search_fields['type'] = set_value('search_type');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-staff-notes'))) {

			foreach ($this->session->userdata('search-staff-notes') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-staff-notes', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`date` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['summary'] != '') {
				$search_where[] = "`summary` LIKE '%" . $this->db->escape_like_str($search_fields['summary']) . "%'";
			}

			if ($search_fields['content'] != '') {
				$search_where[] = "`content` LIKE '%" . $this->db->escape_like_str($search_fields['content']) . "%'";
			}

			if ($search_fields['type'] != '') {
				$search_where[] = "`type` = " . $this->db->escape($search_fields['type']);
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('staff_notes')->where($where)->where($search_where, NULL, FALSE)->order_by('date desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('staff_notes')->where($where)->where($search_where, NULL, FALSE)->order_by('date desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/notes', $data);
	}

	/**
	 * edit a note
	 * @param  int $noteID
	 * @param int $staffID
	 * @return void
	 */
	public function edit($noteID = NULL, $staffID = NULL)
	{

		$note_info = new stdClass();

		// check if editing
		if ($noteID != NULL) {

			// office can't edit
			if ($this->auth->user->department == 'office') {
				show_403();
			}

			// check if numeric
			if (!ctype_digit($noteID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'noteID' => $noteID,
				'accountID' => $this->auth->user->accountID,
				'type !=' => 'privacy'
			);

			// run query
			$query = $this->db->from('staff_notes')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$note_info = $row;
				$staffID = $note_info->staffID;
			}

		}

		// required
		if ($staffID == NULL) {
			show_404();
		}

		// look up
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
		$title = 'New Item';
		if ($noteID != NULL) {
			$submit_to = 'staff/notes/edit/' . $noteID;
			$title = $note_info->summary;
		} else {
			$submit_to = 'staff/notes/' . $staffID . '/new/';
		}
		$return_to = 'staff/notes/' . $staffID;
		$icon = 'book';
		$tab = 'notes';
		$current_page = 'staff';
		$section = 'staff';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
			'staff/notes/' . $staffID => 'Development'
 		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('date', 'Date', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			if ($this->input->post('type') == 'observation') {
				$this->form_validation->set_rules('observation_score', 'Observation Score', 'trim|xss_clean|required|integer|greater_than_equal_to[1]|less_than_equal_to[100]');
			}
			$this->form_validation->set_rules('summary', 'Summary', 'trim|xss_clean|required');
			$this->form_validation->set_rules('content', 'Details', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'date' => uk_to_mysql_date(set_value('date')),
					'type' => set_value('type'),
					'observation_score' => NULL,
					'summary' => set_value('summary'),
					'content' => $this->input->post('content', FALSE),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($data['type'] == 'observation') {
					$data['observation_score'] = set_value('observation_score');
				}

				if ($noteID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['staffID'] = $staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($noteID == NULL) {
						// insert
						$query = $this->db->insert('staff_notes', $data);

					} else {
						$where = array(
							'noteID' => $noteID
						);

						// update
						$query = $this->db->update('staff_notes', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($staffID == NULL) {
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
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/note', $data);
	}

	/**
	 * delete a note
	 * @param  int $staffID
	 * @return mixed
	 */
	public function remove($noteID = NULL) {

		// office can't delete
		if ($this->auth->user->department == 'office') {
			show_403();
		}

		// check params
		if (empty($noteID)) {
			show_404();
		}

		$where = array(
			'noteID' => $noteID,
			'accountID' => $this->auth->user->accountID,
			'type !=' => 'privacy'
		);

		// run query
		$query = $this->db->from('staff_notes')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$note_info = $row;

			// all ok, delete
			$query = $this->db->delete('staff_notes', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $note_info->summary . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $note_info->summary . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'staff/notes/' . $note_info->staffID;

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

}

/* End of file notes.php */
/* Location: ./application/controllers/staff/notes.php */
