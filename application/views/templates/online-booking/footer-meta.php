<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('maps_frontend_api_key', 'google'); ?>"></script>
<script>var currency_symbol = '<?php echo
currency_symbol($this->online_booking->accountID); ?>';</script>
<script src="<?php echo $this->crm_library->asset_url('dist/js/components/online-booking.js'); ?>"></script>
