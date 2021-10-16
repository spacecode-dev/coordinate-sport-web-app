<?php

class Orgs_library
{
	private $CI;

	public function __construct() {
		$this->CI =& get_instance();
	}

	public function findContactByIdBooking($bookingId, $contactId) {
		$where = array(
			'bookings.bookingID' => $bookingId,
			'orgs_contacts.contactID' => $contactId,
			'orgs_contacts.accountID' => $this->CI->auth->user->accountID
		);

		$query = $this->CI->db->select('orgs_contacts.*')
			->from('orgs_contacts')
			->join('bookings', 'orgs_contacts.orgID = bookings.orgID', 'inner')
			->where($where)
			->limit(1)
			->get();

		$result = [];
		foreach ($query->result() as $contact_info) {
			$result = $contact_info;
		}

		return $result;
	}

	public function findContactById($contactId, $useAccount = false) {
		$where = array(
			'orgs_contacts.contactID' => $contactId
		);

		if ($useAccount) {
			$where['orgs_contacts.accountID'] = $this->CI->auth->user->accountID;
		}

		$query = $this->CI->db->select('orgs_contacts.*')
			->from('orgs_contacts')
			->where($where)
			->limit(1)
			->get();

		$result = [];
		foreach ($query->result() as $contact_info) {
			$result = $contact_info;
		}

		return $result;
	}

	public function getAllOrgs($accountId = null) {
	    $where = [];

	    if ($accountId) {
			$where['orgs.accountID'] = $accountId;
		}

	    $query = $this->CI->db->select()
			->from('orgs')
			->where($where)
			->order_by('name asc')
			->get();

	    $result = [];
	    if ($query->num_rows() < 1) {
	        return $result;
		}

		foreach ($query->result() as $row) {
	        $result[] = $row;
		}

		return $result;
	}
}
