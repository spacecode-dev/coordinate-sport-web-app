<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Acceptance extends MY_Controller {

	private $allowed_departments = array(
		'directors',
		'management'
	);

	public function __construct() {
		parent::__construct(FALSE, array(), array(), array());

		// load library
		$this->load->model('Offers/OffersModel');
		$this->load->library('offer_accept_library');
	}

	/**
	 * show approvals for logged in user
	 * @param $show_all boolean
	 * @return void
	 */
	public function index($show_all = FALSE, $manual = FALSE) {

		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			if (!$this->auth->has_features(array('offer_accept'))
				&& !$this->auth->has_features(array('offer_accept_manual'))) {
				show_403();
			}
		} else {
			if($manual) {
				if (!$this->auth->has_features('offer_accept_manual')) {
					show_403();
				}
			} else {
				if (!$this->auth->has_features('offer_accept')) {
					show_403();
				}
			}
		}

		// convert var to bool
		if ($show_all === 'true') {
			$show_all = TRUE;
		}

		// only allow certain people to view all
		if ($show_all == TRUE && !in_array($this->auth->user->department, $this->allowed_departments)) {
			$show_all = FALSE;
		}

		// set defaults
		$icon = 'check-square';
		$current_page = 'acceptance';
		$section = 'acceptance';
		$page_base = 'acceptance';
		$title = 'Accept Sessions';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$selected_offers = array();
		$action = NULL;
		$breadcrumb_levels = array();

		if ($manual) {
			$page_base = 'acceptance_manual';
            $current_page = 'acceptance_manual_own';
            $section = 'acceptance_manual';
            $title = 'Accept Sessions';
        }

		// set where
		$where = array(
			'offer_accept.accountID' => $this->auth->user->accountID,
			'offer_accept.staffID' => $this->auth->user->staffID
		);

		// if showing all
		if ($show_all === TRUE) {
			$title = 'All Offers';
			unset($where['offer_accept.staffID']);
			$page_base .= '/all';
			$current_page = 'acceptance_manual_all';
			$breadcrumb_levels['acceptance'] = 'Accept Sessions';
			if ($manual) {
				$title = 'All Offers';
				unset($breadcrumb_levels['acceptance']);
				$breadcrumb_levels['acceptance_manual'] = 'Accept Sessions';
			}
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'staff_id' => NULL,
			'status' => NULL,
			'date_from' => date('d/m/Y', strtotime("-30 days")),
			'date_to' => date('d/m/Y'),
			'search' => NULL,
			'org' => NULL,
			'brand_id' => NULL,
			'type_id' => NULL,
			'activity_id' => NULL,
			'staffing_type' => NULL
		);

		// if search
		if ($this->input->post('search')) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_status', 'Status', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_orgs', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_department', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type_id', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_activity', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staffing_type', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['status'] = set_value('search_status');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['search'] = set_value('search');
			$search_fields['org'] = set_value('search_orgs');
			$search_fields['brand_id'] = set_value('search_department');
			$search_fields['type_id'] = set_value('search_type_id');
			$search_fields['activity_id'] = set_value('search_activity');
			$search_fields['staffing_type'] = set_value('search_staffing_type');


			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-offer_accept'))) {

			foreach ($this->session->userdata('search-offer_accept') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-offer_accept', $search_fields);

			if ($search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("offer_accept") . "`.`staffID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if ($search_fields['status'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("offer_accept") . "`.`status` = " . $this->db->escape($search_fields['status']);
			}

			if ($search_fields['org'] != '') {
				$search_where[] = '(`' . $this->db->dbprefix("orgs") . "`.`orgID` = ". $this->db->escape($search_fields['org']) ." OR `orgs_blocks`.`orgID` = ". $this->db->escape($search_fields['org']) . ')';
			}

			if ($search_fields['brand_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`brandID` = " . $this->db->escape($search_fields['brand_id']);
			}

			if (!empty($search_fields['type_id'])) {
				$where['bookings_lessons.typeID'] = $search_fields['type_id'];
			}

			if (!empty($search_fields['activity_id'])) {
				$where['bookings_lessons.activityID'] = $search_fields['activity_id'];
			}

			if (!empty($search_fields['staffing_type'])) {
				$where['offer_accept.type'] = $search_fields['staffing_type'];
			}
		}

		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
			if ($date_from !== FALSE) {
				$search_where[] = '`' . $this->db->dbprefix("offer_accept") . "`.`added` >= " . $this->db->escape($date_from . ' 00:00:00');
			}
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
			if ($date_to !== FALSE) {
				$search_where[] = '`' . $this->db->dbprefix("offer_accept") . "`.`added` <= " . $this->db->escape($date_to . ' 23:59:59');
			}
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// bulk actions
		if ($this->input->post('bulk') == 1) {
			if (is_array($this->input->post('selected_offers'))) {
				$selected_offers = $this->input->post('selected_offers');
			}
			$action = $this->input->post('action');
			$bulk_successful = 0;
			$bulk_failed = 0;
			if (count($selected_offers) > 0) {
				foreach ($selected_offers as $offerID) {
					switch ($action) {
						case 'accept':
							$res = $this->accept($offerID, TRUE);
							if ($res === TRUE) {
								$bulk_successful++;
							} else {
								$bulk_failed++;
							}
							break;
						case 'decline':
							$res = $this->decline($offerID, TRUE);
							if ($res === TRUE) {
								$bulk_successful++;
							} else {
								$bulk_failed++;
							}
							break;
					}
				}
			}

			// notify in bulk
			if ($bulk_successful > 0) {
				$this->offer_accept_library->bulk_notify($action, $manual);
			}

			if ($bulk_successful > 0 && $bulk_failed == 0) {
				$pl_sq = 's have';
				if ($bulk_successful == 1) {
					$pl_sq = ' has';
				}
				$this->session->set_flashdata('success', $bulk_successful . ' offer' . $pl_sq . ' been processed successfully.');
			} else if ($bulk_successful == 0 && $bulk_failed > 0) {
				$this->session->set_flashdata('error', $bulk_failed . ' offer(s) could not be processed.');
			} else if ($bulk_successful > 0 && $bulk_failed > 0) {
				$pl_sq = 's have been';
				if ($bulk_successful == 1) {
					$pl_sq = ' has been';
				}
				$this->session->set_flashdata('info', $bulk_successful . ' offer' . $pl_sq . ' been processed successfully, however ' .  $bulk_failed . ' offers(s) could not be processed.');
			}
			$redirect_to = 'acceptance';

			if ($this->input->post('manual') && in_array($this->auth->user->department, $this->allowed_departments)) {
				$redirect_to = 'acceptance_manual';
			}

			if ($show_all === TRUE) {
				$redirect_to .= '/all';
			}
			redirect($redirect_to);
			exit();
		}

		if (in_array($this->auth->user->department, $this->allowed_departments)) {
			if ($manual) {
				$where['offer_accept.offer_type != '] = 'auto';
			} else {
				$where['offer_accept.offer_type'] = 'auto';
			}
		}

		// run query
		$res = $this->db->select('*')
			->from('offer_accept')
			->join('bookings_lessons', 'offer_accept.lessonID = bookings_lessons.lessonID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as orgs_blocks', 'bookings_blocks.orgID = orgs_blocks.orgID', 'left')
			->where($where)
			->where($search_where, NULL, FALSE)->get();

		// workout pagination
		$total_items = $res->num_rows();
		$pagination = $this->pagination_library->calc($total_items);

		// run again, but limited
		$res = $this->db->select('offer_accept.*, staff.first, staff.surname, bookings_lessons.startTime,
			bookings_lessons.day, bookings_lessons.location, bookings_lessons.type_other, bookings_lessons.activity_other,
			bookings_lessons.activity_desc, bookings_lessons.group, bookings_lessons.group_other,
			bookings_lessons.class_size, bookings_lessons.endTime, bookings_lessons.startDate as lesson_start,
			bookings_lessons.endDate as lesson_end, bookings_blocks.startDate as block_start,
			bookings_blocks.endDate as block_end, activities.name as activity, lesson_types.name as lesson_type,
			orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town,
			orgs_addresses.county, orgs_addresses.postcode, bookings.name as event_name, combined_with,
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
			->where($search_where, NULL, FALSE)
			->order_by('offer_accept.added desc')
			->group_by('offer_accept.offerID')
			->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		$result_offers = [];
		$booking_names = [];
		foreach ($res->result() as $row) {
			$result_offers[$row->bookingID][] = $row;
			$booking_names[$row->bookingID] = [
				'name' => $row->event_name,
				'project' => $row->project
			];
		}


		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// staff
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		$this->load->library('orgs_library');

		$sessionTypes = $this->settings_library->getSessionTypes($this->auth->user->accountID);
		$orgs = $this->orgs_library->getAllOrgs($this->auth->user->accountID);
		$departments = $this->settings_library->getDepartments($this->auth->user->accountID);
		$activities = $this->settings_library->getActivities($this->auth->user->accountID);

		//process combined offers
		$combinedOffers = [];
		foreach ($result_offers as $booking => $offers) {
			foreach ($offers as $offer) {
				if ($offer->combined_with) {
					$combinedOffers[$booking][md5($offer->staffID . $offer->offer_type
						. $offer->status . $offer->combined_with . $offer->type . $offer->groupID)][] = $offer;
				} else {
					$combinedOffers[$booking][][] = $offer;
				}
			}
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
			'offers_bookings' => $combinedOffers,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'staff_list' => $staff_list,
			'selected_offers' => $selected_offers,
			'action' => $action,
			'show_all' => $show_all,
			'booking_names' => $booking_names,
			'orgs' => $orgs,
			'departments' => $departments,
			'session_types' => $sessionTypes,
			'activities' => $activities,
			'manual' => $manual
		);

		// load view
		$this->crm_view('acceptance/list', $data);
	}

	/**
	 * accept an offer
	 * @param  int $offerID
	 * @param boolean $bulk
	 * @return mixed
	 */
	public function accept($offerID = NULL, $bulk = FALSE) {

		// check params
		if (empty($offerID)) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		$where = array(
			'offerID' => $offerID,
			'status' => 'offered',
			'accountID' => $this->auth->user->accountID,
			'staffID' => $this->auth->user->staffID
		);

		// if in allowed department, can approve any
		if (in_array($this->auth->user->department, $this->allowed_departments)) {
			unset($where['staffID']);
		}

		// run query
		$query = $this->db->from('offer_accept')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$offer_info = $row;

			// all ok, process
			if (!$this->offer_accept_library->accept($offerID, $bulk)) {
				$this->session->set_flashdata('error', 'There was an error accepting the session');
				$redirect_to = 'acceptance';
				if ($offer_info->staffID != $this->auth->user->staffID) {
					$redirect_to .= '/all';
				}

				if (!empty($this->input->get('from'))) {
					$redirect_to = $this->input->get('from');
				}

				redirect($redirect_to);
			}

			if ($bulk == TRUE) {
				return TRUE;
			}

			$this->session->set_flashdata('success', 'Offer has been accepted successfully.');

			$redirect_to = 'acceptance';
			if ($offer_info->staffID != $this->auth->user->staffID) {
				$redirect_to .= '/all';
			}

			if (!empty($this->input->get('from'))) {
				$redirect_to = $this->input->get('from');
			}

			redirect($redirect_to);
		}
	}

	/**
	 * declines an offer
	 * @param  int $offerID
	 * @param boolean $bulk
	 * @return mixed
	 */
	public function decline($offerID = NULL, $bulk = FALSE) {

		// check params
		if (empty($offerID)) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		$where = array(
			'offerID' => $offerID,
			'status' => 'offered',
			'accountID' => $this->auth->user->accountID,
			'staffID' => $this->auth->user->staffID
		);

		// if in allowed department, can approve any
		if (in_array($this->auth->user->department, $this->allowed_departments)) {
			unset($where['staffID']);
		}

		// run query
		$query = $this->db->from('offer_accept')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$offer_info = $row;

			// all ok, process
			if (!$this->offer_accept_library->decline($offerID, $bulk)) {
				$this->session->set_flashdata('error', 'There was an error declining the session');
				$redirect_to = 'acceptance';
				if ($offer_info->staffID != $this->auth->user->staffID) {
					$redirect_to .= '/all';
				}

				if (!empty($this->input->get('from'))) {
					$redirect_to = $this->input->get('from');
				}

				redirect($redirect_to);
			}

			if ($bulk == TRUE) {
				return TRUE;
			}

			$this->session->set_flashdata('success', 'Offer has been declined successfully.');

			$redirect_to = 'acceptance';
			if ($offer_info->staffID != $this->auth->user->staffID) {
				$redirect_to .= '/all';
			}

			if (!empty($this->input->get('from'))) {
				$redirect_to = $this->input->get('from');
			}

			redirect($redirect_to);
		}
	}
}

/* End of file Acceptance.php */
/* Location: ./application/controllers/Acceptance.php */
