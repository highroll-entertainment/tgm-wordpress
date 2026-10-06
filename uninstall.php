<?php
/**
 * Removes the plugin's settings when it is deleted.
 *
 * @package TerraGaming_Ads
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'terragaming_ads_settings' );
