<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | DNSC Scholarship System</title>

<link rel="icon" type="image/png" href="{{ asset('images/logo/bakr-logo.png') }}">
<!-- Bootstrap 5.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('css/bakr-admin-dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/bakr-dashboard.css') }}">
</head>
<body class="dash">

<!-- ============================================================
     MOBILE TOP BAR
     ============================================================ -->
<div class="mobile-topbar d-lg-none">
  <button class="btn btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-label="Open navigation">
    <i class="bi bi-list fs-4 u-text-ink"></i>
  </button>
  <div class="d-flex align-items-center gap-2 mobile-topbar-brand">
    <div class="brand-badge mobile-brand-mark"><i class="bi bi-shield-lock-fill"></i></div>
    <span class="fw-semibold mobile-topbar-title">Admin Portal</span>
  </div>
  <button class="btn btn-sm p-0" type="button" onclick="showToast('No new notifications', 'You are all caught up for today.')" aria-label="Notifications">
    <i class="bi bi-bell fs-5 u-text-green"></i>
  </button>
</div>

<!-- ============================================================
     OFFCANVAS SIDEBAR (mobile / tablet)
     ============================================================ -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-label="Mobile navigation">
  <div class="offcanvas-header border-bottom">
    <div class="brand offcanvas-brand">
      <div class="brand-badge"><i class="bi bi-shield-lock-fill"></i></div>
      <div class="brand-text">
        <div class="brand-title">Admin Portal</div>
        <div class="brand-sub">BAKR Scholarship Management System</div>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <h6 class="nav-section-label">MENU</h6>
    <nav class="sidebar-nav" id="mobileNavMenu">
      <a href="#" class="nav-link" data-page="dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="#" class="nav-link" data-page="analytics"><i class="bi bi-bar-chart-line"></i> Analytics</a>
      <a href="#" class="nav-link" data-page="manage-scholars"><i class="bi bi-people"></i> Scholars <span class="badge ms-auto nav-counter-badge" data-counter="pending-scholars">5</span></a>
      <a href="#" class="nav-link" data-page="manage-scholarships"><i class="bi bi-mortarboard"></i> Scholarships</a>
      <a href="#" class="nav-link" data-page="officers"><i class="bi bi-person-badge"></i> Officers</a>
      <a href="#" class="nav-link" data-page="settings"><i class="bi bi-gear"></i> Settings</a>
    </nav>
  </div>
</div>

<!-- ============================================================
     AUDIT LOG DRAWER (Dashboard: "Audit Log" trigger)
     Slide-out activity feed, demoted from the main dashboard body
     so it no longer consumes high-fold screen space.
     ============================================================ -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="auditLogDrawer" aria-labelledby="auditLogDrawerLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="auditLogDrawerLabel"><i class="bi bi-clock-history me-2 u-text-green"></i>Audit Log &amp; System Activity</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <p class="text-muted-custom activity-description">Officer &amp; Admin actions, most recent first.</p>
    <div id="activityFeed">
      <!-- Demo entries — replace with a live audit trail -->
      <div class="activity-item">
        <div class="activity-icon act-admin"><i class="bi bi-check-circle-fill"></i></div>
        <div>
          <div class="activity-text"><strong>Admin</strong> approved scholar <strong>Maria Jimenez</strong></div>
          <div class="activity-time">2026-09-26 09:24 · Application: Pending → Approved</div>
        </div>
      </div>
      <div class="activity-item">
        <div class="activity-icon act-system"><i class="bi bi-pencil-fill"></i></div>
        <div>
          <div class="activity-text">Scholarship criteria updated for <strong>DNSC Academic Scholarship</strong></div>
          <div class="activity-time">2026-09-26 07:24</div>
        </div>
      </div>
      <div class="activity-item">
        <div class="activity-icon act-officer"><i class="bi bi-person-plus-fill"></i></div>
        <div>
          <div class="activity-text"><strong>Officer R. Cruz</strong> added student <strong>Maria Jimenez</strong> to CHED TDP</div>
          <div class="activity-time">2026-09-25 14:10</div>
        </div>
      </div>
      <div class="activity-item">
        <div class="activity-icon act-admin"><i class="bi bi-x-circle-fill"></i></div>
        <div>
          <div class="activity-text"><strong>Admin</strong> requested correction for scholar <strong>Miguel Castillo</strong></div>
          <div class="activity-time">2026-09-25 13:45 · Sent back to Officer R. Cruz with remarks</div>
        </div>
      </div>
      <div class="activity-item">
        <div class="activity-icon act-officer"><i class="bi bi-file-earmark-check-fill"></i></div>
        <div>
          <div class="activity-text"><strong>Officer R. Cruz</strong> submitted a scholar registration for <strong>Patricia Navarro</strong></div>
          <div class="activity-time">2026-09-24 10:14</div>
        </div>
      </div>
      <div class="activity-item">
        <div class="activity-icon act-officer"><i class="bi bi-file-earmark-check-fill"></i></div>
        <div>
          <div class="activity-text"><strong>Officer R. Cruz</strong> submitted a scholar registration for <strong>Isabel Fernandez</strong></div>
          <div class="activity-time">2026-09-23 15:05</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     APP SHELL
     ============================================================ -->
