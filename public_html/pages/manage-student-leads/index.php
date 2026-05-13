<?php
declare(strict_types=1);

require_admin_or_staff();
require_any_permission(['student_lead.view']);

if (!function_exists('student_lead_extract_email')) {
    function student_lead_extract_email(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }

        if (preg_match('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $normalized, $matches) === 1) {
            return strtolower((string) ($matches[0] ?? ''));
        }

        return '';
    }
}

if (!function_exists('student_lead_extract_phone')) {
    function student_lead_extract_phone(string $value): string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return '';
        }

        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $normalized, $matches) !== 1) {
            return '';
        }

        $digits = preg_replace('/\D+/', '', (string) ($matches[0] ?? ''));
        if (!is_string($digits) || $digits === '') {
            return '';
        }

        return $digits;
    }
}

if (!function_exists('student_lead_suggested_username')) {
    function student_lead_suggested_username(array $lead): string
    {
        $parentName = (string) ($lead['parent_name'] ?? '');
        $parentPhone = trim((string) ($lead['parent_phone'] ?? ''));

        $email = student_lead_extract_email($parentName);
        if ($email !== '' && str_contains($email, '@')) {
            $localPart = (string) strstr($email, '@', true);
            if ($localPart !== '') {
                return $localPart;
            }
        }

        $phoneDigits = student_lead_extract_phone($parentPhone);
        if ($phoneDigits === '') {
            $phoneDigits = student_lead_extract_phone($parentName);
        }
        if ($phoneDigits !== '') {
            return 'student' . substr($phoneDigits, -6);
        }

        $studentName = strtolower(trim((string) ($lead['student_name'] ?? '')));
        if ($studentName !== '') {
            $slug = preg_replace('/[^a-z0-9]+/', '', $studentName) ?? '';
            if ($slug !== '') {
                return 'student.' . substr($slug, 0, 24);
            }
        }

        return 'student' . date('His');
    }
}

if (!function_exists('student_lead_status_badge_class')) {
    function student_lead_status_badge_class(string $status): string
    {
        return match ($status) {
            'new' => 'is-pending',
            'entry_tested' => 'is-upcoming',
            'trial_completed' => 'is-trial',
            'official' => 'is-approved',
            'cancelled' => 'is-rejected',
            default => 'is-pending',
        };
    }
}

if (!function_exists('student_lead_value_or_dash')) {
    function student_lead_value_or_dash(mixed $value): string
    {
        $normalized = trim((string) $value);
        return $normalized === '' ? '—' : $normalized;
    }
}

if (!function_exists('student_lead_short_text')) {
    function student_lead_short_text(mixed $value, int $limit = 120): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '—';
        }

        $length = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
        if ($length <= $limit) {
            return $text;
        }

        $snippet = function_exists('mb_substr') ? mb_substr($text, 0, $limit - 1) : substr($text, 0, $limit - 1);
        return rtrim($snippet) . '…';
    }
}

if (!function_exists('student_lead_format_datetime')) {
    function student_lead_format_datetime(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '—';
        }

        try {
            $dt = new DateTimeImmutable($raw);
            return $dt->format('d/m/Y H:i');
        } catch (Throwable) {
            return $raw;
        }
    }
}

if (!function_exists('student_lead_format_date')) {
    function student_lead_format_date(?string $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '—';
        }

        try {
            $dt = new DateTimeImmutable($raw);
            return $dt->format('d/m/Y');
        } catch (Throwable) {
            return $raw;
        }
    }
}

if (!function_exists('student_lead_can_convert_status')) {
    function student_lead_can_convert_status(string $status): bool
    {
        return in_array(strtolower(trim($status)), ['trial_completed', 'official'], true);
    }
}

if (!function_exists('student_lead_prefill_link')) {
    function student_lead_prefill_link(array $lead): string
    {
        $payload = [
            'lead_id' => (int) ($lead['id'] ?? 0),
            'student_name' => trim((string) ($lead['student_name'] ?? '')),
            'student_dob' => trim((string) ($lead['dob'] ?? '')),
            'parent_name' => trim((string) ($lead['parent_name'] ?? '')),
            'parent_phone' => trim((string) ($lead['parent_phone'] ?? '')),
            'parent_email' => trim((string) ($lead['parent_email'] ?? '')),
            'student_hobbies' => trim((string) ($lead['interests'] ?? '')),
            'student_school' => trim((string) ($lead['school_name'] ?? '')),
            'student_grade' => trim((string) ($lead['current_grade'] ?? '')),
            'current_level' => trim((string) ($lead['current_level'] ?? '')),
            'student_gender' => trim((string) ($lead['gender'] ?? '')),
            'student_personality' => trim((string) ($lead['personality'] ?? '')),
        ];

        return page_url('register-consultation', [
            'prefill' => api_encode_payload($payload),
        ]);
    }
}

