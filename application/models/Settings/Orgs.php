<?php

require_once APPPATH.'models/Settings/Settings.php';

class Orgs extends Settings
{
	const TABLE = 'orgs';

	private $CI;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
	}

	// TODO: refactor to repository
	public function getList($accountID = null, $orderBy = 'name asc')
	{
		$query = $this->CI->db->from(self::TABLE)->order_by($orderBy);

		$where = [];
		if (!empty($accountID)) {
			$where['accountID'] = $accountID;
		}

		$result = [];
		foreach ($query->where($where)->get()->result() as $value) {
			$result[] = $value;
		}

		return $result;
	}

	public function getById($id)
	{
		// TODO: Implement getById() method.
	}

}
