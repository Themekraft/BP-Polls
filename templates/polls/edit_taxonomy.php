<?php
global $vpl_group_id,$vpl_user_id, $vpl_taxonomy;

?>
<h3><?php _e('Edit taxonomy', 'bp_polls')?>: <?php echo ucfirst( stripslashes( $vpl_taxonomy->name ) )?></h3>
<form action="" method="post" class="standard-form">
	<input type="text" name="taxonomy_name" value="<?php echo ucfirst( stripslashes( $vpl_taxonomy->name ) )?>"/><br/>
	<input type="submit" value="<?php _e('Save taxonomy', 'bp_polls')?>" name="save_taxonomy" />
	<input type="hidden" value="<?php echo $vpl_group_id;?>" name="group_id" />
	<input type="hidden" value="<?php echo $vpl_user_id;?>" name="user_id" />
	<input type="hidden" value="<?php echo (int) $vpl_taxonomy->id?>" name="taxonomy_id" />
</form>