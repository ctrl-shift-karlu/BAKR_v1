/* BAKR — Admin Dashboard (adminDashboardF). Moved out of the Blade view's inline <script> blocks (same order). */

  
/* Logout confirmation modal behavior (same as the Scholar Dashboard).
   Kept in its own script so it works independently of the main page script. */
(function () {
  var backdrop = document.getElementById('logout-backdrop');
  var cancelBtn = document.getElementById('logout-cancel');
  var closeBtn = document.getElementById('logout-close');
  var confirmBtn = document.getElementById('logout-confirm');
  var trigger = null;

  window.openLogoutModal = function () {
    trigger = document.activeElement;
    backdrop.hidden = false;
    document.body.classList.add('lgo-scroll-lock');
    confirmBtn.focus();
  };

  function closeLogoutModal() {
    backdrop.hidden = true;
    document.body.classList.remove('lgo-scroll-lock');
    if (trigger && trigger.focus) trigger.focus();
  }

  cancelBtn.addEventListener('click', closeLogoutModal);
  closeBtn.addEventListener('click', closeLogoutModal);
  backdrop.addEventListener('click', function (e) {
    if (e.target === backdrop) closeLogoutModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !backdrop.hidden) closeLogoutModal();
  });
  var loggingOut = false;
  confirmBtn.addEventListener('click', function (e) {
    // Hold the link's instant jump, show the preloader, then go to the login page.
    e.preventDefault();
    if (loggingOut) return;
    loggingOut = true;
    var link = confirmBtn.querySelector('a');
    var target = link ? link.href : null;
    closeLogoutModal();
    showPage('dashboard');
    var pre = document.getElementById('nxPreloader');
    if (pre) { pre.classList.remove('nx-preloader-hide'); pre.setAttribute('aria-hidden', 'false'); }
    if (target) setTimeout(function () { window.location.href = target; }, 1600);
  });
})();

/* ---------- next block ---------- */
/* Edit Password: frontend-only validation, same behavior as the Scholar Dashboard.
   Kept in its own script so it works independently of the main page script. */
(function () {
  var passwordForm = document.getElementById('password-form');
  if (!passwordForm) return;
  var currentPw = document.getElementById('current-password');
  var newPw = document.getElementById('new-password');
  var confirmPw = document.getElementById('confirm-password');
  var pwMsg = document.getElementById('password-msg');
  var pwToggle = document.getElementById('password-toggle');
  var pwCancel = document.getElementById('password-cancel');

  function showPasswordMsg(kind, text) {
    pwMsg.className = 'pwc-msg ' + kind;
    pwMsg.textContent = text;
    pwMsg.hidden = false;
  }

  // The form is optional: it stays hidden until the officer asks for it.
  function setPasswordOpen(open) {
    passwordForm.hidden = !open;
    pwToggle.hidden = open;
    pwToggle.setAttribute('aria-expanded', String(open));
    if (open) {
      pwMsg.hidden = true;
      currentPw.focus();
    } else {
      passwordForm.reset();
      pwToggle.focus();
    }
  }
  pwToggle.addEventListener('click', function () { setPasswordOpen(true); });
  pwCancel.addEventListener('click', function () {
    pwMsg.hidden = true;
    setPasswordOpen(false);
  });

  passwordForm.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!currentPw.value || !newPw.value || !confirmPw.value) {
      showPasswordMsg('error', 'Please fill in all three password fields.');
      return;
    }
    if (newPw.value.length < 8) {
      showPasswordMsg('error', 'New password must be at least 8 characters long.');
      return;
    }
    if (newPw.value !== confirmPw.value) {
      showPasswordMsg('error', 'New password and confirmation do not match.');
      return;
    }
    // No backend yet: just confirm the change, clear and hide the form.
    setPasswordOpen(false);
    showPasswordMsg('success', 'Your password has been updated.');
  });
})();

/* ---------- next block ---------- */
/* ============================================================
   TOAST HELPER
   ============================================================ */
function showToast(title, message, type) {
  document.getElementById('toastTitle').textContent = title;
  document.getElementById('toastMessage').textContent = message || '';
  const icon = document.getElementById('toastIcon');
  icon.className = 'bi fs-5 ' + (type === 'red' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill');
  icon.style.color = type === 'red' ? 'var(--sd-danger)' : 'var(--sd-green-700)';
  const toastEl = document.getElementById('mainToast');
  bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3200 }).show();
}

/* ------------------------------------------------------------
   SAMPLE DATA — shaped after the ERD. Everything on the Analytics page and the
   dashboard counters is COMPUTED from these tables (CAMPUSES, ACADEMIC_PROGRAMS,
   SCHOLARSHIPS via PROGRAM_CONFIGS, SCHOLARSHIP_AWARDS, REQUIREMENT_SUBMISSIONS,
   SCHOLAR_APPLICATIONS via PENDING_SCHOLARS). Replace with API data when connected.
   ------------------------------------------------------------ */
const SAMPLE_AS_OF = new Date('2026-10-13T00:00:00'); // sample "today"; matches ACADEMIC_TERMS current term below
const CAMPUSES = [
  { campus_id: 1, campus_name: 'Main (Panabo Campus)' },
  { campus_id: 2, campus_name: 'Carmen Campus' },
  { campus_id: 3, campus_name: 'Samal Campus' },
  { campus_id: 4, campus_name: 'Talaingod Campus' },
  { campus_id: 5, campus_name: 'Asuncion Campus' }
];
const SCHOOL_OPTIONS = CAMPUSES.map(c => c.campus_name);
const ACADEMIC_PROGRAMS = [
  { program_id: 1,  program_code: 'BSIS',      program_name: 'Bachelor of Science in Information Systems',            institute: 'Institute of Computing (IC)' },
  { program_id: 2,  program_code: 'BSIT',      program_name: 'Bachelor of Science in Information Technology',         institute: 'Institute of Computing (IC)' },
  { program_id: 3,  program_code: 'BSFAS',     program_name: 'Bachelor of Science in Fisheries and Aquatic Sciences', institute: 'Institute of Aquatic and Applied Sciences (IAAS)' },
  { program_id: 4,  program_code: 'BSFT',      program_name: 'Bachelor of Science in Food Technology',                institute: 'Institute of Aquatic and Applied Sciences (IAAS)' },
  { program_id: 5,  program_code: 'BPA',       program_name: 'Bachelor of Public Administration',                     institute: 'Institute of Business and Public Affairs (IBPA)' },
  { program_id: 6,  program_code: 'BSCrim',    program_name: 'Bachelor of Science in Criminology',                    institute: 'Institute of Business and Public Affairs (IBPA)' },
  { program_id: 7,  program_code: 'BACOMM',    program_name: 'Bachelor of Arts in Communication',                     institute: 'Institute of Business and Public Affairs (IBPA)' },
  { program_id: 8,  program_code: 'BSSW',      program_name: 'Bachelor of Science in Social Work',                    institute: 'Institute of Business and Public Affairs (IBPA)' },
  { program_id: 9,  program_code: 'BSeD-Math', program_name: 'Bachelor of Secondary Education - Mathematics',         institute: 'Institute of Teacher Education (ITED)' },
  { program_id: 10, program_code: 'BEED',      program_name: 'Bachelor of Elementary Education',                      institute: 'Institute of Teacher Education (ITED)' }
];
const ANALYTICS_INSTITUTES = [...new Set(ACADEMIC_PROGRAMS.map(p => p.institute))];
function isInternalKey(key) { return !!(PROGRAM_CONFIGS[key] && PROGRAM_CONFIGS[key].hasStipend === false); }
// Scholarship filter value: 'all' | '__internal' (all DNSC Internal) | '__external' | a specific key/slug
function matchesProgramKey(key, filter) {
  if (filter === 'all') return true;
  if (filter === '__internal') return isInternalKey(key);
  if (filter === '__external') return !isInternalKey(key);
  return key === filter;
}
function matchesProgramSlug(slug, filter) {
  if (filter === 'all') return true;
  if (filter === '__internal') return isInternalSlug(slug);
  if (filter === '__external') return !isInternalSlug(slug);
  return slug === filter;
}
function programFilterLabel(filter) {
  if (filter === 'all') return 'All scholarships';
  if (filter === '__internal') return 'All DNSC Internal scholarships';
  if (filter === '__external') return 'All external scholarships';
  return (PROGRAM_CONFIGS[filter] && PROGRAM_CONFIGS[filter].label) || filter;
}
function programByCode(code) { return ACADEMIC_PROGRAMS.find(p => p.program_code === code) || null; }
// Scholarship slugs used by table rows (data-program) -> PROGRAM_CONFIGS keys (SCHOLARSHIPS records)
function isInternalSlug(slug) { return /^dnsc /.test(String(slug || '')); }
const SCHOLARSHIP_SLUGS = {
  'dnsc academic': 'DNSC Academic Scholarship',
  'dnsc gia a': 'DNSC GIA Category A',
  'dnsc gia b': 'DNSC GIA Category B',
  'dnsc sa c': 'DNSC SA Category C',
  'ched tdp': 'CHED TDP',
  'ched tes': 'CHED TES',
  'cmsp': 'CHED Merit Scholarship Program',
  'dost': 'DOST-SEI',
  'bpmsp': 'Bagong Pilipinas Scholarship Program',
  'grab': 'GrabScholar',
  'cebuana': 'cebuana'
};

function populateAnalyticsFilters() {
  const programSel = document.getElementById('analyticsProgram');
  const schoolSel = document.getElementById('analyticsSchool');
  if (programSel && programSel.options.length <= 1) {
    const group = (label, entries) => {
      const g = document.createElement('optgroup'); g.label = label;
      entries.forEach(([value, text]) => { const opt = document.createElement('option'); opt.value = value; opt.textContent = text; g.appendChild(opt); });
      programSel.appendChild(g);
    };
    const keys = Object.keys(PROGRAM_CONFIGS);
    group('DNSC Internal (no stipend)', [['__internal', 'All DNSC Internal'], ...keys.filter(isInternalKey).map(k => [k, PROGRAM_CONFIGS[k].label])]);
    group('External scholarships (with stipend)', [['__external', 'All external'], ...keys.filter(k => !isInternalKey(k)).map(k => [k, PROGRAM_CONFIGS[k].label])]);
  }
  if (schoolSel && schoolSel.options.length <= 1) {
    SCHOOL_OPTIONS.forEach(school => {
      const opt = document.createElement('option');
      opt.value = school; opt.textContent = school;
      schoolSel.appendChild(opt);
    });
  }
}

// Deterministic pseudo-random in [0,1) seeded by a string (FNV-1a + avalanche).
// Kept as a helper; no dashboard figure is random — all are computed from the scholars list.
function seededRatio(seed) {
  let h = 2166136261 >>> 0;
  const str = String(seed);
  for (let i = 0; i < str.length; i++) { h ^= str.charCodeAt(i); h = Math.imul(h, 16777619) >>> 0; }
  h ^= h >>> 15; h = Math.imul(h, 2246822507) >>> 0;
  h ^= h >>> 13; h = Math.imul(h, 3266489909) >>> 0;
  h ^= h >>> 16;
  return (h >>> 0) / 4294967296;
}
function pickWeighted(weights, r) {
  const total = weights.reduce((a, b) => a + b, 0);
  let acc = 0;
  for (let i = 0; i < weights.length; i++) { acc += weights[i] / total; if (r < acc) return i; }
  return weights.length - 1;
}

/* ---- Sample SCHOLARSHIP_AWARDS (+ their REQUIREMENT_SUBMISSIONS) ----
   Each award: { award_id, student_id, scholarship_key (-> SCHOLARSHIPS), campus_id,
   program_id (-> ACADEMIC_PROGRAMS), award_status: 'Active',
   submissions: [{ requirement (-> SCHOLARSHIP_REQUIREMENTS.document_name), verification_status }] }
   Awards are built ONLY from the actual scholars in the Scholars directory table, so the
   dashboard and analytics always reflect that list. */
