var should_calc_totals = true;
var block_subtotals = new Object();
var block_discounts = new Object();
var block_totals = new Object();

var participants_selected = 0;
var subscriptions_selected = 0;
var lesson_participants = [];
var lesson_participants_exc_subs = [];
var participant_dobs = [];
var higherScopeReq = false;
var selected_lessons_temp = [];

function calc_age(dob, at) {
	var dob_parts = dob.split('-');
	var dob = new Date(dob_parts[0], dob_parts[1] - 1, dob_parts[2]);
	var at_parts = at.split('-');
	var at = new Date(at_parts[0], at_parts[1] - 1, at_parts[2]);
	var diff = new Date(at.getTime() - dob.getTime());
	var age = diff.getUTCFullYear() - 1970;
	if (age < 0) {
		age = 0;
	}
	return age;
}

function update_participants(initial) {
	var participants = {};
	participants_selected = 0;
	$('#select-participants input[type=checkbox]').each(function() {
		if ($(this).is(':checked')) {
			var participantID = $(this).val();
			// use string as key so keeps same order
			var key = '#' + participantID;
			participants[key] = $(this).closest('label').find('.name').text();
			participants_selected++;
			participant_dobs[participantID] = $(this).attr('data-dob')
		}
	});
	if (Object.keys(participants).length == 1) {
		$('#sessions').addClass('single-participant');
	} else {
		$('#sessions').removeClass('single-participant');
	}
	if ($('#select-participants input[type=checkbox]:checked').length > 0) {
		$('#step2:hidden').slideDown();
	} else {
		$('#step2:visible').slideUp();
	}

	$('#select-participants :checkbox').off('change').change(function() {
		var participantID = $(this).val();
		var subID = '#subscription-' + participantID;

		if(this.checked) {
			var selectedSub = $('#select-participants input[type=checkbox][value=' + participantID + ']').attr('data-subscription-id');
			if(selectedSub != '') {
				var id = 'sub-' + participantID + '-' + selectedSub;
				if($("#"+id).length){
					document.getElementById(id).checked = true;
				}

				$(subID + ' input[type=checkbox]').each(function() {
					if(!$(this).is(':checked')) {
						//$(this).parent().hide();
					}
				});
			}
			$(subID + ':hidden').slideDown();
		} else {
			$(subID + ' input[type=checkbox]').each(function() {
				$(this).prop("checked", false);
				$(this).parent().show();
			});
			$(subID + ':visible').slideUp();
		}
	});

	// write html
	var dates = [];
	var selecteddates = [];
	$('.sessions .lesson:not(.sold-out)').each(function() {
		var lesson = $(this);
		var lessonID = lesson.attr('data-lessonID');
		var lesson_date = lesson.attr('data-date');
		var min_age = parseInt(lesson.attr('data-min_age'));
		var max_age = parseInt(lesson.attr('data-max_age'));
		var participants_html = '';
		var someone_checked = false;
		var someone_allowed = false;
		for (var key in participants) {
			var participantID = key.substring(1);
			var age = calc_age(participant_dobs[participantID], lesson_date);
			var checked = '';
			if (selected_lessons.hasOwnProperty(lessonID) && selected_lessons[lessonID].hasOwnProperty(lesson_date) && selected_lessons[lessonID][lesson_date].includes(participantID)) {
				checked = ' checked';
				someone_checked = true;
			}
			if (selected_lessons_temp.hasOwnProperty(lessonID) && selected_lessons_temp[lessonID].hasOwnProperty(lesson_date) && selected_lessons_temp[lessonID][lesson_date].includes(participantID)) {
				checked = ' checked';
				someone_checked = true;
			}

			var input = '<input type="checkbox" id="lessons_'+lessonID+lesson_date+participantID+'" name="lessons[' + lessonID + '][' + lesson_date + '][]" value="' + participantID + '"' + checked + '>';

			var icons = '<i class="far fa-circle unchecked"></i>';
			icons += '<i class="far fa-check-circle checked"></i>';
			icons += '<i class="far fa-ban disabled"></i>';
			var suffix = '';

			participants_html += '<div class="checkbox">';
			participants_html += '<label class="fancy-checkbox';

			// check if already booked session in another booking
			if (already_booked_sessions.hasOwnProperty(lessonID) && already_booked_sessions[lessonID].hasOwnProperty(lesson_date) && already_booked_sessions[lessonID][lesson_date].includes(participantID)) {
				participants_html += ' disabled alreadybooked';
				input = '';
				icons = '<i class="far fa-ban"></i>';
				suffix = ' (Already Booked)';
				// check age
			} else if ((min_age > 0 && age < min_age) || (max_age > 0 && age > max_age)) {
				participants_html += ' disabled';
				input = '';
				icons = '<i class="far fa-ban"></i>';
				if (min_age > 0 && age < min_age) {
					suffix = ' (Too Young)';
				} else if (max_age > 0 && age > max_age) {
					suffix = ' (Too Old)';
				}
			}else if($('.subscriptions').length > 0 && $('.subscriptions').hasClass('subs_only') && $('input[name="subscriptions['+participantID+']"]:checked').length == 0){
				participants_html += ' disabled';
				input = '';
				icons = '<i class="far fa-ban"></i>';
				lesson.find(".fancy-checkbox").attr('subonly', "1");
				suffix = ' (Subscription Only)';
			}else if((!someone_checked && $('.subscriptions').length > 0 && $('input[name="subscriptions['+participantID+']"][data-sub-active="0"]:checked').length > 0) || (lesson.find(".fancy-checkbox").length > 0 && lesson.find(".fancy-checkbox").attr("subs-inactive").length > 0 && lesson.find(".fancy-checkbox").attr("subs-inactive") != "" && lesson.find(".fancy-checkbox").attr("subs-inactive").indexOf(participantID) != -1)){
				participants_html += ' disabled';
				input = '';
				icons = '<i class="far fa-ban"></i>';
				suffix = ' (Subscription inactive/cancelled)';
			} else {
				lesson.find(".fancy-checkbox").removeAttr('subonly');
				someone_allowed = true;
			}
			participants_html += '">';
			participants_html += input;
			participants_html += icons;
			participants_html += ' <span class="name">' + participants[key] + suffix + '</span>';
			participants_html += '</label>';
			participants_html += '</div>';
		}
		lesson.find('.participants').html(participants_html);
		if (initial && someone_checked) {
			$('#initialFlag').val(0);
			lesson.find('.lesson-toggle').prop('checked', true);
			lesson.find(".participants").show();
		}else if(lesson.find('.participants input[type=checkbox]:checked').length > 0){
			lesson.find(".participants").show();
		}
		if (someone_allowed) {
			lesson.removeClass('all-disabled');
		} else {
			lesson.addClass('all-disabled');
		}
		calc_lesson(lesson);

		if($('.subscriptions').hasClass('subs_only')){
			if($('input[name="subscriptions['+participantID+']"]:checked').length > 0){
				if($(".sessions").find(".participants").find("input[type='checkbox'][value='"+participantID+"']:checked").length == 0){
					document.getElementById('add-to-cart').disabled = true;
				}else{
					document.getElementById('add-to-cart').disabled = false;
				}
			}
		}
	});
	// calc totals
	calc_totals();

	// monitoring
	$('#monitoring').find('th, td').not(':first-child').remove();
	for (var key in participants) {
		var participantID = key.substring(1);
		$('#monitoring thead tr').append('<th scope="col">' + participants[key] + '</th>');
		$('#monitoring tbody tr').each(function() {
			var monitoringID = $(this).attr('data-key');
			var value = '';
			if (monitoring_existing.hasOwnProperty(monitoringID) && monitoring_existing[monitoringID].hasOwnProperty(participantID)) {
				value = monitoring_existing[monitoringID][participantID];
			}
			if (monitoringID == 'medical') {
				var checked = '';
				if (value == 1) {
					checked = ' checked="checked"';
				}
				$(this).append('<td class="has_checkbox"><input type="checkbox" value="1" name="monitoring[' + monitoringID + '][' + participantID + ']"' + checked + '></td>');
			} else {
				$(this).append('<td><input type="text" class="form-control" maxlength="255" name="monitoring[' + monitoringID + '][' + participantID + ']" value="' + value + '"></td>');
			}
		});
	}

	//Subscription Validation
	if($(".subscriptions").length > 0){
		$("#select-participants input[type=checkbox]:checked").each(function () {
			var participantID = $(this).val();
			if($('input[name="subscriptions['+participantID+']"]:checked').length > 0){
				//If user has selected one session but not selected any subscription show price otherwise hide them
				if($(".sessions .lesson").find("input[type='checkbox']:checked").length === 0){
					$(".sessions .lesson").find(".price").html('');
				}

				//If user has selected subscription but not selected session then disable add-to-cart
				if($(".lesson").find("input[type='checkbox'][value='"+participantID+"']").length == 0){
					document.getElementById('add-to-cart').disabled = false;
					return false;
				}
			}
		});
	}
}

