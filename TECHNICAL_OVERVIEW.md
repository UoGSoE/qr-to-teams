# Technical Overview

Last updated: 2026-06-16

## What This Is

A small service that turns a plain `GET` request (typically encoded in a QR code) into a queued `POST` to an MS Teams incoming webhook, so a scan or a link click posts a message into a Teams channel.

## Stack

- PHP 8.2 / Laravel 12.62
- Livewire 4 + Flux / Flux Pro 2 (admin UI)
- Tailwind CSS 4 + Vite 7
- Laravel Horizon 5 (queue) · Sanctum 4 · Socialite 5 (Keycloak SSO)
- `ohffs/laravel-msteams-alerts` 1.2 — actual Teams delivery (queued job)
- `simplesoftwareio/simple-qrcode` 4 — QR generation
- `vinkla/hashids` 13 — webhook shortcodes
- `uogsoe/ldap-laravel` 3 — LDAP lookup/auth (`\Ldap` facade)
- Sentry (error reporting)
- Tests: Pest 3

## Directory Structure

```
app/
  Http/Controllers/
    OutgoingWebhookController.php  # CORE: GET /api/help -> queued Teams post
    FormController.php             # /form: LDAP-authed self-service message
    MessageController.php          # "/" status/result page
    AdminController.php            # /admin dashboard (hosts webhook-editor)
    UserController.php             # /admin/users (hosts user-list)
    Auth/SSOController.php         # Keycloak SSO + local login + logout
  Livewire/
    WebhookEditor.php              # CRUD webhooks, build URLs, generate QR SVG
    UserList.php                   # LDAP lookup + create/delete staff users
  Console/Commands/                # webhook:create|delete|list|default
  Models/                         # User, Webhook
config/sso.php                     # SSO feature flags (see Authorization)
routes/{web,api}.php
```

## Domain Model

`User` and `Webhook` are independent — there is no relationship between them.

```
Webhook                         User
  url            (Teams URL)      username
  name                            forenames / surname
  shortcode      (Hashid of id)   email (unique)
  is_default                      is_staff
  called_count                    password
```

### Webhook (`app/Models/Webhook.php`)

- `shortcode` is `Hashids::encode(id)` — generated in `createNew()` after the row exists, so it is derived from the primary key.
- `createNew(url, name, isDefault)` flips any existing default off when a new default is set.
- `registerCalled()` bumps `called_count` and touches `updated_at` on each delivery.
- "Form" behaviour is driven by the `form=1` query param, not a DB column.

### User (`app/Models/User.php`)

- Populated either from Keycloak SSO or via LDAP lookup in the admin UI.
- `is_admin` is a boolean column (default `false`, cast to bool) gating the `SSO_ADMINS_ONLY` flag in `SSOController`. Deliberately **not** in `$fillable` (no mass assignment) — promote explicitly via `$user->is_admin = true; $user->save();` behind an authorisation check.

## The Core Flow

`GET /api/help` → `OutgoingWebhookController@store` (throttle 10/min by IP):

1. Requires one of `btext` or `etext` to be present.
2. `c=<shortcode>` selects the webhook; otherwise the `is_default` webhook is used.
3. Decodes the message from **`btext`** (base64) or **`etext`** (`encrypt()`).
4. `form=1` → redirect to `/form` to let a user supply their own message instead.
5. `MSTeamsAlert::to($webhook->url)->message($message)` dispatches the queued `SendToMSTeamsChannelJob`; `registerCalled()` updates stats.
6. Redirects to `/` (`MessageController`) with a base64 status message.

There is deliberately no plain-text param: messages are always at least base64-obfuscated (`btext`) or encrypted (`etext`), to make casual tampering with the URL less inviting.

### Message encodings

`WebhookEditor::generateWebhookUrl()` builds links preferring `etext` (encrypted), falling back to `btext` (base64), capping the URL at 2000 chars; beyond that it reports the URL is too long.

### /form flow

