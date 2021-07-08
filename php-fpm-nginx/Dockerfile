FROM php:8.0-fpm
ENV DEBIAN_FRONTEND=noninteractive
# Install dependencies, libssl1.1-dev ?
RUN apt-get update && apt-get install -y \
    xvfb \
    wget \
    openssl \
    xorg \
    libssl1.1 \
    ca-certificates \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    dcmtk \
    nginx \
    supervisor \
    libpq-dev \
    geoip-database \
    libgeoip1
    
# https://stackoverflow.com/questions/30860516/only-allow-certain-countries-traffic-access-specific-page    
# Install Postgre PDO
RUN wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
RUN tar xvJf wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
RUN cp wkhtmltox/bin/wkhtmlto* /usr/bin/
# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
COPY php.ini /usr/local/etc/php/php.ini
COPY mime.types /etc/nginx/mime.types

# adjustments to php.ini base on the production version.
# application/wasm  wasm is missing in mime.types for the nginx distro

# Install extensions and configure

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-configure zip
RUN docker-php-ext-install pdo_mysql zip exif pcntl gd sockets
# Install Postgre PDO
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# RUN sed -E -i -e 's/max_execution_time = 1200/max_execution_time = 120/' /etc/php.ini \
#  && sed -E -i -e 's/memory_limit = 128M/memory_limit = 512M/' /etc/php.ini \
#  && sed -E -i -e 's/post_max_size = 8M/post_max_size = 64M/' /etc/php.ini \
#  && sed -E -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 64M/' /etc/php.ini

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY entrypoint.sh /
ENTRYPOINT ["/bin/bash","/entrypoint.sh"]