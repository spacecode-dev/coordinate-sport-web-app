<?php

class Staff_library
{
	private $CI;

	public function __construct() {
		$this->CI =& get_instance();
	}

	public function getAllStaff($accountId = null, $onlyActive = false) {
	    $where = [];

	    if (!empty($accountId)) {
	        $where['staff.accountID'] = $accountId;
        }

        if ($onlyActive) {
	        $where['staff.active'] = 1;
        }

        $query = $this->CI->db->select()
            ->from('staff')
            ->where($where)
			->order_by('first asc, surname asc')
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
