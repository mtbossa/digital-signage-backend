#!/usr/bin/env bash
{
cd ${INSTALLATION_FOLDER}/intus
git stash
git pull origin main 
sudo chmod +x ./intus-raspberry
} > ${INSTALLATION_FOLDER}/startup.log
NODE_ENV=${NODE_ENV}  RASPBERRY_ID="${RASPBERRY_ID}" RASPBERRY_API_TOKEN="${RASPBERRY_API_TOKEN}" API_URL="${API_URL}" PUSHER_CLUSTER="${PUSHER_CLUSTER}" PUSHER_APP_KEY="${PUSHER_APP_KEY}" PUSHER_HOST="${PUSHER_HOST}" PUSHER_PORT="${PUSHER_PORT}" PUSHER_CLUSTER="${PUSHER_CLUSTER}" ./intus-raspberry & > /dev/null 2>&1
