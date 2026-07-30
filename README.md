# 📱 My Compass Phone

[![Version](https://img.shields.io/badge/version-26.7.29.1425-62c9ff.svg?style=for-the-badge)](https://github.com/HalloftheGods/xophz-compass-phone)
[![License](https://img.shields.io/badge/license-GPL--2.0+-blue.svg?style=for-the-badge)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b.svg?style=for-the-badge&logo=wordpress&logoColor=white)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4.svg?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Category](https://img.shields.io/badge/COMPASS-Command%20Deck-00f2fe.svg?style=for-the-badge)](https://github.com/HalloftheGods)

> **Standalone WordPress backend router, passwordless authentication server, and single-page web app container for the My Compass Phone mobile experience in the Xophz COMPASS ecosystem.**

---

## ⚡ Overview

**My Compass Phone** (`xophz-compass-phone`) serves as the dedicated WordPress backend plugin and SPA container for the mobile presentation, command center, and ecosystem phone client of Xophz COMPASS. 

It provides seamless URL rewrite routing (hosting the client application at `/my-compass-phone` or a customizable slug), passwordless Magic Link & 6-digit OTP verification, secure REST API authentication endpoints, and Hot Module Replacement (HMR) proxying for local Vite development.

---

## ✨ Key Features

### 📱 Standalone Single-Page Application Host
* **Custom Route Interception**: Automatically routes web traffic from `/my-compass-phone` (or custom configured slug) to the client application without requiring standard WordPress theme overhead.
* **Catch-All Frontend Routing**: Implements rewrite rules to support deep linking and client-side routing (Vue Router / History mode).
* **Automatic Asset Rewriting**: Dynamically rewrites production bundle paths (`/assets/*`, `/vite.svg`) to serve directly from the plugin's `public/dist/` directory.

### 🔑 Passwordless Magic Link & OTP Authentication
* **6-Digit Verification Codes**: Generates 6-digit numeric OTPs stored in WordPress transients with 15-minute expiration windows.
* **One-Click Magic Links**: Emails direct sign-in URLs containing token payloads to rapidly log users into the mobile app experience.
* **Auto-Account Provisioning**: Automatically registers new user accounts upon first successful Magic Link verification for frictionless onboarding.
* **Password Authentication Fallback**: Supports standard WordPress username/email and password authentication.

### ⚡ Vite Development Proxy & HMR Bridge
* **Dev Mode Detection**: Automatically detects `WP_ENV === 'development'` or `WP_DEBUG`.
* **Vite Server Integration**: Proxies assets and injects Vite HMR client scripts from the Vite development server running on port `8086`.
* **State & Nonce Injection**: Automatically injects `window.wpApiSettings` (`root`, `nonce`, `pluginUrl`, `version`, `userId`) into both development and production HTML outputs.

### ⚙️ Customizable Deployment & Administrative Control
* **WP Admin Settings Page**: Accessible via `Settings > My Compass Phone` in WordPress Admin.
* **Configurable Slug**: Easily modify the endpoint slug (default: `my-compass-phone`) with automated rewrite rule flushing upon saving.

---

## 🚀 REST API Endpoints

All authentication endpoints are registered under the `compass-phone/v1` namespace.

### Authentication Endpoints (`compass-phone/v1`)

| Method | Endpoint | Description | Payload / Query Params |
| :--- | :--- | :--- | :--- |
| `POST` | `/wp-json/compass-phone/v1/check-email` | Check if an account exists and retrieve avatar & display name. | `{ "email": "user@example.com" }` |
| `POST` | `/wp-json/compass-phone/v1/login` | Authenticate using email/username and password. Sets auth cookies and returns fresh REST nonce. | `{ "email": "user@example.com", "password": "..." }` |
| `POST` | `/wp-json/compass-phone/v1/send-magic-link` | Dispatch a 6-digit OTP code and Magic Link to the provided email address. | `{ "email": "user@example.com" }` |
| `POST` | `/wp-json/compass-phone/v1/verify-magic-link` | Verify a 6-digit OTP code or Magic Link token. Auto-creates account if new user. | `{ "token": "123456" }` |
| `GET`  | `/wp-json/compass-phone/v1/me` | Fetch active user session details, role list, display name, and updated REST nonce. | Header: `X-WP-Nonce` or Cookie |
| `POST` | `/wp-json/compass-phone/v1/logout` | Terminate user session, clear auth cookies, and return guest REST nonce. | — |

---

## 🏗️ Architecture & Dev Workflow

The plugin functions as the backend bridge for the Vue 3 frontend application located in `apps/my-compass-phone`.

```
Xophz-COMPASS/
├── apps/
│   └── my-compass-phone/          # Vue 3 + Vite Frontend Application
│       ├── src/                    # UI Components, Composables & Views
│       └── vite.config.ts          # Configured for dev server port 8086
└── wp-content/plugins/
    └── xophz-compass-phone/       # WordPress Backend Plugin
        ├── includes/
        │   └── class-xophz-compass-phone-auth-rest.php  # REST Controller
        ├── public/
        │   └── dist/              # Production build target (`vite build`)
        └── xophz-compass-phone.php # Main plugin & rewrite router
```

### Development Mode
1. Start the Vite development server in `apps/my-compass-phone` (runs on port `8086`).
2. Navigate to `http://localhost/my-compass-phone`.
3. The plugin detects development mode, fetches the HTML from `http://compass:8086/`, injects `window.wpApiSettings`, and enables HMR.

### Production Build
1. Build the frontend app:
   ```bash
   pnpm --filter my-compass-phone build
   ```
2. Assets compile directly to `wp-content/plugins/xophz-compass-phone/public/dist/`.
3. The plugin serves `public/dist/index.html` with injected session settings for non-dev environments.

---

## 🛠️ Installation & Setup

1. Clone or copy `xophz-compass-phone` into your WordPress plugins directory:
   ```bash
   wp-content/plugins/xophz-compass-phone
   ```
2. Ensure core **COMPASS** plugin (`xophz-compass`) is active.
3. Activate **My Compass Phone** in WordPress Admin (`Plugins > Installed Plugins`).
4. Configure the deployment slug under `Settings > My Compass Phone` (defaults to `/my-compass-phone`).

---

## 🧪 Developer Hooks & Integration

The plugin hooks into WordPress core actions and provides standard extensibility points:

```php
// Listen for user login via Phone Auth REST controller
add_action( 'wp_login', function( $user_login, $user ) {
    // Custom action when user authenticates through My Compass Phone
}, 10, 2 );

// Access global REST API settings injected into client window
// window.wpApiSettings = {
//   root: "http://example.com/wp-json/",
//   nonce: "a1b2c3d4e5",
//   pluginUrl: "http://example.com/wp-content/plugins/xophz-compass-phone/",
//   version: "26.7.29.1425",
//   userId: 1
// };
```

---

## 📄 License & Attribution

Developed with ❤️ by **[Hall of the Gods, Inc.](https://www.hallofthegods.com/)**  
Licensed under the [GNU General Public License v2.0 or later](http://www.gnu.org/licenses/gpl-2.0.txt).