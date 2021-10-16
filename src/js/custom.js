function sortMeBy(arg, sel, elem, order) {
	var $selector = sel,
		$element = $selector.children(elem);

	$element.sort(function(a, b) {
		var an = parseFloat(a.getAttribute(arg)),
		bn = parseFloat(b.getAttribute(arg));

		if (order == 'asc') {
			if (an > bn)
				return 1;
			if (an < bn)
				return -1;
		} else if (order == 'desc') {
			if (an < bn)
				return 1;
			if (an > bn)
				return -1;
		}
		return 0;
	});

	$element.detach().appendTo($selector);
}

function containsAny(str, substrings) {
	for (var i = 0; i != substrings.length; i++) {
		var substring = substrings[i];
		if (str.indexOf(substring) != - 1) {
			return substring;
		}
	}
	return null;
}

function fixed_scrollbar() {
	$(".fixed-scrollbar").each(function() {
		let fixed_scroll = $(this), element = $($(this).next());
		if (element!==undefined) {
			let width = element.get(0).scrollWidth;
			if (width>Math.ceil(fixed_scroll.width())) {
				fixed_scroll.css("height","16px").html("<div style='width: "+width+"px;'></div>");
				fixed_scroll.scroll(function () {
					element.scrollLeft(fixed_scroll.scrollLeft());
				});
				element.scroll(function () {
					$(fixed_scroll).scrollLeft(element.scrollLeft());
				});
			}
			else {
				fixed_scroll.css("height",0);
			}
		}
	});
}

// bulk actions on lessons
function handle_bulk_subsections(section) {
	$('.bulk-supplementary.bulk-supplementary-subsection').hide();
	if (section != '') {
		$('.bulk-supplementary.bulk-supplementary-subsection.' + section).show();
	}
}

function handle_bulk_supplementary() {
	$('.bulk-supplementary').hide();
	var action = $('#action').val();

	if(action == "times"){
		$('.bulk-supplementary.' + action).show();
	} else if (action != '') {
		let select = $('.bulk-supplementary.' + action).show().find("select");
		if (action=='removestaff') {
			let selections = $(".bulk-checkboxes tbody").find("td:first-child input[type='checkbox']:checked").map(function() { return $(this).val(); }).get().join();
			select.empty();
			if (selections.length!=0) {
				$.ajax({
					url: select.data('dynamic-list-url')+"/"+encodeURIComponent(selections),
					type: 'GET',
					success: function(data) {
						data = JSON.parse(data);
						$.each(data, function(key,value) {
							select.append($("<option></option>").attr("value", value.staffID).text(value.first + " " + value.surname));
						});
					}
				});
			}
		}
		if(action == 'staff'){
			var mindate = null;
			var maxdate = null;
			$('#scroll-table .datepicker').each(function() {
				$(this).datepicker("destroy");
			})
			$(".bulk-checkboxes tbody input:checkbox:checked").each(function(){
				if($(this).data('start-date') != ''){
					if(mindate != null){
						if(new Date($(this).data('start-date')) < new Date(mindate)){
							mindate = $(this).data('start-date');
						}
					}else{
						mindate = $(this).data('start-date');
					}
				}
				if($(this).data('end-date') != ''){
					if(maxdate != null){
						if(new Date($(this).data('end-date')) > new Date(maxdate)){
							maxdate = $(this).data('end-date');
						}
					}else{
						maxdate = $(this).data('end-date');
					}
				}
			});
			if(mindate != null){
				$('#from_date').val(moment(mindate).format('DD/MM/YYYY'));
			}else{
				mindate = $('#block_start_date').val();
				$('#from_date').val(moment(mindate).format('DD/MM/YYYY'));
			}
			if(maxdate != null){
				$('#to_date').val(moment(maxdate).format('DD/MM/YYYY'));
			}else{
				maxdate = $('#block_end_date').val();
				$('#to_date').val(moment(maxdate).format('DD/MM/YYYY'));
			}
			$('.datepicker').datepicker({
				dateFormat: 'dd/mm/yy',
				firstDay: 1,
				changeMonth: true,
				changeYear: true,
				yearRange: '-100:+10',
				maxDate: new Date(maxdate),
				minDate: new Date(mindate)
			});
		}
		select.val('').trigger('change');
	}
}

