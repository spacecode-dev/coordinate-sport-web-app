<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Recruitment_payscales_roles extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
            $this->db->from('settings_fields')->where(['section' => 'staff_recruitment', 'field' => 'head'])->delete();
            $this->db->from('settings_fields')->where(['section' => 'staff_recruitment', 'field' => 'assistant'])->delete();
            $data = array(
                // personal info
                array(
                    'section' => 'staff_recruitment',
                    'field' => 'payscales',
                    'label' => 'Payscales',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 600,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );
            $this->db->insert_batch('settings_fields', $data);
		}

		public function down() {
            // insert
            $data = array(
                // personal info
                array(
                    'section' => 'staff_recruitment',
                    'field' => 'head',
                    'label' => 'Head Coach',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 600,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'section' => 'staff_recruitment',
                    'field' => 'assistant',
                    'label' => 'Assistant Coach',
                    'show' => 1,
                    'required' => 0,
                    'locked' => 0,
                    'order' => 601,
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                    'modified' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );
            $this->db->insert_batch('settings_fields', $data);
            $this->db->from('settings_fields')->where(['section' => 'staff_recruitment', 'field' => 'payscales'])->delete();
		}
}