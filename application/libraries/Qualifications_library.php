<?php

class Qualifications_library
{
    const MANDATORY_QUALS_TABLE = 'mandatory_quals';

    const STAFF_QUALS_TABLE = 'staff_quals';

    const MANDATORY_QUALS = [
        'first' => 'First Aid',
        'child' => 'Child Protection',
        'fsscrb' => 'Company DBS',
        'othercrb' => 'Other DBS'
    ];

    const TAGS_TO_REPLACE = [
		'first' => '{first_aid}',
		'child' => '{child_protection}',
		'fsscrb' => '{company_dbs}',
		'othercrb' => '{other_dbs}'
	];

    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function createQualificationsTable($qualifications_data, $qualId) {
    	$table = '<div class="' . $qualId .'"><p><strong>' . self::MANDATORY_QUALS[$qualId] . '</strong></p>' .
			'<table border="1" width="100%">' .
			'<tbody>' .
			'<tr>' .
			'<th>' .
			'Staff Name' .
			'</th>' .
			(in_array($qualId, ['first', 'child']) ? '' :
				'<th>' .
				'DBS No.' .
				'</th>') .
			'<th>' .
			'Issue Date' .
			'</th>' .
			'<th>' .
			'Expiry Date' .
			'</th>' .
			'</tr>';

    	foreach ($qualifications_data as $data) {
			$ref = !isset($data[$qualId]['ref']) ? 'Unknown' : $data[$qualId]['ref'];


			$table .=
				'<tr>' .
				'<td>' . $data['name'] . '</td>' .
				(in_array($qualId, ['first', 'child']) ? '' :
					'<td>' .
					$ref .
					'</td>') .
				'<td>' . $data[$qualId]['issue_date'] . '</td>' .
				'<td>' . $data[$qualId]['expiry_date'] . '</td>' .
				'</tr>';
		}

		$table .=
			'</tbody>' .
			'</table></div>';

    	return $table;
	}

	public function collectQualificationsDataByBooking($bookingId, $qualId = null) {
		$defaultQuals = $this->getDefaultQuals();

		$this->CI->load->library('bookings_library');
		$this->CI->load->library('attachment_library');

		$staffList = $this->CI->bookings_library->getStaffAttachedToBooking($bookingId);

		$qualData = [];
		foreach ($staffList as $staff) {
			$attachments = $this->CI->attachment_library->getQualAttachments($staff->staffID, 'mandatory_quals', $staff->accountID);

			$qualData[] = [
				'name' => $staff->first . ' ' . $staff->surname,
				'attachments' => $attachments,
				'first' => [
					'issue_date' => mysql_to_uk_date($staff->qual_first_issue_date),
					'expiry_date' => mysql_to_uk_date($staff->qual_first_expiry_date),
					'name' => 'First Aid'
				],
				'child' => [
					'issue_date' => mysql_to_uk_date($staff->qual_child_issue_date),
					'expiry_date' => mysql_to_uk_date($staff->qual_child_expiry_date),
					'name' => 'Child Protection'
				],
				'fsscrb' => [
					'issue_date' => mysql_to_uk_date($staff->qual_fsscrb_issue_date),
					'expiry_date' => mysql_to_uk_date($staff->qual_fsscrb_expiry_date),
					'ref' => $staff->qual_fsscrb_ref,
					'name' => 'Company DBS'
				],
				'othercrb' => [
					'issue_date' => mysql_to_uk_date($staff->qual_othercrb_issue_date),
					'expiry_date' => mysql_to_uk_date($staff->qual_othercrb_expiry_date),
					'ref' => $staff->qual_othercrb_ref,
					'name' => 'Other DBS'
				]
			];
		}

		$attachToEmail = true;
		if (!isset($defaultQuals[$qualId])) {
			$attachToEmail = false;
		}

		return [
			'attach_to_email' => $attachToEmail,
			'defalt_quals' => $defaultQuals,
			'data' => $qualData
		];
	}

