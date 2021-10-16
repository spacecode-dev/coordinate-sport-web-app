<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

abstract class Base_Controller extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		// set default timezone
		date_default_timezone_set('Europe/London');

		// if under maintenance
		if (getenv('MAINTENANCE_MODE') == 1 && $this->input->get('migrate') != '1') {
			set_status_header(503);
			echo $this->load->view('errors/html/error_maintenance', '', TRUE);
			exit();
		}

		// migrate to latest version if not already on it
		if ($this->migration->current() === FALSE) {
			show_error($this->migration->error_string());
		}

		// if under maintenance and migrating return version info
		if (getenv('MAINTENANCE_MODE') == 1 && $this->input->get('migrate') == '1') {
			$this->config->load('migration');
			$current_migration = $this->db->select('version')->from('migrations')->get()->result()[0]->version;
			echo 'OK: ' . $current_migration . '/' . $this->config->item('migration_version');
			exit();
		}

		// force SSL if not saving from zoho and forcing ssl
		if (PROTOCOL === 'https' && strpos($_SERVER["REQUEST_URI"], '/attachment/save/') === FALSE) {
			$this->crm_library->force_ssl();
		}

		// check if using AWS and determine upload path
		$this->config->load('aws', TRUE);
		if ($this->config->item('use_aws', 'aws') === TRUE) {
			$this->load->library('aws_library');
			if (getenv('DISABLE_ACTIVITY') != 1) {
				$this->load->library('activity_library');
			}
			$s3_bucket = $this->aws_library->init_s3();
			define('AWS', TRUE);
			$path = 's3://' . $s3_bucket . '/';
		} else {
			define('AWS', FALSE);
			$path = APPPATH . '../public/uploads/';
		}

		// shared path
		define('UPLOADPATH_SHARED', $path);
		// account specific path
		if (isset($this->auth->user->accountID) && !empty($this->auth->user->accountID)) {
			$path .=  $this->auth->user->accountID . '/';
		}
		define('UPLOADPATH', $path);

		// if profiling
		if (getenv('CI_PROFILER') == 1) {
			$this->output->enable_profiler(TRUE);
		}
	}

	/**
	 * run a view through a template
	 * @param  string $content_view
	 * @param  array  $data
	 * @param  string $template
	 * @return void
	 */
	public function crm_view($content_view, $data = array(), $template = 'templates/master')
	{
		$data['content'] = $this->load->view($content_view, $data, true);

		$this->load->view($template, $data);
	}
}
