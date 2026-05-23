# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]

**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: [e.g., Python 3.11, Swift 5.9, Rust 1.75 or NEEDS CLARIFICATION]

**Primary Dependencies**: [e.g., FastAPI, UIKit, LLVM or NEEDS CLARIFICATION]

**Storage**: [if applicable, e.g., PostgreSQL, CoreData, files or N/A]

**Testing**: [e.g., pytest, XCTest, cargo test or NEEDS CLARIFICATION]

**Target Platform**: [e.g., Linux server, iOS 15+, WASM or NEEDS CLARIFICATION]

**Project Type**: [e.g., library/cli/web-service/mobile-app/compiler/desktop-app or NEEDS CLARIFICATION]

**Performance Goals**: [domain-specific, e.g., 1000 req/s, 10k lines/sec, 60 fps or NEEDS CLARIFICATION]

**Constraints**: [domain-specific, e.g., <200ms p95, <100MB memory, offline-capable or NEEDS CLARIFICATION]

**Scale/Scope**: [domain-specific, e.g., 10k users, 1M LOC, 50 screens or NEEDS CLARIFICATION]

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Architecture**
- [ ] §A-1 Controllers never touch Eloquent directly; all data access goes through Repository interfaces
- [ ] §A-2 Business logic lives only in Services, never in Controllers/Repositories/migrations/seeders/routes
- [ ] §A-3 Payment gateways implement `PaymentGatewayInterface` (Strategy Pattern), resolved via container
- [ ] §A-4 All Service method signatures use `spatie/laravel-data` DTOs — no raw arrays
- [ ] §A-5 All endpoints versioned under `/api/v1/` with `ApiVersionMiddleware`; controllers in `App\Http\Controllers\Api\V1`

**Code Quality**
- [ ] §C-1 Every PHP file starts with `declare(strict_types=1);`
- [ ] §C-2 Domain constants use PHP-backed Enums, not magic strings/integers
- [ ] §C-3 ULID primary keys on all models — no auto-increment integers
- [ ] §C-4 `Model::shouldBeStrict()` enabled in non-production
- [ ] §C-5 No business logic in migrations, seeders, or route files

**Testing**
- [ ] §T-1 All tests use Pest functional syntax; no PHPUnit class-based tests
- [ ] §T-2 Every feature has at least one test under `tests/Feature/Api/V1/`
- [ ] §T-3 Tests use `RefreshDatabase`; no shared mutable state between tests
- [ ] §T-4 `BaseApiTestCase` provides typed helpers (`apiGet`, `apiPost`, etc.) with `/api/v1/` prefix

**Logging**
- [ ] §L-1 Three dedicated log channels: `app`, `api_requests`, `payment`
- [ ] §L-2 `RequestLogger` middleware logs method, url, user_id, status, duration_ms to `api_requests`
- [ ] §L-3 Every Service uses `Loggable` trait for start/success/failure logging
- [ ] §L-4 Log retention: payment=90d, api_requests=30d, app=14d

**Security**
- [ ] §S-1 All protected routes use `auth:sanctum` middleware
- [ ] §S-2 No credentials in code; all secrets via `.env`
- [ ] §S-3 Exception handler converts all Throwable to JSON for API routes (no stack traces in production)
- [ ] §S-4 Validation errors return 422 with structured `errors` key

**Database**
- [ ] §D-1 Every table has `created_at`, `updated_at`, `deleted_at` (SoftDeletes on all models)
- [ ] §D-2 Foreign keys explicitly indexed in migrations
- [ ] §D-3 Enum columns cast to PHP-backed Enums via `$casts`
- [ ] §D-4 Every `up()` has a matching `down()` — reversible migrations only

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
# [REMOVE IF UNUSED] Option 1: Single project (DEFAULT)
src/
├── models/
├── services/
├── cli/
└── lib/

tests/
├── contract/
├── integration/
└── unit/

# [REMOVE IF UNUSED] Option 2: Web application (when "frontend" + "backend" detected)
backend/
├── src/
│   ├── models/
│   ├── services/
│   └── api/
└── tests/

frontend/
├── src/
│   ├── components/
│   ├── pages/
│   └── services/
└── tests/

# [REMOVE IF UNUSED] Option 3: Mobile + API (when "iOS/Android" detected)
api/
└── [same as backend above]

ios/ or android/
└── [platform-specific structure: feature modules, UI flows, platform tests]
```

**Structure Decision**: [Document the selected structure and reference the real
directories captured above]

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
