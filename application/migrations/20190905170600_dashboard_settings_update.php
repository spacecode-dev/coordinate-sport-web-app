<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Dashboard_settings_update extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$data = array(
				'order' => '7',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where([
			    'key' => 'employee_of_month'
            ])->update('settings', $data);

            $data = array(
                'order' => '8',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'dashboard_staff_birthdays'
            ])->update('settings', $data);

            $data = array(
                'title' => 'Custom Widget 2 Title',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'dashboard_custom_widget_2_title'
            ])->update('settings', $data);

            $data = array(
                'title' => 'Custom Widget 3 Title',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'dashboard_custom_widget_3_title'
            ])->update('settings', $data);

            $data = array(
                'title' => 'Custom Widget 2 HTML',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'dashboard_custom_widget_2_html'
            ])->update('settings', $data);

            $data = array(
                'title' => 'Custom Widget 3 HTML',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'dashboard_custom_widget_3_html'
            ])->update('settings', $data);
		}

		public function down() {
            $data = array(
                'order' => '-1',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'employee_of_month'
            ])->update('settings', $data);

            $data = array(
                'order' => '0',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'dashboard_staff_birthdays'
            ])->update('settings', $data);
		}
}
