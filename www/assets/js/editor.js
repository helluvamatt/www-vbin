
var onLoad = function() {
	
	var $editorField = $('#pasteField');
	
	var editor = ace.edit('pasteFieldEditor');
	editor.setTheme("ace/theme/twilight");
	editor.getSession().setMode("ace/mode/javascript");
	
	$editorField.hide();
	editor.getSession().setValue($editorField.val());
	editor.getSession().on('change', function(){
		$editorField.val(editor.getSession().getValue());
	});
    
	//var $body = $('body');
	//var appRootUrl = $body.data('rootUrl');
	
	var $modeMenu = $('#modeMenu');
	$modeMenu.on('click', 'li a', function() {
		var $this = $(this);
		var mode = $this.data('mode');
		console.log('setting mode to "' + mode + '"');
		editor.getSession().setMode(mode);
	});
	
	
};
$(onLoad);
