$(document).ready(function() {
	$('.project-list').change(function () {
		var projectId = $(this).val();
		$.ajax({
			url: '/participants/subscriptions/get_session_type/' + projectId,
			type: 'GET',
			success: function (response) {
				$("#types").select2("destroy");
				$("#types").empty();
				if(response.result === "SUCCESS"){
					$("#types").append('<option value="0">Select All</option>');
					$.each(response.data, function (index, data) {
						var newOption = new Option(data, index, false, false);
						$('#types').append(newOption);
					});
					$('#types').select2();
				}else{
					$("#types").append('<option value="">No Session Types</option>');
				}
			}
		});
	});
});
