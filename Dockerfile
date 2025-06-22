FROM php:8.1-apache

# Copia los archivos a la carpeta del servidor
COPY public/ /var/www/html/

# Habilita mod_rewrite si lo necesitas
RUN a2enmod rewrite
