FROM chajr/php56-nginx-extended:latest

USER root

RUN rm /var/www/html/index.php

COPY --chown=nginx:nginx ./source /var/www/html
COPY --chown=nginx:nginx ./conf/default.conf /etc/nginx/conf.d/default.conf

COPY ./conf/php.ini "$PHP_INI_DIR/php.ini"

USER nginx

HEALTHCHECK --interval=20s --timeout=5s CMD curl -f http://127.0.0.1:8080/pl-PL/ || exit 1
