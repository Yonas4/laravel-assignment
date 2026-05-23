---

description: "Task list template for feature implementation"
---

# Tasks: [FEATURE NAME]

**Input**: Design documents from `/specs/[###-feature-name]/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: The examples below include test tasks. Tests are OPTIONAL - only include them if explicitly requested in the feature specification.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel API**: `app/` (Models, Services, Repositories, Data, Enums, Http), `tests/` at repository root
- **Web app**: `backend/` + `frontend/` at repository root
- **Mobile**: `api/` + `ios/` or `android/`
- Paths shown below assume Laravel API — adjust based on plan.md structure

<!--
  ============================================================================
  IMPORTANT: The tasks below are SAMPLE TASKS for illustration purposes only.

  The /speckit-tasks command MUST replace these with actual tasks based on:
  - User stories from spec.md (with their priorities P1, P2, P3...)
  - Feature requirements from plan.md
  - Entities from data-model.md
  - Endpoints from contracts/

  Tasks MUST be organized by user story so each story can be:
  - Implemented independently
  - Tested independently
  - Delivered as an MVP increment

  DO NOT keep these sample tasks in the generated tasks.md file.
  ============================================================================
-->

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [ ] T001 Create project structure per implementation plan
- [ ] T002 Initialize [language] project with [framework] dependencies
- [ ] T003 [P] Configure linting and formatting tools

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

Examples of foundational tasks (adjust based on your project):

- [ ] T004 Setup database schema, ULID-based migrations, and SoftDeletes on all tables (§C-3, §D-1)
- [ ] T005 [P] Implement Sanctum authentication + `auth:sanctum` middleware (§S-1)
- [ ] T006 [P] Setup `/api/v1/` routing with `ApiVersionMiddleware` in `routes/api_v1.php` (§A-5)
- [ ] T007 Create `Loggable` trait, `RequestLogger` middleware, and 3 log channels (§L-1..§L-3)
- [ ] T008 [P] Configure JSON exception handler for API routes (§S-3, §S-4)
- [ ] T009 [P] Create `BaseApiTestCase` with typed HTTP helpers (`apiGet`, `apiPost`, etc.) (§T-4)
- [ ] T010 [P] Setup Repository interface + Eloquent binding pattern in Service Provider (§A-1)
- [ ] T011 Enable `Model::shouldBeStrict()` in non-production `AppServiceProvider` (§C-4)

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - [Title] (Priority: P1) 🎯 MVP

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 1 (OPTIONAL - only if tests requested) ⚠️

> **NOTE: Write these Pest tests FIRST, ensure they FAIL before implementation**

- [ ] T010 [P] [US1] Contract test for [endpoint] in tests/Feature/Api/V1/[Name]Test.php
- [ ] T011 [P] [US1] Integration test for [user journey] in tests/Feature/Services/[Name]ServiceTest.php

### Implementation for User Story 1

- [ ] T012 [P] [US1] Create [Entity1] model with ULID in app/Models/[Entity1].php
- [ ] T013 [P] [US1] Implement [Entity1]Repository interface and Eloquent implementation
- [ ] T014 [P] [US1] Create validation DTO [Entity1]Data in app/Data/[Entity1]Data.php
- [ ] T015 [US1] Implement [Service] in app/Services/[Service].php using Loggable trait
- [ ] T016 [US1] Implement [Controller] in app/Http/Controllers/Api/V1/[Controller].php
- [ ] T017 [US1] Ensure all execution paths are logged and type-strict with `declare(strict_types=1)`

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - [Title] (Priority: P2)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 2 (OPTIONAL - only if tests requested) ⚠️

- [ ] T018 [P] [US2] Feature test for [endpoint] in tests/Feature/Api/V1/[Name]Test.php
- [ ] T019 [P] [US2] Service integration test in tests/Feature/Services/[Name]ServiceTest.php

### Implementation for User Story 2

- [ ] T020 [P] [US2] Create [Entity] model with ULID + SoftDeletes in app/Models/[Entity].php
- [ ] T021 [P] [US2] Implement [Entity]Repository interface and Eloquent implementation
- [ ] T022 [P] [US2] Create DTO [Entity]Data in app/Data/[Entity]Data.php
- [ ] T023 [US2] Implement [Service] in app/Services/[Service].php using Loggable trait
- [ ] T024 [US2] Implement [Controller] in app/Http/Controllers/Api/V1/[Controller].php

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - [Title] (Priority: P3)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 3 (OPTIONAL - only if tests requested) ⚠️

- [ ] T025 [P] [US3] Feature test for [endpoint] in tests/Feature/Api/V1/[Name]Test.php
- [ ] T026 [P] [US3] Service integration test in tests/Feature/Services/[Name]ServiceTest.php

### Implementation for User Story 3

- [ ] T027 [P] [US3] Create [Entity] model with ULID + SoftDeletes in app/Models/[Entity].php
- [ ] T028 [P] [US3] Implement [Entity]Repository interface and Eloquent implementation
- [ ] T029 [P] [US3] Create DTO [Entity]Data in app/Data/[Entity]Data.php
- [ ] T030 [US3] Implement [Service] in app/Services/[Service].php using Loggable trait
- [ ] T031 [US3] Implement [Controller] in app/Http/Controllers/Api/V1/[Controller].php

**Checkpoint**: All user stories should now be independently functional

---

[Add more user story phases as needed, following the same pattern]

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] TXXX [P] Documentation updates in docs/
- [ ] TXXX Code cleanup and refactoring
- [ ] TXXX Performance optimization across all stories
- [ ] TXXX [P] Additional Pest tests (if requested) in tests/Unit/
- [ ] TXXX Security hardening (verify `auth:sanctum`, `.env` secrets, JSON exceptions)
- [ ] TXXX Constitution compliance audit (all §A, §C, §T, §L, §S, §D gates)
- [ ] TXXX Run quickstart.md validation

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3)
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - May integrate with US1 but should be independently testable
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - May integrate with US1/US2 but should be independently testable

### Within Each User Story

- Tests (if included) MUST be written and FAIL before implementation
- Models before services
- Services before endpoints
- Core implementation before integration
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel
- All Foundational tasks marked [P] can run in parallel (within Phase 2)
- Once Foundational phase completes, all user stories can start in parallel (if team capacity allows)
- All tests for a user story marked [P] can run in parallel
- Models within a story marked [P] can run in parallel
- Different user stories can be worked on in parallel by different team members

---

## Parallel Example: User Story 1

```bash
# Launch all tests for User Story 1 together (if tests requested):
Task: "Contract test for [endpoint] in tests/contract/test_[name].py"
Task: "Integration test for [user journey] in tests/integration/test_[name].py"

# Launch all models for User Story 1 together:
Task: "Create [Entity1] model in src/models/[entity1].py"
Task: "Create [Entity2] model in src/models/[entity2].py"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test User Story 1 independently
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo
4. Add User Story 3 → Test independently → Deploy/Demo
5. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1
   - Developer B: User Story 2
   - Developer C: User Story 3
3. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify tests fail before implementing
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
