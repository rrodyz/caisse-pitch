#!/usr/bin/env bash
# =============================================================================
# Caisse Pitch — Script de première installation sur n0c.com
# À exécuter UNE SEULE FOIS depuis le terminal n0c.com
# =============================================================================

set -euo pipefail

REPO="https://github.com/rrodyz/caisse-pitch.git"
APP_DIR="/home/htegyeahqn/caisse-pitch"
PHP="php"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✓${NC} $1"; }
warn() { echo -e "${YELLOW}⚠${NC}  $1"; }
die()  { echo -e "${RED}✗ ERREUR${NC}: $1"; exit 1; }

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Caisse Pitch — Installation initiale"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# ── Clone ─────────────────────────────────────────────────────────────────────
if [[ -d "$APP_DIR/.git" ]]; then
    warn "Repo déjà cloné dans $APP_DIR — utiliser deploy.sh pour les mises à jour."
    exit 0
fi

echo "▸ Clonage du dépôt..."
git clone "$REPO" "$APP_DIR"
ok "Repo cloné"
cd "$APP_DIR"

# ── .env ─────────────────────────────────────────────────────────────────────
if [[ ! -f ".env" ]]; then
    cp .env.example .env
    warn ".env créé depuis .env.example — ÉDITEZ-LE MAINTENANT avant de continuer !"
    warn ""
    warn "Variables requises :"
    warn "  APP_URL=https://pitch-club.net"
    warn "  APP_ENV=production"
    warn "  APP_DEBUG=false"
    warn "  DB_HOST=  DB_DATABASE=  DB_USERNAME=  DB_PASSWORD="
    warn "  ADMIN_EMAIL=  ADMIN_PASSWORD="
    warn "  ESTABLISHMENT_NAME="
    warn "  SESSION_ENCRYPT=true"
    warn "  SESSION_SECURE_COOKIE=true"
    warn ""
    echo "Éditez .env puis relancez : bash $APP_DIR/deploy/install.sh"
    exit 0
fi

# ── APP_KEY ──────────────────────────────────────────────────────────────────
if ! grep -q "^APP_KEY=base64:" .env; then
    $PHP artisan key:generate --force
    ok "APP_KEY généré"
fi

# ── Lancer le déploiement complet ─────────────────────────────────────────────
bash deploy/deploy.sh --fresh
