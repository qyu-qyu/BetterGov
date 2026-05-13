<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>BetterGov</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

<style>

*{
  margin:0;
  padding:0;
  box-sizing:border-box;
}

:root{
  --bg:#f5f7fb;
  --white:#ffffff;
  --border:#e5e7eb;
  --text:#111827;
  --muted:#6b7280;
  --accent:#2563eb;
  --success:#10b981;
  --error:#ef4444;
  --radius:14px;
}

body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg);
  color:var(--text);
}

.page{
  min-height:100vh;
  display:grid;
  grid-template-columns:1fr 1fr;
}

.brand-panel{
  background:white;
  border-right:1px solid var(--border);
  padding:50px;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
}

.logo{
  display:flex;
  align-items:center;
  gap:12px;
}

.logo-mark{
  width:42px;
  height:42px;
  border-radius:12px;
  background:var(--accent);
  color:white;
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight:800;
  font-family:'Syne',sans-serif;
}

.logo-text{
  font-size:22px;
  font-family:'Syne',sans-serif;
  font-weight:700;
}

.logo-text span{
  color:var(--accent);
}

.brand-hero h1{
  font-size:52px;
  line-height:1.05;
  font-family:'Syne',sans-serif;
  margin-bottom:18px;
}

.brand-hero h1 span{
  color:var(--accent);
}

.brand-sub{
  color:var(--muted);
  line-height:1.7;
  max-width:420px;
  margin-bottom:40px;
}

.feature-list{
  display:flex;
  flex-direction:column;
  gap:14px;
}

.feature-item{
  display:flex;
  align-items:center;
  gap:12px;
  color:var(--muted);
}

.feature-icon{
  width:36px;
  height:36px;
  border-radius:10px;
  background:#eff6ff;
  display:flex;
  align-items:center;
  justify-content:center;
}

.brand-footer{
  color:var(--muted);
  font-size:14px;
}

.form-panel{
  display:flex;
  justify-content:center;
  align-items:center;
  padding:40px;
}

.form-card{
  width:100%;
  max-width:430px;
  background:white;
  border:1px solid var(--border);
  border-radius:24px;
  padding:34px;
  box-shadow:0 10px 30px rgba(0,0,0,0.04);
}

.tab-bar{
  display:flex;
  background:#f3f4f6;
  border-radius:12px;
  padding:4px;
  margin-bottom:28px;
}

.tab-btn{
  flex:1;
  border:none;
  background:none;
  padding:12px;
  border-radius:10px;
  cursor:pointer;
  font-size:14px;
  font-weight:600;
  transition:0.2s;
}

.tab-btn.active{
  background:white;
  color:var(--accent);
}

.form-heading{
  margin-bottom:24px;
}

.form-heading h1{
  font-size:28px;
  font-family:'Syne',sans-serif;
  margin-bottom:6px;
}

.form-heading p{
  color:var(--muted);
}

.field{
  margin-bottom:18px;
}

.field label{
  display:block;
  margin-bottom:8px;
  font-size:14px;
  font-weight:500;
}

.input-wrap{
  position:relative;
}

.input-icon{
  position:absolute;
  top:50%;
  left:14px;
  transform:translateY(-50%);
  color:#9ca3af;
}

input,
select{
  width:100%;
  border:1px solid var(--border);
  border-radius:12px;
  padding:13px 14px 13px 42px;
  font-size:14px;
  outline:none;
  transition:0.2s;
  background:white;
}

input:focus,
select:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 4px rgba(37,99,235,0.1);
}

.pw-toggle{
  position:absolute;
  right:14px;
  top:50%;
  transform:translateY(-50%);
  border:none;
  background:none;
  cursor:pointer;
}

.submit-btn{
  width:100%;
  border:none;
  background:var(--accent);
  color:white;
  padding:14px;
  border-radius:12px;
  cursor:pointer;
  font-weight:600;
  margin-top:6px;
  transition:0.2s;
}

.submit-btn:hover{
  opacity:0.9;
}

.divider{
  text-align:center;
  margin:22px 0;
  color:var(--muted);
  position:relative;
}

.divider::before,
.divider::after{
  content:"";
  position:absolute;
  top:50%;
  width:40%;
  height:1px;
  background:var(--border);
}

.divider::before{
  left:0;
}

.divider::after{
  right:0;
}

.field-error{
  color:var(--error);
  font-size:12px;
  margin-top:6px;
  min-height:16px;
}

.hidden{
  display:none;
}

.demo-box{
  background:#f9fafb;
  border:1px solid var(--border);
  border-radius:12px;
  padding:16px;
  margin-bottom:20px;
}

.demo-box-title{
  font-weight:700;
  margin-bottom:10px;
  color:var(--accent);
}

.demo-row{
  display:flex;
  justify-content:space-between;
  margin-bottom:8px;
  font-size:13px;
}

.demo-cred{
  cursor:pointer;
  background:white;
  border:1px solid var(--border);
  padding:4px 8px;
  border-radius:6px;
}

