# Pakai mesin PHP dan Apache
FROM php:8.2-apache

# 🔥 REVISI 1: Gabungkan instalasi dasar di atas agar kena cache Docker (Build jadi super cepat)
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

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

# 🔥 REVISI 2: Pake metode EOF biar penulisan syntax Apache aman tanpa ngetik \n\t manual
RUN cat << 'EOF' > /etc/apache2/conf-available/override.conf
<Directory /var/www/html>
    AllowOverride All
</Directory>
EOF

RUN a2enconf override

# ========================================================
# FIX SAAS MULTI-TENANT: INLINE GENERATE VHOST CONFIG (ANTI-NOT FOUND)
# ========================================================
# Membuat file vhost secara langsung tanpa perlu file vhost.conf eksternal di GitHub
RUN cat << 'EOF' > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ServerAlias *
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
# ========================================================

# Buka port standar web
EXPOSE 80
