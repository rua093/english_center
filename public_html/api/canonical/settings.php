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
