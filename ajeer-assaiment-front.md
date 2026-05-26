# Ajeer API Explorer — Frontend Build Prompt
# Run this in Claude Code inside the ajeer-assessment Laravel project
# Output: a single self-contained HTML file served via Laravel

---

## Context

You are building a **self-contained API Explorer** for the ajeer-assessment
Laravel 13 backend. The goal is to visually test every endpoint and verify
that the API responses are correct — this is a developer QA tool, not a
production user interface.

The backend runs at: http://localhost:8000
All API endpoints are prefixed with: /api/v1/

The file must be served by Laravel so CORS is not an issue.
Create it at: public/explorer.html
Access it at: http://localhost:8000/explorer.html

---

## What to Build


A single HTML file (vanilla JS, no build step, no npm) that is:
- Self-contained — zero external dependencies except Google Fonts via CDN
- Functional — makes real fetch() calls to the local API
- Organized — grouped by module with clear visual hierarchy
- Informative — shows full request + response with status codes and timing
- Stateful — stores the auth token in memory and attaches it to all requests

---

## Aesthetic Direction

**Industrial terminal meets modern fintech dashboard.**

- Dark background: #0A0A0F
- Panel surfaces: #111118 with 1px border #1E1E2E
- Accent primary: #00D4AA (teal-green — Ajeer brand feeling)
- Accent danger: #FF4560
- Accent warning: #FFB547
- Accent muted: #3D3D52
- Text primary: #E8E8F0
- Text secondary: #7070A0
- Font: "JetBrains Mono" for code/responses, "Syne" for headings and labels
- Status codes: colored pills (green=2xx, yellow=3xx, red=4xx/5xx)
- JSON responses: syntax-highlighted with custom tokenizer
- Sidebar navigation: fixed left, module groups collapsible
- Request panel: right side, shows last request + response

No purple gradients. No Inter. No rounded-everything. Sharp edges, tight
spacing, monospace energy. Think "developer tool built by designers."

---

## Layout Structure

```
┌──────────────────────────────────────────────────────────────┐
│  HEADER: Ajeer API Explorer  |  Base URL input  |  Token pill │
├────────────────┬─────────────────────────────────────────────┤
│                │  ENDPOINT CARD                              │
│  SIDEBAR       │  ┌─────────────────────────────────────┐   │
│                │  │ METHOD  /api/v1/path    [RUN] button │   │
│  ▼ Auth        │  │ Body (JSON editor textarea)         │   │
│    register    │  └─────────────────────────────────────┘   │
│    login       │                                             │
│    logout      │  RESPONSE PANEL                             │
│    me          │  ┌─────────────────────────────────────┐   │
│                │  │ Status: 200 OK  |  Time: 124ms      │   │
│  ▼ Payments    │  │ ─────────────────────────────────── │   │
│    gateways    │  │ {                                   │   │
│    initiate    │  │   "success": true,                  │   │
│    callback    │  │   "data": { ... }                   │   │
│    transactions│  │ }                                   │   │
│    tx/{id}     │  └─────────────────────────────────────┘   │
│                │                                             │
│  ▼ Subscriptions  QUICK SCENARIOS                           │
│  ▼ Services    │  [ Run All Checks ]  [ Clear Token ]       │
│  ▼ Packages    │                                             │
│  ▼ Cart        │                                             │
│                │                                             │
│  ⚡ Scenarios  │                                             │
└────────────────┴─────────────────────────────────────────────┘
```

---

## Required Endpoints (all 22)

Build one card per endpoint. Each card has:
- HTTP method badge (colored: GET=teal, POST=blue, DELETE=red)
- URL with path params editable inline
- Request body textarea (pre-filled with realistic example JSON)
- [Run] button
- Response panel showing: status pill, timing, syntax-highlighted JSON

