<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_Unused_Theme_Customisation_Fields extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
			$this->db->delete('settings', array('key' => 'body_colour'));
			$this->db->delete('settings', array('key' => 'contrast_colour'));
			$this->db->delete('settings', array('key' => 'label_nostaff_colour'));
        }

        public function down() {
			$data = array(
				'key' => 'body_colour',
        		'title' => 'Body Colour',
        		'type' => 'select',
				'section' => 'styling',
				'subsection' => NULL,
				'order' => '10',
				'options' => 'white : White\r\nred : Red\r\nblue : Blue\r\norange : Burnt Orange\r\npurple : Purple\r\ngreen : Green\r\nmuted : Muted\r\nfb : Facebook Blue\r\ndark : Dark\r\npink : Muave\r\ngrass-green : Grass Green\r\nbanana : Banana\r\ndark-orange : Dark Orange\r\nbrown : Brown',
				'tabpos' => NULL,
				'value' => 'white',
				'description' => NULL,
				'instruction' => '',
				'toggle_fields' => NULL,
				'max_height' => 0,
				'max_width' => 0,
				'readonly' => 0,
				'required_features' => NULL,
				'created_at' => '2015-04-14 16:01:02',
				'updated_at' => '2017-09-15 14:50:25'
			);

			$this->db->insert('settings', $data);

			$data = array(
				'key' => 'contrast_colour',
				'title' => 'Contrast Colour',
				'type' => 'select',
				'section' => 'styling',
				'subsection' => NULL,
				'order' => '10',
				'options' => 'light : Light\r\ndark : Dark\r\ndark-blue : Dark Blue',
				'tabpos' => NULL,
				'value' => 'dark',
				'description' => NULL,
				'instruction' => '',
				'toggle_fields' => NULL,
				'max_height' => 0,
				'max_width' => 0,
				'readonly' => 0,
				'required_features' => NULL,
				'created_at' => '2015-04-14 16:01:02',
				'updated_at' => '2017-09-15 14:50:25'
			);

			$this->db->insert('settings', $data);

			$data = array(
				'key' => 'label_nostaff_colour',
				'title' => 'No Staff Label Colour',
				'type' => 'select',
				'section' => 'styling',
				'subsection' => NULL,
				'order' => '40',
				'options' => 'blue : Blue\r\norange : Orange\r\nred : Red\r\ngreen : Green\r\npurple : Purple\r\nblue : Blue\r\npink : Pink\r\nlight-blue : Light Blue\r\ndark-grey : Dark Grey',
				'tabpos' => NULL,
				'value' => 'red',
				'description' => NULL,
				'instruction' => 'Other label colours can be set in Settings > Brands',
				'toggle_fields' => NULL,
				'max_height' => 0,
				'max_width' => 0,
				'readonly' => 0,
				'required_features' => NULL,
				'created_at' => '2015-04-14 16:01:02',
				'updated_at' => '2017-09-15 14:50:25'
			);

			$this->db->insert('settings', $data);
        }
}
