<?php
require_once 'includes/header.php';
?>

<!-- IDENTITY OVERLAY -->
<div id="identityOverlay" class="full-overlay">
    <div class="overlay-box">
        <div class="icon">📡</div>
        <h3>WiFi Manager</h3>
        <p>Select your account to continue</p>
        <div id="adminButtons"></div>
    </div>
</div>

<!-- APP HEADER -->
<header class="app-header">
    <div class="brand">
        <div class="brand-icon">
            <i class="bi bi-router-fill" style="color: #60a5fa;"></i>
        </div>
        <div class="brand-text">WiFi <span>Manager</span></div>
    </div>
    <div class="header-right">
        <button onclick="openLivePanel()" class="hdr-btn hdr-btn-live" title="Live Connections">
            <i class="bi bi-wifi"></i>
            <span style="display:none" class="d-sm-inline">Live</span>
            <span class="live-dot" id="liveDot"></span>
        </button>
        <button onclick="openStatsPanel()" class="hdr-btn" style="display:none" id="statsHdrBtn" title="Stats">
            <i class="bi bi-bar-chart-fill"></i>
        </button>
        <div class="user-pill" onclick="switchUser()" title="Switch user">
            <div class="avatar" id="userAvatar">?</div>
            <span id="currentUser">...</span>
            <i class="bi bi-chevron-down" style="font-size:10px;opacity:0.6"></i>
        </div>
    </div>
</header>

<!-- MAIN BODY -->
<div class="app-body">

    <!-- DESKTOP SIDEBAR -->
    <aside class="desktop-sidebar">
        <div class="sidebar-widget">
            <div class="widget-title">
                <i class="bi bi-grid-1x2-fill" style="color:var(--primary)"></i>
                Overview
            </div>
            <div>
                <div class="widget-row total">
                    <span class="lbl"><i class="bi bi-people-fill"></i> Total Users</span>
                    <span id="totalCount2" class="num">—</span>
                </div>
                <div class="widget-row paid">
                    <span class="lbl"><i class="bi bi-check-circle-fill"></i> Paid</span>
                    <span id="paidCount2" class="num">—</span>
                </div>
                <div class="widget-row unpaid">
                    <span class="lbl"><i class="bi bi-x-circle-fill"></i> Unpaid</span>
                    <span id="unpaidCount2" class="num">—</span>
                </div>
            </div>
        </div>
        <div class="sidebar-widget">
            <div class="widget-title">
                <i class="bi bi-lightning-charge-fill" style="color:var(--warning)"></i>
                Quick Actions
            </div>
            <div>
                <button onclick="openStatsPanel()" class="widget-action-btn">
                    <i class="bi bi-bar-chart-fill" style="color:var(--primary)"></i> Collection Stats
                </button>
                <button onclick="openLivePanel()" class="widget-action-btn">
                    <i class="bi bi-wifi" style="color:var(--success)"></i> Live Connections
                </button>
            </div>
        </div>
        <div class="sidebar-widget">
            <div class="widget-title">
                <i class="bi bi-calendar-event-fill" style="color:var(--success)"></i>
                This Month
            </div>
            <div id="desktopMonthStats" style="color:var(--text-muted);font-size:13px">Loading...</div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main>

        <!-- Stats Row (mobile only) -->
        <div class="stats-row" id="mobileStats">
            <div class="stat-card total">
                <div style="font-size: 18px; margin-bottom: 4px; opacity: 0.8; color: var(--primary)"><i class="bi bi-people-fill"></i></div>
                <div class="num" id="totalCount">—</div>
                <div class="lbl">Total</div>
            </div>
            <div class="stat-card paid">
                <div style="font-size: 18px; margin-bottom: 4px; opacity: 0.8; color: var(--success)"><i class="bi bi-check-circle-fill"></i></div>
                <div class="num" id="paidCount">—</div>
                <div class="lbl">Paid</div>
            </div>
            <div class="stat-card unpaid">
                <div style="font-size: 18px; margin-bottom: 4px; opacity: 0.8; color: var(--danger)"><i class="bi bi-x-circle-fill"></i></div>
                <div class="num" id="unpaidCount">—</div>
                <div class="lbl">Unpaid</div>
            </div>
        </div>

        <!-- Search -->
        <div style="position:relative;margin-bottom:20px">
            <i class="bi bi-search" style="position:absolute;left:18px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:16px;pointer-events:none"></i>
            <input type="text" id="searchInput" class="wm-input" style="padding-left:46px;margin-bottom:0" placeholder="Search by name or mobile number..." oninput="filterUsers(this.value)">
        </div>

        <div class="section-label" id="listLabel">
            <i class="bi bi-list-stars" style="font-size:14px;color:var(--primary)"></i>
            Users
        </div>
        <div id="userList">
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-router"></i></div>
                <div style="font-weight:600">Loading dashboard...</div>
            </div>
        </div>

    </main>
