# Solve CMS

**Your Content. Your Website. Your Rules.**

Solve CMS is a modern, SEO-first Content Management System built as a single Laravel
application. It combines three capabilities that usually live in separate products:

1. **Content management** — posts, pages, categories, tags, comments and a media library.
2. **Website appearance management** — a theme engine and customizer with live preview (Phase 5).
3. **A technical SEO platform** — per-entry metadata, sitemaps, redirects and schema (Phase 3).

The administration area lives at `/admin`. The public website is served from `/` and is
powered entirely by database content and the site settings.

---

## Technology Stack

| Layer                | Technology                                                              |
| -------------------- | ----------------------------------------------------------------------- |
| Runtime              | PHP 8.3                                                                 |
| Framework            | Laravel 13                                                              |
| Database             | SQLite (development), MySQL / MariaDB (production)                      |
| ORM                  | Laravel Eloquent                                                        |
| Auth                 | Laravel Fortify (login, 2FA, passkeys, email verification)              |
| Frontend             | Vue 3 (Composition API, **JavaScript only**)                            |
| Build                | Vite via `vite-plus` (`vp`)                                             |
| Styling              | Tailwind CSS v4                                                         |
| Routing (client)     | Inertia.js v3                                                           |
| Route helpers        | Laravel Wayfinder (generated to JavaScript at build time)               |
| Icons                | `@lucide/vue`                                                           |
| Primitive components | `reka-ui` (shadcn-vue style components in `resources/js/components/ui`) |

> **JavaScript only.** This project contains no TypeScript source. Wayfinder generates
> `.ts` files, and `scripts/wayfinder-js.mjs` converts them to `.js` during every build.

### Not yet installed (later phases)

- **Laravel Reverb + Echo** — real-time live chat and notifications (Phase 6).
- **Redis** — optional cache/queue backend; the application runs fine without it.

Those phases are not started. Real-time functionality is **not** wired up yet and is not
simulated with polling or dummy data.

---

## Requirements

- PHP **8.3+** with the `gd` extension (used for image thumbnails)
- Composer 2
- Node.js 20+ and npm
- SQLite (default) or MySQL 8 / MariaDB 10.6+

---

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Create the environment file and application key
cp .env.example .env
php artisan key:generate

# 3. Configure the database (see below), then migrate and seed
php artisan migrate --seed

# 4. Install frontend dependencies and build assets
npm install
npm run build
```

The full setup can also be run in one step:

```bash
composer setup
```

### Default accounts

`php artisan migrate --seed` creates three development accounts. **Change or delete these
before exposing the application to the internet.**

| Role          | Email                  | Password   |
| ------------- | ---------------------- | ---------- |
| Super Admin   | `admin@solvecms.test`  | `password` |
| Editor        | `editor@solvecms.test` | `password` |
| Support Agent | `agent@solvecms.test`  | `password` |

---

## Environment Configuration

The most relevant variables in `.env`:

```dotenv
APP_NAME="Solve CMS"
APP_URL=http://localhost:8000

# Development default — no database server required.
DB_CONNECTION=sqlite

# Session, cache and queue all work on the database driver out of the box.
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
```

### Database setup

**SQLite (default, zero configuration):**

```dotenv
DB_CONNECTION=sqlite
```

The database file `database/database.sqlite` is created automatically by `composer setup`.
Create it manually if it is missing:

```bash
touch database/database.sqlite
```

**MySQL / MariaDB:**

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=solve_cms
DB_USERNAME=root
DB_PASSWORD=
```

Migrations are written driver-agnostic, so the same schema applies to both. Then run:

```bash
php artisan migrate --seed
```

### Media storage

Uploaded files are stored on the `public` disk under `media/YYYY/MM/`. Link the storage
directory once per environment:

```bash
php artisan storage:link
```

Uploads are validated by MIME type and size, and raster images get a WebP thumbnail
generated with GD (best effort — a failure never breaks the upload).

---

## Development

Start the Vite dev server and the Laravel server together:

```bash
composer dev
```

This runs `php artisan serve`, `npm run dev`, the queue listener and Pail log tailing
concurrently. To run them separately:

