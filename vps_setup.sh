#!/bin/bash
# VPS Setup Script for Lux E-Commerce

echo "Updating system..."
apt update && apt upgrade -y
apt install -y curl wget git unzip software-properties-common

echo "Installing Nginx & MariaDB..."
apt install -y nginx mariadb-server

echo "Installing PHP 8.2 & Extensions..."
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath

echo "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "Installing Node.js (v20)..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

echo "Creating Database and User..."
mysql -e "CREATE DATABASE IF NOT EXISTS lux_db;"
mysql -e "CREATE USER IF NOT EXISTS 'lux_user'@'localhost' IDENTIFIED BY 'SecretLuxPass123!';"
mysql -e "GRANT ALL PRIVILEGES ON lux_db.* TO 'lux_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "Setting up web directory..."
mkdir -p /var/www/lux
chown -R www-data:www-data /var/www/lux

echo "Setting up Nginx configuration..."
cat > /etc/nginx/sites-available/lux << 'EOF'
server {
    listen 80;
    server_name 46.250.229.164; # Using IP for now
    root /var/www/lux/frontend/dist; # Vue build directory
    index index.html;

    # Frontend Routing
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API Routing (Assuming Laravel is in /var/www/lux/backend)
    location /api {
        alias /var/www/lux/backend/public;
        try_files $uri $uri/ @api;
        
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            include fastcgi_params;
        }
    }

    location @api {
        rewrite /api/(.*)$ /api/index.php?/$1 last;
    }
}
EOF

ln -sf /etc/nginx/sites-available/lux /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl restart nginx

echo "Setting up Firewall..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "✅ VPS Provisioning Complete!"
