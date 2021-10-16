<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Birthday extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}
	}

	/**
	 * edit birthday
	 * @param  int $bookingID
	 * @return void
	 */
	public function index($bookingID = NULL)
	{

		$booking_info = new stdClass;

		// check
		if ($bookingID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($bookingID)) {
			show_404();
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings.*, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$booking_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Birthday';
		$submit_to = 'bookings/birthday/' . $bookingID;
		$return_to = $submit_to;
		$buttons = NULL;
		$icon = 'star';
		$tab = 'birthday';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
		}
		$section = 'bookings';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('bPackage', 'Package', 'trim|xss_clean|required');
			$this->form_validation->set_rules('bTheme', 'Theme', 'trim|xss_clean|required');
			$this->form_validation->set_rules('bAttendees', 'Attendees', 'trim|xss_clean|required');
			$this->form_validation->set_rules('bNotes', 'Notes', 'trim|xss_clean');
			$this->form_validation->set_rules('bPaid', 'Paid', 'trim|xss_clean');
			$this->form_validation->set_rules('bcCoaching', '1hour 15mins coaching', 'trim|xss_clean');
			$this->form_validation->set_rules('bcCard', 'Birthday Card', 'trim|xss_clean');
			$this->form_validation->set_rules('bcCerts', 'Certificates', 'trim|xss_clean');
			$this->form_validation->set_rules('bcInvites', 'Invitations', 'trim|xss_clean');
			$this->form_validation->set_rules('bcMedals', 'Medals', 'trim|xss_clean');
			$this->form_validation->set_rules('bcCake', 'Birthday Cake', 'trim|xss_clean');
			$this->form_validation->set_rules('bcBags', 'Party Bags', 'trim|xss_clean');
			$this->form_validation->set_rules('bcTrophy', 'Trophy', 'trim|xss_clean');
			$this->form_validation->set_rules('bcPhoto', 'Photo shoot', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {



				// all ok, prepare data
				$data = array(
					'bPackage' => set_value('bPackage'),
					'bTheme' => set_value('bTheme'),
					'bAttendees' => set_value('bAttendees'),
					'bNotes' => set_value('bNotes'),
					'bPaid' => 0,
					'bcCoaching' => 0,
					'bcCard' => 0,
					'bcCerts' => 0,
					'bcInvites' => 0,
					'bcMedals' => 0,
					'bcCake' => 0,
					'bcBags' => 0,
					'bcTrophy' => 0,
					'bcPhoto' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if (set_value('bPaid') == 1) {
					$data['bPaid'] = 1;
				}
				if (set_value('bcCoaching') == 1) {
					$data['bcCoaching'] = 1;
				}
				if (set_value('bcCard') == 1) {
					$data['bcCard'] = 1;
				}
				if (set_value('bcCerts') == 1) {
					$data['bcCerts'] = 1;
				}
				if (set_value('bcInvites') == 1) {
					$data['bcInvites'] = 1;
				}
				if (set_value('bcMedals') == 1) {
					$data['bcMedals'] = 1;
				}
				if (set_value('bcCake') == 1) {
					$data['bcCake'] = 1;
				}
				if (set_value('bcBags') == 1) {
					$data['bcBags'] = 1;
				}
				if (set_value('bcTrophy') == 1) {
					$data['bcTrophy'] = 1;
				}
				if (set_value('bcPhoto') == 1) {
					$data['bcPhoto'] = 1;
				}

				// final check for errors
				if (count($errors) == 0) {

					$where = array(
						'bookingID' => $bookingID,
						'accountID' => $this->auth->user->accountID
					);

					// update
					$query = $this->db->update('bookings', $data, $where);

					// if updated
					if ($this->db->affected_rows() == 1) {

						$this->session->set_flashdata('success', 'Birthday data has been updated successfully.');

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
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
			'booking_info' => $booking_info,
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'type' => $booking_info->type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/birthday', $data);
	}

}

/* End of file birthday.php */
/* Location: ./application/controllers/bookings/birthday.php */