<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attachments extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach'), array(), array('staff_management'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}

        $this->load->library('qualifications_library');
	}

	/**
	 * show list of files
	 * @return void
	 */
	public function index($staffID = NULL) {

		if ($staffID == NULL) {
			show_404();
		}

		// look up staff
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('staff')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}


		foreach ($res->result() as $row) {
			$staff_info = $row;
		}

		// set defaults
		$icon = 'paperclip';
		$current_page = 'staff';
		$section = 'staff';
		$tab = 'attachments';
		$page_base = 'staff/attachments/' . $staffID;
		$add_url = 'staff/attachments/' . $staffID . '/new';
		$title = 'Attachments';
		$buttons = '<a class="btn btn-success" href="' . site_url($add_url) . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname
 		);

		// set where
		$where = array(
			'staff_attachments.staffID' => $staffID,
			'staff_attachments.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'comment' => NULL,
			'search' => NULL,
            'area' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_comment', 'Comment', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('search_area', 'Qualifications', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['comment'] = set_value('search_comment');
			$search_fields['search'] = set_value('search');
			$search_fields['area'] = set_value('search_area');
			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-staff-attachments'))) {

			foreach ($this->session->userdata('search-staff-attachments') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-staff-attachments', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['comment'] != '') {
				$search_where[] = "`comment` LIKE '%" . $this->db->escape_like_str($search_fields['comment']) . "%'";
			}

            if ($search_fields['area'] != '') {
                $search_where[] = "`area` = '" . $search_fields['area'] . "'";
            }
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->from('staff_attachments')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->from('staff_attachments')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$mandatoryQualifications = $this->qualifications_library->getMandatoryQuals($this->auth->user->accountID);

		$additionalQualifications = $this->qualifications_library->getAdditionalQuals($staffID, $this->auth->user->accountID);

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'files' => $res,
			'add_url' => $add_url,
			'staffID' => $staffID,
			'tab' => $tab,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
            'mandatory_qualifications' => $mandatoryQualifications,
            'additional_qualifications' => $additionalQualifications
		);

		// load view
		$this->crm_view('staff/attachments', $data);
	}

	/**
	 * edit a file
	 * @param  int $attachmentID
	 * @param int $staffID
	 * @return void
	 */
	public function edit($attachmentID = NULL, $staffID = NULL)
	{

		$attachment_info = new stdClass;

		// check if editing
		if ($attachmentID != NULL) {

			// office can't edit
			if ($this->auth->user->department == 'office') {
				show_403();
			}

			// check if numeric
			if (!ctype_digit($attachmentID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'attachmentID' => $attachmentID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('staff_attachments')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$attachment_info = $row;
				$staffID = $attachment_info->staffID;
			}

		}

		// required
		if ($staffID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New File';
		$submit_to = 'staff/attachments/' . $staffID . '/new';
		$return_to = 'staff/attachments/' . $staffID;
		if ($attachmentID != NULL) {
			$title = $attachment_info->name;
			$submit_to = 'staff/attachments/edit/' . $attachmentID;
		}
		$icon = 'paperclip';
		$tab = 'attachments';
		$current_page = 'staff';
		$section = 'staff';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
			'staff/attachments/' . $staffID => 'Attachments'
 		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('templateID', 'Template', 'trim|xss_clean');
			$this->form_validation->set_rules('comment', 'Comment', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok

				// prepare data
				$data = array(
					'comment' => set_value('comment'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($attachmentID == NULL) {

					$data['byID'] = $this->auth->user->staffID;
					$data['staffID'] = $staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// if copying template
				if (set_value('templateID') != '') {

					// look up
					$where = array(
						'attachmentID' => set_value('templateID'),
						'accountID' => $this->auth->user->accountID
					);

					$res = $this->db->from('files')->where($where)->limit(1)->get();

					if ($res->num_rows() == 0) {
						$errors[] = 'Template not found';
					} else {
						foreach ($res->result() as $row) {

							// duplicate
							$path = $this->crm_library->duplicate_upload($row->path);

							if ($path == FALSE) {
								$errors[] = 'Could not copy template';
							} else {
								$data['name'] = $row->name;
								$data['path'] = $path;
								$data['type'] = $row->type;
								$data['size'] = $row->size;
								$data['ext'] = $row->ext;

								if (!empty($attachmentID) && !empty($attachment_info->path)) {
									// delete previous file, if exists
									$path = UPLOADPATH;
									if (file_exists($path . $attachment_info->path)) {
										unlink($path . $attachment_info->path);
									}
								}
							}

						}
					}


				} else {

					$upload_res = $this->crm_library->handle_upload();

					if ($upload_res === NULL) {
						// on edit, might just be changing comment
						if (empty($attachmentID)) {
							$errors[] = 'A valid file is required';
						}
					} else {
						$data['name'] = $upload_res['client_name'];
						$data['path'] = $upload_res['raw_name'];
						$data['type'] = $upload_res['file_type'];
						$data['size'] = $upload_res['file_size']*1024;
						$data['ext'] = substr($upload_res['file_ext'], 1);

						if (!empty($attachmentID) && !empty($attachment_info->path)) {
							// delete previous file, if exists
							$path = UPLOADPATH;
							if (file_exists($path . $attachment_info->path)) {
								unlink($path . $attachment_info->path);
							}
						}
					}
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($attachmentID == NULL) {
						// insert id
						$query = $this->db->insert('staff_attachments', $data);
					} else {
						$where = array(
							'attachmentID' => $attachmentID
						);

						// update
						$query = $this->db->update('staff_attachments', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if (empty($data['name'])) {
							$data['name'] = $attachment_info->name;
						}

						if ($attachmentID == NULL) {
							$this->session->set_flashdata('success', $data['name'] . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', $data['name'] . ' has been updated successfully.');
						}

						redirect('staff/attachments/' . $staffID);

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

		// templates
		$where = array(
			'files.accountID' => $this->auth->user->accountID,
			'settings_resources.staff_attachments' => 1
		);
		$templates = $this->db->select('files.*')
			->from('files')
			->join('settings_resourcefile_map', 'files.attachmentID = settings_resourcefile_map.attachmentID', 'inner')
			->join('settings_resources', 'settings_resourcefile_map.resourceID = settings_resources.resourceID', 'inner')
			->where($where)
			->order_by('files.name asc')
			->group_by('files.attachmentID')
			->get();

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
			'staffID' => $staffID,
			'tab' => $tab,
			'templates' => $templates,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/attachment', $data);
	}

	/**
	 * delete a file
	 * @param  int $attachmentID
	 * @return mixed
	 */
	public function remove($attachmentID = NULL, $redirect = NULL) {

		// office can't delete
		if ($this->auth->user->department == 'office') {
			show_403();
		}

		// check params
		if (empty($attachmentID)) {
			show_404();
		}

		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff_attachments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;

			// all ok, delete
			$query = $this->db->delete('staff_attachments', $where);

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
            switch ($redirect) {
                case 'staff_quals':
                    $redirect = 'staff/quals/' . $attachment_info->staffID;
                    break;
                case 'quals_edit':
                    if (isset($attachment_info->belongs_to)) {
                        $redirect = 'staff/quals/edit/' . $attachment_info->belongs_to;
                    }
                    break;
                case 'additional_quals':
                    $redirect = 'staff/quals/additional/' . $attachment_info->staffID;
                    break;
                default:
                    $redirect = 'staff/attachments/' . $attachment_info->staffID;
                    break;

            }

			redirect($redirect);
		}
	}

}

/* End of file attachments.php */
/* Location: ./application/controllers/staff/attachments.php */
