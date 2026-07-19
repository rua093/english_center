<?php
declare(strict_types=1);

require_role(['admin']);
require_permission('admin.dashboard.view');

$module = 'custom-ui';
$adminTitle = t('admin.custom_ui.title');
$adminDescription = t('admin.custom_ui.description');

$success = get_flash('success');
$error = get_flash('error');

$customUiItems = [];
foreach (custom_ui_media_catalog() as $mediaKey => $definition) {
    $currentUrl = custom_ui_media_current_url($mediaKey);
    $resolvedUrl = custom_ui_media_resolve_url($mediaKey);
    $customUiItems[] = [
        'key' => $mediaKey,
        'title' => t((string) ($definition['title_key'] ?? 'admin.custom_ui.untitled')),
        'description' => t((string) ($definition['description_key'] ?? 'admin.custom_ui.description')),
        'empty_text' => t((string) ($definition['empty_key'] ?? 'admin.custom_ui.empty_generic')),
        'live_text' => t((string) ($definition['live_key'] ?? 'admin.custom_ui.live_generic')),
        'page_url' => page_url((string) ($definition['page_slug'] ?? 'home')),
        'accept' => (string) ($definition['accept'] ?? 'image/*,video/*'),
        'preset' => (string) ($definition['preset'] ?? 'ui_banner_media'),
        'current_url' => $currentUrl,
        'resolved_url' => $resolvedUrl,
        'default_url' => custom_ui_media_default_url($mediaKey),
        'is_custom' => custom_ui_media_uses_custom_value($mediaKey),
        'is_video' => custom_ui_media_is_video($resolvedUrl),
        'media_kind' => (string) ($definition['media_kind'] ?? 'image_or_video'),
    ];
}
?>
<div class="grid gap-5">
    <?php if ($success): ?>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <section class="rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,#071a33_0%,#102b53_48%,#164072_100%)] p-6 text-white shadow-[0_30px_80px_rgba(15,23,42,0.18)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-cyan-100"><?= e(t('admin.custom_ui.badge')); ?></span>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-white md:text-4xl"><?= e(t('admin.custom_ui.hero_title')); ?></h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-100/90"><?= e(t('admin.custom_ui.hero_copy')); ?></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/15" href="<?= e(page_url('home')); ?>" target="_blank" rel="noreferrer"><?= e(t('admin.custom_ui.preview_home')); ?></a>
                <a class="inline-flex items-center rounded-full border border-white/15 bg-white px-4 py-2 text-sm font-black text-slate-900 transition hover:bg-cyan-200" href="<?= e(page_url('dashboard-admin')); ?>"><?= e(t('admin.custom_ui.back_dashboard')); ?></a>
            </div>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <?php foreach ($customUiItems as $item): ?>
            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-cyan-600"><?= e(t('admin.custom_ui.section_label')); ?></p>
                        <h3 class="mt-1 text-xl font-black text-slate-900"><?= e($item['title']); ?></h3>
                        <p class="mt-1 text-sm text-slate-500"><?= e($item['description']); ?></p>
                    </div>
                    <a class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-200" href="<?= e($item['page_url']); ?>" target="_blank" rel="noreferrer"><?= e(t('admin.custom_ui.preview_page')); ?></a>
                </div>

                <form class="mt-4 grid gap-4" method="post" action="/api/index.php?resource=settings&method=save-custom-ui-media" enctype="multipart/form-data" data-custom-ui-form="1">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="custom_ui_key" value="<?= e($item['key']); ?>">
                    <input type="hidden" name="custom_ui_media_url_hidden" value="<?= e($item['current_url']); ?>" data-custom-ui-hidden="1">

                    <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-950 shadow-sm" data-custom-ui-preview-wrap="1">
                        <?php if ($item['is_video']): ?>
                            <video class="max-h-80 w-full bg-black object-cover" controls playsinline preload="metadata" data-custom-ui-video="1">
                                <source src="<?= e($item['resolved_url']); ?>" data-custom-ui-video-source="1">
                            </video>
                            <img src="" alt="" class="hidden max-h-80 w-full object-cover" data-custom-ui-image="1">
                        <?php else: ?>
                            <video class="hidden max-h-80 w-full bg-black object-cover" controls playsinline preload="metadata" data-custom-ui-video="1">
                                <source src="" data-custom-ui-video-source="1">
                            </video>
                            <img src="<?= e($item['resolved_url']); ?>" alt="<?= e($item['title']); ?>" class="max-h-80 w-full object-cover" data-custom-ui-image="1">
                        <?php endif; ?>
                    </div>

                    <div class="rounded-[1.4rem] border <?= $item['is_custom'] ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600'; ?> px-4 py-3 text-sm font-semibold" data-custom-ui-status-box="1">
                        <?= e($item['is_custom'] ? $item['live_text'] : $item['empty_text']); ?>
                    </div>

                    <label class="group relative flex cursor-pointer flex-col items-center justify-center rounded-[1.5rem] border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition-all hover:border-cyan-400 hover:bg-cyan-50">
                        <input
                            type="file"
                            name="custom_ui_media_file"
                            accept="<?= e($item['accept']); ?>"
                            class="absolute inset-0 z-10 cursor-pointer opacity-0"
                            data-custom-ui-input="1"
                            data-direct-upload-preset="<?= e($item['preset']); ?>"
                        >
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-cyan-600 shadow-sm transition-transform group-hover:scale-110">
                            <i class="fa-solid fa-photo-film text-xl"></i>
                        </div>
                        <p class="text-sm font-black text-slate-700" data-custom-ui-upload-title="1"><?= e(t('admin.custom_ui.upload_media')); ?></p>
                        <p class="mt-1 text-xs font-medium text-slate-400" data-custom-ui-upload-meta="1"><?= e($item['media_kind'] === 'video' ? t('admin.custom_ui.video_meta') : t('admin.custom_ui.media_meta')); ?></p>
                    </label>

                    <div class="flex flex-wrap items-center gap-3">
                        <button class="inline-flex h-11 items-center justify-center rounded-2xl bg-cyan-600 px-5 text-sm font-black text-white shadow-lg shadow-cyan-600/20 transition hover:-translate-y-0.5 hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60" type="submit" data-custom-ui-save="1">
                            <?= e(t('admin.custom_ui.save_media')); ?>
                        </button>
                        <button class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50" type="submit" name="reset_media" value="1">
                            <?= e(t('admin.custom_ui.reset_default')); ?>
                        </button>
                    </div>
                </form>
            </article>
        <?php endforeach; ?>
    </section>
