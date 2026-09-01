<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1f36">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: #4361ee;
            --primary-light: #f0f3ff;
            --primary-hover: #304fd0;
            --primary-glow: rgba(67, 97, 238, 0.15);
            
            --success: #10b981;
            --success-light: #ecfdf5;
            --success-hover: #059669;
            --success-glow: rgba(16, 185, 129, 0.15);
            
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --danger-hover: #dc2626;
            --danger-glow: rgba(239, 68, 68, 0.15);
            
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --warning-hover: #d97706;
            --warning-glow: rgba(245, 158, 11, 0.15);
            
            --purple: #8b5cf6;
            --purple-light: #f5f3ff;
            --purple-hover: #7c3aed;
            --purple-glow: rgba(139, 92, 246, 0.15);
            
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #f1f5f9;
            --radius: 18px;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow: 0 12px 30px -4px rgba(0,0,0,0.05), 0 4px 12px -2px rgba(0,0,0,0.02);
            --shadow-lg: 0 22px 48px -8px rgba(0,0,0,0.07);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: radial-gradient(at 0% 0%, #f1f5f9 0px, transparent 50%),
                        radial-gradient(at 50% 0%, #eef2ff 0px, transparent 50%),
                        var(--bg);
            background-attachment: fixed;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── APP HEADER ─── */
        .app-header {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: white;
            padding: 0 24px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 500;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }

        .app-header .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .app-header .brand-icon {
            width: 38px;
            height: 38px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: inset 0 2px 4px rgba(255,255,255,0.1);
        }

        .app-header .brand-text {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .app-header .brand-text span {
            color: #60a5fa;
            font-weight: 600;
        }

        .app-header .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hdr-btn {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            color: white;
            border-radius: 12px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hdr-btn:hover {
            background: rgba(255,255,255,0.16);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        .hdr-btn:active {
            transform: translateY(0);
        }

        .hdr-btn-live { position: relative; }

        .live-dot {
            position: absolute;
            top: -1px;
            right: -1px;
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            border: 2px solid #0f172a;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.6);
            display: none;
        }

        .user-pill {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 50px;
            padding: 5px 14px 5px 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .user-pill:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .user-pill .avatar {
            width: 26px;
            height: 26px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--purple) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: white;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
        }

        /* ─── LAYOUT ─── */
        .app-body {
            max-width: 1140px;
            margin: 0 auto;
            padding: 24px 16px 100px;
        }

        @media (min-width: 768px) {
            .app-body {
                display: grid;
                grid-template-columns: 300px 1fr;
                gap: 28px;
                padding: 36px 24px 40px;
                align-items: start;
            }
        }

        /* ─── STAT CARDS ─── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        @media (min-width: 768px) {
            .stats-row { gap: 16px; }
        }

        .stat-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 18px 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            text-align: center;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: transparent;
        }

        .stat-card.total::before  { background: linear-gradient(90deg, var(--primary), var(--purple)); }
        .stat-card.paid::before   { background: var(--success); }
        .stat-card.unpaid::before { background: var(--danger); }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .num {
            font-size: 30px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .stat-card .lbl {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 6px;
            color: var(--text-muted);
        }

        .stat-card.total .num  { color: var(--primary); }
        .stat-card.paid .num   { color: var(--success); }
        .stat-card.unpaid .num { color: var(--danger); }

        /* ─── USER CARDS ─── */
        .user-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 20px 18px;
            margin-bottom: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            border-left: 5px solid #e2e8f0;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .user-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: #e2e8f0;
        }

        .user-card.status-paid     { border-left-color: var(--success); }
        .user-card.status-expiring { border-left-color: var(--warning); }
        .user-card.status-unpaid,
        .user-card.status-expired  { border-left-color: var(--danger); }
        .user-card.status-pending  { border-left-color: var(--purple); }

        .user-name {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.3px;
        }

        .user-phone {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ─── STATUS BADGE ─── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            border: 1.5px solid transparent;
            transition: all 0.2s;
        }

        .badge-paid     { background: var(--success-light); color: var(--success); }
        .badge-expiring { background: var(--warning-light); color: var(--warning); }
        .badge-expired,
        .badge-unpaid   { background: var(--danger-light);  color: var(--danger); }
        .badge-pending  { background: var(--purple-light);  color: var(--purple); }

        /* ─── ACTION BUTTONS ─── */
        .card-btn {
            border: none;
            border-radius: 12px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .card-btn:active { transform: scale(0.95); }

        .btn-pay    { background: var(--success); color: white; }
        .btn-pay:hover { background: var(--success-hover); box-shadow: 0 4px 12px var(--success-glow); }
        
        .btn-wa     { background: #e8fbf0; color: #128c7e; border: 1px solid rgba(18,140,126,0.15); }
        .btn-wa:hover { background: #d0f7df; box-shadow: 0 4px 12px rgba(18,140,126,0.15); }
        
        .btn-sms    { background: #f0f6ff; color: #1d4ed8; border: 1px solid rgba(29,78,216,0.15); }
        .btn-sms:hover { background: #dbeafe; box-shadow: 0 4px 12px rgba(29,78,216,0.15); }
        
        .btn-edit   { background: var(--primary-light); color: var(--primary); }
        .btn-edit:hover { background: #e0e7ff; }
        
        .btn-del    { background: var(--danger-light);  color: var(--danger); }
        .btn-del:hover { background: #ffe4e6; }

        .last-pay-info {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 8px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        /* ─── DESKTOP SIDEBAR ─── */
        .desktop-sidebar { display: none !important; }

        @media (min-width: 768px) {
            .desktop-sidebar { display: block !important; }
            #mobileStats { display: none !important; }
        }

        /* ─── SIDEBAR WIDGETS (desktop) ─── */
        .sidebar-widget {
            background: var(--card);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 20px;
            transition: transform 0.25s;
        }
        .sidebar-widget:hover {
            transform: translateY(-2px);
        }

        .widget-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ─── SIDEBAR WIDGET ELEMENTS ─── */
        .widget-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            border: 1px solid transparent;
            margin-bottom: 8px;
            text-decoration: none;
        }
        .widget-row:last-child {
            margin-bottom: 0;
        }
        .widget-row:hover {
            transform: translateX(4px);
        }
        .widget-row.total   { background: var(--primary-light); color: var(--primary); border-color: rgba(67, 97, 238, 0.08); }
        .widget-row.paid    { background: var(--success-light); color: var(--success); border-color: rgba(16, 185, 129, 0.08); }
        .widget-row.unpaid  { background: var(--danger-light); color: var(--danger); border-color: rgba(239, 68, 68, 0.08); }
        .widget-row .lbl    { font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .widget-row .num    { font-size: 18px; font-weight: 800; }
        
        .widget-action-btn {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            text-align: left;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 8px;
        }
        .widget-action-btn:last-child {
            margin-bottom: 0;
        }
        .widget-action-btn:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: rgba(67, 97, 238, 0.15);
            transform: translateY(-1px);
        }
        .widget-action-btn i {
            font-size: 18px;
        }


        /* ─── BOTTOM NAV ─── */
        .bottom-nav {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            display: flex;
            justify-content: space-around;
            padding: 6px 12px;
        }

        .nav-btn {
            border: none;
            background: none;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            transition: all 0.2s;
            border-radius: 16px;
            flex: 1;
        }

        .nav-btn i { font-size: 20px; transition: transform 0.2s; }
        .nav-btn:hover i { transform: translateY(-2px); }
        .nav-btn.active {
            color: var(--primary);
            background: var(--primary-light);
        }

        @media (min-width: 768px) {
            .bottom-nav { display: none; }
        }

        /* ─── SIDE PANELS ─── */
        .panel-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            z-index: 9990;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .panel-overlay.open { display: block; }

        .side-panel {
            position: fixed;
            top: 0;
            right: -110%;
            width: 88%;
            max-width: 400px;
            height: 100%;
            z-index: 9991;
            transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-y: auto;
            box-shadow: -10px 0 40px rgba(0,0,0,0.1);
        }

        .side-panel.open { right: 0; }

        /* ─── FULL OVERLAYS ─── */
        .full-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at center, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0.98) 100%);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .full-overlay.open { display: flex; }

        .overlay-box {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 28px;
            padding: 40px 28px 36px;
            width: 100%;
            max-width: 360px;
            text-align: center;
            color: white;
            box-shadow: 0 24px 60px rgba(0,0,0,0.4);
        }

        .overlay-box .icon {
            font-size: 64px;
            margin-bottom: 14px;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        }

        .overlay-box h3 {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .overlay-box p {
            color: rgba(255,255,255,0.5);
            font-size: 14px;
            margin-bottom: 28px;
        }

        .overlay-btn {
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            border: none;
            background: var(--primary);
            color: white;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: 12px;
            transition: all 0.2s;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(67, 97, 238, 0.4);
        }
        .overlay-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        .overlay-btn:active { transform: scale(0.97); }

        .pin-input-wrap {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
        }

        .pin-input {
            flex: 1;
            background: rgba(255,255,255,0.08);
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 16px;
            padding: 18px 14px;
            color: white;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 12px;
            text-align: center;
            outline: none;
            min-height: 64px;
            touch-action: manipulation;
        }

        .pin-input:focus { border-color: #60a5fa; background: rgba(255,255,255,0.11); }

        .pin-go-btn {
            background: var(--primary);
            border: none;
            border-radius: 16px;
            color: white;
            padding: 14px 20px;
            min-width: 64px;
            min-height: 64px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }

        /* ─── ADD FORM ─── */
        .add-form-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            border-top: 4px solid var(--primary);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .wm-input {
            width: 100%;
            border: 2px solid var(--border);
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 15px;
            outline: none;
            transition: all 0.2s ease-in-out;
            background: white;
            color: var(--text);
            margin-bottom: 14px;
            display: block;
            box-shadow: var(--shadow-sm);
        }

        .wm-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
            transform: scale(1.005);
        }

        .wm-btn {
            border: none;
            border-radius: 14px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .wm-btn:active { transform: scale(0.97); }
        .wm-btn-primary { background: var(--primary); color: white; }
        .wm-btn-primary:hover { background: var(--primary-hover); box-shadow: 0 4px 12px var(--primary-glow); }
        .wm-btn-ghost   { background: var(--bg); color: var(--text-muted); }
        .wm-btn-ghost:hover   { background: #e2e8f0; }

        /* ─── PENDING WARNING ─── */
        .pending-warn {
            background: var(--warning-light);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: var(--warning-hover);
            border-radius: 10px;
            padding: 6px 12px;
            font-size: 12px;
            margin-top: 8px;
            display: inline-block;
            font-weight: 500;
        }

        /* ─── SECTION LABEL ─── */
        .section-label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            margin-bottom: 14px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ─── EMPTY STATE ─── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state .empty-icon { font-size: 52px; opacity: 0.3; margin-bottom: 12px; }

        /* ─── TOAST ─── */
        .toast-wrap {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 99999;
            pointer-events: none;
        }
    </style>
</head>
<body>
