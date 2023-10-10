<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!function_exists('wcabe_starts_with')) {
	/**
	 * Function to check if string starting
	 * with given substring
	 *
	 * @param $string string The string to search within
	 * @param $startString string The string to search for
	 *
	 * @return bool
	 */
	function wcabe_starts_with ($string, $startString)
	{
		$len = strlen($startString);
		return (substr($string, 0, $len) === $startString);
	}
}

if (!function_exists('wcabe_ends_with')) {
	/**
	 * Function to check the string if it ends
	 * with given substring or not
	 *
	 * @param $string string The string to search within
	 * @param $endString string The string to search for
	 *
	 * @return bool
	 */
	function wcabe_ends_with($string, $endString)
	{
		$len = strlen($endString);
		if ($len == 0) {
			return true;
		}
		return (substr($string, -$len) === $endString);
	}
}

if (!function_exists('wcabe_verify_ajax_nonce')) {
	/**
	 * Checks the $_POST request for existing valid nonce
	 *
	 * @return bool
	 */
	function wcabe_verify_ajax_nonce()
	{
		return wp_verify_nonce( $_POST['nonce'], 'w3ex-advbedit-nonce' );
	}
}

if (!function_exists('wcabe_verify_ajax_nonce_or_die')) {
	/**
	 * Checks the $_POST request for existing valid nonce
	 *
	 * @param string $die_message Message that will be send back to the ajax request
	 *
	 * @return void
	 */
	function wcabe_verify_ajax_nonce_or_die($die_message='no-nonce')
	{
		if (!wcabe_verify_ajax_nonce()) {
			echo json_encode( [
				'error'  => $die_message,
				'products' => []
			] );
			error_log('dying');
			die();
		}
	}
}

if (!function_exists( 'wcabe_get_current_user_roles' )) {
	/**
	 * Get an array of the current user assigned roles
	 *
	 * @return array
	 */
	function wcabe_get_current_user_roles() {
		
		if( is_user_logged_in() ) { // check if there is a logged in user
			
			$user = wp_get_current_user(); // getting & setting the current user
			$roles = ( array ) $user->roles; // obtaining the role
			
			return $roles; // return the role for the current user
			
		} else {
			
			return array(); // if there is no logged in user return empty array
			
		}
	}
}

if (!function_exists('wcabe_is_current_user_admin')) {
	/**
	 * Check the current user if he has administrator role.
	 *
	 * @return bool
	 */
	function wcabe_is_current_user_admin(): bool {
		$current_user = wp_get_current_user();
		return in_array( 'administrator', (array) $current_user->roles );
	}
}
