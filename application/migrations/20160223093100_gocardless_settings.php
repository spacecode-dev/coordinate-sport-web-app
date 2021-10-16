<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Gocardless_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'gocardless_app_id',
                    'title' => 'GoCardless App Identifier',
                    'type' => 'text',
                    'section' => 'general',
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
                    'section' => 'general',
                    'order' => 111,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'gocardless_access_token',
                    'title' => 'GoCardless Merchant Access Token',
                    'type' => 'text',
                    'section' => 'general',
                    'order' => 112,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'gocardless_merchant_id',
                    'title' => 'GoCardless Merchant ID',
                    'type' => 'text',
                    'section' => 'general',
                    'order' => 113,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'gocardless_environment',
                    'title' => 'GoCardless Environment',
                    'type' => 'select',
                    'section' => 'general',
                    'order' => 114,
                    'options' => "production : Production
sandbox : Sandbox",
                    'value' => "sandbox",
                    'instruction' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'gocardless_success_redirect',
                    'title' => 'GoCardless Success Redirect',
                    'type' => 'url',
                    'section' => 'general',
                    'order' => 115,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => 'Enter the link customers should be redirected to after setting up the direct debit',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);

            // update order of mailchimp setting to 100
            $data = array(
                'order' => 100
            );
            $where = array(
                'key' => 'mailchimp_key'
            );
            $this->db->update('settings', $data, $where, 1);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'gocardless_app_id',
                'gocardless_app_secret',
                'gocardless_access_token',
                'gocardless_merchant_id'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}