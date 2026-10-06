#!/bin/sh
set -eu

plugin_source='/opt/saber/plugins/saber-net-price-calculator'
plugin_target='/var/www/html/wp-content/plugins/saber-net-price-calculator'

mkdir -p "$(dirname "$plugin_target")"
rm -rf "$plugin_target"
cp -a "$plugin_source" "$plugin_target"
chown -R www-data:www-data "$plugin_target"

exec docker-entrypoint.sh "$@"
