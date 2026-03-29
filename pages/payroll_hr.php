<?php
$required_page = 'hr_payroll';
require_once '../src/api/session_guard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Code Latte – Payroll Management</title>
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
      --paid:     #3d7a4e;
      --pending:  #c4842b;
      --deduct:   #b84040;
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
    .logo-icon {
      width: 34px; height: 34px; background: var(--accent);
      border-radius: 8px; display: flex; align-items: center;
      justify-content: center; color: #fff; font-size: 15px;
    }
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
      height: 100vh; overflow-y: auto; padding: 32px 36px;
      gap: 20px;
      scrollbar-width: thin;
      scrollbar-color: var(--border) transparent;
    }
    main::-webkit-scrollbar { width: 6px; }
    main::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

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
    .header-actions { display: flex; gap: 10px; }
    .btn-export {
      padding: 9px 18px; border-radius: 8px;
      border: 1px solid var(--border); background: var(--card);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; font-weight: 500;
      color: var(--text); cursor: pointer;
      transition: background .15s;
    }
    .btn-export:hover { background: var(--border); }
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
    }
    .stat-card .label { font-size: 11px; font-weight: 600; letter-spacing:.06em; text-transform:uppercase; color: var(--muted); margin-bottom: 8px; }
    .stat-card .value { font-family: 'DM Serif Display', serif; font-size: 28px; line-height:1; margin-bottom: 5px; }
    .stat-card .desc  { font-size: 12px; color: var(--muted); }
    .stat-card.total .value  { color: var(--text); }
    .stat-card.paid .value   { color: var(--paid); }
    .stat-card.pending .value { color: var(--pending); }

    /* ── PAY PERIOD PANEL ── */
    .period-panel {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 18px 24px;
      box-shadow: var(--shadow); flex-shrink: 0;
    }
    .period-label {
      display: flex; align-items: center; gap: 8px;
      font-size: 13px; font-weight: 600; margin-bottom: 14px;
    }
    .period-label span.hint { font-size: 12px; color: var(--muted); font-weight: 400; }
    .period-label svg { width:16px; height:16px; color: var(--gold); }
    .period-grid {
      display: grid; grid-template-columns: 1fr 1fr 1fr 1fr;
      gap: 16px;
    }
    .period-group { display: flex; flex-direction: column; gap: 5px; }
    .period-group label {
      font-size: 11px; font-weight: 600; letter-spacing:.06em;
      text-transform: uppercase; color: var(--muted);
    }
    .period-group input,
    .period-group select {
      padding: 9px 12px;
      border: 1px solid var(--border); border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      outline: none; width: 100%;
      transition: border-color .15s;
    }
    .period-group input:focus,
    .period-group select:focus { border-color: var(--accent); }
    .period-group select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center;
      padding-right: 28px;
    }

    /* ── RECORDS PANEL ── */
    .records-panel {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 20px 24px;
      box-shadow: var(--shadow); flex-shrink: 0;
    }
    .records-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 16px;
    }
    .records-title {
      font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px;
    }
    .records-count {
      font-size: 12px; color: var(--muted); font-weight: 400;
    }
    .btn-mark-all {
      padding: 8px 16px; border-radius: 8px; border: none;
      background: var(--paid);
      font-family: 'DM Sans', sans-serif;
      font-size: 12px; font-weight: 600;
      color: #fff; cursor: pointer;
      transition: background .15s;
    }
    .btn-mark-all:hover { background: #2d5e3a; }

    /* ── TABLE ── */
    .table-scroll {
      overflow-x: auto;
      scrollbar-width: thin;
      scrollbar-color: var(--border) transparent;
    }
    .table-scroll::-webkit-scrollbar { height: 5px; }
    .table-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

    table { width: 100%; border-collapse: collapse; min-width: 860px; }
    thead th {
      text-align: left; font-size: 11px; font-weight: 600;
      letter-spacing:.06em; color: var(--muted); text-transform: uppercase;
      padding: 10px 12px; border-bottom: 1px solid var(--border);
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: rgba(92,74,30,.04); }
    tbody td { padding: 13px 12px; font-size: 13px; vertical-align: middle; }

    .id-cell     { color: var(--muted); font-size: 12px; font-weight: 500; }
    .name-cell   { font-weight: 500; }
    .period-cell { color: var(--muted); font-size: 12px; }
    .paydate-cell{ font-size: 13px; }
    .money       { font-size: 13px; }
    .deduct      { color: var(--deduct); font-weight: 500; }
    .net         { font-weight: 600; }

    .status-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .status-badge.paid    { background: #e8f5ec; color: var(--paid); }
    .status-badge.pending { background: #fef3e2; color: var(--pending); }
    .status-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }

    .btn-mark-paid {
      padding: 5px 12px; border-radius: 6px; border: none;
      background: var(--paid);
      font-family: 'DM Sans', sans-serif;
      font-size: 11px; font-weight: 600;
      color: #fff; cursor: pointer;
      transition: background .12s;
      white-space: nowrap;
    }
    .btn-mark-paid:hover { background: #2d5e3a; }

    .actions { display: flex; gap: 6px; align-items: center; }
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
      color: var(--deduct); cursor: pointer; transition: background .12s;
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
      background: #fff; border-radius: var(--radius);
      padding: 28px 32px; width: 500px;
      box-shadow: 0 8px 40px rgba(44,36,22,.18);
      animation: fadeUp .22s ease;
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
    .modal-group select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center;
      padding-right: 28px;
    }

    /* Net salary preview */
    .net-preview {
      grid-column: 1 / -1;
      background: var(--bg); border: 1px solid var(--border);
      border-radius: 8px; padding: 12px 16px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .net-preview .net-label { font-size: 13px; color: var(--muted); }
    .net-preview .net-value {
      font-family: 'DM Serif Display', serif;
      font-size: 20px; color: var(--text);
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
    .confirm-modal p { font-size: 13px; color: var(--muted); margin-bottom: 22px; }
    .btn-confirm-delete {
      padding: 9px 22px; border-radius: 8px; border: none;
      background: var(--deduct);
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
    <a href="attendance_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
      Attendance
    </a>
    <a href="payroll_hr.php" class="active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      Payroll
    </a>
    <a href="recruitment_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="16.65" y1="16.65" x2="21" y2="21"/></svg>
      Recruitment
    </a>
  </nav>
<div class="user-block">
  <div class="avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
  <div class="user-info">
    <div class="uname"><?= htmlspecialchars($_SESSION['user_name']); ?></div>
    <div class="urole"><?= htmlspecialchars($_SESSION['user_access']); ?></div>
  </div>
</div>
</aside>

<!-- MAIN -->
<main>
  <h1>Payroll Management</h1>

  <!-- SECTION HEADER -->
  <div class="section-header">
    <span class="section-title">Payroll</span>
    <div class="header-actions">
      <button class="btn-export" onclick="exportCSV()">Export</button>
      <button class="btn-add" onclick="openAddModal()">+ Add Payroll</button>
    </div>
  </div>

  <!-- STAT CARDS -->
  <div class="stats">
    <div class="stat-card total">
      <div class="label">Total Net Salary</div>
      <div class="value" id="stat-total">₱0.00</div>
      <div class="desc" id="stat-period">Mar 1, 2026 – Mar 15, 2026</div>
    </div>
    <div class="stat-card paid">
      <div class="label">Paid</div>
      <div class="value" id="stat-paid">₱0.00</div>
      <div class="desc" id="stat-paid-count">0 employees</div>
    </div>
    <div class="stat-card pending">
      <div class="label">Pending</div>
      <div class="value" id="stat-pending">₱0.00</div>
      <div class="desc" id="stat-pending-count">0 employees</div>
    </div>
  </div>

  <!-- PAY PERIOD PANEL -->
  <div class="period-panel">
    <div class="period-label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Set Active Pay Period
      <span class="hint">New entries will use these dates automatically</span>
    </div>
    <div class="period-grid">
      <div class="period-group">
        <label>Period Start</label>
        <input type="date" id="period-start" value="2026-03-01" onchange="updatePeriodStats()"/>
      </div>
      <div class="period-group">
        <label>Period End</label>
        <input type="date" id="period-end" value="2026-03-15" onchange="updatePeriodStats()"/>
      </div>
      <div class="period-group">
        <label>Pay Date</label>
        <input type="date" id="pay-date" value="2026-03-16"/>
      </div>
      <div class="period-group">
        <label>Filter</label>
        <select id="status-filter" onchange="renderTable()">
          <option value="All">All</option>
          <option value="Paid">Paid</option>
          <option value="Pending">Pending</option>
        </select>
      </div>
    </div>
  </div>

  <!-- RECORDS PANEL -->
  <div class="records-panel">
    <div class="records-header">
      <div class="records-title">
        Payroll Records
        <span class="records-count" id="records-count">0 records</span>
      </div>
      <button class="btn-mark-all" onclick="markAllPaid()">✓ Mark All Pending as Paid</button>
    </div>
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Employee</th>
            <th>Period</th>
            <th>Pay Date</th>
            <th>Base</th>
            <th>Bonus</th>
            <th>OT</th>
            <th>Deductions</th>
            <th>Net Salary</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="pay-table-body"></tbody>
      </table>
    </div>
  </div>
</main>

<!-- ADD / EDIT MODAL -->
<div class="modal-overlay" id="pay-modal">
  <div class="modal">
    <div class="modal-top">
      <h2 id="modal-title">Add Payroll Record</h2>
      <button class="btn-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-grid">
      <div class="modal-group">
        <label>Employee</label>
        <select id="m-employee">
          <option value="">Select employee…</option>
          <option>Juan Reyes</option>
          <option>Maria Cruz</option>
          <option>Ramon Lopez</option>
          <option>Ana Santos</option>
          <option>Karl Dela Cruz</option>
        </select>
      </div>
      <div class="modal-group">
        <label>Pay Date</label>
        <input type="date" id="m-paydate"/>
      </div>
      <div class="modal-group">
        <label>Period Start</label>
        <input type="date" id="m-period-start"/>
      </div>
      <div class="modal-group">
        <label>Period End</label>
        <input type="date" id="m-period-end"/>
      </div>
      <div class="modal-group">
        <label>Base Salary (₱)</label>
        <input type="number" id="m-base" placeholder="0" min="0" oninput="calcNet()"/>
      </div>
      <div class="modal-group">
        <label>Bonus (₱)</label>
        <input type="number" id="m-bonus" placeholder="0" min="0" oninput="calcNet()"/>
      </div>
      <div class="modal-group">
        <label>Overtime (₱)</label>
        <input type="number" id="m-ot" placeholder="0" min="0" oninput="calcNet()"/>
      </div>
      <div class="modal-group">
        <label>Deductions (₱)</label>
        <input type="number" id="m-deductions" placeholder="0" min="0" oninput="calcNet()"/>
      </div>
      <!-- Net Preview -->
      <div class="net-preview">
        <span class="net-label">Net Salary</span>
        <span class="net-value" id="m-net-preview">₱0.00</span>
      </div>
      <div class="modal-group" style="grid-column:1/-1;">
        <label>Status</label>
        <select id="m-status">
          <option>Pending</option>
          <option>Paid</option>
        </select>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel-modal" onclick="closeModal()">Cancel</button>
      <button class="btn-save-modal" onclick="saveRecord()">Save</button>
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
  let records = [
    { id:'#001', name:'Juan Reyes',  period:'Mar 1, 2026 – Mar 15, 2026', payDate:'Mar 16, 2026', base:9000,  bonus:500,  ot:1200, deductions:300, net:10400, status:'Paid'    },
    { id:'#002', name:'Maria Cruz',  period:'Mar 1, 2026 – Mar 15, 2026', payDate:'Mar 16, 2026', base:8500,  bonus:0,    ot:0,    deductions:400, net:8100,  status:'Paid'    },
    { id:'#003', name:'Ramon Lopez', period:'Mar 1, 2026 – Mar 15, 2026', payDate:'Mar 16, 2026', base:9000,  bonus:0,    ot:800,  deductions:900, net:8900,  status:'Pending' },
    { id:'#004', name:'Ana Santos',  period:'Mar 1, 2026 – Mar 15, 2026', payDate:'Mar 16, 2026', base:12000, bonus:1000, ot:1500, deductions:200, net:14300, status:'Pending' },
  ];

  let editIdx   = null;
  let deleteIdx = null;

  function fmt(n) { return '₱' + Number(n).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}); }

  function updateStats() {
    const total   = records.reduce((s,r) => s + r.net, 0);
    const paid    = records.filter(r => r.status === 'Paid');
    const pending = records.filter(r => r.status === 'Pending');
    document.getElementById('stat-total').textContent   = fmt(total);
    document.getElementById('stat-paid').textContent    = fmt(paid.reduce((s,r) => s + r.net, 0));
    document.getElementById('stat-pending').textContent = fmt(pending.reduce((s,r) => s + r.net, 0));
    document.getElementById('stat-paid-count').textContent    = paid.length + ' employee' + (paid.length !== 1 ? 's' : '');
    document.getElementById('stat-pending-count').textContent = pending.length + ' employee' + (pending.length !== 1 ? 's' : '');
  }

  function renderTable() {
    const filter = document.getElementById('status-filter').value;
    const list   = filter === 'All' ? records : records.filter(r => r.status === filter);
    document.getElementById('records-count').textContent = list.length + ' record' + (list.length !== 1 ? 's' : '');
    const tbody = document.getElementById('pay-table-body');
    tbody.innerHTML = list.map(r => {
      const realIdx = records.indexOf(r);
      const isPaid  = r.status === 'Paid';
      return `
        <tr>
          <td class="id-cell">${r.id}</td>
          <td class="name-cell">${r.name}</td>
          <td class="period-cell">${r.period}</td>
          <td class="paydate-cell">${r.payDate}</td>
          <td class="money">${fmt(r.base)}</td>
          <td class="money">${fmt(r.bonus)}</td>
          <td class="money">${fmt(r.ot)}</td>
          <td class="money deduct">${fmt(r.deductions)}</td>
          <td class="money net">${fmt(r.net)}</td>
          <td>
            ${isPaid
              ? `<span class="status-badge paid"><span class="status-dot"></span>Paid</span>`
              : `<button class="btn-mark-paid" onclick="markPaid(${realIdx})">✓ Mark Paid</button>`
            }
          </td>
          <td>
            <div class="actions">
              <button class="btn-edit" onclick="openEditModal(${realIdx})">Edit</button>
              <button class="btn-delete" onclick="openDelModal(${realIdx})">Delete</button>
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  function markPaid(idx) {
    records[idx].status = 'Paid';
    updateStats(); renderTable();
  }

  function markAllPaid() {
    records.forEach(r => { if (r.status === 'Pending') r.status = 'Paid'; });
    updateStats(); renderTable();
  }

  function calcNet() {
    const base = parseFloat(document.getElementById('m-base').value) || 0;
    const bonus= parseFloat(document.getElementById('m-bonus').value) || 0;
    const ot   = parseFloat(document.getElementById('m-ot').value) || 0;
    const ded  = parseFloat(document.getElementById('m-deductions').value) || 0;
    const net  = base + bonus + ot - ded;
    document.getElementById('m-net-preview').textContent = fmt(Math.max(0, net));
  }

  function openAddModal() {
    editIdx = null;
    document.getElementById('modal-title').textContent = 'Add Payroll Record';
    document.getElementById('m-employee').value = '';
    document.getElementById('m-paydate').value  = document.getElementById('pay-date').value;
    document.getElementById('m-period-start').value = document.getElementById('period-start').value;
    document.getElementById('m-period-end').value   = document.getElementById('period-end').value;
    ['m-base','m-bonus','m-ot','m-deductions'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('m-status').value = 'Pending';
    document.getElementById('m-net-preview').textContent = '₱0.00';
    document.getElementById('pay-modal').classList.add('open');
  }

  function openEditModal(idx) {
    editIdx = idx;
    const r = records[idx];
    document.getElementById('modal-title').textContent = 'Edit Payroll Record';
    document.getElementById('m-employee').value = r.name;
    // parse date for input
    function toInput(s) {
      const d = new Date(s); return isNaN(d) ? '' : d.toISOString().split('T')[0];
    }
    document.getElementById('m-paydate').value = toInput(r.payDate);
    const [ps, pe] = r.period.split(' – ');
    document.getElementById('m-period-start').value = toInput(ps);
    document.getElementById('m-period-end').value   = toInput(pe);
    document.getElementById('m-base').value       = r.base;
    document.getElementById('m-bonus').value      = r.bonus;
    document.getElementById('m-ot').value         = r.ot;
    document.getElementById('m-deductions').value = r.deductions;
    document.getElementById('m-status').value     = r.status;
    calcNet();
    document.getElementById('pay-modal').classList.add('open');
  }

  function closeModal() {
    document.getElementById('pay-modal').classList.remove('open');
    editIdx = null;
  }

  function saveRecord() {
    const name = document.getElementById('m-employee').value;
    if (!name) { alert('Please select an employee.'); return; }
    const rawPay = document.getElementById('m-paydate').value;
    const rawPS  = document.getElementById('m-period-start').value;
    const rawPE  = document.getElementById('m-period-end').value;
    const base   = parseFloat(document.getElementById('m-base').value) || 0;
    const bonus  = parseFloat(document.getElementById('m-bonus').value) || 0;
    const ot     = parseFloat(document.getElementById('m-ot').value) || 0;
    const ded    = parseFloat(document.getElementById('m-deductions').value) || 0;
    const net    = Math.max(0, base + bonus + ot - ded);
    const status = document.getElementById('m-status').value;

    function fmtDate(s) {
      const d = new Date(s);
      return isNaN(d) ? '' : d.toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'});
    }

    const rec = {
      id:      editIdx !== null ? records[editIdx].id : '#' + String(records.length + 1).padStart(3,'0'),
      name,
      period:  fmtDate(rawPS) + ' – ' + fmtDate(rawPE),
      payDate: fmtDate(rawPay),
      base, bonus, ot, deductions: ded, net, status,
    };

    if (editIdx === null) records.push(rec);
    else records[editIdx] = rec;

    closeModal();
    updateStats();
    renderTable();
  }

  function openDelModal(idx) {
    deleteIdx = idx;
    document.getElementById('del-modal').classList.add('open');
  }
  function closeDelModal() {
    document.getElementById('del-modal').classList.remove('open');
  }
  function confirmDelete() {
    if (deleteIdx !== null) records.splice(deleteIdx, 1);
    closeDelModal();
    updateStats();
    renderTable();
  }

  function updatePeriodStats() { updateStats(); }

  function exportCSV() {
    const headers = ['ID','Employee','Period','Pay Date','Base','Bonus','OT','Deductions','Net Salary','Status'];
    const rows = records.map(r => [r.id, r.name, r.period, r.payDate, r.base, r.bonus, r.ot, r.deductions, r.net, r.status]);
    const csv = [headers, ...rows].map(r => r.map(v => `"${v}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv,' + encodeURIComponent(csv);
    a.download = 'payroll.csv';
    a.click();
  }

  document.getElementById('pay-modal').addEventListener('click', function(e) { if(e.target===this) closeModal(); });
  document.getElementById('del-modal').addEventListener('click', function(e) { if(e.target===this) closeDelModal(); });

  updateStats();
  renderTable();
</script>
</body>
</html>