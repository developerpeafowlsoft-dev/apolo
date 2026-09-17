# Web Frontend Architecture & Client-Side Design: Ready eCommerce (Apolo)

A comprehensive analysis of the Vue.js 3 frontend storefront, detailing its routing structure, component hierarchy, Pinia state stores, API communications, validation rules, and customer journeys.

---

## 1. Web Folder Structure

The client-facing storefront is a Single Page Application (SPA) built using **Vue.js 3**, styled with **TailwindCSS**, managed using **Pinia** state stores, and compiled using **Vite**.

### Detailed Frontend Source Directory (`Web/resources/js`)
```
resources/js/
├── errors/                           # Error template views
│   └── 404.vue                       # 404 Not Found Page
├── icons/                            # SVG Custom Icons (Bag, Facebook, Apple, Google)
├── layouts/                          # Master Page Layouts
│   ├── auth.vue                      # Customer dashboard dashboard layout (with sidebar menu)
│   ├── blank.vue                     # Full-width blank layout (used for 404 pages)
│   ├── blog.vue                      # Header/layout for news and blogging views
│   └── default.vue                   # Primary layout for customer shopping pages
├── pages/                            # Full-scale routed views
│   ├── AboutUs.vue
│   ├── AddNewAddress.vue
│   ├── BestDeal.vue
│   ├── Blog.vue
│   ├── BlogDetails.vue
│   ├── BuyNow.vue                    # Custom direct-checkout page
│   ├── Category.vue
│   ├── CategoryProduct.vue           # Products listed under a specific category/slug
│   ├── ChangePassword.vue
│   ├── Checkout.vue                  # Primary shopping checkout page
│   ├── ContactUs.vue
│   ├── Dashboard.vue                 # Customer account landing overview page
│   ├── EditAddress.vue
│   ├── FlashSale.vue
│   ├── Home.vue                      # Main Storefront landing home page
│   ├── ManageAddress.vue             # Addresses list page
│   ├── Messages.vue                  # Chat portal page (Customer support chat box)
│   ├── MostPopular.vue
│   ├── MyProfile.vue                 # Profile editing form page
│   ├── OrderDetails.vue              # Order parameters and payment retry screen
│   ├── OrderHistory.vue              # Tabled history of customer purchases
│   ├── PolicyPages.vue               # Dynamic terms/policy pages
│   ├── PrivacyPolicy.vue
│   ├── ProductDetails.vue            # Product page with variant selectors & reviews
│   ├── Products.vue                  # Search and listing page for products
│   ├── Shop.vue                      # List of shops (for multi-vendor settings)
│   ├── ShopDetails.vue               # Shop catalog and info
│   ├── Support.vue
│   ├── SupportTicket.vue             # Tickets list page
│   ├── SupportTicketDetails.vue      # Chat logs in support tickets
│   ├── TermsAndConditions.vue
│   └── Wishlist.vue                  # Favorite products list
├── router/                           # Client-side SPA Router
│   └── index.js                      # Route path registrations & title hooks
├── stores/                           # Pinia State Management Stores
│   ├── AuthStore.js                  # Persisted customer registration, login, and address state
│   ├── BasketStore.js                # Persisted cart, items count, checkout details, and coupons
│   ├── ChatStore.js                  # Live chat message channels
│   └── MasterStore.js                # Persisted global configuration settings (currencies, theme colors)
├── App.vue                           # Master Component (sets loops for last-seen updates & Pusher channels)
├── app.js                            # Frontend Bootstrapper (registers plugins, mounts App)
├── bootstrap.js                      # Axios config (configures base URL '/api' and lang headers)
└── localization.js                   # Handles Vue i18n configurations
```

### ⚠️ Crucial Architectural Distinction: Blade vs Vue
The web application is split into two separate rendering architectures:
- **Laravel Blade (Server-Side Rendered)**: Used for the central **Admin Dashboard** and **Seller/Shop POS Dashboard** (`views/admin/` and `views/shop/`). This optimizes data security and handles server-intensive operations (POS invoicing and bookkeeping).
- **Vue.js 3 SPA (Client-Side Rendered)**: Used for the **Customer Storefront** and the **Customer Account Panel** (`resources/js/`). This provides a smooth, fast, and responsive user experience.

---

## 2. Client-Side Page Flow

The client routes define two distinct user paths: public shopping areas (using the `default` or `blog` layouts) and authenticated customer dashboards (using the `auth` layout).

