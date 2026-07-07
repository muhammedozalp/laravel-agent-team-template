#!/usr/bin/env bash
# Runs once on first volume init: create the dedicated test database
# (phpunit.xml points here; RefreshDatabase wipes it every run).
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE DATABASE ${POSTGRES_DB}_testing OWNER $POSTGRES_USER;
EOSQL