function calc_lesson(lesson) {
	var participants = lesson.find('.participants input[type=checkbox]:checked');
	var count = participants.length;
	var date = lesson.attr('data-date');
	var lessonID = lesson.attr('data-lessonID');
	var participants = lesson.find('.participants input[type=checkbox]:checked');
	var participants_exc_subs = lesson.find('.participants input[type=checkbox]:not([data-subscription]):checked');

	// store participant count in var
	if (!lesson_participants.hasOwnProperty(lessonID)) {
		lesson_participants[lessonID] = new Object();
	}
	lesson_participants[lessonID][date] = [];
	participants.each(function() {
		lesson_participants[lessonID][date].push(participants.length);
	});

	// store participant count in var (excluding subscriptions)
	if (!lesson_participants_exc_subs.hasOwnProperty(lessonID)) {
		lesson_participants_exc_subs[lessonID] = new Object();
	}
	lesson_participants_exc_subs[lessonID][date] = [];
	participants.each(function() {
		lesson_participants_exc_subs[lessonID][date].push(participants_exc_subs.length);
	});

	if(subscriptions_selected > 0) {
		// subscription = true;
		count = count - subscriptions_selected;
	}

	// delete existing session/date if set
	if (selected_lessons.hasOwnProperty(lessonID) && selected_lessons[lessonID].hasOwnProperty(date)) {
		delete selected_lessons[lessonID][date];
	}

	// store data in var
	if (count > 0) {
		if (!selected_lessons.hasOwnProperty(lessonID)) {
			selected_lessons[lessonID] = new Object();
		}
		selected_lessons[lessonID][date] = [];
		participants.each(function() {
			selected_lessons[lessonID][date].push($(this).val());
		});
	}

	// assume not subscription
	lesson.find('.participants input[type=checkbox]').removeAttr('data-subscription');

	// if nothing selected, show price for 1 person
	if (count < 1) {
		count = 1;
	}
	var price = 0;
	var no_price = 0;
	if(subscriptions_selected > 0){
		participants.each(function() {
			if ($('input[name="subscriptions[' + $(this).val() + ']"]:checked').length > 0){

				$(".sessions .lesson").find("input[type='checkbox']:checked").each(function() {
					var pId = $(this).val();
					if(pId !== ""){
						if($('input[name="subscriptions[' + pId + ']"]:checked').length === 0){
							no_price = 1;
						}
					}
				});
				if(no_price === 0){
					price = '';
					lesson.find('.price').html(price);
				}
				// if participant has subscription and sessions selected, mark as subscription for auto/sibling discounts and totals
				var participant_checkbox = lesson.find('.participants input[type=checkbox][value="' + $(this).val() + '"]');
				if ($(participant_checkbox).is(':checked') > 0) {
					participant_checkbox.attr('data-subscription', 'true');
				}
			} else {
				price = parseFloat(lesson.attr('data-price'))*count;
				price = (Math.round( price * 100 ) / 100).toFixed(2);
				lesson.find('.price').html(currency_symbol + '<span></span>');
				lesson.find('.price span').text(price);
			}
		});
	}else{
		price = parseFloat(lesson.attr('data-price'))*count;
		price = (Math.round( price * 100 ) / 100).toFixed(2);
		lesson.find('.price').html(currency_symbol + '<span></span>');
		lesson.find('.price span').text(price);
	}
}

