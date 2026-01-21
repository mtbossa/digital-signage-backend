# Production Deployment Guide

This guide covers deploying the Laravel backend and Angular frontend to production using Docker with Nginx and Cloudflare.

## Architecture Overview

- **Web Server**: Nginx (with Cloudflare Origin Certificates)
- **Port**: 2053 (HTTPS only - Cloudflare proxy supported port)
- **Protocol**: HTTPS exclusively - HTTP is disabled
- **Backend**: Laravel PHP 8.1-FPM
- **Frontend**: Angular SPA
- **Database**: PostgreSQL 13
- **Queue Worker**: Laravel queue processor
- **CDN/Proxy**: Cloudflare (with proxy enabled - orange cloud)

### Domains
- API: `https://api.paroquia-piox.app.br:2053`
- Frontend: `https://app.paroquia-piox.app.br:2053`

---

## Prerequisites

- Docker and Docker Compose installed on your server
- Domain names configured in Cloudflare
- Cloudflare account with access to SSL/TLS settings
- Server with port 2053 open

---

## Step 1: Configure Cloudflare DNS

1. **Add DNS Records** in Cloudflare Dashboard:

   For API domain:
   - Type: `A`
   - Name: `api.paroquia-piox.app.br` (or just `api` if using subdomain)
   - Content: Your server IP address
   - Proxy status: **Proxied** (orange cloud - enabled)

   For App domain:
   - Type: `A`
   - Name: `app.paroquia-piox.app.br` (or just `app` if using subdomain)
   - Content: Your server IP address
   - Proxy status: **Proxied** (orange cloud - enabled)

2. **Note**: Port 2053 is one of Cloudflare's supported HTTPS ports for proxied traffic

---

## Step 2: Generate Cloudflare Origin Certificate

Origin Certificates encrypt traffic between Cloudflare and your server. We'll create a **single wildcard certificate** that covers both API and App subdomains.

1. **Navigate to Cloudflare Dashboard**:
   - Go to: **SSL/TLS** → **Origin Server**

2. **Create Wildcard Certificate**:
   - Click **"Create Certificate"**
   - Hostnames: Enter both domains (or use wildcard):
     - `*.paroquia-piox.app.br` (wildcard - covers all subdomains)
     - `paroquia-piox.app.br` (root domain)
   - Certificate Validity: **15 years** (recommended)
   - Private Key Type: **RSA (2048)**
   - Click **"Create"**

3. **Save Certificate Files**:
   - Copy the **Origin Certificate** content
   - Save it as: `deployment/nginx/certs/cloudflare-paroquia-piox.app.br.pem`

   - Copy the **Private Key** content
   - Save it as: `deployment/nginx/certs/cloudflare-paroquia-piox.app.br.key`

**Note**: The default certificate name is `cloudflare-paroquia-piox.app.br`, but you can customize it by setting `SSL_CERT_NAME` in your `.env` file (without the .pem/.key extension).

### Example file structure:
```
deployment/nginx/certs/
├── .gitignore
├── cloudflare-paroquia-piox.app.br.pem
└── cloudflare-paroquia-piox.app.br.key
```

### Custom Certificate Name (Optional)

To use a different certificate name, add to your `.env`:
```env
SSL_CERT_NAME=my-custom-cert-name
```

Then save your certificates as:
- `deployment/nginx/certs/my-custom-cert-name.pem`
- `deployment/nginx/certs/my-custom-cert-name.key`

---

## Step 3: Configure Cloudflare SSL/TLS Settings

1. **Go to**: SSL/TLS → **Overview**
2. **Set encryption mode** to: **Full (strict)**
   - This ensures end-to-end encryption between visitors → Cloudflare → your server
   - Cloudflare validates the origin certificate

3. **Recommended Settings** (SSL/TLS → Edge Certificates):
   - ✅ Always Use HTTPS: **On**
   - ✅ Automatic HTTPS Rewrites: **On**
   - ✅ Minimum TLS Version: **TLS 1.2** or higher
   - ✅ TLS 1.3: **On**

---

## Step 4: Configure Environment Variables

Create or update your `.env` file:

```env
APP_NAME="Paroquia Pio X"
APP_ENV=production
APP_KEY=base64:your-generated-app-key-here
APP_DEBUG=false
APP_URL=https://api.paroquia-piox.app.br:2053
APP_FRONT_URL=https://app.paroquia-piox.app.br:2053

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=paroquia_pio_x_production
DB_USERNAME=your_db_username
DB_PASSWORD=your_secure_password

QUEUE_CONNECTION=database
CACHE_DRIVER=file
SESSION_DRIVER=file

# Add other production environment variables
```

**Important**:
- Generate a secure `APP_KEY` using: `php artisan key:generate`
- Use strong database credentials
- Set `APP_DEBUG=false` in production

---

## Step 5: Deploy the Application

### 5.1 Prepare the Server

```bash
# Clone your repository (if not already on server)
git clone your-repository-url
cd digital-signage-backend

# Ensure .env file exists with production values
cp .env.example .env
# Edit .env with your production values
nano .env
```

