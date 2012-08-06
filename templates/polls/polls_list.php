<?php
global $vpl_polls_list, $bp;
$counter = 1;

?>

<div class="vpl-polls-list">
<?php if( !empty($vpl_polls_list)): ?>
	<ul id="poll_list">
	<?php foreach($vpl_polls_list as $poll): ?>	
		<?php //var_dump($poll);die;?>
		<?php if( 'draft' != $poll->status || vpl_is_user_can_modify_polls() == 1 || $this->user_is_author($poll) ):?>
		
			<li class="poll-list-item <?php if( vpl_is_user_can_modify_polls() || $this->user_is_author($poll) ) { echo $poll->status; if($poll->hidden == 1) echo ' hidden_poll'; } ?>" >
				<?php if( vpl_is_user_can_modify_polls() == 1 || $this->user_is_author($poll) ):?>
					<form class="vpl-poll-actions" method="post" action="">
						<a class="button" href="<?php echo $poll->edit_link ?>" /><?php _e('Edit' ,'bp_polls')?></a>
						<a class="button delete-poll" href="javascript:void(0)" /><?php _e('Delete Poll', 'bp_polls')?></a>
						<input type="hidden" class="confirm_text" value="<?php echo sprintf(__("*WARNING*\nOnce deleted this poll cant be restored!\nAre you sure you want to delete th poll '%s'?\n", 'bp_polls') , $poll->name ); ?>" />
						<input type="hidden" name="poll_id" value="<?php echo $poll->id?>" />
						<input type="hidden" name="delete_poll" value="<?php _e('Delete', 'bp_polls')?>" />
					</form>
				<?php endif;?>
				<h6>
					<a href="<?php echo $poll->permalink?>" ><?php echo stripcslashes( $poll->name )?></a>
				</h6>
				<div class="poll-meta">
					
					<?php if( vpl_is_user_can_modify_polls() == 1 ):?>
						<?php if($poll->hidden == 1):?>
							<span><?php _e('This is hidden poll', 'bp_polls')?></span><br/>
						<?php endif;?>
						<span><?php _e('Status', 'bp_polls')?>: </span>
						<?php echo vpl_get_status_name( $poll->status ) ?> 
						<br/>
					<?php endif;?>
					
					<span><?php _e('Start', 'bp_polls')?>: </span>
					<?php echo date_i18n( VPL_DATE_FORMATE ,$poll->start) . ' ' . date_i18n( VPL_TIME_FORMATE ,$poll->start) ?><br/>
					
					<?php if($poll->expiry != 0):?>
						<span><?php _e('Expire', 'bp_polls')?>: </span>
						<?php echo date_i18n( VPL_DATE_FORMATE ,$poll->expiry) . ' ' . date_i18n( VPL_TIME_FORMATE ,$poll->expiry) ?><br/>
					<?php endif;?>
					
					<span><?php _e('Categories', 'bp_polls')?>: </span>
					<?php
						$i = 0;	
						foreach( (array)$poll->categories as $c) {
							if( $i != 0 ) {
								echo ', ';
							}
							echo '<a href="taxonomy/view/'.$c->id.'">' . ucfirst($c->name) . '</a>';
							$i++;
						} 
					?>
					<br/>
					
					<span><?php _e('Tags', 'bp_polls')?>: </span>
					<?php
						$i = 0;	
						foreach( (array)$poll->tags as $t) {
							if( $i != 0 ) {
								echo ', ';
							}
							echo '<a href="taxonomy/view/'.$t->id.'">' . ucfirst($t->name) . '</a>';
							$i++;
						} 
					?>
					<br/>
						
					<?php if($poll->questions && $poll->poll_type == 'question'):?>
						<span><?php _e('Questions', 'bp_polls')?>: </span> 
						<ul class="question-list">
						<?php foreach($poll->questions as $q): ?>	
							<li><?php echo stripcslashes( $q->question )?></li>
						<?php endforeach;?>
						</ul>
					<?php endif;?>
				</div>
			</li>
			<?php $counter++;?>
			
		<?php endif; ?>
	<?php endforeach; ?>
	</ul>
<?php else:?>
	<p><?php _e('There are no polls to show', 'bp_polls')?></p>
<?php endif;?>
</div>
<script>
jQuery(document).ready(function(){
	jQuery('.delete-poll').click(function(){
		if( confirm( jQuery(this).parent().find('.confirm_text').val() ) ){
			jQuery(this).parent().submit();
		}
		
	})
})
</script>