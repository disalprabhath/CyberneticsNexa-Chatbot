<?php
/**
 * Settings page view.
 *
 * @package CyberneticsNexa_Chatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$o = CNX_Settings::get();
$n = CNX_OPTION;

/* ---- Small field helpers (all output is escaped) -------------------- */

$text = function ( $key, $css_class = 'regular-text', $type = 'text', $attrs = '' ) use ( $o, $n ) {
	// $attrs is only ever a hard-coded string from this file (min/max/placeholder).
	printf(
		'<input type="%1$s" class="%2$s" name="%3$s[%4$s]" value="%5$s" %6$s>',
		esc_attr( $type ),
		esc_attr( $css_class ),
		esc_attr( $n ),
		esc_attr( $key ),
		esc_attr( $o[ $key ] ),
		$attrs // phpcs:ignore WordPress.Security.EscapeOutput
	);
};

$area = function ( $key, $rows = 3 ) use ( $o, $n ) {
	printf(
		'<textarea class="large-text" rows="%1$d" name="%2$s[%3$s]">%4$s</textarea>',
		(int) $rows,
		esc_attr( $n ),
		esc_attr( $key ),
		esc_textarea( $o[ $key ] )
	);
};

$check = function ( $key, $label ) use ( $o, $n ) {
	printf(
		'<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label>',
		esc_attr( $n ),
		esc_attr( $key ),
		checked( ! empty( $o[ $key ] ), true, false ),
		esc_html( $label )
	);
};

$select = function ( $key, $options ) use ( $o, $n ) {
	printf( '<select name="%1$s[%2$s]" id="cnx-%2$s">', esc_attr( $n ), esc_attr( $key ) );
	foreach ( $options as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $o[ $key ], $val, false ), esc_html( $label ) );
	}
	echo '</select>';
};

$color = function ( $key ) use ( $o, $n ) {
	printf(
		'<input type="text" class="cnx-color" name="%1$s[%2$s]" value="%3$s" data-default-color="%3$s">',
		esc_attr( $n ),
		esc_attr( $key ),
		esc_attr( $o[ $key ] )
	);
};

$desc = function ( $t ) {
	echo '<p class="description">' . esc_html( $t ) . '</p>';
};

/**
 * Image picker (logo / custom launcher icon).
 */
$media = function ( $key, $url_key ) use ( $o, $n ) {
	$id  = (int) $o[ $key ];
	$src = $id ? CNX_Settings::image_url( $id, 'thumbnail' ) : '';
	if ( ! $src && ! empty( $o[ $url_key ] ) ) {
		$src = $o[ $url_key ];
	}
	?>
	<div class="cnx-media">
		<input type="hidden" name="<?php echo esc_attr( $n . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $id ); ?>" class="cnx-media-id">
		<div class="cnx-media-preview">
			<?php if ( $src ) : ?>
				<img src="<?php echo esc_url( $src ); ?>" alt="">
			<?php endif; ?>
		</div>
		<div class="cnx-media-side">
			<div class="cnx-media-actions">
				<button type="button" class="button cnx-media-select"><?php esc_html_e( 'Select image', 'cyberneticsnexa-chatbot' ); ?></button>
				<button type="button" class="button-link cnx-media-remove" <?php echo $src ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'cyberneticsnexa-chatbot' ); ?></button>
			</div>
			<label class="cnx-media-url-label">
				<span><?php esc_html_e( 'Or paste an image URL', 'cyberneticsnexa-chatbot' ); ?></span>
				<input type="url" class="regular-text cnx-media-url" placeholder="https://example.com/logo.png" name="<?php echo esc_attr( $n . '[' . $url_key . ']' ); ?>" value="<?php echo esc_attr( $o[ $url_key ] ); ?>">
			</label>
			<span class="cnx-media-error" role="alert" style="display:none"></span>
		</div>
	</div>
	<?php
};