let SAMPLE_DB = null;
function sampleSubmissionsFor(key, verification) {
  const reqs = (PROGRAM_CONFIGS[key] && PROGRAM_CONFIGS[key].documents) || [];
  return reqs.map((doc, i) => {
    let st = 'Verified';
    if (verification === 'Needs Review' && i === reqs.length - 1) st = 'Needs Review';
    if (verification === 'Pending') st = i === 0 ? 'Submitted' : 'Not Submitted';
    return { requirement: doc, verification_status: st };
  });
}
// One SCHOLARSHIP_AWARDS record (+ submissions) from a Scholars-directory table row.
function sampleAwardFromRow(row, awardId) {
  const ds = row.dataset;
  const sid = ((ds.search || '').match(/\d{4}-\d{5}/) || [])[0] || null;
  const rec = SCHOLAR_RECORDS[sid] || {};
  const key = SCHOLARSHIP_SLUGS[ds.program];
  if (!key) return null;
  return {
    award_id: awardId, student_id: sid, scholarship_key: key,
    campus_id: (CAMPUSES.find(c => c.campus_name === ds.school) || {}).campus_id || null,
    program_id: (programByCode(rec.code) || {}).program_id || null,
    award_status: 'Active',
    submissions: sampleSubmissionsFor(key, ds.verification)
  };
}
// Scholar counts shown in Manage Scholarships = Active awards per scholarship.
function recountScholarships(db) {
  Object.keys(PROGRAM_CONFIGS).forEach(key => {
    PROGRAM_CONFIGS[key].scholarCount = db.awards.filter(a => a.scholarship_key === key && a.award_status === 'Active').length;
  });
}
// Awards are built ONLY from the actual scholars in the Scholars directory table.
function buildSampleDb() {
  const db = { awards: [] };
  document.querySelectorAll('#scholarDirectoryTable .directory-row').forEach(row => {
    const award = sampleAwardFromRow(row, 5001 + db.awards.length);
    if (award) db.awards.push(award);
  });
  recountScholarships(db);
  return db;
}

// Approval: the applicant becomes a row in the Scholars directory and a SCHOLARSHIP_AWARDS
// record, so every counter (dashboard, analytics, Manage Scholarships) includes them.
function addApprovedScholar(app) {
  const body = document.querySelector('#scholarDirectoryTable tbody');
  const key = app && SCHOLARSHIP_SLUGS[app.program];
  if (!body || !key || SCHOLAR_RECORDS[app.studentId]) return;
  const courseName = String(app.course).split(',')[0].trim();
  const code = (courseName.match(/\(([^)]+)\)/) || [])[1] || '';
  const yr = (String(app.course).match(/(\d)(?:st|nd|rd|th)\s*Yr/i) || [])[1];
  const parts = app.name.trim().split(/\s+/);
  const last = parts.length > 1 ? parts.pop() : '';
  SCHOLAR_RECORDS[app.studentId] = {
    first: parts.join(' '), middle: '', last, birth: '', sex: '', address: '', city: '', province: '', zip: '',
    phone: '', email: app.email || '', alt: '', code, year: yr ? Number(yr) : null, gwa: null,
    renewal: 'Pending', class: 'Regular Grantee', start: '2026-10-13', channel: '', provider: '', acct: ''
  };
  const e = sfdEsc, nm = e(app.name);
  const stipendStatus = isInternalSlug(app.program) ? 'Not Applicable' : 'Not Eligible'; // DNSC internal scholarships have no stipend (₱0.00)
  const html = `<tr class="directory-row" data-search="${e(app.name + ' ' + app.studentId + ' ' + courseName)}" data-school="${e(app.campus)}" data-verification="Pending" data-stipend="${stipendStatus}" data-program="${e(app.program)}" data-status-group="active"><td>${e(app.studentId)}</td><td><strong>${e(app.name)}</strong></td><td><span class="program-chip">${e(scholarshipDisplayName(app.program))}</span></td><td>${e(app.campus)}</td><td title="${e(courseName)}">${e(code)}</td><td><span class="lifecycle-pill revision" role="status" aria-label="Verification status: Pending"><i class="bi bi-hourglass-split" aria-hidden="true"></i>Pending</span></td><td><span class="lifecycle-pill archived" role="status" aria-label="Stipend status: ${stipendStatus}"><i class="bi bi-dash-circle" aria-hidden="true"></i>${stipendStatus}</span></td><td class="text-end"><button class="directory-action" onclick="viewMasterFile('${nm}','${e(app.studentId)}')"><i class="bi bi-eye"></i> Details</button></td></tr>`;
  const anchor = body.querySelector('#directoryNoResults, .empty-state-row');
  let row;
  if (anchor) { anchor.insertAdjacentHTML('beforebegin', html); row = anchor.previousElementSibling; }
  else { body.insertAdjacentHTML('beforeend', html); row = body.lastElementChild; }
  const db = getSampleDb();
  const award = sampleAwardFromRow(row, Math.max(5000, ...db.awards.map(a => a.award_id)) + 1);
  if (award) db.awards.push(award);
  recountScholarships(db);
  filterScholarDirectory();
}

function getSampleDb() { if (!SAMPLE_DB) SAMPLE_DB = buildSampleDb(); return SAMPLE_DB; }

// An award is "compliant" when every REQUIREMENT_SUBMISSION for the current term is Verified.
// All required documents uploaded (Verified, or Submitted and awaiting verification; 'Needs Review' = flagged, must be resubmitted).
function awardIsComplete(a) { return a.submissions.every(sub => sub.verification_status === 'Verified' || sub.verification_status === 'Submitted'); }
function documentDeadlinePassed() { return SAMPLE_AS_OF > new Date(SFD_TERM.document_deadline + 'T23:59:59'); }
// Standing: 'good' = all documents uploaded; 'incomplete' = documents missing, deadline not yet reached; 'warning' = documents missing and deadline passed.
function awardStanding(a) { return awardIsComplete(a) ? 'good' : (documentDeadlinePassed() ? 'warning' : 'incomplete'); }
// SCHOLAR_APPLICATIONS with application_status = Pending (live from the Pending Approvals store)
function pendingApplicationsFor(program, school) {
  return Object.values(PENDING_SCHOLARS).filter(app =>
    matchesProgramKey(SCHOLARSHIP_SLUGS[app.program], program) && (school === 'all' || app.campus === school));
}
function applicationAgeDays(app) { return (SAMPLE_AS_OF - new Date(String(app.date).replace(' ', 'T'))) / 86400000; }

/* Definitions (each figure is a count/ratio over the rows above):
   total       = awards with award_status 'Active' in the selected scholarship/campus
   pending     = SCHOLAR_APPLICATIONS with status Pending; urgent = pending older than 3 days
   compliance  = Compliant awards ÷ Active awards
   outstanding = Active awards still missing documents (Incomplete + Warning)
   standing    = share of awards in scope: Good (all documents uploaded), Incomplete (documents missing, term deadline not yet reached),
                 Warning (documents missing and the term deadline has passed) */
function getFilteredAnalytics(program, school) {
  const db = getSampleDb();
  const campusIds = school === 'all' ? null : CAMPUSES.filter(c => c.campus_name === school).map(c => c.campus_id);
  const pool = db.awards.filter(a => matchesProgramKey(a.scholarship_key, program) && (!campusIds || campusIds.includes(a.campus_id)));
  const active = pool.filter(a => a.award_status === 'Active');
  const total = active.length;
  const standings = active.map(awardStanding);
  const compliantCount = standings.filter(x => x === 'good').length;
  const incompleteCount = standings.filter(x => x === 'incomplete').length;
  const warningCount = standings.filter(x => x === 'warning').length;

  const institutes = ANALYTICS_INSTITUTES.map(name => {
    const count = active.filter(a => { const pr = ACADEMIC_PROGRAMS.find(x => x.program_id === a.program_id); return pr && pr.institute === name; }).length;
    return { name, count, pct: total ? Math.round((count / total) * 1000) / 10 : 0 };
  });

  const goodPct = total ? Math.round((compliantCount / total) * 100) : 0;
  const incPct = total ? Math.round((incompleteCount / total) * 100) : 0;
  const standing = {
    good: goodPct, incomplete: incPct, warning: total ? Math.max(0, 100 - goodPct - incPct) : 0,
    counts: { good: compliantCount, incomplete: incompleteCount, warning: warningCount }
  };

  const pendingApps = pendingApplicationsFor(program, school);
  const urgent = pendingApps.filter(app => applicationAgeDays(app) > 3).length;
  const complianceRate = total ? Math.round((compliantCount / total) * 1000) / 10 : 0;

  const internalTotal = active.filter(a => isInternalKey(a.scholarship_key)).length;
  return { total, internalTotal, externalTotal: total - internalTotal, institutes, standing, pending: pendingApps.length, urgent, complianceRate, overdue: incompleteCount + warningCount };
}

function renderInstituteBarChart(institutes) {
  const host = document.getElementById('instituteBarChart');
  if (!host) return;
  const maxCount = Math.max(...institutes.map(i => i.count), 1);
  const shortNames = {
    'Institute of Computing (IC)': 'Computing (IC)',
    'Institute of Aquatic and Applied Sciences (IAAS)': 'Aquatic &amp; Applied Sciences (IAAS)',
    'Institute of Business and Public Affairs (IBPA)': 'Business &amp; Public Affairs (IBPA)',
    'Institute of Teacher Education (ITED)': 'Teacher Education (ITED)'
  };
  const codeOf = name => (name.match(/\(([A-Z]+)\)\s*$/) || [])[1] || name;
  host.innerHTML = institutes.map(inst => {
    const heightPct = Math.round((inst.count / maxCount) * 100);
    const label = `${inst.name}: ${inst.count} Scholars (${inst.pct}% of total)`;
    return `<div class="bar-col" tabindex="0" role="button" aria-expanded="false" aria-label="${label}" title="${label}" data-tooltip="${label}"><div class="bar-value">${inst.count}</div><div class="bar" style="--bar-height:${heightPct}%;"></div><div class="bar-label"><span class="bl-full">${shortNames[inst.name] || inst.name}</span><span class="bl-code">${codeOf(inst.name)}</span></div></div>`;
  }).join('');
}

function renderStandingDonut(standing) {
  const donut = document.getElementById('standingDonut');
  if (!donut) return;
  const goodEnd = standing.good;
  const incEnd = goodEnd + standing.incomplete;
  donut.style.background = `conic-gradient(var(--sd-green-700) 0 ${goodEnd}%, var(--sd-gold) ${goodEnd}% ${incEnd}%, var(--sd-danger) ${incEnd}% 100%)`;
  donut.setAttribute('aria-label', `Document compliance status (share of active scholars): ${standing.good}% Compliant, ${standing.incomplete}% Incomplete Documents, ${standing.warning}% Overdue`);
  document.getElementById('standingGoodPct').textContent = `${standing.good}%`;
  const c = standing.counts || { good: 0, incomplete: 0, warning: 0 };
  document.getElementById('standingGoodCount').textContent = `${standing.good}% (${c.good})`;
  document.getElementById('standingIncompleteCount').textContent = `${standing.incomplete}% (${c.incomplete})`;
  document.getElementById('standingOverdueCount').textContent = `${standing.warning}% (${c.warning})`;
}

function activeFilterLabel() {
  const program = document.getElementById('analyticsProgram')?.value || 'all';
  const school = document.getElementById('analyticsSchool')?.value || 'all';
  const programText = program === 'all' ? 'all scholarships' : programFilterLabel(program);
  const schoolText = school === 'all' ? 'all campuses' : school;
  return `Showing data for ${programText} across ${schoolText}.`;
}

function renderAnalyticsFiltered() {
  populateAnalyticsFilters();
  const program = document.getElementById('analyticsProgram')?.value || 'all';
  const school = document.getElementById('analyticsSchool')?.value || 'all';
  const data = getFilteredAnalytics(program, school);

  document.getElementById('analyticsActiveFilterText').textContent = activeFilterLabel();

  document.getElementById('kpiTotalScholars').textContent = data.total.toLocaleString('en-PH');
  const split = document.getElementById('kpiScholarSplit');
  if (split) split.textContent = `${data.externalTotal} external (with stipend) · ${data.internalTotal} DNSC Internal (no stipend)`;
  document.getElementById('kpiPendingQueue').textContent = data.pending.toLocaleString('en-PH');
  document.getElementById('kpiUrgentCount').textContent = data.urgent;
  document.getElementById('kpiComplianceRate').textContent = `${data.complianceRate}%`;
  document.getElementById('kpiOverdueCount').textContent = data.overdue;

  renderInstituteBarChart(data.institutes);
  renderStandingDonut(data.standing);

  const stage = document.getElementById('analyticsChartStage');
  const emptyState = document.getElementById('analyticsEmptyState');
  const hasData = data.total > 0;
  stage.classList.toggle('is-empty', !hasData);
  emptyState.classList.toggle('is-visible', !hasData);
}