	public function qualificationsDataByLesson($lessons, $qualId = null) {
		$defaultQuals = $this->getDefaultQuals();

		$this->CI->load->library('bookings_library');
		$this->CI->load->library('attachment_library');

		$staffList = $this->CI->bookings_library->getStaffAttachedToLessons($lessons);

		$qualData = [];
		foreach ($staffList as $staff) {
			$attachments = $this->CI->attachment_library->getQualAttachments($staff->staffID, 'mandatory_quals', $staff->accountID);

			$qualData[] = [
				'name' => $staff->first . ' ' . $staff->surname,
				'attachments' => $attachments,
				'first' => [
					'issue_date' => mysql_to_uk_date($staff->qual_first_issue_date),
					'expiry_date' => mysql_to_uk_date($staff->qual_first_expiry_date),
					'name' => 'First Aid'
				],
				'child' => [
					'issue_date' => mysql_to_uk_date($staff->qual_child_issue_date),
					'expiry_date' => mysql_to_uk_date($staff->qual_child_expiry_date),
					'name' => 'Child Protection'
				],
				'fsscrb' => [
					'issue_date' => mysql_to_uk_date($staff->qual_fsscrb_issue_date),
					'expiry_date' => mysql_to_uk_date($staff->qual_fsscrb_expiry_date),
					'ref' => $staff->qual_fsscrb_ref,
					'name' => 'Company DBS'
				],
				'othercrb' => [
					'issue_date' => mysql_to_uk_date($staff->qual_othercrb_issue_date),
					'expiry_date' => mysql_to_uk_date($staff->qual_othercrb_expiry_date),
					'ref' => $staff->qual_othercrb_ref,
					'name' => 'Other DBS'
				]
			];
		}

		$attachToEmail = true;
		if (!isset($defaultQuals[$qualId])) {
			$attachToEmail = false;
		}

		return [
			'attach_to_email' => $attachToEmail,
			'defalt_quals' => $defaultQuals,
			'data' => $qualData
		];
	}

    /**
     * get all mandatory qualifications by account
     * @param $accountID
     * @return array
     */
    public function getMandatoryQuals($accountID, $includeSystemQuals = false) {
        $query = $this->CI->db->select()
            ->from(self::MANDATORY_QUALS_TABLE)
            ->where([
                'accountID' => $accountID
            ])
            ->get();

        $result = [];
        if ($query->num_rows() < 1) {
            return $result;
        }

        foreach ($query->result() as $qual) {
            $result[$qual->qualID] = $qual;
        }

        if ($includeSystemQuals) {
            $result = [];
            foreach (self::MANDATORY_QUALS as $id => $qual) {
                $result[$id] = $qual;
            }

            foreach ($query->result() as $qual) {
                $result[$qual->qualID] = $qual->name;
            }
        }

        return $result;
    }

    /**
     * get additional qualifications by staff
     * @param $staffID
     * @param $accountID
     * @return array
     */
    public function getAdditionalQuals($staffID, $accountID) {
        $query = $this->CI->db->select()
            ->from(self::STAFF_QUALS_TABLE)
            ->where([
                'staffID' => $staffID,
                'accountID' => $accountID
            ])
            ->get();

        $result = [];
        if ($query->num_rows() < 1) {
            return $result;
        }

        foreach ($query->result() as $qual) {
            $result[$qual->qualID] = $qual;
        }

        return $result;
    }

    /**
     * just return default qualifications
     * @return array
     */
    public function getDefaultQuals() {
        return self::MANDATORY_QUALS;
    }

    public function getDefaultQualsTags() {
    	return self::TAGS_TO_REPLACE;
	}

	public function getAllTags($accountID) {
		$query = $this->CI->db->select()
			->from(self::MANDATORY_QUALS_TABLE)
			->where([
				'accountID' => $accountID
			])
			->get();

		$result = [];
		if ($query->num_rows() < 1) {
			return $result;
		}

		foreach ($query->result() as $qual) {
			$result[$qual->qualID] = $qual->tag;
		}

		return $result;
	}
}
