<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

// default to dashboard
$route['default_controller'] = "dashboard";

// if using online booking domain, load online booking routes
require_once( APPPATH .'helpers/crm_helper.php');
if (resolve_online_booking_domain() !== FALSE) {
	$route['default_controller'] = "online-booking/main";

	// online booking
	$route['calendar/(:num)/(:num)'] = "online-booking/main/index/calendar/$1/$2";
	$route['calendar'] = "online-booking/main/index/calendar";
	$route['list'] = "online-booking/main/index/list";
	$route['list/page/(:num)'] = "online-booking/main/index/list";
	$route['list/view/all'] = "online-booking/main/index/list";
	$route['map'] = "online-booking/main/index/map";
	$route['dept/(:num)'] = "online-booking/main/index/dept/$1";
	$route['activity/(:num)'] = "online-booking/main/index/activity/$1";
	$route['type/(:num)'] = "online-booking/main/index/type/$1";

	$route['event/(:num)'] = "online-booking/main/event/$1";
	$route['book/(:num)'] = "online-booking/book/index/$1";
	$route['book'] = "online-booking/book/redirect_old_links/book";
	$route['book/(:any)/(:num)'] = "online-booking/book/redirect_old_links/$1/$2";
	$route['book/(:any)'] = "online-booking/book/redirect_old_links/$1";
	$route['cart/(:any)/(:num)'] = "online-booking/cart/$1/$2";
	$route['cart/(:any)'] = "online-booking/cart/$1";
	$route['cart/remove_subscription/(:num)/(:num)'] = "online-booking/cart/remove_subscription/$1/$2";
	$route['cart'] = "online-booking/cart";
	$route['checkout'] = "online-booking/cart/index/true";
	$route['checkout/removevoucher/(:num)'] = "online-booking/cart/removevoucher/$1";

	$route['sagepay/(:any)'] = "online-booking/sagepay/index/$1";
	$route['sagepay'] = "online-booking/sagepay/index";

	$route['account'] = "online-booking/account";
	$route['account/reset/(:any)'] = "online-booking/account/reset/$1";
	$route['account/payment-plans'] = "online-booking/account/payment_plans";
	$route['account/(:any)'] = "online-booking/account/$1";
	$route['account/participants/(:any)'] = "online-booking/account/participants/$1";
	$route['account/participants/(:any)/(:any)'] = "online-booking/account/participants/$1/$2";
	$route['account/individual/(:any)'] = "online-booking/account/individual/$1";
	$route['account/privacy/confirm'] = "online-booking/account/privacy/confirm";
	$route['account/booking/(:num)'] = "online-booking/account/booking/$1";
	$route['account/subscription/(:num)'] = "online-booking/account/subscription/$1";
	$route['account/cancel_subscription/(:num)'] = "online-booking/account/cancel_subscription/$1";
	$route['account/shapeup/(:num)'] = "online-booking/account/shapeup_view/$1";
} else {
	// booking within crm
	$route['booking/book/(:num)'] = "booking/book/index/$1";
	$route['booking/book/(:num)/(:num)'] = "booking/book/index/$1/$2";
	$route['booking/checkout'] = "booking/cart/index/true";
	$route['booking/checkout/removevoucher/(:num)'] = "booking/cart/removevoucher/$1";
	$route['booking/book/new/child'] = "booking/book/participant";
	$route['booking/book/new/individual'] = "booking/book/individual";
}

// dashboard
$route['policies/confirm'] = "dashboard/acceptpolicies";
$route['removeoverride'] = "dashboard/removeoverride";

// tasks
$route['tasks/new'] = "tasks/edit//";

// user
$route['login'] = "user/login";
$route['logout'] = "user/logout";
$route['reset/(:any)'] = "user/reset/$1";
$route['reset'] = "user/reset";
$route['profile'] = "user/profile";
$route['password-change'] = "user/password_change";
$route['terms'] = "user/terms";

// calendar feed
$route['feed/(:any)'] = "bookings/timetable/ics_feed/$1";
$route['timetable/feed'] = "bookings/timetable/feed";

// timetable (own)
$route['timetable'] = "bookings/timetable/index///true";
$route['timetable/recall'] = "bookings/timetable/index///true";
$route['timetable/(:num)/(:num)'] = "bookings/timetable/index/$1/$2/true";
$route['timetable/(:num)/(:num)/recall'] = "bookings/timetable/index/$1/$2/true";
$route['timetable/confirm/(:num)/(:num)'] = "bookings/timetable/confirm/$1/$2/true";
$route['timetable/(:any)'] = "bookings/timetable/index/$1//true";

// bookings
$route['bookings'] = "bookings/main/index";
$route['bookings/dashboard'] = "bookings/main/dashboard";
$route['bookings/page/(:num)'] = "bookings/main/index";
$route['bookings/view/all'] = "bookings/main/index";
$route['bookings/recall'] = "bookings/main/index";
$route['bookings/new'] = "bookings/main/edit//booking";
$route['bookings/remove/(:num)/force'] = "bookings/main/remove/$1/force";
$route['bookings/remove/(:num)'] = "bookings/main/remove/$1";
$route['bookings/edit/(:num)'] = "bookings/main/edit/$1/information";
$route['bookings/edit/(:num)/(:any)'] = "bookings/main/edit/$1/$2";
$route['bookings/duplicate/(:num)'] = "bookings/main/duplicate/$1";
$route['bookings/jumpto/(:num)'] = "bookings/main/jumpto/$1";
$route['bookings/confirm/(:num)/(:any)'] = "bookings/main/confirm/$1/$2";
$route['bookings/default_pricing'] = "bookings/main/default_pricing";

$route['bookings/exceptions/page/(:num)'] = "bookings/exceptions/all";
$route['bookings/exceptions/view/all'] = "bookings/exceptions/all";
$route['bookings/exceptions/recall'] = "bookings/exceptions/all";
$route['bookings/exceptions/remove/(:num)/true'] = "bookings/exceptions/remove/$1/true";

$route['bookings/timetable/recall'] = "bookings/timetable/index";
$route['bookings/timetable/(:num)/(:num)'] = "bookings/timetable/index/$1/$2";
$route['bookings/timetable/(:num)/(:num)/recall'] = "bookings/timetable/index/$1/$2";
$route['bookings/timetable/(:any)/(:num)/recall'] = "bookings/timetable/index/$1/$2";
$route['bookings/timetable/(:any)/(:num)'] = "bookings/timetable/index/$1/$2";
$route['bookings/timetable/(:any)/recall'] = "bookings/timetable/index/$1";
$route['bookings/timetable/(:any)'] = "bookings/timetable/index/$1";

