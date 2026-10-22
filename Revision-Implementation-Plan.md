# Revision Implementation Plan — Pencarian Sertifikat & Seamless UI

**Project:** Bebras IT Del Portal  
**Date:** 2026-10-22  
**Scope:** (1) Certificate search UX, (2) Cross-page UI unification so Home, Berita, Detail Berita and Sertifikat feel like one site.  
**Supersedes:** the earlier "Revision Implementation Plan" draft. That draft was written before parts of it were applied; this version is reconciled with the code as it is today.

---

## Part 0 — Current-State Reconciliation (read first)

The previous draft listed everything as TODO. Checking the repository shows most certificate items are already in the code, and some of that work introduced regressions.

| Draft item | Real status | Evidence |
|---|---|---|
| REV-CS-1 GET form | ✅ Done | `search.blade.php:54`, `routes/web.php` GET route |
| REV-CS-2 Group by year (accordion) | ✅ Done | `CertificateController::search`, `search.blade.php:152` |
| REV-CS-3 Year filter | ⚠️ Partly | Dropdown only appears *after* a search; `$certificateYears` comes from a `View::composer('*')`, not the controller |
| REV-CS-4 Wider results | ✅ Done | Results sit outside `.cert-search-form-wrap` |
| REV-CS-5 Filename | ✅ Done | `Certificate::getDownloadFilenameAttribute` |
| REV-CS-8 Indonesian badges | ⚠️ Partly | View is fixed, but `Certificate::AWARD_TYPES` still says `Peserta (Participant)` / `Pemenang (Winner)` (used by admin/import) |
| REV-CS-9 Banners only before search | ✅ Done | `@if(!$searched)` |
| REV-UI-1 H1 in brand | ✅ Done, **but caused a bug** (see UI-A) | `app.blade.php:99` is now a `<span>` |
| REV-UI-2 Dynamic footer | ✅ Done | Uses `$globalSettings`. Note the real keys are `contact_*` and `social_*`, not `facebook_url` as the draft said |
| REV-UI-3 Remove "Masuk Admin" | ✅ Done | Footer no longer has it |
| REV-UI-4 Single icon library | ⚠️ Partly | Layout fixed; `public/js/main.js:86` still uses `bi bi-chevron-*` |

### Regressions and defects found (these come before any new feature)

| ID | Sev | Defect | Location |
|---|---|---|---|
| **R-1** | 🔴 | **Search view is broken.** The file contains a second, truncated `@section('content')` (line 263 on) with no `@endsection`. PHPUnit reports "did not close its own output buffer" and 4 `CertificateSearchTest` cases are WARN, not PASS. In production the page can render empty or error. | `resources/views/public/certificates/search.blade.php:263-268` |
| **R-2** | 🔴 | **Mojibake in rendered output.** The file was saved double-encoded (`â€“`, `â€”`, `â”€â”€`). It is visible to users: the empty-school placeholder `'â€“'` and the download button tooltip. | `search.blade.php:192, 212`, plus comments |
| **R-3** | 🔴 | **"Tentang Kami / Kegiatan / Tantangan" links are dead on sub-pages.** `main.js` runs `preventDefault()` on every nav link with a hash and then calls `$(this.hash).offset().top`. On `/berita` or `/sertifikat` the target does not exist, so it throws and the link never navigates. | `public/js/main.js:30-43`, `app.blade.php:108-110` |
| **R-4** | 🟠 | **Brand text lost its styling.** `style.css` styles `.navbar-brand h1` (white on the hero, gradient when sticky). The brand is now a `<span class="navbar-brand-text">` that has no CSS, so on the gradient header it falls back to Bootstrap's near-black and fails contrast. | `style.css:182-255` vs `app.blade.php:99` |
| **R-5** | 🟠 | **Multiple `<h1>` on Home.** "Kegiatan Bebras Biro IT Del", "Kategori Tantangan" and "Berita Terbaru" are all `<h1>`, plus the hero `<h1>`. | `public/index.blade.php:91,122,203` |
| **R-6** | 🟡 | `<title>` says "Pencarian Sertifikat Peserta" while the `<h1>` says "Pencarian Sertifikat Bebras Challenge" (CS-14 still open). | `search.blade.php:3,27` |

