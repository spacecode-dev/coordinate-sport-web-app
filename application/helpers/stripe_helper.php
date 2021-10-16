<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function stripe_js($stripe_pk, $flag = null, $stripe_price_id = null) {
	$CI =& get_instance();
	$contactID = $CI->cart_library->contactID;
	?><script src="https://js.stripe.com/v3/"></script>
	<script>
		var stripe_price_ids = <?php echo isset($stripe_price_id)?$stripe_price_id: '{}'; ?>;
		var flagVal = '<?php echo $flag; ?>';
		var stripe = Stripe('<?php echo $stripe_pk; ?>');

		// create fields
		var elements = stripe.elements();
		var args = {
			classes: {
				base: 'form-control'
			},
			style: {
				base: {
					lineHeight: '20px'
				}
			}
		};
		var card_number = elements.create('cardNumber', args);
		card_number.mount('#cardNumber');
		var card_expiry = elements.create('cardExpiry', args);
		card_expiry.mount('#cardExpiry');
		var card_cvc = elements.create('cardCvc', args);
		card_cvc.mount('#cardCvc');

		window.addEventListener('load', function() {

			var form = document.querySelectorAll('#pay, #checkout')[0];
			var form_fieldsets = document.querySelectorAll('#pay fieldset, #checkout fieldset, .checkout fieldset');
			var form_fields = document.querySelectorAll('#pay input, #pay select, #pay textarea, #checkout input, #checkout select, #checkout textarea');
			var pay_button = document.querySelectorAll('#pay input[type=button], #pay input[type=submit], #checkout input[type=button], #checkout input[type=submit], #checkout .submit-checkout');
			var pay_button_original_text = pay_button[0].value;
			var card_errors = document.getElementById('card-errors');
			form.onsubmit = function(event) {
				if (document.getElementById('payment_amount').value > 0 && (!document.getElementById('payment_method') || document.getElementById('payment_method').value == 'card')) {
					event.preventDefault();
					for (var i = 0; i < pay_button.length; i++) {
						pay_button[i].value = 'Processing...';
					}
					for (var i = 0; i < form_fieldsets.length; i++) {
						form_fieldsets[i].setAttribute('disabled', 'disabled');
					}
					card_errors.innerHTML = '';
					var cardTemp = card_number
					stripe.createPaymentMethod('card', card_number).then(function(result) {
						if (result.error) {
							// Show error in payment form
							card_errors.innerHTML = '<div class="alert alert-danger"><p>' + result.error.message + '</p></div>';
							location.hash = '#card-errors';
							// reset pay button
							for (var i = 0; i < pay_button.length; i++) {
								pay_button[i].value = pay_button_original_text;
							}
							for (var i = 0; i < form_fieldsets.length; i++) {
								form_fieldsets[i].removeAttribute('disabled', 'disabled');
							}
						} else {
							var sub_price = document.getElementById('sub_price');
							var session_price = document.getElementById('session_price');
							var amount = 0.00;
							if (typeof(sub_price) != 'undefined' && sub_price != null && typeof(session_price) != 'undefined' && session_price != null){
								amount = parseFloat(session_price.value)*100;
							}else{
								amount = parseFloat(document.getElementById('payment_amount').value)*100;
							}

							if(flagVal == '1') {
								//If subscription
								var contactID = <?php echo $contactID; ?>;
								fetch('/account/load_subscription', {
									method: 'POST',
									headers: {'Content-Type': 'application/json'},
									body: JSON.stringify({
										'flag': 1,
										'stripe_pk': '<?php echo $stripe_pk;?>',
										'stripe_price_id': stripe_price_ids
									})
								}).then(function (confirmResult) {
									confirmResult.json().then(function (json) {
										if (json.hasOwnProperty('data')) {
											let script_tag = document.createElement('script');
											script_tag.innerHTML = json.data;
											document.body.appendChild(script_tag);

											createCustomer().then((result) => {
												if (result.error) {
													// Show error in payment form
													card_errors.innerHTML = '<div class=\"alert alert-danger\"><p>' + result.error + '</p></div>';
													location.hash = '#card-errors';
													// reset pay button
													for (var i = 0; i < pay_button.length; i++) {
														pay_button[i].value = pay_button_original_text;
													}
													for (var i = 0; i < form_fieldsets.length; i++) {
														form_fieldsets[i].removeAttribute('disabled', 'disabled');
													}
												} else {
													return stripe
														.createPaymentMethod({
															type: 'card',
															card: card_number,
														})
														.then((response) => {
															if (response.error) {
																displayError(error);
															} else {
																for (const [key, value] of Object.entries(stripe_price_ids)) {
																	createSubscription(result.customer.id, response.paymentMethod.id, key, value.qty, value.cartID);
																}
															}
														});
												}
											});

										}
									})
								});

							}

							// Otherwise send paymentMethod.id to server
							fetch('/account/stripe_auth', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json'
								},
								body: JSON.stringify({
									payment_method_id: result.paymentMethod.id,
									amount: amount
								})
							}).then(function(result) {

								// Handle server response
								result.json().then(function(json) {
									handleServerResponse(json);
								})
							});
						}
					});

					function displayError(event) {
						// Show error in payment form
						card_errors.innerHTML = '<div class="alert alert-danger"><p>' + event.error + '</p></div>';
						location.hash = '#card-errors';
						// reset pay button
						for (var i = 0; i < pay_button.length; i++) {
							pay_button[i].value = pay_button_original_text;
						}
						for (var i = 0; i < form_fieldsets.length; i++) {
							form_fieldsets[i].removeAttribute('disabled', 'disabled');
						}
					}

					function handleServerResponse(response) {
						if (response.error) {
							// Show error in payment form
							card_errors.innerHTML = '<div class="alert alert-danger"><p>' + response.error + '</p></div>';
							location.hash = '#card-errors';
							// reset pay button
							for (var i = 0; i < pay_button.length; i++) {
								pay_button[i].value = pay_button_original_text;
							}
							for (var i = 0; i < form_fieldsets.length; i++) {
								form_fieldsets[i].removeAttribute('disabled', 'disabled');
							}
						} else if (response.requires_action) {
							// Use Stripe.js to handle required card action
							stripe.handleCardAction(
								response.payment_intent_client_secret
							).then(function(result) {
								if (result.error) {
									// Show error in payment form
									card_errors.innerHTML = '<div class="alert alert-danger"><p>' + result.error + '</p></div>';
									location.hash = '#card-errors';
									// reset pay button
									for (var i = 0; i < pay_button.length; i++) {
										pay_button[i].value = pay_button_original_text;
									}
									for (var i = 0; i < form_fieldsets.length; i++) {
										form_fieldsets[i].removeAttribute('disabled', 'disabled');
									}
								} else {
									// The card action has been handled
									// The PaymentIntent can be confirmed again on the server
									fetch('/account/stripe_auth', {
										method: 'POST',
										headers: { 'Content-Type': 'application/json' },
										body: JSON.stringify({ payment_intent_id: result.paymentIntent.id })
									}).then(function(confirmResult) {
										return confirmResult.json();
									}).then(handleServerResponse);
								}
							});
						} else {
							// Send the intent id to server
							var hiddenInput = document.createElement('input');
							hiddenInput.setAttribute('type', 'hidden');
							hiddenInput.setAttribute('name', 'payment_intent_id');
							hiddenInput.setAttribute('value', response.id);
							form.appendChild(hiddenInput);

							// remove disabled so submits fields
							for (var i = 0; i < form_fieldsets.length; i++) {
								form_fieldsets[i].removeAttribute('disabled', 'disabled');
							}
							// add read only to fields so still submit
							for (var i = 0; i < form_fields.length; i++) {
								form_fields[i].setAttribute('readonly', 'readonly');
							}
							// add disabled to pay buttons
							for (var i = 0; i < pay_button.length; i++) {
								pay_button[i].setAttribute('disabled', 'disabled');
							}

							if (flagVal != '1')
							{
								form.submit();
							}
						}
					}
				}
			};
		});
	</script>
	<?php
}