$route['bookings/projects'] = "bookings/main/index/event/true";
$route['bookings/projects/page/(:num)'] = "bookings/main/index/event/true";
$route['bookings/projects/view/all'] = "bookings/main/index/event/true";
$route['bookings/projects/recall'] = "bookings/main/index/event/true";
$route['bookings/new/project'] = "bookings/main/edit//booking/true";
$route['bookings/new/project/event'] = "bookings/main/edit//event/true";
$route['bookings/projects/(:num)'] = "bookings/main/projecttype/$1";

$route['bookings/contract'] = "bookings/main/edit//booking";
$route['bookings/contract/(:num)'] = "bookings/main/edit/$1/information";
$route['bookings/contract/(:num)/(:any)'] = "bookings/main/edit/$1/$2";
$route['bookings/contract/new'] = "bookings/main/edit//booking";
$route['bookings/course'] = "bookings/main/edit//booking/true";
$route['bookings/course/(:num)'] = "bookings/main/edit/$1/information";
$route['bookings/course/(:num)/(:any)'] = "bookings/main/edit/$1/$2";
$route['bookings/course/new'] = "bookings/main/edit//booking/true";
$route['bookings/event'] = "bookings/main/edit//event/true";
$route['bookings/event/(:num)'] = "bookings/main/edit/$1/information";
$route['bookings/event/(:num)/(:any)'] = "bookings/main/edit/$1/$2";
$route['bookings/event/new'] = "bookings/main/edit//event/true";

$route['bookings/finances/invoices/(:num)'] = "bookings/invoices/index/$1";
$route['bookings/finances/invoices/edit/(:num)'] = "bookings/invoices/edit/$1";
$route['bookings/finances/invoices/remove/(:num)'] = "bookings/invoices/remove/$1";
$route['bookings/finances/invoices/(:num)/new'] = "bookings/invoices/edit//$1";
$route['bookings/finances/invoices/(:num)/page/(:num)'] = "bookings/invoices/index/$1";
$route['bookings/finances/invoices/(:num)/view/all'] = "bookings/invoices/index/$1";
$route['bookings/finances/invoices/(:num)/recall'] = "bookings/invoices/index/$1";
$route['bookings/finances/invoices/invoice/(:num)'] = "bookings/invoices/invoice/$1";
$route['bookings/finances/invoices/uninvoice/(:num)'] = "bookings/invoices/uninvoice/$1";

$route['bookings/blocks/(:num)'] = "bookings/blocks/index/$1";
$route['bookings/blocks/(:num)/new'] = "bookings/blocks/edit//$1";
$route['bookings/blocks/(:num)/page/(:num)'] = "bookings/blocks/index/$1";
$route['bookings/blocks/(:num)/view/all'] = "bookings/blocks/index/$1";
$route['bookings/blocks/(:num)/recall'] = "bookings/blocks/index/$1";

$route['bookings/availabilitycal/(:num)'] = "bookings/availabilitycal/index/$1";

$route['bookings/exceptions/(:num)'] = "bookings/exceptions/index/$1";
$route['bookings/exceptions/(:num)/new'] = "bookings/exceptions/edit//$1";
$route['bookings/exceptions/(:num)/page/(:num)'] = "bookings/exceptions/index/$1";
$route['bookings/exceptions/(:num)/view/all'] = "bookings/exceptions/index/$1";
$route['bookings/exceptions/(:num)/recall'] = "bookings/exceptions/index/$1";

$route['bookings/subscriptions/(:num)'] = "bookings/subscriptions/index/$1";
$route['bookings/subscriptions/(:num)/new'] = "bookings/subscriptions/edit//$1";
$route['bookings/subscriptions/session/(:num)'] = "bookings/subscriptions/session/$1";
$route['bookings/subscriptions/sessionsave'] = "bookings/subscriptions/sessionsave";
$route['bookings/subscriptions/cancel/(:num)/(:num)'] = "bookings/subscriptions/cancel/$1/$2";
$route['bookings/subscriptions/remove/(:num)'] = "bookings/subscriptions/remove/$1";

$route['bookings/sessions/(:num)'] = "bookings/sessions/index/$1";
$route['bookings/sessions/(:num)/(:num)'] = "bookings/sessions/index/$1/$2";
$route['bookings/sessions/(:num)/(:num)/(:any)'] = "bookings/sessions/index/$1/$2/$3";
$route['bookings/sessions/(:num)/new'] = "bookings/sessions/edit//$1";
$route['bookings/sessions/(:num)/(:num)/page/(:num)'] = "bookings/sessions/index/$1/$2";
$route['bookings/sessions/(:num)/(:num)/view/all'] = "bookings/sessions/index/$1/$2";
$route['bookings/sessions/(:num)/(:num)/recall'] = "bookings/sessions/index/$1/$2";

$route['bookings/participants/(:num)'] = "bookings/participants/index/$1";
$route['bookings/participants/(:num)/(:num)'] = "bookings/participants/index/$1/$2";
$route['bookings/participants/(:num)/new/(:num)/(:num)'] = "bookings/participants/edit//$1/$2/$3";
$route['bookings/participants/(:num)/new/(:num)'] = "bookings/participants/edit//$1/$2";
$route['bookings/participants/(:num)/new'] = "bookings/participants/edit//$1";
$route['bookings/participants/(:num)/recall'] = "bookings/participants/index/$1";
$route['bookings/participants/(:num)/(:num)/recall'] = "bookings/participants/index/$1/$2";
$route['bookings/participants/print/(:num)'] = "bookings/participants/print_view/$1";
$route['bookings/participants/print/(:num)/(:num)'] = "bookings/participants/print_view/$1/$2";
$route['bookings/participants/viewdetailoverview/(:num)'] = "bookings/participants/print_view/$1/$2";
$route['bookings/participants/viewdetail/(:num)/(:num)/(:num)'] = "bookings/participants/viewdetail/$1/$2/$3";
$route['bookings/participants/bikeability/(:any)/(:num)/(:num)'] = "bookings/participants/bikeability_overall/$1/$2/$3";

$route['bookings/attachments/(:num)'] = "bookings/attachments/index/$1";
$route['bookings/attachments/(:num)/new'] = "bookings/attachments/edit//$1";
$route['bookings/attachments/(:num)/page/(:num)'] = "bookings/attachments/index/$1";
$route['bookings/attachments/(:num)/view/all'] = "bookings/attachments/index/$1";
$route['bookings/attachments/(:num)/recall'] = "bookings/attachments/index/$1";

$route['bookings/birthday/(:num)'] = "bookings/birthday/index/$1";