$(document).ready(function() {
	//Staff Checkall Checkboxes
	$('.chk-all').click(function() {
		var position= $(this).data("position");
		var status= $(this).data("status");
		if(status == "0"){
			$("input[name^='availability_info[1]["+(position - 1)+"]']").prop( "checked", true );
			$(this).data("status", "1");
		}else{
			$("input[name^='availability_info[1]["+(position - 1)+"]']").prop( "checked", false );
			$(this).data("status", "0");
		}
	});

	// select2
	$('.select2').select2();

	fixed_scrollbar();
	$( window ).resize(function() {
		fixed_scrollbar();
	});

	// cards
	$('.card').each(function() {
		var card_el = $(this);
		var card = new KTCard(this);
		if (this.querySelector("[data-card-tool=toggle]")) {
			$(card_el.children(".card-header")[0]).css("cursor","pointer").on("click", function (e) {
				if ($(e.target).is(".card-header, .card-title, .card-label, .card-toolbar, small")) {
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

	if ($(window).width() <= 1280) {
		$('body').addClass('aside-minimize');
	}


	// confirm deletions
	$('body').on('click', '.confirm-delete', function() {
		var name = 'this';
		var additional_message = '';
		var action = $(this).attr('href');

		if ($('.name', $(this).closest('tr')).length == 1) {
			name = '<strong>' + $.trim($('.name', $(this).closest('tr')).text()) + '</strong>';
            if ($(this).hasClass('remove-attachment')) {
                name = '<strong>' + $.trim($('.attachment-name', $(this).closest('tr')).text()) + '</strong>';
            }
		}

		var message_body = 'Are you sure you want to remove ' + name + '?';

		//Is a delete for an exception. Confirm that this will revert any exception refunds.
		if($(this).hasClass("exception")){
			message_body += "\n\nThis will also cancel any refunds that were issued when the exception was created.";
		}

		BootstrapDialog.show({
			title: 'Confirmation',
			message: message_body,
			buttons: [{
				label: 'Confirm Removal',
				cssClass: 'btn-success',
				action: function() {
					window.location = action;
				}
			}, {
				label: 'Cancel',
				//cssClass: 'btn-danger',
				action: function(dialogItself){
					dialogItself.close();
				}
			}]
		});

		return false;
	});


	// confirm deletions
	$('.confirm-forward').click(function() {
		var name = 'this';
		var action = $(this).attr('href');

		if ($('.name', $(this).closest('tr')).length == 1) {
			name = '<strong>' + $.trim($('.name', $(this).closest('tr')).text()) + '</strong>';
		}

		var message_body = 'Are you sure you want forward to support ' + name + '?';

		BootstrapDialog.show({
			title: 'Confirmation',
			message: message_body,
			buttons: [{
				label: 'Confirm Forward',
				cssClass: 'btn-success',
				action: function() {
					window.location = action;
				}
			}, {
				label: 'Cancel',
				//cssClass: 'btn-danger',
				action: function(dialogItself){
					dialogItself.close();
				}
			}]
		});

		return false;
	});

	// confirm duplicate
	$('.confirm-duplicate').click(function() {
		var name = 'this';
		var action = $(this).attr('href');

		if ($('.name', $(this).closest('tr')).length == 1) {
			name = '<strong>' + $.trim($('.name', $(this).closest('tr')).text()) + '</strong>';
		}

		var message_body = 'Are you sure you want to duplicate ' + name + '?';

		BootstrapDialog.show({
			title: 'Confirmation',
			message: message_body,
			buttons: [{
				label: 'Confirm Duplicate',
				cssClass: 'btn-success',
				action: function() {
					window.location = action;
				}
			}, {
				label: 'Cancel',
				//cssClass: 'btn-danger',
				action: function(dialogItself){
					dialogItself.close();
				}
			}]
		});

		return false;
	});

	$('a.confirm').click(function() {
		var action = $(this).attr('href');
		var message_body = 'Are you sure?';
		if ($(this).attr('data-message') != undefined && $(this).attr('data-message') != '') {
			message_body = $(this).attr('data-message');
		}

		BootstrapDialog.show({
			title: 'Confirmation',
			message: message_body,
			buttons: [{
				label: 'Confirm',
				cssClass: 'btn-success',
				action: function() {
					window.location = action;
				}
			}, {
				label: 'Cancel',
				//cssClass: 'btn-danger',
				action: function(dialogItself){
					dialogItself.close();
				}
			}]
		});

		return false;
	});

	$('input.confirm').click(function() {
		var element = $(this);
		// if just checked
		if (element.is(':checked')) {
			element.prop('checked', '');
			var message_body = 'Are you sure?';
			if ($(this).attr('data-message') != undefined && $(this).attr('data-message') != '') {
				message_body = $(this).attr('data-message');
			}

			BootstrapDialog.show({
				title: 'Confirmation',
				message: message_body,
				buttons: [{
					label: 'Confirm',
					cssClass: 'btn-success',
					action: function(dialogItself) {
						element.prop('checked', true);
						dialogItself.close();
						element.closest('form').submit();
						return true;
					}
				}, {
					label: 'Cancel',
					//cssClass: 'btn-danger',
					action: function(dialogItself){
						dialogItself.close();
					}
				}]
			});
		}
	});

	if ($('.sessions.in-crm .lesson').length>0) {
		$('button.btn.btn-primary.update-booking, button.btn.btn-primary.add-booking').on("click", function(event) {
			$('.sessions .lesson').each(function() {
				let selected = $(this).find(".participants input[type='checkbox']:checked").length;
				if (typeof $(this).data("available_excluding") === "undefined") { return; }

				if ((parseInt($(this).data("available_excluding"))-selected)<0 && $(this).data("available")!=="unlimited" && $(this).data("added_to")===true) {
					event.preventDefault();
					BootstrapDialog.show({
						title: 'Confirmation',
						message: "This session has reached the limit set in the target participant count, would you still like to proceed?",
						buttons: [{
							label: 'Yes',
							cssClass: 'btn-success',
							action: function(dialogItself) {
								dialogItself.close();
								$(event.currentTarget).closest("form").submit();
							}
						}, {
							label: 'No',
							action: function(dialogItself){
								dialogItself.close();
							}
						}]
					});
					return false;
				}
			});
		});
	}

	// names register
	if ($('table#names_register').length == 1) {
		var unsaved_changes = false;
		var saving = false;
		var storage_key = 'numbers-' + $('#names_register').attr('data-block');
		var register_type = $('#names_register').attr('data-registertype');

		localforage.getItem(storage_key, function(err, offline_data) {
			if (offline_data != null && offline_data != 'none') {
				if (confirm('Unsaved attendance data has been detected, would you like to load it?')) {
					attendance_data = offline_data;
					load_numbers_register();
					$('.save').removeClass('btn-primary').addClass('btn-success');
				}
			}
		});

		window.onbeforeunload = function(){
		   if (unsaved_changes) {
			   return 'Are you sure you want to leave this page? All unsaved changes will be lost.';
		   }
		}

		// on load
		$('#names_register tr.nodata td.loading').text('No data');
		load_numbers_register();
		function load_numbers_register() {
			$('#names_register tbody tr:not(.nodata)').remove();
			if (Object.keys(attendance_data).length > 0) {
				// load existing lesson
				for (var i in attendance_data) {
					create_row(attendance_data[i], i);
				}
				calc_shapeup_weight();
			}
		}

		function create_row(data, participantID) {
			var name = '';
			if (participantID === undefined) {
				participantID = 'new';
			}
			if (data !== undefined && data['name'] !== undefined) {
				name = data['name'];
			}
			var table_row = '<tr>';
			if (register_type == 'bikeability') {
				table_row += '<td class="center"><input type="checkbox"></td>';
			}
			table_row += '<td class="name"><input type="text" name="names[' + participantID + ']" value="' + name + '" class="form_control" max_length="100"></td>';
			for (var date in lesson_data) {
				for (var lesson_id in lesson_data[date]) {
					var attended = 0;
					if (data !== undefined && data['sessions'] !== undefined && data['sessions'][lesson_id] !== undefined && data['sessions'][lesson_id][date] !== undefined) {
						var button = '<a href="#" class="btn btn-success btn-xs toggle_attendance" title="Attended" data-participant="' + participantID + '"  data-lesson="' + lesson_id + '"  data-date="' + date + '" data-attended="1"><i class="far fa-check"></i></a>';
						attended = 1;
					} else {
						var button = '<a href="#" class="btn btn-warning btn-xs toggle_attendance" title="Not Attended" data-participant="' + participantID + '"  data-lesson="' + lesson_id + '"  data-date="' + date + '" data-attended="0"><i class="far fa-times"></i></a>';
					}
					table_row += '<td class="has_icon">' + button + '</td>';
					if (register_type == 'bikeability') {
						bikeability_val = null;
						if (data !== undefined && data['bikeability_levels'] !== undefined && data['bikeability_levels'][lesson_id] !== undefined && data['bikeability_levels'][lesson_id][date] !== undefined) {
							bikeability_val = data['bikeability_levels'][lesson_id][date];
						}
						table_row += '<td><select class="bikeability_level" data-participant="' + participantID + '"  data-lesson="' + lesson_id + '"  data-date="' + date + '"';
						if (attended != 1) {
							table_row += ' disabled="disabled"';
						}
						table_row += '><option value="">-</option>';
						for (var key in bikeability_levels) {
							table_row += '<option value="' + key + '"';
							if (bikeability_val == key) {
								table_row += ' selected="selected"';
							}
							table_row += '>' + key + ' ' + bikeability_levels[key] + '</option>';
						}
						table_row += '</select></td>';
					} else if (register_type == 'shapeup') {
						shapeup_val = null;
						if (data !== undefined && data['shapeup_weights'] !== undefined && data['shapeup_weights'][lesson_id] !== undefined && data['shapeup_weights'][lesson_id][date] !== undefined) {
							shapeup_val = data['shapeup_weights'][lesson_id][date];
						}
						table_row += '<td><input type="number" min="0" step=".1" class="shapeup_weight form-control" data-participant="' + participantID + '"  data-lesson="' + lesson_id + '"  data-date="' + date + '" value="' + shapeup_val + '"';
						if (attended != 1) {
							table_row += ' disabled="disabled"';
						}
						table_row += '></td>';
					}
				}
			}
			if (register_type == 'bikeability') {
				var bikeability_val = null;
				if (data !== undefined && data['bikeability_level'] !== undefined) {
					bikeability_val = data['bikeability_level'];
				}
				table_row += '<td><select class="bikeability_level_overall"><option value="">-</option>';
				for (var key in bikeability_levels_overall) {
					table_row += '<option value="' + key + '"';
					if (bikeability_val == key) {
						table_row += ' selected="selected"';
					}
					table_row += '>' + bikeability_levels_overall[key] + '</option>';
				}
				table_row += '</select></td>';
			}
			if (register_type == 'shapeup') {
				table_row += '<td class="target_loss_kg"></td>';
				table_row += '<td class="target_loss_lbs"></td>';
				table_row += '<td class="target_weight_kg"></td>';
				table_row += '<td class="target_weight_lbs"></td>';
				table_row += '<td class="current_loss_kg"></td>';
				table_row += '<td class="current_loss_lbs"></td>';
				table_row += '<td class="percent_lost"></td>';
			}
			for (var key in monitoring_fields) {
				var monitoring_val = '';
				if (data !== undefined && data['monitoring'] !== undefined && data['monitoring'][key] !== undefined && data['monitoring'][key] !== null) {
					monitoring_val = data['monitoring'][key];
				}
				table_row += '<td><input type="text" name="monitoring[' + key + ']" value="' + monitoring_val + '" data-monitoring="' + key + '" class="form_control" max_length="255"></td>';
			}
			table_row += '<td><a href="#" class="btn btn-danger btn-xs remove-row" title="Remove"><i class="far fa-trash"></i></a></td></tr>';
			$('#names_register tbody').append(table_row);
			$('#names_register tr.nodata').hide();
			$('#names_register th input[type=checkbox]').removeAttr('checked');
			if ($('.responsive-table').hasClass('fixed-1')) {
				$('#names_register tbody tr:last-child').find('th:first-child, td:first-child').css({height: $('#names_register tbody tr:last-child').outerHeight() + 'px'});
			} else if ($('.responsive-table').hasClass('fixed-2')) {
				$('#names_register tbody tr:last-child').find('th:first-child, td:first-child, th:nth-child(2), td:nth-child(2)').css({height: $('#names_register tbody tr:last-child').outerHeight() + 'px'});
			}

			$('#names_register tbody tr:last-child input.shapeup_weight').change(function() {
				calc_shapeup_weight();
			});
		}

		// check if name changes
		$('#names_register').on("input", function() {
			$('.save').removeClass('btn-primary').addClass('btn-success');
			unsaved_changes = true;
			// remove listener, no need to check again
			$('#names_register').unbind("input");
		});

		// toggle attendance
		$('#names_register').on("click", ".toggle_attendance", function() {
			if (saving) {
				return false;
			}
			if ($(this).attr('data-attended') == '1') {
				$(this).attr('data-attended', '0');
				$(this).attr('title', 'Not Attended');
				$(this).removeClass('btn-success').addClass('btn-warning');
				$('i', this).removeClass('fa-check').addClass('fa-times');
				if (register_type == 'bikeability') {
					$(this).closest('td').next().find('select.bikeability_level').attr('disabled', 'disabled');
				}
				if (register_type == 'shapeup') {
					$(this).closest('td').next().find('.shapeup_weight').attr('disabled', 'disabled');
				}
			} else {
				$(this).attr('data-attended', '1');
				$(this).attr('title', 'Attended');
				$(this).removeClass('btn-warning').addClass('btn-success');
				$('i', this).removeClass('fa-times').addClass('fa-check');
				if (register_type == 'bikeability') {
					$(this).closest('td').next().find('select.bikeability_level').removeAttr('disabled');
				}
				if (register_type == 'shapeup') {
					$(this).closest('td').next().find('.shapeup_weight').removeAttr('disabled');
				}
			}
			unsaved_changes = true;
			$('.save').removeClass('btn-primary').addClass('btn-success');
			return false;
		});

		// add new row
		$('.add-row').click(function() {
			if (saving) {
				return false;
			}
			create_row();
			unsaved_changes = true;
			$('.save').removeClass('btn-primary').addClass('btn-success');
			return false;
		});

		// remove row
		$('#names_register').on("click", ".remove-row", function() {
			if (saving) {
				return false;
			}
			var message = 'Are you sure you want to delete ';
			if ($('.name input', $(this).closest('tr')).val() != '') {
				message += $('.name input', $(this).closest('tr')).val();
			} else {
				message += 'this';
			}
			message += '?';
			if (confirm(message)) {
				$(this).closest('tr').remove();
				if ($('#names_register tbody tr:not(.nodata)').length == 0) {
					$('#names_register tr.nodata').show();
				}
			}
			unsaved_changes = true;
			$('.save').removeClass('btn-primary').addClass('btn-success');
			return false;
		});

		// save
		$('.save').click(function() {
			var save_data = [];
			if (saving) {
				return false;
			}
			saving = true;
			$(this).text('Saving').attr('disabled', 'disabled');
			$('.add-row').attr('disabled', 'disabled');
			$('#names_register input').attr('readonly', 'readonly');
			$('#names_register tbody tr:not(.nodata)').each(function() {
				var row_data = {
					name: $('td.name input[name^=name]', this).val(),
					monitoring: {},
					sessions: {},
					shapeup_weights: {},
					bikeability_levels: {},
					bikeability_level: null
				}
				$('[data-monitoring]', this).each(function() {
					var monitoring_id = $(this).attr('data-monitoring');
					row_data['monitoring'][monitoring_id] = $(this).val();
				});
				$('[data-attended=1]', this).each(function() {
					var lesson_id = $(this).attr('data-lesson');
					var lesson_date = $(this).attr('data-date');
					if (row_data['sessions'][lesson_id] === undefined) {
						row_data['sessions'][lesson_id] = {};
					}
					row_data['sessions'][lesson_id][lesson_date] = 1;
				});
				if (register_type == 'bikeability') {
					$('select.bikeability_level', this).each(function() {
						var lesson_id = $(this).attr('data-lesson');
						var lesson_date = $(this).attr('data-date');
						if (row_data['bikeability_levels'][lesson_id] === undefined) {
							row_data['bikeability_levels'][lesson_id] = {};
						}
						row_data['bikeability_levels'][lesson_id][lesson_date] = $(this).val();
					});
					row_data['bikeability_level'] =	$('select.bikeability_level_overall', this).val();
				} else if (register_type == 'shapeup') {
					$('.shapeup_weight', this).each(function() {
						var lesson_id = $(this).attr('data-lesson');
						var lesson_date = $(this).attr('data-date');
						if (row_data['shapeup_weights'][lesson_id] === undefined) {
							row_data['shapeup_weights'][lesson_id] = {};
						}
						row_data['shapeup_weights'][lesson_id][lesson_date] = $(this).val();
					});
				}
				save_data.push(row_data);
			});
			// if no save date, send something
			if (save_data.length == 0) {
				save_data = 'none';
			}
			// save copy locally
			localforage.setItem(storage_key, save_data, function() {
				$.ajax({
					url: window.location,
					type: 'POST',
					data: {
						save_data: save_data,
						csrf_token: $('input[name=csrf_token]').val()
					},
					timeout: 5000,
					success: function(data) {
						if (data == 'OK') {
							unsaved_changes = false;
							// delete local copy
							localforage.setItem(storage_key, null, function() {
								// no problems, reload
								window.location = window.location;
							});
						} else {
							// error
							alert('Error saving data, please try again.');
							saving = false;
							$('.save').text('Save').removeAttr('disabled');
							$('.add-row').removeAttr('disabled');
							$('#names_register input').removeAttr('readonly');
						}
					}, error: function(x, t, m) {
						if(t==="timeout") {
							// load message in div
							alert('You appear to be offline. Come back when you are back online and your data will be waiting.');
						} else {
							// misc error
							alert('You appear to be offline. Come back when you are back online and your data will be waiting.');
						}
						saving = false;
						$('.save').text('Save').removeAttr('disabled');
						$('.add-row').removeAttr('disabled');
						$('#names_register input').removeAttr('readonly');
					}
				});
			});
			return false;
		});
	}

	// bikeability register bulk actions
	$('#names_register, #participants_overview').find('select.bulk').change(function() {
		var val = $(this).val();
		var col_index = $(this).closest('td').index();
		var checked_rows = $(this).closest('table').find('tbody tr:has(input[type=checkbox]:checked)');
		if (checked_rows.length == 0) {
			alert('Select at least one row first');
			return;
		}
		if (val == '') {
			return;
		} else if (val == 'remove') {
			$(checked_rows).each(function() {
				$('td', this).eq(col_index).find('select').val('').trigger('change');
			});
		} else {
			$(checked_rows).each(function() {
				$('td', this).eq(col_index).find('select').val(val).trigger('change');
				var attendance_toggle = $('td', this).eq(col_index-1).find('a').not('[class*=btn-success]');
				if (attendance_toggle.length == 1) {
					attendance_toggle.click();
				}
			});
		}
		// set back to default
		$(this).val('');
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

	// if only allowing selection of date in future
	$('.datepicker-future').datepicker('option', 'minDate', '0');

	// if only allowing dates in the past
	$('.datepicker-past').datepicker('option', 'maxDate', '0');

	// min date
	$('.datepicker[data-mindate]').each(function() {
		var mindate =  $(this).attr('data-mindate').split("-");
		$(this).datepicker('option', 'minDate', new Date(mindate[0], mindate[1] - 1, mindate[2]));
	});

	// min date
	$('.datepicker[data-maxdate]').each(function() {
		var maxdate =  $(this).attr('data-maxdate').split("-");
		$(this).datepicker('option', 'maxDate', new Date(maxdate[0], maxdate[1] - 1, maxdate[2]));
	});

	// show only 1 day
	$('.datepicker[data-onlyday]').each(function() {
		var onlyday = $(this).attr('data-onlyday');
		var day = 1;
		if (onlyday == 'tuesday') {
			day = 2;
		} else if (onlyday == 'wednesday') {
			day = 3;
		} else if (onlyday == 'thursday') {
			day = 4;
		} else if (onlyday == 'friday') {
			day = 5;
		} else if (onlyday == 'saturday') {
			day = 6;
		} else if (onlyday == 'sunday') {
			day = 7;
		}

		$(this).datepicker('option', 'beforeShowDay',  function (date)	{
			return [date.getDay() == day, ''];
		});
	});

	// colour picker
	$(".colorpicker").colorpicker();
	/*
	// insert default text
	$('.insert-default').click(function() {
		var text = $(this).attr("data-default");

		if (text !== '') {
			$('input, textarea', $(this).closest('.form-group')).val(text);
		}

		return false;
	});
	*/

	// timesheets
	if ($('table#timesheet').length == 1) {
		$('#timesheet tr.existing, #expenses tr.existing, #mileage tr.existing, #fuel_card_mileage tr.existing').each(function() {
			if ($('input[type=hidden]', $(this).closest('tr')).val() == 1) {
				$('.readonly, a.edit', $(this).closest('tr')).hide();
				$('div.edit, a.cancel', $(this).closest('tr')).show();
			};
			$('a.edit', this).click(function() {
				$('.readonly, a.edit', $(this).closest('tr')).hide();
				$('div.edit, a.cancel', $(this).closest('tr')).show();
				$('input[type=hidden]', $(this).closest('tr')).val(1);
				return false;
			});
			$('a.cancel', this).click(function() {
				$('div.edit, a.cancel', $(this).closest('tr')).hide();
				$('.readonly, a.edit', $(this).closest('tr')).show();
				$('input[type=hidden]', $(this).closest('tr')).val(0);
				return false;
			});
		});

		$('#timesheet, #expenses, #mileage').on("click", "a.remove", function() {
			// if user editable, store for processing in hidden field
			var item_id = $(this).closest('tr').attr('data-id');
			if (item_id != '') {
				var remove_field = 'deleted_items';
				if ($(this).closest('table').attr('id') == 'expenses') {
					remove_field = 'deleted_expenses';
				}
				if ($(this).closest('table').attr('id') == 'mileage') {
					remove_field = 'deleted_mileage';
				}
				$('input[name=' + remove_field + ']').val($('input[name=' + remove_field + ']').val() + item_id + ',');
			}
			$(this).closest('tr').remove();
			return false;
		});
		$('#timesheet, #expenses, #mileage').on("click", "a.clear", function() {
			$(this).closest('tr').find('input').val('');
			$(this).closest('tr').find('select').val('').select2('val', '');
			return false;
		});
		$('#timesheet').on("click", "a.add", function() {
			$('#timesheet tr.new select').each(function() {
				$(this).select2('destroy');
			});
			var $tr = $('#timesheet tr.new').eq(0);
			var $clone = $tr.clone();
			var $new_row_count = $('#timesheet tr.new').length;
			$clone.find('input, select').each(function() {
				$(this).attr('name', $(this).attr('name').replace('new_items[0]', 'new_items[' + $new_row_count + ']'));
				$(this).val('');
			});
			$clone.find('select').each(function() {
				$(this).val($("option:first", this).val());
			});
			$clone.find('select.end_time_h').val('08');
			$clone.find('.remove').css('display', 'inline-block');
			$clone.find('.clear').css('display', 'none');
			$('#timesheet').append($clone);
			$('#timesheet tr.new select').each(function() {
				$(this).select2();
			});
			return false;
		});
		$('#expenses').on("click", "a.add", function() {
			$('#expenses tr.new select').each(function() {
				$(this).select2('destroy');
			});
			var $tr = $('#expenses tr.new').eq(0);
			var $clone = $tr.clone();
			var $new_row_count = $('#expenses tr.new').length;
			$clone.find('input, select').each(function() {
				$(this).attr('name', $(this).attr('name').replace('new_expenses[0]', 'new_expenses[' + $new_row_count + ']'));
				$(this).val('');
			});
			$clone.find('input[id]').each(function() {
				$(this).attr('id', $(this).attr('id').replace('new_expenses_0_', 'new_expenses_' + $new_row_count + '_'));
			});
			$clone.find('label[for]').each(function() {
				$(this).attr('for', $(this).attr('for').replace('new_expenses_0_', 'new_expenses_' + $new_row_count + '_'));
			});
			$clone.find('select').each(function() {
				$(this).val($("option:first", this).val());
			});
			$clone.find('.remove').css('display', 'inline-block');
			$clone.find('.clear').css('display', 'none');
			$('#expenses').append($clone);
			$('#expenses tr.new select').each(function() {
				$(this).select2();
			});
			return false;
		});

		$('#mileage').on("click", "a.add", function() {
			$('#mileage tr.new select').each(function() {
				$(this).select2('destroy');
			});
			$('#mileage tr.new .hasDatepicker').each(function() {
				$(this).datepicker('destroy');
			});
			var $tr = $('#mileage tr.new').eq(0);
			var $clone = $tr.clone();
			var $new_row_count = $('#mileage tr.new').length;
			$clone.find('input, select').each(function() {
				$(this).attr('name', $(this).attr('name').replace('new_mileage[0]', 'new_mileage[' + $new_row_count + ']'));
				$(this).val('');
			});
			$clone.find('select').each(function() {
				$(this).val($("option:first", this).val());
			});
			$clone.find('.remove').css('display', 'inline-block');
			$clone.find('.clear').css('display', 'none');
			$('#mileage').append($clone);
			$('#mileage tr.new select').each(function() {
				$(this).select2();
			});

			$('.datepicker').datepicker({
				dateFormat: 'dd/mm/yy',
				firstDay: 1,
				changeMonth: true,
				changeYear: true,
				yearRange: '-100:+10'
			});

			return false;
		});

		$('#timesheet').on('change', 'select[name*=brand]', function() {
			$(this).closest('tr').attr('data-brand', $(this).val());
		});
		$('#timesheet').on('change', 'tr:not(.existing) select[name*=reason]', function() {
			$(this).closest('tr').attr('data-role', $(this).val());
		});

		function calc_timesheet_times() {
			var total_minutes = 0;
			var brand_minutes = [];
			$('#timesheet tbody tr').each(function() {
				if ($('input[name*="[edited]"]', this).val() == 1 || $(this).hasClass('new')) {
					if ($('select[name*="[date]"]', this).val() != '') {
						var start_minutes = parseInt($('select.start_time_h', this).val())*60 + parseInt($('select.start_time_m', this).val());
						var end_minutes = parseInt($('select.end_time_h', this).val())*60 + parseInt($('select.end_time_m', this).val());
					} else {
						var start_minutes = 0;
						var end_minutes = 0;
					}
				} else {
					if ($(this).attr('data-status') != 'declined') {
						var fieldArray = $('.start_time', this).text().split(':');
						var start_minutes = parseInt(fieldArray[0])*60 + parseInt(fieldArray[1]);
						var fieldArray = $('.end_time', this).text().split(':');
						var end_minutes = parseInt(fieldArray[0])*60 + parseInt(fieldArray[1]);
					} else {
						var start_minutes = 0;
						var end_minutes = 0;
					}
				}
				var extra_time = 0;
				if ($(this).attr('data-extra_time') > 0) {
					extra_time = parseInt($(this).attr('data-extra_time'));
				}
				var item_length = parseInt(end_minutes - start_minutes + extra_time);
				if (!isNaN(item_length)) {
					total_minutes += item_length;
					var brand_id = $(this).attr('data-brand');
					var role = $(this).attr('data-role');
					var reason = $('select.reason', this).val();
					if (brand_id != '') {
						if (brand_minutes[brand_id] == undefined) {
							brand_minutes[brand_id] = [];
						}
						if (brand_minutes[brand_id]['total'] == undefined) {
							brand_minutes[brand_id]['total'] = 0;
						}
						brand_minutes[brand_id]['total'] += item_length;
						if (role != '') {
							if (brand_minutes[brand_id][role] == undefined) {
								brand_minutes[brand_id][role] = 0;
							}
							brand_minutes[brand_id][role] += item_length;
						}
					}
				}
			});

			$('#totals .total_time strong').text(minutes_to_hhmm(total_minutes));
			$('#totals tbody tr').each(function() {
				var role_minutes = 0;
				if ($(this).hasClass('totals')) {
					$('td[data-brand]', this).each(function() {
						var minutes = 0;
						var brand_id = $(this).attr('data-brand');
						if (brand_minutes[brand_id] != undefined && brand_minutes[brand_id]['total'] != undefined) {
							minutes = brand_minutes[brand_id]['total'];
						}
						role_minutes += minutes;
						if (minutes == 0) {
							$(this).text('-');
						} else {
							$(this).text(minutes_to_hhmm(minutes));
						}
					});
				} else {
					var role = $(this).attr('data-role');
					$('td[data-brand]', this).each(function() {
						var minutes = 0;
						var brand_id = $(this).attr('data-brand');
						if (brand_minutes[brand_id] != undefined && brand_minutes[brand_id][role] != undefined) {
							minutes = brand_minutes[brand_id][role];
						}
						role_minutes += minutes;
						if (minutes == 0) {
							$(this).text('-');
						} else {
							$(this).text(minutes_to_hhmm(minutes));
						}
					});
				}
				if (role_minutes == 0) {
					$('.total', this).text('-');
				} else {
					$('.total', this).text(minutes_to_hhmm(role_minutes));
				}
			});
		}

		calc_timesheet_times();

		setInterval(function() {
			calc_timesheet_times();
		}, 500);

		function calc_timesheet_travel() {
			var home_postcode = $('#timesheet').attr('data-home');
			var origin = null;
			var destination = null;
			var via_location = null;
			var i = 1;

			var service = new google.maps.DistanceMatrixService;

			$('#timesheet tbody tr[data-postcode]').each(function() {
				var row = this;
				if (i == 1) {
					origin = home_postcode;
				}
				destination = $(row).attr('data-postcode');
				if (i == $('tbody tr[data-day="' + $(row).attr('data-day') + '"]').length) {
					//destination = home_postcode;
					i = 0;
				}
				//var time_bits = $('.time', row).text().split(":")
				//var departure_time = new Date(2016, 06, 17, time_bits[0], time_bits[1], 0);
				var departure_time = new Date();
				// TODO: modify from and to depending on day start/end
				// TODO: calc traffic depending on day
				//$('.calc', row).text(origin + ' to ' + destination + ' at ' + departure_time.getHours() + ':' + departure_time.getMinutes());
				$('.travel_time', row).attr('title', origin + ' to ' + destination);
				// work out
				if (origin == destination) {
					$('.travel_time', row).text('0 mi / 0 mins');
				} else {
					service.getDistanceMatrix({
						origins: [origin],
						destinations: [destination],
						travelMode: google.maps.TravelMode.DRIVING,
						unitSystem: google.maps.UnitSystem.IMPERIAL,
						avoidHighways: false,
						avoidTolls: false,
						drivingOptions: {
							departureTime: departure_time,
							trafficModel: google.maps.TrafficModel.BEST_GUESS
						}
					}, function(response, status) {
						if (status !== google.maps.DistanceMatrixStatus.OK) {
							$('.travel_time', row).text('Unknown');
							//alert('Error was: ' + status);
						} else {
							var results = response.rows[0].elements[0];
							if (results.status !== google.maps.DistanceMatrixStatus.OK) {
								$('.travel_time', row).text('Unknown');
								//alert('Error was: ' + results.status);
							} else {
								$('.travel_time', row).text(results.distance.text + ' / ' + results.duration.text);
							}
							//console.log(results);
						}
					});
				}
				origin = destination;
				i++;
			});

		}

		calc_timesheet_travel();
	}

	function minutes_to_hhmm(d) {
		d = Number(d);
		var h = Math.floor(d / 60);
		var m = Math.floor(d % 60);
		if (m >= 0) {
			var m_output = ('0' + m).slice(-2);
		} else {
			m = ('' + m).slice(1);
			h += 1;
			var m_output = ('0' + m).slice(-2);
		}
		if (h >= 0) {
			var h_output = ('0' + h).slice(-2);
		} else {
			h = ('' + h).slice(1);
			var h_output = '-' + ('0' + h).slice(-2);
		}
		return h_output + ':' + m_output;
	}

	var BASE_URL = '/';
	tinyMCE.baseURL = BASE_URL + 'dist/plugins/custom/tinymce';
	tinyMCE.suffix = '.min';
	tinymce.init({
		selector: '.wysiwyg',
		menubar: false,
		resize: true,
		min_height: 250,
		max_height: 350,
		branding: false,
		plugins: [
			'advlist autolink lists link anchor code autoresize paste'
		],
		toolbar: 'undo redo | bold italic underline | justifyleft justifycenter justifyright | bullist numlist | link unlink | removeformat code',
		contextmenu: false,
		content_css: [
			BASE_URL + 'public/dist/css/wysiwyg.css'
		],
		content_style: "body { font-weight: 400; font-size: 13px; color: #464E5F; }",
		document_base_url : BASE_URL,
		height: 250,
		autoresize_bottom_margin: 0
	});

	/*

	// slug autofill
	$("input#title").focus(function() {
		if ($("input#slug").val() == "") {
			// start monitoring
			$("input#title").keyup(function() {
				var val = $(this).val();
				val = jQuery.trim(val);
				val = val.toLowerCase();
				val = val.replace(/[^a-zA-Z- 0-9]+/g,'');
				val = val.replace(/\s+/g,'-');
				$("input#slug").val(val);
			});
		} else {
			// stop monitoring
			$("input#title").unbind("keyup");
		}
	});
	*/

	if ($('.step-pane, .account-participant, .account-holder').length>=1) {
		$("body").on('change', "select[name='gender'], select[name='religion'], select[name='sexual_orientation']", function(e) {
			let selectName = $(this).attr("name");
			let selected = $(this,'option:selected').val();
			if (selected==="please_specify") {
				$(this).siblings("input[name='"+selectName+"_specify']").removeClass("d-none");
			}
			else {
				$(this).siblings("input[name='"+selectName+"_specify']").addClass("d-none");
			}
		});
	}

	$('body').on("click", ".bulk-checkboxes th input[type='checkbox']", function(e) {
		e.stopImmediatePropagation();
		var target = $(this).closest('table');
		if ($(this).is(":checked")) {
			$("td input[type='checkbox']", target).prop('checked', true);
			$("td input[type='checkbox']", target).closest('td').addClass('selected');
			$('tfoot.bulk-actions:hidden').show();
		} else {
			$("td input[type='checkbox']", target).prop('checked', false);
			$("td input[type='checkbox']", target).closest('td').removeClass('selected');
			$('tfoot.bulk-actions:visible').hide();
		}
		if ($('.bulk-supplementary').length > 0) {
			handle_bulk_supplementary();
		}
	});

	$('body').on("click", ".bulk-checkboxes th:has(input[type='checkbox'])", function() {
		var target = $(this).closest('table');
		if ($("input", this).is(":checked")) {
			$("input", this).prop('checked', false);
			$("td input[type='checkbox']", target).prop('checked', false);
			$("td input[type='checkbox']", target).closest('td').removeClass('selected');
			$('tfoot.bulk-actions:visible').hide();
		} else {
			$("input", this).prop('checked', true);
			$("td input[type='checkbox']", target).prop('checked', true);
			$("td input[type='checkbox']", target).closest('td').addClass('selected');
			$('tfoot.bulk-actions:hidden').show();
		}
		if ($('.bulk-supplementary').length > 0) {
			handle_bulk_supplementary();
		}
	});

	$("body").on('click', ".bulk-checkboxes tbody td input[type='checkbox'], .bulk-checkboxes tbody td:has(input[type='checkbox'])", function(e) {
		e.stopImmediatePropagation();
		if (e.target.nodeName.toLowerCase() == 'input') {
			var checkbox = $(this);
			if (checkbox.is(':checked')) {
				checkbox.parent().addClass("selected");
			} else {
				checkbox.parent().removeClass("selected");
			}
		} else {
			var checkbox = $(this).find('input[type=checkbox]');
			if (checkbox.is(':checked')) {
				checkbox.prop('checked', false);
				checkbox.parent().removeClass("selected");
			} else {
				checkbox.prop('checked', true);
				checkbox.parent().addClass("selected");
			}
		}
		var target = $(this).closest('table');
		var total = $("td input[type='checkbox']", target).length;
		var checked = $("td input[type='checkbox']:checked", target).length;
		if ($(this).is(':checked')) {
			$('tfoot.bulk-actions:hidden').show();
		}
		if (checked == total) {
			$("th input[type='checkbox']", target).prop('checked', true);
		} else {
			$("th input[type='checkbox']", target).prop('checked', false);
		}
		if (checked == 0) {
			$('tfoot.bulk-actions:visible').hide();
		}
		if ($('.bulk-supplementary').length > 0) {
			handle_bulk_supplementary();
		}
	});

	if ($(".bulk-checkboxes td input[type='checkbox']").length > 0) {
		var total = $("td input[type='checkbox']").length;
		var checked = $("td input[type='checkbox']:checked").length;
		if (checked == total) {
			$("th input[type='checkbox']").prop('checked', true);
			$('tfoot.bulk-actions:hidden').show();
		} else {
			$("th input[type='checkbox']").prop('checked', false);
			$('tfoot.bulk-actions:visible').hide();
		}
	}

	$("table.availability td input, table#activities td input, table.checkbox-enable-td td input, table#fields td input").each(function() {
		if ($(this).is(':checked')) {
			$(this).parent().addClass("selected");
		} else {
			$(this).parent().removeClass("selected");
		}
	});

	$("table.availability td input, table#activities td input, table.checkbox-enable-td td input, table#fields td input").click(function(e) {
		if ($(this).attr('readonly') || $(this).attr('readonly')) {
			return false;
		}
		if ($(this).is(':checked')) {
			$(this).parent().addClass("selected");
		} else {
			$(this).parent().removeClass("selected");
		}
		e.stopPropagation();
	});

	$("table.availability td, table#activities td, table.checkbox-enable-td td, table#fields td").click(function() {
		if ($("input", this).prop('disabled')) {
			return false;
		}
		// if not clicking the time
		if ($("input", this).length == 1) {
			if ($("input", this).attr('readonly') || $("input", this).attr('readonly')) {
				return false;
			}
			if ($("input", this).is(':checked')) {
				$(this).removeClass("selected");
				$("input", this).prop('checked', false);
			} else {
				$(this).addClass("selected");
				$("input", this).prop('checked', true);
			}
		}
	});

	$("a.selectall").click(function() {
		$(this).closest('div').prev().find("input").prop('checked', true);
		$(this).closest('div').prev().find("input").parent().addClass('selected');
		return false;
	});

	$("a.selectmon").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(1) input").prop('checked', true);
		$(this).closest('div').prev().find("tr").find("td:eq(1) input").parent().addClass('selected');
		return false;
	});

	$("a.selecttue").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(2) input").prop('checked', true);
		$(this).closest('div').prev().find("tr").find("td:eq(2) input").parent().addClass('selected');
		return false;
	});

	$("a.selectwed").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(3) input").prop('checked', true);
		$(this).closest('div').prev().find("tr").find("td:eq(3) input").parent().addClass('selected');
		return false;
	});

	$("a.selectthu").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(4) input").prop('checked', true);
		$(this).closest('div').prev().find("tr").find("td:eq(4) input").parent().addClass('selected');
		return false;
	});

	$("a.selectfri").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(5) input").prop('checked', true);
		$(this).closest('div').prev().find("tr").find("td:eq(5) input").parent().addClass('selected');
		return false;
	});

	$("a.selectsat").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(6) input").prop('checked', true);
		$(this).closest('div').prev().find("tr").find("td:eq(6) input").parent().addClass('selected');
		return false;
	});

	$("a.selectsun").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(7) input").prop('checked', true);
		$(this).closest('div').prev().find("tr").find("td:eq(7) input").parent().addClass('selected');
		return false;
	});

	$("a.unselectall").click(function() {
		$(this).closest('div').prev().find("input").prop('checked', false);
		$(this).closest('div').prev().find("input").parent().removeClass('selected');
		return false;
	});

	$("a.unselectmon").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(1) input").prop('checked', false);
		$(this).closest('div').prev().find("tr").find("td:eq(1) input").parent().removeClass('selected');
		return false;
	});

	$("a.unselecttue").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(2) input").prop('checked', false);
		$(this).closest('div').prev().find("tr").find("td:eq(2) input").parent().removeClass('selected');
		return false;
	});

	$("a.unselectwed").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(3) input").prop('checked', false);
		$(this).closest('div').prev().find("tr").find("td:eq(3) input").parent().removeClass('selected');
		return false;
	});

	$("a.unselectthu").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(4) input").prop('checked', false);
		$(this).closest('div').prev().find("tr").find("td:eq(4) input").parent().removeClass('selected');
		return false;
	});

	$("a.unselectfri").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(5) input").prop('checked', false);
		$(this).closest('div').prev().find("tr").find("td:eq(5) input").parent().removeClass('selected');
		return false;
	});

	$("a.unselectsat").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(6) input").prop('checked', false);
		$(this).closest('div').prev().find("tr").find("td:eq(6) input").parent().removeClass('selected');
		return false;
	});

	$("a.unselectsun").click(function() {
		$(this).closest('div').prev().find("tr").find("td:eq(7) input").prop('checked', false);
		$(this).closest('div').prev().find("tr").find("td:eq(7) input").parent().removeClass('selected');
		return false;
	});

	$('#system_pay_rates').change(function() {
		if ($('#system_pay_rates').prop('checked')) {
			$('.system_pay_rates_text').show();
			$('#houry_rate').prop('checked', false);
			$('.hourly_rate_value_form').hide();
		} else {
			$('.system_pay_rates_text').hide();
		}
	});

	$('#houry_rate').change(function() {
		if ($('#houry_rate').prop('checked')) {
			$('#system_pay_rates').prop('checked', false);
			$('.system_pay_rates_text').hide();
		} else {
			$('.system_pay_rates_text').hide();
		}
	});
	/*
	var count_elements_left_old = 0;
	var count_elements_right_old = 0;
	function recountDynamicDivHeight() {
		if ($('div.dynamic-height').length > 0) {
			$('div.dynamic-height.left').each(function(){
				var count = 0;
				var height_left = 0;
				$('div.dynamic-height.left[area=' + $(this).attr('area') + '] > *').each(function () {
					//console.log('asdas', $(this));
					if ($(this).css('display') != 'none') {
						//console.log('elem', $(this).height());
						height_left += $(this).height();
						//console.log('l', height_left);
					}
				});
				var height_right = 0;
				$('div.dynamic-height.right[area=' + $(this).attr('area') + '] > *').each(function () {
					if ($(this).css('display') != 'none') {
						height_right += $(this).height();
						//console.log('r', height_right);
					}
				});

				var result_height = 0;
				if(height_right > height_left) {
					result_height = height_right + 'px';
				} else {
					result_height = height_left + 'px';
				}

				$('div.dynamic-height.right[area=' + $(this).attr('area') + ']').css('height', result_height);
				$('div.dynamic-height.left[area=' + $(this).attr('area') + ']').css('height', result_height);
			});
		}
	}
	*/
	// checkbox toggle
	$('input[data-togglecheckbox]').each(function() {
		var toggle_items = $(this).attr('data-togglecheckbox').split(' ');

		if ($(this).prop('checked') != true) {
			for (var i = toggle_items.length - 1; i >= 0; i--) {
				$('#' + toggle_items[i]).closest('.form-group').hide();
				// hide sub items if unchecked
				if ($('#' + toggle_items[i]).tagName == 'input' && $('#' + toggle_items[i]).attr('type') == 'checkbox' && $(this).prop('checked') != true) {
					var toggle_sub_items = $('#' + toggle_items[i]).attr('data-togglecheckbox').split(' ');
					for (var i = toggle_sub_items.length - 1; i >= 0; i--) {
						$('#' + toggle_sub_items[i]).closest('.form-group').hide();
					};
				}
			};
		}

		$(this).change(function() {
			if ($(this).prop('checked')) {
				for (var i = toggle_items.length - 1; i >= 0; i--) {
					$('#' + toggle_items[i]).closest('.form-group').show();
				};
			} else {
				for (var i = toggle_items.length - 1; i >= 0; i--) {
					$('#' + toggle_items[i]).closest('.form-group').hide();
				};
			}
			// hide sub items conditional on first toggle checkbox command
			for (var i = toggle_items.length - 1; i >= 0; i--) {
				if ($('#' + toggle_items[i]).tagName == 'input' && $('#' + toggle_items[i]).attr('type') == 'checkbox') {
					var toggle_sub_items = $('#' + toggle_items[i]).attr('data-togglecheckbox').split(' ');
					if ($(this).prop('checked') && $('#' + toggle_items[i]).prop('checked')) {
						for (var i = toggle_sub_items.length - 1; i >= 0; i--) {
							$('#' + toggle_sub_items[i]).closest('.form-group').show();
						};
					} else {
						for (var i = toggle_sub_items.length - 1; i >= 0; i--) {
							$('#' + toggle_sub_items[i]).closest('.form-group').hide();
						};
					}
				}
			}
			recountDynamicDivHeight();
		});
	});

	// select toggle other within form groups
	$('.form-group select[data-toggleother], .bulk-supplementary select[data-toggleother]').each(function() {
		var toggle_items = $(this).attr('data-toggleother').split(' ');

		if ($(this).val().toLowerCase().indexOf("other") == -1) {
			for (var i = toggle_items.length - 1; i >= 0; i--) {
				$('#' + toggle_items[i]).closest('div').hide();
			};
		}

		$(this).change(function() {
			if ($(this).attr('toggle-subsections') == 1) {
				handle_bulk_subsections($(this).val());
			} else {
                if ($(this).val().toLowerCase().indexOf("other") >= 0) {
                    for (var i = toggle_items.length - 1; i >= 0; i--) {
                        $('#' + toggle_items[i]).closest('div').show();

                    };
                } else {
                    for (var i = toggle_items.length - 1; i >= 0; i--) {
                        $('#' + toggle_items[i]).closest('div').hide();
                    };
                }
			}
		});
	});

	// select toggle other within table forms
	$('td select[data-toggleother]').each(function() {
		var toggle_items = $(this).attr('data-toggleother').split(' ');

		if ($(this).val().toLowerCase().indexOf("other") == -1) {
			for (var i = toggle_items.length - 1; i >= 0; i--) {
				$('#' + toggle_items[i]).hide();
			};
		}

		$(this).change(function() {
			if ($(this).val().toLowerCase().indexOf("other") >= 0) {
				for (var i = toggle_items.length - 1; i >= 0; i--) {
					$('#' + toggle_items[i]).show();

				};
			} else {
				for (var i = toggle_items.length - 1; i >= 0; i--) {
					$('#' + toggle_items[i]).hide();
				};
			}
		});
	});

	// toggle numbers only fields
	if ($('#register_type').length == 1) {
		toggle_register_type();

		$('#register_type').change(function() {
			toggle_register_type();
		});
	}

	function toggle_register_type() {
		var type = $('#register_type').val();

		if (type == 'numbers') {
			$('.hide-for-numbers:visible').slideUp();
		} else {
			$('.hide-for-numbers:hidden').slideDown();
		}

		if (type == 'numbers' || type == 'names' || type == 'bikeability' || type == 'shapeup') {
			$('.hide-for-numbers-and-names:visible').slideUp();
		} else {
			$('.hide-for-numbers-and-names:hidden').slideDown();
		}
	}

	// toggle fields based on account_status
	if ($('#account_status').length == 1) {
		toggle_account_status();

		$('#account_status').change(function() {
			toggle_account_status();
		});
	}

	function toggle_account_status() {
		var status = $('#account_status').val();
		$('.paid_until, .trial_until').hide();

		if (status == 'paid') {
			$('.paid_until').show();
		}

		if (status == 'trial') {
			$('.trial_until').show();
		}
	}

	// toggle fields based on staff address type
	if ($('#staff_addresstype').length == 1) {
		toggle_staff_addresstype();

		$('#staff_addresstype').change(function() {
			toggle_staff_addresstype();
		});
	}

	function toggle_staff_addresstype() {
		var type = $('#staff_addresstype').val();
		$('.non-emergency, .emergency').hide();

		if (type == 'emergency') {
			$('.emergency').show();
		} else {
			$('.non-emergency').show();
		}

		if (type == 'main') {
			$('#toM').closest('.form-group').hide();
		}
	}

	/*// toggle drivers declaration
	if ($('#driving_declaration').length == 1) {
		toggle_driving_declaration();

		$('#driving_mot, #driving_insurance').change(function() {
			toggle_driving_declaration();
		});
	}

	function toggle_driving_declaration() {
		var show = false;
		if ($('#driving_mot').prop('checked')) {
			show = true;
		}
		if ($('#driving_insurance').prop('checked')) {
			show = true;
		}
		if (show) {
			$('#driving_insurance').closest('fieldset').css('height', '160px');
			$('#driving_mot').closest('fieldset').css('height', '160px');
			$('#driving_declaration').closest('.form-group').show();
		} else {
			$('#driving_insurance').closest('fieldset').css('height', 'auto');
			$('#driving_mot').closest('fieldset').css('height', 'auto');
			$('#driving_declaration').closest('.form-group').hide();
		}
	}*/

	// invoices
	if ($('form#invoice').length == 1) {
		toggle_invoice_blocks();

		$('#type').change(function() {
			toggle_invoice_blocks();
		});

		$('#type,input[name^=blocks]').change(function() {
			calc_invoice();
		});

		$(document).on('click', 'a.recalc_invoice', function() {
			calc_invoice();
		});

		// if invoice info coming from existing invoice, show recalc button
		$('#invoice_info label').append(' (<a href="#" class="recalc_invoice">Recalculate?</a>)');
	}

	if ($('div.card.select-block').length==1) {
		$('div.card.select-block .nav-tabs-line').animate({scrollLeft: $('div.card.select-block .nav-tabs-line li.nav-item > .active').position().left}, 500);
	}

	function calc_invoice() {
		var post_data = {
			bookingID: parseInt($('#invoice').attr('data-booking')),
			type: $('#type').val(),
			'blocks[]' : []
		};
		$("input[name^=blocks]:checked").each(function() {
			post_data['blocks[]'].push($(this).val());
		});

		$('#invoice_info').html("Loading...");
		$('input[name=desc]').val('');
		$('#field_amount').val('');

		$.ajax({
			url: '/bookings/invoices/calc',
			type: 'POST',
			data: post_data,
			success: function(data) {
				if (data.info == 'ERROR') {
					// missing data
					$('#invoice_info').html('');
					$('input[name=desc]').val('');
					$('#field_amount').val('');
				} else {
					// ok
					$('#invoice_info').html(data.info);
					$('input[name=desc]').val(data.info);
					$('#field_amount').val(data.amount);
				}
			}
		});
	}

	function toggle_invoice_blocks() {
		$('label[for=blocks]').closest('.form-group').find('input').removeAttr('disabled').closest('.checkbox').attr('title', 'Not available due to multiple lesson types being used within the block');
		if ($('#type').val() == 'blocks' || $('#type').val() == 'contract pricing' || $('#type').val() == 'participants per session' || $('#type').val() == 'participants per block' || $('#type').val() == 'other') {
			$('label[for=blocks]').closest('.form-group').show();
			if ($('#type').val() == 'participants per block') {
				$('label[for=blocks]').closest('.form-group').find('input').filter(function() {
					return $(this).attr("data-type_count") > 1;
				}).attr('disabled', 'disabled').removeAttr('checked').closest('.checkbox').attr('title', 'Not available due to multiple lesson types within the block');
			}
		} else {
			$('label[for=blocks]').closest('.form-group').hide();
		}
	}

	// timetable
	var hidden_labels = new Array();

	$('.timetable-legend .label').click(function() {
		var toggle_label = $(this).attr('data-toggle');

		if ($(this).hasClass('label-disabled')) {

			// unhide
			var index = hidden_labels.indexOf(toggle_label);
			if (index > -1) {
				hidden_labels.splice(index, 1);
			}

			// show all
			$('.timetable .label').show();
			$(this).removeClass('label-disabled');

			// rehide hidden
			for (var i=0;i<hidden_labels.length;i++) {
				if (hidden_labels[i] != '') {
					$('.timetable .' + hidden_labels[i]).hide();
					$('.timetable-legend .' + hidden_labels[i]).addClass('label-disabled');
				}
			}

		} else {

			$('.timetable .' + toggle_label).hide();
			$(this).addClass('label-disabled');

			hidden_labels.push(toggle_label);
		}
		timetable_cookie();
		recalc_timetable_stats();
	});
	
	if($('.timetable-legend').length == 0){
		document.cookie = "timetable_filter=null;path=/";
	}
	
	$('.timetable-legend .label').dblclick(function() {
		var toggle_label = $(this).attr('data-toggle');

		$('.timetable .' + toggle_label).show();
		$(this).removeClass('label-disabled');

		var index = hidden_labels.indexOf(toggle_label);
		if (index > -1) {
			hidden_labels.splice(index, 1);
		}

		$('.label:not(.' + toggle_label + ')', $(this).closest('div')).each(function() {
			var label_to_hide = $(this).attr('data-toggle');
			$('.timetable .' + label_to_hide + ':not(.' + toggle_label + ')').hide();
			$(this).addClass('label-disabled');
			var index = hidden_labels.indexOf(label_to_hide);
			if (index > -1) {
				hidden_labels.splice(index, 1);
			}
			hidden_labels.push(label_to_hide);
		});
		timetable_cookie();
		recalc_timetable_stats();
	});
	
	function timetable_cookie() {
		var value = hidden_labels.join();
		document.cookie = "timetable_filter=" + value + ';path=/';
	}

	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}

	var timetable_labels = readCookie('timetable_filter');
	if (timetable_labels != null) {
		hidden_labels = timetable_labels.split(',');
		for (var i=0;i<hidden_labels.length;i++) {
			if (hidden_labels[i] != '') {
				$('.timetable .' + hidden_labels[i]).hide();
				$('.timetable-legend .' + hidden_labels[i]).addClass('label-disabled');
			}
		}
		recalc_timetable_stats();
	}
	
	function recalc_timetable_stats() {
		var lesson_count = parseInt($('.timetable .label:visible').length);
		$('.lesson_count').text(lesson_count);

		var lesson_seconds = 0;
		$('.timetable .label:visible').each(function() {
			lesson_seconds += parseInt($(this).attr('data-length'));
		});

		var hours = Math.floor(lesson_seconds / (60 * 60));
		var divisor_for_minutes = lesson_seconds % (60 * 60);
		var minutes = Math.floor(divisor_for_minutes / 60);

		$('.lesson_hours').text(hours + 'h' + minutes + 'm');

		// repeat for day count in columns
		for (var i = 2; i <= 8; i++) {
			var day_seconds = 0;
			$('.timetable td:nth-child(' + i + ') .label:visible').each(function() {
				day_seconds += parseInt($(this).attr('data-length'));
			});

			var hours = Math.floor(day_seconds / (60 * 60));
			var divisor_for_minutes = day_seconds % (60 * 60);
			var minutes = Math.floor(divisor_for_minutes / 60);

			$('.day_hours td:nth-child(' + i + ')').text(hours + 'h' + minutes + 'm');
		};
	}

	$('.timetable-legend #week, .timetable-legend #year').change(function() {
		var date = getDateRangeOfWeek($('#week').val(), $('#year').val());
		$('#field_start_from').datepicker('setDate', date[0]);
		$('#field_start_to').datepicker('setDate', date[1]);
		$('#year-value').val($('#year').val());
		$('#week-value').val($('#week').val());
        $('#search-form').submit();
	});

    $('.timetable-legend #timetable_view').change(function(e) {
        $('#view-value').val(this.value);

        var url = '/bookings/timetable?view=';
        if ($('#own-value').val() == 1) {
            var url = '/timetable?view=';
		}

        $('#search-form').attr('action', url + this.value);

        if (this.value != 'standard') {
            $('.date-filter').css('display', 'block');
		} else {
        	$('.date-filter').css('display', 'none');
		}

        $('#search-form').submit();

		$('#search-form').append('<input type="hidden" name="search" value="true">');
    });

    // qtip
    $(".timetable .label, .timetable .exceptions, form#lessons td[data-tooltip], table.availability_cal .item .label[data-tooltip], form#acceptance td[data-tooltip], table.evaluations td[data-tooltip]").qtip({
		content: {
			text: function() {
				return $('.' + $(this).attr("data-tooltip")).html();
			}
		},
		position: {
			target: 'mouse',
			viewport: $(window),
			adjust: {
				method: 'shift',
				x: 10,
				y: 10
			}
		}
	});

	$('.timetable .exceptions').click(function(){
		var url = $(this).attr('data-url');
		window.location.href= url;
	})

	// new booking
	if ($('form.booking').length == 1 && $("form.booking select#orgID").length == 1) {

		var contactIDClone = $('form.booking select#contactID').html();

		function updateBookingContacts() {

			// remove select2
			$("form.booking #contactID").select2('destroy');

			if ($("form.booking #orgID").val() == '') {
				$("form.booking select#contactID").html("<option value=\"\">Select customer first</option>");
			} else {
				//get type
				var org = $("form.booking #orgID").val();

				// set default
				$("form.booking #contactID").html("<option value=\"\">Select</option>");

				// replace with original
				$("form.booking #contactID").append(contactIDClone);

				// remove any which dont match
				$("form.booking #contactID option[data-org!='" + org + "']").remove();
			}

			// init select2
			$("form.booking #contactID").select2();

		}

		var addressIDClone = $('form.booking select#addressID').html();

		function updateBookingAddresses() {

			// remove select2
			$("form.booking #addressID").select2('destroy');

			if ($("form.booking #orgID").val() == '') {
				$("form.booking select#addressID").html("<option value=\"\">Select venue first</option>");
			} else {
				//get type
				var org = $("form.booking #orgID").val();

				// set default
				$("form.booking #addressID").html("<option value=\"\">Select</option>");

				// replace with original
				$("form.booking #addressID").append(addressIDClone);

				// remove any which dont match
				$("form.booking #addressID option[data-org!='" + org + "']").remove();
			}

			// init select2
			$("form.booking #addressID").select2();

		}

		// call on load and set up events
		if ($('#contactID').length == 1) {
			updateBookingContacts();
			$("form.booking #orgID").change(function() {
				updateBookingContacts();
				$('.venue-change-warning').slideDown();
			});
		} else if ($('#addressID').length == 1) {
			updateBookingAddresses();

			$("form.booking #orgID").change(function() {
				updateBookingAddresses();
				$('.venue-change-warning').slideDown();
			});
		}

		// default pricing
		$('form.booking #orgID, form.booking #brandID').change(function() {
			var orgID = $('form.booking #orgID').val();
			var brandID = $('form.booking #brandID').val();
			// if both filled in, get default pricing
			if (orgID != '' && brandID != '') {
				$.ajax({
					url: '/bookings/default_pricing',
					type: 'POST',
					data: {
						orgID: orgID,
						brandID: brandID
					},
					success: function(data) {
						if (data.result == 'OK') {
							if (Object.keys(data.pricing).length > 0) {
								for (var typeID in data.pricing) {
									$('input[data-prices-amount=' + typeID + ']').val(data.pricing[typeID]['amount']);
									if (data.pricing[typeID]['contract'] == 1) {
										$('input[data-prices-contract=' + typeID + ']').prop('checked', 'checked');
									} else {
										$('input[data-prices-contract=' + typeID + ']').removeAttr('checked');
									}
								}
							}
						}
					}
				});
			}
		});

	}

	if ($('form.booking').length == 1) {
		function toggle_autodiscount_amount() {
			if ($('#autodiscount').val() != 'off') {
				var discount_input_group = $('#autodiscount_amount').closest('.input-group');
				var discount_form_group = $('#autodiscount_amount').closest('.form-group');
				if ($('#autodiscount').val() == 'percentage') {
					discount_input_group.find('.amount').hide();
					discount_input_group.find('.percentage').show();
					discount_form_group.find('.add-text').text('Automatic discounts will apply to all participants unless tags are entered above which then only apply the discount to participants with matching tags added in their profile.' +
						"</br>" +
						'\'Percentage\' will apply the discount to the total cost of each session when a particpant books a full block of sessions.');
				} else if ($('#autodiscount').val() == 'amount' || $('#autodiscount').val() == 'fixed') {
					discount_input_group.find('.amount').show();
					discount_input_group.find('.percentage').hide();
					if($('#autodiscount').val() == 'amount') {
						discount_form_group.find('.add-text').text('Automatic discounts will apply to all participants unless tags are entered above which then only apply the discount to participants with matching tags added in their profile.' +
							"</br>" +
							'\'Amount\' will apply the discount to the total cost of each session when a particpant books a full block of sessions.');
					}
				}
				$('#autodiscount_amount').closest('.form-group').show();
			} else {
				$('#autodiscount_amount').closest('.form-group').hide();
			}
		}
		toggle_autodiscount_amount();
		$('#autodiscount').change(function() {
			toggle_autodiscount_amount();
		});
		function toggle_siblingdiscount_amount() {
			if ($('#siblingdiscount').val() != 'off') {
				var discount_input_group = $('#siblingdiscount_amount').closest('.input-group');
				if ($('#siblingdiscount').val() == 'percentage') {
					discount_input_group.find('.amount').hide();
					discount_input_group.find('.percentage').show();
				} else if ($('#siblingdiscount').val() == 'amount' || $('#siblingdiscount').val() == 'fixed') {
					discount_input_group.find('.amount').show();
					discount_input_group.find('.percentage').hide();
				}
				$('#siblingdiscount_amount').closest('.form-group').show();
			} else {
				$('#siblingdiscount_amount').closest('.form-group').hide();
			}
		}
		toggle_siblingdiscount_amount();
		$('#siblingdiscount').change(function() {
			toggle_siblingdiscount_amount();
		});
	}

	if ($('#regionID, #areaID').length == 2) {
		var areaIDClone = $('select#areaID').html();

		function updateAreas() {

			// remove select2
			$("#areaID").select2('destroy');

			if ($("#regionID").val() == '') {
				$("select#areaID").html("<option value=\"\">Select region first</option>");
			} else {
				//get type
				var region = $("#regionID").val();

				// set default
				$("#areaID").html("<option value=\"\">Select</option>");

				// replace with original
				$("#areaID").append(areaIDClone);

				// remove any which dont match
				$("#areaID option[data-region!='" + region + "']").remove();
			}

			// init select2
			$("#areaID").select2();

		}

		updateAreas();

		$("#regionID").change(function() {
			updateAreas()
		});
	}

	$('.ajax_toggle a').click(function() {
		var link = this;
		$(link).removeClass('btn-danger btn-success').addClass('btn-warning');
		$('i', link).removeClass('fa-times fa-check').addClass('fa-clock');
		$.ajax({
			url: $(this).attr('href'),
			type: 'GET',
			success: function(data) {
				if (data == 'OK') {
					if ($(link).attr('title') == 'Yes') {
						$(link).attr('title', 'No');
						$(link).removeClass('btn-warning').addClass('btn-danger');
						$('i', link).removeClass('fa-clock').addClass('fa-times');
						$(link).attr('href', $(link).attr('href').replace('no', 'yes'));
					} else {
						$(link).attr('title', 'Yes');
						$(link).removeClass('btn-warning').addClass('btn-success');
						$('i', link).removeClass('fa-clock').addClass('fa-check');
						$(link).attr('href', $(link).attr('href').replace('yes', 'no'));
					}
				} else if (data.substr(0, 4) == 'http') {
					window.location = data;
				}
			}
		});
		return false;
	});

	$('input[name="monitor_register_value"]').blur(function () {
		var input = this;
		$(this).attr("disabled", "disabled");
		$.ajax({
			url: $(this).data('url')+"/"+encodeURIComponent($(this).val()),
			type: 'GET',
			success: function(data) {
				$(input).removeAttr("disabled");
				if (data !== 'OK') {
					alert('Error saving data. Please try again.');
				}
			}
		});
		return false;
	});

	// register toggle
	$('.register_toggle a').click(function() {
		var link = this;
		var dataid = $(this).attr("data-id");
		var pin1 = $('#pin1').val();
		var pin = $('#hidden_'+dataid).val();
		var hidden_flag_skip = $("#hidden_flag_skip").val();

		if(pin1 == "" && pin != '' && pin != 0 && hidden_flag_skip == 0 && $(link).attr('title') == 'Signout'){
			printModal(dataid);
		}else{
			$.ajax({
				url: $(this).attr('href'),
				type: 'GET',
				success: function(data) {
					if (data == 'OK') {
						var currentdate = new Date();
						var datetime = currentdate.getHours() + ":"  +
						(currentdate.getMinutes()<10?'0':'')+ currentdate.getMinutes() + ":" +
						(currentdate.getSeconds()<10?'0':'') + currentdate.getSeconds();
						if ($(link).attr('title') == 'Attended') {
							$(link).attr('title', 'Attending');
							$(link).removeClass('btn-success').addClass('btn-warning');
							$('i', link).removeClass('fa-check').addClass('fa-times');
							$(link).attr('href', $(link).attr('href').replace('unattend', 'attend'));
							$(link).closest('td').next().find('.bikeability_level, .shapeup_weight').attr('disabled', 'disabled');
							$('.time1_'+dataid).html(datetime);
						}else if ($(link).attr('title') == 'Signout') {
							$(link).attr('title', 'NotSignout');
							$(link).removeClass('btn-warning').addClass('btn-success');
							$('i', link).removeClass('fa-times').addClass('fa-check');
							$(link).attr('href', $(link).attr('href').replace('signout', 'notsignout'));
							$(link).closest('td').next().find('.bikeability_level, .shapeup_weight').removeAttr('disabled');
							$('.time_'+dataid).html(datetime);
						} else if ($(link).attr('title') == 'NotSignout') {
							$(link).attr('title', 'Signout');
							$(link).removeClass('btn-success').addClass('btn-warning');
							$('i', link).removeClass('fa-check').addClass('fa-times');
							$(link).attr('href', $(link).attr('href').replace('notsignout', 'signout'));
							$(link).closest('td').next().find('.bikeability_level, .shapeup_weight').attr('disabled', 'disabled');
							$('.time_'+dataid).html(datetime);
						} else {
							$(link).attr('title', 'Attended');
							$(link).removeClass('btn-warning').addClass('btn-success');
							$('i', link).removeClass('fa-times').addClass('fa-check');
							$(link).attr('href', $(link).attr('href').replace('attend', 'unattend'));
							$(link).closest('td').next().find('.bikeability_level, .shapeup_weight').removeAttr('disabled');
							$('.time1_'+dataid).html(datetime);
						}
						$('#pin1').val("");
						$('#pin2').val("");
						$('#pin3').val("");
						$('#pin4').val("");
						$('#hidden_flag_skip').val(0);
					}else {
						alert('Error saving data. Please try again.');
					}
				}
			});
		}
		return false;
	});

	// bikeability level
	$('select.bikeability_level[data-action]').change(function() {
		$.ajax({
			url: $(this).attr('data-action'),
			type: 'POST',
			data: {
				level: $(this).val()
			},
			success: function(data) {
				if (data == 'OK') {
					// all ok
					//console.log('saved');
				} else {
					alert('Error saving data. Please try again.');
				}
			}
		});
		return false;
	});

	// shape up weight
	$('input.shapeup_weight[data-action]').change(function() {
		calc_shapeup_weight();
		$.ajax({
			url: $(this).attr('data-action'),
			type: 'POST',
			data: {
				weight: $(this).val()
			},
			success: function(data) {
				if (data == 'OK') {
					// all ok
					//console.log('saved');
				} else {
					alert('Error saving data. Please try again.');
				}
			}
		});
		return false;
	});

	// select recipients by department on new message
	$('.select_recipient').click(function() {
		if ($(this).attr('data-department') == 'all' || $(this).attr('data-school-type') == 'all' || $(this).attr('data-org-type') == 'all') {
			$('#to option').prop('selected', true);
		} else if ($(this).attr('data-department') == 'none' || $(this).attr('data-school-type') == 'none' || $(this).attr('data-org-type') == 'none') {
			$('#to option:selected').prop('selected', false);
		} else if ($(this).attr('data-department') == 'team') {
			$('#to option[data-team=true]').prop('selected', true);
		} else if (typeof $(this).attr('data-group') !== typeof undefined && $(this).attr('data-group') !== false) {
			$('#to option[data-group="' + $(this).attr('data-group') + '"]').prop('selected', true);
		} else if (typeof $(this).attr('data-sector') !== typeof undefined && $(this).attr('data-sector') !== false) {
			$('#to option[data-sector="' + $(this).attr('data-sector') + '"]').prop('selected', true);
		} else if (typeof $(this).attr('data-org-type') !== typeof undefined && $(this).attr('data-org-type') !== false) {
			$('#to option[data-org-type="' + $(this).attr('data-org-type') + '"]').prop('selected', true);
		}else if (typeof $(this).attr('data-school-type') !== typeof undefined && $(this).attr('data-school-type') !== false) {
			$('#to option[data-school-type="' + $(this).attr('data-school-type') + '"]').prop('selected', true);
		} else {
			$('#to option[data-department="' + $(this).attr('data-department') + '"]').prop('selected', true);
		}
		$('#to').trigger("change");
		return false;
	});

	function generatePassword(length, special) {
		var iteration = 0;
		var password = "";
		var randomNumber;
		if(special == undefined){
			var special = false;
		}
		while(iteration < length){
			randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
			if(!special){
				if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
				if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
				if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
				if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
			}
			iteration++;
			password += String.fromCharCode(randomNumber);
		}
		return password;
	}

	$('a.generatepassword').click(function() {
		var password = generatePassword(8);
		$('input#password, input#password_confirm, input#account_password, input#account_password_confirm').val(password);
		alert("The following password has been generated. Please make a note of this for future reference.\n\n" + password);
		return false;
	});

	//Same as above because fields will generate dynamically
	$(document).on("click", ".dynamic-generatepassword" , function() {
		var password = generatePassword(8);
		$(this).parent().next().val(password);
		$(this).parents('.form-group').next().find("input[type='password']").val(password);
		alert("The following password has been generated. Please make a note of this for future reference.\n\n" + password);
		return false;
	});

	// safety equipment checkbox toggle
	$('.safety-equipment input[type=checkbox]').each(function() {
		if ($(this).prop('checked') != true) {
			$(this).closest('.checkbox-single').next('input').hide();
		}

		$(this).change(function() {
			if ($(this).prop('checked')) {
				$(this).closest('.checkbox-single').next('input').show();
			} else {
				$(this).closest('.checkbox-single').next('input').hide();
			}
		});
	});

	if ($('a.add_contact').length == 1) {
		$("a.add_contact").click(function() {
			$(".add_contact_fields").show();
			$(this).closest('.form-group').remove();
			$("input[name=add_contact]").val("1");
			return false;
		});
	}

	if ($('a.add_address').length == 1) {
		$("a.add_address").click(function() {
			$(".add_address_fields").show();
			$(this).closest('.form-group').remove();
			$("input[name=add_address]").val("1");
			return false;
		});
	}

	if ($('a.add_school').length == 1) {
		$(document).on("click", "a.add_school" , function() {
			$(this).parents(".form-group").next().next().show();
			$(this).parents('.form-group').next().val("1");
			$(this).parents('.form-group').remove();
			return false;
		});
	}

	if ($('form.exception').length == 1) {
		function toggle_exception_type_single() {
			var type = $('#type').val();

			$('#fromID, #staffID').closest('.form-group').hide();
			$('.reason').hide();

			if (type == 'staffchange') {
				$('#fromID, #staffID').closest('.form-group').show();
				$('.reason').show();
			}
		}

		toggle_exception_type_single();

		$('#type').change(function() {
			toggle_exception_type_single();
		});

		// toggle reason other
		function exception_reason_other_single() {

			$('#reason').closest('.form-group').hide();

			if ($('#reason_select').val() == 'other') {
				$('#reason').closest('.form-group').show();
			}

		}

		exception_reason_other_single();

		$("#reason_select").change(function() {
			exception_reason_other_single()
		});

		var reasonsClone = $('select#reason_select').html();

		// toggle reason drop down
		function exception_reasons_single() {

			// remove select2
			$("#reason_select").select2('destroy');

			if ($("#assign_to").val() == '') {
				$("select#reason_select").html("<option value=\"\">Select assign to first</option>");
			} else {
				//get type
				var assigned = $("#assign_to").val();

				// set default
				$("#reason_select").html("<option value=\"\">Select</option>");

				// replace with original
				$("#reason_select").append(reasonsClone);

				// remove any which dont match
				$("#reason_select option").not("[data-assigned~='" + assigned + "']").remove();
			}

			// init select2
			$("#reason_select").select2();

			exception_reason_other_single();

		}

		exception_reasons_single();

		if ($("input[name=hidden_reason_select]", this).val() != '') {
			var val = $("input[name=hidden_reason_select]", this).val();
			$("#reason_select", this).val(val);
			$("#reason_select", this).select2('destroy');
			$("#reason_select", this).select2();
			exception_reason_other_single();
		}

		$("#assign_to").change(function() {
			exception_reasons_single()
		});

	}

	// bulk exceptions
	if ($('div.exception').length > 0) {
		function toggle_exception_type() {
			$('div.exception').each(function() {
				var type = $('input[name^=type]', this).val();

				$('select[name^=fromID], select[name^=staffID]', this).closest('.form-group').hide();
				$('.reason', this).hide();

				if (type == 'staffchange') {
					$('select[name^=fromID], select[name^=staffID]', this).closest('.form-group').show();
					$('.reason', this).show();
				}
			});
		}

		toggle_exception_type();

		$('input[name^=type]').change(function() {
			toggle_exception_type();
		});

		// toggle reason other
		function exception_reason_other() {
			$('div.exception').each(function() {
				$('input[name^=reason]', this).closest('.form-group').hide();
				var parent = this;
				$('select[name^=reason_select]', this).each(function() {
					if ($(this).val() == 'other') {
						$('input[name^=reason]', parent).closest('.form-group').show();
					}
				});
			});
		}

		exception_reason_other();

		$('select[name^=reason_select]', this).change(function() {
			exception_reason_other()
		});

		var reasonsClone = $('select[name^=reason_select]').eq(0).html();

		// toggle reason drop down
		function exception_reasons() {
			$('div.exception').each(function() {
				// remove select2
				$('select[name^=reason_select]', this).select2('destroy');

				if ($("select[name^=assign_to]", this).val() == '') {
					$('select[name^=reason_select]', this).html("<option value=\"\">Select assign to first</option>");
				} else {
					//get type
					var assigned = $("select[name^=assign_to]", this).val();

					// set default
					$('select[name^=reason_select]', this).html("<option value=\"\">Select</option>");

					// replace with original
					$('select[name^=reason_select]', this).append(reasonsClone);

					// remove any which dont match
					$('select[name^=reason_select] option', this).not("[data-assigned~='" + assigned + "']").remove();
				}

				// init select2
				$('select[name^=reason_select]', this).select2();

				exception_reason_other();
			});
		}

		exception_reasons();

		$('div.exception').each(function() {
			if ($("input[name^=hidden_reason_select]", this).val() != '') {
				var val = $("input[name^=hidden_reason_select]", this).val();
				$("select[name^=reason_select]", this).val(val);
				$("select[name^=reason_select]", this).select2('destroy');
				$("select[name^=reason_select]", this).select2();
				exception_reason_other();
			}
		});

		$("select[name^=assign_to]").change(function() {
			exception_reasons()
		});

		var prev_assign = null;

		function copy_reasons() {

			if (prev_assign != $('div.exception:eq(0) select[name^=assign_to]').val()) {

				var val = $('div.exception:eq(0) select[name^=assign_to]').val();

				$('div.exception:not(:first) select[name^=assign_to]').each(function() {
					$(this).val(val);
					$(this).select2('destroy')
					$(this).select2();
				});

				exception_reasons();

				prev_assign = val;
			}

			$('div.exception:not(:first) select[name^=reason_select]').each(function() {
				var val = $('div.exception:eq(0) select[name^=reason_select]').val();
				$(this).val(val);
				$(this).select2('destroy')
				$(this).select2();
			});

			exception_reason_other();

			$('div.exception:not(:first) input[name^=reason]').each(function() {
				$(this).val($('div.exception:eq(0) input[name^=reason]').val());
			});

		}

		$('div.exception:eq(0) select[name^=assign_to]').change(function() {
			copy_reasons();
		});

		$('div.exception:eq(0) select[name^=reason_select]').change(function() {
			copy_reasons();
		});

		$('div.exception:eq(0) input[name^=reason]').blur(function() {
			copy_reasons();
		});

	}

	// bulk staff
	if ($('div.bulk-staff').length > 0) {

		function copy_comment() {

			$('div.bulk-staff:not(:first) input[name^=comment]').each(function() {
				$(this).val($('div.bulk-staff:eq(0) input[name^=comment]').val());
			});

		}

		$('div.bulk-staff:eq(0) input[name^=comment]').blur(function() {
			copy_comment();
		});

	}

	$('.book_online .blockID').change(function() {
		if ($(this).val() != '') {
			$(this).closest('form').submit();
		}
	});

	if ($('form.new_family').length > 0) {
		// store previous lookup so not duplicating check
		var prev_first_name = 'none';
		var prev_last_name = 'none';
		var prev_child_first_name = 'none';
		var prev_child_last_name = 'none';


		$(document).on("change", "input[name='first_name'], input[name='last_name']" , function() {
			var fNameEle, lNameEle, contactCheckEle;
			if($(this).attr("name") === "first_name"){
				fNameEle = $(this);
				lNameEle = $(this).closest(".form-group").next().find('input');
				contactCheckEle = $(this).closest(".form-group").prev();
			}else{
				fNameEle = $(this).closest(".form-group").prev().find('input');
				lNameEle = $(this);
				contactCheckEle = $(this).closest(".form-group").prev().prev();
			}
			if (fNameEle.val() != '' && lNameEle.val() != '' && (fNameEle.val() != prev_first_name || lNameEle.val() != prev_last_name)) {
				$.ajax({
					url: '/participants/contactcheck',
					type: 'POST',
					data: {
						first_name: fNameEle.val(),
						last_name: lNameEle.val()
					},
					success: function(data) {
						if (data == 'OK') {
							contactCheckEle.hide();
						} else {
							contactCheckEle.find('.result').html(data);
							contactCheckEle.show();
						}
					}
				});
			}

			prev_first_name = fNameEle.val();
			prev_last_name = lNameEle.val();

			return false;
		});

		$(document).on("change", "input[name='child_first_name'], input[name='child_last_name']" , function() {
			var fNameEle, lNameEle, contactCheckEle;
			if($(this).attr("name") === "child_first_name"){
				fNameEle = $(this);
				lNameEle = $(this).closest(".form-group").next().find('input');
				contactCheckEle = $(this).closest(".form-group").prev();
			}else{
				fNameEle = $(this).closest(".form-group").prev().find('input');
				lNameEle = $(this);
				contactCheckEle = $(this).closest(".form-group").prev().prev();
			}
			if (fNameEle.val() != '' && lNameEle.val() != '' && (fNameEle.val() != prev_child_first_name || lNameEle.val() != prev_child_last_name)) {
				$.ajax({
					url: '/participants/childcheck',
					type: 'POST',
					data: {
						first_name: fNameEle.val(),
						last_name: lNameEle.val()
					},
					success: function(data) {
						if (data == 'OK') {
							contactCheckEle.hide();
						} else {
							contactCheckEle.find('.result').html(data);
							contactCheckEle.show();
						}
					}
				});
			}

			prev_child_first_name = fNameEle.val();
			prev_child_last_name = lNameEle.val();

			return false;
		});
	}

	// payment form
	if ($('form#payment_form').length == 1) {

		function handle_card_payment_single() {
			$('.manual_payment').hide();
			if ($("#method").val() == 'card' && parseFloat($('#amount').val()) > 0) {
				$('.manual_payment').show();
			}
		}

		var orig_ref = $("input[name=transaction_ref]").val();

		$("#method, #contactID, #recordID, #amount").change(function() {
			handle_card_payment_single();
			// generate reference
			var ref_start = $("input[name='accountID']").val() + '-' + $("input[name='familyID']").val() + '-';
			// only generator reference if original reference empty
			if (orig_ref == "") {
				if ($("#contactID").val() != '') {
					var now = new Date();
					var vendortxcode = ref_start + $("#contactID").val() + '-' + now.getFullYear() + ("0" + (now.getMonth() + 1)).slice(-2) + ("0" + now.getDate()).slice(-2) + ("0" + now.getHours()).slice(-2) + ("0" + now.getMinutes()).slice(-2) + ("0" + now.getSeconds()).slice(-2);
					$('input[name=transaction_ref]').val(vendortxcode);
				}
			}
		});

		handle_card_payment_single();

		$(".manual_payment a").click(function(e) {
			e.preventDefault();
			// work out dimensions
			var height = $(window).height();
			var width = $(window).width();
			var amount = $("#amount").val();
			if(amount == ""){
				amount = 0;
			}

			var link = $(this).attr("href") + "/" + $("#contactID").val() + "/" + amount + '/' + $("input[name=transaction_ref]").val();

			// open booking info
			var infoWindow = window.open(link,'infoWindow','height=' + height + ',width=' + (width*.20) + ',left=0,top=0,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=yes,directories=no,status=yes');

			// open sage pay
			var paymentWindow = window.open($(this).attr('data-payment-link'),'paymentWindow','height=' + height + ',width=' + (width*.8) + ',left=' + (width*.2) + ',top=0,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=yes,directories=no,status=yes');

			return false;
		});
	}

	if ($('form.resource').length == 1) {

		function toggle_policies_checkbox() {
			if ($("#category").val() == "policies") {
				$("#reset_policies").closest('.form-group').show();
			} else {
				$("#reset_policies").closest('.form-group').hide();
			}
		}

		$("#category").change(function() {
			toggle_policies_checkbox();
		});

		toggle_policies_checkbox();

	}
	if ($('form.export').length == 1) {

		function toggle_export_fields(val = null) {
			$('form.export .customers, form.export .families').hide();

			// show if match both

			$('form.export .customers.families').show();

			if($("#type").val() == 'participants')
				$('.hide_option').show();
			else
				$('.hide_option').hide();

			if ($("#type").val() != "") {
				$('form.export .' + $('#type').val()).show();
			}
		}

		$("#type").change(function() {
			toggle_export_fields(this.val);
		});

		toggle_export_fields();

	}

	$(document).on('click', 'a.timetable_read', function() {
		var link = this;
		$.ajax({
			url: $(this).attr('href'),
			type: 'POST',
			success: function(data) {
				if (data == 'CONFIRMED') {
					$(link).removeClass("text-danger").addClass("text-success").text('Unconfirm?');
					$('i', $(link).closest('.dd-handle')).removeClass().addClass('far fa-check text-success');
					$('span', $(link).closest('.dd-handle')).removeClass().addClass('unconfirm');
					$(link).attr('href', $(link).attr('href').replace('/confirm', '/unconfirm'));
				} else if (data == 'UNCONFIRMED') {
					$(link).removeClass("text-success").addClass("text-danger").text('Confirm?');
					$('i', $(link).closest('.dd-handle')).removeClass().addClass('far fa-times text-danger');
					$('span', $(link).closest('.dd-handle')).removeClass().addClass('confirm');
					$(link).attr('href', $(link).attr('href').replace('/unconfirm', '/confirm'));
				} else {
					alert('Error: Please refresh the page and try again');
				}
			}
		});
		return false;
	});

	$(document).on('change', '.todo-list input[type=checkbox]', function() {
		var task = this;
		$.ajax({
			url: $(this).attr('data-action'),
			type: 'POST',
			success: function(data) {
				if (data == 'COMPLETE') {
					$(task).prop('checked', true);
					$(task).closest('.item').addClass('done');
					$(task).attr('data-action', $(task).attr('data-action').replace('/complete', '/uncomplete'));
				} else if (data == 'UNCOMPLETE') {
					$(task).prop('checked', false);
					$(task).closest('.item').removeClass('done');
					$(task).attr('data-action', $(task).attr('data-action').replace('/uncomplete', '/complete'));
				} else {
					alert('Error: Please refresh the page and try again');
				}
			}
		});
		return false;
	});

	$(document).on('click', '.todo-list .remove', function() {
		var link = this;
		if (confirm('Are you sure?')) {
			$.ajax({
				url: $(this).attr('href'),
				type: 'POST',
				success: function(data) {
					if (data == 'DELETED') {
						$(link).closest('.item').fadeOut();
					} else {
						alert('Error: Please refresh the page and try again');
					}
				}
			});
		}
		return false;
	});

	$('.toggle_completed').click(function() {
		$('.todo-list .done').toggleClass('show');
		return false;
	});

	if ($('.bulk-supplementary').length > 0) {
		handle_bulk_supplementary();
		$('body').on('change','#action', function() {
			handle_bulk_supplementary();
		});
	}

	if ($('.lesson-staff').length > 0) {

		function check_availability(caller, staff_id, exclude_dropdown_values) {
			var parent = $(caller).closest('.lesson-staff');

			$(".reason", parent).html("");
			if( $(".from", parent).val() != "" && $(".to", parent).val() != "") {

				if ($(parent).attr('data-type') == 'exception') {
					var post_data = {
						startDate: $(".date", parent).val(),
						endDate: $(".date", parent).val(),
						startTimeH: $(parent).attr('data-fromH'),
						startTimeM: $(parent).attr('data-fromM'),
						endTimeH: $(parent).attr('data-toH'),
						endTimeM: $(parent).attr('data-toM'),
						day: $(parent).attr('data-day'),
						booking: $(parent).attr('data-booking'),
						lesson: $(parent).attr('data-lesson'),
						activityID: $(parent).attr('data-activity'),
						staffType: $(".fromID", parent).find(':selected').attr('data-staffType')
					};
				} else if ($(parent).attr('data-type') == 'bulk-exception') {
					var post_data = {
						startDate: $(parent).attr('data-date'),
						endDate: $(parent).attr('data-date'),
						startTimeH: $(parent).attr('data-fromH'),
						startTimeM: $(parent).attr('data-fromM'),
						endTimeH: $(parent).attr('data-toH'),
						endTimeM: $(parent).attr('data-toM'),
						day: $(parent).attr('data-day'),
						booking: $(parent).attr('data-booking'),
						lesson: $(parent).attr('data-lesson'),
						activityID: $(parent).attr('data-activity'),
						staffType: $(".fromID", parent).find(':selected').attr('data-staffType')
					};
				} else {
					var post_data = {
						startDate: $(".from", parent).val(),
						endDate: $(".to", parent).val(),
						startTimeH: $("select.fromH", parent).val(),
						startTimeM: $("select.fromM", parent).val(),
						endTimeH: $("select.toH", parent).val(),
						endTimeM: $("select.toM", parent).val(),
						day: $(parent).attr('data-day'),
						booking: $(parent).attr('data-booking'),
						lesson: $(parent).attr('data-lesson'),
						activityID: $(parent).attr('data-activity'),
						staffType: $("select.staffType", parent).val(),
					};
				}

				$.post("/bookings/availability", post_data, function(data){
					$("select.staffID", parent).html('');
					$(".reason", parent).html("");
					if (data.errors !== undefined) {
						$("select.staffID", parent).append('<option value="">Check dates/times are correct</option>');
						return;
					}
					//$("select.staffID", parent).append('<option value="">Select</option>');

					if (data.length > 0) {
						// url parsing to avoid adding addition field in not needed places
						var url = document.URL.split('/');
						if (url[3] && url[4]) {
							if (url[3] == 'sessions' && ['exceptions', 'bulk'].indexOf(url[4]) !== -1) {
								$(".staffID", parent).append('<option value="0" data-priority="-50" data-key="-1">No Replacement Required</option>');
							}
						}
					}

					// work out those to exclude
					var exclude_ids = [];
					if (exclude_dropdown_values.length == 1) {
						// only exclude selected
						exclude_ids.push(exclude_dropdown_values.val());
					}

					$.each(data, function(key) {
						if (exclude_ids.indexOf(data[key].staffID) <= -1) {
							$(".staffID", parent).append(data[key].option);
							var item_class = "text-warning";
							if (data[key].status == 'green') {
								var item_class = "text-success";
							} else if (data[key].status == 'red') {
								var item_class = "text-danger";
							}
							$('select.staffID option[value=' + data[key].staffID + ']', parent).addClass(item_class).attr('data-key', key);
						}
					});

					// auto select first, unless staff id passed in
					$('select.staffID', parent).val($('select.staffID option:first', parent).val());
					if (staff_id != undefined && staff_id != '') {
						$('select.staffID', parent).val(staff_id);
					}

					// show reason for selected item
					var key = $('select.staffID option[value=' + $('select.staffID', parent).val() + ']', parent).attr('data-key');
					if (key != -1) {
						if (data[key].reason != undefined && data[key].reason != '') {
							if (data[key].priority > 30) {
								$(".reason", parent).html('<div class="alert alert-success"><h4>Good Match</h4><ul>' + data[key].reason + '</ul></div>');
							} else if (data[key].priority < 0) {
								$(".reason", parent).html('<div class="alert alert-danger"><h4>Negative Match</h4><ul>' + data[key].reason + '</ul></div>');
							} else {
								$(".reason", parent).html('<div class="alert alert-warning"><h4>Neutral Match</h4><ul>' + data[key].reason + '</ul></div>');
							}
						}
					}

					$("select.staffID", parent).change(function() {
						var key = $('select.staffID option[value=' + $(this).val() + ']', parent).attr('data-key');
						$(".reason", parent).html("");
						$(this).attr('data-staff', $(this).val());
						if (key != -1) {
							if (data[key].reason != undefined && data[key].reason != '') {
								if (data[key].priority > 30) {
									$(".reason", parent).html('<div class="alert alert-success"><h4>Good Match</h4><ul>' + data[key].reason + '</ul></div>');
								} else if (data[key].priority < 0) {
									$(".reason", parent).html('<div class="alert alert-danger"><h4>Negative Match</h4><ul>' + data[key].reason + '</ul></div>');
								} else {
									$(".reason", parent).html('<div class="alert alert-warning"><h4>Neutral Match</h4><ul>' + data[key].reason + '</ul></div>');
								}
							}
						}
					});
				});
			}
		}


		// check availability on bookings
		$(".from, .to, select.fromH, select.fromM, select.toH, select.toM, select.staffType, .date, .fromID").change( function() {
			check_availability(this, $('select.staffID', $(this).closest('.lesson-staff')).attr('data-staff'), $(this).closest('.lesson-staff').find('.fromID'));
		});

		// if staff id not prefilled, query
		$('select.staffID').each(function() {
			$(this).change(function() {
				$(this).attr('data-staff', $(this).val());
			});

			check_availability(this, $(this).attr('data-staff'), $(this).closest('.lesson-staff').find('.fromID'));
		});

		function calc_staff_nums(lessonID) {
			var div = $('.lesson-staff[data-lesson=' + lessonID + ']');
			// reset to existing
			$('span.staff_head', div).text($('span.staff_head', div).attr('data-existing'));
			$('span.staff_lead', div).text($('span.staff_lead', div).attr('data-existing'));
			$('span.staff_assistant', div).text($('span.staff_assistant', div).attr('data-existing'));

			// calc new numbers
			if ($('.staffType', div).val() == 'head') {
				$('span.staff_head', div).text(parseInt($('span.staff_head', div).attr('data-existing')) + 1);
			} else if ($('.staffType', div).val() == 'lead') {
				$('span.staff_lead', div).text(parseInt($('span.staff_lead', div).attr('data-existing')) + 1);
			} else if ($('.staffType', div).val() == 'assistant') {
				$('span.staff_assistant', div).text(parseInt($('span.staff_assistant', div).attr('data-existing')) + 1);
			}
		}

		$('div.lesson-staff').each(function() {
			var lessonID = $(this).attr('data-lesson');
			calc_staff_nums(lessonID);
		});

		$('div.lesson-staff .staffType').change(function() {
			var lessonID = $(this).closest('.lesson-staff').attr('data-lesson');
			calc_staff_nums(lessonID);
		});
	}

	// toggle showing white label fields on edit account
	if ($('form.edit_account').length == 1) {
		function toggle_whitelabel_fields() {
			if ($('#addon_whitelabel:checked').length == 1) {
				$('.whitelabel_fields').show();
			} else {
				$('.whitelabel_fields').hide();
			}
		}
		toggle_whitelabel_fields();
		$('#addon_whitelabel').change(function() {
			toggle_whitelabel_fields();
		});
	}

	$('.truncate').succinct({
		size: 50
	});

	function timeToSeconds(s) {
		var c = s.split(':');
		return (parseInt(c[0]) * 60 * 60) + (parseInt(c[1])* 60);
	}

	function google_maps_travel_time(origin, destination, travel_datetime, direction, target, target_within, container) {
		var title_label = '';
		var label_before = '';
		var label_after = ' <i class="far fa-arrow-right"></i> <i class="far fa-map-marker-alt"></i> '
		if (direction == 'to') {
			title_label = '';
			label_before = ' <i class="far fa-arrow-right"></i> ';
			label_after = '';
		}

		var deferred = new $.Deferred();
		var service = new google.maps.DistanceMatrixService;
		service.getDistanceMatrix({
			origins: [origin],
			destinations: [destination],
			travelMode: google.maps.TravelMode.DRIVING,
			unitSystem: google.maps.UnitSystem.IMPERIAL,
			avoidHighways: false,
			avoidTolls: false,
			drivingOptions: {
				departureTime: travel_datetime,
				trafficModel: google.maps.TrafficModel.BEST_GUESS
			}
		}, function(response, status) {
			var distance_meters = 0, travel_desc ="";
			//console.log(response);
			if (status !== google.maps.DistanceMatrixStatus.OK) {
				travel_desc = label_before + 'Unknown' + label_after;
				//alert('Error was: ' + status);
			} else {
				var results = response.rows[0].elements[0];
				if (results.status !== google.maps.DistanceMatrixStatus.OK) {
					travel_desc = label_before + 'Unknown' + label_after;
					//alert('Error was: ' + results.status);
				} else {
					var duration_text = results.duration.text;
					var duration_value = results.duration.value;
					// if traffix returned, use that
					if (results.duration_in_traffic != undefined) {
						duration_text = results.duration_in_traffic.text;
						duration_value = results.duration_in_traffic.value;
					}
					// check if enough time to make it
					if (direction == 'from') {
						distance_meters = results.distance.value;
						var time_from = $(target_within).attr('data-time-prev');
						var time_to = $(container).attr('data-start_time');
					} else {
						var time_from = $(container).attr('data-end_time');
						var time_to = $(target_within).attr('data-time-next');
					}
					if (time_from != undefined && time_to != undefined) {
						var time_diff = timeToSeconds(time_to) - timeToSeconds(time_from);
						//console.log(time_diff + ' ' + duration_value);
						if (time_diff < duration_value) {
							label_before = label_before + '<span class="text-red">';
							label_after = '</span>' + label_after;
							title_label = title_label + ' (Not Enough Time)';
						}
					}
					travel_desc = label_before + results.distance.text + ' / ' + duration_text + label_after;
				}
				//console.log(results);
			}

			$(target, target_within).html(travel_desc + ' ').attr('title', title_label + origin + ' to ' + destination);

			deferred.resolve(
				{
					'distance_from': distance_meters
				}
			);
		});

		return deferred.promise();
	}

	if ($('#availability[data-postcode]').length == 1) {
		function calc_availability_travel() {
			var postcode = $('#availability').attr('data-postcode');
			var start_time = $('#availability').attr('data-start_time');
			var end_time = $('#availability').attr('data-end_time');
			var base_date = $('#availability').attr('data-date');
			var date_bits = base_date.split("/");
			var counted_distance_elements = [];
			var count_elements_to_reorder = $("#availability li[data-status='green'][data-postcode-prev][data-postcode-prev!='"+ postcode +"']").length;
			$('#availability li').each(function() {
				var row = this;
				var prev_postcode = $(row).attr('data-postcode-prev');
				var next_postcode = $(row).attr('data-postcode-next');
				var prev_time = $(row).attr('data-time-prev');
				var next_time = $(row).attr('data-time-next');
				if ($(row).attr('data-status') =='green') {
					var journeys = ["from", "to"];
					var multiple_values = false;
					for (var i = 0; i < journeys.length; i++) {
						var travel_desc = null;
						if (journeys[i] == 'from') {
							if (prev_time != undefined) {
								// use from prev lesson
								var time_bits = prev_time.split(":");
								var hour = parseInt(time_bits[0]);
								var minute = parseInt(time_bits[1]);
							} else {
								// set depature time to 1 hour before start
								var time_bits = start_time.split(":");
								var hour = parseInt(time_bits[0])-1;
								var minute = parseInt(time_bits[1]);
							}
							var departure_time = new Date(date_bits[2], date_bits[1]-1, date_bits[0], hour, minute, 0);
							var now = new Date();
							// if depature time in past, used tomorrows date as google can't calc traffic in past
							if (departure_time < now) {
								var tomorrow = new Date(new Date().getTime() + (24 * 60 * 60 * 1000));
								departure_time = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate(), hour, minute, 0);
							}
							var origin = prev_postcode;
							var destination = postcode;
							var target = '.travel_from';
						} else {
							// use end of lesson as depature time
							var time_bits = end_time.split(":");
							var departure_time = new Date(date_bits[2], date_bits[1]-1, date_bits[0], parseInt(time_bits[0]), parseInt(time_bits[1]), 0);
							var now = new Date();
							// if depature time in past, used tomorrows date as google can't calc traffic in past
							if (departure_time < now) {
								var tomorrow = new Date(new Date().getTime() + (24 * 60 * 60 * 1000));
								departure_time = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate(), parseInt(time_bits[0]), parseInt(time_bits[1]), 0);
							}
							var origin = postcode;
							var destination = next_postcode;
							var target = '.travel_to';
						}
						if (origin != '' && destination != '' && origin != destination) {
							// work out only if origin different to destination
							$.when(google_maps_travel_time(origin, destination, departure_time, journeys[i], target, row, '#availability')).done(function(result){
								if (result.distance_from > 0) {
									counted_distance_elements.push({
										'distance': result.distance_from,
										'element': row
									});

									if (count_elements_to_reorder == counted_distance_elements.length) {
										counted_distance_elements.forEach(function(result) {
											result.element.remove();
										});

										function sort_by_distance(a, b) {
											if ( a.distance > b.distance ){
												return -1;
											}
											if ( a.distance < b.distance ){
												return 1;
											}
										}

										counted_distance_elements.sort(sort_by_distance);

										counted_distance_elements.forEach(function(result) {
											$('#availability .item-list').prepend(result.element);
										});
									}
								}
							});
						}
					}
				}
			});
		}

		calc_availability_travel();
	}

	if ($('table#approvals').length == 1) {
		function calc_approvals_travel() {
			$('#approvals tr[data-postcode]').each(function() {
				var row = this;
				var postcode = $(row).attr('data-postcode');
				var prev_postcode = $(row).attr('data-postcode-prev');
				var next_postcode = $(row).attr('data-postcode-next');
				var base_date = $(row).attr('data-date');
				var date_bits = base_date.split("/");
				var start_time = $(row).attr('data-start_time');
				var end_time = $(row).attr('data-end_time');
				var journeys = ["from", "to"];
				var multiple_values = false;
				for (var i = 0; i < journeys.length; i++) {
					var travel_desc = null;
					if (journeys[i] == 'from') {
						// set depature time to 1 hour before start
						var time_bits = start_time.split(":");
						var hour = parseInt(time_bits[0])-1;
						var minute = parseInt(time_bits[1]);
						var departure_time = new Date(date_bits[2], date_bits[1]-1, date_bits[0], hour, minute, 0);
						var now = new Date();
						// if depature time in past, used tomorrows date as google can't calc traffic in past
						if (departure_time < now) {
							var tomorrow = new Date(new Date().getTime() + (24 * 60 * 60 * 1000));
							departure_time = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate(), hour, minute, 0);
						}
						var origin = prev_postcode;
						var destination = postcode;
						var target = '.travel_from';
					} else {
						// use end of lesson as depature time
						var time_bits = end_time.split(":");
						var departure_time = new Date(date_bits[2], date_bits[1]-1, date_bits[0], parseInt(time_bits[0]), parseInt(time_bits[1]), 0);
						var now = new Date();
						// if depature time in past, used tomorrows date as google can't calc traffic in past
						if (departure_time < now) {
							var tomorrow = new Date(new Date().getTime() + (24 * 60 * 60 * 1000));
							departure_time = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate(), parseInt(time_bits[0]), parseInt(time_bits[1]), 0);
						}
						var origin = postcode;
						var destination = next_postcode;
						var target = '.travel_to';
					}
					if (origin != '' && destination != '' && origin != destination) {
						// work out only if origin different to destination
						google_maps_travel_time(origin, destination, departure_time, journeys[i], target, row, '#approvals');
					}
				}
			});
		}

		calc_approvals_travel();
	}

	if ($('form#equipment_booking').length == 1) {
		function toggle_equipment_booking_type() {
			$('#staffID, #orgID, #contactID, #childID').closest('.form-group').hide();
			var type = $('#type').val();
			if (type == 'staff') {
				$('#staffID').closest('.form-group').show();
			}
			if (type == 'org') {
				$('#orgID').closest('.form-group').show();
			}
			if (type == 'contact') {
				$('#contactID').closest('.form-group').show();
			}
			if (type == 'child') {
				$('#childID').closest('.form-group').show();
			}
		}
		toggle_equipment_booking_type();
		$('#type').change(function() {
			toggle_equipment_booking_type();
		});
	}

	// select2 tags
	$(".select2-tags").select2({
		tags: true
	});

	// select2 ajax
	$(".select2-ajax").each(function() {
		$(this).select2({
			dataType: 'json',
			allowClear: true,
			placeholder: 'Search',
			ajax: {
				url: $(this).attr('data-ajax-url'),
				data: function (params) {
					var query = {
						term: params.term,
						page: params.page || 1
					}
					return query;
				},
				delay: 250
			}
		});
	});

	// block address
	if ($('form.block').length == 1 && $("form.block select#orgID").length == 1) {

		// clone addresses
		var addressIDClone = $('form.block #addressID').html();

		function updateBlockAddresses() {
			// remove select2
			$("form.block #addressID").select2('destroy');

			//get type
			var org = $("form.block #orgID").val();

			// set default
			$("form.block #addressID").html("<option value=\"\" class=\"select\">Select</option>");

			// replace with original
			$("form.block #addressID").append(addressIDClone);

			// remove any which dont match
			$("form.block #addressID option[data-org!='" + org + "']:not(.select)").remove();

			// init select2
			$("form.block #addressID").select2();
		}

		updateBlockAddresses();

		$("form.block #orgID").change(function() {
			updateBlockAddresses();
		});
	}

	//Back to top button on selected reports
	if ($("form#utilisation-report-search").length == 1 ||
		$("form#activities-report-search").length == 1 ||
		$("form#timesheets-report-search").length == 1 ||
		$("form#projects-contracts-report-search").length == 1 ||
		$("form#session-delivery-report-search").length == 1 ||
		$("form#projects-report-search").length == 1 ||
		$("form#project-code-report-search").length == 1 ||
		$("form#performance-report-search").length == 1 ||
		$("form#payroll-history-report-search").length == 1 ||
		$("form#bikeability-report-search").length == 1 ||
		$("form#marketing-report-search").length == 1 ||
		$("form#participant-billing-report-search").length == 1 ||
		$("form#payroll-report-search").length == 1) {
		addBackToTop({
			diameter: 37,
			backgroundColor: '#3699FF',
			textColor: '#fff'
		})
	}

	// utilisation report
	if ($("form#utilisation-report-search select#field_staff_id").length == 1) {

		// clone addresses
		var staffClone = $('form#utilisation-report-search select#field_staff_id').html();

		function updateActiveStaff() {
			// remove select2
			$("form#utilisation-report-search select#field_staff_id").select2('destroy');

			//get type
			var active_status = $("form#utilisation-report-search select#field_is_active").val();

			// set default
			$("form#utilisation-report-search select#field_staff_id").html("<option value=\"\" class=\"select\">Select</option>");

			// replace with original
			$("form#utilisation-report-search select#field_staff_id").append(staffClone);

			// remove any which dont match
			if (active_status != '') {
				if (active_status == 'yes') {
					active_status = 1;
				} else if (active_status == 'no') {
					active_status = 0;
				}
				$("form#utilisation-report-search select#field_staff_id option[data-active!='" + active_status + "']:not(.select)").remove();
			}

			// init select2
			$("form#utilisation-report-search select#field_staff_id").select2();
		}

		updateActiveStaff();

		$("form#utilisation-report-search select#field_is_active").change(function() {
			updateActiveStaff();
		});
	}

	if ($('table.availabilitycal_slots').length == 1) {
		$('.availabilitycal_slots').on("click", "a.remove", function() {
			$(this).closest('tr').remove();
			return false;
		});
		$('.availabilitycal_slots').on("click", "a.add", function() {
			$('.availabilitycal_slots tbody tr:first-child .select2[tabindex=-1]').each(function() {
				$(this).select2('destroy');
			});
			var $tr = $('.availabilitycal_slots tbody tr').eq(0);
			var $clone = $tr.clone();
			var $clone_id = $tr.attr('data-id');
			var $new_id = parseInt($('.availabilitycal_slots tbody tr:last-child').attr('data-id')) + 1;
			$clone.attr('data-id', $new_id);
			$clone.find('input, select').each(function() {
				$(this).attr('name', $(this).attr('name').replace('slots[' + $clone_id + ']', 'slots[' + $new_id + ']'));
				$(this).val('');
			});
			$clone.find('select').each(function() {
				$(this).val($("option:first", this).val());
			});
			$clone.find('select.startTimeH').val('07');
			$clone.find('select.endTimeH').val('08');
			$clone.find('select.startTimeM, select.endTimeM').val('08');
			$('.availabilitycal_slots tbody').append($clone);
			$('.availabilitycal_slots tbody tr:first-child .select2, .availabilitycal_slots tbody tr:last-child .select2').each(function() {
				$(this).select2();
			});
			return false;
		});
	}

	$('.responsive-table.fixed-1 tr').each(function () {
		var css= {
			height: $(this).outerHeight() + 'px'
		};
		$(this).find('th:first-child, td:first-child').css(css);
		$(this).css(css);
	});
	$('.responsive-table.fixed-2 tr').each(function () {
		var css= {
			height: $(this).outerHeight() + 'px'
		};
		$(this).find('th:first-child, td:first-child, th:nth-child(2), td:nth-child(2)').css(css);
		$(this).css(css);
	});
	$('.responsive-table.fixed-3 tr').each(function () {
		var css= {
			height: $(this).outerHeight() + 'px'
		};
		$(this).find('th:first-child, td:first-child, th:nth-child(2), td:nth-child(2), th:nth-child(3), td:nth-child(3)').css(css);
		$(this).css(css);
	});
	$('.responsive-table.fixed-1, .responsive-table.fixed-2, .responsive-table.fixed-3').addClass('height-calculated');

	if ($('#bikeability_report').length == 1) {
		function toggle_register_type() {
			$('#field_org_id').closest('div').hide();
			$('#field_brand_id').closest('div').show();
			if ($('#field_type').val() == 'names') {
				$('#field_org_id').closest('div').show();
				$('#field_brand_id').closest('div').hide();
			}
		}
		toggle_register_type();
		$('#field_type').change(function() {
			toggle_register_type();
		});
	}

	$('.upload-session-attachment').click(function() {
		$(this).slideUp();
		$('#upload-session-attachment').slideDown();
	});


	function dashboard_request(box) {
		var url = $(box).attr('data-url');
		if (url == '' || url == false || url == undefined) {
			return false;
		}
		// if already loaded skip
		if ($(box).find('.loading').length == 0) {
			return false;
		}
		$.ajax({
			url: url,
			type: 'GET',
			results_container: $('.results', $(box)),
			success: function(data) {
				$(this.results_container).html(data);

				// remove box if empty
				if ($('> div.dd', $(this.results_container)).hasClass('none') && $(box).attr('id') != 'dashboard-detail') {
					$(box).fadeOut();
				}

				// jump to section
				if (window.location.hash.length > 0) {
					window.location = window.location;
				}

				//check element exist
				if($(".view-bookings-toggle").length) {
					BookingsViewOverlay.init('view-bookings');
				}
			}
		});
	}

	if ($('.widget_area').length > 0) {
		function save_widget_state() {
			var widget_cols = [];
			$('.widget_area').each(function() {
				var col_widgets = [];
				$('.card', this).each(function() {
					var state = 'open';
					if ($(this).hasClass('card-collapsed') || $(this).hasClass('card-collapse')) {
						state = 'collapsed';
					}
					col_widgets.push({
						id: $(this).attr('id'),
						state: state
					});
				});
				widget_cols.push(col_widgets);
			});
			$.post('/dashboard/save_state', { json: JSON.stringify(widget_cols) });
		}
		$(".widget_area").sortable({
			connectWith: ".widget_area",
			handle: ".card-header",
			placeholder: "drop-placeholder",
			stop: function() {
				save_widget_state();
			}
		});
		function load_widget_state() {
			// create tmp area to store them in
			$('body').append('<div class="tmp_widgets" style="display:none;"></div>');
			var i = 0;
			// add col id to each widget for later
			$('.widget_area').each(function() {
				$('.card', this).each(function() {
					$(this).attr('data-col', i);
				});
				i++;
			});
			// move widgets to tmp area
			$('.widget_area .card').each(function() {
				$(this).appendTo('.tmp_widgets');
			});
			if (dashboard_config != '') {
				// assign widgets to specified user specified col
				var widget_cols = JSON.parse(dashboard_config);
				for (var i = 0; i < widget_cols.length; i++) {
					for (var j = 0; j < widget_cols[i].length; j++) {
						var box = $('.tmp_widgets #' + widget_cols[i][j].id);
						if (widget_cols[i][j].state == 'collapsed') {
							box.addClass('card-collapsed');
						}
						else {
							box.removeClass('card-collapsed');
						}
						$('.widget_area').eq(i).append(box);
					}
				}
			}
			// add any that are not in json to their default cols
			if ($('.tmp_widgets .card').length > 0) {
				$('.tmp_widgets .card').each(function() {
					var col = parseInt($(this).attr('data-col'));
					$('.widget_area').eq(col).append(this);
				});
			}
			// remove tmp area
			$('.tmp_widgets').remove();

			$('.widget_area .card').each(function() {
				dashboard_request(this);
			});

			$('.widget_area .card').addClass('visible');
		}
		load_widget_state();

	}
	if ($('#highlights').length > 0) {
		dashboard_request('#highlights');
	}
	if ($('#dashboard-detail').length > 0) {
		dashboard_request('#dashboard-detail');
	}

	if ($('form#staff-note').length > 0) {
		function staff_note_observation_toggle() {
			$('#field_observation_score').closest('.form-group').hide();
			if ($('#type').val() == 'observation') {
				$('#field_observation_score').closest('.form-group').show();
			}
		}
		staff_note_observation_toggle();
		$('#type').change(function() {
			staff_note_observation_toggle();
		});
	}

	// show changed times option on edit lesson
	if ($('form.edit_lesson').length > 0) {
		// on load
		if ($('#startTimeH').val() != orig_startH || $('#startTimeM').val() != orig_startM || $('#endTimeH').val() != orig_endH || $('#endTimeM').val() != orig_endM) {
			$('.times_changed').show();
		}
		// on change
		$('form.edit_lesson select').change(function() {
			if ($('#startTimeH').val() != orig_startH || $('#startTimeM').val() != orig_startM || $('#endTimeH').val() != orig_endH || $('#endTimeM').val() != orig_endM) {
				$('.times_changed').show();
			} else {
				$('.times_changed').hide();
			}
		});
	}

	// show/hide input field extras for discount depending on discount type
	if ($('select[name=discount_type]').length > 0) {
		function discount_type_toggle() {
			var discount_input_group = $('input[name=discount]').closest('.input-group');
			if ($('select[name=discount_type]').val() == 'percentage') {
				discount_input_group.find('.amount,.block_amount').hide();
				discount_input_group.find('.percentage').show();
			} else if ($('select[name=discount_type]').val() == 'amount') {
				discount_input_group.find('.amount').show();
				discount_input_group.find('.percentage,.block_amount').hide();
			} else if ($('select[name=discount_type]').val() == 'block_amount') {
				discount_input_group.find('.block_amount').show();
				discount_input_group.find('.percentage,.amount').hide();
			}
		}
		discount_type_toggle();
		$('select[name=discount_type]').change(function() {
			discount_type_toggle();
		});
	}

	// toggle contact privacy newsletters
	if ($('form.family #marketing_consent, form.participant-families #marketing_consent').length > 0) {
		function privacy_toggle_fields(curEle) {
			if(curEle === ''){
				$('.form-group.marketing_allowed').hide();
					return;
			}
			if (curEle.is(':checked')) {
					curEle.parents(".form-group").next().show();
					curEle.parents(".multi-columns").next().find(".marketing_allowed").show();
				} else {
					curEle.parents(".form-group").next().hide();
					curEle.parents(".multi-columns").next().find(".marketing_allowed").hide();
			}
			privacy_toggle_source_other('');
		}
		privacy_toggle_fields('');
		$(document).on("change", 'input[name="marketing_consent"]' , function() {
			privacy_toggle_fields($(this));
		});

		function privacy_toggle_source_other(curEle) {
			if(curEle === ''){
				$('#source_other').closest('.form-group').hide();
				return;
			}
			if (curEle.parents('.multi-columns').prev().find('input[name="marketing_consent"]').is(':checked') && curEle.val() == 'Other') {
				curEle.parents('.form-group').next().show();
			} else {
				curEle.parents('.form-group').next().hide();
			}
		}
		privacy_toggle_source_other('');
		$(document).on("change", 'select[name="source"]' , function() {
			privacy_toggle_source_other($(this));
		});
	}

	$('a.check-out').click(function(e) {
        e.preventDefault();
        var orig_label = $(this).text();
        var link = this;
        var alert_message = $(this).closest('.alert');
        $(link).addClass('disabled');
        $(link).text('Checking out...');

        $.ajax({
            url: $(link).attr('href'),
            type: 'POST',
            success: function(data) {
                if (data == 'OK') {
                    // tell user
                    $(alert_message).find('h4 span').text('Session Check-out Successful');
                    $(alert_message).find('p span').text('You\'re now checked out');
                    $(link).slideUp();
                } else {
                    // error saving
                    alert("Error saving, please try again");
                    $(link).removeClass('disabled');
                    $(link).text(orig_label);
                }
            },
            error: function(x, t, m) {
                // error detecting
                alert("Error saving, please try again");
                $(link).removeClass('disabled');
                $(link).text(orig_label);
            }
        });
	});

	// session check in
	$('a.check-in').click(function(e) {
		e.preventDefault();
		var orig_label = $(this).text();
		var link = this;
		var alert_message = $(this).closest('.alert');
		$(link).addClass('disabled');
		$(link).text('Detecting location...');

		if (!navigator.geolocation){
			// not supported
			alert("<p>Geolocation is not supported by your browser</p>");
			$(link).removeClass('disabled');
			$(link).text(orig_label);
			return;
		}

		navigator.geolocation.getCurrentPosition(function(position) {
			// ok, save
			$.ajax({
				url: $(link).attr('href'),
				type: 'POST',
				data: {
					lat: position.coords.latitude,
					lng: position.coords.longitude,
					accuracy: position.coords.accuracy,
					csrf_token: $('input[name=csrf_token]').val()
				},
				timeout: 5000,
				success: function(data) {
					if (data == 'OK') {
						// tell user
						$(alert_message).find('h4 span').text('Session Check-in Successful');
						$(alert_message).find('p span').text('You\'re now checked in');
						$(link).slideUp();
					} else {
						// error saving
						alert("Error saving location, please try again");
						$(link).removeClass('disabled');
						$(link).text(orig_label);
					}
				},
				error: function(x, t, m) {
					// error detecting
					alert("Error saving location, please try again");
					$(link).removeClass('disabled');
					$(link).text(orig_label);
				}
			});
		}, function() {
			// declined or timed out
			alert("Unable to retrieve your location, please allow the location permission within your browser");
			$(link).removeClass('disabled');
			$(link).text(orig_label);
		});
	});

	// checkins map
	if (typeof checkin_markers !== 'undefined') {
		var center = new google.maps.LatLng(51.509865, -0.118092);

		var checkin_map = new google.maps.Map(document.getElementById("checkin_map"), {
			zoom: 7,
			center: center,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});

		var bounds = new google.maps.LatLngBounds();

		var infowindow = null;

		infowindow = new google.maps.InfoWindow({
			content: 'Loading...'
		});

		for (var i = 0; i < checkin_markers.length; i++) {
			var m = checkin_markers[i];
			if (m.lat == 0 || m.lng == 0) {
				continue;
			}

			var html = '<h4>' + m.org + '</h4><p><strong>Staff</strong>: ' + m.staff + '<br><strong>Session Time</strong>:<br>';

			m.lesson_times = Object.values(m.lesson_times);

			m.lesson_times.forEach(function(item){
				html += item + '<br>';
			});

			//not checked in at all
            if (m.not_checked_in != 1) {
                html += '<strong>Check-in Time</strong>:<br> ';

                m.checkin_times.forEach(function(item){
                    html += item + '<br>';
                });

                if (m.checkout_times.length > 0) {
                    html += '<strong>Check-out Time</strong>:<br> ';

                    m.checkout_times.forEach(function(item){
                        html += item + '<br>';
                    });
                }
			}

			var checkin_time = '';
            if ((m.checkin_time===null) || (m.checkin_time==='')) {
				checkin_time = m.checkin_time;
			} else {
                checkin_time = m.checkin_time.replace(/<[^>]*>/g, '');
			}

			var description;
			switch (m.colour) {
				case '0000FF':
					description = 'The member of staff has checked out of the session.';
					break;
				case 'FF0000':
					description = 'The member of staff hasn\'t checked in or checked in outside session area.';
					break;
                case 'FFBF00':
                    description = 'The member of staff has checked in late to the session.';
                    break;
				default:
					description = 'The member of staff is early or on time for the session and is at the correct location.';
					break;
			}

			if (m.accuracy != '') {
				html += '<strong>Accuracy</strong>: ' + m.accuracy + 'm';
			}
			html += '</p>';
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(parseFloat(m.lat), parseFloat(m.lng)),
				map: checkin_map,
				icon: 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + m.colour,
				title: m.staff + ' (' + checkin_time + ') - ' + description,
				html: html
			});

			bounds.extend(marker.position);
			google.maps.event.addListener(marker, 'click', function () {
				infowindow.setContent(this.html);
				infowindow.open(checkin_map, this);
			});
		}

		checkin_map.fitBounds(bounds);
	}

	// also in print.js
	function calc_shapeup_weight() {
		var target = 0.05;
		var lbs = 2.20462;

		$('#participants_overview, #names_register').find('tbody tr').each(function() {
			var target_loss_kg = 0;
			var target_loss_lbs = 0;
			var target_weight_kg = 0;
			var target_weight_lbs = 0;
			var current_loss_kg = 0;
			var current_loss_lbs = 0;
			var percent_lost = 0;

			var i = 0;
			var first_weight = 0;
			var last_weight = 0;
			$(this).find('.shapeup_weight').each(function() {
				var val = parseFloat($(this).val());
				if (i == 0) {
					first_weight = val;
				} else if (val > 0){
					last_weight = val;
				}
				i++;
			});

			if (first_weight > 0) {
				target_loss_kg = first_weight*target;
				target_loss_lbs = target_loss_kg*lbs;
				target_weight_kg = first_weight*(1-target);
				target_weight_lbs = target_weight_kg*lbs;
				if (last_weight > 0) {
					current_loss_kg = last_weight-first_weight;
					current_loss_lbs = current_loss_kg*lbs;
					percent_lost = (current_loss_kg/first_weight)*100;
				}
			}

			// to 1 decimal place
			$(this).find('.target_loss_kg').text(Math.round(target_loss_kg * 10) / 10);
			$(this).find('.target_loss_lbs').text(Math.round(target_loss_lbs * 10) / 10);
			$(this).find('.target_weight_kg').text(Math.round(target_weight_kg * 10) / 10);
			$(this).find('.target_weight_lbs').text(Math.round(target_weight_lbs * 10) / 10);
			$(this).find('.current_loss_kg').text(Math.round(current_loss_kg * 10) / 10);
			$(this).find('.current_loss_lbs').text(Math.round(current_loss_lbs * 10) / 10);
			var td_class = ''
			if (percent_lost <= -5) {
				td_class = 'green';
			} else if (percent_lost <= -2.5){
				td_class = 'orange';
			} else if (percent_lost == 0) {
				td_class = '';
			} else {
				td_class = 'red';
			}
			$(this).find('.percent_lost').text(Math.round(percent_lost * 10) / 10).removeClass('green orange red').addClass(td_class);
		});
	}
	if ($('#participants_overview, #names_register').length > 0) {
		calc_shapeup_weight();
	}

	/*

	if ($('form.settings').length > 0) {
		$('form.settings input[type=checkbox][data-toggle_fields]').each(function() {
			var toggle_fields_array = $(this).attr('data-toggle_fields').split(',');
			var toggle_fields_ids = new Array;
			for (var i = 0; i < toggle_fields_array.length; i++) {
				toggle_fields_ids.push('#' + toggle_fields_array[i]);
			}
			var toggle_fields = toggle_fields_ids.join(',');
			if ($(this).is(':checked')) {
				$(toggle_fields).closest('.form-group').show();
			} else {
				$(toggle_fields).closest('.form-group').hide();
			}
			$(this).on('change', function() {
				var toggle_fields_this = toggle_fields;
				if ($(this).is(':checked')) {
					$(toggle_fields_this).closest('.form-group').show();
				} else {
					$(toggle_fields_this).closest('.form-group').hide();
				}
			});
		});
	}

	$('#search_by').on('change', function(){
		switch (this.value) {
			case 'dates_period':
				$('#search_academic_year').closest('div').hide();
				$('#field_date_from').closest('div').show();
				$('#field_date_to').closest('div').show();
				break;
			default:
				$('#search_academic_year').closest('div').show();
				$('#field_date_from').closest('div').hide();
				$('#field_date_to').closest('div').hide();
				break;
		}
	});

	recountDynamicDivHeight();

	// prompt to exit cart (if not within some selected pages)
	if ($('.in-cart').length == 1 && containsAny(window.location.href, ['/booking/'])) {
		$('a:not([href*="/bookings"],[href*="/booking/"],[href*="/participants/view/"],[href*="/participants/payments/"], .ajax_toggle a, [href^="#"])').click(function() {
			var action = $(this).attr('href');

			var message_body = 'Do you want to close the cart before continuing?';

			BootstrapDialog.show({
				title: 'Confirmation',
				message: message_body,
				buttons: [{
					label: 'Yes',
					cssClass: 'btn-success',
					action: function() {
						window.location = '/booking/cart/close/?redirect=' + encodeURIComponent(action);
					}
				}, {
					label: 'No',
					//cssClass: 'btn-danger',
					action: function(dialogItself){
						window.location = action;
					}
				}]
			});

			return false;
		});
	}
	*/

	// use fixed width icons in tables
	$('table .far').addClass('fa-fw');

	$('.history-back').click(function(e) {
		e.preventDefault();
		window.history.back();
	});

	if ($('#booking_site_domain_type').length == 1) {
		function toggle_booking_site_domain_type() {
			$('#booking_subdomain, #booking_customdomain').closest('.form-group').hide();
			var val = $('#booking_site_domain_type').val();
			if (val == 'subdomain') {
				$('#booking_subdomain').closest('.form-group').show();
				$('#booking_customdomain').val('');
			} else if (val == 'customdomain') {
				$('#booking_customdomain').closest('.form-group').show();
				$('#booking_subdomain').val('');
			} else {
				$('#booking_subdomain, #booking_customdomain').val('');
			}
		}
		toggle_booking_site_domain_type();
		$('#booking_site_domain_type').on('change', function() {
			toggle_booking_site_domain_type();
		});
	}

	// disable multiple submissions for sessions list form
	// this could be applied globally to other forms, but test with ajax enabled forms first
	$('form#lessons').on('submit', function() {
		$(this).find(':submit').attr('disabled','disabled');
	});

	//Bookings Dashboard call
	var box_element = $("#booking_dashboard_alerts");
	if(box_element.length > 0){
		dashboard_request(box_element);
	}
});
/*
$('#start_date_order').on('click', function() {
	$('#search_form').submit();
});

//align message form to right side
if ($('div.attachments').length > 0) {
	$('.message-form').css('margin-top', $('div.attachments').height() + 'px');
}
*/

