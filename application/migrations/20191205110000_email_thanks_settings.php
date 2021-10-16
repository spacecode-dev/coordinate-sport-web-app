<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Email_thanks_settings extends CI_Migration {

		private $keys;

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();

			$this->keys = [
				'email_event_thanks',
				'email_event_thanks_subject',
				'event_thanks_email_emailsms',
				'send_event_thanks'
			];
		}

		public function up() {
			// copy duplicates
			$res = $this->db->from('settings')
				->where_in('key', $this->keys)
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result_array() as $row) {
					// make copy for block email
					$data = $row;
					foreach ($data as $key => $val) {
						// replace references to event with block (certain fields)
						if (!in_array($key, ['key', 'title', 'section', 'subsection', 'toggle_fields'])) {
							continue;
						}
						$data[$key] = str_replace('event', 'block', $data[$key]);
						$data[$key] = str_replace('Event', 'Block', $data[$key]);
					}
					$data['created_at'] = mdate('%Y-%m-%d %H:%i:%s');
					$data['updated_at'] = mdate('%Y-%m-%d %H:%i:%s');
					$this->db->insert('settings', $data);

					// rename event emails
					$data = [
						'title' => str_replace('Event Thanks', 'Participant Booking Thanks', $row['title'])
					];
					if ($row['key'] == 'send_event_thanks') {
						$data['value'] = 0;
						$data['order'] = 18;
					}
					$where = [
						'key' => $row['key']
					];
					$this->db->update('settings', $data, $where, 1);
				}
			}

			// duplicate any custom account settings for block emails
			$res = $this->db->from('accounts_settings')
				->where_in('key', $this->keys)
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result_array() as $row) {
					$data = $row;
					unset($data['settingID']);
					$data['key'] = str_replace('event', 'block', $data['key']);
					$data['updated_at'] = mdate('%Y-%m-%d %H:%i:%s');
					$this->db->insert('accounts_settings', $data);
				}
			}

			// don't send booking thanks by default
			$data = [
				'value' => 0
			];
			$where = [
				'key' => 'send_event_thanks'
			];
			$this->db->update('accounts_settings', $data, $where);
		}

		public function down() {
			// rename event emails
			$res = $this->db->from('settings')
				->where_in('key', $this->keys)
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result_array() as $row) {
					$data = [
						'title' => str_replace('Participant Booking Thanks', 'Event Thanks', $row['title'])
					];
					$where = [
						'key' => $row['key']
					];
					$this->db->update('settings', $data, $where, 1);
				}
			}

			// delete block emails
			$keys = [
				'email_block_thanks',
				'email_block_thanks_subject',
				'block_thanks_email_emailsms',
				'send_block_thanks'
			];
			$this->db->where_in('key', $keys)->delete('settings');
			$this->db->where_in('key', $keys)->delete('accounts_settings');
		}
}