```bash
php artisan serve      # http://localhost:8000
npm run dev            # Vite HMR
```

If a frontend change does not appear, or you see an "Unable to locate file in Vite
manifest" error, run `npm run build` (or make sure `npm run dev` is running).

---

## Build Commands

```bash
npm run build      # production assets + Wayfinder JS conversion
npm run build:ssr  # same, plus SSR bundle
npm run dev        # Vite development server
npm run format     # format JS/Vue/CSS with Oxfmt (vp fmt)
npm run check      # lint JS/Vue with Oxc, type-aware
```

`npm run build` also runs `npm run wayfinder:js`, which converts the TypeScript files
generated by Laravel Wayfinder into JavaScript. Do not edit files under
`resources/js/actions`, `resources/js/routes` or `resources/js/wayfinder` by hand —
they are regenerated on every build and are git-ignored.

---

## Testing

```bash
# Everything (backend)
php artisan test --compact

# Frontend smoke tests (Vitest + jsdom)
npm run test:js

# Everything, in CI order
npm run ci:check

# A single suite or file
php artisan test --compact tests/Feature/PostCrudTest.php

# Filter by test name
php artisan test --compact --filter=slug
```

### What is covered

| Area                                                                 | Test file                                  |
| -------------------------------------------------------------------- | ------------------------------------------ |
| Admin authentication and access control                              | `tests/Feature/AdminAuthorizationTest.php` |
| Post create / update / delete, revisions, validation                 | `tests/Feature/PostCrudTest.php`           |
| Page create / update, slug uniqueness, self-parent guard             | `tests/Feature/PageCrudTest.php`           |
| Media upload, MIME rejection, metadata, delete                       | `tests/Feature/MediaUploadTest.php`        |
| Menu CRUD, item linking, cross-menu isolation                        | `tests/Feature/MenuManagementTest.php`     |
| Public routes, archives, search, comments, 404, SEO head             | `tests/Feature/FrontendTest.php`           |
| Theme registry, activation, customizer, draft/publish, preview       | `tests/Feature/ThemeEngineTest.php`        |
| Security headers, error pages, cache invalidation, health check      | `tests/Feature/FinalizationTest.php`       |
| Visitor chat, agent inbox, ownership, assignment, chat notifications | `tests/Feature/ChatTest.php`               |
| Notification generation, authorization and read state                | `tests/Feature/NotificationTest.php`       |
| Website settings, branding uploads, activity log                     | `tests/Feature/WebsiteSettingsTest.php`    |
| Media library API, featured images, inline content images            | `tests/Feature/PostMediaTest.php`          |
| HTML sanitizer allow-list and XSS removal                            | `tests/Feature/HtmlSanitizerTest.php`      |
| Sitemap XML and dynamic robots.txt                                   | `tests/Feature/SitemapAndRobotsTest.php`   |
| Redirects, redirect admin and SEO analyzer                           | `tests/Feature/SeoEngineTest.php`          |
| Slug generation and uniqueness                                       | `tests/Feature/SlugServiceTest.php`        |
| SEO metadata persistence and update                                  | `tests/Feature/SeoMetadataTest.php`        |
| Fortify auth flows                                                   | `tests/Feature/Auth/*`                     |

Tests run against an in-memory SQLite database (`phpunit.xml`) and use
`RefreshDatabase`, so they never touch your development data.

`npm run test:js` mounts the admin shell and every admin page in jsdom. It exists because a
server-side test cannot catch a Vue render crash — this suite catches undefined component
bindings, forbidden props (for example a `SelectItem` with an empty-string `value`) and
client-side navigation failures. `npm run build` also runs `scripts/check-props.mjs`, which
fails the build when a template binds a variable the component never declares.

---

## Authorisation Model

Roles and permissions are defined in `config/solve.php`, which is the single source of
truth. `RolePermissionSeeder` syncs them into the database.

**Roles:** Super Admin, Administrator, Editor, Author, Support Agent.

Permissions are grouped by area (`posts.publish`, `seo.redirects`, `chat.reply`, …).
Checks happen through Laravel Gates and Policies:

```php
$user->hasPermission('posts.publish');

Gate::authorize('update', $post);
```

