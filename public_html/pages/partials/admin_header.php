<!doctype html>
<html lang="<?= e(current_locale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e((string) ($adminTitle ?? t('app.admin_default_title'))); ?> | <?= e(t('app.admin_suffix')); ?></title>
    <meta name="description" content="<?= e(t('app.admin_description')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <?php require_once __DIR__ . '/tailwind_cdn.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sceditor@3/minified/themes/default.min.css" rel="stylesheet">
    <style>
        .ts-wrapper {
            margin-top: 0.45rem !important;
            width: 100% !important;
        }
        .ts-wrapper .ts-control {
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            padding: 0.62rem 0.78rem !important;
            font-size: 0.92rem !important;
            line-height: 1.35 !important;
            color: #0f172a !important;
            box-shadow: none !important;
            min-height: auto !important;
        }
        .ts-wrapper.focus .ts-control {
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
            background: #f8fbff !important;
        }
        .ts-wrapper .ts-control > input {
            font-size: 0.92rem !important;
            line-height: 1.35 !important;
            color: #0f172a !important;
        }
        .ts-dropdown {
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07) !important;
            font-size: 0.92rem !important;
            color: #0f172a !important;
            margin-top: 0.25rem !important;
            overflow: hidden !important;
        }
        .ts-dropdown .ts-dropdown-content {
            max-height: 250px !important;
        }
        .ts-dropdown .option {
            padding: 0.6rem 0.8rem !important;
            cursor: pointer !important;
            transition: background-color 120ms ease, color 120ms ease !important;
        }
        .ts-dropdown .option.active,
        .ts-dropdown .option:hover {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .admin-ui article > h3 {
            margin-bottom: 0.9rem;
            font-family: "Sora", ui-sans-serif, system-ui, sans-serif;
            font-size: 1.02rem;
            font-weight: 700;
            color: #0f172a;
        }

        .admin-font {
            font-family: "Manrope", ui-sans-serif, system-ui, sans-serif;
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

        .admin-ui form > div > label,
        .admin-ui form .grid > div > label,
        .admin-ui form .bbcode-editor + label {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #334155;
        }

        .admin-ui form label > input:not([type='checkbox']):not([type='hidden']),
        .admin-ui form label > select,
        .admin-ui form label > textarea,
        .admin-ui form .bbcode-editor textarea[data-bbcode-input='1'] {
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

        .admin-ui form label > textarea,
        .admin-ui form .bbcode-editor textarea[data-bbcode-input='1'] {
            min-height: 6.5rem;
            resize: vertical;
        }

        .admin-ui form label > .bbcode-editor {
            margin-top: 0.45rem;
        }

        .bbcode-rendered-box {
            border-radius: 0.85rem;
            border: 1px solid #dbe4f0;
            background: #f8fafc;
            padding: 0.8rem 0.9rem;
            color: #0f172a;
        }

        .bbcode-editor {
            margin-top: 0.45rem;
        }

        .bbcode-editor textarea[data-bbcode-input='1'] {
            margin-top: 0;
            min-height: 8rem;
        }

        .bbcode-editor {
            --bbcode-editor-bg: #ffffff;
            --bbcode-editor-panel: #f8fafc;
            --bbcode-editor-panel-strong: #f1f5f9;
            --bbcode-editor-border: #e2e8f0;
            --bbcode-editor-border-strong: #cbd5e1;
            --bbcode-editor-text: #0f172a;
            --bbcode-editor-muted: #64748b;
            --bbcode-editor-accent: #2563eb;
            --bbcode-editor-accent-soft: rgba(37, 99, 235, 0.08);
            --bbcode-editor-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 10px 24px rgba(15, 23, 42, 0.04);
        }

        .bbcode-editor .sceditor-container {
            width: 100% !important;
            border-radius: 0.9rem;
            border: 1px solid var(--bbcode-editor-border) !important;
            background: var(--bbcode-editor-bg) !important;
            box-shadow: var(--bbcode-editor-shadow) !important;
            overflow: hidden;
            transition: border-color 140ms ease, box-shadow 140ms ease, background-color 140ms ease;
        }

        .bbcode-editor .sceditor-container.focus,
        .bbcode-editor .sceditor-container:focus-within {
            border-color: #93c5fd !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10) !important;
            background: #ffffff !important;
        }

        .bbcode-editor .sceditor-toolbar {
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            align-items: center;
            border-bottom: 1px solid var(--bbcode-editor-border);
            background: #fbfcfe;
            padding: 0.55rem 0.6rem;
        }

        .bbcode-editor .sceditor-group {
            display: inline-flex;
            align-items: center;
            gap: 0.15rem;
            margin: 0;
            padding: 0;
            border-radius: 0.7rem;
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        .bbcode-editor .sceditor-button {
            position: relative;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            min-width: 2rem;
            border-radius: 0.6rem;
            border: 1px solid transparent;
            background: transparent;
            color: var(--bbcode-editor-muted);
            transition:
                background-color 140ms ease,
                color 140ms ease,
                border-color 140ms ease,
                box-shadow 140ms ease;
        }

        .bbcode-editor .sceditor-button:hover,
        .bbcode-editor .sceditor-button.active,
        .bbcode-editor .sceditor-button:focus {
            background: #f3f6fb;
            color: var(--bbcode-editor-text);
            border-color: transparent;
        }

        .bbcode-editor .sceditor-button.active {
            background: var(--bbcode-editor-accent-soft);
            color: var(--bbcode-editor-accent);
            border-color: transparent;
            box-shadow: none;
        }

        .bbcode-editor .sceditor-button:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .bbcode-editor .sceditor-button.disabled {
            opacity: 0.42;
            pointer-events: none;
        }

        .bbcode-editor .sceditor-button div,
        .bbcode-editor .sceditor-button span {
            display: none !important;
        }

        .bbcode-editor .sceditor-button svg {
            width: 1rem;
            height: 1rem;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.85;
            stroke-linecap: round;
            stroke-linejoin: round;
            pointer-events: none;
        }

        .bbcode-editor .sceditor-button.sceditor-button-source svg {
            width: 1.05rem;
            height: 1.05rem;
        }

        .bbcode-editor .sceditor-container iframe,
        .bbcode-editor .sceditor-container textarea,
        .bbcode-editor .sceditor-container .sceditor-wysiwyg {
            min-height: 15rem !important;
            padding: 1rem 1.1rem !important;
            font-family: "Manrope", ui-sans-serif, system-ui, sans-serif !important;
            font-size: 0.94rem !important;
            line-height: 1.7 !important;
            letter-spacing: 0.01em;
            color: var(--bbcode-editor-text) !important;
            background: #ffffff !important;
            caret-color: var(--bbcode-editor-accent) !important;
        }

        .bbcode-editor .sceditor-container textarea {
            border-top: 1px solid var(--bbcode-editor-border) !important;
            background: #ffffff !important;
        }

        .bbcode-editor .sceditor-container iframe {
            background: #ffffff !important;
        }

        .bbcode-editor .sceditor-container .sceditor-wysiwyg body,
        .bbcode-editor .sceditor-container .sceditor-wysiwyg {
            color: var(--bbcode-editor-text) !important;
            font-family: "Manrope", ui-sans-serif, system-ui, sans-serif !important;
            line-height: 1.72 !important;
        }

        .bbcode-editor .sceditor-container .sceditor-wysiwyg p,
        .bbcode-editor .sceditor-container .sceditor-wysiwyg blockquote,
        .bbcode-editor .sceditor-container .sceditor-wysiwyg pre {
            margin-bottom: 0.85rem;
        }

        .bbcode-editor .sceditor-container .sceditor-wysiwyg blockquote {
            margin: 1rem 0;
            padding: 0.9rem 1rem;
            border-left: 3px solid #cbd5e1;
            border-radius: 0.75rem;
            background: #f8fafc;
            color: #1e3a8a;
        }

        .bbcode-editor .sceditor-container .sceditor-wysiwyg pre {
            overflow-x: auto;
            padding: 0.95rem 1rem;
            border-radius: 0.8rem;
            background: #0f172a;
            color: #e2e8f0;
        }

        .bbcode-editor .sceditor-container .sceditor-wysiwyg a {
            color: var(--bbcode-editor-accent);
            text-decoration: underline;
            text-underline-offset: 0.16em;
        }

        .bbcode-editor .sceditor-statusbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.42rem 0.8rem;
            border-top: 1px solid var(--bbcode-editor-border);
            background: #fafbfc;
            color: var(--bbcode-editor-muted);
            font-size: 0.73rem;
        }

        .bbcode-editor .sceditor-grip {
            opacity: 0.58;
            filter: saturate(0);
        }

        .bbcode-hint {
            margin-top: 0.55rem;
            padding: 0 0.2rem;
            font-size: 0.76rem;
            line-height: 1.55;
            color: #64748b;
        }

        #classroom-lesson-modal .bbcode-editor .sceditor-container iframe,
        #classroom-lesson-modal .bbcode-editor .sceditor-container textarea,
        #classroom-lesson-modal .bbcode-editor .sceditor-container .sceditor-wysiwyg,
        #classroom-assignment-modal .bbcode-editor .sceditor-container iframe,
        #classroom-assignment-modal .bbcode-editor .sceditor-container textarea,
        #classroom-assignment-modal .bbcode-editor .sceditor-container .sceditor-wysiwyg {
            min-height: 10.5rem !important;
        }

        @media (max-width: 767px) {
            .bbcode-editor .sceditor-toolbar {
                gap: 0.3rem;
                padding: 0.5rem;
            }

            .bbcode-editor .sceditor-group {
                gap: 0.1rem;
                border-radius: 0.7rem;
            }

            .bbcode-editor .sceditor-button {
                width: 1.9rem;
                height: 1.9rem;
                min-width: 1.9rem;
                border-radius: 0.55rem;
            }

            .bbcode-editor .sceditor-container iframe,
            .bbcode-editor .sceditor-container textarea,
            .bbcode-editor .sceditor-container .sceditor-wysiwyg {
                min-height: 12.5rem !important;
                padding: 0.9rem 1rem !important;
                font-size: 0.92rem !important;
            }

            #classroom-lesson-modal .bbcode-editor .sceditor-container iframe,
            #classroom-lesson-modal .bbcode-editor .sceditor-container textarea,
            #classroom-lesson-modal .bbcode-editor .sceditor-container .sceditor-wysiwyg,
            #classroom-assignment-modal .bbcode-editor .sceditor-container iframe,
            #classroom-assignment-modal .bbcode-editor .sceditor-container textarea,
            #classroom-assignment-modal .bbcode-editor .sceditor-container .sceditor-wysiwyg {
                min-height: 9rem !important;
            }
        }


        .bbcode-content {
            font-size: 0.9rem;
            line-height: 1.6;
            word-break: break-word;
        }

        .bbcode-content:empty::before {
            content: "Chưa có nội dung để xem trước.";
            color: #94a3b8;
        }

        .bbcode-content .bbcode-link {
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: underline;
        }

        .bbcode-content .bbcode-quote {
            margin: 0.35rem 0;
            border-left: 4px solid #93c5fd;
            background: #eff6ff;
            padding: 0.7rem 0.85rem;
            border-radius: 0 0.8rem 0.8rem 0;
            color: #1e3a8a;
        }

        .bbcode-content .bbcode-code {
            margin: 0.35rem 0;
            overflow-x: auto;
            border-radius: 0.8rem;
            background: #0f172a;
            padding: 0.8rem 0.95rem;
            color: #e2e8f0;
            font-size: 0.82rem;
            line-height: 1.55;
        }

        .bbcode-content .bbcode-image {
            display: block;
            max-width: 100%;
            height: auto;
            margin: 0.5rem 0;
            border-radius: 0.8rem;
        }

        .admin-ui form label > input:not([type='checkbox']):not([type='hidden']):focus,
        .admin-ui form label > select:focus,
        .admin-ui form label > textarea:focus,
        .admin-ui form .bbcode-editor textarea[data-bbcode-input='1']:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
            background: #f8fbff;
        }

        .admin-ui form label > input:not([type='checkbox']):not([type='hidden']):disabled,
        .admin-ui form label > select:disabled,
        .admin-ui form label > textarea:disabled,
        .admin-ui form .bbcode-editor textarea[data-bbcode-input='1']:disabled,
        .admin-ui form fieldset:disabled,
        .admin-ui form fieldset:disabled *,
        .admin-edit-modal-body button:disabled,
        .admin-edit-modal-body input[type='submit']:disabled {
            cursor: not-allowed;
        }

        .admin-ui form label > input:not([type='checkbox']):not([type='hidden']):disabled,
        .admin-ui form label > select:disabled,
        .admin-ui form label > textarea:disabled,
        .admin-ui form .bbcode-editor textarea[data-bbcode-input='1']:disabled {
            color: #475569;
            background: #f8fafc;
            border-color: #dbe4f0;
            -webkit-text-fill-color: #475569;
            opacity: 1;
        }

        .admin-edit-modal-body button:disabled,
        .admin-edit-modal-body input[type='submit']:disabled {
            opacity: 1;
            border-color: #cbd5e1 !important;
            background: #e2e8f0 !important;
            color: #64748b !important;
            box-shadow: none !important;
        }

        .admin-edit-modal-body [data-process-locked-section='1'],
        .admin-edit-modal-body [data-process-locked-section='1'] * {
            cursor: not-allowed !important;
        }

        .admin-ui form input[type='checkbox'] {
            margin-right: 0.45rem;
            height: 1rem;
            width: 1rem;
            accent-color: #2563eb;
            vertical-align: middle;
        }

        .admin-ui form input::placeholder,
        .admin-ui form textarea::placeholder,
        .admin-ui form .bbcode-editor textarea[data-bbcode-input='1']::placeholder {
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

        .admin-edit-modal-dialog.is-process-modal {
            width: min(1280px, calc(100vw - 1rem));
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

        .admin-edit-modal-dialog.is-process-modal .admin-edit-modal-body {
            padding: 0.85rem;
        }

        .admin-edit-modal-body .admin-modal-helper {
            margin-bottom: 0.8rem;
            font-size: 0.74rem;
            font-weight: 700;
            color: #64748b;
        }

        .admin-edit-modal-body .admin-edit-modal-success {
            border: 1px solid #a7f3d0;
            border-radius: 0.8rem;
            background: #ecfdf5;
            color: #047857;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 0.9rem;
            margin-bottom: 0.85rem;
        }

        .admin-edit-modal-body .admin-ui.is-process-modal > .admin-modal-helper {
            display: none;
        }

        .admin-edit-modal-body [data-edit-modal-mode='process'] {
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        .admin-edit-modal-body [data-edit-modal-mode='process'] > .mb-4 {
            margin-bottom: 0.75rem;
        }

        .admin-edit-modal-body [data-edit-modal-mode='process'] > .grid.gap-3.xl\:grid-cols-3 {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(360px, 0.95fr);
            gap: 0.75rem;
        }

        .admin-edit-modal-body [data-edit-modal-mode='process'] > .mt-3.grid.gap-3.md\:grid-cols-2 {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .admin-edit-modal-body [data-edit-modal-mode='process'] .rounded-xl {
            padding: 0.85rem;
        }

        .admin-edit-modal-body [data-edit-modal-mode='process'] form.grid.gap-2 {
            gap: 0.6rem;
        }

        .admin-edit-modal-body [data-edit-modal-mode='process'] textarea {
            min-height: 112px;
        }

        .admin-edit-modal-body [data-edit-modal-mode='process'] .text-sm.leading-relaxed {
            line-height: 1.6;
        }

        @media (max-width: 1100px) {
            .admin-edit-modal-dialog.is-process-modal {
                width: min(1080px, calc(100vw - 1rem));
            }

            .admin-edit-modal-body [data-edit-modal-mode='process'] > .grid.gap-3.xl\:grid-cols-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 860px) {
            .admin-edit-modal-body [data-edit-modal-mode='process'] > .grid.gap-3.xl\:grid-cols-3,
            .admin-edit-modal-body [data-edit-modal-mode='process'] > .mt-3.grid.gap-3.md\:grid-cols-2 {
                grid-template-columns: minmax(0, 1fr);
            }
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

        .admin-notification-shell {
            position: relative;
        }

        .admin-notification-toggle {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.85rem;
            height: 2.85rem;
            border-radius: 999px;
            border: 1px solid rgba(203, 213, 225, 0.9);
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.09);
            transition: transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease, background-color 150ms ease;
        }

        .admin-notification-toggle:hover,
        .admin-notification-toggle:focus-visible {
            transform: translateY(-1px);
            border-color: rgba(96, 165, 250, 0.9);
            background: #ffffff;
            box-shadow: 0 22px 48px rgba(37, 99, 235, 0.16);
            outline: none;
        }

        .admin-notification-toggle svg {
            width: 1.18rem;
            height: 1.18rem;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .admin-notification-badge {
            position: absolute;
            top: -0.1rem;
            right: -0.1rem;
            min-width: 1.28rem;
            height: 1.28rem;
            padding: 0 0.32rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #f43f5e, #ef4444);
            color: #ffffff;
            font-size: 0.66rem;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(244, 63, 94, 0.35);
        }

        .admin-notification-dropdown {
            position: absolute;
            top: calc(100% + 0.7rem);
            right: 0;
            width: min(26rem, calc(100vw - 2rem));
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(18px);
            box-shadow: 0 30px 65px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            pointer-events: none;
            transition: opacity 160ms ease, transform 160ms ease, visibility 160ms ease;
            z-index: 70;
        }

        .admin-notification-dropdown.is-open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }

        .admin-notification-dropdown-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1rem 0.85rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        }

        .admin-notification-dropdown-title {
            font-family: "Sora", ui-sans-serif, system-ui, sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }

        .admin-notification-dropdown-subtitle {
            margin-top: 0.18rem;
            font-size: 0.76rem;
            font-weight: 700;
            color: #64748b;
        }

        .admin-notification-dropdown-list {
            max-height: 24rem;
            overflow: auto;
            padding: 0.45rem;
        }

        .admin-notification-dropdown-item {
            display: block;
            padding: 0.9rem 0.95rem;
            border-radius: 1rem;
            color: inherit;
            text-decoration: none;
            transition: background-color 140ms ease, transform 140ms ease;
        }

        .admin-notification-dropdown-item:hover,
        .admin-notification-dropdown-item:focus-visible {
            background: #f8fafc;
            transform: translateY(-1px);
            outline: none;
        }

        .admin-notification-dropdown-item.is-unread {
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.96), rgba(248, 250, 252, 0.96));
        }

        .admin-notification-item-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .admin-notification-item-title {
            font-size: 0.84rem;
            font-weight: 800;
            line-height: 1.35;
            color: #0f172a;
        }

        .admin-notification-item-meta {
            margin-top: 0.42rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748b;
        }

        .admin-notification-item-message {
            margin-top: 0.48rem;
            font-size: 0.77rem;
            line-height: 1.45;
            color: #475569;
        }

        .admin-notification-item-dot {
            flex: 0 0 auto;
            width: 0.58rem;
            height: 0.58rem;
            border-radius: 999px;
            background: #f43f5e;
            box-shadow: 0 0 0 5px rgba(244, 63, 94, 0.12);
            margin-top: 0.24rem;
        }

        .admin-notification-empty {
            padding: 1.2rem 1rem 1.35rem;
            text-align: center;
            font-size: 0.82rem;
            font-weight: 700;
            color: #64748b;
        }

        .admin-notification-dropdown-footer {
            padding: 0.85rem 1rem 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(248, 250, 252, 0.72);
        }

        .admin-notification-dropdown-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 2.65rem;
            border-radius: 0.95rem;
            background: #0f172a;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 800;
            transition: background-color 140ms ease, transform 140ms ease;
        }

        .admin-notification-dropdown-link:hover,
        .admin-notification-dropdown-link:focus-visible {
            background: #1d4ed8;
            transform: translateY(-1px);
            outline: none;
        }

        .admin-notification-toast-stack {
            position: fixed;
            right: 1.1rem;
            bottom: 1.1rem;
            z-index: 120;
            display: grid;
            gap: 0.7rem;
            width: min(24rem, calc(100vw - 1.5rem));
            pointer-events: none;
        }

        .admin-notification-toast {
            pointer-events: auto;
            display: grid;
            gap: 0.45rem;
            border: 1px solid rgba(191, 219, 254, 0.95);
            border-radius: 1.15rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.18);
            padding: 0.95rem 1rem 1rem;
            transform: translateY(14px);
            opacity: 0;
            transition: opacity 180ms ease, transform 180ms ease;
        }

        .admin-notification-toast.is-visible {
            transform: translateY(0);
            opacity: 1;
        }

        .admin-notification-toast-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .admin-notification-toast-kicker {
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #2563eb;
        }

        .admin-notification-toast-title {
            margin-top: 0.18rem;
            font-size: 0.87rem;
            font-weight: 800;
            line-height: 1.35;
            color: #0f172a;
        }

        .admin-notification-toast-message {
            font-size: 0.78rem;
            line-height: 1.5;
            color: #475569;
        }

        .admin-notification-toast-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .admin-notification-toast-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.2rem;
            padding: 0 0.95rem;
            border-radius: 0.85rem;
            background: #0f172a;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.74rem;
            font-weight: 800;
            transition: background-color 140ms ease, transform 140ms ease;
        }

        .admin-notification-toast-link:hover,
        .admin-notification-toast-link:focus-visible {
            background: #1d4ed8;
            transform: translateY(-1px);
            outline: none;
        }

        .admin-notification-toast-time {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
        }

        .admin-notification-toast-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 999px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: #ffffff;
            color: #64748b;
            transition: background-color 140ms ease, color 140ms ease, border-color 140ms ease;
        }

        .admin-notification-toast-close:hover,
        .admin-notification-toast-close:focus-visible {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
            outline: none;
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

            html.admin-sidebar-collapsed .admin-shell,
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

        .admin-main-content.is-ajax-loading {
            opacity: 0.65;
            pointer-events: none;
            transition: opacity 140ms ease;
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
    <script>
        (function () {
            try {
                if (window.localStorage.getItem('admin-ui:sidebar-collapsed') === '1') {
                    document.documentElement.classList.add('admin-sidebar-collapsed');
                } else {
                    document.documentElement.classList.remove('admin-sidebar-collapsed');
                }
            } catch (error) {
                document.documentElement.classList.remove('admin-sidebar-collapsed');
            }
        })();
    </script>
</head>
<body class="min-h-screen bg-slate-100 admin-font leading-relaxed text-slate-900">
<?php
require_once __DIR__ . '/../../models/AcademicModel.php';
$adminUser = auth_user();
$adminNotificationModel = new AcademicModel();
$adminRecentNotifications = [];
$adminUnreadNotificationCount = 0;
$adminUnreadNotificationModules = [];
$canUseNotificationBell = can_use_notification_bell();
$canManageNotificationCenter = can_manage_notification_center();
$adminNotificationFallbackUrl = $canManageNotificationCenter ? page_url('notifications-manage') : '#';
if (is_array($adminUser) && (int) ($adminUser['id'] ?? 0) > 0) {
    try {
        $adminUnreadNotificationCount = $adminNotificationModel->countUnreadNotifications((int) $adminUser['id']);
        $adminUnreadNotificationModules = $adminNotificationModel->countUnreadNotificationsByModule((int) $adminUser['id']);
        $adminRecentNotifications = $adminNotificationModel->listNotificationDropdownItems((int) $adminUser['id'], 6);
    } catch (Throwable) {
        $adminRecentNotifications = [];
        $adminUnreadNotificationCount = 0;
        $adminUnreadNotificationModules = [];
    }
}
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
        'rooms-manage' => 'rooms',
        'notifications-manage' => 'notifications',
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
    'dashboard' => t('admin.page_title.dashboard'),
    'tuition' => t('admin.page_title.tuition'),
    'registration' => t('admin.page_title.registration'),
    'promotions' => t('admin.page_title.promotions'),
    'payments' => t('admin.page_title.payments'),
    'users' => t('admin.page_title.users'),
    'approvals' => t('admin.page_title.approvals'),
    'feedbacks' => t('admin.page_title.feedbacks'),
    'student-leads' => t('admin.page_title.student_leads'),
    'job-applications' => t('admin.page_title.job_applications'),
    'activities' => t('admin.page_title.activities'),
    'rooms' => t('admin.page_title.rooms'),
    'notifications' => t('admin.page_title.notifications'),
    'courses' => t('admin.page_title.courses'),
    'roadmaps' => t('admin.page_title.roadmaps'),
    'classes' => t('admin.page_title.classes'),
    'classrooms' => t('admin.page_title.classrooms'),
    'schedules' => t('admin.page_title.schedules'),
    'assignments' => t('admin.page_title.assignments'),
    'materials' => t('admin.page_title.materials'),
    'portfolios' => t('admin.page_title.portfolios'),
    'exports' => t('admin.page_title.exports'),
];

$adminPageDescriptionMap = [
    'dashboard' => t('admin.page_desc.dashboard'),
    'tuition' => t('admin.page_desc.tuition'),
    'registration' => t('admin.page_desc.registration'),
    'promotions' => t('admin.page_desc.promotions'),
    'payments' => t('admin.page_desc.payments'),
    'users' => t('admin.page_desc.users'),
    'approvals' => t('admin.page_desc.approvals'),
    'feedbacks' => t('admin.page_desc.feedbacks'),
    'student-leads' => t('admin.page_desc.student_leads'),
    'job-applications' => t('admin.page_desc.job_applications'),
    'activities' => t('admin.page_desc.activities'),
    'rooms' => t('admin.page_desc.rooms'),
    'notifications' => t('admin.page_desc.notifications'),
    'courses' => t('admin.page_desc.courses'),
    'roadmaps' => t('admin.page_desc.roadmaps'),
    'classes' => t('admin.page_desc.classes'),
    'classrooms' => t('admin.page_desc.classrooms'),
    'schedules' => t('admin.page_desc.schedules'),
    'assignments' => t('admin.page_desc.assignments'),
    'materials' => t('admin.page_desc.materials'),
    'portfolios' => t('admin.page_desc.portfolios'),
    'exports' => t('admin.page_desc.exports'),
];

$displayAdminTitle = (string) ($adminPageTitleMap[$activeModule] ?? ($adminTitle ?? t('app.admin_default_title')));
$displayAdminDescription = trim((string) ($adminDescription ?? ''));
if ($displayAdminDescription === '') {
    $displayAdminDescription = (string) ($adminPageDescriptionMap[$activeModule] ?? t('admin.generic_description'));
}
?>
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-top">
            <a class="admin-sidebar-brand" href="<?= e(page_url('admin')); ?>">
                <span class="admin-sidebar-brand-mark">EC</span>
                <span class="admin-sidebar-brand-text"><?= e(t('admin.brand_title')); ?></span>
            </a>

            <button
                type="button"
                class="admin-sidebar-toggle"
                id="adminSidebarToggle"
                aria-label="<?= e(t('admin.sidebar.toggle')); ?>"
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

        <nav class="admin-sidebar-nav" aria-label="<?= e(t('admin.sidebar.menu_label')); ?>">
            <?php if (can_access_page('dashboard-admin')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'dashboard' ? ' is-active' : ''; ?>" href="<?= e(page_url('dashboard-admin')); ?>" title="<?= e(t('admin.nav.dashboard')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8"></rect><rect x="13" y="3" width="8" height="5"></rect><rect x="13" y="10" width="8" height="11"></rect><rect x="3" y="13" width="8" height="8"></rect></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.dashboard')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('tuition-finance')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'tuition' ? ' is-active' : ''; ?>" href="<?= e(page_url('tuition-finance')); ?>" title="<?= e(t('admin.nav.tuition')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="12" rx="2"></rect><path d="M3 11h18"></path><path d="M16 15h2"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.tuition')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('registration-finance')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'registration' ? ' is-active' : ''; ?>" href="<?= e(page_url('registration-finance')); ?>" title="<?= e(t('admin.nav.registration')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 5h16"></path><path d="M4 10h10"></path><path d="M4 15h8"></path><path d="m14 14 2 2 4-4"></path><circle cx="18" cy="16" r="5"></circle></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.registration')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('promotions-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'promotions' ? ' is-active' : ''; ?>" href="<?= e(page_url('promotions-manage')); ?>" title="<?= e(t('admin.nav.promotions')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M20.59 13.41 12 22l-8.59-8.59a2 2 0 0 1 0-2.82L12 2l8.59 8.59a2 2 0 0 1 0 2.82z"></path><circle cx="9" cy="9" r="1.5"></circle></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.promotions')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('payments-finance')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'payments' ? ' is-active' : ''; ?>" href="<?= e(page_url('payments-finance')); ?>" title="<?= e(t('admin.nav.payments')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="2.5" y="5" width="19" height="14" rx="2"></rect><path d="M2.5 10h19"></path><path d="M6 15h4"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.payments')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('users-admin')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'users' ? ' is-active' : ''; ?>" href="<?= e(page_url('users-admin')); ?>" title="<?= e(t('admin.nav.users')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 19a6 6 0 0 1 12 0"></path><circle cx="17" cy="9" r="2.5"></circle><path d="M14.5 19a4.5 4.5 0 0 1 6.5 0"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.users')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('approvals-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'approvals' ? ' is-active' : ''; ?>" href="<?= e(page_url('approvals-manage')); ?>" title="<?= e(t('admin.nav.approvals')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6Z"></path><path d="m9 12 2 2 4-4"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.approvals')); ?></span>
                    <?php if (((int) ($adminUnreadNotificationModules['approvals'] ?? 0)) > 0): ?>
                        <span
                            id="adminSidebarModuleBadge-approvals"
                            class="ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm"
                        >
                            <?= e((string) ((((int) ($adminUnreadNotificationModules['approvals'] ?? 0)) > 99) ? '99+' : ((int) ($adminUnreadNotificationModules['approvals'] ?? 0)))); ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('feedbacks-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'feedbacks' ? ' is-active' : ''; ?>" href="<?= e(page_url('feedbacks-manage')); ?>" title="<?= e(t('admin.nav.feedbacks')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 6h16v10H8l-4 4z"></path><path d="M8 10h8"></path><path d="M8 13h5"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.feedbacks')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('student-leads-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'student-leads' ? ' is-active' : ''; ?>" href="<?= e(page_url('student-leads-manage')); ?>" title="<?= e(t('admin.nav.student_leads')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="10" cy="8" r="3"></circle><path d="M4 19a6 6 0 0 1 12 0"></path><path d="M16 8h5"></path><path d="M18.5 5.5v5"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.student_leads')); ?></span>
                    <?php if (((int) ($adminUnreadNotificationModules['student-leads'] ?? 0)) > 0): ?>
                        <span
                            id="adminSidebarModuleBadge-student-leads"
                            class="ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm"
                        >
                            <?= e((string) ((((int) ($adminUnreadNotificationModules['student-leads'] ?? 0)) > 99) ? '99+' : ((int) ($adminUnreadNotificationModules['student-leads'] ?? 0)))); ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('job-applications-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'job-applications' ? ' is-active' : ''; ?>" href="<?= e(page_url('job-applications-manage')); ?>" title="<?= e(t('admin.nav.job_applications')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 6h18v12H3z"></path><path d="m3 7 9 6 9-6"></path><path d="M7 11h2"></path><path d="M7 14h4"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.job_applications')); ?></span>
                    <?php if (((int) ($adminUnreadNotificationModules['job-applications'] ?? 0)) > 0): ?>
                        <span
                            id="adminSidebarModuleBadge-job-applications"
                            class="ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm"
                        >
                            <?= e((string) ((((int) ($adminUnreadNotificationModules['job-applications'] ?? 0)) > 99) ? '99+' : ((int) ($adminUnreadNotificationModules['job-applications'] ?? 0)))); ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('activities-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'activities' ? ' is-active' : ''; ?>" href="<?= e(page_url('activities-manage')); ?>" title="<?= e(t('admin.nav.activities')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M8 2v4"></path><path d="M16 2v4"></path><path d="M3 10h18"></path><path d="m9 15 2 2 4-4"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.activities')); ?></span>
                    <?php if (((int) ($adminUnreadNotificationModules['activities'] ?? 0)) > 0): ?>
                        <span
                            id="adminSidebarModuleBadge-activities"
                            class="ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm"
                        >
                            <?= e((string) ((((int) ($adminUnreadNotificationModules['activities'] ?? 0)) > 99) ? '99+' : ((int) ($adminUnreadNotificationModules['activities'] ?? 0)))); ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('rooms-manage')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'rooms' ? ' is-active' : ''; ?>" href="<?= e(page_url('rooms-manage')); ?>" title="<?= e(t('admin.nav.rooms')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 6h16v12H4z"></path><path d="M8 10h3"></path><path d="M8 14h3"></path><path d="M16 14h.01"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.rooms')); ?></span>
                </a>
            <?php endif; ?>

            <?php if ($canManageNotificationCenter): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'notifications' ? ' is-active' : ''; ?>" href="<?= e(page_url('notifications-manage')); ?>" title="<?= e(t('admin.nav.notifications')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.notifications')); ?></span>
                    <?php if ($adminUnreadNotificationCount > 0): ?>
                        <span
                            id="adminSidebarNotificationBadge"
                            class="ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm"
                        >
                            <?= e((string) ($adminUnreadNotificationCount > 99 ? '99+' : $adminUnreadNotificationCount)); ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>


            <?php if (can_access_page('courses-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'courses' ? ' is-active' : ''; ?>" href="<?= e(page_url('courses-academic')); ?>" title="<?= e(t('admin.nav.courses')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 4h16v14H4z"></path><path d="M8 8h8"></path><path d="M8 12h5"></path><path d="M4 20h16"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.courses')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('roadmaps-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'roadmaps' ? ' is-active' : ''; ?>" href="<?= e(page_url('roadmaps-academic')); ?>" title="<?= e(t('admin.nav.roadmaps')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 7h4v4H3z"></path><path d="M10 7h11"></path><path d="M3 13h4v4H3z"></path><path d="M10 15h11"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.roadmaps')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('classes-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'classes' ? ' is-active' : ''; ?>" href="<?= e(page_url('classes-academic')); ?>" title="<?= e(t('admin.nav.classes')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 5a3 3 0 0 1 3-3h6v18H6a3 3 0 0 0-3 3z"></path><path d="M21 5a3 3 0 0 0-3-3h-6v18h6a3 3 0 0 1 3 3z"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.classes')); ?></span>
                    <?php if (((int) ($adminUnreadNotificationModules['classes'] ?? 0)) > 0): ?>
                        <span
                            id="adminSidebarModuleBadge-classes"
                            class="ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm"
                        >
                            <?= e((string) ((((int) ($adminUnreadNotificationModules['classes'] ?? 0)) > 99) ? '99+' : ((int) ($adminUnreadNotificationModules['classes'] ?? 0)))); ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('schedules-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'schedules' ? ' is-active' : ''; ?>" href="<?= e(page_url('schedules-academic')); ?>" title="<?= e(t('admin.nav.schedules')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.schedules')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('assignments-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'assignments' ? ' is-active' : ''; ?>" href="<?= e(page_url('assignments-academic')); ?>" title="<?= e(t('admin.nav.assignments')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="6" y="4" width="12" height="17" rx="2"></rect><path d="M9 4.5h6"></path><path d="M9 10h6"></path><path d="M9 14h6"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.assignments')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('materials-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'materials' ? ' is-active' : ''; ?>" href="<?= e(page_url('materials-academic')); ?>" title="<?= e(t('admin.nav.materials')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 7h6l2 2h10v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><path d="M3 7V5a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.materials')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('portfolios-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'portfolios' ? ' is-active' : ''; ?>" href="<?= e(page_url('portfolios-academic')); ?>" title="<?= e(t('admin.nav.portfolios')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2v2h4v12H4V6h4V4a2 2 0 0 1 2-2z"></path><path d="M8 10h8"></path><path d="M8 14h8"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.portfolios')); ?></span>
                </a>
            <?php endif; ?>

            <?php if (can_access_page('exports-academic')): ?>
                <a class="admin-sidebar-link<?= $activeModule === 'exports' ? ' is-active' : ''; ?>" href="<?= e(page_url('exports-academic')); ?>" title="<?= e(t('admin.nav.exports')); ?>">
                    <span class="admin-sidebar-link-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M14 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M8 3h8v4H8z"></path><path d="m9 12 2 2 4-4"></path><path d="M8 17h8"></path></svg>
                    </span>
                    <span class="admin-sidebar-link-label"><?= e(t('admin.nav.exports')); ?></span>
                </a>
            <?php endif; ?>

        </nav>

        <div class="admin-sidebar-actions">
            <a class="<?= ui_btn_secondary_classes('sm'); ?>" href="<?= e(page_url('home')); ?>"><?= e(t('admin.back_home')); ?></a>
            <a class="<?= ui_btn_primary_classes('sm'); ?>" href="<?= e(page_url('logout')); ?>"><?= e(t('nav.logout')); ?></a>
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
                document.documentElement.classList.toggle('admin-sidebar-collapsed', collapsed);

                const expandedText = collapsed ? 'false' : 'true';
                const labelText = collapsed ? <?= json_encode(t('admin.sidebar.open'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?> : <?= json_encode(t('admin.sidebar.collapse'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
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

    <main class="admin-main-content min-w-0 p-4 md:p-6" id="adminMainContent" data-admin-main-content="1">
        <header class="admin-page-hero">
            <div class="admin-page-hero-content">
                <div class="admin-page-hero-title-row">
                    <button
                        type="button"
                        class="admin-main-sidebar-toggle"
                        id="adminMainSidebarToggle"
                        aria-label="<?= e(t('admin.sidebar.toggle')); ?>"
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
                <div class="inline-flex rounded-full border border-slate-200 bg-white p-1 text-xs font-black shadow-sm" aria-label="<?= e(t('locale.language')); ?>">
                    <?php $adminCurrentLocale = current_locale(); ?>
                    <a class="rounded-full px-2.5 py-1 <?= $adminCurrentLocale === 'vi' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:text-blue-700'; ?>" href="<?= e(localized_current_url('vi')); ?>" title="<?= e(t('locale.switch_to', ['language' => 'Tiếng Việt'])); ?>"><?= e(t('locale.vi')); ?></a>
                    <a class="rounded-full px-2.5 py-1 <?= $adminCurrentLocale === 'en' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:text-blue-700'; ?>" href="<?= e(localized_current_url('en')); ?>" title="<?= e(t('locale.switch_to', ['language' => 'English'])); ?>"><?= e(t('locale.en')); ?></a>
                </div>
                <?php if ($canUseNotificationBell): ?>
                    <div class="admin-notification-shell" data-admin-notification-shell="1">
                        <button
                            type="button"
                            class="admin-notification-toggle"
                            id="adminNotificationToggle"
                            aria-label="<?= e(t('admin.notifications.open')); ?>"
                            aria-haspopup="dialog"
                            aria-expanded="false"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span id="adminNotificationBadge" class="admin-notification-badge" <?= $adminUnreadNotificationCount > 0 ? '' : 'hidden'; ?>><?= e((string) ($adminUnreadNotificationCount > 99 ? '99+' : $adminUnreadNotificationCount)); ?></span>
                        </button>

                        <div class="admin-notification-dropdown" id="adminNotificationDropdown" role="dialog" aria-label="<?= e(t('admin.notifications.dialog')); ?>">
                            <div class="admin-notification-dropdown-header">
                                <div>
                                    <div class="admin-notification-dropdown-title"><?= e(t('admin.notifications.new')); ?></div>
                                    <div class="admin-notification-dropdown-subtitle" id="adminNotificationSubtitle">
                                        <?= e($adminUnreadNotificationCount > 0
                                            ? t('admin.notifications.unread', ['count' => ($adminUnreadNotificationCount > 99 ? '99+' : $adminUnreadNotificationCount)])
                                            : t('admin.notifications.all_read')); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-notification-dropdown-list" id="adminNotificationDropdownList">
                                <?php if ($adminRecentNotifications === []): ?>
                                    <div class="admin-notification-empty"><?= e(t('admin.notifications.empty')); ?></div>
                                <?php else: ?>
                                    <?php foreach ($adminRecentNotifications as $notification): ?>
                                        <?php
                                        $notificationId = (int) ($notification['id'] ?? 0);
                                        $notificationTitle = trim((string) ($notification['title'] ?? t('admin.notifications.system_title')));
                                        $notificationMessage = trim((string) ($notification['message'] ?? ''));
                                        if ($notificationMessage !== '') {
                                            if (function_exists('mb_strimwidth')) {
                                                $notificationMessage = mb_strimwidth($notificationMessage, 0, 140, '...');
                                            } elseif (strlen($notificationMessage) > 140) {
                                                $notificationMessage = substr($notificationMessage, 0, 137) . '...';
                                            }
                                        }
                                        $notificationIsRead = (int) ($notification['is_read'] ?? 0) === 1;
                                        $notificationActionUrl = trim((string) ($notification['action_url'] ?? ''));
                                        if ($notificationActionUrl === '') {
                                            $notificationActionUrl = $adminNotificationFallbackUrl;
                                        }
                                        ?>
                                        <a
                                            class="admin-notification-dropdown-item<?= $notificationIsRead ? '' : ' is-unread'; ?>"
                                            href="<?= e($notificationActionUrl); ?>"
                                            data-notification-id="<?= $notificationId; ?>"
                                        >
                                            <div class="admin-notification-item-head">
                                                <div class="min-w-0">
                                                    <div class="admin-notification-item-title"><?= e($notificationTitle); ?></div>
                                                    <div class="admin-notification-item-meta"><?= e(ui_format_datetime((string) ($notification['created_at'] ?? ''))); ?></div>
                                                </div>
                                                <?php if (!$notificationIsRead): ?>
                                                    <span class="admin-notification-item-dot" aria-hidden="true"></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($notificationMessage !== ''): ?>
                                                <div class="admin-notification-item-message"><?= e($notificationMessage); ?></div>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($canManageNotificationCenter): ?>
                                <div class="admin-notification-dropdown-footer">
                                    <a class="admin-notification-dropdown-link" href="<?= e(page_url('notifications-manage')); ?>"><?= e(t('admin.notifications.view_all')); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <a class="admin-page-profile-link" href="<?= e(page_url('profile')); ?>"><?= e(t('nav.profile')); ?></a>
            </div>
        </header>

        <div id="adminNotificationToastStack" class="admin-notification-toast-stack" aria-live="polite" aria-atomic="true"></div>

        <script>
            (function () {
                const toggle = document.getElementById('adminNotificationToggle');
                const dropdown = document.getElementById('adminNotificationDropdown');
                const shell = document.querySelector('[data-admin-notification-shell="1"]');
                const list = document.getElementById('adminNotificationDropdownList');
                const subtitle = document.getElementById('adminNotificationSubtitle');
                const bellBadge = document.getElementById('adminNotificationBadge');
                const sidebarBadge = document.getElementById('adminSidebarNotificationBadge');
                const moduleBadgeElements = {
                    approvals: document.getElementById('adminSidebarModuleBadge-approvals'),
                    'student-leads': document.getElementById('adminSidebarModuleBadge-student-leads'),
                    'job-applications': document.getElementById('adminSidebarModuleBadge-job-applications'),
                    activities: document.getElementById('adminSidebarModuleBadge-activities'),
                    classes: document.getElementById('adminSidebarModuleBadge-classes'),
                };
                const csrfToken = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
                const feedUrl = <?= json_encode('/api/index.php?resource=notifications&method=admin-feed&format=json&limit=6', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
                const markReadUrl = <?= json_encode('/api/index.php?resource=notifications&method=mark-read&format=json', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
                const allNotificationsUrl = <?= json_encode($adminNotificationFallbackUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
                const notificationText = {
                    unread: <?= json_encode(t('admin.notifications.unread', ['count' => '__COUNT__']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
                    allRead: <?= json_encode(t('admin.notifications.all_read'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
                    empty: <?= json_encode(t('admin.notifications.empty'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
                    systemTitle: <?= json_encode(t('admin.notifications.system_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
                    newTitle: <?= json_encode(t('admin.notifications.new'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
                    close: <?= json_encode(t('admin.notifications.close'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
                    openDetail: <?= json_encode(t('admin.notifications.open_detail'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
                };
                const pollIntervalMs = 45000;
                const toastStack = document.getElementById('adminNotificationToastStack');
                const knownNotificationIds = new Set();
                let didHydrateNotifications = false;

                if (!(toggle instanceof HTMLButtonElement) || !(dropdown instanceof HTMLElement) || !(shell instanceof HTMLElement) || !(list instanceof HTMLElement) || !(subtitle instanceof HTMLElement)) {
                    return;
                }

                function setDropdownOpen(isOpen) {
                    dropdown.classList.toggle('is-open', isOpen);
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                }

                function escapeHtml(value) {
                    return String(value || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function unreadBadgeText(count) {
                    return count > 99 ? '99+' : String(Math.max(0, count));
                }

                function updateBadgeElement(badgeElement, count, className) {
                    if (!(badgeElement instanceof HTMLElement)) {
                        return;
                    }

                    if (count > 0) {
                        badgeElement.textContent = unreadBadgeText(count);
                        badgeElement.className = className;
                        badgeElement.hidden = false;
                        return;
                    }

                    badgeElement.hidden = true;
                }

                function updateUnreadDisplay(unreadCount) {
                    const normalizedCount = Math.max(0, Number(unreadCount || 0));
                    subtitle.textContent = normalizedCount > 0
                        ? notificationText.unread.replace('__COUNT__', unreadBadgeText(normalizedCount))
                        : notificationText.allRead;

                    updateBadgeElement(
                        bellBadge,
                        normalizedCount,
                        'admin-notification-badge'
                    );

                    updateBadgeElement(
                        sidebarBadge,
                        normalizedCount,
                        'ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm'
                    );
                }

                function updateModuleBadges(moduleCounts) {
                    const counts = moduleCounts && typeof moduleCounts === 'object' ? moduleCounts : {};
                    Object.keys(moduleBadgeElements).forEach(function (moduleKey) {
                        updateBadgeElement(
                            moduleBadgeElements[moduleKey],
                            Number(counts[moduleKey] || 0),
                            'ml-auto inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm'
                        );
                    });
                }

                function renderNotifications(items) {
                    if (!Array.isArray(items) || items.length === 0) {
                        list.innerHTML = '<div class="admin-notification-empty">' + escapeHtml(notificationText.empty) + '</div>';
                        return;
                    }

                    list.innerHTML = items.map(function (item) {
                        const notificationId = Number(item && item.id ? item.id : 0);
                        const title = escapeHtml(item && item.title ? item.title : notificationText.systemTitle);
                        const message = escapeHtml(item && item.message ? item.message : '');
                        const createdAt = escapeHtml(item && item.created_at_display ? item.created_at_display : '');
                        const actionUrl = escapeHtml(item && item.action_url ? item.action_url : allNotificationsUrl);
                        const isRead = Boolean(item && item.is_read);

                        return ''
                            + '<a class="admin-notification-dropdown-item' + (isRead ? '' : ' is-unread') + '" href="' + actionUrl + '" data-notification-id="' + notificationId + '">'
                            + '<div class="admin-notification-item-head">'
                            + '<div class="min-w-0">'
                            + '<div class="admin-notification-item-title">' + title + '</div>'
                            + '<div class="admin-notification-item-meta">' + createdAt + '</div>'
                            + '</div>'
                            + (isRead ? '' : '<span class="admin-notification-item-dot" aria-hidden="true"></span>')
                            + '</div>'
                            + (message !== '' ? '<div class="admin-notification-item-message">' + message + '</div>' : '')
                            + '</a>';
                    }).join('');
                }

                function dismissToast(toastElement) {
                    if (!(toastElement instanceof HTMLElement)) {
                        return;
                    }

                    toastElement.classList.remove('is-visible');
                    window.setTimeout(function () {
                        toastElement.remove();
                    }, 220);
                }

                function showNotificationToast(item) {
                    if (!(toastStack instanceof HTMLElement) || !item || typeof item !== 'object') {
                        return;
                    }

                    const notificationId = Number(item.id || 0);
                    if (notificationId <= 0 || toastStack.querySelector('[data-toast-notification-id="' + String(notificationId) + '"]')) {
                        return;
                    }

                    const title = escapeHtml(item.title || notificationText.systemTitle);
                    const message = escapeHtml(item.message || '');
                    const createdAt = escapeHtml(item.created_at_display || '');
                    const actionUrl = item.action_url || allNotificationsUrl;

                    const toastElement = document.createElement('article');
                    toastElement.className = 'admin-notification-toast';
                    toastElement.setAttribute('data-toast-notification-id', String(notificationId));
                    toastElement.innerHTML = ''
                        + '<div class="admin-notification-toast-head">'
                        + '<div>'
                        + '<div class="admin-notification-toast-kicker">' + escapeHtml(notificationText.newTitle) + '</div>'
                        + '<div class="admin-notification-toast-title">' + title + '</div>'
                        + '</div>'
                        + '<button type="button" class="admin-notification-toast-close" aria-label="' + escapeHtml(notificationText.close) + '">×</button>'
                        + '</div>'
                        + (message !== '' ? '<div class="admin-notification-toast-message">' + message + '</div>' : '')
                        + '<div class="admin-notification-toast-actions">'
                        + '<span class="admin-notification-toast-time">' + createdAt + '</span>'
                        + '<a class="admin-notification-toast-link" href="' + escapeHtml(actionUrl) + '" data-notification-id="' + notificationId + '">' + escapeHtml(notificationText.openDetail) + '</a>'
                        + '</div>';

                    const closeButton = toastElement.querySelector('.admin-notification-toast-close');
                    if (closeButton instanceof HTMLButtonElement) {
                        closeButton.addEventListener('click', function () {
                            dismissToast(toastElement);
                        });
                    }

                    const actionLink = toastElement.querySelector('.admin-notification-toast-link');
                    if (actionLink instanceof HTMLAnchorElement) {
                        actionLink.addEventListener('click', function () {
                            markNotificationRead(notificationId);
                        });
                    }

                    toastStack.prepend(toastElement);
                    while (toastStack.children.length > 3) {
                        toastStack.lastElementChild?.remove();
                    }

                    window.requestAnimationFrame(function () {
                        toastElement.classList.add('is-visible');
                    });

                    window.setTimeout(function () {
                        dismissToast(toastElement);
                    }, 7000);
                }

                async function refreshNotifications() {
                    try {
                        const response = await fetch(feedUrl, {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const payload = await response.json();

                        if (!response.ok || !payload || payload.status !== 'success') {
                            return;
                        }

                        const data = payload.data && typeof payload.data === 'object' ? payload.data : {};
                        const items = Array.isArray(data.items) ? data.items : [];
                        const incomingIds = items
                            .map(function (item) { return Number(item && item.id ? item.id : 0); })
                            .filter(function (id) { return id > 0; });

                        if (!didHydrateNotifications) {
                            incomingIds.forEach(function (id) {
                                knownNotificationIds.add(id);
                            });
                            didHydrateNotifications = true;
                        } else {
                            items.forEach(function (item) {
                                const notificationId = Number(item && item.id ? item.id : 0);
                                if (notificationId <= 0 || knownNotificationIds.has(notificationId)) {
                                    return;
                                }

                                knownNotificationIds.add(notificationId);
                                showNotificationToast(item);
                            });
                        }

                        updateUnreadDisplay(Number(data.unread_count || 0));
                        updateModuleBadges(data.module_counts || {});
                        renderNotifications(items);
                    } catch (error) {
                        // Keep existing dropdown content if polling fails.
                    }
                }

                function markNotificationRead(notificationId) {
                    const resolvedId = Math.max(0, Number(notificationId || 0));
                    if (resolvedId <= 0) {
                        return;
                    }

                    const body = new URLSearchParams();
                    body.set('_csrf', csrfToken);
                    body.set('id', String(resolvedId));

                    fetch(markReadUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: body.toString(),
                        keepalive: true,
                    }).catch(function () {
                        // Ignore mark-read transport errors and let the next poll fix the badge.
                    });
                }

                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    setDropdownOpen(!dropdown.classList.contains('is-open'));
                    if (!dropdown.classList.contains('is-open')) {
                        return;
                    }

                    refreshNotifications();
                });

                document.addEventListener('click', function (event) {
                    if (!(event.target instanceof Node)) {
                        return;
                    }

                    const notificationLink = event.target instanceof HTMLElement
                        ? event.target.closest('[data-notification-id]')
                        : null;
                    if (notificationLink instanceof HTMLAnchorElement) {
                        markNotificationRead(notificationLink.getAttribute('data-notification-id'));
                    }

                    if (!shell.contains(event.target)) {
                        setDropdownOpen(false);
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        setDropdownOpen(false);
                    }
                });

                refreshNotifications();
                window.setInterval(refreshNotifications, pollIntervalMs);
            })();
        </script>

        <div class="admin-ui min-w-0 grid gap-4">
