$(document).on('click', function(event) {
	if (event.target.id !== 'input-field-edit-option'
		&& !$(event.target).hasClass('edit-icon') && $('#input-field-edit-option').length > 0) {
		sendEditRequest($('#input-field-edit-option'));
		replaceInput($('#input-field-edit-option'));
	}
});

$(document).on('click', 'td .edit-icon', function(e) {
	var td = e.target.closest('td');

	if ($('#input-field-edit-option').length > 0) {
		sendEditRequest($('#input-field-edit-option'));
		replaceInput($('#input-field-edit-option'));
	}

	var value = $(td).find('.name')[0].innerHTML;

	var width = $(td).width();
	var input = '<input type="text" class="form-control editable-input" id="input-field-edit-option" autofocus style="width: '+width+'px">';


	td.innerHTML = input;
	$('.editable-input').val(value);
});

function resizeInput() {
	var td = $('#input-field-edit-option').closest('td');
	$('#input-field-edit-option').css('width', $(td).width() + 'px');
}

timeout = null;
$(document).on('keypress', '#input-field-edit-option', function (e) {
	if(e.which == 13) {
		var id = $(e.target).closest('tr').attr('record-id');
		var name = $(e.target).val();
		var that = this;

		editRecord(id, name);
		replaceInput(e.target);
	}
});

$(document).on('mouseenter', 'td.editable-td', function(e) {
	$(e.target).find('.edit-icon').css('display', 'inline');
});

$(document).on('mouseleave', 'td.editable-td', function(e) {
	$(e.target).find('.edit-icon').css('display', 'none');
});

function replaceInput(input) {
	var td = $(input).closest('td');
	var tr = $(td).closest('tr');
	var input = $(td).find('input#input-field-edit-option');
	var value = input.val();

	//if empty value is entered - take last not empty value
	if ($.trim(value) == '') {
		value = $(tr).attr('original-value');
	}

	td[0].innerHTML = '<span class="name">' + value + '</span>' +
		'<div class="block-extend-cell-width"><i class="edit-icon far fa-pencil"></i></div>';
}

function sendEditRequest(input) {
	var id = $(input).closest('tr').attr('record-id');
	var name = $(input).val();

	editRecord(id, name);
}

function editRecord(id, name) {
	if ($.trim(name) !== '') {
		$.ajax({
			url: $('#update-url').val() + id,
			type: "POST",
			data: 'name='+name+"&"+$('#token').attr("name")+'='+$('#token').val(),
			success: function (res) {
				setOriginalValue(id, name);
			},
			error: function(xhr, status, error) {
				console.log(xhr.responseText);
			}
		});
	}
}

function setOriginalValue(id, name) {
	var tr = $('tr[record-id=' + id + ']');
	$(tr).attr('original-value', name);
}

function addRecordToTheTable(id, name) {
	if ($('.records-table').length < 1) {
		$('.result').html(
			'<div class="card-body"><table class="table records-table">' +
			'<tbody></tbody>' +
			'</table></div>'
		);
	}

	if ($('.records-table').length > 0) {
		$('.records-table tbody').prepend(
			'<tr record-id="'+ id +'" original-value="'+ name +'">' +
			'<td class="editable-td pl-0">' +
			'<span class="name">'+ name +'</span>' +
			'<div class="block-extend-cell-width"><i class="edit-icon far fa-pencil"></i></div>' +
			'</td>' +
			'<td class="actions">' +
			'<a class="delete confirm-delete" href="'+ $('#remove-url').val() + id +'" title="Remove">' +
			'<i class="far fa-times"></i>' +
			'</a>' +
			'</td>' +
			'</tr>'
		);
	}
}

function removeItemFromTheTable(id) {
	$('tr[record-id='+ id +']').remove();

	if ($('table.records-table tr').length < 1) {
		$('.result').html('<div class="card-body"><div class="alert alert-info">' +
			'<i class="far fa-info-circle"></i>' + ' No records found.' +
		'</div></div>');
	}
}

$('#add-form').submit(function(e){
	e.preventDefault();
	var $form = $(this);

	var name = $(e.target).find('input[name="name"]').val();

	if ($.trim(name) != '') {
		$.ajax({
			url: $form.attr('action'),
			type: $form.attr('method'),
			data: $form.serialize(),
			success: function (res) {
				res = $.parseJSON(res);
				if (res.status == 'ok') {
					addRecordToTheTable(res.id, res.name);
				}
			},
			error: function(xhr, status, error) {
				console.log(xhr.responseText);
			}
		});
	}

	$(e.target).find('input[name="name"]').val('');
});

$(document).on('click', '.delete', function(e){
	e.preventDefault();

	var name = $(e.target).closest('tr').attr('original-value');
	var id = $(e.target).closest('tr').attr('record-id');
	var removeUrl = $('#remove-url').val() + id;

	BootstrapDialog.show({
		title: 'Confirmation',
		message: 'Are you sure you want to delete ' + name,
		buttons: [{
			label: 'Confirm Removal',
			cssClass: 'btn-success',
			action: function(dialogItself) {
				$.ajax({
					url: removeUrl,
					type: 'GET',
					success: function (res) {
						res = $.parseJSON(res);
						if (res.status == 'ok') {
							removeItemFromTheTable(id);
						}
					},
					error: function(xhr, status, error) {
						console.log(xhr.responseText);
					}
				});
				dialogItself.close();
			}
		}, {
			label: 'Cancel',
			//cssClass: 'btn-danger',
			action: function(dialogItself){
				dialogItself.close();
			}
		}]
	});
});


