<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subscriptions extends MY_Controller {

	private $bookingID;
	private $in_crm = TRUE;
	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}

		$this->load->library('gocardless_library');
	}

	public function index($bookingID = NULL) {
		if ($bookingID == NULL) {
			show_404();
		}

		// if so, check user exists
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

		// set defaults
		$icon = 'sack-dollar';
		$tab = 'subscriptions';
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
		$breadcrumb_levels['bookings/subscriptions/' . $bookingID] = 'Subscriptions';
		$page_base = 'bookings/subscriptions/' . $bookingID;
		$section = 'bookings';
		$title = 'Subscriptions';
		$buttons = '<a class="btn btn-success" href="' . site_url('bookings/subscriptions/' . $bookingID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID,
			'familyID IS NULL' => NULL
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			// 'name' => NULL,
			// 'code' => NULL,
			// 'search' => NULL
		);

		// run query
		$res = $this->db->from('subscriptions')->where($where)->where($search_where, NULL, FALSE)->order_by('subName asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('subscriptions')->where($where)->where($search_where, NULL, FALSE)->order_by('subName asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// get sub ids
		$subIDs = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$subIDs[] = $row->subID;
			}
		}

		$session_types = array();

		if(count($subIDs) > 0) {
			$sub_types = $this->db
				->select('subscriptions_lessons_types.subID,
					GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('lesson_types') . ' .name SEPARATOR \'!SEPARATOR!\') AS types')
				->from('subscriptions_lessons_types')
				->join('lesson_types', 'subscriptions_lessons_types.typeID = lesson_types.typeID', 'left')
				->where_in('subscriptions_lessons_types.subID', $subIDs)
				->group_by('subscriptions_lessons_types.subID')->get();

			if($sub_types->num_rows() > 0) {
				foreach($sub_types->result() as $row) {
					$types = explode("!SEPARATOR!", $row->types);
					if (is_array($types) && count($types) > 0) {
						foreach ($types as $item) {
							if ($item != "other") {
								$session_types[$row->subID][$item] = $item;
							}
						}
					}
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => $title,
			'tab' => $tab,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'booking_info' => $booking_info,
			'bookingID' => $bookingID,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'subs' => $res,
			'type' => $booking_info->type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'session_types' => $session_types,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'goCardless' => $this->gocardless_library->valid_config()
		);

		$this->crm_view('bookings/subscriptions', $data);
	}

	public function edit($subscriptionID = NULL, $bookingID = NULL) {

		$sub_info = new stdClass();
		$history = new stdClass();

		if($subscriptionID != NULL) {
			// check if numeric
			if (!ctype_digit($subscriptionID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'subscriptions.subID' => $subscriptionID,
				'subscriptions.accountID' => $this->auth->user->accountID
			);

			$query = $this->db
				->select('subscriptions.*, GROUP_CONCAT('.$this->db->dbprefix("lesson_types").'.typeID) as types')
				->from('subscriptions')
				->join('subscriptions_lessons_types', 'subscriptions.subID = subscriptions_lessons_types.subID', 'inner')
				->join('lesson_types', 'lesson_types.typeID = subscriptions_lessons_types.typeID', 'left')
				->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$sub_info = $row;
				$bookingID = $sub_info->bookingID;
			}
		}

		// required
		if ($bookingID == NULL) {
			show_404();
		}

		// save booking ID
		$this->bookingID = $bookingID;

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

		if ($booking_info->type != 'event' && $booking_info->project != 1) {
			show_404();
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Subscription';
		if ($subscriptionID != NULL) {
			$submit_to = 'bookings/subscriptions/edit/' . $subscriptionID;
			$title = $sub_info->subName;
		} else {
			$submit_to = 'bookings/subscriptions/' . $bookingID . '/new/';
		}
		$return_to = 'bookings/subscriptions/' . $bookingID;
		$icon = 'sack-dollar';
		$tab = 'subscriptions';
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
		$breadcrumb_levels['bookings/subscriptions/' . $bookingID] = 'Subscriptions';
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$participants_id_field = 'childID';
		$participants_table = 'family_children';

		if (strpos($booking_info->register_type, 'individuals') === 0) {
			$participants_id_field = 'contactID';
			$participants_table = 'family_contacts';
		}

		if($subscriptionID !== NULL) {


			//get subscription history
			$where = array(
				'participant_subscriptions.subID' => $subscriptionID,
				'participant_subscriptions.accountID' => $this->auth->user->accountID
			);

			$history = $this->db
				->select('subscriptions.subID, status, subName, family_children.first_name, family_children.last_name, family_contacts.first_name as contact_name, family_contacts.last_name as contact_last_name, gc_subscription_id, participant_subscriptions.last_payment_date, participant_subscriptions.childID, participant_subscriptions.contactID')
				->from('participant_subscriptions')
				->join('family_children', 'participant_subscriptions.childID = family_children.childID', 'left')
				->join('family_contacts', 'participant_subscriptions.contactID = family_contacts.contactID', 'left')
				->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID', 'left')
				->where($where)
				->get();
			$cartArray = array();
			foreach($history->result() as $result){
				$childID = $result->childID;
				$contactID = $result->contactID;
				if($childID != null &&  $childID != ""){
					$query = $this->db->select("cartID")->from("bookings_cart_subscriptions")->WHERE("subID", $result->subID)->WHERE("childID",$childID)->get();
					foreach($query->result() as $result1){
						$cartArray[$result->childID] = $result1->cartID;
					}
				}else{
					$query = $this->db->select("cartID")->from("bookings_cart_subscriptions")->WHERE("subID", $result->subID)->WHERE("childID",null)->WHERE("contactID", $contactID)->get();
					foreach($query->result() as $result1){
						$cartArray[$result->contactID] = $result1->cartID;
					}
				}
			}
		}

		// session types
        $lesson_types = array();
		$lesson_types[0] = "Select All";
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
        $res = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();
        if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_types[$row->typeID] = $row->name;
			}
		}

		if($this->input->post()) {

			$this->form_validation->set_rules('subName', 'subName', 'trim|xss_clean|required');
			$this->form_validation->set_rules('frequency', 'Frequency', 'trim|xss_clean|required');
			$this->form_validation->set_rules('payment_provider', 'Provider', 'trim|xss_clean|required');
            $this->form_validation->set_rules('price', 'Price', 'trim|xss_clean|required|numeric|greater_than[0]|less_than[10000]');
			$this->form_validation->set_rules('types', 'Types', 'trim|xss_clean|required');

			if($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				$payment_provider = set_value('payment_provider');

				if($payment_provider == 'stripe') {
					try{
						\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $this->cart_library->accountID));
						//create product in stripe
						$product = \Stripe\Product::create([
							'name' => set_value('subName'),
							'type' => 'service'
						]);
					} catch (Exception $e) {
						$errors[] = $e->getMessage();
					}
				}

				$sub_data = array(
					'subName' => set_value('subName'),
					'no_of_sessions_per_week' => set_value('no_of_sessions_per_week'),
					'session_cut_off' => set_value('session_cut_off'),
					'price' => set_value('price'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'payment_provider' => $payment_provider,
					'accountID' => $this->auth->user->accountID
				);

				if($subscriptionID == NULL){
					$sub_data['frequency'] = set_value('frequency');
					$sub_data['bookingID'] = $this->bookingID;
					$sub_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				if(count($errors) == 0) {

					if($subscriptionID == NULL){
						$query = $this->db->insert('subscriptions', $sub_data);

						$subscriptionID = $this->db->insert_id();
						$just_added = TRUE;
					} else {
						$where = array(
							'subID' => $subscriptionID,
							'accountID' => $this->auth->user->accountID
						);
						$res = $this->db->select('*')
							->from('subscriptions')
							->where($where)
							->get();

						//Cancel each subscription and email user
						if($res->num_rows() > 0) {
							foreach($res->result() as $item) {
								$sub_data['frequency'] = $item->frequency;
								$sub_data['payment_provider'] = $item->payment_provider;
								$payment_provider = $item->payment_provider;
							}
						}

						// update
						$query = $this->db->update('subscriptions', $sub_data, $where);
					}
                    if($this->db->affected_rows() == 1) {
                        //add in session types
                        $types = $this->input->post('types');
                        if(!is_array($types)) {
                            $types = array();
                        }
                        // remove existing
					    $where = array(
						    'subID' => $subscriptionID,
						    'accountID' => $this->auth->user->accountID
                        );
                        $this->db->delete('subscriptions_lessons_types', $where);

                        if(count($types) > 0) {
							$flag = 0;
							if(in_array("All Session Types", $types)) {$types = $lesson_types; $flag = 1; }
							foreach($types as $key => $type) {
								if($flag == 0) { $key = $type; }
								if($key != 0){
									$data = array(
										'subID' => $subscriptionID,
										'bookingID' => $bookingID,
										'typeID' => $key,
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'accountID' => $this->auth->user->accountID
									);

									$this->db->insert('subscriptions_lessons_types', $data);
								}
							}
                        }

						if($payment_provider == 'stripe') {

							$productID = $product->id;

							$price = \Stripe\Price::create([
								'nickname' => $sub_data['subName'],
								'product' => $productID,
								'unit_amount' => round($sub_data['price'] * 100),
								'currency' => currency_code(),
								'recurring' => [
									'interval' => substr($sub_data['frequency'], 0, -2)
								]
							]);

							$data = array(
								'stripe_product_id' => $productID,
								'stripe_price_id' => $price->id
							);

							$where = array(
								'subID' => $subscriptionID,
								'accountID' => $this->auth->user->accountID
							);

							$this->db->update('subscriptions', $data, $where);
						}

						if (isset($just_added)) {
							$this->session->set_flashdata('success', set_value('name') . ' Subscription has been created successfully.');
						} else {
							//check if there are active subscriptions
							$where = array(
								'accountID' => $this->auth->user->accountID,
								'subID' => $subscriptionID,
								'status' => 'active'
							);

							$res = $this->db->from('participant_subscriptions')->where($where)->get();

							if($res->num_rows() > 0) {
								//update go cardless subscriptions
								if(!$this->gocardless_library->update_subscription($subscriptionID)) {
									$this->session->set_flashdata('info', 'Error saving data, please try again.');
								}
							}

							$this->session->set_flashdata('success', set_value('name') . ' Subscription has been updated successfully.');
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
		} else if ($this->session->flashdata('error')) {
			$info = $this->session->flashdata('error');
		}

		$gc_error = NULL;
		$stripe_error = NULL;

		if ($this->gocardless_library->valid_config() !== TRUE) {
			$gc_error = 'Please complete the information for GoCardless in ' . anchor('settings/integrations', 'Settings > Integrations') . '.';
		}

		$stripe_pk = $this->settings_library->get('stripe_pk', $this->cart_library->accountID);
		$stripe_sk = $this->settings_library->get('stripe_sk', $this->cart_library->accountID);
		if (empty($stripe_pk) || empty($stripe_sk)) {
			$stripe_error = 'Please complete the information for Stripe in ' . anchor('settings/integrations', 'Settings > Integrations') . '.';
		}

        $data = array(
            'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'sub_info' => $sub_info,
			'history' => $history,
			'participants_id_field' => $participants_id_field,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'lesson_types' => $lesson_types,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'gc_error' => $gc_error,
			'cartArray' => $cartArray,
			'stripe_error' => $stripe_error
		);

		$this->crm_view('bookings/subscription', $data);
	}

	public function remove($subID = NULL) {
		if($subID === NULL) {
			show_404();
		}

		$where = array(
			'subID' => $subID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('subscriptions')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		foreach ($query->result() as $row) {
			$sub_info = $row;

			//get participants and cancel subscription
			$where = array(
				'subscriptions.subID' => $subID,
				'participant_subscriptions.accountID' => $this->auth->user->accountID,
				'participant_subscriptions.status !=' => 'cancelled'
			);

			$res = $this->db->select('participant_subscriptions.id as psID, participant_subscriptions.gc_subscription_id, participant_subscriptions.childID, subscriptions.subID, subscriptions.subName, subscriptions.frequency, subscriptions.price, subscriptions.bookingID')
				->from('participant_subscriptions')
				->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID')
				->where($where)
				->get();

			//Cancel each subscription and email user
			if($res->num_rows() > 0) {
				foreach($res->result() as $sub) {
					$this->cancel($sub->subID, $sub->childID, TRUE);
				}
			}

			$where = array(
				'subID' => $subID,
				'accountID' => $this->auth->user->accountID
			);

			// all ok, delete
			$query = $this->db->delete('subscriptions', $where);

			if ($this->db->affected_rows() >= 1) {
				$this->session->set_flashdata('success', 'Subscription has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', 'Subscription could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings/subscriptions/' . $sub_info->bookingID;

			redirect($redirect_to);
		}
	}


	/* Session Change for Subscription */

	public function session($cartID){
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
		$args['accountID'] = $this->auth->user->accountID;
		$args['in_crm'] = TRUE;

		$blockID = $blockIDs;

		$this->cart_library->init($args);


		$title = 'Update Sessions';
		$body_class = 'book';
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
		$blocks = $this->cart_library->get_blocks($where, $blocks_where);

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

		if($this->auth->has_features('online_booking_subscription_module')) {
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
							'label' => $sub->subName . ' (' . currency_symbol() . $sub->price . ' - ' . ucfirst($sub->frequency) . ')',
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
				$monitoring_existing = (array)$this->input->post('monitoring');
				$register_type = $this->input->post('register_type');
				$selected_subs = (array) $this->input->post('subscriptions');

				//Subscription only event
				if($this->auth->has_features('online_booking_subscription_module') && $block->subscriptions_only === '1'){
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
								if (isset($already_booked_sessions[$lessonID][$date]) && in_array($participantID, $already_booked_sessions[$lessonID][$date])) {
									// session already booked in another session, skip
								} else {
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
				$res = $this->cart_library->process_block($blockID, $selected_lessons);


				if (!is_string($res)) {
					$blocks = $this->cart_library->get_blocks($where, $blocks_where);
					$selected_lessons = $res;
				} else {
					$success = 'Your booking has been updated successfully';
					$this->session->set_flashdata('success', $success);
					redirect("bookings/subscriptions/session/".$cartID);
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
					$info = $this->settings_library->get_label('participant', $this->cart_library->accountID) . ' already has this event in their cart which you can edit below';
				}
			}

		}
		// output
		$data = array(
			'title' => $title,
			'register_type' => $register_type,
			'body_class' => $body_class,
			'blockID' => $blockID,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'blocks' => $blocks,
			'participants' => $participants,
			'selected_participants' => $selected_participants,
			'subs' => $subs,
			'selected_subs' => $selected_subs,
			'subscriptions_only' => $subscriptions_only,
			'selected_lessons' => $selected_lessons,
			'new_participants_link' => $new_participants_link,
			'new_adults_link' => $new_adults_link,
			'already_booked_sessions' => $already_booked_sessions,
			'already_booked_subscriptions' => $already_booked_subscriptions,
			'breadcrumb_levels' => $breadcrumb_levels,
			'subscription_status' => $subscription_status,
			'in_crm' => true,
			'cartID' => $cartID
		);

		$this->crm_view('bookings/subscription_session', $data);
	}


	/**
	 * cancel a subscription for user
	 * @param int $planID
	 * @param int $childID
	 * @param boolean $remove
	 * @return mixed
	 */
	public function cancel($subID, $participantID, $remove = FALSE) {

		// check params
		if (empty($subID)) {
			show_404();
		}

		$where = array(
			'subscriptions.subID' => $subID,
			'subscriptions.accountID' => $this->auth->user->accountID,
		);

		$res = $this->db->from('subscriptions')->where($where)->limit(1)->get();

		foreach($res->result() as $sub) {
			$sub_info = $sub;
		}

		$where = array(
			'bookings.bookingID' => $sub_info->bookingID,
			'bookings.accountID' => $this->auth->user->accountID,
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

		$where = array(
			'subscriptions.subID' => $subID,
			'subscriptions.accountID' => $this->auth->user->accountID,
			'participant_subscriptions.status !=' => 'cancelled',
			$participants_id_field => $participantID
		);

		// run query
		$query = $this->db->select('subscriptions.*, participant_subscriptions.' . $participants_id_field . ', participant_subscriptions.gc_subscription_id,  participant_subscriptions.stripe_subscription_id')
			->from('subscriptions')
			->join('participant_subscriptions', 'subscriptions.subID = participant_subscriptions.subID')
			->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$sub_info = $row;
		}

		$cancel = FALSE;

		switch($sub_info->payment_provider) {
			case 'gocardless':
				if($sub_info->gc_subscription_id != NULL) {
					$cancel = $this->gocardless_library->cancel_subscription($sub_info->gc_subscription_id);
				}
				break;
			case 'stripe':
				if($sub_info->stripe_subscription_id !== NULL) {
					try {
						\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $this->cart_library->accountID));
						$subscription = \Stripe\Subscription::retrieve($sub_info->stripe_subscription_id);
						$qty = $subscription->items->data[0]->quantity;
						if ($qty > 1) {
							$cancel = $subscription->update($sub_info->stripe_subscription_id,
								['quantity' => $qty - 1]);
						} else {
							$cancel = $subscription->update($sub_info->stripe_subscription_id,
								['cancel_at_period_end' => true]);
						}
						if (isset($cancel) && $cancel != FALSE) {
							$cancel = TRUE;
						}
					} catch(\Stripe\Error\Card $e) {
						$this->session->set_flashdata('error', $e->getMessage());
					} catch(\Stripe\Exception\InvalidRequestException $e) {
						$this->session->set_flashdata('error', $e->getMessage());
					} catch(\Stripe\Exception\AuthenticationException $e) {
						$this->session->set_flashdata('error', $e->getMessage());
					}
				}
				break;
		}

		if($cancel === TRUE) {

			$where = array (
				$participants_table . '.' . $participants_id_field => $sub_info->$participants_id_field,
				'family_contacts.accountID' => $this->auth->user->accountID
			);

			$res = $this->db->select('family_contacts.first_name,family_contacts.last_name, family_contacts.email, family_contacts.familyID, family_contacts.contactID')
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

			//get company name
			$where = array(
				'accountID' => $this->auth->user->accountID
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
				'bookings.accountID' => $this->auth->user->accountID,
				'bookings.bookingID' => $sub_info->bookingID
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
				'subscriptions_lessons_types.subID' => $sub_info->subID,
				'subscriptions_lessons_types.accountID' => $this->auth->user->accountID
			);


			$res = $this->db->select('GROUP_CONCAT(' . $this->db->dbprefix('lesson_types') .'.name SEPARATOR\', \') as types')
				->from('lesson_types')
				->join('subscriptions_lessons_types', 'lesson_types.typeID = subscriptions_lessons_types.typeID', 'inner')
				->where($where)
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $types) break;
			}

			$smart_tags['subscription_details'] =
				'Name: ' . $sub_info->subName . '<br>Frequency: ' . ucfirst($sub_info->frequency) . '<br>Rate: ' . currency_symbol() . $sub_info->price . '<br>No. of Sessions per Week: ' . $sub_info->no_of_sessions_per_week . '<br>';

			$smart_tags['link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';
			$smart_tags['login_link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';

			// get email template
			$subject = $this->settings_library->get('email_cancel_subscription_subject', $this->auth->user->accountID);
			$email_html = $this->settings_library->get('email_cancel_subscription', $this->auth->user->accountID);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			//Send Email to staff
			$email = $this->settings_library->get('email', $this->cart_library->accountID);
			if(!empty($email)){
				// get email template
				$staff_subject = $this->settings_library->get('staff_cancel_subscription_subject', $this->cart_library->accountID);
				$staff_email_html = $this->settings_library->get('staff_cancel_subscription_body', $this->cart_library->accountID);
				if(!empty($staff_subject) && !empty($staff_email_html)){
					// replace smart tags in email
					foreach ($smart_tags as $key => $value) {
						$staff_email_html = str_replace('{' . $key . '}', $value, $staff_email_html);
						$staff_subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $staff_subject);
					}
				}
				$this->crm_library->send_email($email, $staff_subject, $staff_email_html, array(), TRUE, $this->cart_library->accountID);
			}

			// send
			if ($this->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->auth->user->accountID)) {
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
					'accountID' => $this->auth->user->accountID
				);

				$this->db->insert('family_notifications', $data);
			}

			$data = array(
				'status' => 'cancelled',
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
			);

			$where = array(
				'subID' => $subID,
				'accountID' => $this->auth->user->accountID,
				$participants_id_field => $sub_info->$participants_id_field
			);

			$this->db->update('participant_subscriptions', $data, $where, 1);

			if($remove === TRUE) {
				return TRUE;
			}

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', 'Subscription has been cancelled successfully.');
			} else {
				$this->session->set_flashdata('error', 'There was an error cancelling the subscription.');
			}
		} else {
			$this->session->set_flashdata('error', 'There was an error cancelling the subscription.');
		}

		// determine which page to send the user back to
		$redirect_to = 'bookings/subscriptions/edit/' . $sub_info->subID;

		redirect($redirect_to);
	}
}
