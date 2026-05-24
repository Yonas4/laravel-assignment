# Feature Specification: Ajeer Assessment — Multi-Gateway Payments + Service Booking Platform

**Feature Branch**: `001-laravel-api-setup`

**Created**: 2026-05-23

**Status**: Draft

**Input**: Senior Laravel Developer technical assessment for Ajeer (ajeer.app), KSA. Build a production-ready Laravel 13 API with two core modules: (1) a multi-gateway payment system with city/module-based availability, and (2) a trial subscription + service booking + cart + packages platform.

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Retrieve Available Payment Gateways (Priority: P1)

As a client application, I can retrieve the list of payment gateways available for a given city and module so that I only present valid payment options to the end user.

**Why this priority**: This is the entry point for the entire payment flow. Without knowing which gateways are available, no payment can be initiated. It also validates the config-driven gateway availability engine which is the core differentiator of the payment module.

**Independent Test**: Can be fully tested by calling `GET /api/v1/payments/gateways?city=Riyadh&module=booking` and verifying the response contains the correct subset of gateways based on the config rules.

**Acceptance Scenarios**:

1. **Given** the system has three configured gateways (Moyasar: all cities / subscription+booking+cart; Stripe: all cities / subscription+cart; Tap: Riyadh+Jeddah+Dammam / booking+cart), **When** a client requests gateways with `city=Riyadh&module=booking`, **Then** the response contains Moyasar and Tap (Stripe does not support booking).
2. **Given** a gateway is disabled via config (`STRIPE_ENABLED=false`), **When** a client requests gateways for any city/module, **Then** Stripe never appears in the results.
3. **Given** a client requests gateways with `city=Abha&module=booking`, **When** the system evaluates availability, **Then** only Moyasar is returned (Tap is restricted to Riyadh/Jeddah/Dammam, Stripe does not support booking).

---

### User Story 2 — Initiate a Payment (Priority: P1)

As an authenticated user, I can initiate a payment through any available gateway so that I receive a redirect URL or payment token to complete the transaction.

**Why this priority**: This is the core transaction flow and the assessors' primary evaluation target. It validates the Strategy Pattern implementation and the end-to-end flow from request to gateway interaction.

**Independent Test**: Can be tested by sending `POST /api/v1/payments/initiate` with a valid gateway, amount, currency, and module, then verifying a `payment_transaction` record is created with status `pending` and a redirect URL is returned.

**Acceptance Scenarios**:

1. **Given** an authenticated user selects an available gateway (e.g., Moyasar), **When** they submit a payment initiation request with amount, currency, and module, **Then** the system creates a `payment_transaction` record with status `pending` and returns a redirect URL or payment token.
2. **Given** an authenticated user attempts to initiate a payment through a gateway that is unavailable for their city or module, **When** the request is processed, **Then** the system returns a `422` error with a structured `errors` key explaining why the gateway is unavailable.
3. **Given** an unauthenticated user attempts to initiate a payment, **When** the request is sent, **Then** the system returns a `401 Unauthorized` response.

---

### User Story 3 — Handle Gateway Callbacks (Priority: P1)

As a system, I receive webhook callbacks from payment gateways and persist the outcome so that transaction statuses are always up-to-date.

**Why this priority**: Without callback handling, payments remain permanently in `pending` status. This validates the Strategy Pattern's `handleCallback()` contract and the idempotency requirement.

**Independent Test**: Can be tested by sending `POST /api/v1/payments/callback/{gateway}` with a simulated gateway payload and verifying the corresponding transaction record is updated.

**Acceptance Scenarios**:

1. **Given** a pending transaction exists for a Moyasar payment, **When** the Moyasar gateway sends a success callback, **Then** the transaction status is updated to `success` and `paid_at` is populated.
2. **Given** a pending transaction exists, **When** the gateway sends a failure callback, **Then** the transaction status is updated to `failed`.
3. **Given** a transaction has already been marked as `success`, **When** the gateway sends a duplicate callback, **Then** the system does not create a duplicate record or change the existing status (idempotent).

---

### User Story 4 — View Transaction History (Priority: P2)

As an authenticated user, I can list my transaction history with pagination and view individual transaction details so that I can track my payment activity.

**Why this priority**: Important for user trust and auditability but depends on transactions being created and updated first (US2, US3).

**Independent Test**: Can be tested by creating multiple transactions for a user, then calling `GET /api/v1/payments/transactions` and verifying paginated results, and `GET /api/v1/payments/transactions/{id}` for a single record.

**Acceptance Scenarios**:

1. **Given** an authenticated user has 25 transactions, **When** they request their transaction list with default pagination (15 per page), **Then** the response returns the first 15 transactions with pagination metadata.
2. **Given** an authenticated user requests a specific transaction by ID, **When** the transaction belongs to them, **Then** the full transaction detail is returned including gateway, amount, status, and timestamps.
3. **Given** an authenticated user requests a transaction that belongs to another user, **When** the system evaluates ownership, **Then** a `404 Resource not found` response is returned.

---

### User Story 5 — User Registration & Authentication (Priority: P1)

