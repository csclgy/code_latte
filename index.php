<?php 
$base = '/hrm_module/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Code Latte – HR System</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:        #f5f0e8;
      --sidebar:   #f0ebe0;
      --card:      #faf7f2;
      --border:    #e3ddd2;
      --text:      #2c2416;
      --muted:     #8a7f6e;
      --accent:    #5c4a1e;
      --gold:      #c49a2b;
      --gold-lt:   #e8c96a;
      --present:   #3d7a4e;
      --absent:    #b84040;
      --late:      #c4842b;
      --leave:     #5a7ab8;
      --bar-h:     6px;
      --radius:    14px;
      --shadow:    0 2px 12px rgba(80,60,20,.08);
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      display: flex;
      min-height: 100vh;
    }

    /* ── SIDEBAR ── */
    aside {
      width: 200px;
      min-height: 100vh;
      background: var(--sidebar);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      padding: 24px 0;
      position: fixed;
      top: 0; left: 0;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 0 20px 28px;
      border-bottom: 1px solid var(--border);
    }
    .logo-icon {
      width: 34px; height: 34px;
      background: var(--accent);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      font-size: 16px;
    }
    .logo-text .name   { font-family: 'DM Serif Display', serif; font-size: 15px; line-height:1.1; }
    .logo-text .sub    { font-size: 11px; color: var(--muted); }

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
    nav a.active svg { stroke: #fff; }

    .user-block {
      padding: 16px 20px;
      border-top: 1px solid var(--border);
      display: flex; align-items: center; gap: 10px;
    }
    .avatar {
      width: 34px; height: 34px;
      border-radius: 50%;
      background: var(--gold);
      display: flex; align-items: center; justify-content: center;
      font-weight: 600; font-size: 13px; color: #fff;
    }
    .user-info .uname { font-size: 13px; font-weight: 600; }
    .user-info .urole { font-size: 11px; color: var(--muted); }

    /* ── MAIN ── */
    main {
      margin-left: 200px;
      flex: 1;
      padding: 32px 36px;
      min-height: 100vh;
    }

    h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 26px;
      font-weight: 400;
      margin-bottom: 24px;
    }

    /* ── STAT CARDS ── */
    .stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }
    .stat-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px 22px;
      box-shadow: var(--shadow);
    }
    .stat-card .label { font-size: 12px; color: var(--muted); font-weight: 500; margin-bottom: 6px; }
    .stat-card .value { font-family: 'DM Serif Display', serif; font-size: 32px; line-height:1; margin-bottom: 4px; }
    .stat-card .desc  { font-size: 12px; color: var(--muted); }

    /* ── GRID 2-COL ── */
    .row { display: grid; grid-template-columns: 1fr 440px; gap: 20px; margin-bottom: 20px; }

    /* ── PANEL ── */
    .panel {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 22px 24px;
      box-shadow: var(--shadow);
    }
    .panel-title {
      font-size: 14px; font-weight: 600;
      margin-bottom: 18px;
    }

    /* ── CHART ── */
    .chart-wrap {
      display: flex; align-items: flex-end; gap: 10px;
      height: 130px;
      margin-bottom: 12px;
    }
    .month-group {
      flex: 1;
      display: flex; align-items: flex-end; gap: 3px;
      flex-direction: row;
    }
    .bar-set { display: flex; flex-direction: column; align-items: center; flex:1; gap:2px;}
    .bar {
      width: 100%;
      border-radius: 4px 4px 0 0;
      transition: opacity .2s;
    }
    .bar:hover { opacity: .75; cursor: pointer; }
    .bar.present { background: var(--accent); }
    .bar.absent  { background: var(--absent); }
    .bar.late    { background: var(--gold); }

    .month-labels {
      display: flex; gap: 10px;
      padding: 0 0 6px;
    }
    .month-labels span { flex: 1; text-align: center; font-size: 11px; color: var(--muted); }

    .legend { display: flex; gap: 14px; }
    .legend-item { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--muted); }
    .legend-dot  { width:10px; height:10px; border-radius: 50%; }

    /* ── TODAY STATUS ── */
    .status-list { display: flex; flex-direction: column; gap: 2px; }
    .status-row {
      display: flex; align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid var(--border);
    }
    .status-row:last-child { border-bottom: none; }
    .status-info { flex: 1; }
    .status-name { font-size: 13px; font-weight: 600; }
    .status-role { font-size: 11px; color: var(--muted); margin-top: 1px; }
    .badge {
      font-size: 11px; font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
    }
    .badge.present { background: #e8f5ec; color: var(--present); }
    .badge.absent  { background: #fceaea; color: var(--absent); }
    .badge.late    { background: #fef3e2; color: var(--late); }
    .badge.leave   { background: #e8eef8; color: var(--leave); }

    /* ── PAYROLL BAR ── */
    .payroll-list { display: flex; flex-direction: column; gap: 14px; }
    .payroll-row .top-row {
      display: flex; justify-content: space-between;
      font-size: 13px; margin-bottom: 6px;
    }
    .payroll-row .amount { font-weight: 600; font-size: 13px; }
    .bar-track {
      height: var(--bar-h);
      background: var(--border);
      border-radius: 99px;
      overflow: hidden;
    }
    .bar-fill {
      height: 100%;
      border-radius: 99px;
      background: linear-gradient(90deg, var(--accent), var(--gold));
    }

    /* ── PAYROLL SUMMARY ── */
    .summary-list { display: flex; flex-direction: column; gap: 2px; }
    .summary-row {
      display: flex; align-items: center;
      padding: 11px 0;
      border-bottom: 1px solid var(--border);
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-info { flex: 1; }
    .summary-name { font-size: 13px; font-weight: 600; }
    .summary-date { font-size: 11px; color: var(--muted); margin-top: 1px; }
    .pay-badge {
      font-size: 12px; font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
    }
    .pay-badge.paid    { background: #e8f5ec; color: var(--present); }
    .pay-badge.pending { background: #fef3e2; color: var(--late); }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(14px); }
      to   { opacity:1; transform:translateY(0); }
    }
    .stat-card, .panel { animation: fadeUp .4s ease both; }
    .stat-card:nth-child(1){ animation-delay:.05s }
    .stat-card:nth-child(2){ animation-delay:.10s }
    .stat-card:nth-child(3){ animation-delay:.15s }
    .stat-card:nth-child(4){ animation-delay:.20s }
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
    <a href="index.php" class="active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a href="<?= $base ?>pages/employee_management_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Employees
    </a>
    <a href="<?= $base ?>pages/attendance_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
      Attendance
    </a>
    <a href="<?= $base ?>pages/payroll_hr.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      Payroll
    </a>
    <a href="<?= $base ?>pages/recruitment_hr.php">
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
  <h1>Dashboard</h1>

  <!-- STAT CARDS -->
  <div class="stats">
    <div class="stat-card">
      <div class="label">Total Staff</div>
      <div class="value">4</div>
      <div class="desc">Active employees</div>
    </div>
    <div class="stat-card">
      <div class="label">Present Today</div>
      <div class="value">2</div>
      <div class="desc">Today's attendance</div>
    </div>
    <div class="stat-card">
      <div class="label">Monthly Payroll</div>
      <div class="value">₱42K</div>
      <div class="desc">Total net salary</div>
    </div>
    <div class="stat-card">
      <div class="label">Applicants</div>
      <div class="value">3</div>
      <div class="desc">Total received</div>
    </div>
  </div>

  <!-- ROW 1 -->
  <div class="row">
    <!-- Monthly Attendance Chart -->
    <div class="panel">
      <div class="panel-title">Monthly Attendance</div>
      <div class="chart-wrap" id="attendance-chart"></div>
      <div class="month-labels" id="month-labels"></div>
      <div class="legend">
        <div class="legend-item"><div class="legend-dot" style="background:var(--accent)"></div>Present</div>
        <div class="legend-item"><div class="legend-dot" style="background:var(--absent)"></div>Absent</div>
        <div class="legend-item"><div class="legend-dot" style="background:var(--gold)"></div>Late</div>
      </div>
    </div>

    <!-- Today's Status -->
    <div class="panel">
      <div class="panel-title">Today's Status</div>
      <div class="status-list">
        <div class="status-row">
          <div class="status-info">
            <div class="status-name">Ana Santos</div>
            <div class="status-role">Supervisor · Morning</div>
          </div>
          <span class="badge present">● Present</span>
        </div>
        <div class="status-row">
          <div class="status-info">
            <div class="status-name">Juan Reyes</div>
            <div class="status-role">Barista · Morning</div>
          </div>
          <span class="badge present">● Present</span>
        </div>
        <div class="status-row">
          <div class="status-info">
            <div class="status-name">Karl Dela Cruz</div>
            <div class="status-role">Kitchen Staff · Afternoon</div>
          </div>
          <span class="badge absent">● Absent</span>
        </div>
        <div class="status-row">
          <div class="status-info">
            <div class="status-name">Maria Cruz</div>
            <div class="status-role">Cashier · Morning</div>
          </div>
          <span class="badge late">● Late</span>
        </div>
        <div class="status-row">
          <div class="status-info">
            <div class="status-name">Ramon Lopez</div>
            <div class="status-role">Barista · Morning</div>
          </div>
          <span class="badge leave">● On Leave</span>
        </div>
      </div>
    </div>
  </div>

  <!-- ROW 2 -->
  <div class="row">
    <!-- Payroll by Role -->
    <div class="panel">
      <div class="panel-title">Payroll by Role</div>
      <div class="payroll-list" id="payroll-bars"></div>
    </div>

    <!-- Payroll Summary -->
    <div class="panel">
      <div class="panel-title">Payroll Summary</div>
      <div class="summary-list">
        <div class="summary-row">
          <div class="summary-info">
            <div class="summary-name">Juan Reyes</div>
            <div class="summary-date">Mar 1, 2026 – Mar 15, 2026</div>
          </div>
          <span class="pay-badge paid">● ₱10,400.00</span>
        </div>
        <div class="summary-row">
          <div class="summary-info">
            <div class="summary-name">Maria Cruz</div>
            <div class="summary-date">Mar 1, 2026 – Mar 15, 2026</div>
          </div>
          <span class="pay-badge paid">● ₱8,100.00</span>
        </div>
        <div class="summary-row">
          <div class="summary-info">
            <div class="summary-name">Ramon Lopez</div>
            <div class="summary-date">Mar 1, 2026 – Mar 15, 2026</div>
          </div>
          <span class="pay-badge pending">● Pending</span>
        </div>
        <div class="summary-row">
          <div class="summary-info">
            <div class="summary-name">Ana Santos</div>
            <div class="summary-date">Mar 1, 2026 – Mar 15, 2026</div>
          </div>
          <span class="pay-badge pending">● Pending</span>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  // ── Monthly Attendance Chart ──
  const months = ['Jan','Feb','Mar','Apr','May','Jun'];
  const data = [
    { present: 100, absent: 18, late: 10 },
    { present: 95,  absent: 22, late: 12 },
    { present: 108, absent: 15, late:  8 },
    { present: 90,  absent: 20, late: 14 },
    { present: 102, absent: 12, late:  9 },
    { present: 115, absent: 16, late: 11 },
  ];
  const maxVal = Math.max(...data.map(d => d.present));
  const chartH  = 120;

  const chartEl  = document.getElementById('attendance-chart');
  const labelsEl = document.getElementById('month-labels');

  data.forEach((d, i) => {
    const group = document.createElement('div');
    group.className = 'month-group';

    const sets = [
      { cls: 'present', val: d.present },
      { cls: 'absent',  val: d.absent  },
      { cls: 'late',    val: d.late    },
    ];
    sets.forEach(s => {
      const wrap = document.createElement('div');
      wrap.className = 'bar-set';
      const bar = document.createElement('div');
      bar.className = `bar ${s.cls}`;
      const h = Math.round((s.val / maxVal) * chartH);
      bar.style.height = h + 'px';
      bar.title = `${months[i]} – ${s.cls}: ${s.val}`;
      wrap.appendChild(bar);
      group.appendChild(wrap);
    });

    chartEl.appendChild(group);

    const lbl = document.createElement('span');
    lbl.textContent = months[i];
    labelsEl.appendChild(lbl);
  });

  // ── Payroll Bars ──
  const roles = [
    { name: 'Barista',       amount: 36000 },
    { name: 'Cashier',       amount: 25500 },
    { name: 'Kitchen Staff', amount: 16000 },
    { name: 'Supervisor',    amount: 13000 },
  ];
  const maxPay = roles[0].amount;
  const container = document.getElementById('payroll-bars');

  roles.forEach(r => {
    const row = document.createElement('div');
    row.className = 'payroll-row';
    const pct = Math.round((r.amount / maxPay) * 100);
    row.innerHTML = `
      <div class="top-row">
        <span>${r.name}</span>
        <span class="amount">₱${r.amount.toLocaleString()}</span>
      </div>
      <div class="bar-track">
        <div class="bar-fill" style="width:${pct}%"></div>
      </div>`;
    container.appendChild(row);
  });
</script>
</body>
</html>