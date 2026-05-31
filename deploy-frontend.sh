#!/bin/bash
# =============================================================================
# FRONTEND Forge Deployment Script (www.ujishi.baconologies.com)
# Uses PM2 to manage the Nuxt Node.js process (no Forge Daemons needed)
# =============================================================================

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY/frontend

npm install

npm run build

$ACTIVATE_RELEASE()

# Install PM2 globally if not present
if ! command -v pm2 &> /dev/null; then
    sudo npm install -g pm2
fi

# Reload existing process OR start fresh if first deploy
pm2 reload nuxt-frontend --update-env 2>/dev/null || \
pm2 start /home/forge/www.ujishi.baconologies.com/current/frontend/.output/server/index.mjs \
    --name nuxt-frontend \
    --env production

pm2 save
