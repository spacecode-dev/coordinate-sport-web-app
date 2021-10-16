<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Availability extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * availability checker
	 * @return void
	 */
	public function index() {
		$this->load->library('availability_library');
		$return = $this->availability_library->check_availability($this->input->post());
		header("Content-type: application/json");
		echo json_encode($return);
	}
}

/* End of file availability.php */
/* Location: ./application/controllers/bookings/availability.php */