As a visitor, I can register for an account and log in to receive a Sanctum bearer token so that I can access protected API endpoints.

**Why this priority**: Authentication is a foundational prerequisite for all protected endpoints (payments, subscriptions, cart, bookings). Without it, no authenticated user stories can function.

**Independent Test**: Can be tested by registering a user via `POST /api/v1/auth/register`, then logging in via `POST /api/v1/auth/login` and verifying a bearer token is returned, then using that token to access a protected endpoint.

**Acceptance Scenarios**:

1. **Given** a visitor submits valid registration data (name, email, password, phone, city), **When** the system processes the request, **Then** a new user is created with status `active` and a Sanctum token is returned.
2. **Given** a registered user submits valid login credentials, **When** the system verifies them, **Then** a Sanctum bearer token is returned.
3. **Given** an authenticated user sends a logout request, **When** the system processes it, **Then** the current token is revoked and a success response is returned.
4. **Given** a visitor submits registration data with an already-taken email, **When** the system validates, **Then** a `422` validation error is returned with a specific message for the `email` field.

---

### User Story 6 — Activate Trial Subscription (Priority: P2)

As a new user, I can activate a free 14-day trial subscription so that I can access the platform's services without immediate payment.

**Why this priority**: The trial is the primary onboarding mechanism and a gateway to service booking. It must be implemented before booking can work.

**Independent Test**: Can be tested by registering a new user, activating a trial via `POST /api/v1/subscriptions/trial`, and verifying the subscription record shows `trial` status with correct start/end dates.

**Acceptance Scenarios**:

1. **Given** a newly registered user with no prior subscription, **When** they request a trial activation, **Then** a subscription record is created with status `trial`, start date of now, and end date 14 days from now.
2. **Given** a user who has already used their trial, **When** they attempt to activate another trial, **Then** the system returns a `422` error indicating the trial has already been claimed.
3. **Given** a user's trial has expired (end date is in the past), **When** they check their subscription status via `GET /api/v1/subscriptions/my`, **Then** the status shows `expired`.

---

### User Story 7 — Browse and Book Services (Priority: P2)

As a user with an active subscription, I can browse available maintenance services and book one for a specific date and time.

**Why this priority**: Service booking is a core platform capability and the primary value delivered to subscribed users.

**Independent Test**: Can be tested by listing services via `GET /api/v1/services`, viewing a single service, and booking it via `POST /api/v1/services/{id}/book` with an active subscription.

**Acceptance Scenarios**:

1. **Given** the system has multiple services across categories, **When** a user requests the service list with an optional category filter, **Then** the response returns services matching the filter with name, category, price, duration, and availability status.
2. **Given** a user with an active subscription selects an available service, **When** they submit a booking request with a date and time, **Then** a booking record is created and confirmed.
3. **Given** a user with an expired subscription attempts to book a service, **When** the system checks their subscription status, **Then** a `403 Forbidden` response is returned indicating an active subscription is required.
4. **Given** a user views a specific service by ID, **When** the service exists, **Then** the full service detail is returned.

---

### User Story 8 — Cart Management (Priority: P3)

As an authenticated user, I can add individual services or packages to my cart, view my cart, remove items, and clear the entire cart.

**Why this priority**: Cart is a convenience feature that aggregates multiple services/packages before checkout. It depends on services and packages existing first.

**Independent Test**: Can be tested by adding items to the cart via `POST /api/v1/cart/items`, viewing via `GET /api/v1/cart`, removing a single item, and clearing the entire cart.

**Acceptance Scenarios**:

1. **Given** an authenticated user, **When** they add a service to their cart, **Then** the cart item is created and the cart total is updated.
2. **Given** a user with items in their cart, **When** they view their cart, **Then** all items are listed with individual prices and a cart total.
3. **Given** a user with items in their cart, **When** they remove a specific item, **Then** that item is removed and the cart total is recalculated.
4. **Given** a user with items in their cart, **When** they clear the entire cart, **Then** all items are removed and the cart is empty.

---

### User Story 9 — Packages (Priority: P3)

As a user, I can view available packages that bundle multiple services and add a package to my cart, which expands to its constituent services.

**Why this priority**: Packages are a value-add feature that bundles services. They depend on both services and cart being implemented.

**Independent Test**: Can be tested by listing packages, viewing a package's details (including bundled services), and adding a package to the cart.

**Acceptance Scenarios**:

1. **Given** the system has packages with bundled services, **When** a user lists packages, **Then** each package shows its name, price, and the list of included services.
2. **Given** a user views a specific package by ID, **When** the package exists, **Then** the full package detail is returned including all bundled services.
3. **Given** an authenticated user adds a package to their cart, **When** the system processes the request, **Then** the individual services within the package are added as cart items.
4. **Given** a package contains fewer than two services, **When** the system validates, **Then** the package is rejected as invalid (a package must contain at least two services).

---

### Edge Cases