$route['bookings/messaging/(:num)'] = "bookings/notifications/index/$1";
$route['bookings/history/(:num)'] = "bookings/notifications/history/$1";
$route['bookings/notification/view_org/(:num)'] = "bookings/notifications/view_org/$1";
$route['bookings/notification/view_family/(:num)'] = "bookings/notifications/view_family/$1";

$route['bookings/finances/profit/(:num)'] = "bookings/profit/index/$1";
$route['bookings/finances/profit/(:num)/export'] = "bookings/profit/index/$1/true";

$route['bookings/report/(:num)'] = "bookings/report/index/$1";
$route['bookings/report/(:num)/export'] = "bookings/report/index/$1/true";

$route['bookings/vouchers/(:num)'] = "bookings/vouchers/index/$1";
$route['bookings/vouchers/(:num)/new'] = "bookings/vouchers/edit//$1";
$route['bookings/vouchers/(:num)/page/(:num)'] = "bookings/vouchers/index/$1";
$route['bookings/vouchers/(:num)/view/all'] = "bookings/vouchers/index/$1";
$route['bookings/vouchers/(:num)/recall'] = "bookings/vouchers/index/$1";

$route['bookings/costs/(:num)'] = "bookings/costs/index/$1";
$route['bookings/costs/(:num)/new'] = "bookings/costs/edit//$1";
$route['bookings/costs/(:num)/page/(:num)'] = "bookings/costs/index/$1";
$route['bookings/costs/(:num)/view/all'] = "bookings/costs/index/$1";
$route['bookings/costs/(:num)/recall'] = "bookings/costs/index/$1";

$route['bookings/confirmation/(:num)'] = "bookings/confirmation/index/$1";

$route['sessions/bulk/(:num)/force'] = "bookings/sessions/bulk/$1/force";
$route['sessions/bulk/(:num)'] = "bookings/sessions/bulk/$1";
$route['sessions/get_staff_on_session/(:num)/(:any)'] = "bookings/sessions/get_staff_on_session/$1/$2";

$route['sessions/staff/(:num)'] = "sessions/staff/index/$1";
$route['sessions/staff/(:num)'] = "sessions/staff/index/$1";
$route['sessions/staff/(:num)/new'] = "sessions/staff/edit//$1";
$route['sessions/staff/(:num)/page/(:num)'] = "sessions/staff/index/$1";
$route['sessions/staff/(:num)/view/all'] = "sessions/staff/index/$1";
$route['sessions/staff/(:num)/recall'] = "sessions/staff/index/$1";

$route['sessions/exceptions/(:num)'] = "sessions/exceptions/index/$1";
$route['sessions/exceptions/(:num)'] = "sessions/exceptions/index/$1";
$route['sessions/exceptions/(:num)/new'] = "sessions/exceptions/edit//$1";
$route['sessions/exceptions/(:num)/page/(:num)'] = "sessions/exceptions/index/$1";
$route['sessions/exceptions/(:num)/view/all'] = "sessions/exceptions/index/$1";
$route['sessions/exceptions/(:num)/recall'] = "sessions/exceptions/index/$1";

$route['sessions/notes/(:num)'] = "sessions/notes/index/$1";
$route['sessions/notes/(:num)'] = "sessions/notes/index/$1";
$route['sessions/notes/(:num)/new'] = "sessions/notes/edit//$1";
$route['sessions/notes/(:num)/page/(:num)'] = "sessions/notes/index/$1";
$route['sessions/notes/(:num)/view/all'] = "sessions/notes/index/$1";
$route['sessions/notes/(:num)/recall'] = "sessions/notes/index/$1";

$route['sessions/attachments/(:num)'] = "sessions/attachments/index/$1";
$route['sessions/attachments/(:num)'] = "sessions/attachments/index/$1";
$route['sessions/attachments/(:num)/new'] = "sessions/attachments/edit//$1";
$route['sessions/attachments/(:num)/page/(:num)'] = "sessions/attachments/index/$1";
$route['sessions/attachments/(:num)/view/all'] = "sessions/attachments/index/$1";
$route['sessions/attachments/(:num)/recall'] = "sessions/attachments/index/$1";

// customers

$route['customers'] = "customers/main/index";
$route['customers/edit/(:num)'] = "customers/main/edit/$1";
$route['customers/remove/(:num)'] = "customers/main/remove/$1";
$route['customers/bulk'] = "customers/main/bulk";
$route['customers/new/school'] = "customers/main/edit//school";
$route['customers/new/organisation'] = "customers/main/edit//organisation";
$route['customers/new/prospect/school'] = "customers/main/edit//school/true";
$route['customers/new/prospect/organisation'] = "customers/main/edit//organisation/true";

$route['customers/schools'] = "customers/main/index/school";
$route['customers/schools/page/(:num)'] = "customers/main/index/school";
$route['customers/schools/view/all'] = "customers/main/index/school";
$route['customers/schools/recall'] = "customers/main/index/school";

$route['customers/organisations'] = "customers/main/index/organisation";
$route['customers/organisations/page/(:num)'] = "customers/main/index/organisation";
$route['customers/organisations/view/all'] = "customers/main/index/organisation";
$route['customers/organisations/recall'] = "customers/main/index/organisation";

$route['customers/prospects/schools'] = "customers/main/index/school/true";
$route['customers/prospects/schools/page/(:num)'] = "customers/main/index/school/true";
$route['customers/prospects/schools/view/all'] = "customers/main/index/school/true";
$route['customers/prospects/schools/recall'] = "customers/main/index/school/true";

$route['customers/prospects/organisations'] = "customers/main/index/organisation/true";
$route['customers/prospects/organisations/page/(:num)'] = "customers/main/index/organisation/true";
$route['customers/prospects/organisations/view/all'] = "customers/main/index/organisation/true";
$route['customers/prospects/organisations/recall'] = "customers/main/index/organisation/true";

$route['customers/addresses/(:num)'] = "customers/addresses/index/$1";
$route['customers/addresses/(:num)/new'] = "customers/addresses/edit//$1";
$route['customers/addresses/(:num)/page/(:num)'] = "customers/addresses/index/$1";
$route['customers/addresses/(:num)/view/all'] = "customers/addresses/index/$1";
$route['customers/addresses/(:num)/recall'] = "customers/addresses/index/$1";

$route['customers/contacts/(:num)'] = "customers/contacts/index/$1";
$route['customers/contacts/(:num)/new'] = "customers/contacts/edit//$1";
$route['customers/contacts/(:num)/page/(:num)'] = "customers/contacts/index/$1";
$route['customers/contacts/(:num)/view/all'] = "customers/contacts/index/$1";
$route['customers/contacts/(:num)/recall'] = "customers/contacts/index/$1";
$route['customers/contacts/active/(:num)/(:value)'] = "customers/contacts/active/$1/$2";

