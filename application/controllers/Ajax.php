<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends MY_Controller {

	private $results;
	private $search_term;
	private $page;
	private $per_page = 100;

	public function __construct() {
		parent::__construct(FALSE);

		$this->results = [];

		$this->search_term = $this->input->get('term');
		$this->page = $this->input->get('page');

		if (empty($this->page) || $this->page <= 0) {
			$this->page = 1;
		}
	}

	// projects
	public function projects() {
		$res = $this->db->select('bookingID, name')
		->from('bookings')
		->where([
			'accountID' => $this->auth->user->accountID,
			'project' => 1
		])
		->like('name', $this->search_term)
		->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$this->results[] = [
					'id' => $row->bookingID,
					'text' => html_entity_decode($row->name)
				];
			}
		}
		// respond
		return $this->respond_json();
	}

	// blocks
	public function blocks() {
		$res = $this->db->select('blockID, name')
		->from('bookings_blocks')
		->where([
			'accountID' => $this->auth->user->accountID
		])
		->like('name', $this->search_term)
		->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$this->results[] = [
					'id' => $row->blockID,
					'text' => html_entity_decode($row->name)
				];
			}
		}
		// respond
		return $this->respond_json();
	}

	// contacts and children
	public function participants() {
		// contacts
		$res = $this->db->select('contactID, first_name, last_name')
		->from('family_contacts')
		->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])
		->where("CONCAT(first_name, ' ', last_name) LIKE '%" . $this->db->escape_like_str($this->search_term) . "%'", NULL, FALSE)
		->order_by('first_name asc, last_name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$this->results[] = [
					'id' => 'contact_' . $row->contactID,
					'text' => html_entity_decode($row->first_name . ' '. $row->last_name)
				];
			}
		}
		// children
		$res = $this->db->select('childID, first_name, last_name')
		->from('family_children')
		->where([
			'accountID' => $this->auth->user->accountID,
		])
		->where("CONCAT(first_name, ' ', last_name) LIKE '%" . $this->db->escape_like_str($this->search_term) . "%'", NULL, FALSE)
		->order_by('first_name asc, last_name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$this->results[] = [
					'id' => 'child_' . $row->childID,
					'text' => html_entity_decode($row->first_name . ' '. $row->last_name)
				];
			}
		}
		// respond
		return $this->respond_json();
	}

	// contacts
	public function contacts() {
		$res = $this->db->select('contactID, first_name, last_name')
		->from('family_contacts')
		->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])
		->where("CONCAT(first_name, ' ', last_name) LIKE '%" . $this->db->escape_like_str($this->search_term) . "%'", NULL, FALSE)
		->order_by('first_name asc, last_name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$this->results[] = [
					'id' => $row->contactID,
					'text' => html_entity_decode($row->first_name . ' '. $row->last_name)
				];
			}
		}
		// respond
		return $this->respond_json();
	}

	// respond with ajax
	private function respond_json() {
		// get paginated data
		$offset = ($this->page-1)*$this->per_page;
		$results = array_slice($this->results, $offset, $this->per_page);

		// check for more results
		$next_offset = $offset += $this->per_page;
		$next_results = array_slice($this->results, $next_offset, $this->per_page);
		$more = FALSE;
		if (count($next_results) > 0) {
			$more = TRUE;
		}

		// return JSON
		header('Content-type: application/json');
		echo json_encode([
			'results' => $results,
			'pagination' => [
				'more' => $more
			]
		]);
		exit();
	}

}

/* End of file Ajax.php */
/* Location: ./application/controllers/Ajax.php */
