#!/bin/sh
set -eu
listen_port="${PORT:-80}"
sed -i -E "s/^Listen [0-9]+$/Listen ${listen_port}/" /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:([0-9]+)>/<VirtualHost *:${listen_port}>/" /etc/apache2/sites-available/000-default.conf
exec "$@"
