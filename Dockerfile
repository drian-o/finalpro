# Pakai mesin PHP dan Apache
FROM php:8.2-apache

# Aktifkan ekstensi database MySQLi
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan fitur baca .htaccess (URL Rewrite)
RUN a2enmod rewrite

# Copy semua kodingan lu ke folder server
COPY . /var/www/html/

# ========================================================
# FIX UPLOAD GAMBAR (HAK AKSES FOLDER & UKURAN FILE)
# ========================================================
# 1. Berikan hak akses folder kodingan ke Apache (www-data)
RUN chown -R www-data:www-data /var/www/html

# 2. Naikkan batas ukuran upload PHP menjadi 64MB
RUN echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini
# ========================================================

# Beri akses ke .htaccess
RUN echo "<Directory /var/www/html>\n\tAllowOverride All\n</Directory>" > /etc/apache2/conf-available/override.conf
RUN a2enconf override

# ========================================================
# FIX SAAS MULTI-TENANT: BUKA GERBANG ALIAS APACHE
# ========================================================
# Menyuruh Apache menerima semua domain asing (*) yang masuk ke port 80 kontainer ini
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo "ServerAlias *" >> /etc/apache2/apache2.conf
# ========================================================

# Buka port standar web
EXPOSE 80
