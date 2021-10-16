<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Online_booking_header extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		// define new settings
		$data = array(
			array(
				'key' => 'online_booking_header_image',
				'title' => 'Online Booking Header Image',
				'type' => 'image',
				'section' => 'styling',
				'order' => 41,
				'value' => '',
				'instruction' => 'Recommended size: 1920px x 800px. Will be cropped depending on screen size',
				'max_height' => 800,
				'max_width' => 1920,
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			)
		);

		// bulk insert
		$this->db->insert_batch('settings', $data);
    }

    public function down() {
		// remove new settings
		$where_in = array(
			'online_booking_header_image'
		);
		$this->db->from('settings')->where_in('key', $where_in)->delete();
    }
}
