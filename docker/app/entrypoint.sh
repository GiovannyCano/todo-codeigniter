#!/bin/sh
set -e

APPDIR="/var/www/html"
SEED_CLASS_DEFAULT="App\\Database\\Seeds\\TaskSeeder"
SEED_TABLE_DEFAULT="${SEED_TABLE:-tasks}"

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

mkdir -p "$APPDIR/writable"
chown -R www-data:www-data "$APPDIR/writable" 2>/dev/null || true

if [ "${MIGRATE_ON_BOOT:-1}" = "1" ]; then
  echo ">> Running spark migrate (App)…"
  php "$APPDIR/spark" migrate -n App || true
fi

if [ "${SEED_ON_BOOT:-1}" = "1" ]; then
  if [ "${ALWAYS_RESEED:-0}" = "1" ]; then
    do_seed=1
    cnt="FORCED"
  else
    cnt=$(php -r '
      $h = getenv("DB_HOST") ?: "db";
      $u = getenv("DB_USER") ?: "app";
      $p = getenv("DB_PASS") ?: "app";
      $d = getenv("DB_NAME") ?: "todo";
      $t = getenv("SEED_TABLE") ?: "tasks";
      try {
        $pdo = new PDO("mysql:host=".$h.";dbname=".$d.";port=3306;charset=utf8mb4", $u, $p, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $exists = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t))->fetchColumn() !== false;
        if (!$exists) { echo "MISSING"; exit; }
        echo (string) $pdo->query("SELECT COUNT(*) FROM `".$t."`")->fetchColumn();
      } catch (Throwable $e) { echo "ERR"; }
    ')
    if [ "$cnt" = "MISSING" ] || [ "$cnt" = "0" ] || [ "$cnt" = "ERR" ]; then
      do_seed=1
    else
      do_seed=0
    fi
  fi

  if [ "${do_seed:-0}" = "1" ]; then
    cls="${SEED_CLASS:-$SEED_CLASS_DEFAULT}"
    echo ">> Seeding with: $cls (table=${SEED_TABLE:-$SEED_TABLE_DEFAULT}, count=$cnt)"
    php "$APPDIR/spark" db:seed "$cls" || true
  else
    echo ">> Seed skipped: table ${SEED_TABLE:-$SEED_TABLE_DEFAULT} has rows (count=$cnt)."
  fi
fi

echo ">> CI Boot: done. Starting PHP-FPM…"
exec "$@"
