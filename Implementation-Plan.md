# Implementation Plan — Bebras IT Del Portal Rebuild

## 1. Project Overview

**Portal:** `https://bebras.del.ac.id`  
**Organization:** Bebras Biro Institut Teknologi Del (Gerakan PANDAI)  
**Project type:** Legacy static website modernization  
**Primary objective:** Rebuild the existing static website as a maintainable Laravel web application with public news publishing, administrator-managed website content, and secure certificate search and distribution.

### 1.1 Goals

1. Preserve the existing website's Indonesian content, branding, imagery, information architecture, and essential visual behavior.
2. Provide an authenticated administrative interface for managing news, website content, and certificates.
3. Allow students and teachers to search for certificates using their Bebras usernames without registering or logging in.
4. Support individual certificate uploads and validated bulk imports of PDF certificates with CSV metadata.
5. Provide a secure, auditable, and maintainable application suitable for production deployment.

### 1.2 Existing Website

The repository contains a static single-page website built with HTML, Bootstrap 5, jQuery, WOW.js, Owl Carousel, CounterUp, and other third-party libraries.

Existing sections include:

- **Hero:** Bebras Indonesia logo and IT Del mascot in its ulos variant.
- **About Us:** Apa Itu Bebras, Sejarah Bebras Biro IT Del (member since 2017), and Apa Itu Berpikir Komputasional.
- **Statistics:** 59 mitra and 3,151 participants in Bebras Challenge 2024.
- **Kegiatan:** Lokakarya and Tantangan Bebras.
- **Bebras Challenge:** Four categories:
  - SiKecil — SD kelas 1–3.
  - Siaga — SD kelas 4–6.
  - Penggalang — SMP kelas 7–9.
  - Penegak — SMA/SMK kelas 10–12.
- **Footer:** IT Del address, telephone, `del@bebras.ac.id`, and quick links.

Existing assets are stored in `img/`, `css/`, `js/`, and `lib/`.

**Preservation requirement:** Treat the existing repository as the authoritative source for the current website's content and assets. Do not invent replacement copy, substitute branding, remove sections, or redesign the site unnecessarily. Audit the original HTML, stylesheets, scripts, and image references before implementing the Laravel views.

The statistics above are existing website values, not automatically current statistics. Preserve their original context and year unless the administrator explicitly updates them.

### 1.3 Scope Boundaries

**Included in this phase:**

- Public landing page and informational sections.
- Editable website content and statistics.
- News listing, publication workflow, and article details.
- Public certificate search and private PDF downloads.
- Individual certificate management.
- CSV-and-ZIP bulk certificate imports.
- Administrator authentication and authorization.
- Validation, automated tests, deployment documentation, and operational safeguards.

**Not included:**

- Public registration or student accounts.
- Email notifications to certificate recipients.
- Online Bebras Challenge delivery or integration.
- Multilingual switching.
- Automated certificate generation or PDF modification.
- A separate public API or mobile application.

---

## 2. Technology Stack and Compatibility

| Layer | Technology |
|---|---|
| Backend | Laravel 13 |
| Runtime | PHP 8.3+; prefer a currently supported PHP release compatible with all dependencies |
| Database | MySQL 8.x |
| Templates | Laravel Blade |
| Public frontend | Existing Bootstrap 5 and jQuery assets, integrated through Laravel Vite where practical |
| Admin panel | Filament PHP, using a release verified to support Laravel 13 |
| Authentication | Filament panel authentication; administrator accounts only |
| Database access | Laravel Eloquent ORM |
| File storage | Laravel private/local disk for certificates; public disk for intentionally public news images |
| Queue | Laravel database queue initially; a dedicated worker in production |
| Testing | PHPUnit or Pest, following the installed Laravel project's conventions |
| Asset build | Vite and npm |
| Version control | Git |

### 2.1 Dependency Selection

Do not assume that any arbitrary Filament 3 release works with Laravel 13. Resolve and verify the complete dependency graph before implementing the admin panel.

1. Create a clean Laravel 13 application.
2. Select a compatible, maintained Filament release.
3. Verify PHP, Laravel, Livewire, Filament, and third-party Filament plugin compatibility.
4. Pin dependencies through `composer.lock`.
5. Run the application's automated tests after dependency installation.

Use Tailwind CSS only if it offers a clear benefit. The existing website uses Bootstrap 5, so Bootstrap should remain the primary public-site styling framework during this migration. Filament supplies its own administrative interface styling.

