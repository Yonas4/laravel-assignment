<!--
SYNC IMPACT REPORT
- Version change: N/A -> 1.0.0
- List of modified principles:
  - [PRINCIPLE_1_NAME] -> I. Repository Pattern & Service Layer
  - [PRINCIPLE_2_NAME] -> II. API Versioning (/api/v1/)
  - [PRINCIPLE_3_NAME] -> III. Modern PHP Type Safety & DTO-Driven Data Flow
  - [PRINCIPLE_4_NAME] -> IV. Pest Testing Discipline
  - [PRINCIPLE_5_NAME] -> V. Observability & Service Logging
  - Added Principle -> VI. Data Access Isolation (No direct Eloquent in Controllers/Services)
- Added sections:
  - Additional Technical Constraints
  - Testing & Code Quality Gates
- Removed sections: None
- Templates requiring updates:
  - .specify/templates/plan-template.md (✅ updated)
  - .specify/templates/spec-template.md (✅ updated)
  - .specify/templates/tasks-template.md (✅ updated)
- Follow-up TODOs: None
-->

# Ajeer Assignment API Constitution

> [!IMPORTANT]
> This Constitution defines the foundational architectural rules and governance principles for the Ajeer Laravel 13 API. All code contributions, architectural designs, and automated pipelines MUST strictly comply with these principles. No deviations are allowed without a formal amendment process.

## Core Principles

### I. Repository Pattern & Service Layer Isolation
The Service Layer and Repository Pattern are strictly enforced to achieve absolute separation of concerns.
- **Rules**:
  - **Controllers** MUST act only as HTTP entry points, delegating all business logic to specific **Service** classes.
  - **Services** MUST encapsulate business workflows and coordinate actions, retrieving and persisting data strictly through **Repositories**.
  - **Repositories** MUST abstract the data source completely. Direct instantiation or execution of database/query builder actions outside a Repository is strictly forbidden.
- **Rationale**: Keeps controllers extremely thin, business workflows reusable/composable across different delivery channels (CLI, HTTP, Queue), and decouples application core from database implementation details.

### II. Strict API Versioning under `/api/v1/`
Every HTTP endpoint exposed by the application MUST be versioned to guarantee stable and predictable API contracts.
- **Rules**:
  - All routing files, URL paths, and controller namespaces MUST be organized under the `/api/v1/` prefix.
  - Controllers must be namespaced under `App\Http\Controllers\Api\V1`.
  - Any breaking change to contracts MUST be introduced in a new API version (e.g., `/api/v2/`), keeping `/api/v1/` fully functional and backward-compatible.
- **Rationale**: Ensures external clients and integrations do not break when internal data schemas or requirements change.

### III. PHP 8.3 Type Safety, ULIDs, and DTO-Driven Data Flow
Enforce type safety at compile and runtime, while standardizing on robust identifier and data transfer schemas.
- **Rules**:
  - Every PHP file MUST begin with the strict types declaration: `declare(strict_types=1);`.
  - All database primary keys and foreign keys MUST use Universally Unique Lexicographically Sortable Identifiers (ULIDs) via `Symfony\Component\Uid\Ulid`. Auto-incrementing integers are strictly prohibited in database schemas.
  - Data transfer entering controllers (HTTP payloads) and leaving the API MUST be validated and encapsulated using Data Transfer Objects (DTOs) via `spatie/laravel-data`. Direct array or generic request class usage in services is prohibited.
- **Rationale**: Mitigates class of type-safety bugs, prevents ID enumeration attacks, guarantees sortability of keys, and provides a clean, self-documenting data contract.

### IV. Functional Pest Testing Discipline
Testing is a non-negotiable prerequisite to ensure production stability and code confidence.
- **Rules**:
  - All unit, integration, and contract tests MUST be written using the Pest testing framework.
  - Standard PHPUnit class-based structures (e.g., class definitions, test methods, or extending `PHPUnit\Framework\TestCase`) are strictly forbidden.
  - Tests MUST utilize Pest's functional and declarative syntax (`it()`, `describe()`, `expect()`).
- **Rationale**: Encourages modern, highly readable, and cohesive test suites that align perfectly with the modern Laravel ecosystem and improve developer velocity.

### V. Observability & Service-Level Logging
The application core must remain transparent and highly observable at all times.
- **Rules**:
  - Every Service class MUST utilize a `Loggable` trait (or dedicated logger wrapper).
  - Every execution of a business action (start, success, or failure/exception) MUST be logged.
  - Logs MUST include rich structured context (e.g., current actor ULID, payload metadata, operation execution duration) without leaking sensitive personal/financial information.
- **Rationale**: Guarantees production-readiness by providing comprehensive logs that allow fast diagnosis of errors and auditability of system state.

### VI. Data Access Isolation (No Direct Eloquent in Controllers or Services)
Strict boundary isolation between business rules and persistence implementation.
- **Rules**:
  - There MUST be NO direct Eloquent queries (e.g., `User::where(...)`, `->save()`, `->update()`, `->delete()`) inside Controllers or Services.
  - All querying, caching, filtering, and persistence operations MUST go through the Repository interface.
  - Services interact only with interfaces; they do not know about concrete database/ORM details.
- **Rationale**: Minimizes coupling with the database driver and makes swapping storage mechanisms or mock testing individual units simple without requiring database boot.

## Additional Technical Constraints

### Architecture & Design Patterns
- **Dependency Injection**: Services and Repositories MUST be bound to interfaces within the Laravel Service Container and injected via constructor dependency injection.
- **Form Requests**: Custom DTOs via `spatie/laravel-data` should be resolved directly in controller actions for validation and payload casting.
- **Standardized Responses**: All API responses MUST follow a consistent JSON envelope structure:
  ```json
  {
    "success": true,
    "data": {},
    "meta": {}
  }
  ```

## Testing & Code Quality Gates

### Quality Standards
- **Static Analysis**: All code MUST pass PHPStan at Level 8.
- **Code Style**: Every contribution MUST conform to Laravel Pint styling rules.
- **Test Coverage**: Business-critical Service Layer workflows MUST maintain 100% test coverage. Total application coverage must not fall below 90%.

## Governance
This Constitution is the ultimate source of truth for the codebase architecture.
- **Compliance Checks**: All Pull Requests must pass automated CI checks for PHPStan, Pest, Pint, and a manual review validating adherence to Repository/Service patterns.
- **Amendments**: Modifying, adding, or deleting any core principle requires a minor/major version bump of this Constitution, backed by a written architecture design decision (ADR) and explicit team consensus.
- **Guidance File**: Use the project `README.md` and standard architecture guidelines for runtime development assistance.

**Version**: 1.0.0 | **Ratified**: 2026-05-23 | **Last Amended**: 2026-05-23
