<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_favicon_setting extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
		
	}

	public function up() {
		
		// Add Key 
		$data = array(
			'key' => 'favicon',
			'title' => 'Favicon',
			'type' => 'image',
			'section' => 'styling',
			'max_height' => '16',
			'max_width' => '16',
			'order' => 0,
			'instruction' => 'Optimum size of 16px x16px. This Favicon will apply to both the Bookings Site and Web App of this customer account'
		);
		
		//insert
		$this->db->insert("settings", $data);
	
	}

	public function down() {
		// reverse
		$this->db->from('settings')->where('key', "favicon")->delete();
	}
}
