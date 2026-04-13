<?php require_once '../src/api/auth/check_session.php'; ?>
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
      --processing:#7b5ea7;
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
      width: 200px; min-height: 100vh;
      background: var(--sidebar);
      border-right: 1px solid var(--border);
      display: flex; flex-direction: column;
      padding: 24px 0;
      position: fixed; top: 0; left: 0;
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
    nav a:hover  { background: var(--border); color: var(--text); }
    nav a.active { background: var(--accent); color: #fff; }
    nav a.active svg { stroke: #fff; }

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
    main { margin-left: 200px; flex: 1; padding: 32px 36px; min-height: 100vh; }
    h1 { font-family: 'DM Serif Display', serif; font-size: 26px; font-weight: 400; margin-bottom: 24px; }

    /* ── STAT CARDS ── */
    .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .stat-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 20px 22px; box-shadow: var(--shadow);
    }
    .stat-card .label { font-size: 12px; color: var(--muted); font-weight: 500; margin-bottom: 6px; }
    .stat-card .value { font-family: 'DM Serif Display', serif; font-size: 32px; line-height:1; margin-bottom: 4px; }
    .stat-card .desc  { font-size: 12px; color: var(--muted); }

    /* ── GRID 2-COL ── */
    .row { display: grid; grid-template-columns: 1fr 440px; gap: 20px; margin-bottom: 20px; align-items: start; }

    /* ── PANEL ── */
    .panel {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 22px 24px; box-shadow: var(--shadow);
    }
    .panel-title { font-size: 14px; font-weight: 600; margin-bottom: 18px; }

    /* ── CHART ── */
    .chart-wrap { display: flex; align-items: flex-end; gap: 10px; height: clamp(100px, 18vw, 160px); margin-bottom: 12px; position: relative; }
    .month-group { flex:1; display:flex; align-items:flex-end; gap:3px; height:100%; }
    .bar-set { display:flex; flex-direction:column; justify-content:flex-end; align-items:stretch; flex:1; height:100%; }
    .bar { width:100%; border-radius:4px 4px 0 0; transition:height .4s cubic-bezier(.4,0,.2,1),opacity .15s,filter .15s; cursor:pointer; min-height:2px; }
    .bar:hover   { opacity:.80; filter:brightness(1.12); }
    .bar.present { background: var(--accent); }
    .bar.absent  { background: var(--absent); }
    .bar.late    { background: var(--gold); }

    .month-labels { display:flex; gap:10px; padding:0 0 6px; }
    .month-labels span { flex:1; text-align:center; font-size:11px; color:var(--muted); }
    .legend { display:flex; gap:14px; }
    .legend-item { display:flex; align-items:center; gap:5px; font-size:12px; color:var(--muted); }
    .legend-dot  { width:10px; height:10px; border-radius:50%; }

    /* ── ATTENDANCE TOOLTIP ── */
    .att-tooltip {
      position: fixed; background: #2c2416; color: #faf7f2;
      font-size: 12px; padding: 9px 13px; border-radius: 9px;
      pointer-events: none; opacity: 0;
      transition: opacity .12s ease, transform .12s ease;
      z-index: 9999; white-space: nowrap;
      box-shadow: 0 6px 20px rgba(0,0,0,.28);
      line-height: 1.55; transform: translateY(4px);
    }
    .att-tooltip.visible { opacity:1; transform:translateY(0); }
    .att-tooltip .tip-month { font-size:10px; opacity:.55; margin-bottom:4px; text-transform:uppercase; letter-spacing:.06em; }
    .att-tooltip .tip-type  { display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; margin-bottom:4px; }
    .att-tooltip .tip-dot   { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
    .att-tooltip .tip-count { font-size:12px; }
    .att-tooltip .tip-pct   { font-size:11px; opacity:.6; margin-top:2px; }

    /* ── SHARED HIDDEN-SCROLLBAR SCROLL ZONE ── */
    .panel-scroll {
      max-height: 264px;         /* ~4 rows visible */
      overflow-y: scroll;
      scrollbar-width: none;     /* Firefox */
      -ms-overflow-style: none;  /* IE / Edge */
    }
    .panel-scroll::-webkit-scrollbar { display: none; } /* Chrome / Safari */

    /* ── TODAY STATUS ── */
    .status-list { display:flex; flex-direction:column; }
    .status-row { display:flex; align-items:center; padding:10px 0; border-bottom:1px solid var(--border); }
    .status-row:last-child { border-bottom:none; }
    .status-info { flex:1; }
    .status-name { font-size:13px; font-weight:600; }
    .status-role { font-size:11px; color:var(--muted); margin-top:1px; }
    .badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; white-space:nowrap; }
    .badge.present { background:#e8f5ec; color:var(--present); }
    .badge.absent  { background:#fceaea; color:var(--absent); }
    .badge.late    { background:#fef3e2; color:var(--late); }
    .badge.leave   { background:#e8eef8; color:var(--leave); }

    /* ── PAYROLL BAR ── */
    .payroll-list { display:flex; flex-direction:column; gap:14px; }
    .payroll-row .top-row { display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px; }
    .payroll-row .amount  { font-weight:600; font-size:13px; }
    .bar-track { height:var(--bar-h); background:var(--border); border-radius:99px; overflow:hidden; }
    .bar-fill  { height:100%; border-radius:99px; background:linear-gradient(90deg,var(--accent),var(--gold)); }

    /* ── PAYROLL SUMMARY ── */
    .summary-list { display:flex; flex-direction:column; }
    .summary-row { display:flex; align-items:center; padding:11px 0; border-bottom:1px solid var(--border); }
    .summary-row:last-child { border-bottom:none; }
    .summary-info { flex:1; min-width:0; }
    .summary-name { font-size:13px; font-weight:600; }
    .summary-sub  { font-size:11px; color:var(--muted); margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .pay-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; white-space:nowrap; margin-left:8px; }
    .pay-badge.paid       { background:#e8f5ec; color:var(--present); }
    .pay-badge.pending    { background:#fef3e2; color:var(--late); }
    .pay-badge.processing { background:#f0eaf8; color:var(--processing); }

    /* ── MISC ── */
    .empty-state { font-size:13px; color:var(--muted); text-align:center; padding:24px 0; }
    .skeleton { background:linear-gradient(90deg,var(--border) 25%,var(--bg) 50%,var(--border) 75%); background-size:200% 100%; animation:shimmer 1.2s infinite; border-radius:6px; height:16px; }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
    @keyframes fadeUp  { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
    .stat-card,.panel { animation:fadeUp .4s ease both; }
    .stat-card:nth-child(1){animation-delay:.05s}
    .stat-card:nth-child(2){animation-delay:.10s}
    .stat-card:nth-child(3){animation-delay:.15s}
    .stat-card:nth-child(4){animation-delay:.20s}
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
    <a href="dashboard_hr.php" class="active">
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
  <h1>Dashboard</h1>

  <div class="stats">
    <div class="stat-card">
      <div class="label">Total Staff</div>
      <div class="value" id="stat-total-staff">—</div>
      <div class="desc">Active employees</div>
    </div>
    <div class="stat-card">
      <div class="label">Present Today</div>
      <div class="value" id="stat-present-today">—</div>
      <div class="desc">Today's attendance</div>
    </div>
    <div class="stat-card">
      <div class="label">Monthly Payroll</div>
      <div class="value" id="stat-monthly-payroll">—</div>
      <div class="desc">Total net salary</div>
    </div>
    <div class="stat-card">
      <div class="label">Applicants</div>
      <div class="value" id="stat-applicants">—</div>
      <div class="desc">Total received</div>
    </div>
  </div>

  <div class="row">
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

    <div class="panel">
      <div class="panel-title">Today's Status</div>
      <div class="panel-scroll">
        <div class="status-list" id="todays-status">
          <div class="skeleton" style="margin-bottom:10px"></div>
          <div class="skeleton" style="margin-bottom:10px"></div>
          <div class="skeleton"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="panel" id="payroll-bars-panel">
      <div class="panel-title">Payroll by Role</div>
      <div class="payroll-list" id="payroll-bars">
        <div class="skeleton" style="margin-bottom:10px"></div>
        <div class="skeleton" style="margin-bottom:10px"></div>
        <div class="skeleton"></div>
      </div>
    </div>

    <div class="panel" id="payroll-summary-panel">
      <div class="panel-title">Payroll Summary</div>
      <div class="panel-scroll">
        <div class="summary-list" id="payroll-summary">
          <div class="skeleton" style="margin-bottom:10px"></div>
          <div class="skeleton" style="margin-bottom:10px"></div>
          <div class="skeleton"></div>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="att-tooltip" id="att-tooltip">
  <div class="tip-month" id="tip-month"></div>
  <div class="tip-type">
    <div class="tip-dot" id="tip-dot"></div>
    <span id="tip-type-label"></span>
  </div>
  <div class="tip-count" id="tip-count"></div>
  <div class="tip-pct"   id="tip-pct"></div>
</div>

<script>
  const API_URL = '../src/api/hr/dashboard/get_dashboard.php';

  /* ── Helpers ── */
  function formatPeso(v) {
    return '₱' + Number(v).toLocaleString('en-PH', { minimumFractionDigits:2, maximumFractionDigits:2 });
  }
  function formatPesoShort(v) {
    if (v >= 1000000) return '₱' + (v/1000000).toFixed(1) + 'M';
    if (v >= 1000)    return '₱' + (v/1000).toFixed(0) + 'K';
    return formatPeso(v);
  }
  function formatDate(s) {
    if (!s) return '';
    return new Date(s).toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
  }

  /* ── Tooltip ── */
  const tooltip      = document.getElementById('att-tooltip');
  const tipMonth     = document.getElementById('tip-month');
  const tipDot       = document.getElementById('tip-dot');
  const tipTypeLabel = document.getElementById('tip-type-label');
  const tipCount     = document.getElementById('tip-count');
  const tipPct       = document.getElementById('tip-pct');
  const BAR_COLORS   = { present:'var(--accent)', absent:'var(--absent)', late:'var(--gold)' };
  const BAR_LABELS   = { present:'Present', absent:'Absent', late:'Late' };

  function showTooltip(e, month, type, count, total) {
    const pct = total > 0 ? Math.round((count/total)*100) : 0;
    tipMonth.textContent     = month;
    tipDot.style.background  = BAR_COLORS[type] || '#888';
    tipTypeLabel.textContent = BAR_LABELS[type]  || type;
    tipCount.textContent     = `${count} out of ${total} employee${total!==1?'s':''}`;
    tipPct.textContent       = `${pct}% of workforce`;
    tooltip.classList.add('visible');
    positionTooltip(e);
  }
  function positionTooltip(e) {
    requestAnimationFrame(() => {
      const tw=tooltip.offsetWidth, th=tooltip.offsetHeight, x=e.clientX, y=e.clientY, g=14;
      tooltip.style.left = ((x+g+tw>window.innerWidth)?x-tw-g:x+g)+'px';
      tooltip.style.top  = Math.min(Math.max(y-th/2,8),window.innerHeight-th-8)+'px';
    });
  }
  function hideTooltip() { tooltip.classList.remove('visible'); }

  /* ── Render: Stat Cards ── */
  function renderStatCards(s) {
    document.getElementById('stat-total-staff').textContent     = s.total_staff;
    document.getElementById('stat-present-today').textContent   = s.present_today;
    document.getElementById('stat-monthly-payroll').textContent = formatPesoShort(s.monthly_payroll);
    document.getElementById('stat-applicants').textContent      = s.total_applicants;
  }

  /* ── Render: Monthly Attendance Chart ── */
  function renderAttendanceChart(data, totalStaff) {
    const chartEl  = document.getElementById('attendance-chart');
    const labelsEl = document.getElementById('month-labels');
    chartEl.innerHTML = ''; labelsEl.innerHTML = '';
    if (!data || !data.length) { chartEl.innerHTML='<div class="empty-state">No data.</div>'; return; }

    const base = totalStaff > 0 ? totalStaff
      : Math.max(...data.map(d=>Math.max(d.present,d.absent,d.late)),1);

    data.forEach(d => {
      const g = document.createElement('div');
      g.className = 'month-group';
      [{cls:'present',val:d.present},{cls:'absent',val:d.absent},{cls:'late',val:d.late}].forEach(s => {
        const w=document.createElement('div'); w.className='bar-set';
        const b=document.createElement('div'); b.className=`bar ${s.cls}`;
        b.style.height = Math.min(100,(s.val/base)*100).toFixed(2)+'%';
        b.addEventListener('mouseenter', e=>showTooltip(e,d.label,s.cls,s.val,totalStaff||base));
        b.addEventListener('mousemove',  e=>positionTooltip(e));
        b.addEventListener('mouseleave', ()=>hideTooltip());
        w.appendChild(b); g.appendChild(w);
      });
      chartEl.appendChild(g);
      const l=document.createElement('span'); l.textContent=d.label; labelsEl.appendChild(l);
    });
  }

  /* ── Render: Today's Status ── */
  function renderTodaysStatus(list) {
    const container = document.getElementById('todays-status');
    container.innerHTML = '';
    if (!list || !list.length) {
      container.innerHTML = '<div class="empty-state">No employee records found.</div>';
      return;
    }
    const MAP = { 'Present':'present','Absent':'absent','Late':'late','On Leave':'leave' };
    list.forEach(e => {
      const cls = MAP[e.att_status]||'absent';
      const row = document.createElement('div'); row.className='status-row';
      row.innerHTML = `
        <div class="status-info">
          <div class="status-name">${e.emp_fname} ${e.emp_lname}</div>
          <div class="status-role">${e.emp_role}${e.emp_schedule?' · '+e.emp_schedule:''}</div>
        </div>
        <span class="badge ${cls}">● ${e.att_status||'Absent'}</span>`;
      container.appendChild(row);
    });
  }

  /* ── Render: Payroll by Role ── */
  function renderPayrollByRole(data) {
    const container = document.getElementById('payroll-bars');
    const panel     = document.getElementById('payroll-bars-panel');
    container.innerHTML = '';
    const active = (data||[]).filter(r=>r.total_net_salary>0);
    if (!active.length) { container.innerHTML='<div class="empty-state">No payroll data.</div>'; panel.style.height='auto'; return; }
    const maxPay = Math.max(...active.map(r=>r.total_net_salary),1);
    active.forEach(r => {
      const pct = Math.round((r.total_net_salary/maxPay)*100);
      const row = document.createElement('div'); row.className='payroll-row';
      row.innerHTML=`<div class="top-row"><span>${r.role}</span><span class="amount">${formatPeso(r.total_net_salary)}</span></div><div class="bar-track"><div class="bar-fill" style="width:${pct}%"></div></div>`;
      container.appendChild(row);
    });
    panel.style.height = (44+46+active.length*52)+'px';
  }

  /* ── Render: Payroll Summary ── */
  function renderPayrollSummary(data) {
    const container = document.getElementById('payroll-summary');
    container.innerHTML = '';
    if (!data || !data.length) { container.innerHTML='<div class="empty-state">No payroll records.</div>'; return; }
    data.forEach(p => {
      const sl = (p.payroll_status||'').toLowerCase();
      const bc = sl==='paid'?'paid':sl==='processing'?'processing':'pending';
      const label = sl==='paid' ? formatPeso(p.net_salary) : p.payroll_status;
      const row = document.createElement('div'); row.className='summary-row';
      row.innerHTML=`
        <div class="summary-info">
          <div class="summary-name">${p.emp_fname} ${p.emp_lname}</div>
          <div class="summary-sub">${p.emp_role} · ${formatDate(p.payperiod_start)} – ${formatDate(p.payperiod_end)}</div>
        </div>
        <span class="pay-badge ${bc}">● ${label}</span>`;
      container.appendChild(row);
    });
  }

  /* ── Load Dashboard ── */
  async function loadDashboard() {
    try {
      const res  = await fetch(API_URL);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = await res.json();
      if (json.status !== 'success') throw new Error(json.message||'API error');
      const ts = json.stat_cards.total_staff||0;
      renderStatCards(json.stat_cards);
      renderAttendanceChart(json.monthly_attendance, ts);
      renderTodaysStatus(json.todays_status);
      renderPayrollByRole(json.payroll_by_role);
      renderPayrollSummary(json.payroll_summary);

    } catch(err) {
      console.warn('API unavailable – dummy data:', err);
      const D = {
        stat_cards: { total_staff:12, present_today:8, monthly_payroll:105000, total_applicants:5 },
        monthly_attendance: [
          {label:'Nov',present:9,absent:2,late:1},{label:'Dec',present:7,absent:3,late:2},
          {label:'Jan',present:10,absent:1,late:1},{label:'Feb',present:8,absent:2,late:2},
          {label:'Mar',present:11,absent:1,late:0},{label:'Apr',present:8,absent:3,late:1},
        ],
        todays_status: [
          {emp_fname:'Maria',    emp_lname:'Santos',     emp_role:'Barista',       emp_schedule:'Morning',   att_status:'Present' },
          {emp_fname:'Jose',     emp_lname:'Reyes',      emp_role:'Cashier',       emp_schedule:'Morning',   att_status:'Present' },
          {emp_fname:'Ana',      emp_lname:'Cruz',       emp_role:'Barista',       emp_schedule:'Morning',   att_status:'Late'    },
          {emp_fname:'Carlo',    emp_lname:'Mendoza',    emp_role:'Kitchen Staff', emp_schedule:'Morning',   att_status:'Present' },
          {emp_fname:'Liza',     emp_lname:'Bautista',   emp_role:'Cashier',       emp_schedule:'Afternoon', att_status:'Present' },
          {emp_fname:'Ramon',    emp_lname:'Garcia',     emp_role:'Barista',       emp_schedule:'Afternoon', att_status:'Late'    },
          {emp_fname:'Nina',     emp_lname:'Torres',     emp_role:'Kitchen Staff', emp_schedule:'Afternoon', att_status:'Absent'  },
          {emp_fname:'Mark',     emp_lname:'Flores',     emp_role:'Barista',       emp_schedule:'Afternoon', att_status:'Absent'  },
          {emp_fname:'Claire',   emp_lname:'Villanueva', emp_role:'Cashier',       emp_schedule:'Morning',   att_status:'On Leave'},
          {emp_fname:'Diego',    emp_lname:'Ramos',      emp_role:'Kitchen Staff', emp_schedule:'Morning',   att_status:'Present' },
          {emp_fname:'Francesca',emp_lname:'Lugay',      emp_role:'Barista',       emp_schedule:'Morning',   att_status:'Present' },
          {emp_fname:'Blessie',  emp_lname:'Pamplona',   emp_role:'Cashier',       emp_schedule:'Morning',   att_status:'Present' },
        ],
        payroll_by_role: [
          {role:'Manager',total_net_salary:32000},{role:'Barista',total_net_salary:28000},
          {role:'Cashier',total_net_salary:24500},{role:'Kitchen Staff',total_net_salary:20500},
        ],
        payroll_summary: [
          {emp_fname:'Maria',    emp_lname:'Santos',     emp_role:'Barista',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:7500, payroll_status:'Paid'      },
          {emp_fname:'Jose',     emp_lname:'Reyes',      emp_role:'Cashier',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:6500, payroll_status:'Paid'      },
          {emp_fname:'Ana',      emp_lname:'Cruz',       emp_role:'Barista',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:7500, payroll_status:'Processing'},
          {emp_fname:'Carlo',    emp_lname:'Mendoza',    emp_role:'Kitchen Staff', payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:5800, payroll_status:'Pending'   },
          {emp_fname:'Liza',     emp_lname:'Bautista',   emp_role:'Cashier',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:6500, payroll_status:'Paid'      },
          {emp_fname:'Ramon',    emp_lname:'Garcia',     emp_role:'Barista',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:7500, payroll_status:'Pending'   },
          {emp_fname:'Nina',     emp_lname:'Torres',     emp_role:'Kitchen Staff', payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:5800, payroll_status:'Paid'      },
          {emp_fname:'Mark',     emp_lname:'Flores',     emp_role:'Barista',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:7500, payroll_status:'Processing'},
          {emp_fname:'Claire',   emp_lname:'Villanueva', emp_role:'Cashier',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:6500, payroll_status:'Pending'   },
          {emp_fname:'Diego',    emp_lname:'Ramos',      emp_role:'Kitchen Staff', payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:5800, payroll_status:'Paid'      },
          {emp_fname:'Francesca',emp_lname:'Lugay',      emp_role:'Barista',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:7500, payroll_status:'Paid'      },
          {emp_fname:'Blessie',  emp_lname:'Pamplona',   emp_role:'Manager',       payperiod_start:'2026-04-01',payperiod_end:'2026-04-15',net_salary:12000,payroll_status:'Processing'},
        ],
      };
      const ts = D.stat_cards.total_staff;
      renderStatCards(D.stat_cards);
      renderAttendanceChart(D.monthly_attendance, ts);
      renderTodaysStatus(D.todays_status);
      renderPayrollByRole(D.payroll_by_role);
      renderPayrollSummary(D.payroll_summary);
    }
  }

  loadDashboard();
</script>
</body>
</html>