```
[Default Layout / Guest Flow]
Landing Page (/) ─┬─> Product Details (/products/:id) ──> Cart Sidebar ──> Checkout (/checkout)
                  ├─> Categories (/categories) ──> Products list
                  └─> Shop Search (/shops) ──> Shop detail page (/shops/:id)

[Auth Layout / Registered User Flow]
Dashboard (/dashboard) ─┬─> Order History (/order-history) ──> Order Details
                        ├─> Support Tickets (/support-tickets) ──> Ticket Detail chat
                        ├─> Address Book (/manage-address) ──> New / Edit Address
                        ├─> Profile Update (/profile) & Change Password
                        └─> Chat Messenger (/massages)
```

---

## 3. Component Hierarchy

The components are nested inside layouts that mount dynamically based on the active route metadata:

```
App.vue (Entry Component)
 └── Dynamic Layout Component (:is="$route.meta.layout")
      │
      ├── default.vue (Storefront Layout)
      │    ├── NavbarTop (Language dropdown, store contact)
      │    ├── NavbarMiddle (Logo, search bar, login/registration trigger, wishlist tally)
      │    │    └── AuthUserDropdown (Dropdown menu for profile settings and logout)
      │    ├── NavbarBottom (Category list, subcategory menus, hot deals, and blogs link)
      │    ├── RouterView (Mounts Home, Products, ShopDetails, Checkout, etc.)
      │    │    └── E.g., ProductDetails.vue
      │    │         ├── ProductDetailsRightSide (Size selectors, color options, quantity buttons)
      │    │         ├── AddCartPopupDialog (Confirms items added to cart)
      │    │         ├── RecentlyViews (Displays recently viewed products)
      │    │         └── Review & ReviewRatings (Customer ratings & feedback section)
      │    ├── Basket (Sliding shopping cart sidebar panel)
      │    │    ├── BasketProductItem (Row items showing price, quantity, size/color variant details)
      │    │    └── RemoveCartPopupDialog (Confirms removal of items)
      │    ├── FooterTop (Links, app download links, payment icons, social icons)
      │    └── FooterBottom (Copyright labels)
      │
      ├── auth.vue (Customer Account Dashboard Layout)
      │    ├── NavbarTop / NavbarMiddle / NavbarBottom (Reused shopping navbar)
      │    ├── DashboardSidebar (Left-hand menu navigation: Profile, Wishlist, Orders, Tickets, Messages)
      │    ├── RouterView (Mounts Dashboard, MyProfile, OrderHistory, SupportTicket)
      │    │    └── E.g., Dashboard.vue
      │    │         ├── DashboardBasketCard (Brief summary of items in cart)
      │    │         ├── DashboardDefaultShippingAddress (Default address details)
      │    │         ├── DashboardMyCart (Quick overview of cart items)
      │    │         └── DashboardRecentlyView (Recently viewed items list)
      │    └── Footer (Bottom footer)
      │
      └── blog.vue (Blog and News Layout)
           ├── BlogHeader (Main header design for articles)
           └── RouterView (Mounts Blog list or BlogDetails view)
                └── BlogCard & BlogCardHorizontal (Blog card designs)
```

---

## 4. API Integration (Axios)

Axios communication is centralized in `Web/resources/js/bootstrap.js` with key defaults:
- **Base URL**: `window.axios.defaults.baseURL = '/api';` (routing all requests directly to the API routes).
- **Accept-Language**: Automatically sets the header: `localStorage.getItem('locale') ?? 'en'`, ensuring error messages and product descriptions are returned in the customer's selected language.

### Mapping API Endpoints to Stores and Pages
| Endpoint | Method | Trigger Component / Store | Description |
| :--- | :--- | :--- | :--- |
| `/master` | `GET` | `MasterStore.js` | Retrieves app name, currencies, gateways list, theme colors, and settings. |
| `/login` | `POST` | `LoginModal.vue` | Submits credentials and returns user tokens. |
| `/registration` | `POST` | `RegistrationDialogModal.vue` | Registers a new user. |
| `/logout` | `GET` | `AuthStore.js` | Revokes the current Sanctum token. |
| `/addresses` | `GET` | `AuthStore.js` | Retrieves the customer's saved address book. |
| `/favorite-products` | `GET` | `AuthStore.js` | Lists liked products for the wishlist. |
| `/cart/checkout` | `POST` | `BasketStore.js` | Calculates order totals (tax, delivery fees, discounts) during checkout. |
| `/shipping/calculate` | `POST` | `Checkout.vue` | Calculates dynamic shipping charges based on the selected address and vendors. |
| `/place-order` | `POST` | `CheckoutOrderSummary.vue` | Places the order and returns a cash confirmation or payment gateway URL. |
| `/update-last-seen` | `POST` | `App.vue` | Interval script (10 minutes) updating the user's online status in the database. |
| `/unread-messages` | `GET` | `App.vue` | Retrieves the unread chat message count. |

