<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('show_403')) {
	function show_403() {
		header('HTTP/1.0 403 Forbidden');
		require_once(APPPATH . 'views/errors/html/error_403.php');
		exit();
	}
}

if (!function_exists('display_messages')) {
	function display_messages($fa_weight = 'far') {
		$CI =& get_instance();

		// get vars from view
		$view_vars = $CI->load->get_vars();
		if (is_array($view_vars)) {
			foreach ($view_vars as $key => $value) {
				if (in_array($key, array('success', 'info', 'error', 'errors'))) {
					$$key = $value;
				}
			}
		}

		// check for button
		$button = NULL;
		$button_link = $CI->session->flashdata('alert_button_link');
		if (!empty($button_link)) {
			$button = '<div class="mt-2"><a href="' . $button_link . '" class="btn btn-white btn-sm confirm">Confirm Removal</a></div>';
		}

		// show messages
		if (isset($success) && !empty($success)) {
			?><div class="alert alert-custom alert-success" role="alert">
				<div class="alert-icon"><i class="<?php echo $fa_weight; ?> fa-check-circle "></i></div>
				<div class="alert-text"><?php echo $success; ?></div>
			</div><?php
		}
		if (isset($info) && !empty($info)) {
			?><div class="alert alert-custom alert-info" role="alert">
				<div class="alert-icon"><i class="<?php echo $fa_weight; ?> fa-info-circle"></i></div>
				<div class="alert-text"><?php echo $info; ?></div>
			</div><?php
		}
		if (isset($error) && !empty($error)) {
			?><div class="alert alert-custom alert-danger" role="alert">
				<div class="alert-icon"><i class="<?php echo $fa_weight; ?> fa-exclamation-circle"></i></div>
				<div class="alert-text"><?php echo $error . $button; ?></div>
			</div><?php
		} else if (isset($errors) && is_array($errors) && count($errors) > 0) {
			?><div class="alert alert-custom alert-danger" role="alert">
				<div class="alert-icon"><i class="<?php echo $fa_weight; ?> fa-exclamation-circle"></i></div>
				<div class="alert-text">
					<p><?php
						if (count($errors) == 1) {
							echo array_values($errors)[0];
						} else {
							?>Please correct the following errors:<?php
						}
					?></p><?php
					if (count($errors) > 1)  {
						?><ul><?php
							foreach ($errors as $error) {
								?><li><?php
								echo $error;
								?></li><?php
							}
						?></ul><?php
					}
					echo $button;
					?>
				</div>
			</div><?php
		}
	}
}

