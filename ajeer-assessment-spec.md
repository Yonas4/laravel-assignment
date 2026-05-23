# Ajeer Assessment — Spec-Driven Development Guide
# Compatible with github/spec-kit + Claude Code

> **كيفية الاستخدام مع spec-kit:**
> ```bash
> specify init ajeer-assessment --integration claude
> cd ajeer-assessment
> # ثم شغّل Claude Code وابدأ بالخطوات بالترتيب أدناه
> ```

---

## ═══════════════════════════════════════
## PHASE 0 — /speckit.constitution
## ═══════════════════════════════════════

```
/speckit.constitution

Establish the governing principles for ajeer-assessment, a production-ready
Laravel 13 API project. These principles are non-negotiable across all phases.

ARCHITECTURE PRINCIPLES:
- Strict Repository Pattern: Controllers never touch Eloquent directly
- Strict Service Layer: Business logic lives only in Services, never Controllers
- Strategy Pattern for Payments: every gateway implements a shared contract
- DTOs (spatie/laravel-data) replace raw arrays in all Service method signatures
- All endpoints are versioned under /api/v1/ with ApiVersionMiddleware

CODE QUALITY PRINCIPLES:
- PHP 8.3 strict_types=1 in every file
- Enums replace magic strings/integers everywhere
- ULID primary keys on all models (no auto-increment integers)
- Model::shouldBeStrict() enabled in non-production (prevents N+1, mass-assignment, missing attributes)
- No business logic in migrations, seeders, or route files

TESTING PRINCIPLES:
- Pest is the only test runner; PHPUnit-style test classes are forbidden
- Every feature gets at least one Feature test under tests/Feature/Api/V1/
- Tests use RefreshDatabase; no shared mutable state between tests
- BaseApiTestCase provides typed HTTP helpers (apiGet, apiPost, etc.)

LOGGING PRINCIPLES:
- Three dedicated log channels: app / api_requests / payment
- RequestLogger middleware logs every API request (method, url, user_id, status, duration_ms)
- Loggable trait used in Services for business-level logging
- Payment channel retained for 90 days; others for 14–30 days

SECURITY PRINCIPLES:
- All protected routes use auth:sanctum middleware
- No credentials in code; all secrets via .env
- Exception handler converts all Throwable to JSON for API routes
- Validation errors return 422 with structured errors key

DATABASE PRINCIPLES:
- Every table has created_at, updated_at, deleted_at (SoftDeletes on all models)
- Foreign keys indexed explicitly
- Enum columns use PHP-backed Enums, not raw strings
- Migrations are reversible: every up() has a matching down()
```

---

## ═══════════════════════════════════════
## PHASE 1 — /speckit.specify
## ═══════════════════════════════════════

```
/speckit.specify

PROJECT: ajeer-assessment
CONTEXT: Senior Laravel Developer technical assessment for Ajeer (ajeer.app), KSA.
DURATION: 5 days
SUBMISSION: Public GitHub repository

════════ TASK 1 — Multi-Gateway Payment System ════════

Build a Laravel API for processing payments through multiple gateways.
Gateway availability is config-driven and depends on three factors:
  1. City   — some gateways operate only in specific cities
  2. Module — some gateways support only certain features (booking, cart, subscription)
  3. Status — gateways can be globally enabled/disabled via config

USER STORIES:
  - As a client app, I can retrieve available gateways for my city and module
  - As a user, I can initiate a payment through any available gateway
  - As a system, I receive callbacks from gateways and persist their outcome
  - As a user, I can list my transaction history with status (pending/success/failed/refunded)
  - As a user, I can view a single transaction's full detail

BUSINESS RULES:
  - Moyasar: available in all cities, for modules: subscription, booking, cart
  - Stripe: available in all cities, for modules: subscription, cart
  - Tap: available only in Riyadh, Jeddah, Dammam — for modules: booking, cart
  - A disabled gateway must never appear in the available list
  - Initiating through an unavailable gateway returns a structured 422 error
  - Each gateway callback is idempotent (duplicate callbacks don't create duplicate records)

ACCEPTANCE CRITERIA:
  - GET  /api/v1/payments/gateways?city=Riyadh&module=booking → filtered list
  - POST /api/v1/payments/initiate → returns redirect_url or payment_token
  - POST /api/v1/payments/callback/{gateway} → persists result, returns 200
  - GET  /api/v1/payments/transactions → paginated, auth required
  - GET  /api/v1/payments/transactions/{id} → single record, auth required

════════ TASK 2 — Trial Subscription + Services + Cart + Packages ════════

Build a small service-booking platform inspired by Ajeer's core product.

USER STORIES:
  - As a visitor, I can register and log in (Sanctum token-based)
  - As a new user, I am eligible for one free 14-day trial subscription
  - As a user with an active subscription, I can browse maintenance services
  - As a user, I can schedule/book a service for a specific date and time
  - As a user, I can add individual services or packages to my cart
  - As a user, I can view, update, and clear my cart
  - As a user, I can view available packages (a package bundles multiple services)
  - As a user, I can add a package to my cart (which expands to its services)

BUSINESS RULES:
  - A user can only claim one trial subscription ever (enforced at DB + service level)
  - Trial lasts 14 days from activation; expired trials block service booking
  - A package must contain at least two services
  - Cart items are user-scoped; guest carts are not supported
  - Booking requires an active subscription (trial or paid)
  - Services have a category, price, duration_minutes, and availability status

ACCEPTANCE CRITERIA:
  - POST /api/v1/auth/register
  - POST /api/v1/auth/login  → returns sanctum token
  - POST /api/v1/auth/logout [auth:sanctum]
  - POST /api/v1/subscriptions/trial → activates trial (once per user)
  - GET  /api/v1/subscriptions/my   → current subscription status
  - GET  /api/v1/services           → list (filterable by category)
  - GET  /api/v1/services/{id}
  - POST /api/v1/services/{id}/book [auth:sanctum] → schedule booking
  - GET  /api/v1/cart               [auth:sanctum]
  - POST /api/v1/cart/items         [auth:sanctum]
  - DELETE /api/v1/cart/items/{id}  [auth:sanctum]
  - DELETE /api/v1/cart             [auth:sanctum] → clear cart
  - GET  /api/v1/packages
  - GET  /api/v1/packages/{id}
  - POST /api/v1/packages/{id}/add-to-cart [auth:sanctum]
```

