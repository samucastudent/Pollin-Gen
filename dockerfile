FROM php:8.2-apache

# Instala extensões necessárias para o cURL funcionar corretamente
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl

# Copia os arquivos do projeto para o diretório do servidor
COPY . /var/www/html/

# Ajusta as permissões
RUN chown -R www-data:www-data /var/www/html/

# Expõe a porta 80
EXPOSE 80
