FROM wordpress:6.9.4-php8.2-apache

# Keep the image self-contained so local startup does not depend on large
# Windows bind mounts for third-party plugins and parent themes.
COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/wordpress/performance.conf /etc/apache2/conf-available/saber-performance.conf
COPY wordpress/wp-content/plugins/ /var/www/html/wp-content/plugins/
COPY wordpress/wp-content/themes/ /var/www/html/wp-content/themes/
COPY wordpress/wp-content/mu-plugins/ /var/www/html/wp-content/mu-plugins/

# The official image declares /var/www/html as a volume. Keep the custom
# calculator outside that volume and sync only this project-owned plugin at
# container startup so anonymous volumes cannot hide it.
COPY wordpress/wp-content/plugins/saber-net-price-calculator/ /opt/saber/plugins/saber-net-price-calculator/
COPY docker/wordpress/saber-entrypoint.sh /usr/local/bin/saber-entrypoint

RUN a2enmod headers expires rewrite \
	&& a2enconf saber-performance \
	&& chmod +x /usr/local/bin/saber-entrypoint

# Add WP-CLI for local maintenance tasks.
ADD https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar /usr/local/bin/wp
RUN chmod +x /usr/local/bin/wp

ENTRYPOINT ["saber-entrypoint"]
CMD ["apache2-foreground"]
