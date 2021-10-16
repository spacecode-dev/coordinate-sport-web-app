<?php

require_once APPPATH.'repositories/Offers/OffersRepository.php';

class OffersModel
{
	public function getCombinedOffers($offerID) {
		$offer =  OffersRepository::getInstance()->getById($offerID);

		return OffersRepository::getInstance()->searchList([
			'staffID' 		=> $offer->staffID,
			'combined_with' => $offer->combined_with,
			'status' 		=> 'offered',
			'type' 			=> $offer->type,
			'offer_type' 	=> $offer->offer_type,
			'groupID' 		=> $offer->groupID
		]);
	}

	public function search($strictArray = [], $likeArray = [], $amount = null, $start = null, $order = null)
	{
		return OffersRepository::getInstance()->searchList($strictArray, $likeArray, $amount, $start, $order);
	}
}
