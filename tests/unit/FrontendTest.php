<?php
/**
 * what the plugin puts on the page:
 * - the site tag's attributes (async, the host, the ids on the fallback host, the article
 *   selector — `[data-tgm-article]` when automatic in-article is on, unless a custom one is set);
 * - the `[tgm_ad unit="…"]` shortcode (and the block / widget renderer): one placement div, ids
 *   validated, the slot type optional;
 * - the automatic in-article container around the post content, in the main query's loop only.
 *
 * @package TerraGaming_Ads
 */

namespace TerraGaming_Ads\Tests;

use Brain\Monkey\Functions;
use TerraGaming_Ads_Frontend;
use TerraGaming_Ads_Settings;

final class FrontendTest extends TestCase {
	private function settings( array $values ): void {
		$this->options[ TerraGaming_Ads_Settings::OPTION ] = array_merge( TerraGaming_Ads_Settings::defaults(), $values );
	}

	public function test_site_tag_attributes_on_the_verified_host(): void {
		$this->settings( array( 'host' => 'tgmads.example.com', 'auto_article' => false ) );
		$this->assertSame(
			array( 'src' => 'https://tgmads.example.com/tag.js' ),
			TerraGaming_Ads_Frontend::script_attributes()
		);
	}

	public function test_site_tag_attributes_on_the_fallback_host_with_automatic_in_article(): void {
		$this->settings(
			array(
				'host'         => 'cdn.terramedia-sandbox.com',
				'property'     => 'PROP-1-1',
				'publisher'    => 'PUB-1',
				'in_article'   => 'TGM-HPO-INART',
				'auto_article' => true,
				'spa'          => 'manual',
			)
		);
		$this->assertSame(
			array(
				'src'                       => 'https://cdn.terramedia-sandbox.com/tag.js',
				'data-tgm-property'         => 'PROP-1-1',
				'data-tgm-publisher'        => 'PUB-1',
				'data-tgm-in-article'       => 'TGM-HPO-INART',
				'data-tgm-article-selector' => '[data-tgm-article]',
				'data-tgm-spa'              => 'manual',
			),
			TerraGaming_Ads_Frontend::script_attributes()
		);
	}

	public function test_a_custom_article_selector_wins(): void {
		$this->settings(
			array(
				'host'             => 'tgmads.example.com',
				'auto_article'     => true,
				'article_selector' => 'article .entry-content',
			)
		);
		$this->assertSame(
			'article .entry-content',
			TerraGaming_Ads_Frontend::script_attributes()['data-tgm-article-selector']
		);
	}

	public function test_no_host_no_tag(): void {
		$this->settings( array() );
		$this->assertNull( TerraGaming_Ads_Frontend::script_attributes() );
	}

	public function test_shortcode(): void {
		$this->assertSame(
			'<div class="tgm-ad" data-tgm-unit="TGM-HPO-SBR01"></div>',
			TerraGaming_Ads_Frontend::shortcode( array( 'unit' => 'TGM-HPO-SBR01' ) )
		);
		$this->assertSame(
			'<div class="tgm-ad" data-tgm-unit="TGM-HPO-FLT01" data-tgm-slot="floating"></div>',
			TerraGaming_Ads_Frontend::shortcode( array( 'unit' => 'tgm-hpo-flt01', 'slot' => 'floating' ) )
		);
		$this->assertSame( '', TerraGaming_Ads_Frontend::shortcode( array( 'unit' => '"><script>' ) ) );
		$this->assertSame( '', TerraGaming_Ads_Frontend::shortcode( array() ) );
	}

	public function test_in_article_container_in_the_main_loop_only(): void {
		$this->settings( array( 'host' => 'tgmads.example.com', 'auto_article' => true ) );
		Functions\when( 'is_singular' )->justReturn( true );
		Functions\when( 'in_the_loop' )->justReturn( true );
		Functions\when( 'is_main_query' )->justReturn( true );
		Functions\when( 'wp_is_block_theme' )->justReturn( false );
		Functions\when( 'get_queried_object_id' )->justReturn( 42 );
		Functions\when( 'get_the_ID' )->justReturn( 42 );
		$this->assertSame(
			'<div data-tgm-article><p>One</p></div>',
			TerraGaming_Ads_Frontend::article_container( '<p>One</p>' )
		);
		Functions\when( 'in_the_loop' )->justReturn( false );
		$this->assertSame( '<p>One</p>', TerraGaming_Ads_Frontend::article_container( '<p>One</p>' ) );
	}

	public function test_in_article_container_in_a_block_theme(): void {
		// Block themes render the post through core/post-content, outside the classic loop.
		$this->settings( array( 'host' => 'tgmads.example.com', 'auto_article' => true ) );
		Functions\when( 'is_singular' )->justReturn( true );
		Functions\when( 'in_the_loop' )->justReturn( false );
		Functions\when( 'is_main_query' )->justReturn( true );
		Functions\when( 'wp_is_block_theme' )->justReturn( true );
		Functions\when( 'get_queried_object_id' )->justReturn( 42 );
		Functions\when( 'get_the_ID' )->justReturn( 42 );
		$this->assertSame(
			'<div data-tgm-article><p>One</p></div>',
			TerraGaming_Ads_Frontend::article_container( '<p>One</p>' )
		);
		// Another post's content on the page (a query loop, related posts): not the article.
		Functions\when( 'get_the_ID' )->justReturn( 7 );
		$this->assertSame( '<p>One</p>', TerraGaming_Ads_Frontend::article_container( '<p>One</p>' ) );
	}

	public function test_in_article_container_off(): void {
		$this->settings( array( 'host' => 'tgmads.example.com', 'auto_article' => false ) );
		Functions\when( 'is_singular' )->justReturn( true );
		Functions\when( 'in_the_loop' )->justReturn( true );
		Functions\when( 'is_main_query' )->justReturn( true );
		$this->assertSame( '<p>One</p>', TerraGaming_Ads_Frontend::article_container( '<p>One</p>' ) );
	}
}