---

## Part 1 — Cross-Page UI Audit ("seamless" requirement)

The four public pages share a layout but each page defines its own rhythm, so moving between them is visibly jarring. Findings:

| ID | Sev | Inconsistency | Where |
|---|---|---|---|
| **UI-A** | 🟠 | **Header spacing differs by viewport.** On ≥992px the navbar is `position:absolute` over the page, so `padding-top: 8rem` is needed. Below 992px the navbar is in normal flow, but `.page-header` still has 8rem, leaving a large blank band on phones. The Home hero correctly drops to `0` on mobile; sub-pages do not. | `app.blade.php:33-40`, `style.css:259-279` |
| **UI-B** | 🟠 | **Page header is copy-pasted three times** with small drifts: Sertifikat has a subtitle (`fs-5 mb-3`), Berita has none, Detail has an inline `style="max-width:900px; line-height:1.3"` and `mt-3` on the breadcrumb. | `search`, `news/index`, `news/show` views |
| **UI-C** | 🟠 | **Section spacing is not shared.** Home uses `py-5 > container py-5 px-lg-5`; sub-pages use `py-4 > container px-lg-5`. Jumping Home → Berita shifts the vertical rhythm. | all views |
| **UI-D** | 🟠 | **Three different card languages.** News cards `12px` radius with a hover lift; Sertifikat `rounded-4 bg-light shadow-sm`; Detail `rounded border shadow-sm`; Home features `bg-light rounded`. No single radius, border or shadow token. | inline `<style>` + Bootstrap utility classes |
| **UI-E** | 🟠 | **Page-specific CSS lives in a layout `<style>` block** (`.page-header`, `.news-card`, `.search-container` (now unused), `.table-responsive`) and in a `@push('styles')` block in `search.blade.php`. Page CSS is split across three places, and `.table-responsive{overflow:hidden}` clips dropdowns and focus rings. | `app.blade.php:33-79`, `search.blade.php:7-20` |
| **UI-F** | 🟡 | **Active-nav logic is inconsistent.** Only Beranda / Berita / Cari Sertifikat get `active`. The three anchor links never do, and `main.js` moves `.active` by hand, so after clicking one on Home both "Beranda" and the anchor can look inactive/active incorrectly. `data-bs-spy` is on `<body>` but is useless on sub-pages. | `app.blade.php:83,107-112`, `main.js` |
| **UI-G** | 🟡 | **CTA duplication.** "Cari Sertifikat" appears as a nav link, a nav button, a footer link, and a footer button. | `app.blade.php` |
| **UI-H** | 🟡 | **Empty/zero states differ.** Berita uses a big icon + muted text + gradient button; Home uses a `.alert-light`; Sertifikat uses an `.alert-warning` card. | views |
| **UI-I** | 🟡 | **Dead / stale front-end code.** Owl carousels (`screenshot-carousel`, `testimonial-carousel`) have no markup but ship JS/CSS; jQuery 3.4.1 (old); no SRI on CDN assets. | `main.js`, `app.blade.php:186-197` |
| **UI-J** | 🟢 | Date formats differ across views (`d F Y`, `d F Y, H:i` + hard-coded "WIB", `d M Y`). | news views |

---

## Part 2 — Certificate Search: Remaining Work

