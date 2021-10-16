<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tasks extends MY_Controller {

	/**
	 * edit task
	 * @param  int $taskID
	 * @return void
	 */
	public function edit($taskID = NULL)
	{

		$task_info = new stdClass;

		// check if editing
		if ($taskID != NULL) {

			// check if numeric
			if (!ctype_digit($taskID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'taskID' => $taskID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('tasks')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$task_info = $row;
			}

			// check permissions
			if ($task_info->staffID != $this->auth->user->staffID && !in_array($this->auth->user->department, array('management', 'directors'))) {
				show_404();
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Task';
		$submit_to = 'tasks/new/';
		$return_to = '';
		if ($taskID != NULL) {
			$title = 'Edit Task';
			$submit_to = 'tasks/edit/' . $taskID;
		}
		$icon = 'list';
		$current_page = 'dashboard';
		$section = 'dashboard';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('task', 'Task', 'trim|xss_clean|required');
			$this->form_validation->set_rules('staffID', 'Staff', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'task' => set_value('task'),
					'staffID' => set_value('staffID'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (empty($data['staffID'])) {
					$data['staffID'] = $this->auth->user->staffID;
				}

				// if new
				if ($taskID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['complete'] = 0;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($taskID == NULL) {
						// insert id
						$query = $this->db->insert('tasks', $data);
					} else {
						$where = array(
							'taskID' => $taskID
						);

						// update
						$query = $this->db->update('tasks', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($taskID == NULL) {

							$this->session->set_flashdata('success', set_value('task') . ' has been created successfully.');

						} else {

							$this->session->set_flashdata('success', set_value('task') . ' has been updated successfully.');
						}

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

		// staff
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'task_info' => $task_info,
			'taskID' => $taskID,
			'staff' => $staff_list,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('dashboard/task', $data);
	}

	/**
	 * change task status
	 * @return void
	 */
	public function status($taskID = NULL, $status = 'complete') {

		// check params
		if (empty($taskID) || !in_array($status, array('complete', 'uncomplete'))) {
			show_404();
		}

		// look up task
		$where = array(
			'taskID' => $taskID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('tasks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $task_info) {}

		// check permissions
		if ($task_info->staffID != $this->auth->user->staffID && in_array($this->auth->user->department, array('coaching', 'fulltimecoach', 'heacoach'))) {
			show_404();
		}

		// set data
		$data = array(
			'complete' => 0,
			'modified' => mdate('%Y-%m-%d %H:%i:%s')
		);

		if ($status == 'complete') {
			$data['complete'] = 1;
		}

		// update
		$res = $this->db->update('tasks', $data, $where);

		if ($this->db->affected_rows() > 0) {
			echo strtoupper($status);
			return TRUE;
		}

		show_404();
		return FALSE;

	}

	/**
	 * remove task
	 * @return void
	 */
	public function remove($taskID = NULL) {

		// check params
		if (empty($taskID)) {
			show_404();
		}

		// look up task
		$where = array(
			'taskID' => $taskID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('tasks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $task_info) {}

		// check permissions
		if ($task_info->staffID != $this->auth->user->staffID && in_array($this->auth->user->department, array('coaching', 'fulltimecoach', 'heacoach'))) {
			show_404();
		}

		// remove
		$res = $this->db->delete('tasks', $where);

		if ($this->db->affected_rows() > 0) {
			echo 'DELETED';
			return TRUE;
		}

		show_404();
		return FALSE;

	}
}

/* End of file tasks.php */
/* Location: ./application/controllers/tasks.php */