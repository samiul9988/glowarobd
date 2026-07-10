<style>
    /* ========================================================
   Applicant Details — Custom Styles (Bootstrap 4 + Custom)
   ======================================================== */

    :root {
        --bg: #f5f6f8;
        --surface: #ffffff;
        --border: #e6e8ec;
        --border-strong: #d6d9df;
        --text: #0f172a;
        --text-2: #374151;
        --muted: #6b7280;
        --primary: #0f172a;
        --accent: #2563eb;
        --success: #16a34a;
        --success-soft: #ecfdf5;
        --warning: #d97706;
        --warning-soft: #fff7ed;
        --dangerr: #dc2626;
        --dangerr-soft: #fef2f2;
        --info: #0ea5e9;
        --info-soft: #eff6ff;
        --yellow-soft: #fefce8;
        --green-soft: #e8feea;
        --radius: 14px;
        --shadow-sm: 0 1px 2px rgba(15, 23, 42, .04);
        --shadow-md: 0 4px 14px rgba(15, 23, 42, .06);
    }

    * {
        box-sizing: border-box
    }

    html,
    body {
        height: 100%
    }

    body {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif !important;
        background: var(--bg);
        color: var(--text);
        font-size: 14px;
        letter-spacing: -0.1px;
        -webkit-font-smoothing: antialiased;
    }

    a {
        color: var(--accent)
    }

    a:hover {
        text-decoration: none;
        color: var(--primary)
    }

    /* ===== Topbar ===== */
    .topbar {
        background: #fff;
        border-bottom: 1px solid var(--border);
        padding: 14px 0;
        position: sticky;
        top: 0;
        z-index: 50;
    }

    .topbar .brand {
        font-weight: 700;
        letter-spacing: -0.3px;
        font-size: 15px;
        color: var(--text)
    }

    .topbar .crumbs {
        color: var(--muted);
        font-size: 12.5px;
        margin-top: 2px
    }

    .topbar .crumbs a {
        color: var(--muted)
    }

    .topbar .crumbs a:hover {
        color: var(--text)
    }

    .btn-ghost {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 14px;
        transition: all .15s;
    }

    .btn-ghost:hover {
        border-color: var(--primary);
        background: #fff
    }

    /* ===== Card ===== */
    .card-soft {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
    }

    .card-soft .card-head {
        border-bottom: 1px solid var(--border);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 600;
        font-size: 14px;
    }

    .card-soft .card-head .muted {
        color: var(--muted);
        font-weight: 500;
        font-size: 12.5px
    }

    .card-soft .card-body-p {
        padding: 20px
    }

    /* ===== Profile ===== */
    .profile-head {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    .job-application-avatar {
        flex: 0 0 auto;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #111827, #374151);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 22px;
        letter-spacing: 0.5px;
    }

    .profile-info {
        flex: 1 1 260px;
        min-width: 0
    }

    .name {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        color: var(--text)
    }

    .sub {
        color: var(--muted);
        font-size: 13px
    }

    .meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px
    }

    .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f3f4f6;
        color: #374151;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }

    .meta-pill i {
        font-size: 11px;
        color: #6b7280
    }

    /* Score ring */
    .score-wrap {
        flex: 0 0 auto;
        text-align: center;
        min-width: 90px
    }

    .score-ring {
        --val: 100;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: conic-gradient(var(--success) calc(var(--val)*1%), #e5e7eb 0);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin: 0 auto;
    }

    .score-ring::after {
        content: "";
        position: absolute;
        inset: 6px;
        background: #fff;
        border-radius: 50%;
    }

    .score-ring span {
        position: relative;
        z-index: 1;
        font-weight: 700;
        font-size: 13px;
        color: var(--text)
    }

    /* Status badges */
    .tag-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 18px
    }

    .badge-soft {
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 12px;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-pending {
        background: var(--warning-soft);
        color: #9a3412;
        border-color: #fed7aa
    }

    .badge-confirmed {
        background: var(--info-soft);
        color: #1d4ed8;
        border-color: #bfdbfe
    }

    .badge-hired {
        background: var(--success-soft);
        color: #047857;
        border-color: #a7f3d0
    }

    .badge-rejected {
        background: var(--dangerr-soft);
        color: #b91c1c;
        border-color: #fecaca
    }

    .badge-not-shortlisted {
        background: var(--yellow-soft);
        color: #854d0e;
        border-color: #fde68a
    }

    .badge-shortlisted {
        background: var(--green-soft);
        color: #047857;
        border-color: #a7f3d0
    }

    .badge-neutral {
        background: #f3f4f6;
        color: #374151;
        border-color: #e5e7eb
    }

    /* ===== Status buttons ===== */
    .status-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .status-grid .s-btn {
        border-radius: 12px;
        font-weight: 600;
        font-size: 13px;
        padding: 12px 10px;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all .15s ease;
        width: 100%;
    }

    .status-grid .s-btn:hover {
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm)
    }

    .status-grid .s-btn i {
        font-size: 13px
    }

    .status-grid .s-btn.active {
        color: #fff;
        border-color: transparent
    }

    .status-grid .s-btn.active.s-pending {
        background: var(--warning)
    }

    .status-grid .s-btn.active.s-confirmed {
        background: var(--accent)
    }

    .status-grid .s-btn.active.s-hired {
        background: var(--success)
    }

    .status-grid .s-btn.active.s-rejected {
        background: var(--dangerr)
    }

    .shortlist-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        padding: 8px 12px;
        border: 1px dashed var(--border-strong);
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: var(--text);
        transition: all .15s;
    }

    .shortlist-toggle:hover {
        border-color: var(--warning);
        color: var(--warning)
    }

    .shortlist-toggle.active {
        background: var(--yellow-soft);
        border-color: #fde68a;
        color: #854d0e;
        border-style: solid
    }

    .shortlist-toggle i {
        color: #d97706
    }

    /* ===== Q&A ===== */
    .qa-item {
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fafbfc;
    }

    .qa-label {
        font-weight: 600;
        margin-bottom: 4px;
        color: var(--text);
        font-size: 14px
    }

    .qa-meta {
        color: var(--muted);
        font-size: 12px
    }

    .answer-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--success-soft);
        color: #047857;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        border: 1px solid #a7f3d0;
    }

    .expected-row {
        margin-top: 12px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px
    }

    .expected-chip {
        display: inline-block;
        background: #fff;
        color: #374151;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid var(--border);
    }

    /* ===== Notes ===== */
    .note {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 10px;
        position: relative;
        transition: border-color .15s;
    }

    .note:hover {
        border-color: var(--border-strong)
    }

    .note-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
        gap: 10px
    }

    .note-author {
        display: flex;
        align-items: center;
        gap: 8px
    }

    .note-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #eef2f7;
        color: #374151;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 11px;
    }

    .note-who {
        font-weight: 600;
        font-size: 13px;
        color: var(--text)
    }

    .note-when {
        color: var(--muted);
        font-size: 12px
    }

    .note-text {
        color: var(--text-2);
        line-height: 1.55;
        font-size: 13.5px;
        margin: 0
    }

    .note-actions {
        display: flex;
        gap: 4px
    }

    .icon-btn {
        border: none;
        background: transparent;
        color: var(--muted);
        width: 30px;
        height: 30px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
    }

    .icon-btn:hover {
        background: #fef2f2;
        color: var(--dangerr)
    }

    .note-form textarea {
        border-radius: 12px;
        border: 1px solid var(--border);
        resize: vertical;
        min-height: 80px;
        font-family: inherit;
        padding: 10px 12px;
        width: 100%;
        font-size: 13.5px;
        color: var(--text);
        background: #fff;
    }

    .note-form textarea:focus {
        border-color: var(--primary);
        box-shadow: none;
        outline: none
    }

    .btn-dark-soft {
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        padding: 9px 16px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all .15s;
    }

    .btn-dark-soft:hover {
        background: #000;
        color: #fff
    }

    /* ===== Sidebar actions ===== */
    .action-btn {
        width: 100%;
        text-align: left;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: #fff;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all .15s;
        color: var(--text);
        font-size: 13px;
    }

    .action-btn:hover {
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm)
    }

    .action-btn .ico {
        flex: 0 0 auto;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #111827;
        font-size: 14px;
    }

    .action-btn .label-wrap {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1
    }

    .action-btn .label-sub {
        font-weight: 400;
        font-size: 12px;
        color: var(--muted);
        margin-top: 2px
    }

    .action-btn.primary {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary)
    }

    .action-btn.primary .ico {
        background: rgba(255, 255, 255, .12);
        color: #fff
    }

    .action-btn.primary .label-sub {
        color: rgba(255, 255, 255, .75)
    }

    .action-btn.success {
        background: var(--success);
        color: #fff;
        border-color: var(--success)
    }

    .action-btn.success .ico {
        background: rgba(255, 255, 255, .18);
        color: #fff
    }

    .action-btn.success .label-sub {
        color: rgba(255, 255, 255, .85)
    }

    .action-btn.accent {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent)
    }

    .action-btn.accent .ico {
        background: rgba(255, 255, 255, .18);
        color: #fff
    }

    .action-btn.accent .label-sub {
        color: rgba(255, 255, 255, .85)
    }

    .action-btn.warning {
        background: var(--warning);
        color: #fff;
        border-color: var(--warning)
    }

    .action-btn.warning .ico {
        background: rgba(255, 255, 255, .18);
        color: #fff
    }

    .action-btn.warning .label-sub {
        color: rgba(255, 255, 255, .85)
    }

    /* ===== Job summary ===== */
    .job-title {
        font-weight: 700;
        font-size: 15px;
        margin: 0 0 4px;
        color: var(--text)
    }

    .job-sub {
        color: var(--muted);
        font-size: 12.5px;
        margin-bottom: 14px
    }

    .job-meta {
        list-style: none;
        padding: 0;
        margin: 0
    }

    .job-meta li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px dashed var(--border);
        font-size: 13px;
    }

    .job-meta li:last-child {
        border-bottom: none
    }

    .job-meta .lbl {
        color: var(--muted);
        display: inline-flex;
        align-items: center;
        gap: 6px
    }

    .job-meta .val {
        font-weight: 600;
        color: var(--text);
        text-align: right
    }

    .btn-block-soft {
        display: block;
        width: 100%;
        margin-top: 14px;
        background: #f3f4f6;
        color: var(--text);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all .15s;
    }

    .btn-block-soft:hover {
        background: #eaecef
    }

    /* ===== Activity timeline ===== */
    .tl-item {
        display: flex;
        gap: 12px;
        padding: 8px 0
    }

    .tl-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 13px;
    }

    .tl-icon.success {
        background: var(--success-soft);
        color: #047857
    }

    .tl-icon.warning {
        background: var(--yellow-soft);
        color: #854d0e
    }

    .tl-icon.info {
        background: var(--info-soft);
        color: #1d4ed8
    }

    .tl-title {
        font-weight: 600;
        font-size: 13px;
        color: var(--text)
    }

    .tl-time {
        color: var(--muted);
        font-size: 12px;
        margin-top: 2px
    }

    /* ===== Section title (small caps) ===== */
    .section-title {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--muted);
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
    }

    /* ===== Modal ===== */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px rgba(15, 23, 42, .18)
    }

    .modal-header {
        border-bottom: 1px solid var(--border);
        padding: 18px 22px
    }

    .modal-header .modal-title {
        font-weight: 700;
        font-size: 15px
    }

    .modal-body {
        padding: 22px
    }

    .modal-footer {
        border-top: 1px solid var(--border);
        padding: 14px 22px
    }

    .modal .form-control {
        border-radius: 10px;
        border: 1px solid var(--border);
        padding: 10px 12px;
        font-size: 13.5px
    }

    .modal .form-control:focus {
        border-color: var(--primary);
        box-shadow: none
    }

    .modal label {
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 6px
    }

    .btn-success-solid {
        background: var(--success);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 9px 16px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .btn-success-solid:hover {
        background: #15803d;
        color: #fff
    }

    .btn-light-bordered {
        background: #fff;
        color: var(--text);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 9px 16px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
    }

    .btn-light-bordered:hover {
        background: #f3f4f6
    }

    /* ===== Description content ===== */
    .description-html {
        font-size: 13.5px;
        color: var(--text-2);
        max-height: 280px;
        overflow: auto;
        padding-right: 8px
    }

    .description-html h4 {
        font-size: 14px;
        font-weight: 700;
        margin: 18px 0 8px;
        color: var(--text)
    }

    .description-html h4:first-child {
        margin-top: 0
    }

    .description-html p {
        margin: 0 0 10px
    }

    .description-html ul {
        padding-left: 20px;
        margin: 0 0 10px
    }

    .description-html li {
        margin-bottom: 4px
    }

    /* ===== Responsive ===== */
    @media (max-width: 991.98px) {
        .status-grid {
            grid-template-columns: repeat(2, 1fr)
        }
    }

    @media (max-width: 575.98px) {
        .topbar .crumbs {
            display: none
        }

        .profile-head {
            align-items: flex-start
        }

        .score-wrap {
            margin-top: 8px
        }
    }

    /* ===== Custom modal overlay (lightweight, no Bootstrap JS needed) ===== */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .45);
        display: none;
        align-items: flex-start;
        justify-content: center;
        z-index: 1000;
        padding: 60px 16px 20px;
        overflow-y: auto;
    }

    .modal-overlay.open {
        display: flex
    }

    .modal-card {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 560px;
        box-shadow: 0 20px 60px rgba(15, 23, 42, .25);
        overflow: hidden;
        animation: popin .18s ease;
    }

    .modal-card.lg {
        max-width: 720px
    }

    @keyframes popin {
        from {
            opacity: 0;
            transform: translateY(8px) scale(.98)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .modal-card .m-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
    }

    .modal-card .m-head h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        display: inline-flex;
        align-items: center;
        gap: 8px
    }

    .modal-card .m-body {
        padding: 20px
    }

    .modal-card .m-foot {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 14px 20px;
        border-top: 1px solid var(--border);
    }

    .m-close {
        background: transparent;
        border: none;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        color: var(--muted);
        cursor: pointer;
        font-size: 16px;
    }

    .m-close:hover {
        background: #f3f4f6;
        color: var(--text)
    }

    .form-group-c {
        margin-bottom: 14px
    }

    .form-group-c label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .input-c,
    .textarea-c,
    .select-c {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13.5px;
        color: var(--text);
        background: #fff;
        font-family: inherit;
    }

    .textarea-c {
        min-height: 90px;
        resize: vertical
    }

    .input-c:focus,
    .textarea-c:focus,
    .select-c:focus {
        outline: none;
        border-color: var(--primary)
    }

    .row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px
    }

    @media (max-width:520px) {
        .row-2 {
            grid-template-columns: 1fr
        }
    }

    .checkbox-c {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-2);
        cursor: pointer
    }

    .checkbox-c input {
        width: 16px;
        height: 16px;
        accent-color: var(--primary)
    }

    .help-text {
        color: var(--muted);
        font-size: 12px;
        margin-top: 4px
    }

    /* ===== Safety grid fallback (works even if Bootstrap CDN fails) ===== */
    .container {
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
        padding-left: 16px;
        padding-right: 16px
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -12px;
        margin-right: -12px
    }

    .col-lg-8,
    .col-lg-4 {
        padding-left: 12px;
        padding-right: 12px;
        width: 100%
    }

    @media (min-width:992px) {
        .col-lg-8 {
            flex: 0 0 66.6667%;
            max-width: 66.6667%
        }

        .col-lg-4 {
            flex: 0 0 33.3333%;
            max-width: 33.3333%
        }
    }

    .py-4 {
        padding-top: 1.5rem;
        padding-bottom: 1.5rem
    }

    .mt-1 {
        margin-top: .25rem !important
    }

    .mt-2 {
        margin-top: .5rem !important
    }

    .mt-4 {
        margin-top: 1.5rem !important
    }

    .mr-1 {
        margin-right: .25rem !important
    }

    .mr-2 {
        margin-right: .5rem !important
    }

    .d-flex {
        display: flex !important
    }

    .align-items-center {
        align-items: center !important
    }

    .align-items-start {
        align-items: flex-start !important
    }

    .justify-content-between {
        justify-content: space-between !important
    }

    .justify-content-end {
        justify-content: flex-end !important
    }
</style>
