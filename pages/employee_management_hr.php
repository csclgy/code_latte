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
    .logo-icon {
      width: 34px; height: 34px;
      background: var(--accent);
      border-radius: 8px;
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
    <div class="avatar">A</div>
    <div class="user-info">
      <div class="uname">Admin</div>
      <div class="urole">Store Manager</div>
    </div>
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
        <input type="text" id="f-first" placeholder="First name"/>
      </div>
      <div class="form-group">
        <label>Last Name</label>
        <input type="text" id="f-last" placeholder="Last name"/>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="f-email" placeholder="email@codelatte.com"/>
      </div>
      <div class="form-group">
        <label>Contact</label>
        <input type="text" id="f-contact" placeholder="+63 9XX XXX XXXX"/>
      </div>
      <div class="form-group">
        <label>Role</label>
        <select id="f-role">
          <option>Barista</option>
          <option>Cashier</option>
          <option>Kitchen Staff</option>
          <option>Supervisor</option>
        </select>
      </div>
      <div class="form-group">
        <label>Shift</label>
        <select id="f-shift">
          <option value="Morning">Morning (6AM–2PM)</option>
          <option value="Afternoon">Afternoon (2PM–10PM)</option>
          <option value="Evening">Evening (10PM–6AM)</option>
        </select>
      </div>
      <div class="form-group">
        <label>Date Hired</label>
        <input type="date" id="f-date"/>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select id="f-status">
          <option>Active</option>
          <option>On Leave</option>
          <option>Inactive</option>
        </select>
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
  let employees = [
    { id:'#001', first:'Juan',  last:'Reyes',     email:'juan@codelatte.com',  contact:'+63 912 345 6789', role:'Barista',       shift:'Morning',   date:'Jan 10, 2024', status:'Active'   },
    { id:'#002', first:'Maria', last:'Cruz',      email:'maria@codelatte.com', contact:'+63 917 234 5678', role:'Cashier',       shift:'Morning',   date:'Mar 15, 2024', status:'Active'   },
    { id:'#003', first:'Ramon', last:'Lopez',     email:'ramon@codelatte.com', contact:'+63 918 765 4321', role:'Barista',       shift:'Morning',   date:'Nov 20, 2023', status:'On Leave' },
    { id:'#004', first:'Ana',   last:'Santos',    email:'ana@codelatte.com',   contact:'+63 920 111 2222', role:'Supervisor',    shift:'Morning',   date:'Jun 1, 2023',  status:'Active'   },
    { id:'#005', first:'Karl',  last:'Dela Cruz', email:'karl@codelatte.com',  contact:'+63 915 333 4444', role:'Kitchen Staff', shift:'Afternoon', date:'Jul 1, 2024',  status:'Active'   },
  ];

  let editIndex   = null;
  let deleteIndex = null;

  function statusClass(s) {
    if (s === 'Active')   return 'active';
    if (s === 'On Leave') return 'on-leave';
    return 'inactive';
  }

  function renderTable(list) {
    const tbody = document.getElementById('emp-table-body');
    document.getElementById('record-count').textContent =
      list.length + ' record' + (list.length !== 1 ? 's' : '');
    tbody.innerHTML = list.map(e => {
      const realIdx = employees.indexOf(e);
      return `
        <tr>
          <td class="id-cell">${e.id}</td>
          <td class="name-cell">${e.first} ${e.last}</td>
          <td><span class="role-badge">${e.role}</span></td>
          <td class="shift-cell">${e.shift}</td>
          <td class="date-cell">${e.date}</td>
          <td><span class="status-badge ${statusClass(e.status)}">
            <span class="status-dot"></span>${e.status}
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

  function filterTable() {
    const q  = document.getElementById('search-input').value.toLowerCase();
    const rf = document.getElementById('role-filter').value;
    const sf = document.getElementById('status-filter').value;
    const filtered = employees.filter(e => {
      const fullName = (e.first + ' ' + e.last).toLowerCase();
      return (!q  || fullName.includes(q) || e.id.includes(q)) &&
             (!rf || e.role === rf) &&
             (!sf || e.status === sf);
    });
    renderTable(filtered);
  }

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
    ['f-first','f-last','f-email','f-contact','f-date'].forEach(id =>
      document.getElementById(id).value = '');
    document.getElementById('f-role').value   = 'Barista';
    document.getElementById('f-shift').value  = 'Morning';
    document.getElementById('f-status').value = 'Active';
  }

  function openEditForm(idx) {
    editIndex = idx;
    const e = employees[idx];
    document.getElementById('form-title').textContent = 'Edit Employee';
    document.getElementById('f-first').value   = e.first;
    document.getElementById('f-last').value    = e.last;
    document.getElementById('f-email').value   = e.email;
    document.getElementById('f-contact').value = e.contact;
    document.getElementById('f-role').value    = e.role;
    document.getElementById('f-shift').value   = e.shift;
    document.getElementById('f-status').value  = e.status;
    const d = new Date(e.date);
    if (!isNaN(d)) document.getElementById('f-date').value = d.toISOString().split('T')[0];
    document.getElementById('emp-form-panel').classList.add('open');
  }

  function saveEmployee() {
    const first   = document.getElementById('f-first').value.trim();
    const last    = document.getElementById('f-last').value.trim();
    const email   = document.getElementById('f-email').value.trim();
    const contact = document.getElementById('f-contact').value.trim();
    const role    = document.getElementById('f-role').value;
    const shift   = document.getElementById('f-shift').value;
    const status  = document.getElementById('f-status').value;
    const rawDate = document.getElementById('f-date').value;
    if (!first || !last) { alert('Please enter first and last name.'); return; }
    const dateStr = rawDate
      ? new Date(rawDate).toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'})
      : '—';
    if (editIndex === null) {
      const newId = '#' + String(employees.length + 1).padStart(3, '0');
      employees.push({ id: newId, first, last, email, contact, role, shift, date: dateStr, status });
    } else {
      employees[editIndex] = { ...employees[editIndex], first, last, email, contact, role, shift, date: dateStr, status };
    }
    closeForm();
    filterTable();
  }

  function openDeleteModal(idx) {
    deleteIndex = idx;
    document.getElementById('del-modal').classList.add('open');
  }
  function closeDeleteModal() {
    document.getElementById('del-modal').classList.remove('open');
  }
  function confirmDelete() {
    if (deleteIndex !== null) employees.splice(deleteIndex, 1);
    closeDeleteModal();
    filterTable();
  }

  document.getElementById('del-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });

  renderTable(employees);
</script>
</body>
</html>