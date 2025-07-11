# Imagen base oficial de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto al contenedor
COPY . /var/www/html/

# Copiar archivo virtual host default si querés (opcional)
# COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Dar permisos correctos (opcional si tu framework lo necesita)
RUN chown -R www-data:www-data /var/www/html

# Habilitar módulos de Apache (opcional)
RUN a2enmod rewrite

# Exponer puerto 80
EXPOSE 80

# Instalar dependencias PHP
WORKDIR /var/www/html
RUN composer install

# Comando por defecto
CMD ["apache2-foreground"]