function applyAnalyticsFilters() {
  const overlay = document.getElementById('analyticsLoadingOverlay');
  overlay.classList.add('is-visible');
  overlay.setAttribute('aria-hidden', 'false');
  window.clearTimeout(window.analyticsFilterTimer);
  window.analyticsFilterTimer = window.setTimeout(() => {
    renderAnalyticsFiltered();
    overlay.classList.remove('is-visible');
    overlay.setAttribute('aria-hidden', 'true');
    const program = document.getElementById('analyticsProgram').value;
    const school = document.getElementById('analyticsSchool').value;
    showToast('Analytics scope updated', activeFilterLabel());
  }, 450);
}

function exportSummary() {
  bootstrap.Modal.getOrCreateInstance(document.getElementById('analyticsExportModal')).show();
}

function selectedExportSections() {
  const sections = [];
  if (document.getElementById('exportAcademicPerformance').checked) sections.push('Academic Performance Audit');
  if (document.getElementById('exportMissingRequirements').checked) sections.push('Missing Requirements List');
  return sections;
}

function analyticsReportRows() {
  const program = document.getElementById('analyticsProgram').value;
  const school = document.getElementById('analyticsSchool').value;
  const programLabel = programFilterLabel(program);
  const schoolLabel = school === 'all' ? 'All campuses' : school;
  const value = selector => document.querySelector(`#page-analytics ${selector}`)?.textContent.trim() || '0';
  const sections = selectedExportSections();
  const rows = [
    ['Analytics Summary', ''],
    ['Scholarship', programLabel],
    ['Campus', schoolLabel],
    ['Generated', new Date().toLocaleString()],
    ['Included Sections', sections.join(' \u00b7 ') || 'None selected'],
    ['', ''],
    ['Data basis', 'Sample records shaped after the ERD, as of 2026-10-13'],
    ['Metric', 'Value'],
    ['Total Active Scholars', value('#kpiTotalScholars')],
    ['Pending Applications Queue', value('#kpiPendingQueue')],
    ['Document Renewal Compliance Rate', value('#kpiComplianceRate')]
  ];
  if (sections.includes('Missing Requirements List')) rows.push(['Outstanding requirements', `${value('#kpiOverdueCount')} active scholars with documentary requirements still missing`]);
  return rows;
}

function exportRawCsv() {
  const rows = analyticsReportRows();
  const escapeCell = cell => `"${String(cell).replaceAll('"', '""')}"`;
  const workbook = rows.map(row => row.map(escapeCell).join(',')).join('\n');
  const blob = new Blob([workbook], { type: 'text/csv;charset=utf-8' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `analytics-summary-${new Date().toISOString().slice(0, 10)}.csv`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(link.href);
  bootstrap.Modal.getInstance(document.getElementById('analyticsExportModal'))?.hide();
  showToast('CSV exported', 'The selected raw analytics sections are ready.');
}

function downloadPdfReport() {
  const rows = analyticsReportRows();
  const reportWindow = window.open('', '_blank');
  if (!reportWindow) { showToast('Enable pop-ups', 'Allow the report window to generate the PDF.', 'red'); return; }
  reportWindow.document.write(`<title>Export Analytics Summary</title><style>body{font:14px Arial;color:#1e3b2a;padding:32px}h1{font-size:22px}table{border-collapse:collapse;width:100%;margin-top:20px}td{border:1px solid #d8dfd8;padding:8px}td:first-child{font-weight:700;width:42%}@media print{button{display:none}}</style><h1>Export Analytics Summary</h1><p>Non-financial audit report · ${new Date().toLocaleString()}</p><table>${rows.map(row => `<tr><td>${row[0]}</td><td>${row[1]}</td></tr>`).join('')}</table><button onclick="window.print()">Print / Save as PDF</button>`);
  reportWindow.document.close();
  bootstrap.Modal.getInstance(document.getElementById('analyticsExportModal'))?.hide();
}

/* ============================================================
   NAVIGATION
   ============================================================ */
function showPage(page) {
  document.querySelectorAll('.page-section').forEach(sec => sec.classList.remove('active-page'));
  const target = document.getElementById('page-' + page);
  if (target) target.classList.add('active-page');

  document.querySelectorAll('.sidebar-nav .nav-link[data-page]').forEach(link => {
    const isActive = link.getAttribute('data-page') === page;
    link.classList.toggle('active', isActive);
    if (isActive) link.setAttribute('aria-current', 'page'); else link.removeAttribute('aria-current');
  });

  // close mobile offcanvas if open
  const offcanvasEl = document.getElementById('mobileSidebar');
  const oc = bootstrap.Offcanvas.getInstance(offcanvasEl);
  if (oc) oc.hide();

  window.scrollTo({ top: 0, behavior: 'instant' in window ? 'instant' : 'auto' });
}

document.querySelectorAll('.sidebar-nav .nav-link[data-page]').forEach(link => {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    showPage(link.getAttribute('data-page'));
  });
});

/* ============================================================
   PENDING APPROVALS: scholar data store (demo)
   Each entry carries the program rules shown in the confirmation
   summary. Swap this out for live API data when wiring the backend.
   ============================================================ */
// Names and amounts are read from the scholarship configuration (SCHOLARSHIPS /
// SCHOLARSHIP_BENEFITS) in PROGRAM_CONFIGS so every screen shows the same values.
function scholarshipConfigFor(value) { return PROGRAM_CONFIGS[SCHOLARSHIP_SLUGS[String(value || '').toLowerCase()]] || PROGRAM_CONFIGS[value] || null; }
function scholarshipDisplayName(value) {
  const cfg = scholarshipConfigFor(value);
  return cfg ? cfg.label : (value || '–');
}
function scholarshipStipendLabel(value) {
  const cfg = scholarshipConfigFor(value);
  return cfg ? cfg.amountLabel : 'Amount not stated';
}

const PENDING_SCHOLARS = {
  mj: {
    name: 'Patricia Navarro', campus: 'Main (Panabo Campus)', studentId: '2023-01034', course: 'Bachelor of Science in Information Systems (BSIS), 3rd Yr',
    program: 'ched tdp', officer: 'Officer R. Cruz', date: '2026-09-24 10:14',
    eligibility: 'GWA ≤ 1.75', email: 'patricia.navarro@gmail.com', bulkEligible: true, validationNote: 'Enrollment and GWA checked against the registrar record.'
  },
  jd: {
    name: 'Emmanuel Torres', campus: 'Carmen Campus', studentId: '2022-00941', course: 'Bachelor of Science in Criminology (BSCrim), 4th Yr',
    program: 'dnsc academic', officer: 'Officer L. Tan', date: '2026-09-24 07:40',
    eligibility: 'Institutional eligibility review', email: 'emmanuel.torres@gmail.com', bulkEligible: false, validationNote: 'Eligibility needs manual review before approval.'
  },
  kr: {
    name: 'Isabel Fernandez', campus: 'Samal Campus', studentId: '2024-00215', course: 'Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS), 2nd Yr',
    program: 'dost', officer: 'Officer R. Cruz', date: '2026-09-23 15:05',
    eligibility: 'Household income cap', email: 'isabel.fernandez@gmail.com', bulkEligible: true, validationNote: 'Household income declaration checked.'
  },
  ab: {
    name: 'Miguel Castillo', campus: 'Talaingod Campus', studentId: '2026-00388', course: 'Bachelor of Public Administration (BPA), 1st Yr',
    program: 'ched tdp', officer: 'Officer R. Cruz', date: '2026-09-24 09:02',
    eligibility: 'GWA ≤ 1.75 (borderline review)', email: 'miguel.castillo@gmail.com', bulkEligible: false, validationNote: 'GWA is borderline; flagged for Admin review.'
  },
  ra: {
    name: 'Rochelle D. Abella', campus: 'Main (Panabo Campus)', studentId: '2026-00387', course: 'Bachelor of Science in Food Technology (BSFT), 1st Yr',
    program: 'bpmsp', officer: 'Officer R. Cruz', date: '2026-09-24 11:30',
    eligibility: 'Top 5 of graduating class or SHS GWA ≥ 95%; incoming 1st-year; family income ≤ ₱2,000,000', email: 'rochelle.abella@gmail.com', bulkEligible: true, validationNote: 'Enrollment checked against the registrar record. Incoming 1st-year (AY 2026-2027); BPMSP eligibility and documentary requirements to be verified per the scholarship configuration.'
  }
};

let currentQuickViewId = null;
let pendingApprovalIds = [];

let negativeActionId = null;
let negativeActionIds = [];

function findPendingIdByName(name) {
  return Object.keys(PENDING_SCHOLARS).find(id => PENDING_SCHOLARS[id].name === name) || null;
}

function requestRevision(name) {
  const id = findPendingIdByName(name);
  if (!id) return;
  negativeActionId = id;
  negativeActionIds = [id];
  const s = PENDING_SCHOLARS[id];
  document.getElementById('revisionScholar').textContent = s.name;
  document.getElementById('revisionOfficer').textContent = s.officer;
  document.getElementById('revisionNotes').value = '';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('revisionModal')).show();
}

function submitRevision() {
  const s = PENDING_SCHOLARS[negativeActionId];
  if (!s) return;
  const note = document.getElementById('revisionNotes').value.trim();
  if (!note) {
    showToast('Admin remarks required', 'Tell the Officer exactly what needs to be fixed.', 'red');
    return;
  }
  removePendingRecords(negativeActionIds);
  bootstrap.Modal.getInstance(document.getElementById('revisionModal'))?.hide();
  showToast('Revision requested', `${s.name} was sent back to ${s.officer} with your admin remarks.`, 'red');
  negativeActionId = null; negativeActionIds = [];
}

function rejectIneligible(name) {
  const id = findPendingIdByName(name);
  if (!id) return;
  negativeActionId = id;
  negativeActionIds = [id];
  document.getElementById('ineligibleScholar').textContent = PENDING_SCHOLARS[id].name;
  document.getElementById('ineligibleReason').value = '';
  bootstrap.Modal.getOrCreateInstance(document.getElementById('ineligibleModal')).show();
}

function confirmIneligible() {
  const s = PENDING_SCHOLARS[negativeActionId];
  if (!s) return;
  const reason = document.getElementById('ineligibleReason').value.trim();
  removePendingRecords(negativeActionIds);
  bootstrap.Modal.getInstance(document.getElementById('ineligibleModal'))?.hide();
  showToast('Record archived as ineligible', `${s.name} was permanently archived and marked Ineligible.${reason ? ' Reason recorded.' : ''}`, 'red');
  negativeActionId = null; negativeActionIds = [];
}


function removePendingRecords(ids) {
  ids.forEach(id => {
    const row = document.getElementById('pendingRow-' + id);
    if (row) row.remove();
    delete PENDING_SCHOLARS[id];
  });
  decrementPendingCounts(ids.length);
  checkEmptyPendingTable();
}

/* ---------- Quick View ---------- */
function openQuickView(id, name, studentId, courseYear, program, officer, date) {
  currentQuickViewId = id;
  const s = PENDING_SCHOLARS[id] || {};
  const course = courseYear || s.course || '';
  const yearMatch = course.match(/(\d)(st|nd|rd|th)\s*Yr/i);
  const programName = course.replace(/,\s*\d(st|nd|rd|th)\s*Yr.*$/i, '').trim();
  document.getElementById('qvName').textContent = name || s.name || '–';
  document.getElementById('qvStudentId').textContent = studentId || s.studentId || '–';
  document.getElementById('qvProgramName').textContent = programName || '–';
  document.getElementById('qvYear').textContent = yearMatch ? yearMatch[1] + yearMatch[2] + ' Year' : '–';
  document.getElementById('qvCampus').textContent = s.campus || '–';
  document.getElementById('qvEmail').textContent = s.email || '–';
  document.getElementById('qvProgram').textContent = scholarshipDisplayName(program || s.program);
  document.getElementById('qvStipend').textContent = scholarshipStipendLabel(program || s.program);
  document.getElementById('qvOfficer').textContent = officer || s.officer || '–';
  document.getElementById('qvDate').textContent = date || s.date || '–';
  document.getElementById('qvNotes').textContent = s.validationNote || 'No notes from the Officer.';
  const qvCfg = scholarshipConfigFor(program || s.program);
  const qvList = (elId, items, empty) => { const el = document.getElementById(elId); if (el) el.innerHTML = (items && items.length ? items : [empty]).map(i => `<li>${sfdEsc(i)}</li>`).join(''); };
  qvList('qvEligibility', qvCfg && qvCfg.rules, 'No eligibility criteria configured.');
  qvList('qvDocuments', qvCfg && qvCfg.documents, (qvCfg && qvCfg.documentsNote) || 'No documentary requirements configured for this scholarship.');
  if (qvCfg && qvCfg.documentsNote && qvCfg.documents.length) document.getElementById('qvDocuments').insertAdjacentHTML('beforeend', `<li class="text-muted"><em>${sfdEsc(qvCfg.documentsNote)}</em></li>`);

  bootstrap.Modal.getOrCreateInstance(document.getElementById('quickViewModal')).show();
}