`Gate::before` grants every ability to the Super Admin role. Policies exist for
`Post`, `Page`, `Comment`, `Media` and `User`.

**Enforcement is always server-side.** The sidebar hides items the current user cannot
access, but that is a convenience only — every controller action authorises the request
independently, and an unauthorised request receives `403`.

---

## Project Structure

```
app/
  Http/
    Controllers/Admin/     Dashboard and CRUD controllers, SEO controllers
    Controllers/Frontend/  Public home, blog, post, page, archives, search, comments
    Controllers/Seo/       Sitemap and robots.txt
    Middleware/            Inertia props, active-user check
    Requests/Admin/        Form request validation
  Models/                  Post, Page, Category, Tag, Media, Comment, ...
  Policies/                Post, Page, Comment, Media, User
  Providers/               App, Authorization, Fortify
  Events/ Listeners/       Broadcast events and notification listeners
  Notifications/           Chat, comment, user and content notifications
  Services/
    Content/               SlugService, PostService, HtmlSanitizer
    Media/                 MediaService (upload, thumbnails)
    Seo/                   SeoService, SeoAnalyzer, SeoPresenter (page metadata)
    Frontend/              FrontendData (site + menu props for public pages)
    Navigation/            MenuService (public menu resolution)
    Theme/                 ThemeRegistry (disk), ThemeService (activation, settings)
    Chat/                  ChatService (conversations, messages, assignment)
    Notifications/         Notifier (permission-aware delivery)
    ActivityLogger.php     Activity log writer
    SiteSettings.php       Typed site settings accessor

config/solve.php           Roles, permissions and role→permission map

resources/js/
  components/admin/        AdminSidebar, AdminTopbar, PageHeader, RichTextEditor, SeoPanel, navigation.js
  components/frontend/     PostCard, Pagination, Sidebar, CommentList, CommentForm, SeoHead, ChatWidget
  components/admin/        … NotificationDropdown
  components/ui/           shadcn-vue style primitives
  layouts/AdminLayout.vue  Admin shell (sidebar + topbar + toaster)
  layouts/FrontendLayout.vue  Public shell (header, nav, search, footer)
  pages/Admin/             Dashboard, Posts, Pages, Categories, Tags, Comments,
                           Media, Menus, Users, Seo/{Overview,Settings,Analysis,Redirects}
  pages/Frontend/          Home, Blog, Post, Page, Archive, Search, NotFound
  pages/Admin/Themes       Theme listing and activation
  pages/Admin/Customizer   Customizer with live preview
  pages/Admin/Chat         Live chat inbox
  pages/Admin/Notifications Notification history

resources/themes/          Theme packages (theme.json + styles/theme.css)
  lib/format.js            Date/status formatting helpers

routes/
  web.php                  Public root, /dashboard redirect, sitemap.xml, robots.txt
  admin.php                Everything under /admin

scripts/wayfinder-js.mjs   Wayfinder .ts → .js build step
```

---

## Status by Phase

| Phase | Description                                                               | Status       |
| ----- | ------------------------------------------------------------------------- | ------------ |
| 1     | Foundation — auth, admin layout, RBAC, dashboard, branding                | **Complete** |
| 2     | Core CMS — posts, pages, categories, tags, media, comments, menus, editor | **Complete** |
| 3     | SEO engine — metadata UI, analyzer, sitemap, robots, redirects            | **Complete** |
| 4     | Public frontend — dynamic homepage, blog, archives, search                | **Complete** |
| 5     | Theme engine and customizer                                               | **Complete** |
| 6     | Live chat and notifications (Reverb)                                      | **Complete** |
| 7     | Finalisation — security, performance, deployment                          | **Complete** |

### SEO engine

- **Metadata** per post, page, category and tag: SEO title, meta description, focus
  keyword, canonical URL, robots directive, Open Graph and Twitter fields (`seo_metadata`,
  polymorphic via `SeoService`).
- **Editor panel** — a live Google-style search preview plus a checklist of checks, both
  refreshed from `POST /admin/seo/analyze` as you type.
