#!/bin/bash
# =============================================================================
# FRONTEND Forge Deployment Script (www.ujishi.baconologies.com)
# Paste into Forge > www.ujishi.baconologies.com > Deployment Script
#
# Forge site settings:
#   Linked Files:    frontend/.env
#
# AFTER FIRST SUCCESSFUL DEPLOY, create the Forge Daemon at:
#   Forge > Server > Daemons > New Daemon
#   Command:   node /home/forge/www.ujishi.baconologies.com/current/frontend/.output/server/index.mjs
#   Directory: /home/forge/www.ujishi.baconologies.com/current/frontend/.output/server
#   User:      forge
# =============================================================================

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY/frontend

npm install

npm run build

$ACTIVATE_RELEASE()

# Restart all supervisor-managed daemons (|| true = non-fatal on first deploy before daemon exists)
sudo supervisorctl restart all 2>/dev/null || true
