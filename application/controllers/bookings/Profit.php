<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profit extends MY_Controller {

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
	 * show profit and loss report
	 * @param int $bookingID
	 * @param mixed $export
	 * @return void
	 */
	public function index($bookingID = NULL, $export = FALSE)
	{
		$this->load->library('reports_library');
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

		// set defaults
		$title = 'Profit & Loss';
		$buttons = '<a class="btn btn-success" href="' . site_url('bookings/costs/'. $bookingID.'/new') . '"><i class="far fa-plus"></i> Add Cost</a><a class="btn btn-primary" href="' . site_url('bookings/finances/profit/' . $bookingID . '/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$icon = 'sack-dollar';
		$tab = 'profit';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
			$page_base = 'bookings/finances/profit/'.$bookingID;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
			$page_base = 'bookings/finances/profit/'.$bookingID;
		}
		$section = 'bookings';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$week = NULL;
		$year = date("Y");
		$startdate = NULL;
		$enddate = NULL;

		if($this->input->post()){
			$week = set_value("week");
			$year = set_value("year");
			$search_fields = array("week" => $week, "year" => $year);
			$this->session->set_userdata('search-reports', $search_fields);
		}else if (($export == TRUE && is_array($this->session->userdata('search-reports')))){
			foreach ($this->session->userdata('search-reports') as $key => $value) {
				if($key == 'week'){
					$week = $value;
				}else if($key == 'year'){
					$year = $value;
				}
			}
		}
		$startdate = date("Y-m-d",strtotime($year."W".str_pad($week,2,0,STR_PAD_LEFT)."1"));
		$enddate = date("Y-m-d",strtotime($year."W".str_pad($week,2,0,STR_PAD_LEFT)."7"));

		$max_weeks = gmdate("W", strtotime("28 December " . $year));

		// calculate
		$income = $this->crm_library->count_income($booking_info, $week, $year);
		$blocks = $income['blocks'];
		$profit_costs = $this->crm_library->count_profit_costs($booking_info, $week, $year);

		//Calculate subscription payments
		$where = array(
			'bookings_cart.accountID' => $this->auth->user->accountID,
			'bookings_cart.type' => 'booking',
			'bookings_cart_subscriptions.bookingID' => $bookingID,
		);

		if($week != 0 && $week != NULL){
			$where["bookings_cart.booked >= "] = $startdate;
			$where["bookings_cart.booked <= "] = $enddate;
		}

			$where_add = " (".$this->db->dbprefix("participant_subscriptions").".stripe_subscription_id <> '' OR ".$this->db->dbprefix("participant_subscriptions").".gc_subscription_id <> '')";
		$sub_total = $this->db
			->select('IFNULL(`app_family_payments`.`amount`, "0") as amount')
			->from('bookings_cart')
			->join('bookings_cart_subscriptions', 'bookings_cart.cartID = bookings_cart_subscriptions.cartID', 'inner')
			->join('participant_subscriptions', 'bookings_cart_subscriptions.subID = participant_subscriptions.subID AND (bookings_cart_subscriptions.childID = '.$this->db->dbprefix("participant_subscriptions").'.`childID` OR bookings_cart_subscriptions.contactID = `'.$this->db->dbprefix("participant_subscriptions").'`.`contactID`)', 'inner')
			->join('family_payments', $this->db->dbprefix('family_payments').".transaction_ref LIKE CONCAT('%',Trim(".$this->db->dbprefix('participant_subscriptions').".stripe_subscription_id),'%') OR  ".$this->db->dbprefix('family_payments').".note LIKE CONCAT('%',Trim(".$this->db->dbprefix('participant_subscriptions').".gc_subscription_id),'%')", 'inner')
			->where($where)
			->where($where_add)
			->group_by("participant_subscriptions.gc_subscription_id, app_participant_subscriptions.stripe_subscription_id")
			->get();

		// check for data
		if (count($blocks) == 0) {
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
			'blocks' => $blocks,
			'sub_total' => $sub_total,
			'week' => $week,
			'year' => $year,
			'max_weeks' => $max_weeks,
			'page_base' => $page_base,
			'session_income' => $income['session_income'],
			'contract_income' => $income['contract_income'],
			'misc_income' => $income['misc_income'],
			'staff_costs' => $profit_costs['staff_costs'],
			'costs' => $profit_costs['costs'],
			'exception_refund' => $profit_costs['exception_refund'],
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		if ($export == TRUE || $export == 'true') {
			//load csv helper
			$this->load->helper('csv_helper');
			$this->load->view('bookings/profit-export', $data);
		} else {
			$this->crm_view('bookings/profit', $data);
		}
	}
}

/* End of file profit.php */
/* Location: ./application/controllers/bookings/profit.php */
