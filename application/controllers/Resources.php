<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resources extends MY_Controller {

	private $resources;
	private $read_only = FALSE;

	public function __construct() {
		// everyone has some kind of access - filtered below
		parent::__construct(FALSE, array(), array(), array('resources'));
		$this->resources = array();
		$this->_restrict_write_access();

	}

	/**
	 * show list of files
	 * @return void
	 */
	public function index($resourceID = NULL) {
		$this->_get_resources();
		$resource = NULL;

		// if no resources
		if (count($this->resources) === 0) {
			return $this->no_categories();
		}

		// set default resource
		if ($resourceID == NULL ) {
			 if( count($this->resources) > 0){
				$resource = reset($this->resources);
				$resourceID = $resource['resourceID'];
			 }
		}else{
			$resource = $this->_get_selected_resource($resourceID);
		}

		if(!array_key_exists($resourceID , $this->resources)){
			show_404();
		}

		// set defaults
		$icon = 'folder';
		$current_page = 'resources';
		$section = 'resources';
		$page_base = 'resources/'. $resourceID;
		$add_url = 'resources/'. $resourceID.'/new';
		$title = $resource['resourceName'];
		$buttons = '<a class="btn btn-success" href="' . site_url($add_url) . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'resources' => 'Resources'
		);

		if ($this->read_only === TRUE || count($this->resources) == 0){
			$buttons = NULL;
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'search' => NULL
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

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-resources'))) {

			foreach ($this->session->userdata('search-resources') as $key => $value) {
				$search_fields[$key] = $value;
			}
			$is_search = TRUE;
		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-resources', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("files") . "`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->_get_files($resourceID , $search_where);

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'resources' => $this->resources,
			'resource' => $resource,
			'brands' => $brands,
			'page_base' => $page_base,
			'files' => $res,
			'add_url' => $add_url,
			'read_only' => $this->read_only,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('resources/main', $data);
	}

	/**
	 * edit a file
	 * @param  int $resourceID
	 * @param int $attachmentID
	 * @return void
	 */
	public function edit($resourceID = NULL , $attachmentID = NULL)
	{
		$attachment_info = new stdClass;
		$resource = null;
		$this->_get_resources();

		if($resourceID != NULL){
			if(!array_key_exists($resourceID , $this->resources)){
				show_404();
		    }
		    $resource = $this->_get_selected_resource($resourceID);
		}

		// check if editing
		if ($attachmentID != NULL) {
			$attachment_info = $this->_get_attachment_info($attachmentID);
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New File';
		$submit_to = 'resources/' . $resource['resourceID'].'/new';
		$return_to = 'resources/' . $resource['resourceID'];
		if ($attachmentID != NULL) {
			$title = 'Edit File';
			$submit_to = 'resources/edit/'.$attachmentID;
		}
		$icon = 'folder';
		$current_page = 'resources';
		$section = 'resources';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'resources' => 'Resources',
			'resources/'.$resource['resourceID'] => $resource['resourceName']
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('category', 'Category', 'trim|xss_clean|required');

			if ($attachmentID == NULL) {
				$this->form_validation->set_rules('file', '', 'callback_file_check');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

                $resourceID = set_value('category');
				// prepare data
				$data = array(
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($attachmentID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				$upload_res = $this->crm_library->handle_upload();

				if ($upload_res === NULL) {
					if (empty($attachmentID)) {
						$errors[] =  trim(strip_tags($this->upload->display_errors()));
					}
				} else {
					$data['name'] = $upload_res['client_name'];
					$data['path'] = $upload_res['raw_name'];
					$data['type'] = $upload_res['file_type'];
					$data['size'] = $upload_res['file_size']*1024;
					$data['ext'] = substr($upload_res['file_ext'], 1);

					if (!empty($attachmentID)) {
						// delete previous file, if exists
						$path = UPLOADPATH;
						if (file_exists($path . $attachment_info->path)) {
							unlink($path . $attachment_info->path);
						}
					}
				}

				// final check for errors
				if (count($errors) == 0) {
					$just_added = TRUE;
					if ($attachmentID == NULL) {
						// insert id
						$query = $this->db->insert('files', $data);
						$attachmentID = $this->db->insert_id();
					} else {
						$just_added = FALSE;
						$where = array(
							'attachmentID' => $attachmentID
						);
						// update
						$query = $this->db->update('files', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if (empty($data['name'])) {
							$data['name'] = $attachment_info->name;
						}

						//update reference table
						$this->_update_resource_mapping($attachmentID, $resourceID);

						if ($just_added == TRUE) {
							$this->session->set_flashdata('success', $data['name'] . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', $data['name'] . ' has been updated successfully.');
						}
						redirect('resources/' . $resourceID);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

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
			'attachment_info' => $attachment_info,
			'attachmentID' => $attachmentID,
			'resource' => $resource,
			'resources' => $this->resources,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('resources/resource', $data);
	}

	/**
	 * delete a file
	 * @param  int $resourceID
	 * @param int $attachmentID
	 * @return mixed
	 */
	public function remove($resourceID = NULL, $attachmentID = NULL) {

		if ($this->read_only === TRUE) {
			show_403();
		}

		// check params
		if (empty($attachmentID)) {
			show_404();
		}

		$this->_get_resources();
		if (!array_key_exists($resourceID, $this->resources)) {
			show_404();
		}

		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('files')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;

			// all ok, delete
			$query = $this->db->delete('files', $where);

			// delete file, if exists
			$path = UPLOADPATH;
			if (file_exists($path . $attachment_info->path)) {
				unlink($path . $attachment_info->path);
			}

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', ucwords($attachment_info->name) . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', ucwords($attachment_info->name) . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'resources/'.$resourceID;

			redirect($redirect_to);
		}
	}

	/**
	 * send with bookings
	 * @param int brandID
	 * @param int $attachmentID
	 * @param string $value
	 * @return mixed
	 */
	public function sendwithbookings($brandID = NULL, $attachmentID = NULL, $value = NULL) {

		if ($this->read_only === TRUE) {
			show_403();
		}

		// check params
		if (empty($brandID) || empty($attachmentID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		// check brand exists
		$where = array(
			'brandID' => $brandID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('brands')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// check file exists
		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('files')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// set where
		$where = array(
			'brandID' => $brandID,
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// process
		if ($value == 'yes') {
			// check if exists
			$res = $this->db->from('files_brands')->where($where)->limit(1)->get();

			// if not, insert
			if ($res->num_rows() == 0) {
				$data = $where;
				$this->db->insert('files_brands', $data);
			}
		} else {
			// remove
			$this->db->delete('files_brands', $where, 1);
		}

		echo 'OK';
		exit();

	}

	private function _get_resources(){
		$this->resources = array();
		/*Ex -
		users with 'directors' permission will able to see all resources that belong to any permission level
		users with 'headcoach' permission will able to see all resources that belong to headcoach','fulltimecoach', 'coaching'
		*/
		$permissions =array(
			'directors' => array('directors','management','office' ,'headcoach','fulltimecoach', 'coaching' ),
			'management' => array('management','office', 'headcoach','fulltimecoach', 'coaching' ),
			'office' => array('office', 'headcoach','fulltimecoach', 'coaching' ),
			'headcoach' => array('headcoach','fulltimecoach', 'coaching'),
			'fulltimecoach' => array('fulltimecoach', 'coaching'),
			'coaching' => array('coaching')
		);

		if(array_key_exists($this->auth->user->department , $permissions)){
			$allowed_permissions = $permissions[$this->auth->user->department];
			$where = array(
				'accountID' => $this->auth->user->accountID,
			);

			$query = $this->db->from('settings_resources')
			->where($where)
			->where_in('permissionLevel', $allowed_permissions)
			->order_by('name asc')
			->get();

			foreach ($query->result() as $row) {
				$this->resources[$row->resourceID]['resourceName'] = $row->name;
				$this->resources[$row->resourceID]['resourceID'] = $row->resourceID;
				$this->resources[$row->resourceID]['permissionLevel'] = $row->permissionLevel;
			}
		}
	}

	private function _restrict_write_access(){
		switch ($this->auth->user->department) {
			case 'headcoach':
				$this->read_only = TRUE;
				break;
			case 'coaching':
				$this->read_only = TRUE;
				break;
			case 'fulltimecoach':
				$this->read_only = TRUE;
				break;
		}
	}

	private function _get_selected_resource($resourceID){
		$resource =NULL;
		foreach($this->resources as $key => $value){
			if($key == $resourceID){
				$resource =  $value;
				break;
			}
		 }
		 return $resource;
	}

	private function _get_attachment_info($attachmentID){
		$attachment_info = new stdClass;
		// check if numeric
		if (!ctype_digit($attachmentID)) {
			show_404();
		}

		// if so, check user exists
		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('files')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;
		 }
		 return $attachment_info;
	}

	private function _get_files($resourceID , $search_where){
		// set where
		$where = array(
			'files.accountID' => $this->auth->user->accountID,
			'settings_resourcefile_map.resourceID' => $resourceID
		);

		$res = $this->db->select('files.*, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('files_brands') . '.brandID SEPARATOR \',\') AS brands')
		->from('files')
		->join('files_brands', 'files.attachmentID = files_brands.attachmentID', 'left')
		->join('settings_resourcefile_map', 'files.attachmentID = settings_resourcefile_map.attachmentID', 'inner')
		->where($where)
		->where($search_where, NULL, FALSE)
		->order_by('files.name asc')
		->group_by('files.attachmentID')
		->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->select('files.*, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('files_brands') . '.brandID SEPARATOR \',\') AS brands')
		->from('files')
		->join('files_brands', 'files.attachmentID = files_brands.attachmentID', 'left')
		->join('settings_resourcefile_map', 'files.attachmentID = settings_resourcefile_map.attachmentID', 'inner')
		->where($where)
		->where($search_where, NULL, FALSE)
		->order_by('files.name asc')
		->group_by('files.attachmentID')
		->limit($this->pagination_library->amount, $this->pagination_library->start)
		->get();

		return $res;
	}

	private function _update_resource_mapping($attachmentID, $resourceID){
		$where = array(
			'attachmentID' => $attachmentID
		);
		$query = $this->db->delete('settings_resourcefile_map', $where);
		$reourceData = array(
			'resourceID'=> $resourceID,
			'attachmentID' => $attachmentID,
			'added' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$query = $this->db->insert('settings_resourcefile_map', $reourceData);
	}

	public function file_check($str){

        if(!isset($_FILES['file']['name']) || $_FILES['file']['name']==""){
			$this->form_validation->set_message('file_check', 'Please choose a file to upload.');
            return FALSE;
        }
		return TRUE;
	}

	private function no_categories() {
		// set defaults
		$icon = 'folder';
		$current_page = 'resources';
		$section = 'resources';
		$title = 'Resources';
		$breadcrumb_levels = array(
			'resources' => 'Resources'
		);

		$error = 'There are no relevant resources for your permission level.';
		if ($this->auth->has_features('settings') && in_array($this->auth->user->department, array('directors', 'management'))) {
			$error = 'To use resources, please ' . anchor('settings/resources', ' add a category') . ' first';
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'breadcrumb_levels' => $breadcrumb_levels,
			'error' => $error
		);

		// load view
		$this->crm_view('resources/no-categories', $data);
	}
}

/* End of file resources.php */
/* Location: ./application/controllers/resources.php */
