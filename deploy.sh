#!/bin/bash

# Laravel Planning Poker - Deployment Script
#
# Lokales Deployment-Script für manuelles Deployment von deinem Rechner.
# Für automatisches CI/CD-Deployment nutze GitHub Actions (.github/workflows/deploy.yml),
# die GitHub Secrets verwendet.
#
# Usage: ./deploy.sh [server-alias]
# Example: ./deploy.sh production

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Load configuration from external file
CONFIG_FILE="deploy.config"

if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}Error: Configuration file '$CONFIG_FILE' not found!${NC}"
    echo ""
    echo "Please create '$CONFIG_FILE' based on 'deploy.config.example':"
    echo "  cp deploy.config.example $CONFIG_FILE"
    echo "  # Then edit $CONFIG_FILE with your server details"
    exit 1
fi

# Source the configuration file
# Check if current shell supports associative arrays
# If running in bash < 4.0, switch to zsh or Homebrew bash
if [ -n "$ZSH_VERSION" ]; then
    # Running in zsh - load config (zsh supports typeset -A)
    source "$CONFIG_FILE"
elif bash -c 'declare -A test 2>/dev/null' &>/dev/null 2>&1; then
    # bash 4+ supports associative arrays - load config normally
    source "$CONFIG_FILE"
else
    # bash < 4.0 doesn't support associative arrays
    # Try Homebrew bash first (usually version 5+)
    if command -v /usr/local/bin/bash &>/dev/null && /usr/local/bin/bash --version 2>/dev/null | grep -q "version [4-9]"; then
        # Use Homebrew bash - reload script with Homebrew bash
        exec /usr/local/bin/bash "$0" "$@"
    elif command -v zsh &>/dev/null; then
        # Fallback to zsh - reload script with zsh
        exec zsh "$0" "$@"
    else
        echo -e "${RED}Error: Associative arrays not supported in this bash version.${NC}"
        echo "Please install bash 4+ via Homebrew: ${YELLOW}brew install bash${NC}"
        exit 1
    fi
fi

# Validate that required arrays are set
if [ -z "${SERVERS[production]}" ] && [ -z "${SERVERS[staging]}" ]; then
    echo -e "${RED}Error: No servers configured in '$CONFIG_FILE'!${NC}"
    echo "Please configure at least one server (production or staging)."
    exit 1
fi

# Get server from argument or use production as default
SERVER_ALIAS=${1:-production}

if [ -z "${SERVERS[$SERVER_ALIAS]}" ]; then
    echo -e "${RED}Error: Unknown server alias '$SERVER_ALIAS'${NC}"
    echo "Available servers: ${!SERVERS[@]}"
    exit 1
fi

SERVER=${SERVERS[$SERVER_ALIAS]}
REMOTE_PATH=${PATHS[$SERVER_ALIAS]}

echo -e "${GREEN}═══════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Deploying to: ${YELLOW}$SERVER_ALIAS${NC}"
echo -e "${GREEN}  Server: ${YELLOW}$SERVER${NC}"
echo -e "${GREEN}  Path: ${YELLOW}$REMOTE_PATH${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════${NC}"
echo ""

# Function to run commands on remote server
run_remote() {
    echo -e "${YELLOW}▶ $1${NC}"
    ssh $SERVER "cd $REMOTE_PATH && $1"
}

# 1. Push local changes
echo -e "${GREEN}[1/9] Pushing local changes to Git...${NC}"
git push origin $(git branch --show-current)

# 2. Pull changes on server
echo -e "${GREEN}[2/9] Pulling latest code on server...${NC}"
run_remote "git pull origin $(git branch --show-current)"

# 3. Install Composer dependencies
echo -e "${GREEN}[3/9] Installing Composer dependencies...${NC}"
run_remote "composer install --no-dev --optimize-autoloader --no-interaction"

# 4. Install NPM dependencies
echo -e "${GREEN}[4/9] Installing NPM dependencies...${NC}"
run_remote "npm ci --production=false"

# 5. Build assets
echo -e "${GREEN}[5/9] Building assets...${NC}"
run_remote "npm run build"

# 6. Run migrations
echo -e "${GREEN}[6/9] Running database migrations...${NC}"
run_remote "php artisan migrate --force"

# 7. Clear caches
echo -e "${GREEN}[7/9] Clearing caches...${NC}"
run_remote "php artisan cache:clear"
run_remote "php artisan config:clear"
run_remote "php artisan route:clear"
run_remote "php artisan view:clear"

# 8. Optimize
echo -e "${GREEN}[8/9] Optimizing application...${NC}"
run_remote "php artisan config:cache"
run_remote "php artisan route:cache"
run_remote "php artisan view:cache"

# 9. Restart PM2 (Reverb)
echo -e "${GREEN}[9/9] Restarting Reverb server...${NC}"
run_remote "pm2 restart reverb"

echo ""
echo -e "${GREEN}═══════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✓ Deployment completed successfully!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════${NC}"
echo ""
echo -e "Next steps:"
echo -e "  1. Check application: ${YELLOW}https://poker.halemba.rocks${NC}"
echo -e "  2. Monitor logs: ${YELLOW}ssh $SERVER 'pm2 logs reverb'${NC}"
echo -e "  3. Check Laravel logs: ${YELLOW}ssh $SERVER 'tail -f $REMOTE_PATH/storage/logs/laravel.log'${NC}"

