<?php
/**
 * Creates tables for plugin
 * @global  $wpdb 
 */
 
function vpl_create_tables() {
	global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	// Pools table
	$table_name = $wpdb->prefix . "vpl_polls";
	$sql = "CREATE TABLE " . $table_name . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				user_id int(11) NOT NULL DEFAULT 0,
				group_id int(11) NOT NULL DEFAULT 0,
				author_id int(11) NOT NULL DEFAULT 0,
				name varchar(200) NOT NULL default '',
                created int(20) NOT NULL default 0,
				start int(20) NOT NULL default 0,
				expiry int(20) NOT NULL default 0,
				poll_type varchar(20) NOT NULL default 'question',
				show_results tinyint(1) NOT NULL default 1,
				active tinyint(1) NOT NULL default 1,
				hidden tinyint(1) NOT NULL default 0,
				restriction varchar(50) NOT NULL default 'all',
				UNIQUE KEY id (id)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
		dbDelta($sql);
    
	
	// Questions Table. poll_id is secondary key to polls table
	$table_name = $wpdb->prefix . "vpl_questions";
	
	$sql = "CREATE TABLE " . $table_name . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				poll_id int(11) NOT NULL,
				question varchar(200) character set utf8 NOT NULL default '',
				controls_type varchar(20) NOT NULL default 'radio',
				options_limit int(3) NOT NULL default '0',
				UNIQUE KEY id (id)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	dbDelta($sql);
    
	
	// Answers Table. poll_id is secondary key to polls table, question_id is key for questions table
	$table_name = $wpdb->prefix . "vpl_answers";
	
	$sql = "CREATE TABLE " . $table_name . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				poll_id int(11) NOT NULL,
				question_id int(11) NOT NULL,
				answer varchar(200) character set utf8 NOT NULL default '',
				votes int(10) NOT NULL default '0',
				UNIQUE KEY id (id)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
		dbDelta($sql);
    
	
	/*
	 *  Votes table. In this table store all votes. 
	 * We can determine user by BP User ID ( user_id ) or his IP address ( user_ip ) to avoid duble voting
	 */
	$table_name = $wpdb->prefix . "vpl_votes";
	
	$sql = "CREATE TABLE " . $table_name . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				poll_id int(11) NOT NULL,
				question_id int(11) NOT NULL,
				answer_id int(11) NOT NULL,
				timestamp varchar(20) NOT NULL default '0000-00-00 00:00:00',
				user_id int(10) NOT NULL default '0',
				user_ip varchar(100) NOT NULL default '',
				user_host VARCHAR(200) NOT NULL default '',
				UNIQUE KEY id (id),
				KEY poll_id (poll_id)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	dbDelta($sql);
    
	
	
	/*
	 *
	 */
	$table_name = $wpdb->prefix . "vpl_taxonomy";
	
	$sql = "CREATE TABLE " . $table_name . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				name varchar(100) NOT NULL,
				s_name varchar(100) NOT NULL,
				group_id int(11) DEFAULT 0,
				user_id int(11) DEFAULT 0,
				tax_type ENUM('category', 'tag'),
				UNIQUE KEY id (id)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
		dbDelta($sql);
    
	
	$table_name = $wpdb->prefix . "vpl_poll_taxonomy";
	
	$sql = "CREATE TABLE " . $table_name . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				poll_id int(11) NOT NULL,
				tax_id int(11) NOT NULL,
				UNIQUE KEY id (id)
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
	dbDelta($sql);
    
    
    $table_name = $wpdb->prefix . "vpl_invites";
    
    $sql= "CREATE TABLE  ".$table_name." (
                `id` INT( 20 ) NOT NULL AUTO_INCREMENT,
                `poll_id` INT( 20 ) NOT NULL ,
                `user_id` INT( 20 ) NOT NULL,
                UNIQUE KEY id (id)
                ) ENGINE = MYISAM ;";
    dbDelta($sql);
    
}

?>