`FormController` (throttle 10/min): GET pre-fills a form with the decoded message; POST authenticates the user against LDAP (`\Ldap::authenticate` + `findUser`), builds a message containing their GUID, name, email and a Teams chat deep-link, then redirects to `api.help` with an encrypted `etext`.

## Authorization

Authentication is **Keycloak SSO** via Socialite (`SSOController`). Behaviour is controlled by `config/sso.php` flags (env-backed):

| Flag | Default | Effect |
|------|---------|--------|
| `enabled` | `true` | SSO on; when `false`, falls back to local username/password login |
| `autocreate_new_users` | `true` | Create a `User` on first successful SSO login |
| `allow_students` | `true` | When `false`, deny logins whose username looks like a matric number (`/^[0-9]{7}[a-z]$/i`) |
| `admins_only` | `false` | When `true`, only `is_admin` users may log in (see User gap above) |

- Logout invalidates the session and sets `force_reauth`, forcing Keycloak `max_age=0` re-auth on next SSO login.
- The `/admin` group is protected by the `auth` middleware. Guests are redirected to `login` (`bootstrap/app.php`).

## Routes Overview

### Web (`routes/web.php`)

| Route | Handler | Access |
|-------|---------|--------|
| `GET /` | `MessageController@show` | Public (status page) |
| `GET /login` | `SSOController@login` | Guest |
| `POST /login` | `SSOController@localLogin` | Local login (403 if SSO enabled) |
| `GET /login/sso` · `GET /auth/callback` | `SSOController` | SSO redirect/callback |
| `POST /logout` · `GET /logged-out` | `SSOController` | — |
| `GET/POST /form` | `FormController` | Public, throttle 10/min |
| `GET /admin` | `AdminController@index` → `home` (`@livewire('webhook-editor')`) | `auth` |
| `GET /admin/users` | `UserController@index` → `user.index` (`@livewire('user-list')`) | `auth` |

### API (`routes/api.php`)

| Endpoint | Notes |
|----------|-------|
| `GET /api/help` | Core webhook trigger. Throttle 10/min by IP. Named `api.help`. |

Health check at `GET /up`.

## Key Business Logic

| Location | Purpose |
|----------|---------|
| `OutgoingWebhookController` | Decode incoming request → dispatch Teams post |
| `MSTeamsAlert` (vendor `ohffs/...`) | `to()` accepts a full URL (passthrough) or config name; `message()` dispatches `SendToMSTeamsChannelJob` (`ShouldQueue`) — **needs a queue worker / Horizon to deliver** |
| `WebhookEditor` (Livewire) | Build webhook URLs, live-preview, download QR as SVG (1024px) |
| `Webhook::generateShortcode()` | Hashid of the row id |

## Console Commands

`webhook:create {name?} {url?} {default?}` · `webhook:delete` · `webhook:list` · `webhook:default` — manage webhooks from the CLI (interactive prompts when args omitted).

## Testing

- Framework: **Pest 3** (`tests/Feature/{ApiTest,AdminTest,UserManagementTest,CliTest}.php`).
- Pattern: feature tests bind a `FakeLdapConnection` in `beforeEach`, use `Bus::fake()` and assert `SendToMSTeamsChannelJob` was (not) dispatched, and `freezeTime()` for the stats assertions.
- Run: `lando test` (→ `php artisan test --parallel`), `lando testf <filter>`, or `composer test`.
- CI runs the suite against **mysql:5.7** in GitLab; there is also a GitHub workflow (`.github/workflows/main.yml`).

## Local Development

- **Lando** (`.lando.yml`): laravel recipe, PHP 8.2, redis cache, node 22, MailHog.
  - `lando mfs` — `migrate:fresh` + `db:seed --class=TestDataSeeder`
  - `lando horizon` — run the queue worker (required for Teams delivery)
  - `lando fixldap` — install insecure LDAP config for dev
- Alternatively `docker compose up` (see README) with `webhook:create` to seed a webhook.
- Deployment: **Deployer** (`deploy.php`) to `gently.cose.gla.ac.uk`; build runs `npm ci && npm run build`; `artisan:migrate` is currently disabled.
</content>
</invoke>
