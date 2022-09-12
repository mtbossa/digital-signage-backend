#!/bin/bash

echo ""
echo "My Command Line Installer"
echo ""

echo "Updating packages"

apt update

echo "Upgrading packages"

apt upgrade

echo "Creating .env file and folders"

# Create destination folder
INSTALLATIONFOLDER="$HOME/intus"
MEDIASFOLDER=${INSTALLATIONFOLDER}/medias
DATAFOLDER=${INSTALLATIONFOLDER}/data
LOGSFOLDER=${INSTALLATIONFOLDER}/logs

mkdir -p ${MEDIASFOLDER} ${DATAFOLDER} ${LOGSFOLDER}  

env_string=$(cat << EOF
NODE_ENV=production
API_URL='http://192.168.0.108:80'
DISPLAY_ID=##PLACE##
DISPLAY_API_TOKEN=##PLACE##
REPO_USER=mtbossa
REPO_PASS=Vaw2Pmm1234
WATCHTOWER_DEBUG=false
MEDIAS_FOLDER_PATH=${MEDIASFOLDER}
DB_FOLDER_PATH=${DATAFOLDER}
LOGS_FOLDER_PATH=${LOGSFOLDER}
EOF
)

echo $env_string > ${INSTALLATIONFOLDER}/.env

echo "Downloading docker-compose"
 
curl -H GET http://192.168.0.108:80/api/docker/installer/download -o ${INSTALLATIONFOLDER}/docker-compose.yml

echo "Creating docker startup bash script and making it run automatically"

app_startup_script=$(cat << EOF
#!/bin/bash
cd $HOME/intus
docker compose up
EOF
)
echo $app_startup_script > ${INSTALLATIONFOLDER}/intus-startup.sh
run_app=@bash ${INSTALLATIONFOLDER}/intus-startup.sh
echo $run_app >> /etc/xdg/lxsession/LXDE-pi/autostart

echo "Making Raspberry automatically open browser on startup"

startup=@chromium-browser --kiosk localhost:45691
echo $startup >> /etc/xdg/lxsession/LXDE-pi/autostart

echo "Downloading Docker"

curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
usermod -aG docker ${USER}

echo ""
echo "Installation complete.  Rebooting"
echo ""

reboot now

# Exit from the script with success (0)
exit 0
