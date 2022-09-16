#!/usr/bin/env bash

separator="-------------------------------"

echo "
$separator
Intus Display App installer
$separator
"

echo "
$separator
Creating .env file and folders
$separator
"

INSTALLATIONFOLDER="$HOME/intus"
MEDIASFOLDER=${INSTALLATIONFOLDER}/medias
DATAFOLDER=${INSTALLATIONFOLDER}/data
LOGSFOLDER=${INSTALLATIONFOLDER}/logs

mkdir -p ${MEDIASFOLDER} ${DATAFOLDER} ${LOGSFOLDER}  

env_string=$(cat << EOF
NODE_ENV=production
API_URL=**API_URL**
DISPLAY_ID=**DISPLAY_ID**
DISPLAY_API_TOKEN=**DISPLAY_API_TOKEN**
PUSHER_APP_KEY=**PUSHER_APP_KEY**
PUSHER_APP_CLUSTER=**PUSHER_APP_CLUSTER**
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
$separator
Downloading docker-compose file
$separator
"

curl -H GET **API_URL**/api/docker/installer/download -o ${INSTALLATIONFOLDER}/docker-compose.yml

echo "
$separator
Creating docker startup bash script and making it run automatically
$separator
"

app_startup_script=$(cat << EOF
#!/usr/bin/env bash
xset s noblank
xset -dpms
xset -s off
cd $HOME/intus
docker compose up -d
chromium-browser --kiosk http://localhost:45691
EOF
)
  
echo "$app_startup_script" > ${INSTALLATIONFOLDER}/intus-startup.sh
sudo chmod +x ${INSTALLATIONFOLDER}/intus-startup.sh
run_app="@bash ${INSTALLATIONFOLDER}/intus-startup.sh"
echo "$run_app" | sudo tee -a /etc/xdg/lxsession/LXDE-pi/autostart

echo "
$separator
Installing unclutter
$separator
"

sudo apt-get install unclutter

hide_mouse="@unclutter -idle 0"
echo "$hide_mouse" | sudo tee -a /etc/xdg/lxsession/LXDE-pi/autostart

# Checks if Docker is installed, and if not, installs it
echo "
$separator
Checking docker installation
$separator
"

if ! command -v docker &> /dev/null
then
    echo "
    $separator
    Docker not installed, installing Docker.
    $separator
    "
    docker_access_token=**DOCKER_ACCESS_TOKEN**
    
    sudo groupadd docker
    sudo usermod -aG docker ${USER}
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh &> /dev/null
    
    echo "$docker_access_token" | docker login -u mtbossa --password-stdin
fi

# Downloads Docker App image
echo "
$separator
Downloading Docker App image
$separator
"
docker_tag=**DOCKER_TAG**  
docker pull mtbossa/raspberry-prod:$docker_tag

echo ""
echo "Installation complete.  You must reboot the system"
echo ""

exit 0
