# CI / CD GitHub Actions

## Pipelines

| Workflow | Quand | Quoi |
|----------|--------|------|
| **Tests** | PR + push | `composer install` + PHPUnit (PHP 8.4) |
| **Deploy VPS** | push `main` (après tests verts) ou *Run workflow* sur `main` | SSH → `git pull` → `docker compose up` → migrations |

Fichier : `.github/workflows/ci.yml`

---

## Secrets GitHub (Settings → Secrets and variables → Actions)

| Secret | Exemple |
|--------|---------|
| `VPS_HOST` | `141.136.42.36` |
| `VPS_USER` | `digis` |
| `VPS_SSH_KEY` | clé **privée** (voir ci-dessous) |
| `VPS_PATH` | `/var/www/arca` |
| `VPS_PORT` | `22` (optionnel) |

---

## 1. Clé SSH dédiée (une fois)

**Sur ta machine locale :**

```bash
ssh-keygen -t ed25519 -C "github-actions-arca" -f ~/.ssh/arca_gha -N ""
```

**Sur le VPS** — autoriser la clé publique :

```bash
ssh digis@141.136.42.36
mkdir -p ~/.ssh && chmod 700 ~/.ssh
echo "<contenu de arca_gha.pub>" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

**Dans GitHub :** secret `VPS_SSH_KEY` = contenu **intégral** de `~/.ssh/arca_gha` (le privé, y compris `BEGIN/END`).

Vérifie en local avant de relancer Actions :

```bash
ssh -i ~/.ssh/arca_gha -o IdentitiesOnly=yes digis@141.136.42.36 'whoami && hostname'
```

Doit afficher `digis`. Si ça échoue, la clé n’est pas dans `authorized_keys`.

Le VPS continue d’utiliser **sa propre** clé pour `git pull` vers GitHub (déjà configurée).

---

## 2. Premier run

1. Pousse `.github/workflows/ci.yml` sur `main` (ou ouvre une PR pour ne tester que la CI).
2. Onglet **Actions** → workflow *CI / CD*.
3. Un push sur `main` vert déclenche le deploy.

Déploiement manuel : Actions → CI / CD → **Run workflow** (branche `main`).

---

## 3. Erreur `ssh: unable to authenticate`

Causes fréquentes :

1. **Clé publique absente sur le VPS** — colle `arca_gha.pub` dans `/home/digis/.ssh/authorized_keys` (user `digis`, pas root).
2. **Mauvais secret** — `VPS_SSH_KEY` doit être la clé **privée** (`arca_gha`), pas `.pub`.
3. **Sauts de ligne perdus** — recopie le fichier entier :

```bash
# macOS
pbcopy < ~/.ssh/arca_gha
```

Le secret doit commencer par `-----BEGIN OPENSSH PRIVATE KEY-----` et finir par `-----END OPENSSH PRIVATE KEY-----` (plusieurs lignes).
4. **Passphrase** — la clé a été créée avec `-N ""` (vide). Si tu as mis une passphrase, GitHub ne pourra pas l’utiliser (sauf secret `passphrase`).

