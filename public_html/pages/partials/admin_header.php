<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e((string) ($adminTitle ?? 'Khu vực quản trị')); ?> | Trung tâm Anh ngữ</title>
    <meta name="description" content="Khu vực điều hành nội bộ cho Admin và Staff.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <?php require_once __DIR__ . '/tailwind_cdn.php'; ?>
    <style>
        .admin-ui article > h3 {
            margin-bottom: 0.9rem;
            font-family: "Sora", ui-sans-serif, system-ui, sans-serif;
            font-size: 1.02rem;
            font-weight: 700;
            color: #0f172a;
        }

        .admin-ui form label {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #334155;
        }

        .admin-ui form label.inline-flex {
            display: inline-flex;
            align-items: center;
        }

        .admin-ui form label > input:not([type='checkbox']):not([type='hidden']),
        .admin-ui form label > select,
        .admin-ui form label > textarea {
            margin-top: 0.45rem;
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            padding: 0.62rem 0.78rem;
            font-size: 0.92rem;
            line-height: 1.35;
            color: #0f172a;
            transition: border-color 120ms ease, box-shadow 120ms ease, background-color 120ms ease;
        }

        .admin-ui form label > textarea {
            min-height: 6.5rem;
            resize: vertical;
        }

        .admin-ui form label > input:not([type='checkbox']):not([type='hidden']):focus,
        .admin-ui form label > select:focus,
        .admin-ui form label > textarea:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
            background: #f8fbff;
        }

        .admin-ui form input[type='checkbox'] {
            margin-right: 0.45rem;
            height: 1rem;
            width: 1rem;
            accent-color: #2563eb;
            vertical-align: middle;
        }

        .admin-ui form input::placeholder,
        .admin-ui form textarea::placeholder {
            color: #94a3b8;
        }

        .admin-ui {
            min-width: 0;
            width: 100%;
            max-width: 100%;
            grid-template-columns: minmax(0, 1fr);
        }

        .admin-ui > * {
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }

        .admin-ui .overflow-x-auto > table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        .admin-ui .overflow-x-auto > table thead th {
            padding: 0.72rem 0.82rem;
            border-bottom: 1px solid #dbe4f0;
            background: #f8fafc;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.035em;
            text-transform: uppercase;
            color: #475569;
            white-space: nowrap;
        }

        .admin-ui .overflow-x-auto > table tbody td {
            padding: 0.78rem 0.82rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 0.86rem;
            line-height: 1.45;
            color: #1e293b;
            word-break: break-word;
        }

        .admin-ui .overflow-x-auto > table tbody tr:hover td {
            background: #f8fafc;
        }

        .admin-ui .overflow-x-auto > table thead th:last-child,
        .admin-ui .overflow-x-auto > table tbody td:last-child {
            text-align: left;
        }

        .admin-ui .overflow-x-auto > table thead th:last-child {
            width: 13.5rem;
            white-space: nowrap;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child {
            white-space: normal;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child > .inline-flex,
        .admin-ui .overflow-x-auto > table tbody td:last-child > span.inline-flex,
        .admin-ui .overflow-x-auto > table tbody td:last-child > div.inline-flex {
            width: 100%;
            justify-content: flex-start;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child form {
            margin: 0;
        }

        .admin-ui .overflow-x-auto > table tbody td a:not([class]) {
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: none;
        }

        .admin-ui .overflow-x-auto > table tbody td a:not([class]):hover {
            text-decoration: underline;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child a,
        .admin-ui .overflow-x-auto > table tbody td:last-child button,
        .admin-ui .overflow-x-auto > table tbody td:last-child input[type='submit'],
        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-row-detail-button {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 0.65rem;
            background: #ffffff;
            color: #334155;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.2;
            min-height: 2rem;
            padding: 0.3rem 0.5rem;
            text-decoration: none !important;
            transition: border-color 120ms ease, background-color 120ms ease, color 120ms ease;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child a:hover,
        .admin-ui .overflow-x-auto > table tbody td:last-child button:hover,
        .admin-ui .overflow-x-auto > table tbody td:last-child input[type='submit']:hover,
        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-row-detail-button:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child form[action*='delete'] button,
        .admin-ui .overflow-x-auto > table tbody td:last-child form[action*='request-delete'] button {
            border-color: #fecaca;
            background: #fef2f2;
            color: #b91c1c;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child form[action*='delete'] button:hover,
        .admin-ui .overflow-x-auto > table tbody td:last-child form[action*='request-delete'] button:hover {
            border-color: #fca5a5;
            background: #fee2e2;
            color: #991b1b;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-action-icon-btn {
            width: 2rem;
            min-width: 2rem;
            height: 2rem;
            min-height: 2rem;
            padding: 0;
            border-radius: 0.6rem;
            overflow: hidden;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-action-icon-btn[data-action-kind='detail'],
        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-action-icon-btn[data-action-kind='edit'] {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-action-icon-btn[data-action-kind='save'] {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-action-icon-btn[data-action-kind='delete'],
        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-action-icon-btn[data-action-kind='lock'] {
            border-color: #fecaca;
            background: #fef2f2;
            color: #b91c1c;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-action-icon-btn[data-action-kind='request'] {
            border-color: #fcd34d;
            background: #fffbeb;
            color: #b45309;
        }

        .admin-ui .overflow-x-auto > table tbody td:last-child .admin-action-icon-btn:hover {
            transform: translateY(-1px);
        }

        .admin-ui .admin-action-icon-label {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
            white-space: nowrap;
        }

        .admin-ui .admin-action-icon-glyph {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1rem;
            height: 1rem;
        }

        .admin-ui .admin-action-icon-glyph svg {
            width: 1rem;
            height: 1rem;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .admin-ui .table-filter-bar {
            margin-bottom: 0.75rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #dbe4f0;
            border-radius: 0.8rem;
            background: #f8fafc;
            padding: 0.6rem;
        }

        .admin-ui .table-filter-controls {
            display: flex;
            flex: 1 1 500px;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .admin-ui .table-filter-controls input,
        .admin-ui .table-filter-controls select {
            border: 1px solid #cbd5e1;
            border-radius: 0.65rem;
            background: #ffffff;
            color: #0f172a;
            font-size: 0.82rem;
            line-height: 1.3;
            padding: 0.5rem 0.65rem;
        }

        .admin-ui .table-filter-controls input {
            flex: 1 1 280px;
            min-width: 220px;
        }

        .admin-ui .table-filter-controls select {
            flex: 0 1 220px;
            min-width: 170px;
        }

        .admin-ui .table-filter-controls button {
            border: 1px solid #cbd5e1;
            border-radius: 0.65rem;
            background: #ffffff;
            color: #334155;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.5rem 0.7rem;
        }

        .admin-ui .table-filter-controls button:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .admin-ui .table-filter-counter {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
        }

        .admin-ui .admin-row-detail-button {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .admin-ui .is-pending,
        .admin-ui .is-upcoming,
        .admin-ui .is-trial {
            border-color: #fcd34d !important;
            background: #fffbeb !important;
            color: #b45309 !important;
        }

        .admin-ui .is-paid,
        .admin-ui .is-success,
        .admin-ui .is-approved,
        .admin-ui .is-active,
        .admin-ui .is-reviewed,
        .admin-ui .is-finished,
        .admin-ui .is-ongoing {
            border-color: #86efac !important;
            background: #f0fdf4 !important;
            color: #166534 !important;
        }

        .admin-ui .is-debt,
        .admin-ui .is-failed,
        .admin-ui .is-inactive,
        .admin-ui .is-rejected,
        .admin-ui .is-closed {
            border-color: #fca5a5 !important;
            background: #fef2f2 !important;
            color: #b91c1c !important;
        }

        body.admin-modal-open {
            overflow: hidden;
        }

        .admin-edit-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(1px);
        }

        .admin-edit-modal-backdrop.hidden {
            display: none;
        }

        .admin-edit-modal-dialog {
            width: min(920px, 100%);
            max-height: calc(100vh - 2rem);
            border-radius: 1rem;
            border: 1px solid #dbe4f0;
            background: #ffffff;
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.26);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .admin-edit-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0.75rem 0.9rem;
        }

        .admin-edit-modal-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
        }

        .admin-edit-modal-close {
            border: 1px solid #cbd5e1;
            border-radius: 0.65rem;
            background: #ffffff;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.4rem 0.6rem;
        }

        .admin-edit-modal-close:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .admin-edit-modal-body {
            overflow: auto;
            padding: 1rem;
            background: #ffffff;
        }

        .admin-edit-modal-body .admin-modal-helper {
            margin-bottom: 0.8rem;
            font-size: 0.74rem;
            font-weight: 700;
            color: #64748b;
        }

        .admin-edit-modal-body .admin-record-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.65rem;
        }

        .admin-edit-modal-body .admin-record-detail-item {
            border: 1px solid #dbe4f0;
            border-radius: 0.75rem;
            background: #f8fafc;
            padding: 0.65rem 0.75rem;
        }

        .admin-edit-modal-body .admin-record-detail-item dt {
            margin: 0 0 0.35rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #64748b;
        }

        .admin-edit-modal-body .admin-record-detail-item dd {
            margin: 0;
            font-size: 0.86rem;
            line-height: 1.45;
            color: #0f172a;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .admin-edit-modal-body .admin-record-detail-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 0.8rem;
            background: #f8fafc;
            color: #475569;
            font-size: 0.86rem;
            font-weight: 700;
            padding: 1rem;
            text-align: center;
        }

        .admin-edit-modal-loading {
            border: 1px dashed #cbd5e1;
            border-radius: 0.8rem;
            background: #f8fafc;
            color: #475569;
            font-size: 0.86rem;
            font-weight: 700;
            padding: 1rem;
            text-align: center;
        }

        .admin-edit-modal-error {
            border: 1px solid #fecaca;
            border-radius: 0.8rem;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 0.9rem;
        }

        .admin-edit-modal-body form[data-readonly-form='1'] label {
            color: #475569;
        }

        .admin-edit-modal-body form[data-readonly-form='1'] input:not([type='checkbox']):not([type='radio']):not([type='hidden']),
        .admin-edit-modal-body form[data-readonly-form='1'] select,
        .admin-edit-modal-body form[data-readonly-form='1'] textarea {
            border-color: #dbe4f0;
            background: #f8fafc;
            color: #334155;
        }

        .admin-edit-modal-body form[data-readonly-form='1'] input[readonly],
        .admin-edit-modal-body form[data-readonly-form='1'] textarea[readonly] {
            pointer-events: none;
        }

        .admin-edit-modal-body form a {
            display: none !important;
        }

        .admin-shell {
            display: grid;
            min-height: 100vh;
            grid-template-columns: 1fr;
        }

        .admin-sidebar {
            border-bottom: 1px solid #dbe4f0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .admin-sidebar-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
        }

        .admin-sidebar-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
        }

        .admin-sidebar-brand:hover .admin-sidebar-brand-text {
            color: #1d4ed8;
        }

        .admin-sidebar-brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 800;
            box-shadow: 0 10px 20px rgba(29, 78, 216, 0.25);
        }

        .admin-sidebar-brand-text {
            color: #0f172a;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            transition: color 150ms ease;
        }

        .admin-sidebar-toggle,
        .admin-main-sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.05rem;
            min-width: 2.05rem;
            height: 2.05rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.72rem;
            background: #ffffff;
            color: #334155;
            transition: border-color 150ms ease, background-color 150ms ease, color 150ms ease;
        }

        .admin-sidebar-toggle:hover,
        .admin-main-sidebar-toggle:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .admin-sidebar-toggle svg,
        .admin-main-sidebar-toggle svg {
            width: 1rem;
            height: 1rem;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: transform 160ms ease;
        }

        .admin-sidebar-profile {
            border: 1px solid #dbe4f0;
            border-radius: 0.95rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 0.9rem;
            display: grid;
            gap: 0.2rem;
        }

        .admin-sidebar-profile strong {
            color: #0f172a;
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .admin-sidebar-profile small {
            color: #475569;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .admin-sidebar-nav {
            display: grid;
            gap: 0.42rem;
        }

        .admin-sidebar-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.72rem;
            border: 1px solid #d1dae7;
            border-radius: 0.82rem;
            background: #f8fafc;
            color: #1e293b;
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1.25;
            padding: 0.7rem 0.78rem;
            text-decoration: none;
            transition: border-color 150ms ease, background-color 150ms ease, color 150ms ease, box-shadow 150ms ease, transform 150ms ease;
        }

        .admin-sidebar-link:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
            transform: translateY(-1px);
        }

        .admin-sidebar-link.is-active {
            border-color: #93c5fd;
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 65%, #f8fbff 100%);
            color: #1e3a8a;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.16);
        }

        .admin-sidebar-link.is-active::before {
            content: '';
            position: absolute;
            left: 0.36rem;
            top: 0.48rem;
            bottom: 0.48rem;
            width: 0.22rem;
            border-radius: 999px;
            background: #1d4ed8;
        }

        .admin-sidebar-link-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.78rem;
            min-width: 1.78rem;
            height: 1.78rem;
            border-radius: 0.62rem;
            background: #e2e8f0;
            color: #334155;
            transition: background-color 150ms ease, color 150ms ease;
        }

        .admin-sidebar-link:hover .admin-sidebar-link-icon {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .admin-sidebar-link.is-active .admin-sidebar-link-icon {
            background: #2563eb;
            color: #ffffff;
        }

        .admin-sidebar-link-icon svg {
            width: 1rem;
            height: 1rem;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .admin-sidebar-link-label {
            min-width: 0;
        }

        .admin-sidebar-actions {
            margin-top: auto;
            display: grid;
            gap: 0.55rem;
            padding-top: 0.1rem;
        }

        .admin-main-sidebar-toggle {
            display: none;
        }

        .admin-page-hero {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.9rem;
            border: 1px solid #dbe4f0;
            border-radius: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 75%, #f1f5f9 100%);
            padding: 0.95rem 1rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
        }

        .admin-page-hero-content {
            min-width: 0;
            display: grid;
            gap: 0.3rem;
            max-width: min(78ch, 100%);
        }

        .admin-page-hero-title-row {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
        }

        .admin-page-title {
            margin: 0;
            font-family: "Sora", ui-sans-serif, system-ui, sans-serif;
            font-size: clamp(1.2rem, 1.62vw, 1.46rem);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: 0.004em;
            color: #0f172a;
            text-wrap: balance;
        }

        .admin-page-description {
            margin: 0;
            color: #334155;
            font-size: 0.91rem;
            font-weight: 500;
            line-height: 1.54;
            text-wrap: pretty;
        }

        .admin-page-hero-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .admin-page-profile-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #ffffff;
            color: #1d4ed8;
            font-size: 0.8rem;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;
            padding: 0.56rem 0.9rem;
            transition: border-color 150ms ease, background-color 150ms ease, color 150ms ease;
        }

        .admin-page-profile-link:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1e40af;
        }

        .admin-shell.is-sidebar-collapsed .admin-sidebar-brand-text,
        .admin-shell.is-sidebar-collapsed .admin-sidebar-profile,
        .admin-shell.is-sidebar-collapsed .admin-sidebar-link-label,
        .admin-shell.is-sidebar-collapsed .admin-sidebar-actions {
            display: none;
        }

        .admin-shell.is-sidebar-collapsed .admin-sidebar {
            gap: 0.65rem;
            align-items: center;
            padding: 0.9rem 0.5rem;
        }

        .admin-shell.is-sidebar-collapsed .admin-sidebar-top {
            width: 100%;
            justify-content: center;
            flex-direction: column;
            gap: 0.45rem;
        }

        .admin-shell.is-sidebar-collapsed .admin-sidebar-toggle svg {
            transform: rotate(180deg);
        }

        .admin-shell.is-sidebar-collapsed .admin-sidebar-brand {
            justify-content: center;
        }

        .admin-shell.is-sidebar-collapsed .admin-sidebar-nav {
            width: 100%;
            gap: 0.45rem;
        }

        .admin-shell.is-sidebar-collapsed .admin-sidebar-link {
            justify-content: center;
            padding: 0.55rem;
            border-radius: 0.72rem;
        }

        .admin-shell.is-sidebar-collapsed .admin-sidebar-link.is-active::before {
            left: 0.2rem;
            top: 0.35rem;
            bottom: 0.35rem;
        }

        @media (min-width: 1024px) {
            .admin-shell {
                grid-template-columns: 290px minmax(0, 1fr);
            }

            .admin-shell.is-sidebar-collapsed {
                grid-template-columns: 92px minmax(0, 1fr);
            }

            .admin-sidebar {
                position: sticky;
                top: 0;
                height: 100vh;
                border-right: 1px solid #dbe4f0;
                border-bottom: 0;
                padding: 1.05rem;
            }

            .admin-sidebar-nav {
                overflow: auto;
                max-height: calc(100vh - 235px);
                padding-right: 0.2rem;
            }
        }

        @media (max-width: 1023px) {
            .admin-main-sidebar-toggle {
                display: inline-flex;
            }

            .admin-shell.is-sidebar-collapsed .admin-sidebar {
                display: none;
            }
        }

        @media (max-width: 1024px) {
            .admin-ui .overflow-x-auto > table {
                min-width: 900px;
                table-layout: auto;
            }
        }

        @media (max-width: 640px) {
            .admin-page-hero {
                align-items: flex-start;
                flex-direction: column;
                padding: 0.82rem 0.9rem;
            }

            .admin-page-hero-actions {
                width: 100%;
            }

            .admin-page-profile-link {
                width: 100%;
            }

            .admin-ui .table-filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .admin-ui .table-filter-counter {
                text-align: right;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans leading-relaxed text-slate-900">
<?php
$adminUser = auth_user();
$activeModule = (string) ($module ?? '');
$pageSlug = resolve_page_slug((string) ($_GET['page'] ?? 'dashboard-admin'));
if ($activeModule === '') {
    $moduleByPage = [
        'dashboard-admin' => 'dashboard',
        'users-admin' => 'users',
        'tuition-finance' => 'tuition',
        'registration-finance' => 'registration',
        'promotions-manage' => 'promotions',
        'payments-finance' => 'payments',
        'approvals-manage' => 'approvals',
        'feedbacks-manage' => 'feedbacks',
        'student-leads-manage' => 'student-leads',
        'job-applications-manage' => 'job-applications',
        'activities-manage' => 'activities',
        'bank-manage' => 'bank',
        'courses-academic' => 'courses',
        'roadmaps-academic' => 'roadmaps',
        'classes-academic' => 'classes',
        'classrooms-academic' => 'classrooms',
        'classes-academic-edit' => 'classes',
        'schedules-academic' => 'schedules',
        'schedules-academic-edit' => 'schedules',
        'assignments-academic' => 'assignments',
        'assignments-academic-edit' => 'assignments',
        'materials-academic' => 'materials',
        'materials-academic-edit' => 'materials',
        'portfolios-academic' => 'portfolios',
    ];
    $activeModule = (string) ($moduleByPage[$pageSlug] ?? '');
}

$adminPageTitleMap = [
    'dashboard' => 'Toàn cảnh vận hành',
    'tuition' => 'Học phí và công nợ',
    'registration' => 'Đăng ký khóa học',
    'promotions' => 'Ưu đãi giảm giá',
    'payments' => 'Giao dịch thanh toán',
    'users' => 'Người dùng và phân quyền',
    'approvals' => 'Yêu cầu phê duyệt',
    'feedbacks' => 'Phản hồi học viên',
    'student-leads' => 'Lead học viên',
    'job-applications' => 'Ứng tuyển giáo viên',
    'activities' => 'Hoạt động ngoại khóa',
    'bank' => 'Tài khoản ngân hàng',
    'courses' => 'Danh mục khóa học',
    'roadmaps' => 'Roadmap theo khóa học',
    'classes' => 'Danh mục lớp học',
    'classrooms' => 'Quản lý lớp học',
    'schedules' => 'Kế hoạch lịch dạy',
    'assignments' => 'Hệ thống bài tập',
    'materials' => 'Kho tài liệu',
    'portfolios' => 'Portfolio học viên',
];

$adminPageDescriptionMap = [
    'dashboard' => 'Theo dõi doanh thu, biến động lớp học và thông báo quan trọng để nắm nhanh tình hình vận hành mỗi ngày.',
    'tuition' => 'Theo dõi hóa đơn đã tạo, cập nhật số tiền đã thu và kiểm soát công nợ còn lại theo từng học viên trong từng lớp.',
    'registration' => 'Đăng ký học viên vào khóa/lớp, áp giảm giá và tự động tạo công nợ học phí để theo dõi thu tiền chuẩn nghiệp vụ.',
    'promotions' => 'Thiết lập các chương trình ưu đãi theo khóa học hoặc toàn trung tâm để áp dụng giảm giá tự động khi đăng ký.',
    'payments' => 'Quản lý mã giao dịch, đối soát trạng thái thanh toán và rà soát lịch sử thu học phí một cách tập trung.',
    'users' => 'Quản lý tài khoản, vai trò và hồ sơ theo từng nhóm người dùng để kiểm soát truy cập hệ thống an toàn.',
    'approvals' => 'Tạo yêu cầu, cập nhật trạng thái duyệt và theo dõi người xử lý cho từng quy trình nội bộ của trung tâm.',
    'feedbacks' => 'Thu thập nhận xét học viên, theo dõi điểm đánh giá và xử lý phản hồi theo lớp hoặc giáo viên phụ trách.',
    'student-leads' => 'Theo dõi lead học viên từ lúc gửi form đến khi kiểm tra đầu vào, học thử và chuyển đổi chính thức.',
    'job-applications' => 'Xử lý hồ sơ ứng tuyển giáo viên theo pipeline phỏng vấn và chuyển đổi thành tài khoản giảng dạy.',
    'activities' => 'Lên kế hoạch hoạt động ngoại khóa, theo dõi trạng thái tổ chức và số lượng học viên đăng ký tham gia.',
    'bank' => 'Cấu hình tài khoản nhận tiền, quản lý thông tin QR và thiết lập tài khoản mặc định cho thanh toán học phí.',
    'courses' => 'Quản lý danh mục khóa học, mức học phí cơ bản và số buổi chuẩn để đồng bộ toàn bộ vận hành học vụ.',
    'roadmaps' => 'Thiết kế lộ trình kiến thức theo từng khóa học, làm chuẩn cho giáo án buổi học và kế hoạch giảng dạy.',
    'classes' => 'Theo dõi danh sách lớp, giáo viên phụ trách và trạng thái vận hành để điều phối học vụ chính xác hơn.',
    'classrooms' => 'Lập kế hoạch tiết học theo từng lớp, gắn buổi vào lịch học theo thời khóa biểu tuần và thao tác nhanh theo từng buổi.',
    'schedules' => 'Sắp xếp lịch dạy, phòng học và khung giờ giảng dạy nhằm hạn chế trùng lịch và tối ưu nguồn lực.',
    'assignments' => 'Tạo bài tập, quản lý hạn nộp và kiểm soát tài nguyên đính kèm theo từng buổi học hoặc khóa học.',
    'materials' => 'Quản lý kho tài liệu theo khóa học, cập nhật tệp nhanh và giữ cấu trúc nội dung học tập nhất quán.',
    'portfolios' => 'Tổng hợp sản phẩm học viên, lưu minh chứng tiến bộ và tổ chức nội dung portfolio theo giai đoạn học.',
];

$displayAdminTitle = (string) ($adminPageTitleMap[$activeModule] ?? ($adminTitle ?? 'Khu vực quản trị'));
$displayAdminDescription = trim((string) ($adminDescription ?? ''));
if ($displayAdminDescription === '') {
    $displayAdminDescription = (string) ($adminPageDescriptionMap[$activeModule] ?? 'Quản lý dữ liệu theo từng phân hệ của hệ thống.');
}
?>
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-top">
            <a class="admin-sidebar-brand" href="<?= e(page_url('dashboard-admin')); ?>">
                <span class="admin-sidebar-brand-mark">EC</span>
                <span class="admin-sidebar-brand-text">Khu vực điều hành</span>
            </a>

            <button
                type="button"
                class="admin-sidebar-toggle"
                id="adminSidebarToggle"
                aria-label="Đóng mở sidebar"
                aria-controls="adminSidebar"
                aria-expanded="true"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18 9 12l6-6"></path></svg>
            </button>
        </div>

        <div class="admin-sidebar-profile">
            <strong><?= e((string) ($adminUser['full_name'] ?? '')); ?></strong>
            <small><?= strtoupper((string) ($adminUser['role'] ?? '')); ?></small>
        </div>

        <nav class="admin-sidebar-nav" aria-label="Menu quản trị">
            <?php if (can_access_page('dashboard-admin')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'dashboard' ? ' is-active' : ''; ?>" href="<?= e(page_url('dashboard-admin')); ?>" title="Tổng quan">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8"></rect><rect x="13" y="3" width="8" height="5"></rect><rect x="13" y="10" width="8" height="11"></rect><rect x="3" y="13" width="8" height="8"></rect></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Tổng quan</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('tuition-finance')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'tuition' ? ' is-active' : ''; ?>" href="<?= e(page_url('tuition-finance')); ?>" title="Học phí">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="12" rx="2"></rect><path d="M3 11h18"></path><path d="M16 15h2"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Học phí</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('registration-finance')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'registration' ? ' is-active' : ''; ?>" href="<?= e(page_url('registration-finance')); ?>" title="Đăng ký khóa học">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 5h16"></path><path d="M4 10h10"></path><path d="M4 15h8"></path><path d="m14 14 2 2 4-4"></path><circle cx="18" cy="16" r="5"></circle></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Đăng ký khóa học</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('promotions-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'promotions' ? ' is-active' : ''; ?>" href="<?= e(page_url('promotions-manage')); ?>" title="Ưu đãi giảm giá">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M20.59 13.41 12 22l-8.59-8.59a2 2 0 0 1 0-2.82L12 2l8.59 8.59a2 2 0 0 1 0 2.82z"></path><circle cx="9" cy="9" r="1.5"></circle></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Ưu đãi giảm giá</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('payments-finance')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'payments' ? ' is-active' : ''; ?>" href="<?= e(page_url('payments-finance')); ?>" title="Thanh toán">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="2.5" y="5" width="19" height="14" rx="2"></rect><path d="M2.5 10h19"></path><path d="M6 15h4"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Thanh toán</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('users-admin')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'users' ? ' is-active' : ''; ?>" href="<?= e(page_url('users-admin')); ?>" title="Người dùng">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 19a6 6 0 0 1 12 0"></path><circle cx="17" cy="9" r="2.5"></circle><path d="M14.5 19a4.5 4.5 0 0 1 6.5 0"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Người dùng</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('approvals-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'approvals' ? ' is-active' : ''; ?>" href="<?= e(page_url('approvals-manage')); ?>" title="Phê duyệt">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6Z"></path><path d="m9 12 2 2 4-4"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Phê duyệt</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('feedbacks-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'feedbacks' ? ' is-active' : ''; ?>" href="<?= e(page_url('feedbacks-manage')); ?>" title="Đánh giá">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 6h16v10H8l-4 4z"></path><path d="M8 10h8"></path><path d="M8 13h5"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Đánh giá</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('student-leads-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'student-leads' ? ' is-active' : ''; ?>" href="<?= e(page_url('student-leads-manage')); ?>" title="Lead học viên">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="10" cy="8" r="3"></circle><path d="M4 19a6 6 0 0 1 12 0"></path><path d="M16 8h5"></path><path d="M18.5 5.5v5"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Lead học viên</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('job-applications-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'job-applications' ? ' is-active' : ''; ?>" href="<?= e(page_url('job-applications-manage')); ?>" title="Ứng tuyển giáo viên">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 6h18v12H3z"></path><path d="m3 7 9 6 9-6"></path><path d="M7 11h2"></path><path d="M7 14h4"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Ứng tuyển GV</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('activities-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'activities' ? ' is-active' : ''; ?>" href="<?= e(page_url('activities-manage')); ?>" title="Hoạt động">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M8 2v4"></path><path d="M16 2v4"></path><path d="M3 10h18"></path><path d="m9 15 2 2 4-4"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Hoạt động</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('bank-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'bank' ? ' is-active' : ''; ?>" href="<?= e(page_url('bank-manage')); ?>" title="Ngân hàng">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 3 3 7h18z"></path><path d="M5 10v9"></path><path d="M9 10v9"></path><path d="M15 10v9"></path><path d="M19 10v9"></path><path d="M2 19h20"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Ngân hàng</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('courses-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'courses' ? ' is-active' : ''; ?>" href="<?= e(page_url('courses-academic')); ?>" title="Khóa học">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 4h16v14H4z"></path><path d="M8 8h8"></path><path d="M8 12h5"></path><path d="M4 20h16"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Danh mục khóa học</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('roadmaps-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'roadmaps' ? ' is-active' : ''; ?>" href="<?= e(page_url('roadmaps-academic')); ?>" title="Roadmap khóa học">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 7h4v4H3z"></path><path d="M10 7h11"></path><path d="M3 13h4v4H3z"></path><path d="M10 15h11"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Roadmap khóa học</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('classes-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'classes' ? ' is-active' : ''; ?>" href="<?= e(page_url('classes-academic')); ?>" title="Lớp học">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 5a3 3 0 0 1 3-3h6v18H6a3 3 0 0 0-3 3z"></path><path d="M21 5a3 3 0 0 0-3-3h-6v18h6a3 3 0 0 1 3 3z"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Danh mục lớp</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('schedules-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'schedules' ? ' is-active' : ''; ?>" href="<?= e(page_url('schedules-academic')); ?>" title="Lịch dạy">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Lịch dạy</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('assignments-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'assignments' ? ' is-active' : ''; ?>" href="<?= e(page_url('assignments-academic')); ?>" title="Bài tập">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="6" y="4" width="12" height="17" rx="2"></rect><path d="M9 4.5h6"></path><path d="M9 10h6"></path><path d="M9 14h6"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Bài tập</span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('materials-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'materials' ? ' is-active' : ''; ?>" href="<?= e(page_url('materials-academic')); ?>" title="Tài liệu">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 7h6l2 2h10v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><path d="M3 7V5a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label">Tài liệu</span>
                </a>
            <?php endif; ?>

        </nav>

        <div class="admin-sidebar-actions">
            <a class="<?= ui_btn_secondary_classes('sm'); ?>" href="<?= e(page_url('home')); ?>">Về trang chủ</a>
            <a class="<?= ui_btn_primary_classes('sm'); ?>" href="<?= e(page_url('logout')); ?>">Đăng xuất</a>
        </div>
    </aside>

    <script>
        (function () {
            const sidebarCollapsedStorageKey = 'admin-ui:sidebar-collapsed';
            const sidebarScrollStorageKey = 'admin-ui:sidebar-scroll';

            function getCurrentRouteKey() {
                return window.location.pathname + window.location.search;
            }

            function saveSidebarScroll(routeKey, scrollTop) {
                try {
                    window.sessionStorage.setItem(sidebarScrollStorageKey, JSON.stringify({
                        routeKey: String(routeKey || ''),
                        scrollTop: Math.max(0, Number(scrollTop || 0)),
                        timestamp: Date.now(),
                    }));
                } catch (error) {
                    // Ignore storage failures in restricted browsing contexts.
                }
            }

            function readSidebarScroll() {
                try {
                    const raw = window.sessionStorage.getItem(sidebarScrollStorageKey);
                    if (!raw) {
                        return null;
                    }

                    const payload = JSON.parse(raw);
                    if (!payload || typeof payload !== 'object') {
                        return null;
                    }

                    return payload;
                } catch (error) {
                    return null;
                }
            }

            function isLinkVisibleInNav(link, nav) {
                const linkTop = link.offsetTop;
                const linkBottom = linkTop + link.offsetHeight;
                const viewTop = nav.scrollTop;
                const viewBottom = viewTop + nav.clientHeight;

                return linkTop >= viewTop && linkBottom <= viewBottom;
            }

            function restoreSidebarScroll(nav) {
                const currentRouteKey = getCurrentRouteKey();
                const payload = readSidebarScroll();
                let restored = false;

                if (
                    payload
                    && String(payload.routeKey || '') === currentRouteKey
                    && Number.isFinite(Number(payload.scrollTop))
                ) {
                    nav.scrollTop = Math.max(0, Number(payload.scrollTop));
                    restored = true;
                }

                const activeLink = nav.querySelector('.admin-sidebar-link.is-active');
                if (!(activeLink instanceof HTMLElement)) {
                    return;
                }

                // If no saved scroll (or saved value doesn't show active link), snap to active item.
                const navCanScroll = nav.scrollHeight > (nav.clientHeight + 4);
                if (!navCanScroll) {
                    return;
                }

                if (!restored || !isLinkVisibleInNav(activeLink, nav)) {
                    requestAnimationFrame(function () {
                        activeLink.scrollIntoView({
                            block: 'nearest',
                            inline: 'nearest',
                        });
                    });
                }
            }

            function bindSidebarScrollPersistence(nav) {
                restoreSidebarScroll(nav);

                nav.addEventListener('click', function (event) {
                    const link = event.target instanceof Element
                        ? event.target.closest('a.admin-sidebar-link[href]')
                        : null;
                    if (!(link instanceof HTMLAnchorElement)) {
                        return;
                    }

                    const destination = new URL(link.href, window.location.href);
                    const destinationRouteKey = destination.pathname + destination.search;

                    let targetScrollTop = nav.scrollTop;
                    if (nav.scrollHeight > (nav.clientHeight + 4)) {
                        const centeredTop = link.offsetTop - Math.max(0, Math.round((nav.clientHeight - link.offsetHeight) / 2));
                        targetScrollTop = Math.max(0, centeredTop);
                    }

                    saveSidebarScroll(destinationRouteKey, targetScrollTop);
                });

                window.addEventListener('beforeunload', function () {
                    saveSidebarScroll(getCurrentRouteKey(), nav.scrollTop);
                });
            }

            function setCollapsedState(collapsed) {
                const shell = document.querySelector('.admin-shell');
                if (!(shell instanceof HTMLElement)) {
                    return;
                }

                shell.classList.toggle('is-sidebar-collapsed', collapsed);

                const expandedText = collapsed ? 'false' : 'true';
                const labelText = collapsed ? 'Mở sidebar' : 'Thu gọn sidebar';
                ['adminSidebarToggle', 'adminMainSidebarToggle'].forEach(function (id) {
                    const button = document.getElementById(id);
                    if (!(button instanceof HTMLButtonElement)) {
                        return;
                    }

                    button.setAttribute('aria-expanded', expandedText);
                    button.setAttribute('aria-label', labelText);
                });

                try {
                    window.localStorage.setItem(sidebarCollapsedStorageKey, collapsed ? '1' : '0');
                } catch (error) {
                    // Ignore persistence failures in restricted browsing contexts.
                }
            }

            function initSidebarToggle() {
                const shell = document.querySelector('.admin-shell');
                if (!(shell instanceof HTMLElement)) {
                    return;
                }

                let collapsed = false;
                try {
                    collapsed = window.localStorage.getItem(sidebarCollapsedStorageKey) === '1';
                } catch (error) {
                    collapsed = false;
                }

                setCollapsedState(collapsed);

                const sidebarNav = document.querySelector('.admin-sidebar-nav');
                if (sidebarNav instanceof HTMLElement) {
                    bindSidebarScrollPersistence(sidebarNav);
                }

                ['adminSidebarToggle', 'adminMainSidebarToggle'].forEach(function (id) {
                    const button = document.getElementById(id);
                    if (!(button instanceof HTMLButtonElement)) {
                        return;
                    }

                    button.addEventListener('click', function () {
                        const nextCollapsed = !shell.classList.contains('is-sidebar-collapsed');
                        setCollapsedState(nextCollapsed);
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSidebarToggle);
                return;
            }

            initSidebarToggle();
        })();
    </script>

    <main class="min-w-0 p-4 md:p-6">
        <header class="admin-page-hero">
            <div class="admin-page-hero-content">
                <div class="admin-page-hero-title-row">
                    <button
                        type="button"
                        class="admin-main-sidebar-toggle"
                        id="adminMainSidebarToggle"
                        aria-label="Đóng mở sidebar"
                        aria-controls="adminSidebar"
                        aria-expanded="true"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h16"></path></svg>
                    </button>
                    <h1 class="admin-page-title"><?= e($displayAdminTitle); ?></h1>
                </div>
                <p class="admin-page-description"><?= e($displayAdminDescription); ?></p>
            </div>

            <div class="admin-page-hero-actions">
                <a class="admin-page-profile-link" href="<?= e(page_url('profile')); ?>">Hồ sơ</a>
            </div>
        </header>

        <div class="admin-ui min-w-0 grid gap-4">