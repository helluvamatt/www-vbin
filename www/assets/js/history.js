var onLoad = function()
{
	
	var $compareBtn = $('#compareBtn');
	
	var $radioRevFrom = $('input.radio-revFrom');
	var $radioRevTo = $('input.radio-revTo');
	
	var selectedFromIndex = undefined;
	var selectedToIndex = undefined;
	
	var updateCompareLink = function()
	{
		var selectedFromRev = $('input[name="revFrom"]:checked').val();
		var selectedToRev = $('input[name="revTo"]:checked').val();
		if (selectedFromRev != '' && selectedToRev != '')
		{
			var diffSpec = selectedFromRev + '+' + selectedToRev;
			var href = $compareBtn.data('diffLink').replace('%spec%', diffSpec);
			$compareBtn.attr('href', href);
		}
	};
	
	$radioRevTo.on('change', function() {
		var $this = $(this);
		selectedToIndex = $this.data('index');
		$radioRevFrom.prop('disabled', false);
		$radioRevFrom.filter(function(index, element ) {
			return index < selectedToIndex;
		}).prop('disabled', true);
		updateCompareLink();
	});
	
	$radioRevFrom.on('change', function() {
		var $this = $(this);
		selectedFromIndex = $this.data('index');
		$radioRevTo.prop('disabled', false);
		$radioRevTo.filter(function(index, element ) {
			return index >= selectedFromIndex;
		}).prop('disabled', true);
		updateCompareLink();
	});
};

$(onLoad);
