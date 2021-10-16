function addStaff(e) {
    var exists = $('#group_users_table tbody tr#tr-staff-' + e.value).length;
    if (parseInt(e.value) > 0 && exists < 1) {
        $('#group_users_table').css('display', 'inline-table');
        $('.group-staff-alert').css('display', 'none');

        var usersCount = $('#group_users_table tbody tr').length;

        $('.added_staff').append('<input id="added-staff-'+ e.value +'" type="hidden" name="staffId[]" value="'+ e.value +'">');

        $('#group_users_table > tbody:last-child').append(
            '<tr id="tr-staff-'+ e.value +'">' +
            '<td class="staff-count width-1p">' +
            parseInt(usersCount + 1) +
            '</td>' +
            '<td>' +
            e.options[e.selectedIndex].text +
            '</td>' +
            '<td class="text-right width-1p">' +
            '<a class="btn btn-danger btn-xs" title="Remove" onclick="removeStaff('+ e.value +');"><i class="far fa-trash"></i></a>' +
            '</td>' +
            '</tr>'
        );
    }
}

function removeStaff(id) {
    $('tr#tr-staff-' + id).remove();
    $('input#added-staff-' + id).remove();
    var usersCount = $('#group_users_table tbody tr').length;

    if (usersCount < 1) {
        $('#group_users_table').css('display', 'none');
        $('.group-staff-alert').css('display', 'block');
    }

    recountStaff();
}

function recountStaff() {
    if ($('#group_users_table tbody tr').length > 0) {
        var i = 0;
        $('#group_users_table tbody tr').each(function () {
            i++;
            $(this).find('.staff-count').html(i);
        });
    }
}
