#!/bin/sh
set -e

if [ ! -f .env.deploy ]; then
    echo "Error: .env.deploy not found. Copy .env.deploy.example and fill in your credentials."
    exit 1
fi

set -a
. .env.deploy
set +a

PHP=${DEPLOY_PHP:-php}

if [ "$1" = "--revoke" ]; then
    if [ -z "$2" ]; then
        echo "Usage: ./create-token.sh --revoke \"App Name\""
        exit 1
    fi

    ssh -p "$DEPLOY_PORT" "$DEPLOY_USER@$DEPLOY_HOST" "cd $DEPLOY_PATH && $PHP artisan token:revoke \"$2\""
    exit 0
fi

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "Usage: ./create-token.sh \"App Name\" \"email@example.com\""
    echo "       ./create-token.sh --revoke \"App Name\""
    exit 1
fi

ssh -p "$DEPLOY_PORT" "$DEPLOY_USER@$DEPLOY_HOST" "cd $DEPLOY_PATH && $PHP artisan token:create \"$1\" \"$2\""
exit 0
