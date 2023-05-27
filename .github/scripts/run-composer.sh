#!/bin/sh
cp .env.ci .env
composer install --no-ansi --no-interaction --no-suggest --prefer-dist
