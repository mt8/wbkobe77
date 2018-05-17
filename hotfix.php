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


if ( ! function_exists('mysql_connect') ) :
	function mysql_connect($dbhost,$dbuser,$dbpassword) {
		return mysqli_connect($dbhost,$dbuser,$dbpassword);
	}
endif;

if ( ! function_exists('mysql_select_db') ) :
	function mysql_select_db($dbname,$mysqli) {
		return mysqli_select_db($mysqli, $dbname);
	}
endif;

if ( ! function_exists('mysql_query') ) :
	function mysql_query($query, $myslqi = null) {
		if ( ! $myslqi ) {
			global $wpdb;
			$myslqi = $wpdb->dbh;
		}
		return mysqli_query($myslqi,$query);
	}
endif;

if ( ! function_exists('mysql_error') ) :
	function mysql_error() {
		global $wpdb;
		$myslqi = $wpdb->dbh;
		return mysqli_error($myslqi);
	}
endif;

if ( ! function_exists('mysql_num_fields') ) :
	function mysql_num_fields($result) {
		return mysqli_num_fields($result);
	}
endif;

if ( ! function_exists('mysql_fetch_field') ) :
	function mysql_fetch_field($result) {
		return mysqli_fetch_field($result);
	}
endif;

if ( ! function_exists('mysql_fetch_object') ) :
	function mysql_fetch_object($result) {
		return mysqli_fetch_object($result);
	}
endif;

if ( ! function_exists('mysql_free_result') ) :
	function mysql_free_result($result) {
		return mysqli_free_result($result);
	}
endif;

if ( ! function_exists('mysql_affected_rows') ) :
	function mysql_affected_rows() {
		global $wpdb;
		return mysqli_affected_rows($wpdb->dbh);
	}
endif;

if ( ! function_exists('mysql_insert_id') ) :
	function mysql_insert_id() {
		global $wpdb;
		return mysqli_insert_id($wpdb->dbh);
	}
endif;


