<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Gocardless_pro extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields to contact
        $fields = array(
            'gc_redirect_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'relationship'
            ),
            'gc_customer_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'gc_redirect_id'
            ),
            'gc_mandate_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'gc_customer_id'
            )
        );
        $this->dbforge->add_column('family_contacts', $fields);

        // add fields to plans
        $fields = array(
            'gc_code' => array(
                'type' => 'VARCHAR',
                'constraint' => 6,
                'default' => NULL,
                'null' => TRUE,
                'unique' => TRUE,
                'after' => 'gocardless_subscription_id'
            )
        );
        $this->dbforge->add_column('family_payments_plans', $fields);

        // rename field in plans
        $fields = array(
            'gocardless_subscription_id' => array(
                'name' => 'gc_subscription_id',
                'type' => "VARCHAR",
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
            )
        );
        $this->dbforge->modify_column('family_payments_plans', $fields);

        // rename settings
        $data = array(
            'title' => 'GoCardless Access Token',
            'instruction' => 'Complete this field to enable payment plans by Direct Debit. You can create this in your <a href="https://manage.gocardless.com/developers/access-tokens/create" target="_blank">GoCardless Account</a>. <strong> Read-write access is required</strong>.'
        );
        $where = array(
            'key' => 'gocardless_access_token'
        );
        $this->db->update('settings', $data, $where, 1);

        // modify email settings
        $data = array(
            'title' => 'GoCardless Subscription Confirmation Subject',
            'value' => 'Payment plan confirmation'
        );
        $where = array(
            'key' => 'email_gocardless_subscription_subject'
        );
        $this->db->update('settings', $data, $where, 1);
        $data = array(
            'title' => 'GoCardless Subscription Confirmation',
            'value' => '<p>Hi {contact_first},</p>
<p>Thank you for requesting to pay by direct debit for {event_name}.</p>
<p>Your plan will consist of {details}</p>',
            'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {details}',
        );
        $where = array(
            'key' => 'email_gocardless_subscription'
        );
        $this->db->update('settings', $data, $where, 1);

        // add settings
        $data = array(
            array(
                'key' => 'gocardless_webhook_secret',
                'title' => 'GoCardless Webhook Secret',
                'type' => 'text',
                'section' => 'integrations',
                'order' => 113,
                'value' => '',
                'instruction' => 'You also need to enter the following URL in your <a href="https://manage.gocardless.com/developers/webhook-endpoints/create" target="_blank">Webhook endpoints</a> settings page: <a href="{site_url}webhooks/gocardless/{account_id}" target="_blank">{site_url}webhooks/gocardless/{account_id}</a>. Either choose your own secret, else GoCardless will generate one for you. <strong></strong>This needs to match exactly</strong>.',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s')
            ),
            array(
                'key' => 'email_gocardless_mandate_subject',
                'title' => 'GoCardless Mandate Link Subject',
                'type' => 'text',
                'section' => 'emailsms',
                'order' => 164,
                'value' => 'Complete your payment plan set up',
                'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s')
            ),
            array(
                'key' => 'email_gocardless_mandate',
                'title' => 'GoCardless Subscription Link',
                'type' => 'wysiwyg',
                'section' => 'emailsms',
                'order' => 165,
                'value' => '<p>Hi {contact_first},</p>
<p>Thank you for requesting to pay by direct debit for {event_name}.</p>
<p>Please click the following link to set up your direct debit securely:</p>
<p><a href="{mandate_link}">Set up my Direct Debit</a></p>',
                'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {mandate_link}',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s')
            ),
        );

        // bulk insert
        $this->db->insert_batch('settings', $data);

        // delete settings
        $keys = array(
            'gocardless_app_id',
            'gocardless_app_secret',
            'gocardless_merchant_id',
        );
        foreach ($keys as $key) {
            $where = array(
                'key' => $key
            );
            $this->db->delete('settings', $where, 1);
        }

        // reset settings
        $keys = array(
            'gocardless_access_token',
            'email_gocardless_subscription',
            'email_gocardless_subscription_subject'
        );
        foreach ($keys as $key) {
            $where = array(
                'key' => $key
            );
            $this->db->delete('accounts_settings', $where);
        }
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('family_contacts', 'gc_redirect_id');
        $this->dbforge->drop_column('family_contacts', 'gc_customer_id');
        $this->dbforge->drop_column('family_contacts', 'gc_mandate_id');
        $this->dbforge->drop_column('family_payments_plans', 'gc_code');

        // rename field in plans
        $fields = array(
            'gc_subscription_id' => array(
                'name' => 'gocardless_subscription_id',
                'type' => "VARCHAR",
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
            )
        );
        $this->dbforge->modify_column('family_payments_plans', $fields);

        // rename settings
        $data = array(
            'title' => 'GoCardless Merchant Access Token',
            'instruction' => NULL
        );
        $where = array(
            'key' => 'gocardless_access_token'
        );
        $this->db->update('settings', $data, $where, 1);

        // modify email settings
        $data = array(
            'title' => 'GoCardless Subscription Link Subject',
            'value' => 'Complete your payment plan set up',
        );
        $where = array(
            'key' => 'email_gocardless_subscription_subject'
        );
        $this->db->update('settings', $data, $where, 1);
        $data = array(
            'title' => 'GoCardless Subscription Link',
            'value' => '<p>Hi {contact_first},</p>
<p>Thank you for requesting to pay by direct debit for {event_name}.</p>
<p>Payment will consist of {details}</p>
<p>Please click the following link to set up your direct debit securely:</p>
<p><a href="{subscription_link}">Set up my Direct Debit</a></p>',
            'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {subscription_link}, {details}',
        );
        $where = array(
            'key' => 'email_gocardless_subscription'
        );
        $this->db->update('settings', $data, $where, 1);

        // add settings
        $data = array(
            array(
                'key' => 'gocardless_app_id',
                'title' => 'GoCardless App Identifier',
                'type' => 'text',
                'section' => 'integrations',
                'order' => 110,
                'options' => NULL,
                'value' => NULL,
                'instruction' => 'Complete these fields to enable payment plans by Direct Debit. You can find these details in your <a href="https://dashboard.gocardless.com/developer/api-keys" target="_blank">GoCardless Account</a>. You also need to enter the following in your <a href="https://dashboard.gocardless.com/developer/uri-settings" target="_blank">URI Settings</a> page: Redirect URI (<a href="{site_url}webhooks/gocardless/confirm/{account_id}" target="_blank">{site_url}webhooks/gocardless/confirm/{account_id}</a>) and WebHook URI (<a href="{site_url}webhooks/gocardless/handler/{account_id}" target="_blank">{site_url}webhooks/gocardless/handler/{account_id}</a>)',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s')
            ),
            array(
                'key' => 'gocardless_app_secret',
                'title' => 'GoCardless App Secret',
                'type' => 'text',
                'section' => 'integrations',
                'order' => 111,
                'options' => NULL,
                'value' => NULL,
                'instruction' => NULL,
                'created_at' => mdate('%Y-%m-%d %H:%i:%s')
            ),
            array(
                'key' => 'gocardless_merchant_id',
                'title' => 'GoCardless Merchant ID',
                'type' => 'text',
                'section' => 'integrations',
                'order' => 113,
                'options' => NULL,
                'value' => NULL,
                'instruction' => NULL,
                'created_at' => mdate('%Y-%m-%d %H:%i:%s')
            )
        );

        // bulk insert
        $this->db->insert_batch('settings', $data);

        // delete settings
        $keys = array(
            'gocardless_webhook_secret',
            'email_gocardless_mandate',
            'email_gocardless_mandate_subject'
        );
        foreach ($keys as $key) {
            $where = array(
                'key' => $key
            );
            $this->db->delete('settings', $where, 1);
        }
    }
}