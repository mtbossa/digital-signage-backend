### Setting Laravel Sanctum:

* Must set .env variables to the frontend domain with port, for example, for local Angular applications:
  * `SANCTUM_STATEFUL_DOMAINS=localhost:4200`
  * `SESSION_DOMAIN=localhost`

In production, eg.:

* `SANCTUM_STATEFUL_DOMAINS=*.revendahost.inf.br`
* `SESSION_DOMAIN=.revendahost.inf.br`

---

### How to debug Laravel API inside PHPStorm:

* Set to .env SAIL_XDEBUG_MODE=develop,debug
* Add on PHPStorm settings: `PHP > Servers > Host: 0.0.0.0 - Absolute path on server: /var/www/html`
* Install Sail normally
* Send `XDEBUG_SESSION=session_name` or `XDEBUG_SESSION_START=session_name` with any request

> Read: **https://xdebug.org/docs/step_debug#manual-init**

* Or install Xdebug web extension

#### Debug tests on PHPStorm

* Add ./docker-compose.yml (laravel container) as CLI Interpreter

---

### Install script

`sudo curl -H "Authorization: Bearer <DISPLAY_API_TOKEN>" <API_URL>/api/displays/<DISPLAY_ID>/installer/download | bash`

---

### Post Max Size - Upload Max Size

* Must set `post_max_size = 150M` and `upload_max_filesize = 150M`
* Development:
  * If we don't [publish sail files](https://laravel.com/docs/9.x/sail#sail-customization), we'll need to change the
    php.ini
    configuration file inside `vendor/laravel/sail/runtimes/RUNTIME_VERSION/php.ini` everytime we build the sail
    container `sail build --no-cache`.

---

## ER

<a href="https://i.ibb.co/Bw4t2p7/intus-er.jpg" target="_blank"><img width="70%" src="https://i.ibb.co/Bw4t2p7/intus-er.jpg"></a>

