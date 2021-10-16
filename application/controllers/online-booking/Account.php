<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH.'/traits/Booking_account_trait.php';

class Account extends Online_Booking_Controller {
	use Booking_account_trait;

	public $in_crm = FALSE;
	public $fa_weight = 'fas';

	public function __construct() {
		parent::__construct();

		// load gocardless library
		$args = array(
			'accountID' => $this->online_booking->accountID
		);
		$this->load->library('gocardless_library', $args);
	}

	public function index() {
		// check auth
		$this->online_booking->require_auth();

		// set defaults
		$title = 'Account Overview';
		$body_class = 'account';
		$tab = 'bookings';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// get bookings
		$where = array(
			'bookings_cart.accountID' => $this->online_booking->accountID,
			'bookings_cart.familyID' => $this->online_booking->user->familyID,
			'bookings_cart.type' => 'booking'
		);

		$res = $this->db->select("bookings_cart.*, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, GROUP_CONCAT(DISTINCT CONCAT(children.first_name, ' ', children.last_name)) as child_names, GROUP_CONCAT(DISTINCT CONCAT(individuals.first_name, ' ', individuals.last_name)) as individual_names")
			->from('bookings_cart')
			->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'inner')
			->join('bookings_cart_sessions as sessions_children', 'bookings_cart.cartID = sessions_children.cartID', 'left')
			->join('family_children as children', 'sessions_children.childID = children.childID', 'left')
			->join('bookings_cart_sessions as sessions_individuals', 'bookings_cart.cartID = sessions_individuals.cartID', 'left')
			->join('family_contacts as individuals', 'sessions_individuals.contactID = individuals.contactID', 'left')
			->where($where)
			->group_by('bookings_cart.cartID')
			->order_by('bookings_cart.booked desc')
			->get();

