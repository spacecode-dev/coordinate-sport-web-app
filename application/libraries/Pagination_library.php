<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pagination_library {

	private $CI;
	public $start = 0;
	public $amount = 25;
	public $total_pages = 1;
	public $current_page = 1;
	public $view = NULL;
	public $is_search = FALSE;
	public $fa_weight = 'far'; // font awesome weight

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();

		$this->amount = $this->CI->settings_library->get('items_per_page');

		if ($this->amount == 0) {
			$this->amount = 25;
		}
	}

	public function calc($total_items) {

		$segments = $this->CI->uri->segment_array();

		if (count($segments) > 0) {
			$last = end($segments);
			$z = prev($segments);

			switch ($z) {
				case 'page':
					if (ctype_digit($last)) {
						$this->current_page = $last;
					}
					break;
				case 'view':
					if ($last == 'all') {
						$this->current_page = 1;
						$this->view_all();
					}
					break;
			}
		}

		$this->total_pages = ceil($total_items/$this->amount);

		if ($this->current_page <= 0) {
			$this->current_page = 1;
		}

		$this->start = intval(($this->current_page - 1) * $this->amount);

		return TRUE;
	}

	public function calc_by_url($total_items) {
		parse_str($_SERVER['QUERY_STRING'], $url_params);

		$this->current_page = isset($url_params['page']) ? $url_params['page'] : 1;

		$this->total_pages = ceil($total_items/$this->amount);

		if ($this->current_page <= 0) {
			$this->current_page = 1;
		}

		$this->start = intval(($this->current_page - 1) * $this->amount);

		return TRUE;
	}

	public function view_all() {
		$this->amount = 9999999;
		$this->view = 'all';
	}

	public function is_search() {
		$this->view_all();
		$this->is_search = TRUE;
	}

	public function display($base_url = NULL, $flag = NULL) {

		$return = NULL;

		$order = '';
		if (isset($_GET['order'])) {
			$i = 0;
			$order = '?';
			$count = count($_GET['order']);
			foreach ($_GET['order'] as $key => $value) {
				$order .= 'order[' . $key . ']=' . $value;
				$i++;
				if ($i < $count) {
					$order .= '&';
				}
			}
		}
		$url = '';
		if($flag != NULL)
			$url = '/page/1';

		// if viewing all on one page
		if ($this->view == 'all') {

			if ($this->is_search != TRUE) {
				$return .= '<nav class="mt-1" aria-label="Pagination">';
				$return .= '<ul class="pagination pagination-md">';
				$return .= '<li class="page-item">' . anchor(site_url($base_url.$url), 'View by Page', ['class'=>'page-link']) . '</li>';
				$return .= '</ul>';
				$return .= '</nav>';
			}

		} else if ($this->total_pages > 1) {
			// if more than one, do something
			$return .= '<nav class="mt-1" aria-label="Pagination">';
			$return .= '<ul class="pagination pagination-md">';


			if ($this->current_page != 1) {
				$return .= '<li class="page-item">' . anchor($base_url . '/page/1' . $order, 'First', ['class'=>'page-link']) . '</li>';
				$return .= '<li class="page-item">' . anchor($base_url . '/page/' . ($this->current_page - 1) . $order, '&laquo;', ['class'=>'page-link']) . '</li>';
			}

			if ($this->total_pages > 10) {
				$i = 1;
				while ($i <= $this->total_pages) {
					if ($i >= $this->current_page - 2 && $i <= $this->current_page + 2) {
						if ($i == $this->current_page) {
							$return .= '<li class="page-item active">' . anchor($base_url . '/page/' . $i . $order, $i, ['class'=>'page-link']) . "</li>";
						} else {
							$return .= '<li class="page-item">' . anchor($base_url . '/page/' . $i . $order, $i, ['class'=>'page-link']) . '</li>';
						}
					} else {
						if ($i == $this->current_page - 3) {
							$return .= '<li class="page-item">' . anchor($base_url . '/page/' . $this->current_page . $order, '...', ['class'=>'no-action page-link']) . '</li>';
						} else if ($i == $this->current_page + 3) {
							$return .= '<li class="page-item">' . anchor($base_url . '/page/' . $this->current_page . $order, '...', ['class'=>'no-action page-link']) . '</li>';
						}
					}
					$i++;
				}
			} else {
				$i = 1;
				while ($i <= $this->total_pages) {
					if ($i == $this->current_page) {
						$return .= '<li class="page-item active">' . anchor($base_url . '/page/' . $i . $order, $i, ['class'=>'page-link']) . "</li>";
					} else {
						$return .= '<li class="page-item">' . anchor($base_url . '/page/' . $i . $order, $i, ['class'=>'page-link']) . '</li>';
					}
					$i++;
				}
			}

			if ($this->current_page != $this->total_pages) {
				$return .= '<li class="page-item">' . anchor($base_url . '/page/' . ($this->current_page + 1) . $order, '&raquo;', ['class'=>'page-link']) . '</li>';
				$return .= '<li class="page-item">' . anchor($base_url . '/page/' . $this->total_pages . $order, 'Last', ['class'=>'page-link']) . '</li>';
			}

			if ($this->view != 'all') {
				$return .= '<li class="page-item">' . anchor(site_url($base_url . '/view/all'), 'View All', ['class'=>'page-link']) . '</li>';
			}

			$return .= '</ul>';
			$return .= '</nav>';

		}

		return $return;
	}


	//display pagination, but page will be in url
	public function display_get($base_url = NULL) {
		$return = NULL;

		$order = '';
		if (isset($_GET['order'])) {
			$i = 0;
			$order = '?';
			$count = count($_GET['order']);
			foreach ($_GET['order'] as $key => $value) {
				$order .= 'order[' . $key . ']=' . $value;
				$i++;
				if ($i < $count) {
					$order .= '&';
				}
			}
		}

		parse_str($_SERVER['QUERY_STRING'], $url_params);

		if ($this->total_pages > 1) {
			// if more than one, do something
			$return .= '<div>';
			$return .= '<ul class="pagination pagination-md">';


			if ($this->current_page != 1) {
				$return .= '<li class="page-item">' . anchor($base_url . $this->build_query_string(1, $url_params), 'First', ['class'=>'page-link']) . '</li>';
				$return .= '<li class="page-item">' . anchor($base_url . $this->build_query_string($this->current_page - 1, $url_params), '&laquo;', ['class'=>'page-link']) . '</li>';
			}

			if ($this->total_pages > 10) {
				$i = 1;
				while ($i <= $this->total_pages) {
					if ($i >= $this->current_page - 2 && $i <= $this->current_page + 2) {
						if ($i == $this->current_page) {
							$return .= '<li class="page-item active">' . anchor($base_url . $this->build_query_string($i, $url_params), $i, ['class'=>'page-link']) . "</li>";
						} else {
							$return .= '<li class="page-item">' . anchor($base_url . $this->build_query_string($i, $url_params), $i, ['class'=>'page-link']) . '</li>';
						}
					} else {
						if ($i == $this->current_page - 3) {
							$return .= '<li class="page-item">' . anchor($base_url . $this->build_query_string($this->current_page, $url_params), '...', ['class'=>'no-action page-link']) . '</li>';
						} else if ($i == $this->current_page + 3) {
							$return .= '<li class="page-item">' . anchor($base_url . $this->build_query_string($this->current_page, $url_params), '...', ['class'=>'no-action page-link']) . '</li>';
						}
					}
					$i++;
				}
			} else {
				$i = 1;
				while ($i <= $this->total_pages) {
					if ($i == $this->current_page) {
						$return .= '<li class="page-item active">' . anchor($base_url . $this->build_query_string($i, $url_params), $i, ['class'=>'page-link']) . "</li>";
					} else {
						$return .= '<li class="page-item">' . anchor($base_url . $this->build_query_string($i, $url_params), $i, ['class'=>'page-link']) . '</li>';
					}
					$i++;
				}
			}

			if ($this->current_page != $this->total_pages) {
				$return .= '<li class="page-item">' . anchor($base_url . $this->build_query_string($this->current_page + 1, $url_params), '&raquo;', ['class'=>'page-link']) . '</li>';
				$return .= '<li class="page-item">' . anchor($base_url . $this->build_query_string($this->total_pages, $url_params), 'Last', ['class'=>'page-link']) . '</li>';
			}

			$return .= '</ul>';
			$return .= '</div>';

		}

		return $return;
	}

	private function build_query_string($page = 1, $params = []) {
		$params['page'] = $page;
		return '?' . http_build_query($params);
	}
}