| ID | Sev | Item |
|---|---|---|
| CS-A | 🟡 | Year filter is hidden until a search is made, so a first-time user cannot pre-select a year. Show it always (inside the form, collapsed under the input on mobile). Pass `$certificateYears` from the controller explicitly instead of relying on a `*` composer that runs a DB query for every view and every partial. |
| CS-B | 🟡 | `AppServiceProvider` registers bindings **after** the composer and catches `\Throwable` silently (hides a broken DB on every page). Move to `Cache::remember` (e.g. 10 min, flushed on settings/certificate change) and log the failure instead of swallowing it. |
| CS-C | 🟡 | `Certificate::AWARD_TYPES` still contains English parentheticals. Use `Peserta` / `Pemenang` and make the view read from one label map (`Certificate::awardLabel()`), so the badge, admin and import screens agree. |
| CS-D | 🟡 | Rate limit is `40/min` per IP on search. Add a username-enumeration guard: identical response timing for found/not-found and no "did you mean" hints. Keep the generic copy. |
| CS-E | 🟢 | Add a loading state on submit (disable button, swap icon for spinner). |
| CS-F | 🟢 | Add `rel="nofollow"` on download links and `noindex` on `?username=` result URLs (they expose personal names). |
| CS-G | 🟢 | Preview/thumbnail of the PDF (CS-9 from the old draft) — keep out of scope; revisit with `edition_name` / `score` columns. |

---

## Part 3 — Implementation Plan

Each step is independently shippable. Do them in this order; run `php artisan test` after every step.

### Step 1 — Stop the bleeding (R-1, R-2, R-6)
1. Delete lines 262–end of `search.blade.php` (the duplicate `@section`).
2. Re-save the file as **UTF-8 without BOM**; replace mojibake with the real characters (`–`, `—`) and plain ASCII comments. Add `.editorconfig` (`charset = utf-8`), which already exists, and a CI/lint check: `grep -rnP "â€|â”" resources/views` must return nothing.
3. Align the title: `@section('title', 'Pencarian Sertifikat Bebras Challenge')`.
4. **Gate:** `CertificateSearchTest` all PASS (no "output buffer" warnings).

### Step 2 — Fix navigation (R-3, R-4, UI-F)
1. In `main.js`, only intercept in-page anchors:
   ```js
   $('.navbar-nav a').on('click', function (e) {
       if (this.hash && this.pathname === location.pathname) { /* smooth scroll */ }
   });
   ```
   Cross-page anchors (`/berita` → `/#aboutus`) then navigate natively and the browser jumps to the section.
2. On Home, render the three anchor links as `#aboutus`, `#programs`, `#bebraschallenge`; elsewhere as `route('home').'#…'`. Use `@if(request()->routeIs('home'))`.
3. Drop `data-bs-spy` from `<body>`; apply it only on Home (`@section('body_attrs')`), with `data-bs-target="#navbarCollapse"` so it highlights the section being read.
4. Restore brand styling: rename the selectors in `style.css` from `.navbar-brand h1` to `.navbar-brand-text` (white on the hero, gradient when sticky/mobile). Verify contrast ≥ 4.5:1 in all three states.
5. **Gate:** from `/berita`, clicking "Tentang Kami" lands on `/#aboutus`; on `/`, it smooth-scrolls; no console errors.

### Step 3 — One page-header component (UI-A, UI-B, R-5)
1. Create `resources/views/components/page-header.blade.php` with props `title`, `subtitle` (optional), `breadcrumbs` (array of `[label, url|null]`), `h1` always rendered as the only `<h1>`.
2. Replace the three hand-written headers with `<x-page-header …/>`.
3. Padding is driven by one CSS variable:
   ```css
   :root { --nav-offset: 0px; }
   @media (min-width: 992px) { :root { --nav-offset: 5.5rem; } }   /* navbar is absolute */
   .page-header { padding: calc(var(--nav-offset) + 2.5rem) 0 3rem; margin-bottom: 3rem; }
   ```
   This removes the mobile dead band and lets the navbar height change without touching pages.
