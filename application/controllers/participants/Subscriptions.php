<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subscriptions extends MY_Controller {

	private $in_crm = TRUE;
	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants', 'online_booking'));

		// load gocardless library
		$this->load->library('gocardless_library');
		$this->load->library('cart_library');
	}

	/**
	 * show list of subscriptions
	 * @return void
	 */
	public function index($familyID = NULL) {

		if ($familyID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('family')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$family_info = $row;
		}

		// set defaults
		$icon = 'calendar-alt';
		$tab = 'subscriptions';
		$current_page = 'participants';
		$page_base = 'participants/subscriptions/' . $familyID;
		$section = 'participants';
		$title = 'Subscriptions';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account'
 		);


		// set where
		$where = array(
			'subscriptions.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'amount' => NULL,
			'contact_id' => NULL,
			'child_id' => NULL,
			'note' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_amount', 'Amount', 'trim|xss_clean');
			$this->form_validation->set_rules('search_contact_id', 'From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_child_id', 'From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_note', 'Transaction Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['amount'] = set_value('search_amount');
			$search_fields['contact_id'] = set_value('search_contact_id');
			$search_fields['child_id'] = set_value('search_child_id');
			$search_fields['note'] = set_value('search_note');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-family-payments-plans'))) {

			foreach ($this->session->userdata('search-family-payments-plans') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-family-payments-plans', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("participant_subscriptions") . "`.`added` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("participant_subscriptions") . "`.`added` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['amount'] != '') {
				$search_where[] = "`amount` = " . $this->db->escape($search_fields['amount']);
			}

			if ($search_fields['contact_id'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("family_contacts") . "`.`contactID` = " . $this->db->escape($search_fields['contact_id']);
			}

			if ($search_fields['child_id'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("family_children") . "`.`childID` = " . $this->db->escape($search_fields['child_id']);
			}

			if ($search_fields['note'] != '') {
				$search_where[] = "`note` LIKE '%" . $this->db->escape_like_str($search_fields['note']) . "%'";;
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		$res = $this->db->select('subscriptions.*, bookings.name, participant_subscriptions.status, participant_subscriptions.id,
		IFNULL('.$this->db->dbprefix('family_contacts').'.familyID,'.$this->db->dbprefix('family_children').'.familyID) as familyID,
		IFNULL('.$this->db->dbprefix('family_contacts').'.first_name,'.$this->db->dbprefix('family_children').'.first_name) as first_name,
		IFNULL('.$this->db->dbprefix('family_contacts').'.last_name,'.$this->db->dbprefix('family_children').'.last_name) as last_name,
		bookings_cart_subscriptions.cartID,bookings_cart_subscriptions.subID,
		family_children.childID, family_contacts.contactID')
			->from('participant_subscriptions')
			->join('bookings_cart_subscriptions', '(bookings_cart_subscriptions.contactID = participant_subscriptions.contactID OR bookings_cart_subscriptions.childID = `'.$this->db->dbprefix('participant_subscriptions').'`.`childID`) AND bookings_cart_subscriptions.subID = participant_subscriptions.subID', 'inner')
			->join('subscriptions', 'subscriptions.subID = participant_subscriptions.subID', 'left')
			->join('bookings', 'subscriptions.bookingID = bookings.bookingID', 'left')
			->join('family_children', 'participant_subscriptions.childID = family_children.childID', 'left')
			->join('family_contacts', 'participant_subscriptions.contactID = family_contacts.contactID', 'left')
			->where($where)
			->where('('.$this->db->dbprefix('family_children').'.familyID = '.$familyID.' OR  '.$this->db->dbprefix('family_contacts').'.familyID = '.$familyID.')')
			->where($search_where, NULL, FALSE)
			->order_by('added desc')
			->get();


		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->select('subscriptions.*, bookings.name, participant_subscriptions.status, participant_subscriptions.id,
		IFNULL('.$this->db->dbprefix('family_contacts').'.familyID,'.$this->db->dbprefix('family_children').'.familyID) as familyID,
		IFNULL('.$this->db->dbprefix('family_contacts').'.first_name,'.$this->db->dbprefix('family_children').'.first_name) as first_name,
		IFNULL('.$this->db->dbprefix('family_contacts').'.last_name,'.$this->db->dbprefix('family_children').'.last_name) as last_name,
		bookings_cart_subscriptions.cartID,bookings_cart_subscriptions.subID,
		family_children.childID, family_contacts.contactID')
			->from('participant_subscriptions')
			->join('bookings_cart_subscriptions', '(bookings_cart_subscriptions.contactID = participant_subscriptions.contactID OR bookings_cart_subscriptions.childID = `'.$this->db->dbprefix('participant_subscriptions').'`.`childID`) AND bookings_cart_subscriptions.subID = participant_subscriptions.subID', 'inner')
			->join('subscriptions', 'subscriptions.subID = participant_subscriptions.subID', 'left')
			->join('bookings', 'subscriptions.bookingID = bookings.bookingID', 'left')
			->join('family_children', 'participant_subscriptions.childID = family_children.childID', 'left')
			->join('family_contacts', 'participant_subscriptions.contactID = family_contacts.contactID', 'left')
			->where($where)
			->where('('.$this->db->dbprefix('family_children').'.familyID = '.$familyID.' OR  '.$this->db->dbprefix('family_contacts').'.familyID = '.$familyID.')')
			->where($search_where, NULL, FALSE)
			->order_by('added desc')
			->limit($this->pagination_library->amount, $this->pagination_library->start)
			->get();

		// get sub ids
		$subIDs = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$subIDs[] = $row->subID;
			}
		}
		// check for valid config
		if ($this->gocardless_library->valid_config() !== TRUE) {
			$info = 'Please complete your GoCardless API details in ' . anchor('settings/integrations', 'Settings') . ' to create and manage subscriptions';
		} else if ($this->settings_library->get('send_gocardless_mandate') != 1) {
			$info = 'Please enable sending of GoCardless Mandate Links in ' . anchor('settings/emailsms', 'Settings') . ' to create and manage subscriptions';
		}

		$session_types = array();

		if(count($subIDs) > 0) {
			$sub_types = $this->db
				->select('subscriptions_lessons_types.subID,
					GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('lesson_types') . ' .name SEPARATOR \'!SEPARATOR!\') AS types')
				->from('subscriptions_lessons_types')
				->join('lesson_types', 'subscriptions_lessons_types.typeID = lesson_types.typeID', 'left')
				->where_in('subscriptions_lessons_types.subID', $subIDs)
				->group_by('subscriptions_lessons_types.subID')->get();

			if($sub_types->num_rows() > 0) {
				foreach($sub_types->result() as $row) {
					$types = explode("!SEPARATOR!", $row->types);
					if (is_array($types) && count($types) > 0) {
						foreach ($types as $item) {
							if ($item != "other") {
								$session_types[$row->subID][$item] = $item;
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

		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('first_name ASC')->get();
		$children = $this->db->from('family_children')->where($where)->order_by('first_name ASC')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'subs' => $res,
			'contacts' => $contacts,
			'children' => $children,
			'session_types' => $session_types,
			'familyID' => $familyID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'valid_config' => $this->gocardless_library->valid_config()
		);

		// load view
		$this->crm_view('participants/subscriptions', $data);
	}

	/**
	 * show all participants subscriptions
	 * @return void
	 */
	public function view_all($familyID = NULL) {

		// look up
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('family')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$family_info = $row;
		}

		// set defaults
		$icon = 'calendar-alt';
		$tab = 'subscriptions';
		$current_page = 'subscriptions';
		$page_base = 'participants/subscriptions/all';
		$section = 'participants';
		$title = 'Subscriptions';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array();

		$where = array(
			'main' => TRUE
		);

		$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();


		// set where
		$where = array(
			'participant_subscriptions.accountID' => $this->auth->user->accountID,
			'participant_subscriptions.status !=' => 'cancelled'
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'project_name' => NULL,
			'participant_name' => NULL,
			'frequency' => NULL,
			'price' => NULL,
			'no_sessions_per_week' => NULL,
			'provider' => NULL,
			'status' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_project_name', 'Project Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_participant_name', 'Participant Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_frequency', 'Frequency', 'trim|xss_clean');
			$this->form_validation->set_rules('search_price', 'Price', 'trim|xss_clean');
			$this->form_validation->set_rules('search_no_sessions_per_week', 'Number of Sessions per Week', 'trim|xss_clean');
			$this->form_validation->set_rules('search_provider', 'Provider', 'trim|xss_clean');
			$this->form_validation->set_rules('search_status', 'Status', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['project_name'] = set_value('search_project_name');
			$search_fields['participant_name'] = set_value('search_participant_name');
			$search_fields['frequency'] = set_value('search_frequency');
			$search_fields['price'] = set_value('search_price');
			$search_fields['no_sessions_per_week'] = set_value('search_no_sessions_per_week');
			$search_fields['provider'] = set_value('search_provider');
			$search_fields['status'] = set_value('search_status');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			if ($search_fields['project_name'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("bookings") . "`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['project_name']). "%'";
			}

			if ($search_fields['participant_name'] != '') {
				$search_where[] = "(CONCAT_WS(' ', " . $this->db->dbprefix('family_contacts').".`title`, " . $this->db->dbprefix('family_contacts').".`first_name`, " . $this->db->dbprefix('family_contacts').".`last_name`) LIKE '%" . $this->db->escape_like_str($search_fields['participant_name']) . "%'"." OR CONCAT_WS(' ', " . $this->db->dbprefix('family_children').".`first_name`, " . $this->db->dbprefix('family_children').".`last_name`) LIKE '%" . $this->db->escape_like_str($search_fields['participant_name']) . "%')";
			}

			if ($search_fields['frequency'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("subscriptions") . "`.`frequency` = " . $this->db->escape($search_fields['frequency']);
			}

			if ($search_fields['price'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("subscriptions") . "`.`price` = " . $this->db->escape($search_fields['price']);
			}

			if ($search_fields['no_sessions_per_week'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("subscriptions") . "`.`sessions_per_week` = " . $this->db->escape($search_fields['no_sessions_per_week']);
			}

			if ($search_fields['provider'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("subscriptions") . "`.`payment_provider` = " . $this->db->escape($search_fields['provider']);
			}

			if ($search_fields['status'] != '') {
				$search_where[] = "`" . $this->db->dbprefix("participant_subscriptions") . "`.`status` = " . $this->db->escape($search_fields['status']);
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		$res = $this->db->select('subscriptions.*, bookings.name, participant_subscriptions.status, participant_subscriptions.id,
		IFNULL('.$this->db->dbprefix('family_contacts').'.familyID,'.$this->db->dbprefix('family_children').'.familyID) as familyID,
		IFNULL('.$this->db->dbprefix('family_contacts').'.first_name,'.$this->db->dbprefix('family_children').'.first_name) as first_name,
		IFNULL('.$this->db->dbprefix('family_contacts').'.last_name,'.$this->db->dbprefix('family_children').'.last_name) as last_name,
		bookings_cart_subscriptions.cartID,bookings_cart_subscriptions.subID,
		family_children.childID, family_contacts.contactID')
			->from('participant_subscriptions')
			->join('bookings_cart_subscriptions', '(bookings_cart_subscriptions.contactID = participant_subscriptions.contactID OR bookings_cart_subscriptions.childID = `'.$this->db->dbprefix('participant_subscriptions').'`.`childID`) AND bookings_cart_subscriptions.subID = participant_subscriptions.subID', 'inner')
			->join('subscriptions', 'subscriptions.subID = participant_subscriptions.subID', 'left')
			->join('bookings', 'subscriptions.bookingID = bookings.bookingID', 'left')
			->join('family_children', 'participant_subscriptions.childID = family_children.childID', 'left')
			->join('family_contacts', 'participant_subscriptions.contactID = family_contacts.contactID', 'left')
			->where($where)
			->where($search_where, NULL, FALSE)
			->order_by('added desc')
			->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->select('subscriptions.*, bookings.name, participant_subscriptions.status, participant_subscriptions.id,
		IFNULL('.$this->db->dbprefix('family_contacts').'.familyID,'.$this->db->dbprefix('family_children').'.familyID) as familyID,
		IFNULL('.$this->db->dbprefix('family_contacts').'.first_name,'.$this->db->dbprefix('family_children').'.first_name) as first_name,
		IFNULL('.$this->db->dbprefix('family_contacts').'.last_name,'.$this->db->dbprefix('family_children').'.last_name) as last_name,
		bookings_cart_subscriptions.cartID,bookings_cart_subscriptions.subID,
		family_children.childID, family_contacts.contactID')
			->from('participant_subscriptions')
			->join('bookings_cart_subscriptions', '(bookings_cart_subscriptions.contactID = participant_subscriptions.contactID OR bookings_cart_subscriptions.childID = `'.$this->db->dbprefix('participant_subscriptions').'`.`childID`) AND bookings_cart_subscriptions.subID = participant_subscriptions.subID', 'inner')
			->join('subscriptions', 'subscriptions.subID = participant_subscriptions.subID', 'left')
			->join('bookings', 'subscriptions.bookingID = bookings.bookingID', 'left')
			->join('family_children', 'participant_subscriptions.childID = family_children.childID', 'left')
			->join('family_contacts', 'participant_subscriptions.contactID = family_contacts.contactID', 'left')
			->where($where)
			->where($search_where, NULL, FALSE)
			->order_by('added desc')
			->limit($this->pagination_library->amount, $this->pagination_library->start)
			->get();

		// get sub ids
		$subIDs = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$subIDs[] = $row->subID;
			}
		}
		// check for valid config
		if ($this->gocardless_library->valid_config() !== TRUE) {
			$info = 'Please complete your GoCardless API details in ' . anchor('settings/integrations', 'Settings') . ' to create and manage subscriptions';
		} else if ($this->settings_library->get('send_gocardless_mandate') != 1) {
			$info = 'Please enable sending of GoCardless Mandate Links in ' . anchor('settings/emailsms', 'Settings') . ' to create and manage subscriptions';
		}

		$session_types = array();

		if(count($subIDs) > 0) {
			$sub_types = $this->db
				->select('subscriptions_lessons_types.subID,
					GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('lesson_types') . ' .name SEPARATOR \'!SEPARATOR!\') AS types')
				->from('subscriptions_lessons_types')
				->join('lesson_types', 'subscriptions_lessons_types.typeID = lesson_types.typeID', 'left')
				->where_in('subscriptions_lessons_types.subID', $subIDs)
				->group_by('subscriptions_lessons_types.subID')->get();

			if($sub_types->num_rows() > 0) {
				foreach($sub_types->result() as $row) {
					$types = explode("!SEPARATOR!", $row->types);
					if (is_array($types) && count($types) > 0) {
						foreach ($types as $item) {
							if ($item != "other") {
								$session_types[$row->subID][$item] = $item;
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

		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('first_name ASC')->get();
		$children = $this->db->from('family_children')->where($where)->order_by('first_name ASC')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'subs' => $res,
			'contacts' => $contacts,
			'children' => $children,
			'session_types' => $session_types,
			'familyID' => $familyID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'valid_config' => $this->gocardless_library->valid_config()
		);

		// load view
		$this->crm_view('participants/subscriptions-view-all', $data);
	}

	/**
	 * get projects' session type
	 * @param int @project_id
	 */
	function get_session_type($project_id){
		header("Content-type:application/json");
		// check params
		if (empty($project_id)) {
			$json['result'] = 'ERROR';
			echo json_encode($json);
			return TRUE;
		}
		$where = array(
			'bookings.accountID' => $this->auth->user->accountID,
			'bookings.bookingID' => $project_id
		);

		$bookings_lesson_type = $this->db->select('lesson_types.name, lesson_types.typeID')
			->from('bookings')
			->join('bookings_lessons', 'bookings.bookingID = bookings_lessons.bookingID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->where($where)
			->order_by('name ASC')->get();
		$session_type = array();
		if($bookings_lesson_type->num_rows() > 0) {
			foreach($bookings_lesson_type->result() as $item) {
				$session_type['data'][$item->typeID] = $item->name;
			}
			$session_type['result'] = 'SUCCESS';
			echo json_encode($session_type);
			return TRUE;
		}else{
			$json['result'] = 'ERROR';
			echo json_encode($json);
			return TRUE;
		}

	}
	/**
	 * activate subscription
	 * @param int @subID
	 */
	public function activate($subID) {
		// check params
		if (empty($subID)) {
			show_404();
		}

		$where = array(
			'participant_subscriptions.subID' => $subID,
			'participant_subscriptions.accountID' => $this->auth->user->accountID,
			'participant_subscriptions.status' => 'inactive'
		);

		$query = $this->db->from('participant_subscriptions')
							->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID')
							->where($where)->limit(1)->get();

		if($query->num_rows() == 0) {
			show_404();
		}

		foreach($query->result() as $row) {
			$sub_info = $row;

			$activate = $this->gocardless_library->new_subscription_crm($subID);

			if($activate === FALSE) {
				$this->session->set_flashdata('error', 'There was an error activating the subscription');
			} else {
				if ($activate === 'mandate_sent') {
					$this->session->set_flashdata('info', 'Mandate link sent to contact');
				} else if ($activate === TRUE) {
					$this->session->set_flashdata('success', 'Direct debit activated');
				}


			}

			$redirect_to = 'participants/subscriptions/' . $sub_info->familyID;

			redirect($redirect_to);
		}
	}

	/**
	 * delete a subscription
	 * @param  int $subID
	 * @return mixed
	 */
	public function remove($subID = NULL) {

		// check params
		if (empty($subID)) {
			show_404();
		}

		$where = array(
			'subscriptions.subID' => $subID,
			'subscriptions.accountID' => $this->auth->user->accountID,
		);

		// run query
		$query = $this->db->from('subscriptions')
							->join('participant_subscriptions', 'subscriptions.subID = participant_subscriptions.subID')
							->where($where)->where_in('participant_subscriptions.status', array('inactive', 'cancelled'))->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$sub_info = $row;

			// all ok, delete
			$query = $this->db->delete('subscriptions', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', 'Subscription has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', 'Subscription could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'participants/subscriptions/' . $sub_info->familyID;

			redirect($redirect_to);
		}
	}

	/**
	 * cancel a subscription
	 * @param  int $planID
	 * @return mixed
	 */
	public function cancel($subID = NULL) {

		// check params
		if (empty($subID)) {
			show_404();
		}

		$where = array(
			'subscriptions.subID' => $subID,
			'subscriptions.accountID' => $this->auth->user->accountID,
			'participant_subscriptions.status !=' => 'cancelled'
		);

		// run query
		$query = $this->db->select('subscriptions.*, participant_subscriptions.childID, participant_subscriptions.gc_subscription_id')
							->from('subscriptions')
							->join('participant_subscriptions', 'subscriptions.subID = participant_subscriptions.subID')
							->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$sub_info = $row;
		}

		// attempt cancel
		if ($this->gocardless_library->cancel_subscription($sub_info->gc_subscription_id)) {
			$this->session->set_flashdata('success', 'Subscription has been cancelled successfully.');

			$where = array (
				'family_children.childID' => $sub_info->childID,
				'family_contacts.accountID' => $this->auth->user->accountID,
				'family_contacts.main' => TRUE,
			);

			$res = $this->db->select('family_contacts.first_name, family_contacts.email, family_contacts.familyID, family_contacts.contactID')
							->from('family_contacts')
							->join('family_children', 'family_contacts.familyID = family_children.familyID')
							->where($where)->limit(1)->get();

			if($res->num_rows() == 0) {
				return FALSE;
			}

			foreach ($res->result() as $contact_info) break;

			//send email
			$smart_tags = array();
			$smart_tags['contact_first'] = $contact_info->first_name;

			//get company name
			$where = array(
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->select('company')->from('accounts')->where($where)->limit(1)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// replace tags
					$smart_tags['company'] = $row->company;
				}
			}

			//get org name
			$where = array(
				'bookings.accountID' => $this->auth->user->accountID,
				'bookings.bookingID' => $sub_info->bookingID
			);

			$res = $this->db->select('orgs.name as org_name, bookings.name as bookings_name')
								->from('orgs')
								->join('bookings', 'orgs.orgID = bookings.orgID')
								->where($where)
								->limit(1)
								->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// replace tags
					$smart_tags['org_name'] = $row->org_name;
					$smart_tags['event_name'] = $row->bookings_name;
				}
			}

			$smart_tags['subscription_details'] =
				'Name: ' . $sub_info->subName . '<br>Frequency: ' . ucfirst($sub_info->frequency) . '<br>Rate: ' . currency_symbol() . $sub_info->price . '<br>No. of Sessions per Week: ' . $sub->no_of_sessions_per_week . '<br>';

			$smart_tags['link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';

			// get email template
			$subject = $this->settings_library->get('email_cancel_subscription_subject', $this->auth->user->accountID);
			$email_html = $this->settings_library->get('email_cancel_subscription', $this->auth->user->accountID);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// send
			if ($this->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->auth->user->accountID)) {
				$byID = NULL;
				if (isset($this->auth->user->staffID)) {
					$byID = $this->auth->user->staffID;
				}

				// get html email and convert to plain text
				$this->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				// save
				$data = array(
					'familyID' => $contact_info->familyID,
					'contactID' => $contact_info->contactID,
					'byID' => $byID,
					'type' => 'email',
					'destination' => $contact_info->email,
					'subject' => $subject,
					'contentHTML' => $email_html,
					'contentText' => $email_plain,
					'status' => 'sent',
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				$this->db->insert('family_notifications', $data);
			}

			$data = array(
				'status' => 'cancelled',
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
			);

			$where = array(
				'subID' => $subID,
			);

			$this->db->update('participant_subscriptions', $data, $where, 1);
		}


			// determine which page to send the user back to
			$redirect_to = 'participants/subscriptions/' . $sub_info->familyID;

		redirect($redirect_to);
	}

	/**
	 * cancel a subscription for specific user
	 * @param  int $planID
	 * @return mixed
	 */
	public function inactive_participant_subscription($id) {

		if($id == NULL || !isset($this->auth->user->accountID)) {
			echo json_encode([
				'error' => 'Invalid argument passed. Please check and try again later.'
			]);
			exit();
		}

		\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $this->cart_library->accountID));

		$where = array(
			'participant_subscriptions.id' => $id,
			'participant_subscriptions.accountID' => $this->auth->user->accountID,
		);

		$res = $this->db->select('participant_subscriptions.gc_subscription_id, participant_subscriptions.childID, participant_subscriptions.contactID, subscriptions.subID, subscriptions.subName, subscriptions.frequency, subscriptions.price, subscriptions.bookingID, subscriptions.no_of_sessions_per_week, participant_subscriptions.stripe_subscription_id, subscriptions.payment_provider')
			->from('participant_subscriptions')
			->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID')
			->where($where)
			->get();

		if($res->num_rows() > 0) {
			foreach($res->result() as $sub) break;

			$cancel = FALSE;

			switch($sub->payment_provider) {
				case 'gocardless':
					if($sub->gc_subscription_id != NULL) {
						$cancel = $this->gocardless_library->cancel_subscription($sub->gc_subscription_id);
					}
					break;
				case 'stripe':
					if($sub->stripe_subscription_id !== NULL) {
						try {
							$subscription = \Stripe\Subscription::retrieve($sub->stripe_subscription_id);
							$qty = $subscription->items->data[0]->quantity;
							if ($qty > 1) {
								$cancel = $subscription->update($sub->stripe_subscription_id,
									['quantity' => $qty - 1]);
								if (isset($cancel)) {
									$cancel = TRUE;
								}
							} else {
								$cancel = $subscription->update($sub->stripe_subscription_id,
									['cancel_at_period_end' => true]);
								if (isset($cancel)) {
									$cancel = TRUE;
								}
							}
						} catch(\Stripe\Error\Card $e) {
							$body = $e->getJsonBody();
							echo json_encode([
								'error' => $body['error']['message']
							]);
							exit();
						} catch(\Stripe\Exception\InvalidRequestException $e) {
							$body = $e->getJsonBody();
							echo json_encode([
								'error' => $body['error']['message']
							]);
							exit();
						}
					}
					break;
			}

			if($cancel === TRUE) {

				$data = array(
					'status' => 'inactive',
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if($sub->payment_provider == "gocardless"){
					$data ['gc_subscription_id'] = '';
					$data ['status'] = 'cancelled';
				}

				$this->db->update('participant_subscriptions', $data, $where, 1);

				$where = array(
					'bookings.bookingID' => $sub->bookingID,
					'bookings.accountID' => $this->auth->user->accountID,
				);

				// run query
				$query = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

				// no match
				if ($query->num_rows() == 0) {
					echo json_encode([
						'error' => 'Invalid argument passed. Please check and try again later.'
					]);
					exit();
				}

				// match
				foreach ($query->result() as $row) {
					$booking_info = $row;
				}

				if ($booking_info->type != 'event' && $booking_info->project != 1) {
					echo json_encode([
						'error' => 'Invalid argument passed. Please check and try again later.'
					]);
					exit();
				}

				$participants_id_field = 'childID';
				$participants_table = 'family_children';

				if (!empty($sub->contactID)) {
					$participants_id_field = 'contactID';
					$participants_table = 'family_contacts';
				}

				$where = array (
					$participants_table.'.'.$participants_id_field => $sub->$participants_id_field,
					'family_contacts.accountID' => $this->auth->user->accountID
				);

				$res = $this->db->select('family_contacts.first_name,family_contacts.last_name, family_contacts.email, family_contacts.familyID, family_contacts.contactID')
					->from('family_contacts')
					->join('family_children', 'family_contacts.familyID = family_children.familyID')
					->where($where)->limit(1)->get();

				if($res->num_rows() == 0) {
					echo json_encode([
						'error' => 'Invalid argument passed. Please check and try again later.'
					]);
					exit();
				}

				foreach ($res->result() as $contact_info) break;

				//send email
				$smart_tags = array();
				$smart_tags['contact_first'] = $contact_info->first_name;
				$smart_tags['contact_last'] = $contact_info->last_name;

				//get company name
				$where = array(
					'accountID' => $this->auth->user->accountID
				);
				$res = $this->db->select('company')->from('accounts')->where($where)->limit(1)->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						// replace tags
						$smart_tags['company'] = $row->company;
					}
				}

				//get org name
				$where = array(
					'bookings.accountID' => $this->auth->user->accountID,
					'bookings.bookingID' => $sub->bookingID
				);

				$res = $this->db->select('orgs.name as org_name, bookings.name as bookings_name')
					->from('orgs')
					->join('bookings', 'orgs.orgID = bookings.orgID')
					->where($where)
					->limit(1)
					->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						// replace tags
						$smart_tags['org_name'] = $row->org_name;
						$smart_tags['event_name'] = $row->bookings_name;
					}
				}

				$smart_tags['subscription_details'] =
					'Name: ' . $sub->subName . '<br>Frequency: ' . ucfirst($sub->frequency) . '<br>Rate: ' . currency_symbol() . $sub->price . '<br>No. of Sessions per Week: ' . $sub->no_of_sessions_per_week . '<br>';

				$smart_tags['link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';
				$smart_tags['login_link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';

				// get email template
				$subject = $this->settings_library->get('email_cancel_subscription_subject', $this->auth->user->accountID);
				$email_html = $this->settings_library->get('email_cancel_subscription', $this->auth->user->accountID);

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
				}

				//Send Email to staff
				$email = $this->settings_library->get('email', $this->cart_library->accountID);
				if(!empty($email)){
					// get email template
					$staff_subject = $this->settings_library->get('staff_cancel_subscription_subject', $this->cart_library->accountID);
					$staff_email_html = $this->settings_library->get('staff_cancel_subscription_body', $this->cart_library->accountID);
					if(!empty($staff_subject) && !empty($staff_email_html)){
						// replace smart tags in email
						foreach ($smart_tags as $key => $value) {
							$staff_email_html = str_replace('{' . $key . '}', $value, $staff_email_html);
							$staff_subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $staff_subject);
						}
					}
					$this->crm_library->send_email($email, $staff_subject, $staff_email_html, array(), TRUE, $this->cart_library->accountID);
				}

				// send
				if ($this->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->auth->user->accountID)) {
					$byID = NULL;
					if (isset($this->auth->user->staffID)) {
						$byID = $this->auth->user->staffID;
					}

					// get html email and convert to plain text
					$this->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					// save
					$data = array(
						'familyID' => $contact_info->familyID,
						'contactID' => $contact_info->contactID,
						'byID' => $byID,
						'type' => 'email',
						'destination' => $contact_info->email,
						'subject' => $subject,
						'contentHTML' => $email_html,
						'contentText' => $email_plain,
						'status' => 'sent',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID
					);

					$this->db->insert('family_notifications', $data);
				}

				echo json_encode([
					'success' => 'The subscription has been cancelled and we\'ve also sent an email confirming this.'
				]);
				exit();
			} else {
				echo json_encode([
					'error' => 'There was an error cancelling selected subscription.'
				]);
				exit();
			}
		} else {
			echo json_encode([
				'error' => 'There was an error cancelling selected subscription.'
			]);
			exit();
		}
	}

	/**
	 * Activate a subscription for specific user
	 * @param  int $planID
	 * @return mixed
	 */
	public function activate_participant_subscription($id){
		if($id == NULL || !isset($this->auth->user->accountID)) {
			echo json_encode([
				'error' => 'Invalid argument passed. Please check and try again later.'
			]);
			exit();
		}

		$where = array(
			'participant_subscriptions.id' => $id,
			'participant_subscriptions.accountID' => $this->auth->user->accountID,
		);

		$res = $this->db->select('participant_subscriptions.gc_subscription_id, participant_subscriptions.childID, participant_subscriptions.contactID, subscriptions.subID, subscriptions.subName, subscriptions.frequency, subscriptions.price, subscriptions.bookingID, subscriptions.no_of_sessions_per_week, participant_subscriptions.stripe_subscription_id, subscriptions.payment_provider')
			->from('participant_subscriptions')
			->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID')
			->where($where)
			->get();

		if($res->num_rows() > 0) {
			foreach ($res->result() as $sub) break;

			$cancel = FALSE;

			switch ($sub->payment_provider) {
				case 'gocardless':
					if ($sub->gc_subscription_id != NULL) {
						$cancel = $this->gocardless_library->cancel_subscription($sub->gc_subscription_id);
					}
					break;
				case 'stripe':
					if ($sub->stripe_subscription_id !== NULL) {
						try {
							\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $this->cart_library->accountID));
							$subscription = \Stripe\Subscription::retrieve($sub->stripe_subscription_id);
							$qty = $subscription->items->data[0]->quantity;

							if($subscription->cancel_at_period_end == 1){
								$cancel = $subscription->update($sub->stripe_subscription_id, [
									'cancel_at_period_end' => false
								]);
								if (isset($cancel)) {
									$cancel = TRUE;
								}
							}else{
								$cancel = $subscription->update($sub->stripe_subscription_id,
									['quantity' => $qty + 1]);
								if (isset($cancel)) {
									$cancel = TRUE;
								}
							}
						} catch (\Stripe\Error\Card $e) {
							$body = $e->getJsonBody();
							echo json_encode([
								'error' => $body['error']['message']
							]);
							exit();
						} catch (\Stripe\Exception\ApiConnectionException $e) {
							$body = $e->getJsonBody();
							echo json_encode([
								'error' => $body['error']['message']
							]);
							exit();
						} catch (\Stripe\Exception\InvalidRequestException $e) {
							$body = $e->getJsonBody();
							echo json_encode([
								'error' => $body['error']['message']
							]);
							exit();
						}
					}
					break;
			}

			if($cancel === TRUE) {

				$data = array(
					'status' => 'active',
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$this->db->update('participant_subscriptions', $data, $where, 1);

				$where = array(
					'bookings.bookingID' => $sub->bookingID,
					'bookings.accountID' => $this->auth->user->accountID,
				);

				// run query
				$query = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

				// no match
				if ($query->num_rows() == 0) {
					echo json_encode([
						'error' => 'Invalid argument passed. Please check and try again later.'
					]);
					exit();
				}

				// match
				foreach ($query->result() as $row) {
					$booking_info = $row;
				}

				if ($booking_info->type != 'event' && $booking_info->project != 1) {
					echo json_encode([
						'error' => 'Invalid argument passed. Please check and try again later.'
					]);
					exit();
				}

				$participants_id_field = 'childID';
				$participants_table = 'family_children';

				if (!empty($sub->contactID)) {
					$participants_id_field = 'contactID';
					$participants_table = 'family_contacts';
				}

				$where = array (
					$participants_table.'.'.$participants_id_field => $sub->$participants_id_field,
					'family_contacts.accountID' => $this->auth->user->accountID
				);

				$res = $this->db->select('family_contacts.first_name, family_contacts.email, family_contacts.familyID, family_contacts.contactID')
					->from('family_contacts')
					->join('family_children', 'family_contacts.familyID = family_children.familyID')
					->where($where)->limit(1)->get();

				if($res->num_rows() == 0) {
					echo json_encode([
						'error' => 'Invalid argument passed. Please check and try again later.'
					]);
					exit();
				}

				foreach ($res->result() as $contact_info) break;

				//send email
				$smart_tags = array();
				$smart_tags['contact_first'] = $contact_info->first_name;

				//get company name
				$where = array(
					'accountID' => $this->auth->user->accountID
				);
				$res = $this->db->select('company')->from('accounts')->where($where)->limit(1)->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						// replace tags
						$smart_tags['company'] = $row->company;
					}
				}

				//get org name
				$where = array(
					'bookings.accountID' => $this->auth->user->accountID,
					'bookings.bookingID' => $sub->bookingID
				);

				$res = $this->db->select('orgs.name as org_name, bookings.name as bookings_name')
					->from('orgs')
					->join('bookings', 'orgs.orgID = bookings.orgID')
					->where($where)
					->limit(1)
					->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						// replace tags
						$smart_tags['org_name'] = $row->org_name;
						$smart_tags['event_name'] = $row->bookings_name;
					}
				}

				//get session types
				$where = array(
					'subscriptions_lessons_types.subID' => $sub->subID,
					'subscriptions_lessons_types.accountID' => $this->auth->user->accountID
				);

				$smart_tags['subscription_details'] =
					'Name: ' . $sub->subName . '<br>Frequency: ' . ucfirst($sub->frequency) . '<br>Rate: ' . currency_symbol() . $sub->price . '<br>No. of Sessions per Week: ' . $sub->no_of_sessions_per_week . '<br>';

				$smart_tags['link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';

				// get email template
				$subject = $this->settings_library->get('email_confirm_subscription_subject', $this->auth->user->accountID);
				$email_html = $this->settings_library->get('email_confirm_subscription', $this->auth->user->accountID);

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
					$subject = str_replace('{' . $key . '}', $value, $subject);
				}

				// send
				if ($this->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->auth->user->accountID)) {
					$byID = NULL;
					if (isset($this->auth->user->staffID)) {
						$byID = $this->auth->user->staffID;
					}

					// get html email and convert to plain text
					$this->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					// save
					$data = array(
						'familyID' => $contact_info->familyID,
						'contactID' => $contact_info->contactID,
						'byID' => $byID,
						'type' => 'email',
						'destination' => $contact_info->email,
						'subject' => $subject,
						'contentHTML' => $email_html,
						'contentText' => $email_plain,
						'status' => 'sent',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID
					);

					$this->db->insert('family_notifications', $data);
				}

				echo json_encode([
					'success' => 'The subscription has been re-activated and we\'ve also sent an email confirming this.'
				]);
				exit();
			} else {
				echo json_encode([
					'error' => 'There was an error re-activating selected subscription.'
				]);
				exit();
			}
		}else{
			echo json_encode([
				'error' => 'There was an error re-activating selected subscription.'
			]);
			exit();
		}
	}

	public function session($cartID){
		$where = array(
			'bookings_cart.cartID' => $cartID,
			'bookings_cart.accountID' => $this->cart_library->accountID,
			'bookings_cart.type' => 'booking'
		);
		$res = $this->db->select('bookings_cart.contactID, bookings_cart.familyID, GROUP_CONCAT(DISTINCT sessions.blockID) as blockIDs')
			->from('bookings_cart')
			->join('bookings_cart_sessions as sessions', 'bookings_cart.cartID = sessions.cartID', 'left')
			->where($where)
			->get();

		if ($res->num_rows() == 0) {
			show_404();
		}
		$familyID = 0;
		foreach ($res->result() as $cart) {
			$contactID = $cart->contactID;
			$blockIDs = $cart->blockIDs;
			$familyID = $cart->familyID;
		}

		$args['cartID'] = $cartID;
		$args['contactID'] = $contactID;
		$args['accountID'] = $this->auth->user->accountID;
		$args['in_crm'] = TRUE;

		$blockID = $blockIDs;

		$this->cart_library->init($args);


		$title = 'Update Sessions';
		$section = 'participants';
		$current_page = 'subscriptions';
		$body_class = 'book';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();
		$prevent_booking = FALSE;
		$participants = array();
		$selected_participants = array();
		$subs = array();
		$selected_subs = array();
		$subscriptions_only = FALSE;
		$selected_lessons = array();
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/subscriptions/all' => 'Subscriptions'
		);

		// look up block
		$where = array(
			'bookings_blocks.blockID' => $blockID,
		);

		$blocks_where = [];
		if (!$this->in_crm) {
			$where['bookings.disable_online_booking !='] = 1;
		}
		$blocks = $this->cart_library->get_blocks($where, $blocks_where);

		// if doesn't exist, 404
		if (count($blocks) == 0) {
			show_404();
		}

		// get first result
		foreach ($blocks as $block_info) {
			break;
		}

		// set title
		$title = $block_info->booking;
		$register_type = $block_info->register_type;


		// get participants link
		$new_participants_link = 'account/participants/new/' . $block_info->bookingID;
		$participant_id_field = 'childID';
		$participants_table = 'family_children';
		$new_participants_link = 'booking/book/new/child';
		if (strpos($block_info->register_type, 'individuals') === 0) {
			$new_participants_link = 'account/individual/new';
			$participant_id_field = 'contactID';
			$participants_table = 'family_contacts';
			$new_participants_link = 'booking/book/new/individual';

		}
		$new_adults_link ='';$adult_id_field='';$adults_table='';
		if (strpos($block_info->register_type, 'adults_children') === 0) {
			//add participants Link
			$new_participants_link = 'booking/book/new/child';
			$participant_id_field = 'childID';
			$participants_table = 'family_children';

			//add adults Link
			$new_adults_link = 'account/individual/new';
			$adult_id_field = 'contactID';
			$adults_table = 'family_contacts';
			$new_adults_link = 'booking/book/new/individual';

		}

		// get booking
		$where = array(
			'bookings.bookingID' => $block_info->bookingID
		);
		$res = $this->db->from('bookings')
			->where($where)
			->limit(1)
			->get();

		// check if already booked sessions on this booking
		$already_booked_sessions = array();
		$already_booked_subscriptions = array();
		if ($prevent_booking !== TRUE) {
			// get block bookingID
			$where = array(
				'bookings_cart.accountID' => $this->cart_library->accountID,
				'bookings_cart.familyID' => $this->cart_library->familyID,
				'bookings_cart.type' => 'booking',
				'bookings_cart_sessions.bookingID' => $block_info->bookingID,
				'bookings_cart.cartID !=' => $this->cart_library->cartID // if editing booking
			);
			$res = $this->db->from('bookings_cart')->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					if (strpos($block_info->register_type, 'adults_children') === 0) {
						$already_booked_sessions[$row->lessonID][$row->date][] = ($row->childID == "")?$row->contactID:$row->childID;
					}else{
						$already_booked_sessions[$row->lessonID][$row->date][] = $row->$participant_id_field;
					}
				}
			}

			// get block bookingID
			$where = array(
				'bookings_cart.accountID' => $this->cart_library->accountID,
				'bookings_cart.familyID' => $this->cart_library->familyID,
				'bookings_cart.type' => 'cart',
				'participant_subscriptions.status' => 'active'
			);
			$res = $this->db->select('participant_subscriptions.subID, participant_subscriptions.childID, participant_subscriptions.contactID')
				->from('bookings_cart_subscriptions')
				->join('bookings_cart', 'bookings_cart.contactID = bookings_cart_subscriptions.contactID', 'LEFT')
				->join('participant_subscriptions','participant_subscriptions.accountID = bookings_cart_subscriptions.accountID AND
				(bookings_cart_subscriptions.childID = participant_subscriptions.childID OR bookings_cart_subscriptions.contactID = `'.$this->db->dbprefix("participant_subscriptions").'`.`contactID`)', "LEFT")
				->where($where)
				->group_by(array("participant_subscriptions.contactID", "participant_subscriptions.childID", "participant_subscriptions.subID"))
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$already_booked_subscriptions[empty($row->childID)?$row->contactID:$row->childID][] = $row->subID;
				}
			}
		}

		$sold_out = TRUE;
		foreach ($blocks as $block) {
			if ($block->availability_status_class !== 'soldout') {
				$sold_out = FALSE;
			}
		}

		// get participants
		$participants = $this->cart_library->get_participants($blockID);
		$fieldContact = NULL;$fieldChild = NULL; $arrayContact = array();$arrayChild = array();
		foreach($participants as $participant){
			if(isset($participant->contactID)){
				$fieldContact = 'contactID';
				array_push($arrayContact, $participant->contactID);
			}
			if(isset($participant->childID)){
				$fieldChild = 'childID';
				array_push($arrayChild, $participant->childID);
			}
			if(isset($participant->type) && $participant->type === "child"){
				$fieldChild = 'childID';
				array_push($arrayChild, $participant->Id);
			}
			if(isset($participant->type) && $participant->type === "parent"){
				$fieldContact = 'contactID';
				array_push($arrayContact, $participant->Id);
			}
		}

		//Get Current subscription status
		$subscription_status = array();
		$query_part ='';
		if(!empty($fieldChild) && !empty($fieldContact)){
			$query_part = 'AND (childID IN ('.implode(",", $arrayChild).') OR contactID IN ('.implode(",", $arrayChild).'))';
		}elseif(!empty($fieldChild) && empty($fieldContact)){
			$query_part = 'AND childID IN ('.implode(",", $arrayChild).')';
		}elseif(empty($fieldChild) && !empty($fieldContact)){
			$query_part = 'AND contactID IN ('.implode(",", $arrayContact).')';
		}

		$sql = 'SELECT subID, childID, contactID, status, modified FROM ' . $this->db->dbprefix('participant_subscriptions') . ' WHERE
						accountID = '.$this->cart_library->accountID.' AND status <> "active" '.$query_part.' GROUP BY contactID, childID, subID';
		$res = $this->db->query($sql);

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$subscription_status[empty($row->childID)?$row->contactID:$row->childID][$row->subID]['status'] = $row->status;
				$action_performed_date = date("d-m-Y", strtotime($row->modified));
				$subscription_status[empty($row->childID)?$row->contactID:$row->childID][$row->subID]['valid'] = $action_performed_date;
			}
		}

		$already_booked_subscriptions = array();
		if ($prevent_booking !== TRUE) {
			// get block bookingID
			$where = array(
				'participant_subscriptions.accountID' => $this->cart_library->accountID,
				'participant_subscriptions.status' => 'active'
			);
			$res = $this->db->select('subID, childID, contactID')
				->from('participant_subscriptions')
				->where($where)
				->where_in($fieldContact, $arrayContact)
				->where_in($fieldChild, $arrayChild)
				->group_by(array("participant_subscriptions.contactID", "participant_subscriptions.childID", "participant_subscriptions.subID"))
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$already_booked_subscriptions[empty($row->childID)?$row->contactID:$row->childID][] = $row->subID;
				}
			}
		}

		if($this->auth->has_features('online_booking_subscription_module')) {
			$subscriptions_only = $block_info->subscriptions_only;

			foreach($participants as $participant) {
				// get subs
				$where = array(
					'subscriptions.bookingID' => $block_info->bookingID,
					'subscriptions.accountID' => $this->cart_library->accountID,
				);

				$subs_res = $this->db->select('subscriptions.subID, no_of_sessions_per_week, session_cut_off, subName, frequency, price, individual_subscription')
					->from('subscriptions')
					->where($where)
					->group_by('subscriptions.subID')
					->get();

				if($subs_res->num_rows() > 0) {
					foreach($subs_res->result() as $sub) {
						if($sub->individual_subscription == true) {
							$where = array(
								'childID' => $participant->$participant_id_field,
								'subID' => $sub->subID,
								'status !=' => 'cancelled'
							);
							$indivdual_subscription = $this->db->from('participant_subscriptions')->where($where)->get();
							if($indivdual_subscription->num_rows() == 0){
								continue;
							}
						}

						$field='';
						if(isset($participant->$participant_id_field)){
							$field = $participant->$participant_id_field;
						}
						if(isset($participant->type)){
							$field = $participant->Id;
						}

						$subs[$field][$sub->subID] = array(
							'label' => $sub->subName . ' (' . currency_symbol() . $sub->price . ' - ' . ucfirst($sub->frequency) . ')',
							'frequency' => $sub->frequency,
							'price' => $sub->price,
							'no_of_sessions_per_week' => $sub->no_of_sessions_per_week,
							'session_cut_off' => $sub->session_cut_off
						);
					}
				}
			}
		}

		// if all checks passed, continue
		if ($prevent_booking !== TRUE) {
			// look up all current and future blocks in booking
			$where = array(
				'bookings_blocks.bookingID' => $block_info->bookingID
			);
			$search_fields = array();
			$search_fields['show_all'] = TRUE;
			$blocks = $this->cart_library->get_blocks($where, $search_fields);

			// no blocks found, 404
			if (count($blocks) == 0) {
				show_404();
			}

			// check if already in cart
			$booked_sessions = $this->cart_library->get_booked_sessions();
			foreach ($booked_sessions as $block_id => $sessions) {
				if (in_array($block_id, array_keys($blocks))) {
					foreach ($sessions as $date => $lessons) {
						foreach ($lessons as $lessonID => $participantIDs) {
							foreach ($participantIDs as $participantID) {
								if (!isset($selected_lessons[$lessonID][$date])) {
									$selected_lessons[$lessonID][$date] = array();
								}
								$selected_lessons[$lessonID][$date][] = $participantID;
								$selected_participants[$participantID] = $participantID;
								$already_in_cart = TRUE;
							}
						}
					}
				}
			}

			if ($this->input->post('process') == 1) {
				$selected_participants = (array)$this->input->post('participants');
				$selected_lessons = (array)$this->input->post('lessons');
				$monitoring_existing = (array)$this->input->post('monitoring');
				$register_type = $this->input->post('register_type');
				$selected_subs = (array) $this->input->post('subscriptions');

				//Subscription only event
				if($this->auth->has_features('online_booking_subscription_module') && $block->subscriptions_only === '1'){
					foreach ($block->lessons as $lesson_index => $lesson_value){
						if(isset($already_booked_sessions) && isset($already_booked_sessions[$lesson_index])){
							continue;
						}
						foreach ($block->dates as $date_index => $date_value) {
							if (strtotime($date_index) > strtotime(date("Y-m-d"))) {
								foreach ($selected_participants as $participant_index => $participant_value) {
									$selected_lessons[$lesson_index][$date_index][$participant_index] = $participant_value;
								}
								break 2;
							}
						}
					}
				}

				// if booking all, only have first date, so populate with all dates
				if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
					$new_selected_lessons = array();
					if (count($selected_lessons) > 0) {
						// loop lessons
						foreach ($selected_lessons as $lessonID => $dates) {
							foreach ($dates as $date => $participantIDs) {
								foreach ($participantIDs as $participantID) {
									// loop block to find out which block session is in
									foreach ($blocks as $block) {
										if (array_key_exists($lessonID, $block->lessons)) {
											// session is in this block, get dates
											foreach ($block->dates as $tmp_date => $lessons) {
												// if session happens on this date, return
												if (array_key_exists($lessonID, $lessons)) {
													if (!isset($new_selected_lessons[$lessonID][$tmp_date])) {
														$new_selected_lessons[$lessonID][$tmp_date] = array();
													}
													$new_selected_lessons[$lessonID][$tmp_date][] = $participantID;
												}
											}
										}
									}
								}
							}
						}
					}
					$selected_lessons = $new_selected_lessons;
				}

				// check not trying to book sessions they already booked
				$new_selected_lessons = array();
				if (count($selected_lessons) > 0) {
					// loop lessons
					foreach ($selected_lessons as $lessonID => $dates) {
						foreach ($dates as $date => $participantIDs) {
							foreach ($participantIDs as $participantID) {
								if (isset($already_booked_sessions[$lessonID][$date]) && in_array($participantID, $already_booked_sessions[$lessonID][$date])) {
									// session already booked in another session, skip
								} else {
									// ok
									if (!isset($new_selected_lessons[$lessonID][$date])) {
										$new_selected_lessons[$lessonID][$date] = array();
									}
									$new_selected_lessons[$lessonID][$date][] = $participantID;
								}
							}
						}
					}
				}
				$selected_lessons = $new_selected_lessons;
				$res = $this->cart_library->process_block($blockID, $selected_lessons);


				if (!is_string($res)) {
					$blocks = $this->cart_library->get_blocks($where, $blocks_where);
					$selected_lessons = $res;
				} else {
					$success = 'Your booking has been updated successfully';
					$this->session->set_flashdata('success', $success);
					redirect("bookings/subscriptions/session/".$cartID);
				}
			} else {


				$potential_participant = $this->input->get('participant');
				if (!empty($potential_participant) && array_key_exists($potential_participant, $participants)) {
					$selected_participants[] = $potential_participant;
				}


				//check for selected subscriptions
				$where = array(
					'cartID' => $this->cart_library->cartID,
					'accountID' => $this->cart_library->accountID,
				);

				$res = $this->db->from('bookings_cart_subscriptions')
					->where($where)
					->get();

				if($res->num_rows() > 0) {
					foreach($res->result() as $sub) {
						$field_val = $sub->$participant_id_field;
						if(isset($sub->contactID) &&  $sub->contactID !== '0' && !empty($sub->contactID)){
							$field_val = $sub->contactID;
						}
						if(isset($sub->childID) &&  $sub->childID !== '0' && !empty($sub->childID)){
							$field_val = $sub->childID;
						}
						$selected_subs[$field_val] = $sub->subID;
						$selected_participants[$field_val] = $field_val;
					}
					$info = $this->settings_library->get_label('participant', $this->cart_library->accountID) . ' already has this event in their cart which you can edit below';
				}
			}

		}
		// output
		$data = array(
			'title' => $title,
			'current_page' => $current_page,
			'section' => $section,
			'register_type' => $register_type,
			'body_class' => $body_class,
			'blockID' => $blockID,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'blocks' => $blocks,
			'participants' => $participants,
			'selected_participants' => $selected_participants,
			'subs' => $subs,
			'selected_subs' => $selected_subs,
			'subscriptions_only' => $subscriptions_only,
			'selected_lessons' => $selected_lessons,
			'new_participants_link' => $new_participants_link,
			'new_adults_link' => $new_adults_link,
			'already_booked_sessions' => $already_booked_sessions,
			'already_booked_subscriptions' => $already_booked_subscriptions,
			'breadcrumb_levels' => $breadcrumb_levels,
			'subscription_status' => $subscription_status,
			'in_crm' => true,
			'subscription_status' => $subscription_status,
			'cartID' => $cartID
		);

		$this->crm_view('participants/subscription_session', $data);
	}

	public function check_amount_more_than_num_payments($amount) {
		$interval_count = $this->input->post('interval_count');

		// if nothing, all ok
		if (empty($amount) || empty($interval_count)) {
			return TRUE;
		}

		// gocardless has minimum amount of 1 per payment
		if ($amount < $interval_count) {
			return FALSE;
		}

		return TRUE;
	}
}

/* End of file subscriptions.php */
/* Location: ./application/controllers/participants/subscriptions.php */