function calc_totals() {
	var sub_total = 0;
	var discount = 0;
	var total = 0;
	var subscription_total = 0;
	var selected_subscriptions = $('.subscriptions input[type=checkbox]:checked');

	// reset block totals
	block_subtotals = new Object();
	block_discounts = new Object();
	block_totals = new Object();

	// calc block totals and discounts
	calc_blocks();

	// add block subtotals
	for (var block in block_subtotals) {
		sub_total += block_subtotals[block];
	}

	// add block discounts
	for (var block in block_discounts) {
		discount += block_discounts[block];
	}

	// add block totals
	for (var block in block_totals) {
		total += block_totals[block];
	}

	// show block totals for fixed discounts
	//$('.panel .block_totals').hide();
	for (var block in block_subtotals) {
		var block_totals_div = $('.panel[data-block="' + block + '"] .block_totals');
		var table = block_totals_div.closest('.panel').find('table');
		if (block_subtotals.hasOwnProperty(block)) {
			block_totals_div.find('.price span').text(block_subtotals[block].toFixed(2));
			if (block_totals[block] < block_subtotals[block]) {
				block_totals_div.find('.discounted_price').text(currency_symbol + block_totals[block].toFixed(2));
				block_totals_div.find('.price').addClass('discounted');
				//block_totals_div.show();
			} else {
				block_totals_div.find('.discounted_price').text('');
				block_totals_div.find('.price').removeClass('discounted');
			}
			// always show block_totals_div if has block price
			if (table.attr('data-block_price')) {
				//block_totals_div.show();
			}
		}
	}

	//calc subscriptions
	var sub_prices = {'Weekly':0, 'Monthly':0, 'Yearly':0};
	if(selected_subscriptions.length > 0) {
		price = 0;
		$('.sub-total').html('');

		for(var i = 0; i < selected_subscriptions.length; i++) {
			var price = selected_subscriptions[i].getAttribute('data-sub-price');
			sub_prices[selected_subscriptions[i].getAttribute('data-frequency')] = parseFloat(sub_prices[selected_subscriptions[i].getAttribute('data-frequency')]) + parseFloat(price);
			price = parseInt(price);
			subscription_total += price;
		}
	}else{
		sub_prices = {};
		$('.sub-total').html('<p>Subscription Total: ' + currency_symbol + '<span>0.00</span></p>');
	}

	if (!jQuery.isEmptyObject(sub_prices)) {
		$.each(sub_prices, function (key, value) {
			if(value > 0) {
				$('.sub-total').append('<p>' + key + ' Subscription Total: ' + currency_symbol + '<span>' + value.toFixed(2) + '</span></p>');
			}
		});
	}

	total = sub_total - discount + subscription_total;
	$('#sub_total').text(sub_total.toFixed(2));
	if (discount > 0) {
		$('#discount').closest('p').slideDown();
	} else {
		$('#discount').closest('p').slideUp();
	}
	$('#discount').text(discount.toFixed(2));
	$('#total').text(total.toFixed(2));

	// show lesson price as blank if number of participants matches number of subscriptions selected
	if (participants_selected === subscriptions_selected){
		$(".lesson").find('.price').html('');
	}
}

