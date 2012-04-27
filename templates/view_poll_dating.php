<?php
global $vpl_poll, $bp, $current_user;


?>

<div class="vpl-poll-actions">
	<?php $this->show_invites_button($vpl_poll);?>
	<?php if( vpl_is_user_can_modify_polls() ):?>
		<a class="button" href="<?php echo $vpl_poll->edit_link ?>" /><?php _e('Edit Poll', 'bp_polls')?></a>
	<?php endif;?>
</div>

<div class="vpl-view-poll" rel="poll_<?php echo $vpl_poll->id?>">
	
	<h3><?php echo stripcslashes($vpl_poll->name) ?></h3>
	<input type="hidden" class="poll_link" value="<?php echo $vpl_poll->permalink ?>" />
	<input type="hidden" class="show_results" value="<?php echo $vpl_poll->show_results ?>" />
	<input type="hidden" class="is_admin" value="<?php echo vpl_is_user_can_modify_polls() ?>" />
	
	<?php $this->show_invites_list($vpl_poll);?>
	
	<?php if($vpl_poll->status == 'closed'):?>
		<p class="vpl-message warning"><?php _e('This poll is expired', 'bp_polls')?></p>
	<?php else:?>
		<strong><?php _e('Start', 'bp_polls')?>: </strong> <?php echo date( VPL_DATE_FORMATE, $vpl_poll->start) . ' ' . date( VPL_TIME_FORMATE , $vpl_poll->start) ?><br/>
		<?php if($vpl_poll->expiry != 0):?>
			<strong><?php _e('Expire', 'bp_polls')?>: </strong> <?php echo date(VPL_DATE_FORMATE, $vpl_poll->expiry). ' ' . date(VPL_TIME_FORMATE, $vpl_poll->expiry) ?><br/>
		<?php endif; ?>
	<?php endif;?>
	
	<!-- Questions List -->
	<?php foreach($vpl_poll->questions as $question): ?>
	<hr/>
	<div class="vpl-poll-question" rel="question_<?php echo $question->id ?>">
		<h4><?php echo stripcslashes( $question->question )?></h4>
		<!-- Show answers  -->
		<?php if( ( $vpl_poll->show_results == '1' || $question->current_user_vote == 0 )  || vpl_is_user_can_modify_polls() ): ?>
		
		
			<?php  $answers_ids = array();?>
			<div class="vpl-dating-poll-statistic">
				<div class="dating-poll-dates">
					<div class="spacer"></div>
					<?php foreach( $question->answers as $answer ):?>
						<div class="date">
							<?php $start_end = explode(':',$answer->answer)?>
							<span class="days"><?php echo date(VPL_DATE_FORMATE, $start_end['0'])?></span> <span class="time"><?php echo date(VPL_TIME_FORMATE, $start_end['0'])?></span><br/>
							<span class="days"><?php echo date(VPL_DATE_FORMATE, $start_end['1'])?></span> <span class="time"><?php echo date(VPL_TIME_FORMATE, $start_end['1'])?></span><br/>
						</div>
						<?php $answers_ids[] = $answer->id ?>
					<?php endforeach;?>
				</div>
				<div class="dating-poll-users">

					<?php foreach($question->statistic_by_users as $user):?>
						<div class="user">	
							<div class="user_login"><?php echo $user['user']->user_login ?></div>
							<?php foreach($answers_ids as $answer_id):?>
								<?php if( in_array($answer_id, $user['answers']) ):?>
									<div class="yes"></div>
								<?php else:?>
									<div class="no"></div>
								<?php endif;?>
							<?php endforeach;?>
						</div>
					<?php endforeach;?>
					<!--If poll not closed and user not voted show voting feature -->
					<?php if( $question->current_user_vote == 0  && $vpl_poll->status != 'closed'):?>
						<div class="user current_user">	
							<div class="user_login"><?php echo $bp->loggedin_user->fullname ?></div>
							<?php foreach($answers_ids as $answer_id):?>
								<div class="checkbox"><input type="checkbox" value="<?php echo $answer_id;?>" /></div>
							<?php endforeach;?>
							<div>
								<input type="button" class="dating_vote_button" value="<?php _e('Vote', 'bp_polls')?>" />
							</div>
						</div>
					<?php endif;?>

					<div class="votes">
						<?php foreach( $question->answers as $answer ):?>
							<div class="vote_count" rel="<?php echo $answer->id;?>" ><?php echo $answer->votes; ?></div>
						<?php endforeach;?>
					</div>

				</div>
				<div style="clear:both"></div>
			</div>
			<?php if( $question->current_user_vote != 0 ):?>
				<p class="already-vote"><?php _e('You have already voted', 'bp_polls')?> </p>
			<?php endif;?>
				
		<?php else:?>
			<p class="already-vote"><?php _e('You have already voted', 'bp_polls')?> </p>
		<?php endif;?>
		
		
				
	</div>
	<?php endforeach;?>
	
</div>
