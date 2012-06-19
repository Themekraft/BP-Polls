<?php
global $vpl_group_id, $vpl_user_id;

if($_GET['type'] == 'tag'){
	$type = 'tag';
}else {
	$type = 'category';
}
?>



<h3><?php _e('New '. ucfirst($type) , 'bp_polls')?></h3>
<form action="" method="post" class="standard-form">
	<input type="text" name="name"/><br/>
	<input type="hidden" name="type" value="<?php echo $type?>"/><br/>
	<input type="submit" value="<?php _e('Add ' . ucfirst($type) , 'bp_polls')?>" name="add_taxonomy" />
	<input type="hidden" value="<?php echo $vpl_group_id;?>" name="group_id" />
	<input type="hidden" value="<?php echo $vpl_user_id;?>" name="user_id" />
</form>