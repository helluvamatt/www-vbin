
var onLoad = function() {
	
	// Obtain a reference to the editor field
	var $editorField = $('#pasteField');
	
	// Instantiate an ACE editor
	var editor = ace.edit('pasteFieldEditor');
	
	// Provides: modes, modesByName, getModeForPath
	var modeList = ace.require('ace/ext/modelist');
	
	// Setup editor
	editor.setTheme("ace/theme/twilight");
	editor.getSession().setMode("ace/mode/javascript");
	
	// Bind the editor to the editorField and hide the field
	$editorField.hide();
	editor.getSession().setValue($editorField.val());
	editor.getSession().on('change', function(){
		$editorField.val(editor.getSession().getValue());
	});
    
	// Get the app url from the template
	//var $body = $('body');
	//var appRootUrl = $body.data('rootUrl');
	
	// Obtain references to the language menu and language save field
	var $langMenu = $('#languageMenu');
	var $langField = $('#languageField');
	
	// Handle changes to the language menu
	$langMenu.on('change', function() {
		var $this = $(this);
		var mode = $this.val();
		editor.getSession().setMode(mode);
		$langField.val(mode);
	});
	
	// Populate the Language Menu
	for (var i in modeList.modes)
	{
		var mode = modeList.modes[i];
		var $entry = $('<option value="' + mode.mode + '">' + mode.caption + '</option>');
		$entry.data('mode', mode);
		$langMenu.append($entry);
	}
	
	// Set the menu to the saved language value if editing
	var origLangVal = $langMenu.val();
	if (origLangVal != '')
	{
		$langMenu.val(origLangVal);
	}
	
};
$(onLoad);