$adminModel = new AdminModel();
$statusOptions = [
    'new' => t('admin.student_leads.status.new'),
    'entry_tested' => t('admin.student_leads.status.entry_tested'),
    'trial_completed' => t('admin.student_leads.status.trial_completed'),
    'official' => t('admin.student_leads.status.official'),
    'cancelled' => t('admin.student_leads.status.cancelled'),
];

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
if (!isset($statusOptions[$statusFilter])) {
    $statusFilter = '';
}
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$highlightLeadId = max(0, (int) ($_GET['highlight_lead_id'] ?? 0));
$filters = [
    'status' => $statusFilter,
];

$leadPage = max(1, (int) ($_GET['lead_page'] ?? 1));
$leadPerPage = ui_pagination_resolve_per_page('lead_per_page', 10);
$leadTotal = $adminModel->countStudentLeads($filters, $searchQuery);
$leadTotalPages = max(1, (int) ceil($leadTotal / $leadPerPage));
if ($leadPage > $leadTotalPages) {
    $leadPage = $leadTotalPages;
}

$leads = $adminModel->listStudentLeadsPage($leadPage, $leadPerPage, $filters, $searchQuery);
$leadPerPageOptions = ui_pagination_per_page_options();

$statusSummary = [];
foreach ($statusOptions as $statusKey => $statusLabel) {
    $statusSummary[$statusKey] = [
        'label' => $statusLabel,
        'count' => $adminModel->countStudentLeads(['status' => $statusKey]),
        'badgeClass' => student_lead_status_badge_class($statusKey),
    ];
}

$editingLead = null;
if (!empty($_GET['edit'])) {
    $editingLead = $adminModel->findStudentLead((int) $_GET['edit']);
}

$module = 'student-leads';
$adminTitle = t('admin.student_leads.title');
$adminDescription = t('admin.student_leads.description');

