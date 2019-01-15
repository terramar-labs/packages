FROM composer

FROM webdevops/php-nginx:7.2

ENV WEB_DOCUMENT_ROOT=/app/web
ENV COMPOSER_ALLOW_SUPERUSER 1

ENV PACKAGES_NAME='Terramar Labs'
ENV PACKAGES_HOMEPAGE='https://github.com/terramar-labs/packages'
ENV PACKAGES_CONTACT='contact@terramarlabs.com'
ENV PACKAGES_BASEPATH='https://localhost'
ENV PACKAGES_SECURE=false
ENV PACKAGES_USER=user
ENV PACKAGES_PASSWORD=password
ENV PACKAGES_PDO_DRIVER=pdo_sqlite
ENV PACKAGES_PDO_PATH=%app.root_dir%/database.sqlite
ENV PACKAGES_PDO_DBNAME=packages
ENV PACKAGES_REDIS_HOST='redis://redis'
ENV PACKAGES_REDIS_PORT=6379

WORKDIR /app

COPY --from=0 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y gettext-base procps \
    && rm -r /var/lib/apt/lists/*

COPY . /app
COPY ./docker/nginx/vhost.common.d/vhost.common.conf /opt/docker/etc/nginx/vhost.common.d/10-location-root.conf
RUN chown -R 1000:1000 /app
RUN composer install

RUN mkdir /root/.ssh \
    && ssh-keyscan -t rsa github.com >> /root/.ssh/known_hosts \
    && ssh-keyscan -t rsa bitbucket.org >> /root/.ssh/known_hosts \
    && ssh-keyscan -t rsa gitlab.com >> /root/.ssh/known_hosts

ENTRYPOINT envsubst < config.yml.tmpl > config.yml \
            && bin/console resque:worker:start \
            && /entrypoint supervisord
CMD []