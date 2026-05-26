<?php
require_once __DIR__ . '/../../core/file_storage.php';

$assignmentId = (int) ($_GET['id'] ?? 0);
if ($assignmentId > 0) {
    require_permission('academic.assignments.update');
} else {
    require_permission('academic.assignments.create');
}

$academicModel = new AcademicModel();
$editingAssignment = $assignmentId > 0 ? $academicModel->findAssignment($assignmentId) : null;
$lessonRows = $academicModel->assignmentLookups();
$lessons = array_map(static function (array $lesson): array {
    $title = trim((string) ($lesson['actual_title'] ?? ''));
    if ($title === '') {
        $title = t('admin.assignment_edit.lesson_fallback', [
            'date' => (string) ($lesson['study_date'] ?? ''),
            'time' => (string) ($lesson['start_time'] ?? ''),
        ]);
    }
    $className = trim((string) ($lesson['class_name'] ?? ''));
    return [
        'id' => (int) ($lesson['id'] ?? 0),
        'class_id' => (int) ($lesson['class_id'] ?? 0),
        'class_name' => $className,
        'title' => $className !== '' ? ($title . ' - ' . $className) : $title,
    ];
}, $lessonRows);

$assignmentClasses = [];
foreach ($lessons as $lesson) {
    $classId = (int) ($lesson['class_id'] ?? 0);
    if ($classId <= 0 || isset($assignmentClasses[$classId])) {
        continue;
    }

    $assignmentClasses[$classId] = [
        'id' => $classId,
        'class_name' => (string) ($lesson['class_name'] ?? t('admin.assignment_edit.class_fallback', ['id' => (string) $classId])),
    ];
}

$selectedAssignmentClassId = 0;
$selectedAssignmentScheduleId = 0;
if (is_array($editingAssignment)) {
    $selectedAssignmentScheduleId = (int) ($editingAssignment['schedule_id'] ?? 0);
    foreach ($lessons as $lesson) {
        if ((int) ($lesson['id'] ?? 0) !== $selectedAssignmentScheduleId) {
            continue;
        }

        $selectedAssignmentClassId = (int) ($lesson['class_id'] ?? 0);
        break;
    }
} else {
    $requestedClassId = max(0, (int) ($_GET['class_id'] ?? 0));
    $requestedScheduleId = max(0, (int) ($_GET['schedule_id'] ?? 0));

    if ($requestedClassId > 0 && isset($assignmentClasses[$requestedClassId])) {
        $selectedAssignmentClassId = $requestedClassId;
    }

    if ($requestedScheduleId > 0) {
        foreach ($lessons as $lesson) {
            if ((int) ($lesson['id'] ?? 0) !== $requestedScheduleId) {
                continue;
            }

            $lessonClassId = (int) ($lesson['class_id'] ?? 0);
            if ($selectedAssignmentClassId > 0 && $lessonClassId !== $selectedAssignmentClassId) {
                break;
            }

            $selectedAssignmentClassId = $lessonClassId;
            $selectedAssignmentScheduleId = $requestedScheduleId;
            break;
        }
    }
}

$deadlineValue = !empty($editingAssignment['deadline']) ? date('Y-m-d\TH:i', strtotime((string) $editingAssignment['deadline'])) : '';
$existingAssignmentFileUrl = normalize_public_file_url((string) ($editingAssignment['file_url'] ?? ''));

