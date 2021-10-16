<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if(!function_exists('directory_copy')) {
	function directory_copy($src,$dst) {
		//preparing the paths
		$src=rtrim($src,'/');
		$dst=rtrim($dst,'/');

		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					directory_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}
}