<!--
SYNC IMPACT REPORT
- Version change: 1.0.0 -> 2.0.0
- Bump rationale: MAJOR — principles restructured from 6 flat items into
  6 categorized groups (Architecture, Code Quality, Testing, Logging,
  Security, Database) with 26 testable sub-rules. New principles added
  (Strategy Pattern, Sanctum auth, SoftDeletes, log channels, reversible
  migrations). Existing principles redefined with expanded scope.
- Modified principles (old title -> new mapping):
  - I. Repository Pattern & Service Layer Isolation -> Architecture §A-1 + §A-2
  - II. Strict API Versioning under /api/v1/ -> Architecture §A-5
  - III. PHP 8.3 Type Safety, ULIDs, DTO-Driven Data Flow -> Code Quality §C-1 + §C-3 + Architecture §A-4
  - IV. Functional Pest Testing Discipline -> Testing §T-1
  - V. Observability & Service-Level Logging -> Logging §L-3
  - VI. Data Access Isolation -> Architecture §A-1 (subsumed)
- Added principles:
  - Architecture §A-3: Strategy Pattern for Payments
  - Code Quality §C-2: Enums replace magic strings/integers
  - Code Quality §C-4: Model::shouldBeStrict() in non-production
  - Code Quality §C-5: No business logic in migrations/seeders/routes
  - Testing §T-2: Feature tests under tests/Feature/Api/V1/
  - Testing §T-3: RefreshDatabase, no shared mutable state
  - Testing §T-4: BaseApiTestCase typed HTTP helpers
  - Logging §L-1: Three dedicated log channels (app/api_requests/payment)
  - Logging §L-2: RequestLogger middleware
  - Logging §L-4: Log retention policy (14-90 days)
  - Security §S-1: auth:sanctum on all protected routes
  - Security §S-2: No credentials in code
  - Security §S-3: Exception handler JSON conversion
  - Security §S-4: Validation errors 422 with errors key
  - Database §D-1: Timestamps + SoftDeletes on all tables
  - Database §D-2: Explicit foreign key indexes
  - Database §D-3: PHP-backed Enum columns
  - Database §D-4: Reversible migrations (up/down)
- Removed sections:
  - "Additional Technical Constraints" (merged into Architecture)
  - "Testing & Code Quality Gates" (merged into Code Quality + Testing)
- Templates requiring updates:
  - .specify/templates/plan-template.md (✅ updated)
  - .specify/templates/spec-template.md (✅ updated)
  - .specify/templates/tasks-template.md (✅ updated)
- Follow-up TODOs: None
-->

# Ajeer Assessment API Constitution

> [!IMPORTANT]
> This Constitution defines the non-negotiable governing principles for
> **ajeer-assessment**, a production-ready Laravel 13 API. Every code
> contribution, architectural decision, CI pipeline, and review MUST comply
> with these principles across all phases. No deviation is permitted without
> a formal amendment (see Governance).

## Architecture Principles

### A-1. Strict Repository Pattern
Controllers MUST NOT touch Eloquent directly. All data access (queries,
persistence, caching, filtering) MUST flow through a Repository that
implements a dedicated interface.
- Every Repository interface MUST live in `app/Repositories/Contracts/`.
- Eloquent implementations MUST live in `app/Repositories/Eloquent/`.
- Repositories MUST be bound to their interfaces in a Service Provider via
  constructor dependency injection.
- **Rationale**: Decouples the application from the ORM, enables unit testing
  with in-memory fakes, and makes future storage swaps trivial.

### A-2. Strict Service Layer
Business logic lives **only** in Service classes — never in Controllers,
Repositories, migrations, seeders, or route files.
- Controllers MUST act exclusively as HTTP entry points: receive a request,
  delegate to a Service, return a response.
- Services MUST coordinate business workflows and interact with the
  persistence layer solely through Repository interfaces.
- Services MUST be registered in the container and injected via constructor
  dependency injection.
- **Rationale**: Keeps controllers thin, makes business rules reusable across
  HTTP, CLI, and queue contexts, and enables isolated unit testing.

### A-3. Strategy Pattern for Payments
Every payment gateway MUST implement a shared contract
(`PaymentGatewayInterface`).
- The active gateway MUST be resolved at runtime through the Service
  Container (driver pattern or explicit factory).
- Adding a new gateway MUST NOT require modifying existing gateway code
  (Open/Closed Principle).
- **Rationale**: Guarantees that swapping or adding payment providers is a
  configuration change, not a refactor.

