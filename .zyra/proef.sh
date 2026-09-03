#!/usr/bin/env bash
#
# Proefdraaien van Buildora.
#
# Buildora is een Laravel-package (composer library), geen standalone
# Laravel-applicatie: er is geen artisan, geen public/index.php en geen
# .env. Dit script installeert daarom de dependencies, draait de
# automatische testsuite (inclusief de reproductietests voor issue #143
# in tests/Unit/Fields/CurrencyFieldTest.php) en serveert een leesbaar
# testrapport op poort $PORT, zodat je in de browser kunt zien wat er
# groen en rood is.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_DIR"

PORT="${PORT:-8000}"

echo "== Buildora - proefdraaien =="
echo "Repository: $REPO_DIR"
echo

echo "-> composer install"
if ! composer install --no-interaction --prefer-dist; then
    echo
    echo "composer install zonder extra vlaggen is mislukt (waarschijnlijk"
    echo "een ontbrekende PHP-extensie zoals ext-gd voor maatwebsite/excel)."
    echo "Nieuwe poging met --ignore-platform-req=ext-gd ..."
    echo
    composer install --no-interaction --prefer-dist --ignore-platform-req=ext-gd
fi

REPORT_DIR="$(mktemp -d)"
REPORT_HTML="$REPORT_DIR/index.html"

echo
echo "-> vendor/bin/phpunit --testdox"
set +e
vendor/bin/phpunit --testdox >"$REPORT_DIR/phpunit.txt" 2>&1
PHPUNIT_EXIT=$?
set -e

cat "$REPORT_DIR/phpunit.txt"

{
    echo "<!DOCTYPE html><html lang=\"nl\"><head><meta charset=\"utf-8\">"
    echo "<title>Buildora - testrapport (issue #143)</title>"
    echo "<style>body{font-family:ui-monospace,Consolas,monospace;background:#111;color:#eee;padding:2rem}"
    echo "pre{white-space:pre-wrap} h1{color:#fff} .exit-ok{color:#7CFC00} .exit-fail{color:#FF6B6B}</style>"
    echo "</head><body>"
    echo "<h1>Buildora - testrapport</h1>"
    echo "<p>Bevat o.a. de reproductietests voor <strong>issue #143</strong>"
    echo "(CurrencyField + string-waarden) in"
    echo "<code>tests/Unit/Fields/CurrencyFieldTest.php</code>.</p>"
    if [ "$PHPUNIT_EXIT" -eq 0 ]; then
        echo "<p class=\"exit-ok\">phpunit exitcode: ${PHPUNIT_EXIT} (alle tests groen)</p>"
    else
        echo "<p class=\"exit-fail\">phpunit exitcode: ${PHPUNIT_EXIT} (er zijn falende/rode tests"
        echo "&mdash; dat is voor issue #143 bewust: die tests tonen de huidige crash aan)</p>"
    fi
    echo "<pre>"
    sed -e 's/&/\&amp;/g' -e 's/</\&lt;/g' -e 's/>/\&gt;/g' "$REPORT_DIR/phpunit.txt"
    echo "</pre></body></html>"
} >"$REPORT_HTML"

echo
echo "== Testrapport wordt geserveerd op http://0.0.0.0:${PORT}/ =="
echo "(Ctrl+C om te stoppen)"

exec php -S 0.0.0.0:"${PORT}" -t "$REPORT_DIR"
