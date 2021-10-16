<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_duplicate_evaluations extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // find duplicates
        $sql = "SELECT
		    GROUP_CONCAT(noteID ORDER BY noteID ASC) as noteIDs,
		    lessonID,
		    byID,
		    date,
		    GROUP_CONCAT(added ORDER BY added ASC),
		    COUNT(*) AS NumDuplicates
		FROM
		    " . $this->db->dbprefix('bookings_lessons_notes') . "
		WHERE type = 'evaluation'
		GROUP BY lessonID , byID , date
		HAVING NumDuplicates > 1
		ORDER BY noteID  DESC";
		$res = $this->db->query($sql);

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$noteIDs = explode(',', $row->noteIDs);

				// check for unsubmitted evaluations with those IDs
				$where = [
					'status' => 'unsubmitted',
					'type' => 'evaluation'
				];
				$res_check = $this->db->from('bookings_lessons_notes')->where_in('noteID', $noteIDs)->where($where)->get();
				if ($res_check->num_rows() == 0) {
					// don't remove duplicates if no unsubmitted within them
					continue;
				}

				// count those already submitted
				$submitted = count($noteIDs) - $res_check->num_rows();

				// assume removing all unsubmitted
				$to_remove = $res_check->num_rows();

				// if none submitted, remove all but 1
				if ($submitted == 0) {
					$to_remove--;
				}

				// if at least one to remove
				if ($to_remove >= 1) {
					$this->db->where_in('noteID', $noteIDs);
					$this->db->delete('bookings_lessons_notes', $where, $to_remove);
				}
			}
		}
    }

    public function down() {
        // no going back
    }
}
