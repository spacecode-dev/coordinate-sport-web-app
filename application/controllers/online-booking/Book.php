<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH.'/traits/Book_trait.php';

class Book extends Online_Booking_Controller {
	use Book_trait;

	private $fa_weight = 'fas';
	private $in_crm = FALSE;
	private $cart_base = '';
	private $buttons = NULL;

	public function __construct() {
		parent::__construct();
	}

	// redirect old links from previous booking site, see routes.php
	public function redirect_old_links($path, $ID = NULL) {
		$redirect = NULL;
		switch ($path) {
			case 'book':
				$redirect = '/';
				break;
			case 'profile':
				$redirect = 'account/profile';
				break;
			case 'bookings':
				$redirect = 'account';
				break;
			case 'payments':
				$redirect = 'account/payments';
				break;
			case 'payment-plans':
				$redirect = 'account/payment-plans';
				break;
			case 'login':
				$redirect = 'account/login';
				break;
			case 'register':
				$redirect = 'account/register';
				break;
			case 'logout':
				$redirect = 'account/logout';
				break;
			case 'reset':
				$redirect = 'account/reset';
				break;
			case 'event':
				$redirect = 'event/' . $ID;
				break;
			case 'make':
				$redirect = 'book/' . $ID;
				break;
			case 'dept':
				$redirect = 'dept/' . $ID;
				break;
		}
		if (empty($redirect)) {
			show_404();
		}
		redirect($redirect, 'auto', 301);
	}
}

/* End of file Book.php */
/* Location: ./application/controllers/online-booking/Book.php */