		if ($res->num_rows() == 0) {
			$info = 'No bookings yet';
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
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'tab' => $tab,
			'payments' => $res
		);
		$this->booking_view('online-booking/account/bookings', $data, 'templates/online-booking-account');
	}

	public function payments() {
		// check auth
		$this->online_booking->require_auth();

		// set defaults
		$title = 'Payments';
		$body_class = 'account payments';
		$tab = 'payments';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// get payments
		$where = array(
			'family_payments.accountID' => $this->online_booking->accountID,
			'family_payments.familyID' => $this->online_booking->user->familyID
		);
		$res = $this->db->select('family_payments.*, family_contacts.first_name, family_contacts.last_name')
			->from('family_payments')
			->join('family_contacts', 'family_payments.contactID = family_contacts.contactID', 'left')
			->where($where)
			->order_by('family_payments.added desc')
			->get();

		if ($res->num_rows() == 0) {
			$info = 'No payments yet';
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
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'tab' => $tab,
			'payments' => $res
		);
		$this->booking_view('online-booking/account/payments', $data, 'templates/online-booking-account');
	}

	public function pay() {
		// check auth
		$this->online_booking->require_auth();

		// set defaults
		$title = 'Make Payment';
		$body_class = 'account pay';
		$tab = 'payments';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();
		$prevent_payment = FALSE;
		$stripe_pk = NULL;
		$stripe_sk = NULL;
		$sagepay_environment = NULL;
		$sagepay_vendor = NULL;
		$sagepay_encryption_password = NULL;

		// work out min/max payment
		$min_payment = ($this->cart_library->get_family_account_balance()*-1) - $this->cart_library->get_family_credit_limit();
		if ($min_payment <= 0) {
			// set min payment to 1 if balance under credit limit
			$min_payment = 1;
			// if balance less than 1, reduce min payment
			if ($this->cart_library->get_family_account_balance() > -1) {
				$min_payment = ($this->cart_library->get_family_account_balance()*-1);
			}
		}
		$max_payment = ($this->cart_library->get_family_account_balance()*-1);

		if ($this->cart_library->get_family_account_balance() >= 0) {
			$info = 'No balance due';
			$prevent_payment = TRUE;
		}

		// get payment gateway
		$payment_gateway = $this->settings_library->get('cc_processor', $this->cart_library->accountID);

		switch ($payment_gateway) {
			case 'stripe':
				$stripe_pk = $this->settings_library->get('stripe_pk', $this->cart_library->accountID);
				$stripe_sk = $this->settings_library->get('stripe_sk', $this->cart_library->accountID);
				if (empty($stripe_pk) || empty($stripe_sk)) {
					$prevent_payment = TRUE;
					$error = 'Invalid payment gateway configuration';
				}
				break;
			case 'sagepay':
				$sagepay_environment = $this->settings_library->get('sagepay_environment', $this->cart_library->accountID);
				$sagepay_vendor = $this->settings_library->get('sagepay_vendor', $this->cart_library->accountID);
				$sagepay_encryption_password = $this->settings_library->get('sagepay_encryption_password', $this->cart_library->accountID);
				if (empty($sagepay_environment) || empty($sagepay_vendor) || empty($sagepay_encryption_password)) {
					$prevent_payment = TRUE;
					$error = 'Invalid payment gateway configuration';
				}
				$sagepay_is_production = FALSE;
				if ($sagepay_environment == 'production') {
					$sagepay_is_production = TRUE;
				}
				// sage pay only supports GBP
				if (currency_code($this->cart_library->accountID) !== 'GBP') {
					$prevent_payment = TRUE;
					$error = currency_code($this->cart_library->accountID) .  ' currency not supported';
				}
				break;
			default:
				$prevent_payment = TRUE;
				$error = 'Missing payment gateway configuration';
				break;
		}

		if ($prevent_payment !== TRUE && $this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			$this->form_validation->set_rules('payment_amount', 'Payment Amount', 'trim|xss_clean|greater_than[0]|greater_than_equal_to[' . $min_payment . ']|less_than_equal_to[' . $max_payment . ']');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				switch ($payment_gateway) {
					case 'stripe';
						\Stripe\Stripe::setApiKey($stripe_sk);
						try {
							$payment_intent_id = $this->input->post('payment_intent_id');

							// add description
							$desc = $this->cart_library->contact_name . ' (#' . $this->cart_library->contactID . ')';
							\Stripe\PaymentIntent::update($payment_intent_id, [
								'description' => $desc
							]);

							// capture intent
							$intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
							$intent->capture();

							// apply payment to account
							$data = array(
								'accountID' => $this->cart_library->accountID,
								'familyID' => $this->cart_library->familyID,
								'contactID' => $this->cart_library->contactID,
								'amount' => ($intent->amount/100),
								'method' => 'online',
								'transaction_ref' => $intent->id,
								'locked' => 1,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							$this->db->insert('family_payments', $data);
							$paymentID = $this->db->insert_id();

							// calc family balance
							$this->crm_library->recalc_family_balance($this->cart_library->familyID);

							// send payment email
							$this->crm_library->send_payment_confirmation($paymentID);

							// show success
							$success = 'Payment has been applied successfully';
							$this->session->set_flashdata('success', $success);
							redirect('account/payments#details');
						} catch(\Stripe\Error\Card $e) {
							$body = $e->getJsonBody();
							$errors[] = str_replace('The', 'Your', $body['error']['message']);
						} catch(\Stripe\Error\InvalidRequest $e) {
							$body = $e->getJsonBody();
							$errors[] = str_replace('The', 'Your', $body['error']['message']);
						}
						break;
					case 'sagepay':
						$sagePay = new \Eurolink\SagePayForm\Builder([
							'isProduction' => $sagepay_is_production,
							'encryptPassword' => $sagepay_encryption_password,
							'vendor' => $sagepay_vendor,
						]);
						$sagePay->setVendorTxCode($this->cart_library->accountID . '-' . $this->cart_library->familyID . '-' . $this->cart_library->contactID . '-' . date('YmdHis'));
						$sagePay->setDescription('Payment from ' . $this->online_booking->user->first_name . ' ' . $this->online_booking->user->last_name);
						$sagePay->setCurrency(currency_code($this->cart_library->accountID));
						$sagePay->setAmount(set_value('payment_amount'));
						$sagePay->setSendEMail(1);
						$sagePay->setBillingSurname($this->online_booking->user->last_name);
						$sagePay->setBillingFirstnames($this->online_booking->user->first_name);
						$sagePay->setBillingCity($this->online_booking->user->town);
						$sagePay->setBillingPostCode($this->online_booking->user->postcode);
						$sagePay->setBillingAddress1($this->online_booking->user->address1);
						$sagePay->setBillingAddress2($this->online_booking->user->address2);
						$sagePay->setCustomerEMail($this->online_booking->user->email);
						$sagePay->setCustomerName($this->online_booking->user->first_name . ' ' . $this->online_booking->user->last_name);
						$sagePay->setBillingCountry('GB');
						$sagePay->setDeliverySameAsBilling();
						$sagePay->setSuccessURL(site_url('sagepay'));
						$sagePay->setFailureURL(site_url('sagepay'));
						?><form method="POST" action="<?php echo $sagePay->getFormEndpoint(); ?>" id="sagepay">
							<input type="hidden" name="VPSProtocol" value="<?php echo $sagePay->getVPSProtocol(); ?>">
							<input type="hidden" name="TxType" value="<?php echo $sagePay->getTxType(); ?>">
							<input type="hidden" name="Vendor" value="<?php echo $sagePay->getVendorCode(); ?>">
							<input type="hidden" name="Crypt" value="<?php echo $sagePay->getCrypt(); ?>">
							<button type="submit">Pay with SagePay</button>
						</form>
						<script>
							document.getElementById('sagepay').submit();
						</script><?php
						exit();
						break;
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
			'body_class' => $body_class,
			'min_payment' => $min_payment,
			'max_payment' => $max_payment,
			'prevent_payment' => $prevent_payment,
			'payment_gateway' => $payment_gateway,
			'stripe_pk' => $stripe_pk,
			'stripe_sk' => $stripe_sk,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'tab' => $tab,
		);
		$this->booking_view('online-booking/account/pay', $data, 'templates/online-booking-account');
	}

	public function load_subscription(){
		// check auth
		$this->online_booking->require_auth();

		header('Content-Type: application/json');

		# retrieve json from POST body
		$json_str = file_get_contents('php://input');
		$json_obj = json_decode($json_str);

		$stripe_pk= $json_obj->stripe_pk;
		$stripe_price_id = json_encode($json_obj->stripe_price_id);

		if(!empty($stripe_pk) && !empty($stripe_price_id)) {
			$this->load->helper('stripe_helper');
			$values = stripe_subscription_external_js($stripe_pk, $stripe_price_id);
			echo json_encode([
				'data' => $values
			]);
			exit();
		}

		# Invalid status
		http_response_code(500);
		echo json_encode([
			'error' => 'Invalid request, please try again later.'
		]);
	}

	public function stripe_auth() {
		// check auth
		$this->online_booking->require_auth();

		\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $this->cart_library->accountID));

		header('Content-Type: application/json');

		# retrieve json from POST body
		$json_str = file_get_contents('php://input');
		$json_obj = json_decode($json_str);

		$intent = null;
		try {
			if (isset($json_obj->payment_method_id)) {
				# Create the PaymentIntent
				$intent = \Stripe\PaymentIntent::create([
					'payment_method' => $json_obj->payment_method_id,
					'amount' => $json_obj->amount,
					'currency' => currency_code($this->online_booking->accountID),
					'confirmation_method' => 'manual',
					'confirm' => true,
					'capture_method' => 'manual',
				]);

			}
			if (isset($json_obj->payment_intent_id)) {
				$intent = \Stripe\PaymentIntent::retrieve(
					$json_obj->payment_intent_id
				);
				$intent->confirm();
			}
			# Note that if your API version is before 2019-02-11, 'requires_action'
			# appears as 'requires_source_action'.
			if ($intent->status == 'requires_action' &&
				$intent->next_action->type == 'use_stripe_sdk') {
				# Tell the client to handle the action
				echo json_encode([
					'requires_action' => true,
					'payment_intent_client_secret' => $intent->client_secret
				]);
			} else if ($intent->status == 'succeeded') {
				// not used as using authorisation
			} else if ($intent->status == 'requires_capture') {
				// payment authorised, send intent id
				echo json_encode([
					"id" => $intent->id
				]);
			} else {
				# Invalid status
				http_response_code(500);
				echo json_encode([
					'error' => 'Invalid PaymentIntent status'
				]);
			}
		}catch (\Stripe\Exception\InvalidRequestException $e) {
			// Invalid parameters were supplied to Stripe's API
			return $this->output
				->set_content_type('application/json')
				->set_output(
					json_encode([
						'error' => $e->getMessage()
					])
				);
		} catch (\Stripe\Error\Base $e) {
			# Display error on client
			echo json_encode([
				'error' => $e->getMessage()
			]);
		}
	}

	public function get_stripe_customer() {

		$this->online_booking->require_auth();

		\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $this->cart_library->accountID));

		header('Content-Type: application/json');

		# retrieve json from POST body
		$json_str = file_get_contents('php://input');
		$json_obj = json_decode($json_str);

		$where = array(
			'accountID' => $this->cart_library->accountID,
			'contactID' => $json_obj->contactID
		);

		$res = $this->db->from('family_contacts')->where($where)->get();

		foreach($res->result() as $contact_info) break;

		//get if exists else create
		if($contact_info->stripe_customer_id !== NULL && !empty($contact_info->stripe_customer_id)) {
			try {
				$customer = \Stripe\Customer::retrieve($contact_info->stripe_customer_id);

				echo json_encode([
					'customer' => $customer
				]);
			} catch (\Stripe\Exception\InvalidRequestException $e) {
				// Invalid parameters were supplied to Stripe's API
				return $this->output
					->set_content_type('application/json')
					->set_output(
						json_encode([
							'error' => $e->getMessage()
						])
					);
			}catch (\Stripe\Error\Base $e) {
				# Display error on client
				return $this->output
					->set_content_type('application/json')
					->set_output(
						json_encode([
							'error' => $e->getMessage()
						])
					);
			}
		} else {
			try {
				$customer = \Stripe\Customer::create([
					'email' => $contact_info->email,
					'name' => $contact_info->first_name.' '.$contact_info->last_name,
					'description' => 'customer',
					'address' => [
						'line1' => $contact_info->address1,
						'city' => $contact_info->town,
						'country' => 'GB',
						'postal_code' => $contact_info->postcode,
					]
				]);

				$data = array(
					'stripe_customer_id' => $customer->id,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$this->db->update('family_contacts', $data, $where, 1);

				return $this->output
					->set_content_type('application/json')
					->set_output(
						json_encode([
							'customer' => $customer
						])
					);
			} catch (\Stripe\Error\Base $e) {
				# Display error on client
				return $this->output
					->set_content_type('application/json')
					->set_output(
						json_encode([
							'error' => $e->getMessage()
						])
					);
			}
		}
	}

	public function create_stripe_subscription() {
		$this->online_booking->require_auth();

		\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $this->cart_library->accountID));

		header('Content-Type: application/json');

		# retrieve json from POST body
		$json_str = file_get_contents('php://input');
		$json_obj = json_decode($json_str);

		 // Attach the payment method to the customer.
		try {
			$payment_method = \Stripe\PaymentMethod::retrieve(
			  	$json_obj->paymentMethodId
			);

			$payment_method->attach([
				'customer' => $json_obj->customerId,
			]);
		} catch (Exception $e) {
			# Display error on client
			return $this->output
						->set_content_type('application/json')
						->set_output(
							json_encode([
								'error' => $e->getMessage()
							])
						);
		}

		// Set the default payment method on the customer
		\Stripe\Customer::update($json_obj->customerId, [
			'invoice_settings' => [
			  'default_payment_method' => $json_obj->paymentMethodId
			]
		]);

		try {
			// Create the subscription
			$subscription = \Stripe\Subscription::create([
				'customer' => $json_obj->customerId,
				'items' => [
					[
						'price' => $json_obj->priceId,
						'quantity' => $json_obj->quantity
					],
				],
				['metadata' => ['cartID' => $json_obj->cartID]],
				'expand' => ['latest_invoice.payment_intent'],
			]);

			return $this->output
						->set_content_type('application/json')
						->set_output(
							json_encode($subscription)
						);

		} catch (Exception $e) {
			# Display error on client
			return $this->output
						->set_content_type('application/json')
						->set_output(
							json_encode([
								'error' => $e->getMessage()
							])
						);
		}
	}

	public function subscriptions() {
		// check auth
		$this->online_booking->require_auth();

		// set defaults
		$title = 'Subscriptions';
		$body_class = 'account';
		$tab = 'subscriptions';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// get payments
		$where = array(
			'participant_subscriptions.accountID' => $this->online_booking->accountID,
			'participant_subscriptions.status !=' => 'cancelled',
			'(' . $this->db->dbprefix('family_children') .'.familyID = ' . $this->online_booking->user->familyID . ' OR ' . $this->db->dbprefix('family_contacts') .'.familyID = ' . $this->online_booking->user->familyID . ')' => NULL
		);

		$res = $this->db->select('subscriptions.*, participant_subscriptions.id as psID, family_children.first_name, family_children.last_name, family_contacts.first_name as contact_first_name, family_contacts.last_name as contact_last_name')
						->from('subscriptions')
						->join('participant_subscriptions', 'subscriptions.subID = participant_subscriptions.subID', 'left')
						->join('family_children', 'participant_subscriptions.childID = family_children.childID', 'left')
						->join('family_contacts', 'participant_subscriptions.contactID = family_contacts.contactID', 'left')
						->where($where)
						->get();

		if ($res->num_rows() == 0) {
			$info = 'No subscriptions yet';
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
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'tab' => $tab,
			'subscriptions' => $res
		);
		$this->booking_view('online-booking/account/subscriptions', $data, 'templates/online-booking-account');
	}

	public function subscription($id) {
		// check auth
		$this->online_booking->require_auth();

		// set defaults
		$title = 'Subscription Details';
		$body_class = '';
		$tab = 'subscriptions';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		
		//get subscription module activation
		$online_booking_subscription_module = 0;
		$query = $this->db->from("accounts")->where("accountID", $this->cart_library->accountID)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$online_booking_subscription_module = $result->addon_online_booking_subscription_module;
			}
		}

		$where = array(
			'participant_subscriptions.id' => $id,
			'participant_subscriptions.accountID' => $this->online_booking->accountID,
		);

		$res = $this->db->select('subscriptions.*, participant_subscriptions.id as psID, participant_subscriptions.contactID, participant_subscriptions.childID, participant_subscriptions.status')
						->from('subscriptions')
						->join('participant_subscriptions', 'subscriptions.subID = participant_subscriptions.subID', 'inner')
						->where($where)
						->limit(1)
						->get();

		if($res->num_rows() == 0) {
			show_404();
		}

		$cartID = 0;
		foreach($res->result() as $subdata) break;
		$subStatus = $subdata->status;
		$chID = $subdata->childID;
		$cID = $subdata->contactID;
		if($chID != null &&  $chID != ""){
			$query = $this->db->select("cartID")->from("bookings_cart_subscriptions")->WHERE("subID", $subdata->subID)->WHERE("childID",$chID)->get();
			foreach($query->result() as $result1){
				$cartID = $result1->cartID;
			}
		}else{
			$query = $this->db->select("cartID")->from("bookings_cart_subscriptions")->WHERE("subID", $subdata->subID)->WHERE("contactID", $cID)->get();
			foreach($query->result() as $result1){
				$cartID = $result1->cartID;
			}
		}


		$where = array(
			'bookings_cart.cartID' => $cartID,
			'bookings_cart.accountID' => $this->cart_library->accountID,
			'bookings_cart.type' => 'booking'
		);
		$res = $this->db->select('bookings_cart.contactID, bookings_cart.familyID, GROUP_CONCAT(DISTINCT sessions.blockID) as blockIDs')
		->from('bookings_cart')
			->join('bookings_cart_sessions as sessions', 'bookings_cart.cartID = sessions.cartID', 'left')
			->where($where)
		->get();

		if ($res->num_rows() == 0) {
			show_404();
		}
		$familyID = 0;
		foreach ($res->result() as $cart) {
			$contactID = $cart->contactID;
			$blockIDs = $cart->blockIDs;
			$familyID = $cart->familyID;
		}

		$args['cartID'] = $cartID;
		$args['contactID'] = $contactID;
		$args['accountID'] = $this->online_booking->accountID;
		$args['in_crm'] = FALSE;

		$blockID = $blockIDs;
		$flag = 1;
		$this->cart_library->init($args, $flag);


		$title = 'Update Sessions';
		$body_class = 'account account_session';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();
		$prevent_booking = FALSE;
		$participants = array();
		$selected_participants = array();
		$subs = array();
		$selected_subs = array();
		$subscriptions_only = FALSE;
		$selected_lessons = array();
		$breadcrumb_levels = array(
		);

		// look up block
		$where = array(
			'bookings_blocks.blockID' => $blockID,
		);

		$blocks_where = [];
		if (!$this->in_crm) {
			$where['bookings.disable_online_booking !='] = 1;
		}
		$blocks = $this->cart_library->get_blocks($where, $blocks_where, NULL, FALSE);

		// if doesn't exist, 404
		if (count($blocks) == 0) {
			show_404();
		}

		// get first result
		foreach ($blocks as $block_info) {
			break;
		}

		// set title
		$title = $block_info->booking;
		$register_type = $block_info->register_type;


		// get participants link
		$new_participants_link = 'account/participants/new/' . $block_info->bookingID;
		$participant_id_field = 'childID';
		$participants_table = 'family_children';
		$new_participants_link = 'booking/book/new/child';
		if (strpos($block_info->register_type, 'individuals') === 0) {
			$new_participants_link = 'account/individual/new';
			$participant_id_field = 'contactID';
			$participants_table = 'family_contacts';
			$new_participants_link = 'booking/book/new/individual';

		}
		$new_adults_link ='';$adult_id_field='';$adults_table='';
		if (strpos($block_info->register_type, 'adults_children') === 0) {
			//add participants Link
			$new_participants_link = 'booking/book/new/child';
			$participant_id_field = 'childID';
			$participants_table = 'family_children';

			//add adults Link
			$new_adults_link = 'account/individual/new';
			$adult_id_field = 'contactID';
			$adults_table = 'family_contacts';
			$new_adults_link = 'booking/book/new/individual';

		}

		// get booking
		$where = array(
			'bookings.bookingID' => $block_info->bookingID
		);
		$res = $this->db->from('bookings')
		->where($where)
		->limit(1)
		->get();

		// check if already booked sessions on this booking
		$already_booked_sessions = array();
		$already_booked_subscriptions = array();
		if ($prevent_booking !== TRUE) {
			// get block bookingID
			$where = array(
				'bookings_cart.accountID' => $this->cart_library->accountID,
				'bookings_cart.familyID' => $this->cart_library->familyID,
				'bookings_cart.type' => 'booking',
				'bookings_cart_sessions.bookingID' => $block_info->bookingID,
				'bookings_cart.cartID !=' => $this->cart_library->cartID // if editing booking
			);
			$res = $this->db->from('bookings_cart')->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					if (strpos($block_info->register_type, 'adults_children') === 0) {
						$already_booked_sessions[$row->lessonID][$row->date][] = ($row->childID == "")?$row->contactID:$row->childID;
					}else{
						$already_booked_sessions[$row->lessonID][$row->date][] = $row->$participant_id_field;
					}
				}
			}

			// get block bookingID
			$where = array(
				'bookings_cart.accountID' => $this->cart_library->accountID,
				'bookings_cart.familyID' => $this->cart_library->familyID,
				'bookings_cart.type' => 'cart',
				'participant_subscriptions.status' => 'active'
			);
			$res = $this->db->select('participant_subscriptions.subID, participant_subscriptions.childID, participant_subscriptions.contactID')
				->from('bookings_cart_subscriptions')
				->join('bookings_cart', 'bookings_cart.contactID = bookings_cart_subscriptions.contactID', 'LEFT')
				->join('participant_subscriptions','participant_subscriptions.accountID = bookings_cart_subscriptions.accountID AND
				(bookings_cart_subscriptions.childID = participant_subscriptions.childID OR bookings_cart_subscriptions.contactID = `'.$this->db->dbprefix("participant_subscriptions").'`.`contactID`)', "LEFT")
				->where($where)
				->group_by(array("participant_subscriptions.contactID", "participant_subscriptions.childID", "participant_subscriptions.subID"))
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$already_booked_subscriptions[empty($row->childID)?$row->contactID:$row->childID][] = $row->subID;
				}
			}
		}

		$sold_out = TRUE;
		foreach ($blocks as $block) {
			if ($block->availability_status_class !== 'soldout') {
				$sold_out = FALSE;
			}
		}

		// get participants
		$participants = $this->cart_library->get_participants($blockID);
		$fieldContact = NULL;$fieldChild = NULL; $arrayContact = array();$arrayChild = array();
		foreach($participants as $participant){
			if(isset($participant->contactID)){
				$fieldContact = 'contactID';
				array_push($arrayContact, $participant->contactID);
			}
			if(isset($participant->childID)){
				$fieldChild = 'childID';
				array_push($arrayChild, $participant->childID);
			}
			if(isset($participant->type) && $participant->type === "child"){
				$fieldChild = 'childID';
				array_push($arrayChild, $participant->Id);
			}
			if(isset($participant->type) && $participant->type === "parent"){
				$fieldContact = 'contactID';
				array_push($arrayContact, $participant->Id);
			}
		}

		$already_booked_subscriptions = array();
		if ($prevent_booking !== TRUE) {
			// get block bookingID
			$where = array(
				'participant_subscriptions.accountID' => $this->cart_library->accountID,
				'participant_subscriptions.status' => 'active'
			);
			$res = $this->db->select('subID, childID, contactID')
				->from('participant_subscriptions')
				->where($where)
				->where_in($fieldContact, $arrayContact)
				->where_in($fieldChild, $arrayChild)
				->group_by(array("participant_subscriptions.contactID", "participant_subscriptions.childID", "participant_subscriptions.subID"))
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$already_booked_subscriptions[empty($row->childID)?$row->contactID:$row->childID][] = $row->subID;
				}
			}
		}

		//Get Current subscription status
		$subscription_status = array();
		$query_part ='';
		if(!empty($fieldChild) && !empty($fieldContact)){
			$query_part = 'AND (childID IN ('.implode(",", $arrayChild).') OR contactID IN ('.implode(",", $arrayChild).'))';
		}elseif(!empty($fieldChild) && empty($fieldContact)){
			$query_part = 'AND childID IN ('.implode(",", $arrayChild).')';
		}elseif(empty($fieldChild) && !empty($fieldContact)){
			$query_part = 'AND contactID IN ('.implode(",", $arrayContact).')';
		}
		$query_part = ' AND id ='.$id;

		$sql = 'SELECT subID, childID, contactID, status, modified FROM ' . $this->db->dbprefix('participant_subscriptions') . ' WHERE
						accountID = '.$this->cart_library->accountID.' AND status <> "active" '.$query_part.' GROUP BY contactID, childID, subID';
		$res = $this->db->query($sql);

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$subscription_status[empty($row->childID)?$row->contactID:$row->childID][$row->subID]['status'] = $row->status;
				$action_performed_date = date("d-m-Y", strtotime($row->modified));
				$subscription_status[empty($row->childID)?$row->contactID:$row->childID][$row->subID]['valid'] = $action_performed_date;
			}
		}

		if($online_booking_subscription_module) {
			$subscriptions_only = $block_info->subscriptions_only;

			foreach($participants as $participant) {
				// get subs
				$where = array(
					'subscriptions.bookingID' => $block_info->bookingID,
					'subscriptions.accountID' => $this->cart_library->accountID,
				);

				$subs_res = $this->db->select('subscriptions.subID, no_of_sessions_per_week, session_cut_off, subName, frequency, price, individual_subscription')
					->from('subscriptions')
					->where($where)
					->group_by('subscriptions.subID')
					->get();

				if($subs_res->num_rows() > 0) {
					foreach($subs_res->result() as $sub) {
						if($sub->individual_subscription == true) {
							$where = array(
								'childID' => $participant->$participant_id_field,
								'subID' => $sub->subID,
								'status !=' => 'cancelled'
							);
							$indivdual_subscription = $this->db->from('participant_subscriptions')->where($where)->get();
							if($indivdual_subscription->num_rows() == 0){
								continue;
							}
						}

						$where = array(
							'subscriptions_lessons_types.accountID' => $this->cart_library->accountID,
							'subscriptions_lessons_types.subID' => $sub->subID
						);


						/*$lesson_types = $this->db->select('GROUP_CONCAT( ' . $this->db->dbprefix('lesson_types') . '.name  SEPARATOR\', \') as types')
							->from('subscriptions_lessons_types')
							->join('lesson_types', 'subscriptions_lessons_types.typeID = lesson_types.typeID')
							->where($where)
							->get();

						$types = '';
						if($lesson_types->num_rows() > 0) {
							foreach($lesson_types->result() as $types) break;
							$types = ' (' . $types->types . ')';
						}*/
						$field='';
						if(isset($participant->$participant_id_field)){
							$field = $participant->$participant_id_field;
						}
						if(isset($participant->type)){
							$field = $participant->Id;
						}

						$subs[$field][$sub->subID] = array(
							'label' => $sub->subName . ' (' . currency_symbol($this->online_booking->accountID) . $sub->price . ' - ' . ucfirst($sub->frequency) . ')',
							'frequency' => $sub->frequency,
							'price' => $sub->price,
							'no_of_sessions_per_week' => $sub->no_of_sessions_per_week,
							'session_cut_off' => $sub->session_cut_off
						);
					}
				}
			}
		}

		// if all checks passed, continue
		if ($prevent_booking !== TRUE) {
			// look up all current and future blocks in booking
			$where = array(
				'bookings_blocks.bookingID' => $block_info->bookingID
			);
			$search_fields = array();
			/*if (!$this->in_crm) {
				$where['bookings.disable_online_booking !='] = 1;
				$where['bookings_blocks.endDate >='] = date('Y-m-d');
				$search_fields = array(
					'future_only' => true
				);
			} else if ($this->cart_library->cart_type == 'booking') {*/
				$search_fields['show_all'] = TRUE;
			//}
			$blocks = $this->cart_library->get_blocks($where, $search_fields);

			// no blocks found, 404
			if (count($blocks) == 0) {
				show_404();
			}

			// check if already in cart
			$booked_sessions = $this->cart_library->get_booked_sessions();
			foreach ($booked_sessions as $block_id => $sessions) {
				if (in_array($block_id, array_keys($blocks))) {
					foreach ($sessions as $date => $lessons) {
						foreach ($lessons as $lessonID => $participantIDs) {
							foreach ($participantIDs as $participantID) {
								if (!isset($selected_lessons[$lessonID][$date])) {
									$selected_lessons[$lessonID][$date] = array();
								}
								$selected_lessons[$lessonID][$date][] = $participantID;
								$selected_participants[$participantID] = $participantID;
								$already_in_cart = TRUE;
							}
						}
					}
				}
			}

			if ($this->input->post('process') == 1) {
				$selected_participants = (array)$this->input->post('participants');
				$selected_lessons = (array)$this->input->post('lessons');
				$register_type = $this->input->post('register_type');
				$selected_subs = (array) $this->input->post('subscriptions');

				//Subscription only event
				if($online_booking_subscription_module && $block->subscriptions_only === '1'){
					foreach ($block->lessons as $lesson_index => $lesson_value){
						if(isset($already_booked_sessions) && isset($already_booked_sessions[$lesson_index])){
							continue;
						}
						foreach ($block->dates as $date_index => $date_value) {
							if (strtotime($date_index) > strtotime(date("Y-m-d"))) {
								foreach ($selected_participants as $participant_index => $participant_value) {
									$selected_lessons[$lesson_index][$date_index][$participant_index] = $participant_value;
								}
								break 2;
							}
						}
					}
				}

				// if booking all, only have first date, so populate with all dates
				if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
					$new_selected_lessons = array();
					if (count($selected_lessons) > 0) {
						// loop lessons
						foreach ($selected_lessons as $lessonID => $dates) {
							foreach ($dates as $date => $participantIDs) {
								foreach ($participantIDs as $participantID) {
									// loop block to find out which block session is in
									foreach ($blocks as $block) {
										if (array_key_exists($lessonID, $block->lessons)) {
											// session is in this block, get dates
											foreach ($block->dates as $tmp_date => $lessons) {
												// if session happens on this date, return
												if (array_key_exists($lessonID, $lessons)) {
													if (!isset($new_selected_lessons[$lessonID][$tmp_date])) {
														$new_selected_lessons[$lessonID][$tmp_date] = array();
													}
													$new_selected_lessons[$lessonID][$tmp_date][] = $participantID;
												}
											}
										}
									}
								}
							}
						}
					}
					$selected_lessons = $new_selected_lessons;
				}

				// check not trying to book sessions they already booked
				$new_selected_lessons = array();
				if (count($selected_lessons) > 0) {
					// loop lessons
					foreach ($selected_lessons as $lessonID => $dates) {
						foreach ($dates as $date => $participantIDs) {
							foreach ($participantIDs as $participantID) {
								if (!isset($already_booked_sessions[$lessonID][$date]) || !in_array($participantID, $already_booked_sessions[$lessonID][$date])) {
									// ok
									if (!isset($new_selected_lessons[$lessonID][$date])) {
										$new_selected_lessons[$lessonID][$date] = array();
									}
									$new_selected_lessons[$lessonID][$date][] = $participantID;
								}
							}
						}
					}
				}
				$selected_lessons = $new_selected_lessons;
				$flag = 1;
				$res = $this->cart_library->process_block($blockID, $selected_lessons, $flag);


				if (!is_string($res)) {
					$blocks = $this->cart_library->get_blocks($where, $blocks_where);
					$selected_lessons = $res;
				} else {

					$success = 'Your booking has been updated successfully';
					$this->session->set_flashdata('success', $success);
					redirect("account/subscription/".$id);

				}
			} else {


				$potential_participant = $this->input->get('participant');
				if (!empty($potential_participant) && array_key_exists($potential_participant, $participants)) {
					$selected_participants[] = $potential_participant;
				}


				//check for selected subscriptions
				$where = array(
					'cartID' => $this->cart_library->cartID,
					'accountID' => $this->cart_library->accountID,
				);

				$res = $this->db->from('bookings_cart_subscriptions')
					->where($where)
					->get();

				if($res->num_rows() > 0) {
					foreach($res->result() as $sub) {
						$field_val = $sub->$participant_id_field;
						if(isset($sub->contactID) &&  $sub->contactID !== '0' && !empty($sub->contactID)){
							$field_val = $sub->contactID;
						}
						if(isset($sub->childID) &&  $sub->childID !== '0' && !empty($sub->childID)){
							$field_val = $sub->childID;
						}
						$selected_subs[$field_val] = $sub->subID;
						$selected_participants[$field_val] = $field_val;
					}
				}
			}

		}
		foreach ($blocks as $block) {
			$blocks[$block->blockID]->status = $subStatus;
		}

		//get session types
		$where = array(
			'subscriptions_lessons_types.subID' => $subdata->subID,
			'subscriptions_lessons_types.accountID' => $this->online_booking->accountID
		);

		$res = $this->db->select('GROUP_CONCAT(' . $this->db->dbprefix('lesson_types') .'.name SEPARATOR\', \') as types')
							->from('lesson_types')
							->join('subscriptions_lessons_types', 'lesson_types.typeID = subscriptions_lessons_types.typeID', 'inner')
							->where($where)
							->get();

		foreach($res->result() as $types) break;

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
			'body_class' => $body_class,
			'register_type' => $register_type,
			'success' => $success,
			'blockID' => $blockID,
			'error' => $error,
			'info' => $info,
			'tab' => $tab,
			'subdata' => $subdata,
			'types' => $types,
			'blocks' => $blocks,
			'participants' => $participants,
			'selected_participants' => $selected_participants,
			'subs' => $subs,
			'subscription_status' => $subscription_status,
			'selected_subs' => $selected_subs,
			'subscriptions_only' => $subscriptions_only,
			'selected_lessons' => $selected_lessons,
			'new_participants_link' => $new_participants_link,
			'new_adults_link' => $new_adults_link,
			'already_booked_sessions' => $already_booked_sessions,
			'already_booked_subscriptions' => $already_booked_subscriptions,
			'breadcrumb_levels' => $breadcrumb_levels,
			'in_crm' => false,
			'cartID' => $cartID
		);
		$this->booking_view('online-booking/account/subscription', $data, 'templates/online-booking-account');
	}

	public function cancel_subscription($id) {
		if($id == NULL) {
			return FALSE;
		}

		\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $this->cart_library->accountID));

		$where = array(
			'participant_subscriptions.id' => $id,
			'participant_subscriptions.accountID' => $this->online_booking->accountID,
		);

		$res = $this->db->select('participant_subscriptions.gc_subscription_id, participant_subscriptions.childID, participant_subscriptions.contactID, subscriptions.subID, subscriptions.subName, subscriptions.frequency, subscriptions.price, subscriptions.bookingID, subscriptions.no_of_sessions_per_week, participant_subscriptions.stripe_subscription_id, subscriptions.payment_provider')
						->from('participant_subscriptions')
						->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID')
						->where($where)
						->get();

		if($res->num_rows() > 0) {
			foreach($res->result() as $sub) break;

			$cancel = FALSE;

			switch($sub->payment_provider) {
				case 'gocardless':
					if($sub->gc_subscription_id != NULL) {
						$cancel = $this->gocardless_library->cancel_subscription($sub->gc_subscription_id);
					}
				break;
				case 'stripe':
					if($sub->stripe_subscription_id !== NULL) {
						try {
							$subscription = \Stripe\Subscription::retrieve($sub->stripe_subscription_id);
							$qty = $subscription->items->data[0]->quantity;
							if ($qty > 1) {
								$cancel = $subscription->update($sub->stripe_subscription_id,
									['quantity' => $qty - 1]);
								if (isset($cancel)) {
									$cancel = TRUE;
								}
							} else {
								$cancel = $subscription->update($sub->stripe_subscription_id,
									['cancel_at_period_end' => true]);
								if (isset($cancel)) {
									$cancel = TRUE;
								}
							}
						} catch(\Stripe\Error\Card $e) {
							$body = $e->getJsonBody();
						} catch(\Stripe\Exception\InvalidRequestException $e) {
							$body = $e->getJsonBody();
						}
					}
					break;
				break;
			}


			if($cancel === TRUE) {

				$data = array(
					'status' => 'inactive',
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'gc_subscription_id' => NULL
				);

				$this->db->update('participant_subscriptions', $data, $where, 1);

				$where = array(
					'bookings.bookingID' => $sub->bookingID,
					'bookings.accountID' => $this->online_booking->accountID,
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

				if ($booking_info->type != 'event' && $booking_info->project != 1) {
					show_404();
				}

				$participants_id_field = 'childID';
				$participants_table = 'family_children';

				if (strpos($booking_info->register_type, 'individuals') === 0) {
					$participants_id_field = 'contactID';
					$participants_table = 'family_contacts';
				}

				$where = array (
					$participants_table .'.' . $participants_id_field => $sub->$participants_id_field,
					'family_contacts.accountID' => $this->online_booking->accountID
				);

				$res = $this->db->select('family_contacts.first_name, family_contacts.last_name, family_contacts.email, family_contacts.familyID, family_contacts.contactID')
								->from('family_contacts')
								->join('family_children', 'family_contacts.familyID = family_children.familyID')
								->where($where)->limit(1)->get();

				if($res->num_rows() == 0) {
					return FALSE;
				}

				foreach ($res->result() as $contact_info) break;

				//send email
				$smart_tags = array();
				$smart_tags['contact_first'] = $contact_info->first_name;
				$smart_tags['contact_last'] = $contact_info->last_name;
				$smart_tags['login_link'] = site_url();

				//get company name
				$where = array(
					'accountID' => $this->online_booking->accountID
				);
				$res = $this->db->select('company')->from('accounts')->where($where)->limit(1)->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						// replace tags
						$smart_tags['company'] = $row->company;
					}
				}

				//get org name
				$where = array(
					'bookings.accountID' => $this->online_booking->accountID,
					'bookings.bookingID' => $sub->bookingID
				);

				$res = $this->db->select('orgs.name as org_name, bookings.name as bookings_name')
									->from('orgs')
									->join('bookings', 'orgs.orgID = bookings.orgID')
									->where($where)
									->limit(1)
									->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						// replace tags
						$smart_tags['org_name'] = $row->org_name;
						$smart_tags['event_name'] = $row->bookings_name;
					}
				}

				//get session types
				$where = array(
					'subscriptions_lessons_types.subID' => $sub->subID,
					'subscriptions_lessons_types.accountID' => $this->online_booking->accountID
				);

				$smart_tags['subscription_details'] =
					'Name: ' . $sub->subName . '<br>Frequency: ' . ucfirst($sub->frequency) . '<br>Rate: ' . currency_symbol($this->online_booking->accountID) . $sub->price . '<br>No. of Sessions per Week: ' . $sub->no_of_sessions_per_week . '<br>';

				$smart_tags['link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';

				// get email template
				$subject = $this->settings_library->get('email_cancel_subscription_subject', $this->online_booking->accountID);
				$email_html = $this->settings_library->get('email_cancel_subscription', $this->online_booking->accountID);

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
				}

				//Send Email to staff
				$email = $this->settings_library->get('email', $this->cart_library->accountID);
				if(!empty($email)){
					// get email template
					$staff_subject = $this->settings_library->get('staff_cancel_subscription_subject', $this->online_booking->accountID);
					$staff_email_html = $this->settings_library->get('staff_cancel_subscription_body', $this->online_booking->accountID);
					if(!empty($staff_subject) && !empty($staff_email_html)){
						// replace smart tags in email
						foreach ($smart_tags as $key => $value) {
							$staff_email_html = str_replace('{' . $key . '}', $value, $staff_email_html);
							$staff_subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $staff_subject);
						}
					}
					$this->crm_library->send_email($email, $staff_subject, $staff_email_html, array(), TRUE, $this->online_booking->accountID);
				}

				// send
				if ($this->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->online_booking->accountID)) {

					$byID = NULL;
					if (isset($this->auth->user->staffID)) {
						$byID = $this->auth->user->staffID;
					}

					// get html email and convert to plain text
					$this->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					// save
					$data = array(
						'familyID' => $contact_info->familyID,
						'contactID' => $contact_info->contactID,
						'byID' => $byID,
						'type' => 'email',
						'destination' => $contact_info->email,
						'subject' => $subject,
						'contentHTML' => $email_html,
						'contentText' => $email_plain,
						'status' => 'sent',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->online_booking->accountID
					);

					$this->db->insert('family_notifications', $data);
				}

				$delete_success_redirect = site_url('account/subscriptions#details');
				$delete_success_message = "Your subscription has been cancelled and we've also sent you an email confirming this.";
				$this->session->set_flashdata('success', $delete_success_message);
				redirect($delete_success_redirect);
			} else {
				$delete_error_redirect = site_url('account/subscription/' . $id . '#details');
				$delete_error_message = "There was an error cancelling your subscription.";
				$this->session->set_flashdata('error', $delete_error_message);
				redirect($delete_error_redirect);
			}
		} else {
			$delete_error_redirect = site_url('account/subscription/' . $id . '#details');
			$delete_error_message = "There was an error cancelling your subscription.";
			$this->session->set_flashdata('error', $delete_error_message);
			redirect($delete_error_redirect);
		}
	}

	public function payment_plans() {
		// check auth
		$this->online_booking->require_auth();

		// set defaults
		$title = 'Payment Plans';
		$body_class = 'account payment_plans';
		$tab = 'payment-plans';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// get payments
		$where = array(
			'family_payments_plans.accountID' => $this->online_booking->accountID,
			'family_payments_plans.familyID' => $this->online_booking->user->familyID
		);
		$res = $this->db->select('family_payments_plans.*, family_contacts.first_name, family_contacts.last_name')
			->from('family_payments_plans')
			->join('family_contacts', 'family_payments_plans.contactID = family_contacts.contactID', 'left')
			->where($where)
			->order_by('family_payments_plans.added desc')
			->get();

		if ($res->num_rows() == 0) {
			$info = 'No payment plans yet';
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
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'tab' => $tab,
			'payment_plans' => $res
		);
		$this->booking_view('online-booking/account/payment-plans', $data, 'templates/online-booking-account');
	}

	public function login() {
		// check auth
		$this->online_booking->require_auth(FALSE);

		// set defaults
		$title = 'Login';
		$body_class = 'login';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {
			// get email and password
			$email = $this->input->post('email');
			$password = $this->input->post('password');

			if (empty($email) && empty($password)) {
				$error = 'Please enter your email address and password';
			} else if (!empty($email) && empty($password)) {
				$error = 'Please enter your password';
			} else if (empty($email) && !empty($password)) {
				$error = 'Please enter your email address';
			} else if (!empty($this->session->flashdata('login_reason'))) {
				$error = $this->session->flashdata('login_reason');
			}

			// check auth
			if (empty($error) && $this->online_booking->check_auth($email, $password)) {
				// check if redirecting
				$redirect_to = $this->session->userdata('redirect_to');

				if (!empty($redirect_to)) {
					// unset redirect
					$this->session->unset_userdata('redirect_to');
				} else {
					$redirect_to = 'account';
				}

				if (!$this->online_booking->check_active()) {
					$error = 'Your account is currently inactive, please email your administrator for assistance at '.
						$this->online_booking->account->company .'<br>' .
						mailto($this->settings_library->get('email_from', $this->online_booking->user->accountID),
							$this->settings_library->get('email_from', $this->online_booking->user->accountID));
					$this->online_booking->destroy_session();
				}

				if (!$error) {
					// redirect
					redirect($redirect_to);
					return TRUE;
				}
			}

			if (empty($error)) {
				$error = 'You have entered an incorrect email address or password. Please check your login details and try again or ' . anchor('account/reset', 'reset your password');
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
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);
		$this->booking_view('online-booking/account/login', $data);
	}

	public function logout() {
		// check auth
		$this->online_booking->require_auth();

		// log out
		$this->online_booking->logout();

		// redirect
		redirect('account/login');

		return TRUE;
	}

	public function reset($reset_hash = NULL) {
		// check auth
		$this->online_booking->require_auth(FALSE);

		// set defaults
		$title = 'Reset Password';
		$body_class = 'reset-password';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// load libraries
		$this->load->library('form_validation');

		// if setting new password
		if (!empty($reset_hash)) {
			// if hash invalid
			if (!$user_info = $this->online_booking->check_hash($reset_hash)) {
				$error = 'Invalid or expired confirmation link. Please try again.';
			} else {
				return $this->set_new_password($user_info->contactID);
			}
		}

		// if posted
		if ($this->input->post()) {
			// set validation rules
			$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				// check account and generate reset hash
				$reset_hash = $this->online_booking->reset_password($this->input->post('email'));

				// tell user
				$success = 'If you\'ve entered a valid email, we\'ve sent you an email with further instructions.';

				$this->session->set_flashdata('success', $success);

				redirect('account/reset');
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
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);
		$this->booking_view('online-booking/account/reset', $data);
	}

	private function set_new_password($contactID = NULL) {
		// check auth
		$this->online_booking->require_auth(FALSE);

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Set Password';
		$body_class = 'set-password';
		$show_login_link = TRUE;
		$redirect_to = 'account/login';
		$password = NULL;
		$password_confirm = NULL;
		$success = NULL;
		$error = NULL;
		$errors = array();

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('password', 'New Password', 'trim|xss_clean|required|min_length[8]|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// get passwords
				$password = $this->input->post('password');
				$password_confirm = $this->input->post('password_confirm');

				// encrypt password
				$password_hash = $this->online_booking->encrypt_password($password);

				// verify ok
				if (!$password_hash) {
					$error = 'Password could not be encryped';
				} else {
					// all ok

					// set where
					$where = array(
						'contactID' => $contactID
					);

					// update user
					$data = array(
						'reset_hash' => NULL,
						'reset_at' => NULL,
						'invalid_logins' => 0,
						'locked_until' => NULL,
						'password' => $password_hash,
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);

					$this->db->update('family_contacts', $data, $where, 1);

					if ($this->db->affected_rows() > 0) {
						// redirect
						$this->session->set_flashdata('success', 'Your password has been reset. Please login to access your account.');
						redirect($redirect_to);
						return TRUE;
					} else {
						$error = 'There was an error, please try again.';
					}
				}
			}
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'errors' => $errors
		);

		// load view
		$this->booking_view('online-booking/account/set-password', $data);

	}

	public function register($mode = 'register') {
		// check auth
		if ($mode == 'register') {
			$this->online_booking->require_auth(FALSE);
		} else {
			// profile requires being logged in
			$this->online_booking->require_auth();
		}

		// set defaults
		$title = 'Register';
		$body_class = 'register';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();
		$contact_info = NULL;
		$submit_url = 'account/register';
		if ($mode == 'profile') {
			$title = 'Profile';
			$body_class = 'account profile';
			$contact_info = $this->online_booking->user;
			$submit_url = 'account/profile#details';
		}

		// get brands with newsletter ids
		$where = array(
			'accountID' => $this->online_booking->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// if posted
		if ($this->input->post()) {
			// run validation rules for profile details
			$validation = $this->online_booking->validate_profile_details($mode, $this->input->post());

			if (is_array($validation) && !empty($validation)) {
				$errors = $validation;
			} else {
				// check if a similar user exists already
				if ($mode == 'register') {
					$where = array(
						'first_name' => set_value('first_name'),
						'last_name' => set_value('last_name'),
						'postcode' => set_value('postcode'),
						'accountID' => $this->online_booking->accountID
					);
					$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();
					if ($res->num_rows() == 1) {
						$errors[] = "We think you may have booked with us before. If you have an email address, you can <a href=\"" . site_url('account/reset') . "\">reset your password</a> or contact us and we can try to locate your account.";
					}
				}

				// encrypt password
				$password_hash = NULL;
				if ($mode == 'register' || set_value('password') != '') {
					$password_hash = $this->online_booking->encrypt_password(set_value('password'));
					if (count($errors) == 0 && empty($password_hash)) {
						$errors[] = 'Error encrypting password';
					}
				}

				if (count($errors) == 0) {

					// all ok

					// create family
					if ($mode == 'register') {
						$family_data = array(
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->online_booking->accountID
						);
						$res = $this->db->insert('family', $family_data);
						$familyID = $this->db->insert_id();
					} else {
						$familyID = $this->online_booking->user->familyID;
					}

					// create contact
					$contact_data = array(
						'accountID' => $this->online_booking->accountID,
						'familyID' => $familyID,
						'title' => NULL,
						'first_name' => set_value('first_name'),
						'last_name' => set_value('last_name'),
						'phone' => set_value('phone'),
						'mobile' => set_value('mobile'),
						'workPhone' => set_value('workPhone'),
						'dob' => NULL,
						'address1' => set_value('address1'),
						'address2' => set_value('address2'),
						'address3' => set_value('address3'),
						'town' => set_value('town'),
						'county' => set_value('county'),
						'postcode' => set_value('postcode'),
						'gender' => NULL,
						'gender_specify' => NULL,
						'gender_since_birth' => NULL,
						'sexual_orientation' => NULL,
						'sexual_orientation_specify' => NULL,
						'booking_for' => NULL,
						'medical' => set_value('medical'),
						'disability_info' => set_value('disability_info'),
						'behavioural_info' => set_value('behavioural_information'),
						'ethnic_origin' => set_value('ethnic_origin'),
						'religion' => NULL,
						'religion_specify' => NULL,
						'emergency_contact_1_name' => null_if_empty(set_value('emergency_contact_1_name')),
						'emergency_contact_1_phone' => null_if_empty(set_value('emergency_contact_1_phone')),
						'emergency_contact_2_name' => null_if_empty(set_value('emergency_contact_2_name')),
						'emergency_contact_2_phone' => null_if_empty(set_value('emergency_contact_2_phone')),
						'email' => set_value('email'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);

					if (in_array(set_value('booking_for'), array("child", "contact", "child_and_contact"))) {
						$contact_data['booking_for'] = set_value('booking_for');
					}
					if (set_value('title') != '') {
						$contact_data['title'] = set_value('title');
					}
					if (in_array(set_value('gender'), array('male', 'female', 'please_specify', 'other'))) {
						$contact_data['gender'] = set_value('gender');
						if (set_value('gender')=="please_specify") {
							$contact_data['gender_specify'] = set_value('gender_specify');
						}
					}
					if (in_array(set_value('gender_since_birth'), array("yes", "no", "prefer_not_to_say"))) {
						$contact_data['gender_since_birth'] = set_value('gender_since_birth');
					}
					if (in_array(set_value('sexual_orientation'), array_keys($this->settings_library->sexual_orientations))) {
						$contact_data['sexual_orientation'] = set_value('sexual_orientation');
						if (set_value('sexual_orientation')=="please_specify") {
							$contact_data['sexual_orientation_specify'] = set_value('sexual_orientation_specify');
						}
					}
					if (in_array(set_value('religion'), array_keys($this->settings_library->religions))) {
						$contact_data['religion'] = set_value('religion');
						if (set_value('religion')=="please_specify") {
							$contact_data['religion_specify'] = set_value('religion_specify');
						}
					}
					if (set_value('dob') != '') {
						$contact_data['dob'] = uk_to_mysql_date(set_value('dob'));
					}
					if (!empty($password_hash)) {
						$contact_data['password'] = $password_hash;
					}

					// update profile picture
					$upload_res = $this->crm_library->handle_image_upload('profile_pic', FALSE, $this->online_booking->accountID, 500, 500, 50, 50, TRUE);

					if ($upload_res !== NULL) {
						$image_data = array(
							'name' => $upload_res['client_name'],
							'path' => $upload_res['raw_name'],
							'type' => $upload_res['file_type'],
							'size' => $upload_res['file_size']*1024,
							'ext' => substr($upload_res['file_ext'], 1)
						);
						$contact_data['profile_pic'] = serialize($image_data);
					}

					if ($mode == 'register') {
						$contact_data['marketing_consent'] = intval(set_value('marketing_consent'));
						$contact_data['marketing_consent_date'] = mdate('%Y-%m-%d %H:%i:%s');
						$contact_data['privacy_agreed'] = intval(set_value('privacy_agreed'));
						$contact_data['privacy_agreed_date'] = mdate('%Y-%m-%d %H:%i:%s');
						if (!empty($this->settings_library->get('participant_safeguarding', $this->online_booking->accountID))) {
							$contact_data['safeguarding_agreed'] = intval(set_value('safeguarding_agreed'));
							$contact_data['safeguarding_agreed_date'] = mdate('%Y-%m-%d %H:%i:%s');
						}
						if (!empty($this->settings_library->get('participant_data_protection_notice', $this->online_booking->accountID))) {
							$contact_data['data_protection_agreed'] = intval(set_value('data_protection_agreed'));
							$contact_data['data_protection_agreed_date'] = mdate('%Y-%m-%d %H:%i:%s');
						}
						$contact_data['source'] = null_if_empty(set_value('source'));
						$contact_data['source_other'] = null_if_empty(set_value('source_other'));
						$contact_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$res = $this->db->insert('family_contacts', $contact_data);
					} else {
						$where = array(
							'contactID' => $this->online_booking->user->contactID,
							'accountID' => $this->online_booking->accountID
						);
						$res = $this->db->update('family_contacts', $contact_data, $where, 1);
					}

					if ($this->db->affected_rows() == 1) {
						if ($mode == 'register') {
							$contactID = $this->db->insert_id();
						} else {
							$contactID = $this->online_booking->user->contactID;
						}

						//Delete any existing disability data so it can be updated
						if ($mode != 'register') {
							$where = array(
								'accountID' => $this->online_booking->accountID,
								'contactID' => $contactID
							);
							$query = $this->db->delete('family_disabilities', $where, 1);
						}

						//Add disability data
						if (is_array($this->input->post('disability'))) {
							$disability_data = array(
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->online_booking->accountID,
								'contactID' => $contactID
							);
							foreach ($this->input->post('disability') as $disability => $v) {
								$disability_data[$disability] = ($v=="1" ? 1 : NULL);
							}

							$this->db->insert('family_disabilities', $disability_data);
						}

						// geocode address
						if ($res_geocode = geocode_address($contact_data['address1'], $contact_data['town'], $contact_data['postcode'])) {
							$where = array(
								'contactID' => $contactID,
								'accountID' => $this->online_booking->accountID
							);
							$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('family_contacts');
						}

						// insert note
						if ($mode == 'register') {
							$details = 'Contact: ' . $contact_data['first_name'] . ' ' . $contact_data['last_name'] . '
							By: Participant
							IP: ' . get_ip_address() . '
							Hostname: ' . gethostbyaddr(get_ip_address());
							$summary = 'Marketing Consent: ';
							if ($contact_data['marketing_consent'] == 1) {
								$summary .= 'Yes';
							} else {
								$summary .= 'No';
							}
							$summary .= ', Privacy Agreed: ';
							if ($contact_data['privacy_agreed'] == 1) {
								$summary .= 'Yes';
							} else {
								$summary .= 'No';
							}
							if (!empty($this->settings_library->get('participant_safeguarding', $this->online_booking->accountID))) {
								$summary .= ', Safeguarding Agreed: ';
								if ($contact_data['safeguarding_agreed'] == 1) {
									$summary .= 'Yes';
								} else {
									$summary .= 'No';
								}
							}
							if (!empty($this->settings_library->get('participant_data_protection_notice', $this->online_booking->accountID))) {
								$summary .= ', Data Protection Notice Agreed: ';
								if ($contact_data['data_protection_agreed'] == 1) {
									$summary .= 'Yes';
								} else {
									$summary .= 'No';
								}
							}
							$summary .= ', Source: ';
							if (strtolower($contact_data['source']) == 'other' && !empty($contact_data['source_other'])) {
								$summary .= $contact_data['source_other'];
							} else if (!empty($contact_data['source'])) {
								$summary .= $contact_data['source'];
							} else {
								$summary .= 'Unknown';
							}
							$data = array(
								'type' => 'privacy',
								'summary' => $summary,
								'content' => $details,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'familyID' => $familyID,
								'accountID' => $this->online_booking->accountID
							);
							$res = $this->db->insert('family_notes', $data);

							// update newsletter
							if ($brands->num_rows() > 0) {
								$newsletters = $this->input->post('newsletters');
								if (!is_array($newsletters)) {
									$newsletters = array();
								}
								foreach ($brands->result() as $brand) {
									if (in_array($brand->brandID, $newsletters)) {
										// insert
										$data = array(
											'brandID' => $brand->brandID,
											'contactID' => $contactID,
											'accountID' => $this->online_booking->accountID
										);
										$this->db->insert('family_contacts_newsletters', $data);
									}
								}
							}

							// email user
							if ($this->settings_library->get('send_new_participant', $this->online_booking->accountID) == 1) {
								// set message
								$subject = $this->settings_library->get('email_new_participant_subject', $this->online_booking->accountID);
								$message = $this->settings_library->get('email_new_participant', $this->online_booking->accountID);

								// set tags
								$smart_tags = array(
									'contact_title' => ucwords($contact_data['title']),
									'contact_first' => $contact_data['first_name'],
									'contact_last' => $contact_data['last_name'],
									'contact_email' => $contact_data['email'],
									'company' => $this->online_booking->account->company,
									'password' => set_value('password'),
									'link' => PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN
								);

								// replace
								foreach ($smart_tags as $key => $value) {
									$message = str_replace('{' . $key . '}', $value, $message);
									$subject = str_replace('{' . $key . '}', $value, $subject);
								}

								// send
								$this->crm_library->send_email($contact_data['email'], $subject, $message, array(), FALSE, $this->online_booking->accountID);
							}

							// log user in
							if ($this->online_booking->check_auth($contact_data['email'], set_value('password'))) {
								// check if redirecting
								$redirect_to = $this->session->userdata('redirect_to');

								if (!empty($redirect_to)) {
									// unset redirect
									$this->session->unset_userdata('redirect_to');
								} else {
									$redirect_to = 'account';
								}

								// tell user
								$success = 'Thank you for registering ' . $contact_data['first_name'];
								if (strpos($redirect_to, 'book') !== FALSE) {
										$success .= ', continue your booking below';
								} else {
									$success .= ', you are now logged in to your account';
								}
								$this->session->set_flashdata('success',  $success);

								// redirect
								redirect($redirect_to);
								return TRUE;
							}
						} else {
							// tell user
							$success = 'Your details have been updated';
							$this->session->set_flashdata('success',  $success);
							$redirect_to = 'account';

							// redirect
							redirect($redirect_to);
							return TRUE;
						}
					} else {
						$error = 'Error saving data';
					}
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

		//We can be brought here after a booking, where validation has already been done.
		//So check for any additional errors which have been set.
		if ($this->session->flashdata('errors')) {
			$errors = array_merge($errors, $this->session->flashdata('errors'));
		}

		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'fields' => get_fields("account_holder"),
			'info' => $info,
			'brands' => $brands,
			'mode' => $mode,
			'contact_info' => $contact_info,
			'existing_newsletters' => [],
			'tab' => $mode,
			'submit_url' => $submit_url
		);
		if ($mode == 'profile') {
			$this->booking_view('online-booking/account/register', $data, 'templates/online-booking-account');
		} else {
			$this->booking_view('online-booking/account/register', $data);
		}
	}

	public function profile() {
		return $this->register('profile');
	}

	public function participants($childID = NULL) {
		// check auth
		$this->online_booking->require_auth();

		if (!empty($childID)) {
			return $this->participant($childID);
		}

		// set defaults
		$title = 'Participants';
		$body_class = 'account participants';
		$tab = 'participants';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// get children
		$where = array(
			'family_children.familyID' => $this->online_booking->user->familyID,
			'family_children.accountID' => $this->online_booking->accountID
		);
		$res_children = $this->db->select('family_children.*, orgs.name as school')
		->from('family_children')
		->join('orgs', 'family_children.orgID = orgs.orgID', 'left')
		->where($where)
		->order_by('first_name asc, last_name asc')
		->get();

		if ($res_children->num_rows() == 0) {
			$info = 'No participants added yet';
		}

		// check for flashdata
		if ($this->session->flashdata('child_success')) {
			$success = $this->session->flashdata('child_success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'children' => $res_children,
			'tab' => $tab,
		);
		$this->booking_view('online-booking/account/participants', $data, 'templates/online-booking-account');
	}

	public function shapeup() {

		// check auth
		$this->online_booking->require_auth();

		// check for shapeup enabled on account
		if ($this->online_booking->account->addon_shapeup != 1 && $this->online_booking->account->addons_all != 1) {
			show_403();
		}

		// set defaults
		$title = 'Shape Up';
		$body_class = 'account shapeup';
		$tab = 'shapeup';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// get bookings
		$where = array(
			'bookings_cart.accountID' => $this->online_booking->accountID,
			'bookings_cart.type' => 'booking',
			'bookings_cart_sessions.contactID' => $this->online_booking->user->contactID,
			$this->db->dbprefix('bookings') . '.`register_type` LIKE' => '%shapeup%'
		);
		$res = $this->db->select('bookings_cart.cartID, bookings.startDate, bookings.endDate, bookings.name')
		->from('bookings_cart')
		->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
		->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
		->where($where)
		->group_by('bookings_cart_sessions.bookingID')
		->order_by('bookings.startDate desc, bookings.endDate desc')
		->get();

		if ($res->num_rows() == 0) {
			$info = 'No shape up bookings found';
		}

		// check for flashdata
		if ($this->session->flashdata('child_success')) {
			$success = $this->session->flashdata('child_success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'bookings' => $res,
			'tab' => $tab,
		);
		$this->booking_view('online-booking/account/shapeup', $data, 'templates/online-booking-account');
	}

	public function shapeup_view($cartID) {

		// check auth
		$this->online_booking->require_auth();

		// check for shapeup enabled on account
		if ($this->online_booking->account->addon_shapeup != 1 && $this->online_booking->account->addons_all != 1) {
			show_403();
		}

		// set defaults
		$title = 'Shape Up';
		$body_class = 'account shapeup';
		$tab = 'shapeup';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// get booking
		$where = array(
			'bookings_cart.accountID' => $this->online_booking->accountID,
			'bookings_cart.type' => 'booking',
			'bookings_cart.cartID' => $cartID,
			'bookings_cart_sessions.contactID' => $this->online_booking->user->contactID,
			$this->db->dbprefix('bookings') . '.`register_type` LIKE' => '%shapeup%'
		);
		$res = $this->db->select('bookings_cart.cartID, bookings.startDate, bookings.endDate, bookings.name')
		->from('bookings_cart')
		->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
		->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
		->where($where)
		->group_by('bookings_cart_sessions.bookingID')
		->order_by('bookings.startDate desc, bookings.endDate desc')
		->get();

		if ($res->num_rows() == 0) {
			show_404();
		} else {
			foreach ($res->result() as $cart_info) {}
		}

		// get sessions
		$where = array(
			'bookings_cart.accountID' => $this->online_booking->accountID,
			'bookings_cart.type' => 'booking',
			'bookings_cart.cartID' => $cartID,
			'bookings_cart_sessions.contactID' => $this->online_booking->user->contactID,
			'bookings_cart_sessions.attended' => 1,
			'bookings_cart_sessions.shapeup_weight >' => 0
		);
		$res = $this->db->select('bookings_cart_sessions.*, bookings_lessons.day')
		->from('bookings_cart')
		->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
		->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
		->where($where)
		->group_by('bookings_cart_sessions.sessionID')
		->order_by('bookings_cart_sessions.date asc')
		->get();

		// check for flashdata
		if ($this->session->flashdata('child_success')) {
			$success = $this->session->flashdata('child_success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'event' => $cart_info->name,
			'sessions' => $res,
			'tab' => $tab,
		);
		$this->booking_view('online-booking/account/shapeup-view', $data, 'templates/online-booking-account');
	}

	public function privacy($mode = 'profile') {
		// check auth
		$this->online_booking->require_auth();

		// set defaults
		$title = 'Data & Privacy';
		$body_class = 'account privacy';
		$tab = 'privacy';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();

		// load libraries
		$this->load->library('form_validation');

		// get brands with newsletter ids
		$where = array(
			'accountID' => $this->online_booking->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		$where = array(
			'contactID' => $this->online_booking->user->contactID,
			'accountID' => $this->online_booking->accountID
		);

		// if posted
		if ($this->input->post()) {
			// set validation rules
			$this->form_validation->set_rules('marketing_consent', 'Marketing Consent', 'trim|xss_clean');
			$this->form_validation->set_rules('privacy_agreed', 'Agreement to privacy policy', 'trim|xss_clean|required|callback_is_checked');
			if (!empty($this->settings_library->get('participant_safeguarding', $this->online_booking->accountID))) {
				$this->form_validation->set_rules('safeguarding_agreed', 'Agreement to safeguarding policy', 'trim|xss_clean|required|callback_is_checked');
			}
			if (!empty($this->settings_library->get('participant_data_protection_notice', $this->online_booking->accountID))) {
				$this->form_validation->set_rules('data_protection_agreed', 'Agreement to data protection notice', 'trim|xss_clean|required|callback_is_checked');
			}

			$this->form_validation->set_rules('source', 'Source', 'trim|xss_clean');
			if ($this->input->post('source') == 'Other') {
				$this->form_validation->set_rules('source_other', 'Other (Please specify)', 'trim|required|xss_clean');
			} else {
				$this->form_validation->set_rules('source_other', 'Other (Please specify)', 'trim|xss_clean');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				$contact_data = array(
					'marketing_consent' => intval(set_value('marketing_consent')),
					'marketing_consent_date' => mdate('%Y-%m-%d %H:%i:%s'),
					'privacy_agreed' => intval(set_value('privacy_agreed')),
					'privacy_agreed_date' => mdate('%Y-%m-%d %H:%i:%s'),
					'source' => null_if_empty(set_value('source')),
					'source_other' => null_if_empty(set_value('source_other')),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if (!empty($this->settings_library->get('participant_data_protection_notice', $this->online_booking->accountID))) {
					$contact_data["data_protection_agreed"] = intval(set_value('data_protection_agreed'));
					$contact_data["data_protection_agreed_date"] = mdate('%Y-%m-%d %H:%i:%s');
				}

				if (!empty($this->settings_library->get('participant_safeguarding', $this->online_booking->accountID))) {
					$contact_data["safeguarding_agreed"] = intval(set_value('safeguarding_agreed'));
					$contact_data["safeguarding_agreed_date"] = mdate('%Y-%m-%d %H:%i:%s');
				}

				$res = $this->db->update('family_contacts', $contact_data, $where, 1);

				// insert note
				$details = 'Contact: ' . $this->online_booking->user->first_name . ' ' . $this->online_booking->user->last_name . '
				By: Participant
				IP: ' . get_ip_address() . '
				Hostname: ' . gethostbyaddr(get_ip_address());
				$summary = 'Marketing Consent: ';
				if ($contact_data['marketing_consent'] == 1) {
					$summary .= 'Yes';
				} else {
					$summary .= 'No';
				}
				$summary .= ', Privacy Agreed: ';
				if ($contact_data['privacy_agreed'] == 1) {
					$summary .= 'Yes';
				} else {
					$summary .= 'No';
				}
				$summary .= ', Source: ';
				if (strtolower($contact_data['source']) == 'other' && !empty($contact_data['source_other'])) {
					$summary .= $contact_data['source_other'];
				} else if (!empty($contact_data['source'])) {
					$summary .= $contact_data['source'];
				} else {
					$summary .= 'Unknown';
				}
				$data = array(
					'type' => 'privacy',
					'summary' => $summary,
					'content' => $details,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'familyID' => $this->online_booking->user->familyID,
					'accountID' => $this->online_booking->accountID
				);
				$res = $this->db->insert('family_notes', $data);

				// update newsletter
				$this->db->delete('family_contacts_newsletters', $where);
				if ($brands->num_rows() > 0) {
					$newsletters = $this->input->post('newsletters');
					if (!is_array($newsletters)) {
						$newsletters = array();
					}
					foreach ($brands->result() as $brand) {
						if (in_array($brand->brandID, $newsletters)) {
							// insert
							$data = array(
								'brandID' => $brand->brandID,
								'contactID' => $this->online_booking->user->contactID,
								'accountID' => $this->online_booking->accountID
							);
							$this->db->insert('family_contacts_newsletters', $data);
						}
					}
				}

				// set message
				$subject = $this->settings_library->get('participant_consent_changed_subject', $this->online_booking->accountID);
				$message = $this->settings_library->get('participant_consent_changed', $this->online_booking->accountID);

				// set tags
				$smart_tags = array(
					'first_name' => $this->online_booking->user->first_name,
					'changed_by' => $this->online_booking->user->first_name . ' ' . $this->online_booking->user->last_name,
					'changed_at' => date('d/m/Y H:i'),
					'company' => $this->online_booking->account->company,
					'link' => PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account/privacy/'
				);

				// replace
				foreach ($smart_tags as $key => $value) {
					$message = str_replace('{' . $key . '}', $value, $message);
					$subject = str_replace('{' . $key . '}', $value, $subject);
				}

				// send
				$this->crm_library->send_email($this->online_booking->user->email, $subject, $message, array(), FALSE, $this->online_booking->accountID);

				// tell user
				$success = 'Your privacy details have been updated';
				$this->session->set_flashdata('success',  $success);

				// check if redirecting
				$redirect_to = $this->session->userdata('redirect_to');

				if (!empty($redirect_to)) {
					// unset redirect
					$this->session->unset_userdata('redirect_to');
				} else {
					$redirect_to = 'account';
				}

				// redirect
				redirect($redirect_to);
				return TRUE;
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

		// get existing newsletters
		$existing_newsletters = array();
		$res = $this->db->from('family_contacts_newsletters')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$existing_newsletters[$row->brandID] = $row->brandID;
			}
		}
		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'brands' => $brands,
			'marketing_consent' => $this->online_booking->user->marketing_consent,
			'existing_newsletters' => $existing_newsletters,
			'source' => $this->online_booking->user->source,
			'source_other' => $this->online_booking->user->source_other,
			'tab' => $tab,
			'mode' => $mode
		);
		if ($mode == 'confirm') {
			$this->booking_view('online-booking/account/privacy', $data);
		} else {
			$this->booking_view('online-booking/account/privacy', $data, 'templates/online-booking-account');
		}
	}

	/**
	 * check if a field is checked
	 * @param  string $value
	 * @return boolean
	 */
	public function is_checked($value) {

		if (empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check current password is correct
	 * @param  string $password
	 * @return mixed
	 */
	public function check_current_password($password) {
		if (empty($password)) {
			return TRUE;
		}
		// get logged in users password hash
		$where = array(
			'contactID' => $this->online_booking->user->contactID,
			'accountID' => $this->online_booking->accountID
		);
		$res = $this->db->select('password')->from('family_contacts')->where($where)->limit(1)->get();
		if ($res->num_rows() == 1) {
			foreach($res->result() as $row) {
				if (password_verify($password, $row->password)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

}

/* End of file Account.php */
/* Location: ./application/controllers/online-booking/Account.php */
