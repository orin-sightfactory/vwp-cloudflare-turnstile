jQuery(document).ready(function(){
	//clear any previous responses
	jQuery(".cf-turnstile input").val('');
});
jQuery(document).on( 'nfFormReady', function() {	
		jQuery('.vwpturnstile-wrap .nf-field-element').append('<div class="cf-turnstile"></div>');	
		
		
		jQuery.ajax({
			url: vwpscripts.siteUrl+'/wp-admin/admin-ajax.php',
			type: 'post',
			data: { action: 'vwp_turnstile_data_fetch'},
			success: function(vwp_turnstile_site_key) {
				
				turnstile.render('.cf-turnstile', {
					sitekey: vwp_turnstile_site_key,
					callback: function(token) {
						jQuery('.vwpturnstile-wrap input').val(token);
						
						setTimeout(function() {
							jQuery('.vwpturnstile-wrap input').trigger('change');
							
						}, 500);
						
						
					},
				});	
				
				
			}
		});	
		
	
});