# CMS Blueprint

Dokumen ini adalah target arsitektur CMS modular untuk pengembangan bertahap.

## 1) Arsitektur Layer

- Core Framework: HTTP kernel, router, middleware, container, config, hooks, events, module manager.
- CMS Domain: posts, pages, category, tags, media, users, permissions, comments, settings.
- Infrastructure: db query builder, cache, queue sederhana, scheduler sederhana.
- Presentation: themes, partials, components, admin panel.

## 2) Struktur Folder Rekomendasi

```
app/
  Core/
    Container.php
    Cms.php
    CmsKernel.php
    ServiceProvider.php
    Support/
      EventDispatcher.php
      HookManager.php
      ModuleManager.php
  Cms/
    Posts/
      PostRepository.php
      PostService.php
    Pages/
    Taxonomy/
    Media/
    Users/
    Comments/
    Settings/
modules/
  CoreCms/
    Module.php
  Blog/
    Module.php
  Seo/
    Module.php
  Sitemap/
    Module.php
themes/
  default/
  modern/
```

## 3) Flow System

1. Request masuk ke public/index.php.
2. App melakukan load env dan boot CmsKernel.
3. CmsKernel mendaftarkan module provider dari folder modules.
4. Setiap module menjalankan register lalu boot.
5. Router dispatch request + middleware + controller.
6. Hook dan event dapat intercept lifecycle request.
7. Controller memanggil service/repository domain CMS.

## 4) Konvensi Implementasi

- Semua business logic di Service layer.
- Repository menangani query dan mapping data.
- Controller tipis, hanya orchestration request-response.
- Hook dipakai untuk extension point lintas modul.
- Event dipakai untuk proses async/non-critical (analytics, log, notifikasi internal).

## 5) Prioritas Implementasi Bertahap

- Fase 1: extensibility core (hooks/events/providers/module discovery).
- Fase 2: post, page, taxonomy, slug, draft-publish-trash, revision.
- Fase 3: media library, comment, menu builder, widget.
- Fase 4: theme engine dan shortcode/block editor.
- Fase 5: plugin ecosystem, queue, scheduler, cache optimization.
