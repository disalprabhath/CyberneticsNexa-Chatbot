<?php
/**
 * Frontend: enqueue assets, print CSS variables + JS config, render markup.
 *
 * @package CyberneticsNexa_Chatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the public assets and renders the chatbot markup in the footer.
 */
class CNX_Frontend {

	/**
	 * Hooks the frontend asset enqueue and markup render into WordPress.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		// Priority 5 = printed BEFORE the footer scripts (priority 20), so the markup exists when chatbot.js runs.
		add_action( 'wp_footer', array( __CLASS__, 'render' ), 5 );
	}

	/**
	 * Whether the chatbot should be shown on the current request.
	 *
	 * @return bool
	 */
	private static function should_display() {
		if ( is_admin() || is_feed() || is_embed() || wp_is_json_request() ) {
			return false;
		}
		$s = CNX_Settings::get();
		if ( empty( $s['enabled'] ) ) {
			return false;
		}
		if ( ! empty( $s['exclude_ids'] ) && is_singular() ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $s['exclude_ids'] ) ) );
			if ( in_array( (int) get_queried_object_id(), $ids, true ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Converts a hex colour to a "r,g,b" string, for use inside an rgba() CSS value.
	 *
	 * @param string $hex Hex colour, with or without the leading "#".
	 * @return string
	 */
	private static function hex_to_rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
			return '37,99,235';
		}
		return hexdec( substr( $hex, 0, 2 ) ) . ',' . hexdec( substr( $hex, 2, 2 ) ) . ',' . hexdec( substr( $hex, 4, 2 ) );
	}

	/**
	 * Enqueues the public stylesheet/script and prints the per-site CSS
	 * variables and the window.CNX_DATA config JavaScript consumes.
	 */
	public static function enqueue() {
		if ( ! self::should_display() ) {
			return;
		}
		$s = CNX_Settings::get();

		wp_enqueue_style( 'cnx-chatbot', CNX_URL . 'public/css/chatbot.css', array(), CNX_VERSION );

		$css = sprintf(
			'.cnx-root{--cnx-primary:%1$s;--cnx-primary-rgb:%2$s;--cnx-secondary:%3$s;--cnx-header:%4$s;--cnx-launcher-bg:%5$s;--cnx-launcher-text:%6$s;--cnx-send:%7$s;--cnx-x:%8$dpx;--cnx-y:%9$dpx;--cnx-w:%10$dpx;--cnx-z:%11$d;}',
			sanitize_hex_color( $s['color_primary'] ),
			self::hex_to_rgb( $s['color_primary'] ),
			sanitize_hex_color( $s['color_secondary'] ),
			sanitize_hex_color( $s['color_header'] ),
			sanitize_hex_color( $s['color_launcher_bg'] ),
			sanitize_hex_color( $s['color_launcher_text'] ),
			sanitize_hex_color( $s['color_send'] ),
			(int) $s['offset_x'],
			(int) $s['offset_y'],
			(int) $s['popup_width'],
			(int) $s['z_index']
		);
		wp_add_inline_style( 'cnx-chatbot', $css );

		wp_enqueue_script( 'cnx-chatbot', CNX_URL . 'public/js/chatbot.js', array(), CNX_VERSION, true );

		$replies = array();
		foreach ( $s['replies'] as $r ) {
			$replies[] = array(
				'title'    => $r['title'],
				'type'     => $r['type'],
				'content'  => $r['content'],
				'url'      => $r['url'],
				'keywords' => $r['keywords'],
			);
		}

		$wa_url = CNX_Settings::whatsapp_url( $s );

		$data = array(
			'hello'    => $s['hello_message'],
			'fallback' => $s['fallback_message'],
			'replies'  => $replies,
			'whatsapp' => $wa_url ? array(
				'url'    => $wa_url,
				'label'  => $s['whatsapp_label'],
				'reply'  => $s['whatsapp_reply'],
				'button' => $s['whatsapp_button'],
			) : null,
			'autoOpen' => (int) $s['auto_open_delay'],
			'i18n'     => array(
				'opening' => __( 'Opening it for you. If nothing happens, use the button below.', 'cyberneticsnexa-chatbot' ),
			),
		);

		wp_add_inline_script(
			'cnx-chatbot',
			'window.CNX_DATA = ' . wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP ) . ';',
			'before'
		);
	}

	// ------------------------------------------------------------------
	// Markup.
	// ------------------------------------------------------------------

	/**
	 * Renders the chatbot markup into the footer.
	 */
	public static function render() {
		if ( ! self::should_display() ) {
			return;
		}
		$s    = CNX_Settings::get();
		$logo = CNX_Settings::logo_url( $s );

		$classes = array(
			'cnx-root',
			'cnx-pos-' . $s['position'],
			'cnx-anim-' . $s['animation'],
			'cnx-style-' . $s['launcher_style'],
		);
		if ( ! empty( $s['hide_on_mobile'] ) ) {
			$classes[] = 'cnx-hide-mobile';
		}

		$label = '' !== trim( $s['launcher_text'] ) ? $s['launcher_text'] : __( 'Open chat', 'cyberneticsnexa-chatbot' );
		?>
<div id="cnx-root" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<div class="cnx-window" id="cnx-window" role="dialog" aria-labelledby="cnx-title" aria-hidden="true">
		<div class="cnx-header">
			<div class="cnx-avatar">
				<img src="<?php echo esc_url( $logo ); ?>" alt="" width="40" height="40">
				<?php if ( ! empty( $s['show_online_dot'] ) ) : ?>
					<span class="cnx-online" aria-hidden="true"></span>
				<?php endif; ?>
			</div>
			<div class="cnx-head-text">
				<div class="cnx-title" id="cnx-title"><?php echo esc_html( $s['bot_title'] ); ?></div>
				<?php if ( '' !== $s['bot_slogan'] ) : ?>
					<div class="cnx-slogan"><?php echo esc_html( $s['bot_slogan'] ); ?></div>
				<?php endif; ?>
			</div>
			<button type="button" class="cnx-close" aria-label="<?php esc_attr_e( 'Close chat', 'cyberneticsnexa-chatbot' ); ?>">
				<?php echo CNX_Component::svg( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?>
			</button>
		</div>

		<div class="cnx-messages" aria-live="polite"></div>
		<div class="cnx-chips"></div>

		<form class="cnx-form" autocomplete="off">
			<input type="text" class="cnx-input" placeholder="<?php echo esc_attr( $s['input_placeholder'] ); ?>" aria-label="<?php echo esc_attr( $s['input_placeholder'] ); ?>" maxlength="300">
			<button type="submit" class="cnx-send" aria-label="<?php esc_attr_e( 'Send message', 'cyberneticsnexa-chatbot' ); ?>">
				<?php echo CNX_Component::svg( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?>
			</button>
		</form>
	</div>

	<button type="button" class="cnx-launcher" aria-controls="cnx-window" aria-expanded="false" aria-label="<?php echo esc_attr( $label ); ?>">
		<?php if ( 'icon' === $s['launcher_style'] ) : ?>
			<?php echo CNX_Component::launcher_icon( $s, $logo ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
		<?php elseif ( '' !== trim( $s['launcher_text'] ) ) : ?>
			<span class="cnx-launcher-text"><?php echo esc_html( $s['launcher_text'] ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $s['show_online_dot'] ) ) : ?>
			<span class="cnx-badge" aria-hidden="true"></span>
		<?php endif; ?>
	</button>

</div>
		<?php
	}
}