<div class="app-shell dash-panel">

  <!-- ---------- DESKTOP SIDEBAR ---------- -->
  <aside class="sidebar dash-side d-none d-lg-flex flex-column">
    <div class="brand">
      <div class="brand-badge"><i class="bi bi-shield-lock-fill"></i></div>
      <div class="brand-text">
        <div class="brand-title">Admin Portal</div>
        <div class="brand-sub">BAKR Scholarship Management System</div>
        <span class="role-badge">ADMIN ACCESS</span>
      </div>
    </div>

    <h6 class="nav-section-label">MENU</h6>
    <nav class="sidebar-nav dash-nav" id="desktopNavMenu">
      <a href="#" class="nav-link" data-page="dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="#" class="nav-link" data-page="analytics"><i class="bi bi-bar-chart-line"></i> Analytics</a>
      <a href="#" class="nav-link" data-page="manage-scholars"><i class="bi bi-people"></i> Scholars <span class="badge ms-auto nav-counter-badge" data-counter="pending-scholars">5</span></a>
      <a href="#" class="nav-link" data-page="manage-scholarships"><i class="bi bi-mortarboard"></i> Scholarships</a>
      <a href="#" class="nav-link" data-page="officers"><i class="bi bi-person-badge"></i> Officers</a>
      <a href="#" class="nav-link" data-page="settings"><i class="bi bi-gear"></i> Settings</a>
    </nav>

    <div class="sidebar-footer dash-user">
      <div class="admin-mini">
        <div class="admin-mini-avatar">A</div>
        <div>
          <div class="admin-mini-name">Admin User</div>
          <div class="admin-mini-role">System Administrator</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ---------- MAIN CONTENT ---------- -->
  <main class="main-content dash-main">

    <!-- ============================================================
         PAGE: DASHBOARD
         ============================================================ -->
    <section class="page-section" id="page-dashboard">
      <div class="page-header">
        <div>
          <div class="eyebrow">ADMIN WORKSPACE · CURRENT TERM</div>
          <h1>Welcome back, Admin!</h1>
          <p class="text-muted-custom mb-0">Review submitted scholars and keep the scholarship system configured and on track.</p>
        </div>
      </div>

      <!-- Hero Operational Banner -->
      <div class="sd-hero">
        <div>
          <h2>Here's what needs your attention</h2>
          <p class="sd-sub">Scholar submissions are awaiting your review. Approve or return them so the system can create the scholar account. Document submission becomes available after approval.</p>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="quick-action-btn quick-action-btn-primary" onclick="showPage('manage-scholars')">
            <i class="bi bi-person-check"></i> Review Pending Scholars
          </button>
          <button type="button" class="quick-action-btn" onclick="showPage('manage-scholarships')">
            <i class="bi bi-mortarboard"></i> Manage Scholarships
          </button>
        </div>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card dashboard-scholar-filter-card">
          <div class="kpi-top"><div class="kpi-icon icon-green"><i class="bi bi-people-fill"></i></div><button type="button" class="btn-view-sm btn-ui btn-ui--ghost btn-ui--sm" onclick="showPage('manage-scholars')">View</button></div>
          <div class="kpi-label">Total Active Scholars</div><div class="kpi-value" id="dashboardActiveScholarCount">–</div>
          <div class="kpi-sub" id="dashboardScholarSplit">Currently enrolled in active programs</div>
        </div>
        <button type="button" class="kpi-card kpi-highlight" onclick="showPage('manage-scholars')">
          <div class="kpi-top"><div class="kpi-icon icon-gold"><i class="bi bi-person-check-fill"></i></div></div>
          <div class="kpi-label">Pending Approvals</div><div class="kpi-value kpi-value-gold" id="dashboardPendingCount">5</div>
          <div class="kpi-sub">Scholar submissions awaiting review</div>
        </button>
        <button type="button" class="kpi-card" onclick="showPage('manage-scholarships')">
          <div class="kpi-top"><div class="kpi-icon icon-green"><i class="bi bi-mortarboard-fill"></i></div></div>
          <div class="kpi-label">Active Scholarship Programs</div><div class="kpi-value" id="dashboardProgramCount">11</div>
          <div class="kpi-sub">Programs configured for the current term</div>
        </button>
        <button type="button" class="kpi-card" onclick="showPage('officers')">
          <div class="kpi-top"><div class="kpi-icon icon-neutral"><i class="bi bi-person-badge-fill"></i></div></div>
          <div class="kpi-label">Active Officers</div><div class="kpi-value" id="dashboardOfficerCount">4</div>
          <div class="kpi-sub">Officers with system-wide access</div>
        </button>
      </div>

      <div class="card-panel dash-section" id="recentActivityPanel">
        <div class="section-title"><div class="st-left"><i class="bi bi-clock-history"></i><span>Recent Activity</span></div><button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-toggle="offcanvas" data-bs-target="#auditLogDrawer">View audit log</button></div>
        <div class="activity-list">
          <div class="activity-item"><div class="activity-icon act-admin"><i class="bi bi-check-circle-fill"></i></div><div><div class="activity-text"><strong>Admin</strong> approved scholar <strong>Maria Jimenez</strong></div><div class="activity-time">2026-09-26 09:24</div></div></div>
          <div class="activity-item"><div class="activity-icon act-system"><i class="bi bi-pencil-fill"></i></div><div><div class="activity-text"><strong>Admin</strong> updated scholarship <strong>DNSC Academic Scholarship</strong></div><div class="activity-time">2026-09-26 07:24</div></div></div>
          <div class="activity-item"><div class="activity-icon act-officer"><i class="bi bi-person-plus-fill"></i></div><div><div class="activity-text"><strong>Officer R. Cruz</strong> added student <strong>Maria Jimenez</strong> to CHED TDP</div><div class="activity-time">2026-09-25 14:10</div></div></div>
        </div>
      </div>
    </section>

    <!-- ============================================================
         PAGE: ANALYTICS
         ============================================================ -->
    <section class="page-section" id="page-analytics">
      <div class="page-header">
        <div>
          <div class="eyebrow">ADMIN WORKSPACE &middot; ACADEMIC TRACKING</div>
          <h1>Analytics &amp; Insights</h1>
          <p class="text-muted-custom mb-0">Academic tracking, application queues, and compliance insights computed from sample records.</p>
        </div>
        <div class="ph-actions"><div class="sd-date-pill"><i class="bi bi-bar-chart-line"></i><strong>Sample data · as of Oct 13, 2026</strong></div></div>
      </div>

      <div class="kpi-grid">
        <button type="button" class="kpi-card" title="Count of scholarship awards with status Active in the selected scholarship and campus (sample data)." onclick="showPage('manage-scholars')">
          <div class="kpi-top"><div class="kpi-icon icon-green"><i class="bi bi-people-fill"></i></div></div>
          <div class="kpi-label">Total Active Scholars</div><div class="kpi-value" id="kpiTotalScholars">–</div>
          <div class="kpi-sub"><i class="bi bi-calendar-check"></i> <span id="kpiScholarSplit">Active awards · AY 2026-2027, 1st Semester</span></div>
        </button>
        <button type="button" class="kpi-card kpi-highlight" title="Count of scholar applications with status Pending (sample data)." onclick="showPage('manage-scholars')">
          <div class="kpi-top"><div class="kpi-icon icon-gold"><i class="bi bi-inbox-fill"></i></div><span class="status-pill pill-amber" role="status" aria-label="Status: Urgent"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>Urgent</span></div>
          <div class="kpi-label">Pending Applications Queue</div><div class="kpi-value kpi-value-gold" id="kpiPendingQueue">–</div>
          <div class="kpi-sub kpi-trend-warn"><i class="bi bi-exclamation-triangle-fill"></i> <span id="kpiUrgentCount">0</span> require urgent review (&gt; 3 days old)</div>
        </button>
        <button type="button" class="kpi-card" title="Active scholars who have uploaded all documentary requirements for the current term, divided by all active scholars (sample data)." onclick="showPage('manage-scholars')" aria-label="View document renewal compliance details">
          <div class="kpi-top"><div class="kpi-icon icon-neutral"><i class="bi bi-file-earmark-check-fill"></i></div></div>
          <div class="kpi-label">Document Renewal Compliance Rate</div><div class="kpi-value" id="kpiComplianceRate">–</div>
          <div class="kpi-sub kpi-trend-danger"><i class="bi bi-exclamation-circle-fill"></i> <span id="kpiOverdueCount">0</span> scholars with outstanding requirements (deadline 2026-11-15)</div>
        </button>
      </div>

      <div class="analytics-global-filter filter-bar ui-filter-toolbar" role="group" aria-label="Global analytics filters">
        <div class="analytics-filter-group"><label for="analyticsProgram">Scholarship</label><select id="analyticsProgram" onchange="applyAnalyticsFilters()"><option value="all">All scholarships</option></select></div>
        <div class="analytics-filter-group"><label for="analyticsSchool">Campus</label><select id="analyticsSchool" onchange="applyAnalyticsFilters()"><option value="all">All campuses</option></select></div>
        <button type="button" class="btn-export-summary" onclick="exportSummary()" aria-label="Export non-financial audit summary"><i class="bi bi-download"></i> Export Summary</button>
      </div>

      <div class="source-truth-note" id="analyticsActiveFilterNote" role="status"><i class="bi bi-funnel-fill"></i><span id="analyticsActiveFilterText">Showing data for all scholarships across all campuses.</span> <span class="text-muted-custom">· Figures are counts computed from the Scholars list, its requirement submissions and the pending applications; standing percentages are shares of active scholars.</span></div>

      <div class="analytics-chart-stage" id="analyticsChartStage">
        <div class="analytics-widget-grid">
        <div class="card-panel analytics-widget">
          <div class="section-title"><div class="st-left"><i class="bi bi-bar-chart-fill"></i><span>Scholar Distribution by Institute</span></div><span class="text-muted-custom chart-meta">Student Count</span></div>
          <div class="bar-chart" id="instituteBarChart" aria-label="Scholars by institute bar chart"></div>
        </div>
        <div class="card-panel analytics-widget">
          <div class="section-title"><div class="st-left"><i class="bi bi-donut-chart-fill"></i><span>Document Compliance Status</span></div></div>
          <div class="donut-layout">
            <div class="donut-chart" id="standingDonut" role="img" aria-label="Document compliance status: Compliant, Incomplete Documents, Overdue"><div class="donut-center"><strong id="standingGoodPct">–</strong><span>Compliant</span></div></div>
            <div class="chart-legend" aria-label="Document compliance status">
              <div class="legend-row"><span class="legend-dot legend-green"></span>Compliant <strong id="standingGoodCount">–</strong></div>
              <div class="legend-row"><span class="legend-dot legend-gold"></span>Incomplete Documents <strong id="standingIncompleteCount">–</strong></div>
              <div class="legend-row"><span class="legend-dot legend-danger"></span>Overdue Documents <strong id="standingOverdueCount">–</strong></div>
            </div>
          </div>
        </div>
      </div>
        <div class="analytics-loading-overlay" id="analyticsLoadingOverlay" aria-live="polite" aria-hidden="true">
          <div class="analytics-loading-card"><div class="analytics-loading-label"><span class="analytics-spinner"></span> Updating analytics</div><div class="analytics-skeleton-grid"><div class="analytics-skeleton"></div><div class="analytics-skeleton"></div><div class="analytics-skeleton"></div><div class="analytics-skeleton"></div></div></div>
        </div>
        <div class="analytics-empty-state" id="analyticsEmptyState" role="status"><div><i class="bi bi-bar-chart-line"></i><strong>No data available for the selected scholarship type and school.</strong><span>Try switching filters.</span></div></div>
      </div>
    </section>

    <!-- ============================================================
         PAGE: OFFICERS
         ============================================================ -->
    <section class="page-section" id="page-officers">
  <div class="page-header"><div><div class="eyebrow">OFFICER DIRECTORY · SYSTEM ACCESS</div><h1>Manage Officers</h1><p class="text-muted-custom mb-0">Every Officer can access all scholarships, scholars, documents, stipends, and notifications.</p></div></div>
  <div class="card-panel dash-section"><div class="section-title"><div class="st-left"><i class="bi bi-people"></i><span>Officers</span></div><span class="text-muted-custom inline-utility-font-size-76rem">4 officers</span></div><div class="directory-filter-row filter-bar mb-3 ui-filter-toolbar"><div class="directory-filter-group directory-search-group"><label class="directory-filter-label" for="officerDirectorySearch">Search officer</label><input id="officerDirectorySearch" class="directory-search" type="search" placeholder="Search officer..." oninput="filterOfficerDirectory()"></div></div><div class="table-app-wrap table-responsive-wrap"><table class="app-table table-app officer-table" id="officerDirectoryTable"><thead><tr><th scope="col">Officer</th><th scope="col">Email</th><th scope="col">Status</th><th scope="col" class="text-end">Actions</th></tr></thead><tbody>
    <tr class="officer-row" data-search="Officer R. Cruz r.cruz@dnsc.edu.ph"><td><strong>Officer R. Cruz</strong></td><td>r.cruz@dnsc.edu.ph</td><td><span class="status-pill pill-active" role="status" aria-label="Status: Active"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Active</span></td><td class="text-end"><button class="directory-action" onclick="viewOfficer('Officer R. Cruz','r.cruz@dnsc.edu.ph')">View</button><button class="danger-action" onclick="toggleOfficerStatus('Officer R. Cruz','Deactivate')">Deactivate</button></td></tr>
    <tr class="officer-row" data-search="Officer L. Tan l.tan@dnsc.edu.ph"><td><strong>Officer L. Tan</strong></td><td>l.tan@dnsc.edu.ph</td><td><span class="status-pill pill-active" role="status" aria-label="Status: Active"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Active</span></td><td class="text-end"><button class="directory-action" onclick="viewOfficer('Officer L. Tan','l.tan@dnsc.edu.ph')">View</button><button class="danger-action" onclick="toggleOfficerStatus('Officer L. Tan','Deactivate')">Deactivate</button></td></tr>
    <tr class="officer-row" data-search="Officer M. Dela Cruz m.delacruz@dnsc.edu.ph"><td><strong>Officer M. Dela Cruz</strong></td><td>m.delacruz@dnsc.edu.ph</td><td><span class="status-pill pill-active" role="status" aria-label="Status: Active"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Active</span></td><td class="text-end"><button class="directory-action" onclick="viewOfficer('Officer M. Dela Cruz','m.delacruz@dnsc.edu.ph')">View</button><button class="danger-action" onclick="toggleOfficerStatus('Officer M. Dela Cruz','Deactivate')">Deactivate</button></td></tr>
    <tr class="officer-row" data-search="Officer J. Morales j.morales@dnsc.edu.ph"><td><strong>Officer J. Morales</strong></td><td>j.morales@dnsc.edu.ph</td><td><span class="status-pill pill-active" role="status" aria-label="Status: Active"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Active</span></td><td class="text-end"><button class="directory-action" onclick="viewOfficer('Officer J. Morales','j.morales@dnsc.edu.ph')">View</button><button class="danger-action" onclick="toggleOfficerStatus('Officer J. Morales','Deactivate')">Deactivate</button></td></tr>
    <tr class="is-hidden" id="officerNoResults"><td colspan="4"><div class="empty-state empty-state-compact"><i class="bi bi-search"></i><strong>No officers found</strong><span>Try a different search.</span></div></td></tr>
  </tbody></table></div></div>