Avoid introducing Tailwind solely to satisfy the initial technology proposal; using Bootstrap and Tailwind together without a deliberate separation creates unnecessary CSS conflicts and maintenance overhead.

---

## 3. System Actors and Permissions

| Actor | Authentication | Permissions |
|---|---|---|
| Administrator | Required | Manage website content, news, certificates, and bulk-import jobs |
| Guest | Not required | View the website, browse published news, search certificates, and download matching PDFs |

### 3.1 Administrator Access

- The administrative panel is available at `/admin`.
- Only explicitly authorized administrator accounts may access it.
- Public registration is disabled.
- Administrator creation uses an interactive Artisan command or a controlled provisioning process.
- Production administrator credentials must not be committed to source control or embedded in seeders.
- Apply rate limiting to authentication and configure secure session cookies over HTTPS.

If additional staff roles are introduced later, implement explicit authorization policies rather than assuming every authenticated user is an administrator.

---

## 4. Data Model and Database Schema

Use InnoDB tables, appropriate foreign keys, indexed lookup columns, and Laravel-managed timestamps.

### 4.1 `users`

Use Laravel's standard `users` migration and authentication conventions.

Only administrator accounts are required. Add roles or permission packages only if the project needs multiple permission levels.

### 4.2 `news`

| Column | Type | Constraints / Purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED | Primary key |
| `title` | VARCHAR(255) | Required article title |
| `slug` | VARCHAR(255) | Required, unique |
| `excerpt` | VARCHAR(500), nullable | Optional editorial summary |
| `content` | LONGTEXT | Required article body |
| `image_path` | VARCHAR(255), nullable | Public cover-image storage path |
| `is_published` | BOOLEAN | Default `false` |
| `published_at` | TIMESTAMP, nullable | Publication date and time |
| `created_at` | TIMESTAMP | Laravel timestamp |
| `updated_at` | TIMESTAMP | Laravel timestamp |

**Publication rule:** An article is publicly visible only when `is_published = true` and `published_at` is not null and is less than or equal to the current application time.

An unpublished or future-dated article must not appear in public queries, the landing-page news section, search results, or direct article routes.

Additional rules:

- Generate a slug from the title but allow an administrator to edit it.
- Enforce slug uniqueness at the database level.
- Generate an excerpt from sanitized plain text when one is not supplied.
- Sanitize rich-text HTML before rendering it.
- Preserve the original image when editing an article without uploading a replacement.
- Delete an old cover image only after confirming that no other record uses it.

### 4.3 `site_settings`

The original plan allows administrators to edit the About Us section but does not define where this content is stored. Add a dedicated settings table.

| Column | Type | Constraints / Purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED | Primary key |
| `key` | VARCHAR(100) | Required, unique setting identifier |
| `value` | LONGTEXT, nullable | Text or JSON-encoded structured content |
| `created_at` | TIMESTAMP | Laravel timestamp |
| `updated_at` | TIMESTAMP | Laravel timestamp |

Use a predefined set of supported keys for:

- About Bebras content.
- History of Bebras Biro IT Del.
- Computational thinking explanation.
- Partner and participant statistics.
- Activity descriptions.
- Footer contact information and quick links, where appropriate.

Seed these settings from the existing website copy. Do not expose arbitrary configuration editing or permit administrators to modify executable code through the settings interface.

If preserving the current text without administrative editing is sufficient, these values may instead remain in Blade configuration or a dedicated content file. However, that alternative must be explicitly approved before omitting the settings resource.

### 4.4 `certificates`

| Column | Type | Constraints / Purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED | Primary key |
| `bebras_username` | VARCHAR(100) | Required canonical search key |
| `participant_name` | VARCHAR(255) | Required |
| `school_name` | VARCHAR(255), nullable | School or institution |
| `category` | VARCHAR(20) | SiKecil, Siaga, Penggalang, or Penegak |
| `award_type` | VARCHAR(20) | Participant or Winner |
| `year` | SMALLINT UNSIGNED | Challenge year |
| `file_path` | VARCHAR(500) | Required private storage path |
| `original_filename` | VARCHAR(255), nullable | Original uploaded filename for administrative reference |
| `file_size` | BIGINT UNSIGNED, nullable | File size in bytes |
| `file_sha256` | CHAR(64), nullable | SHA-256 checksum for integrity verification |
| `created_at` | TIMESTAMP | Laravel timestamp |
| `updated_at` | TIMESTAMP | Laravel timestamp |

Use application validation or PHP backed enums for category and award values. Validate that the year falls within an explicitly configured acceptable range.

**Username normalization contract:**

