<?php
/**
 * Unit test bootstrap: WordPress functions are mocked with Brain Monkey (no database, no core).
 *
 * @package TerraGaming_Ads
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

define( 'ABSPATH', __DIR__ . '/' );
define( 'TERRAGAMING_ADS_TESTING', true );

require_once dirname( __DIR__ ) . '/terragaming-media-ads.php';
require_once __DIR__ . '/unit/TestCase.php';
