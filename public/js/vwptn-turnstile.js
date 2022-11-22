jQuery(document).ready(function(){
	//clear any previous responses
	jQuery(".cf-turnstile input").val('');
});
jQuery(document).on( 'nfFormReady', function() {	
		jQuery('.vwpturnstile-wrap .nf-field-element').append('<div class="cf-turnstile"></div>');	
		
		//fetch site key and status
		jQuery.ajax({
			dataType: "json",
			url: vwpscripts.siteUrl+'/wp-admin/admin-ajax.php',
			type: 'post',
			data: { action: 'vwptn_turnstile_data_fetch','nonce':vwpscripts.nonce},
			success: function(vwptn_settings) {				
				var vwptn_status = vwptn_settings.vwptnstatus;
				//console.log(vwptn_status);
				var vwptn_turnstile_site_key = vwptn_settings.sitekey;
				
				if(vwptn_status == 1) {
					turnstile.render('.cf-turnstile', {
						sitekey: vwptn_turnstile_site_key,
						callback: function(token) {
							jQuery('.vwpturnstile-wrap input').val(token);
							
							setTimeout(function() {
								jQuery('.vwpturnstile-wrap input').trigger('change');
								
							}, 500);
							
							
						},
					});	
				}
				
				
			}
		});	
		
	
});
