"use strict";
var BookingsViewOverlay = function() {
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
			closeBy: 'view-bookings-toggle-close',
			toggleBy: 'view-bookings-toggle'
		});

		KTUtil.scrollInit(content, {
			disableForMobile: true,
			resetHeightOnDestroy: true,
			handleWindowResize: true,
			height: function() {
				var height = parseInt(KTUtil.getViewPort().height);

				if (header) {
					height = height - parseInt(KTUtil.actualHeight(header));
					height = height - parseInt(KTUtil.css(header, 'marginTop'));
					height = height - parseInt(KTUtil.css(header, 'marginBottom'));
				}

				if (content) {
					height = height - parseInt(KTUtil.css(content, 'marginTop'));
					height = height - parseInt(KTUtil.css(content, 'marginBottom'));
				}

				height = height - parseInt(KTUtil.css(_element, 'paddingTop'));
				height = height - parseInt(KTUtil.css(_element, 'paddingBottom'));

				height = height - 2;

				return height;
			}
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
	module.exports = BookingsViewOverlay;
}

// Init Quick Bookings View Panel
BookingsViewOverlay.init('view-bookings');

//Load Bookings
function load_bookings(attrValue, statFlag){
	var url = '/participants/bookings/view/'+attrValue+'/ajax';
	if(statFlag){
		url = "/dashboard/ajax/section/bookings";
	}
	$("#view-bookings").find(".offcanvas-content")
		.html('<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div>');
	fetch(url, {
		method: 'GET'
	}).then(function (confirmResult) {
		return confirmResult.text();
	}).then(function (html) {
		$("#view-bookings").find(".offcanvas-content").html(html);
		if(statFlag){
			var element = attrValue.substring(attrValue.indexOf('#')+1);
			$("#view-bookings").find(".offcanvas-content > div").each(function () {
				if($(this).attr("id") !== element){
					$(this).hide();
				}
			});
		}
	});
}