$route['customers/safety/(:num)'] = "customers/safety/index/$1";
$route['customers/safety/school/(:num)/new'] = "customers/safety/school//$1";
$route['customers/safety/camp/(:num)/new'] = "customers/safety/camp//$1";
$route['customers/safety/risk/(:num)/new'] = "customers/safety/risk//$1";
$route['customers/safety/(:num)/page/(:num)'] = "customers/safety/index/$1";
$route['customers/safety/(:num)/view/all'] = "customers/safety/index/$1";
$route['customers/safety/(:num)/recall'] = "customers/safety/index/$1";
$route['customers/safety/hazard/remove/(:num)'] = "customers/safety/remove_hazard/$1";
$route['customers/safety/hazard/(:num)'] = "customers/safety/hazard/$1";
$route['customers/safety/hazard/(:num)/new'] = "customers/safety/hazard//$1";
$route['customers/safety/duplicate/(:num)'] = "customers/safety/duplicate/$1";

$route['customers/notes/(:num)'] = "customers/notes/index/$1";
$route['customers/notes/(:num)/new'] = "customers/notes/edit//$1";
$route['customers/notes/(:num)/page/(:num)'] = "customers/notes/index/$1";
$route['customers/notes/(:num)/view/all'] = "customers/notes/index/$1";
$route['customers/notes/(:num)/recall'] = "customers/notes/index/$1";

$route['customers/attachments/(:num)'] = "customers/attachments/index/$1";
$route['customers/attachments/(:num)/new'] = "customers/attachments/edit//$1";
$route['customers/attachments/(:num)/page/(:num)'] = "customers/attachments/index/$1";
$route['customers/attachments/(:num)/view/all'] = "customers/attachments/index/$1";
$route['customers/attachments/(:num)/recall'] = "customers/attachments/index/$1";

$route['customers/notifications/(:num)'] = "customers/notifications/index/$1";
$route['customers/notifications/(:num)/page/(:num)'] = "customers/notifications/index/$1";
$route['customers/notifications/(:num)/view/all'] = "customers/notifications/index/$1";
$route['customers/notifications/(:num)/recall'] = "customers/notifications/index/$1";

// families
$route['participants'] = "participants/main/index";
$route['participants/page/(:num)'] = "participants/main/index";
$route['participants/view/all'] = "participants/main/index";
$route['participants/recall'] = "participants/main/index";
$route['participants/new'] = "participants/main/new_family";
$route['participants/new-account'] = "participants/main/new_account";
$route['participants/form-validate-ah'] = "participants/main/account_holder_validator";
$route['participants/form-submit-ah'] = "participants/main/account_holder_submit";
$route['participants/form-validate-p'] = "participants/main/participant_validator";
$route['participants/form-submit-p'] = "participants/main/participant_submit";
$route['participants/view/(:num)'] = "participants/main/view/$1";
$route['participants/active/(:num)/(:any)'] = "participants/main/active/$1/$2";
$route['participants/maincontact/(:num)'] = "participants/main/maincontact/$1";
$route['participants/photoconsent/(:num)/(:any)'] = "participants/main/photoconsent/$1/$2";
$route['participants/contactcheck'] = "participants/main/contactcheck";
$route['participants/childcheck'] = "participants/main/childcheck";

$route['participants/tools/recall'] = "participants/tools/index";

$route['participants/contacts/(:num)/new'] = "participants/contacts/edit//$1";

$route['participants/participant/(:num)/new'] = "participants/children/edit//$1";
$route['participants/participant/edit/(:num)'] = "participants/children/edit/$1";
$route['participants/participant/remove/(:num)'] = "participants/children/remove/$1";
$route['participants/participant/check_dob/(:num)'] = "participants/children/check_dob/$1";

$route['participants/notes/(:num)'] = "participants/notes/index/$1";
$route['participants/notes/(:num)/new'] = "participants/notes/edit//$1";
$route['participants/notes/(:num)/page/(:num)'] = "participants/notes/index/$1";
$route['participants/notes/(:num)/view/all'] = "participants/notes/index/$1";
$route['participants/notes/(:num)/recall'] = "participants/notes/index/$1";

$route['participants/privacy/(:num)'] = "participants/privacy/index/$1";

$route['participants/notifications/(:num)'] = "participants/notifications/index/$1";
$route['participants/notifications/(:num)/page/(:num)'] = "participants/notifications/index/$1";
$route['participants/notifications/(:num)/view/all'] = "participants/notifications/index/$1";
$route['participants/notifications/(:num)/recall'] = "participants/notifications/index/$1";

$route['participants/bookings/(:num)'] = "participants/bookings/index/$1";
$route['participants/bookings/(:num)/page/(:num)'] = "participants/bookings/index/$1";
$route['participants/bookings/(:num)/view/all'] = "participants/bookings/index/$1";
$route['participants/bookings/(:num)/recall'] = "participants/bookings/index/$1";

$route['participants/bookings/view/(:num)'] = "participants/bookings/booking/$1";
$route['participants/bookings/view/(:num)/ajax'] = "participants/bookings/booking/$1/ajax";

$route['participants/payments/(:num)'] = "participants/payments/index/$1";
$route['participants/payments/(:num)/new/(:num)'] = "participants/payments/edit//$1/$2";
$route['participants/payments/(:num)/new'] = "participants/payments/edit//$1";
$route['participants/payments/(:num)/page/(:num)'] = "participants/payments/index/$1";
$route['participants/payments/(:num)/view/all'] = "participants/payments/index/$1";
$route['participants/payments/(:num)/recall'] = "participants/payments/index/$1";

$route['participants/subscriptions/all'] = "participants/subscriptions/view_all";
$route['participants/subscriptions/inactive_participant_subscription/(:num)'] = "participants/subscriptions/inactive_participant_subscription/$1";
$route['participants/subscriptions/activate_participant_subscription/(:num)'] = "participants/subscriptions/activate_participant_subscription/$1";
$route['participants/subscriptions/(:num)'] = "participants/subscriptions/index/$1";
$route['participants/subscriptions/activate/(:num)'] = "participants/subscriptions/activate/$1";
$route['participants/subscriptions/remove/(:num)'] = "participants/subscriptions/remove/$1";
$route['participants/subscriptions/cancel/(:num)'] = "participants/subscriptions/cancel/$1";
$route['participants/subscriptions/(:num)/page/(:num)'] = "participants/subscriptions/index/$1";
$route['participants/subscriptions/(:num)/view/all'] = "participants/subscriptions/index/$1";
$route['participants/subscriptions/(:num)/recall'] = "participants/subscriptions/index/$1";