- Trim leading and trailing whitespace.
- Normalize usernames to lowercase when storing and searching, assuming Bebras usernames are case-insensitive.
- Do not remove or replace internal characters unless the official username rules explicitly permit it.
- Preserve the participant's original display name independently of the search key.

If Bebras usernames are actually case-sensitive, replace this policy with exact, case-sensitive matching and a corresponding database collation before implementation.

**Indexes:**

- Index `bebras_username`.
- Add a composite index on `(bebras_username, year)`.
- Consider an additional index on `(year, category)` for administrative filtering.
- Add a unique constraint on `(bebras_username, year, category, award_type)` only if the data owner confirms that this combination uniquely identifies a certificate.

The application must not silently overwrite an existing certificate when an imported row duplicates an existing record.

### 4.5 `certificate_import_batches`

Track every bulk upload independently so administrators can monitor progress and investigate failures.

| Column | Type | Purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED | Primary key |
| `uploaded_by` | BIGINT UNSIGNED, nullable | Administrator ID |
| `status` | VARCHAR(20) | Pending, processing, completed, completed_with_errors, or failed |
| `archive_path` | VARCHAR(500) | Private ZIP path |
| `csv_path` | VARCHAR(500) | Private CSV path |
| `total_rows` | INT UNSIGNED | CSV data rows |
| `processed_rows` | INT UNSIGNED | Rows examined |
| `successful_rows` | INT UNSIGNED | Certificates successfully imported |
| `failed_rows` | INT UNSIGNED | Rows rejected |
| `skipped_rows` | INT UNSIGNED | Duplicate or intentionally skipped rows |
| `error_report_path` | VARCHAR(500), nullable | Private error-report path |
| `started_at` | TIMESTAMP, nullable | Processing start |
| `completed_at` | TIMESTAMP, nullable | Processing completion |
| `created_at` | TIMESTAMP | Creation time |
| `updated_at` | TIMESTAMP | Last update |

Add the appropriate foreign key to `users` when the relationship is retained.

This table supports retry decisions, progress reporting, and downloadable error reports. Do not rely solely on transient browser notifications.

---

## 5. Functional Requirements

### 5.1 Administrative Panel (`/admin`)

#### Authentication

- Provide secure login and logout.
- Disable public registration.
- Restrict panel access to authorized administrators.
- Require CSRF protection and standard session security.
- Provide an administrator provisioning procedure.

#### News Management

Implement a `NewsResource` with:

- Paginated listing of title, slug, publication status, publication date, and creation date.
- Search by title and slug.
- Filters for draft, published, and scheduled articles.
- Create and edit forms.
- Rich-text article editor.
- Cover-image upload and preview.
- Automatic slug generation with manual editing.
- Publication toggle and date/time selection.
- Confirmation before deletion.
- Validation for required fields, unique slugs, image formats, and file size.

The publication workflow must support drafts, immediate publication, and scheduled publication through `published_at`.

#### Website Content Management

Implement a `SiteSettingResource` or dedicated settings page.

- Expose only predefined content fields.
- Preserve the original Indonesian text and formatting.
- Provide fields for the three About Us subsections, statistics, activities, and approved footer information.
- Validate numeric statistics as non-negative integers.
- Ensure the public landing page reads the saved values rather than duplicating hard-coded content.
- Require confirmation before changing historically significant statistics.

#### Certificate Management

Implement a `CertificateResource` with:

- Searchable table columns for username and participant name.
- Filters for school, category, award type, and year.
- Individual record creation and editing.
- PDF upload and replacement.
- Private download action for authorized administrators.
- Confirmation before deletion.
- Validation for category, award type, year, metadata, and PDF content.

Deleting or replacing a certificate must not leave unreferenced files behind. Perform database changes and file cleanup in a controlled sequence, with cleanup failures logged for recovery.

### 5.2 Bulk Certificate Import

Create a custom Filament bulk-upload action and a dedicated import workflow.

#### Upload inputs

1. A ZIP archive containing certificate PDFs.
2. A CSV file containing certificate metadata.

Define the CSV contract as UTF-8, comma-delimited, with a header row:

```csv
bebras_username,participant_name,school_name,category,award_type,year,file_name
smp_budi_123,Budi Santoso,SMP Contoh,Penggalang,Participant,2024,smp_budi_123.pdf
```

The example is illustrative. Confirm the actual naming convention and metadata format with the certificate data owner before implementation.

`file_name` is required because an archive filename may contain prefixes or suffixes that do not exactly match the participant's username.

