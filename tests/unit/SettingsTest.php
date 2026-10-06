<?php
/**
 * saving the settings: a pasted site tag fills the fields; every field is validated
 * (host, PROP- / PUB- / TGM- ids, a plain CSS selector) and invalid values are dropped.
 *
 * @package TerraGaming_Ads
 */

namespace TerraGaming_Ads\Tests;

use Brain\Monkey\Functions;
use TerraGaming_Ads_Settings;

final class SettingsTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'add_settings_error' )->justReturn( null );
	}

	public function test_a_pasted_site_tag_fills_the_fields(): void {
		$saved = TerraGaming_Ads_Settings::sanitize(
			array(
				'site_tag'     => '<script async src="https://cdn.terramedia-sandbox.com/tag.js" data-tgm-property="PROP-1-1" data-tgm-publisher="PUB-1"></script>',
				'auto_article' => '1',
			)
		);
		$this->assertSame( 'cdn.terramedia-sandbox.com', $saved['host'] );
		$this->assertSame( 'PROP-1-1', $saved['property'] );
		$this->assertSame( 'PUB-1', $saved['publisher'] );
		$this->assertTrue( $saved['auto_article'] );
	}

	public function test_fields_are_validated(): void {
		$saved = TerraGaming_Ads_Settings::sanitize(
			array(
				'host'             => 'https://TGMADS.example.com/',
				'property'         => 'PROP-1-1',
				'publisher'        => 'nope',
				'in_article'       => 'TGM-HPO-INART',
				'article_selector' => '.entry-content',
				'spa'              => 'manual',
			)
		);
		$this->assertSame( 'tgmads.example.com', $saved['host'] );
		$this->assertSame( 'PROP-1-1', $saved['property'] );
		$this->assertSame( '', $saved['publisher'] );
		$this->assertSame( 'TGM-HPO-INART', $saved['in_article'] );
		$this->assertSame( '.entry-content', $saved['article_selector'] );
		$this->assertSame( 'manual', $saved['spa'] );
		$this->assertFalse( $saved['auto_article'] );
	}

	public function test_unsafe_values_are_dropped(): void {
		$saved = TerraGaming_Ads_Settings::sanitize(
			array(
				'host'             => 'evil.com"><script>',
				'article_selector' => '"><script>alert(1)</script>',
				'spa'              => 'sometimes',
			)
		);
		$this->assertSame( '', $saved['host'] );
		$this->assertSame( '', $saved['article_selector'] );
		$this->assertSame( 'auto', $saved['spa'] );
	}
}
