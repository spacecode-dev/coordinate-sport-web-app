<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_on_delete_booking_cart_trigger extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up()
	{
		$cart = $this->db->dbprefix('bookings_cart');
		$bikeability = $this->db->dbprefix('bookings_cart_bikeability');
		$monitoring = $this->db->dbprefix('bookings_cart_monitoring');
		$sessions = $this->db->dbprefix('bookings_cart_sessions');
		$vouchers = $this->db->dbprefix('bookings_cart_vouchers');
		$this->db->query("
			CREATE TRIGGER on_delete_booking_cart
			BEFORE DELETE ON $cart
			FOR EACH ROW
			BEGIN
				DELETE FROM $bikeability WHERE $bikeability.cartID = OLD.cartID;
				DELETE FROM $monitoring WHERE $monitoring.cartID = OLD.cartID;
				DELETE FROM $sessions WHERE $sessions.cartID = OLD.cartID;
				DELETE FROM $vouchers WHERE $vouchers.cartID = OLD.cartID;
			END;
		");
	}

	public function down()
	{
		$this->db->query('DROP TRIGGER on_delete_booking_cart');
	}
}
