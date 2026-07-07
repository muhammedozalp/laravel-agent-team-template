#!/bin/sh
# Runs from /docker-entrypoint.d/ before nginx starts: if no cert exists for
# $APP_HOST, generate a self-signed one covering the host + all subdomains.
# For a browser-trusted cert instead, run on the host:
#   mkcert -cert-file docker/nginx/certs/$APP_HOST.pem \
#          -key-file  docker/nginx/certs/$APP_HOST-key.pem \
#          "$APP_HOST" "*.$APP_HOST"
set -e

CERT_DIR=/etc/nginx/certs
[ -f "$CERT_DIR/$APP_HOST.pem" ] && exit 0

# nginx:alpine ships without the openssl CLI
command -v openssl >/dev/null 2>&1 || apk add --no-cache openssl >/dev/null

echo "Generating self-signed TLS cert for $APP_HOST + *.$APP_HOST ..."
mkdir -p "$CERT_DIR"
openssl req -x509 -nodes -newkey rsa:2048 -days 825 \
    -keyout "$CERT_DIR/$APP_HOST-key.pem" \
    -out "$CERT_DIR/$APP_HOST.pem" \
    -subj "/CN=$APP_HOST" \
    -addext "subjectAltName=DNS:$APP_HOST,DNS:*.$APP_HOST"
