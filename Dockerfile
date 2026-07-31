FROM php:8.2-apache

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite headers

# Configurar o diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos do projeto para o container
COPY . /var/www/html/

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expor a porta 80
EXPOSE 80