// get friendly DB error showing why an item can't be deleted
function get_friendly_db_error($table, $itemID, $details, $subject = NULL) {
	$CI =& get_instance();
	$error = $CI->db->error();
	$table_prefix = $CI->db->dbprefix;

	// get subject
	if (empty($subject)) {
		if (isset($details->name) && !empty($details->name)) {
			$subject = $details->name;
		} else {
			$subject = 'Item';
		}
	}

	// capitalise subject
	$subject = ucwords($subject);

	// get table dependencies as mysql error only returns first dependent table on error
	$CI->config->load('table_dependencies', TRUE);
	$tables = $CI->config->item('tables', 'table_dependencies');

	// get mysql error code
	if (is_array($error) && array_key_exists('code', $error) && array_key_exists('message', $error)) {
		switch ($error['code']) {
			case 1451:
				// if in tables array above, loop through dependencies
				if (array_key_exists($table, $tables) && array_key_exists('dependencies', $tables[$table])) {
					$conflicts = [];
					$key = $tables[$table]['key'];
					foreach ($tables[$table]['dependencies'] as $dependant_table => $dependant_details) {
						// check for conflict in child table
						$res = $CI->db->from($dependant_table)
							->where([
								$key => $itemID
							])
							->limit(1)
							->get();
						if ($res->num_rows() > 0) {
							// if dependency has a link set, replace variables
							if (array_key_exists('link', $dependant_details) && !empty($dependant_details['link'])) {
								$vars = [];
								if (array_key_exists('vars', $dependant_details) && !empty($dependant_details['vars'])) {
									foreach ($dependant_details['vars'] as $var) {
										if (isset($details->$var)) {
											$vars[] = $details->$var;
										} else {
											$vars[] = '';
										}
									}
								}
								$conflicts[$dependant_details['name']] = anchor(vsprintf($dependant_details['link'], $vars), $dependant_details['name'], ['target' => '_blank']);
							} else {
								$conflicts[$dependant_details['name']] = $dependant_details['name'];
							}
						}
					}
					// if some conflicts, show error
					if (count($conflicts) > 0) {
						// sort alphabetically
						ksort($conflicts);

						// check permission
						$removal_perms = (array)$CI->settings_library->get('removal_permissions');
						if (in_array($CI->auth->user->department, $removal_perms)) {
							// show force delete option if has permission
							if (stripos(current_url(), '/force') !== FALSE) {
								$CI->session->set_flashdata('alert_button_link', current_url());
							} else {
								$CI->session->set_flashdata('alert_button_link', current_url() . '/force/');
							}
						}

						// show error
						return $subject . ' could not removed due to there being existing ' . natural_language_join($conflicts);
					}
				}
				// if no conflicts from above, fall back to simple message with table name
				// get table name
				preg_match('/(?:a foreign key constraint fails \(`[A-Za-z0-9_\-]+`\.`)([A-Za-z0-9_\-]+)(?:`, CONSTRAINT)/', $error['message'], $matches);
				if (is_array($matches) && count($matches) > 0) {
					// table name is last in array
					$table_conflict = end($matches);
					if (!empty($table_conflict)) {
						// check if table name starts with db prefix
						if (stripos($table_conflict, $table_prefix) === 0) {
							// if so, remove prefix
							$table_conflict = substr($table_conflict, strlen($table_prefix));
						}

						// not interested in bookings_ prefix
						$table_conflict = str_replace('bookings_', '', $table_conflict);

						// rename lessons to session
						$table_conflict = str_replace('lessons', 'session', $table_conflict);

						// replace understores in table name with spaces
						$name = str_replace('_', ' ', $table_conflict);

						// return generic error with table name
						return $subject . ' could not removed due to there being existing ' . $name;
					}
				}
				break;
		}

		// error unknown, tell user
		return $subject . ' could not be removed due to an unknown error (#' . $error['code'] . ')';
	}

	// no error
	return FALSE;
}

// force delete db dependant tables
function force_delete_db_dependants($table, $itemID) {
	$CI =& get_instance();

	// check permissions
	$removal_perms = (array)$CI->settings_library->get('removal_permissions');
	if (!in_array($CI->auth->user->department, $removal_perms)) {
		return FALSE;
	}

	// get tables dependencies
	$CI->config->load('table_dependencies', TRUE);
	$tables = $CI->config->item('tables', 'table_dependencies');

	// if in tables array above, loop through dependencies
	if (array_key_exists($table, $tables) && array_key_exists('dependencies', $tables[$table])) {
		$conflicts = [];
		$key = $tables[$table]['key'];
		foreach ($tables[$table]['dependencies'] as $dependant_table => $dependant_details) {
			$cartIDs = [];
			// if certain tables
			switch ($dependant_table) {
				case 'bookings_blocks':
				case 'bookings_lessons':
					// delete their dependencies fist
					$dependant_key = 'blockID';
					if ($dependant_table === 'bookings_lessons') {
						$dependant_key = 'lessonID';
					}
					// loop through dependant table and delete their dependents
					$res = $CI->db->from($dependant_table)
					->where([
						$key => $itemID
					])
					->get();
					if ($res->num_rows() > 0) {
						foreach ($res->result() as $row) {
							force_delete_db_dependants($dependant_table, $row->$dependant_key);
						}
					}
					break;
				case 'timesheets_items':
				case 'timesheets_expenses':
					// set lessonID to NULL;
					$where = [
						'lessonID' => $itemID
					];
					$data = [
						'lessonID' => NULL
					];
					$CI->db->update($dependant_table, $data, $where);
					break;
				case 'bookings_cart_sessions':
					if ($table === 'bookings_lessons') {
						$dependant_key = 'lessonID';
						// loop through dependant table and get affected cart IDs for sessions we will delete
						$res = $CI->db->from($dependant_table)
							->where([
								$key => $itemID
							])
							->get();
						if ($res->num_rows() > 0) {
							foreach ($res->result() as $row) {
								$cartIDs[$row->cartID] = $row->cartID;
							}
						}
					}
					break;
			}
			// try and delete dependant table items
			$res = $CI->db->delete($dependant_table, [
				$key => $itemID
			]);
			// if certain tables
			switch ($dependant_table) {
				case 'bookings_cart_sessions':
					if ($table === 'bookings_lessons' && count($cartIDs) > 0) {
						// now that cart sessions were deleted above, loop carts
						foreach ($cartIDs as $cartID) {
							$where = [
								'cartID' => $cartID
							];
							// get cart info
							$res = $CI->db->from('bookings_cart')
								->where($where)
								->limit(1)
								->get();

							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$cart_info = $row;

									// check for other sessions in booking
									$res = $CI->db->from('bookings_cart_sessions')
										->where($where)
										->limit(1)
										->get();

									// if none, delete cart
									if ($res->num_rows() === 0) {
										$res = $CI->db->delete('bookings_cart', $where, 1);
									} else {
										// if some sessions left, recalc cart totals
										$CI->load->library('cart_library');
										$args = array(
											'contactID' => $cart_info->contactID,
											'accountID' => $cart_info->accountID,
											'cartID' => $cartID,
											'in_crm' => TRUE
										);
										$CI->cart_library->init($args);
										$CI->cart_library->validate_cart();
									}

									// recalc family balance
									$CI->crm_library->recalc_family_balance($cart_info->familyID);
								}
							}
						}
					}
					break;
			}
		}
	}
}

