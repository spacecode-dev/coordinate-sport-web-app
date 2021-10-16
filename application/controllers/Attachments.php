<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attachments extends MY_Controller {

	public function __construct() {
		// public, don't require login
		parent::__construct(TRUE);

		// close session write as not required
		session_write_close();
	}

	public function index($type = NULL, $path = NULL, $action = 'view', $accountID = NULL, $nonce = NULL) {

		// check params
		if (empty($type) || empty($path) || !ctype_alnum(str_replace('_', '', $path))) {
			show_404();
		}

		// if not specific types, check login
		$open_access = array('staff-id', 'event', 'customer', 'setting', 'brand', 'booking-image');
		if (!in_array($type, $open_access) && $this->auth->user == FALSE) {
			// check for nonce if viewing numbers only register
			if ($action == 'view' && $type == 'numbers' && !empty($accountID) && !empty($nonce)) {
				$where = [
					'accountID' => $accountID,
					'numbers_path' => $path,
					'numbers_nonce' => $nonce
				];
				$res = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();
				if ($res->num_rows() == 1) {
					// all ok, add acccess to numbers temporarily
					$open_access[] = 'numbers';
					// remove nonce so can't be accessed again
					$data = [
						'numbers_nonce' => NULL
					];
					$this->db->update('bookings_blocks', $data, $where, 1);
				} else {
					show_404();
				}
			} else {
				show_404();
			}
		}
		// switch type
		switch ($type) {
			case "booking":
				$table = "bookings_lessons_attachments";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "booking-image":
				$table = "bookings_images";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "event":
				$table = "bookings_attachments";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "customer":
				$table = "orgs_attachments";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "customernotification":
				$table = "orgs_notifications_attachments";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "staff":
				$table = "staff_attachments";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "staff_availability_exception":
				$table = "staff_availability_exceptions";
				$field_type = "file_type";
				$field_name = "attachment_name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "staff-id":
				$table = "staff";
				$field_type = "id_photo_type";
				$field_name = "id_photo_name";
				$field_path = "id_photo_path";
				$content_disposition = "inline";
				break;
			case "message":
				$table = "messages_attachments";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "message_template":
				$table = "message_templates_attachments";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "files":
				$table = "files";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "numbers":
				$table = "bookings_blocks";
				$field_type = "type";
				$field_name = "name";
				$field_path = "numbers_path";
				$content_disposition = "inline";
				break;
			case "setting":
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "brand":
				$table = "brands";
				$field_type = "logo_type";
				$field_name = "name";
				$field_path = "logo_path";
				$content_disposition = "inline";
				break;
			case "expense":
				$table = "timesheets_expenses";
				$field_type = "receipt_type";
				$field_name = "receipt_name";
				$field_path = "receipt_path";
				$content_disposition = "inline";
				break;
			case "fuelcard":
				$table = "timesheets_fuel_card";
				$field_type = "receipt_type";
				$field_name = "receipt_name";
				$field_path = "receipt_path";
				$content_disposition = "inline";
				break;
			case "participant":
				$table = "family_contacts";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "participant_child":
				$table = "family_children";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			case "staff_profile_pic":
				$table = "staff";
				$field_type = "type";
				$field_name = "name";
				$field_path = "path";
				$content_disposition = "inline";
				break;
			default:
				show_404();
				break;
		}

		switch ($type) {
			case 'participant_child':
				if (!empty($accountID)) {
					$where = array(
						'childID' => $accountID
					);
					$res = $this->db->from($table)->where($where)->limit(1)->get();

					if ($res->num_rows() == 0) {
						show_404();
					}

					foreach ($res->result() as $row) {
						$image_data = @unserialize($row->profile_pic);
					}
				}
				if ($image_data != FALSE) {
					$attachment_info = new stdClass;
					$attachment_info->$field_path = $image_data['path'];
					$attachment_info->$field_name =  $image_data['name'];
					$attachment_info->$field_type = $image_data['type'];
					$attachment_info->ext = $image_data['ext'];
					$accountID = $this->auth->user->accountID;
				}
				break;
			case 'participant':
				if (!empty($accountID)) {
					$where = array(
						'contactID' => $accountID
					);
					$res = $this->db->from($table)->where($where)->limit(1)->get();

					if ($res->num_rows() == 0) {
						show_404();
					}

					foreach ($res->result() as $row) {
						$image_data = @unserialize($row->profile_pic);
					}
				}
				if ($image_data != FALSE) {
					$attachment_info = new stdClass;
					$attachment_info->$field_path = $image_data['path'];
					$attachment_info->$field_name =  $image_data['name'];
					$attachment_info->$field_type = $image_data['type'];
					$attachment_info->ext = $image_data['ext'];
					$accountID = $this->auth->user->accountID;
				}
				break;
			case 'staff_profile_pic':
				if (!empty($accountID)) {


					$where = array(
						'staffID' => $accountID
					);
					$res = $this->db->from($table)->where($where)->limit(1)->get();

					if ($res->num_rows() == 0) {
						show_404();
					}

					foreach ($res->result() as $row) {
						$image_data = @unserialize($row->profile_pic);
					}
				}

				if ($image_data != FALSE) {
					$attachment_info = new stdClass;
					$attachment_info->$field_path = $image_data['path'];
					$attachment_info->$field_name =  $image_data['name'];
					$attachment_info->$field_type = $image_data['type'];
					$attachment_info->ext = $image_data['ext'];
					$accountID = $this->auth->user->accountID;
				}
				break;
			case 'setting':
				if (!empty($accountID)) {
					// specific account
					$image_data = @unserialize($this->settings_library->get($path, $accountID));
				} else if ($this->auth->user !== FALSE) {
					$image_data = @unserialize($this->settings_library->get($path, $this->auth->user->accountID));
				} else {
					$image_data = @unserialize($this->settings_library->get($path, 'default'));
				}
				if ($image_data != FALSE) {
					$attachment_info = new stdClass;
					$attachment_info->$field_path = $image_data['path'];
					$attachment_info->$field_name =  $image_data['name'];
					$attachment_info->$field_type = $image_data['type'];
					$attachment_info->ext = $image_data['ext'];

					// if image data is same as that of default, using default, therefore access from shared path
					if ($image_data == @unserialize($this->settings_library->get($path, 'default'))) {
						$accountID = 'default';
					}
				}
				break;
			default:
				// look up
				$where = array(
					$field_path => $path
				);

				// if require login, restrict files to account
				if (!in_array($type, $open_access)) {
					if (isset($this->auth->user->accountID)) {
						$where['accountID'] = $this->auth->user->accountID;
					} else {
						// not logged in, no access
						$where['accountID'] = -1;
					}
				}

				if ($type == 'message' && strpos($path, '_') !== false) {
					$pathValues = explode('_', $path);
					$attachmentID = $pathValues[1];

					$where = [
						'attachmentID' => $attachmentID
					];
				}

				$res = $this->db->from($table)->where($where)->limit(1)->get();

				if ($res->num_rows() == 0) {
					show_404();
				}

				foreach ($res->result() as $row) {
					$attachment_info = $row;
				}
				break;
		}

		// check attachment info exists

		if (!isset($attachment_info)) {
			show_404();
		}

		$path = UPLOADPATH . $attachment_info->$field_path;

		if ($type == 'message') {
			$path = 's3://' . AWS_S3_BUCKET . '/' . $attachment_info->accountID . '/' . $attachment_info->path;
		}

		if (!empty($accountID)) {
			if ($accountID == 'default') {
				$path = UPLOADPATH_SHARED . $attachment_info->$field_path;
			} else if (is_numeric($accountID) && (!isset($this->auth->user->accountID) || $accountID != $this->auth->user->accountID)) {
				$path = UPLOADPATH_SHARED . $accountID . '/' . $attachment_info->$field_path;
			}
		}

		// check file exists
		if (!file_exists($path)) {
			show_404();
		}

		switch ($type) {
			case 'numbers':
				$attachment_info->$field_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
				$attachment_info->ext = 'xlsx';
				$attachment_info->$field_name .= '.' . $attachment_info->ext;
				break;
			case 'brand':
				$attachment_info->$field_name .= '.' . $attachment_info->logo_ext;
				break;
		}

		switch ($action) {
			case 'view':
				// show file
				header("Content-type: " . $attachment_info->$field_type);
				header('Content-Disposition: ' . $content_disposition . '; filename="' . $attachment_info->$field_name . '"');
				header("Content-Length: " . filesize($path));
				// if setting or staff id photo, cache
				if (in_array($type, array('setting', 'staff-id'))) {
					$seconds_to_cache = 3600;
					$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
					header("Expires: $ts");
					header("Pragma: cache");
					header("Cache-Control: max-age=$seconds_to_cache");
				}
				readfile($path);
				exit();
				break;
			case 'thumb':
				// show thumb if exists
				$path = $path .= '_thumb';
				if (!file_exists($path)) {
					show_404();
				}
				header("Content-type: " . $attachment_info->$field_type);
				header('Content-Disposition: ' . $content_disposition . '; filename="' . $attachment_info->$field_name . '"');
				header("Content-Length: " . filesize($path));
				// cache
				$seconds_to_cache = 3600;
				$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
				header("Expires: $ts");
				header("Pragma: cache");
				header("Cache-Control: max-age=$seconds_to_cache");
				// output
				readfile($path);
				exit();
				break;
			case 'edit':
				// check extension
				if (!in_array($attachment_info->ext, array("doc", "docx", "xls", "xlsx"))) {
					show_404();
				}

				// account is required
				if (empty($accountID)) {
					show_404();
				}

				// must be logged in
				if ($this->auth->user == FALSE) {
					show_403();
				}

				// coaches and headcoaches cant edit anything (except numbers register)
				if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach')) && $type != 'numbers') {
					show_403();
				}

				// build fields for zoho
				$fields = [];
				if (AWS) {
					if (in_array($type, $open_access)) {
						$fields['url'] = site_url('attachment/' . $type . '/' . $attachment_info->$field_path . '/' . $accountID);
					} else if ($type == 'numbers') {
						// generate nonce and store as zoho sheets doesn't like URLs with query strings
						$nonce = random_string('alnum', 10);
						$where = [
							'accountID' => $accountID,
							'numbers_path' => $attachment_info->$field_path,
						];
						$data = [
							'numbers_nonce' => $nonce
						];
						$this->db->update('bookings_blocks', $data, $where, 1);
						$fields['url'] = site_url('attachment/' . $type . '/' . $attachment_info->$field_path . '/' . $accountID . '/' . $nonce);
					} else {
						show_404();
					}
				} else {
					$fields['document'] = new CURLFile($path, $attachment_info->$field_type, $attachment_info->$field_name);
				}
				$fields['apikey'] = "ac35afd1db575fd0672e820ec7379ca3";
				$fields['document_info'] = json_encode([
					'document_name' => pathinfo($attachment_info->$field_name, PATHINFO_FILENAME)
				]);
				$fields['callback_settings'] = [
					'save_format' => $attachment_info->ext,
					'save_url' => site_url("attachment/save/" . $accountID . "/Ge3esWacUsu6aswEB8vawRachUrumUY3")
				];

				switch ($attachment_info->ext) {
					case "doc":
					case "docx":
					default:
						$url = "https://writer.zoho.com/writer/officeapi/v1/document";
						$fields['callback_settings']['save_url_params'] = [
							'id' => $attachment_info->$field_path
						];
						break;
					case "xls":
					case "xlsx":
						$url = "https://sheet.zoho.com/sheet/officeapi/v1/spreadsheet";
						$fields['id'] = $attachment_info->$field_path;
						break;
				}

				$fields['callback_settings'] = json_encode($fields['callback_settings']);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type" => "multipart/form-data"]);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
				curl_setopt($ch, CURLOPT_VERBOSE,  1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$res = curl_exec($ch);
				curl_close($ch);

				$data = json_decode($res);

				if (isset($data->document_url)) {
					header("Location:{$data->document_url}");
					exit();
				}

				echo "Error";
				exit();
				break;
		}

	}

	public function save($accountID = NULL, $key = NULL) {

		// check params
		if (empty($accountID) || empty($key)) {
			show_404();
		}

		// check key
		if ($key !== 'Ge3esWacUsu6aswEB8vawRachUrumUY3') {
			show_404();
		}

		// get path
		$path = UPLOADPATH . $accountID . '/';

		// check fields
		if (!isset($_FILES['content']) || $this->input->post('id') == '' || !ctype_alnum($this->input->post('id')) || !file_exists($path . $this->input->post('id'))) {
			show_404();
		}

		// move
		if (move_uploaded_file($_FILES['content']['tmp_name'], $path . $this->input->post('id'))) {
			header("HTTP/1.0 200 OK");
			echo "OK";
			exit();
		}
	}

}

/* End of file attachments.php */
/* Location: ./application/controllers/attachments.php */
