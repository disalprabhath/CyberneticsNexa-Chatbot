<?php
/**
 * Admin: dashboard menu, settings registration and admin assets.
 *
 * @package CyberneticsNexa_Chatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the dashboard menu page, the settings and the admin-only assets.
 */
class CNX_Admin {

	const SLUG = 'cyberneticsnexa-chatbot';

	/**
	 * Hook suffix of the dashboard menu page, set by menu().
	 *
	 * @var string
	 */
	private static $hook = '';

	/**
	 * Hooks the admin menu, settings and asset callbacks into WordPress.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( CNX_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Adds the "Chatbot" dashboard menu page.
	 */
	public static function menu() {
		self::$hook = add_menu_page(
			__( 'CyberneticsNexa Chatbot', 'cyberneticsnexa-chatbot' ),
			__( 'Chatbot', 'cyberneticsnexa-chatbot' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render' ),
			'dashicons-format-chat',
			58
		);
	}

	/**
	 * Registers the `cnx_settings` option with the Settings API.
	 */
	public static function register() {
		register_setting(
			'cnx_group',
			CNX_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'CNX_Settings', 'sanitize' ),
				'default'           => CNX_Settings::defaults(),
			)
		);
	}

	/**
	 * Enqueues the admin screen assets, limited to the plugin's own settings page.
	 *
	 * @param string $hook Hook suffix of the current admin screen.
	 */
	public static function assets( $hook ) {
		if ( ! self::$hook || self::$hook !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'cnx-admin', CNX_URL . 'admin/css/admin.css', array(), CNX_VERSION );
		wp_enqueue_script(
			'cnx-admin',
			CNX_URL . 'admin/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
			CNX_VERSION,
			true
		);
		wp_localize_script(
			'cnx-admin',
			'CNX_ADMIN',
			array(
				'chooseImage'  => __( 'Choose image', 'cyberneticsnexa-chatbot' ),
				'mediaMissing' => __( 'The WordPress media library could not be loaded. Paste an image URL below instead, or reload this page.', 'cyberneticsnexa-chatbot' ),
				'useImage'     => __( 'Use this image', 'cyberneticsnexa-chatbot' ),
				'newReply'     => __( 'New reply', 'cyberneticsnexa-chatbot' ),
				'max'          => CNX_Settings::MAX_REPLIES,
				'maxMsg'       => __( 'You have reached the maximum number of quick replies.', 'cyberneticsnexa-chatbot' ),
			)
		);
	}

	/**
	 * Adds a "Settings" link to the plugin's row on the Plugins screen.
	 *
	 * @param string[] $links Existing plugin action links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'admin.php?page=' . self::SLUG );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'cyberneticsnexa-chatbot' ) . '</a>' );
		return $links;
	}

	/**
	 * Renders the settings page, gated on the `manage_options` capability.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require CNX_PATH . 'admin/views/settings-page.php';
	}
}