</div>

<!-- BOTTOM NAV -->
<nav class="bottom-nav">
    <button class="nav-btn active" onclick="scrollToTop()">
        <i class="bi bi-house-fill"></i>
        Home
    </button>
    <button class="nav-btn" onclick="openLivePanel()">
        <i class="bi bi-wifi"></i>
        Live
    </button>
    <button class="nav-btn" onclick="openStatsPanel()">
        <i class="bi bi-bar-chart-fill"></i>
        Stats
    </button>
    <button class="nav-btn" onclick="switchUser()">
        <i class="bi bi-person-fill"></i>
        Switch
    </button>
</nav>

<!-- STATS PANEL -->
<div id="statsOverlay" class="panel-overlay" onclick="closeStatsPanel()"></div>
<div id="statsPanel" class="side-panel" style="background:white;padding:24px 20px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <div>
            <div style="font-weight:800;font-size:18px">Collection Stats</div>
            <div style="font-size:12px;color:var(--text-muted)" id="statsMonth"></div>
        </div>
        <button onclick="closeStatsPanel()" style="background:var(--bg);border:none;border-radius:10px;width:34px;height:34px;cursor:pointer;font-size:16px">✕</button>
    </div>
    <div id="statsPanelContent" style="color:var(--text-muted);font-size:14px">Loading...</div>
</div>

<!-- LIVE PANEL -->
<div id="liveOverlay" class="panel-overlay" onclick="closeLivePanel()"></div>
<div id="livePanel" class="side-panel" style="background:#0f172a;padding:22px 18px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px">
        <div>
            <div style="font-weight:800;font-size:17px;color:#fff">Live Connections</div>
            <div id="liveTimestamp" style="font-size:11px;color:rgba(255,255,255,0.35);margin-top:2px"></div>
        </div>
        <button onclick="closeLivePanel()" style="background:rgba(255,255,255,0.1);border:none;border-radius:10px;width:34px;height:34px;color:white;cursor:pointer;font-size:16px">✕</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:16px 0">
        <div style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25);border-radius:12px;padding:12px;text-align:center">
            <div id="liveCount" style="font-size:24px;font-weight:800;color:#10b981">—</div>
            <div style="font-size:10px;color:rgba(255,255,255,0.4);text-transform:uppercase;margin-top:2px">Online</div>
        </div>
        <div style="background:rgba(96,165,250,0.12);border:1px solid rgba(96,165,250,0.25);border-radius:12px;padding:12px;text-align:center">
            <div id="live5gCount" style="font-size:24px;font-weight:800;color:#60a5fa">—</div>
            <div style="font-size:10px;color:rgba(255,255,255,0.4);text-transform:uppercase;margin-top:2px">5G</div>
        </div>
        <div style="background:rgba(139,92,246,0.12);border:1px solid rgba(139,92,246,0.25);border-radius:12px;padding:12px;text-align:center">
            <div id="live24gCount" style="font-size:24px;font-weight:800;color:#a78bfa">—</div>
            <div style="font-size:10px;color:rgba(255,255,255,0.4);text-transform:uppercase;margin-top:2px">2.4G</div>
        </div>
    </div>
    <button onclick="fetchLiveClients()" id="liveRefreshBtn"
        style="width:100%;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.6);border-radius:12px;padding:10px;font-size:13px;font-weight:600;cursor:pointer;margin-bottom:14px;display:flex;align-items:center;justify-content:center;gap:6px">
        <i class="bi bi-arrow-clockwise"></i> Refresh
    </button>
    <div id="liveClientList">
        <div style="text-align:center;padding:50px 0;color:rgba(255,255,255,0.25)">
            <div style="font-size:40px">📡</div>
            <div style="margin-top:10px;font-size:13px">Loading...</div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