function setRecipients(users) {

	var usersArray = users.split(',');
	var selected = $('#staff_recipient').val();

	for (var i=0; i<usersArray.length; i++) {
		selected.push(usersArray[i]);
	}

	$('#staff_recipient').val(selected);
	$('#staff_recipient').trigger('change');
}

function setTemplate() {
	$.ajax({
		url: '/messages/template/' + $('#field_template').val(),
		type: 'POST',
		success: function(data) {
			$('.attachments_to_send ul').html('');
			$('#subject').val(data.template.subject);
			tinyMCE.activeEditor.setContent(data.template.message);
			data.attachments.forEach(function(attachment){
				$('.attachments_to_send ul').append(
					'<li>' +
					'<a href="/attachment/message_template/' + attachment.path + '" target="_blank">'+ attachment.name +'</a>' +
					'</li>'
				);
			});
		},
		error: function() {
			$('.attachments_to_send ul').html('');
			$('#subject').val('');
			tinyMCE.activeEditor.setContent('');
		}
	});
}

/*
function updateQueryStringParam(param, value) {
    baseUrl = [location.protocol, '//', location.host, location.pathname].join('');
    urlQueryString = document.location.search;
    var newParam = param + '=' + value,
        params = '?' + newParam;

    // If the "search" string exists, then build params from it
    if (urlQueryString) {
        keyRegex = new RegExp('([\?&])' + param + '[^&]*');
        // If param exists already, update it
        if (urlQueryString.match(keyRegex) !== null) {
            params = urlQueryString.replace(keyRegex, "$1" + newParam);
        } else { // Otherwise, add it to end of query string
            params = urlQueryString + '&' + newParam;
        }
    }
    window.history.replaceState({}, "", baseUrl + params);
}

function getQueryVariable(variable)
{
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if(pair[0] == variable){return pair[1];}
    }
    return(false);
}


function switchPage(direction, data) {
	var page = $('input[name="page"]').val();

	$('.table-pages tbody').empty();

    switch (direction) {
        case 'forward':
            page = parseInt(page) + 1;
            break;
        default:
            page = parseInt(page) - 1;
            break;
    }

    if (page > 0) {
        $('.prev-page').css('display', 'block');
    } else {
        $('.prev-page').css('display', 'none');
    }

	var offset = 50;
	var start = page * offset;
	var end = start + offset;

	var result = data.slice(start, end);

	var next_result = data.slice(end, end + offset);

	if (next_result.length < 1) {
        $('.next-page').css('display', 'none');
	} else {
        $('.next-page').css('display', 'block');
	}

	if (page > 0 && next_result.length > 0) {
		$('.separator').css('display','block');
	} else {
        $('.separator').css('display','none');
	}

	result.forEach(function(e) {
		var headcoaches = '';
		var leadcoaches = '';
		var assistantcoaches = '';
		e.headcoaches.forEach(function(h){
            headcoaches += h + '<br>';
		});
        e.leadcoaches.forEach(function(h){
            leadcoaches += h + '<br>';
        });
        e.assistantcoaches.forEach(function(h){
            assistantcoaches += h + '<br>';
        });

        var activity_type = e.activity_name || '';
        var type_name = e.type_name || '';
        var post_code = e.post_code || '';
        var main_contact = e.main_contact || '';
        var main_tel = e.main_tel || '';
		$('.table-pages tbody').append(
			'<tr>' +
				'<td>' + e.org + '</td>' +
				'<td>' + e.date + '</td>' +
				'<td>' + e.time + '</td>' +
				'<td>' + e.day + '</td>' +
				'<td>' + activity_type + '</td>' +
				'<td>' + type_name + '</td>' +
				'<td>' + post_code + '</td>' +
				'<td>' + e.class_size + '</td>' +
				'<td>' + headcoaches + '</td>' +
				'<td>' + leadcoaches + '</td>' +
				'<td>' + assistantcoaches + '</td>' +
				'<td>' + main_contact + '</td>' +
				'<td>' + main_tel + '</td>' +
			'</tr>'
		);
	});

    $('input[name="page"]').val(page);
}

$(document).ready(function() {
	var url = window.location.pathname;
    parts = url.split("/");
    if (parts[1] == 'bookings' && parts[2] == 'timetable') {
    	if (getQueryVariable('view')) {
    		$('.search-box').addClass('box-collapsed');
		}
	}

	if (parts[1] == 'checkins') {
        $('#field_view').change(function(e) {
            $(this).closest('form').submit();
		});
	}
});

*/
// Returns the ISO week of the date.
Date.prototype.getWeek = function() {
    var date = new Date(this.getTime());
    date.setHours(0, 0, 0, 0);
    // Thursday in current week decides the year.
    date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
    // January 4 is always in week 1.
    var week1 = new Date(date.getFullYear(), 0, 4);
    // Adjust to Thursday in week 1 and count number of weeks from date to week1.
    return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
}

