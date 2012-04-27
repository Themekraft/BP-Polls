<?php


/*
 * Return current module ( Group , User or void string )
 */
function vpl_get_current_module() {
	global $bp;
	$current_module = '';
	
	if( $bp->current_component == bp_get_groups_slug() ) {
		$current_module = VPL_GROUP_MODULE;
	} elseif( $bp->displayed_user->id ) {
		$current_module = VPL_USER_MODULE;
	}elseif( $bp->current_component == VPL_ALL_POLLS_MODULE ) {
		$current_module = VPL_ALL_POLLS_MODULE;
	}
	
	return $current_module;
}

/*
 * Return current component ( For user it is Friends, Profile ,Polls etc ; For groups: Members, Polls etc )
 * Actually we l;ooking for Poll component.
 * We doing this to avoid including some not needed processing functions 
 * ( POST processing need only under Poll component)
 */
function vpl_get_current_component() {
	global $bp;
	$current_component = '';
	
	if ( VPL_USER_MODULE == VPL_CURRENT_MODULE ) {
		$current_component = $bp->current_component;
	} elseif( VPL_GROUP_MODULE == VPL_CURRENT_MODULE ) {
		$current_component = $bp->current_action;
	}
	
	return $current_component;
}

/*
 * Return current action ( view, edit ,list, new )
 */
function vpl_get_current_action() {
	global $bp;
	$action = '';
	
	if ( VPL_USER_MODULE == VPL_CURRENT_MODULE ) {
		$action = $bp->current_action;
	} elseif( VPL_GROUP_MODULE == VPL_CURRENT_MODULE ) {
		if( $bp->action_variables[0] ) {
			$action = $bp->action_variables[0];
		}
	}
	
	if( ! $action ) $action = 'list';
	
	return $action;
}



function vpl_get_current_component_url() {
	global $bp;
	$polls_url = '';
	
	if ( VPL_CURRENT_MODULE == VPL_USER_MODULE) {
		$polls_url = trailingslashit( $bp->displayed_user->domain . VPL_COMPONENT_SLUG );
		
	} elseif( VPL_CURRENT_MODULE == VPL_GROUP_MODULE) {
		$polls_url = site_url( bp_get_groups_slug() .'/'. $bp->groups->current_group->slug . '/' . VPL_COMPONENT_SLUG . '/');
		
	}
	return $polls_url;
}