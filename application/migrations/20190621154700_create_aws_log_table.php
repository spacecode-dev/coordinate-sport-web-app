<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_aws_log_table extends CI_Migration {

	private $CI;

	public function __construct() {
		parent::__construct();
		$this->CI = & get_instance();
		$this->CI->load->library('aws_library');
		if (getenv('DISABLE_ACTIVITY') != 1) {
			$this->CI->load->library('activity_library');
		}
	}

	public function up() {
		if (getenv('DISABLE_ACTIVITY') != 1 && !$this->CI->activity_library->checkExistsTable()) {
			$this->CI->activity_library->createTable();
			// it takes some time to be executed, to prevent future warnings.
			sleep(10);
		}
	}

	public function down() {
	}
}