#### Processing workflow

1. Validate that both files are present and within configured size limits.
2. Create a `certificate_import_batches` record.
3. Store the uploaded ZIP and CSV in private staging storage.
4. Dispatch `ProcessBulkCertificateUpload` to the queue.
5. Extract the archive into an isolated temporary directory.
6. Validate every CSV row and normalize the username.
7. Match each row to a PDF using the declared `file_name`.
8. Validate the PDF's actual content and reject invalid, missing, ambiguous, or duplicate filenames.
9. Copy validated files into private certificate storage using server-generated storage paths.
10. Insert valid certificate records while detecting duplicates.
11. Record each rejected or skipped row with a useful error message.
12. Update batch counters and status.
13. Generate a private CSV error report when necessary.
14. Remove temporary extracted files and staging artifacts according to the retention policy.
15. Notify the administrator through Filament and persist the final status in the admin interface.

#### Import behavior

- One invalid row must not automatically invalidate every valid row.
- Use transactions for individual record operations or appropriately sized groups, rather than one enormous transaction for an entire archive.
- Never overwrite an existing certificate implicitly.
- A repeated import must detect duplicate records and report them.
- If a file is copied successfully but its database insert fails, remove the unreferenced file or queue it for cleanup.
- If processing is interrupted, expose the batch as failed or interrupted and provide an explicit, safe retry operation.
- Do not claim successful completion until the database records, file storage, and final batch status have been reconciled.

#### Operational visibility

Provide a batch history page showing:

- Batch ID and uploader.
- Submission time.
- Current status.
- Total, successful, failed, and skipped rows.
- Start and completion times.
- Error report download.
- Retry action where retry is safe.

---

## 6. Public-Facing Interface

### 6.1 Landing Page (`/`)

Create `resources/views/public/index.blade.php`, extending the main layout.

Preserve the existing sections and copy:

1. Hero and logos.
2. About Bebras.
3. History of Bebras Biro IT Del.
4. Computational thinking explanation.
5. Partner and participant statistics.
6. Activities: Lokakarya and Tantangan Bebras.
7. Four Bebras Challenge category cards.
8. Latest news section.
9. Footer with existing contact details and links.

The latest news section displays up to three currently published articles in reverse chronological order.

Each card includes an optional cover image, title, short excerpt, publication date, and article link.

Preserve WOW.js animations, CounterUp effects, Owl Carousel behavior, and responsive layouts where they remain useful and compatible.

### 6.2 News Index (`/berita`)

- Display published articles only.
- Sort by `published_at` descending, with a deterministic secondary sort.
- Use server-side pagination, initially 9 or 12 articles per page.
- Show cover image, title, publication date, and a sanitized excerpt.
- Provide a clearly labeled "Baca Selengkapnya" link.
- Display an appropriate empty state when no articles are published.

### 6.3 News Detail (`/berita/{slug}`)

- Resolve the article by slug.
- Return HTTP 404 for missing, unpublished, or future-dated articles.
- Display title, publication date, cover image, and full article content.
- Sanitize rich-text HTML before rendering it.
- Populate the HTML `<title>` and meta description.
- Add canonical URLs and social-sharing metadata where appropriate.
- Preserve line breaks, links, lists, and images from the rich-text editor without permitting executable content.

### 6.4 Certificate Search (`/sertifikat`)

Provide a simple, accessible form containing one input: **Bebras Username**.

#### Search behavior

- Accept a username using POST.
- Normalize the input according to the documented username policy.
- Validate required input, length, and permitted characters.
- Apply rate limiting to reduce automated enumeration.
- Query only the required certificate metadata.
- Never expose internal storage paths or filesystem details.

#### Search results

If records are found, display:

- Participant name.
- School name, if available.
- Category.
- Award type.
- Challenge year.
- An "Unduh Sertifikat" action for each result.

If multiple years or certificate types exist, display all matching records in a table with clear year and category distinctions.

If no record is found, display:

> Sertifikat tidak ditemukan untuk username ini. Pastikan Anda memasukkan username yang sama persis dengan yang digunakan saat Bebras Challenge.

Provide an explicit search-again action.

Avoid exposing unrelated participant data, administrative metadata, checksums, or batch-import information.

### 6.5 Secure Certificate Download (`/sertifikat/unduh/{id}`)

