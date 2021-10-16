<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Email_thanks_block_name_tag extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add block_name tag
			$data = [
				'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {block_name}, {website}'
			];
			$where_in = [
				'email_block_thanks',
				'email_block_thanks_subject'
			];
			$this->db->where_in('key', $where_in)->update('settings', $data);
		}

		public function down() {
			// remove block_name tag
			$data = [
				'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {website}'
			];
			$where_in = [
				'email_block_thanks',
				'email_block_thanks_subject'
			];
			$this->db->where_in('key', $where_in)->update('settings', $data);
		}
}