---

## ═══════════════════════════════════════
## PHASE 2 — /speckit.plan
## ═══════════════════════════════════════

```
/speckit.plan

Implement the following technical plan exactly as described.
Do not make architectural decisions — all decisions are already made.
Execute every step in order. Do not skip any step.

════════════════════════════════════════════════════════════
OVERVIEW
════════════════════════════════════════════════════════════

Project name   : ajeer-assessment
PHP version    : 8.3+ (required by Laravel 13)
Laravel version: 13.x
Database       : MySQL 8.0+
Auth           : Laravel Sanctum (token-based)
Architecture   : Repository Pattern + Service Layer + Strategy Pattern
API Style      : RESTful, versioned (/api/v1/)
Testing        : Pest (Laravel 13 default)

════════════════════════════════════════════════════════════
STEP 1 — CREATE LARAVEL PROJECT
════════════════════════════════════════════════════════════

Run:
  composer create-project laravel/laravel:^13.0 ajeer-assessment
  cd ajeer-assessment

Install required packages:
  composer require laravel/sanctum
  composer require spatie/laravel-activitylog
  composer require spatie/laravel-data

Install dev packages:
  composer require --dev barryvdh/laravel-ide-helper
  composer require --dev larastan/larastan

Run:
  php artisan install:api

NOTE: In Laravel 13, routes/api.php does NOT exist by default.
`php artisan install:api` creates it and configures Sanctum automatically.

════════════════════════════════════════════════════════════
STEP 2 — DIRECTORY STRUCTURE
════════════════════════════════════════════════════════════

Create the following directories (use mkdir -p):

  app/Console/Commands/
  app/Exceptions/
  app/Http/Controllers/Api/V1/
  app/Http/Middleware/
  app/Http/Requests/Api/V1/
  app/Http/Resources/Api/V1/
  app/Models/
  app/Modules/Payment/Contracts/
  app/Modules/Payment/Gateways/
  app/Repositories/Contracts/
  app/Repositories/Eloquent/
  app/Services/
  app/Enums/
  app/Traits/
  app/Data/
  app/Providers/
  config/
  database/migrations/
  database/seeders/
  database/factories/
  routes/api/v1/
  tests/Feature/Api/V1/
  tests/Unit/

════════════════════════════════════════════════════════════
STEP 3 — AppServiceProvider
════════════════════════════════════════════════════════════

Replace file: app/Providers/AppServiceProvider.php

<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Enforce strict mode in non-production:
        // prevents lazy loading (N+1), mass-assignment of non-fillable attributes,
        // and accessing non-existent attributes.
        Model::shouldBeStrict(! app()->isProduction());

        // Remove the default 'data' wrapper from API Resources globally.
        // The ApiResponse trait handles response structure manually.
        JsonResource::withoutWrapping();
    }
}

════════════════════════════════════════════════════════════
STEP 4 — BASE INTERFACE: RepositoryInterface
════════════════════════════════════════════════════════════

Create file: app/Repositories/Contracts/RepositoryInterface.php

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): LengthAwarePaginator;

    /**
     * Returns null if not found; does NOT throw ModelNotFoundException.
     * Callers that require the record to exist should handle the null case.
     */
    public function findById(
        int|string $id,
        array $columns = ['*'],
        array $relations = []
    ): ?Model;

    public function create(array $payload): ?Model;

    public function update(int|string $id, array $payload): bool;

    public function deleteById(int|string $id): bool;

    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection;

    public function findOneByField(string $field, mixed $value, array $columns = ['*']): ?Model;
}

════════════════════════════════════════════════════════════
STEP 5 — BASE REPOSITORY: BaseRepository
════════════════════════════════════════════════════════════

Create file: app/Repositories/Eloquent/BaseRepository.php

IMPORTANT CORRECTION: findById uses find() (returns null) NOT findOrFail()
(which throws). The interface contract declares ?Model return type — both
sides must be consistent.

<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->select($columns)->with($relations)->get();
    }

    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): LengthAwarePaginator {
        return $this->model->select($columns)->with($relations)->paginate($perPage);
    }

    /**
     * Returns null when the record does not exist.
     * Services that need a guaranteed record should call findById() and
     * throw ResourceNotFoundException if null is returned.
     */
    public function findById(
        int|string $id,
        array $columns = ['*'],
        array $relations = []
    ): ?Model {
        return $this->model->select($columns)->with($relations)->find($id);
    }

    public function create(array $payload): ?Model
    {
        return $this->model->create($payload);
    }

    public function update(int|string $id, array $payload): bool
    {
        $record = $this->model->find($id);
        if (! $record) {
            return false;
        }
        return $record->update($payload);
    }

    public function deleteById(int|string $id): bool
    {
        $record = $this->model->find($id);
        if (! $record) {
            return false;
        }
        return (bool) $record->delete();
    }

    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->where($field, $value)->get();
    }

    public function findOneByField(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->where($field, $value)->first();
    }
}

════════════════════════════════════════════════════════════
STEP 6 — BASE SERVICE
════════════════════════════════════════════════════════════

Create file: app/Services/BaseService.php

<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ResourceNotFoundException;
use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class BaseService
{
    public function __construct(protected RepositoryInterface $repository) {}

    public function getAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    public function getPaginated(
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): LengthAwarePaginator {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    /**
     * Throws ResourceNotFoundException when the record does not exist.
     * Services should call this instead of findById() when the record
     * must exist for the operation to proceed.
     */
    public function getById(
        int|string $id,
        array $columns = ['*'],
        array $relations = [],
        string $resourceName = 'Resource'
    ): Model {
        $record = $this->repository->findById($id, $columns, $relations);

        if (! $record) {
            throw new ResourceNotFoundException($resourceName);
        }

        return $record;
    }

    public function create(array $data): ?Model
    {
        return $this->repository->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->deleteById($id);
    }
}

════════════════════════════════════════════════════════════
STEP 7 — API RESPONSE TRAIT
════════════════════════════════════════════════════════════

Create file: app/Traits/ApiResponse.php

<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation completed successfully.',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    protected function errorResponse(
        string $message = 'Something went wrong.',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully.'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    protected function unauthorizedResponse(string $message = 'Unauthorized.'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    protected function forbiddenResponse(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    protected function validationErrorResponse(
        mixed $errors,
        string $message = 'Validation failed.'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $message = 'Data fetched successfully.'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }
}

════════════════════════════════════════════════════════════
STEP 8 — LOGGABLE TRAIT
════════════════════════════════════════════════════════════

Create file: app/Traits/Loggable.php

<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait Loggable
{
    protected function logInfo(string $message, array $context = []): void
    {
        Log::channel('app')->info($message, array_merge($this->defaultLogContext(), $context));
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::channel('app')->error($message, array_merge($this->defaultLogContext(), $context));
    }

    protected function logWarning(string $message, array $context = []): void
    {
        Log::channel('app')->warning($message, array_merge($this->defaultLogContext(), $context));
    }

    protected function logDebug(string $message, array $context = []): void
    {
        Log::channel('app')->debug($message, array_merge($this->defaultLogContext(), $context));
    }

    private function defaultLogContext(): array
    {
        return [
            'class'   => static::class,
            'user_id' => auth()->id() ?? 'guest',
            'ip'      => request()->ip(),
        ];
    }
}

════════════════════════════════════════════════════════════
STEP 9 — BASE API CONTROLLER
════════════════════════════════════════════════════════════

Create file: app/Http/Controllers/Api/V1/BaseApiController.php

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Traits\Loggable;

abstract class BaseApiController extends Controller
{
    use ApiResponse, Loggable;
}

════════════════════════════════════════════════════════════
STEP 10 — CUSTOM EXCEPTIONS
════════════════════════════════════════════════════════════

Create file: app/Exceptions/ApiException.php

<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public function __construct(
        string $message = 'An API error occurred.',
        int $code = 400,
        private readonly mixed $errors = null
    ) {
        parent::__construct($message, $code);
    }

    public function getErrors(): mixed
    {
        return $this->errors;
    }
}

---

Create file: app/Exceptions/BusinessException.php

<?php

declare(strict_types=1);

namespace App\Exceptions;

class BusinessException extends ApiException
{
    public function __construct(string $message = 'Business rule violation.', mixed $errors = null)
    {
        parent::__construct($message, 422, $errors);
    }
}

---

Create file: app/Exceptions/ResourceNotFoundException.php

<?php

declare(strict_types=1);

namespace App\Exceptions;

class ResourceNotFoundException extends ApiException
{
    public function __construct(string $resource = 'Resource')
    {
        parent::__construct("{$resource} not found.", 404);
    }
}

════════════════════════════════════════════════════════════
STEP 11 — EXCEPTION HANDLING (bootstrap/app.php)
════════════════════════════════════════════════════════════

IMPORTANT: In Laravel 13, exception handling is registered inside
bootstrap/app.php — do NOT modify a Handler.php class.

Replace the entire contents of bootstrap/app.php with:

<?php

use App\Http\Middleware\ApiVersionMiddleware;
use App\Http\Middleware\RequestLogger;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'api.version' => ApiVersionMiddleware::class,
        ]);

        $middleware->appendToGroup('api', [
            RequestLogger::class,
        ]);

        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            $respond = static function (
                bool $success,
                string $message,
                int $status,
                mixed $errors = null
            ) {
                $body = ['success' => $success, 'message' => $message];
                if (! is_null($errors)) {
                    $body['errors'] = $errors;
                }
                return response()->json($body, $status);
            };

            return match (true) {
                $e instanceof ValidationException
                    => $respond(false, 'Validation failed.', 422, $e->errors()),

                $e instanceof AuthenticationException
                    => $respond(false, 'Unauthorized.', 401),

                $e instanceof ModelNotFoundException,
                $e instanceof NotFoundHttpException
                    => $respond(false, 'Resource not found.', 404),

                $e instanceof \App\Exceptions\ApiException
                    => $respond(false, $e->getMessage(), $e->getCode(), $e->getErrors()),

                default => $respond(
                    false,
                    config('app.debug') ? $e->getMessage() : 'Server error.',
                    500
                ),
            };
        });
    })
    ->create();

════════════════════════════════════════════════════════════
STEP 12 — REQUEST LOGGER MIDDLEWARE
════════════════════════════════════════════════════════════

Create file: app/Http/Middleware/RequestLogger.php

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $start    = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::channel('api_requests')->info('API Request', [
            'method'      => $request->method(),
            'url'         => $request->fullUrl(),
            'ip'          => $request->ip(),
            'user_id'     => auth()->id() ?? 'guest',
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'user_agent'  => $request->userAgent(),
        ]);

        return $response;
    }
}

════════════════════════════════════════════════════════════
STEP 13 — API VERSION MIDDLEWARE
════════════════════════════════════════════════════════════

Create file: app/Http/Middleware/ApiVersionMiddleware.php

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersionMiddleware
{
    private const SUPPORTED_VERSIONS = ['v1'];

    public function handle(Request $request, Closure $next, string $version = 'v1'): Response
    {
        if (! in_array($version, self::SUPPORTED_VERSIONS, true)) {
            return response()->json([
                'success' => false,
                'message' => "API version '{$version}' is not supported. Supported: "
                    . implode(', ', self::SUPPORTED_VERSIONS),
            ], 400);
        }

        $request->headers->set('X-API-Version', $version);

        return $next($request);
    }
}

════════════════════════════════════════════════════════════
STEP 14 — ROUTE STRUCTURE
════════════════════════════════════════════════════════════

Replace file: routes/api.php
(This file was created by `php artisan install:api` — replace its entire content)

<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.version:v1'])->group(function () {
    require base_path('routes/api/v1/auth.php');
    require base_path('routes/api/v1/payment.php');
    require base_path('routes/api/v1/subscription.php');
    require base_path('routes/api/v1/services.php');
    require base_path('routes/api/v1/cart.php');
    require base_path('routes/api/v1/packages.php');
});

---

Create file: routes/api/v1/auth.php

<?php

use Illuminate\Support\Facades\Route;

// Auth routes — implemented in Phase 3
Route::prefix('auth')->name('auth.')->group(function () {
    // POST /api/v1/auth/register
    // POST /api/v1/auth/login
    // POST /api/v1/auth/logout  [auth:sanctum]
});

---

Create file: routes/api/v1/payment.php

<?php

use Illuminate\Support\Facades\Route;

// Payment routes — implemented in Phase 2
Route::prefix('payments')->name('payments.')->group(function () {
    // GET  /api/v1/payments/gateways          (public — filtered by city + module)
    // POST /api/v1/payments/initiate          [auth:sanctum]
    // POST /api/v1/payments/callback/{gateway} (public — webhook from gateway)
    // GET  /api/v1/payments/transactions      [auth:sanctum]
    // GET  /api/v1/payments/transactions/{id} [auth:sanctum]
});

---

Create file: routes/api/v1/subscription.php

<?php

use Illuminate\Support\Facades\Route;

// Subscription routes — implemented in Phase 3
Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
    // GET  /api/v1/subscriptions/plans  (public)
    // POST /api/v1/subscriptions/trial  [auth:sanctum]
    // GET  /api/v1/subscriptions/my     [auth:sanctum]
});

---

Create file: routes/api/v1/services.php

<?php

use Illuminate\Support\Facades\Route;

// Services routes — implemented in Phase 3
Route::prefix('services')->name('services.')->group(function () {
    // GET  /api/v1/services         (public)
    // GET  /api/v1/services/{id}    (public)
    // POST /api/v1/services/{id}/book [auth:sanctum]
});

---

Create file: routes/api/v1/cart.php

<?php

use Illuminate\Support\Facades\Route;

// Cart routes — implemented in Phase 3
Route::prefix('cart')->name('cart.')->middleware(['auth:sanctum'])->group(function () {
    // GET    /api/v1/cart
    // POST   /api/v1/cart/items
    // DELETE /api/v1/cart/items/{id}
    // DELETE /api/v1/cart
});

---

Create file: routes/api/v1/packages.php

<?php

use Illuminate\Support\Facades\Route;

// Packages routes — implemented in Phase 3
Route::prefix('packages')->name('packages.')->group(function () {
    // GET  /api/v1/packages              (public)
    // GET  /api/v1/packages/{id}         (public)
    // POST /api/v1/packages/{id}/add-to-cart [auth:sanctum]
});

════════════════════════════════════════════════════════════
STEP 15 — LOGGING CONFIGURATION
════════════════════════════════════════════════════════════

Open config/logging.php and add these three channels inside the 'channels' array:

'app' => [
    'driver' => 'daily',
    'path'   => storage_path('logs/app.log'),
    'level'  => env('LOG_LEVEL', 'debug'),
    'days'   => 30,
],

'api_requests' => [
    'driver' => 'daily',
    'path'   => storage_path('logs/api_requests.log'),
    'level'  => 'info',
    'days'   => 14,
],

'payment' => [
    'driver' => 'daily',
    'path'   => storage_path('logs/payment.log'),
    'level'  => 'debug',
    'days'   => 90,
],

════════════════════════════════════════════════════════════
STEP 16 — CONFIG FILES
════════════════════════════════════════════════════════════

Create file: config/payment_gateways.php

NOTE: The 'driver' values reference classes inside app/Modules/Payment/Gateways/
which are scaffolded as empty stubs in STEP 17 below.

<?php

return [
    'default' => env('PAYMENT_GATEWAY_DEFAULT', 'moyasar'),

    'gateways' => [

        'moyasar' => [
            'driver'  => \App\Modules\Payment\Gateways\MoyasarGateway::class,
            'label'   => 'Moyasar',
            'enabled' => env('MOYASAR_ENABLED', true),
            'credentials' => [
                'api_key'    => env('MOYASAR_API_KEY', ''),
                'public_key' => env('MOYASAR_PUBLIC_KEY', ''),
            ],
            'availability' => [
                'cities'  => null, // null = all cities
                'modules' => ['subscription', 'booking', 'cart'],
            ],
        ],

        'stripe' => [
            'driver'  => \App\Modules\Payment\Gateways\StripeGateway::class,
            'label'   => 'Stripe',
            'enabled' => env('STRIPE_ENABLED', true),
            'credentials' => [
                'secret_key'      => env('STRIPE_SECRET_KEY', ''),
                'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
                'webhook_secret'  => env('STRIPE_WEBHOOK_SECRET', ''),
            ],
            'availability' => [
                'cities'  => null,
                'modules' => ['subscription', 'cart'],
            ],
        ],

        'tap' => [
            'driver'  => \App\Modules\Payment\Gateways\TapGateway::class,
            'label'   => 'Tap Payments',
            'enabled' => env('TAP_ENABLED', true),
            'credentials' => [
                'secret_key' => env('TAP_SECRET_KEY', ''),
            ],
            'availability' => [
                'cities'  => ['Riyadh', 'Jeddah', 'Dammam'],
                'modules' => ['booking', 'cart'],
            ],
        ],

    ],
];

---

Create file: config/subscription.php

<?php

return [
    'trial' => [
        'enabled'       => env('TRIAL_ENABLED', true),
        'duration_days' => (int) env('TRIAL_DURATION_DAYS', 14),
    ],

    'plans' => [
        'basic' => [
            'label'         => 'Basic',
            'price'         => 49.00,
            'currency'      => 'SAR',
            'duration_days' => 30,
            'features'      => ['booking', 'cart'],
        ],
        'pro' => [
            'label'         => 'Pro',
            'price'         => 99.00,
            'currency'      => 'SAR',
            'duration_days' => 30,
            'features'      => ['booking', 'cart', 'packages', 'priority_support'],
        ],
    ],
];

════════════════════════════════════════════════════════════
STEP 17 — PAYMENT GATEWAY STUBS
════════════════════════════════════════════════════════════

Create the contract first, then three empty gateway stubs.
These will be fully implemented in Phase 2 (payment module).
Creating them now prevents config/payment_gateways.php from
referencing non-existent classes.

---

Create file: app/Modules/Payment/Contracts/PaymentGatewayInterface.php

<?php

declare(strict_types=1);

namespace App\Modules\Payment\Contracts;

interface PaymentGatewayInterface
{
    /** Initiate a payment and return redirect URL or token */
    public function initiate(array $payload): array;

    /** Handle the gateway callback/webhook and return normalized result */
    public function handleCallback(array $payload): array;

    /** Return the gateway's unique identifier string */
    public function getName(): string;
}

---

Create file: app/Modules/Payment/Gateways/MoyasarGateway.php

<?php

declare(strict_types=1);

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\Contracts\PaymentGatewayInterface;

class MoyasarGateway implements PaymentGatewayInterface
{
    public function initiate(array $payload): array
    {
        // TODO: implement in Phase 2
        throw new \RuntimeException('MoyasarGateway::initiate() not implemented yet.');
    }

    public function handleCallback(array $payload): array
    {
        // TODO: implement in Phase 2
        throw new \RuntimeException('MoyasarGateway::handleCallback() not implemented yet.');
    }

    public function getName(): string
    {
        return 'moyasar';
    }
}

---

Create file: app/Modules/Payment/Gateways/StripeGateway.php

<?php

declare(strict_types=1);

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function initiate(array $payload): array
    {
        // TODO: implement in Phase 2
        throw new \RuntimeException('StripeGateway::initiate() not implemented yet.');
    }

    public function handleCallback(array $payload): array
    {
        // TODO: implement in Phase 2
        throw new \RuntimeException('StripeGateway::handleCallback() not implemented yet.');
    }

    public function getName(): string
    {
        return 'stripe';
    }
}

---

Create file: app/Modules/Payment/Gateways/TapGateway.php

<?php

declare(strict_types=1);

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\Contracts\PaymentGatewayInterface;

class TapGateway implements PaymentGatewayInterface
{
    public function initiate(array $payload): array
    {
        // TODO: implement in Phase 2
        throw new \RuntimeException('TapGateway::initiate() not implemented yet.');
    }

    public function handleCallback(array $payload): array
    {
        // TODO: implement in Phase 2
        throw new \RuntimeException('TapGateway::handleCallback() not implemented yet.');
    }

    public function getName(): string
    {
        return 'tap';
    }
}

════════════════════════════════════════════════════════════
STEP 18 — ENUMS
════════════════════════════════════════════════════════════

Create file: app/Enums/StatusEnum.php

<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusEnum: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Pending  = 'pending';
    case Banned   = 'banned';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Pending  => 'Pending',
            self::Banned   => 'Banned',
        };
    }
}

---

Create file: app/Enums/CurrencyEnum.php

<?php

declare(strict_types=1);

namespace App\Enums;

enum CurrencyEnum: string
{
    case SAR = 'SAR';
    case USD = 'USD';
    case EUR = 'EUR';
}

---

Create file: app/Enums/TransactionStatusEnum.php

<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionStatusEnum: string
{
    case Pending   = 'pending';
    case Success   = 'success';
    case Failed    = 'failed';
    case Refunded  = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'Pending',
            self::Success  => 'Success',
            self::Failed   => 'Failed',
            self::Refunded => 'Refunded',
        };
    }
}

---

Create file: app/Enums/SubscriptionStatusEnum.php

<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatusEnum: string
{
    case Trial    = 'trial';
    case Active   = 'active';
    case Expired  = 'expired';
    case Canceled = 'canceled';
}

════════════════════════════════════════════════════════════
STEP 19 — BASE MODEL
════════════════════════════════════════════════════════════

Create file: app/Models/BaseModel.php

NOTE: The default User model extends Authenticatable (Laravel requirement for auth)
and should NOT extend BaseModel. All other models extend BaseModel.

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    // ULIDs: string primary keys (e.g. 01HXYZ...) instead of auto-increment integers.
    // Better for distributed systems, avoids enumeration attacks, sortable by time.

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}

════════════════════════════════════════════════════════════
STEP 20 — USER MODEL UPDATE (keep Authenticatable, add fields)
════════════════════════════════════════════════════════════

The User model MUST keep extending Authenticatable for Sanctum to work.
Add the extra business fields via a migration, not by changing the parent class.

Run:
  php artisan make:migration add_extra_fields_to_users_table --table=users

In the up() method of the generated migration:

    $table->string('phone')->nullable()->after('email');
    $table->string('city')->nullable()->after('phone');
    $table->enum('status', ['active', 'inactive', 'banned'])->default('active')->after('city');
    $table->index(['city']);
    $table->index(['status']);

In the down() method:

    $table->dropIndex(['city']);
    $table->dropIndex(['status']);
    $table->dropColumn(['phone', 'city', 'status']);

Then update app/Models/User.php — add these fields to $fillable:

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'city',
        'status',
    ];

And add to the casts() method or $casts:

    'status' => \App\Enums\StatusEnum::class,

════════════════════════════════════════════════════════════
STEP 21 — PAYMENT TRANSACTIONS MIGRATION (skeleton)
════════════════════════════════════════════════════════════

The assessors evaluate DB design from Phase 1.
Create the payment_transactions table schema now.

Run:
  php artisan make:migration create_payment_transactions_table

Fill the migration with:

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id');
            $table->string('gateway');                           // moyasar | stripe | tap
            $table->string('gateway_transaction_id')->nullable()->unique(); // from gateway response
            $table->string('module');                            // subscription | booking | cart
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('SAR');
            $table->string('status')->default('pending');       // TransactionStatusEnum
            $table->json('gateway_payload')->nullable();         // raw gateway request payload
            $table->json('gateway_response')->nullable();        // raw gateway response
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'status']);
            $table->index(['gateway', 'status']);
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

════════════════════════════════════════════════════════════
STEP 22 — SERVICE PROVIDER: RepositoryServiceProvider
════════════════════════════════════════════════════════════

Create file: app/Providers/RepositoryServiceProvider.php

<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Add interface => concrete bindings here as modules are implemented.
     *
     * Example (Phase 2):
     *   \App\Repositories\Contracts\PaymentTransactionRepositoryInterface::class
     *     => \App\Repositories\Eloquent\PaymentTransactionRepository::class,
     */
    public array $bindings = [];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}

---

Replace the entire content of bootstrap/providers.php with:

<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
];

════════════════════════════════════════════════════════════
STEP 23 — BASE DTO (spatie/laravel-data)
════════════════════════════════════════════════════════════

Create file: app/Data/BaseData.php

<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

abstract class BaseData extends Data
{
    // All DTOs extend this base.
    // Services receive typed DTOs instead of raw arrays.
    //
    // Example (Phase 2):
    //
    // class InitiatePaymentData extends BaseData
    // {
    //     public function __construct(
    //         public readonly string $gateway,
    //         public readonly float  $amount,
    //         public readonly string $currency,  // CurrencyEnum
    //         public readonly string $module,
    //         public readonly string $city,
    //     ) {}
    // }
}

════════════════════════════════════════════════════════════
STEP 24 — BASE API RESOURCE
════════════════════════════════════════════════════════════

Create file: app/Http/Resources/Api/V1/BaseResource.php

<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    // JsonResource wrapping is disabled globally in AppServiceProvider.
    // Every concrete Resource must implement toArray().
}

════════════════════════════════════════════════════════════
STEP 25 — DATABASE SEEDER
════════════════════════════════════════════════════════════

Replace file: database/seeders/DatabaseSeeder.php

<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Phase 2 — Payment:
            // PaymentGatewaySeeder::class,

            // Phase 3 — Auth, Subscriptions, Services, Cart, Packages:
            // SubscriptionPlanSeeder::class,
            // ServiceCategorySeeder::class,
            // ServiceSeeder::class,
            // PackageSeeder::class,
            // UserSeeder::class,
        ]);
    }
}

════════════════════════════════════════════════════════════
STEP 26 — ENVIRONMENT (.env) ADDITIONS
════════════════════════════════════════════════════════════

Append to the .env file:

# ── API ──────────────────────────────────────────────────
API_VERSION=v1

# ── Payment Gateways ─────────────────────────────────────
PAYMENT_GATEWAY_DEFAULT=moyasar

MOYASAR_ENABLED=true
MOYASAR_API_KEY=
MOYASAR_PUBLIC_KEY=

STRIPE_ENABLED=true
STRIPE_SECRET_KEY=
STRIPE_PUBLISHABLE_KEY=
STRIPE_WEBHOOK_SECRET=

TAP_ENABLED=true
TAP_SECRET_KEY=

# ── Subscription ─────────────────────────────────────────
TRIAL_ENABLED=true
TRIAL_DURATION_DAYS=14

════════════════════════════════════════════════════════════
STEP 27 — PEST TEST BASE
════════════════════════════════════════════════════════════

Create file: tests/Feature/Api/V1/BaseApiTestCase.php

<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

abstract class BaseApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl = '/api/v1';

    protected function apiGet(string $path, array $headers = []): TestResponse
    {
        return $this->getJson("{$this->baseUrl}{$path}", $headers);
    }

    protected function apiPost(string $path, array $data = [], array $headers = []): TestResponse
    {
        return $this->postJson("{$this->baseUrl}{$path}", $data, $headers);
    }

    protected function apiPut(string $path, array $data = [], array $headers = []): TestResponse
    {
        return $this->putJson("{$this->baseUrl}{$path}", $data, $headers);
    }

    protected function apiDelete(string $path, array $headers = []): TestResponse
    {
        return $this->deleteJson("{$this->baseUrl}{$path}", [], $headers);
    }

    protected function actingAsUser(): static
    {
        $user = User::factory()->create();
        return $this->actingAs($user, 'sanctum');
    }
}

---

Create file: tests/Feature/Api/V1/HealthTest.php

IMPORTANT CORRECTIONS vs. original:
  - /up returns 200 (Laravel health endpoint) ✓
  - /api/v99/... returns 404 (not 400) because the route does not exist.
    The ApiVersionMiddleware only applies to registered routes under /api/v1/.
    An unknown path like /api/v99/ is caught by Laravel's router as 404.

<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;

it('returns a healthy response from the health endpoint', function () {
    /** @var TestResponse $response */
    $response = $this->getJson('/up');
    $response->assertOk();
});

it('returns a 404 JSON response for a completely unknown API route', function () {
    $response = $this->getJson('/api/v99/auth/login');

    $response->assertStatus(404)
             ->assertJson(['success' => false]);
});

it('returns a structured error response for any api route', function () {
    $response = $this->getJson('/api/v99/anything');

    $response->assertJson([
        'success' => false,
        'message' => 'Resource not found.',
    ]);
});

════════════════════════════════════════════════════════════
STEP 28 — README.md
════════════════════════════════════════════════════════════

Create file: README.md

# Ajeer Technical Assessment — Laravel 13

## Requirements
- PHP 8.3+
- Composer 2.x
- MySQL 8.0+
- Laravel 13.x

## Setup

### 1. Clone & Install
\`\`\`bash
git clone <repo-url>
cd ajeer-assessment
composer install
\`\`\`

### 2. Environment
\`\`\`bash
cp .env.example .env
php artisan key:generate
\`\`\`

Update `.env` with your database credentials:
\`\`\`env
DB_DATABASE=ajeer_assessment
DB_USERNAME=root
DB_PASSWORD=
\`\`\`

### 3. Database
\`\`\`bash
php artisan migrate
php artisan db:seed
\`\`\`

### 4. Run
\`\`\`bash
php artisan serve
\`\`\`

API base URL: `http://localhost:8000/api/v1`

