<?php
if(!defined('WP_SESSION_COOKIE')) {
    define('WP_SESSION_COOKIE', '_wp_session');
}
/**
 * WordPress session managment.
 *
 * Standardizes WordPress session data and uses either database transients or in-memory caching
 * for storing user session information.
 *
 * @package WordPress
 * @subpackage Session
 * @since   3.7.0
 */

/**
 * Return the current cache expire setting.
 *
 * @return int
 */
function ipfm_session_cache_expire() {
	$session=IPFM_Session::get_instance();
	return $session->cache_expiration();
}

/**
 * Alias of wp_session_write_close()
 */
function ipfm_session_commit() {
	ipfm_session_write_close();
}

/**
 * Load a JSON-encoded string into the current session.
 *
 * @param string $data
 */
function ipfm_session_decode($data) {
	$session=IPFM_Session::get_instance();
	return $session->json_in($data);
}

/**
 * Encode the current session's data as a JSON string.
 *
 * @return string
 */
function ipfm_session_encode() {
	$session=IPFM_Session::get_instance();
	return $session->json_out();
}

/**
 * Regenerate the session ID.
 *
 * @param bool $delete_old_session
 *
 * @return bool
 */
function ipfm_session_regenerate_id($delete_old_session=false) {
	$session=IPFM_Session::get_instance();
	$session->regenerate_id($delete_old_session);
	return true;
}

/**
 * Start new or resume existing session.
 *
 * Resumes an existing session based on a value sent by the _wp_session cookie.
 *
 * @return bool
 */
function ipfm_session_start() {
	$session=IPFM_Session::get_instance();
	do_action('wp_session_start');
	return $session->session_started();
}
add_action('plugins_loaded', 'ipfm_session_start');

/**
 * Return the current session status.
 *
 * @return int
 */
function ipfm_session_status() {
	$session=IPFM_Session::get_instance();
	if ($session->session_started()) {
		return PHP_SESSION_ACTIVE;
	}
	return PHP_SESSION_NONE;
}

/**
 * Unset all session variables.
 */
function ipfm_session_unset() {
	$session=IPFM_Session::get_instance();
	$session->reset();
}

/**
 * Write session data and end session
 */
function ipfm_session_write_close() {
	$session=IPFM_Session::get_instance();
	$session->write_data();
	do_action('ipfm_session_commit');
}
add_action('shutdown', 'ipfm_session_write_close');

/**
 * Clean up expired sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method should never be called directly and should instead be triggered as part
 * of a scheduled task or cron job.
 */
function ipfm_session_cleanup() {
	global $wpdb;

	if (defined('WP_SETUP_CONFIG')) {
		return;
	}

	if (! defined('WP_INSTALLING')) {
		$expiration_keys=$wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_wp_session_expires_%'");

		$now=time();
		$expired_sessions=array();

		foreach($expiration_keys as $expiration) {
			// If the session has expired
			if ($now > intval($expiration->option_value)) {
				// Get the session ID by parsing the option_name
				$session_id=substr($expiration->option_name, 20);

				$expired_sessions[]=$expiration->option_name;
				$expired_sessions[]="_wp_session_$session_id";
			}
		}

		// Delete all expired sessions in a single query
		if (! empty($expired_sessions)) {
			$option_names=implode("','", $expired_sessions);
			$wpdb->query("DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')");
		}
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action('ipfm_session_cleanup');
}
add_action('ipfm_session_garbage_collection', 'ipfm_session_cleanup');

/**
 * Register the garbage collector as a twice daily event.
 */
function ipfm_session_register_garbage_collection() {
	if (! wp_next_scheduled('ipfm_session_garbage_collection')) {
		wp_schedule_event(time(), 'hourly', 'ipfm_session_garbage_collection');
	}
}
add_action('wp', 'ipfm_session_register_garbage_collection');