</section>
<section class="page-section" id="page-settings">
  <div class="page-header"><div><div class="eyebrow">ACCOUNT SETTINGS</div><h1>Settings</h1><p class="text-muted-custom mb-0">Manage your administrator account.</p></div></div>
  <div class="settings-simple-grid mb-3" style="grid-template-columns:1fr;"><div class="card-panel dash-section"><div class="section-title"><div class="st-left"><i class="bi bi-person-circle"></i><span>Admin Profile</span></div></div><div class="settings-profile"><div class="settings-avatar">A</div><div><strong>Admin User</strong><div class="text-muted-custom">admin@dnsc.edu.ph</div><div class="demo-note">System Administrator</div></div></div></div></div>
      <div class="pwc-card mb-3" id="password-card" aria-labelledby="password-title">
        <div class="pwc-head">
          <div>
            <h3 id="password-title">Edit Password</h3>
            <div class="pwc-sub">Optional. Change your password only if you want to.</div>
          </div>
          <button type="button" class="pwc-btn quiet sm" id="password-toggle" aria-expanded="false" aria-controls="password-form">Change Password</button>
        </div>
        <div class="pwc-msg" id="password-msg" role="alert" hidden></div>
        <form id="password-form" class="pwc-form" novalidate hidden>
          <div class="pwc-field">
            <label for="current-password">Current Password</label>
            <input class="pwc-input" type="password" id="current-password" autocomplete="current-password" />
          </div>
          <div class="pwc-field">
            <label for="new-password">New Password</label>
            <input class="pwc-input" type="password" id="new-password" autocomplete="new-password" />
          </div>
          <div class="pwc-field">
            <label for="confirm-password">Confirm New Password</label>
            <input class="pwc-input" type="password" id="confirm-password" autocomplete="new-password" />
          </div>
          <div class="pwc-actions">
            <button type="submit" class="pwc-btn primary" id="password-submit">Update Password</button>
            <button type="button" class="pwc-btn quiet" id="password-cancel">Cancel</button>
          </div>
        </form>
      </div>

      <div class="card-panel sd-account-card sd-logout-card">
        <div>
          <div class="section-title" style="margin-bottom:0.25rem;">
            <div class="st-left"><i class="bi bi-box-arrow-right"></i><span>Log Out</span></div>
          </div>
          <p class="small text-muted-custom mb-0">End your current session on this device.</p>
        </div>
        <button type="button" class="btn btn-academic-red" onclick="handleLogout()">
          <i class="bi bi-box-arrow-right me-2"></i>Log Out
        </button>
      </div>
