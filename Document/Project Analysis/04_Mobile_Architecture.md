# Mobile Application Architecture & Client-Side Design: Ready eCommerce (Apolo)

A comprehensive analysis of the Flutter-based cross-platform mobile application, covering its directory structure, dynamic routing system, state management architecture (Riverpod), local storage (Hive), and messaging systems.

---

## 1. Mobile Folder Structure

The mobile client is a cross-platform application written in **Flutter/Dart**. It implements a modular structure that dynamically targets four business domains (General eCommerce, Food, Grocery, Pharmacy) under a unified codebase.

### Detailed Directory Tree (`Mobile/lib`)
```
lib/
├── components/                       # Reusable UI Custom Widgets
│   ├── buttons/                      # Primary, secondary, text, and icon buttons
│   ├── cards/                        # Grid, product, shop, and review card layouts
│   ├── dialogs/                      # Confirmation boxes, popups, and alert messages
│   └── textfields/                   # Validated text, phone, and password inputs
├── config/                           # Application Configurations & Styling
│   ├── app_color.dart                # Dynamic theme color variables (credits Hex values from settings)
│   ├── app_constants.dart            # Base URLs, endpoints, Pusher keys, and Hive box names
│   └── theme.dart                    # Application light and dark theme configurations
├── controllers/                      # Riverpod State Management Notifiers
│   ├── common/                       # Shared controllers (Master, Blog, Country, Base logic)
│   ├── eCommerce/                    # eCommerce controllers (Cart, Checkout, Products, Profile, etc.)
│   ├── food/                         # Food delivery modules controllers
│   ├── grocery/                      # Grocery shopping modules controllers
│   ├── pharmacy/                     # Pharmacy medicine modules controllers
│   └── misc/                         # Miscellaneous trackers
├── gen/                              # Automatically compiled code (theme assets, etc.)
├── generated/                        # Localized translation assets (compiled from l10n)
├── l10n/                             # ARB translation files supporting multiple locales
├── models/                           # Pure Dart Model classes mapping HTTP payloads
│   ├── eCommerce/                    # eCommerce data mapping (Cart, Order, Address, User, Product)
│   ├── food/                         # Restaurant and food model mappings
│   ├── grocery/                      # Groceries model mappings
│   └── pharmacy/                     # Medicines model mappings
├── services/                         # REST APIs & Database Integration Layer
│   ├── base/                         # Abstract contract classes defining repository methods
│   │   └── eCommerce/                # eCommerce base provider interfaces
│   ├── common/                       # Shared utilities (Hive helper, Master config helper)
│   └── eCommerce/                    # eCommerce network client implementations (API call setups)
├── utils/                            # Shared Helper Utilities
│   ├── api_client.dart               # HTTP wrapper around Dio (injects tokens, handles headers)
│   ├── global_function.dart          # Status bar settings, snacker prompts, coordinate calculations
│   ├── notification_handler.dart     # Firebase FCM foreground and background message listeners
│   └── request_handler.dart          # HTTP status checks and token-expiry/auth intercepts
├── views/                            # Screen Widgets separated by business domain
│   ├── common/                       # Splash, onboarding, auth (login, registration, OTP checks)
│   ├── eCommerce/                    # Catalog, cart, checkout, orders, address, and profile screens
│   ├── food/                         # Restaurant menus and checkout screens
│   ├── grocery/                      # Grocery sections and cart list screens
│   └── pharmacy/                     # Pharmacy medicine detail and prescription upload screens
├── firebase_options.dart             # Auto-generated Firebase configurations (linking Android/iOS projects)
├── main.dart                         # Main entry point (initializes Hive, Firebase, and routes core App)
└── routes.dart                       # Global route configurations and page transition transitions
```

---

## 2. Complete App Flow & User Journey

```
                        ┌────────────────────────┐
                        │      App Launch        │
                        │    (Splash Screen)     │
                        └───────────┬────────────┘
                                    │ Check Auth & settings
                                    ▼
                        ┌────────────────────────┐
                        │    Onboarding / Intro  │
                        │  (First-time launch)   │
                        └───────────┬────────────┘
                                    │
                                    ▼
                        ┌────────────────────────┐
                        │     Login Screen       │
                        │    (Guest bypass)      │
                        └───────────┬────────────┘
                                    │
                         ┌──────────┴──────────┐
                         ▼                     ▼
                  ┌──────────────┐      ┌──────────────┐
                  │ Niche Choice │      │ Direct Login │
                  │  (Dashboard) │      │  To Session  │
                  └──────┬───────┘      └──────┬───────┘
                         │                     │
                         └──────────┬──────────┘
                                    │ Resolve prefixed Core route
                                    ▼
                        ┌────────────────────────┐
                        │  Core Tab Controller   │
                        │ (Home, Shop, Cart, Profile)│
                        └───────────┬────────────┘
                                    │ Checkout Event
                                    ▼
                        ┌────────────────────────┐
                        │   Checkout Screen      │
                        │ (Calculate shipping &) │
                        │ (Select Payment)       │
                        └───────────┬────────────┘
                                    │ Payment Trigger
                                    ▼
                        ┌────────────────────────┐
                        │  In-App Webview Payment│
                        │ (PayPal, Stripe redirect)│
                        └───────────┬────────────┘
                                    │ Callback Verify
                                    ▼
                        ┌────────────────────────┐
                        │  Order Confirm Screen  │
                        │  (Redirect to history) │
                        └────────────────────────┘
```

