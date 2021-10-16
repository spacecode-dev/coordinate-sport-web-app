<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Note_remove_update extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		//remove duplicate evaluations in the period of June and July
		$query = $this->db->select()->from('bookings_lessons_notes')->where([
			'type' => 'evaluation',
			'added >= ' => '2019-05-01',
			'added <= ' => '2019-07-31',
		])->get();

		$notes = [];
		foreach ($query->result() as $row) {
			$notes[md5($row->lessonID . $row->date . $row->byID)][] = $row;
		}

		$notes_to_remove = [];
		foreach ($notes as $key => $note) {
			if (count($note) > 1) {
				$remove_count = 0;
				foreach ($note as $value) {
					if ($value->remove == 1) {
						$remove_count++;
						$notes_to_remove[] = $value->noteID;
						continue;
					}
				}
				if ($remove_count < 1) {
					for ($i = 0; $i < count($note) - 1; $i++) {
						$notes_to_remove[] = $note[$i]->noteID;
					}
				}
			}
		}

		if (!empty($notes_to_remove)) {
			$this->db->from('bookings_lessons_notes')->where_in('noteID', $notes_to_remove)->delete();
		}

		$this->dbforge->drop_column('bookings_lessons_notes', 'remove');
	}

	public function down() {
		$fields = array(
			'remove' => array(
				'type' => 'BOOLEAN',
				'after' => 'type',
				'default' => 0
			)
		);
		$this->dbforge->add_column('bookings_lessons_notes', $fields);
	}
}