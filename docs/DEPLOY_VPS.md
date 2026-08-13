# Déploiement VPS — Arca (dev)

Stack Docker derrière **Nginx hôte** (ports 80/443 déjà pris).

| Service | Conteneur | Accès hôte |
|---------|-----------|------------|
| API (FrankenPHP) | `php` | `127.0.0.1:8081` |
| MySQL 8 | `database` | `127.0.0.1:3308` |
| Messenger | `messenger` | — |
| Mailpit | `mailer` | UI `127.0.0.1:8026` |

Domaine public : `api.arca.digisafrica.tech` → Nginx → `127.0.0.1:8081`

---

## 1. Sur le VPS (une fois)

```bash
ssh digis@141.136.42.36
sudo mkdir -p /var/www && sudo chown digis:digis /var/www
cd /var/www
git clone <URL_DU_REPO> arca
cd arca

cp .env.vps.dist .env.vps
nano .env.vps   # changer APP_SECRET, mots de passe MySQL, JWT_PASSPHRASE
```

### Nginx reverse proxy

```bash
sudo cp docker/nginx/api.arca.digisafrica.tech.conf /etc/nginx/sites-available/
sudo ln -sf /etc/nginx/sites-available/api.arca.digisafrica.tech.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### HTTPS (Certbot)

```bash
sudo certbot --nginx -d api.arca.digisafrica.tech
```

---

## 2. Lancer la stack

```bash
chmod +x deploy-vps.sh docker/entrypoint.sh
./deploy-vps.sh
```

Équivalent manuel :

```bash
docker compose -f compose.vps.yaml --env-file .env.vps up -d --build
```

Vérifications :

```bash
curl -I http://127.0.0.1:8081/api
curl -I https://api.arca.digisafrica.tech/api
docker compose -f compose.vps.yaml --env-file .env.vps logs -f php
```

---

## 3. Commandes utiles

```bash
# Shell dans le conteneur
docker compose -f compose.vps.yaml --env-file .env.vps exec php bash

# Migrations manuelles
docker compose -f compose.vps.yaml --env-file .env.vps exec php \
  php bin/console doctrine:migrations:migrate --no-interaction

# Seed échelle sanctions
docker compose -f compose.vps.yaml --env-file .env.vps exec php \
  php bin/console app:seed:sanction-scales

# Redéployer après git pull
./deploy-vps.sh
```

---

## 4. Pourquoi ces ports ?

| Port | Statut |
|------|--------|
| 80 / 443 | Nginx hôte (reverse proxy) |
| 8080 / 3307 | Déjà pris par d’autres conteneurs |
| **8081** | API Arca (bind localhost) |
| **3308** | MySQL Arca (bind localhost) |
| **8026** | Mailpit UI |

Rien n’est exposé publiquement hors Nginx.

---

## 5. Fichiers ajoutés

- `Dockerfile` — FrankenPHP PHP 8.4
- `compose.vps.yaml` — stack VPS
- `docker/Caddyfile`, `docker/entrypoint.sh`, `docker/php.ini`
- `docker/nginx/api.arca.digisafrica.tech.conf`
- `.env.vps.dist` → copier en `.env.vps` (ne pas committer)
- `deploy-vps.sh`

> Note : le `compose.yaml` Symfony Flex (PostgreSQL) reste pour le local Flex ; le VPS utilise **MySQL 8**, aligné avec les migrations actuelles.
