<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tags extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));

		$this->load->model('Settings/TagsModel');
	}

	/**
	 * show list of tags
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'tags';
		$page_base = 'settings/tags';
		$section = 'settings';
		$title = 'Tags';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
		);

		$search_fields = array(
			'name' => NULL,
			'search' => false
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-tags'))) {

			foreach ($this->session->userdata('search-tags') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-tags', $search_fields);
		}

		$res = $this->TagsModel->searchListByName($this->auth->user->accountID, $search_fields['name']);


		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'tags' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/tags', $data);
	}

	/**
	 * edit a tag
	 * @param  int $tagID
	 * @return void
	 */
	public function edit($tagID = NULL)
	{

		$tag_info = new stdClass();

		// check if editing
		if ($tagID != NULL) {

			// check if numeric
			if (!ctype_digit($tagID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'tagID' => $tagID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('settings_tags')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$tag_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Tag';
		if ($tagID != NULL) {
			$submit_to = 'settings/tags/edit/' . $tagID;
			$title = $tag_info->name;
		} else {
			$submit_to = 'settings/tags/new/';
		}
		$return_to = 'settings/tags';
		$icon = 'cog';
		$current_page = 'tags';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/tags' => 'Tags'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($tagID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($tagID == NULL) {
						// insert
						$query = $this->db->insert('settings_tags', $data);

						$tagID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'tagID' => $tagID
						);

						// update
						$query = $this->db->update('settings_tags', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {
						echo json_encode([
							'status' => 'ok',
							'id' => $tagID,
							'name' => $data['name']
						]);
					} else {
						echo json_encode([
							'status' => 'error'
						]);
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * delete a tag
	 * @param  int $tagID
	 * @return mixed
	 */
	public function remove($tagID = NULL) {

		// check params
		if (empty($tagID)) {
			show_404();
		}

		$where = array(
			'tagID' => $tagID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_tags')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$tag_info = $row;

			// all ok, delete
			$query = $this->db->delete('settings_tags', $where);

			if ($this->db->affected_rows() == 1) {
				echo json_encode([
					'status' => 'ok'
				]);
			} else {
				echo json_encode([
					'status' => 'error'
				]);
			}
		}
	}
}

/* End of file tags.php */
/* Location: ./application/controllers/settings/tags.php */
