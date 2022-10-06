#!/usr/bin/env bash
{
cd ${INSTALLATION_FOLDER}/intus
git stash
git pull origin ${NODE_ENV}
sudo chmod +x ./intus-raspberry  
} > ${INSTALLATION_FOLDER}/startup.log
NODE_ENV=${NODE_ENV} RASPBERRY_ID="${RASPBERRY_ID}" RASPBERRY_API_TOKEN="${RASPBERRY_API_TOKEN}" ./intus-raspberry & > /dev/null 2>&1
