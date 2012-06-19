<?php

//Include pagination class
include('Poll_Pagination.php');

/**
 * Class Poll_Extension_View
 * Display polls pages in group section
 */

class Poll_Extension_View {   
	
	/**
	 * Error messages array
	 * @var array
	 */
	public static $errors_messages = array();
	
	/**
	 * Success messages array
	 * @var array
	 */
	public static $succes_messages = array();
	
	/**
	 * Current module ( user or group )
	 * @var string
	 */
	protected $module;
	
	/**
	 * Current action (list of polls, edit poll etc)
	 * @var string
	 */
	protected $action;
	
	/**
	 * Variables sending in GET
	 * @var array
	 */
	protected $action_vars = array();
	
	/**
	 * Displayed user ID (if in members component)
	 * @var int
	 */
	protected $user_id = 0;
	
	/**
	 * Current group ID
	 * @var int
	 */
	protected $group_id = 0;
	
	
	public function __construct( $module, $action = 'list' , $action_vars = array() ) {
		global $bp;
		
		$this->module = $module;
		$this->action = $action;
		$this->action_vars = $action_vars;
		
		if( 'group' == $this->module ) {
			$this->group_id = $bp->groups->current_group->id;
		} elseif( 'user' == $this->module ) {
			$this->user_id = $bp->displayed_user->id;
		}
		
		
	}
	
	
	/**
	 * Add error to Errors array
	 * @param $error Error string
	 */
	public static function add_error( $error ) {
		self::$errors_messages[] = $error;
	}
	
	/**
	 * Add message to Success Messages array
	 * @param $message Message string
	 */
	public static function add_message( $message ) {
		self::$succes_messages[] = $message;
	}
	
	
	/**
	 * Getting Id of viewed or editing poll
	 * @return int poll_id
	 */
	protected function get_poll_id_from_request() {
		return (int) $this->action_vars[0];
	}
	
	/**
	 * Return poll status
	 * @param int $start Start date
	 * @param int $end Expire date
	 * @param bool $is_active Poll active or pending
	 * @return string Poll status
	 */
	protected function get_poll_status($start, $end, $is_active) {
		// Default status
		$status = 'draft';
		
		$time_zone = get_option('gmt_offset');
		if ( $time_zone ) {
			$time_offset = $time_zone*60*60;
			$start -= $time_offset;
			if ( $end ) {
				$end -= $time_offset;
			}
		}
		
		if( $is_active ) {
			if ( $start < time() ) {
				$status = 'open';
				if ($end != 0) {
					if( $end < time() ) {
						$status = 'closed';
					} 
				}
			}
		}
		return $status;
		
	}
	
	/**
	 * Return answers statistic for question.
	 * Statistic includes: 
	 * Number of votes and percent for each answer and total votes for question
	 * 
	 * @param int $question_id Question ID
	 * @return array Statistic info
	 */
	protected function get_question_answers_statistic( $question_id ) {
		global $wpdb;
		$statistic = array();
		
		//Getting answers 
		$sql = "SELECT answer, votes , 0 as percent FROM ". VPL_TABLE_ANSWERS ."
				WHERE question_id = ".$question_id."
				ORDER BY id
				";
		
		$statistic['answers'] = $wpdb->get_results($sql);
		
		//Calculate total votes for this question
		$sql_sum = "SELECT sum(votes) FROM ". VPL_TABLE_ANSWERS ."
					WHERE question_id = ".$question_id."
				";
		$statistic['total'] = (int) $wpdb->get_var($sql_sum);
		
		//If someone voted and question has answers
		if( $statistic['total'] != 0 && !empty($statistic['answers']) ) {
			//Calculate percentage of each answer
			foreach( $statistic['answers'] as $a ) {
				$a->percent = (int) round( ($a->votes / $statistic['total'] * 100), 2 );
			}	
		}
		
		return $statistic;
	}
	

	protected function get_question_answers_statistic_by_users( $question_id ){
		global $wpdb;
		$statistic = array();
		
		$sql = "SELECT u.ID, u.user_login FROM ". VPL_TABLE_VOTES ." v
				LEFT JOIN ". $wpdb->users ." u
				ON u.ID = v.user_id
				WHERE v.question_id = ".$question_id."
				GROUP BY u.user_login
				";
		$voted_users = $wpdb->get_results($sql);
		
		foreach( $voted_users as $user) {
			$sql = "SELECT a.id FROM ". VPL_TABLE_VOTES ." v
					LEFT JOIN ". VPL_TABLE_ANSWERS ." a
					ON a.id = v.answer_id
					WHERE v.question_id = ".$question_id."
					AND v.user_id = ".$user->ID." 
					";
			$answers = $wpdb->get_results($sql, ARRAY_N);
			foreach($answers as $i=>$a){
				$answers[$i]= $a[0];
			}
			
			$statistic[] = array( 'user' => $user, 'answers' => $answers );
		}
		
		return $statistic;
	}
	
