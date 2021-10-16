//=require plugins/ace/ace.js

$(document).ready(function() {
    var editor_instance = 1;
	ace.config.set("basePath", "/public/js/plugins/ace");
    $('textarea[data-editor]').each(function() {
        var editor_area = $(this);
        editor_area.after('<div id="codeflask_' + editor_instance + '" class="codeflask"></div>');
        editor_area.hide();

		var editor = ace.edit('codeflask_' + editor_instance);
  		editor.getSession().setMode('ace/mode/' + editor_area.attr('data-editor'));
		editor.getSession().setUseWrapMode(true);
		editor.getSession().setValue(editor_area.val());
		editor.getSession().setUseWorker(false)
		editor.getSession().on('change', function() {
			editor_area.val(editor.getSession().getValue());
		});

        editor_instance++;
    });
});