function approveFromQuickView() {
  if (!currentQuickViewId || !PENDING_SCHOLARS[currentQuickViewId]) return;
  pendingApprovalIds = [currentQuickViewId];
  performApproval();
}

function revisionFromQuickView() {
  const modalEl = document.getElementById('quickViewModal');
  const modal = bootstrap.Modal.getInstance(modalEl);
  const name = document.getElementById('qvName').textContent;
  if (modal) modal.hide();
  setTimeout(() => requestRevision(name), 250);
}

function ineligibleFromQuickView() {
  const modalEl = document.getElementById('quickViewModal');
  const modal = bootstrap.Modal.getInstance(modalEl);
  const name = document.getElementById('qvName').textContent;
  if (modal) modal.hide();
  setTimeout(() => rejectIneligible(name), 250);
}

/* ---------- Approval → account creation flow ---------- */
function performApproval() {
  const ids = pendingApprovalIds.filter(id => PENDING_SCHOLARS[id]);
  if (!ids.length) return;
  const first = PENDING_SCHOLARS[ids[0]];
  const names = ids.map(id => PENDING_SCHOLARS[id].name);
  const account = {
    scholarId: first.studentId,
    name: first.name,
    email: first.email || '–',
    password: `DNSC-${String(first.studentId || '').replace(/[^0-9]/g, '')}`
  };
  ids.forEach(id => addApprovedScholar(PENDING_SCHOLARS[id]));
  ids.forEach(id => {
    const row = document.getElementById('pendingRow-' + id);
    if (row) row.remove();
    delete PENDING_SCHOLARS[id];
  });
  decrementPendingCounts(ids.length);
  checkEmptyPendingTable();
  bootstrap.Modal.getInstance(document.getElementById('quickViewModal'))?.hide();
  showToast('Scholar approved', `${names.join(', ')} ${names.length === 1 ? 'has' : 'have'} been approved and added to the active roster.`);
  pendingApprovalIds = [];
  document.querySelectorAll('#pendingApprovalsTable .row-select').forEach(cb => { cb.checked = false; });
  updateBatchContext();
  populateAccountCreatedModal(account);
  setTimeout(() => bootstrap.Modal.getOrCreateInstance(document.getElementById('accountCreatedModal')).show(), 300);
}
function populateAccountCreatedModal(account) {
  document.getElementById('createdScholarId').textContent = account.scholarId || '–';
  document.getElementById('createdScholarName').textContent = account.name || '–';
  document.getElementById('createdScholarEmail').textContent = account.email || '–';
  document.getElementById('createdScholarPassword').textContent = account.password || '–';
  const btn = document.getElementById('notifyScholarBtn');
  if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-envelope"></i> Notify Scholar by Email'; }
}
function notifyApprovedScholar() {
  const email = document.getElementById('createdScholarEmail')?.textContent || 'the provided email';
  const btn = document.getElementById('notifyScholarBtn');
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-check2"></i> Email Notification Sent'; }
  showToast('Scholar notified', `Account credentials were sent to ${email}.`);
}

/* Only checkboxes on rows the current search/filter keeps visible count toward
   selection, so "Select all" / batch actions respect the active filters. */
function getSelectedPendingIds() {
  return Array.from(document.querySelectorAll('#pendingApprovalsTable .row-select:checked')).map(cb => cb.dataset.scholarId).filter(Boolean);
}
function updateBatchContext() {
  const ids = getSelectedPendingIds();
  const bar = document.getElementById('pendingBatchBar');
  const count = document.getElementById('pendingSelectedCount');
  if (count) count.textContent = ids.length;
  if (bar) bar.classList.toggle('is-visible', ids.length > 0);
  const boxes = Array.from(document.querySelectorAll('#pendingApprovalsTable .row-select')).filter(cb => cb.closest('tr')?.dataset.filtered !== 'out');
  const selectAll = document.getElementById('pendingSelectAll');
  if (selectAll) { selectAll.checked = boxes.length > 0 && ids.length === boxes.length; selectAll.indeterminate = ids.length > 0 && ids.length < boxes.length; }
}
function toggleAllPending(checked) {
  document.querySelectorAll('#pendingApprovalsTable .row-select').forEach(cb => {
    if (cb.closest('tr')?.dataset.filtered === 'out') return; // leave filtered-out rows untouched
    cb.checked = checked;
  });
  updateBatchContext();
}
/* ---------- Search + filter (name/ID/program, scholarship type, school) ---------- */
function filterScholarApprovalQueue() {
  const val = id => document.getElementById(id)?.value || 'all';
  const q = (document.getElementById('pendingSearch')?.value || '').toLowerCase().trim();
  const program = val('pendingProgramFilter'), school = val('pendingSchoolFilter');
  const rows = document.querySelectorAll('#pendingApprovalsTable tr[data-scholar-id]');
  let visible = 0;
  rows.forEach(row => {
    const matches = (!q || (row.dataset.search || '').includes(q)) && matchesProgramSlug(row.dataset.program, program) && (school === 'all' || row.dataset.school === school);
    row.dataset.filtered = matches ? '' : 'out';
    if (!matches) { const cb = row.querySelector('.row-select'); if (cb) cb.checked = false; }
    if (matches) visible++;
  });
  refreshAppTable('pendingApprovalsTable', true);
  const count = document.getElementById('pendingVisibleCount'); if (count) count.textContent = visible;
  const totalRemaining = Object.keys(PENDING_SCHOLARS).length;
  const noResults = document.getElementById('pendingNoResults');
  if (noResults) noResults.style.display = (totalRemaining > 0 && visible === 0) ? 'table-row' : 'none';
  updateBatchContext();
}
function clearScholarApprovalFilters() {
  ['pendingSearch', 'pendingProgramFilter', 'pendingSchoolFilter'].forEach(id => { const el = document.getElementById(id); if (el) el.value = el.tagName === 'SELECT' ? 'all' : ''; });
  filterScholarApprovalQueue();
}
function approveSelectedPending() {
  const ids = getSelectedPendingIds().filter(id => PENDING_SCHOLARS[id]);
  if (!ids.length) return;
  pendingApprovalIds = ids;
  openQuickView(ids[0]);
}
function exportSelectedPending() {
  const rows = getSelectedPendingIds().map(id => PENDING_SCHOLARS[id]).filter(Boolean);
  if (!rows.length) return;
  const csv = [['Scholar','Student ID','Program','Officer','Date'], ...rows.map(s => [s.name,s.studentId,scholarshipDisplayName(s.program),s.officer,s.date])].map(row => row.map(v => `"${String(v).replaceAll('"','""')}"`).join(',')).join('\n');
  const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'})); link.download = 'selected-pending-scholars.csv'; link.click(); URL.revokeObjectURL(link.href);
  showToast('Selected scholars exported', `${rows.length} pending scholar record${rows.length === 1 ? '' : 's'} exported.`);
}

function decrementPendingCounts() { syncSampleCounters(); }

// Recompute every dashboard counter from the sample data / live pending store.
function syncSampleCounters() {
  const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  const pending = Object.keys(PENDING_SCHOLARS).length;
  const allData = getFilteredAnalytics('all', 'all');
  set('dashboardActiveScholarCount', allData.total.toLocaleString('en-PH'));
  set('dashboardScholarSplit', `${allData.externalTotal} external (with stipend) · ${allData.internalTotal} DNSC Internal (no stipend)`);
  set('dashboardPendingCount', pending);
  document.querySelectorAll('[data-counter="pending-scholars"]').forEach(b => { b.textContent = pending; });
  set('pendingApprovalsBadge', pending + ' pending');
  const programs = Object.keys(PROGRAM_CONFIGS).length;
  set('dashboardProgramCount', programs);
  set('dashboardOfficerCount', document.querySelectorAll('#officerDirectoryTable .officer-row .pill-active').length);
  set('programConfigCount', programs + ' active configurations');
  syncScholarshipRows();
  renderAnalyticsFiltered();
}

// Scholarship list rows read their label, category, code, amount and scholar count from PROGRAM_CONFIGS.
function syncScholarshipRows() {
  document.querySelectorAll('#adminProgramsGrid .simple-scholarship-row').forEach(row => {
    const m = (row.querySelector('button')?.getAttribute('onclick') || '').match(/openScholarshipEditor\('([^']+)'\)/);
    const cfg = m && PROGRAM_CONFIGS[m[1]];
    if (!cfg) return;
    row.querySelector('.simple-scholarship-main strong').textContent = cfg.label;
    row.querySelector('.simple-scholarship-main span').innerHTML = `${cfg.category} &middot; ${cfg.code}`;
    row.querySelector('.simple-scholarship-meta strong').textContent = cfg.amountLabel;
    row.querySelector('.simple-scholarship-meta span').textContent = `${cfg.scholarCount} scholars`;
  });
}

function checkEmptyPendingTable() {
  const emptyRow = document.getElementById('pendingEmptyRow');
  const queueCount = document.getElementById('scholarApprovalCount');
  const remaining = Object.keys(PENDING_SCHOLARS).length;
  if (emptyRow) emptyRow.classList.toggle('is-hidden', remaining > 0);
  if (queueCount) queueCount.innerHTML = `<i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>${remaining} pending`;
  filterScholarApprovalQueue();
}

/* ============================================================
   MANAGE SCHOLARS: simple lifecycle directory and audit governance
   ============================================================ */
let activeDirectoryStatus = 'all';

/* Summary stat cards reflect whatever the filter bar currently shows,
   so they always describe the visible slice of the "Already Scholars" table. */
function syncScholarSummaryFilters() {
  let total = 0, waitingVerification = 0, waitingStipend = 0;
  document.querySelectorAll('#scholarDirectoryTable .directory-row').forEach(row => {
    if (row.dataset.filtered === 'out') return;
    total++;
    if (row.dataset.verification !== 'Verified') waitingVerification++;
    if (row.dataset.stipend === 'Ready') waitingStipend++;
  });
  [['directoryTotal', total], ['directoryWaitingVerification', waitingVerification], ['directoryWaitingStipend', waitingStipend]].forEach(([id, v]) => { const el = document.getElementById(id); if (el) el.textContent = v; });
}
function filterScholarDirectory() {
  const val = id => document.getElementById(id)?.value || 'all';
  const query=(document.getElementById('scholarDirectorySearch')?.value||'').toLowerCase().trim();
  const program=val('scholarProgramFilter'), school=val('scholarSchoolFilter'), verification=val('scholarVerificationFilter'), stipend=val('scholarStipendFilter');
  const rows=document.querySelectorAll('#scholarDirectoryTable .directory-row'); let visible=0;
  rows.forEach(row=>{
    const matches=(!query||row.dataset.search.toLowerCase().includes(query))&&matchesProgramSlug(row.dataset.program,program)&&(school==='all'||row.dataset.school===school)&&(verification==='all'||row.dataset.verification===verification)&&(stipend==='all'||row.dataset.stipend===stipend);
    row.dataset.filtered=matches?'':'out'; if(matches)visible++;
  });
  refreshAppTable('scholarDirectoryTable', true);
  syncScholarSummaryFilters(); const count=document.getElementById('directoryVisibleCount'); if(count)count.textContent=visible; const pill=document.getElementById('scholarDirectoryCountPill'); if(pill){pill.innerHTML='<i class="bi bi-people-fill" aria-hidden="true"></i>'+visible+' scholar'+(visible===1?'':'s'); pill.setAttribute('aria-label','Status: '+visible+' scholars');} const empty=document.getElementById('directoryNoResults'); if(empty)empty.style.display=visible?'none':'table-row';
}
function clearScholarDirectoryFilters() {
  ['scholarDirectorySearch','scholarProgramFilter','scholarSchoolFilter','scholarVerificationFilter','scholarStipendFilter'].forEach(id=>{const el=document.getElementById(id); if(el)el.value=el.tagName==='SELECT'?'all':'';}); filterScholarDirectory();
}
/* ---------- Scholar full-record modal ----------
   Demo data shaped after the ERD (USERS, SCHOLARS, ENROLLMENTS, SCHOLAR_APPLICATIONS,
   SCHOLARSHIP_AWARDS, REQUIREMENT_SUBMISSIONS, PAYOUT_ACCOUNTS, DISBURSEMENTS,
   NOTIFICATIONS, AUDIT_LOGS + scholarship configuration tables).
   Replace SCHOLAR_RECORDS / SCHOLARSHIP_CONFIG with the records API response when connected. */
