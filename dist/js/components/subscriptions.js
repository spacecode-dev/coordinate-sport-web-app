$( document ).ready(function() {
	var card_message = $('.subscription-message');
	$(document).on("click", ".inactive-sub" , function(e) {
		e.stopPropagation();
		var id = $(this).data("id");
		var provider = $(this).data("provider");
		var button_ele = $(this);
		var is_permanent = $(this).is("[data-permanent]");
		card_message.html('');
		var message_body = "Are you sure you want to cancel this subscription? This will stop any future payments.";
		if(provider == "gocardless" && !is_permanent){
			message_body = "Are you sure you want to cancel this subscription? This will stop any future payments and it cannot reactivate again.";
		}
		BootstrapDialog.show({
			title: 'Confirmation',
			message: message_body,
			buttons: [{
				label: 'Confirm',
				cssClass: 'btn-success',
				action: function(dialogItself) {
					dialogItself.close();

					button_ele.innerHTML = '';
					button_ele.html('<i class="far fa-fw spinner spinner-white"></i>');
					button_ele.addClass("confirm");

					fetch('/participants/subscriptions/inactive_participant_subscription/'+id, {
						method: 'GET',
						headers: {'Content-Type': 'application/json'},
					}).then(function (confirmResult) {
						confirmResult.json().then(function (json) {
							if (json.error) {
								displayError(json);
								button_ele.html('Cancel');
								if(is_permanent){
									button_ele.html("<i class='far fa-ban'></i>");
								}
							} else {
								card_message.html('<div class="alert alert-custom alert-success" role="alert"><div class="alert-icon"><i class="far fa-check-circle "></i></div><div class="alert-text">' + json.success + '</div></div');
								button_ele.hide();
								if(provider != "gocardless") {
									var activateButton = document.createElement('a');
									activateButton.setAttribute('href', 'javascript:void(0);');
									activateButton.setAttribute('class', 'btn font-weight-bold btn-sm btn-success activate-sub');
									activateButton.setAttribute('title', 'Activate');
									activateButton.setAttribute('data-id', id);
									activateButton.innerHTML = 'Activate';
									if(is_permanent){
										activateButton.innerHTML = "<i class='far fa-check-circle'></i>";
									}
									button_ele.after(activateButton);
								}
							}
						});
					});
				}
			}, {
				label: 'Cancel',
				action: function(dialogItself){
					dialogItself.close();
				}
			}]
		});
	});

	$(document).on("click", ".activate-sub" , function(e) {
		e.stopPropagation();
		var id = $(this).data("id");
		var button_ele = $(this);
		card_message.html('');
		var is_permanent = $(this).is("[data-permanent]");
		var message_body = "Are you sure you want to activate this subscription? This will start all future payments.";
		BootstrapDialog.show({
			title: 'Confirmation',
			message: message_body,
			buttons: [{
				label: 'Confirm',
				cssClass: 'btn-success',
				action: function(dialogItself) {
					dialogItself.close();
					button_ele.html('<i class="far fa-fw spinner spinner-white"></i>');
					button_ele.addClass("confirm");
					fetch('/participants/subscriptions/activate_participant_subscription/'+id, {
						method: 'GET',
						headers: {'Content-Type': 'application/json'},
					}).then(function (confirmResult) {
						confirmResult.json().then(function (json) {
							if (json.error) {
								displayError(json);
								button_ele.html('Activate');
								if(is_permanent){
									button_ele.html("<i class='far fa-check-circle'></i>");
								}
							} else {
								card_message.html('<div class="alert alert-custom alert-success" role="alert"><div class="alert-icon"><i class="far fa-check-circle "></i></div><div class="alert-text">' + json.success + '</div></div');
								button_ele.hide();
								var activateButton = document.createElement('a');
								activateButton.setAttribute('href', 'javascript:void(0);');
								activateButton.setAttribute('class', 'btn btn-danger btn-sm inactive-sub');
								activateButton.setAttribute('title', 'Cancel');
								activateButton.setAttribute('data-id', id);
								activateButton.innerHTML = 'Cancel';
								if(is_permanent){
									activateButton.innerHTML = "<i class='far fa-ban'></i>";
								}
								button_ele.after(activateButton);
							}
						});
					});
				}
			}, {
				label: 'Cancel',
				action: function(dialogItself){
					dialogItself.close();
				}
			}]
		});
	});

	function displayError(event) {
		// Show error in payment form
		card_message.html('<div class="alert alert-custom alert-danger" role="alert"><div class="alert-icon"><i class="far fa-exclamation-circle"></i></div><div class="alert-text">' + event.error + '</div></div>');
	}
});
