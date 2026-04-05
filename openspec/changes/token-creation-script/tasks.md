## 1. create-token.sh Script

- [ ] 1.1 Create `create-token.sh` in the project root: validate `.env.deploy` exists, validate name and email arguments are provided, source `.env.deploy`, SSH into the server and run `php artisan token:create "$1" "$2"` in `DEPLOY_PATH`
- [ ] 1.2 Make `create-token.sh` executable (`chmod +x`)

## 2. API Documentation (Scramble)

- [ ] 2.1 Update `info.description` in `config/scramble.php`: add a sentence explaining that all endpoints require a Bearer token and that consumers must contact the owner to request access

## 3. README

- [ ] 3.1 Replace `README.md` with a project-specific document covering: project purpose, base URL, authentication (Bearer token + how to request access), endpoint overview (link to `/docs/api`), deployment (`./deploy.sh`), and token creation (`./create-token.sh`)