function getDateRangeOfWeek(weekNo, y){
    var d1, numOfdaysPastSinceLastMonday, rangeIsFrom, rangeIsTo;
    d1 = new Date(''+y+'');
    numOfdaysPastSinceLastMonday = d1.getDay() - 1;
    d1.setDate(d1.getDate() - numOfdaysPastSinceLastMonday);
    d1.setDate(d1.getDate() + (7 * (weekNo - d1.getWeek())));
    rangeIsFrom = d1.getDate() + "/" + (d1.getMonth() + 1) + "/" + d1.getFullYear();
    d1.setDate(d1.getDate() + 6);
    rangeIsTo = d1.getDate() + '/' + (d1.getMonth() + 1) + "/" + d1.getFullYear() ;
    return [rangeIsFrom, rangeIsTo];
};

$('.week_navigate').click(function(event){
    event.preventDefault();
    var date = getDateRangeOfWeek($(this).attr('week'), $(this).attr('year'));
    $('#field_start_from').datepicker('setDate', date[0]);
    $('#field_start_to').datepicker('setDate', date[1]);
    $('#year-value').val($(this).attr('year'));
    $('#week-value').val($(this).attr('week'));
    $('#search-form').submit();
});

/*

function toggleProjectCode() {
	var codes = {};

	$('.code_active_checkbox').each(function() {
		codes[$(this).attr('code_id')] = $(this).prop('checked');
	});

	$.ajax({
		url: '/settings/projectcodes/updateAjax/',
		type: 'POST',
		data: {'codes': codes},
		success: function(data) {
			window.location.reload();
		}
	});
}
*/
$(function() {
	/*
	var t1, t2;
	var date = new RegExp('^([0-2][0-9]|(3)[0-1])(\\/)(((0)[0-9])|((1)[0-2]))(\\/)\\d{4}$');
	$("#marketing_report_field_date_to").change(function () {
		clearTimeout(t1);
		var val = $(this).val();
		t1 = setTimeout(function() {
			if (date.test(val)) {
				$('#marketing_report_field_lessons').closest('div').closest('div').removeClass('hidden');
				$('#marketing_report_field_postcode').parent('div').closest('div').removeClass('hidden');
			} else {
				$('#marketing_report_field_lessons').parent('div').closest('div').addClass('hidden');
				$('#marketing_report_field_postcode').parent('div').closest('div').addClass('hidden');
			}
			// do stuff when user has been idle for 1 second
		}, 100);
	});

	$("#marketing_report_field_date_from").change(function () {
		clearTimeout(t2);
		var val = $(this).val();
		t1 = setTimeout(function() {
			if (date.test(val)) {
				$('#marketing_report_field_lessons').closest('div').closest('div').removeClass('hidden');
				$('#marketing_report_field_postcode').parent('div').closest('div').removeClass('hidden');
			} else {
				$('#marketing_report_field_lessons').parent('div').closest('div').addClass('hidden');
				$('#marketing_report_field_postcode').parent('div').closest('div').addClass('hidden');
			}
			// do stuff when user has been idle for 1 second
		}, 100);

	});
	*/

	if($("#check_all").length) {

		var flag = 1;
		$("input[name='block_list[]']").each(function () {
			if ($(this).prop("checked") == false) {
				flag = 0;
			}
		});
		if (flag) {
			$("#check_all").prop("checked", true);
		}

		$("#check_all").change(function () {
			if ($(this).prop("checked") == true) {
				$("input[name='block_list[]']").prop("checked", true);
			} else {
				$("input[name='block_list[]']").prop("checked", false);
			}
		});

		$("input[name='block_list[]']").change(function () {
			var flag = 1;
			$("input[name='block_list[]']").each(function () {
				if ($(this).prop("checked") == false) {
					flag = 0;
				}
			});
			if (flag) {
				$("#check_all").prop("checked", true);
			}else{
				$("#check_all").prop("checked", false);
			}
		});
	}
});

