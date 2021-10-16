$(document).ready(function() {

	if($('.select2').length > 0) {
		$('.select2').select2();
	}

	$('a.print').click(function() {
		window.print();
		return false;
	});

	$('textarea').click(function () {
		$(this).attr("rows",5);
	});

	$('textarea').blur(function () {
		$(this).attr("rows",1);
		var input = this;
		$(this).attr("disabled", "disabled");
		$.ajax({
			url: $(this).data('url')+"/"+encodeURIComponent($(this).val()),
			type: 'GET',
			success: function(data) {
				$(input).removeAttr("disabled");
				$('#myModal').modal("hide");
				if (data !== 'OK') {
					alert('Error saving data. Please try again.');
				}
			}
		});
		return false;
	});

	$('input[name="monitor_register_value_popup"]').blur(function () {
		var input = this;
		$(this).attr("disabled", "disabled");
		$.ajax({
			url: $(this).data('url')+"/"+encodeURIComponent($(this).val()),
			type: 'GET',
			success: function(data) {
				$(input).removeAttr("disabled");
				$('#myModal').modal("hide");
				if (data !== 'OK') {
					alert('Error saving data. Please try again.');
				}else{
					location.reload();
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

		if(pin1 == "" && pin != '' && pin != 0 && hidden_flag_skip == 0 && $(this).attr('title') == 'Signout'){
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
					} else {
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
	$('.shapeup_weight[data-action]').change(function() {
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

	// bikeability register bulk actions
    $('#participants_overview').find('select.bulk').change(function() {
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

	$(".bulk-checkboxes th input[type='checkbox']").click(function() {
		var target = $(this).closest('table');
		if ($(this).is(":checked")) {
			$("td input[type='checkbox']", target).prop('checked', true);
            $('tfoot.bulk-actions:hidden').show();
		} else {
			$("td input[type='checkbox']", target).removeAttr("checked");
            $('tfoot.bulk-actions:visible').hide();
		}
	});
	$(".bulk-checkboxes tbody").on('click', "td input[type='checkbox']", function() {
		var target = $(this).closest('table');
		total = $("td input[type='checkbox']", target).length;
		checked = $("td input[type='checkbox']:checked").length;
        if ($(this).is(':checked')) {
            $('tfoot.bulk-actions:hidden').show();
        }
		if (checked == total) {
			$("th input[type='checkbox']", target).prop('checked', true);
		} else {
			$("th input[type='checkbox']", target).removeAttr("checked");
		}
        if (checked == 0) {
            $('tfoot.bulk-actions:visible').hide();
        }
	});
    if ($(".bulk-checkboxes td input[type='checkbox']").length > 0) {
		total = $("td input[type='checkbox']").length;
		checked = $("td input[type='checkbox']:checked").length;
		if (checked == total) {
			$("th input[type='checkbox']").prop('checked', true);
            $('tfoot.bulk-actions:hidden').show();
		} else {
			$("th input[type='checkbox']").removeAttr("checked");
            $('tfoot.bulk-actions:visible').hide();
		}
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

});

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

function pinVerified(){
	var familyID = $('#familyID').val();
	var URL = $('#URL').val();

	var origionalPIN = $('#hidden_'+familyID).val();
	var pin1 = $('#pin1').val();
	var pin2 = $('#pin2').val();
	var pin3 = $('#pin3').val();
	var pin4 = $('#pin4').val();
	var inputPIN = pin1+pin2+pin3+pin4;

	if(inputPIN == origionalPIN){
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
	}else{
		$('#errormsg').show();
	}
}

$('#filterdate').change(function(e){
	$('.mobilescreen').addClass("d-none");
	var val = $(this).val();
	$('#th1_'+val).removeClass("d-none");
	$('#th2_'+val).removeClass("d-none");
	$('#th3_'+val).removeClass("d-none");
	var count = $('#count').val();
	var filter = $('#filter').val();
	for(i=1; i<=count; i++){
		$('#tr_'+i).removeClass("d-none");
	}
	var arr = [];
	for(i=1; i<=count; i++){
		if(filter != null && filter != ""){
			for(j=0; j<filter.length; j++){
				if($('#tr_'+i).hasClass(filter[j]+val)){
					arr.push(i);
				}
			}
		}else{
			$('#tr_'+i).removeClass("d-none");
			$('#'+i+'td1_'+val).removeClass("d-none");
			$('#'+i+'td2_'+val).removeClass("d-none");
		}
	}
	if(filter.length > 0){
		for(i=1; i<=count; i++){
			if($.inArray(i,arr) >= 0){
				$('#tr_'+i).removeClass("d-none");
				$('#'+i+'td1_'+val).removeClass("d-none");
				$('#'+i+'td2_'+val).removeClass("d-none");
			}else{
				$('#tr_'+i).addClass("d-none");
				$('#'+i+'td1_'+val).addClass("d-none");
				$('#'+i+'td2_'+val).addClass("d-none");
			}
		}
	}
})

$('#filter').change(function(e){
	$('.mobilescreen').addClass("d-none");
	var val = $('#filterdate').val();
	$('#th1_'+val).removeClass("d-none");
	$('#th2_'+val).removeClass("d-none");
	$('#th3_'+val).removeClass("d-none");
	var count = $('#count').val();
	var filter = $(this).val();
	for(i=1; i<=count; i++){
		$('#tr_'+i).removeClass("d-none");
	}
	var arr = [];
	for(i=1; i<=count; i++){
		if(filter != null && filter != ""){
			for(j=0; j<filter.length; j++){
				if($('#tr_'+i).hasClass(filter[j]+val)){
					arr.push(i);
				}
			}
		}else{
			$('#tr_'+i).removeClass("d-none");
			$('#'+i+'td1_'+val).removeClass("d-none");
			$('#'+i+'td2_'+val).removeClass("d-none");
		}
	}
	if(filter.length > 0){
		for(i=1; i<=count; i++){
			if($.inArray(i,arr) >= 0){
				$('#tr_'+i).removeClass("d-none");
				$('#'+i+'td1_'+val).removeClass("d-none");
				$('#'+i+'td2_'+val).removeClass("d-none");
			}else{
				$('#tr_'+i).addClass("d-none");
				$('#'+i+'td1_'+val).addClass("d-none");
				$('#'+i+'td2_'+val).addClass("d-none");
			}
		}
	}
})

$('#filterdate_overview').change(function(e){
	$('.mobilescreen').addClass("d-none");
	var val1 = $(this).val();
	val1 = val1.replace("/","");

	var data = $("#"+val1).val();

	var a = [];
	a = data.split(",");
	$('#filtertime').html("");
	var c = [];
	for(var i=0; i< a.length; i++){
		var v = a[i].replace(/\:/g,"");
		if($.inArray(a[i], c) == -1){
			c.push(a[i]);
			$('#filtertime').append("<option value='"+v+"'>" + a[i] + "</option>");
		}
	}


	var filtertime = $('#filtertime').val();
	var val = $(this).val();

	$('#th1_'+val+filtertime).removeClass("d-none");
	$('#th2_'+val+filtertime).removeClass("d-none");
	$('#th3_'+val+filtertime).removeClass("d-none");
	$('#th4_'+val+filtertime).removeClass("d-none");

	var count = $('#count').val();
	var filter = $('#filteroverview').val();
	for(i=1; i<=count; i++){
		$('#tr_'+i).removeClass("d-none");
	}
	var arr = [];
	for(i=1; i<=count; i++){
		if(filter != null && filter != ""){
			for(j=0; j<filter.length; j++){
				if($('#tr_'+i).hasClass(filter[j]+val+filtertime)){
					arr.push(i);
				}
			}
		}else{
			$('#tr_'+i).removeClass("d-none");
			$('.'+i+'td1_'+val+filtertime).removeClass("d-none");
			$('.'+i+'td2_'+val+filtertime).removeClass("d-none");
		}
	}
	if(filter.length > 0){
		for(i=1; i<=count; i++){
			if($.inArray(i,arr) >= 0){
				$('#tr_'+i).removeClass("d-none");
				$('.'+i+'td1_'+val+filtertime).removeClass("d-none");
				$('.'+i+'td2_'+val+filtertime).removeClass("d-none");
			}else{
				$('#tr_'+i).addClass("d-none");
				$('.'+i+'td1_'+val+filtertime).addClass("d-none");
				$('.'+i+'td2_'+val+filtertime).addClass("d-none");
			}
		}
	}

})
$('#filteroverview').change(function(e){
	$('.mobilescreen').addClass("d-none");
	var val1 = $('#filterdate_overview').val();
	val1 = val1.replace("/","");

	var data = $("#"+val1).val();

	var a = [];
	a = data.split(",");
	$('#filtertime').html("");
	for(var i=0; i< a.length; i++){
		var v = a[i].replace(/\:/g,"");
		$('#filtertime').append("<option value='"+v+"'>" + a[i] + "</option>");
	}


	var filtertime = $('#filtertime').val();
	var val = $('#filterdate_overview').val();

	$('#th1_'+val+filtertime).removeClass("d-none");
	$('#th2_'+val+filtertime).removeClass("d-none");
	$('#th3_'+val+filtertime).removeClass("d-none");
	$('#th4_'+val+filtertime).removeClass("d-none");

	var count = $('#count').val();
	var filter = $(this).val();
	for(i=1; i<=count; i++){
		$('#tr_'+i).removeClass("d-none");
	}
	var arr = [];
	for(i=1; i<=count; i++){
		if(filter != null && filter != ""){
			for(j=0; j<filter.length; j++){
				if($('#tr_'+i).hasClass(filter[j]+val+filtertime)){
					arr.push(i);
				}
			}
		}else{
			$('#tr_'+i).removeClass("d-none");
			$('.'+i+'td1_'+val+filtertime).removeClass("d-none");
			$('.'+i+'td2_'+val+filtertime).removeClass("d-none");
		}
	}
	if(filter.length > 0){
		for(i=1; i<=count; i++){
			if($.inArray(i,arr) >= 0){
				$('#tr_'+i).removeClass("d-none");
				$('.'+i+'td1_'+val+filtertime).removeClass("d-none");
				$('.'+i+'td2_'+val+filtertime).removeClass("d-none");
			}else{
				$('#tr_'+i).addClass("d-none");
				$('.'+i+'td1_'+val+filtertime).addClass("d-none");
				$('.'+i+'td2_'+val+filtertime).addClass("d-none");
			}
		}
	}
})

$('#filtertime').change(function(e){
	$('.mobilescreen').addClass("d-none");
	var filterdate = $('#filterdate_overview').val();
	var val = $(this).val();

	$('#th1_'+filterdate+val).removeClass("d-none");
	$('#th2_'+filterdate+val).removeClass("d-none");
	$('#th3_'+filterdate+val).removeClass("d-none");
	$('#th4_'+filterdate+val).removeClass("d-none");

	var count = $('#count').val();
	var filter = $('#filteroverview').val();
	for(i=1; i<=count; i++){
		$('#tr_'+i).removeClass("d-none");
	}
	var arr = [];
	for(i=1; i<=count; i++){
		if(filter != null && filter != ""){
			for(j=0; j<filter.length; j++){
				if($('#tr_'+i).hasClass(filter[j]+filterdate+val)){
					arr.push(i);
				}
			}
		}else{
			$('#tr_'+i).removeClass("d-none");
			$('.'+i+'td1_'+filterdate+val).removeClass("d-none");
			$('.'+i+'td2_'+filterdate+val).removeClass("d-none");
		}
	}
	if(filter.length > 0){
		for(i=1; i<=count; i++){
			if($.inArray(i,arr) >= 0){
				$('#tr_'+i).removeClass("d-none");
				$('.'+i+'td1_'+filterdate+val).removeClass("d-none");
				$('.'+i+'td2_'+filterdate+val).removeClass("d-none");
			}else{
				$('#tr_'+i).addClass("d-none");
				$('.'+i+'td1_'+filterdate+val).addClass("d-none");
				$('.'+i+'td2_'+filterdate+val).addClass("d-none");
			}
		}
	}
})

function viewModel(val = null, URL = null){
	$('#monitor_register_value_popup').val(val);
	if(URL == null){
		$('#monitor_register_value_popup').attr("disabled", "disabled");
	}else{
		$('#monitor_register_value_popup').attr("data-url",URL);
		$('#monitor_register_value_popup').removeAttr("disabled", "disabled");
	}

	$('#myModal1').modal("show");
}

function hide_show(id){
	if(id == 'collapse'){
		$('#collapse').hide();
		$('.expand').slideDown();
		$('#expand').show();
	}else{
		$('.expand').slideUp()
		$('#expand').hide();
		$('#collapse').show();
	}

}