function stripe_subscription_js($stripe_pk, $priceID) {
	$CI =& get_instance(); ?>
	<script src="https://js.stripe.com/v3/"></script>
	<script>
		var stripe = Stripe('<?php echo $stripe_pk; ?>');
		var priceID = <?php echo $priceID ?>;
		var master_config = {};
		var loop_count = Object.keys(priceID).length;
		var actual_counter = 1;
		var customer, form, card_errors, form_fieldsets, form_fields, pay_button, pay_button_original_text;

		// create fields
		var elements = stripe.elements();
		var args = {
			classes: {
				base: 'form-control'
			},
			style: {
				base: {
					lineHeight: '20px'
				}
			}
		};
		var card_number = elements.create('cardNumber', args);
		card_number.mount('#cardNumber');
		var card_expiry = elements.create('cardExpiry', args);
		card_expiry.mount('#cardExpiry');
		var card_cvc = elements.create('cardCvc', args);
		card_cvc.mount('#cardCvc');

		window.addEventListener('load', function() {
			form = document.querySelectorAll('#pay, #checkout')[0];
			form_fieldsets = document.querySelectorAll('#pay fieldset, #checkout fieldset, .checkout fieldset');
			form_fields = document.querySelectorAll('#pay input, #pay select, #pay textarea, #checkout input, #checkout select, #checkout textarea');
			pay_button = document.querySelectorAll('#pay input[type=button], #pay input[type=submit], #checkout input[type=button], #checkout input[type=submit], #checkout .submit-checkout');
			pay_button_original_text = pay_button[0].value;
			card_errors = document.getElementById('card-errors');
			form.onsubmit = function(event) {
				event.preventDefault();

				for (var i = 0; i < pay_button.length; i++) {
					pay_button[i].value = 'Processing...';
				}
				for (var i = 0; i < form_fieldsets.length; i++) {
					form_fieldsets[i].setAttribute('disabled', 'disabled');
				}
				createCustomer().then((result) => {
					if (result.error) {
						displayError(error);
					} else {
						for (const [key, value] of Object.entries(priceID)) {
							createPaymentMethod(card_number, result.customer.id, key, value.qty, value.cartID);
						}
					}
				});
			}
		});

		function createCustomer() {
			//create customer
			var contactID = '<?php echo $CI->cart_library->contactID ?>';
			return fetch('account/get_stripe_customer', {
				method: 'post',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					contactID: contactID
				})
			}).then(response => response.json())
				.then(result => {
					return result;
				});
		}

		function createPaymentMethod(cardElement, customerId, priceId, qty, cartID) {
			return stripe
				.createPaymentMethod({
					type: 'card',
					card: cardElement,
				})
				.then((result) => {
					if (result.error) {
						displayError(error);
					} else {
						createSubscription(customerId, result.paymentMethod.id, priceId, qty, cartID);
					}
				});
		}

		function displayError(event) {
			// Show error in payment form
			card_errors.innerHTML = '<div class="alert alert-danger"><p>' + event.error + '</p></div>';
			location.hash = '#card-errors';
			// reset pay button
			for (var i = 0; i < pay_button.length; i++) {
				pay_button[i].value = pay_button_original_text;
			}
			for (var i = 0; i < form_fieldsets.length; i++) {
				form_fieldsets[i].removeAttribute('disabled', 'disabled');
			}
		}

		function createSubscription(customerId, paymentMethodId, priceId, qty, cartID) {
			return (
				fetch('/account/create_stripe_subscription', {
					method: 'post',
					headers: {
						'Content-type': 'application/json',
					},
					body: JSON.stringify({
						customerId: customerId,
						paymentMethodId: paymentMethodId,
						priceId: priceId,
						quantity: qty,
						cartID: cartID
					}),
				})
					.then((response) => {
						return response.json();
					})
					// If the card is declined, display an error to the user.
					.then((result) => {
						if (result.error) {
							// The card had an error when trying to attach it to a customer.
							throw result;
						}
						return result;
					})
					// Normalize the result to contain the object returned by Stripe.
					// Add the addional details we need.
					.then((result) => {
						return {
							subscription: result,
							paymentMethodId: paymentMethodId,
							priceId: priceId,
						};
					})
					// Some payment methods require a customer to be on session
					// to complete the payment process. Check the status of the
					// payment intent to handle these actions.
					.then(handleCustomerActionRequired)
					// If attaching this card to a Customer object succeeds,
					// but attempts to charge the customer fail, you
					// get a requires_payment_method error.
					.then(handlePaymentMethodRequired)
					// No more actions required. Provision your service for the user.
					.then(onSubscriptionComplete)
					.catch((error) => {
						// An error has happened. Display the failure to the user here.
						// We utilize the HTML element we created.
						displayError(error);
					})
			);
		}

		function handleCustomerActionRequired({
												  subscription,
												  invoice,
												  priceId,
												  paymentMethodId,
												  isRetry,
											  }) {
			if (subscription && subscription.status === 'active') {
				// Subscription is active, no customer actions required.
				return { subscription, priceId, paymentMethodId };
			}

			// If it's a first payment attempt, the payment intent is on the subscription latest invoice.
			// If it's a retry, the payment intent will be on the invoice itself.
			let paymentIntent = invoice
				? invoice.payment_intent
				: subscription.latest_invoice.payment_intent;

			if (
				paymentIntent.status === 'requires_action' ||
				(isRetry === true && paymentIntent.status === 'requires_payment_method')
			) {
				return stripe
					.confirmCardPayment(paymentIntent.client_secret, {
						payment_method: paymentMethodId,
					})
					.then((result) => {
						if (result.error) {
							// start code flow to handle updating the payment details
							// Display error message in your UI.
							// The card was declined (i.e. insufficient funds, card has expired, etc)
							throw result;
						} else {
							if (result.paymentIntent.status === 'succeeded') {
								// There's a risk of the customer closing the window before callback
								// execution. To handle this case, set up a webhook endpoint and
								// listen to invoice.payment_succeeded. This webhook endpoint
								// returns an Invoice.
								return {
									priceId: priceId,
									subscription: subscription,
									invoice: invoice,
									paymentMethodId: paymentMethodId,
								};
							}
						}
					});
			} else {
				// No customer action needed
				return { subscription, priceId, paymentMethodId };
			}
		}

		function handlePaymentMethodRequired({
												 subscription,
												 paymentMethodId,
												 priceId,
											 }) {
			if (subscription.status === 'active') {
				// subscription is active, no customer actions required.
				return { subscription, priceId, paymentMethodId };
			} else if (
				subscription.latest_invoice.payment_intent.status ===
				'requires_payment_method'
			) {
				// Using localStorage to store the state of the retry here
				// (feel free to replace with what you prefer)
				// Store the latest invoice ID and status
				localStorage.setItem('latestInvoiceId', subscription.latest_invoice.id);
				localStorage.setItem(
					'latestInvoicePaymentIntentStatus',
					subscription.latest_invoice.payment_intent.status
				);
				throw { error: { message: 'Your card was declined.' } };
			} else {
				return { subscription, priceId, paymentMethodId };
			}
		}

		function onSubscriptionComplete(result) {
			master_config[result.priceId] = {
				'contactID' : priceID[result.priceId]['contactID'],
				'childID' : priceID[result.priceId]['childID'],
				'strip_sub_id' : result.subscription.id,
				'subID' : priceID[result.priceId]['subID'],
				'qty' : priceID[result.priceId]['qty'],
				'price' : priceID[result.priceId]['price'],
				'status' : result.subscription.status
			};
			var tempVar = JSON.stringify(master_config);

			// remove disabled so submits fields
			for (var i = 0; i < form_fieldsets.length; i++) {
				form_fieldsets[i].removeAttribute('disabled', 'disabled');
			}
			// add read only to fields so still submit
			for (var i = 0; i < form_fields.length; i++) {
				form_fields[i].setAttribute('readonly', 'readonly');
			}
			// add disabled to pay buttons
			for (var i = 0; i < pay_button.length; i++) {
				pay_button[i].setAttribute('disabled', 'disabled');
			}
			if(loop_count == actual_counter){
				// Send the subscription id to server
				var hiddenInput = document.createElement('input');
				hiddenInput.setAttribute('type', 'hidden');
				hiddenInput.setAttribute('name', 'subscriptions[]');
				hiddenInput.setAttribute('value', tempVar);
				form.appendChild(hiddenInput);
				form.submit();
			}
			actual_counter++;
		}
	</script><?php
}

