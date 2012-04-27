<?php

add_action('wp_ajax_nopriv_vpl_make_vote', 'vpl_make_vote');
add_action('wp_ajax_vpl_make_vote', 'vpl_make_vote');
add_action('wp_ajax_nopriv_vpl_send_invites', 'vpl_send_invites');
add_action('wp_ajax_vpl_send_invites', 'vpl_send_invites');

/*
 * Function that handle ajax request for voting
 * 
 */
function vpl_make_vote() {
	global $wpdb, $bp;
	$output = '';
	
	$user_id = $bp->loggedin_user->id;
	$poll_id = (int) $_POST['poll_id'];
	$question_id = (int) $_POST['question_id'];
	$answer_str = esc_html($_POST['answer']);
	$answers = explode(',', $answer_str);
	$time = time();
	$host = esc_html( $_SERVER['HTTP_HOST']);
	$ip = esc_html( $_SERVER['REMOTE_ADDR']);
	
	//If user is looged in we check by ID
	if( $user_id ) {
		$user_votes = $wpdb->get_var('
			SELECT COUNT(*) FROM '.VPL_TABLE_VOTES.'
			WHERE question_id = '.$question_id.'
			AND user_id = '.$user_id.'
		');
		if( $user_votes ){
			$output['type'] = 'error';
			$output['error_message'] = __('You have already voted', 'bp_polls');
		}
		
	} else {
		//IF not logged check IP
		$user_votes = $wpdb->get_var("
			SELECT COUNT(*) FROM ".VPL_TABLE_VOTES."
			WHERE question_id = ".$question_id."
			AND user_ip = '".$ip."'
		");
		
		if( $user_votes ){
			$output['type'] = 'error';
			$output['error_message'] = __('A voice from your IP is already considered', 'bp_polls');
		}
		
	}
	
	
	if( $user_votes == 0) {
		
		if( !empty($answers) ) {	
			foreach($answers as $answer_id) {
				$success = $wpdb->insert( VPL_TABLE_VOTES, array(
					'poll_id' => $poll_id,
					'question_id' => $question_id,
					'answer_id' => $answer_id,
					'timestamp' => $time,
					'user_id' => $user_id,
					'user_ip' => $ip,
					'user_host' => $host
				));

				if( $success ) {
					$wpdb->query("
						UPDATE ". VPL_TABLE_ANSWERS ."
						SET votes = votes + 1
						WHERE id = ".$answer_id."
					");
					
					$output['type'] = 'success';
				}
			}
		}
		
	}
	
	if( !$_POST['no_results'] ){
		//Get question statistic
		$sql = "SELECT answer, votes FROM ". VPL_TABLE_ANSWERS ."
				WHERE question_id = ".$question_id."
				ORDER BY id
				";
		$answers = $wpdb->get_results($sql);
		$output['answers'] = $answers;
	}
	
	$output = json_encode($output);
	echo $output;
	die;
}


function vpl_send_invites() {
	global $bp,$wpdb;
	$output = array();
	$user_id = $bp->loggedin_user->id;
	$friends = explode(',', $_POST['friends']);
	$poll_id = $_POST['id'];
	$poll_name = $_POST['name'];
	$poll_link = $_POST['link'];
	
	$content = '<a href="'.$bp->loggedin_user->domain.'">'.$bp->loggedin_user->fullname.'</a> ' . __('invites you to particiapte in a poll', 'bp_polls') . '<a href="'.$poll_link.'">'.$poll_name.'</a>';
	
	messages_new_message(array(
		'subject' => __('Participate in a poll!','bp_polls'),
		'recipients'=> $friends,
		'content' => $content
	));
    
    foreach($friends as $friend_id){
        $wpdb->insert( $wpdb->prefix . 'vpl_invites', array(
            'poll_id' => $poll_id,
            'user_id' => $friend_id
        ));
    }
	
	$output['type'] = 'success';
	$output = json_encode($output);
	echo $output;
	die;
}