@media(max-width:768px){

  .page{
    grid-template-columns:1fr;
  }

  .brand-panel{
    display:none;
  }

  .form-panel{
    padding:20px;
  }

}

</style>
</head>

<body>

<div class="page">

  <div class="brand-panel">

    <div class="logo">
      <div class="logo-mark">B</div>
      <div class="logo-text">Better<span>Gov</span></div>
    </div>

    <div class="brand-hero">
      <h1>
        Public services,
        <span>reimagined</span>
        for citizens.
      </h1>

      <p class="brand-sub">
        A centralized platform connecting citizens,
        government offices and administrators.
      </p>

      <div class="feature-list">

        <div class="feature-item">
          <div class="feature-icon">📋</div>
          Track service requests
        </div>

        <div class="feature-item">
          <div class="feature-icon">📅</div>
          Book appointments online
        </div>

        <div class="feature-item">
          <div class="feature-icon">💳</div>
          Secure online payments
        </div>

      </div>
    </div>

    <div class="brand-footer">
      © {{ date('Y') }} BetterGov
    </div>

  </div>

  <div class="form-panel">

    <div class="form-card">

      <div class="tab-bar">

        <button
          id="tab-login"
          class="tab-btn active"
          onclick="switchTab('login')"
          type="button">
          Sign In
        </button>

        <button
          id="tab-register"
          class="tab-btn"
          onclick="switchTab('register')"
          type="button">
          Create Account
        </button>

      </div>

      <!-- LOGIN -->

      <div id="panel-login">

        <div class="form-heading">
          <h1>Welcome back</h1>
          <p>Sign in to your account</p>
        </div>

        <div class="demo-box">

          <div class="demo-box-title">
            Demo Credentials
          </div>

          <div class="demo-row">
            <span>Admin</span>
            <span class="demo-cred"
              onclick="fillLogin('admin@bettergov.lb','Admin@1234')">
              Use
            </span>
          </div>

          <div class="demo-row">
            <span>Citizen</span>
            <span class="demo-cred"
              onclick="fillLogin('citizen@bettergov.lb','Citizen@1234')">
              Use
            </span>
          </div>

        </div>

        <form>

          <div class="field">

            <label>Email</label>

            <div class="input-wrap">
              <span class="input-icon">✉️</span>

              <input
                type="email"
                id="l-email"
                placeholder="you@example.com">
            </div>

            <div class="field-error"></div>

          </div>

          <div class="field">

            <label>Password</label>

            <div class="input-wrap">

              <span class="input-icon">🔒</span>

              <input
                type="password"
                id="l-password"
                placeholder="Enter password">

              <button
                class="pw-toggle"
                type="button"
                onclick="togglePw('l-password',this)">
                👁️
              </button>

            </div>

          </div>

          <button class="submit-btn" type="submit">
            Sign In
          </button>

        </form>

      </div>

      <!-- REGISTER -->

      <div id="panel-register" class="hidden">

        <div class="form-heading">
          <h1>Create account</h1>
          <p>Join BetterGov platform</p>
        </div>

        <form>

          <div class="field">

            <label>Full Name</label>

            <div class="input-wrap">

              <span class="input-icon">👤</span>

              <input
                type="text"
                placeholder="Your name">

            </div>

          </div>

          <div class="field">

            <label>Email</label>

            <div class="input-wrap">

              <span class="input-icon">✉️</span>

              <input
                type="email"
                placeholder="you@example.com">

            </div>

          </div>

          <div class="field">

            <label>Password</label>

            <div class="input-wrap">

              <span class="input-icon">🔒</span>

              <input
                type="password"
                id="r-password"
                placeholder="Create password">

              <button
                class="pw-toggle"
                type="button"
                onclick="togglePw('r-password',this)">
                👁️
              </button>

            </div>

          </div>

          <button class="submit-btn" type="submit">
            Create Account
          </button>

        </form>

      </div>

    </div>

  </div>

</div>

<script>

function switchTab(tab){

  const loginPanel =
    document.getElementById('panel-login');

  const registerPanel =
    document.getElementById('panel-register');

  const loginTab =
    document.getElementById('tab-login');

  const registerTab =
    document.getElementById('tab-register');

  if(tab === 'login'){

    loginPanel.classList.remove('hidden');
    registerPanel.classList.add('hidden');

    loginTab.classList.add('active');
    registerTab.classList.remove('active');

  }else{

    registerPanel.classList.remove('hidden');
    loginPanel.classList.add('hidden');

    registerTab.classList.add('active');
    loginTab.classList.remove('active');

  }

}

function togglePw(id,btn){

  const input =
    document.getElementById(id);

  if(input.type === 'password'){
    input.type = 'text';
    btn.innerHTML = '🙈';
  }else{
    input.type = 'password';
    btn.innerHTML = '👁️';
  }

}

function fillLogin(email,password){

  document.getElementById('l-email').value =
    email;

  document.getElementById('l-password').value =
    password;

}

</script>

</body>
</html>