4. Long titles (news detail): `.page-header h1 { max-width: 900px; margin-inline: auto; line-height: 1.3; text-wrap: balance; }` in CSS, not inline.
5. Home: demote the three section titles to `<h2 class="mb-5">` keeping the visual size via `.display-title` (so nothing looks different). The hero `<h1>` stays (its image has the alt text "Bebras Indonesia"; add a visually hidden text node "Bebras Biro Institut Teknologi Del").
6. **Gate:** each page has exactly one `<h1>` (add an assertion in tests); header height is visually identical on Berita, Detail and Sertifikat at 375 / 768 / 1280px.

### Step 4 — Shared section rhythm and card tokens (UI-C, UI-D, UI-H)
Add to `public/css/style.css`:
```css
:root {
    --radius-card: 1rem;
    --shadow-card: 0 .25rem 1rem rgba(38,43,71,.08);
    --shadow-card-hover: 0 .75rem 1.75rem rgba(66,148,227,.18);
    --section-y: 3rem;          /* sub-pages */
    --section-y-lg: 5rem;       /* home */
}
.page-section      { padding-block: var(--section-y); }
.page-section--lg  { padding-block: var(--section-y-lg); }
.surface           { background:#fff; border:1px solid rgba(0,0,0,.06); border-radius:var(--radius-card); box-shadow:var(--shadow-card); }
.surface--muted    { background:var(--light); border-color:transparent; }
.surface--hover:hover { transform:translateY(-4px); box-shadow:var(--shadow-card-hover); }
```
- Replace the ad-hoc `rounded`, `rounded-4`, `bg-light shadow-sm border` combinations with `.surface` / `.surface--muted` on: news card, article card, sidebar boxes, certificate search card, empty state, home feature and category cards, info banners.
- Add an `<x-empty-state icon title text>` component (one pattern for "no news", "no certificates", "no result").
- **Gate:** side-by-side screenshots of Home, Berita, Detail and Sertifikat show the same radius, shadow and spacing.

### Step 5 — Consolidate CSS and clean dependencies (UI-E, UI-I)
1. Move the layout `<style>` block and the `@push('styles')` block into `public/css/style.css`, grouped under `/*** Sub-page ***/`, `/*** News ***/`, `/*** Certificates ***/`. Delete the unused `.search-container`.
2. Replace `.table-responsive { overflow:hidden }` with a wrapper that clips only the rounded corners (`overflow-x:auto` on the table wrapper and `overflow:hidden` on the *accordion item*), so keyboard focus rings are not cut off.
3. Remove the owl-carousel and testimonial code from `main.js`, remove the unused `lib/owlcarousel` links, and delete `bi bi-chevron-*`. Remove `wow` from the footer if it causes layout shift.
4. Upgrade jQuery to 3.7.1 and align Bootstrap JS with the version in `css/bootstrap.min.css`; add `integrity` + `crossorigin` attributes (or vendor the files into `public/lib`).
5. **Gate:** no page references a missing file (network tab has zero 404s); Lighthouse "Best Practices" ≥ 95.

### Step 6 — Certificate search polish (CS-A to CS-F)
1. Controller passes `certificateYears`; filter `<select>` is always visible and keeps its value on re-submit; "Hapus Filter" resets only `year`.
2. Cache site settings and years (`Cache::remember`), invalidate from the admin save hooks and the import job; log errors.
3. Centralise labels in `Certificate` (`awardLabel()`, `categoryLabel()`).
4. Submit-state spinner; `noindex` meta when `$searched`; `rel="nofollow noopener"` on downloads.
5. Mobile (375px): result rows render as stacked cards (`d-md-none`) instead of a 5-column table: name + username on one line, school below, badges and the PDF button on a final row. Desktop keeps the table.
6. **Gate:** no horizontal scroll at 375px with a 4-certificate result.

### Step 7 — Tests (do alongside Steps 1–6)
Extend the existing tests rather than only re-pointing POST→GET (that migration is already done):

