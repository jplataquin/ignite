<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Nginx Deployment Guide

This guide provides step-by-step instructions for deploying and running this application on an Nginx web server running on a Linux system (such as Ubuntu 22.04 or 24.04).

### 1. System Prerequisites

Ensure your server is updated and has the necessary system packages installed:

```bash
sudo apt update && sudo apt upgrade -y
```

Install PHP 8.3, Nginx, Git, Composer, and the required PHP extensions:

```bash
sudo apt install -y nginx git unzip curl \
    php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-sqlite3 \
    php8.3-xml php8.3-bcmath php8.3-curl php8.3-mbstring php8.3-zip \
    php8.3-intl php8.3-gd php8.3-xmlrpc php8.3-soap
```

#### Node.js & NPM (for compiling assets)
Install Node.js (v20+) and NPM:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2. Clone the Application

Clone this repository into your web directory (e.g., `/var/www/ignite`):

```bash
sudo mkdir -p /var/www/ignite
sudo chown -R $USER:www-data /var/www/ignite
git clone https://github.com/your-username/ignite.git /var/www/ignite
cd /var/www/ignite
```

### 3. Application Setup

Use the built-in setup script to run Composer install, generate the app key, compile assets, and migrate your database:

```bash
# Set up .env and install dependencies
composer run setup
```

Alternatively, you can run the steps manually:

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Create environment configuration
cp .env.example .env
php artisan key:generate

# Install Node dependencies & build front-end assets
npm install --ignore-scripts
npm run build

# Run database migrations
php artisan migrate --force
```

Configure your database and external service settings in `/var/www/ignite/.env` using your favorite text editor:
```bash
nano .env
```

### 4. File Permissions

The web server user (`www-data`) needs read/write access to specific Laravel directories:

```bash
# Set ownership to the deployer user and the web server group
sudo chown -R $USER:www-data /var/www/ignite

# Grant read/write permissions to storage and bootstrap/cache
sudo chmod -R 775 /var/www/ignite/storage
sudo chmod -R 775 /var/www/ignite/bootstrap/cache
```

### 5. Nginx Server Configuration

Create a new Nginx configuration file for your application:

```bash
sudo nano /etc/nginx/sites-available/ignite
```

Paste the following Nginx virtual host configuration. Replace `your-domain.com` with your actual domain name or server IP address, and adjust the PHP-FPM socket path if needed:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/ignite/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Handle main application routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Disable logging for favicon and robots
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # Pass PHP scripts to PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the configuration and restart Nginx:

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/ignite /etc/nginx/sites-enabled/

# Test Nginx configuration for syntax errors
sudo nginx -t

# Reload Nginx to apply changes
sudo systemctl reload nginx
```

### 6. Production Optimizations

To maximize the performance of your production application, run the following cache commands:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Optional Services

#### Cron (Task Scheduler)
To run Laravel's scheduled tasks, add the following cron job to your server:

```bash
# Open crontab editor
crontab -e
```

And add this line:
```cron
* * * * * cd /var/www/ignite && php artisan schedule:run >> /dev/null 2>&1
```

#### Supervisor (Queue Workers)
If this application processes background jobs via queues, install Supervisor to manage queue workers:

```bash
sudo apt install -y supervisor
```

Create a configuration file:
```bash
sudo nano /etc/supervisor/conf.d/ignite-worker.conf
```

Add the following:
```ini
[program:ignite-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ignite/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ignite/storage/logs/worker.log
stopwaitsecs=3600
```

Start the Supervisor daemon and load your configuration:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ignite-worker:*
```

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
