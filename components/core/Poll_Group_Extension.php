<?php
/*
 * Class Poll_Extension
 * Extend the groups adding Polls feature
 */

class VPL_Poll_Group_Extension extends BP_Group_Extension {   
	
	//Disable create step
	var $enable_create_step = false;
	
	public function __construct() {
		$this->name = VPL_COMPONENT_NAME;
        $this->slug = VPL_COMPONENT_SLUG;
 
        $this->create_step_position = 21;
        $this->nav_item_position = 31;
	}
 
	function widget_display() { ?>
        <div class="info-group">
            <h4><?php echo esc_attr( $this->name ) ?></h4>
            
        </div>
        <?php
    }
	
    function create_screen() {
        if ( !bp_is_group_creation_step( $this->slug ) )
            return false;
        ?>
 
        <p>You can adjust your polls settings later</p>
 
        <?php
        wp_nonce_field( 'groups_create_save_' . $this->slug );
    }
 
    function create_screen_save() {
        global $bp;
 
        check_admin_referer( 'groups_create_save_' . $this->slug );
 
        /* Save any details submitted here */
        groups_update_groupmeta( $bp->groups->new_group_id, 'my_meta_name', 'value' );
    }
 
    function edit_screen() {
		global $bp;
        if ( !bp_is_group_admin_screen( $this->slug ) )
            return false; 
		
		$can_see_polls = groups_get_groupmeta( $bp->groups->current_group->id, 'vpl_can_see_polls' );
		
		?>
 
        <h2><?php echo esc_attr( $this->name ) ?></h2>
		
		<label for="polls-name"><?php _e('Who can see group polls?')?></label>
		<input <?php if( 'all' == $can_see_polls ) echo 'checked="checked"'?> type="radio" name="can_see_polls" value="all"  aria-required="true"/> <?php _e('All users','bp_polls');?><br/>
		<input <?php if( 'members' == $can_see_polls ) echo 'checked="checked"'?>  type="radio" name="can_see_polls" value="members" aria-required="true"/> <?php _e('Group Members','bp_polls');?><br/>
		
		<br/>
		<p>
			<input type="submit" name="save" value="<?php _e('Save','bp_polls');?>" />
		</p>
		
        <?php
        wp_nonce_field( 'groups_edit_save_' . $this->slug );
    }
 
    function edit_screen_save() {
        global $bp;
 
        if ( !isset( $_POST['save'] ) )
            return false;
 
        check_admin_referer( 'groups_edit_save_' . $this->slug );
 
		$can_see_polls = esc_html( $_POST['can_see_polls'] );
		groups_update_groupmeta( $bp->groups->current_group->id, 'vpl_can_see_polls', $can_see_polls );
		
		$success = true;
        if ( !$success )
            bp_core_add_message( __( 'There was an error saving, please try again', 'buddypress' ), 'error' );
        else
            bp_core_add_message( __( 'Settings saved successfully ', 'buddypress' ) );
 
        bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
    }
 
    function display() {
		 
    }
}