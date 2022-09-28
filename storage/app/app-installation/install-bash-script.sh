#!/usr/bin/env bash


echo "
- Intus Display App installer
"

# Checks if Docker is installed, and if not, installs it
echo "
- Checking docker installation
"

if ! command -v docker &> /dev/null
then
    echo "
    - Docker not installed, installing Docker.
    "
    docker_access_token=**DOCKER_ACCESS_TOKEN**
    
    sudo groupadd docker
    sudo usermod -aG docker ${USER}
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    
    echo "$docker_access_token" | docker login -u mtbossa --password-stdin
fi

# Downloads Docker App image
echo "
- Downloading Docker App image
"

docker pull **DOCKER_COMPLETE_IMAGE**

echo "
- Creating .env file and folders
"

INSTALLATIONFOLDER="$HOME/.local/share/intus"
MEDIASFOLDER=${INSTALLATIONFOLDER}/medias
DATAFOLDER=${INSTALLATIONFOLDER}/data
LOGSFOLDER=${INSTALLATIONFOLDER}/logs

mkdir -p ${MEDIASFOLDER} ${DATAFOLDER} ${LOGSFOLDER}  

env_string=$(cat << EOF
NODE_ENV=**NODE_ENV**
DISPLAY_ID=**DISPLAY_ID**
DISPLAY_API_TOKEN=**DISPLAY_API_TOKEN**
MEDIAS_FOLDER_PATH=${MEDIASFOLDER}
DB_FOLDER_PATH=${DATAFOLDER}
LOGS_FOLDER_PATH=${LOGSFOLDER}
EOF
)

echo "$env_string" > ${INSTALLATIONFOLDER}/.env # variable inside "" so new lines are preserved

echo "
- Downloading docker-compose file
"

curl -H GET **API_URL**/api/docker/installer/download -o ${INSTALLATIONFOLDER}/docker-compose.yml

echo "
- Creating docker startup bash script and making it run automatically
"

app_startup_script=$(cat << EOF
#!/usr/bin/env bash
cd ${INSTALLATIONFOLDER}
docker pull **DOCKER_COMPLETE_IMAGE**
docker compose up -d
chromium-browser --kiosk http://localhost:45691
EOF
)
  
echo "$app_startup_script" > ${INSTALLATIONFOLDER}/intus-startup.sh
sudo chmod +x ${INSTALLATIONFOLDER}/intus-startup.sh

run_app="@bash ${INSTALLATIONFOLDER}/intus-startup.sh"
echo "$run_app" | sudo tee -a /etc/xdg/lxsession/LXDE-pi/autostart

echo "
- Setting unclutter
"

if ! command -v unclutter &> /dev/null
then
    echo "
    - Unclutter not installed, installing...
    "
    sudo apt-get install unclutter
    
    hide_mouse="@unclutter -idle 0"
    echo "$hide_mouse" | sudo tee -a /etc/xdg/lxsession/LXDE-pi/autostart
fi

echo "
- Making Raspberry not sleep by uncommenting setting \"xserver-command=X -s 0 -dpms\" inside /etc/lightdm/lightdm.conf
"
# Reference https://stackoverflow.com/a/42863888/14919507
sudo sed -i 's/#xserver-command=X/xserver-command=X -s 0 -dpms/' /etc/lightdm/lightdm.conf

echo ""
echo "Installation complete.  Rebooting in 10 seconds"
echo ""

sleep 10
sudo reboot now

exit 0
