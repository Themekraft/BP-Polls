<?php

/*
 * This function Looking for suitable submitted forms and launches appropriate processing functions
 */
function vpl_processing_post() {
	//  Submitted new poll form
	if ( isset( $_POST['create_poll'] ) ) {
		vpl_create_poll();
	}
	//  Submitted edit poll form
	if ( isset( $_POST['edit_poll'] ) ) {
		vpl_edit_poll();
	}
	//  Submitted edit poll form
	if ( isset( $_POST['edit_poll_only'] ) ) {
		vpl_save_poll_only();
	}
	// Request for Deleting poll 
	if ( isset( $_POST['delete_poll'] ) ) {
		vpl_delete_poll();
	}
	
	// Create category
	if ( isset( $_POST['add_taxonomy'] ) ) {
		vpl_add_taxonomy();
	}
	
	// Edit category
	if ( isset( $_POST['save_taxonomy'] ) ) {
		vpl_edit_taxonomy();
	}
	
	// Delete category
	if ( isset( $_POST['delete_taxonomy'] ) ) {
		vpl_delete_taxonomy();
	}
}




/*
 * Create new poll
 */
function vpl_create_poll() {
	global $wpdb,$bp, $current_user;
	
	
	$user_id = (int) $_POST['user_id'];
	$group_id = (int) $_POST['group_id'];
	$poll_name = strip_tags($_POST['poll_name']);
	$poll_start_time = esc_html($_POST['poll_start']);
	$poll_end_time = esc_html($_POST['poll_end']);
	$poll_restriction = $_POST['poll_restriction'];
	$poll_show_results = $_POST['show_results'];
	$poll_type = $_POST['poll_type'];
	$poll_active = ($_POST['poll_active'] == 'on' )? 1 : 0;
	$cats = $_POST['cats'];
	$tags = $_POST['tags'];
	
	//Insert a poll
	$poll_inserting = $wpdb->insert( VPL_TABLE_POLLS, array(
		'name' => $poll_name,
		'user_id' => $user_id,
		'group_id' => $group_id,
		'start' => $poll_start_time,
		'expiry' => $poll_end_time,
		'created' => time(),
		'restriction' => $poll_restriction,
		'author_id' => $current_user->ID, 
		'active' => $poll_active,
		'poll_type' => $poll_type,
		'show_results' => $poll_show_results
	));
	
	//I poll added succesfully, insert questions
	if( $poll_inserting ) {
		$poll_id = $wpdb->insert_id; //Inserting Poll ID
		
		vpl_update_categories($poll_id, $cats);
		vpl_update_tags($poll_id, $tags);
		
		if( count($_POST['questions']) > 0) {
			//Insert questions and answers
			vpl_create_poll_questions( $poll_id, $_POST['questions'] );
			//Redirect
			
			wp_redirect( VPL_CURRENT_COMPONENT_URL . 'view/' . $poll_id );
			die();
			
		} else {
			//Poll_Extension_View::$errors_messages[] = 'There are no question for this poll';
		}
	} else {
		
		if( vpl_is_debug() ) {
			Poll_Extension_View::$errors_messages[] = 'Inserting failing. MySQL Error:<br/>'.mysql_error();
		}else{
			Poll_Extension_View::$errors_messages[] = __('We are sorry, but poll was not created. Some error occured.','bp_polls');
		}
		
	}
	
}


/*
 * Save oonly poll info. NOt touhching questions and votes
 */
function vpl_save_poll_only(){
	global $wpdb,$bp;
	
	$poll_id = (int) $_POST['poll_id'];
	$poll_name = strip_tags( $_POST['poll_name']);
	$poll_start_time = (int) $_POST['poll_start'];
	$poll_end_time = (int) $_POST['poll_end'];
	$poll_restriction = $_POST['poll_restriction'];
	$poll_active = ($_POST['poll_active'] == 'on' )? 1 : 0;
	$poll_show_results = $_POST['show_results'];
	
	$tags = $_POST['tags'];
	$cats = $_POST['cats'];
	
	
	if( $poll_id ) {
		//Update a poll info
		$poll_updated = $wpdb->update( VPL_TABLE_POLLS, 
			array(
				'name' => $poll_name,
				'start' => $poll_start_time,
				'expiry' => $poll_end_time,
				'active' => $poll_active ,
				'show_results' => $poll_show_results,
				'restriction' => $poll_restriction ),
			array(
				'id' => $poll_id //WHERE
		));	
		
		vpl_update_categories($poll_id, $cats);
		vpl_update_tags($poll_id, $tags);
		
		if( $poll_updated !== false ) {	
			wp_redirect( VPL_CURRENT_COMPONENT_URL . 'view/' . $poll_id );
			die();
		}
	} else {
		Poll_Extension_View::$errors_messages[] = __('We are sorry, but poll not updated. Missing poll ID.','bp_polls');
	}
}

