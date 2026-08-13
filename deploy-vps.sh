#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT_DIR"

COMPOSE=(docker compose -f compose.vps.yaml --env-file .env.vps)

if [ ! -f .env.vps ]; then
  echo "❌ Missing .env.vps — copy from .env.vps.dist and edit secrets:"
  echo "   cp .env.vps.dist .env.vps"
  exit 1
fi

echo "🚚 Pulling latest code (if git remote configured)..."
git pull --ff-only || true

echo "🏗️  Building & starting stack..."
"${COMPOSE[@]}" up -d --build

echo "⏳ Waiting for php container..."
sleep 5

echo "🌱 Optional seeds (profiles / sanction scales)..."
"${COMPOSE[@]}" exec -T php php bin/console app:seed:sanction-scales || true

echo "✅ Stack status:"
"${COMPOSE[@]}" ps

echo
echo "API (local):  http://127.0.0.1:8081"
echo "Public DNS:   https://api.arca.digisafrica.tech  (via Nginx hôte)"
echo "Mailpit UI:   http://127.0.0.1:8026"
echo "MySQL:        127.0.0.1:3308"
