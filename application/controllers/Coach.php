<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Coach extends MY_Controller {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * session info
	 * @param  int $lessonID
	 * @param  string $date
	 * @return void
	 */
	public function session($lessonID = NULL, $date = NULL)
	{

		// check params
		if (empty($lessonID)|| empty($date)) {
			show_404();
		}

		// check date
		if (!check_mysql_date($date)) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($lessonID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'bookings_lessons.lessonID' => $lessonID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_lessons.*, activities.name as activity, lesson_types.name as type,
		 lesson_types.birthday_tab, lesson_types.session_evaluations')
			->from('bookings_lessons')
			->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$lesson_info = $row;
			$lessonID = $lesson_info->lessonID;
		}

		// check date is on the same day as lesson
		if ($lesson_info->day != strtolower(date('l', strtotime($date)))) {
			show_404();
		}

		// if session dates set, check is within them
		if ((!empty($lesson_info->startDate) && !empty($lesson_info->endDate)) && (strtotime($date) < strtotime($lesson_info->startDate) || strtotime($date) > strtotime($lesson_info->endDate))) {
			show_404();
		}

		// get booking info and check date is within booking
		$where = array(
			'bookingID' => $lesson_info->bookingID,
			'startDate <=' => $date,
			'endDate >=' => $date,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('bookings')->where($where)->limit(1)->get();

		// not found or out of range
		if ($res->num_rows() == 0) {
			show_404();
		}

		$evaluation_show_form = FALSE;
		$has_access = false;

		foreach ($res->result() as $booking_info) {}

		// if in a coaching dept, check has access to lesson
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach', 'headcoach'))) {

			// check if staff usually on lesson
			$where = array(
				'lessonID' => $lessonID,
				'staffID' => $this->auth->user->staffID,
				'startDate <=' => $date,
				'endDate >=' => $date,
				'accountID' => $this->auth->user->accountID
			);

			$res = $this->db->from('bookings_lessons_staff')->where($where)->limit(1)->get();

			if ($res->num_rows() == 1) {

				// yes, check not removed by exception
				$where = array(
					'lessonID' => $lessonID,
					'date' => $date,
					'type' => 'staffchange',
					'fromID' => $this->auth->user->staffID,
					'accountID' => $this->auth->user->accountID
				);

				$res = $this->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

				if ($res->num_rows() < 1) {
					$has_access = true;
					$evaluation_show_form = true;
				}
			} else {

				// no, check not added by exception
				$where = array(
					'lessonID' => $lessonID,
					'date' => $date,
					'type' => 'staffchange',
					'staffID' => $this->auth->user->staffID,
					'accountID' => $this->auth->user->accountID
				);

				$res = $this->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

				if ($res->num_rows() > 0) {
					$has_access = true;
					$evaluation_show_form = true;
				}
			}
			//if there is no access, check if session inside blocks, assigned to staff
			if (!$has_access) {

				// get sessions inside blocks to check access to sessions
				$query = $this->db->select('bookings_lessons.blockID')
					->from('bookings_lessons_staff')
					->where([
						'staffID' => $this->auth->user->staffID
					])
					->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID')
					->group_by('bookings_lessons.blockID')->get();

				$blocks = [];
				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$blocks[] = $row->blockID;
					}
				}

				$lessons = [];
				if (!empty($blocks)) {
					$query = $this->db->select('bookings_lessons.lessonID')
						->from('bookings_lessons')
						->where_in('bookings_lessons.blockID', $blocks)
						->group_by('bookings_lessons.lessonID')->get();

					if ($query->num_rows() > 0) {
						foreach ($query->result() as $row) {
							$lessons[] = $row->lessonID;
						}
					}
				}

				if (!in_array($lessonID, $lessons)) {
					show_404();
				}
				$evaluation_show_form = true;
			}
		}

		// get block info
		$where = array(
			'blockID' => $lesson_info->blockID,
			'accountID' => $this->auth->user->accountID,
			'provisional !=' => 1
		);

		if ($this->settings_library->get('provisional_own_timetable') == 1) {
			unset($where['provisional !=']);
		}

		$res = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		// not found or out of range
		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $block_info) {}

		// check session not cancelled by exception
		$where = array(
			'lessonID' => $lessonID,
			'date' => $date,
			'type' => 'cancellation',
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

		if ($res->num_rows() == 1) {
			show_404();
		}

		// set defaults
		$title = 'Session Information';
		if($this->session->userdata('search-timetable-staff') != ""){
			$return_to = 'staff/timetable/'.$this->session->userdata('search-timetable-staff')."/" . date('Y/W', strtotime($date));
		}else{
			$return_to = 'timetable/' . date('Y/W', strtotime($date));
		}
		$icon = 'calendar-alt';
		$current_page = 'timetable_own';
		$section = 'timetable_own';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to Timetable</a>';

		$org_info = FALSE;
		$org_contact_info = FALSE;
		$address_info = FALSE;
		$staff_address_info = FALSE;
		$evaluation_info = new stdClass();
		$evaluation_read_only = [];
		$evaluation_read_only_general = FALSE;

		$success = NULL;
		$info = NULL;
		$errors = array();
		$breadcrumb_levels = array(
			'staff/timetable/'.$this->session->userdata('search-timetable-staff')."/". date('Y', strtotime($date)) . '/' . date('W', strtotime($date)) => 'Timetable'
		);

		// get org info
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID
		);
		// if block has customer override, get from that customer
		if (!empty($block_info->orgID)) {
			$where['orgID'] = $block_info->orgID;
		}

		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 1) {
			foreach ($res->result() as $org_info) {}
		}
		
		// Check for main contact
		if ($this->settings_library->get('show_customer_in_session_staff', $this->auth->user->accountID) == 1) {
			$where['active'] = 1;
			$res = $this->db->from('orgs_contacts')->where($where)->order_by("isMain, added", "desc")->limit(1)->get();
			
			if ($res->num_rows() == 1) {
				foreach ($res->result() as $org_contact_info) {}
			}
		}
		
		// get address info
		$where = array(
			'addressID' => $booking_info->addressID,
			'accountID' => $this->auth->user->accountID
		);

		// if booking, get address from lesson
		if ($booking_info->type == 'booking') {
			$where['addressID'] = $lesson_info->addressID;
		}

		$res = $this->db->from('orgs_addresses')->where($where)->limit(1)->get();

		if ($res->num_rows() == 1) {
			foreach ($res->result() as $address_info) {}
		}

		// get main address for logged in user
		$where = array(
			'staffID' => $this->auth->user->staffID,
			'type' => 'main',
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('staff_addresses')->where($where)->limit(1)->get();

		if ($res->num_rows() == 1) {
			foreach ($res->result() as $staff_address_info) {}
		}

		// get staff names and contact details
		$staff_names = array();
		$staff_contact_details = array();
		$where = array(
			'staff.accountID' => $this->auth->user->accountID
		);
		$staff = $this->db->select('staff.*, staff_addresses.phone, staff_addresses.mobile, staff_addresses.mobile_work')->from('staff')->join('staff_addresses', "staff.staffID = staff_addresses.staffID and staff_addresses.type = 'main'", 'left')->where($where)->get();

		if ($staff->num_rows() > 0) {
			foreach ($staff->result() as $s) {
				$staff_names[$s->staffID] = $s->first . ' ' . $s->surname;
				$contact_bits = array();
				if (!empty($s->mobile_work)) {
					$contact_bits['mobile_work'] = '<a href="tel:' . $s->mobile_work . '">' . $s->mobile_work . '</a>';
				} else if (!empty($s->mobile)) {
					$contact_bits['mobile'] = '<a href="tel:' . $s->mobile . '">' . $s->mobile . '</a>';
				}
				if (!empty($s->email)) {
					$contact_bits['email'] = '<a href="mailto:' . $s->email . '">Email</a>';
				}
				$staff_contact_details[$s->staffID] = array(
					'formatted' => implode(" | ", $contact_bits),
					'mobile' => $s->mobile,
					'email' => $s->email
				);
			}
		}

		// get staff
		$staff_ids = array();
		$staff_list = array(
			'headcoaches' => array(),
			'leadcoaches' => array(),
			'coaches' => array(),
			'observers' => array(),
			'participants' => array()
		);

		$where = array(
			'lessonID' => $lessonID,
			'startDate <=' => $date,
			'endDate >=' => $date,
			'accountID' => $this->auth->user->accountID
		);

		$res_staff = $this->db->from('bookings_lessons_staff')->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $staff) {
				$staff_ids[$staff->staffID] = $staff->type;
			}
		}

		// check for evaluation
		$where = array(
			'lessonID' => $lessonID,
			'date' => $date,
			'accountID' => $this->auth->user->accountID,
			'type' => 'evaluation'
		);
		$res = $this->db->from('bookings_lessons_notes')->where($where)->get();

		$evaluations = [];
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $evaluation_info) {
				$evaluations[$evaluation_info->byID] = $evaluation_info;
				// can only edit if rejected
				if (in_array($evaluation_info->status, array('submitted', 'approved'))) {
					$evaluation_read_only[$evaluation_info->noteID] = TRUE;
				}
			}
		}

		// check for session exceptions
		$where = array(
			'date' => $date,
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID
		);
		$res_exceptions = $this->db->get_where('bookings_lessons_exceptions', $where);
		if ($res_exceptions->num_rows() > 0) {
			foreach ($res_exceptions->result() as $exception) {
				// staff change
				if (array_key_exists($exception->fromID, $staff_ids)) {
					// swap if moved to another staff
					if (!empty($exception->staffID)) {
						$staff_ids[$exception->staffID] = $staff_ids[$exception->fromID];
					}
					if (isset($staff_ids[$exception->fromID])) {
						unset($staff_ids[$exception->fromID]);
					}
				}
			}
		}

		// loop through staff
		foreach ($staff_ids as $staff_id => $type) {

			// map staff ids to staff names and add to head coach, etc arrays
			if (!array_key_exists($staff_id, $staff_names)) {
				unset($staff_ids[$staff_id]);
				continue;
			}

			// display staff times if different to session times
			$where_times = array(
				'lessonID' => $lessonID,
				'staffID' => $staff_id,
				'startDate <=' => $date,
				'endDate >=' => $date,
				'accountID' => $this->auth->user->accountID
			);
			$res_times = $this->db->from('bookings_lessons_staff')->where($where_times)->get();
			if ($res_times->num_rows() > 0) {
				foreach ($res_times->result() as $row_time) {
					if ($row_time->startTime != $lesson_info->startTime || $row_time->endTime != $lesson_info->endTime) {
						$staff_names[$staff_id] .= ' (' . substr($row_time->startTime, 0, 5) . '-' . substr($row_time->endTime, 0 ,5) . ' only)';
					}
				}
			}

			switch ($type) {
				case 'head':
					$staff_list['headcoaches'][$staff_id] = $staff_names[$staff_id] . ' (' . $staff_contact_details[$staff_id]['formatted'] . ')';
					break;
				case 'lead':
					$staff_list['leadcoaches'][$staff_id] = $staff_names[$staff_id] . ' (' . $staff_contact_details[$staff_id]['formatted'] . ')';
					break;
				default:
					$staff_list['coaches'][$staff_id] = $staff_names[$staff_id] . ' (' . $staff_contact_details[$staff_id]['formatted'] . ')';
					break;
				case 'observer':
					$staff_list['observers'][$staff_id] = $staff_names[$staff_id] . ' (' . $staff_contact_details[$staff_id]['formatted'] . ')';
					break;
				case 'participant':
					$staff_list['participants'][$staff_id] = $staff_names[$staff_id] . ' (' . $staff_contact_details[$staff_id]['formatted'] . ')';
					break;
			}
		}

		// sort all staff
		asort($staff_list['headcoaches']);
		asort($staff_list['leadcoaches']);
		asort($staff_list['coaches']);
		asort($staff_list['observers']);
		asort($staff_list['participants']);

		if (!array_key_exists($this->auth->user->staffID, $staff_list['headcoaches'])) {
			foreach ($evaluations as $note) {
				$evaluation_read_only[$note->noteID] = TRUE;
			}
			$evaluation_read_only_general = TRUE;

			if (count($evaluations) < 1) {
				$evaluation_show_form = false;
			}
		}

		if ($lesson_info->session_evaluations == 0 || strtotime($date . ' ' . $lesson_info->startTime) > time()) {
			$evaluation_show_form = false;
		}

		// get staff on other sessions within block
		$other_sessions_staff = array();
		$where = array(
			'bookings_lessons.blockID' => $lesson_info->blockID,
			'bookings_lessons_staff.lessonID !=' => $lessonID,
			'bookings_lessons_staff.accountID' => $this->auth->user->accountID
		);
		$res_staff = $this->db->select('bookings_lessons_staff.*, bookings_lessons.day')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->order_by('bookings_lessons.day asc, bookings_lessons_staff.startDate, bookings_lessons_staff.endDate, bookings_lessons_staff.startTime, bookings_lessons_staff.endTime')->get();

		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $staff) {
				$other_sessions_staff[] = $staff;
			}
		}

		// session plans
		$where = array(
			'lessonID' => $lessonID,
			'files.accountID' => $this->auth->user->accountID
		);
		$lesson_plans = $this->db->select('files.*')->from('files')->join('bookings_lessons_resources_attachments', 'files.attachmentID = bookings_lessons_resources_attachments.attachmentID')->where($where)->order_by('name asc')->get();

		// session notes
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID,
			'type' => 'note'
		);
		$lesson_notes = $this->db->from('bookings_lessons_notes')->where($where)->order_by('added desc')->get();

		// Project attachments
		$where = array(
			'bookings_attachments.bookingID' => $lesson_info->bookingID,
			'bookings_attachments.accountID' => $this->auth->user->accountID,
			'bookings_attachments_blocks.blockID' => $lesson_info->blockID,
		);

		$project_attachments = $this->db->select('bookings_attachments.*, GROUP_CONCAT(' . $this->db->dbprefix('bookings_attachments_blocks') . '.blockID) AS blocks')
			->from('bookings_attachments')
			->join('bookings_attachments_blocks', 'bookings_attachments.attachmentID = bookings_attachments_blocks.attachmentID', 'left')
			->where($where)
			->order_by('bookings_attachments.name asc')
			->group_by('bookings_attachments.attachmentID')
			->get();

		// process evaluation if session in past and head coach and session type allows
		if ($this->auth->has_features('session_evaluations') && strtotime($date . ' ' . $lesson_info->startTime) <= time() && array_key_exists($this->auth->user->staffID, $staff_list['headcoaches']) && $lesson_info->session_evaluations == 1) {

			$evaluation_show_form = TRUE;

			if ($this->input->post('action') == 'evaluation' && $evaluation_read_only_general !== TRUE) {

				// load libraries
				$this->load->library('form_validation');

				// set validation rules
				$this->form_validation->set_rules('evaluation', 'Evaluation', 'trim|xss_clean|required');

				if ($this->form_validation->run() == FALSE) {
					$errors = $this->form_validation->error_array();
				} else {

					// all ok

					// prepare data
					$data = array(
						'content' => $this->input->post('evaluation'),
						'status' => 'submitted',
						'added' => mdate('%Y-%m-%d %H:%i:%s'), // submission date
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$evaluationId = (int)set_value('evaluations_id');
					//check if evaluation related to this coach was created already
					if (array_key_exists($this->auth->user->staffID, $evaluations)) {
						$evaluationId = (int)$evaluations[$this->auth->user->staffID]->noteID;
					}

					if ($evaluationId < 1) {
						$data['byID'] = $this->auth->user->staffID;
						$data['lessonID'] = $lessonID;
						$data['bookingID'] = $lesson_info->bookingID;
						$data['date'] = $date;
						$data['type'] = 'evaluation';
						$data['accountID'] = $this->auth->user->accountID;
					}

					// final check for errors
					if (count($errors) == 0) {

						if ($evaluationId < 1) {
							// insert
							$res = $this->db->insert('bookings_lessons_notes', $data);
						} else {
							// update
							$where = array(
								'noteID' => $evaluationId,
								'accountID' => $this->auth->user->accountID
							);
							$res = $this->db->update('bookings_lessons_notes', $data, $where, 1);
						}

						// if inserted/updated
						if ($this->db->affected_rows() == 1) {

							$this->session->set_flashdata('success', 'Evaluation has been submitted successfully.');

							redirect('evaluations');

							return TRUE;
						} else {
							$this->session->set_flashdata('info', 'Error saving data, please try again.');
						}
					}
				}
			}
		}

		// process new session attachment
		if ($this->auth->has_features('staff_lesson_uploads') && $this->input->post('action') == 'attachment') {

			// load libraries
			$this->load->library('form_validation');

			// set validation rules
			$this->form_validation->set_rules('comment', 'Comment', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok

				// prepare data
				$data = array(
					'byID' => $this->auth->user->staffID,
					'lessonID' => $lessonID,
					'comment' => set_value('comment'),
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				$upload_res = $this->crm_library->handle_upload();
				if ($upload_res === NULL) {
					$errors[] = 'A valid file is required';
				} else {
					$data['name'] = $upload_res['client_name'];
					$data['path'] = $upload_res['raw_name'];
					$data['type'] = $upload_res['file_type'];
					$data['size'] = $upload_res['file_size']*1024;
					$data['ext'] = substr($upload_res['file_ext'], 1);
				}

				// final check for errors
				if (count($errors) == 0) {

					// insert id
					$query = $this->db->insert('bookings_lessons_attachments', $data);

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if (empty($data['name'])) {
							$data['name'] = $attachment_info->name;
						}

						$this->session->set_flashdata('success', $data['name'] . ' has been uploaded successfully.');

						redirect('coach/session/' . $lessonID . '/' . $date);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

		// session attachments
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $this->auth->user->accountID
		);
		$lesson_attachments = $this->db->from('bookings_lessons_attachments')->where($where)->order_by('name asc')->get();

		// org attachments
		$where = array(
			'orgID' => $booking_info->orgID,
			'bookingID' => $lesson_info->bookingID,
			'lessonID' => $lessonID,
			'coachaccess' => 1,
			'orgs_attachments.accountID' => $this->auth->user->accountID
		);
		// if block has customer override, get from that customer
		if (!empty($block_info->orgID)) {
			$where['orgID'] = $block_info->orgID;
		}

		if ($booking_info->type == 'booking') {
			$addressID = $lesson_info->addressID;
		} else {
			$addressID = $booking_info->addressID;
		}

		$where_custom = '(addressID IS NULL OR addressID = ' . $this->db->escape($addressID) . ')';

		$org_attachments = $this->db->select('orgs_attachments.*')->from('orgs_attachments')->join('bookings_lessons_orgs_attachments', 'orgs_attachments.attachmentID = bookings_lessons_orgs_attachments.attachmentID')->where($where)->where($where_custom, NULL, FALSE)->order_by('name asc')->get();

		// safety docs
		$where = array(
			'orgs_safety.addressID' => $addressID,
			'orgs_safety.expiry >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'orgs_safety.accountID' => $this->auth->user->accountID
		);
		$safety_docs = $this->db->select('orgs_safety.*, lesson_types.name as lesson_type')->from('orgs_safety')->join('lesson_types', 'orgs_safety.typeID = lesson_types.typeID', 'left')->where($where)->order_by('date asc, type asc, expiry asc')->get();

		// direction link
		$directions_link = 'http://maps.google.co.uk/maps?f=d&amp;daddr=' .  $address_info->postcode;
		// if staff address has postcode, set starting address
		if ($staff_address_info !== FALSE && !empty($staff_address_info->postcode)) {
			$directions_link .= '&amp;saddr=' . $staff_address_info->postcode;
		}
		$buttons .= ' ' . anchor($directions_link, '<i class="far fa-map-marker-alt"></i> Get Directions', 'target="_blank" class="btn btn-success"');

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
			'return_to' => $return_to,
			'date' => $date,
			'booking_info' => $booking_info,
			'lesson_info' => $lesson_info,
			'org_info' => $org_info,
			'org_contact_info' => $org_contact_info,
			'address_info' => $address_info,
			'staff_address_info' => $staff_address_info,
			'staff_list' => $staff_list,
			'lesson_plans' => $lesson_plans,
			'lesson_notes' => $lesson_notes,
			'lesson_attachments' => $lesson_attachments,
			'project_attachments' => $project_attachments,
			'org_attachments' => $org_attachments,
			'safety_docs' => $safety_docs,
			'lessonID' => $lessonID,
			'other_sessions_staff' => $other_sessions_staff,
			'staff_names' => $staff_names,
			'staff_contact_details' => $staff_contact_details,
			'evaluation_read_only' => $evaluation_read_only,
			'evaluation_show_form' => $evaluation_show_form,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'info' => $info,
			'errors' => $errors,
			'evaluations' => $evaluations
		);

		// load view
		$this->crm_view('coach/session', $data);
	}

}

/* End of file coach.php */
/* Location: ./application/controllers/coach.php */