---

## 5. Store & State Management

The application uses **Pinia** for state management, configured with **`pinia-plugin-persistedstate`** to save user sessions in `localStorage` across page refreshes.

```
                  ┌──────────────────────────────────────────────┐
                  │              Pinia Master Stores             │
                  └───────┬──────────────┬──────────────┬────────┘
                          │              │              │
                          ▼              ▼              ▼
                  ┌──────────────┐┌──────────────┐┌──────────────┐
                  │  AuthStore   ││ BasketStore  ││ MasterStore  │
                  └──────┬───────┘└──────┬───────┘└──────┬───────┘
                         │               │               │
                         ▼               ▼               ▼
                  ┌──────────────────────────────────────────────┐
                  │       Local Storage Persistence Layer        │
                  │   (Token, Cart Items, Settings preserved)   │
                  └──────────────────────────────────────────────┘
```

- **`AuthStore.js`**:
  - **State**: `user` (profile resource), `token` (`Bearer <token>`), `addresses`, `favoriteProducts`, and modals state (`loginModal`, `registerModal`, `showAddressModal`).
  - **Actions**: `setToken()`, `setUser()`, `fetchAddresses()`, `fetchFavoriteProducts()`, and `logout()`. On logout, it clears user details, addresses, wishlist tallies, and tokens.
- **`BasketStore.js`**:
  - **State**: `cart` (items array), `selectedShopIds` (selected vendors), `checkoutProducts`, `total_amount`, `delivery_charge`, `coupon_discount`, `payable_amount`, `order_tax_amount`, and `all_vat_taxes`.
  - **Actions**: `addToCart()`, `fetchCart()`, `incrementQuantity()`, `decrementQuantity()`, and `fetchCheckoutProducts()` (submitting the selected vendor shop IDs to `/cart/checkout`).
- **`MasterStore.js`**:
  - **State**: Settings configurations (`locale`, `langDirection`, `currency` symbols/positions, `appName`, `socialAuths` config, `themeColors`).
  - **Actions**: `fetchData()` (syncs configurations from `/master`), and `showCurrency(amount)` (converts prices dynamically based on default currencies).

---

## 6. Authentication Flow

```
                      ┌───────────────────────────┐
                      │  Guest clicks Protected   │
                      │  Action (e.g. Checkout)   │
                      └─────────────┬─────────────┘
                                    │
                                    ▼
                      ┌───────────────────────────┐
                      │    Trigger LoginModal     │
                      │   (AuthStore.loginModal)  │
                      └─────────────┬─────────────┘
                                    │
                         ┌──────────┴──────────┐
                         ▼                     ▼
                  ┌──────────────┐      ┌──────────────┐
                  │ Google / FB  │      │ Phone / Email│
                  │  Social SDK  │      │ Login Form   │
                  └──────┬───────┘      └──────┬───────┘
                         │                     │
                         └──────────┬──────────┘
                                    │ Submit to API
                                    ▼
                      ┌───────────────────────────┐
                      │ AuthController returning  │
                      │    Bearer Token           │
                      └─────────────┬─────────────┘
                                    │
                                    ▼
                      ┌───────────────────────────┐
                      │  Pinia saves token & user │
                      │  in Local Storage         │
                      └─────────────┬─────────────┘
                                    │ Trigger refresh
                                    ▼
                      ┌───────────────────────────┐
                      │ Fetch Cart & Wishlist     │
                      │ (Auto-closes LoginModal)  │
                      └───────────────────────────┘
```

1. **Guest Access**: Guest users browse products, shops, and read blogs. Attempting to add an item to the wishlist or checkout triggers the authentication check.
2. **Dynamic Overlay**: Displays the login modal (`AuthStore.loginModal = true`) as a overlay window instead of redirecting the user, maintaining their active browsing state.
3. **Federated OAuth**:
   - Google Sign-In initializes via the JS SDK Client: `google.accounts.oauth2.initCodeClient`.
   - The user authorizes their account, returning a code that is sent to the backend endpoint `/auth/google/token` to be exchanged for a token.
