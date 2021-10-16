<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// we only care about those tables which block parent table deletions, not those set to cascade
$config = [
	'tables' => [
		'bookings' => [
			'key' => 'bookingID',
			'dependencies' => [
				'bookings_attachments' => [
					'name' => 'attachments',
					'link' => 'bookings/attachments/%d',
					'vars' => [
						'bookingID'
					]
				],
				'bookings_blocks' => [
					'name' => 'blocks',
					'link' => 'bookings/blocks/%d',
					'vars' => [
						'bookingID'
					]
				],
				'bookings_cart_monitoring' => [
					'name' => 'monitoring data'
				],
				/*'bookings_individuals_old' => [],
				'bookings_individuals_sessions_old' => [],*/
				'bookings_invoices' => [
					'name' => 'invoices',
					'link' => 'bookings/finances/invoices/%d',
					'vars' => [
						'bookingID'
					]
				],
				'bookings_vouchers' => [
					'name' => 'vouchers',
					'link' => 'bookings/vouchers/%d',
					'vars' => [
						'bookingID'
					]
				],
			]
		],
		'bookings_blocks' => [
			'key' => 'blockID',
			'dependencies' => [
				'bookings_lessons' => [
					'name' => 'sessions',
					'link' => 'bookings/sessions/%d/%d',
					'vars' => [
						'bookingID',
						'blockID'
					]
				],
				'bookings_costs' => [
					'name' => 'costs',
					'link' => 'bookings/costs/%d',
					'vars' => [
						'blockID'
					]
				],
				'bookings_cart_sessions' => [
					'name' => 'participants/cart items',
					'link' => 'bookings/participants/%d',
					'vars' => [
						'blockID'
					]
				],
				'bookings_attendance_names_sessions' => [
					'name' => 'participants',
					'link' => 'bookings/participants/%d',
					'vars' => [
						'blockID'
					]
				],
				'bookings_attendance_names' => [
					'name' => 'participants',
					'link' => 'bookings/participants/%d',
					'vars' => [
						'blockID'
					]
				],
			]
		],
		'bookings_lessons' => [
			'key' => 'lessonID',
			'dependencies' => [
				'bookings_attendance_names_sessions' => [
					'name' => 'participants',
					'link' => 'bookings/participants/%d',
					'vars' => [
						'blockID'
					]
				],
				'bookings_cart_sessions' => [
					'name' => 'participants/cart items',
					'link' => 'bookings/participants/%d',
					'vars' => [
						'blockID'
					]
				],
				/*'bookings_individuals_sessions_old' => [],*/
				'bookings_lessons_attachments' => [
					'name' => 'attachments',
					'link' => 'sessions/attachments/%d',
					'vars' => [
						'lessonID'
					]
				],
				'bookings_lessons_exceptions' => [
					'name' => 'exceptions',
					'link' => 'sessions/exceptions/%d',
					'vars' => [
						'lessonID'
					]
				],
				'bookings_lessons_notes' => [
					'name' => 'notes/evaluations',
					'link' => 'sessions/notes/%d',
					'vars' => [
						'lessonID'
					]
				],
				'bookings_lessons_staff' => [
					'name' => 'staff',
					'link' => 'sessions/staff/%d',
					'vars' => [
						'lessonID'
					]
				],
				'timesheets_expenses' => [
					'name' => 'timesheets',
					'link' => 'finance/timesheets'
				],
				'timesheets_items' => [
					'name' => 'timesheets',
					'link' => 'finance/timesheets'
				]
			]
		]
	]
];

/* End of file table_dependencies.php */
/* Location: ./application/config/table_dependencies.php */