$route['participants/payment-plans/(:num)'] = "participants/payment_plans/index/$1";
$route['participants/payment-plans/edit/(:num)'] = "participants/payment_plans/edit/$1";
$route['participants/payment-plans/(:num)/new/(:num)'] = "participants/payment_plans/edit//$1/$2";
$route['participants/payment-plans/(:num)/new'] = "participants/payment_plans/edit//$1";
$route['participants/payment-plans/activate/(:num)'] = "participants/payment_plans/activate/$1";
$route['participants/payment-plans/remove/(:num)'] = "participants/payment_plans/remove/$1";
$route['participants/payment-plans/cancel/(:num)'] = "participants/payment_plans/cancel/$1";
$route['participants/payment-plans/(:num)/page/(:num)'] = "participants/payment_plans/index/$1";
$route['participants/payment-plans/(:num)/view/all'] = "participants/payment_plans/index/$1";
$route['participants/payment-plans/(:num)/recall'] = "participants/payment_plans/index/$1";
$route['participants/payment-plans/view/(:num)'] = "participants/payment_plans/edit/$1";

// staff
$route['staff'] = "staff/main/index";
$route['staff/page/(:num)'] = "staff/main/index";
$route['staff/view/all'] = "staff/main/index";
$route['staff/recall'] = "staff/main/index";
$route['staff/new'] = "staff/main/edit";
$route['staff/edit/(:num)'] = "staff/main/edit/$1";
$route['staff/remove/(:num)'] = "staff/main/remove/$1";
$route['staff/active/(:num)/(:any)'] = "staff/main/active/$1/$2";
$route['staff/access/(:num)'] = "staff/main/access/$1";

$route['staff/addresses/(:num)'] = "staff/addresses/index/$1";
$route['staff/addresses/(:num)/new'] = "staff/addresses/edit//$1";
$route['staff/addresses/(:num)/page/(:num)'] = "staff/addresses/index/$1";
$route['staff/addresses/(:num)/view/all'] = "staff/addresses/index/$1";
$route['staff/addresses/(:num)/recall'] = "staff/addresses/index/$1";

$route['staff/availability/(:num)'] = "staff/availability/index/$1";
$route['staff/availability/(:num)/exceptions'] = "staff/availability/exceptions/$1";
$route['staff/availability/(:num)/exceptions/edit/(:num)'] = "staff/availability/edit_exception/$1/$2";
$route['staff/availability/(:num)/exceptions/new'] = "staff/availability/edit_exception/$1";
$route['staff/availability/(:num)/exceptions/remove/(:num)'] = "staff/availability/remove_exception/$1/$2";
$route['staff/availability/(:num)/exceptions/page/(:num)'] = "staff/availability/exceptions/$1";
$route['staff/availability/(:num)/exceptions/view/all'] = "staff/availability/exceptions/$1";
$route['staff/availability/(:num)/exceptions/recall'] = "staff/availability/exceptions/$1";
$route['staff/staff_replacement/(:num)/(:num)'] = "staff/availability/staff_replacement/$1/$2";

$route['staff/quals/(:num)'] = "staff/quals/index/$1";
$route['staff/quals/abl-deliver/(:num)'] = "staff/quals/deliver/$1";
$route['staff/quals/(:num)/new'] = "staff/quals/edit//$1";
$route['staff/quals/additonal/(:num)/page/(:num)'] = "staff/quals/additonal/$1";
$route['staff/quals/additonal/(:num)/view/all'] = "staff/quals/additonal/$1";
$route['staff/quals/additonal/(:num)/recall'] = "staff/quals/additonal/$1";

$route['staff/recruitment/(:num)'] = "staff/recruitment/index/$1";

$route['staff/id/(:num)'] = "staff/id/index/$1";
$route['staff/id/(:num)/print'] = "staff/id/print_id/$1";

$route['staff/safety/(:num)'] = "staff/safety/index/$1";

$route['staff/notes/(:num)'] = "staff/notes/index/$1";
$route['staff/notes/(:num)/new'] = "staff/notes/edit//$1";
$route['staff/notes/(:num)/page/(:num)'] = "staff/notes/index/$1";
$route['staff/notes/(:num)/view/all'] = "staff/notes/index/$1";
$route['staff/notes/(:num)/recall'] = "staff/notes/index/$1";

$route['staff/privacy/(:num)'] = "staff/privacy/index/$1";

$route['staff/attachments/(:num)'] = "staff/attachments/index/$1";
$route['staff/attachments/(:num)/new'] = "staff/attachments/edit//$1";
$route['staff/attachments/(:num)/page/(:num)'] = "staff/attachments/index/$1";
$route['staff/attachments/(:num)/view/all'] = "staff/attachments/index/$1";
$route['staff/attachments/(:num)/recall'] = "staff/attachments/index/$1";

$route['staff/timetable/(:num)'] = "staff/main/timetable/$1";
$route['staff/timetable/(:num)/(:num)/(:num)'] = "staff/main/timetable/$1/$2/$3";
$route['staff/equipment/(:num)'] = "staff/main/equipment/$1";

$route['staff/checkins/(:num)'] = "staff/checkins/index/$1";

// equipment
$route['equipment'] = "equipment/main/index";
$route['equipment/page/(:num)'] = "equipment/main/index";
$route['equipment/view/all'] = "equipment/main/index";
$route['equipment/recall'] = "equipment/main/index";
$route['equipment/new'] = "equipment/main/edit";
$route['equipment/edit/(:num)'] = "equipment/main/edit/$1";
$route['equipment/remove/(:num)'] = "equipment/main/remove/$1";

$route['equipment/bookings/page/(:num)'] = "equipment/bookings/index";
$route['equipment/bookings/view/all'] = "equipment/bookings/index";
$route['equipment/bookings/recall'] = "equipment/bookings/index";
$route['equipment/bookings/new'] = "equipment/bookings/edit";

// resources
$route['resources/edit/(:num)'] = "resources/edit//$1";
$route['resources/(:num)/edit/(:num)'] = "resources/edit/$1/$2";
$route['resources/(:num)/remove/(:num)'] = "resources/remove/$1/$2";
$route['resources/new/(:any)'] = "resources/edit//$1";
$route['resources/(:num)/new'] = "resources/edit/$1";
$route['resources/sendwithbookings/(:any)/(:num)/(:any)'] = "resources/sendwithbookings/$1/$2/$3";
$route['resources/(:any)/page/(:num)'] = "resources/index/$1";
$route['resources/(:any)/view/all'] = "resources/index/$1";
$route['resources/(:any)/recall'] = "resources/index/$1";
$route['resources/(:any)'] = "resources/index/$1";