const SFD_TERM = { academic_year: '2026-2027', semester: '1st Semester', document_deadline: '2026-11-15', is_current: true };
const SCHOLAR_RECORDS = {
  '2023-00456': { first:'Maria', middle:'Santos', last:'Jimenez', birth:'2004-03-12', sex:'Female', address:'Purok 4, Brgy. Gredu', city:'Panabo City', province:'Davao del Norte', zip:'8105', phone:'+63 917 555 0142', email:'maria.jimenez@gmail.com', alt:'mjimenez.dnsc@gmail.com', code:'BSIS', year:3, gwa:1.58, renewal:'Renewed', class:'Regular Grantee', start:'2023-08-14', channel:'E-wallet', provider:'GCash', acct:'0917 555 0142' },
  '2022-00189': { first:'Jaymar', middle:'Dela Cruz', last:'Delos Santos', birth:'2003-11-02', sex:'Male', address:'Zone 2, Brgy. Poblacion', city:'Carmen', province:'Davao del Norte', zip:'8101', phone:'+63 926 555 0188', email:'jaymar.ds@gmail.com', alt:'', code:'BSCrim', year:4, gwa:2.35, renewal:'For Review', class:'Institutional Grantee', start:'2022-08-15', channel:'Bank', provider:'Landbank', acct:'1234-5678-90' },
  '2023-00812': { first:'Kristine', middle:'Aguilar', last:'Robles', birth:'2004-07-25', sex:'Female', address:'Sitio Kaputian, Brgy. Peñaplata', city:'Island Garden City of Samal', province:'Davao del Norte', zip:'8119', phone:'+63 905 555 0121', email:'kristine.robles@gmail.com', alt:'k.robles@dnsc.edu.ph', code:'BSFAS', year:3, gwa:1.42, renewal:'Renewed', class:'Undergraduate Scholar', start:'2023-08-14', channel:'Bank', provider:'BDO', acct:'0045-8821-3390' },
  '2023-00901': { first:'Ana', middle:'Lopez', last:'Bautista', birth:'2004-01-30', sex:'Female', address:'Purok 1, Brgy. Palma Gil', city:'Talaingod', province:'Davao del Norte', zip:'8118', phone:'+63 939 555 0199', email:'ana.bautista@gmail.com', alt:'', code:'BPA', year:3, gwa:1.92, renewal:'Pending', class:'Regular Grantee', start:'2023-08-14', channel:'E-wallet', provider:'Maya', acct:'0939 555 0199' },
  '2021-00643': { first:'Lorenzo', middle:'Rivera', last:'Garcia', birth:'2002-09-18', sex:'Male', address:'Purok 7, Brgy. Poblacion', city:'Asuncion', province:'Davao del Norte', zip:'8102', phone:'+63 918 555 0164', email:'lorenzo.garcia@gmail.com', alt:'', code:'BACOMM', year:4, gwa:2.48, renewal:'For Review', class:'Institutional Grantee', start:'2021-08-16', channel:'Bank', provider:'Landbank', acct:'4421-7783-12' },
  '2020-00318': { first:'Sofia', middle:'Cruz', last:'Mercado', birth:'2001-12-05', sex:'Female', address:'Brgy. Little Panay', city:'Panabo City', province:'Davao del Norte', zip:'8105', phone:'+63 927 555 0110', email:'sofia.mercado@gmail.com', alt:'smercado@dnsc.edu.ph', code:'BSFT', year:4, gwa:1.31, renewal:'Renewed', class:'Undergraduate Scholar', start:'2020-08-17', channel:'Bank', provider:'BPI', acct:'3049-1120-77' },
  '2019-00207': { first:'Daniel', middle:'Gomez', last:'Reyes', birth:'2000-05-09', sex:'Male', address:'Zone 5, Brgy. Poblacion', city:'Carmen', province:'Davao del Norte', zip:'8101', phone:'+63 915 555 0173', email:'daniel.reyes@gmail.com', alt:'', code:'BSeD-Math', year:4, gwa:1.67, renewal:'Completed', class:'Regular Grantee', start:'2019-08-19', channel:'E-wallet', provider:'GCash', acct:'0915 555 0173' },
  '2022-00776': { first:'Carla', middle:'Dizon', last:'Villanueva', birth:'2003-04-21', sex:'Female', address:'Sitio Libertad, Brgy. Peñaplata', city:'Island Garden City of Samal', province:'Davao del Norte', zip:'8119', phone:'+63 949 555 0155', email:'carla.villanueva@gmail.com', alt:'', code:'BSSW', year:4, gwa:2.21, renewal:'Pending', class:'Institutional Grantee', start:'2022-08-15', channel:'E-wallet', provider:'Maya', acct:'0949 555 0155' }
};
// Supplemental eligibility / maintenance wording per scholarship. Category, frequency, amounts
// and required documents are overridden from PROGRAM_CONFIGS by sfdConfigFor() below.
const SCHOLARSHIP_CONFIG = {
  'ched tdp': { code:'CHED-TDP', category:'Government', freq:'Per semester', min:7500, max:7500,
    eligibility:[['Maximum GWA','1.75 or better'],['Enrollment','Full-time, CHED-recognized program'],['Household income','Within CHED threshold']],
    benefits:[['Tuition & other fees support','Financial','Covers tuition and school fees'],['Stipend','Cash','₱7,500.00 per semester']],
    requirements:[['Certificate of Enrollment','Official COE for the current term','Registrar\'s Office','1 day'],['Grade Slip','Latest term grades','Registrar\'s Office','1-2 days'],['Certificate of Indigency','Proof of household income','Barangay Hall','1 day']],  },
  'dost': { code:'DOST-SEI', category:'Government', freq:'Monthly', min:8000, max:8000,
    eligibility:[['Qualifier','Passed DOST-SEI scholarship exam'],['Program','DOST priority course']],
    benefits:[['Monthly stipend','Cash','₱8,000.00 per month'],['Book allowance','Cash','Per DOST-SEI guidelines']],
    requirements:[['Certificate of Enrollment','Official COE for the current term','Registrar\'s Office','1 day'],['Grade Slip','Latest term grades','Registrar\'s Office','1-2 days'],['Scholarship Contract','Signed DOST-SEI contract','DOST Regional Office','3-5 days']],  }
};
function sfdConfigFor(slug) {
  const pc = PROGRAM_CONFIGS[SCHOLARSHIP_SLUGS[slug]];
  const base = SCHOLARSHIP_CONFIG[slug] || {};
  const cfg = Object.assign({ code: (slug || 'N/A').toUpperCase(), category: '—', freq: '—', min: 0, max: 0, eligibility: [], benefits: [], requirements: [], maintenance: [] }, base);
  if (pc) {
    const freqMap = { 'Per month': 'Monthly', 'Per semester': 'Per semester', 'Per academic year': 'Per academic year', 'Fixed stipend': 'Fixed stipend' };
    cfg.code = pc.code; cfg.category = pc.category; cfg.freq = freqMap[pc.frequency] || '—';
    cfg.docsNote = pc.documentsNote || ''; cfg.min = pc.minAmount || 0; cfg.max = pc.maxAmount || 0; cfg.noStipend = pc.hasStipend === false;
    cfg.requirements = (pc.documentDetails && pc.documentDetails.length) ? pc.documentDetails.map(d => d.slice()) : pc.documents.map(d => [d, '', '', '']);
    if (!base.eligibility) cfg.eligibility = pc.rules.map(r => ['Criterion', r]);
    if (!base.maintenance) cfg.maintenance = pc.maintenance.map(m => ['Maintenance', m, 'Evaluated every semester']);
  }
  return cfg;
}
function sfdPeso(n) { return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function sfdEsc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c])); }
function sfdAge(birth) { const b = new Date(birth), n = new Date('2026-10-13'); let a = n.getFullYear() - b.getFullYear(); if (n < new Date(n.getFullYear(), b.getMonth(), b.getDate())) a--; return a; }
function sfdPill(text) {
  const t = String(text).toLowerCase();
  let cls = 'review';
  if (/(verified|active|renewed|approved|released|credited|completed|disbursed)/.test(t)) cls = 'active';
  else if (/(reject|fail|revision|not submitted|withheld|ineligible)/.test(t)) cls = 'revision';
  else if (/(archived|not eligible|not applicable|inactive|n\/a)/.test(t)) cls = 'archived';
  return `<span class="lifecycle-pill ${cls}">${sfdEsc(text)}</span>`;
}
function sfdFields(list) {
  return '<div class="sfd-grid">' + list.map(([l, v, raw]) => {
    const empty = v === '' || v == null;
    return `<div class="sfd-field"><div class="l">${sfdEsc(l)}</div><div class="v${empty ? ' muted' : ''}">${empty ? '—' : (raw ? v : sfdEsc(v))}</div></div>`;
  }).join('') + '</div>';
}
function sfdCard(title, icon, tbl, body) {
  return `<div class="sfd-card"><div class="sfd-card-title"><i class="bi ${icon}"></i>${sfdEsc(title)}<span class="tbl">${sfdEsc(tbl)}</span></div>${body}</div>`;
}
function sfdTable(heads, rows, emptyMsg) {
  if (!rows.length) return `<div class="sfd-empty">${sfdEsc(emptyMsg || 'No records.')}</div>`;
  return '<div class="sfd-table-wrap"><table class="sfd-table"><thead><tr>' + heads.map(h => `<th>${sfdEsc(h)}</th>`).join('') + '</tr></thead><tbody>' +
    rows.map(r => '<tr>' + r.map(c => `<td>${c && c.raw ? c.raw : sfdEsc(c == null || c === '' ? '—' : c)}</td>`).join('') + '</tr>').join('') + '</tbody></table></div>';
}
function sfdBuild(name, studentId, row) {
  const rec = SCHOLAR_RECORDS[studentId] || {};
  const ap = programByCode(rec.code); // ACADEMIC_PROGRAMS
  const parts = name.split(' ');
  const progKey = (row?.dataset.program || '').toLowerCase();
  const cfg = sfdConfigFor(progKey); // SCHOLARSHIPS + child tables
  const verification = row?.dataset.verification || 'Pending';
  const stipend = row?.dataset.stipend || 'Not Eligible';
  const campus = row?.dataset.school || '—';
  const scholarStatus = row?.dataset.statusGroup === 'archived' ? 'Archived' : 'Active';
  const scholarshipName = scholarshipDisplayName(progKey);
  const first = rec.first || parts[0], last = rec.last || parts.slice(1).join(' '), middle = rec.middle || '';
  const fixedAmount = (cfg.min && cfg.min === cfg.max) ? cfg.max : 0;
  const periodWord = { 'Monthly': 'month', 'Per academic year': 'year' }[cfg.freq] || 'semester';
  const stipendText = fixedAmount ? sfdPeso(fixedAmount) + ' / ' + periodWord : scholarshipStipendLabel(progKey);
  const amount = cfg.freq === 'Per academic year' ? fixedAmount / 2 : fixedAmount; // per-release amount
  const yearText = rec.year ? rec.year + (['st', 'nd', 'rd'][rec.year - 1] || 'th') + ' year' : '';

  /* REQUIREMENT_SUBMISSIONS: status derives from the directory's verification status */
  const subs = cfg.requirements.map((r, i) => {
    let st = 'Verified', note = '';
    if (verification === 'Needs Review' && i === cfg.requirements.length - 1) { st = 'Needs Review'; note = 'Please resubmit a clearer copy.'; }
    if (verification === 'Pending') st = i === 0 ? 'Submitted' : 'Not Submitted';
    return [r[0], { raw: sfdPill(st) }, note];
  });

  /* DISBURSEMENTS: derive from stipend status */
  const disb = [];
  const period = cfg.freq === 'Monthly' ? 'September 2026' : '1st Semester ' + SFD_TERM.academic_year;
  if (stipend === 'Released') disb.push([period, amount ? sfdPeso(amount) : 'Per grant', '2026-09-30', '2026-09-30', { raw: sfdPill('Released') }]);
  if (stipend === 'Ready') disb.push([cfg.freq === 'Monthly' ? 'October 2026' : period, amount ? sfdPeso(amount) : 'Per grant', '2026-10-20', '', { raw: sfdPill('Scheduled') }]);

  /* header + summary */
  document.getElementById('sfdSummary').innerHTML = [
    ['Student ID', studentId], ['Scholarship', scholarshipName], ['Program', rec.code || ''],
    ['Year level · GWA', [yearText, rec.gwa != null ? rec.gwa.toFixed(2) : ''].filter(Boolean).join(' · ') || '—'], ['Stipend', stipendText]
  ].map(([k, v]) => `<div class="sfd-stat" title="${sfdEsc(k)}: ${sfdEsc(v || '—')}"><div class="k">${sfdEsc(k)}</div><div class="v">${sfdEsc(v || '—')}</div></div>`).join('');
  document.getElementById('sfdHeadPills').innerHTML = sfdPill(scholarStatus) + sfdPill('Verification: ' + verification) + sfdPill('Stipend: ' + (stipend === 'Released' ? 'Disbursed' : stipend));
  document.getElementById('sfdAvatar').textContent = ((first[0] || '') + (last[0] || '')).toUpperCase();

  /* ---------- Profile: SCHOLARS ---------- */
  const address = [rec.address, rec.city, rec.province, rec.zip].filter(Boolean).join(', ');
  const profile =
    sfdCard('Personal Information', 'bi-person-vcard', 'SCHOLARS', sfdFields([
      ['Full name', [first, middle, last].filter(Boolean).join(' ')], ['Student ID', studentId], ['Campus', campus],
      ['Birth date', rec.birth ? rec.birth + ' (' + sfdAge(rec.birth) + ' yrs)' : ''], ['Sex', rec.sex || ''], ['Nationality', rec.nationality || 'Filipino'],
      ['Scholar status', sfdPill(scholarStatus), true]])) +
    sfdCard('Contact & Address', 'bi-geo-alt', 'SCHOLARS', sfdFields([
      ['Email', rec.email || ''], ['Alternative email', rec.alt || ''], ['Phone number', rec.phone || ''], ['Home address', address]]));

  /* ---------- Academic & Scholarship: ENROLLMENTS + SCHOLARSHIP_AWARDS + config ---------- */
  const bullets = (items) => items.length ? '<ul class="sfd-list">' + items.map(t => `<li>${sfdEsc(t)}</li>`).join('') + '</ul>' : '<div class="sfd-empty">None configured.</div>';
  const academic =
    sfdCard('Enrollment', 'bi-mortarboard', 'ENROLLMENTS', sfdFields([
      ['Program', ap ? ap.program_code + ' — ' + ap.program_name : ''], ['Institute', ap ? ap.institute : ''],
      ['Term', SFD_TERM.academic_year + ' · ' + SFD_TERM.semester], ['Year level', rec.year || ''],
      ['GWA', rec.gwa != null ? rec.gwa.toFixed(2) : ''], ['Renewal status', sfdPill(rec.renewal || 'Pending'), true]])) +
    sfdCard('Scholarship Award', 'bi-award', 'SCHOLARSHIP_AWARDS', sfdFields([
      ['Scholarship', scholarshipName], ['Category', cfg.category], ['Release frequency', cfg.freq],
      ['Amount', stipendText], ['Award status', sfdPill('Active'), true], ['Start date', rec.start || '']])) +
    '<div class="sfd-two">' +
    sfdCard('Eligibility', 'bi-check2-square', 'SCHOLARSHIP_ELIGIBILITY', bullets(cfg.eligibility.map(r => r[0] === 'Criterion' ? r[1] : r[0] + ': ' + r[1]))) +
    sfdCard('Maintenance Requirements', 'bi-shield-check', 'SCHOLARSHIP_MAINTENANCE_REQUIREMENTS', bullets(cfg.maintenance.map(r => r[1]))) +
    '</div>';

  /* ---------- Documents: REQUIREMENT_SUBMISSIONS ---------- */
  const docs =
    sfdCard('Requirement Submissions · ' + SFD_TERM.academic_year + ' ' + SFD_TERM.semester, 'bi-folder2-open', 'REQUIREMENT_SUBMISSIONS',
      sfdTable(['Requirement', 'Status', 'Reviewer note'], subs, cfg.docsNote || 'No requirements configured for this scholarship.') + (subs.length && cfg.docsNote ? `<div class="sfd-empty">${sfdEsc(cfg.docsNote)}</div>` : '')) +
    `<div class="sfd-empty">Document deadline: <strong>${sfdEsc(SFD_TERM.document_deadline)}</strong></div>`;

  /* ---------- Stipend: PAYOUT_ACCOUNTS + DISBURSEMENTS ---------- */
  const payout =
    sfdCard('Payout Account', 'bi-wallet2', 'PAYOUT_ACCOUNTS', sfdFields([['Payment channel', cfg.noStipend ? '' : (rec.channel || '')], ['Provider', cfg.noStipend ? '' : (rec.provider || '')], ['Account number', cfg.noStipend ? '' : (rec.acct || '')]])) +
    sfdCard('Disbursements', 'bi-cash-coin', 'DISBURSEMENTS',
      sfdTable(['Release period', 'Amount', 'Scheduled', 'Released', 'Status'], disb,
        stipend === 'Not Applicable' ? 'No stipend — DNSC internal scholarships do not provide a stipend (₱0.00).' : stipend === 'Not Eligible' ? 'No disbursements — scholar is currently not eligible for stipend release.' : 'No disbursements yet.'));

  return { profile, academic, docs, payout };
}
function viewMasterFile(name, studentId) {
  document.getElementById('masterFileName').textContent = name;
  const sourceRow = Array.from(document.querySelectorAll('#scholarDirectoryTable .directory-row')).find(row => row.textContent.includes(studentId));
  document.getElementById('masterFileSubtitle').textContent = `${name} · scholar master record`;
  const sections = sfdBuild(name, studentId, sourceRow);
  Object.keys(sections).forEach(k => { const el = document.getElementById('sfd-' + k); if (el) el.innerHTML = sections[k]; });
  /* always open on the first tab */
  const firstTab = document.querySelector('#masterFileModal .sfd-tabs .nav-link');
  if (firstTab) bootstrap.Tab.getOrCreateInstance(firstTab).show();
  const body = document.querySelector('#masterFileModal .sfd-wrap > .tab-content'); if (body) body.scrollTop = 0;
  bootstrap.Modal.getOrCreateInstance(document.getElementById('masterFileModal')).show();
}
function reassignOfficer(name) { showToast('Officer reassignment opened', `Select a new validating Officer for ${name}.`); }
function exportScholarRecord(name) { showToast('Record export queued', `${name}'s master record will be exported.`); }

