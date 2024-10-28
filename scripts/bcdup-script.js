jQuery(document).ready(function($) {
	$('input:checkbox[id*=include_drafts]').click(function () {
		var include_only_drafts = $('input:checkbox[id*=include_only_drafts]');
		var include_only_drafts_label = $('label[id*=include_only_drafts_label]');
		
		if ( $(this).is(':checked') ) {
			include_only_drafts.removeAttr('disabled');
			include_only_drafts_label.removeClass("bcdup-disabled");
		} else {
			include_only_drafts.attr('disabled', 'disabled');
			include_only_drafts_label.addClass("bcdup-disabled");
		}
	});

	$('.widgets-sortables').ajaxSuccess(function() {
		$('input:checkbox[id*=include_drafts]').click(function () {
			var include_only_drafts = $('input:checkbox[id*=include_only_drafts]');
			var include_only_drafts_label = $('label[id*=include_only_drafts_label]');
			
			if ( $(this).is(':checked') ) {
				include_only_drafts.removeAttr('disabled');
				include_only_drafts_label.removeClass("bcdup-disabled");
			} else {
				include_only_drafts.attr('disabled', 'disabled');
				include_only_drafts_label.addClass("bcdup-disabled");
			}
		});
	});
});
