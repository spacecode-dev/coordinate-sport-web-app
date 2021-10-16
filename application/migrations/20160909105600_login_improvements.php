<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Login_improvements extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'reset_hash' => array(
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => TRUE,
                'after' => 'feed_key'
            ),
            'reset_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'after' => 'reset_hash'
            ),
            'invalid_logins' => array(
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'reset_at'
            ),
            'locked_until' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'after' => 'invalid_logins'
            ),
            'last_password_change' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'after' => 'locked_until'
            )
        );
        $this->dbforge->add_column('staff', $fields);

        // modify fields
        $fields = array(
            'password' => array(
                'name' => 'password',
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('staff', $fields);

        // add settings
        $data = array(
            'key' => 'email_reset_password',
            'title' => 'Reset Pasword Email',
            'type' => 'wysiwyg',
            'section' => 'global',
            'order' => 10,
            'value' => "<p>Hello {first_name},</p>
<p>You or someone else has requested a password reset.</p>
<p>To reset your password, please click the link below:</p>
<p>{reset_link}</p>
<p>Note: This link is only valid for 15 minutes from when this email was requested. If you didn't request this, please ignore this email.</p>",
            'instruction' => 'Available tags: {first_name}, {reset_link}',
            'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
        $this->db->insert('settings', $data);
        $data = array(
            'key' => 'max_invalid_logins',
            'title' => 'Maximum Invalid Logins',
            'type' => 'number',
            'section' => 'global',
            'order' => 10,
            'value' => 5,
            'instruction' => 'After x attempts, account will be locked temporarily',
            'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
        $this->db->insert('settings', $data);
        $data = array(
            'key' => 'force_password_change_every_x_months',
            'title' => 'Force Password Change Every x Months',
            'type' => 'number',
            'section' => 'general',
            'order' => 120,
            'value' => 3,
            'instruction' => 'Leave blank to disable',
            'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
        $this->db->insert('settings', $data);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('staff', 'reset_hash');
        $this->dbforge->drop_column('staff', 'reset_at');
        $this->dbforge->drop_column('staff', 'invalid_logins');
        $this->dbforge->drop_column('staff', 'locked_until');
        $this->dbforge->drop_column('staff', 'last_password_change');

        // modify fields
        $fields = array(
            'password' => array(
                'name' => 'password',
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('staff', $fields);

        // delete settings
        $where = array(
            'key' => 'email_reset_password'
        );
        $this->db->delete('settings', $where, 1);
        $where = array(
            'key' => 'max_invalid_logins'
        );
        $this->db->delete('settings', $where, 1);
        $where = array(
            'key' => 'force_password_change_every_x_months'
        );
        $this->db->delete('settings', $where, 1);
    }
}