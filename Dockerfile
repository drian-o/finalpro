# Pakai mesin PHP dan Apache (kayak cPanel)
FROM php:7.4-apache

# Aktifkan ekstensi database MySQLi
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan fitur baca .htaccess (URL Rewrite)
RUN a2enmod rewrite

# Copy semua kodingan lu ke folder server
COPY . /var/www/html/

# Beri akses ke .htaccess
RUN echo "<Directory /var/www/html>\n\tAllowOverride All\n</Directory>" > /etc/apache2/conf-available/override.conf
RUN a2enconf override

# Buka port standar web
EXPOSE 80
