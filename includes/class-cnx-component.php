<?php
/**
 * Small reusable frontend markup components.
 *
 * @package CyberneticsNexa_Chatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Small, stateless helpers for pieces of frontend markup reused across the chatbot.
 */
class CNX_Component {

	/**
	 * Static, trusted inline SVG icon markup.
	 *
	 * @param string $name One of 'chat', 'close', 'arrow', 'wa'.
	 * @return string SVG markup, or '' for an unknown name.
	 */
	public static function svg( $name ) {
		$paths = array(
			'chat'  => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
			'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
			'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
			'wa'    => '<path d="M3 21l1.65-4.8A8.5 8.5 0 1 1 8 19.4L3 21Z"/><path d="M9 10c0 3 2 5 5 5l1.2-1.2-1.7-1-.8.6c-.9-.4-1.6-1.1-2-2l.6-.8-1-1.7L9 10Z"/>',
		);

		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}

		$open = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
		return $open . $paths[ $name ] . '</svg>';
	}

	/**
	 * Launcher icon markup for the current settings (logo, custom image, SVG or dot).
	 *
	 * @param array  $settings Settings array.
	 * @param string $logo     Logo URL, used when the icon choice is 'logo'.
	 * @return string HTML markup.
	 */
	public static function launcher_icon( $settings, $logo ) {
		$icon = $settings['launcher_icon'];

		if ( 'dot' === $icon && 'icon' === $settings['launcher_style'] ) {
			$icon = 'chat';
		}

		if ( 'logo' === $icon ) {
			return '<img class="cnx-launcher-img" src="' . esc_url( $logo ) . '" alt="" width="26" height="26">';
		}
		if ( 'custom' === $icon ) {
			$url = CNX_Settings::image_url( $settings['launcher_icon_id'], 'thumbnail' );
			if ( ! $url && ! empty( $settings['launcher_icon_url'] ) ) {
				$url = $settings['launcher_icon_url'];
			}
			if ( $url ) {
				return '<img class="cnx-launcher-img" src="' . esc_url( $url ) . '" alt="" width="26" height="26">';
			}
			$icon = 'chat';
		}
		if ( 'chat' === $icon || 'custom' === $icon ) {
			return '<span class="cnx-launcher-svg">' . self::svg( 'chat' ) . '</span>';
		}
		return '<span class="cnx-dot"></span>';
	}
}
