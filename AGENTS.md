# AGENTS.md — SIPAMAN Laravel Project Guide

This file is the coding guide for AI agents working in this repository. It is **not** a one-time task prompt. Use it to understand the project structure, current code reality, business rules, file responsibilities, and verification requirements before editing code.

This guide was re-verified against the current uploaded code snapshot containing:

- `app/`
- `routes/`
- `resources/`
- `database/`
- `composer.json`

Important principle: **separate current implementation from accepted target rules**. Some accepted business rules below are not fully implemented yet. If a rule is listed as a target/pending gap, do not claim it is already implemented until the code has actually been changed and verified.

---

## 1. Project overview

SIPAMAN stands for **Sistem Informasi Pangan Aman**. It is a Laravel-based information system for PIRT products in Karanganyar.

The system has three main areas:

1. **Public website**
   - Public visitors can view verified PIRT products.
   - Public visitors can browse UMKM/pelaku usaha.
   - Public landing page content is loaded from the database.

2. **User / pelaku usaha area**
   - Product owner accounts are linked to verified PIRT products.
   - User accounts may be created automatically from Status Pemenuhan Komitmen import when a product becomes verified and has NIB.
   - Target business rule: user/pelaku usaha uses **NIB** as identity/login identifier, not email.
   - Target business rule: user can only edit limited display/support fields, not official PIRT data.

3. **Admin / super admin area**
   - Admin manages operational data: products, imports, verification, jenis barang, product images, landing page content, activity/import monitoring.
   - Super admin additionally manages admin accounts, system settings, and audit trails.

The codebase uses both Blade web controllers and JSON API controllers. When adding or changing logic, keep business rules reusable so web and API behavior do not drift apart.

---

## 2. Tech stack and commands

### Backend

Current `composer.json` requires:

- PHP `^8.3`
- Laravel Framework `^13.0`
- Laravel Sanctum `^4.3`
- Laravel Tinker `^3.0`
- Maatwebsite/Laravel-Excel `^3.1`

Dev dependencies include Laravel Pail, Pint, Mockery, Collision, and PHPUnit.

### Frontend asset pipeline

- Vite via `laravel-vite-plugin`
- Tailwind CSS via `@tailwindcss/vite`
- Blade views
- Frontend entry points are declared in `vite.config.js`: `resources/css/app.css` and `resources/js/app.js`.
- Put shared browser behavior, admin UI behavior, and small page initializers in `resources/js/app.js`; avoid inline Blade scripts unless a page truly needs one-off server-rendered data.

### Important commands

