# Pake base image PHP 7.4 yang udah ada server Apache-nya
FROM php:7.4-apache

# Install ekstensi database biar bisa konek ke MySQL lu
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifin rewrite module buat Apache (wajib kalau kode lu pake .htaccess)
RUN a2enmod rewrite

# Pindahin semua kode lu dari GitHub ke folder web server
COPY . /var/www/html/

# Benerin hak akses foldernya biar server bisa baca kodenya
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Buka port 80 biar Zeabur tau jalurnya
EXPOSE 80
