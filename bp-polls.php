<?php
/**
 * Plugin Name: BP Polls
 * Plugin URI:  http://buddypress.org
 * Description: Add polls feature to your buddypress site
 * Author:      Themekraft
 * Version:     1.2
 * Author URI:  http://themekraft.com
 */

add_action('bp_init', 'vpl_init');

register_activation_hook(__FILE__, 'vpl_activation');


function vpl_init() {
	global $bp,$wpdb;
	
	load_plugin_textdomain( 'bp_polls', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
	
	define( VPL_DEBUG, false );
	define( VPL_ROOT_URL, plugins_url('', __FILE__));
	define( VPL_ROOT_PATH, plugin_dir_path(__FILE__));
	
	define( VPL_TABLE_POLLS, $wpdb->prefix . "vpl_polls");
	define( VPL_TABLE_QUESTIONS, $wpdb->prefix . "vpl_questions");
	define( VPL_TABLE_ANSWERS, $wpdb->prefix . "vpl_answers");
	define( VPL_TABLE_VOTES, $wpdb->prefix . "vpl_votes");
	define( VPL_TABLE_TAXONOMY, $wpdb->prefix . "vpl_taxonomy");
	define( VPL_TABLE_POLL_TAXONOMY, $wpdb->prefix . "vpl_poll_taxonomy");
	
	define( VPL_COMPONENT_SLUG, 'polls');
	define( VPL_COMPONENT_NAME, __('Polls', 'bp_polls') );
	define( VPL_GROUP_MODULE, 'group');
	define( VPL_USER_MODULE, 'user');
	define( VPL_ALL_POLLS_PAGE, 'all_polls_page');
	define( VPL_ALL_POLLS_MODULE, 'all_polls');
	
	define( VPL_DATE_FORMATE, get_option('date_format'));
	define( VPL_TIME_FORMATE, get_option('time_format'));
	
	
	add_filter('bp_directory_pages', 'vpl_add_admin_page');
	
	//Define Constants that varias for users and groups
	include_once('components/defines_functions.php');
	define( VPL_CURRENT_MODULE, vpl_get_current_module() ); // user or group
	define( VPL_CURRENT_COMPONENT, vpl_get_current_component() );  // expect for 'polls'
	
	// Url to poll component ( in groups - groups/single/polls; in porfile members/username/polls/ )
	define( VPL_CURRENT_COMPONENT_URL, vpl_get_current_component_url() ); 
	
	
	// Load Polls view Class
	include_once('components/core/Poll_Extension_View.php');
	
	// Load style and scripts
	include_once('components/styles_and_scripts.php');
	add_action('wp_head','vpl_poll_print_js_translation');
	
	
	add_action('admin_menu','vpl_poll_menu');
		
	
	// Ajax functions
	include_once('components/ajax.php');
				
	//Add shortcode
	add_shortcode('bp-polls','vpl_poll_shortcode');
	
	//Add shortcode
	add_shortcode('bp-poll','vpl_poll_single_shortcode');
	
	// Add polls to user menu ( control panel also )
	include_once('components/register_user_polls.php');
	
	define( VPL_CURRENT_ACTION, vpl_get_current_action() ); // [ list, view, edit, new ]
	
	if( VPL_CURRENT_MODULE ) {
		
		//This function Looking for suitable submitted forms and launches 
		//appropriate processing functions from  'components/processing_POST.php'
		if( !empty($_POST) ) {
			include_once('components/processing_POST.php');
			vpl_processing_post();
		}
		
		if( VPL_CURRENT_MODULE != VPL_ALL_POLLS_MODULE ) {
			
			if( VPL_CURRENT_MODULE == VPL_GROUP_MODULE ) {	
				// Need register group extension when we in groups component
				include_once('components/register_group_polls.php');
				vpl_register_group_polls();
			}

			// Display Polls content
			if( VPL_CURRENT_COMPONENT ==  VPL_COMPONENT_SLUG ) {
				// Display polls functions
				include_once('components/display_polls.php');

				
			}
			
		} else {
			// Load Polls view Class
			add_filter('bp_located_template', 'vpl_load_template_filter', 10, 2);
			add_filter('wp_title', 'vpl_add_title');
			global $view;
			$view = new Poll_Extension_View( VPL_CURRENT_MODULE, VPL_CURRENT_ACTION , array() );
			bp_core_load_template('polls/all_polls');
		}
	}
	
}


function vpl_add_title($title){
	global $post;
	$title = $post->post_title.' '.$title;
	return  $title;
}
/**
 * Plugin Activation function
 */
function vpl_activation() {
	include_once('components/install.php');
	vpl_create_tables();
}

function vpl_poll_shortcode($atts){
	$view = new Poll_Extension_View( VPL_CURRENT_MODULE, VPL_CURRENT_ACTION , array());
	$view->avaible_polls();
}

function vpl_poll_single_shortcode($atts){
	$poll_id =  $atts['id'];
	
	if( $poll_id ) {
		$view = new Poll_Extension_View( 'shortcode', 'view' , array($poll_id));
		$view->set_group_id(0);
		$view->display();
	}
}


/**
 * Return permission of current user to modify polls 
 */
function vpl_is_user_can_modify_polls() {
	global $bp, $current_user;
	// current_user_can('administrator') );
	// $bp->is_item_admin );
	return $bp->is_item_admin;
}

function vpl_is_user_can_create_polls() {
	global $bp;
	if( VPL_GROUP_MODULE == VPL_CURRENT_MODULE ) {
		if( $bp->groups->current_group->is_member ) {
			return true;
		}
	}
	return false;
}

function vpl_add_admin_page($pages) {
	$pages[VPL_ALL_POLLS_MODULE] = VPL_COMPONENT_NAME;
	return $pages;
}


function vpl_load_template_filter($found_template, $templates) {
    if( VPL_CURRENT_MODULE ) { 
		foreach ((array) $templates as $template) {
            if (file_exists(STYLESHEETPATH . '/' . $template))
                $filtered_templates[] = STYLESHEETPATH . '/' . $template;
            else
                $filtered_templates[] = dirname(__FILE__) . '/templates/' . $template;
        }
		
        $found_template = $filtered_templates[0];
		return apply_filters('vpl_load_template_filter', $found_template);
    }else {
        return $found_template;
    }
}

function vpl_is_debug() {
	if( vpl_is_user_can_modify_polls() && VPL_DEBUG == true ) 
		return true;
	else 
		return false;
	
}

function vpl_js_date_format( $php_date_str = '') {
	$js_date_str = 'M d, yy';
	if( !$php_date_str ){
		$php_date_str = VPL_DATE_FORMATE;
	}
	
	$dates = array(
		'F j, Y' => 'MM d, yy',
		'Y/m/d' => 'yy/mm/dd',
		'm/d/Y' => 'mm/dd/yy',
		'd/m/Y' => 'dd/mm/yy',
		'd.m.Y' => 'dd.mm.yy'
	);
	
	if( isset($dates[$php_date_str])) {
		$js_date_str = $dates[$php_date_str];
	}
	
	return $js_date_str;
}

function vpl_js_time_format(){
	$js_time_str = 'hh:mm';
	return $js_time_str;
}

function vpl_poll_print_js_translation() {
	?>
	<script>
	// Translation function
	function __e(str){
		if( VPL_TRANS[str] ) {
			return VPL_TRANS[str];
		} else {
			return str;
		}
	}
	
	var VPL_TRANS = [];
	
	VPL_TRANS['Please enter the Poll name'] = '<?php _e('Please enter the Poll name','bp_polls')?>';
	VPL_TRANS['Start date can not be empty'] = '<?php _e('Start date can not be empty','bp_polls')?>';
	VPL_TRANS['End date can not be empty'] = '<?php _e('End date can not be empty','bp_polls')?>';
	VPL_TRANS['Start date must be less than the end date'] = '<?php _e('Start date must be less than the end date','bp_polls')?>';
	VPL_TRANS['Poll must have at least one question'] = '<?php _e('Poll must have at least one question','bp_polls')?>';
	VPL_TRANS['Missed question text for Question #'] = '<?php _e('Missed question text for Question #','bp_polls')?>';
	VPL_TRANS['Dating Poll must have at least 2 dates'] = '<?php _e('Dating Poll must have at least 2 dates','bp_polls')?>';
	VPL_TRANS['Question #'] = '<?php _e('Question #','bp_polls')?>';
	VPL_TRANS['must have at least 2 answers'] = '<?php _e('must have at least 2 answers','bp_polls')?>';
	VPL_TRANS['Missed start for date #'] = '<?php _e('Missed start for date #','bp_polls')?>';
	VPL_TRANS['Missed end for date #'] = '<?php _e('Missed end for date #','bp_polls')?>';
	VPL_TRANS['The startdate for answer'] = '<?php _e('The startdate for answer','bp_polls')?>';
	VPL_TRANS['have to be before the enddate'] = '<?php _e('have to be before the enddate','bp_polls')?>';
	VPL_TRANS['A start and an end date for answer'] = '<?php _e('A start and an end date for answer','bp_polls')?>';
	VPL_TRANS['have to be filled in'] = '<?php _e('have to be filled in','bp_polls')?>';
	VPL_TRANS['Missed answer text for Question #'] = '<?php _e('Missed answer text for Question #','bp_polls')?>';
	VPL_TRANS['Answer #'] = '<?php _e('Answer #','bp_polls')?>';
	VPL_TRANS['Are you sure want delete this answer?'] = '<?php _e('Are you sure want delete this answer?','bp_polls')?>';
	VPL_TRANS['Are you sure want delete this date?'] = '<?php _e('Are you sure want delete this date?','bp_polls')?>';
	VPL_TRANS['Are you sure want delete this question?'] = '<?php _e('Are you sure want delete this question?','bp_polls')?>';
	VPL_TRANS['Date Title'] = '<?php _e('Poll Name','bp_polls')?>';
	VPL_TRANS['Poll Name'] = '<?php _e('Poll Name','bp_polls')?>';
	VPL_TRANS['Total'] = '<?php _e('Total','bp_polls')?>';
	VPL_TRANS['votes'] = '<?php _e('votes','bp_polls')?>';
	VPL_TRANS['Thank you for the vote! It was successfully saved.'] = '<?php _e('Thank you for the vote! It was successfully saved.','bp_polls')?>';
	VPL_TRANS['Invites have been sent successfully'] = '<?php _e('Invites have been sent successfully','bp_polls')?>';
	VPL_TRANS['Are you sure want to proceed? This action will clear all votes statistic.'] = '<?php _e('Are you sure want to proceed? This action will clear all votes statistic.','bp_polls')?>';
	
	//timepicker
	VPL_TRANS['Choose Time'] = '<?php _e('Choose Time','bp_polls')?>';
	VPL_TRANS['Now'] = '<?php _e('Now','bp_polls')?>';
	VPL_TRANS['Done'] = '<?php _e('Done','bp_polls')?>';
	VPL_TRANS['Time'] = '<?php _e('Time','bp_polls')?>';
	VPL_TRANS['Hour'] = '<?php _e('Hour','bp_polls')?>';
	VPL_TRANS['Minute'] = '<?php _e('Minute','bp_polls')?>';
	
	
	</script>
	<?php
}


function vpl_get_status_name( $status ) {
	$translated = '';
	switch($status){
		case 'open':
			$translated = __('Open', 'bp_polls');
			break;
		case 'closed':
			$translated = __('Closed', 'bp_polls');
			break;
		case 'draft':
			$translated = __('Draft', 'bp_polls');
			break;
	}
	return $translated;
}

function vpl_poll_menu(){
	add_options_page('BP Polls', 'BP Polls', 'manage_options', 'vpl-bp-polls', 'vpl_menu_options');
}

function vpl_menu_options(){
	if( $_POST['save']){
		if( $_POST['invite_all'] == '1') {
			update_option('vpl_invite_all', 1);
		} else {
			update_option('vpl_invite_all', 0);
		}
	}
	?>
	<div class="wrap">
		<h2><?php _e('BP Polls Options', 'bp_polls')?></h2>
		<form action="" method="post">
			<label><input name="invite_all" type="checkbox" <?php checked(1, get_option('vpl_invite_all') ); ?> value="1" /> <?php _e('Enable inviting all users to poll.', 'bp_polls')?> </label><br/><br/>
			<input name="save" type="submit" value="Save" class="button-primary"/>
		</form>
	</div>
	<?php
}