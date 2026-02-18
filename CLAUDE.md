# CLAUDE.md — Project Context for Claude Code

## Project Overview

Coldplay Mumbai — A concert ticket booking platform built as a 1-day senior engineer assignment.
Demonstrates concurrency-safe booking, clean architecture, API-first design, and senior-level decision-making.

## Tech Stack

- **PHP**: 8.3+ (strict types everywhere)
- **Laravel**: 12 (streamlined structure — no Kernel.php)
- **Frontend**: Vue 3 + Inertia.js v2 + TypeScript + TailwindCSS
- **Database**: MySQL 8 (InnoDB for row-level locking)
- **Cache/Queue**: Redis 7
- **Testing**: Pest 4
- **Linting**: Laravel Pint (custom strict rules in `.pint.json`)
- **Infrastructure**: Docker Compose (app, nginx, mysql, redis, queue worker)
- **Local Dev**: Laravel Herd (Windows) + `npm run dev`

---

## Architecture Principles

1. **Service Layer** — business logic in `app/Services/`. Controllers are thin.
2. **Form Requests** — all validation in dedicated request classes, shared across web + API. Never inline validation.
3. **API-First** — every feature has a JSON API route. Inertia frontend uses same services.
4. **Strict Typing** — return types, param types, typed properties on every method and class.
5. **SOLID but Pragmatic** — no over-abstraction for a 1-day build. Document where you'd abstract more.

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Web/           # Inertia controllers (return Inertia::render)
│   │   └── Api/           # JSON API controllers (return JsonResponse)
│   └── Requests/          # Form Requests (shared by Web + API)
├── Services/              # Business logic (BookingService)
├── Models/                # Eloquent models
├── Mail/                  # Mailable classes
├── Jobs/                  # Queued jobs (SendBookingConfirmation)
├── Enums/                 # PHP 8.3 enums (BookingStatus, SeatStatus)
└── Exceptions/            # Custom exceptions
```

Do not create new base folders without approval. Check sibling files for conventions before creating new files.

---

## Key Design Decisions

### Concurrency: SELECT FOR UPDATE (Pessimistic Row-Level Locking)

- Wrap seat selection + booking in a DB transaction
- `SELECT ... FOR UPDATE` on the seat row acquires exclusive InnoDB row lock
- Check seat status === available AFTER acquiring lock
- Update seat + create booking atomically
- Other concurrent requests block until lock releases, then see updated status

### Idempotency

- Client sends `idempotency_key` (UUID) with every booking request
- Server checks for existing booking with same key before processing
- Retries with same key return original booking, never duplicates

---

## PHP Conventions

- `declare(strict_types=1)` at the top of every PHP file.
- Always use explicit return type declarations on all methods and functions.
- Always use PHP type hints for all method parameters.
- Use PHP 8 constructor property promotion. No empty constructors.
- Enum keys must be TitleCase: `Available`, `Locked`, `Booked`.
- Prefer PHPDoc blocks over inline comments. No comments within code unless logic is exceptionally complex.
- Use descriptive names: `isRegisteredForDiscounts`, not `discount()`.
- Always use curly braces for control structures, even single-line bodies.

```php
// Good
protected function isAccessible(User $user, ?string $path = null): bool
{
    //
}

// Good — constructor promotion
public function __construct(
    private readonly BookingService $bookingService,
) {}
```

---

## Laravel 12 Conventions

- **No `app/Http/Kernel.php`**. Middleware configured in `bootstrap/app.php` via `Application::configure()->withMiddleware()`.
- **No `app/Console/Kernel.php`**. Console config in `bootstrap/app.php` or `routes/console.php`.
- Console commands in `app/Console/Commands/` are auto-discovered.
- Service providers registered in `bootstrap/providers.php`.
- Use `php artisan make:` commands to create files. Pass `--no-interaction` always.
- Model casts should use the `casts()` method, not the `$casts` property.

### Database

- Always use Eloquent models and relationships. Avoid `DB::` facade — prefer `Model::query()`.
- Use eager loading to prevent N+1 queries.
- Relationship methods must have return type hints.
- When modifying columns in migrations, include ALL previously defined attributes or they'll be dropped.

### Controllers & Validation

- Always create Form Request classes. Never inline validation in controllers.
- Check sibling Form Requests for convention (array vs string rules).

### Queues

- Use queued jobs with `ShouldQueue` interface for time-consuming operations (emails, notifications).

### Configuration

- Never use `env()` outside config files. Always use `config('key')`.

### URL Generation

- Prefer named routes and the `route()` function.

---

## Frontend Conventions

- Inertia v2 — use `Inertia::render()` for server-side routing.
- Pages live in `resources/js/pages`.
- Wayfinder generates TypeScript functions for Laravel routes. Import from `@/actions/` or `@/routes/`.
- If a frontend change isn't visible, `npm run build` or `npm run dev` may be needed.

---

## Testing (Pest 4)

- Create tests: `php artisan make:test --pest {name}` (feature by default, `--unit` for unit tests).
- Most tests should be feature tests.
- Use model factories with custom states. Check if a factory state exists before manually setting up models.
- Use `fake()->` for Faker data. Follow existing convention for `$this->faker` vs `fake()`.
- Do NOT delete tests without approval.
- Every change must be tested. Run minimum tests needed:

```bash
php artisan test --compact                          # All tests
php artisan test --compact --filter=Booking         # Filtered
php artisan test --compact tests/Feature/BookingTest.php  # Specific file
```

---

## Formatting (Pint)

Run before finalizing any changes:

```bash
./vendor/bin/pint --dirty        # Fix only changed files
./vendor/bin/pint                # Fix all
./vendor/bin/pint --test         # Check without fixing
```

---

## Commands (Local Dev)

```bash
npm run dev                      # Vite dev server
php artisan queue:work redis     # Queue worker for emails
php artisan test --compact       # Run Pest tests
./vendor/bin/pint --dirty        # Fix formatting on changed files
```

## Commands (Docker — for evaluators)

```bash
make setup    # One-command: build, migrate, seed
make test     # Run tests
make fresh    # Nuclear reset
```

---

## Commit Convention

Format: `type: description`
Types: `init`, `infra`, `db`, `feat`, `api`, `test`, `quality`, `docs`, `fix`

---

## Key Files

- `app/Services/BookingService.php` — THE core file. Seat locking, booking creation, idempotency.
- `app/Models/Seat.php` — status enum, scoped queries for availability.
- `app/Models/Booking.php` — belongs to User + Event, has idempotency_key.
- `app/Enums/SeatStatus.php` — Available, Locked, Booked.
- `app/Enums/BookingStatus.php` — Confirmed, Cancelled.
