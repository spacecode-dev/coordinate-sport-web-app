<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bulk_dbs_email_tag_update extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update instruction
            $data = array(
                'instruction' => 'Available tags: {contact_name}, {block_name}'
            );
            $where = array(
                'key' => 'email_senddbs_subject'
            );
            $this->db->update('settings', $data, $where, 1);

            // update instruction and value
            $data = array(
                'instruction' => 'Available tags: {contact_name}, {block_name}, {details}',
                'value' =>'<p>Dear {contact_name},</p>
 <p>As per your request, we are pleased to supply you with confirmation if the DBS details you have requested for {block_name}:</p>
 <p>{details}</p>
 <p>If you have any further queries please do not hesitate to contact us.</p>'
            );
            $where = array(
                'key' => 'email_senddbs'
            );
            $this->db->update('settings', $data, $where, 1);


        }

        public function down() {
            // update instruction
            $data = array(
                'instruction' => 'Available tags: {main_contact}, {block_name}'
            );
            $where = array(
                'key' => 'email_senddbs_subject'
            );
            $this->db->update('settings', $data, $where, 1);

            // update instruction and value
            $data = array(
                'instruction' => 'Available tags: {main_contact}, {block_name}, {details}',
                'value' =>'<p>Dear {main_contact},</p>
 <p>As per your request, we are pleased to supply you with confirmation if the DBS details you have requested for {block_name}:</p>
 <p>{details}</p>
 <p>If you have any further queries please do not hesitate to contact us.</p>'
            );
            $where = array(
                'key' => 'email_senddbs'
            );
            $this->db->update('settings', $data, $where, 1);
        }
}