</section>
<section class="page-section" id="page-manage-scholars">
  <div class="page-header"><div><div class="eyebrow">SYSTEM-WIDE DIRECTORY · CURRENT TERM</div><h1>Manage Scholars</h1><p class="text-muted-custom mb-0">View and manage all scholar records. Officers can access every scholar in the system.</p></div><button class="btn-audit-trigger" data-bs-toggle="offcanvas" data-bs-target="#auditLogDrawer"><i class="bi bi-clock-history"></i> Audit Log</button></div>
  <!-- ============================================================
       DIVISION 1: PENDING SCHOLAR APPROVALS
       ============================================================ -->
  <div class="card-panel scholar-approval-panel" id="scholarApprovalPanel" aria-labelledby="scholarApprovalTitle">
    <div class="section-title"><div class="st-left"><i class="bi bi-person-check"></i><span id="scholarApprovalTitle">Pending Scholar Approvals</span></div><span class="status-pill pill-amber" id="scholarApprovalCount" role="status" aria-label="Status: 5 pending"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>5 pending</span></div>
    <p class="scholar-approval-intro">Scholarship Officers have submitted these registration entries. Review the registration details before creating the scholar account or rejecting the submission. Documents are submitted only after approval.</p>
    <div class="directory-filter-row filter-bar ui-filter-toolbar" role="group" aria-label="Pending approvals toolbar">
      <div class="directory-filter-group directory-search-group"><label class="directory-filter-label" for="pendingSearch">Search scholar / Student ID / program</label><input id="pendingSearch" class="directory-search" type="search" placeholder="Search name, ID, program..." oninput="filterScholarApprovalQueue()"></div>
      <div class="directory-filter-group"><label class="directory-filter-label" for="pendingProgramFilter">Scholarship</label><select class="directory-select" id="pendingProgramFilter" onchange="filterScholarApprovalQueue()"><option value="all">All scholarships</option><optgroup label="DNSC Internal (no stipend)"><option value="__internal">All DNSC Internal</option><option value="dnsc academic">DNSC Academic Scholarship</option><option value="dnsc gia a">DNSC Grant-in-Aid (GIA) – Category A</option><option value="dnsc gia b">DNSC Grant-in-Aid (GIA) – Category B</option><option value="dnsc sa c">DNSC Student Assistant (SA) – Category C</option></optgroup><optgroup label="External scholarships (with stipend)"><option value="__external">All external</option><option value="ched tdp">CHED TDP</option><option value="ched tes">CHED TES</option><option value="cmsp">CHED Merit (CMSP)</option><option value="dost">DOST-SEI Scholarship</option><option value="bpmsp">Bagong Pilipinas (BPMSP)</option><option value="grab">GrabScholar</option><option value="cebuana">Cebuana Lhuillier (CLFI)</option></optgroup></select></div>
      <div class="directory-filter-group"><label class="directory-filter-label" for="pendingSchoolFilter">Campus</label><select class="directory-select" id="pendingSchoolFilter" onchange="filterScholarApprovalQueue()"><option value="all">All campuses</option><option value="Main (Panabo Campus)">Main (Panabo Campus)</option><option value="Carmen Campus">Carmen Campus</option><option value="Samal Campus">Samal Campus</option><option value="Talaingod Campus">Talaingod Campus</option><option value="Asuncion Campus">Asuncion Campus</option></select></div>
      <button class="btn btn-sm btn-outline-scholarship directory-reset btn-ui btn-ui--secondary btn-ui--sm" onclick="clearScholarApprovalFilters()">Reset</button>
    </div>
    <div class="directory-toolbar"><strong><span id="pendingVisibleCount">5</span> pending scholars</strong><span class="scholar-approval-selectall"><label for="pendingSelectAll">Select all</label><input id="pendingSelectAll" type="checkbox" aria-label="Select all pending scholars" onchange="toggleAllPending(this.checked)"></span></div>
    <div class="table-app-wrap table-responsive-wrap">
      <table class="app-table table-app" id="pendingApprovalsTable">
        <thead><tr><th scope="col" class="text-center">&nbsp;</th><th scope="col">Student ID</th><th scope="col">Name</th><th scope="col">Scholarship</th><th scope="col">Campus</th><th scope="col">Program</th><th scope="col" class="text-end">Action</th></tr></thead>
        <tbody id="pendingApprovalsBody">
          <tr id="pendingRow-mj" data-scholar-id="mj" data-program="ched tdp" data-school="Main (Panabo Campus)" data-search="patricia navarro 2023-01034 bachelor of science in information systems (bsis), 3rd yr"><td class="text-center"><input class="row-select" type="checkbox" data-scholar-id="mj" aria-label="Select Patricia Navarro for batch actions" onchange="updateBatchContext()"></td><td>2023-01034</td><td><strong>Patricia Navarro</strong></td><td><span class="program-chip">CHED TDP</span></td><td>Main (Panabo Campus)</td><td title="Bachelor of Science in Information Systems (BSIS)">BSIS</td><td class="text-end"><button class="btn-view-sm btn-ui btn-ui--ghost btn-ui--sm" onclick="openQuickView(this.closest('tr').dataset.scholarId)"><i class="bi bi-eye"></i> Review</button> <button class="btn-approve-sm btn-ui btn-ui--primary btn-ui--sm" onclick="openQuickView('mj')"><i class="bi bi-check2"></i> Approve</button> <button class="btn-ineligible-sm btn-ui btn-ui--danger btn-ui--sm" onclick="rejectIneligible('Patricia Navarro')"><i class="bi bi-x-lg"></i> Reject</button></td></tr>
          <tr id="pendingRow-jd" data-scholar-id="jd" data-program="dnsc academic" data-school="Carmen Campus" data-search="emmanuel torres 2022-00941 bachelor of science in criminology (bscrim), 4th yr"><td class="text-center"><input class="row-select" type="checkbox" data-scholar-id="jd" aria-label="Select Emmanuel Torres for batch actions" onchange="updateBatchContext()"></td><td>2022-00941</td><td><strong>Emmanuel Torres</strong></td><td><span class="program-chip">DNSC Academic</span></td><td>Carmen Campus</td><td title="Bachelor of Science in Criminology (BSCrim)">BSCrim</td><td class="text-end"><button class="btn-view-sm btn-ui btn-ui--ghost btn-ui--sm" onclick="openQuickView(this.closest('tr').dataset.scholarId)"><i class="bi bi-eye"></i> Review</button> <button class="btn-approve-sm btn-ui btn-ui--primary btn-ui--sm" onclick="openQuickView('jd')"><i class="bi bi-check2"></i> Approve</button> <button class="btn-ineligible-sm btn-ui btn-ui--danger btn-ui--sm" onclick="rejectIneligible('Emmanuel Torres')"><i class="bi bi-x-lg"></i> Reject</button></td></tr>
          <tr id="pendingRow-kr" data-scholar-id="kr" data-program="dost" data-school="Samal Campus" data-search="isabel fernandez 2024-00215 bachelor of science in fisheries and aquatic sciences (bsfas), 2nd yr"><td class="text-center"><input class="row-select" type="checkbox" data-scholar-id="kr" aria-label="Select Isabel Fernandez for batch actions" onchange="updateBatchContext()"></td><td>2024-00215</td><td><strong>Isabel Fernandez</strong></td><td><span class="program-chip">DOST-SEI</span></td><td>Samal Campus</td><td title="Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)">BSFAS</td><td class="text-end"><button class="btn-view-sm btn-ui btn-ui--ghost btn-ui--sm" onclick="openQuickView(this.closest('tr').dataset.scholarId)"><i class="bi bi-eye"></i> Review</button> <button class="btn-approve-sm btn-ui btn-ui--primary btn-ui--sm" onclick="openQuickView('kr')"><i class="bi bi-check2"></i> Approve</button> <button class="btn-ineligible-sm btn-ui btn-ui--danger btn-ui--sm" onclick="rejectIneligible('Isabel Fernandez')"><i class="bi bi-x-lg"></i> Reject</button></td></tr>
          <tr id="pendingRow-ab" data-scholar-id="ab" data-program="ched tdp" data-school="Talaingod Campus" data-search="miguel castillo 2026-00388 bachelor of public administration (bpa), 1st yr"><td class="text-center"><input class="row-select" type="checkbox" data-scholar-id="ab" aria-label="Select Miguel Castillo for batch actions" onchange="updateBatchContext()"></td><td>2026-00388</td><td><strong>Miguel Castillo</strong></td><td><span class="program-chip">CHED TDP</span></td><td>Talaingod Campus</td><td title="Bachelor of Public Administration (BPA)">BPA</td><td class="text-end"><button class="btn-view-sm btn-ui btn-ui--ghost btn-ui--sm" onclick="openQuickView(this.closest('tr').dataset.scholarId)"><i class="bi bi-eye"></i> Review</button> <button class="btn-approve-sm btn-ui btn-ui--primary btn-ui--sm" onclick="openQuickView('ab')"><i class="bi bi-check2"></i> Approve</button> <button class="btn-ineligible-sm btn-ui btn-ui--danger btn-ui--sm" onclick="rejectIneligible('Miguel Castillo')"><i class="bi bi-x-lg"></i> Reject</button></td></tr>
          <tr id="pendingRow-ra" data-scholar-id="ra" data-program="bpmsp" data-school="Main (Panabo Campus)" data-search="rochelle d. abella 2026-00387 bachelor of science in food technology (bsft), 1st yr"><td class="text-center"><input class="row-select" type="checkbox" data-scholar-id="ra" aria-label="Select Rochelle D. Abella for batch actions" onchange="updateBatchContext()"></td><td>2026-00387</td><td><strong>Rochelle D. Abella</strong></td><td><span class="program-chip">Bagong Pilipinas (BPMSP)</span></td><td>Main (Panabo Campus)</td><td title="Bachelor of Science in Food Technology (BSFT)">BSFT</td><td class="text-end"><button class="btn-view-sm btn-ui btn-ui--ghost btn-ui--sm" onclick="openQuickView(this.closest('tr').dataset.scholarId)"><i class="bi bi-eye"></i> Review</button> <button class="btn-approve-sm btn-ui btn-ui--primary btn-ui--sm" onclick="openQuickView('ra')"><i class="bi bi-check2"></i> Approve</button> <button class="btn-ineligible-sm btn-ui btn-ui--danger btn-ui--sm" onclick="rejectIneligible('Rochelle D. Abella')"><i class="bi bi-x-lg"></i> Reject</button></td></tr>
          <tr class="empty-state-row is-hidden" id="pendingEmptyRow"><td colspan="7"><div class="empty-state"><i class="bi bi-check-circle"></i><strong>No pending approvals</strong><span>There are currently no scholars waiting for admin approval.</span></div></td></tr>
          <tr class="is-hidden" id="pendingNoResults"><td colspan="7"><div class="empty-state empty-state-compact"><i class="bi bi-search"></i><strong>No pending scholars found</strong><span>Try a different search or filter.</span></div></td></tr>
        </tbody>
      </table>
    </div>
    <div class="batch-context-bar" id="pendingBatchBar" role="region" aria-live="polite"><span><strong id="pendingSelectedCount">0</strong> selected</span><div class="batch-context-actions"><button type="button" class="sd-btn sd-btn-sm sd-btn-primary" onclick="approveSelectedPending()"><i class="bi bi-check2"></i> Approve Selected</button><button type="button" class="sd-btn sd-btn-sm sd-btn-secondary" onclick="exportSelectedPending()"><i class="bi bi-download"></i> Export Selected</button></div></div>
  </div>

  <!-- ============================================================
       DIVISION 2: ALREADY SCHOLARS (approved, active roster)
       ============================================================ -->
  <div class="card-panel scholar-approval-panel" id="alreadyScholarsPanel" aria-labelledby="alreadyScholarsTitle">
    <div class="section-title"><div class="st-left"><i class="bi bi-people"></i><span id="alreadyScholarsTitle">Scholars</span></div><span class="status-pill pill-active" id="scholarDirectoryCountPill" role="status" aria-label="Status: 8 scholars"><i class="bi bi-people-fill" aria-hidden="true"></i>8 scholars</span></div>
    <p class="scholar-approval-intro">Approved scholars in the active roster. Search or filter by scholarship type, school, verification status and stipend status.</p>
    <div class="directory-filter-row filter-bar ui-filter-toolbar" role="group" aria-label="Already scholars toolbar"><div class="directory-filter-group directory-search-group"><label class="directory-filter-label" for="scholarDirectorySearch">Search scholar / Student ID / Program</label><input id="scholarDirectorySearch" class="directory-search" type="search" placeholder="Search scholars..." oninput="filterScholarDirectory()"></div><div class="directory-filter-group"><label class="directory-filter-label" for="scholarProgramFilter">Scholarship</label><select class="directory-select" id="scholarProgramFilter" onchange="filterScholarDirectory()"><option value="all">All scholarships</option><optgroup label="DNSC Internal (no stipend)"><option value="__internal">All DNSC Internal</option><option value="dnsc academic">DNSC Academic Scholarship</option><option value="dnsc gia a">DNSC Grant-in-Aid (GIA) – Category A</option><option value="dnsc gia b">DNSC Grant-in-Aid (GIA) – Category B</option><option value="dnsc sa c">DNSC Student Assistant (SA) – Category C</option></optgroup><optgroup label="External scholarships (with stipend)"><option value="__external">All external</option><option value="ched tdp">CHED TDP</option><option value="ched tes">CHED TES</option><option value="cmsp">CHED Merit (CMSP)</option><option value="dost">DOST-SEI Scholarship</option><option value="bpmsp">Bagong Pilipinas (BPMSP)</option><option value="grab">GrabScholar</option><option value="cebuana">Cebuana Lhuillier (CLFI)</option></optgroup></select></div><div class="directory-filter-group"><label class="directory-filter-label" for="scholarSchoolFilter">Campus</label><select class="directory-select" id="scholarSchoolFilter" onchange="filterScholarDirectory()"><option value="all">All campuses</option><option value="Main (Panabo Campus)">Main (Panabo Campus)</option><option value="Carmen Campus">Carmen Campus</option><option value="Samal Campus">Samal Campus</option><option value="Talaingod Campus">Talaingod Campus</option><option value="Asuncion Campus">Asuncion Campus</option></select></div><div class="directory-filter-group"><label class="directory-filter-label" for="scholarVerificationFilter">Verification Status</label><select class="directory-select" id="scholarVerificationFilter" onchange="filterScholarDirectory()"><option value="all">All statuses</option><option value="Verified">Verified</option><option value="Needs Review">Needs Review</option><option value="Pending">Pending</option></select></div><div class="directory-filter-group"><label class="directory-filter-label" for="scholarStipendFilter">Stipend Status</label><select class="directory-select" id="scholarStipendFilter" onchange="filterScholarDirectory()"><option value="all">All</option><option value="Released">Disbursed</option><option value="Ready">Ready</option><option value="Not Eligible">Not Eligible</option><option value="Not Applicable">Not Applicable (DNSC Internal)</option></select></div><button class="btn btn-sm btn-outline-scholarship directory-reset btn-ui btn-ui--secondary btn-ui--sm" onclick="clearScholarDirectoryFilters()">Reset</button></div>
    <div class="directory-toolbar"><strong><span id="directoryVisibleCount">8</span> scholars</strong><span class="text-muted-custom">All Officers have system-wide scholar access.</span></div>
    <div class="table-app-wrap table-responsive-wrap"><table class="app-table table-app" id="scholarDirectoryTable"><thead><tr><th scope="col">Student ID</th><th scope="col">Name</th><th scope="col">Scholarship</th><th scope="col">Campus</th><th scope="col">Program</th><th scope="col">Verification Status</th><th scope="col">Stipend Status</th><th scope="col" class="text-end">Actions</th></tr></thead><tbody>
      <tr class="directory-row" data-search="Maria Jimenez 2023-00456 Bachelor of Science in Information Systems (BSIS)" data-school="Main (Panabo Campus)" data-verification="Verified" data-stipend="Released" data-program="ched tdp" data-status-group="active"><td>2023-00456</td><td><strong>Maria Jimenez</strong></td><td><span class="program-chip">CHED TDP</span></td><td>Main (Panabo Campus)</td><td title="Bachelor of Science in Information Systems (BSIS)">BSIS</td><td><span class="lifecycle-pill active" role="status" aria-label="Verification status: Verified"><i class="bi bi-patch-check-fill" aria-hidden="true"></i>Verified</span></td><td><span class="lifecycle-pill active" role="status" aria-label="Stipend status: Disbursed"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Disbursed</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('Maria Jimenez','2023-00456')"><i class="bi bi-eye"></i> Details</button></td></tr>
      <tr class="directory-row" data-search="Jaymar Delos Santos 2022-00189 Bachelor of Science in Criminology (BSCrim)" data-school="Carmen Campus" data-verification="Needs Review" data-stipend="Not Applicable" data-program="dnsc academic" data-status-group="attention"><td>2022-00189</td><td><strong>Jaymar Delos Santos</strong></td><td><span class="program-chip">DNSC Academic</span></td><td>Carmen Campus</td><td title="Bachelor of Science in Criminology (BSCrim)">BSCrim</td><td><span class="lifecycle-pill review" role="status" aria-label="Verification status: Needs Review"><i class="bi bi-eye-fill" aria-hidden="true"></i>Needs Review</span></td><td><span class="lifecycle-pill archived" role="status" aria-label="Stipend status: Not Applicable"><i class="bi bi-dash-circle" aria-hidden="true"></i>Not Applicable</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('Jaymar Delos Santos','2022-00189')"><i class="bi bi-eye"></i> Details</button></td></tr>
      <tr class="directory-row" data-search="Kristine Robles 2023-00812 Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)" data-school="Samal Campus" data-verification="Verified" data-stipend="Ready" data-program="dost" data-status-group="active"><td>2023-00812</td><td><strong>Kristine Robles</strong></td><td><span class="program-chip">DOST-SEI</span></td><td>Samal Campus</td><td title="Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)">BSFAS</td><td><span class="lifecycle-pill active" role="status" aria-label="Verification status: Verified"><i class="bi bi-patch-check-fill" aria-hidden="true"></i>Verified</span></td><td><span class="lifecycle-pill review" role="status" aria-label="Stipend status: Ready"><i class="bi bi-hourglass-split" aria-hidden="true"></i>Ready</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('Kristine Robles','2023-00812')"><i class="bi bi-eye"></i> Details</button></td></tr>
      <tr class="directory-row" data-search="Ana Bautista 2023-00901 Bachelor of Public Administration (BPA)" data-school="Talaingod Campus" data-verification="Pending" data-stipend="Not Eligible" data-program="ched tdp" data-status-group="attention"><td>2023-00901</td><td><strong>Ana Bautista</strong></td><td><span class="program-chip">CHED TDP</span></td><td>Talaingod Campus</td><td title="Bachelor of Public Administration (BPA)">BPA</td><td><span class="lifecycle-pill revision" role="status" aria-label="Verification status: Pending"><i class="bi bi-hourglass-split" aria-hidden="true"></i>Pending</span></td><td><span class="lifecycle-pill archived" role="status" aria-label="Stipend status: Not Eligible"><i class="bi bi-dash-circle" aria-hidden="true"></i>Not Eligible</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('Ana Bautista','2023-00901')"><i class="bi bi-eye"></i> Details</button></td></tr>
      <tr class="directory-row" data-search="Lorenzo Garcia 2021-00643 Bachelor of Arts in Communication (BACOMM)" data-school="Asuncion Campus" data-verification="Needs Review" data-stipend="Not Applicable" data-program="dnsc gia a" data-status-group="attention"><td>2021-00643</td><td><strong>Lorenzo Garcia</strong></td><td><span class="program-chip">DNSC Grant-in-Aid (GIA) – Category A</span></td><td>Asuncion Campus</td><td title="Bachelor of Arts in Communication (BACOMM)">BACOMM</td><td><span class="lifecycle-pill review" role="status" aria-label="Verification status: Needs Review"><i class="bi bi-eye-fill" aria-hidden="true"></i>Needs Review</span></td><td><span class="lifecycle-pill archived" role="status" aria-label="Stipend status: Not Applicable"><i class="bi bi-dash-circle" aria-hidden="true"></i>Not Applicable</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('Lorenzo Garcia','2021-00643')"><i class="bi bi-eye"></i> Details</button></td></tr>
      <tr class="directory-row" data-search="Sofia Mercado 2020-00318 Bachelor of Science in Food Technology (BSFT)" data-school="Main (Panabo Campus)" data-verification="Verified" data-stipend="Released" data-program="dost" data-status-group="active"><td>2020-00318</td><td><strong>Sofia Mercado</strong></td><td><span class="program-chip">DOST-SEI</span></td><td>Main (Panabo Campus)</td><td title="Bachelor of Science in Food Technology (BSFT)">BSFT</td><td><span class="lifecycle-pill active" role="status" aria-label="Verification status: Verified"><i class="bi bi-patch-check-fill" aria-hidden="true"></i>Verified</span></td><td><span class="lifecycle-pill active" role="status" aria-label="Stipend status: Disbursed"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Disbursed</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('Sofia Mercado','2020-00318')"><i class="bi bi-eye"></i> Details</button></td></tr>
      <tr class="directory-row" data-search="Daniel Reyes 2019-00207 Bachelor of Secondary Education (BSeD) - Mathematics" data-school="Carmen Campus" data-verification="Verified" data-stipend="Released" data-program="ched tdp" data-status-group="active"><td>2019-00207</td><td><strong>Daniel Reyes</strong></td><td><span class="program-chip">CHED TDP</span></td><td>Carmen Campus</td><td title="Bachelor of Secondary Education (BSeD) - Mathematics">BSeD</td><td><span class="lifecycle-pill active" role="status" aria-label="Verification status: Verified"><i class="bi bi-patch-check-fill" aria-hidden="true"></i>Verified</span></td><td><span class="lifecycle-pill active" role="status" aria-label="Stipend status: Disbursed"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>Disbursed</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('Daniel Reyes','2019-00207')"><i class="bi bi-eye"></i> Details</button></td></tr>
      <tr class="directory-row" data-search="Carla Villanueva 2022-00776 Bachelor of Science in Social Work (BSSW)" data-school="Samal Campus" data-verification="Pending" data-stipend="Not Applicable" data-program="dnsc sa c" data-status-group="attention"><td>2022-00776</td><td><strong>Carla Villanueva</strong></td><td><span class="program-chip">DNSC Student Assistant (SA) – Category C</span></td><td>Samal Campus</td><td title="Bachelor of Science in Social Work (BSSW)">BSSW</td><td><span class="lifecycle-pill revision" role="status" aria-label="Verification status: Pending"><i class="bi bi-hourglass-split" aria-hidden="true"></i>Pending</span></td><td><span class="lifecycle-pill archived" role="status" aria-label="Stipend status: Not Applicable"><i class="bi bi-dash-circle" aria-hidden="true"></i>Not Applicable</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('Carla Villanueva','2022-00776')"><i class="bi bi-eye"></i> Details</button></td></tr>
      <tr class="is-hidden" id="directoryNoResults"><td colspan="8"><div class="empty-state empty-state-compact"><i class="bi bi-search"></i><strong>No scholars found</strong><span>Try a different search or filter.</span></div></td></tr>
    </tbody></table></div>
  </div>
