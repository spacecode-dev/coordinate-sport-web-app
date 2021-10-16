$(document).ready(function() {

	var existing_body_class = $('body').attr('class');

	$('.select a').click(function() {
		$('.select a').removeClass('active');
		$('body').attr('class', $(this).attr('class') + ' ' + existing_body_class);
		$('#logo').attr('src', $(this).attr('data-logo'));
		$(this).addClass('active');
		return false;
	});

	$('a.print').click(function() {
		window.print();
		return false;
	});

});