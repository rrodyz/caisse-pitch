#!/usr/bin/env bash
# =============================================================================
# Caisse Pitch — Script de déploiement n0c.com
# Usage :
#   ./deploy.sh           → mise à jour (git pull + migrate + cache)
#   ./deploy.sh --fresh   → première installation complète
# =============================================================================

set -euo pipefail

# ── Config ────────────────────────────────────────────────────────────────────
APP_DIR="/home/htegyeahqn/caisse-pitch"
PHP="php"          # ajuster si n0c.com impose php8.2 ou php82
COMPOSER="composer"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✓${NC} $1"; }
warn() { echo -e "${YELLOW}⚠${NC}  $1"; }
die()  { echo -e "${RED}✗ ERREUR${NC}: $1"; exit 1; }

FRESH=false
[[ "${1:-}" == "--fresh" ]] && FRESH=true

# ── Vérifications préliminaires ───────────────────────────────────────────────
[[ -d "$APP_DIR" ]] || die "Répertoire $APP_DIR introuvable. Cloner d'abord le repo."
cd "$APP_DIR"

[[ -f ".env" ]] || die ".env absent — copier .env.example et remplir les variables."

# ── Maintenance ON ────────────────────────────────────────────────────────────
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Caisse Pitch — Déploiement"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
$PHP artisan down --retry=60 2>/dev/null || true
ok "Mode maintenance activé"

# ── Git ───────────────────────────────────────────────────────────────────────
echo ""
echo "▸ Récupération du code..."
git fetch origin main
git reset --hard origin/main
ok "Code mis à jour ($(git log -1 --format='%h %s'))"

# ── Dépendances PHP ───────────────────────────────────────────────────────────
echo ""
echo "▸ Dépendances PHP..."
$COMPOSER install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --prefer-dist \
    2>&1 | tail -5
ok "Composer OK"

# ── Assets (si npm disponible) ────────────────────────────────────────────────
if command -v npm &>/dev/null; then
    echo ""
    echo "▸ Build assets..."
    npm ci --silent
    npm run build --silent
    ok "Assets compilés"
else
    warn "npm absent — assurez-vous que public/build/ est présent dans le repo."
fi

# ── Base de données ───────────────────────────────────────────────────────────
echo ""
echo "▸ Migrations..."
$PHP artisan migrate --force
ok "Migrations appliquées"

if $FRESH; then
    echo ""
    echo "▸ Seeding (première installation)..."
    $PHP artisan db:seed --force
    ok "Données initiales insérées"
    warn "Changez les mots de passe staff via l'interface !"
fi

# ── Storage & permissions ─────────────────────────────────────────────────────
echo ""
echo "▸ Storage..."
$PHP artisan storage:link 2>/dev/null || true
mkdir -p storage/app/private storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
ok "Dossiers storage OK"

# ── Cache Laravel ─────────────────────────────────────────────────────────────
echo ""
echo "▸ Cache..."
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan event:cache
ok "Cache régénéré"

# ── Queue ─────────────────────────────────────────────────────────────────────
$PHP artisan queue:restart 2>/dev/null && ok "Workers queue redémarrés" || true

# ── Maintenance OFF ───────────────────────────────────────────────────────────
echo ""
$PHP artisan up
ok "Application en ligne"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "  ${GREEN}Déploiement terminé${NC} — $(date '+%d/%m/%Y %H:%M')"
echo "  Commit : $(git log -1 --format='%h — %s')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
