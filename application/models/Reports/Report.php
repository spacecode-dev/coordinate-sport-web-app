<?php

abstract class Reports
{
	const REPORTS_TABLE = 'reports_logs';

	private $CI;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
	}

	public function log($user, $report, $data) {
		$this->CI->db->insert(self::REPORTS_TABLE, [
			'staffID' => $user->staffID,
			'reportType' => $report,
			'data' => $data,
			'added' => time()
		]);

		return $this->CI->db->insert_id();
	}

	public function getLogs($report, $data, $only_count = false, $limit = 20, $start = 0) {

		$searchData = [
			'reportType' => $report,
			self::REPORTS_TABLE . '.added >=' => $data['from'],
			self::REPORTS_TABLE . '.added <=' => $data['to']
		];

		if (!empty($data['staff_id'])){
			$searchData[self::REPORTS_TABLE . '.staffID'] = $data['staff_id'];
		}

		if (!empty($data['accountId'])){
			$searchData['staff.accountID'] = $data['accountId'];
		}

		if ($only_count) {
			$query = $this->CI->db->select('reports_logs.id')->from(self::REPORTS_TABLE)
				->join('staff', 'staff.staffID = '. self::REPORTS_TABLE  .'.staffID', 'inner')
				->where($searchData)->get();

			$count = 0;

			if ($query->num_rows() > 0) {
				$count = $query->num_rows();
			}

			return $count;
		}

		$query = $this->CI->db->select('reports_logs.*, staff.first, staff.surname')->from(self::REPORTS_TABLE)
			->join('staff', 'staff.staffID = '. self::REPORTS_TABLE  .'.staffID', 'inner')
			->where($searchData)->order_by('id desc')->limit($limit, $start)->get();

		$result = [];
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$decodedData = json_decode($row->data, true);
				if(is_null($decodedData['date_from'])) {
					continue;
				}
				$result[$row->id] = $row;
				$result[$row->id]->decoded_data = $decodedData;
			}
		}

		return $result;
	}
}
