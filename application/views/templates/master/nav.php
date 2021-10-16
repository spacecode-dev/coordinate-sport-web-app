<?php
// Dashboard
$menu_items = [
	'dashboard' => [
		'title' => 'Dashboard',
		'icon' => 'fa-tachometer-alt',
		'url' => '/'
	]
];
// Your Timetable
if ($this->auth->has_features(array('bookings_timetable', 'bookings_timetable_own')) && $this->auth->user->non_delivery != 1) {
	$menu_items['timetable_own'] = [
		'title' => 'Your Timetable',
		'icon' => 'fa-calendar-check',
		'url' => 'timetable'
	];
}
// Offer & Accept
if ($this->auth->has_features(array('offer_accept_manual')) && in_array($this->auth->user->department, array('directors', 'management'))) {
	$menu_items['acceptance_manual'] = [
		'title' => 'Offer &amp; Accept',
		'icon' => 'fa-check-square',
		'url' => 'acceptance_manual',
		'sub_items' => [
			'acceptance_manual_own' => [
				'title' => 'Own',
				'url' => 'acceptance_manual'
			],
			'acceptance_manual_all' => [
				'title' => 'All',
				'url' => 'acceptance_manual/all'
			]
		]
	];
}
// Accept Sessions
if (($this->auth->has_features(array('offer_accept')) || $this->auth->has_features(array('offer_accept_manual')))
	&& !in_array($this->auth->user->department, array('directors', 'management'))) {
	$menu_items['acceptance'] = [
		'title' => 'Accept Sessions',
		'icon' => 'fa-check-square',
		'url' => 'acceptance'
	];
}
// Session Evaluations
if ($this->auth->has_features(array('session_evaluations'))) {
	$can_approve = FALSE;
	if (in_array($this->auth->user->department, array('directors', 'management', 'headcoach'))) {
		$can_approve = TRUE;
	}
	$menu_items['evaluations'] = [
		'title' => 'Session Evaluations',
		'icon' => 'fa-clipboard',
		'url' => 'evaluations'
	];
	if ($can_approve === TRUE) {
		$menu_items['evaluations']['sub_items'] = [
			'evaluations' => [
				'title' => 'Own',
				'url' => 'evaluations'
			],
			'evaluations_all' => [
				'title' => 'All',
				'url' => 'evaluations/all'
			],
			'evaluations_approvals' => [
				'title' => 'Approvals',
				'url' => 'evaluations/approvals'
			]
		];
	}
}
// Bookings
if ($this->auth->has_features('bookings_timetable') || $this->auth->has_features('bookings_bookings') || $this->auth->has_features('bookings_projects') || $this->auth->has_features('bookings_exceptions')) {
	// deny from coach + full time coach
	if (!in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
		$menu_items['bookings'] = [
			'title' => 'Bookings',
			'icon' => 'fa-calendar-alt',
			'url' => 'bookings/dashboard',
			'sub_items' => [
				'dashboard' => [
					'title' => 'Dashboard',
					'url' => 'bookings/dashboard'
				]
			]
		];
		if ($this->auth->has_features('bookings_projects')) {
			$menu_items['bookings']['sub_items']['projects'] = [
				'title' => 'Projects',
				'url' => 'bookings/projects',
				'sub_items' => [
					'projects' => [
						'title' => 'All',
						'url' => 'bookings/projects'
						]
					]
				];
			// get project types
			$where = array(
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('project_types')->where($where)->order_by('name asc')->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$menu_items['bookings']['sub_items']['projects']['sub_items']['projects_type_' . $row->typeID] = [
						'title' => $row->name,
						'url' => 'bookings/projects/' . $row->typeID
					];
				}
			}
		}

		if ($this->auth->has_features('bookings_bookings')) {
			$menu_items['bookings']['sub_items']['bookings'] = [
				'title' => 'Contracts',
				'url' => 'bookings'
			];
		}
		if ($this->auth->has_features('bookings_timetable')) {
			$menu_items['bookings']['sub_items']['timetable'] = [
				'title' => 'Timetable',
				'url' => 'bookings/timetable'
			];
		}
	}
}
// Customers
if ($this->auth->has_features('customers_schools') || $this->auth->has_features('customers_schools_prospects') || $this->auth->has_features('customers_orgs') || $this->auth->has_features('customers_orgs_prospects')) {
	// deny from coach + full time coach
	if (!in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
		$menu_items['customers'] = [
			'title' => $this->settings_library->get_label('customers'),
			'icon' => 'fa-laptop'
		];
		if($this->auth->has_features('customers_schools')){
			$menu_items['customers']['url'] = 'customers/schools';
		}else if($this->auth->has_features('customers_orgs')){
			$menu_items['customers']['url'] = 'customers/organisations';
		}else if($this->auth->has_features('customers_schools_prospects')){
			$menu_items['customers']['url'] ='customers/prospects/schools';
		}else if($this->auth->has_features('customers_orgs_prospects')){
			$menu_items['customers']['url'] ='customers/prospects/organisations';
		}

		if ($this->auth->has_features('customers_schools')) {
			$menu_items['customers']['sub_items']['schools'] = [
				'title' => 'Schools',
				'url' => 'customers/schools'
			];
		}
		if ($this->auth->has_features('customers_orgs')) {
			$menu_items['customers']['sub_items']['organisations'] = [
				'title' => 'Organisations',
				'url' => 'customers/organisations'
			];
		}
		if ($this->auth->has_features('customers_schools_prospects')) {
			$menu_items['customers']['sub_items']['prospective-schools'] = [
				'title' => 'Prospective Schools',
				'url' => 'customers/prospects/schools'
			];
		}
		if ($this->auth->has_features('customers_orgs_prospects')) {
			$menu_items['customers']['sub_items']['prospective-organisations'] = [
				'title' => 'Prospective Organisations',
				'url' => 'customers/prospects/organisations'
			];
		}
	}
}
// Participants
if ($this->auth->has_features('online_booking_subscription_module')) {
	if ($this->auth->has_features('participants')) {
		// deny from coach + full time coach
		if (!in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			$menu_items['participants'] = [
				'title' => $this->settings_library->get_label('participants'),
				'icon' => 'fa-users',
				'url' => 'participants'
			];
			$menu_items['participants']['sub_items']['participants'] = [
				'title' => 'Participants',
				'url' => 'participants'
			];
			$menu_items['participants']['sub_items']['subscriptions'] = [
				'title' => 'Subscriptions',
				'url' => 'participants/subscriptions/all'
			];
		}
	}

}else {
	if ($this->auth->has_features('participants')) {
		// deny from coach + full time coach
		if (!in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			$menu_items['participants'] = [
				'title' => $this->settings_library->get_label('participants'),
				'icon' => 'fa-users',
				'url' => 'participants'
			];
		}
	}
}
// Staff - deny from coach + full time coach or if is admin account deny from anything but management and directors
if ((!in_array($this->auth->user->department, array('coaching', 'fulltimecoach')) && $this->auth->account->admin == 0) || ($this->auth->account->admin == 1 && in_array($this->auth->user->department, array('management', 'directors')))) {
	$menu_items['staff'] = [
		'title' => 'Staff',
		'icon' => 'fa-sitemap',
		'url' => 'staff'
	];
}
// Resources
if ($this->auth->has_features('resources')) {
	$menu_items['resources'] = [
		'title' => 'Resources',
		'icon' => 'fa-folder',
		'url' => 'resources'
	];
}
// Messages
if ($this->auth->has_features('messages')) {
	$menu_items['messages'] = [
		'title' => 'Messages',
		'icon' => 'fa-inbox',
		'url' => 'messages',
	];
	$menu_items['messages']['sub_items']['staff'] = [
		'title' => 'Staff',
		'url' => 'messages/inbox/staff'
	];
	if ($this->auth->user->department != 'headcoach' && $this->auth->user->department != 'fulltimecoach' && $this->auth->user->department != 'coaching') {
		$menu_items['messages']['sub_items']['schools'] = [
			'title' => 'Schools',
			'url' => 'messages/inbox/schools'
		];
		$menu_items['messages']['sub_items']['organisations'] = [
			'title' => 'Organisations',
			'url' => 'messages/inbox/organisations'
		];
		$menu_items['messages']['sub_items']['participants'] = [
			'title' => 'Participants',
			'url' => 'messages/inbox/participants'
		];
	}

	if ($this->auth->user->department == 'directors' && $this->auth->account->admin == 1) {
		$menu_items['messages']['sub_items']['templates'] = [
			'title' => 'Templates',
			'url' => 'messages/templates/inbox'
		];
	}

}
// Equipment
if ($this->auth->has_features('equipment')) {
	$menu_items['equipment'] = [
		'title' => 'Equipment',
		'icon' => 'fa-futbol',
		'url' => 'equipment'
	];
}
// Export

