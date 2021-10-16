<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/



/* End of file hooks.php */
/* Location: ./application/config/hooks.php */

if (getenv('DISABLE_ACTIVITY') != 1) {
	$hook['post_controller_constructor'] = array(
	    'class' => 'Http_request_logger',
	    'function' => 'log',
	    'filename' => 'http_request_logger.php',
	    'filepath' => 'hooks',
	    'params' => array()
	);
}
