
/* global jQuery:true */

/*
 * Fuel UX Wizard
 * https://github.com/ExactTarget/fuelux
 *
 * Copyright (c) 2014 ExactTarget
 * Licensed under the BSD New license.
 */

// -- BEGIN UMD WRAPPER PREFACE --

// For more information on UMD visit:
// https://github.com/umdjs/umd/blob/master/jqueryPlugin.js

(function umdFactory (factory) {
	if (typeof define === 'function' && define.amd) {
		// if AMD loader is available, register as an anonymous module.
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// Node/CommonJS
		module.exports = factory(require('jquery'));
	} else {
		// OR use browser globals if AMD is not present
		factory(jQuery);
	}
}(function WizardWrapper ($) {
	// -- END UMD WRAPPER PREFACE --

	// -- BEGIN MODULE CODE HERE --

	var old = $.fn.wizard;

	// WIZARD CONSTRUCTOR AND PROTOTYPE

	var Wizard = function (element, options) {
		this.$element = $(element);
		this.options = $.extend({}, $.fn.wizard.defaults, options);
		this.options.disablePreviousStep = (this.$element.attr('data-restrict') === 'previous') ? true : this.options.disablePreviousStep;
		this.currentStep = this.options.selectedItem.step;
		this.numSteps = this.$element.find('.steps li').length - 2;
		this.$prevBtn = this.$element.find('button.btn-prev');
		this.$nextBtn = this.$element.find('button.btn-next');

		var kids = this.$nextBtn.children().detach();
		this.nextText = $.trim(this.$nextBtn.text());
		this.$nextBtn.append(kids);

		var steps = this.$element.children('.steps-container');
		// maintains backwards compatibility with < 3.8, will be removed in the future
		if (steps.length === 0) {
			steps = this.$element;
			this.$element.addClass('no-steps-container');
			if (window && window.console && window.console.warn) {
				window.console.warn('please update your wizard markup to include ".steps-container" as seen in http://getfuelux.com/javascript.html#wizard-usage-markup');
			}
		}
		steps = steps.find('.steps');

		// handle events
		this.$prevBtn.on('click.fu.wizard', $.proxy(this.previous, this));
		this.$nextBtn.on('click.fu.wizard', $.proxy(this.next, this));
		steps.on('click.fu.wizard', 'li.complete', $.proxy(this.stepclicked, this));

		this.selectedItem(this.options.selectedItem);

		if (this.options.disablePreviousStep) {
			this.$prevBtn.attr('disabled', true);
			this.$element.find('.steps').addClass('previous-disabled');
		}
	};

	Wizard.prototype = {

		constructor: Wizard,

		destroy: function () {
			this.$element.remove();
			// any external bindings [none]
			// empty elements to return to original markup [none]
			// returns string of markup
			return this.$element[0].outerHTML;
		},

		//index is 1 based
		//second parameter can be array of objects [{ ... }, { ... }] or you can pass n additional objects as args
		//object structure is as follows (all params are optional): { badge: '', label: '', pane: '' }
		addSteps: function (index) {
			var items = [].slice.call(arguments).slice(1);
			var $steps = this.$element.find('.steps');
			var $stepContent = this.$element.find('.step-content');
			var i, l, $pane, $startPane, $startStep, $step;
			index = (index === -1 || (index > (this.numSteps + 1))) ? this.numSteps + 1 : index;
			if (items[0] instanceof Array) {
				items = items[0];
			}

			$startStep = $steps.find('li:nth-child(' + index + ')');
			$startPane = $stepContent.find('.step-pane:nth-child(' + index + ')');

			if ($startStep.length < 1) {
				$startStep = null;
			}

			for (i = 0, l = items.length; i < l; i++) {
				$steps.find('li.active').removeClass('active');
				$stepContent.find('div.active').removeClass('active');
				;
				$step = $('<li data-step="' + index + '" data-role="' + items[i].action_data + '" class="active"><div class="title"><i class="badge">&nbsp;</i>'+items[i].label+'<a title="Remove" class="remove" href="javascript:void(0);"><i class="far fa-times text-white"></i></a></div></li>');

				$pane = $('<div class="step-pane active" data-step="' + index + '"></div>');
				$pane.append(items[i].pane || '');
				if (!$startStep) {
					$steps.append($step);
					$stepContent.append($pane);
				} else {
					$startStep.before($step);
					$startPane.before($pane);
				}
				index++;
			}
			this.syncSteps();
			this.numSteps = $steps.find('li').length - 2;
			this.setState();
		},

		//index is 1 based, howMany is number to remove
		removeSteps: function (index, howMany) {
			var action = 'nextAll';
			var i = 0;
			var $steps = this.$element.find('.steps');
			var $stepContent = this.$element.find('.step-content');
			var $start;

			howMany = (howMany !== undefined) ? howMany : 1;

			if (index > $steps.find('li').length) {
				$start = $steps.find('li:last');
			} else {
				$start = $steps.find('li:nth-child(' + index + ')').prev();
				if ($start.length < 1) {
					action = 'children';
					$start = $steps;
				}

			}

			$start[action]().each(function () {
				var item = $(this);
				var step = item.attr('data-step');
				if (i < howMany) {
					item.remove();
					$stepContent.find('.step-pane[data-step="' + step + '"]:first').remove();
				} else {
					return false;
				}

				i++;
			});

			this.syncSteps();
			this.numSteps = $steps.find('li').length;
			this.setState();
		},

		setState: function () {
			var canMovePrev = (this.currentStep > 1);//remember, steps index is 1 based...
			var isFirstStep = (this.currentStep === 1);
			var isLastStep = (this.currentStep === this.numSteps);

			// disable buttons based on current step
			if (!this.options.disablePreviousStep) {
				this.$prevBtn.attr('disabled', (isFirstStep === true || canMovePrev === false));
			}

			// change button text of last step, if specified
			var last = this.$nextBtn.attr('data-last');
			if (last) {
				this.lastText = last;
				// replace text
				var text = this.nextText;
				if (isLastStep === true) {
					text = this.lastText;
					// add status class to wizard
					this.$element.addClass('complete');
				} else {
					this.$element.removeClass('complete');
				}

				var kids = this.$nextBtn.children().detach();
				this.$nextBtn.text(text).append(kids);
			}

			// reset classes for all steps
			var $steps = this.$element.find('.steps li');
			$steps.removeClass('active').removeClass('complete');
			$steps.find('span.badge').removeClass('badge-info').removeClass('badge-success');

			// set class for all previous steps
			var prevSelector = '.steps li:lt(' + (this.currentStep - 1) + ')';
			var $prevSteps = this.$element.find(prevSelector);
			$prevSteps.addClass('complete');
			$prevSteps.find('span.badge').addClass('badge-success');

			// set class for current step
			var currentSelector = '.steps li:eq(' + (this.currentStep - 1) + ')';
			var $currentStep = this.$element.find(currentSelector);
			$currentStep.addClass('active');
			$currentStep.find('span.badge').addClass('badge-info');

			// set display of target element
			var $stepContent = this.$element.find('.step-content');
			var target = $currentStep.attr('data-step');
			$stepContent.find('.step-pane').removeClass('active');
			$stepContent.find('.step-pane[data-step="' + target + '"]:first').addClass('active');

			// reset the wizard position to the left
			//this.$element.find('.steps').first().attr('style', 'margin-left: 0');

			// check if the steps are wider than the container div
			var totalWidth = 0;
			this.$element.find('.steps > li').each(function () {
				totalWidth += $(this).outerWidth();
			});
			var containerWidth = 0;
			if (this.$element.find('.actions').length) {
				containerWidth = this.$element.width() - this.$element.find('.actions').first().outerWidth();
			} else {
				containerWidth = this.$element.width();
			}

			if (totalWidth > containerWidth) {
				// set the position so that the last step is on the right
				var newMargin = totalWidth - containerWidth;
				//this.$element.find('.steps').first().attr('style', 'margin-left: -' + newMargin + 'px');

				// set the position so that the active step is in a good
				// position if it has been moved out of view
				if (this.$element.find('li.active').first().position().left < 200) {
					newMargin += this.$element.find('li.active').first().position().left - 200;
					if (newMargin < 1) {
						this.$element.find('.steps').first().attr('style', 'margin-left: 0');
					} else {
						this.$element.find('.steps').first().attr('style', 'margin-left: -' + newMargin + 'px');
					}

				}

			}

			// only fire changed event after initializing
			if (typeof (this.initialized) !== 'undefined') {
				var e = $.Event('changed.fu.wizard');
				this.$element.trigger(e, {
					step: this.currentStep
				});
			}

			this.initialized = true;
		},

		stepclicked: function (e) {
			var li = $(e.currentTarget);
			var index = this.$element.find('.steps li').index(li);

			if (index < this.currentStep && this.options.disablePreviousStep) {//enforce restrictions
				return;
			} else {
				var evt = $.Event('stepclicked.fu.wizard');
				this.$element.trigger(evt, {
					step: index + 1
				});
				if (evt.isDefaultPrevented()) {
					return;
				}

				this.currentStep = (index + 1);
				this.setState();
			}
		},

		syncSteps: function () {
			var i = 1;
			var $steps = this.$element.find('.steps');
			var $stepContent = this.$element.find('.step-content');

			$steps.children().each(function () {
				var item = $(this);
				if(item.data('action')){
					return false;
				}
				var badge = item.find('.badge');
				var step = item.attr('data-step');

				if (!isNaN(parseInt(badge.html(), 10))) {
					badge.html(i);
				}

				item.attr('data-step', i);
				$stepContent.find('.step-pane[data-step="' + step + '"]:last').attr('data-step', i);
				i++;
			});
		},

		previous: function () {
			if (this.options.disablePreviousStep || this.currentStep === 1) {
				return;
			}

			var e = $.Event('actionclicked.fu.wizard');
			this.$element.trigger(e, {
				step: this.currentStep,
				direction: 'previous'
			});
			if (e.isDefaultPrevented()) {
				return;
			}// don't increment ...what? Why?

			this.currentStep -= 1;
			this.setState();

			// only set focus if focus is still on the $nextBtn (avoid stomping on a focus set programmatically in actionclicked callback)
			if (this.$prevBtn.is(':focus')) {
				var firstFormField = this.$element.find('.active').find('input, select, textarea')[0];

				if (typeof firstFormField !== 'undefined') {
					// allow user to start typing immediately instead of having to click on the form field.
					$(firstFormField).focus();
				} else if (this.$element.find('.active input:first').length === 0 && this.$prevBtn.is(':disabled')) {
					//only set focus on a button as the last resort if no form fields exist and the just clicked button is now disabled
					this.$nextBtn.focus();
				}

			}
		},

		next: function () {
			var e = $.Event('actionclicked.fu.wizard');
			this.$element.trigger(e, {
				step: this.currentStep,
				direction: 'next'
			});
			if (e.isDefaultPrevented()) {
				return;
			}// respect preventDefault in case dev has attached validation to step and wants to stop propagation based on it.

			if (this.currentStep < this.numSteps) {
				this.currentStep += 1;
				this.setState();
			} else {//is last step
				this.$element.trigger('finished.fu.wizard');
			}

			// only set focus if focus is still on the $nextBtn (avoid stomping on a focus set programmatically in actionclicked callback)
			if (this.$nextBtn.is(':focus')) {
				var firstFormField = this.$element.find('.active').find('input, select, textarea')[0];

				if (typeof firstFormField !== 'undefined') {
					// allow user to start typing immediately instead of having to click on the form field.
					$(firstFormField).focus();
				} else if (this.$element.find('.active input:first').length === 0 && this.$nextBtn.is(':disabled')) {
					//only set focus on a button as the last resort if no form fields exist and the just clicked button is now disabled
					this.$prevBtn.focus();
				}

			}
		},

		selectedItem: function (selectedItem) {
			var retVal, step;

			if (selectedItem) {
				step = selectedItem.step || -1;
				//allow selection of step by data-name
				step = Number(this.$element.find('.steps li[data-name="' + step + '"]').first().attr('data-step')) || Number(step);

				if (1 <= step && step <= this.numSteps) {
					this.currentStep = step;
					this.setState();
				} else {
					step = this.$element.find('.steps li.active:first').attr('data-step');
					if (!isNaN(step)) {
						this.currentStep = parseInt(step, 10);
						this.setState();
					}

				}

				retVal = this;
			} else {
				retVal = {
					step: this.currentStep
				};
				if (this.$element.find('.steps li.active:first[data-name]').length) {
					retVal.stepname = this.$element.find('.steps li.active:first').attr('data-name');
				}

			}

			return retVal;
		}
	};


	// WIZARD PLUGIN DEFINITION

	$.fn.wizard = function (option) {
		var args = Array.prototype.slice.call(arguments, 1);
		var methodReturn;

		var $set = this.each(function () {
			var $this = $(this);
			var data = $this.data('fu.wizard');
			var options = typeof option === 'object' && option;

			if (!data) {
				$this.data('fu.wizard', (data = new Wizard(this, options)));
			}

			if (typeof option === 'string') {
				methodReturn = data[option].apply(data, args);
			}
		});

		return (methodReturn === undefined) ? $set : methodReturn;
	};

	$.fn.wizard.defaults = {
		disablePreviousStep: false,
		selectedItem: {
			step: -1
		}//-1 means it will attempt to look for "active" class in order to set the step
	};

	$.fn.wizard.Constructor = Wizard;

	$.fn.wizard.noConflict = function () {
		$.fn.wizard = old;
		return this;
	};


	// DATA-API

	$(document).on('mouseover.fu.wizard.data-api', '[data-initialize=wizard]', function (e) {
		var $control = $(e.target).closest('.wizard');
		if (!$control.data('fu.wizard')) {
			$control.wizard($control.data());
		}
	});

	// Must be domReady for AMD compatibility
	$(function () {
		$('[data-initialize=wizard]').each(function () {
			var $this = $(this);
			if ($this.data('fu.wizard')) return;
			$this.wizard($this.data());
		});
	});

	// -- BEGIN UMD WRAPPER AFTERWORD --
}));
// -- END UMD WRAPPER AFTERWORD --
$( document ).ready(function() {
	var formWizard = $('#form-wizard');
	formWizard.wizard();
	$("select.select2").select2('destroy');
	$("select.select2-tags").select2('destroy');
	$('.datepicker').datepicker('destroy');
	$('.datepicker').removeClass("hasDatepicker").removeAttr('id');
	var paneContent = $(".account-holder-content").html();
	var participantPaneContent = $(".participant-data").html();
	$('.select2').select2();
	$(".select2-tags").select2({tags: true});
	$('.datepicker').datepicker({
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		yearRange: '-100:+10'
	});
	$('.datepicker-dob').datepicker('option', 'yearRange', '-100:+0');
	$('.datepicker-dob').datepicker('option', 'maxDate', '0');

	window.onscroll = function() {scrollFunction()};
	function scrollFunction() {
		if (document.body.scrollTop > 190 || document.documentElement.scrollTop > 190) {
			$(".steps-container").addClass("minimize");
		} else {
			$(".steps-container").removeClass("minimize");
		}
	}

	//Form Submit first check validation
	$(".submit-new-account").click(function(e) {
		$("#overlay").show();
		var validatorPass = 1;
		var formSubmitPass = 1;
		var errorTab = '';
		var FormSubmitData = [];
		var promises = [], promises1 = [], promises2 = [];
		var familyId = 0, contactId = 0;

		$(document).find(".new_family").each(function( index ) {
			if($(this).parent().attr("data-dummy")){
				return;
			}
			var url, formData;
			var form = $(this);
			var newsletters = [];
			var stepInfo = form.parent().attr("data-step");
			if(form.find("input[name='newsletters[]']:checked").length > 0){
				form.find("input[name='newsletters[]']:checked").each(function(){
					newsletters.push($(this).val());
				});
			}
			formData = new FormData(form[0]);
			if(form.attr("data-role") === 'ah'){
				url = '/participants/form-validate-ah';
			}else{
				url = '/participants/form-validate-p';
			}
			var request = $.ajax({
				url: url,
				type: 'POST',
				dataType:"json",
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if (response.code == '1') {
						form.find(".alert").eq(0).hide();
						form.find(".alert").eq(0).find('ul').html('');
					} else {
						validatorPass = 0;
						var errorMsg = '';
						errorTab = form.parent().attr("data-step");
						$.each(response.data, function( key, value ) {
							errorMsg += "<li>"+value+"</li>";
						});
						form.find(".alert").eq(0).find('ul').html('');
						form.find(".alert").eq(0).find('ul').html(errorMsg);
						form.find(".alert").eq(0).show();
						$('html, body').animate({
							scrollTop: 0
						}, 500);
					}
				}
			});
			promises.push(request);
		});
		$.when.apply(null, promises).done(function(){
			var elArray = [];
			if(validatorPass){
				$(document).find(".new_family[data-role='ah']").each(function( index ) {
					elArray[parseInt($(this).parent().attr("data-step")) - 1] = $(this);
				});
				var url, formData;
				var form = $("div.step-pane[data-step='1']").find(".new_family[data-role='ah']");

				url = '/participants/form-submit-ah';
				var newsletters = [];
				if(form.find("input[name='newsletters[]']:checked").length > 0){
					form.find("input[name='newsletters[]']:checked").each(function(){
						newsletters.push($(this).val());
					});
				}
				formData = new FormData(form[0]);
				formData.append('main_ah', 1);
				var request = $.ajax({
					url: url,
					type: 'POST',
					dataType:"json",
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.code == '1') {
							if(response.familyId){
								familyId = response.familyId;
							}
							if(response.contactId){
								contactId = response.contactId;
							}
						} else {
							formSubmitPass = 0;
							var errorMsg = '';
							$.each(response.data, function( key, value ) {
								errorMsg += "<li>"+value+"</li>";
							});
							form.find(".alert").eq(0).find('ul').html('');
							form.find(".alert").eq(0).find('ul').html(errorMsg);
							form.find(".alert").eq(0).show();
							$('html, body').animate({
								scrollTop: 0
							}, 500);
						}
					}
				});
				promises1.push(request);

				$.when.apply(null, promises1).done(function(){
					$.each(elArray, function (index) {
						if(index === 0){
							return;
						}
						var url, formData;
						var form = $(this);

						url = '/participants/form-submit-ah';
						var newsletters = [];
						if(form.find("input[name='newsletters[]']:checked").length > 0){
							form.find("input[name='newsletters[]']:checked").each(function(){
								newsletters.push($(this).val());
							});
						}
						formData = new FormData(form[0]);
						formData.append('main_ah', 0);
						formData.append('family_id', (familyId === 0)?0:familyId);
						formData.append('contact_id', (contactId === 0)?0:contactId);
						var request = $.ajax({
							url: url,
							type: 'POST',
							dataType:"json",
							data: formData,
							processData: false,
							contentType: false,
							success: function(response) {
								if (response.code == '1') {
									if(response.familyId){
										familyId = response.familyId;
									}
									if(response.contactId){
										contactId = response.contactId;
									}
								} else {
									formSubmitPass = 0;
									var errorMsg = '';
									$.each(response.data, function( key, value ) {
										errorMsg += "<li>"+value+"</li>";
									});
									form.find(".alert").eq(0).find('ul').html('');
									form.find(".alert").eq(0).find('ul').html(errorMsg);
									form.find(".alert").eq(0).show();
									$('html, body').animate({
										scrollTop: 0
									}, 500);
								}
							}
						});
						promises2.push(request);
					});
					$(document).find(".new_family[data-role='p']").each(function( index ) {
						//Do not process last form its just skeleton form
						if($(this).parent().attr("data-dummy")){
							return;
						}
						var url, formData;
						var form = $(this);

						url = '/participants/form-submit-p';
						var newsletters = [];
						if(form.find("input[name='newsletters[]']:checked").length > 0){
							form.find("input[name='newsletters[]']:checked").each(function(){
								newsletters.push($(this).val());
							});
						}
						formData = new FormData(form[0]);
						formData.append('family_id', (familyId === 0)?0:familyId);
						var request = $.ajax({
							url: url,
							type: 'POST',
							dataType:"json",
							data: formData,
							processData: false,
							contentType: false,
							success: function(response) {
								if (response.code != '1') {
									formSubmitPass = 0;
									var errorMsg = '';
									$.each(response.data, function( key, value ) {
										errorMsg += "<li>"+value+"</li>";
									});
									form.find(".alert").eq(0).find('ul').html('');
									form.find(".alert").eq(0).find('ul').html(errorMsg);
									form.find(".alert").eq(0).show();
									$('html, body').animate({
										scrollTop: 0
									}, 500);
								}
							}
						});
						promises2.push(request);
					});
					$.when.apply(null, promises2).done(function(){
						$("#overlay").hide();
						if(validatorPass && formSubmitPass){
							window.location.href = '/participants';
						}else{
							formWizard.wizard('selectedItem', {
								step: errorTab
							});
						}
					});
				});
			}else{
				$("#overlay").hide();
				formWizard.wizard('selectedItem', {
					step: errorTab
				});
			}
		});
	});

	//Copy Emergency Contact Details
	$("div.step-pane[data-step='1']").find("input[name='first_name']").change(function(e) {
		$(document).find(".new_family[data-role='p']").each(function (index) {
			if ($(this).parent().attr("data-dummy")) {
				return;
			}
			if($.trim($(this).find("input[name='emergency_contact_1_name']").val()) == ''){
				$(this).find("input[name='emergency_contact_1_name']").val($.trim($("div.step-pane[data-step='1']").find("input[name='first_name']").val())+" "+$.trim($("div.step-pane[data-step='1']").find("input[name='last_name']").val()));
			}
		});
	});
	$("div.step-pane[data-step='1']").find("input[name='last_name']").change(function(e) {
		$(document).find(".new_family[data-role='p']").each(function (index) {
			if ($(this).parent().attr("data-dummy")) {
				return;
			}
			if($.trim($(this).find("input[name='emergency_contact_1_name']").val()) == ''){
				$(this).find("input[name='emergency_contact_1_name']").val($.trim($("div.step-pane[data-step='1']").find("input[name='first_name']").val())+" "+$.trim($("div.step-pane[data-step='1']").find("input[name='last_name']").val()));
			}
		});
	});
	$("div.step-pane[data-step='1']").find("input[name='mobile']").change(function(e) {
		$(document).find(".new_family[data-role='p']").each(function (index) {
			if ($(this).parent().attr("data-dummy")) {
				return;
			}
			if ($(this).find("input[name='emergency_contact_1_phone']").val() == '') {
				$(this).find("input[name='emergency_contact_1_phone']").val($.trim($("div.step-pane[data-step='1']").find("input[name='mobile']").val()));
			}
		});
	});
	$(document).on("change",  ".new_family[data-role='p'] input[name='emergency_contact_1_name']", function() {
		var pos = parseInt($(this).closest("form").parent().attr("data-step")) - 1 - $(document).find(".new_family[data-role='ah']").length;
		var pemeContactNameEle = $(this);
		if(pos == 0){
			$(document).find(".new_family[data-role='p']").each(function( index ) {
				if($(this).parent().attr("data-dummy")){
					return;
				}
				if((parseInt($(this).parent().attr("data-step")) - 1 - $(document).find(".new_family[data-role='ah']").length) > 0){
					if($(this).find("input[name='emergency_contact_1_name']").val() === ""){
						$(this).find("input[name='emergency_contact_1_name']").val(pemeContactNameEle.val());
					}
				}
			});
		}
	});
	$(document).on("change",  ".new_family[data-role='p'] input[name='emergency_contact_1_phone']", function() {
		var pos = parseInt($(this).closest("form").parent().attr("data-step")) - 1 - $(document).find(".new_family[data-role='ah']").length;
		var pemeContactNameEle = $(this);
		if(pos == 0){
			$(document).find(".new_family[data-role='p']").each(function( index ) {
				if($(this).parent().attr("data-dummy")){
					return;
				}
				if((parseInt($(this).parent().attr("data-step")) - 1 - $(document).find(".new_family[data-role='ah']").length) > 0){
					if($(this).find("input[name='emergency_contact_1_phone']").val() === ""){
						$(this).find("input[name='emergency_contact_1_phone']").val(pemeContactNameEle.val());
					}
				}
			});
		}
	});

	//Jump to specific tab
	$(document).on("click", ".remove" , function(e) {
		var action = $(this).parent().parent().attr('data-role');
		var ele = $(this).parent().parent().parent().find("li[data-role='"+action+"']").last();
		var step = ele.attr('data-step');
		formWizard.wizard('removeSteps', step, 1);
		formWizard.wizard('selectedItem', {
			step: parseInt(step) - 1
		});
		e.stopPropagation();
	});

	$(document).on("click", ".steps li" , function() {
		if($(this).data('step') && !$(this).data('action')){
			//Jump to the tab
			var step = $(this).attr('data-step');
			formWizard.wizard('selectedItem', {
				step: step
			});
		}else{
			//Add new Tab
			//P - Participant
			//AH - Account Holder
			var action = $(this).attr('data-action');
			var title = 'Participant';
			var ele = $(this).parent().find("li[data-role='"+action+"']").last();
			var firstStepCount = 0;
			var label = '';
			if(action === 'ah'){
				title = 'Account Holder';
				label = title+' '+(parseInt(ele.attr('data-step')) + 1);
			}else{
				if($(this).parent().find("li[data-role='"+action+"']").length < 1){
					ele = $(this).parent().find("li[data-role='ah']").last();
					label = title+' 1';
				}else{
					label = title+' '+($(this).parent().find("li[data-role='p']").length + 1);
				}
			}
			var step = parseInt(ele.attr('data-step')) + 1;
			formWizard.wizard('addSteps', step, [
				{
					badge: '',
					label: label,
					pane: (action === 'ah')?paneContent:participantPaneContent,
					action_data: action
				}
			]);
			formWizard.wizard('selectedItem', {
				step: step
			});
			$('.steps').animate({scrollLeft: $(".steps").find("li[data-step='"+step+"']").position().left}, 500);
			$(".step-pane[data-step='"+step+"']").find('.select2').select2();
			$(".step-pane[data-step='"+step+"']").find(".select2-tags").select2({tags: true});

			// datepicker
			$(".step-pane[data-step='"+step+"']").find('.datepicker').datepicker({
				dateFormat: 'dd/mm/yy',
				firstDay: 1,
				changeMonth: true,
				changeYear: true,
				yearRange: '-100:+10'
			});
			$(".step-pane[data-step='"+step+"']").find('.datepicker-dob').datepicker('option', 'yearRange', '-100:+0');
			$(".step-pane[data-step='"+step+"']").find('.datepicker-dob').datepicker('option', 'maxDate', '0');

			//Onchnage of first participant's form
			var pFormArr = []
			$(document).find(".new_family[data-role='p']").each(function( index ) {
				if($(this).parent().attr("data-dummy")){
					return;
				}
				pFormArr[parseInt($(this).parent().attr("data-step")) - 1 - $(document).find(".new_family[data-role='ah']").length] = $(this);
			});

			if(pFormArr.length === 1){
				pFormArr[0].find("input[name='emergency_contact_1_name']").val($.trim($("div.step-pane[data-step='1']").find("input[name='first_name']").val())+" "+$.trim($("div.step-pane[data-step='1']").find("input[name='last_name']").val()));
				pFormArr[0].find("input[name='emergency_contact_1_phone']").val($.trim($("div.step-pane[data-step='1']").find("input[name='mobile']").val()));
			}else{
				$.each(pFormArr, function (index) {
					if(index === 0){
						return;
					}
					if($.trim($(this).find("input[name='emergency_contact_1_name']").val()) == ""){
						$(this).find("input[name='emergency_contact_1_name']").val($.trim($("div.step-pane[data-step='1']").find("input[name='first_name']").val())+" "+$.trim($("div.step-pane[data-step='1']").find("input[name='last_name']").val()));
					}
					if($.trim($(this).find("input[name='emergency_contact_1_phone']").val()) == "") {
						$(this).find("input[name='emergency_contact_1_phone']").val($.trim($("div.step-pane[data-step='1']").find("input[name='mobile']").val()));
					}
				});
			}
		}
		return;
	});
});
