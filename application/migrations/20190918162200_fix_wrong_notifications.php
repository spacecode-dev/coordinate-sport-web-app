<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fix_wrong_notifications extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
		    if (ENVIRONMENT == 'production') {
		        // move some messages to correct customer
                $data = [
                    'orgID' => 7329
                ];

                $exists = $this->db->select()->from('orgs_notifications')
                    ->where([
                        'notificationID' => 2161,
                        'orgID' => 7640
                    ])->get();

                if ($exists->num_rows() > 0) {
                    $this->db->where('notificationID', 2161)->update('orgs_notifications', $data);
                }

                $exists = $this->db->select()->from('orgs_notifications')
                    ->where([
                        'notificationID' => 2160,
                        'orgID' => 7640
                    ])->get();

                if ($exists->num_rows() > 0) {
                    $this->db->where('notificationID', 2160)->update('orgs_notifications', $data);
                }

                $data = [
                    'orgID' => 7522
                ];

                $exists = $this->db->select()->from('orgs_notifications')
                    ->where([
                        'notificationID' => 2159,
                        'orgID' => 7640
                    ])->get();

                if ($exists->num_rows() > 0) {
                    $this->db->where('notificationID', 2159)->update('orgs_notifications', $data);
                }
            }
		}

		public function down() {

		}
}
