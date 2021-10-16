$(document).ready(function(){
    var lastKey = $("input[name=last_key]").val();
    var dateFrom = $("#field_date_from").val();
    var dateTo = $("#field_date_to").val();
    var staffId = $("#field_staff_id").val();

    var inProgress = false;

    $(window).scroll(function() {
        if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
            if (lastKey > 0) {
                $.ajax({
                    url: '/user-activity/get-records',
                    method: 'POST',
                    data: {
                        "lastKey" : lastKey,
                        "dateFrom" : dateFrom,
                        "dateTo" : dateTo,
                        "staffId": staffId
                    },
                    beforeSend: function() {
                        inProgress = true;
                    }
                }).done(function(data){
                    data = jQuery.parseJSON(data);
                    if (data.data.length > 0) {
                        lastKey = data.last_key;
                        $.each(data.data, function(index, data){
                            $("#user-activity").append("<tr>" +
                                "<td>" + data.created_at + "</td>" +
                                "<td>" + data.info.action + "</td>" +
                                "<td>" + data.info.page_name + " - <a href='" + data.info.url + "' target='_blank'>" + data.info.url +
                                "</a></td>" +
                                "</tr>");
                        });

                        inProgress = false;
                    }
                });
            }
        }
    });
});
