<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH.'/traits/Cart_trait.php';

class Cart extends MY_Controller {
	use Cart_trait;

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

	public function init($contactID, $blockID = NULL) {
		// if was editing booking, unset
		$this->session->unset_userdata('cart_cartID');

		if ($res = $this->crm_library->init_contact_cart($contactID)) {
			// save in session
			$this->session->set_userdata('cart_contactID', $contactID);

			// tell user
			$this->session->set_flashdata('success', 'Cart loaded successfully');

			$redirect = 'booking/cart';
			if (!empty($blockID)) {
				$redirect = 'booking/book/' . $blockID;
			}

			// redirect
			redirect($redirect);
		} else {
			$data = array(
				'error' => 'Error initiating cart'
			);
			$this->load->view('blank');
		}
	}

	// edit a booking
	public function edit($cartID, $blockID = NULL) {
		// look up cart
		$where = array(
			'bookings_cart.cartID' => $cartID,
			'bookings_cart.accountID' => $this->cart_library->accountID,
			'bookings_cart.type' => 'booking'
		);
		$res = $this->db->select('bookings_cart.contactID, GROUP_CONCAT(DISTINCT sessions.blockID) as blockIDs')
		->from('bookings_cart')
			->join('bookings_cart_sessions as sessions', 'bookings_cart.cartID = sessions.cartID', 'left')
			->where($where)
		->get();
		if ($res->num_rows() == 0) {
			show_404();
		}
		foreach ($res->result() as $cart) {
			$contactID = $cart->contactID;
			$blockIDs = $cart->blockIDs;
		}

		if ($res = $this->crm_library->init_contact_cart($contactID, $cartID)) {
			// save in session
			$this->session->set_userdata('cart_contactID', $contactID);
			$this->session->set_userdata('cart_cartID', $cartID);

			// tell user
			$this->session->set_flashdata('success', 'Booking loaded successfully');

			$redirect = 'booking/cart';
			if (!empty($blockID)) {
				$redirect = 'booking/book/' . $blockID;
			}
			elseif (strpos($blockIDs,",")===false && !empty($blockIDs)) {
				//Automaticlly load block if only one is present in the booking
				$redirect = 'booking/book/' . $blockIDs;
				
			}

			// redirect
			redirect($redirect);
		} else {
			$data = array(
				'error' => 'Error initiating cart'
			);
			$this->load->view('blank');
		}
	}

	public function close($blockID = NULL) {
		// remove from session
		$this->session->unset_userdata('cart_contactID');
		$this->session->unset_userdata('cart_cartID');

		// tell user
		$this->session->set_flashdata('success', 'Cart closed successfully');

		$redirect = 'booking/cart';
		if (!empty($blockID)) {
			$redirect = 'booking/book/' . $blockID;
		}

		$query_redirect = $this->input->get('redirect');
		if (!empty($query_redirect)) {
			$redirect = parse_url($query_redirect, PHP_URL_PATH);
			$this->session->set_flashdata('success', 'Cart closed successfully');
		}

		// redirect
		redirect($redirect);
	}

	public function jump($contactID, $childID = NULL) {
		if ($res = $this->crm_library->init_contact_cart($contactID)) {
			// save in session
			$this->session->set_userdata('cart_contactID', $contactID);

			$participantID = $contactID;
			if (!empty($childID)) {
				$participantID = $childID;
			}

			$blockID = $this->input->post('blockID');
			if (!empty($blockID)) {
				redirect('booking/book/' . $blockID . '?participant=' . $participantID);
			} else {
				show_403();
			}
		} else {
			show_404();
		}
	}

	private function prevented() {
		$title = 'Booking Cart';
		$info = 'No active cart. Select a contact to load their cart.';
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
				redirect('booking/cart/init/' . set_value('contactID'));
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

/* End of file Cart.php */
/* Location: ./application/controllers/booking/Cart.php */