## Architecture

| Concern        | Approach                                         |
|----------------|--------------------------------------------------|
| Pattern        | Repository + Service Layer + Strategy (Payments) |
| Auth           | Laravel Sanctum (Bearer Token)                   |
| API            | RESTful, versioned (`/api/v1/`)                  |
| Models         | ULID primary keys, SoftDeletes, PHP 8.3 enums    |
| DTOs           | spatie/laravel-data                              |
| Logs           | app / api_requests / payment (daily, rotating)   |
| Tests          | Pest (Laravel 13 default)                        |
| Keys           | ULIDs on all models (not auto-increment)         |

## Module Roadmap

| Module                                    | Status    |
|-------------------------------------------|-----------|
| Base Structure (Phase 1)                  | ✅ Done   |
| Multi-Gateway Payments (Phase 2)          | Pending   |
| Auth + Subscriptions + Services + Cart (Phase 3) | Pending |

## API Endpoints (Phase 1 — Skeleton)

All routes are prefixed with `/api/v1/`. Auth routes require `Authorization: Bearer {token}`.

\`\`\`
GET    /up                                    Health check
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout                   [auth]
GET    /api/v1/payments/gateways
POST   /api/v1/payments/initiate             [auth]
POST   /api/v1/payments/callback/{gateway}
GET    /api/v1/payments/transactions         [auth]
GET    /api/v1/payments/transactions/{id}    [auth]
POST   /api/v1/subscriptions/trial           [auth]
GET    /api/v1/subscriptions/my              [auth]
GET    /api/v1/services
GET    /api/v1/services/{id}
POST   /api/v1/services/{id}/book            [auth]
GET    /api/v1/cart                          [auth]
POST   /api/v1/cart/items                    [auth]
DELETE /api/v1/cart/items/{id}               [auth]
DELETE /api/v1/cart                          [auth]
GET    /api/v1/packages
GET    /api/v1/packages/{id}
POST   /api/v1/packages/{id}/add-to-cart     [auth]
\`\`\`

════════════════════════════════════════════════════════════
STEP 29 — FINAL VERIFICATION COMMANDS
════════════════════════════════════════════════════════════

Run these commands in order. All must complete without errors.

  php artisan config:clear
  php artisan route:clear
  php artisan cache:clear
  php artisan migrate
  php artisan route:list
  php artisan test

Expected results:
  ✓ All routes listed under /api/v1/ prefix
  ✓ No "Class not found" errors
  ✓ Migrations run without errors (including payment_transactions)
  ✓ `php artisan test` → 3 tests passing (HealthTest)
  ✓ php artisan route:list shows /up health route

════════════════════════════════════════════════════════════
STEP 30 — COMMIT
════════════════════════════════════════════════════════════

  git init
  git add .
  git commit -m "feat(phase-1): initialize Laravel 13 base — repository pattern, service layer, strategy stubs, ULID keys, DTOs, enums, API versioning, payment_transactions schema, Pest tests, structured logging"

Push to your public GitHub repository and email the link to:
  a.alsuhaibi@ajeer.app

════════════════════════════════════════════════════════════
END OF PHASE 1 PLAN — READY FOR /speckit.tasks THEN /speckit.implement
════════════════════════════════════════════════════════════
```