	function user_is_author( $poll ) {
		global $wpdb, $current_user;
		if( is_int($poll) ) {
			$poll = $wpdb->get_row("
				SELECT * FROM ". VPL_TABLE_POLLS ."
				WHERE id = ".$poll_id." 
			");
		}
		$user_is_author = $current_user->ID == $poll->author_id;
		if( $user_is_author ) {
			return true;
		}
		return false;
	}
	
	
	protected function current_user_can_see_poll($poll) {
		global $bp, $wpdb, $current_user;
        
        if( current_user_can('administrator') || vpl_is_user_can_modify_polls() ) {
            return true;
        }
		
		if( is_int($poll) ) {
			$poll = $wpdb->get_row("
				SELECT * FROM ". VPL_TABLE_POLLS ."
				WHERE id = ".$poll_id." 
			");
		}
		
		if( $this->user_is_author($poll) ) {
			return true;
		}
		
		if( $poll->group_id != 0 && $current_user->ID ) {
			$member = $wpdb->get_row("
					SELECT * 
					FROM {$wpdb->prefix}bp_groups_members gm
					WHERE gm.group_id = {$poll->group_id}
					AND gm.user_id = {$current_user->ID}
					AND gm.is_confirmed = 1
					");
			
			if( $member ) {
				if( (int)$member->is_admin || (int)$member->is_mod ) {
					return true;
				}
			} else {
				$gr = $wpdb->get_row("
					SELECT * 
					FROM {$wpdb->prefix}bp_groups
					WHERE id = {$poll->group_id}
					");
					
				if( $gr->status != 'public' ) {
					return false;
				}
			}
		}
		
		
		
		
        switch($poll->restriction) {
			case 'all':
				return true;
			
			case 'auth':
				if( $current_user->ID ) {
					return true;
				} else {
					return false;
				}
				
			case 'friend':
                if( $current_user->ID ) {
					if( $poll->group_id != 0 ) {
						if ( $member ) {
							return true;
						} else {
							return false;
						}
                    } elseif( $poll->user_id != 0  ) {
                        $user_poll = $current_user->ID == $poll->user_id;
                        $user_friend = bp_is_friend($poll->user_id) != 'not_friends';
                        return ( $user_poll || $user_friend  );
                    } else {
						return false;
					}
                    
				} else {
					return false;
				}
            case 'invited':
               return $this->is_current_user_invited($poll->id);
    
			default:
				return false;
				
		}
		
	}
	
	function display_shortcode(){
		
	}
	
	/**
	 * Return all poll data with all related questions and answers
	 * @param int $poll_id Poll ID
	 * @return object $vpl_poll Poll data
	 */
	protected function get_poll_data( $poll_id ) {
		global $current_user,$wpdb;
		$user_id = $current_user->ID;
		
		
		if( $this->module == VPL_GROUP_MODULE ) {
			// Get current poll
			$vpl_poll = $wpdb->get_row("
				SELECT p.* FROM ". VPL_TABLE_POLLS ." p
				WHERE p.id = ".$poll_id." 
				AND p.group_id = ".$this->group_id."
				");
			
		} else {
			$vpl_poll = $wpdb->get_row("
				SELECT p.* FROM ". VPL_TABLE_POLLS ." p
				WHERE p.id = ".$poll_id." 
				");
		}
		
		if( !empty($vpl_poll) ) {
			
			$vpl_poll->category_id = 0;
			
			//Get Cats
			$cats = $wpdb->get_results("
				SELECT t.* 
				FROM ".VPL_TABLE_POLL_TAXONOMY." pt
				LEFT JOIN ".VPL_TABLE_TAXONOMY." t
				ON pt.tax_id = t.id
				WHERE pt.poll_id = $poll_id
				AND t.tax_type = 'category'
			");
			
			$vpl_poll->categories = $cats;
			$vpl_poll->cats = array();
			foreach( $cats as $cat ) {
				$vpl_poll->cats[] = $cat->id;
			}
			
			
			//Get tags
			$tags = $wpdb->get_results("
				SELECT t.* 
				FROM ".VPL_TABLE_POLL_TAXONOMY." pt
				LEFT JOIN ".VPL_TABLE_TAXONOMY." t
				ON pt.tax_id = t.id
				WHERE pt.poll_id = $poll_id
				AND t.tax_type = 'tag'
			");
			$vpl_poll->tags = $tags;
			if( !empty($tags) ){
				foreach($tags as $tag){
					$vpl_poll->tags_values .= $tag->name . '=' . $tag->id . '&';
				}
			}
			
			
			
			//Search for question for that poll
			$questions = $wpdb->get_results("
				SELECT * FROM ". VPL_TABLE_QUESTIONS ."
				WHERE poll_id = ".$vpl_poll->id." 
				");
			if( !empty($questions) ) {
				// Add porperty with list of questions to poll object
				$vpl_poll->questions = $questions;
				unset($questions);
				//Search answers for each question and store them in 'answers' property
				
				if ($vpl_poll->poll_type == 'dating') {
					$order_by = 'answer';
				} else {
					$order_by = 'id';
				}
				
				foreach( $vpl_poll->questions as $q) {
					$q->answers =  $wpdb->get_results("
						SELECT * FROM ". VPL_TABLE_ANSWERS ."
						WHERE question_id = ".$q->id." 
						ORDER BY ".$order_by." 
						");
					
					if( $user_id ) {
						$q->current_user_vote = (int) $wpdb->get_var('
							SELECT count(*) FROM '.VPL_TABLE_VOTES. '
							WHERE question_id = '.$q->id.'
							AND user_id = '.$user_id.'
						');
					} else {
						$q->current_user_vote = (int) $wpdb->get_var('
							SELECT count(*) FROM '.VPL_TABLE_VOTES. '
							WHERE question_id = '.$q->id.'
							AND user_ip = "'.$_SERVER['REMOTE_ADDR'].'"
						');
					}
					
				}
			} else {
				$vpl_poll->questions = array();
			}
			
			
			if( $vpl_poll->group_id ) {
				$group_slug = $wpdb->get_var("
						SELECT slug FROM {$wpdb->prefix}bp_groups 
						WHERE id = ".$vpl_poll->group_id." 
				");
				$polls_url = site_url( bp_get_groups_slug() .'/'.$group_slug . '/' . VPL_COMPONENT_SLUG . '/');
			} elseif ( $vpl_poll->user_id ) {
				$user_profile = get_user_by('id',$vpl_poll->user_id);
				if($user_profile){
					$user_slug = $user_profile->data->user_login;
					$polls_url = site_url( bp_get_members_slug() . '/'.$user_slug.'/' . VPL_COMPONENT_SLUG  . '/');
				} else {
					$polls_url = '/';
				}
			}
			
			$vpl_poll->permalink = $polls_url . 'view/' . $vpl_poll->id;
			$vpl_poll->edit_link = $polls_url . 'edit/' . $vpl_poll->id;
			$vpl_poll->status = $this->get_poll_status( $vpl_poll->start, $vpl_poll->expiry, $vpl_poll->active );
		} 
		
		return $vpl_poll;
	}
	
	/**
	 * Display list of polls
	 */
	protected function polls_list() {
		global $bp, $wpdb, $vpl_polls_list;
		
		// SQL "WHERE" string
		$sql_where = '';
		
		//If module GROUP search for group_id
		if( $this->module == VPL_GROUP_MODULE ){
			$sql_where = "WHERE group_id = ".$this->group_id;
		//Else if we in USER module serach for user_id
		} elseif( $this->module == VPL_USER_MODULE ) {
			$sql_where = "WHERE user_id = ".$this->user_id ." OR author_id = ".$this->user_id ;
			
		}
		
		$sql = "
			SELECT *
			FROM ". VPL_TABLE_POLLS ."
			".$sql_where." 
			ORDER BY created DESC
		";
		
		$vpl_polls_list = $wpdb->get_results($sql);
		
        
        foreach ( $vpl_polls_list as $i => $poll ) {
            if( $this->current_user_can_see_poll( $poll ) ) {
				$vpl_polls_list[$i] = $this->get_poll_data($poll->id);
			} else {
				//remove poll from list
				unset( $vpl_polls_list[$i] );
			}
		}
		
		//var_dump($vpl_polls_list);
		//die;
		$this->load_template('polls_list.php');
		
	}
	
	public function has_polls(){
		global $bp, $wpdb, $vpl_polls_list;
		
		// SQL "WHERE" string
		$sql_where = '';
		
		//If module GROUP search for group_id
		if( $this->module == VPL_GROUP_MODULE ){
			$sql_where = "WHERE group_id = ".$this->group_id;
		//Else if we in USER module serach for user_id
		} elseif( $this->module == VPL_USER_MODULE ) {
			$sql_where = "WHERE user_id = ".$this->user_id ." OR author_id = ".$this->user_id ;
			
		}
		
		$sql = "
			SELECT *
			FROM ". VPL_TABLE_POLLS ."
			".$sql_where." 
			ORDER BY created DESC
		";
		
		$vpl_polls_list = $wpdb->get_results($sql);
		
		if( count( $vpl_polls_list ) )
			return TRUE;
		else 
			return FALSE;
	}
	
	/**
	 * Display list of viewed user polls
	 */
	protected function user_polls() {
		global $bp, $wpdb, $vpl_polls_list;
		
		if( $this->module != VPL_USER_MODULE ) {
			$this->show_404();
			return;
		} 
		
		$sql = "
			SELECT *
			FROM ". VPL_TABLE_POLLS ." 
			WHERE author_id = ".$this->user_id." 
			OR user_id = ".$this->user_id." 
			ORDER BY created DESC
		";
		
		$vpl_polls_list = $wpdb->get_results($sql);
		
        foreach ( $vpl_polls_list as $i => $poll ) {
            if( $this->current_user_can_see_poll( $poll ) ) {
				$vpl_polls_list[$i] = $this->get_poll_data($poll->id);
			} else {
				//remove poll from list
				unset( $vpl_polls_list[$i] );
			}
		}
		
		
		$this->load_template('polls_list.php');
		
	}
	
	
	/**
	 * Display new poll form
	 */
	protected function new_poll() {
		global $vpl_user_id, $vpl_group_id,  $vpl_top_categories, $vpl_other_categories, $vpl_categories, $vpl_tags;
		
		$vpl_user_id = $this->user_id;
		$vpl_group_id = $this->group_id;
		$vpl_categories = $this->get_categories();
		
		$vpl_top_categories = array_slice($vpl_categories, 0 , 7);
		$vpl_other_categories = array_slice($vpl_categories, 7 );
		
		$vpl_tags = $this->get_tags();
		
		$this->load_template('new_poll.php');
	}
	
	/**
	 * Display Poll
	 */
	protected function view_poll() {
		global $wpdb, $vpl_poll; // vpl_poll will contains all info about current poll for template
		
		// Get poll id
		$poll_id = $this->get_poll_id_from_request();
		
		if( $poll_id ) {
			// Get current poll
			$vpl_poll = $this->get_poll_data( $poll_id );
			
			if( $vpl_poll && $this->current_user_can_see_poll($vpl_poll) ) {
				
				foreach( $vpl_poll->questions as $question ) {
					//If question is closed or user already vored - collect info about votes
					if( $question->current_user_vote != 0 || 'closed' == $vpl_poll->status ) {
						$question->statistic = $this->get_question_answers_statistic( $question->id );
					}
					
					if($vpl_poll->poll_type == 'dating'){
						$question->statistic_by_users = $this->get_question_answers_statistic_by_users( $question->id );
					}
				}
				
				//If poll is not 'Draft'
				if( 'draft' != $vpl_poll->status) {
					//Show 404 for hidden post if user is not friend (fro user Module only)
					if( $this->module == VPL_USER_MODULE && $vpl_poll->hidden == 1 && ( !bp_is_my_profile() && !( bp_is_friend($this->user_id) == 'is_friend') ) ) {
						$this->show_404();
					}else{
						if ($vpl_poll->poll_type == 'question') {
							$this->load_template('view_poll.php');
						} else {
							$this->load_template('view_poll_dating.php');
						}
					}
					
				} else {
					//Draft Polls can see only admins
					if( vpl_is_user_can_modify_polls() ){
						if ($vpl_poll->poll_type == 'question') {
							$this->load_template('view_poll.php');
						} else {
							$this->load_template('view_poll_dating.php');
						}
					}else{
						$this->show_404();	
					}
				}
					
			} else {
				$this->show_message( __('There is no poll with this ID','bp_polls') , 'error');
				$this->show_404();
			}
		} else {
			$this->show_message( __('Missing poll ID', 'bp_polls') , 'error');
			$this->show_404();
		}
	}
	
	
	function set_group_id( $id ) {
		$this->group_id = $id;
	}
	
	/*
	 * Show list of users to invite.
	 * For Grouop - members, for users - friends
	 */
	function show_invites_list( $poll ) {
		global $bp;
		if( $poll->status == 'open' && $bp->loggedin_user->id ) {
			
			$friends = array();
			$friends_ids = array();
			
			$user_friends = bp_get_friend_ids($bp->loggedin_user->id);
			if(!empty( $user_friends )) {
				$friends_ids = explode(',', $user_friends );
				foreach($friends_ids as $i => $friend_id) {
					$friends[$i] = get_userdata($friend_id);
					$friends[$i]->user_id = $friends[$i]->ID;
					$friends[$i]->is_friend = 1;
				}
			}
			
			$group_members = array();
			
			if($this->module == VPL_GROUP_MODULE) {
				$members = BP_Groups_Member::get_all_for_group( $this->group_id);
				if( $members ) {
					$group_members = $members['members'];
				}
			}
			
			foreach($group_members as $g) {
				$g->is_group_member = 1;
			}
			
			foreach($group_members as $i => $gr ) {
				foreach($friends as $j => $fr ){
					if($fr->user_id == $gr->user_id) {
						$group_members[$i]->is_friend = 1;
						unset($friends[$j]);
					}
				}
			}
			
			
			$users = array_merge($group_members, $friends);
			
			
			foreach($users as $i => $friend) {
				if( !( $friend->user_id != $bp->loggedin_user->id && $friend->user_id != $this->user_id ) ) {
					unset($users[$i]);
				} else {
					if( in_array($friend->user_id, $friends_ids) ){
						$friend->is_friend = 1;
					}
				}
			}
			
			
			?>
			<div class="invite_users_wrap">
				<div class="hidden invite_users_list">
				<?php 
					if( !empty($users)) {
						?>
						<div class="fast_invite">
							<?php if($this->module == VPL_GROUP_MODULE):?>
							<input type="button" class="select_group" value="<?php _e('Invite group users', 'bp_polls'); ?>"/><br/>
							<input type="button" class="select_friends" value="<?php _e('Invite all friends', 'bp_polls'); ?>"/><br/>
							<?php endif;?>
							<input type="button" class="select_all" value="<?php _e('Invite all users', 'bp_polls'); ?>"/>
						</div>
						<div class="friends">
						<?php
						foreach($users as $friend) {
							?>	
							<div class="friend <?php if($friend->is_friend) echo 'is_friend '; if($friend->is_group_member) echo 'is_group_member';?>" >
								<?php echo bp_core_fetch_avatar(array('item_id' => $friend->user_id, 'class'=>'' )); ?>
								<p class="friend_name"><?php echo $friend->user_login;?></p>
								<input type="hidden" class="friend_id" value="<?php echo $friend->user_id;?>" />
							</div>
							<?php
						}
						
						?>
						</div>
						<div style="clear:both"></div>
						<form class="invites_form">
							<input type="button" class="send_invites_button" value="<?php _e('Send Invites', 'bp_polls')?>" />
							<input type="button" class="cancel_invites_button" value="<?php _e('Cancel', 'bp_polls')?>" />
							<input type="hidden" class="friend_ids" value="" />
						</form>
						<?php
					} else {
						echo "<p class='vpl-message warning'>". __('No users to invite', 'bp_polls'). "</p>";
					}
				?>
				</div>
			</div>
			<?php
		}
	}
	
	/* Show invites button */
	 function show_invites_button($poll){
		 global $bp;
		 if( $poll->status == 'open' && $bp->loggedin_user->id ){
			?>
			<a class="button invite_user" href="javascript:void(0)" /><?php _e('Invite to the poll', 'bp_polls')?></a>
			<?php
		 }
	 }
	
	/**
	 * Display poll edit form
	 */
	protected function edit_poll() {
		global $wpdb, $vpl_poll, $vpl_categories, $vpl_top_categories, $vpl_middle_categories, $vpl_other_categories, $vpl_user_id, $vpl_group_id, $vpl_tags; // vpl_poll will contains all info about current poll for template
		
		$vpl_user_id = $this->user_id;
		$vpl_group_id = $this->group_id;
		
		$poll_id = $this->get_poll_id_from_request();
		if( $poll_id ) {
			// Get current poll
			$vpl_poll = $this->get_poll_data( $poll_id );
				if( $vpl_poll ) {
					if ( vpl_is_user_can_modify_polls() || $this->user_is_author($vpl_poll) ) {
						
						$vpl_categories = $this->get_categories();
						
						$vpl_top_categories = array();
						$vpl_other_categories = array();
						
						foreach( $vpl_categories as $cat ) {
							if( in_array($cat->id, $vpl_poll->cats) ) {
								$vpl_top_categories[] = $cat;
							} else {
								$vpl_other_categories[] = $cat;
							}
						}
						
						$top_count = count( $vpl_top_categories );
						if( $top_count < 7 ) {
							for($i = 0; $i < 7-$top_count ; $i++) {
								if( !empty($vpl_other_categories) ){
									$vpl_middle_categories[] = array_shift($vpl_other_categories);
								}
							}
						}
						
						
						
						$vpl_tags = $this->get_tags();
						$this->load_template('edit_poll.php');
						
					} else {
						$this->show_403();
					}
				} else {
					$this->show_message( __('There is no poll with this ID', 'bp_polls'), 'error' );
					$this->show_404();
				}
		} else {
			$this->show_message( __('Missing poll ID', 'bp_polls'), 'error' );
			$this->show_404();
		}
	}
	
	
	//-----------------------------------//
	//    Taxonomy functions block 
	//-----------------------------------//
	
	/**
	 * This method start processing taxonomy section of url's
	 */
	protected function taxonomy() {
		
		if( !empty($this->action_vars) ) {
			/*
			 * First element of action_vars is action of taxonomy block 
			 * Second element, if exists, contain "id" of taxonomy item 
			 * (  ".../polls/taxonomy/$taxonomy_action/$taxonomy_id" )
			 */
			$taxonomy_action = $this->action_vars[0];
			$taxonomy_id = $this->action_vars[1];
			
			switch( $taxonomy_action ) {
				//View polls in this taxonomy
				case "view":
					$this->view_taxonomy( $taxonomy_id );
					break;
				
				//Create new taxonomy ( only for category)
				case "new":
					$this->new_category();
					break;
				
				//Edit taxonomy 
				case "edit":
					$this->edit_taxonomy( $taxonomy_id );
					break;
				
				//List of all taxonomies	
				default:
					$this->list_taxonomy();
					break;
			}
			
		} else {
			$this->list_taxonomy();
		}
		
	}
	
	/**
	 * Display list of polls in taxonomy 
	 * @param int $tax_id Taxonomy ID
	 */
	protected function view_taxonomy( $tax_id ) {
		global $wpdb, $vpl_polls_list;
		
		if( $tax_id ) {
			//Get category by ID
			$taxonomy = $wpdb->get_row("
					SELECT * 
					FROM ".VPL_TABLE_TAXONOMY." 
					WHERE group_id = ".$this->group_id." 
					AND id = ".$tax_id."
					 ");
			
			if( $taxonomy ) {
				
				//If category exist, select related polls
				$sql = "
					SELECT p.*
					FROM ". VPL_TABLE_POLLS ." p
					LEFT JOIN ". VPL_TABLE_POLL_TAXONOMY ." t 
					ON t.poll_id = p.id
					WHERE t.tax_id = $tax_id
					ORDER BY p.created DESC
				";
				$vpl_polls_list = $wpdb->get_results($sql);
				
				//If there are polls in this category
				if( !empty( $vpl_polls_list ) ) {
					foreach ( $vpl_polls_list as $i => $poll ) {
						
						if( $this->current_user_can_see_poll($poll) ){
							//Get question for each poll
							$questions = $wpdb->get_results("SELECT question FROM ". VPL_TABLE_QUESTIONS ." WHERE poll_id = ".$poll->id." ");
							$poll->questions = $questions;
							$poll->number = $i + 1;
							//Link to poll
							$poll->permalink = VPL_CURRENT_COMPONENT_URL . 'view/' . $poll->id;
							//Link to poll edit
							$poll->edit_link = VPL_CURRENT_COMPONENT_URL . 'edit/' . $poll->id;
							$poll->status = $this->get_poll_status( $poll->start, $poll->expiry, $poll->active );
						}else{
							unset($vpl_polls_list[$i]);
						}
					}
					
					//Start output
					echo '<h3>'.ucfirst( $taxonomy->tax_type ).': '.ucfirst( $taxonomy->name ).'</h3>';	
					$this->load_template('polls_list.php');
					
				} else {
					//If there no polls in this cat - show message
					echo '<h3>'.ucfirst( $taxonomy->tax_type ).': '.ucfirst( $taxonomy->name ).'</h3>';
					echo '<p>' . __('There are no polls in this taxonomy','bp_polls') . '</p>';
				}
				
			} else {
				//If cant find category by ID
				$this->show_message( __('There in no taxonomy with this ID', 'bp_polls'), 'error' );
				$this->show_404();
			}
			
		} else {
			//If missing taxonomy_id 
			$this->show_message( __('Missing taxonomy ID', 'bp_polls'), 'error' );
			$this->show_404();
		}
		
	}
	
	/**
	 * Display a List of all taxonomies
	 */
	protected function list_taxonomy() {
		global $wpdb, $vpl_group_categories, $vpl_group_tags, $vpl_max_tags_count,$vpl_min_tags_count;
		
		if ( $this->module == 'group' ) {
			$where = "WHERE t.group_id = ".$this->group_id;
		} else {
			$where = "WHERE t.user_id = ".$this->user_id;
		}
		
		//Get all categories
		$vpl_group_categories = $wpdb->get_results("
					SELECT t.*, COUNT( pt.poll_id ) as count
					FROM ".VPL_TABLE_TAXONOMY." t
					LEFT JOIN ".VPL_TABLE_POLL_TAXONOMY." pt
					ON pt.tax_id = t.id
					" .$where. "
					AND t.tax_type = 'category' 
					GROUP BY t.id
					ORDER BY count DESC
					");
		
		//Get tags
		$vpl_group_tags = $wpdb->get_results("
					SELECT t.*, COUNT( pt.poll_id ) as count
					FROM ".VPL_TABLE_TAXONOMY." t
					LEFT JOIN ".VPL_TABLE_POLL_TAXONOMY." pt
					ON pt.tax_id = t.id
					" .$where. "
					AND t.tax_type = 'tag' 
					GROUP BY t.id
					ORDER BY t.name ASC
					");
		if( !empty($vpl_group_tags) ) {
			foreach( $vpl_group_tags as $t ) {
				if($t->count < 20 ){
					$t->size = 100 + ( ( $t->count - 1 ) * 5 );				
				}else{
					$t->size = 200;
				}
			}
		}
		
		$this->load_template('taxonomy.php');
	}
	
	/**
	 * New category form
	 */
	protected function new_category() {
		global $vpl_group_id, $vpl_user_id;
		$vpl_group_id = $this->group_id;
		$vpl_user_id = $this->user_id;
		
		$this->load_template('new_taxonomy.php');
	}
	
	/**
	 * Edit taxonomy form
	 * @param int $tax_id 
	 */
	protected function edit_taxonomy( $tax_id ) {
		global $wpdb, $vpl_group_id, $vpl_taxonomy ,$vpl_user_id;
		$vpl_group_id = $this->group_id;
		$vpl_user_id = $this->user_id;
		
		if( $tax_id ) {
			$vpl_taxonomy = $wpdb->get_row("
					SELECT *
					FROM ".VPL_TABLE_TAXONOMY."
					WHERE id = ".$tax_id."
			");
			$this->load_template('edit_taxonomy.php');
		} else {
			$this->show_message( __('Missing category ID', 'bp_polls'), 'error' );
			$this->show_404();
		}
	}
	
	/**
	 * Return List of categories in current group
	 * @return array List of categories in group
	 */
	protected function get_categories() {
		global $wpdb;
		
		if ( $this->module == 'group' ) {
			$where = "WHERE t.group_id = ".$this->group_id;
		} else {
			$where = "WHERE t.user_id = ".$this->user_id;
		}
				
		//Get all categories
		$vpl_group_categories = $wpdb->get_results("
					SELECT t.*, COUNT( pt.poll_id ) as count
					FROM ".VPL_TABLE_TAXONOMY." t
					LEFT JOIN ".VPL_TABLE_POLL_TAXONOMY." pt
					ON pt.tax_id = t.id
					" .$where. "
					AND t.tax_type = 'category' 
					GROUP BY t.id
					ORDER BY count DESC
					");
		
		return $vpl_group_categories;
	}
	
	/**
	 * Return List of tag in current group
	 * @return array List of tag in group
	 */
	protected function get_tags() {
		global $wpdb;
		
		if ( $this->module == 'group' ) {
			$where = "WHERE group_id = ".$this->group_id;
		} else {
			$where = "WHERE user_id = ".$this->user_id;
		}
		
		$vpl_group_tags = $wpdb->get_results("
					SELECT *
					FROM ".VPL_TABLE_TAXONOMY."
					" .$where. "
					AND tax_type = 'tag' 
					ORDER BY name
					");
		return $vpl_group_tags;
	}
	
	
	//  Taxonomy functions block End
	//---------------------------------//
	
	
	/**
	 * Show 404 - Page not found template
	 */
	protected function show_404() {
		$this->load_template('404.php');
	}
	
	
	/**
	 * Show 403 - Access Denied template
	 */
	protected function show_403() {
		$this->load_template('403.php');
	}
	
	
	/**
	 * Load template. Using template path of plugin. 
	 * @param string $template_name File name of template ( with subdirectory )
	 */
	protected function load_template( $template_name, $load = TRUE, $require_once = TRUE ) {
	    $located = '';
	    $located = locate_template( 'polls/' . $template_name, $load, $require_once );

	    if ( $located == '' ) {
		    if ( !$template_name )
				continue;
			
		    if ( file_exists( VPL_ROOT_PATH . 'templates/polls/' . $template_name ) ) {
				$located = VPL_ROOT_PATH . 'templates/polls/' . $template_name;
		    }
	    }

	    if ( $load && '' != $located )
		   include_once( $located );
	}
			
	
	/**
	 * Subnavigation items of poll extension
	 */
	protected function display_subnavigation() {
		if( $this->module == 'group' ) {
			global $vpl_polls_list; ?>

			<?php if( !empty( $vpl_polls_list ) ): ?>
			<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
				<ul>
					<li <?php if('list' == $this->action ) echo 'class="current"'; ?> ><a href="<?php echo VPL_CURRENT_COMPONENT_URL ?>"><?php _e('Polls List', 'bp_polls')?></a></li>
					<?php if( vpl_is_user_can_modify_polls() || vpl_is_user_can_create_polls() ):?>
						<li <?php if('new' == $this->action ) echo 'class="current"'; ?> ><a href="<?php echo VPL_CURRENT_COMPONENT_URL . 'new/' ?>"><?php _e('New Poll', 'bp_polls')?></a></li>
					<?php endif;?>
					<li <?php if('taxonomy' == $this->action ) echo 'class="current"'; ?> ><a href="<?php echo VPL_CURRENT_COMPONENT_URL . 'taxonomy/' ?>"><?php _e('Taxonomy', 'bp_polls')?></a></li>
					
				</ul>
			</div>
			<?php else: ?>
			<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
				<ul>
					<?php if( vpl_is_user_can_modify_polls() || vpl_is_user_can_create_polls() ):?>
						<li <?php if('new' == $this->action ) echo 'class="current"'; ?> ><a href="<?php echo VPL_CURRENT_COMPONENT_URL . 'new/' ?>"><?php _e('New Poll', 'bp_polls')?></a></li>
					<?php endif;?>
					<li <?php if('list' == $this->action ) echo 'class="current"'; ?> ><a href="<?php echo VPL_CURRENT_COMPONENT_URL ?>list/"><?php _e('Polls List', 'bp_polls')?></a></li>
					<li <?php if('taxonomy' == $this->action ) echo 'class="current"'; ?> ><a href="<?php echo VPL_CURRENT_COMPONENT_URL . 'taxonomy/' ?>"><?php _e('Taxonomy', 'bp_polls')?></a></li>
					
				</ul>
			</div>	
			<?php endif; ?>
			<?php
		}
	}
	
	
	/** 
	 * Display single message.
	 * Using for output errors or notify
	 * 
	 * @param string $message Message for output 
	 * @param string $class CSS Class (warning, error etc)
	 */
	protected function show_message( $message , $class ) {
		if( $message ) {
			echo '<p class="vpl-message '. $class .'" >';
			echo $message;
			echo '</p>';
		}
	}
	
	
	/**
	 * Display messages from array
	 * 
	 * @param array $messages Array of Messages 
	 * @param string $class CSS Class (warning, error etc)
	 */
	protected function show_messages( $messages , $class) {
		if( !empty($messages) ) {
			foreach($messages as $message){
				$this->show_message( $message , $class );
			}
		}
	}
	
	
	/**
	 * Public method for clients code. 
	 * All starts from entry point.
	 */
	public function display() {
		
		//Show subnavigation
		$this->display_subnavigation();
		
		//Display messages
		if( !empty( self::$succes_messages ) ) {
			$this->show_messages( self::$succes_messages, 'success');
		}
		if( !empty( self::$errors_messages ) ) {
			$this->show_messages( self::$errors_messages, 'error');
		}
		
		//Load main part
		switch( $this->action ) {
			//For polls listing
			case 'list' :
				$this->polls_list();
				break;
			
			//View single poll
			case 'view' :
				$this->view_poll();
				break;
			
			//New poll form
			case 'new' :
				//Check permission
				if ( vpl_is_user_can_modify_polls() || vpl_is_user_can_create_polls() ) {
					$this->new_poll();
				} else {
					$this->show_403();
				}
				break;
			
			//Edit poll form
			case 'edit' :
				$this->edit_poll();
				break;
			
			// Taxonomy block (list of categories, tags )
			case 'taxonomy' :
				$this->taxonomy(); // In this method follow future processing
				break;
			
			//For polls listing
			case 'user_polls' :
				$this->user_polls();
				break;
			
			//By default show 404 error
			default:
				$this->show_404();
		}
		
	}
	
	
	public function avaible_polls(){
		global $bp, $wpdb, $vpl_polls_list;
		
		$sql = "
			SELECT p.*, g.slug as group_slug
			FROM ". VPL_TABLE_POLLS ." p
			LEFT JOIN ".$wpdb->prefix."bp_groups g 
			ON g.id = p.group_id
			ORDER BY p.created DESC
		";
		
		$vpl_polls_list_not_filter = $wpdb->get_results($sql);
		
        foreach ( $vpl_polls_list_not_filter as $i => $poll ) {
            if( ! $this->current_user_can_see_poll( $poll ) ) {
				unset($vpl_polls_list_not_filter[$i]);
			}
		}
		
        $vpl_polls_list_full = $this->get_filtered_polls($vpl_polls_list_not_filter);
		
		$per_page = (int) get_option('posts_per_page');
		$pagination = new VPL_Poll_Pagination($vpl_polls_list_full, $per_page, __('&laquo; Previous', 'bp_polls'), __('Next &raquo;', 'bp_polls') );
		$vpl_polls_list = $pagination->get_sliced_data();
		
		foreach ( $vpl_polls_list as $i => $poll ) {
			
			$component_url = '';
			if( $poll->user_id != 0 ) {
				$component_url = bp_core_get_user_domain($poll->user_id);
			}elseif( $poll->group_id != 0 ) {
				$component_url = bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' .$poll->group_slug. '/';
			}

			$questions = $wpdb->get_results("SELECT question FROM ". VPL_TABLE_QUESTIONS ." WHERE poll_id = ".$poll->id." ");
			$poll->questions = $questions;
			$poll->permalink =  $component_url . 'polls/view/' . $poll->id;
			$poll->edit_link =  $component_url . 'polls/edit/' . $poll->id;
			$poll->status = $this->get_poll_status( $poll->start, $poll->expiry, $poll->active );
		}
		
		$pagination->show_pagination();
		$this->load_template('polls_list.php');
		$pagination->show_pagination();
	}
	
    function is_current_user_invited($poll_id){
        global $wpdb, $current_user;
        $res = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."vpl_invites WHERE poll_id = ".$poll_id." AND user_id = ".$current_user->ID ." ");
        if( !empty($res) ) {
            return true;
        } else {
            return false;
        }
    }
	/*
	 * Return filterd polls array
	 */
	protected function get_filtered_polls($vpl_polls_list_not_filter){
		$vpl_polls_list_full = array();
		foreach ( $vpl_polls_list_not_filter as $i => $poll ){
            $poll->status = $this->get_poll_status( $poll->start, $poll->expiry, $poll->active );
			if( 'draft' != $poll->status || vpl_is_user_can_modify_polls() == 1 ){
				$vpl_polls_list_full[] = $poll;
			}
            
		}
		foreach($vpl_polls_list_full as $i => $poll){
			$poll->number = $i + 1;
		}
		
		return $vpl_polls_list_full;
	}
	
} // End of Class Poll_Extension_View
