#!/usr/bin/env bash
# Run on production server after git push to main.
set -euo pipefail

APP_DIR="${1:-/home/khanplac/erp.erazehan.com}"

cd "$APP_DIR"
git pull origin main
php artisan view:clear
php artisan cache:clear
php artisan config:clear

echo "Live site updated from origin/main."
