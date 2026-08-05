<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/file_storage.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../models/MediaGalleryModel.php';

function helper_extract_youtube_id(string $url): ?string
{
    $url = trim($url);
    if ($url === '') {
        return null;
    }

    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $matches)) {
        return $matches[1];
    }

    return null;
}

function api_media_gallery_save_category_action(): void
{
    require_role(['admin', 'academic']);
    api_require_post(page_url('manage-media'));

    $galleryModel = new MediaGalleryModel();
    $id = input_int($_POST, 'id');
    $name = trim((string) ($_POST['name'] ?? ''));

    if ($name === '') {
        set_flash('error', 'Vui lòng nhập tên chủ đề.');
        redirect(page_url('manage-media', ['tab' => 'categories']));
    }

    try {
        $savedId = $galleryModel->saveCategory($_POST);
        set_flash('success', $id > 0 ? 'Đã cập nhật chủ đề thành công.' : 'Đã thêm chủ đề mới thành công.');
    } catch (Throwable $e) {
        set_flash('error', 'Lỗi khi lưu chủ đề: ' . $e->getMessage());
    }

    redirect(page_url('manage-media', ['tab' => 'categories']));
}

function api_media_gallery_delete_category_action(): void
{
    require_role(['admin', 'academic']);
    api_require_post(page_url('manage-media'));

    $id = input_int($_POST, 'id');
    if ($id <= 0) {
        set_flash('error', 'Chủ đề không hợp lệ.');
        redirect(page_url('manage-media', ['tab' => 'categories']));
    }

    try {
        $galleryModel = new MediaGalleryModel();
        $galleryModel->deleteCategory($id);
        set_flash('success', 'Đã xóa chủ đề thành công.');
    } catch (Throwable $e) {
        set_flash('error', 'Không thể xóa chủ đề. Có thể chủ đề đang chứa các media.');
    }

    redirect(page_url('manage-media', ['tab' => 'categories']));
}

