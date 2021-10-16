<?php

abstract class Repository
{
	protected static $instances;

	protected $CI;

	protected $table;

	protected $idField;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
	}

	final private function __clone() { }

	public static function getInstance() {
		$class = get_called_class();

		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class;
		}
		return self::$instances[$class];
	}

	public function getById($id, $accountID = null) {
		$where = [$this->idField => $id];
		if (!empty($accountID)) {
			$where['accountID'] = $accountID;
		}

		$query = $this->CI->db->from($this->table)
			->where($where)
			->limit(1)
			->get();

		$result = [];
		foreach ($query->result() as $value) {
			$result = $value;
		}

		return $result;
	}

	public function getAllList($accountID = NULL, $active = NULL, $orderBy = NULL) {
		$query = $this->CI->db->from($this->table);

		$where = [];
		if (!empty($accountID)) {
			$where['accountID'] = $accountID;
		}

		if (!is_null($active)) {
			$where['active'] = (int)$active;
		}

		if (!is_null($orderBy)) {
			$query->order_by($orderBy);
		}

		$result = [];
		foreach ($query->where($where)->get()->result() as $value) {
			$result[] = $value;
		}

		return $result;
	}

	public function searchList($strictArray = [], $likeArray = [], $amount = NULL, $start = NULL, $orderBy = NULL) {
		$query = $this->CI->db->from($this->table);

		$where = [];
		if (!empty($strictArray) && is_array($strictArray)) {
			foreach ($strictArray as $key => $value) {
				$where[$key] = $value;
			}

			$query->where($where);
		}

		$like = [];
		if (!empty($likeArray) && is_array($likeArray)) {
			foreach ($likeArray as $key => $value) {
				$like[$key] = $value;
			}

			$query->like($like);
		}


		if (!is_null($amount) && !is_null($start)) {
			$query->limit($amount, $start);
		}

		if (!is_null($orderBy)) {
			$query->order_by($orderBy);
		}

		$result = [];
		foreach ($query->get()->result() as $value) {
			$result[] = $value;
		}

		return $result;
	}

	public function create($data){
		$this->CI->db->insert($this->table, $data);

		return $this->CI->db->insert_id();
	}

	public function update($id, $data, $accountID = NULL) {
		$where = [];
		if (is_array($id)) {
			$this->CI->db->where_in($this->idField, $id);
		} else {
			$where = [
				$this->idField => $id
			];
		}

		if (!empty($accountID)) {
			$where['accountID'] = $accountID;
		}

		$this->CI->db->update($this->table, $data, $where);

		return $this->CI->db->affected_rows();
	}

	public function getField($field, $accountID = NULL) {;
		$query = $this->CI->db->select($field)->from($this->table);

		$where = [];
		if (!empty($accountID)) {
			$where['accountID'] = $accountID;
		}

		$result = [];
		foreach ($query->where($where)->get()->result() as $value) {
			$result[] = $value->{$field};
		}

		sort($result);

		return $result;
	}
}
