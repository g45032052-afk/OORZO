#!/usr/bin/env bash
#
# Build the Builder addon's Vite bundle and package it into
# Modules/Builder/resources/dist/build.zip — replacing any previous zip.
#
# `npm run build:prod` now runs the packaging step itself (via
# `php artisan prepare:builder-bundle`), which writes the zip to the lowercase
# resources/dist path AddonController reads on activation. After this, zip
# Modules/Builder/ for distribution.
#
# Prerequisites: bash, npm + node. (No `zip` binary — packaging is pure PHP.)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

DIST_ZIP="Modules/Builder/resources/dist/build.zip"

echo "[1/1] Production build + bundle package (npm run build:prod)"
npm run build:prod

if [[ ! -f "$DIST_ZIP" ]]; then
    echo "[error] $DIST_ZIP was not produced — is Modules/Builder present?"
    exit 1
fi

echo
echo "Done."
echo "  Output: $DIST_ZIP"
echo "  Size:   $(du -h "$DIST_ZIP" | awk '{print $1}')"
echo "Ready to zip Modules/Builder/ for distribution."