function api_media_gallery_save_item_action(): void
{
    require_role(['admin', 'academic']);
    api_require_post(page_url('manage-media'));

    $galleryModel = new MediaGalleryModel();
    $itemId = input_int($_POST, 'id');
    $existing = $itemId > 0 ? $galleryModel->findMediaItemById($itemId) : null;

    $categoryId = input_int($_POST, 'category_id');
    $title = trim((string) ($_POST['title'] ?? ''));
    $mediaType = trim((string) ($_POST['media_type'] ?? 'image'));
    $youtubeUrlInput = trim((string) ($_POST['youtube_url'] ?? ''));

    if ($categoryId <= 0) {
        set_flash('error', 'Vui lòng chọn chủ đề cho media.');
        redirect(page_url('manage-media'));
    }

    if ($title === '') {
        set_flash('error', 'Vui lòng nhập tiêu đề bài đăng.');
        redirect(page_url('manage-media'));
    }

    $filePathOrUrl = trim((string) ($existing['file_path_or_url'] ?? ''));
    $thumbnailUrl = trim((string) ($existing['thumbnail_url'] ?? ''));

    // Handle Direct Upload S3 / Preset URL if passed from JS
    $directUploadPath = trim((string) ($_POST['uploaded_media_url'] ?? ''));
    if ($directUploadPath !== '') {
        if (!is_trusted_uploaded_file_url($directUploadPath)) {
            set_flash('error', 'Tệp tải lên không hợp lệ.');
            redirect(page_url('manage-media'));
        }
        $filePathOrUrl = normalize_public_file_url($directUploadPath);
    }

    $currentUser = auth_user();

    // Check if multiple files are uploaded (Batch upload mode for new media)
    $hasMultipleFiles = false;
    if ($itemId === 0 && isset($_FILES['media_files']) && is_array($_FILES['media_files']['name'] ?? null) && count($_FILES['media_files']['name']) > 1) {
        $hasMultipleFiles = true;
    }

    if ($hasMultipleFiles) {
        $uploadedCount = 0;
        $filesCount = count($_FILES['media_files']['name']);
        for ($i = 0; $i < $filesCount; $i++) {
            $errorCode = (int) ($_FILES['media_files']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_OK) {
                continue;
            }
            $singleFile = [
                'name' => $_FILES['media_files']['name'][$i],
                'type' => $_FILES['media_files']['type'][$i],
                'tmp_name' => $_FILES['media_files']['tmp_name'][$i],
                'error' => $_FILES['media_files']['error'][$i],
                'size' => $_FILES['media_files']['size'][$i],
            ];

            $storedPath = store_uploaded_file_for_preset($singleFile, 'media_gallery_item');
            if ($storedPath !== null) {
                $itemType = app_uploaded_file_looks_like_video($singleFile) ? 'video' : 'image';
                $itemTitle = $title . ($filesCount > 1 ? ' (' . ($uploadedCount + 1) . ')' : '');

                $payload = [
                    'id' => 0,
                    'category_id' => $categoryId,
                    'title' => $itemTitle,
                    'media_type' => $itemType,
                    'file_path_or_url' => $storedPath,
                    'thumbnail_url' => '',
                    'description' => trim((string) ($_POST['description'] ?? '')),
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 1,
                    'created_by' => (int) ($currentUser['id'] ?? 0),
                ];
                $galleryModel->saveMediaItem($payload);
                app_uploaded_object_manifest_mark_attached($storedPath, ['entity' => 'media_gallery_item']);
                $uploadedCount++;
            }
        }

        if ($uploadedCount > 0) {
            set_flash('success', "Đã tải lên thành công {$uploadedCount} tệp media cho chủ đề.");
        } else {
            set_flash('error', 'Không thể tải các tệp media lên. Vui lòng kiểm tra dung lượng tệp.');
        }
        redirect(page_url('manage-media'));
        return;
    }

    // Single file upload fallback
    if (isset($_FILES['media_file']) && is_array($_FILES['media_file']) && (int) ($_FILES['media_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $storedPath = store_uploaded_file_for_preset($_FILES['media_file'], 'media_gallery_item');
        if ($storedPath === null) {
            set_flash('error', 'Không thể tải tệp media lên. Vui lòng thử lại.');
            redirect(page_url('manage-media'));
        }
        $filePathOrUrl = $storedPath;

        // Auto-detect media type if file was uploaded
        if (app_uploaded_file_looks_like_video($_FILES['media_file'])) {
            $mediaType = 'video';
        } else {
            $mediaType = 'image';
        }
    } elseif (isset($_FILES['media_files']) && is_array($_FILES['media_files']['name'] ?? null) && (int) ($_FILES['media_files']['error'][0] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $singleFile = [
            'name' => $_FILES['media_files']['name'][0],
            'type' => $_FILES['media_files']['type'][0],
            'tmp_name' => $_FILES['media_files']['tmp_name'][0],
            'error' => $_FILES['media_files']['error'][0],
            'size' => $_FILES['media_files']['size'][0],
        ];
        $storedPath = store_uploaded_file_for_preset($singleFile, 'media_gallery_item');
        if ($storedPath !== null) {
            $filePathOrUrl = $storedPath;
            $mediaType = app_uploaded_file_looks_like_video($singleFile) ? 'video' : 'image';
        }
    }

    // Handle Thumbnail File Upload
    if (isset($_FILES['thumbnail_file']) && is_array($_FILES['thumbnail_file']) && (int) ($_FILES['thumbnail_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $storedThumbPath = store_uploaded_file_for_preset($_FILES['thumbnail_file'], 'media_gallery_item');
        if ($storedThumbPath !== null) {
            $thumbnailUrl = $storedThumbPath;
        }
    }

    // Handle YouTube Video Type
    if ($mediaType === 'youtube') {
        if ($youtubeUrlInput !== '') {
            $ytId = helper_extract_youtube_id($youtubeUrlInput);
            if ($ytId !== null) {
                $filePathOrUrl = 'https://www.youtube.com/watch?v=' . $ytId;
                if ($thumbnailUrl === '') {
                    $thumbnailUrl = 'https://img.youtube.com/vi/' . $ytId . '/hqdefault.jpg';
                }
            } else {
                $filePathOrUrl = $youtubeUrlInput;
            }
        }
    }

    if ($filePathOrUrl === '') {
        set_flash('error', 'Vui lòng chọn tệp ảnh/video hoặc dán đường dẫn YouTube.');
        redirect(page_url('manage-media'));
    }

    $payload = [
        'id' => $itemId,
        'category_id' => $categoryId,
        'title' => $title,
        'media_type' => $mediaType,
        'file_path_or_url' => $filePathOrUrl,
        'thumbnail_url' => $thumbnailUrl,
        'description' => trim((string) ($_POST['description'] ?? '')),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 1,
        'created_by' => (int) ($currentUser['id'] ?? 0),
    ];

    $savedId = $galleryModel->saveMediaItem($payload);

    if ($filePathOrUrl !== '' && !str_starts_with($filePathOrUrl, 'http://') && !str_starts_with($filePathOrUrl, 'https://')) {
        app_uploaded_object_manifest_mark_attached($filePathOrUrl, ['entity' => 'media_gallery_item']);
    }

    if (is_array($existing)) {
        $oldFile = (string) ($existing['file_path_or_url'] ?? '');
        if ($oldFile !== '' && $oldFile !== $filePathOrUrl) {
            app_cleanup_replaced_uploaded_file($oldFile, $filePathOrUrl);
        }
    }

    set_flash('success', $itemId > 0 ? 'Đã cập nhật bài đăng thành công.' : 'Đã đăng bài thành công.');
    redirect(page_url('manage-media'));
}

function api_media_gallery_delete_item_action(): void
{
    require_role(['admin', 'academic']);
    api_require_post(page_url('manage-media'));

    $id = input_int($_POST, 'id');
    if ($id <= 0) {
        set_flash('error', 'Bài đăng không hợp lệ.');
        redirect(page_url('manage-media'));
    }

    try {
        $galleryModel = new MediaGalleryModel();
        $existing = $galleryModel->findMediaItemById($id);
        $galleryModel->deleteMediaItem($id);

        if (is_array($existing)) {
            $filePath = (string) ($existing['file_path_or_url'] ?? '');
            if ($filePath !== '' && str_starts_with($filePath, '/assets/uploads')) {
                app_delete_uploaded_file_by_url($filePath);
            }
        }
        set_flash('success', 'Đã xóa bài đăng media.');
    } catch (Throwable $e) {
        set_flash('error', 'Không thể xóa bài đăng media.');
    }

    redirect(page_url('manage-media'));
}
