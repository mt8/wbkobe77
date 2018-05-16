<?php

global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS;

$HTTP_GET_VARS = $_GET;
$HTTP_POST_VARS = $_POST;
$HTTP_COOKIE_VARS = $_COOKIE;
$HTTP_SERVER_VARS = $_SERVER;

if ( ! function_exists('mysqli_list_tables' ) ) :
	//mysql_list_tablesの互換関数
	function mysqli_list_tables( $DB_NAME ) {
		global $wpdb;
		return mysqli_query($wpdb->dbh,"SHOW TABLES");
	}
endif;
