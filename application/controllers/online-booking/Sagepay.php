<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sagepay extends Online_Booking_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function index($type = 'payment') {
		$fail_redirect = 'account/pay#details';
		if ($type == 'checkout') {
			$fail_redirect = 'checkout';
		}

		// handle response from sage
		if (!$this->settings_library->get('cc_processor', $this->cart_library->accountID)) {
			$error = 'Invalid payment gateway configuration';
		} else {
			$sagepay_environment = $this->settings_library->get('sagepay_environment', $this->cart_library->accountID);
			$sagepay_vendor = $this->settings_library->get('sagepay_vendor', $this->cart_library->accountID);
			$sagepay_encryption_password = $this->settings_library->get('sagepay_encryption_password', $this->cart_library->accountID);
			if (empty($sagepay_environment) || empty($sagepay_vendor) || empty($sagepay_encryption_password)) {
				$error = 'Invalid payment gateway configuration';
			}
			$sagepay_is_production = FALSE;
			if ($sagepay_environment == 'production') {
				$sagepay_is_production = TRUE;
			}
		}

		if (isset($error)) {
			$this->session->set_flashdata('error', $error);
			redirect($fail_redirect);
		}

		$sagePay = new \Eurolink\SagePayForm\Builder([
			'isProduction' => $sagepay_is_production,
			'encryptPassword' => $sagepay_encryption_password,
			'vendor' => $sagepay_vendor,
		]);

		$sagePay->decode($_GET['crypt']);

		// if not valid response
		if (!$sagePay->isResponseValid() || !$sagePay->isResponseStatusOK()) {
			$error = 'Payment ' . $sagePay->getResponseStatusMessage();
			$this->session->set_flashdata('error', $error);
			redirect($fail_redirect);
		}

		// get vars
		$ref = $sagePay->getResponseVendorCode();
		$ref_parts = explode("-", $ref);

		// check if ref already exists
		$where = array(
			'transaction_ref' => $ref
		);
		$res = $this->db->from('family_payments')->where($where)->limit(1)->get();
		$duplicate = FALSE;
		if ($res->num_rows() == 1) {
			$duplicate = TRUE;
		}

		// apply payment to account
		if ($duplicate === FALSE) {
			$data = array(
				'accountID' => $ref_parts[0],
				'familyID' => $ref_parts[1],
				'contactID' => $ref_parts[2],
				'amount' => $sagePay->getResponseAmount(),
				'method' => 'online',
				'transaction_ref' => $ref,
				'locked' => 1,
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->db->insert('family_payments', $data);

			// send payment email
			$paymentID = $this->db->insert_id();
			$this->crm_library->send_payment_confirmation($paymentID);
		}

		// if cart payment
		if ($type == 'checkout' && array_key_exists(4, $ref_parts)) {
			if ($duplicate === FALSE) {
				// convert to booking
				$data = array(
					'type' => 'booking',
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'booked' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$where = array(
					'cartID' => $ref_parts[4],
					'accountID' => $ref_parts[0]
				);
				$res = $this->db->update('bookings_cart', $data, $where, 1);

				// calc family balance
				$this->crm_library->recalc_family_balance($ref_parts[1]);

				// send booking email
				$this->crm_library->send_event_confirmation($ref_parts[4]);
			}

			// redirect
			$redirect = '/account/booking/' . $ref_parts[4] . '#details';

			// tell user
			$success = "Thank you for your booking. You can find your details below and we've also sent you a confirmation email with this information";
		} else {
			// normal payment
			$redirect = 'account/payments#details';

			// show success
			$success = 'Payment has been applied successfully';

			// calc family balance
			if ($duplicate === FALSE) {
				$this->crm_library->recalc_family_balance($ref_parts[1]);
			}
		}

		// calc family balance
		$this->crm_library->recalc_family_balance($ref_parts[1]);

		$this->session->set_flashdata('success', $success);
		redirect($redirect);
	}

}

/* End of file Sagepay.php */
/* Location: ./application/controllers/online-booking/Sagepay.php */
