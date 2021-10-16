"use strict";

var CustomerSlideOut = function() {
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
			closeBy: 'customer-slide-out-close',
			toggleBy: 'customer-slide-out-toggle'
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
	module.exports = CustomerSlideOut;
}

// Init customer slide out window
CustomerSlideOut.init('view-customer-slide-out');

var leftside_width = 0;
var total_width = 0;
setTimeout(function() {
	var sidebar = $('#kt_aside_menu');
	var table = $('.table-striped');
	var tbl_width = 0;
	var  i =0;
	table.find('tbody td')
		.each(function() {
			tbl_width += $(this).width();
			if(i == 1){
				return false;
			}
			i++;
		});
	leftside_width = parseInt($("#kt_aside").position().left + sidebar.width()) + 20 + tbl_width;
	total_width = parseInt($( window ).width());
	$("#view-customer-slide-out.offcanvas").css("width", (total_width - leftside_width - 10));
	$("#view-customer-slide-out.offcanvas-right").css("right", '-'+(total_width - leftside_width - 10)+'px');
}, 1000);

$( window ).resize(function() {
	var sidebar = $('#kt_aside_menu');
	var table = $('.table-striped');
	var tbl_width = 0;
	var  i =0;
	table.find('tbody td')
		.each(function() {
			tbl_width += $(this).width();
			if(i == 1){
				return false;
			}
			i++;
		});
	leftside_width = parseInt($("#kt_aside").position().left + sidebar.width()) + 20 + tbl_width;
	total_width = parseInt($( window ).width());
	$("#view-customer-slide-out.offcanvas").css("width", (total_width - leftside_width - 10));
	$("#view-customer-slide-out.offcanvas-right").css("right", '-'+(total_width - leftside_width - 10)+'px');
});

$(".customer-slide-out-toggle").click(function() {
	$("#view-customer-slide-out.offcanvas-right").css("right", '0');
});

$("#customer-slide-out-close").click(function() {
	$("#view-customer-slide-out.offcanvas-right").css("right", '-'+(total_width - leftside_width - 10)+'px');
});

//Load Bookings
function load_customer_slide_out(attrValue, contact){
	var offCanvasContent = $("#view-customer-slide-out").find(".offcanvas-content");
	offCanvasContent.prepend('<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div>');
	$(".offcanvas-overlay").click(function () {
		$("#view-customer-slide-out.offcanvas-right").css("right", '-'+(total_width - leftside_width - 10)+'px');
	});
	var url = '';
	if(contact == 1){
		url = '/customers/addresses/'+attrValue;
	}else{
		url = '/customers/edit/'+attrValue;
	}
	offCanvasContent.html('<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div><iframe src="'+url+'" title="Customer data" class="customer-iframe">');
	var iframe = offCanvasContent.find("iframe");
	$('.customer-iframe').on("load", function(){
		init_content_monitor();
		var iframe = $(this);
		var iframeContent = $(this).contents();
		$(this).css({'visibility': 'hidden'});
		iframeContent.find("body").css({'background': 'transparent'});
		iframeContent.find("#account_overridden").remove();
		iframeContent.find("#kt_header_mobile").remove();
		iframeContent.find("#kt_header").remove();
		iframeContent.find("#kt_aside").remove();
		iframeContent.find("#kt_subheader").remove();
		iframeContent.find("#kt_wrapper").css({'padding':'0'});
		iframeContent.find("#kt_content").css({'margin-top':'0', 'padding-top':'0'});
		iframeContent.find(".topbar").remove();
		iframeContent.find(".footer").remove();
		if(iframeContent.find(".slide-out-btn").length){
			iframeContent.find(".slide-out-btn").removeClass("d-none");
		}
		var card_width = iframeContent.find(".card:first").width();
		var card_height = iframeContent.find(".card:first").height();
		iframeContent.find(".card:first").css({'position':'fixed', 'z-index':'12', 'width': card_width+'px'});
		var alert = iframeContent.find("body").find('.alert-custom');
		if(alert.length > 0){
			var card = iframeContent.find(".card:first");
			alert.insertAfter(card);
				alert.css({'margin-bottom': '10px', 'margin-top': (card_height + 20)+'px'});
		}else{
			iframeContent.find(".card:eq(1)").css({'margin-top': (card_height + 20)+'px'});
		}
		$(this).css({'visibility': 'visible'});
		if(iframeContent.find(".spinner").length > 0){
			iframeContent.find(".spinner").remove();
		}
		if(offCanvasContent.find(".spinner").length > 0){
			offCanvasContent.find(".spinner").remove();
		}
	});


	$("#view-customer-slide-out").find("div.card").css({'position':'fixed', 'z-index':'12', 'width': (total_width - leftside_width - 10 - 65)+'px'});
	$("#view-customer-slide-out").find("fieldset.card").css({'margin-top': ($("#view-customer-slide-out").find("div.card").height() + 15)+'px'});
	// select2
	$('.select2').select2();

	fixed_scrollbar();
	$( window ).resize(function() {
		fixed_scrollbar();
	});

	// select2 tags
	$(".select2-tags").select2({
		tags: true
	});

	$("#view-customer-slide-out").find('.card').each(function() {
		var card_el = $(this);
		var card = new KTCard(this);
		if (this.querySelector("[data-card-tool=toggle]")) {
			$(card_el.children(".card-header")[0]).css("cursor","pointer").on("click", function (e) {
				if ($(e.target).is(".card-header, .card-title, .card-label, .card-toolbar")) {
					card.toggle();
				}
			});
		}
		card.on('beforeCollapse', function (card) {
			card_el.find('.card-footer').slideUp();
		});
		card.on('beforeExpand', function (card) {
			card_el.find('.card-footer').slideDown();
		});
	});
}
