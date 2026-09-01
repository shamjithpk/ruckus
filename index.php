<?php require_once dirname(__DIR__) . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#1e1b4b">
<title>Super Admin — WiFi Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',-apple-system,sans-serif;background:#0f0f1a;color:#e2e8f0;min-height:100vh;-webkit-font-smoothing:antialiased}

/* PIN OVERLAY */
.pin-screen{position:fixed;inset:0;background:linear-gradient(135deg,#1e1b4b,#312e81,#1e1b4b);display:flex;align-items:center;justify-content:center;z-index:9999;padding:20px}
.pin-card{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:28px;padding:40px 24px;width:100%;max-width:340px;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,0.5)}
.pin-card .lock{font-size:64px;margin-bottom:16px;display:block}
.pin-card h2{font-size:26px;font-weight:800;margin-bottom:6px;color:#fff}
.pin-card p{color:rgba(255,255,255,0.45);font-size:14px;margin-bottom:28px}
.pin-row{display:flex;flex-direction:column;gap:10px;margin-bottom:14px}
.pin-input{width:100%;background:rgba(255,255,255,0.08);border:2px solid rgba(255,255,255,0.15);border-radius:16px;padding:18px;color:#fff;font-size:32px;font-weight:700;letter-spacing:14px;text-align:center;outline:none;min-height:68px}
.pin-input:focus{border-color:#818cf8;background:rgba(255,255,255,0.12)}
.pin-go{width:100%;background:#4f46e5;border:none;border-radius:16px;color:#fff;padding:18px;font-size:18px;font-weight:800;cursor:pointer;min-height:60px;transition:background .15s;touch-action:manipulation}
.pin-go:active{background:#4338ca;transform:scale(0.97)}
.pin-error{color:#f87171;font-size:13px;margin-bottom:12px;display:none}

/* HEADER */
.sa-header{background:linear-gradient(135deg,#1e1b4b,#312e81);padding:0 20px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 20px rgba(0,0,0,0.4)}
.sa-brand{display:flex;align-items:center;gap:10px;font-size:17px;font-weight:800;color:#fff}
.sa-brand .badge{background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:8px;padding:3px 10px;font-size:11px;font-weight:700;color:#a5b4fc;letter-spacing:.5px}
.sa-logout{background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:rgba(255,255,255,0.7);border-radius:10px;padding:7px 14px;font-size:13px;cursor:pointer;font-weight:600}

/* TABS */
.tab-bar{background:#13131f;border-bottom:1px solid rgba(255,255,255,0.07);display:flex;overflow-x:auto;scrollbar-width:none;position:sticky;top:60px;z-index:99}
.tab-bar::-webkit-scrollbar{display:none}
.tab-btn{flex-shrink:0;background:none;border:none;color:rgba(255,255,255,0.4);padding:14px 20px;font-size:13px;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;display:flex;align-items:center;gap:6px}
.tab-btn.active{color:#818cf8;border-bottom-color:#818cf8}
.tab-btn i{font-size:16px}

/* CONTENT */
.tab-content{display:none;padding:20px 16px 100px;max-width:900px;margin:0 auto}
.tab-content.active{display:block}
@media(min-width:768px){.tab-content{padding:24px 24px 60px}}

/* CARDS */
.sa-card{background:#1a1a2e;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px;margin-bottom:12px}
.sa-card-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:rgba(255,255,255,0.35);margin-bottom:14px}

/* STAT GRID */
.stat-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:16px}
@media(min-width:480px){.stat-grid{grid-template-columns:repeat(4,1fr)}}
.stat-item{background:#1a1a2e;border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:16px;text-align:center}
.stat-item .n{font-size:28px;font-weight:800;line-height:1}
.stat-item .l{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-top:5px;color:rgba(255,255,255,0.35)}

/* USER CARDS */
.user-card{background:#1a1a2e;border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:14px 12px;margin-bottom:8px;border-left:4px solid rgba(255,255,255,0.1)}
.user-card.s-paid{border-left-color:#10b981}
.user-card.s-expiring{border-left-color:#f59e0b}
.user-card.s-unpaid,.user-card.s-expired{border-left-color:#ef4444}
.user-card.s-pending{border-left-color:#8b5cf6}
.u-name{font-size:15px;font-weight:700;color:#f1f5f9}
.u-phone{font-size:12px;color:rgba(255,255,255,0.35);margin-top:2px}
.u-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:30px;font-size:11px;font-weight:700;border:1.5px solid currentColor;margin-top:7px}
.b-paid{color:#10b981;background:rgba(16,185,129,.1)}
.b-expiring{color:#f59e0b;background:rgba(245,158,11,.1)}
.b-unpaid,.b-expired{color:#ef4444;background:rgba(239,68,68,.1)}
.b-pending{color:#8b5cf6;background:rgba(139,92,246,.1)}

/* BUTTONS */
.sa-btn{border:none;border-radius:10px;padding:7px 13px;font-size:12px;font-weight:700;cursor:pointer;transition:filter .15s,transform .1s;line-height:1;display:inline-flex;align-items:center;gap:5px}
.sa-btn:active{transform:scale(0.93)}
.btn-green{background:#10b981;color:#fff}
.btn-blue{background:#4f46e5;color:#fff}
.btn-yellow{background:rgba(245,158,11,.15);color:#f59e0b}
.btn-red{background:rgba(239,68,68,.15);color:#ef4444}
.btn-purple{background:rgba(139,92,246,.15);color:#8b5cf6}

/* FORM */
.sa-input{width:100%;background:rgba(255,255,255,0.06);border:2px solid rgba(255,255,255,0.1);border-radius:12px;padding:12px 14px;color:#f1f5f9;font-size:15px;outline:none;transition:border-color .2s;margin-bottom:10px;display:block}
.sa-input:focus{border-color:#818cf8}
.sa-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,0.35);display:block;margin-bottom:6px}
.add-form-wrap{background:#13131f;border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:20px;margin-bottom:16px;border-top:3px solid #4f46e5}

/* HISTORY */
.hist-item{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:#1a1a2e;border:1px solid rgba(255,255,255,0.06);border-radius:12px;margin-bottom:6px}
.hist-name{font-weight:700;font-size:14px;color:#f1f5f9}
.hist-meta{font-size:11px;color:rgba(255,255,255,0.3);margin-top:2px}
.hist-amt{font-weight:800;font-size:16px;color:#10b981}

/* SEARCH */
.search-wrap{position:relative;margin-bottom:14px}
.search-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.3);font-size:15px;pointer-events:none}
.search-input{width:100%;background:rgba(255,255,255,0.06);border:2px solid rgba(255,255,255,0.1);border-radius:12px;padding:11px 14px 11px 40px;color:#f1f5f9;font-size:14px;outline:none}
.search-input:focus{border-color:#818cf8}

/* SETTINGS */
.setting-row{display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid rgba(255,255,255,0.06)}
.setting-row:last-child{border-bottom:none}
.setting-label{font-weight:600;font-size:14px;color:#e2e8f0}
.setting-sub{font-size:12px;color:rgba(255,255,255,0.3);margin-top:2px}
.setting-input{background:rgba(255,255,255,0.06);border:2px solid rgba(255,255,255,0.1);border-radius:10px;padding:8px 12px;color:#f1f5f9;font-size:15px;font-weight:700;outline:none;width:110px;text-align:right}
.setting-input:focus{border-color:#818cf8}

/* TOAST */
.toast-box{position:fixed;top:72px;left:50%;transform:translateX(-50%);z-index:99999;padding:10px 24px;border-radius:30px;font-weight:700;font-size:14px;color:white;box-shadow:0 4px 24px rgba(0,0,0,0.3);white-space:nowrap;animation:fadeIn .2s ease;pointer-events:none}
@keyframes fadeIn{from{opacity:0;transform:translateX(-50%) translateY(-6px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}
@keyframes spin{to{transform:rotate(360deg)}}

/* BOTTOM NAV mobile */
.sa-bottom-nav{position:fixed;bottom:0;left:0;right:0;background:#13131f;border-top:1px solid rgba(255,255,255,0.07);display:grid;grid-template-columns:repeat(4,1fr);z-index:200;padding-bottom:env(safe-area-inset-bottom)}
@media(min-width:768px){.sa-bottom-nav{display:none}}
.sa-nav-btn{border:none;background:none;color:rgba(255,255,255,0.3);padding:10px 4px 12px;font-size:10px;font-weight:600;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:3px;text-transform:uppercase;letter-spacing:.3px;transition:color .15s}
.sa-nav-btn i{font-size:22px}
.sa-nav-btn.active{color:#818cf8}

/* Desktop tab bar hide on mobile handled by tab-bar scroll */
</style>
</head>
<body>

<!-- PIN SCREEN -->
<div id="pinScreen" class="pin-screen">
    <div class="pin-card">
        <span class="lock">🔐</span>
        <h2>Super Admin</h2>
        <p>Enter your PIN to access the dashboard</p>
        <div class="pin-row">
            <input type="password" id="pinInput" class="pin-input" placeholder="••••" maxlength="6"
                onkeypress="if(event.key==='Enter')doLogin()">
            <button onclick="doLogin()" class="pin-go">🔓 Enter Dashboard</button>
        </div>
        <div id="pinError" class="pin-error">Wrong PIN — try again ❌</div>
        <a href="/wifi/" style="color:rgba(255,255,255,0.25);font-size:13px;text-decoration:none;margin-top:8px;display:block">← Back to Admin</a>
    </div>
</div>

<!-- DASHBOARD (hidden until PIN ok) -->
<div id="dashboard" style="display:none">

    <!-- Header -->
    <header class="sa-header">
        <div class="sa-brand">
            <i class="bi bi-shield-lock-fill" style="color:#818cf8;font-size:20px"></i>
            WiFi Manager
            <span class="badge">SUPER ADMIN</span>
        </div>
        <button class="sa-logout" onclick="doLogout()"><i class="bi bi-box-arrow-right"></i> Logout</button>
    </header>

    <!-- Tab Bar (desktop) -->
    <div class="tab-bar d-none d-md-flex" id="desktopTabBar">
        <button class="tab-btn active" onclick="switchTab('users',this)"><i class="bi bi-people-fill"></i> Users</button>
        <button class="tab-btn" onclick="switchTab('stats',this)"><i class="bi bi-bar-chart-fill"></i> Stats</button>
        <button class="tab-btn" onclick="switchTab('history',this)"><i class="bi bi-clock-history"></i> History</button>
        <button class="tab-btn" onclick="switchTab('settings',this)"><i class="bi bi-gear-fill"></i> Settings</button>
    </div>

    <!-- ── USERS TAB ── -->
    <div id="tab-users" class="tab-content active">

        <!-- Add User Form -->
        <div class="add-form-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                <span style="font-weight:800;font-size:16px;color:#f1f5f9">➕ Add New User</span>
            </div>
            <label class="sa-label">Full Name</label>
            <input type="text" id="addName" class="sa-input" placeholder="Enter name">
            <label class="sa-label">WhatsApp Number</label>
            <input type="tel" id="addWA" class="sa-input" placeholder="+966...">
            <label class="sa-label">Device Limit</label>
            <select id="addLimit" class="sa-input">
                <option value="1" selected>1 Device (Standard)</option>
                <option value="2">2 Devices</option>
                <option value="3">3 Devices</option>
                <option value="4">4 Devices</option>
            </select>
            <button onclick="doAddUser()" class="sa-btn btn-blue" style="width:100%;padding:13px;font-size:15px;border-radius:12px;justify-content:center">
                <i class="bi bi-person-plus-fill"></i> Save User
            </button>
        </div>

        <!-- Search -->
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" placeholder="Search users..." oninput="filterUsers(this.value)">
        </div>

        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:rgba(255,255,255,0.25);margin-bottom:10px" id="userCount">Users</div>
        <div id="userList"><div style="text-align:center;padding:50px;color:rgba(255,255,255,0.2)">Loading...</div></div>
    </div>

    <!-- ── STATS TAB ── -->
    <div id="tab-stats" class="tab-content">
        <div class="stat-grid">
            <div class="stat-item"><div class="n" id="s-total" style="color:#818cf8">—</div><div class="l">Total</div></div>
            <div class="stat-item"><div class="n" id="s-paid" style="color:#10b981">—</div><div class="l">Paid</div></div>
            <div class="stat-item"><div class="n" id="s-unpaid" style="color:#ef4444">—</div><div class="l">Unpaid</div></div>
            <div class="stat-item"><div class="n" id="s-pending" style="color:#8b5cf6">—</div><div class="l">Pending</div></div>
        </div>
        <div id="statsContent"><div style="text-align:center;padding:30px;color:rgba(255,255,255,0.2)">Loading...</div></div>
    </div>

    <!-- ── HISTORY TAB ── -->
    <div id="tab-history" class="tab-content">
        <div class="sa-card" style="margin-bottom:16px">
            <div class="sa-card-title">Filter by Month</div>
            <input type="month" id="histMonth" class="sa-input" style="margin-bottom:0"
                onchange="renderHistory()">
        </div>
        <div id="histTotal" style="font-size:22px;font-weight:800;color:#10b981;margin-bottom:14px"></div>
        <div id="histList"><div style="text-align:center;padding:30px;color:rgba(255,255,255,0.2)">Loading...</div></div>
    </div>

    <!-- ── SETTINGS TAB ── -->
    <div id="tab-settings" class="tab-content">
        <div class="sa-card">
            <div class="sa-card-title">App Settings</div>

            <div class="setting-row">
                <div><div class="setting-label">Monthly Fee</div><div class="setting-sub">SAR per month</div></div>
                <input type="number" id="set-fee" class="setting-input" min="1">
            </div>
            <div class="setting-row">
                <div><div class="setting-label">Billing Days</div><div class="setting-sub">Days per cycle</div></div>
                <input type="number" id="set-days" class="setting-input" min="1" max="365">
            </div>
            <div class="setting-row">
                <div><div class="setting-label">Admin PIN</div><div class="setting-sub">Super Admin PIN</div></div>
                <input type="password" id="set-pin" class="setting-input" maxlength="8" placeholder="••••">
            </div>
        </div>

        <div class="sa-card" style="margin-top:12px">
            <div class="sa-card-title">Admin Names</div>
            <div id="adminNamesList"></div>
            <button onclick="addAdminName()" class="sa-btn btn-blue" style="margin-top:10px">
                <i class="bi bi-plus-lg"></i> Add Admin
            </button>
        </div>

        <button onclick="saveSettings()" class="sa-btn btn-green"
            style="width:100%;padding:15px;font-size:16px;border-radius:14px;justify-content:center;margin-top:16px">
            <i class="bi bi-check-lg"></i> Save All Settings
        </button>
    </div>

    <!-- Bottom Nav (mobile) -->
    <nav class="sa-bottom-nav">
        <button class="sa-nav-btn active" onclick="switchTab('users',this)" data-tab="users">
            <i class="bi bi-people-fill"></i>Users
        </button>
        <button class="sa-nav-btn" onclick="switchTab('stats',this)" data-tab="stats">
            <i class="bi bi-bar-chart-fill"></i>Stats
        </button>
        <button class="sa-nav-btn" onclick="switchTab('history',this)" data-tab="history">
            <i class="bi bi-clock-history"></i>History
        </button>
        <button class="sa-nav-btn" onclick="switchTab('settings',this)" data-tab="settings">
            <i class="bi bi-gear-fill"></i>Settings
        </button>
    </nav>

</div>

<!-- Firebase -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-database-compat.js"></script>
<script>
firebase.initializeApp({
    apiKey:            "<?php echo FIREBASE_API_KEY; ?>",
    authDomain:        "<?php echo FIREBASE_AUTH_DOMAIN; ?>",
    databaseURL:       "<?php echo FIREBASE_DATABASE_URL; ?>",
    projectId:         "<?php echo FIREBASE_PROJECT_ID; ?>",
    storageBucket:     "<?php echo FIREBASE_STORAGE_BUCKET; ?>",
    messagingSenderId: "<?php echo FIREBASE_MESSAGING_SENDER_ID; ?>",
    appId:             "<?php echo FIREBASE_APP_ID; ?>"
});
const db = firebase.database();

const DEF = {
    fee: <?php echo DEFAULT_FEE; ?>,
    days: <?php echo DEFAULT_BILLING_DAYS; ?>,
    pin: "<?php echo DEFAULT_PIN; ?>",
    admins: <?php echo DEFAULT_ADMINS; ?>
};

// ─── STATE ───
let st = { users:[], history:[], settings:{...DEF}, search:'', processingPayment: null };

// ─── PIN ───
function doLogin() {
    const pin = document.getElementById('pinInput').value;
    const correct = st.settings.pin || DEF.pin;
    if (pin === correct) {
        localStorage.setItem('sa_time', Date.now());
        document.getElementById('pinScreen').style.display = 'none';
        document.getElementById('dashboard').style.display = 'block';
        initFirebase();
    } else {
        document.getElementById('pinInput').style.borderColor = '#ef4444';
        document.getElementById('pinError').style.display = 'block';
        setTimeout(() => { document.getElementById('pinInput').style.borderColor = ''; }, 700);
    }
}

function doLogout() {
    localStorage.removeItem('sa_time');
    location.href = '/wifi/';
}

// ─── AUTO LOGIN ───
function checkAutoLogin() {
    const t = parseInt(localStorage.getItem('sa_time') || '0');
    if (t && Date.now() - t < 30 * 60 * 1000) {
        document.getElementById('pinScreen').style.display = 'none';
        document.getElementById('dashboard').style.display = 'block';
        initFirebase();
    }
}

// ─── FIREBASE ───
function initFirebase() {
    db.ref('settings').on('value', snap => {
        if (snap.val()) st.settings = { ...DEF, ...snap.val() };
        if (typeof st.settings.admins === 'string') st.settings.admins = JSON.parse(st.settings.admins);
        renderAll();
    });
    db.ref('users').on('value', snap => {
        st.users = [];
        if (snap.val()) Object.keys(snap.val()).forEach(k => st.users.push({ id:k, ...snap.val()[k] }));
        renderAll();
    });
    db.ref('paymentHistory').on('value', snap => {
        st.history = [];
        if (snap.val()) Object.keys(snap.val()).forEach(k => st.history.push({ id:k, ...snap.val()[k] }));
        renderAll();
    });

    // Set default month filter
    const now = new Date();
    document.getElementById('histMonth').value = now.toISOString().slice(0,7);
}

// ─── HELPERS ───
function genId() { return Date.now().toString(36) + Math.random().toString(36).slice(2,7); }
function getDaysLeft(d) {
    if (!d) return -999;
    const n = new Date(); n.setHours(0,0,0,0);
    const e = new Date(d); e.setHours(0,0,0,0);
    return Math.ceil((e-n)/864e5);
}
function getPending(u) {
    let p = u.unpaidMonths || 0;
    const d = getDaysLeft(u.expiryDate);
    if (d < 0) p += Math.floor(Math.abs(d)/30);
    return p;
}
function getStatus(u) {
    const p = getPending(u);
    if (p > 0) return { cls:'pending', label:`⏳ ${p} mo. due` };
    if (u.status === 'unpaid' || !u.expiryDate) return { cls:'unpaid', label:'❌ Unpaid' };
    const d = getDaysLeft(u.expiryDate);
    if (d <= 0)  return { cls:'expired',  label:'❌ Expired' };
    if (d <= 7)  return { cls:'expiring', label:`⚠️ ${d}d left` };
    return { cls:'paid', label:'✅ Paid' };
}
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); }
function monthLabel() { return new Date().toLocaleDateString('en-US',{month:'long',year:'numeric'}); }
function toast(msg, type) {
    const el = document.createElement('div');
    el.className = 'toast-box';
    el.style.background = type === 'error' ? '#ef4444' : '#10b981';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 2600);
}

// ─── TABS ───
function switchTab(name, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    document.querySelectorAll('.tab-btn, .sa-nav-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll(`[data-tab="${name}"], .tab-btn`).forEach(b => {
        if (b.getAttribute('data-tab') === name || b.textContent.toLowerCase().includes(name)) b.classList.add('active');
    });
    if (btn) { btn.classList.add('active'); }
    if (name === 'stats')   renderStats();
    if (name === 'history') renderHistory();
    if (name === 'settings') renderSettings();
}

// ─── RENDER ALL ───
function renderAll() {
    renderUsers();
    renderStats();
    renderHistory();
    renderSettings();
}

// ─── USERS ───
function filterUsers(q) { st.search = q.toLowerCase(); renderUsers(); }

function renderUsers() {
    let users = st.users;
    if (st.search) users = users.filter(u =>
        (u.name||'').toLowerCase().includes(st.search) || (u.whatsapp||'').includes(st.search)
    );
    const order = {pending:0,expired:1,unpaid:1,expiring:2,paid:3};
    users = [...users].sort((a,b) => (order[getStatus(a).cls]||3) - (order[getStatus(b).cls]||3));

    const cnt = document.getElementById('userCount');
    if (cnt) cnt.textContent = `${users.length} User${users.length !== 1 ? 's' : ''}`;

    const el = document.getElementById('userList');
    if (!el) return;

    if (!users.length) {
        el.innerHTML = `<div style="text-align:center;padding:50px;color:rgba(255,255,255,0.2)">
            <div style="font-size:40px;opacity:.3">👥</div><div style="margin-top:8px">No users</div></div>`;
        return;
    }

    el.innerHTML = users.map(u => {
        const si = getStatus(u);
        const d  = getDaysLeft(u.expiryDate);
        const p  = getPending(u);
        const fee = st.settings.monthlyFee || DEF.fee;
        const due = (p + (u.status === 'unpaid' || d <= 0 ? 1 : 0)) * fee;
        const lp  = st.history.filter(h => h.userId === u.id).pop();

        return `
        <div class="user-card s-${si.cls}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px">
                <div style="flex:1;min-width:0">
                    <div class="u-name text-truncate">${u.name}</div>
                    <div class="u-phone">📱 ${u.whatsapp || '—'}</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-top:6px">
                        <span class="u-badge b-${si.cls}">${si.label}</span>
                        ${u.deviceLimit > 1 ? `<span class="u-badge" style="color:#a5b4fc;background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.15)">📱 ${u.deviceLimit} Devices</span>` : ''}
                        ${si.cls === 'paid'
                            ? `<span style="font-size:12px;color:rgba(255,255,255,0.3)">${d}d left</span>`
                            : due > 0 ? `<span style="font-size:12px;font-weight:700;color:#ef4444">${due} SAR</span>` : ''}
                    </div>
                    ${p >= 1 ? `<div style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);color:#f59e0b;border-radius:8px;padding:4px 10px;font-size:12px;margin-top:6px;display:inline-block">
                        ⚠️ ${p} months pending — ${(p+1)*fee} SAR</div>` : ''}
                    ${lp ? `<div style="font-size:11px;color:rgba(255,255,255,0.25);margin-top:5px">
                        Last: ${lp.collectedBy} · ${fmtDate(lp.paidDate)}
                        <span style="background:${lp.period===monthLabel()?'#10b981':'#4f46e5'};color:white;border-radius:5px;padding:1px 7px;font-size:10px;font-weight:700;margin-left:3px">${lp.period||'—'}</span>
                    </div>` : ''}
                </div>
                <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-end;flex-shrink:0">
                    ${st.processingPayment === u.id
                        ? `<button disabled class="sa-btn btn-green" style="opacity:0.6;cursor:not-allowed">⏳ ...</button>`
                        : `<button onclick="markPaid('${u.id}')" class="sa-btn btn-green"><i class="bi bi-cash"></i> PAY</button>`
                    }
                    <button onclick="sendWA('${u.id}')" class="sa-btn btn-purple"><i class="bi bi-whatsapp"></i></button>
                    <button onclick="editUser('${u.id}')" class="sa-btn btn-yellow"><i class="bi bi-pencil"></i></button>
                    <button onclick="delUser('${u.id}')" class="sa-btn btn-red"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>`;
    }).join('');
}

// ─── ADD USER ───
async function doAddUser() {
    const name = document.getElementById('addName').value.trim();
    const wa   = document.getElementById('addWA').value.trim();
    const limit = parseInt(document.getElementById('addLimit').value) || 1;
    if (!name || !wa) { toast('Fill all fields', 'error'); return; }
    toast('Adding user...', 'info');
    let passKey = '';
    try {
        const fd = new FormData();
        fd.append('action','create_pass'); fd.append('fullname',name);
        fd.append('duration', st.settings.billingDays || DEF.days);
        fd.append('limitnumber', limit);
        const r = await fetch('https://api.wifistore.online/ruckus_api.php',{method:'POST',body:fd});
        const d = await r.json();
        if (d.status === 'success') passKey = d.pass_key;
    } catch(e) {}
    const today = new Date().toISOString().split('T')[0];
    const exp   = new Date(today); exp.setDate(exp.getDate() + (st.settings.billingDays || DEF.days));
    db.ref('users/' + genId()).set({ name, whatsapp:wa, status:'paid', paymentDate:today,
        expiryDate:exp.toISOString().split('T')[0], wifiPass:passKey, unpaidMonths:0, deviceLimit: limit, createdAt:new Date().toISOString() });
    document.getElementById('addName').value = '';
    document.getElementById('addWA').value   = '';
    toast(passKey ? `${name} added — Pass: ${passKey} ✅` : `${name} added ✅`);
}

// ─── MARK PAID ───
async function markPaid(uid) {
    if (st.processingPayment) return;
    const u = st.users.find(x => x.id === uid); if (!u) return;
    try {
        st.processingPayment = uid;
        renderUsers();
        toast(`Processing ${u.name}...`, 'info');

        const daysLeft = getDaysLeft(u.expiryDate);
        const carryForward = daysLeft > 0 ? daysLeft : 0;
        const billingDays = st.settings.billingDays || DEF.days;
        const newDuration = billingDays + carryForward;

        // Delete old pass from router first to avoid key conflict if keeping the same key
        if (u.wifiPass) {
            try {
                const delFd = new FormData();
                delFd.append('action', 'delete_pass');
                delFd.append('pass_key', u.wifiPass);
                await fetch('https://api.wifistore.online/ruckus_api.php', { method: 'POST', body: delFd });
            } catch(delErr) { console.error('Error deleting old pass:', delErr); }
        }

        let passKey = '';
        try {
            const fd = new FormData();
            fd.append('action','create_pass'); fd.append('fullname',u.name);
            fd.append('duration', newDuration);
            fd.append('limitnumber', u.deviceLimit || 1);
            if (u.wifiPass) {
                fd.append('pass_key', u.wifiPass);
            }
            const r = await fetch('https://api.wifistore.online/ruckus_api.php',{method:'POST',body:fd});
            const d = await r.json();
            if (d.status === 'success') {
                passKey = d.pass_key;
            }
        } catch(e) {}
        const today = new Date().toISOString().split('T')[0];
        const exp   = new Date(today); exp.setDate(exp.getDate() + newDuration);
        const upd = { status:'paid', paymentDate:today, expiryDate:exp.toISOString().split('T')[0],
            unpaidMonths: Math.max(0,(u.unpaidMonths||0)-1) };
        if (passKey) upd.wifiPass = passKey;
        await db.ref('users/' + uid).update(upd);
        await db.ref('paymentHistory/' + genId()).set({ userId:uid, userName:u.name,
            amount:st.settings.monthlyFee || DEF.fee, paidDate:today,
            collectedBy:'Super Admin', period:monthLabel(), timestamp:new Date().toISOString() });
        toast(`${u.name} — Paid ✅`);
    } catch(e) {
        console.error(e);
        toast('Error processing payment', 'error');
    } finally {
        st.processingPayment = null;
        renderUsers();
    }
}

// ─── DELETE ───
function delUser(id) {
    const u = st.users.find(x => x.id === id);
    if (!confirm(`Delete "${u?.name}"? This cannot be undone.`)) return;
    db.ref('users/' + id).remove();
    toast('User deleted', 'error');
}

// ─── WHATSAPP ───
function sendWA(uid) {
    const u = st.users.find(x => x.id === uid); if (!u) return;
    const fee = st.settings.monthlyFee || DEF.fee;
    const d   = getDaysLeft(u.expiryDate);
    const p   = getPending(u);
    const tot = p + (u.status === 'unpaid' || d <= 0 ? 1 : 0);
    let msg = u.status === 'paid' && u.wifiPass
        ? `Hi ${u.name},\n\nYour WiFi payment of *${fee} SAR* is received. Thank you! 🙏\n\n🔑 *New WiFi Pass:* ${u.wifiPass}\n_(Validity: ${st.settings.billingDays || DEF.days} Days)_`
        : tot >= 2
            ? `Hi ${u.name},\n\n⚠️ You have *${tot} months* pending.\n💰 Total Due: *${tot*fee} SAR*\n\nPlease clear your dues.\n\nThank you! 🙏`
            : `Hi ${u.name},\n\nYour WiFi has expired. Please pay *${fee} SAR*.\n\nThank you! 🙏`;
    if (u.wifiPass && u.status !== 'paid') msg += `\n\n🔑 Current Pass: ${u.wifiPass}`;
    window.open(`https://wa.me/${(u.whatsapp||'').replace(/[^0-9]/g,'')}?text=${encodeURIComponent(msg)}`, '_blank');
}

// ─── EDIT ───
function editUser(uid) {
    const u = st.users.find(x => x.id === uid); if (!u) return;
    document.getElementById('editModal')?.remove();
    document.body.insertAdjacentHTML('beforeend', `
    <div id="editModal" style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9999;
        display:flex;align-items:flex-end;justify-content:center;backdrop-filter:blur(4px)">
        <div style="background:#1a1a2e;width:100%;max-width:500px;border-radius:24px 24px 0 0;padding:24px 20px;border-top:3px solid #4f46e5">
            <div style="width:40px;height:4px;background:rgba(255,255,255,.1);border-radius:2px;margin:0 auto 18px"></div>
            <div style="font-weight:800;font-size:17px;color:#f1f5f9;margin-bottom:16px">✏️ Edit User</div>
            <label class="sa-label">Name</label>
            <input type="text" id="eN" class="sa-input" value="${u.name}">
            <label class="sa-label">WhatsApp</label>
            <input type="tel" id="eW" class="sa-input" value="${u.whatsapp||''}">
            <label class="sa-label">Expiry Date</label>
            <input type="date" id="eE" class="sa-input" value="${u.expiryDate||''}">
            <label class="sa-label">Payment Date</label>
            <input type="date" id="eP" class="sa-input" value="${u.paymentDate||''}">
            <label class="sa-label">Device Limit</label>
            <select id="eL" class="sa-input">
                <option value="1" ${u.deviceLimit == 1 || !u.deviceLimit ? 'selected' : ''}>1 Device (Standard)</option>
                <option value="2" ${u.deviceLimit == 2 ? 'selected' : ''}>2 Devices</option>
                <option value="3" ${u.deviceLimit == 3 ? 'selected' : ''}>3 Devices</option>
                <option value="4" ${u.deviceLimit == 4 ? 'selected' : ''}>4 Devices</option>
            </select>
            <div style="display:flex;gap:10px;margin-top:6px">
                <button onclick="document.getElementById('editModal').remove()"
                    style="flex:1;padding:13px;background:rgba(255,255,255,.06);border:none;border-radius:12px;color:rgba(255,255,255,.5);font-size:15px;font-weight:700;cursor:pointer">Cancel</button>
                <button onclick="saveEdit('${uid}')"
                    style="flex:2;padding:13px;background:#4f46e5;border:none;border-radius:12px;color:white;font-size:15px;font-weight:700;cursor:pointer">Save Changes</button>
            </div>
        </div>
    </div>`);
}

function saveEdit(uid) {
    const name = document.getElementById('eN')?.value.trim();
    const wa   = document.getElementById('eW')?.value.trim();
    const exp  = document.getElementById('eE')?.value;
    const pay  = document.getElementById('eP')?.value;
    const limit = parseInt(document.getElementById('eL')?.value) || 1;
    if (!name) { toast('Name required', 'error'); return; }
    const status = exp && new Date(exp) > new Date() ? 'paid' : 'unpaid';
    db.ref('users/' + uid).update({ name, whatsapp:wa||'', expiryDate:exp||'', paymentDate:pay||'', status, deviceLimit: limit });
    document.getElementById('editModal').remove();
    toast(`${name} updated ✅`);
}

// ─── STATS ───
function renderStats() {
    const fee     = st.settings.monthlyFee || DEF.fee;
    const monthSt = new Date().toISOString().slice(0,7);
    const thisMo  = st.history.filter(p => (p.paidDate||'').startsWith(monthSt));
    const admins  = st.settings.admins || DEF.admins;
    const paid    = st.users.filter(u => u.status === 'paid' && getDaysLeft(u.expiryDate) > 0).length;
    const unpaid  = st.users.length - paid;
    const pending = st.users.filter(u => getPending(u) > 0).length;
    const allTime = st.history.reduce((s,p) => s + (p.amount||0), 0);
    const thisTot = thisMo.reduce((s,p) => s + (p.amount||0), 0);
    const pend$   = st.users.reduce((s,u) => {
        const d = getDaysLeft(u.expiryDate);
        if (u.status === 'unpaid' || d <= 0) s += fee;
        s += getPending(u) * fee;
        return s;
    }, 0);

    const el = (id,v) => { const e = document.getElementById(id); if(e) e.textContent = v; };
    el('s-total', st.users.length); el('s-paid', paid); el('s-unpaid', unpaid); el('s-pending', pending);

    const adminRows = admins.map(n => {
        const amt = thisMo.filter(p => p.collectedBy === n).reduce((s,p) => s+(p.amount||0), 0);
        return `<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;
            background:rgba(255,255,255,.04);border-radius:10px;margin-bottom:6px">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:28px;height:28px;background:rgba(79,70,229,.2);border-radius:8px;
                    display:flex;align-items:center;justify-content:center;font-size:13px">👤</div>
                <span style="font-weight:600;font-size:14px">${n}</span>
            </div>
            <span style="font-weight:800;color:#10b981">${amt} SAR</span>
        </div>`;
    }).join('');

    const sc = document.getElementById('statsContent');
    if (!sc) return;
    sc.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
            <div style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);border-radius:14px;padding:16px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#10b981;letter-spacing:.5px">This Month</div>
                <div style="font-size:28px;font-weight:800;color:#10b981;margin-top:4px">${thisTot} <span style="font-size:13px;font-weight:400">SAR</span></div>
            </div>
            <div style="background:rgba(79,70,229,.1);border:1px solid rgba(79,70,229,.2);border-radius:14px;padding:16px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#818cf8;letter-spacing:.5px">All Time</div>
                <div style="font-size:28px;font-weight:800;color:#818cf8;margin-top:4px">${allTime} <span style="font-size:13px;font-weight:400">SAR</span></div>
            </div>
        </div>
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);border-radius:14px;padding:16px;margin-bottom:14px">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#ef4444;letter-spacing:.5px">Total Pending</div>
            <div style="font-size:28px;font-weight:800;color:#ef4444;margin-top:4px">${pend$} <span style="font-size:13px;font-weight:400">SAR</span></div>
        </div>
        <div class="sa-card"><div class="sa-card-title">Per Admin — This Month</div>${adminRows}</div>`;
}

// ─── HISTORY ───
function renderHistory() {
    const m  = document.getElementById('histMonth')?.value || new Date().toISOString().slice(0,7);
    const list = [...st.history].filter(p => (p.paidDate||'').startsWith(m)).reverse();
    const tot  = list.reduce((s,p) => s+(p.amount||0), 0);
    const tEl  = document.getElementById('histTotal');
    if (tEl) tEl.innerHTML = `<span style="font-size:13px;color:rgba(255,255,255,.3);font-weight:400">Collected: </span>${tot} SAR`;
    const el = document.getElementById('histList');
    if (!el) return;
    if (!list.length) { el.innerHTML = `<div style="text-align:center;padding:40px;color:rgba(255,255,255,.2)">No payments this month</div>`; return; }
    el.innerHTML = list.map(p => `
        <div class="hist-item">
            <div>
                <div class="hist-name">${p.userName || '—'}</div>
                <div class="hist-meta">${p.collectedBy || '?'} · ${fmtDate(p.paidDate)}</div>
            </div>
            <div class="hist-amt">${p.amount || 0} SAR</div>
        </div>`).join('');
}

// ─── SETTINGS ───
function renderSettings() {
    const s = st.settings;
    const f = document.getElementById('set-fee');  if(f) f.value = s.monthlyFee || DEF.fee;
    const d = document.getElementById('set-days'); if(d) d.value = s.billingDays || DEF.days;
    const p = document.getElementById('set-pin');  if(p) p.placeholder = '••••';

    const admins = s.admins || DEF.admins;
    const el = document.getElementById('adminNamesList');
    if (el) el.innerHTML = admins.map((n,i) => `
        <div style="display:flex;gap:8px;margin-bottom:8px">
            <input type="text" value="${n}" id="aName_${i}" class="sa-input" style="margin-bottom:0;flex:1">
            <button onclick="removeAdmin(${i})" class="sa-btn btn-red" style="padding:10px 12px">
                <i class="bi bi-trash"></i>
            </button>
        </div>`).join('');
}

function addAdminName() {
    const admins = [...(st.settings.admins || DEF.admins), 'New Admin'];
    st.settings.admins = admins;
    renderSettings();
}

function removeAdmin(i) {
    const admins = [...(st.settings.admins || DEF.admins)];
    admins.splice(i, 1);
    st.settings.admins = admins;
    renderSettings();
}

function saveSettings() {
    const fee  = parseInt(document.getElementById('set-fee')?.value) || DEF.fee;
    const days = parseInt(document.getElementById('set-days')?.value) || DEF.days;
    const pin  = document.getElementById('set-pin')?.value.trim();
    const admins = (st.settings.admins || DEF.admins).map((_, i) =>
        document.getElementById(`aName_${i}`)?.value.trim() || _
    ).filter(Boolean);
    const upd = { monthlyFee:fee, billingDays:days, admins, currency:'SAR' };
    if (pin) upd.pin = pin;
    db.ref('settings').update(upd);
    toast('Settings saved ✅');
}

// ─── INIT ───
document.addEventListener('DOMContentLoaded', checkAutoLogin);
document.getElementById('pinInput').addEventListener('keypress', e => { if(e.key==='Enter') doLogin(); });
</script>
</body>
</html>
