# plainenv API

> Laravel REST API backend for plainenv — team .env management SaaS.

## Project Overview

plainenv is a simple team environment variable manager. Developers store, share, and sync `.env` variables across environments (dev/staging/prod) and teammates.

Tagline: "The simplest way to manage your team's environment variables"

## Stack

- **PHP 8.3** / **Laravel 11**
- **MySQL** (Laragon local, Railway production)
- **Laravel Sanctum** — API token auth
- **AES-256 encryption** — all variable values encrypted at rest

## Architecture

```
plainenv-api/   ← this repo (Laravel)
plainenv-app/   ← React frontend (separate repo)
```

## Database Schema

```
users                   — auth
projects                — owned by a user
environments            — dev/staging/prod per project
variables               — key/value (value encrypted), per environment
members                 — user ↔ project pivot (role: owner/editor/viewer)
activity_logs           — audit trail (who changed what)
personal_access_tokens  — Sanctum CLI tokens
```

## API Structure

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me

GET    /api/projects
POST   /api/projects
GET    /api/projects/{id}
PUT    /api/projects/{id}
DELETE /api/projects/{id}

GET    /api/projects/{id}/environments
POST   /api/projects/{id}/environments
PUT    /api/projects/{id}/environments/{envId}
DELETE /api/projects/{id}/environments/{envId}

GET    /api/projects/{id}/environments/{envId}/variables
POST   /api/projects/{id}/environments/{envId}/variables
PUT    /api/projects/{id}/environments/{envId}/variables/{varId}
DELETE /api/projects/{id}/environments/{envId}/variables/{varId}
GET    /api/projects/{id}/environments/{envId}/export   ← returns .env file

GET    /api/projects/{id}/compare?from={envId}&to={envId}
GET    /api/projects/{id}/activity

POST   /api/projects/{id}/members          ← invite by email
DELETE /api/projects/{id}/members/{userId}
```

## Encryption

Variable values are encrypted using AES-256-CBC before saving to DB.
Key stored in `APP_KEY` (Laravel default). Never store plaintext values.

```php
// Encrypt before save
Crypt::encryptString($value)

// Decrypt on read
Crypt::decryptString($encrypted)
```

## Auth Flow

- Register/login returns Sanctum token
- All protected routes require: `Authorization: Bearer {token}`
- CLI tokens: named tokens via `/api/tokens` for `envpatch pull`

## Code Principles

- English only: code, comments, variable names, routes
- No over-engineering — keep controllers thin, logic in services if complex
- Always return consistent JSON: `{ data, message, errors }`
- Use Form Requests for validation
- Use API Resources for response formatting

## Local Dev

```bash
cd plainenv-api
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

MySQL via Laragon (localhost:3306, root, no password).

## Rules References

- Laravel conventions: `.claude/rules/laravel-rule.md`
- API design: `.claude/rules/api-rule.md`
- Security & encryption: `.claude/rules/security-rule.md`
- Database schema: `.claude/rules/database-rule.md`

## Related

- Frontend: `plainenv-app` (React + Tailwind, separate repo)
- CLI integration: `envpatch` npm package (`npx envpatch pull --token xxx`)
- GitHub: github.com/long260398/plainenv-api