$success = get_flash('success');
$error = get_flash('error');
$canConvertLead = has_any_permission(['student_lead.update']);
$canDeleteLead = has_permission('student_lead.delete');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= e(t('admin.student_leads.pipeline_title')); ?></h3>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <?php foreach ($statusSummary as $summary): ?>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= e($summary['label']); ?></p>
                    <p class="mt-1 text-2xl font-black text-slate-800"><?= (int) $summary['count']; ?></p>
                    <span class="mt-2 inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold <?= e((string) $summary['badgeClass']); ?>">
                        <?= e($summary['label']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <?php if (is_array($editingLead)): ?>
        <?php
            $editingStatus = (string) ($editingLead['status'] ?? 'new');
            $editingStatusLabel = (string) ($statusOptions[$editingStatus] ?? $editingStatus);
            $editingLeadConverted = (int) ($editingLead['converted_user_id'] ?? 0) > 0;
            $editingLeadLockReason = t('admin.student_leads.lock_reason');
            $editingLeadCanConvertNow = student_lead_can_convert_status($editingStatus);
            $editingLeadUrl = page_url('student-leads-manage', [
                'edit' => (int) ($editingLead['id'] ?? 0),
                'lead_page' => $leadPage,
                'lead_per_page' => $leadPerPage,
                'status' => $statusFilter !== '' ? $statusFilter : null,
                'search' => $searchQuery !== '' ? $searchQuery : null,
            ]);
        ?>
        <a id="student-lead-edit-auto-open" href="<?= e($editingLeadUrl); ?>" class="hidden" aria-hidden="true" tabindex="-1"><?= e(t('admin.student_leads.open_modal')); ?></a>
        <article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-hidden="true" data-edit-form-source="1" data-edit-modal-mode="process" data-edit-modal-title="<?= e(t('admin.student_leads.modal_title')); ?>">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="mb-1"><?= e(t('admin.student_leads.process_title', ['id' => (int) ($editingLead['id'] ?? 0), 'name' => (string) ($editingLead['student_name'] ?? '')])); ?></h3>
                    <p class="text-sm text-slate-600"><?= e(t('admin.student_leads.source_label')); ?> <strong><?= e(student_lead_value_or_dash($editingLead['referral_source'] ?? t('admin.student_leads.source_default'))); ?></strong> • <?= e(t('admin.student_leads.created_at_label')); ?> <strong><?= e(student_lead_format_datetime((string) ($editingLead['created_at'] ?? ''))); ?></strong></p>
                </div>
                <span data-process-status-badge="1" class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-extrabold <?= e(student_lead_status_badge_class($editingStatus)); ?>">
                    <?= e($editingStatusLabel); ?>
                </span>
            </div>

            <div class="grid gap-3 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800"><?= e(t('admin.student_leads.student_info')); ?></h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.full_name')); ?>:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['student_name'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.gender')); ?>:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['gender'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.birthdate')); ?>:</dt> <dd class="inline"><?= e(student_lead_format_date((string) ($editingLead['dob'] ?? ''))); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.current_level')); ?>:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['current_level'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.personality')); ?>:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['personality'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800"><?= e(t('admin.student_leads.parent_info')); ?></h4>
                    <dl class="grid gap-1 text-sm text-slate-700">
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.contact')); ?>:</dt> <dd class="inline"><?= e(student_lead_value_or_dash(trim((string) ($editingLead['parent_name'] ?? '') . ' ' . ($editingLead['parent_phone'] ?? '')))); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.school_grade')); ?>:</dt> <dd class="inline"><?= e(student_lead_value_or_dash(trim((string) ($editingLead['school_name'] ?? '') . ' ' . ($editingLead['current_grade'] ?? '')))); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.interests')); ?>:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['interests'] ?? '')); ?></dd></div>
                        <div><dt class="inline font-semibold"><?= e(t('admin.student_leads.study_time')); ?>:</dt> <dd class="inline"><?= e(student_lead_value_or_dash($editingLead['study_time'] ?? '')); ?></dd></div>
                    </dl>
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-white p-3"
                    <?= $editingLeadConverted ? 'data-process-locked-section="1" title="' . e($editingLeadLockReason) . '"' : ''; ?>
                >
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800"><?= e(t('admin.student_leads.update_process')); ?></h4>
                    <form class="grid gap-2" method="post" action="/api/leads/update">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="id" value="<?= (int) ($editingLead['id'] ?? 0); ?>">
                        <fieldset class="grid gap-2" <?= $editingLeadConverted ? 'disabled aria-disabled="true"' : ''; ?>>
                            <label>
                                <?= e(t('admin.student_leads.status_label')); ?>
                                <div class="relative mt-1">
                                    <select
                                        name="status"
                                        required
                                        data-process-status-select="1"
                                        data-is-converted="<?= $editingLeadConverted ? '1' : '0'; ?>"
                                        data-locked-status="official"
                                        <?= $editingLeadConverted ? 'disabled aria-disabled="true"' : ''; ?>
                                    >
                                        <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                                            <?php $isLockedOutStatus = $editingLeadConverted && $statusValue !== 'official'; ?>
                                            <?php $isSelectedStatus = $editingLeadConverted ? $statusValue === 'official' : $editingStatus === $statusValue; ?>
                                            <option value="<?= e($statusValue); ?>" <?= $isSelectedStatus ? 'selected' : ''; ?> <?= $isLockedOutStatus ? 'disabled' : ''; ?>><?= e($statusLabel); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($editingLeadConverted): ?>
                                        <span
                                            class="absolute inset-0 z-10 cursor-not-allowed rounded-xl"
                                            title="<?= e($editingLeadLockReason); ?>"
                                            aria-label="<?= e($editingLeadLockReason); ?>"
                                        ></span>
                                    <?php endif; ?>
                                </div>
                            </label>
                            <label>
                                <?= e(t('admin.student_leads.admin_note')); ?>
                                <textarea name="admin_note" rows="4" placeholder="<?= e(t('admin.student_leads.admin_note_placeholder')); ?>"><?= e((string) ($editingLead['admin_note'] ?? '')); ?></textarea>
                            </label>
                            <div>
                                <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit"><?= e(t('admin.student_leads.save_update')); ?></button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>

            <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <h4 class="mb-2 text-sm font-extrabold text-slate-800"><?= e(t('admin.student_leads.customer_note')); ?></h4>
                    <p class="text-sm leading-relaxed text-slate-700"><?= nl2br(e(student_lead_value_or_dash($editingLead['parent_expectation'] ?? ''))); ?></p>
                </div>

                <?php if ($editingLeadConverted): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                        <p class="font-semibold"><?= e(t('admin.student_leads.converted_notice')); ?></p>
                        <p class="mt-1"><?= e(t('admin.student_leads.account_id')); ?>
                            <button
                                type="button"
                                class="font-bold text-emerald-800 underline"
                                data-admin-row-detail="1"
                                data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $editingLead['converted_user_id']])); ?>"
                            >#<?= (int) $editingLead['converted_user_id']; ?></button>
                        </p>
                        <p class="mt-1 text-xs"><?= e(t('admin.student_leads.converted_at')); ?> <?= e(student_lead_format_datetime((string) ($editingLead['converted_at'] ?? ''))); ?></p>
                    </div>
                <?php elseif ($canConvertLead): ?>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                        <h4 class="mb-2 text-sm font-extrabold text-blue-900"><?= e(t('admin.student_leads.create_student_account')); ?></h4>
                        <form
                            class="grid gap-2 md:grid-cols-2"
                            method="post"
                            action="/api/leads/convert"
                            data-process-convert-form="1"
                            data-valid-statuses="trial_completed,official"
                            data-locked-status="official"
                            data-is-converted="0"
                        >
                            <?= csrf_input(); ?>
                            <input type="hidden" name="id" value="<?= (int) ($editingLead['id'] ?? 0); ?>">
                            <label>
                                <?= e(t('admin.student_leads.username')); ?>
                                <input type="text" name="username" value="<?= e(student_lead_suggested_username($editingLead)); ?>" required>
                            </label>
                            <label>
                                <?= e(t('admin.student_leads.password')); ?>
                                <input type="text" name="password" value="">
                            </label>
                            <label class="md:col-span-2">
                                <?= e(t('admin.student_leads.convert_note')); ?>
                                <textarea name="admin_note" rows="2"><?= e((string) ($editingLead['admin_note'] ?? '')); ?></textarea>
                            </label>
                            <div class="md:col-span-2">
                                <button
                                    class="<?= ui_btn_primary_classes('sm'); ?>"
                                    type="submit"
                                    data-process-convert-button="1"
                                    <?= $editingLeadCanConvertNow ? '' : 'disabled aria-disabled="true" title="' . e(t('admin.student_leads.convert_blocked')) . '"'; ?>
                                ><?= e(t('admin.student_leads.create_user')); ?></button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="mb-1"><?= e(t('admin.student_leads.list_title')); ?></h3>
                <p class="text-sm text-slate-600"><?= e(t('admin.student_leads.list_note')); ?></p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600"><?= e(t('admin.student_leads.total_badge', ['count' => (int) $leadTotal])); ?></span>
        </div>

        <div
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="student-leads-manage"
            data-ajax-page-param="lead_page"
            data-ajax-search-param="search"
        >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex w-full flex-wrap items-center gap-3">
                <input
                    data-ajax-search="1"
                    type="search"
                    value="<?= e($searchQuery); ?>"
                    placeholder="<?= e(t('admin.student_leads.search_placeholder')); ?>"
                    autocomplete="off"
                    class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                <select
                    name="status"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value=""><?= e(t('admin.student_leads.status_all')); ?></option>
                    <?php foreach ($statusOptions as $statusKey => $statusLabel): ?>
                        <option value="<?= e($statusKey); ?>" <?= $statusFilter === $statusKey ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.student_leads.table_id')); ?></th>
                        <th><?= e(t('admin.student_leads.table_student')); ?></th>
                        <th><?= e(t('admin.student_leads.table_goal')); ?></th>
                        <th><?= e(t('admin.student_leads.table_status')); ?></th>
                        <th><?= e(t('admin.student_leads.table_note')); ?></th>
                        <th><?= e(t('admin.student_leads.table_conversion')); ?></th>
                        <th><?= e(t('admin.common.actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.student_leads.empty')); ?></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <?php
                                $statusValue = (string) ($lead['status'] ?? 'new');
                                $statusLabel = (string) ($statusOptions[$statusValue] ?? $statusValue);
                                $leadId = (int) ($lead['id'] ?? 0);
                                $isHighlightedLead = $highlightLeadId > 0 && $highlightLeadId === $leadId;
                            ?>
                            <tr id="student-lead-row-<?= $leadId; ?>" <?= $isHighlightedLead ? 'class="bg-amber-50/80"' : ''; ?>>
                                <td class="font-semibold">#<?= $leadId; ?></td>
                                <td>
                                    <div class="font-bold text-slate-800"><?= e(student_lead_value_or_dash($lead['student_name'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-600"><?= e(t('admin.student_leads.contact_label')); ?> <?= e(student_lead_short_text(trim((string) ($lead['parent_name'] ?? '') . ' ' . ($lead['parent_phone'] ?? '')), 60)); ?></div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-700"><?= e(t('admin.student_leads.level_label')); ?> <?= e(student_lead_value_or_dash($lead['current_level'] ?? '')); ?></div>
                                    <div class="text-xs text-slate-600"><?= e(t('admin.student_leads.time_label')); ?> <?= e(student_lead_short_text($lead['study_time'] ?? '', 35)); ?></div>
                                    <div class="text-xs text-slate-500"><?= e(t('admin.student_leads.interests_label')); ?> <?= e(student_lead_short_text($lead['interests'] ?? '', 35)); ?></div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= e(student_lead_status_badge_class($statusValue)); ?>"><?= e($statusLabel); ?></span>
                                    <div class="mt-1 text-xs text-slate-500"><?= e(t('admin.student_leads.created_at_label')); ?> <?= e(student_lead_format_datetime((string) ($lead['created_at'] ?? ''))); ?></div>
                                </td>
                                <td>
                                    <?php $summaryNote = trim((string) ($lead['admin_note'] ?? '')) !== '' ? (string) ($lead['admin_note'] ?? '') : (string) ($lead['parent_expectation'] ?? ''); ?>
                                    <div class="text-sm text-slate-700" data-full-value="<?= e($summaryNote); ?>"><?= e(student_lead_short_text($summaryNote, 80)); ?></div>
                                </td>
                                <td>
                                    <?php if ((int) ($lead['converted_user_id'] ?? 0) > 0): ?>
                                        <button
                                            type="button"
                                            class="font-semibold text-blue-700 hover:underline"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $lead['converted_user_id'], 'search' => $searchQuery])); ?>"
                                        >User #<?= (int) $lead['converted_user_id']; ?></button>
                                    <?php else: ?>
                                        <span class="text-xs font-semibold text-slate-500"><?= e(t('admin.student_leads.not_converted')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-detail-url="<?= e(page_url('student-leads-manage', ['edit' => (int) $lead['id'], 'lead_page' => $leadPage, 'lead_per_page' => $leadPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.common.view_detail')); ?>"
                                            aria-label="<?= e(t('admin.common.view_detail')); ?>"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.common.view_detail')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </button>
                                        <a
                                            href="<?= e(page_url('student-leads-manage', ['edit' => (int) $lead['id'], 'lead_page' => $leadPage, 'lead_per_page' => $leadPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.student_leads.process_action')); ?>"
                                            aria-label="<?= e(t('admin.student_leads.process_action')); ?>"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.student_leads.process_action')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                        <button
                                            type="button"
                                            class="admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-skip-action-icon="1"
                                            data-lead-prefill-trigger="1"
                                            data-lead-prefill-url="<?= e(student_lead_prefill_link($lead)); ?>"
                                            data-lead-prefill-name="<?= e((string) ($lead['student_name'] ?? '')); ?>"
                                            title="Lấy link form đầy đủ"
                                            aria-label="Lấy link form đầy đủ"
                                        >
                                            <span class="admin-action-icon-label">Form đầy đủ</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M14 3h7v7"></path><path d="M10 14 21 3"></path><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"></path></svg>
                                            </span>
                                        </button>
                                        <?php if ($canDeleteLead): ?>
                                            <form class="inline-block" method="post" action="/api/leads/delete?id=<?= (int) $lead['id']; ?>" onsubmit="return confirm('<?= e(t('admin.student_leads.delete_confirm')); ?>');">
                                                <?= csrf_input(); ?>
                                                <button
                                                    class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                    data-action-kind="delete"
                                                    data-skip-action-icon="1"
                                                    type="submit"
                                                    title="<?= e(t('admin.common.delete')); ?>"
                                                    aria-label="<?= e(t('admin.common.delete')); ?>"
                                                >
                                                    <span class="admin-action-icon-label"><?= e(t('admin.common.delete')); ?></span>
                                                    <span class="admin-action-icon-glyph" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                                                    </span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($leadTotal > 0): ?>
                <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.student_leads.page_info', ['current' => (int) $leadPage, 'total' => (int) $leadTotalPages, 'count' => (int) $leadTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('student-leads-manage')); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="lead-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="lead-per-page" name="lead_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($leadPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $leadPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($leadPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('student-leads-manage', ['lead_page' => $leadPage - 1, 'lead_per_page' => $leadPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($leadPage < $leadTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('student-leads-manage', ['lead_page' => $leadPage + 1, 'lead_per_page' => $leadPerPage, 'status' => $statusFilter, 'search' => $searchQuery])); ?>"><?= e(t('admin.common.next')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </article>

    <div
        id="lead-prefill-modal"
        class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/55 p-4"
        aria-hidden="true"
    >
        <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Link form đầy đủ</h3>
                    <p id="lead-prefill-modal-note" class="mt-1 text-sm text-slate-600">Sao chép link này và gửi cho học viên để hoàn tất form tư vấn dài.</p>
                </div>
                <button type="button" id="lead-prefill-close" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:text-slate-700" aria-label="Đóng">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6 18 18"></path><path d="M18 6 6 18"></path></svg>
                </button>
            </div>
            <label class="block text-xs font-black uppercase tracking-wide text-slate-500" for="lead-prefill-url">Link gửi học viên</label>
            <input id="lead-prefill-url" type="text" readonly class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 outline-none">
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="button" id="lead-prefill-copy" class="<?= ui_btn_primary_classes('sm'); ?>">Sao chép link</button>
                <a id="lead-prefill-open" href="#" target="_blank" rel="noopener noreferrer" class="<?= ui_btn_secondary_classes('sm'); ?>">Mở thử link</a>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('lead-prefill-modal');
    const closeButton = document.getElementById('lead-prefill-close');
    const copyButton = document.getElementById('lead-prefill-copy');
    const openLink = document.getElementById('lead-prefill-open');
    const input = document.getElementById('lead-prefill-url');
    const note = document.getElementById('lead-prefill-modal-note');
    const triggers = Array.from(document.querySelectorAll('[data-lead-prefill-trigger="1"]'));

    if (!modal || !closeButton || !copyButton || !openLink || !input || !note || triggers.length === 0) {
        return;
    }

    const openModal = (url, studentName) => {
        input.value = url;
        openLink.href = url;
        note.textContent = studentName
            ? `Sao chép link này và gửi cho ${studentName} để hoàn tất form tư vấn dài.`
            : 'Sao chép link này và gửi cho học viên để hoàn tất form tư vấn dài.';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        input.focus();
        input.select();
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            openModal(trigger.getAttribute('data-lead-prefill-url') || '', trigger.getAttribute('data-lead-prefill-name') || '');
        });
    });

    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('flex')) {
            closeModal();
        }
    });

    copyButton.addEventListener('click', async () => {
        input.focus();
        input.select();
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }
            if (typeof showNotify === 'function') {
                showNotify('success', 'Đã sao chép link form đầy đủ.');
            }
        } catch (error) {
            if (typeof showNotify === 'function') {
                showNotify('error', 'Không thể sao chép tự động. Link đã được bôi đen, bạn có thể nhấn Ctrl+C.');
            }
        }
    });
})();
</script>
<?php if (is_array($editingLead)): ?>
<script>
window.addEventListener('load', function () {
    const autoOpenLink = document.getElementById('student-lead-edit-auto-open');
    if (!(autoOpenLink instanceof HTMLAnchorElement) || autoOpenLink.dataset.opened === '1') {
        return;
    }

    window.setTimeout(function () {
        if (autoOpenLink.dataset.opened === '1') {
            return;
        }

        if (typeof window.__openAdminEditModal !== 'function') {
            return;
        }

        autoOpenLink.dataset.opened = '1';
        window.__openAdminEditModal(autoOpenLink.href);
    }, 0);
}, { once: true });
</script>
<?php endif; ?>
<?php if ($highlightLeadId > 0): ?>
<script>
window.addEventListener('load', function () {
    const targetRow = document.getElementById('student-lead-row-<?= (int) $highlightLeadId; ?>');
    if (!(targetRow instanceof HTMLElement)) {
        return;
    }

    window.setTimeout(function () {
        targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 180);
}, { once: true });
</script>
<?php endif; ?>
