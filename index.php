<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Coffee Latte – Login</title>
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
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      top: -120px; right: -120px;
      width: 480px; height: 480px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(196,154,43,.13) 0%, transparent 70%);
      pointer-events: none;
    }
    body::after {
      content: '';
      position: fixed;
      bottom: -100px; left: -100px;
      width: 400px; height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(92,74,30,.10) 0%, transparent 70%);
      pointer-events: none;
    }

    .bg-pattern {
      position: fixed; inset: 0;
      background-image: radial-gradient(circle, rgba(92,74,30,.07) 1px, transparent 1px);
      background-size: 28px 28px;
      pointer-events: none;
    }

    .card {
      position: relative; z-index: 1;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 48px 44px;
      width: 420px;
      box-shadow: 0 8px 48px rgba(44,36,22,.12);
      animation: fadeUp .3s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .heading { margin-bottom: 32px; }
    .heading h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 26px; font-weight: 400;
      margin-bottom: 5px;
    }
    .heading p { font-size: 13px; color: var(--muted); }

    .form-group {
      display: flex; flex-direction: column; gap: 6px;
      margin-bottom: 18px;
    }
    .form-group label {
      font-size: 11px; font-weight: 600;
      letter-spacing: .06em; text-transform: uppercase;
      color: var(--muted);
    }

    .input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-icon {
      position: absolute; left: 12px;
      width: 15px; height: 15px;
      color: var(--muted);
      pointer-events: none;
      flex-shrink: 0;
    }
    .input-wrap input {
      width: 100%;
      padding: 11px 44px 11px 38px;
      border: 1px solid var(--border);
      border-radius: 9px;
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px; color: var(--text);
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .input-wrap input::placeholder { color: var(--muted); }
    .input-wrap input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(92,74,30,.08);
    }

    .eye-btn {
      position: absolute;
      right: 12px;
      background: none;
      border: none;
      cursor: pointer;
      color: var(--muted);
      font-size: 12px;
      font-family: 'DM Sans', sans-serif;
      font-weight: 500;
      padding: 4px 6px;
      border-radius: 4px;
      transition: color .12s, background .12s;
      user-select: none;
      line-height: 1;
    }
    .eye-btn:hover {
      color: var(--accent);
      background: rgba(92,74,30,.06);
    }

    .forgot-wrap {
      text-align: right;
      margin-bottom: 26px;
      margin-top: -10px;
    }
    .forgot-link {
      font-size: 12px; color: var(--accent); text-decoration: none;
      font-weight: 500; transition: opacity .12s;
    }
    .forgot-link:hover { opacity: .7; }

    .error-msg {
      display: none;
      background: #fceaea; border: 1px solid #f0d0d0;
      border-radius: 8px; padding: 10px 14px;
      font-size: 12px; color: #b84040;
      margin-bottom: 16px;
      align-items: center; gap: 8px;
    }
    .error-msg.show { display: flex; }

    .btn-login {
      width: 100%; padding: 13px;
      border-radius: 9px; border: none;
      background: var(--accent);
      font-family: 'DM Sans', sans-serif;
      font-size: 14px; font-weight: 600;
      color: #fff; cursor: pointer;
      transition: background .15s, transform .1s, box-shadow .15s;
    }
    .btn-login:hover {
      background: #3e3010;
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(92,74,30,.25);
    }
    .btn-login:active { transform: translateY(0); }
    .btn-login:disabled {
      opacity: .6; cursor: not-allowed;
      transform: none;
    }

    .form-footer {
      margin-top: 20px; text-align: center;
      font-size: 11px; color: var(--muted);
    }
  </style>
</head>
<body>

<div class="bg-pattern"></div>

<div class="card">

  <div class="heading">
    <h1>Welcome to Coffee Latte</h1>
    <p>Sign in to access.</p>
  </div>

  <div class="error-msg" id="error-msg">
    ⚠ <span id="error-text">Invalid username or password.</span>
  </div>

  <!-- USERNAME -->
  <div class="form-group">
    <label>Username</label>
    <div class="input-wrap">
      <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="8" r="4"/>
        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
      </svg>
      <input type="text" id="username" placeholder="Enter your username" autocomplete="username"/>
    </div>
  </div>

  <!-- PASSWORD -->
  <div class="form-group">
    <label>Password</label>
    <div class="input-wrap">
      <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="11" width="18" height="11" rx="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
      <input type="password" id="password" placeholder="Enter your password" autocomplete="current-password"/>
      <button class="eye-btn" id="eye-btn" type="button">Show</button>
    </div>
  </div>

  <div class="forgot-wrap">
    <a href="#" class="forgot-link">Forgot password?</a>
  </div>

  <button class="btn-login" id="btn-login" onclick="handleLogin()">Sign In</button>

  <div class="form-footer">Coffee Latte &nbsp;·&nbsp; 2026</div>

</div>

<script>
  const pwdInput = document.getElementById('password');
  const eyeBtn   = document.getElementById('eye-btn');

  // ── SHOW / HIDE PASSWORD ──
  eyeBtn.addEventListener('click', function () {
    if (pwdInput.type === 'password') {
      pwdInput.type      = 'text';
      eyeBtn.textContent = 'Hide';
    } else {
      pwdInput.type      = 'password';
      eyeBtn.textContent = 'Show';
    }
  });

  // ── LOGIN ──
  async function handleLogin() {
    const username = document.getElementById('username').value.trim();
    const password = pwdInput.value;
    const errorBox = document.getElementById('error-msg');
    const errorTxt = document.getElementById('error-text');
    const btn      = document.getElementById('btn-login');

    errorBox.classList.remove('show');

    if (!username) {
      errorTxt.textContent = 'Please enter your username.';
      errorBox.classList.add('show'); return;
    }
    if (!password) {
      errorTxt.textContent = 'Please enter your password.';
      errorBox.classList.add('show'); return;
    }

    // Disable button while processing
    btn.disabled        = true;
    btn.textContent     = 'Signing in…';

    try {
      const res  = await fetch('src/api/login/login.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({
          user_name:     username,
          user_password: password
        })
      });

      const json = await res.json();

      if (json.success) {
        window.location.href = json.redirect;
      } else {
        errorTxt.textContent = json.error || 'Invalid username or password.';
        errorBox.classList.add('show');
        btn.disabled    = false;
        btn.textContent = 'Sign In';
      }

    } catch (err) {
      errorTxt.textContent = 'Something went wrong. Please try again.';
      errorBox.classList.add('show');
      btn.disabled    = false;
      btn.textContent = 'Sign In';
    }
  }

  // Allow Enter key to submit
  document.addEventListener('keydown', e => {
    if (e.key === 'Enter') handleLogin();
  });
</script>
</body>
</html>