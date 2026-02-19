# Coldplay Mumbai — Ticket Booking Platform

A high-demand concert ticket booking system built with Laravel 12, Vue 3, and Inertia.js. Designed to handle concurrent bookings with seat locking, real-time availability checks, and a smooth checkout flow.

## Tech Stack

- **Backend:** PHP 8.5, Laravel 12
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS
- **Database:** MySQL 8.4
- **Cache/Queue/Session:** Redis 8
- **Mail:** Mailpit (local)

## Quick Start (Docker)

### Prerequisites

- Docker & Docker Compose
- Git

### Setup

```bash
# Clone the repository
git clone <repository-url>
cd coldplay-mumbai

# Copy environment file
cp .env.example .env

# Start all services
docker-compose up -d --build

# Install PHP dependencies
docker-compose exec app composer install

# Install Node dependencies and build assets
docker-compose exec app npm install
docker-compose exec app npm run build

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations and seed the database
docker-compose exec app php artisan migrate:fresh --seed
```

### Access Points

| Service   | URL                    |
|-----------|------------------------|
| App       | http://localhost:8080  |
| Mailpit   | http://localhost:8025  |
| MySQL     | localhost:3307         |
| Redis     | localhost:6380         |

## Local Development (Without Docker)

### Prerequisites

- PHP 8.4+ with extensions: pdo_mysql, redis, gd, zip, bcmath
- Composer 2
- Node.js 22+
- MySQL 8+
- Redis 7+

### Setup

```bash
# Install dependencies
composer install
npm install

# Copy and configure environment
cp .env.example .env

# Update .env for local development:
# DB_HOST=127.0.0.1
# DB_USERNAME=root
# DB_PASSWORD=your_password
# REDIS_HOST=127.0.0.1
# MAIL_HOST=localhost
# SESSION_DRIVER=database
# CACHE_STORE=database
# QUEUE_CONNECTION=database

# Generate key and run migrations
php artisan key:generate
php artisan migrate:fresh --seed

# Build assets
npm run build

# Start the server
php artisan serve
```

## Project Structure

```
├── app/
│   ├── Enums/              # SeatStatus, BookingStatus, PaymentStatus
│   ├── Exceptions/         # BookingException
│   ├── Http/
│   │   ├── Controllers/    # EventController, BookingController
│   │   └── Requests/       # LockSeatsRequest
│   ├── Models/             # Event, Venue, Seat, Booking, Order
│   └── Services/           # BookingService (seat locking logic)
├── database/
│   ├── factories/          # Test data factories
│   ├── migrations/         # Database schema
│   └── seeders/            # Demo event data
├── resources/
│   └── js/
│       └── Pages/          # Vue components (Show.vue, Checkout.vue)
├── docker/
│   └── nginx/
│       └── default.conf    # Nginx configuration
├── tests/
│   ├── Feature/            # Integration tests
│   └── Unit/               # Unit tests
├── docker-compose.yml
├── Dockerfile
└── TRADEOFFS.md            # Architecture decisions
```

## Key Features

### Seat Selection & Locking

The checkout flow handles high-traffic scenarios:

1. **Availability Check** — Verifies selected seats are still available
2. **Seat Locking** — Temporarily reserves seats using database transactions with `lockForUpdate()`
3. **Checkout Redirect** — User proceeds to payment

### Checkout Flow (Frontend)

When a user clicks "Proceed to Checkout":

1. Modal appears with step indicators
2. API call checks seat availability
3. If available, seats are locked for the user
4. User is redirected to payment page
5. On failure, seat map refreshes automatically

### Queue Processing

Background jobs handle:
- Booking confirmation emails
- Seat lock expiration (releases unpaid seats)
- Payment webhook processing

## Common Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f
docker-compose logs -f queue    # Queue worker logs

# Run tests
docker-compose exec app php artisan test

# Fresh database with seeds
docker-compose exec app php artisan migrate:fresh --seed

# Clear all caches
docker-compose exec app php artisan optimize:clear

# Enter app container
docker-compose exec app bash
```

## Testing

```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test file
docker-compose exec app php artisan test tests/Feature/BookingTest.php

# Run with coverage
docker-compose exec app php artisan test --coverage
```

## Environment Variables

Key variables to configure:

| Variable | Description | Docker Default |
|----------|-------------|----------------|
| `DB_HOST` | Database host | `mysql` |
| `REDIS_HOST` | Redis host | `redis` |
| `MAIL_HOST` | SMTP host | `mailpit` |
| `QUEUE_CONNECTION` | Queue driver | `redis` |
| `SESSION_DRIVER` | Session driver | `redis` |

## Troubleshooting

### Port already in use

```bash
# Check what's using the port
netstat -ano | findstr :8080

# Change ports in docker-compose.yml if needed
```

### Permissions issues

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Queue not processing

```bash
# Check queue container
docker-compose ps
docker-compose logs queue

# Restart queue worker
docker-compose restart queue
```

### Assets not loading

```bash
# Rebuild assets
docker-compose exec app npm run build
```

## License

MIT
