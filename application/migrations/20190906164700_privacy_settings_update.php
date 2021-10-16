<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Privacy_settings_update extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$data = array(
				'order' => '1',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where([
			    'key' => 'participant_marketing_consent_question'
            ])->update('settings', $data);

            $data = array(
                'order' => '2',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'participant_privacy_phone_script'
            ])->update('settings', $data);

            $data = array(
                'order' => '3',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'participant_consent_changed_subject'
            ])->update('settings', $data);

            $data = array(
                'order' => '4',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'participant_consent_changed'
            ])->update('settings', $data);

            $data = array(
                'order' => '5',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'staff_privacy'
            ])->update('settings', $data);

            $data = array(
                'order' => '6',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'reconfirm_participant_privacy'
            ])->update('settings', $data);

            $data = array(
                'order' => '7',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'staff_privacy'
            ])->update('settings', $data);

            $data = array(
                'order' => '8',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'reconfirm_participant_privacy'
            ])->update('settings', $data);

            $data = array(
                'order' => '9',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'reconfirm_staff_privacy'
            ])->update('settings', $data);
		}

		public function down() {
            $data = array(
                'order' => '10',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'participant_marketing_consent_question'
            ])->update('settings', $data);

            $data = array(
                'order' => '20',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'participant_privacy_phone_script'
            ])->update('settings', $data);

            $data = array(
                'order' => '40',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'participant_consent_changed_subject'
            ])->update('settings', $data);

            $data = array(
                'order' => '41',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'participant_consent_changed'
            ])->update('settings', $data);

            $data = array(
                'order' => '100',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'staff_privacy'
            ])->update('settings', $data);

            $data = array(
                'order' => '1',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'reconfirm_participant_privacy'
            ])->update('settings', $data);

            $data = array(
                'order' => '101',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'reconfirm_staff_privacy'
            ])->update('settings', $data);
		}
}
