# Authentication & authorization

_How auth works in this template and which absences are deliberate. Flow details
live in the code (Fortify is headless — the app supplies actions, views, and
limiters); this guide is the map + the decisions._

## What ships (Fortify, all features on)

| Piece | Where |
|---|---|
| Feature switches | `config/fortify.php` (registration, reset, **enforced email verification**, 2FA, passkeys, password confirmation) |
| User actions | `app/Actions/Fortify/` (create user, reset password) + `app/Concerns/` (validation rules) |
| Page wiring + rate limiters | `app/Providers/FortifyServiceProvider.php` (login 5/min, 2FA 5/min, passkeys 10/min, **register 10/min**) |
| React pages | `resources/js/pages/auth/*.tsx` |
| Settings (profile/password/2FA/passkeys) | `routes/settings.php` + `app/Http/Controllers/Settings/` |
| Password policy | `AppServiceProvider::configureDefaults()` — production: min 12, mixed case, numbers, symbols, HIBP check |

## Hardening baked in (each has a regression test)

- **Password change kills every other session** — `AuthenticateSession` in the web
  group (`bootstrap/app.php`) + remember-token rotation +
  `Auth::logoutOtherDevices()` in `SecurityController::update`
  (`tests/Feature/Settings/SessionSecurityTest.php`).
- **Email change requires the current password** and **notifies the old address**
  (`ProfileUpdateRequest`, `App\Notifications\EmailChanged`,
  `tests/Feature/Settings/EmailChangeTest.php`).
- **Registration is rate limited** (10/min/IP — Fortify ships it unthrottled).
- **Auth audit trail:** every Login/Logout/Failed/Lockout/Registered/Verified/
  PasswordReset/OtherDeviceLogout event lands in `storage/logs/auth-*.log`
  (90 days) via `app/Subscribers/AuthEventSubscriber.php`.
- **Auth mail is queued** (`QueuedVerifyEmail`, `QueuedResetPassword`) — a slow
  SMTP provider can't block registration/reset requests.
- Production checklist: `SESSION_SECURE_COOKIE=true` (`deploy.md`).

## Deliberate absences (decide per project, don't relitigate)

- **Authorization / roles:** nothing ships — every verified user is equal. First
  need: prefer a boolean/enum column (`is_admin`) + policies; reach for
  `spatie/laravel-permission` only when a client actually has role matrices.
- **API auth (Sanctum):** absent — this is an Inertia session app. Add Sanctum
  only when a real API consumer appears (mobile app, third party).
- **Social login (Socialite):** absent — add per client request; it changes
  privacy policy and account-linking UX, so it's a product decision.
- **Admin panel:** see `../backlog/admin-panel-stance.md`.
- **GDPR extras** (export, anonymization, deletion grace period): the baseline
  password-confirmed hard delete ships; anything more is per-client policy.
