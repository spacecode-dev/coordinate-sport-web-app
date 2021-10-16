<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Privacy extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants'));
	}

	/**
	 * show list of privacy logs
	 * @return void
	 */
	public function index($familyID = NULL) {

		if ($familyID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('family')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$family_info = $row;
		}

		// set defaults
		$icon = 'eye';
		$tab = 'privacy';
		$current_page = 'participants';
		$page_base = 'participants/privacy/' . $familyID;
		$section = 'participants';
		$title = 'Data & Privacy';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account'
 		);

		// set where
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID,
			'type' => 'privacy'
		);

		// run query
		$res = $this->db->from('family_notes')->where($where)->order_by('added desc')->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get contacts
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('main desc, first_name asc, last_name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'logs' => $res,
			'contacts' => $contacts,
			'familyID' => $familyID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('participants/privacy', $data);
	}

	/**
	 * edit a contact's privacy permissions
	 * @param  int $contactID
	 * @return void
	 */
	public function edit($contactID = NULL)
	{

		$contact_info = new stdClass();

		// check if editing
		if ($contactID != NULL) {

			// check if numeric
			if (!ctype_digit($contactID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'family_contacts.contactID' => $contactID,
				'family_contacts.accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->select('family_contacts.*, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('family_contacts_newsletters') . '.brandID SEPARATOR \',\') AS newsletters')->join('family_contacts_newsletters', 'family_contacts.contactID = family_contacts_newsletters.contactID', 'left')->from('family_contacts')->where($where)->group_by('family_contacts.contactID')->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$contact_info = $row;
				$familyID = $contact_info->familyID;
			}

		}

		// required
		if ($familyID == NULL) {
			show_404();
		}

		// look up family
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$family_info = $row;
		}

		// set defaults
		$title = $contact_info->first_name . ' ' . $contact_info->last_name;
		$submit_to = 'participants/privacy/edit/' . $contactID;
		$return_to = 'participants/privacy/' . $familyID;
		$icon = 'eye';
		$tab = 'privacy';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'Participant Account',
			'participants/privacy/' . $familyID => 'Data & Privacy'
 		);

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// if posted
		if ($this->input->post()) {

			$this->load->library('form_validation');

			// set validation rules
			$this->form_validation->set_rules('marketing_consent', 'Marketing Consent', 'trim|xss_clean');
			$this->form_validation->set_rules('privacy_agreed', 'Privacy Agreed', 'trim|required|xss_clean');

			$this->form_validation->set_rules('source', 'Source', 'trim|xss_clean');
			if ($this->input->post('source') == 'Other') {
				$this->form_validation->set_rules('source_other', 'Other (Please specify)', 'trim|required|xss_clean');
			} else {
				$this->form_validation->set_rules('source_other', 'Other (Please specify)', 'trim|xss_clean');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				if (set_value('privacy_agreed') != 1) {
					$errors[] = 'Privacy policy must be agreed to';
				} else {

					// all ok, prepare data
					$data = array(
						'marketing_consent' => intval(set_value('marketing_consent')),
						'marketing_consent_date' => mdate('%Y-%m-%d %H:%i:%s'),
						'privacy_agreed' => intval(set_value('privacy_agreed')),
						'privacy_agreed_date' => mdate('%Y-%m-%d %H:%i:%s'),
						'source' => null_if_empty(set_value('source')),
						'source_other' => null_if_empty(set_value('source_other')),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					);
					$where = array(
						'contactID' => $contactID,
						'familyID' => $familyID,
						'accountID' => $this->auth->user->accountID
					);

					// update
					$query = $this->db->update('family_contacts', $data, $where);

					// update newsletter
					if ($brands->num_rows() > 0) {
						$newsletters = $this->input->post('newsletters');
						if (!is_array($newsletters)) {
							$newsletters = array();
						}
						foreach ($brands->result() as $brand) {
							// set where
							$where = array(
								'brandID' => $brand->brandID,
								'contactID' => $contactID,
								'accountID' => $this->auth->user->accountID
							);

							// process
							if (in_array($brand->brandID, $newsletters)) {
								// check if exists
								$res = $this->db->from('family_contacts_newsletters')->where($where)->limit(1)->get();

								// if not, insert
								if ($res->num_rows() == 0) {
									$data = $where;
									$this->db->insert('family_contacts_newsletters', $data);
								}
							} else {
								// remove
								$this->db->delete('family_contacts_newsletters', $where, 1);
							}
						}
					}

					// insert note
					$details = 'Contact: ' . $contact_info->first_name . ' ' . $contact_info->last_name . '
					By: ' . $this->auth->user->first . ' ' . $this->auth->user->surname . ' (Staff)
					IP: ' . get_ip_address() . '
					Hostname: ' . gethostbyaddr(get_ip_address());
					$summary = 'Marketing Consent: ';
					if (set_value('marketing_consent') == 1) {
						$summary .= 'Yes';
					} else {
						$summary .= 'No';
					}
					$summary .= ', Privacy Agreed: ';
					if (set_value('privacy_agreed') == 1) {
						$summary .= 'Yes';
					} else {
						$summary .= 'No';
					}
					$summary .= ', Source: ';
					if (strtolower(set_value('source')) == 'other' && !empty(set_value('source_other'))) {
						$summary .= set_value('source_other');
					} else if (!empty(set_value('source'))) {
						$summary .= set_value('source');
					} else {
						$summary .= 'Unknown';
					}
					$data = array(
						'type' => 'privacy',
						'summary' => $summary,
						'content' => $details,
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'familyID' => $familyID,
						'accountID' => $this->auth->user->accountID,
						'byID' => $this->auth->user->byID
					);
					$query = $this->db->insert('family_notes', $data);

					// email user
					$subject = $this->settings_library->get('participant_consent_changed_subject');
					$message = $this->settings_library->get('participant_consent_changed');

					// smart tags
					$smart_tags = array(
						'first_name' => $contact_info->first_name,
						'changed_by' => $this->auth->user->first . ' ' . $this->auth->user->surname,
						'changed_at' => mysql_to_uk_datetime($data['modified']),
						'company' => $this->auth->account->company,
						'link' => $this->auth->get_bookings_site()
					);

					// replace smart tags
					if (count($smart_tags) > 0) {
						foreach ($smart_tags as $tag => $value) {
							$subject = str_replace('{' . $tag . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
							$message = str_replace('{' . $tag . '}', $value, $message);
						}
					}

					// html email
					$email_html = $message;

					$this->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->auth->user->accountID);

					// tell user
					$success = $contact_info->first_name . ' ' . $contact_info->last_name . ' has been updated and notified';
					$this->session->set_flashdata('success', $success);
					redirect('participants/privacy/' . $familyID);
					return TRUE;
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'contact_info' => $contact_info,
			'contactID' => $contactID,
			'familyID' => $familyID,
			'brands' => $brands,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('participants/privacy-edit', $data);
	}
}

/* End of file privacy.php */
/* Location: ./application/controllers/participants/privacy.php */
