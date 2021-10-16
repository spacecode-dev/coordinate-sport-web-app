<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Marketing extends MY_Controller
{

	public function __construct()
	{
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports'));

		// load library
		$this->load->library('reports_library');
	}

	public function index($action = FALSE)
	{

		// set defaults
		$icon = 'book';
		$current_page = 'marketing';
		$section = 'reports';
		$page_base = 'reports/marketing';
		$title = 'Marketing Data & Privacy Report';
		$buttons = ' <a class="btn btn-primary" href="' . site_url('reports/marketing/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$export = FALSE;

		// check if exporting
		if ($action == 'export') {
			$export = TRUE;
			$this->pagination_library->is_search();
		}

		// set up search
		$search_fields = array(
			'name' => NULL,
			'marketing_consent' => NULL,
			'privacy_policy' => NULL,
			'newsletters' => NULL,
			'referral_data' => NULL,
			'date_from' => NULL,
			'date_to' => NULL,
			'age_from' => NULL,
			'age_to' => NULL,
			'lessons' => NULL,
			'postcode' => NULL,
			'activities' => NULL,
			'departments' => NULL,
			'marketing_order' => 'asc',
			'schoolID' => NULL,
			'search' => NULL
		);

		$is_search = false;
		$form_submitted = false;
		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('marketing_consent', 'Marketing Consent', 'trim|xss_clean');
			$this->form_validation->set_rules('privacy_policy', 'Privacy Policy', 'trim|xss_clean');
			$this->form_validation->set_rules('newsletters', 'Newsletters', 'trim|xss_clean');
			$this->form_validation->set_rules('referral_data', 'Referral data', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_age_from', 'Start Age', 'trim|xss_clean');
			$this->form_validation->set_rules('search_age_to', 'End Age', 'trim|xss_clean');
			$this->form_validation->set_rules('lessons', 'Lessons', 'trim|xss_clean');
			$this->form_validation->set_rules('postcode', 'End Age', 'trim|xss_clean');
			$this->form_validation->set_rules('activities', 'Activities', 'trim|xss_clean');
			$this->form_validation->set_rules('departments', 'Departments', 'trim|xss_clean');
			$this->form_validation->set_rules('marketing_order', 'Marketing Order', 'trim|xss_clean');
			$this->form_validation->set_rules('search_schoolID', 'School', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['marketing_consent'] = set_value('search_marketing_consent');
			$search_fields['privacy_policy'] = set_value('search_privacy_policy');
			$search_fields['newsletters'] = set_value('search_newsletters');
			$search_fields['referral_data'] = set_value('search_referral_data');
			$search_fields['date_from'] = set_value('search_date_from') == '' ? NULL : set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to') == '' ? NULL : set_value('search_date_to');
			$search_fields['activities'] = set_value('search_activities');
			$search_fields['departments'] = set_value('search_departments');
			$search_fields['schoolID'] = set_value('search_schoolID');
			$search_fields['lessons'] = set_value('search_lessons');
			$search_fields['marketing_order'] = set_value('marketing_order');

			$search_fields['age_from'] =
				(set_value('search_age_from') == '' || !preg_match('/^[1-9][0-9]*$/', set_value('search_age_from')))
				? NULL : set_value('search_age_from');
			$search_fields['age_to'] =
				(set_value('search_age_to') == '' || !preg_match('/^[1-9][0-9]*$/', set_value('search_age_to')))
					? NULL : set_value('search_age_to');

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			// if from after to, reset
			if (
				!is_null($search_fields['date_from'])
				&& !is_null($search_fields['date_to'])
				&&strtotime(uk_to_mysql_date($search_fields['date_from'])) > strtotime(uk_to_mysql_date($search_fields['date_to']))
			) {
				$search_fields['date_to'] = NULL;
				$search_fields['date_from'] = NULL;
			}

			if ($search_fields['date_to'] || $search_fields['date_from']) {
				$search_fields['postcode'] = set_value('search_postcode');
			}
			$is_search = true;
			$this->pagination_library->is_search();
			$form_submitted = TRUE;
		} else if (($export == TRUE || $this->crm_library->last_segment() == 'recall') && is_array($this->session->userdata('search-reports'))) {
			foreach ($this->session->userdata('search-reports') as $key => $value) {
				$search_fields[$key] = $value;
			}
		}

		if ($is_search) {
			// store search fields
			$this->session->set_userdata('search-reports', $search_fields);
		}
		
		if ($this->input->get('order')) {
			$order_type = $this->input->get('order');
			if($order_type["name"] == 'desc')
				$order = 'family_contacts.first_name desc, family_contacts.last_name desc';
			else
				$order = 'family_contacts.first_name asc, family_contacts.last_name asc';
		} else {
			$order = 'family_contacts.first_name asc, family_contacts.last_name asc';
		}

		$search_where = [];
		$search_where[] = $this->db->dbprefix('family_contacts') . '.accountID = '. $this->auth->user->accountID;
		if (!is_null($search_fields['date_from'])) {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
			$search_where[] = "marketing_consent_date > '$date_from' OR marketing_consent_date = '$date_from'" ;
		}
		if (!is_null($search_fields['date_to'])) {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
			$search_where[] = "(marketing_consent_date < '$date_to' OR marketing_consent_date = '$date_to')" ;
		}

		if ($search_fields['name'] != '') {
			$search_where[] = 'CONCAT(' . $this->db->dbprefix('family_contacts') . '.first_name,'
				. $this->db->dbprefix('family_contacts') . '.last_name) LIKE \'%' . $search_fields['name'] . '%\'';
		}
		if ($search_fields['postcode'] != '') {
			$search_where[] = 'CONCAT(' . $this->db->dbprefix('family_contacts') . '.postcode,'
				. $this->db->dbprefix('family_contacts') . '.postcode) LIKE \'%' . $search_fields['postcode'] . '%\'';
		}
		if ($search_fields['marketing_consent'] == 'yes') {
			$search_where[] = 'marketing_consent = 1';
		} elseif ($search_fields['marketing_consent'] == 'no') {
			$search_where[] = 'marketing_consent != 1';
		}
		if ($search_fields['privacy_policy'] == 'yes') {
			$search_where[] = 'privacy_agreed = 1';
		} elseif ($search_fields['privacy_policy'] == 'no') {
			$search_where[] = 'privacy_agreed != 1';
		}
		if ($search_fields['referral_data'] == 'yes') {
			$search_where[] = 'source IS NOT NULL';
			$search_where[] = 'source_other IS NOT NULL';
		} elseif ($search_fields['referral_data'] == 'no') {
			$search_where[] = 'source IS NULL';
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}
		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		 $query = $this->db
			->select('family_contacts.first_name,'
				. 'family_contacts.last_name,'
				. 'family_contacts.marketing_consent AS marketing_consent,'
				. 'family_contacts.marketing_consent_date,'
				. 'family_contacts.privacy_agreed AS privacy_agreed,'
				. 'family_contacts.privacy_agreed_date,'
				. 'family_contacts.email,'
				. 'family_contacts.mobile,'
				. 'family_contacts.source AS source,'
				. 'family_contacts.source_other AS source_other,'
				. 'YEAR(CURDATE()) - YEAR(' . $this->db->dbprefix('family_contacts').'.dob) AS age,'
				. "GROUP_CONCAT( DISTINCT " . $this->db->dbprefix('brands') . ".name ORDER BY " . $this->db->dbprefix('brands') . ".name SEPARATOR ', ') AS newsletters,"
				. "GROUP_CONCAT( DISTINCT YEAR(CURDATE()) - YEAR(" . $this->db->dbprefix('family_children') . ".dob) SEPARATOR ',r') AS children_age"
			)->from('family_contacts')
			->join('family_contacts_newsletters', 'family_contacts.contactID = family_contacts_newsletters.contactID', 'left')
			->join('family_children', 'family_contacts.familyID = family_children.familyID', 'left')
			->join('brands', 'family_contacts_newsletters.brandID = brands.brandID', 'left')
			->where($search_where, NULL, FALSE)
			->group_by('family_contacts.familyID');
		if (($search_fields['newsletters'] != '')) {
			$query->having('newsletters LIKE \'%' . $search_fields['newsletters'] . '%\'');
		}
		if (($search_fields['lessons'] != '')) {
			$query->join(
				'(
					SELECT `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`, `activityID`
					FROM `'. $this->db->dbprefix('bookings_cart') .'`
					LEFT JOIN `'. $this->db->dbprefix('bookings_cart_sessions') .'`
					ON `'. $this->db->dbprefix('bookings_cart') .'`.`cartID` = `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`cartID`
					INNER JOIN `'. $this->db->dbprefix('bookings_lessons') .'`
					ON `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`bookingID` = `'. $this->db->dbprefix('bookings_lessons') .'`.`bookingID`
					AND `'. $this->db->dbprefix('bookings_lessons') .'`.`typeID` = ' . $this->db->escape($search_fields['lessons']) .'
					GROUP BY `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`
				   ) as lessons', ''. $this->db->dbprefix('family_contacts') .'.contactID = lessons.contactID', 'inner'
			);
		}

		if(($search_fields['activities'] != '')) {
			$query->join(
				'(
					SELECT `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`, `activityID`
					FROM `'. $this->db->dbprefix('bookings_cart') .'`
					LEFT JOIN `'. $this->db->dbprefix('bookings_cart_sessions') .'`
					ON `'. $this->db->dbprefix('bookings_cart') .'`.`cartID` = `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`cartID`
					INNER JOIN `'. $this->db->dbprefix('bookings_lessons') .'`
					ON `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`bookingID` = `'. $this->db->dbprefix('bookings_lessons') .'`.`bookingID`
					AND `'. $this->db->dbprefix('bookings_lessons') .'`.`activityID` = ' . $this->db->escape($search_fields['activities']) .'
					GROUP BY `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`
				   ) as activity', ''. $this->db->dbprefix('family_contacts') .'.contactID = activity.contactID', 'inner'
			);
		}
		
		// filter by schsool
		if ($search_fields['schoolID'] != '') {
			$query->join(
				'(
					SELECT `familyID`
					FROM `'. $this->db->dbprefix('family_children') .'`
					WHERE `orgID` = ' .  $this->db->escape($search_fields['schoolID']) . '
					GROUP BY `familyID`
				   ) as children', ''. $this->db->dbprefix('family_contacts') .'.familyID = children.familyID', 'inner'
			);
		}

		if(($search_fields['departments'] != '')) {
			$query->join(
				'(
					SELECT `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`, `brandID`
					FROM `'. $this->db->dbprefix('bookings_cart') .'`
					LEFT JOIN `'. $this->db->dbprefix('bookings_cart_sessions') .'`
					ON `'. $this->db->dbprefix('bookings_cart') .'`.`cartID` = `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`cartID`
					INNER JOIN `'. $this->db->dbprefix('bookings') .'`
					ON `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`bookingID` = `'. $this->db->dbprefix('bookings') .'`.`bookingID`
					AND `'. $this->db->dbprefix('bookings') .'`.`brandID` = ' . $this->db->escape($search_fields['departments']) .'
					GROUP BY `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`
				   ) as department', ''. $this->db->dbprefix('family_contacts') .'.contactID = department.contactID', 'inner'
			);
		}

		$res = $query->get();
		$total_items = $res->num_rows();
		$pagination = $this->pagination_library->calc($total_items);
		
		// run again but limited
		$query = $this->db
			->select('family_contacts.first_name,'
				. 'family_contacts.last_name,'
				. 'family_contacts.marketing_consent AS marketing_consent,'
				. 'family_contacts.marketing_consent_date,'
				. 'family_contacts.privacy_agreed AS privacy_agreed,'
				. 'family_contacts.privacy_agreed_date,'
				. 'family_contacts.email,'
				. 'family_contacts.mobile,'
				. 'family_contacts.source AS source,'
				. 'family_contacts.source_other AS source_other,'
				. 'YEAR(CURDATE()) - YEAR(' . $this->db->dbprefix('family_contacts').'.dob) AS age,'
				. "GROUP_CONCAT( DISTINCT " . $this->db->dbprefix('brands') . ".name ORDER BY " . $this->db->dbprefix('brands') . ".name SEPARATOR ', ') AS newsletters,"
				. "GROUP_CONCAT( DISTINCT YEAR(CURDATE()) - YEAR(" . $this->db->dbprefix('family_children') . ".dob) SEPARATOR ',r') AS children_age"
			)->from('family_contacts')
			->join('family_contacts_newsletters', 'family_contacts.contactID = family_contacts_newsletters.contactID', 'left')
			->join('family_children', 'family_contacts.familyID = family_children.familyID', 'left')
			->join('brands', 'family_contacts_newsletters.brandID = brands.brandID', 'left')
			->where($search_where, NULL, FALSE)
			->group_by('family_contacts.familyID')
			->order_by($order)
			->limit($this->pagination_library->amount, $this->pagination_library->start);
		if (($search_fields['newsletters'] != '')) {
			$query->having('newsletters LIKE \'%' . $search_fields['newsletters'] . '%\'');
		}
		if (($search_fields['lessons'] != '')) {
			$query->join(
				'(
					SELECT `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`, `activityID`
					FROM `'. $this->db->dbprefix('bookings_cart') .'`
					LEFT JOIN `'. $this->db->dbprefix('bookings_cart_sessions') .'`
					ON `'. $this->db->dbprefix('bookings_cart') .'`.`cartID` = `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`cartID`
					INNER JOIN `'. $this->db->dbprefix('bookings_lessons') .'`
					ON `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`bookingID` = `'. $this->db->dbprefix('bookings_lessons') .'`.`bookingID`
					AND `'. $this->db->dbprefix('bookings_lessons') .'`.`typeID` = ' . $this->db->escape($search_fields['lessons']) .'
					GROUP BY `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`
				   ) as lessons', ''. $this->db->dbprefix('family_contacts') .'.contactID = lessons.contactID', 'inner'
			);
		}

		if(($search_fields['activities'] != '')) {
			$query->join(
				'(
					SELECT `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`, `activityID`
					FROM `'. $this->db->dbprefix('bookings_cart') .'`
					LEFT JOIN `'. $this->db->dbprefix('bookings_cart_sessions') .'`
					ON `'. $this->db->dbprefix('bookings_cart') .'`.`cartID` = `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`cartID`
					INNER JOIN `'. $this->db->dbprefix('bookings_lessons') .'`
					ON `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`bookingID` = `'. $this->db->dbprefix('bookings_lessons') .'`.`bookingID`
					AND `'. $this->db->dbprefix('bookings_lessons') .'`.`activityID` = ' . $this->db->escape($search_fields['activities']) .'
					GROUP BY `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`
				   ) as activity', ''. $this->db->dbprefix('family_contacts') .'.contactID = activity.contactID', 'inner'
			);
		}

		if(($search_fields['departments'] != '')) {
			$query->join(
				'(
					SELECT `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`, `brandID`
					FROM `'. $this->db->dbprefix('bookings_cart') .'`
					LEFT JOIN `'. $this->db->dbprefix('bookings_cart_sessions') .'`
					ON `'. $this->db->dbprefix('bookings_cart') .'`.`cartID` = `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`cartID`
					INNER JOIN `'. $this->db->dbprefix('bookings') .'`
					ON `'. $this->db->dbprefix('bookings_cart_sessions') .'`.`bookingID` = `'. $this->db->dbprefix('bookings') .'`.`bookingID`
					AND `'. $this->db->dbprefix('bookings') .'`.`brandID` = ' . $this->db->escape($search_fields['departments']) .'
					GROUP BY `'. $this->db->dbprefix('bookings_cart') .'`.`contactID`
				   ) as department', ''. $this->db->dbprefix('family_contacts') .'.contactID = department.contactID', 'inner'
			);
		}
		
		// filter by schsool
		if ($search_fields['schoolID'] != '') {
			$query->join(
				'(
					SELECT `familyID`
					FROM `'. $this->db->dbprefix('family_children') .'`
					WHERE `orgID` = ' .  $this->db->escape($search_fields['schoolID']) . '
					GROUP BY `familyID`
				   ) as children', ''. $this->db->dbprefix('family_contacts') .'.familyID = children.familyID', 'inner'
			);
		}
		
		$row_data = $query->get();
		
		
		$newsletters = $this->db->select('name')->from('brands')->get();
		$newslettersOptions = ['' => 'Select'];
		foreach ($newsletters->result() as $newsletters) {
			$newslettersOptions[$newsletters->name] = $newsletters->name;
		}

		$lessons = $this->settings_library->getSessionTypes($this->auth->user->accountID);
		$lessonsOptions = ['' => 'Select'];
		foreach ($lessons as $lesson) {
			$lessonsOptions[$lesson->typeID] = $lesson->name;
		}

		$activities = $this->settings_library->getActivities($this->auth->user->accountID);

		$activitiesOptions = ['' => 'Select'];
		foreach($activities as $result) {
			$activitiesOptions[$result->activityID] = $result->name;
		}

		$departments = $this->settings_library->getDepartments($this->auth->user->accountID);

		$departmentsOptions = ['' => 'Select'];
		foreach($departments as $department) {
			$departmentsOptions[$department->brandID] = $department->name;
		}

		// get schools
		$schools = $this->settings_library->getSchools($this->auth->user->accountID);

		$schoolsOptions = ['' => 'Select'];
		foreach($schools as $school) {
			$schoolsOptions[$school->orgID] = $school->name;
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'row_data' => $row_data,
			'page_base' => $page_base,
			'newsletters_options' => $newslettersOptions,
			'lessons_options' => $lessonsOptions,
			'activities_options' => $activitiesOptions,
			'departments_options' => $departmentsOptions,
			'form_submitted' => $form_submitted,
			'schools_options' => $schoolsOptions,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');
			$view = 'reports/marketing-export';
			$this->load->view($view, $data);
		} else {
			$this->crm_view('reports/marketing', $data);
		}
	}

}

