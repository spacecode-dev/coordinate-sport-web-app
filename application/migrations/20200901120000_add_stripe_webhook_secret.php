<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_stripe_webhook_secret extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		$where = array(
			'key' => 'stripe_whs'
		);
		$res_check = $this->db->from('settings')->where($where)->get();

		if ($res_check->num_rows() > 0) {
			$data = array(
				'key' => 'stripe_whs',
				'title' => 'Stripe Webhook Signing Secret Key',
				'type' => 'text',
				'section' => 'integrations',
				'order' => 132,
				'options' => '',
				'value' => '',
				'tab' => 'keys',
				'readonly' => '0',
				'instruction' => 'From your <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Stripe Dashboard</a>.You also need to enter the following URL in your <a href="https://dashboard.stripe.com/webhooks" target="_blank">Webhook endpoints</a> settings page: <a href="{site_url}webhooks/stripe/{account_id}" target="_blank">{site_url}webhooks/stripe/{account_id}</a>. Stripe will generate one for you. <strong></strong>This needs to match exactly</strong>.'
			);
			$where = array(
				'key' => 'stripe_whs'
			);
			$this->db->update('settings', $data, $where, 1);
		}else {
			$data = array(
				'key' => 'stripe_whs',
				'title' => 'Stripe Webhook Signing Secret Key',
				'type' => 'text',
				'section' => 'integrations',
				'order' => 132,
				'options' => '',
				'value' => '',
				'tab' => 'keys',
				'readonly' => '0',
				'instruction' => 'From your <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Stripe Dashboard</a>.You also need to enter the following URL in your <a href="https://dashboard.stripe.com/webhooks" target="_blank">Webhook endpoints</a> settings page: <a href="{site_url}webhooks/stripe/{account_id}" target="_blank">{site_url}webhooks/stripe/{account_id}</a>. Stripe will generate one for you. <strong></strong>This needs to match exactly</strong>.',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->insert('settings', $data);
		}
    }

    public function down() {
        // remove new settings
        $where_in = array(
            'stripe_whs'
        );
        $this->db->from('settings')->where_in('key', $where_in)->delete();
    }
}
