<?php
/**
 * the WordPress.org release metadata agrees: the plugin header's Version, readme.txt's
 * Stable tag and the TERRAGAMING_ADS_VERSION constant; readme.txt discloses the external service.
 *
 * @package TerraGaming_Ads
 */

namespace TerraGaming_Ads\Tests;

final class ReleaseTest extends TestCase {
	public function test_versions_agree(): void {
		$root   = dirname( __DIR__, 2 );
		$plugin = file_get_contents( $root . '/terragaming-media-ads.php' );
		$readme = file_get_contents( $root . '/readme.txt' );
		$this->assertSame( 1, preg_match( '/^\s*\*\s*Version:\s*(\S+)/m', $plugin, $v ) );
		$this->assertSame( 1, preg_match( '/^Stable tag:\s*(\S+)/m', $readme, $s ) );
		$this->assertSame( $v[1], $s[1] );
		$this->assertSame( $v[1], TERRAGAMING_ADS_VERSION );
	}

	public function test_readme_discloses_the_external_service(): void {
		$readme = file_get_contents( dirname( __DIR__, 2 ) . '/readme.txt' );
		$this->assertStringContainsString( '== External services ==', $readme );
		$this->assertStringContainsString( 'tag.js', $readme );
		$this->assertStringContainsString( 'https://terragamingmedia.com/legal/privacy', $readme );
	}
}