</div>

<script>
    (function () {
        const forms = Array.from(document.querySelectorAll('[data-custom-ui-form="1"]'));
        if (forms.length === 0) {
            return;
        }

        const i18n = {
            uploadMedia: <?= json_encode(t('admin.custom_ui.upload_media'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            videoMeta: <?= json_encode(t('admin.custom_ui.video_meta'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            mediaMeta: <?= json_encode(t('admin.custom_ui.media_meta'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            uploading: <?= json_encode(t('admin.custom_ui.direct_uploading'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            uploadComplete: <?= json_encode(t('admin.custom_ui.direct_upload_complete'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            uploadFailed: <?= json_encode(t('admin.custom_ui.direct_upload_failed'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        };

        function csrfToken() {
            const field = document.querySelector('input[name="_csrf"]');
            return field ? field.value : '';
        }

        async function requestDirectUpload(file, preset) {
            const payload = new URLSearchParams();
            payload.set('_csrf', csrfToken());
            payload.set('preset', preset);
            payload.set('filename', file.name);
            payload.set('content_type', file.type || 'application/octet-stream');
            payload.set('file_size', String(file.size || 0));
            payload.set('format', 'json');

            const response = await fetch('/api/index.php?resource=uploads&method=direct-sign&format=json', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString()
            });

            const result = await response.json().catch(() => null);
            if (!response.ok || !result || result.status !== 'success' || !result.data) {
                throw new Error((result && result.message) ? result.message : i18n.uploadFailed);
            }

            return result.data;
        }

        async function uploadFileDirect(file, preset) {
            const spec = await requestDirectUpload(file, preset);
            const uploadHeaders = spec.headers && typeof spec.headers === 'object' ? spec.headers : {};
            const uploadResponse = await fetch(spec.upload_url, {
                method: spec.method || 'PUT',
                headers: uploadHeaders,
                body: file
            });

            if (!uploadResponse.ok) {
                throw new Error(i18n.uploadFailed);
            }

            return spec;
        }

        function looksLikeVideo(path) {
            return /\.(mp4|mov|webm|avi|m4v)(?:$|[?#])/i.test(String(path || ''));
        }

        function updatePreview(form, url) {
            const image = form.querySelector('[data-custom-ui-image="1"]');
            const video = form.querySelector('[data-custom-ui-video="1"]');
            const videoSource = form.querySelector('[data-custom-ui-video-source="1"]');
            const previewUrl = String(url || '').trim();
            const useVideo = looksLikeVideo(previewUrl);

            if (image instanceof HTMLImageElement) {
                if (useVideo) {
                    image.classList.add('hidden');
                    image.removeAttribute('src');
                } else {
                    image.src = previewUrl;
                    image.classList.remove('hidden');
                }
            }

            if (video instanceof HTMLVideoElement && videoSource instanceof HTMLSourceElement) {
                if (useVideo) {
                    videoSource.src = previewUrl;
                    video.classList.remove('hidden');
                    video.load();
                } else {
                    video.classList.add('hidden');
                    video.pause();
                    videoSource.src = '';
                    video.load();
                }
            }
        }

        forms.forEach(function (form) {
            const input = form.querySelector('[data-custom-ui-input="1"]');
            const hiddenField = form.querySelector('[data-custom-ui-hidden="1"]');
            const title = form.querySelector('[data-custom-ui-upload-title="1"]');
            const meta = form.querySelector('[data-custom-ui-upload-meta="1"]');
            const statusBox = form.querySelector('[data-custom-ui-status-box="1"]');
            const saveButton = form.querySelector('[data-custom-ui-save="1"]');

            if (!(input instanceof HTMLInputElement) || !(hiddenField instanceof HTMLInputElement) || !(title instanceof HTMLElement) || !(meta instanceof HTMLElement) || !(saveButton instanceof HTMLButtonElement)) {
                return;
            }

            input.addEventListener('change', async function () {
                const file = input.files && input.files[0] ? input.files[0] : null;
                const preset = input.dataset.directUploadPreset || '';
                if (!file || preset === '') {
                    return;
                }

                saveButton.disabled = true;
                title.textContent = i18n.uploading;
                meta.textContent = file.name;

                try {
                    const spec = await uploadFileDirect(file, preset);
                    hiddenField.value = String(spec.public_url || '');
                    updatePreview(form, hiddenField.value);
                    title.textContent = i18n.uploadComplete;
                    meta.textContent = file.name;
                    if (statusBox instanceof HTMLElement) {
                        statusBox.className = 'rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700';
                        statusBox.textContent = i18n.uploadComplete;
                    }
                } catch (error) {
                    title.textContent = i18n.uploadMedia;
                    meta.textContent = i18n.uploadFailed;
                    if (statusBox instanceof HTMLElement) {
                        statusBox.className = 'rounded-[1.4rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700';
                        statusBox.textContent = error instanceof Error && error.message ? error.message : i18n.uploadFailed;
                    }
                } finally {
                    saveButton.disabled = false;
                }
            });
        });
    })();
</script>
