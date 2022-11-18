<?php
 
/**
 
 * @package VWPTurnstile
 
 */
 
/*
 
Plugin Name: VisualWP - Cloudflare Turnstile 
Plugin URI: https://sightfactory.com/wordpress-plugins/turnstile 
Description: Increase security and protect against bots, spammers and hackers. Add Cloudflare Turnstile to WordPress and Ninja Forms.
Version: 1.0.1
Author: Sightfactory 
Author URI: https://sightfactory.com/wordpress-plugins/
Requires at least: 5.9
Tested up to: 6.1
Requires PHP: 7.4 
License: GPLv2 or later 
Text Domain: vwpturnstile
 
*/

function vwp_turnstile_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=vwp-turnstile">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'vwp_turnstile_add_settings_link' );


function vwp_init_turnstile_scripts() {
	if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
		wp_enqueue_script( 'vwp-turnstilejs', 'https://challenges.cloudflare.com/turnstile/v0/api.js' , '1.0', true );
		//wp_enqueue_script( 'vwp-turnstilejs-render', 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback' , '1.0', true );

	}
	else {
		wp_enqueue_script( 'vwp-turnstilejs-render', 'https://challenges.cloudflare.com/turnstile/v0/api.js' , '1.0', true );
		if(!is_admin()) {
			wp_enqueue_script('vwp-turnstile-js', plugins_url('/public/js/vwp-turnstile.js' , __FILE__) , array('jquery','vwp-turnstilejs-render'));
			wp_localize_script('vwp-turnstile-js', 'vwpscripts', array(
				'siteUrl' => get_site_url(),
			));
			wp_enqueue_style('vwp-turnstyle-css', plugins_url('/public/css/vwp-turnstile.css' , __FILE__), array());
		}
		
		
		if(is_admin()) {
			wp_enqueue_style('vwp-turnstyle-admin-css', plugins_url('/admin/css/vwp.css' , __FILE__), array());
			wp_enqueue_style('vwp-gf-sen','https://fonts.googleapis.com/css2?family=Sen:wght@400;700&display=swap');
		}
		
	}


}

function vwp_init_turnstile_verification(WP_User $user) {
	
	$data = sanitize_text_field($_POST['cf-turnstile-response']);
	$response = vwp_get_turnstile_response($data);	
	if($response != 1) {
		$user = new WP_Error( 'authentication_failed',esc_html($response));		  
	}
	return $user;

}

function vwp_init_turnstile_verification_register($errors, $sanitized_user_login, $user_email ) {
	
	$data = sanitize_text_field($_POST['cf-turnstile-response']);
	$response = vwp_get_turnstile_response($data);	
	if($response != 1) {
		    $errors->add( 'vwp_turnstile_error', __( esc_html($response) ) );  
	}
	return $errors;

}

function vwp_init_turnstile_verification_comment($commentdata) {
	
	$data = sanitize_text_field($_POST['cf-turnstile-response']);
	$response = vwp_get_turnstile_response($data);
	
	if($response != 1) {
		
		wp_die( __( esc_html($response).'<br>You will be redirected in a few seconds...<script>setTimeout(function(){
			window.history.back()
		}, 6000);</script><p><a href="javascript:history.back()">« Back</a></p>' ) );  
	}
	return $commentdata;

}

add_action( 'login_enqueue_scripts', 'vwp_init_turnstile_scripts' );


add_action( 'init', 'vwp_init_turnstile_scripts' );

@$vwp_turnstile_key_check = get_option('vwp_turnstile_site_key');

if(strlen($vwp_turnstile_key_check) > 0) {
/*Login*/
add_action( 'login_form', 'vwp_init_turnstile_widget' );
add_action('wp_authenticate_user','vwp_init_turnstile_verification',10, 1);

/*Regsistration*/
add_action('register_form','vwp_init_turnstile_widget');
add_action('registration_errors', 'vwp_init_turnstile_verification_register', 10, 3);

/*Lost Password*/
add_action('lostpassword_form','vwp_init_turnstile_widget');
add_action('lostpassword_post','vwp_init_turnstile_verification', 10, 1);

/*WordPress Comments*/
add_action("comment_form_logged_in_after", "vwp_init_turnstile_widget");
add_action("comment_form_after_fields", "vwp_init_turnstile_widget");
add_action('preprocess_comment','vwp_init_turnstile_verification_comment', 10, 1);

//add_action('comment_form_submit_button','cfturnstile_field_comment', 100, 2);

/*Ninja Forms*/
add_action('lostpassword_form','vwp_init_turnstile_widget');
add_action('lostpassword_post','vwp_init_turnstile_verification', 10, 1);

/*Woocommerce*/
//TODO
}
/**/
function vwp_init_turnstile_widget() { 
	$vwp_turnstile_site_key_value = get_option('vwp_turnstile_site_key');
	echo sprintf('<div class="cf-turnstile" data-sitekey="%s"></div><style>.cf-turnstile iframe { max-width:270px !important}</style>',esc_html($vwp_turnstile_site_key_value));

}