Use these when relevant:

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan test
npm install
npm run build
```

Development command from `composer.json`:

```bash
composer run dev
```

Current `composer run dev` runs Laravel server, queue listener, Laravel Pail, and Vite together. Do not change this script unless the task explicitly requires development tooling changes.

Development command from `package.json`:

```bash
npm run dev
```

Current `npm run dev` starts Vite only. Use it when only frontend assets/hot reload are needed.

---

## 3. Mandatory workflow for every coding agent

Before editing code:

1. Read this `AGENTS.md` completely.
2. Inspect the exact related files first: route, controller, request, service, model, migration, seeder, Blade, API response, and tests/checklist.
3. State a short implementation plan before changing code.
4. Identify whether the task touches web only, API only, or both.
5. Identify whether the change touches schema, storage, imports, authentication, authorization, or public-facing content.

While editing code:

1. Keep controllers thin.
2. Put business logic in services/support classes.
3. Put validation in FormRequests where appropriate.
4. Use existing services before creating new ones.
5. Do not duplicate logic between web and API controllers.
6. Keep role authorization enforced by route middleware/policy/controller checks, not only hidden buttons in Blade.
7. Keep Indonesian UI messages clear and non-technical for admin/pemerintahan users.
8. Never log raw passwords, password hashes, API keys, tokens, session values, `.env` secrets, or full uploaded file contents.
9. Do not mass-rename tables, columns, routes, model classes, namespaces, or controller classes unless explicitly requested and all references are updated.
10. Do not delete files until references are checked in routes, imports, views, API clients, and search results.

After editing code:

1. Run the relevant commands/tests when possible.
2. Review the diff and confirm no unrelated changes were made.
3. Verify web and API rules remain consistent when both paths exist.
4. Report exactly what was changed, what was tested, and what was not tested.

---

## 4. Definition of Done / Verification Gate

Every task must pass this verification gate before the agent reports completion.

### 4.1 Required self-review

Check:

1. The change follows current project structure.
2. The change matches the accepted business rule.
3. Controllers remain thin.
4. Validation is not hidden only in Blade.
5. Authorization is not hidden only by UI.
6. Web and API behavior are aligned when they share a feature.
7. Schema changes update migration, model fillable/casts/relations, FormRequest, service/controller, Blade/API response, seeder, and manual/test checklist.
8. Storage changes delete or preserve files intentionally.
9. Logs do not contain secrets or sensitive credential data.
10. Admin-facing text is understandable for non-IT staff.
11. The change does not break route names used by Blade/API.
12. The change does not introduce duplicate logic where a service already exists.

### 4.2 Commands to run when relevant

Run these when the task touches backend/schema/API:

```bash
php artisan test
```

Run this when Blade/assets/frontend styling may be affected:

```bash
npm run build
```

Run this when migrations/seeders change and a fresh local database is acceptable:

```bash
php artisan migrate:fresh --seed
```

Run this when storage symlinks are needed for image display:

```bash
php artisan storage:link
```

If a command cannot be run, state the exact reason. Do not claim it passed.

### 4.3 Required final report format

When finishing a coding task, report:

1. Summary of changes.
2. Files changed.
3. Why each change was needed.
4. Migration/seed impact.
5. Commands/tests actually run and results.
6. Manual checks performed.
7. Remaining risks or follow-up.

Never claim UI was checked unless it was actually opened or can only be reasoned from code with that limitation stated.

---

## 5. Current route map

### 5.1 Web routes: `routes/web.php`

Public:

- `GET /` → `Web/Public/HomeController@index`
- `GET /products` → `Web/Public/ProductController@index`
- `GET /products/{produk}` → `Web/Public/ProductController@show`
- `GET /umkm` → `Web/Public/UmkmController@index`
- `GET /umkm/{namaPelakuUsaha}` → `Web/Public/UmkmController@show`

Auth:

- `GET /login` → `Web/Auth/AuthenticatedSessionController@create`
- `POST /login` → `Web/Auth/AuthenticatedSessionController@store`
- `POST /logout` → `Web/Auth/AuthenticatedSessionController@destroy`
- `/register` redirects to `/login`

User routes use middleware:

```php
['auth', 'role:user']
```

Prefix/name:

```text
/user
user.*
```

Current user routes:

- `/user/dashboard`
- `/user/account`
- `/user/account/password`
- `/user/products/setting`
- `/user/products/setting/{id}/edit`
- `/user/products/setting/{id}`
- `/user/products/setting/{id}/gambar`

Admin routes use middleware:

```php
['auth', 'role:admin,super_admin']
```

Prefix/name:

```text
/panel
panel.*
```

Current admin routes:

- Dashboard
- `panel.products.index/show` only; product data is read-only from the admin UI
- Rekap PIRT import
- Dedicated product image management page under `panel.product-images.*`
- Verifications index/import/read-only detail
- `panel.jenis-barang.*` resource except show
- Jenis Barang review page for `Lainnya / Perlu Review`
- Jenis Barang sync action to reclassify existing products
- Admin pelaku usaha account management under `panel.pelaku-usaha.*`
- Landing page index/edit/update
- Activity logs
- Import logs

Super admin routes use middleware:

```php
['auth', 'role:super_admin']
```

Prefix/name:

```text
/super-admin
super-admin.*
```

Current super admin routes:

- `super-admin.users.*` resource except show
- `super-admin.settings.update-group`
- `super-admin.settings.index/update`
- `super-admin.audit-trails.index`

### 5.2 API routes: `routes/api.php`

Auth API:

- `POST /api/auth/register` is not registered because self-registration is disabled.
- `POST /api/auth/login`
- Protected by Sanctum:
  - `POST /api/auth/logout`
  - `GET /api/auth/me`
  - `POST /api/auth/update-profile`

Public API:

- `GET /api/produk`
- `GET /api/produk/filter`
- `GET /api/produk/{produk}`
- `GET /api/landing-page`

User API uses middleware:

```php
['auth:sanctum', 'role:user']
```

Current user API routes:

- `GET /api/user/dashboard`
- `GET /api/user/produk`
- `GET /api/user/produk/{produk}`
- `PATCH /api/user/produk/{produk}`
- `POST /api/user/produk/{produk}/image`

Admin API uses middleware:

```php
['auth:sanctum', 'role:admin,super_admin']
```

Current admin API routes:

- `GET /api/admin/produk`
- `GET /api/admin/produk/{produk}`
- Rekap PIRT import
- Status Komitmen import
- Status Komitmen import is the only verification-status API path; manual update/reject endpoints are not registered.
- Product image upload/delete, guarded to verified products only
- Landing page admin index/update for managed fixed-layout sections

Super admin API uses middleware:

```php
['auth:sanctum', 'role:super_admin']
```

Current super admin API routes:

- `apiResource /api/super-admin/users`
- `apiResource /api/super-admin/settings` index/update only
- audit trail list
- activity log list

---

## 6. Authentication and user identity rules

### 6.1 Current implementation

Current web and API login use a single `identifier` field in the form/request, but lookup is role-aware:

- role `user` / pelaku usaha is matched by `users.nib`,
- role `admin` and `super_admin` are matched by `users.email`.

The current login flow blocks accounts with `password = null` using `needsPasswordSetup()` and blocks accounts with `status_akun` other than `aktif`.

### 6.2 Accepted target rule

- Role `user` / pelaku usaha must use **NIB** as login identity.
- Email should not be used, displayed, or edited for role `user`.
- Role `admin` and `super_admin` may continue using email for login.
- Do not drop the `email` column globally because admin/super admin still need it.
- User-facing login errors should say **NIB/password** for user flows, or use neutral wording that does not confuse pelaku usaha.

### 6.3 Current implementation notes

Current implementation now follows these rules for the touched paths:

- Role-based login lookup is now split: pelaku usaha is matched by NIB, admin/super admin by email.
- API `formatUser()` hides `email` for role `user`.
- API `updateProfile()` prohibits `nama` and `email` updates for role `user`.
- User account page shows NIB/name read-only and only allows password update.
- Current UserSeeder seeds role `user` with NIB and `email = null`.

When changing auth/profile behavior, update web, API, Blade, validation, and tests/checklist together.

---

## 7. Public website flow

Main files:

- `app/Http/Controllers/Web/Public/HomeController.php`
- `app/Http/Controllers/Web/Public/ProductController.php`
- `app/Http/Controllers/Web/Public/UmkmController.php`
- `resources/views/public/home.blade.php`
- `resources/views/public/products/index.blade.php`
- `resources/views/public/products/show.blade.php`
- `resources/views/public/umkm/index.blade.php`
- `resources/views/public/umkm/show.blade.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/partials/public/navbar.blade.php`
- `resources/views/partials/public/hero.blade.php`
- `resources/views/partials/public/footer.blade.php`

Rules:

- Public catalog must show only `Produk::verified()`.
- Public product detail must abort 404 when product is not verified.
- Public product cards should tolerate missing images.
- Public product filters must use normalized relational fields when available, not raw imported strings.
- Public navbar and footer labels/content must read from `SystemSettings` with safe fallbacks.
- Public navbar routes remain fixed by code; super admin may only edit the logo, site name/tagline, and menu labels from System Settings.

Current behavior:

- Web and API public catalog both support `jenis_barang_id`.
- The web dropdown is loaded from active `jenis_barangs`.
- Public catalog queries use `verified()`, `byKecamatan()`, `byJenisBarang()`, and `search()`.

---

## 8. Product and PIRT data rules

`Produk` is the central PIRT product model.

Important fields:

- `no_sppirt`
- `nama_branding`
- `kategori_pangan`
- `jenis_pangan`
- `kemasan`
- `cara_penyimpanan`
- `wilayah`
- `kecamatan_id`
- `jenis_barang_id`
- `nama_pelaku_usaha`
- `alamat`
- `nib`
- `no_hp`
- `nama_toko`
- `alamat_toko`
- `harga`
- `deskripsi`
- `tanggal_pengajuan`
- `tanggal_verifikasi`
- `masa_berlaku_pirt`
- `status_oss`
- `is_verified`

Important relationships:

- `user`
- `kecamatan`
- `jenisBarang`
- `gambarProduks`
- `gambarUtama`
- `verifikasi`
- `commitmentStatus`

Important scopes:

- `verified`
- `byKecamatan`
- `byJenisBarang`
- `ownedBy`
- `search`

### 8.1 Official/legal data vs display data

Official PIRT data comes from imports. Admin/super admin must not create, edit, update, or delete official product records manually from the Produk menu or admin product API. Verification status is updated through Status Pemenuhan Komitmen import, not manual admin web/API edits. User/pelaku usaha must not edit official/legal fields.

Fields that user must not edit:

- `no_sppirt`
- `nib`
- `nama_branding`
- `nama_pelaku_usaha`
- `kategori_pangan`
- `jenis_pangan`
- `kemasan`
- `cara_penyimpanan`
- `wilayah`
- `kecamatan_id`
- `jenis_barang_id`
- `is_verified`
- `tanggal_pengajuan`
- `tanggal_verifikasi`
- `masa_berlaku_pirt`
- `status_oss`
- `no_hp`
- `user_id`

Accepted target rule:

- User must not edit `nama_toko` either.
- User may only edit explicitly allowed support/display fields such as `harga`, possibly `deskripsi`, and product image depending on final UI policy.

Current implementation:

- Web and API user product update use a shared FormRequest.
- User may update `harga` and `deskripsi`.
- User is prohibited from updating `nama_toko`, `alamat_toko`, legal PIRT fields, verification fields, NIB, ownership, and normalized classification fields.

---

## 9. Jenis Barang classification rules

### 9.1 Current implementation

Current code has:

- `app/Models/JenisBarang.php`
- `app/Models/JenisBarangAlias.php`
- `app/Support/ProductTypeClassifier.php`
- `database/migrations/2026_06_02_000002_add_official_fields_to_jenis_barangs_table.php`
- `database/migrations/2026_05_29_000002_create_jenis_barang_aliases_table.php`
- `database/seeders/JenisBarangSeeder.php`

`ProductTypeClassifier` resolves imported raw `kategori_pangan` and `jenis_pangan` into simplified `jenis_barangs` categories. It checks database aliases first, then built-in keyword rules, then fallback category:

```text
Lainnya / Perlu Review
```

`ProdukImport` stores raw Excel values in:

- `kategori_pangan`
- `jenis_pangan`

and stores simplified relation in:

- `jenis_barang_id`

This is the correct direction: raw imported data remains preserved, while public/admin filters use normalized categories.

### 9.2 Current categories

Default classifier categories include:

- Makanan Ringan
- Roti & Kue
- Minuman
- Bumbu & Sambal
- Olahan Hewani
- Olahan Buah & Sayur
- Olahan Kacang, Biji & Umbi
- Gula, Madu & Pemanis
- Makanan Siap Saji
- Lainnya / Perlu Review

### 9.3 Current implementation

Current admin `JenisBarangController` only handles simple CRUD for `nama_jenis`.

Current implementation:

- Admin UI manages `slug`, `deskripsi`, `is_active`, and `jenis_barang_aliases`.
- Admin can review products in `Lainnya / Perlu Review`.
- Admin can run `Sinkronkan Ulang Jenis Produk` after alias changes.
- Public web and admin product filters use `jenis_barang_id` through `Produk::byJenisBarang()`.

Target rule:

- Admin should be able to add/edit aliases/keywords without calling a programmer.
- New unknown import values should land in `Lainnya / Perlu Review`.
- Admin should be able to reclassify existing products after alias changes.
- Classification logic must stay in service/support class, not controller or Blade.

---

## 10. Import flow

### 10.1 Shared import service

Main service:

- `app/Services/ProductImportService.php`

Support files:

- `app/Support/Imports/SpreadsheetFileResolver.php`
- `app/Support/Imports/SpreadsheetTemplateValidator.php`
- `app/Rules/ImportSpreadsheetFile.php`
- `app/Http/Requests/Admin/Concerns/HasImportSpreadsheetRules.php`
- `app/Http/Requests/Admin/ImportProductRequest.php`
- `app/Http/Requests/Admin/ImportCommitmentStatusRequest.php`

Current import validation supports:

- `.xls`
- `.xlsx`
- `.csv`

Current validation max size:

- 10 MB

The service resolves reader type explicitly before calling Laravel Excel.

### 10.2 Rekap Data PIRT import

Main files:

- Web: `Web/Admin/ProductImportController.php`
- API: `Api/Admin/ProductImportController.php`
- Import class: `app/Imports/ProdukImport.php`

Current spreadsheet mapping in `ProdukImport`:

- A: No
- B: No SPPIRT
- C: Nama Branding Produk
- D: Kategori Pangan
- E: Jenis Pangan
- F: Kemasan
- G: Cara Penyimpanan
- H: NIB
- I: Wilayah
- J: Tanggal Pengajuan
- K: Status OSS
- L: No HP
- M: Nama Pelaku Usaha
- N: Alamat

Rules:

- Data starts at row 5.
- `no_sppirt`, `nama_branding`, `nama_pelaku_usaha`, and `alamat` are required for a valid product row.
- Existing products are matched by `no_sppirt`.
- Existing product `is_verified` must not be reset by Rekap PIRT re-import.
- New products default to `is_verified = false`.
- Raw Excel data remains stored.
- `jenis_barang_id` is set through `ProductTypeClassifier`.
- Row failures must remain readable.

### 10.3 Status Pemenuhan Komitmen import

Main files:

- Web: `Web/Admin/ProductVerificationController.php`
- API: `Api/Admin/ProductVerificationController.php`
- Import class: `app/Imports/PirtCommitmentStatusImport.php`

Current spreadsheet mapping:

- A: No
- B: No SPPIRT
- C: Provinsi
- D: Kab/Kota
- E: Nama Pelaku Usaha
- F: Alamat Usaha
- G: Phone
- H: Terdaftar
- I: NIB
- J: Verifikasi Produk
- K: Verifikasi Label
- L: PKP
- M: CPPOB
- N: Status Pemenuhan Komitmen

Rules:

- Data starts at row 2.
- Missing `no_sppirt` fails the row.
- Unknown `no_sppirt` fails the row and tells admin to import Rekap PIRT first.
- Writes to `pirt_commitment_statuses`.
- Writes/updates `verifikasi_produks`.
- Updates `produks.is_verified`.
- If product becomes verified and has NIB, create/link one `user` account per NIB.
- Auto-created user has `email = null` and `password = null`.
- User with null password cannot login until admin sets password.
- Status Pemenuhan Komitmen import is the only source allowed to change `verifikasi_produks`, `pirt_commitment_statuses`, and `produks.is_verified`.
- Admin web/API verification screens are read-only except for the Excel import action.

Current behavior:

- When product becomes verified, `tanggal_verifikasi` is set to now.
- `masa_berlaku_pirt` is set to now + 5 years.

Be careful: if real PIRT expiry dates later come from official documents, update this logic deliberately and document the source of truth.

---

## 11. Product image rules

### 11.1 Current implementation

Current image files:

- `app/Models/GambarProduk.php`
- `app/Services/ProductImageService.php`
- `app/Http/Requests/Admin/StoreProductImageRequest.php`
- `Api/Admin/ProductImageController.php`
- `Web/User/ProductSettingController.php`
- `Api/User/ProductImageController.php`
- `resources/views/admin/products/show.blade.php`
- `resources/views/user/products/setting-edit.blade.php`

Current schema:

- `gambar_produks` has `produk_id`, `url_gambar`, `is_primary`, `uploaded_at`.
- A cleanup/constraint migration enforces one `gambar_produks` row per product.

Current service:

- `ProductImageService::replaceOne(Produk $produk, UploadedFile $file)`
- `ProductImageService::delete(GambarProduk $gambarProduk)`

Current implementation:

- Admin web, admin API, user web, and user API upload flows use `ProductImageService::replaceOne()`.
- `ProductImageService::replaceOne()` and `delete()` reject changes when the product is not verified.
- The dedicated admin/super admin **Gambar Produk** page lists only verified products; its search, image-status filters, and statistics are scoped to `Produk::verified()`.
- Uploading a new image deletes old records/files and stores the new image as primary.
- User-facing image delete/set-primary routes were removed.
- UI uses a single active image and “Ganti Gambar” wording.

### 11.2 Accepted target rule

Final intended rule: **1 product has exactly one active product image**.

When a new image is uploaded:

1. Delete old physical file from `storage/app/public`.
2. Delete or replace old `gambar_produks` record.
3. Store the new file.
4. Save new record as primary.
5. Ensure public/admin/user display still works when no image exists.

Product images may only be changed after the product is verified. UI should explain this clearly, but the service/controller/API guard is mandatory so direct URL/API access cannot bypass the rule. The operational product image management page must not list unverified products at all.

Implementation note:

- The single-image rule is implemented through service logic, request validation, UI changes, API changes, and a data cleanup/unique-index migration.

---

## 12. User / pelaku usaha area

Main current files:

- `Web/User/DashboardController.php`
- `Web/User/AccountController.php`
- `Web/User/ProductSettingController.php`
- `Api/User/DashboardController.php`
- `Api/User/ProductController.php`
- `Api/User/ProductImageController.php`
- `resources/views/user/dashboard.blade.php`
- `resources/views/user/settings/index.blade.php`
- `resources/views/user/products/setting.blade.php`
- `resources/views/user/products/setting-edit.blade.php`

Current web user features:

- Dashboard product cards.
- Account page with NIB/name read-only and password form.
- Product settings page.
- Product edit page.
- Single image replacement.

Accepted target rules:

- Dashboard product action should only show **Edit**.
- User must not delete products.
- User must not see multiple confusing icons for the same edit action.
- Remove/disable `view`, `tune`, and `delete` actions from dashboard card if they do not have distinct allowed functions.
- User must not edit own name from user area.
- User area must not display email.
- User must not edit `nama_toko`.
- User may edit only allowed support fields such as `harga`, possibly `deskripsi`, and image.

Current implementation:

- Dashboard product card exposes one action: Edit.
- User settings do not display email and do not allow name updates.
- User product edit only allows `harga`, `deskripsi`, and image replacement.
- User product image replacement is only available for verified products and is also blocked by `ProductImageService`.
- User image UI no longer supports multiple images, set-primary, or delete.
- Web/API routes no longer expose user image delete or set-primary.

When implementing these accepted rules, update routes, controllers, Blade, API, and tests/checklist together.

---

## 13. Admin operational flow

Admin routes are available to both `admin` and `super_admin` through:

```php
['auth', 'role:admin,super_admin']
```

Main admin files:

- `Web/Admin/DashboardController.php`
- `Web/Admin/ProductController.php`
- `Web/Admin/ProductImageManagementController.php`
- `Web/Admin/ProductImportController.php`
- `Web/Admin/PelakuUsahaAccountController.php`
- `Web/Admin/ProductVerificationController.php`
- `Web/Admin/JenisBarangController.php`
- `Web/Admin/LandingPageController.php`
- `Web/Admin/LogController.php`
- `Web/Admin/ImportLogController.php`
- `resources/views/partials/admin/sidebar.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/products/*`
- `resources/views/admin/verifications/*`
- `resources/views/admin/jenis-barang/*`
- `resources/views/admin/landing-page/index.blade.php`
- `resources/views/admin/logs/index.blade.php`
- `resources/views/admin/import-logs/index.blade.php`

Current sidebar groups:

- Utama
  - Dashboard
- Data PIRT
  - Produk
  - Gambar Produk
  - Jenis Barang
  - Verifikasi
- Pengguna
  - Akun Pelaku Usaha
- Konten Website
  - Landing Page
- Monitoring
  - Log Aktivitas
  - Riwayat Import
- Super Admin, only for `super_admin`
  - Kelola User
  - System Settings
  - Audit Trail

Current implementation:

- Admin product data from the **Produk** menu is read-only. Admin/super admin may list, search, filter, view detail, and import Rekap PIRT, but must not create, edit, update, or delete official product records manually.
- Admin product create/update/delete web routes and API routes are not part of the active route map. Keep old views/controllers unused unless deliberately reviewed.
- `panel.product-images.*` provides the operational **Gambar Produk** page for admin and super admin, scoped to verified products only.
- `panel.pelaku-usaha.*` lets admin/super admin manage only role `user` / pelaku usaha accounts.
- Legacy admin user/category/UMKM views from older snapshots are not present in the current filesystem. Do not recreate or wire them without a fresh feature request and route/controller review.
- Broad admin-scope user management API is not registered; pelaku usaha account management lives under the shared web panel, while super-admin admin-account management lives under `/super-admin`.

Do not wire old/legacy files blindly. Inspect and adapt them or build a clean controller/request/view path.

---

## 14. Super admin flow

Super admin routes are restricted to:

```php
['auth', 'role:super_admin']
```

Current files:

- `Web/SuperAdmin/UserManagementController.php`
- `Web/SuperAdmin/SystemSettingController.php`
- `Web/SuperAdmin/AuditTrailController.php`
- `Api/SuperAdmin/UserManagementController.php`
- `Api/SuperAdmin/SystemSettingController.php`
- `Api/SuperAdmin/AuditTrailController.php`
- `resources/views/super-admin/users/*`
- `resources/views/super-admin/settings/index.blade.php`
- `resources/views/super-admin/audit-trails/index.blade.php`

Current web user management behavior:

- Super admin can create admin accounts.
- Super admin cannot edit/delete their own account from this page.
- Super admin cannot edit/delete another super admin through this flow.
- `StoreUserRequest` only allows creating role `admin`.
- `UpdateUserRequest` prohibits changing name, email, NIB, and role; allows password and status updates.
- Destroy only allows deleting admin accounts; pelaku usaha should be nonactivated/locked instead.

Rules:

- Do not allow normal admin direct access to `/super-admin/*`.
- Do not let normal admin create/edit/delete admin or super admin accounts.
- Do not expose super-admin-only menu items to normal admin.
- If admin pelaku usaha management is added, keep it in admin scope with role restrictions that only allow role `user` records.

---

## 15. Landing page content rules

Current files:

- `app/Models/LandingPageContent.php`
- `app/Services/LandingPageContentService.php`
- `Web/Admin/LandingPageController.php`
- `Api/Admin/LandingPageController.php`
- `app/Http/Requests/Admin/UpdateLandingPageRequest.php`
- `database/migrations/2024_01_01_000011_create_landing_page_contents_table.php`
- `database/seeders/LandingPageContentSeeder.php`
- `resources/views/admin/landing-page/index.blade.php`
- `resources/views/admin/landing-page/edit.blade.php`
- public Blade files consuming landing content

Current fields:

- `section_key`
- `judul`
- `subjudul`
- `konten`
- `image_path`
- `image_alt`
- `button_text`
- `button_url`
- `secondary_button_text`
- `secondary_button_url`
- `is_active`
- `updated_by`

Current service behavior:

- Defines the managed fixed-layout sections and their human-friendly labels.
- Stores new landing images on public disk.
- Deletes old image when replaced or removed.
- Updates `updated_by`.

Rules:

- Landing page is fixed-layout.
- Admin may edit safe content fields only.
- Admin must not edit section key, route, CSS class, order, or Blade structure.
- Keep section keys seeded and controlled.
- The managed landing page sections are exactly `hero`, `featured_products`, and `region_potential`, in that order.
- Admin-facing labels are "Banner Utama", "Bagian Produk Terverifikasi", and "Bagian Potensi Wilayah".
- Only "Banner Utama" may have an editable image. Other sections edit text, button, and active status only.
- Banner Utama has two managed buttons: primary product button (`button_text` / `button_url`) and secondary UMKM button (`secondary_button_text` / `secondary_button_url`).
- Sections besides Banner Utama use one button when needed and must not show secondary-button fields.
- Validate `button_url` and `secondary_button_url` to only allow `http://`, `https://`, `/`, or `#`.
- Do not store landing page paragraph content in `system_settings`.

Current UI:

- Admin Landing Page does not use display previews. Do not show content preview text, button previews, image previews, placeholder previews, example website cards, or a dedicated right-side preview area.
- Admin index shows simple section cards with section name, function description, website status, last-edited info when available, and an "Edit Bagian" action.
- Admin edit form uses friendly section labels, manages content fields, and does not expose `section_key` as an editable field.
- `image_alt` is labeled as "Keterangan gambar".
- Button URLs are managed through human-friendly dropdowns for common public pages, "Tidak memakai tombol", plus validated custom-link fields.
- Upload helper text includes recommended image size/ratio.
- Only the "Banner Utama" edit form shows image upload/removal controls; other managed sections do not show image inputs.

---

## 16. System settings rules

Current files:

- `app/Models/SystemSetting.php`
- `app/Services/SystemSettingService.php`
- `app/Support/SystemSettingCatalog.php`
- `app/Support/SystemSettings.php`
- `Web/SuperAdmin/SystemSettingController.php`
- `Api/SuperAdmin/SystemSettingController.php`
- `app/Http/Requests/SuperAdmin/UpdateSystemSettingGroupRequest.php`
- `app/Http/Requests/SuperAdmin/UpdateSystemSettingRequest.php`
- `database/migrations/2026_05_12_065652_create_system_settings_table.php`
- `database/seeders/SystemSettingSeeder.php`
- `resources/views/super-admin/settings/index.blade.php`

Current seeded keys:

- `site_logo_path`
- `site_name`
- `site_tagline`
- `nav_home_label`
- `nav_products_label`
- `nav_umkm_label`
- `contact_email`
- `contact_phone`
- `office_address`
- `office_hours`
- `footer_copyright`
- `footer_verified_text`
- `default_pagination`
- `import_max_file_size_kb`

Rules:

- Use system settings for global non-secret configuration only.
- System Settings controls website identity, public navbar labels, public footer/contact text, and safe global system configuration.
- Landing Page content must not be mixed into System Settings.
- Super admin may edit `site_logo_path`, `site_name`, `site_tagline`, `nav_home_label`, `nav_products_label`, `nav_umkm_label`, contact/footer fields, and safe system values.
- Logo upload is stored on the `public` disk as a relative path, validates JPG/PNG/WebP max 2 MB, and replaces/deletes the previous managed upload.
- Navbar menu routes (`/`, `/products`, `/umkm`) are fixed in code; settings only change the visible labels.
- Do not store passwords, tokens, API keys, private keys, or secrets in system settings.
- Secrets belong in `.env`.
- `deskripsi` is a helper explanation for admins/super admins, not public content.
- `SystemSettings::forget()` must be called after setting updates because settings are cached.
- `default_pagination` must be read through the shared settings helper/cache and used by relevant product listing pages instead of hardcoded pagination numbers.
- `default_pagination` must normalize to a safe integer from 3 to 100 and fall back to 12 when empty or invalid.
- The default footer copyright is `© 2026 SIPAMAN Kabupaten Karanganyar.`.
- After saving a System Settings group, the web page must return to that same group/section instead of jumping to the top.

Current request already blocks keys containing:

- `password`
- `secret`
- `token`
- `api_key`
- `private_key`

Current UI:

- System Settings are grouped as "Identitas Website", "Navigasi Website", "Kontak & Footer", "Tampilan Data", and "Pengaturan Sistem".
- Technical keys may appear only as small helper text, not as the main admin label.
- `deskripsi` is shown as compact helper text below inputs, not as a primary editable field or large box.
- The web UI saves settings per group and uses anchors/return targets to keep the user near the group that was saved.
- Update validation prohibits editing `deskripsi` from the settings form/API request.

---

## 17. Audit trail and activity logs

Current files:

- `app/Models/AuditTrail.php`
- `app/Models/ActivityLog.php`
- `app/Traits/LogsAuditTrail.php`
- `Web/Admin/LogController.php`
- `Web/SuperAdmin/AuditTrailController.php`
- `Api/SuperAdmin/AuditTrailController.php`
- `resources/views/admin/logs/index.blade.php`
- `resources/views/super-admin/audit-trails/index.blade.php`

Current trait methods:

- `logAudit()` writes create/update/delete/verify/import style changes to `audit_trails`.
- `logActivity()` writes login/logout/activity style events to `activity_logs`.

Rules:

- AuditTrail = data change history.
- ActivityLog = access/activity history.
- Do not log raw passwords, tokens, secrets, session values, or full uploaded file contents.
- Keep logs useful but not noisy.

Current implementation:

- API and web login/logout create ActivityLog entries.
- ActivityLog records user, activity text, IP address, and user agent when available.

---

## 18. Sidebar and menu rules

Current sidebar source:

- `resources/views/partials/admin/sidebar.blade.php`

Rules:

- Sidebar visibility must match route middleware.
- Do not show super-admin-only items to normal admin.
- Do not add a sidebar item without route/controller/view/middleware.
- Add menu groups by workflow, not by technical table names.
- Use labels understandable by non-IT government staff.

When adding a new sidebar item, update/check:

1. route name exists,
2. middleware matches intended role,
3. controller exists,
4. view exists,
5. active route pattern works,
6. icon is clear,
7. empty state exists,
8. audit logging is added when data changes.

Recommended future groups:

- Utama: Dashboard
- Data PIRT: Produk, Jenis Barang, Verifikasi, Gambar Produk
- Pengguna: Akun Pelaku Usaha
- Konten Website: Landing Page
- Monitoring: Log Aktivitas, Riwayat Import, later Pengingat PIRT if implemented
- Super Admin: Kelola Admin, System Settings, Audit Trail

---

## 19. File upload and storage rules

Product images:

- Store files on Laravel `public` disk.
- Render with accessor/helper or `Storage::url()`.
- Validate image type and size.
- Current max image size is 2 MB in existing requests.
- Product image operations should be centralized in `ProductImageService`.
- Target rule: one product should have one image; upload replaces old image.

Landing page images:

- Use `LandingPageContentService`.
- Store paths, not absolute machine paths.
- Delete old image when replaced/removed.
- Validate image type and size.
- Only the `hero` / "Banner Utama" landing section has an editable image.

Website logo:

- Use `SystemSettingService`.
- Store the uploaded logo on the Laravel `public` disk as a relative path in `site_logo_path`.
- Validate JPG/PNG/WebP max 2 MB and recommend a 1:1 logo.
- Replacing the logo deletes the previous managed upload from storage.

Spreadsheet imports:

- Keep validation in FormRequests/custom rule.
- Keep reader resolution and template validation in support/service classes.
- Keep row parsing in import classes.
- Keep import orchestration in `ProductImportService`.
- Keep import failure summaries readable.

---

## 20. Database model map

### `roles`

Files:

- migration: `2024_01_01_000001_create_roles_table.php`
- model: `App\Models\Role`

Purpose:

- Stores role names: `user`, `admin`, `super_admin`.

### `users`

Files:

- migration: `2024_01_01_000002_create_users_table.php`
- model: `App\Models\User`

Purpose:

- Stores admins, super admins, and pelaku usaha accounts.
- Supports nullable email/password and unique nullable NIB in the base users migration.
- Supports unique nullable NIB.

Important helpers:

- `hasRole()`
- `isActive()`
- `needsPasswordSetup()`

Rules:

- Do not remove email globally because admin/super admin still use it.
- Do not create duplicate users for the same NIB.
- User role email should be null/not used as a user-facing identity.

### `kecamatans`

Files:

- migration: `2024_01_01_000003_create_kecamatans_table.php`
- model: `App\Models\Kecamatan`

Purpose:

- Stores district data for Karanganyar.

### `jenis_barangs`

Files:

- migration: `2024_01_01_000004_create_jenis_barangs_table.php`
- migration: `2026_06_02_000002_add_official_fields_to_jenis_barangs_table.php`
- model: `App\Models\JenisBarang`

Purpose:

- Stores simplified product type/category for filtering and display.

Current fields include:

- `nama_jenis`
- `slug`
- `deskripsi`
- `is_active`

### `jenis_barang_aliases`

Files:

- migration: `2026_05_29_000002_create_jenis_barang_aliases_table.php`
- model: `App\Models\JenisBarangAlias`

Purpose:

- Stores keywords/aliases for classifying raw imported `jenis_pangan` into simplified `jenis_barangs`.

### `produks`

Files:

- migration: `2024_01_01_000005_create_produks_table.php`
- model: `App\Models\Produk`

Purpose:

- Main PIRT product table.
- Public catalog shows verified products only.
- Unique business key is `no_sppirt`.

### `gambar_produks`

Files:

- migration: `2024_01_01_000006_create_gambar_produks_table.php`
- migration: current `gambar_produks` schema already enforces one image per product with `gambar_produks_produk_id_unique`
- model: `App\Models\GambarProduk`

Purpose:

- Stores product image path and primary flag.

Rule:

- One product can have at most one image row; upload replaces the previous file and record.

### `verifikasi_produks`

Files:

- migration: `2024_01_01_000007_create_verifikasi_produks_table.php`
- model: `App\Models\VerifikasiProduk`

Purpose:

- Stores verification checklist and status.

### `import_logs`

Files:

- migration: `2024_01_01_000008_create_import_logs_table.php`
- model: `App\Models\ImportLog`

Purpose:

- Records import file name, type, row counts, success/failure counts, description, and importing user.

### `audit_trails`

Files:

- migration: `2024_01_01_000009_create_audit_trails_table.php`
- model: `App\Models\AuditTrail`

Purpose:

- Records create/update/delete/verify/import data changes.

### `activity_logs`

Files:

- migration: `2024_01_01_000010_create_activity_logs_table.php`
- model: `App\Models\ActivityLog`

Purpose:

- Records user activity such as login/logout.

### `landing_page_contents`

Files:

- migration: `2024_01_01_000011_create_landing_page_contents_table.php`
- model: `App\Models\LandingPageContent`

Purpose:

- Stores fixed public landing page section content.

### `pirt_commitment_statuses`

Files:

- migration: `2024_01_01_000012_create_pirt_commitment_statuses_table.php`
- model: `App\Models\PirtCommitmentStatus`

Purpose:

- Stores Status Pemenuhan Komitmen import results.
- May link to `produks`.

### `system_settings`

Files:

- migration: `2026_05_12_065652_create_system_settings_table.php`
- model: `App\Models\SystemSetting`

Purpose:

- Stores global non-secret app configuration.

---

## 21. Service and support map

### `DashboardStatisticService`

Purpose:

- Provides admin and super admin dashboard statistics.

Methods:

- `adminStats()`
- `superAdminStats()`

Use this for dashboard counts instead of duplicating queries.

### `ProductImportService`

Purpose:

- Shared import orchestrator.
- Resolves reader type.
- Validates spreadsheet template.
- Runs Laravel Excel import in a DB transaction.
- Creates `ImportLog`.
- Returns import summary.

Methods:

- `importRekapPirt(UploadedFile $file)`
- `importCommitmentStatus(UploadedFile $file)`

### `ProductVerificationQueryService`

Purpose:

- Builds read-only verification tab queries and tracking filters for the admin verification page.
- Keeps verification list/filter logic out of the controller.

Methods:

- `resolveTab(array $filters)`
- `resolveTrackingFilters(array $filters, string $tab)`
- `query(string $tab, array $trackingFilters)`
- `stats()`

Current rule: verification status changes must come from Status Pemenuhan Komitmen spreadsheet import, not manual web/API actions.

### `ProductImageService`

Purpose:

- Product image upload/delete logic.

Current methods:

- `replaceOne(Produk $produk, UploadedFile $file)`
- `delete(GambarProduk $gambarProduk)`

Use `replaceOne()` for all admin/user web/API upload flows.

### `JenisBarangManagementService`

Purpose:

- Stores/updates jenis barang metadata.
- Synchronizes admin-managed aliases/keywords for classification.

### `LandingPageContentService`

Purpose:

- Updates landing page content.
- Handles image replacement/removal.
- Deletes old landing page images.

### `ProductTypeClassifier`

Purpose:

- Classifies imported raw food type/category into simplified `jenis_barangs`.
- Uses database aliases first, built-in rules second, fallback category last.

### `SystemSettings`

Purpose:

- Cached helper for public/global settings.
- Uses `Cache::rememberForever`.
- Must be forgotten after updates.

---

## 22. FormRequest map

Admin requests:

- `ImportProductRequest`
- `ImportCommitmentStatusRequest`
- `ImportProductRequest`
- `UpdateProductSupportRequest`
- `StoreProductImageRequest`
- `StoreJenisBarangRequest`
- `UpdateJenisBarangRequest`
- `UpdatePelakuUsahaAccountRequest`
- `ImportCommitmentStatusRequest`
- `UpdateLandingPageRequest`

User requests:

- `UpdateProductSupportRequest`

Super admin requests:

- `StoreUserRequest`
- `UpdateUserRequest`
- `UpdateSystemSettingRequest`

Rules:

- Prefer FormRequests over inline controller validation.
- If web and API share input shape, reuse a FormRequest where possible.
- Add Indonesian validation messages when users/admins will see them.
- Do not let API accept fields that web UI forbids for the same role.

Current implementation:

- User product update web/API share `UpdateProductSupportRequest`.
- User/admin image uploads use `StoreProductImageRequest` and `ProductImageService`.

---

## 23. API rules

Rules:

- Return JSON consistently.
- Do not return Blade redirects from API controllers.
- Use Sanctum for protected API routes.
- Use role middleware for protected API groups.
- Keep public APIs from exposing unverified products.
- Do not expose sensitive fields such as password, token, secrets, or unnecessary email for role `user`.
- If web and API represent the same feature, business rules must match.

Current implementation:

- API user profile/update prohibits email/name changes for role `user`.
- API user product update only allows `harga` and `deskripsi`.
- API user product image upload replaces the single active image.
- API auth `formatUser()` hides email for role `user`.

---

## 24. Legacy or possibly unused files

Current audit note: legacy admin/category/UMKM/user-management files from older snapshots are not present in the current filesystem and are not registered in `routes/web.php` or `routes/api.php`. Check references before editing or deleting any file that looks unused.

Known current candidate for review:

- `resources/views/components/modal-delete.blade.php` currently has no active reference, but should only be removed after a final reference check in the same cleanup task.

Rules:

- Do not connect legacy files to routes without reviewing their validation, authorization, and business logic.
- Do not delete them unless cleanup is explicitly requested and all references are verified.

---

## 25. Branding rules

Visible branding is SIPAMAN / Sistem Informasi Pangan Aman.

Safe to update:

- page titles,
- sidebar header,
- login page text,
- navbar/footer text,
- landing page seed content,
- visible Blade labels,
- config display name if explicitly requested.

Do not automatically rename:

- route names,
- database tables,
- columns,
- PHP namespaces,
- model classes,
- controller classes,
- migration filenames,
- storage paths.

Technical renames can break route references, model binding, migrations, and existing data.

---

## 27. Coding style rules

### PHP/Laravel

- Follow existing Laravel conventions.
- Use typed return values where existing code does.
- Prefer dependency injection for services.
- Prefer route model binding when safe and clear.
- Use Eloquent relationships and scopes.
- Use `DB::transaction()` for multi-model writes.
- Use `abort_if()` / `abort_unless()` for clear authorization or state failures when appropriate.
- Extract repeated logic into services/support classes.
- Do not create large static utility classes unless truly justified.

### Blade/Tailwind

- Reuse layouts, components, and partials.
- Keep labels/messages in Indonesian.
- Preserve responsive behavior.
- Keep admin UI simple for non-technical government staff.
- Avoid business logic in Blade beyond display conditions.
- Do not duplicate whole forms when a partial already exists.

### Database/migrations

- For existing deployed projects, create new migrations for schema changes.
- If the user explicitly says the database will be migrated fresh and asks to merge schema changes into existing migrations, editing existing migrations may be acceptable.
- Always update dependent files together.

### Security

- Do not store secrets in database settings.
- Do not log credentials or tokens.
- Do not expose super-admin routes to normal admin.
- Do not rely on hidden UI buttons as the only protection.
- Keep validation and authorization server-side.

---

## 28. Common change playbooks

### 28.1 Changing product filters

Check/update:

1. public web controller,
2. public Blade filter form,
3. public API controller,
4. admin product controller,
5. admin product Blade filter form,
6. `Produk` scopes,
7. query string preservation,
8. empty states.

### 28.2 Changing jenis barang classification

Check/update:

1. `ProductTypeClassifier`,
2. `JenisBarang` model,
3. `JenisBarangAlias` model,
4. migrations,
5. seeders,
6. `ProdukImport`,
7. admin jenis barang controller/view,
8. reclassification action/command if added,
9. audit trail.

### 28.3 Changing user/pelaku usaha profile rules

Check/update:

1. web routes,
2. web `AccountController`,
3. user settings Blade,
4. API `AuthController`,
5. API responses,
6. policies/middleware if needed,
7. validation messages,
8. seeders.

### 28.4 Changing product image behavior

Check/update:

1. migration/data cleanup,
2. `GambarProduk` model,
3. `ProductImageService`,
4. admin web image controller,
5. admin API image controller,
6. user web image controller,
7. user API image controller,
8. `StoreProductImageRequest`,
9. admin product image views,
10. user product image views,
11. public image display,
12. storage file deletion.

### 28.5 Adding admin pelaku usaha management

Check/update:

1. route under `/panel` for shared admin/super-admin operations,
2. middleware `role:admin,super_admin`,
3. controller under `Web/Admin`,
4. FormRequest for user role only,
5. view under `resources/views/admin`,
6. sidebar item,
7. policy or controller guard preventing admin/super admin modifications,
8. audit trail,
9. API equivalent if required.

### 28.6 Changing landing page behavior

Check/update:

1. `landing_page_contents` migration,
2. `LandingPageContent` model,
3. `UpdateLandingPageRequest`,
4. `LandingPageContentService`,
5. web admin controller,
6. API admin controller,
7. public controller/view,
8. admin Blade labels,
9. seeders,
10. image storage replacement.


---

## 29. Manual testing checklist

### Auth

- Admin login with email.
- Super admin login with email.
- User login with NIB.
- Null-password user is blocked with clear message.
- Inactive/locked user is blocked.
- Logout works.
- Redirect path matches role.
- Web and API login messages are not misleading.

### Public website

- Home loads.
- Product catalog only shows verified products.
- Product detail blocks unverified products.
- Search works.
- Kecamatan filter works.
- Jenis Barang filter works after implemented.
- Missing images do not break layout.

### Admin

- Dashboard loads.
- Product list/search/filter works.
- Product create/update/delete works according to admin rules.
- Rekap PIRT import works.
- Status Pemenuhan Komitmen import works.
- Verification tabs and read-only detail work; status changes are import-only.
- Jenis Barang CRUD works.
- Alias/reclassify works after implemented.
- Product image replace works only for verified products.
- Landing page editor works.
- Activity logs and import logs load.

### Super admin

- Normal admin cannot access `/super-admin/*`.
- Super admin can create admin accounts.
- Super admin can update admin credentials/status.
- Super admin cannot edit/delete own account from user management.
- Super admin account is protected from regular management actions.
- System settings update clears cache.
- Audit trail page loads.

### User / pelaku usaha

- User dashboard loads.
- User only sees allowed product action(s).
- User cannot delete products.
- User cannot edit official PIRT data.
- User cannot edit name/email after target rule is implemented.
- User cannot edit `nama_toko` after target rule is implemented.
- User can edit only allowed support fields.
- User image upload follows single-image replacement after target rule is implemented.

### Import

- Valid Rekap PIRT import succeeds.
- Invalid Rekap PIRT rows show friendly row errors.
- Existing verified product is not reset by Rekap PIRT re-import.
- Valid Status Pemenuhan Komitmen import succeeds.
- Unknown No SPPIRT produces row failure.
- ImportLog is created.
- Verified products appear publicly.
- User account creation from NIB does not create duplicates.

### UI build

- Admin layout renders.
- Sidebar active states work.
- Public layout renders.
- Responsive layout is not broken.
- Vite build succeeds.

---

## 30. Known gaps from current code snapshot

The previously listed gaps for jenis barang filters, alias management, reclassification, user dashboard/profile restrictions, single-image product uploads, admin image management, admin pelaku usaha management, web activity logging, landing page labels, and read-only system setting descriptions have been implemented.

Remaining watch items:

1. Legacy/possibly unused files listed in section 24 still exist and should not be wired without review.
2. Broad admin-scope user management API is not registered in current API routes.

When fixing future gaps, update this file again if the code structure or rules change.

---

## 31. Agent final answer requirements

When a coding agent finishes a task, the answer must include:

1. What changed.
2. Files changed.
3. Why each change was needed.
4. Commands/tests run.
5. Any commands/tests not run and why.
6. Migration/seed impact.
7. Manual verification steps.
8. Remaining risks/follow-up.

Do not claim a command passed unless it was actually run.
Do not claim a UI was checked unless it was actually opened or explicitly reasoned from code with that limitation stated.
Do not hide failures.

---

## 32. Current code snapshot coverage appendix
This appendix was generated from the current uploaded code snapshot. It helps future agents verify that they have inspected the right files. It is an inventory, not a promise that every listed file is active in routes. Files marked elsewhere as legacy/possibly unused still need reference checks before editing or deleting.

Coverage note: the functional sections above define the main behavior and responsibilities. This appendix closes the remaining gap by listing all current `app/`, `database/`, and `resources/views/` files visible in the uploaded snapshot. Default Laravel framework infrastructure files are listed but should normally be edited only when a task explicitly touches framework behavior.

### Console commands

- `app/Console/Commands/BackfillProdukJenisBarang.php`
- `app/Console/Commands/BackfillProdukKecamatan.php`
- `app/Console/Commands/SendPirtExpiryNotifications.php`

### HTTP Controllers

- `app/Http/Controllers/Api/Admin/LandingPageController.php`
- `app/Http/Controllers/Api/Admin/ProductController.php`
- `app/Http/Controllers/Api/Admin/ProductImageController.php`
- `app/Http/Controllers/Api/Admin/ProductImportController.php`
- `app/Http/Controllers/Api/Admin/ProductVerificationController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/ProdukController.php`
- `app/Http/Controllers/Api/SuperAdmin/AuditTrailController.php`
- `app/Http/Controllers/Api/SuperAdmin/SystemSettingController.php`
- `app/Http/Controllers/Api/SuperAdmin/UserManagementController.php`
- `app/Http/Controllers/Api/User/DashboardController.php`
- `app/Http/Controllers/Api/User/ProductController.php`
- `app/Http/Controllers/Api/User/ProductImageController.php`
- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/Web/Admin/DashboardController.php`
- `app/Http/Controllers/Web/Admin/ImportLogController.php`
- `app/Http/Controllers/Web/Admin/JenisBarangController.php`
- `app/Http/Controllers/Web/Admin/LandingPageController.php`
- `app/Http/Controllers/Web/Admin/LogController.php`
- `app/Http/Controllers/Web/Admin/PelakuUsahaAccountController.php`
- `app/Http/Controllers/Web/Admin/ProductController.php`
- `app/Http/Controllers/Web/Admin/ProductImageManagementController.php`
- `app/Http/Controllers/Web/Admin/ProductImportController.php`
- `app/Http/Controllers/Web/Admin/ProductVerificationController.php`
- `app/Http/Controllers/Web/Auth/AuthenticatedSessionController.php`
- `app/Http/Controllers/Web/Public/HomeController.php`
- `app/Http/Controllers/Web/Public/ProductController.php`
- `app/Http/Controllers/Web/Public/UmkmController.php`
- `app/Http/Controllers/Web/SuperAdmin/AuditTrailController.php`
- `app/Http/Controllers/Web/SuperAdmin/SystemSettingController.php`
- `app/Http/Controllers/Web/SuperAdmin/UserManagementController.php`
- `app/Http/Controllers/Web/User/AccountController.php`
- `app/Http/Controllers/Web/User/DashboardController.php`
- `app/Http/Controllers/Web/User/ProductSettingController.php`

### Form Requests

- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/Auth/UpdateProfileRequest.php`
- `app/Http/Requests/Admin/Concerns/HasImportSpreadsheetRules.php`
- `app/Http/Requests/Admin/Concerns/ValidatesJenisBarangFields.php`
- `app/Http/Requests/Admin/ImportCommitmentStatusRequest.php`
- `app/Http/Requests/Admin/ImportProductRequest.php`
- `app/Http/Requests/Admin/StoreJenisBarangRequest.php`
- `app/Http/Requests/Admin/StoreProductImageRequest.php`
- `app/Http/Requests/Admin/UpdateJenisBarangRequest.php`
- `app/Http/Requests/Admin/UpdateLandingPageRequest.php`
- `app/Http/Requests/Admin/UpdatePelakuUsahaAccountRequest.php`
- `app/Http/Requests/SuperAdmin/StoreUserRequest.php`
- `app/Http/Requests/SuperAdmin/UpdateSystemSettingGroupRequest.php`
- `app/Http/Requests/SuperAdmin/UpdateSystemSettingRequest.php`
- `app/Http/Requests/SuperAdmin/UpdateUserRequest.php`
- `app/Http/Requests/User/UpdateProductSupportRequest.php`
- `app/Http/Requests/User/UpdateUserPasswordRequest.php`

### Jobs

- `app/Jobs/SendPirtExpiryWarningWhatsApp.php`

### Middleware

- `app/Http/Middleware/Authenticate.php`
- `app/Http/Middleware/CheckRole.php`
- `app/Http/Middleware/EncryptCookies.php`
- `app/Http/Middleware/PreventRequestsDuringMaintenance.php`
- `app/Http/Middleware/RedirectIfAuthenticated.php`
- `app/Http/Middleware/TrimStrings.php`
- `app/Http/Middleware/TrustHosts.php`
- `app/Http/Middleware/TrustProxies.php`
- `app/Http/Middleware/ValidateSignature.php`
- `app/Http/Middleware/VerifyCsrfToken.php`

### Models

- `app/Models/ActivityLog.php`
- `app/Models/AuditTrail.php`
- `app/Models/GambarProduk.php`
- `app/Models/ImportLog.php`
- `app/Models/JenisBarang.php`
- `app/Models/JenisBarangAlias.php`
- `app/Models/Kecamatan.php`
- `app/Models/LandingPageContent.php`
- `app/Models/PirtCommitmentStatus.php`
- `app/Models/PirtExpiryNotificationLog.php`
- `app/Models/Produk.php`
- `app/Models/Role.php`
- `app/Models/SystemSetting.php`
- `app/Models/User.php`
- `app/Models/VerifikasiProduk.php`

### Services

- `app/Services/DashboardStatisticService.php`
- `app/Services/AuthenticationService.php`
- `app/Services/JenisBarangManagementService.php`
- `app/Services/LandingPageContentService.php`
- `app/Services/PhoneNumberNormalizer.php`
- `app/Services/PirtExpiryMessageRenderer.php`
- `app/Services/PirtExpiryNotificationService.php`
- `app/Services/PublicProductCatalogService.php`
- `app/Services/ProductImageService.php`
- `app/Services/ProductImportService.php`
- `app/Services/ProductVerificationQueryService.php`
- `app/Services/StarSenderClient.php`
- `app/Services/SystemSettingService.php`

### Support classes

- `app/Support/Imports/SpreadsheetFileResolver.php`
- `app/Support/Imports/SpreadsheetTemplateValidator.php`
- `app/Support/KecamatanResolver.php`
- `app/Support/ProductTypeClassifier.php`
- `app/Support/SystemSettingCatalog.php`
- `app/Support/SystemSettings.php`

### Import classes

- `app/Imports/PirtCommitmentStatusImport.php`
- `app/Imports/ProdukImport.php`

### Policies

- `app/Policies/AuditTrailPolicy.php`
- `app/Policies/ProdukPolicy.php`
- `app/Policies/SystemSettingPolicy.php`
- `app/Policies/UserPolicy.php`

### Providers

- `app/Providers/AppServiceProvider.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Providers/BroadcastServiceProvider.php`
- `app/Providers/EventServiceProvider.php`
- `app/Providers/RouteServiceProvider.php`

### Traits

- `app/Traits/LogsAuditTrail.php`

### Rules

- `app/Rules/ImportSpreadsheetFile.php`

### Migrations

- `database/migrations/0001_01_01_000001_create_cache_table.php`
- `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `database/migrations/2024_01_01_000001_create_roles_table.php`
- `database/migrations/2024_01_01_000002_create_users_table.php`
- `database/migrations/2024_01_01_000003_create_kecamatans_table.php`
- `database/migrations/2024_01_01_000004_create_jenis_barangs_table.php`
- `database/migrations/2024_01_01_000005_create_produks_table.php`
- `database/migrations/2024_01_01_000006_create_gambar_produks_table.php`
- `database/migrations/2024_01_01_000007_create_verifikasi_produks_table.php`
- `database/migrations/2024_01_01_000008_create_import_logs_table.php`
- `database/migrations/2024_01_01_000009_create_audit_trails_table.php`
- `database/migrations/2024_01_01_000010_create_activity_logs_table.php`
- `database/migrations/2024_01_01_000011_create_landing_page_contents_table.php`
- `database/migrations/2024_01_01_000012_create_pirt_commitment_statuses_table.php`
- `database/migrations/2024_01_01_000013_create_personal_access_tokens_table.php`
- `database/migrations/2026_05_12_065652_create_system_settings_table.php`
- `database/migrations/2026_05_29_000002_create_jenis_barang_aliases_table.php`
- `database/migrations/2026_06_02_000001_create_pirt_expiry_notification_logs_table.php`
- `database/migrations/2026_06_02_000002_add_official_fields_to_jenis_barangs_table.php`

### Seeders

- `database/seeders/DatabaseSeeder.php`
- `database/seeders/data/pirt_jenis_pangan.php`
- `database/seeders/JenisBarangSeeder.php`
- `database/seeders/KecamatanSeeder.php`
- `database/seeders/LandingPageContentSeeder.php`
- `database/seeders/RoleSeeder.php`
- `database/seeders/SystemSettingSeeder.php`
- `database/seeders/UserSeeder.php`

### Factories

- `database/factories/UserFactory.php`

### Blade views

- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/import-logs/index.blade.php`
- `resources/views/admin/jenis-barang/_form.blade.php`
- `resources/views/admin/jenis-barang/create.blade.php`
- `resources/views/admin/jenis-barang/edit.blade.php`
- `resources/views/admin/jenis-barang/index.blade.php`
- `resources/views/admin/jenis-barang/review.blade.php`
- `resources/views/admin/landing-page/index.blade.php`
- `resources/views/admin/landing-page/edit.blade.php`
- `resources/views/admin/logs/index.blade.php`
- `resources/views/admin/pelaku-usaha/edit.blade.php`
- `resources/views/admin/pelaku-usaha/index.blade.php`
- `resources/views/admin/product-images/index.blade.php`
- `resources/views/admin/products/index.blade.php`
- `resources/views/admin/products/show.blade.php`
- `resources/views/admin/verifications/show.blade.php`
- `resources/views/admin/verifications/index.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/components/alert.blade.php`
- `resources/views/components/badge-status.blade.php`
- `resources/views/components/modal-delete.blade.php`
- `resources/views/components/product-card.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/layouts/auth.blade.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/partials/admin/breadcrumb.blade.php`
- `resources/views/partials/admin/sidebar.blade.php`
- `resources/views/partials/admin/topbar.blade.php`
- `resources/views/partials/public/footer.blade.php`
- `resources/views/partials/public/hero.blade.php`
- `resources/views/partials/public/navbar.blade.php`
- `resources/views/public/home.blade.php`
- `resources/views/public/products/index.blade.php`
- `resources/views/public/products/show.blade.php`
- `resources/views/public/umkm/index.blade.php`
- `resources/views/public/umkm/show.blade.php`
- `resources/views/super-admin/audit-trails/index.blade.php`
- `resources/views/super-admin/settings/index.blade.php`
- `resources/views/super-admin/users/_form.blade.php`
- `resources/views/super-admin/users/create.blade.php`
- `resources/views/super-admin/users/edit.blade.php`
- `resources/views/super-admin/users/index.blade.php`
- `resources/views/user/dashboard.blade.php`
- `resources/views/user/products/setting-edit.blade.php`
- `resources/views/user/products/setting.blade.php`
- `resources/views/user/settings/index.blade.php`

### Laravel bootstrap/infrastructure

- `app/Console/Commands/BackfillProdukJenisBarang.php`
- `app/Console/Commands/BackfillProdukKecamatan.php`
- `app/Console/Commands/SendPirtExpiryNotifications.php`
- `app/Console/Kernel.php`
- `app/Exceptions/Handler.php`
- `app/Http/Kernel.php`

### Other

- `database/.gitignore`

---

## Appendix A - Verified file inventory from current uploaded snapshot

This appendix is a maintained high-level inventory for the current working tree under `app/`, `routes/`, `resources/`, `database/`, and `composer.json`. Use it to avoid guessing whether a file exists, and still verify with `rg --files` before cleanup tasks.

- `app/Console/Kernel.php`
- `app/Exceptions/Handler.php`
- `app/Http/Controllers/Api/Admin/LandingPageController.php`
- `app/Http/Controllers/Api/Admin/ProductController.php`
- `app/Http/Controllers/Api/Admin/ProductImageController.php`
- `app/Http/Controllers/Api/Admin/ProductImportController.php`
- `app/Http/Controllers/Api/Admin/ProductVerificationController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/ProdukController.php`
- `app/Http/Controllers/Api/SuperAdmin/AuditTrailController.php`
- `app/Http/Controllers/Api/SuperAdmin/SystemSettingController.php`
- `app/Http/Controllers/Api/SuperAdmin/UserManagementController.php`
- `app/Http/Controllers/Api/User/DashboardController.php`
- `app/Http/Controllers/Api/User/ProductController.php`
- `app/Http/Controllers/Api/User/ProductImageController.php`
- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/Web/Admin/DashboardController.php`
- `app/Http/Controllers/Web/Admin/ImportLogController.php`
- `app/Http/Controllers/Web/Admin/JenisBarangController.php`
- `app/Http/Controllers/Web/Admin/LandingPageController.php`
- `app/Http/Controllers/Web/Admin/LogController.php`
- `app/Http/Controllers/Web/Admin/PelakuUsahaAccountController.php`
- `app/Http/Controllers/Web/Admin/ProductController.php`
- `app/Http/Controllers/Web/Admin/ProductImageManagementController.php`
- `app/Http/Controllers/Web/Admin/ProductImportController.php`
- `app/Http/Controllers/Web/Admin/ProductVerificationController.php`
- `app/Http/Controllers/Web/Auth/AuthenticatedSessionController.php`
- `app/Http/Controllers/Web/Public/HomeController.php`
- `app/Http/Controllers/Web/Public/ProductController.php`
- `app/Http/Controllers/Web/Public/UmkmController.php`
- `app/Http/Controllers/Web/SuperAdmin/AuditTrailController.php`
- `app/Http/Controllers/Web/SuperAdmin/SystemSettingController.php`
- `app/Http/Controllers/Web/SuperAdmin/UserManagementController.php`
- `app/Http/Controllers/Web/User/AccountController.php`
- `app/Http/Controllers/Web/User/DashboardController.php`
- `app/Http/Controllers/Web/User/ProductSettingController.php`
- `app/Http/Kernel.php`
- `app/Http/Middleware/Authenticate.php`
- `app/Http/Middleware/CheckRole.php`
- `app/Http/Middleware/EncryptCookies.php`
- `app/Http/Middleware/PreventRequestsDuringMaintenance.php`
- `app/Http/Middleware/RedirectIfAuthenticated.php`
- `app/Http/Middleware/TrimStrings.php`
- `app/Http/Middleware/TrustHosts.php`
- `app/Http/Middleware/TrustProxies.php`
- `app/Http/Middleware/ValidateSignature.php`
- `app/Http/Middleware/VerifyCsrfToken.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/Auth/UpdateProfileRequest.php`
- `app/Http/Requests/Admin/Concerns/HasImportSpreadsheetRules.php`
- `app/Http/Requests/Admin/Concerns/ValidatesJenisBarangFields.php`
- `app/Http/Requests/Admin/ImportCommitmentStatusRequest.php`
- `app/Http/Requests/Admin/ImportProductRequest.php`
- `app/Http/Requests/Admin/StoreJenisBarangRequest.php`
- `app/Http/Requests/Admin/StoreProductImageRequest.php`
- `app/Http/Requests/Admin/UpdateJenisBarangRequest.php`
- `app/Http/Requests/Admin/UpdateLandingPageRequest.php`
- `app/Http/Requests/Admin/UpdatePelakuUsahaAccountRequest.php`
- `app/Http/Requests/SuperAdmin/StoreUserRequest.php`
- `app/Http/Requests/SuperAdmin/UpdateSystemSettingGroupRequest.php`
- `app/Http/Requests/SuperAdmin/UpdateSystemSettingRequest.php`
- `app/Http/Requests/SuperAdmin/UpdateUserRequest.php`
- `app/Http/Requests/User/UpdateProductSupportRequest.php`
- `app/Http/Requests/User/UpdateUserPasswordRequest.php`
- `app/Imports/PirtCommitmentStatusImport.php`
- `app/Imports/ProdukImport.php`
- `app/Jobs/SendPirtExpiryWarningWhatsApp.php`
- `app/Models/ActivityLog.php`
- `app/Models/AuditTrail.php`
- `app/Models/GambarProduk.php`
- `app/Models/ImportLog.php`
- `app/Models/JenisBarang.php`
- `app/Models/JenisBarangAlias.php`
- `app/Models/Kecamatan.php`
- `app/Models/LandingPageContent.php`
- `app/Models/PirtCommitmentStatus.php`
- `app/Models/PirtExpiryNotificationLog.php`
- `app/Models/Produk.php`
- `app/Models/Role.php`
- `app/Models/SystemSetting.php`
- `app/Models/User.php`
- `app/Models/VerifikasiProduk.php`
- `app/Policies/AuditTrailPolicy.php`
- `app/Policies/ProdukPolicy.php`
- `app/Policies/SystemSettingPolicy.php`
- `app/Policies/UserPolicy.php`
- `app/Providers/AppServiceProvider.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Providers/BroadcastServiceProvider.php`
- `app/Providers/EventServiceProvider.php`
- `app/Providers/RouteServiceProvider.php`
- `app/Rules/ImportSpreadsheetFile.php`
- `app/Services/AuthenticationService.php`
- `app/Services/DashboardStatisticService.php`
- `app/Services/JenisBarangManagementService.php`
- `app/Services/LandingPageContentService.php`
- `app/Services/PhoneNumberNormalizer.php`
- `app/Services/PirtExpiryMessageRenderer.php`
- `app/Services/PirtExpiryNotificationService.php`
- `app/Services/PublicProductCatalogService.php`
- `app/Services/ProductImageService.php`
- `app/Services/ProductImportService.php`
- `app/Services/ProductVerificationQueryService.php`
- `app/Services/StarSenderClient.php`
- `app/Services/SystemSettingService.php`
- `app/Support/Imports/SpreadsheetFileResolver.php`
- `app/Support/Imports/SpreadsheetTemplateValidator.php`
- `app/Support/KecamatanResolver.php`
- `app/Support/ProductTypeClassifier.php`
- `app/Support/SystemSettingCatalog.php`
- `app/Support/SystemSettings.php`
- `app/Traits/LogsAuditTrail.php`
- `composer.json`
- `database/.gitignore`
- `database/factories/UserFactory.php`
- `database/migrations/0001_01_01_000001_create_cache_table.php`
- `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `database/migrations/2024_01_01_000001_create_roles_table.php`
- `database/migrations/2024_01_01_000002_create_users_table.php`
- `database/migrations/2024_01_01_000003_create_kecamatans_table.php`
- `database/migrations/2024_01_01_000004_create_jenis_barangs_table.php`
- `database/migrations/2024_01_01_000005_create_produks_table.php`
- `database/migrations/2024_01_01_000006_create_gambar_produks_table.php`
- `database/migrations/2024_01_01_000007_create_verifikasi_produks_table.php`
- `database/migrations/2024_01_01_000008_create_import_logs_table.php`
- `database/migrations/2024_01_01_000009_create_audit_trails_table.php`
- `database/migrations/2024_01_01_000010_create_activity_logs_table.php`
- `database/migrations/2024_01_01_000011_create_landing_page_contents_table.php`
- `database/migrations/2024_01_01_000012_create_pirt_commitment_statuses_table.php`
- `database/migrations/2024_01_01_000013_create_personal_access_tokens_table.php`
- `database/migrations/2026_05_12_065652_create_system_settings_table.php`
- `database/migrations/2026_05_29_000002_create_jenis_barang_aliases_table.php`
- `database/migrations/2026_06_02_000001_create_pirt_expiry_notification_logs_table.php`
- `database/migrations/2026_06_02_000002_add_official_fields_to_jenis_barangs_table.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/data/pirt_jenis_pangan.php`
- `database/seeders/JenisBarangSeeder.php`
- `database/seeders/KecamatanSeeder.php`
- `database/seeders/LandingPageContentSeeder.php`
- `database/seeders/RoleSeeder.php`
- `database/seeders/SystemSettingSeeder.php`
- `database/seeders/UserSeeder.php`
- `resources/css/app.css`
- `resources/js/app.js`
- `resources/js/bootstrap.js`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/import-logs/index.blade.php`
- `resources/views/admin/jenis-barang/create.blade.php`
- `resources/views/admin/jenis-barang/edit.blade.php`
- `resources/views/admin/jenis-barang/index.blade.php`
- `resources/views/admin/jenis-barang/review.blade.php`
- `resources/views/admin/jenis-barang/_form.blade.php`
- `resources/views/admin/landing-page/edit.blade.php`
- `resources/views/admin/landing-page/index.blade.php`
- `resources/views/admin/logs/index.blade.php`
- `resources/views/admin/pelaku-usaha/edit.blade.php`
- `resources/views/admin/pelaku-usaha/index.blade.php`
- `resources/views/admin/product-images/index.blade.php`
- `resources/views/admin/products/index.blade.php`
- `resources/views/admin/products/show.blade.php`
- `resources/views/admin/verifications/show.blade.php`
- `resources/views/admin/verifications/index.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/components/alert.blade.php`
- `resources/views/components/badge-status.blade.php`
- `resources/views/components/modal-delete.blade.php`
- `resources/views/components/product-card.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/layouts/auth.blade.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/partials/admin/breadcrumb.blade.php`
- `resources/views/partials/admin/sidebar.blade.php`
- `resources/views/partials/admin/topbar.blade.php`
- `resources/views/partials/public/footer.blade.php`
- `resources/views/partials/public/hero.blade.php`
- `resources/views/partials/public/navbar.blade.php`
- `resources/views/public/home.blade.php`
- `resources/views/public/products/index.blade.php`
- `resources/views/public/products/show.blade.php`
- `resources/views/public/umkm/index.blade.php`
- `resources/views/public/umkm/show.blade.php`
- `resources/views/super-admin/audit-trails/index.blade.php`
- `resources/views/super-admin/settings/index.blade.php`
- `resources/views/super-admin/users/_form.blade.php`
- `resources/views/super-admin/users/create.blade.php`
- `resources/views/super-admin/users/edit.blade.php`
- `resources/views/super-admin/users/index.blade.php`
- `resources/views/user/dashboard.blade.php`
- `resources/views/user/products/setting-edit.blade.php`
- `resources/views/user/products/setting.blade.php`
- `resources/views/user/settings/index.blade.php`
- `routes/api.php`
- `routes/channels.php`
- `routes/console.php`
- `routes/web.php`
