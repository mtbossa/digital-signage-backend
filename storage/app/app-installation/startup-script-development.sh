#!/usr/bin/env bash
{
cd ${INSTALLATION_FOLDER}/intus
git stash
git pull origin ${NODE_ENV} 
sudo chmod +x ./intus-raspberry
} > ${INSTALLATION_FOLDER}/startup.log
NODE_ENV=${NODE_ENV}  RASPBERRY_ID="${RASPBERRY_ID}" RASPBERRY_API_TOKEN="${RASPBERRY_API_TOKEN}" API_URL="${API_URL}" PUSHER_CLUSTER="${PUSHER_CLUSTER}" PUSHER_APP_KEY="${PUSHER_APP_KEY}" ./intus-raspberry & > /dev/null 2>&1
