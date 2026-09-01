/**
 * WiFi Manager — app.js
 */

// ─── STATE ───
let state = {
    role: 'admin',
    adminName: '',
    users: [],
    paymentHistory: [],
    searchQuery: '',
    processingPayment: null,
    settings: {
        monthlyFee:  APP_DEFAULTS.fee,
        billingDays: APP_DEFAULTS.billingDays,
        currency:    APP_DEFAULTS.currency,
        pin:         APP_DEFAULTS.pin,
        admins:      APP_DEFAULTS.admins
    }
};

// ─── HELPERS ───
function genId() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
}

function getDaysLeft(expiryDate) {
    if (!expiryDate) return -999;
    const now = new Date(); now.setHours(0,0,0,0);
    const exp = new Date(expiryDate); exp.setHours(0,0,0,0);
    return Math.ceil((exp - now) / 864e5);
}

function getPendingMonths(user) {
    if (!user.expiryDate) return user.unpaidMonths || 0;
    let pending = user.unpaidMonths || 0;
    const days = getDaysLeft(user.expiryDate);
    if (days < 0) pending += Math.floor(Math.abs(days) / 30);
    return pending;
}

function getStatusInfo(user) {
    const pending = getPendingMonths(user);
    if (pending > 0) return { cls: 'pending', label: `⏳ ${pending} mo. due` };
    if (user.status === 'unpaid' || !user.expiryDate) return { cls: 'unpaid', label: '❌ Unpaid' };
    const days = getDaysLeft(user.expiryDate);
    if (days <= 0)  return { cls: 'expired',  label: '❌ Expired' };
    if (days <= 7)  return { cls: 'expiring', label: `⚠️ ${days}d left` };
    return { cls: 'paid', label: '✅ Paid' };
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}

function getCurrentMonthLabel() {
    return new Date().toLocaleDateString('en-US', { month:'long', year:'numeric' });
}

function setEl(id, v) {
    const e = document.getElementById(id);
    if (e) e.textContent = v;
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── TOAST ───
function showToast(msg, type) {
    const bg = type === 'error' ? '#ef4444' : '#10b981';
    const el = document.createElement('div');
    el.style.cssText = `position:fixed;top:74px;left:50%;transform:translateX(-50%);z-index:99999;
        padding:10px 24px;border-radius:30px;font-weight:700;font-size:14px;
        background:${bg};color:white;box-shadow:0 4px 24px rgba(0,0,0,0.2);white-space:nowrap;
        animation:fadeIn .2s ease`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 2600);
}

// ─── IDENTITY ───
function initCheck() {
    if (window.location.search.includes('sa')) {
        const saTime = parseInt(localStorage.getItem('wm_sa_time') || '0');
        if (saTime && Date.now() - saTime < 30 * 60 * 1000) {
            activateSuperAdmin();
        } else {
            localStorage.removeItem('wm_sa_time');
            document.getElementById('superOverlay').classList.add('open');
        }
        return;
    }
    const saved = localStorage.getItem('wifi_mode');
    if (saved) {
        state.role = 'admin';
        state.adminName = saved;
        buildAdminButtons();
    } else {
        buildAdminButtons();
        document.getElementById('identityOverlay').classList.add('open');
    }
}

function buildAdminButtons() {
    const admins = state.settings.admins || APP_DEFAULTS.admins;
    const el = document.getElementById('adminButtons');
    if (!el) return;
    el.innerHTML = admins.map(name => `
        <button onclick="setIdentity('${name}')" class="overlay-btn"
            style="display:flex;align-items:center;justify-content:center;gap:10px">
            <span style="font-size:20px">👤</span> ${name}
        </button>
    `).join('');
}

function setIdentity(name) {
    localStorage.setItem('wifi_mode', name);
    state.role = 'admin';
    state.adminName = name;
    document.getElementById('identityOverlay').classList.remove('open');
    render();
}

function switchUser() {
    if (!confirm('Switch user?')) return;
    localStorage.removeItem('wifi_mode');
    state.role = null;
    state.adminName = '';
    buildAdminButtons();
    document.getElementById('identityOverlay').classList.add('open');
}

function activateSuperAdmin() {
    state.role = 'super_admin';
    state.adminName = 'Super Admin';
    document.getElementById('superOverlay').classList.remove('open');
    render();
}

function loginSuper() {
    const pin = document.getElementById('superPin').value;
    const correctPin = state.settings.pin || APP_DEFAULTS.pin;
    if (pin === correctPin) {
        localStorage.setItem('wm_sa_time', Date.now().toString());
        activateSuperAdmin();
    } else {
        const inp = document.getElementById('superPin');
        inp.style.borderColor = '#ef4444';
        document.getElementById('superPinError').style.display = 'block';
        setTimeout(() => { inp.style.borderColor = ''; }, 700);
    }
}

