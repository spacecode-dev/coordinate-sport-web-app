"use strict";

var KTLayoutHeaderTopbar = function() {
    // Private properties
	var _toggleElement;
    var _toggleObject;

    // Private functions
    var _init = function() {
		_toggleObject = new KTToggle(_toggleElement, {
			target: KTUtil.getBody(),
			targetState: 'topbar-mobile-on',
			toggleState: 'active',
		});

		_toggleObject.on('toggle', function(toggle) {
			if(toggle.getState() == "on"){
				var height = $("#account_overridden").height();
				var header_height = $("#kt_header_mobile").height();
				$(".topbar").css({'margin-top': (height + header_height - 1) + "px"});
			}else{
				var height = $("#account_overridden").height();
				$(".topbar").css({'margin-top': height + "px"});
			}
		});
    }

    // Public methods
	return {
		init: function(id) {
            _toggleElement = KTUtil.getById(id);

			if (!_toggleElement) {
                return;
            }

            // Initialize
            _init();
		},

        getToggleElement: function() {
            return _toggleElement;
        },
	};
}();

// Webpack support
if (typeof module !== 'undefined') {
	module.exports = KTLayoutHeaderTopbar;
}
