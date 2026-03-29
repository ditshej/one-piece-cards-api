## 1. Deploy Scripts

- [x] 1.1 Create `_deploy.sh` — remote script: git pull, composer install --no-dev, artisan migrate --force, artisan optimize:clear, artisan optimize
- [x] 1.2 Create `deploy.sh` — local script: source `.env.deploy`, SSH into server and run `_deploy.sh`
- [x] 1.3 Create `.env.deploy.example` with placeholder `DEPLOY_SSH_CONNECTION` and `DEPLOY_PATH`
- [x] 1.4 Add `.env.deploy` to `.gitignore`
- [x] 1.5 Make both shell scripts executable (`chmod +x`)

## 2. Roadmap Update

- [x] 2.1 Mark Changes 1–6 as done in `docs/implementation-roadmap.md`

## 3. Server Setup (Manual, One-Time)

- [x] 3.1 SSH into Metanet (`sshm`) and verify PHP 8.4 binary path (`ls /usr/bin/php*`) and Composer path
- [x] 3.2 Clone the repo into `~/op-cards.ditshej.ch` on the server
- [x] 3.3 Set Metanet document root to `~/op-cards.ditshej.ch/public` in the hosting panel
- [x] 3.4 Create `.env` on the server with production values (APP_KEY, APP_URL, APP_ENV=production, APP_DEBUG=false, DB_CONNECTION=sqlite)
- [x] 3.5 Run `php artisan key:generate` and `php artisan migrate --force` on the server
- [x] 3.6 Configure DNS: add A-Record `op-cards` → Metanet IP at the domain registrar

## 4. First Deploy & Verification

- [x] 4.1 Create local `.env.deploy` with real Metanet SSH credentials
- [x] 4.2 Run `bash deploy.sh` — verify it connects and completes without errors
- [x] 4.3 Verify `https://op-cards.ditshej.ch/up` returns HTTP 200
- [x] 4.4 Verify `https://op-cards.ditshej.ch/v1/packs` returns JSON