/**
 * Join a string with a natural language conjunction at the end.
 * https://gist.github.com/angry-dan/e01b8712d6538510dd9c
 */
function natural_language_join(array $list, $conjunction = 'and') {
	$last = array_pop($list);
	if ($list) {
		return implode(', ', $list) . ' ' . $conjunction . ' ' . $last;
	}
	return $last;
}

// order multidimensional array by multiple values
// usage: $slot_ranges = array_orderby($slot_ranges, 'start', SORT_ASC, 'end', SORT_ASC);
function array_orderby()
{
	$args = func_get_args();
	$data = array_shift($args);
	foreach ($args as $n => $field) {
		if (is_string($field)) {
			$tmp = array();
			foreach ($data as $key => $row)
				$tmp[$key] = $row[$field];
				$args[$n] = $tmp;
			}
	}
	$args[] = &$data;
	call_user_func_array('array_multisort', $args);
	return array_pop($args);
}

// order multidimensional array by multiple object keys
// usage: $slot_ranges = array_orderby_object_keys($slot_ranges, 'start', SORT_ASC, 'end', SORT_ASC);
function array_orderby_object_keys()
{
	$args = func_get_args();
	$data = array_shift($args);
	foreach ($args as $n => $field) {
		if (is_string($field)) {
			$tmp = array();
			foreach ($data as $key => $row)
				$tmp[$key] = $row->$field;
				$args[$n] = $tmp;
			}
	}
	$args[] = &$data;
	call_user_func_array('array_multisort', $args);
	return array_pop($args);
}

if ( ! function_exists('get_random_password'))
{
	/**
	 * Generate a random password.
	 *
	 * get_random_password() will return a random password with length 6-8 of lowercase letters only.
	 *
	 * @access    public
	 * @param    $chars_min the minimum length of password (optional, default 8)
	 * @param    $chars_max the maximum length of password (optional, default 8)
	 * @param    $use_upper_case boolean use upper case for letters, means stronger password (optional, default true)
	 * @param    $include_numbers boolean include numbers, means stronger password (optional, default true)
	 * @param    $include_special_chars include special characters, means stronger password (optional, default false)
	 *
	 * @return    string containing a random password
	 */
	function get_random_password($chars_min=8, $chars_max=8, $use_upper_case=true, $include_numbers=true, $include_special_chars=false)
	{
		$length = rand($chars_min, $chars_max);
		$selection = 'aeuoyibcdfghjklmnpqrstvwxz';
		if($include_numbers) {
			$selection .= "1234567890";
		}
		if($include_special_chars) {
			$selection .= "!@\"#$%&[]{}?|";
		}

		$password = "";
		for($i=0; $i<$length; $i++) {
			$current_letter = $use_upper_case ? (rand(0,1) ? strtoupper($selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))];
			$password .=  $current_letter;
		}

	  return $password;
	}

}

