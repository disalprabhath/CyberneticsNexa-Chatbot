<?php
/**
 * Runs when the plugin is deleted from the WordPress dashboard.
 *
 * @package CyberneticsNexa_Chatbot
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'cnx_settings' );
