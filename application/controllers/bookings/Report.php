<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}

		// if no access to reporting
		if (!$this->auth->has_features('reports')) {
			show_403();
		}
	}

	/**
	 * show project report
	 * @param int $bookingID
	 * @param mixed $export
	 * @return void
	 */
	public function index($bookingID = NULL, $export = FALSE)
	{

		$booking_info = new stdClass;

		// check
		if ($bookingID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($bookingID)) {
			show_404();
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$booking_info = $row;
		}

		// projects only
		if ($booking_info->type != 'event' && $booking_info->project != 1) {
			show_404();
		}

		// set defaults
		$title = 'Project Report';
		$buttons = '<a class="btn btn-primary" href="' . site_url('bookings/report/' . $bookingID . '/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$icon = 'book';
		$tab = 'report';
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
		$section = 'bookings';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// track data
		$types_by_day = array(
			'monday' => array(),
			'tuesday' => array(),
			'wednesday' => array(),
			'thursday' => array(),
			'friday' => array(),
			'saturday' => array(),
			'sunday' => array(),
		);
		$block_data = array();
		$lesson_data = array();
		$participants = array();
		$target_participants = array();

		// get cancellations
		$lesson_cancellations = array();
		$where = array(
			'bookings_lessons_exceptions.bookingID' => $bookingID,
			'bookings_lessons_exceptions.accountID' => $this->auth->user->accountID,
			'bookings_lessons_exceptions.type' => 'cancellation'
		);
		$res = $this->db->from('bookings_lessons_exceptions')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_cancellations[$row->lessonID][] = $row->date;
			}
		}

		// loop blocks
		$where = array(
			'bookings_blocks.bookingID' => $bookingID,
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);
		$blocks_res = $this->db->select('bookings_blocks.*, orgs.name as block_org')->from('bookings_blocks')->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')->where($where)->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate ASC')->get();
		if ($blocks_res->num_rows() > 0) {
			foreach ($blocks_res->result() as $block) {
				// map days to block dates
				$block_days = array();
				$date = $block->startDate;
				$end_date = $block->endDate;
				while(strtotime($date) <= strtotime($end_date)) {
					$key = strtolower(date('l', strtotime($date)));
					$block_days[$key] = $date;
					$date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
				}
				// save block data
				$block_data[$block->blockID] = array(
					'name' => $block->name,
					'dates' => mysql_to_uk_date($block->startDate),
					'provisional' => $block->provisional
				);
				if (strtotime($block->endDate) > strtotime($block->startDate)) {
					$block_data[$block->blockID]['dates'] .= ' - ' . mysql_to_uk_date($block->endDate);
				}
				// check for block org different to booking org
				if (!empty($block->orgID) && $block->orgID != $booking_info->orgID && !empty($block->block_org)) {
					$block_data[$block->blockID]['name'] .= ' (' . $block->block_org . ')';
				}
				// get sessions in block
				$where = array(
					'bookings_lessons.blockID' => $block->blockID,
					'bookings_lessons.accountID' => $this->auth->user->accountID
				);
				$lesson_res = $this->db->select('bookings_lessons.*, lesson_types.name as type')->from('bookings_lessons')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->where($where)->order_by('day asc, startTime asc, endTime asc')->get();
				if ($lesson_res->num_rows() > 0) {
					foreach ($lesson_res->result() as $lesson) {
						// skip if block dates doesn't cover this day
						if (!array_key_exists($lesson->day, $block_days)) {
							continue;
						}
						$lesson_date = $block_days[$lesson->day];
						// check if cancelled
						if (array_key_exists($lesson->lessonID, $lesson_cancellations) && in_array($lesson_date, $lesson_cancellations[$lesson->lessonID])) {
							continue;
						}
						// track types
						$lesson_type = 'Unknown';
						if (!empty($lesson->type)) {
							$lesson_type = $lesson->type;
						} else if (!empty($lesson->type_other)) {
							$lesson_type = $lesson->type_other;
						}
						if (!in_array($lesson_type, $types_by_day[$lesson->day])) {
							$types_by_day[$lesson->day][] = $lesson_type;
						}
						// track targets
						if (!isset($target_participants[$lesson->blockID][$lesson->day][$lesson_type])) {
							$target_participants[$lesson->blockID][$lesson->day][$lesson_type] = 0;
						}
						$target_participants[$lesson->blockID][$lesson->day][$lesson_type] += $lesson->target_participants;
						// save session data
						$lesson_data[$lesson->lessonID] = array(
							'blockID' => $lesson->blockID,
							'type' => $lesson_type,
							'day' => $lesson->day
						);
					}
				}
			}
		}

		// attendance data
		switch ($booking_info->register_type) {
			case 'numbers':
				// numbers only register
				$where = array(
					'bookings_attendance_numbers.bookingID' => $bookingID,
					'bookings_attendance_numbers.accountID' => $this->auth->user->accountID
				);
				$attendance_res = $this->db->select('bookings_attendance_numbers.lessonID, SUM(' . $this->db->dbprefix('bookings_attendance_numbers') . '.attended) as participants')->from('bookings_attendance_numbers')->where($where)->group_by('bookings_attendance_numbers.lessonID')->get();
				break;
			case 'names':
			case 'bikeability':
			case 'shapeup':
				// names only register
				$where = array(
					'bookings_attendance_names_sessions.bookingID' => $bookingID,
					'bookings_attendance_names_sessions.accountID' => $this->auth->user->accountID
				);
				$attendance_res = $this->db->select('bookings_attendance_names_sessions.lessonID, COUNT(DISTINCT ' . $this->db->dbprefix('bookings_attendance_names_sessions') . '.attendanceID) as participants')->from('bookings_attendance_names_sessions')->where($where)->group_by('bookings_attendance_names_sessions.lessonID')->get();
				break;
			default:
				// normal bookings
				$where = array(
					'bookings_cart_sessions.bookingID' => $bookingID,
					'bookings_cart_sessions.accountID' => $this->auth->user->accountID,
					'bookings_cart.type' => 'booking'
				);
				$count_on = 'contactID';
				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					$count_on = 'childID';
				}
				$attendance_res = $this->db->select('bookings_cart_sessions.lessonID, COUNT(DISTINCT ' . $this->db->dbprefix('bookings_cart_sessions') . '.' . $count_on . ') as participants')
				->from('bookings_cart_sessions')
				->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
				->where($where)
				->group_by('bookings_cart_sessions.lessonID')
				->get();
				break;
		}

		// process attendance
		if ($attendance_res->num_rows() > 0) {
			foreach ($attendance_res->result() as $attendance) {
				if (!array_key_exists($attendance->lessonID, $lesson_data)) {
					continue;
				}
				$blockID = $lesson_data[$attendance->lessonID]['blockID'];
				$day = $lesson_data[$attendance->lessonID]['day'];
				$lesson_type = $lesson_data[$attendance->lessonID]['type'];
				if (!isset($participants[$blockID][$day][$lesson_type])) {
					$participants[$blockID][$day][$lesson_type] = 0;
				}
				$participants[$blockID][$day][$lesson_type] += $attendance->participants;
			}
		}

		// remove days with no sessions
		foreach ($types_by_day as $key => $val) {
			if (count($val) == 0) {
				unset($types_by_day[$key]);
			}
		}

		// check for data
		if (count($block_data) == 0) {
			$buttons = NULL;
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		}
		if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}
		if ($this->session->flashdata('error')) {
			$errors[] = $this->session->flashdata('error');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'types_by_day' => $types_by_day,
			'blocks' => $block_data,
			'lessons' => $lesson_data,
			'participants' => $participants,
			'target_participants' => $target_participants,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		if ($export == TRUE || $export == 'true') {
			//load csv helper
			$this->load->helper('csv_helper');
			$this->load->view('bookings/report-export', $data);
		} else {
			$this->crm_view('bookings/report', $data);
		}
	}
}

/* End of file Report.php */
/* Location: ./application/controllers/bookings/Report.php */
