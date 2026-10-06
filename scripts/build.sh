#!/usr/bin/env bash
# Builds the distributable plugin (build/terragaming-media-ads/ and its zip) from .distignore.
set -euo pipefail
cd "$(dirname "$0")/.."
rm -rf build && mkdir -p build/terragaming-media-ads
rsync -a --exclude-from=.distignore ./ build/terragaming-media-ads/
(cd build && zip -qr terragaming-media-ads.zip terragaming-media-ads)
echo "build/terragaming-media-ads.zip"
