<?php
add_action('wp_enqueue_scripts', 'vpl_print_styles_and_scripts');

/**
 * Enqueue style and scripts according to request
 */
function vpl_print_styles_and_scripts() {
	
	$styles_path = VPL_ROOT_URL.'/inc/css/';
	$js_path = VPL_ROOT_URL.'/inc/js/';
	
	wp_register_style('vpl_styles', $styles_path . 'vpl_styles.css');
    wp_enqueue_style( 'vpl_styles');
	
	wp_register_script('vpl_scripts_view_poll', $js_path . 'view_poll.js');
	wp_enqueue_script( 'vpl_scripts_view_poll');
	
	
	if( 'new' == VPL_CURRENT_ACTION ||  'edit' == VPL_CURRENT_ACTION ) {
		
		wp_register_style('vpl_styles_jquery_ui', $styles_path . 'jquery-ui-1.8.17.custom.css');
		wp_enqueue_style( 'vpl_styles_jquery_ui');
		
		/* Scripts */
		
		$lang = WPLANG != '' ? WPLANG : 'en_GB';
		$script = $js_path . ' /datepicker/i18n/' . $lang . '.js';
		wp_enqueue_script('date_picker_i', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider'));
		
		wp_enqueue_script( 'jquery-ui-widget');
		wp_enqueue_script( 'jquery-ui-position');
		wp_enqueue_script( 'jquery-ui-autocomplete');
		
		
		wp_register_script('vpl_scripts_timepicker', $js_path . 'jquery.time_addon.js');
		wp_enqueue_script( 'vpl_scripts_timepicker');
		
		wp_register_script('vpl_scripts_poll_edit_form', $js_path . 'poll_edit_form.js');
		wp_enqueue_script( 'vpl_scripts_poll_edit_form');
		
	} 
	
	
	if( VPL_CURRENT_MODULE == VPL_ALL_POLLS_MODULE ){
		if ( file_exists( get_stylesheet_directory() . '/_inc/css/adminbar.css' ) ) // Backwards compatibility
		$stylesheet = get_stylesheet_directory_uri() . '/_inc/css/adminbar.css';
		elseif ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			$stylesheet = BP_PLUGIN_URL . '/bp-core/css/buddybar.dev.css';
		else
			$stylesheet = BP_PLUGIN_URL . '/bp-core/css/buddybar.css';

		wp_enqueue_style( 'bp-admin-bar', apply_filters( 'bp_core_admin_bar_css', $stylesheet ), array(), '20110723' );
	}
	
}