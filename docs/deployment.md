# Deployment

## Target

| | |
| --- | --- |
| Server | Metanet (Plesk). Host, user and SSH port: ask the maintainer |
| Domain | `https://cards.optcg.ch` |
| Checkout | `/cards.optcg.ch` (Plesk chroot, `~` is `/`) |
| Document root | `cards.optcg.ch/public` |
| PHP | domain 8.3, shell `/opt/php83/bin/php` (plain `php` in the shell is older) |
| Database | SQLite `database/database.sqlite` |
| Git remote on the server | `git@github-op-cards:ditshej/one-piece-cards-api.git` |
| Deploy key | `~/.ssh/op-cards-deploy` on the server, read-only on GitHub ("plesk metanet") |

The SQLite database holds the card data, the API tokens (Sanctum, one user per client app), sessions, cache and queue. It is never copied between environments: card data comes through `cards:sync`, tokens are issued on the server with `./create-token.sh`.

## Deploy

```bash
cp .env.deploy.example .env.deploy   # once, values below
./deploy.sh
```

`.env.deploy` (stays on the Mac, gitignored):

```
DEPLOY_USER=<ssh user>
DEPLOY_HOST=<ssh host>
DEPLOY_PORT=<ssh port>
DEPLOY_PATH=/cards.optcg.ch
DEPLOY_PHP=/opt/php83/bin/php
```

Values for user, host and port: ask the maintainer. No password, no token: SSH uses your key.

What happens: `npm run build` on the Mac, rsync of `public/build` to the server, then SSH to the server, where `_deploy.sh` checks the branch (`main`) and a clean working tree, pulls with `--ff-only` and runs `composer install --no-dev`, `migrate --force` and `optimize:clear`.

## Card data

`cards:sync` runs on the Mac. It uploads the local vegapull JSON to `storage/vegapull` on the server and runs `cards:import` there. It reads `SYNC_*` from the local `.env` (not from `.env.deploy`):

```
SYNC_HOST=<ssh host>
SYNC_USER=<ssh user>
SYNC_PORT=<ssh port>
SYNC_PATH=/cards.optcg.ch
SYNC_PHP=/opt/php83/bin/php
```

```bash
php artisan cards:sync            # sync existing local JSON
php artisan cards:sync --fetch    # refresh from Bandai first, then sync
```

## Scheduler

There is no scheduled task for `cards.optcg.ch`. Keep `IMPORT_SCHEDULE_ENABLED=false` in the server `.env` and do not add a Plesk cron for `schedule:run`.

The only scheduler job is the weekly `cards:import`. It imports only the JSON files that are already on the server (`storage/vegapull`), and `cards:sync` already runs the import after each upload. New card data comes from the Mac through `cards:sync`.

`schedule:list` still lists `cards:import` when the flag is `false`. The job does not run.

## API tokens

`./create-token.sh` runs on the Mac and calls the token commands on the server over SSH. It reads `.env.deploy`.

```bash
./create-token.sh "App Name" "email@example.com"   # prints the token once
./create-token.sh --list
./create-token.sh --revoke "App Name"
```

## Checks

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://cards.optcg.ch/up                 # 200
curl -s -o /dev/null -w "%{http_code}\n" https://cards.optcg.ch/.env               # 404
curl -s -o /dev/null -w "%{http_code}\n" https://cards.optcg.ch/.git/config        # 404
curl -s -o /dev/null -w "%{http_code}\n" https://cards.optcg.ch/api/v1/packs       # 401 (no token)
curl -s -H "Authorization: Bearer <test token>" -H "Accept: application/json" \
    https://cards.optcg.ch/api/v1/packs | head -c 300                              # JSON with packs
curl -s -o /dev/null -w "%{http_code}\n" https://cards.optcg.ch/docs/api           # 200
```

`curl` must run without `-k`: the certificate is valid. For `/mcp`, connect an MCP client to `https://cards.optcg.ch/mcp` with the test token as Bearer token and list the tools. Revoke the test token afterwards with `./create-token.sh --revoke "<test app name>"`.

## Rollback

`git revert` on `main`, then `./deploy.sh` again. Never change files on the server: the next deploy aborts on a dirty working tree. If the card data is wrong, run `php artisan cards:sync` again.

## When the deploy aborts

- "server is on branch …": on the server `git checkout main`.
- "uncommitted changes …": on the server `git status`, find out where the change came from, then `git checkout -- <file>`.
- `git pull` not a fast-forward: someone committed on the server. Do not merge there; ask.
- Permission denied (publickey) towards GitHub: `ssh -T git@github-op-cards` on the server; is the deploy key still on GitHub?
- Laravel fails with odd errors from an old state: on the server `rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php`, then `./deploy.sh` again.

## First setup

Steps to set up `cards.optcg.ch`. Done by the maintainer in Plesk and on the server.

### 1. Site in Plesk

Plesk → Websites & Domains → add the subdomain `cards.optcg.ch` (a new site, not an alias).

In the hosting settings:

1. Document root: `cards.optcg.ch/public`. Set it before the checkout.
2. PHP version: 8.3 (the same as the shell PHP `/opt/php83/bin/php`).
3. SSL: Let's Encrypt, only "secure the domain name", without "www subdomain" and without wildcard.

Check: `curl -sI https://cards.optcg.ch/` runs through without `-k`.

### 2. Checkout

The deploy key and the `github-op-cards` host in `~/.ssh/config` already exist on the server. Check:

```bash
ssh -T git@github-op-cards
```

Expect `Hi ditshej/one-piece-cards-api! …`. Then:

```bash
cd ~/cards.optcg.ch
ls -a . public
rm index.html public/index.html      # only the Plesk placeholders that exist
git init
git remote add origin git@github-op-cards:ditshej/one-piece-cards-api.git
git fetch origin main
git checkout -b main --track origin/main
echo /public/.well-known/ >> .git/info/exclude
git status --short                   # empty
```

Keep `public/.well-known/` (Let's Encrypt renewal).

### 3. App on the server

```bash
cd ~/cards.optcg.ch
cp .env.example .env
vi .env
touch database/database.sqlite
/opt/php83/bin/php /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction
/opt/php83/bin/php artisan key:generate --force
/opt/php83/bin/php artisan migrate --force
/opt/php83/bin/php artisan optimize:clear
```

Keys to set in `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cards.optcg.ch
DB_CONNECTION=sqlite
IMPORT_SCHEDULE_ENABLED=false
```

### 4. First deploy and data

On the Mac: set `.env.deploy` and `SYNC_*` as above, then:

```bash
./deploy.sh
php artisan cards:sync
```

### 5. Checks

No scheduled task (see "Scheduler"). Run all checks (see "Checks").

### Checklist

- [ ] Site `cards.optcg.ch` in Plesk, document root, PHP 8.3, Let's Encrypt
- [ ] Checkout on the server, placeholders removed, `git status` clean
- [ ] `.env`, Composer, `key:generate`, migrations
- [ ] `.env.deploy` and `SYNC_*` on the Mac, first `./deploy.sh` ran through
- [ ] `cards:sync` ran through
- [ ] `IMPORT_SCHEDULE_ENABLED=false`, no scheduled task for `schedule:run`
- [ ] Checks: `/up`, `/api/v1` with a test token, `/docs/api`, `/mcp`; `.env` and `.git/config` answer 404