</section>

<section class="page-section" id="page-manage-scholarships">
  <div class="page-header"><div><div class="eyebrow">PROGRAM ADMINISTRATION · SOURCE OF TRUTH</div><h1>Manage Scholarships</h1><p class="text-muted-custom mb-0">Each scholarship has one configuration used by Scholar and Officer workflows.</p></div><button class="btn-quick-primary btn-ui btn-ui--primary btn-ui--md" onclick="openProgramCreateModal()"><i class="bi bi-plus-circle"></i> Add Scholarship</button></div>
  <div class="source-truth-note"><i class="bi bi-info-circle-fill"></i><span><strong>Source of truth:</strong> Basic information, eligibility, stipend, benefits, required documents, and maintenance requirements are managed in each scholarship configuration.</span></div>
  <div class="section-title"><div class="st-left"><i class="bi bi-grid-3x3-gap-fill"></i><span>Scholarships</span></div><span class="text-muted-custom inline-utility-font-size-76rem" id="programConfigCount">11 active configurations</span></div>
  <div class="simple-scholarship-list" id="adminProgramsGrid">
    <div class="text-muted-custom" style="font-size:.74rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin:.35rem 0 .1rem">DNSC Internal · no stipend</div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>DNSC Academic Scholarship</strong><span>Institutional support &middot; DNS-001</span></div><div class="simple-scholarship-meta"><strong>&#8369;0.00 (no stipend)</strong><span>0 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('DNSC Academic Scholarship')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>DNSC Grant-in-Aid (GIA) – Category A</strong><span>Institutional support &middot; DNS-002</span></div><div class="simple-scholarship-meta"><strong>&#8369;0.00 (no stipend)</strong><span>0 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('DNSC GIA Category A')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>DNSC Grant-in-Aid (GIA) – Category B</strong><span>Institutional support &middot; DNS-003</span></div><div class="simple-scholarship-meta"><strong>&#8369;0.00 (no stipend)</strong><span>0 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('DNSC GIA Category B')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>DNSC Student Assistant (SA) – Category C</strong><span>Institutional support &middot; DNS-004</span></div><div class="simple-scholarship-meta"><strong>&#8369;0.00 (no stipend)</strong><span>0 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('DNSC SA Category C')">View / Edit</button></div>
    <div class="text-muted-custom" style="font-size:.74rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin:.35rem 0 .1rem">External scholarships · with stipend</div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>CHED TDP</strong><span>Government assistance &middot; TDP-002</span></div><div class="simple-scholarship-meta"><strong>&#8369;7,500.00 / semester</strong><span>58 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('CHED TDP')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>DOST-SEI Scholarship</strong><span>Government support &middot; DOST-003</span></div><div class="simple-scholarship-meta"><strong>&#8369;8,000.00 / month</strong><span>35 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('DOST-SEI')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>CHED TES</strong><span>Merit-based &middot; TES-001</span></div><div class="simple-scholarship-meta"><strong>&#8369;20,000.00 / year</strong><span>42 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('CHED TES')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>Bagong Pilipinas (BPMSP)</strong><span>Performance-based &middot; BPM-005</span></div><div class="simple-scholarship-meta"><strong>Amount not stated</strong><span>18 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('Bagong Pilipinas Scholarship Program')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>CHED Merit (CMSP)</strong><span>Community support &middot; CMS-006</span></div><div class="simple-scholarship-meta"><strong>&#8369;20,000.00&ndash;40,000.00 / semester</strong><span>24 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('CHED Merit Scholarship Program')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>GrabScholar</strong><span>Partner assistance &middot; GRB-007</span></div><div class="simple-scholarship-meta"><strong>&#8369;8,000.00 / month</strong><span>16 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('GrabScholar')">View / Edit</button></div>
    <div class="simple-scholarship-row"><div class="simple-scholarship-main"><strong>Cebuana Lhuillier (CLFI)</strong><span>Partner assistance &middot; CEB-008</span></div><div class="simple-scholarship-meta"><strong>Up to &#8369;50,000.00</strong><span>12 scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('cebuana')">View / Edit</button></div>
  </div>