function calc_blocks() {
	$('.sessions').each(function() {
		var panel = $(this).closest('.panel-body');
		var table = $(this);
		var potential_autodiscount = 0;
		var potential_siblingdiscount = 0;
		var block = parseInt(panel.closest('.panel').attr('data-block'));

		// reset ui
		$('td.lesson', table).attr('data-discount', '0');
		$('td.lesson', table).find('.discounted_price').text('');
		$('td.lesson', table).find('.price').removeClass('discounted').removeClass('discounted_samesize');

		// reset totals
		block_subtotals[block] = 0;
		block_discounts[block] = 0;
		block_totals[block] = 0;

		// calc prices without discounts
		var lessons = table.find('td.lesson');
		var participants_count = 0;
		if (lessons.length > 0) {
			$(lessons).each(function() {
				var participants = $(this).find('.participants input[type=checkbox]:checked:not([data-subscription])').length;
				var price = parseFloat($(this).attr('data-price'));
				block_subtotals[block] += participants*price;

				// store participants count for block price below
				participants_count = participants;
			});
		}
		// if block priced, use instead
		if (table.attr('data-block_price')) {
			block_subtotals[block] = parseFloat(table.attr('data-block_price')) * participants_count;
		}

		// calc autodiscount
		if (table.attr('data-autodiscount') !== 'off') {
			var autodiscount_eligible_lessons = table.find('td.lesson[data-autodiscount]:not([data-autodiscount="0"])');
			var autodiscount_lessons = autodiscount_eligible_lessons.length;
			if (autodiscount_lessons > 0) {
				var autodiscount_selected_lessons = 0;
				var autodiscount_participants = 0;
				var autodiscount_discounts = {};
				$(autodiscount_eligible_lessons).each(function() {
					if (lesson_participant_count($(this), false) > 0) {
						autodiscount_selected_lessons++;
					}
				});
				if (autodiscount_selected_lessons == autodiscount_lessons) {
					$(panel).find('.upsell-autodiscount').stop().slideUp();
					var i = 0;
					$(autodiscount_eligible_lessons).each(function() {
						// count total participants who booked all autodiscount sessions
						var participants = lesson_participant_count($(this), false);
						if (i == 0) {
							// if first run, set to selected participants from first session
							autodiscount_participants = participants;
						} else if (participants < autodiscount_participants) {
							// on subsequent, reduce as less are selected
							autodiscount_participants = participants;
						}
						if (autodiscount_participants > 0) {
							var date = $(this).closest('tr').attr('data-date');
							var lesson_id = $(this).attr('data-lessonid');
							// if not fixed amount discount, calc discount
							if (table.attr('data-fixed_autodiscount') === undefined) {
								var discount = parseFloat($(this).attr('data-autodiscount'));
								// create sub object if doesn't exist
								if (date in autodiscount_discounts == false){
									autodiscount_discounts[date] = {};
								}
								// keep track of discount
								autodiscount_discounts[date][lesson_id] = discount;
							}
						}
						i++;
					});
					// work out potential auto discount
					if (table.attr('data-fixed_autodiscount') !== undefined) {
						potential_autodiscount += parseFloat(table.attr('data-fixed_autodiscount'))*autodiscount_participants;
					} else {
						for (date in autodiscount_discounts) {
							for (lesson_id in autodiscount_discounts[date]) {
								potential_autodiscount += autodiscount_discounts[date][lesson_id]*autodiscount_participants;
							}
						}
					}
				}
			}

			// show upsell
			if (autodiscount_selected_lessons > 0) {
				var lessons_needed = autodiscount_lessons - autodiscount_selected_lessons;
				$(panel).find('.upsell-autodiscount .lessons_needed').text(lessons_needed);
				if (lessons_needed > 1) {
					$(panel).find('.upsell-autodiscount .pl').show();
				} else {
					$(panel).find('.upsell-autodiscount .pl').hide();
				}
				if (lessons_needed > 0) {
					$(panel).find('.upsell-autodiscount').stop().slideDown();
				} else {
					$(panel).find('.upsell-autodiscount').stop().slideUp();
				}
			}
		}

		// calc sibling discount
		if (table.attr('data-siblingdiscount') !== 'off') {
			var siblingdiscount_eligible_lessons = table.find('td.lesson[data-siblingdiscount]');
			var siblingdiscount_lessons = siblingdiscount_eligible_lessons.length;
			if (siblingdiscount_lessons > 0) {
				var siblingdiscount_participants = 0;
				var siblingdiscount_max_participants = 0;
				var siblingdiscount_selected_lessons = 0;
				var siblingdiscount_discounts = {};
				$(panel).find('.upsell-siblingdiscount').stop().slideUp();
				var i = 0;
				$(siblingdiscount_eligible_lessons).each(function() {
					// get selected participants
					var participants = $(this).find('.participants input[type=checkbox]:not([data-subscription]):checked').length;
					// get already booked
					var alreadybooked_participants = 0;
					var lessonID = $(this).attr('data-lessonid');
					var lesson_date = $(this).attr('data-date');
					if (already_booked_sessions.hasOwnProperty(lessonID) && already_booked_sessions[lessonID].hasOwnProperty(lesson_date)) {
						alreadybooked_participants = already_booked_sessions[lessonID][lesson_date].length;
					}
					// work out total participants for sibling discount
					var total_participants = participants + alreadybooked_participants;

					// get count of participants for fixed sibling discount
					if (i == 0) {
						// if first run, set to selected participants from first session
						siblingdiscount_participants = total_participants;
						siblingdiscount_max_participants = total_participants;
					} else {
						// on subsequent
						// reduce participants if less for fixed discount
						if (total_participants < siblingdiscount_participants) {
							siblingdiscount_participants = total_participants;
						}
						// increase max participants for upsell
						if (total_participants > siblingdiscount_max_participants) {
							siblingdiscount_max_participants = total_participants;
						}
					}
					// must be 2 or more participants booked on same session including existing bookings
					if (total_participants >= 2) {
						var discount = parseFloat($(this).attr('data-siblingdiscount'));
						var date = $(this).closest('tr').attr('data-date');
						var lesson_id = $(this).attr('data-lessonid');
						siblingdiscount_selected_lessons++;
						// if not fixed amount discount, calc discount
						if (table.attr('data-fixed_siblingdiscount') === undefined) {
							var discount = parseFloat($(this).attr('data-siblingdiscount'));
							// create sub object if doesn't exist
							if(date in siblingdiscount_discounts == false){
								siblingdiscount_discounts[date] = {};
							}
							// keep track of discount
							siblingdiscount_discounts[date][lesson_id] = discount;
							// add to potential
							potential_siblingdiscount += discount*participants;
						}
					}
					i++;
				});
				// work out potential sibling discount
				if (siblingdiscount_participants >= 2) {
					if (table.attr('data-fixed_siblingdiscount') !== undefined) {
						potential_siblingdiscount = 0;
						if (siblingdiscount_lessons == siblingdiscount_selected_lessons) {
							potential_siblingdiscount = parseFloat(table.attr('data-fixed_siblingdiscount'))*siblingdiscount_participants;
						}
					} else if (table.attr('data-block_price')) {
						if (table.attr('data-siblingdiscount') == 'percentage') {
							potential_siblingdiscount = parseFloat(table.attr('data-block_price'))*siblingdiscount_participants*(parseFloat(table.attr('data-siblingdiscount_amount'))/100);
						} else if (table.attr('data-siblingdiscount') == 'amount') {
							potential_siblingdiscount = parseFloat(table.attr('data-siblingdiscount_amount'))*siblingdiscount_participants;
						}
					}
				}

				// show upsell
				if (siblingdiscount_max_participants > 0) {
					if (table.attr('data-fixed_siblingdiscount') !== undefined) {
						// fixed discount, all session required
						var participants_needed = 2;
						$(panel).find('.upsell-siblingdiscount .participants_needed').text(participants_needed);
						$(panel).find('.upsell-siblingdiscount .on').text('all sessions');
						if (participants_needed > 1) {
							$(panel).find('.upsell-siblingdiscount .pl').show();
						} else {
							$(panel).find('.upsell-siblingdiscount .pl').hide();
						}
						if (siblingdiscount_participants < 2) {
							$(panel).find('.upsell-siblingdiscount').stop().slideDown();
						} else {
							$(panel).find('.upsell-siblingdiscount').stop().slideUp();
						}
					} else {
						if (siblingdiscount_max_participants < 2) {
							var participants_needed = 2 - siblingdiscount_max_participants;
							$(panel).find('.upsell-siblingdiscount .participants_needed').text(participants_needed + ' more');
							$(panel).find('.upsell-siblingdiscount .on').text('a session');
							if (participants_needed > 1) {
								$(panel).find('.upsell-siblingdiscount .pl').show();
							} else {
								$(panel).find('.upsell-siblingdiscount .pl').hide();
							}
							if (participants_needed > 0) {
								$(panel).find('.upsell-siblingdiscount').stop().slideDown();
							} else {
								$(panel).find('.upsell-siblingdiscount').stop().slideUp();
							}
						}
					}
				}
			}
		}

		//console.log('Auto: ' + potential_autodiscount + ' / Sibling: ' + potential_siblingdiscount);

		// if autodiscount bigger, apply
		if (potential_autodiscount> 0 && potential_autodiscount >= potential_siblingdiscount) {
			//console.log('Auto discount bigger');
			// show discounts in ui
			if (table.attr('data-fixed_autodiscount') !== undefined) {
				// show as discounted, but at normal size
				$(autodiscount_eligible_lessons).find('.price').addClass('discounted_samesize');
			} else {
				if (Object.keys(autodiscount_discounts).length > 0) {
					for (date in autodiscount_discounts) {
						for (lesson_id in autodiscount_discounts[date]) {
							var session = table.find('tr[data-date="' + date + '"] td[data-lessonid="' + lesson_id + '"]');
							var discount = autodiscount_discounts[date][lesson_id];
							var discounted_price = (parseFloat($(session).attr('data-price')) - discount).toFixed(2);
							$(session).attr('data-discount', discount);
							$(session).find('.discounted_price').text(currency_symbol + (discounted_price*autodiscount_participants).toFixed(2));
							$(session).find('.price').addClass('discounted');
						}
					}
				}
			}
			// save
			block_discounts[block] = potential_autodiscount;
		// if sibling discount bigger, apply
		} else if (potential_siblingdiscount > 0 && potential_siblingdiscount > potential_autodiscount) {
			//console.log('Sibling discount bigger');
			// show discounts in ui
			if (table.attr('data-fixed_siblingdiscount') !== undefined) {
				// show as discounted, but at normal size
				$(siblingdiscount_eligible_lessons).find('.price').addClass('discounted_samesize');
			} else {
				if (Object.keys(siblingdiscount_discounts).length > 0) {
					for (date in siblingdiscount_discounts) {
						for (lesson_id in siblingdiscount_discounts[date]) {
							var session = table.find('tr[data-date="' + date + '"] td[data-lessonid="' + lesson_id + '"]');
							var participants = $(session).find('.participants input[type=checkbox]:checked').length;
							var discount = siblingdiscount_discounts[date][lesson_id];
							var discounted_price = (parseFloat($(session).attr('data-price')) - discount).toFixed(2);
							$(session).attr('data-discount', discount);
							$(session).find('.discounted_price').text(currency_symbol + (discounted_price*participants).toFixed(2));
							$(session).find('.price').addClass('discounted');
						}
					}
				}
			}
			// save
			block_discounts[block] = potential_siblingdiscount;
		} else {
			//console.log('No discount applicable');
		}

		// calc totals
		block_totals[block] = block_subtotals[block] - block_discounts[block];

		// total can't be less than 0
		if (block_totals[block] < 0) {
			block_totals[block] = 0;
		}
	});
}