### 5.2 Build and Start Containers

```bash
# Build and start all services
docker-compose -f docker-compose.prod.yml up -d --build

# This will start:
# - laravel-app (PHP-FPM application)
# - laravel-worker (Queue worker)
# - nginx-webserver (Web server on port 2053 - HTTPS only)
# - pgsql (PostgreSQL database)
```

**Important**: In production, your application code (including vendor directory) is **baked into the Docker image**. The containers do NOT mount your source code from the host.

**Storage Strategy**:
- Application code is in the image (immutable)
- Writable directories (`storage/` and `bootstrap/cache/`) use **Docker named volumes**
- Data persists between container restarts
- No permission conflicts (volumes owned by www-data)

To verify vendor exists:
```bash
docker exec laravel-app ls -la /var/www/vendor
# Should show all composer packages

# Check volume permissions
docker exec laravel-app ls -la /var/www/storage
# Should be owned by www-data
```

### 5.3 Initialize the Application

```bash
# Run database migrations
docker exec laravel-app php artisan migrate --force

# Clear and cache configuration
docker exec laravel-app php artisan config:cache
docker exec laravel-app php artisan route:cache
docker exec laravel-app php artisan view:cache

# Create storage link (if needed)
docker exec laravel-app php artisan storage:link
```

### 5.4 Verify Deployment

```bash
# Check all containers are running
docker-compose -f docker-compose.prod.yml ps

# Should show all services as "Up"
```

---

## Step 6: Test Your Deployment

### Test from your local machine:

```bash
# Test API endpoint
curl -I https://api.paroquia-piox.app.br:2053

# Expected response: HTTP/2 200 OK

# Test App endpoint
curl -I https://app.paroquia-piox.app.br:2053

# Expected response: HTTP/2 200 OK

# Test API health endpoint (if you have one)
curl https://api.paroquia-piox.app.br:2053/api/health
```

### Test in browser:
- Visit: `https://app.paroquia-piox.app.br:2053`
- Should load your Angular application with valid HTTPS certificate
- Check browser console for any errors

---

## Monitoring and Logs

### View Container Logs

```bash
# View all logs
docker-compose -f docker-compose.prod.yml logs -f

# View specific service logs
docker logs nginx-webserver -f
docker logs laravel-app -f
docker logs laravel-worker -f
docker logs pgsql -f
```

### Check Nginx Configuration

```bash
# Test Nginx configuration
docker exec nginx-webserver nginx -t

# Should output: "configuration file /etc/nginx/nginx.conf test is successful"
```

### Monitor Queue Workers

```bash
# Check if queue worker is processing jobs
docker logs laravel-worker -f

# Restart queue worker if needed
docker restart laravel-worker
```

---

## Updating the Application

### Deploy New Changes

Since your application code is baked into the Docker image, you **must rebuild** the images to deploy code changes.

```bash
# Pull latest changes from repository
git pull origin main

# Rebuild images with new code and restart containers
# The --build flag is ESSENTIAL - it rebuilds the images with your new code
docker-compose -f docker-compose.prod.yml up -d --build

# Run migrations (if any)
docker exec laravel-app php artisan migrate --force

# Clear caches
docker exec laravel-app php artisan cache:clear
docker exec laravel-app php artisan config:cache
docker exec laravel-app php artisan route:cache
docker exec laravel-app php artisan view:cache

# Restart queue worker to load new code
docker restart laravel-worker
```

**Note**: The `--build` flag ensures Docker rebuilds the image with your latest code changes. Without it, containers will use the old image with old code.

---

## Troubleshooting

### SSL/Certificate Errors

**Problem**: "SSL certificate error" or "Invalid certificate"

**Solutions**:
1. Verify Cloudflare SSL/TLS mode is set to **Full (strict)**
2. Check certificate files exist:
   ```bash
   docker exec nginx-webserver ls -la /etc/nginx/certs/
   ```
3. Verify certificate file permissions
4. Check Nginx logs:
   ```bash
   docker logs nginx-webserver -f
   ```

### Connection Refused or Timeout

**Problem**: Cannot connect to `domain.com:2053`

**Solutions**:
1. Verify port 2053 is open on your firewall:
   ```bash
   sudo ufw status
   sudo ufw allow 2053/tcp
   ```
2. Check if Nginx container is running:
   ```bash
   docker ps | grep nginx
   ```
3. Verify Cloudflare DNS is pointing to correct IP
4. Ensure Cloudflare proxy (orange cloud) is enabled

### 502 Bad Gateway

**Problem**: Nginx returns 502 error

**Solutions**:
1. Check if PHP-FPM container is running:
   ```bash
   docker ps | grep laravel-app
   ```
2. Check app container logs:
   ```bash
   docker logs laravel-app
   ```
3. Restart app container:
   ```bash
   docker restart laravel-app
   ```

### Database Connection Errors

**Problem**: "Could not connect to database"

**Solutions**:
1. Check PostgreSQL is running:
   ```bash
   docker ps | grep pgsql
   ```
2. Verify database credentials in `.env`
3. Test database connection:
   ```bash
   docker exec pgsql pg_isready -U your_username -d your_database
   ```
