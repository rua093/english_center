        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/sceditor@3/minified/sceditor.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sceditor@3/minified/formats/bbcode.min.js"></script>
<script>
    (function () {
        'use strict';

        const PAGINATION_SCROLL_KEY = 'admin-ui:pagination-scroll';
        const EDIT_MODAL_ID = 'admin-edit-modal';
        const ROW_DETAIL_TRIGGER_ATTR = 'data-admin-row-detail';
        const AJAX_TABLE_ROOT_SELECTOR = '[data-ajax-table-root="1"]';
        const AJAX_SEARCH_SELECTOR = 'input[data-ajax-search="1"]';
        const AJAX_PER_PAGE_SELECTOR = 'select[data-ajax-per-page="1"]';
        const AJAX_FILTER_SELECTOR = '[data-ajax-filter="1"]';
        const AJAX_TBODY_SELECTOR = 'tbody[data-ajax-tbody="1"]';
        const AJAX_PAGINATION_SELECTOR = '[data-ajax-pagination="1"]';
        const AJAX_ROW_INFO_SELECTOR = '[data-ajax-row-info="1"]';
        const ADMIN_MAIN_CONTENT_SELECTOR = '[data-admin-main-content="1"]';
        const ajaxTableControllers = new WeakMap();
        const ajaxTableSearchTimers = new WeakMap();
        const bbcodeTextareaSyncLocks = new WeakSet();
        let adminNavigationController = null;

        function escapeBbcodeHtml(value) {
            return String(value || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        function sanitizeBbcodeUrl(rawValue) {
            const value = String(rawValue || '').trim();
            if (value === '') {
                return '';
            }

            if (/^(https?:|mailto:|tel:|\/)/i.test(value)) {
                return value;
            }

            return '';
        }

        function replaceSimpleBbcodeTag(content, tagName, htmlOpen, htmlClose) {
            let output = content;
            for (let index = 0; index < 6; index += 1) {
                const updated = output.replace(new RegExp('\\[' + tagName + '\\]([\\s\\S]*?)\\[\\/' + tagName + '\\]', 'gi'), htmlOpen + '$1' + htmlClose);
                if (updated === output) {
                    break;
                }
                output = updated;
            }
            return output;
        }

        function renderBbcodeHtml(rawValue) {
            const rawText = String(rawValue || '').replaceAll('\r\n', '\n').replaceAll('\r', '\n');
            if (rawText.trim() === '') {
                return '';
            }

            const placeholders = [];
            let placeholderIndex = 0;
            const tokenPrefix = '__BBCODE_TOKEN_';

            let content = rawText.replace(/\[code\]([\s\S]*?)\[\/code\]/gi, function (_, codeValue) {
                const token = tokenPrefix + String(placeholderIndex++) + '__';
                placeholders.push({
                    token: token,
                    html: '<pre class="bbcode-code"><code>' + escapeBbcodeHtml(codeValue) + '</code></pre>',
                });
                return token;
            });

            content = content.replace(/\[img\]([\s\S]*?)\[\/img\]/gi, function (_, imageUrl) {
                const token = tokenPrefix + String(placeholderIndex++) + '__';
                const safeUrl = sanitizeBbcodeUrl(imageUrl);
                placeholders.push({
                    token: token,
                    html: safeUrl !== ''
                        ? '<img class="bbcode-image" src="' + escapeBbcodeHtml(safeUrl) + '" alt="BBCode image">'
                        : escapeBbcodeHtml('[img]' + String(imageUrl || '') + '[/img]'),
                });
                return token;
            });

            content = escapeBbcodeHtml(content);
            content = replaceSimpleBbcodeTag(content, 'b', '<strong>', '</strong>');
            content = replaceSimpleBbcodeTag(content, 'i', '<em>', '</em>');
            content = replaceSimpleBbcodeTag(content, 'u', '<u>', '</u>');
            content = replaceSimpleBbcodeTag(content, 's', '<s>', '</s>');
            content = replaceSimpleBbcodeTag(content, 'quote', '<blockquote class="bbcode-quote">', '</blockquote>');

            content = content.replace(/\[url=([^\]]+)\]([\s\S]*?)\[\/url\]/gi, function (_, urlValue, labelValue) {
                const safeUrl = sanitizeBbcodeUrl(urlValue);
                if (safeUrl === '') {
                    return String(labelValue || '');
                }

                return '<a class="bbcode-link" href="' + escapeBbcodeHtml(safeUrl) + '" target="_blank" rel="noopener noreferrer">' + String(labelValue || '') + '</a>';
            });

            content = content.replace(/\[url\]([\s\S]*?)\[\/url\]/gi, function (_, urlValue) {
                const normalizedUrl = String(urlValue || '').trim();
                const safeUrl = sanitizeBbcodeUrl(normalizedUrl);
                if (safeUrl === '') {
                    return escapeBbcodeHtml(normalizedUrl);
                }

                return '<a class="bbcode-link" href="' + escapeBbcodeHtml(safeUrl) + '" target="_blank" rel="noopener noreferrer">' + escapeBbcodeHtml(normalizedUrl) + '</a>';
            });

            content = content.replace(/\n/g, '<br>');

            placeholders.forEach(function (placeholder) {
                content = content.replaceAll(placeholder.token, placeholder.html);
            });

            content = content.replace(/<br>\s*(<\/(?:blockquote|pre)>)/gi, '$1');
            content = content.replace(/(<(?:blockquote|pre)[^>]*>)\s*<br>/gi, '$1');
            return content;
        }

        function renderBbcodeFallback(rawValue) {
            return renderBbcodeHtml(rawValue);
        }

        function bbcodeToolbarIconMarkup(name) {
            const icons = {
                bold: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h6a4 4 0 0 1 0 8H7z"></path><path d="M7 13h7a4 4 0 0 1 0 8H7z"></path></svg>',
                italic: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h4"></path><path d="M6 20h4"></path><path d="M14 4 10 20"></path></svg>',
                underline: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4v7a5 5 0 0 0 10 0V4"></path><path d="M5 20h14"></path></svg>',
                strike: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 12h12"></path><path d="M9 6.5A3.5 3.5 0 0 1 12 5h.5A3.5 3.5 0 0 1 16 8.5"></path><path d="M15 17.5A3.5 3.5 0 0 1 12.5 19H12A4 4 0 0 1 8 15"></path></svg>',
                link: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 1 0-7l1.2-1.2a5 5 0 0 1 7 7L17 13"></path><path d="M14 11a5 5 0 0 1 0 7l-1.2 1.2a5 5 0 1 1-7-7L7 11"></path></svg>',
                image: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"></rect><circle cx="9" cy="10" r="1.4"></circle><path d="m20 16-4.2-4.2a1.5 1.5 0 0 0-2.1 0L8 17"></path></svg>',
                quote: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 8H6a1 1 0 0 0-1 1v4a2 2 0 0 0 2 2h2V8z"></path><path d="M18 8h-3a1 1 0 0 0-1 1v4a2 2 0 0 0 2 2h2V8z"></path></svg>',
                code: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8-4 4 4 4"></path><path d="m16 8 4 4-4 4"></path><path d="m14 4-4 16"></path></svg>',
                source: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 7-5 5 5 5"></path><path d="m15 7 5 5-5 5"></path></svg>',
            };

            return icons[name] || '';
        }

        function decorateBbcodeToolbar(editor) {
            if (!(editor instanceof HTMLElement)) {
                return;
            }

            const labels = {
                bold: 'In đậm',
                italic: 'In nghiêng',
                underline: 'Gạch chân',
                strike: 'Gạch ngang',
                link: 'Chèn liên kết',
                image: 'Chèn hình ảnh',
                quote: 'Trích dẫn',
                code: 'Khối mã',
                source: 'Chế độ BBCode',
            };

            Object.keys(labels).forEach(function (name) {
                const button = editor.querySelector('.sceditor-button-' + name);
                if (!(button instanceof HTMLElement)) {
                    return;
                }

                if (button.dataset.bbcodeIconReady === '1') {
                    return;
                }

                button.setAttribute('title', labels[name]);
                button.setAttribute('aria-label', labels[name]);
                button.innerHTML = bbcodeToolbarIconMarkup(name);
                button.dataset.bbcodeIconReady = '1';
            });
        }

        function normalizeBbcodeToolbarState(editor) {
            if (!(editor instanceof HTMLElement)) {
                return;
            }

            editor.querySelectorAll(
                '.sceditor-button-bold, .sceditor-button-italic, .sceditor-button-underline, .sceditor-button-strike, .sceditor-button-link, .sceditor-button-image, .sceditor-button-quote, .sceditor-button-code'
            ).forEach(function (button) {
                if (button instanceof HTMLElement) {
                    button.classList.remove('disabled');
                    button.removeAttribute('disabled');
                    button.setAttribute('aria-disabled', 'false');
                }
            });
        }

        function getBbcodeEditorInstance(input) {
            if (!(input instanceof HTMLTextAreaElement) || typeof window.sceditor === 'undefined' || !window.sceditor) {
                return null;
            }

            try {
                return typeof window.sceditor.instance === 'function' ? (window.sceditor.instance(input) || null) : null;
            } catch (error) {
                return null;
            }
        }

        function syncBbcodeEditorOriginalValue(input) {
            const instance = getBbcodeEditorInstance(input);
            if (instance && typeof instance.updateOriginal === 'function') {
                instance.updateOriginal();
            }
        }

        function syncBbcodeEditorFromTextarea(input) {
            if (!(input instanceof HTMLTextAreaElement)) {
                return;
            }

            const instance = getBbcodeEditorInstance(input);
            if (!instance || typeof instance.val !== 'function') {
                return;
            }

            const nextValue = String(input.value || '');
            const currentValue = String(instance.val() || '');
            if (currentValue === nextValue) {
                return;
            }

            bbcodeTextareaSyncLocks.add(input);
            try {
                instance.val(nextValue);
                syncBbcodeEditorOriginalValue(input);
            } finally {
                bbcodeTextareaSyncLocks.delete(input);
            }
        }

        function setBbcodeEditorValue(input, value) {
            if (!(input instanceof HTMLTextAreaElement)) {
                return;
            }

            input.value = String(value || '');
            syncBbcodeEditorFromTextarea(input);
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function refreshBbcodeEditorUi(input, shouldFocus) {
            if (!(input instanceof HTMLTextAreaElement)) {
                return;
            }

            const editor = input.closest('[data-bbcode-editor="1"]');
            if (editor instanceof HTMLElement) {
                decorateBbcodeToolbar(editor);
                normalizeBbcodeToolbarState(editor);
            }

            const instance = getBbcodeEditorInstance(input);
            if (!instance) {
                return;
            }

            if (typeof instance.updateOriginal === 'function') {
                instance.updateOriginal();
            }

            if (shouldFocus === true && typeof instance.focus === 'function') {
                window.requestAnimationFrame(function () {
                    try {
                        instance.focus();
                    } catch (error) {
                        // Keep modal flow stable even if the editor refuses focus.
                    }

                    if (editor instanceof HTMLElement) {
                        normalizeBbcodeToolbarState(editor);
                    }
                });
            }
        }

        function syncBbcodeEditorsInForm(form) {
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            form.querySelectorAll('textarea[data-bbcode-input="1"]').forEach(function (field) {
                if (field instanceof HTMLTextAreaElement) {
                    syncBbcodeEditorOriginalValue(field);
                }
            });
        }

        function initBbcodeEditors(root) {
            const scope = (root && typeof root.querySelectorAll === 'function') ? root : document;
            const editors = scope.querySelectorAll('[data-bbcode-editor="1"]');
            editors.forEach(function (editor) {
                if (!(editor instanceof HTMLElement) || editor.dataset.bbcodeReady === '1') {
                    return;
                }

                const input = editor.querySelector('textarea[data-bbcode-input="1"]');
                if (!(input instanceof HTMLTextAreaElement)) {
                    return;
                }

                input.addEventListener('input', function () {
                    if (bbcodeTextareaSyncLocks.has(input)) {
                        return;
                    }

                    syncBbcodeEditorFromTextarea(input);
                });

                if (typeof window.sceditor !== 'undefined' && window.sceditor && typeof window.sceditor.create === 'function' && !getBbcodeEditorInstance(input)) {
                    window.sceditor.create(input, {
                        format: 'bbcode',
                        style: 'https://cdn.jsdelivr.net/npm/sceditor@3/minified/themes/content/default.min.css',
                        autoUpdate: true,
                        resizeEnabled: true,
                        width: '100%',
                        height: Math.max(260, Number(input.rows || 0) * 30),
                        toolbar: 'bold,italic,underline,strike|link,image|quote,code|source',
                    });
                }

                decorateBbcodeToolbar(editor);
                normalizeBbcodeToolbarState(editor);
                syncBbcodeEditorFromTextarea(input);
                editor.dataset.bbcodeReady = '1';
            });
        }

        window.adminBbcode = {
            initEditors: initBbcodeEditors,
            renderHtml: renderBbcodeFallback,
            setValue: setBbcodeEditorValue,
            syncTextarea: syncBbcodeEditorOriginalValue,
            refreshUi: refreshBbcodeEditorUi,
        };

        function hasPaginationParam(url) {
            const entries = Array.from(url.searchParams.keys());
            return entries.some(function (key) {
                const normalized = String(key || '').toLowerCase();
                return normalized === 'page' || normalized.endsWith('_page');
            });
        }

        function restorePaginationScroll() {
            const currentUrl = new URL(window.location.href);
            if (!hasPaginationParam(currentUrl)) {
                return;
            }

            const raw = sessionStorage.getItem(PAGINATION_SCROLL_KEY);
            if (!raw) {
                return;
            }

            let payload = null;
            try {
                payload = JSON.parse(raw);
            } catch (error) {
                sessionStorage.removeItem(PAGINATION_SCROLL_KEY);
                return;
            }

            if (!payload || payload.path !== currentUrl.pathname) {
                sessionStorage.removeItem(PAGINATION_SCROLL_KEY);
                return;
            }

            const timestamp = Number(payload.timestamp || 0);
            const age = Date.now() - timestamp;
            if (age > 120000) {
                sessionStorage.removeItem(PAGINATION_SCROLL_KEY);
                return;
            }

            const targetY = Math.max(0, Number(payload.scrollY || 0));
            requestAnimationFrame(function () {
                window.scrollTo(0, targetY);
            });

            setTimeout(function () {
                window.scrollTo(0, targetY);
                sessionStorage.removeItem(PAGINATION_SCROLL_KEY);
            }, 80);
        }

        function bindPaginationScrollSaver() {
            document.addEventListener('click', function (event) {
                const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
                if (!link) {
                    return;
                }

                const destination = new URL(link.getAttribute('href') || '', window.location.href);
                if (destination.origin !== window.location.origin || !hasPaginationParam(destination)) {
                    return;
                }

                const anchorContainer = link.closest('article, section, .admin-ui') || document.body;
                const containerTop = anchorContainer.getBoundingClientRect().top + window.scrollY;
                const targetScroll = Math.max(0, Math.round(containerTop - 20));

                sessionStorage.setItem(PAGINATION_SCROLL_KEY, JSON.stringify({
                    path: destination.pathname,
                    scrollY: targetScroll,
                    timestamp: Date.now(),
                }));
            });
        }

        function isCreateSaveForm(form) {
            if (!(form instanceof HTMLFormElement)) {
                return false;
            }

            if (form.dataset.keepCreateDefaults === '1') {
                return false;
            }

            const actionAttr = String(form.getAttribute('action') || '').trim();
            if (actionAttr === '') {
                return false;
            }

            let actionPath = actionAttr;
            try {
                actionPath = new URL(actionAttr, window.location.href).pathname;
            } catch (error) {
                actionPath = actionAttr;
            }

            if (!actionPath.startsWith('/api/') || !actionPath.endsWith('/save')) {
                return false;
            }

            const idField = form.querySelector('input[name="id"]');
            if (!(idField instanceof HTMLInputElement)) {
                return false;
            }

            return Number(idField.value || 0) === 0;
        }

        function ensureEmptyOption(selectElement) {
            if (selectElement.querySelector('option[value=""]')) {
                return;
            }

            const option = document.createElement('option');
            option.value = '';
            option.textContent = '-- Chọn --';
            selectElement.insertBefore(option, selectElement.firstChild);
        }

        function clearCreateSaveForms() {
            const forms = document.querySelectorAll('.admin-ui form[action]');
            forms.forEach(function (form) {
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                if (form.dataset.createFormBlankApplied === '1') {
                    return;
                }

                if (!isCreateSaveForm(form)) {
                    return;
                }

                const fields = form.querySelectorAll('input, textarea, select');
                fields.forEach(function (field) {
                    if (field instanceof HTMLInputElement) {
                        const type = String(field.type || 'text').toLowerCase();
                        if (type === 'hidden' || type === 'submit' || type === 'button' || type === 'reset' || type === 'image') {
                            return;
                        }

                        if (type === 'checkbox' || type === 'radio') {
                            field.checked = false;
                            return;
                        }

                        field.value = '';
                        return;
                    }

                    if (field instanceof HTMLTextAreaElement) {
                        if (field.matches('textarea[data-bbcode-input="1"]') && window.adminBbcode && typeof window.adminBbcode.setValue === 'function') {
                            window.adminBbcode.setValue(field, '');
                        } else {
                            field.value = '';
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        return;
                    }

                    if (field instanceof HTMLSelectElement) {
                        if (field.multiple) {
                            Array.from(field.options).forEach(function (option) {
                                option.selected = false;
                            });
                        } else {
                            ensureEmptyOption(field);
                            field.value = '';
                        }

                        field.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });

                form.dataset.createFormBlankApplied = '1';
            });
        }

        function toUrl(rawUrl) {
            try {
                return new URL(String(rawUrl || ''), window.location.href);
            } catch (error) {
                return null;
            }
        }

        function isApiSavePath(actionPath) {
            return actionPath.startsWith('/api/') && (
                actionPath.endsWith('/save') ||
                actionPath.endsWith('/update') ||
                actionPath.endsWith('/update-registration')
            );
        }

        function isLikelyEditUrl(url) {
            if (!(url instanceof URL) || url.origin !== window.location.origin) {
                return false;
            }

            if (url.searchParams.has('edit')) {
                return true;
            }

            if (url.searchParams.has('registration_edit')) {
                return true;
            }

            const pageParam = String(url.searchParams.get('page') || '').toLowerCase();
            if (pageParam.includes('-edit')) {
                return true;
            }

            const pathname = String(url.pathname || '').toLowerCase();
            return pathname.includes('-edit') || /\/edit(\/|$)/i.test(pathname);
        }

        function ensureEditModal() {
            let modal = document.getElementById(EDIT_MODAL_ID);
            if (modal instanceof HTMLElement) {
                return modal;
            }

            modal = document.createElement('div');
            modal.id = EDIT_MODAL_ID;
            modal.className = 'admin-edit-modal-backdrop hidden';
            modal.innerHTML = '' +
                '<div class="admin-edit-modal-dialog" role="dialog" aria-modal="true" aria-label="Cập nhật dữ liệu">' +
                    '<div class="admin-edit-modal-header">' +
                        '<h3 class="admin-edit-modal-title">Cập nhật dữ liệu</h3>' +
                        '<button type="button" class="admin-edit-modal-close" data-admin-edit-close="1">Đóng</button>' +
                    '</div>' +
                    '<div class="admin-edit-modal-body"></div>' +
                '</div>';

            modal.addEventListener('click', function (event) {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                if (target.getAttribute('data-admin-edit-close') === '1' || target === modal) {
                    closeEditModal();
                }
            });

            document.body.appendChild(modal);
            return modal;
        }

        function setEditModalTitle(modal, title) {
            if (!(modal instanceof HTMLElement)) {
                return;
            }

            const titleElement = modal.querySelector('.admin-edit-modal-title');
            if (titleElement instanceof HTMLElement) {
                titleElement.textContent = String(title || '').trim() || 'Cập nhật dữ liệu';
            }
        }

        function closeEditModal() {
            const modal = document.getElementById(EDIT_MODAL_ID);
            if (!(modal instanceof HTMLElement)) {
                return;
            }

            const shouldReload = modal.dataset.reloadOnClose === '1';

            modal.classList.add('hidden');
            const dialog = modal.querySelector('.admin-edit-modal-dialog');
            if (dialog instanceof HTMLElement) {
                dialog.classList.remove('is-process-modal');
            }
            const body = modal.querySelector('.admin-edit-modal-body');
            if (body) {
                body.innerHTML = '';
            }
            delete modal.dataset.reloadOnClose;
            document.body.classList.remove('admin-modal-open');

            if (shouldReload) {
                window.location.reload();
            }
        }

        function isSidebarAjaxLink(anchor) {
            if (!(anchor instanceof HTMLAnchorElement)) {
                return false;
            }

            if (!anchor.matches('.admin-sidebar-link, .admin-sidebar-brand')) {
                return false;
            }

            const url = toUrl(anchor.href);
            if (!(url instanceof URL) || url.origin !== window.location.origin) {
                return false;
            }

            return String(url.searchParams.get('page') || '').trim() !== '';
        }

        function executeScriptsWithin(rootElement) {
            if (!(rootElement instanceof HTMLElement)) {
                return;
            }

            const originalDocumentAddEventListener = document.addEventListener.bind(document);
            const originalWindowAddEventListener = window.addEventListener.bind(window);

            document.addEventListener = function (type, listener, options) {
                if (type === 'DOMContentLoaded' && typeof listener === 'function') {
                    listener.call(document, new Event('DOMContentLoaded'));
                    return;
                }

                return originalDocumentAddEventListener(type, listener, options);
            };

            window.addEventListener = function (type, listener, options) {
                if (type === 'load' && typeof listener === 'function') {
                    listener.call(window, new Event('load'));
                    return;
                }

                return originalWindowAddEventListener(type, listener, options);
            };

            const scripts = Array.from(rootElement.querySelectorAll('script'));
            try {
                scripts.forEach(function (oldScript) {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(function (attribute) {
                        newScript.setAttribute(attribute.name, attribute.value);
                    });

                    if (!oldScript.src) {
                        newScript.textContent = oldScript.textContent || '';
                    }

                    oldScript.parentNode?.replaceChild(newScript, oldScript);
                });
            } finally {
                document.addEventListener = originalDocumentAddEventListener;
                window.addEventListener = originalWindowAddEventListener;
            }
        }

        function syncSidebarActiveState(nextDocument) {
            if (!(nextDocument instanceof Document)) {
                return;
            }

            const nextActiveLink = nextDocument.querySelector('.admin-sidebar-link.is-active');
            const nextActiveUrl = nextActiveLink instanceof HTMLAnchorElement ? toUrl(nextActiveLink.href) : null;
            const nextActiveKey = nextActiveUrl ? nextActiveUrl.pathname + nextActiveUrl.search : '';

            document.querySelectorAll('.admin-sidebar-link').forEach(function (link) {
                if (!(link instanceof HTMLAnchorElement)) {
                    return;
                }

                const currentUrl = toUrl(link.href);
                const currentKey = currentUrl ? currentUrl.pathname + currentUrl.search : '';
                link.classList.toggle('is-active', nextActiveKey !== '' && currentKey === nextActiveKey);
            });
        }

        async function replaceAdminMainContentFromUrl(targetUrl, historyMode) {
            const currentMain = document.querySelector(ADMIN_MAIN_CONTENT_SELECTOR);
            if (!(currentMain instanceof HTMLElement)) {
                window.location.href = targetUrl.toString();
                return;
            }

            if (adminNavigationController) {
                adminNavigationController.abort();
            }

            adminNavigationController = new AbortController();
            currentMain.classList.add('is-ajax-loading');

            try {
                const response = await fetch(targetUrl.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: adminNavigationController.signal,
                });

                if (!response.ok) {
                    window.location.href = targetUrl.toString();
                    return;
                }

                const html = await response.text();
                const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                const nextMain = nextDocument.querySelector(ADMIN_MAIN_CONTENT_SELECTOR);
                if (!(nextMain instanceof HTMLElement)) {
                    window.location.href = targetUrl.toString();
                    return;
                }

                if (typeof window.__closeAdminEditModal === 'function') {
                    window.__closeAdminEditModal();
                }

                currentMain.replaceWith(nextMain);
                document.title = String(nextDocument.title || document.title);
                syncSidebarActiveState(nextDocument);

                executeScriptsWithin(nextMain);

                if (historyMode === 'push') {
                    window.history.pushState({ adminAjax: true }, '', targetUrl.toString());
                } else if (historyMode === 'replace') {
                    window.history.replaceState({ adminAjax: true }, '', targetUrl.toString());
                }

                window.scrollTo(0, 0);

                if (typeof window.__refreshAdminUi === 'function') {
                    await window.__refreshAdminUi(nextMain);
                }
            } catch (error) {
                if (!(error instanceof DOMException) || error.name !== 'AbortError') {
                    window.location.href = targetUrl.toString();
                }
            } finally {
                const activeMain = document.querySelector(ADMIN_MAIN_CONTENT_SELECTOR);
                if (activeMain instanceof HTMLElement) {
                    activeMain.classList.remove('is-ajax-loading');
                }
            }
        }

        function initAdminSidebarAjaxNavigation() {
            document.addEventListener('click', function (event) {
                const anchor = event.target instanceof Element ? event.target.closest('a[href]') : null;
                if (!(anchor instanceof HTMLAnchorElement) || !isSidebarAjaxLink(anchor)) {
                    return;
                }

                if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const targetUrl = toUrl(anchor.href);
                if (!(targetUrl instanceof URL)) {
                    return;
                }

                const currentUrl = new URL(window.location.href);
                if ((currentUrl.pathname + currentUrl.search) === (targetUrl.pathname + targetUrl.search)) {
                    return;
                }

                event.preventDefault();
                replaceAdminMainContentFromUrl(targetUrl, 'push');
            });

            window.addEventListener('popstate', function () {
                const currentMain = document.querySelector(ADMIN_MAIN_CONTENT_SELECTOR);
                if (!(currentMain instanceof HTMLElement)) {
                    return;
                }

                const targetUrl = new URL(window.location.href);
                if (String(targetUrl.searchParams.get('page') || '').trim() === '') {
                    window.location.reload();
                    return;
                }

                replaceAdminMainContentFromUrl(targetUrl, null);
            });
        }

        function extractEditSaveForm(doc) {
            const forms = Array.from(doc.querySelectorAll('form[action]'));
            for (const form of forms) {
                if (!(form instanceof HTMLFormElement)) {
                    continue;
                }

                const actionUrl = toUrl(form.getAttribute('action') || '');
                const actionPath = actionUrl ? actionUrl.pathname : String(form.getAttribute('action') || '');
                if (!isApiSavePath(actionPath)) {
                    continue;
                }

                const idField = form.querySelector('input[name="id"]');
                if (!(idField instanceof HTMLInputElement) || Number(idField.value || 0) <= 0) {
                    continue;
                }

                return form;
            }

            return null;
        }

        function extractEditModalSource(doc) {
            const sourceContainer = doc.querySelector('[data-edit-form-source="1"]');
            if (sourceContainer instanceof HTMLElement) {
                return sourceContainer;
            }

            return extractEditSaveForm(doc);
        }

        function buildModalForm(sourceElement) {
            const wrapper = document.createElement('div');
            wrapper.className = 'admin-ui';

            const modalMode = sourceElement instanceof HTMLElement ? String(sourceElement.getAttribute('data-edit-modal-mode') || '').trim() : '';
            if (modalMode !== '') {
                wrapper.classList.add('is-' + modalMode + '-modal');
                wrapper.setAttribute('data-edit-modal-mode', modalMode);
            }

            const hint = document.createElement('p');
            hint.className = 'admin-modal-helper';
            hint.textContent = 'Mọi thay đổi sẽ được lưu trực tiếp sau khi bấm nút Lưu.';
            wrapper.appendChild(hint);

            const cloneSource = sourceElement.cloneNode(true);
            if (!(cloneSource instanceof HTMLElement)) {
                return null;
            }

            cloneSource.classList.remove('hidden');
            cloneSource.removeAttribute('id');
            cloneSource.removeAttribute('aria-hidden');

            if (cloneSource instanceof HTMLFormElement) {
                cloneSource.setAttribute('autocomplete', 'off');
            }

            cloneSource.querySelectorAll('[id]').forEach(function (element) {
                element.removeAttribute('id');
            });
            cloneSource.querySelectorAll('label[for]').forEach(function (label) {
                label.removeAttribute('for');
            });
            cloneSource.querySelectorAll('a[href]').forEach(function (anchor) {
                anchor.remove();
            });
            cloneSource.querySelectorAll('form').forEach(function (form) {
                if (form instanceof HTMLFormElement) {
                    form.setAttribute('autocomplete', 'off');
                }
            });

            wrapper.appendChild(cloneSource);
            return wrapper;
        }

        function showModalFeedback(modalBody, type, message) {
            if (!(modalBody instanceof HTMLElement)) {
                return;
            }

            modalBody.querySelectorAll('.admin-edit-modal-error, .admin-edit-modal-success').forEach(function (node) {
                node.remove();
            });

            const box = document.createElement('div');
            box.className = type === 'success' ? 'admin-edit-modal-success' : 'admin-edit-modal-error';
            box.textContent = String(message || '').trim();
            modalBody.insertBefore(box, modalBody.firstChild);
        }

        function extractFlashMessageFromDoc(doc) {
            if (!(doc instanceof Document)) {
                return null;
            }

            const blocks = Array.from(doc.querySelectorAll('div'));
            for (const block of blocks) {
                if (!(block instanceof HTMLDivElement)) {
                    continue;
                }

                const className = String(block.className || '');
                const message = String(block.textContent || '').replace(/\s+/g, ' ').trim();
                if (message === '') {
                    continue;
                }

                if (className.includes('border-emerald-500')) {
                    return { type: 'success', message: message };
                }

                if (className.includes('border-rose-500')) {
                    return { type: 'error', message: message };
                }
            }

            return null;
        }

        function storeReloadFlash(type, message) {
            if (typeof window.sessionStorage === 'undefined') {
                return;
            }

            const normalizedType = String(type || '').trim();
            const normalizedMessage = String(message || '').trim();
            if (normalizedType === '' || normalizedMessage === '') {
                return;
            }

            window.sessionStorage.setItem('admin-ui:reload-flash', JSON.stringify({
                type: normalizedType,
                message: normalizedMessage,
            }));
        }

        function renderStoredReloadFlash() {
            if (typeof window.sessionStorage === 'undefined') {
                return;
            }

            const raw = window.sessionStorage.getItem('admin-ui:reload-flash');
            if (!raw) {
                return;
            }
            window.sessionStorage.removeItem('admin-ui:reload-flash');

            let payload = null;
            try {
                payload = JSON.parse(raw);
            } catch (error) {
                payload = null;
            }

            const type = payload && typeof payload.type === 'string' ? payload.type.trim() : '';
            const message = payload && typeof payload.message === 'string' ? payload.message.trim() : '';
            if (type === '' || message === '') {
                return;
            }

            const targetRoot = document.querySelector('.admin-ui.min-w-0.grid.gap-4') || document.querySelector('.admin-ui');
            if (!(targetRoot instanceof HTMLElement)) {
                return;
            }

            const box = document.createElement('div');
            box.className = type === 'success'
                ? 'rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700'
                : 'rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700';
            box.textContent = message;
            targetRoot.insertBefore(box, targetRoot.firstChild);
        }

        function markModalForReload(modalBody) {
            const modal = modalBody instanceof HTMLElement ? modalBody.closest('.admin-edit-modal-backdrop') : null;
            if (modal instanceof HTMLElement) {
                modal.dataset.reloadOnClose = '1';
            }
        }

        function parseProcessStatusList(value) {
            return String(value || '')
                .split(',')
                .map(function (item) {
                    return normalizeText(item);
                })
                .filter(function (item) {
                    return item !== '';
                });
        }

        function syncProcessConvertAvailability(modalBody) {
            if (!(modalBody instanceof HTMLElement)) {
                return;
            }

            const statusSelect = modalBody.querySelector('[data-process-status-select="1"]');
            const convertForm = modalBody.querySelector('[data-process-convert-form="1"]');
            if (!(convertForm instanceof HTMLFormElement)) {
                return;
            }

            const convertButton = convertForm.querySelector('[data-process-convert-button="1"]');
            const allowedStatuses = parseProcessStatusList(convertForm.getAttribute('data-valid-statuses') || '');
            const currentStatus = statusSelect instanceof HTMLSelectElement ? normalizeText(statusSelect.value) : '';
            const canConvert = allowedStatuses.indexOf(currentStatus) !== -1;

            if (convertButton instanceof HTMLButtonElement || convertButton instanceof HTMLInputElement) {
                convertButton.disabled = !canConvert;
                convertButton.setAttribute('aria-disabled', canConvert ? 'false' : 'true');
                if (canConvert) {
                    convertButton.setAttribute('title', 'Có thể chuyển đổi user.');
                } else {
                    convertButton.setAttribute('title', 'Trạng thái hiện tại chưa hợp lệ để chuyển đổi user.');
                }
            }
        }

        function bindProcessModalState(modalBody) {
            if (!(modalBody instanceof HTMLElement)) {
                return;
            }

            const statusSelect = modalBody.querySelector('[data-process-status-select="1"]');
            if (statusSelect instanceof HTMLSelectElement && statusSelect.dataset.processStateBound !== '1') {
                statusSelect.dataset.processStateBound = '1';
                statusSelect.addEventListener('change', function () {
                    syncProcessConvertAvailability(modalBody);
                });
            }

            syncProcessConvertAvailability(modalBody);
        }

        function syncProcessModalAfterUpdate(form, modalBody, actionPath) {
            if (!(form instanceof HTMLFormElement) || !(modalBody instanceof HTMLElement)) {
                return;
            }

            const statusSelect = form.querySelector('select[name="status"]');
            const badge = modalBody.querySelector('[data-process-status-badge="1"]');
            if (statusSelect instanceof HTMLSelectElement && badge instanceof HTMLElement) {
                const selectedOption = statusSelect.selectedOptions[0];
                badge.textContent = selectedOption ? String(selectedOption.textContent || '').trim() : badge.textContent;
            }

            if (actionPath.endsWith('/leads/update')) {
                const noteField = form.querySelector('textarea[name="admin_note"]');
                const convertNoteField = modalBody.querySelector('form[action="/api/leads/convert"] textarea[name="admin_note"]');
                if (noteField instanceof HTMLTextAreaElement && convertNoteField instanceof HTMLTextAreaElement) {
                    convertNoteField.value = noteField.value;
                }
                return;
            }

            if (actionPath.endsWith('/applications/update')) {
                const noteField = form.querySelector('textarea[name="hr_note"]');
                const convertNoteField = modalBody.querySelector('form[action="/api/applications/convert"] textarea[name="admin_note"]');
                if (noteField instanceof HTMLTextAreaElement && convertNoteField instanceof HTMLTextAreaElement) {
                    convertNoteField.value = noteField.value;
                }
            }

            syncProcessConvertAvailability(modalBody);
        }

        function buildReadonlyModalForm(sourceForm) {
            const wrapper = buildModalForm(sourceForm);
            if (!(wrapper instanceof HTMLElement)) {
                return null;
            }

            const helper = wrapper.querySelector('.admin-modal-helper');
            if (helper instanceof HTMLElement) {
                helper.textContent = 'Thông tin chi tiết của bản ghi. Biểu mẫu chỉ xem, không chỉnh sửa.';
            }

            const form = wrapper.querySelector('form');
            if (!(form instanceof HTMLFormElement)) {
                return wrapper;
            }

            form.setAttribute('data-readonly-form', '1');
            form.removeAttribute('action');

            form.querySelectorAll('input[type="hidden"]').forEach(function (field) {
                field.remove();
            });

            form.querySelectorAll('button[type="submit"], input[type="submit"], input[type="button"], input[type="reset"]').forEach(function (field) {
                field.remove();
            });

            form.querySelectorAll('input, select, textarea, button').forEach(function (field) {
                if (field instanceof HTMLInputElement) {
                    const type = normalizeText(field.type || 'text');
                    if (type === 'checkbox' || type === 'radio') {
                        field.disabled = true;
                        return;
                    }

                    if (type === 'file') {
                        field.disabled = true;
                        return;
                    }

                    if (type !== 'hidden' && type !== 'button' && type !== 'submit' && type !== 'reset') {
                        field.readOnly = true;
                    }
                    return;
                }

                if (field instanceof HTMLSelectElement) {
                    field.disabled = true;
                    return;
                }

                if (field instanceof HTMLTextAreaElement) {
                    field.readOnly = true;
                    return;
                }

                if (field instanceof HTMLButtonElement) {
                    field.disabled = true;
                }
            });

            return wrapper;
        }

        function bindRoleProfileSections(form) {
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const roleSelect = form.querySelector('select[name="role_id"]');
            if (!(roleSelect instanceof HTMLSelectElement)) {
                return;
            }

            const sections = Array.from(form.querySelectorAll('[data-role-profile]'));
            if (sections.length === 0) {
                return;
            }

            const emptyHint = form.querySelector('[data-role-profile-empty="1"]');

            function resolveRoleName() {
                const selectedOption = roleSelect.selectedOptions[0];
                if (!(selectedOption instanceof HTMLOptionElement)) {
                    return '';
                }

                const roleName = normalizeText(selectedOption.textContent || selectedOption.value || '');
                return roleName.replace(/\s+/g, '');
            }

            function toggleRoleSections() {
                const activeRole = resolveRoleName();
                let hasActiveSection = false;

                sections.forEach(function (section) {
                    if (!(section instanceof HTMLElement)) {
                        return;
                    }

                    const sectionRole = normalizeText(section.getAttribute('data-role-profile') || '').replace(/\s+/g, '');
                    const isActive = activeRole !== '' && sectionRole === activeRole;
                    hasActiveSection = hasActiveSection || isActive;

                    section.classList.toggle('hidden', !isActive);
                    section.querySelectorAll('input, select, textarea').forEach(function (field) {
                        if (
                            field instanceof HTMLInputElement
                            || field instanceof HTMLSelectElement
                            || field instanceof HTMLTextAreaElement
                        ) {
                            field.disabled = !isActive;
                        }
                    });
                });

                if (emptyHint instanceof HTMLElement) {
                    emptyHint.classList.toggle('hidden', hasActiveSection);
                }
            }

            roleSelect.addEventListener('change', toggleRoleSections);
            toggleRoleSections();
        }

        function bindModalFormSubmit(form, modalBody) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (form.dataset.submitting === '1') {
                    return;
                }

                form.dataset.submitting = '1';
                const submitButtons = Array.from(form.querySelectorAll('button[type="submit"], input[type="submit"]'));
                submitButtons.forEach(function (button) {
                    if (button instanceof HTMLButtonElement || button instanceof HTMLInputElement) {
                        button.disabled = true;
                    }
                });

                const errorBox = modalBody.querySelector('.admin-edit-modal-error');
                if (errorBox) {
                    errorBox.remove();
                }

                try {
                    const actionUrl = toUrl(form.getAttribute('action') || '');
                    if (!actionUrl) {
                        throw new Error('Không xác định được địa chỉ lưu dữ liệu.');
                    }

                    const actionPath = String(actionUrl.pathname || '');

                    const response = await fetch(actionUrl.toString(), {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: new FormData(form),
                    });

                    if (!response.ok) {
                        throw new Error('Máy chủ từ chối cập nhật dữ liệu. Vui lòng thử lại.');
                    }

                    const responseText = await response.text();
                    const responseDoc = new DOMParser().parseFromString(responseText, 'text/html');

                    if (actionPath.endsWith('/leads/update') || actionPath.endsWith('/applications/update')) {
                        markModalForReload(modalBody);
                        syncProcessModalAfterUpdate(form, modalBody, actionPath);
                        showModalFeedback(modalBody, 'success', 'Đã lưu cập nhật. Bạn có thể tiếp tục xử lý hoặc chuyển đổi tài khoản ngay trong popup này.');
                        return;
                    }

                    if (actionPath.endsWith('/leads/convert') || actionPath.endsWith('/applications/convert')) {
                        const flashInfo = extractFlashMessageFromDoc(responseDoc);
                        if (flashInfo) {
                            storeReloadFlash(flashInfo.type, flashInfo.message);
                        }
                        closeEditModal();
                        window.location.reload();
                        return;
                    }

                    closeEditModal();
                    window.location.reload();
                } catch (error) {
                    showModalFeedback(modalBody, 'error', error instanceof Error ? error.message : 'Cập nhật thất bại. Vui lòng thử lại.');
                } finally {
                    form.dataset.submitting = '0';
                    submitButtons.forEach(function (button) {
                        if (button instanceof HTMLButtonElement || button instanceof HTMLInputElement) {
                            button.disabled = false;
                        }
                    });
                }
            });
        }

        async function fetchEditSaveForm(url, errorMessage) {
            const response = await fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(errorMessage);
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            return extractEditModalSource(doc);
        }

        async function openEditModalForUrl(url) {
            const modal = ensureEditModal();
            const body = modal.querySelector('.admin-edit-modal-body');
            if (!(body instanceof HTMLElement)) {
                window.location.href = url.toString();
                return;
            }

            const dialog = modal.querySelector('.admin-edit-modal-dialog');
            if (dialog instanceof HTMLElement) {
                dialog.classList.remove('is-process-modal');
            }

            setEditModalTitle(modal, 'Cập nhật dữ liệu');
            body.innerHTML = '<div class="admin-edit-modal-loading">Đang tải biểu mẫu cập nhật...</div>';
            modal.classList.remove('hidden');
            document.body.classList.add('admin-modal-open');

            try {
                const sourceForm = await fetchEditSaveForm(url, 'Không thể tải biểu mẫu cập nhật.');
                if (!sourceForm) {
                    window.location.href = url.toString();
                    return;
                }

                const customTitle = sourceForm instanceof HTMLElement ? String(sourceForm.getAttribute('data-edit-modal-title') || '').trim() : '';
                const modalMode = sourceForm instanceof HTMLElement ? String(sourceForm.getAttribute('data-edit-modal-mode') || '').trim() : '';
                setEditModalTitle(modal, customTitle !== '' ? customTitle : 'Cập nhật dữ liệu');

                const wrapper = buildModalForm(sourceForm);
                if (!wrapper) {
                    window.location.href = url.toString();
                    return;
                }

                if (dialog instanceof HTMLElement && modalMode === 'process') {
                    dialog.classList.add('is-process-modal');
                }

                body.innerHTML = '';
                body.appendChild(wrapper);
                bindProcessModalState(body);

                wrapper.querySelectorAll('form').forEach(function (formElement) {
                    if (formElement instanceof HTMLFormElement) {
                        bindRoleProfileSections(formElement);
                        bindModalFormSubmit(formElement, body);
                    }
                });
            } catch (error) {
                const message = error instanceof Error ? error.message : 'Không thể mở biểu mẫu chỉnh sửa.';
                body.innerHTML = '<div class="admin-edit-modal-error">' + message + '</div>';
            }
        }

        function openEditModalFromValue(value) {
            const url = value instanceof URL ? value : toUrl(value);
            if (!url) {
                return false;
            }

            openEditModalForUrl(url);
            return true;
        }

        function bindGlobalEditModal() {
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeEditModal();
                }
            });

            document.addEventListener('click', function (event) {
                if (event.defaultPrevented || event.button !== 0) {
                    return;
                }

                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const target = event.target instanceof Element ? event.target.closest('a[href]') : null;
                if (!(target instanceof HTMLAnchorElement)) {
                    return;
                }

                if (target.getAttribute('data-no-edit-modal') === '1' || target.target === '_blank') {
                    return;
                }

                const url = toUrl(target.getAttribute('href') || '');
                if (!url || !isLikelyEditUrl(url)) {
                    return;
                }

                event.preventDefault();
                openEditModalForUrl(url);
            });
        }

        function normalizeText(value) {
            let text = String(value || '').toLowerCase().trim();
            if (typeof text.normalize === 'function') {
                text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }
            return text;
        }

        function extractCellText(cell) {
            if (!cell) {
                return '';
            }

            const clone = cell.cloneNode(true);
            clone.querySelectorAll('form, button, input, select, textarea').forEach(function (node) {
                node.remove();
            });
            return String(clone.textContent || '').replace(/\s+/g, ' ').trim();
        }

        function getTableHeaderTitles(table) {
            const headCells = Array.from(table.querySelectorAll('thead th'));
            return headCells.map(function (cell) {
                return String(cell.textContent || '').trim();
            });
        }

        function findActionColumnIndex(headerTitles, table) {
            const blockedNames = ['thao tac', 'hanh dong', 'action'];
            for (let index = 0; index < headerTitles.length; index += 1) {
                if (blockedNames.indexOf(normalizeText(headerTitles[index])) !== -1) {
                    return index;
                }
            }

            if (table instanceof HTMLTableElement) {
                const hitCounter = new Map();
                const rows = Array.from(table.querySelectorAll('tbody tr'));
                rows.forEach(function (row) {
                    if (!(row instanceof HTMLTableRowElement)) {
                        return;
                    }

                    const cells = Array.from(row.querySelectorAll('td'));
                    cells.forEach(function (cell, index) {
                        if (!(cell instanceof HTMLTableCellElement)) {
                            return;
                        }

                        if (cell.querySelector('a, button, input[type="submit"]')) {
                            hitCounter.set(index, (hitCounter.get(index) || 0) + 1);
                        }
                    });
                });

                if (hitCounter.size > 0) {
                    let bestIndex = -1;
                    let bestScore = -1;
                    hitCounter.forEach(function (score, index) {
                        if (score > bestScore || (score === bestScore && index > bestIndex)) {
                            bestIndex = index;
                            bestScore = score;
                        }
                    });

                    if (bestIndex >= 0) {
                        return bestIndex;
                    }
                }
            }

            if (headerTitles.length > 0) {
                return headerTitles.length - 1;
            }

            return -1;
        }

        function resolveDetailCellValue(cell) {
            if (!(cell instanceof HTMLElement)) {
                return '';
            }

            const fullValueNode = cell.querySelector('[data-full-value]');
            if (fullValueNode instanceof HTMLElement) {
                const explicitValue = String(fullValueNode.getAttribute('data-full-value') || '').trim();
                if (explicitValue !== '') {
                    return explicitValue;
                }
            }

            return extractCellText(cell);
        }

        function extractRowDetailPairs(row, headerTitles, actionColumnIndex) {
            if (!(row instanceof HTMLTableRowElement)) {
                return [];
            }

            const cells = Array.from(row.querySelectorAll('td'));
            const pairs = [];
            headerTitles.forEach(function (headerTitle, index) {
                if (index === actionColumnIndex) {
                    return;
                }

                const label = String(headerTitle || ('Trường ' + (index + 1))).trim();
                if (label === '') {
                    return;
                }

                const value = resolveDetailCellValue(cells[index] || null);
                pairs.push({
                    label: label,
                    value: value === '' ? 'Chưa cập nhật' : value,
                });
            });

            return pairs;
        }

        function humanizeFieldName(fieldName) {
            const compact = String(fieldName || '').replace(/\[\]$/, '').replace(/[_-]+/g, ' ').trim();
            if (compact === '') {
                return 'Thông tin';
            }
            return compact.charAt(0).toUpperCase() + compact.slice(1);
        }

        function cleanLabelText(labelElement) {
            if (!(labelElement instanceof HTMLElement)) {
                return '';
            }

            const clone = labelElement.cloneNode(true);
            clone.querySelectorAll('input, select, textarea, button, [data-bbcode-editor="1"], .bbcode-preview-label, .bbcode-preview, .bbcode-hint').forEach(function (node) {
                node.remove();
            });

            return String(clone.textContent || '').replace(/\s+/g, ' ').trim();
        }

        function resolveControlLabel(control, form) {
            if (!(form instanceof HTMLFormElement)) {
                return humanizeFieldName(control.name || control.id || '');
            }

            const parentLabel = control.closest('label');
            const inlineLabel = cleanLabelText(parentLabel);
            if (inlineLabel !== '') {
                return inlineLabel;
            }

            const controlId = String(control.id || '').trim();
            if (controlId !== '') {
                const quotedId = controlId.replace(/"/g, '\\"');
                const linkedLabel = form.querySelector('label[for="' + quotedId + '"]');
                const linkedLabelText = cleanLabelText(linkedLabel);
                if (linkedLabelText !== '') {
                    return linkedLabelText;
                }
            }

            return humanizeFieldName(control.name || control.id || '');
        }

        function shouldSkipDetailControl(control) {
            if (control.disabled) {
                return true;
            }

            const fieldName = normalizeText(control.name || control.id || '');
            if (fieldName === 'csrf' || fieldName === '_csrf' || fieldName === '_token' || fieldName === 'csrf_token' || fieldName === 'id') {
                return true;
            }

            if (fieldName.includes('password') || fieldName.includes('mat khau') || fieldName.includes('mat_khau')) {
                return true;
            }

            if (control instanceof HTMLInputElement) {
                const type = normalizeText(control.type || 'text');
                return type === 'hidden'
                    || type === 'submit'
                    || type === 'button'
                    || type === 'reset'
                    || type === 'image'
                    || type === 'password'
                    || type === 'file';
            }

            return false;
        }

        function readControlDisplayValue(control, seenRadioGroups) {
            if (control instanceof HTMLSelectElement) {
                const selectedOptions = Array.from(control.selectedOptions || []);
                const labels = selectedOptions.map(function (option) {
                    return String(option.textContent || option.value || '').trim();
                }).filter(function (value) {
                    return value !== '';
                });
                return labels.join(', ');
            }

            if (control instanceof HTMLTextAreaElement) {
                return String(control.value || '').trim();
            }

            if (control instanceof HTMLInputElement) {
                const inputType = normalizeText(control.type || 'text');
                if (inputType === 'checkbox') {
                    return control.checked ? 'Có' : 'Không';
                }

                if (inputType === 'radio') {
                    const groupName = String(control.name || control.id || '').trim();
                    if (!control.checked || groupName === '' || seenRadioGroups.has(groupName)) {
                        return null;
                    }

                    seenRadioGroups.add(groupName);
                    return String(control.value || '').trim();
                }

                return String(control.value || '').trim();
            }

            return '';
        }

        function extractFormDetailPairs(form) {
            if (!(form instanceof HTMLFormElement)) {
                return [];
            }

            const controls = Array.from(form.querySelectorAll('input, select, textarea'));
            const pairs = [];
            const seenKeys = new Set();
            const seenRadioGroups = new Set();

            controls.forEach(function (control) {
                if (shouldSkipDetailControl(control)) {
                    return;
                }

                const key = normalizeText((control.name || control.id || '').replace(/\[\]$/, ''));
                if (key !== '' && seenKeys.has(key)) {
                    return;
                }

                const label = resolveControlLabel(control, form);
                if (normalizeText(label).includes('mat khau') || normalizeText(label).includes('password')) {
                    return;
                }

                const rawValue = readControlDisplayValue(control, seenRadioGroups);
                if (rawValue === null) {
                    return;
                }

                const value = String(rawValue || '').trim();
                pairs.push({
                    label: label,
                    value: value === '' ? 'Chưa cập nhật' : value,
                    format: control instanceof HTMLTextAreaElement && control.dataset.bbcodeField === '1' ? 'bbcode' : 'text',
                });

                if (key !== '') {
                    seenKeys.add(key);
                }
            });

            return pairs;
        }

        function mergeDetailPairs(primaryPairs, fallbackPairs) {
            const merged = [];
            const seenLabels = new Set();

            function pushPair(pair) {
                if (!pair || typeof pair !== 'object') {
                    return;
                }

                const label = String(pair.label || '').trim();
                if (label === '') {
                    return;
                }

                const normalizedLabel = normalizeText(label);
                if (seenLabels.has(normalizedLabel)) {
                    return;
                }

                seenLabels.add(normalizedLabel);
                merged.push({
                    label: label,
                    value: String(pair.value || '').trim() || 'Chưa cập nhật',
                });
            }

            primaryPairs.forEach(pushPair);
            fallbackPairs.forEach(pushPair);
            return merged;
        }

        function buildRecordDetailView(pairs) {
            const wrapper = document.createElement('div');
            wrapper.className = 'admin-ui';

            const helper = document.createElement('p');
            helper.className = 'admin-modal-helper';
            helper.textContent = 'Thông tin chi tiết đầy đủ của bản ghi đã chọn.';
            wrapper.appendChild(helper);

            if (!Array.isArray(pairs) || pairs.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'admin-record-detail-empty';
                empty.textContent = 'Không có dữ liệu chi tiết để hiển thị.';
                wrapper.appendChild(empty);
                return wrapper;
            }

            const detailForm = document.createElement('form');
            detailForm.className = 'grid gap-3 md:grid-cols-2';
            detailForm.setAttribute('data-readonly-form', '1');

            pairs.forEach(function (pair) {
                const fieldLabel = document.createElement('label');

                const labelText = document.createElement('span');
                labelText.textContent = String(pair.label || 'Thông tin');
                fieldLabel.appendChild(labelText);

                const value = String(pair.value || 'Chưa cập nhật');
                if (pair.format === 'bbcode') {
                    const content = document.createElement('div');
                    content.className = 'bbcode-rendered-box bbcode-content';
                    content.innerHTML = renderBbcodeHtml(value);
                    if (content.innerHTML.trim() === '') {
                        content.textContent = 'Chưa cập nhật';
                    }
                    fieldLabel.appendChild(content);
                    detailForm.appendChild(fieldLabel);
                    return;
                }

                const useTextarea = value.length > 120 || value.includes('\n');
                if (useTextarea) {
                    const textarea = document.createElement('textarea');
                    const lineCount = value.split('\n').length;
                    textarea.value = value;
                    textarea.readOnly = true;
                    textarea.rows = Math.max(3, Math.min(8, lineCount));
                    fieldLabel.appendChild(textarea);
                } else {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = value;
                    input.readOnly = true;
                    fieldLabel.appendChild(input);
                }

                detailForm.appendChild(fieldLabel);
            });

            wrapper.appendChild(detailForm);
            return wrapper;
        }

        function ensureRowActionContainer(actionCell) {
            let container = actionCell.querySelector('span.inline-flex, div.inline-flex');
            if (container instanceof HTMLElement) {
                return container;
            }

            container = document.createElement('span');
            container.className = 'inline-flex flex-wrap items-center gap-2';
            while (actionCell.firstChild) {
                container.appendChild(actionCell.firstChild);
            }
            actionCell.appendChild(container);
            return container;
        }

        function appendRowDetailButton(row, actionColumnIndex, detailUrl) {
            if (!(row instanceof HTMLTableRowElement)) {
                return false;
            }

            const cells = Array.from(row.querySelectorAll('td'));
            const fallbackIndex = cells.length > 0 ? cells.length - 1 : -1;
            const targetIndex = actionColumnIndex >= 0 ? actionColumnIndex : fallbackIndex;
            const actionCell = targetIndex >= 0 ? cells[targetIndex] : null;
            if (!(actionCell instanceof HTMLTableCellElement)) {
                return false;
            }

            const existing = actionCell.querySelector('button[' + ROW_DETAIL_TRIGGER_ATTR + '="1"]');
            if (existing) {
                return false;
            }

            const container = ensureRowActionContainer(actionCell);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'admin-row-detail-button admin-action-icon-btn';
            button.dataset.actionKind = 'detail';
            button.setAttribute(ROW_DETAIL_TRIGGER_ATTR, '1');
            button.innerHTML = [
                '<span class="admin-action-icon-label">Xem chi tiết</span>',
                '<span class="admin-action-icon-glyph" aria-hidden="true">',
                '<svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6"></path><circle cx="12" cy="12" r="3"></circle></svg>',
                '</span>'
            ].join('');
            button.title = 'Xem chi tiết';
            button.setAttribute('aria-label', 'Xem chi tiết');
            button.dataset.skipActionIcon = '1';

            if (detailUrl instanceof URL) {
                button.dataset.detailUrl = detailUrl.toString();
            }

            container.insertBefore(button, container.firstChild);
            return true;
        }

        function findRowEditAnchor(row) {
            if (!(row instanceof HTMLTableRowElement)) {
                return null;
            }

            const anchors = Array.from(row.querySelectorAll('a[href]'));
            for (const anchor of anchors) {
                if (!(anchor instanceof HTMLAnchorElement)) {
                    continue;
                }

                const url = toUrl(anchor.getAttribute('href') || '');
                if (url && isLikelyEditUrl(url)) {
                    return {
                        anchor: anchor,
                        url: url,
                    };
                }
            }

            return null;
        }

        function openDetailModalFromPairs(pairs) {
            const modal = ensureEditModal();
            const body = modal.querySelector('.admin-edit-modal-body');
            if (!(body instanceof HTMLElement)) {
                return;
            }

            setEditModalTitle(modal, 'Chi tiết bản ghi');
            body.innerHTML = '';
            body.appendChild(buildRecordDetailView(pairs));
            modal.classList.remove('hidden');
            document.body.classList.add('admin-modal-open');
        }

        async function openDetailModalForUrl(url, row, headerTitles, actionColumnIndex) {
            const modal = ensureEditModal();
            const body = modal.querySelector('.admin-edit-modal-body');
            if (!(body instanceof HTMLElement)) {
                return;
            }

            setEditModalTitle(modal, 'Chi tiết bản ghi');
            body.innerHTML = '<div class="admin-edit-modal-loading">Đang tải dữ liệu chi tiết...</div>';
            modal.classList.remove('hidden');
            document.body.classList.add('admin-modal-open');

            const fallbackPairs = extractRowDetailPairs(row, headerTitles, actionColumnIndex);

            try {
                const sourceForm = await fetchEditSaveForm(url, 'Không thể tải dữ liệu chi tiết.');
                if (sourceForm instanceof HTMLFormElement) {
                    const readonlyWrapper = buildReadonlyModalForm(sourceForm);
                    if (readonlyWrapper instanceof HTMLElement) {
                        body.innerHTML = '';
                        body.appendChild(readonlyWrapper);
                        return;
                    }
                }

                const formPairs = sourceForm ? extractFormDetailPairs(sourceForm) : [];
                const mergedPairs = mergeDetailPairs(formPairs, fallbackPairs);
                body.innerHTML = '';
                body.appendChild(buildRecordDetailView(mergedPairs));
            } catch (error) {
                if (fallbackPairs.length > 0) {
                    body.innerHTML = '';
                    body.appendChild(buildRecordDetailView(fallbackPairs));
                    return;
                }

                const message = error instanceof Error ? error.message : 'Không thể mở chi tiết bản ghi.';
                body.innerHTML = '<div class="admin-edit-modal-error">' + message + '</div>';
            }
        }

        function splitLabelTokens(value) {
            return normalizeText(value)
                .split(/[^a-z0-9]+/)
                .map(function (part) {
                    return part.trim();
                })
                .filter(function (part) {
                    return part.length > 1;
                });
        }

        function labelsLookSimilar(left, right) {
            const normalizedLeft = normalizeText(left);
            const normalizedRight = normalizeText(right);
            if (normalizedLeft === '' || normalizedRight === '') {
                return false;
            }

            if (normalizedLeft === normalizedRight || normalizedLeft.includes(normalizedRight) || normalizedRight.includes(normalizedLeft)) {
                return true;
            }

            const leftTokens = splitLabelTokens(normalizedLeft);
            const rightTokens = splitLabelTokens(normalizedRight);
            if (leftTokens.length === 0 || rightTokens.length === 0) {
                return false;
            }

            const rightSet = new Set(rightTokens);
            let shared = 0;
            leftTokens.forEach(function (token) {
                if (rightSet.has(token)) {
                    shared += 1;
                }
            });

            const minLength = Math.min(leftTokens.length, rightTokens.length);
            return minLength > 0 && shared / minLength >= 0.8;
        }

        function isTableLikelyComplete(headerTitles, actionColumnIndex, formPairs) {
            const contentHeaders = headerTitles.filter(function (_, index) {
                return index !== actionColumnIndex;
            }).map(function (title) {
                return String(title || '').trim();
            }).filter(function (title) {
                return title !== '';
            });

            const comparableFields = formPairs.map(function (pair) {
                return String(pair.label || '').trim();
            }).filter(function (label) {
                const normalized = normalizeText(label);
                return normalized !== ''
                    && !normalized.includes('mat khau')
                    && !normalized.includes('password');
            });

            if (contentHeaders.length === 0 || comparableFields.length === 0) {
                return false;
            }

            let matchedCount = 0;
            comparableFields.forEach(function (fieldLabel) {
                const matched = contentHeaders.some(function (headerLabel) {
                    return labelsLookSimilar(fieldLabel, headerLabel);
                });

                if (matched) {
                    matchedCount += 1;
                }
            });

            const coverage = matchedCount / comparableFields.length;
            return comparableFields.length <= contentHeaders.length && coverage >= 0.85;
        }

        async function initGlobalRowDetails() {
            const tables = document.querySelectorAll('.admin-ui .overflow-x-auto > table');
            for (const table of tables) {
                if (table.dataset.globalRowDetailReady === '1') {
                    continue;
                }

                if (table.dataset.disableRowDetail === '1') {
                    table.dataset.globalRowDetailReady = '1';
                    continue;
                }

                const headerTitles = getTableHeaderTitles(table);
                if (headerTitles.length === 0) {
                    continue;
                }

                const actionColumnIndex = findActionColumnIndex(headerTitles, table);
                if (actionColumnIndex < 0) {
                    continue;
                }

                const rows = Array.from(table.querySelectorAll('tbody tr'));
                const dataRows = rows.filter(function (row) {
                    return row instanceof HTMLTableRowElement && !row.querySelector('td[colspan]');
                });
                if (dataRows.length === 0) {
                    continue;
                }

                let anyInserted = false;
                const enableRowDetail = table.dataset.enableRowDetail === '1';
                const forceRowDetail = table.dataset.forceRowDetail === '1';
                const editableRows = [];

                dataRows.forEach(function (row) {
                    const editAnchor = findRowEditAnchor(row);
                    if (editAnchor && editAnchor.url instanceof URL) {
                        editableRows.push({
                            row: row,
                            url: editAnchor.url,
                        });
                        return;
                    }

                    if (enableRowDetail) {
                        anyInserted = appendRowDetailButton(row, actionColumnIndex, null) || anyInserted;
                    }
                });

                if (editableRows.length > 0) {
                    let shouldInjectForEditRows = true;
                    if (!forceRowDetail) {
                        try {
                            const sampleForm = await fetchEditSaveForm(editableRows[0].url, 'Không thể tải biểu mẫu mẫu để phân tích bảng.');
                            if (sampleForm instanceof HTMLFormElement) {
                                const samplePairs = extractFormDetailPairs(sampleForm);
                                shouldInjectForEditRows = !isTableLikelyComplete(headerTitles, actionColumnIndex, samplePairs);
                            }
                        } catch (error) {
                            shouldInjectForEditRows = true;
                        }
                    }

                    if (shouldInjectForEditRows) {
                        editableRows.forEach(function (entry) {
                            anyInserted = appendRowDetailButton(entry.row, actionColumnIndex, entry.url) || anyInserted;
                        });
                    }
                }

                if (anyInserted) {
                    table.dataset.globalRowDetailReady = '1';
                }
            }
        }

        function bindGlobalRowDetailButtons() {
            document.addEventListener('click', function (event) {
                const selector = 'button[' + ROW_DETAIL_TRIGGER_ATTR + '="1"]';
                const target = event.target instanceof Element ? event.target.closest(selector) : null;
                if (!(target instanceof HTMLButtonElement)) {
                    return;
                }

                event.preventDefault();

                const row = target.closest('tr');
                const table = target.closest('table');
                if (!(row instanceof HTMLTableRowElement) || !(table instanceof HTMLTableElement)) {
                    return;
                }

                const headerTitles = getTableHeaderTitles(table);
                const actionColumnIndex = findActionColumnIndex(headerTitles, table);
                const detailUrl = toUrl(target.dataset.detailUrl || '');
                if (detailUrl) {
                    openDetailModalForUrl(detailUrl, row, headerTitles, actionColumnIndex);
                    return;
                }

                const pairs = extractRowDetailPairs(row, headerTitles, actionColumnIndex);
                openDetailModalFromPairs(pairs);
            });
        }

        function resolveActionLabel(element) {
            if (element instanceof HTMLInputElement) {
                return String(element.value || '').trim();
            }

            return String(element.textContent || '').replace(/\s+/g, ' ').trim();
        }

        function resolveActionKind(labelText) {
            const normalized = normalizeText(labelText);
            if (normalized === '') {
                return '';
            }

            if (normalized.includes('xem chi tiet')) {
                return 'detail';
            }

            if (normalized.includes('gui duyet') || normalized.includes('yeu cau')) {
                return 'request';
            }

            if (normalized.includes('sua') || normalized.includes('chinh sua') || normalized.includes('cap nhat')) {
                return 'edit';
            }

            if (normalized.includes('khoa')) {
                return 'lock';
            }

            if (normalized.includes('xoa')) {
                return 'delete';
            }

            if (normalized.includes('luu')) {
                return 'save';
            }

            if (normalized.includes('duyet')) {
                return 'request';
            }

            return '';
        }

        function iconMarkupByActionKind(kind) {
            const map = {
                detail: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6"></path><circle cx="12" cy="12" r="3"></circle></svg>',
                edit: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>',
                delete: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>',
                lock: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path></svg>',
                save: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m20 7-11 11-5-5"></path></svg>',
                request: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 12 18-9-4 18-5-6-9-3Z"></path><path d="m12 15 9-12"></path></svg>',
            };

            return map[kind] || '';
        }

        function toIconButtonElement(element) {
            if (!(element instanceof HTMLInputElement) || normalizeText(element.type || '') !== 'submit') {
                return element;
            }

            const replacement = document.createElement('button');
            replacement.type = 'submit';
            replacement.className = element.className;

            Array.from(element.attributes).forEach(function (attribute) {
                const key = normalizeText(attribute.name || '');
                if (key === 'type' || key === 'value' || key === 'class') {
                    return;
                }
                replacement.setAttribute(attribute.name, attribute.value);
            });

            element.replaceWith(replacement);
            return replacement;
        }

        function applyActionIcon(element, actionKind, labelText) {
            const target = toIconButtonElement(element);
            if (!(target instanceof HTMLAnchorElement) && !(target instanceof HTMLButtonElement)) {
                return;
            }

            if (target.dataset.actionIconReady === '1') {
                return;
            }

            const safeLabel = String(labelText || '').trim() || 'Thao tác';
            const markup = iconMarkupByActionKind(actionKind);
            if (markup === '') {
                return;
            }

            target.classList.add('admin-action-icon-btn');
            target.setAttribute('data-action-kind', actionKind);
            target.setAttribute('title', safeLabel);
            target.setAttribute('aria-label', safeLabel);
            target.setAttribute('data-action-label', safeLabel);
            target.innerHTML = '<span class="admin-action-icon-label">' + safeLabel + '</span><span class="admin-action-icon-glyph" aria-hidden="true">' + markup + '</span>';
            target.dataset.actionIconReady = '1';
        }

        function initActionIcons() {
            const tables = document.querySelectorAll('.admin-ui .overflow-x-auto > table');
            tables.forEach(function (table) {
                const headerTitles = getTableHeaderTitles(table);
                if (headerTitles.length === 0) {
                    return;
                }

                const actionColumnIndex = findActionColumnIndex(headerTitles, table);
                if (actionColumnIndex < 0) {
                    return;
                }

                const rows = Array.from(table.querySelectorAll('tbody tr'));
                rows.forEach(function (row) {
                    if (!(row instanceof HTMLTableRowElement)) {
                        return;
                    }

                    const cells = Array.from(row.querySelectorAll('td'));
                    const fallbackIndex = cells.length > 0 ? cells.length - 1 : -1;
                    const targetIndex = actionColumnIndex >= 0 && actionColumnIndex < cells.length ? actionColumnIndex : fallbackIndex;
                    const actionCell = targetIndex >= 0 ? cells[targetIndex] : null;
                    if (!(actionCell instanceof HTMLTableCellElement)) {
                        return;
                    }

                    const actionElements = Array.from(actionCell.querySelectorAll('a, button, input[type="submit"]'));
                    actionElements.forEach(function (element) {
                        if (!(element instanceof HTMLElement)) {
                            return;
                        }

                        if (element.closest('[data-skip-action-icon="1"]')) {
                            return;
                        }

                        const labelText = resolveActionLabel(element);
                        const actionKind = resolveActionKind(labelText);
                        if (actionKind === '') {
                            return;
                        }

                        applyActionIcon(element, actionKind, labelText);
                    });
                });
            });
        }

        function resolveSearchableColumns(headerTitles) {
            const blockedNames = ['thao tac', 'hanh dong', 'action'];
            const columns = [];

            headerTitles.forEach(function (title, index) {
                const displayTitle = String(title || ('Cột ' + (index + 1))).trim();
                const normalizedTitle = normalizeText(displayTitle);
                if (normalizedTitle === '' || blockedNames.indexOf(normalizedTitle) !== -1) {
                    return;
                }
                columns.push({
                    index: index,
                    title: displayTitle,
                });
            });

            if (columns.length === 0) {
                headerTitles.forEach(function (title, index) {
                    columns.push({
                        index: index,
                        title: String(title || ('Cột ' + (index + 1))).trim(),
                    });
                });
            }

            return columns;
        }

        function createToolbar(table, dataRows, headerTitles) {
            const wrapper = table.closest('.overflow-x-auto') || table.parentElement;
            if (!wrapper || !wrapper.parentNode) {
                return;
            }

            const searchableColumns = resolveSearchableColumns(headerTitles);
            const rowCache = dataRows.map(function (row) {
                const cells = Array.from(row.querySelectorAll('td'));
                const values = headerTitles.map(function (_, index) {
                    return extractCellText(cells[index] || null);
                });
                const normalizedValues = values.map(function (value) {
                    return normalizeText(value);
                });

                return {
                    row: row,
                    values: values,
                    normalizedValues: normalizedValues,
                };
            });

            const toolbar = document.createElement('div');
            toolbar.className = 'table-filter-bar';

            const controls = document.createElement('div');
            controls.className = 'table-filter-controls';

            const searchInput = document.createElement('input');
            searchInput.type = 'search';
            searchInput.placeholder = 'Tìm kiếm trong bảng...';
            searchInput.setAttribute('aria-label', 'Tìm kiếm bảng dữ liệu');

            const columnSelect = document.createElement('select');
            columnSelect.setAttribute('aria-label', 'Lọc theo cột');

            const allOption = document.createElement('option');
            allOption.value = '-1';
            allOption.textContent = 'Tất cả cột';
            columnSelect.appendChild(allOption);

            searchableColumns.forEach(function (column) {
                const option = document.createElement('option');
                option.value = String(column.index);
                option.textContent = column.title;
                columnSelect.appendChild(option);
            });

            const valueSelect = document.createElement('select');
            valueSelect.setAttribute('aria-label', 'Lọc theo giá trị cột');
            valueSelect.disabled = true;

            const clearButton = document.createElement('button');
            clearButton.type = 'button';
            clearButton.textContent = 'Xóa lọc';

            controls.appendChild(searchInput);
            controls.appendChild(columnSelect);
            controls.appendChild(valueSelect);
            controls.appendChild(clearButton);

            const counter = document.createElement('span');
            counter.className = 'table-filter-counter';

            toolbar.appendChild(controls);
            toolbar.appendChild(counter);
            wrapper.parentNode.insertBefore(toolbar, wrapper);

            const emptyRow = document.createElement('tr');
            emptyRow.setAttribute('data-global-filter-empty', '1');
            emptyRow.style.display = 'none';
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = Math.max(1, headerTitles.length);
            emptyCell.innerHTML = '<div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-500">Không tìm thấy dữ liệu phù hợp.</div>';
            emptyRow.appendChild(emptyCell);
            table.tBodies[0].appendChild(emptyRow);

            function updateValueOptions() {
                const selectedColumn = Number(columnSelect.value);
                valueSelect.innerHTML = '';

                const anyOption = document.createElement('option');
                anyOption.value = '';
                anyOption.textContent = 'Tất cả giá trị';
                valueSelect.appendChild(anyOption);

                if (selectedColumn < 0) {
                    valueSelect.disabled = true;
                    return;
                }

                const uniqueValues = new Map();
                rowCache.forEach(function (entry) {
                    const displayValue = String(entry.values[selectedColumn] || '').trim();
                    const normalizedValue = String(entry.normalizedValues[selectedColumn] || '').trim();
                    if (normalizedValue !== '' && !uniqueValues.has(normalizedValue)) {
                        uniqueValues.set(normalizedValue, displayValue);
                    }
                });

                Array.from(uniqueValues.entries())
                    .sort(function (a, b) {
                        return a[1].localeCompare(b[1], 'vi');
                    })
                    .forEach(function (pair) {
                        const option = document.createElement('option');
                        option.value = pair[0];
                        option.textContent = pair[1];
                        valueSelect.appendChild(option);
                    });

                valueSelect.disabled = false;
            }

            function applyFilter() {
                const query = normalizeText(searchInput.value);
                const selectedColumn = Number(columnSelect.value);
                const selectedValue = normalizeText(valueSelect.value);
                let visibleCount = 0;

                rowCache.forEach(function (entry) {
                    const haystack = selectedColumn >= 0
                        ? String(entry.normalizedValues[selectedColumn] || '')
                        : normalizeText(searchableColumns.map(function (column) {
                            return entry.normalizedValues[column.index] || '';
                        }).join(' '));

                    const matchedQuery = query === '' || haystack.indexOf(query) !== -1;
                    const matchedValue = selectedColumn < 0 || selectedValue === ''
                        ? true
                        : String(entry.normalizedValues[selectedColumn] || '') === selectedValue;
                    const matched = matchedQuery && matchedValue;

                    entry.row.style.display = matched ? '' : 'none';
                    if (matched) {
                        visibleCount += 1;
                    }
                });

                emptyRow.style.display = visibleCount === 0 ? '' : 'none';
                counter.textContent = 'Hiển thị ' + visibleCount + '/' + rowCache.length + ' dòng';
            }

            clearButton.addEventListener('click', function () {
                searchInput.value = '';
                columnSelect.value = '-1';
                updateValueOptions();
                applyFilter();
                searchInput.focus();
            });

            searchInput.addEventListener('input', applyFilter);
            columnSelect.addEventListener('change', function () {
                updateValueOptions();
                applyFilter();
            });
            valueSelect.addEventListener('change', applyFilter);

            updateValueOptions();
            applyFilter();
        }

        function initTableFilters() {
            const tables = document.querySelectorAll('.admin-ui .overflow-x-auto > table');
            tables.forEach(function (table) {
                if (table.dataset.globalFilterReady === '1') {
                    return;
                }

                if (table.dataset.disableGlobalFilter === '1') {
                    return;
                }

                const headCells = Array.from(table.querySelectorAll('thead th'));
                const headerTitles = headCells.map(function (cell) {
                    return String(cell.textContent || '').trim();
                });
                if (headerTitles.length === 0) {
                    return;
                }

                const rows = Array.from(table.querySelectorAll('tbody tr'));
                const dataRows = rows.filter(function (row) {
                    return !row.querySelector('td[colspan]');
                });
                if (dataRows.length === 0) {
                    return;
                }

                table.dataset.globalFilterReady = '1';
                createToolbar(table, dataRows, headerTitles);
            });
        }

        function initGlobalTomSelect(rootElement) {
            const selects = (rootElement || document).querySelectorAll('.admin-ui form select, .admin-edit-modal-dialog form select');
            selects.forEach(function (select) {
                if (select.name && select.name.endsWith('per_page')) {
                    return;
                }
                if (select.tomselect || select.dataset.noSearch === '1' || select.readOnly || select.classList.contains('no-search')) {
                    return;
                }
                new TomSelect(select, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            });
        }

        function initPhoneInputs(rootElement) {
            const inputs = (rootElement || document).querySelectorAll('input[type="tel"], input[name*="phone"]');
            inputs.forEach(function (input) {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }
                if (input.dataset.phoneSanitized === '1') {
                    return;
                }

                const sanitizePhoneValue = function () {
                    input.value = String(input.value || '').replace(/\D+/g, '');
                };

                input.dataset.phoneSanitized = '1';
                input.setAttribute('inputmode', 'numeric');
                input.setAttribute('pattern', '[0-9]*');
                input.addEventListener('input', sanitizePhoneValue);
                input.addEventListener('paste', function () {
                    requestAnimationFrame(sanitizePhoneValue);
                });
            });
        }

        async function refreshAdminUi(rootElement) {
            const scope = rootElement instanceof HTMLElement ? rootElement : document;

            clearCreateSaveForms();

            scope.querySelectorAll('table').forEach(function (table) {
                if (table instanceof HTMLTableElement) {
                    delete table.dataset.globalRowDetailReady;
                    delete table.dataset.globalFilterReady;

                    const wrapper = table.closest('.overflow-x-auto');
                    const toolbar = wrapper instanceof HTMLElement ? wrapper.previousElementSibling : null;
                    if (toolbar instanceof HTMLElement && toolbar.classList.contains('table-filter-bar')) {
                        toolbar.remove();
                    }
                }
            });

            try {
                await initGlobalRowDetails();
            } catch (error) {
                // Keep refresh resilient even when detail bootstrap fails.
            }

            initActionIcons();
            initTableFilters();
            initGlobalTomSelect(scope);
            initPhoneInputs(scope);
        }

        function getAjaxTableRoot(element) {
            return element instanceof Element ? element.closest(AJAX_TABLE_ROOT_SELECTOR) : null;
        }

        function getAjaxTableConfig(root) {
            if (!(root instanceof HTMLElement)) {
                return null;
            }

            return {
                pageKey: String(root.dataset.ajaxPageKey || 'page').trim() || 'page',
                pageValue: String(root.dataset.ajaxPageValue || '').trim(),
                pageParam: String(root.dataset.ajaxPageParam || '').trim(),
                searchParam: String(root.dataset.ajaxSearchParam || 'search').trim() || 'search',
            };
        }

        function getAjaxTableSearchInput(root) {
            return root instanceof HTMLElement ? root.querySelector(AJAX_SEARCH_SELECTOR) : null;
        }

        function getAjaxTableFilters(root) {
            if (!(root instanceof HTMLElement)) {
                return [];
            }

            return Array.from(root.querySelectorAll(AJAX_FILTER_SELECTOR)).filter(function (element) {
                return element instanceof HTMLInputElement || element instanceof HTMLSelectElement || element instanceof HTMLTextAreaElement;
            });
        }

        function rootMatchesAjaxUrl(root, url) {
            const config = getAjaxTableConfig(root);
            if (!config || !(url instanceof URL) || url.origin !== window.location.origin) {
                return false;
            }

            if (config.pageValue === '') {
                return true;
            }

            return String(url.searchParams.get(config.pageKey) || '') === config.pageValue;
        }

        function buildAjaxTableUrlFromForm(root, form) {
            const config = getAjaxTableConfig(root);
            if (!config || !(form instanceof HTMLFormElement)) {
                return null;
            }

            const url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
            const formData = new FormData(form);
            url.search = '';

            formData.forEach(function (value, key) {
                url.searchParams.set(String(key), String(value));
            });

            if (config.pageValue !== '') {
                url.searchParams.set(config.pageKey, config.pageValue);
            }

            const searchInput = getAjaxTableSearchInput(root);
            const keyword = searchInput instanceof HTMLInputElement ? String(searchInput.value || '').trim() : '';
            if (keyword === '') {
                url.searchParams.delete(config.searchParam);
            } else {
                url.searchParams.set(config.searchParam, keyword);
            }

            getAjaxTableFilters(root).forEach(function (filterElement) {
                const filterName = String(filterElement.name || '').trim();
                if (filterName === '') {
                    return;
                }

                const filterValue = String(filterElement.value || '').trim();
                if (filterValue === '') {
                    url.searchParams.delete(filterName);
                } else {
                    url.searchParams.set(filterName, filterValue);
                }
            });

            if (config.pageParam !== '') {
                url.searchParams.set(config.pageParam, '1');
            }

            return url;
        }

        function findMatchingAjaxRoot(doc, currentRoot) {
            if (!(doc instanceof Document) || !(currentRoot instanceof HTMLElement)) {
                return null;
            }

            const config = getAjaxTableConfig(currentRoot);
            if (!config) {
                return null;
            }

            const candidates = Array.from(doc.querySelectorAll(AJAX_TABLE_ROOT_SELECTOR));
            for (const candidate of candidates) {
                if (!(candidate instanceof HTMLElement)) {
                    continue;
                }

                const candidateConfig = getAjaxTableConfig(candidate);
                if (!candidateConfig) {
                    continue;
                }

                if (candidateConfig.pageKey === config.pageKey && candidateConfig.pageValue === config.pageValue) {
                    return candidate;
                }
            }

            return candidates[0] instanceof HTMLElement ? candidates[0] : null;
        }

        function syncAjaxTableSearchInput(root, url) {
            const config = getAjaxTableConfig(root);
            const input = getAjaxTableSearchInput(root);
            if (!config || !(input instanceof HTMLInputElement) || !(url instanceof URL)) {
                return;
            }

            input.value = String(url.searchParams.get(config.searchParam) || '');
        }

        function syncAjaxTableFilters(root, url) {
            if (!(root instanceof HTMLElement) || !(url instanceof URL)) {
                return;
            }

            getAjaxTableFilters(root).forEach(function (filterElement) {
                const filterName = String(filterElement.name || '').trim();
                if (filterName === '') {
                    return;
                }

                filterElement.value = String(url.searchParams.get(filterName) || '');
            });
        }

        async function fetchAjaxTable(root, url, historyMode) {
            const config = getAjaxTableConfig(root);
            const currentTbody = root instanceof HTMLElement ? root.querySelector(AJAX_TBODY_SELECTOR) : null;
            const currentPagination = root instanceof HTMLElement ? root.querySelector(AJAX_PAGINATION_SELECTOR) : null;
            const currentRowInfo = root instanceof HTMLElement ? root.querySelector(AJAX_ROW_INFO_SELECTOR) : null;
            if (
                !config
                || !(root instanceof HTMLElement)
                || !(currentTbody instanceof HTMLTableSectionElement)
                || !(currentPagination instanceof HTMLElement)
                || !rootMatchesAjaxUrl(root, url)
            ) {
                window.location.href = url.toString();
                return;
            }

            const previousController = ajaxTableControllers.get(root);
            if (previousController instanceof AbortController) {
                previousController.abort();
            }

            const controller = new AbortController();
            ajaxTableControllers.set(root, controller);
            currentTbody.classList.add('opacity-60');
            currentPagination.classList.add('opacity-60', 'pointer-events-none');

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: controller.signal
                });

                if (!response.ok) {
                    throw new Error('Không thể tải dữ liệu bảng.');
                }

                const html = await response.text();
                const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                const nextRoot = findMatchingAjaxRoot(nextDocument, root);
                const nextTbody = nextRoot instanceof HTMLElement ? nextRoot.querySelector(AJAX_TBODY_SELECTOR) : null;
                const nextPagination = nextRoot instanceof HTMLElement ? nextRoot.querySelector(AJAX_PAGINATION_SELECTOR) : null;
                const nextRowInfo = nextRoot instanceof HTMLElement ? nextRoot.querySelector(AJAX_ROW_INFO_SELECTOR) : null;
                const currentRowInfo = root.querySelector(AJAX_ROW_INFO_SELECTOR);

                if (!(nextTbody instanceof HTMLTableSectionElement) || !(nextPagination instanceof HTMLElement)) {
                    throw new Error('Không tìm thấy vùng dữ liệu mới.');
                }

                currentTbody.replaceWith(nextTbody);
                currentPagination.replaceWith(nextPagination);

                const rowInfoLivesInsidePagination = currentRowInfo instanceof HTMLElement && currentPagination.contains(currentRowInfo);
                if (!rowInfoLivesInsidePagination && currentRowInfo instanceof HTMLElement && nextRowInfo instanceof HTMLElement) {
                    currentRowInfo.replaceWith(nextRowInfo);
                }

                if (historyMode === 'push') {
                    window.history.pushState({ ajaxTable: true }, '', url.toString());
                } else if (historyMode === 'replace') {
                    window.history.replaceState({ ajaxTable: true }, '', url.toString());
                }

                await refreshAdminUi(root);
            } catch (error) {
                if (error instanceof DOMException && error.name === 'AbortError') {
                    return;
                }

                window.location.href = url.toString();
            } finally {
                if (ajaxTableControllers.get(root) === controller) {
                    ajaxTableControllers.delete(root);
                }

                const activeTbody = root.querySelector(AJAX_TBODY_SELECTOR);
                const activePagination = root.querySelector(AJAX_PAGINATION_SELECTOR);
                if (activeTbody instanceof HTMLElement) {
                    activeTbody.classList.remove('opacity-60');
                }
                if (activePagination instanceof HTMLElement) {
                    activePagination.classList.remove('opacity-60', 'pointer-events-none');
                }
            }
        }

        function initGlobalAjaxTables() {
            if (window.__globalAjaxTablesBound === '1') {
                return;
            }

            window.__globalAjaxTablesBound = '1';

            document.addEventListener('click', function (event) {
                const link = event.target instanceof Element ? event.target.closest(AJAX_PAGINATION_SELECTOR + ' a[href]') : null;
                if (!(link instanceof HTMLAnchorElement)) {
                    return;
                }

                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const root = getAjaxTableRoot(link);
                const url = new URL(link.href, window.location.href);
                if (!(root instanceof HTMLElement) || !rootMatchesAjaxUrl(root, url)) {
                    return;
                }

                event.preventDefault();
                fetchAjaxTable(root, url, 'push');
            });

            document.addEventListener('change', function (event) {
                const select = event.target;
                if (!(select instanceof HTMLSelectElement) || select.getAttribute('data-ajax-per-page') !== '1') {
                    return;
                }

                const form = select.form;
                const root = getAjaxTableRoot(select);
                const url = buildAjaxTableUrlFromForm(root, form);
                if (!(root instanceof HTMLElement) || !(url instanceof URL)) {
                    return;
                }

                event.preventDefault();
                fetchAjaxTable(root, url, 'push');
            });

            document.addEventListener('change', function (event) {
                const field = event.target;
                if (
                    !(field instanceof HTMLInputElement)
                    && !(field instanceof HTMLSelectElement)
                    && !(field instanceof HTMLTextAreaElement)
                ) {
                    return;
                }

                if (field.getAttribute('data-ajax-filter') !== '1') {
                    return;
                }

                const root = getAjaxTableRoot(field);
                const config = getAjaxTableConfig(root);
                if (!(root instanceof HTMLElement) || !config) {
                    return;
                }

                const url = new URL(window.location.href);
                if (config.pageValue !== '') {
                    url.searchParams.set(config.pageKey, config.pageValue);
                }

                const searchInput = getAjaxTableSearchInput(root);
                const keyword = searchInput instanceof HTMLInputElement ? String(searchInput.value || '').trim() : '';
                if (keyword === '') {
                    url.searchParams.delete(config.searchParam);
                } else {
                    url.searchParams.set(config.searchParam, keyword);
                }

                getAjaxTableFilters(root).forEach(function (filterElement) {
                    const filterName = String(filterElement.name || '').trim();
                    if (filterName === '') {
                        return;
                    }

                    const filterValue = String(filterElement.value || '').trim();
                    if (filterValue === '') {
                        url.searchParams.delete(filterName);
                    } else {
                        url.searchParams.set(filterName, filterValue);
                    }
                });

                if (config.pageParam !== '') {
                    url.searchParams.set(config.pageParam, '1');
                }

                fetchAjaxTable(root, url, 'push');
            });

            document.addEventListener('keydown', function (event) {
                const input = event.target;
                if (!(input instanceof HTMLInputElement) || input.getAttribute('data-ajax-search') !== '1') {
                    return;
                }

                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });

            document.addEventListener('input', function (event) {
                const input = event.target;
                if (!(input instanceof HTMLInputElement) || input.getAttribute('data-ajax-search') !== '1') {
                    return;
                }

                const root = getAjaxTableRoot(input);
                const config = getAjaxTableConfig(root);
                if (!(root instanceof HTMLElement) || !config) {
                    return;
                }

                const existingTimer = ajaxTableSearchTimers.get(root);
                if (existingTimer) {
                    window.clearTimeout(existingTimer);
                }

                const timer = window.setTimeout(function () {
                    const url = new URL(window.location.href);
                    if (config.pageValue !== '') {
                        url.searchParams.set(config.pageKey, config.pageValue);
                    }

                    getAjaxTableFilters(root).forEach(function (filterElement) {
                        const filterName = String(filterElement.name || '').trim();
                        if (filterName === '') {
                            return;
                        }

                        const filterValue = String(filterElement.value || '').trim();
                        if (filterValue === '') {
                            url.searchParams.delete(filterName);
                        } else {
                            url.searchParams.set(filterName, filterValue);
                        }
                    });

                    if (config.pageParam !== '') {
                        url.searchParams.set(config.pageParam, '1');
                    }

                    const keyword = String(input.value || '').trim();
                    if (keyword === '') {
                        url.searchParams.delete(config.searchParam);
                    } else {
                        url.searchParams.set(config.searchParam, keyword);
                    }

                    fetchAjaxTable(root, url, 'push');
                }, 500);

                ajaxTableSearchTimers.set(root, timer);
            });

            window.addEventListener('popstate', function () {
                const url = new URL(window.location.href);
                const roots = document.querySelectorAll(AJAX_TABLE_ROOT_SELECTOR);
                roots.forEach(function (root) {
                    if (!(root instanceof HTMLElement) || !rootMatchesAjaxUrl(root, url)) {
                        return;
                    }

                    syncAjaxTableSearchInput(root, url);
                    syncAjaxTableFilters(root, url);
                    fetchAjaxTable(root, url, null);
                });
            });
        }

        async function bootstrapAdminUi() {
            clearCreateSaveForms();
            initBbcodeEditors(document);
            document.addEventListener('submit', function (event) {
                if (event.target instanceof HTMLFormElement) {
                    syncBbcodeEditorsInForm(event.target);
                }
            }, true);
            document.addEventListener('reset', function (event) {
                if (!(event.target instanceof HTMLFormElement)) {
                    return;
                }

                window.setTimeout(function () {
                    event.target.querySelectorAll('textarea[data-bbcode-input="1"]').forEach(function (field) {
                        if (field instanceof HTMLTextAreaElement) {
                            syncBbcodeEditorFromTextarea(field);
                        }
                    });
                }, 0);
            }, true);
            try {
                await initGlobalRowDetails();
            } catch (error) {
                // Keep the rest of admin-ui features active even if detail bootstrap fails.
            }
            initActionIcons();
            initTableFilters();
            bindPaginationScrollSaver();
            restorePaginationScroll();
            bindGlobalRowDetailButtons();
            bindGlobalEditModal();
            renderStoredReloadFlash();
            initGlobalTomSelect(document);
            initPhoneInputs(document);
            initGlobalAjaxTables();
            window.__refreshAdminUi = refreshAdminUi;
            window.__openAdminEditModal = openEditModalFromValue;
            window.__closeAdminEditModal = closeEditModal;

            // Observe for dynamically added selects (e.g. inside modals)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node instanceof HTMLElement) {
                                if (node.tagName === 'SELECT') {
                                    initGlobalTomSelect(node.parentElement || node);
                                    initPhoneInputs(node.parentElement || node);
                                } else if (node.tagName === 'INPUT') {
                                    initPhoneInputs(node.parentElement || node);
                                } else if (node.querySelectorAll) {
                                    initBbcodeEditors(node);
                                    initGlobalTomSelect(node);
                                    initPhoneInputs(node);
                                }
                            }
                        });
                    }
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
            initAdminSidebarAjaxNavigation();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                bootstrapAdminUi();
            });
        } else {
            bootstrapAdminUi();
        }
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
</body>
</html>