1.  **Splash (Bootup)**: Reads local storage (Hive settings box). Checks if the app is launched for the first time, loads saved languages/locales, fetches saved tokens/profiles, and redirects accordingly.
2.  **Authentication**: If not logged in, the user is directed to the Login screen. They can proceed as a Guest or login using their phone/email and password, which uses OTP codes for verification if enabled.
3.  **Core Interface (Multi-Niche Dashboard)**:
    Loads the main dashboard. The app changes its branding, layout, and catalog options dynamically depending on the selected business mode (`ecommerce`, `food`, `grocery`, or `pharmacy`). The navigation bar handles routes dynamically:
    - **Home**: Banner slides, category grids, flash sales, search fields, top shops.
    - **Categories**: Complete hierarchical catalogue list.
    - **My Cart**: Local cart list displaying totals.
    - **Profile**: Address list, purchase history, support tickets, language settings.
4.  **Checkout & Calculations**: Calculating checkout totals submits the shipping address ID to `/shipping/calculate` to get the delivery fee and tax totals.
5.  **In-App Webview Checkout**: Online gateway checkouts open in a dynamic Webview (`webview_flutter`). The app monitors redirects to catch payment success (`/payment/success`) or cancellation (`/payment/cancel`) URLs, completing the order flow inside the app.

---

## 3. Screen List

The application's views are grouped under the matching business niche namespaces. Key views include:

### 1. Common Views (`lib/views/common/`)
- **Splash Screen**: Renders company branding while loading configurations.
- **Onboarding Screen**: Introduces core app features to first-time users.
- **Login Screen**: Handles phone/email and password login credentials.
- **SignUp Screen**: Registers new users.
- **ConfirmOTP / RegistrationOtpScreen**: Verifies SMS or email authentication codes.
- **RecoverPassword / CreatePassword**: Manages password reset verification links.

### 2. eCommerce Core Views (`lib/views/eCommerce/`)
- **Core Dashboard**: Custom bottom navigation wrapper mounting core views.
- **Home View**: Main landing page with sliders, category grids, and shop highlights.
- **Categories View**: Hierarchical layout displaying all categories.
- **Products View**: Product grids with search filters and sorting options.
- **ProductDetails View**: Product details page with image sliders, sizing/color variant selectors, and reviews.
- **Shops / ShopView / ShopProductsView**: Dynamic shop portals for multi-vendor navigation.
- **MyCart View**: Renders current cart items with item counts.
- **Checkout View**: Order summary view for selecting addresses, payment methods, and applying coupons.
- **AddUpdateAddress / ManageAddress**: Renders input maps and addresses form panels.
- **MyOrder / OrderDetails**: Purchase history lists with payment retry actions.
- **OrderTrack View**: Map coordinates rendering displaying delivery rider tracks.
- **Notification View**: Lists push alerts received.

---

## 4. API Endpoints Mapping

