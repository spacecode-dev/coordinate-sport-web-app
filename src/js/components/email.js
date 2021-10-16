$( document ).ready(function() {
    $('#contactID').on('change', function() {
        $("#cc option").prop('disabled', false);
        $("#bcc option").prop('disabled', false);
        $("#cc option[value="+ $(this).val() +"]").prop('disabled', true);
        $("#bcc option[value="+ $(this).val() +"]").prop('disabled', true);

        refreshSelects();
    });
});

function addAdditionalEmail(type) {
    $("#add_" + type).before('<input type="text" name="extra-' + type + '[]" id="extra-' + type + '" class="extra-email form-control" maxlength="200">');
}

function refreshSelects() {
    $("#cc").val($("#cc").val());
    $("#cc").trigger('change');
    $("#bcc").val($("#bcc").val());
    $("#bcc").trigger('change');

    $("#cc").select2('destroy');
    $("#cc").select2();
    $("#bcc").select2('destroy');
    $("#bcc").select2();
}

function attachDataToEmail(bookingId, qualId) {
    var checked = $('#qual-' + qualId).prop("checked");
    $.ajax({
        url: '/bookings/confirmation/qualifications_data/' + bookingId + '/' + qualId,
        type: 'GET',
        success: function(data) {
            var content = tinyMCE.activeEditor.getContent();
            if (checked) {
                if (data.attach_to_email) {
                    var table = '<div class="'+ qualId +'"><p><strong>' + data.defalt_quals[qualId] + '</strong></p>' +
                        '<table border="1" width="100%">' +
                        '<tbody>' +
                        '<tr>' +
                        '<th>' +
                        'Staff Name' +
                        '</th>' +
                        ($.inArray(qualId, ['first', 'child']) != -1 ? '' :
                            '<th>' +
                            'DBS No.' +
                            '</th>') +
                        '<th>' +
                        'Issue Date' +
                        '</th>' +
                        '<th>' +
                        'Expiry Date' +
                        '</th>' +
                        '</tr>';
                    $.each(data.data, function (index, value) {
                        var ref = value[qualId].ref || 'Unknown'
                        table +=
                            '<tr>' +
                            '<td>' + value.name + '</td>' +
                            ($.inArray(qualId, ['first', 'child']) != -1 ? '' :
                                '<td>' +
                                ref +
                                '</td>') +
                            '<td>' + value[qualId].issue_date + '</td>' +
                            '<td>' + value[qualId].expiry_date + '</td>' +
                            '</tr>';
                    });
                    table +=
                        '</tbody>' +
                        '</table></div>';
                    tinyMCE.activeEditor.setContent(content + table);
                }
                $.each(data.data, function (index, value) {
                    if ((Object.keys(value.attachments)).length > 0) {
						if (value.attachments[qualId]) {
							$('.email-attachments').append(
								'<p class="email-attachment-'+qualId+'">' +
								'<a href="/attachment/staff/' + value.attachments[qualId].path + '">' +
								value.attachments[qualId].name +
								'</a>' +
								'</p>' +
								'<input type="hidden" class="email-attachment-'+qualId+'" name="addition_attachment[]" value="'+ value.attachments[qualId].attachmentID +'">');
						}
                    }
                });
            } else {
                if ($('.email-attachment-' + qualId).length > 0) {
                    $('.email-attachment-' + qualId).remove();
                }
                tinyMCE.activeEditor.dom.remove(tinymce.activeEditor.dom.select('div.' + qualId));
            }
        }
    });
}

function attachDataToEmailLessons(qualId) {
    var checked = $('#qual-' + qualId).prop("checked");

    if ($("input[name='lessons[]']").length < 1) {
        return false;
    }

    var lessons = [];
    $("input[name='lessons[]']").each(function() {
        lessons.push($(this).val());
    });

    $.ajax({
        url: '/bookings/confirmation/qualifications_data_by_lesson/' + qualId,
        type: 'POST',
        data: {'lessons': lessons},
        success: function(data) {
            var content = tinyMCE.activeEditor.getContent();
            if (checked) {
                if (data.attach_to_email) {
                    var table = '<div class="'+ qualId +'"><p><strong>' + data.defalt_quals[qualId] + '</strong></p>' +
                        '<table border="1" width="100%">' +
                        '<tbody>' +
                        '<tr>' +
                        '<th>' +
                        'Staff Name' +
                        '</th>' +
                        ($.inArray(qualId, ['first', 'child']) != -1 ? '' :
                            '<th>' +
                            'DBS No.' +
                            '</th>') +
                        '<th>' +
                        'Issue Date' +
                        '</th>' +
                        '<th>' +
                        'Expiry Date' +
                        '</th>' +
                        '</tr>';
                    $.each(data.data, function (index, value) {
                        var ref = value[qualId].ref || 'Unknown'
                        table +=
                            '<tr>' +
                            '<td>' + value.name + '</td>' +
                            ($.inArray(qualId, ['first', 'child']) != -1 ? '' :
                                '<td>' +
                                ref +
                                '</td>') +
                            '<td>' + value[qualId].issue_date + '</td>' +
                            '<td>' + value[qualId].expiry_date + '</td>' +
                            '</tr>';
                    });
                    table +=
                        '</tbody>' +
                        '</table></div>';
                    tinyMCE.activeEditor.setContent(content + table);
                }
                $.each(data.data, function (index, value) {
                    if ((Object.keys(value.attachments)).length > 0) {
                        if (value.attachments[qualId]) {
                            $('.email-attachments').append(
								'<p class="email-attachment-'+qualId+'">' +
									'<a href="/attachment/staff/' + value.attachments[qualId].path + '">' +
										value.attachments[qualId].name +
									'</a>' +
								'</p>' +
								'<input type="hidden" class="email-attachment-'+qualId+'" name="addition_attachment[]" value="'+ value.attachments[qualId].attachmentID +'">');
                        }
                    }
                });
            } else {
                if ($('.email-attachment-' + qualId).length > 0) {
                    $('.email-attachment-' + qualId).remove();
                }
                tinyMCE.activeEditor.dom.remove(tinymce.activeEditor.dom.select('div.' + qualId));
            }
        }
    });
}