- **Content analyzer** (`SeoAnalyzer`) — 18 practical checks: title/description presence and
  length, keyword placement (title, description, slug, first paragraph, headings), content
  length, image alt text, internal/external links, heading hierarchy, slug length, canonical
  presence and duplicate metadata. It reports recommendations only; it never claims a score
  predicts ranking.
- **Sitemap screen** (`/admin/seo/sitemap`) lists exactly what crawlers receive — totals per
  content type and every URL with its last-modified, change frequency and priority — plus
  buttons to open `sitemap.xml` and `robots.txt` in a new tab. It warns when generation is
  switched off. Both the screen and the XML endpoint read from the same `SitemapBuilder`, so
  they can never disagree.
- **XML sitemap** at `/sitemap.xml` and **dynamic `robots.txt`** at `/robots.txt`. Both use
  the configured website URL. The sitemap de-duplicates URLs and only lists entries whose
  public route is registered, so the sitemap always reflects what the frontend actually
  serves: the homepage, pages, posts, categories and tags.
- **Redirect manager** — 301/302/307/308 rules with hit counting, activation toggle and a
  global middleware that only intercepts unmatched GET/HEAD paths (admin, storage and the
  SEO endpoints are skipped, and self-redirects are ignored to prevent loops).
- **Technical settings** page — website URL, title separator, default robots directive,
  organization name, Twitter handle, Google verification token and the sitemap switch.

### Public frontend (Phase 4)

| Route              | Renders                                                             |
| ------------------ | ------------------------------------------------------------------- |
| `/`                | Homepage — featured and latest posts, category overview             |
| `/blog`            | Blog index with pagination and a sidebar (categories, tags, recent) |
| `/blog/{slug}`     | Single post with related articles and comments                      |
| `/{slug}`          | Static page at the site root                                        |
| `/category/{slug}` | Category archive                                                    |
| `/tag/{slug}`      | Tag archive                                                         |
| `/search?q=`       | Site search across posts and pages                                  |

Notes:

- **SEO is server-rendered.** `SeoPresenter` composes title, description, canonical, robots,
  Open Graph, Twitter and JSON-LD (Organization, WebSite, Article, WebPage, BreadcrumbList),
  and `app.blade.php` writes them into the initial HTML. Crawlers and social scrapers see
  correct metadata without running JavaScript. Client-side navigation refreshes the tags in
  `SeoHead.vue`.
- **Pagination SEO.** Canonical always points at the unpaginated URL and results pages after
  the first are `noindex,follow`. Search results are `noindex,follow`.
- **Pages live at the root** for clean URLs. The catch-all route is registered last and
  excludes every reserved segment (auth, admin, blog, search, …), so it never shadows
  another part of the application.
- **Navigation** comes from the menus in the database, resolved through `MenuService`
  (primary and footer locations).
- **Appearance** is driven by Site Settings — `FrontendData` exposes the colours, font,
  container width, sidebar toggle and blog layout, and the layout applies them as CSS
  variables. Phase 5 turns this into a full theme engine.
- **Comments** can be submitted by visitors without an account and enter moderation as
  `pending`; only `approved` comments render.
- A themed **404** page is rendered for public GET requests via the exception handler.

### Theme engine and customizer (Phase 5)

Themes are configuration packages, not code. A theme is a directory under
`resources/themes/<slug>/` containing a `theme.json` manifest and an optional
`styles/theme.css`. **No PHP from a theme is ever read or executed.**

Manifest:

```json
{
    "name": "Solve Default",
    "version": "1.0.0",
    "author": "Solve CMS",
    "description": "…",
    "templates": {
        "header": ["classic", "centered"],
        "blog": ["grid", "list"]
    },
    "defaults": { "primary_color": "#2563eb", "header_layout": "classic" }
}
```

- **Registry** — `ThemeRegistry` scans the themes directory, validates slugs (no path
  traversal) and exposes metadata. Two themes ship: `solve-default` and `solve-minimal`.
- **Activation** — `ThemeService` syncs the registry into the `themes` table; exactly one
  theme is active at a time. Deactivating the only active theme is refused.
- **Customizer** — tabs for General (title, tagline, description, logo, favicon), Colors,
  Typography, Layout and Components. Only keys in `ThemeService::APPEARANCE_KEYS` are
  persisted; everything else is dropped and values are range-validated.
