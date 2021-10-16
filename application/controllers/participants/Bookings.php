<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH.'/traits/Booking_account_trait.php';

class Bookings extends MY_Controller {
	use Booking_account_trait;

	public $in_crm = TRUE;
	public $fa_weight = 'far';

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants'));
	}

	/**
	 * show list of bookings
	 * @return void
	 */
	public function index($familyID = NULL) {

		if ($familyID == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('family')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'calendar-alt';
		$tab = 'bookings';
		$current_page = 'participants';
		$page_base = 'participants/bookings/' . $familyID;
		$section = 'participants';
		$title = 'Bookings';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account'
 		);

		// set where
		$where = array(
			'bookings_cart.familyID' => $familyID,
			'bookings_cart.type' => 'booking',
			'bookings_cart.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'child_id' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_child_id', 'Child', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['child_id'] = set_value('search_child_id');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-family-bookings'))) {

			foreach ($this->session->userdata('search-family-bookings') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-family-bookings', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("bookings_cart") . "`.`booked` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`" . $this->db->dbprefix("bookings_cart") . "`.`booked` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['child_id'] != '') {
				$search_where[] = "`sessions_children`.`childID` = " . $this->db->escape($search_fields['child_id']);
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select("bookings_cart.*,GROUP_CONCAT(DISTINCT " . $this->db->dbprefix("bookings") . ".name) as project_name, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, GROUP_CONCAT(DISTINCT CONCAT(children.first_name, ' ', children.last_name)) as child_names, GROUP_CONCAT(DISTINCT CONCAT(individuals.first_name, ' ', individuals.last_name)) as individual_names, GROUP_CONCAT(DISTINCT " . $this->db->dbprefix("subscriptions") . ".subName) as subscriptions")
		->from('bookings_cart')
		->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'inner')
		->join('bookings_cart_subscriptions', 'bookings_cart.cartID = bookings_cart_subscriptions.cartID', 'left')
		->join('subscriptions', 'bookings_cart_subscriptions.subID = subscriptions.subID', 'left')
		->join('bookings_cart_sessions as sessions_children', 'bookings_cart.cartID = sessions_children.cartID', 'left')
		->join('bookings', 'sessions_children.bookingID = bookings.bookingID', 'inner')
		->join('family_children as children', 'sessions_children.childID = children.childID', 'left')
		->join('bookings_cart_sessions as sessions_individuals', 'bookings_cart.cartID = sessions_individuals.cartID', 'left')
		->join('family_contacts as individuals', 'sessions_individuals.contactID = individuals.contactID', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->group_by('bookings_cart.cartID')
		->order_by('bookings_cart.booked desc')
		->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select("bookings_cart.*,GROUP_CONCAT(DISTINCT " . $this->db->dbprefix("bookings") . ".name) as project_name, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, GROUP_CONCAT(DISTINCT CONCAT(children.first_name, ' ', children.last_name)) as child_names, GROUP_CONCAT(DISTINCT CONCAT(individuals.first_name, ' ', individuals.last_name)) as individual_names, GROUP_CONCAT(DISTINCT " . $this->db->dbprefix("subscriptions") . ".subName) as subscriptions")
		->from('bookings_cart')
		->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'inner')
		->join('bookings_cart_subscriptions', 'bookings_cart.cartID = bookings_cart_subscriptions.cartID', 'left')
		->join('subscriptions', 'bookings_cart_subscriptions.subID = subscriptions.subID', 'left')
		->join('bookings_cart_sessions as sessions_children', 'bookings_cart.cartID = sessions_children.cartID', 'left')
		->join('bookings', 'sessions_children.bookingID = bookings.bookingID', 'inner')
		->join('family_children as children', 'sessions_children.childID = children.childID', 'left')
		->join('bookings_cart_sessions as sessions_individuals', 'bookings_cart.cartID = sessions_individuals.cartID', 'left')
		->join('family_contacts as individuals', 'sessions_individuals.contactID = individuals.contactID', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->group_by('bookings_cart.cartID')
		->order_by('bookings_cart.booked desc')
		->limit($this->pagination_library->amount, $this->pagination_library->start)
		->get();

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
		$children = $this->db->from('family_children')->where($where)->order_by('first_name ASC')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'children' => $children,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'bookings' => $res,
			'familyID' => $familyID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('participants/bookings', $data);
	}

	/**
	 * delete a booking
	 * @param  int $cartID
	 * @return mixed
	 */
	public function remove($cartID = NULL) {

		// check params
		if (empty($cartID)) {
			show_404();
		}

		$where = array(
			'cartID' => $cartID,
			'accountID' => $this->auth->user->accountID,
			'type' => 'booking'
		);

		// run query
		$query = $this->db->from('bookings_cart')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$cart_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings_cart', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', 'Booking has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', 'Booking could not be removed.');
			}

			// recalc family balance
			$this->crm_library->recalc_family_balance($cart_info->familyID);

			// determine which page to send the user back to
			$redirect_to = 'participants/bookings/' . $cart_info->familyID;

			redirect($redirect_to);
		}
	}

}

/* End of file bookings.php */
/* Location: ./application/controllers/participants/bookings.php */
