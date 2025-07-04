# Usa una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilita mod_rewrite para Apache (si fuera necesario)
RUN a2enmod rewrite

# Instala extensiones necesarias: curl, mbstring, etc.
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instala paquetes del sistema necesarios (y Git por si usas Composer)
RUN apt-get update && apt-get install -y \
    zip unzip libzip-dev libpng-dev libonig-dev git && \
    docker-php-ext-install zip

# Copia los archivos de tu proyecto al contenedor
COPY . /var/www/html/

# Establece permisos (opcional)
RUN chown -R www-data:www-data /var/www/html/

# Puerto expuesto (Render.com espera 10000 o usa variable)
EXPOSE 80

# Comando por defecto (Apache)
CMD ["apache2-foreground"]