- **Draft / Publish / Reset** — edits are stored per theme in `theme_settings` under a
  `draft` or `published` scope. `Publish` promotes the draft, `Reset` restores the manifest
  defaults.
- **Live preview** — the customizer renders a live mock that reacts to every control, and
  the full site can be previewed with draft settings via `/?theme-preview=1`. That flag is
  honoured **only** for users holding `appearance.customize`; guests always see published
  settings.
- **Theme application** — `FrontendData` resolves appearance from the active theme and its
  published (or previewed draft) settings, and passes it to the public layout as CSS
  variables, layout variants and component toggles. The theme's own stylesheet is injected
  into the public site only.

### Hardening, performance and deployment (Phase 7)

**Security headers** — `SecurityHeaders` middleware adds a per-request CSP nonce and:

- `Content-Security-Policy`:
    - `script-src 'self' 'nonce-…'` — no `unsafe-inline`. Every inline script (dark-mode
      bootstrap, JSON-LD) and every Vite script tag carries the nonce.
    - `style-src-elem 'self' 'nonce-…'` in production — `<style>` elements are nonce-bound.
      Local relaxes this because the Vite dev server injects stylesheets without a nonce.
    - `style-src-attr 'unsafe-inline'` — **required**. A nonce cannot cover inline `style`
      attributes, and Vue bindings plus reka-ui primitives depend on them; without it the
      admin UI renders unstyled and unusable.
    - `object-src 'none'`, `frame-ancestors 'self'`, `base-uri 'self'`, and `connect-src`
      including the Reverb WebSocket origin.
- `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`,
  `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, and
  `Strict-Transport-Security` on HTTPS. `X-Powered-By` is removed.

**Performance** — added indexes for the hot listing queries (`posts.is_featured`,
`posts.published_at`, `comments.created_at`, unread notifications, chat recency), and short
cached reads for the homepage (featured, latest, categories) and the blog sidebar. Menu
resolution is cached per location. Every content mutation flushes the relevant keys, so the
site is never stale after publishing.

**Error handling** — public 403/404/419/429/500/503 responses render themed Inertia pages
with `noindex`, never a stack trace. Admin and JSON requests are unaffected.

**Tests added** — security headers and nonce matching, themed error pages, cache
invalidation on publish and menu edits, theme-settings propagation, and the health check.

#### Production deployment

```bash
# 1. Build
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 2. Environment
cp .env.example .env && php artisan key:generate

# 3. Database and storage
php artisan migrate --force
php artisan storage:link

# 4. Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

`.env` for production:

```dotenv
APP_ENV=production
APP_DEBUG=false          # never true in production
APP_URL=https://example.com
LOG_LEVEL=warning
BROADCAST_CONNECTION=reverb
REVERB_SCHEME=https
REVERB_HOST=ws.example.com
REVERB_PORT=443
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=true
```

Background processes (supervisor recommended):

```ini
; Queue worker — notifications, queued jobs
[program:solve-queue]
command=php /var/www/solve-cms/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2

; Reverb WebSocket server — live chat and notifications
[program:solve-reverb]
command=php /var/www/solve-cms/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
```

Scheduler (cron entry):

```cron
* * * * * cd /var/www/solve-cms && php artisan schedule:run >> /dev/null 2>&1
```

Operational notes:

- **Health check** — `GET /up` returns 200 when the application is booting correctly; point
  your load balancer here.
- **Deploy order** — after code changes run `php artisan migrate --force`, then
  `php artisan optimize:clear` followed by the cache commands, then restart the queue and
  Reverb workers so they pick up new code.
- **Reverb behind TLS** — terminate TLS at your proxy and forward WebSocket upgrades to
  Reverb; set `REVERB_SCHEME=https` and `REVERB_HOST`/`REVERB_PORT` to the public endpoint so
  the CSP `connect-src` and Echo both accept it.
- **Backups** — back up the database and the `storage/app` directory together; they hold
  content and uploaded media respectively.
- **Queues without Redis** — the application runs on the database queue driver; Redis is an
  optional upgrade for higher throughput.

