<?php

require_once APPPATH.'repositories/Repository.php';

class LessonsRepository extends Repository
{
	public function __construct() {
		$this->table = 'bookings_lessons';
		$this->idField = 'lessonID';
		$this->CI = & get_instance();
	}

	public function getDetailedList($strictArraySearch, $likeArray, $customQuery) {
		$query = $this->CI->db
			->select('bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end,
			bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end,
			bookings_lessons.day, bookings_lessons.lessonID as id, orgs.name as booking_org,
			block_org.name as block_org, bookings_lessons.startTime, bookings_lessons.endTime,
			activities.name as activity, types.name as type_name, event_address.postcode as event_postcode,
			orgs_addresses.postcode as lesson_postcode, bookings_lessons.class_size, 
			event_contacts.tel as event_tel, block_contacts.tel as block_tel,
			event_contacts.name as event_main_contact, block_contacts.name as block_main_contact,
			orgs.regionID, orgs.areaID, bookings_blocks.orgID as block_orgID,
		 	block_org.regionID as block_regionID, block_org.areaID as block_areaID')
			->from($this->table);


		$where = [];
		if (!empty($strictArraySearch) && is_array($strictArraySearch)) {
			foreach ($strictArraySearch as $key => $value) {
				$where[$key] = $value;
			}

			$query->where($where);
		}

		$like = [];
		if (!empty($likeArray) && is_array($likeArray)) {
			foreach ($likeArray as $key => $value) {
				$like[$key] = $value;
			}

			$query->like($like);
		}



		if(!empty($customQuery)) {
			$searchWhere = '(' . implode(' AND ', $customQuery) . ')';
			$query->where($searchWhere, NULL, FALSE);
		}

		$query
			->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
			->join('bookings', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')
			->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')
			->join('orgs as block_org', 'bookings_blocks.orgID = block_org.orgID', 'left')
			->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')
			->join('lesson_types as types', 'types.typeID = bookings_lessons.typeID', 'left')
			->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
			->join('orgs_addresses as event_address', 'bookings.addressID = event_address.addressID', 'left')
			->join('orgs_contacts as event_contacts', 'orgs.orgID = event_contacts.orgID and event_contacts.isMain = 1', 'left')
			->join('orgs_contacts as block_contacts', 'block_org.orgID = block_contacts.orgID and block_contacts.isMain = 1', 'left')
			->order_by('bookings_lessons.lessonID desc');

		$result = [];
		foreach ($query->get()->result() as $value) {
			$result[] = $value;
		}
//		print $this->CI->db->last_query();
//		die();

		return $result;
	}
}
