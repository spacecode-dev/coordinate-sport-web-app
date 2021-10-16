<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_settings_items_in_carts_alert extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up()
	{
		$this->db->insert('settings', [
			'key' => 'items_in_carts_alerts_emailsms',
			'title' => 'Item(s) in Cart Alerts',
			'type' => 'checkbox',
			'section' => 'emailsms-main',
			'order' => 28,
			'value' => 0,
			'toggle_fields' => 'send_items_in_carts_alert',
			'instruction' => '',
			'created_at' => mdate('%Y-%m-%d %H:%i:%s')
		]);
		$this->db->insert('settings', [
			'key' => 'send_items_in_carts_alert',
			'title' => 'Send Item(s) in Cart Alerts',
			'type' => 'checkbox',
			'section' => 'emailsms',
			'order' => 431,
			'value' => 0,
			'toggle_fields' => '',
			'instruction' => '',
			'created_at' => mdate('%Y-%m-%d %H:%i:%s')
		]);
		$data = [
			[
				'key' => 'item_in_cart_alert_subject',
				'title' => 'Item in Cart Alert Subject',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'items_in_carts_alerts_emailsms',
				'order' => 0,
				'value' => 'Pending Item in Booking Cart',
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],

			[
				'key' => 'item_in_cart_alert',
				'title' => 'Item in Cart Alert',
				'type' => 'wysiwyg',
				'section' => 'emailsms',
				'subsection' => 'items_in_carts_alerts_emailsms',
				'order' => 1,
				'value' =>
					'<p>Hi {contact_first},</p>

					<p>Thank you for visiting the {org_name} bookings site. We are sending this message to make you aware that your booking has not been completed and there are still items remaining in your booking cart.</p>

					<p>To confirm your booking you will need to login to the booking site and click on ‘Checkout’.</p>

					<p>Please click on the following link to log in to the bookings site: {bookings_site_link}</p>

					<p>Please reply to this email if you require any further assistance.</p>

					<p>Thank you</p>

					<p>{org_name}</p>'
				,
				'instruction' => 'Available tags: {contact_first}, {org_name}, {bookings_site_link}, {booking_cart_timeout}',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'item_in_cart_alert_email_timeframe',
				'title' => 'Email Timeframe',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'items_in_carts_alerts_emailsms',
				'order' => 2,
				'value' => '',
				'instruction' => 'Hours',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'item_in_cart_alert_send_reminder',
				'title' => 'Send Reminder',
				'type' => 'checkbox',
				'section' => 'emailsms',
				'subsection' => 'items_in_carts_alerts_emailsms',
				'order' => 3,
				'value' => '',
				'toggle_fields' => '1',
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'item_in_cart_alert_reminder_timeframe',
				'title' => 'Reminder Timeframe',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'items_in_carts_alerts_emailsms',
				'order' => 4,
				'value' => '',
				'instruction' => 'Days',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
		];
		foreach ($data as $datum) {
			$this->db->insert('settings', $datum);
		}
	}

	public function down()
	{
		$this->db->delete('settings', ['key' => 'items_in_carts_alerts_emailsms'], 1);
		$this->db->delete('settings', ['key' => 'item_in_cart_alert_subject'], 1);
		$this->db->delete('settings', ['key' => 'item_in_cart_alert'], 1);
		$this->db->delete('settings', ['key' => 'item_in_cart_alert_email_timeframe'], 1);
		$this->db->delete('settings', ['key' => 'item_in_cart_alert_send_reminder'], 1);
		$this->db->delete('settings', ['key' => 'item_in_cart_alert_reminder_timeframe'], 1);
	}
}