/* ============================================================
   MANAGE SCHOLARSHIPS: program administration
   ============================================================ */
function switchProgramTerm(term) {
  showToast('Term context updated', `Program configuration is now scoped to ${term}.`);
}
function openProgramCreateModal() {
  ['newProgramName','newProgramStipend','newProgramEligibility','newProgramBenefits','newProgramMaintenance','newProgramDocuments'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
  bootstrap.Modal.getOrCreateInstance(document.getElementById('programCreateModal')).show();
}
function saveNewProgram() {
  const name=document.getElementById('newProgramName').value.trim();
  if (!name) { showToast('Program name required','Enter a program name before creating the configuration.','red'); return; }
  if (PROGRAM_CONFIGS[name]) { showToast('Scholarship already exists','Use View / Edit on the existing row instead.','red'); return; }
  PROGRAM_CONFIGS[name] = {
    label: name, code: 'NEW-' + Math.floor(100 + Math.random() * 900), category: document.getElementById('newProgramType').value, scholarCount: 0, minAmount: null, maxAmount: null,
    amountLabel: document.getElementById('newProgramStipend').value.trim() || 'Amount not stated', frequency: document.getElementById('newProgramFrequency').value, note: '',
    rules: [document.getElementById('newProgramEligibility').value.trim() || 'Program-specific eligibility rule'],
    benefits: document.getElementById('newProgramBenefits').value.split(',').map(v => v.trim()).filter(Boolean),
    documents: document.getElementById('newProgramDocuments').value.split(',').map(v => v.trim()).filter(Boolean),
    maintenance: document.getElementById('newProgramMaintenance').value.split(',').map(v => v.trim()).filter(Boolean)
  };
  appendScholarshipRow(name);
  syncSampleCounters();
  bootstrap.Modal.getInstance(document.getElementById('programCreateModal'))?.hide();
  showToast('Program created', `${name} was added as an active program configuration.`);
}
function openScholarshipEditor(value) {
  const cfg = PROGRAM_CONFIGS[value];
  if (!cfg) return;
  currentScholarshipValue = value;
  renderScholarshipView(cfg);
  toggleScholarshipEdit(false);
  bootstrap.Modal.getOrCreateInstance(document.getElementById('scholarshipEditorModal')).show();
}
function editProgram(value) { openScholarshipEditor(value); }

function renderScholarshipView(cfg) {
  document.getElementById('svName').textContent = cfg.label;
  document.getElementById('svNameView').textContent = cfg.label;
  document.getElementById('svNameEdit').value = cfg.label;
  document.getElementById('svCode').textContent = cfg.code;
  document.getElementById('svCodeView').textContent = cfg.code;
  document.getElementById('svCodeEdit').value = cfg.code;
  document.getElementById('svCategory').textContent = cfg.category;
  document.getElementById('svCategoryViewText').textContent = cfg.category;
  document.getElementById('svCategoryEdit').value = cfg.category;
  document.getElementById('svScholarCount').textContent = `${cfg.scholarCount} scholars`;
  const eligibility = cfg.rules.join('; ');
  document.getElementById('svEligibilityView').textContent = eligibility || 'No eligibility rules configured.';
  document.getElementById('svEligibilityEdit').value = eligibility;
  document.getElementById('svAmountView').textContent = cfg.amountLabel;
  document.getElementById('svAmountEdit').value = cfg.amountLabel;
  document.getElementById('svFrequencyView').textContent = cfg.frequency;
  document.getElementById('svFrequencyEdit').value = cfg.frequency;
  document.getElementById('svBenefitsView').innerHTML = cfg.benefits.map(b => `<li>${b}</li>`).join('');
  document.getElementById('svBenefitsEdit').value = cfg.benefits.join('\n');
  document.getElementById('svDocumentsView').innerHTML = cfg.documents.map(d => `<li>${d}</li>`).join('') + (cfg.documentsNote ? `<li class="text-muted"><em>${cfg.documentsNote}</em></li>` : '');
  document.getElementById('svDocumentsEdit').value = cfg.documents.join('\n');
  document.getElementById('svMaintenanceView').innerHTML = cfg.maintenance.map(m => `<li>${m}</li>`).join('');
  document.getElementById('svMaintenanceEdit').value = cfg.maintenance.join('\n');
}

function toggleScholarshipEdit(editing) {
  document.getElementById('scholarshipEditorModal').classList.toggle('is-editing', editing);
  document.getElementById('svFooterView').classList.toggle('d-none', editing);
  document.getElementById('svFooterEdit').classList.toggle('d-none', !editing);
  if (!editing && currentScholarshipValue) renderScholarshipView(PROGRAM_CONFIGS[currentScholarshipValue]);
}

function saveScholarshipEdit() {
  const cfg = PROGRAM_CONFIGS[currentScholarshipValue];
  if (!cfg) return;
  const splitLines = id => document.getElementById(id).value.split('\n').map(v => v.trim()).filter(Boolean);
  cfg.label = document.getElementById('svNameEdit').value.trim() || cfg.label;
  cfg.code = document.getElementById('svCodeEdit').value.trim() || cfg.code;
  cfg.category = document.getElementById('svCategoryEdit').value;
  cfg.rules = document.getElementById('svEligibilityEdit').value.split(';').map(v => v.trim()).filter(Boolean);
  cfg.amountLabel = document.getElementById('svAmountEdit').value.trim() || cfg.amountLabel;
  cfg.frequency = document.getElementById('svFrequencyEdit').value;
  cfg.benefits = splitLines('svBenefitsEdit');
  cfg.documents = splitLines('svDocumentsEdit');
  cfg.maintenance = splitLines('svMaintenanceEdit');
  renderScholarshipView(cfg);
  syncScholarshipRow(currentScholarshipValue);
  toggleScholarshipEdit(false);
  showToast('Scholarship updated', `${cfg.label} configuration was saved.`);
}

function appendScholarshipRow(value) {
  const cfg = PROGRAM_CONFIGS[value];
  const grid = document.getElementById('adminProgramsGrid');
  if (!cfg || !grid) return;
  const row = document.createElement('div');
  row.className = 'simple-scholarship-row';
  row.innerHTML = `<div class="simple-scholarship-main"><strong>${cfg.label}</strong><span>${cfg.category} &middot; ${cfg.code}</span></div><div class="simple-scholarship-meta"><strong>${cfg.amountLabel}</strong><span>${cfg.scholarCount} scholars</span></div><span class="program-status">Active</span><button class="btn btn-sm btn-outline-scholarship btn-ui btn-ui--secondary btn-ui--sm" onclick="openScholarshipEditor('${value}')">View / Edit</button>`;
  grid.appendChild(row);
}

function syncScholarshipRow(value) {
  const cfg = PROGRAM_CONFIGS[value];
  const row = document.querySelector(`.simple-scholarship-row button[onclick="openScholarshipEditor('${value}')"]`)?.closest('.simple-scholarship-row');
  if (!row || !cfg) return;
  row.querySelector('.simple-scholarship-main strong').textContent = cfg.label;
  row.querySelector('.simple-scholarship-main span').innerHTML = `${cfg.category} &middot; ${cfg.code}`;
  row.querySelector('.simple-scholarship-meta strong').textContent = cfg.amountLabel;
}

/* ============================================================
   MANAGE SCHOLARSHIPS: configuration sync
   ============================================================ */
// Canonical scholarship configurations — names, benefits, required documents,
// and maintenance rules mirror the Officer Dashboard's SCHOLARSHIP_CONFIGS /
// SCHOLARSHIP_TYPE_OPTIONS so both portals describe the same programs.
let currentScholarshipValue = null;
// minAmount / maxAmount mirror SCHOLARSHIPS.min_amount / max_amount (null = not stated).
// scholarCount is COMPUTED from the Scholars directory (Active awards per scholarship).
// hasStipend:false = DNSC internal scholarship (no stipend, ₱0.00). These scholars are kept separate from externally funded scholarships.
const PROGRAM_CONFIGS = {
  'DNSC Academic Scholarship': {
    label: 'DNSC Academic Scholarship', code: 'DNS-001', category: 'Institutional support', minAmount: 0, maxAmount: 0, hasStipend: false, scholarCount: 0,
    amountLabel: '₱0.00 (no stipend)', frequency: 'N/A', note: 'DNSC internal scholarship: no stipend is provided (₱0.00)',
    rules: ['DNSC student', 'Academic performance / GPA', 'Year level', 'Enrollment status'],
    benefits: ['Institutional scholarship — no cash stipend (₱0.00)'],
    documents: [],
    documentsNote: 'Renewal documents are not listed publicly by DNSC; confirm with the DNSC Scholarship and Grants Unit.',
    maintenance: ['Retention requirements and grade thresholds are not published; confirm with the DNSC Scholarship and Grants Unit']
  },
  'DNSC GIA Category A': {
    label: 'DNSC Grant-in-Aid (GIA) – Category A', code: 'DNS-002', category: 'Institutional support', minAmount: 0, maxAmount: 0, hasStipend: false, scholarCount: 0,
    amountLabel: '₱0.00 (no stipend)', frequency: 'N/A', note: 'DNSC internal scholarship: no stipend is provided (₱0.00)',
    rules: ['DNSC student', 'Talent / skill category (music, dance, arts, sports)', 'Awards / achievements', 'Year level', 'Enrollment status'],
    benefits: ['Institutional scholarship — no cash stipend (₱0.00)'],
    documents: [],
    documentsNote: 'Renewal documents are not listed publicly by DNSC; confirm with the DNSC Scholarship and Grants Unit.',
    maintenance: ['Retention requirements and grade thresholds are not published; confirm with the DNSC Scholarship and Grants Unit']
  },
  'DNSC GIA Category B': {
    label: 'DNSC Grant-in-Aid (GIA) – Category B', code: 'DNS-003', category: 'Institutional support', minAmount: 0, maxAmount: 0, hasStipend: false, scholarCount: 0,
    amountLabel: '₱0.00 (no stipend)', frequency: 'N/A', note: 'DNSC internal scholarship: no stipend is provided (₱0.00)',
    rules: ['DNSC student', 'Student Supreme Council officer or Student Publication Editorial Board member', 'Year level'],
    benefits: ['Institutional scholarship — no cash stipend (₱0.00)'],
    documents: [],
    documentsNote: 'Renewal documents are not listed publicly by DNSC; confirm with the DNSC Scholarship and Grants Unit.',
    maintenance: ['Retention requirements and grade thresholds are not published; confirm with the DNSC Scholarship and Grants Unit']
  },
  'DNSC SA Category C': {
    label: 'DNSC Student Assistant (SA) – Category C', code: 'DNS-004', category: 'Institutional support', minAmount: 0, maxAmount: 0, hasStipend: false, scholarCount: 0,
    amountLabel: '₱0.00 (no stipend)', frequency: 'N/A', note: 'DNSC internal scholarship: no stipend is provided (₱0.00)',
    rules: ['DNSC student', 'Financial need / family income', 'Willingness to work as student assistant', 'Year level'],
    benefits: ['Institutional scholarship — no cash stipend (₱0.00)'],
    documents: [],
    documentsNote: 'Renewal documents are not listed publicly by DNSC; confirm with the DNSC Scholarship and Grants Unit.',
    maintenance: ['Retention requirements and grade thresholds are not published; confirm with the DNSC Scholarship and Grants Unit']
  },
  'CHED TDP': {
    label: 'CHED TDP', code: 'TDP-002', category: 'Government assistance', minAmount: 7500, maxAmount: 7500, scholarCount: 0,
    amountLabel: '₱7,500.00 / semester', frequency: 'Per semester', note: '',
    rules: ['GWA ≤ 1.75', 'Enrolled full-time'],
    benefits: ['₱7,500 per semester educational assistance'],
    documents: ['Certified true copy or electronically generated Certificate of Enrolment/Registration (COE/COR) showing the number of units', 'Certified true copy of the Leave of Absence (LOA) filed with the school (if applicable: returning students who skipped one semester in an academic year)'],
    documentsNote: '',
    maintenance: ['Continuing grantees must have a TDP Award Number and be enrolled in the current semester', 'The school, not the student, submits the consolidated Continuing TDP Forms 1–4 (billing statement, grantee details, registrar\'s certification) to the CHED regional office', 'No grade-average requirement as long as courses are passed (per a 2025 news report; secondary information, confirm with CHED)']
  },
  'DOST-SEI': {
    label: 'DOST-SEI Scholarship', code: 'DOST-003', category: 'Government support', minAmount: 8000, maxAmount: 8000, scholarCount: 0,
    amountLabel: '₱8,000.00 / month', frequency: 'Per month', note: '',
    rules: ['Household income cap', 'GWA ≤ 2.00'],
    benefits: ['₱8,000 per month living allowance', 'Other configured DOST-SEI benefits'],
    documents: ['Registration form (each semester)', 'Certified copy of grades for all subjects enrolled (each semester)'],
    documentsNote: 'Per-semester items come from a secondary source (slide deck of the DOST-SEI FAQ); confirm with the DOST regional office.',
    maintenance: ['Comply with the Scholarship Agreement and maintain the grade requirements until the course is finished', 'Grades are evaluated before the next semester\'s benefits are released', 'DOST-SEI issues a Letter of Enrolment each term', 'Exact grade cutoff and current list of forms not found; confirm with the DOST regional office']
  },
  'CHED TES': {
    label: 'CHED TES', code: 'TES-001', category: 'Merit-based', minAmount: 20000, maxAmount: 20000, scholarCount: 0,
    amountLabel: '₱20,000.00 / year', frequency: 'Per academic year', note: 'DNSC is an SUC/LUC: ₱20,000 per academic year · ₱10,000 per semester disbursement',
    rules: ['GWA ≤ 1.50'],
    benefits: ['₱20,000 academic year for SUC/LUC or ₱27,000 for Private HEI'],
    documents: ['Certificate of Registration/Enrollment (COR/COE)', 'Certificate of Residency (private HEIs in cities or municipalities with no SUC/LUC only)', 'PWD ID (if applicable)'],
    documentsNote: '',
    maintenance: ['Meet the admission and retention policies of the SUC/LUC', 'No previous undergraduate degree', 'Do not overstay in the program (4–5 years plus a 1-year grace period)', 'Schools submit Continuing TES Forms 1–3 for validation each semester']
  },
  'Bagong Pilipinas Scholarship Program': {
    label: 'Bagong Pilipinas (BPMSP)', code: 'BPM-005', category: 'Performance-based', minAmount: null, maxAmount: null, scholarCount: 0,
    amountLabel: 'Amount not stated', frequency: 'N/A', note: 'Stipend amount not stated',
    rules: ['Filipino citizen', 'Incoming first-year college student with no tertiary units or degree earned', 'Top 5 of the graduating class OR Grade 12 / SHS GWA of at least 95%', 'Combined annual gross income of parents/guardians not exceeding ₱2,000,000', 'Enrolled in a CHED-identified priority program'],
    benefits: ['Merit scholarship benefits per approved program configuration'],
    documents: ['Certified true copy of COR/COE, or system-generated registration document certified by the Registrar (each succeeding payment)', 'Certified true copy of grades (GWA) from the previous semester/term (each succeeding payment)', 'Certification of no other nationally funded scholarship (start of every academic year)', 'Leave of Absence (Annex H) (if applicable)', 'Permit to Travel (if going abroad)', 'Graduate exit form (Annex I) (at graduation)', 'Return Service Agreement papers (at graduation)'],
    documentsNote: '',
    maintenance: ['Pass all subjects every semester', 'Enroll the required regular load', 'Promptly inform the CHED regional office of any change in status or program', 'Leave of Absence (Annex H): prior written approval required; allowed once for up to one academic year', 'Transfer or shift outside the priority domain requires prior CHED regional office approval; otherwise it is grounds for termination', 'Permit to Travel required when going abroad', 'At graduation: submit the graduate exit form (Annex I) and Return Service Agreement papers']
  },
  'CHED Merit Scholarship Program': {
    label: 'CHED Merit (CMSP)', code: 'CMS-006', category: 'Community support', minAmount: 20000, maxAmount: 40000, scholarCount: 0,
    amountLabel: '₱20,000.00–40,000.00 / semester', frequency: 'Per semester', note: 'Full: ₱40,000/semester (₱35,000 stipend + ₱5,000 allowance) · Half: ₱20,000/semester (₱17,500 stipend + ₱2,500 allowance)',
    rules: ['Community program eligibility'],
    benefits: ['Full: ₱40,000/semester (₱35,000 stipend + ₱5,000 book/connectivity allowance)', 'Half: ₱20,000/semester (₱17,500 stipend + ₱2,500 book/connectivity allowance)', 'Tuition and other school fees are covered by Free Higher Education for SUC/LUC scholars'],
    documents: [],
    documentsNote: 'CHED\'s official list of per-semester documents was not found (COR and certified grades are usually submitted, but this is unverified).',
    maintenance: ['Maintain a GWA of at least 85% (full scholar) or 80% (half scholar)', 'Carry a full course load each semester', 'Numeric grades are required; a pass/fail transcript cannot be evaluated']
  },
  'GrabScholar': {
    label: 'GrabScholar', code: 'GRB-007', category: 'Partner assistance', minAmount: 8000, maxAmount: 8000, scholarCount: 0,
    amountLabel: '₱8,000.00 / month', frequency: 'Per month', note: 'Monthly student allowance. Full-ride: also covers 100% tuition and miscellaneous fees plus a ₱11,000/year school-supplies allowance (Grab PH)',
    rules: ['Partner program eligibility'],
    benefits: ['₱8,000 per month', '100% tuition and miscellaneous fees', '₱11,000/year school-supplies allowance'],
    documents: [],
    documentsNote: 'Retention documents are not listed by Grab; ask grabscholarcollegescholarship@gmail.com or request your Scholarship Agreement.',
    maintenance: ['Retention requirements are not listed publicly (Grab covers school fees for the whole degree as long as retention requirements are met); confirm with grabscholarcollegescholarship@gmail.com or the Scholarship Agreement']
  },
  'cebuana': {
    label: 'Cebuana Lhuillier (CLFI)', code: 'CEB-008', category: 'Partner assistance', minAmount: null, maxAmount: 50000, scholarCount: 0,
    amountLabel: 'Up to ₱50,000.00', frequency: 'Fixed stipend', note: 'CLFI publishes a fixed stipend of up to ₱50,000 but does not state the payout period on its official page; confirm with CLFI before disbursing',
    rules: ['Partner program eligibility'],
    benefits: ['Fixed stipend of up to ₱50,000 for school-related and living expenses', 'Education-to-Employment (E2E) pathway with the PJ Lhuillier Group of Companies'],
    documents: ['24K Loyalty Card', 'Proof of income', 'Grades (GWA of 85% with no failing mark)', 'Good Moral certificate', 'Enrollment form', 'Grading system'],
    documentsNote: 'Application documents per the official CLFI FAQ; no renewal documents are published.',
    maintenance: ['Renewal/retention rules are not published by CLFI; contact clfi@pjlhuillier.com']
  }
};

function viewOfficer(name,email) { document.getElementById('viewOfficerName').textContent=name; document.getElementById('viewOfficerEmail').textContent=email; const officerRow=Array.from(document.querySelectorAll('#officerDirectoryTable .officer-row')).find(r=>r.textContent.includes(email)); document.getElementById('viewOfficerStatus').textContent=officerRow&&officerRow.querySelector('.status-pill')?officerRow.querySelector('.status-pill').textContent.trim():'Active'; bootstrap.Modal.getOrCreateInstance(document.getElementById('officerViewModal')).show(); }

function toggleOfficerStatus(name, action) { showToast(`${action} officer`, `${name} would be marked ${action.toLowerCase()}d after confirming the access change.`, 'red'); }
function logoutAdmin() { openLogoutModal(); }
function handleLogout() { openLogoutModal(); }


/* ============================================================
   OFFICER-STYLE TABLE CONTROLLER (sorting, pagination footer, mobile cards)
   Rows hidden by a filter carry data-filtered="out"; the controller owns row visibility.
   ============================================================ */
const AppTables = {};
function initAppTable(id, cfg) {
  const table = document.getElementById(id); if (!table) return;
  const wrap = table.closest('.table-app-wrap'); const tbody = table.tBodies[0];
  const st = { page: 1, size: 10, sortIdx: null, sortDir: 'asc', cfg, busy: false };
  AppTables[id] = st;
  const ths = Array.from(table.tHead.rows[0].cells);
  st.labels = ths.map(th => th.textContent.trim());
  (cfg.sortable || []).forEach(idx => {
    const th = ths[idx]; th.classList.add('th-sortable'); th.tabIndex = 0; th.setAttribute('role', 'button');
    th.insertAdjacentHTML('beforeend', ' <i class="bi bi-arrow-down-up sort-icon"></i>');
    const go = () => { if (st.sortIdx === idx) st.sortDir = st.sortDir === 'asc' ? 'desc' : 'asc'; else { st.sortIdx = idx; st.sortDir = 'asc'; } st.page = 1; refreshAppTable(id); };
    th.addEventListener('click', go);
    th.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(); } });
  });
  st.cards = document.createElement('div'); st.cards.id = id + 'MobileCards';
  st.footer = document.createElement('div'); st.footer.className = 'table-footer-bar';
  st.footer.innerHTML = `<div class="tf-pagesize"><label class="form-label mb-0 small text-muted-custom" for="${id}PageSize">Rows per page</label><select class="form-select form-select-sm" id="${id}PageSize"><option value="10">10</option><option value="25">25</option><option value="50">50</option></select></div><div class="tf-counter small text-muted-custom" id="${id}Counter"></div><div class="tf-pagebtns"><button type="button" class="btn btn-outline-scholarship btn-sm" id="${id}Prev"><i class="bi bi-chevron-left"></i> Previous</button><span class="d-flex gap-1 align-items-center" id="${id}Pages"></span><button type="button" class="btn btn-outline-scholarship btn-sm" id="${id}Next">Next <i class="bi bi-chevron-right"></i></button></div>`;
  wrap.after(st.cards); st.cards.after(st.footer);
  st.footer.querySelector('select').addEventListener('change', e => { st.size = parseInt(e.target.value, 10) || 10; st.page = 1; refreshAppTable(id); });
  st.footer.querySelector('#' + id + 'Prev').addEventListener('click', () => { st.page--; refreshAppTable(id); });
  st.footer.querySelector('#' + id + 'Next').addEventListener('click', () => { st.page++; refreshAppTable(id); });
  st.footer.querySelector('#' + id + 'Pages').addEventListener('click', e => { const b = e.target.closest('button[data-page]'); if (b) { st.page = +b.dataset.page; refreshAppTable(id); } });
  st.obs = new MutationObserver(() => { if (!st.busy) refreshAppTable(id); });
  st.obs.observe(tbody, { childList: true });
  refreshAppTable(id);
}
function appTableSortValue(text) {
  const t = text.trim();
  if (/^[A-Z][a-z]{2} \d{1,2}, \d{4}/.test(t)) { const d = Date.parse(t.replace(' · ', ' ')); if (!isNaN(d)) return d; }
  return t.toLowerCase();
}
function refreshAppTable(id, resetPage) {
  const st = AppTables[id]; if (!st) return;
  const table = document.getElementById(id), tbody = table.tBodies[0];
  if (resetPage) st.page = 1;
  st.busy = true;
  const all = Array.from(tbody.rows).filter(r => !r.classList.contains('empty-state-row') && !/NoResults$/.test(r.id));
  let rows = all.filter(r => r.dataset.filtered !== 'out');
  if (st.sortIdx !== null) {
    const dir = st.sortDir === 'asc' ? 1 : -1;
    rows = rows.map((r, i) => ({ r, i, v: appTableSortValue(r.cells[st.sortIdx]?.textContent || '') }))
      .sort((a, b) => (a.v < b.v ? -1 : a.v > b.v ? 1 : a.i - b.i) * dir).map(o => o.r);
    const anchor = tbody.querySelector('.empty-state-row, [id$="NoResults"]');
    rows.forEach(r => tbody.insertBefore(r, anchor));
  }
  const total = rows.length, pages = Math.max(1, Math.ceil(total / st.size));
  st.page = Math.min(Math.max(1, st.page), pages);
  const start = (st.page - 1) * st.size, pageRows = rows.slice(start, start + st.size);
  all.forEach(r => { r.style.display = pageRows.includes(r) ? '' : 'none'; });
  // sort indicators
  Array.from(table.tHead.rows[0].cells).forEach((th, idx) => {
    if (!th.classList.contains('th-sortable')) return;
    const on = idx === st.sortIdx;
    th.classList.toggle('sorted-asc', on && st.sortDir === 'asc'); th.classList.toggle('sorted-desc', on && st.sortDir === 'desc');
    th.setAttribute('aria-sort', on ? (st.sortDir === 'asc' ? 'ascending' : 'descending') : 'none');
    const ic = th.querySelector('.sort-icon'); if (ic) ic.className = 'bi sort-icon ' + (on ? (st.sortDir === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up');
  });
  // footer
  const noun = st.cfg.noun || 'records';
  document.getElementById(id + 'Counter').textContent = total ? `Showing ${start + 1}\u2013${Math.min(start + st.size, total)} of ${total} ${total === 1 ? noun.replace(/s$/, '') : noun}` : `Showing 0 of 0 ${noun}`;
  document.getElementById(id + 'Prev').disabled = st.page <= 1; document.getElementById(id + 'Next').disabled = st.page >= pages;
  document.getElementById(id + 'Pages').innerHTML = Array.from({ length: pages }, (_, i) => i + 1)
    .filter(n => n === 1 || n === pages || Math.abs(n - st.page) <= 1)
    .reduce((a, n, i, arr) => a.concat(i && n - arr[i - 1] > 1 ? ['\u2026'] : [], n), [])
    .map(n => n === '\u2026' ? '<span class="text-muted-custom px-1">\u2026</span>' : `<button type="button" data-page="${n}" class="btn btn-sm ${n === st.page ? 'btn-scholarship-green' : 'btn-outline-scholarship'}">${n}</button>`).join('');
  // mobile cards
  st.cards.innerHTML = '';
  pageRows.forEach(r => {
    const cells = Array.from(r.cells), c = st.cfg, card = document.createElement('div'); card.className = 'row-card-mobile';
    let top = `<div class="rc-top"><div class="scholar-cell">${cells[c.primary].innerHTML}</div>${c.badge != null ? cells[c.badge].innerHTML : ''}</div>`;
    let lines = cells.map((td, i) => (i === c.primary || i === c.badge || i === c.actions || i === c.select) ? '' : `<div class="rc-line"><span class="rc-label">${st.labels[i]}</span><span class="rc-value">${td.innerHTML}</span></div>`).join('');
    card.innerHTML = top + lines + (c.actions != null ? '<div class="rc-actions"></div>' : '');
    const box = card.querySelector('.rc-actions');
    if (box) cells[c.actions].querySelectorAll('button').forEach(orig => { const b = orig.cloneNode(true); b.removeAttribute('onclick'); b.addEventListener('click', () => orig.click()); box.appendChild(b); });
    st.cards.appendChild(card);
  });
  if (st.obs) st.obs.takeRecords(); // discard our own reorder mutations so the observer never loops
  st.busy = false;
}
function filterOfficerDirectory() {
  const q = (document.getElementById('officerDirectorySearch')?.value || '').toLowerCase().trim(); let visible = 0;
  document.querySelectorAll('#officerDirectoryTable .officer-row').forEach(row => { const ok = !q || (row.dataset.search || '').toLowerCase().includes(q); row.dataset.filtered = ok ? '' : 'out'; if (ok) visible++; });
  const empty = document.getElementById('officerNoResults'); if (empty) empty.style.display = visible ? 'none' : 'table-row';
  refreshAppTable('officerDirectoryTable', true);
}
initAppTable('pendingApprovalsTable', { sortable: [1, 2, 3, 4, 5], primary: 2, badge: null, actions: 6, select: 0, noun: 'scholars' });
initAppTable('officerDirectoryTable', { sortable: [0, 1, 2], primary: 0, badge: 2, actions: 3, noun: 'officers' });
initAppTable('scholarDirectoryTable', { sortable: [0, 1, 2, 3, 4, 5, 6], primary: 1, badge: null, actions: 7, noun: 'scholars' });

/* ============================================================
   INIT
   ============================================================ */
showPage('dashboard');
syncScholarshipRows();
syncSampleCounters();
checkEmptyPendingTable();
filterScholarApprovalQueue();
filterScholarDirectory();

/* Accessibility interaction helpers — event delegation so this also
   covers .bar-col elements the analytics filters render dynamically. */
function toggleBarTooltip(bar) {
  document.querySelectorAll('.bar-col.is-tooltip-visible').forEach(other => { if (other !== bar) { other.classList.remove('is-tooltip-visible'); other.setAttribute('aria-expanded', 'false'); } });
  bar.classList.toggle('is-tooltip-visible');
  bar.setAttribute('aria-expanded', bar.classList.contains('is-tooltip-visible') ? 'true' : 'false');
}
document.addEventListener('click', event => {
  const bar = event.target.closest('.bar-col');
  if (bar) { toggleBarTooltip(bar); return; }
  document.querySelectorAll('.bar-col.is-tooltip-visible').forEach(b => { b.classList.remove('is-tooltip-visible'); b.setAttribute('aria-expanded','false'); });
});
document.addEventListener('keydown', event => {
  if (event.key !== 'Enter' && event.key !== ' ') return;
  const bar = event.target.closest('.bar-col');
  if (!bar) return;
  event.preventDefault();
  toggleBarTooltip(bar);
});
document.querySelectorAll('.row-hoverable').forEach(row => {
  row.addEventListener('keydown', event => {
    if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); row.click(); }
  });
});