### Website settings and activity log

- **Website Settings** (`/admin/settings`, permission `settings.manage`) — site name, tagline,
  description, canonical website URL, posts per page and timezone, plus **three independent
  branding uploads**. Uploads go through the media pipeline (validated, stored on the public
  disk).

    | Upload           | Setting key           | Where it appears              |
    | ---------------- | --------------------- | ----------------------------- |
    | Public site logo | `logo_path`           | Header of the public website  |
    | Dashboard logo   | `dashboard_logo_path` | Admin sidebar, login screen   |
    | Favicon          | `favicon_path`        | Browser tab, on both surfaces |

    The dashboard and the public website therefore carry **different logos**. The admin layout
    receives its logo through the `branding` Inertia prop; the public layout receives its own
    through `site.logo`, so changing one never affects the other. Both fall back to the
    built-in Solve mark when empty, and the favicon replaces the default `<link rel="icon">`
    tags in the root Blade view.

- **Activity Logs** (`/admin/activity`, permission `activity.view`) — every administrative
  action recorded by `ActivityLogger`, filterable by event, user and description, with
  pagination. Setting changes, branding uploads, content changes, moderation, chat actions and
  theme work are all logged with the acting user and IP address.

### Live chat and notifications (Phase 6)

Built on **Laravel Reverb** + **Laravel Echo**, with no fake real-time: when Reverb is
running, messages and notifications arrive over WebSockets; when it is not, the chat keeps
working through a polling fallback and broadcast failures are logged, never surfaced as
errors.

**Visitor chat** (`/chat`, JSON API + floating widget on every public page):

- Start a conversation without an account (name, optional email).
- Anonymous identity is a UUID kept in the server session; the channel is authorised
  against that session token, so a visitor can only ever read their own conversation.
- Rate limited per route (start 10/min, messages 30/min, reads 60/min).

**Agent inbox** (`/admin/chat`):

- Conversation list with status filters, search, unread counts, visitor online indicator
  and message history.
- Reply, assign to an agent, close and reopen. Replying auto-assigns the conversation.
- Agents only see conversations that are unassigned, assigned to them, or — with
  `chat.assign` — all of them. Enforced server-side on every read and write.
- Live updates via the private `chat.conversation.{id}` channel; polling fallback every
  6 seconds.

**Broadcasting authorisation** (`routes/channels.php`):

| Channel                  | Who may listen                                                        |
| ------------------------ | --------------------------------------------------------------------- |
| `chat.visitor.{token}`   | The visitor session that owns the token, or an agent with `chat.view` |
| `chat.inbox`             | Users with `chat.view`                                                |
| `chat.conversation.{id}` | `chat.assign` holders, the assigned agent, or anyone when unassigned  |
| `App.Models.User.{id}`   | The user themselves (notifications)                                   |

**Notifications** — Laravel database notifications with broadcast delivery, generated
from real domain events:

| Event                        | Recipients                     |
| ---------------------------- | ------------------------------ |
| Visitor sends a chat message | Users with `chat.view`         |
| New chat conversation        | Users with `chat.view`         |
| Conversation assigned        | The assigned agent             |
| Visitor comment submitted    | Users with `comments.moderate` |
| User registers               | Users with `users.manage`      |
| Content published            | Users with `posts.view`        |

Recipients are resolved through roles/permissions, so a user only ever receives
notifications they are authorised to see. The topbar has a bell dropdown with an unread
badge, mark-all-read and live updates; `/admin/notifications` is the full history.

**Running Reverb locally** — `composer dev` already starts it (alongside the server, queue
worker, Vite and log tailing). To run it alone:

```bash
php artisan reverb:start
```

Relevant `.env` values (already present in `.env.example`):

