<?php
/*
 * Edit Poll Form Template
 */
global $vpl_poll, $vpl_categories, $vpl_user_id, $vpl_group_id, $vpl_top_categories, $vpl_middle_categories, $vpl_other_categories,$vpl_tags;

?>
<script>

var JS_DATE_FORMAT = '<?php echo vpl_js_date_format();?>';
var JS_TIME_FORMAT = '<?php echo vpl_js_time_format();?>';

var group_tags = [""<?php foreach($vpl_tags as $t){echo ',"'.$t->name.'"';}?>];
jQuery(document).ready(function(){
	jQuery('.tag_field').autocomplete({
		source: group_tags
	});
})

</script>
<div class="vpl-poll-actions">
	<a class="button" href="<?php echo $vpl_poll->permalink ?>" onclick="return confirm('Continue without saving?') "/><?php _e('View Poll', "bp_polls")?></a>
</div>

<h3><?php _e('Edit Poll', "bp_polls")?>: <?php echo stripcslashes( $vpl_poll->name )?></h3>
<form class="standard-form new-poll-form" action="" class="" method="post">
	<div class="hidden">
		<input type="radio" name="poll_type" value="question" <?php if($vpl_poll->poll_type == 'question') echo 'checked="checked"';?>/>
		<input type="radio" name="poll_type" value="dating" <?php if($vpl_poll->poll_type == 'dating') echo 'checked="checked"';?>/>
	</div>
		
	<label><span class="poll_name_title"><?php _e('Poll Name', 'bp_polls')?></span> <span class="req">*</span></label>
	<input type="text" name="poll_name" value="<?php echo stripcslashes( $vpl_poll->name) ?>" />
	
	<label><?php _e('Categories', 'bp_polls')?></label>
	<input type="text" value="" class="category_field"/> <input type="button" class="add_category" value="<?php _e('Add Category', 'bp_polls')?>"/>
	<div class="categories">
		<?php if( !empty( $vpl_top_categories ) ):?>
			<?php foreach($vpl_top_categories as $cat):?>
				<div class="cat">
					<label><input type="checkbox" <?php /*if(in_array($cat->id, $vpl_poll->cats))*/ echo 'checked="checked"'?> <?php ?> value="1" name="cats[<?php echo $cat->id ?>]" /><span class="name"><?php echo ucfirst($cat->name)?></span></label>
				</div>
			<?php endforeach;?>
		<?php endif;?>
		
		<?php if( !empty( $vpl_middle_categories ) ):?>
			<?php foreach($vpl_middle_categories as $cat):?>
				<div class="cat">
					<label><input type="checkbox" <?php ?> value="1" name="cats[<?php echo $cat->id ?>]" /><span class="name"><?php echo ($cat->name)?></span></label>
				</div>
			<?php endforeach;?>
		<?php endif;?>
		
		<?php if( !empty( $vpl_other_categories ) ):?>
			<input type="button" class="show_all_cats" value="<?php _e('Show All Categories','bp_polls')?>"/>
			<?php foreach($vpl_other_categories as $cat):?>
				<div class="cat hidden">
					<label><input type="checkbox" value="1" name="cats[<?php echo $cat->id ?>]" /><span class="name"><?php echo ($cat->name)?></span></label>
				</div>
			<?php endforeach;?>
		<?php endif;?>
		
	</div>
	<div class="hidden cat_prototype">
		<div class="cat">
			<label><input type="checkbox" checked="checked" name="CATNAME" value="1" /><span class="name">CAT_NAME</span></label>
		</div>
	</div>

	<label><?php _e('Tags', 'bp_polls')?></label>
	<div class="hidden tag_prototype">
		<span class="tag">
			<span class="tag_name">TAGNAME</span>
			<span class="delete_tag" title="Delete tag">&nbsp;</span>
			<input type="hidden" class="tag_id" value="0"/>
		</span>
	</div>
	<input type="text" value="" class="tag_field"/> <input type="button" class="add_tag" value="<?php _e('Add Tag', 'bp_polls')?>"/>
	<div class="tags">
		<?php if( !empty($vpl_poll->tags) ):?>
			<?php foreach($vpl_poll->tags as $tag):?>
				<span class="tag">
					<span class="tag_name"><?php echo stripcslashes($tag->name)?></span>
					<span class="delete_tag" title="Delete tag">&nbsp;</span>
					<input type="hidden" class="tag_id" value="<?php echo $tag->id?>"/>
				</span>
			<?php endforeach;?>
		<?php endif; ?>
	</div>
	<input type="hidden" class="tags_values" name="tags" value="<?php echo $vpl_poll->tags_values?>"/>

	<div class="help_text">
		<?php _e('This category should be so broad as to expect more polls to be part of that category. Ideally you chose just one category for every poll, but it is possible to chose multiple categories. Categories are descriptive elements. Tags are more associative elements. You can use them to further refine the description of your poll. Just write down 3-5 words that come to your mind when you think about this poll. Tags should not include categories.', 'bp_polls');?>		
	</div>
	
	<div class="start_date_wrap">
		<label><?php _e('Poll Start Date', 'bp_polls')?>  <span class="req">*</span></label>
		<input type="text" name="poll_start_date" class="date_field" value="<?php echo  date(VPL_DATE_FORMATE.' @ '.VPL_TIME_FORMATE, $vpl_poll->start);  ?>"/> 
		<input type="hidden" name="poll_start" value="" />
	</div>
		
	<div class="end_date_wrap">
		<label><?php _e('Poll End Date', 'bp_polls')?>  </label>
		<input type="text" name="poll_end_date" class="date_field" value="<?php if($vpl_poll->expiry != 0) echo date( VPL_DATE_FORMATE.' @ '.VPL_TIME_FORMATE, $vpl_poll->expiry); else echo '';?>" /> 
		<input type="hidden" name="poll_end" value="" />
	</div>
	
	<label><?php _e('Activate Poll', 'bp_polls')?></label>
	<input type="checkbox" name="poll_active" class="time_field" <?php if( $vpl_poll->active ) echo 'checked="checked"' ?> /> <?php _e('Make this poll active', 'bp_polls')?>
	
	<label><?php _e('Who can see this poll', 'bp_polls')?></label>
	
	<?php if( get_option('vpl_invite_all') == '1'):?>
		<input type="radio" name="poll_restriction" value="all" <?php if($vpl_poll->restriction == 'all') echo 'checked="checked"';?>  /> <span><?php _e('All users', 'bp_polls')?></span><br/>
		<input type="radio" name="poll_restriction" value="auth" <?php if($vpl_poll->restriction == 'auth') echo 'checked="checked"';?> /> <?php _e('Autorized users', 'bp_polls')?><br/>
	<?php else:?>
		<!-- <input type="radio" name="poll_restriction" value="auth" <?php if($vpl_poll->restriction == 'auth' ||$vpl_poll->restriction == 'all' ) echo 'checked="checked"';?> /> <?php _e('Autorized users', 'bp_polls')?><br/>-->
		<input type="radio" name="poll_restriction" value="auth" <?php if($vpl_poll->restriction == 'auth' ||$vpl_poll->restriction == 'all' ) echo 'checked="checked"';?> /> <?php _e('All users', 'bp_polls')?><br/>
	<?php endif;?>
	
	<input type="radio" name="poll_restriction" value="invited" <?php if($vpl_poll->restriction == 'invited') echo 'checked="checked"';?> /> <?php _e('Invited users', 'bp_polls')?><br/>
	<?php if( VPL_CURRENT_MODULE == 'group'): ?>
	<input type="radio" name="poll_restriction" value="friend" <?php if($vpl_poll->restriction == 'friend') echo 'checked="checked"';?> /> <?php _e('Group Members', 'bp_polls')?><br/>
	<?php else: ?>
	<input type="radio" name="poll_restriction" value="friend" <?php if($vpl_poll->restriction == 'friend') echo 'checked="checked"';?> /> <?php _e('Friends', 'bp_polls')?><br/>
	<?php endif; ?>
	
	<label><?php _e('Show Poll results to users', 'bp_polls')?></label>
	<input type="radio" name="show_results" value="1" <?php if($vpl_poll->show_results == 1) echo 'checked="checked"';?>/> <?php _e('Yes', 'bp_polls')?><br/>
	<input type="radio" name="show_results" value="0" <?php if($vpl_poll->show_results == 0) echo 'checked="checked"';?>/> <?php _e('No', 'bp_polls')?><br/>
	
	<div class="vpl-message error hidden form-errors poll_only"></div>
	<p class="center"><input type="submit" onclick="setSubmitType('poll_only')" name="edit_poll_only" value="<?php _e('Save only poll data', 'bp_polls')?>"/></p>
	<div class="vpl-message warning"><?php _e('This button will save only poll  Start and End date, Name and activity status. Questions and answers will NOT be saved. And votes statistic will NOT be cleared', 'bp_polls')?></div>
	<hr/>
	<div class="poll_questions">		
		<?php if( !empty( $vpl_poll->questions ) ): ?>
		
			<?php $question_number = 1; ?>
			<?php foreach ( $vpl_poll->questions as $question ):?>
				<div class="poll-question-item" rel="question_item_<?php echo $question_number?>" >
					<input type="button" class="remove_question" value="<?php _e('Remove Question', 'bp_polls')?>"/>
					<h4><?php _e('Question', 'bp_polls')?></h4>
					<label><?php _e('Your question', 'bp_polls')?></label>
					<input type="text" class="q_name" name="questions[<?php echo $question_number?>][name]"  value="<?php echo stripcslashes($question->question) ?>" />

					<label><?php _e('Answer Type', 'bp_polls')?></label>
					<select name="questions[<?php echo $question_number?>][type]" class="control_type">
						<option value="radio" <?php if( 'radio' == $question->controls_type ) echo 'selected="selected"'?> ><?php _e('One Answer', 'bp_polls')?></option>
						<option value="checkbox" <?php if( 'checkbox' == $question->controls_type ) echo 'selected="selected"'?>><?php _e('Multiple Answers', 'bp_polls')?></option>
					</select>
					
					<div class="answers_limit <?php if('radio' == $question->controls_type) echo 'hidden';?>">
						<label><?php _e('Limit number of answers', 'bp_polls')?></label>
						<input type="text" name="questions[<?php echo $question_number?>][limit]" class="number" value="<?php echo $question->options_limit ?>"/>
					</div>
					<label class="answers-title">
						<span class="question"><?php _e('Answers', 'bp_polls')?></span>
						<span class="date"><?php _e('Dates', 'bp_polls')?></span>
					</label>
					<div class="question-answers-wrap">
						<div class="question-answers">
							<?php if( !empty( $question->answers ) ):?>
							
								<?php foreach( $question->answers as $answer):?>
									<div class="question-answer-item" >
										<input type="text"  name="questions[<?php echo $question_number?>][answers][]" class="answer-text" value="<?php echo stripcslashes($answer->answer) ?>" />
										<?php if($vpl_poll->poll_type == 'dating'):?>
											<?php $start_end = explode(':',$answer->answer)?>
											<div class="answer-date"/>
												<div>
													<label><?php _e('Start', 'bp_polls')?></label>
													<input type="text" value="<?php echo date(VPL_DATE_FORMATE.' @ '.VPL_TIME_FORMATE, $start_end[0]) ?>" class="start date_field"/>
												</div>
												<div>
													<label><?php _e('End', 'bp_polls')?></label>
													<input type="text" value="<?php echo date(VPL_DATE_FORMATE.' @ '.VPL_TIME_FORMATE, $start_end[1]) ?>" class="end date_field"/>
												</div>
												<div>
													<label>&nbsp;</label>
													<input type="button" class="remove-answer" value="<?php _e('Remove', 'bp_polls')?>" />
												</div>
											</div>
										<?php endif;?>
										<input type="button" class="remove-answer question" value="<?php _e('Remove', 'bp_polls')?>" />
									</div>
								<?php endforeach;?>
							
							<?php endif;?>
						</div>
						<input type="button" class="add_answer question" value="<?php _e('Add Answer', 'bp_polls')?>"/>
						<input type="button" class="add_answer date" value="<?php _e('Add Date', 'bp_polls')?>"/>
					</div>

					<hr/>
				</div>
				<?php $question_number++;?>
			<?php endforeach; ?>
		
		<?php endif;?>
	</div>
	<input type="button" class="add_question" value="<?php _e('Add Question', 'bp_polls')?>"/>
	<hr/>
	<input type="hidden" name="poll_id" value="<?php echo $vpl_poll->id?>" />
	<input type="hidden" name="user_id" value="<?php echo $vpl_user_id;?> " />
	<input type="hidden" name="group_id" value="<?php echo $vpl_group_id;?> " />
	<div class="vpl-message error hidden form-errors full"></div>
	<p class="center"><input type="submit" onclick="setSubmitType('full')" name="edit_poll" value="<?php _e('Save poll (votes will be cleared)', 'bp_polls')?>"/></p>
	<div class="vpl-message warning"><?php _e('This button will save ALL poll data, questions, answers. And VOTES statistic will be CLEARED', 'bp_polls')?></div>
	