### Auth Module
```
POST /api/v1/auth/register
  body: { "name": "Yunes Demo", "email": "yunes@ajeer.app",
          "password": "password123", "password_confirmation": "password123",
          "city": "Riyadh" }

POST /api/v1/auth/login
  body: { "email": "demo@ajeer.app", "password": "password" }
  → On success: extract token from data.token and store it

POST /api/v1/auth/logout
  → requires token

GET  /api/v1/auth/me
  → requires token
```

### Payment Module
```
GET  /api/v1/payments/gateways?city=Riyadh&module=booking
  query params editable: city, module

POST /api/v1/payments/initiate
  body: { "gateway": "moyasar", "amount": 49.00, "currency": "SAR",
          "module": "subscription", "city": "Riyadh",
          "idempotency_key": "auto-generated-uuid" }
  → Auto-generate idempotency_key with crypto.randomUUID()
  → requires token

POST /api/v1/payments/callback/{gateway}
  gateway dropdown: moyasar | stripe | tap
  body: { "id": "moy_demo_ref_001", "status": "paid" }

GET  /api/v1/payments/transactions
  → requires token

GET  /api/v1/payments/transactions/{id}
  id: editable text input
  → requires token
```

### Subscription Module
```
GET  /api/v1/subscriptions/plans

POST /api/v1/subscriptions/trial
  → requires token

GET  /api/v1/subscriptions/my
  → requires token
```

### Service Module
```
GET  /api/v1/services
  query params: per_page=10, category_id (optional)

GET  /api/v1/services/{id}
  id: editable, with "pick from list" button that fetches /services first

POST /api/v1/services/{id}/book
  id: editable
  body: { "scheduled_at": "<tomorrow ISO>", "notes": "Please call before arriving" }
  → requires token
```

### Package Module
```
GET  /api/v1/packages

GET  /api/v1/packages/{id}
  id: editable

POST /api/v1/packages/{id}/add-to-cart
  id: editable
  body: { "quantity": 1 }
  → requires token
```

### Cart Module
```
GET    /api/v1/cart
  → requires token

POST   /api/v1/cart/items
  body: { "item_id": "<service-id>", "item_type": "service", "quantity": 1 }
  → requires token

DELETE /api/v1/cart/items/{cartItemId}
  cartItemId: editable
  → requires token

DELETE /api/v1/cart
  → requires token
```

---

## Quick Scenarios Panel

Below all endpoint cards, add a "⚡ Quick Scenarios" section with
pre-built automated flows that run multiple endpoints in sequence:

### Scenario 1 — Full Auth Flow
```
1. POST /auth/register (new random email)
2. Store token automatically
3. GET  /auth/me
4. POST /auth/logout
Report: ✅ PASS or ❌ FAIL with reason
```

### Scenario 2 — Trial Subscription (Once Only)
```
1. POST /auth/login (demo@ajeer.app)
2. POST /subscriptions/trial → expect 201
3. POST /subscriptions/trial again → expect 422
4. GET  /subscriptions/my → show active subscription
Report: ✅ PASS if second trial returns 422
```

### Scenario 3 — Gateway City Filter
```
1. GET /payments/gateways?city=Riyadh&module=booking
   → Tap should appear
2. GET /payments/gateways?city=Medina&module=booking
   → Tap should NOT appear
3. GET /payments/gateways?city=Riyadh&module=subscription
   → Stripe should appear, Tap should NOT
Report: ✅ PASS if all 3 filters are correct
```

### Scenario 4 — Idempotency Check
```
1. POST /auth/login (demo@ajeer.app)
2. POST /payments/initiate with key="idem-test-001"
3. POST /payments/initiate with SAME key="idem-test-001"
4. Compare transaction IDs in both responses
Report: ✅ PASS if both responses have identical transaction id
```

### Scenario 5 — Cart Flow
```
1. POST /auth/login (demo@ajeer.app)
2. GET  /services → pick first active service id
3. POST /cart/items (service, qty=1)
4. POST /cart/items (SAME service, qty=1) → should increment not duplicate
5. GET  /cart → verify items.length=1 AND total_items=2
6. DELETE /cart → clear
7. GET  /cart → verify total_items=0
Report: ✅ PASS if step 5 shows 1 item row with qty=2
```

