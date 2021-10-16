<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Revert_add_on_cart_update_trigger extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function down()
	{
		$cart = $this->db->dbprefix('bookings_cart');
		$sessions = $this->db->dbprefix('bookings_cart_sessions');
		$this->db->query("
			create trigger app_bookings_cart_sessions_insert
				before insert
				on $sessions
				for each row
			BEGIN
				UPDATE $cart set modified = NOW() where $cart.cartID = new.cartID;
			END;
		");
//		$this->db->query("
//			create trigger app_bookings_cart_sessions_update
//				before update
//				on $sessions
//				for each row
//			BEGIN
//				UPDATE $cart set modified = NOW() where $cart.cartID = new.cartID;
//			END;
//		");
//		$this->db->query("
//			create trigger app_bookings_cart_sessions_delete
//				before delete
//				on $sessions
//				for each row
//			BEGIN
//				UPDATE $cart set modified = NOW() where $cart.cartID = old.cartID;
//			END;
//		");
	}

	public function up()
	{
		$this->db->query('DROP TRIGGER app_bookings_cart_sessions_insert');
//		$this->db->query('DROP TRIGGER app_bookings_cart_sessions_update');
//		$this->db->query('DROP TRIGGER app_bookings_cart_sessions_delete');
	}
}
