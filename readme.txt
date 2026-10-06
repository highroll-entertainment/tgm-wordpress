=== TerraGaming Media Ads ===
Contributors: terragamingmedia
Tags: ads, advertising, monetization, ad network, casino
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Show TerraGaming Media ads on your site: your site tag on every page, ad units with a block, a widget or a shortcode, and automatic in-article ads.

== Description ==

TerraGaming Media is an ad network for poker and casino publishers. This plugin connects your WordPress site to your TerraGaming Media publisher account.

* **Site tag on every page.** Paste the site tag from the TerraGaming Media portal once. The plugin adds it to the head of every page, asynchronously, so it never slows down your content.
* **Ad units anywhere.** Use the *TerraGaming Media Ad* block, the classic widget, or the `[tgm_ad unit="TGM-…"]` shortcode. A unit can appear on many pages, and more than once per page.
* **In-article ads, automatically.** The plugin marks your post content, so in-article ads find the paragraphs on any theme: up to three per article, never two on screen at once.
* **Works with caching plugins.** The tag is excluded from WP Rocket, LiteSpeed Cache, Autoptimize and SiteGround Optimizer's delay and combine features.
* **Respects privacy choices.** With Global Privacy Control, or without the visitor's consent in your consent manager (c15t, IAB TCF, OneTrust, Termly), no ad is requested.

You need a TerraGaming Media publisher account with an approved property. Sign up at https://terragamingmedia.com.

== Installation ==

1. Install and activate the plugin from **Plugins → Add New**.
2. In the TerraGaming Media portal, open **Ad Units & Tags → Install site tag** and copy the site tag.
3. In WordPress, open **Settings → TerraGaming Media**, paste the site tag and save.
4. Add ad units with the *TerraGaming Media Ad* block, the widget or the shortcode, using the unit IDs from the portal.

Full guide: https://help.terragamingmedia.com/publishers/install/wordpress/

== Frequently Asked Questions ==

= Do I add the site tag once per ad unit? =

No. The site tag goes on every page once, and the plugin does that for you. Each ad unit is just a block, a widget or a shortcode.

= Where do in-article ads appear? =

After the 3rd paragraph of a post (or the 5th when the first paragraphs are short), then every 6 paragraphs, at most three per post. Untick the automatic option in the settings to use the article selector from the portal instead.

= How do I check that it works? =

Open a post and run `window.tgm.status()` in your browser's console. Within about ten minutes the portal shows "Tag detected" for your property.

== External services ==

This plugin loads TerraGaming Media's ad tag (`tag.js`) from your TerraGaming Media tag host (your own `tgmads.` subdomain once verified, otherwise TerraGaming Media's shared host `cdn.terramedia-sandbox.com`). It does this on every public page where a tag host is configured.

When a visitor has allowed advertising, the tag sends the page address, the referrer, the screen size, language and time zone, and the ad placements on the page to the tag host, to choose and show ads. TerraGaming Media also receives the visitor's IP address, which it uses for an approximate location. The tag sets one first-party cookie, `_tgm_vid`, on the tag host for frequency capping and local geofencing. With Global Privacy Control, or without consent, nothing is requested.

The service is provided by TerraGaming Media: Terms of Service https://terragamingmedia.com/legal/terms, Privacy Policy https://terragamingmedia.com/legal/privacy.

== Changelog ==

= 0.1.0 =
* First release: site tag, ad unit block, widget and shortcode, automatic in-article ads, cache plugin exclusions.
