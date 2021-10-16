<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attachments extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any customer types
		if (!$this->auth->has_features('customers_schools') && !$this->auth->has_features('customers_schools_prospects') && !$this->auth->has_features('customers_orgs') && !$this->auth->has_features('customers_orgs_prospects')) {
			show_403();
		}
	}

	/**
	 * show list of files
	 * @return void
	 */
	public function index($orgID = NULL) {

		if ($orgID == NULL) {
			show_404();
		}

		// look up orgs
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'paperclip';
		$tab = 'attachments';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$section = 'customers';
		$page_base = 'customers/attachments/' . $orgID;
		$add_url = 'customers/attachments/' . $orgID . '/new';
		$title = 'Attachments';
		$buttons = '<a class="btn btn-success" href="' . site_url($add_url) . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'orgs_attachments.orgID' => $orgID,
			'orgs_attachments.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'comment' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_comment', 'Comment', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['comment'] = set_value('search_comment');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-customer-attachments'))) {

			foreach ($this->session->userdata('search-customer-attachments') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-customer-attachments', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['comment'] != '') {
				$search_where[] = "`comment` LIKE '%" . $this->db->escape_like_str($search_fields['comment']) . "%'";
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->from('orgs_attachments')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->from('orgs_attachments')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'files' => $res,
			'add_url' => $add_url,
			'orgID' => $orgID,
			'org_info' => $org_info,
			'tab' => $tab,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/attachments', $data);
	}

	/**
	 * edit a file
	 * @param  int $attachmentID
	 * @param int $orgID
	 * @return void
	 */
	public function edit($attachmentID = NULL, $orgID = NULL)
	{

		$attachment_info = new stdClass;

		// check if editing
		if ($attachmentID != NULL) {

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
			$query = $this->db->from('orgs_attachments')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$attachment_info = $row;
				$orgID = $attachment_info->orgID;
			}

		}

		// required
		if ($orgID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$org_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New File';
		$submit_to = 'customers/attachments/' . $orgID . '/new';
		$return_to = 'customers/attachments/' . $orgID;
		if ($attachmentID != NULL) {
			$title = $attachment_info->name;
			$submit_to = 'customers/attachments/edit/' . $attachmentID;
		}
		$icon = 'paperclip';
		$tab = 'attachments';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$breadcrumb_levels['customers/attachments/' . $orgID] = 'Attachments';
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

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
					$data['orgID'] = $orgID;
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
						$query = $this->db->insert('orgs_attachments', $data);
					} else {
						$where = array(
							'attachmentID' => $attachmentID
						);

						// update
						$query = $this->db->update('orgs_attachments', $data, $where);
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

						redirect($return_to);

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

		$where = array(
			'files.accountID' => $this->auth->user->accountID,
			'settings_resources.customer_attachments' => 1
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
			'orgID' => $orgID,
			'tab' => $tab,
			'templates' => $templates,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/attachment', $data);
	}

	/**
	 * delete a file
	 * @param  int $attachmentID
	 * @return mixed
	 */
	public function remove($attachmentID = NULL) {

		// check params
		if (empty($attachmentID)) {
			show_404();
		}

		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_attachments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;

			// all ok, delete
			$query = $this->db->delete('orgs_attachments', $where);

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
			$redirect_to = 'customers/attachments/' . $attachment_info->orgID;

			redirect($redirect_to);
		}
	}

	/**
	 * coach access
	 * @param  int $attachmentID
	 * @param string $value
	 * @return mixed
	 */
	public function coachaccess($attachmentID = NULL, $value = NULL) {

		// check params
		if (empty($attachmentID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_attachments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;

			$data = array(
				'coachaccess' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['coachaccess'] = 1;
			}

			// run query
			$query = $this->db->update('orgs_attachments', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

	/**
	 * send with confirmation
	 * @param  int $attachmentID
	 * @param string $value
	 * @return mixed
	 */
	public function sendwithconfirmation($attachmentID = NULL, $value = NULL) {

		// check params
		if (empty($attachmentID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_attachments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;

			$data = array(
				'sendwithconfirmation' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['sendwithconfirmation'] = 1;
			}

			// run query
			$query = $this->db->update('orgs_attachments', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

}

/* End of file attachments.php */
/* Location: ./application/controllers/customers/attachments.php */