if (document.getElementById("pin")!==null) {
	setInputFilter(document.getElementById("pin"), function (value) {
		return /^\d*\.?\d*$/.test(value); // Allow digits and '.' only, using a RegExp
	});

	function setInputFilter(textbox, inputFilter) {
		["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function (event) {
			textbox.addEventListener(event, function () {
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

function printModal(familyID = null){
	$('#pin1').val("");
	$('#pin2').val("");
	$('#pin3').val("");
	$('#pin4').val("");
	$('#errormsg').hide();
	$('#verification').show();
	$('#verification_pin').hide();
	$('#familyID').val(familyID);
	$( "#pin1" ).focus();
	$('#myModal').modal('show');
}

function resetPin(){
	$('#pin1').val("");
	$('#pin2').val("");
	$('#pin3').val("");
	$('#pin4').val("");
	$('#myModal').modal('hide');
}

function pickupPin(){
	$('#pin1').val("");
	$('#pin2').val("");
	$('#pin3').val("");
	$('#pin4').val("");
	$('#errormsg').hide();
	$('#verification').hide();
	$('#verification_pin').show();
	$( "#pin1" ).focus();
}

function singoutModel(){
	$('#hidden_flag_skip').val(1);
	var familyID = $('#familyID').val();
	if($('#participants_overview').is(":visible")){
		$('#click_'+familyID).click();
	}if($('#participants_overview1').is(":visible")){
		$('#click_mini_'+familyID).click();
	}if($('#participants_indivisual').is(":visible")){
		$('#click_'+familyID).click();
	}if($('#participants_indivisual1').is(":visible")){
		$('#click_mini_'+familyID).click();
	}
	$('#myModal').modal('hide');
}

function pinVerified() {
	var familyID = $('#familyID').val();
	var URL = $('#URL').val();

	var origionalPIN = $('#hidden_' + familyID).val();
	var pin1 = $('#pin1').val();
	var pin2 = $('#pin2').val();
	var pin3 = $('#pin3').val();
	var pin4 = $('#pin4').val();
	var inputPIN = pin1 + pin2 + pin3 + pin4;

	if (inputPIN == origionalPIN) {
		if($('#participants_overview').is(":visible")){
			$('#click_'+familyID).click();
		}if($('#participants_overview1').is(":visible")){
			$('#click_mini_'+familyID).click();
		}if($('#participants_indivisual').is(":visible")){
			$('#click_'+familyID).click();
		}if($('#participants_indivisual1').is(":visible")){
			$('#click_mini_'+familyID).click();
		}
		$('#myModal').modal('hide');
	} else {
		$('#errormsg').show();
	}
}


$('#blockform').submit(function(e){
	e.preventDefault();
	var addressID = $('#addressID').val();
	var old_address_id = $('#old_address_id').val();

	if(addressID != old_address_id && $(this).hasClass("has-sessions")){
		$('#myModal').modal("show");
	}else{
		$('#flag').val(1);
		this.submit();
	}
})

$('#Yesbutton').click(function(){
	$('#flag').val(1);
	document.getElementById("blockform").submit();
})
$('#Nobutton').click(function(){
	$('#flag').val(2);
	document.getElementById("blockform").submit();
});
function init_content_monitor() {
	var content = $('.customer-iframe');

	// The user did navigate away from the currently displayed iframe page. Show an animation
	var content_start_loading = function() {
		$("#view-customer-slide-out").find(".offcanvas-content").prepend('<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div>');
		content.css({'visibility': 'hidden'});
	}

	// Listen to messages sent from the content iframe
	var receiveMessage = function receiveMessage(e){
		var url = window.location.href,
			url_parts = url.split("/"),
			allowed = url_parts[0] + "//" + url_parts[2];

		// Only react to messages from same domain as current document
		if (e.origin !== allowed) return;
		// Handle the message
		switch (e.data) {
			case 'iframe_change': content_start_loading(); break;
		}
	};
	window.addEventListener("message", receiveMessage, false);

}
window.onunload = function() {
	// Notify top window of the unload event
	window.top.postMessage('iframe_change', '*');
};

function hide_show_session_types(){
	var status = false;
	var status1 = false;
	var count = 0;
	$("#types option:selected").each(function () {
		count = 1;
		var $this = $(this);
		if ($this.length) {
			var selText = $this.text();
			if(selText == 'Select All'){
				status = true;
			}
			if(selText == 'Deselect All'){
				status1 = true;
			}
		}
	});

	if(status == true){
		$('#types').html('');
		var o = $("<option/>", {value: "0", text: "Select All"});
		$('#types').append(o);
		var o = $("<option/>", {value: "All Session Types", text: "All Session Types"});
		$('#types').append(o);

		$('select option:contains("Select All")').text('Deselect All');
		$('#types').select2('destroy').find('option').prop('selected', 'selected').end().select2();
		$('#types').select2('destroy').find('option value[0]').prop('selected', false).end().select2();
		var options = $('#types option[value="0"]');
		options.prop('selected', false);
		$('#types').trigger('change.select2');
	}

	if(status1 == true || count == 0){
		$('#types').html('');
		$('#session_type option').clone().appendTo('#types');
		$('select option:contains("Deselect All")').text('Select All');
		if(status1 == true)
			$("#types").select2('destroy').find('option').prop('selected', false).end().select2()
	}

};
$(document).ready(function() {
	var height = $("#account_overridden").height();
	orientationChanged(height);

	window.onresize = function(){
		height = $("#account_overridden").height();
		orientationChanged(height);
	}

	function orientationChanged(height){
		if(window.matchMedia("(max-width: 767px)").matches) {
			if (height > 60 || height < 60) {
				$("#kt_header_mobile").css({'top': (height - 1) + "px"});
				$("#kt_content").css({'margin-top': (height - 1) + "px"});
				var header_height = $("#kt_header_mobile").height();
				$(".topbar").css({'margin-top': (height - 1) + "px"});
			}
		}else{
			$("#kt_content").css({'margin-top': ""});
			$(".topbar").css({'margin-top': ""});
		}
	}
});

if($("#view-notification-slide-out").length || $("#view-customer-slide-out").length) {
	var obj = "";
	var table_pos = 1;
	if ($("#view-notification-slide-out").length) {
		obj = "view-notification-slide-out";
		table_pos = 0;
	} else {
		obj = "view-customer-slide-out";
	}
	var sidebar = $('#kt_aside');
	var table = $('.table-striped');
	var tbl_width = 0;
	var i = 0;
	table.find('tbody td')
		.each(function () {
			tbl_width += $(this).width();
			if (i == table_pos) {
				return false;
			}
			i++;
		});

	var leftside_width = parseInt(sidebar.width()) + 20 + tbl_width;
	var total_width = parseInt($(window).width());
	$("#" + obj + ".offcanvas").css("width", (total_width - leftside_width - 10));
	$("#" + obj + ".offcanvas-right").css("right", '-' + (total_width - leftside_width - 10) + 'px');

	$(".notification-slide-out-toggle").click(function () {
		$("#" + obj + ".offcanvas-right").css("right", '0');
	});

	$("#notification-slide-out-close").click(function () {
		$("#" + obj + ".offcanvas-right").css("right", '-' + (total_width - leftside_width - 10) + 'px');
	});

	$(".customer-slide-out-toggle").click(function () {
		$("#view-customer-slide-out.offcanvas-right").css("right", '0');
	});

	$("#customer-slide-out-close").click(function () {
		$("#view-customer-slide-out.offcanvas-right").css("right", '-' + (total_width - leftside_width - 10) + 'px');
	});
}
//Participants Payment
$(document).ready(function() {
	if($("#contactID").length > 0){
		var options = [{id: 'card', text: 'Credit/Debit Card'},
			{id: 'cash', text: 'Cash'},
			{id: 'cheque', text: 'Cheque'},
			{id: 'direct debit', text: 'Direct Debit'},
			{id: 'childcare voucher', text: 'Childcare Voucher'},
			{id: 'credit note', text: 'Credit Note'},
			{id: 'other', text: 'Other'},
			{id: 'bacs', text: 'BACS'},
			{id: 'refund', text: 'Refund'}
		];

		$('#contactID').on('select2:select', function (e) {
			$('#method').html('');
			var newOption = {};
			$("#childcarevoucher_details").hide();
			$(".manual_payment").hide();
			if(e.params.data.id == "internal") {
				$.each(options, function (key, value) {
					if (value.id === 'credit note' || value.id === 'other' || value.id === 'refund') {
						newOption = new Option(value.text, value.id, false, false);
						$('#method').append(newOption);
					}
				});
			}else{
				$(".manual_payment").show();
				$.each(options, function (key, value) {
					if (value.id !== 'credit note' && value.id !== 'other' && value.id !== 'refund') {
						newOption = new Option(value.text, value.id, false, false);
						$('#method').append(newOption);
					}
				});
			}
			$('#method').trigger('change');
		});

		$('#method').on('select2:select', function (e) {
			let amount = $("#amount");
			amount.removeAttr("min max step");
			if(e.params.data.id == "credit note"){
				amount.attr({"type": "number", "min": "0", "step":"0.01"});
				return;
			}
			else if(e.params.data.id == "refund"){
				amount.attr({"type": "number", "max": "-1", "step":"0.01"});
				return;
			}
			amount.attr({"type": "text"});
			$("#childcarevoucher_details").hide();
			if(e.params.data.id == "childcare voucher"){
				$("#childcarevoucher_details").show();
			}
		});

		// toggle showing info notices
		$('#childcarevoucher_providerID').change(function() {
			$('.notices .notice:visible').slideUp();
			$('.notices .notice[data-provider="' + $(this).val() + '"]:hidden').slideDown();
		});
		$('.notices .notice[data-providerID="' + $('#childcarevoucher_providerID').val() + '"]:hidden').slideDown();
	}

	// submit search forms for export
	$(".export-search-submit").on('click', function(e) {
		e.preventDefault();
		var form = $('form.card-search');
		var orig_action = form.attr('action');
		// update action
		form.attr('action', $(this).attr('href'));
		form.attr('target',  $(this).attr('target'));
		form.trigger('submit');
		// revert action
		setTimeout(function() {
			form.attr('action', orig_action);
			form.removeAttr('target');
		}, 1000);
		return false;
	});
});

/* Open when someone clicks on the settings element */
function openNav() {
	var overlay = document.getElementById("settings-overlay-container");
	overlay.classList.add("overlay-animation");
	var purple_elem =  document.getElementById('account_overridden');
	var header =  document.getElementById('kt_header');
	var height = 0;
	if (typeof(purple_elem) != 'undefined' && purple_elem != null){
		height = purple_elem.offsetHeight;
	}
	if (typeof(header) != 'undefined' && header != null){
		height = height + header.offsetHeight;
	}
	document.getElementById("settings-overlay-container").style.top = height+'px';

}

/* Close when someone clicks on the "x" symbol inside the overlay */
function closeNav() {
	var overlay = document.getElementById("settings-overlay-container");
	overlay.classList.remove("overlay-animation");
}

if($('table#sessiontable').length == 1){
	$('body').on('click', '.gobutton', function(){
		$('.gobutton').attr('disabled', 'disabled');
		var action = $('#action').val();
		if(action == 'cancellation' || action == 'staffchange' || action == 'confirmation' || action == 'dbs' || action == 'staff' || action == 'note'){
			var length = $('[name="lessons[]"]:checked').length;
			if(length == 0 || length == null){
				$('.gobutton').removeAttr('disabled', 'disabled');
				var msg = '<div class="alert alert-custom alert-danger" style="margin:0" role="alert"><div class="alert-icon"><i class="far fa-exclamation-circle fa-2x"></i></div><div class="alert-text">Please select at least one session.</div></div>';
				$('#msg').html(msg);
				$('#myModal_message').modal("show");
				$('.modal-backdrop').hide();
				setTimeout(function(){
					$('#myModal_message').modal("hide");
				},5000);
			}else{
				$('#lessons').submit();
			}
		}
		var blockID = $("input[name=blockID]").val();
		$.ajax({
			url: '/sessions/bulk/'+blockID,
			type: 'POST',
			data: $('#lessons').serialize(),
			async: false,
			success: function(res){
				$('.gobutton').removeAttr('disabled', 'disabled');
				var data = JSON.parse(res);
				if(data.error == 1){
					var msg = '<div class="alert alert-custom alert-danger" style="margin:0" role="alert"><div class="alert-icon"><i class="far fa-exclamation-circle fa-2x"></i></div><div class="alert-text">'+data.message+'</div></div>';
					$('#msg').html(msg);
					$('#myModal_message').modal("show");
					$('.modal-backdrop').hide();
				}else{
					var msg = '<div class="alert alert-custom alert-success" style="margin:0" role="alert"><div class="alert-icon"><i class="far fa-check-circle "></i></div><div class="alert-text">'+data.message+'</div></div>';
					$('#msg').html(msg);
					$('#myModal_message').modal("show");
					$('.modal-backdrop').hide();
					session_list();
				}
				setTimeout(function(){
					$('#myModal_message').modal("hide");
				},5000);
			}
		});
	});
	var flag = $('#modalflag').val();
	if(flag != 0){
		$('html, body').animate({
			scrollTop: $("#scroll-table").offset().top
		}, 2000);

		$('#myModal_message').modal("show");
		$('.modal-backdrop').hide();
		setTimeout(function(){
			$('#myModal_message').modal("hide");
		},5000);
	}
	function session_list(){
		var blockID = $("input[name=blockID]").val();
		var bookingID = $("input[name=bookingID]").val();
		$.ajax({
			url: '/bookings/sessions/'+bookingID+'/'+blockID+'/1',
			type: 'GET',
			async: false,
			success: function(res){
				$('#ajaxData').html(res);
				if ($('#sessiontable tr select').data('select2')) {
					$('#sessiontable tr select').select2('destroy');
				}
				$('#sessiontable tr select').each(function() {
					$(this).select2();
				})
				if ($('#scroll-table select').data('select2')) {
					$('#scroll-table select').select2('destroy');
				}
				$('#scroll-table select').each(function() {
					$(this).select2();
				})
				$('#scroll-table .datepicker').each(function() {
					$(this).datepicker("destroy");
				})
				$('.datepicker').datepicker({
					dateFormat: 'dd/mm/yy',
					firstDay: 1,
					changeMonth: true,
					changeYear: true,
					yearRange: '-100:+10'
				});
				$("form#lessons td[data-tooltip]").qtip({
					content: {
						text: function() {
							return $('.' + $(this).attr("data-tooltip")).html();
						}
					},
					position: {
						target: 'mouse',
						viewport: $(window),
						adjust: {
							method: 'shift',
							x: 10,
							y: 10
						}
					}
				});

				if ($('.bulk-supplementary').length > 0) {
					handle_bulk_supplementary();
				}

				if ($(".bulk-checkboxes td input[type='checkbox']").length > 0) {
					var total = $("td input[type='checkbox']").length;
					var checked = $("td input[type='checkbox']:checked").length;
					if (checked == total) {
						$("th input[type='checkbox']").prop('checked', true);
						$('tfoot.bulk-actions:hidden').show();
					} else {
						$("th input[type='checkbox']").prop('checked', false);
						$('tfoot.bulk-actions:visible').hide();
					}
				}

				$('.bulk-supplementary select[data-toggleother]').each(function() {
					var toggle_items = $(this).attr('data-toggleother').split(' ');

					if ($(this).val().toLowerCase().indexOf("other") == -1) {
						for (var i = toggle_items.length - 1; i >= 0; i--) {
							$('#' + toggle_items[i]).closest('div').hide();
						};
					}

					$(this).change(function() {
						if ($(this).attr('toggle-subsections') == 1) {
							handle_bulk_subsections($(this).val());
						} else {
							if ($(this).val().toLowerCase().indexOf("other") >= 0) {
								for (var i = toggle_items.length - 1; i >= 0; i--) {
									$('#' + toggle_items[i]).closest('div').show();

								};
							} else {
								for (var i = toggle_items.length - 1; i >= 0; i--) {
									$('#' + toggle_items[i]).closest('div').hide();
								};
							}
						}
					});
				});
			}
		});
	}
}

if($("table#customerTable").length == 1) {
	addBackToTop({
		diameter: 37,
		backgroundColor: '#3699FF',
		textColor: '#fff'
	})
}

// staff Exception
if ($('tr.exception_info').length > 0) {
	function toggle_exception_type() {
		$('tr.exception_info').each(function() {
			var type = $('input[name^=type]', this).val();

			$('select[name^=fromID], select[name^=staffID]', this).closest('.form-group').hide();
			$('.reason', this).hide();

			if (type == 'staffchange') {
				$('select[name^=fromID], select[name^=staffID]', this).closest('.form-group').show();
				$('.reason', this).show();
			}
		});
	}

	toggle_exception_type();

	$('input[name^=type]').change(function() {
		toggle_exception_type();
	});

	// toggle reason other
	function exception_reason_other() {
		$('tr.exception_info').each(function() {
			$('input[name^=reason]', this).closest('.form-group').hide();
			var parent = this;
			$('select[name^=reason_select]', this).each(function() {
				if ($(this).val() == 'other') {
					$('input[name^=reason]', parent).closest('.form-group').show();
				}
			});
		});
	}

	exception_reason_other();

	$('select[name^=reason_select]', this).change(function() {
		exception_reason_other()
	});

	var reasonsClone = $('select[name^=reason_select]').eq(0).html();

	// toggle reason drop down
	function exception_reasons() {
		$('tr.exception_info').each(function() {

			if ($("select[name^=assign_to]", this).val() == '') {
				$('select[name^=reason_select]', this).html("<option value=\"\">Select assign to first</option>");
			} else {
				//get type
				var assigned = $("select[name^=assign_to]", this).val();

				// set default
				$('select[name^=reason_select]', this).html("<option value=\"\">Select</option>");

				// replace with original
				$('select[name^=reason_select]', this).append(reasonsClone);

				// remove any which dont match
				$('select[name^=reason_select] option', this).not("[data-assigned~='" + assigned + "']").remove();
			}


			// init select2
			$('select[name^=reason_select]', this).select2();
			exception_reason_other();
		});
	}

	exception_reasons();

	$('tr.exception_info').each(function() {
		if ($("input[name^=hidden_reason_select]", this).val() != '') {
			var val = $("input[name^=hidden_reason_select]", this).val();
			$("select[name^=reason_select]", this).val(val);
			exception_reason_other();
		}
	});

	$("select[name^=assign_to]").change(function() {
		exception_reasons()
	});

	var prev_assign = null;

	function copy_reasons() {

		if (prev_assign != $('tr.exception_info:eq(0) select[name^=assign_to]').val()) {

			var val = $('tr.exception_info:eq(0) select[name^=assign_to]').val();

			$('tr.exception_info:not(:first) select[name^=assign_to]').each(function() {
				$(this).val(val).trigger("change");
			});

			exception_reasons();

			prev_assign = val;
		}

		$('tr.exception_info:not(:first) select[name^=reason_select]').each(function() {
			var val = $('tr.exception_info:eq(0) select[name^=reason_select]').val();
			$(this).val(val);
		});

		exception_reason_other();

		$('tr.exception_info:not(:first) input[name^=reason]').each(function() {
			$(this).val($('tr.exception_info:eq(0) input[name^=reason]').val());
		});

	}

	$('tr.exception_info:eq(0) select[name^=reason_select], tr.exception_info:eq(0) input[name^=reason], tr.exception_info:eq(0) select[name^=assign_to]').change(function() {
		copy_reasons();
	});

	$('#staff_all').change(function(){
		var val = $('#staff_all').val();
		$('tr.exception_info select[name^=staffID]').each(function() {
			$(this).val(val).trigger('change');
		});
	});

}

$(document).ready(function() {

	$('#availabilities').on('select2:select', function (e) {
		var data = e.params.data;
		if(data.id){
			window.location.href = "/bookings/availabilitycal/"+data.id;
		}
	});

	$(".expand-filters").click(function () {
		var elements = $(this).closest( ".card-footer" ).prev().find(".row > div.d-none");
		var add_class="d-none";
		var remove_class="d-block";
		if($(this).data("state") == '1'){
			$(this).data("state", "0");
			elements = $(this).closest( ".card-footer" ).prev().find(".row > div.d-block");
			add_class="d-block";
			remove_class="d-none";
			$(this).find(".rotate-arrow").removeClass("down");
			$(this).find('span').html("Expand Search Filters");
		}else{
			$(this).find('span').html("Minimize Search Filters");
			$(this).data("state", "1");
			$(this).find(".rotate-arrow").addClass("down");
		}
		elements.each(function () {
			$(this).removeClass(add_class).fadeOut(0).fadeIn(500);
			$(this).addClass(remove_class).fadeIn(500);
		});
	});

	// minimum setup
	$('#kt_daterangepicker_1').daterangepicker({
		buttonClasses: ' btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary'
	});
});

$('#searchform').submit(function(e){
	e.preventDefault();
	var fieldVal = $("#field_is_balance_due").val();
	if(fieldVal == "3"){
		$('#myModal').modal("show");
	}else{
		this.submit();
	}
})

$("#Yestoexport").click(function(){
	$('#myModal').modal("hide");
	document.getElementById("searchform").submit();
});

if($("#myModal_message").length && $("#myModal_message").data("display") == 1){
	$('#myModal_message').modal("show");
	$('.modal-backdrop').hide();
	$('#myModal_message').find(".alert").css({'margin': '0'});
	setTimeout(function(){
		$('#myModal_message').modal("hide");
	},5000);
	$('#myModal_message').on('hidden.bs.modal', function () {
		show_refund_modal();
	});
}
else {
	show_refund_modal();
}

function show_refund_modal() {
	let refund_form = $(".refund-callback");
	if(refund_form.length) {
		var c_flag = getCookie("participant_refund_flag");
		if(c_flag == null) {
			BootstrapDialog.show({
				title: 'REFUND',
				message: 'Would you like to refund the price of the cancelled sessions to the account balances of each participant?',
				onhide : function(dialog) {
					//Modal was hidden by the "Yes" button wasnt clicked
					if (getCookie("participant_refund_flag")!==1) {
						setCookie("participant_refund_flag", '0', 60);
					}
				},
				buttons: [{
					label: 'Yes',
					cssClass: 'btn-success',
					action: function (dialogItself) {
						$.ajax({
							url: refund_form.attr("action"),
							type: 'POST',
							data: refund_form.serialize(),
							success: function(data) {
								if (data!=="OK") {
									alert("Error while refunding participants");
								}
								else{
									setCookie("participant_refund_flag", '1', 60);
								}
								dialogItself.close();
							}
						});
					}
				}, {
					label: 'No',
					action: function (dialogItself) {
						dialogItself.close();
					}
				}]
			});
		}
	}else{
		eraseCookie('participant_refund_flag');
	}
}

function setCookie(name,value,minutes) {
	var expires = "";
	if (minutes) {
		var date = new Date();
		date.setTime(date.getTime() + (minutes * 60 * 1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
function eraseCookie(name) {
	document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

if($(".slide-out-resizable").length > 0){
	var $view_bookings = $(".slide-out-resizable");
	$view_bookings.resizable({
		handles: "w",
		start: function (e, ui) {
			if (ui.position.left > 0) {
				ui.position.right = 0;
				ui.position.left = 'auto';
			}
		},
		resize: function (e, ui) {
			if (ui.position.left > 0) {
				ui.position.right = 0;
				ui.position.left = 'auto';
			}
		},
		stop: function (e, ui) {
			if (ui.position.left > 0) {
				ui.position.right = 0;
				ui.position.left = 'auto';
			}

		}
	});
}

$(document).ready(function() {

	if($('.less').length){
		if ($('.less')[0].scrollWidth <= $('.less').width()) {
			$(".text-size").hide();
		}
		$('.less').children(':first-child').children(':first-child').css({
			'word-wrap': 'break-word',
			'white-space': 'nowrap',
			'text-overflow': 'ellipsis',
			'overflow': 'hidden'
		});
	}

	$(".text-size").click(function(){
		if($(this).text() == "See More") {
			$(this).html("See Less");
			$(this).parent().children(':first-child').children(':first-child').removeAttr('style');
			$(this).parent().children(':first-child').children(':first-child').css({'white-space': 'normal'});
		}else{
			$(this).html("See More");
			$(this).parent().children(':first-child').children(':first-child').css({
				'word-wrap': 'break-word',
				'white-space': 'nowrap',
				'text-overflow': 'ellipsis',
				'overflow': 'hidden'
			});
		}
	});
});

if($('table#project_contract').length == 1){
	$('#search_by').change(function(){
		$('.'+$(this).val()).show();
		if($(this).val() == 'academic_year'){
			$('.dates_period').hide();
		}else{
			$('.academic_year').hide();
		}
	})
}

if($('.profit').length == 1){
	$('#week').change(function() {
		$('#search-form').submit();
	});
}

if($('.mileage_section').length == 1){
	$('#mileage_activate_fuel_cards').change(function(){
		if($(this).is(":checked")){
			$('.automatically_approve').show();
		}else{
			$('.automatically_approve').hide();
		}
	})
}