| Test | Asserts |
|---|---|
| `search page has no unclosed sections` | GET `/sertifikat` returns 200 and no PHPUnit output-buffer warning |
| `no mojibake in rendered pages` | For `/`, `/berita`, `/sertifikat?username=x`, response does not contain `â€` |
| `each public page has exactly one h1` | `substr_count($html, '<h1')` is 1 for each public route (data provider) |
| `year filter returns only that year` | Seed 2023 + 2024; `?username=…&year=2023` lacks 2024 |
| `same user, same year, both award types` | Two downloads have different `Content-Disposition` filenames |
| `footer reads from SiteSetting` | Change `contact_email` → appears in footer on all routes |
| `no admin link in public footer` | Response lacks `/admin` |
| `cross-page nav anchors` | On `/berita`, the "Tentang Kami" href is `…/#aboutus`; on `/` it is `#aboutus` |
| `search result urls are noindex` | `?username=` response has `robots` meta `noindex` |

Browser check (manual or Playwright): 375 / 768 / 1280px on all four pages, plus click-through of every nav link from each page.

---

## Part 4 — Acceptance Criteria

| ID | Criterion |
|---|---|
| A-1 | `php artisan test` passes with **zero warnings**, including the new tests above. |
| A-2 | `/sertifikat` renders fully; no duplicated sections; no mojibake anywhere in `resources/views`. |
| A-3 | Every nav item works from every page (anchor links navigate to Home sections from sub-pages). |
| A-4 | Exactly one `<h1>` per public page. |
| A-5 | Page headers on Berita, Detail and Sertifikat have the same height, type scale and breadcrumb style at 375, 768 and 1280px, with no empty gap under the mobile navbar. |
| A-6 | Cards, empty states and section spacing use the shared `.surface` / `.page-section` tokens. No page-specific CSS remains in Blade files. |
| A-7 | The brand name is legible on the hero gradient, on the sticky white navbar, and in the mobile menu. |
| A-8 | The year dropdown is available before the first search; filtering by year returns only that year; "Semua Tahun" returns all. |
| A-9 | Download filenames follow `{username}_{year}_{category}_{award_type}_sertifikat.pdf`; two certificates for the same user and year never collide. |
| A-10 | Footer contact info and social links come from `SiteSetting` (`contact_*`, `social_*`); no admin link in the public footer. |
| A-11 | At 375px, certificate results have no horizontal scroll. |
| A-12 | No 404s or console errors on any public page; no unused carousel or Bootstrap-Icons code remains. |

---

## Part 5 — Risks and Notes

- **Navbar is `position:absolute` on desktop** and flips to `fixed` after a 45px scroll via JS. That is why the header offset exists; Step 3 centralises it, but changing the navbar to `position: sticky` from the start would remove the whole class of problem. Defer unless the design is approved to change.
- **News body is printed with `{!! $news->content !!}`.** It is admin-authored, but sanitise on save (HTML Purifier or an allow-list) before wider editor access.
- **Search result URLs contain usernames.** Keep them out of analytics and server access logs where practical (CS-F).
- **Schema:** no migrations needed. `edition_name` and `score` remain future work.
- **Do not overwrite `Implementation-Plan.md`.** It is the original rebuild plan; this document is its revision.

## Part 6 — Execution Order

```
1  Remove duplicate section + fix encoding + title        (R-1, R-2, R-6)   → tests green
2  Navigation fixes + brand CSS                           (R-3, R-4, UI-F)
3  <x-page-header> + --nav-offset + h1 cleanup            (UI-A, UI-B, R-5)
4  .surface / .page-section tokens + <x-empty-state>      (UI-C, UI-D, UI-H)
5  CSS consolidation + dependency cleanup                 (UI-E, UI-I)
6  Certificate search polish + mobile result cards        (CS-A … CS-F)
7  Tests + 3-viewport browser pass                        → A-1 … A-12
```