/* ---------- next block ---------- */
/* Interaction accessibility: preserve trigger focus and synchronize expanded rows. */
(() => {
  const offcanvasTriggers = new WeakMap();
  document.querySelectorAll('.offcanvas').forEach(panel => {
    panel.addEventListener('show.bs.offcanvas', () => {
      const trigger = document.activeElement;
      offcanvasTriggers.set(panel, trigger && trigger !== document.body ? trigger : null);
      document.body.classList.add('offcanvas-open');
    });
    panel.addEventListener('hidden.bs.offcanvas', () => {
      document.body.classList.remove('offcanvas-open');
      const trigger = offcanvasTriggers.get(panel);
      if (trigger && document.contains(trigger)) trigger.focus({ preventScroll: true });
    });
  });

  document.addEventListener('click', event => {
    const control = event.target.closest('[data-expand-row], .expand-chevron');
    if (!control) return;
    const row = control.closest('.row-hoverable');
    if (!row) return;
    const expanded = row.getAttribute('aria-expanded') === 'true';
    row.setAttribute('aria-expanded', String(!expanded));
    control.setAttribute('aria-expanded', String(!expanded));
    row.classList.toggle('row-expanded', !expanded);
    const targetId = control.getAttribute('aria-controls') || control.dataset.expandRow;
    const detail = targetId && document.getElementById(targetId);
    if (detail) {
      detail.classList.toggle('show', !expanded);
      detail.setAttribute('aria-hidden', String(expanded));
    }
  });

  document.querySelectorAll('.row-hoverable').forEach(row => {
    if (!row.hasAttribute('aria-expanded')) row.setAttribute('aria-expanded', 'false');
    const control = row.querySelector('.expand-chevron');
    if (control && !control.hasAttribute('aria-expanded')) control.setAttribute('aria-expanded', 'false');
  });
})();