// get real ip address from AWS
function get_ip_address() {
	// look for IP from load balancer
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		if (stripos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') === FALSE) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		// more than 1 ip listed, get first
		$ips = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
		return trim($ips[0]);
	}
	// if not, return normal IP
	return $_SERVER['REMOTE_ADDR'];
}

// get editable fields
function get_fields($type) {
	$CI =& get_instance();
	$fields = array();
	$where = array(
		'settings_fields.section' => $type
	);
	$res = $CI->db->select('settings_fields.*, accounts_fields.show as account_show, accounts_fields.required as account_required')
		->from('settings_fields')
		->join('accounts_fields', 'settings_fields.section = accounts_fields.section and settings_fields.field = accounts_fields.field and accounts_fields.accountID = ' . (isset($CI->auth->user->accountID) ? $CI->auth->user->accountID : $CI->online_booking->accountID), 'left')
		->where($where)
		->order_by('settings_fields.section asc, settings_fields.order asc, settings_fields.field asc')->get();
	if ($res->num_rows()) {
		foreach($res->result() as $field) {
			$fields[$field->field] = array(
				'label' => $field->label,
				'show' => $field->show,
				'required' => $field->required
			);
			if ($field->account_show !== NULL) {
				$fields[$field->field]['show'] = $field->account_show;
			}
			if ($field->account_required !== NULL) {
				$fields[$field->field]['required'] = $field->account_required;
			}
		}
	}
	return $fields;
}

function check_tab_availability($section) {
	$CI =& get_instance();

	$all_fields = $CI->db->from('settings_fields')
		->where(['section' => $section])
		->get();

	$hidden_fields = $CI->db->from('accounts_fields')
		->where([
			'accountID' => $CI->auth->user->accountID,
			'section' => $section,
			'show' => 0
		])
		->get();

	if ($all_fields->num_rows() == $hidden_fields->num_rows()) {
		return false;
	}
	return true;
}

function check_tab_availability_on_dashboard($section) {
	$CI =& get_instance();




	switch ($section) {
		case 'staff_recruitment':
			$fields = ['passport', 'ni_card', 'drivers_licence', 'birth_certificate', 'utility_bill', 'other',
			'proof_of_qualifications', 'valid_working_permit', 'id_card',
			'pay_dates', 'timesheet', 'policy_agreement',
			'travel_expenses', 'equal_opportunities', 'employment_contract',
			'p45', 'dbs', 'policies',
			'details_updated', 'tshirt'];
			break;
		default:
			break;

	}

	$all_fields = $CI->db->from('settings_fields')
		->where(['section' => $section])
		->where_in('field', $fields)->get();

	$hidden_fields = $CI->db->from('accounts_fields')
		->where([
			'accountID' => $CI->auth->user->accountID,
			'section' => $section,
			'show' => 0
		])
		->where_in('field', $fields)->get();

	if ($all_fields->num_rows() == $hidden_fields->num_rows()) {
		return false;
	}
	return true;
}

// show field labels when field is editable in settings
function field_label($field, $fields, $raw = FALSE) {
	if (!isset($fields[$field])) {
		return NULL;
	}
	$label = $fields[$field]['label'];
	if (in_array($field, ['county', 'eCounty'])) {
		$label = localise('county');
	}
	if ($raw === TRUE) {
		return $label;
	}
	$label .= required_field($field, $fields, 'label');
	return form_label($label, $field);
}

// check if field is required if is editable in settings
function required_field($field, $fields, $type = NULL) {
	//Check for complementary _specify field (religion_specify, gender_specify, etc) and instead confirm
	//if parent field is required (such as gender field) instead.
	if (preg_match("~\_specify$~",$field)) {
		return required_field(str_replace("_specify","",$field), $fields, $type);
	}
	if (!isset($fields[$field]['required'])) {
		return NULL;
	}
	// if not set to shown, can't be required
	if (!show_field($field, $fields)) {
		return NULL;
	}
	if ($fields[$field]['required'] == 1) {
		switch ($type) {
			case 'validation':
				return '|required';
				break;
			case 'label':
				return ' <em>*</em>';
				break;
			default:
				return TRUE;
				break;
		}
	}
	return NULL;
}

