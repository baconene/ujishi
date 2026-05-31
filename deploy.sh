#!/bin/bash
# =============================================================================
# BACKEND Forge Deployment Script (api.ujishi.baconologies.com)
# Paste into Forge > api.ujishi.baconologies.com > Deployment Script
#
# Forge site settings:
#   Web Directory:       backend/public
#   Linked Files:        backend/.env
#   Linked Directories:  backend/storage
# =============================================================================

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY/backend

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

$FORGE_PHP artisan migrate --force
$FORGE_PHP artisan optimize
$FORGE_PHP artisan storage:link

$ACTIVATE_RELEASE()

$RESTART_QUEUES()
