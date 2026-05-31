# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Uji-Shi Matcha Cafe** — A full-stack e-commerce platform with:
- `backend/` — Laravel 13 REST API
- `frontend/` — Nuxt 4 SSR application (shop + admin panel)
- Database: MySQL (`uji_shi`)

## Common Commands

### Backend (Laravel 13)
```bash
# Run development server
cd backend && php artisan serve

# Run migrations
php artisan migrate

# Run seeders (creates admin@ujishi.ph / password)
php artisan db:seed

# Fresh migration + seed
php artisan migrate:fresh --seed

# Run tests
php artisan test

# Single test file
php artisan test --filter=ProductControllerTest

# Link storage (for uploaded media)
php artisan storage:link

# Clear caches
php artisan config:cache && php artisan route:cache

# Tail logs
php artisan pail
```

### Frontend (Nuxt 4)
```bash
# Development server (http://localhost:3000)
cd frontend && npm run dev

# Type check
cd frontend && npx vue-tsc --noEmit

# Build for production
cd frontend && npm run build

# Preview production build
cd frontend && npm run preview

# Nuxt prepare (regenerate .nuxt/ types after config changes)
cd frontend && npm run postinstall
```

## Architecture

### Backend Structure (`backend/app/`)

```
Http/
  Controllers/API/
    Auth/AuthController.php          — register, login, logout, user
    Shop/ProductController.php       — public product listing + detail
    Shop/CartController.php          — hybrid guest+auth cart (X-Session-Id header)
    Shop/OrderController.php         — checkout, order history, PayMongo webhook
    Shop/HomepageController.php      — assembled homepage sections
    Shop/ReviewController.php        — submit product reviews
    Shop/WishlistController.php      — toggle wishlist items
    Shop/NewsletterController.php    — subscribe
    Admin/AdminProductController.php — CRUD with content blocks + gallery sync
    Admin/AdminCategoryController.php
    Admin/AdminOrderController.php   — list, detail, status update
    Admin/AdminHomepageController.php — sections reorder, carousel, banners
    Admin/AdminMediaController.php   — file upload (Intervention Image)
    Admin/AdminSettingsController.php — key/value store
    Admin/AdminDashboardController.php — stats + top products
  Middleware/AdminMiddleware.php      — checks user.isAdmin()
Services/
  CartService.php                    — addItem, updateItem, mergeGuestCart
  OrderService.php                   — createFromCart (DB transaction), updateStatus
  PayMongoService.php                — createPaymentIntent, handleWebhook
  HomepageService.php                — assemble() loads sections with data per type
  DashboardService.php               — 30-day stats, dailyRevenue, topProducts
  MediaService.php                   — upload (resize to 1920px), delete
Models/                              — see schema section below
```

### API Route Groups (`backend/routes/api.php`)
- **Public** (no auth): `GET /homepage`, `GET /products`, `GET /products/{slug}`, `GET /categories`, `GET /pages/{slug}`, `POST /newsletter/subscribe`, `POST /payment/webhook`
- **Auth** (Sanctum token): `/auth/register`, `/auth/login`, `/auth/logout`, `/auth/user`
- **Customer** (auth required): `/cart`, `/wishlist`, `/orders`, `/products/{id}/reviews`
- **Admin** (auth + AdminMiddleware): All `/admin/*` routes

### Authentication
- Sanctum **token-based** (not cookie). Login returns `{ token, user }`.
- Frontend stores token in a cookie named `auth_token` via Pinia `authStore`.
- Each API request adds `Authorization: Bearer {token}` header.
- The `plugins/api.ts` plugin wraps `$fetch.create()` with the auth header and 401 redirect.

### Database Schema (key relationships)
```
users → user_addresses (hasMany)
     → orders (hasMany)
     → reviews (hasMany)
     → wishlists (hasMany)
     → cartItems (hasMany)

categories → products (hasMany, nullable FK)
           → children (self-referential hasMany via parent_id)

products → product_images (hasMany, ordered by sort_order)
         → product_content_blocks (hasMany, ordered by sort_order)
         → reviews → approved_reviews (scope)
         → wishlists, order_items

orders → order_items (hasMany)
       → order_shipping_addresses (hasOne)
       → coupons (belongsTo, nullable)

homepage_sections — one row per section type (type is unique key for seeding)
carousel_slides   — ordered by sort_order, toggled by is_active
banners           — used in promotional_banner section
media_files       — uploaded images; url = Storage::disk('public')->url(path)
settings          — key/value store, cached 1hr per key in Setting::get()
```

### Frontend Structure (`frontend/`)

