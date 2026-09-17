# Development Roadmap & Project Health Evaluation: Ready eCommerce (Apolo)

A comprehensive technical readiness evaluation and development roadmap for the Ready eCommerce (Apolo) web and mobile application suite.

---

## 1. Project Health & Architecture Quality

The project represents a functional, customized multi-vendor marketplace combined with an ERP accounting ledger. 

### 1.1 Code Quality & Structure
- **Laravel Backend (v11.x)**: **Good**. Code follows standard Laravel conventions, utilizing the Repository-Service pattern. Business transactions are wrapped in DB transactions.
- **Vue.js Storefront (v3.x)**: **Good**. Uses the Composition API, Pinia state stores, and dynamic layout routing, keeping codebase dependencies separated.
- **Flutter Mobile Client (v3.x)**: **Fair/Good**. Leverages Riverpod for state management. While the multi-niche code separation is clean, the presence of unused endpoints (e.g. Gifts/Returns) and boilerplate naming conventions (e.g., `laundrySeller_authBox`) indicate incomplete refactoring.

### 1.2 Security Review
- **Sanctum Authentication**: API routes are secured via Laravel Sanctum Bearer tokens.
- **Spatie ACL Permissions**: Permissions are verified via request route names. Blacklists (`UserNonPermission`) allow granular control over individual employee permissions.
- **WebSocket Broadcast Security**: 
  - **Risk**: Chat events (`SendMessageToUser`) broadcast on public channels rather than `PrivateChannel` sockets, exposing chat parameters to eavesdropping.
  - **Remedy**: Re-route WebSocket channels to private authenticated scopes.

### 1.3 Performance Review
- **API Caching**: Cache queries in `AppServiceProvider.php` (such as active settings and categories) prevent redundant database lookups.
- **Database Indexing**: Standard foreign key mappings index basic tables. 
  - **Debt**: The custom double-entry ledger queries (`VoucherEntry`, `AccountBalance`) lack explicit compound indexing on `(account_id, shop_id, financial_year_id)`. High-volume POS invoicing may suffer from database slowdowns over time.

---

## 2. Technical Debt & Development Risks

### 2.1 Technical Debt Catalog
1. **Broken API Features in Mobile**: The Flutter client calls return order and gift endpoints (e.g. `/api/return-order`, `/api/gifts`) that do not exist on the Laravel backend, leading to 404 errors.
2. **Missing Real-Time GPS Tracking Broadcast**: The Flutter client expects coordinates via Pusher on `"rider-location.$riderId"`, but the Laravel backend has no background coordinates scheduler or broad-caster.
3. **Single-Niche Web Storefront**: The web storefront only supports general eCommerce, whereas the mobile client is structured for eCommerce, Food, Grocery, and Pharmacy.

### 2.2 Risk Profiles
- **High-Risk Modules**:
  - `VoucherService.php`: Disrupting this file will break double-entry financial posting and inventory ledger balances.
  - `CheckPermission.php`: Security routing modifications here can inadvertently expose administrative features.
  - Dynamic route switch cases in Flutter (`routes.dart`): Minor syntax changes can break navigation across the app's niches.
- **Low-Risk Modules**:
  - Storefront banners, product catalogs, customer profile updates, blog articles, and help-desk ticket updates.

---

## 3. Recommended Improvement & Refactoring Strategy

1. **Implement Return/Refund APIs on Backend (Critical)**: Create the necessary controllers, models, and migrations for return orders on the backend to fix the broken refund flow in the mobile app.
2. **Align Web Multi-Niche UI (High)**: Update the Vue.js web storefront to support dynamic switches for restaurant menus, grocery carts, and prescription uploads, matching the mobile app's feature set.
3. **WebSocket Security Hardening (High)**: Convert public chat broadcast channels to `PrivateChannel` wrappers, securing conversations behind proper authentication checks.
4. **Optimize Ledger Indexing (Medium)**: Add compound index migrations on the accounting balance tables to ensure fast POS performance.

---

## 4. Testing & Development Workflows

### 4.1 Recommended Testing Strategy
```
┌────────────────────────────────────────────────────────┐
│                      Unit Tests                        │
│      - VoucherService debit vs credit balancing        │
│      - Form request parameter validations              │
└───────────────────────────┬────────────────────────────┘
                            │ Pass
                            ▼
┌────────────────────────────────────────────────────────┐
│                   Integration Tests                    │
│      - Checkout splits by shop ID                      │
│      - Payment popup redirect loops & success callbacks│
└───────────────────────────┬────────────────────────────┘
                            │ Pass
                            ▼
┌────────────────────────────────────────────────────────┐
│                   Manual / UI Verification             │
│      - Multi-niche Flutter dashboard routing checks    │
│      - Physical POS thermal PDF printer layouts        │
└────────────────────────────────────────────────────────┘
```

- **Unit Testing**: Run PHPUnit tests to verify accounting balances and validation rules.
- **Integration Testing**: Validate checkout splits, order creation, and payment callbacks.
- **End-to-End Testing**: Test the payment popup workflow and mobile Webview redirects using dummy gateway profiles (Stripe / PayPal sandbox).

### 4.2 Git Workflow & Branching
Use the **Feature Branch Workflow**:
- `main`: Production-ready branch.
- `staging`: Quality assurance and release candidate validations.
- `feature/*`: Developers work on isolated branches (e.g. `feature/refund-apis`) and submit Pull Requests to `staging`.

---

## 5. Module Complexity & Estimation Matrix

| Development Module | Target File / Area | Expected Complexity | Estimated Time | Priority |
| :--- | :--- | :--- | :--- | :--- |
| **Return Order APIs** | Backend controllers, migrations, models | **Medium** | 3-5 Days | **Critical** |
| **Return Orders Web UI** | Vue views, dashboard routing | **Medium** | 2-3 Days | **High** |
| **Gifts Module APIs** | Backend migrations, routes, models | **Low/Medium** | 2-4 Days | **High** |
| **GPS Map Tracking APIs** | Location endpoints, coordinate broadcasts | **Medium/High** | 5-7 Days | **High** |
| **Web Multi-Niche Layouts** | Vue.js components, page variants | **High** | 10-15 Days | **Medium** |
| **WebSocket Chat Security** | Broadcast event scripts, channel auth | **Low** | 1 Day | **High** |
| **Ledger DB Optimization** | Migration indexing | **Low** | 1 Day | **Medium** |

---

## 6. Executive Summary

### Project State
Ready eCommerce (Apolo) is a multi-vendor platform with a customized ERP bookkeeping system. The core backend (Laravel) and web storefront (Vue) codebases are stable. However, there are significant gaps between the web and mobile platforms.

### Key Risks
The mobile client contains features (Product Returns, Shop Gifts, and Real-Time Driver Tracking Maps) that call non-existent backend endpoints, resulting in API errors. Additionally, the web storefront is restricted to general eCommerce, while the mobile client supports four separate business niches.

### Strategic Recommendations
Prioritize implementing the missing return, gift, and location endpoints on the backend to fix the broken mobile app features. Next, secure WebSockets by migrating to private Pusher channels, and optimize the database to handle high POS transaction volumes. Once these core issues are resolved, update the web storefront to support the same multi-niche layouts as the mobile client.
