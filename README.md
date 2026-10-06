# TerraGaming Media Ads for WordPress

The WordPress plugin for [TerraGaming Media](https://terragamingmedia.com) publishers, distributed
on WordPress.org as **TerraGaming Media Ads** (`terragaming-media-ads`).

- Adds your site tag to every page (async, in the head).
- Places ad units with the **TerraGaming Media Ad** block, a widget, or `[tgm_ad unit="TGM-…"]`.
- Inserts in-article ads automatically on any theme (classic or block).
- Keeps the tag out of WP Rocket, LiteSpeed Cache, Autoptimize and SiteGround Optimizer's
  delay / combine features.

Publisher guide: https://help.terragamingmedia.com/publishers/install/wordpress/ ·
WordPress.org readme: [readme.txt](readme.txt).

## Development

Requires PHP 8.4 and Composer for the tooling (the plugin itself runs on PHP 7.4+ and WordPress 6.5+).

```bash
composer install
vendor/bin/phpunit   # unit tests (Brain Monkey)
vendor/bin/phpcs     # WordPress Coding Standards + PHPCompatibilityWP
./scripts/build.sh   # build/terragaming-media-ads.zip, from .distignore
```

Release: bump the version in `terragaming-media-ads.php` (header and `TERRAGAMING_ADS_VERSION`) and
`readme.txt` (`Stable tag`, changelog), then push a `v<version>` tag.

## Licence

MIT — see [LICENSE](LICENSE).
