<?php
/**
 * Plugin Name:       TerraGaming Media Ads
 * Plugin URI:        https://help.terragamingmedia.com/publishers/install/wordpress/
 * Description:       Show TerraGaming Media ads: adds your site tag to every page, places ad units with a block, a widget or a shortcode, and inserts in-article ads automatically.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            TerraGaming Media
 * Author URI:        https://terragamingmedia.com
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       terragaming-media-ads
 *
 * @package TerraGaming_Ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TERRAGAMING_ADS_VERSION', '0.1.0' );
define( 'TERRAGAMING_ADS_FILE', __FILE__ );
define( 'TERRAGAMING_ADS_DIR', __DIR__ );

require_once __DIR__ . '/includes/class-terragaming-ads-site-tag.php';
require_once __DIR__ . '/includes/class-terragaming-ads-settings.php';
require_once __DIR__ . '/includes/class-terragaming-ads-frontend.php';

if ( ! defined( 'TERRAGAMING_ADS_TESTING' ) ) {
	TerraGaming_Ads_Settings::register();
	TerraGaming_Ads_Frontend::register();
	add_action( 'init', 'terragaming_ads_register_block' );
	add_action( 'widgets_init', 'terragaming_ads_register_widget' );
}

/** The "TerraGaming Media Ad" block (dynamic: rendered by the shortcode renderer). */
function terragaming_ads_register_block() {
	register_block_type( __DIR__ . '/blocks/ad-unit' );
}

/** The classic "TerraGaming Media Ad" widget. */
function terragaming_ads_register_widget() {
	require_once __DIR__ . '/includes/class-terragaming-ads-widget.php';
	register_widget( 'TerraGaming_Ads_Widget' );
}