/*
 * In this function we first delete all old questions and answers of poll,
 * and ten creating new, updated questions
 */
function vpl_edit_poll() {
	global $wpdb, $bp;
	
	$poll_id = (int) $_POST['poll_id'];
	$poll_name = strip_tags($_POST['poll_name']);
	$poll_start_time = (int) $_POST['poll_start'];
	$poll_end_time = (int) $_POST['poll_end'];
	$poll_restriction = $_POST['poll_restriction'];
	$poll_active = ($_POST['poll_active'] == 'on' )? 1 : 0;
	$poll_show_results = $_POST['show_results'];
	
	$tags = $_POST['tags'];
	$cats = $_POST['cats'];
	
	if( $poll_id ) {
		//Update a poll info
		$poll_updated = $wpdb->update( VPL_TABLE_POLLS, 
			array(
				'name' => $poll_name,
				'start' => $poll_start_time,
				'expiry' => $poll_end_time,
				'restriction' => $poll_restriction,
				'show_results' => $poll_show_results,
				'active' => $poll_active ),
			array(
				'id' => $poll_id //WHERE
		));
		
		vpl_update_categories($poll_id, $cats);
		vpl_update_tags($poll_id, $tags);
		
		//If poll added succesfully, delete old and insert new questions
		if( $poll_updated !== false ) {
			vpl_delete_poll_questions( $poll_id );
			vpl_delete_poll_votes( $poll_id );
			
			if( count($_POST['questions']) > 0) {
				vpl_create_poll_questions( $poll_id, $_POST['questions'] );
			}
			
			//Redirect
			wp_redirect( VPL_CURRENT_COMPONENT_URL . 'view/' . $poll_id );
			die();
			
		} else {
			//Poll_Extension_View::$errors_messages[] = 'We are sorry, but poll not updated. Some error appear.';
		}	
		
	} // End of if( $poll_id ) 
}

/*
 * Create question and answers for poll
 */
function vpl_create_poll_questions( $poll_id, $questions) {
	global $wpdb;
	
	foreach ( $questions as $question ) {
		$question_text = strip_tags($question['name']);
		$controls_type = strip_tags($question['type']);
		$options_limit = (int) $question['limit'];

		//Insetting quesistion 
		$question_inserting = $wpdb->insert( VPL_TABLE_QUESTIONS, array(
			'poll_id' => $poll_id,
			'question' => $question_text,
			'controls_type' => $controls_type,
			'options_limit' => $options_limit
		));
		//If quesistion inserting succesfull, process answers
		if ( $question_inserting ) {
			$question_id = $wpdb->insert_id; //Last question ID
			if( count($question['answers']) > 0) {
				foreach ( $question['answers'] as $answer ) {
					$res = $wpdb->insert( VPL_TABLE_ANSWERS, array(
						'poll_id' => $poll_id,
						'question_id' => $question_id,
						'answer' => $answer
					));
					if(!res){
						//Poll_Extension_View::$errors_messages[] = 'Answer: '.$answer.', for question: "'.$question_text.'" not saved.';
					}
				}
			}

		} else {
			//Poll_Extension_View::$errors_messages[] = 'Question: "'.$question_text.'" not saved.';
		}
	}
	
}



/*
 * Delete Poll with all related questions and answers
 */
function vpl_delete_poll() {
	$poll_id = (int) $_POST['poll_id'];
	
	if( $poll_id ) {
		if( vpl_delete_poll_data( $poll_id ) ) {
			//If Poll succesfully deleting 
			vpl_delete_poll_questions( $poll_id );
			vpl_delete_poll_votes( $poll_id );
			vpl_delete_poll_taxes( $poll_id );
			//Add message
			Poll_Extension_View::$succes_messages[] = __('Poll was succesfully deleted','bp_polls');
		}
	}
}

/*
 * Delete poll from polls table but dont touch related questions and answers.
 */
