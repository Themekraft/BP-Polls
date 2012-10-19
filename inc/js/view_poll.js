jQuery(document).ready(function() {
	
	jQuery('.vote_button').live('click',function() {
		
		var poll_id = jQuery('.vpl-view-poll').attr('rel').substr(5);
		var question  = jQuery(this).parent();
		var question_id = question.attr('rel').substr(9);
		var controls_type = question.find('.vpl-poll-answers').attr('rel').substr(5);
		var answer = '';
		
		if( controls_type == 'radio') {
			if( question.find('.vpl-poll-answers input[type=radio]:checked').length > 0) {
				answer = question.find('.vpl-poll-answers input[type=radio]:checked').val();
			}
			
		} else if ( controls_type == 'checkbox' ) {
			
			var checkboxes_values = [];
			question.find('.vpl-poll-answers input[type=checkbox]:checked').each(function(){
				checkboxes_values.push( jQuery(this).val() );
			});
			answer = checkboxes_values.join(',')
			
		} else {
			return false;
		}
		
		
		if( answer != '' ) {
			
			jQuery(this).hide();
			
			jQuery.post( '/wp-admin/admin-ajax.php',
						{
							action:'vpl_make_vote',
							poll_id: poll_id,
							question_id: question_id,
							answer: answer
						},
						function(response){
							var result = JSON.parse(response);
							if( result.type != 'error' ) {
								if( jQuery('.show_results').val() == 1 || jQuery('.is_admin').val() == 1 ) {
									//Count total votes
									var total_votes = 0;
									for( var a in result.answers){
										total_votes += parseInt(result.answers[a].votes)
									}

									var statistic = '<div class="vpl-poll-statistic" >';

									jQuery(result.answers).each(function(i, answer) {
										answer.percent =  Math.round( (parseInt(answer.votes) / total_votes * 100) * 100 ) / 100;

										statistic += '<div class="item" ><div class="title">'+answer.answer+' '+answer.percent+'% ('+answer.votes+')</div>';
										statistic += '<div class="progress-bar" style="width:'+answer.percent+'%" ></div></div>';
									});
									statistic += '<p class="total">'+__e('Total')+': '+total_votes+' ' + __e('votes') + '</p></div>';

									if( result.type == 'user_error' ) {
										statistic += '<p class="already-vote">'+result.error_message+'</p>';	
									}

									//Show startistic and remove votes buttons for curent question
									question.find('ul, p, input').remove();
									question.append(statistic)
									
								} else {
									question.find('ul, p, input').remove();
									question.append('<p class="already-vote">'+ __e('Thank you for the vote! It was successfully saved.')+ '</p>');
								}
								
							}else{
								
							}
						});
			
		} else {
			//TODO ERROR EMPTY VALUES
		}
		
	});
	
	jQuery('.dating_vote_button').live('click',function() {
		
		var poll_id = jQuery('.vpl-view-poll').attr('rel').substr(5);
		var question  = jQuery('.vpl-poll-question:first');
		var question_id = question.attr('rel').substr(9);
		var answer = '';
		
		var checkboxes_values = [];
		question.find('.user.current_user .checkbox input[type=checkbox]:checked').each(function(){
			checkboxes_values.push( jQuery(this).val() );
		});
		
		answer = checkboxes_values.join(',')
		
		if( answer != '' ) {
			jQuery(this).hide();
			question.find('.user.current_user .checkbox').addClass('loaded');
			question.find('.user.current_user .checkbox input[type=checkbox]').hide();
			jQuery.post( '/wp-admin/admin-ajax.php',
						{
							no_results: '1',
							action: 'vpl_make_vote',
							poll_id: poll_id,
							question_id: question_id,
							answer: answer
						},
						function(response){
							var result = JSON.parse(response);
							if( result.type != 'error' ) {
								if( jQuery('.show_results').val() == 1 || jQuery('.is_admin').val() == 1 ) {
									question.find('.user.current_user .checkbox ').removeClass('loaded');
									question.find('.user.current_user .checkbox input[type=checkbox]:checked').each(function(){
										jQuery(this).parent().removeClass('checkbox').addClass('yes');
										var votes = jQuery('.vote_count[rel='+jQuery(this).val()+']');
										console.log(votes);
										votes.text( parseInt(votes.text()) + 1 );

									})
									question.find('.user.current_user .checkbox input[type=checkbox]').parent().removeClass('checkbox').addClass('no');
									
								} else {
									question.find('.vpl-dating-poll-statistic').remove();
									question.append('<p class="already-vote">'+__e('Thank you for the vote! It was successfully saved.')+'</p>');
								}
								
							}else{
								question.find('.user.current_user').remove();
								question.find('.vpl-dating-poll-statistic').after('<p class="already-vote">'+result.error_message+'</p>');
							}
							
						});
			
		} else {
			//TODO ERROR EMPTY VALUES
		}
		
	});
	
	
	// On checkbox click check if maximum options limit is reached
	jQuery('.vpl-poll-answers input[type=checkbox]').live('click',function() {
		var max_options = jQuery(this).parent().parent().parent().find('.options_limit').val();
		// If limit is reached return false
		if( max_options > 0) {
			if( jQuery('.vpl-poll-answers input[type=checkbox]:checked').length > max_options) {
				return false;
			} 
		}
	});
	
	
	jQuery('.select_group').click(function() {
		jQuery('.invite_users_list .friend').removeClass('selected');
		jQuery('.invite_users_list .friend.is_group_member').addClass('selected');
		jQuery('.friend_ids').val( getFriendIdsForInvite() );
	})
	
	jQuery('.select_friends').click(function() {
		jQuery('.invite_users_list .friend').removeClass('selected');
		jQuery('.invite_users_list .friend.is_friend').addClass('selected');
		jQuery('.friend_ids').val( getFriendIdsForInvite() );
	})
	
	jQuery('.select_all').click(function() {
		jQuery('.invite_users_list .friend').addClass('selected');
		jQuery('.friend_ids').val( getFriendIdsForInvite() );
	})
	
	
	jQuery('.invite_user').click(function(){
		jQuery('.invite_users_list').slideToggle(200);
	})
	
	
	jQuery('.cancel_invites_button').click(function(){
		jQuery('.invite_users_list .friend').removeClass('selected');
		jQuery('.invite_users_list').slideToggle(200);
	});
	
	jQuery('.send_invites_button').click(function() {
		var ids = jQuery('.friend_ids').val();
		var poll_id = jQuery('.vpl-view-poll .poll_id').val();
		var poll_name = jQuery('.vpl-view-poll h3').text();
		var poll_link = jQuery('.vpl-view-poll .poll_link').val();
		
		jQuery('.invite_users_list').slideUp(100);
		jQuery('.invite_users_list .friend').removeClass('selected');
		
		if( ids ) {
			jQuery.post( '/wp-admin/admin-ajax.php',
						{
							action:'vpl_send_invites',
							friends: ids,
							id: poll_id,
							name: poll_name,
							link: poll_link
							
						},
						function(response){
							var result = JSON.parse(response);
							if( result.type == 'success' ) {
								jQuery('.vpl-poll-actions').before('<p class="vpl-message success dissapear">'+__e('Invites have been sent successfully')+'</p>');
								jQuery('.friend_ids').val('');
								setTimeout(function(){ jQuery('.dissapear').fadeOut(1000) }, 1000)
							}
			});
		}
	})
	
	jQuery('.invite_users_list .friend').toggle(function(){
			jQuery(this).addClass('selected');
			jQuery('.friend_ids').val( getFriendIdsForInvite() );
			
		},function(){
			jQuery(this).removeClass('selected');
			jQuery('.friend_ids').val( getFriendIdsForInvite() );
	})
	
	
});

function getFriendIdsForInvite() {
	var ids = [];
	jQuery('.invite_users_list .friend.selected').each(function(){
		ids.push( jQuery(this).find('.friend_id').val() );
	})
	
	return ids.join(',');
}