4. Check database logs:
   ```bash
   docker logs pgsql
   ```

### Real IP Not Detected

**Problem**: Application sees Cloudflare IPs instead of visitor IPs

**Solution**: The Nginx configuration already includes Cloudflare IP ranges via `set_real_ip_from` directives in `deployment/nginx/default.conf:18-38`. These are automatically configured.

To verify:
```bash
# Check Nginx configuration includes Cloudflare IPs
docker exec nginx-webserver cat /etc/nginx/conf.d/default.conf | grep set_real_ip_from
```

---

## Security Best Practices

### 1. Keep Certificates Secure
- Never commit certificate files (`.pem`, `.key`) to git
- Already protected via `.gitignore` in `deployment/nginx/certs/`
- Set proper file permissions:
  ```bash
  chmod 600 deployment/nginx/certs/*.key
  chmod 644 deployment/nginx/certs/*.pem
  ```

### 2. Cloudflare Security Settings

Enable these in Cloudflare Dashboard:

- **Security** → **Settings**:
  - Security Level: Medium or High
  - Challenge Passage: 30 minutes

- **SSL/TLS** → **Edge Certificates**:
  - Always Use HTTPS: On
  - HTTP Strict Transport Security (HSTS): Enable with max-age of 6 months

- **Security** → **Bots**:
  - Bot Fight Mode: On (for basic protection)

### 3. Database Security
- Use strong passwords (min 16 characters, mixed case, numbers, symbols)
- Don't expose PostgreSQL port externally (remove port mapping in production if not needed)
- Regular backups

### 4. Application Security
- Keep `APP_DEBUG=false` in production
- Regularly update dependencies: `composer update`
- Monitor logs for suspicious activity

---

## Certificate Renewal

### Cloudflare Origin Certificates

- **Validity**: 15 years from creation date
- **Renewal**: Before expiration, generate new certificates following Step 2
- **Set Reminder**: Add calendar reminder ~14 years from now to renew

To renew:
1. Generate new certificates in Cloudflare (same process as Step 2)
2. Replace old certificate files in `deployment/nginx/certs/`
3. Reload Nginx:
   ```bash
   docker exec nginx-webserver nginx -s reload
   ```

---

## Backup Strategy

### Database Backup

```bash
# Create backup
docker exec pgsql pg_dump -U your_username your_database > backup-$(date +%Y%m%d).sql

# Restore from backup
cat backup-20261119.sql | docker exec -i pgsql psql -U your_username -d your_database
```

### Application Files Backup

Since `storage/` uses a Docker volume, backup from the running container:

```bash
# Backup storage volume (uploads, logs, etc.)
docker run --rm \
  --volumes-from laravel-app \
  -v $(pwd):/backup \
  alpine tar czf /backup/storage-backup-$(date +%Y%m%d).tar.gz -C /var/www storage

# Restore storage volume
docker run --rm \
  --volumes-from laravel-app \
  -v $(pwd):/backup \
  alpine tar xzf /backup/storage-backup-20261119.tar.gz -C /var/www

# Backup .env file
cp .env .env.backup
```

**Alternative**: Access volume directly:
```bash
# View volume contents
docker exec laravel-app ls -la /var/www/storage

# Copy specific files out
docker cp laravel-app:/var/www/storage/app/public ./storage-backup
```

### Automated Backups

Consider setting up a cron job for daily backups:

```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * cd /path/to/project && docker exec pgsql pg_dump -U username dbname > backups/backup-$(date +\%Y\%m\%d).sql
```

---

## Performance Optimization

### Nginx Tuning

Already configured in `deployment/nginx/default.conf`:
- HTTP/2 enabled
- Gzip compression enabled
- SSL session caching
- Proper file size limits (300M for API, 15M for frontend)

### PHP Tuning

Edit `deployment/php/local.ini` if needed:
```ini
upload_max_filesize = 300M
post_max_size = 300M
memory_limit = 512M
max_execution_time = 300
```

Then rebuild containers:
```bash
docker-compose -f docker-compose.prod.yml up -d --build
```

### Laravel Optimizations

```bash
# Cache everything
docker exec laravel-app php artisan optimize

# Enable OPcache (already in PHP-FPM by default)
# Consider using Redis for cache/sessions in high-traffic scenarios
```

---

## Shutting Down

### Stop all services:
```bash
docker-compose -f docker-compose.prod.yml down
```

### Stop and remove volumes (⚠️ This deletes data):
```bash
docker-compose -f docker-compose.prod.yml down -v
```

---

## Additional Resources

- [Cloudflare Origin Certificates](https://developers.cloudflare.com/ssl/origin-configuration/origin-ca)
- [Cloudflare Supported Ports](https://developers.cloudflare.com/fundamentals/reference/network-ports/)
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

---

## Support

For issues specific to this deployment:
1. Check logs: `docker-compose -f docker-compose.prod.yml logs -f`
2. Review Troubleshooting section above
3. Verify Cloudflare settings match this guide
4. Test each component individually (database, app, webserver)
