<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Mark_sessions_as_block_priced extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// mark existing bookings booked ith require_all_sessions as block priced
		$sql = "UPDATE
			`" . $this->db->dbprefix('bookings_cart_sessions') . "` AS sessions
			INNER JOIN (
				SELECT
					sessions.sessionID
				FROM
					`" . $this->db->dbprefix('bookings_cart_sessions') . "` AS sessions
					LEFT JOIN `" . $this->db->dbprefix('bookings_blocks') . "` as blocks ON sessions.blockID = blocks.blockID
				WHERE
					blocks.require_all_sessions = 1
			) AS sessions_selected ON sessions.sessionID = sessions_selected.sessionID
		SET
			block_priced = 1";
		$res = $this->db->query($sql);
	}

	public function down() {
		// no going back
	}
}
