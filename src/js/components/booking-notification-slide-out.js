"use strict";
var NotificationSlideOut = function() {
	// Private properties
	var _element;
	var _offcanvasObject;

	// Private functions
	var _init = function() {
		var header = KTUtil.find(_element, '.offcanvas-header');
		var content = KTUtil.find(_element, '.offcanvas-content');

		_offcanvasObject = new KTOffcanvas(_element, {
			overlay: true,
			baseClass: 'offcanvas',
			placement: 'right',
			closeBy: 'notification-slide-out-close',
			toggleBy: 'notification-slide-out-toggle'
		});
	}

	// Public methods
	return {
		init: function(className) {
			_element = KTUtil.getById(className);

			if (!_element) {
				return;
			}

			// Initialize
			_init();
		},

		getElement: function() {
			return _element;
		}
	};
}();

// Webpack support
if (typeof module !== 'undefined') {
	module.exports = NotificationSlideOut;
}

// Init notification slide out window
NotificationSlideOut.init('view-notification-slide-out');

//Load Bookings
function load_notification_slide_out(attrValue, origin){
	var offCanvasContent = $("#view-notification-slide-out").find(".offcanvas-content");
	offCanvasContent.html('<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div>');
	var url = '/bookings/notification/view_org/'+attrValue;
	if(origin == "family"){
		url = '/bookings/notification/view_family/'+attrValue;
	}
	$(".offcanvas-overlay").on("click", function () {
		$("#view-notification-slide-out.offcanvas-right").css("right", '-'+(total_width - leftside_width - 10)+'px');
	});
	fetch(url, {
		method: 'GET'
	}).then(function (confirmResult) {
		return confirmResult.text();
	}).then(function (html) {
		offCanvasContent.html(html);
	});
}
