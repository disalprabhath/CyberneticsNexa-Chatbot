<?php
/**
 * Plugin Name:       CyberneticsNexa Chatbot
 * Description:       Floating chat assistant with quick-reply buttons, WhatsApp live-person handoff, animated launcher and full dashboard customisation.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            CyberneticsNexa
 * License:           GPL-2.0-or-later
 * Text Domain:       cyberneticsnexa-chatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CNX_VERSION', '1.0.1' );
define( 'CNX_FILE', __FILE__ );
define( 'CNX_PATH', plugin_dir_path( __FILE__ ) );
define( 'CNX_URL', plugin_dir_url( __FILE__ ) );
define( 'CNX_OPTION', 'cnx_settings' );

require_once CNX_PATH . 'includes/class-cnx-settings.php';
require_once CNX_PATH . 'includes/class-cnx-component.php';
require_once CNX_PATH . 'includes/class-cnx-admin.php';
require_once CNX_PATH . 'includes/class-cnx-frontend.php';

register_activation_hook( __FILE__, array( 'CNX_Settings', 'activate' ) );

add_action(
	'plugins_loaded',
	function () {
		if ( is_admin() ) {
			CNX_Admin::init();
		}
		CNX_Frontend::init();
	}
);
