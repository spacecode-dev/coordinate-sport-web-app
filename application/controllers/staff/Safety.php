<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Safety extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach'), array(), array('safety'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
	}

	/**
	 * show list of unread documents
	 * @return void
	 */
	public function index($staffID = NULL) {

		if ($staffID == NULL) {
			show_404();
		}

		// look up staff
		$where = array(
			'staffID' => $staffID,
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
		$tab = 'safety';
		$current_page = 'staff';
		$page_base = 'staff/safety/' . $staffID;
		$section = 'staff';
		$title = 'Health & Safety';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname
 		);

		// set where
		$addressIDs = $this->crm_library->get_upcoming_addresses($staffID);

		// if no addresses, add dummy so don't get error
		if (count($addressIDs) == 0) {
			$addressIDs = array(
				-1
			);
		}

		// run queries

		// unread docs
		$where = "(" . $this->db->dbprefix('orgs_safety_read') . ".`date` IS NULL OR (" . $this->db->dbprefix('orgs_safety_read') . ".`outdated` = 1 AND " . $this->db->dbprefix('orgs_safety_read') . ".`readID` = (
					SELECT `readID` FROM " . $this->db->dbprefix('orgs_safety_read') . " AS `read` WHERE `read`.`staffID` = " . $this->db->escape($staffID) . " AND `read`.`docID` = " . $this->db->dbprefix('orgs_safety') . ".`docID` ORDER BY `read`.`date` DESC LIMIT 1
				))) AND " . $this->db->dbprefix('orgs_safety') . ".`expiry` >= CURDATE() AND " . $this->db->dbprefix('orgs_safety') . ".`renewed` != 1 AND " . $this->db->dbprefix('orgs_safety') . ".`addressID` IN (" .implode(",", $addressIDs) . ") AND " . $this->db->dbprefix('orgs_addresses'). " .`accountID` = '" . $this->auth->user->accountID . "'";

		$unread = $this->db->select('orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode, orgs_safety.type, orgs.name, orgs_safety.docID, orgs_safety.details, orgs_safety.date, orgs_safety.expiry, orgs_safety_read.outdated')->from('orgs_safety')->join('orgs_safety_read', 'orgs_safety.docID = orgs_safety_read.docID AND ' . $this->db->dbprefix('orgs_safety_read') . '.staffID = ' . $this->db->escape($staffID), 'left outer')->join('orgs_addresses', 'orgs_addresses.addressID = orgs_safety.addressID', 'inner')->join('orgs', 'orgs.orgID = orgs_addresses.orgID', 'inner')->where($where, NULL, FALSE)->order_by('orgs_addresses.address1 asc, orgs_addresses.address2 asc, orgs_addresses.address3 asc, orgs.name asc')->get();

		// read docs
		$where = array(
			'orgs_safety_read.staffID' => $staffID,
			'orgs_addresses.accountID' => $this->auth->user->accountID
		);

		$read = $this->db->select('orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode, orgs_safety.type, orgs.name, orgs_safety.docID, orgs_safety.details, orgs_safety.date, orgs_safety.expiry, orgs_safety_read.outdated, orgs_safety_read.date as `read`')->from('orgs_safety')->join('orgs_safety_read', 'orgs_safety.docID = orgs_safety_read.docID', 'inner')->join('orgs_addresses', 'orgs_addresses.addressID = orgs_safety.addressID', 'inner')->join('orgs', 'orgs.orgID = orgs_addresses.orgID', 'inner')->where($where)->order_by('orgs_safety_read.date desc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'unread' => $unread,
			'read' => $read,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/safety', $data);
	}

}

/* End of file safety.php */
/* Location: ./application/controllers/staff/safety.php */