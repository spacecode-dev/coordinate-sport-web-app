<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cart_library {

	private $CI;
	public $accountID = FALSE;
	public $familyID = FALSE;
	public $contactID = FALSE;
	public $cartID = NULL;
	public $count = 0;
	public $contact_name = NULL;
	public $contact_postcode = NULL;
	public $contact_blacklisted = FALSE;
	public $contact_tags = array();
	public $cart_type = 'cart';
	public $online_booking_subscription_module = 0;
	private $family_credit_limit = 0;
	private $family_account_balance = 0;
	private $bookingIDs = array();
	private $bookingID = NULL;
	private $blockIDs = array();
	private $errors = array();
	private $in_crm = FALSE;
	private $voucherIDs_just_added = [];
	private $vouchers_used = [];

	public function __construct($params) {
		// get CI instance
		$this->CI =& get_instance();

		return $this->init($params);
	}

	public function init($params, $flagValue = null) {
		if (!is_array($params) || !isset($params['accountID']) || empty($params['accountID'])) {
			return FALSE;
		}

		// reset all
		$this->accountID = FALSE;
		$this->familyID = FALSE;
		$this->contactID = FALSE;
		$this->cartID = NULL;
		$this->count = 0;
		$this->contact_name = NULL;
		$this->contact_postcode = NULL;
		$this->contact_blacklisted = FALSE;
		$this->contact_tags = array();
		$this->cart_type = 'cart';
		$this->family_credit_limit = 0;
		$this->family_account_balance = 0;
		$this->bookingIDs = array();
		$this->blockIDs = array();
		$this->errors = array();
		$this->in_crm = FALSE;

		$this->accountID = $params['accountID'];

		$this->online_booking_subscription_module = 0;
		$query = $this->CI->db->from("accounts")->where("accountID", $this->accountID)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$this->online_booking_subscription_module = $result->addon_online_booking_subscription_module;
			}
		}

		// check if in crm
		if (isset($params['in_crm']) && $params['in_crm'] === TRUE) {
			$this->in_crm = TRUE;
		}

		// look up contact
		if (isset($params['contactID']) && !empty($params['contactID'])) {
			$where = array(
				'accountID' => $this->accountID,
				'contactID' => $params['contactID']
			);
			$res = $this->CI->db->select('familyID, first_name, last_name, postcode, blacklisted')->from('family_contacts')->where($where)->get();
			if ($res->num_rows() == 1) {
				foreach ($res->result() as $row) {
					$this->contactID = $params['contactID'];
					$this->familyID = $row->familyID;
					$this->contact_name = $row->first_name . ' ' . $row->last_name;
					$this->contact_postcode = $row->postcode;
					if ($row->blacklisted == 1) {
						$this->contact_blacklisted = TRUE;
					}
				}

				// get contact tags
				$where = array(
					'accountID' => $this->accountID,
					'contactID' => $this->contactID
				);
				$res = $this->CI->db->select('tagID')->from('family_contacts_tags')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$this->contact_tags[] = $row->tagID;
					}
				}

				// get default credit limit
				$this->family_credit_limit = $this->CI->settings_library->get('default_credit_limit', $this->accountID);

				// check if overridden on family level
				$where = array(
					'accountID' => $this->accountID,
					'familyID' => $this->familyID
				);
				$res = $this->CI->db->select('account_balance, credit_limit')->from('family')->where($where)->limit(1)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						if (!empty($row->credit_limit)) {
							$this->family_credit_limit = $row->credit_limit;
						}
						$this->family_account_balance = $row->account_balance;
					}
				}

				// check if credit limits turned off
				if ($this->CI->settings_library->get('enable_credit_limits', $this->accountID) != 1) {
					$this->family_credit_limit = 100000;
				}
			} else {
				return FALSE;
			}

			// if passing in cartID, may be editing existing booking
			if (isset($params['cartID']) && !empty($params['cartID'])) {
				// look up
				$where = array(
					'accountID' => $this->accountID,
					'contactID' => $this->contactID,
					'cartID' => $params['cartID']
				);
				$res = $this->CI->db->select('type')->from('bookings_cart')->where($where)->limit(1)->get();
				if ($res->num_rows() == 1) {
					foreach ($res->result() as $row) {
						$this->cartID = $params['cartID'];
						$this->cart_type = $row->type;
					}
				}
			}

			// if no cart id, get existing cart
			if (empty($this->cartID)) {
				$where = array(
					'accountID' => $this->accountID,
					'contactID' => $this->contactID,
					'type' => 'cart'
				);
				$res = $this->CI->db->select('cartID')->from('bookings_cart')->where($where)->order_by('added desc')->limit(1)->get();
				if ($res->num_rows() == 1) {
					foreach ($res->result() as $row) {
						$this->cartID = $row->cartID;
					}
				}
			}

			// if still, no cart id, create new
			if (empty($this->cartID)) {
				$data = array(
					'accountID' => $this->accountID,
					'familyID' => $this->familyID,
					'contactID' => $this->contactID,
					'type' => 'cart',
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$res = $this->CI->db->insert('bookings_cart', $data);
				$this->cartID = $this->CI->db->insert_id();
			}

			// look up summary
			$this->refresh_cart($flagValue);
		}

		return TRUE;
	}

	public function refresh_cart($flagValue = NULL) {
		if (empty($this->cartID)) {
			return FALSE;
		}
		$where = array(
			'bookings_cart.accountID' => $this->accountID,
			'bookings_cart.contactID' => $this->contactID,
			'bookings_cart.cartID' => $this->cartID
		);
		$res = $this->CI->db->select('bookings_cart.cartID, GROUP_CONCAT(DISTINCT ' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.bookingID) as bookings, GROUP_CONCAT(DISTINCT ' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.blockID) as blocks')->from('bookings_cart')->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'left')->where($where)->group_by('bookings_cart.cartID')->limit(1)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$blocks = array();
				$blocks_array = explode(',', $row->blocks);
				if (count($blocks_array) > 0) {
					$blocks_array = array_filter($blocks_array);
					foreach ($blocks_array as $blockID) {
						$blocks[] = intval(trim($blockID));
					}
				}
				$bookings = array();
				$bookings_array = explode(',', $row->bookings);
				if (count($bookings_array) > 0) {
					$bookings_array = array_filter($bookings_array);
					foreach ($bookings_array as $bookingID) {
						$bookings[] = intval(trim($bookingID));
					}
				}
				if($flagValue == 1){
					$this->count = 0;
				}else{
					$this->count = count($blocks);
				}
				$this->bookingIDs = $bookings;
				$this->blockIDs = $blocks;
			}
		}
	}
	public function clear() {
		if (empty($this->cartID)) {
			return FALSE;
		}
		$where = array(
			'accountID' => $this->accountID,
			'cartID' => $this->cartID
		);
		$res = $this->CI->db->delete('bookings_cart_sessions', $where);
		if ($this->CI->db->affected_rows() > 0) {
			$where = array(
				'accountID' => $this->accountID,
				'cartID' => $this->cartID
			);

			$subs = $this->CI->db->from('bookings_cart_subscriptions')->where($where)->get();
			if($subs->num_rows() > 0) {
				$this->CI->db->delete('bookings_cart_subscriptions', $where);
			}
			$this->refresh_cart();
			return TRUE;
		}
		return FALSE;
	}

	public function remove_block($blockID) {
		if (empty($this->cartID)) {
			return FALSE;
		}
		$where = array(
			'accountID' => $this->accountID,
			'cartID' => $this->cartID,
			'blockID' => $blockID
		);
		$this->CI->db->delete('bookings_cart_sessions', $where);
		$where = array(
			'accountID' => $this->accountID,
			'cartID' => $this->cartID
		);
		$this->CI->db->delete('bookings_cart_subscriptions', $where);
		return TRUE;
	}

	public function remove_user_subscription($userID, $bookingID) {
		if (empty($this->cartID) || empty($userID) || empty($bookingID)) {
			return FALSE;
		}
		$where = array(
			'accountID' => $this->accountID,
			'cartID' => $this->cartID,
			'childID' => $userID,
			'bookingID' => $bookingID
		);
		$this->CI->db->delete('bookings_cart_sessions', $where);

		if ($this->CI->db->affected_rows() === 0) {
			$where = array(
				'accountID' => $this->accountID,
				'cartID' => $this->cartID,
				'contactID' => $userID,
				'bookingID' => $bookingID
			);
			$this->CI->db->delete('bookings_cart_sessions', $where);
			if ($this->CI->db->affected_rows() === 0) {
				return FALSE;
			}else{
				$where = array(
					'accountID' => $this->accountID,
					'cartID' => $this->cartID,
					'contactID' => $userID,
					'bookingID' => $bookingID
				);
				$this->CI->db->delete('bookings_cart_subscriptions', $where);
				if ($this->CI->db->affected_rows() === 0) {
					return FALSE;
				}
			}
		}else{
			$where = array(
				'accountID' => $this->accountID,
				'cartID' => $this->cartID,
				'childID' => $userID,
				'bookingID' => $bookingID
			);
			$this->CI->db->delete('bookings_cart_subscriptions', $where);
			if ($this->CI->db->affected_rows() === 0) {
				return FALSE;
			}
		}
		return TRUE;
	}

	public function remove_booking($bookingID, $preserveCancelledSessions = false) {
		if (empty($this->cartID)) {
			return FALSE;
		}
		$where = array(
			'accountID' => $this->accountID,
			'cartID' => $this->cartID,
			'bookingID' => $bookingID
		);
		if ($preserveCancelledSessions) {
			//Exclude deleting sessions which are cancelled by exceptions (these sessions
			//Dont appear in the edit booking screen).
			$sessionsToExclude = array();
			$exceptions = $this->CI->db->from('bookings_lessons_exceptions')->where(array(
				'accountID' => $this->accountID,
				'bookingID' => $bookingID,
				'type' => 'cancellation'
			))->get();
			if ($exceptions->num_rows()>0) {
				foreach($exceptions->result() as $exception) {
					$session = $this->CI->db->from('bookings_cart_sessions')->where(array(
						"lessonID" => $exception->lessonID,
						"date" => $exception->date,
						"bookingID" => $bookingID,
						"cartID" => $this->cartID,
						'accountID' => $this->accountID,
					))->limit(1)->get();
					if ($session->num_rows() > 0) {
						$sessionsToExclude[] = $session->result()[0]->sessionID;
					}
				}

				if (count($sessionsToExclude)>0) {
					$this->CI->db->where_not_in("sessionID",$sessionsToExclude);
				}
			}
		}
		$res = $this->CI->db->delete('bookings_cart_sessions', $where);
		if ($this->CI->db->affected_rows() > 0) {
			$where = array(
				'accountID' => $this->accountID,
				'cartID' => $this->cartID
			);

			//Check if user remove booking after editing event
			if(!$this->CI->input->post('subscriptions')) {
				$subs = $this->CI->db->from('bookings_cart_subscriptions')->where($where)->get();
				if ($subs->num_rows() > 0) {
					$this->CI->db->delete('bookings_cart_subscriptions', $where);
				}
			}
			$this->refresh_cart();
			return TRUE;
		}
		return FALSE;
	}

	public function get_booked_blocks() {
		// if no booked blocks, return empty array
		if (count($this->blockIDs) == 0) {
			return array();
		}

		// look up blocks
		$where = array();
		if (!$this->in_crm) {
			$where['bookings.disable_online_booking !='] = 1;
		}
		$custom_where = " AND `" . $this->CI->db->dbprefix("bookings_blocks") . "`.`blockID` IN (" . $this->CI->db->escape_str(implode(',', $this->blockIDs)) . ")";
		return $this->get_blocks($where, array('show_all' => TRUE), $custom_where);
	}

	public function get_booked_sessions() {
		$booked_sessions = array();

		// get booked sessions
		$where = array(
			'bookings_cart_sessions.accountID' => $this->accountID,
			'bookings_cart_sessions.cartID' => $this->cartID
		);
		$res = $this->CI->db->select('bookings_cart_sessions.*, CONCAT_WS(" ", ' . $this->CI->db->dbprefix("family_contacts") . '.first_name, ' . $this->CI->db->dbprefix("family_contacts") . '.last_name) AS contact_name, CONCAT_WS(" ", ' . $this->CI->db->dbprefix("family_children") . '.first_name, ' . $this->CI->db->dbprefix("family_children") . '.last_name) AS child_name')
			->from('bookings_cart_sessions')
			->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
			->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
			->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
			->where($where)
			->order_by('bookings_cart_sessions.date asc, bookings_lessons.startTime asc')
			->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$row->participantID = $row->contactID;
				if (!empty($row->childID)) {
					$row->participantID = $row->childID;
				}
				$booked_sessions[$row->blockID][$row->date][$row->lessonID][] = $row->participantID;
			}
		}
		return $booked_sessions;
	}

	public function get_booked_subscriptions() {
		$booked_sessions = array();

		// get booked sessions
		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'bookings_cart_subscriptions.cartID' => $this->cartID,
			'bookings_cart.type' => 'cart'
		);
		$res = $this->CI->db->select('bookings_cart_subscriptions.*, CONCAT_WS(" ", ' . $this->CI->db->dbprefix("family_contacts") . '.first_name, ' . $this->CI->db->dbprefix("family_contacts") . '.last_name) AS contact_name, CONCAT_WS(" ", ' . $this->CI->db->dbprefix("family_children") . '.first_name, ' . $this->CI->db->dbprefix("family_children") . '.last_name) AS child_name')
			->from('bookings_cart_subscriptions')
			->join('bookings_cart', 'bookings_cart_subscriptions.cartID = bookings_cart.cartID', 'left')
			->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
			->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
			->where($where)
			->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$row->participantID = $row->contactID;
				if (!empty($row->childID)) {
					$row->participantID = $row->childID;
				}
				$booked_sessions[$row->blockID][$row->date][$row->lessonID][] = $row->participantID;
			}
		}
		return $booked_sessions;
	}

	public function get_cart_summary() {
		$return = array(
			'sessions' => array(),
			'sessions_subtotals' => array(),
			'sessions_discounts' => array(),
			'sessions_totals' => array(),
			'block_totals' => array(),
			'block_priced' => array(),
			'subtotal' => 0,
			'subscription_total' => 0,
			'discount' => 0,
			'total' => 0,
			'vouchers' => $this->get_vouchers(TRUE),
			'subscriptions' => array()
		);

		// get booked sessions
		$where = array(
			'bookings_cart_sessions.accountID' => $this->accountID,
			'bookings_cart_sessions.cartID' => $this->cartID
		);
		$res = $this->CI->db->select('bookings_cart_sessions.*, lesson_types.name as lesson_type, CONCAT_WS(" ", ' . $this->CI->db->dbprefix("family_contacts") . '.first_name, ' . $this->CI->db->dbprefix("family_contacts") . '.last_name) AS contact_name, CONCAT_WS(" ", ' . $this->CI->db->dbprefix("family_children") . '.first_name, ' . $this->CI->db->dbprefix("family_children") . '.last_name) AS child_name, subscriptions.subID, subName, subscriptions.price as subPrice, frequency')
			->from('bookings_cart_sessions')
			->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
			->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
			->join('bookings_cart_subscriptions', 'bookings_cart_sessions.cartID = bookings_cart_subscriptions.cartID AND (bookings_cart_sessions.childID = bookings_cart_subscriptions.childID OR bookings_cart_sessions.contactID = '.$this->CI->db->dbprefix("bookings_cart_subscriptions") .'.contactID)', 'left')
			->join('subscriptions', 'bookings_cart_subscriptions.subID = subscriptions.subID', 'left')
			->where($where)
			->order_by('bookings_lessons.startTime asc')
			->group_by('bookings_cart_sessions.sessionID')
			->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$row->participant = $row->contact_name;
				if (!empty($row->childID)) {
					$row->participant = $row->child_name;
				}
				if (empty($row->lesson_type)) {
					$row->lesson_type = 'Session';
				}
				$return['sessions'][$row->blockID][$row->date][$row->participant][] = $row->lesson_type;

				if($row->subID != NULL) {
					$subscription = $row->subName . '(' . currency_symbol($this->accountID) . $row->subPrice . ' - ' . ucfirst($row->frequency) . ')';
					$return['sessions_subscriptions'][$row->blockID][$row->date][$row->participant] = $subscription;
				}

				// store prices
				if (!isset($return['sessions_subtotals'][$row->blockID][$row->date])) {
					$return['sessions_subtotals'][$row->blockID][$row->date] = 0;
				}
				$return['sessions_subtotals'][$row->blockID][$row->date] += $row->price;
				$return['subtotal'] += $row->price;

				// store discounts
				if (!isset($return['sessions_discounts'][$row->blockID][$row->date])) {
					$return['sessions_discounts'][$row->blockID][$row->date] = 0;
				}
				$return['sessions_discounts'][$row->blockID][$row->date] += $row->discount;
				$return['discount'] += $row->discount;

				// store price after discount
				if (!isset($return['sessions_totals'][$row->blockID][$row->date])) {
					$return['sessions_totals'][$row->blockID][$row->date] = 0;
				}
				$return['sessions_totals'][$row->blockID][$row->date] += $row->total;
				$return['total'] += $row->total;

				// store block totals
				if (!isset($return['block_totals'][$row->blockID])) {
					$return['block_totals'][$row->blockID] = 0;
				}
				$return['block_totals'][$row->blockID] += $row->total;

				// check if block priced
				if ($row->block_priced == 1) {
					$return['block_priced'][$row->blockID] = true;
				}

				if(!empty($row->contactID)) {
					$where = array(
						'cartID' => $this->cartID,
						'accountID' => $this->accountID,
						'contactID' => $row->contactID
					);
					$subs = $this->CI->db->select('*')
						->from('bookings_cart_subscriptions')
						->where($where)->get();
					if($subs->num_rows() > 0) {
						$return['sessions_subtotals'][$row->blockID][$row->date] -= $row->price;
						$return['sessions_totals'][$row->blockID][$row->date] -= $row->price;
						$return['subtotal'] -= $row->price;
						$return['total'] -= $row->price;
						$return['block_totals'][$row->blockID] -= $row->price;
					}
				}else{
					$where = array(
						'cartID' => $this->cartID,
						'accountID' => $this->accountID,
						'childID' => $row->childID
					);
					$subs = $this->CI->db->select('*')
						->from('bookings_cart_subscriptions')
						->where($where)->get();
					if($subs->num_rows() > 0) {
						$return['sessions_subtotals'][$row->blockID][$row->date] -= $row->price;
						$return['sessions_totals'][$row->blockID][$row->date] -= $row->price;
						$return['subtotal'] -= $row->price;
						$return['total'] -= $row->price;
						$return['block_totals'][$row->blockID] -= $row->price;
					}
				}

			}
		}


		//get subscriptions
		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'bookings_cart_subscriptions.cartID' => $this->cartID
		);

		$subs = $this->CI->db->select('subscriptions.*, CONCAT_WS(" ", ' . $this->CI->db->dbprefix("family_contacts") . '.first_name, ' . $this->CI->db->dbprefix("family_contacts") . '.last_name) AS contact_name, CONCAT_WS(" ", ' . $this->CI->db->dbprefix("family_children") . '.first_name, ' . $this->CI->db->dbprefix("family_children") . '.last_name) AS child_name, bookings_cart_subscriptions.blockID, bookings_cart_subscriptions.cartID, family_contacts.contactID, family_children.childID')
			->from('bookings_cart_subscriptions')
			->join('subscriptions', 'bookings_cart_subscriptions.subID = subscriptions.subID')
			->join('family_contacts', 'bookings_cart_subscriptions.contactID = family_contacts.contactID', 'left')
			->join('family_children', 'bookings_cart_subscriptions.childID = family_children.childID', 'left')
			->where($where)->get();
		if($subs->num_rows() > 0) {
			foreach ($subs->result() as $subscription) {
				$return['subscriptions'][] = $subscription;
				$return['subscription_total'] += $subscription->price;
				$return['total'] += $subscription->price;
			}

		}


		$return = $this->apply_fixed_discount($return);
		return $return;
	}

	public function process_block($blockID, $selected_participants, $flagValue = NULL) {
		if (empty($this->cartID)) {
			return FALSE;
		}


		$validated_lessons = $this->validate_post($blockID, $selected_participants);

		// check for errors
		if ($this->get_errors() !== FALSE) {
			return $validated_lessons;
		}


		// get bookingID
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->accountID
		);
		$res = $this->CI->db->select('bookingID')->from('bookings_blocks')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $row) {
			$bookingID = $row->bookingID;
		}

		//Validate mandatory booking monitoring fields
		$res = $this->CI->db->from('bookings')->where(array('bookings.bookingID' => $bookingID))->limit(1)->get();
		foreach ($res->result() as $booking_info) {
			for ($i = 1; $i <= 20; $i++) {
				$field = 'monitoring' . $i;
				if (!empty(trim($booking_info->$field))
					&& $booking_info->{$field."_entry_type"}=="2"
					&& $booking_info->{$field."_mandatory"}=="1") {
					foreach ((array)$this->CI->input->post('monitoring')[$i] as $inputted_field) {
						if (empty($inputted_field)) {
							$this->errors[] = "Some mandatory fields are missing information.";
							return $validated_lessons;
						}
					}
				}
			}
		}

		// all ok, process

		// get blocks
		$where = array(
			'bookings_blocks.bookingID' => $bookingID
		);
		if (!$this->in_crm) {
			$where['bookings.disable_online_booking !='] = 1;
		}
		$blocks = $this->get_blocks($where);

		if (count($blocks) == 0) {
			return FALSE;
		}
		$participants_id_field = 'childID';
		$participants_table = 'family_children';
		foreach($blocks as $block_info) {
			if (strpos($block_info->register_type, 'individuals') === 0) {
				$participants_id_field = 'contactID';
				$participants_table = 'family_contacts';
			}
			if (strpos($block_info->register_type, 'adults_children') === 0) {
				$participants_id_field = 'contactID, childID';
				$participants_table = 'family_contacts, family_children';
			}
			break;
		}

		// check for any existing extra data, e.g. attendance, bikeability, shapeup
		$extra_data = array(
			'attended' => array(),
			'bikeability_level' => array(),
			'shapeup_weight' => array()
		);
		$where = array(
			'accountID' => $this->accountID,
			'cartID' => $this->cartID,
			'bookingID' => $block_info->bookingID
		);
		$res = $this->CI->db->select($participants_id_field . ', lessonID, attended, bikeability_level, shapeup_weight')->from('bookings_cart_sessions')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (strpos($block_info->register_type, 'adults_children') === 0) {
					$ids = explode(",", $participants_id_field);
					foreach($ids as $id){
						if(isset($row->$id) && $row->$id != ""){
							$extra_data['attended'][$row->lessonID][$row->$id] = $row->attended;
							$extra_data['bikeability_level'][$row->lessonID][$row->$id] = $row->bikeability_level;
							$extra_data['shapeup_weight'][$row->lessonID][$row->$id] = $row->shapeup_weight;
						}
					}
				}else {
					$extra_data['attended'][$row->lessonID][$row->$participants_id_field] = $row->attended;
					$extra_data['bikeability_level'][$row->lessonID][$row->$participants_id_field] = $row->bikeability_level;
					$extra_data['shapeup_weight'][$row->lessonID][$row->$participants_id_field] = $row->shapeup_weight;
				}
			}
		}
		// delete any existing sessions in this booking
		$this->remove_booking($block_info->bookingID, true);
		$already_in_cart = FALSE;
		if ($this->CI->db->affected_rows() > 0) {
			$already_in_cart = TRUE;
		}

		// create sessions in cart
		$participantIDs = array();
		foreach($blocks as $blockID => $block) {
			if ($this->online_booking_subscription_module === "1" || ($this->online_booking_subscription_module === "0" && $block->subscriptions_only === "0")) {
				foreach ($validated_lessons as $lessonID => $dates) {
					if (array_key_exists($lessonID, $block->lessons)) {
						foreach ($dates as $date => $participants) {
							foreach ($participants as $participantID) {
								if (strpos($block_info->register_type, 'adults_children') === 0) {
									$participants_id_field = $this->check_user_type($participantID);
								}
								$data = array(
									'accountID' => $this->accountID,
									'cartID' => $this->cartID,
									'bookingID' => $block_info->bookingID,
									'blockID' => $blockID,
									'lessonID' => $lessonID,
									$participants_id_field => $participantID,
									'date' => $date,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
								if (isset($extra_data['attended'][$lessonID][$participantID])) {
									$data['attended'] = $extra_data['attended'][$lessonID][$participantID];
								}
								if (isset($extra_data['bikeability_level'][$lessonID][$participantID])) {
									$data['bikeability_level'] = $extra_data['bikeability_level'][$lessonID][$participantID];
								}
								if (isset($extra_data['shapeup_weight'][$lessonID][$participantID])) {
									$data['shapeup_weight'] = $extra_data['shapeup_weight'][$lessonID][$participantID];
								}
								$res = $this->CI->db->insert('bookings_cart_sessions', $data);
								$participantIDs[$participantID] = $participantID;
							}
						}
					}
				}
			}
		}

		if($this->CI->input->post('subscriptions')) {
			$subs = $this->CI->input->post('subscriptions');
			if(count($subs) > 0) {
				// delete previous subscriptions
				$where = array(
					'cartID' => $this->cartID,
					'accountID' => $this->accountID,
					'blockID' => $blockID,
				);
				$res = $this->CI->db->delete('bookings_cart_subscriptions', $where);

				foreach($subs as $Id => $sub) {
					if (strpos($block_info->register_type, 'adults_children') === 0) {
						$participants_id_field = $this->check_user_type($Id);
					}
					if(!isset($participantIDs[$Id])){
						$this->errors[] = 'Please select at least one session with subscription';
					}
					$data = array(
						'accountID' => $this->accountID,
						'cartID' => $this->cartID,
						'subID' => $sub,
						$participants_id_field => $Id,
						'bookingID' => $block_info->bookingID,
						'blockID' => $blockID,
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$this->CI->db->insert('bookings_cart_subscriptions', $data);
				}
			}
		}

		// calc prices and discounts
		$this->validate_cart($flagValue);

		// delete previous monitoring
		$where = array(
			'cartID' => $this->cartID,
			'bookingID' => $block_info->bookingID
		);
		$res = $this->CI->db->delete('bookings_cart_monitoring', $where);

		// get monitoring
		$monitoring = (array)$this->CI->input->post('monitoring');
		$monitoring_fields = array();
		$where = array(
			'bookings.bookingID' => $block_info->bookingID
		);
		$res = $this->CI->db->from('bookings')
			->where($where)
			->limit(1)
			->get();
		foreach ($res->result() as $booking_info) {
			for ($i = 1; $i <= 20; $i++) {
				$field = 'monitoring' . $i;
				if (!empty(trim($booking_info->$field))) {
					$monitoring_fields[$i] = $booking_info->$field;
				}
			}
		}
		// islington only - ID 35
		if ($this->accountID == 35) {
			$monitoring_fields['medical'] = 'Tick if there are any medical conditions that you need to discuss with your instructor(s) prior to your cycling session.';
		}

		// process monitoring
		if (count($monitoring_fields) > 0) {
			foreach ($participantIDs as $participantID) {
				if(strpos($participantID, "a") === 0){
					$participantID = ltrim($participantID, "a");
					$participants_id_field = "contactID";
				}elseif(strpos($participantID, "p") === 0){
					$participantID = ltrim($participantID, "p");
					$participants_id_field = "childID";
				}
				$data = array(
					'accountID' => $this->accountID,
					'cartID' => $this->cartID,
					'bookingID' => $block_info->bookingID,
					$participants_id_field => $participantID,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$has_monitoring = FALSE;
				foreach ($monitoring_fields as $key => $value) {
					if ($key == 'medical') {
						if ($value == 1) {
							$contact_data = array(
								'medical' => 'Contact Participant',
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							$where = array(
								'accountID' => $this->accountID,
								$participants_id_field => $participantID
							);
							$this->CI->db->update($participants_table, $contact_data, $where, 1);
						}
						continue;
					}

					if (isset($monitoring[$key][$participantID]) && !empty(trim($monitoring[$key][$participantID]))) {
						$data['monitoring' . $key] = $monitoring[$key][$participantID];
						$has_monitoring = TRUE;
					}
				}
				// save monitoring if some data
				if ($has_monitoring) {
					$this->CI->db->insert('bookings_cart_monitoring', $data);
				}
			}
		}

		if ($already_in_cart) {
			return 'updated';
		}
		return 'added';
	}

	public function check_user_type($Id)
	{
		$where = array(
			'accountID' => $this->accountID,
			'contactID' => $Id
		);
		$res = $this->CI->db->select('contactID')->from('family_contacts')->where($where)->get();
		if ($res->num_rows() > 0) {
			return 'contactID';
		}
		return 'childID';
	}

	public function validate_post($blockID, $selected_lessons) {
		$validated_lessons = array();
		$subscription_flag = 0;

		// get participants
		$participants = $this->get_participants($blockID);

		// get bookingID
		$where = array(
			'blockID' => $blockID,
			'accountID' => $this->accountID
		);
		$res = $this->CI->db->select('bookingID')->from('bookings_blocks')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $row) {
			$bookingID = $row->bookingID;
		}

		// get blocks
		$where = array(
			'bookings_blocks.bookingID' => $bookingID
		);
		if (!$this->in_crm) {
			$where['bookings.disable_online_booking !='] = 1;
		}
		$blocks = $this->get_blocks($where);

		if (count($blocks) == 0) {
			return FALSE;
		}

		$subs = $this->CI->input->post('subscriptions');
		$users_with_no_subs = array();

		// validate
		if (is_array($selected_lessons) && count($selected_lessons) > 0) {
			if (count($blocks) > 0) {
				foreach ($blocks as $blockID => $block) {
					if (count($block->dates) > 0) {
						foreach ($block->dates as $date => $lessons) {
							foreach ($lessons as $lessonID => $lesson) {
								$places_taken = 0;
								// if selected
								if (isset($selected_lessons[$lessonID][$date]) && is_array($selected_lessons[$lessonID][$date]) && count($selected_lessons[$lessonID][$date]) > 0) {
									foreach ($selected_lessons[$lessonID][$date] as $participantID) {
										$prefix = '';
										if(strpos($participantID, "a") === 0){
											$participantID = ltrim($participantID, "a");
											$prefix = 'a';
										}elseif(strpos($participantID, "p") === 0){
											$participantID = ltrim($participantID, "p");
											$prefix = 'p';
										}

										// if within age range (if set)
										$age = calculate_age($participants[$participantID]->dob, $date);

										if ((empty($lesson['min_age']) || $age >= $lesson['min_age']) && (empty($lesson['max_age']) || $age <= $lesson['max_age'])) {
											// check if places available or allow CRM users to update bookings regardless of available
											if ($lesson['available'] === 'unlimited' || $places_taken < $lesson['available'] || $this->in_crm==true) {
												$validated_lessons[$lessonID][$date][] = $prefix.$participantID;
												$places_taken++;
											} else {
												// no, tell user
												$this->errors[] = $participants[$participantID]->first_name . ' ' . $participants[$participantID]->last_name . ' - ' . $block->lessons[$lessonID]['type'] . ' (' . mysql_to_uk_date($date) . ') removed due to no places remaining';
											}
										}
										if(!isset($subs[$participantID])){
											$users_with_no_subs[$participantID] = 1;
										}
									}
								}
							}
						}
					}
				}
			}
		}else{
			foreach ($blocks as $blockID => $block) {
				if ($this->online_booking_subscription_module === "1") {
					$subscription_flag = 1;
				}
			}
		}

		//Check for any non subscription booking, if found restrict user to add to cart
		$where = array(
			'bookings_cart_sessions.accountID' => $this->accountID,
			'bookings_cart_sessions.cartID' => $this->cartID
		);
		$res = $this->CI->db->select('count(sessionID) as session, count(id) as subs')
			->from('bookings_cart')
			->join('bookings_cart_sessions', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'left')
			->join('bookings_cart_subscriptions', 'bookings_cart_sessions.cartID = bookings_cart_subscriptions.cartID', 'left')
			->where($where)
			->get();
		if($res->num_rows() > 0){
			foreach($res->result() as $booking_details) break;
			if(($booking_details->subs == 0 && $booking_details->session > 0) ||
				($booking_details->subs > 0 && empty($this->CI->input->post('subscriptions'))) ||
				($booking_details->subs > 0 && count($users_with_no_subs) > 0) ||
				(!empty($this->CI->input->post('subscriptions')) && count($users_with_no_subs) > 0)
			){
				$this->errors[] = 'Subscription based bookings are not permitted with non-subscription based bookings.';
			}
		}

		if($subs) {
			$validate_subs = $subs;
			if(count($subs) > 0) {
				foreach($subs as $Id => $sub) {
					if (is_array($selected_lessons) && count($selected_lessons) > 0) {
						if (count($blocks) > 0) {
							foreach ($blocks as $blockID => $block) {
								if (count($block->dates) > 0) {
									foreach ($block->dates as $date => $lessons) {
										foreach ($lessons as $lessonID => $lesson) {
											if (isset($selected_lessons[$lessonID][$date]) && is_array($selected_lessons[$lessonID][$date]) && count($selected_lessons[$lessonID][$date]) > 0) {
												foreach ($selected_lessons[$lessonID][$date] as $participantID) {
													if(strpos($participantID, "a") === 0){
														$participantID = ltrim($participantID, "a");
														$prefix = 'a';
													}elseif(strpos($participantID, "p") === 0){
														$participantID = ltrim($participantID, "p");
														$prefix = 'p';
													}
													if(array_key_exists($participantID, $validate_subs)) {
														unset($validate_subs[$participantID]);
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if (count($validated_lessons) == 0) {
			$this->errors[] = 'Please book at least one session';
		}else{
			if(isset($validate_subs) && count($validate_subs) > 0){
				$this->errors[] = 'Please book at least one session';
			}
		}

		return $validated_lessons;
	}

	// list of participants who can book on block
	public function get_participants($blockID) {
		$participants = array();

		// get blocks
		$where = array(
			'bookings_blocks.blockID' => $blockID
		);
		if (!$this->in_crm) {
			$where['bookings.disable_online_booking !='] = 1;
		}
		$blocks = $this->get_blocks($where);


		//sprcifically for adults and children
		foreach ($blocks as $block) {
			if ($block->register_type === 'adults_children') {

				$sub_field_main='';$sub_field='';$subscription_query_child='';$subscription_query_parent='';
				if($this->online_booking_subscription_module) {
					$subscription_query_child = 'LEFT JOIN `app_participant_subscriptions` ps ON
					`fc`.`childID` = `ps`.`childID`
					AND `ps`.`status` != "cancelled"
					LEFT JOIN `app_subscriptions` s ON `ps`.`subID` = `s`.`subID`
					AND (`s`.`bookingID` = ' . $block->bookingID . ' OR `s`.`bookingID` IS NULL)';
					$subscription_query_parent = 'LEFT JOIN `app_participant_subscriptions` ps ON
					`fc`.`contactID` = `ps`.`contactID`
					AND `ps`.`status` != "cancelled"
					LEFT JOIN `app_subscriptions` s ON `ps`.`subID` = `s`.`subID`
					AND (`s`.`bookingID` = ' . $block->bookingID . ' OR `s`.`bookingID` IS NULL)';
					$sub_field = ',s.`subID`';
					$sub_field_main = ",subID";
				}

				$res = $this->CI->db->query("select  `first_name`, `last_name`, `dob`, `Id`, `type` ".$sub_field_main." FROM
				(SELECT  `first_name`, `last_name`, `dob`, fc.`contactID` as `Id`, 'parent' as `type` ".$sub_field."
				FROM `app_family_contacts` fc
				".$subscription_query_parent."
				WHERE fc.`familyID` =  '" . $this->familyID . "'
				AND fc.`accountID` =  '" . $this->accountID . "') tb1
				UNION ALL
				(SELECT  `first_name`, `last_name`, `dob`, fc.`childID` as `Id`, 'child' as `type` ".$sub_field."
				FROM `app_family_children` fc
				".$subscription_query_child."
				WHERE fc.`familyID` = '" . $this->familyID . "'
				AND fc.`accountID` = '" . $this->accountID . "')");

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$participants[$row->Id] = $row;
					}
				}
				return $participants;
			}
		}

		$participants_id_field = 'childID';
		$participants_table = 'family_children';
		$enable_subscription = 0;
		foreach ($blocks as $block) {
			$bookingID = $block->bookingID;
			if (strpos($block->register_type, 'individuals') === 0) {
				$participants_id_field = 'contactID';
				$participants_table = 'family_contacts';
			}
			if($this->online_booking_subscription_module) {
				$enable_subscription = 1;
			}
			break;
		}

		$where = array(
			$participants_table . '.familyID' => $this->familyID,
			$participants_table . '.accountID' => $this->accountID,
		);

		$this->CI->db->select($participants_table . '.' . $participants_id_field . ', first_name, last_name, dob');

		//Include SubID if subscription is present
		if($enable_subscription) {
			$this->CI->db->select('participant_subscriptions.subID');
		}

		$this->CI->db->from($participants_table);

		if($enable_subscription) {
			$this->CI->db->join('participant_subscriptions', $participants_table . '.' . $participants_id_field . ' = participant_subscriptions.' . $participants_id_field . ' AND participant_subscriptions.status != "cancelled"', 'left')
				->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID AND (subscriptions.bookingID = ' . $bookingID . ' OR subscriptions.bookingID IS NULL)', 'left');
		}

		$res = $this->CI->db->where($where)->order_by('first_name asc, last_name asc')->get();
		//return $this->CI->db->last_query();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$participants[$row->$participants_id_field] = $row;
			}
		}

		return $participants;
	}

	public function validate_cart($flagValue = NULL) {
		if (empty($this->cartID)) {
			return FALSE;
		}

		$this->refresh_cart($flagValue);

		// vars
		$lesson_prices = [];
		$autodiscount_sessions = [];
		$autodiscount_sessions_eligible = [];
		$autodiscount_sessions_selected = [];
		$siblingdiscount_sessions_selected = [];
		$siblingdiscount_req_met = [];
		$siblingdiscount_req_all_met = [];
		$participants = [];
		$total_sessions = [];
		$total_sessions_selected = [];

		// if using any booking vouchers, remove any which are no longer active
		if ($this->cart_type == 'cart') {
			$where = array(
				'bookings_cart_vouchers.cartID' => $this->cartID,
				'bookings_vouchers.active !=' => 1
			);
			$res = $this->CI->db->select('bookings_cart_vouchers.id')
				->from('bookings_cart_vouchers')
				->join('bookings_vouchers', 'bookings_cart_vouchers.voucherID = bookings_vouchers.voucherID', 'inner')
				->where($where)
				->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$where = array(
						'id' => $row->id,
						'cartID' => $this->cartID
					);
					$this->CI->db->delete('bookings_cart_vouchers', $where, 1);
				}
			}
		}

		// if using any global vouchers, remove any which are no longer active
		if ($this->cart_type == 'cart') {
			$where = array(
				'bookings_cart_vouchers.cartID' => $this->cartID,
				'vouchers.active !=' => 1
			);
			$res = $this->CI->db->select('bookings_cart_vouchers.id')
				->from('bookings_cart_vouchers')
				->join('vouchers', 'bookings_cart_vouchers.voucherID_global = vouchers.voucherID', 'inner')
				->where($where)
				->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$where = array(
						'id' => $row->id,
						'cartID' => $this->cartID
					);
					$this->CI->db->delete('bookings_cart_vouchers', $where, 1);
				}
			}
		}

		// get vouchers
		$vouchers = $this->get_voucher_discounts();
		$lesson_voucher_discounts = array();
		$lesson_voucher_discountIDs = array();

		// get session types applicable to auto discounts
		$autodiscount_lesson_types = array();
		$where = array(
			'lesson_types.accountID' => $this->accountID,
			'lesson_types.exclude_autodiscount !=' => 1
		);
		$res = $this->CI->db->select('lesson_types.typeID')->from('lesson_types')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$autodiscount_lesson_types[] = $row->typeID;
			}
		}

		//get subscriptions
		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'bookings_cart_subscriptions.cartID' => $this->cartID
		);

		$subs = $this->CI->db->select('bookings_cart_subscriptions.contactID, bookings_cart_subscriptions.childID,subscriptions.subID, subscriptions.price, subscriptions.bookingID')
			->from('bookings_cart_subscriptions')
			->join('subscriptions', 'bookings_cart_subscriptions.subID = subscriptions.subID')
			->where($where)->get();

		$subscription = NULL;
		if($subs->num_rows() > 0) {
			$subscription = $subs->result();
		}

		// get blocks in cart
		$blocks = $this->get_booked_blocks();

		if (count($blocks) == 0) {
			return;
		}

		// track processed
		$processed_blocks = array();

		// get booked sessions
		$session_present = '';
		$booked_sessions = $this->get_booked_sessions();
		if(count($blocks) > 0) {
			$session_present = 0;
			foreach ($blocks as $blockID => $block) {
				$processed_blocks[] = $blockID;
				$participants_id_field = 'childID';
				$siblingdiscount_req_all_met[$blockID] = TRUE; // assume all req met
				$autodiscount_total = 0;
				$autodiscount_data = [];
				$siblingdiscount_total = 0;
				$siblingdiscount_data = [];
				$participants = $this->get_participants($block->blockID);
				if (strpos($block->register_type, 'individuals') === 0) {
					$participants_id_field = 'contactID';
				}

				// get already booked sessions for sibling disocunt
				$already_booked_sessions = array();
				$where = array(
					'bookings_cart.accountID' => $this->accountID,
					'bookings_cart.familyID' => $this->familyID,
					'bookings_cart.type' => 'booking',
					'bookings_cart_sessions.blockID' => $blockID,
					'bookings_cart.cartID !=' => $this->cartID // if editing booking
				);
				$res = $this->CI->db->from('bookings_cart')
					->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
					->where($where)
					->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						if (!isset($already_booked_sessions[$row->lessonID][$row->date])) {
							$already_booked_sessions[$row->lessonID][$row->date] = 0;
						}
						$already_booked_sessions[$row->lessonID][$row->date]++;
					}
				}

				foreach ($block->dates as $date => $lessons) {
					foreach ($lessons as $lessonID => $lesson) {
						// if cart and session time in past
						if (!$this->in_crm && $this->cart_type == 'cart' && $lesson['cutoff'] < time()) {
							// remove session for all participants
							$where = array(
								'cartID' => $this->cartID,
								'accountID' => $this->accountID,
								'blockID' => $blockID,
								'lessonID' => $lessonID,
								'date' => $date
							);
							$res = $this->CI->db->delete('bookings_cart_sessions', $where);
							continue;
						}
						if ($vouchers !== FALSE) {
							$lesson_voucher_discounts[$lessonID] = $lesson['discount'];
							$lesson_voucher_discountIDs[$lessonID] = $lesson['discount_voucherID'];
						} else {
							// check for auto discount
							if ($block->autodiscount !== 'off' && in_array($lesson['typeID'], $autodiscount_lesson_types) && $lesson['price'] > 0 && $lesson['autodiscount'] != 0) {
								// if no booking tags, or tags match, allow
								if (count($block->booking_tags) == 0 || count(array_intersect($block->booking_tags, $this->contact_tags)) > 0) {
									$autodiscount_sessions[$blockID][$lessonID] = TRUE;
									$key = $lessonID . '-' . $date;
									$autodiscount_sessions_eligible[$blockID][$key] = TRUE;
								}
							}
						}

						// check for sibling discount eligibility - vouchers and auto sibling discount
						// only applies if 2 or more participants booked on session (including existing)
						$already_booked = isset($already_booked_sessions[$lessonID][$date]) ? $already_booked_sessions[$lessonID][$date] : 0;
						if (isset($booked_sessions[$blockID][$date][$lessonID]) && (count($booked_sessions[$blockID][$date][$lessonID]) + $already_booked) >= 2) {
							$siblingdiscount_req_met[$blockID][$date][$lessonID] = TRUE;
						} else {
							$siblingdiscount_req_all_met[$blockID] = FALSE; // if not met, can't apply fixed sibling discount
							$siblingdiscount_req_met[$blockID][$date][$lessonID] = FALSE;
						}

						// count total sessions within this block
						if (!array_key_exists($blockID, $total_sessions)) {
							$total_sessions[$blockID] = 0;
						}
						$total_sessions[$blockID]++;
					if (isset($booked_sessions[$blockID][$date][$lessonID])) {
						// save price
						$lesson_prices[$lessonID] = $block->lessons[$lessonID]['price'];

						$places_taken = 0;
						foreach ($booked_sessions[$blockID][$date][$lessonID] as $participantID) {
							// check still within age range
							if (!isset($participants[$participantID])) { continue; }
							$age = calculate_age($participants[$participantID]->dob, $date);
							if ((empty($lesson['min_age']) || $age >= $lesson['min_age']) && (empty($lesson['max_age']) || $age <= $lesson['max_age'])) {
								// check if places available or since CRM users can add participants regardless of limits, check discount
								if ($lesson['available'] === 'unlimited' || $places_taken < $lesson['available'] || $this->in_crm==true) {
									// all ok
									$places_taken++;

									// mark as selected for auto discount
									$key = $lessonID . '-' . $date;
									if (isset($autodiscount_sessions_eligible[$blockID][$key])) {
										$autodiscount_sessions_selected[$blockID][$participantID][$key] = $lesson['price'];
									}

									// mark as selected for sibling discount
									$siblingdiscount_sessions_selected[$blockID][$participantID][$key] = $lesson['price'];

									// count total sessions booked by this participant for this block;
									if (!isset($total_sessions_booked[$blockID]) || !array_key_exists($participantID, $total_sessions_booked[$blockID])) {
										$total_sessions_booked[$blockID][$participantID] = 0;
									}
									$total_sessions_booked[$blockID][$participantID]++;
								} else if ($this->cart_type == 'cart') {
									if (strpos($block->register_type, 'adults_children') === 0) {
										if($participants[$participantID]->type === "parent"){
											$participants_id_field = "contactID";
										}else{
											$participants_id_field = "childID";
										}
									}
									// no space, remove
									$where = array(
										'cartID' => $this->cartID,
										'accountID' => $this->accountID,
										'blockID' => $blockID,
										'lessonID' => $lessonID,
										$participants_id_field => $participantID,
										'date' => $date
									);
									$res = $this->CI->db->delete('bookings_cart_sessions', $where);
									$key = array_search($participantID, $booked_sessions[$blockID][$date][$lessonID]);
									unset($booked_sessions[$blockID][$date][$lessonID][$key]);

									// tell user
									$this->errors[] = $participants[$participantID]->first_name . ' ' . $participants[$participantID]->last_name . ' - ' . $block->lessons[$lessonID]['type'] . ' (' . mysql_to_uk_date($date) . ') removed due to no places remaining';
								}
							} else if ($this->cart_type == 'cart') {
								if (strpos($block->register_type, 'adults_children') === 0) {
									if($participants[$participantID]->type === "parent"){
										$participants_id_field = "contactID";
									}else{
										$participants_id_field = "childID";
									}
								}
								// remove
								$where = array(
									'cartID' => $this->cartID,
									'accountID' => $this->accountID,
									'blockID' => $blockID,
									'lessonID' => $lessonID,
									$participants_id_field => $participantID,
									'date' => $date
								);
								$res = $this->CI->db->delete('bookings_cart_sessions', $where);
								$key = array_search($participantID, $booked_sessions[$blockID][$date][$lessonID]);
								unset($booked_sessions[$blockID][$date][$lessonID][$key]);

									// tell user
									$this->errors[] = $participants[$participantID]->first_name . ' ' . $participants[$participantID]->last_name . ' - ' . $block->lessons[$lessonID]['type'] . ' (' . mysql_to_uk_date($date) . ') removed due to age';
								}
							}
						}
					}
				}

				// work out discounts and save prices
				$block_priced = [];
				$block_amount_discount_values_remaining = [];
				if(isset($booked_sessions) && isset($booked_sessions[$blockID])) {
					$booked_sessions_in_block = $booked_sessions[$blockID];
					foreach ($booked_sessions[$blockID] as $date => $lessons) {
						if (count($lessons) == 0) {
							continue;
						}
						foreach ($lessons as $lessonID => $participantIDs) {
							if (count($participantIDs) == 0) {
								continue;
							}
							// if price not found, session not available any more so remove
							if (!array_key_exists($lessonID, $lesson_prices)) {
								$where = array(
									'cartID' => $this->cartID,
									'accountID' => $this->accountID,
									'lessonID' => $lessonID,
									'date' => $date
								);
								if ($this->in_crm) {
									//Check the reason the lesson isnt available anymore isnt because of an exception cancellation.
									//If this is the case, we dont want to remove the session, as these need to now be preserved on edit bookings.
									$w = array(
										'accountID' => $this->accountID,
										'lessonID' => $lessonID,
										'date' => $date,
										'type' => 'cancellation'
									);
									$exceptions = $this->CI->db->from('bookings_lessons_exceptions')->where($w)->limit(1)->get();
									if ($exceptions->num_rows()==0) {
										$this->CI->db->delete('bookings_cart_sessions', $where);
									}
								}
								else {
									$this->CI->db->delete('bookings_cart_sessions', $where);
								}
								continue;
							}

							// loop participants
							foreach ($participantIDs as $participantID) {
								$price = $lesson_prices[$lessonID];
								$discount = 0;
								$autodiscount = 0;
								$siblingdiscount = 0;
								$is_subscription = FALSE;

								//set price to 0 for subscription
								if($subscription !== NULL && count($subscription) > 0) {
									foreach ($subscription as $sub) {
										if($sub->contactID == $participantID || $sub->childID == $participantID){
											$price = 0;
											$is_subscription = TRUE;
										}
									}
								}

								// apply price to first session only if fixed block price
								if ($block->require_all_sessions == 1 && $block->block_price !== NULL) {
									$price = 0;
									if (!array_key_exists($participantID, $block_priced)) {
										$price = floatval($block->block_price);

										if ($block->subscriptions_only === "1") {
											$price = 0;
										}

										if($subscription !== NULL && count($subscription) > 0) {
											foreach ($subscription as $sub) {
												if($sub->contactID == $participantID || $sub->childID == $participantID){
													$price = 0;
												}
											}
										}

										$block_priced[$participantID] = true;

										// apply vouchers differently for block priced
										$vouchers = $this->get_voucher_discounts();
										$highest_discount = 0;
										$potential_voucherID = NULL;
										if ($vouchers !== FALSE && $is_subscription === FALSE) {
											foreach ($vouchers as $voucherID => $voucher) {
												$voucher_req_met = TRUE;
												$v_lesson_count = 0;
												// if global voucher, ensure each lesson in block is an applicable type
												if (count($voucher['typeIDs']) > 0) {
													foreach($booked_sessions_in_block as $v_date => $v_lessons) {
														foreach ($v_lessons as $v_lessonID => $v_participantIDs) {
															foreach ($v_participantIDs as $v_participantID) {
																if ($v_participantID == $participantID) {
																	$v_lesson_count++;
																	if (!in_array($block->lessons[$v_lessonID]['typeID'], $voucher['typeIDs'])) {
																		$voucher_req_met = FALSE;
																	}
																}
															}
														}
													}
												// if booking voucher, ensure voucher applies to all lessons
												} else if (count($voucher['lessonIDs']) > 0) {
													foreach($booked_sessions_in_block as $v_date => $v_lessons) {
														foreach ($v_lessons as $v_lessonID => $v_participantIDs) {
															foreach ($v_participantIDs as $v_participantID) {
																if ($v_participantID == $participantID) {
																	$v_lesson_count++;
																	if (!in_array($v_lessonID, $voucher['lessonIDs'])) {
																		$voucher_req_met = FALSE;
																	}
																}
															}
														}
													}
												}
												// if sibling discount voucher, check req met
												if ($voucher['siblingdiscount'] === TRUE) {
													if (isset($siblingdiscount_req_all_met[$blockID]) && $siblingdiscount_req_all_met[$blockID] === TRUE) {
														// req met
													} else {
														$voucher_req_met = FALSE;
													}
												}
												if ($voucher_req_met) {
													switch ($voucher['discount_type']) {
														case 'amount':
															$potential_discount = $voucher['discount'] * $v_lesson_count;
															break;
														case 'block_amount':
															$potential_discount = $voucher['discount'];
															break;
														case 'percentage':
															$potential_discount = ($voucher['discount'] / 100) * $price;
															break;
													}
													if ($potential_discount > $highest_discount) {
														$discount = $potential_discount;
														$highest_discount = $potential_discount;
														$potential_voucherID = $voucherID;
													}
												}
												if ($discount > $price) {
													$discount = $price;
												}
											}
											// track vouchers applicable to cart
											if (!empty($potential_voucherID)) {
												$this->vouchers_used[$potential_voucherID] = $potential_voucherID;
											}
										} else {
											// auto sibling discount could apply to block priced
											if ($siblingdiscount_req_all_met[$blockID] === TRUE) {
												switch ($block->siblingdiscount) {
													case 'percentage':
														$discount = ($block->siblingdiscount_amount / 100) * $price;
														break;
													case 'fixed':
													case 'amount':
														// save discount in first session
														$discount = $block->siblingdiscount_amount;
														break;
												}
												if ($discount > $price) {
													$discount = $price;
												}
											}
										}
									}
								} else {
									if ($vouchers !== FALSE && $is_subscription === FALSE) {
										$discount = $lesson_voucher_discounts[$lessonID];
										$block_amount_discount = FALSE;

										// process sibling discount vouchers (excluding block_amount discount type)
										foreach ($vouchers as $voucherID => $voucher) {
											if ($voucher['siblingdiscount'] !== TRUE || $voucher['discount_type'] === 'block_amount') {
												continue;
											}
											$voucher_req_met = TRUE;
											// if global voucher, ensure each lesson in block is an applicable type
											if (count($voucher['typeIDs']) > 0) {
												foreach($booked_sessions_in_block as $v_date => $v_lessons) {
													foreach ($v_lessons as $v_lessonID => $v_participantIDs) {
														foreach ($v_participantIDs as $v_participantID) {
															if ($v_participantID == $participantID) {
																if (!in_array($block->lessons[$v_lessonID]['typeID'], $voucher['typeIDs'])) {
																	$voucher_req_met = FALSE;
																}
															}
														}
													}
												}
											// if booking voucher, ensure voucher applies to all lessons
											} else if (count($voucher['lessonIDs']) > 0) {
												foreach($booked_sessions_in_block as $v_date => $v_lessons) {
													foreach ($v_lessons as $v_lessonID => $v_participantIDs) {
														foreach ($v_participantIDs as $v_participantID) {
															if ($v_participantID == $participantID) {
																if (!in_array($v_lessonID, $voucher['lessonIDs'])) {
																	$voucher_req_met = FALSE;
																}
															}
														}
													}
												}
											}
											// check sibling discount req met
											if (isset($siblingdiscount_sessions_selected[$blockID][$participantID], $siblingdiscount_req_met[$blockID][$date][$lessonID]) && $siblingdiscount_req_met[$blockID][$date][$lessonID] === TRUE) {
												// req met
											} else {
												$voucher_req_met = FALSE;
											}
											if ($voucher_req_met) {
												switch ($voucher['discount_type']) {
													case 'amount':
														$discount = $voucher['discount'];
														break;
													case 'percentage':
														$discount = $price * ($voucher['discount']/100);
														break;
												}
												// if discount more than session price, reduce
												if ($discount > $price) {
													$discount = $price;
												}

												// track vouchers applicable to cart
												$this->vouchers_used[$voucherID] = $voucherID;
											}
										}

										// if block hasn't started, and all sessions selected, check for block_amount vouchers
										if ($block->started === FALSE && isset($total_sessions[$blockID], $total_sessions_booked[$blockID][$participantID]) && $total_sessions[$blockID] == $total_sessions_booked[$blockID][$participantID]) {
											$potential_discounts = [];
											foreach ($vouchers as $voucherID => $voucher) {
												if ($voucher['discount_type'] !== 'block_amount') {
													continue;
												}
												$voucher_req_met = TRUE;
												// if global voucher, ensure each lesson in block is an applicable type
												if (count($voucher['typeIDs']) > 0) {
													foreach($booked_sessions_in_block as $v_date => $v_lessons) {
														foreach ($v_lessons as $v_lessonID => $v_participantIDs) {
															foreach ($v_participantIDs as $v_participantID) {
																if ($v_participantID == $participantID) {
																	if (!in_array($block->lessons[$v_lessonID]['typeID'], $voucher['typeIDs'])) {
																		$voucher_req_met = FALSE;
																	}
																}
															}
														}
													}
												// if booking voucher, ensure voucher applies to all lessons
												} else if (count($voucher['lessonIDs']) > 0) {
													foreach($booked_sessions_in_block as $v_date => $v_lessons) {
														foreach ($v_lessons as $v_lessonID => $v_participantIDs) {
															foreach ($v_participantIDs as $v_participantID) {
																if ($v_participantID == $participantID) {
																	if (!in_array($v_lessonID, $voucher['lessonIDs'])) {
																		$voucher_req_met = FALSE;
																	}
																}
															}
														}
													}
												}
												// if sibling discount voucher, check req met
												if ($voucher['siblingdiscount'] === TRUE) {
													if (isset($siblingdiscount_req_all_met[$blockID]) && $siblingdiscount_req_all_met[$blockID] === TRUE) {
														// req met
													} else {
														$voucher_req_met = FALSE;
													}
												}
												if ($voucher_req_met) {

													// if voucher not used yet, store value remaining
													if (!array_key_exists($voucherID, $block_amount_discount_values_remaining)) {
														$block_amount_discount_values_remaining[$voucherID] = $voucher['discount'];

													}

													// track as a potential
													$potential_discounts[$voucherID] = $block_amount_discount_values_remaining[$voucherID];
												}
											}

											// if some potential discounts
											if (count($potential_discounts) > 0) {
												// get max
												$potential_discount = max($potential_discounts);

												// get voucher cart ID
												$cart_voucherID = array_search($potential_discount, $potential_discounts);

												// discount can't be more than session price
												if ($potential_discount > $price) {
													$potential_discount = $price;
												}

												// if more than previous discount, apply instead
												if ($potential_discount > $discount) {
													// save discount
													$discount = $potential_discount;

													// track amount remaining
													$block_amount_discount_values_remaining[$cart_voucherID] -= $potential_discount;

													// track vouchers applicable to cart
													$this->vouchers_used[$cart_voucherID] = $cart_voucherID;
													$block_amount_discount = TRUE;
												}
											}
										}

										// track vouchers applicable to cart
										if ($block_amount_discount === FALSE && !empty($lesson_voucher_discountIDs[$lessonID])) {
											$this->vouchers_used[$lesson_voucher_discountIDs[$lessonID]] = $lesson_voucher_discountIDs[$lessonID];
										}

									} else {
										// check for autodiscount if all eligible sessions booked in block
										if (isset($autodiscount_sessions[$blockID], $autodiscount_sessions_eligible[$blockID], $autodiscount_sessions_selected[$blockID][$participantID]) && array_key_exists($lessonID, $autodiscount_sessions[$blockID]) && count($autodiscount_sessions_eligible[$blockID]) === count($autodiscount_sessions_selected[$blockID][$participantID])) {
											switch ($block->autodiscount) {
												case 'amount':
													$autodiscount = $block->autodiscount_amount;
													break;
												case 'percentage':
													$autodiscount = $lesson_prices[$lessonID] * ($block->autodiscount_amount / 100);
													break;
												case 'fixed':
													$price = 0;
													$autodiscount = 0;
													if (!array_key_exists($participantID, $block_priced)) {
														// add together price for all selected sessions and save in first session
														$price = array_sum($autodiscount_sessions_selected[$blockID][$participantID]);
														$autodiscount = $block->autodiscount_amount;
														$block_priced[$participantID] = true;
													}
													break;
											}
											if ($autodiscount > $lesson_prices[$lessonID] && $block->autodiscount != 'fixed') {
												$autodiscount = $lesson_prices[$lessonID];
											}

											$autodiscount_total += $autodiscount;
										}

										// check for sibling discount if 2 or more participants are booked on this session (including on another booking)
										if (isset($siblingdiscount_sessions_selected[$blockID][$participantID], $siblingdiscount_req_met[$blockID][$date][$lessonID]) && $siblingdiscount_req_met[$blockID][$date][$lessonID] === TRUE) {
											switch ($block->siblingdiscount) {
												case 'amount':
													$siblingdiscount = $block->siblingdiscount_amount;
													break;
												case 'percentage':
													$siblingdiscount = $lesson_prices[$lessonID] * ($block->siblingdiscount_amount / 100);
													break;
												case 'fixed':
													// check all sessions have 2 or more participants before applying
													if ($siblingdiscount_req_all_met[$blockID] === TRUE) {
														$price = 0;
														$siblingdiscount = 0;
													 	if (!array_key_exists($participantID, $block_priced)) {
															// add together price for all selected sessions and save in first session
															$price = array_sum($siblingdiscount_sessions_selected[$blockID][$participantID]);
															$siblingdiscount = $block->siblingdiscount_amount;
															$block_priced[$participantID] = true;
														}
													}
													break;
											}
											if ($siblingdiscount > $lesson_prices[$lessonID] && $block->siblingdiscount != 'fixed') {
												$siblingdiscount = $lesson_prices[$lessonID];
											}

											$siblingdiscount_total += $siblingdiscount;
										}

										// don't apply auto/sibling discounts now as we need to work out which is bigger after looped all sessions
									}
								}

								if (strpos($block->register_type, 'adults_children') === 0) {
									if($participants[$participantID]->type === "parent"){
										$participants_id_field = "contactID";
									}else{
										$participants_id_field = "childID";
									}
								}

								$total = $price - $discount;

								$data = array(
									'price' => $price,
									'discount' => $discount,
									'total' => $total,
									'balance' => $total,
									'block_priced' => 0,
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								);
								if (array_key_exists($participantID, $block_priced)) {
									$data['block_priced'] = 1;
								}
								$where = array(
									'cartID' => $this->cartID,
									'accountID' => $this->accountID,
									'lessonID' => $lessonID,
									'date' => $date,
									$participants_id_field => $participantID
								);
								$session_present = 1;
								$res = $this->CI->db->update('bookings_cart_sessions', $data, $where);

								// save auto/sibling discount data
								if ($autodiscount > 0) {
									$data_autodiscount = $data;
									$data_autodiscount['discount'] = $autodiscount;
									$data_autodiscount['total'] = $data['price'] - $autodiscount;
									$data_autodiscount['balance'] = $data_autodiscount['total'];
									$autodiscount_data[] = [
										'data' => $data_autodiscount,
										'where' => $where
									];
								}
								if ($siblingdiscount > 0) {
									$data_siblingdiscount = $data;
									$data_siblingdiscount['discount'] = $siblingdiscount;
									$data_siblingdiscount['total'] = $data['price'] - $siblingdiscount;
									$data_siblingdiscount['balance'] = $data_siblingdiscount['total'];
									$siblingdiscount_data[] = [
										'data' => $data_siblingdiscount,
										'where' => $where
									];
								}
							}
						}
					}

					// apply bigger of auto or sibling discount
					if ($autodiscount_total >= $siblingdiscount_total) {
						if (count($autodiscount_data) > 0) {
							foreach ($autodiscount_data as $item) {
								$res = $this->CI->db->update('bookings_cart_sessions', $item['data'], $item['where']);
							}
						}
					} else if ($siblingdiscount_total > $autodiscount_total) {
						if (count($siblingdiscount_data) > 0) {
							foreach ($siblingdiscount_data as $item) {
								$res = $this->CI->db->update('bookings_cart_sessions', $item['data'], $item['where']);
							}
						}
					}
				}
			}


			// remove any blocks in cart not processed, perhaps due to already have happened
			/*$blocks_to_remove = $this->blockIDs;
			if (count($processed_blocks) > 0) {
				foreach ($processed_blocks as $blockID) {
					$key = array_search($blockID, $blocks_to_remove);
					if ($key !== FALSE) {
						unset($blocks_to_remove[$key]);
					}
				}
			}
			if (count($blocks_to_remove) > 0) {
				foreach ($blocks_to_remove as $blockID) {
					$where = array(
						'cartID' => $this->cartID,
						'accountID' => $this->accountID,
						'blockID' => $blockID,
					);
					$res = $this->CI->db->delete('bookings_cart_sessions', $where, 1);
				}
			}*/
		}

		// update totals if editing booking
		if ($this->cart_type == 'booking' || $session_present === 1 || $this->CI->input->post('subscriptions')) {
			$cart_summary = $this->get_cart_summary();
			$data = array(
				'subtotal' => $cart_summary['subtotal'],
				'subscription_total' => isset($cart_summary['subscription_total'])?$cart_summary['subscription_total']:0,
				'discount' => $cart_summary['discount'],
				'total' => $cart_summary['total'],
				'balance' => $cart_summary['total'],
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$where = array(
				'cartID' => $this->cartID,
				'accountID' => $this->accountID
			);
			$res = $this->CI->db->update('bookings_cart', $data, $where, 1);

			// recalc payments
			$this->CI->crm_library->recalc_family_balance($this->familyID);
		}

		// check for unused vouchers
		if ($vouchers !== FALSE) {
			$unused_vouchers = array_diff_key($vouchers, $this->vouchers_used);

			// show message for any vouchers removed no longer applicable to cart
			if (count($unused_vouchers) > 0) {
				$unused_voucher_codes = [];
				foreach ($unused_vouchers as $voucherID => $voucher) {
					if (!in_array($voucherID, $this->voucherIDs_just_added)) {
						$unused_voucher_codes[$voucherID] = $voucher['code'] . ' (' . $voucher['name'] . ')';
					}
				}
				if (count($unused_voucher_codes) > 0) {
					$message = 'Voucher ';
					if (count($unused_voucher_codes) > 1) {
						$message .= 's';
					}
					$message .= natural_language_join($unused_voucher_codes) . ' ';
					if (count($unused_voucher_codes) > 1) {
						$message .= 'have been removed as they are';
					} else {
						$message .= 'has been removed as it\'s';
					}
					$message .= ' no longer applicable to any sessions in your cart';
					$this->CI->session->set_flashdata('error', $message);

					// delete from database
					$where = [
						'accountID' => $this->accountID
					];
					$this->CI->db->where($where)->where_in('id', array_keys($unused_voucher_codes))->delete('bookings_cart_vouchers');
				}
			}

			// voucher just added
			if (count($this->voucherIDs_just_added) > 0) {
				// even though we are only processing one code, multiple vouchers can share the same code so check to see if at least one is useful
				$voucher_useful = FALSE;
				foreach ($this->voucherIDs_just_added as $voucherID) {
					if (!array_key_exists($voucherID, $unused_vouchers)) {
						$voucher_useful = TRUE;
					} else {
						// delete from database
						$where = [
							'id' => $voucherID,
							'accountID' => $this->accountID
						];
						$this->CI->db->delete('bookings_cart_vouchers', $where, 1);
					}
				}

				$code = end($vouchers)['code'];

				// tell user if voucher useful
				if ($voucher_useful === TRUE) {
					$this->CI->session->set_flashdata('success', 'Voucher ' . $code . ' has been added successfully');
				} else {
					// show different message if there's already a voucher in the cart with the same code
					foreach ($vouchers as $voucherID => $voucher) {
						if ($voucher['code'] == $code && !array_key_exists($voucherID, $unused_vouchers)) {
							$this->CI->session->set_flashdata('error', 'Voucher ' . $code . ' could not be applied as it\'s already in your cart');
							return;
						}
					}
					if ($subscription !== NULL && count($subscription) > 0) {
						$this->CI->session->set_flashdata('error', 'Voucher ' . $code . ' cannot be added to a booking with an active subscription');
					} else {
						$this->CI->session->set_flashdata('error', 'Voucher ' . $code . ' could not be applied as it\'s not applicable to any sessions in your cart');
					}
				}
			}
		}
	}

	public function apply_voucher($code) {
		if (empty($this->cartID)) {
			return FALSE;
		}
		$code = trim(strtoupper($code));

		// get existing vouchers
		$existing_project_vouchers = [];
		$existing_global_vouchers = [];
		$vouchers = $this->get_voucher_discounts();
		if ($vouchers !== FALSE) {
			foreach ($vouchers as $voucherID => $voucher) {
				if ($voucher['type'] === 'project') {
					$existing_project_vouchers[] = $voucher['voucherID'];
				} else {
					$existing_global_vouchers[] = $voucher['voucherID'];
				}
			}
		}

		// track voucher IDs to apply
		$project_vouchers = [];
		$global_vouchers = [];

		// look up for a booking specific voucher
		$where = array(
			'accountID' => $this->accountID,
			'code' => $code,
			'active' => 1
		);
		$res = $this->CI->db->from('bookings_vouchers')->where($where)->where_in('bookingID', $this->bookingIDs)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!in_array($row->voucherID, $existing_project_vouchers)) {
					$project_vouchers[] = $row->voucherID;
				}
			}
		}

		// look for global vouchers
		$res = $this->CI->db->from('vouchers')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!in_array($row->voucherID, $existing_global_vouchers)) {
					$global_vouchers[] = $row->voucherID;
				}
			}
		}

		// check for any
		if (count($global_vouchers) === 0 && count($project_vouchers) === 0) {
			$this->errors[] = 'Voucher not found or inactive';
			return FALSE;
		}

		// store project vouchers
		foreach ($project_vouchers as $voucherID) {
			$data = array(
				'accountID' => $this->accountID,
				'cartID' => $this->cartID,
				'voucherID' => $voucherID,
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$res = $this->CI->db->insert('bookings_cart_vouchers', $data);

			// store ID as will check has valid items before showing message
			$this->voucherIDs_just_added[] = $this->CI->db->insert_id();
		}

		// store global vouchers
		foreach ($global_vouchers as $voucherID) {
			$data = array(
				'accountID' => $this->accountID,
				'cartID' => $this->cartID,
				'voucherID_global' => $voucherID,
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$res = $this->CI->db->insert('bookings_cart_vouchers', $data);

			// store ID as will check has valid items before showing message
			$this->voucherIDs_just_added[] = $this->CI->db->insert_id();
		}

		return TRUE;
	}

	public function remove_voucher($id) {
		if (empty($this->cartID)) {
			return FALSE;
		}
		$where = array(
			'accountID' => $this->accountID,
			'cartID' => $this->cartID,
			'id' => $id,
		);
		$res = $this->CI->db->delete('bookings_cart_vouchers', $where, 1);
		if ($this->CI->db->affected_rows() > 0) {
			return TRUE;
		}

		$this->errors[] = 'Voucher could not be removed';
		return FALSE;
	}

	public function is_only_subscriptions() {
		if (empty($this->cartID)) {
			return FALSE;
		}

		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'bookings_cart_subscriptions.cartID' => $this->cartID
		);

		$res = $this->CI->db->select('subscriptions.subID, bookings_cart_sessions.sessionID')
			->from('bookings_cart_subscriptions')
			->join('bookings_cart_sessions', 'bookings_cart_sessions.cartID = bookings_cart_subscriptions.cartID', 'left')
			->join('subscriptions', '(bookings_cart_subscriptions.subID = subscriptions.subID AND bookings_cart_subscriptions.bookingID = ' . $this->CI->db->dbprefix('subscriptions') . '.bookingID)', 'left')
			->where($where)
			->get();

		if($res->num_rows() == 0){
			return FALSE;
		}
		foreach($res->result() as $result) {
			if(!empty($result->sessionID)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	public function cart_includes_subscriptions() {
		if (empty($this->cartID)) {
			return FALSE;
		}

		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'bookings_cart_subscriptions.cartID' => $this->cartID
		);

		$res = $this->CI->db->from('bookings_cart_subscriptions')->where($where)->get();

		if($res->num_rows() > 0) {
			return TRUE;
		}

		return FALSE;
	}

	public function get_cart_subscription_payment_provider() {
		if (empty($this->cartID)) {
			return FALSE;
		}

		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'bookings_cart_subscriptions.cartID' => $this->cartID
		);

		$res = $this->CI->db->select('subscriptions.payment_provider')
							->from('bookings_cart_subscriptions')
							->join('subscriptions', '(bookings_cart_subscriptions.subID = subscriptions.subID AND bookings_cart_subscriptions.bookingID = ' . $this->CI->db->dbprefix('subscriptions') . '.bookingID)', 'left')
							->where($where)
							->get();

		if($res->num_rows() > 0) {
			$provider_array = [];
			foreach($res->result() as $type) {
				$provider_array[] = $type->payment_provider;
			}
			return $provider_array;
		} else {
			return NULL;
		}

	}

	public function get_cart_subscription_amount($provider) {
		if (empty($this->cartID)) {
			return FALSE;
		}

		$price = 0;

		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'bookings_cart_subscriptions.cartID' => $this->cartID,
			'subscriptions.payment_provider' => $provider
		);

		$res = $this->CI->db->select('subscriptions.price')
							->from('bookings_cart_subscriptions')
							->join('subscriptions', '(bookings_cart_subscriptions.subID = subscriptions.subID AND bookings_cart_subscriptions.bookingID = ' . $this->CI->db->dbprefix('subscriptions') . '.bookingID)', 'left')
							->where($where)
							->get();

		if($res->num_rows() == 0) {
			return $price;
		} else {
			$prices = 0;
			foreach($res->result() as $sub) {
				$prices += $sub->price;
			}
			return $prices;
		}
	}

	public function get_stripe_price_id() {
		if (empty($this->cartID)) {
			return FALSE;
		}

		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'bookings_cart_subscriptions.cartID' => $this->cartID,
			'subscriptions.payment_provider' => 'stripe'
		);

		$res = $this->CI->db->select('subscriptions.stripe_price_id, count('.$this->CI->db->dbprefix('subscriptions').'.stripe_price_id) as sub_count')
							->from('bookings_cart_subscriptions')
							->join('subscriptions', '(bookings_cart_subscriptions.subID = subscriptions.subID AND bookings_cart_subscriptions.bookingID = ' . $this->CI->db->dbprefix('subscriptions') . '.bookingID)', 'left')
							->where($where)
							->group_by('subscriptions.stripe_price_id')
							->get();

		if($res->num_rows() > 0) {
			foreach($res->result() as $priceId) {
				$priceIds[$priceId->stripe_price_id] = $priceId->sub_count;
			}
			return $priceIds;
		} else {
			return NULL;
		}

	}

	public function get_vouchers($include_desc = FALSE) {
		if (empty($this->cartID)) {
			return FALSE;
		}
		$where = array(
			'bookings_cart_vouchers.accountID' => $this->accountID,
			'bookings_cart_vouchers.cartID' => $this->cartID
		);
		$res = $this->CI->db->select('bookings_cart_vouchers.id, bookings_vouchers.code, vouchers.code as code_global, bookings_vouchers.name, vouchers.name as name_global')->from('bookings_cart_vouchers')->join('bookings_vouchers', 'bookings_cart_vouchers.voucherID = bookings_vouchers.voucherID', 'left')->join('vouchers', 'bookings_cart_vouchers.voucherID_global = vouchers.voucherID', 'left')->where($where)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		$vouchers = array();

		foreach ($res->result() as $row) {
			if (empty($row->code)) {
				$row->code = $row->code_global;
				$row->name = $row->name_global;
			}
			if ($include_desc === TRUE) {
				$row->code .= ' <span>(' . $row->name . ')</span>';
			}
			$vouchers[$row->id] = $row->code;
		}

		return $vouchers;
	}

	public function get_voucher_discounts() {
		if (empty($this->cartID)) {
			return FALSE;
		}

		$vouchers = array();

		// booking specific
		$where = array(
			'bookings_cart_vouchers.accountID' => $this->accountID,
			'bookings_cart_vouchers.cartID' => $this->cartID
		);
		$res = $this->CI->db->select('bookings_cart_vouchers.id, bookings_vouchers.*, GROUP_CONCAT(DISTINCT ' . $this->CI->db->dbprefix('bookings_lessons_vouchers') . '.lessonID) AS lessons')
			->from('bookings_cart_vouchers')
			->join('bookings_vouchers', 'bookings_cart_vouchers.voucherID = bookings_vouchers.voucherID', 'inner')
			->join('bookings_lessons_vouchers', 'bookings_cart_vouchers.voucherID = bookings_lessons_vouchers.voucherID', 'left')
			->where($where)
			->group_by('bookings_vouchers.voucherID')
			->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$voucher = array(
					'name' => $row->name,
					'code' => $row->code,
					'typeIDs' => array(),
					'lessonIDs' => array(),
					'discount_type' => $row->discount_type,
					'discount' => $row->discount,
					'voucherID' => $row->voucherID,
					'type' => 'project',
					'siblingdiscount' => boolval($row->siblingdiscount)
				);
				$lessons = (array)explode(',', $row->lessons);
				$voucher['lessonIDs'] = array_filter($lessons);
				if (count($lessons) > 0) {
					$vouchers[$row->id] = $voucher;
				}
			}
		}

		// global
		$where = array(
			'bookings_cart_vouchers.accountID' => $this->accountID,
			'bookings_cart_vouchers.cartID' => $this->cartID
		);
		$res = $this->CI->db->select('bookings_cart_vouchers.id, vouchers.*, GROUP_CONCAT(DISTINCT ' . $this->CI->db->dbprefix('vouchers_lesson_types') . '.typeID) AS types')
			->from('bookings_cart_vouchers')
			->join('vouchers', 'bookings_cart_vouchers.voucherID_global = vouchers.voucherID', 'inner')
			->join('vouchers_lesson_types', 'vouchers.voucherID = vouchers_lesson_types.voucherID', 'left')
			->where($where)
			->group_by('vouchers.voucherID')
			->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$voucher = array(
					'name' => $row->name,
					'code' => $row->code,
					'typeIDs' => array(),
					'lessonIDs' => array(),
					'discount_type' => $row->discount_type,
					'discount' => $row->discount,
					'voucherID' => $row->voucherID,
					'type' => 'global',
					'siblingdiscount' => boolval($row->siblingdiscount)
				);
				$types = (array)explode(',', $row->types);
				$voucher['typeIDs'] = array_filter($types);
				if (count($types) > 0) {
					$vouchers[$row->id] = $voucher;
				}
			}
		}

		if (count($vouchers) == 0) {
			return FALSE;
		}

		return $vouchers;
	}

	public function get_childcarevoucher_providers($notices = FALSE, $ignore_cartID = FALSE) {
		if (empty($this->cartID) && $ignore_cartID == FALSE) {
			return FALSE;
		}
		$where = array(
			'accountID' => $this->accountID,
			'active' => 1
		);
		$res = $this->CI->db->from('settings_childcarevoucherproviders')->where($where)->order_by('name asc, reference asc')->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}

		$providers = array();

		foreach ($res->result() as $row) {
			if ($notices === FALSE) {
				$providers[$row->providerID] = $row->name . ' (Reference: ' . $row->reference . ')';
			} else if (!empty($row->information)) {
				$providers[$row->providerID] = $row->information;
			}
		}

		return $providers;
	}

	public function get_errors() {
		if (count($this->errors) == 0) {
			return FALSE;
		}
		return $this->errors;
	}

	// get online booking blocks
	function get_blocks($where = array(), $search_params = array(), $custom_where_sql = NULL, $view_flag = TRUE) {

		// set default params
		$blocks = array();

		// get session types applicable to auto discounts
		$autodiscount_lesson_types = array();
		$where_types = array(
			'lesson_types.accountID' => $this->accountID,
			'lesson_types.exclude_autodiscount !=' => 1
		);
		$res = $this->CI->db->select('lesson_types.typeID')->from('lesson_types')->where($where_types)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$autodiscount_lesson_types[] = $row->typeID;
			}
		}

		// get vouchers
		$vouchers = $this->get_voucher_discounts();

		// search
		$where['bookings_blocks.accountID'] = $this->accountID;
		if (!$this->in_crm) {
			// allow booking in crm
			if (!array_key_exists('bookings_blocks.public', $where)) {
				$where['bookings_blocks.public'] = 1;
			}
			if (!array_key_exists('bookings.public', $where)) {
				$where['bookings.public'] = 1;
			}
		} else {
			// allow booking in crm
			if (!array_key_exists('bookings.disable_online_booking !=', $where)) {
				unset($where['bookings.disable_online_booking']);
			}
		}
		if (!array_key_exists('bookings.project', $where)) {
			$where['bookings.project'] = 1;
		}
		$where_custom = '(`' . $this->CI->db->dbprefix("bookings") . '`.`register_type` LIKE \'%children%\' OR `' . $this->CI->db->dbprefix("bookings") . '`.`register_type` LIKE \'%individuals%\')' . $custom_where_sql;

		// run query
		$res = $this->CI->db->select('bookings_blocks.bookingID, bookings_blocks.blockID, bookings_blocks.startDate, bookings_blocks.require_all_sessions, bookings_blocks.block_price, bookings.type as booking_type, bookings.name as booking, bookings.disable_online_booking, bookings.online_booking_password, bookings.limit_participants, bookings.register_type, bookings.booking_requirement, bookings.booking_postcodes, bookings.autodiscount, bookings.autodiscount_amount, bookings.siblingdiscount, bookings.siblingdiscount_amount, bookings_blocks.endDate, bookings_blocks.name as block, bookings.website_description, bookings.booking_instructions, bookings.disable_online_booking, bookings.min_age as booking_min_age, bookings.max_age as booking_max_age, bookings_blocks.min_age as block_min_age, bookings_blocks.max_age as block_max_age, orgs.name as org, block_orgs.name as block_org, brands.colour as brand_colour, bookings.location, bookings.subscriptions_only,
		address.addressID, address.address1, address.address2, address.address3, address.town, address.county, address.postcode,
		block_address.addressID as block_addressID, block_address.address1 as block_address1, block_address.address2 as block_address2, block_address.address3 as block_address3, block_address.town as block_town, block_address.county as block_county, block_address.postcode as block_postcode,
		GROUP_CONCAT(' . $this->CI->db->dbprefix('lesson_types') . ' .colour) as lesson_colours, GROUP_CONCAT(DISTINCT ' . $this->CI->db->dbprefix('bookings_lessons') . ' .day) as lesson_days, CONCAT_WS(\',\', ST_X(address.location), ST_Y(address.location)) as booking_coords, CONCAT_WS(\',\', ST_X(block_address.location), ST_Y(block_address.location)) as block_coords, GROUP_CONCAT(DISTINCT ' . $this->CI->db->dbprefix('bookings_images') . ' .path ORDER BY ' . $this->CI->db->dbprefix('bookings_images') . ' .order) as booking_images, GROUP_CONCAT(DISTINCT ' . $this->CI->db->dbprefix('bookings_tags') . ' .tagID) as booking_tags')
			->from('bookings_blocks')
			->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
			->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
			->join('brands', 'bookings.brandID = brands.brandID', 'left')
			->join('bookings_images', 'bookings.bookingID = bookings_images.bookingID', 'left')
			->join('bookings_tags', 'bookings.bookingID = bookings_tags.bookingID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->join('orgs_addresses as address', 'bookings.addressID = address.addressID', 'left')
			->join('orgs_addresses as block_address', 'bookings_blocks.addressID = block_address.addressID', 'left')
			->where($where)
			->where($where_custom, NULL, FALSE)
			->order_by('bookings_blocks.startDate asc, bookings_blocks.name asc')
			->group_by('bookings_blocks.blockID')
			->get();

		if ($res !== FALSE && $res->num_rows() > 0) {

			// if location search, new instance of geokit
			if (isset($search_params['location_coordinates']) && $search_params['location_coordinates'] != '') {
				$geokit = new Geokit\Math();
			}

			foreach ($res->result() as $row) {
				// get min age
				$row->min_age = $this->CI->settings_library->get('min_age', $this->accountID);
				if (!empty($row->booking_min_age)) {
					$row->min_age = $row->booking_min_age;
				}
				if (!empty($row->block_min_age)) {
					$row->min_age = $row->block_min_age;
				}

				// get max age
				$row->max_age = $this->CI->settings_library->get('max_age', $this->accountID);
				if (!empty($row->booking_max_age)) {
					$row->max_age = $row->booking_max_age;
				}
				if (!empty($row->block_max_age)) {
					$row->max_age = $row->block_max_age;
				}

				// if searching by age, check
				if (isset($search_params['age']) && $search_params['age'] != '') {
					// no limits, don't show
					if (empty($row->min_age) && empty($row->max_age)) {
						continue;
					} else if (!empty($row->min_age) && intval($search_params['age']) < intval($row->min_age)) {
						continue;
					} else if (!empty($row->max_age) && intval($search_params['age']) > intval($row->max_age)) {
						continue;
					}
				}

				// use dept colour
				$row->colour = brand_colour($row->brand_colour);
				// or most used session type colour if set
				$lesson_type_colours = explode(",", $row->lesson_colours);
				$lesson_type_colours = array_filter($lesson_type_colours);
				if (is_array($lesson_type_colours) && count($lesson_type_colours) > 0) {
					$row->colour = array_most_common_value($lesson_type_colours);
				}

				// override org if set at block level
				if (!empty($row->block_org)) {
					$row->org = $row->block_org;
				}

				// set vars to be populated later
				$row->dates = array();
				$row->lessons = array();
				$row->lesson_columns = array();
				$row->lesson_types_summary = array();
				$row->availability_status = NULL;
				$row->availability_status_class = NULL;
				$row->started = FALSE;

				// get address
				if ($row->booking_type == 'booking') {
					$row->addressID = $row->block_addressID;
					$address_parts = array();
					if (!empty($row->block_address1)) {
						$address_parts[] = $row->block_address1;
					}
					if (!empty($row->block_address2)) {
						$address_parts[] = $row->block_address2;
					}
					if (!empty($row->block_address3)) {
						$address_parts[] = $row->block_address3;
					}
					if (!empty($row->block_town)) {
						$address_parts[] = $row->block_town;
					}
					if (!empty($row->block_county)) {
						$address_parts[] = $row->block_county;
					}
					if (!empty($row->block_postcode)) {
						$address_parts[] = $row->block_postcode;
					}
					$row->address = implode(", " , $address_parts);
				} else {
					$address_parts = array();
					if (!empty($row->address1)) {
						$address_parts[] = $row->address1;
					}
					if (!empty($row->address2)) {
						$address_parts[] = $row->address2;
					}
					if (!empty($row->address3)) {
						$address_parts[] = $row->address3;
					}
					if (!empty($row->town)) {
						$address_parts[] = $row->town;
					}
					if (!empty($row->county)) {
						$address_parts[] = $row->county;
					}
					if (!empty($row->postcode)) {
						$address_parts[] = $row->postcode;
					}
					$row->address = implode(", " , $address_parts);
				}

				// get coordinates
				$row->coordinates = NULL;
				$row->distance = NULL;
				$coords = $row->booking_coords;
				if ($row->booking_type == 'booking') {
					$coords = $row->block_coords;
				}
				$coords = explode(",", $coords);
				if (count($coords) == 2) {
					$row->coordinates = $coords;
					if (isset($search_params['location_coordinates']) && $search_params['location_coordinates'] != '') {
						$distance = $geokit->distanceHaversine($search_params['location_coordinates'], $row->coordinates);
						$row->distance = $distance->miles();
					}
				}

				// if location search and no distance, skip
				if (isset($search_params['location_coordinates']) && $search_params['location_coordinates'] != '' && empty($row->distance)) {
					continue;
				}

				// get images
				$row->images = array();
				$images = explode(",", $row->booking_images);
				if (is_array($images) && count($images) > 0) {
					// remove empty
					$images = array_filter($images);
					// return links
					foreach ($images as $image) {
						$row->images[] = array(
							'full' => site_url('attachment/booking-image/' . $image . '/' . $this->accountID),
							'thumb' => site_url('attachment/booking-image/' . $image . '/thumb/' . $this->accountID)
						);
					}
				}

				// get attachments
				$where = array(
					'bookingID' => $row->bookingID,
					'showonbookingssite' => 1
				);
				$res_attachments = $this->CI->db->from('bookings_attachments')->where($where)->order_by('name asc')->get();
				$row->attachments = array();
				if ($res_attachments->num_rows() > 0) {
					foreach ($res_attachments->result() as $attachment) {
						$row->attachments[$attachment->path] = $attachment->name;
					}
				}

				// get tags
				$tags = array();
				$booking_tags = explode(",", $row->booking_tags);
				if (is_array($booking_tags) && count($booking_tags) > 0) {
					// remove empty
					$booking_tags = array_filter($booking_tags);
					// return links
					foreach ($booking_tags as $tagID) {
						$tags[] = $tagID;
					}
				}
				$row->booking_tags = $tags;

				// if not requiring all sessions, set block price to NULL
				if ($row->require_all_sessions != 1 || empty($row->block_price)) {
					$row->block_price = NULL;
				}

				$row->subs = array();

				if($this->online_booking_subscription_module) {
					//get subscriptions
					$where = array(
						'subscriptions.bookingID' => $row->bookingID,
						'subscriptions.accountID' => $this->accountID,
						'subscriptions.familyID IS NULL' => null
					);

					$res_subs = $this->CI->db->select('subName')->from('subscriptions')->where($where)->get();
					if ($res_subs->num_rows() > 0) {
						foreach ($res_subs->result() as $sub) {
							$row->subs[] = $sub->subName;
						}
					}
				}

				// save
				$blocks[$row->blockID] = $row;
			}
		}

		if (count($blocks) == 0) {
			return $blocks;
		}

		// get sessions cancellations for blocks
		$lesson_cancellations = array();
		$where = array(
			'bookings_lessons.accountID' => $this->accountID,
			'bookings_lessons_exceptions.type' => 'cancellation'
		);
		$where_in = array_keys($blocks);
		$res = $this->CI->db->select('bookings_lessons.lessonID, bookings_lessons_exceptions.date')
			->from('bookings_lessons')
			->join('bookings_lessons_exceptions', 'bookings_lessons.lessonID = bookings_lessons_exceptions.lessonID', 'inner')
			->where($where)
			->where_in('bookings_lessons.blockID', $where_in)
			->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_cancellations[$row->lessonID][$row->date] = $row->date;
			}
		}

		// get sessions participants
		$lesson_participants = array();
		$where = array(
			'bookings_cart_sessions.accountID' => $this->accountID,
			'bookings_cart.type' => 'booking'
		);
		$where_in = array_keys($blocks);
		$res = $this->CI->db->select('bookings_cart_sessions.lessonID, bookings_cart_sessions.date, COUNT(DISTINCT ' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.childID) as participants_children, COUNT(DISTINCT ' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.contactID) as participants_contacts')
			->from('bookings_cart_sessions')
			->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
			->where($where)
			->where_in('bookings_cart_sessions.blockID', $where_in)
			->group_by('bookings_cart_sessions.lessonID, bookings_cart_sessions.date')
			->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_participants[$row->lessonID][$row->date] = $row->participants_children + $row->participants_contacts;
			}
		}

		// get sessions for blocks
		$lesson_prices = array();
		$lesson_types_summary = array();
		$blocks_to_skip = array();
		$fixed_autodiscount_max_discounts = array();
		$fixed_siblingdiscount_max_discounts = array();
		$where = array(
			'bookings_lessons.accountID' => $this->accountID
		);
		$where_in = array_keys($blocks);
		$res = $this->CI->db->select('bookings_lessons.lessonID, bookings_lessons.blockID, bookings_lessons.day, bookings_lessons.typeID, bookings_lessons.startDate, bookings_lessons.endDate, bookings_lessons.price, bookings_lessons.startTime, bookings_lessons.endTime, bookings_lessons.target_participants, bookings_lessons.min_age, bookings_lessons.max_age, bookings_lessons.booking_cutoff, lesson_types.exclude_online_booking_price_summary, lesson_types.exclude_online_booking_availability_status, lesson_types.name as lesson_type, activities.name as activity, bookings_lessons.activity_other, bookings_lessons.addressID, orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode')
			->from('bookings_lessons')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
			->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
			->where($where)
			->where_in('bookings_lessons.blockID', $where_in)
			->order_by('bookings_lessons.startTime asc, lesson_type asc')
			->group_by('bookings_lessons.lessonID')
			->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $lesson) {
				// if skipping block due to no lessons, continue
				if (in_array($lesson->blockID, $blocks_to_skip)) {
					continue;
				}

				// save session type and price
				if (!empty($lesson->typeID) && $lesson->exclude_online_booking_price_summary != 1) {
					$lesson_types_summary[$lesson->typeID] = $lesson->lesson_type;
					$lesson_prices[$lesson->blockID][$lesson->typeID][] = floatval($lesson->price);
				}

				// get block
				$block = &$blocks[$lesson->blockID];

				// get block dates
				$date = $block->startDate;
				$end_date = $block->endDate;

				// check if overridden on block level
				if (!empty($lesson->startDate)) {
					$date = $lesson->startDate;
				}
				if (!empty($lesson->endDate)) {
					$end_date = $lesson->endDate;
				}

				// loop through dates in lesson
				while (strtotime($date) <= strtotime($end_date)) {
					$day = strtolower(date('l', strtotime($date)));
					// if days is on one of the dates
					if ($day == $lesson->day && !isset($lesson_cancellations[$lesson->lessonID][$date])) {
						$lesson_timestamp = strtotime($date . ' ' . $lesson->startTime);
						$lesson_cutoff_hours = $this->CI->settings_library->get('booking_cutoff', $this->accountID);
						if ($lesson->booking_cutoff !== NULL) {
							$lesson_cutoff_hours = $lesson->booking_cutoff;
						}
						$lesson_cutoff = $lesson_timestamp - ($lesson_cutoff_hours*60*60);

						// if only showing future, or booking requirment is all work out if session is after cut off
						if ((!$this->in_crm && (!isset($search_params['show_all']) || $this->cart_type == 'cart') && $view_flag)
							|| (isset($search_params['future_only']) && in_array($search_params['future_only'], array(true, 1, 2)))
							|| (isset($search_params['sessions_after']) && !empty($search_params['sessions_after']))
							|| ($block->booking_type == 'booking' && $block->booking_requirement == 'all' && !isset($search_params['show_all']) && $view_flag)) {


							$compare_time = $lesson_cutoff;
							// if val of 2, compare on session end time
							if (isset($search_params['future_only']) && $search_params['future_only'] === 2) {
								$compare_time = strtotime($date . ' ' . $lesson->endTime);
							}

							// by default compare to now
							$compare_to = time();

							// if looking for sessions after a specific time, compare to that time
							if (isset($search_params['sessions_after']) && !empty($search_params['sessions_after'])) {
								$compare_to = strtotime($search_params['sessions_after']);
							}

							// if cut off is in past, continue
							if ($compare_time < $compare_to) {
								// move to next day
								$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));

								// if using autodiscount and a session has already occurred, turn off
								if ($block->autodiscount != 'off') {
									$block->autodiscount = 'off';
								}

								// if using fixed sibling discount and a session has already occurred, turn off
								if ($block->siblingdiscount === 'fixed') {
									$block->siblingdiscount = 'off';
								}

								// mark as started
								$block->started = TRUE;

								// if booking requirement is all, stop and remove all lessons
								if (!$this->in_crm && $block->booking_type == 'booking' && $block->booking_requirement == 'all') {
									$block->lessons = array();
									$block->dates = array();
									$blocks_to_skip[] = $block->blockID;
									break;
								}
								continue;
							}
						}

						// use block ages, if sessions ages empty
						if (empty($lesson->min_age)) {
							$lesson->min_age = $blocks[$lesson->blockID]->min_age;
						}
						if (empty($lesson->max_age)) {
							$lesson->max_age = $blocks[$lesson->blockID]->max_age;
						}

						// if no price, set to 0
						if (empty($lesson->price)) {
							$lesson->price = 0;
						}

						// build session info
						$lesson_info = array(
							'lessonID' => $lesson->lessonID,
							'typeID' => $lesson->typeID,
							'type' => $lesson->lesson_type,
							'activity' => $lesson->activity_other,
							'time' => substr($lesson->startTime, 0, 5) . ' to ' . substr($lesson->endTime, 0, 5),
							'date' => $date,
							'target_participants' => $lesson->target_participants,
							'actual_participants' => 0,
							'available' => 'unlimited',
							'sold_out' => FALSE,
							'price' => $lesson->price,
							'min_age' => $lesson->min_age,
							'max_age' => $lesson->max_age,
							'exclude_availability_status' => $lesson->exclude_online_booking_availability_status,
							'discount' => 0,
							'discount_voucherID' => NULL,
							'autodiscount' => false,
							'siblingdiscount' => false,
							'cutoff' => $lesson_cutoff,
							'address' => NULL
						);

						// if session address different to block, add
						if (!empty($lesson->addressID) && $lesson->addressID != $blocks[$lesson->blockID]->addressID) {
							$address_parts = array();
							if (!empty($lesson->address1)) {
								$address_parts[] = $lesson->address1;
							}
							if (!empty($lesson->address2)) {
								$address_parts[] = $lesson->address2;
							}
							if (!empty($lesson->address3)) {
								$address_parts[] = $lesson->address3;
							}
							if (!empty($lesson->town)) {
								$address_parts[] = $lesson->town;
							}
							if (!empty($lesson->county)) {
								$address_parts[] = $lesson->county;
							}
							if (!empty($lesson->postcode)) {
								$address_parts[] = $lesson->postcode;
							}
							$lesson_info['address'] = implode(", " , $address_parts);
						}

						// get activity if set
						if (!empty($lesson->activity)) {
							$lesson_info['activity'] = $lesson->activity;
						}

						// get actual participants
						if (isset($lesson_participants[$lesson->lessonID][$date])) {
							$lesson_info['actual_participants'] = intval($lesson_participants[$lesson->lessonID][$date]);
						}

						// work out available places if not unlimited
						if ($lesson_info['target_participants'] > 0 && $block->limit_participants == 1) {
							$available_places = $lesson_info['target_participants'] - $lesson_info['actual_participants'];
							if ($available_places <= 0) {
								$available_places = 0;
								$lesson_info['sold_out'] = TRUE;
							}
							$lesson_info['available'] = $available_places;
						}

						if ($block->require_all_sessions == 1 && $block->block_price !== NULL) {
							// no discounts if fixed block price
						} else {
							// vouchers
							if ($vouchers !== FALSE) {
								$highest_discount = 0;
								$potential_discount = 0;
								foreach ($vouchers as $voucherID => $voucher) {
									// skip sibling discount vouchers as processed in validate_cart
									if ($voucher['siblingdiscount'] === TRUE) {
										continue;
									}
									// if in allowed types or lessons
									if (in_array($lesson->lessonID, $voucher['lessonIDs']) || in_array($lesson->typeID, $voucher['typeIDs'])) {
										switch ($voucher['discount_type']) {
											case 'amount':
												$potential_discount = $voucher['discount'];
												break;
											case 'percentage':
												$potential_discount = $lesson->price * ($voucher['discount']/100);
												break;
										}
										// if discount more than session price, reduce
										if ($potential_discount > $lesson->price) {
											$potential_discount = $lesson->price;
										}
										if ($potential_discount > $highest_discount) {
											$highest_discount = $potential_discount;
											// store highest discount voucher ID in lesson so can remove no longer relevant vouchers
											$lesson_info['discount_voucherID'] = $voucherID;
										}
									}
								}
								$lesson_info['discount'] = $highest_discount;
							} else {
								// if no vouchers, calc auto discount if not off, and is a session type not excluded
								if ($block->autodiscount !== 'off' && in_array($lesson->typeID, $autodiscount_lesson_types)) {
									switch ($block->autodiscount) {
										case 'amount':
											$lesson_info['autodiscount'] = $block->autodiscount_amount;
											break;
										case 'percentage':
											$lesson_info['autodiscount'] = ($lesson_info['price']*($block->autodiscount_amount/100));
											break;
										case 'fixed':
											$lesson_info['autodiscount'] = -1;
											// track max discount for fixed auto discount if user entered a higher amount than available
											if (!array_key_exists($block->blockID, $fixed_autodiscount_max_discounts)) {
												$fixed_autodiscount_max_discounts[$block->blockID] = 0;
											}
											$fixed_autodiscount_max_discounts[$block->blockID] += $lesson_info['price'];
											break;
									}
									// if amount is more than price, cap
									if ($lesson_info['autodiscount'] > $lesson_info['price'] && $block->autodiscount !== 'fixed') {
										$lesson_info['autodiscount'] = $lesson_info['price'];
									}
								}
								// if no vouchers, calc siblingdiscount discount if not off
								if ($block->siblingdiscount !== 'off') {
									switch ($block->siblingdiscount) {
										case 'amount':
											$lesson_info['siblingdiscount'] = $block->siblingdiscount_amount;
											break;
										case 'percentage':
											$lesson_info['siblingdiscount'] = ($lesson_info['price']*($block->siblingdiscount_amount/100));
											break;
										case 'fixed':
											$lesson_info['siblingdiscount'] = -1;
											// track max discount for fixed auto discount if user entered a higher amount than available
											if (!array_key_exists($block->blockID, $fixed_siblingdiscount_max_discounts)) {
												$fixed_siblingdiscount_max_discounts[$block->blockID] = 0;
											}
											$fixed_siblingdiscount_max_discounts[$block->blockID] += $lesson_info['price'];
											break;
									}
									// if amount is more than price, cap
									if ($lesson_info['siblingdiscount'] > $lesson_info['price'] && $block->siblingdiscount !== 'fixed') {
										$lesson_info['siblingdiscount'] = $lesson_info['price'];
									}
								}
							}
						}

						// add to dates
						$block->dates[$date][$lesson->lessonID] = $lesson_info;

						// add to summary without date specific info
						unset($lesson_info['date']);
						unset($lesson_info['actual_participants']);
						unset($lesson_info['available']);
						unset($lesson_info['sold_out']);
						$block->lessons[$lesson->lessonID] = $lesson_info;

						// add to session columns
						$key = $lesson_info['time'] . '!#!' . $lesson_info['type'] . '!#!' . $lesson_info['activity'];
						if (!array_key_exists($key, $block->lesson_columns)) {
							$block->lesson_columns[$key] = array();
						}
						$block->lesson_columns[$key][$lesson->lessonID] = $lesson->lessonID;
					}
					$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
				}
			}
		}

		// loop blocks
		foreach ($blocks as $blockID => &$block) {

			// if no sessions in block, skip
			if (count($block->lessons) == 0) {
				unset($blocks[$blockID]);
				continue;
			}

			// work out price summary
			if (isset($lesson_prices[$blockID])) {
				// if require all sessions, add all prices
				if ($block->require_all_sessions == 1) {
					if ($block->block_price !== NULL) {
						$block_total = floatval($block->block_price);
					} else {
						$block_total = 0;
						foreach ($block->dates as $date => $lessons) {
							foreach ($lessons as $lesson) {
								// check if past cut off
								if ($lesson['cutoff'] < time()) {
									continue;
								}
								$block_total += floatval($lesson['price']);
							}
						}
					}

					$price_formatted = currency_symbol($this->accountID) . number_format($block_total, 2);
					// if no decimals, only show whole
					if ($block_total === round($block_total)) {
						$price_formatted = currency_symbol($this->accountID) . round($block_total);
					}
					$block->lesson_types_summary['Price'] = $price_formatted;

				} else {
					// else, show price for each type
					foreach ($lesson_prices[$blockID] as $typeID => $prices) {

						$price_data = array_values($prices);
						$unique_prices = array_unique($prices);

						// sort by price asc
						natsort($price_data);

						// loop
						foreach ($price_data as $price) {
							$price_prefix = NULL;
							// if more than 1 price, say from
							if (count($unique_prices) > 1) {
								$price_prefix = 'From ';
							}
							if ($price == 0) {
								$price_formatted = 'Free';
							} else {
								$price_formatted = currency_symbol($this->accountID) . number_format($price, 2);
								// if no decimals, only show whole
								if ($price === round($price)) {
									$price_formatted = currency_symbol($this->accountID) . round($price);
								}
							}
							$block->lesson_types_summary[$lesson_types_summary[$typeID]] = $price_prefix . $price_formatted;
							break;
						}
					}
				}
			}

			// sort by name
			ksort($block->lesson_types_summary);
			ksort($block->lesson_columns);

			// sort dates
			//When a booking is subscriptions only, group sessions by Monday to Sunday order, otherwise
			//order by actual date.
			if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
				$week_order = array_flip(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'));
				uksort($block->dates, function($a, $b) use ($week_order) { return $week_order[date("l",strtotime($a))] - $week_order[date("l",strtotime($b))]; });
			}
			else {
				ksort($block->dates);
			}

			// work out availability status
			$average_fullness_array = array();
			$average_fullness = 0;
			$all_sold = TRUE;
			foreach ($block->dates as $date => $lessons) {
				foreach ($lessons as $lessonID => $lesson) {
					// only if including in calc and date is more than today
					if ($lesson['exclude_availability_status'] != 1 && strtotime($date) > strtotime(date('Y-m-d'))) {
						if ($lesson['available'] === 'unlimited') {
							$val = 0;
						} else {
							$val = $lesson['available'] === 0 ? 1 : $lesson['actual_participants']/$lesson['target_participants'];
						}
						$average_fullness_array[] = $val;
						if ($val < 1) {
							$all_sold = FALSE;
						}
					}
				}
			}

			if (count($average_fullness_array) > 0) {
				$average_fullness = array_sum($average_fullness_array)/count($average_fullness_array);
				if ($average_fullness < .1) {
					$block->availability_status = 'Good';
					$block->availability_status_class = 'good';
				} else if ($all_sold === TRUE) {
					$block->availability_status = 'Sold Out';
					$block->availability_status_class = 'soldout';
				} else {
					$block->availability_status = 'Limited';
					$block->availability_status_class = 'limited';
				}
			}

			// if autodiscount in block off, double check to ensure all sessions have it marked as off
			if ($block->autodiscount === 'off') {
				foreach ($block->dates as $date => $lessons) {
					foreach ($lessons as $lessonID => $lesson) {
						$block->dates[$date][$lessonID]['autodiscount'] = false;
					}
				}
			}

			// work out max fixed autodiscount
			if ($block->autodiscount === 'fixed' && array_key_exists($blockID, $fixed_autodiscount_max_discounts) && $fixed_autodiscount_max_discounts[$blockID] < $block->autodiscount_amount) {
				$block->autodiscount_amount = $fixed_autodiscount_max_discounts[$blockID];
			}

			// if sibling discount in block off, double check to ensure all sessions have it marked as off
			if ($block->siblingdiscount === 'off') {
				foreach ($block->dates as $date => $lessons) {
					foreach ($lessons as $lessonID => $lesson) {
						$block->dates[$date][$lessonID]['siblingdiscount'] = false;
					}
				}
			}

			// work out max fixed sibling discount
			if ($block->siblingdiscount === 'fixed' && array_key_exists($blockID, $fixed_siblingdiscount_max_discounts) && $fixed_siblingdiscount_max_discounts[$blockID] < $block->siblingdiscount_amount) {
				$block->siblingdiscount_amount = $fixed_siblingdiscount_max_discounts[$blockID];
			}
		}

		// order by distance if location search
		if (isset($search_params['location_coordinates']) && $search_params['location_coordinates'] != '') {
			$blocks = array_orderby_object_keys($blocks, 'distance', SORT_ASC);
		}

		return $blocks;
	}

	function get_family_credit_limit() {
		return $this->family_credit_limit;
	}

	function get_family_account_balance() {
		return $this->family_account_balance;
	}

	function apply_fixed_discount($cart_summary){
		if($cart_summary['total'] != 0 ){
			$vouchers = $this->get_voucher_discounts();
			$total_fixed_discount = 0;
			if ($vouchers !== FALSE) {
				foreach ($vouchers as $key => $voucher) {
					if($voucher['discount_type']=='fixed_amount'){
						$total_fixed_discount += $voucher['discount'];
					}
				}
				$cart_summary['total_fixed_discount']= $total_fixed_discount;
				$cart_summary['discount'] = $cart_summary['discount'] + $total_fixed_discount;
				$cart_summary['total'] =  $cart_summary['total'] - $total_fixed_discount;
				if($cart_summary['total'] < 0){
					$cart_summary['total'] = 0;
				}
			}
		}
		return $cart_summary;
	}

	function subscription_session($familyID){
		$this->familyID = $familyID;
		$this->in_crm = TRUE;
	}

}
