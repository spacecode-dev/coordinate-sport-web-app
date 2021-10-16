<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Participants extends MY_Controller {

	public $bikeability_levels = array();
	public $bikeability_levels_overall = array();

	public function __construct() {
		// allow all as coaches need to view print version, but restrict within individual controllers
		parent::__construct(FALSE, array(), array(), array('participants'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}

		$this->bikeability_levels = array(
			'1.1' => 'Demonstrate understanding of safety equipment and clothing',
			'1.2' => 'Carry out a simple bike check',
			'1.3' => 'Get on and off the bike without help',
			'1.4' => 'Start off and pedal without help',
			'1.5' => 'Stop without help',
			'1.6' => 'Ride along without help for roughly one minute or more',
			'1.7' => 'Make the bike go where they want',
			'1.8' => 'Use gears (where present)',
			'1.9' => 'Stop quickly with control',
			'1.10' => 'Manoeuvre safely to avoid objects',
			'1.11' => 'Look all around, including behind, without loss of control',
			'1.12' => 'Control the bike with one hand',
			'1.13' => 'Share space with pedestrians and other cyclists (not compulsory)',
			'2.1' => 'All Level 1 Outcomes',
			'2.2' => 'Start an on road journey',
			'2.3' => 'Finish an on road journey',
			'2.4' => 'Be aware of potential hazards',
			'2.5' => 'Understand how and when to signal intentions to other road users',
			'2.6' => 'Understand where to ride on roads being used',
			'2.7' => 'Pass parked or slower moving vehicles',
			'2.8' => 'Pass side roads',
			'2.9' => 'Turn left into minor road',
			'2.10' => 'Make a U-turn',
			'2.11' => 'Turn left into a major road',
			'2.12' => 'Turn right into a major road',
			'2.13' => 'Turn right from a major to minor road',
			'2.14' => 'Demonstrate decision-making and understanding of safe riding strategy',
			'2.15' => 'Demonstrate a basic understanding of the Highway Code.',
			'2.16' => 'Decide where cycle infrastructure can help a journey and demonstrate correct use ',
			'2.17' => 'Go straight on from minor road to minor road at a crossroad',
			'2.18' => 'Use mini-roundabouts and single lane roundabouts',
			'3.1' => 'All Level 2 manoeuvres',
			'3.2' => 'Preparing for a journey',
			'3.3' => 'Understanding advanced road positioning',
			'3.4' => 'Passing queuing traffic',
			'3.5' => 'Hazard perception and strategy to deal with hazards',
			'3.6' => 'Understanding driver blind, spots, particularly for large vehicles',
			'3.7' => 'Reacting to hazardous road surfaces',
			'3.8' => 'How to use roundabouts',
			'3.9' => 'How to use junctions controlled by traffic lights',
			'3.10' => 'How to use multi-lane roads',
			'3.11' => 'How to use both on and off road cycle infrastructure',
			'3.12' => 'Dealing with vehicles that pull in and stop front of you',
			'3.13' => 'Sharing the	road with other cyclists',
			'3.14' => 'Cycling on roads with a speed limit above 30 mph',
			'3.15' => 'Cycling in bus lanes',
			'3.16' => 'Cycling in pairs or groups',
			'3.17' => 'Locking a bike securely'
		);

		$this->bikeability_levels_overall = array(
			1 => 'Level 1',
			2 => 'Level 2',
			3 => 'Level 3'
		);
	}

	/**
	 * show list of participants
	 * @return void
	 */
	public function index($blockID = NULL, $lessonID = NULL, $print_view = 1, $export = FALSE, $familyID = NULL) {

		if ($blockID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$block_info = $row;
			$this->session->set_userdata('last_blockID', $blockID);
			$bookingID = $block_info->bookingID;
		}

		// look up
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// if names only
		if (in_array($booking_info->register_type, array('names', 'bikeability', 'shapeup'))) {
			if ($export === TRUE) {
				return $this->names($blockID, TRUE);
			}
			return $this->names($blockID);
		}

		// if numbers only
		if ($booking_info->register_type == 'numbers') {
			return $this->numbers($blockID);
		}

		// get booking days
		$booking_info->days = array();
		$lesson_days = array();

		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_lessons')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$booking_info->days[$row->day] = $row->day;
				$lesson_days[$row->lessonID] = $row->day;
			}
		}

		// deny from coaches + full time coach, if not printing
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			if ($print_view !== 2) {
				show_404();
			} else {
				// check permission
				if ($this->check_permission($bookingID) !== TRUE) {
					show_404();
				}
			}
		}

		if (!empty($lessonID)) {
			$print_link = 'bookings/participants/print/' . $blockID . '/' . $lessonID;
		} else {
			$print_link = 'bookings/participants/print/' . $blockID;
		}

		// set defaults
		$icon = 'user';
		$tab = 'blocks';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$page_base = 'bookings/participants/' . $blockID;
		$search_base = $page_base;
		if ($print_view === 2) {
			$page_base = 'bookings/participants/print/' . $blockID;
		}
		$section = 'bookings';
		$title = 'Participants';
		$buttons = NULL;
		if (!in_array($booking_info->register_type, array('numbers', 'names', 'bikeability', 'shapeup'))) {
			$buttons = '<a class="btn btn-success" href="' . site_url('booking/book/' . $blockID) . '"><i class="far fa-plus"></i> Create New</a> ';
		}
		$buttons .= '<a class="btn btn-primary" href="' . site_url($print_link) . '" target="_blank"><i class="far fa-print"></i> Print</a>';
		if (empty($lessonID)) {
			$buttons .= ' <a class="btn btn-primary" href="' . site_url('bookings/participants/export/' . $blockID) . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		}
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$items = array();
		$booked_lessons = array();

		// get sessions
		$where = array(
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);
		$sessions = $this->db->select('bookings_lessons.*, activities.name as activity, lesson_types.name as type')->from('bookings_lessons')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->where($where)->order_by('bookings_lessons.day asc, bookings_lessons.startTime asc, bookings_lessons.endTime asc')->get();

		// get tabs
		$tabs = array();
		$dateArray = array();
		if ($sessions->num_rows() > 0) {
			$i = 1;
			foreach ($sessions->result() as $session) {
				$session_desc = ucwords(substr($session->day, 0, 3)) . ' (' . substr($session->startTime, 0, 5) . ' to ' . substr($session->endTime, 0, 5) . ')';
				if(!in_array($session->startDate, $dateArray) && $session->startDate != "" && $session->startDate != '0000-00-00')
					if($lessonID == $session->lessonID)
						$dateArray[] = $session->startDate;
				if (!empty($session->activity)) {
					$session_desc .= ' - ' . $session->activity;
				} else if (!empty($session->activity_other)) {
					$session_desc .= ' - ' . $session->activity_other;
				}
				if (!empty($session->type)) {
					$session_desc .= ' - ' . $session->type;
				} else if (!empty($session->type_other)) {
					$session_desc .= ' - ' . $session->type_other;
				}

				$tabs[$session->lessonID] = array(
					'startTime' => $session->startTime,
					'endTime' => $session->endTime,
					'typeID' => $session->typeID,
					'activityID' => $session->activityID,
					'desc' => $session_desc
				);
				$i++;
			}
		}

		if (!empty($lessonID)) {
			// check tab exists
			if (!array_key_exists($lessonID, $tabs)) {
				$lessonID = NULL;
			} else {
				$search_base .= '/' . $lessonID;
			}
		}

		// set where
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'child' => NULL,
			'contact' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_contact', 'Contact', 'trim|xss_clean');
			$this->form_validation->set_rules('search_child', 'Child', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['contact'] = set_value('search_contact');
			$search_fields['child'] = set_value('search_child');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings-participants'))) {

			foreach ($this->session->userdata('search-bookings-participants') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// store search fields
			$this->session->set_userdata('search-bookings-participants', $search_fields);

			if ($search_fields['contact'] != '') {
				if (strpos($booking_info->register_type, 'individuals') === 0) {
					$search_where[] = "CONCAT_WS(' ', " . $this->db->dbprefix('family_contacts') . ".title, " . $this->db->dbprefix('family_contacts') . ".first_name, " . $this->db->dbprefix('family_contacts') . ".last_name) LIKE '%" . $this->db->escape_like_str($search_fields['contact']) . "%'";
				} else {
					// children register, search booker
					$search_where[] = "CONCAT_WS(' ', `booker`.title, `booker`.first_name, `booker`.last_name) LIKE '%" . $this->db->escape_like_str($search_fields['contact']) . "%'";
				}
			}

			if ($search_fields['child'] != '') {
				$search_where[] = "CONCAT_WS(' ', " . $this->db->dbprefix('family_children') . ".first_name, " . $this->db->dbprefix('family_children') . ".last_name) LIKE '%" . $this->db->escape_like_str($search_fields['child']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		$where = array(
			'bookings_cart_sessions.bookingID' => $bookingID,
			'bookings_cart.type' => 'booking',
			'bookings_lessons.blockID' => $blockID
		);

		// order by booker, unless on session view or individual register
		$order_by = 'booker.first_name asc, booker.last_name asc, family_contacts.first_name asc, family_contacts.last_name asc, family_children.first_name asc, family_children.last_name asc';
		if ($lessonID !== NULL || in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
			$order_by = 'family_contacts.first_name asc, family_contacts.last_name asc, family_children.first_name asc, family_children.last_name asc';
		}

		// if tab index set, limit
		if ($lessonID !== NULL) {
			$where['bookings_lessons.lessonID'] = $lessonID;
		}

		// children
		$res_children = $this->db->select('bookings_cart.byID, bookings_cart.familyID, bookings_cart.contactID as booker_contactID, bookings_cart_sessions.contactID,
		family_children.medical, family_children.behavioural_info, family_children.disability_info,
		family_contacts.medical as ac_medical, family_contacts.behavioural_info as ac_behavioural_info, family_contacts.disability_info as ac_disability_info
		, booker.title as booker_title, booker.first_name as booker_first, booker.last_name as booker_last, family_contacts.title as contact_title, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, family_contacts.dob as contact_dob, family_children.dob as child_dob, family_contacts.medical as contact_medical, booker.relationship, family_children.first_name as child_first, family_children.profile_pic as child_profile_pic, family_children.last_name as child_last, family_children.dob, family_children.pin, family_contacts.phone, family_contacts.mobile, family_contacts.workPhone, family_contacts.address1, family_contacts.address2, family_contacts.address3, family_contacts.town, family_contacts.county, family_contacts.postcode, family_contacts.email, family_contacts.profile_pic, booker.phone as booker_phone, booker.mobile as booker_mobile, booker.workPhone as booker_workPhone, booker.address1 as booker_address1, booker.address2 as booker_address2, booker.address3 as booker_address3, booker.town as booker_town, booker.county as booker_county, booker.postcode as booker_postcode, booker.email as booker_email, family_children.medical, family_children.photoConsent, bookings_cart.cartID, bookings_cart.childcarevoucher_providerID, family_children.childID, GROUP_CONCAT(' . $this->db->dbprefix('bookings_cart_sessions') . '.lessonID) as lessons, child_monitoring.monitoring1 as child_monitoring1, child_monitoring.monitoring2 as child_monitoring2, child_monitoring.monitoring3 as child_monitoring3, child_monitoring.monitoring4 as child_monitoring4, child_monitoring.monitoring5 as child_monitoring5, child_monitoring.monitoring6 as child_monitoring6, child_monitoring.monitoring7 as child_monitoring7, child_monitoring.monitoring8 as child_monitoring8, child_monitoring.monitoring9 as child_monitoring9, child_monitoring.monitoring10 as child_monitoring10, child_monitoring.monitoring11 as child_monitoring11, child_monitoring.monitoring12 as child_monitoring12, child_monitoring.monitoring13 as child_monitoring13, child_monitoring.monitoring14 as child_monitoring14, child_monitoring.monitoring15 as child_monitoring15, child_monitoring.monitoring16 as child_monitoring16, child_monitoring.monitoring17 as child_monitoring17, child_monitoring.monitoring18 as child_monitoring18, child_monitoring.monitoring19 as child_monitoring19, child_monitoring.monitoring20 as child_monitoring20,child_monitoring.notes as child_notes, contact_monitoring.monitoring1 as contact_monitoring1, contact_monitoring.monitoring2 as contact_monitoring2, contact_monitoring.monitoring3 as contact_monitoring3, contact_monitoring.monitoring4 as contact_monitoring4, contact_monitoring.monitoring5 as contact_monitoring5, contact_monitoring.monitoring6 as contact_monitoring6, contact_monitoring.monitoring7 as contact_monitoring7, contact_monitoring.monitoring8 as contact_monitoring8, contact_monitoring.monitoring9 as contact_monitoring9, contact_monitoring.monitoring10 as contact_monitoring10, contact_monitoring.monitoring11 as contact_monitoring11, contact_monitoring.monitoring12 as contact_monitoring12, contact_monitoring.monitoring13 as contact_monitoring13, contact_monitoring.monitoring14 as contact_monitoring14, contact_monitoring.monitoring15 as contact_monitoring15, contact_monitoring.monitoring16 as contact_monitoring16, contact_monitoring.monitoring17 as contact_monitoring17, contact_monitoring.monitoring18 as contact_monitoring18, contact_monitoring.monitoring19 as contact_monitoring19, contact_monitoring.monitoring20 as contact_monitoring20, contact_monitoring.notes as contact_notes, bookings_cart_sessions.attended, bookings_cart_sessions.signout,bookings_cart_sessions.signout_time,bookings_cart_sessions.attend_time, bikeability.bikeability_level as bikeability_level_overall, bikeability_contact.bikeability_level as bikeability_level_overall_contact, SUM(' . $this->db->dbprefix('bookings_cart_sessions') . '.total) as participant_total, SUM(' . $this->db->dbprefix('bookings_cart_sessions') . '.balance) as participant_balance')
		->from('bookings_cart')
		->join('family_contacts as booker', 'bookings_cart.contactID = booker.contactID', 'inner')
		->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
		->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
		->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
		->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
		->join('bookings_cart_monitoring as child_monitoring', 'bookings_cart.cartID = child_monitoring.cartID AND ' . $this->db->dbprefix('bookings_cart_sessions') . '.childID = child_monitoring.childID', 'left')
		->join('bookings_cart_monitoring as contact_monitoring', 'bookings_cart.cartID = contact_monitoring.cartID AND ' . $this->db->dbprefix('bookings_cart_sessions') . '.contactID = contact_monitoring.contactID', 'left')
		->join('bookings_cart_bikeability as bikeability', 'bookings_cart.cartID = bikeability.cartID AND ' . $this->db->dbprefix('bookings_cart_sessions') . '.childID = bikeability.childID', 'left')
		->join('bookings_cart_bikeability as bikeability_contact', 'bookings_cart.cartID = bikeability_contact.cartID AND ' . $this->db->dbprefix('bookings_cart_sessions') . '.contactID = bikeability_contact.contactID', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->group_by('bookings_cart_sessions.contactID, bookings_cart_sessions.childID, bookings_cart.cartID')
		->order_by($order_by)
		->get();


		// add to array
		if ($res_children->num_rows() > 0) {
			foreach ($res_children->result() as $row) {
				$row->type = 'child';
				if ($booking_info->register_type == 'individuals_bikeability') {
					$row->bikeability_level_overall = $row->bikeability_level_overall_contact;
				}
				$items[] = $row;
			}
		}

		// staff participants - only on lessons, not payment page or export
		if ($lessonID !== NULL) {
			$where = array(
				'bookings_lessons_staff.lessonID' => $lessonID,
				'bookings_lessons_staff.type' => 'participant',
				'bookings_lessons_staff.accountID' => $this->auth->user->accountID
			);

			// edit search to work with staff
			$search_where = array();

			if (isset($is_search) && $is_search === TRUE) {

				if ($search_fields['child'] != '') {
					$search_where[] = "CONCAT_WS(' ', " . $this->db->dbprefix('staff') . ".first, " . $this->db->dbprefix('staff') . ".surname) LIKE '%" . $this->db->escape_like_str($search_fields['child']) . "%'";
				}

				if (count($search_where) > 0) {
					$search_where = '(' . implode(' AND ', $search_where) . ')';
				}
			}

			$res_staff = $this->db->select('staff.staffID, staff.first, staff.surname, staff.dob, staff.email, staff.medical')->from('bookings_lessons_staff')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->where($where)->where($search_where, NULL, FALSE)->group_by('staff.staffID')->order_by('staff.first, staff.surname')->get();

			// add to array
			if ($res_staff->num_rows() > 0) {

				foreach ($res_staff->result() as $row) {
					$actual_row = new stdClass;
					$actual_row->type = 'participant';
					$actual_row->cartID = 'staff' . $row->staffID;
					$actual_row->contactID = $row->staffID;
					$actual_row->childID = $row->staffID;
					$actual_row->child_first = $row->first;
					$actual_row->child_last = $row->surname;
					$actual_row->dob = NULL;
					$actual_row->childcarevoucher_providerID = NULL;
					$actual_row->booker_title = NULL;
					$actual_row->booker_first = NULL;
					$actual_row->booker_last = NULL;
					$actual_row->contact_title = NULL;
					$actual_row->contact_first = NULL;
					$actual_row->contact_last = NULL;
					$actual_row->contact_dob = NULL;
					$actual_row->contact_medical = NULL;
					$actual_row->lessons = NULL;

					if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
						$actual_row->child_first = NULL;
						$actual_row->child_last = NULL;
						$actual_row->dob = NULL;
						$actual_row->contact_first = $row->first;
						$actual_row->contact_last = $row->surname;
						$actual_row->contact_dob = NULL;
						$actual_row->contact_medical = NULL;
					}

					// check for main address
					$where = array(
						'staffID' => $row->staffID,
						'type' => 'main',
						'accountID' => $this->auth->user->accountID
					);
					$res_address = $this->db->from('staff_addresses')->where($where)->order_by('modified desc')->limit(1)->get();
					if ($res_address->num_rows() > 0) {
						foreach ($res_address->result() as $row_address) {
							$actual_row->phone = NULL;
							$actual_row->mobile = NULL;
						}
					} else {
						$actual_row->phone = NULL;
						$actual_row->mobile = NULL;
					}

					$actual_row->photoConsent = NULL;
					$actual_row->medical = NULL;
					$actual_row->byID = NULL;

					$items[] = $actual_row;

					$booked_lessons[$actual_row->cartID][$actual_row->contactID] = array();

					// get lessons
					$where = array(
						'bookings_lessons_staff.lessonID' => $lessonID,
						'staffID' => $row->staffID,
						'bookings_lessons_staff.accountID' => $this->auth->user->accountID
					);
					$res_lessons = $this->db->select('bookings_lessons_staff.*, bookings_lessons.day')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->get();

					if ($res_lessons->num_rows() > 0) {
						foreach ($res_lessons->result() as $lesson) {
							if ($booking_info->type == 'booking') {
								$date = $lesson->startDate;
								while (strtotime($date) <= strtotime($lesson->endDate)) {
									$day = strtolower(date("l", strtotime($date)));
									if ($day ==  $lesson->day) {
										$booked_lessons[$actual_row->cartID][$actual_row->contactID][$lesson->lessonID][$date] = array(
											'sessionID' => NULL,
											'attended' => 'staff',
											'signout' => 'staff',
											'signout_time' => NULL,
											'attend_time' => NULL
										);
									}
									$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
								}
							} else {
								$booked_lessons[$actual_row->cartID][$actual_row->contactID][$lesson->lessonID][$lesson->day] = array(
									'sessionID' => NULL,
									'attended' => 'staff',
									'signout' => 'staff',
									'signout_time' => NULL,
									'attend_time' => NULL
								);
							}
						}
					}
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get days in booking
		$lesson_ids = array();
		$days = array();
		$where = array(
			'bookingID' => $bookingID,
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);
		// if tab index set, limit
		if ($lessonID !== NULL) {
			$where['bookings_lessons.startTime'] = $tabs[$lessonID]['startTime'];
			$where['bookings_lessons.endTime'] = $tabs[$lessonID]['endTime'];
			$where['bookings_lessons.typeID'] = $tabs[$lessonID]['typeID'];
			$where['bookings_lessons.activityID'] = $tabs[$lessonID]['activityID'];
			$where['bookings_lessons.blockID'] = $blockID;
		}
		$res = $this->db->from('bookings_lessons')->where($where)->group_by('day')->order_by('day asc')->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// get sessions on this day
				$where_day = array(
					'bookings_lessons.bookingID' => $bookingID,
					'bookings_lessons.day' => $row->day,
					'bookings_lessons.blockID' => $blockID,
					'bookings_lessons.accountID' => $this->auth->user->accountID
				);
				$res_lessons = $this->db->select('bookings_lessons.*, lesson_types.name as type, lesson_types.show_label_register')->from('bookings_lessons')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->where($where_day)->order_by('bookings_lessons.startTime asc')->get();
				if ($res_lessons->num_rows() > 0) {
					foreach ($res_lessons->result() as $lesson) {
						if ($lesson->show_label_register == 1) {
							$lesson_type = $lesson->type;
						} else {
							$lesson_type = substr($lesson->startTime, 0, 5) . '-&#8203;' . substr($lesson->endTime, 0, 5);
						}
						$lesson_ids[$lesson->day][$lesson->lessonID] = $lesson_type;
					}
				}
				$days[] = $row->day;
			}
		}


		// if booking, sort sessions ids by date
		if ($booking_info->type == 'booking') {
			$lesson_ids_dates = array();
			$date = $block_info->startDate;
			while (strtotime($date) <= strtotime($block_info->endDate)) {
				$day = strtolower(date("l", strtotime($date)));
				if (array_key_exists($day, $lesson_ids)) {
					foreach ($lesson_ids[$day] as $lesson_id => $lesson_type) {
						$lesson_ids_dates[$date][$lesson_id] = $lesson_type;
					}
				}
				$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
			}
			$lesson_ids = $lesson_ids_dates;
		}

		// get booked sessions
		$where = array(
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('bookings_cart_sessions.sessionID, bookings_cart_sessions.attended, bookings_cart_sessions.signout, bookings_cart_sessions.signout_time, bookings_cart_sessions.attend_time, bookings_cart_sessions.bikeability_level, bookings_cart_sessions.shapeup_weight, bookings_cart.cartID, bookings_cart_sessions.contactID, bookings_cart_sessions.childID, bookings_cart_sessions.lessonID, bookings_cart_sessions.date, bookings_lessons.day')
		->from('bookings_cart_sessions')
		->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
		->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
		->where($where)
		->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
					$field = 'contactID';
				} else {
					if ($booking_info->register_type=="adults_children") {
						$field = !empty($row->childID) ? "childID" : "contactID";
					}
					else {
						$field = 'childID';
					}
				}
				if ($booking_info->type == 'booking') {
					$date_field = 'date';
				} else {
					$date_field = 'day';
				}
				$booked_lessons[$row->cartID][$row->$field][$row->lessonID][$row->$date_field] = array(
					'sessionID' => $row->sessionID,
					'attended' => $row->attended,
					'signout' => $row->signout,
					'signout_time' => $row->signout_time,
					'attend_time' => $row->attend_time,
					'bikeability_level' => $row->bikeability_level,
					'shapeup_weight' => $row->shapeup_weight
				);
			}
		}

		// look up cancellations
		$cancellations = array();

		$where = array(
			'bookingID' => $bookingID,
			'type' => 'cancellation',
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('bookings_lessons_exceptions')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$cancellations[$row->lessonID][$row->date] = TRUE;
			}
		}

		// Get Participants Profile Fields Display settings_library-
		$where = array("accountID" => $this->auth->user->accountID);
		$where_section = " (section = 'participant' OR section = 'account_holder')";
		$participant_profile_display = array();$ac_profile_display = array();
		$res = $this->db->from('accounts_fields')->where($where)->where($where_section)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if($row->section == "account_holder"){
					$ac_profile_display[$row->field] = $row->show;
				}else {
					$participant_profile_display[$row->field] = $row->show;
				}
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
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'search_base' => $search_base,
			'items' => $items,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'lessonID' => $lessonID,
			'familyID' => $familyID,
			'children' => $res_children,
			'days' => $days,
			'tabs' => $tabs,
			'lesson_ids' => $lesson_ids,
			'type' => $booking_info->type,
			'print_view' => $print_view,
			'booked_lessons' => $booked_lessons,
			'cancellations' => $cancellations,
			'lesson_days' => $lesson_days,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'dateArray' => $dateArray,
			'participant_profile_display' => $participant_profile_display,
			'ac_profile_display' => $ac_profile_display,
			'bikeability_levels' => $this->bikeability_levels,
			'bikeability_levels_overall' => $this->bikeability_levels_overall
		);

		// load view
		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');

			$view = 'bookings/participants-export';
		} else if ($lessonID == NULL) {
			$view = 'bookings/participants-overview';
		} else {
			$view = 'bookings/participants';
		}
		if ($print_view === 2 || $export === TRUE|| $print_view === 3) {
			$this->load->view($view, $data);
		} else {
			$this->crm_view($view, $data);
		}
	}

	/**
	 * print view
	 * @param  int $blockID
	 * @param  int $lessonID
	 * @return mixed
	 */
	public function print_view($blockID = NULL, $lessonID = NULL) {
		return $this->index($blockID, $lessonID, 2);
	}

	public function viewdetail($blockID = NULL, $lessonID = NULL, $familyID = NULL){
		return $this->index($blockID, $lessonID, 3,'', $familyID);
	}

	public function viewdetailoverview($blockID = NULL, $familyID = NULL){
		return $this->index($blockID, NULL, 3,'', $familyID);
	}

	/**
	 * export participants to CSV
	 * @param  int $blockID
	 * @return mixed
	 */
	public function export($blockID = NULL) {
		return $this->index($blockID, NULL, FALSE, TRUE);
	}

	/**
	 * show names only participant recording
	 * @return void
	 */
	public function names($blockID = NULL, $export = FALSE) {

		if ($blockID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$block_info = $row;
			$this->session->set_userdata('last_blockID', $blockID);
			$bookingID = $block_info->bookingID;
		}

		// look up
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings')->where($where)->where_in('register_type', array('names', 'bikeability', 'shapeup'))->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// deny from coaches + full time coach
		if ($this->check_permission($bookingID) !== TRUE) {
			show_404();
		}

		// set defaults
		$icon = 'user';
		$tab = 'blocks';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$page_base = 'bookings/participants/' . $blockID;
		$return_to = $page_base;
		$section = 'bookings';
		$title = 'Participants';
		$tab = 'blocks';
		$buttons = '<a class="btn btn-primary" href="' . site_url('bookings/participants/import/' . $blockID) . '"><i class="far fa-upload"></i> Import</a> <a class="btn btn-primary" href="' . site_url('bookings/participants/export/' . $blockID) . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// load libraries
		$this->load->library('form_validation');

		// look up cancellations
		$cancellations = array();
		$where = array(
			'bookingID' => $bookingID,
			'type' => 'cancellation',
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_lessons_exceptions')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$cancellations[$row->lessonID][$row->date] = TRUE;
			}
		}
		// look up sessions with start or end date
		$partial_lessons = array();
		$where = array(
			'bookingID' => $bookingID
		);
		$res = $this->db->from('bookings_lessons')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!empty($row->startDate) || !empty($row->endDate)) {
					$partial_lessons[$row->lessonID] = array(
						'startDate' => $row->startDate,
						'endDate' => $row->endDate
					);
				}
			}
		}

		// get sessions by days
		$lessons = array();
		$where = array(
			'bookings_lessons.bookingID' => $bookingID,
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('bookings_lessons.*, lesson_types.name as type, lesson_types.show_label_register')->from('bookings_lessons')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->where($where)->order_by('bookings_lessons.startTime asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($row->show_label_register == 1) {
					$lesson_type = $row->type;
				} else {
					$lesson_type = substr($row->startTime, 0, 5);
				}
				$lessons[$row->day][$row->lessonID] = $lesson_type;
			}
		}

		// sort sessions into dates
		$lesson_data = array();
		$date = $block_info->startDate;
		while (strtotime($date) <= strtotime($block_info->endDate)) {
			$day = strtolower(date("l", strtotime($date)));
			if (array_key_exists($day, $lessons)) {
				foreach ($lessons[$day] as $lesson_id => $lesson_type) {
					// check if cancelled
					if (!isset($cancellations[$lesson_id][$date])) {
						// check for partial lessons
						if ((isset($partial_lessons[$lesson_id]) && strtotime($date) < strtotime($partial_lessons[$lesson_id]['startDate'])) || (isset($partial_lessons[$lesson_id]) && strtotime($date) > strtotime($partial_lessons[$lesson_id]['endDate']))) {
							// outside of partial session range
						} else {
							$lesson_data[$date][$lesson_id] = $lesson_type;
						}
					}
				}
			}
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}

		// get monitoring fields
		$monitoring_fields = array();
		for ($i=1; $i <= 20; $i++) {
			$field = 'monitoring' . $i;
			if (!empty($booking_info->$field)) {
				$monitoring_fields[$i] = $booking_info->$field;
			}
		}

		// if export
		if ($export === TRUE) {
			// shape up vars
			$target = 0.05;
			$lbs = 2.20462;

			$csv_data = array();

			// header 1
			$row_data = array(
				'Name'
			);
			if (count($monitoring_fields) > 0) {
				foreach ($monitoring_fields as $key => $label) {
					$row_data[] = $label;
				}
			}
			$date = $block_info->startDate;
			while (strtotime($date) <= strtotime($block_info->endDate)) {
				if (array_key_exists($date, $lesson_data)) {
					for ($i=0; $i < count($lesson_data[$date]); $i++) {
						$row_data[] = mysql_to_uk_date($date);
						if (in_array($booking_info->register_type, array('shapeup', 'bikeability'))) {
							$row_data[] = NULL;
						}
					}
				}
				$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
			}
			if ($booking_info->register_type == 'bikeability') {
				$row_data[] = 'Overall Level';
			}
			if ($booking_info->register_type == 'shapeup') {
				$row_data[] = '5% Weight Loss';
				$row_data[] = NULL;
				$row_data[] = 'Target Weight';
				$row_data[] = NULL;
				$row_data[] = 'Current Weight Loss';
				$row_data[] = NULL;
				$row_data[] = '% Weight Lost';
			}
			$csv_data[] = $row_data;
			// header 2
			$row_data = array(
				NULL
			);
			if (count($monitoring_fields) > 0) {
				foreach ($monitoring_fields as $key => $label) {
					$row_data[] = NULL;
				}
			}
			foreach ($lesson_data as $date => $lessons) {
				foreach ($lessons as $lessonID => $name) {
					$row_data[] = $name;
					if ($booking_info->register_type == 'bikeability') {
						$row_data[] = 'Level';
					}
					if ($booking_info->register_type == 'shapeup') {
						$row_data[] = 'Weight';
					}
				}
			}
			if ($booking_info->register_type == 'bikeability') {
				$row_data[] = NULL;
			}
			if ($booking_info->register_type == 'shapeup') {
				$row_data[] = 'kg';
				$row_data[] = 'lbs';
				$row_data[] = 'kg';
				$row_data[] = 'lbs';
				$row_data[] = 'kg';
				$row_data[] = 'lbs';
				$row_data[] = NULL;
			}
			$csv_data[] = $row_data;

			// get data
			$attendance_data = $this->get_names_attendance($blockID);
			if (count($attendance_data) > 0) {
				foreach ($attendance_data as $participant) {
					// shape up vars
					$target_loss_kg = 0;
					$target_loss_lbs = 0;
					$target_weight_kg = 0;
					$target_weight_lbs = 0;
					$current_loss_kg = 0;
					$current_loss_lbs = 0;
					$percent_lost = 0;
					$first_weight = 0;
					$last_weight = 0;

					$row_data = array(
						$participant['name']
					);
					if (count($monitoring_fields) > 0) {
						foreach ($monitoring_fields as $key => $label) {
							$row_data[] = $participant['monitoring'][$key];
						}
					}
					$i = 0;
					foreach ($lesson_data as $date => $lessons) {
						foreach ($lessons as $lessonID => $name) {
							if (isset($participant['sessions'][$lessonID][$date])) {
								$row_data[] = 'Yes';
							} else {
								$row_data[] = 'No';
							}
							if ($booking_info->register_type == 'bikeability') {
								if (isset($participant['bikeability_levels'][$lessonID][$date])) {
									$row_data[] = $participant['bikeability_levels'][$lessonID][$date];
								} else {
									$row_data[] = NULL;
								}
							} else if ($booking_info->register_type == 'shapeup') {
								if (isset($participant['shapeup_weights'][$lessonID][$date])) {
									$weight = $participant['shapeup_weights'][$lessonID][$date];
									$row_data[] = $weight;
									if ($i == 0) {
					                    $first_weight = $weight;
					                } else if ($weight > 0){
					                    $last_weight = $weight;
					                }
								} else {
									$row_data[] = NULL;
								}
							}
							$i++;
						}
					}
					if ($booking_info->register_type == 'bikeability') {
						$row_data[] = $participant['bikeability_level'];
					}
					if ($booking_info->register_type == 'shapeup') {
						if ($first_weight > 0) {
							$target_loss_kg = $first_weight*$target;
							$target_loss_lbs = $target_loss_kg*$lbs;
							$target_weight_kg = $first_weight*(1-$target);
							$target_weight_lbs = $target_weight_kg*$lbs;
							if ($last_weight > 0) {
				                $current_loss_kg = $last_weight-$first_weight;
				                $current_loss_lbs = $current_loss_kg*$lbs;
				                $percent_lost = ($current_loss_kg/$first_weight)*100;
							}
						}
						$row_data[] = round($target_loss_kg, 1);
			            $row_data[] = round($target_loss_lbs, 1);
			            $row_data[] = round($target_weight_kg, 1);
			            $row_data[] = round($target_weight_lbs, 1);
			            $row_data[] = round($current_loss_kg, 1);
			            $row_data[] = round($current_loss_lbs, 1);
						$row_data[] = round($percent_lost, 1);
					}
					$csv_data[] = $row_data;
				}
			}
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=attendance-export.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			$file = fopen('php://output', 'w');
			foreach ($csv_data as $row) {
				fputcsv($file, $row);
			}
			exit();
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('save_data', 'Attendance');

			$this->form_validation->run();

			$save_data = $this->input->post('save_data');

			if (!is_array($save_data)) {
				$save_data = array();
			}

			// remove all existng names from block
			$where = array(
				'bookingID' => $bookingID,
				'blockID' => $blockID,
				'accountID' => $this->auth->user->accountID
			);

			$res = $this->db->delete('bookings_attendance_names_sessions', $where);
			$res = $this->db->delete('bookings_attendance_names', $where);

			// save
			foreach ($save_data as $row) {

				// skip if no name var
				if (!isset($row['name'])) {
					continue;
				}

				// monitoring fields
				for ($i=1; $i <= 20; $i++) {
					$field = 'monitoring' . $i;
					$$field = NULL;
					if (isset($row['monitoring'][$i]) && !empty($row['monitoring'][$i])) {
						$$field = trim($row['monitoring'][$i]);
					}
				}

				// bikeability level
				$bikeability_level = NULL;
				if ($booking_info->register_type == 'bikeability' && array_key_exists($row['bikeability_level'], $this->bikeability_levels_overall)) {
					$bikeability_level = $row['bikeability_level'];
				}

				// add participants
				$data = array(
					'bookingID' => $bookingID,
					'blockID' => $blockID,
					'name' => trim($row['name']),
					'bikeability_level' => $bikeability_level,
					'byID' => $this->auth->user->staffID,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);
				for ($i=1; $i <= 20; $i++) {
					$field = 'monitoring' . $i;
					$data[$field] = $$field;
				}
				$this->db->insert('bookings_attendance_names', $data);

				$participantID = $this->db->insert_id();

				// insert sessions
				if (isset($row['sessions']) && is_array($row['sessions'])) {
					foreach ($row['sessions'] as $lessonID => $dates) {
						if (!is_array($dates)) {
							continue;
						}
						foreach ($dates as $date => $val) {
							if (!check_mysql_date($date) || !isset($lesson_data[$date][$lessonID])) {
								continue;
							}

							// bikeability level
							$bikeability_level = NULL;
							if (isset($row['bikeability_levels'][$lessonID][$date]) && array_key_exists($row['bikeability_levels'][$lessonID][$date], $this->bikeability_levels)) {
								$bikeability_level = $row['bikeability_levels'][$lessonID][$date];
							}

							// shapeup weight
							$shapeup_weight = NULL;
							if (isset($row['shapeup_weights'][$lessonID][$date]) && !empty($row['shapeup_weights'][$lessonID][$date])) {
								$shapeup_weight = $row['shapeup_weights'][$lessonID][$date];
							}

							// save new
							$data = array(
								'participantID' => $participantID,
								'bookingID' => $bookingID,
								'blockID' => $blockID,
								'lessonID' => $lessonID,
								'byID' => $this->auth->user->staffID,
								'date' => $date,
								'bikeability_level' => $bikeability_level,
								'shapeup_weight' => $shapeup_weight,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);

							$this->db->insert('bookings_attendance_names_sessions', $data);
						}
					}
				}
			}

			// calc targets
			$this->crm_library->calc_targets($blockID);

			// tell user
			$this->session->set_flashdata('success', $this->settings_library->get_label('participants') . ' updated successfully.');
			echo 'OK';
			exit();

		} else {
			$attendance_data = $this->get_names_attendance($blockID);
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
			'page_base' => $page_base,
			'lesson_data' => $lesson_data,
			'monitoring_fields' => $monitoring_fields,
			'attendance_data' => $attendance_data,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'type' => $booking_info->type,
			'register_type' => $booking_info->register_type,
			'tab' => $tab,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'bikeability_levels' => $this->bikeability_levels,
			'bikeability_levels_overall' => $this->bikeability_levels_overall,
		);

		// load view
		$this->crm_view('bookings/participants-names', $data);
	}


	/**
	 * show numbers only participant recording
	 * @return void
	 */
	public function numbers($blockID = NULL) {

		if ($blockID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$block_info = $row;
			$this->session->set_userdata('last_blockID', $blockID);
			$bookingID = $block_info->bookingID;
		}

		// look up
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID,
			'register_type' => 'numbers'
		);
		$res = $this->db->from('bookings')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// deny from coaches + full time coach
		if ($this->check_permission($bookingID) !== TRUE) {
			show_404();
		}

		// set defaults
		$icon = 'user';
		$tab = 'blocks';
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
		$breadcrumb_levels['bookings/blocks/edit/' . $blockID] = $block_info->name;
		$page_base = 'bookings/participants/' . $blockID;
		$return_to = $page_base;
		$section = 'bookings';
		$title = 'Participants';
		$tab = 'blocks';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// load libraries
		$this->load->library('form_validation');

		// copy xls register template if doesn't exist
		if (empty($block_info->numbers_path)) {
			$xls_path = APPPATH . '../public/documents/register.xlsx';
			if (file_exists($xls_path)) {
				$upload_dir = UPLOADPATH;

				// generate new path
				$new_path = random_string('alnum', 32);

				if (copy($xls_path, $upload_dir . $new_path)) {
					$data = array(
						'numbers_path' => $new_path
					);
					$where = array(
						'blockID' => $blockID,
						'accountID' => $this->auth->user->accountID
					);
					$this->db->update('bookings_blocks', $data, $where, 1);
					$block_info->numbers_path = $new_path;
				}
			}
		}

		if (!empty($block_info->numbers_path)) {
			$buttons = '<a class="btn btn-success" href="' . site_url('attachment/edit/numbers/' . $block_info->numbers_path . '/' . $this->auth->user->accountID) . '" target="_blank"><i class="far fa-pencil"></i> Edit Register</a> <a class="btn btn-primary" href="' . site_url('attachment/numbers/' . $block_info->numbers_path) . '"><i class="far fa-save"></i> Download Register</a>';
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('attendance', 'Attendance');

			$this->form_validation->run();

			$attendance = $this->input->post('attendance');

			if (!is_array($attendance)) {
				$attendance = array();
			}

			// remove all existng sessions for block
			$where = array(
				'blockID' => $blockID,
				'accountID' => $this->auth->user->accountID
			);

			$res = $this->db->delete('bookings_attendance_numbers', $where);

			// save
			foreach ($attendance as $lessonID => $dates) {

				foreach ($dates as $date => $attended) {

					if (check_mysql_date($date) === TRUE) {
						// save new
						$data = array(
							'bookingID' => $bookingID,
							'blockID' => $blockID,
							'lessonID' => $lessonID,
							'date' => $date,
							'attended' => $attended,
							'byID' => $this->auth->user->staffID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);

						$this->db->insert('bookings_attendance_numbers', $data);
					}

				}

			}

			// calc targets
			$this->crm_library->calc_targets($blockID);

			// tell user
			$this->session->set_flashdata('success', $this->settings_library->get_label('participant') . ' numbers updated successfully.');
			redirect($return_to);

		} else {
			$attendance = array();

			// get attendance
			$where = array(
				'blockID' => $blockID,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('bookings_attendance_numbers')->where($where)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$attendance[$row->lessonID][$row->date] = $row->attended;
				}
			}
		}

		// get sessions by day
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);
		$sessions = $this->db->select('bookings_lessons.*, GROUP_CONCAT(lessonID) as lessonIDs')->from('bookings_lessons')->where($where)->group_by('day, startTime, endTime, typeID, activityID')->order_by('startTime asc, endTime asc')->get();

		if ($sessions->num_rows() > 0) {
			foreach ($sessions->result() as $row) {
				$row->lessonIDs = explode(',', $row->lessonIDs);
				if (count($row->lessonIDs) > 0) {
					foreach ($row->lessonIDs as $lessonID) {
						$booking_info->days[$row->day][] = $lessonID;
					}
				}
			}
		}

		$where = array(
			'bookings_lessons.blockID' => $blockID,
			'bookings_lessons.accountID' => $this->auth->user->accountID
		);
		$sessions = $this->db->select('bookings_lessons.*, GROUP_CONCAT(lessonID) as lessonIDs, activities.name AS activity, lesson_types.name AS type')->from('bookings_lessons')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->where($where)->group_by('startTime, endTime, typeID, type_other, activityID, activity_other')->order_by('startTime asc, endTime asc')->get();

		// look up cancellations
		$cancellations = array();

		$where = array(
			'bookingID' => $bookingID,
			'type' => 'cancellation',
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('bookings_lessons_exceptions')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$cancellations[$row->lessonID][$row->date] = TRUE;
			}
		}

		// look up sessions with start or end date
		$partial_lessons = array();

		$where = array(
			'bookingID' => $bookingID
		);

		$res = $this->db->from('bookings_lessons')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!empty($row->startDate) || !empty($row->endDate)) {
					$partial_lessons[$row->lessonID] = array(
						'startDate' => $row->startDate,
						'endDate' => $row->endDate
					);
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
			'page_base' => $page_base,
			'sessions' => $sessions,
			'attendance' => $attendance,
			'cancellations' => $cancellations,
			'partial_lessons' => $partial_lessons,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'blockID' => $blockID,
			'block_info' => $block_info,
			'lessonID' => $lessonID,
			'type' => $booking_info->type,
			'tab' => $tab,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/participants-numbers', $data);
	}

	public function update_monitoring_field($cartID, $bookingID, $accountID, $type, $user, $monitorField, $message = "") {
		if (empty($cartID) || empty($bookingID) || empty($accountID) || empty($monitorField) || $monitorField<1 || $monitorField>20 || !(trim($type)=="contact" || trim($type)=="child") || empty($user)) {
			if($monitorField != "notes")
				show_404();
		}

		$message = trim(urldecode($message));
		$where = array(
			'bookingID' => $bookingID,
			'cartID' => $cartID,
			(strtolower(trim($type)).'ID') => $user
		);

		$res = $this->db->from('bookings_cart_monitoring')->where($where)->limit(1)->get();
		if ($res->num_rows()>0) {
			if($monitorField == "notes"){
				$res = $this->db->update('bookings_cart_monitoring', array('notes' => $message, 'modified' => mdate('%Y-%m-%d %H:%i:%s')), $where);
			}else{
				$res = $this->db->update('bookings_cart_monitoring', array('monitoring'.$monitorField => $message, 'modified' => mdate('%Y-%m-%d %H:%i:%s')), $where);
			}
			if ($this->db->affected_rows() == 1) {
				echo "OK";
				return TRUE;
			}
		}
		else {
			$data = array(
				'accountID' => $accountID,
				'cartID' => $cartID,
				'bookingID' => $bookingID,
				(strtolower(trim($type)).'ID') => $user,
			);
			for($i=1;$i<=20;$i++) {
				if ($monitorField==$i) {
					$data['monitoring'.$monitorField] = $message;
				}
				else {
					$data['monitoring'.$i] = "";
				}
			}
			$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
			$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

			if($monitorField == "notes")
				$data["notes"] = $message;

			$insert = $this->db->insert('bookings_cart_monitoring', $data);
			if ($this->db->affected_rows() == 1) {
				echo "OK";
				return TRUE;
			}
		}

		echo 'NOTOK';
		return FALSE;
	}

	/**
	 * attend a session
	 * @param  int $sessionID
	 * @return mixed
	 */
	public function attend($sessionID = NULL) {

		// check params
		if (empty($sessionID)) {
			show_404();
		}

		$where = array(
			'sessionID' => $sessionID,
			'bookings_cart_sessions.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_cart_sessions.*, bookings_lessons.blockID')->from('bookings_cart_sessions')->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$session_info = $row;

			// check permission
			if ($this->check_permission($session_info->bookingID) !== TRUE) {
				show_404();
			}

			// all ok, update
			$data= array(
				'attended' => 1,
				'attend_time' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('bookings_cart_sessions', $data, $where);

			if ($this->db->affected_rows() == 1) {
				echo 'OK';
				return TRUE;
			}
		}

		echo 'NOTOK';
		return FALSE;
	}

	/**
	 * signout a session
	 * @param  int $sessionID
	 * @return mixed
	 */
	public function signout($sessionID = NULL) {

		// check params
		if (empty($sessionID)) {
			show_404();
		}

		$where = array(
			'sessionID' => $sessionID,
			'bookings_cart_sessions.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_cart_sessions.*, bookings_lessons.blockID')->from('bookings_cart_sessions')->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$session_info = $row;

			// check permission
			if ($this->check_permission($session_info->bookingID) !== TRUE) {
				show_404();
			}

			// all ok, update
			$data= array(
				'signout' => 1,
				'signout_time' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('bookings_cart_sessions', $data, $where);

			if ($this->db->affected_rows() == 1) {
				echo 'OK';
				return TRUE;
			}
		}

		echo 'NOTOK';
		return FALSE;
	}

	/**
	 * unattend a session
	 * @param  int $sessionID
	 * @return mixed
	 */
	public function unattend($sessionID = NULL, $date = NULL) {

		// check params
		if (empty($sessionID)) {
			show_404();
		}

		$where = array(
			'sessionID' => $sessionID,
			'bookings_cart_sessions.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_cart_sessions.*, bookings_lessons.blockID')->from('bookings_cart_sessions')->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$session_info = $row;

			// check permission
			if ($this->check_permission($session_info->bookingID) !== TRUE) {
				show_404();
			}

			// all ok, update
			$data= array(
				'attended' => 0,
				'attend_time' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('bookings_cart_sessions', $data, $where);

			if ($this->db->affected_rows() == 1) {
				echo 'OK';
				return TRUE;
			}
		}

		echo 'NOTOK';
		return FALSE;
	}

	/**
	 * Not signout a session
	 * @param  int $sessionID
	 * @return mixed
	 */
	public function notsignout($sessionID = NULL, $date = NULL) {

		// check params
		if (empty($sessionID)) {
			show_404();
		}

		$where = array(
			'sessionID' => $sessionID,
			'bookings_cart_sessions.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_cart_sessions.*, bookings_lessons.blockID')->from('bookings_cart_sessions')->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$session_info = $row;

			// check permission
			if ($this->check_permission($session_info->bookingID) !== TRUE) {
				show_404();
			}

			// all ok, update
			$data= array(
				'signout' => 0,
				'signout_time' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('bookings_cart_sessions', $data, $where);

			if ($this->db->affected_rows() == 1) {
				echo 'OK';
				return TRUE;
			}
		}

		echo 'NOTOK';
		return FALSE;
	}

	/**
	 * import data for names only
	 * @return void
	 */
	public function import($blockID = NULL) {

		if ($blockID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$block_info = $row;
			$this->session->set_userdata('last_blockID', $blockID);
			$bookingID = $block_info->bookingID;
		}

		// look up
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings')->where($where)->where_in('register_type', array('names', 'bikeability', 'shapeup'))->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Import Names';
		$submit_to = 'bookings/participants/import/' . $blockID;
		$return_to = 'bookings/participants/' . $blockID;
		$icon = 'user';
		$current_page = $booking_info->type . 's';
		if ($booking_info->project == 1) {
			$current_page = 'projects';
		}
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

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
				$imported = 0;

				if ($upload_data === NULL) {
					$errors[] = 'A valid file is required';
				} else {
					try {
						$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($upload_data['full_path']); // identify the file
						$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType); // creating the reader
						$objReader->setReadDataOnly(true);
						$objPHPExcel = $objReader->load($upload_data['full_path']); // loading the file

						// get worksheet dimensions
						$sheet = $objPHPExcel->getSheet(0); // selecting sheet 0
						$highestRow = $sheet->getHighestRow(); // getting number of rows
						$highestColumn = $sheet->getHighestColumn(); // getting number of columns

						// loop through each row of the worksheet in turn, skip first
						for ($row = 2; $row <= $highestRow; $row++) {

							// read a row of data into an array
							$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
							$data = $rowData[0];

							// require at least first cell (name)
							if (!isset($data[0]) || empty($data[0])) {
								continue;
							}

							// add participants
							$db_data = array(
								'bookingID' => $bookingID,
								'blockID' => $blockID,
								'name' => trim($data[0]),
								'byID' => $this->auth->user->staffID,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);

							// get monitoring
							for ($i=1; $i <= 20; $i++) {
								$field = 'monitoring' . $i;
								$$field = NULL;
								if (isset($data[$i]) || !empty($data[$i])) {
									$$field = $data[$i];
								}
								$db_data[$field] = $$field;
							}

							$this->db->insert('bookings_attendance_names', $db_data);
							if ($this->db->affected_rows() > 0) {
								$imported++;
							}
						}
						if ($imported == 0) {
							$errors[] = 'No names to import';
						} else {
							$this->session->set_flashdata('success', $imported . ' name(s) imported');
							redirect($return_to);
							return TRUE;
						}
					} catch (Exception $e) {
   						$errors[] = 'File could not be read' . $e->getMessage();
   					}
				}

				// delete tmp file
				@unlink($upload_data['full_path']);

			} else {
				$errors[] = trim(strip_tags($this->upload->display_errors()));
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
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'bookingID' => $bookingID
		);

		// load view
		$this->crm_view('bookings/participants-import', $data);
	}

	/**
	 * generate import sample
	 * @return void
	 */
	public function importsample($bookingID = NULL) {
		if ($bookingID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', 'Name');
		$sheet->setCellValue('A2', 'David Johnson');

		// get monitoring
		$letter = 'B';
		for ($i=1; $i <= 10; $i++) {
			$field = 'monitoring' . $i;
			if (isset($booking_info->$field) || !empty($booking_info->$field)) {
				$sheet->setCellValue($letter . '1', $booking_info->$field);
			}
			$letter++;
		}

		// style header
		$styleArray = [
		    'font' => [
		        'bold' => true,
		    ]
		];
		$spreadsheet->getActiveSheet()->getStyle('A1:Z1')->applyFromArray($styleArray);

		// save
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="names-import.xlsx"');
		$writer = new Xlsx($spreadsheet);
		$writer->save("php://output");
	}

	/**
	 * set bikeability level for session
	 * @param  int $sessionID
	 * @return mixed
	 */
	public function bikeability($sessionID = NULL) {

		// check params
		if (empty($sessionID)) {
			show_404();
		}

		$level = $this->input->post('level');

		// check valid level
		if (!array_key_exists($level, $this->bikeability_levels)) {
			show_404();
		}

		$where = array(
			'sessionID' => $sessionID,
			'bookings_cart_sessions.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_cart_sessions.*, bookings_lessons.blockID')->from('bookings_cart_sessions')->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$session_info = $row;

			// check permission
			if ($this->check_permission($session_info->bookingID) !== TRUE) {
				show_404();
			}

			// all ok, update
			$data= array(
				'bikeability_level' => $level,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('bookings_cart_sessions', $data, $where);

			if ($this->db->affected_rows() == 1) {
				echo 'OK';
				return TRUE;
			}
		}

		echo 'NOTOK';
		return FALSE;
	}

	/**
	 * set overall bikeability level for session
	 * @param  string $type
	 * @param  int $cartID
	 * @param  int $itemID
	 * @return mixed
	 */
	public function bikeability_overall($type = NULL, $cartID = NULL, $itemID = NULL) {

		// check params
		if (!in_array($type, array('child', 'contact')) || empty($cartID) || empty($itemID)) {
			show_404();
		}

		$field = $type . 'ID';
		$level = trim($this->input->post('level'));

		// check valid level
		if (!array_key_exists($level, $this->bikeability_levels_overall) && !empty($level)) {
			show_404();
		}

		$where = array(
			'cartID' => $cartID,
			$field => $itemID,
			'accountID' => $this->auth->user->accountID
		);

		// check record exists
		$query = $this->db->from('bookings_cart_sessions')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$record_info = $row;

			// check permission
			if ($this->check_permission($record_info->bookingID) !== TRUE) {
				show_404();
			}

			// delete existing level
			$where = array(
				'cartID' => $cartID,
				$field => $itemID,
				'accountID' => $this->auth->user->accountID
			);
			$this->db->delete('bookings_cart_bikeability', $where, 1);

			// add level
			$data= array(
				'accountID' => $this->auth->user->accountID,
				'byID' => $this->auth->user->staffID,
				'bookingID' => $record_info->bookingID,
				'cartID' => $cartID,
				$field => $itemID,
 				'bikeability_level' => $level,
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->insert('bookings_cart_bikeability', $data);

			if ($this->db->affected_rows() == 1) {
				echo 'OK';
				return TRUE;
			}
		}

		echo 'NOTOK';
		return FALSE;
	}

	/**
	 * set shapeup weight for session
	 * @param  int $sessionID
	 * @return mixed
	 */
	public function shapeup_weight($sessionID = NULL) {

		// check params
		if (empty($sessionID)) {
			show_404();
		}

		$weight = $this->input->post('weight');

		$where = array(
			'sessionID' => $sessionID,
			'bookings_cart_sessions.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings_cart_sessions.*, bookings_lessons.blockID')->from('bookings_cart_sessions')->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$session_info = $row;

			// check permission
			if ($this->check_permission($session_info->bookingID) !== TRUE) {
				show_404();
			}

			// all ok, update
			$data= array(
				'shapeup_weight' => $weight,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('bookings_cart_sessions', $data, $where);

			if ($this->db->affected_rows() == 1) {
				echo 'OK';
				return TRUE;
			}
		}

		echo 'NOTOK';
		return FALSE;
	}

	/**
	 * process booking dropdowns from family pages
	 * @param  integer $contactID
	 * @param  integer $childID
	 * @return mixed
	 */
	public function family($contactID = NULL, $childID = NULL) {

		// check params
		if (empty($contactID) || $this->input->post('bookingID') == '') {
			show_404();
		}

		$redirect_to = 'bookings/participants/' . $this->input->post('bookingID') . '/new/' . $contactID;

		if (!empty($childID)) {
			$redirect_to .= '/' . $childID;
		}

		redirect($redirect_to);

	}

	/**
	 * check if user has permission to view register
	 * @param  integer $bookingID
	 * @return boolean
	 */
	private function check_permission($bookingID = NULL) {
		// check params
		if (empty($bookingID)) {
			return FALSE;
		}

		// check permission if coaching level
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {

			// check if staff usually in booking
			$where = array(
				'bookingID' => $bookingID,
				'staffID' => $this->auth->user->staffID,
				'accountID' => $this->auth->user->accountID,
				'type !=' => 'participant'
			);

			$res = $this->db->from('bookings_lessons_staff')->where($where)->limit(1)->get();

			if ($res->num_rows() == 0) {

				// no, check not added by exception
				$where = array(
					'bookingID' => $bookingID,
					'type' => 'staffchange',
					'staffID' => $this->auth->user->staffID,
					'accountID' => $this->auth->user->accountID
				);

				$res = $this->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

				if ($res->num_rows() == 0) {
					return FALSE;
				}

			}
		}

		return TRUE;
	}

	/**
	 * check to see if family already booked or they have any outstanding payments
	 * @param  int $familyID
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function check_family() {

		// get vars
		$familyID = $this->input->post('familyID');
		$bookingID = $this->input->post('bookingID');

		// check params
		if (empty($familyID) || empty($bookingID)) {
			show_404();
		}

		$message = NULL;

		$where = array(
			'bookings_cart.familyID' => $familyID,
			'bookings_cart.bookingID' => $bookingID,
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_cart.accountID' => $this->auth->user->accountID
		);

		// check if already booked
		$res = $this->db->select('bookings_cart.*, bookings.register_type')->from('bookings_cart')->join('bookings', 'bookings_cart.bookingID = bookings.bookingID', 'inner')->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where)->group_by('bookings_cart.cartID')->order_by('bookings_cart.added desc')->limit(1)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (in_array($row->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
					$message = 'This individual is already booked on this event - ' . anchor('bookings/participants/edit/' . $row->cartID, 'Edit booking.');
				} else {
					$message = 'This family is already booked on this event - ' . anchor('bookings/participants/edit/' . $row->cartID, 'Edit booking to add more children.');
				}
			}
		} else {

			// check for outstanding payments
			$where = array(
				'familyID' => $familyID,
				'balance >' => 0,
				'accountID' => $this->auth->user->accountID
			);

			$res = $this->db->from('bookings_cart')->where($where)->limit(1)->get();

			if ($res->num_rows() > 0) {
				$message = 'This family has outstanding payments - ' . anchor('participants/bookings/' . $familyID, '
				View booking history.', 'target="_blank"');
			}

		}

		if (!empty($message)) {
			?><div class="alert alert-info">
				<p><i class="far fa-info-circle"></i> <?php
					echo $message;
				?></p>
			</div><?php
		} else {
			echo 'OK';
		}

	}

	/**
	 * check if either this field filled in if value of another field is other
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function required_if_other($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// check
		if (strtolower($value2) == 'other' && empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if fields required because making payment
	 * @return boolean
	 */
	public function required_if_payment($var) {

		// trim
		$amount = floatval($this->input->post('amount'));
		$method = trim($var);

		// check
		if ($amount > 0 && empty($method)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if amount want to pay is more than total (or balance if set)
	 * @param float $amount
	 * @return boolean
	 */
	public function more_than_total($amount) {

		// if not paying, all ok
		if ($amount == 0) {
			return TRUE;
		}

		// convert to float
		$amount = floatval($amount);
		if ($this->input->post('balance') > 0) {
			$total = floatval($this->input->post('balance'));
		} else {
			$total = floatval($this->input->post('total'));
		}

		// check
		if ($amount > $total) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if either this field filled in if value of another field is checked
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function required_if_checked($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// check
		if ($value2 == 1 && empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * format postcode and check is correct
	 * @param  string $postcode
	 * @return mixed
	 */
	public function check_postcode($postcode) {

		return $this->crm_library->check_postcode($postcode);

	}

	/**
	 * check if either this or another field is filled in
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function phone_or_mobile($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// if both empty, not valid
		if (empty($value) && empty($value2)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * validation function for checking email is unique, except in specified user record
	 * @param  string $email
	 * @param  int $user_id
	 * @return bool
	 */
	public function check_email($email = NULL, $contactID = NULL) {
		// if not email specified, skip
		if (empty($email)) {
			return TRUE;
		}

		// check email not in use with anyone on this account
		$where = array(
			'email' => $email,
			'accountID' => $this->auth->user->accountID
		);

		// exclude current user, if set
		if (!empty($contactID)) {
			$where['contactID !='] = $contactID;
		}

		// check
		$query = $this->db->get_where('family_contacts', $where, 1);

		// check results
		if ($query->num_rows() == 0) {
			// none matching, so ok
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * check mobile number is valid
	 * @param  string $number
	 * @return mixed
	 */
	public function check_mobile($number = NULL) {
		return $this->crm_library->check_mobile($number);
	}

	/**
	 * validation function for check a dob is valid and in past
	 * @param  string $date
	 * @return bool
	 */
	public function check_dob($date) {

		// valid if empty
		if (empty($date)) {
			return TRUE;
		}

		// check valid date
		if (!check_uk_date($date)) {
			return FALSE;
		}

		// check date is in future
		if (strtotime(uk_to_mysql_date($date)) > time()) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * validation function to check if something is required (used to check something in array)
	 * @param  string $val
	 * @return bool
	 */
	public function required($val) {

		if (is_array($val) && count($val) == 0) {
			return FALSE;
		}  else if (empty($val)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if contact is on blacklist
	 * @param integer $contactID
	 * @return bool
	 */
	public function check_blacklist($contactID) {
		$where = array(
			'contactID' => $contactID,
			'blacklisted' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();

		// on black list
		if ($res->num_rows() > 0) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if contact is in allowed postcodes
	 * @param integer $contactID
	 * @return bool
	 */
	public function check_postcode_restriction($contactID, $booking_postcodes) {

		if (empty($contactID)) {
			return TRUE;
		}

		$booking_postcodes = explode(',', $booking_postcodes);
		if (empty($booking_postcodes) || !is_array($booking_postcodes) || count($booking_postcodes) == 0) {
			return TRUE;
		}

		$allowed_postcodes = array();
		foreach ($booking_postcodes as $postcode) {
			// clean up
			$postcode = preg_replace("/[^A-Z0-9]/", '', strtoupper($postcode));
			if (!empty($postcode)) {
				$allowed_postcodes[] = $postcode;
			}
		}

		// if still empty, return true
		if (count($allowed_postcodes) == 0) {
			return TRUE;
		}

		// look up contact
		$where = array(
			'contactID' => $contactID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();

		// not found
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		// clean up
		$contact_postcode = preg_replace("/[^A-Z0-9]/", '', strtoupper($contact_info->postcode));

		// check for match
		foreach ($allowed_postcodes as $postcode) {
			// check for match
			if (substr($contact_postcode, 0, strlen($postcode)) == $postcode) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * get attendance names data for a block
	 * @param integer $blockID
	 * @return array
	 */
	private function get_names_attendance($blockID) {
		$attendance_data = array();

		// get participants
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_attendance_names')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$attendance_data[$row->participantID] = array(
					'name' => $row->name,
					'monitoring' => array(),
					'sessions' => array(),
					'bikeability_level' => $row->bikeability_level,
					'bikeability_levels' => array(),
					'shapeup_weights' => array()
				);
				for ($i=1; $i <= 20; $i++) {
					$field = 'monitoring' . $i;
					$attendance_data[$row->participantID]['monitoring'][$i] = $row->$field;
				}
			}
		}

		// get sessions
		$res = $this->db->from('bookings_attendance_names_sessions')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$attendance_data[$row->participantID]['sessions'][$row->lessonID][$row->date] = 1;
				$attendance_data[$row->participantID]['bikeability_levels'][$row->lessonID][$row->date] = $row->bikeability_level;
				$attendance_data[$row->participantID]['shapeup_weights'][$row->lessonID][$row->date] = $row->shapeup_weight;
			}
		}

		return $attendance_data;
	}

	/**
	 * check at least one session
	 * @return bool
	 */
	public function at_least_one_session() {

		$sessions = $this->input->post('sessions');

		if (is_array($sessions) && count($sessions) > 0) {
			return TRUE;
		}
		return FALSE;
	}

}

/* End of file participants.php */
/* Location: ./application/controllers/bookings/participants.php */