function mobilise_sessions_table() {
	$('.sessions').each(function() {
		var columns = [];
		var i = 0;
		$(this).find('thead th').each(function() {
			columns[i] = $(this).html().replace('<br>', ' (') + ')';
			i++;
		});
		$(this).find('tbody tr').each(function() {
			var i = 1;
			$(this).find('td').each(function() {
				$(this).attr('data-title', columns[i]);
				i++;
			});
		});
	});
}

$(document).ready(function() {
	$(".fancy-checkbox").change(function() {
		var attr = $(this).attr('subonly');
		if (typeof attr !== typeof undefined && attr !== false && $(this).find(".fa-circle").is(":hidden")) {
			show_error();
		}
	});

	if($(".subscriptions input[type=checkbox]:checked").length) {
		subscriptions_selected = $('.subscriptions input[type=checkbox]:checked').length;
		$(".subscriptions input[type=checkbox]:checked").each(function () {
			var id = $(this).attr('name').match(/\[(.*?)\]/);
			$("#subscription-"+id[1]).show();
		});
	}

	$("#select-participants input[type=checkbox]:checked").each(function (index) {
		var val = $(this).val();
		$('#subscription-'+val).show();
		$('#step2').slideDown();
	});

	if ($('body').hasClass('book') || $('body').hasClass('account_session')) {
		update_participants(true);
		mobilise_sessions_table();
		$('#select-participants').on('change', 'input[type=checkbox]', function() {
			if (!$(this).is(':checked')) {
				var participantID = $(this).val();
				$('.sessions .lesson-toggle:checked').each(function(){
					var lesson = $(this).closest('.lesson');
					var lessonID = lesson.attr('data-lessonID');
					var lesson_date = lesson.attr('data-date');
					if ($(".participants input[type=checkbox][name='lessons[" + lessonID + "][" + lesson_date + "][]'][value='"+participantID+"']:checked").length > 0) {
						if (!selected_lessons_temp.hasOwnProperty(lessonID)) {
							selected_lessons_temp[lessonID] = new Object();
						}
						if (!selected_lessons_temp[lessonID].hasOwnProperty(lesson_date)) {
							selected_lessons_temp[lessonID][lesson_date] = [];
						}
						if($.inArray(participantID, selected_lessons_temp[lessonID][lesson_date]) < 0){
							selected_lessons_temp[lessonID][lesson_date].push(participantID);
						}
					}

				});

			}
			update_participants(false);
		});

		$('.subscriptions').on('change', 'input[type=checkbox]', function() {
			var subs = $('.subscriptions');
			var name = document.getElementsByName(this.name);
			var id = $(this).attr("id");
			var newid = id.replace('sub', 'sp');

			if(this.checked) {
				$('#'+newid).show();
				for(var i = 0; i < name.length; i++) {
					if(name[i].id != this.id) {
						name[i].checked = false;
						var ids = name[i].id;
						var newid1 = ids.replace('sub', 'sp');
						$('#'+newid1).hide();
					}
				}
			}else{
				$('#'+newid).hide();
			}
			// count number of subscriptions selected
			subscriptions_selected = $('.subscriptions input[type=checkbox]:checked').length;
			update_participants(false);
		});

		// bulk select row
		$('.sessions th[scope="row"]').click(function() {
			// disable per total calculations and run after instead of each session for performance
			should_calc_totals = false;
			var currenElement = $(this);
			var ele = $('.lesson-toggle', $(this).closest('tr'));
			var attr = $('.fancy-checkbox', $(this).closest('tr')).attr("subonly");
			if(currenElement.attr("data-check-all") == "1"){
				ele.prop('checked', false).trigger('change');
				currenElement.attr("data-check-all", "0");
			}else{
				ele.prop('checked', true).trigger('change');
				if(typeof attr !== typeof undefined && attr !== false){
					show_error();
				}
				currenElement.attr("data-check-all", "1");
			}
			higherScopeReq = false;
			//$('.lesson-toggle', $(this).closest('tr')).prop('checked', true).trigger('change');
			// reenable
			should_calc_totals = true;
			calc_totals();
		});

		// bulk select column
		$('.sessions th[scope="col"]').click(function() {
			var currenElement = $(this);
			var cells = $(this).closest('tr').find('th');
			var index = cells.index(this);
			// disable per total calculations and run after instead of each session for performance
			should_calc_totals = false;
			higherScopeReq = true;
			var rowFlag = false;
			$(this).closest('.sessions').find('tbody tr').each(function() {
				//Column Calculations
				var ele = $(this).find('th, td').eq(index);
				var attr = ele.find(".fancy-checkbox").eq(0).attr("subonly");
				var ele_lesson = ele.find('.lesson-toggle');
				if(currenElement.attr("data-check-all") == "1"){
					ele_lesson.prop('checked', false).trigger('change');
				}else{
					ele_lesson.prop('checked', true).trigger('change');
					if(typeof attr !== typeof undefined && attr !== false){
						show_error();
					}
				}

				//Row calculations
				var currentRow = $(this);
				currentRow.find("td").each(function(){
					if(!$(this).hasClass("no-lesson") && !$(this).find('.lesson-toggle').prop('checked')){
						currentRow.find('th').attr('data-check-all', '0');
						rowFlag = true;
					}
				});
				if(!rowFlag){
					currentRow.find('th').attr('data-check-all', '1');
				}
			});
			higherScopeReq = false;
			if(currenElement.attr("data-check-all") == "1") {
				currenElement.attr("data-check-all", '0');
			}else{
				currenElement.attr("data-check-all", '1');
			}
			// reenable
			should_calc_totals = true;
			calc_totals();
		});

		Date.prototype.getWeek = function (dowOffset) {
			dowOffset = typeof(dowOffset) == 'int' ? dowOffset : 0; //default dowOffset to zero
			var newYear = new Date(this.getFullYear(),0,1);
			var day = newYear.getDay() - dowOffset; //the day of week the year begins on
			day = (day >= 0 ? day : day + 7);
			var daynum = Math.floor((this.getTime() - newYear.getTime() -
				(this.getTimezoneOffset()-newYear.getTimezoneOffset())*60000)/86400000) + 1;
			var weeknum;
			//if the year starts before the middle of a week
			if(day < 4) {
				weeknum = Math.floor((daynum+day-1)/7) + 1;
				if(weeknum > 52) {
					nYear = new Date(this.getFullYear() + 1,0,1);
					nday = nYear.getDay() - dowOffset;
					nday = nday >= 0 ? nday : nday + 7;
					/*if the next year starts before the middle of
					  the week, it is week #1 of that year*/
					weeknum = nday < 4 ? 1 : 53;
				}
			}
			else {
				weeknum = Math.floor((daynum+day-1)/7);
			}
			return weeknum;
		};

		$('.lesson-toggle').change(function() {

			var lesson = $(this).closest('.lesson');

			if ($(this).is(':checked')) {
				$('#participantFlag').val(0);
				lesson.find('.participants input[type=checkbox]').prop('checked', true).trigger('change');
				//lesson.find(".participants").show();
			} else {
				$('#participantFlag').val(0);
				lesson.find('.participants input[type=checkbox]').prop('checked', false).trigger('change');
				//lesson.find(".participants").hide();
			}
			calc_lesson(lesson);

			if($(this).prop("checked")){
				select_all_sessions();
			}

			//check column and row header status
			var currIndex = $(this).closest("td").index();
			var colHead = $(this).closest("tbody").prev().find('tr').find('th').eq(currIndex);
			var colFlag = 0;
			var rowFlag = false;
			$(this).closest('.sessions').find('tbody tr').each(function() {
				//Column Calculations
				if($(this).find('th, td').eq(currIndex).find('.lesson-toggle').length > 0 && !$(this).find('th, td').eq(currIndex).find('.lesson-toggle').prop('checked') && !higherScopeReq){
					colHead.attr("data-check-all", '0');
					colFlag = 1;
				}

				//Row Calculations
				var currentRow = $(this);
				currentRow.find("td").each(function(){
					if(!$(this).hasClass("no-lesson") && !$(this).find('.lesson-toggle').prop('checked') && !higherScopeReq){
						currentRow.find('th').attr('data-check-all', '0');
						rowFlag = true;
					}
				});
				if(!rowFlag && !higherScopeReq){
					currentRow.find('th').attr('data-check-all', '1');
				}
			});

			if(colFlag == 0 && !higherScopeReq){
				colHead.attr("data-check-all", '1');
			}

			if(!$('.subscriptions').hasClass('subs_only') && $(".lesson-toggle:checked").length == 0 && $(".subscriptions input[type=checkbox]:checked").length == 0){
				document.getElementById('add-to-cart').disabled = true;
			}else if($('.subscriptions').hasClass('subs_only') && $(".subscriptions input[type=checkbox]:checked").length == 0){
				document.getElementById('add-to-cart').disabled = true;
			}else if($('.subscriptions').hasClass('subs_only') && $(".lesson-toggle:checked").length == 0){
				document.getElementById('add-to-cart').disabled = true;
			}else{
				document.getElementById('add-to-cart').disabled = false;
			}

		});

		$('.sessions .lesson:not(.sold-out)').on('change', '.participants input[type=checkbox]', function() {
			var checkbox = $(this);
			var lesson = checkbox.closest('.lesson');
			var lessonID = lesson.attr("data-lessonID");
			var lesson_date1 = lesson.attr('data-date');
			var d = new Date(lesson_date1);
			var newDateArray = d.getWeek()+'-'+d.getFullYear();
			var status = true;
			var status1 = true;
			var lesson_date = lesson_date1;

			var block = checkbox.closest('table');
			var participantID = checkbox.val();

			if ($(this).is(':checked')) {
				if($(".subscriptions").length > 0){
					$("#select-participants input[type=checkbox]:checked").each(function () {
						var pid = $(this).val();
						if(pid == participantID){
							if($('input[name="subscriptions['+participantID+']"]:checked').length > 0){
								var subID = $('input[name="subscriptions['+participantID+']"]:checked').val();
								var now = $('#now-'+participantID+'-'+subID).val();
								var cut_off = $('#cut_off-'+participantID+'-'+subID).val();
								if(now != 0 && now != ""){
									var dateArray = [];
									var cnt = 0;
									$(".participants input[type=checkbox]:checked").each(function () {
										if($(this).val() == participantID){
											cnt++;
											var lesson = $(this).closest(".lesson");
											var lesson_date = lesson.attr('data-date');
											var d = new Date(lesson_date);
											dateArray.push(d.getWeek()+'-'+d.getFullYear());
										}
									});
									var cnt1 = 0;
									if(cnt > (now)){
										for(i=0; i<dateArray.length; i++){
											if(dateArray[i] == newDateArray){
												cnt1++;
											}
										}
									}
									if(cnt1 > (now)){
										status = false;
									}
								}
								var already_in_cart = $('#already_in_cart').val();
								var initialFlag = $('#initialFlag').val();
								if(already_in_cart == 1 && initialFlag == 1){
									var cut_off_array = [];
									if(cut_off != "" && cut_off != null && cut_off != 0 && status == true){
										var currentdate = new Date();
										var month = currentdate.getMonth()+1;
										var date = currentdate.getDate();
										cut_off_array.push(currentdate.getFullYear()+'-'+(month < 10 ?'0'+month:month)+'-'+(date < 10 ?'0'+date:date))
										for(var i=1; i<=cut_off; i++){
											var today = currentdate.getTime();
											var curr = new Date(today+(86400000*i));
											var month = curr.getMonth()+1;
											var date = curr.getDate();
											cut_off_array.push(curr.getFullYear()+'-'+(month < 10 ?'0'+month:month)+'-'+(date < 10 ?'0'+date:date));
										}
										for(w=0; w<cut_off_array.length; w++){
											if(lesson_date1 == cut_off_array[w]){
												status1 = false;
											}
										}
									}
								}
							}else{
							}
						}
					});
				}
			}else{
				if($(".subscriptions").length > 0){
					$("#select-participants input[type=checkbox]:checked").each(function () {
						var pid = $(this).val();
						if(pid == participantID){
							if($('input[name="subscriptions['+participantID+']"]:checked').length > 0){
								var subID = $('input[name="subscriptions['+participantID+']"]:checked').val();
								var now = $('#now-'+participantID+'-'+subID).val();
								var cut_off = $('#cut_off-'+participantID+'-'+subID).val();
								var initialFlag = $('#initialFlag').val();
								var already_in_cart = $('#already_in_cart').val();
								if(already_in_cart == 1 && initialFlag == 1){
									var cut_off_array = [];
									if(cut_off != "" && cut_off != null && cut_off != 0 && status == true){
										var currentdate = new Date();
										var month = currentdate.getMonth()+1;
										var date = currentdate.getDate();
										cut_off_array.push(currentdate.getFullYear()+'-'+(month < 10 ?'0'+month:month)+'-'+(date < 10 ?'0'+date:date))
										for(var i=1; i<=cut_off; i++){
											var today = currentdate.getTime();
											var curr = new Date(today+(86400000*i));
											var month = curr.getMonth()+1;
											var date = curr.getDate();
											cut_off_array.push(curr.getFullYear()+'-'+(month < 10 ?'0'+month:month)+'-'+(date < 10 ?'0'+date:date));
										}
										for(w=0; w<cut_off_array.length; w++){
											if(lesson_date1 == cut_off_array[w]){
												status1 = false;
											}
										}
									}
								}
							}else{
							}
						}
					});
				}
			}

			if(status == false && status1 == true){
				$('#lessons_'+lessonID+lesson_date+participantID).parent().addClass("disabled");
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".checked").hide();
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".unchecked").hide();
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".fa-ban").attr("style","display:inline-block");
				var txt = $('#lessons_'+lessonID+lesson_date+participantID).parent().find(".name").text();
				var txt1 = txt.replace("(Weekly Session Limit Reached)","");
				var txt2 = txt1 + "(Weekly Session Limit Reached)";
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".name").text(txt2);
				$('#lessons_'+lessonID+lesson_date+participantID).prop('checked',false);
				if (lesson.find('.lesson-toggle').is(":checked")) {
					lesson.find(".participants").show();
				}else{
					lesson.find(".participants").hide();
				}

			}else if(status1 == false){
				if($('#lessons_'+lessonID+lesson_date+participantID).is(":checked"))
					$('#lessons_'+lessonID+lesson_date+participantID).prop('checked',false);
				else
					$('#lessons_'+lessonID+lesson_date+participantID).prop('checked',true);
				var participantFlag = $('#participantFlag').val();
				if(participantFlag == 0){
					if (lesson.find('.lesson-toggle').is(":checked")) {
						lesson.find('.lesson-toggle').prop('checked', false);
						lesson.find(".participants").hide();
					} else {
						lesson.find('.lesson-toggle').prop('checked', true);
						lesson.find(".participants").show();
					}
				}
				calc_lesson(lesson);
			}else{
				if (lesson.find('.lesson-toggle').is(":checked")) {
					lesson.find(".participants").show();
				}else{
					lesson.find(".participants").hide();
				}

				$('#lessons_'+lessonID+lesson_date+participantID).parent().removeClass("disabled");
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".checked").removeAttr("style");
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".unchecked").removeAttr("style");
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".unchecked").removeAttr("style");
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".fa-ban").hide();
				var txt = $('#lessons_'+lessonID+lesson_date+participantID).parent().find(".name").text();

				var txt1 = txt.replace("(Weekly Session Limit Reached)","");
				$('#lessons_'+lessonID+lesson_date+participantID).parent().find(".name").text(txt1);

				if (checkbox.is(':checked')) {
					checkbox.closest('.lesson').attr("data-added_to", "true");
				}

				if (block.attr('data-require_all_sessions') == '1') {
					var selector = 'input[type=checkbox][value=' + participantID + ']';
					block.find(selector).each(function() {
						$(this).prop('checked', checkbox.prop('checked'));
						var lesson = $(this).closest('.lesson');
						calc_lesson(lesson); //Recalculate lesson before checking lesson participant count (otherwise the response will not reflect any additions made).
						if (lesson_participant_count(lesson, true) > 0) {
							lesson.find('.lesson-toggle').prop('checked', true);
							lesson.find('.participants').show();
						} else {
							lesson.find('.lesson-toggle').prop('checked', false);
							lesson.find('.participants').hide();
						}
					});
				}

				var lesson = checkbox.closest('.lesson');
				calc_lesson(lesson);
				if (should_calc_totals) {
					calc_totals();
				}
				if($('.subscriptions').hasClass('subs_only')){
					if($('input[name="subscriptions['+participantID+']"]:checked').length > 0){
						if($(".sessions").find(".participants").find("input[type='checkbox'][value='"+participantID+"']:checked").length == 0){
							document.getElementById('add-to-cart').disabled = true;
						}else{
							document.getElementById('add-to-cart').disabled = false;
						}
					}
				}
			}
			$('#participantFlag').val(1);
			$('#initialFlag').val(1);
		});
	}

	if ($('body').hasClass('cart') || $('body').hasClass('checkout')) {
		$('.cart .remove, .checkout .remove').click(function() {
			return confirm('Are you sure?');
		});
	}

	function toggle_payment_fields() {
		if ($('#payment_amount').val() > 0) {
			$('.card_payment_fields:hidden').slideDown();
			$('input[type=submit]').val('Book & Pay');
		} else {
			$('.card_payment_fields:visible').slideUp();
			$('input[type=submit]').val('Book');
		}
	}

	if ($('body').hasClass('checkout')) {
		$('.submit-checkout').click(function(e) {
			e.preventDefault();
			$('#checkout').trigger('submit');
			return false;
		});

		if ($('#payment_amount').length > 0) {

			toggle_payment_fields();
			$('#payment_amount').change(function() {
				toggle_payment_fields();
			});
		}

		function toggle_childcarevoucher() {
			if ($('#childcarevoucher').is(':checked')) {
				$('#childcarevoucher_providerID').attr('required', 'required');
				$('#childcarevoucher_details:hidden').slideDown();
				$('#payment_amount').attr('min', '0');
				$('#payment_amount').val('0');
			} else {
				$('#childcarevoucher_providerID').removeAttr('required');
				$('#childcarevoucher_details:visible').slideUp();
				$('#payment_amount').attr('min', $('#payment_amount').attr('data-min-payment'));
				$('#payment_amount').val($('#payment_amount').attr('data-default-value'));
			}
			toggle_payment_fields();
		}
		toggle_childcarevoucher();
		$('#childcarevoucher').change(function() {
			toggle_childcarevoucher();
		});

		// toggle showing info notices
		$('#childcarevoucher_providerID').change(function() {
			$('.notices .notice:visible').slideUp();
			$('.notices .notice[data-provider="' + $(this).val() + '"]:hidden').slideDown();
		});
		$('.notices .notice[data-providerID="' + $('#childcarevoucher_providerID').val() + '"]:hidden').slideDown();

		function toggle_source_other() {
			if ($('#source').val() == 'Other') {
				$('#source_other').closest('.form-group:hidden').slideDown();
				$('#source_other').attr('required', 'required');
			} else {
				$('#source_other').closest('.form-group:visible').slideUp();
				$('#source_other').removeAttr('required');
			}
		}
		toggle_source_other();
		$('#source').change(function() {
			toggle_source_other();
		});

		$('.checkout-btn').click(function(e) {
			e.preventDefault();
			$('form#checkout').submit();
		});

		if ($('#payment_method').length > 0) {
			function toggle_payment_method() {
				var selected = $('#payment_method').val();
				$('.method_fields:not(.' + selected + '):visible').slideUp();
				$('.method_fields.' + selected + ':hidden').slideDown();
			}
			toggle_payment_method();
			$('#payment_method').change(function() {
				toggle_payment_method();
			});
		}
	}

	// new school
	if ($('input[name="add_school"]').length == 1) {
		function toggle_add_school(anim_length) {
			if ($('input[name="add_school"]').val() == 1) {
				$('#orgID:visible').closest('.form-group').slideUp(anim_length);
				$('#new_school:hidden').closest('.form-group').slideDown(anim_length);
			} else {
				$('#orgID:hidden').closest('.form-group').slideDown(anim_length);
				$('#new_school:visible').closest('.form-group').slideUp(anim_length);
			}
		}
		toggle_add_school(0);
		$('.new-school').click(function(e) {
			e.preventDefault();
			if ($('input[name="add_school"]').val() == 1) {
				$('input[name="add_school"]').val(0);
			} else {
				$('input[name="add_school"]').val(1);
			}
			toggle_add_school(500);
		});
	}

	// lightbox
	$('a.lightbox').magnificPopup({
		type: 'iframe'
	});

	$("#cancel-subscription").click(function () {
		$('#myModal').modal("show");
	});

	$("#Yesbutton").click(function () {
		$('#myModal').modal("hide");
		window.location.href = $("#cancel-subscription").data("redirect");
	});

});