// check if field can be shown if is editable in settings
function show_field($field, $fields) {
	if (isset($fields[$field]['show']) && $fields[$field]['show'] == 1) {
		return TRUE;
	}
	return FALSE;
}

// function to generate breadcrumb from array
function breadcrumb($levels) {
	$CI =& get_instance();
	if ($CI->uri->total_segments() == 0) {
		return FALSE;
	}
	$output = '<ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">';
	if (is_array($levels) && count($levels) > 0) {
		foreach ($levels as $link => $label) {
			$output .= '<li class="breadcrumb-item"><a href="' . site_url($link) . '" class="text-muted">' . $label . '</a></li> ';
		}
	}
	$output .= '</ul>';
	return $output;
}

// convert colour to hex
function colour_to_hex($colour) {
	$colours = array(
		'blue' => '#1b75bc',
		'orange' => '#f7931e',
		'red' => '#db4228',
		'green' => '#39b54a',
		'purple' => '#9564E2',
		'pink' => '#FF33CC',
		'light-blue' => '#00acec',
		'dark-grey' => '#4d4d4f'
	);
	if (array_key_exists($colour, $colours)) {
		return $colours[$colour];
	}
	return false;
}

// get contrast colour - black or white
function get_contrast_colour($hexColor) {

		// hexColor RGB
		$R1 = hexdec(substr($hexColor, 1, 2));
		$G1 = hexdec(substr($hexColor, 3, 2));
		$B1 = hexdec(substr($hexColor, 5, 2));

		// Black RGB
		$blackColor = "#000000";
		$R2BlackColor = hexdec(substr($blackColor, 1, 2));
		$G2BlackColor = hexdec(substr($blackColor, 3, 2));
		$B2BlackColor = hexdec(substr($blackColor, 5, 2));

		 // Calc contrast ratio
		 $L1 = 0.2126 * pow($R1 / 255, 2.2) +
			   0.7152 * pow($G1 / 255, 2.2) +
			   0.0722 * pow($B1 / 255, 2.2);

		$L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
			  0.7152 * pow($G2BlackColor / 255, 2.2) +
			  0.0722 * pow($B2BlackColor / 255, 2.2);

		$contrastRatio = 0;
		if ($L1 > $L2) {
			$contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
		} else {
			$contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
		}

		// If contrast is more than level, return black color
		if ($contrastRatio > 8) {
			return 'black';
		} else { // if not, return white color.
			return 'white';
		}
}

// get brand colour
function brand_colour($colour) {
	if (substr($colour, 0, 1) !== '#') {
		$colour = colour_to_hex($colour);
	}
	return $colour;
}

// output css for label style
function label_style($colour) {
	$colour = brand_colour($colour);
	if (empty($colour)) {
		return FALSE;
	}
	$css = array(
		'background-color: ' . $colour,
		'color: ' . get_contrast_colour($colour)
	);
	return implode(";", $css);
}

// hex to rgb
function hex_to_rgb($hex) {
	return sscanf($hex, "#%02x%02x%02x");
}

// output css for row style
function row_style($colour) {
	$colour = brand_colour($colour);
	if (empty($colour)) {
		return FALSE;
	}
	$rgb = hex_to_rgb($colour);
	$css = array(
		'background-color: rgba(' . implode(',', $rgb) . ', .1)'
	);
	return implode(";", $css);
}

// output css for link style
function link_style($colour) {
	$colour = brand_colour($colour);
	if (empty($colour)) {
		return FALSE;
	}
	$css = array(
		'color: ' . get_contrast_colour($colour)
	);
	return implode(";", $css);
}