if ($this->auth->has_features('export')) {
	// directors and data protection officer only
	$where = array(
		'accountID' => $this->auth->user->accountID,
		'key' => 'data_protection_officer'
	);
	$data_protection_officer = array();
	$res = $this->db->from('accounts_settings')->where($where)->get();
	if($res->num_rows() > 0){
		foreach($res->result() as $result){
			$data_protection_officer = explode(",",$result->value);
		}
	}
	if (in_array($this->auth->user->department, array('directors')) || in_array($this->auth->user->staffID, $data_protection_officer)) {
		$menu_items['export'] = [
			'title' => 'Export Data',
			'icon' => 'fa-download',
			'url' => 'export'
		];
	}
}
// Accounts
if ($this->auth->has_features('accounts')) {
	$menu_items['accounts'] = [
		'title' => 'Accounts',
		'icon' => 'fa-server',
		'url' => 'accounts',
		'sub_items' => [
			'accounts' => [
				'title' => 'Accounts',
				'url' => 'accounts'
			],
			'plans' => [
				'title' => 'Plans',
				'url' => 'accounts/plans'
			]
		]
	];
	// directors and management only
	if (in_array($this->auth->user->department, array('directors', 'management'))) {
		$menu_items['accounts']['sub_items']['defaults'] = [
			'title' => 'Defaults',
			'url' => 'accounts/defaults',
			'sub_items' => [
				'defaults_general' => [
					'title' => 'General',
					'url' => 'accounts/defaults/listing/general'
				],
				'defaults_dashboard' => [
					'title' => 'Dashboard',
					'url' => 'accounts/defaults/dashboard'
				],
				'defaults_termsprivacy' => [
					'title' => 'Terms &amp; Privacy',
					'url' => 'accounts/defaults/termsprivacy'
				],
				'defaults_emailsms' => [
					'title' => 'Email & SMS',
					'url' => 'accounts/defaults/listing/emailsms'
				],
				'defaults_global' => [
					'title' => 'Global',
					'url' => 'accounts/defaults/global'
				],
				'defaults_safety' => [
					'title' => 'Health &amp; Safety',
					'url' => 'accounts/defaults/safety'
				],
				'defaults_integrations' => [
					'title' => 'Integrations',
					'url' => 'accounts/defaults/integrations'
				],
				'defaults_styling' => [
					'title' => 'Styling',
					'url' => 'accounts/defaults/styling'
				],
				'defaults_dashboardtriggers' => [
					'title' => 'Dashboard Triggers',
					'url' => 'accounts/dashboardtriggers'
				]
			]
		];
	}
}
// Timesheets
if ($this->auth->has_features('timesheets')) {
	// directors and management only
	if (in_array($this->auth->user->department, array('directors', 'management')) || $this->auth->user->department == 'headcoach') {
		$menu_items['timesheets'] = [
			'title' => 'Finance',
			'icon' => 'fa-sack-dollar',
			'sub_items' => [
				'timesheets_own' => [
					'title' => 'Your Timesheets',
					'url' => 'finance/timesheets/own'
				],
				'approvals_own' => [
					'title' => 'Your Approvals',
					'url' => 'finance/approvals/own'
				]
			]
		];
		if ($this->auth->has_features('staff_invoices')) {
			$menu_items['timesheets']['sub_items']['invoices_own'] = [
				'title' => 'Your Invoices',
				'url' => 'finance/invoices/own'
			];
		}
		if ($this->auth->user->department != 'headcoach') {
			$menu_items['timesheets']['sub_items']['timesheets'] = [
				'title' => 'Timesheets',
				'url' => 'finance/timesheets'
			];
			$menu_items['timesheets']['sub_items']['approvals'] = [
				'title' => 'Approvals',
				'url' => 'finance/approvals'
			];
			if ($this->auth->has_features('staff_invoices')) {
				$menu_items['timesheets']['sub_items']['invoices'] = [
					'title' => 'Invoices',
					'url' => 'finance/invoices'
				];
			}
		}
	} else {
		$menu_items['timesheets'] = [
			'title' => 'Finance',
			'icon' => 'fa-sack-dollar',
			'sub_items' => [
				'timesheets_own' => [
					'title' => 'Your Timesheets',
					'url' => 'finance/timesheets'
				]
			]
		];
		if ($this->auth->has_features('staff_invoices')) {
			$menu_items['timesheets']['sub_items']['invoices_own'] = [
				'title' => 'Invoices',
				'url' => 'finance/invoices'
			];
		}
	}
}
// Reports
if ($this->auth->has_features('reports')) {
	// directors and management only
	if (in_array($this->auth->user->department, array('directors', 'management'))) {
		$menu_items['reports'] = [
			'title' => 'Reports',
			'icon' => 'fa-chart-bar',
			'sub_items' => [
				'utilisation' => [
					'title' => 'Utilisation',
					'url' => 'reports/utilisation'
				]
			]
		];
		if ($this->auth->has_features('reports')) {
			$menu_items['reports']['sub_items']['timesheets'] = [
				'title' => 'Timesheets',
				'url' => 'reports/timesheets'
			];
		}
		if ($this->auth->has_features('payroll')) {
			$menu_items['reports']['sub_items']['payroll'] = [
				'title' => 'Payroll',
				'url' => 'reports/payroll'
			];
		}
		if ($this->auth->has_features('projectcode')) {
			$menu_items['reports']['sub_items']['project_code'] = [
				'title' => 'Project Code Costs',
				'url' => 'reports/project_code'
			];
		}
		if ($this->auth->has_features('bikeability')) {
			$menu_items['reports']['sub_items']['bikeability'] = [
				'title' => 'Bikeability',
				'url' => 'reports/bikeability'
			];
		}
		if ($this->auth->has_features('contracts')) {
			$menu_items['reports']['sub_items']['contracts'] = [
				'title' => 'Projects &amp; Contracts',
				'url' => 'reports/contracts'
			];
		}
		if ($this->auth->has_features('staff_performance')) {
			$menu_items['reports']['sub_items']['performance'] = [
				'title' => 'Staff Performance',
				'url' => 'reports/performance'
			];
		}
		$menu_items['reports']['sub_items']['projects'] = [
			'title' => 'Project Delivery',
			'url' => 'reports/projects'
		];
		$menu_items['reports']['sub_items']['activities'] = [
			'title' => 'Activity Type',
			'url' => 'reports/activities'
		];
		if ($this->auth->has_features('session_delivery')) {
			$menu_items['reports']['sub_items']['session_delivery'] = [
				'title' => 'Session Delivery',
				'url' => 'reports/session_delivery'
			];
		}
		if ($this->auth->has_features('participant_billing')) {
			$menu_items['reports']['sub_items']['payments'] = [
				'title' => 'Booking Payments & Transactions',
				'url' => 'reports/payments'
			];
		}
		if ($this->auth->has_features('marketing_report')) {
			$menu_items['reports']['sub_items']['marketing'] = [
				'title' => 'Marketing Data &amp; Privacy',
				'url' => 'reports/marketing'
			];
		}
		if ($this->auth->has_features('mileage')) {
			$menu_items['reports']['sub_items']['mileage'] = [
				'title' => 'Mileage',
				'url' => 'reports/mileage'
			];
		}
	}
}
// Check Ins
if ($this->auth->has_features('lesson_checkins') && in_array($this->auth->user->department, array('directors', 'management', 'headcoach'))) {
	$menu_items['checkins'] = [
		'title' => 'Check-ins',
		'icon' => 'fa-map-marker-alt',
		'url' => 'checkins'
	];
}
// User Activity
if (getenv('DISABLE_ACTIVITY') != 1 && ($this->auth->user->show_user_activity || $this->auth->account->admin)) {
	$menu_items['user-activity'] = [
		'title' => 'User Activity',
		'icon' => 'fa-book',
		'url' => 'user-activity'
	];
}
?><!--begin::Aside Menu-->
<div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
	<!--begin::Menu Container-->
	<div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">
		<!--begin::Menu Nav-->
		<ul class="menu-nav">
			<?php
			foreach ($menu_items as $key => $item) {
				$tag_open = 'span';
				$tag_close = 'span';
				if (array_key_exists('url', $item)) {
					if (substr($item['url'], 0, 4) !== 'http') {
						$item['url'] = site_url($item['url']);
					}
					$tag_open = 'a href="' . $item['url'] . '"';
					if (array_key_exists('target', $item)) {
						$tag_open .= ' target="' . $item['target'] . '"';
					}
					$tag_close = 'a';
				}
				?><li class="menu-item<?php
				 	if (array_key_exists('sub_items', $item)) {
						echo ' menu-item-submenu';
						if (isset($section) && $section == $key) {
							echo ' menu-item-open';
						}
					} else if (isset($section) && $section == $key) {
						echo ' menu-item-active';
					}
					?>"<?php if (array_key_exists('sub_items', $item)) { ?> aria-haspopup="true" data-menu-toggle="hover"<?php } ?>>
					<<?php echo $tag_open; ?> class="menu-link<?php if (array_key_exists('sub_items', $item)) { ?>  menu-toggle<?php } ?>">
						<?php
						if (array_key_exists('icon', $item)) {
							if (substr($item['icon'], 0, 3) == 'fa-') {
								?><i class="far <?php echo $item['icon']; ?> menu-icon"></i><?php
							}
						}
						?>
						<span class="menu-text"><?php echo $item['title']; ?></span>
						<?php if (array_key_exists('sub_items', $item)) { ?><i class="menu-arrow"></i><?php } ?>
					</<?php echo $tag_close; ?>>
					<?php
					if (array_key_exists('sub_items', $item)) {
						?><div class="menu-submenu">
							<i class="menu-arrow"></i>
							<ul class="menu-subnav">
								<?php
								foreach ($item['sub_items'] as $sub_key => $sub_item) {
									$tag_open = 'span';
									$tag_close = 'span';
									if (array_key_exists('url', $sub_item)) {
										if (substr($sub_item['url'], 0, 4) !== 'http') {
											$sub_item['url'] = site_url($sub_item['url']);
										}
										$tag_open = 'a href="' . $sub_item['url'] . '"';
										if (array_key_exists('target', $sub_item)) {
											$tag_open .= ' target="' . $sub_item['target'] . '"';
										}
										$tag_close = 'a';
									}
									?><li class="menu-item<?php
									 	if (array_key_exists('sub_items', $sub_item)) {
											echo ' menu-item-submenu';
											if (isset($current_page, $section) && $section == $key && substr($current_page, 0, strlen($sub_key)) == $sub_key) {
												echo ' menu-item-open';
											}
										} else if (isset($current_page, $section) && $section == $key && $current_page == $sub_key) {
											echo ' menu-item-active';
										}
										?>"<?php if (array_key_exists('sub_items', $sub_item)) { ?> aria-haspopup="true" data-menu-toggle="hover"<?php } ?>>
										<<?php echo $tag_open; ?> class="menu-link<?php if (array_key_exists('sub_items', $sub_item)) { ?>  menu-toggle<?php } ?>">
											<i class="menu-bullet menu-bullet-line">
												<span></span>
											</i>
											<span class="menu-text"><?php echo $sub_item['title']; ?></span>
											<?php if (array_key_exists('sub_items', $sub_item)) { ?><i class="menu-arrow"></i><?php } ?>
										</<?php echo $tag_close; ?>><?php
										if (array_key_exists('sub_items', $sub_item)) {
											?><div class="menu-submenu">
												<i class="menu-arrow"></i>
												<ul class="menu-subnav">
													<?php
													foreach ($sub_item['sub_items'] as $sub_sub_key => $sub_sub_item) {
														$tag_open = 'span';
														$tag_close = 'span';
														if (array_key_exists('url', $sub_sub_item)) {
															if (substr($sub_sub_item['url'], 0, 4) !== 'http') {
																$sub_sub_item['url'] = site_url($sub_sub_item['url']);
															}
															$tag_open = 'a href="' . $sub_sub_item['url'] . '"';
															if (array_key_exists('target', $sub_sub_item)) {
																$tag_open .= ' target="' . $sub_sub_item['target'] . '"';
															}
															$tag_close = 'a';
														}
														?><li class="menu-item<?php
															if (isset($current_page, $section) && $section == $key && $current_page == $sub_sub_key) {
																echo ' menu-item-active';
															}
															?>">
															<<?php echo $tag_open; ?> class="menu-link">
																<i class="menu-bullet menu-bullet-dot">
																	<span></span>
																</i>
																<span class="menu-text"><?php echo $sub_sub_item['title']; ?></span>
															</<?php echo $tag_close; ?>>
														</li><?php
													}
												?></ul>
											</div><?php
										}
									?></li><?php
								}
								?>
							</ul>
						</div><?php
					}
					?></li><?php
				}
			?>
		</ul>
		<!--end::Menu Nav-->
	</div>
	<!--end::Menu Container-->
</div>
<!--end::Aside Menu-->
