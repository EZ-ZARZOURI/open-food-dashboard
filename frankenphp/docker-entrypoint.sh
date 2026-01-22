#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then

    
    if [ ! -f composer.json ]; then
        echo "Creating Symfony project..."
        rm -Rf tmp/
        composer create-project "symfony/skeleton $SYMFONY_VERSION" tmp --stability="$STABILITY" --prefer-dist --no-progress --no-interaction --no-install

        cd tmp
        cp -Rp . ..
        cd -
        rm -Rf tmp/

        composer require "php:>=$PHP_VERSION"
        composer config --json extra.symfony.docker 'true'

        if grep -q ^DATABASE_URL= .env; then
            echo 'To finish installation, press Ctrl+C and run: docker compose up --build --wait'
            sleep infinity
        fi
    fi

    # Installer les dépendances PHP
    if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
        echo "Installing PHP dependencies..."
        composer install --prefer-dist --no-progress --no-interaction
    fi

    php bin/console -V

    # Vérification et migrations base de données 
    if grep -q ^DATABASE_URL= .env; then
        echo 'Waiting for database to be ready...'
        ATTEMPTS_LEFT=60
        until [ $ATTEMPTS_LEFT -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
            sleep 1
            ATTEMPTS_LEFT=$((ATTEMPTS_LEFT - 1))
            echo "Waiting for DB... $ATTEMPTS_LEFT attempts left"
        done

        if [ $ATTEMPTS_LEFT -eq 0 ]; then
            echo "Database not reachable"
            exit 1
        fi

        # Migrations
        if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
            php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
        else
            php bin/console doctrine:schema:update --force
        fi

        # Fixtures
        if [ -f bin/console ]; then
            echo "Loading fixtures..."
            php bin/console doctrine:fixtures:load --no-interaction || true
        fi
    fi

    echo 'PHP app ready'

    # Node / npm 
    if [ -f package.json ]; then
        echo 'Installing Node dependencies...'
        npm install

        echo 'Starting frontend dev server...'
        npm run dev & 
    fi

    # FrankenPHP 
    echo 'Starting FrankenPHP...'
    exec frankenphp run --config /etc/frankenphp/Caddyfile --watch
fi

exec docker-php-entrypoint "$@"
