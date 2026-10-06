<?php
/**
 * What the plugin adds to the site: the site tag (once per page, async, in the head), ad unit
 * placements (`[tgm_ad unit="TGM-…"]`, the block and the widget share the renderer), the automatic
 * in-article container around the post content, and exclusions from cache / optimisation plugins
 * that would delay or combine the tag.
 *
 * @package TerraGaming_Ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The public side of the plugin.
 */
final class TerraGaming_Ads_Frontend {
	const HANDLE  = 'terragaming-ads';
	const ARTICLE = '[data-tgm-article]';

	/** Hooks the site tag, the shortcode, the in-article container and the cache exclusions. */
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'wp_script_attributes', array( __CLASS__, 'filter_attributes' ) );
		add_shortcode( 'tgm_ad', array( __CLASS__, 'shortcode' ) );
		add_filter( 'the_content', array( __CLASS__, 'article_container' ), 20 );
		self::exclude_from_optimizers();
	}

	/**
	 * The site tag's attributes (WordPress adds `async` and the id), or null when no host is set.
	 *
	 * @return array<string, string>|null
	 */
	public static function script_attributes() {
		$s = TerraGaming_Ads_Settings::get();
		if ( '' === $s['host'] ) {
			return null;
		}
		$attrs = array( 'src' => 'https://' . $s['host'] . '/tag.js' );
		foreach ( array(
			'property'   => 'data-tgm-property',
			'publisher'  => 'data-tgm-publisher',
			'in_article' => 'data-tgm-in-article',
		) as $key => $attr ) {
			if ( '' !== $s[ $key ] ) {
				$attrs[ $attr ] = $s[ $key ];
			}
		}
		if ( '' !== $s['article_selector'] ) {
			$attrs['data-tgm-article-selector'] = $s['article_selector'];
		} elseif ( $s['auto_article'] ) {
			$attrs['data-tgm-article-selector'] = self::ARTICLE;
		}
		if ( 'manual' === $s['spa'] ) {
			$attrs['data-tgm-spa'] = 'manual';
		}
		return $attrs;
	}

	/** Whether this request is a page that shows ads. */
	private static function on_page() {
		if ( is_admin() || is_feed() || is_embed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}
		if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
			return false;
		}
		/**
		 * Filters whether TerraGaming Media ads load on this request.
		 *
		 * @param bool $enabled True on public pages.
		 */
		return (bool) apply_filters( 'terragaming_ads_enabled', true );
	}

	/** The site tag: once per page, async, in the head (no version query: the URL is the tag). */
	public static function enqueue() {
		$attrs = self::script_attributes();
		if ( null === $attrs || ! self::on_page() ) {
			return;
		}
		wp_enqueue_script(
			self::HANDLE,
			$attrs['src'],
			array(),
			null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- the host serves the current tag.
			array(
				'strategy'  => 'async',
				'in_footer' => false,
			)
		);
	}

	/**
	 * Adds the data-tgm-* attributes to the site tag's `<script>`.
	 *
	 * @param array<string, mixed> $attributes The script tag's attributes.
	 * @return array<string, mixed>
	 */
	public static function filter_attributes( $attributes ) {
		if ( ! is_array( $attributes ) || ( $attributes['id'] ?? '' ) !== self::HANDLE . '-js' ) {
			return $attributes;
		}
		$attrs = self::script_attributes();
		if ( null === $attrs ) {
			return $attributes;
		}
		unset( $attrs['src'] );
		return array_merge( $attributes, $attrs );
	}

	/**
	 * `[tgm_ad unit="TGM-…" slot="floating"]`: one placement. Also renders the block and the widget.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'unit' => '',
				'slot' => '',
			),
			is_array( $atts ) ? $atts : array(),
			'tgm_ad'
		);
		return self::placement( (string) $atts['unit'], (string) $atts['slot'] );
	}

	/**
	 * The placement markup, or '' for an invalid unit id.
	 *
	 * @param string $unit The ad unit id (TGM-…).
	 * @param string $slot Optional: "floating".
	 */
	public static function placement( $unit, $slot = '' ) {
		$unit = TerraGaming_Ads_Site_Tag::valid( $unit, TerraGaming_Ads_Site_Tag::UNIT );
		if ( '' === $unit ) {
			return '';
		}
		$slot_attr = 'floating' === $slot ? ' data-tgm-slot="floating"' : '';
		return '<div class="tgm-ad" data-tgm-unit="' . esc_attr( $unit ) . '"' . $slot_attr . '></div>';
	}

	/**
	 * Wraps the post content so the tag finds its paragraphs on any theme (in-article ads).
	 *
	 * @param string $content The post content.
	 */
	public static function article_container( $content ) {
		$s = TerraGaming_Ads_Settings::get();
		if ( '' === $s['host'] || ! $s['auto_article'] || '' !== $s['article_selector'] ) {
			return $content;
		}
		// The queried post's own content: in the classic loop, or in a block theme's
		// core/post-content block (outside the loop) — never another post's (query loops).
		if ( ! is_singular() || ! is_main_query() || ( ! in_the_loop() && ! wp_is_block_theme() ) ) {
			return $content;
		}
		if ( (int) get_the_ID() !== (int) get_queried_object_id() ) {
			return $content;
		}
		return '<div data-tgm-article>' . $content . '</div>';
	}

	/** Keeps cache / optimisation plugins from delaying, deferring or combining the tag. */
	private static function exclude_from_optimizers() {
		$append = static function ( $items ) {
			$items   = is_array( $items ) ? $items : array();
			$items[] = '/tag.js';
			return $items;
		};
		// WP Rocket.
		add_filter( 'rocket_delay_js_exclusions', $append );
		add_filter( 'rocket_exclude_js', $append );
		add_filter( 'rocket_exclude_defer_js', $append );
		// LiteSpeed Cache.
		add_filter( 'litespeed_optimize_js_excludes', $append );
		add_filter( 'litespeed_optm_js_defer_exc', $append );
		// SiteGround Optimizer (by handle).
		$handle = static function ( $items ) {
			$items   = is_array( $items ) ? $items : array();
			$items[] = self::HANDLE;
			return $items;
		};
		add_filter( 'sgo_javascript_combine_exclude', $handle );
		add_filter( 'sgo_js_minify_exclude', $handle );
		add_filter( 'sgo_js_async_exclude', $handle );
		// Autoptimize (a comma-separated string).
		add_filter(
			'autoptimize_filter_js_exclude',
			static function ( $exclude ) {
				return trim( (string) $exclude . ', /tag.js', ', ' );
			}
		);
	}
}
