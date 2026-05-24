# Implementation Plan: Ajeer Assessment Laravel API Setup

**Branch**: `001-laravel-api-setup` | **Date**: 2026-05-23 | **Spec**: [spec.md](file:///home/yunes-alkhaledi/AndroidStudioProjects/ajeer/ajeer_assigment/specs/001-laravel-api-setup/spec.md)

**Input**: Feature specification from `/specs/001-laravel-api-setup/spec.md`

**Note**: This plan is created by the `/speckit-plan` command.

---

## Summary

The primary objective is to implement a robust, production-ready RESTful Laravel 13 API for **ajeer-assessment**. The solution comprises two modules:
1. **Multi-Gateway Payment System**: Built using the Strategy Pattern (`PaymentGatewayInterface`, driver-based `PaymentManager`), featuring config-driven availability (filtering by city and module) for Moyasar, Stripe, and Tap.
2. **Trial Subscription + Service Booking Platform**: Enforcing a strict Repository Pattern and Service Layer. Includes user registration/login via Sanctum, one-time 14-day trials, maintenance service catalog, user-scoped carts, and service packages (which expand into their individual services inside the cart).

---

## Technical Context

**Language/Version**: PHP 8.3

**Primary Dependencies**:
- Laravel 13.x
- Laravel Sanctum (Token authentication)
- `spatie/laravel-data` (Type-safe DTOs replacing arrays in service layer and request validations)
- Pest (Laravel 13 default functional testing framework)

**Storage**: MySQL 8.0+ (utilizing ULID strings for primary and foreign keys, SoftDeletes)

**Testing**: Pest (functional syntax with `it()`, `describe()`, `expect()`, using `RefreshDatabase`)

**Target Platform**: Linux web-service

**Project Type**: RESTful Web Service

**Performance Goals**:
- Auth responses: < 2.0s
- Gateway filtering query: < 500ms
- Payment initiation callback: < 3.0s

**Constraints**:
- Strictly versioned API endpoints under `/api/v1/`.
- STRICT isolation of business logic (Services only) and data retrieval (Repositories only).
- Strict type declarations and Model::shouldBeStrict() enabled in local/testing.

**Scale/Scope**: Developer assessment targeting comprehensive design patterns, strict database structures, and high test coverage.

---

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Architecture**
- [x] §A-1 Controllers never touch Eloquent directly; all data access goes through Repository interfaces
- [x] §A-2 Business logic lives only in Services, never in Controllers/Repositories/migrations/seeders/routes
- [x] §A-3 Payment gateways implement `PaymentGatewayInterface` (Strategy Pattern), resolved via container
- [x] §A-4 All Service method signatures use `spatie/laravel-data` DTOs — no raw arrays
- [x] §A-5 All endpoints versioned under `/api/v1/` with `ApiVersionMiddleware`; controllers in `App\Http\Controllers\Api\V1`

**Code Quality**
- [x] §C-1 Every PHP file starts with `declare(strict_types=1);`
- [x] §C-2 Domain constants use PHP-backed Enums, not magic strings/integers
- [x] §C-3 ULID primary keys on all models — no auto-increment integers
- [x] §C-4 `Model::shouldBeStrict()` enabled in non-production
- [x] §C-5 No business logic in migrations, seeders, or route files

**Testing**
- [x] §T-1 All tests use Pest functional syntax; no PHPUnit class-based tests
- [x] §T-2 Every feature has at least one test under `tests/Feature/Api/V1/`
- [x] §T-3 Tests use `RefreshDatabase`; no shared mutable state between tests
- [x] §T-4 `BaseApiTestCase` provides typed helpers (`apiGet`, `apiPost`, etc.) with `/api/v1/` prefix

**Logging**
- [x] §L-1 Three dedicated log channels: `app`, `api_requests`, `payment`
- [x] §L-2 `RequestLogger` middleware logs method, url, user_id, status, duration_ms to `api_requests`
- [x] §L-3 Every Service uses `Loggable` trait for start/success/failure logging
- [x] §L-4 Log retention: payment=90d, api_requests=30d, app=14d

**Security**
- [x] §S-1 All protected routes use `auth:sanctum` middleware
- [x] §S-2 No credentials in code; all secrets via `.env`
- [x] §S-3 Exception handler converts all Throwable to JSON for API routes (no stack traces in production)
- [x] §S-4 Validation errors return 422 with structured `errors` key

**Database**
- [x] §D-1 Every table has `created_at`, `updated_at`, `deleted_at` (SoftDeletes on all models)
- [x] §D-2 Foreign keys explicitly indexed in migrations
- [x] §D-3 Enum columns cast to PHP-backed Enums via `$casts`
- [x] §D-4 Every `up()` has a matching `down()` — reversible migrations only

---

## Project Structure

### Documentation (this feature)

```text
specs/001-laravel-api-setup/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/
│   └── api_v1.md        # Phase 1 output endpoint definitions
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code Structure

```text
app/
├── Data/                          # DTOs via spatie/laravel-data
│   ├── Auth/
│   ├── Payment/
│   └── Subscription/
├── Enums/                         # Backed Enums
│   ├── BookingStatus.php
│   ├── PaymentGateway.php
│   ├── SubscriptionStatus.php
│   └── TransactionStatus.php
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/
│   │           ├── AuthController.php
│   │           ├── BookingController.php
│   │           ├── CartController.php
│   │           ├── PackageController.php
│   │           ├── PaymentController.php
│   │           └── ServiceController.php
│   └── Middleware/
│       ├── ApiVersionMiddleware.php
│       └── RequestLogger.php
├── Models/                        # Models with ULIDs and SoftDeletes
│   ├── Booking.php
│   ├── Cart.php
│   ├── CartItem.php
│   ├── Package.php
│   ├── PaymentTransaction.php
│   ├── Service.php
│   ├── Subscription.php
│   └── User.php
├── Repositories/                  # Strict Repository Pattern
│   ├── Contracts/
│   │   ├── BookingRepositoryInterface.php
│   │   ├── CartRepositoryInterface.php
│   │   ├── PackageRepositoryInterface.php
│   │   ├── PaymentTransactionRepositoryInterface.php
│   │   ├── ServiceRepositoryInterface.php
│   │   ├── SubscriptionRepositoryInterface.php
│   │   └── UserRepositoryInterface.php
│   └── Eloquent/
│       ├── BookingRepository.php
│       ├── CartRepository.php
│       ├── PackageRepository.php
│       ├── PaymentTransactionRepository.php
│       ├── ServiceRepository.php
│       ├── SubscriptionRepository.php
│       └── UserRepository.php
├── Services/                      # Service Layer
│   ├── AuthService.php
│   ├── BookingService.php
│   ├── CartService.php
│   ├── PackageService.php
│   ├── SubscriptionService.php
│   ├── Payment/
│   │   ├── Contracts/
│   │   │   └── PaymentGatewayInterface.php
│   │   ├── Gateways/
│   │   │   ├── MoyasarGateway.php
│   │   │   ├── StripeGateway.php
│   │   │   └── TapGateway.php
│   │   ├── PaymentManager.php
│   │   └── PaymentService.php
│   └── Traits/
│       └── Loggable.php
config/
└── payments.php                   # Gateway rules & configurations
database/
├── migrations/                    # Migration definitions
└── seeders/                       # Seeders for testing
routes/
└── api_v1.php                     # Route definitions prefixed /api/v1/
tests/
├── Feature/
│   └── Api/
│       └── V1/
│           ├── AuthTest.php
│           ├── BookingTest.php
│           ├── CartTest.php
│           ├── PackageTest.php
│           ├── PaymentTest.php
│           └── SubscriptionTest.php
└── Pest.php
```

**Structure Decision**: Standard Laravel 13 single-project monolith with versioned API structure.

---

## Complexity Tracking

*No violations in the Constitution Check require justification. Every rule is perfectly adhered to.*
