<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_safeguarding_and_data_protection_settings extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		//Add new settings
		$data = array(
			array(
				"key" => "participant_safeguarding",
				"title" => "Participant Safeguarding",
				"type" => "textarea",
				"section" => "termsprivacy",
				"order" => 3
			),
			array(
				"key" => "participant_data_protection_notice",
				"title" => "Participant Data Protection Notice",
				"type" => "textarea",
				"section" => "termsprivacy",
				"order" => 3
			),
		);

		$this->db->insert_batch("settings", $data);

		//Reorder existing settings
		$where = array(
			'section' => 'termsprivacy',
			'key' => 'participant_marketing_consent_question'
		);
		$this->db->update('settings', array("order" => 4), $where, 1);

		$where = array(
			'section' => 'termsprivacy',
			'key' => 'participant_consent_changed_subject'
		);
		$this->db->update('settings', array("order" => 5), $where, 1);

		$where = array(
			'section' => 'termsprivacy',
			'key' => 'participant_consent_changed'
		);
		$this->db->update('settings', array("order" => 6), $where, 1);

		//Add data protection information to contact
		if (!$this->db->field_exists("data_protection_agreed","family_contacts")) {
			$fields = array(
				'data_protection_agreed' => array(
					'type' => "TINYINT(1)",
					'after' => 'privacy_agreed_date',
					'default' => '0'
				),
			);

			$this->dbforge->add_column('family_contacts', $fields);
		}
		if (!$this->db->field_exists("data_protection_agreed_date","family_contacts")) {
			$fields = array(
				'data_protection_agreed_date' => array(
					'type' => "DATETIME",
					'after' => 'data_protection_agreed',
					'NULL' => true
				),
			);

			$this->dbforge->add_column('family_contacts', $fields);
		}

		//Add safeguarding information to contact
		if (!$this->db->field_exists("safeguarding_agreed","family_contacts")) {
			$fields = array(
				'safeguarding_agreed' => array(
					'type' => "TINYINT(1)",
					'after' => 'data_protection_agreed_date',
					'default' => '0'
				),
			);

			$this->dbforge->add_column('family_contacts', $fields);
		}
		if (!$this->db->field_exists("safeguarding_agreed_date","family_contacts")) {
			$fields = array(
				'safeguarding_agreed_date' => array(
					'type' => "DATETIME",
					'after' => 'safeguarding_agreed',
					'NULL' => true
				),
			);

			$this->dbforge->add_column('family_contacts', $fields);
		}
	}

	public function down() {
		$this->db->where_in("key", array("participant_safeguarding", "participant_data_protection_notice"));
		$this->db->delete("settings");
		$where = array(
			'section' => 'termsprivacy',
			'key' => 'participant_marketing_consent_question'
		);
		$this->db->update('settings', array("order" => 1), $where, 1);

		$where = array(
			'section' => 'termsprivacy',
			'key' => 'participant_consent_changed_subject'
		);
		$this->db->update('settings', array("order" => 3), $where, 1);

		$where = array(
			'section' => 'termsprivacy',
			'key' => 'participant_consent_changed'
		);
		$this->db->update('settings', array("order" => 4), $where, 1);


		$this->dbforge->drop_column('family_contacts', 'safeguarding_agreed');
		$this->dbforge->drop_column('family_contacts', 'data_protection_agreed');
		$this->dbforge->drop_column('family_contacts', 'safeguarding_agreed_date');
		$this->dbforge->drop_column('family_contacts', 'data_protection_agreed_date');
	}
}
