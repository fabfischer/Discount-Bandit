FROM php:8.2-fpm
ARG WORKDIR=/var/www/html
ARG CACHE_DIR=/var/www/.cache
ENV DOCUMENT_ROOT=${WORKDIR}
ENV LARAVEL_PROCS_NUMBER=1
ENV NODE_MAJOR=20
ARG HOST_UID=1000
ENV USER=www-data
ENV TZ=Europe/Berlin
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    wget \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmemcached-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    librdkafka-dev \
    libpq-dev \
    openssh-server \
    zip \
    unzip \
    tzdata \
    supervisor \
    sqlite3  \
    nano \
    cron
# Install Nodejs
RUN apt-get update && apt-get install -y ca-certificates curl gnupg
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
# RUN apt-get update
# RUN apt-get install nodejs -y

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
# Install Kafka
RUN git clone https://github.com/arnaud-lb/php-rdkafka.git\
    && cd php-rdkafka \
    && phpize \
    && ./configure \
    && make all -j 5 \
    && make install

# Install Rdkafka and enable it
RUN docker-php-ext-enable rdkafka \
     && cd .. \
    && rm -rf /php-rdkafka

# Install PHP extensions zip, mbstring, exif, bcmath, intl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install zip mbstring exif pcntl bcmath -j$(nproc) gd intl

# imagemagick
RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends
RUN pecl install imagick && docker-php-ext-enable imagick

# Install Redis and enable it
RUN pecl install redis  && docker-php-ext-enable redis

# Install the php memcached extension
RUN pecl install memcached && docker-php-ext-enable memcached

# Install the PHP pdo_mysql extention
RUN docker-php-ext-install pdo_mysql

# Install the PHP pdo_pgsql extention
RUN docker-php-ext-install pdo_pgsql

# Install PHP Opcache extention
# RUN docker-php-ext-install opcache


# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Set working directory
WORKDIR $WORKDIR

RUN rm -Rf /var/www/* && \
mkdir -p /var/www/html && \
mkdir -p /var/www/.cache

ADD ./php.ini $PHP_INI_DIR/conf.d/
# ADD ./opcache.ini $PHP_INI_DIR/conf.d/
ADD ./supervisord.conf /etc/supervisor/supervisord.conf
ADD ./www.conf /usr/local/etc/php-fpm.d/www.conf

COPY ./entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN ln -s /usr/local/bin/entrypoint.sh /

ENTRYPOINT ["entrypoint.sh"]

RUN usermod -u ${HOST_UID} www-data
RUN groupmod -g ${HOST_UID} www-data

RUN useradd -ms /bin/bash --no-user-group -g 1000 -u 1337 sail

RUN chmod -R 755 $WORKDIR
RUN chmod -R 755 $CACHE_DIR
RUN chown -R www-data:www-data $WORKDIR
RUN chown -R www-data:www-data $CACHE_DIR

#USER www-data
ADD ./crontab.txt /crontab.txt
ADD ./crontab.txt /etc/cron.d/crontab

#USER root
RUN chmod 0644 /etc/cron.d/crontab \
    && touch /var/log/cron.log \
    && crontab /etc/cron.d/crontab

USER root
EXPOSE 9000
CMD [ "entrypoint" ]
