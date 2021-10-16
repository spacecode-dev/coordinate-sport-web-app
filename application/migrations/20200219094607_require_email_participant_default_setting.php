<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Require_email_participant_default_setting extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$data = ['value' => 1];
		$where = ['subsection' => 'participants_general'];
		$this->db->update('settings', $data, $where, 1);
	}

	public function down()
	{
		$data = ['value' => null];
		$where = ['subsection' => 'participants_general'];
		$this->db->update('settings', $data, $where, 1);

	}
}
