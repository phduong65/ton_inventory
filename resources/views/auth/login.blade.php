<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        height: 100vh;
        overflow: hidden;
    }

    body {
        display: flex;
        height: 100vh;
        overflow: hidden;
        font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        background: #fff;
        -webkit-font-smoothing: antialiased;
    }

    /* ════════════════════════════════════════════════
       LEFT PANEL — illustration (flex 3 = 60%)
    ════════════════════════════════════════════════ */
    .lp-left {
        flex: 3;
        position: relative;
        overflow: hidden;
        background: linear-gradient(155deg, #5b5ef4 0%, #4338ca 45%, #312e9e 100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 52px 60px;
    }

    /* Decorative rings */
    .lp-ring {
        position: absolute;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,.07);
        pointer-events: none;
    }
    .lp-ring-1 { width: 640px; height: 640px; top: -230px; left: -170px; }
    .lp-ring-2 { width: 430px; height: 430px; top: -90px;  left: -40px;  border-color: rgba(255,255,255,.05); }
    .lp-ring-3 { width: 220px; height: 220px; top:  56px;  left:  96px;  border-color: rgba(255,255,255,.04); }

    /* Blur blobs */
    .lp-blob {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        filter: blur(100px);
    }
    .lp-blob-1 { width: 480px; height: 480px; background: rgba(139,128,255,.18); top: -130px; left: -90px; }
    .lp-blob-2 { width: 300px; height: 300px; background: rgba(96,165,250,.09); bottom: 100px; left: 60px; }

    /* ── Dashboard mockup ── */
    .lp-mockup {
        position: absolute;
        top: 40px;
        left: 52px;
        right: 44px;
        background: #fff;
        border-radius: 12px;
        box-shadow:
            0 0 0 1px rgba(0,0,0,.06),
            0 24px 64px rgba(0,0,0,.28),
            0 8px 24px rgba(0,0,0,.14);
        overflow: hidden;
        transform: perspective(1400px) rotateY(3deg) rotateX(1.5deg);
        max-width: 720px;
    }

    /* Browser chrome */
    .lp-chrome {
        display: flex;
        align-items: center;
        gap: 0;
        padding: 0 14px;
        height: 34px;
        background: #f8f8f9;
        border-bottom: 1px solid #eaeaec;
        flex-shrink: 0;
    }
    .lp-dot { width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }

    /* App nav bar inside mockup */
    .lp-app-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 14px;
        height: 36px;
        background: #fff;
        border-bottom: 1px solid #f3f4f6;
    }
    .lp-app-nav-left {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 9px;
        color: #9ca3af;
        font-weight: 500;
    }
    .lp-app-nav-left .sep { color: #d1d5db; }
    .lp-app-nav-left .active { color: #374151; font-weight: 600; }
    .lp-app-nav-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .lp-nav-icon {
        width: 20px; height: 20px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex; align-items: center; justify-content: center;
        font-size: 8px; color: #9ca3af;
    }

    /* Mockup body */
    .lp-mock-inner { padding: 14px 16px 16px; }

    /* Stat + chart row */
    .lp-mock-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr auto;
        gap: 8px;
        align-items: stretch;
        margin-bottom: 12px;
    }
    .lp-stat {
        background: #fafafa;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 9px 10px;
        min-width: 0;
    }
    .lp-stat-l {
        font-size: 8px;
        color: #9ca3af;
        margin-bottom: 3px;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .lp-stat-v {
        font-size: 14px;
        font-weight: 800;
        color: #111827;
        letter-spacing: -.02em;
        line-height: 1;
        margin-bottom: 4px;
    }
    .lp-stat-v small { font-size: 8px; font-weight: 500; color: #9ca3af; }
    .lp-stat-tag {
        display: inline-flex;
        align-items: center;
        font-size: 8px;
        font-weight: 700;
        padding: 1px 5px;
        border-radius: 99px;
    }
    .lp-tag-up   { background: #dcfce7; color: #16a34a; }
    .lp-tag-down { background: #fee2e2; color: #dc2626; }
    .lp-tag-neu  { background: #ede9fe; color: #7c3aed; }

    /* Mini bar chart */
    .lp-chart-wrap {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        width: 80px;
        flex-shrink: 0;
    }
    .lp-chart-bars {
        display: flex;
        align-items: flex-end;
        gap: 2.5px;
        height: 46px;
    }
    .lp-bar {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
    }
    .lp-bar-fill {
        border-radius: 2px 2px 0 0;
        background: #c7d2fe;
    }
    .lp-bar-fill.today { background: #4f46e5; }
    .lp-chart-x {
        display: flex;
        gap: 2.5px;
        margin-top: 3px;
    }
    .lp-chart-x span {
        flex: 1;
        text-align: center;
        font-size: 6.5px;
        color: #d1d5db;
        font-weight: 500;
    }

    /* Table */
    .lp-tbl { width: 100%; border-collapse: collapse; }
    .lp-tbl thead tr {
        border-bottom: 1px solid #f3f4f6;
    }
    .lp-tbl th {
        font-size: 7.5px;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #9ca3af;
        padding: 0 8px 6px;
        text-align: left;
        font-weight: 600;
    }
    .lp-tbl td {
        font-size: 9.5px;
        color: #374151;
        padding: 5px 8px;
        border-top: 1px solid #f9f9f9;
        font-weight: 500;
    }
    .lp-tbl tr:first-child td { border-top: none; }
    .lp-badge {
        display: inline-block;
        font-size: 8px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 99px;
        letter-spacing: .02em;
    }
    .lp-badge-in  { background: #dbeafe; color: #2563eb; }
    .lp-badge-out { background: #fce7f3; color: #be185d; }
    .lp-badge-ok  { background: #dcfce7; color: #16a34a; }

    /* ── Tagline ── */
    .lp-tagline {
        position: relative;
        z-index: 1;
    }
    .lp-tagline h2 {
        font-size: 29px;
        font-weight: 800;
        color: #fff;
        line-height: 1.22;
        letter-spacing: -.03em;
        margin-bottom: 10px;
    }
    .lp-tagline h2 span { color: rgba(199,210,254,.85); }
    .lp-tagline p {
        font-size: 13.5px;
        color: rgba(255,255,255,.52);
        line-height: 1.65;
        font-weight: 400;
    }

    /* Feature pills */
    .lp-pills {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
        margin-top: 20px;
        position: relative;
        z-index: 1;
    }
    .lp-pill {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 99px;
        padding: 5px 12px 5px 10px;
        font-size: 11.5px;
        color: rgba(255,255,255,.75);
        font-weight: 500;
        transition: background .15s, border-color .15s;
    }
    .lp-pill:hover {
        background: rgba(255,255,255,.13);
        border-color: rgba(255,255,255,.2);
    }
    .lp-pill-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: rgba(199,210,254,.6);
        flex-shrink: 0;
    }

    /* ════════════════════════════════════════════════
       RIGHT PANEL — form (flex 2 = 40%)
    ════════════════════════════════════════════════ */
    .lp-right {
        flex: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 40px 52px;
        background: #fff;
        overflow: hidden;
    }

    /* Logo */
    .lp-logo {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        max-width: 100%;
        margin-bottom: 28px;
    }
    .lp-logo img {
        width: 120px;
        height: 120px;
        border-radius: 20px;
        object-fit: cover;
    }
    .lp-logo-name {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        letter-spacing: -.02em;
    }

    /* Form wrap */
    .lp-form-wrap {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 32px 0 28px;
        width: 100%;
        max-width: 100%;
    }

    .lp-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #6366f1;
        margin-bottom: 14px;
    }
    .lp-eyebrow::before {
        content: '';
        display: block;
        width: 16px;
        height: 1.5px;
        background: #6366f1;
        border-radius: 99px;
    }

    .lp-heading {
        font-size: 40px;
        font-weight: 800;
        color: #0f0f1a;
        letter-spacing: -.04em;
        line-height: 1.15;
        margin-bottom: 8px;
        text-align: center;
    }
    .lp-sub {
        font-size: 13.5px;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 28px;
        font-weight: 400;
        text-align: center;
    }

    /* Error banner */
    .lp-error {
        display: flex;
        align-items: center;
        gap: 9px;
        background: #fff5f5;
        border: 1.5px solid #fecdd3;
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 13px;
        color: #be123c;
        margin-bottom: 20px;
        font-weight: 500;
    }
    .lp-error i { font-size: 13px; flex-shrink: 0; }

    /* Form */
    .lp-form { display: flex; flex-direction: column; gap: 16px; }

    .lp-field { display: flex; flex-direction: column; gap: 6px; }

    .lp-field label {
        font-size: 12.5px;
        font-weight: 700;
        color: #374151;
        letter-spacing: -.01em;
    }

    .lp-inp-wrap { position: relative; display: flex; align-items: center; }

    .lp-inp-wrap input {
        width: 100%;
        padding: 11.5px 42px 11.5px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 13.5px;
        font-family: inherit;
        font-weight: 500;
        color: #111827;
        background: #fafafa;
        transition: border-color .15s, box-shadow .15s, background .15s;
        line-height: 1.4;
    }
    .lp-inp-wrap input:hover {
        border-color: #d1d5db;
        background: #f9f9fb;
    }
    .lp-inp-wrap input:focus {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3.5px rgba(99,102,241,.12);
    }
    .lp-inp-wrap input::placeholder {
        color: #c9cacc;
        font-size: 13px;
        font-weight: 400;
    }
    .lp-inp-wrap input.err {
        border-color: #f43f5e;
        background: #fff5f5;
    }

    .lp-eye {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: #c4c4cc;
        cursor: pointer;
        font-size: 13px;
        display: flex;
        align-items: center;
        padding: 3px;
        transition: color .15s;
    }
    .lp-eye:hover { color: #6b7280; }

    /* Remember row */
    .lp-row {
        display: flex;
        align-items: center;
        margin-top: -2px;
    }
    .lp-check {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        user-select: none;
    }
    .lp-check input[type="checkbox"] {
        width: 15px;
        height: 15px;
        accent-color: #6366f1;
        cursor: pointer;
        border-radius: 4px;
    }
    .lp-check span {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }

    /* Submit button */
    .lp-btn {
        width: 100%;
        padding: 13px;
        background: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        font-weight: 700;
        letter-spacing: -.01em;
        cursor: pointer;
        transition: background .15s, box-shadow .15s, transform .1s;
        box-shadow:
            0 1px 2px rgba(79,70,229,.2),
            0 4px 14px rgba(79,70,229,.38);
        margin-top: 6px;
        position: relative;
        overflow: hidden;
    }
    .lp-btn::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, rgba(255,255,255,.08), transparent);
        pointer-events: none;
    }
    .lp-btn:hover {
        background: #4338ca;
        box-shadow:
            0 1px 2px rgba(67,56,202,.25),
            0 6px 20px rgba(67,56,202,.45);
        transform: translateY(-1px);
    }
    .lp-btn:active {
        transform: translateY(0);
        box-shadow:
            0 1px 2px rgba(79,70,229,.2),
            0 2px 8px rgba(79,70,229,.25);
    }

    /* Divider */
    .lp-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 4px;
    }
    .lp-divider::before,
    .lp-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #f3f4f6;
    }
    .lp-divider span {
        font-size: 11px;
        color: #d1d5db;
        font-weight: 500;
        white-space: nowrap;
    }

    /* Footer */
    .lp-footer {
        font-size: 11.5px;
        color: #d1d5db;
        padding-top: 12px;
        font-weight: 400;
        letter-spacing: .01em;
        width: 100%;
        max-width: 100%;
        text-align: center;
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .lp-left  { display: none; }
        .lp-right { flex: 1; border-left: none; padding: 36px 28px; }
        .lp-form-wrap { max-width: 100%; }
    }
    </style>
</head>
<body>

    {{-- ══ Left panel — illustration ══════════════════════ --}}
    <div class="lp-left">

        <div class="lp-ring lp-ring-1"></div>
        <div class="lp-ring lp-ring-2"></div>
        <div class="lp-ring lp-ring-3"></div>
        <div class="lp-blob lp-blob-1"></div>
        <div class="lp-blob lp-blob-2"></div>

        {{-- Dashboard mockup --}}
        <div class="lp-mockup">

            {{-- Browser chrome --}}
            <div class="lp-chrome">
                <div class="lp-dot" style="background:#f87171"></div>
                <div class="lp-dot" style="background:#fbbf24"></div>
                <div class="lp-dot" style="background:#34d399;margin-right:10px"></div>
                <div style="flex:1;height:18px;background:#eeeef0;border-radius:5px;display:flex;align-items:center;padding:0 8px">
                    <span style="font-size:8px;color:#9ca3af;font-weight:500">inventory.app / dashboard</span>
                </div>
            </div>

            {{-- App nav --}}
            <div class="lp-app-nav">
                <div class="lp-app-nav-left">
                    <span>Kho Tổng 40</span>
                    <span class="sep">/</span>
                    <span class="active">Tổng quan</span>
                </div>
                <div class="lp-app-nav-right">
                    <div class="lp-nav-icon"><i class="bi bi-bell"></i></div>
                    <div class="lp-nav-icon" style="background:#4f46e5;color:#fff"><i class="bi bi-person"></i></div>
                </div>
            </div>

            {{-- Dashboard body --}}
            <div class="lp-mock-inner">
                <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:10px">
                    Hôm nay — {{ now()->format('d/m/Y') }}
                </div>

                {{-- Stat cards + chart --}}
                <div class="lp-mock-row">
                    <div class="lp-stat">
                        <div class="lp-stat-l">Phiếu nhập</div>
                        <div class="lp-stat-v">142 <small>p</small></div>
                        <div class="lp-stat-tag lp-tag-up">+12%</div>
                    </div>
                    <div class="lp-stat">
                        <div class="lp-stat-l">Phiếu xuất</div>
                        <div class="lp-stat-v">89 <small>p</small></div>
                        <div class="lp-stat-tag lp-tag-up">+8%</div>
                    </div>
                    <div class="lp-stat">
                        <div class="lp-stat-l">Giá trị tồn</div>
                        <div class="lp-stat-v">2.4 <small>tỷ</small></div>
                        <div class="lp-stat-tag lp-tag-neu">Kho 40</div>
                    </div>
                    <div class="lp-stat">
                        <div class="lp-stat-l">Sản phẩm</div>
                        <div class="lp-stat-v">318 <small>SKU</small></div>
                        <div class="lp-stat-tag lp-tag-up">+5</div>
                    </div>
                    {{-- Mini bar chart --}}
                    <div class="lp-chart-wrap">
                        <div class="lp-chart-bars">
                            <div class="lp-bar"><div class="lp-bar-fill" style="height:58%"></div></div>
                            <div class="lp-bar"><div class="lp-bar-fill" style="height:74%"></div></div>
                            <div class="lp-bar"><div class="lp-bar-fill" style="height:46%"></div></div>
                            <div class="lp-bar"><div class="lp-bar-fill" style="height:86%"></div></div>
                            <div class="lp-bar"><div class="lp-bar-fill" style="height:62%"></div></div>
                            <div class="lp-bar"><div class="lp-bar-fill" style="height:38%"></div></div>
                            <div class="lp-bar"><div class="lp-bar-fill today" style="height:78%"></div></div>
                        </div>
                        <div class="lp-chart-x">
                            <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
                        </div>
                    </div>
                </div>

                {{-- Transaction table --}}
                <table class="lp-tbl">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Loại</th>
                            <th>SL</th>
                            <th>Điểm nhận</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Bia Heineken 330ml</td>
                            <td><span class="lp-badge lp-badge-in">Nhập</span></td>
                            <td>240</td>
                            <td style="color:#9ca3af">—</td>
                            <td><span class="lp-badge lp-badge-ok">Duyệt</span></td>
                        </tr>
                        <tr>
                            <td>Rượu Chivas 12 năm</td>
                            <td><span class="lp-badge lp-badge-out">Xuất</span></td>
                            <td>12</td>
                            <td>Kho 43</td>
                            <td><span class="lp-badge lp-badge-ok">Duyệt</span></td>
                        </tr>
                        <tr>
                            <td>Nước ngọt Pepsi 1.5L</td>
                            <td><span class="lp-badge lp-badge-in">Nhập</span></td>
                            <td>480</td>
                            <td style="color:#9ca3af">—</td>
                            <td><span class="lp-badge lp-badge-ok">Duyệt</span></td>
                        </tr>
                        <tr>
                            <td>Bia Tiger lon 330ml</td>
                            <td><span class="lp-badge lp-badge-out">Xuất</span></td>
                            <td>96</td>
                            <td>Kho 44</td>
                            <td><span class="lp-badge lp-badge-ok">Duyệt</span></td>
                        </tr>
                        <tr>
                            <td>Johnnie Walker Black</td>
                            <td><span class="lp-badge lp-badge-in">Nhập</span></td>
                            <td>36</td>
                            <td style="color:#9ca3af">—</td>
                            <td><span class="lp-badge lp-badge-ok">Duyệt</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tagline --}}
        <div class="lp-tagline" data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
            <h2>Quản lý kho<br><span>hiệu quả và chính xác.</span></h2>
            <p>Nhập xuất, kiểm kê, báo cáo tồn kho<br>nhanh chóng, mọi lúc mọi nơi.</p>
        </div>

        <div class="lp-pills" data-aos="fade-up" data-aos-duration="600" data-aos-delay="320">
            <div class="lp-pill"><span class="lp-pill-dot"></span>Nhập / Xuất kho</div>
            <div class="lp-pill"><span class="lp-pill-dot"></span>Kiểm kê tồn kho</div>
            <div class="lp-pill"><span class="lp-pill-dot"></span>Báo cáo chi tiết</div>
            <div class="lp-pill"><span class="lp-pill-dot"></span>Phân quyền RBAC</div>
        </div>

    </div>

    {{-- ══ Right panel — form ══════════════════════════════ --}}
    <div class="lp-right">

        <div class="lp-form-wrap">

            {{-- Logo --}}
            <div class="lp-logo" data-aos="fade-down" data-aos-duration="600">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
                {{-- <span class="lp-logo-name">{{ config('app.name') }}</span> --}}
            </div>

            {{-- <div class="lp-eyebrow" data-aos="fade-up" data-aos-duration="500" data-aos-delay="80">Hệ thống quản lý kho</div> --}}

            <h1 class="lp-heading" data-aos="fade-up" data-aos-duration="500" data-aos-delay="140"><span style="color:#4f46e5; font-size: 46px;">Chào mừng</span><br><span>TON-Inventory</span></h1>
            <p class="lp-sub" data-aos="fade-up" data-aos-duration="500" data-aos-delay="180">Nhập thông tin đăng nhập để truy cập hệ thống.</p>

            @if($errors->any())
            <div class="lp-error" data-aos="fade-up" data-aos-duration="400">
                <i class="bi bi-shield-exclamation"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="lp-form">
                @csrf

                <div class="lp-field" data-aos="fade-up" data-aos-duration="500" data-aos-delay="220">
                    <label for="email">Email</label>
                    <div class="lp-inp-wrap">
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               placeholder="admin@company.com" required autofocus
                               class="{{ $errors->has('email') ? 'err' : '' }}">
                    </div>
                </div>

                <div class="lp-field" x-data="{ show: false }" data-aos="fade-up" data-aos-duration="500" data-aos-delay="270">
                    <label for="password">Mật khẩu</label>
                    <div class="lp-inp-wrap">
                        <input id="password" :type="show ? 'text' : 'password'" name="password"
                               placeholder="••••••••" required>
                        <button type="button" @click="show = !show" class="lp-eye" tabindex="-1">
                            <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="lp-row" data-aos="fade-up" data-aos-duration="500" data-aos-delay="310">
                    <label class="lp-check">
                        <input type="checkbox" name="remember">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                </div>

                <button type="submit" class="lp-btn" data-aos="fade-up" data-aos-duration="500" data-aos-delay="360">Đăng nhập</button>

            </form>

        </div>

        <div class="lp-footer" data-aos="fade-up" data-aos-duration="400" data-aos-delay="420">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>

    </div>

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            easing: 'ease-out-cubic',
            offset: 0,
        });
    </script>
</body>
</html>
