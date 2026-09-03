#!/usr/bin/env bash
#
# Proefdraaien van Buildora.
#
# Buildora is een Laravel-package (composer library): geen artisan, geen
# public/index.php en geen .env. Dit script installeert de dependencies
# en draait de automatische testsuite, en serveert het testrapport op
# poort $PORT zodat je in de browser kunt zien wat er groen en rood is.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_DIR"

PORT="${PORT:-8000}"

echo "-> composer install"
if ! composer install --no-interaction --prefer-dist; then
    echo "composer install is mislukt, nieuwe poging zonder platform-checks..."
    composer install --no-interaction --prefer-dist --ignore-platform-reqs
fi

REPORT_DIR="$(mktemp -d)"

echo "-> vendor/bin/phpunit --testdox"
set +e
vendor/bin/phpunit --testdox >"$REPORT_DIR/phpunit.txt" 2>&1
PHPUNIT_EXIT=$?
set -e

cat "$REPORT_DIR/phpunit.txt"
echo "phpunit exitcode: ${PHPUNIT_EXIT}"

printf '<!doctype html><meta charset="utf-8"><title>Buildora - testrapport</title><pre>%s</pre>' \
    "$(sed -e 's/&/\&amp;/g' -e 's/</\&lt;/g' -e 's/>/\&gt;/g' "$REPORT_DIR/phpunit.txt")" \
    >"$REPORT_DIR/index.html"

echo
echo "== Testrapport wordt geserveerd op http://0.0.0.0:${PORT}/ (Ctrl+C om te stoppen) =="
exec php -S 0.0.0.0:"${PORT}" -t "$REPORT_DIR"
