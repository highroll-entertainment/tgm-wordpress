<?php
/**
 * Brain Monkey set-up and the escaping / sanitising functions the plugin uses, as WordPress
 * implements them for these inputs.
 *
 * @package TerraGaming_Ads
 */

namespace TerraGaming_Ads\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;

abstract class TestCase extends \PHPUnit\Framework\TestCase {
	/** @var array<string, mixed> */
	protected $options = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$this->options = array();
		Functions\stubs(
			array(
				'esc_attr'            => static function ( $s ) {
					return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
				},
				'esc_html'            => static function ( $s ) {
					return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
				},
				'esc_url'             => static function ( $s ) {
					return (string) $s;
				},
				'sanitize_text_field' => static function ( $s ) {
					return trim( preg_replace( '/[\r\n\t ]+/', ' ', wp_strip_all_tags_stub( (string) $s ) ) );
				},
				'wp_unslash'          => static function ( $s ) {
					return $s;
				},
				'__'                  => static function ( $s ) {
					return $s;
				},
				'esc_html__'          => static function ( $s ) {
					return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' );
				},
				'absint'              => static function ( $v ) {
					return abs( (int) $v );
				},
				'get_option'          => function ( $name, $default = false ) {
					return array_key_exists( $name, $this->options ) ? $this->options[ $name ] : $default;
				},
				'wp_parse_args'       => static function ( $args, $defaults ) {
					return array_merge( $defaults, (array) $args );
				},
				'shortcode_atts'      => static function ( $pairs, $atts ) {
					return array_merge( $pairs, array_intersect_key( (array) $atts, $pairs ) );
				},
			)
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}

/**
 * `wp_strip_all_tags()` for the inputs used in these tests.
 *
 * @param string $s Input.
 */
function wp_strip_all_tags_stub( $s ) {
	return trim( strip_tags( preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $s ) ) );
}
