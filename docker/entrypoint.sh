#!/bin/sh
set -e

cd /app

# Named volume /app/vendor always exists as a directory — check autoload, not the folder.
if [ ! -f vendor/autoload.php ]; then
  echo "📦 composer install..."
  composer install --prefer-dist --no-progress --no-interaction
fi

if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
  echo "🔑 Generating JWT keypair..."
  mkdir -p config/jwt
  php bin/console lexik:jwt:generate-keypair --overwrite --no-interaction || true
fi

mkdir -p var/cache var/log var/share public/media
chmod -R ug+rwX var public/media || true

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  echo "⚙️  Waiting for database..."
  ATTEMPTS=30
  until php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1 || [ $ATTEMPTS -eq 0 ]; do
    ATTEMPTS=$((ATTEMPTS - 1))
    sleep 2
  done

  if [ $ATTEMPTS -gt 0 ]; then
    echo "⚙️  Running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true
  else
    echo "⚠️  Database not ready — skipping migrations"
  fi
fi

exec "$@"
