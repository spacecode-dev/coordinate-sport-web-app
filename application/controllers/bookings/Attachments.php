<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attachments extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * show list of files
	 * @return void
	 */
	public function index($bookingID = NULL) {

		if ($bookingID == NULL) {
			show_404();
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$res = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		if ($booking_info->type != 'event' && $booking_info->project != 1) {
			show_404();
		}

		// set defaults
		$icon = 'paperclip';
		$tab = 'attachments';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$section = 'bookings';
		$page_base = 'bookings/attachments/' . $bookingID;
		$add_url = 'bookings/attachments/' . $bookingID . '/new';
		$title = 'Attachments';
		$buttons = '<a class="btn btn-success" href="' . site_url($add_url) . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$attach_all_blocks = array();

		// set where
		$where = array(
			'bookings_attachments.bookingID' => $bookingID,
			'bookings_attachments.accountID' => $this->auth->user->accountID,
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

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-booking-attachments'))) {

			foreach ($this->session->userdata('search-booking-attachments') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-booking-attachments', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = $this->db->dbprefix('bookings_attachments') . ".`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['comment'] != '') {
				$search_where[] = $this->db->dbprefix('bookings_attachments') . ".`comment` LIKE '%" . $this->db->escape_like_str($search_fields['comment']) . "%'";
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->from('bookings_attachments')->where($where)->where($search_where, NULL, FALSE)->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->select('bookings_attachments.*, GROUP_CONCAT(' . $this->db->dbprefix('bookings_attachments_blocks') . '.blockID) AS blocks')
		->from('bookings_attachments')
		->join('bookings_attachments_blocks', 'bookings_attachments.attachmentID = bookings_attachments_blocks.attachmentID', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->order_by('bookings_attachments.name asc')
		->group_by('bookings_attachments.attachmentID')
		->limit($this->pagination_library->amount, $this->pagination_library->start)
		->get();

		// get list of blocks
		$where = array(
			'bookings_blocks.accountID' => $this->auth->user->accountID,
			'bookings_blocks.bookingID' => $bookingID
		);
		$resBlocks = $this->db->select('bookings_blocks.*, orgs.name as org, bookings.orgID as booking_orgID')
			->from('bookings_blocks')
			->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
			->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')
			->where($where)
			->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate asc, bookings_blocks.name asc')
			->get();

		// blocks
		foreach($res->result() as $row) {
			$flag = 1;
			$attachmentID = $row->attachmentID;
			$block_list_array = array();
			$where = array(
				'bookings_attachments.attachmentID' => $attachmentID,
				'bookings_attachments.accountID' => $this->auth->user->accountID
			);
			$resBookingBlocks = $this->db->select('bookings_attachments_blocks.blockID')->from('bookings_attachments')->join('bookings_attachments_blocks', 'bookings_attachments.attachmentID = bookings_attachments_blocks.attachmentID', 'inner')->where($where)->get();
			if ($resBookingBlocks->num_rows() > 0) {
				foreach ($resBookingBlocks->result() as $row) {
					$block_list_array[] = $row->blockID;
				}
			}else{
				$attach_all_blocks[$attachmentID] = 0;
			}
			foreach ($resBlocks->result() as $row) {
				if (!in_array($row->blockID, $block_list_array)) {
					$flag = 0;
				}
			}
			$attach_all_blocks[$attachmentID] = $flag;
		}

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
			'bookingID' => $bookingID,
			'booking_type' => $booking_info->type,
			'booking_info' => $booking_info,
			'tab' => $tab,
			'breadcrumb_levels' => $breadcrumb_levels,
			'attach_all_blocks' => $attach_all_blocks,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/attachments', $data);
	}

	/**
	 * edit a file
	 * @param  int $attachmentID
	 * @param int $bookingID
	 * @return void
	 */
	public function edit($attachmentID = NULL, $bookingID = NULL)
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
			$query = $this->db->from('bookings_attachments')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$attachment_info = $row;
				$bookingID = $attachment_info->bookingID;
			}

		}

		// required
		if ($bookingID == NULL) {
			show_404();
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$res = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		if ($booking_info->type != 'event' && $booking_info->project != 1) {
			show_404();
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New File';
		$submit_to = 'bookings/attachments/' . $bookingID . '/new';
		$return_to = 'bookings/attachments/' . $bookingID;
		if ($attachmentID != NULL) {
			$title = $attachment_info->name;
			$submit_to = 'bookings/attachments/edit/' . $attachmentID;
		}
		$icon = 'paperclip';
		$tab = 'attachments';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$breadcrumb_levels['bookings/attachments/' . $bookingID] = 'Attachments';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// get list of blocks
		$where = array(
			'bookings_blocks.accountID' => $this->auth->user->accountID,
			'bookings_blocks.bookingID' => $bookingID
		);
		$res = $this->db->select('bookings_blocks.*, orgs.name as org, bookings.orgID as booking_orgID')
		->from('bookings_blocks')
		->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
		->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')
		->where($where)
		->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate asc, bookings_blocks.name asc')
		->get();
		$block_list = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$block_list[$row->blockID] = $row->name . ' - ' . mysql_to_uk_date($row->startDate);
				if ($row->endDate != $row->startDate) {
					$block_list[$row->blockID] .= ' - ' . mysql_to_uk_date($row->endDate);
				}
				if (!empty($row->orgID) && $row->orgID != $row->booking_orgID) {
					$block_list[$row->blockID] .= ' (' . $row->org . ')';
				}
			}
		}

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
					$data['bookingID'] = $bookingID;
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

								if ($attachmentID !== NULL) {
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

						if ($attachmentID !== NULL) {
							// delete previous file, if exists
							$path = UPLOADPATH;
							if (isset($attachment_info->path) && file_exists($path . $attachment_info->path)) {
								unlink($path . $attachment_info->path);
							}
						}
					}
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($attachmentID == NULL) {
						// insert id
						$query = $this->db->insert('bookings_attachments', $data);
						$attachmentID = $this->db->insert_id();
						$just_added = TRUE;
					} else {
						$where = array(
							'attachmentID' => $attachmentID
						);

						// update
						$query = $this->db->update('bookings_attachments', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						$attachment_name = NULL;
						if (isset($data['name'])) {
							$attachment_name = $data['name'];
						} else if (isset($attachment_info->name)) {
							$attachment_name = $attachment_info->name;
						}

						// add/update block associations
						$block_list_posted = $this->input->post('block_list');
						if (!is_array($block_list_posted)) {
							$block_list_posted = array();
						}
						foreach ($block_list as $blockID => $lesson) {
							$where = array(
								'attachmentID' => $attachmentID,
								'blockID' => $blockID,
								'accountID' => $this->auth->user->accountID
							);
							if (!in_array($blockID, $block_list_posted)) {
								// not set, remove
								$this->db->delete('bookings_attachments_blocks', $where);
							} else {
								// look up, see if site record already exists
								$res = $this->db->from('bookings_attachments_blocks')->where($where)->get();

								$data = $where;

								if ($res->num_rows() > 0) {
									$this->db->update('bookings_attachments_blocks', $data, $where);
								} else {
									$this->db->insert('bookings_attachments_blocks', $data);
								}
							}
						}

						if (isset($just_added)) {
							$this->session->set_flashdata('success', $attachment_name . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', $attachment_name . ' has been updated successfully.');
						}

						redirect('bookings/attachments/' . $bookingID);

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
			'settings_resources.booking_attachments' => 1
		);
		$templates = $this->db->select('files.*')
			->from('files')
			->join('settings_resourcefile_map', 'files.attachmentID = settings_resourcefile_map.attachmentID', 'inner')
			->join('settings_resources', 'settings_resourcefile_map.resourceID = settings_resources.resourceID', 'inner')
			->where($where)
			->order_by('files.name asc')
			->group_by('files.attachmentID')
			->get();

		// blocks
		$block_list_array = array();
		$where = array(
			'bookings_attachments.attachmentID' => $attachmentID,
			'bookings_attachments.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('bookings_attachments_blocks.blockID')->from('bookings_attachments')->join('bookings_attachments_blocks', 'bookings_attachments.attachmentID = bookings_attachments_blocks.attachmentID', 'inner')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$block_list_array[] = $row->blockID;
			}
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
			'booking_type' => $booking_info->type,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'tab' => $tab,
			'templates' => $templates,
			'breadcrumb_levels' => $breadcrumb_levels,
			'block_list' => $block_list,
			'block_list_array' => $block_list_array,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/attachment', $data);
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
		$query = $this->db->from('bookings_attachments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings_attachments', $where);

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
			$redirect_to = 'bookings/attachments/' . $attachment_info->bookingID;

			redirect($redirect_to);
		}
	}

	/**
	 * attach all blocks to attachment
	 * @param  int $attachmentID
	 * @param string $value
	 * @return mixed
	 */
	public function attachallblocks($bookingID = NULL, $attachmentID = NULL, $value = NULL) {

		// check params
		if (empty($bookingID) || empty($attachmentID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_attachments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		$flag = 1;
		foreach ($query->result() as $row) {

			// get list of blocks
			$where = array(
				'bookings_blocks.accountID' => $this->auth->user->accountID,
				'bookings_blocks.bookingID' => $bookingID
			);
			$res = $this->db->select('bookings_blocks.*, orgs.name as org, bookings.orgID as booking_orgID')
				->from('bookings_blocks')
				->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
				->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')
				->where($where)
				->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate asc, bookings_blocks.name asc')
				->get();
			$block_list = array();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$where = array(
						'attachmentID' => $attachmentID,
						'blockID' => $row->blockID,
						'accountID' => $this->auth->user->accountID
					);
					if ($value == 'no') {
						// not set, remove
						$this->db->delete('bookings_attachments_blocks', $where);
					} else {
						// look up, see if site record already exists
						$res = $this->db->from('bookings_attachments_blocks')->where($where)->get();

						$data = $where;

						if ($res->num_rows() > 0) {
							$this->db->update('bookings_attachments_blocks', $data, $where);
						} else {
							$this->db->insert('bookings_attachments_blocks', $data);
						}
					}
				}
			}
		}
		echo 'OK';
		exit();
	}

	/**
	 * show on bookings site
	 * @param  int $attachmentID
	 * @param string $value
	 * @return mixed
	 */
	public function showonbookingssite($attachmentID = NULL, $value = NULL) {

		// check params
		if (empty($attachmentID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_attachments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;

			$data = array(
				'showonbookingssite' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['showonbookingssite'] = 1;
			}

			// run query
			$query = $this->db->update('bookings_attachments', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

	/**
	 * send with thanks email
	 * @param  int $attachmentID
	 * @param string $value
	 * @return mixed
	 */
	public function sendwiththanks($attachmentID = NULL, $value = NULL) {

		// check params
		if (empty($attachmentID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'attachmentID' => $attachmentID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_attachments')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$attachment_info = $row;

			$data = array(
				'sendwiththanks' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['sendwiththanks'] = 1;
			}

			// run query
			$query = $this->db->update('bookings_attachments', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

}

/* End of file attachments.php */
/* Location: ./application/controllers/bookings/attachments.php */
