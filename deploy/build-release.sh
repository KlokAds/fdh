#!/usr/bin/env bash
# Builds the ready-to-run website for shared hosting: production PHP packages (no dev tools) and the
# built CSS/JS, so the server needs no Composer, Node.js or SSH. Run from the repository root (or in CI).
#   bash deploy/build-release.sh --dir <folder>   → built files in <folder> (CI commits them to the "release" branch)
#   bash deploy/build-release.sh                  → dist/fdh-release.zip to upload by hand
set -euo pipefail
OUT_DIR=""
[ "${1:-}" = "--dir" ] && OUT_DIR="${2:?--dir needs a folder}"

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

echo "→ Copying the tracked source…"
git -C "$ROOT" archive --format=tar HEAD | tar -x -C "$WORK/"
cd "$WORK"

echo "→ PHP packages (production only)…"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
find vendor -type d \( -name .git -o -name .github -o -name tests -o -name docs \) -prune -exec rm -rf {} +

echo "→ Styles and scripts…"
if [ -d "$ROOT/node_modules" ] && [ -z "${CI:-}" ]; then
    ln -s "$ROOT/node_modules" node_modules
else
    npm ci --no-audit --no-fund
fi
npm run build
rm -rf node_modules public/hot bootstrap/cache/*.php tests .github phpunit.xml

# Which source commit this build is: shown in Admin → System update.
SHA="$(git -C "$ROOT" rev-parse HEAD)"
SUBJECT="$(git -C "$ROOT" log -1 --format=%s)"
printf '%s\n%s\n%s\n' "$SHA" "$(date -u +%Y-%m-%dT%H:%M:%SZ)" "$SUBJECT" > REVISION

if [ -n "$OUT_DIR" ]; then
    mkdir -p "$OUT_DIR"
    find "$OUT_DIR" -mindepth 1 -maxdepth 1 ! -name .git -exec rm -rf {} +
    cp -a . "$OUT_DIR/"
    cp "$ROOT/deploy/release.gitignore" "$OUT_DIR/.gitignore"
    echo "Built into $OUT_DIR"
    exit 0
fi

echo "→ Zipping…"
mkdir -p "$ROOT/dist"
rm -f "$ROOT/dist/fdh-release.zip"
zip -qr "$ROOT/dist/fdh-release.zip" . -x "*.DS_Store"
du -h "$ROOT/dist/fdh-release.zip"
