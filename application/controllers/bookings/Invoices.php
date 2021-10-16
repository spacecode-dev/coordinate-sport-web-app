<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invoices extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * show list of invoices
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

		if ($booking_info->type != 'booking') {
			show_404();
		}

		// set defaults
		$icon = 'sack-dollar';
		$tab = 'invoices';
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
		$page_base = 'bookings/finances/invoices/' . $bookingID;
		$section = 'bookings';
		$title = 'Invoices';
		$buttons = '<a class="btn btn-success" href="' . site_url('bookings/finances/invoices/' . $bookingID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'note' => NULL,
			'type' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_note', 'Note', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['type'] = set_value('search_type');
			$search_fields['note'] = set_value('search_note');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-bookings-invoices'))) {

			foreach ($this->session->userdata('search-bookings-invoices') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-bookings-invoices', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`invoiceDate` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`invoiceDate` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['type'] != '') {
				$search_where[] = "`type` = " . $this->db->escape($search_fields['type']);
			}

			if ($search_fields['note'] != '') {
				$search_where[] = "`note` LIKE '%" . $this->db->escape_like_str($search_fields['note']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('bookings_invoices')->where($where)->where($search_where, NULL, FALSE)->order_by('invoiceDate asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('bookings_invoices')->where($where)->where($search_where, NULL, FALSE)->order_by('invoiceDate asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get blocks
		$blocks = array();
		$where = array(
			'bookings_invoices.bookingID' => $bookingID
		);
		$res_invoices = $this->db->select('bookings_invoices.invoiceID, bookings_blocks.*, orgs.name as org_name')->from('bookings_invoices')->join('bookings_invoices_blocks', 'bookings_invoices.invoiceID = bookings_invoices_blocks.invoiceID', 'inner')->join('bookings_blocks', 'bookings_invoices_blocks.blockID = bookings_blocks.blockID', 'inner')->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')->group_by('bookings_invoices_blocks.blockID')->where($where)->get();
		if ($res_invoices->num_rows() > 0) {
			foreach($res_invoices->result() as $row) {
				$blocks[$row->invoiceID][$row->blockID] = $row->name;
				if (!empty($row->org_name)) {
					$blocks[$row->invoiceID][$row->blockID] .= ' (' . $row->org_name . ')';
				}
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'invoices' => $res,
			'bookingID' => $bookingID,
			'type' => $booking_info->type,
			'booking_info' => $booking_info,
			'blocks' => $blocks,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/invoices', $data);
	}

	/**
	 * edit a invoice
	 * @param  int $invoiceID
	 * @param int $bookingID
	 * @return void
	 */
	public function edit($invoiceID = NULL, $bookingID = NULL)
	{

		$invoice_info = new stdClass();

		// check if editing
		if ($invoiceID != NULL) {

			// check if numeric
			if (!ctype_digit($invoiceID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'invoiceID' => $invoiceID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('bookings_invoices')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$invoice_info = $row;
				$bookingID = $invoice_info->bookingID;
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
		$query = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$booking_info = $row;
		}

		if ($booking_info->type != 'booking') {
			show_404();
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Invoice';
		if ($invoiceID != NULL) {
			$submit_to = 'bookings/finances/invoices/edit/' . $invoiceID;
			$title = mysql_to_uk_date($invoice_info->invoiceDate);
		} else {
			$submit_to = 'bookings/finances/invoices/' . $bookingID . '/new/';
		}
		$return_to = 'bookings/finances/invoices/' . $bookingID;
		$icon = 'sack-dollar';
		$tab = 'invoices';
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
		$breadcrumb_levels['bookings/finances/invoices/' . $bookingID] = 'Invoices';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// get list of blocks
		$blocks = array();
		$where = array(
			'bookings_blocks.bookingID' => $bookingID,
			'bookings_blocks.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('bookings_blocks.*, COUNT(DISTINCT ' . $this->db->dbprefix('bookings_lessons') . '.typeID) as type_count, orgs.name as org_name')->from('bookings_blocks')->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID', 'left')->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')->where($where)->order_by('bookings_blocks.startDate asc, bookings_blocks.endDate asc, bookings_blocks.name asc')->group_by('bookings_blocks.blockID')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// get block name
				$block_desc = $row->name;
				if (!empty($row->org_name)) {
					$block_desc .= ' (' . $row->org_name . ')';
				}
				$block_desc .= ' - ' . mysql_to_uk_date($row->startDate);
				if (strtotime($row->endDate) > strtotime($row->startDate)) {
					$block_desc .= ' to ' . mysql_to_uk_date($row->endDate);
				}
				$blocks[$row->blockID] = array(
					'label' => $block_desc,
					'type_count' => $row->type_count
				);
			}
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('invoiceNumber', 'Invoice Number', 'trim|xss_clean|required');
			$this->form_validation->set_rules('invoiceDate', 'Invoice Date', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			$this->form_validation->set_rules('blocks', 'Block(s)', 'callback_required_if_type[' . $this->input->post('type') . ']');
			$this->form_validation->set_rules('desc', 'Description', 'trim|xss_clean');
			$this->form_validation->set_rules('amount', 'Amount', 'trim|xss_clean|required|is_numeric');
			$this->form_validation->set_rules('note', 'Note', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'invoiceNumber' => set_value('invoiceNumber'),
					'invoiceDate' => uk_to_mysql_date(set_value('invoiceDate')),
					'type' => set_value('type'),
					'desc' => set_value('desc'),
					'amount' => set_value('amount'),
					'note' => set_value('note'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($invoiceID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['bookingID'] = $bookingID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($invoiceID == NULL) {
						// insert
						$query = $this->db->insert('bookings_invoices', $data);

						$invoiceID = $this->db->insert_id();

					} else {
						$where = array(
							'invoiceID' => $invoiceID
						);

						// update
						$query = $this->db->update('bookings_invoices', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// add/update blocks
						switch (set_value('type')) {
							case 'booking':
								// if booking, look up blocks
								$blocks_posted = array();
								$where = array(
									'bookingID' => $bookingID,
									'accountID' => $this->auth->user->accountID
								);
								$res_blocks = $this->db->from('bookings_blocks')->where($where)->get();
								if ($res_blocks->num_rows() > 0) {
									foreach ($res_blocks->result() as $row) {
										$blocks_posted[] = $row->blockID;
									}
								}
								break;
							case 'blocks':
							case 'contract pricing':
							case 'participants per block':
							case 'participants per session':
							case 'other':
								$blocks_posted = $this->input->post('blocks');
								break;
						}

						// if none, set default array
						if (!is_array($blocks_posted)) {
							$blocks_posted = array();
						}
						foreach ($blocks as $blockID => $block) {
							$where = array(
								'invoiceID' => $invoiceID,
								'blockID' => $blockID,
								'accountID' => $this->auth->user->accountID
							);
							if (!in_array($blockID, $blocks_posted)) {
								// not set, remove
								$this->db->delete('bookings_invoices_blocks', $where);
							} else {
								// look up, see if site record already exists
								$res = $this->db->from('bookings_invoices_blocks')->where($where)->get();

								$data = array(
									'invoiceID' => $invoiceID,
									'blockID' => $blockID,
									'accountID' => $this->auth->user->accountID
								);

								if ($res->num_rows() == 0) {
									$this->db->insert('bookings_invoices_blocks', $data);
								}
							}
						}

						if ($bookingID == NULL) {
							$this->session->set_flashdata('success', set_value('invoiceDate') . ' Invoice has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('invoiceDate') . ' Invoice has been updated successfully.');
						}

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

		// look up org
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('orgs')->where($where)->limit(1)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $org_info) {}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// blocks
		$blocks_array = array();
		$where = array(
			'invoiceID' => $invoiceID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_invoices_blocks')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$blocks_array[] = $row->blockID;
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'invoice_info' => $invoice_info,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'org_info' => $org_info,
			'type' => $booking_info->type,
			'blocks' => $blocks,
			'blocks_array' => $blocks_array,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/invoice', $data);
	}

	/**
	 * delete a invoice
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function remove($invoiceID = NULL) {

		// check params
		if (empty($invoiceID)) {
			show_404();
		}

		$where = array(
			'invoiceID' => $invoiceID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_invoices')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$invoice_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings_invoices', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', mysql_to_uk_date($invoice_info->invoiceDate) . ' Invoice has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', mysql_to_uk_date($invoice_info->invoiceDate) . ' Invoice could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/finances/invoices/' . $invoice_info->bookingID;

			redirect($redirect_to);
		}
	}

	/**
	 * calculate an invoice
	 * @return mixed
	 */
	public function calc() {

		$json = array(
			'info' => NULL,
			'amount' => 0
		);

		$bookingID = $this->input->post('bookingID');
		$type = $this->input->post('type');
		$blocks = $this->input->post('blocks');

		if (!is_array($blocks)) {
			$blocks = array();
		}

		// check params
		if (empty($bookingID) || !ctype_digit($bookingID) || empty($type) || (in_array($type, array('blocks', 'participants per block', 'participants per session')) && count($blocks) == 0) || $type == 'other') {
			header("Content-type:application/json");
			$json['info'] = 'ERROR';
			echo json_encode($json);
			return TRUE;
		}

		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			header("Content-type:application/json");
			$json['info'] = 'ERROR';
			echo json_encode($json);
			return TRUE;
		}

		foreach ($query->result() as $row) {
			$booking_info = $row;
		}

		// get cancellations
		$booking_cancellations = array();
		$where = array(
			'bookingID' => $bookingID,
			'type' => 'cancellation',
			'accountID' => $this->auth->user->accountID
		);
		$res_cancellations = $this->db->from('bookings_lessons_exceptions')->where($where)->get();
		if ($res_cancellations->num_rows() > 0) {
			foreach($res_cancellations->result() as $row) {
				$booking_cancellations[$row->date][$row->lessonID] = $row->exceptionID;
			}
		}

		// get booking pricing
		$prices = array();
		$prices_contract = array();
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_pricing')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$prices[$row->typeID] = $row->amount;
				if ($row->contract == 1) {
					$prices_contract[$row->typeID] = 1;
				}
			}
		}

		// get list of session types
		$lesson_types = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_types[$row->typeID] = $row->name;
			}
		}

		switch ($type) {
			case 'booking':
			case 'contract pricing':
				$where = array(
					'bookings_blocks.bookingID' => $bookingID,
					'bookings_blocks.accountID' => $this->auth->user->accountID
				);
				//  get sessions from all blocks
				$query = $this->db->select('bookings_lessons.*, bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd')->from('bookings_blocks')->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')->group_by('bookings_lessons.lessonID')->where($where)->get();
				break;
			case 'blocks':
			case 'participants per session':
				// get sessions from selected blocks
				$where = array(
					'bookings_lessons.accountID' => $this->auth->user->accountID
				);
				$query = $this->db->select('bookings_lessons.*, bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd')->from('bookings_blocks')->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')->group_by('bookings_lessons.lessonID')->where($where)->where_in('bookings_blocks.blockID', $blocks)->get();
				break;
			case 'participants per block':
				// get selected blocks
				$where = array(
					'bookings_lessons.accountID' => $this->auth->user->accountID
				);
				$query = $this->db->select('bookings_lessons.*, bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd')->from('bookings_blocks')->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')->group_by('bookings_lessons.blockID')->where($where)->where_in('bookings_blocks.blockID', $blocks)->get();
				break;
		}

		$table_data = array();

		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				// if participant per block
				if ($type == 'participants per block') {
					switch($booking_info->register_type) {
						case 'numbers':
							// numbers only register
							$where = array(
								'bookings_attendance_numbers.blockID' => $row->blockID,
								'bookings_attendance_numbers.accountID' => $this->auth->user->accountID
							);
							$res_check = $this->db->select('SUM(' . $this->db->dbprefix('bookings_attendance_numbers') . '.attended) as participants')->from('bookings_attendance_numbers')->where($where)->group_by('bookings_attendance_numbers.blockID')->get();

							if ($res_check->num_rows() > 0) {
								foreach ($res_check->result() as $check_info) {
									if (!array_key_exists($row->typeID, $prices_contract)) {
										if (array_key_exists($row->typeID, $prices) && floatval($prices[$row->typeID]) > 0) {
											for ($i=0; $i < $check_info->participants; $i++) {
												$table_data[$row->typeID][$prices[$row->typeID]][] = $row->blockID;
											}
										}
									}
								}
							}
							break;
						case 'names':
						case 'bikeability':
						case 'shapeup':
							// names only register
							$where = array(
								'bookings_attendance_names.blockID' => $row->blockID,
								'bookings_attendance_names.accountID' => $this->auth->user->accountID
							);
							$res_check = $this->db->select('COUNT(DISTINCT ' . $this->db->dbprefix('bookings_attendance_names') . '.participantID) as participants')->from('bookings_attendance_names')->where($where)->group_by('bookings_attendance_names.blockID')->get();

							if ($res_check->num_rows() > 0) {
								foreach ($res_check->result() as $check_info) {
									if (!array_key_exists($row->typeID, $prices_contract)) {
										if (array_key_exists($row->typeID, $prices) && floatval($prices[$row->typeID]) > 0) {
											for ($i=0; $i < $check_info->participants; $i++) {
												$table_data[$row->typeID][$prices[$row->typeID]][] = $row->blockID;
											}
										}
									}
								}
							}
							break;
						default:
							// children and individuals
							if ($booking_info->type == 'booking') {
								switch ($booking_info->register_type) {
									case 'children':
										$where = array(
											'bookings_lessons.blockID' => $row->blockID,
											'bookings_cart_sessions.accountID' => $this->auth->user->accountID,
											'bookings_cart.type' => 'booking'
										);
										$res_check = $this->db->select('COUNT(DISTINCT ' . $this->db->dbprefix('bookings_cart_sessions') . '.childID) as participants')
										->from('bookings_cart_sessions')
										->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
										->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'left')
										->where($where)
										->group_by('bookings_lessons.blockID')
										->get();
										break;
									case 'individuals':
										$where = array(
											'bookings_lessons.blockID' => $row->blockID,
											'bookings_cart_sessions.accountID' => $this->auth->user->accountID,
											'bookings_cart.type' => 'booking'
										);
										$res_check = $this->db->select('COUNT(DISTINCT ' . $this->db->dbprefix('bookings_cart_sessions') . '.contactID) as participants')
										->from('bookings_cart_sessions')
										->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
										->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'left')
										->where($where)
										->group_by('bookings_lessons.blockID')
										->get();
										break;
								}

								if ($res_check->num_rows() > 0) {
									foreach ($res_check->result() as $check_info) {
										if (!array_key_exists($row->typeID, $prices_contract)) {
											if (array_key_exists($row->typeID, $prices) && floatval($prices[$row->typeID]) > 0) {
												for ($i=0; $i < $check_info->participants; $i++) {
													$table_data[$row->typeID][$prices[$row->typeID]][] = $row->blockID;
												}
											}
										}
									}
								}
							} else {
								// no invoicing for events
							}
							break;
					}
					// skip remaining foreach
					continue;
				}

				// switch start and end dates depending on if session has them, else default to block
				if (!empty($row->startDate) && !empty($row->endDate)) {
					$date = $row->startDate;
					$end_date = $row->endDate;
				} else {
					$date = $row->blockStart;
					$end_date = $row->blockEnd;
				}

				// loop through dates to see how many times day occurs
				while (strtotime($date) <= strtotime($end_date)) {
					if (strtolower(date('l', strtotime($date))) == $row->day) {
						// check if cancelled
						if (!array_key_exists($date, $booking_cancellations) || !array_key_exists($row->lessonID, $booking_cancellations[$date])) {
							if ($type == 'contract pricing') {
								if (array_key_exists($row->typeID, $prices_contract)) {
									if (array_key_exists($row->typeID, $prices) && floatval($prices[$row->typeID]) > 0) {
										$table_data[$row->typeID][$prices[$row->typeID]]['contract'] = $row->lessonID;
									}
								}
							} else {
								// charge once per lesson
								$multiplier = 1;
								// if participants, charge per participant
								if ($type == 'participants per session') {
									switch($booking_info->register_type) {
										case 'numbers':
											// numbers only register
											$where = array(
												'bookings_attendance_numbers.lessonID' => $row->lessonID,
												'bookings_attendance_numbers.date' => $date,
												'bookings_attendance_numbers.accountID' => $this->auth->user->accountID
											);
											$res_check = $this->db->select('SUM(' . $this->db->dbprefix('bookings_attendance_numbers') . '.attended) as participants')->from('bookings_attendance_numbers')->where($where)->group_by('bookings_attendance_numbers.lessonID')->get();

											// assume no participants on this date/session
											$multiplier = 0;

											if ($res_check->num_rows() > 0) {
												foreach ($res_check->result() as $check_info) {
													$multiplier = $check_info->participants;
												}
											}
											break;
										case 'names':
										case 'bikeability':
										case 'shapeup':
											// names only register
											$where = array(
												'bookings_attendance_names_sessions.lessonID' => $row->lessonID,
												'bookings_attendance_names_sessions.date' => $date,
												'bookings_attendance_names_sessions.accountID' => $this->auth->user->accountID
											);
											$res_check = $this->db->select('COUNT(' . $this->db->dbprefix('bookings_attendance_names_sessions') . '.participantID) as participants')->from('bookings_attendance_names_sessions')->where($where)->group_by('bookings_attendance_names_sessions.lessonID')->get();

											// assume no participants on this date/session
											$multiplier = 0;

											if ($res_check->num_rows() > 0) {
												foreach ($res_check->result() as $check_info) {
													$multiplier = $check_info->participants;
												}
											}
											break;
										default:
											// children and individuals
											if ($booking_info->type == 'booking') {
												$where = array(
													'bookings_cart_sessions.lessonID' => $row->lessonID,
													'bookings_cart_sessions.date' => $date,
													'bookings_cart_sessions.accountID' => $this->auth->user->accountID,
													'bookings_cart.type' => 'booking'
												);
												$res_check = $this->db->select('COUNT(' . $this->db->dbprefix('bookings_cart_sessions') . '.sessionID) as participants')
												->from('bookings_cart_sessions')
												->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
												->where($where)
												->group_by('bookings_cart_sessions.lessonID')
												->get();

												// assume no participants on this date/session
												$multiplier = 0;

												if ($res_check->num_rows() > 0) {
													foreach ($res_check->result() as $check_info) {
														$multiplier = $check_info->participants;
													}
												}
											} else {
												// no invoicing for events
												$multiplier = 0;
											}
											break;
									}
								}
								switch ($row->charge) {
									case 'default':
										if (!array_key_exists($row->typeID, $prices_contract)) {
											if (array_key_exists($row->typeID, $prices) && floatval($prices[$row->typeID]) > 0) {
												for ($i=0; $i < $multiplier; $i++) {
													$table_data[$row->typeID][$prices[$row->typeID]][] = $row->lessonID;
												}
											}
										}
										break;
									case 'other':
										if (!array_key_exists($row->typeID, $prices_contract)) {
											if (floatval($row->charge_other) > 0) {
												for ($i=0; $i < $multiplier; $i++) {
													$table_data[$row->typeID][$row->charge_other][] = $row->lessonID;
												}
											}
										}
										break;
								}
							}
						}
					}
					$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
				}
			}
		}

		if (count($table_data) > 0) {
			$json['info'] .= '<label>Calculation</label><table class="table table-striped table-bordered"><thead><tr><th>Item</th><th>Sub Total</th></tr></thead><tbody>';
			foreach ($table_data as $typeID => $amounts) {
				foreach ($amounts as $amount => $count) {
					$lesson_desc = 'Unknown';
					if (array_key_exists($typeID, $lesson_types)) {
						$lesson_desc = $lesson_types[$typeID];
					}
					$json['amount'] += $amount*count($count);
					if (array_key_exists('contract', $count)) {
						$json['info'] .= '<tr><td>' . $lesson_desc . '</td><td>' . currency_symbol() . number_format($amount*count($count),2) . '</td></tr>';
					} else {
						$json['info'] .= '<tr><td>' . count($count) . ' x ' . $lesson_desc . ' @ ' . currency_symbol() . number_format($amount, 2) . '</td><td>' . currency_symbol() . number_format($amount*count($count),2) . '</td></tr>';
					}
				}
			}
			$json['info'] .= '<tr><td>Total</td><td>' . currency_symbol() . number_format($json['amount'],2) . '</td></tr>';
			$json['info'] .= '</tbody></table>';
		}

		$json['amount'] = number_format($json['amount'], 2, '.', '');

		header("Content-type:application/json");
		echo json_encode($json);
		return TRUE;

	}

	/**
	 * invoice a invoice
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function invoice($invoiceID = NULL) {

		// check params
		if (empty($invoiceID)) {
			show_404();
		}

		$where = array(
			'invoiceID' => $invoiceID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_invoices')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$invoice_info = $row;

			// all ok, update
			$data= array(
				'is_invoiced' => 1,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('bookings_invoices', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', mysql_to_uk_date($invoice_info->invoiceDate) . ' Invoice has been marked as invoiced.');
			} else {
				$this->session->set_flashdata('error', mysql_to_uk_date($invoice_info->invoiceDate) . ' Invoice could not be marked as invoiced.');
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/finances/invoices/' . $invoice_info->bookingID;

			redirect($redirect_to);
		}
	}

	/**
	 * uninvoice a invoice
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function uninvoice($invoiceID = NULL) {

		// check params
		if (empty($invoiceID)) {
			show_404();
		}

		$where = array(
			'invoiceID' => $invoiceID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings_invoices')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$invoice_info = $row;

			// all ok, update
			$data= array(
				'is_invoiced' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('bookings_invoices', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', mysql_to_uk_date($invoice_info->invoiceDate) . ' Invoice has been marked as not invoiced.');
			} else {
				$this->session->set_flashdata('error', mysql_to_uk_date($invoice_info->invoiceDate) . ' Invoice could not be marked as not invoiced.');
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/finances/invoices/' . $invoice_info->bookingID;

			redirect($redirect_to);
		}
	}

	/**
	 * check if fields required because of specific type
	 * @return boolean
	 */
	public function required_if_type($var, $type) {

		// get form post as it's array
		$var = $this->input->post('blocks');

		if (in_array($type, array('blocks', 'contract pricing', 'other', 'participants')) && empty($var)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check date is correct
	 * @param  string $date
	 * @return bool
	 */
	public function check_date($date) {

		// date not required
		if (empty($date)) {
			return TRUE;
		}

		// if set, check
		if (check_uk_date($date)) {
			return TRUE;
		}

		return FALSE;

	}

}

/* End of file invoices.php */
/* Location: ./application/controllers/bookings/invoices.php */
