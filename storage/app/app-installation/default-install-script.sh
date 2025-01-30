#!/usr/bin/env bash
 
# Variable setup
INSTALLATION_FOLDER="$HOME/.local/bin"
LOGS_FOLDER="$HOME/.local/share/intus/logs"
NODE_ENV=**NODE_ENV**
RASPBERRY_ID=**RASPBERRY_ID**
RASPBERRY_API_TOKEN="**RASPBERRY_API_TOKEN**"
APP_GITHUB_REPO_URL=**APP_GITHUB_REPO_URL**

API_URL=**API_URL**
PUSHER_CLUSTER=**PUSHER_CLUSTER**
PUSHER_APP_KEY=**PUSHER_APP_KEY**
PUSHER_HOST=**PUSHER_HOST**
PUSHER_PORT=**PUSHER_PORT**

echo ""
echo "Intus Kiosk App installer - [ Version: ${NODE_ENV} ]"
echo ""

# Step 1 - Clone repo
echo ""
echo "[ Intus Kiosk App ] Cloning and saving app from GitHub to ${INSTALLATION_FOLDER}/intus"
echo ""

mkdir -p "$LOGS_FOLDER"
mkdir -p "$INSTALLATION_FOLDER" 
# shellcheck disable=SC2164
cd "$INSTALLATION_FOLDER"
git clone --depth 1 -b main "${APP_GITHUB_REPO_URL}" intus

# Step 2 - Update deps
echo ""
echo "[ Intus Kiosk App ] Upgrading dependencies"
echo ""

sudo apt update
sudo apt upgrade -y

# Step 3 - Hide mouse
echo ""
echo "[ Intus Kiosk App ] Hiding mouse on startup"
echo ""

sudo apt install unclutter -y
hide_mouse="@unclutter -idle 0"

if ! grep -Fxq "$hide_mouse" /etc/xdg/lxsession/LXDE-pi/autostart # Checks if the line already exists before adding it
then
  echo "$hide_mouse" | sudo tee -a /etc/xdg/lxsession/LXDE-pi/autostart
fi

# Step 4 - Install chromium-browser
echo ""
echo "[ Intus Kiosk App ] Installing chromium-browser"
echo ""

sudo apt install chromium-browser -y

# Step 5 - Ensure Raspberry won't sleep
echo ""
echo "[ Intus Kiosk App ] Ensuring Raspberry won't sleep"
echo ""

sudo sed -i 's/#xserver-command=X/xserver-command=X -s 0 -dpms/' /etc/lightdm/lightdm.conf # Reference https://stackoverflow.com/a/42863888/14919507

# Step 6 - Create personalized startup script for current Raspberry
echo ""
echo "[ Intus Kiosk App ] Creating startup script"
echo ""

app_startup_script=$(
cat <<EOF
**STARTUP_SCRIPT**
EOF
)
  
echo "$app_startup_script" >"${INSTALLATION_FOLDER}"/intus-startup.sh
sudo chmod +x "${INSTALLATION_FOLDER}"/intus-startup.sh

# Step 7 - Make startup script run on every startup
echo ""
echo "[ Intus Kiosk App ] Making startup script run on start"
echo ""

run_app=$(
cat <<EOF
[Desktop Entry]
Name=Intus Application
Exec=/usr/bin/bash $INSTALLATION_FOLDER/intus-startup.sh
EOF
)
  
sudo touch /etc/xdg/autostart/intus.desktop
echo "$run_app" | sudo tee -a /etc/xdg/autostart/intus.desktop

# Step 8 - Reboot
echo ""
echo "[ Intus Kiosk App ] Installation complete.  Rebooting in 10 seconds..."
echo ""

sleep 10
sudo reboot now

exit 0
