#!/bin/sh
set -e

if [ ! -f .env.deploy ]; then
    echo "Error: .env.deploy not found. Copy .env.deploy.example and fill in your credentials."
    exit 1
fi

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "Usage: ./create-token.sh \"App Name\" \"email@example.com\""
    exit 1
fi

set -a
. .env.deploy
set +a

ssh -p "$DEPLOY_PORT" "$DEPLOY_USER@$DEPLOY_HOST" -t "cd $DEPLOY_PATH && php artisan token:create '$1' '$2'"
