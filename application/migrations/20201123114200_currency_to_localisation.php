<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Currency_to_localisation extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// update setting
			$data = [
				'key' => 'localisation',
				'title' => 'Localisation',
				'type' => 'select',
				'section' => 'general',
				'subsection' => 'general_general',
				'order' => 80,
				'value' => 'GB',
				'options' => "GB : United Kingdom (GBP)
EU : Europe (EUR)
AU : Australia (AUD)
US : United States of America (USD)",
				'instruction' => 'This will set your default currency and address format, as well as ensuring that the Google Maps services work properly.',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			];
			$where = [
				'key' => 'default_currency'
			];
			$this->db->update('settings', $data, $where, 1);

			// migrate account settings
			$map = [
				'GBP' => 'GB',
				'EUR' => 'EU',
				'AUD' => 'AU',
				'USD' => 'US'
			];
			foreach ($map as $from => $to) {
				$data = [
					'value' => $to,
					'key' => 'localisation'
				];
				$where = [
					'value' => $from,
					'key' => 'default_currency'
				];
				$this->db->update('accounts_settings', $data, $where);
			}
		}

		public function down() {
			// migrate account settings
			$map = [
				'GB' => 'GBP',
				'EU' => 'EUR',
				'AU' => 'AUD',
				'US' => 'USD',
			];
			foreach ($map as $from => $to) {
				$data = [
					'value' => $to,
					'key' => 'default_currency'
				];
				$where = [
					'value' => $from,
					'key' => 'localisation'
				];
				$this->db->update('accounts_settings', $data, $where);
			}

			// update setting
			$data = [
				'key' => 'default_currency',
				'title' => 'Default Currency',
				'type' => 'select',
				'section' => 'general',
				'subsection' => 'general_general',
				'order' => 80,
				'value' => 'GBP',
				'options' => "AUD : AUD
EUR : EUR
GBP : GBP
USD : USD",
				'instruction' => '',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			];
			$where = [
				'key' => 'localisation'
			];
			$this->db->update('settings', $data, $where, 1);
		}
}