Requests are mapped to Dio endpoints inside [app_constants.dart](file:///D:/Apolo/Mobile/lib/config/app_constants.dart).

| Constant Name | API Endpoint Path | Description |
| :--- | :--- | :--- |
| `settings` | `/master` | Dynamic theme colors, gateways list, settings. |
| `loginUrl` | `/login` | Authenticates phone and password, returning tokens. |
| `registrationUrl` | `/registration` | Registers new accounts. |
| `sendOTP` | `/send-otp` | Sends password reset OTP keys. |
| `verifyOtp` | `/verify-otp` | Verifies account verification OTP keys. |
| `resetPassword` | `/reset-password` | Confirms password updates. |
| `getDashboardData` | `/home` | Main dashboard catalog details. |
| `getCategories` | `/categories` | Parent category directories. |
| `getProducts` | `/products` | Filtered product search lists. |
| `productFavoriteAddRemoveUrl`| `/favorite-add-or-remove`| Updates wishlist items. |
| `addAddess` | `/address/store` | Adds new addresses to the profile. |
| `cartSummery` | `/cart/checkout` | Calculates cart totals (shipping fee, vat tax, discounts). |
| `placeOrder` | `/place-order` | Places orders and returns cashier options or gateway links. |
| `getOrders` | `/orders` | Lists user purchase history. |
| `unreadMessage` | `/unread-messages` | Checks for unread support chat messages. |

---

## 5. Navigation & Dynamic Routing

Routing uses a dynamic, prefix-based routing table configured in [routes.dart](file:///D:/Apolo/Mobile/lib/routes.dart).

```
                        ┌────────────────────────┐
                        │   RouteSettings Name   │
                        │  (e.g., 'food/home')   │
                        └───────────┬────────────┘
                                    │
                                    ▼
                        ┌────────────────────────┐
                        │   Parse serviceName    │
                        │    (Regex Match)       │
                        └───────────┬────────────┘
                                    │
                                    ▼
                        ┌────────────────────────┐
                        │   getHomeView(service) │
                        │  (Switch-case matching)│
                        └───────────┬────────────┘
                                    │
                    ┌───────────────┼───────────────┐
                    ▼               ▼               ▼
           ┌──────────────┐┌──────────────┐┌──────────────┐
           │ EcommerceHome││   FoodHome   ││  GroceryHome │
           │    Widget    ││    Widget    ││    Widget    │
           └──────────────┘└──────────────┘└──────────────┘
```

1.  **Prefix Resolution**: Routes use a specific niche prefix name before the page name (e.g. `ecommerce/core`, `food/core`, `grocery/core`, `pharmacy/core`).
2.  **Switch Case Matching**: The router extracts the service name and uses switch statements to return the correct widget for that niche (e.g., calling `getHomeView` returns `EcommerceHomeView` or `FoodHomeView`).
3.  **Transition Animations**: Pages are loaded using `PageTransition` from the `page_transition` package, with `PageTransitionType.theme` and a standard 300ms transition time.
4.  **Route Arguments**: Parameters (such as `categoryId` or `shopName`) are passed as lists to route arguments, dynamically unpacked by the generated route helper, and passed to the screen constructor.

---

## 6. State Management (Riverpod)

The app uses **Riverpod** for state management, keeping business logic clean and isolated from the UI layout tree.

- **Controllers (`lib/controllers`)**:
  Extend `StateNotifier<T>` to manage state variables, monitor events, and update the UI:
  ```dart
  class AuthController extends StateNotifier<bool> {
    final Ref ref;
    AuthController(this.ref) : super(false);
    // ...
  }
  ```
- **State Notifier Providers**:
  Expose controllers globally. Screen widgets watch these providers (`ref.watch(authControllerProvider)`) to reactively update view states:
  ```dart
  final authControllerProvider =
      StateNotifierProvider<AuthController, bool>((ref) => AuthController(ref));
  ```
- **Modular Controllers**: State is separated by business niche and common modules (e.g., `cart_controller` for cart operations, `order_controller` for checkout transactions, and `address_controller` for location and shipping coordinates).

---

## 7. Data Flow & Network Client Interceptors

Network requests use **Dio** wrapped inside a custom `ApiClient` configuration that injects headers and handles tokens dynamically.

```
┌────────────────────────────────────────────────────────┐
│                        Dio Client                      │
└───────────────────────────┬────────────────────────────┘
                            │ Add Interceptors
                            ▼
┌────────────────────────────────────────────────────────┐
│                   ApiInterceptors Layer                │
└───────────────────────────┬────────────────────────────┘
                            ├─> Header Config: Accept / Type
                            ├─> Logger Config: PrettyDioLogger
                            └─> Response/Error Handler
                                 │
                     ┌───────────┴───────────┐
                     ▼                       ▼
            ┌─────────────────┐     ┌─────────────────┐
            │   HTTP Error    │     │   HTTP 401      │
            │ (Timeout / Bad) │     │ (Unauthorized)  │
            └────────┬────────┘     └────────┬────────┘
                     │                       │
                     ▼                       ▼
            ┌─────────────────┐     ┌─────────────────┐
            │ Display Custom  │     │ - Clear Token   │
            │ Snackbar Toast  │     │ - Redirect to   │
            │  (Alert User)   │     │   Login Screen  │
            └─────────────────┘     └─────────────────┘
```

1.  **Request Setup**: The custom `ApiClient` updates authorization headers automatically when tokens are changed:
    ```dart
    defaultHeaders[HttpHeaders.authorizationHeader] = 'Bearer $token';
    ```
2.  **Timeouts & Formats**: Requests have a default 30-second timeout. Headers are pre-configured to handle JSON formatting:
    `Accept: application/json` and `Content-Type: application/json`.
3.  **Logger Interceptor**: Integrates `PrettyDioLogger` to print request/response logs in the console during development.
4.  **Response & Error Handling (`ApiInterceptors`)**:
    - **HTTP Errors (400, 403, 404, 422, 500)**: The interceptor automatically extracts the backend's error message and displays it as a toast alert (`GlobalFunction.showCustomSnackbar(message, isSuccess: false)`).
    - **HTTP 401 Unauthorized**: If a request returns a 401 error, the interceptor clears the saved token from the Hive box (`authBox.delete(authToken)`) and redirects the user back to the login screen (`Routes.login`).
5.  **Connectivity Checks**: Captures network timeouts and connection losses, prompting the user to check their internet connection.
