#!/usr/bin/env bash
#
# Proefscript voor Buildora (Laravel-package, geen eigen host-app).
#
# Buildora heeft zelf geen artisan/.env/routes/serve-entrypoint, omdat het
# een package is dat je in een consumerende Laravel-app installeert. Voor
# lokaal proefdraaien gebruiken we daarom Orchestra Testbench: dat start
# een minimale Laravel-skeleton-app met deze package erin geladen (inclusief
# de eigen migraties en routes onder /buildora), zodat je zonder aparte
# host-app kunt rondkijken.
#
# Gebruik:
#   PORT=8080 .zyra/proef.sh
#
# Standaard poort is 8000 als $PORT niet gezet is.

set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

PORT="${PORT:-8000}"

echo "==> Composer-dependencies installeren"
if ! composer install --no-interaction; then
    echo "==> Normale composer install faalde, opnieuw met --ignore-platform-req=ext-gd"
    composer install --no-interaction --ignore-platform-req=ext-gd
fi

echo "==> Testbench-database (sqlite) klaarzetten"
vendor/bin/testbench workbench:create-sqlite-db --no-interaction

echo "==> Migraties draaien (inclusief Buildora's eigen migraties)"
vendor/bin/testbench migrate --no-interaction
# Let op: dit package levert geen eigen DatabaseSeeder, dus er is niets om
# te seeden. Wil je een admin-gebruiker, gebruik dan na het opstarten in een
# tweede terminal:
#   vendor/bin/testbench buildora:install
# of
#   vendor/bin/testbench buildora:user:create

echo "==> Server starten op 0.0.0.0:${PORT}"
echo "    Open na het starten: http://localhost:${PORT}/buildora/install"
exec vendor/bin/testbench serve --host=0.0.0.0 --port="${PORT}" --no-interaction
