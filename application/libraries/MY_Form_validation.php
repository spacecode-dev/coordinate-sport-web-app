<?php
class MY_Form_validation extends CI_Form_validation
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	* Error Array
	*
	* Returns the error messages as an array
	*
	* @return  array
	*/
	function error_array()
	{
		if (count($this->_error_array) === 0)
		{
			return FALSE;
		}
		else
		{
			return $this->_error_array;
		}
	}

	/**
	 * Required - add support for arrays
	 *
	 * @param	mixed
	 * @return	bool
	 */
	public function required($val)
	{
		if (is_array($val) && count($val) == 0) {
			return FALSE;
		}

		if (is_string($val) && empty($val)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 *
	 * Fails if value is in array
	 *
	 * @param $val
	 * @param string $string_values
	 * @return bool
	 */
	public function not_in_list($val, $string_values = '') {

		$array = explode(',', $string_values);

		if (in_array($val, $array)) {
//			$this->CI->form_validation->set_message('not_in_list', 'Value already exists, please enter a new one.');
			return false;
		}

		return true;
	}
}
