<?php require_once '../src/api/auth/check_session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Code Latte – Employee Management</title>
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
      --leave:    #c4842b;
      --absent:   #b84040;
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
    .logo-icon { width:34px; height:34px; background:var(--accent); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .logo-icon img { width:100%; height:100%; object-fit:cover; border-radius:8px; }
    .logo-text .name { font-family: 'DM Serif Display', serif; font-size: 15px; line-height:1.1; }
    .logo-text .sub  { font-size: 11px; color: var(--muted); }
    nav { flex: 1; padding: 20px 0; }
    nav a {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 20px;
      font-size: 14px; font-weight: 500;
      color: var(--muted);
      text-decoration: none;
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

    .page-header {
      display: flex; align-items: center; justify-content: space-between;
      flex-shrink: 0;
    }
    h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 26px; font-weight: 400;
    }
    .btn-add {
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px 18px;
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 600;
      cursor: pointer;
      transition: background .15s, transform .1s;
    }
    .btn-add:hover { background: #3e3010; transform: translateY(-1px); }

    /* ── INLINE FORM PANEL ── */
    .form-panel {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 26px 28px;
      box-shadow: var(--shadow);
      flex-shrink: 0;
      display: none;
      animation: fadeDown .25s ease;
    }
    .form-panel.open { display: block; }

    @keyframes fadeDown {
      from { opacity:0; transform: translateY(-8px); }
      to   { opacity:1; transform: translateY(0); }
    }

    .form-panel h2 { font-size: 15px; font-weight: 600; margin-bottom: 20px; }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px 24px;
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label {
      font-size: 11px; font-weight: 600;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--muted);
    }
    .form-group input,
    .form-group select {
      padding: 10px 14px;
      border: 1px solid var(--border);
      border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      outline: none;
      transition: border-color .15s;
      width: 100%;
    }
    .form-group input:focus,
    .form-group select:focus { border-color: var(--accent); }
    .form-group input::placeholder { color: #bbb5a8; }
    .form-group select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      padding-right: 32px;
    }
    .form-actions { display: flex; gap: 10px; margin-top: 20px; }
    .btn-save {
      padding: 9px 22px; border-radius: 8px; border: none;
      background: var(--accent);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 600;
      color: #fff; cursor: pointer;
      transition: background .15s;
    }
    .btn-save:hover { background: #3e3010; }
    .btn-cancel {
      padding: 9px 18px; border-radius: 8px;
      border: 1px solid var(--border);
      background: transparent;
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 500;
      color: var(--muted); cursor: pointer;
    }
    .btn-cancel:hover { background: var(--border); }

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
      animation: fadeUp .35s ease both;
    }
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(12px); }
      to   { opacity:1; transform:translateY(0); }
    }

    /* ── TOOLBAR ── */
    .toolbar {
      display: flex; align-items: center; gap: 10px;
      margin-bottom: 16px;
      flex-shrink: 0;
    }
    .search-wrap { position: relative; }
    .search-wrap svg {
      position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
      width:14px; height:14px; color: var(--muted); pointer-events:none;
    }
    .search-wrap input {
      padding: 8px 12px 8px 30px;
      border: 1px solid var(--border);
      border-radius: 8px;
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
      border: 1px solid var(--border);
      border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      cursor: pointer; outline: none; appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      transition: border-color .15s;
    }
    .toolbar select:focus { border-color: var(--accent); }
    .record-count { margin-left: auto; font-size: 12px; color: var(--muted); }

    /* ── SCROLLABLE TABLE ── */
    .table-scroll {
      flex: 1;
      overflow-y: auto;
      min-height: 0;
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
      letter-spacing: .06em;
      color: var(--muted);
      text-transform: uppercase;
      padding: 10px 12px;
      border-bottom: 1px solid var(--border);
      position: sticky; top: 0;
      background: var(--card);
      z-index: 1;
    }
    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background .12s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: rgba(92,74,30,.04); }
    tbody td { padding: 14px 12px; font-size: 13px; vertical-align: middle; }
    .id-cell   { color: var(--muted); font-size: 12px; font-weight: 500; }
    .name-cell { font-weight: 500; }
    .role-badge {
      display: inline-block; padding: 3px 10px;
      border-radius: 20px;
      background: var(--bg); border: 1px solid var(--border);
      font-size: 12px; color: var(--text);
    }
    .shift-cell { color: var(--muted); }
    .date-cell  { color: var(--muted); }
    .status-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .status-badge.active   { background: #e8f5ec; color: var(--present); }
    .status-badge.on-leave { background: #fef3e2; color: var(--leave); }
    .status-badge.inactive { background: #fceaea; color: var(--absent); }
    .status-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }
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

    /* ── DELETE CONFIRM MODAL ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(44,36,22,.45);
      z-index: 100; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--card); border-radius: var(--radius);
      padding: 28px 32px; width: 360px; text-align: center;
      box-shadow: 0 8px 40px rgba(44,36,22,.18);
      animation: fadeDown .2s ease;
    }
    .modal h2 {
      font-family: 'DM Serif Display', serif;
      font-size: 20px; font-weight: 400; margin-bottom: 10px;
    }
    .modal p { font-size: 13px; color: var(--muted); margin-bottom: 22px; }
    .modal-actions { display: flex; justify-content: center; gap: 10px; }
    .btn-confirm-delete {
      padding: 9px 22px; border-radius: 8px; border: none;
      background: var(--absent);
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
    <a href="employee_management_hr.php" class="active">
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
    <a href="recruitment_hr.php">
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
  <div class="page-header">
    <h1>Employee Management</h1>
    <button class="btn-add" onclick="toggleForm()">+ Add Employee</button>
  </div>

  <!-- INLINE FORM -->
<div class="form-panel" id="emp-form-panel">
  <h2 id="form-title">New Employee</h2>
  <div class="form-grid">

    <div class="form-group">
      <label>First Name</label>
      <input type="text" id="f-fname" name="emp_fname" placeholder="First name"/>
    </div>

    <div class="form-group">
      <label>Last Name</label>
      <input type="text" id="f-lname" name="emp_lname" placeholder="Last name"/>
    </div>

    <div class="form-group">
      <label>Middle Name</label>
      <input type="text" id="f-mname" name="emp_mname" placeholder="Middle name"/>
    </div>

    <div class="form-group">
      <label>Age</label>
      <input type="number" id="f-age" name="emp_age" placeholder="e.g. 25" min="16" max="80"/>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" id="f-email" name="emp_email" placeholder="email@codelatte.com"/>
    </div>

    <div class="form-group">
      <label>Contact</label>
      <input type="text" id="f-contact" name="emp_contact" placeholder="+63 9XX XXX XXXX"/>
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
      <label>Address</label>
      <input type="text" id="f-address" name="emp_address" placeholder="Street, Barangay, City"/>
    </div>

  <div class="form-group">
    <label>Department</label>
    <select id="f-dept" name="dept_id">
      <option value="">Select department...</option>
      <!-- populated dynamically -->
    </select>
  </div>

  <div class="form-group">
    <label>Position</label>
    <select id="f-pos" name="pos_id">
      <option value="">Select position...</option>
      <!-- populated dynamically -->
    </select>
  </div>

    <div class="form-group">
      <label>Schedule</label>
      <select id="f-schedule" name="emp_schedule">
        <option value="Morning">Morning (6AM–2PM)</option>
        <option value="Afternoon">Afternoon (2PM–10PM)</option>
        <option value="Evening">Evening (10PM–6AM)</option>
      </select>
    </div>

    <div class="form-group">
      <label>Working Hours</label>
      <input type="number" id="f-hours" name="emp_working_hours" placeholder="e.g. 8" min="1" max="24"/>
    </div>

    <div class="form-group">
      <label>Date Hired</label>
      <input type="date" id="f-date" name="emp_date_hired"/>
    </div>

    <div class="form-group">
      <label>Status</label>
      <select id="f-status" name="emp_status">
        <option value="Active">Active</option>
        <option value="On Leave">On Leave</option>
        <option value="Inactive">Inactive</option>
      </select>
    </div>

    <div class="form-group">
      <label>Username</label>
      <input type="text" id="f-username" name="user_name" placeholder="e.g. jreyes"/>
    </div>

    <div class="form-group">
      <label>Password</label>
      <input type="password" id="f-password" name="user_password" placeholder="Set a password"/>
    </div>

  </div>
  <div class="form-actions">
    <button class="btn-save" onclick="saveEmployee()">Save</button>
    <button class="btn-cancel" onclick="closeForm()">Cancel</button>
  </div>
</div>

  <!-- TABLE PANEL -->
  <div class="table-panel">
    <div class="toolbar">
      <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="16.65" y1="16.65" x2="21" y2="21"/></svg>
        <input type="text" id="search-input" placeholder="Search employee..." oninput="filterTable()"/>
      </div>
      <select id="role-filter" onchange="filterTable()">
        <option value="">All Roles</option>
        <option>Barista</option>
        <option>Cashier</option>
        <option>Kitchen Staff</option>
        <option>Supervisor</option>
      </select>
      <select id="status-filter" onchange="filterTable()">
        <option value="">All Status</option>
        <option>Active</option>
        <option>On Leave</option>
        <option>Inactive</option>
      </select>
      <span class="record-count" id="record-count"></span>
    </div>

    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Role</th>
            <th>Shift</th>
            <th>Date Hired</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="emp-table-body"></tbody>
      </table>
    </div>
  </div>
</main>

<!-- DELETE CONFIRM MODAL -->
<div class="modal-overlay" id="del-modal">
  <div class="modal">
    <h2>Delete Employee?</h2>
    <p>This action cannot be undone. The employee record will be permanently removed.</p>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn-confirm-delete" onclick="confirmDelete()">Yes, Delete</button>
    </div>
  </div>
</div>

<script>
  const BASE = '/hrm_module/src/api/hr/employees';

  let employees   = [];
  let editIndex   = null;
  let deleteIndex = null;
  let positionMap = {}; // ← declared at top
  let deptMap     = {}; // ← declared at top

  // ── LOAD DROPDOWNS ──
  async function loadDropdowns() {
    try {
      const [deptRes, posRes] = await Promise.all([
        fetch(`${BASE}/get_departments.php`),
        fetch(`${BASE}/get_positions.php`)
      ]);

      const deptJson = await deptRes.json();
      const posJson  = await posRes.json();

      // Populate departments
      if (deptJson.success) {
        const deptSel = document.getElementById('f-dept');
        deptSel.innerHTML = '<option value="">Select department...</option>';
        deptJson.data.forEach(d => {
          deptMap[String(d.dept_id)] = d.dept_name; // ← store in map
          const opt = document.createElement('option');
          opt.value       = d.dept_id;
          opt.textContent = d.dept_name;
          deptSel.appendChild(opt);
        });
      }

      // Populate positions
      if (posJson.success) {
        const posSel = document.getElementById('f-pos');
        posSel.innerHTML = '<option value="">Select position...</option>';
        posJson.data.forEach(p => {
          positionMap[String(p.pos_id)] = p.pos_name; // ← store in map
          const opt = document.createElement('option');
          opt.value       = p.pos_id;
          opt.textContent = p.pos_name;
          posSel.appendChild(opt);
        });
      }

      // Load employees AFTER maps are populated
      loadEmployees();

    } catch (err) {
      console.error('Dropdown load error:', err);
    }
  }

  // ── LOAD EMPLOYEES ──
  async function loadEmployees() {
    try {
      const res  = await fetch(`${BASE}/get_employees.php`);
      const json = await res.json();
      if (json.success) {
        employees = json.data;
        filterTable();
      } else {
        console.error('Failed to load:', json.error);
      }
    } catch (err) {
      console.error('Load error:', err);
    }
  }

  // ── HELPERS ──
  function statusClass(s) {
    if (s === 'Active')   return 'active';
    if (s === 'On Leave') return 'on-leave';
    return 'inactive';
  }

  function getPosLabel(pos_id) {
    return positionMap[String(pos_id)] || '—'; // ← String() to handle number/string mismatch
  }

  function getDeptLabel(dept_id) {
    return deptMap[String(dept_id)] || '—';
  }

  // ── RENDER TABLE ──
  function renderTable(list) {
    const tbody = document.getElementById('emp-table-body');
    document.getElementById('record-count').textContent =
      list.length + ' record' + (list.length !== 1 ? 's' : '');
    tbody.innerHTML = list.map(e => {
      const realIdx = employees.indexOf(e);
      return `
        <tr>
          <td class="id-cell">#${String(e.emp_id).padStart(3,'0')}</td>
          <td class="name-cell">${e.emp_fname} ${e.emp_lname}</td>
          <td><span class="role-badge">${getPosLabel(e.pos_id)}</span></td>
          <td class="shift-cell">${e.emp_schedule ?? '—'}</td>
          <td class="date-cell">${e.emp_date_hired ?? '—'}</td>
          <td><span class="status-badge ${statusClass(e.emp_status)}">
            <span class="status-dot"></span>${e.emp_status}
          </span></td>
          <td>
            <div class="actions">
              <button class="btn-edit" onclick="openEditForm(${realIdx})">Edit</button>
              <button class="btn-delete" onclick="openDeleteModal(${realIdx})">Delete</button>
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  // ── FILTER ──
  function filterTable() {
    const q  = document.getElementById('search-input').value.toLowerCase();
    const rf = document.getElementById('role-filter').value;
    const sf = document.getElementById('status-filter').value;
    const filtered = employees.filter(e => {
      const fullName = (e.emp_fname + ' ' + e.emp_lname).toLowerCase();
      return (!q  || fullName.includes(q) || String(e.emp_id).includes(q)) &&
             (!rf || String(e.pos_id) === rf) &&
             (!sf || e.emp_status === sf);
    });
    renderTable(filtered);
  }

  // ── FORM TOGGLE ──
  function toggleForm() {
    const panel = document.getElementById('emp-form-panel');
    if (panel.classList.contains('open')) {
      closeForm();
    } else {
      editIndex = null;
      document.getElementById('form-title').textContent = 'New Employee';
      clearForm();
      panel.classList.add('open');
    }
  }

  function closeForm() {
    document.getElementById('emp-form-panel').classList.remove('open');
    editIndex = null;
  }

  function clearForm() {
    ['f-fname','f-lname','f-mname','f-email','f-contact',
     'f-address','f-date','f-hours','f-age','f-username','f-password'
    ].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f-dept').value     = '';
    document.getElementById('f-pos').value      = '';
    document.getElementById('f-schedule').value = 'Morning';
    document.getElementById('f-status').value   = 'Active';
  }

  // ── OPEN EDIT FORM ──
  function openEditForm(idx) {
    editIndex = idx;
    const e = employees[idx];
    document.getElementById('form-title').textContent = 'Edit Employee';

    document.getElementById('f-fname').value    = e.emp_fname         ?? '';
    document.getElementById('f-lname').value    = e.emp_lname         ?? '';
    document.getElementById('f-mname').value    = e.emp_mname         ?? '';
    document.getElementById('f-email').value    = e.emp_email         ?? '';
    document.getElementById('f-contact').value  = e.emp_contact       ?? '';
    document.getElementById('f-address').value  = e.emp_address       ?? '';
    document.getElementById('f-dept').value     = e.dept_id           ?? '';
    document.getElementById('f-pos').value      = e.pos_id            ?? '';
    document.getElementById('f-schedule').value = e.emp_schedule      ?? 'Morning';
    document.getElementById('f-hours').value    = e.emp_working_hours ?? '';
    document.getElementById('f-age').value      = e.emp_age           ?? '';
    document.getElementById('f-status').value   = e.emp_status        ?? 'Active';
    document.getElementById('f-username').value = e.user_name         ?? ''; // ← fixed from e.User_name
    document.getElementById('f-password').value = ''; // never pre-fill

    if (e.emp_date_hired) {
      const d = new Date(e.emp_date_hired);
      if (!isNaN(d)) document.getElementById('f-date').value = d.toISOString().split('T')[0];
    }

    document.getElementById('emp-form-panel').classList.add('open');
  }

  // ── SAVE (Add or Edit) ──
  async function saveEmployee() {
    const fname    = document.getElementById('f-fname').value.trim();
    const lname    = document.getElementById('f-lname').value.trim();
    const dept_id  = document.getElementById('f-dept').value;
    const pos_id   = document.getElementById('f-pos').value;
    const username = document.getElementById('f-username').value.trim();
    const password = document.getElementById('f-password').value;

    if (!fname || !lname)                        { alert('Please enter first and last name.'); return; }
    if (!dept_id)                                { alert('Please select a department.'); return; }
    if (!pos_id)                                 { alert('Please select a position.'); return; }
    if (!username)                               { alert('Please enter a username.'); return; }
    if (editIndex === null && !password)         { alert('Please enter a password.'); return; }

    const payload = {
      emp_fname:         fname,
      emp_lname:         lname,
      emp_mname:         document.getElementById('f-mname').value.trim(),
      emp_email:         document.getElementById('f-email').value.trim(),
      emp_contact:       document.getElementById('f-contact').value.trim(),
      emp_address:       document.getElementById('f-address').value.trim(),
      emp_age:           document.getElementById('f-age').value           || null,
      dept_id:           dept_id,
      pos_id:            pos_id,
      emp_schedule:      document.getElementById('f-schedule').value,
      emp_working_hours: document.getElementById('f-hours').value         || null,
      emp_date_hired:    document.getElementById('f-date').value          || null,
      emp_status:        document.getElementById('f-status').value,
      user_name:         username,
      user_password:     password,
    };

    const isEdit = editIndex !== null;
    if (isEdit) payload.emp_id = employees[editIndex].emp_id;

    try {
      const res  = await fetch(`${BASE}/${isEdit ? 'update_employee' : 'add_employee'}.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      });
      const json = await res.json();

      if (json.success) {
        closeForm();
        loadEmployees();
      } else {
        alert('Error: ' + json.error);
      }
    } catch (err) {
      console.error('Save error:', err);
      alert('Something went wrong. Check the console.');
    }
  }

  // ── DELETE ──
  function openDeleteModal(idx) {
    deleteIndex = idx;
    document.getElementById('del-modal').classList.add('open');
  }

  function closeDeleteModal() {
    document.getElementById('del-modal').classList.remove('open');
  }

  async function confirmDelete() {
    if (deleteIndex === null) return;
    try {
      const res  = await fetch(`${BASE}/delete_employee.php`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ emp_id: employees[deleteIndex].emp_id }),
      });
      const json = await res.json();
      if (json.success) {
        closeDeleteModal();
        loadEmployees();
      } else {
        alert('Error: ' + json.error);
      }
    } catch (err) {
      console.error('Delete error:', err);
      alert('Something went wrong. Check the console.');
    }
  }

  // close delete modal on overlay click
  document.getElementById('del-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });

  // ── INIT — only call loadDropdowns, it calls loadEmployees internally ──
  loadDropdowns();
</script>
</body>
</html>