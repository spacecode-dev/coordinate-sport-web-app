<?php

class Attachment_library
{
    const MAIN_TABLE = 'staff_attachments';

    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Retrieve mandatory quals attachments
     * @param $staffID
     * @param $accountID
     * @return array
     */
    public function getQualAttachments($staffID, $qualArea, $accountID) {
        $query = $this->CI->db->select()
            ->from(self::MAIN_TABLE)
            ->where([
                'accountID' => $accountID,
                'staffID' => $staffID,
                'area' => $qualArea
            ])
            ->get();

        $result = [];
        if ($query->num_rows() < 1) {
            return $result;
        }

        foreach ($query->result() as $attachment) {
            $result[$attachment->belongs_to] = $attachment;
        }

        return $result;
    }

    /**
     * add new attachment
     * @param $data
     * @return mixed
     */
    public function addAttachement($data) {
        $this->CI->db->insert(self::MAIN_TABLE, $data);
        return $this->CI->db->insert_id();
    }

    /**
     * get attachment related to qualification
     * @param $qualId
     * @param $qualArea
     * @param $accountId
     * @return array
     */
    public function getAttachmentInfoByQualification($qualId, $qualArea, $accountId) {
        $query = $this->CI->db->select()
            ->from(self::MAIN_TABLE)
            ->where([
                'accountID' => $accountId,
                'area' => $qualArea,
                'belongs_to' => $qualId
            ])
            ->limit(1)
            ->get();

        $result = [];
        if ($query->num_rows() < 1) {
            return $result;
        }

        foreach ($query->result() as $attachment) {
            return $attachment;
        }
    }

    /**
     * get attachment info by ID
     * @param $attachmentID
     * @return array
     */
    public function getAttachmentInfo($attachmentID) {
        $query = $this->CI->db->select()
            ->from(self::MAIN_TABLE)
            ->where([
                'attachmentID' => $attachmentID
            ])
            ->limit(1)
            ->get();

        $result = [];
        if ($query->num_rows() < 1) {
            return $result;
        }

        foreach ($query->result() as $attachment) {
            return $attachment;
        }
    }

    /**
     * remove attachment by ID
     * @param $attachmentID
     * @return mixed
     */
    public function removeAttachment($attachmentID) {
        $attachmentInfo = $this->getAttachmentInfo($attachmentID);

        if (!empty($attachmentInfo)) {
            $path = UPLOADPATH;
            if (file_exists($path . $attachmentInfo->path)) {
                unlink($path . $attachmentInfo->path);
            }

            $query = $this->CI->db->delete(self::MAIN_TABLE, [
                'attachmentID' => $attachmentID
            ]);

            return $this->CI->db->affected_rows();
        }

        return false;
    }
}