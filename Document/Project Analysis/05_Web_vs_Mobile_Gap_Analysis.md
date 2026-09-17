# Web vs. Mobile Gap Analysis: Ready eCommerce (Apolo)

A detailed technical gap analysis comparing the Laravel + Vue.js Web Storefront with the Flutter Mobile application, highlighting feature mismatches, API discrepancies, logic offsets, and database gaps.

---

## 1. Feature Availability Comparison

### 1.1 Features Available on Web but Missing/Different in Mobile
| Feature Name | Web Implementation | Mobile (Flutter) Status | Priority |
| :--- | :--- | :--- | :--- |
| **Complete POS Dashboard** | Highly detailed web dashboard for cashiers (`POSController`, `PosCartRepository`) with counter selection and draft sales. | Missing. POS is only available on the web portal for store clerks. | **Medium** |
| **Social Login Gateways** | Native Facebook, Google, and Apple login modals integrated via web SDK endpoints. | Requires different mobile-native SDK setups (e.g. Firebase Auth wrappers or Google Sign-In plugins). | **High** |
| **Voucher Collection and Application** | Visual dashboard allowing users to collect vouchers (`voucher.collect`) and apply generic discounts. | Implemented via basic API calls, but UI lacks the same visual grid cards. | **Low** |

---

### 1.2 Features Available in Mobile but Missing/Different on Web
| Feature Name | Mobile (Flutter) Implementation | Web (Vue.js) Status | Priority |
| :--- | :--- | :--- | :--- |
| **Dynamic Multi-Niche Mode Switcher** | The UI dynamically adapts to four niche layouts (**eCommerce, Food, Grocery, Pharmacy**) depending on the setting. | **Missing**. The Vue storefront is strictly configured for general eCommerce. | **Critical** |
| **Doctor Prescription Uploads** | Interface in the Pharmacy layout to upload doctor prescription files (photos/PDFs). | **Missing**. No file upload or prescription review flow exists in the Vue SPA. | **Critical** |
| **Live Delivery Rider Tracking Map** | Animated tracking page (`OrderTrackView`) integrating Google Maps to show the rider's coordinates. | **Missing**. The web storefront only shows static text statuses (e.g., "On the Way"). | **High** |
| **Address GPS Geolocation** | Pinpoints addresses using Google Maps to fetch latitude/longitude coordinates. | **Missing**. The web form only uses text fields (flat, street, postcode, area). | **High** |
| **Shop Gifts Module** | Shop owners can attach gift cards or items to the cart (`getAllGifts`, `addGiftToCart`). | **Missing**. There is no visual gift listing or checkout option on the web storefront. | **Medium** |

---

## 2. API & Backend Endpoint Mismatch
These are critical gaps where the Flutter client is configured to hit endpoints that are completely missing from the Laravel backend.

### 2.1 Missing Return Order API Endpoints
- **Mobile Client**: Calls return-order endpoints:
  - `returnOrderSubmit` (`POST` -> `/api/return-order`)
  - `returnHistory` (`GET` -> `/api/return-history`)
  - `returnOrdersList` (`GET` -> `/api/return-orders`)
  - `returnOrderDetails` (`GET` -> `/api/return-order-details`)
- **Laravel Backend**: **None of these routes or controllers exist** in `routes/api.php` or `app/Http/Controllers/`.
- **Impact**: Triggering a product return in the mobile app returns a `404 Not Found` HTTP error.
- **Priority**: **Critical**

### 2.2 Missing Gifts Module API Endpoints
- **Mobile Client**: Calls gift-related endpoints:
  - `getAllGifts` (`GET` -> `/api/gifts`)
  - `addGift` (`POST` -> `/api/gift/store`)
  - `removeGift` (`DELETE` -> `/api/gift/delete`)
- **Laravel Backend**: **These routes are missing** from the backend router.
- **Impact**: Selecting gifts on mobile results in `404 Not Found` API exceptions.
- **Priority**: **High**

### 2.3 Commented-out and Missing WebSocket Channels
- **Mobile Client**: Subscribes to the Pusher channel `"rider-location.$riderId"` to fetch real-time map coordinate updates.
- **Laravel Backend**: The `rider-location` event class and authorization callback in `routes/channels.php` are missing. Additionally, the user message channel callback in `routes/channels.php` is commented out.
- **Impact**: The live rider tracking map remains static, and chats revert to polling or fallback queries.
- **Priority**: **High**

---

## 3. Business Logic Mismatch

### 3.1 Cart Syncing Behavior
- **Web Storefront**: Every cart addition, quantity update, or deletion is synced directly to the backend database via `/cart/store`, `/cart/increment`, and `/cart/decrement` immediately.
- **Mobile Client**: Uses a local **Hive database cache** (`hive_cart_model_box`). Updates are managed locally and synced in batches, creating potential mismatches if a user switches between web and mobile devices.
- **Priority**: **Medium**

### 3.2 GPS vs Text Addresses
- **Mobile Client**: Uses geographical coordinates (`latitude` and `longitude`) to calculate shipping fees, verifying nearby shops using coordinate bounds.
- **Web Storefront**: Bypasses GPS coordinates and relies entirely on text-based postal codes and areas, which can lead to discrepancies in shipping fee calculations.
- **Priority**: **High**

---

## 4. Validation Mismatch

### 4.1 Signup Parameters Validation
- **Mobile Client**: The `SingUp` model requires both `country` and `phone_code` to construct registration requests.
- **Web Storefront**: The signup modal includes a `country` selector but does not collect or send a `phone_code`.
- **Laravel Backend**: `RegistrationRequest.php` validates `country` but does not enforce validation on `phone_code` to accommodate the web storefront client payload.
- **Priority**: **Medium**

---

## 5. Database Schema Gaps
The following database tables are required by the mobile application's business logic but are currently missing from the database migrations:

### 5.1 Return Orders & Refund Tables
- **Required Tables**: `return_orders`, `return_items`, `refund_transactions`.
- **Reason**: Needed to store refund requests, return reasons, and image uploads from the mobile app.
- **Priority**: **Critical**

### 5.2 Gifts Module Tables
- **Required Tables**: `gifts`, `gift_shop`, `cart_gifts`.
- **Reason**: Needed to store gift listings, map them to specific shops, and track selected gifts in user shopping carts.
- **Priority**: **High**

---

## 6. Authentication Mismatch

### 6.1 Device Key Registrations
- **Mobile Client**: Appends Firebase Cloud Messaging tokens (`device_key` and `device_type`) automatically during login and registration API calls to register the device.
- **Web Storefront**: Bypasses FCM tokens entirely, managing notifications via in-app database queries and Pusher public channels.
- **Priority**: **Medium**

### 6.2 Token Expiry Actions
- **Mobile Client**: Token failures (HTTP 401) trigger Dio interceptors to clear the Hive box cache and redirect the user back to the login screen (`Routes.login`).
- **Web Storefront**: Token failures wipe Pinia stores and redirect users back to the homepage (`/`), opening the login overlay modal.
- **Priority**: **Low**
