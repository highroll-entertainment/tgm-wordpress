<?php
/**
 * Reads the host and ids out of the site tag a publisher pastes from the TerraGaming Media portal:
 * the one-line tag (`<script async src="https://tgmads.<domain>/tag.js">`), the fallback host's tag
 * with its `data-tgm-*` attributes, or a pre-2026 inline `window.tgmConfig` snippet.
 *
 * @package TerraGaming_Ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site tag parsing and the value formats shared with the settings.
 */
final class TerraGaming_Ads_Site_Tag {
	const HOST      = '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+$/';
	const PROPERTY  = '/^PROP-\d+-\d+$/';
	const PUBLISHER = '/^PUB-\d+$/';
	const UNIT      = '/^TGM-[A-Z0-9]{3}-[A-Z0-9]{2,12}$/';
	const SELECTOR  = '/^[^<>"\'`\\\\{};]{1,200}$/';

	/**
	 * The bare host, or '' when it is not a hostname.
	 *
	 * @param string $host A host, optionally with a scheme and a trailing slash.
	 */
	public static function host( $host ) {
		$host = strtolower( trim( (string) $host ) );
		$host = preg_replace( '#^https?://#', '', $host );
		$host = rtrim( (string) $host, '/' );
		return preg_match( self::HOST, $host ) ? $host : '';
	}

	/**
	 * A value if it matches the pattern, else ''.
	 *
	 * @param string $value   The value.
	 * @param string $pattern One of the patterns above.
	 */
	public static function valid( $value, $pattern ) {
		$value = trim( (string) $value );
		if ( self::UNIT === $pattern ) {
			$value = strtoupper( $value );
		}
		return preg_match( $pattern, $value ) ? $value : '';
	}

	/**
	 * The fields found in a pasted site tag, or null when it is not a TerraGaming Media site tag.
	 *
	 * @param string $html The pasted HTML.
	 * @return array<string, string>|null host, and when present property, publisher, in_article, article_selector.
	 */
	public static function parse( $html ) {
		$html = (string) $html;
		$out  = array();
		if ( preg_match_all( '#<script\b([^>]*)>#i', $html, $tags ) ) {
			foreach ( $tags[1] as $attrs ) {
				$a = self::attributes( $attrs );
				if ( empty( $a['src'] ) || ! preg_match( '#^https://([^/]+)/(tag|site)\.js(\?.*)?$#i', $a['src'], $m ) ) {
					continue;
				}
				$host = self::host( $m[1] );
				if ( '' === $host ) {
					continue;
				}
				$out['host'] = $host;
				$fields      = array(
					'property'         => array( 'data-tgm-property', self::PROPERTY ),
					'publisher'        => array( 'data-tgm-publisher', self::PUBLISHER ),
					'in_article'       => array( 'data-tgm-in-article', self::UNIT ),
					'article_selector' => array( 'data-tgm-article-selector', self::SELECTOR ),
				);
				foreach ( $fields as $key => $spec ) {
					$value = isset( $a[ $spec[0] ] ) ? self::valid( $a[ $spec[0] ], $spec[1] ) : '';
					if ( '' !== $value ) {
						$out[ $key ] = $value;
					}
				}
				break;
			}
		}
		// A pre-2026 inline config: window.tgmConfig={publisherId:"…",…}.
		if ( preg_match( '#tgmConfig\s*=\s*\{([^}]*)\}#', $html, $cfg ) ) {
			$legacy = array(
				'host'             => array( 'host', null ),
				'property'         => array( 'propertyId', self::PROPERTY ),
				'publisher'        => array( 'publisherId', self::PUBLISHER ),
				'in_article'       => array( 'inArticleUnitId', self::UNIT ),
				'article_selector' => array( 'articleSelector', self::SELECTOR ),
			);
			foreach ( $legacy as $key => $spec ) {
				if ( isset( $out[ $key ] ) || ! preg_match( '#\b' . $spec[0] . '\s*:\s*"([^"]*)"#', $cfg[1], $v ) ) {
					continue;
				}
				$value = null === $spec[1] ? self::host( $v[1] ) : self::valid( $v[1], $spec[1] );
				if ( '' !== $value ) {
					$out[ $key ] = $value;
				}
			}
		}
		if ( empty( $out['host'] ) ) {
			return null;
		}
		$order = array( 'host', 'property', 'publisher', 'in_article', 'article_selector' );
		return array_merge( array_intersect_key( array_flip( $order ), $out ), $out );
	}

	/**
	 * The attributes of a tag's attribute string.
	 *
	 * @param string $attrs The text between `<script` and `>`.
	 * @return array<string, string>
	 */
	private static function attributes( $attrs ) {
		$out = array();
		if ( preg_match_all( '#([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))#', $attrs, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $pair ) {
				$value                         = isset( $pair[4] ) && '' !== $pair[4] ? $pair[4] : ( isset( $pair[3] ) && '' !== $pair[3] ? $pair[3] : $pair[2] );
				$out[ strtolower( $pair[1] ) ] = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
			}
		}
		return $out;
	}
}