/**
 * One quick-reply row. $i is the array index ("__INDEX__" for the JS template).
 */
$reply_row = function ( $i, $r ) use ( $n ) {
	$base = $n . '[replies][' . $i . ']';
	?>
	<div class="cnx-row" data-type="<?php echo esc_attr( $r['type'] ); ?>">
		<div class="cnx-row-head">
			<span class="cnx-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'cyberneticsnexa-chatbot' ); ?>"></span>
			<strong class="cnx-row-title"><?php echo esc_html( '' !== $r['title'] ? $r['title'] : __( 'New reply', 'cyberneticsnexa-chatbot' ) ); ?></strong>
			<button type="button" class="button-link button-link-delete cnx-remove"><?php esc_html_e( 'Remove', 'cyberneticsnexa-chatbot' ); ?></button>
		</div>
		<div class="cnx-row-body">
			<div class="cnx-grid-2">
				<p>
					<label><?php esc_html_e( 'Message title (button text)', 'cyberneticsnexa-chatbot' ); ?></label>
					<input type="text" class="widefat cnx-title-input" name="<?php echo esc_attr( $base ); ?>[title]" value="<?php echo esc_attr( $r['title'] ); ?>">
				</p>
				<p>
					<label><?php esc_html_e( 'When clicked', 'cyberneticsnexa-chatbot' ); ?></label>
					<select class="widefat cnx-type" name="<?php echo esc_attr( $base ); ?>[type]">
						<option value="reply" <?php selected( $r['type'], 'reply' ); ?>><?php esc_html_e( 'Show a reply message', 'cyberneticsnexa-chatbot' ); ?></option>
						<option value="url" <?php selected( $r['type'], 'url' ); ?>><?php esc_html_e( 'Open a link', 'cyberneticsnexa-chatbot' ); ?></option>
						<option value="whatsapp" <?php selected( $r['type'], 'whatsapp' ); ?>><?php esc_html_e( 'Open WhatsApp', 'cyberneticsnexa-chatbot' ); ?></option>
					</select>
				</p>
			</div>
			<p class="cnx-if-reply">
				<label><?php esc_html_e( 'Message content (bot reply)', 'cyberneticsnexa-chatbot' ); ?></label>
				<textarea class="widefat" rows="3" name="<?php echo esc_attr( $base ); ?>[content]"><?php echo esc_textarea( $r['content'] ); ?></textarea>
			</p>
			<p class="cnx-if-url">
				<label><?php esc_html_e( 'Link URL', 'cyberneticsnexa-chatbot' ); ?></label>
				<input type="url" class="widefat" placeholder="https://" name="<?php echo esc_attr( $base ); ?>[url]" value="<?php echo esc_attr( $r['url'] ); ?>">
			</p>
			<p class="cnx-if-reply cnx-if-url">
				<label><?php esc_html_e( 'Keywords (optional, comma separated)', 'cyberneticsnexa-chatbot' ); ?></label>
				<input type="text" class="widefat" name="<?php echo esc_attr( $base ); ?>[keywords]" value="<?php echo esc_attr( $r['keywords'] ); ?>" placeholder="price, cost, quote">
				<span class="description"><?php esc_html_e( 'If a visitor types one of these words, this reply is shown.', 'cyberneticsnexa-chatbot' ); ?></span>
			</p>
		</div>
	</div>
	<?php
};

$blank_reply = array(
	'title'    => '',
	'type'     => 'reply',
	'content'  => '',
	'url'      => '',
	'keywords' => '',
);

