### Setting Laravel Sanctum:
* Must set .env variables to the frontend domain with port, for example, for local Angular applications:
  * `SANCTUM_STATEFUL_DOMAINS=localhost:4200`
  * `SESSION_DOMAIN=localhost`

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

## ER

<a href="https://i.ibb.co/Bw4t2p7/intus-er.jpg" target="_blank"><img width="70%" src="https://i.ibb.co/Bw4t2p7/intus-er.jpg"></a>