4. **Email/Phone Login**: Submits parameters to the backend `/login` route. On success, `AuthStore.setToken` saves the Bearer token, the modal closes, and the store updates the user's cart (`basketStore.fetchCart()`) and wishlist (`AuthStore.fetchFavoriteProducts()`).

---

## 7. Forms & Validation

The application relies on server-side validations (HTTP 422 Unprocessable Entity) and maps error fields dynamically back to the UI.

- **Reactive State Mapping**: Input elements use `v-model` binding on a reactive `ref` object:
  ```javascript
  const formData = ref({
      current_password: '',
      password: '',
      password_confirmation: '',
  });
  ```
- **Error Capture**: Form submissions capture errors in a reactive variable:
  ```javascript
  const errors = ref({});
  // ...
  axios.post('/change-password', formData.value)
    .catch((error) => {
        errors.value = error.response.data.errors;
    });
  ```
- **Dynamic CSS Classes**: Inputs display red borders dynamically if validation errors exist:
  ```html
  <input :class="(errors && errors?.current_password) ? 'border-red-500' : 'border-slate-200'" />
  ```
- **Validation Messages**: The first error message for each field is displayed directly below the input:
  ```html
  <span v-if="errors && errors?.current_password" class="text-red-500 text-sm">
      {{ errors?.current_password[0] }}
  </span>
  ```

---

## 8. Customer Purchase Journey & Payment Popup Tracking

The frontend implements a centered popup window mechanism to handle online payment redirects without interrupting the user's active session state in the SPA.

```
                             ┌─────────────────────────┐
                             │ Customer clicks checkout│
                             │ (Selects Online Payment)│
                             └────────────┬────────────┘
                                          │
                                          ▼
                             ┌─────────────────────────┐
                             │  Submit '/place-order'  │
                             │  (Returns payment URL)  │
                             └────────────┬────────────┘
                                          │
                                          ▼
                             ┌─────────────────────────┐
                             │ Open Centered Popup     │
                             │ (window.open paymentURL)│
                             └────────────┬────────────┘
                                          │
                                          ▼
                             ┌─────────────────────────┐
                             │  Start Interval Timer   │
                             │  (trackURLChanges 1s)   │
                             └────────────┬────────────┘
                                          │
                     ┌────────────────────┴────────────────────┐
                     │                                         │
                     ▼                                         ▼
         ┌───────────────────────┐                 ┌───────────────────────┐
         │ URL: /payment/success │                 │ URL: /payment/cancel  │
         └───────────┬───────────┘                 └───────────┬───────────┘
                     │                                         │
                     ▼                                         ▼
         ┌───────────────────────┐                 ┌───────────────────────┐
         │ - Close Payment Popup │                 │ - Close Payment Popup │
         │ - Clear Interval Timer│                 │ - Clear Interval Timer│
         │ - Show Success Modal  │                 │ - Show Cancel Modal   │
         │ - Redirect to details │                 │ - Show Cancel Toast   │
         └───────────────────────┘                 └───────────────────────┘
```

1. **Calculate Shipping**: Changing the delivery address or selecting/deselecting shops in `Checkout.vue` calls `/shipping/calculate`. This updates shipping charges and delivery fees dynamically across multiple vendors before the order is placed.
2. **Submit Order**: Clicking the **Place Order** button in `CheckoutOrderSummary.vue` calls the `/place-order` endpoint, passing selected shop IDs, the active address ID, the payment method, and coupon codes.
3. **Popup Window Hook**:
   If the user selected an online payment gateway, the API returns a redirect link (`order_payment_url`). The client opens this URL in a centered popup window:
   ```javascript
   let options = "popup,resizable,height=700,width=700,top=" + top + ",left=" + left;
   let win = window.open(url, null, options);
   ```
4. **URL Tracking Interval**:
   An interval runs every second to monitor the popup window's current URL path (`win.location.pathname`):
   ```javascript
   var intervalID = setInterval(trackURLChanges, 1000);
   ```
   - **Payment Success**: If the path matches `/payment/success`, the interval timer is cleared, the popup closes, and the client displays the order confirmation modal (`basketStore.showOrderConfirmModal = true`).
   - **Payment Cancelled**: If the path matches `/payment/cancel` (or if the user closes the popup window manually), the interval timer is cleared, the popup closes, the client displays a "Payment Canceled" toast notification, and redirects the user back to the homepage.
5. **Session Recovery**: Because payment processing is handled in a separate popup window, the main application state, cart items, and navigation session are preserved without full-page reloads.
