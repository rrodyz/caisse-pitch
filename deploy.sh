#!/usr/bin/env bash
# deploy.sh — Script de déploiement production pour Caisse Pitch
# Usage: bash deploy.sh [--skip-tests] [--no-down]
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

SKIP_TESTS=false
NO_DOWN=false

for arg in "$@"; do
    case $arg in
        --skip-tests) SKIP_TESTS=true ;;
        --no-down)    NO_DOWN=true ;;
    esac
done

echo "────────────────────────────────────────────"
echo " Caisse Pitch — Déploiement $(date '+%Y-%m-%d %H:%M:%S')"
echo "────────────────────────────────────────────"

# ── 1. Vérifications préalables ──────────────────────────────────────────────
echo "[1/9] Vérification de l'environnement..."

if [ ! -f ".env" ]; then
    echo "ERREUR : fichier .env introuvable." >&2
    exit 1
fi

APP_ENV=$(grep -E '^APP_ENV=' .env | cut -d'=' -f2 | tr -d '"')
if [ "$APP_ENV" != "production" ]; then
    echo "ERREUR : APP_ENV=$APP_ENV — ce script est réservé à la production." >&2
    exit 1
fi

APP_DEBUG=$(grep -E '^APP_DEBUG=' .env | cut -d'=' -f2 | tr -d '"')
if [ "$APP_DEBUG" = "true" ]; then
    echo "AVERTISSEMENT : APP_DEBUG=true en production !" >&2
fi

# ── 2. Maintenance ───────────────────────────────────────────────────────────
if [ "$NO_DOWN" = false ]; then
    echo "[2/9] Activation du mode maintenance..."
    php artisan down --retry=60 --secret="deploy-$(date +%s)" 2>/dev/null || true
fi

# ── 3. Git pull ──────────────────────────────────────────────────────────────
echo "[3/9] Récupération du code..."
git pull origin main --no-rebase

# ── 4. Composer ──────────────────────────────────────────────────────────────
echo "[4/9] Installation des dépendances PHP..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet

# ── 5. Tests (optionnel) ─────────────────────────────────────────────────────
if [ "$SKIP_TESTS" = false ]; then
    echo "[5/9] Exécution de la suite de tests..."
    php artisan test --env=testing
else
    echo "[5/9] Tests ignorés (--skip-tests)."
fi

# ── 6. Assets ────────────────────────────────────────────────────────────────
echo "[6/9] Compilation des assets..."
npm ci --silent
npm run build

# Supprimer le fichier hot pour éviter de servir les assets dev
rm -f public/hot

# ── 7. Base de données ───────────────────────────────────────────────────────
echo "[7/9] Migrations de la base de données..."
php artisan migrate --force --no-interaction

# ── 8. Optimisation ──────────────────────────────────────────────────────────
echo "[8/9] Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache 2>/dev/null || true
php artisan event:cache 2>/dev/null || true

# ── 9. Fin de maintenance ────────────────────────────────────────────────────
if [ "$NO_DOWN" = false ]; then
    echo "[9/9] Désactivation du mode maintenance..."
    php artisan up
fi

echo ""
echo "✓ Déploiement terminé avec succès."

# Audit rapide post-déploiement
echo ""
echo "── Audit de sécurité post-déploiement ──"
php artisan security:audit || true
