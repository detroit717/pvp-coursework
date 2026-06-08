#!/bin/sh
set -e

mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache storage/logs

exec "$@"
