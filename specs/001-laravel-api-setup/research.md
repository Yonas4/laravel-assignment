# Research & Technical Decisions: Multi-Gateway Payments & Service Booking

This document outlines the architectural research, decisions, and patterns selected to build a production-ready Laravel 13 API for the **ajeer-assessment** project.

## 1. Multi-Gateway Payment System (Strategy Pattern)

### Decision
Implement the **Strategy Pattern** for the multi-gateway payment system. A generic `PaymentGatewayInterface` will define the contract for all gateways (Moyasar, Stripe, Tap). A factory class or a driver-based manager (`PaymentManager`) will resolve the active gateway at runtime using Laravel's Service Container.

### Rationale
- **Open/Closed Principle**: We can add new payment gateways without altering existing controllers or business logic.
- **Decoupled Logic**: Each gateway has its own driver implementation encapsulating its SDK calls, payload mapping, and response formats.
- **Ease of Testing**: We can easily swap real gateway implementations with a fake/mock implementation (`MockPaymentGateway`) during testing.

### Contract Definition (`PaymentGatewayInterface`)
```php
declare(strict_types=1);

namespace App\Services\Payment\Contracts;

use App\Data\Payment\PaymentInitiateData;
use App\Data\Payment\PaymentResponseData;
use App\Data\Payment\PaymentCallbackData;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment transaction.
     */
    public function initiate(PaymentInitiateData $data): PaymentResponseData;

    /**
     * Parse and validate callback payload from the gateway.
     */
    public function handleCallback(array $payload): PaymentCallbackData;
}
```

### Config-Driven Availability Rules
To satisfy **FR-001** and **User Story 1**, availability rules will be defined in a configuration file (`config/payments.php`):
- **Moyasar**: Available in all cities, supports modules `subscription`, `booking`, `cart`.
- **Stripe**: Available in all cities, supports modules `subscription`, `cart`.
- **Tap**: Available in Riyadh, Jeddah, Dammam; supports modules `booking`, `cart`.
- Gateways can be enabled/disabled individually via environment variables: `MOYASAR_ENABLED`, `STRIPE_ENABLED`, `TAP_ENABLED`.

---

## 2. strict_types, ULIDs & spatie/laravel-data DTOs

### Decision
- Require `declare(strict_types=1);` in every PHP file.
- Enable `Model::shouldBeStrict()` in non-production.
- Use `spatie/laravel-data` for all incoming requests, API responses, and Service layer method parameters.
- Use ULIDs for all primary and foreign database keys.

### Rationale
- **PHP 8.3 Type Safety**: Prevents runtime type coercion issues and ensures predictable behavior.
- **ULID Benefits**: ULIDs are 128-bit lexicographically sortable identifiers. Unlike UUIDs, they maintain indexing efficiency in MySQL because they sort chronologically. Unlike auto-incrementing integers, they prevent ID enumeration and information leakage.
- **Laravel 13 Strictness**: Enforcing strict attributes, strict lazy loading, and strict mass assignment prevents silent bugs (like calling a missing relation or discarding an unfillable attribute).
- **Data Transfer Objects (DTOs)**: Ensure robust type guarantees between HTTP controllers and business services. `spatie/laravel-data` provides validation, casting, and transformation automatically.

---

## 3. Strict Repository Pattern + Service Layer

### Decision
- **Controller**: Pure HTTP gateway. Validates request (using DTO/Form Request), invokes Service, returns structured JSON.
- **Service**: Implements all business processes (e.g. `activateTrial`, `bookService`, `processPayment`). Uses DTOs. Communicates with data store *strictly* through Repository Interfaces.
- **Repository**: Encapsulates all Eloquent queries and DB interactions. Implements a dedicated Contract interface.

### Rationale
- **Isolation**: Keeps business logic isolated from HTTP/routing frameworks and database-specific query structures.
- **Testability**: Makes it simple to mock the database layer by passing a FakeRepository to the Service, or unit test the Controller using a mock Service.

---

## 4. Observability & Custom Logging Channels

### Decision
Configure three distinct channels in `config/logging.php`:
1. `app`: General errors and info. Logs service method execution details using the `Loggable` trait.
2. `api_requests`: Inbound API traffic (method, URI, IP, status, duration, user_id).
3. `payment`: Dedicated audit trail of gateway payloads, callback payloads, transaction state transitions, and signature verification results.

### Rationale
- High-volume requests logs don't clutter system error logs.
- Payment logs have different security and retention policies (90 days vs 14 days for general application logs), making compliance tracking easier.