// geocode
function geocode($place, $accountID = NULL) {
	$CI =& get_instance();

	// load config
	$CI->config->load('google', TRUE);

	// build request
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($place . ', ' . localise('geocode_country', $accountID)) . '&key=' . $CI->config->item('maps_backend_api_key', 'google');

	// fetch response
	$client = new \GuzzleHttp\Client();
	$geocodeResponse = $client->get($url)->getBody();
	$geocode_data = json_decode($geocodeResponse);

	// if some data, return
	if (!empty($geocode_data) && $geocode_data->status != 'ZERO_RESULTS' && isset($geocode_data->results) && isset($geocode_data->results[0])) {
		return array(
			'lat' => $geocode_data->results[0]->geometry->location->lat,
			'lng' => $geocode_data->results[0]->geometry->location->lng
		);
	}

	// no data
	return FALSE;
}

function geocode_mileage($start, $end, $accountID = NULL) {
	$CI =& get_instance();

	// load config
	$CI->config->load('google', TRUE);

	// build request
	$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . urlencode($start . ', ' . localise('geocode_country', $accountID)) . '&destinations=' . urlencode($end . ', UK') . '&mode=driving&sensor=false&key=' . $CI->config->item('maps_backend_api_key', 'google');

	// fetch response
	$client = new \GuzzleHttp\Client();
	$geocodeResponse = $client->get($url)->getBody();
	$geocode_data = json_decode($geocodeResponse);
	return $geocode_data;
}

// geocode address
function geocode_address($address, $city, $postcode, $accountID = NULL) {
	return geocode($address . ', ' . $city . ', ' . $postcode, $accountID);
}

// most popular value in array
function array_most_common_value($array) {
	$values = array_count_values($array);
	arsort($values);
	return array_slice(array_keys($values), 0, 5, true)[0];
}

//generate dates for lessons
function generateDatesForSearch($lesson, $date_from_search, $date_to_search) {
	$date_from = strtotime($date_from_search);
	if (strtotime($lesson->startDate) > strtotime($date_from_search)) {
		$date_from = strtotime($lesson->startDate);
	}

	$date_to = strtotime($date_to_search);
	if (strtotime($lesson->endDate) < $date_to) {
		$date_to = strtotime($lesson->endDate);
	}

	$dates_between = [];
	while (true) {
		$dates_between[strtolower(date('l', $date_from))][] = date('Y-m-d', $date_from);

		if ($date_from >= $date_to)
			break;

		$date_from += 86400;
	}

	return $dates_between;
}

function createXlsDocument($table, $fileName, $lastHeaderIndex = 1) {
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
	$spreadsheet = $reader->loadFromString($table);

	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');

	// style for header
	$styleArray = [
		'font' => [
			'bold' => true,
		]
	];

	$worksheet = $spreadsheet->getActiveSheet();

	$highestColumn = $worksheet->getHighestColumn();

	$worksheet->getStyle('A1:' . $highestColumn . $lastHeaderIndex)
		->applyFromArray($styleArray);


	// style for borders
	$styleArray = array(
		'borders' => array(
			'outline' => array(
				'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
			),
		),
	);
	foreach ($worksheet->getRowIterator() as $row) {
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
		foreach ($cellIterator as $cell) {
			$worksheet->getStyle($cell->getCoordinate())
				->applyFromArray($styleArray);
		}
	}


	header('Content-Type: application/vnd.ms-excel"');
	header('Content-Disposition: attachment; filename=' . $fileName . '.xls');
	$writer->save('php://output');
	exit();
}

// return null if string empty for db inserts
function null_if_empty($value) {
	if (empty($value)) {
		return NULL;
	} return $value;
}