- Resolve the requested certificate through its database record.
- Return 404 for nonexistent records.
- Resolve the storage path through Laravel's private filesystem disk.
- Verify that the referenced file exists and is a valid stored certificate.
- Return the PDF as a forced download with a safe filename, such as `{username}_{year}_sertifikat.pdf`.
- Set an appropriate PDF content type and attachment disposition.
- Add `X-Content-Type-Options: nosniff` and suitable private/no-store cache headers.
- Never accept a filesystem path from the request.
- Log relevant failures without logging sensitive document contents.

The download route is intentionally public because certificate recipients do not have accounts. Consequently, a certificate ID is not an authorization secret. Rate limiting and minimal metadata exposure are required, but sequential IDs alone must not be treated as a complete security control.

If certificate privacy requirements later demand stronger protection, introduce a recipient verification mechanism or expiring signed download links in a separate approved phase.

---

## 7. Routing Plan

| Method | Route | Handler | Purpose |
|---|---|---|---|
| GET | `/` | `PublicController@index` | Landing page |
| GET | `/berita` | `NewsController@index` | Published news list |
| GET | `/berita/{slug}` | `NewsController@show` | Published article detail |
| GET | `/sertifikat` | `CertificateController@search` | Search form |
| POST | `/sertifikat` | `CertificateController@search` | Search submission |
| GET | `/sertifikat/unduh/{certificate}` | `CertificateController@download` | Private PDF download |

Use explicit route names, for example:

- `home`
- `news.index`
- `news.show`
- `certificates.search`
- `certificates.download`

Define static and parameterized routes carefully to prevent route conflicts.

All public routes should use web middleware. Apply appropriate rate limits to certificate search, downloads, and login attempts. Admin routes must use Filament's authentication and authorization mechanisms.

---

## 8. Implementation Phases

### Phase 1 — Repository Audit and Environment

1. Inspect the repository structure, Git status, README, existing HTML, CSS, JavaScript, and assets.
2. Identify the current page sections, actual text, image references, external dependencies, and animation behavior.
3. Record the original visual baseline through desktop and mobile screenshots.
4. Verify the available PHP, Composer, Node.js, npm, MySQL, and required PHP extensions.
5. Create a Laravel 13 application in a clean directory or an approved project directory. Do not overwrite the existing site before completing the audit and establishing a rollback path.
6. Install a compatible Filament version and verify dependency resolution.
7. Configure the local environment and database.
8. Commit the initial working application and dependency lockfiles.

Example commands for a new, empty application directory:

```bash
composer create-project laravel/laravel . "^13.0"

composer require filament/filament:"<verified-compatible-version>" -W

php artisan filament:install --panels
```

Replace the Filament placeholder with the verified version constraint before execution.

Configure `.env` locally, including:

```dotenv
APP_NAME="Bebras IT Del"
APP_URL=https://bebras.del.ac.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bebras_portal
DB_USERNAME=your_local_user
DB_PASSWORD=your_local_password

QUEUE_CONNECTION=database
```

Use a private certificate disk configured explicitly in `config/filesystems.php`. Do not rely on the public `local` or `public` disk defaults for sensitive files.

### Phase 2 — Database, Models, and Seed Data

1. Generate migrations for `news`, `site_settings`, `certificates`, and `certificate_import_batches`.
2. Confirm indexes, foreign keys, nullable columns, and unique constraints.
3. Implement the `News`, `SiteSetting`, `Certificate`, and `CertificateImportBatch` models.
4. Define `$fillable` or explicit model assignment policies and appropriate `$casts`.
5. Add a publication scope that excludes drafts and future-dated content.
6. Add username normalization in a dedicated service or model-independent domain utility used consistently by manual uploads and imports.
7. Create factories for automated testing.
8. Seed the existing website copy and settings without introducing invented content.
9. Run migrations on a disposable development database and verify rollback behavior.
10. Add tests for publication rules, normalization, and duplicate detection.

Use deterministic seed data for development and testing. Do not seed production with example participants or fabricated certificate records.

### Phase 3 — Administrator Panel

1. Create the Filament panel configuration.
2. Configure administrator authentication and panel-access authorization.
3. Implement `NewsResource`.
4. Implement the settings interface.
5. Implement `CertificateResource`.
6. Add PDF and image upload validation.
7. Add private certificate download and controlled deletion workflows.
8. Implement `CertificateImportBatch` administration.
9. Add the bulk-upload form and job-dispatch workflow.
10. Add persistent batch status, row-level error reporting, and safe retries.
11. Verify all CRUD operations and authorization boundaries.
12. Create the initial production administrator through a secure provisioning process.

### Phase 4 — Bulk Import Pipeline

Implement bulk imports as a service-backed, queued workflow rather than placing all processing logic in a Filament action.

