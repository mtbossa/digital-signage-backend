#!/usr/bin/env bash

echo "
- Intus Display App installer
"

INSTALLATION_FOLDER="$HOME/.local/bin"
NODE_ENV=**NODE_ENV**
DISPLAY_ID=**DISPLAY_ID**
DISPLAY_API_TOKEN="**DISPLAY_API_TOKEN**"
APP_GITHUB_REPO_URL=**APP_GITHUB_REPO_URL**

echo "
- Cloning and saving app from GitHub to ${INSTALLATION_FOLDER}/intus
" 

cd "$INSTALLATION_FOLDER"
git clone --depth 1 -b "${NODE_ENV}" "${APP_GITHUB_REPO_URL}" intus

echo "
- Creating startup script
"

app_startup_script=$(cat << EOF
#!/usr/bin/env bash
cd ${INSTALLATION_FOLDER}/intus

git pull origin ${NODE_ENV}

if [ $? -eq 0 ]
then
  NODE_ENV=${NODE_ENV} DISPLAY_ID=${DISPLAY_ID} DISPLAY_API_TOKEN="${DISPLAY_API_TOKEN}" ./intus-raspberry > debug.log 2>&1 &
else
  NODE_ENV=${NODE_ENV} DISPLAY_ID=${DISPLAY_ID} DISPLAY_API_TOKEN="${DISPLAY_API_TOKEN}" ./intus-raspberry > debug.log 2>&1 &
fi
EOF
)
  
echo "$app_startup_script" > "${INSTALLATION_FOLDER}"/intus-startup.sh
sudo chmod +x "${INSTALLATION_FOLDER}"/intus-startup.sh

echo "
- Making startup script run on start
"

run_app=$(cat << EOF
[Desktop Entry]
Name=Intus Application
Exec=/usr/bin/bash $INSTALLATION_FOLDER/intus-startup.sh
EOF
)
sudo touch /etc/xdg/autostart/intus.desktop
echo "$run_app" | sudo tee -a /etc/xdg/autostart/intus.desktop

echo "
- Adding unclutter config to autostart
"
# Makes mouse hidden by default
sudo apt install unclutter -y
hide_mouse="@unclutter -idle 0"
echo "$hide_mouse" | sudo tee -a /etc/xdg/lxsession/LXDE-pi/autostart

echo "
- Making Raspberry not sleep by uncommenting setting \"xserver-command=X -s 0 -dpms\" inside /etc/lightdm/lightdm.conf
"
# Reference https://stackoverflow.com/a/42863888/14919507
sudo sed -i 's/#xserver-command=X/xserver-command=X -s 0 -dpms/' /etc/lightdm/lightdm.conf

echo "
- Installation complete.  Rebooting in 10 seconds...
"

sleep 10
sudo reboot now

exit 0
