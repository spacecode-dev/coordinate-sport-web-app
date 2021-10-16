<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Offer_accept_library {

	private $CI;
	private $staff_offered = array();
	private $bulk_lesson_details = array();

	private $combined_lessons = [];

	private static $errors = [];

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
		$this->CI->load->model('Offers/OffersModel');
	}

	public function setErrors($message) {
		self::$errors[] = $message;
	}

	public function getErrors() {
		return self::$errors;
	}

	/**
	 * offer session to coaches
	 * @param  integer $lessonID
	 * @return mixed
	 */
	public function offer_to_individual($lessonID, $staffId, $staffType, $combinedLessons = [], $salaried = 0) {

		$this->combined_lessons = $combinedLessons;

		// check params
		if (empty($lessonID) || empty($staffId)) {
			return FALSE;
		}

		// look up lesson
		$where = array(
			'lessonID' => $lessonID,
			'offer_accept_status' => 'offering'
		);
		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $lesson_info);
		// look up block
		$where = array(
			'blockID' => $lesson_info->blockID,
		);
		$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $block_info);

		// check session dates to see if already started
		$lesson_start = $lesson_info->startDate;
		if (empty($lesson_start)) {
			$lesson_start = $block_info->startDate;
		}
		if (strtotime($lesson_start) <= strtotime(date('Y-m-d'))) {
			// in past, expire offer
			$this->expire_offers($lessonID, 'Already Started');
			return FALSE;
		}

		// compare staffing levels
		$required_staff = array(
			'head' => $lesson_info->staff_required_head,
			'lead' => $lesson_info->staff_required_lead,
			'assistant' => $lesson_info->staff_required_assistant,
			'observer' => $lesson_info->staff_required_observer,
			'participant' => $lesson_info->staff_required_participant
		);

		// track staff to exclude
		$exclude_staff = array();

		// get staff already on lesson
		$where = array(
			'lessonID' => $lessonID,
		);
		$res = $this->CI->db->from('bookings_lessons_staff')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$exclude_staff[] = $row->staffID;
				// reduce required staff
				$required_staff[$row->type]--;
			}
		}

		// get staff already asked
		$where = array(
			'lessonID' => $lessonID
		);
		$res = $this->CI->db->from('offer_accept')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!in_array($row->status, ['declined', 'expired']) || ($row->status == 'declined' && $row->type == $staffType)) {
					$exclude_staff[] = $row->staffID;
				}
			}
		}

		// check if already fully staffed
		if ($this->check_if_fully_staffed($lesson_info)) {
			$this->expire_offers($lessonID, 'Fully Staffed', 'assigned');
			return FALSE;
		}

		//check if specific staff is needed to be offered
		if (in_array($staffId, $exclude_staff)) {
			$this->setErrors('The selected member of staff is not available. Please select another one');
			$this->try_to_decline_offers($lessonID);
			return false;
		}

		// work out end date
		$lesson_end = $lesson_info->endDate;
		if (empty($lesson_end)) {
			$lesson_end = $block_info->endDate;
		}

		$this->CI->load->library('availability_library');
		$conflicts = $this->CI->availability_library->checkAvailabilityById($lessonID, $staffId, $lesson_start, $lesson_end);

		if (!empty($conflicts)) {
			$this->setErrors('The selected member of staff is not available. Please select another one');
			return false;
		}

		$staff_assigned = false;
		if ($this->offer_lesson($lessonID, $staffId, $staffType, 'individual', NULL, $salaried)) {
			$staff_assigned = true;
		}

		//if no staff was offered - trying to decline all offers
		if (!$staff_assigned) {
			$this->try_to_decline_offers($lessonID);
			return FALSE;
		}

		return TRUE;
	}


	public function offer_to_group($lessonID, $groupID, $staffType, $combinedLessons = [], $salaried = 0) {

		if (empty($this->combined_lessons)) {
			$this->combined_lessons = $combinedLessons;
		}

		if (empty($lessonID) || empty($groupID)) {
			return false;
		}

		// look up lesson
		$where = array(
			'lessonID' => $lessonID,
			'offer_accept_status' => 'offering'
		);

		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return false;
		}

		foreach ($res->result() as $lesson_info);

		$groupInfo = $this->CI->settings_library->getGroupInfo($groupID);

		if (!$groupInfo) {
			return false;
		}

		$groupStaff = $this->CI->settings_library->getStaffByGroup($groupID);

		if (empty($groupStaff)) {
			return false;
		}

		// look up block
		$where = array(
			'blockID' => $lesson_info->blockID,
		);
		$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $block_info);

		// check session dates to see if already started
		$lesson_start = $lesson_info->startDate;
		if (empty($lesson_start)) {
			$lesson_start = $block_info->startDate;
		}
		if (strtotime($lesson_start) <= strtotime(date('Y-m-d'))) {
			// in past, expire offer
			$this->expire_offers($lessonID, 'Already Started');
			return FALSE;
		}

		// compare staffing levels
		$required_staff = array(
			'head' => $lesson_info->staff_required_head,
			'lead' => $lesson_info->staff_required_lead,
			'assistant' => $lesson_info->staff_required_assistant,
			'observer' => $lesson_info->staff_required_observer,
			'participant' => $lesson_info->staff_required_participant
		);

		// track staff to exclude
		$exclude_staff = array();

		// get staff already on lesson
		$where = array(
			'lessonID' => $lessonID,
		);
		$res = $this->CI->db->from('bookings_lessons_staff')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$exclude_staff[] = $row->staffID;
				// reduce required staff
				$required_staff[$row->type]--;
			}
		}

		// get staff already asked
		$where = array(
			'lessonID' => $lessonID,
		);
		$res = $this->CI->db->from('offer_accept')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!in_array($row->status, ['declined', 'expired']) || ($row->status == 'declined' && $row->type == $staffType)) {
					$exclude_staff[] = $row->staffID;
				}
			}
		}

		// check if already fully staffed
		if ($this->check_if_fully_staffed($lesson_info)) {
			$this->expire_offers($lessonID, 'Fully Staffed', 'assigned');
			return FALSE;
		}

		// work out end date
		$lesson_end = $lesson_info->endDate;
		if (empty($lesson_end)) {
			$lesson_end = $block_info->endDate;
		}

		$assigned_staff = 0;
		$this->CI->load->library('availability_library');

		$conflicted_staff = 0;
		switch ($groupInfo->offer_type) {
			case 'order':
				foreach ($groupStaff as $staff) {

					$staffID = $staff->staffID;
					$conflicts = $this->CI->availability_library->checkAvailabilityById($lessonID, $staffID, $lesson_start, $lesson_end);

					if (!empty($conflicts)) {
						continue;
					}

					// if already staffed or staff excluded, skip
					if ($this->check_if_fully_staffed_by_role($lesson_info, $staffType) || in_array($staffID, $exclude_staff)) {
						continue;
					}

					// offer session and send email
					if ($this->offer_lesson($lessonID, $staffID, $staffType, 'group', $groupID, $salaried)) {
						// add to excluded for future loops
						$required_staff[$staffType]--;
						$assigned_staff++;
						break;
					}
				}
				break;
			case 'all':
				foreach ($groupStaff as $staff) {
					$staffID = $staff->staffID;
					$conflicts = $this->CI->availability_library->checkAvailabilityById($lessonID, $staffID, $lesson_start, $lesson_end);

					if (!empty($conflicts)) {
						continue;
					}
					// if already staffed or staff excluded, skip
					if ($this->check_if_fully_staffed_by_role($lesson_info, $staffType) || in_array($staffID, $exclude_staff)) {
						continue;
					}

					if ($this->offer_lesson($lessonID, $staffID, $staffType, 'group', $groupID, $salaried)) {
						$assigned_staff++;
					}
				}
				break;
			case 'auto':
				// check availability
				$search_vars = array(
					'startDate' => mysql_to_uk_date($lesson_start),
					'endDate' => mysql_to_uk_date($lesson_end),
					'startTimeH' => substr($lesson_info->startTime, 0, 2),
					'startTimeM' => substr($lesson_info->startTime, 3, 2),
					'endTimeH' => substr($lesson_info->endTime, 0, 2),
					'endTimeM' => substr($lesson_info->endTime, 3, 2),
					'booking' => $lesson_info->bookingID,
					'day' => $lesson_info->day,
					'lesson' => $lessonID,
					'activityID' => $lesson_info->activityID,
					'staffType' => $staffType,
				);
				$availability_res = $this->CI->availability_library->check_availability($search_vars);

				$grouped_users = [];
				foreach ($groupStaff as $staff) {
					$grouped_users[] = $staff->staffID;
				}

				foreach ($availability_res as $details) {
					// if details not array, staff excluded, skip
					if (!array_key_exists('status', $details)) {
						continue;
					}

					if (!in_array($details['staffID'], $grouped_users)) {
						continue;
					}

					if (in_array($details['staffID'], $exclude_staff)) {
						continue;
					}

					if ($details['status'] != 'green') {
						continue;
					}

					if ($this->offer_lesson($lessonID, $details['staffID'], $staffType, 'group', $groupID, $salaried)) {
						$assigned_staff++;
						break;
					}
				}

				break;
		}

		if ($assigned_staff < 1) {
			$this->setErrors('No member of staff in the group selected is available. Please select another group');
			$this->try_to_decline_offers($lessonID);
			return false;
		}

		return true;
	}

	/**
	 * offer session to coaches
	 * @param  integer $lessonID
	 * @return mixed
	 */
	public function offer($lessonID = NULL) {

		// check params
		if (empty($lessonID)) {
			return FALSE;
		}

		//if it is not web session get account from lesson
		if (!$this->CI->auth->user) {
			$res = $this->CI->db->from('bookings_lessons')->where([
				'lessonID' => $lessonID
			])->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $lesson_info);

			$accountId = $lesson_info->accountID;
		} else {
			$accountId = $this->CI->auth->user->accountID;
		}

		// look up lesson
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $accountId,
			'offer_accept_status' => 'offering'
		);
		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $lesson_info);
		// look up block
		$where = array(
			'blockID' => $lesson_info->blockID,
			'accountID' => $accountId
		);
		$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $block_info);

		// check session dates to see if already started
		$lesson_start = $lesson_info->startDate;
		if (empty($lesson_start)) {
			$lesson_start = $block_info->startDate;
		}
		if (strtotime($lesson_start) <= strtotime(date('Y-m-d'))) {
			// in past, expire offer
			$this->expire_offers($lessonID, 'Already Started');
			return FALSE;
		}

		// compare staffing levels
		$required_staff = array(
			'head' => $lesson_info->staff_required_head,
			'lead' => $lesson_info->staff_required_lead,
			'assistant' => $lesson_info->staff_required_assistant,
			'observer' => $lesson_info->staff_required_observer,
			'participant' => $lesson_info->staff_required_participant
		);
		$pending_acceptance = array(
			'head' => 0,
			'lead' => 0,
			'assistant' => 0,
			'observer' => 0,
			'participant' => 0
		);

		// track staff to exclude
		$exclude_staff = array();

		// get staff already on lesson
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $accountId
		);
		$res = $this->CI->db->from('bookings_lessons_staff')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$exclude_staff[] = $row->staffID;
				// reduce required staff
				$required_staff[$row->type]--;
			}
		}

		// get staff already asked
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $accountId
		);
		$res = $this->CI->db->from('offer_accept')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$exclude_staff[] = $row->staffID;
				// check if any pending acceptance
				if ($row->status == 'offered') {
					$pending_acceptance[$row->type]++;
				}
			}
		}

		// check if already fully staffed
		if ($this->check_if_fully_staffed($lesson_info)) {
			$this->expire_offers($lessonID, 'Fully Staffed', 'assigned');
			return FALSE;
		}

		// get requried staff after subrtracting pending acceptance
		foreach ($required_staff as $type => $count) {
			// if no staff required, set to exhausted
			if ($count <= 0) {
				$required_staff[$type] = 'exhausted';
			} else {
				$required_staff[$type] = $count - $pending_acceptance[$type];
			}
		}

		// check if potentially fully staffed
		if ($required_staff['head'] <= 0 && $required_staff['lead'] <= 0 && $required_staff['assistant'] <= 0 && $required_staff['observer'] <= 0 && $required_staff['participant'] <= 0) {
			// yes, skip
			return FALSE;
		}

		// work out end date
		$lesson_end = $lesson_info->endDate;
		if (empty($lesson_end)) {
			$lesson_end = $block_info->endDate;
		}

		// load availability library
		$this->CI->load->library('availability_library');

		// loop required roles and nums
		foreach ($required_staff as $type => $count) {
			// skip if none required or already exhausted
			if ($count == 'exhausted' || $count <= 0) {
				continue;
			}
			// check availability
			$search_vars = array(
				'startDate' => mysql_to_uk_date($lesson_start),
				'endDate' => mysql_to_uk_date($lesson_end),
				'startTimeH' => substr($lesson_info->startTime, 0, 2),
				'startTimeM' => substr($lesson_info->startTime, 3, 2),
				'endTimeH' => substr($lesson_info->endTime, 0, 2),
				'endTimeM' => substr($lesson_info->endTime, 3, 2),
				'booking' => $lesson_info->bookingID,
				'day' => $lesson_info->day,
				'lesson' => $lessonID,
				'activityID' => $lesson_info->activityID,
				'staffType' => $type,
			);
			$availability_res = $this->CI->availability_library->check_availability($search_vars);

			$assigned_staff = array();
			foreach ($availability_res as $details) {
				$staffID = $details['staffID'];
				// if details not array, staff excluded, skip
				if (!array_key_exists('status', $details) || in_array($staffID, $exclude_staff)) {
					continue;
				}

				if ($details['status'] != 'green') {
					// check if exhausted
					if (count($assigned_staff) == 0) {
						$required_staff[$type] = 'exhausted';
					}
					break;
				}

				// offer session and send email
				if ($this->offer_lesson($lessonID, $staffID, $type)) {
					// add to excluded for future loops
					$exclude_staff[] = $staffID;
					$assigned_staff[] = $staffID;
					$required_staff[$type]--;
				}

				if ($required_staff[$type] <= 0) {
					break;
				}
			}
		}

		// check if all types exhausted
		if ($required_staff['head'] === 'exhausted' && $required_staff['lead'] === 'exhausted' && $required_staff['assistant'] === 'exhausted' && $required_staff['observer'] === 'exhausted' && $required_staff['participant'] === 'exhausted') {
			$this->expire_offers($lessonID, NULL, 'exhausted');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * expire offers
	 * @param  integer $lessonID
	 * @return boolean
	 */
	public function expire_offers($lessonID = NULL, $reason = NULL, $status = 'expired', $offer_type = 'auto') {
		// check params
		if (empty($lessonID)) {
			return FALSE;
		}

		//if it is not web session get account from lesson
		if (!$this->CI->auth->user) {
			$res = $this->CI->db->from('bookings_lessons')->where([
				'lessonID' => $lessonID
			])->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $lesson_info);

			$accountId = $lesson_info->accountID;
		} else {
			$accountId = $this->CI->auth->user->accountID;
		}

		// look up lesson
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $accountId,
			'offer_accept_status' => 'offering'
		);
		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $lesson_info);

		// set session to expired
		$data = array(
			'offer_accept_status' => $status,
			'offer_accept_reason' => $reason,
			'modified' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $accountId,
			'offer_accept_status' => 'offering'
		);
		$this->CI->db->update('bookings_lessons', $data, $where, 1);

		// set offers to expired
		$data = array(
			'status' => 'expired',
			'reason' => $reason,
			'modified' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $accountId,
			'status' => 'offered'
		);
		$this->CI->db->update('offer_accept', $data, $where);

		// if exhausted, send email
		if ($status == 'exhausted') {

			$email_type = '';
			if ($offer_type == 'manual') {
				$email_type = '_manual';
			}

			// get email template
			$to = $this->CI->settings_library->get('email_offer_accept_notifications_to' . $email_type, $lesson_info->accountID);
			$subject = $this->CI->settings_library->get('email_offer_accept_exhausted_subject' . $email_type, $lesson_info->accountID);
			$email_html = $this->CI->settings_library->get('email_offer_accept_exhausted' . $email_type, $lesson_info->accountID);


			// if no to, skip
			if (empty($to)) {
				return TRUE;
			}

			$smart_tags = array(
				'details' => $this->lesson_details($lessonID),
				'link' => '<a href="' . site_url('sessions/staff/' . $lessonID) . '">' . site_url('sessions/staff/' . $lessonID) . '</a>'
			);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			$smart_tags = array();
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
			}

			// send email
			if (($this->CI->settings_library->get('send_offer_accept_emails', $lesson_info->accountID) == 1 && $offer_type == 'auto') ||
				($this->CI->settings_library->get('send_offer_accept_manual_emails', $lesson_info->accountID) == 1 && $offer_type == 'manual')) {
				$this->CI->crm_library->send_email($to, $subject, $email_html, array(), TRUE, $lesson_info->accountID);
			}
		}

		// all ok
		return TRUE;
	}

	public function sendOfferAcceptEmail($to, $subject, $emailHtml, $lessonInfo, $offerType) {
		$emailType = '';
		if ($offerType == 'manual') {
			$emailType = '_manual';
		}

		// get email template
		$to = $this->CI->settings_library->get($to . $emailType, $lessonInfo->accountID);
		$subject = $this->CI->settings_library->get($subject . $emailType, $lessonInfo->accountID);
		$email_html = $this->CI->settings_library->get($emailHtml . $emailType, $lessonInfo->accountID);

		// if no to, skip
		if (empty($to)) {
			return TRUE;
		}

		$smart_tags = array(
			'details' => $this->lesson_details($lessonInfo->lessonID),
			'link' => '<a href="' . site_url('sessions/staff/' . $lessonInfo->lessonID) . '">' . site_url('sessions/staff/' . $lessonInfo->lessonID) . '</a>'
		);

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		// replace smart tags in subject
		$smart_tags = array();
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
		}

		// send email
		if (($this->CI->settings_library->get('send_offer_accept_emails', $lessonInfo->accountID) == 1 && $offerType == 'auto') ||
			($this->CI->settings_library->get('send_offer_accept_manual_emails', $lessonInfo->accountID) == 1 && $offerType == 'manual')) {
			$this->CI->crm_library->send_email($to, $subject, $emailHtml, array(), TRUE, $lessonInfo->accountID);
		}
	}

	/**
	 * offer staff lesson
	 * @param  integer $lessonID
	 * @param  integer $staffID
	 * @param  string $type
	 * @return boolean
	 */
	public function offer_lesson($lessonID = NULL, $staffID = NULL, $type = NULL, $offer_type = 'auto', $groupId = NULL, $salaried = 0) {
		// check params
		if (empty($lessonID) || empty($staffID) || empty($type)) {
			return FALSE;
		}

		//if it is not web session get account from lesson
		if (!$this->CI->auth->user) {
			$res = $this->CI->db->from('bookings_lessons')->where([
				'lessonID' => $lessonID
			])->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $lesson_info);

			$accountId = $lesson_info->accountID;
		} else {
			$accountId = $this->CI->auth->user->accountID;
		}

		// look up lesson
		$where = array(
			'lessonID' => $lessonID,
			'accountID' => $accountId,
			'offer_accept_status' => 'offering'
		);
		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $lesson_info);

		// look up staff
		$where = array(
			'staffID' => $staffID,
			'accountID' => $accountId
		);
		$res = $this->CI->db->from('staff')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $staff_info);

		// add to db
		$data = array(
			'accountID' => $accountId,
			'lessonID' => $lessonID,
			'staffID' => $staffID,
			'byID' => $this->CI->auth->user->staffID,
			'type' => $type,
			'salaried' => intval($salaried),
			'status' => 'offered',
			'added' => mdate('%Y-%m-%d %H:%i:%s'),
			'modified' => mdate('%Y-%m-%d %H:%i:%s'),
			'offer_type' => $offer_type,
			'groupID' => $groupId
		);

		if (!empty($this->combined_lessons)) {
			$data['combined_with'] = json_encode($this->combined_lessons);
		}
		$res = $this->CI->db->insert('offer_accept', $data);
		if ($this->CI->db->affected_rows() == 0) {
			return FALSE;
		}

		// if not already sent an email for these sessions, send
		if (!in_array($staffID, $this->staff_offered)) {

			$email_type = '_manual';
			if ($offer_type == 'auto') {
				$email_type = '';
			}
			$link = '<a href="' . site_url('acceptance' . $email_type) . '">' . site_url('acceptance' . $email_type) . '</a>';

			// get email template
			$subject = $this->CI->settings_library->get('email_offer_accept_offer_subject' . $email_type, $lesson_info->accountID);
			$email_html = $this->CI->settings_library->get('email_offer_accept_offer' . $email_type, $lesson_info->accountID);

			$smart_tags = array(
				'first_name' => $staff_info->first,
				'link' => $link
			);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
			}

			// send email
			if (($this->CI->settings_library->get('send_offer_accept_emails', $lesson_info->accountID) == 1 && $offer_type == 'auto') ||
				($this->CI->settings_library->get('send_offer_accept_manual_emails', $lesson_info->accountID) == 1 && $offer_type != 'auto')) {
				$this->CI->crm_library->send_email($staff_info->email, $subject, $email_html, array(), TRUE, $lesson_info->accountID);
				$this->staff_offered[] = $staffID;
			}
		}
		return TRUE;
	}

	public function handle_manual_accept($lessonID, $offer_info) {
		// check params
		if (empty($lessonID)) {
			return FALSE;
		}

		$where = array(
			'lessonID' => $lessonID,
			'offer_accept_status' => 'offering'
		);

		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $lesson_info);

		if ($this->check_if_started($lesson_info)) {
			$this->expire_offers($lessonID, 'Already Started');
			return false;
		}

		if ($this->check_if_fully_staffed($lesson_info)) {
			$this->expire_offers($lessonID, 'Fully Staffed', 'assigned', 'manual');
			return false;
		}

		if ($this->check_if_fully_staffed_by_role($lesson_info, $offer_info->type)) {
			// set offers to expired
			$data = array(
				'status' => 'expired',
				'reason' => 'Fully Staffed',
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$where = array(
				'lessonID' => $lessonID,
				'status' => 'offered',
				'type' => $offer_info->type
			);
			$this->CI->db->update('offer_accept', $data, $where);
		}

		$where = array(
			'lessonID' => $lessonID,
			'status' => 'offered'
		);

		$res = $this->CI->db->from('offer_accept')->where($where)->get();

		//if no more offers - stop offering
		if ($res->num_rows() == 0) {
			$this->disable_offering($lessonID);
			return FALSE;
		}
	}

	private function disable_offering($lessonID) {
		$data = array(
			'offer_accept_status' => 'off',
			'modified' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'lessonID' => $lessonID,
			'offer_accept_status' => 'offering'
		);
		$this->CI->db->update('bookings_lessons', $data, $where, 1);
	}

	public function handle_manual_decline($lessonID) {
		$where = array(
			'lessonID' => $lessonID,
			'offer_accept_status' => 'offering'
		);

		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $lesson_info);


		if ($this->check_if_started($lesson_info)) {
			$this->expire_offers($lessonID, 'Already Started');
			return false;
		}

		$this->try_to_decline_offers($lessonID);

		return true;
	}


	//check if there are all offers declined or no active offers
	private function try_to_decline_offers($lessonID) {
		$declined_offers = [];
		$offered_offers = [];
		$where = array(
			'lessonID' => $lessonID
		);
		$res = $this->CI->db->from('offer_accept')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($row->status == 'offered') {
					$offered_offers[] = $row;
				}
				if ($row->status == 'declined') {
					$declined_offers[] = $row;
				}
			}

			//if all offers declined - set status to declined
			if ($res->num_rows() == count($declined_offers)) {
				$this->expire_offers($lessonID, NULL, 'exhausted', 'manual');
				return false;
			}

			if (count($offered_offers) < 1) {
				$this->disable_offering($lessonID);
				return false;
			}
		}
	}

	public function check_if_fully_staffed($lesson_info) {
		// compare staffing levels
		$required_staff = array(
			'head' => $lesson_info->staff_required_head,
			'lead' => $lesson_info->staff_required_lead,
			'assistant' => $lesson_info->staff_required_assistant,
			'observer' => $lesson_info->staff_required_observer,
			'participant' => $lesson_info->staff_required_participant
		);
		$staff_needed = array_sum($required_staff);
		$staff_count = 0;

		// get staff already on lesson
		$where = array(
			'lessonID' => $lesson_info->lessonID
		);
		$res = $this->CI->db->from('bookings_lessons_staff')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$required_staff[$row->type]--;
				$staff_count++;
			}
		}

		// if no staff req
		if ($staff_needed == 0) {
			// one staff needed of any type
			$staff_needed = 1;
			// if not enough staff, not full
			if ($staff_count < $staff_needed) {
				return false;
			}
			return true;
		// if each type is already full, lesson is full
		} else if ($required_staff['head'] <= 0 && $required_staff['lead'] <= 0 && $required_staff['assistant'] <= 0 && $required_staff['observer'] <= 0 && $required_staff['participant'] <= 0) {
			return true;
		}

		return false;
	}

	public function check_if_fully_staffed_by_role($lesson_info, $staff_type) {
		$required_staff = array(
			'head' => $lesson_info->staff_required_head,
			'lead' => $lesson_info->staff_required_lead,
			'assistant' => $lesson_info->staff_required_assistant,
			'observer' => $lesson_info->staff_required_observer,
			'participant' => $lesson_info->staff_required_participant
		);
		$staff_needed = array_sum($required_staff);
		$staff_count = 0;

		// get staff already on lesson
		$where = array(
			'lessonID' => $lesson_info->lessonID,
			'type' => $staff_type
		);
		$res = $this->CI->db->from('bookings_lessons_staff')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// reduce required staff
				$required_staff[$staff_type]--;
				// increase staff count
				$staff_count++;
			}
		}

		// if no staff req, dont check by type
		if ($staff_needed == 0) {
			// one staff needed of any type
			$staff_needed = 1;
			// if not enough staff, not full
			if ($staff_count < $staff_needed) {
				return false;
			}
			return true;
		// if each type is already full, lesson is full
	} else if ($required_staff[$staff_type] <= 0) {
			return true;
		}

		return false;
	}

	public function check_if_started($lesson_info) {
		// look up block
		$where = array(
			'blockID' => $lesson_info->blockID,
		);
		$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $block_info);

		// check session dates to see if already started
		$lesson_start = $lesson_info->startDate;
		if (empty($lesson_start)) {
			$lesson_start = $block_info->startDate;
		}
		if (strtotime($lesson_start) <= strtotime(date('Y-m-d'))) {
			// in past, expire offer
			return true;
		}

		return false;
	}

	/**
	 * accept an offer
	 * @param  integer $offerID
	 * @return boolean
	 */
	public function accept($offerID = NULL, $bulk = FALSE) {
		// check params
		if (empty($offerID)) {
			return FALSE;
		}

		// look up offer
		$where = array(
			'offerID' => $offerID,
			'status' => 'offered',
			'accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->from('offer_accept')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $offer_info);

		//process combined offers
		$combinedOffers[] = $offer_info;
		if ($offer_info->combined_with) {
			$combinedOffers = $this->CI->OffersModel->getCombinedOffers($offerID);
		}

		foreach ($combinedOffers as $offer) {
			// look up lesson$offer_info
			$where = array(
				'lessonID' => $offer->lessonID,
				'accountID' => $this->CI->auth->user->accountID
			);
			$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $lesson_info);

			// look up block
			$where = array(
				'blockID' => $lesson_info->blockID,
				'accountID' => $this->CI->auth->user->accountID
			);
			$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $block_info);

			// look up staff
			$where = array(
				'staffID' => $offer->staffID,
				'accountID' => $this->CI->auth->user->accountID
			);
			$res = $this->CI->db->from('staff')->where($where)->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $staff_info);

			// look up offer info
			$where = array(
				'staffID' => $offer->byID,
				'accountID' => $this->CI->auth->user->accountID
			);
			$res = $this->CI->db->from('staff')->where($where)->limit(1)->get();

			foreach ($res->result() as $offered_by_info);

			// assign to lesson
			$data = array(
				'lessonID' => $lesson_info->lessonID,
				'bookingID' => $lesson_info->bookingID,
				'startDate' => $lesson_info->startDate,
				'endDate' => $lesson_info->endDate,
				'startTime' => $lesson_info->startTime,
				'endTime' => $lesson_info->endTime,
				'staffID' => $offer->staffID,
				'type' => $offer->type,
				'salaried' => $offer->salaried,
				'comment' => 'Coach Accepted',
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $this->CI->auth->user->accountID
			);
			if (empty($data['startDate'])) {
				$data['startDate'] = $block_info->startDate;
			}
			if (empty($data['endDate'])) {
				$data['endDate'] = $block_info->endDate;
			}
			$res = $this->CI->db->insert('bookings_lessons_staff', $data);
			if ($this->CI->db->affected_rows() == 0) {
				return FALSE;
			}

			$data = array(
				'status' => 'accepted',
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$where = array(
				'offerID' => $offer->offerID,
				'accountID' => $this->CI->auth->user->accountID
			);
			$this->CI->db->update('offer_accept', $data, $where);
		}

		switch ($offer_info->offer_type) {
			case 'auto':
				$this->offer($offer_info->lessonID);
				break;
			case 'group':
				foreach ($combinedOffers as $offer) {
					$groupInfo = $this->CI->settings_library->getGroupInfo($offer->groupID);
					//we should not offer session if it was already offered to all staff
					if ($groupInfo->offer_type != 'all') {
						$this->offer_to_group($offer->lessonID, $offer->groupID, $offer->type);
					} else {
						$this->handle_manual_accept($offer->lessonID, $offer);
					}
				}
				break;
			case 'individual':
				//accept all combined offers
				foreach ($combinedOffers as $offer) {
					$this->handle_manual_accept($offer->lessonID, $offer);
				}
				// no need to offer to another staff - just decline
				break;
		}

		// if bulk, store details
		if ($bulk === TRUE) {
			$text = 'Member of Staff: <strong>' . $staff_info->first . ' ' . $staff_info->surname .
					'</strong><br>Details of Session: ' . $this->lesson_details($offer_info->lessonID) . '<br><br>';

			if(isset($offered_by_info)) {
				$text .= 'Offered By: <strong>' . $offered_by_info->first .' ' . $offered_by_info->surname .'</strong><br>
				Date and Time of Offer: ' . date('d/m/Y H:i:s', strtotime($offer_info->added)) . '<br><br>';
			}

			$this->bulk_lesson_details[] = $text;
		// else email
		} else {

			$email_type = '_manual';
			if ($offer_info->offer_type == 'auto') {
				$email_type = '';
			}
			$link = '<a href="' . site_url('acceptance' . $email_type) . '">' . site_url('acceptance' . $email_type) . '</a>';

			// get email template
			$to = $this->CI->settings_library->get('email_offer_accept_notifications_to' . $email_type, $lesson_info->accountID);
			$subject = $this->CI->settings_library->get('email_offer_accept_accepted_subject' . $email_type, $lesson_info->accountID);
			$email_html = $this->CI->settings_library->get('email_offer_accept_accepted' . $email_type, $lesson_info->accountID);

			// if no to, skip
			if (empty($to)) {
				return TRUE;
			}

			$text = 'Member of Staff: <strong>' . $staff_info->first . ' ' . $staff_info->surname .
					'</strong><br>Details of Session: ' . $this->lesson_details($offer_info->lessonID) . '<br><br>';

			if(isset($offered_by_info)) {
				$text .= 'Offered By: <strong>' . $offered_by_info->first .' ' . $offered_by_info->surname .'</strong><br>
				Date and Time of Offer: ' . date('d/m/Y H:i:s', strtotime($offer_info->added)) . '<br><br>';
			}

			$smart_tags = array(
				'details' => $text,
				'link' => $link
			);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			$smart_tags = array();
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
			}

			// send email
			if (($this->CI->settings_library->get('send_offer_accept_emails', $lesson_info->accountID) == 1 && $offer_info->offer_type == 'auto') ||
				($this->CI->settings_library->get('send_offer_accept_manual_emails', $lesson_info->accountID) == 1) && $offer_info->offer_type != 'auto') {
				$this->CI->crm_library->send_email($to, $subject, $email_html, array(), TRUE, $lesson_info->accountID);
			}
		}

		return TRUE;
	}

	/**
	 * decline an offer
	 * @param  integer $offerID
	 * @return boolean
	 */
	public function decline($offerID = NULL, $bulk = FALSE, $reason = 'declined') {
		// check params
		if (empty($offerID)) {
			return FALSE;
		}

		//if it is not web session get account from lesson
		if (!$this->CI->auth->user) {
			$res = $this->CI->db->from('offer_accept')->where([
				'offerID' => $offerID
			])->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $offer_info);

			$accountId = $offer_info->accountID;
		} else {
			$accountId = $this->CI->auth->user->accountID;
		}


		// look up offer
		$where = array(
			'offerID' => $offerID,
			'status' => 'offered',
			'accountID' => $accountId
		);
		$res = $this->CI->db->from('offer_accept')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $offer_info);

		//process combined offers
		$combinedOffers[] = $offer_info;
		if ($offer_info->combined_with) {
			$combinedOffers = $this->CI->OffersModel->getCombinedOffers($offerID);

			if (empty($this->combined_lessons)) {
				$this->combined_lessons = json_decode($offer_info->combined_with);
			}
		}

		foreach ($combinedOffers as $offer) {
			// look up lesson
			$where = array(
				'lessonID' => $offer->lessonID,
				'accountID' => $accountId
			);
			$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $lesson_info);

			// look up staff
			$where = array(
				'staffID' => $offer->staffID,
				'accountID' => $accountId
			);
			$res = $this->CI->db->from('staff')->where($where)->limit(1)->get();
			if ($res->num_rows() == 0) {
				return FALSE;
			}
			foreach ($res->result() as $staff_info);

			// look up offer info
			$where = array(
				'staffID' => $offer->byID,
				'accountID' => $accountId
			);
			$res = $this->CI->db->from('staff')->where($where)->limit(1)->get();

			foreach ($res->result() as $offered_by_info);

			// mark offer declined
			$data = array(
				'status' => $reason,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$where = array(
				'offerID' => $offer->offerID,
				'accountID' => $accountId
			);
			$this->CI->db->update('offer_accept', $data, $where);

		}

		switch ($offer_info->offer_type) {
			case 'auto':
				$this->offer($offer_info->lessonID);
				break;
			case 'group':
				foreach ($combinedOffers as $offer) {
					$groupInfo = $this->CI->settings_library->getGroupInfo($offer->groupID);
					//we should not offer session if it was already offered to all staff
					if ($groupInfo->offer_type != 'all') {
						$this->offer_to_group($offer->lessonID, $offer->groupID, $offer->type);
					} else {
						// no need to offer to another staff - just decline
						$this->handle_manual_decline($offer->lessonID);
					}
				}
				break;
			case 'individual':
				// no need to offer to another staff - just decline
				foreach ($combinedOffers as $offer) {
					$this->handle_manual_decline($offer->lessonID);
				}
				break;
		}

		// if bulk, store details
		if ($bulk === TRUE) {

			$text = 'Member of Staff: <strong>' . $staff_info->first . ' ' . $staff_info->surname .
					'</strong><br>Details of Session: ' . $this->lesson_details($offer_info->lessonID) . '<br><br>';

			if(isset($offered_by_info)) {
				$text .= 'Offered By: <strong>' . $offered_by_info->first .' ' . $offered_by_info->surname .'</strong><br>
				Date and Time of Offer: ' . date('d/m/Y H:i:s', strtotime($offer_info->added)) . '<br><br>';
			}

			$this->bulk_lesson_details[] = $text;
		// else email
		} else {
			$email_type = '_manual';
			if ($offer_info->offer_type == 'auto') {
				$email_type = '';
			}
			$link = '<a href="' . site_url('acceptance' . $email_type) . '">' . site_url('acceptance' . $email_type) . '</a>';

			// get email template
			$to = $this->CI->settings_library->get('email_offer_accept_notifications_to' . $email_type, $lesson_info->accountID);
			$subject = $this->CI->settings_library->get('email_offer_accept_declined_subject' . $email_type, $lesson_info->accountID);
			$email_html = $this->CI->settings_library->get('email_offer_accept_declined' . $email_type, $lesson_info->accountID);

			// if no to, skip
			if (empty($to)) {
				return TRUE;
			}

			$text = 'Member of Staff: <strong>' . $staff_info->first . ' ' . $staff_info->surname .
					'</strong><br>Details of Session: ' . $this->lesson_details($offer_info->lessonID) . '<br><br>';

			if(isset($offered_by_info)) {
				$text .= 'Offered By: <strong>' . $offered_by_info->first .' ' . $offered_by_info->surname .'</strong><br>
				Date and Time of Offer: ' . date('d/m/Y H:i:s', strtotime($offer_info->added)) . '<br><br>';
			}

			$smart_tags = array(
				'details' => $text,
				'link' => $link
			);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			$smart_tags = array();
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
			}

			// send email
			if (($this->CI->settings_library->get('send_offer_accept_emails', $lesson_info->accountID) == 1 && $offer_info->offer_type == 'auto') ||
				($this->CI->settings_library->get('send_offer_accept_manual_emails', $lesson_info->accountID) == 1 && $offer_info->offer_type != 'auto')) {
				$this->CI->crm_library->send_email($to, $subject, $email_html, array(), TRUE, $lesson_info->accountID);
			}
		}

		return TRUE;
	}

	/**
	 * bulk notify of accept/decline
	 * @param  string $type
	 * @return boolean
	 */
	public function bulk_notify($type = NULL, $manual = 0) {
		// check params
		if (!in_array($type, array('accept', 'decline'))) {
			return FALSE;
		}

		if (count($this->bulk_lesson_details) == 0) {
			return FALSE;
		}

		if ($type == 'accept') {
			$email_type = 'accepted';
		} else {
			$email_type = 'declined';
		}

		$email_additional_type = '';
		if ($manual) {
			$email_additional_type = '_manual';
		}
		$link = '<a href="' . site_url('acceptance' . $email_type) . '">' . site_url('acceptance' . $email_type) . '</a>';

		// get email template
		$to = $this->CI->settings_library->get('email_offer_accept_notifications_to' . $email_additional_type, $this->CI->auth->user->accountID);
		$subject = $this->CI->settings_library->get('email_offer_accept_' . $email_type . '_subject' . $email_additional_type, $this->CI->auth->user->accountID);
		$email_html = $this->CI->settings_library->get('email_offer_accept_' . $email_type . $email_additional_type, $this->CI->auth->user->accountID);

		// if no to, skip
		if (empty($to)) {
			return TRUE;
		}

		$smart_tags = array(
			'details' => implode('<br />', $this->bulk_lesson_details),
			'link' => $link
		);

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		// replace smart tags in subject
		$smart_tags = array();
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
		}

		// send email
		if (($this->CI->settings_library->get('send_offer_accept_emails', $this->CI->auth->user->accountID) == 1 && !$manual)
		|| ($this->CI->settings_library->get('send_offer_accept_manual_emails', $this->CI->auth->user->accountID) == 1 && $manual)) {
			$this->CI->crm_library->send_email($to, $subject, $email_html, array(), TRUE, $this->CI->auth->user->accountID);
		}

		return TRUE;
	}

	/**
	 * get session details
	 * @param  integer $lessoID
	 * @return mixed
	 */
	public function lesson_details($lessonID = NULL) {
		// check params
		if (empty($lessonID)) {
			return FALSE;
		}

		// look up offer
		$where = array(
			'bookings_lessons.lessonID' => $lessonID,
			'bookings_lessons.accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->select('bookings_lessons.*,
									bookings_lessons.startDate as lesson_start,
									bookings_lessons.endDate as lesson_end,
									bookings_blocks.startDate as block_start,
									bookings_blocks.endDate as block_end,
									bookings.name as event_name,
									orgs.name as booking_org,
									orgs_blocks.name as block_org')
								->from('bookings_lessons')
								->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
								->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
								->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
								->join('orgs as orgs_blocks', 'bookings_blocks.orgID = orgs_blocks.orgID', 'left')
								->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
								->where($where)
								->group_by('bookings_lessons.lessonID')->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $lesson_info);

		$return = NULL;

		if (!empty($lesson_info->event_name)) {
			$return .= $lesson_info->event_name . ' - ';
		}

		if (!empty($lesson_info->block_org)) {
			$return .= $lesson_info->block_org;
		} else if (!empty($lesson_info->booking_org)) {
			$return .= $lesson_info->booking_org;
		}

		$lesson_start = $lesson_info->lesson_start;
		if (empty($lesson_start)) {
			$lesson_start = $lesson_info->block_start;
		}
		$lesson_end = $lesson_info->lesson_end;
		if (empty($lesson_end)) {
			$lesson_end = $lesson_info->block_end;
		}

		$return .=  '<br />' . mysql_to_uk_date($lesson_start);
		if ($lesson_start != $lesson_end) {
			$return .=  '-' . mysql_to_uk_date($lesson_end);
		}

		$return .= ' on ' . ucwords($lesson_info->day) . 's - ' . substr($lesson_info->startTime, 0, 5) . '-' .substr($lesson_info->endTime, 0, 5);

		return $return;
	}

	public function get_lesson_offers($lessonId) {
		$where = [
			'offer_accept.lessonID' => $lessonId
		];

		$result = [];

		$query = $this->CI->db->select('offer_accept.*, staff.first, staff.surname, bookings_lessons.startTime,
			bookings_lessons.day, bookings_lessons.location, bookings_lessons.type_other, bookings_lessons.activity_other,
			bookings_lessons.activity_desc, bookings_lessons.group, bookings_lessons.group_other,
			bookings_lessons.class_size, bookings_lessons.endTime, bookings_lessons.startDate as lesson_start,
			bookings_lessons.endDate as lesson_end, bookings_blocks.startDate as block_start,
			bookings_blocks.endDate as block_end, activities.name as activity, lesson_types.name as lesson_type,
			orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town,
			orgs_addresses.county, orgs_addresses.postcode, bookings.name as event_name,
			orgs.name as booking_org, bookings.bookingID, bookings.project, orgs_blocks.name as block_org')
			->from('offer_accept')
			->join('bookings_lessons', 'offer_accept.lessonID = bookings_lessons.lessonID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('staff', 'offer_accept.staffID = staff.staffID', 'inner')
			->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as orgs_blocks', 'bookings_blocks.orgID = orgs_blocks.orgID', 'left')
			->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
			->where($where)
			->order_by('offer_accept.added desc')
			->group_by('offer_accept.offerID')->get();

		foreach ($query->result() as $row) {
			$result[] = $row;
		}

		return $result;
	}

	public function process_offers_timeout() {
		$query = $this->CI->db->select('offer_accept.*, accounts.addon_offer_accept, accounts.accountID')
			->from('offer_accept')
			->join('staff', 'offer_accept.staffID = staff.staffID', 'inner')
			->join('accounts', 'staff.accountID = accounts.accountID', 'inner')
			->where([
				'offer_accept.status' => 'offered',
				'offer_accept.offer_type' => 'auto'
			])->get();

		$processed_offers = 0;
		foreach ($query->result() as $offer) {
			if ($offer->addon_offer_accept == 0) {
				continue;
			}

			$timeout = $this->CI->settings_library->get('offer_accept_timeout', $offer->accountID);

			$offer_added = strtotime($offer->added);

			$timeout_seconds = $timeout * 60 * 60;

			// timeout passed
			if (time() > ($offer_added + $timeout_seconds)) {
				$this->decline($offer->offerID, false, 'expired');
				$processed_offers++;
			}
		}

		return $processed_offers;
	}

	public function process_manual_offers_timeout() {
		$query = $this->CI->db->select('offer_accept.*, accounts.addon_offer_accept_manual, accounts.accountID')
			->from('offer_accept')
			->join('staff', 'offer_accept.staffID = staff.staffID', 'inner')
			->join('accounts', 'staff.accountID = accounts.accountID', 'inner')
			->where([
				'offer_accept.status' => 'offered'
			])
			->where_in('offer_accept.offer_type', ['group', 'individual'])
			->get();

		$processed_offers = 0;
		foreach ($query->result() as $offer) {
			if ($offer->addon_offer_accept_manual == 0) {
				continue;
			}

			$timeout = $this->CI->settings_library->get('offer_accept_timeout_manual', $offer->accountID);

			$offer_added = strtotime($offer->added);

			$timeout_seconds = $timeout * 60 * 60;

			// timeout passed
			if (time() > ($offer_added + $timeout_seconds)) {
				$this->decline($offer->offerID, false, 'expired');
				$processed_offers++;
			}
		}

		return $processed_offers;
	}
}
