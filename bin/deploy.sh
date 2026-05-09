#!/usr/bin/env bash
# Deploy phpdeck to a low-resource VPS via SSH+rsync.
# Reads DEPLOY_* values from local .env. Builds frontend locally, runs composer/migrate/cache on server.
# Idempotent: safe to re-run.

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$PROJECT_ROOT/.env"
START_TS=$SECONDS

# ---------- helpers ----------
log()  { printf '\033[1;36m▸\033[0m %s\n' "$*"; }
ok()   { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m!\033[0m %s\n' "$*" >&2; }
die()  { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

read_env() {
    local key="$1"
    grep -E "^${key}=" "$ENV_FILE" 2>/dev/null | head -1 | sed "s/^${key}=//" | sed 's/^"\(.*\)"$/\1/'
}

# ---------- pre-flight ----------
[ -f "$ENV_FILE" ] || die ".env not found at $ENV_FILE"
command -v sshpass >/dev/null || die "sshpass not installed (apt install sshpass)"
command -v rsync   >/dev/null || die "rsync not installed"
command -v npm     >/dev/null || die "npm not installed"

DEPLOY_SSH_HOST=$(read_env DEPLOY_SSH_HOST)
DEPLOY_SSH_PORT=$(read_env DEPLOY_SSH_PORT)
DEPLOY_SSH_USER=$(read_env DEPLOY_SSH_USER)
DEPLOY_SSH_PASSWORD=$(read_env DEPLOY_SSH_PASSWORD)
DEPLOY_PATH=$(read_env DEPLOY_PATH)
DEPLOY_DOMAIN=$(read_env DEPLOY_DOMAIN)

[ -n "$DEPLOY_SSH_HOST"     ] || die "DEPLOY_SSH_HOST is empty in .env"
[ -n "$DEPLOY_SSH_USER"     ] || die "DEPLOY_SSH_USER is empty in .env"
[ -n "$DEPLOY_SSH_PASSWORD" ] || die "DEPLOY_SSH_PASSWORD is empty in .env"
[ -n "$DEPLOY_PATH"         ] || die "DEPLOY_PATH is empty in .env"
DEPLOY_SSH_PORT="${DEPLOY_SSH_PORT:-22}"

cd "$PROJECT_ROOT"

if [ -n "$(git status --porcelain 2>/dev/null || true)" ]; then
    warn "git working tree is dirty — deploying uncommitted changes"
fi

remote_exec() {
    SSHPASS="$DEPLOY_SSH_PASSWORD" sshpass -e ssh \
        -o StrictHostKeyChecking=accept-new \
        -o LogLevel=ERROR \
        -p "$DEPLOY_SSH_PORT" \
        "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST" \
        "$@"
}

# ---------- 1. local build ----------
log "[1/7] Building frontend assets locally"
npm run build >/tmp/phpdeck-build.log 2>&1 || { cat /tmp/phpdeck-build.log; die "npm run build failed"; }
ok "build done ($(du -sh public/build 2>/dev/null | awk '{print $1}'))"

# ---------- 2. rsync ----------
log "[2/7] Rsyncing code → $DEPLOY_SSH_HOST:$DEPLOY_PATH"
SSHPASS="$DEPLOY_SSH_PASSWORD" sshpass -e rsync -az --delete \
    --exclude '.git' \
    --exclude '.github' \
    --exclude '.claude' \
    --exclude 'node_modules' \
    --exclude 'vendor' \
    --exclude '.env' \
    --exclude '.env.backup' \
    --exclude '.env.local' \
    --exclude '.env.production' \
    --exclude '.env.testing' \
    --exclude '.env.bak' \
    --exclude 'tests' \
    --exclude 'phpunit.xml' \
    --exclude 'storage/logs/*.log' \
    --exclude 'storage/framework/cache/data/*' \
    --exclude 'storage/framework/sessions/*' \
    --exclude 'storage/framework/views/*' \
    --exclude 'storage/app/private/*' \
    --exclude 'storage/app/public/*' \
    --exclude 'database/database.sqlite' \
    --exclude '*.md' \
    --exclude 'docs' \
    --exclude 'repomix-*' \
    --exclude 'pnpm-workspace.yaml' \
    --exclude 'eslint.config.js' \
    --exclude 'pint.json' \
    --exclude 'tsconfig.json' \
    --exclude 'components.json' \
    --exclude 'package.json' \
    --exclude 'package-lock.json' \
    --exclude 'vite.config.ts' \
    --info=stats1 \
    --chown=www-data:www-data \
    --chmod=Du=rwx,Dg=rwxs,Do=rx,Fu=rw,Fg=r,Fo=r \
    -e "ssh -o StrictHostKeyChecking=accept-new -o LogLevel=ERROR -p $DEPLOY_SSH_PORT" \
    ./ "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST:$DEPLOY_PATH/" \
    | grep -E '^(Number of|Total transferred)' || true
ok "rsync done"

# ---------- 3. fix perms (storage/db/cache writable for www-data) ----------
log "[3/7] Fixing permissions"
remote_exec "
    chown -R www-data:www-data $DEPLOY_PATH
    chmod -R 775 $DEPLOY_PATH/storage $DEPLOY_PATH/bootstrap/cache $DEPLOY_PATH/database
    [ -f $DEPLOY_PATH/database/database.sqlite ] && chmod 664 $DEPLOY_PATH/database/database.sqlite || true
    chmod 600 $DEPLOY_PATH/.env
    chmod +x $DEPLOY_PATH/artisan $DEPLOY_PATH/bin/*.sh 2>/dev/null || true
"
ok "perms set"

# ---------- 4. composer install (no-op if lock unchanged) ----------
log "[4/7] Installing PHP dependencies (no-dev)"
remote_exec "cd $DEPLOY_PATH && \
    COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1 \
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress 2>&1 \
    | tail -3 && \
    chown -R www-data:www-data $DEPLOY_PATH/vendor"
ok "composer install done"

# ---------- 5. migrations (no-op if up-to-date) ----------
log "[5/7] Running migrations"
remote_exec "cd $DEPLOY_PATH && sudo -u www-data php artisan migrate --force 2>&1 | tail -5"
ok "migrations done"

# ---------- 6. caches ----------
log "[6/7] Rebuilding config/route/view caches"
remote_exec "cd $DEPLOY_PATH && \
    sudo -u www-data php artisan optimize:clear 2>&1 | tail -3 && \
    sudo -u www-data php artisan config:cache 2>&1 | tail -1 && \
    sudo -u www-data php artisan route:cache 2>&1 | tail -1 && \
    sudo -u www-data php artisan view:cache 2>&1 | tail -1"
ok "caches rebuilt"

# ---------- 7. reload php-fpm (clear opcache) ----------
log "[7/7] Reloading php-fpm"
remote_exec "systemctl reload php8.4-fpm"
ok "php-fpm reloaded"

# ---------- health check ----------
if [ -n "$DEPLOY_DOMAIN" ]; then
    log "Health check"
    # Follow redirects (http→https) and check final landing page
    HTTP_CODE=$(curl -s -L -o /dev/null -w "%{http_code}" --max-time 15 \
        --resolve "$DEPLOY_DOMAIN:80:$DEPLOY_SSH_HOST" \
        --resolve "$DEPLOY_DOMAIN:443:$DEPLOY_SSH_HOST" \
        "http://$DEPLOY_DOMAIN/login" 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" = "200" ]; then
        ok "/login → 200"
    else
        warn "/login → $HTTP_CODE (expected 200)"
    fi
fi

# ---------- disk usage tail ----------
DISK=$(remote_exec "df -h / | awk 'NR==2 {print \$3\"/\"\$2\" (\"\$5\")\"}'")
ok "disk: $DISK"

printf '\n\033[1;32m✓\033[0m deployed in %ds\n' "$((SECONDS - START_TS))"