function vwp_get_turnstile_response($data) {
	$vwp_turnstile_secret_key_value = get_option('vwp_turnstile_secret_key');	
	$request = NULL; 
	$headers = array(
				'body' => [
					'secret' => esc_html($vwp_turnstile_secret_key_value),
					'response' => $data
				]
			);
	
	$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	
	/*
	'timeout'     => 60, // added
		'redirection' => 5,  // added
		'blocking'    => true, // added
		'sslverify' => true,
		'method'      => 'POST',	
		'httpversion' => '1.1'
	*/
	
	try {
		$request = wp_remote_post($url,$headers);
		
		$request = wp_remote_retrieve_body($request);
		$response  = json_decode($request);	
		
		if($response->success) {
			return true;
		}
		else {
			
			$errors = '';
			foreach($response->{'error-codes'} as $code) {
				$errors .= vwp_get_turnstile_errors($code);
			}
			return esc_html($errors);
		
		}
			
		
	}	
		
	catch (Exception $e) {
		return $e->getMessage();
	}
	
}

function vwp_get_turnstile_errors($error_code) {
	switch ($error_code) {
		case 'missing-input-secret':
			return esc_html('An unexpected error occurred. The secret parameter was not passed.');
		case 'invalid-input-secret':
			return esc_html('An unexpected error occurred. The secret parameter was invalid or did not exist.');
		case 'missing-input-response':
			return esc_html('An unexpected error occurred. The response parameter was not passed.');
		case 'invalid-input-response':
			return esc_html('An unexpected error occurred. The response parameter is invalid or has expired.');
		case 'bad-request':
			return esc_html('An unexpected error occurred. The request was rejected because it was malformed.');
		case 'timeout-or-duplicate':
			return esc_html('An unexpected error occurred. The response parameter has already been validated before.');
		case 'internal-error':
			return esc_html('An unexpected error occurred. An internal error happened while validating the response. The request can be retried.');	
	}
}


