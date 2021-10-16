<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Confirmation extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}

		// check if allowed
		if ($this->settings_library->get('send_new_booking') != 1) {
			show_403();
		}

		$this->load->library('notifications_library');
		$this->load->library('qualifications_library');
		$this->load->library('attachment_library');
		$this->load->library('bookings_library');
		$this->load->library('orgs_library');
	}

	/**
	 * send confirmation
	 * @return void
	 */
	public function index($bookingID = NULL)
	{

		// required
		if ($bookingID == NULL) {
			show_404();
		}

		// look up booking
		$where = array(
			'bookings.bookingID' => $bookingID,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('bookings.*, brands.name as brand, orgs.name as org')->from('bookings')->join('brands', 'bookings.brandID = brands.brandID', 'left')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		foreach ($query->result() as $booking_info) {}

		// booking or project only
		if ($booking_info->type != 'booking' && $booking_info->project != 1) {
			show_404();
		}

		// look up booking
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		foreach ($query->result() as $org_info) {}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$tab = 'messaging';
		$title = 'Send Confirmation';
		$submit_to = 'bookings/confirmation/' . $bookingID;
		$return_to = 'bookings/confirmation/' . $bookingID;
		$icon = 'envelope';
		$current_page = $booking_info->type . 's';
		$breadcrumb_levels = array();
		if ($booking_info->project == 1) {
			$current_page = 'projects';
			$breadcrumb_levels['bookings/projects'] = 'Projects';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->name;
			$breadcrumb_levels['bookings/notifications/' . $bookingID] = 'Messaging';
		} else {
			$breadcrumb_levels['bookings'] = 'Contracts';
			$breadcrumb_levels['bookings/edit/' . $bookingID] = $booking_info->org;
			$breadcrumb_levels['bookings/notifications/' . $bookingID] = 'Messaging';
		}
		$section = 'bookings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$error = NULL;
		$success = NULL;
		$info = NULL;

		// look up contacts
		$where = array(
			'orgID' => $booking_info->orgID,
			'accountID' => $this->auth->user->accountID,
			'email !=' => '',
			'email IS NOT NULL' => NULL
		);

		$contacts = $this->db->from('orgs_contacts')->where($where)->order_by('isMain desc, name asc')->get();

		if ($contacts->num_rows() == 0) {
			if (isset($this->bulk_data)) {
				$this->session->set_flashdata('bulk_data', $this->bulk_data);
			}
			$this->session->set_flashdata('error', 'Booking confirmed, however couldn\'t send confirmation as there are no contacts set in the organisation with email addresses');
			redirect($return_to);
		}

		// blocks
		$blockIDs = [];
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$blocks = $this->db->from('bookings_blocks')->where($where)->order_by('startDate asc, endDate asc, name asc')->get();

		if ($blocks->num_rows() == 0) {
			if (isset($this->bulk_data)) {
				$this->session->set_flashdata('bulk_data', $this->bulk_data);
			}
			$this->session->set_flashdata('error', 'Booking confirmed, however couldn\'t send confirmation as there are no blocks in booking');
			redirect($return_to);
		} else {
			foreach ($blocks->result() as $row) {
				$blockIDs[] = $row->blockID;
			}
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('contactID', 'Contact', 'trim|xss_clean|required|callback_valid_contact[' . $bookingID . ']');
			$this->form_validation->set_rules('subject', 'Subject', 'trim|xss_clean|required');
			$this->form_validation->set_rules('cc[]', 'CC', 'trim|xss_clean');
			$this->form_validation->set_rules('bcc[]', 'BCC', 'trim|xss_clean');
			$this->form_validation->set_rules('extra-cc[]', 'CC', 'trim|xss_clean');
			$this->form_validation->set_rules('extra-bcc[]', 'BCC', 'trim|xss_clean');
			$this->form_validation->set_rules('addition_attachment[]', 'Attachments', 'trim|xss_clean');
			$this->form_validation->set_rules('content', 'Content', 'trim|required');

			$extraCC = set_value('extra-cc');
			$extraBCC = set_value('extra-bcc');

			$emailNames = ['extraCC', 'extraBCC'];
			$emailsToValidate = [];

			foreach ($emailNames as $name) {
				if (!empty(${$name})) {
					foreach (${$name} as $email) {
						if (!empty($email))
						$emailsToValidate[] = $email;
					}
				}
			}


			$customErrors = $this->crm_library->validateArrayEmails($emailsToValidate);

			if (!empty($customErrors)) {
				$customErrors = ['A valid email address must be entered in the CC or BCC fields'];
			}

			$attachments = array();
			$attachment_data = array();
			if (!empty($_FILES['files']['name'][0])) {
				$this->load->library('upload');
				// check for upload
				$upload_res = $this->crm_library->handle_multi_upload();

				if ($upload_res == NULL) {
					$customErrors[] = strip_tags($this->upload->display_errors());
					} else {
						foreach ($upload_res as $res) {
							// prepare table
							$attachment_data[] = [
								'byID' => $this->auth->user->staffID,
								'name' => $res['client_name'],
								'path' => $res['raw_name'],
								'type' => $res['file_type'],
								'size' => $res['file_size']*1024,
								'ext' => substr($res['file_ext'], 1),
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							];

							// attach to email
							$attachments[UPLOADPATH . $res['raw_name']] = $res['client_name'];
						}
				}
			}


			if ($this->form_validation->run() == FALSE || !empty($customErrors)) {
				$errors = !empty($customErrors) ? $customErrors : $this->form_validation->error_array();
			} else {

				// get html email and convert to plain text
				$email_html = $this->input->post('content', FALSE);
				$this->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				$additional_attachments = set_value('addition_attachment');

				if ($additional_attachments) {
					foreach ($additional_attachments as $attachment) {
						$file = $this->attachment_library->getAttachmentInfo($attachment);
						if (!empty($file)) {
							$attachment_data[] = [
								'byID' => $this->auth->user->staffID,
								'name' => $file->name,
								'path' => $file->path,
								'type' => $file->type,
								'size' => $file->size*1024,
								'ext' => substr($file->ext, 1),
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),

							];
							$attachments[UPLOADPATH . $file->path] = $file->name;
						}
					}
				}

				// customer attachments
				$customerAttachmentIDs = array();

				$where = array(
					'orgID' => $booking_info->orgID,
					'sendwithconfirmation' => 1,
					'accountID' => $this->auth->user->accountID
				);

				$res = $this->db->from('orgs_attachments')->where($where)->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$attachments[UPLOADPATH . $row->path] = $row->name;
						$customerAttachmentIDs[] = $row->attachmentID;
					}
				}

				// get booking attachments
				$bookingAttachmentIDs = array();
				$where = array(
					'bookings_attachments.accountID' => $this->auth->user->accountID
				);
				$res = $this->db->select('bookings_attachments.*')
				->from('bookings_attachments')
				->join('bookings_attachments_blocks', 'bookings_attachments.attachmentID = bookings_attachments_blocks.attachmentID', 'inner')
				->where($where)
				->where_in('bookings_attachments_blocks.blockID', $blockIDs)
				->group_by('bookings_attachments.attachmentID')
				->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$attachments[UPLOADPATH . $row->path] = $row->name;
						$bookingAttachmentIDs[] = $row->attachmentID;
					}
				}

				// resources
				$resourceAttachmentIDs = array();

				// send different files depending on brand
				if ($this->auth->has_features('resources')) {
					$where = array(
						'files.accountID' => $this->auth->user->accountID,
						'files_brands.brandID' => $booking_info->brandID
					);

					$res = $this->db->select('files.*')->from('files')->join('files_brands', 'files.attachmentID = files_brands.attachmentID', 'left')->where($where)->group_by('files.attachmentID')->get();

					if ($res->num_rows() > 0) {
						foreach ($res->result() as $row) {
							$attachments[UPLOADPATH . $row->path] = $row->name;
							$resourceAttachmentIDs[] = $row->attachmentID;
						}
					}
				}

				$contact_info = $this->orgs_library->findContactByIdBooking($bookingID, set_value('contactID'));

				// no match
				if (empty($contact_info)) {
					return false;
				}


				$smart_tags = array(
					'main_contact' => $contact_info->name,
					'contact_name' => $contact_info->name,
				);

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
				}

				$cc = set_value('cc');
				$bcc = set_value('bcc');

				$ccEmails = [];
				if (!empty($cc)) {
					// look up contact
					foreach ($cc as $recipient) {
						$contact_info = $this->orgs_library->findContactById($recipient);

						if (empty($contact_info)) {
							continue;
						}

						$ccEmails[] = $contact_info->email;
					}
				}

				$extraCC = set_value('extra-cc');

				if (!empty($extraCC)) {
					foreach ($extraCC as $email) {
						$ccEmails[] = $email;
					}
				}

				$bccEmails = [];
				if (!empty($bcc)) {
					// look up contact
					foreach ($bcc as $recipient) {
						$contact_info = $this->orgs_library->findContactById($recipient);

						if (empty($contact_info)) {
							continue;
						}

						$bccEmails[] = $contact_info->email;
					}
				}

				$extraBCC = set_value('extra-bcc');

				if (!empty($extraBCC)) {
					foreach ($extraBCC as $email) {
						$bccEmails[] = $email;
					}
				}

				if ($this->crm_library->send_email($contact_info->email, set_value('subject', NULL, FALSE), $email_html, $attachments, FALSE, $booking_info->accountID, $booking_info->brandID, $bccEmails, $ccEmails)) {

					// save
					$this->notifications_library->addCustomerEmailRecord(
						set_value('contactID'),
						$booking_info->orgID,
						$contact_info->email,
						$email_html,
						$email_plain,
						$attachment_data,
						$customerAttachmentIDs,
						$resourceAttachmentIDs,
						$bookingAttachmentIDs,
						$bookingID
					);

					$this->session->set_flashdata('success', 'Booking confirmation sent successfully.');
					redirect($return_to);
				} else {
					$error = 'Confirmation could not be sent';
				}

			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// set defaults
		$subject = $this->settings_library->get('email_new_booking_subject');
		$content = $this->settings_library->get('email_new_booking');

		// set smart tags
		$smart_tags = array(
			'org_name' => $org_info->name,
			'brand' => $booking_info->brand,
			'date_description' => ' between ' . mysql_to_uk_date($booking_info->startDate) . ' and ' . mysql_to_uk_date($booking_info->endDate),
			'details' => NULL
		);

		// if one day only, change text
		if ($booking_info->startDate == $booking_info->endDate) {
			$smart_tags['date_description'] = ' on ' . mysql_to_uk_date($booking_info->startDate);
		}

		// get details
		if ($blocks->num_rows() > 0) {
			foreach ($blocks->result() as $block) {

				// lessons
				$where = array(
					'bookings_lessons.blockID' => $block->blockID,
					'bookings_lessons.accountID' => $this->auth->user->accountID
				);
				$lessons = $this->db->select('bookings_lessons.*, activities.name as activity')->from('bookings_lessons')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->where($where)->order_by('bookings_lessons.startDate, bookings_lessons.endDate, bookings_lessons.day asc, bookings_lessons.startTime asc, bookings_lessons.endTime asc')->get();

				if ($lessons->num_rows() > 0) {

					// block header
					$smart_tags['details'] .= '<p><strong>' . $block->name . ' (' . mysql_to_uk_date($block->startDate);
					if (strtotime($block->endDate) > strtotime($block->startDate)) {
						$smart_tags['details'] .= ' to ' . mysql_to_uk_date($block->endDate);
					}
					$smart_tags['details'] .= ')</strong></p>';

					// get lessons
					$smart_tags['details'] .= '<table width="100%" border="1">
					<tr>
						<th scope="col">Day</th>
						<th scope="col">Start</th>
						<th scope="col">End</th>
						<th scope="col">Group</th>
						<th scope="col">Activity</th>
					</tr>';

					foreach ($lessons->result() as $lesson) {
						$group = 'Unknown';
						$activity = 'Unknown';

						if ($lesson->group == 'other') {
							$group = $lesson->group_other;
						} else if (!empty($lesson->group)) {
							$group = $this->crm_library->format_lesson_group($lesson->group);
						}

						if (!empty($lesson->activity)) {
							$activity = $lesson->activity;
						} else if (!empty($lesson->activity_other)) {
							$activity = $lesson->activity_other;
						}

						$smart_tags['details'] .= '<tr>
							<td>' . ucwords($lesson->day);
							if (!empty($lesson->startDate)) {
								$smart_tags['details'] .= ' (' . mysql_to_uk_date($lesson->startDate);
								if (!empty($lesson->endDate) && strtotime($lesson->endDate) > strtotime($lesson->startDate)) {
									$smart_tags['details'] .= '-' . mysql_to_uk_date($lesson->endDate);
								}
								$smart_tags['details'] .= ')';
							}
							$smart_tags['details'] .= '</td>
							<td>' . substr($lesson->startTime, 0, 5) . '</td>
							<td>' . substr($lesson->endTime, 0, 5) . '</td>
							<td>' . $group . '</td>
							<td>' . $activity;
							if (!empty($lesson->activity_desc)) {
								$smart_tags['details'] .= ': ' . $lesson->activity_desc;
							}
							$smart_tags['details'] .= '</td>
						</tr>';
					}

					$smart_tags['details'] .= '</table>';

					// exceptions
					$where = array(
						'bookings_lessons.blockID' => $block->blockID,
						'bookings_lessons_exceptions.type' => 'cancellation',
						'bookings_lessons.accountID' => $this->auth->user->accountID
					);
					$exceptions = $this->db->select('bookings_lessons.*, bookings_lessons_exceptions.date, bookings_lessons_exceptions.reason_select, bookings_lessons_exceptions.reason, activities.name as activity')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->where($where)->order_by('date asc, bookings_lessons.startTime, bookings_lessons.endTime')->get();

					if ($exceptions->num_rows() > 0) {

						// get exceptions
						$smart_tags['details'] .= '<p>You have informed us of the following dates where you would not like the sessions to take place:</p>
						<table width="100%" border="1">
						<tr>
							<th scope="col">Date</th>
							<th scope="col">Start</th>
							<th scope="col">End</th>
							<th scope="col">Group</th>
							<th scope="col">Activity</th>
							<th scope="col">Reason</th>
						</tr>';

						foreach ($exceptions->result() as $exception) {
							$group = 'Unknown';
							$activity = 'Unknown';
							$reason = 'Unknown';

							if ($exception->group == 'other') {
								$group = $exception->group_other;
							} else if (!empty($exception->group)) {
								$group = $this->crm_library->format_lesson_group($exception->group);
							}

							if (!empty($exception->activity)) {
								$activity = $exception->activity;
							} else if (!empty($exception->activity_other)) {
								$activity = $exception->activity_other;
							}

							if ($exception->reason_select == 'other') {
								$reason = $exception->reason;
							} else if (!empty($exception->reason_select)) {
								$reason = ucwords($exception->reason_select);
							}

							$smart_tags['details'] .= '<tr>
								<td>' . mysql_to_uk_date($exception->date) . '</td>
								<td>' . substr($exception->startTime, 0, 5) . '</td>
								<td>' . substr($exception->endTime, 0, 5) . '</td>
								<td>' . $group . '</td>
								<td>' . $activity . '</td>
								<td>' . $reason . '</td>
							</tr>';
						}

						$smart_tags['details'] .= '</table>';

					}

				}
			}
		}

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$content = str_replace('<p>{' . $key . '}</p>', $value, $content);
			$content = str_replace('{' . $key . '}', $value, $content);
		}

		// replace smart tags in subject
		unset($smart_tags['details']);
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
		}

		$qualifications = $this->qualifications_library->getMandatoryQuals($this->auth->user->accountID, true);

		$tagsToReplace = $this->qualifications_library->getDefaultQualsTags();

		$qualifications_data = $this->qualifications_library->collectQualificationsDataByBooking($bookingID);

		$mandatoryQualsTags = array_filter($this->qualifications_library->getAllTags($this->auth->user->accountID));

		foreach ($tagsToReplace as $qualName => $tag) {
			if (stripos($content, $tag) !== false) {
				$table = $this->qualifications_library->createQualificationsTable($qualifications_data['data'], $qualName);
				$content = str_replace($tag, $table, $content);
				unset($qualifications[$qualName]);
			}
		}

		foreach ($mandatoryQualsTags as $qualId => $qualTag) {
			if (stripos($content, '{' . $qualTag . '}') !== false) {
				$content = str_replace('{' . $qualTag . '}', '', $content);
				unset($qualifications[$qualId]);
			}
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
			'bookingID' => $bookingID,
			'booking_info' => $booking_info,
			'contacts' => $contacts,
			'subject' => $subject,
			'content' => $content,
			'attachment_field' => TRUE,
			'success' => $success,
			'breadcrumb_levels' => $breadcrumb_levels,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'qualifications' => $qualifications
		);

		// load view
		$this->crm_view('bookings/notification_customer', $data);
	}

	/**
	 * check if a contact is valid
	 * @param  string $contactID
	 * @param  string $bookingID
	 * @return boolean
	 */
	public function valid_contact($contactID = NULL, $bookingID = NULL) {

		if (empty($bookingID) || empty($contactID)) {
			return FALSE;
		}

		// look up contact
		$where = array(
			'bookings.bookingID' => $bookingID,
			'orgs_contacts.contactID' => $contactID,
			'orgs_contacts.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('orgs_contacts.*')->from('orgs_contacts')->join('bookings', 'orgs_contacts.orgID = bookings.orgID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			return false;
		}

		foreach ($query->result() as $contact_info) {}

		if (!empty($contact_info->email) && filter_var($contact_info->email, FILTER_VALIDATE_EMAIL)) {
			return TRUE;
		}


		return FALSE;

	}

	public function qualifications_data($bookingId, $qualId) {
		$data = $this->qualifications_library->collectQualificationsDataByBooking($bookingId, $qualId);

		header("Content-type:application/json");
		echo json_encode($data);
	}

	public function qualifications_data_by_lesson($qualId) {
		$lessons = $_POST['lessons'];

		$data = $this->qualifications_library->qualificationsDataByLesson($lessons, $qualId);

		header("Content-type:application/json");
		echo json_encode($data);
	}

}

/* End of file confirmation.php */
/* Location: ./application/controllers/bookings/confirmation.php */
