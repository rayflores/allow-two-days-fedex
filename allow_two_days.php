<?php

/**
 * Plugin Name: Woocommerce Shipping Methods Per Product
 * Plugin URI: http://www.rayflores.com/plugins/wc-allow-two-days 
 * Version: 1.0
 * Author: Ray Flores
 * Author URI: http://www.rayflores.com 
 * Description: Extend Woocommerce plugin to allow two-days shipping method to a product
 * Requires at least: 4.1
 * Tested up to: 4.1

 */
 /**
 * Add a $5 surcharge to your checkout
 * change the $amount to set the surcharge to a value to suit
 * Uses the WooCommerce fees API
 *
  */
 
 add_action('admin_menu', 'create_options_page');
function create_options_page() {  
	add_menu_page('Shipping Zips', 'Shipping Zips', 'administrator', 'ship_to_zips', 'ship_to_zips_options_page');
}
function ship_to_zips_options_page() { ?>  
<div id="theme-options-wrap" class="widefat">    
	<div class="icon32" id="icon-tools"> <br /> 
	</div>    
	<h2>Add Zip Codes for Shipping via Two Days</h2>    
		<p>Enter zip codes, comma seperated, in the box below:</p>    
			<form method="post" action="options.php" enctype="multipart/form-data"> 
			<?php settings_fields('plugin_options'); ?>  
			<?php do_settings_sections(__FILE__); ?>  
				<p class="submit">    
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />  
				</p>
			</form> 
</div>
<?php }
add_action('admin_init', 'register_and_build_fields');
function register_and_build_fields() {   
	register_setting('plugin_options', 'plugin_options', 'validate_setting');
	//main section
	add_settings_section('main_section', 'Main Settings', 'section_cb', __FILE__);
	// fields in main section
	add_settings_field('entered_zip_codes', 'Zip Codes:', 'entered_zip_codes_setting', __FILE__, 'main_section');

}

function validate_setting($plugin_options) { 
	$keys = array_keys($_FILES); 
	$i = 0; 
		foreach ( $_FILES as $image ) {   
			// if a files was upload   
			if ($image['size']) {     
				// if it is an image     
				if ( preg_match('/(jpg|jpeg|png|gif)$/', $image['type']) ) {       
					$override = array('test_form' => false);       
					// save the file, and store an array, containing its location in $file       
					$file = wp_handle_upload( $image, $override );       
					$plugin_options[$keys[$i]] = $file['url'];     
					} else {       
					// Not an image.        
					$options = get_option('plugin_options');       
					$plugin_options[$keys[$i]] = $options[$logo];       
					// Die and let the user know that they made a mistake.       
						wp_die('No image was uploaded.');     
					}   
			}   // Else, the user didn't upload a file.   
			// Retain the image that's already on file.   
			else {     
				$options = get_option('plugin_options');     
				$plugin_options[$keys[$i]] = $options[$keys[$i]];   
			}   $i++; 
		} return $plugin_options;
}
function section_cb() {
}

// zip code box
function entered_zip_codes_setting() {  
	$options = get_option('plugin_options');  
	echo "<textarea name='plugin_options[entered_zip_codes]' value='{$options['entered_zip_codes']}' cols='20' rows='6'>" . $options['entered_zip_codes'] . "</textarea>";

}

/**
 * woocommerce_package_rates is a 2.1+ hook
 */
add_filter( 'woocommerce_package_rates', 'rf_enable_two_day_per_zips', 10, 2 );
  
/**
 * Hide fedex:FEDEX_GROUND shipping rate when not within zip codes per backend settings 
 *
 * @param array $rates Array of rates found from FEDEX zip range
 * @param array $package The package array/object being shipped, maybe?
 * @return array of modified rates
 */
function rf_enable_two_day_per_zips( $rates, $package ) {
     // get available zip codes from backend options
    $options = get_option('plugin_options');  
	$all_ship_zips = $options['entered_zip_codes'];
	$ship_zips = explode(",",$all_ship_zips );
    // Only modify if postcade is in backend settings
	if ( isset( $rates['fedex:FEDEX_GROUND'] ) ) {
 if (!in_array( WC()->customer->get_postcode(), $ship_zips ) ) { 
     
        // To unset a single rate/method, do the following. This example unsets flat_rate shipping
        unset( $rates['fedex:FEDEX_GROUND'] );

    }
     
    return $rates;
	}
return $rates;
}