Recommended components:

- `BulkCertificateImportService`
- `ProcessBulkCertificateUpload`
- `CertificateImportBatch`
- A CSV parser or import service.
- A private staging-storage service.
- A private error-report generator.

The Filament action should validate the submission, store the source files, create the batch, and dispatch the job. The job should coordinate processing and update the batch status.

The pipeline must enforce the following safeguards:

- Restrict ZIP and CSV upload sizes.
- Limit the number of archive entries, individual uncompressed file size, and total extracted size.
- Reject absolute paths, traversal sequences, symbolic links, and unexpected archive entries.
- Extract only into a newly created isolated temporary directory.
- Reject nested archives and non-PDF entries unless explicitly required.
- Validate PDFs using file signatures and MIME inspection; do not trust filename extensions alone.
- Configure processing timeouts and queue retries.
- Prevent duplicate execution from creating duplicate records.
- Keep staging files and error reports private.
- Clean up temporary files after success or failure.
- Record detailed, actionable errors without exposing filesystem paths to public users.

Do not process large archives inside a normal HTTP request.

### Phase 5 — Public Controllers and Routes

Implement:

- `PublicController@index`
- `NewsController@index`
- `NewsController@show`
- `CertificateController@search`
- `CertificateController@download`

Keep controllers focused on HTTP handling. Move complicated import, file-management, and normalization logic into dedicated services.

Use:

- Eloquent scopes for published content.
- Form request classes where validation is sufficiently complex.
- Named routes.
- Route-model binding or explicit record lookup.
- Pagination and bounded queries.
- Server-side validation and escaped output.
- Private filesystem storage for all certificates.

The certificate search POST action should return a view with the submitted search context and matching results. Do not place the participant's username in query strings unnecessarily.

### Phase 6 — Blade Views and Frontend Migration

Create:

```text
resources/views/
├── layouts/
│   └── app.blade.php
└── public/
    ├── index.blade.php
    ├── news/
    │   ├── index.blade.php
    │   └── show.blade.php
    └── certificates/
        └── search.blade.php
```

Additional partials may be introduced for the navbar, footer, news cards, statistics, and category cards.

Implementation requirements:

1. Migrate the existing navigation, sections, and footer into Blade.
2. Replace static news placeholders with database-driven content.
3. Introduce accessible certificate search and results views.
4. Preserve existing image filenames and relative asset relationships where possible.
5. Update asset references to use Laravel's `asset()` helper or Vite.
6. Preserve responsive Bootstrap styling and the existing color palette, including `#4294E3` and `#8F12FD`.
7. Verify that jQuery-dependent scripts load in the correct order.
8. Avoid duplicate library loading and unnecessary dependencies.
9. Retain WOW.js, CounterUp, and Owl Carousel only where they work correctly and contribute to the original experience.
10. Ensure all forms have labels, validation feedback, keyboard accessibility, and appropriate empty states.
11. Render article content only after HTML sanitization.
12. Use correct page titles and meta descriptions.

Do not blindly copy all legacy scripts and styles into the new application. Audit them first and migrate only the dependencies needed to preserve existing functionality.

### Phase 7 — Automated Testing and Quality Assurance

Create automated feature, unit, and integration tests.

**News and content tests**

- Published articles appear in the news list.
- Draft and future-dated articles are hidden.
- Direct requests for unpublished articles return 404.
- Slugs are unique.
- Article excerpts and rich-text output are safe.
- Settings changes appear on the landing page.
- Pagination works correctly.

**Certificate tests**

- A valid username returns the correct certificates.
- Normalization behaves according to the documented username contract.
- Multiple certificates are displayed correctly.
- Missing certificates return the intended empty state.
- Invalid search input is rejected.
- Nonexistent download IDs return 404.
- Successful downloads use the expected filename.
- No public route exposes a raw filesystem path.
- Missing or corrupted files are handled safely.
- Rate limiting is applied.

**Bulk-import tests**

- Valid ZIP-and-CSV imports succeed.
- Missing PDFs are reported.
- Duplicate filenames are detected.
- Invalid categories, years, and award types are rejected.
- Malformed CSV rows are reported without crashing the entire batch.
- Invalid or malicious archive paths are rejected.
- Oversized archives are rejected.
- Non-PDF and malformed PDF files are rejected.
- Duplicate records are skipped or rejected according to the defined policy.
- Repeated job execution does not create duplicate certificates.
- Partial failures do not leave unreferenced files.
- Batch counts and final statuses remain consistent.
- Failed jobs and retries are observable.

