<?php
session_start();
// If already logged in, redirect to their dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $redirect = match((int)($_SESSION['dept_id'] ?? 0)) {
        1 => '/hrm_module/pages/dashboard_hr.php',
        2 => '/hrm_module/pages/dashboard_inv.php',
        default => '/hrm_module/pages/dashboard_hr.php'
    };
    header("Location: $redirect");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Code Latte – Login</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:      #f5f0e8;
      --card:    #faf7f2;
      --border:  #e3ddd2;
      --text:    #2c2416;
      --muted:   #8a7f6e;
      --accent:  #5c4a1e;
      --gold:    #c49a2b;
      --error:   #b84040;
      --radius:  14px;
      --shadow:  0 8px 40px rgba(80,60,20,.12);
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      /* subtle grain texture */
      background-image: radial-gradient(ellipse at 20% 50%, rgba(196,154,43,.08) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 20%, rgba(92,74,30,.06) 0%, transparent 50%);
    }

    .login-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 28px;
      width: 100%;
      max-width: 420px;
    }

    /* ── LOGO ── */
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .brand-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      overflow: hidden;
      padding: 0;
      background: var(--accent); /* fallback bg if image fails */
      flex-shrink: 0;
    }
    .brand-icon img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      border-radius: 12px;
    }
    .brand-text .name {
      font-family: 'DM Serif Display', serif;
      font-size: 22px; line-height: 1.1;
    }
    .brand-text .sub {
      font-size: 12px; color: var(--muted);
    }

    /* ── CARD ── */
    .login-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 36px 40px;
      width: 100%;
      box-shadow: var(--shadow);
      animation: fadeUp .35s ease;
    }
    @keyframes fadeUp {
      from { opacity:0; transform: translateY(16px); }
      to   { opacity:1; transform: translateY(0); }
    }

    .login-card h2 {
      font-family: 'DM Serif Display', serif;
      font-size: 22px; font-weight: 400;
      margin-bottom: 6px;
    }
    .login-card .subtitle {
      font-size: 13px; color: var(--muted);
      margin-bottom: 28px;
    }

    /* ── FORM ── */
    .form-group {
      display: flex; flex-direction: column; gap: 6px;
      margin-bottom: 16px;
    }
    .form-group label {
      font-size: 11px; font-weight: 600;
      letter-spacing: .06em; text-transform: uppercase;
      color: var(--muted);
    }
    .form-group input {
      padding: 11px 14px;
      border: 1px solid var(--border);
      border-radius: 8px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 14px; color: var(--text);
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .form-group input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(92,74,30,.08);
    }
    .form-group input::placeholder { color: #bbb5a8; }
    .form-group input.error-input  { border-color: var(--error); }

    /* password wrapper */
    .pass-wrap { position: relative; }
    .pass-wrap input { padding-right: 42px; width: 100%; }
    .toggle-pass {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      color: var(--muted); padding: 4px;
      display: flex; align-items: center; justify-content: center;
    }
    .toggle-pass:hover { color: var(--text); }
    .toggle-pass svg { width: 16px; height: 16px; }

    /* ── ERROR BOX ── */
    .error-box {
      display: none;
      background: #fceaea;
      border: 1px solid #f0d0d0;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      color: var(--error);
      margin-bottom: 16px;
      align-items: center;
      gap: 8px;
    }
    .error-box.show { display: flex; }
    .error-box svg  { width: 16px; height: 16px; flex-shrink: 0; }

    /* ── SUBMIT ── */
    .btn-login {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: none;
      background: var(--accent);
      font-family: 'DM Sans', sans-serif;
      font-size: 14px; font-weight: 600;
      color: #fff; cursor: pointer;
      margin-top: 8px;
      transition: background .15s, transform .1s, opacity .15s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-login:hover:not(:disabled) { background: #3e3010; transform: translateY(-1px); }
    .btn-login:disabled { opacity: .65; cursor: not-allowed; transform: none; }

    /* spinner */
    .spinner {
      display: none;
      width: 16px; height: 16px;
      border: 2px solid rgba(255,255,255,.4);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .6s linear infinite;
    }
    .btn-login.loading .spinner     { display: block; }
    .btn-login.loading .btn-label  { display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── FOOTER ── */
    .login-footer {
      font-size: 12px; color: var(--muted);
      text-align: center;
    }
  </style>
</head>
<body>

<div class="login-wrapper">

  <!-- BRAND -->
  <div class="brand">
    <div class="brand-icon">
      <img src="assets/images/code_latte.png" alt="Code Latte Logo" onerror="this.style.display='none'; this.parentElement.textContent='☕';"/>
    </div>
    <div class="brand-text">
      <div class="name">Code Latte</div>
      <div class="sub">Human Resource Management</div>
    </div>
  </div>

  <!-- LOGIN CARD -->
  <div class="login-card">
    <h2>Welcome back</h2>
    <p class="subtitle">Sign in to your account to continue</p>

    <!-- ERROR BOX -->
    <div class="error-box" id="error-box">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <span id="error-msg">Invalid credentials.</span>
    </div>

    <!-- USERNAME -->
    <div class="form-group">
      <label>Username</label>
      <input type="text" id="user_name" placeholder="Enter your username" autocomplete="username"/>
    </div>

    <!-- PASSWORD -->
    <div class="form-group">
      <label>Password</label>
      <div class="pass-wrap">
        <input type="password" id="user_password" placeholder="Enter your password" autocomplete="current-password"/>
        <button class="toggle-pass" type="button" onclick="togglePassword()" title="Show/hide password">
          <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- SUBMIT -->
    <button class="btn-login" id="login-btn" onclick="handleLogin()">
      <div class="spinner"></div>
      <span class="btn-label">Sign In</span>
    </button>
  </div>

  <!-- <div class="login-footer">
    &copy; <?= date('Y') ?> Code Latte. All rights reserved.
  </div> -->
</div>

<script>
  // ── TOGGLE PASSWORD VISIBILITY ──
  function togglePassword() {
    const input   = document.getElementById('user_password');
    const eyeIcon = document.getElementById('eye-icon');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    // swap icon
    eyeIcon.innerHTML = isHidden
      ? `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
         <line x1="1" y1="1" x2="23" y2="23"/>`
      : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  }

  // ── SHOW ERROR ──
  function showError(msg) {
    const box = document.getElementById('error-box');
    document.getElementById('error-msg').textContent = msg;
    box.classList.add('show');
    // shake inputs
    ['user_name','user_password'].forEach(id => {
      const el = document.getElementById(id);
      el.classList.add('error-input');
      setTimeout(() => el.classList.remove('error-input'), 2000);
    });
  }
  function hideError() {
    document.getElementById('error-box').classList.remove('show');
  }

  // ── LOGIN ──
  async function handleLogin() {
    const username = document.getElementById('user_name').value.trim();
    const password = document.getElementById('user_password').value;

    hideError();

    if (!username || !password) {
      showError('Please enter your username and password.');
      return;
    }

    // show loading state
    const btn = document.getElementById('login-btn');
    btn.classList.add('loading');
    btn.disabled = true;

    try {
      const res  = await fetch('/hrm_module/src/api/auth/login.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ user_name: username, user_password: password }),
      });
      const json = await res.json();

      if (json.success) {
        // redirect to their department dashboard
        window.location.href = json.redirect;
      } else {
        showError(json.error || 'Invalid credentials.');
      }
    } catch (err) {
      showError('Connection error. Please try again.');
      console.error('Login error:', err);
    } finally {
      btn.classList.remove('loading');
      btn.disabled = false;
    }
  }

  // ── ENTER KEY SUPPORT ──
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') handleLogin();
  });

  // ── CLEAR ERROR ON TYPE ──
  ['user_name','user_password'].forEach(id => {
    document.getElementById(id).addEventListener('input', hideError);
  });
</script>
</body>
</html>