</section>


  </main>
</div>

<div class="modal fade" id="officerViewModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title modal-title-font">Officer Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="info-grid"><div class="info-item"><div class="info-label">Officer</div><div class="info-value" id="viewOfficerName">–</div></div><div class="info-item"><div class="info-label">Email</div><div class="info-value" id="viewOfficerEmail">–</div></div><div class="info-item"><div class="info-label">Role</div><div class="info-value">Officer · System-wide access</div></div><div class="info-item"><div class="info-label">Status</div><div class="info-value" id="viewOfficerStatus">Active</div></div></div></div><div class="modal-footer"><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-dismiss="modal">Close</button></div></div></div></div>
<!-- ============================================================
     LOGOUT CONFIRMATION MODAL (same design as the Scholar Dashboard)
     ============================================================ -->
<!-- Logout preloader: shown after the logout is confirmed -->
<div id="nxPreloader" class="nx-preloader nx-preloader-hide" role="status" aria-live="polite" aria-hidden="true">
  <div class="nx-preloader-mark"></div>
  <span>BAKR</span>
</div>

<div class="lgo-backdrop" id="logout-backdrop" hidden>
  <div class="lgo-modal" id="logout-modal" role="dialog" aria-modal="true" aria-labelledby="logout-modal-title">
    <div class="lgo-head">
      <h3 id="logout-modal-title">Log out?</h3>
      <button type="button" class="lgo-close" id="logout-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <path d="M18 6 6 18M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="lgo-body">
      <p class="lgo-current">You will be signed out of your admin account on this device.</p>
    </div>
    <div class="lgo-foot">
      <button type="button" class="lgo-btn quiet" id="logout-cancel">Cancel</button>
      <button type="button" class="lgo-btn danger" id="logout-confirm"> <a href="{{ url('/adminLogin') }}">Log out</a></button>
    </div>
  </div>