// resolve online booking domain
function resolve_online_booking_domain() {
	// can't load CI instance yet as using in routes.php, so load DB class directly
	require_once(BASEPATH .'database/DB.php');
	$db =& DB();

	// check for online booking custom domain
	if (!empty(CUSTOM_DOMAIN)) {
		$where = array(
			'accounts.booking_customdomain' => CUSTOM_DOMAIN,
			'accounts.active' => 1
		);
		$or_where = '(' .  $db->dbprefix('accounts') . '.addon_online_booking = 1 OR ' .  $db->dbprefix('accounts_plans') . '.addons_all = 1)';
		$res = $db->select('accounts.*, accounts_plans.addons_all')->from('accounts')->join('accounts_plans', 'accounts.planID = accounts_plans.planID', 'left')->where($where)->where($or_where, NULL, FALSE)->limit(1)->get();

		// not found
		if ($res->num_rows() == 1) {
			foreach ($res->result() as $row) {
				return $row->accountID;
			}
		}
	}
	// check for online booking sub domain
	if (!empty(SUB_DOMAIN)) {
		$where = array(
			'accounts.booking_subdomain' => SUB_DOMAIN,
			'accounts.active' => 1
		);
		$or_where = '(' .  $db->dbprefix('accounts') . '.addon_online_booking = 1 OR ' .  $db->dbprefix('accounts_plans') . '.addons_all = 1)';
		$res = $db->select('accounts.*, accounts_plans.addons_all')->from('accounts')->join('accounts_plans', 'accounts.planID = accounts_plans.planID', 'left')->where($where)->where($or_where, NULL, FALSE)->limit(1)->get();

		// not found
		if ($res->num_rows() == 1) {
			foreach ($res->result() as $row) {
				return $row->accountID;
			}
		}
	}
	return FALSE;
}

// resolve custom domain
function resolve_custom_domain() {
	$CI =& get_instance();

	// check for custom domain
	if (!empty(CUSTOM_DOMAIN)) {
		$where = array(
			'accounts.crm_customdomain' => CUSTOM_DOMAIN,
			'accounts.active' => 1
		);
		$res = $CI->db->from('accounts')
			->where($where)
			->limit(1)
			->get();

		// not found
		if ($res->num_rows() == 1) {
			foreach ($res->result() as $row) {
				return $row->accountID;
			}
		}
	}
	return FALSE;
}

// get localisation
function localise($item, $accountID = NULL) {
	$CI =& get_instance();

	// based on ISO 3166 country code, except Europe (eu)
	$localisations = [
		'GB' => [
			'currency_code' => 'GBP',
			'currency_symbol' => '£',
			'currency_symbol_small' => 'Pence',
			'county' => 'County',
			'postcode_regex' => '/^[a-z](\d[a-z\d]?|[a-z]\d[a-z\d]?) \d[a-z]{2}$/i', // AN NAA | ANN NAA | AAN NAA | AANN NAA | ANA NAA | AANA NAA
			'geocode_country' => 'United Kingdom',
			'country_code' => '44',
			'mobile_regex' =>  '/^07([\d]{3})[(\D\s)]?[\d]{3}[(\D\s)]?[\d]{3}$/'
		],
		'EU' => [
			'currency_code' => 'EUR',
			'currency_symbol' => '€',
			'currency_symbol_small' => 'Cents',
			'county' => 'State',
			'postcode_regex' => '/^[a-z0-9 ]+$/i',
			'geocode_country' => 'Europe',
			'country_code' => '',
			'mobile_regex' => '/^[0-9]+$/'
		],
		'AU' => [
			'currency_code' => 'AUD',
			'currency_symbol' => '$',
			'currency_symbol_small' => 'Cents',
			'county' => 'State',
			'postcode_regex' => '/^[0-9]{4}$/',
			'geocode_country' => 'Australia',
			'country_code' => '61',
			'mobile_regex' => '/^[0-9]{10}$/'
		],
		'US' => [
			'currency_code' => 'USD',
			'currency_symbol' => '$',
			'currency_symbol_small' => 'Cents',
			'county' => 'State',
			'postcode_regex' => '/^[a-z0-9 ]+$/i',
			'geocode_country' => 'USA',
			'country_code' => '', // don't set this as US phones don't start with 0
			'mobile_regex' => '/^[0-9]+$/'
		]
	];

	$localisation = $CI->settings_library->get('localisation', $accountID);

	if (!array_key_exists($localisation, $localisations)) {
		$localisation = 'gb';
	}

	if (!array_key_exists($item, $localisations[$localisation])) {
		throw new Error('Localisation item not found');
	}

	return $localisations[$localisation][$item];
}

// get currency code
function currency_code($accountID = NULL) {
	return localise('currency_code', $accountID);
}

// get currency symbol
function currency_symbol($accountID = NULL) {
	return localise('currency_symbol', $accountID);
}

// get currency small symbol
function currency_small_symbol($accountID = NULL) {
	return localise('currency_symbol_small', $accountID);
}

/* End of file crm_helper.php */
/* Location: ./application/helpers/crm_helper.php */
