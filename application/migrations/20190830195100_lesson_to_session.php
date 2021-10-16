<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Lesson_to_session extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
			$sql = [];
			// Case sensitive Lesson to Session in settings
			$sql[] = "UPDATE `" . $this->db->dbprefix('settings') . "`
			SET `value` = REPLACE(`value`, 'Lesson', 'Session')
			WHERE CONVERT( `value` USING LATIN1) COLLATE latin1_general_cs LIKE '%Lesson%'";
			// Case insensitive lesson to session in settings
			$sql[] = "UPDATE `" . $this->db->dbprefix('settings') . "`
			SET `value` = REPLACE(`value`, 'lesson', 'session')
			WHERE `value` LIKE '%lesson%'";
			// Case sensitive Lesson to Session in account settings
			$sql[] = "UPDATE `" . $this->db->dbprefix('accounts_settings') . "`
			SET `value` = REPLACE(`value`, 'Lesson', 'Session')
			WHERE CONVERT( `value` USING LATIN1) COLLATE latin1_general_cs LIKE '%Lesson%'";
			// Case insensitive lesson to session in account settings
			$sql[] = "UPDATE `" . $this->db->dbprefix('accounts_settings') . "`
			SET `value` = REPLACE(`value`, 'lesson', 'session')
			WHERE `value` LIKE '%lesson%'";

			// run queries
			foreach ($sql as $query) {
				$res = $this->db->query($query);
			}
        }

        public function down() {
			$sql = [];
			// Case sensitive Session to Lesson in settings
			$sql[] = "UPDATE `" . $this->db->dbprefix('settings') . "`
			SET `value` = REPLACE(`value`, 'Session', 'Lesson')
			WHERE CONVERT( `value` USING LATIN1) COLLATE latin1_general_cs LIKE '%Session%'";
			// Case insensitive session to lesson in settings
			$sql[] = "UPDATE `" . $this->db->dbprefix('settings') . "`
			SET `value` = REPLACE(`value`, 'session', 'lesson')
			WHERE `value` LIKE '%session%'";
			// Case sensitive Session to Lesson in account settings
			$sql[] = "UPDATE `" . $this->db->dbprefix('accounts_settings') . "`
			SET `value` = REPLACE(`value`, 'Session', 'Lesson')
			WHERE CONVERT( `value` USING LATIN1) COLLATE latin1_general_cs LIKE '%Session%'";
			// Case insensitive session to lesson in account settings
			$sql[] = "UPDATE `" . $this->db->dbprefix('accounts_settings') . "`
			SET `value` = REPLACE(`value`, 'session', 'lesson')
			WHERE `value` LIKE '%session%'";

			// run queries
			foreach ($sql as $query) {
				$res = $this->db->query($query);
			}
        }
}