**Security tests**

- Guests cannot access the admin panel.
- Unauthorized users cannot modify records.
- CSRF protection remains enabled for state-changing forms.
- Malicious rich-text content is neutralized.
- Upload validation cannot be bypassed using misleading filenames.
- Error messages do not expose credentials, stack traces, or internal paths.

**Visual regression tests**

Compare the original website and the Laravel implementation at representative desktop and mobile viewport sizes. Verify logos, mascot imagery, category cards, navigation, statistics, animations, and footer layout.

### Phase 8 — Production Deployment

1. Confirm hosting supports the selected PHP version, Laravel, MySQL, Composer dependencies, and Node-built assets.
2. Configure the web server document root to point to Laravel's `public/` directory.
3. Enable HTTPS and secure session cookies.
4. Configure production database credentials and filesystem permissions.
5. Generate and protect `APP_KEY`; never replace it casually after production data has been encrypted.
6. Run production migrations with `php artisan migrate --force`.
7. Build frontend assets using `npm ci` and `npm run build`.
8. Configure a supervised queue worker.
9. Configure queue failure monitoring and retry procedures.
10. Schedule periodic cleanup of expired temporary files and abandoned import staging files.
11. Configure automated database backups and a separate backup policy for uploaded certificates.
12. Test database and file restoration procedures.
13. Set `APP_DEBUG=false` and configure centralized error logging.
14. Configure appropriate cache, session, and filesystem settings.
15. Verify that certificate storage is outside the web-accessible directory.
16. Run smoke tests against the production hostname.
17. Monitor application errors, queue failures, and disk usage after release.

Use Laravel's production deployment guidance as the baseline for server configuration and optimization.

---

## 9. Target File Structure

```text
bebras-del-portal/
├── app/
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── NewsResource.php
│   │   │   ├── CertificateResource.php
│   │   │   ├── SiteSettingResource.php
│   │   │   └── CertificateImportBatchResource.php
│   │   └── Pages/
│   │       └── SiteSettings.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PublicController.php
│   │   │   ├── NewsController.php
│   │   │   └── CertificateController.php
│   │   └── Requests/
│   ├── Jobs/
│   │   └── ProcessBulkCertificateUpload.php
│   ├── Models/
│   │   ├── News.php
│   │   ├── SiteSetting.php
│   │   ├── Certificate.php
│   │   └── CertificateImportBatch.php
│   ├── Policies/
│   └── Services/
│       ├── BulkCertificateImportService.php
│       └── CertificateStorageService.php
├── config/
│   └── filesystems.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── legacy-assets/
│       ├── img/
│       ├── css/
│       ├── js/
│       └── lib/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       └── public/
│           ├── index.blade.php
│           ├── news/
│           │   ├── index.blade.php
│           │   └── show.blade.php
│           └── certificates/
│               └── search.blade.php
├── routes/
│   └── web.php
├── storage/
│   └── app/
│       ├── private/
│       │   ├── certificates/
│       │   ├── certificate-imports/
│       │   └── certificate-reports/
│       └── temporary/
├── tests/
│   ├── Feature/
│   └── Unit/
├── .env.example
├── composer.json
├── composer.lock
├── package.json
└── vite.config.js
```

The exact Filament resource and page structure should follow the installed Filament version. The legacy asset directory may instead remain under `public/img`, `public/css`, `public/js`, and `public/lib` if that simplifies migration; the essential requirement is to preserve working asset references and keep sensitive documents outside the public directory.

---

## 10. Security and Data Integrity

### 10.1 Authentication and Authorization

- Disable public administrator registration.
- Enforce authorization at the panel and resource levels.
- Protect administrative actions with authentication and CSRF controls.
- Use strong credentials and HTTPS.
- Apply rate limiting to login and certificate endpoints.
- Never expose production credentials in source control.

### 10.2 File Uploads

- Validate file extensions, MIME types, actual content, and file size.
- Keep uploaded PDFs and CSV error reports on private storage.
- Generate server-side storage filenames instead of trusting uploaded filenames.
- Reject dangerous archive entries and enforce decompression limits.
- Avoid exposing upload errors containing internal paths.
- Reconcile filesystem changes with database transactions and log cleanup failures.

### 10.3 Certificate Downloads

- Download only files referenced by valid certificate records.
- Resolve file paths from trusted database records.
- Return safe attachment filenames.
- Apply rate limits and private cache headers.
- Never assume an unpredictable or sequential database ID is a complete authorization mechanism.
- Minimize public participant metadata.

