<?php
require_once APPPATH.'repositories/Orgs/OrgsSafetyRepository.php';

class OrgsSafetyModel
{
	public function getById($id) {
		return OrgsSafetyRepository::getInstance()->getById($id);
	}

	public function create($data) {
		return OrgsSafetyRepository::getInstance()->create($data);
	}
}
