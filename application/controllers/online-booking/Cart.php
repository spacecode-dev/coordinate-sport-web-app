<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH.'/traits/Cart_trait.php';

class Cart extends Online_Booking_Controller {
	use Cart_trait;

	private $fa_weight = 'fas';
	private $in_crm = FALSE;
	private $cart_base = '';
	private $buttons = NULL;

	public function __construct() {
		parent::__construct();
	}
}

/* End of file Cart.php */
/* Location: ./application/controllers/online-booking/Cart.php */
