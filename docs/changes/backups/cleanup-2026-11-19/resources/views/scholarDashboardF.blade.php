<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scholar Dashboard</title>
<link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
  <script src="{{ asset('js/bakr-scholar-dashboard-guard.js') }}" data-login-url="{{ url('/scholarLogin') }}"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link
    href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <!-- Bootstrap 5.3: responsive grid, flex utilities, spacing, buttons and form controls. -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
    crossorigin="anonymous" />
  <link rel="stylesheet" href="{{ asset('css/bakr-scholar-dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
</head>

<body class="dash">
  <header class="mobile-header" aria-label="Mobile navigation">
    <button
      type="button"
      class="menu-toggle"
      id="menu-toggle"
      aria-label="Open navigation menu"
      aria-expanded="false"
      aria-controls="sidebar">
      <svg class="icon-burger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
        <path d="M4 7h16M4 12h16M4 17h16" />
      </svg>
      <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
        <path d="M18 6 6 18M6 6l12 12" />
      </svg>
    </button>
  </header>
  <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
  <div class="layout app-shell dash-panel d-flex">
    <!-- Bootstrap: .d-flex establishes the main flex container; custom CSS keeps the original sizing. -->
    <aside class="sidebar dash-side" id="sidebar">
      <div class="sidebar-inner d-flex flex-column h-100">
        <!-- Bootstrap: .flex-column + .h-100 keeps sidebar content filling the available height. -->
        <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close navigation menu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M18 6 6 18M6 6l12 12" />
          </svg>
        </button>
        <div class="brand d-flex align-items-center">
          <span class="brand-mark brand-badge" aria-hidden="true">
            <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="">
          </span> 
          <span class="brand-name">BAKR</span>
          <span class="brand-tag">PORTAL</span>
        </div>

        <div class="scholar-card officer-mini">
          <div class="avatar-row d-flex align-items-center">
            <div class="avatar" id="sd-side-avatar"></div>
            <div>
              <div class="scholar-name" id="sd-side-name"></div>
              <div class="scholar-meta" id="sd-side-meta"></div>
            </div>
          </div>
          <div class="scholar-year-pill role-badge">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path d="M22 10 12 5 2 10l10 5 10-5Z" />
              <path d="M6 12.5V17c0 1.5 3 3 6 3s6-1.5 6-3v-4.5" />
            </svg>
            <span id="sd-side-year"></span>
          </div>
        </div>

        <nav class="sidebar-nav dash-nav d-flex flex-column flex-grow-1">
          <!-- Bootstrap: .flex-grow-1 makes navigation occupy the remaining sidebar space. -->
          <button type="button"
            class="nav-item active"
            data-view="dashboard"
            aria-current="page">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <rect x="3" y="3" width="7" height="9" rx="1.5" />
              <rect x="14" y="3" width="7" height="5" rx="1.5" />
              <rect x="14" y="12" width="7" height="9" rx="1.5" />
              <rect x="3" y="16" width="7" height="5" rx="1.5" />
            </svg>
            Dashboard
          </button>
          <button type="button"
            class="nav-item"
            data-view="scholarship">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path d="M12 2 3 7v6c0 5 4 8.5 9 9 5-.5 9-4 9-9V7z" />
            </svg>
            My Scholarship
          </button>
          <button type="button"
            class="nav-item"
            data-view="documents">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path
                d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <path d="M14 2v6h6" />
            </svg>
            Documents
          </button>
          <button type="button"
            class="nav-item"
            data-view="benefits">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path
                d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
            Benefits
          </button>
          <button type="button"
            class="nav-item"
            data-view="notifications">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2">
              <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.7 21a2 2 0 0 1-3.4 0" />
            </svg>
            Notifications
          </button>
          <div class="bottom-nav">
            <button type="button"
              class="nav-item"
              data-view="profile">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 21c0-4 4-7 8-7s8 3 8 7" />
              </svg>
              Profile
            </button>
            <button type="button"
              class="nav-item"
              data-view="settings">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2">
                <circle cx="12" cy="12" r="3" />
                <path
                  d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z" />
              </svg>
              Settings
            </button>
          </div>
        </nav>
      </div>
    </aside>

    <main class="main main-content dash-main" id="view-dashboard">
      <div class="header page-header">
        <div>
          <h1><span><span class="js-greeting">Good evening</span>, <span id="sd-first-name"></span>.</span></h1>
          <p>Here's what's happening with your scholarship.</p>
        </div>
        <time class="date-pill" datetime="2026-09-19">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
          </svg>
          <strong class="js-date">SEPTEMBER 19, 2026</strong> · <span class="js-time">12:00AM</span>
        </time>
      </div>

      <section class="hero d-flex justify-content-between align-items-center flex-wrap">
        <!-- Bootstrap: flex utilities preserve the hero alignment and wrapping. -->
        <div class="hero-left">
          <div class="hero-eyebrow">SCHOLARSHIP STATUS</div>
          <span class="status-pill pill-active" id="sd-hero-status"></span>
          <h2 id="sd-hero-title"></h2>
          <p class="sub" id="sd-hero-sub"></p>
          <div class="hero-renewal">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.4">
              <path d="M20 6 9 17l-5-5" />
            </svg>
            <span id="sd-hero-renewal-text"></span>
          </div>
        </div>
        <div class="hero-ring" id="sd-hero-ring" aria-label="Requirements progress">
          <div class="ring-wrap">
            <svg viewBox="0 0 132 132" aria-hidden="true">
              <circle class="ring-bg" cx="66" cy="66" r="52" />
              <circle class="ring-fill" id="sd-ring-fill" cx="66" cy="66" r="52" />
            </svg>
            <div class="ring-label">
              <span class="pct" id="sd-ring-pct"></span>
              <span class="txt">complete</span>
            </div>
          </div>
          <div class="hero-ring-caption" id="sd-ring-caption"></div>
        </div>
      </section>

      <!-- Bootstrap: .row + .col-lg-7/.col-lg-5 replaces the old responsive grid media query. -->
      <section class="two-col row g-3">
        <div class="col-lg-7">
          <div class="card card-panel">
            <div class="card-head">
              <div>
                <h3>Needs Your Action</h3>
              </div>
            </div>
            <ul class="doc-list" id="sd-action-list"></ul>
            <a class="card-link" href="#my-scholarship" data-view="scholarship">View all requirements</a>
          </div>
        </div>

        <div class="col-lg-5">
          <section class="card dashboard-snapshot card-panel" aria-labelledby="snapshot-title">
            <div class="card-head">
              <div>
                <h3 id="snapshot-title">Scholarship Snapshot</h3>
                <div class="card-sub">Your current award at a glance</div>
              </div>
            </div>
            <dl class="snapshot-list">
              <div class="snapshot-row">
                <dt>Current standing</dt>
                <dd><span class="status-pill pill-active" id="sd-snap-standing"></span></dd>
              </div>
              <div class="snapshot-row" id="sd-snap-stipend-row">
                <dt id="sd-snap-stipend-label"></dt>
                <dd id="sd-snap-stipend"></dd>
              </div>
              <div class="snapshot-row">
                <dt>Renewal readiness</dt>
                <dd id="sd-snap-ready"></dd>
              </div>
            </dl>
            <div class="snapshot-actions">
              <button type="button" class="doc-btn btn quiet sm" data-view="benefits">View benefits</button>
              <button type="button" class="doc-btn btn primary sm" data-view="documents">Manage documents</button>
            </div>
          </section>
        </div>
      </section>

      <section class="card notif-panel card-panel">
        <div class="card-head">
          <div>
            <h3>Recent Notifications</h3>
            <div class="card-sub" id="sd-recent-sub"></div>
          </div>
        </div>
        <ul class="notif-list" id="sd-recent-notifs"></ul>
        <a class="card-link" href="#notifications" data-view="notifications">View all notifications</a>
      </section>
    </main>
    <main class="main main-content dash-main" id="view-scholarship" hidden>
      <div class="header page-header">
        <h1>My Scholarship</h1>
        <time class="date-pill" datetime="2026-09-19">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
          </svg>
          <strong class="js-date">SEPTEMBER 19, 2026</strong> · <span class="js-time">12:00AM</span>
        </time>
      </div>

      <!-- 1. Scholarship overview: which scholarship, and am I active? -->
      <section class="hero overview" aria-label="Scholarship overview">
        <div class="ov-top">
          <div class="hero-left">
            <h2 id="sd-ov-title"></h2>
            <p class="sub" id="sd-ov-sub"></p>
          </div>
          <div class="ov-status">
            <div class="ov-status-label">Scholarship status</div>
            <span class="status-pill pill-active" id="sd-ov-status"></span>
          </div>
        </div>
        <!-- Bootstrap: responsive row-cols creates 1/3 columns without custom breakpoint CSS. -->
        <dl class="ov-grid row row-cols-1 row-cols-sm-3 g-3">
          <div class="col">
            <dt>Disbursed amount</dt>
            <dd id="sd-ov-disbursed"></dd>
          </div>
          <div class="col">
            <dt>Term period</dt>
            <dd id="sd-ov-term"></dd>
          </div>
          <div class="col">
            <dt>Documentary deadline</dt>
            <dd id="sd-ov-deadline"></dd>
          </div>
        </dl>
      </section>

      <!-- 2. Required documents (highest priority) -->
      <section class="card docs-card docs-card-summary card-panel" aria-labelledby="docs-title">
        <div class="docs-summary">
          <div class="compact-progress" id="sd-compact">
            <strong id="sd-sum-fraction"></strong>
            <span>submitted</span>
          </div>
          <div class="docs-headline">
            <h2 class="docs-title" id="docs-title">Required Documents</h2>
            <p class="docs-progress" id="sd-docs-progress"></p>
            <p class="docs-alert" id="sd-docs-alert"></p>
          </div>
          <!-- Bootstrap: responsive row-cols controls the summary tile layout across breakpoints. -->
          <ul class="sum-tiles" aria-label="Document summary">
            <li class="sum-tile submitted">
              <div class="n" id="sd-n-submitted"></div>
              <div class="l">Submitted</div>
            </li>
            <li class="sum-tile pending">
              <div class="n" id="sd-n-pending"></div>
              <div class="l">Pending</div>
            </li>
            <li class="sum-tile review">
              <div class="n" id="sd-n-review"></div>
              <div class="l">Under Review</div>
            </li>
            <li class="sum-tile rejected">
              <div class="n" id="sd-n-rejected"></div>
              <div class="l">Rejected</div>
            </li>
            <li class="sum-tile total">
              <div class="n" id="sd-n-total"></div>
              <div class="l">Total</div>
            </li>
          </ul>
        </div>
      </section>

      <section class="card docs-card docs-card-list card-panel">
        <div class="docs-body" id="sd-doc-groups"></div>
      </section>

      <!-- 3. Maintaining requirements (scholarship_maintenance_requirements, grouped by category) -->
      <section class="card acc-card collapsible-card card-panel" aria-labelledby="maint-title" id="maint-card">
        <button
          type="button"
          class="card-head card-head-btn"
          id="maint-toggle"
          aria-expanded="true"
          aria-controls="maint-body">
          <div>
            <h3 id="maint-title">Maintaining Requirements</h3>
            <div class="card-sub">What you need to do to keep your scholarship active</div>
          </div>
        </button>
        <div class="card-collapse" id="maint-body" role="region" aria-labelledby="maint-toggle">
          <div class="card-collapse-inner">
            <div class="acc-groups" id="sd-maint-groups"></div>
          </div>
        </div>
      </section>
    </main>
    <main class="main main-content dash-main" id="view-documents" hidden>
      <div class="header page-header">
        <h1>Documents</h1>
        <time class="date-pill" datetime="2026-09-19">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
          </svg>
          <strong class="js-date">SEPTEMBER 19, 2026</strong> · <span class="js-time">12:00AM</span>
        </time>
      </div>

      <section class="card docs-card card-panel" aria-labelledby="docs-page-title">
        <div class="docs-summary">
          <div class="compact-progress" aria-label="documents submitted">
            <strong id="docs-ring-pct"></strong>
            <span>submitted</span>
          </div>
          <div class="docs-headline">
            <h2 class="docs-title" id="docs-page-title">Required Documents</h2>
            <p class="docs-scholarship" id="docs-scholarship"></p>
            <p class="docs-alert" id="docs-alert">
              <span class="req-dot" id="docs-alert-dot">!</span>
              <span id="docs-alert-text"></span>
            </p>
            <p class="docs-note" id="docs-note"></p>
          </div>
        </div>

        <div class="docs-body">
          <ul class="acc-list" id="docs-acc"></ul>
        </div>
      </section>
      <div class="sr-only" id="docs-live" aria-live="polite"></div>
    </main>
    <main class="main main-content dash-main" id="view-benefits" hidden>
      <div class="header page-header">
        <h1>Benefits</h1>
        <time class="date-pill" datetime="2026-09-19">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
          </svg>
          <strong class="js-date">SEPTEMBER 19, 2026</strong> · <span class="js-time">12:00AM</span>
        </time>
      </div>

      <div class="ben-split">
      <!-- 1. Stipend (filled from the scholarship's data below) -->
      <section class="card docs-card card-panel" aria-labelledby="stipend-title">
        <div class="docs-summary">
          <div class="docs-headline">
            <h2 class="docs-title" id="stipend-title">Stipend</h2>
          </div>
        </div>
        <div class="docs-body">
          <!-- Bootstrap: .row/.row-cols-1 keeps the stipend tile full width. -->
          <ul class="ben-grid single row row-cols-1 g-3" id="stipend-grid"></ul>
        </div>
      </section>

      <!-- 2. Other benefits (list comes from the scholarship's data below) -->
      <section class="card docs-card card-panel" aria-labelledby="other-title">
        <div class="docs-summary">
          <div class="docs-headline">
            <h2 class="docs-title" id="other-title">Other Benefits</h2>
          </div>
        </div>
        <div class="docs-body">
          <!-- Same container design as the stipend: one featured tile per benefit. -->
          <ul class="ben-grid single row row-cols-1 g-3" id="other-grid"></ul>
        </div>
      </section>
      </div>
    </main>
    <main class="main main-content dash-main" id="view-notifications" hidden>
      <div class="header page-header">
        <h1>Notifications</h1>
        <time class="date-pill" datetime="2026-09-19">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
          </svg>
          <strong class="js-date">SEPTEMBER 19, 2026</strong> · <span class="js-time">12:00AM</span>
        </time>
      </div>

      <section class="card notif-panel card-panel" aria-labelledby="notif-page-title">
        <div class="card-head">
          <div>
            <h3 id="notif-page-title">All Notifications</h3>
            <div class="card-sub" id="notif-unread-count"></div>
          </div>
        </div>
        <ul class="notif-list notif-page-list" id="notif-full-list"></ul>
      </section>
    </main>

    <!-- Profile view: read-only scholar details, grouped -->
    <main class="main main-content dash-main" id="view-profile" hidden>
      <div class="header page-header profile-header">
        <div>
          <h1>Profile</h1>
          <p class="profile-header-sub">View and manage your scholar information.</p>
        </div>
        <div class="profile-header-actions">
          <button type="button" class="btn profile-edit-btn" aria-label="Edit Profile">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 20h9" />
              <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
            </svg>
            Edit Profile
          </button>
          <time class="date-pill" datetime="2026-09-19">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <path d="M16 2v4M8 2v4M3 10h18" />
            </svg>
            <strong class="js-date">SEPTEMBER 19, 2026</strong> · <span class="js-time">12:00AM</span>
          </time>
        </div>
      </div>

      <!-- Identity summary: who the scholar is and which program/ID they hold,
             at a glance, before the full detail sections below. -->
      <section class="card card-panel" aria-labelledby="profile-hero-name">
        <div class="profile-hero-row">
          <div class="avatar profile-hero-avatar" id="pf-avatar"></div>
          <div class="profile-hero-text">
            <div class="profile-hero-name" id="profile-hero-name"></div>
            <div class="profile-hero-meta" id="pf-meta"></div>
            <div class="profile-hero-badges">
              <span class="scholar-year-pill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 10 12 5 2 10l10 5 10-5Z" />
                  <path d="M6 12.5V17c0 1.5 3 3 6 3s6-1.5 6-3v-4.5" />
                </svg>
                <span id="pf-year"></span>
              </span>
              <span class="profile-id-pill">Scholar ID: <span id="pf-scholar-no"></span></span>
            </div>
          </div>
          <div class="profile-hero-program">
            <span class="profile-field-label">Scholarship Program</span><br />
            <span class="profile-field-value" id="pf-scholarship"></span>
          </div>
        </div>
      </section>

      <!-- Full detail sections: each is a standard card, two per row on
             desktop, stacking on smaller screens. -->
      <div class="profile-category-grid row row-cols-1 row-cols-md-2 g-3">
        <div class="col">
          <section class="card h-100 card-panel" aria-labelledby="profile-personal">
            <div class="card-head">
              <div>
                <h3 id="profile-personal">Personal Information</h3>
                <div class="card-sub">Who you are</div>
              </div>
            </div>
            <div class="profile-fields" id="pf-personal"></div>
          </section>
        </div>

        <div class="col">
          <section class="card h-100 card-panel" aria-labelledby="profile-contact">
            <div class="card-head">
              <div>
                <h3 id="profile-contact">Contact Information</h3>
                <div class="card-sub">How the scholarship office reaches you</div>
              </div>
            </div>
            <div class="profile-fields" id="pf-contact"></div>
          </section>
        </div>

        <div class="col">
          <section class="card h-100 card-panel" aria-labelledby="profile-school">
            <div class="card-head">
              <div>
                <h3 id="profile-school">School Information</h3>
                <div class="card-sub">Your current academic information</div>
              </div>
            </div>
            <div class="profile-fields" id="pf-school"></div>
          </section>
        </div>

        <div class="col">
          <section class="card h-100 card-panel" aria-labelledby="profile-address">
            <div class="card-head">
              <div>
                <h3 id="profile-address">Address Information</h3>
                <div class="card-sub">Where you currently live</div>
              </div>
            </div>
            <div class="profile-fields" id="pf-address"></div>
          </section>
        </div>
      </div>
    </main>

    <!-- Settings view: only Edit Password and Logout, as requested -->
    <main class="main main-content dash-main" id="view-settings" hidden>
      <div class="header page-header">
        <h1>Settings</h1>
        <time class="date-pill" datetime="2026-09-19">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
          </svg>
          <strong class="js-date">SEPTEMBER 19, 2026</strong> · <span class="js-time">12:00AM</span>
        </time>
      </div>

      <section class="card card-panel" id="password-card" aria-labelledby="password-title">
        <div class="card-head">
          <div>
            <h3 id="password-title">Edit Password</h3>
            <div class="card-sub">Optional. Change your password only if you want to.</div>
          </div>
          <button type="button" class="doc-btn btn quiet sm" id="password-toggle" aria-expanded="false" aria-controls="password-form">Change Password</button>
        </div>
        <div class="form-msg" id="password-msg" role="alert" hidden></div>
        <form id="password-form" novalidate hidden>
          <div class="form-field">
            <label class="form-label" for="current-password">Current Password</label>
            <input class="form-control" type="password" id="current-password" autocomplete="current-password" />
          </div>
          <div class="form-field">
            <label class="form-label" for="new-password">New Password</label>
            <input class="form-control" type="password" id="new-password" autocomplete="new-password" />
          </div>
          <div class="form-field">
            <label class="form-label" for="confirm-password">Confirm New Password</label>
            <input class="form-control" type="password" id="confirm-password" autocomplete="new-password" />
          </div>
          <div class="form-actions d-flex flex-wrap gap-2">
            <!-- Bootstrap: .d-flex/.flex-wrap/.gap-2 handles responsive button spacing. -->
            <button type="submit" class="doc-btn btn primary" id="password-submit">Update Password</button>
            <button type="button" class="doc-btn btn quiet" id="password-cancel">Cancel</button>
          </div>
        </form>
      </section>

      <section class="lgc-card" aria-labelledby="logout-title-card">
        <div>
          <div class="lgc-title">
            <div class="lgc-title-left">
              <svg class="lgc-icon" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
              </svg>
              <span id="logout-title-card">Log Out</span>
            </div>
          </div>
          <p class="lgc-note">End your current session on this device.</p>
        </div>
        <button type="button" class="lgc-btn" id="logout-open">
          <svg class="lgc-btn-icon" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
            <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
          </svg>Log Out
        </button>
      </section>
    </main>
  </div>

  <div class="modal-backdrop" id="upload-backdrop" hidden>
    <div
      class="modal"
      id="upload-modal"
      role="dialog"
      aria-modal="true"
      aria-labelledby="upload-title"
      aria-describedby="upload-doc-name">
      <div class="modal-head">
        <h3 id="upload-title">Upload Document</h3>
        <div class="modal-doc">
          <span class="doc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <path d="M14 2v6h6M8 13h8M8 17h5" />
            </svg></span>
          <div>
            <div class="modal-doc-name" id="upload-doc-name"></div>
            <div class="modal-doc-meta" id="upload-doc-meta"></div>
          </div>
        </div>
        <button type="button" class="modal-close" id="upload-close" aria-label="Close upload dialog">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M18 6 6 18M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="modal-body" id="upload-body">
        <p class="modal-current" id="upload-current" hidden></p>

        <div class="dz" id="upload-dz">
          <span class="dz-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <path d="m17 8-5-5-5 5M12 3v12" />
            </svg>
          </span>
          <div class="dz-title">Drag and drop your file here</div>
          <div class="dz-sub">or browse from your device</div>
          <button type="button" class="doc-btn btn primary" id="upload-browse">Browse Files</button>
          <div class="dz-hint">PDF, JPG, PNG, DOC or DOCX. Up to 10 MB.</div>
        </div>

        <div class="file-card" id="upload-file" hidden>
          <span class="doc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <path d="M14 2v6h6" />
            </svg></span>
          <div class="doc-text">
            <div class="doc-name" id="upload-file-name"></div>
            <div class="doc-meta" id="upload-file-meta"></div>
          </div>
          <div class="file-card-actions">
            <button type="button" class="doc-btn btn quiet sm" id="upload-change">Change</button>
            <button type="button" class="doc-btn btn quiet sm" id="upload-remove">Remove</button>
          </div>
        </div>

        <div class="modal-error" id="upload-error" role="alert" hidden>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v4.5M12 16h.01" />
          </svg>
          <span id="upload-error-text"></span>
        </div>

        <div class="upload-progress" id="upload-progress" hidden><span></span></div>

        <input
          type="file"
          id="upload-input"
          accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
          hidden />
      </div>

      <div class="modal-foot">
        <button type="button" class="doc-btn btn quiet" id="upload-cancel">Cancel</button>
        <button type="button" class="doc-btn btn primary" id="upload-submit">Upload</button>
      </div>
    </div>
  </div>

  <!-- Document preview -->
  <div class="modal-backdrop preview-backdrop" id="preview-backdrop" hidden>
    <div class="modal preview-modal" id="preview-modal" role="dialog" aria-modal="true" aria-labelledby="preview-name">
      <div class="modal-head">
        <h3>Preview</h3>
        <div class="modal-doc">
          <div>
            <div class="modal-doc-name" id="preview-name"></div>
            <div class="modal-doc-meta" id="preview-meta"></div>
          </div>
        </div>
        <button type="button" class="modal-close" id="preview-close" aria-label="Close preview">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M18 6 6 18M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="modal-body preview-body" id="preview-stage"></div>
      <div class="modal-foot">
        <button type="button" class="doc-btn btn quiet" id="preview-download">Download</button>
        <button type="button" class="doc-btn btn primary" id="preview-done">Close</button>
      </div>
    </div>
  </div>

  <!-- Logout preloader: same brand pulse as the home page's opening preloader -->
  <div id="nxPreloader" class="nx-preloader nx-preloader-hide" role="status" aria-live="polite" aria-hidden="true">
    <div class="nx-preloader-mark"></div>
    <span>BAKR</span>
  </div>

  <!-- Logout confirmation modal (reuses the same .modal-backdrop / .modal styles as the upload dialog above) -->
  <div class="modal-backdrop" id="logout-backdrop" hidden>
    <div
      class="modal modal-logout"
      id="logout-modal"
      role="dialog"
      aria-modal="true"
      aria-labelledby="logout-modal-title">
      <div class="modal-head">
        <h3 id="logout-modal-title">Log out?</h3>
        <button type="button" class="modal-close" id="logout-close" aria-label="Close">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M18 6 6 18M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="modal-body">
        <p class="modal-current">You will be signed out of your scholar account on this device.</p>
      </div>
      <div class="modal-foot">
        <button type="button" class="doc-btn btn quiet" id="logout-cancel">Cancel</button>
        <button type="button" class="doc-btn btn-logout btn danger" id="logout-confirm"> <a href="{{ url('/scholarLogin') }}">Log out</a></button>
      </div>
    </div>
  </div>





<script src="{{ asset('js/bakr-scholar-dashboard.js') }}"></script>
</body>

</html>