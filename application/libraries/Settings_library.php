<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Settings_library {

	private $CI;
	private $cache = array();
	private $dashboard_cache = array();
	private $label_cache = array();
	private $label_defaults = array(
		'brand' => 'Department',
		'brands' => 'Departments',
		'customer' => 'Customer',
		'customers' => 'Customers',
		'participant' => 'Participant',
		'participants' => 'Participants',
		'adult' => 'Adult',
		'adults' => 'Adults',
		'adults_children' => 'Adults & Children',
	);
	private $levels_cache = array();
	private $levels_defaults = array(
		'directors' => 'Super User',
		'management' => 'Management',
		'office' => 'Office',
		'headcoach' => 'Team Leader',
		'fulltimecoach' => 'Salaried Coach',
		'coaching' => 'Coaches'
	);
	private $levels_reports = array(
		'management' => 'Management',
		'office' => 'Office',
		'headcoach' => 'Team Leader',
		'fulltimecoach' => 'Salaried Coach',
		'coaching' => 'Coaches'
	);
	private $staffing_types_cache = array();
	public $staffing_types_defaults = array(
		'head' => 'Head Coach',
		'lead' => 'Lead Coach',
		'assistant' => 'Assistant Coach',
		'participant' => 'Participant',
		'observer' => 'Observer'
	);
	public $staffing_types_required_for_sessions = [
		'head' => 'Head Coach',
		'lead' => 'Lead Coach',
		'assistant' => 'Assistant Coach'
	];
	public $staffing_types_default_for_payscales = [
		'head' => 'Head Coach',
		'assistant' => 'Assistant Coach'
	];
	public $ethnic_origins = array(
		'Prefer not to say' => 'Prefer not to say',
		'Asian/Asian British - Indian' => 'Asian/Asian British - Indian',
		'Asian/Asian British - Pakistani' => 'Asian/Asian British - Pakistani',
		'Asian/Asian British - Chinese' => 'Asian/Asian British - Chinese',
		'Asian/Asian British - Bangladeshi' => 'Asian/Asian British - Bangladeshi',
		'Asian/Asian British - Other' => 'Asian/Asian British - Other',
		'Black/African/Caribbean/Black British - African' => 'Black/African/Caribbean/Black British - African',
		'Black/African/Caribbean/Black British - Caribbean' => 'Black/African/Caribbean/Black British - Caribbean',
		'Black/African/Caribbean/Black British - Other' => 'Black/African/Caribbean/Black British - Other',
		'Mixed/multiple ethnic groups - White and Asian' => 'Mixed/multiple ethnic groups - White and Asian',
		'Mixed/multiple ethnic groups - White and Black African' => 'Mixed/multiple ethnic groups - White and Black African',
		'Mixed/multiple ethnic groups - White and Black Caribbean' => 'Mixed/multiple ethnic groups - White and Black Caribbean',
		'Mixed/multiple ethnic groups - Other' => 'Mixed/multiple ethnic groups - Other',
		'White - Welsh/English/Scottish/Northern Irish/British' => 'White - Welsh/English/Scottish/Northern Irish/British',
		'White - Irish' => 'White - Irish',
		'White - Gypsy, Roma or Irish Traveller' => 'White - Gypsy, Roma or Irish Traveller',
		'White - Eastern European' => 'White - Eastern European',
		'White - Other' => 'White - Other',
		'Other ethnic group - Arab' => 'Other ethnic group - Arab',
	);

	public $religions = array(
		"christianity" => 'Christianity (All Denominations)',
		"hinduism" => 'Hinduism',
		"jewish_judaism" => 'Jewish / Judaism',
		"islam" => 'Islam (Muslim)',
		"sikhism" => 'Sikhism',
		"no_religion" => 'No Religion',
		"prefer_not_to_say" => 'Prefer not to say',
		"please_specify" => 'Other (please specify)'
	);

	public $sexual_orientations = array(
		"bisexual" => 'Bisexual',
		"gay_man" => 'Gay Man',
		"gay_woman_lesbian" => 'Gay Woman/Lesbian',
		"heterosexual" => 'Heterosexual',
		"prefer_not_to_say" => 'Prefer not to say',
		"please_specify" => 'Other (please specify)'
	);

	public $disabilities = array(
		"not_applicable" => "Not Applicable",
		"hearing" => "Hearing (Deaf, Partially Deaf, Hard of Hearing)",
		"learning_difficulty" => "Learning difficulty (e.g. Dyspraxia, Dyslexia, ADHD Asperger’s Syndrome, Dyscalculia)",
		"learning_disability" => "Learning disability (e.g. Down's syndrome, Aspergers MLD, SLD)",
		"mental_health_condition" => "Mental health condition (e.g. Anxiety, depression, schizophrenia, dementia)",
		"physical_ambulant" => "Physical - ambulant (I do not use a wheelchair)",
		"physical_wheelchair" => "Physical - wheelchair user",
		"sight" => "Sight (blind or partially-sighted)",
		"other" => "Other",
		"prefer_not_to_say" => "Prefer not to say"
	);

	public $additonal_roles = [
		'travel' => 'Travel',
		'training' => 'Training',
		'marketing' => 'Marketing',
		'admin' => 'Admin',
		'other' => 'Other'
	];

	const TAGS = [
		'new_booking_email' => [
			'contact_name',
			'org_name',
			'brand',
			'date_description',
			'details',
			'first_aid',
			'child_protection',
			'company_dbs',
			'other_dbs'
		]
	];

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
	}

	public function get_tags($tag) {
		return self::TAGS[$tag];
	}

	/**
	 * get sections for main table listing
	 * @param $section
	 * @return array
	 */
	public function get_main_sections($section) {
		$query = $this->CI->db->select()->from('settings')
			->where([
				'section' => $section . '-main'
			])
			->order_by('order asc')
			->get();

		$result = [];

		foreach ($query->result() as $row) {
			$result[] = $row;
		}

		return $result;
	}

	public function get_subsection_data($section, $subsection) {
		$query = $this->CI->db->select()->from('settings')
			->where([
				'section' => $section,
				'subsection' => $subsection
			])
			->order_by('order asc')
			->get();

		$result = [];

		foreach ($query->result() as $row) {
			$result[] = $row;
		}

		return $result;
	}

	/**
	 * getting required staff for sessions
	 * @return array
	 */
	public function get_required_staff_for_session() {
		$staffing_types = [];
		$query = $this->CI->db->select()->from('staffing_types')
			->where([
				'accountID' => $this->CI->auth->user->accountID,
			])->get();

		foreach ($query->result() as $row) {
			$staffing_types[$row->type] = $row;
		}

		$required_staff_for_session = $this->staffing_types_required_for_sessions;

		foreach ($staffing_types as $type => $staffing_type) {
			if ($staffing_type->required_for_session) {
				if (!isset($required_staff_for_session[$type])) {
					$required_staff_for_session[$type] = $staffing_type->name;
				}
			}

			if (array_key_exists($staffing_type->type, $required_staff_for_session) && !$staffing_type->required_for_session) {
				unset($required_staff_for_session[$type]);
			}
		}

		return $required_staff_for_session;
	}

	/**
	 * get staff list for payroll report
	 * @return array
	 */
	public function get_staff_for_payroll() {
		$staffing_types = [];

		$query = $this->CI->db->select()->from('staffing_types')
			->where([
				'accountID' => $this->CI->auth->user->accountID,
			])->get();

		foreach ($query->result() as $row) {
			$staffing_types[$row->type] = $row;
		}

		$staff_to_display = $this->staffing_types_required_for_sessions;


		foreach ($staffing_types as $type => $staffing_type) {
			if ($staffing_type->display_on_payroll) {
				if (!isset($staff_to_display[$type]) || (array_key_exists($staffing_type->type, $staff_to_display))) {
					$staff_to_display[$type] = $staffing_type->name;
				}
			}

			if (array_key_exists($staffing_type->type, $staff_to_display) && !$staffing_type->display_on_payroll) {
				unset($staff_to_display[$type]);
			}
		}

		return $staff_to_display;
	}

	public function getSettingInfo($key) {

		// set where
		$where = array(
			'key' => $key
		);

		// get default
		$query = $this->CI->db->from('settings')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 1) {
			foreach ($query->result() as $row) {
				return $row;
			}
		}

		return [];
	}

	public function updateSettingInfo($key, $data) {

		// set where
		$where = array(
			'key' => $key
		);

		// get default
		$query = $this->CI->db->update('settings', $data, $where);


		return true;
	}

	/**
	 * get a setting
	 * @param  string $key
	 * @return mixed
	 */
	public function get($key, $accountID = NULL) {

		// if no account id
		if (empty($accountID) && $this->CI->auth->user !== FALSE) {
			$accountID = $this->CI->auth->user->accountID;
		}

		// if no account id, use default
		if (empty($accountID)) {
			$accountID = 'default';
		}

		// lookup in cache
		if (array_key_exists($accountID, $this->cache) && array_key_exists($key, $this->cache[$accountID])) {
			return $this->cache[$accountID][$key];
		}

		// else get from db

		// set default
		$value = NULL;

		// set where
		$where = array(
			'key' => $key
		);

		// deny access to global vars if not accessing default
		if ($accountID != 'default') {
			$where['section !='] = 'global';
		}

		// get default
		$query = $this->CI->db->from('settings')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 1) {
			foreach ($query->result() as $row) {
				if ($row->type === 'permission-levels') {
					$value = explode(',', $row->value);
				} else {
					$value = trim($row->value);
				}
			}
		}

		// get account specific setting if not requesting default
		if ($accountID != 'default') {
			$where['accountID'] = $accountID;

			// if not whitelabel, use default styling, unless current user has access to accounts area
			if (!$this->CI->auth->has_features('whitelabel') && !$this->CI->auth->has_features('accounts') && !in_array($key, array('logo','favicon', 'online_booking_header_image', 'online_booking_css', 'online_booking_meta', 'online_booking_header', 'online_booking_footer')) && stripos($key, 'onlinebooking_search_') === FALSE) {
				$where['section !='] = 'styling';
			}

			// if limiting by section, change to search by default section as section doesn't exist in account settings table
			if (array_key_exists('section !=', $where)) {
				$where['settings.section !='] = $where['section !='];
				unset($where['section !=']);
			}

			// change key to search account settings
			$where['accounts_settings.key'] = $where['key'];
			unset($where['key']);

			// run query
			$query = $this->CI->db->select('accounts_settings.*, settings.type')->from('accounts_settings')->join('settings', 'accounts_settings.key = settings.key', 'inner')->where($where)->limit(1)->get();

			// check for result
			if ($query->num_rows() == 1) {
				foreach ($query->result() as $row) {
					if ($row->type === 'permission-levels') {
						$value = explode(',', $row->value);
					// if not empty or checkbox or number, use account value, else use default
					} else if (!empty($row->value) || in_array($row->type, array('checkbox', 'number'))) {
						$value = trim($row->value);
					}
				}
			}
		}

		// cache
		$this->cache[$accountID][$key] = $value;

		return $value;

	}

	/**
	 * save a setting to the cache
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value) {
		$this->cache[$key] = $value;
	}

	/**
	 * save a setting
	 * @param  string $key
	 * @return mixed
	 */
	public function save($key, $value, $accountID = NULL) {

		// if no account id
		if (empty($accountID)) {
			return FALSE;
		}

		// check key exists
		$where = array(
			'key' => $key
		);

		// check exists
		$query = $this->CI->db->from('settings')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 0) {
			return FALSE;
		}

		$data = array(
			'value' => trim($value),
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		);

		$where = array(
			'key' => $key,
			'accountID' => $accountID
		);

		// check if exists
		$res = $this->CI->db->from('accounts_settings')->where($where)->get();

		if ($res->num_rows() > 0) {
			// update
			$query = $this->CI->db->update('accounts_settings', $data, $where);
		} else {
			// insert
			$data['key'] = $key;
			$data['accountID'] = $accountID;
			$data['created_at'] = mdate('%Y-%m-%d %H:%i:%s');
			$query = $this->CI->db->insert('accounts_settings', $data);
		}

		// cache
		$this->cache[$accountID][$key] = trim($value);

		return $value;

	}

	/**
	 * get default setting information
	 * @param  string $key
	 * @return mixed
	 */
	public function get_field_info($key) {

		// set where
		$where = array(
			'key' => $key
		);

		// get default
		$query = $this->CI->db->from('settings')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 1) {
			foreach ($query->result() as $row) {
				return $row;
			}
		}

		// not found
		return false;

	}

	/**
	 * get label from plan
	 * @param  string $key
	 * @param  boolean $default
	 * @return mixed
	 */
	public function get_label($key, $default = FALSE) {

		// return default if requests
		if ($default === TRUE) {
			if (array_key_exists($key, $this->label_defaults)) {
				return $this->label_defaults[$key];
			}
			return FALSE;
		} else if (is_numeric($default)) {
			$accountID = $default;
		}

		// loop up in cache and return if exists
		if (array_key_exists($key, $this->label_cache)) {
			return $this->label_cache[$key];
		}

		if (!isset($accountID)) {
			$accountID = $this->CI->auth->user->accountID;
		}

		// look up account
		$where = array(
			'accountID' => $accountID,
			'active' => 1
		);
		$query = $this->CI->db->from('accounts')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 1) {
			// get account info
			foreach ($query->result() as $row) {
				// look up
				$where = array(
					'planID' => $row->planID
				);
				$query = $this->CI->db->from('accounts_plans')->where($where)->limit(1)->get();

				// check for result
				if ($query->num_rows() == 1) {
					foreach ($query->result() as $row) {
						$field = 'label_' . $key;
						if ($this->CI->db->field_exists($field, 'accounts_plans') && !empty($row->$field)) {
							// store in cache
							$this->label_cache[$key] = $row->$field;
							return $row->$field;
						}
					}
				}
			}
		}

		// return default if empty or not found
		if (array_key_exists($key, $this->label_defaults)) {
			return $this->label_defaults[$key];
		}
		return FALSE;
	}

	public function get_permission_levels() {
		return $this->levels_reports;
	}

	/**
	 * get permission level label
	 * @param  string $department
	 * @param  boolean $default
	 * @return mixed
	 */
	public function get_permission_level_label($department, $default = FALSE)	{
		// return default if requests
		if ($default === TRUE) {
			if (array_key_exists($department, $this->levels_defaults)) {
				return $this->levels_defaults[$department];
			}
			return FALSE;
		}

		// loop up in cache and return if exists
		if (array_key_exists($department, $this->levels_cache)) {
			return $this->levels_cache[$department];
		}

		// look up to see if has value
		$where = array(
			'accountID' => $this->CI->auth->user->accountID,
			'department' => $department,
			'department !=' => 'directors' // dont allow custom names for super user
		);
		$query = $this->CI->db->from('permission_levels')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 1) {
			// get account info
			foreach ($query->result() as $row) {
				// store in cache
				$this->levels_cache[$department] = $row->name;
				return $row->name;
			}
		}

		// return default if empty or not found
		if (array_key_exists($department, $this->levels_defaults)) {
			return $this->levels_defaults[$department];
		}
		return FALSE;
	}

	/**
	 * get staffing type label
	 * @param  string $type
	 * @param  boolean $default
	 * @return mixed
	 */
	public function get_staffing_type_label($type, $default = FALSE)	{
		// return default if requests
		if ($default === TRUE) {
			if (array_key_exists($type, $this->staffing_types_defaults)) {
				return $this->staffing_types_defaults[$type];
			}
			return FALSE;
		}

		// loop up in cache and return if exists
		if (array_key_exists($type, $this->staffing_types_cache)) {
			return $this->staffing_types_cache[$type];
		}

		// look up to see if has value
		$where = array(
			'accountID' => $this->CI->auth->user->accountID,
			'type' => $type
		);
		$query = $this->CI->db->from('staffing_types')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 1) {
			// get account info
			foreach ($query->result() as $row) {
				// store in cache
				$this->staffing_types_cache[$type] = $row->name;
				return $row->name;
			}
		}

		// return default if empty or not found
		if (array_key_exists($type, $this->staffing_types_defaults)) {
			return $this->staffing_types_defaults[$type];
		}
		return FALSE;
	}

	/**
	 * get a dashboard trigger setting
	 * @param  string $key
	 * @return mixed
	 */
	public function get_dashboard_trigger($key, $accountID = NULL) {

		// if no account id
		if (empty($accountID) && $this->CI->auth->user !== FALSE) {
			$accountID = $this->CI->auth->user->accountID;
		}

		// if no account id, use default
		if (empty($accountID)) {
			$accountID = 'default';
		}

		// lookup in cache
		if (array_key_exists($accountID, $this->dashboard_cache) && array_key_exists($key, $this->dashboard_cache[$accountID])) {
			return $this->dashboard_cache[$accountID][$key];
		}

		// else get from db

		// set default
		$values = array(
			'amber' => NULL,
			'red' => NULL,
			'positive_only' => TRUE
		);

		// set where
		$where = array(
			'key' => $key
		);

		// get default
		$query = $this->CI->db->from('settings_dashboard')->where($where)->limit(1)->get();

		// check for result
		if ($query->num_rows() == 1) {
			foreach ($query->result() as $row) {
				$values['amber'] = trim($row->value_amber);
				$values['red'] = trim($row->value_red);
				if ($row->positive_only != 1) {
					$values['positive_only'] = FALSE;
				}
			}
		}

		// get account specific setting if not requesting default
		if ($accountID != 'default') {
			$where['accountID'] = $accountID;

			// run query
			$query = $this->CI->db->select('*')->from('accounts_settings_dashboard')->where($where)->limit(1)->get();

			// check for result
			if ($query->num_rows() == 1) {
				foreach ($query->result() as $row) {
					// if not empty, use account value, else use default
					if (!empty($row->value_amber)) {
						$values['amber'] = trim($row->value_amber);
					}
					if (!empty($row->value_red)) {
						$values['red'] = trim($row->value_red);
					}
				}
			}
		}

		// cache
		$this->dashboard_cache[$accountID][$key] = $values;

		return $values;

	}

	public function getSessionTypes($accountId = null) {
		$where = [
			'active' => 1
		];

		if (!empty($accountId)) {
			$where['lesson_types.accountID'] = $accountId;
		}

		$query = $this->CI->db->select()
			->from('lesson_types')
			->where($where)
			->order_by('name asc')
			->get();

		$result = [];
		if ($query->num_rows() < 1) {
			return $result;
		}

		foreach ($query->result() as $row) {
			$result[] = $row;
		}

		return $result;
	}

	public function getSessionTypeInfo($typeId) {
		$where = [
			'typeID' => $typeId
		];

		$query = $this->CI->db->select()
			->from('lesson_types')
			->where($where)
			->limit(1)
			->get();


		$result = [];
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				return $row;
			}
		}

		return $result;
	}


	public function getActivities($accountId = null) {
		$where = [
			'active' => 1
		];

		if (!empty($accountId)) {
			$where['activities.accountID'] = $accountId;
		}

		$query = $this->CI->db->select()
			->from('activities')
			->where($where)
			->order_by('name asc')
			->get();

		$result = [];
		if ($query->num_rows() < 1) {
			return $result;
		}

		foreach ($query->result() as $row) {
			$result[] = $row;
		}

		return $result;
	}

	public function getDepartments($accountId = null) {
		$where = [
			'active' => 1
		];

		if (!empty($accountId)) {
			$where['brands.accountID'] = $accountId;
		}

		$query = $this->CI->db->select()
			->from('brands')
			->where($where)
			->order_by('name asc')
			->get();

		$result = [];
		if ($query->num_rows() < 1) {
			return $result;
		}

		foreach ($query->result() as $row) {
			$result[] = $row;
		}

		return $result;
	}

	public function getSchools($accountId = null) {
		$where = [
			'type' => 'school'
		];

		if (!empty($accountId)) {
			$where['orgs.accountID'] = $accountId;
		}

		$query = $this->CI->db->select()
			->from('orgs')
			->where($where)
			->order_by('name asc')
			->get();

		$result = [];
		if ($query->num_rows() < 1) {
			return $result;
		}

		foreach ($query->result() as $row) {
			$result[] = $row;
		}

		return $result;
	}

	public function createGroup($data, $staffIds = null) {
		$this->CI->db->insert('groups', $data);

		$groupId = $this->CI->db->insert_id();

		if ($staffIds && is_array($staffIds)) {
			foreach ($staffIds as $staffId) {
				$this->CI->db->insert('staff_groups', [
					'accountID' => $data['accountID'],
					'groupID' => $groupId,
					'staffID' => $staffId,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				]);
			}
		}

		return $groupId;
	}

	public function updateGroup($groupId, $data, $staffIds = null) {
		$this->CI->db->update('groups', $data, [
			'groupID' => $groupId
		]);

		$this->CI->db->delete('staff_groups', [
			'groupID' => $groupId
		]);

		if (is_array($staffIds)) {
			foreach ($staffIds as $staffId) {
				$this->CI->db->insert('staff_groups', [
					'accountID' => $data['accountID'],
					'groupID' => $groupId,
					'staffID' => $staffId,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				]);
			}
		}
	}

	public function getGroupInfo($groupId) {
		$query = $this->CI->db->from('groups')->where([
			'groupID' => $groupId
		])->get();

		if ($query->num_rows() > 0) {
			return $query->row();
		}

		return null;
	}

	public function getGroups($where = [], $like_where = [], $start = null, $limit = null) {
		$query = $this->CI->db->from('groups');

		if (!empty($where)) {
			$query->where($where);
		}

		foreach ($like_where as $column => $value) {
			$query->like($column, $value);
		}

		if (!is_null($start) && !is_null($limit)){
			$query->limit($limit, $start);
		}

		$res = $query->get();

		$groups = [];
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$groups[] = $row;
			}
		}

		return $groups;
	}

	public function getStaffByGroup($groupId) {
		$query = $this->CI->db->from('staff_groups')
			->select('staff.staffID, staff.first, staff.surname')
			->where([
				'groupID' => $groupId
			])
			->join('staff', 'staff_groups.staffID = staff.staffID', 'inner')
			->order_by('linkID asc')
			->get();

		$staffList = [];
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$staffList[] = $row;
			}
		}

		return $staffList;
	}

	public function removeGroup($groupId) {
		$this->CI->db->delete('groups', [
			'groupID' => $groupId
		]);

		return $this->CI->db->affected_rows();
	}
}
