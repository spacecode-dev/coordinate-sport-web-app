<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_orphan_evaluations extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // look up orphan evaluations
            $where = array(
                'bookings_lessons_notes.type' => 'evaluation',
                'bookings_lessons_staff.recordID' => NULL,
                'bookings_lessons_exceptions.exceptionID' => NULL
            );
            $res = $this->db->from('bookings_lessons_notes')->join('bookings_lessons_staff', 'bookings_lessons_notes.byID = bookings_lessons_staff.staffID and bookings_lessons_notes.lessonID = bookings_lessons_staff.lessonID', 'left')->join('bookings_lessons_exceptions', 'bookings_lessons_notes.byID = bookings_lessons_exceptions.staffID and bookings_lessons_notes.lessonID = bookings_lessons_exceptions.lessonID', 'left')->where($where)->get();

            // delete them
            if ($res->num_rows() > 0) {
                foreach ($res->result() as $row) {
                    $where = array(
                        'noteID' => $row->noteID
                    );
                    $this->db->delete('bookings_lessons_notes', $where, 1);
                }
            }
        }

        public function down() {
            // no going back
        }
}