- What happens when a user requests gateways for a city that has no available gateways? → Returns an empty list with a `200` status.
- What happens when a gateway callback references a non-existent transaction? → Returns `200` to the gateway (to prevent retries) but logs a warning.
- What happens when a user tries to book a service that is marked as unavailable? → Returns `422` with a clear error message.
- What happens when a user adds the same service to their cart twice? → The quantity is incremented or a duplicate item is created (implementation decision documented in assumptions).
- What happens when an authenticated user accesses another user's cart? → Carts are user-scoped; the system only returns the authenticated user's cart.

## Requirements *(mandatory)*

> [!TIP]
> **Constitution Guardrails (v2.0.0)**: All endpoints under `/api/v1/`, ULID PKs, `spatie/laravel-data` DTOs, Pest tests, `Loggable` trait on Services, `auth:sanctum` on protected routes, SoftDeletes + timestamps on all tables.

### Functional Requirements

- **FR-001**: System MUST provide a config-driven gateway availability engine that filters gateways by city, module, and enabled status.
- **FR-002**: System MUST support three payment gateways (Moyasar, Stripe, Tap) each implementing a shared `PaymentGatewayInterface` contract (Strategy Pattern).
- **FR-003**: System MUST allow authenticated users to initiate a payment, creating a `pending` transaction record and returning a gateway-specific redirect URL or token.
- **FR-004**: System MUST handle gateway callbacks idempotently, updating transaction status without creating duplicate records.
- **FR-005**: System MUST provide paginated transaction history scoped to the authenticated user.
- **FR-006**: System MUST support user registration and login via Sanctum bearer tokens with proper validation (unique email, strong password).
- **FR-007**: System MUST enforce a one-time-only trial subscription per user, lasting exactly 14 days from activation.
- **FR-008**: System MUST prevent service booking for users without an active subscription (trial or paid).
- **FR-009**: System MUST allow users to browse services filterable by category and view individual service details.
- **FR-010**: System MUST allow authenticated users to manage a user-scoped cart (add items, remove items, clear cart).
- **FR-011**: System MUST support packages that bundle at least two services, and adding a package to the cart MUST expand to its individual services.
- **FR-012**: System MUST log every API request via `RequestLogger` middleware (method, URL, user_id, status, duration_ms).
- **FR-013**: System MUST log all Service-layer business actions using the `Loggable` trait (start, success, failure).
- **FR-014**: System MUST return structured JSON error responses for all API routes including validation errors (422 with `errors` key) and authentication errors (401).

### Key Entities

- **User**: Registered platform user with name, email, password, phone, city, and status (Enum: active/inactive/banned). Extends Authenticatable for Sanctum. ULID primary key.
- **PaymentTransaction**: Records each payment attempt. Fields: user_id, gateway, gateway_transaction_id, module, amount, currency (Enum: SAR/USD/EUR), status (Enum: pending/success/failed/refunded), gateway_payload, gateway_response, paid_at. ULID primary key.
- **Subscription**: Tracks user subscription state. Fields: user_id, plan, status (Enum: trial/active/expired/canceled), starts_at, ends_at, trial_used (boolean). ULID primary key.
- **Service**: Maintenance service offered on the platform. Fields: name, category, description, price, duration_minutes, is_available (boolean). ULID primary key.
- **Booking**: A scheduled service appointment. Fields: user_id, service_id, scheduled_at, status. ULID primary key.
- **Cart**: User's shopping cart. Fields: user_id. ULID primary key.
- **CartItem**: Individual item in a cart. Fields: cart_id, service_id, quantity, price. ULID primary key.
- **Package**: A bundle of services. Fields: name, description, price. ULID primary key. Must contain at least 2 services.
- **PackageService** (pivot): Links packages to services. Fields: package_id, service_id.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can register and receive a bearer token in under 2 seconds.
- **SC-002**: Gateway availability query returns correct filtered results within 500ms for any city/module combination.
- **SC-003**: Payment initiation creates a pending transaction and returns a gateway response in under 3 seconds.
- **SC-004**: Gateway callbacks are processed idempotently — duplicate callbacks produce no duplicate records (verified by test).
- **SC-005**: Trial activation is enforced as one-per-user — a second attempt always returns an error (verified by test).
- **SC-006**: Service booking is blocked for users without active subscriptions — returns a clear error (verified by test).
- **SC-007**: All API endpoints return structured JSON responses with consistent `success`, `message`, `data`, and `errors` keys.
- **SC-008**: All Pest tests pass (`php artisan test`) with zero failures.
- **SC-009**: All database tables use ULID primary keys, timestamps, and SoftDeletes.
- **SC-010**: The system operates correctly with three gateway configurations demonstrating the Strategy Pattern.

## Assumptions

- Target users are developers/assessors evaluating the codebase — the API is not deployed for end users during assessment.
- MySQL 8.0+ is available in the evaluation environment.
- Payment gateways are simulated/stubbed — no real payment processing occurs during assessment.
- The assessment duration is 5 days; the spec covers the complete scope.
- Cart items are individual rows (adding the same service twice creates a second row, not an incremented quantity).
- Guest carts are not supported — all cart operations require authentication.
- Service categories are seeded via database seeders, not managed through CRUD endpoints.
- The `User` model extends `Authenticatable` (not `BaseModel`) to maintain Sanctum compatibility; ULID and SoftDeletes are added via traits directly.
