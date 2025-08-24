#!/bin/sh
set -e

APPDIR="/var/www/html"

echo ">> CI Boot: starting…"

if [ ! -f "$APPDIR/composer.json" ]; then
  echo ">> Installing CodeIgniter (appstarter) into $APPDIR …"
  composer create-project codeigniter4/appstarter "$APPDIR"
fi

if [ ! -d "$APPDIR/vendor" ]; then
  echo ">> Installing composer dependencies…"
  composer install --no-interaction --prefer-dist -d "$APPDIR"
fi

if [ ! -f "$APPDIR/.env" ] && [ -f "$APPDIR/env" ]; then
  cp "$APPDIR/env" "$APPDIR/.env"
  sed -i'' -e "s/# CI_ENVIRONMENT = production/CI_ENVIRONMENT = development/" "$APPDIR/.env" || true
fi

if ! grep -q "^app.baseURL" "$APPDIR/.env" 2>/dev/null; then
cat >> "$APPDIR/.env" <<'EOF'

# --- Added by container bootstrap ---
app.baseURL = 'http://localhost:8080/'

database.default.hostname = db
database.default.database = todo
database.default.username = app
database.default.password = app
database.default.DBDriver = MySQLi
database.default.port     = 3306
EOF
fi

chown -R www-data:www-data "$APPDIR/writable" 2>/dev/null || true

if [ "${MIGRATE_ON_BOOT:-0}" = "1" ]; then
  echo ">> Running spark migrate (App)…"
  php "$APPDIR/spark" migrate -n App || true
fi

echo ">> CI Boot: done. Starting PHP-FPM…"
exec "$@"
