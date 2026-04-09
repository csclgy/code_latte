<?php require_once '../src/api/auth/check_session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Code Latte – Recruitment Management</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:         #f5f0e8;
      --sidebar:    #f0ebe0;
      --card:       #faf7f2;
      --border:     #e3ddd2;
      --text:       #2c2416;
      --muted:      #8a7f6e;
      --accent:     #5c4a1e;
      --gold:       #c49a2b;
      --interview:  #5a7ab8;
      --applied:    #c4842b;
      --screening:  #b87a2b;
      --hired:      #3d7a4e;
      --rejected:   #b84040;
      --radius:     14px;
      --shadow:     0 2px 12px rgba(80,60,20,.08);
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* ── SIDEBAR ── */
    aside {
      width: 200px; height: 100vh;
      background: var(--sidebar);
      border-right: 1px solid var(--border);
      display: flex; flex-direction: column;
      padding: 24px 0; flex-shrink: 0;
    }
    .logo {
      display: flex; align-items: center; gap: 10px;
      padding: 0 20px 28px;
      border-bottom: 1px solid var(--border);
    }
    .logo-icon { width:34px; height:34px; background:var(--accent); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .logo-icon img { width:100%; height:100%; object-fit:cover; border-radius:8px; }
    .logo-text .name { font-family: 'DM Serif Display', serif; font-size: 15px; line-height:1.1; }
    .logo-text .sub  { font-size: 11px; color: var(--muted); }
    nav { flex: 1; padding: 20px 0; }
    nav a {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 20px; font-size: 14px; font-weight: 500;
      color: var(--muted); text-decoration: none;
      border-radius: 0 8px 8px 0; margin-right: 12px;
      transition: background .15s, color .15s;
    }
    nav a svg { width:16px; height:16px; flex-shrink:0; }
    nav a:hover { background: var(--border); color: var(--text); }
    nav a.active { background: var(--accent); color: #fff; }
    .user-block {
      padding: 16px 20px; border-top: 1px solid var(--border);
      display: flex; align-items: center; gap: 10px;
    }
    .avatar {
      width: 34px; height: 34px; border-radius: 50%;
      background: var(--gold);
      display: flex; align-items: center; justify-content: center;
      font-weight: 600; font-size: 13px; color: #fff;
    }
    .user-info .uname { font-size: 13px; font-weight: 600; }
    .user-info .urole { font-size: 11px; color: var(--muted); }

    /* ── MAIN ── */
    main {
      flex: 1; display: flex; flex-direction: column;
      height: 100vh; overflow: hidden;
      padding: 32px 36px; gap: 20px;
    }

    h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 26px; font-weight: 400; flex-shrink: 0;
    }

    /* ── SECTION HEADER ── */
    .section-header {
      display: flex; align-items: center; justify-content: space-between;
      flex-shrink: 0;
    }
    .section-title { font-size: 15px; font-weight: 600; }
    .btn-add {
      padding: 9px 18px; border-radius: 8px; border: none;
      background: var(--accent);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 600;
      color: #fff; cursor: pointer;
      transition: background .15s, transform .1s;
    }
    .btn-add:hover { background: #3e3010; transform: translateY(-1px); }

    /* ── STAT CARDS ── */
    .stats {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 16px; flex-shrink: 0;
    }
    .stat-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 20px 24px;
      box-shadow: var(--shadow);
      animation: fadeUp .35s ease both;
    }
    .stat-card:nth-child(1){ animation-delay:.05s }
    .stat-card:nth-child(2){ animation-delay:.10s }
    .stat-card:nth-child(3){ animation-delay:.15s }
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(12px); }
      to   { opacity:1; transform:translateY(0); }
    }
    .stat-card .label { font-size: 12px; color: var(--muted); font-weight: 500; margin-bottom: 6px; }
    .stat-card .value {
      font-family: 'DM Serif Display', serif;
      font-size: 36px; line-height:1; margin-bottom: 4px;
      color: var(--text);
    }
    .stat-card .desc { font-size: 12px; color: var(--muted); }

    /* ── TABLE PANEL ── */
    .table-panel {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 20px 24px;
      box-shadow: var(--shadow);
      display: flex; flex-direction: column;
      flex: 1; min-height: 0;
      animation: fadeUp .4s ease .2s both;
    }

    /* ── TOOLBAR ── */
    .toolbar {
      display: flex; align-items: center; gap: 10px;
      margin-bottom: 16px; flex-shrink: 0;
    }
    .search-wrap { position: relative; }
    .search-wrap svg {
      position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
      width:14px; height:14px; color: var(--muted); pointer-events:none;
    }
    .search-wrap input {
      padding: 8px 12px 8px 30px;
      border: 1px solid var(--border); border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      width: 220px; outline: none;
      transition: border-color .15s;
    }
    .search-wrap input:focus { border-color: var(--accent); }
    .search-wrap input::placeholder { color: var(--muted); }
    .toolbar select {
      padding: 8px 28px 8px 12px;
      border: 1px solid var(--border); border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      cursor: pointer; outline: none; appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center;
      transition: border-color .15s;
    }
    .toolbar select:focus { border-color: var(--accent); }
    .record-count { margin-left: auto; font-size: 12px; color: var(--muted); }

    /* ── SCROLLABLE TABLE ── */
    .table-scroll {
      flex: 1; overflow-y: auto; min-height: 0;
      scrollbar-width: thin;
      scrollbar-color: var(--border) transparent;
    }
    .table-scroll::-webkit-scrollbar { width: 6px; }
    .table-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

    table { width: 100%; border-collapse: collapse; }
    thead th {
      text-align: left; font-size: 11px; font-weight: 600;
      letter-spacing:.06em; color: var(--muted); text-transform: uppercase;
      padding: 10px 12px; border-bottom: 1px solid var(--border);
      position: sticky; top: 0; background: var(--card); z-index: 1;
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: rgba(92,74,30,.04); }
    tbody td { padding: 14px 12px; font-size: 13px; vertical-align: middle; }

    .name-cell  { font-weight: 500; }
    .email-cell { color: var(--muted); font-size: 12px; }
    .pos-badge {
      display: inline-block; padding: 3px 10px;
      border-radius: 20px;
      background: var(--bg); border: 1px solid var(--border);
      font-size: 12px; color: var(--text);
    }
    .date-cell { color: var(--muted); font-size: 13px; }

    .status-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .status-badge.interview  { background: #e8eef8; color: var(--interview); }
    .status-badge.applied    { background: #fef3e2; color: var(--applied); }
    .status-badge.screening  { background: #fef3e2; color: var(--screening); }
    .status-badge.hired      { background: #e8f5ec; color: var(--hired); }
    .status-badge.rejected   { background: #fceaea; color: var(--rejected); }
    .status-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }

    .actions { display: flex; gap: 6px; }
    .btn-edit {
      padding: 5px 14px; border-radius: 6px;
      border: 1px solid var(--border); background: var(--card);
      font-family: 'DM Sans', sans-serif;
      font-size: 12px; font-weight: 500;
      color: var(--text); cursor: pointer; transition: background .12s;
    }
    .btn-edit:hover { background: var(--border); }
    .btn-delete {
      padding: 5px 14px; border-radius: 6px;
      border: 1px solid #f0d0d0; background: #fff8f8;
      font-family: 'DM Sans', sans-serif;
      font-size: 12px; font-weight: 500;
      color: var(--rejected); cursor: pointer; transition: background .12s;
    }
    .btn-delete:hover { background: #fceaea; }

    /* ── MODAL ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(44,36,22,.45);
      z-index: 100; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    @keyframes modalIn {
      from { opacity:0; transform: translateY(14px); }
      to   { opacity:1; transform: translateY(0); }
    }
    .modal {
      background: #fff; border-radius: var(--radius);
      padding: 28px 32px; width: 480px;
      box-shadow: 0 8px 40px rgba(44,36,22,.18);
      animation: modalIn .22s ease;
    }
    .modal-top {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 22px;
    }
    .modal-top h2 {
      font-family: 'DM Serif Display', serif;
      font-size: 20px; font-weight: 400;
    }
    .btn-close {
      background: none; border: none; font-size: 18px;
      color: var(--muted); cursor: pointer; padding: 2px 6px;
      border-radius: 4px; transition: background .12s;
    }
    .btn-close:hover { background: var(--border); }

    .modal-grid {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 14px 20px;
    }
    .modal-group { display: flex; flex-direction: column; gap: 6px; }
    .modal-group label {
      font-size: 11px; font-weight: 600;
      letter-spacing:.06em; text-transform: uppercase; color: var(--muted);
    }
    .modal-group input,
    .modal-group select {
      padding: 9px 12px; border: 1px solid var(--border);
      border-radius: 8px; background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      outline: none; width: 100%;
      transition: border-color .15s;
    }
    .modal-group input:focus,
    .modal-group select:focus { border-color: var(--accent); }
    .modal-group input::placeholder { color: #bbb5a8; }
    .modal-group select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center;
      padding-right: 28px;
    }

    .modal-actions {
      display: flex; justify-content: flex-end; gap: 10px;
      margin-top: 22px;
    }
    .btn-cancel-modal {
      padding: 9px 18px; border-radius: 8px;
      border: 1px solid var(--border); background: transparent;
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 500;
      color: var(--muted); cursor: pointer;
    }
    .btn-cancel-modal:hover { background: var(--border); }
    .btn-save-modal {
      padding: 9px 22px; border-radius: 8px; border: none;
      background: var(--accent);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 600;
      color: #fff; cursor: pointer; transition: background .15s;
    }
    .btn-save-modal:hover { background: #3e3010; }

    /* Delete confirm */
    .confirm-modal { width: 360px; text-align: center; }
    .confirm-modal .modal-top { justify-content: center; margin-bottom: 10px; }
    .confirm-modal p { font-size: 13px; color: var(--muted); margin-bottom: 22px; }
    .btn-confirm-delete {
      padding: 9px 22px; border-radius: 8px; border: none;
      background: var(--rejected);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 600;
      color: #fff; cursor: pointer;
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside>
  <div class="logo">
    <div class="logo-icon">
      <img src="../assets/images/code_latte.png" alt="Code Latte Logo" onerror="this.style.display='none'; this.parentElement.textContent='☕';">
    </div>
    <div class="logo-text">
      <div class="name">Code Latte</div>
      <div class="sub">HR System</div>
    </div>
  </div>
  <nav>
    <a href="dashboard_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a href="employee_management_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Employees
    </a>
    <a href="attendance_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
      Attendance
    </a>
    <a href="payroll_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      Payroll
    </a>
    <a href="recruitment_hr.php" class="active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="16.65" y1="16.65" x2="21" y2="21"/></svg>
      Recruitment
    </a>
  </nav>
  <div class="user-block">
    <div class="avatar">
      <?= strtoupper(substr($_SESSION['emp_fname'] ?? 'A', 0, 1)) ?>
    </div>
    <div class="user-info">
      <div class="uname"><?= htmlspecialchars($_SESSION['emp_fname'] ?? 'Admin') ?></div>
      <div class="urole"><?= htmlspecialchars($_SESSION['pos_name'] ?? 'Staff') ?></div>
    </div>
    <a href="/hrm_module/src/api/auth/logout.php" title="Logout" style="margin-left:auto; color:var(--muted);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </a>
  </div>
</aside>

<!-- MAIN -->
<main>
  <h1>Recruitment Management</h1>

  <!-- SECTION HEADER -->
  <div class="section-header">
    <span class="section-title">Recruitment</span>
    <button class="btn-add" onclick="openAddModal()">+ Add Applicant</button>
  </div>

  <!-- STAT CARDS -->
  <div class="stats">
    <div class="stat-card">
      <div class="label">Total Applicants</div>
      <div class="value" id="stat-total">0</div>
      <div class="desc">All records</div>
    </div>
    <div class="stat-card">
      <div class="label">For Interview</div>
      <div class="value" id="stat-interview">0</div>
      <div class="desc">Shortlisted</div>
    </div>
    <div class="stat-card">
      <div class="label">Hired</div>
      <div class="value" id="stat-hired">0</div>
      <div class="desc">This month</div>
    </div>
  </div>

  <!-- TABLE PANEL -->
  <div class="table-panel">
    <div class="toolbar">
      <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="16.65" y1="16.65" x2="21" y2="21"/></svg>
        <input type="text" id="search-input" placeholder="Search applicant..." oninput="filterTable()"/>
      </div>
      <select id="status-filter" onchange="filterTable()">
        <option value="">All Status</option>
        <option>Applied</option>
        <option>Screening</option>
        <option>Interview</option>
        <option>Hired</option>
        <option>Rejected</option>
      </select>
      <span class="record-count" id="record-count"></span>
    </div>

    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Position Applied</th>
            <th>Date Applied</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="rec-table-body"></tbody>
      </table>
    </div>
  </div>
</main>

<!-- ADD / EDIT MODAL -->
<div class="modal-overlay" id="app-modal">
  <div class="modal">
    <div class="modal-top">
      <h2 id="modal-title">Add Applicant</h2>
      <button class="btn-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-grid">
      <div class="modal-group">
        <label>First Name</label>
        <input type="text" id="m-first" placeholder="First name"/>
      </div>
      <div class="modal-group">
        <label>Last Name</label>
        <input type="text" id="m-last" placeholder="Last name"/>
      </div>
      <div class="modal-group">
        <label>Email</label>
        <input type="email" id="m-email" placeholder="email@example.com"/>
      </div>
      <div class="modal-group">
        <label>Date Applied</label>
        <input type="date" id="m-date"/>
      </div>
      <div class="modal-group">
        <label>Position Applied</label>
        <select id="m-vacancy">
          <option value="">Select position…</option>
        </select>
      </div>
      <div class="modal-group">
        <label>Status</label>
        <select id="m-status">
          <option>Applied</option>
          <option>Screening</option>
          <option>Interview</option>
          <option>Hired</option>
          <option>Rejected</option>
        </select>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel-modal" onclick="closeModal()">Cancel</button>
      <button class="btn-save-modal" onclick="saveApplicant()">Save</button>
    </div>
  </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal-overlay" id="del-modal">
  <div class="modal confirm-modal">
    <div class="modal-top">
      <h2>Delete Applicant?</h2>
    </div>
    <p>This action cannot be undone.</p>
    <div class="modal-actions" style="justify-content:center;">
      <button class="btn-cancel-modal" onclick="closeDelModal()">Cancel</button>
      <button class="btn-confirm-delete" onclick="confirmDelete()">Yes, Delete</button>
    </div>
  </div>
</div>

<script>
  const BASE_REC = '/hrm_module/src/api/hr/recruitment';

  let applicants = [];
  let editIdx    = null;
  let deleteIdx  = null;
  let positionMap = {};

  // ── LOAD VACANCIES INTO DROPDOWN ──
  async function loadVacancyDropdown() {
      try {
          const res  = await fetch('/hrm_module/src/api/hr/employees/get_positions.php');
          const json = await res.json();
          if (json.success) {
              const sel = document.getElementById('m-vacancy');
              sel.innerHTML = '<option value="">Select position…</option>';
              json.data.forEach(p => {
                  positionMap[String(p.pos_id)] = p.pos_name; // ← store for table display
                  const opt = document.createElement('option');
                  opt.value       = p.pos_id;
                  opt.textContent = p.pos_name;
                  sel.appendChild(opt);
              });
          }
      } catch (err) {
          console.error('Position dropdown error:', err);
      }
  }

  // ── LOAD APPLICANTS ──
  async function loadApplicants() {
    try {
      const res  = await fetch(`${BASE_REC}/get_applicants.php`);
      const json = await res.json();
      if (json.success) {
        applicants = json.data;
        updateStats();
        filterTable();
      } else {
        console.error('Failed to load:', json.error);
      }
    } catch (err) {
      console.error('Load error:', err);
    }
  }

  // ── STATS ──
  function updateStats() {
    const thisMonth = new Date().toISOString().slice(0, 7);
    document.getElementById('stat-total').textContent =
      applicants.length;
    document.getElementById('stat-interview').textContent =
      applicants.filter(a => a.application_status === 'Interview').length;
    document.getElementById('stat-hired').textContent =
      applicants.filter(a =>
        a.application_status === 'Hired' &&
        a.result_date && a.result_date.startsWith(thisMonth)
      ).length;
  }

  // ── STATUS CLASS ──
  function statusClass(s) {
    if (!s) return '';
    return s.toLowerCase().replace(' ', '-');
  }

  // ── RENDER TABLE ──
  function renderTable(list) {
    if (!list) list = getFiltered();
    document.getElementById('record-count').textContent =
        list.length + ' record' + (list.length !== 1 ? 's' : '');
    const tbody = document.getElementById('rec-table-body');
    tbody.innerHTML = list.map(a => {
        const realIdx = applicants.indexOf(a);
        return `
            <tr>
                <td class="name-cell">${a.f_name} ${a.l_name}</td>
                <td class="email-cell">${a.email ?? '—'}</td>
                <td><span class="pos-badge">${a.pos_name ?? positionMap[String(a.pos_id)] ?? '—'}</span></td>
                <td class="date-cell">${a.application_date ?? '—'}</td>
                <td><span class="status-badge ${statusClass(a.application_status)}">
                    <span class="status-dot"></span>${a.application_status}
                </span></td>
                <td>
                    <div class="actions">
                        <button class="btn-edit" onclick="openEditModal(${realIdx})">Edit</button>
                        <button class="btn-delete" onclick="openDelModal(${realIdx})">Delete</button>
                    </div>
                </td>
            </tr>`;
    }).join('');
  }

  // ── FILTER ──
  function getFiltered() {
    const q  = document.getElementById('search-input').value.toLowerCase();
    const sf = document.getElementById('status-filter').value;
    return applicants.filter(a => {
      const name = (a.f_name + ' ' + a.l_name).toLowerCase();
      return (!q  || name.includes(q) || (a.email ?? '').toLowerCase().includes(q)) &&
             (!sf || a.application_status === sf);
    });
  }
  function filterTable() { renderTable(getFiltered()); }

  // ── OPEN ADD MODAL ──
  function openAddModal() {
    editIdx = null;
    document.getElementById('modal-title').textContent = 'Add Applicant';
    document.getElementById('m-first').value    = '';
    document.getElementById('m-last').value     = '';
    document.getElementById('m-email').value    = '';
    document.getElementById('m-vacancy').value  = '';
    document.getElementById('m-status').value   = 'Applied';
    document.getElementById('m-date').value     = new Date().toISOString().split('T')[0];
    document.getElementById('app-modal').classList.add('open');
  }

  // ── OPEN EDIT MODAL ──
  function openEditModal(idx) {
      editIdx = idx;
      const a = applicants[idx];
      document.getElementById('modal-title').textContent = 'Edit Applicant';
      document.getElementById('m-first').value   = a.f_name            ?? '';
      document.getElementById('m-last').value    = a.l_name            ?? '';
      document.getElementById('m-email').value   = a.email             ?? '';
      document.getElementById('m-vacancy').value = a.pos_id            ?? ''; // ← vacancy_id → pos_id
      document.getElementById('m-status').value  = a.application_status ?? 'Applied';
      document.getElementById('m-date').value    = a.application_date  ?? '';
      document.getElementById('app-modal').classList.add('open');
  }

  function closeModal() {
    document.getElementById('app-modal').classList.remove('open');
    editIdx = null;
  }

  // ── SAVE ──
  async function saveApplicant() {
    const first      = document.getElementById('m-first').value.trim();
    const last       = document.getElementById('m-last').value.trim();
    const email      = document.getElementById('m-email').value.trim();
    const vacancy_id = document.getElementById('m-vacancy').value;
    const status     = document.getElementById('m-status').value;
    const rawDate    = document.getElementById('m-date').value;

    if (!first || !last) { alert('Please enter first and last name.'); return; }
    if (!rawDate)        { alert('Please select an application date.'); return; }

    const payload = {
        f_name:             first,
        l_name:             last,
        email:              email || null,
        pos_id:             document.getElementById('m-vacancy').value || null, // ← vacancy_id → pos_id
        application_date:   rawDate,
        application_status: status,
    };

    const isEdit = editIdx !== null;
    if (isEdit) payload.applicant_id = applicants[editIdx].applicant_id;

    try {
      const res  = await fetch(`${BASE_REC}/${isEdit ? 'update_applicant' : 'add_applicant'}.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      });
      const json = await res.json();
      if (json.success) {
        closeModal();
        loadApplicants();
      } else {
        alert('Error: ' + json.error);
      }
    } catch (err) {
      console.error('Save error:', err);
      alert('Something went wrong. Check the console.');
    }
  }

  // ── DELETE ──
  function openDelModal(idx) {
    deleteIdx = idx;
    document.getElementById('del-modal').classList.add('open');
  }
  function closeDelModal() {
    document.getElementById('del-modal').classList.remove('open');
  }
  async function confirmDelete() {
    if (deleteIdx === null) return;
    try {
      const res  = await fetch(`${BASE_REC}/delete_applicant.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ applicant_id: applicants[deleteIdx].applicant_id }),
      });
      const json = await res.json();
      if (json.success) {
        closeDelModal();
        loadApplicants();
      } else {
        alert('Error: ' + json.error);
      }
    } catch (err) {
      console.error('Delete error:', err);
    }
  }

  // close modals on overlay click
  document.getElementById('app-modal').addEventListener('click', function(e) { if(e.target===this) closeModal(); });
  document.getElementById('del-modal').addEventListener('click', function(e) { if(e.target===this) closeDelModal(); });

  // ── INIT ──
  async function init() {
      await loadVacancyDropdown(); // wait for positions to load first
      loadApplicants();            // then load applicants so positionMap is ready
  }

  init();
</script>
</body>
</html>