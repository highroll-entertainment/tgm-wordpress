<?php
/**
 * "Paste your site tag": the plugin reads the host and ids from what the portal shows.
 * - the one-line tag (verified CNAME host): the host only;
 * - the fallback host's tag: the host and its data-tgm-* attributes;
 * - a pre-2026 inline `window.tgmConfig` snippet: its host and ids (migration);
 * - anything else: nothing, so the settings page can say so.
 *
 * @package TerraGaming_Ads
 */

namespace TerraGaming_Ads\Tests;

use TerraGaming_Ads_Site_Tag;

final class SiteTagTest extends TestCase {
	public function test_one_line_site_tag(): void {
		$this->assertSame(
			array( 'host' => 'tgmads.example.com' ),
			TerraGaming_Ads_Site_Tag::parse(
				"<!-- TerraGaming Media site tag: Example. Add it once, in the <head> of every page. -->\n" .
				'<script async src="https://tgmads.example.com/tag.js"></script>'
			)
		);
	}

	public function test_fallback_host_with_data_attributes(): void {
		$this->assertSame(
			array(
				'host'             => 'cdn.terramedia-sandbox.com',
				'property'         => 'PROP-1-1',
				'publisher'        => 'PUB-1',
				'in_article'       => 'TGM-HPO-INART',
				'article_selector' => '.post-content',
			),
			TerraGaming_Ads_Site_Tag::parse(
				'<script async src="https://cdn.terramedia-sandbox.com/tag.js" data-tgm-property="PROP-1-1" data-tgm-publisher="PUB-1" data-tgm-in-article="TGM-HPO-INART" data-tgm-article-selector=".post-content"></script>'
			)
		);
	}

	public function test_legacy_inline_config(): void {
		$this->assertSame(
			array(
				'host'             => 'tgmads.highrollpoker.com',
				'property'         => 'PROP-1-1',
				'publisher'        => 'PUB-1',
				'in_article'       => 'TGM-HPO-INART',
				'article_selector' => '.post-content',
			),
			TerraGaming_Ads_Site_Tag::parse(
				'<!-- TerraGaming Media Site Tag: Highroll Poker --><script>window.tgmConfig={publisherId:"PUB-1",propertyId:"PROP-1-1",articleSelector:".post-content",inArticleUnitId:"TGM-HPO-INART",host:"tgmads.highrollpoker.com"};</script><script async src="https://tgmads.highrollpoker.com/tag.js"></script>'
			)
		);
	}

	public function test_rejects_anything_else(): void {
		$this->assertNull( TerraGaming_Ads_Site_Tag::parse( '' ) );
		$this->assertNull( TerraGaming_Ads_Site_Tag::parse( '<script src="https://evil.example/x.js"></script>' ) );
		$this->assertNull( TerraGaming_Ads_Site_Tag::parse( '<script src="javascript:alert(1)//tag.js"></script>' ) );
	}

	public function test_ignores_invalid_ids(): void {
		$this->assertSame(
			array( 'host' => 'tgmads.example.com' ),
			TerraGaming_Ads_Site_Tag::parse(
				'<script async src="https://tgmads.example.com/tag.js" data-tgm-property="1 OR 1=1" data-tgm-in-article="<b>"></script>'
			)
		);
	}
}