// attachments
$route['attachment/edit/(:any)/(:any)/(:num)'] = "attachments/index/$1/$2/edit/$3";
$route['attachment/save/(:num)/(:any)'] = "attachments/save/$1/$2";
$route['attachment/(:any)/(:any)/thumb/(:num)'] = "attachments/index/$1/$2/thumb/$3";
$route['attachment/(:any)/(:any)/thumb'] = "attachments/index/$1/$2/thumb";
$route['attachment/(:any)/(:any)/(:any)/(:any)'] = "attachments/index/$1/$2/view/$3/$4";
$route['attachment/(:any)/(:any)/(:any)'] = "attachments/index/$1/$2/view/$3";
$route['attachment/(:any)/(:any)'] = "attachments/index/$1/$2";

// messages
$route['messages/sent/(:any)/new'] = "messages/new_message/sent/$1";
$route['messages/sent/(:any)/new/(:num)'] = "messages/new_message/sent/$1//$2";
$route['messages/sent/(:any)/(:num)'] = "messages/index/sent/$1/$2";
$route['messages/sent/(:any)'] = "messages/index/sent/$1";
$route['messages/sent/(:any)/page/(:num)'] = "messages/index/sent/$1";
$route['messages/sent/(:any)/view/all'] = "messages/index/sent/$1";
$route['messages/sent/recall'] = "messages/index/sent";
$route['messages/inbox/(:any)/new'] = "messages/new_message/inbox/$1";
$route['messages/inbox/(:any)'] = "messages/index/inbox/$1";
$route['messages/archive/(:any)'] = "messages/index/archive/$1";
$route['messages/inbox/(:any)/page/(:num)'] = "messages/index/inbox/$1";
$route['messages/inbox/(:any)/view/all'] = "messages/index/inbox/$1";
$route['messages/reply/staff/(:num)'] = "messages/new_message/sent/staff/$1";
$route['messages/reply/archive/(:num)'] = "messages/new_message/sent/staff/$1";
$route['messages/page/(:num)'] = "messages/index";
$route['messages/view/(:any)/(:num)'] = "messages/view/$1/$2";
$route['messages/view/all'] = "messages/index";
$route['messages/(:any)/remove/(:num)'] = "messages/remove/$1/$2";
$route['messages/recall'] = "messages/index";
$route['messages/templates'] = "messages/templates";
$route['messages/templates/remove/(:num)'] = "messages/template_remove/$1";
$route['messages/template'] = "messages/template";
$route['messages/template/(:num)'] = "messages/get_template/$1";
$route['messages/template/view/(:num)'] = "messages/template/$1";
$route['messages/template/remove_attachment/(:num)'] = "messages/remove_template_attachment/$1";
$route['messages/forward/(:num)'] = "messages/forward_to_support/$1";

// settings
$route['settings/groups'] = "settings/groups/index";
$route['settings/groups/new'] = "settings/groups/edit";
$route['settings/groups/edit/(:num)'] = "settings/groups/edit/$1";
$route['settings/groups/remove/(:num)'] = "settings/groups/remove/$1";

$route['settings/vouchers'] = "settings/vouchers/index";
$route['settings/vouchers/page/(:num)'] = "settings/vouchers/index";
$route['settings/vouchers/view/all'] = "settings/vouchers/index";
$route['settings/vouchers/recall'] = "settings/vouchers/index";
$route['settings/vouchers/new'] = "settings/vouchers/edit";
$route['settings/vouchers/edit/(:num)'] = "settings/vouchers/edit/$1";
$route['settings/vouchers/activate/(:num)'] = "settings/vouchers/activate/$1";
$route['settings/vouchers/deactivate/(:num)'] = "settings/vouchers/deactivate/$1";
$route['settings/vouchers/remove/(:num)'] = "settings/vouchers/remove/$1";

$route['settings/childcarevoucherproviders'] = "settings/childcarevoucherproviders/index";
$route['settings/childcarevoucherproviders/page/(:num)'] = "settings/childcarevoucherproviders/index";
$route['settings/childcarevoucherproviders/view/all'] = "settings/childcarevoucherproviders/index";
$route['settings/childcarevoucherproviders/recall'] = "settings/childcarevoucherproviders/index";
$route['settings/childcarevoucherproviders/new'] = "settings/childcarevoucherproviders/edit";
$route['settings/childcarevoucherproviders/edit/(:num)'] = "settings/childcarevoucherproviders/edit/$1";
$route['settings/childcarevoucherproviders/activate/(:num)'] = "settings/childcarevoucherproviders/activate/$1";
$route['settings/childcarevoucherproviders/deactivate/(:num)'] = "settings/childcarevoucherproviders/deactivate/$1";
$route['settings/childcarevoucherproviders/remove/(:num)'] = "settings/childcarevoucherproviders/remove/$1";

$route['settings/departments'] = "settings/departments/index";
$route['settings/departments/page/(:num)'] = "settings/departments/index";
$route['settings/departments/view/all'] = "settings/departments/index";
$route['settings/departments/recall'] = "settings/departments/index";
$route['settings/departments/new'] = "settings/departments/edit";
$route['settings/departments/edit/(:num)'] = "settings/departments/edit/$1";
$route['settings/departments/remove/(:num)'] = "settings/departments/remove/$1";

$route['settings/activities'] = "settings/activities/index";
$route['settings/activities/page/(:num)'] = "settings/activities/index";
$route['settings/activities/view/all'] = "settings/activities/index";
$route['settings/activities/recall'] = "settings/activities/index";
$route['settings/activities/new'] = "settings/activities/edit";
$route['settings/activities/edit/(:num)'] = "settings/activities/edit/$1";
$route['settings/activities/remove/(:num)'] = "settings/activities/remove/$1";

$route['settings/resources'] = "settings/resources/index";
$route['settings/resources/page/(:num)'] = "settings/resources/index";
$route['settings/resources/view/all'] = "settings/resources/index";
$route['settings/resources/recall'] = "settings/resources/index";
$route['settings/resources/new'] = "settings/resources/edit";
$route['settings/resources/edit/(:num)'] = "settings/resources/edit/$1";
$route['settings/resources/remove/(:num)'] = "settings/resources/remove/$1";

$route['settings/availabilitycals'] = "settings/availabilitycals/index";
$route['settings/availabilitycals/page/(:num)'] = "settings/availabilitycals/index";
$route['settings/availabilitycals/view/all'] = "settings/availabilitycals/index";
$route['settings/availabilitycals/recall'] = "settings/availabilitycals/index";
$route['settings/availabilitycals/new'] = "settings/availabilitycals/edit";
$route['settings/availabilitycals/edit/(:num)'] = "settings/availabilitycals/edit/$1";
$route['settings/availabilitycals/remove/(:num)'] = "settings/availabilitycals/remove/$1";

