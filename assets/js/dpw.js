(function($) {

	$(document).ready(function(){
	
		$('.dpw-tabs li').click(function(){
			var tab_id = $(this).attr('data-tab');

			$('.dpw-tabs li').removeClass('current');
			$('#'+tab_id).parent().find('.tab-content').removeClass('current');

			$(this).addClass('current');
			$("#"+tab_id).addClass('current');
		});

	});

})( jQuery );