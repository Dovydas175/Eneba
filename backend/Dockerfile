FROM node:22-alpine AS frontend-build
WORKDIR /build
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends ca-certificates && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_mysql && a2enmod rewrite headers
COPY backend/public/ /var/www/html/
COPY --from=frontend-build /build/dist/ /var/www/html/
COPY backend/src/ /var/www/src/
COPY backend/apache.conf /etc/apache2/conf-available/api.conf
COPY backend/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint
ENV PORT=80
EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
CMD ["apache2-foreground"]
RUN a2enconf api && chown -R www-data:www-data /var/www
