# Data Model & Schema Design: Ajeer Assessment

This document outlines the database schema design, model classes, field specifications, relationships, and validation rules for the **ajeer-assessment** API modules.

---

## 1. Entity: User
Represents a registered platform user. Extends `Illuminate\Foundation\Auth\User` for Sanctum authentication compatibility but uses ULID primary keys and SoftDeletes.

- **Primary Key**: `id` (ULID string)
- **Table Name**: `users`
- **Fields**:
  - `id` (ULID, PK)
  - `name` (string)
  - `email` (string, unique)
  - `email_verified_at` (timestamp, nullable)
  - `password` (string)
  - `phone` (string)
  - `city` (string) - Used by the payment gateway availability engine.
  - `status` (string/enum: `active`, `inactive`, `banned`. Default: `active`)
  - `remember_token` (string, nullable)
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - SoftDeletes)
- **Eloquent Casts**:
  - `id` => `string`
  - `status` => `App\Enums\UserStatus` (backed enum)
- **Relationships**:
  - `hasMany(PaymentTransaction::class)`
  - `hasOne(Subscription::class)`
  - `hasMany(Booking::class)`
  - `hasOne(Cart::class)`

---

## 2. Entity: PaymentTransaction
Records every payment attempt made through the multi-gateway platform.

- **Primary Key**: `id` (ULID string)
- **Table Name**: `payment_transactions`
- **Fields**:
  - `id` (ULID, PK)
  - `user_id` (ULID, FK index, nullable for guests, but here authenticating users is P1)
  - `gateway` (string/enum: `moyasar`, `stripe`, `tap`)
  - `gateway_transaction_id` (string, nullable, unique index - set upon redirect callback/webhook)
  - `module` (string/enum: `subscription`, `booking`, `cart`)
  - `amount` (decimal, 10, 2)
  - `currency` (string/enum: `SAR`, `USD`, `EUR`. Default: `SAR`)
  - `status` (string/enum: `pending`, `success`, `failed`, `refunded`. Default: `pending`)
  - `gateway_payload` (json, nullable) - Raw payload sent to initiate.
  - `gateway_response` (json, nullable) - Callback/webhook outcome payload.
  - `paid_at` (timestamp, nullable)
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - SoftDeletes)
- **Eloquent Casts**:
  - `id` => `string`
  - `gateway` => `App\Enums\PaymentGateway`
  - `status` => `App\Enums\TransactionStatus`
  - `currency` => `App\Enums\Currency`
  - `gateway_payload` => `array`
  - `gateway_response` => `array`
  - `paid_at` => `datetime`
- **Relationships**:
  - `belongsTo(User::class)`

---

## 3. Entity: Subscription
Tracks the trial and paid subscription states for users.

- **Primary Key**: `id` (ULID string)
- **Table Name**: `subscriptions`
- **Fields**:
  - `id` (ULID, PK)
  - `user_id` (ULID, FK index, unique) - One active subscription record per user
  - `plan` (string/enum: `trial`, `basic`, `premium`)
  - `status` (string/enum: `trial`, `active`, `expired`, `canceled`)
  - `starts_at` (timestamp)
  - `ends_at` (timestamp)
  - `trial_used` (boolean, default: false) - Enforces one-time trial limit
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - SoftDeletes)
- **Eloquent Casts**:
  - `id` => `string`
  - `plan` => `App\Enums\SubscriptionPlan`
  - `status` => `App\Enums\SubscriptionStatus`
  - `starts_at` => `datetime`
  - `ends_at` => `datetime`
  - `trial_used` => `boolean`
- **Relationships**:
  - `belongsTo(User::class)`

---

## 4. Entity: Service
Represents standard maintenance and booking services offered by the platform.

- **Primary Key**: `id` (ULID string)
- **Table Name**: `services`
- **Fields**:
  - `id` (ULID, PK)
  - `name` (string)
  - `category` (string) - Seeded, e.g. `plumbing`, `electricity`, `cleaning`.
  - `description` (text, nullable)
  - `price` (decimal, 10, 2)
  - `duration_minutes` (integer)
  - `is_available` (boolean, default: true)
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - SoftDeletes)
- **Eloquent Casts**:
  - `id` => `string`
  - `is_available` => `boolean`
- **Relationships**:
  - `hasMany(Booking::class)`
  - `belongsToMany(Package::class, 'package_service')`

---

## 5. Entity: Booking
A scheduled maintenance or task appointment for active subscribers.

- **Primary Key**: `id` (ULID string)
- **Table Name**: `bookings`
- **Fields**:
  - `id` (ULID, PK)
  - `user_id` (ULID, FK index)
  - `service_id` (ULID, FK index)
  - `scheduled_at` (timestamp)
  - `status` (string/enum: `pending`, `confirmed`, `completed`, `canceled`. Default: `pending`)
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - SoftDeletes)
- **Eloquent Casts**:
  - `id` => `string`
  - `status` => `App\Enums\BookingStatus`
  - `scheduled_at` => `datetime`
- **Relationships**:
  - `belongsTo(User::class)`
  - `belongsTo(Service::class)`

---

## 6. Entity: Cart
A simple cart entity linked to each user.

- **Primary Key**: `id` (ULID string)
- **Table Name**: `carts`
- **Fields**:
  - `id` (ULID, PK)
  - `user_id` (ULID, FK index, unique)
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - SoftDeletes)
- **Relationships**:
  - `belongsTo(User::class)`
  - `hasMany(CartItem::class)`

---

## 7. Entity: CartItem
Represents an individual item (service) inside a shopping cart.

- **Primary Key**: `id` (ULID string)
- **Table Name**: `cart_items`
- **Fields**:
  - `id` (ULID, PK)
  - `cart_id` (ULID, FK index)
  - `service_id` (ULID, FK index)
  - `quantity` (integer, default: 1) - Kept as a field, though US8 defaults to creating new rows per item.
  - `price` (decimal, 10, 2)
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - SoftDeletes)
- **Relationships**:
  - `belongsTo(Cart::class)`
  - `belongsTo(Service::class)`

---

## 8. Entity: Package
A package represents a bundle of pre-selected services sold at a bundled price.

- **Primary Key**: `id` (ULID string)
- **Table Name**: `packages`
- **Fields**:
  - `id` (ULID, PK)
  - `name` (string)
  - `description` (text, nullable)
  - `price` (decimal, 10, 2)
  - `created_at` (timestamp)
  - `updated_at` (timestamp)
  - `deleted_at` (timestamp, nullable - SoftDeletes)
- **Relationships**:
  - `belongsToMany(Service::class, 'package_service')`

---

## 9. Entity: PackageService (Pivot)
Connects packages to their grouped constituent services.

- **Table Name**: `package_service`
- **Fields**:
  - `package_id` (ULID, FK index)
  - `service_id` (ULID, FK index)
- **Indices**:
  - Composite primary key or unique index on `(package_id, service_id)`
