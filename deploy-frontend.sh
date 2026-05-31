#!/bin/bash
# =============================================================================
# FRONTEND Forge Deployment Script (www.ujishi.baconologies.com)
# Paste into Forge > www.ujishi.baconologies.com > Deployment Script
#
# Forge site settings:
#   Type:            Node.js (no PHP)
#   Web Directory:   frontend/public   (Nginx fallback; Nitro handles routing)
#   Start Command:   node .output/server/index.mjs
#   Linked Files:    frontend/.env
# =============================================================================

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY/frontend

npm ci --prefer-offline

npm run build

$ACTIVATE_RELEASE()

# Restart the Nuxt Node.js daemon (Forge Daemon name: nuxt-frontend)
sudo supervisorctl restart "nuxt-frontend:*"