### 10.4 Content Security

- Escape user-controlled text in Blade.
- Sanitize HTML from the rich-text editor before rendering.
- Validate uploaded cover images.
- Keep administrative file uploads separate from public media.
- Avoid arbitrary file paths, unsafe HTML, and executable file uploads.

### 10.5 Bulk-Import Integrity

- Validate the CSV header and every data row.
- Define a documented duplicate policy.
- Use idempotent processing.
- Maintain import-batch counters and actionable error reports.
- Prevent ZIP path traversal and resource-exhaustion attacks.
- Never overwrite existing certificates silently.
- Provide a documented recovery process for partial failures.

### 10.6 Data Protection and Operations

- Restrict database and storage permissions.
- Maintain separate database and document backups.
- Establish retention rules for staging files and error reports.
- Record administrative changes and important import failures.
- Avoid logging certificate contents or unnecessary personal data.
- Test recovery procedures before production release.

---

## 11. Acceptance Criteria

The rebuild is considered complete when all of the following conditions are met.

| ID | Criterion | Verification |
|---|---|---|
| AC-01 | All original landing-page sections, approved copy, branding, and imagery are preserved. | Content audit and visual comparison |
| AC-02 | The site works at desktop and mobile viewport sizes. | Responsive UI testing |
| AC-03 | Authorized administrators can log in and manage news. | Authentication and CRUD tests |
| AC-04 | Administrators can update approved website content and statistics. | Settings feature tests |
| AC-05 | Draft and future-dated articles are never publicly accessible. | Feature tests |
| AC-06 | The landing page displays up to three latest published news articles. | Feature tests |
| AC-07 | News index and detail pages display correct content and metadata. | Feature tests |
| AC-08 | Visitors can search certificates without creating accounts. | Feature tests |
| AC-09 | Multiple certificates for a username are displayed correctly. | Test dataset covering multiple years |
| AC-10 | Certificate downloads return valid PDFs without exposing internal storage paths. | Download and security tests |
| AC-11 | Valid ZIP-and-CSV uploads produce correct certificate records. | Integration tests |
| AC-12 | Invalid rows and files are reported without silently corrupting valid records. | Import failure tests |
| AC-13 | Repeated or retried imports do not create unintended duplicate records. | Idempotency tests |
| AC-14 | Administrators can monitor import status and download error reports. | Admin workflow tests |
| AC-15 | The queue worker, logging, backups, and recovery procedures are documented and operational. | Deployment checklist |
| AC-16 | Production configuration disables debug mode and protects private storage. | Deployment security review |
| AC-17 | Automated tests pass and no critical security or data-integrity defects remain. | Test report and release review |

---

## 12. Definition of Done and Deliverables

Deliver the following:

1. A working Laravel 13 application with verified dependencies.
2. A public website preserving the original Bebras IT Del identity and content.
3. An authenticated administrative panel.
4. News and website-content management.
5. Individual certificate management.
6. Secure public certificate search and download.
7. Queued bulk certificate imports with progress, validation, and error reporting.
8. Database migrations, factories, and appropriate development seeders.
9. Automated feature, unit, and integration tests.
10. An `.env.example` file containing placeholders only.
11. A README covering setup, dependency requirements, migrations, asset compilation, administrator provisioning, queue operation, imports, and deployment.
12. A deployment checklist covering private storage, backups, HTTPS, queues, and production configuration.
13. A documented CSV-and-ZIP import specification with an example file.
14. A visual and functional comparison against the original website.
15. A final implementation report identifying completed features, test results, known limitations, and any remaining operational risks.

## 13. Recommended Execution Order

Prioritize work to reduce integration risk:

1. Audit the existing website and freeze a visual/content baseline.
2. Verify Laravel, PHP, Filament, and plugin compatibility.
3. Establish migrations, models, settings, and publication rules.
4. Rebuild the public landing page and news pages.
5. Implement admin authentication and news management.
6. Implement certificate storage and individual CRUD.
7. Implement public certificate search and downloads.
8. Build the bulk-import pipeline and operational monitoring.
9. Complete security, accessibility, regression, and integration testing.
10. Deploy to staging, validate representative imports, and obtain acceptance.
11. Back up the existing production website, deploy the Laravel application, and retain a documented rollback procedure.

**Final implementation principle:** Preserve the existing website first, establish reliable data and storage contracts second, and introduce the administrative and bulk-processing capabilities on top of that stable foundation. Correctness, privacy, recoverability, and maintainability take precedence over minimizing the number of files or maximizing code generation speed.