### A-4. DTOs via spatie/laravel-data
Raw arrays MUST NOT appear in any Service method signature. All data entering
or leaving a Service MUST be encapsulated in a typed DTO built with
`spatie/laravel-data`.
- DTOs MUST live in `app/Data/`.
- Controller actions MUST resolve DTOs directly for request validation and
  payload casting.
- **Rationale**: Provides compile-time safety, self-documenting contracts,
  and eliminates an entire class of "missing key" runtime bugs.

### A-5. API Versioning under `/api/v1/`
All endpoints MUST be versioned under the `/api/v1/` prefix, enforced by
`ApiVersionMiddleware`.
- Controllers MUST be namespaced under `App\Http\Controllers\Api\V1`.
- Routes MUST be defined in a dedicated `routes/api_v1.php` (or equivalent
  versioned route file).
- Breaking changes MUST be introduced in a new version prefix (e.g.,
  `/api/v2/`), keeping the previous version fully functional and
  backward-compatible.
- **Rationale**: Guarantees stable API contracts for external consumers and
  internal integrations.

## Code Quality Principles

### C-1. PHP 8.3 strict_types in Every File
Every PHP file MUST begin with `declare(strict_types=1);`.
- **Rationale**: Catches type coercion bugs at the earliest possible moment.

### C-2. Enums Replace Magic Strings and Integers
All domain constants (statuses, types, roles, categories) MUST be expressed
as PHP 8.1+ backed Enums — never as raw strings, integers, or class
constants.
- Enum files MUST live in `app/Enums/`.
- Database columns that store enum values MUST reference the PHP-backed Enum
  (see Database §D-3).
- **Rationale**: Provides IDE autocompletion, exhaustive match enforcement,
  and eliminates typo-class bugs.

### C-3. ULID Primary Keys on All Models
All database primary keys and foreign keys MUST use ULIDs. Auto-incrementing
integers are strictly prohibited.
- Models MUST use the `HasUlids` trait (or equivalent) and define
  `$incrementing = false` with `$keyType = 'string'`.
- **Rationale**: Prevents ID enumeration attacks, guarantees lexicographic
  sortability, and avoids sequential-ID information leakage.

### C-4. Model::shouldBeStrict() in Non-Production
`Model::shouldBeStrict()` MUST be enabled in `AppServiceProvider::boot()`
for all environments except `production`.
- This prevents lazy loading (N+1), silently discarded attributes, and
  accessing missing attributes.
- **Rationale**: Surfaces data-access anti-patterns during development
  before they reach production.

### C-5. No Business Logic in Migrations, Seeders, or Route Files
Migrations MUST contain only schema definitions. Seeders MUST contain only
data insertion. Route files MUST contain only route registration.
- Any conditional logic, data transformation, or business rule MUST be
  extracted to a Service or a dedicated command.
- **Rationale**: Keeps infrastructure code deterministic and idempotent.

## Testing Principles

### T-1. Pest Is the Only Test Runner
All tests MUST be written using Pest's functional syntax (`it()`,
`describe()`, `expect()`). PHPUnit-style class-based test structures
(extending `TestCase`, `@test` annotations) are strictly forbidden.
- **Rationale**: Enforces a modern, readable, and cohesive test style
  aligned with the Laravel ecosystem.

### T-2. Feature Tests for Every Endpoint
Every feature MUST have at least one Feature test under
`tests/Feature/Api/V1/`.
- Tests MUST exercise the full HTTP stack (route → middleware → controller →
  service → repository → database).
- **Rationale**: Validates the complete request lifecycle, catching
  integration issues early.

### T-3. RefreshDatabase — No Shared Mutable State
All test classes MUST use `RefreshDatabase`. Tests MUST NOT share mutable
state (global variables, static properties, persistent database rows)
between test cases.
- **Rationale**: Guarantees test isolation and deterministic results
  regardless of execution order.

### T-4. BaseApiTestCase with Typed HTTP Helpers
A `BaseApiTestCase` (or Pest `uses()` binding) MUST provide typed HTTP
helper methods: `apiGet()`, `apiPost()`, `apiPut()`, `apiPatch()`,
`apiDelete()`.
- Helpers MUST automatically prepend the `/api/v1/` prefix, set JSON
  headers, and authenticate when a user is provided.
- **Rationale**: Eliminates boilerplate, enforces consistent API testing
  conventions, and prevents accidental non-versioned test requests.

## Logging Principles

### L-1. Three Dedicated Log Channels
The application MUST define exactly three log channels in
`config/logging.php`:
| Channel | Purpose |
|---|---|
| `app` | General application events and errors |
| `api_requests` | Every inbound API request (see §L-2) |
| `payment` | All payment gateway interactions |
- **Rationale**: Separates concern areas for targeted monitoring, alerting,
  and retention policies.

