
$(document).ready(function()
{
	// Fix input element click problem
	$('.dropdown input, .dropdown label').click(function(e) {
		e.stopPropagation();
	});
	
	$('.with-tooltip').tooltip({
		placement: 'auto top',
		container: 'body'
	});	
});
