//=require jquery-migrate/dist/jquery-migrate.min.js
//=require plugins/bootstrap3/bootstrap.js
//=require magnific-popup/dist/jquery.magnific-popup.js
//=require plugins/richmarker/richmarker.min.js
//=require jquery-ui-dist/jquery-ui.js
//=require select2/dist/js/select2.full.js
//=require jquery-countdown/dist/jquery.countdown.js
//=require promise-polyfill/dist/polyfill.js
//=require fetch-polyfill/fetch.js
//=require components/book.js

$.fn.select2.defaults.set( "theme", "bootstrap" );

function scroll_to(target) {
	var $ = jQuery;
	$target = $(target);

	$('html, body').stop().animate({
		'scrollTop': $target.offset().top - $('header.main .navbar').outerHeight(true)
	}, 500, 'swing', function () {
		//window.location.hash = target;
		at_top();
	});
}

function at_top() {
	var scrollTop = $(window).scrollTop();
	if (scrollTop > 50) {
		$('.back-to-top').addClass('show');
	} else {
		$('.back-to-top').removeClass('show');
	}
}

$(document).ready(function() {
	$('.select2').select2();

	// if calendar has events on a day, add a class
	$('.SimpleCalendar .event').closest('td').addClass('has-events');
	$('.SimpleCalendar .has-events').each(function() {
		var event = $(this).find('.event').eq(0);
		if (event.length == 1) {
			$(this).find('time').wrapInner('<a href="#event-' + event.find('a').attr('data-block') + '"></a>');
		}
	});

	// maps
	if (typeof map_markers !== 'undefined') {
		var center = new google.maps.LatLng(53.739111, -0.332662);

		var map = new google.maps.Map(document.getElementById("map"), {
			zoom: 7,
			center: center,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			streetViewControl: false,
			fullscreenControl: false,
			controlSize: 24
		});

		var bounds = new google.maps.LatLngBounds();

		for (var i = 0; i < map_markers.length; i++) {
			var m = map_markers[i];
			if (m.lat == 0 || m.lng == 0) {
				continue;
			}
			var marker_class = 'marker';
			if (m.color == 'location') {
				m.color = '#000';
				marker_class += ' location';
			}
			var marker = new RichMarker({
				position:  new google.maps.LatLng(parseFloat(m.lat), parseFloat(m.lng)),
				map: map,
				title: m.label,
				content: '<span class="' + marker_class + '" title="' + m.label + '" style="background:' + m.color + '"></span>',
				shadow: "none",
				anchor: RichMarkerPosition.MIDDLE,
				link: m.link
			});
			bounds.extend(marker.position);
			if (m.link != null) {
				google.maps.event.addListener(marker, 'click', function () {
					window.location = this.link;
				});
			}
			if (map_markers.length == 1) {
				map.setZoom(15);
				map.setCenter(new google.maps.LatLng(parseFloat(m.lat), parseFloat(m.lng)));
			}
		}
		if (map_markers.length > 1) {
			map.fitBounds(bounds);
		}
	}

	// gallery
	$('.event-gallery').magnificPopup({
		delegate: 'a',
		type: 'image',
		tLoading: 'Loading image #%curr%...',
		mainClass: 'mfp-img-mobile',
		gallery: {
			enabled: true,
			navigateByImgClick: true,
			preload: [0,1] // Will preload 0 - before current, and 1 after the current image
		}
	});

	// datepicker
	$('.datepicker').datepicker({
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		yearRange: '-100:+10'
	});

	// set datepicker options for dob, only in past and make year selector go back further
	$('.datepicker-dob').datepicker('option', 'yearRange', '-100:+0');
	$('.datepicker-dob').datepicker('option', 'maxDate', '0');

	if ($('input#marketing_consent').length > 0) {
		function privacy_toggle_fields() {
			if ($('input#marketing_consent').is(':checked')) {
				$('.marketing_allowed').show();
			} else {
				$('.marketing_allowed').hide();
			}
			privacy_toggle_source_other();
		}
		privacy_toggle_fields();
		$('input#marketing_consent').change(function() {
			privacy_toggle_fields();
		});

		function privacy_toggle_source_other() {
			if ($('input#marketing_consent').is(':checked') && $('#source').val() == 'Other') {
				$('#source_other').closest('.form-group').show();
			} else {
				$('#source_other').closest('.form-group').hide();
			}
		}
		privacy_toggle_source_other();
		$('#source').change(function() {
			privacy_toggle_source_other();
		});
	}

	//Profile specify fields (gender specify, etc).
	if ($('.profile, .participant').length>=1) {
		$("body").on('change', "select[name='gender'], select[name='religion'], select[name='sexual_orientation']", function(e) {
			let selectName = $(this).attr("name");
			let selected = $(this,'option:selected').val();
			if (selected==="please_specify") {
				$(this).siblings("input[name='"+selectName+"_specify']").removeClass("hidden");
			}
			else {
				$(this).siblings("input[name='"+selectName+"_specify']").addClass("hidden");
			}
		});
	}

	// scroll to
	$('a[href^=\\#]:not(a[href=\\#]):not(.panel-heading a)').click(function(e) {
		e.preventDefault();
		scroll_to(this.hash);
		return false;
	});

	$('.back-to-top').click(function(e) {
		e.preventDefault();
		scroll_to('html');
	});

	at_top();
	$(window).scroll(function() {
		at_top();
	});

	if ($('.countdown').length > 0) {
		$('.countdown').each(function() {
			var to = $(this).attr('data-countdown-to');
			$(this).countdown(to, function(event) {
				$(this).html(
					event.strftime('<span><span class="figure">%D</span>Day%!D</span><span><span class="figure">%H</span>Hour%!H</span><span><span class="figure">%M</span>Minute%!M</span><span><span class="figure">%S</span>Second%!S</span %H:%M:%S')
				);
			});
		});
	}
});

if (document.getElementById("pin")!==null) {
	setInputFilter(document.getElementById("pin"), function(value) {
	  return /^\d*\.?\d*$/.test(value); // Allow digits and '.' only, using a RegExp
	});

	function setInputFilter(textbox, inputFilter) {
	  ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
		textbox.addEventListener(event, function() {
		  if (inputFilter(this.value)) {
			this.oldValue = this.value;
			this.oldSelectionStart = this.selectionStart;
			this.oldSelectionEnd = this.selectionEnd;
		  } else if (this.hasOwnProperty("oldValue")) {
			this.value = this.oldValue;
			this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
		  } else {
			this.value = "";
		  }
		});
	  });
	}
}