// calculates count of participants on a lesson from array set in calc_lesson
function lesson_participant_count(lesson, inc_subscriptions) {
	var lessonID = lesson.attr('data-lessonID');
	var lesson_date = lesson.attr('data-date');
	var participants = 0;
	if (inc_subscriptions) {
		if (lesson_participants.hasOwnProperty(lessonID) && lesson_participants[lessonID].hasOwnProperty(lesson_date)) {
			participants = parseInt(lesson_participants[lessonID][lesson_date]);
		}
	} else {
		if (lesson_participants_exc_subs.hasOwnProperty(lessonID) && lesson_participants_exc_subs[lessonID].hasOwnProperty(lesson_date)) {
			participants = parseInt(lesson_participants_exc_subs[lessonID][lesson_date]);
		}
	}
	return participants;
}

function select_all_sessions(){
	//Subscription Validation
	if($(".subscriptions").length > 0){
		$("#select-participants input[type=checkbox]:checked").each(function () {
			var participantID = $(this).val();
			if($('input[name="subscriptions['+participantID+']"]:checked').length > 0){
				var check_flag = false;
				var cross_check_lesson_id;
				$(".lesson").each(function() {
					if($(this).find("input[type='checkbox'][value='"+participantID+"']:checked").length > 0 && check_flag === false){
						check_flag = true;
						cross_check_lesson_id = $(this).attr("data-lessonid");
					}
					if(check_flag){
						var lessonId = $(this).attr("data-lessonid");
						if(lessonId === cross_check_lesson_id){
							$(this).find(".lesson-toggle").prop("checked", true);
							$(this).find(".participants").find("input[type='checkbox']").each(function(){
								if($(this).val() === participantID){
									$(this).closest('.participants').show();
									$(this).prop("checked", true);
								}
							});
						}
					}
				});
			}
		});
	}
}

function show_error(){
	$("#msg").html('Please select a subscription at the top of the page');
	$('#myModal_message').modal("show");
	setTimeout(function(){
		$('#myModal_message').modal("hide");
	},5000);
}