function vpl_delete_poll_data( $poll_id ) {
	global $wpdb;
	
	$p_deleted = $wpdb->query("
		DELETE 
		FROM ". VPL_TABLE_POLLS ." 
		WHERE id = ". $poll_id ." 
	");
	
	return $p_deleted;
}

/*
 * Delete all polls taxonomies
 */
function vpl_delete_poll_taxes( $poll_id ) {
	global $wpdb;
	
	$p_deleted = $wpdb->query("
		DELETE 
		FROM ". VPL_TABLE_POLL_TAXONOMY ." 
		WHERE poll_id = ". $poll_id ." 
	");
	
	return $p_deleted;
}

/*
 * Delete all questions and answers related to poll.
 */
function vpl_delete_poll_questions( $poll_id ) {
	global $wpdb;
	
	//Delete all questions
	$q_deleted = $wpdb->query("
		DELETE 
		FROM ". VPL_TABLE_QUESTIONS ." 
		WHERE poll_id = ". $poll_id ." 
	");
	
	//If questions deleted - remove all answers to
	if( $q_deleted ) {
		//Delete all answers
		$a_deleted = $wpdb->query("
			DELETE 
			FROM ". VPL_TABLE_ANSWERS ." 
			WHERE poll_id = ". $poll_id ." 
		");
		if( !$a_deleted ) {
			//Error Answers not deleted
		}
	} else {
		//Error Questions not deleted
	}
	
	return ( $q_deleted && $a_deleted );
}

/*
 * Delete all votes related to poll
 */
function vpl_delete_poll_votes( $poll_id ){
	global $wpdb;
	
	//Delete all votes
	$v_deleted = $wpdb->query("
		DELETE 
		FROM ". VPL_TABLE_VOTES ." 
		WHERE poll_id = ". $poll_id ." 
	");
	
	return $v_deleted;
}


/*
 * Create categiory
 */
function vpl_add_taxonomy() {
	global $wpdb;
	
	$name = esc_html( $_POST['name'] );
	$s_name = strtolower( $name );
	$group_id = (int) $_POST['group_id'];
	$user_id = (int) $_POST['user_id'];
	$type = $_POST['type'];
	
	$tax = $wpdb->get_row("SELECT * FROM ".VPL_TABLE_TAXONOMY." where s_name = '$s_name' AND group_id = $group_id AND tax_type = '$type' ");
	
	if ($tax) {
		Poll_Extension_View::$errors_messages[] = __( ucfirst($type) . ' with name','bp_polls') . ' ' . $name . ' ' . __('is aleady exist','bp_polls');
	} else {
		$tax_insert = $wpdb->insert( VPL_TABLE_TAXONOMY, array(
			'name' => $name,
			's_name' => $s_name,
			'group_id' => $group_id,
			'user_id' => $user_id,
			'tax_type' => $type,
		));
		
		if( $tax_insert !== false) {
			Poll_Extension_View::$succes_messages[] = __( ucfirst($type) . ' "'.$name.'" saved successfully','bp_polls');
			//wp_redirect( VPL_CURRENT_COMPONENT_URL . 'taxonomy/');
			//die();
		}else{
			Poll_Extension_View::$errors_messages[] = __( ucfirst($type) . ' not saved','bp_polls');
		}
	}
	
}


/*
 * Save categiory
 */
function vpl_edit_taxonomy() {
	global $wpdb;
	
	$name = esc_html( $_POST['taxonomy_name'] );
	$cat_id = (int) $_POST['taxonomy_id'];
	
	$tax_updated = $wpdb->update( VPL_TABLE_TAXONOMY, 
			array( 'name' => $name ),
			array( 'id' => $cat_id )
	);

	if( $tax_updated !== false) {
		Poll_Extension_View::$succes_messages[] = __( ucfirst($type) . ' "'.$name.'" saved successfully','bp_polls');
	}else{
		Poll_Extension_View::$errors_messages[] = __('Taxonomy not saved','bp_polls');
	}
	
}

/*
 * Insert categiory
 */
function vpl_insert_category( $name) {
	global $wpdb;
	
	$s_name = strtolower( $name );
	$group_id = (int) $_POST['group_id'];
	$user_id = (int) $_POST['user_id'];
	
	$tax = $wpdb->get_row("SELECT * FROM ".VPL_TABLE_TAXONOMY." where s_name = '$s_name' AND group_id = $group_id AND user_id = $user_id AND tax_type = 'category'");
	
	if ($tax) {
		return $tax->id;
	} else {
		$tax_insert = $wpdb->insert( VPL_TABLE_TAXONOMY, array(
			'name' => $name,
			's_name' => $s_name,
			'group_id' => $group_id,
			'user_id' => $user_id,
			'tax_type' => 'category',
		));

		if( $tax_insert !== false) {
			return $wpdb->insert_id;
		}else{
			return 0;
		}
	}
}

function vpl_delete_taxonomy() {
	global $wpdb;
	
	//Verify wp nonce
	$nonce = $_POST['nonce'];
	if (! wp_verify_nonce($nonce, 'delete_taxonomy') ) wp_die('You havent permission for this.');
	
	$cat_id = (int) $_POST['taxonomy_id'];
	//Delete taxonomy
	$cat_deleted = $wpdb->query("
		DELETE 
		FROM ". VPL_TABLE_TAXONOMY ." 
		WHERE id = ". $cat_id ." 
	");
	//Delete all relations fronm poll_taxonomy
	if( $deleted !== false ) {
		$wpdb->query("
			DELETE 
			FROM ". VPL_TABLE_POLL_TAXONOMY ." 
			WHERE tax_id = ". $cat_id ." 
		");
		
		wp_redirect( VPL_CURRENT_COMPONENT_URL . 'taxonomy/');
		die();
	}
}

/*
 * Add poll to category
 */
function vpl_add_poll_to_category( $poll_id, $cat_id) { return true;  }

function vpl_update_categories( $poll_id, $cats) {
	$cat_ids = array();
	if( !empty($cats) ) {
		foreach($cats as $cat => $val) {

			if( substr($cat, 0, 2) == '0#' ) {
				$cat_name = substr($cat, 2);
				$cat_id = vpl_insert_category( $cat_name);
				if( $cat_id ) {
					$cat_ids[] = (int)$cat_id;
				}

			} else {
				if( (int) $cat ) {
					$cat_ids[] = (int)$cat;
				}
			}
		}
		vpl_update_poll_categories($poll_id, $cat_ids);
	}
}

function vpl_update_poll_categories( $poll_id, $cat_ids ) {
	global $wpdb;
	
	if( !empty($cat_ids) ) {
		
		$cat_ids = array_unique($cat_ids);
		
		$deleted = $wpdb->query("
			DELETE p.*  
			FROM ".VPL_TABLE_POLL_TAXONOMY." p
			LEFT JOIN ".VPL_TABLE_TAXONOMY." t
			ON t.id = p.tax_id 
			WHERE t.tax_type = 'category' 
			AND p.poll_id = $poll_id 
		");
		
		$insert_string = '';
		foreach($cat_ids as $cats_id){
			$insert_string .= "( ".$poll_id." ,  ".$cats_id." )," ;
		}
		$insert_string = substr($insert_string, 0, -1 );
		
		$wpdb->query("
			INSERT INTO  ".VPL_TABLE_POLL_TAXONOMY." 
			( `poll_id` , `tax_id` )
			VALUES 
			".$insert_string." ;
		");
	}
}




/* Creating and updating tags*/
function vpl_update_tags( $poll_id, $tags ) {
	
	$tag_list = explode('&', substr($tags, 0, -1 ) );
	$tags_ids = array();
	
	foreach($tag_list as $tag) {
		$t = explode('=', $tag);
		
		if($t[1] == 0 ) {
			if( trim($t[0]) != '') {
				$tag_id = vpl_insert_tag( trim($t[0]) );
				if( $tag_id ) {
					$tags_ids[] = $tag_id;
				}
			}
		} else {
			$tags_ids[] = $t[1];
		}
	}
	
	vpl_update_poll_tags($poll_id, $tags_ids);
}


function vpl_update_poll_tags($poll_id, $tags_ids) {
	global $wpdb;
	
	
	if( !empty($tags_ids) ) {
		
		$tags_ids = array_unique($tags_ids);
		$tax_string = implode(',',$tags_ids);
		
		// Check if this poll alreday have category
		$deleted = $wpdb->query("
			DELETE p.*  
			FROM ".VPL_TABLE_POLL_TAXONOMY." p
			LEFT JOIN ".VPL_TABLE_TAXONOMY." t
			ON t.id = p.tax_id 
			WHERE t.tax_type = 'tag' 
			AND p.poll_id = $poll_id 
		");
		
		$insert_string = '';
		foreach($tags_ids as $tag_id){
			$insert_string .= "( ".$poll_id." ,  ".$tag_id." )," ;
		}
		$insert_string = substr($insert_string, 0, -1 );
		
		$tag = $wpdb->query("
			INSERT INTO  ".VPL_TABLE_POLL_TAXONOMY." 
			( `poll_id` , `tax_id` )
			VALUES 
			".$insert_string." ;
		");
		
	}
}

function vpl_insert_tag( $tag ) {
	global $wpdb;
	$group_id = (int) $_POST['group_id'];
	$user_id = (int) $_POST['user_id'];
	
	$tax = $wpdb->get_row("SELECT * 
		FROM ".VPL_TABLE_TAXONOMY." 
		where name = '$tag' 
		AND group_id = $group_id  
		AND user_id = $user_id 
		AND tax_type = 'tag' ");
	
	if ($tax) {
		return $tax->id;
	} else {
		$tax_insert = $wpdb->insert( VPL_TABLE_TAXONOMY, array(
			'name' => $tag,
			'group_id' => $group_id,
			'user_id' => $user_id,
			'tax_type' => 'tag',
		));

		if( $tax_insert !== false) {
			return $wpdb->insert_id;
		} else {
			return 0;
		}
	}
}




?>