<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH.'/traits/Book_trait.php';
require APPPATH.'/traits/Booking_account_trait.php';

class Book extends MY_Controller {
	use Book_trait, Booking_account_trait;

	private $fa_weight = 'far';
	private $in_crm = TRUE;
	private $cart_base = 'booking/';
	private $buttons = NULL;

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	private function prevented() {
		$title = 'Booking Cart';
		$info = 'No active cart. Select a contact to load their cart and book this event.';
		$success = NULL;
		$error = NULL;
		$errors = array();

		// load libraries
		$this->load->library('form_validation');

		// check for post
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('contactID', 'Contact', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				$blockID = $this->crm_library->last_segment();
				redirect('booking/cart/init/' . set_value('contactID') . '/' . $blockID);
			}
		}

		// get selected contact
		$contacts = [];
		if (!empty($this->input->post('contactID'))) {
			$res_contacts = $this->db->select('contactID, first_name, last_name')
				->from('family_contacts')
				->where([
					'accountID' => $this->auth->user->accountID,
					'active' => 1,
					'contactID' => $this->input->post('contactID')
				])
				->limit(1)
				->get();
			if ($res_contacts->num_rows() > 0) {
				foreach ($res_contacts->result() as $row) {
					$contacts[$row->contactID] = $row->first_name . ' ' . $row->last_name;
				}
			}
		}

		// check for flashdata
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => $title,
			'success' => $success,
			'info' => $info,
			'error' => $error,
			'errors' => $errors,
			'contacts' => $contacts
		);
		$this->crm_view('booking/select-contact', $data);
	}
}

/* End of file Book.php */
/* Location: ./application/controllers/booking/Book.php */
