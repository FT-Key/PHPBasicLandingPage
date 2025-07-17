# Usa la imagen base oficial de PHP con Apache
FROM php:8.2-apache

# Instala extensiones necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring

# Habilitar mod_rewrite de Apache (si usás URLs amigables)
RUN a2enmod rewrite

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar todo el código a la carpeta del servidor
COPY . /var/www/html

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html

# Mover al directorio del proyecto
WORKDIR /var/www/html

# Instalar dependencias (con flags para no interactuar y sin sugerencias)
RUN composer install --no-interaction --no-suggest --optimize-autoloader

# Exponer el puerto (opcional, Render lo configura solo)
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]