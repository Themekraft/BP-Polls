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
	<input type="hidden" class="poll_id" value="<?php echo $vpl_poll->id ?>" />
	<input type="hidden" class="show_results" value="<?php echo $vpl_poll->show_results ?>" />
	<input type="hidden" class="is_admin" value="<?php echo vpl_is_user_can_modify_polls() ?>" />
	
	<?php $this->show_invites_list($vpl_poll);?>
	
	<?php if($vpl_poll->status == 'closed'):?>
		<p class="vpl-message warning"><?php _e('This poll is expired', 'bp_polls')?></p>
	<?php else:?>
		<strong><?php _e('Start', 'bp_polls')?>: </strong> <?php echo date(VPL_DATE_FORMATE,$vpl_poll->start) . ' ' . date(VPL_TIME_FORMATE,$vpl_poll->start) ?><br/>
		<?php if($vpl_poll->expiry != 0):?>
			<strong><?php _e('Expire', 'bp_polls')?>: </strong> <?php echo date( VPL_DATE_FORMATE ,$vpl_poll->expiry). ' ' . date( VPL_TIME_FORMATE ,$vpl_poll->expiry) ?><br/>
		<?php endif; ?>
	<?php endif;?>
	
	<!-- Questions List -->
	<?php foreach($vpl_poll->questions as $question): ?>
	<hr/>
	<div class="vpl-poll-question" rel="question_<?php echo $question->id ?>">
		<h4><?php echo stripcslashes( $question->question )?></h4>
		
		<!-- If already votes ot poll status closed show Statistic -->
		<?php if( $question->current_user_vote != 0  || $vpl_poll->status == 'closed'):?>

			<?php if( $vpl_poll->show_results == '1' || vpl_is_user_can_modify_polls() ):?>
		
				<div class="vpl-poll-statistic" >
					<?php foreach($question->statistic['answers'] as $answer): ?>
						<div class="item" >
							<div class="title"><?php echo stripcslashes($answer->answer).' '.$answer->percent.'% ('.$answer->votes.')'; ?></div>
							<div class="progress-bar" style="width:<?php echo $answer->percent?>%" ></div>
						</div>
					<?php endforeach;?>
					<p class="total">Total votes: <?php echo $question->statistic['total']?></p>
				</div>
		
			<?php else:?>
		
				<?php if( $vpl_poll->status == 'closed'):?>
					<p class="already-vote"><?php _e("You don't have enough rights to see the vote's results.", 'bp_polls')?> </p>
				<?php else:?>
					<p class="already-vote"><?php _e('You have already voted', 'bp_polls')?> </p>
				<?php endif;?>
					
			<?php endif;?>
			
			
				

		<?php else:?>
			<!-- Show answers  -->
			<?php if($question->controls_type == 'checkbox'):?>

				<ul class="vpl-poll-answers" rel="type_checkbox">
				<!-- For checkboxes answers-->
				<?php foreach($question->answers as $a): ?>
					<li>
						<input type="checkbox" name="answer_for_question_<?php echo $question->id ?>" value="<?php echo $a->id?>" /> <?php echo stripcslashes($a->answer) ?>
					</li>
				<?php endforeach; ?>
				</ul>
				<p class="maximum-options"><?php _e('Maximum', 'bp_polls')?> <?php echo $question->options_limit ?> <?php _e('options allowed', 'bp_polls')?></p>
				<input type="hidden" class="options_limit" value="<?php echo $question->options_limit ?>" />

			<?php else:?>

				<!-- For radio buttons answers-->
				<ul class="vpl-poll-answers" rel="type_radio">
				<?php foreach($question->answers as $a): ?>
					<li>
						<input type="radio" name="answer_for_question_<?php echo $question->id ?>"  value="<?php echo $a->id?>" /> <?php echo stripcslashes($a->answer)?>
					</li>
				<?php endforeach; ?>
				</ul>

			<?php endif;?>

			<input type="button" class="vote_button" value="<?php _e('Vote', 'bp_polls')?>" />

		<?php endif;?>

	</div>
	<?php endforeach;?>
	
</div>
