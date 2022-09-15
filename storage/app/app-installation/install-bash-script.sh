#!/usr/bin/env bash

echo "
Intus Display App installer
"

echo "
Creating .env file and folders
"

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

echo "
Downloading docker-compose file
"

curl -H GET **PLACE_API_URL**/api/docker/installer/download -o ${INSTALLATIONFOLDER}/docker-compose.yml

echo "
Creating docker startup bash script and making it run automatically
"

app_startup_script=$(cat << EOF
#!/usr/bin/env bash
cd $HOME/intus
docker compose up
EOF
)
  
echo "$app_startup_script" > ${INSTALLATIONFOLDER}/intus-startup.sh
sudo chmod +x ${INSTALLATIONFOLDER}/intus-startup.sh
run_app="@bash ${INSTALLATIONFOLDER}/intus-startup.sh"
echo "$run_app" | sudo tee -a /etc/xdg/lxsession/LXDE-pi/autostart

echo "
Making Raspberry automatically open browser on startup
"

startup="@chromium-browser --kiosk localhost:45691"
echo "$startup" | sudo tee -a /etc/xdg/lxsession/LXDE-pi/autostart

# Checks if Docker is installed, and if not, installs it
echo "
Checking docker installation
"

if command -v docker &> /dev/null
then
    echo ""
    echo "Docker already installed, installation complete!"
    echo ""

    exit 0
fi

echo "
Docker not installed, installing Docker.
"

curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
sudo usermod -aG docker ${USER}

echo ""
echo "Installation complete.  You must reboot the system"
echo ""

exit 0
