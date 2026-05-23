# API v1 Endpoint Contracts: Ajeer Assessment

All API endpoints are versioned under `/api/v1/` and returned formatted JSON responses as mandated by the project constitution.

---

## 1. Authentication Module

### Register a User
- **Method**: `POST`
- **URL**: `/api/v1/auth/register`
- **Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Request Body**:
```json
{
  "name": "Yonas Al-Khaledi",
  "email": "yonas@ajeer.app",
  "password": "StrongPassword123!",
  "password_confirmation": "StrongPassword123!",
  "phone": "+966500000000",
  "city": "Riyadh"
}
```
- **Response (201 Created)**:
```json
{
  "success": true,
  "message": "User registered successfully.",
  "data": {
    "token": "1|sanctum_generated_bearer_token_string",
    "user": {
      "id": "01H2PJEXK1Z56G7HJK8L9MN0PQ",
      "name": "Yonas Al-Khaledi",
      "email": "yonas@ajeer.app",
      "phone": "+966500000000",
      "city": "Riyadh",
      "status": "active"
    }
  }
}
```

### Log In
- **Method**: `POST`
- **URL**: `/api/v1/auth/login`
- **Request Body**:
```json
{
  "email": "yonas@ajeer.app",
  "password": "StrongPassword123!"
}
```
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "token": "2|sanctum_generated_bearer_token_string",
    "user": {
      "id": "01H2PJEXK1Z56G7HJK8L9MN0PQ",
      "name": "Yonas Al-Khaledi",
      "email": "yonas@ajeer.app",
      "phone": "+966500000000",
      "city": "Riyadh",
      "status": "active"
    }
  }
}
```

### Log Out
- **Method**: `POST`
- **URL**: `/api/v1/auth/logout`
- **Headers**:
  - `Authorization: Bearer {token}`
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Tokens revoked and logged out successfully."
}
```

---

## 2. Multi-Gateway Payment Module

### Retrieve Available Payment Gateways
- **Method**: `GET`
- **URL**: `/api/v1/payments/gateways?city=Riyadh&module=booking`
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Available payment gateways retrieved.",
  "data": {
    "gateways": [
      {
        "name": "moyasar",
        "label": "Moyasar Payments",
        "enabled": true
      },
      {
        "name": "tap",
        "label": "Tap Payments",
        "enabled": true
      }
    ]
  }
}
```

### Initiate a Payment
- **Method**: `POST`
- **URL**: `/api/v1/payments/initiate`
- **Headers**:
  - `Authorization: Bearer {token}`
- **Request Body**:
```json
{
  "gateway": "moyasar",
  "module": "booking",
  "amount": 150.00,
  "currency": "SAR"
}
```
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Payment initiated successfully.",
  "data": {
    "transaction_id": "01H2PJFABCDE1234567890ABCD",
    "gateway": "moyasar",
    "status": "pending",
    "redirect_url": "https://api.moyasar.com/v1/payments/simulate_redirect"
  }
}
```

### Handle Gateway Callbacks
- **Method**: `POST`
- **URL**: `/api/v1/payments/callback/{gateway}`
- **Request Body (Moyasar Callback Example)**:
```json
{
  "id": "moy_txn_88776655",
  "status": "paid",
  "amount": 15000,
  "currency": "SAR",
  "message": "Succeeded"
}
```
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Callback processed successfully."
}
```

### View Transaction History
- **Method**: `GET`
- **URL**: `/api/v1/payments/transactions`
- **Headers**:
  - `Authorization: Bearer {token}`
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Transactions retrieved successfully.",
  "data": {
    "transactions": [
      {
        "id": "01H2PJFABCDE1234567890ABCD",
        "gateway": "moyasar",
        "amount": "150.00",
        "currency": "SAR",
        "status": "success",
        "paid_at": "2026-05-23T18:00:00+03:00"
      }
    ],
    "pagination": {
      "total": 1,
      "per_page": 15,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

---

## 3. Subscription & Booking Module

### Activate Trial Subscription
- **Method**: `POST`
- **URL**: `/api/v1/subscriptions/trial`
- **Headers**:
  - `Authorization: Bearer {token}`
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "14-day free trial activated successfully.",
  "data": {
    "subscription": {
      "id": "01H2PJG9999888877776666555",
      "plan": "trial",
      "status": "trial",
      "starts_at": "2026-05-23T18:07:00+03:00",
      "ends_at": "2026-06-06T18:07:00+03:00"
    }
  }
}
```

### Browse Services
- **Method**: `GET`
- **URL**: `/api/v1/services?category=plumbing`
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Services retrieved successfully.",
  "data": [
    {
      "id": "01H2PJH1112223334445556667",
      "name": "AC Cleaning & Filter Wash",
      "category": "maintenance",
      "price": "180.00",
      "duration_minutes": 60,
      "is_available": true
    }
  ]
}
```

### Book a Service
- **Method**: `POST`
- **URL**: `/api/v1/services/{id}/book`
- **Headers**:
  - `Authorization: Bearer {token}`
- **Request Body**:
```json
{
  "scheduled_at": "2026-05-25T10:00:00+03:00"
}
```
- **Response (201 Created)**:
```json
{
  "success": true,
  "message": "Service booked successfully.",
  "data": {
    "booking": {
      "id": "01H2PJK9876543210ABCDEF123",
      "service": {
        "id": "01H2PJH1112223334445556667",
        "name": "AC Cleaning & Filter Wash"
      },
      "scheduled_at": "2026-05-25T10:00:00+03:00",
      "status": "confirmed"
    }
  }
}
```

---

## 4. Cart & Packages Module

### Add Service to Cart
- **Method**: `POST`
- **URL**: `/api/v1/cart/items`
- **Headers**:
  - `Authorization: Bearer {token}`
- **Request Body**:
```json
{
  "service_id": "01H2PJH1112223334445556667"
}
```
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Item added to cart.",
  "data": {
    "cart_item": {
      "id": "01H2PJLZZZZYYYYXXXXWWWWVVV",
      "service_id": "01H2PJH1112223334445556667",
      "price": "180.00"
    }
  }
}
```

### Add Package to Cart (expands into constituent services)
- **Method**: `POST`
- **URL**: `/api/v1/packages/{id}/add-to-cart`
- **Headers**:
  - `Authorization: Bearer {token}`
- **Response (200 OK)**:
```json
{
  "success": true,
  "message": "Package expanded and constituent services added to cart.",
  "data": {
    "added_items": [
      {
        "id": "01H2PJLZZZZYYYYXXXXWWWWVV1",
        "service_id": "01H2PJH1112223334445556667",
        "price": "100.00"
      },
      {
        "id": "01H2PJLZZZZYYYYXXXXWWWWVV2",
        "service_id": "01H2PJH1112223334445556668",
        "price": "80.00"
      }
    ]
  }
}
```