```dotenv
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=solve-cms
REVERB_APP_KEY=solve-cms-key
REVERB_APP_SECRET=solve-cms-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

In production, run `reverb:start` under a process supervisor behind TLS (set
`REVERB_SCHEME=https`, `REVERB_HOST` and `REVERB_PORT=443` to your WebSocket domain), keep a
queue worker running for notifications and other queued work, and rebuild the frontend so
the `VITE_REVERB_*` values are baked into the assets.

> Real-time delivery is only live while Reverb is running. Until then the chat works over
> polling and no live updates are claimed.

### Images in content and featured images

- **Featured image** — the post and page editors have a _Featured image_ field with a picker:
  select from the media library or upload a new file inline. The choice is stored in
  `featured_media_id` and drives post cards, the article header, `og:image` and the Article
  JSON-LD image.
- **Images inside content** — the rich text editor toolbar has an _Insert image_ button. It
  opens the same media library overlay and inserts `<img src alt loading="lazy">` at the
  caret. Content images pass through the server-side sanitizer, which allows `img` with
  `src`/`alt`/`width`/`height`/`loading` and strips event handlers.
- **Media API** — `GET /admin/media/library` returns a searchable JSON list of images (id,
  url, thumbnail, title, alt text, dimensions) and `POST /admin/media` returns JSON when the
  request asks for it, so both the picker and the editor upload without a page reload. Both
  endpoints enforce `media.view` / `media.upload`.

### Image sizing, text wrapping and error feedback

- **Resize images in content** — click an image inside the editor and a control strip
  appears: percentage presets (25/50/75/100), a custom width with `%` or `px`, and _Apply_.
- **Wrap text around images** — the same strip floats the image left or right (text flows
  beside it, with sensible margins), centres it, or makes it full width.
- **Alt text** is editable inline and **Remove** deletes the selected image.
- Sizing and wrapping are stored as an **inline `style`** on the image. The sanitizer only
  allows a small property allow-list (`width`, `height`, `max-width`, `float`, `clear`,
  `margin*`, `display`, `border-radius`, `object-fit`, `aspect-ratio`) on `img`/`figure`,
  rejects values containing `url(`, `expression(`, `javascript:` or quotes, and still strips
  `style` from every other element.
- **Backend errors are always shown** — `resources/js/lib/http.js` wraps fetch so the real
  server message (including Laravel validation errors) reaches the interface, and
  `resources/js/lib/errors.js` toasts `419`/`403`/`5xx` responses during Inertia visits
  instead of failing silently.
- **Upload hardening** — a file must pass both a content MIME check and a filename extension
  allow-list, and the extension written to disk is derived from the file content, so a text
  file named `payload.exe` is rejected rather than stored.

### Rich text editor

`resources/js/components/admin/RichTextEditor.vue` is a dependency-free editor built on a
`contenteditable` surface. It supports headings, bold/italic/underline, links, bullet and
numbered lists, blockquotes, code blocks and horizontal rules. Pasted content is reduced
to plain text.

Because the browser generates the HTML, **every write is sanitized on the server** by
`App\Services\Content\HtmlSanitizer` (allow-list tags and attributes, `javascript:` and
`data:` URLs removed, `script`/`style`/`iframe` dropped with their contents). Content is
cleaned on save for both posts and pages, so nothing untrusted is ever stored.

If a richer editing experience is wanted later, the component can be swapped for TipTap;
that would add dependencies and needs approval first.

---

## Security Notes

- CSRF protection is active on all state-changing routes (Laravel default).
- All requests are validated server-side with Form Requests; client-side validation is
  never trusted.
- Editor content is sanitised on save with an allow-list (`HtmlSanitizer`) before it is
  stored, and only that cleaned HTML is ever rendered on the public site.
- Uploads are validated by MIME type and maximum size before being stored.
- Authorisation is enforced with Gates and Policies on every admin action.
- Deactivated users (`status !== 'active'`) are logged out by the `active` middleware.
- Secrets live in `.env`; `.env` is git-ignored and must never be committed.

---

## Roadmap Notes

When Phases 3–6 begin, the following infrastructure will be required and will be
documented here:

- **Reverb (Phase 6):** `php artisan reverb:start`, a `BROADCAST_CONNECTION=reverb`
  setting, `VITE_REVERB_*` variables, and a running queue worker for notifications.
  Until those are configured, no real-time feature is enabled.
- **Redis (optional):** switch `CACHE_STORE`, `SESSION_DRIVER` and `QUEUE_CONNECTION`
  to `redis` for performance. The application works on database drivers without it.

---

## License

MIT.