$module = 'assignments';
$adminTitle = $editingAssignment ? t('admin.assignment_edit.title_edit') : t('admin.assignment_edit.title_add');
?>
<div class="grid gap-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2><?= e($editingAssignment ? t('admin.assignment_edit.heading_edit') : t('admin.assignment_edit.heading_add')); ?></h2>
        <form id="assignment-edit-upload-form" class="grid gap-3" method="post" action="/api/assignments/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingAssignment['id'] ?? 0); ?>">
                <input type="hidden" name="existing_file_url" value="<?= e($existingAssignmentFileUrl); ?>">
                <input type="hidden" id="assignmentEditUploadedFileUrl" name="uploaded_file_url" value="">
                <label><?= e(t('admin.assignment_edit.class')); ?>
                    <select id="assignment-class-select" name="class_id" required>
                        <option value=""><?= e(t('admin.assignment_edit.choose_class')); ?></option>
                        <?php foreach ($assignmentClasses as $assignmentClass): ?>
                            <option value="<?= (int) $assignmentClass['id']; ?>" <?= $selectedAssignmentClassId === (int) $assignmentClass['id'] ? 'selected' : ''; ?>><?= e((string) $assignmentClass['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= e(t('admin.assignment_edit.lesson')); ?>
                    <select id="assignment-lesson-select" name="schedule_id" required>
                        <option value=""><?= e(t('admin.assignment_edit.choose_lesson')); ?></option>
                        <?php foreach ($lessons as $lesson): ?>
                            <option data-class-id="<?= (int) ($lesson['class_id'] ?? 0); ?>" value="<?= (int) $lesson['id']; ?>" <?= $selectedAssignmentScheduleId === (int) $lesson['id'] ? 'selected' : ''; ?>><?= e((string) $lesson['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= e(t('admin.assignment_edit.assignment_title')); ?><input type="text" name="title" value="<?= e((string) ($editingAssignment['title'] ?? '')); ?>" required></label>
                <label><?= e(t('admin.assignment_edit.description')); ?><textarea name="description" rows="4"><?= e((string) ($editingAssignment['description'] ?? '')); ?></textarea></label>
                <label><?= e(t('admin.assignment_edit.deadline')); ?><input type="datetime-local" name="deadline" value="<?= e($deadlineValue); ?>" required></label>
                <label><?= e(t('admin.assignment_edit.upload_file')); ?><input id="assignmentEditFileInput" type="file" name="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png" data-direct-upload-preset="assignment_file"></label>
                <div id="assignmentEditUploadStatus" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold leading-relaxed text-slate-700">File bài tập sẽ được tải lên khi bạn chọn file.</div>
                <?php if ($existingAssignmentFileUrl !== ''): ?>
                    <div class="rounded-xl border border-blue-200 bg-blue-50/70 px-3 py-2.5 text-sm font-medium leading-relaxed text-slate-700"><?= e(t('admin.assignment_edit.current_file')); ?>: <span class="font-semibold text-slate-800"><?= e(basename(parse_url($existingAssignmentFileUrl, PHP_URL_PATH) ?: $existingAssignmentFileUrl)); ?></span>. <a class="font-semibold text-blue-700 hover:underline" href="<?= e($existingAssignmentFileUrl); ?>" target="_blank" rel="noopener noreferrer"><?= e(t('admin.assignment_edit.open_file')); ?></a>. <?= e(t('admin.assignment_edit.replace_hint')); ?></div>
                <?php endif; ?>
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.assignment_edit.save')); ?></button>
            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('assignments-academic')); ?>"><?= e(t('admin.common.back')); ?></a>
        </form>
    </article>
</div>

<script>
(function () {
    const classSelect = document.getElementById('assignment-class-select');
    const lessonSelect = document.getElementById('assignment-lesson-select');
    if (!classSelect || !lessonSelect) {
        return;
    }

    const lessonOptions = Array.from(lessonSelect.querySelectorAll('option[data-class-id]')).map(function (option) {
        return {
            value: String(option.value || ''),
            classId: String(option.getAttribute('data-class-id') || ''),
            label: String(option.textContent || ''),
        };
    });

    const initialLessonValue = String(lessonSelect.value || '');

    function renderLessonOptions(preferredValue) {
        const selectedClassId = String(classSelect.value || '');
        lessonSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = <?= json_encode(t('admin.assignment_edit.choose_lesson'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        lessonSelect.appendChild(placeholder);

        const matchingLessons = selectedClassId === '' ? [] : lessonOptions.filter(function (lesson) {
            return lesson.classId === selectedClassId;
        });

        matchingLessons.forEach(function (lesson) {
            const option = document.createElement('option');
            option.value = lesson.value;
            option.textContent = lesson.label;
            lessonSelect.appendChild(option);
        });

        if (lessonSelect.tomselect) {
            lessonSelect.tomselect.sync();
        }

        if (selectedClassId === '' || matchingLessons.length === 0) {
            lessonSelect.value = '';
            lessonSelect.disabled = true;
            if (lessonSelect.tomselect) {
                lessonSelect.tomselect.setValue('');
                lessonSelect.tomselect.disable();
            }
            return;
        }

        lessonSelect.disabled = false;
        if (lessonSelect.tomselect) {
            lessonSelect.tomselect.enable();
        }

        const safePreferredValue = String(preferredValue || '');
        if (safePreferredValue !== '' && matchingLessons.some(function (lesson) { return lesson.value === safePreferredValue; })) {
            lessonSelect.value = safePreferredValue;
            if (lessonSelect.tomselect) {
                lessonSelect.tomselect.setValue(safePreferredValue);
            }
        } else {
            lessonSelect.value = '';
            if (lessonSelect.tomselect) {
                lessonSelect.tomselect.setValue('');
            }
        }
    }

    classSelect.addEventListener('change', function () {
        renderLessonOptions('');
    });

    renderLessonOptions(initialLessonValue);
})();
</script>
<script>
(function () {
    const form = document.getElementById('assignment-edit-upload-form');
    const fileInput = document.getElementById('assignmentEditFileInput');
    const hiddenInput = document.getElementById('assignmentEditUploadedFileUrl');
    const status = document.getElementById('assignmentEditUploadStatus');
    const submitButton = form ? form.querySelector('button[type="submit"]') : null;
    let isUploading = false;

    if (!(form instanceof HTMLFormElement) || !(fileInput instanceof HTMLInputElement) || !(hiddenInput instanceof HTMLInputElement)) {
        return;
    }

    function setUploadingState(uploading, message) {
        isUploading = uploading;
        if (submitButton instanceof HTMLButtonElement) {
            submitButton.disabled = uploading;
        }
        if (status instanceof HTMLElement && typeof message === 'string') {
            status.textContent = message;
        }
    }

    async function uploadDirect(file) {
        const signResponse = await fetch('/api/index.php?resource=uploads&method=direct-sign&format=json', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: new URLSearchParams({
                preset: 'assignment_file',
                filename: file.name,
                content_type: file.type || 'application/octet-stream',
                file_size: String(file.size || 0),
                _token: <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            }),
        });
        const signPayload = await signResponse.json().catch(function () { return null; });
        if (!signResponse.ok || !signPayload || signPayload.status !== 'success' || !signPayload.data) {
            throw new Error((signPayload && signPayload.message) || 'Không thể tải file lên lúc này.');
        }

        const spec = signPayload.data;
        const headers = Object.assign({}, spec.headers || {});
        if (!headers['Content-Type']) {
            headers['Content-Type'] = file.type || 'application/octet-stream';
        }

        const uploadResponse = await fetch(spec.upload_url, {
            method: spec.method || 'PUT',
            headers: headers,
            body: file,
        });
        if (!uploadResponse.ok) {
            throw new Error('Không thể tải file bài tập lên.');
        }

        hiddenInput.value = String(spec.public_url || '');
        fileInput.value = '';
    }

    fileInput.addEventListener('change', async function () {
        const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
        hiddenInput.value = '';

        if (!file) {
            setUploadingState(false, 'File bài tập sẽ được tải lên khi bạn chọn file.');
            return;
        }

        try {
            setUploadingState(true, 'Đã chọn file: ' + file.name + '. Đang tải file bài tập lên...');
            await uploadDirect(file);
            setUploadingState(false, 'Đã tải file xong: ' + file.name + '.');
        } catch (error) {
            hiddenInput.value = '';
            setUploadingState(false, 'Đã chọn file: ' + file.name + '. Không thể tải trực tiếp. Bạn vẫn có thể lưu để tải file lên theo cách thông thường.');
        }
    });

    form.addEventListener('submit', function (event) {
        if (isUploading) {
            event.preventDefault();
            setUploadingState(false, 'File vẫn đang được tải lên. Vui lòng chờ hoàn tất.');
            return;
        }

    });
})();
</script>