</div>


<!-- ============================================================
     QUICK-VIEW MODAL (Pending Approvals → View Details)
     ============================================================ -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered quick-review-dialog">
    <div class="modal-content">
      <div class="modal-header"><div><h5 class="modal-title modal-title-font"><i class="bi bi-file-earmark-person me-2 u-text-green"></i>Review Scholar Registration</h5><div class="demo-note">Review the registration details before choosing an action. No documents are required at this stage.</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <div class="review-section"><div class="review-section-title"><i class="bi bi-person"></i> Scholar Information</div><div class="info-grid"><div class="info-item"><div class="info-label">Full Name</div><div class="info-value" id="qvName">–</div></div><div class="info-item"><div class="info-label">Student ID</div><div class="info-value" id="qvStudentId">–</div></div><div class="info-item"><div class="info-label">Program</div><div class="info-value" id="qvProgramName">–</div></div><div class="info-item"><div class="info-label">Year Level</div><div class="info-value" id="qvYear">–</div></div><div class="info-item"><div class="info-label">Campus</div><div class="info-value" id="qvCampus">–</div></div><div class="info-item"><div class="info-label">Email</div><div class="info-value" id="qvEmail">–</div></div></div></div>
        <div class="review-section"><div class="review-section-title"><i class="bi bi-mortarboard"></i> Application</div><div class="info-grid"><div class="info-item"><div class="info-label">Scholarship</div><div class="info-value" id="qvProgram">–</div></div><div class="info-item"><div class="info-label">Stipend</div><div class="info-value" id="qvStipend">–</div></div><div class="info-item"><div class="info-label">Submitted By</div><div class="info-value" id="qvOfficer">–</div></div><div class="info-item"><div class="info-label">Submitted At</div><div class="info-value" id="qvDate">–</div></div></div></div>
        <div class="review-section"><div class="review-section-title"><i class="bi bi-patch-check"></i> Scholarship Eligibility</div><ul class="mb-0 ps-3" id="qvEligibility"></ul></div>
        <div class="review-section"><div class="review-section-title"><i class="bi bi-file-earmark-text"></i> Documentary Requirements <span class="text-muted fw-normal">(submitted per award after approval)</span></div><ul class="mb-0 ps-3" id="qvDocuments"></ul></div>
        <div class="review-section"><div class="review-section-title"><i class="bi bi-chat-left-text"></i> Officer Notes</div><div class="review-detail-box" id="qvNotes">–</div></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-dismiss="modal">Close</button><button type="button" class="btn-revision-sm btn-ui btn-ui--ghost btn-ui--sm" onclick="revisionFromQuickView()"><i class="bi bi-arrow-repeat"></i> Request Revision</button><button type="button" class="btn-ineligible-sm btn-ui btn-ui--danger btn-ui--sm" onclick="ineligibleFromQuickView()"><i class="bi bi-archive"></i> Reject</button><button type="button" class="btn-approve-sm btn-ui btn-ui--primary btn-ui--sm" onclick="approveFromQuickView()"><i class="bi bi-check2"></i> Approve</button></div>
    </div>
  </div>
</div>

<!-- ============================================================
     REQUEST REVISION MODAL
     Sends the record back to its submitting Officer with actionable feedback.
     ============================================================ -->
<div class="modal fade" id="revisionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title modal-title-font"><i class="bi bi-arrow-repeat me-2 u-text-amber"></i>Request Revision</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="revision-context mb-3"><i class="bi bi-send me-1"></i> This record will return to <strong id="revisionOfficer">the submitting Officer</strong>'s queue. The scholar stays active in the review cycle.</div>
        <div class="info-item pb-2"><div class="info-label">Scholar</div><div class="info-value" id="revisionScholar">–</div></div>
        <label for="revisionNotes" class="form-label fw-semibold mt-3 mb-1">Admin Remarks <span class="text-muted fw-normal">(required)</span></label>
        <textarea id="revisionNotes" class="form-control" rows="3" placeholder="Tell the Officer exactly what needs to be fixed..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-revision-sm btn-ui btn-ui--ghost btn-ui--sm" onclick="submitRevision()"><i class="bi bi-send"></i> Send to Officer Queue</button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     REJECT / INELIGIBLE CONFIRMATION MODAL
     Permanently archives the record and marks it ineligible.
     ============================================================ -->
<div class="modal fade" id="ineligibleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title modal-title-font"><i class="bi bi-archive me-2 u-text-danger"></i>Reject / Ineligible</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="ineligible-warning mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> This is a final action. The record will be permanently archived and its status updated to <strong>Ineligible</strong>. It will not return to the Officer queue.</div>
        <div class="info-item"><div class="info-label">Scholar</div><div class="info-value" id="ineligibleScholar">–</div></div>
        <label for="ineligibleReason" class="form-label fw-semibold mt-3 mb-1">Reason for ineligibility</label>
        <textarea id="ineligibleReason" class="form-control" rows="3" placeholder="Record the eligibility basis for the archive..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-ineligible-sm btn-ui btn-ui--danger btn-ui--sm" onclick="confirmIneligible()"><i class="bi bi-archive"></i> Archive as Ineligible</button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     SCHOLAR ACCOUNT CREATED MODAL
     ============================================================ -->
<div class="modal fade" id="accountCreatedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content account-created-modal">
      <div class="modal-header"><div><h5 class="modal-title modal-title-font"><i class="bi bi-check-circle-fill me-2 u-text-green"></i>Scholar Approved!</h5><div class="demo-note">Scholar account created successfully.</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body" id="accountCreatedBody">
        <div class="account-created-banner"><i class="bi bi-person-check-fill"></i><div><strong>Scholar account created</strong><span>The scholar can now sign in using the temporary password below.</span></div></div>
        <div class="info-grid account-credentials-grid"><div class="info-item"><div class="info-label">Student ID</div><div class="info-value" id="createdScholarId">–</div></div><div class="info-item"><div class="info-label">Scholar Name</div><div class="info-value" id="createdScholarName">–</div></div><div class="info-item"><div class="info-label">Email</div><div class="info-value" id="createdScholarEmail">–</div></div><div class="info-item"><div class="info-label">Temporary Password</div><div class="info-value" id="createdScholarPassword">–</div></div></div>
        <div class="account-created-note"><i class="bi bi-info-circle"></i><span>The scholar should change the temporary password after signing in.</span></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-dismiss="modal">Close</button><button type="button" class="btn-quick-primary btn-ui btn-ui--primary btn-ui--sm" id="notifyScholarBtn" onclick="notifyApprovedScholar()"><i class="bi bi-envelope"></i> Notify Scholar by Email</button></div>
    </div>
  </div>
</div>

<!-- ============================================================
     CUMULATIVE DOCUMENT VAULT MODAL
     ============================================================ -->
<div class="modal fade" id="masterFileModal" tabindex="-1" aria-hidden="true" aria-labelledby="masterFileTitle"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content sfd-content"><div class="modal-header sfd-header"><div class="sfd-head-main"><div class="sfd-avatar" id="sfdAvatar" aria-hidden="true">–</div><div><h5 class="modal-title modal-title-font" id="masterFileTitle"><span id="masterFileName">–</span></h5><div class="demo-note" id="masterFileSubtitle">Scholar master record</div><div class="sfd-head-pills" id="sfdHeadPills"></div></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body sfd-body"><div class="sfd-wrap"><div class="sfd-summary" id="sfdSummary"></div><ul class="nav scholarship-editor-tabs sfd-tabs" role="tablist"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sfd-profile" type="button"><i class="bi bi-person-vcard"></i> Profile</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sfd-academic" type="button"><i class="bi bi-mortarboard"></i> Academic &amp; Scholarship</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sfd-docs" type="button"><i class="bi bi-folder2-open"></i> Documents</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sfd-payout" type="button"><i class="bi bi-wallet2"></i> Stipend</button></li></ul><div class="tab-content"><div class="tab-pane fade show active sfd-pane" id="sfd-profile"></div><div class="tab-pane fade sfd-pane" id="sfd-academic"></div><div class="tab-pane fade sfd-pane" id="sfd-docs"></div><div class="tab-pane fade sfd-pane" id="sfd-payout"></div></div></div></div><div class="modal-footer"><span class="demo-note me-auto">Read-only scholar record</span><button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-dismiss="modal">Close</button></div></div></div></div>