$positions = array(
	'top-left'     => __( 'Top left', 'cyberneticsnexa-chatbot' ),
	'top-right'    => __( 'Top right', 'cyberneticsnexa-chatbot' ),
	'bottom-left'  => __( 'Bottom left', 'cyberneticsnexa-chatbot' ),
	'bottom-right' => __( 'Bottom right', 'cyberneticsnexa-chatbot' ),
);
?>
<div class="wrap cnx-wrap">
	<h1><?php esc_html_e( 'CyberneticsNexa Chatbot', 'cyberneticsnexa-chatbot' ); ?></h1>
	<?php settings_errors(); ?>

	<form action="options.php" method="post" id="cnx-form">
		<?php settings_fields( 'cnx_group' ); ?>

		<h2 class="nav-tab-wrapper cnx-tabs">
			<a href="#cnx-general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'cyberneticsnexa-chatbot' ); ?></a>
			<a href="#cnx-replies" class="nav-tab"><?php esc_html_e( 'Quick replies', 'cyberneticsnexa-chatbot' ); ?></a>
			<a href="#cnx-whatsapp" class="nav-tab"><?php esc_html_e( 'WhatsApp', 'cyberneticsnexa-chatbot' ); ?></a>
			<a href="#cnx-launcher" class="nav-tab"><?php esc_html_e( 'Floating button', 'cyberneticsnexa-chatbot' ); ?></a>
			<a href="#cnx-placement" class="nav-tab"><?php esc_html_e( 'Position & behaviour', 'cyberneticsnexa-chatbot' ); ?></a>
			<a href="#cnx-design" class="nav-tab"><?php esc_html_e( 'Colours', 'cyberneticsnexa-chatbot' ); ?></a>
		</h2>

		<!-- ============================ GENERAL ============================ -->
		<div class="cnx-panel" id="cnx-general">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Chatbot', 'cyberneticsnexa-chatbot' ); ?></th>
					<td><?php $check( 'enabled', __( 'Show the chatbot on my website', 'cyberneticsnexa-chatbot' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Logo', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<?php $media( 'logo_id', 'logo_url' ); ?>
						<?php $desc( __( 'Shown in the chat header. Square PNG, JPG or WebP, at least 80 × 80 px. Leave empty to use the default logo.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-bot-title"><?php esc_html_e( 'Chatbot title', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td><?php $text( 'bot_title', 'regular-text', 'text', 'id="cnx-bot-title"' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-bot-slogan"><?php esc_html_e( 'Slogan', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td>
						<?php $text( 'bot_slogan', 'regular-text', 'text', 'id="cnx-bot-slogan"' ); ?>
						<?php $desc( __( 'Small line under the title, for example "Usually replies in a few seconds".', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Hello message', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<?php $area( 'hello_message', 4 ); ?>
						<?php $desc( __( 'The first message visitors see when they open the chat.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-placeholder"><?php esc_html_e( 'Input placeholder', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td><?php $text( 'input_placeholder', 'regular-text', 'text', 'id="cnx-placeholder"' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Fallback message', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<?php $area( 'fallback_message', 3 ); ?>
						<?php $desc( __( 'Sent when the visitor types something that matches no quick reply. A WhatsApp button is added automatically when WhatsApp is set up.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
			</table>
		</div>

		<!-- ============================ REPLIES ============================ -->
		<div class="cnx-panel" id="cnx-replies" style="display:none">
			<p class="description cnx-intro">
				<?php esc_html_e( 'Each item becomes a button under the hello message. Set the message title (the button text) and the message content (what the bot answers). Drag to reorder.', 'cyberneticsnexa-chatbot' ); ?>
			</p>

			<div id="cnx-rows">
				<?php
				foreach ( array_values( $o['replies'] ) as $i => $r ) {
					$reply_row( $i, $r );
				}
				?>
			</div>

			<p><button type="button" class="button button-secondary" id="cnx-add-row">+ <?php esc_html_e( 'Add quick reply', 'cyberneticsnexa-chatbot' ); ?></button></p>

			<script type="text/html" id="cnx-row-tpl">
				<?php $reply_row( '__INDEX__', $blank_reply ); ?>
			</script>
		</div>

		<!-- ============================ WHATSAPP ============================ -->
		<div class="cnx-panel" id="cnx-whatsapp" style="display:none">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Live person', 'cyberneticsnexa-chatbot' ); ?></th>
					<td><?php $check( 'whatsapp_enabled', __( 'Let visitors talk to a live person on WhatsApp', 'cyberneticsnexa-chatbot' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-wa-number"><?php esc_html_e( 'WhatsApp number', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td>
						<?php $text( 'whatsapp_number', 'regular-text', 'text', 'id="cnx-wa-number" placeholder="94771234567"' ); ?>
						<?php $desc( __( 'Full international number with country code. No +, spaces or leading zeros. Example for Sri Lanka: 94771234567.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-wa-label"><?php esc_html_e( 'Chat button text', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td><?php $text( 'whatsapp_label', 'regular-text', 'text', 'id="cnx-wa-label"' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-wa-msg"><?php esc_html_e( 'Pre-filled WhatsApp message', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td><?php $text( 'whatsapp_message', 'large-text', 'text', 'id="cnx-wa-msg"' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Bot reply', 'cyberneticsnexa-chatbot' ); ?></th>
					<td><?php $area( 'whatsapp_reply', 2 ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-wa-btn"><?php esc_html_e( 'Action button text', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td><?php $text( 'whatsapp_button', 'regular-text', 'text', 'id="cnx-wa-btn"' ); ?></td>
				</tr>
			</table>
		</div>

		<!-- ============================ LAUNCHER ============================ -->
		<div class="cnx-panel" id="cnx-launcher" style="display:none">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cnx-launcher-text"><?php esc_html_e( 'Button text', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td>
						<?php $text( 'launcher_text', 'regular-text', 'text', 'id="cnx-launcher-text"' ); ?>
						<?php $desc( __( 'For example "Chat With Us". Used as the button label for screen readers when the icon-only style is selected.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Button style', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<?php
						$select(
							'launcher_style',
							array(
								'pill' => __( 'Text only', 'cyberneticsnexa-chatbot' ),
								'icon' => __( 'Icon only (round)', 'cyberneticsnexa-chatbot' ),
							)
						);
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Button icon', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<?php
						$select(
							'launcher_icon',
							array(
								'dot'    => __( 'Status dot', 'cyberneticsnexa-chatbot' ),
								'chat'   => __( 'Chat bubble', 'cyberneticsnexa-chatbot' ),
								'logo'   => __( 'My chatbot logo', 'cyberneticsnexa-chatbot' ),
								'custom' => __( 'Custom image', 'cyberneticsnexa-chatbot' ),
							)
						);
						?>
						<div class="cnx-custom-icon" style="margin-top:10px">
							<?php $media( 'launcher_icon_id', 'launcher_icon_url' ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Live animation', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<?php $select( 'animation', CNX_Settings::animations() ); ?>
						<?php $desc( __( 'Plays while the chat is closed to draw attention. Stops when the chat opens and for visitors who prefer reduced motion.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Online indicator', 'cyberneticsnexa-chatbot' ); ?></th>
					<td><?php $check( 'show_online_dot', __( 'Show a green "online" dot on the button and the chat header', 'cyberneticsnexa-chatbot' ) ); ?></td>
				</tr>
			</table>
		</div>

		<!-- ============================ PLACEMENT ============================ -->
		<div class="cnx-panel" id="cnx-placement" style="display:none">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Position', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<div class="cnx-screen">
							<?php foreach ( $positions as $val => $label ) : ?>
								<label class="cnx-pos cnx-pos-<?php echo esc_attr( $val ); ?>">
									<input type="radio" name="<?php echo esc_attr( $n ); ?>[position]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $o['position'], $val ); ?>>
									<span><?php echo esc_html( $label ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<?php $desc( __( 'The chat window opens next to the button, on the same side of the screen.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Distance from edges', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<label><?php esc_html_e( 'Side', 'cyberneticsnexa-chatbot' ); ?>
							<?php $text( 'offset_x', 'small-text', 'number', 'min="0" max="400"' ); ?> px</label>
						&nbsp;&nbsp;
						<label><?php esc_html_e( 'Top / bottom', 'cyberneticsnexa-chatbot' ); ?>
							<?php $text( 'offset_y', 'small-text', 'number', 'min="0" max="400"' ); ?> px</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Chat window width', 'cyberneticsnexa-chatbot' ); ?></th>
					<td><?php $text( 'popup_width', 'small-text', 'number', 'min="300" max="520"' ); ?> px</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto-open', 'cyberneticsnexa-chatbot' ); ?></th>
					<td>
						<?php $text( 'auto_open_delay', 'small-text', 'number', 'min="0" max="300"' ); ?> <?php esc_html_e( 'seconds after page load (0 = never)', 'cyberneticsnexa-chatbot' ); ?>
						<?php $desc( __( 'Opens once per visit, on desktop only.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Mobile', 'cyberneticsnexa-chatbot' ); ?></th>
					<td><?php $check( 'hide_on_mobile', __( 'Hide the chatbot on screens narrower than 600 px', 'cyberneticsnexa-chatbot' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-exclude"><?php esc_html_e( 'Hide on pages', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td>
						<?php $text( 'exclude_ids', 'regular-text', 'text', 'id="cnx-exclude" placeholder="12, 48, 103"' ); ?>
						<?php $desc( __( 'Comma-separated page or post IDs where the chatbot should not appear.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cnx-z"><?php esc_html_e( 'Z-index', 'cyberneticsnexa-chatbot' ); ?></label></th>
					<td>
						<?php $text( 'z_index', 'small-text', 'number', 'id="cnx-z" min="1"' ); ?>
						<?php $desc( __( 'Raise this only if your theme header or another plugin covers the chatbot.', 'cyberneticsnexa-chatbot' ) ); ?>
					</td>
				</tr>
			</table>
		</div>

		<!-- ============================ COLOURS ============================ -->
		<div class="cnx-panel" id="cnx-design" style="display:none">
			<table class="form-table" role="presentation">
				<tr><th scope="row"><?php esc_html_e( 'Header background', 'cyberneticsnexa-chatbot' ); ?></th><td><?php $color( 'color_header' ); ?></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Accent colour', 'cyberneticsnexa-chatbot' ); ?></th><td><?php $color( 'color_primary' ); ?><?php $desc( __( 'Used for the animation ring, focus states and visitor messages.', 'cyberneticsnexa-chatbot' ) ); ?></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Accent gradient end', 'cyberneticsnexa-chatbot' ); ?></th><td><?php $color( 'color_secondary' ); ?></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Send button', 'cyberneticsnexa-chatbot' ); ?></th><td><?php $color( 'color_send' ); ?></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Floating button background', 'cyberneticsnexa-chatbot' ); ?></th><td><?php $color( 'color_launcher_bg' ); ?></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Floating button text', 'cyberneticsnexa-chatbot' ); ?></th><td><?php $color( 'color_launcher_text' ); ?></td></tr>
			</table>
		</div>

		<?php submit_button( __( 'Save changes', 'cyberneticsnexa-chatbot' ) ); ?>
	</form>

	<p class="description">
		<?php
		echo wp_kses(
			__( 'Tip: add the class <code>cnx-open-chat</code> to any button or menu item (or link to <code>#cnx-open</code>) to open the chat from anywhere on your site.', 'cyberneticsnexa-chatbot' ),
			array( 'code' => array() )
		);
		?>
	</p>

	<p class="cnx-admin-footer">
		<?php esc_html_e( 'Built by', 'cyberneticsnexa-chatbot' ); ?>
		<a href="https://cyberneticsnexa.com/" target="_blank" rel="noopener noreferrer">CyberneticsNexa</a>
	</p>
</div>