**Nuxt 4 conventions:**
- File-based routing in `pages/`
- Auto-imported components from `components/` (prefix = folder name, e.g. `components/shop/ProductCard.vue` → `<ShopProductCard />`)
- Auto-imported composables from `composables/`
- Stores in `stores/` using Pinia (use `defineStore`)
- `layouts/default.vue` (shop), `layouts/admin.vue` (client-only via `routeRules`), `layouts/auth.vue`

**Key composables:**
- `useApi()` — returns `$api` (typed $fetch with base URL + auth header)
- `useAuthFetch<T>(url, opts)` — SSR-safe useFetch wrapper with auth header

**Pinia stores:**
- `stores/auth.ts` — `user`, `token` (cookie), `isAuthenticated`, `isAdmin`, `login()`, `logout()`, `fetchUser()`
- `stores/cart.ts` — `items`, `count`, `subtotal`, `addItem()`, `updateItem()`, `removeItem()`, `fetchCart()`

**Middleware:**
- `middleware/auth.ts` — redirects to `/auth/login` if not authenticated
- `middleware/admin.ts` — redirects if `!authStore.isAdmin`

**SSR strategy:**
- Shop pages (`pages/index.vue`, `pages/products/`, `pages/products/[slug].vue`): full SSR with `useFetch`/`useAsyncData`
- Admin pages (`pages/admin/**`): client-only SPA via `routeRules: { '/admin/**': { ssr: false } }`
- SEO: `useSeoMeta()` on every shop page

### Homepage Builder
- `homepage_sections` table has one row per section type (8 section types)
- `GET /api/homepage` calls `HomepageService::assemble()` which loads data for each active section
- `HomepageSectionRenderer.vue` switches on `section.type` to render the correct component
- Admin at `/admin/homepage` — drag to reorder (HTML5 drag API), toggle, edit settings JSON
- Sections' `settings` field stores arbitrary JSON (titles, subtitles, images, CTAs for about/newsletter)
- `PUT /api/admin/homepage/sections/reorder` — body: `{ sections: [{id, sort_order}] }`

### Product Content Blocks
- `product_content_blocks` stores blocks as `{type, content: JSON, sort_order}`
- Content types: `rich_text` (HTML string), `ingredients` (item array), `faq` (q/a array), `feature_cards`, `promotional_banner`
- Admin product form (`pages/admin/products/[id].vue`) has inline block editors per type
- Frontend `pages/products/[slug].vue` renders blocks inline with `v-if` per type

### Media Uploads
- `POST /api/admin/media` accepts multipart `file` field
- `MediaService::upload()` uses Intervention Image to resize to 1920px max width, saves as JPEG 85%
- Files stored at `backend/storage/app/public/media/{uuid}.jpg`
- URL: `http://localhost:8000/storage/media/{uuid}.jpg`
- Run `php artisan storage:link` after first setup

### PayMongo Integration
- Config: `PAYMONGO_PUBLIC_KEY` and `PAYMONGO_SECRET_KEY` in `.env`
- Flow: `POST /orders` creates order → `PayMongoService::createPaymentIntent()` → frontend redirects to PayMongo URL → webhook `POST /payment/webhook` marks order as paid
- Webhook does NOT verify signature (add in production with `PayMongo-Signature` header)

### Design System (TailwindCSS)
Custom colors in `tailwind.config.ts`:
- `matcha.500` (#5a9e3c) — primary brand green
- `cream.DEFAULT` (#faf8f2) — page background
- `charcoal.DEFAULT` (#2d2d2d) — text
Fonts: `Noto Serif JP` (headings, class `font-serif`) + `Inter` (body, class `font-sans`)
Utility classes in `assets/css/main.css`: `.btn-primary`, `.btn-outline`, `.btn-ghost`, `.card`, `.input-field`, `.badge`, `.admin-nav-item`

## Environment Variables

### Backend (`backend/.env`)
```
DB_CONNECTION=mysql
DB_DATABASE=uji_shi
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

SANCTUM_STATEFUL_DOMAINS=localhost:3000
FILESYSTEM_DISK=public

PAYMONGO_PUBLIC_KEY=pk_test_...
PAYMONGO_SECRET_KEY=sk_test_...
```

### Frontend (`frontend/.env`)
```
NUXT_PUBLIC_API_BASE=http://localhost:8000/api
NUXT_PUBLIC_PAYMONGO_KEY=pk_test_...
```

## Seeded Test Users
- Admin: `admin@ujishi.ph` / `password`
- Customer: `customer@ujishi.ph` / `password`

## Forge Deployment
- Backend: Nginx site pointing to `backend/public`, PHP 8.3
- Frontend: Node.js site, start command: `node .output/server/index.mjs`
- Deploy script: `composer install --no-dev -o && php artisan migrate --force && php artisan config:cache && php artisan route:cache`
- Frontend deploy: `npm ci && npm run build`