<!-- ============================================================
     STATUS OVERRIDE / AUDIT JUSTIFICATION MODAL
     ============================================================ -->
<!-- ============================================================
     ADD SCHOLARSHIP PROGRAM MODAL
     ============================================================ -->
<div class="modal fade" id="scholarshipEditorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title modal-title-font" id="svName">Scholarship</h5>
          <div class="demo-note"><span id="svCategory">Category</span> &middot; <span id="svCode">Scholarship Code</span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="review-section">
          <div class="review-section-title"><i class="bi bi-mortarboard"></i> Basic Information</div>
          <div class="info-grid">
            <div class="info-item"><div class="info-label">Scholarship Name</div>
              <div class="sv-view info-value" id="svNameView">&ndash;</div>
              <input type="text" class="form-control sv-edit" id="svNameEdit">
            </div>
            <div class="info-item"><div class="info-label">Scholarship Code</div>
              <div class="sv-view info-value" id="svCodeView">&ndash;</div>
              <input type="text" class="form-control sv-edit" id="svCodeEdit">
            </div>
            <div class="info-item"><div class="info-label">Category</div>
              <div class="sv-view info-value" id="svCategoryViewText">&ndash;</div>
              <select class="form-select sv-edit" id="svCategoryEdit">
                <option>Institutional support</option>
                <option>Government assistance</option>
                <option>Government support</option>
                <option>Merit-based</option>
                <option>Performance-based</option>
                <option>Community support</option>
                <option>Partner assistance</option>
              </select>
            </div>
            <div class="info-item"><div class="info-label">Active scholars</div><div class="info-value" id="svScholarCount">&ndash;</div></div>
          </div>
        </div>
        <div class="review-section">
          <div class="review-section-title"><i class="bi bi-shield-check"></i> Eligibility</div>
          <div class="sv-view review-detail-box" id="svEligibilityView">&ndash;</div>
          <textarea class="form-control sv-edit" id="svEligibilityEdit" rows="3"></textarea>
        </div>
        <div class="review-section">
          <div class="review-section-title"><i class="bi bi-cash-coin"></i> Stipend</div>
          <div class="info-grid">
            <div class="info-item"><div class="info-label">Amount</div>
              <div class="sv-view info-value" id="svAmountView">&ndash;</div>
              <input type="text" class="form-control sv-edit" id="svAmountEdit">
            </div>
            <div class="info-item"><div class="info-label">Release Frequency</div>
              <div class="sv-view info-value" id="svFrequencyView">&ndash;</div>
              <select class="form-select sv-edit" id="svFrequencyEdit">
                <option>Per semester</option>
                <option>Per month</option>
                <option>Per academic year</option>
                <option>Fixed stipend</option>
                <option>N/A</option>
              </select>
            </div>
          </div>
                  </div>
        <div class="review-section">
          <div class="review-section-title"><i class="bi bi-gift-fill"></i> Benefits</div>
          <ul class="sv-view sv-list" id="svBenefitsView"></ul>
          <textarea class="form-control sv-edit" id="svBenefitsEdit" rows="3" placeholder="One benefit per line"></textarea>
        </div>
        <div class="review-section">
          <div class="review-section-title"><i class="bi bi-folder2-open"></i> Required Documents</div>
          <ul class="sv-view sv-list" id="svDocumentsView"></ul>
          <textarea class="form-control sv-edit" id="svDocumentsEdit" rows="3" placeholder="One document per line"></textarea>
        </div>
        <div class="review-section">
          <div class="review-section-title"><i class="bi bi-clipboard2-check"></i> Maintenance Requirements</div>
          <ul class="sv-view sv-list" id="svMaintenanceView"></ul>
          <textarea class="form-control sv-edit" id="svMaintenanceEdit" rows="3" placeholder="One requirement per line"></textarea>
        </div>
      </div>
      <div class="modal-footer" id="svFooterView">
        <button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn-quick-primary btn-ui btn-ui--primary btn-ui--md" onclick="toggleScholarshipEdit(true)"><i class="bi bi-pencil"></i> Edit</button>
      </div>
      <div class="modal-footer d-none" id="svFooterEdit">
        <button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="toggleScholarshipEdit(false)">Cancel</button>
        <button type="button" class="btn-quick-primary btn-ui btn-ui--primary btn-ui--md" onclick="saveScholarshipEdit()"><i class="bi bi-check2-circle"></i> Save Changes</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="programCreateModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title modal-title-font"><i class="bi bi-plus-circle me-2 u-text-green"></i>Add New Scholarship</h5><div class="demo-note">Complete the four steps, review the configuration, then create the scholarship.</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><ul class="nav scholarship-editor-tabs mb-3"><li class="nav-item"><button class="nav-link active" type="button">1. Basic Information</button></li><li class="nav-item"><button class="nav-link" type="button">2. Eligibility</button></li><li class="nav-item"><button class="nav-link" type="button">3. Benefits &amp; Stipend</button></li><li class="nav-item"><button class="nav-link" type="button">4. Requirements</button></li></ul><div class="row g-3"><div class="col-md-8"><label class="form-label fw-semibold">Scholarship Name</label><input id="newProgramName" class="form-control" placeholder="e.g., Community Leadership Grant"></div><div class="col-md-4"><label class="form-label fw-semibold">Category</label><select id="newProgramType" class="form-select"><option>Institutional support</option><option>Government assistance</option><option>Government support</option><option>Merit-based</option><option>Performance-based</option><option>Community support</option><option>Partner assistance</option></select></div><div class="col-md-4"><label class="form-label fw-semibold">Amount</label><input id="newProgramStipend" class="form-control" placeholder="₱0.00"></div><div class="col-md-4"><label class="form-label fw-semibold">Release Frequency</label><select id="newProgramFrequency" class="form-select"><option>Per semester</option><option>Monthly</option><option>One-time</option></select></div><div class="col-12"><label class="form-label fw-semibold">Eligibility</label><input id="newProgramEligibility" class="form-control" placeholder="e.g., GWA ≤ 1.75 and enrolled full-time"></div><div class="col-12"><label class="form-label fw-semibold">Benefits</label><input id="newProgramBenefits" class="form-control" placeholder="Tuition support, book allowance, living allowance"></div><div class="col-12"><label class="form-label fw-semibold">Maintenance requirements</label><input id="newProgramMaintenance" class="form-control" placeholder="Requirements to remain eligible"></div><div class="col-12"><label class="form-label fw-semibold">Required documents</label><input id="newProgramDocuments" class="form-control" placeholder="Separate requirements with commas: COE, Grade Slip, Income Certificate"></div></div><div class="source-truth-note mt-3 mb-0"><i class="bi bi-shield-check"></i><span>Saving creates a versioned program configuration. Applicants and Officers will use these rules immediately.</span></div></div><div class="modal-footer"><button type="button" class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn-quick-primary btn-ui btn-ui--primary btn-ui--md" onclick="saveNewProgram()"><i class="bi bi-check2-circle"></i> Create Program</button></div></div></div></div>

<!-- ============================================================
     TOASTS
     ============================================================ -->
<div class="modal fade" id="analyticsExportModal" tabindex="-1" aria-hidden="true" aria-labelledby="analyticsExportModalTitle">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="analyticsExportModalTitle">Export Analytics Summary</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p class="text-muted-custom small mb-3">Select the non-financial sections to include in your report.</p>
        <div class="export-modal-options">
          <label class="export-modal-option"><input type="checkbox" id="exportAcademicPerformance" checked> Academic Performance Audit</label>
          <label class="export-modal-option"><input type="checkbox" id="exportMissingRequirements" checked> Missing Requirements List</label>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-sm btn-export-secondary" onclick="exportRawCsv()">Export Raw CSV</button><button type="button" class="btn-quick-primary btn-ui btn-ui--primary btn-ui--md" onclick="downloadPdfReport()"><i class="bi bi-file-earmark-pdf"></i> Download PDF Report</button></div>
    </div>
  </div>
</div>

<!-- ============================================================
     TOASTS
     ============================================================ -->
<div class="toast-container position-fixed bottom-0 end-0 p-3 toast-stack">
  <div id="mainToast" class="toast toast-demo align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body d-flex align-items-start gap-2">
        <i class="bi bi-check-circle-fill fs-5 u-text-green" id="toastIcon"></i>
        <div>
          <div class="fw-semibold u-text-ink" id="toastTitle">Done</div>
          <div class="small text-muted-custom" id="toastMessage"></div>
        </div>
      </div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



<script src="{{ asset('js/bakr-admin-dashboard.js') }}"></script>

</body>
</html>