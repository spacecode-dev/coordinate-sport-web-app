<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Ethnic_origins extends CI_Migration {

		private $ethnic_origins;

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();

			$this->ethnic_origins = array(
				'whiteBritish' => 'White - Welsh/English/Scottish/Northern Irish/British',
				'whiteIrish' => 'White - Irish',
				'whiteOther' => 'White - Other',
				'mixedCaribbean' => 'Mixed/multiple ethnic groups - White and Black Caribbean',
				'mixedAfrican' => 'Mixed/multiple ethnic groups - White and Black African',
				'mixedOther' => 'Mixed/multiple ethnic groups - Other',
				'asianIndian' => 'Asian/Asian British - Indian',
				'asianPakistani' => 'Asian/Asian British - Pakistani',
				'asianBangladeshi' => 'Asian/Asian British - Bangladeshi',
				'asianOther' => 'Asian/Asian British - Other',
				'asianCaribbean' => 'Asian/Asian British - Other',
				'blackAfrican' => 'Black/African/Caribbean/Black British - Other',
				'chinese' => 'Asian/Asian British - Chinese',
				'other' => 'Prefer not to say'
			);
        }

        public function up() {
            // define fields
            $fields = array(
                'ethnic_origin' => array(
                    'type' => 'VARCHAR',
					'constraint' => 100,
					'default' => NULL,
					'null' => TRUE,
					'after' => 'disability_info'
                )
            );
			$this->dbforge->add_column('family_children', $fields);
			$this->dbforge->add_column('family_contacts', $fields);

			// modify staff field
            $fields = array(
				'equal_ethnic' => array(
                    'name' => 'equal_ethnic_old',
					'type' => "ENUM('whiteBritish', 'whiteIrish', 'whiteOther', 'mixedCaribbean', 'mixedAfrican', 'mixedOther', 'asianIndian', 'asianPakistani', 'asianBangladeshi', 'asianOther', 'asianCaribbean', 'blackAfrican', 'chinese', 'other')",
					'default' => NULL,
					'null' => TRUE
                ),
				'equal_ethnic_other' => array(
                    'name' => 'equal_ethnic_other_old',
					'type' => 'VARCHAR',
					'constraint' => 50,
					'default' => NULL,
					'null' => TRUE
                )
            );
            $this->dbforge->modify_column('staff', $fields);

			// define fields
            $fields = array(
                'equal_ethnic' => array(
                    'type' => 'VARCHAR',
					'constraint' => 100,
					'default' => NULL,
					'null' => TRUE,
					'after' => 'equal_ethnic_old'
                )
            );
			$this->dbforge->add_column('staff', $fields);

			// migrate existing staff ethnic origins
			foreach ($this->ethnic_origins as $from => $to) {
				$where = array(
					'equal_ethnic_old' => $from
				);
				$data = array(
					'equal_ethnic' => $to
				);
				$this->db->update('staff', $data, $where);
			}
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('family_contacts', 'ethnic_origin', TRUE);
			$this->dbforge->drop_column('family_children', 'ethnic_origin', TRUE);
			$this->dbforge->drop_column('staff', 'equal_ethnic', TRUE);

			// modify staff field
            $fields = array(
                'equal_ethnic_old' => array(
                    'name' => 'equal_ethnic',
					'type' => "ENUM('whiteBritish', 'whiteIrish', 'whiteOther', 'mixedCaribbean', 'mixedAfrican', 'mixedOther', 'asianIndian', 'asianPakistani', 'asianBangladeshi', 'asianOther', 'asianCaribbean', 'blackAfrican', 'chinese', 'other')",
					'default' => NULL,
					'null' => TRUE
                ),
				'equal_ethnic_other_old' => array(
                    'name' => 'equal_ethnic_other',
					'type' => 'VARCHAR',
					'constraint' => 50,
					'default' => NULL,
					'null' => TRUE
                )
            );
            $this->dbforge->modify_column('staff', $fields);
        }
}
