# Quickstart Guide: Ajeer Assessment API

This document provides step-by-step instructions for checking and starting the **ajeer-assessment** API environment, seeding mock data, and executing test suites.

---

## 1. Prerequisites Check
Ensure the following are installed locally:
- **PHP 8.3+** (`php -v`)
- **Composer** (`composer -v`)
- **MySQL 8.0+** or SQLite (for local rapid testing)

---

## 2. Initial Setup
Run the standard Laravel setup commands:

```bash
# 1. Install dependencies
composer install

# 2. Duplicate environment template
cp .env.example .env

# 3. Generate secure application key
php artisan key:generate

# 4. Create database and run migrations & seeders
php artisan migrate:fresh --seed
```

---

## 3. Configuration & Multi-Gateway Rules
The payment availability rules reside in `config/payments.php`. You can override gateway status directly in `.env`:

```env
MOYASAR_ENABLED=true
STRIPE_ENABLED=true
TAP_ENABLED=true
```

---

## 4. Running the Test Suite
This project strictly enforces **Pest** functional testing syntax:

```bash
# Run all Pest tests
php artisan test

# Run with test coverage report
php artisan test --coverage
```

---

## 5. Mock Seeding details
The `DatabaseSeeder` populates:
- **Services**: e.g., AC maintenance, pipe repair, plumbing, house cleaning.
- **Packages**: e.g. "Full Maintenance Bundle" containing AC repair and plumbing.
- **Mock User**: Credentials for testing API flows immediately.
