<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Clean_up_word_notes extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		$tables = [
			'bookings_lessons_notes' => [
				'id' => 'noteID',
				'content' => 'content'
			],
			'staff_notes' => [
				'id' => 'noteID',
				'content' => 'content'
			],
			'orgs_notes' => [
				'id' => 'noteID',
				'content' => 'content'
			],
			'family_notes' => [
				'id' => 'noteID',
				'content' => 'content'
			],
			'staff' => [
				'id' => 'staffID',
				'content' => 'id_personalStatement'
			],
			'staff' => [
				'id' => 'staffID',
				'content' => 'equal_disability'
			],
			'staff' => [
				'id' => 'staffID',
				'content' => 'medical'
			],
		];
		foreach ($tables as $table => $fields) {
        	$res = $this->db->from($table)->like($fields['content'], 'MsoNormal')->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					extract($fields);
					$data = [
						$content => $this->strip_word_html($row->$content)
					];
					$where = [
						$id => $row->$id
					];
					$this->db->update($table, $data, $where, 1);
				}
			}
		}
    }

    public function down() {
        // no going back
    }

	private function strip_word_html($text, $allowed_tags = '<b><i><sup><sub><em><strong><u><br><ul><ol><li><table><tr><td><th><p>')
    {
        mb_regex_encoding('UTF-8');
		// remove classes
		$text = str_replace(' class="MsoNormal"', '', $text);
		$text = str_replace(' xss=removed', '', $text);
        //replace MS special characters first
        $search = array('/&lsquo;/u', '/&rsquo;/u', '/&ldquo;/u', '/&rdquo;/u', '/&mdash;/u');
        $replace = array('\'', '\'', '"', '"', '-');
        $text = preg_replace($search, $replace, $text);
        //make sure _all_ html entities are converted to the plain ascii equivalents - it appears
        //in some MS headers, some html entities are encoded and some aren't
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        //try to strip out any C style comments first, since these, embedded in html comments, seem to
        //prevent strip_tags from removing html comments (MS Word introduced combination)
        if(mb_stripos($text, '/*') !== FALSE){
            $text = mb_eregi_replace('#/\*.*?\*/#s', '', $text, 'm');
        }
        //introduce a space into any arithmetic expressions that could be caught by strip_tags so that they won't be
        //'<1' becomes '< 1'(note: somewhat application specific)
        $text = preg_replace(array('/<([0-9]+)/'), array('< $1'), $text);
        $text = strip_tags($text, $allowed_tags);
        //eliminate extraneous whitespace from start and end of line, or anywhere there are two or more spaces, convert it to one
        $text = preg_replace(array('/^\s\s+/', '/\s\s+$/', '/\s\s+/u'), array('', '', ' '), $text);
        //strip out inline css and simplify style tags
        $search = array('#<(strong|b)[^>]*>(.*?)</(strong|b)>#isu', '#<(em|i)[^>]*>(.*?)</(em|i)>#isu', '#<u[^>]*>(.*?)</u>#isu');
        $replace = array('<b>$2</b>', '<i>$2</i>', '<u>$1</u>');
        $text = preg_replace($search, $replace, $text);
        //on some of the ?newer MS Word exports, where you get conditionals of the form 'if gte mso 9', etc., it appears
        //that whatever is in one of the html comments prevents strip_tags from eradicating the html comment that contains
        //some MS Style Definitions - this last bit gets rid of any leftover comments */
        $num_matches = preg_match_all("/\<!--/u", $text, $matches);
        if($num_matches){
              $text = preg_replace('/\<!--(.)*--\>/isu', '', $text);
        }
		// remove empty paragraphs
		$text = str_replace('<p></p>', '', $text);
		// trim
		$text = trim($text);
        return $text;
    }
}
