<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Timesheets_items_salaried extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add fields
			$fields = array(
				'salaried' => array(
					'type' => 'int',
					'default' => 0,
					'null' => false,
					'after' => 'role'
				)
			);
			$this->dbforge->add_column('timesheets_items', $fields);

			// take best guess at marking existing timesheet items as salaried - doesn't account for if a staff member is staffed multiple times on the same session
			$where = [
				'bookings_lessons_staff.salaried' => 1
			];
			$items = $this->db->select('timesheets_items.itemID')
				->from('timesheets')
				->join('timesheets_items', 'timesheets.timesheetID = timesheets_items.timesheetID', 'inner')
				->join('bookings_lessons_staff', 'timesheets.staffID = bookings_lessons_staff.staffID and timesheets_items.lessonID = bookings_lessons_staff.lessonID', 'inner')
				->where($where)
				->group_by('timesheets_items.itemID')
				->get();

			$itemIDs = [];
			if ($items->num_rows() > 0) {
				foreach ($items->result() as $row) {
					$itemIDs[] = $row->itemID;
					// update
					$data = [
						'salaried' => 1
					];
					$where = 'itemID IN (' . implode(',', $itemIDs) . ')';
					$this->db->update('timesheets_items', $data, $where);
				}
			}
		}

		public function down() {
			// drop fields
			$this->dbforge->drop_column('timesheets_items', 'salaried');
		}
}
