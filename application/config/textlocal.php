<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config = array(
	'apikey' => '85SajseFG94-XWHzqiToiwwG85uc2g2t8QeHVTBIIp',
	'from' => 'Coordinate',
	'report_url' => site_url('webhooks/smsreport'),
	'cron_limit' => 5
);

// if production, increase limit
if (substr(ENVIRONMENT, 0, 10) == 'production') {
	$config['cron_limit'] = 100;
}

/* End of file textlocal.php */
/* Location: ./application/config/textlocal.php */
