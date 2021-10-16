<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Reset_customer_status extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
			// Set all to customers
			$sql = "UPDATE `" . $this->db->dbprefix('orgs') . "` SET `prospect` = '0'";
			$res = $this->db->query($sql);

			// Convert customers with no bookings or block bookings in last 3 months (or ever) to prospects:
			$sql = "UPDATE `" . $this->db->dbprefix('orgs') . "` AS o INNER JOIN (
					SELECT * FROM (
						SELECT o.`orgID`, GREATEST(COALESCE(MAX(b.`endDate`), 0),  COALESCE(MAX(bl.`endDate`), 0)) AS `endDate`
						FROM `" . $this->db->dbprefix('orgs') . "` AS o
						LEFT JOIN `" . $this->db->dbprefix('bookings') . "` AS b ON o.`orgID` = b.`orgID`
						LEFT JOIN `" . $this->db->dbprefix('bookings_blocks') . "` AS bl ON o.`orgID` = bl.`orgID`
						WHERE o.`prospect` = '0'
						GROUP BY o.`orgID`
					) AS `potentials`
					WHERE (`endDate` = 0 OR `endDate` < DATE_SUB(NOW(), INTERVAL 3 MONTH))
					ORDER BY `endDate` DESC
				) AS s ON o.`orgID` = s.`orgID` SET `prospect` = '1'";
			$res = $this->db->query($sql);
        }

        public function down() {
			// no going back
        }
}
