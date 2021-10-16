<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Useractivity extends MY_Controller
{
	public function index(){

		if (getenv('DISABLE_ACTIVITY') == 1) {
			show_404();
		}

		if (!$this->auth->user->show_user_activity && !$this->auth->account->admin) {
			show_404();
		}

		// set defaults
		$icon = 'book';
		$current_page = 'user-activity';
		$section = 'user-activity';
		$type = 'user-activity';
		$page_base = 'user-activity';
		$title = 'User Activity';
		$current_page = 'user-activity';

		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => $this->auth->user->staffID
		);

		// get staff
		$where = array(
			'staff.active' => 1
		);

		if (!$this->auth->account->admin) {
			$where['staff.accountID'] = $this->auth->user->accountID;
		}

		$query = $this->db->select("staff.*")
		->from('staff')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)
		->order_by('staff.first asc, staff.surname asc');

		if ($this->auth->user->department == 'headcoach') {
			$query->where('staff_recruitment_approvers.approverID = ' . $this->auth->user->staffID . ' OR staff.staffID = ' . $this->auth->user->staffID);
		}

		$staff = $query->get();

		$staff_list = [];
		foreach ($staff->result() as $row) {
			$staff_list[$row->staffID] = $row->first . ' ' . $row->surname;
		}

		//if user not exists just search by first user of the array (in case of directors, logging using admin account)
		if (!array_key_exists($this->auth->user->staffID, $staff_list)) {
			foreach ($staff_list as $key => $name) {
				$search_fields['staff_id'] = $key;
				break;
			}
		}

		if (!empty($this->input->get('search_date_from'))) {
			$search_fields['date_from'] = $this->input->get('search_date_from');
		}
		if (!empty($this->input->get('search_date_to'))) {
			$search_fields['date_to'] = $this->input->get('search_date_to');
		}
		if (!empty($this->input->get('search_staff_id'))) {
			$search_fields['staff_id'] = $this->input->get('search_staff_id');
		}

		// check dates
		if (!uk_to_mysql_date($search_fields['date_from'])) {
			$search_fields['date_from'] = NULL;
		}
		if (!uk_to_mysql_date($search_fields['date_to'])) {
			$search_fields['date_to'] = NULL;
		}

		// if dates empty, add default
		if (empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y');
		}
		if (empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		$search_data['date_from'] = strtotime(str_replace('/', '-', $search_fields['date_from']) . ' 00:00');
		$search_data['date_to'] = strtotime(str_replace('/', '-', $search_fields['date_to']) . ' 23:59');
		$search_data['staff_id'] = $search_fields['staff_id'];

		$last_key = 0;
		if ($this->input->get('next')) {
			$last_key = intval($this->input->get('last_key'));
		}
		$records = $this->activity_library->getRecords($last_key, $search_data);

		if (isset($records['LastEvaluatedKey'])) {
			$lastKey = $this->activity_library->unmarshal($records['LastEvaluatedKey']);
			$lastKey = $lastKey['created_at'];
		} else {
			$lastKey = 0;
		}

		$data = [
			'title' => $title,
			'page_base' => $page_base,
			'search_fields' => $search_fields,
			'records' => $records,
			'last_key' => $lastKey,
			'staff_list' => $staff_list,
			'current_page' => $current_page,
			'section' => $section
		];

		$this->crm_view('user_activity/main', $data);
	}

	public function getRecords() {
		$searchData = [
			'date_from' => strtotime(str_replace('/', '-', $this->input->post('dateFrom')) . ' 00:00'),
			'date_to' => strtotime(str_replace('/', '-', $this->input->post('dateTo')) . ' 23:59'),
			'staff_id' => $this->input->post('staffId')
		];
		$records = $this->activity_library->getRecords($this->input->post('lastKey'), $searchData);

		$result = [];
		foreach ($records['Items'] as $log) {
			$log = $this->activity_library->unmarshal($log);
			$log['created_at'] = date('d/m/Y H:i:s', $log['created_at']);
			$log['info']['url'] = site_url($log['info']['url']);
			$result['data'][] = $log;
		}

		$result['last_key'] = 0;
		if (isset($records['LastEvaluatedKey'])) {
			$result['last_key'] = $this->activity_library->unmarshal($records['LastEvaluatedKey'])['created_at'];
		}

		echo json_encode($result);
	}

}
