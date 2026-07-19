<?php
declare(strict_types=1);

function api_settings_save_home_intro_video_action(): void
{
    require_role(['admin']);
    require_permission('admin.dashboard.view');
    api_require_post(page_url('dashboard-admin'));

    $settingKey = 'home_intro_video_url';
    $existingVideoUrl = trim((string) app_setting_get($settingKey, ''));
    $videoUrl = trim((string) ($_POST['home_intro_video_url_hidden'] ?? ''));

    if ($videoUrl !== '' && !is_trusted_uploaded_file_url($videoUrl)) {
        set_flash('error', 'Video intro tải lên chưa hợp lệ. Vui lòng thử lại.');
        redirect(page_url('dashboard-admin'));
    }

    if (isset($_FILES['home_intro_video_file']) && (int) ($_FILES['home_intro_video_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $storedVideoUrl = store_uploaded_file_for_preset($_FILES['home_intro_video_file'], 'home_intro_video');
        if ($storedVideoUrl === null) {
            set_flash('error', 'Không thể tải video intro lên. Vui lòng thử lại.');
            redirect(page_url('dashboard-admin'));
        }

        $videoUrl = $storedVideoUrl;
    }

    if ($videoUrl === '') {
        set_flash('error', 'Vui lòng chọn video intro để tải lên.');
        redirect(page_url('dashboard-admin'));
    }

    $videoUrl = normalize_public_file_url($videoUrl);
    app_setting_set($settingKey, $videoUrl);
    app_uploaded_object_manifest_mark_attached($videoUrl, ['entity' => 'home_intro_video']);
    app_cleanup_replaced_uploaded_file($existingVideoUrl, $videoUrl);

    set_flash('success', 'Đã cập nhật video intro trang chủ.');
    redirect(page_url('dashboard-admin'));
}

function api_settings_save_custom_ui_media_action(): void
{
    require_role(['admin']);
    require_permission('admin.dashboard.view');
    api_require_post(page_url('custom-ui-admin'));

    $mediaKey = trim((string) ($_POST['custom_ui_key'] ?? ''));
    $definition = custom_ui_media_definition($mediaKey);
    if (!is_array($definition)) {
        set_flash('error', 'Không tìm thấy cấu hình media giao diện cần cập nhật.');
        redirect(page_url('custom-ui-admin'));
    }

    $settingKey = trim((string) ($definition['setting_key'] ?? ''));
    $presetKey = trim((string) ($definition['preset'] ?? ''));
    $mediaTitle = function_exists('t') ? t((string) ($definition['title_key'] ?? '')) : $mediaKey;
    $existingUrl = trim((string) app_setting_get($settingKey, ''));
    $resetRequested = trim((string) ($_POST['reset_media'] ?? '')) === '1';

    if ($resetRequested) {
        app_setting_set($settingKey, '');
        app_cleanup_replaced_uploaded_file($existingUrl, '');
        set_flash('success', 'Đã khôi phục media mặc định cho ' . $mediaTitle . '.');
        redirect(page_url('custom-ui-admin'));
    }

    $mediaUrl = trim((string) ($_POST['custom_ui_media_url_hidden'] ?? ''));
    if ($mediaUrl !== '' && !is_trusted_uploaded_file_url($mediaUrl)) {
        set_flash('error', 'Media tải lên chưa hợp lệ. Vui lòng thử lại.');
        redirect(page_url('custom-ui-admin'));
    }

    if (isset($_FILES['custom_ui_media_file']) && (int) ($_FILES['custom_ui_media_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $storedMediaUrl = store_uploaded_file_for_preset($_FILES['custom_ui_media_file'], $presetKey);
        if ($storedMediaUrl === null) {
            set_flash('error', 'Không thể tải media banner lên. Vui lòng thử lại.');
            redirect(page_url('custom-ui-admin'));
        }

        $mediaUrl = $storedMediaUrl;
    }

    if ($mediaUrl === '') {
        set_flash('error', 'Vui lòng chọn media để áp dụng cho giao diện.');
        redirect(page_url('custom-ui-admin'));
    }

    $mediaUrl = normalize_public_file_url($mediaUrl);
    app_setting_set($settingKey, $mediaUrl);
    app_uploaded_object_manifest_mark_attached($mediaUrl, ['entity' => $mediaKey]);
    app_cleanup_replaced_uploaded_file($existingUrl, $mediaUrl);

    set_flash('success', 'Đã cập nhật media cho ' . $mediaTitle . '.');
    redirect(page_url('custom-ui-admin'));
}