---

## ═══════════════════════════════════════
## PHASE 3 — /speckit.tasks
## ═══════════════════════════════════════

```
/speckit.tasks

Generate a task breakdown from the plan above.
Group tasks by phase: base-structure → payment-stubs → migrations → providers → tests.
Mark tasks that can run in parallel with [P].
Each task must include the exact file path it creates or modifies.
Order tasks to respect dependencies: contracts before implementations,
models before repositories, repositories before services, services before controllers.
```

---

## ═══════════════════════════════════════
## PHASE 4 — /speckit.implement
## ═══════════════════════════════════════

```
/speckit.implement

Execute all tasks from the task breakdown.
After completing all tasks, run:
  php artisan config:clear && php artisan migrate && php artisan test

Report any failures with the exact error output.
Do not proceed to Phase 2 until all 3 Pest tests pass.
```

---

## ═══════════════════════════════════════
## BUG FIX SUMMARY (corrections from original prompt)
## ═══════════════════════════════════════

| # | Bug | Original | Fixed |
|---|-----|----------|-------|
| 1 | `findById` return type mismatch | `findOrFail()` throws, but return type is `?Model` | Changed to `find()` which returns null |
| 2 | `update/deleteById` silent failures | `findOrFail()` throws on missing record | Changed to `find()` with explicit null check returning `false` |
| 3 | HealthTest wrong status code | `assertStatus(400)` for `/api/v99/` | Changed to `assertStatus(404)` — unknown routes return 404, not 400 |
| 4 | `App\Modules\` directory missing | Directories not created, classes referenced in config | Added `app/Modules/Payment/` to mkdir list + stub classes |
| 5 | `payment_transactions` table absent | No DB schema for payments | Added full migration in Step 21 |
| 6 | `bootstrap/providers.php` incomplete | Only described the change | Full file content provided |
| 7 | `declare(strict_types=1)` missing | No strict types declaration | Added to every file |
| 8 | User model confusion | Note implied User should extend BaseModel | Clarified: User keeps Authenticatable, only domain models extend BaseModel |