$route['settings/mandatoryquals'] = "settings/mandatoryquals/index";
$route['settings/mandatoryquals/page/(:num)'] = "settings/mandatoryquals/index";
$route['settings/mandatoryquals/view/all'] = "settings/mandatoryquals/index";
$route['settings/mandatoryquals/recall'] = "settings/mandatoryquals/index";
$route['settings/mandatoryquals/new'] = "settings/mandatoryquals/edit";
$route['settings/mandatoryquals/edit/(:num)'] = "settings/mandatoryquals/edit/$1";
$route['settings/mandatoryquals/remove/(:num)'] = "settings/mandatoryquals/remove/$1";

$route['settings/tags'] = "settings/tags/index";
$route['settings/tags/page/(:num)'] = "settings/tags/index";
$route['settings/tags/view/all'] = "settings/tags/index";
$route['settings/tags/recall'] = "settings/tags/index";
$route['settings/tags/new'] = "settings/tags/edit";
$route['settings/tags/edit/(:num)'] = "settings/tags/edit/$1";
$route['settings/tags/remove/(:num)'] = "settings/tags/remove/$1";

$route['settings/sessiontypes'] = "settings/sessiontypes/index";
$route['settings/sessiontypes/page/(:num)'] = "settings/sessiontypes/index";
$route['settings/sessiontypes/view/all'] = "settings/sessiontypes/index";
$route['settings/sessiontypes/recall'] = "settings/sessiontypes/index";
$route['settings/sessiontypes/new'] = "settings/sessiontypes/edit";
$route['settings/sessiontypes/edit/(:num)'] = "settings/sessiontypes/edit/$1";
$route['settings/sessiontypes/remove/(:num)'] = "settings/sessiontypes/remove/$1";

$route['settings/projecttypes'] = "settings/projecttypes/index";
$route['settings/projecttypes/page/(:num)'] = "settings/projecttypes/index";
$route['settings/projecttypes/view/all'] = "settings/projecttypes/index";
$route['settings/projecttypes/recall'] = "settings/projecttypes/index";
$route['settings/projecttypes/new'] = "settings/projecttypes/edit";
$route['settings/projecttypes/edit/(:num)'] = "settings/projecttypes/edit/$1";
$route['settings/projecttypes/remove/(:num)'] = "settings/projecttypes/remove/$1";

$route['settings/permissionlevels'] = "settings/permissionlevels/index";
$route['settings/permissionlevels/edit/(:any)'] = "settings/permissionlevels/edit/$1";

$route['settings/staffingtypes'] = "settings/staffingtypes/index";
$route['settings/staffingtypes/edit/(:any)'] = "settings/staffingtypes/edit/$1";

$route['settings/export'] = "settings/main/export";

$route['settings/regions'] = "settings/main/regions";
$route['settings/regions/page/(:num)'] = "settings/main/regions";
$route['settings/regions/view/all'] = "settings/main/regions";
$route['settings/regions/recall'] = "settings/main/regions";
$route['settings/regions/new'] = "settings/main/edit_region";
$route['settings/regions/edit/(:num)'] = "settings/main/edit_region/$1";
$route['settings/regions/remove/(:num)'] = "settings/main/remove_region/$1";

$route['settings/listing'] = "settings/main/listing";
$route['settings/listing/(:any)'] = "settings/main/listing/$1";
$route['settings/listing/(:any)/(:any)'] = "settings/main/listing/$1/$2";
$route['settings/listing/(:any)/(:any)/(:any)'] = "settings/main/listing_new/$1/$2/$3";
$route['settings/listing_remove/(:any)/(:any)/(:any)'] = "settings/main/listing_remove/$1/$2/$3";
$route['settings/subsection/(:any)/create'] = "settings/main/subsection/$1/create";
$route['settings/subsection/(:any)/edit/(:any)'] = "settings/main/subsection/$1/edit/$2";
$route['settings/subsection/(:any)/active'] = "settings/main/subsection/$1/active";

$route['settings/areas'] = "settings/main/areas";
$route['settings/areas/page/(:num)'] = "settings/main/areas";
$route['settings/areas/view/all'] = "settings/main/areas";
$route['settings/areas/recall'] = "settings/main/areas";
$route['settings/areas/new'] = "settings/main/edit_area";
$route['settings/areas/edit/(:num)'] = "settings/main/edit_area/$1";
$route['settings/areas/remove/(:num)'] = "settings/main/remove_area/$1";

$route['settings/projectcodes'] = "settings/projectcodes/index";
$route['settings/projectcodes/page/(:num)'] = "settings/projectcodes/index";
$route['settings/projectcodes/view/all'] = "settings/projectcodes/index";
$route['settings/projectcodes/recall'] = "settings/projectcodes/index";
$route['settings/projectcodes/new'] = "settings/projectcodes/edit";
$route['settings/projectcodes/edit/(:num)'] = "settings/projectcodes/edit/$1";
$route['settings/projectcodes/remove/(:num)'] = "settings/projectcodes/remove/$1";
$route['settings/projectcodes/updateAjax'] = "settings/projectcodes/updateAjax/";
$route['settings/projectcodes/active/(:num)/(:num)'] = "settings/projectcodes/active/$1/$2";

$route['settings/dashboardtriggers'] = "settings/main/dashboard_triggers";

$route['settings/fields'] = "settings/fields/index";
$route['settings/fields/display/(:any)'] = "settings/fields/display/$1";
$route['settings/fields/(:any)'] = "settings/fields/index/$1";

$route['settings/(:any)'] = "settings/main/index/$1";

$route['settings/customers/type/new'] = "settings/organisationtypes/edit/";
$route['settings/customers/type/edit/(:num)'] = "settings/organisationtypes/edit/$1";
$route['settings/customers/type/active/(:num)/(:num)'] = "settings/organisationtypes/active/$1/$2";
$route['settings/customers/type/remove/(:num)'] = "settings/organisationtypes/remove/$1";