// ─── FIREBASE ───
function initFirebase() {
    db.ref('settings').on('value', snap => {
        if (snap.val()) {
            state.settings = { ...state.settings, ...snap.val() };
            if (typeof state.settings.admins === 'string')
                state.settings.admins = JSON.parse(state.settings.admins);
        }
        render();
    });
    db.ref('users').on('value', snap => {
        state.users = [];
        if (snap.val())
            Object.keys(snap.val()).forEach(k => state.users.push({ id:k, ...snap.val()[k] }));
        render();
        syncWithRouter();
    });
    db.ref('paymentHistory').on('value', snap => {
        state.paymentHistory = [];
        if (snap.val())
            Object.keys(snap.val()).forEach(k => state.paymentHistory.push({ id:k, ...snap.val()[k] }));
        render();
    });
}

function initSettings() {
    db.ref('settings').once('value', snap => {
        if (!snap.val()) {
            db.ref('settings').set({
                monthlyFee:  APP_DEFAULTS.fee,
                billingDays: APP_DEFAULTS.billingDays,
                currency:    APP_DEFAULTS.currency,
                pin:         APP_DEFAULTS.pin,
                admins:      APP_DEFAULTS.admins
            });
        }
    });
}

// ─── USER CRUD ───
async function addUser(name, whatsapp) {
    const id = genId();
    showToast('Adding user & creating WiFi pass...', 'info');
    let passKey = '';
    try {
        const fd = new URLSearchParams();
        fd.append('action', 'create_pass');
        fd.append('fullname', name);
        fd.append('duration', state.settings.billingDays || 30);
        fd.append('whatsapp', whatsapp || '');
        const res  = await fetch('https://api.wifistore.online/ruckus_api.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.status === 'success') passKey = data.pass_key;
    } catch(e) { console.error('Router error:', e); }

    const today  = new Date().toISOString().split('T')[0];
    const exp    = new Date(today);
    exp.setDate(exp.getDate() + (state.settings.billingDays || 30));
    const expiryDate = exp.toISOString().split('T')[0];

    db.ref('users/' + id).set({
        name, whatsapp,
        status: 'paid',
        paymentDate: today,
        expiryDate,
        wifiPass: passKey,
        unpaidMonths: 0,
        deviceLimit: 1,
        createdAt: new Date().toISOString()
    });

    // Queue auto SMS in Firebase
    if (passKey) {
        try {
            const smsMsg = `Hi ${name},\n\nYour WiFi payment of ${state.settings.monthlyFee} SAR is received. Thank you! 🙏\n\n🔑 WiFi Pass: ${passKey}\n(Validity: ${state.settings.billingDays || 30} Days)`;
            const cleanNum = (whatsapp || '').replace(/[^0-9]/g, '');
            if (cleanNum) {
                db.ref('sms_queue/' + genId()).set({
                    phone: '+' + cleanNum,
                    message: smsMsg,
                    timestamp: new Date().toISOString()
                });
            }
        } catch (smsErr) {
            console.error('Failed to queue SMS:', smsErr);
        }
    }

    showToast(passKey ? `${name} added — Pass: ${passKey} ✅` : `${name} added ⚠️ (router pass failed)`);
}

async function deleteUser(id) {
    if (!confirm('Delete this user permanently?')) return;
    const user = state.users.find(u => u.id === id);
    db.ref('users/' + id).remove();
    showToast('User deleted', 'error');
    // Also remove their WiFi pass from the router
    if (user && user.wifiPass) {
        try {
            const fd = new URLSearchParams();
            fd.append('action', 'delete_pass');
            fd.append('pass_key', user.wifiPass);
            await fetch('https://api.wifistore.online/ruckus_api.php', { method:'POST', body:fd });
        } catch(e) { console.error('Router delete error:', e); }
    }
}

// ─── ROUTER SYNC ───
// Users whose WiFi pass was deleted on the router are removed from the site too.
let _routerSyncInProgress = false;

async function syncWithRouter() {
    if (_routerSyncInProgress) return;
    _routerSyncInProgress = true;
    try {
        const fd = new URLSearchParams();
        fd.append('action', 'get_guest_passes');
        const res  = await fetch('https://api.wifistore.online/ruckus_api.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.status !== 'success' || !Array.isArray(data.passes)) return;
        
        // 1. Delete users from Firebase if they were deleted on the router
        const routerKeys = new Set(data.passes.map(p => (p.key || '').toUpperCase()));
        const stale = state.users.filter(u => {
            if (!u.wifiPass) return false;
            const ageMs = Date.now() - new Date(u.createdAt).getTime();
            const isNew = ageMs >= -60000 && ageMs < 10 * 60 * 1000; // 10 min window, allows 1 min negative drift
            return !routerKeys.has(u.wifiPass.toUpperCase()) && !isNew;
        });
        if (stale.length) {
            stale.forEach(u => db.ref('users/' + u.id).remove());
            showToast(`🧹 ${stale.length} user(s) removed — pass deleted on router`);
        }

        // 2. Import users from the router if they don't exist in Firebase
        const firebaseKeys = new Set(state.users.map(u => (u.wifiPass || '').toUpperCase()));
        const newPasses = data.passes.filter(p => p.key && !firebaseKeys.has(p.key.toUpperCase()));
        if (newPasses.length) {
            newPasses.forEach(p => {
                const id = genId();
                const expiryDate = p.expire_time ? new Date(p.expire_time * 1000).toISOString().split('T')[0] : '';
                const paymentDate = p.create_time ? new Date(p.create_time * 1000).toISOString().split('T')[0] : '';
                const today = new Date();
                const status = p.expire_time && new Date(p.expire_time * 1000) > today ? 'paid' : 'unpaid';
                const createdAt = p.create_time ? new Date(p.create_time * 1000).toISOString() : new Date().toISOString();
                
                db.ref('users/' + id).set({
                    name: p.fullname || 'Imported User',
                    whatsapp: p.phone ? '+' + p.phone.replace(/[^0-9]/g, '') : '',
                    status: status,
                    paymentDate: paymentDate,
                    expiryDate: expiryDate,
                    wifiPass: p.key,
                    unpaidMonths: 0,
                    createdAt: createdAt
                });
            });
            showToast(`📥 ${newPasses.length} user(s) imported from router`);
        }
    } catch(e) { 
        console.error('Router sync error:', e); 
    } finally {
        _routerSyncInProgress = false;
    }
}

// ─── PAYMENT ───
async function markPaid(userId) {
    if (state.processingPayment) return;
    const user = state.users.find(u => u.id === userId);
    if (!user) return;
    try {
        state.processingPayment = userId;
        render();
        showToast(`Processing payment for ${user.name}...`, 'info');

        const daysLeft = getDaysLeft(user.expiryDate);
        const carryForward = daysLeft > 0 ? daysLeft : 0;
        const billingDays = state.settings.billingDays || 30;
        const newDuration = billingDays + carryForward;

        // Delete old pass from router first to avoid key conflict if keeping the same key
        if (user.wifiPass) {
            try {
                const delFd = new URLSearchParams();
                delFd.append('action', 'delete_pass');
                delFd.append('pass_key', user.wifiPass);
                await fetch('https://api.wifistore.online/ruckus_api.php', { method: 'POST', body: delFd });
            } catch(e) { console.error('Error deleting old pass:', e); }
        }

        const fd = new URLSearchParams();
        fd.append('action', 'create_pass');
        fd.append('fullname', user.name);
        fd.append('duration', newDuration);
        fd.append('whatsapp', user.whatsapp || '');
        fd.append('limitnumber', user.deviceLimit || 1);
        if (user.wifiPass) {
            fd.append('pass_key', user.wifiPass);
        }
        const res  = await fetch('https://api.wifistore.online/ruckus_api.php', { method:'POST', body:fd });
        const data = await res.json();
        let passKey = '';
        if (data.status === 'success') {
            passKey = data.pass_key;
            showToast(`Pass '${passKey}' created ✅`);
        } else {
            showToast(`Router: ${data.message} ⚠️`, 'error');
        }
        const today = new Date().toISOString().split('T')[0];
        const exp   = new Date(today);
        exp.setDate(exp.getDate() + newDuration);
        const expiryDate = exp.toISOString().split('T')[0];
        const updates = {
            status: 'paid',
            paymentDate: today,
            expiryDate,
            unpaidMonths: Math.max(0, (user.unpaidMonths || 0) - 1)
        };
        if (passKey) updates.wifiPass = passKey;
        await db.ref('users/' + userId).update(updates);
        await db.ref('paymentHistory/' + genId()).set({
            userId,
            userName:    user.name,
            amount:      state.settings.monthlyFee,
            paidDate:    today,
            collectedBy: state.adminName,
            period:      getCurrentMonthLabel(),
            timestamp:   new Date().toISOString()
        });

        // Queue auto SMS in Firebase
        if (passKey) {
            try {
                const smsMsg = `Hi ${user.name},\n\nYour WiFi payment of ${state.settings.monthlyFee} SAR is received. Thank you! 🙏\n\n🔑 WiFi Pass: ${passKey}\n(Validity: ${state.settings.billingDays || 30} Days)`;
                const cleanNum = (user.whatsapp || '').replace(/[^0-9]/g, '');
                if (cleanNum) {
                    await db.ref('sms_queue/' + genId()).set({
                        phone: '+' + cleanNum,
                        message: smsMsg,
                        timestamp: new Date().toISOString()
                    });
                }
            } catch (smsErr) {
                console.error('Failed to queue SMS:', smsErr);
            }
        }

        showToast(`${user.name} — Paid ✅`);
    } catch(e) {
        console.error(e);
        showToast('Router connection error', 'error');
    } finally {
        state.processingPayment = null;
        render();
    }
}

// ─── WHATSAPP ───
function sendReminder(userId) {
    const user    = state.users.find(u => u.id === userId);
    if (!user) return;
    const fee     = state.settings.monthlyFee;
    const days    = getDaysLeft(user.expiryDate);
    const pending = getPendingMonths(user);
    const totalMo = pending + (user.status === 'unpaid' || days <= 0 ? 1 : 0);
    const due     = totalMo * fee;
    let msg = '';
    if (user.status === 'paid' && user.wifiPass) {
        msg = `Hi ${user.name},\n\nYour WiFi payment of *${fee} SAR* is received. Thank you! 🙏\n\n🔑 *New WiFi Pass:* ${user.wifiPass}\n_(Validity: ${state.settings.billingDays} Days)_`;
    } else if (days > 0 && days <= 7 && pending === 0) {
        msg = `Hi ${user.name},\n\nYour WiFi expires in *${days} days*.\nPlease pay *${fee} SAR* to renew.\n\nThank you! 🙏`;
    } else if (totalMo >= 2) {
        msg = `Hi ${user.name},\n\n⚠️ You have *${totalMo} months* pending.\n💰 Total Due: *${due} SAR*\n\nPlease clear your dues.\n\nThank you! 🙏`;
    } else {
        msg = `Hi ${user.name},\n\nYour WiFi has expired.\nPlease pay *${fee} SAR* to continue.\n\nThank you! 🙏`;
    }
    if (user.status !== 'paid' && user.wifiPass)
        msg += `\n\n🔑 *Current WiFi Pass:* ${user.wifiPass}`;
    const num = (user.whatsapp || '').replace(/[^0-9]/g, '');
    window.open(`https://wa.me/${num}?text=${encodeURIComponent(msg)}`, '_blank');
}

// ─── SMS (TEXT MESSAGE) ───
function sendSMS(userId) {
    const user    = state.users.find(u => u.id === userId);
    if (!user) return;
    const fee     = state.settings.monthlyFee;
    const days    = getDaysLeft(user.expiryDate);
    const pending = getPendingMonths(user);
    const totalMo = pending + (user.status === 'unpaid' || days <= 0 ? 1 : 0);
    const due     = totalMo * fee;
    let msg = '';
    if (user.status === 'paid' && user.wifiPass) {
        msg = `Hi ${user.name},\n\nYour WiFi payment of ${fee} SAR is received. Thank you! 🙏\n\n🔑 New WiFi Pass: ${user.wifiPass}\n(Validity: ${state.settings.billingDays} Days)`;
    } else if (days > 0 && days <= 7 && pending === 0) {
        msg = `Hi ${user.name},\n\nYour WiFi expires in ${days} days.\nPlease pay ${fee} SAR to renew.\n\nThank you! 🙏`;
    } else if (totalMo >= 2) {
        msg = `Hi ${user.name},\n\n⚠️ You have ${totalMo} months pending.\n💰 Total Due: ${due} SAR\n\nPlease clear your dues.\n\nThank you! 🙏`;
    } else {
        msg = `Hi ${user.name},\n\nYour WiFi has expired.\nPlease pay ${fee} SAR to continue.\n\nThank you! 🙏`;
    }
    if (user.status !== 'paid' && user.wifiPass)
        msg += `\n\n🔑 Current WiFi Pass: ${user.wifiPass}`;
    const num = (user.whatsapp || '').replace(/[^0-9]/g, '');
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const separator = isIOS ? '&' : '?';
    window.location.href = `sms:+${num}${separator}body=${encodeURIComponent(msg)}`;
}

// ─── ADD FORM ───
function toggleAddForm() {
    const f = document.getElementById('addForm');
    if (!f) return;
    const showing = f.style.display !== 'none' && f.style.display !== '';
    f.style.display = showing ? 'none' : 'block';
    if (!showing) document.getElementById('formName')?.focus();
}

function submitAddUser() {
    const name = document.getElementById('formName')?.value.trim();
    const wa   = document.getElementById('formWA')?.value.trim();
    if (!name || !wa) { showToast('Fill all fields', 'error'); return; }
    addUser(name, wa);
    document.getElementById('formName').value = '';
    document.getElementById('formWA').value   = '';
    document.getElementById('addForm').style.display = 'none';
}

// ─── SEARCH ───
function filterUsers(q) {
    state.searchQuery = q.toLowerCase().trim();
    renderUserList();
}

function getFilteredUsers() {
    let users = state.users;
    if (state.searchQuery) {
        users = users.filter(u =>
            (u.name || '').toLowerCase().includes(state.searchQuery) ||
            (u.whatsapp || '').includes(state.searchQuery)
        );
    }
    return users;
}

// ─── RENDER ───
function render() {
    if (!state.role) return;

    // Update user pill
    const cu = document.getElementById('currentUser');
    if (cu) cu.textContent = state.adminName + (state.role === 'super_admin' ? ' 🔐' : '');

    const av = document.getElementById('userAvatar');
    if (av) av.textContent = (state.adminName || '?').charAt(0).toUpperCase();

    // Header actions (desktop)
    const actions = document.getElementById('headerActions');
    if (actions) {
        actions.innerHTML = state.role === 'super_admin' ? `
            <button onclick="toggleAddForm()" class="hdr-btn d-none d-sm-flex">
                <i class="bi bi-plus-lg"></i> Add User
            </button>` : '';
    }

    // Bottom nav buttons
    const addNavBtn    = document.getElementById('addNavBtn');
    const switchNavBtn = document.getElementById('switchNavBtn');
    const placeholder  = document.getElementById('placeholderNav');
    if (state.role === 'super_admin') {
        if (addNavBtn)    { addNavBtn.style.display = 'flex'; }
        if (switchNavBtn) { switchNavBtn.style.display = 'flex'; }
        if (placeholder)  { placeholder.style.display = 'none'; }
    } else {
        if (addNavBtn)    { addNavBtn.style.display = 'none'; }
        if (switchNavBtn) { switchNavBtn.style.display = 'flex'; }
        if (placeholder)  { placeholder.style.display = 'none'; }
    }

    // Stats
    const users  = state.users;
    const paid   = users.filter(u => u.status === 'paid' && getDaysLeft(u.expiryDate) > 0).length;
    const unpaid = users.length - paid;

    // Mobile stats
    setEl('totalCount', users.length);
    setEl('paidCount',  paid);
    setEl('unpaidCount', unpaid);

    // Desktop sidebar stats
    setEl('totalCount2', users.length);
    setEl('paidCount2',  paid);
    setEl('unpaidCount2', unpaid);

    // Desktop sidebar month stats
    renderDesktopMonthStats();

    renderUserList();
}

function renderUserList() {
    const listEl = document.getElementById('userList');
    if (!listEl) return;
    const users = getFilteredUsers();
    const label = document.getElementById('listLabel');
    if (label) label.textContent = `${users.length} User${users.length !== 1 ? 's' : ''}`;
    listEl.innerHTML = renderUserCards(users);
}

function renderUserCards(users) {
    if (!users || users.length === 0) {
        return `<div class="empty-state">
            <div class="empty-icon">🔍</div>
            <div style="font-weight:600">${state.searchQuery ? 'No results found' : 'No users yet'}</div>
        </div>`;
    }

    const order = { pending:0, expired:1, unpaid:1, expiring:2, paid:3 };
    users = [...users].sort((a,b) => (order[getStatusInfo(a).cls]||3) - (order[getStatusInfo(b).cls]||3));

    return users.map(user => {
        const si      = getStatusInfo(user);
        const days    = getDaysLeft(user.expiryDate);
        const pending = getPendingMonths(user);
        const lastPay = state.paymentHistory.filter(p => p.userId === user.id).pop();
        const fee     = state.settings.monthlyFee;
        const due     = (pending + (user.status === 'unpaid' || days <= 0 ? 1 : 0)) * fee;
        const needPay = ['unpaid','expired','expiring','pending'].includes(si.cls);
        const isCurrent = lastPay && lastPay.period === getCurrentMonthLabel();

        return `
        <div class="user-card status-${si.cls}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px">
                <div style="flex:1;min-width:0">
                    <div class="user-name text-truncate">${user.name}</div>
                    <div class="user-phone">📱 ${user.whatsapp || '—'}</div>

                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:8px">
                        <span class="status-badge badge-${si.cls}">${si.label}</span>
                        ${user.deviceLimit > 1 ? `<span class="status-badge" style="background:#f1f5f9;color:#475569;border-color:#cbd5e1">📱 ${user.deviceLimit} Devices</span>` : ''}
                        ${si.cls === 'paid'
                            ? `<span style="font-size:12px;font-weight:600;color:var(--text-muted)">${days}d left</span>`
                            : due > 0
                                ? `<span style="font-size:12px;font-weight:700;color:var(--danger)">${due} SAR due</span>`
                                : ''}
                    </div>

                    ${pending >= 1 ? `
                        <div class="pending-warn">
                            ⚠️ <strong>${pending} months</strong> pending — Total: <strong>${(pending+1)*fee} SAR</strong>
                        </div>` : ''}

                    ${lastPay ? `
                        <div class="last-pay-info" style="margin-top:6px">
                            Last paid by ${lastPay.collectedBy} · ${formatDate(lastPay.paidDate)}
                            <span style="background:${isCurrent?'#10b981':'#4361ee'};color:white;
                                border-radius:6px;padding:1px 7px;font-size:10px;font-weight:700;margin-left:4px">
                                ${lastPay.period || '—'}
                            </span>
                        </div>` : ''}
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;flex-shrink:0">
                    ${needPay && state.role !== 'super_admin'
                        ? (state.processingPayment === user.id
                            ? `<button disabled class="card-btn btn-pay" style="opacity:0.6;cursor:not-allowed">⏳ ...</button>`
                            : `<button onclick="markPaid('${user.id}')" class="card-btn btn-pay">PAY</button>`)
                        : needPay
                            ? `<span style="font-size:11px;font-weight:700;color:var(--danger)">⚠️ Due</span>`
                            : `<span style="font-size:11px;font-weight:700;color:var(--success)">✓ OK</span>`}
                    <div style="display:flex;gap:6px">
                        <button onclick="sendReminder('${user.id}')" class="card-btn btn-wa" title="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </button>
                        <button onclick="sendSMS('${user.id}')" class="card-btn btn-sms" title="Send SMS">
                            <i class="bi bi-chat-left-text"></i>
                        </button>
                    </div>
                    ${state.role === 'super_admin' ? `
                        <button onclick="showEditModal('${user.id}')" class="card-btn btn-edit" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="deleteUser('${user.id}')" class="card-btn btn-del" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>` : ''}
                </div>
            </div>
        </div>`;
    }).join('');
}

// ─── STATS PANEL ───
function openStatsPanel() {
    document.getElementById('statsPanel').classList.add('open');
    document.getElementById('statsOverlay').classList.add('open');
    renderStatsPanel();
}

function closeStatsPanel() {
    document.getElementById('statsPanel').classList.remove('open');
    document.getElementById('statsOverlay').classList.remove('open');
}

function renderDesktopMonthStats() {
    const el = document.getElementById('desktopMonthStats');
    if (!el) return;
    const fee        = state.settings.monthlyFee;
    const monthStr   = new Date().toISOString().slice(0, 7);
    const thisMonth  = state.paymentHistory.filter(p => (p.paidDate||'').startsWith(monthStr));
    const thisTotal  = thisMonth.reduce((s,p) => s + (p.amount||0), 0);
    const pending    = state.users.reduce((s,u) => {
        const d = getDaysLeft(u.expiryDate);
        if (u.status === 'unpaid' || d <= 0) s += fee;
        s += getPendingMonths(u) * fee;
        return s;
    }, 0);
    el.innerHTML = `
        <div style="display:flex;justify-content:space-between;padding:10px 0;
            border-bottom:1px solid var(--border)">
            <span style="color:var(--text-muted);font-size:13px">Collected</span>
            <span style="font-weight:700;color:var(--success)">${thisTotal} SAR</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:10px 0">
            <span style="color:var(--text-muted);font-size:13px">Pending</span>
            <span style="font-weight:700;color:var(--danger)">${pending} SAR</span>
        </div>`;
}

function renderStatsPanel() {
    const fee        = state.settings.monthlyFee;
    const monthStr   = new Date().toISOString().slice(0, 7);
    const monthLabel = new Date().toLocaleDateString('en-US', { month:'long', year:'numeric' });
    const thisMonth  = state.paymentHistory.filter(p => (p.paidDate||'').startsWith(monthStr));
    const admins     = state.settings.admins || APP_DEFAULTS.admins;
    const allTime    = state.paymentHistory.reduce((s,p) => s + (p.amount||0), 0);
    const thisTotal  = thisMonth.reduce((s,p) => s + (p.amount||0), 0);
    const pending    = state.users.reduce((s,u) => {
        const d = getDaysLeft(u.expiryDate);
        if (u.status === 'unpaid' || d <= 0) s += fee;
        s += getPendingMonths(u) * fee;
        return s;
    }, 0);
    const paidUsers   = state.users.filter(u => u.status === 'paid' && getDaysLeft(u.expiryDate) > 0).length;
    const unpaidUsers = state.users.length - paidUsers;

    setEl('statsMonth', monthLabel);

    const adminRows = admins.map(name => {
        const amt = thisMonth.filter(p => p.collectedBy === name).reduce((s,p) => s + (p.amount||0), 0);
        return `
        <div style="display:flex;justify-content:space-between;align-items:center;
            padding:10px 12px;border-radius:10px;background:var(--bg);margin-bottom:6px">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:30px;height:30px;background:var(--primary-light);border-radius:8px;
                    display:flex;align-items:center;justify-content:center;font-size:15px">👤</div>
                <span style="font-weight:600;font-size:14px">${name}</span>
            </div>
            <span style="font-weight:800;color:var(--success)">${amt} SAR</span>
        </div>`;
    }).join('');

    document.getElementById('statsPanelContent').innerHTML = `
        <div style="background:var(--success-light);border-radius:14px;padding:16px;margin-bottom:14px">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;color:#059669;margin-bottom:4px">This Month Collected</div>
            <div style="font-size:32px;font-weight:800;color:var(--success)">${thisTotal}
                <span style="font-size:14px;font-weight:400">SAR</span></div>
        </div>

        <div style="font-size:11px;font-weight:700;text-transform:uppercase;
            letter-spacing:.5px;color:var(--text-muted);margin-bottom:8px">Per Admin</div>
        ${adminRows}

        <div style="background:var(--danger-light);border-radius:14px;padding:16px;margin:14px 0">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;color:#dc2626;margin-bottom:4px">Total Pending</div>
            <div style="font-size:32px;font-weight:800;color:var(--danger)">${pending}
                <span style="font-size:14px;font-weight:400">SAR</span></div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">${unpaidUsers} users unpaid</div>
        </div>

        <div style="background:var(--primary-light);border-radius:14px;padding:16px;margin-bottom:14px">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;color:var(--primary);margin-bottom:4px">All Time Collected</div>
            <div style="font-size:32px;font-weight:800;color:var(--primary)">${allTime}
                <span style="font-size:14px;font-weight:400">SAR</span></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div style="background:var(--success-light);border-radius:14px;padding:14px;text-align:center">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                    color:var(--success)">Paid</div>
                <div style="font-size:28px;font-weight:800;color:var(--success)">${paidUsers}</div>
            </div>
            <div style="background:var(--danger-light);border-radius:14px;padding:14px;text-align:center">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                    color:var(--danger)">Unpaid</div>
                <div style="font-size:28px;font-weight:800;color:var(--danger)">${unpaidUsers}</div>
            </div>
        </div>`;
}

// ─── EDIT MODAL ───
function showEditModal(userId) {
    const user = state.users.find(u => u.id === userId);
    if (!user) return;
    document.getElementById('editModal')?.remove();
    document.body.insertAdjacentHTML('beforeend', `
    <div id="editModal" style="position:fixed;inset:0;background:rgba(0,0,0,0.55);
        z-index:9999;display:flex;align-items:flex-end;justify-content:center;
        backdrop-filter:blur(4px)">
        <div style="background:white;width:100%;max-width:500px;border-radius:24px 24px 0 0;
            padding:24px 20px">
            <div style="width:40px;height:4px;background:#e2e8f0;border-radius:2px;
                margin:0 auto 20px"></div>
            <div style="font-weight:800;font-size:18px;margin-bottom:18px">Edit User</div>

            <label style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;color:var(--text-muted);display:block;margin-bottom:6px">Name</label>
            <input type="text" id="editName" class="wm-input" value="${user.name}">

            <label style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;color:var(--text-muted);display:block;margin-bottom:6px">WhatsApp</label>
            <input type="tel" id="editWA" class="wm-input" value="${user.whatsapp||''}" placeholder="+966...">

            <label style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;color:var(--text-muted);display:block;margin-bottom:6px">Expiry Date</label>
            <input type="date" id="editExpiry" class="wm-input" value="${user.expiryDate||''}">

            <label style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;color:var(--text-muted);display:block;margin-bottom:6px">Payment Date</label>
            <input type="date" id="editPayDate" class="wm-input" value="${user.paymentDate||''}">

            <label style="font-size:11px;font-weight:700;text-transform:uppercase;
                letter-spacing:.5px;color:var(--text-muted);display:block;margin-bottom:6px">Device Limit</label>
            <select id="editLimit" class="wm-input">
                <option value="1" ${user.deviceLimit == 1 || !user.deviceLimit ? 'selected' : ''}>1 Device (Standard)</option>
                <option value="2" ${user.deviceLimit == 2 ? 'selected' : ''}>2 Devices</option>
                <option value="3" ${user.deviceLimit == 3 ? 'selected' : ''}>3 Devices</option>
                <option value="4" ${user.deviceLimit == 4 ? 'selected' : ''}>4 Devices</option>
            </select>

            <div style="display:flex;gap:10px;margin-top:4px">
                <button onclick="document.getElementById('editModal').remove()"
                    class="wm-btn wm-btn-ghost" style="flex:1">Cancel</button>
                <button onclick="saveEdit('${userId}')"
                    class="wm-btn wm-btn-primary" style="flex:2">Save Changes</button>
            </div>
        </div>
    </div>`);
}

function saveEdit(userId) {
    const name    = document.getElementById('editName')?.value.trim();
    const wa      = document.getElementById('editWA')?.value.trim();
    const expiry  = document.getElementById('editExpiry')?.value;
    const payDate = document.getElementById('editPayDate')?.value;
    const limit   = parseInt(document.getElementById('editLimit')?.value) || 1;
    if (!name) { showToast('Name required', 'error'); return; }
    const status = expiry && new Date(expiry) > new Date() ? 'paid' : 'unpaid';
    db.ref('users/' + userId).update({ name, whatsapp:wa||'', expiryDate:expiry||'', paymentDate:payDate||'', status, deviceLimit: limit });
    document.getElementById('editModal').remove();
    showToast(`${name} updated ✅`);
}

// ─── LIVE CLIENTS ───
let _liveLoading = false;

function openLivePanel() {
    document.getElementById('livePanel').classList.add('open');
    document.getElementById('liveOverlay').classList.add('open');
    if (!document.querySelector('[data-live-loaded]')) fetchLiveClients();
}

function closeLivePanel() {
    document.getElementById('livePanel').classList.remove('open');
    document.getElementById('liveOverlay').classList.remove('open');
}

async function fetchLiveClients() {
    if (_liveLoading) return;
    _liveLoading = true;
    const listEl = document.getElementById('liveClientList');
    const btn    = document.getElementById('liveRefreshBtn');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Fetching...'; }
    if (listEl) listEl.innerHTML = `
        <div style="text-align:center;padding:50px 0;color:rgba(255,255,255,0.3)">
            <div style="font-size:36px;animation:spin 1s linear infinite;display:inline-block">⏳</div>
            <div style="margin-top:10px;font-size:13px">Connecting to router...</div>
        </div>`;
    try {
        const fd = new URLSearchParams();
        fd.append('action', 'get_active_clients');
        const res  = await fetch('https://api.wifistore.online/ruckus_api.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.status === 'success') {
            renderLiveClients(data.clients);
            const dot = document.getElementById('liveDot');
            if (dot) dot.style.display = 'block';
            setEl('liveTimestamp', 'Updated ' + new Date().toLocaleTimeString());
        } else {
            if (listEl) listEl.innerHTML = `<div style="text-align:center;padding:30px;
                color:#f87171;font-size:13px">❌ ${data.message || 'Router error'}</div>`;
        }
    } catch(e) {
        if (listEl) listEl.innerHTML = `<div style="text-align:center;padding:30px;
            color:#f87171;font-size:13px">❌ Network error — check server</div>`;
    } finally {
        _liveLoading = false;
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh'; }
    }
}

function renderLiveClients(clients) {
    const listEl = document.getElementById('liveClientList');
    if (!listEl) return;
    const total  = clients.length;
    const fiveG  = clients.filter(c => c.band === '5g').length;
    const twoFour = clients.filter(c => c.band === '2.4g').length;
    setEl('liveCount',   total);
    setEl('live5gCount', fiveG);
    setEl('live24gCount', twoFour);
    if (total === 0) {
        listEl.innerHTML = `<div style="text-align:center;padding:50px 0;color:rgba(255,255,255,0.3)">
            <div style="font-size:40px">📡</div>
            <div style="margin-top:8px;font-size:13px">No devices connected</div>
        </div>`;
        return;
    }

    const userMap = {};
    (state.users||[]).forEach(u => {
        const key = (u.name||'').toLowerCase().trim();
        if (key) userMap[key] = u;
    });

    function payBadge(clientUser) {
        const user = userMap[(clientUser||'').toLowerCase().trim()];
        if (!user) return `<span style="background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.35);
            border-radius:6px;padding:2px 8px;font-size:10px">—</span>`;
        const si = getStatusInfo(user);
        const clrMap = {
            paid:['#10b981','rgba(16,185,129,0.15)'],
            expiring:['#f59e0b','rgba(245,158,11,0.15)'],
            expired:['#ef4444','rgba(239,68,68,0.15)'],
            unpaid:['#ef4444','rgba(239,68,68,0.15)'],
            pending:['#8b5cf6','rgba(139,92,246,0.15)']
        };
        const [clr,bg] = clrMap[si.cls]||['#aaa','rgba(255,255,255,0.07)'];
        return `<span style="background:${bg};color:${clr};border:1px solid ${clr};
            border-radius:6px;padding:2px 8px;font-size:10px;font-weight:700">${si.label}</span>`;
    }

    function signalBar(val) {
        const pct = Math.min(100, Math.max(0, val));
        const clr = pct>=50?'#10b981':pct>=25?'#f59e0b':'#ef4444';
        return `<div style="width:36px;height:4px;background:rgba(255,255,255,0.1);border-radius:2px;margin-top:3px">
            <div style="width:${pct}%;height:100%;background:${clr};border-radius:2px"></div></div>`;
    }

    listEl.innerHTML = clients.map(c => {
        const bandClr = c.band === '5g' ? '#60a5fa' : '#a78bfa';
        const bandLbl = c.band === '5g' ? '5G' : '2.4G';
        const host    = c.hostname && c.hostname !== c.mac ? c.hostname : (c.model || c.mac);
        return `
        <div data-live-loaded="1"
            style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);
            border-radius:14px;padding:12px 14px;margin-bottom:8px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div style="flex:1;min-width:0;padding-right:8px">
                    <div style="font-weight:700;color:#fff;font-size:15px;
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${c.user||'(unknown)'}</div>
                    <div style="color:rgba(255,255,255,0.4);font-size:11px;margin-top:1px;
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${host}</div>
                    <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:5px;align-items:center">
                        ${payBadge(c.user)}
                        <span style="background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.45);
                            border-radius:6px;padding:2px 7px;font-size:10px">${c.ip}</span>
                        <span style="background:${bandClr}22;color:${bandClr};border:1px solid ${bandClr}44;
                            border-radius:6px;padding:2px 6px;font-size:10px;font-weight:700">${bandLbl}</span>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:13px;color:rgba(255,255,255,0.7)">${c.signal_label}</div>
                    <div style="font-size:10px;color:rgba(255,255,255,0.3);margin-top:1px">${c.rssi_dbm} dBm</div>
                    ${signalBar(c.rssi_val)}
                </div>
            </div>
        </div>`;
    }).join('');
}

// ─── INIT ───
document.addEventListener('DOMContentLoaded', () => {
    initSettings();
    initFirebase();
    initCheck();
    // Run sync check periodically every 2 minutes
    setInterval(syncWithRouter, 2 * 60 * 1000);
});

// Keyframe injection
const _s = document.createElement('style');
_s.textContent = `
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes fadeIn { from { opacity:0; transform: translateX(-50%) translateY(-6px); } to { opacity:1; transform: translateX(-50%) translateY(0); } }
`;
document.head.appendChild(_s);
