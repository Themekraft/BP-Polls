<?php
global $vpl_group_categories,$vpl_group_tags;
?>

<?php if( vpl_is_user_can_modify_polls() ):?>
<div class="vpl-poll-actions">
	<a class="button" href="<?php echo VPL_CURRENT_COMPONENT_URL . 'taxonomy/new/'?>" /><?php _e('New Category', 'bp_polls')?></a>
</div>
<?php endif;?>
<h4><?php _e('Categories', 'bp_polls');?></h4>
<?php if(!empty($vpl_group_categories)):?>
	<ul class="vpl-categories-list">
		<?php foreach($vpl_group_categories as $cat):?>
			<li>
				<a href="<?php echo VPL_CURRENT_COMPONENT_URL . 'taxonomy/view/'.$cat->id ?>"><?php echo ucfirst( stripcslashes($cat->name) )?></a> (<?php echo $cat->count?>)
				<?php if( vpl_is_user_can_modify_polls() ):?>
					<div class="options">
						<a href="<?php echo VPL_CURRENT_COMPONENT_URL . 'taxonomy/edit/'.$cat->id ?>" ><?php _e('Edit', 'bp_polls')?></a> | <a class="delete-category" href="javascript:void(0)" ><?php _e('Delete', 'bp_polls')?></a>
						<form class="hidden" method="post" action="">
							<input type="hidden" name="taxonomy_id" value="<?php echo $cat->id?>" />
							<input type="hidden" name="delete_taxonomy" value="1" />
							<input type="hidden" name="count" value="<?php echo $cat->count?>" />
							<input type="hidden" name="nonce" value="<?php echo  wp_create_nonce  ('delete_taxonomy'); ?>" />
						</form>
					</div>
				<?php endif;?>
			</li>	
		<?php endforeach;?>
	</ul>
<?php else:?>
<?php endif;?>

<?php if( vpl_is_user_can_modify_polls() ):?>
	<div class="vpl-poll-actions">
		<a class="button" href="<?php echo VPL_CURRENT_COMPONENT_URL . 'taxonomy/new/?type=tag'?>" /><?php _e('New Tag', 'bp_polls')?></a>
	</div>
<?php endif;?>

<h4><?php _e('Tags', 'bp_polls');?></h4>
<?php if(!empty($vpl_group_tags)):?>
    <ul class="vpl-categories-list">
		<?php foreach($vpl_group_tags as $tag):?>
			<li>
				<a href="<?php echo VPL_CURRENT_COMPONENT_URL . 'taxonomy/view/'.$tag->id ?>"><?php echo ucfirst( stripcslashes($tag->name) )?></a> (<?php echo $tag->count?>)
				<?php if( vpl_is_user_can_modify_polls() ):?>
					<div class="options">
						<a href="<?php echo VPL_CURRENT_COMPONENT_URL . 'taxonomy/edit/'.$tag->id ?>" ><?php _e('Edit', 'bp_polls')?></a> | <a class="delete-tag" href="javascript:void(0)" ><?php _e('Delete', 'bp_polls')?></a>
						<form class="hidden" method="post" action="">
							<input type="hidden" name="taxonomy_id" value="<?php echo $tag->id?>" />
							<input type="hidden" name="delete_taxonomy" value="1" />
							<input type="hidden" name="count" value="<?php echo $tag->count?>" />
							<input type="hidden" name="nonce" value="<?php echo  wp_create_nonce  ('delete_taxonomy'); ?>" />
						</form>
					</div>
				<?php endif;?>
			</li>	
		<?php endforeach;?>
	</ul>
<?php else:?>

<?php endif;?>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.delete-category').click(function() {
		var text = '';
		if( jQuery(this).parent().find('[name=count]').val() > 0 )  {
			text = '<?php _e( '*WARNING*\nThis category has polls. If you delete the category - all polls loosing category information.\nAre you sure want delete this category?', 'bp_polls' ); ?>';
		}else {
			text = '<?php _e( 'Are you sure want delete this category?', 'bp_polls' ); ?>';
		}
		
		if ( confirm(text) ) {
			jQuery(this).parent().find('form').submit();
		}
	});
});
</script>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.delete-tag').click(function() {
		var text = '';
		if( jQuery(this).parent().find('[name=count]').val() > 0 )  {
			text = '<?php _e( '*WARNING*\nThis tag has polls. If you delete the category - all polls loosing tag information.\nAre you sure want delete this tag?', 'bp_polls' ); ?>';
		}else {
			text = '<?php _e( 'Are you sure want delete this tag?', 'bp_polls' ); ?>';
		}
		
		if ( confirm(text) ) {
			jQuery(this).parent().find('form').submit();
		}
	});
});
</script>

