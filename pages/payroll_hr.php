<?php require_once '../src/api/auth/check_session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Code Latte – Payroll Management</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <!-- jsPDF for PDF export -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg:#f5f0e8; --sidebar:#f0ebe0; --card:#faf7f2; --border:#e3ddd2;
      --text:#2c2416; --muted:#8a7f6e; --accent:#5c4a1e; --gold:#c49a2b;
      --sent:#4a7c9b; --pending:#c4842b; --approved:#3d7a4e; --deduct:#b84040;
      --radius:14px; --shadow:0 2px 12px rgba(80,60,20,.08);
    }
    body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); display:flex; height:100vh; overflow:hidden; }
    aside { width:200px; height:100vh; background:var(--sidebar); border-right:1px solid var(--border); display:flex; flex-direction:column; padding:24px 0; flex-shrink:0; }
    .logo { display:flex; align-items:center; gap:10px; padding:0 20px 28px; border-bottom:1px solid var(--border); }
    .logo-icon { width:34px; height:34px; background:var(--accent); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .logo-icon img { width:100%; height:100%; object-fit:cover; border-radius:8px; }
    .logo-text .name { font-family:'DM Serif Display',serif; font-size:15px; line-height:1.1; }
    .logo-text .sub  { font-size:11px; color:var(--muted); }
    nav { flex:1; padding:20px 0; }
    nav a { display:flex; align-items:center; gap:10px; padding:10px 20px; font-size:14px; font-weight:500; color:var(--muted); text-decoration:none; border-radius:0 8px 8px 0; margin-right:12px; transition:background .15s,color .15s; }
    nav a svg { width:16px; height:16px; flex-shrink:0; }
    nav a:hover { background:var(--border); color:var(--text); }
    nav a.active { background:var(--accent); color:#fff; }
    .user-block { padding:16px 20px; border-top:1px solid var(--border); display:flex; align-items:center; gap:10px; }
    .avatar { width:34px; height:34px; border-radius:50%; background:var(--gold); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:13px; color:#fff; }
    .user-info .uname { font-size:13px; font-weight:600; }
    .user-info .urole { font-size:11px; color:var(--muted); }
    main { flex:1; display:flex; flex-direction:column; height:100vh; overflow-y:auto; padding:32px 36px; gap:20px; scrollbar-width:thin; scrollbar-color:var(--border) transparent; }
    main::-webkit-scrollbar { width:6px; }
    main::-webkit-scrollbar-thumb { background:var(--border); border-radius:99px; }
    h1 { font-family:'DM Serif Display',serif; font-size:26px; font-weight:400; flex-shrink:0; }
    .section-header { display:flex; align-items:center; justify-content:space-between; flex-shrink:0; }
    .section-title { font-size:15px; font-weight:600; }
    .header-actions { display:flex; gap:10px; }
    .btn-export { padding:9px 18px; border-radius:8px; border:1px solid var(--border); background:var(--card); font-family:'DM Sans',sans-serif; font-size:13px; font-weight:500; color:var(--text); cursor:pointer; transition:background .15s; }
    .btn-export:hover { background:var(--border); }
    .btn-pdf { padding:9px 18px; border-radius:8px; border:none; background:var(--deduct); font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; color:#fff; cursor:pointer; transition:background .15s; }
    .btn-pdf:hover { background:#9a3535; }
    .btn-add { padding:9px 18px; border-radius:8px; border:none; background:var(--accent); font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; color:#fff; cursor:pointer; transition:background .15s,transform .1s; }
    .btn-add:hover { background:#3e3010; transform:translateY(-1px); }
    .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; flex-shrink:0; }
    .stat-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:20px 24px; box-shadow:var(--shadow); }
    .stat-card .label { font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); margin-bottom:8px; }
    .stat-card .value { font-family:'DM Serif Display',serif; font-size:28px; line-height:1; margin-bottom:5px; }
    .stat-card .desc  { font-size:12px; color:var(--muted); }
    .stat-card.total .value   { color:var(--text); }
    .stat-card.sent .value    { color:var(--sent); }
    .stat-card.pending .value { color:var(--pending); }
    .period-panel { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:18px 24px; box-shadow:var(--shadow); flex-shrink:0; }
    .period-label { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; margin-bottom:14px; }
    .period-label span.hint { font-size:12px; color:var(--muted); font-weight:400; }
    .period-label svg { width:16px; height:16px; color:var(--gold); }
    .period-grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:16px; }
    .period-group { display:flex; flex-direction:column; gap:5px; }
    .period-group label { font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); }
    .period-group input,.period-group select { padding:9px 12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text); outline:none; width:100%; transition:border-color .15s; }
    .period-group input:focus,.period-group select:focus { border-color:var(--accent); }
    .period-group select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:28px; }
    .records-panel { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:20px 24px; box-shadow:var(--shadow); flex-shrink:0; }
    .records-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
    .records-title { font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; }
    .records-count { font-size:12px; color:var(--muted); font-weight:400; }
    .btn-send-all { padding:8px 16px; border-radius:8px; border:none; background:var(--sent); font-family:'DM Sans',sans-serif; font-size:12px; font-weight:600; color:#fff; cursor:pointer; transition:background .15s; }
    .btn-send-all:hover { background:#3a5f7a; }
    .table-scroll { overflow-x:auto; scrollbar-width:thin; scrollbar-color:var(--border) transparent; }
    .table-scroll::-webkit-scrollbar { height:5px; }
    .table-scroll::-webkit-scrollbar-thumb { background:var(--border); border-radius:99px; }
    table { width:100%; border-collapse:collapse; min-width:700px; }
    thead th { text-align:left; font-size:11px; font-weight:600; letter-spacing:.06em; color:var(--muted); text-transform:uppercase; padding:10px 12px; border-bottom:1px solid var(--border); }
    tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
    tbody tr:last-child { border-bottom:none; }
    tbody tr:hover { background:rgba(92,74,30,.04); }
    tbody td { padding:13px 12px; font-size:13px; vertical-align:middle; }
    .name-cell { font-weight:500; }
    .money { font-size:13px; font-weight:600; }
    .count-badge { display:inline-flex; align-items:center; justify-content:center; background:var(--sidebar); color:var(--text); padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600; margin-left:6px; }
    .status-summary { display:flex; flex-wrap:wrap; gap:6px; }
    .status-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .status-badge.sent     { background:#e8f0f5; color:var(--sent); }
    .status-badge.pending  { background:#fef3e2; color:var(--pending); }
    .status-badge.approved { background:#e8f5ec; color:var(--approved); }
    .status-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
    .btn-view { padding:6px 14px; border-radius:6px; border:none; background:var(--accent); font-family:'DM Sans',sans-serif; font-size:12px; font-weight:600; color:#fff; cursor:pointer; transition:background .12s; }
    .btn-view:hover { background:#3e3010; }
    .btn-send-finance { padding:5px 12px; border-radius:6px; border:none; background:var(--sent); font-family:'DM Sans',sans-serif; font-size:11px; font-weight:600; color:#fff; cursor:pointer; transition:background .12s; white-space:nowrap; }
    .btn-send-finance:hover { background:#3a5f7a; }
    .btn-send-finance:disabled { background:var(--border); cursor:not-allowed; opacity:0.6; }
    .actions { display:flex; gap:6px; align-items:center; }
    .btn-edit { padding:5px 14px; border-radius:6px; border:1px solid var(--border); background:var(--card); font-family:'DM Sans',sans-serif; font-size:12px; font-weight:500; color:var(--text); cursor:pointer; transition:background .12s; }
    .btn-edit:hover { background:var(--border); }
    .btn-delete { padding:5px 14px; border-radius:6px; border:1px solid #f0d0d0; background:#fff8f8; font-family:'DM Sans',sans-serif; font-size:12px; font-weight:500; color:var(--deduct); cursor:pointer; transition:background .12s; }
    .btn-delete:hover { background:#fceaea; }
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(44,36,22,.45); z-index:100; align-items:center; justify-content:center; padding:20px; }
    .modal-overlay.open { display:flex; }
    @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
    .modal { background:#fff; border-radius:var(--radius); padding:28px 32px; width:600px; max-height:80vh; box-shadow:0 8px 40px rgba(44,36,22,.18); animation:fadeUp .22s ease; overflow-y:auto; }
    .modal.wide { width:800px; }
    .modal-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
    .modal-top h2 { font-family:'DM Serif Display',serif; font-size:20px; font-weight:400; }
    .btn-close { background:none; border:none; font-size:18px; color:var(--muted); cursor:pointer; padding:2px 6px; border-radius:4px; transition:background .12s; }
    .btn-close:hover { background:var(--border); }
    .modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 20px; }
    .modal-group { display:flex; flex-direction:column; gap:6px; }
    .modal-group label { font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); }
    .modal-group input,.modal-group select { padding:9px 12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); font-family:'DM Sans',sans-serif; font-size:13px; color:var(--text); outline:none; width:100%; transition:border-color .15s; }
    .modal-group input:focus,.modal-group select:focus { border-color:var(--accent); }
    .modal-group select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a7f6e' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:28px; }
    .net-preview { grid-column:1/-1; background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; }
    .net-preview .net-label { font-size:13px; color:var(--muted); }
    .net-preview .net-value { font-family:'DM Serif Display',serif; font-size:20px; color:var(--text); }
    .modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:22px; }
    .btn-cancel-modal { padding:9px 18px; border-radius:8px; border:1px solid var(--border); background:transparent; font-family:'DM Sans',sans-serif; font-size:13px; font-weight:500; color:var(--muted); cursor:pointer; }
    .btn-cancel-modal:hover { background:var(--border); }
    .btn-save-modal { padding:9px 22px; border-radius:8px; border:none; background:var(--accent); font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; color:#fff; cursor:pointer; transition:background .15s; }
    .btn-save-modal:hover { background:#3e3010; }
    .confirm-modal { width:360px; text-align:center; }
    .confirm-modal p { font-size:13px; color:var(--muted); margin-bottom:22px; }
    .btn-confirm-delete { padding:9px 22px; border-radius:8px; border:none; background:var(--deduct); font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; color:#fff; cursor:pointer; }
    .details-list { display:flex; flex-direction:column; gap:12px; }
    .detail-item { background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:16px; }
    .detail-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .detail-period { font-size:14px; font-weight:600; color:var(--text); }
    .detail-status { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; }
    .detail-status.pending  { background:#fef3e2; color:var(--pending); }
    .detail-status.sent     { background:#e8f0f5; color:var(--sent); }
    .detail-status.approved { background:#e8f5ec; color:var(--approved); }
    .detail-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
    .detail-cell { display:flex; flex-direction:column; gap:4px; }
    .detail-cell label { font-size:10px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:var(--muted); }
    .detail-cell value { font-size:13px; font-weight:500; color:var(--text); }
    .toast { position:fixed; bottom:20px; right:20px; background:var(--accent); color:#fff; padding:12px 20px; border-radius:8px; box-shadow:var(--shadow); font-size:13px; font-weight:500; z-index:200; opacity:0; transform:translateY(20px); transition:opacity .3s,transform .3s; }
    .toast.show    { opacity:1; transform:translateY(0); }
    .toast.error   { background:var(--deduct); }
    .toast.success { background:var(--approved); }
  </style>
</head>
<body>

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

<main>
  <h1>Payroll Management</h1>
  <div class="section-header">
    <span class="section-title">Employee Payroll Summary</span>
    <div class="header-actions">
      <button class="btn-export" onclick="exportCSV()">Export CSV</button>
      <button class="btn-pdf" onclick="exportPDF()">Export PDF</button>
      <button class="btn-add" onclick="openAddModal()">+ Add Payroll</button>
    </div>
  </div>
  <div class="stats">
    <div class="stat-card total">
      <div class="label">Total Net Salary (All)</div>
      <div class="value" id="stat-total">&#8369;0.00</div>
      <div class="desc"  id="stat-count">0 employees</div>
    </div>
    <div class="stat-card sent">
      <div class="label">Sent to Finance</div>
      <div class="value" id="stat-sent">&#8369;0.00</div>
      <div class="desc"  id="stat-sent-count">0 records</div>
    </div>
    <div class="stat-card pending">
      <div class="label">Pending Submission</div>
      <div class="value" id="stat-pending">&#8369;0.00</div>
      <div class="desc"  id="stat-pending-count">0 records</div>
    </div>
  </div>
  <div class="period-panel">
    <div class="period-label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Filter by Pay Period
      <span class="hint">Filter payroll records by date range</span>
    </div>
    <div class="period-grid">
      <div class="period-group">
        <label>Period Start</label>
        <input type="date" id="filter-start" onchange="loadPayrolls()"/>
      </div>
      <div class="period-group">
        <label>Period End</label>
        <input type="date" id="filter-end" onchange="loadPayrolls()"/>
      </div>
      <div class="period-group">
        <label>Status</label>
        <select id="filter-status" onchange="loadPayrolls()">
          <option value="All">All Status</option>
          <option value="Pending">Pending</option>
          <option value="Sent">Sent to Finance</option>
          <option value="Approved">Approved</option>
        </select>
      </div>
      <div class="period-group">
        <label>Employee</label>
        <select id="filter-employee" onchange="loadPayrolls()">
          <option value="All">All Employees</option>
        </select>
      </div>
    </div>
  </div>
  <div class="records-panel">
    <div class="records-header">
      <div class="records-title">
        Employee Payroll Summary
        <span class="records-count" id="records-count">0 employees</span>
      </div>
      <button class="btn-send-all" onclick="sendAllToFinance()">Send All Pending to Finance</button>
    </div>
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Employee</th><th>Department</th><th>Position</th>
            <th>Payroll Count</th><th>Total Net Salary</th><th>Status Summary</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="pay-table-body">
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- ADD / EDIT MODAL -->
<div class="modal-overlay" id="pay-modal">
  <div class="modal">
    <div class="modal-top">
      <h2 id="modal-title">Add Payroll Record</h2>
      <button class="btn-close" onclick="closeModal()">&#10005;</button>
    </div>
    <div class="modal-grid">
      <div class="modal-group" style="grid-column:1/-1;">
        <label>Employee *</label>
        <select id="m-employee" onchange="loadEmployeeInfo()">
          <option value="">Select employee...</option>
        </select>
      </div>
      <div class="modal-group">
        <label>Department</label>
        <input type="text" id="m-dept" readonly style="background:var(--sidebar);"/>
      </div>
      <div class="modal-group">
        <label>Position</label>
        <input type="text" id="m-position" readonly style="background:var(--sidebar);"/>
      </div>
      <div class="modal-group">
        <label>Period Start *</label>
        <input type="date" id="m-period-start"/>
      </div>
      <div class="modal-group">
        <label>Period End *</label>
        <input type="date" id="m-period-end"/>
      </div>
      <div class="modal-group">
        <label>Base Salary (&#8369;) *</label>
        <input type="number" id="m-base" placeholder="0" min="0" oninput="calcNet()"/>
      </div>
      <div class="modal-group">
        <label>Bonus (&#8369;)</label>
        <input type="number" id="m-bonus" placeholder="0" min="0" oninput="calcNet()"/>
      </div>
      <div class="modal-group">
        <label>Overtime (&#8369;)</label>
        <input type="number" id="m-ot" placeholder="0" min="0" oninput="calcNet()"/>
      </div>
      <div class="modal-group">
        <label>Deductions (&#8369;)</label>
        <input type="number" id="m-deductions" placeholder="0" min="0" oninput="calcNet()"/>
      </div>
      <div class="modal-group" style="grid-column:1/-1;">
        <label>Deductions Label</label>
        <input type="text" id="m-deductions-label" placeholder="e.g., Tax, SSS, PhilHealth"/>
      </div>
      <div class="net-preview">
        <span class="net-label">Net Salary</span>
        <span class="net-value" id="m-net-preview">&#8369;0.00</span>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel-modal" onclick="closeModal()">Cancel</button>
      <button class="btn-save-modal"   onclick="saveRecord()">Save Record</button>
    </div>
  </div>
</div>

<!-- VIEW DETAILS MODAL -->
<div class="modal-overlay" id="view-modal">
  <div class="modal wide">
    <div class="modal-top">
      <h2 id="view-modal-title">Payroll Details</h2>
      <button class="btn-close" onclick="closeViewModal()">&#10005;</button>
    </div>
    <div id="view-details-content"></div>
  </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal-overlay" id="del-modal">
  <div class="modal confirm-modal">
    <div class="modal-top" style="justify-content:center;margin-bottom:10px;">
      <h2>Delete Payroll Record?</h2>
    </div>
    <p>This action cannot be undone. The record will be permanently removed.</p>
    <div class="modal-actions" style="justify-content:center;">
      <button class="btn-cancel-modal"  onclick="closeDelModal()">Cancel</button>
      <button class="btn-confirm-delete" onclick="confirmDelete()">Yes, Delete</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
  const { jsPDF } = window.jspdf;

  let records=[], employees=[], editId=null, deleteId=null, viewEmpId=null;

  function fmt(n){ return '\u20B1'+Number(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }

  function showToast(msg,type='success'){
    const t=document.getElementById('toast');
    t.textContent=msg; t.className='toast show '+type;
    setTimeout(()=>t.classList.remove('show'),3000);
  }

  /* ── LOAD EMPLOYEES ─────────────────────────────────────────── */
  async function loadEmployees(){
    try{
      const res  = await fetch('../src/api/hr/payroll/get_employees_dropdown.php');
      const data = await res.json();
      if(data.status==='success'){
        employees = data.employees.map(e=>({
          emp_id:    e.emp_id,
          full_name: e.full_name,
          dept_name: e.department,
          pos_name:  e.position
        }));
        populateEmployeeSelects();
      } else { showToast(data.message||'Failed to load employees','error'); }
      
    }catch(err){ console.error(err); showToast('Error loading employees','error'); }
  }

  function populateEmployeeSelects(){
    [document.getElementById('m-employee'), document.getElementById('filter-employee')].forEach(sel=>{
      const cur=sel.value;
      sel.innerHTML = sel.id==='filter-employee'
        ? '<option value="All">All Employees</option>'
        : '<option value="">Select employee...</option>';
      employees.forEach(emp=>{
        const o=document.createElement('option');
        o.value=emp.emp_id; o.textContent=emp.full_name; sel.appendChild(o);
      });
      sel.value=cur;
    });
  }

  function loadEmployeeInfo(){
    const emp=employees.find(e=>e.emp_id==document.getElementById('m-employee').value);
    document.getElementById('m-dept').value    = emp ? emp.dept_name||'N/A' : '';
    document.getElementById('m-position').value= emp ? emp.pos_name ||'N/A' : '';
  }

  /* ── LOAD PAYROLLS ──────────────────────────────────────────── */
  async function loadPayrolls(){
    try{
      const params=new URLSearchParams();
      const start =document.getElementById('filter-start').value;
      const end   =document.getElementById('filter-end').value;
      const status=document.getElementById('filter-status').value;
      const emp   =document.getElementById('filter-employee').value;

      if(start)           params.append('period_start',start);
      if(end)             params.append('period_end',  end);
      if(emp!=='All')     params.append('emp_id',      emp);
      
      const statusMap={'Pending':'Draft','Sent':'Submitted','Approved':'Approved'};
      if(status!=='All')  params.append('payroll_status', statusMap[status]||status);

      const res  = await fetch('../src/api/hr/payroll/get_payroll.php?'+params);
      const data = await res.json();
      
      if(data.status==='success'){
        records=data.records.map(r=>({
          payroll_id:       r.payroll_id,
          emp_id:           r.emp_id,
          emp_fname:        r.emp_name.split(' ')[0],
          emp_lname:        r.emp_name.split(' ').slice(1).join(' '),
          dept_name:        r.dept_name,
          pos_name:         r.pos_name,
          payperiod_start:  r.payperiod_start,
          payperiod_end:    r.payperiod_end,
          base_salary:      r.base_salary,
          bonus:            r.bonus,
          overtime:         r.overtime,
          deduction_total:  r.deduction_total,
          deductions_label: r.deductions_label,
          net_salary:       r.net_salary,
          payroll_status:   r.payroll_status,
          approval_status:  mapPayrollStatus(r.payroll_status)
        }));
        applyFilters();
      } else { showToast(data.message||'Failed to load payrolls','error'); }
      
    }catch(err){ showToast('Error loading payrolls','error'); console.error(err); }
  }

  function mapPayrollStatus(payrollStatus) {
    const statusMap = {
      'Draft': 'Pending',
      'Submitted': 'Sent',
      'Approved': 'Approved',
      'Rejected': 'Pending'
    };
    return statusMap[payrollStatus] || payrollStatus;
  }

  /* ── FILTER + GROUP ─────────────────────────────────────────── */
  function applyFilters(){
    const sf=document.getElementById('filter-status').value;
    const ef=document.getElementById('filter-employee').value;
    const s =document.getElementById('filter-start').value;
    const e =document.getElementById('filter-end').value;
    const filtered=records.filter(r=>{
      if(sf!=='All'&&r.approval_status!==sf)     return false;
      if(ef!=='All'&&r.emp_id!=ef)               return false;
      if(s&&r.payperiod_start<s)                 return false;
      if(e&&r.payperiod_end>e)                   return false;
      return true;
    });
    const grouped={};
    filtered.forEach(r=>{
      if(!grouped[r.emp_id]) grouped[r.emp_id]={
        emp_id:r.emp_id, emp_fname:r.emp_fname, emp_lname:r.emp_lname,
        dept_name:r.dept_name, pos_name:r.pos_name, payrolls:[],
        total_net:0, pending_count:0, sent_count:0, approved_count:0
      };
      grouped[r.emp_id].payrolls.push(r);
      grouped[r.emp_id].total_net+=parseFloat(r.net_salary);
      if(r.approval_status==='Pending')  grouped[r.emp_id].pending_count++;
      else if(r.approval_status==='Sent')     grouped[r.emp_id].sent_count++;
      else if(r.approval_status==='Approved') grouped[r.emp_id].approved_count++;
    });
    const ga=Object.values(grouped);
    updateStats(ga,filtered);
    renderTable(ga);
  }

  function updateStats(grouped,all){
    const total  =all.reduce((s,r)=>s+parseFloat(r.net_salary),0);
    const sent   =all.filter(r=>r.approval_status==='Sent'||r.approval_status==='Approved');
    const pending=all.filter(r=>r.approval_status==='Pending');
    document.getElementById('stat-total').textContent        =fmt(total);
    document.getElementById('stat-sent').textContent         =fmt(sent.reduce((s,r)=>s+parseFloat(r.net_salary),0));
    document.getElementById('stat-pending').textContent      =fmt(pending.reduce((s,r)=>s+parseFloat(r.net_salary),0));
    document.getElementById('stat-count').textContent        =grouped.length+' employee'+(grouped.length!==1?'s':'');
    document.getElementById('stat-sent-count').textContent   =sent.length+' record'+(sent.length!==1?'s':'');
    document.getElementById('stat-pending-count').textContent=pending.length+' record'+(pending.length!==1?'s':'');
  }

  function renderTable(grouped){
    const tbody=document.getElementById('pay-table-body');
    if(!grouped.length){
      tbody.innerHTML='<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px;">No payroll records found</td></tr>';
      document.getElementById('records-count').textContent='0 employees'; return;
    }
    document.getElementById('records-count').textContent=grouped.length+' employee'+(grouped.length!==1?'s':'');
    tbody.innerHTML=grouped.map(g=>{
      const badges=[];
      if(g.pending_count >0) badges.push(`<span class="status-badge pending"><span class="status-dot"></span>${g.pending_count} Pending</span>`);
      if(g.sent_count    >0) badges.push(`<span class="status-badge sent"><span class="status-dot"></span>${g.sent_count} Sent</span>`);
      if(g.approved_count>0) badges.push(`<span class="status-badge approved"><span class="status-dot"></span>${g.approved_count} Approved</span>`);
      
      const showSendButton = g.pending_count > 0;
      
      return `
        <tr data-id="${g.emp_id}">
          <td class="name-cell">${g.emp_fname} ${g.emp_lname}</td>
          <td>${g.dept_name||'N/A'}</td>
          <td>${g.pos_name ||'N/A'}</td>
          <td><span class="count-badge">${g.payrolls.length} payrolls</span></td>
          <td class="money">${fmt(g.total_net)}</td>
          <td><div class="status-summary">${badges.join('')||'<span style="color:var(--muted)">N/A</span>'}</div></td>
          <td>
            <div class="actions">
              <button class="btn-view" onclick="viewDetails(${g.emp_id})">View Details</button>
              ${showSendButton?`<button class="btn-send-finance" onclick="sendEmployeeToFinance(${g.emp_id})">Send to Finance</button>`:''}
            </div>
          </td>
        </tr>`;
    }).join('');
  }

  /* ── VIEW DETAILS ───────────────────────────────────────────── */
  function viewDetails(empId){
    const emp=employees.find(e=>e.emp_id==empId);
    const empPayrolls=records.filter(r=>r.emp_id==empId);
    if(!emp||!empPayrolls.length) return;
    viewEmpId=empId;
    document.getElementById('view-modal-title').textContent=`Payroll Details - ${emp.full_name}`;
    document.getElementById('view-details-content').innerHTML=`
      <div class="details-list">
        ${empPayrolls.map(p=>{
          const isPending = p.approval_status === 'Pending';
          const isSent = p.approval_status === 'Sent';
          const isApproved = p.approval_status === 'Approved';
          
          return `
          <div class="detail-item">
            <div class="detail-header">
              <span class="detail-period">${formatDate(p.payperiod_start)} \u2013 ${formatDate(p.payperiod_end)}</span>
              <span class="detail-status ${p.approval_status.toLowerCase()}">${p.approval_status}</span>
            </div>
            <div class="detail-grid">
              <div class="detail-cell"><label>Base Salary</label><value>${fmt(p.base_salary)}</value></div>
              <div class="detail-cell"><label>Bonus</label><value>${fmt(p.bonus||0)}</value></div>
              <div class="detail-cell"><label>Overtime</label><value>${fmt(p.overtime||0)}</value></div>
              <div class="detail-cell"><label>Deductions</label><value>${fmt(p.deduction_total||0)}</value></div>
              <div class="detail-cell" style="grid-column:1/-1;"><label>Deductions Label</label><value>${p.deductions_label||'N/A'}</value></div>
              <div class="detail-cell" style="grid-column:1/-1;background:var(--sidebar);padding:8px;border-radius:6px;">
                <label>Net Salary</label><value style="font-weight:600;font-size:16px;">${fmt(p.net_salary)}</value>
              </div>
            </div>
            <div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end;">
              ${isPending
                ?`<button class="btn-send-finance" onclick="sendToFinance(${p.payroll_id});closeViewModal();">Send to Finance</button>
                  <button class="btn-edit"         onclick="openEditFromView(${p.payroll_id})">Edit</button>
                  <button class="btn-delete"        onclick="deleteFromView(${p.payroll_id})">Delete</button>`
                :`<span style="color:var(--muted);font-size:12px;">${isSent ? 'Sent to Finance - Cannot edit' : 'Approved - Cannot edit'}</span>`
              }
            </div>
          </div>`;
        }).join('')}
      </div>`;
    document.getElementById('view-modal').classList.add('open');
  }

  function closeViewModal(){ document.getElementById('view-modal').classList.remove('open'); viewEmpId=null; }
  function openEditFromView(id){ closeViewModal(); openEditModal(id); }
  function deleteFromView(id)  { closeViewModal(); openDelModal(id); }

  function formatDate(s){
    if(!s) return 'N/A';
    const d=new Date(s);
    return isNaN(d)?s:d.toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'});
  }

  function calcNet(){
    const b=parseFloat(document.getElementById('m-base').value)||0;
    const bo=parseFloat(document.getElementById('m-bonus').value)||0;
    const ot=parseFloat(document.getElementById('m-ot').value)||0;
    const d=parseFloat(document.getElementById('m-deductions').value)||0;
    document.getElementById('m-net-preview').textContent=fmt(Math.max(0,b+bo+ot-d));
  }

  function openAddModal(){
    editId=null;
    document.getElementById('modal-title').textContent='Add Payroll Record';
    ['m-employee','m-dept','m-position','m-period-start','m-period-end',
     'm-base','m-bonus','m-ot','m-deductions','m-deductions-label'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('m-net-preview').textContent='\u20B10.00';
    document.getElementById('pay-modal').classList.add('open');
  }

  function openEditModal(id){
    const r=records.find(rec=>rec.payroll_id==id); if(!r) return;
    editId=id;
    document.getElementById('modal-title').textContent='Edit Payroll Record';
    document.getElementById('m-employee').value        =r.emp_id;
    document.getElementById('m-dept').value            =r.dept_name||'N/A';
    document.getElementById('m-position').value        =r.pos_name ||'N/A';
    document.getElementById('m-period-start').value    =r.payperiod_start;
    document.getElementById('m-period-end').value      =r.payperiod_end;
    document.getElementById('m-base').value            =r.base_salary;
    document.getElementById('m-bonus').value           =r.bonus||'';
    document.getElementById('m-ot').value              =r.overtime||'';
    document.getElementById('m-deductions').value      =r.deduction_total||'';
    document.getElementById('m-deductions-label').value=r.deductions_label||'';
    calcNet();
    document.getElementById('pay-modal').classList.add('open');
  }

  function closeModal(){ document.getElementById('pay-modal').classList.remove('open'); editId=null; }

  /* ── SAVE RECORD ────────────────────────────────────────────── */
  async function saveRecord(){
    const empId=document.getElementById('m-employee').value;
    if(!empId){ showToast('Please select an employee','error'); return; }
    const start=document.getElementById('m-period-start').value;
    const end  =document.getElementById('m-period-end').value;
    if(!start||!end){ showToast('Please set pay period dates','error'); return; }
    const base=parseFloat(document.getElementById('m-base').value)||0;
    if(base<=0){ showToast('Base salary is required','error'); return; }

    const data={
        emp_id:          parseInt(empId),
        payperiod_start: start,
        payperiod_end:   end,
        base_salary:     base,
        bonus:           parseFloat(document.getElementById('m-bonus').value)||0,
        overtime:        document.getElementById('m-ot').value||'0',
        deduction_total: parseFloat(document.getElementById('m-deductions').value)||0,
        deductions_label:document.getElementById('m-deductions-label').value||''
    };

    try{
        if(editId) data.payroll_id=parseInt(editId);
        const url=editId
            ?'../src/api/hr/payroll/update_payroll.php'
            :'../src/api/hr/payroll/add_payroll.php';
        
        console.log('Sending to:', url, 'Data:', data);
        
        const res=await fetch(url,{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify(data)
        });
        
        const result=await res.json();
        console.log('Response:', result);
        
        if(result.status==='success'){
            showToast(editId?'Payroll updated successfully':'Payroll record created');
            closeModal(); 
            loadPayrolls();
        } else { 
            showToast(result.message||'Failed to save','error'); 
        }
    }catch(err){ 
        showToast('Error saving record','error'); 
        console.error('Save error:', err);
    }
  }

  /* ── SEND TO FINANCE ───────────────────────────────────────── */
  async function sendToFinance(id){
    try{
      const res=await fetch('../src/api/hr/payroll/submit_payroll.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({payroll_ids:[id]})
      });
      const result=await res.json();
      if(result.status==='success'){ showToast('Sent to Finance for approval'); loadPayrolls(); }
      else { showToast(result.message||'Failed to send','error'); }
    }catch(err){ showToast('Error sending to finance','error'); console.error(err); }
  }

  async function sendAllToFinance(){
    const pending=records.filter(r=>r.approval_status==='Pending');
    if(!pending.length){ showToast('No pending records to send','error'); return; }

    try{
      const res=await fetch('../src/api/hr/payroll/submit_payroll.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({payroll_ids:pending.map(r=>r.payroll_id)})
      });
      const result=await res.json();
      if(result.status==='success'){ showToast(result.message||`${pending.length} records sent to Finance`); loadPayrolls(); }
      else { showToast(result.message||'Failed to send','error'); }
    }catch(err){ showToast('Error sending to finance','error'); console.error(err); }
  }

  async function sendEmployeeToFinance(empId){
    const empPending=records.filter(r=>r.emp_id==empId&&r.approval_status==='Pending');
    if(!empPending.length){ showToast('No pending records for this employee','error'); return; }

    try{
      const res=await fetch('../src/api/hr/payroll/submit_payroll.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({payroll_ids:empPending.map(r=>r.payroll_id)})
      });
      const result=await res.json();
      if(result.status==='success'){ showToast(result.message||`${empPending.length} records sent to Finance`); loadPayrolls(); }
      else { showToast(result.message||'Failed to send','error'); }
    }catch(err){ showToast('Error sending to finance','error'); console.error(err); }
  }

  /* ── DELETE ─────────────────────────────────────────────────── */
  function openDelModal(id){ deleteId=id; document.getElementById('del-modal').classList.add('open'); }
  function closeDelModal(){ document.getElementById('del-modal').classList.remove('open'); deleteId=null; }

  async function confirmDelete(){
    if(!deleteId) return;
    
    try{
      const res=await fetch('../src/api/hr/payroll/delete_payroll.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({payroll_id:deleteId})
      });
      const result=await res.json();
      if(result.status==='success'){ showToast('Payroll record deleted'); closeDelModal(); loadPayrolls(); }
      else { showToast(result.message||'Failed to delete','error'); }
    }catch(err){ showToast('Error deleting record','error'); console.error(err); }
  }

  /* ── EXPORT CSV ─────────────────────────────────────────────── */
  function exportCSV(){
    const headers=['ID','Employee','Department','Position','Period Start','Period End','Base Salary','Bonus','Overtime','Deductions','Net Salary','Status'];
    const rows=records.map(r=>[r.payroll_id,r.emp_fname+' '+r.emp_lname,r.dept_name||'N/A',r.pos_name||'N/A',r.payperiod_start,r.payperiod_end,r.base_salary,r.bonus||0,r.overtime||0,r.deduction_total||0,r.net_salary,r.approval_status||'Pending']);
    const csv=[headers,...rows].map(r=>r.map(v=>`"${v}"`).join(',')).join('\n');
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
    a.download='payroll_records.csv'; a.click();
  }

  /* ── EXPORT PDF ────────────────────────────────────────────── */
  async function exportPDF() {
    try {
      const doc = new jsPDF();
      
      // Add logo
      const logoUrl = '../assets/images/code_latte.png';
      const img = new Image();
      img.crossOrigin = 'Anonymous';
      
      await new Promise((resolve, reject) => {
        img.onload = resolve;
        img.onerror = reject;
        img.src = logoUrl;
      });
      
      // Convert to base64
      const canvas = document.createElement('canvas');
      canvas.width = img.width;
      canvas.height = img.height;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0);
      const logoData = canvas.toDataURL('image/png');
      
      // Add logo to PDF (top left, 20x20mm with rounded corners effect)
      doc.addImage(logoData, 'PNG', 10, 10, 20, 20);
      
      // Company name
      doc.setFontSize(20);
      doc.setFont('helvetica', 'bold');
      doc.text('Code Latte', 35, 20);
      
      doc.setFontSize(12);
      doc.setFont('helvetica', 'normal');
      doc.text('Payroll Report', 35, 28);
      
      // Date generated
      const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
      doc.setFontSize(10);
      doc.text(`Generated: ${today}`, 35, 34);
      
      // Line separator
      doc.setDrawColor(92, 74, 30);
      doc.setLineWidth(0.5);
      doc.line(10, 40, 200, 40);
      
      // Summary stats
      const totalNet = records.reduce((s,r)=>s+parseFloat(r.net_salary),0);
      const totalRecords = records.length;
      
      doc.setFontSize(11);
      doc.text(`Total Records: ${totalRecords}`, 10, 50);
      doc.text(`Total Net Salary: ${fmt(totalNet)}`, 10, 58);
      
      // Table data
      const tableData = records.map(r => [
        `${r.emp_fname} ${r.emp_lname}`,
        r.dept_name || 'N/A',
        r.pos_name || 'N/A',
        `${r.payperiod_start} to ${r.payperiod_end}`,
        fmt(r.base_salary),
        fmt(r.bonus || 0),
        fmt(r.overtime || 0),
        fmt(r.deduction_total || 0),
        fmt(r.net_salary),
        r.approval_status
      ]);
      
      // Auto table
      doc.autoTable({
        startY: 68,
        head: [['Employee', 'Dept', 'Position', 'Period', 'Base', 'Bonus', 'OT', 'Deductions', 'Net', 'Status']],
        body: tableData,
        theme: 'striped',
        headStyles: {
          fillColor: [92, 74, 30],
          textColor: [255, 255, 255],
          fontSize: 9
        },
        bodyStyles: {
          fontSize: 9
        },
        columnStyles: {
          0: { cellWidth: 30 },
          3: { cellWidth: 35 },
          8: { fontStyle: 'bold' }
        },
        styles: {
          overflow: 'ellipsize',
          cellPadding: 2
        },
        margin: { top: 10 }
      });
      
      // Footer
      const pageCount = doc.internal.getNumberOfPages();
      for(let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(128);
        doc.text(`Code Latte HR System - Page ${i} of ${pageCount}`, 105, 285, { align: 'center' });
      }
      
      doc.save(`Code_Latte_Payroll_${new Date().toISOString().split('T')[0]}.pdf`);
      showToast('PDF exported successfully');
      
    } catch (err) {
      console.error('PDF export error:', err);
      showToast('Error exporting PDF - check console for details', 'error');
    }
  }

  document.getElementById('pay-modal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
  document.getElementById('del-modal').addEventListener('click', function(e){ if(e.target===this) closeDelModal(); });
  document.getElementById('view-modal').addEventListener('click',function(e){ if(e.target===this) closeViewModal(); });

  document.addEventListener('DOMContentLoaded',()=>{ loadEmployees(); loadPayrolls(); });
</script>
</body>
</html>