<?php
global $bp;
$action_variables = $bp->action_variables;
$view = new Poll_Extension_View( VPL_CURRENT_MODULE, VPL_CURRENT_ACTION , $action_variables);

if( $view->has_polls() ):
	$default_subnav_slug = 'list';
	$polls_list_pos = 10;
	$new_poll_pos = 20;
else:
	$default_subnav_slug = 'new';
	$polls_list_pos = 20;
	$new_poll_pos = 10;
endif;

//Add Top level menu in user profile
bp_core_new_nav_item(
	array(
		'name' => __( VPL_COMPONENT_NAME , 'buddypress'),
		'slug' => VPL_COMPONENT_SLUG,
		'position' => 90,
		'show_for_displayed_user' => true,
		'screen_function' => 'vpl_show_user_polls',
		'default_subnav_slug' => $default_subnav_slug
	));


/* Add subnav items  For Displayed User */

// Define parent Url
if( VPL_CURRENT_MODULE == VPL_USER_MODULE ) {
	$parent_url = trailingslashit( $bp->displayed_user->domain . VPL_COMPONENT_SLUG );
}else{
	$parent_url = trailingslashit( $bp->loggedin_user->domain . VPL_COMPONENT_SLUG );
}



if( bp_is_my_profile() ) {
	$my_polls_name = __('My Polls','bp_polls');
} else {
	$my_polls_name = __('User Polls','bp_polls');
}

// Add "List"
$polls_list = array(
	'name'            => $my_polls_name,
	'slug'            => 'list',
	'parent_url'      => $parent_url,
	'parent_slug'     => VPL_COMPONENT_SLUG,
	'screen_function' => 'vpl_show_user_polls',
	'position'        => $polls_list_pos,
	'user_has_access' => 'all'
);
bp_core_new_subnav_item($polls_list);


// Add "New"
$polls_new = array(
	'name'            => __('New Poll','bp_polls'),
	'slug'            => 'new',
	'parent_url'      => $parent_url,
	'parent_slug'     => VPL_COMPONENT_SLUG,
	'screen_function' => 'vpl_show_user_polls',
	'position'        => $new_poll_pos,
	'user_has_access' => ( bp_is_my_profile() || current_user_can('administrator') )
);
bp_core_new_subnav_item($polls_new);

// Add "Taxonomy"
$polls_tax = array(
	'name'            => __('Taxonomy','bp_polls'),
	'slug'            => 'taxonomy',
	'parent_url'      => $parent_url,
	'parent_slug'     => VPL_COMPONENT_SLUG,
	'screen_function' => 'vpl_show_user_polls',
	'position'        => 20,
	'user_has_access' => ( bp_is_my_profile() || current_user_can('administrator') )
);
bp_core_new_subnav_item($polls_tax);





if( VPL_CURRENT_MODULE == VPL_USER_MODULE ) {
	
	// Add "View" 
	$polls_view = array(
		'name'            => __('View Poll','bp_polls'),
		'slug'            => 'view',
		'parent_url'      => $parent_url,
		'parent_slug'     => VPL_COMPONENT_SLUG,
		'screen_function' => 'vpl_show_user_polls',
		'position'        => 50,
		'user_has_access' => 'all',
		'item_css_id' => 'user_poll_view' //( will hide his button with CSS)
	);
	bp_core_new_subnav_item($polls_view);

	// Add "Edit" (hidden)
	$polls_edit = array(
		'name'            => __('Edit Poll','bp_polls'),
		'slug'            => 'edit',
		'parent_url'      => $parent_url,
		'parent_slug'     => VPL_COMPONENT_SLUG,
		'screen_function' => 'vpl_show_user_polls',
		'position'        => 50,
		'user_has_access' => ( bp_is_my_profile() || current_user_can('administrator') ),
		'item_css_id' => 'user_poll_edit' ////( will hide his button with CSS)
	);
	bp_core_new_subnav_item($polls_edit);

}



function vpl_show_user_polls() {
	bp_core_load_template( 'members/single/plugins' );
}
