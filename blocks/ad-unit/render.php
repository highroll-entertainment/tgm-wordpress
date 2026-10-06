<?php
/**
 * Server render of the "TerraGaming Media Ad" block: the unit's placement in the block wrapper.
 *
 * @package TerraGaming_Ads
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$terragaming_ads_html = TerraGaming_Ads_Frontend::placement( (string) ( $attributes['unit'] ?? '' ) );
if ( '' !== $terragaming_ads_html ) {
	printf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by WordPress.
		$terragaming_ads_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from a validated id with esc_attr().
	);
}
