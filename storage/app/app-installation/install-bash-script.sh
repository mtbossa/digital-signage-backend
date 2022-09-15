#!/usr/bin/env bash

echo ""
echo "My Command Line Installer"
echo ""

echo ""
echo "Creating .env file and folders"
echo ""

INSTALLATIONFOLDER="$HOME/intus"
MEDIASFOLDER=${INSTALLATIONFOLDER}/medias
DATAFOLDER=${INSTALLATIONFOLDER}/data
LOGSFOLDER=${INSTALLATIONFOLDER}/logs

mkdir -p ${MEDIASFOLDER} ${DATAFOLDER} ${LOGSFOLDER}  

env_string=$(cat << EOF
NODE_ENV=production
API_URL=**PLACE_API_URL**
DISPLAY_ID=**PLACE_DISPLAY**
DISPLAY_API_TOKEN=**PLACE_DISPLAY**
REPO_USER=mtbossa
REPO_PASS=Vaw2Pmm1234
WATCHTOWER_DEBUG=false
MEDIAS_FOLDER_PATH=${MEDIASFOLDER}
DB_FOLDER_PATH=${DATAFOLDER}
LOGS_FOLDER_PATH=${LOGSFOLDER}
EOF
)

echo "$env_string" > ${INSTALLATIONFOLDER}/.env # variable inside "" so new lines are preserved

echo ""
echo "Downloading docker-compose file"
echo ""

curl -H GET **PLACE_API_URL**/api/docker/installer/download -o ${INSTALLATIONFOLDER}/docker-compose.yml

echo ""
echo "Creating docker startup bash script and making it run automatically"
echo ""

app_startup_script=$(cat << EOF
#!/bin/bash
cd $HOME/intus
docker compose up
EOF
)
echo "$app_startup_script" > ${INSTALLATIONFOLDER}/intus-startup.sh
sudo chmod +x ${INSTALLATIONFOLDER}/intus-startup.sh
run_app=@bash ${INSTALLATIONFOLDER}/intus-startup.sh
echo $run_app >> /etc/xdg/lxsession/LXDE-pi/autostart

echo ""
echo "Making Raspberry automatically open browser on startup"
echo ""

startup=@chromium-browser --kiosk localhost:45691
echo $startup >> /etc/xdg/lxsession/LXDE-pi/autostart

echo ""
echo "Downloading Docker"
echo ""

curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
sudo usermod -aG docker ${USER}

echo ""
echo "Installation complete.  You must reboot the system"
echo ""

exit 0
