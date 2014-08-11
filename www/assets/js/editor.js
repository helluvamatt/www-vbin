
var onLoad = function() {
	
	var $editorField = $('#pasteField');
	var editor = CodeMirror(function(elt) {
		$editorField.first().replaceWith(elt);
	}, {
		value: $editorField.val(),
		lineNumbers: true,
	});
	
};
$(onLoad);
