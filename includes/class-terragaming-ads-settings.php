<?php
/**
 * Settings → TerraGaming Media: paste the site tag (or fill in the fields), automatic in-article
 * ads, single-page-app mode. Stored in one option; every value validated on save.
 *
 * @package TerraGaming_Ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The plugin's settings and their admin page.
 */
final class TerraGaming_Ads_Settings {
	const OPTION = 'terragaming_ads_settings';
	const PAGE   = 'terragaming-ads';

	/**
	 * Defaults for a new install.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'host'             => '',
			'property'         => '',
			'publisher'        => '',
			'in_article'       => '',
			'article_selector' => '',
			'auto_article'     => true,
			'spa'              => 'auto',
		);
	}

	/**
	 * The saved settings over the defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get() {
		return wp_parse_args( get_option( self::OPTION, array() ), self::defaults() );
	}

	/** Hooks the admin page, the setting, the privacy text and the Settings link. */
	public static function register() {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( TERRAGAMING_ADS_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/** The setting and the suggested privacy policy text. */
	public static function admin_init() {
		register_setting(
			self::PAGE,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content(
				__( 'TerraGaming Media Ads', 'terragaming-media-ads' ),
				wp_kses_post(
					'<p>' . __( 'This site shows advertising from TerraGaming Media. When a visitor allows advertising, TerraGaming Media sets one first-party cookie, _tgm_vid, on the site\'s ad host for frequency capping and local geofencing, and receives the page address, the browser\'s screen size, language and time zone, and an approximate location derived from the IP address. With Global Privacy Control or without advertising consent, nothing is loaded. See https://terragamingmedia.com/legal/privacy.', 'terragaming-media-ads' ) . '</p>'
				)
			);
		}
	}

	/** Settings → TerraGaming Media. */
	public static function admin_menu() {
		add_options_page(
			__( 'TerraGaming Media Ads', 'terragaming-media-ads' ),
			__( 'TerraGaming Media', 'terragaming-media-ads' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * A Settings link on the Plugins screen.
	 *
	 * @param string[] $links The plugin's action links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		array_unshift(
			$links,
			'<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Settings', 'terragaming-media-ads' ) . '</a>'
		);
		return $links;
	}

	/**
	 * Validates a save. A pasted site tag fills the host and ids; invalid values are dropped.
	 *
	 * @param mixed $input The submitted values.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? wp_unslash( $input ) : array();
		if ( ! empty( $input['site_tag'] ) ) {
			$parsed = TerraGaming_Ads_Site_Tag::parse( (string) $input['site_tag'] );
			if ( null === $parsed ) {
				add_settings_error( self::OPTION, 'site_tag', __( 'That does not look like a TerraGaming Media site tag. Copy it again from Ad Units & Tags → Install site tag.', 'terragaming-media-ads' ) );
			} else {
				$input = array_merge( $input, $parsed );
			}
		}
		$text = static function ( $key ) use ( $input ) {
			return isset( $input[ $key ] ) ? sanitize_text_field( (string) $input[ $key ] ) : '';
		};
		$host = TerraGaming_Ads_Site_Tag::host( $text( 'host' ) );
		if ( '' !== $text( 'host' ) && '' === $host ) {
			add_settings_error( self::OPTION, 'host', __( 'The tag host must be a host name such as tgmads.example.com.', 'terragaming-media-ads' ) );
		}
		$spa = $text( 'spa' );
		return array(
			'host'             => $host,
			'property'         => TerraGaming_Ads_Site_Tag::valid( $text( 'property' ), TerraGaming_Ads_Site_Tag::PROPERTY ),
			'publisher'        => TerraGaming_Ads_Site_Tag::valid( $text( 'publisher' ), TerraGaming_Ads_Site_Tag::PUBLISHER ),
			'in_article'       => TerraGaming_Ads_Site_Tag::valid( $text( 'in_article' ), TerraGaming_Ads_Site_Tag::UNIT ),
			'article_selector' => isset( $input['article_selector'] ) && preg_match( TerraGaming_Ads_Site_Tag::SELECTOR, trim( (string) $input['article_selector'] ) ) ? trim( (string) $input['article_selector'] ) : '',
			'auto_article'     => ! empty( $input['auto_article'] ),
			'spa'              => 'manual' === $spa ? 'manual' : 'auto',
		);
	}

	/** The settings page. */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s    = self::get();
		$name = static function ( $key ) {
			return esc_attr( self::OPTION . '[' . $key . ']' );
		};
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'TerraGaming Media Ads', 'terragaming-media-ads' ); ?></h1>
			<p>
				<?php esc_html_e( 'Copy your site tag from the TerraGaming Media portal (Ad Units & Tags → Install site tag) and paste it below. The plugin adds it to every page. Then place ad units with the “TerraGaming Media Ad” block, the widget, or the [tgm_ad unit="TGM-…"] shortcode.', 'terragaming-media-ads' ); ?>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( self::PAGE ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="tgm-site-tag"><?php esc_html_e( 'Paste your site tag', 'terragaming-media-ads' ); ?></label></th>
						<td>
							<textarea id="tgm-site-tag" name="<?php echo $name( 'site_tag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by $name. ?>" rows="4" class="large-text code" placeholder="&lt;script async src=&quot;https://tgmads.example.com/tag.js&quot;&gt;&lt;/script&gt;"></textarea>
							<p class="description"><?php esc_html_e( 'Fills in the fields below. Leave empty to keep them.', 'terragaming-media-ads' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tgm-host"><?php esc_html_e( 'Tag host', 'terragaming-media-ads' ); ?></label></th>
						<td>
							<input id="tgm-host" type="text" class="regular-text code" name="<?php echo $name( 'host' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by $name. ?>" value="<?php echo esc_attr( $s['host'] ); ?>" placeholder="tgmads.example.com" />
							<p class="description"><?php esc_html_e( 'Your verified tgmads host. On it, the tag needs nothing else.', 'terragaming-media-ads' ); ?></p>
						</td>
					</tr>
					<?php
					$advanced = array(
						'property'         => array( __( 'Property ID', 'terragaming-media-ads' ), 'PROP-…' ),
						'publisher'        => array( __( 'Publisher ID', 'terragaming-media-ads' ), 'PUB-…' ),
						'in_article'       => array( __( 'In-article unit', 'terragaming-media-ads' ), 'TGM-…-INART' ),
						'article_selector' => array( __( 'Article selector', 'terragaming-media-ads' ), '.entry-content' ),
					);
					foreach ( $advanced as $key => $field ) :
						?>
						<tr>
							<th scope="row"><label for="tgm-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
							<td><input id="tgm-<?php echo esc_attr( $key ); ?>" type="text" class="regular-text code" name="<?php echo $name( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by $name. ?>" value="<?php echo esc_attr( $s[ $key ] ); ?>" placeholder="<?php echo esc_attr( $field[1] ); ?>" /></td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'In-article ads', 'terragaming-media-ads' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo $name( 'auto_article' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by $name. ?>" value="1" <?php checked( $s['auto_article'] ); ?> /> <?php esc_html_e( 'Find the post content automatically (works with any theme). Unchecked, the article selector above or the one set in the portal is used.', 'terragaming-media-ads' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tgm-spa"><?php esc_html_e( 'Page views', 'terragaming-media-ads' ); ?></label></th>
						<td>
							<select id="tgm-spa" name="<?php echo $name( 'spa' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by $name. ?>">
								<option value="auto" <?php selected( $s['spa'], 'auto' ); ?>><?php esc_html_e( 'Automatic (recommended)', 'terragaming-media-ads' ); ?></option>
								<option value="manual" <?php selected( $s['spa'], 'manual' ); ?>><?php esc_html_e( 'Manual (your theme calls tgm.pageview())', 'terragaming-media-ads' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