</form>

<!--	Prototypes for copy	-->
<div style="display:none" class="form_prototypes">
	<div class="poll-question-item" >
			<input type="button" class="remove_question" value="<?php _e('Remove Question', 'bp_polls')?>"/>
			<h4><?php _e('Question', 'bp_polls')?></h4>
			<label><?php _e('Your question', 'bp_polls')?></label>
			<input type="text" class="q_name" />

			<label><?php _e('Answer Type', 'bp_polls')?></label>
			<select  class="q_type control_type" >
				<option value="radio"><?php _e('One Answer', 'bp_polls')?></option>
				<option value="checkbox"><?php _e('Multiple Answers', 'bp_polls')?></option>
			</select>
			<div class="answers_limit hidden">
				<label><?php _e('Limit number of answers', 'bp_polls')?></label>
				<input type="text"  class="number q_limit"/>
			</div>
			<label class="answers-title">
				<span class="question"><?php _e('Answers', 'bp_polls')?></span>
				<span class="date"><?php _e('Dates', 'bp_polls')?></span>
			</label>
			<div class="question-answers-wrap">
				<div class="question-answers">
					<div class="question-answer-item" >
						<input type="text"  class="answer-text"/>
						<div class="answer-date"/>
							<div>
								<label><?php _e('Start', 'bp_polls')?></label>
								<input type="text" class="start date_field"/>
							</div>
							<div>
								<label><?php _e('End', 'bp_polls')?></label>
								<input type="text" class="end date_field"/>
							</div>
							<div>
								<label>&nbsp;</label>
								<input type="button" class="remove-answer" value="<?php _e('Remove', 'bp_polls')?>" />
							</div>
						</div>
						<input type="button" class="remove-answer question" value="<?php _e('Remove', 'bp_polls')?>" />
					</div>
					<div class="question-answer-item" >
						<input type="text"  class="answer-text"/>
						<div class="answer-date"/>
							<div>
								<label><?php _e('Start', 'bp_polls')?></label>
								<input type="text" class="start date_field"/>
							</div>
							<div>
								<label><?php _e('End', 'bp_polls')?></label>
								<input type="text" class="end date_field"/>
							</div>
							<div>
								<label>&nbsp;</label>
								<input type="button" class="remove-answer" value="<?php _e('Remove', 'bp_polls')?>" />
							</div>
						</div>
						<input type="button" class="remove-answer question" value="<?php _e('Remove', 'bp_polls')?>" />
					</div>
				</div>
				<input type="button" class="add_answer question" value="<?php _e('Add Answer', 'bp_polls')?>"/>
				<input type="button" class="add_answer date" value="<?php _e('Add Date', 'bp_polls')?>"/>
			</div>

			<hr/>
	</div>
</div>
<?php 