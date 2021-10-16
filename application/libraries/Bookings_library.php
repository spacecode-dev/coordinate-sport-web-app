<?php

class Bookings_library
{
    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function getStaffAttachedToBooking($bookingId) {
        $query = $this->CI->db->select('DISTINCT(app_staff.staffID), 
        staff.first, 
        staff.surname, 
        staff.qual_first_issue_date, 
        staff.qual_first_expiry_date, 
        staff.qual_child_issue_date, 
        staff.qual_child_expiry_date, 
        staff.qual_fsscrb_issue_date, 
        staff.qual_fsscrb_expiry_date, 
        staff.qual_fsscrb_ref, 
        staff.qual_othercrb_issue_date, 
        staff.qual_othercrb_expiry_date, 
        staff.qual_othercrb_ref, 
        staff.accountID')
            ->from('bookings')
            ->where([
                'bookings.bookingID' => $bookingId
            ])
            ->join('bookings_lessons_staff', 'bookings.bookingID = bookings_lessons_staff.bookingID', 'left')
            ->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'left')
            ->get();

        $result = [];
        if ($query->num_rows() < 1) {
            return $result;
        }

        foreach ($query->result() as $row) {
            $result[] = $row;
        }

        $query = $this->CI->db->select('DISTINCT(app_staff.staffID), 
                staff.first, 
                staff.surname, 
                staff.qual_first_issue_date, 
                staff.qual_first_expiry_date, 
                staff.qual_child_issue_date, 
                staff.qual_child_expiry_date, 
                staff.qual_fsscrb_issue_date, 
                staff.qual_fsscrb_expiry_date, 
                staff.qual_fsscrb_ref, 
                staff.qual_othercrb_issue_date, 
                staff.qual_othercrb_expiry_date, 
                staff.qual_othercrb_ref,
                bookings_lessons_exceptions.type, 
                staff.accountID')
            ->from('bookings_lessons_exceptions')
            ->where_in('bookings_lessons_exceptions.bookingID', $bookingId)
            ->join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'left')
            ->get();

        $exceptions = [];
        foreach ($query->result() as $row) {
            $exceptions[] = $row;
        }

        foreach ($exceptions as $exception) {
        	if (empty($exception->staffID))
        		continue;

            if ($exception->type == 'staffchange') {
                $result[$exception->staffID] = $exception;
            }
        }

        return $result;
    }

    public function getStaffAttachedToLessons($lessonIds) {
        $query = $this->CI->db->select('DISTINCT(app_staff.staffID), 
                staff.first, 
                staff.surname, 
                staff.qual_first_issue_date, 
                staff.qual_first_expiry_date, 
                staff.qual_child_issue_date, 
                staff.qual_child_expiry_date, 
                staff.qual_fsscrb_issue_date, 
                staff.qual_fsscrb_expiry_date, 
                staff.qual_fsscrb_ref, 
                staff.qual_othercrb_issue_date, 
                staff.qual_othercrb_expiry_date, 
                staff.qual_othercrb_ref, 
                staff.accountID')
            ->from('bookings_lessons_staff')
            ->where_in('bookings_lessons_staff.lessonID', $lessonIds)
            ->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'left')
            ->get();

        $result = [];
        if ($query->num_rows() < 1) {
            return $result;
        }

        foreach ($query->result() as $row) {
            $result[$row->staffID] = $row;
        }

        $query = $this->CI->db->select('DISTINCT(app_staff.staffID), 
                staff.first, 
                staff.surname, 
                staff.qual_first_issue_date, 
                staff.qual_first_expiry_date, 
                staff.qual_child_issue_date, 
                staff.qual_child_expiry_date, 
                staff.qual_fsscrb_issue_date, 
                staff.qual_fsscrb_expiry_date, 
                staff.qual_fsscrb_ref, 
                staff.qual_othercrb_issue_date, 
                staff.qual_othercrb_expiry_date, 
                staff.qual_othercrb_ref,
                bookings_lessons_exceptions.type, 
                staff.accountID')
            ->from('bookings_lessons_exceptions')
            ->where_in('bookings_lessons_exceptions.lessonID', $lessonIds)
            ->join('staff', 'bookings_lessons_exceptions.staffID = staff.staffID', 'left')
            ->get();


        $exceptions = [];
        foreach ($query->result() as $row) {
            $exceptions[] = $row;
        }

        foreach ($exceptions as $exception) {
			if (empty($exception->staffID))
				continue;

            if ($exception->type == 'staffchange') {
                $result[$exception->staffID] = $exception;
            }
        }

        return $result;
    }
}
