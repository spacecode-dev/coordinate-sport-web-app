<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_brand_to_pricing extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add field
        $fields = array(
            'brandID' => array(
                'type' => "INT",
                'constraint' => 11,
                'null' => FALSE,
                'after' => 'typeID'
            )
        );
        $this->dbforge->add_column('orgs_pricing', $fields);

        // add key
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_pricing') . '` ADD INDEX (`brandID`)');

        // get orgs_pricing
        $orgs_pricing = $this->db->from('orgs_pricing')->get();

        // track account brands
        $account_brands = array();

        // duplicate previous pricing across all brands
        foreach ($orgs_pricing->result_array() as $price) {
            // look up account brands
            if (array_key_exists($price['accountID'], $account_brands)) {
                // already looked up
                $brands = $account_brands[$price['accountID']];
            } else {
                // look up
                $brands = array();
                $where = array(
                    'accountID' => $price['accountID']
                );
                $res = $this->db->from('brands')->where($where)->get();
                if ($res->num_rows() > 0) {
                    foreach ($res->result() as $row) {
                        $brands[] = $row->brandID;
                    }
                }
                // cache
                $account_brands[$price['accountID']] = $brands;
            }

            // loop through all and copy pricing
            if (count($brands) > 0) {
                foreach ($brands as $brandID) {
                    $data = $price;
                    $data['brandID'] = $brandID;
                    unset($data['linkID']);
                    $this->db->insert('orgs_pricing', $data);
                }
            }

            // delete original
            $where = array(
                'linkID' => $price['linkID']
            );
            $this->db->delete('orgs_pricing', $where, 1);
        }

        // set foreign key
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_pricing') . '` ADD CONSTRAINT `fk_orgs_pricing_brandID` FOREIGN KEY (`brandID`) REFERENCES `' . $this->db->dbprefix('brands') . '`(`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE');
    }

    public function down() {
        // remove foreign keys
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_pricing') . '` DROP FOREIGN KEY `fk_orgs_pricing_brandID`');

        // remove fields
        $this->dbforge->drop_column('orgs_pricing', 'brandID');
    }
}