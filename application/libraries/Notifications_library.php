<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notifications_library
{
    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function addCustomerEmailRecord(
        $contactId,
        $orgId,
        $emailAddress,
        $emailHtml,
        $emailPlain,
        $attachments,
        $customerAttachmentIDs = [],
        $resourceAttachmentIDs = [],
		$bookingAttachmentIDs = [],
		$bookingID = NULL
    ) {
        $data = array(
            'orgID' => $orgId,
            'contactID' => $contactId,
            'byID' => $this->CI->auth->user->staffID,
            'type' => 'email',
            'destination' => $emailAddress,
            'subject' => set_value('subject'),
            'contentHTML' => $emailHtml,
            'contentText' => $emailPlain,
            'status' => 'sent',
            'added' => mdate('%Y-%m-%d %H:%i:%s'),
            'modified' => mdate('%Y-%m-%d %H:%i:%s'),
            'accountID' => $this->CI->auth->user->accountID,
            'bookingID' => $bookingID
        );

        $this->CI->db->insert('orgs_notifications', $data);

        $notificationID = $this->CI->db->insert_id();

        // save attachment if set
        if (count($attachments) > 0) {
            foreach ($attachments as $data) {
                $data['notificationID'] = $notificationID;
                $data['accountID'] = $this->CI->auth->user->accountID;

                // insert
                $query = $this->CI->db->insert('orgs_notifications_attachments', $data);
            }
        }

        // save attachments
        if (count($customerAttachmentIDs) > 0) {
            foreach ($customerAttachmentIDs as $attachmentID) {
                $data = array(
					'accountID' => $this->CI->auth->user->accountID,
                    'notificationID' => $notificationID,
                    'attachmentID' => $attachmentID
                );

                $this->CI->db->insert('orgs_notifications_attachments_customers', $data);
            }
        }

        if (count($resourceAttachmentIDs) > 0) {
            foreach ($resourceAttachmentIDs as $attachmentID) {
                $data = array(
					'accountID' => $this->CI->auth->user->accountID,
                    'notificationID' => $notificationID,
                    'attachmentID' => $attachmentID
                );

                $this->CI->db->insert('orgs_notifications_attachments_resources', $data);
            }
        }

		if (count($bookingAttachmentIDs) > 0) {
            foreach ($bookingAttachmentIDs as $attachmentID) {
                $data = array(
					'accountID' => $this->CI->auth->user->accountID,
                    'notificationID' => $notificationID,
                    'attachmentID' => $attachmentID
                );

                $this->CI->db->insert('orgs_notifications_attachments_bookings', $data);
            }
        }
    }

}