### L-2. RequestLogger Middleware
A `RequestLogger` middleware MUST be applied to all API routes and MUST log:
`method`, `url`, `user_id` (nullable), `status`, `duration_ms`.
- Logs MUST be written to the `api_requests` channel.
- **Rationale**: Provides a complete audit trail of API traffic for debugging
  and compliance.

### L-3. Loggable Trait for Service-Level Logging
Every Service class MUST use a `Loggable` trait that logs the start,
success, and failure/exception of every business action.
- Logs MUST include structured context: actor ULID, operation name, payload
  metadata (without PII), and execution duration.
- Logs MUST be written to the `app` channel.
- **Rationale**: Guarantees observability into business workflows for fast
  incident diagnosis.

### L-4. Log Retention Policy
| Channel | Retention |
|---|---|
| `payment` | 90 days |
| `api_requests` | 30 days |
| `app` | 14 days |
- Retention MUST be enforced via log rotation configuration or scheduled
  cleanup.
- **Rationale**: Balances compliance/audit needs (payments) against storage
  cost.

## Security Principles

### S-1. auth:sanctum on All Protected Routes
Every route that requires authentication MUST use the `auth:sanctum`
middleware. No custom or ad-hoc authentication logic is permitted.
- **Rationale**: Leverages Laravel's first-party, audited auth package and
  ensures consistent token management.

### S-2. No Credentials in Code
All secrets (API keys, database passwords, encryption keys) MUST be sourced
exclusively from `.env` / environment variables. Hard-coded credentials in
source files are strictly forbidden.
- `.env` MUST be listed in `.gitignore`.
- **Rationale**: Prevents accidental credential leakage in version control.

### S-3. Exception Handler — JSON-Only for API Routes
The application exception handler MUST convert all `Throwable` exceptions to
a structured JSON response for API routes:
```json
{
  "success": false,
  "message": "Human-readable error summary",
  "errors": {}
}
```
- Stack traces MUST NOT be exposed outside `local` / `testing` environments.
- **Rationale**: Guarantees a consistent, machine-parseable error contract
  for API consumers.

### S-4. Validation Errors Return 422 with Structured `errors` Key
All validation failures MUST return HTTP 422 with a top-level `errors` key
containing field-specific messages:
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```
- **Rationale**: Follows RFC 4918 / Laravel conventions and allows frontends
  to map errors to form fields predictably.

## Database Principles

### D-1. Timestamps and SoftDeletes on All Tables
Every table MUST include `created_at`, `updated_at`, and `deleted_at`
columns. Every model MUST use the `SoftDeletes` trait.
- **Rationale**: Provides a full audit timeline and prevents accidental
  permanent data loss.

### D-2. Explicit Foreign Key Indexes
All foreign key columns MUST be explicitly indexed in the migration. Do not
rely on implicit index creation.
- **Rationale**: Guarantees predictable query performance across database
  engines.

### D-3. Enum Columns Use PHP-Backed Enums
Database columns storing enumerated values MUST be cast to their
corresponding PHP-backed Enum via Eloquent `$casts`. Raw string or integer
storage without Enum casting is forbidden.
- **Rationale**: Ensures type safety end-to-end from database to application
  layer.

### D-4. Reversible Migrations
Every migration `up()` method MUST have a matching `down()` method that
fully reverses the schema change. Irreversible migrations are forbidden.
- **Rationale**: Enables safe rollbacks and supports zero-downtime deployment
  strategies.

## Governance

This Constitution is the ultimate source of truth for the ajeer-assessment
codebase architecture.

- **Compliance Checks**: All Pull Requests MUST pass automated CI checks
  (PHPStan Level 8, Pest, Laravel Pint) and a manual review verifying
  adherence to Repository/Service/Strategy patterns.
- **Amendment Process**: Modifying, adding, or removing any principle
  requires:
  1. A written Architecture Decision Record (ADR).
  2. A version bump of this Constitution per semantic versioning:
     - **MAJOR**: Principle removal or backward-incompatible redefinition.
     - **MINOR**: New principle or materially expanded guidance.
     - **PATCH**: Wording clarification, typo fix, non-semantic refinement.
  3. Explicit team consensus before merge.
- **Guidance File**: Use the project `README.md` and this constitution for
  runtime development guidance.

**Version**: 2.0.0 | **Ratified**: 2026-05-23 | **Last Amended**: 2026-05-23
