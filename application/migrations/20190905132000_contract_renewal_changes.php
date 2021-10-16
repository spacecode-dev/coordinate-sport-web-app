<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Contract_renewal_changes extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // define fields
        $fields = array(
            'contract_renewal' => array(
                'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE,
				'after' => 'contract_type'
            ),
			'contract_renewed' => array(
                'type' => "ENUM('pending', 'renewed', 'cancelled')",
				'null' => TRUE,
				'after' => 'contract_renewal'
            ),
			'contract_reminders_sent' => array(
                'type' => "TEXT",
				'default' => NULL,
				'null' => TRUE,
				'after' => 'contract_renewed'
            )
        );
		$this->dbforge->add_column('bookings', $fields);

		// modify old fields
        $fields = array(
			'contract_type' => array(
                'name' => 'contract_type_old',
				'type' => "ENUM('one off', 'company', 'external')",
				'default' => NULL,
				'null' => TRUE
            ),
			'renewed' => array(
                'name' => 'renewed_old',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
			'renewal_reminder_1' => array(
                'name' => 'renewal_reminder_1_old',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
			'renewal_reminder_2' => array(
                'name' => 'renewal_reminder_2_old',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
			'renewal_reminder_3' => array(
                'name' => 'renewal_reminder_3_old',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
			'renewal_reminder_4' => array(
                'name' => 'renewal_reminder_4_old',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
        );
        $this->dbforge->modify_column('bookings', $fields);

		// migrate existing fields
		$where = array(
			'renewed_old' => 1
		);
		$data = array(
			'contract_renewed' => 'renewed'
		);
		$this->db->update('bookings', $data, $where);

		$where = array(
			'contract_type_old' => 'company'
		);
		$data = array(
			'contract_renewal' => 1
		);
		$this->db->update('bookings', $data, $where);
    }

    public function down() {
		// remove columns added above
		$this->dbforge->drop_column('bookings', 'contract_renewal', TRUE);
		$this->dbforge->drop_column('bookings', 'contract_renewed', TRUE);
		$this->dbforge->drop_column('bookings', 'contract_reminders_sent', TRUE);

		// restore old fields
        $fields = array(
			'contract_type_old' => array(
                'name' => 'contract_type',
				'type' => "ENUM('one off', 'company', 'external')",
				'default' => NULL,
				'null' => TRUE
            ),
			'renewed_old' => array(
                'name' => 'renewed',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
			'renewal_reminder_1_old' => array(
                'name' => 'renewal_reminder_1',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
			'renewal_reminder_2_old' => array(
                'name' => 'renewal_reminder_2',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
			'renewal_reminder_3_old' => array(
                'name' => 'renewal_reminder_3',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
			'renewal_reminder_4_old' => array(
                'name' => 'renewal_reminder_4',
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE
            ),
        );
        $this->dbforge->modify_column('bookings', $fields);
    }
}
