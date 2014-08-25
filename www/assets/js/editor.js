
var onLoad = function() {
	
	var messageTimeout = undefined;
	
	// Obtain references to the form and various form fields
	var $editorForm = $('#editorForm');
	var $idField = $('#idField');
	var $langField = $('#languageField');
	var $titleField = $('#titleField');
	var $editorField = $('#pasteField');
	
	var $ajaxMessage = $('#ajaxMessage');
	var $message = $ajaxMessage.children('div');
	
	var hideMessage = function()
	{
		$ajaxMessage.fadeOut(200);
	};
	
	var showMessage = function(html, alertType, timeout)
	{
		if (typeof alertType === 'undefined') alertType = 'info';
		if (typeof messageTimeout !== 'undefined') window.clearTimeout(messageTimeout);
			
		$message.html(html);
		$message.removeClass();
		$message.addClass('message alert alert-' + alertType);
		if ($ajaxMessage.is(':hidden')) $ajaxMessage.fadeIn(200);
		
		if (typeof timeout !== 'undefined')
		{
			messageTimeout = window.setTimeout(function() {
				hideMessage();
			}, timeout);
		}
	};
	
	$('li a#tbHistoryAction').on('click', function(e) {
		// Pick up the ID from the form field
		var id = $idField.val();
		
		// Only navigate if we have a valid ID
		if (id != '')
		{
			// Create the URL...
			var url = $(this).data('hrefTemplate').replace('%model.id%', id);
			
			// ...and away we go!
			window.location.href = url;
		}
		
		var e = e || window.event;
		e.preventDefault();
	});
	
	$('li a#tbSaveAction').on('click', function(e) {
		
		// Show loading...
		showMessage('<i class="fa fa-spin fa-refresh"></i>&nbsp;Loading...');
		
		$.ajax({
			data: $editorForm.serialize(),
			error: function( jqXHR, textStatus, errorThrown )
			{
				showMessage('<i class="fa fa-times-circle"></i>&nbsp;There was a problem communicating with the server.', 'danger', 10000);
			},
			success: function ( data, textStatus, jqXHR )
			{
				if (data.error)
				{
					showMessage('<i class="fa fa-times-circle"></i>&nbsp;' + data.error, 'danger', 10000);
				}
				else if (data.status == 0)
				{
					window.removeEventListener('beforeunload', beforeUnloadHandler);
					showMessage('<i class="fa fa-check">&nbsp;Saved!</i>', 'success', 5000);
				}
			},
			type: 'POST',
			url: $editorForm.data('saveUrl')
		});
		
		var e = e || window.event;
		e.preventDefault();
	});
	
	// Prevent form submit on Return/Enter
	$editorForm.on("keyup keypress", 'input', function(e) {
		var code = e.keyCode || e.which; 
		if (code == 13) {
			e.preventDefault();
			return false;
		}
	});
	
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
		
		window.addEventListener('beforeunload', beforeUnloadHandler);
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
	
	// Obtain a reference to the language menu
	var $langMenu = $('#languageMenu');
	
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
	
	// load paste revision from hash URL
	var loadFromHash = function(hash)
	{
		// Ensure the hash is valid
		var hashRe = new RegExp("^\#\!\/([0-9a-zA-z]{8})(?:\/([0-9a-zA-z]{8}))?");
		var matched = hash.match(hashRe);
		if (matched)
		{
			var id = matched[1];
			var rev = '';
			if (matched[2]) rev = '/' + matched[2];
			var ajaxUrl = $editorForm.data('loadUrl').replace('%id%', id).replace('/%rev%', rev);
			
			// Show loading...
			showMessage('<i class="fa fa-spin fa-refresh"></i>&nbsp;Loading...');
			
			// Launch AJAX request
			$.ajax({
				error: function( jqXHR, textStatus, errorThrown )
				{
					showMessage('<i class="fa fa-times-circle"></i>&nbsp;There was a problem communicating with the server: ' + errorThrown, 'danger', 10000);
				},
				success: function ( data, textStatus, jqXHR )
				{
					if (data.error)
					{
						showMessage('<i class="fa fa-times-circle"></i>&nbsp;' + data.error, 'danger', 10000);
					}
					else if (data.status == 0)
					{
						// Populate the ID field:
						$idField.val(data.model.id);
						
						// Populate the title field:
						$titleField.val(data.revision.title);

						// Populate the paste editor:
						editor.getSession().setValue(data.revision.data);
						window.removeEventListener('beforeunload', beforeUnloadHandler);
						
						// Propagate the language around the UI:
						setMode(data.revision.lang);

						// Show the history menu item:
						$('#tbHistoryActionItem').show();
						
						// Clear the loading message
						hideMessage();
					}
				},
				type: 'GET',
				url: ajaxUrl
			});
		}
	}
	
	// Bind hash listener
	$(window).on('hashchange', function() {
		if (window.location.hash != '')
			loadFromHash(window.location.hash);
	});
	
	// Initial hash load on page load
	$(window).trigger('hashchange');
	
};
$(onLoad);
