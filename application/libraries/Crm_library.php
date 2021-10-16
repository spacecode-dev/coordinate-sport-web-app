<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Asika\Autolink\Linker;

class Crm_library {

	const MAX_SIZE_UPLOAD = '6144';

	private $CI;
	private $asset_versions = array();

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
	}

	public function check_postcode($postcode, $accountID = null) {

		// clean up the user input
		$postcode = strtoupper($postcode); // uppercase
		$postcode = preg_replace('/[^\da-z ]/i', '', $postcode); // letters/numbers/spaces only
		$postcode = trim($postcode); // trim any whitespace

		// check format
		if (preg_match(localise('postcode_regex', $accountID), $postcode)) {
			return $postcode;
		} else {
			return FALSE;
		}

	}

	public function check_dob($date) {

		// valid if empty
		if (empty($date)) {
			return TRUE;
		}

		// check valid date
		if (!check_uk_date($date)) {
			return FALSE;
		}

		// check date is in future
		if (strtotime(uk_to_mysql_date($date)) > time()) {
			return FALSE;
		}

		return TRUE;
	}

	public function check_mobile($number = NULL, $accountID = NULL) {

		// if none, ok
		if (empty($number)) {
			return TRUE;
		}

		// strip all non-numeric characters
		$number = preg_replace('/[^0-9]/', '', $number);

		// if number begins with country code, replace with 0 for these tests
		$country_code = localise('country_code', $accountID);
		if (!empty($country_code) && substr($number, 0, strlen($country_code)) == $country_code) {
			$number = 0 . substr($number, strlen($country_code));
		}

		if (preg_match(localise('mobile_regex', $accountID), $number)) {
			return $number;
		}

		return FALSE;
	}

	public function phone_or_mobile($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// if both empty, not valid
		if (empty($value) && empty($value2)) {
			return FALSE;
		}

		return TRUE;
	}

	public function last_segment() {
		$segments = $this->CI->uri->segment_array();

		if (count($segments) > 0) {
			return end($segments);
		}

		return FALSE;
	}

	public function get_days_between_dates($start, $end){

		$start = strtotime($start);
		$end = strtotime($end);

		$return['monday'] = 0;
		$return['tuesday'] = 0;
		$return['wednesday'] = 0;
		$return['thursday'] = 0;
		$return['friday'] = 0;
		$return['saturday'] = 0;
		$return['sunday'] = 0;

		$current = $start;

		while($current <= $end) {
			$day = gmdate('l', $current);
			switch($day) {
				case "Monday":
					$return['monday']++;
					break;
				case "Tuesday":
					$return['tuesday']++;
					break;
				case "Wednesday":
					$return['wednesday']++;
					break;
				case "Thursday":
					$return['thursday']++;
					break;
				case "Friday":
					$return['friday']++;
					break;
				case "Saturday":
					$return['saturday']++;
					break;
				case "Sunday":
					$return['sunday']++;
					break;
			}

			// add day to current
			$current += 3600*24;
		}

		return $return;
	}

	public function get_age($dob) {
		if (empty($dob)) {
			return FALSE;
		}
		list($y,$m,$d) = explode("-", $dob);
		$age = date('Y') - $y - (date('n') < (ltrim($m,'0') + (date('j') < ltrim($d,'0'))));
		if ($age > 0) {
			return $age;
		} else {
			return FALSE;
		}
	}

	/**
	 * handle upload
	 * @return mixed
	 */
	public function handle_upload($field_name = 'file', $image_only = FALSE, $shared_path = FALSE, $accountID = FALSE, $max_width = 0, $max_height = 0, $thumb_width = 0, $thumb_height = 0, $thumb_crop = FALSE, $staff_flag = FALSE) {

		// check if uploading
		if (!isset($_FILES[$field_name]['tmp_name']) || empty($_FILES[$field_name]['tmp_name'])) {
			return NULL;
		}

		// Check Image size for staff only
		if($staff_flag == TRUE){
			$image_path = $_FILES[$field_name]['tmp_name'];
			$size = getimagesize($image_path);

			$h = $size[1];
			$w = $size[0];
			if($h < 200 || $w < 200){
				return 1;
			}
		}

		// if image, check if needs resizing
		if ($image_only == TRUE) {
			// get tmp path
			$image_path = $_FILES[$field_name]['tmp_name'];

			// get image size
			$size = getimagesize($image_path);

			if ($size != FALSE) {
				$size['height'] = $size[1];
				$size['width'] = $size[0];

				// check if needs to be resized
				if (($max_width > 0 && $size['width'] > $max_width) || ($max_height > 0 && $size['height'] > $max_height)) {
					if (empty($max_width)) {
						$max_width = 9999;
					}
					if (empty($max_height)) {
						$max_height = 9999;
					}
					//resize
					$this->CI->load->library('Imageutils');
					$this->CI->imageutils->resize($image_path, $max_width, $max_height);
				}
			}
		}

		// handle image
		$config = array();
		$config['upload_path'] = UPLOADPATH;
		$config['file_name'] = random_string('alnum', 32);
		$config['allowed_types'] = 'doc|docx|xls|xlsx|gif|jpg|jpeg|png|pdf|zip|csv';
		$config['max_size'] = self::MAX_SIZE_UPLOAD;

		// if shared file, upload to shared location
		if ($shared_path === TRUE) {
			$config['upload_path'] = UPLOADPATH_SHARED;
		} else if (!empty($accountID)) {
			$config['upload_path'] = UPLOADPATH_SHARED . $accountID . '/';
		}

		// check if directory exists, if not, create
		if (!is_dir($config['upload_path']))  {
			mkdir($config['upload_path'], 0777, TRUE);
		}

		$this->CI->load->library('upload', $config);

		// reload config in the case of uploading multiple files to ensure unique file names
		$this->CI->upload->initialize($config);

		// attempt upload
		if ($this->CI->upload->do_upload($field_name))	{

			// upload ok
			$upload_data = $this->CI->upload->data();

			// check if image if required
			if ($image_only === TRUE && $upload_data['is_image'] != 1) {
				return NULL;
			}


			if ($upload_data !== NULL) {
				$path = $config['upload_path'] . $upload_data['raw_name'];

				// check if generating thumb
				if ($thumb_width > 0 && $thumb_height > 0) {
					//resize
					$thumb_path_tmp = tempnam(sys_get_temp_dir(), 'coord');
					copy($upload_data['full_path'], $thumb_path_tmp);
					$this->CI->load->library('Imageutils');
					if ($thumb_crop === TRUE) {
						$this->CI->imageutils->resize_and_crop($thumb_path_tmp, $thumb_width, $thumb_height);
					} else {
						$this->CI->imageutils->resize($thumb_path_tmp, $thumb_width, $thumb_height);
					}
					copy($thumb_path_tmp, $path . '_thumb'); // copy first as rename doesn't support cross wrapper renames
					unlink($thumb_path_tmp);
				}

				// remove extension
				rename($upload_data['full_path'], $path);
			}

			return $upload_data;

		}

		return NULL;
	}

	public function handle_image_upload($field_name = 'image', $shared_path = FALSE, $accountID = FALSE, $max_width = 0, $max_height = 0, $thumb_width = 0, $thumb_height = 0, $thumb_crop = FALSE, $staff_flag = FALSE) {
		return $this->handle_upload($field_name, TRUE, $shared_path, $accountID, $max_width, $max_height, $thumb_width, $thumb_height, $thumb_crop, $staff_flag);
	}

	private function multipleFileSizeValidate($files) {
		$overallSize = 0;
		foreach ($files['size'] as $size) {
			$overallSize += $size;
		}

		if ($overallSize > self::MAX_SIZE_UPLOAD * 1000) {
			$this->CI->upload->set_error('upload_invalid_filesize');
			return false;
		}

		return true;
	}

	public function handle_multi_upload($field_name = 'files', $shared_path = FALSE, $accountID = FALSE, $max_width = 0, $max_height = 0, $upload_only_without_errors = false) {
		// retrieve the number of images uploaded
		$number_of_files = count($_FILES[$field_name]['tmp_name']);

		// considering that do_upload() accepts single files, we will have to do a small hack so that we can upload multiple files. For this we will have to keep the data of uploaded files in a variable, and redo the $_FILE.
		$files = $_FILES[$field_name];
		$errors = array();

		// first make sure that there is no error in uploading the files
		if (!$upload_only_without_errors) {
			for ($i=0;$i<$number_of_files;$i++) {
				if ($_FILES[$field_name]['error'][$i] != 0) {
					$errors[] = 'Couldn\'t upload file '.$_FILES[$field_name]['name'][$i];
				}
			}
		}

		// if no errors, upload
		if (count($errors) == 0) {

			if (!$this->multipleFileSizeValidate($files)) {
				return false;
			}

			$upload_data = array();

			// now, taking into account that there can be more than one file, for each file we will have to do the upload
			for ($i = 0; $i < $number_of_files; $i++) {
				if ($files['error'][$i] != 0) {
					continue;
				}
				$_FILES['file']['name'] = $files['name'][$i];
				$_FILES['file']['type'] = $files['type'][$i];
				$_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
				$_FILES['file']['error'] = $files['error'][$i];
				$_FILES['file']['size'] = $files['size'][$i];
				$upload_res = $this->handle_upload('file', $shared_path, $accountID, $max_width, $max_height);
				if ($upload_res !== NULL) {
					$upload_data[] = $upload_res;
				}
			}
			return $upload_data;
		}

		return FALSE;
	}

	public function handle_multi_upload_custom_names($field_name = 'files', $shared_path = FALSE, $accountID = FALSE, $max_width = 0, $max_height = 0)
	{

		$files = $_FILES[$field_name];

		$upload_data = [];
		foreach ($files['size'] as $name => $size) {
			if ($size == 0) {
				continue;
			}

			$_FILES['file']['name'] = $files['name'][$name];
			$_FILES['file']['type'] = $files['type'][$name];
			$_FILES['file']['tmp_name'] = $files['tmp_name'][$name];
			$_FILES['file']['error'] = $files['error'][$name];
			$_FILES['file']['size'] = $files['size'][$name];

			$upload_res = $this->handle_upload('file', $shared_path, $accountID, $max_width, $max_height);
			if ($upload_res !== NULL) {
				$upload_res['belongs_to'] = $name;
				$upload_data[] = $upload_res;
			}
		}

		return $upload_data;
	}

	/**
	 * duplicate upload
	 * @return mixed
	 */
	public function duplicate_upload($path = NULL, $destination = NULL, $fileNameToCopy = NULL, $withExtension = false) {

		// check params
		if (empty($path)) {
			return FALSE;
		}

		$upload_dir = UPLOADPATH;

		if (!file_exists($upload_dir . $path)) {
			return FALSE;
		}

		// generate new path
		$new_path = random_string('alnum', 32);

		if ($withExtension) {
			$new_path .= $this->CI->upload->get_extension($path);
		}

		if (!empty($fileNameToCopy)) {
			$new_path = $fileNameToCopy;
		}

		$destinationUpload = $upload_dir . $new_path;
		if (!empty($destination)) {
			$destinationUpload = $upload_dir . $destination . $new_path;
		}

		if (!copy($upload_dir . $path, $destinationUpload)) {
			return FALSE;
		}

		// copy thumb if exists
		if (file_exists($upload_dir . $path . '_thumb')) {
			copy($upload_dir . $path . '_thumb', $upload_dir . $new_path . '_thumb');
		}

		return $new_path;
	}

	/**
	 * handle safety upload
	 * @return mixed
	 */
	public function handle_safety_upload($org_id = NULL, $field_name = 'file') {

		// check params
		if (empty($org_id)) {
			return FALSE;
		}

		$random_path = random_string('alnum', 32);

		// handle image
		$config['upload_path'] = 'orgs/' . $org_id . '/safety/';
		$config['file_name'] = $random_path;
		$config['allowed_types'] = 'gif|jpg|jpeg|png';
		$config['max_size'] = self::MAX_SIZE_UPLOAD;

		$config['upload_path'] = UPLOADPATH . $config['upload_path'];

		// check if directory exists, if not, create
		if (!is_dir($config['upload_path']))  {
			mkdir($config['upload_path'], 0777, TRUE);
		}

		$this->CI->load->library('upload', $config);

		// attempt upload
		if ($this->CI->upload->do_upload($field_name))	{

			// upload ok
			$upload_data = $this->CI->upload->data();

			// load lib
			$this->CI->load->library('Imageutils');

			// resize big
			$this->CI->imageutils->resize($upload_data['full_path'], 1024, 768);

			$thumb_path = $upload_data['file_path'] . 'thumb.' . $upload_data['file_name'];

			// copy
			copy($upload_data['full_path'], $thumb_path);

			// resize
			$this->CI->imageutils->resize($thumb_path, 180, 120);

			return $upload_data;

		}

		return NULL;
	}

	public function sms_send($phone, $body, $from_name = null, $accountID = NULL) {
		$this->CI->config->load('textlocal', TRUE);

		// set default from name, if empty
		if (empty($from_name)) {
			$from_name = $this->CI->config->item('from', 'textlocal');
		}

		// from name can only be 11 characters
		$from_name = substr($from_name, 0, 11);

		// normalise phone
		$phone = $this->normalise_mobile($phone);

		// load helper
		$this->CI->load->helper('xml_helper');

		// build xml
		$xml = new SimpleXMLExtended("<SMS></SMS>");

		// Account node
		$xmlAccount = $xml->addChild('Account');
		$xmlAccount->addAttribute('apikey', $this->CI->config->item('apikey', 'textlocal'));

		// if not production, send in test mode
		if (substr(ENVIRONMENT, 0, 10) != 'production') {
			$testValue = 1;
		} else {
			$testValue = 0;
		}
		$xmlAccount->addAttribute('Test', $testValue);

		$xmlAccount->addAttribute('Info', 1);
		$xmlAccount->addAttribute('JSON', 0);

		// Sender node
		$xmlSender = $xmlAccount->addChild('Sender');
		$xmlSender->addAttribute('From', $from_name);
		$xmlSender->addAttribute('rcpurl', $this->CI->config->item('report_url', 'textlocal'));

		// Messages node
		$xmlMessages = $xmlSender->addChild('Messages');

		// keep sent list
		$sentTo = array();

		if ($this->check_mobile($phone, $accountID)) {
			$xmlMessage = $xmlMessages->addChild('Msg');
			$xmlMessage->addAttribute('Number', $phone);
			$xmlMessageText = $xmlMessage->addChild('Text')->addCData(str_replace(" & ", " and ", $body));
		} else {
			return FALSE;
		}

		$post = 'data='. urlencode($xml->asXML());
		$url = "https://www.txtlocal.com/xmlapi.php";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return_data = curl_exec($ch);
		curl_close($ch);

		if (strpos(strtolower($return_data), "error") === 0) {
			return FALSE;
		}

		return TRUE;
	}

	public function send_checkin_staff_alerts() {
		$lessons = $this->get_today_potential_lessons(null, false,false, true);

		// if none, return false
		if (count($lessons) == 0) {
			return FALSE;
		}

		$current_date = date('Y-m-d');

		ksort($lessons);

		foreach ($lessons as $array_lessons) {
			foreach ($array_lessons as $lesson) {

				if ($lesson->provisional == 1) {
					continue;
				}

				if ($lesson->checkin_email_sent == 1) {
					continue;
				}

				$query = $this->CI->db->select()
					->from('staff')
					->where([
						'staffID' => $lesson->staffID
					])
					->limit(1)
					->get();

				if ($query->num_rows() < 1) {
					continue;
				}
				$staff_info = [];
				foreach ($query->result() as $row) {
					$staff_info = $row;
				}

				//check if selected account has checkin feature
				$query = $this->CI->db->select()
					->from('accounts')
					->where([
						'accountID' => $staff_info->accountID
					])->limit(1)->get();

				$account_info = [];
				foreach ($query->result() as $row) {
					$account_info = $row;
				}


				if ($account_info->addon_lesson_checkins != 1) {
					continue;
				}

				$company_name = $this->CI->settings_library->get('email_from_name', $staff_info->accountID);

				// smart tags
				$smart_tags = array(
					'company' => $company_name,
				);

				$threshold_time = $this->CI->settings_library->get('email_not_checkin_staff_threshold_time', $staff_info->accountID);

				$checkin = $this->CI->db->select()
					->from('bookings_lessons_checkins')
					->where([
						'staffID' => $lesson->staffID,
						'lessonID' => $lesson->lessonID,
						'date' => $current_date
					])->limit(1)->get();

				//staff has already checked in
				if ($checkin->num_rows() > 0) {
					continue;
				}

				$start_time = $lesson->startTime;
				if (!empty($lesson->staff_start_time)) {
					$start_time = $lesson->staff_start_time;
				}

				$start_time = strtotime($current_date . ' ' . $start_time);

				$minites_from_start = intval((time() - $start_time) / 60);

				if ($minites_from_start >= $threshold_time) {
					$staff_addresses = [];
					$query = $this->CI->db->select()
						->from('staff_addresses')
						->where([
							'staffID' => $staff_info->staffID,
							'type' => 'main'
						])
						->get();

					if ($query->num_rows() > 0) {
						foreach ($query->result() as $row) {
							$staff_addresses = $row;
						}
					}

					$address = geocode_address($lesson->address1 . ' ' . $lesson->county, $lesson->town, $lesson->postcode);
					if (isset($address['lat'])) {
						$data = array(
							'accountID' => $lesson->accountID,
							'staffID' => $lesson->staffID,
							'lessonID' => $lesson->lessonID,
							'date' => date('Y-m-d'),
							'lat' => $address['lat'],
							'lng' => $address['lng'],
							'accuracy' => 0,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'not_checked_in' => 1
						);
						$this->CI->db->insert('bookings_lessons_checkins', $data);
					}

					// get email template
					$to = $staff_info->email;
					$subject = $this->CI->settings_library->get('email_not_checkin_staff_subject', $staff_info->accountID);
					$email_html = $this->CI->settings_library->get('email_not_checkin_staff_body', $staff_info->accountID);

					if (empty($to)) {
						continue;
					}

					// replace smart tags in email
					foreach ($smart_tags as $key => $value) {
						$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
						$email_html = str_replace('{' . $key . '}', $value, $email_html);
					}


					// get html email and convert to plain text
					$this->CI->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					if ($this->CI->settings_library->get('send_not_checkin_staff_email', $staff_info->accountID) == 1) {
						$this->send_email($to, $subject, $email_html, array(), TRUE, $staff_info->accountID);
					}
					if ($this->CI->settings_library->get('send_not_checkin_staff_sms', $staff_info->accountID) == 1) {
						$this->sms_send($staff_addresses->mobile_work, $email_plain, $company_name, $staff_info->accountID);
					}


					if ($this->CI->settings_library->get('send_not_checkin_staff_email', $staff_info->accountID) == 1 || $this->CI->settings_library->get('send_not_checkin_staff_sms', $staff_info->accountID) == 1) {
						//send emails to admins

						$to = [];

						if (!empty($this->CI->settings_library->get('email', $staff_info->accountID))) {
							$to[] = $this->CI->settings_library->get('email', $staff_info->accountID);
						}

						if (!empty($this->CI->settings_library->get('email_from', $staff_info->accountID))) {
							$to[] = $this->CI->settings_library->get('email_from', $staff_info->accountID);
						}

						if (empty($to)) {
							continue;
						}

						// smart tags
						$smart_tags = array(
							'staff_name' => $staff_info->first . ' ' . $staff_info->surname,
							'customer_name' => $lesson->org
						);

						$subject = $this->CI->settings_library->get('email_not_checkin_account_subject', $staff_info->accountID);
						$email_html = $this->CI->settings_library->get('email_not_checkin_account_body', $staff_info->accountID);

						// replace smart tags in email
						foreach ($smart_tags as $key => $value) {
							$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
							$email_html = str_replace('{' . $key . '}', $value, $email_html);
						}

						// get html email and convert to plain text
						$this->CI->load->helper('html2text');
						$html2text = new \Html2Text\Html2Text($email_html);
						$email_plain = $html2text->get_text();

						foreach ($to as $item) {
							$this->send_email($item, $subject, $email_html, array(), TRUE, $staff_info->accountID);
						}

						$this->CI->db->update('bookings_lessons_staff', [
							'checkin_email_sent' => 1
						], [
							'recordID' => $lesson->recordID
						]);

						echo "Successfully sent to" . $staff_info->email . "\n";
					} else {
						echo "Failed sending for" . $staff_info->email . "\n";
					}
				};
			}
		}
	}

	public function send_checkout_staff_alerts() {
		$current_date = date('Y-m-d');

		$lessons = $this->get_today_potential_lessons();

		// if none, return false
		if (count($lessons) == 0) {
			return FALSE;
		}

		$current_date = date('Y-m-d');

		ksort($lessons);

		$lessons = array_values($lessons);

		//group by staff
		$staff_lessons = [];
		foreach ($lessons as $lessons_array) {
			foreach ($lessons_array as $lesson) {
				$staff_lessons[$lesson->staffID][] = $lesson;
			}
		}

		foreach ($staff_lessons as $staff => $lessons_array) {
			$query = $this->CI->db->select()
				->from('staff')
				->where([
					'staffID' => $staff
				])
				->limit(1)
				->get();

			if ($query->num_rows() < 1) {
				continue;
			}
			$staff_info = [];
			foreach ($query->result() as $row) {
				$staff_info = $row;
			}

			//check if selected account has checkin feature
			$query = $this->CI->db->select()
				->from('accounts')
				->where([
					'accountID' => $staff_info->accountID
				])->limit(1)->get();

			$account_info = [];
			foreach ($query->result() as $row) {
				$account_info = $row;
			}

			if ($account_info->addon_lesson_checkins != 1) {
				continue;
			}

			$company_name = $this->CI->settings_library->get('email_from_name', $staff_info->accountID);

			// smart tags
			$smart_tags = array(
				'company' => $company_name,
			);

			foreach ($lessons_array as $key => $lesson) {

				if ($lesson->provisional == 1) {
					continue;
				}

				if ($lesson->checkout_email_sent){
					continue;
				}

				//check if between sessions less than 1 hour
				if (isset($lessons_array[$key + 1])) {
					if (strtotime($current_date . ' ' . $lessons_array[$key + 1]->staff_start_time) - strtotime($current_date . ' ' . $lesson->staff_end_time) <= 3600) {
						continue;
					}
				}
				$end_time = $lesson->endTime;
				if (!empty($lesson->staff_end_time)) {
					$end_time = $lesson->staff_end_time;
				}

				$threshold_time = $this->CI->settings_library->get('email_not_checkout_staff_threshold_time', $staff_info->accountID);

				//not finished session or threshold_time is not over
				if (time() < (strtotime($current_date . ' ' . $end_time) + $threshold_time * 60)) {
					continue;
				}

				$query = $this->CI->db->select()
					->from('bookings_lessons_checkins')
					->where([
						'staffID' => $lesson->staffID,
						'lessonID' => $lesson->lessonID,
						'date' => $current_date,
						'not_checked_in' => 0
					])->limit(1)->order_by('logID DESC')->get();

				//staff was not even checked in - skip
				if ($query->num_rows() < 1) {
					continue;
				}

				$checkin = [];
				foreach ($query->result() as $row) {
					$checkin = $row;
				}

				//get checkouts and check if user is still checked in
				$query = $this->CI->db->select()
					->from('bookings_lessons_checkouts')
					->where([
						'staffID' => $lesson->staffID,
						'lessonID' => $lesson->lessonID,
						'date' => $current_date
					])
					->limit(1)->order_by('logID DESC')->get();

				$checkout = [];
				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$checkout = $row;
					}
				}

				// check if checkout was after checkin - skip
				if (!empty($checkout)) {
					if (strtotime($checkout->added) > strtotime($checkin->added)) {
						continue;
					}
				}

				$staff_addresses = [];
				$query = $this->CI->db->select()
					->from('staff_addresses')
					->where([
						'staffID' => $staff_info->staffID,
						'type' => 'main'
					])
					->get();

				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$staff_addresses = $row;
					}
				}

				// get email template
				$to = $staff_info->email;
				$subject = $this->CI->settings_library->get('email_not_checkout_staff_subject', $staff_info->accountID);
				$email_html = $this->CI->settings_library->get('email_not_checkout_staff_body', $staff_info->accountID);

				if (empty($to)) {
					continue;
				}

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
				}

				// get html email and convert to plain text
				$this->CI->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				if ($this->CI->settings_library->get('send_not_checkout_staff_email', $staff_info->accountID) == 1) {
					$this->send_email($to, $subject, $email_html, array(), TRUE, $staff_info->accountID);
				}

				if ($this->CI->settings_library->get('send_not_checkout_staff_sms', $staff_info->accountID) == 1) {
					$this->sms_send($staff_addresses->mobile_work, $email_plain, $company_name, $staff_info->accountID);
				}


				if ($this->CI->settings_library->get('send_not_checkout_staff_email', $staff_info->accountID) == 1 || $this->CI->settings_library->get('send_not_checkout_staff_sms', $staff_info->accountID) == 1) {
					//send emails to admins

					$to = [];

					if (!empty($this->CI->settings_library->get('email', $staff_info->accountID))) {
						$to[] = $this->CI->settings_library->get('email', $staff_info->accountID);
					}

					if (!empty($this->CI->settings_library->get('email_from', $staff_info->accountID))) {
						$to[] = $this->CI->settings_library->get('email_from', $staff_info->accountID);
					}

					if (empty($to)) {
						continue;
					}

					// smart tags
					$smart_tags = array(
						'staff_name' => $staff_info->first . ' ' . $staff_info->surname,
						'customer_name' => $lesson->org
					);

					$subject = $this->CI->settings_library->get('email_not_checkout_account_subject', $staff_info->accountID);
					$email_html = $this->CI->settings_library->get('email_not_checkout_account_body', $staff_info->accountID);

					// replace smart tags in email
					foreach ($smart_tags as $key => $value) {
						$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
						$email_html = str_replace('{' . $key . '}', $value, $email_html);
					}

					// get html email and convert to plain text
					$this->CI->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					foreach ($to as $item) {
						$this->send_email($item, $subject, $email_html, array(), TRUE, $staff_info->accountID);
					}

					$this->CI->db->update('bookings_lessons_staff', [
						'checkout_email_sent' => 1
					], [
						'recordID' => $lesson->recordID
					]);

					echo "Successfully sent to" . $staff_info->email . "\n";
				} else {
					echo "Failed sending for" . $staff_info->email . "\n";
				}
			}
		}
	}

	public function send_email($to = NULL, $subject = NULL, $message = NULL, $attachments = array(), $inline_attachments = FALSE, $accountID = NULL, $brandID = NULL, $bcc = NULL, $cc = NULL, $email_footer = NULL, $from_name = NULL, $reply_email = NULL) {

		// check params
		if (empty($to) || empty($subject) || empty($message))  {
			return FALSE;
		}

		// build email
		$body = '<style type="text/css">' . file_get_contents(APPPATH . '../dist/css/components/wysiwyg.css') . '</style>';

		// load config
		$this->CI->config->load('email', TRUE);

		// only send emails on production, if not send to tech email
		if (substr(ENVIRONMENT, 0, 10) !== 'production') {
			// tell dev environment and where email should have sent to
			$body .= '<p style="color:#ff0000;"><strong>' . strtoupper(ENVIRONMENT) . '</strong> - Email would normally be sent to: ' . $to . '</p>';
			// send email to tech instead
			$to = $this->CI->settings_library->get('tech_email', 'default');
		}

		// get company logo
		$logo_path = 'attachment/setting/logo';

		// lookup brand
		if (!empty($brandID)) {
			$where = array(
				'brandID' => $brandID
			);
			if (!empty($acountID)) {
				$where['accountID'] = $accountID;
			}
			$res = $this->CI->db->from('brands')->where($where)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					if (!empty($row->logo_path)) {
						$logo_path = 'attachment/brand/' . $row->logo_path;
					}
				}
			}
		}

		// append accountID
		if (!empty($accountID)) {
			$logo_path .= '/' . $accountID;
		}

		// get header
		$args = array(
			'src' => site_url($logo_path),
			'alt' => 'Logo',
			'height' => 40
		);
		$body .= '<p>' . img($args) . '</p>';
		$body .= $message;

		// load email library
		$this->CI->load->library('email');

		// clear previous emails
		$this->CI->email->clear(TRUE);

		// add attachments
		if (count($attachments) > 0) {
			foreach ($attachments as $path => $attachment) {
				// check if attachment is array, if so, get file contents from buffer
				if (is_array($attachment) && isset($attachment['file_name'], $attachment['file_contents'], $attachment['file_type'])) {
					$this->CI->email->attach($attachment['file_contents'], 'attachment', $attachment['file_name'], $attachment['file_type']);
				} else if ($inline_attachments == TRUE && in_array(strtolower(pathinfo($attachment, PATHINFO_EXTENSION)), array('jpg', 'jpeg', 'gif', 'png'))) {
					$this->CI->email->attach($path, 'inline', $attachment);
					$cid = $this->CI->email->attachment_cid($path);
					// add to html email
					$body .= "<p><img src=\"cid:" . $cid . "\" alt=\"" . $attachment . "\" /></p>";
				} else {
					$this->CI->email->attach($path, 'attachment', $attachment);
				}
			}
		}

		// add footer
		if(empty($email_footer)){
			$email_footer = $this->CI->settings_library->get('email_footer', $accountID);
		}
		$body .= $email_footer;

		// replace tags
		$smart_tags = array(
			'company' => NULL
		);

		// get company name
		if (!empty($accountID)) {
			$where = array(
				'accountID' => $accountID
			);
			$res = $this->CI->db->select('company')->from('accounts')->where($where)->limit(1)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// replace tags
					$smart_tags['company'] = $row->company;
				}
			}
		}

		foreach ($smart_tags as $key => $value) {
			$body = str_replace('{' . $key . '}', $value, $body);
		}

		// get plain email
		$this->CI->load->helper('html2text');
		$html2text = new \Html2Text\Html2Text($body);
		$body_alt = $html2text->get_text();

		// autolink
		$body = Linker::convert($body);
		$body = Linker::convertEmail($body);

		// get from
		$from_email = $this->CI->settings_library->get('email_from_default', 'default');
		$from_email_override = $this->CI->settings_library->get('email_from_override', $accountID);
		if(empty($from_name)) {
			$from_name = $this->CI->settings_library->get('email_from_name', $accountID);
		}
		if(empty($reply_email)) {
			$reply_email = $this->CI->settings_library->get('email_from', $accountID);
		}

		// process override
		if (!empty($from_email_override)) {
			$from_email = $from_email_override;
		}

		// build email
		$this->CI->email->from($from_email, $from_name);
		$this->CI->email->reply_to($reply_email, $from_name);
		$this->CI->email->to($to);
		$this->CI->email->subject($subject);
		$this->CI->email->message($body);
		$this->CI->email->set_alt_message($body_alt);

		// bcc (production only)
		if (substr(ENVIRONMENT, 0, 10) === 'production' && !empty($bcc)) {
			$this->CI->email->bcc($bcc);
		}

		// cc (production only)
		if (substr(ENVIRONMENT, 0, 10) === 'production' && !empty($cc)) {
			$this->CI->email->cc($cc);
		}

		// send
		if (!$this->CI->email->send()) {
			//echo $this->CI->email->print_debugger();
			return FALSE;
		}

		return TRUE;
	}

	// get upcoming addresses someone is teaching at in next month
	public function get_upcoming_addresses($staffID = NULL) {

		// check params
		if (empty($staffID)) {
			return FALSE;
		}

		// get all addresses scheduled to teach at in future
		$addressIDs = array();

		// check if has normal sessions at this address
		$where = array(
			'bookings_lessons_staff.staffID' => $staffID,
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+1 month'))
		);

		$res = $this->CI->db->select('bookings_lessons.*')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$addressIDs[$row->addressID] = $row->addressID;
			}
		}

		// check if has event at this address
		$where = array(
			'bookings_lessons_staff.staffID' => $staffID,
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+1 month'))
		);

		$res = $this->CI->db->select('bookings.*')->from('bookings_lessons_staff')->join('bookings', 'bookings_lessons_staff.bookingID = bookings.bookingID')->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$addressIDs[$row->addressID] = $row->addressID;
			}
		}

		// check if has normal sessions at this address (via exception)
		$where = array(
			'bookings_lessons_exceptions.staffID' => $staffID,
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+1 month'))
		);

		$res = $this->CI->db->select('bookings_lessons.*')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$addressIDs[$row->addressID] = $row->addressID;
			}
		}

		// check if has event at this address (via exception)
		$where = array(
			'bookings_lessons_exceptions.staffID' => $staffID,
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s', strtotime('+1 month'))
		);

		$res = $this->CI->db->select('bookings.*')->from('bookings_lessons_exceptions')->join('bookings', 'bookings_lessons_exceptions.bookingID = bookings.bookingID')->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_exceptions.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$addressIDs[$row->addressID] = $row->addressID;
			}
		}

		// remove empty values
		$addressIDs = array_filter($addressIDs);

		return $addressIDs;
	}

	/**
	 * duplicate lessons
	 * @param  array  $lessons   Key as session ID with DB object as value
	 * @param  int $new_block
	 * @return int
	 */
	function duplicate_lessons($lessons = array(), $new_block = NULL, $new_bookingID = NULL) {
		$lessons_duplicated = 0;

		if (!is_array($lessons) || count($lessons) == 0) {
			return $lessons_duplicated;
		}

		foreach ($lessons as $lessonID => $lesson_info) {
			// convert session info to data array
			$data = get_object_vars($lesson_info);

			// unset vars from different tables
			if (array_key_exists('type', $data)) {
				unset($data['type']);
			}
			if (array_key_exists('activity', $data)) {
				unset($data['activity']);
			}

			// remove key
			unset($data['lessonID']);

			// remove offer/accept fields
			unset($data['offer_accept_status']);
			unset($data['offer_accept_groupID']);
			unset($data['offer_accept_reason']);

			// use new booking id if set
			if (!empty($new_bookingID)) {
				$data['bookingID'] = $new_bookingID;
			}

			// update vars
			$data['byID'] = $this->CI->auth->user->staffID;
			$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
			$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

			// if copying to another block, switch data
			if ($new_block !== NULL) {
				$data['blockID'] = $new_block;
			}

			$res = $this->CI->db->insert('bookings_lessons', $data);

			if ($this->CI->db->affected_rows() > 0) {
				$lessons_duplicated++;

				// get id
				$newLessonID = $this->CI->db->insert_id();

				// copy org attachments
				$where = array(
					'lessonID' => $lessonID
				);
				$res = $this->CI->db->from('bookings_lessons_orgs_attachments')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result_array() as $data) {
						// remove key
						unset($data['actualID']);

						// update vars
						$data['byID'] = $this->CI->auth->user->staffID;
						$data['lessonID'] = $newLessonID;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

						// use new booking id if set
						if (!empty($new_bookingID)) {
							$data['bookingID'] = $new_bookingID;
						}

						$res_copy = $this->CI->db->insert('bookings_lessons_orgs_attachments', $data);
					}
				}

				// copy resource attachments
				$where = array(
					'lessonID' => $lessonID
				);
				$res = $this->CI->db->from('bookings_lessons_resources_attachments')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result_array() as $data) {
						// remove key
						unset($data['actualID']);

						// update vars
						$data['byID'] = $this->CI->auth->user->staffID;
						$data['lessonID'] = $newLessonID;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

						// use new booking id if set
						if (!empty($new_bookingID)) {
							$data['bookingID'] = $new_bookingID;
						}

						$res_copy = $this->CI->db->insert('bookings_lessons_resources_attachments', $data);
					}
				}

				// copy vouchers
				$where = array(
					'lessonID' => $lessonID
				);
				$res = $this->CI->db->from('bookings_lessons_vouchers')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result_array() as $data) {
						// remove key
						unset($data['linkID']);

						// update vars
						$data['byID'] = $this->CI->auth->user->staffID;
						$data['lessonID'] = $newLessonID;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

						$res_copy = $this->CI->db->insert('bookings_lessons_vouchers', $data);
					}
				}

			}
		}

		return $lessons_duplicated;
	}

	/**
	 * send customer booking notification
	 * @param  integer $blockID
	 * @return mixed
	 */
	public function send_customer_booking_notification($blockID = NULL, $contactID = NULL) {

		// check params
		if (empty($blockID) || empty($contactID)) {
			return FALSE;
		}

		// look up record
		$where = array(
			'blockID' => $blockID,
			'orgID IS NOT NULL' => NULL
		);

		$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $block_info);

		// check if should send
		if ($this->CI->settings_library->get('send_customer_booking_notification', $block_info->accountID) != 1) {
			return FALSE;
		}

		// look up org
		$where = array(
			'orgID' => $block_info->orgID
		);

		$res = $this->CI->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $org_info);

		$this->CI->load->library('Orgs_library');

		$contact_info = $this->CI->orgs_library->findContactById($contactID, false);

		if (!$contact_info) {
			return false;
		}

		// smart tags
		$smart_tags = array(
			'org_name' => $org_info->name,
			'block_name' => $block_info->name,
			'block_link' => site_url('bookings/sessions/' . $block_info->bookingID . '/' . $block_info->blockID),
			'detail_link' => site_url('bookings/blocks/edit/' . $block_info->blockID),
			'contact_full_name' => $contact_info->name,
			'contact_email' => $contact_info->email
		);

		// get email template
		$to = $this->CI->settings_library->get('email_customer_booking_notification_to', $block_info->accountID);
		$subject = $this->CI->settings_library->get('email_customer_booking_notification_subject', $block_info->accountID);
		$email_html = $this->CI->settings_library->get('email_customer_booking_notification', $block_info->accountID);

		// if no to, send to orders from
		if (empty($to)) {
			$to = $this->CI->settings_library->get('email_from', $block_info->accountID);
		}

		// if still empty, stop
		if (empty($to)) {
			return FALSE;
		}

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		// replace smart tags in subject
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
		}

		// get html email and convert to plain text
		$this->CI->load->helper('html2text');
		$html2text = new \Html2Text\Html2Text($email_html);
		$email_plain = $html2text->get_text();

		if ($this->send_email($to, $subject, $email_html, array(), TRUE, $block_info->accountID)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}


	/**
	 * send customer booking confirmation
	 * @param  integer $blockID
	 * @param  integer $contactID
	 * @return mixed
	 */
	public function send_customer_booking_confirmation($blockID = NULL, $contactID = NULL) {

		// check params
		if (empty($blockID) || empty($contactID)) {
			return FALSE;
		}

		// look up record
		$where = array(
			'blockID' => $blockID,
			'orgID IS NOT NULL' => NULL
		);

		$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $block_info);

		// check if should send
		if ($this->CI->settings_library->get('send_customer_booking_confirmation', $block_info->accountID) != 1) {
			return FALSE;
		}

		// look up org
		$where = array(
			'orgID' => $block_info->orgID
		);

		$res = $this->CI->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $org_info);

		// look up contact
		$where = array(
			'contactID' => $contactID,
			'orgID' => $block_info->orgID
		);

		$res = $this->CI->db->from('orgs_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info);

		// smart tags
		$smart_tags = array(
			'org_name' => $org_info->name,
			'block_name' => $block_info->name,
			'contact_name' => $contact_info->name,
			'date_description' => ' between ' . mysql_to_uk_date($block_info->startDate) . ' and ' . mysql_to_uk_date($block_info->endDate),
			'details' => '<p><em>No details available</em></p>'
		);

		// get sessions
		$where = array(
			'bookings_lessons.blockID' => $blockID
		);
		$res_lessons = $this->CI->db->select('bookings_lessons.*, activities.name as activity')->from('bookings_lessons')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->where($where)->order_by('bookings_lessons.startDate asc, bookings_lessons.endDate asc, bookings_lessons.day asc, bookings_lessons.startTime asc, bookings_lessons.endTime asc')->get();
		if ($res_lessons->num_rows() > 0) {
			$smart_tags['details'] = '<table width="100%" border="1">
				<tr>
					<th scope="col">Day</th>
					<th scope="col">Start</th>
					<th scope="col">End</th>
					<th scope="col">Activity</th>
					<th scope="col">Class Size</th>
				</tr>';
			foreach ($res_lessons->result() as $row) {
				$activity = 'Unknown';
				if (!empty($row->activity)) {
					$activity = $row->activity;
				} else if (!empty($row->activity_other)) {
					$activity = $row->activity_other;
				}
				$class_size = '-';
				if (!empty($row->class_size)) {
					$class_size = $row->class_size;
				}
				$smart_tags['details'] .= '<tr>
					<td>' . ucwords($row->day);
						if (!empty($row->startDate)) {
							$smart_tags['details'] .= ' (' . mysql_to_uk_date($row->startDate);
							if (!empty($row->endDate) && strtotime($row->endDate) > strtotime($row->startDate)) {
								$smart_tags['details'] .= '-' . mysql_to_uk_date($row->endDate);
							}
							$smart_tags['details'] .= ')';
						}
					$smart_tags['details'] .= '</td>
					<td>' . substr($row->startTime, 0, 5) . '</td>
					<td>' . substr($row->endTime, 0, 5) . '</td>
					<td>' . $activity;
						if (!empty($row->activity_desc)) {
							$smart_tags['details'] .= ': ' . $row->activity_desc;
						}
					$smart_tags['details'] .= '</td>
					<td>' . $class_size . '</td>
				</tr>';
			}
			$smart_tags['details'] .= '</table>';
		}

		// get email template
		$subject = $this->CI->settings_library->get('email_customer_booking_confirmation_subject', $block_info->accountID);
		$email_html = $this->CI->settings_library->get('email_customer_booking_confirmation', $block_info->accountID);

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		// replace smart tags in subject
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
		}

		// get html email and convert to plain text
		$this->CI->load->helper('html2text');
		$html2text = new \Html2Text\Html2Text($email_html);
		$email_plain = $html2text->get_text();

		if ($this->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $block_info->accountID)) {
			// save
			$data = array(
				'orgID' => $block_info->orgID,
				'contactID' => $contactID,
				'byID' => NULL,
				'type' => 'email',
				'destination' => $contact_info->email,
				'subject' => $subject,
				'contentHTML' => $email_html,
				'contentText' => $email_plain,
				'status' => 'sent',
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $block_info->accountID
			);
			$this->CI->db->insert('orgs_notifications', $data);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * send event confirmation to contact
	 * @param  integer $cartID
	 * @param boolean $only_current Send out only current and future blocks
	 * @return mixed
	 */
	public function send_event_confirmation($cartID = NULL , $only_current = TRUE) {

		// check params
		if (empty($cartID)) {
			return FALSE;
		}

		// look up record
		$where = array(
			'cartID' => $cartID,
			'type' => 'booking'
		);

		$res = $this->CI->db->from('bookings_cart')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $cart_info);

		// check if should send
		if ($this->CI->settings_library->get('send_event_confirmation', $cart_info->accountID) != 1) {
			return FALSE;
		}

		// look up contact
		$where = array(
			'contactID' => $cart_info->contactID
		);

		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info);

		// smart tags
		$smart_tags = array(
			'contact_title' => $contact_info->title,
			'contact_first' => $contact_info->first_name,
			'contact_last' => $contact_info->last_name
		);

		// work out details

		// get sessions
		$email_html = $this->get_booked_sessions_html($cartID, $only_current, TRUE);
		// if no sessions, stop
		if ($email_html === FALSE) {
			return FALSE;
		}

		if ($cart_info->childcarevoucher_providerID > 0) {
			$email_html .= "<p>Full or partial payment by childcare voucher";
			if (!empty($cart_info->childcarevoucher_provider)) {
				$email_html .= ": " . $cart_info->childcarevoucher_provider;
				if (!empty($cart_info->childcarevoucher_ref)) {
					$email_html .= " (Ref: " . $cart_info->childcarevoucher_ref . ")";
				}
			} else if (!empty($cart_info->childcarevoucher_providerID)) {
				// look up
				$where = array(
					'providerID' => $cart_info->childcarevoucher_providerID
				);

				$res = $this->CI->db->from('settings_childcarevoucherproviders')->where($where)->limit(1)->get();

				if ($res->num_rows() == 1) {
					foreach ($res->result() as $provider_info) {
						$email_html .= ": " . $provider_info->name;
						if (!empty($provider_info->reference)) {
							$email_html .= " (Ref: " . $provider_info->reference . ")";
						}
					}
				}
			}
			$email_html .= ".</p>";
		}

		// get email template
		$subject = $this->CI->settings_library->get('email_event_confirmation_subject', $cart_info->accountID);

		// replace smart tags
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
			$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
		}

		// get html email and convert to plain text
		$this->CI->load->helper('html2text');
		$html2text = new \Html2Text\Html2Text($email_html);
		$email_plain = $html2text->get_text();

		// if no email for contact, send to default email
		if (empty($contact_info->email)) {
			$contact_info->email = $this->CI->settings_library->get('email_from', $cart_info->accountID);
		}


		//Get department wise email templates
		$reply_email = NULL;
		$from_name = NULL;
		$email_footer = NULL;
		$where = array(
			'bookings_cart_sessions.cartID' => $cartID,
		);
		$brand_id = $this->CI->db->select('bookings.brandID')
			->from('bookings_cart_sessions')
			->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
			->where($where)
			->group_by('bookings_cart_sessions.bookingID')
			->limit(1)
			->get();
		if($brand_id->num_rows() > 0){
			foreach($brand_id->result() as $row) break;
			$where = array(
				'settings_departments_relation.accountID' => $cart_info->accountID,
				'settings_departments_relation.departmentID' => $row->brandID,
				'settings_departments_email.active' => '1',
			);
			$department_email = $this->CI->db->select('settings_departments_email.*')
				->from('settings_departments_relation')
				->join('settings_departments_email', 'settings_departments_relation.department_email_id = settings_departments_email.ID', 'left')
				->where($where)
				->limit(1)
				->get();
			if($department_email->num_rows() > 0){
				foreach($department_email->result() as $row) break;
				$reply_email = $row->reply_email;
				$from_name = $row->from_name;
				$email_footer = $row->email_footer;
			}

		}

		// event attachments
		$attachments = array();
		$attachmentIDs = array();

		// get booking IDs for fetching attachments
		$bookingIDs = array();
		$blockIDs = array();
		$where = array(
			'bookings_cart_sessions.cartID' => $cartID,
		);
		if ($only_current === TRUE) {
			$where['bookings_cart_sessions.date >='] = date('Y-m-d');
		}
		$res = $this->CI->db->select('bookings_cart_sessions.bookingID, bookings_cart_sessions.blockID')
		->from('bookings_cart_sessions')
		->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
		->where($where)
		->group_by('bookings_cart_sessions.bookingID, bookings_cart_sessions.blockID')
		->get();
		if ($res->num_rows() > 0) {
			foreach($res->result() as $row) {
				$bookingIDs[] = $row->bookingID;
				$blockIDs[] = $row->blockID;
			}
		} else {
			// no bookings, stop
			return false;
		}

		// get attachments associated with booking blocks
		if (count($blockIDs) > 0) {
			$where = array(
				'bookings_attachments.accountID' => $cart_info->accountID
			);
			$res = $this->CI->db->select('bookings_attachments.*')
			->from('bookings_attachments')
			->join('bookings_attachments_blocks', 'bookings_attachments.attachmentID = bookings_attachments_blocks.attachmentID', 'inner')
			->where($where)
			->where_in('bookings_attachments_blocks.blockID', $blockIDs)
			->group_by('bookings_attachments.attachmentID')
			->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$path = UPLOADPATH_SHARED . $row->accountID . '/' . $row->path;
					$attachments[$path] = $row->name;
					$attachmentIDs[] = $row->attachmentID;
				}
			}
		}

		if ($this->send_email($contact_info->email, $subject, $email_html, $attachments, TRUE, $cart_info->accountID, NULL, $this->CI->settings_library->get('email_event_confirmation_bcc', $cart_info->accountID),NULL, $email_footer, $from_name, $reply_email)) {
			$byID = NULL;
			if (isset($this->CI->auth->user->staffID)) {
				$byID = $this->CI->auth->user->staffID;
			}

			// save
			$data = array(
				'familyID' => $cart_info->familyID,
				'contactID' => $cart_info->contactID,
				'byID' => $byID,
				'type' => 'email',
				'destination' => $contact_info->email,
				'subject' => $subject,
				'contentHTML' => $email_html,
				'contentText' => $email_plain,
				'status' => 'sent',
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $cart_info->accountID
			);

			$this->CI->db->insert('family_notifications', $data);

			$notificationID = $this->CI->db->insert_id();

			// save attachments
			if (count($attachmentIDs) > 0) {
				foreach ($attachmentIDs as $attachmentID) {
					$data = array(
						'notificationID' => $notificationID,
						'attachmentID' => $attachmentID,
						'accountID' => $cart_info->accountID
					);

					$this->CI->db->insert('family_notifications_attachments', $data);
				}
			}

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * get html of booked sessions
	 * @param  int $cartID
	 * @param boolean $only_current Send out only current and future blocks
	 * @param boolean $with_totals Include totals at bottom
	 * @return string
	 */
	public function get_booked_sessions_html($cartID = NULL, $only_current = TRUE, $with_totals = FALSE) {

		// check params
		if (empty($cartID)) {
			return FALSE;
		}

		// look up record
		$where = array(
			'cartID' => $cartID,
			'type' => 'booking'
		);

		$res = $this->CI->db->from('bookings_cart')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $cart_info);

		// load libraries
		if (isset($this->CI->cart_library)) {
			unset($this->CI->cart_library);
		}
		$args = array(
			'accountID' => $cart_info->accountID,
			'contactID' => $cart_info->contactID,
			'in_crm' => TRUE
		);
		$this->CI->load->library('cart_library', $args);

		// get email template
		$output_primary = $this->CI->settings_library->get('email_event_confirmation', $cart_info->accountID);

		// get booking items
		$where = array(
			'bookings_cart_sessions.cartID' => $cartID,
		);
		$res = $this->CI->db->select('family_contacts.familyID, family_children.familyID as child_familyID, bookings_blocks.startDate, bookings_blocks.endDate, bookings_cart_sessions.*, bookings.register_type, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, family_children.first_name as child_first, family_children.last_name as child_last')
		->from('bookings_cart_sessions')
		->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
		->join('bookings_blocks', 'bookings_cart_sessions.blockID = bookings_blocks.blockID', 'inner')
		->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
		->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
		->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
		->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
		->where($where)
		->order_by('bookings_blocks.startDate asc, bookings_cart_sessions.date asc')
		->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		$blockIDs = array();
		$booked_sessions = array();
		$session_prices = array();
		$session_totals = array();
		$block_priced = array();
		$familyID = NULL;
		foreach ($res->result() as $row) {
			if(!empty($row->familyID)){
				$familyID = $row->familyID;
			}else if(!empty($row->child_familyID)){
				$familyID = $row->child_familyID;
			}
			$blockIDs[$row->blockID] = $row->blockID;

			// determine participant
			$row->participant = $row->child_first . ' ' . $row->child_last;
			if (strpos($row->register_type, 'individuals') === 0) {
				$row->participant = $row->contact_first . ' ' . $row->contact_last;
			}

			// add participant to lesson
			if (!isset($booked_sessions[$row->blockID][$row->date][$row->lessonID])) {
				$booked_sessions[$row->blockID][$row->date][$row->lessonID] = array();
			}
			$booked_sessions[$row->blockID][$row->date][$row->lessonID][] = $row->participant;

			// session prices
			if (!isset($session_prices[$row->blockID][$row->date][$row->lessonID])) {
				$session_prices[$row->blockID][$row->date][$row->lessonID] = 0;
			}
			$session_prices[$row->blockID][$row->date][$row->lessonID] += $row->price;

			// session totals
			if (!isset($session_totals[$row->blockID][$row->date][$row->lessonID])) {
				$session_totals[$row->blockID][$row->date][$row->lessonID] = 0;
			}
			$session_totals[$row->blockID][$row->date][$row->lessonID] += $row->total;

			// check if block priced
			if ($row->block_priced == 1) {
				$block_priced[$row->blockID] = true;
			}
		}

		// get blocks
		$search_where = array();
		if ($only_current === TRUE) {
			$search_where['future_only'] = 2;
		}
		$custom_where = " AND `" . $this->CI->db->dbprefix("bookings_blocks") . "`.`blockID` IN (" . $this->CI->db->escape_str(implode(',', $blockIDs)) . ")";
		$blocks = $this->CI->cart_library->get_blocks(array(), $search_where, $custom_where);

		if (count($blocks) == 0) {
			return FALSE;
		}

		// start to build HTML
		$has_lessons = FALSE;
		$output = '';

		$booking_subtotal = 0;
		$booking_discount = 0;
		$booking_total = 0;

		// loop blocks
		$i = 0;
		foreach ($blocks as $blockID => $block) {
			if (strpos($output_primary, '{event_name}') !== false) {
				$output_primary = str_replace('{event_name}', $block->booking, $output_primary);
			}
			if (strpos($output_primary, '{date_description}') !== false) {
				$output_primary = str_replace('{date_description}', ' between ' . mysql_to_uk_date($block->startDate) . ' and ' . mysql_to_uk_date($block->endDate), $output_primary);
			}
			$i++;
			$block_total = 0;
			if ($i >= 2) {
				$output .= '<div class="hr"></div>';
			}
			$output .= '<h3>' . $block->booking . ' - BLOCKTOTAL</h3>';
			$output .= '<p class="event-specs">';
				if (!empty($block->location)) {
					$output .= '<span><strong>Location:</strong> ' . $block->location . '</span><br /> ';
				}
				if ($this->CI->settings_library->get('email_event_confirmation_include_address', $cart_info->accountID) == 1) {
					$output .= '<span><strong>Address:</strong> ' . $block->address . '</span><br /> ';
				}
				$output .= '<span><strong>Event:</strong> ' . $block->block . '</span>
			</p>';
			foreach ($booked_sessions[$blockID] as $date => $lessons) {
				if (!array_key_exists($date, $blocks[$blockID]->dates)) {
					continue;
				}
				$output .= '<h4 class="no-bottom-margin">' . date('D jS M', strtotime($date)) . '</h4>
				<table border="0" class="no-border"><tr>
					<td width="80%">';
						foreach ($lessons as $lessonID => $participants) {
							if (array_key_exists($lessonID, $blocks[$blockID]->dates[$date])) {
								$lesson = $blocks[$blockID]->dates[$date][$lessonID];
								sort($participants);
								$output .= ' - ' . $lesson['time'];
								if (!empty($lesson['type'])) {
									$output .= ' - ' . $lesson['type'];
								}
								if (!empty($lesson['activity']) && $lesson['activity'] !== $lesson['type']) {
									$output .= ' (' . $lesson['activity'] . ')';
								}
								$output .= ' - <strong>' . implode(", ", $participants) . '</strong>';
								if ($this->CI->settings_library->get('email_event_confirmation_include_address', $cart_info->accountID) == 1 && !empty($lesson['address'])) {
									$output .= ' <em>(' . $lesson['address'] . ')</em>';
								}
								$output .= '<br>';
								$has_lessons = TRUE;
							}
						}
					$output .= '</td>
					<td width="20%" class="right" valign="middle" align="right">';
						$price = 0;
						$total = 0;
						foreach ($lessons as $lessonID => $participants) {
							$price += $session_prices[$blockID][$date][$lessonID];
							$total += $session_totals[$blockID][$date][$lessonID];
						}
						// dont show pricing if block priced
						if (!array_key_exists($blockID, $block_priced)) {
							if ($total < $price) {
								$output .= '<span style="text-decoration:line-through; color:red;">';
							}
							if ($price > 0) {
								$output .= currency_symbol($cart_info->accountID) . number_format($price, 2);
							} else {
								$output .= 'Free';
							}
							if ($total < $price) {
								$output .= '</span> ';
								if ($total > 0) {
									$output .= currency_symbol($cart_info->accountID) . number_format($total, 2);
								} else {
									$output .= 'Free';
								}
							}
						}
						$booking_subtotal += $price;
						$booking_discount += ($price - $total);
						$booking_total += $total;
						$block_total += $total;
					$output .= '</td>
				</tr></table>';
			}
			if (!empty($block->booking_instructions)) {
				$output .= $block->booking_instructions;
			}
			if ($block_total > 0){
				$output = str_replace('BLOCKTOTAL', currency_symbol($cart_info->accountID) . number_format($block_total, 2), $output);
			} else {
				$output = str_replace('BLOCKTOTAL', 'Free', $output);
			}
		}

		if (!$has_lessons) {
			return FALSE;
		}

		if ($with_totals) {
			$output .= '<div class="hr"></div><h3>Summary</h3>';

			$output .= '<table border="0" class="no-border">';

			if ($booking_subtotal > 0 && $booking_subtotal != $booking_total) {
				$output .= '<tr><td width="20%" class="no-left-padding"><strong>Sub Total</strong></td><td width="20%" class="right" valign="middle" align="right">' . currency_symbol($cart_info->accountID) . number_format($booking_subtotal, 2) . "</td></tr>";
			}

			if ($booking_discount > 0) {
				$output .= '<tr><td width="20%" class="no-left-padding"><strong>Discount</strong></td><td width="20%" class="right" valign="middle" align="right" style="color:red;">-' . currency_symbol($cart_info->accountID) . number_format($booking_discount, 2) . "</td></tr>";
			}

			if ($booking_total == 0) {
				$output .= '<tr><td width="20%" class="no-left-padding"><strong>Total</strong></td><td width="20%" class="right" valign="middle" align="right">Free</td></tr>';
			} else {
				$output .= '<tr><td width="20%" class="no-left-padding"><strong>Total</strong></td><td width="20%" class="right" valign="middle" align="right">' . currency_symbol($cart_info->accountID) . number_format($booking_total, 2) . "</td></tr>";
			}

			//Get family balance
			$where_family = array(
				'familyID' => $familyID,
				'accountID' => $cart_info->accountID
			);
			$family_balance = $this->CI->db->from('family')->where($where_family)->limit(1)->get();
			$account_balance = 0;
			if ($family_balance->num_rows() > 0) {
				foreach ($family_balance->result() as $family_info) break;
				$account_balance = $family_info->account_balance;
			}
			if($account_balance < 0) {
				$output .= '<tr><td width="20%" class="no-left-padding"><strong>Total Outstanding Balance</strong></td><td width="20%" class="right" valign="middle" align="right">' . currency_symbol($cart_info->accountID) . number_format($account_balance, 2) . "</td></tr>";
			}
			$output .= '</table>';
		}

		if (strpos($output_primary, '{details}') !== false) {
			$output_primary = str_replace('{details}', $output, $output_primary);
			return $output_primary;
		}
		return $output;
	}

	/**
	 * send payment confirmation to contact
	 * @param  integer $paymentID
	 * @return mixed
	 */
	public function send_payment_confirmation($paymentID = NULL) {

		// check params
		if (empty($paymentID)) {
			return FALSE;
		}

		// look up record
		$where = array(
			'paymentID' => $paymentID
		);

		$res = $this->CI->db->from('family_payments')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $payment_info);

		// check if should send
		if ($this->CI->settings_library->get('send_payment_confirmation', $payment_info->accountID) != 1) {
			return FALSE;
		}

		// look up contact
		$where = array(
			'contactID' => $payment_info->contactID
		);

		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info);

		// nicer names for payment methods
		switch ($payment_info->method) {
			case 'card':
				$method = 'Credit/Debit Card';
				break;
			default:
				$method = ucwords($payment_info->method);
				break;
		}

		// smart tags
		$smart_tags = array(
			'contact_title' => $contact_info->title,
			'contact_first' => $contact_info->first_name,
			'contact_last' => $contact_info->last_name,
			'date' => mysql_to_uk_date($payment_info->added),
			'reference' => $payment_info->transaction_ref,
			'method' => $method,
			'amount' => currency_symbol($payment_info->accountID) . $payment_info->amount,
		);

		// get email template
		$subject = $this->CI->settings_library->get('email_payment_confirmation_subject', $payment_info->accountID);
		$email_html = $this->CI->settings_library->get('email_payment_confirmation', $payment_info->accountID);

		// replace smart tags
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
			$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
		}

		// get html email and convert to plain text
		$this->CI->load->helper('html2text');
		$html2text = new \Html2Text\Html2Text($email_html);
		$email_plain = $html2text->get_text();

		// if no email for contact, dont send
		if (empty($contact_info->email)) {
			return FALSE;
		}

		if ($this->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $payment_info->accountID)) {

			$byID = NULL;
			if (isset($this->CI->auth->user->staffID)) {
				$byID = $this->CI->auth->user->staffID;
			}

			// save
			$data = array(
				'familyID' => $payment_info->familyID,
				'contactID' => $payment_info->contactID,
				'byID' => $byID,
				'type' => 'email',
				'destination' => $contact_info->email,
				'subject' => $subject,
				'contentHTML' => $email_html,
				'contentText' => $email_plain,
				'status' => 'sent',
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $payment_info->accountID
			);

			$this->CI->db->insert('family_notifications', $data);

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * @param $account
	 * @return bool
	 */
	public function send_expiring_alert($account) {
		$smart_tags = array(
			'account_name' => $account->company,
			'account_date' => $account->paid_until,
		);

		// get email template
		$subject = $this->CI->settings_library->get('send_renewal_alert_subject');
		$email_html = $this->CI->settings_library->get('send_renewal_alert_message');
		$email_recipient = $this->CI->settings_library->get('send_renewal_alert_recipient');

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		return $this->send_email($email_recipient, $subject, $email_html);
	}

	/**
	 * send thanks emails for event
	 *
	 * @param string $type
	 * @param int $ID
	 * @return void
	 */
	public function send_thanks_email($type = NULL, $ID = NULL) {

		// check params
		if (empty($ID)) {
			return FALSE;
		}

		$table = 'bookings_blocks';
		$id_field = 'blockID';
		if ($type == 'event') {
			$table = 'bookings';
			$id_field = 'bookingID';
		}

		// look up record
		$where = array(
			$id_field => $ID
		);

		$res = $this->CI->db->from($table)->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $res_info);

		$bookingID = $res_info->bookingID;

		// look up event
		$where = array(
			'bookings.bookingID' => $bookingID
		);

		$res = $this->CI->db->select('bookings.*, brands.website as brand_website')
		->from('bookings')
		->join('brands', 'bookings.brandID = brands.brandID', 'left')
		->where($where)
		->limit(1)
		->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $event_info);

		// set to sent in table - before in case fails so don't get sent twice
		$where = array(
			$id_field => $ID
		);
		$data = array(
			'thanksemail_sent' => 1
		);

		$res = $this->CI->db->update($table, $data, $where, 1);

		// event attachments
		$attachments = array();
		$attachmentIDs = array();

		$where = array(
			'bookings_attachments.bookingID' => $bookingID,
			'bookings_attachments.sendwiththanks' => 1
		);

		$res = $this->CI->db->select('bookings_attachments.*, GROUP_CONCAT(' . $this->CI->db->dbprefix('bookings_attachments_blocks') . '.blockID) AS blocks')
		->from('bookings_attachments')
		->join('bookings_attachments_blocks', 'bookings_attachments.attachmentID = bookings_attachments_blocks.attachmentID', 'left')
		->where($where)
		->group_by('bookings_attachments.attachmentID')
		->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$blocks = explode(",", $row->blocks);
				if (!is_array($blocks)) {
					$blocks = array();
				}
				$blocks = array_filter($blocks);
				// if no blocks assigned to attachment, attach, else match if type is block
				if (count($blocks) == 0 || ($type == 'block' && in_array($ID, $blocks))) {
					$path = UPLOADPATH_SHARED . $row->accountID . '/' . $row->path;
					$attachments[$path] = $row->name;
					$attachmentIDs[] = $row->attachmentID;
				}
			}
		}

		// look up participants contacts
		$where = array(
			'bookings_cart_sessions.' . $id_field => $ID
		);

		$contacts = $this->CI->db->select('family_contacts.*')
		->from('bookings_cart_sessions')
		->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
		->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'inner')
		->where($where)
		->group_by('family_contacts.contactID')
		->get();

		if ($contacts->num_rows() == 0) {
			return FALSE;
		}

		foreach ($contacts->result() as $contact) {

			// check email not empty and valid
			if (empty($contact->email) || !filter_var($contact->email, FILTER_VALIDATE_EMAIL)) {
				// skip
				continue;
			}

			// smart tags
			$smart_tags = array(
				'contact_title' => $contact->title,
				'contact_first' => $contact->first_name,
				'contact_last' => $contact->last_name,
				'event_name' => $event_info->name,
				'website' => $event_info->brand_website
			);
			if ($type == 'block') {
				$smart_tags['block_name'] = $res_info->name;
			}

			// if website empty, use company site
			if (empty($smart_tags['website'])) {
				$smart_tags['website'] = $this->CI->settings_library->get('website', $contact->accountID);
			}

			// get email template
			$subject = $this->CI->settings_library->get('email_' . $type . '_thanks_subject', $contact->accountID);
			$email_html = $this->CI->settings_library->get('email_' . $type . '_thanks', $contact->accountID);

			// check for override from event
			if (!empty($res_info->thanksemail_text)) {
				$email_html = $res_info->thanksemail_text;
			}

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				if ($key != 'website') {
					$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
				}
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
			}

			// get html email and convert to plain text
			$this->CI->load->helper('html2text');
			$html2text = new \Html2Text\Html2Text($email_html);
			$email_plain = $html2text->get_text();

			if ($this->send_email($contact->email, $subject, $email_html, $attachments, TRUE, $event_info->accountID, $event_info->brandID)) {

				// save
				$data = array(
					'familyID' => $contact->familyID,
					'contactID' => $contact->contactID,
					'byID' => $this->CI->auth->user->staffID,
					'type' => 'email',
					'destination' => $contact->email,
					'subject' => $subject,
					'contentHTML' => $email_html,
					'contentText' => $email_plain,
					'status' => 'sent',
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $contact->accountID
				);

				$this->CI->db->insert('family_notifications', $data);

				$notificationID = $this->CI->db->insert_id();

				// save attachments
				if (count($attachmentIDs) > 0) {
					foreach ($attachmentIDs as $attachmentID) {
						$data = array(
							'notificationID' => $notificationID,
							'attachmentID' => $attachmentID,
							'accountID' => $contact->accountID
						);

						$this->CI->db->insert('family_notifications_attachments', $data);
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * map session groups to names
	 * @param  string  $key
	 * @param  boolean $return_map
	 * @return mixed
	 */
	public function format_lesson_group($key = NULL, $return_map = FALSE) {

		// map values
		$map = array(
			'f1' => 'F1',
			'f2' => 'F2',
			'yr1' => 'Year 1',
			'yr2' => 'Year 2',
			'yr3' => 'Year 3',
			'yr4' => 'Year 4',
			'yr5' => 'Year 5',
			'yr6' => 'Year 6',
			'yr7' => 'Year 7',
			'yr8' => 'Year 8',
			'yr9' => 'Year 9',
			'yr10' => 'Year 10',
			'yr11' => 'Year 11',
			'yr12' => 'Year 12',
			'yr13' => 'Year 13',
			'yr1+2' => 'Years 1 + 2',
			'yr3+4' => 'Years 3 + 4',
			'yr5+6' => 'Years 5 + 6',
			'ks1' => 'Key Stage 1',
			'ks2' => 'Key Stage 2',
			'ks3' => 'Key Stage 3',
			'ks4' => 'Key Stage 4',
			'ks1+2' => 'Key Stages 1 + 2',
			'ks3+4' => 'Key Stages 3 + 4',
			'other' => 'Other'
		);

		if ($return_map === TRUE) {
			return $map;
		}

		if (array_key_exists($key, $map)) {
			return $map[$key];
		}

		return NULL;
	}

	/**
	 * return list of session groups and keys
	 * @return mixed
	 */
	public function lesson_groups() {
		return $this->format_lesson_group(NULL, TRUE);
	}

	/**
	 * force connection with css
	 * @return mixed
	 */
	public function force_ssl()	{
		$this->CI->config->config['base_url'] = str_replace('http://', 'https://', base_url());

		// check if using ssl
		if (!isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS'])) {
			// if not, redirect
			redirect($this->CI->uri->uri_string(), 'location', 301);
		}
	}

	/**
	 * normalise mobile number to ensure correct format
	 * @param  string $number
	 * @param integer $accountID
	 * @return string
	 */
	public function normalise_mobile($number, $accountID = NULL) {

		$number = $this->check_mobile($number, $accountID);

		// if number begins with 0, replace with country code
		$country_code = localise('country_code', $accountID);
		if (!empty($country_code) && substr($number, 0, 1) === '0') {
			$number = $country_code . substr($number, 1);
		}

		return $number;
	}

	/**
	 * decode characters including quotes
	 * @param  string $string
	 * @return string
	 */
	function htmlspecialchars_decode($string) {
		return htmlspecialchars_decode($string, ENT_QUOTES);
	}

	/**
	 * fix utf decode issue - http://magp.ie/2014/08/13/php-unserialize-string-after-non-utf8-characters-stripped-out/
	 * @param  string $string
	 * @return string
	 */
	function mb_unserialize($string) {

		$string = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matches) {
			if ( isset( $matches[2] ) ) {
				$matches[2] = $this->clean_mb_string($matches[2]);
				return 's:'.strlen($matches[2]).':"'.$matches[2].'";';
			}
			},
			$string
		);
		return unserialize($string);
	}

	/**
	 * clean mb string by replacing characters with readable equivalents
	 * @param  string $string
	 * @return string
	 */
	function clean_mb_string($string) {
		$replacements = array(
			'â€“' => '-',
			'â€¢' => '-',
			'â€™' => "'"
		);
		foreach ($replacements as $find => $replace) {
			$string = str_replace($find, $replace, $string);
		}

		return $string;
	}

	/**
	 * calc session price
	 * @param  int $lessonID
	 * @return mixed
	 */
	function calc_lesson_price($lessonID) {
		// look up
		$where = array(
			'lessonID' => $lessonID
		);

		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $row) {
			$lesson_info = $row;
		}

		// look up booking
		$where = array(
			'bookingID' => $lesson_info->bookingID
		);

		$res = $this->CI->db->from('bookings')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// if event or picking sessions individually, return per session price
		if ($booking_info->type == 'event' || $booking_info->booking_requirement == 'select') {
			return number_format($lesson_info->price, 2, '.', '');
		}

		// if have to book all in block
		if (in_array($booking_info->booking_requirement, array('all', 'remaining'))) {

			// get block info
			$where = array(
				'blockID' => $lesson_info->blockID
			);

			$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();

			if ($res->num_rows() == 0) {
				return FALSE;
			}

			foreach ($res->result() as $row) {
				$block_info = $row;
			}

			// get cancellations
			$cancellations = array();
			$where = array(
				'lessonID' => $lessonID,
				'type' => 'cancellation'
			);

			$res = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$cancellations[] = $row->date;
				}
			}

			$lesson_count = 0;

			// loop through block dates
			$from = strtotime($block_info->startDate);
			$to = strtotime($block_info->endDate);

			// if requirement is remaining sessions only, start from date from today if already started
			if ($booking_info->booking_requirement == 'remaining' && $from < strtotime(date('Y-m-d'))) {
				$from = strtotime(date('Y-m-d'));
			}

			while ($from <= $to) {
				$weekday = strtolower(date("l", $from));
				if ($weekday == $lesson_info->day && !in_array(date("Y-m-d", $from), $cancellations)) {
					$lesson_count++;
				}
				$from += 60*60*24;
			}

			return number_format($lesson_info->price*$lesson_count, 2, '.', '');
		}

		return FALSE;
	}

	/**
	 * get session count
	 * @param  int $lessonID
	 * @return mixed
	 */
	function get_lesson_count($lessonID) {
		// look up
		$where = array(
			'lessonID' => $lessonID
		);

		$res = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $row) {
			$lesson_info = $row;
		}

		// look up booking
		$where = array(
			'bookingID' => $lesson_info->bookingID
		);

		$res = $this->CI->db->from('bookings')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// if event or picking sessions individually, return 1
		if ($booking_info->type == 'event' || $booking_info->booking_requirement == 'select') {
			return 1;
		}

		// if have to book all in block
		if (in_array($booking_info->booking_requirement, array('all', 'remaining'))) {

			// get block info
			$where = array(
				'blockID' => $lesson_info->blockID
			);

			$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();

			if ($res->num_rows() == 0) {
				return FALSE;
			}

			foreach ($res->result() as $row) {
				$block_info = $row;
			}

			// get cancellations
			$cancellations = array();
			$where = array(
				'lessonID' => $lessonID,
				'type' => 'cancellation'
			);

			$res = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$cancellations[] = $row->date;
				}
			}

			$lesson_count = 0;

			// loop through block dates
			$from = strtotime($block_info->startDate);
			$to = strtotime($block_info->endDate);

			// if requirement is remaining sessions only, start from date from today if already started
			if ($booking_info->booking_requirement == 'remaining' && $from < strtotime(date('Y-m-d'))) {
				$from = strtotime(date('Y-m-d'));
			}

			while ($from <= $to) {
				$weekday = strtolower(date("l", $from));
				if ($weekday == $lesson_info->day && !in_array(date("Y-m-d", $from), $cancellations)) {
					$lesson_count++;
				}
				$from += 60*60*24;
			}

			return $lesson_count;
		}

		return FALSE;
	}

	/**
	 * calculate block targets
	 * @param  int $block
	 * @return bool
	 */
	function calc_targets($blockID) {
		// look up block
		$where = array(
			'blockID' => $blockID
		);

		$res = $this->CI->db->from('bookings_blocks')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $row) {
			$block_info = $row;
		}

		// look up booking
		$where = array(
			'bookingID' => $block_info->bookingID
		);

		$res = $this->CI->db->from('bookings')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $row) {
			$booking_info = $row;
		}

		// only calc targets on event of project
		if ($booking_info->project != 1 && $booking_info->type != 'event') {
			return TRUE;
		}

		$targets_set = array();
		$targets_missed = array();

		// check which targets set
		if ($block_info->target_costs > 0) {
			$targets_set[] = 'costs';
		}

		if ($block_info->target_profit > 0) {
			$targets_set[] = 'profit';
		}

		if ($block_info->target_weekly > 0) {
			$targets_set[] = 'weekly';
		}

		if ($block_info->target_total > 0) {
			$targets_set[] = 'total';
		}

		if ($block_info->target_unique > 0) {
			$targets_set[] = 'unique';
		}

		if ($block_info->target_retention > 0 && $block_info->target_retention_weeks > 0) {
			$targets_set[] = 'retention';
		}

		// check if has per session participants targets
		$where = array(
			'blockID' => $blockID,
			'target_participants >' => 0
		);

		$res_check = $this->CI->db->from('bookings_lessons')->where($where)->limit(1)->get();

		if ($res_check->num_rows() > 0) {
			$targets_set[] = 'participants';
		}

		// none set
		if (count($targets_set) == 0) {
			$where = array(
				'blockID' => $blockID
			);
			$data = array(
				'targets_missed' => NULL
			);
			$this->CI->db->update('bookings_blocks', $data, $where, 1);
			return TRUE;
		}

		// costs
		$costs = 0;
		$where = array(
			'bookings_costs.blockID' => $blockID
		);

		$res_check = $this->CI->db->select('SUM(' . $this->CI->db->dbprefix('bookings_costs') . '.amount) as costs')->from('bookings_costs')->where($where)->group_by('bookings_costs.blockID')->limit(1)->get();

		if ($res_check->num_rows() > 0) {
			foreach ($res_check->result() as $cost_info) {
				$costs += floatval($cost_info->costs);
			}
		}

		// get extra time allowance per session type (in hours)
		$extra_role_time = array();
		$where = array(
			'accountID' => $block_info->accountID
		);
		$res = $this->CI->db->from('lesson_types')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($row->extra_time_head > 0) {
					$extra_role_time[$row->typeID]['head'] = $row->extra_time_head/60;
				}
				if ($row->extra_time_lead > 0) {
					$extra_role_time[$row->typeID]['lead'] = $row->extra_time_lead/60;
				}
				if ($row->extra_time_assistant > 0) {
					$extra_role_time[$row->typeID]['assistant'] = $row->extra_time_assistant/60;
				}
			}
		}
		// get cancellations and staff changes
		$lesson_cancellations = array();
		$lesson_changes = array();
		$where = array(
			'bookings_lessons_exceptions.bookingID' => $block_info->bookingID,
			'bookings_lessons_exceptions.accountID' => $block_info->accountID
		);
		$res = $this->CI->db->select('bookings_lessons_exceptions.*,
		 staff.payments_scale_head,
		 staff.payments_scale_assist,
		 staff.payments_scale_lead,
		 staff.payments_scale_participant,
		 staff.payments_scale_observer,
		 lesson_types.hourly_rate')->
		from('bookings_lessons_exceptions')->
		join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'left')->
		join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_exceptions.lessonID', 'left')->
		join('lesson_types', 'lesson_types.typeID = bookings_lessons.lessonID', 'left')->
		where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($row->type == 'cancellation') {
					$lesson_cancellations[$row->lessonID][] = $row->date;
				} else {
					$lesson_changes[$row->lessonID][$row->date][$row->fromID] = array(
						'payments_scale_head' => $row->payments_scale_head,
						'payments_scale_assist' => $row->payments_scale_assist,
						'payments_scale_lead' => $row->payments_scale_lead,
						'payments_scale_participant' => $row->payments_scale_participant,
						'payments_scale_observer' => $row->payments_scale_observer,
					);
				}
			}
		}

		$lesson_staff_change = [];
		$res = $this->CI->db->from('bookings_lessons_exceptions')
			->where([
				'bookingID' => $booking_info->bookingID,
				'accountID' => $this->CI->auth->user->accountID,
				'type'      => 'staffchange',
				'staffID'   => null
			])
			->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_staff_change[$row->lessonID][] = $row->fromID;
			}
		}

		// get staff costs - worked out from hourly rates in staff profile
		$where = array(
			'bookings_lessons.blockID' => $blockID
		);

		$res_check = $this->CI->db->select('bookings_lessons.lessonID, bookings_lessons.day, bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end, bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, bookings_lessons.typeID, bookings_lessons.type_other, lesson_types.name as lesson_type, lesson_types.hourly_rate, bookings_lessons.blockID, bookings_lessons_staff.type,
		bookings_lessons_staff.staffID,
		staff.payments_scale_head,
		staff.payments_scale_assist,
		staff.payments_scale_participant,
		staff.payments_scale_observer,
		staff.payments_scale_lead,
		staff.hourly_rate as staff_rate, staff.system_pay_rates, staff.employment_start_date,
		bookings_lessons_staff.startTime, bookings_lessons_staff.endTime,
		TIMESTAMPDIFF(HOUR, ' . $this->CI->db->dbprefix('bookings_lessons_staff') . '.startTime, ' . $this->CI->db->dbprefix('bookings_lessons_staff') . '.endTime) as hours', FALSE)->
		from('bookings_lessons')->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner')->
		join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->
		join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->
		join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->
		where($where)->get();

		$hours = 0;
		if ($res_check->num_rows() > 0) {
			$this->CI->load->library('reports_library');
			foreach ($res_check->result() as $cost_info) {
				if (isset($lesson_staff_change[$cost_info->lessonID]) && in_array($cost_info->staffID, $lesson_staff_change[$cost_info->lessonID])) {
					continue;
				}

				$start_value = explode(':', $cost_info->startTime);
				$end_value = explode(':', $cost_info->endTime);

				$start_hours = $start_value[0];
				$start_minutes = $start_value[1];

				$end_hours = $end_value[0];
				$end_minutes = $end_value[1];

				$hours_minutes = ($end_hours - $start_hours) * 60;
				$minutes = $end_minutes - $start_minutes;

				$hours = ($hours_minutes + $minutes)/60;
				// determine extra time
				$extra_time = 0;
				if (isset($extra_role_time[$cost_info->typeID][$cost_info->type])) {
					$extra_time = $extra_role_time[$cost_info->typeID][$cost_info->type];
				}

				// determine session type
				$lesson_type = 'Unknown';
				if (!empty($cost_info->lesson_type)) {
					$lesson_type = $cost_info->lesson_type;
				} else if (!empty($cost_info->type_other)) {
					$lesson_type = $cost_info->type_other;
				}

				// get session dates from block
				$start_date = $cost_info->block_start;
				$end_date = $cost_info->block_end;

				// if dates overridden in lesson, use instead
				if (!empty($cost_info->lesson_start)) {
					$start_date = $cost_info->lesson_start;
				}
				if (!empty($cost_info->lesson_end)) {
					$end_date = $cost_info->lesson_end;
				}

				$selected_qual = $this->CI->db->select('mandatory_quals.*')
					->from('mandatory_quals')
					->join('staff_quals_mandatory',
						'mandatory_quals.qualID=staff_quals_mandatory.qualID AND staff_quals_mandatory.preferred_for_pay_rate=1',
						'left')
					->where([
						'mandatory_quals.accountID' => $this->CI->auth->user->accountID,
						'staff_quals_mandatory.staffID' => $cost_info->staffID
					])->limit(1)->get()->result();

				if (!empty($selected_qual)) {
					$selected_qual = $selected_qual[0];
				}

				$session_override_rate = 0;
				if ($cost_info->system_pay_rates && !empty($selected_qual)) {

					$session_rates = $this->CI->db->from('session_qual_rates')
						->where([
							'accountID' => $this->CI->auth->user->accountID,
							'lessionTypeID' => $cost_info->typeID,
							'qualTypeID' => $selected_qual->qualID,
						])->limit(1)->get()->result();

					if (!empty($session_rates)) {
						$session_override_rate = $this->CI->reports_library->get_qualification_rate_by_session($cost_info, $selected_qual, $session_rates[0], $cost_info->type);
					}
				}


				// loop through dates
				while(strtotime($start_date) <= strtotime($end_date)) {
					// check if day matches
					if ($cost_info->day == strtolower(date('l', strtotime($start_date)))) {
						// check if cancelled
						if (isset($lesson_cancellations[$cost_info->lessonID]) && in_array($start_date, $lesson_cancellations[$cost_info->lessonID])) {
							$start_date = date('Y-m-d', strtotime("+1 day", strtotime($start_date)));
							continue;
						}
						// get pay rates from staff
						$payments_scale_head = $cost_info->payments_scale_head;
						$payments_scale_assist = $cost_info->payments_scale_assist;
						$payments_scale_lead = $cost_info->payments_scale_lead;
						$payments_scale_participant = $cost_info->payments_scale_participant;
						$payments_scale_observer = $cost_info->payments_scale_observer;
						// if staff change, use from new staff
						if (isset($lesson_changes[$cost_info->lessonID][$start_date][$cost_info->staffID])) {
							$payments_scale_head = $lesson_changes[$cost_info->lessonID][$start_date][$cost_info->staffID]['payments_scale_head'];
							$payments_scale_assist = $lesson_changes[$cost_info->lessonID][$start_date][$cost_info->staffID]['payments_scale_assist'];
							$payments_scale_lead = $lesson_changes[$cost_info->lessonID][$start_date][$cost_info->staffID]['payments_scale_lead'];
							$payments_scale_participant = $lesson_changes[$cost_info->lessonID][$start_date][$cost_info->staffID]['payments_scale_participant'];
							$payments_scale_observer = $lesson_changes[$cost_info->lessonID][$start_date][$cost_info->staffID]['payments_scale_observer'];
						}

						$hourly_rate = (float)$cost_info->hourly_rate;

						if($hourly_rate > 0)
						{
							$costs += floatval(($hours + $extra_time) * $hourly_rate);
						}
						else {
							if ($session_override_rate > 0) {
								$costs += floatval(($hours + $extra_time) * $session_override_rate);
							} else {
								if(!$cost_info->system_pay_rates &&  (float)$cost_info->staff_rate > 0){
									// for this staff member only hourly_rate is set
									$costs += floatval(($hours + $extra_time) * $cost_info->staff_rate);
								} else {
									if ($cost_info->system_pay_rates && !empty($selected_qual)) {
										$per_hour = $this->CI->reports_library->get_qualification_rate($cost_info, $selected_qual);
										$costs += floatval(($hours + $extra_time) * $per_hour);
									} else {
										{
											$roles = $this->CI->settings_library->get_staff_for_payroll();
											if (isset($roles[$cost_info->type])) {
												$role = $cost_info->type;
												if ($role == 'assistant') {
													$role = 'assist';
												}
												$costs += ${'payments_scale_' . $role};
											} else {
												switch ($cost_info->type)
												{
													case 'head':
													case 'lead':
														$costs += floatval(($hours + $extra_time) * $payments_scale_head);
														break;
													default:
														$costs += floatval(($hours + $extra_time) * $payments_scale_assist);
														break;
												}
											}
										}
									}
								}
							}
						}
					}
					$start_date = date('Y-m-d', strtotime("+1 day", strtotime($start_date)));
				}
			}
		}

		if (in_array('costs', $targets_set) && $costs > $block_info->target_costs) {
			$targets_missed[] = 'Costs';
		}

		// profit
		if (in_array('profit', $targets_set)) {

			// revenue
			$revenue = 0;

			// misc income
			$revenue += floatval($block_info->misc_income);

			// from participants
			switch ($booking_info->register_type) {
				case 'numbers':
					// numbers only register
					$where = array(
						'bookings_lessons.blockID' => $blockID
					);

					$res_check = $this->CI->db->select('SUM(' . $this->CI->db->dbprefix('bookings_attendance_numbers') . '.attended) as participants, bookings_lessons.price')->from('bookings_lessons')->where($where)->join('bookings_attendance_numbers', 'bookings_lessons.lessonID = bookings_attendance_numbers.lessonID', 'inner')->group_by('bookings_lessons.lessonID')->get();

					if ($res_check->num_rows() > 0) {
						foreach ($res_check->result() as $revenue_info) {
							$revenue += floatval($revenue_info->participants * $revenue_info->price);
						}
					}
					break;
				case 'names':
				case 'bikeability':
				case 'shapeup':
					// names only register
					$where = array(
						'bookings_lessons.blockID' => $blockID
					);

					$res_check = $this->CI->db->select('COUNT(' . $this->CI->db->dbprefix('bookings_attendance_names_sessions') . '.participantID) as participants, bookings_lessons.price')->from('bookings_lessons')->where($where)->join('bookings_attendance_names_sessions', 'bookings_lessons.lessonID = bookings_attendance_names_sessions.lessonID', 'inner')->group_by('bookings_lessons.lessonID')->get();

					if ($res_check->num_rows() > 0) {
						foreach ($res_check->result() as $revenue_info) {
							$revenue += floatval($revenue_info->participants * $revenue_info->price);
						}
					}
					break;
				default:
					// normal bookings
					$where = array(
						'bookings_lessons.blockID' => $blockID,
						'bookings_cart.type' => 'booking'
					);

					$res_check = $this->CI->db->select('SUM(' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.total) as revenue')
					->from('bookings_lessons')
					->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
					->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
					->where($where)
					->group_by('bookings_lessons.blockID')
					->limit(1)
					->get();

					if ($res_check->num_rows() > 0) {
						foreach ($res_check->result() as $revenue_info) {
							$revenue = floatval($revenue_info->revenue);
						}
					}
					break;
			}

			// profit
			$profit = $revenue - $costs;

			if ($profit < $block_info->target_profit) {
				$targets_missed[] = 'Profit';
			}
		}

		// weekly
		if ((in_array('weekly', $targets_set) || in_array('retention', $targets_set)) && $booking_info->type != 'event' && $booking_info->register_type != 'numbers') {
			$participants = 0;
			$retained_participants = 0;

			// work out weeks in block
			$block_weeks = floor((strtotime($block_info->endDate) - strtotime($block_info->startDate))/(24*60*60)/7);

			// from participants
			$where = array(
				'bookings_lessons.blockID' => $blockID
			);
			switch ($booking_info->register_type) {
				case 'names':
				case 'bikeability':
				case 'shapeup':
					// names only
					$res_check = $this->CI->db->select('GROUP_CONCAT(' . $this->CI->db->dbprefix('bookings_attendance_names_sessions') . '.date) as dates, bookings_attendance_names_sessions.participantID')
					->from('bookings_lessons')
					->where($where)
					->join('bookings_attendance_names_sessions', 'bookings_lessons.lessonID = bookings_attendance_names_sessions.lessonID', 'inner')
					->group_by('bookings_attendance_names_sessions.participantID')
					->get();
					break;
				case 'children':
				case 'children_bikeability':
				case 'children_shapeup':
					// child bookings
					$where['bookings_cart.type'] = 'booking';
					$res_check = $this->CI->db->select('GROUP_CONCAT(' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.date) as dates, bookings_cart_sessions.childID')
					->from('bookings_lessons')
					->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
					->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
					->where($where)
					->group_by('bookings_cart_sessions.childID')
					->get();
					break;
				case 'individuals':
				case 'individuals_bikeability':
				case 'individuals_shapeup':
					// individual bookings
					$where['bookings_cart.type'] = 'booking';
					$res_check = $this->CI->db->select('GROUP_CONCAT(' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.date) as dates, bookings_cart_sessions.contactID')
					->from('bookings_lessons')
					->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
					->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
					->where($where)
					->group_by('bookings_cart_sessions.contactID')
					->get();
					break;
			}

			if ($res_check->num_rows() > 0) {
				foreach ($res_check->result() as $row) {
					$dates = explode(",", $row->dates);
					if (count($dates) > 0) {
						$weeks = array();
						for ($i=1; $i <= $block_weeks; $i++) {
							$weeks[$i] = $i;
						}
						foreach ($dates as $date) {
							$weeks_in = intval(ceil((strtotime($date) - strtotime($block_info->startDate))/(24*60*60)/7));
							if (array_key_exists($weeks_in, $weeks)) {
								unset($weeks[$weeks_in]);
							}
						}
						if (in_array('weekly', $targets_set) && count($weeks) == 0) {
							$participants++;
						}
						if (in_array('retention', $targets_set) && count($weeks) <= ($block_weeks - intval($block_info->target_retention_weeks))) {
							$retained_participants++;
						}
					}
				}
			}

			if (in_array('weekly', $targets_set) && $participants < $block_info->target_weekly) {
				$targets_missed[] = 'Weekly ' . $this->CI->settings_library->get_label('participants');
			}

			if (in_array('retention', $targets_set) && $retained_participants < $block_info->target_retention) {
				$targets_missed[] = 'Retention';
			}
		}

		// total
		if (in_array('total', $targets_set)) {
			$participants = 0;

			// from participants
			switch ($booking_info->register_type) {
				case 'numbers':
					// numbers only register
					$where = array(
						'blockID' => $blockID
					);

					$res_check = $this->CI->db->select('SUM(' . $this->CI->db->dbprefix('bookings_attendance_numbers') . '.attended) as participants')->from('bookings_attendance_numbers')->where($where)->group_by('blockID')->get();

					if ($res_check->num_rows() > 0) {
						foreach ($res_check->result() as $row) {
							$participants = intval($row->participants);
						}
					}
					break;
				case 'names':
				case 'bikeability':
				case 'shapeup':
					// names only register
					$where = array(
						'blockID' => $blockID
					);

					$res_check = $this->CI->db->from('bookings_attendance_names_sessions')->where($where)->get();

					$participants = intval($res_check->num_rows());
					break;
				default:
					// normal bookings
					$where = array(
						'bookings_lessons.blockID' => $blockID,
						'bookings_cart.type' => 'booking'
					);

					$res_check = $this->CI->db->select('COUNT(' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.sessionID) as participants')
					->from('bookings_lessons')
					->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
					->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
					->where($where)
					->group_by('bookings_lessons.blockID')
					->limit(1)
					->get();

					if ($res_check->num_rows() > 0) {
						foreach ($res_check->result() as $row) {
							$participants = intval($row->participants);
						}
					}
					break;
			}

			if ($participants < $block_info->target_total) {
				$targets_missed[] = $this->CI->settings_library->get_label('participant') . ' Sessions';
			}
		}

		// unique
		if (in_array('unique', $targets_set)) {
			$participants = 0;

			// from participants
			switch ($booking_info->register_type) {
				case 'numbers':
					// numbers only, skip
					break;
				case 'names':
				case 'bikeability':
				case 'shapeup':
					// names only register
					$where = array(
						'blockID' => $blockID
					);

					$res_check = $this->CI->db->from('bookings_attendance_names')->where($where)->get();

					$participants = intval($res_check->num_rows());
					break;
				case 'children':
				case 'children_bikeability':
				case 'children_shapeup':
					// child bookings
					$where = array(
						'bookings_lessons.blockID' => $blockID,
						'bookings_cart.type' => 'booking'
					);

					$res_check = $this->CI->db->select('COUNT(DISTINCT ' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.childID) as participants')
					->from('bookings_lessons')
					->where($where)
					->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
					->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
					->group_by('bookings_lessons.blockID')
					->limit(1)
					->get();

					if ($res_check->num_rows() > 0) {
						foreach ($res_check->result() as $row) {
							$participants = floatval($row->participants);
						}
					}
					break;
				case 'individuals':
				case 'individuals_bikeability':
				case 'individuals_shapeup':
					// individual bookings
					$where = array(
						'bookings_lessons.blockID' => $blockID,
						'bookings_cart.type' => 'bookings'
					);

					$res_check = $this->CI->db->select('COUNT(DISTINCT ' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.contactID) as participants')
					->from('bookings_lessons')
					->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
					->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
					->group_by('bookings_lessons.blockID')
					->where($where)
					->limit(1)
					->get();

					if ($res_check->num_rows() > 0) {
						foreach ($res_check->result() as $row) {
							$participants = floatval($row->participants);
						}
					}
					break;
			}

			if ($participants < $block_info->target_unique) {
				$targets_missed[] = 'Unique ' . $this->CI->settings_library->get_label('participants');
			}
		}

		// participants - per lesson
		if (in_array('participants', $targets_set)) {
			$where = array(
				'bookings_lessons.blockID' => $blockID
			);
			switch ($booking_info->register_type) {
				case 'numbers':
					// numbers only
					$res_check = $this->CI->db->from('bookings_lessons')->join('bookings_attendance_numbers', 'bookings_lessons.lessonID = bookings_attendance_numbers.lessonID', 'left')->where($where)->group_by('bookings_lessons.lessonID')->having('SUM(' . $this->CI->db->dbprefix('bookings_attendance_numbers') . '.attended) < '. $this->CI->db->dbprefix('bookings_lessons') . '.target_participants')->get();
					break;
				case 'names':
				case 'bikeability':
				case 'shapeup':
					// names only
					$res_check = $this->CI->db->from('bookings_lessons')->join('bookings_attendance_names_sessions', 'bookings_lessons.lessonID = bookings_attendance_names_sessions.lessonID', 'left')->where($where)->group_by('bookings_lessons.lessonID')->having('COUNT(' . $this->CI->db->dbprefix('bookings_attendance_names_sessions') . '.lessonID) < '. $this->CI->db->dbprefix('bookings_lessons') . '.target_participants')->get();
					break;
				default:
					// normal registers
					$where['bookings_cart.type'] = 'booking';
					$res_check = $this->CI->db->from('bookings_lessons')
					->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'left')
					->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'left')
					->where($where)
					->group_by('bookings_lessons.lessonID')
					->having('COUNT(' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.lessonID) < '. $this->CI->db->dbprefix('bookings_lessons') . '.target_participants')
					->get();
					break;
				}

			if ($res_check->num_rows() > 0) {
				$targets_missed[] = 'Session ' . $this->CI->settings_library->get_label('participants');
			}
		}

		// update in db
		$where = array(
			'blockID' => $blockID
		);

		$data = array(
			'targets_missed' => 'none'
		);

		if (count($targets_missed) > 0) {
			sort($targets_missed);
			$data['targets_missed'] = implode(', ', $targets_missed);
		}

		$this->CI->db->update('bookings_blocks', $data, $where, 1);

		return TRUE;
	}

	/**
	 * determine assets urls
	 * @param  string $path
	 * @return string
	 */
	public function asset_url($path) {
		// if no versions loaded, load from bust.json if exists
		if (count($this->asset_versions) == 0) {
			$bust_json = APPPATH . '../public/dist/bust.json';
			if (file_exists($bust_json)) {
				$this->asset_versions = json_decode(file_get_contents($bust_json), TRUE);
			}
		}

		// check if path should be versioned
		if (array_key_exists($path, $this->asset_versions)) {
			$path .= '?' . $this->asset_versions[$path];
		}

		// if using aws, check domain is set for cloudfront
		if (AWS && !isset($_SERVER['DISABLE_CLOUDFRONT']) || (isset($_SERVER['DISABLE_CLOUDFRONT']) && $_SERVER['DISABLE_CLOUDFRONT'] != 1)) {
			$cloudfront_config = $this->CI->config->item('cloudfront', 'aws');
			if (array_key_exists('domain', $cloudfront_config) && !empty($cloudfront_config['domain'])) {
				return $cloudfront_config['domain'] . $path;
			}
		}

		// if not, return normal address
		return base_url($path);
	}

	/**
	 * generate unique api key
	 * @return string
	 */
	public function generate_api_key() {
		$unique = FALSE;
		while ($unique == FALSE) {
			$api_key = random_string('alnum', 32);
			$where = array(
				'api_key' => $api_key
			);
			$res = $this->CI->db->from('accounts')->where($where)->limit(1)->get();
			if ($res->num_rows() == 0) {
				return $api_key;
			}
		}
	}

	/**
	 * generate unique feed key
	 * @return string
	 */
	public function generate_feed_key() {
		$unique = FALSE;
		while ($unique == FALSE) {
			$feed_key = random_string('alnum', 32);
			$where = array(
				'feed_key' => $feed_key
			);
			$res = $this->CI->db->from('staff')->where($where)->limit(1)->get();
			if ($res->num_rows() == 0) {
				return $feed_key;
			}
		}
	}

	/**
	 * get session before passed in date or time for a certain staff member
	 * @param string $date
	 * @param string $time
	 * @param integer $staffID
	 * @return mixed
	 */
	public function get_prev_lesson($date, $time, $staffID) {
		return $this->get_next_or_prev_lesson('prev', $date, $time, $staffID);
	}

	/**
	 * get session after passed in date or time for a certain staff member
	 * @param string $date
	 * @param string $time
	 * @param integer $staffID
	 * @return mixed
	 */
	public function get_next_lesson($date, $time, $staffID) {
		return $this->get_next_or_prev_lesson('next', $date, $time, $staffID);
	}

	/**
	 * get session before or after a passed in date or time for a certain staff member
	 * @param string $type prev or next
	 * @param string $date
	 * @param string $time
	 * @param integer $staffID
	 * @return mixed
	 */
	public function get_next_or_prev_lesson($type = NULL, $date = NULL, $time = NULL, $staffID = NULL) {
		// if any params missing, stop
		if (empty($type) || empty($date) || empty($time) || empty($staffID)) {
			return FALSE;
		}

		// if invalid date, stop
		if (!check_mysql_date($date)) {
			return FALSE;
		}

		$matches = array();

		$where = array(
			'bookings_lessons.day' => date('l', strtotime($date)),
			'bookings_lessons_staff.startDate <=' => $date,
			'bookings_lessons_staff.endDate >=' => $date,
			'bookings_lessons_staff.staffID' => $staffID,
			'bookings_lessons.accountID' => $this->CI->auth->user->accountID
		);

		switch ($type) {
			case 'prev':
				$where['bookings_lessons.endTime <='] = $time;
				$order_by = 'bookings_lessons.endTime DESC';
				$key_field = 'endTime';
				break;
			case 'next':
				$where['bookings_lessons.startTime >='] = $time;
				$order_by = 'bookings_lessons.startTime ASC';
				$key_field = 'startTime';
				break;
		}

		// get next/prev
		$res_check = $this->CI->db->select('bookings.type, bookings_lessons.lessonID, bookings_lessons_staff.startTime, bookings_lessons_staff.endTime, orgs_addresses.postcode, event_address.postcode as event_postcode')->from('bookings_lessons')->join('bookings', 'bookings.bookingID = bookings_lessons.bookingID', 'inner')->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID')->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')->join('orgs_addresses as event_address', 'bookings.addressID = event_address.addressID', 'left')->where($where)->order_by($order_by)->get();

		if ($res_check->num_rows() > 0) {
			foreach ($res_check->result() as $row_check) {
				// check for cancelled session exception
				$where = array(
					'lessonID' => $row_check->lessonID,
					'date' => $date,
					'type' => 'cancellation',
					'accountID' => $this->CI->auth->user->accountID,
				);
				$res_cancellation_check = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

				// check for staff change exception
				$where['type'] = 'staffchange';
				$where['fromID'] = $staffID;
				$res_staffchange_check = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->limit(1)->get();

				// if no exceptions, add to matches
				if ($res_cancellation_check->num_rows() == 0 && $res_staffchange_check->num_rows() == 0) {
					$key = strtotime($row_check->$key_field) . '.' . $row_check->lessonID;
					$matches[$key] = $row_check;
				}
			}
		}

		// check to see if added on to another session on this date
		$where = array(
			'bookings_lessons_exceptions.date' => $date,
			'bookings_lessons_exceptions.type' => 'staffchange',
			'bookings_lessons_exceptions.staffID' => $staffID,
			'bookings_lessons_exceptions.accountID' => $this->CI->auth->user->accountID
		);
		switch ($type) {
			case 'prev':
				$where['bookings_lessons.endTime <='] = $time;
				break;
			case 'next':
				$where['bookings_lessons.startTime >='] = $time;
				break;
		}
		$res_check = $this->CI->db->select('bookings.type, bookings_lessons.lessonID, bookings_lessons_staff.startTime, bookings_lessons_staff.endTime, orgs_addresses.postcode, event_address.postcode as event_postcode')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings', 'bookings.bookingID = bookings_lessons.bookingID', 'inner')->join('bookings_lessons_staff', 'bookings_lessons_exceptions.lessonID = bookings_lessons_staff.lessonID and bookings_lessons_exceptions.fromID = bookings_lessons_staff.staffID', 'inner')->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')->join('orgs_addresses as event_address', 'bookings.addressID = event_address.addressID', 'left')->where($where)->get();

		if ($res_check->num_rows() > 0) {
			foreach ($res_check->result() as $row_check) {
				$key = strtotime($row_check->$key_field) . '.' . $row_check->lessonID;
				$matches[$key] = $row_check;
			}
		}

		if (count($matches) == 0) {
			return FALSE;
		}

		switch ($type) {
			case 'prev':
				krsort($matches);
				break;
			case 'next':
				ksort($matches);
				break;
		}

		// return first match
		foreach ($matches as $match) {
			if ($match->type == 'event') {
				$match->postcode = $match->event_postcode;
			}
			unset($match->event_postcode);
			return $match;
		}
	}

	/**
	 * get timesheet item before passed in date or time for a certain timesheet
	 * @param string $date
	 * @param string $time
	 * @param integer $timesheetID
	 * @return mixed
	 */
	public function get_prev_timesheet_item($date, $time, $timesheetID) {
		return $this->get_next_or_prev_timesheet_item('prev', $date, $time, $timesheetID);
	}

	/**
	 * get timesheet item after passed in date or time for a certain timesheet
	 * @param string $date
	 * @param string $time
	 * @param integer $timesheetID
	 * @return mixed
	 */
	public function get_next_timesheet_item($date, $time, $timesheetID) {
		return $this->get_next_or_prev_timesheet_item('next', $date, $time, $timesheetID);
	}

	/**
	 * get timesheet item before or after a passed in date or time for a certain timesheet
	 * @param string $type prev or next
	 * @param string $date
	 * @param string $time
	 * @param integer $timesheetID
	 * @return mixed
	 */
	public function get_next_or_prev_timesheet_item($type = NULL, $date = NULL, $time = NULL, $timesheetID = NULL) {
		// if any params missing, stop
		if (empty($type) || empty($date) || empty($time) || empty($timesheetID)) {
			return FALSE;
		}

		// if invalid date, stop
		if (!check_mysql_date($date)) {
			return FALSE;
		}

		$matches = array();

		$where = array(
			'timesheets_items.date <=' => $date,
			'timesheets_items.timesheetID' => $timesheetID,
			'timesheets_items.accountID' => $this->CI->auth->user->accountID
		);

		switch ($type) {
			case 'prev':
				$where['timesheets_items.end_time <='] = $time;
				$order_by = 'timesheets_items.end_time DESC';
				$key_field = 'end_time';
				break;
			case 'next':
				$where['timesheets_items.start_time >='] = $time;
				$order_by = 'timesheets_items.start_time ASC';
				$key_field = 'start_time';
				break;
		}

		// get next/prev
		$res_check = $this->CI->db->select('timesheets_items.itemID, timesheets_items.lessonID, bookings.type, timesheets_items.start_time, timesheets_items.end_time, orgs_addresses.postcode, event_address.postcode as event_postcode, nonlesson_address.postcode as nonlesson_postcode')->from('timesheets_items')->join('bookings_lessons', 'timesheets_items.lessonID = bookings_lessons.lessonID', 'left')->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'left')->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')->join('orgs_addresses as event_address', 'bookings.addressID = event_address.addressID', 'left')->join('orgs_addresses as nonlesson_address', 'timesheets_items.orgID = nonlesson_address.orgID and nonlesson_address.type = \'main\'', 'left')->where($where)->order_by($order_by)->group_by('timesheets_items.itemID')->get();

		if ($res_check->num_rows() > 0) {
			foreach ($res_check->result() as $row_check) {
				$key = strtotime($row_check->$key_field) . '.' . $row_check->itemID;
				$matches[$key] = $row_check;
			}
		}

		if (count($matches) == 0) {
			return FALSE;
		}

		switch ($type) {
			case 'prev':
				krsort($matches);
				break;
			case 'next':
				ksort($matches);
				break;
		}

		// return first match
		foreach ($matches as $match) {
			if (empty($match->lessonID)) {
				$match->postcode = $match->nonlesson_postcode;
			} else if ($match->type == 'event') {
				$match->postcode = $match->event_postcode;
			}
			unset($match->event_postcode);
			unset($match->nonlesson_postcode);
			return $match;
		}
	}

	/**
	 * generate timesheets for a specific date
	 * @param $date
	 * @return integer number of timesheets created
	 */
	public function generate_timesheets($date = NULL, $accountID = NULL) {

		// if not date, use today's date
		if (empty($date)) {
			$date = date('Y-m-d');
		}

		// if no account ID, get from session
		if (empty($accountID)) {
			$accountID = $this->CI->auth->user->accountID;
		}

		// if date or account ID missing, return
		if (empty($date) || empty($accountID)) {
			return FALSE;
		}

		// get first and last date of week
		$dt = new DateTime($date);
		if ($dt->format('N') == 1) {
			$date_from = $date;
		} else {
			$dt->modify('Last Monday');
			$date_from = $dt->format('Y-m-d');
		}
		$dt->modify('Next Sunday');
		$date_to = $dt->format('Y-m-d');
		$timesheets_created = 0;

		// map days to dates
		$day_map = array();
		$date = $date_from;
		while (strtotime($date) <= strtotime($date_to)) {
			$day = strtolower(date('l', strtotime($date)));
			$day_map[$day] = $date;
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}

		// get extra time and exclude mileage for sessions
		$extra_role_time = array();
		$exclude_mileage_session = array();
		$where = array(
			'accountID' => $accountID
		);
		$res = $this->CI->db->from('lesson_types')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($row->extra_time_head > 0) {
					$extra_role_time[$row->typeID]['head'] = $row->extra_time_head*60;
				}
				if ($row->extra_time_lead > 0) {
					$extra_role_time[$row->typeID]['lead'] = $row->extra_time_lead*60;
				}
				if ($row->extra_time_assistant > 0) {
					$extra_role_time[$row->typeID]['assistant'] = $row->extra_time_assistant*60;
				}
				$exclude_mileage_session[$row->typeID] = $row->exclude_mileage_session;
			}
		}

		// get session exceptions
		$lesson_exceptions = array();
		$lesson_exceptions_where = array(
			'date <=' => $date_to,
			'date >=' => $date_from,
			'accountID' => $accountID
		);
		$res_exceptions = $this->CI->db->from('bookings_lessons_exceptions')->where($lesson_exceptions_where)->get();

		if ($res_exceptions->num_rows() > 0) {
			foreach ($res_exceptions->result() as $row) {
				$lesson_exceptions[$row->accountID][$row->lessonID][$row->date][] = array(
					'fromID' => $row->fromID,
					'staffID' => $row->staffID,
					'type' => $row->type
				);
			}
		}

		// get sessions + staff
		$booking_lessons = array();
		$where = array(
			$this->CI->db->dbprefix('bookings') . '.accountID' => $accountID,
			$this->CI->db->dbprefix('bookings') . '.cancelled !=' => 1,
			$this->CI->db->dbprefix('bookings_blocks') . '.provisional !=' => 1,
			$this->CI->db->dbprefix('bookings_blocks') . '.startDate <=' => $date_to,
			$this->CI->db->dbprefix('bookings_blocks') . '.endDate >=' => $date_from
		);

		$res_lessons = $this->CI->db->select('bookings.accountID, bookings.orgID, bookings.brandID, bookings.type, bookings_blocks.startDate as blockStart, bookings_blocks.endDate as blockEnd, bookings_lessons.startDate as lessonStart, bookings_lessons.endDate as lessonEnd, bookings_lessons.startTime as lessonStartTime, bookings_lessons.endTime as lessonEndTime, bookings_lessons.activityID, bookings_lessons.activityID, bookings_lessons_staff.startDate as staffStart, bookings_lessons_staff.endDate as staffEnd, bookings_lessons_staff.startTime as staffStartTime, bookings_lessons_staff.endTime as staffEndTime, bookings_lessons.lessonID, bookings_lessons.day, bookings_lessons_staff.staffID, bookings_lessons_staff.type as role, bookings_lessons.typeID, bookings_blocks.orgID as block_orgID, bookings_lessons_staff.salaried, bookings_lessons.addressID as lesson_addressID, bookings.addressID as event_addressID')

		->from('bookings_blocks')
		->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
		->join('bookings_lessons', 'bookings_blocks.blockID = bookings_lessons.blockID', 'inner')
		->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner')
		->where($where)
		->order_by('staffStartTime')
		->get();

		if ($res_lessons->num_rows() > 0) {
			foreach ($res_lessons->result() as $row) {
				// get session date
				$lesson_date = $day_map[$row->day];

				// check exceptions
				if (isset($lesson_exceptions[$row->accountID][$row->lessonID][$lesson_date]) && count($lesson_exceptions[$row->accountID][$row->lessonID][$lesson_date]) > 0) {
					foreach ($lesson_exceptions[$row->accountID][$row->lessonID][$lesson_date] as $exception_info)  {
						// process
						switch ($exception_info['type']) {
							case 'cancellation':
								continue 3;
								break;
							case 'staffchange':
								if ($exception_info['fromID'] == $row->staffID) {
									$row->staffID = $exception_info['staffID'];
								}
								break;
						}
					}
				}

				// get start date
				$start_date = $row->blockStart;
				if (!empty($row->lessonStart)) {
					$start_date = $row->lessonStart;
				}

				// get end date
				$end_date = $row->blockEnd;
				if (!empty($row->lessonEnd)) {
					$end_date = $row->lessonEnd;
				}

				// check staff date overrides
				if (!empty($row->staffStart) && strtotime($row->staffStart) >= strtotime($start_date) && strtotime($row->staffStart) <= strtotime($end_date)) {
					$start_date = $row->staffStart;
				}
				if (!empty($row->staffEnd) && strtotime($row->staffEnd) >= strtotime($start_date) && strtotime($row->staffEnd) <= strtotime($end_date) && strtotime($row->staffEnd) >= strtotime($start_date)) {
					$end_date = $row->staffEnd;
				}

				// check date is within the above, if not skip
				if (strtotime($lesson_date) < strtotime($start_date) || strtotime($lesson_date) > strtotime($end_date)) {
					continue;
				}

				// get start time
				$start_time = $row->lessonStartTime;
				if (!empty($row->staffStartTime)) {
					$start_time = $row->staffStartTime;
				}
				// get end time
				$end_time = $row->lessonEndTime;
				if (!empty($row->staffEndTime)) {
					$end_time = $row->staffEndTime;
				}

				// get extra time
				$extra_time = 0;
				if (isset($extra_role_time[$row->typeID][$row->role])) {
					$extra_time = $extra_role_time[$row->typeID][$row->role];
				}

				// if org ID different on block, use that
				if (!empty($row->block_orgID) && $row->block_orgID !== $row->orgID) {
					$row->orgID = $row->block_orgID;
				}

				$addressID = $row->event_addressID;
				if($row->type == 'booking')
					$addressID = $row->lesson_addressID;

				$booking_lessons[$row->accountID][$row->staffID][$row->lessonID] = array(
					'date' => $lesson_date,
					'start_time' => $start_time,
					'end_time' => $end_time,
					'extra_time' => $extra_time,
					'orgID' => $row->orgID,
					'addressID' => $addressID,
					'brandID' => $row->brandID,
					'activityID' => $row->activityID,
					'typeID' => $row->typeID,
					'role' => $row->role,
					'salaried' => intval($row->salaried)
				);
			}
		}

		$where = array(
			'accountID' => $accountID
		);

		$where_in = array("mileage_default_start_location",
		"mileage_default_postcode",
		"mileage_activate_fuel_cards",
		"mileage_default_mode_of_transport");

		$default_start_location = '';
		$mileage_default_postcode = '';
		$mileage_default_mode_of_transport = '';
		$mileage_activate_fuel_cards = '';
		$mileage_activate_fuel_cards = '';
		$query = $this->CI->db->select("*")->from("accounts_settings")->where($where)->where_in("key", $where_in)->get();
		foreach($query->result() as $row){
			if($row->key == 'mileage_default_start_location')
				$default_start_location = $row->value;
			else if($row->key == 'mileage_default_postcode')
				$mileage_default_postcode = $row->value;
			else if($row->key == 'mileage_activate_fuel_cards')
				$mileage_activate_fuel_cards = $row->value;
			else if($row->key == 'mileage_default_mode_of_transport')
				$mileage_default_mode_of_transport = $row->value;
		}

		// Mileage Section Accounts Setting
		$mileage_section = $this->CI->auth->has_features('mileage');

		// Mileage Price Table
		$mileage_price = array();
		$res = $this->CI->db->select("*")->from("mileage")->where("accountID", $accountID)->get();
		foreach($res->result() as $result){
			$mileage_price[$result->mileageID] = $result->rate;
		}

		// look up accounts and staff with no timesheet for this week
		$where = array(
			'timesheets.timesheetID' => NULL,
			'accounts.active' => 1,
			'accounts.accountID' => $accountID
		);

		$res = $this->CI->db->select('accounts.accountID, staff.staffID, staff.department, staff.active, staff.default_start_location, staff.mileage_activate_fuel_cards, staff.mileage_default_mode_of_transport, staff.activate_mileage')->from('accounts')->join('accounts_plans', 'accounts.planID = accounts_plans.planID', 'inner')->join('staff', 'accounts.accountID = staff.accountID', 'inner')->join('timesheets', 'staff.staffID = timesheets.staffID AND timesheets.date = ' . $this->CI->db->escape($date_from), 'left')->where($where)->where("(accounts.addon_timesheets = 1 OR accounts_plans.addons_all = 1)")->get();


		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$timesheet_items = array();
				if (isset($booking_lessons[$row->accountID][$row->staffID]) && count($booking_lessons[$row->accountID][$row->staffID]) > 0) {
					foreach ($booking_lessons[$row->accountID][$row->staffID] as $lessonID => $lesson_details) {
						$timesheet_items[$lessonID] = $lesson_details;
					}
				}

				// if some sessions or is head or full time coach and active, generate timesheet
				if (count($timesheet_items) > 0 || ($row->active == 1 && in_array($row->department, array('headcoach', 'fulltimecoach')))) {
					// insert timesheet
					$data = array(
						'accountID' => $row->accountID,
						'staffID' => $row->staffID,
						'date' => $date_from,
						'total_time' => 0,
						'created' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$this->CI->db->insert('timesheets', $data);
					$timesheetID = $this->CI->db->insert_id();
					$timesheet_data = array();
					$timesheet_data_check = array();
					$timesheet_mileage = array();
					$total_time = 0;
					if (count($timesheet_items) > 0) {
						foreach ($timesheet_items as $lessonID => $lesson_details) {
							$lesson_length = strtotime($lesson_details['end_time']) - strtotime($lesson_details['start_time']) + $lesson_details['extra_time'];
							$total_time += $lesson_length;
							$timesheet_data[] = array(
								'accountID' => $row->accountID,
								'timesheetID' => $timesheetID,
								'orgID' => $lesson_details['orgID'],
								'brandID' => $lesson_details['brandID'],
								'activityID' => $lesson_details['activityID'],
								'lessonID' => $lessonID,
								'date' => $lesson_details['date'],
								'start_time' => $lesson_details['start_time'],
								'end_time' => $lesson_details['end_time'],
								'extra_time' => seconds_to_time($lesson_details['extra_time']),
								'total_time' => seconds_to_time($lesson_length),
								'role' => $lesson_details['role'],
								'salaried' => $lesson_details['salaried'],
								'created' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
						}

						$this->CI->db->insert_batch('timesheets_items', $timesheet_data);
					}

					// update timesheet with actual length
					$data = array(
						'total_time' => seconds_to_time($total_time)
					);
					$where = array(
						'timesheetID' => $timesheetID
					);
					$res = $this->CI->db->update('timesheets', $data, $where, 1);

					$timesheets_created++;

					$where = array(
					"accountID" => $accountID,
					"staffID" => $row->staffID,
					"type" => 'main'
					);
					$staff_postcode = '';
					$query = $this->CI->db->select("*")->from("staff_addresses")->where($where)->get();
					foreach($query->result() as $result){
						$staff_postcode = $result->postcode;
					}

					if($row->default_start_location != null && $row->default_start_location != ""){
						if($row->default_start_location == 'staff_main_address'){
							$postcode = $staff_postcode;
						}else{
							$postcode = $mileage_default_postcode;
						}
					}else{
						if($default_start_location == 'staff_main_address'){
							$postcode = $staff_postcode;
						}else{
							$postcode = $mileage_default_postcode;
						}
					}
					if($row->mileage_default_mode_of_transport != "" && $row->mileage_default_mode_of_transport !=  0)
						$mileage_default_mode_of_transport = $row->mileage_default_mode_of_transport;

					if($mileage_section == 1 && $row->activate_mileage == 1){
						$this->CI->load->helper('crm_helper');
						if (count($timesheet_items) > 0) {
							$count = 0;
							$date = '';
							// $p used for connecting via journey
							$p = '';
							$dateA = array();
							foreach ($timesheet_items as $lessonID => $lesson_details) {
								if(isset($exclude_mileage_session[$lesson_details["typeID"]]) && $exclude_mileage_session[$lesson_details["typeID"]] != 1){
									if(isset($dateA[$lesson_details['date']])){
										$dateA[$lesson_details['date']]++;
									}else{
										$dateA[$lesson_details['date']] = 1;
									}
									$timesheet_data_check[$lesson_details['date']][$lessonID] = $lesson_details;
								}
							}

							foreach ($timesheet_data_check as $lesson_date => $lesson_dates) {
								foreach($lesson_dates as $lessonID => $lesson_details){
									$session_postcode = '';
									$where = array("accountID" => $accountID, "addressID" => $lesson_details['addressID']);
									$query = $this->CI->db->select("*")->from("orgs_addresses")->where($where)->get();
									foreach($query->result() as $result){
										$session_postcode = $result->postcode;
									}
									$count++;
									$start_location_postcode = $postcode;
									if($lesson_details['date'] == $date){
										$start_location_postcode = $p;
									}
									$distance = $cost = 0;
									if($start_location_postcode != $session_postcode){
										if(!empty($start_location_postcode) && !empty($session_postcode)){
											$param = geocode_mileage($start_location_postcode, $session_postcode);
											if($param->status == 'OK'){
												if(isset($param->rows[0]->elements[0]->distance->text)){
													$distance = TRIM($param->rows[0]->elements[0]->distance->text," km") * 0.621371;
													$cost = ($distance * $mileage_price[$mileage_default_mode_of_transport])/100;
												}
											}
										}
										$timesheet_mileage[] = array(
											'accountID' => $row->accountID,
											'timesheetID' => $timesheetID,
											'lessonID' => $lessonID,
											'start_location' => $start_location_postcode,
											'session_location' => $session_postcode,
											'mode' => $mileage_default_mode_of_transport,
											'total_mileage' => $distance,
											'total_cost' => $cost,
											'date' => $lesson_details['date'],
											'role' => $lesson_details['role'],
											'created' => mdate('%Y-%m-%d %H:%i:%s'),
											'modified' => mdate('%Y-%m-%d %H:%i:%s')
										);
									}
									$date = $lesson_details['date'];
									$role = $lesson_details['role'];
									$p = $session_postcode;

									if($dateA[$lesson_details['date']] == $count){
										$distance = $cost = 0;
										$count = 0;
										if(!empty($p) && !empty($postcode)){
											$param = geocode_mileage($p, $postcode);
											if($param->status == 'OK'){
												if(isset($param->rows[0]->elements[0]->distance->text)){
													$distance = TRIM($param->rows[0]->elements[0]->distance->text," km") * 0.621371;
													$cost = ($distance * $mileage_price[$mileage_default_mode_of_transport])/100;
												}
											}
											$timesheet_mileage[] = array(
												'accountID' => $row->accountID,
												'timesheetID' => $timesheetID,
												'lessonID' => $lessonID,
												'start_location' => $p,
												'session_location' => $postcode,
												'mode' => $mileage_default_mode_of_transport,
												'total_mileage' => $distance,
												'total_cost' => $cost,
												'date' => $lesson_details['date'],
												'role' => $lesson_details['role'],
												'created' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s')
											);
										}
									}
								}
							}
							if(count($timesheet_mileage) > 0)
								$this->CI->db->insert_batch('timesheets_mileage', $timesheet_mileage);
						}

						// Add Row in Fuel Card Mileage
						if (count($timesheet_items) > 0 && $mileage_activate_fuel_cards == 1 && $row->mileage_activate_fuel_cards == 1) {

							$previous_week_date = date("Y-m-d",strtotime("-7 day ".$date_from));
							$where = array("timesheets.date" => $previous_week_date,
							"timesheets.accountID" => $row->accountID,
							"timesheets.staffID" => $row->staffID);

							$query = $this->CI->db->select("timesheets_fuel_card.*")
							->from("timesheets")
							->join("timesheets_fuel_card"," timesheets_fuel_card.timesheetID = timesheets.timesheetID", "left")
							->where($where)->get();

							$end_mileage = $start_mileage = 0;

							if($query->num_rows() > 0){
								foreach($query->result() as $result){
									if($result->end_mileage != null)
										$start_mileage = $result->end_mileage;
								}
							}

							$timesheet_fuel_mileage[] = array(
								'accountID' => $row->accountID,
								'timesheetID' => $timesheetID,
								'start_mileage' => $start_mileage,
								'end_mileage' => $end_mileage,
								'created' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);

							$this->CI->db->insert_batch('timesheets_fuel_card', $timesheet_fuel_mileage);

						}
					}

				}

			}
		}

		return $timesheets_created;

	}

	/**
	 * notify staff of new sessions
	 * @param $lesson_staff_ids array
	 * @return boolean
	 */
	public function notify_staff_new_sessions($lesson_staff_ids = array()) {
		// check params
		if (!is_array($lesson_staff_ids) || count($lesson_staff_ids) == 0) {
			return FALSE;
		}

		// get list of session staff and their lessons
		$where = array(
			'bookings_lessons_staff.accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->select('bookings_lessons_staff.*, staff.first as staff_first, staff.email as staff_email, bookings_lessons.day, bookings_lessons.activity_other, activities.name as activity, orgs.name as booking_org, blocks_orgs.name as block_org')->from('bookings_lessons_staff')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('orgs as blocks_orgs', 'bookings_blocks.orgID = blocks_orgs.orgID', 'left')->where($where)->where_in('bookings_lessons_staff.recordID', $lesson_staff_ids)->group_by('bookings_lessons_staff.recordID')->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		$lessons = array();

		foreach ($res->result() as $row) {
			// work out first session date for link
			$dt = new DateTime($row->startDate);
			if (strtolower(date('l', strtotime($row->startDate))) !== $row->day) {
				$dt->modify('next ' . $row->day);
			}
			// session details
			$lesson = array(
				'staff_first' => $row->staff_first,
				'staff_email' => $row->staff_email,
				'type' => $this->CI->settings_library->get_staffing_type_label($row->type),
				'dates' => mysql_to_uk_date($row->startDate),
				'times' => substr($row->startTime, 0, 5) . '-' . substr($row->endTime, 0, 5),
				'day' => $row->day,
				'activity' => $row->activity,
				'venue' => $row->booking_org,
				'link' => site_url('coach/session/' . $row->lessonID . '/' . $dt->format('Y-m-d'))
			);
			if ($row->startDate != $row->endDate) {
				$lesson['dates'].= '-' . mysql_to_uk_date($row->endDate);
			}
			if (empty($row->activity)) {
				$lesson['activity'] = $row->activity_other;
			}
			if (!empty($row->block_org)) {
				$lesson['venue'] = $row->block_org;
			}
			$key = $row->startDate . $row->endDate . date('N', strtotime($row->day)) . $row->lessonID;
			$lessons[$row->staffID][$key] = $lesson;
		}

		if (count($lessons) == 0) {
			return FALSE;
		}

		foreach ($lessons as $staffID => $staff_lessons) {
			// sort by date
			ksort($staff_lessons);

			$staff_email = NULL;

			// smart tags
			$smart_tags = array(
				'staff_first' => NULL, // set within loop
				'timetable_link' => site_url('timetable'),
				'details' => '<p><em>No details available</em></p>'
			);

			// get sessions
			if (count($staff_lessons) > 0) {
				$smart_tags['details'] = '<table width="100%" border="1">
					<tr>
						<th scope="col">Date(s)</th>
						<th scope="col">Day</th>
						<th scope="col">Times</th>
						<th scope="col">Activity</th>
						<th scope="col">Venue</th>
						<th scope="col">Details</th>
					</tr>';
				foreach ($staff_lessons as $lesson) {
					$smart_tags['staff_first'] = $lesson['staff_first'];
					$staff_email = $lesson['staff_email'];

					$smart_tags['details'] .= '<tr>
						<td>' . $lesson['dates'] . '</td>
						<td>' . ucwords($lesson['day']) . '</td>
						<td>' . $lesson['times'] . '</td>
						<td>' . $lesson['activity'] . '</td>
						<td>' . $lesson['venue'] . '</td>
						<td><a href="' . $lesson['link'] . '">View</a></td>
					</tr>';
				}
				$smart_tags['details'] .= '</table>';
			}

			// get email template
			$subject = $this->CI->settings_library->get('email_staff_new_sessions_subject', $this->CI->auth->user->accountID);
			$email_html = $this->CI->settings_library->get('email_staff_new_sessions', $this->CI->auth->user->accountID);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
			}

			// get html email and convert to plain text
			$this->CI->load->helper('html2text');
			$html2text = new \Html2Text\Html2Text($email_html);
			$email_plain = $html2text->get_text();

			// send email
			if (!$this->send_email($staff_email, $subject, $email_html, array(), TRUE, $this->CI->auth->user->accountID)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * notify staff of new exceptions
	 * @param $lesson_staff_ids array
	 * @return boolean
	 */
	public function notify_staff_new_exceptions($exception_ids = array()) {
		// check params
		if (!is_array($exception_ids) || count($exception_ids) == 0) {
			return FALSE;
		}

		// get list of session staff and their lessons
		$where = array(
			'bookings_lessons_exceptions.accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->select('bookings_lessons_exceptions.*, staff.first as staff_first, staff.email as staff_email, bookings_lessons.day, bookings_lessons.startTime, bookings_lessons.endTime, bookings_lessons.activity_other, activities.name as activity, orgs.name as booking_org, blocks_orgs.name as block_org')->from('bookings_lessons_exceptions')->join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'left')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('orgs as blocks_orgs', 'bookings_blocks.orgID = blocks_orgs.orgID', 'left')->where($where)->where_in('bookings_lessons_exceptions.exceptionID', $exception_ids)->group_by('bookings_lessons_exceptions.exceptionID')->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		$lessons = array();

		foreach ($res->result() as $row) {
			$lesson = array(
				'staff_first' => $row->staff_first,
				'staff_email' => $row->staff_email,
				'type' => $this->CI->settings_library->get_staffing_type_label($row->type),
				'dates' => mysql_to_uk_date($row->date),
				'times' => substr($row->startTime, 0, 5) . '-' . substr($row->endTime, 0, 5),
				'day' => $row->day,
				'activity' => $row->activity,
				'venue' => $row->booking_org,
				'link' => site_url('coach/session/' . $row->lessonID . '/' . $row->date)
			);
			if (empty($row->activity)) {
				$lesson['activity'] = $row->activity_other;
			}
			if (!empty($row->block_org)) {
				$lesson['venue'] = $row->block_org;
			}
			$key = $row->date . date('N', strtotime($row->day)) . $row->lessonID;

			switch ($row->type) {
				case 'staffchange':
					$lessons[$row->staffID][$row->type][$key] = $lesson;
					break;
				case 'cancellation':
					// get all staff set to teach this session on this day
					$where = array(
						'bookings_lessons_staff.accountID' => $this->CI->auth->user->accountID,
						'bookings_lessons_staff.lessonID' => $row->lessonID,
						'bookings_lessons_staff.startDate <=' => $row->date,
						'bookings_lessons_staff.endDate >=' => $row->date
					);
					$res_staff = $this->CI->db->select('staff.staffID, staff.first as staff_first, staff.email as staff_email')->from('bookings_lessons_staff')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->where($where)->group_by('bookings_lessons_staff.staffID')->get();

					if ($res_staff->num_rows() > 0) {
						foreach ($res_staff->result() as $row_staff) {
							$lesson['staff_first'] = $row_staff->staff_first;
							$lesson['staff_email'] = $row_staff->staff_email;
							$lessons[$row_staff->staffID][$row->type][$key] = $lesson;
						}
					}
					break;
			}
		}

		if (count($lessons) == 0) {
			return FALSE;
		}

		foreach ($lessons as $staffID => $types) {
			foreach ($types as $typeID => $staff_lessons) {
				// sort by date
				ksort($staff_lessons);

				$staff_email = NULL;

				// smart tags
				$smart_tags = array(
					'staff_first' => NULL, // set within loop
					'timetable_link' => site_url('timetable'),
					'details' => '<p><em>No details available</em></p>'
				);

				// get sessions
				if (count($staff_lessons) > 0) {
					$smart_tags['details'] = '<table width="100%" border="1">
						<tr>
							<th scope="col">Date(s)</th>
							<th scope="col">Day</th>
							<th scope="col">Times</th>
							<th scope="col">Activity</th>
							<th scope="col">Venue</th>';
							if ($row->type != 'cancellation') {
								$smart_tags['details'] .= '<td scope="col">Details</td>';
							}
						$smart_tags['details'] .= '</tr>';
					foreach ($staff_lessons as $lesson) {
						$smart_tags['staff_first'] = $lesson['staff_first'];
						$staff_email = $lesson['staff_email'];

						$smart_tags['details'] .= '<tr>
							<td>' . $lesson['dates'] . '</td>
							<td>' . ucwords($lesson['day']) . '</td>
							<td>' . $lesson['times'] . '</td>
							<td>' . $lesson['activity'] . '</td>
							<td>' . $lesson['venue'] . '</td>';
							if ($row->type != 'cancellation') {
								$smart_tags['details'] .= '<td><a href="' . $lesson['link'] . '">View</a></td>';
							}
						$smart_tags['details'] .= '</tr>';
					}
					$smart_tags['details'] .= '</table>';
				}

				// get email template
				$template = 'email_staff_new_sessions';
				if ($row->type == 'cancellation') {
					$template = 'email_staff_cancelled_sessions';
				}
				$subject = $this->CI->settings_library->get($template . '_subject', $this->CI->auth->user->accountID);
				$email_html = $this->CI->settings_library->get($template, $this->CI->auth->user->accountID);

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
				}

				// replace smart tags in subject
				foreach ($smart_tags as $key => $value) {
					$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
				}

				// get html email and convert to plain text
				$this->CI->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				// send email
				if (!$this->send_email($staff_email, $subject, $email_html, array(), TRUE, $this->CI->auth->user->accountID)) {
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	/**
	 * notify staff of changed sessions
	 * @param $lesson_staff_ids array
	 * @param $prev_times array
	 * @return boolean
	 */
	public function notify_staff_changed_sessions($lesson_staff_ids = array(), $prev_times = array()) {

		// check params
		if (!is_array($lesson_staff_ids) || count($lesson_staff_ids) == 0 || count($prev_times) == 0) {
			return FALSE;
		}

		// get list of session staff and their lessons
		$where = array(
			'bookings_lessons_staff.accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->select('bookings_lessons_staff.*, staff.first as staff_first, staff.email as staff_email, bookings_lessons.day, bookings_lessons.activity_other, activities.name as activity, orgs.name as booking_org, blocks_orgs.name as block_org')->from('bookings_lessons_staff')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('orgs as blocks_orgs', 'bookings_blocks.orgID = blocks_orgs.orgID', 'left')->where($where)->where_in('bookings_lessons_staff.recordID', $lesson_staff_ids)->group_by('bookings_lessons_staff.recordID')->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		$lessons = array();

		foreach ($res->result() as $row) {
			// work out first session date for link
			$dt = new DateTime($row->startDate);
			if (strtolower(date('l', strtotime($row->startDate))) !== $row->day) {
				$dt->modify('next ' . $row->day);
			}
			// session details
			$lesson = array(
				'staff_first' => $row->staff_first,
				'staff_email' => $row->staff_email,
				'type' => $this->CI->settings_library->get_staffing_type_label($row->type),
				'dates' => mysql_to_uk_date($row->startDate),
				'prev_times' => substr($prev_times[$row->recordID]['start'], 0, 5) . '-' . substr($prev_times[$row->recordID]['end'], 0, 5),
				'times' => substr($row->startTime, 0, 5) . '-' . substr($row->endTime, 0, 5),
				'day' => $row->day,
				'activity' => $row->activity,
				'venue' => $row->booking_org,
				'link' => site_url('coach/session/' . $row->lessonID . '/' . $dt->format('Y-m-d'))
			);
			if ($row->startDate != $row->endDate) {
				$lesson['dates'].= '-' . mysql_to_uk_date($row->endDate);
			}
			if (empty($row->activity)) {
				$lesson['activity'] = $row->activity_other;
			}
			if (!empty($row->block_org)) {
				$lesson['venue'] = $row->block_org;
			}
			$key = $row->startDate . $row->endDate . date('N', strtotime($row->day)) . $row->lessonID;
			$lessons[$row->staffID][$key] = $lesson;
		}

		if (count($lessons) == 0) {
			return FALSE;
		}

		foreach ($lessons as $staffID => $staff_lessons) {
			// sort by date
			ksort($staff_lessons);

			$staff_email = NULL;

			// smart tags
			$smart_tags = array(
				'staff_first' => NULL, // set within loop
				'timetable_link' => site_url('timetable'),
				'details' => '<p><em>No details available</em></p>'
			);

			// get sessions
			if (count($staff_lessons) > 0) {
				$smart_tags['details'] = '<table width="100%" border="1">
					<tr>
						<th scope="col">Date(s)</th>
						<th scope="col">Day</th>
						<th scope="col">Previous Times</th>
						<th scope="col">New Times</th>
						<th scope="col">Activity</th>
						<th scope="col">Venue</th>
						<th scope="col">Details</th>
					</tr>';
				foreach ($staff_lessons as $lesson) {
					$smart_tags['staff_first'] = $lesson['staff_first'];
					$staff_email = $lesson['staff_email'];

					$smart_tags['details'] .= '<tr>
						<td>' . $lesson['dates'] . '</td>
						<td>' . ucwords($lesson['day']) . '</td>
						<td><span style="color:red;text-decoration:line-through;">' . $lesson['prev_times'] . '</span></td>
						<td>' . $lesson['times'] . '</td>
						<td>' . $lesson['activity'] . '</td>
						<td>' . $lesson['venue'] . '</td>
						<td><a href="' . $lesson['link'] . '">View</a></td>
					</tr>';
				}
				$smart_tags['details'] .= '</table>';
			}

			// get email template
			$subject = $this->CI->settings_library->get('email_staff_changed_sessions_subject', $this->CI->auth->user->accountID);
			$email_html = $this->CI->settings_library->get('email_staff_changed_sessions', $this->CI->auth->user->accountID);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
			}

			// get html email and convert to plain text
			$this->CI->load->helper('html2text');
			$html2text = new \Html2Text\Html2Text($email_html);
			$email_plain = $html2text->get_text();

			// send email
			if (!$this->send_email($staff_email, $subject, $email_html, array(), TRUE, $this->CI->auth->user->accountID)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * send participant welcome email
	 * @param  integer $contactID
	 * @return mixed
	 */
	public function send_participant_welcome_email($contactID = NULL, $password = NULL) {

		// check params
		if (empty($contactID) || empty($password)) {
			return FALSE;
		}

		// look up contact
		$where = array(
			'family_contacts.contactID' => $contactID
		);

		$res = $this->CI->db->select('family_contacts.*, accounts.company')->from('family_contacts')->join('accounts', 'family_contacts.accountID = accounts.accountID', 'inner')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info);

		// check if should send
		if ($this->CI->settings_library->get('send_new_participant', $contact_info->accountID) != 1) {
			return FALSE;
		}

		// if no email, skip
		if (empty($contact_info->email)) {
			return FALSE;
		}

		// smart tags
		$smart_tags = array(
			'contact_title' => $contact_info->title,
			'contact_first' => $contact_info->first_name,
			'contact_last' => $contact_info->last_name,
			'contact_email' => $contact_info->email,
			'password' => $password,
			'company' => $contact_info->company
		);

		// get email template
		$subject = $this->CI->settings_library->get('email_new_participant_subject', $contact_info->accountID);
		$email_html = $this->CI->settings_library->get('email_new_participant', $contact_info->accountID);

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		// replace smart tags in subject
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
		}

		if ($this->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $contact_info->accountID)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * send staff welcome email
	 * @param  integer $staffID
	 * @return mixed
	 */
	public function send_staff_welcome_email($staffID = NULL, $password = NULL) {

		// check params
		if (empty($staffID) || empty($password)) {
			return FALSE;
		}

		// look up contact
		$where = array(
			'staff.staffID' => $staffID
		);

		$res = $this->CI->db->select('staff.*, accounts.company')->from('staff')->join('accounts', 'staff.accountID = accounts.accountID', 'inner')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $staff_info);

		// check if should send
		if ($this->CI->settings_library->get('send_new_staff', $staff_info->accountID) != 1) {
			return FALSE;
		}

		// if no email, skip
		if (empty($staff_info->email)) {
			return FALSE;
		}

		// smart tags
		$smart_tags = array( // {staff_first}, {staff_last}, {staff_email}, {password}, {company}, {login_link}, {admins}
			'staff_first' => $staff_info->first,
			'staff_last' => $staff_info->surname,
			'staff_email' => $staff_info->email,
			'password' => $password,
			'company' => $staff_info->company,
			'login_link' => site_url(),
			'admins' => '<em>None</em>'
		);

		// get account admins
		$account_admins = array();
		$where = array(
			'staff.accountID' => $staff_info->accountID,
			'staff.active' => 1,
			'staff.department' => 'directors'
		);

		$res = $this->CI->db->select('staff.*, staff_addresses.phone, staff_addresses.mobile')->from('staff')->join('staff_addresses', 'staff.staffID = staff_addresses.staffID and ' . $this->CI->db->dbprefix('staff_addresses') . '.type = \'main\'', 'left')->where($where)->order_by('staff.first asc, staff.surname asc')->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$account_admins[] = $row->first . ' ' . $row->surname . ' - ' . $row->email;
			}
		}
		$smart_tags['admins'] = implode('<br>', $account_admins);

		// get email template
		$subject = $this->CI->settings_library->get('email_new_staff_subject', $staff_info->accountID);
		$email_html = $this->CI->settings_library->get('email_new_staff', $staff_info->accountID);

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		// replace smart tags in subject
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
		}

		if ($this->send_email($staff_info->email, $subject, $email_html, array(), TRUE, $staff_info->accountID)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * send customer welcome email
	 * @param  integer $contactID
	 * @return mixed
	 */
	public function send_customer_welcome_email($contactID = NULL, $password = NULL) {

		// check params
		if (empty($contactID) || empty($password)) {
			return FALSE;
		}

		// look up contact
		$where = array(
			'orgs_contacts.contactID' => $contactID
		);

		$res = $this->CI->db->select('orgs_contacts.*, accounts.company, orgs.name as org')->from('orgs_contacts')->join('accounts', 'orgs_contacts.accountID = accounts.accountID', 'inner')->join('orgs', 'orgs_contacts.orgID = orgs.orgID', 'inner')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info);

		// check if should send
		if ($this->CI->settings_library->get('send_customer_password', $contact_info->accountID) != 1) {
			return FALSE;
		}

		// if no email, skip
		if (empty($contact_info->email)) {
			return FALSE;
		}

		// smart tags
		$smart_tags = array(
			'contact_name' => $contact_info->name,
			'contact_email' => $contact_info->email,
			'org_name' => $contact_info->org,
			'password' => $password,
			'company' => $contact_info->company
		);

		// get email template
		$subject = $this->CI->settings_library->get('email_customer_password_subject', $contact_info->accountID);
		$email_html = $this->CI->settings_library->get('email_customer_password', $contact_info->accountID);

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		// replace smart tags in subject
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
		}

		if ($this->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $contact_info->accountID)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * force staff to reconfirm policy
	 * @return bool
	 */
	public function reconfirm_staff_privacy() {
		$where = array(
			'accountID' => $this->CI->auth->user->accountID
		);
		$data = array(
			'privacy_agreed' => NULL,
			'privacy_agreed_date' => NULL
		);
		$this->CI->db->update('staff', $data, $where);
		return TRUE;
	}

	/**
	 * force participant to reconfirm policy
	 * @return bool
	 */
	public function reconfirm_participant_privacy() {
		$where = array(
			'accountID' => $this->CI->auth->user->accountID
		);
		$data = array(
			'privacy_agreed' => NULL,
			'privacy_agreed_date' => NULL
		);
		$this->CI->db->update('family_contacts', $data, $where);
		return TRUE;
	}

	/**
	 * force staff and participants to reconfirm policy
	 * @return bool
	 */
	public function reconfirm_company_privacy() {
		$where = array(
			// all accounts
		);
		$data = array(
			'privacy_agreed' => NULL,
			'privacy_agreed_date' => NULL
		);
		$this->CI->db->update('staff', $data, $where);
		$this->CI->db->update('family_contacts', $data, $where);
		return TRUE;
	}

	//check if user checked out from current lesson
	public function if_checked_out($lesson) {
		// get session already checked in for
		$where = array(
			'date' => date('Y-m-d'),
			'staffID' => $this->CI->auth->user->staffID,
			'accountID' => $this->CI->auth->user->accountID,
			'lessonID' => $lesson->lessonID
		);
		$res_staff = $this->CI->db->from('bookings_lessons_checkouts')->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			return true;
		}

		return false;
	}

	public function get_checkin_status() {
		$current = date('Y-m-d');
		$data = [
			'bookings_lessons_checkins.date' => $current,
			'bookings_lessons_checkins.staffID' => $this->CI->auth->user->staffID,
			'bookings_lessons_checkins.accountID' => $this->CI->auth->user->accountID,
			'not_checked_in' => 0
		];

		$query = $this->CI->db->select('bookings_lessons_checkins.*,
			bookings_lessons_staff.startTime,
			bookings_lessons_staff.endTime')
			->from('bookings_lessons_checkins')
			->join('bookings_lessons_staff', 'bookings_lessons_checkins.lessonID = bookings_lessons_staff.lessonID', 'left')
			->where($data)
			->order_by('logID desc')
			->limit(1)
			->get();

		if ($query->num_rows() < 1) {
			return 'not_checked_in';
		}

		$checkin = null;
		foreach ($query->result() as $row) {
			$checkin = $row;
		}

		$minutes = $this->CI->settings_library->get('email_not_checkout_staff_threshold_time', $this->CI->auth->user->accountID);

		//show button 10 more minutes
		$minutes += 10;

		//staff not able to checkout if in thresholdtime + 10 mins
		if (time() - strtotime(date('Y-m-d') . ' ' . $checkin->endTime) > $minutes*60) {
			return 'not_checked_in';
		}

		$data = [
			'bookings_lessons_checkouts.date' => $current,
			'bookings_lessons_checkouts.staffID' => $this->CI->auth->user->staffID,
			'bookings_lessons_checkouts.accountID' => $this->CI->auth->user->accountID,
		];

		$query = $this->CI->db->from('bookings_lessons_checkouts')
			->where($data)
			->order_by('logID desc')
			->limit(1)
			->get();

		if ($query->num_rows() < 1) {
			return 'checked_in';
		}

		$checkout = null;
		foreach ($query->result() as $row) {
			$checkout = $row;
		}

		if ($checkout->lessonID == $checkin->lessonID) {
			if (strtotime($checkout->added) > strtotime($checkin->added)) {
				return 'checked_out';
			}
		}

		return 'checked_in';
	}

	//check if user checked in into session in current date
	public function get_current_checkin_status($lesson) {
		// get session already checked in for
		$where = array(
			'date' => date('Y-m-d'),
			'staffID' => $this->CI->auth->user->staffID,
			'accountID' => $this->CI->auth->user->accountID,
			'lessonID' => $lesson->lessonID,
			'not_checked_in' => 0
		);
		$res_staff = $this->CI->db->from('bookings_lessons_checkins')
			->where($where)
			->order_by('logID desc')
			->limit(1)
			->get();

		if ($res_staff->num_rows() < 1) {
			return 'not_checked_in';
		}

		$checkin_lesson = [];
		foreach ($res_staff->result() as $row) {
			$checkin_lesson = $row;
		}

		$where = array(
			'date' => date('Y-m-d'),
			'staffID' => $this->CI->auth->user->staffID,
			'accountID' => $this->CI->auth->user->accountID,
			'lessonID' => $lesson->lessonID
		);
		$res_staff = $this->CI->db->from('bookings_lessons_checkouts')
			->where($where)
			->order_by('logID desc')
			->limit(1)
			->get();

		if ($res_staff->num_rows() > 0) {
			$checkout_lesson = [];
			foreach ($res_staff->result() as $row) {
				$checkout_lesson = $row;
			}

			if (strtotime($checkout_lesson->added) > strtotime($checkin_lesson->added)) {
				return 'checked_out';
			}
		}

		return 'checked_in';
	}

	public function get_checkins($where = [], $search_where = [], $search_fields = [], $convert_time = true) {
		if ($convert_time) {
			$search_fields['date_from'] = uk_to_mysql_date($search_fields['date_from']);
			$search_fields['date_to'] = uk_to_mysql_date($search_fields['date_to']);
		}

		// run query
		$res = $this->CI->db->select('bookings_lessons_checkins.*, staff.first as staff_first,
		 staff.surname as staff_last, orgs.name as org, block_orgs.name as block_org, bookings_lessons.startTime')
			->from('bookings_lessons_checkins')
			->join('staff', 'bookings_lessons_checkins.staffID = staff.staffID', 'inner')
			->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
			->join('bookings_lessons', 'bookings_lessons_checkins.lessonID = bookings_lessons.lessonID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
			->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
			->where($where)->where($search_where, NULL, FALSE)
			->order_by('bookings_lessons_checkins.added desc')->get();

		// get staffing times
		$staff_times = array();
		$where = array(
			'bookings_lessons_staff.accountID' => $this->CI->auth->user->accountID,
			'bookings_lessons_staff.endDate >=' => $search_fields['date_from'],
			'bookings_lessons_staff.startDate <=' => $search_fields['date_to']
		);
		$res_times = $this->CI->db->select('bookings_lessons_staff.*, bookings_lessons.day, bookings_lessons.startTime as lesson_start')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->get();
		$staff_roles = [];
		if ($res_times->num_rows() > 0) {
			foreach ($res_times->result() as $time) {
				$start_date = $time->startDate;
				$end_date = $time->endDate;
				if (strtotime($search_fields['date_from']) > strtotime($start_date)) {
					$start_date = $search_fields['date_from'];
				}
				if (strtotime($search_fields['date_to']) < strtotime($end_date)) {
					$end_date = $search_fields['date_to'];
				}
				$date = $start_date;
				while (strtotime($date) <= strtotime($end_date)) {
					if (strtolower(date("l", strtotime($date))) == $time->day) {
						$start_time = $time->startTime;
						if (empty($start_time)) {
							$start_time = $time->lesson_start;
						}
						$staff_times[$date][$time->lessonID][$time->staffID] = $start_time;
						break;
					}
					$date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
				}
				$staff_roles[$time->lessonID][$time->staffID] = $time->type;
			}
		}

		// get staff change exceptions
		$where = array(
			'date <=' => $search_fields['date_to'],
			'date >=' => $search_fields['date_from'],
			'accountID' => $this->CI->auth->user->accountID,
			'type' => 'staffchange'
		);
		$res_exceptions = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->get();
		if ($res_exceptions->num_rows() > 0) {
			foreach ($res_exceptions->result() as $exception) {
				// if time for from staff exists
				if (isset($staff_times[$exception->date][$exception->lessonID][$exception->fromID])) {
					// set same time for new staff
					$staff_times[$exception->date][$exception->lessonID][$exception->staffID] = $staff_times[$exception->date][$exception->lessonID][$exception->fromID];
					// unset original
					unset($staff_times[$exception->date][$exception->lessonID][$exception->fromID]);
				}
			}
		}

		// get markers
		$markers = [];
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// check for staffing time, if different to lesson
				if (isset($staff_times[$row->date][$row->lessonID][$row->staffID])) {
					$row->startTime = $staff_times[$row->date][$row->lessonID][$row->staffID];
				}

				$lesson_start = $row->date . ' ' . $row->startTime;

				$marker = array(
					'staff' => $row->staff_first .  ' ' . $row->staff_last,
					'staff_id' => $row->staffID,
					'lesson_id' => $row->lessonID,
					'org' => $row->org,
					'checkin_time' => mysql_to_uk_datetime($row->added),
					'checkin_time_clean' => $row->added,
					'lesson_time' => mysql_to_uk_datetime($lesson_start),
					'lesson_time_clean' => $lesson_start,
					'lat' => $row->lat,
					'lng' => $row->lng,
					'accuracy' => $row->accuracy,
					'colour' => '008000',
					'not_checked_in' => $row->not_checked_in,
					'role' => ''
				);

				if (isset($staff_roles[$row->lessonID][$row->staffID])) {
					$marker['role'] = $staff_roles[$row->lessonID][$row->staffID];
				}

				if (!empty($row->block_org)) {
					$marker['org'] = $row->block_org;
				}
				if (strtotime($row->added) > strtotime($lesson_start)) {
					$marker['colour'] = 'FFBF00';
					$marker['checkin_time'] = '<span class="text-red">' . $marker['checkin_time'] . '</span>';
				}

				$distance = 0;
				$res = $this->CI->db->select('CONCAT_WS(\',\', ST_X(address.location), ST_Y(address.location)) as booking_coords')
					->from('bookings_lessons')
					->join('orgs_addresses as address', 'bookings_lessons.addressID = address.addressID', 'left')
					->where([
						'bookings_lessons.accountID' => $this->CI->auth->user->accountID,
						'bookings_lessons.lessonID' => $row->lessonID
					])
					->get();

				if ($res->num_rows() > 0) {
					$geokit = new Geokit\Math();

					foreach ($res->result() as $item) {
						if (!empty($item->booking_coords)) {
							$coords = explode(",", $item->booking_coords);

							$distance = $geokit->distanceHaversine([
								$row->lat, $row->lng
							], $coords);
							$distance = $distance->meters();
						}
					}
				}

				if ($distance > 1000) {
					$marker['colour'] = 'FF0000';
				}

				if (!isset($markers[$marker['lat'] . '-' . $marker['lng'] . '-' . $row->staff_first .  ' ' . $row->staff_last])) {
					$markers[$marker['lat'] . '-' . $marker['lng'] . '-' . $row->staff_first .  ' ' . $row->staff_last] = $marker;
				}

				$markers[$marker['lat'] . '-' . $marker['lng'] . '-' . $row->staff_first .  ' ' . $row->staff_last]['lesson_ids'][strtotime($marker['lesson_time_clean'])] = $marker['lesson_id'];

				$markers[$marker['lat'] . '-' . $marker['lng'] . '-' . $row->staff_first .  ' ' . $row->staff_last]['orgs'][strtotime($marker['lesson_time_clean'])] = $marker['org'];

				$markers[$marker['lat'] . '-' . $marker['lng'] . '-' . $row->staff_first .  ' ' . $row->staff_last]['lesson_times'][strtotime($marker['lesson_time_clean'])] = $marker['lesson_time'];

				if (!isset($markers[$marker['lat'] . '-' . $marker['lng'] . '-' . $row->staff_first .  ' ' . $row->staff_last]['checkin_times'][strtotime($marker['checkin_time_clean'])])) {
					$markers[$marker['lat'] . '-' . $marker['lng'] . '-' . $row->staff_first .  ' ' . $row->staff_last]['checkin_times'][strtotime($marker['checkin_time_clean'])] = $marker['checkin_time'];
				}
			}
		}

		return $markers;
	}


	// preparing markers for the map
	public function prepare_markers($markers, $checkout_dates = []) {
		foreach ($markers as $key => $value) {
			ksort($markers[$key]['lesson_times']);
			ksort($markers[$key]['checkin_times']);
			ksort($markers[$key]['lesson_ids']);
			ksort($markers[$key]['orgs']);

			if (!isset($markers[$key]['checkout_times'])) {
				$markers[$key]['checkout_times'] = [];
			}

			$where = [
				'staffID' => $value['staff_id']
			];

			if (!empty($checkout_dates)) {
				$where['added >='] = $checkout_dates['date_from'] . ' 00:00:00';
				$where['added <='] = $checkout_dates['date_to'] . ' 23:59:59';
			}

			$res = $this->CI->db->select()
				->from('bookings_lessons_checkouts')
				->where($where)
				->where('lessonID IN (' . implode(',', $value['lesson_ids']) . ')')
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$markers[$key]['checkout_times'][strtotime($row->added)] = date('d/m/Y H:i', strtotime($row->added)) ;
				}
			}

			end($markers[$key]['checkout_times']);
			end($markers[$key]['checkin_times']);

			// if checked out from session
			if (key($markers[$key]['checkout_times']) > key($markers[$key]['checkin_times'])) {
				$markers[$key]['colour'] = '0000FF';
			}

			if ($value['not_checked_in']) {
				$markers[$key]['colour'] = 'FF0000';
			}

			$markers[$key]['lesson_times'] = array_values($markers[$key]['lesson_times']);
			$markers[$key]['checkin_times'] = array_values($markers[$key]['checkin_times']);
			$markers[$key]['checkout_times'] = array_values($markers[$key]['checkout_times']);
			$markers[$key]['orgs'] = array_values($markers[$key]['orgs']);
		}

		return $markers;
	}


	private function get_today_potential_lessons(
		$staffID = NULL,
		$before_start_time = false,
		$use_account = false,
		$skip_past_lessons = false
	) {
		// vars
		$current_date = date('Y-m-d');
		$potential_lessons = array();
		$upcoming_lessons = array();
		$fields = 'bookings_lessons.*, orgs.name as org, block_orgs.name as block_org, orgs_addresses.address1, orgs_addresses.address2,
		 	orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode, bookings_blocks.startDate as block_start,
	  		bookings_blocks.endDate as block_end, bookings_blocks.provisional, bookings_lessons_staff.startDate as staff_start,
		   	bookings_lessons_staff.endDate as staff_end, bookings_lessons_staff.startTime as staff_start_time, bookings_lessons_staff.endTime as staff_end_time,
		    bookings_lessons_staff.staffID, bookings_lessons_staff.checkin_email_sent, bookings_lessons_staff.checkout_email_sent, bookings_lessons_staff.recordID';

		// get session exceptions
		$lesson_exceptions = array();
		$where = array(
			'date' => $current_date
		);

		if ($use_account) {
			$where['accountID'] = $this->CI->auth->user->accountID;
		}

		$res_staff = $this->CI->db->from('bookings_lessons_exceptions')->where($where)->get();

		if ($res_staff->num_rows() > 0) {
			foreach ($res_staff->result() as $row) {
				$lesson_exceptions[$row->lessonID][] = array(
					'fromID' => $row->fromID,
					'staffID' => $row->staffID,
					'type' => $row->type
				);
			}
		}

		// get potential sessions based on today and block dates
		$where = array(
			'bookings_lessons.day' => strtolower(date('l', strtotime($current_date))), // must be same day as today
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d %H:%i:%s')
		);

		if ((int)$staffID > 0) {
			$where['bookings_lessons_staff.staffID'] = $staffID;
		}

		if ($use_account) {
			$where['bookings_lessons.accountID'] = $this->CI->auth->user->accountID;
		}

		$res = $this->CI->db->select($fields)
			->from('bookings_lessons_staff')
			->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
			->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')
			->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$potential_lessons[] = $row;
			}
		}

		// get potential sessions based on today and added by exception
		$where = array(
			'bookings_lessons_exceptions.type' => 'staffchange',
			'bookings_lessons_exceptions.date' => $current_date,
			'bookings_lessons.day' => strtolower(date('l', strtotime($current_date))) // must be same day as today
		);

		if ((int)$staffID > 0) {
			$where['bookings_lessons_exceptions.staffID'] = $staffID;
		}

		if ($use_account) {
			$where['bookings_lessons.accountID'] = $this->CI->auth->user->accountID;
		}

		$res = $this->CI->db->select($fields)->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('bookings_lessons_staff', 'bookings_lessons_exceptions.fromID = bookings_lessons_staff.staffID and bookings_lessons_staff.lessonID = bookings_lessons_exceptions.lessonID', 'inner')->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'inner')->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')->join('orgs as block_orgs', 'bookings_blocks.orgID = block_orgs.orgID', 'left')->where($where)->group_by('bookings_lessons_exceptions.lessonID')->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$potential_lessons[] = $row;
			}
		}

		// if none, return false
		if (count($potential_lessons) == 0) {
			return [];
		}

		// loop sessions to check if match
		foreach ($potential_lessons as $key => $row) {

			// dates from block
			$start_date = $row->block_start;
			$end_date = $row->block_end;

			// check for dates from staff
			if (!empty($row->staff_start)) {
				$start_date = $row->staff_start;
			}
			if (!empty($row->staff_end)) {
				$end_date = $row->staff_end;
			}

			// check for dates from lesson
			if (!empty($row->startDate)) {
				$start_date = $row->startDate;
			}
			if (!empty($row->endDate)) {
				$end_date = $row->endDate;
			}

			// double check session is still on today
			if (strtotime($current_date) >= strtotime($start_date) && strtotime($current_date) <= strtotime($end_date)) {
				// yes, check exceptions
				if (array_key_exists($row->lessonID, $lesson_exceptions)) {
					foreach ($lesson_exceptions[$row->lessonID] as $exception) {
						if ($exception['type'] == 'cancellation') {
							continue 2;
						}

						//if we know login staff we can check it and skip lesson
						if ((int)$staffID > 0) {
							if ($exception['type'] == 'staffchange' && $exception['fromID'] == $staffID) {
								continue 2;
							}
						} else {
							//in other case we need reassign staff for lesson.
							$potential_lessons[$key]->staffID = $exception['staffID'];
						}
					}
				}

				// get times
				$start_time = $row->startTime;
				$end_time = $row->endTime;
				// check for times from staff
				if (!empty($row->staff_start_time)) {
					$start_time = $row->staff_start_time;
				}
				if (!empty($row->staff_end_time)) {
					$end_time = $row->staff_end_time;
				}

				if ($skip_past_lessons) {
					// skip sessions which are past end time
					if (strtotime($current_date . ' ' . $end_time) < time()) {
						continue;
					}
				}

				if ($before_start_time) {
					// only allow 30 minutes before start
					if (strtotime($current_date . ' ' . $start_time) > strtotime('+30 minutes')) {
						continue;
					}
				}

				// all ok, save
				$key = $row->startTime . '-' . $row->endTime . '-' . $row->lessonID;
				if ($staffID > 0) {
					$upcoming_lessons[$key] = $row;
				} else {
					$upcoming_lessons[$key][$row->recordID] = $row;
				}

			}
		}

		return $upcoming_lessons;
	}

	// get current session should be teaching
	public function get_current_lesson($staffID = NULL) {

		// use current staff ID, if none passed in
		if (empty($staffID)) {
			$staffID = $this->CI->auth->user->staffID;
		}

		$upcoming_lessons = $this->get_today_potential_lessons(
			$staffID,
			true,
			true,
			true
		);

		// if none, return false
		if (count($upcoming_lessons) == 0) {
			return FALSE;
		}

		// sort by time
		ksort($upcoming_lessons);

		// else get first
		foreach ($upcoming_lessons as $key => $lesson_info) {
			return $lesson_info;
		}
	}

	/**
	 * get week number from shift patternl
	 * @param  string $date
	 * @return integer
	 */
	public function week_number_from_shift_pattern($date) {
		// get number of weeks in shift pattern
		$shift_pattern_weeks = intval($this->CI->settings_library->get('shift_pattern_weeks'));
		if ($shift_pattern_weeks < 1) {
			$shift_pattern_weeks = 1;
		}

		// if only on 1 week pattern, return week 1
		if ($shift_pattern_weeks == 1){
			return $shift_pattern_weeks;
		}

		// else get number of weeks since start date
		$shift_pattern_start = $this->CI->settings_library->get('shift_pattern_start');

		// make sure is a monday
		$shift_pattern_start = date("Y-m-d",strtotime('monday this week', strtotime($shift_pattern_start)));

		// determine if before or after shift pattern start
		if (strtotime($date) >= strtotime($shift_pattern_start)) {
			// future, get monday of next week
			$date = date("Y-m-d",strtotime('monday next week', strtotime($date)));

			// work out weeks in between
			$weeks_from_start = weeks_between_two_dates($shift_pattern_start, $date);

			// if in first shift period, return weeks from start
			if ($weeks_from_start <= $shift_pattern_weeks) {
				return $weeks_from_start;
			}

			// else work out remainder
			$weeks = fmod($weeks_from_start, $shift_pattern_weeks);

			// if no remainder, must be last week
			if ($weeks == 0) {
				$weeks = $shift_pattern_weeks;
			}

			return $weeks;
		} else {
			// past, get monday of this week
			$date = date("Y-m-d",strtotime('monday this week', strtotime($date)));

			// work out weeks in between
			$weeks_from_start = weeks_between_two_dates($date, $shift_pattern_start);

			// if in first shift period, return weeks from start
			if ($weeks_from_start <= $shift_pattern_weeks) {
				$weeks = $weeks_from_start;
			} else {
				// else work out remainder
				$weeks = fmod($weeks_from_start, $shift_pattern_weeks);

				// if no remainder, must be last week
				if ($weeks == 0) {
					$weeks = $shift_pattern_weeks;
				}

			}

			// flip weeks so counting backwards from shift pattern start
			return $shift_pattern_weeks - $weeks + 1;
		}
	}

	public function get_project_codes() {
		return $this->CI->db->select()
			->from('project_codes')
			->where(['accountID' => $this->CI->auth->user->accountID])
			->get();
	}

	public function set_order($db_prefix, $allowed_fields, $allowed_values, $order, $default_order = '') {
		$result = $default_order;
		if ($order && is_array($order)) {
			if (count(array_diff(array_keys($order), $allowed_fields)) == 0) {
				if (count(array_diff(array_values($order), $allowed_values)) == 0) {
					$result = '';
					$i = 0;
					foreach ($order as $key => $value) {
						$result .= $db_prefix.'.'.$key . ' ' . $value . ',';
						if ($i == count($order) - 1) {
							$result = substr($result, 0, -1);
						}
						$i++;
					}
				}
			}
		}
		return $result;
	}

	public function count_income($booking_info, $week=0, $year=0) {
		$session_income = array();
		$contract_income = array();
		$blocks_income = array();
		$total = 0;

		if($week != NULL && $week != 0){
			$sdate = date("Y-m-d",strtotime($year."W".str_pad($week,2,0,STR_PAD_LEFT)."1"));
			$edate = date("Y-m-d",strtotime($year."W".str_pad($week,2,0,STR_PAD_LEFT)."7"));
		}

		// session income (booking)
		if ($booking_info->type != 'event' && $booking_info->project != 1) {
			// get booking pricing
			$prices = array();
			$prices_contract = array();
			$where = array(
				'bookingID' => $booking_info->bookingID,
				'accountID' => $this->CI->auth->user->accountID
			);
			$res = $this->CI->db->from('bookings_pricing')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$prices[$row->typeID] = $row->amount;
					if ($row->contract == 1) {
						$prices_contract[$row->typeID] = $row->amount;
					}
				}
			}

			// get cancellations
			$lesson_cancellations = array();
			$res = $this->CI->db->from('bookings_lessons_exceptions')
				->where([
					'bookingID' => $booking_info->bookingID,
					'accountID' => $this->CI->auth->user->accountID,
					'type'      => 'cancellation'
				])
				->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$lesson_cancellations[$row->lessonID][] = $row->date;
				}
			}

			$lesson_staff_change = [];
			$res = $this->CI->db->from('bookings_lessons_exceptions')
				->where([
					'bookingID' => $booking_info->bookingID,
					'accountID' => $this->CI->auth->user->accountID,
					'type'      => 'staffchange',
					'staffID'   => null
				])
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$lesson_staff_change[$row->lessonID][] = $row->fromID;
				}
			}

			// get lessons
			$where = array(
				'bookings_lessons.bookingID' => $booking_info->bookingID,
				'bookings_lessons.accountID' => $this->CI->auth->user->accountID
			);

			$res = $this->CI->db
				->select('bookings_lessons.lessonID, bookings_lessons.day,
				 bookings_lessons.startDate as lesson_start,
				 bookings_lessons.endDate as lesson_end,
				 bookings_blocks.startDate as block_start,
				 bookings_blocks.endDate as block_end,
				 bookings_lessons.typeID, bookings_lessons.type_other,
				 lesson_types.name as lesson_type, bookings_lessons.blockID,
				 bookings_lessons.charge, bookings_lessons.charge_other')
				->from('bookings_lessons')
				->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
				->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->where($where)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// determine session type
					$lesson_type = 'Unknown';
					if (!empty($row->lesson_type)) {
						$lesson_type = $row->lesson_type;
					} else if (!empty($row->type_other)) {
						$lesson_type = $row->type_other;
					}

					// get session dates from block
					$start_date = $row->block_start;
					$end_date = $row->block_end;

					// if dates overridden in lesson, use instead
					if (!empty($row->lesson_start)) {
						$start_date = $row->lesson_start;
					}
					if (!empty($row->lesson_end)) {
						$end_date = $row->lesson_end;
					}

					// limit to filter, if set
					if (isset($booking_info->startDate_filter) && strtotime($booking_info->startDate_filter) > strtotime($start_date)) {
						$start_date = $booking_info->startDate_filter;
					}
					if (isset($booking_info->endDate_filter) && strtotime($booking_info->endDate_filter) < strtotime($end_date)) {
						$end_date = $booking_info->endDate_filter;
					}

					// Check if week view available
					if($week != NULL && $week != 0){
						$start_date = $sdate;
						$end_date = $edate;
					}

					// loop through dates
					while(strtotime($start_date) <= strtotime($end_date)) {
						// check if day matches
						if ($row->day == strtolower(date('l', strtotime($start_date)))) {
							// check if cancelled
							if (isset($lesson_cancellations[$row->lessonID]) && in_array($start_date, $lesson_cancellations[$row->lessonID])) {
								$start_date = date('Y-m-d', strtotime("+1 day", strtotime($start_date)));
								continue;
							}
							if (!array_key_exists($row->typeID, $prices_contract)) {
								$charge = 0;
								switch ($row->charge) {
									case 'default':
										if (array_key_exists($row->typeID, $prices) && floatval($prices[$row->typeID]) > 0) {
											$charge = floatval($prices[$row->typeID]);
										}
										break;
									case 'other':
										if (floatval($row->charge_other) > 0) {
											$charge = floatval($row->charge_other);
										}
										break;
								}
								if (!isset($session_income[$lesson_type][$row->blockID])) {
									$session_income[$lesson_type][$row->blockID] = 0;
								}
								if (!isset($blocks_income[$row->blockID])) {
									$blocks_income[$row->blockID] = 0;
								}
								$session_income[$lesson_type][$row->blockID] += $charge;
								$blocks_income[$row->blockID] += $charge;
							} else {
								// contract income
								if (array_key_exists($row->typeID, $prices_contract) && floatval($prices_contract[$row->typeID]) > 0) {
									$contract_income[$lesson_type] = floatval($prices_contract[$row->typeID]);
								}
							}
						}
						$start_date = date('Y-m-d', strtotime("+1 day", strtotime($start_date)));
					}
				}
			}
		} else {
			// session income (project or event)
			switch ($booking_info->register_type) {
				case 'numbers':
					// numbers only register
					$where = array(
						'bookings_lessons.bookingID' => $booking_info->bookingID,
						'bookings_lessons.accountID' => $this->CI->auth->user->accountID
					);

					if($week != 0 && $week != NULL){
						// check for week
						$where['bookings_attendance_numbers.date >='] = $sdate;
						$where['bookings_attendance_numbers.date <='] = $edate;
					}else{
						// limit to filter, if set
						if (isset($booking_info->startDate_filter)) {
							$where['bookings_attendance_numbers.date >='] = $booking_info->startDate_filter;
						}
						if (isset($booking_info->endDate_filter)) {
							$where['bookings_attendance_numbers.date <='] = $booking_info->endDate_filter;
						}
					}

					$res = $this->CI->db->select('bookings_lessons.type_other, lesson_types.name as type, bookings_lessons.blockID,
						bookings_lessons.typeID, SUM(' . $this->CI->db->dbprefix('bookings_attendance_numbers') . '.attended) as participants,
						bookings_lessons.price')
						->from('bookings_lessons')
						->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
						->where($where)
						->join('bookings_attendance_numbers', 'bookings_lessons.lessonID = bookings_attendance_numbers.lessonID', 'inner')
						->group_by('bookings_lessons.lessonID')->get();

					if ($res->num_rows() > 0) {
						foreach ($res->result() as $row) {
							$lesson_type = 'Unknown';
							if (!empty($row->type)) {
								$lesson_type = $row->type;
							} else if (!empty($row->type_other)) {
								$lesson_type = $row->type_other;
							}
							if (!isset($session_income[$lesson_type][$row->blockID])) {
								$session_income[$lesson_type][$row->blockID] = 0;
							}
							if (!isset($blocks_income[$row->blockID])) {
								$blocks_income[$row->blockID] = 0;
							}

							$session_income[$lesson_type][$row->blockID] += floatval($row->participants * $row->price);
							$blocks_income[$row->blockID] += floatval($row->participants * $row->price);
						}
					}
					break;
				case 'names':
				case 'bikeability':
				case 'shapeup':
					// names only register
					$where = array(
						'bookings_lessons.bookingID' => $booking_info->bookingID,
						'bookings_lessons.accountID' => $this->CI->auth->user->accountID
					);

					if($week != 0 && $week != NULL){
						// check for week
						$where['bookings_attendance_names_sessions.date >='] = $sdate;
						$where['bookings_attendance_names_sessions.date <='] = $edate;
					}else{
						// limit to filter, if set
						if (isset($booking_info->startDate_filter)) {
							$where['bookings_attendance_names_sessions.date >='] = $booking_info->startDate_filter;
						}
						if (isset($booking_info->endDate_filter)) {
							$where['bookings_attendance_names_sessions.date <='] = $booking_info->endDate_filter;
						}
					}

					$res = $this->CI->db->select('bookings_lessons.type_other, lesson_types.name as type,
					 bookings_lessons.blockID, bookings_lessons.typeID,
					  COUNT(' . $this->CI->db->dbprefix('bookings_attendance_names_sessions') . '.participantID) as participants,
					   bookings_lessons.price')
						->from('bookings_lessons')
						->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
						->where($where)
						->join('bookings_attendance_names_sessions', 'bookings_lessons.lessonID = bookings_attendance_names_sessions.lessonID', 'inner')
						->group_by('bookings_lessons.lessonID')->get();

					if ($res->num_rows() > 0) {
						foreach ($res->result() as $row) {
							$lesson_type = 'Unknown';
							if (!empty($row->type)) {
								$lesson_type = $row->type;
							} else if (!empty($row->type_other)) {
								$lesson_type = $row->type_other;
							}
							if (!isset($session_income[$lesson_type][$row->blockID])) {
								$session_income[$lesson_type][$row->blockID] = 0;
							}

							if (!isset($blocks_income[$row->blockID])) {
								$blocks_income[$row->blockID] = 0;
							}

							$blocks_income[$row->blockID] += floatval($row->participants * $row->price);
							$session_income[$lesson_type][$row->blockID] += floatval($row->participants * $row->price);
						}
					}
					break;
				default:
					// normal bookings
					$where = array(
						'bookings_lessons.bookingID' => $booking_info->bookingID,
						'bookings_lessons.accountID' => $this->CI->auth->user->accountID,
						'bookings_cart.type' => 'booking'
					);

					if($week != 0 && $week != NULL){
						// check for week
						$where['bookings_cart_sessions.date >='] = $sdate;
						$where['bookings_cart_sessions.date <='] = $edate;
					}else{
						// limit to filter, if set
						if (isset($booking_info->startDate_filter)) {
							$where['bookings_cart_sessions.date >='] = $booking_info->startDate_filter;
						}
						if (isset($booking_info->endDate_filter)) {
							$where['bookings_cart_sessions.date <='] = $booking_info->endDate_filter;
						}
					}

					$res = $this->CI->db
						->select('bookings_lessons.type_other, lesson_types.name as type,
							bookings_lessons.blockID, bookings_lessons.typeID,
							SUM(' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.total) as revenue')
						->from('bookings_lessons')
						->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
						->where($where)
						->join('bookings_cart_sessions', 'bookings_lessons.lessonID = bookings_cart_sessions.lessonID', 'inner')
						->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
						->group_by('bookings_lessons.lessonID')->get();

					if ($res->num_rows() > 0) {
						foreach ($res->result() as $row) {
							$lesson_type = 'Unknown';
							if (!empty($row->type)) {
								$lesson_type = $row->type;
							} else if (!empty($row->type_other)) {
								$lesson_type = $row->type_other;
							}
							if (!isset($session_income[$lesson_type][$row->blockID])) {
								$session_income[$lesson_type][$row->blockID] = 0;
							}
							if (!isset($blocks_income[$row->blockID])) {
								$blocks_income[$row->blockID] = 0;
							}

							$blocks_income[$row->blockID] += floatval($row->revenue);
							$session_income[$lesson_type][$row->blockID] += floatval($row->revenue);
						}
					}
					break;
			}
		}

		$blocks = [];
		$misc_income = [];
		$where = array(
			'bookings_blocks.bookingID' => $booking_info->bookingID,
			'bookings_blocks.accountID' => $this->CI->auth->user->accountID
		);

		if($week != 0 && $week != NULL){
			$where['bookings_blocks.startDate <= '] = $edate;
			$where['bookings_blocks.endDate >= '] = $sdate;
		}

		$res = $this->CI->db->select('bookings_blocks.*, orgs.name as block_org')
			->from('bookings_blocks')
			->join('orgs', 'bookings_blocks.orgID = orgs.orgID', 'left')
			->where($where)->order_by('startDate asc')->get();


		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$blocks[$row->blockID] = $row->name;
				if (!empty($row->orgID) && $row->orgID != $booking_info->orgID) {
					$blocks[$row->blockID] .= ' (' . $row->block_org . ')';
				}
				$misc_income[$row->blockID] = floatval($row->misc_income);
			}
		}

		foreach ($session_income as $item) {
			foreach ($item as $income_value) {
				$total += $income_value;
			}
		}

		foreach ($contract_income as $item) {
			$total += $item;
		}

		foreach ($misc_income as $item) {
			$total += $item;
		}

		return [
			'session_income' => $session_income,
			'contract_income' => $contract_income,
			'blocks_income' => $blocks_income,
			'misc_income' => $misc_income,
			'blocks' => $blocks,
			'total' => $total
		];
	}

	public function count_profit_costs($booking_info, $week=0, $year=0) {

		$this->CI->load->library('reports_library');
		$costs = [];
		$staff_costs = [];
		$total = 0;

		if($week != NULL && $week != 0){
			$sdate = date("Y-m-d",strtotime($year."W".str_pad($week,2,0,STR_PAD_LEFT)."1"));
			$edate = date("Y-m-d",strtotime($year."W".str_pad($week,2,0,STR_PAD_LEFT)."7"));
		}

		// get costs
		$where = array(
			'bookings_blocks.bookingID' => $booking_info->bookingID,
			'bookings_blocks.accountID' => $this->CI->auth->user->accountID
		);

		// check for week
		if($week != 0 && $week != NULL){
			$where['bookings_costs.date <= '] = $edate;
			$where['bookings_costs.date >= '] = $sdate;
		}

		$res = $this->CI->db
			->select('bookings_costs.blockID, bookings_costs.category,
			 SUM(' . $this->CI->db->dbprefix('bookings_costs') . '.amount) as costs')
			->from('bookings_costs')
			->join('bookings_blocks', 'bookings_costs.blockID = bookings_blocks.blockID', 'inner')
			->where($where)
			->group_by('bookings_costs.category, bookings_costs.blockID')
			->order_by('bookings_costs.category asc')->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$costs[$row->category][$row->blockID] = floatval($row->costs);
				$total += floatval($row->costs);
			}
		}

		// get extra time allowance per session type (in hours)
		$extra_role_time = array();
		$where = array(
			'accountID' => $this->CI->auth->user->accountID
		);
		$res = $this->CI->db->from('lesson_types')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($row->extra_time_head > 0) {
					$extra_role_time[$row->typeID]['head'] = $row->extra_time_head/60;
				}
				if ($row->extra_time_lead > 0) {
					$extra_role_time[$row->typeID]['lead'] = $row->extra_time_lead/60;
				}
				if ($row->extra_time_assistant > 0) {
					$extra_role_time[$row->typeID]['assistant'] = $row->extra_time_assistant/60;
				}
			}
		}

		// get cancellations and staff changes
		$lesson_cancellations = array();
		$lesson_cancellations_refund = array();
		$lesson_changes = array();
		$where = array(
			'bookings_lessons_exceptions.bookingID' => $booking_info->bookingID,
			'bookings_lessons_exceptions.accountID' => $this->CI->auth->user->accountID
		);

		// check for week
		if($week != 0 && $week != NULL){
			$where['bookings_lessons_exceptions.date >= '] = $sdate;
			$where['bookings_lessons_exceptions.date <= '] = $edate;
		}

		$res = $this->CI->db->select('bookings_lessons_exceptions.*,
					staff.payments_scale_head,
					staff.payments_scale_assist,
					staff.payments_scale_lead,
					staff.payments_scale_participant,
					staff.payments_scale_observer,
					bookings_lessons_exceptions_refund.amount,
					lesson_types.name as lesson_type,
					bookings_lessons.blockID')
			->from('bookings_lessons_exceptions')
			->join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'left')
			->join('bookings_lessons_exceptions_refund', 'bookings_lessons_exceptions.exceptionID = bookings_lessons_exceptions_refund.exceptionID', 'left')
			->join('bookings_lessons', 'bookings_lessons.lessonID = bookings_lessons_exceptions.lessonID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($row->type == 'cancellation') {
					$lesson_cancellations[$row->lessonID][] = $row->date;
					$lesson_cancellations_refund[$row->lesson_type][$row->blockID] = isset($lesson_cancellations_refund[$row->lesson_type][$row->blockID])?($lesson_cancellations_refund[$row->lesson_type][$row->blockID] + $row->amount):$row->amount;
				} else {
					$lesson_changes[$row->lessonID][$row->date][$row->fromID] = array(
						'payments_scale_head' => $row->payments_scale_head,
						'payments_scale_assist' => $row->payments_scale_assist,
						'payments_scale_lead' => $row->payments_scale_lead,
						'payments_scale_participant' => $row->payments_scale_participant,
						'payments_scale_observer' => $row->payments_scale_observer,
					);
				}
			}
		}

		$lesson_staff_change = [];
		$res = $this->CI->db->from('bookings_lessons_exceptions')
			->where([
				'bookingID' => $booking_info->bookingID,
				'accountID' => $this->CI->auth->user->accountID,
				'type'      => 'staffchange',
				'staffID'   => null
			])
			->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_staff_change[$row->lessonID][] = $row->fromID;
			}
		}

		// get staff costs - worked out from hourly rates in staff profile
		$where = array(
			'bookings_lessons.bookingID' => $booking_info->bookingID,
			'bookings_lessons.accountID' => $this->CI->auth->user->accountID
		);

		$res = $this->CI->db->select('bookings_lessons.lessonID, bookings_lessons.day, bookings_lessons.startDate as lesson_start, ' .
			' bookings_lessons.endDate as lesson_end, bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, ' .
			' bookings_blocks.orgID as booking_org_id, ' .
			'bookings_lessons.typeID, bookings_lessons.type_other, lesson_types.name as lesson_type, lesson_types.hourly_rate, bookings_lessons.blockID, ' .
			'bookings_lessons_staff.staffID, bookings_lessons_staff.type,
			 staff.payments_scale_head,
			 staff.payments_scale_assist,
			 staff.payments_scale_lead,
			 staff.payments_scale_participant,
			 staff.payments_scale_observer, ' .
			'staff.system_pay_rates, staff.employment_start_date, staff.hourly_rate as staff_rate, ' .
			'TIMESTAMPDIFF(HOUR, ' . $this->CI->db->dbprefix('bookings_lessons_staff') . '.startTime, ' .
			$this->CI->db->dbprefix('bookings_lessons_staff') . '.endTime) as hours,
			TIMESTAMPDIFF(HOUR, ' . $this->CI->db->dbprefix('bookings_lessons') . '.startTime, ' .
			$this->CI->db->dbprefix('bookings_lessons') . '.endTime) as lesson_hours,
			bookings_lessons_staff.startDate, bookings_lessons_staff.endDate,
			bookings_lessons_staff.startTime, bookings_lessons_staff.endTime,
			bookings_lessons.startTime as lesson_startTime, bookings_lessons.endTime as lesson_endTime', FALSE)->
		from('bookings_lessons')->
		join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner')->
		join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'inner')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->
		join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->
		where($where)->get();

		$hours = 0;
		$block_hours = [];
		$customer_hours = [];
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (isset($lesson_staff_change[$row->lessonID]) && in_array($row->staffID, $lesson_staff_change[$row->lessonID])) {
					continue;
				}

				// if using old system where didn't put lesson times in bookings_lesson_staff
				if (empty($row->startTime)) {
					$row->startTime = $row->lesson_startTime;
				}
				if (empty($row->endTime)) {
					$row->endTime = $row->lesson_endTime;
				}
				if (empty($row->hours)) {
					$row->hours = $row->lesson_hours;
				}

				$start_value = explode(':', $row->startTime);
				$end_value = explode(':', $row->endTime);

				$start_hours = $start_value[0];
				$start_minutes = $start_value[1];

				$end_hours = $end_value[0];
				$end_minutes = $end_value[1];

				$hours_minutes = ($end_hours - $start_hours) * 60;
				$minutes = $end_minutes - $start_minutes;

				$row_hours = ($hours_minutes + $minutes)/60;
				// determine extra time
				$extra_time = 0;
				if (isset($extra_role_time[$row->typeID][$row->type])) {
					$extra_time = $extra_role_time[$row->typeID][$row->type];
				}
				// determine session type
				$lesson_type = 'Unknown';
				if (!empty($row->lesson_type)) {
					$lesson_type = $row->lesson_type;
				} else if (!empty($row->type_other)) {
					$lesson_type = $row->type_other;
				}

				// get session dates from block
				$start_date = $row->block_start;
				$end_date = $row->block_end;

				// if dates overridden in lesson, use instead
				if (!empty($row->lesson_start)) {
					$start_date = $row->lesson_start;
				}
				if (!empty($row->lesson_end)) {
					$end_date = $row->lesson_end;
				}

				// limit to filter, if set
				if (isset($booking_info->startDate_filter) && strtotime($booking_info->startDate_filter) > strtotime($start_date)) {
					$start_date = $booking_info->startDate_filter;
				}
				if (isset($booking_info->endDate_filter) && strtotime($booking_info->endDate_filter) < strtotime($end_date)) {
					$end_date = $booking_info->endDate_filter;
				}

				$selected_qual = $this->CI->db->select('mandatory_quals.*')
					->from('mandatory_quals')
					->join('staff_quals_mandatory',
						'mandatory_quals.qualID=staff_quals_mandatory.qualID AND staff_quals_mandatory.preferred_for_pay_rate=1',
						'left')
					->where([
						'mandatory_quals.accountID' => $this->CI->auth->user->accountID,
						'staff_quals_mandatory.staffID' => $row->staffID
					])->limit(1)->get()->result();

				if (!empty($selected_qual)) {
					$selected_qual = $selected_qual[0];
				}

				$session_override_rate = 0;
				if ($row->system_pay_rates && !empty($selected_qual)) {

					$session_rates = $this->CI->db->from('session_qual_rates')
						->where([
							'accountID' => $this->CI->auth->user->accountID,
							'lessionTypeID' => $row->typeID,
							'qualTypeID' => $selected_qual->qualID,
						])->limit(1)->get()->result();

					if (!empty($session_rates)) {
						$session_override_rate = $this->CI->reports_library->get_qualification_rate_by_session($row, $selected_qual, $session_rates[0], $row->type);
					}
				}

				if($week != 0 && $week != NULL){
					$start_date = $sdate;
					$end_date = $edate;
				}

				// loop through dates
				while(strtotime($start_date) <= strtotime($end_date)) {
					// check if day matches
					if ($row->day == strtolower(date('l', strtotime($start_date))) && (strtotime($start_date) >= strtotime($row->startDate) && strtotime($start_date) <= strtotime($row->endDate))) {
						// check if cancelled
						if (isset($lesson_cancellations[$row->lessonID]) && in_array($start_date, $lesson_cancellations[$row->lessonID])) {
							$start_date = date('Y-m-d', strtotime("+1 day", strtotime($start_date)));
							continue;
						}
						if (!isset($staff_costs[$lesson_type][$row->blockID])) {
							$staff_costs[$lesson_type][$row->blockID] = 0;
						}

						$hours += ($hours_minutes + $minutes)/60;

						isset($block_hours[$row->blockID]) ? $block_hours[$row->blockID] += ($hours_minutes + $minutes)/60 : $block_hours[$row->blockID] = ($hours_minutes + $minutes)/60;

						if (!empty($row->booking_org_id)) {
							isset($customer_hours[$row->booking_org_id]) ? $customer_hours[$row->booking_org_id] += ($hours_minutes + $minutes)/60 : $customer_hours[$row->booking_org_id] = ($hours_minutes + $minutes)/60;
						} else {
							isset($customer_hours[$booking_info->orgID]) ? $customer_hours[$booking_info->orgID] += ($hours_minutes + $minutes)/60 : $customer_hours[$booking_info->orgID] = ($hours_minutes + $minutes)/60;
						}

						if ((float)$row->hourly_rate)
						{
							$staff_costs[$lesson_type][$row->blockID] += floatval(($row_hours + $extra_time) * $row->hourly_rate);
							$total += floatval(($row_hours + $extra_time) * $row->hourly_rate);

						}
						else
						{
							if ($session_override_rate > 0) {
								$staff_costs[$lesson_type][$row->blockID] += floatval(($row_hours + $extra_time) * $session_override_rate);
								$total += floatval(($row_hours + $extra_time) * $session_override_rate);

							} else {
								if(!$row->system_pay_rates &&  (float)$row->staff_rate > 0){
									// for this staff member only hourly_rate is set
									$staff_costs[$lesson_type][$row->blockID] += floatval(($row_hours + $extra_time) * $row->staff_rate);
									$total += floatval(($row_hours + $extra_time) * $row->staff_rate);

								} else {
									if ($row->system_pay_rates && !empty($selected_qual)) {
										$rate = $this->CI->reports_library->get_qualification_rate($row, $selected_qual);
										$staff_costs[$lesson_type][$row->blockID] += floatval(($row_hours + $extra_time) * $rate);
										$total += floatval(($row_hours + $extra_time) * $rate);

									} else {
										// get pay rates from staff
										$payments_scale_head = $row->payments_scale_head;
										$payments_scale_assist = $row->payments_scale_assist;
										$payments_scale_lead = $row->payments_scale_lead;
										$payments_scale_participant = $row->payments_scale_participant;
										$payments_scale_observer = $row->payments_scale_observer;
										// if staff change, use from new staff
										if (isset($lesson_changes[$row->lessonID][$start_date][$row->staffID])) {
											$payments_scale_head = $lesson_changes[$row->lessonID][$start_date][$row->staffID]['payments_scale_head'];
											$payments_scale_assist = $lesson_changes[$row->lessonID][$start_date][$row->staffID]['payments_scale_assist'];
											$payments_scale_lead = $lesson_changes[$row->lessonID][$start_date][$row->staffID]['payments_scale_lead'];
											$payments_scale_participant = $lesson_changes[$row->lessonID][$start_date][$row->staffID]['payments_scale_participant'];
											$payments_scale_observer = $lesson_changes[$row->lessonID][$start_date][$row->staffID]['payments_scale_observer'];
										}

										$roles = $this->CI->settings_library->get_staff_for_payroll();

										if (isset($roles[$row->type])) {
											$role = $row->type;
											if ($role == 'assistant') {
												$role = 'assist';
											}
											$staff_costs[$lesson_type][$row->blockID] += floatval(($row_hours + $extra_time) * ${'payments_scale_' . $role});
											$total += floatval(($row_hours + $extra_time) * ${'payments_scale_' . $role});
										} else {
											switch ($row->type)
											{
												case 'head':
												case 'lead':
													$staff_costs[$lesson_type][$row->blockID] += floatval(($row_hours + $extra_time) * $payments_scale_head);
													$total += floatval(($row_hours + $extra_time) * $payments_scale_head);
													break;
												default:
													$staff_costs[$lesson_type][$row->blockID] += floatval(($row_hours + $extra_time) * $payments_scale_assist);
													$total += floatval(($row_hours + $extra_time) * $payments_scale_assist);
													break;
											}
										}
									}
								}
							}
						}
					}
					$start_date = date('Y-m-d', strtotime("+1 day", strtotime($start_date)));
				}
			}
		}
		return [
			'staff_costs' => $staff_costs,
			'exception_refund' => $lesson_cancellations_refund,
			'costs' => $costs,
			'total' => $total,
			'hours' => $hours,
			'block_hours' => $block_hours,
			'customer_hours' => $customer_hours,
			'org' => $booking_info->orgID
		];
	}

	public function recalc_family_balance($familyID) {
		// look up family
		$where = array(
			'familyID' => $familyID
		);
		$res = $this->CI->db->from('family')
		->where($where)
		->limit(1)
		->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $row) {
			$family = $row;
		}

		// get booking payments including first subscription payments, but excluding recurring
		$sql = 'SELECT paymentID, amount FROM ' . $this->CI->db->dbprefix("family_payments") .'
		WHERE familyID = '.$familyID.' AND is_first_payment != 0 order by added asc';
		$res = $this->CI->db->query($sql);

		$payments = [];
		if ($res->num_rows()) {
			foreach ($res->result() as $row) {
				$payments[$row->paymentID] = floatval($row->amount);
			}
		}
		$payments_total = array_sum($payments);

		// get bookings total
		$where = array(
			'familyID' => $familyID,
			'type' => 'booking'
		);
		$res = $this->CI->db->select('SUM(total) AS bookings_total')
		->from('bookings_cart')
		->where($where)
		->group_by('familyID')
		->get();
		$bookings_total = 0;
		if ($res->num_rows()) {
			foreach ($res->result() as $row) {
				$bookings_total = floatval($row->bookings_total);
			}
		}

		// reset cart balance for family to total
		$where = array(
			'familyID' => $familyID,
			'type' => 'booking'
		);
		$res = $this->CI->db->from('bookings_cart')
		->where($where)
		->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$data = array(
					'balance' => $row->total
				);
				$where = array(
					'cartID' => $row->cartID
				);
				$this->CI->db->update('bookings_cart', $data, $where, 1);
			}
		}

		// reset cart sessions balance for family to total
		$where = array(
			'bookings_cart.familyID' => $familyID,
			'bookings_cart.type' => 'booking'
		);
		$res = $this->CI->db->select('bookings_cart_sessions.*')
		->from('bookings_cart_sessions')
		->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
		->where($where)
		->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$data = array(
					'balance' => $row->total
				);
				$where = array(
					'cartID' => $row->cartID,
					'sessionID' => $row->sessionID
				);
				$this->CI->db->update('bookings_cart_sessions', $data, $where, 1);
			}
		}

		// remove previous family payment sessions associations
		$where = [
			'familyID' => $familyID,
			'is_sub' => '0'
		];
		$this->CI->db->delete('family_payments_sessions', $where);
		$payments_sessions = [];

		// loop through bookings and find sessions that require payments
		$sessions_to_pay = [];
		$where = array(
			'bookings_cart.familyID' => $familyID,
			'bookings_cart.type' => 'booking',
			'bookings_cart.total >' => 0 // not free
		);
		$res = $this->CI->db
			->select('bookings_cart.cartID, bookings_cart.total as cart_total, SUM(' . $this->CI->db->dbprefix('bookings_cart_sessions') . '.total) AS sessions_total')
			->from('bookings_cart')
			->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
			->where($where)
			->order_by('bookings_cart.booked asc')
			->group_by('bookings_cart.cartID')
			->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $cart) {
				// if sessions total is less than cart total, this booking has subscriptions
				$subscription_booking = FALSE;
				$subscription_total_applied = FALSE;
				if ($cart->cart_total > $cart->sessions_total) {
					$subscription_booking = TRUE;
				}
				// loop sessions in booking
				$where = array(
					'cartID' => $cart->cartID
				);
				// if this is not a subscription booking, limit to those sessions requiring payment
				if ($subscription_booking !== TRUE) {
					$where['total >'] = 0;
				}
				$res_sessions = $this->CI->db->from('bookings_cart_sessions')
					->where($where)
					->order_by('date asc')
					->get();
				if ($res_sessions->num_rows() > 0) {
					foreach ($res_sessions->result() as $session) {
						// if this is a subscription booking and sesson total is 0
						if ($subscription_booking === TRUE && $session->total == 0) {
							// apply difference to first 0 total session
							if ($subscription_total_applied !== TRUE) {
								$session->total = $cart->cart_total - $cart->sessions_total;
								$subscription_total_applied = TRUE;
							} else {
								// skip any other zero total sessions
								continue;
							}
						}
						// no index key as we need to be able to move back/forward through payments to apply refunds
						$sessions_to_pay[] = [
							'sessionID' => $session->sessionID,
							'orig_balance' => $session->total,
							'balance' => $session->total,
							'payments' => []
						];
					}
				}
			}
		}

		// loop payments and apply
		$session_index = 0;
		foreach ($payments as $paymentID => &$payment_amount) {

			// handle payment
			if ($payment_amount > 0) {

				// loop sessions
				while ($session_index < count($sessions_to_pay)) {

					$session = $sessions_to_pay[$session_index];

					$session['balance'] = round($session['balance'], 2);

					$payment_amount = round($payment_amount, 2);

					// balance already paid, move on to next session
					if ($session['balance'] <= 0) {
						$session_index++;

						// can't go above session count
						if ($session_index >= count($sessions_to_pay)) {
							// set to max index
							$session_index = count($sessions_to_pay) - 1;

							// next payment
							continue 2;
						}
						continue;
					}

					// try and apply full amount
					$to_apply = $payment_amount;

					// if payment amount is more than session balance, reduce
					if ($to_apply > $session['balance']) {
						$to_apply = $session['balance'];
					}

					// apply
					$sessions_to_pay[$session_index]['balance'] -= $to_apply;

					// store
					$sessions_to_pay[$session_index]['payments'][$paymentID] = $to_apply;

					// reduce payment amount left
					$payment_amount -= $to_apply;

					// no payment left, move on to next payment
					if ($payment_amount <= 0) {
						continue 2;
					}
				}
			// handle refund
			} else if ($payment_amount < 0) {

				// loop sessions
				while ($session_index < count($sessions_to_pay)) {

					$session = $sessions_to_pay[$session_index];

					// can't refund any more than orig balance, move back to prev session
					if ($session['balance'] >= $session['orig_balance']) {
						// work backwards
						$session_index--;

						// can't go past zero
						if ($session_index < 0) {
							// set index to 0
							$session_index = 0;

							// next payment
							continue 2;
						}
						continue;
					}

					// make positive
					$refund_amount = $payment_amount*-1;

					// try and apply full amount
					$to_apply = $refund_amount;

					// if refund amount is more than session balance, reduce
					$possible_refund = $session['orig_balance'] - $session['balance'];
					if ($to_apply > $possible_refund) {
						$to_apply = $possible_refund;
					}

					// apply
					$sessions_to_pay[$session_index]['balance'] += $to_apply;

					// store
					$sessions_to_pay[$session_index]['payments'][$paymentID] = $to_apply*-1;

					// reduce refund amount left
					$payment_amount += $to_apply;

					// no refund left, move on to next payment
					if ($payment_amount >= 0) {
						continue 2;
					}
				}
			}
		}

		// reorganise array to use sessionID as index for easier lookup below
		$sessions_paid = [];
		foreach ($sessions_to_pay as $session) {
			$sessions_paid[$session['sessionID']] = $session;
		}

		// loop through bookings to save payments (oldest first)
		$where = array(
			'familyID' => $familyID,
			'type' => 'booking',
			'total >' => 0 // not free
		);
		$res = $this->CI->db->from('bookings_cart')
		->where($where)
		->order_by('booked asc')
		->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $cart) {
				$cart_applied = 0;

				// loop sessions to apply payments from above
				$where = array(
					'cartID' => $cart->cartID
				);
				$res_sessions = $this->CI->db->from('bookings_cart_sessions')
					->where($where)
					->order_by('date asc')
					->get();
				if ($res_sessions->num_rows() > 0) {
					foreach ($res_sessions->result() as $session) {

						// get payment data from above
						if (array_key_exists($session->sessionID, $sessions_paid)) {
							$s = $sessions_paid[$session->sessionID];

							$payment_applied = $s['orig_balance'] - $s['balance'];

							// store
							$data = array(
								'balance' => $s['balance']
							);
							$where = array(
								'cartID' => $cart->cartID,
								'sessionID' => $session->sessionID
							);
							$this->CI->db->update('bookings_cart_sessions', $data, $where, 1);

							// track what applied to cart
							$cart_applied += $payment_applied;

							// save payments
							if (count($s['payments']) > 0) {
								foreach ($s['payments'] as $paymentID => $amount) {
									$payments_sessions[] = [
										'accountID' => $session->accountID,
										'familyID' => $familyID,
										'paymentID' => $paymentID,
										'sessionID' => $session->sessionID,
										'amount' => $amount
									];
								}
							}
						}
					}
				}

				// work out cart balance
				$cart_balance = $cart->balance - $cart_applied;

				// update cart balance
				$data = array(
					'balance' => $cart_balance
				);
				$where = array(
					'cartID' => $cart->cartID,
					'familyID' => $familyID
				);
				$this->CI->db->update('bookings_cart', $data, $where, 1);
			}
		}

		// there is an issue with bookings from the older booking systems where the cart total doesn't match the total of the session prices (perhaps due to discounts which no longer exist),so loop through thebookings and any cart totals which are covered by the payments total, make those carts and their sessions as fully paid
		$payments_left = $payments_total;
		$where = array(
			'familyID' => $familyID,
			'type' => 'booking'
		);
		$res = $this->CI->db->from('bookings_cart')
		->where($where)
		->order_by('booked asc')
		->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $cart) {
				if (bccomp($cart->total, $payments_left) <= 0) {
					$data = [
						'balance' => 0
					];
					$where = [
						'cartID' => $cart->cartID
					];

					// set cart to zero balance
					$this->CI->db->update('bookings_cart', $data, $where, 1);
					// set sessions o zero balance
					$this->CI->db->update('bookings_cart_sessions', $data, $where);

					// reduce payment left
					$payments_left -= $cart->total;
				} else {
					// payment left couldn't cover this cart, stop applying payments
					break;
				}
			}
		}

		// recalc balance based on payments minus bookings total
		$account_balance = $payments_total - $bookings_total;

		// update family
		$data = array(
			'account_balance' => $account_balance
		);
		$where = array(
			'familyID' => $familyID
		);
		$this->CI->db->update('family', $data, $where, 1);

		// store payment sessions associations
		if (count($payments_sessions) > 0) {
			$this->CI->db->insert_batch('family_payments_sessions', $payments_sessions);
		}

		return TRUE;
	}

	// get a cart for a contact within the CRM
	public function get_contact_cart() {
		if (!$this->CI->auth->has_features('participants')) {
			return FALSE;
		}

		$cart_contactID = $this->CI->session->userdata('cart_contactID');
		$cart_cartID = $this->CI->session->userdata('cart_cartID');
		if (!empty($cart_contactID)) {
			if (!empty($cart_cartID)) {
				return $this->init_contact_cart($cart_contactID, $cart_cartID);
			}
			return $this->init_contact_cart($cart_contactID);
		}
		return FALSE;
	}

	// init a cart for a contact within the CRM
	public function init_contact_cart($contactID, $cartID = NULL) {
		// check contact exists
		$where = array(
			'contactID' => $contactID,
			'accountID' => $this->CI->auth->user->accountID
		);

		// run query
		$query = $this->CI->db->from('family_contacts')
		->where($where)
		->limit(1)
		->get();

		// no match
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		// update cart library
		$args = $where; // same args as where above
		$args['in_crm'] = TRUE;
		if (!empty($cartID)) {
			$args['cartID'] = $cartID;
		}
		$this->CI->cart_library->init($args);

		return TRUE;
	}

	public function validateArrayEmails($emails) {
		$errors = [];
		if (empty($emails)) {
			return $errors;
		}

		foreach ($emails as $email) {
			if (!empty($email)) {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$errors[] = $email;
				}
			}
		}

		if (empty($errors)) {
			return $errors;
		}

		return [
			'message' => 'Emails address is not valid:',
			'data' => $errors
		];
	}

	/**
	 * send Export data notification
	 * @param  array $staffID
	 * @param  varchar $type
	 * @param  array $filters
	 * @return mixed
	 */
	public function export_data_notification($staffID = NULL, $type = NULL, $filters = NULL){
		if(empty($staffID) || !is_array($staffID))
			return false;

		for($i=0; $i<count($staffID); $i++){

			// look up record for staff
			$where = array(
				'staffID' => $staffID[$i],
				'accountID' => $this->CI->auth->user->accountID
			);

			$res = $this->CI->db->from('staff')->where($where)->limit(1)->get();

			if ($res->num_rows() == 0) {
				return FALSE;
			}

			foreach ($res->result() as $staff_info);

			// smart tags
			$smart_tags = array(
				'contact_first' => $staff_info->first,
				'contact_last' => $staff_info->surname
			);

			// current login
			$where = array(
				'staffID' => $this->CI->auth->user->staffID
			);


			$res = $this->CI->db->from('staff')->where($where)->limit(1)->get();

			foreach ($res->result() as $officer_info);

			$export_details = '<b>Type:</b> '.ucfirst($type)."<br /><br />";
			if(is_array($filters) && count($filters) > 0){
				$export_details .= '<b>Filter:</b> '.implode(", ", $filters)."<br /><br />";
			}
			$export_details .= '<b>Data Officer:</b> '.$officer_info->first." ".$officer_info->surname."<br /><br />";

			$smart_tags['export_details'] = $export_details;

			//subject
			$subject = $this->CI->settings_library->get('data_export_notification_subject');
			$email_html = $this->CI->settings_library->get('data_export_notification_email');

			// replace smart tags
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
				$subject = str_replace('{' . $key . '}', $this->htmlspecialchars_decode($value), $subject);
			}

			//send copy
			$bcc = $this->CI->settings_library->get('send_a_copy_of_data_export_notification_email_to');

			if ($this->send_email($staff_info->email, $subject, $email_html, array(), TRUE, $staff_info->accountID, NULL, $bcc)) {
				return true;
			}else{
				return false;
			}

		}
	}


}
