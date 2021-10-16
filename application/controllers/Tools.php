<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tools extends MY_Controller {

	public function __construct() {
		parent::__construct();

		// only admin account can access
		if ($this->auth->account->admin != 1) {
			show_403();
		}
	}

	public function index() {
		//$this->replace_customer_attachments();
		//$this->recalc_targets();
		//$this->sync_newsletter_from_csv();
		//$this->migrate_brands();
	}

	// replace customer attachments
	private function replace_customer_attachments() {

		$documents = array(
			// docx
			'Athletics Risk Assessment.docx' => 'Athletics Risk Assessment.pdf',
			'Badminton Risk Assessment.docx' => 'Badminton Risk Assessment.pdf',
			'Basketball Risk Assessment.docx' => 'Basketball Risk Assessment.pdf',
			'Benchball Risk Assessment.docx' => 'Benchball Risk Assessment.pdf',
			'Cricket Risk Assessment.docx' => 'Cricket Risk Assessment.pdf',
			'Dance Risk Assessment.docx' => 'Dance Risk Assessment.pdf',
			'Dodgeball Risk Assessment.docx' => 'Dodgeball Risk Assessment.pdf',
			'Drama Risk Assessment.docx' => 'Drama Risk Assessment.pdf',
			'Fitness Session Risk Assessment.docx' => 'Fitness Session Risk Assessment.pdf',
			'Football Risk Assessment.docx' => 'Football Risk Assessment.pdf',
			'Golf Risk Assessment.docx' => 'Golf Risk Assessment.pdf',
			'Gymnastics Risk Assessment.docx' => 'Gymnastics Risk Assessment.pdf',
			'Handball Risk Assessment.docx' => 'Handball Risk Assessment.pdf',
			'Hockey Risk Assessment.docx' => 'Hockey Risk Assessment.pdf',
			'Invasion Games Risk Assessment.docx' => 'Invasion Games Risk Assessment.pdf',
			'Lacrosse Risk Assessment.docx' => 'Lacrosse Risk Assessment.pdf',
			'Longball Risk Assessment.docx' => 'Longball Risk Assessment.pdf',
			'Multi-Skills Risk Assessment.docx' => 'Multi-Skills Risk Assessment.pdf',
			'Multi-Sports Risk Assessment.docx' => 'Multi-Sports Risk Assessment.pdf',
			'Net Wall Risk Assessment.docx' => 'Net Wall Risk Assessment.pdf',
			'Netball Risk Assessment.docx' => 'Netball Risk Assessment.pdf',
			'OAA Risk Assessment.docx' => 'OAA Risk Assessment.pdf',
			'Parachute Games Risk Assessment.docx' => 'Parachute Games Risk Assessment.pdf',
			'Rounders Risk Assessment.docx' => 'Rounders Risk Assessment.pdf',
			'Stackers Session Risk Assessment.docx' => 'Stackers Session Risk Assessment.pdf',
			'Striking and Fielding Risk Assessment.docx' => 'Striking and Fielding Risk Assessment.pdf',
			'Table Tennis Risk Assessment.docx' => 'Table Tennis Risk Assessment.pdf',
			'Tag Rugby Risk Assessment.docx' => 'Tag Rugby Risk Assessment.pdf',
			'Tennis Risk Assessment.docx' => 'Tennis Risk Assessment.pdf',
			'Ultimate Frisbee Risk Assessment.docx' => 'Ultimate Frisbee Risk Assessment.pdf',
			'Volleyball Risk Assessment.docx' => 'Volleyball Risk Assessment.pdf',
			// doc
			'Athletics Risk Assessment.doc' => 'Athletics Risk Assessment.pdf',
			'Badminton Risk Assessment.doc' => 'Badminton Risk Assessment.pdf',
			'Basketball Risk Assessment.doc' => 'Basketball Risk Assessment.pdf',
			'Benchball Risk Assessment.doc' => 'Benchball Risk Assessment.pdf',
			'Cricket Risk Assessment.doc' => 'Cricket Risk Assessment.pdf',
			'Dance Risk Assessment.doc' => 'Dance Risk Assessment.pdf',
			'Dodgeball Risk Assessment.doc' => 'Dodgeball Risk Assessment.pdf',
			'Drama Risk Assessment.doc' => 'Drama Risk Assessment.pdf',
			'Fitness Session Risk Assessment.doc' => 'Fitness Session Risk Assessment.pdf',
			'Football Risk Assessment.doc' => 'Football Risk Assessment.pdf',
			'Golf Risk Assessment.doc' => 'Golf Risk Assessment.pdf',
			'Gymnastics Risk Assessment.doc' => 'Gymnastics Risk Assessment.pdf',
			'Handball Risk Assessment.doc' => 'Handball Risk Assessment.pdf',
			'Hockey Risk Assessment.doc' => 'Hockey Risk Assessment.pdf',
			'Invasion Games Risk Assessment.doc' => 'Invasion Games Risk Assessment.pdf',
			'Lacrosse Risk Assessment.doc' => 'Lacrosse Risk Assessment.pdf',
			'Longball Risk Assessment.doc' => 'Longball Risk Assessment.pdf',
			'Multi-Skills Risk Assessment.doc' => 'Multi-Skills Risk Assessment.pdf',
			'Multi-Sports Risk Assessment.doc' => 'Multi-Sports Risk Assessment.pdf',
			'Net Wall Risk Assessment.doc' => 'Net Wall Risk Assessment.pdf',
			'Netball Risk Assessment.doc' => 'Netball Risk Assessment.pdf',
			'OAA Risk Assessment.doc' => 'OAA Risk Assessment.pdf',
			'Parachute Games Risk Assessment.doc' => 'Parachute Games Risk Assessment.pdf',
			'Rounders Risk Assessment.doc' => 'Rounders Risk Assessment.pdf',
			'Stackers Session Risk Assessment.doc' => 'Stackers Session Risk Assessment.pdf',
			'Striking and Fielding Risk Assessment.doc' => 'Striking and Fielding Risk Assessment.pdf',
			'Table Tennis Risk Assessment.doc' => 'Table Tennis Risk Assessment.pdf',
			'Tag Rugby Risk Assessment.doc' => 'Tag Rugby Risk Assessment.pdf',
			'Tennis Risk Assessment.doc' => 'Tennis Risk Assessment.pdf',
			'Ultimate Frisbee Risk Assessment.doc' => 'Ultimate Frisbee Risk Assessment.pdf',
			'Volleyball Risk Assessment.doc' => 'Volleyball Risk Assessment.pdf'
		);

		$res_files = $this->db->from('orgs_attachments')->get();

		$updated = 0;

		if ($res_files->num_rows() > 0) {
			foreach ($res_files->result() as $file) {
				if (array_key_exists($file->name, $documents)) {
					echo "ID:" . $file->attachmentID . ' - ' . $file->name . '<br />';

					$random_path = random_string('alnum', 32);

					if (file_exists(APPPATH . '../public/risk/' . $documents[$file->name])) {

						copy(APPPATH . '../public/risk/' . $documents[$file->name], UPLOADPATH . $random_path);

						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$mime = finfo_file($finfo, APPPATH . '../public/risk/' . $documents[$file->name]);
						finfo_close($finfo);

						$data = array();
						$data['name'] = $documents[$file->name];
						$data['path'] = $random_path;
						$data['type'] = $mime;
						$data['size'] = filesize(APPPATH . '../public/risk/' . $documents[$file->name]);
						$data['ext'] = pathinfo($documents[$file->name], PATHINFO_EXTENSION);

						$where = array(
							'attachmentID' => $file->attachmentID,
							'accountID' => $file->accountID
						);

						// update
						$query = $this->db->update('orgs_attachments', $data, $where);

						$updated++;
					}
				}
			}
		}

		echo "Updated Attachments: " . $updated . "<br />";
	}

	// recalc targets
	private function recalc_targets() {

		$or_where = array(
			'bookings.project' => 1,
			'bookings.type' => 'event'
		);

		$res_blocks = $this->db->select('bookings_blocks.blockID')->from('bookings_blocks')->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')->or_where($or_where)->get();

		echo "Total Blocks: " . $res_blocks->num_rows() . "<br />";

		if ($res_blocks->num_rows() > 0) {
			foreach ($res_blocks->result() as $block) {

				echo "ID:" . $block->blockID . '<br />';

				$this->crm_library->calc_targets($block->blockID);

			}
		}
	}

	// sync newsletter from csv
	private function sync_newsletter_from_csv() {

		// If using, update code below with new brands fields in seperate table
		 echo 'Script update required';
		exit();

		$res_contacts = $this->db->from('tmp_newsletter_sync')->get();

		echo "Total Contacts: " . $res_contacts->num_rows() . "<br />";

		if ($res_contacts->num_rows() > 0) {
			foreach ($res_contacts->result() as $contact) {

				if ($contact->Delete == 1) {

					// look up
					$where = array(
						'contactID' => $contact->contactID,
						'orgID' => $contact->orgID,
						'accountID' => $this->auth->user->accountID,
						'active' => 1
					);

					$res_contact = $this->db->from('orgs_contacts')->where($where)->limit(1)->get();
					if($res_contact->num_rows() == 1) {
						foreach ($res_contact->result() as $tmp_contact) {
							if ($tmp_contact->isMain != '1') {
								unset($where['contactID']);
								// delete org too
								$this->db->delete('orgs', $where, 1);
								if ($this->db->affected_rows() == 1) {
									echo "Deleted org: " . $contact->orgID . '<br />';
								} else {
									echo "Error deleting org: " . $contact->orgID . '<br />';
									continue;
								}
							}
							// delete contact
							$where['contactID'] = $tmp_contact->contactID;
							$this->db->delete('orgs_contacts', $where, 1);
							if ($this->db->affected_rows() == 1) {
								echo "Deleted: " . $contact->contactID . '<br />';
							} else {
								echo "Error deleting: " . $contact->contactID . '<br />';
							}
						}

					}

				} else {

					$data = array(
						'email' => $contact->email,
						'newsletter_group' => intval($contact->group),
						'newsletter_education' => intval($contact->education),
						'newsletter_training' => intval($contact->training),
						'newsletter_development' => intval($contact->development),
						'newsletter_cycle' => intval($contact->cycle),
						'newsletter_kids' => intval($contact->kids),
					);

					$where = array(
						'contactID' => $contact->contactID,
						'orgID' => $contact->orgID,
						'accountID' => $this->auth->user->accountID
					);

					$res_update = $this->db->update('orgs_contacts', $data, $where, 1);

					echo "Updated: " . $contact->contactID . '<br />';

				}

			}
		}
	}

	// migrate static brands to ID based
	private function migrate_brands() {

		// get brand map
		$brand_map = array(
			'group' => 17,
			'education' => 19,
			'training' => 21,
			'development' => 20,
			'cycle' => 16,
			'kids' => 18
		);

		// migrate brands in bookings
		$map = array(
			'group' => $brand_map['group'],
			'education' => $brand_map['education'],
			'training' => $brand_map['training'],
			'development' => $brand_map['development'],
			'cycle' => $brand_map['cycle'],
			'kids' => $brand_map['kids'],
		);

		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->get_where('bookings', $where);

		echo "Total Bookings: " . $res->num_rows() . "<br />";

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (array_key_exists($row->brand, $map)) {
					// update
					$where = array(
						'bookingID' => $row->bookingID,
						'accountID' => $row->accountID
					);

					$data = array(
						'brandID' => $map[$row->brand]
					);

					$this->db->update('bookings', $data, $where);

					echo "ID:" . $row->bookingID . '<br />';
				}
			}
		}

		// migrate family contact newsletters
		$map = array(
			'newsletter_group' => $brand_map['group'],
			'newsletter_education' => $brand_map['education'],
			'newsletter_training' => $brand_map['training'],
			'newsletter_development' => $brand_map['development'],
			'newsletter_cycle' => $brand_map['cycle'],
			'newsletter' => $brand_map['kids'],
		);
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->get_where('family_contacts', $where);

		echo "Total Family Contacts: " . $res->num_rows() . "<br />";

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				foreach ($map as $field => $brandID) {
					if ($row->$field == 1) {
						$where = array(
							'brandID' => $brandID,
							'contactID' => $row->contactID,
							'accountID' => $this->auth->user->accountID
						);

						// check if exists
						$res = $this->db->from('family_contacts_newsletters')->where($where)->limit(1)->get();

						// if not, insert
						if ($res->num_rows() == 0) {
							$data = $where;
							$this->db->insert('family_contacts_newsletters', $data);
						}
					}
				}

				echo "ID:" . $row->contactID . '<br />';
			}
		}

		// migrate org contact newsletters
		$map = array(
			'newsletter_group' => $brand_map['group'],
			'newsletter_education' => $brand_map['education'],
			'newsletter_training' => $brand_map['training'],
			'newsletter_development' => $brand_map['development'],
			'newsletter_cycle' => $brand_map['cycle'],
			'newsletter_kids' => $brand_map['kids'],
		);
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);

		$res = $this->db->get_where('orgs_contacts', $where);

		echo "Total Org Contacts: " . $res->num_rows() . "<br />";

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				foreach ($map as $field => $brandID) {
					if ($row->$field == 1) {
						$where = array(
							'brandID' => $brandID,
							'contactID' => $row->contactID,
							'accountID' => $this->auth->user->accountID
						);

						// check if exists
						$res = $this->db->from('orgs_contacts_newsletters')->where($where)->limit(1)->get();

						// if not, insert
						if ($res->num_rows() == 0) {
							$data = $where;
							$this->db->insert('orgs_contacts_newsletters', $data);
						}
					}
				}

				echo "ID:" . $row->contactID . '<br />';
			}
		}

		// migrate documents send with bookings
		$map = array(
			'send_with_bookings' => $brand_map['group'],
			'send_with_bookings_education' => $brand_map['education'],
			'send_with_bookings_training' => $brand_map['training'],
			'send_with_bookings_development' => $brand_map['development'],
			'send_with_bookings_cycle' => $brand_map['cycle'],
			'send_with_bookings_kids' => $brand_map['kids'],
		);
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->get_where('files', $where);

		echo "Total Files: " . $res->num_rows() . "<br />";

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				foreach ($map as $field => $brandID) {
					if ($row->$field == 1) {
						$where = array(
							'brandID' => $brandID,
							'attachmentID' => $row->attachmentID,
							'accountID' => $this->auth->user->accountID
						);

						// check if exists
						$res = $this->db->from('files_brands')->where($where)->limit(1)->get();

						// if not, insert
						if ($res->num_rows() == 0) {
							$data = $where;
							$this->db->insert('files_brands', $data);
						}
					}
				}

				echo "ID:" . $row->attachmentID . '<br />';
			}
		}
	}

	public function recalc_family_balances($outstanding_only = false) {
		set_time_limit(0);

		$page = $this->input->get('page');
		$offset = 0;
		if ($page > 0) {
			$offset = $page*50;
		}
		$where = [];
		if ($outstanding_only == 1) {
			$where['family.account_balance <'] = 0;
		}

		// recalc all family balances
		$res = $this->db->select('family.familyID')
		->from('family')
		->join('bookings_cart', 'family.familyID = bookings_cart.familyID', 'inner')
		->order_by('family.familyID asc')
		->group_by('family.familyID')
		->limit(50, $offset)
		->where($where)
		->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$this->crm_library->recalc_family_balance($row->familyID);
				echo 'ID:' . $row->familyID . '<br />';
			}
			$next_link = 'tools/recalc_family_balances';
			if ($outstanding_only == 1) {
				$next_link .= '/1';
			}
			$next_link .= '?page=' . ($page + 1);
			?><script>
				setTimeout(function() {
					window.location = '<?php echo site_url($next_link); ?>';
				}, 500);
			</script><?php
		} else {
			echo 'All done';
		}
	}

	public function geocode_addresses() {
		set_time_limit(0);

		$geocode_cache = array();

		// orgs addresses
		$where = array(
			'location' => NULL
		);
		$res = $this->db->select('addressID, address1, town, postcode')
		->from('orgs_addresses')
		->where($where)
		->limit(50)
		->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				echo '<br>Org Address ID: ' . $row->addressID;
				$key = $row->address1 . ', ' . $row->town . ',' . $row->postcode;
				// check in cache first
				if (array_key_exists($key, $geocode_cache)) {
					$res_geocode = $geocode_cache[$key];
				} else {
					// look up
					$res_geocode = geocode_address($row->address1, $row->town, $row->postcode);
					$geocode_cache[$key] = $res_geocode;
				}
				// if something, store
				if ($res_geocode) {
					$where = array(
						'addressID' => $row->addressID
					);
					$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('orgs_addresses');
					echo ' OK';
				} else {
					echo ' ERROR';
				}
			}
		}

		// family contacts
		$where = array(
			'location' => NULL
		);
		$res2 = $this->db->select('contactID, address1, town, postcode')
		->from('family_contacts')
		->where($where)
		->limit(50)
		->get();
		if ($res2->num_rows() > 0) {
			foreach ($res2->result() as $row) {
				echo '<br>Contact Address ID: ' . $row->addressID;
				$key = $row->address1 . ', ' . $row->town . ',' . $row->postcode;
				// check in cache first
				if (array_key_exists($key, $geocode_cache)) {
					$res_geocode = $geocode_cache[$key];
				} else {
					// look up
					$res_geocode = geocode_address($row->address1, $row->town, $row->postcode);
					$geocode_cache[$key] = $res_geocode;
				}
				// if something, store
				if ($res_geocode) {
					$where = array(
						'contactID' => $row->contactID
					);
					$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('family_contacts');
					echo ' OK';
				} else {
					echo 'ERROR';
				}
			}
		}

		if ($res->num_rows() > 0 || $res2->num_rows() > 0) {
			?><script>
				setTimeout(function() {
					window.location = '<?php echo site_url('tools/geocode_addresses');?>';
				}, 500);
			</script><?php
		} else {
			echo 'All done';
		}
	}

}

/* End of file tools.php */
/* Location: ./application/controllers/tools.php */
