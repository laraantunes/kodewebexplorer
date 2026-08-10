FROM php:8.2-apache

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite headers

# Permitir passagem de UID e GID do host no momento do build (útil para mapeamento de volumes no Linux)
ARG PUID=33
ARG PGID=33
RUN usermod -u ${PUID} www-data && groupmod -g ${PGID} www-data

# Configurar o diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos do projeto para o container
COPY . /var/www/html/

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expor a porta 80
EXPOSE 80
