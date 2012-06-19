<?php

add_action('bp_template_content', 'vpl_load_polls_view');

/**
 * Display content of polls page.
 */
function vpl_load_polls_view() {
	global $bp;
	
	if( VPL_CURRENT_COMPONENT == VPL_COMPONENT_SLUG ) {
		
		if( VPL_CURRENT_MODULE == 'user') {
			// First element of action_variables is ID
			$action_variables = $bp->action_variables;
			
			$view = new Poll_Extension_View( VPL_CURRENT_MODULE, VPL_CURRENT_ACTION , $action_variables);
			$view->display();
			
		} elseif( VPL_CURRENT_MODULE == 'group') {

			// Second element of action_variables is ID
			$action_variables = $bp->action_variables;
			array_shift($action_variables);
			
			$view = new Poll_Extension_View( VPL_CURRENT_MODULE, VPL_CURRENT_ACTION , $action_variables);
			$view->display();
			
		}
	}
}