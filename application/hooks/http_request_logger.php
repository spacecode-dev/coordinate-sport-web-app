<?php

class Http_request_logger {

	public function log() {
		if (getenv('DISABLE_ACTIVITY') == 1) {
			return FALSE;
		}

		$CI = & get_instance();

		if (!$CI->auth->user) {
			return false;
		}

		//if user log in to another user we should not record activity
		if ($CI->session->account_id_override || $CI->session->user_id_override) {
			return false;
		}

		$CI->load->library('activity_library');
		$CI->load->library('form_validation');

		//ignoring ajax requests
		$requests_to_ignore = [
			'participants/contactcheck',
			'participants/childcheck',
			'user-activity/get-records',
			'login',
			'logout'
		];

		if (in_array($CI->uri->uri_string, $requests_to_ignore)){
			return false;
		}

		$method = $_SERVER['REQUEST_METHOD'];

		$index = array_search('remove', $CI->uri->segments);

		$page = '';
		foreach ($CI->uri->segments as $segment) {
			$page .= ' -> ' . ucfirst(str_replace('_', ' ', $segment));
		}

		$page = substr($page , 4);


		if ($index) {
			$method = 'DELETE';
		}

		switch ($method) {
			case 'POST':
				if (!isset($_POST['search'])) {
					$CI->activity_library->createRecord($CI->auth->user,
						in_array('new', $CI->uri->segments) ? 'Created' : 'Edited', $page, $_SERVER['REQUEST_URI']);
				}
				break;
			case 'DELETE':
				$CI->activity_library->createRecord($CI->auth->user,
					'Removed', $page, $_SERVER['REQUEST_URI']);
				break;
			default:
				break;
		}
	}

}