if ( in_array( 'ninja-forms/ninja-forms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	// Create turnstile field for Ninja Forms
	add_filter( 'ninja_forms_register_fields', function( $fields ) {
		$fields['vwpturnstile'] = new Vwpturnstile;
		return $fields;
	} );

    

	class Vwpturnstile extends NF_Abstracts_Input {			
		protected $_name = 'vwpturnstile';
		protected $_nicename = 'Turnstile';
		protected $_section = 'misc';
		protected $_icon = 'globe';
		protected $_type = 'text';
		protected $_templates = 'text';
		protected $_wrap_template = 'wrap-no-label';
		protected $_settings_only = array(
			'key', 'label', 'admin_label'
		);
		public function __construct() {
			parent::__construct();
			$this->_nicename = __( 'Turnstile', 'ninja-forms' );
			$this->_settings[ 'label' ][ 'width' ] = 'full';
		}
	}

	// Set value for vwpturnstile field
	add_filter( 'ninja_forms_render_default_value', 'nf_default_value_vwpturnstile' , 10 , 3);
	function nf_default_value_vwpturnstile( $default_value, $field_type, $field_settings ) {
		
		if ( 'vwpturnstile' == $field_type && in_array( 'vwpturnstile', $field_settings ) ) {
			$default_value = '';
		}
		return esc_html($default_value);
	}


	add_filter( 'ninja_forms_submit_data', 'my_ninja_forms_submit_data' );

	function my_ninja_forms_submit_data( $form_data ) {
 
	  foreach( $form_data[ 'fields' ] as $field ) { // Field settigns, including the field key and value.
	   
	  if(stristr($field['key'],'turnstile')) {
		  
	   $response = esc_html(vwp_get_turnstile_response($field['value']));	
	   
		   if($response != 1) {
			   
				 $errors = [
				  __( 'An unexpected error occurred - '.$response, 'vwpturnstile' )
				];
				
				foreach( $form_data[ 'fields' ] as $field ) { // Field settigns, including the field key and value.
				

						//print_r($field);
						if(stristr($field['key'],'turnstile')) {
							$form_data['errors']['fields'][$field['id']] = $response;
						}
					
				}
				
				  return $form_data;
				  wp_die(); 
			}
			else {
		//		 $form_settings = $form_data[ 'settings' ]; // Form settings.
		  
		//		  $extra_data = $form_data[ 'extra' ]; // Extra data included with the submission.
				
				  return $form_data;
			}
		
		}
	  }
	  
	 
	}

}

if( ! function_exists( 'vwp_current_user_has_role' ) ){
    function vwp_current_user_has_role( $role ) {

        $user = get_userdata( get_current_user_id() );
        if( ! $user || ! $user->roles ){
            return false;
        }

        if( is_array( $role ) ){
            return array_intersect( $role, (array) $user->roles ) ? true : false;
        }

        return in_array( $role, (array) $user->roles );
    }
}

add_action( 'admin_menu', 'vwp_turnstile_menu' );

function vwp_turnstile_menu() {
	add_options_page( 'Turnstile', 'Turnstile', 'manage_options', 'vwp-turnstile', 'vwp_turnstile_menu_options' );
}

function vwp_turnstile_menu_options() {
	$update_notice = '';
	$plugin_title = get_admin_page_title();
    $plugin_page = "Turnstile Options";
	$user = get_userdata( get_current_user_id() );
    if( ! $user || ! $user->roles ){
        return false;
    }
	
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	else {
		if (isset($_POST['vwp_turnstile_site_key']) && vwp_current_user_has_role( 'administrator' ) )  {	
			check_admin_referer( 'vwp_option_page_action' );
			update_option('vwp_turnstile_site_key', sanitize_text_field($_POST['vwp_turnstile_site_key']));
			update_option('vwp_turnstile_secret_key', sanitize_text_field($_POST['vwp_turnstile_secret_key']));
			$update_notice = 'Settings Updated';
		} 
		
	
		$vwp_turnstile_site_key_value = get_option('vwp_turnstile_site_key');
		$vwp_turnstile_secret_key_value = get_option('vwp_turnstile_secret_key');
	?>
	<div class="wrap">
	
	<div class="vwp-plugin-header">
	<a href="https://www.sightfactory.com/wordpress-plugins/visual-wp" target="_blank">
	<img align="center" style="width:25px;margin-top:-5px" src="<?php echo plugins_url('/admin/images/vwp-logo.png' , __FILE__)?>"/> <span class="plugin-creator">VisualWP</span></a><?php esc_html_e($plugin_title) ?>
	
	
	<img align="center" style="float:right;width:55px;margin-top:-15px" src="<?php echo plugins_url('/admin/images/vwp-turnstile-logo.gif' , __FILE__)?>"/>
	</div>
	<div class="vwp-plugin-body">
		
		<?php 
		echo sprintf("<span class='vwp-notice'>%s</span>",esc_html($update_notice));
		$update_notice = '';
		?>
		
		<form method="POST">
		<div>
		<label for="awesome_text"><p>Turnstile Site Key</p></label>
		
		<input type="password" name="vwp_turnstile_site_key" id="turnstile_site_key" size="40" value="<?php echo esc_html(@$vwp_turnstile_site_key_value); ?>">
		</div>
		<div>
		 <label for="awesome_text"><p>Turnstile Secret Key</p></label>
		<input type="password" name="vwp_turnstile_secret_key" id="turnstile_secret_key" size="40" value="<?php echo esc_html(@$vwp_turnstile_secret_key_value); ?>">
		</div>
		<?php wp_nonce_field( 'vwp_option_page_action' ); ?>
		<br>
		<input type="submit" value="Save Changes" class="button button-primary button-large">
		
		</form>
		<p>Don't have a Cloudflare Turnstile key? <a href="https://www.cloudflare.com/lp/turnstile/" target="_blank">Create one here</a></p>.
		<!--<p><a href="https://www.sightfactory.com/wordpress-plugins/vwp-turnstile" target="_blank">View Documentation</a></p>-->
		
	</div>
	</div>
	<?php
	}
	
	
	
}


function vwp_turnstile_defer_scripts( $tag, $handle, $src ) {
  $defer = array( 
    'vwp-turnstilejs'
  );

  if ( in_array( $handle, $defer ) ) {
     return '<script src="' . esc_html($src) . '" type="text/javascript" async defer></script>' . "\n";
  }
    
    return $tag;
} 

add_filter( 'script_loader_tag', 'vwp_turnstile_defer_scripts', 10, 3 );

// Provides a way to get the site key for forms that are created dynamically
add_action('wp_ajax_vwp_turnstile_data_fetch' , 'vwp_data_fetch');
add_action('wp_ajax_nopriv_vwp_turnstile_data_fetch','vwp_data_fetch');
function vwp_data_fetch(){
	esc_html_e(get_option('vwp_turnstile_site_key'));
	wp_die();
}



?>
