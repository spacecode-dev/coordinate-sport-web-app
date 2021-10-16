<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notes extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * show list of notes
	 * @return void
	 */
	public function index($lessonID = NULL) {

		if ($lessonID == NULL) {
			show_404();
		}

		// look up
        $res = $this->db->select('bookings_lessons.*, lesson_types.session_evaluations')->from('bookings_lessons')->where([
			'lessonID' => $lessonID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		])->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$lesson_info = $row;
			$bookingID = $lesson_info->bookingID;
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$res = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// look up block
		$where = array(
			'blockID' => $lesson_info->blockID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;
		}

		// set defaults
		$icon = 'book';
		$tab = 'notes';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$breadcrumb_levels['bookings/blocks/' . $bookingID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $lesson_info->blockID] = 'Sessions';
		$breadcrumb_levels['bookings/sessions/edit/' . $lessonID] = ucwords($lesson_info->day) . ' ' . substr($lesson_info->startTime, 0, 5);
		$page_base = 'sessions/notes/' . $lessonID;
		$section = 'bookings';
		$title = 'Notes';
		if ($this->auth->has_features('session_evaluations')) {
			$title .= '/Evaluations';
		}
		$buttons = '<a class="btn btn-success" href="' . site_url('sessions/notes/' . $lessonID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'bookings_lessons_notes.lessonID' => $lessonID,
			'bookings_lessons_notes.accountID' => $this->auth->user->accountID
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

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-lesson-notes'))) {

			foreach ($this->session->userdata('search-lesson-notes') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-lesson-notes', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = $this->db->dbprefix('bookings_lessons_notes') . ".`added` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = $this->db->dbprefix('bookings_lessons_notes') . ".`added` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['summary'] != '') {
				$search_where[] = $this->db->dbprefix('bookings_lessons_notes') . ".`summary` LIKE '%" . $this->db->escape_like_str($search_fields['summary']) . "%'";
			}

			if ($search_fields['content'] != '') {
				$search_where[] = $this->db->dbprefix('bookings_lessons_notes') . ".`content` LIKE '%" . $this->db->escape_like_str($search_fields['content']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('bookings_lessons_notes.*, staff.first as staff_first, staff.surname as staff_last')->from('bookings_lessons_notes')->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('bookings_lessons_notes.added desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('bookings_lessons_notes.*, staff.first as staff_first, staff.surname as staff_last')->from('bookings_lessons_notes')->join('staff', 'bookings_lessons_notes.byID = staff.staffID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('bookings_lessons_notes.added desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'booking_type' => $booking_info->type,
			'lessonID' => $lessonID,
			'lesson_info' => $lesson_info,
			'bookingID' => $bookingID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('sessions/notes', $data);
	}

	/**
	 * edit a note
	 * @param  int $noteID
	 * @param int $lessonID
	 * @return void
	 */
	public function edit($noteID = NULL, $lessonID = NULL)
	{

		$note_info = new stdClass();
		$type = 'note';
		$read_only = FALSE;
		$info = NULL;

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
			$query = $this->db->from('bookings_lessons_notes')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$note_info = $row;
				$lessonID = $note_info->lessonID;
				$type = $row->type;
				if ($type == 'evaluation' && $note_info->status != 'submitted') {
					$info = 'Evaluation is only editable once the coach has submitted it.';
					$read_only = TRUE;
				}
			}

		}

		// required
		if ($lessonID == NULL) {
			show_404();
		}

		// look up org
        $query = $this->db->select('bookings_lessons.*, lesson_types.session_evaluations')->from('bookings_lessons')->where([
            'lessonID' => $lessonID,
            'bookings_lessons.accountID' => $this->auth->user->accountID
        ])->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$lesson_info = $row;
			$bookingID = $lesson_info->bookingID;
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$res = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// look up block
		$where = array(
			'blockID' => $lesson_info->blockID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$block_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Note';
		if ($noteID != NULL) {
			$submit_to = 'sessions/notes/edit/' . $noteID;
			$title = $note_info->summary;
			if ($type == 'evaluation') {
				$title = 'Session Evaluation';
				if (isset($note_info->date) && !empty($note_info->date)) {
					$title .= ' for ' . mysql_to_uk_date($note_info->date);
				}
			}
		} else {
			$submit_to = 'sessions/notes/' . $lessonID . '/new/';
		}
		$return_to = 'sessions/notes/' . $lessonID;
		$icon = 'book';
		$tab = 'notes';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$breadcrumb_levels['bookings/blocks/' . $bookingID] = $block_info->name;
		$breadcrumb_levels['bookings/sessions/' . $bookingID . '/' . $lesson_info->blockID] = 'Sessions';
		$breadcrumb_levels['bookings/sessions/edit/' . $lessonID] = ucwords($lesson_info->day) . ' ' . substr($lesson_info->startTime, 0, 5);
		$breadcrumb_levels['sessions/notes/' . $lessonID] = 'Notes';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;

		// if posted
		if ($this->input->post() && $read_only !== TRUE) {

			// set validation rules
			switch ($type) {
				case 'note':
					$this->form_validation->set_rules('summary', 'Summary', 'trim|xss_clean|required');
					$this->form_validation->set_rules('content', 'Details', 'trim|xss_clean|required');
					break;
				case 'evaluation':
					$this->form_validation->set_rules('content', 'Evaluation', 'trim|xss_clean|required');
					break;
			}

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

				// if evaluation, no summary
				if ($type == 'evaluation') {
					unset($data['summary']);
				}

				if ($noteID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['bookingID'] = $bookingID;
					$data['lessonID'] = $lessonID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($noteID == NULL) {
						// insert
						$query = $this->db->insert('bookings_lessons_notes', $data);

					} else {
						$where = array(
							'noteID' => $noteID
						);

						// update
						$query = $this->db->update('bookings_lessons_notes', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($lessonID == NULL) {
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
			'booking_type' => $booking_info->type,
			'lessonID' => $lessonID,
			'lesson_info' => $lesson_info,
			'bookingID' => $bookingID,
			'type' => $type,
			'read_only' => $read_only,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('sessions/note', $data);
	}

	/**
	 * delete a note
	 * @param  int $lessonID
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
		$query = $this->db->from('bookings_lessons_notes')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$note_info = $row;

			// don't allow deleting approved
			if ($note_info->type == 'evaluation' && $note_info->status == 'approved') {
				show_404();
			}

			// all ok, delete
			$query = $this->db->delete('bookings_lessons_notes', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $note_info->summary . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $note_info->summary . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'sessions/notes/' . $note_info->lessonID;

			redirect($redirect_to);
		}
	}

}

/* End of file notes.php */
/* Location: ./application/controllers/sessions/notes.php */
