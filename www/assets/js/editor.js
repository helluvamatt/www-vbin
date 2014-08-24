
var onLoad = function() {
	
	$('li a#tbSaveAction').on('click', function() {
		window.beforeunload = null;
		$('#editorForm').submit();
	});
	
	// Obtain a reference to the editor field
	var $editorField = $('#pasteField');
	
	// Instantiate an ACE editor
	var editor = ace.edit('pasteFieldEditor');
	
	// Provides: modes, modesByName, getModeForPath
	var modeList = ace.require('ace/ext/modelist');
	
	// Setup editor
	editor.setTheme("ace/theme/twilight");
	
	var editorDirty = false;
	
	// Check if the editor is dirty, and prompt the user
	var beforeUnloadHandler = function(e) {
		// If we haven't been passed the event get the window.event
	    e = e || window.event;

	    var message = 'You have no saved your changes to this paste.';

	    // For IE6-8 and Firefox prior to version 4
	    if (e) 
	    {
	        e.returnValue = message;
	    }

	    // For Chrome, Safari, IE8+ and Opera 12+
	    return message;
	};
	
	// Bind the editor to the editorField and hide the field
	$editorField.hide();
	editor.getSession().setValue($editorField.val());
	editor.getSession().on('change', function(){
		$editorField.val(editor.getSession().getValue());
		
		window.beforeunload = beforeUnloadHandler;
	});
	
	editor.commands.addCommand({
	    name: 'save',
	    bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
	    exec: function(editor) {
	    	$('#tbSaveAction').trigger('click');
	    },
	    readOnly: false
	});
    
	// Get the app url from the template
	//var $body = $('body');
	//var appRootUrl = $body.data('rootUrl');
	
	// Set mode on editor and set menu
	var setMode = function(modeName)
	{
		$langMenu.val(modeName);
		editor.getSession().setMode(modeName != '' ? modeName : 'ace/mode/text');
		$langField.val(modeName);
	};
	
	// Obtain references to the language menu and language save field
	var $langMenu = $('#languageMenu');
	var $langField = $('#languageField');
	
	// Handle changes to the language menu
	$langMenu.on('change', function() {
		var $this = $(this);
		var mode = $this.val();
		setMode(mode);
	});
	
	// Get a mode name for a path
	// Adapted from ace/ext-modelist.js
	// with special handling of no match
	var getModeNameForPath = function(modes, val)
	{
		var modeName = '';
		var fileName = val.split(/[\/\\]/).pop();
		for (var i = 0; i < modes.length; i++) {
			if (modes[i].supportsFile(fileName)) {
				modeName = modes[i].mode;
				break;
			}
		}
		return modeName;
	};
	
	// Handle changes to the title field
	var $titleField = $('#titleField');
	var handleTitleChange = function() {
		var val = $(this).val();
		var modeName = getModeNameForPath(modeList.modes, val);
		setMode(modeName);
	};
	$titleField.on('change', handleTitleChange);
	$titleField.on('keyup', handleTitleChange);
	
	// Populate the Language Menu
	for (var i in modeList.modes)
	{
		var mode = modeList.modes[i];
		var $entry = $('<option value="' + mode.mode + '">' + mode.caption + '</option>');
		$entry.data('mode', mode);
		$langMenu.append($entry);
	}
	
	// Set the menu to the saved language value if editing
	var origLangVal = $langField.val();
	setMode(origLangVal);
	
};
$(onLoad);
