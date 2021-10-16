/*
 * Copyright (c) 2014 Mike King (@micjamking)
 *
 * jQuery Succinct plugin
 * Version 1.1.0 (October 2014)
 *
 * Licensed under the MIT License
 */
/*
Modified for click to expand/truncate
 */
 /*global jQuery*/
(function($) {
	'use strict';

	$.fn.succinct = function(options) {

		var settings = $.extend({
				size: 240,
				omission: '...',
				ignore: true
			}, options);

		return this.each(function() {

			var textDefault,
				textTruncated,
				elements = $(this),
				regex    = /[!-\/:-@\[-`{-~]$/,
				init     = function() {
					elements.each(function() {
						textDefault = $(this).html();

						if (textDefault.length > settings.size) {
							textTruncated = $.trim(textDefault)
											.substring(0, settings.size)
											.split(' ')
											.slice(0, -1)
											.join(' ');

							if (settings.ignore) {
								textTruncated = textTruncated.replace(regex, '');
							}

                            textTruncated = $.trim(textTruncated) + settings.omission;

							$(this).html(textTruncated).addClass('truncated').attr('title', 'Expand').click(function() {
                                if ($(this).hasClass('truncated')) {
                                    $(this).removeClass('truncated').addClass('expanded').attr('title', 'Truncate').html(textDefault);
                                } else {
                                    $(this).addClass('truncated').removeClass('expanded').attr('title', 'Expand').html(textTruncated);
                                }
                            });
						}
					});
				};
			init();
		});
	};
})(jQuery);
