<?php

/**
 * Register Group Extension for polls
 */
function vpl_register_group_polls() {
	global $bp;	
	//Show polls only for admins and for choosen user groups
	$show_polls = true;
	if( !vpl_is_user_can_modify_polls() ) {
		$who_can_see_polls = groups_get_groupmeta( $bp->groups->current_group->id, 'vpl_can_see_polls' );
		if( $who_can_see_polls == 'members' && ! $bp->groups->current_group->is_user_member) {
			$show_polls = false;
		}
	}
	
	if( $show_polls ) {
		//Load extension class and register it
		include_once( VPL_ROOT_PATH . 'components/core/Poll_Group_Extension.php');
		bp_register_group_extension( 'VPL_Poll_Group_Extension' );
	}
}