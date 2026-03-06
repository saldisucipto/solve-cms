# Solve Framework / CMS

Ini adalah PHP framework yang akan berfungsi sebagai framework yang mempunyai fitur mirip laravel namun dengan tidak terlalu banyak dependancy yang berat dan digunakan sebagai kerangka kerja yang flexible dan tidak terpaku pada satu ekosistem saja.

## Tujuan

- Hanya tidak ingin terpaku sama laravel dan breaking change
- Biar ngerti aja

### Overview

Framework ini menggunakan pendekatan modular dengan core system
yang ringan dan mudah dikembangkan.

### Tech Stack

**Frontend:** Tailwindcss, Vite.js, Vanilla JS, Native CSS & Native PHP.
**Server:** PHP
**Database:** MYSQL

---

## Komponen Utama

- Kernel
- Router
- Middleware
- Controller
- Service Container
- Config Loader

---

## Helpful Docs

- [Request Lifecycle](#request-lifecycle)
- [Routing System](#routing-system)
- [Middleware](#middleware)
- [Cache](#cache)
- [CLI](#cli)

### Request Lifecycle

```mermaid
graph TD
    Request --> Kernel
    Kernel --> Router
    Router --> Middleware
    Middleware --> Controller
    Controller --> Response
```

---

### Routing System

> Routing system menggunkan 2 file yaitu admin.php untuk bagian dashboard dan web.php untuk bagian public.

### Cache

> Implementasi Cache sudah dilakukan dengan File Driver

## CLI

> Implementasi CLI untuk beberapa hal yang butuh dilakukan seperti pembuatan seeder ke database, pembuatan file startup ketika project dijalankan, Cara Penggunaanya seperti ini :

```
php cli/seed.php
```

## Dev Server

Command ini menjalankan dev server untuk PHP di folder public, sekaligus menjalankan Vite dev Server npm run dev :

```
php cli/develop.php
```