### [▶ Run All Scenarios] button
Runs all 5 in sequence, shows a summary table:
```
┌──────────────────────────────────┬────────┐
│ Scenario                         │ Result │
├──────────────────────────────────┼────────┤
│ 1. Full Auth Flow                │  ✅    │
│ 2. Trial Subscription Once Only  │  ✅    │
│ 3. Gateway City Filter           │  ✅    │
│ 4. Idempotency Check             │  ✅    │
│ 5. Cart Flow                     │  ❌    │
└──────────────────────────────────┴────────┘
  4/5 PASSED — Click ❌ to see details
```

---

## Technical Requirements

### Token Management
```javascript
// State
let authToken = null;
let baseUrl   = 'http://localhost:8000';

// After login/register
function setToken(token) {
  authToken = token;
  // Update token pill in header: show first 20 chars + ...
  document.getElementById('token-display').textContent =
    token ? token.substring(0, 20) + '...' : 'No token';
}

// Build headers
function buildHeaders(requiresAuth) {
  const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
  if (requiresAuth && authToken) {
    headers['Authorization'] = `Bearer ${authToken}`;
  }
  return headers;
}
```

### Request Execution
```javascript
async function runRequest(method, path, body, requiresAuth) {
  const start = performance.now();
  const url   = baseUrl + path;

  const options = {
    method,
    headers: buildHeaders(requiresAuth),
  };

  if (body && method !== 'GET') {
    options.body = JSON.stringify(body);
  }

  try {
    const response = await fetch(url, options);
    const duration = Math.round(performance.now() - start);
    const data     = await response.json();

    return { status: response.status, duration, data, ok: response.ok };
  } catch (err) {
    return { status: 0, duration: 0, data: { error: err.message }, ok: false };
  }
}
```

### JSON Syntax Highlighter
```javascript
function highlight(json) {
  const str = JSON.stringify(json, null, 2);
  return str
    .replace(/("[\w-]+")\s*:/g, '<span class="json-key">$1</span>:')
    .replace(/:\s*(".*?")/g, ': <span class="json-string">$1</span>')
    .replace(/:\s*(true|false)/g, ': <span class="json-bool">$1</span>')
    .replace(/:\s*(null)/g, ': <span class="json-null">$1</span>')
    .replace(/:\s*(-?\d+\.?\d*)/g, ': <span class="json-number">$1</span>');
}
```

### Status Code Styling
```
2xx → color: #00D4AA  label: OK
3xx → color: #FFB547  label: REDIRECT
4xx → color: #FF4560  label: ERROR
5xx → color: #FF4560  label: SERVER ERROR
0   → color: #7070A0  label: NETWORK ERROR
```

### Auto-fill Helpers
- `[Today + 3 days]` button on scheduled_at fields → fills ISO timestamp
- `[Generate UUID]` button on idempotency_key → fills crypto.randomUUID()
- After GET /services responds, fill service ID fields automatically
- After GET /packages responds, fill package ID fields automatically
- After GET /payments/transactions responds, fill transaction ID field

---

## File Output

Create file: public/explorer.html

The complete file must:
- Work when opened at http://localhost:8000/explorer.html
- Require zero build tools
- Load JetBrains Mono and Syne from Google Fonts CDN only
- Be fully functional with ~800-1000 lines of clean HTML/CSS/JS
- Have all 22 endpoint cards + 5 scenarios + report table
- Handle loading states (spinner/pulse on [Run] button while fetching)
- Handle errors gracefully (network errors, JSON parse errors)

After creating the file, output:
  ✅ Created: public/explorer.html
  🌐 Open: http://localhost:8000/explorer.html
  📋 22 endpoints | 5 scenarios | Full response viewer