<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Code Latte – Attendance Tracking</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:       #f5f0e8;
      --sidebar:  #f0ebe0;
      --card:     #faf7f2;
      --border:   #e3ddd2;
      --text:     #2c2416;
      --muted:    #8a7f6e;
      --accent:   #5c4a1e;
      --gold:     #c49a2b;
      --present:  #3d7a4e;
      --absent:   #b84040;
      --late:     #c4842b;
      --leave:    #5a7ab8;
      --radius:   14px;
      --shadow:   0 2px 12px rgba(80,60,20,.08);
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
      width: 200px;
      height: 100vh;
      background: var(--sidebar);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      padding: 24px 0;
      flex-shrink: 0;
    }
    .logo {
      display: flex; align-items: center; gap: 10px;
      padding: 0 20px 28px;
      border-bottom: 1px solid var(--border);
    }
    .logo-icon {
      width: 34px; height: 34px;
      background: var(--accent); border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 15px;
    }
    .logo-text .name { font-family: 'DM Serif Display', serif; font-size: 15px; line-height:1.1; }
    .logo-text .sub  { font-size: 11px; color: var(--muted); }
    nav { flex: 1; padding: 20px 0; }
    nav a {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 20px;
      font-size: 14px; font-weight: 500;
      color: var(--muted); text-decoration: none;
      border-radius: 0 8px 8px 0;
      margin-right: 12px;
      transition: background .15s, color .15s;
    }
    nav a svg { width:16px; height:16px; flex-shrink:0; }
    nav a:hover { background: var(--border); color: var(--text); }
    nav a.active { background: var(--accent); color: #fff; }
    .user-block {
      padding: 16px 20px;
      border-top: 1px solid var(--border);
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
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
      padding: 32px 36px;
      gap: 20px;
    }

    /* ── PAGE HEADER ── */
    h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 26px; font-weight: 400;
      flex-shrink: 0;
    }

    /* ── ATTENDANCE SUB-HEADER ── */
    .section-header {
      display: flex; align-items: center; justify-content: space-between;
      flex-shrink: 0;
    }
    .section-title { font-size: 15px; font-weight: 600; }
    .header-actions { display: flex; gap: 10px; }

    .btn-export {
      padding: 9px 18px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: var(--card);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 500;
      color: var(--text); cursor: pointer;
      transition: background .15s;
    }
    .btn-export:hover { background: var(--border); }

    .btn-log {
      padding: 9px 18px;
      border-radius: 8px;
      border: none;
      background: var(--accent);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 600;
      color: #fff; cursor: pointer;
      transition: background .15s, transform .1s;
    }
    .btn-log:hover { background: #3e3010; transform: translateY(-1px); }

    /* ── STAT CARDS ── */
    .stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      flex-shrink: 0;
    }
    .stat-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 18px 22px;
      box-shadow: var(--shadow);
    }
    .stat-card .label { font-size: 12px; color: var(--muted); font-weight: 500; margin-bottom: 6px; }
    .stat-card .value {
      font-family: 'DM Serif Display', serif;
      font-size: 34px; line-height: 1;
      margin-bottom: 4px;
    }
    .stat-card .desc  { font-size: 12px; color: var(--muted); }
    .stat-card.present .value { color: var(--present); }
    .stat-card.absent  .value { color: var(--absent); }
    .stat-card.late    .value { color: var(--late); }
    .stat-card.leave   .value { color: var(--leave); }

    /* ── TABLE PANEL ── */
    .table-panel {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px 24px;
      box-shadow: var(--shadow);
      display: flex;
      flex-direction: column;
      flex: 1;
      min-height: 0;
    }

    /* ── TOOLBAR ── */
    .toolbar {
      display: flex; align-items: center; gap: 10px;
      margin-bottom: 16px; flex-shrink: 0;
    }
    .search-wrap { position: relative; }
    .search-wrap svg {
      position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
      width: 14px; height: 14px; color: var(--muted); pointer-events: none;
    }
    .search-wrap input {
      padding: 8px 12px 8px 30px;
      border: 1px solid var(--border); border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      width: 200px; outline: none;
      transition: border-color .15s;
    }
    .search-wrap input:focus { border-color: var(--accent); }
    .search-wrap input::placeholder { color: var(--muted); }

    .toolbar input[type="date"],
    .toolbar select {
      padding: 8px 12px;
      border: 1px solid var(--border); border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      outline: none; cursor: pointer;
      transition: border-color .15s;
    }
    .toolbar select {
      appearance: none;
      padding-right: 28px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
    }
    .toolbar input[type="date"]:focus,
    .toolbar select:focus { border-color: var(--accent); }
    .record-count { margin-left: auto; font-size: 12px; color: var(--muted); }

    /* ── SCROLLABLE TABLE ── */
    .table-scroll {
      flex: 1; overflow-y: auto; min-height: 0;
      scrollbar-width: thin;
      scrollbar-color: var(--border) transparent;
    }
    .table-scroll::-webkit-scrollbar { width: 6px; }
    .table-scroll::-webkit-scrollbar-track { background: transparent; }
    .table-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

    table { width: 100%; border-collapse: collapse; }
    thead th {
      text-align: left;
      font-size: 11px; font-weight: 600;
      letter-spacing: .06em; color: var(--muted);
      text-transform: uppercase;
      padding: 10px 12px;
      border-bottom: 1px solid var(--border);
      position: sticky; top: 0;
      background: var(--card); z-index: 1;
    }
    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background .12s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: rgba(92,74,30,.04); }
    tbody td { padding: 13px 12px; font-size: 13px; vertical-align: middle; }

    .date-cell  { color: var(--muted); font-size: 12px; }
    .name-cell  { font-weight: 500; }
    .role-badge {
      display: inline-block; padding: 3px 10px;
      border-radius: 20px;
      background: var(--bg); border: 1px solid var(--border);
      font-size: 12px; color: var(--text);
    }
    .shift-cell { color: var(--muted); }
    .time-cell  { font-size: 13px; }
    .hours-cell { font-size: 13px; font-weight: 500; }
    .dash       { color: var(--border); }

    .status-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .status-badge.present  { background: #e8f5ec; color: var(--present); }
    .status-badge.absent   { background: #fceaea; color: var(--absent); }
    .status-badge.late     { background: #fef3e2; color: var(--late); }
    .status-badge.on-leave { background: #e8eef8; color: var(--leave); }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .actions { display: flex; gap: 6px; }
    .btn-edit {
      padding: 5px 14px; border-radius: 6px;
      border: 1px solid var(--border); background: var(--card);
      font-family: 'DM Sans', sans-serif;
      font-size: 12px; font-weight: 500;
      color: var(--text); cursor: pointer;
      transition: background .12s;
    }
    .btn-edit:hover { background: var(--border); }
    .btn-delete {
      padding: 5px 14px; border-radius: 6px;
      border: 1px solid #f0d0d0; background: #fff8f8;
      font-family: 'DM Sans', sans-serif;
      font-size: 12px; font-weight: 500;
      color: var(--absent); cursor: pointer;
      transition: background .12s;
    }
    .btn-delete:hover { background: #fceaea; }

    /* ── MODAL ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(44,36,22,.45);
      z-index: 100; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    @keyframes fadeUp {
      from { opacity:0; transform: translateY(12px); }
      to   { opacity:1; transform: translateY(0); }
    }

    .modal {
      background: #fff;
      border-radius: var(--radius);
      padding: 28px 32px;
      width: 480px;
      box-shadow: 0 8px 40px rgba(44,36,22,.18);
      animation: fadeUp .22s ease;
      position: relative;
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
      background: none; border: none;
      font-size: 18px; color: var(--muted);
      cursor: pointer; line-height: 1;
      padding: 2px 6px;
      border-radius: 4px;
      transition: background .12s;
    }
    .btn-close:hover { background: var(--border); }

    .modal-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px 20px;
    }
    .modal-group { display: flex; flex-direction: column; gap: 6px; }
    .modal-group label {
      font-size: 11px; font-weight: 600;
      letter-spacing: .06em; text-transform: uppercase;
      color: var(--muted);
    }
    .modal-group select,
    .modal-group input {
      padding: 9px 12px;
      border: 1px solid var(--border); border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      outline: none; width: 100%;
      transition: border-color .15s;
    }
    .modal-group select {
      appearance: none;
      padding-right: 28px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
    }
    .modal-group select:focus,
    .modal-group input:focus { border-color: var(--accent); }

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
      color: #fff; cursor: pointer;
      transition: background .15s;
    }
    .btn-save-modal:hover { background: #3e3010; }

    /* ── DELETE CONFIRM ── */
    .confirm-modal {
      width: 360px; text-align: center;
    }
    .confirm-modal p { font-size: 13px; color: var(--muted); margin-bottom: 22px; }
    .btn-confirm-delete {
      padding: 9px 22px; border-radius: 8px; border: none;
      background: var(--absent);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 600;
      color: #fff; cursor: pointer;
    }

    @keyframes fadeDown {
      from { opacity:0; transform: translateY(-8px); }
      to   { opacity:1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside>
  <div class="logo">
    <div class="logo-icon">{}&#x2609;</div>
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
    <a href="attendance_hr.php" class="active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
      Attendance
    </a>
    <a href="payroll_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      Payroll
    </a>
    <a href="recruitment_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="16.65" y1="16.65" x2="21" y2="21"/></svg>
      Recruitment
    </a>
  </nav>
  <div class="user-block">
    <div class="avatar">A</div>
    <div class="user-info">
      <div class="uname">Admin</div>
      <div class="urole">Store Manager</div>
    </div>
  </div>
</aside>

<!-- MAIN -->
<main>
  <h1>Attendance Tracking</h1>

  <!-- SECTION HEADER -->
  <div class="section-header">
    <span class="section-title">Attendance</span>
    <div class="header-actions">
      <button class="btn-export" onclick="exportCSV()">Export</button>
      <button class="btn-log" onclick="openLogModal()">+ Log Attendance</button>
    </div>
  </div>

  <!-- STAT CARDS -->
  <div class="stats">
    <div class="stat-card present">
      <div class="label">Present</div>
      <div class="value" id="count-present">0</div>
      <div class="desc">Today</div>
    </div>
    <div class="stat-card absent">
      <div class="label">Absent</div>
      <div class="value" id="count-absent">0</div>
      <div class="desc">Today</div>
    </div>
    <div class="stat-card late">
      <div class="label">Late</div>
      <div class="value" id="count-late">0</div>
      <div class="desc">Today</div>
    </div>
    <div class="stat-card leave">
      <div class="label">On Leave</div>
      <div class="value" id="count-leave">0</div>
      <div class="desc">Today</div>
    </div>
  </div>

  <!-- TABLE PANEL -->
  <div class="table-panel">
    <div class="toolbar">
      <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="16.65" y1="16.65" x2="21" y2="21"/></svg>
        <input type="text" id="search-input" placeholder="Search name..." oninput="filterTable()"/>
      </div>
      <input type="date" id="date-filter" onchange="filterTable()"/>
      <select id="status-filter" onchange="filterTable()">
        <option value="">All Status</option>
        <option>Present</option>
        <option>Absent</option>
        <option>Late</option>
        <option>On Leave</option>
      </select>
      <span class="record-count" id="record-count"></span>
    </div>

    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Role</th>
            <th>Shift</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Hours</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="att-table-body"></tbody>
      </table>
    </div>
  </div>
</main>

<!-- LOG / EDIT MODAL -->
<div class="modal-overlay" id="log-modal">
  <div class="modal">
    <div class="modal-top">
      <h2 id="modal-title">Log Attendance</h2>
      <button class="btn-close" onclick="closeLogModal()">✕</button>
    </div>
    <div class="modal-grid">
      <div class="modal-group">
        <label>Employee</label>
        <select id="m-employee">
          <option value="">Select employee…</option>
          <!-- populated dynamically from DB -->
        </select>
      </div>
      <div class="modal-group">
        <label>Date</label>
        <input type="date" id="m-date"/>
      </div>
      <div class="modal-group">
        <label>Shift</label>
        <select id="m-shift">
          <option value="Morning">Morning (6AM–2PM)</option>
          <option value="Afternoon">Afternoon (2PM–10PM)</option>
          <option value="Evening">Evening (10PM–6AM)</option>
        </select>
      </div>
      <div class="modal-group">
        <label>Status</label>
        <select id="m-status" onchange="toggleTimePicker()">
          <option>Present</option>
          <option>Absent</option>
          <option>Late</option>
          <option>On Leave</option>
        </select>
      </div>
      <div class="modal-group" id="time-in-group">
        <label>Time In</label>
        <input type="time" id="m-time-in"/>
      </div>
      <div class="modal-group" id="time-out-group">
        <label>Time Out</label>
        <input type="time" id="m-time-out"/>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel-modal" onclick="closeLogModal()">Cancel</button>
      <button class="btn-save-modal" onclick="saveRecord()">Save Record</button>
    </div>
  </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal-overlay" id="del-modal">
  <div class="modal confirm-modal">
    <div class="modal-top" style="justify-content:center; margin-bottom:10px;">
      <h2>Delete Record?</h2>
    </div>
    <p>This action cannot be undone.</p>
    <div class="modal-actions" style="justify-content:center;">
      <button class="btn-cancel-modal" onclick="closeDelModal()">Cancel</button>
      <button class="btn-confirm-delete" onclick="confirmDelete()">Yes, Delete</button>
    </div>
  </div>
</div>

<script>
  const BASE_ATT = '/hrm_module/src/api/hr/attendance';

  let records     = [];
  let editIdx     = null;
  let deleteIdx   = null;

  // ── LOAD EMPLOYEES INTO DROPDOWN ──
  async function loadEmployeeDropdown() {
    try {
      const res  = await fetch(`${BASE_ATT}/get_employees_dropdown.php`);
      const json = await res.json();
      if (json.success) {
        const sel = document.getElementById('m-employee');
        sel.innerHTML = '<option value="">Select employee…</option>';
        json.data.forEach(e => {
          const opt = document.createElement('option');
          opt.value       = e.emp_id;
          opt.textContent = e.emp_fname + ' ' + e.emp_lname;
          opt.dataset.schedule = e.emp_schedule; // store schedule for auto-fill
          sel.appendChild(opt);
        });
      }
    } catch (err) {
      console.error('Dropdown error:', err);
    }
  }

  // ── LOAD ATTENDANCE RECORDS ──
  async function loadAttendance() {
    try {
      const res  = await fetch(`${BASE_ATT}/get_attendance.php`);
      const json = await res.json();
      if (json.success) {
        records = json.data;
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
    const today = new Date().toISOString().split('T')[0];
    const todayRecords = records.filter(r => r.attendance_date === today);
    document.getElementById('count-present').textContent = todayRecords.filter(r => r.status === 'Present').length;
    document.getElementById('count-absent').textContent  = todayRecords.filter(r => r.status === 'Absent').length;
    document.getElementById('count-late').textContent    = todayRecords.filter(r => r.status === 'Late').length;
    document.getElementById('count-leave').textContent   = todayRecords.filter(r => r.status === 'On Leave').length;
  }

  // ── RENDER TABLE ──
  function statusClass(s) {
    if (s === 'Present')  return 'present';
    if (s === 'Absent')   return 'absent';
    if (s === 'Late')     return 'late';
    if (s === 'On Leave') return 'on-leave';
    return '';
  }

// ── UPDATED formatTime — strip seconds from DB time format ──
function formatTime(t) {
    if (!t || t === '00:00:00') return '–';
    // DB returns "06:03:00", strip seconds
    const parts = t.substring(0, 5).split(':').map(Number);
    const h = parts[0], m = parts[1];
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12  = h % 12 || 12;
    return `${h12}:${String(m).padStart(2,'0')} ${ampm}`;
}


// ── UPDATED calcHours — handles late_minutes fallback ──
function calcHours(time_in, time_out, late_minutes) {
    // if both times exist, calculate normally
    if (time_in && time_out) {
        // strip seconds if present (e.g. "06:03:00" → "06:03")
        const tin  = time_in.substring(0, 5);
        const tout = time_out.substring(0, 5);
        const [ih, im] = tin.split(':').map(Number);
        const [oh, om] = tout.split(':').map(Number);
        const diff = ((oh * 60 + om) - (ih * 60 + im)) / 60;
        return diff > 0 ? diff.toFixed(1) + 'h' : '–';
    }
    // if only late_minutes is available, show that
    if (late_minutes && late_minutes > 0) {
        return late_minutes + ' min late';
    }
    return '–';
}

// ── UPDATED renderTable — use emp_role instead of emp_schedule for Role column ──
function renderTable(list) {
    const tbody = document.getElementById('att-table-body');
    document.getElementById('record-count').textContent =
        list.length + ' record' + (list.length !== 1 ? 's' : '');
    tbody.innerHTML = list.map(r => {
        const realIdx = records.indexOf(r);
        const timeIn  = formatTime(r.time_in);
        const timeOut = formatTime(r.time_out);
        const hours   = calcHours(r.time_in, r.time_out, r.late_minutes); // ← pass late_minutes
        return `
            <tr>
                <td class="date-cell">${r.attendance_date ?? '—'}</td>
                <td class="name-cell">${r.emp_fname} ${r.emp_lname}</td>
                <td><span class="role-badge">${r.emp_role ?? '—'}</span></td>
                <td class="shift-cell">${r.emp_schedule ?? '—'}</td>
                <td class="time-cell ${!r.time_in ? 'dash' : ''}">${timeIn}</td>
                <td class="time-cell ${!r.time_out ? 'dash' : ''}">${timeOut}</td>
                <td class="hours-cell">${hours}</td>
                <td><span class="status-badge ${statusClass(r.status)}">
                    <span class="status-dot"></span>${r.status}
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
  function filterTable() {
    const q  = document.getElementById('search-input').value.toLowerCase();
    const df = document.getElementById('date-filter').value;
    const sf = document.getElementById('status-filter').value;
    const filtered = records.filter(r => {
      const name = (r.emp_fname + ' ' + r.emp_lname).toLowerCase();
      return (!q  || name.includes(q)) &&
             (!df || r.attendance_date === df) &&
             (!sf || r.status === sf);
    });
    renderTable(filtered);
  }

  // ── MODAL ──
  function toggleTimePicker() {
    const s    = document.getElementById('m-status').value;
    const show = (s === 'Present' || s === 'Late');
    document.getElementById('time-in-group').style.display  = show ? '' : 'none';
    document.getElementById('time-out-group').style.display = show ? '' : 'none';
  }

  function openLogModal() {
    editIdx = null;
    document.getElementById('modal-title').textContent = 'Log Attendance';
    document.getElementById('m-employee').value = '';
    document.getElementById('m-date').value     = new Date().toISOString().split('T')[0];
    document.getElementById('m-shift').value    = 'Morning';
    document.getElementById('m-status').value   = 'Present';
    document.getElementById('m-time-in').value  = '';
    document.getElementById('m-time-out').value = '';
    toggleTimePicker();
    document.getElementById('log-modal').classList.add('open');
  }

  function openEditModal(idx) {
    editIdx = idx;
    const r = records[idx];
    document.getElementById('modal-title').textContent  = 'Edit Attendance';
    document.getElementById('m-employee').value = r.emp_id;
    document.getElementById('m-date').value     = r.attendance_date ?? '';
    document.getElementById('m-status').value   = r.status;
    document.getElementById('m-time-in').value  = r.time_in  ?? '';
    document.getElementById('m-time-out').value = r.time_out ?? '';
    toggleTimePicker();
    document.getElementById('log-modal').classList.add('open');
  }

  function closeLogModal() {
    document.getElementById('log-modal').classList.remove('open');
    editIdx = null;
  }

  // ── SAVE ──
  async function saveRecord() {
    const emp_id   = document.getElementById('m-employee').value;
    const rawDate  = document.getElementById('m-date').value;
    const status   = document.getElementById('m-status').value;
    const time_in  = document.getElementById('m-time-in').value  || null;
    const time_out = document.getElementById('m-time-out').value || null;

    if (!emp_id)  { alert('Please select an employee.'); return; }
    if (!rawDate) { alert('Please select a date.'); return; }

    const payload = {
      emp_id,
      attendance_date: rawDate,
      status,
      time_in:  (status === 'Present' || status === 'Late') ? time_in  : null,
      time_out: (status === 'Present' || status === 'Late') ? time_out : null,
    };

    const isEdit = editIdx !== null;
    if (isEdit) payload.attendance_id = records[editIdx].attendance_id;

    try {
      const res  = await fetch(`${BASE_ATT}/${isEdit ? 'update_attendance' : 'add_attendance'}.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      });
      const json = await res.json();
      if (json.success) {
        closeLogModal();
        loadAttendance();
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
      const res  = await fetch(`${BASE_ATT}/delete_attendance.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ attendance_id: records[deleteIdx].attendance_id }),
      });
      const json = await res.json();
      if (json.success) {
        closeDelModal();
        loadAttendance();
      } else {
        alert('Error: ' + json.error);
      }
    } catch (err) {
      console.error('Delete error:', err);
    }
  }

  // ── EXPORT CSV ──
  function exportCSV() {
    const headers = ['Date','Name','Schedule','Time In','Time Out','Hours','Status'];
    const rows = records.map(r => [
      r.attendance_date,
      r.emp_fname + ' ' + r.emp_lname,
      r.emp_schedule,
      formatTime(r.time_in),
      formatTime(r.time_out),
      calcHours(r.time_in, r.time_out),
      r.status
    ]);
    const csv = [headers, ...rows].map(r => r.map(v => `"${v}"`).join(',')).join('\n');
    const a   = document.createElement('a');
    a.href    = 'data:text/csv,' + encodeURIComponent(csv);
    a.download = 'attendance.csv';
    a.click();
  }

  // close modals on overlay click
  document.getElementById('log-modal').addEventListener('click', function(e) { if(e.target===this) closeLogModal(); });
  document.getElementById('del-modal').addEventListener('click', function(e) { if(e.target===this) closeDelModal(); });

  // ── INIT ──
  loadEmployeeDropdown();
  loadAttendance();
</script>
</body>
</html>