// reports
$route['reports/timesheets/(:any)'] = 'reports/timesheets/index/$1';
$route['reports/payroll/(:any)'] = 'reports/payroll/index/$1';
$route['reports/payroll/page/(:num)'] = 'reports/payroll/index';
$route['reports/payroll/(:any)/page/(:num)'] = 'reports/payroll/index/$1';
$route['reports/payroll/view/all'] = 'reports/payroll/index';
$route['reports/payroll-history'] = 'reports/payroll/history';
$route['reports/contracts/(:any)'] = 'reports/contracts/index/$1';
$route['reports/project_code/(:any)'] = 'reports/project_code/index/$1';
$route['reports/utilisation/(:any)'] = 'reports/utilisation/index/$1';
$route['reports/bikeability/(:any)'] = 'reports/bikeability/index/$1';
$route['reports/performance/(:any)'] = 'reports/performance/index/$1';
$route['reports/projects/(:any)'] = 'reports/projects/index/$1';
$route['reports/activities/(:any)'] = 'reports/activities/index/$1';
$route['reports/session_delivery/(:any)'] = 'reports/session_delivery/index/$1';
$route['reports/marketing/(:any)'] = 'reports/marketing/index/$1';
$route['reports/marketing/page/(:num)'] = 'reports/marketing/index';
$route['reports/marketing/view/all'] = 'reports/marketing/index';
$route['reports/marketing/(:any)/page/(:num)'] = 'reports/marketing/index/$1';
$route['reports/mileage/(:any)'] = 'reports/mileage/index/$1';
$route['reports/payments/bookings'] = 'reports/payments/bookings';
$route['reports/payments/(:any)'] = 'reports/payments/index/$1';

// accounts
$route['accounts'] = "accounts/main/index";
$route['accounts/page/(:num)'] = "accounts/main/index";
$route['accounts/view/all'] = "accounts/main/index";
$route['accounts/recall'] = "accounts/main/index";
$route['accounts/new'] = "accounts/main/edit";
$route['accounts/edit/(:num)'] = "accounts/main/edit/$1";
$route['accounts/remove/(:num)'] = "accounts/main/remove/$1";
$route['accounts/access/(:num)'] = "accounts/main/access/$1";
$route['accounts/active/(:num)/(:any)'] = "accounts/main/active/$1/$2";
$route['accounts/demodata/(:num)'] = "accounts/main/demodata/$1";
$route['accounts/cleardata/(:num)'] = "accounts/main/cleardata/$1";
$route['accounts/import/(:num)'] = "accounts/import/index/$1";
$route['accounts/anonymise/(:num)'] = "accounts/anonymise/index/$1";

$route['accounts/plans/page/(:num)'] = "accounts/plans/index/$1";
$route['accounts/plans/view/all'] = "accounts/plans/index";
$route['accounts/plans/recall'] = "accounts/plans/index";
$route['accounts/plans/new'] = "accounts/plans/edit";
$route['accounts/plans/feature/(:num)/(:any)/(:any)'] = "accounts/plans/feature/$1/$2/$3";

$route['accounts/users/page/(:num)'] = "accounts/users/index/$1";
$route['accounts/users/view/all'] = "accounts/users/index";
$route['accounts/users/recall'] = "accounts/users/index";

$route['accounts/defaults/listing/(:any)'] = "settings/main/listing/$1/defaults/defaults";
$route['accounts/defaults/listing/(:any)/(:any)'] = "settings/main/listing/$1/$2/defaults";
$route['accounts/defaults/(:any)'] = "settings/main/index/defaults/$1";
$route['accounts/dashboardtriggers'] = "settings/main/dashboard_triggers/defaults";

// timesheets
$route['finance/approvals/own'] = "timesheets/approvals/false"; //timesheets/approvals/own
$route['finance/approvals'] = "timesheets/approvals"; //timesheets/approvals

$route['finance/invoices/own'] = "timesheets/invoices/false"; //timesheets/invoices/own
$route['finance/invoices/own/page/(:num)'] = "timesheets/invoices/false/$1"; //timesheets/invoices/own
$route['finance/invoices'] = "timesheets/invoices"; //timesheets/invoices
$route['finance/invoices/page/(:num)'] = "timesheets/invoices/$1"; //timesheets/invoices

$route['finance/timesheets'] = "timesheets/index";
$route['finance/timesheets/page/(:num)'] = "timesheets/index/$1"; //timesheets/page/(:num)

$route['finance/timesheets/own'] = "timesheets/own";
$route['finance/timesheets/own/page/(:num)'] = "timesheets/own/$1";

// offer/accept
$route['acceptance'] = "acceptance/index";
$route['acceptance/all'] = "acceptance/index/true";
$route['acceptance/page/(:num)'] = "acceptance/index";
$route['acceptance/view/all'] = "acceptance/index";
$route['acceptance/recall'] = "acceptance/index";
$route['acceptance/all/page/(:num)'] = "acceptance/index/true";
$route['acceptance/all/view/all'] = "acceptance/index/true";
$route['acceptance/all/recall'] = "acceptance/index/true";

$route['acceptance_manual'] = "acceptance/index/0/1";
$route['acceptance_manual/all'] = "acceptance/index/true/1";
$route['acceptance_manual/page/(:num)'] = "acceptance/index/0/1";
$route['acceptance_manual/view/all'] = "acceptance/index/0/1";
$route['acceptance_manual/recall'] = "acceptance/index/0/1";
$route['acceptance_manual/all/page/(:num)'] = "acceptance/index/true/1";
$route['acceptance_manual/all/view/all'] = "acceptance/index/true/1";
$route['acceptance_manual/all/recall'] = "acceptance/index/true/1";

// session evaluations
$route['evaluations'] = "evaluations/index";
$route['evaluations/all'] = "evaluations/index/true";
$route['evaluations/page/(:num)'] = "evaluations/index";
$route['evaluations/view/all'] = "evaluations/index";
$route['evaluations/recall'] = "evaluations/index";
$route['evaluations/all/page/(:num)'] = "evaluations/index/true";
$route['evaluations/all/view/all'] = "evaluations/index/true";
$route['evaluations/all/recall'] = "evaluations/index/true";
$route['evaluations/approvals/page/(:num)'] = "evaluations/approvals";
$route['evaluations/approvals/view/all'] = "evaluations/approvals";
$route['evaluations/approvals/recall'] = "evaluations/approvals";

// gocardless
$route['gc/(:any)'] = "gocardless/mandate/$1";
$route['gc/confirm/(:num)/(:num)'] = "gocardless/confirm/$1/$2";
$route['webhooks/gocardless/handler/(:num)'] = "webhooks/gocardless/$1";

//Strip Webhook
$route['webhooks/stripe/(:num)'] = "webhooks/stripe/$1";


$route['user-activity'] = "useractivity/index";
$route['user-activity/get-records'] = "useractivity/getRecords";

// Export Section Dataconflicts
$route['dataconflicts/recall'] = "dataconflicts/index";
$route['export/dataprotection'] = "export/dataprotection";

// misc
$route['404_override'] = '';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
