<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BetterGov — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #0b0f1a; color: #f0f4ff; font-family: 'DM Sans', sans-serif; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
  h1  { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 800; }
  p   { color: #8892a4; font-size: 15px; }
  .badge { padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 500; text-transform: capitalize; }
  .badge-admin   { background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); }
  .badge-office  { background: rgba(59,158,255,0.1);  color: #63b7ff; border: 1px solid rgba(59,158,255,0.3); }
  .badge-citizen { background: rgba(52,211,153,0.1);  color: #34d399; border: 1px solid rgba(52,211,153,0.3); }
  a { color: #63b7ff; text-decoration: none; font-size: 14px; }
  a:hover { text-decoration: underline; }
</style>
</head>
<body>
  <span class="badge badge-{{ $role ?? 'citizen' }}">{{ $role ?? 'user' }} dashboard</span>
  <h1>🏛️ BetterGov</h1>
  <p>Dashboard for <strong>{{ $role ?? 'user' }}</strong> — coming soon.</p>
  <a href="{{ route('auth') }}">← Back to sign in</a>
</body>
</html>
