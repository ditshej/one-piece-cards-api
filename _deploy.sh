#!/bin/sh
set -e

PHP=${DEPLOY_PHP:-/opt/php83/bin/php}

current_branch=$(git rev-parse --abbrev-ref HEAD)
if [ "$current_branch" != "main" ]; then
    echo "Deploy aborted: server is on branch '$current_branch', expected 'main'." >&2
    echo "Fix on the server with: git checkout main" >&2
    exit 1
fi

if ! git -c core.fileMode=false diff-index --quiet HEAD --; then
    echo "Deploy aborted: uncommitted changes in the server working tree." >&2
    echo "Inspect on the server with: git status" >&2
    exit 1
fi

git pull --ff-only origin main

$PHP /usr/bin/composer install --no-interaction --optimize-autoloader --no-dev

$PHP artisan migrate --force

$PHP artisan optimize:clear