function stripe_subscription_external_js($stripe_pk, $priceID) {
	$CI =& get_instance();
	$contactID = $CI->cart_library->contactID;

	return "
		var priceID = $priceID;
		var master_config = {};
		var loop_count = Object.keys(priceID).length;
		var actual_counter = 1;
		var customer, form, card_errors, form_fieldsets, form_fields, pay_button, pay_button_original_text;
		var form = document.querySelectorAll('#pay, #checkout')[0];
		var form_fieldsets = document.querySelectorAll('#pay fieldset, #checkout fieldset, .checkout fieldset');
		var form_fields = document.querySelectorAll('#pay input, #pay select, #pay textarea, #checkout input, #checkout select, #checkout textarea');
		var pay_button = document.querySelectorAll('#pay input[type=button], #pay input[type=submit], #checkout input[type=button], #checkout input[type=submit], #checkout .submit-checkout');
		var pay_button_original_text = pay_button[0].value;
		var card_errors = document.getElementById('card-errors');

		function createCustomer() {
			//create customer
			var contactID = '$contactID';
			return fetch('account/get_stripe_customer', {
				method: 'post',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					contactID: contactID
				})
			}).then(response => {
				return response.json();
			})
				.then(result => {
					// result.customer.id is used to map back to the customer object
					// result.setupIntent.client_secret is used to create the payment method
					return result;
				});
		}

		function createPaymentMethod(cardElement, customerId, priceId) {
			return stripe
				.createPaymentMethod({
					type: 'card',
					card: cardElement,
				})
				.then((result) => {
					if (result.error) {
						displayError(error);
					} else {
						createSubscription(customerId, result.paymentMethod.id, priceId);
					}
				});
		}

		function displayError(event) {
			// Show error in payment form
			card_errors.innerHTML = '<div class=\"alert alert-danger\"><p>' + event.error + '</p></div>';
			location.hash = '#card-errors';
			// reset pay button
			for (var i = 0; i < pay_button.length; i++) {
				pay_button[i].value = pay_button_original_text;
			}
			for (var i = 0; i < form_fieldsets.length; i++) {
				form_fieldsets[i].removeAttribute('disabled', 'disabled');
			}
		}

		function createSubscription(customerId, paymentMethodId, priceId, qty, cartID) {
			return (
				fetch('/account/create_stripe_subscription', {
					method: 'post',
					headers: {
						'Content-type': 'application/json',
					},
					body: JSON.stringify({
						customerId: customerId,
						paymentMethodId: paymentMethodId,
						priceId: priceId,
						quantity: qty,
						cartID: cartID
					}),
				})
					.then((response) => {
						return response.json();
					})
					// If the card is declined, display an error to the user.
					.then((result) => {
						if (result.error) {
							// The card had an error when trying to attach it to a customer.
							throw result;
						}
						return result;
					})
					// Normalize the result to contain the object returned by Stripe.
					// Add the addional details we need.
					.then((result) => {
						return {
							subscription: result,
							paymentMethodId: paymentMethodId,
							priceId: priceId,
						};
					})
					// Some payment methods require a customer to be on session
					// to complete the payment process. Check the status of the
					// payment intent to handle these actions.
					.then(handleCustomerActionRequired)
					// If attaching this card to a Customer object succeeds,
					// but attempts to charge the customer fail, you
					// get a requires_payment_method error.
					.then(handlePaymentMethodRequired)
					// No more actions required. Provision your service for the user.
					.then(onSubscriptionComplete)
					.catch((error) => {
						// An error has happened. Display the failure to the user here.
						// We utilize the HTML element we created.
						displayError(error);
					})
			);
		}

		function handleCustomerActionRequired({
												  subscription,
												  invoice,
												  priceId,
												  paymentMethodId,
												  isRetry,
											  }) {
			if (subscription && subscription.status === 'active') {
				// Subscription is active, no customer actions required.
				return { subscription, priceId, paymentMethodId };
			}

			// If it's a first payment attempt, the payment intent is on the subscription latest invoice.
			// If it's a retry, the payment intent will be on the invoice itself.
			let paymentIntent = invoice
				? invoice.payment_intent
				: subscription.latest_invoice.payment_intent;

			if (
				paymentIntent.status === 'requires_action' ||
				(isRetry === true && paymentIntent.status === 'requires_payment_method')
			) {
				return stripe
					.confirmCardPayment(paymentIntent.client_secret, {
						payment_method: paymentMethodId,
					})
					.then((result) => {
						if (result.error) {
							// start code flow to handle updating the payment details
							// Display error message in your UI.
							// The card was declined (i.e. insufficient funds, card has expired, etc)
							throw result;
						} else {
							if (result.paymentIntent.status === 'succeeded') {
								// There's a risk of the customer closing the window before callback
								// execution. To handle this case, set up a webhook endpoint and
								// listen to invoice.payment_succeeded. This webhook endpoint
								// returns an Invoice.
								return {
									priceId: priceId,
									subscription: subscription,
									invoice: invoice,
									paymentMethodId: paymentMethodId,
								};
							}
						}
					});
			} else {
				// No customer action needed
				return { subscription, priceId, paymentMethodId };
			}
		}

		function handlePaymentMethodRequired({
												 subscription,
												 paymentMethodId,
												 priceId,
											 }) {
			if (subscription.status === 'active') {
				// subscription is active, no customer actions required.
				return { subscription, priceId, paymentMethodId };
			} else if (
				subscription.latest_invoice.payment_intent.status ===
				'requires_payment_method'
			) {
				// Using localStorage to store the state of the retry here
				// (feel free to replace with what you prefer)
				// Store the latest invoice ID and status
				localStorage.setItem('latestInvoiceId', subscription.latest_invoice.id);
				localStorage.setItem(
					'latestInvoicePaymentIntentStatus',
					subscription.latest_invoice.payment_intent.status
				);
				throw { error: { message: 'Your card was declined.' } };
			} else {
				return { subscription, priceId, paymentMethodId };
			}
		}

		function onSubscriptionComplete(result) {

			master_config[result.priceId] = {
				'contactID' : priceID[result.priceId]['contactID'],
				'childID' : priceID[result.priceId]['childID'],
				'strip_sub_id' : result.subscription.id,
				'subID' : priceID[result.priceId]['subID'],
				'qty' : priceID[result.priceId]['qty'],
				'price' : priceID[result.priceId]['price'],
				'status' : result.subscription.status
			};
			var tempVar = JSON.stringify(master_config);

			// remove disabled so submits fields
			for (var i = 0; i < form_fieldsets.length; i++) {
				form_fieldsets[i].removeAttribute('disabled', 'disabled');
			}
			// add read only to fields so still submit
			for (var i = 0; i < form_fields.length; i++) {
				form_fields[i].setAttribute('readonly', 'readonly');
			}
			// add disabled to pay buttons
			for (var i = 0; i < pay_button.length; i++) {
				pay_button[i].setAttribute('disabled', 'disabled');
			}

			if(loop_count == actual_counter){
				// Send the subscription id to server
				var hiddenInput = document.createElement('input');
				hiddenInput.setAttribute('type', 'hidden');
				hiddenInput.setAttribute('name', 'subscriptions[]');
				hiddenInput.setAttribute('value', tempVar);
				form.appendChild(hiddenInput);
				form.submit();
			}
			actual_counter++;
			return;
		}";

}


/* End of file stripe_helper.php */

