<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_rooms_save_action(): void
{
    api_require_post(page_url('rooms-manage'));

    $roomId = input_int($_POST, 'id');
    api_guard_permission($roomId > 0 ? 'academic.rooms.update' : 'academic.rooms.create');

    $roomName = trim((string) ($_POST['room_name'] ?? ''));
    $redirectQuery = [];
    if ($roomId > 0) {
        $redirectQuery['edit'] = $roomId;
    }

    if ($roomName === '') {
        set_flash('error', 'Vui long nhap ten phong hoc.');
        redirect(page_url('rooms-manage', $redirectQuery));
    }

    try {
        (new AcademicModel())->saveRoom([
            'id' => $roomId,
            'room_name' => $roomName,
        ]);
    } catch (Throwable $exception) {
        set_flash('error', 'Khong the luu phong hoc. Vui long thu lai.');
        redirect(page_url('rooms-manage', $redirectQuery));
    }

    set_flash('success', 'Da luu phong hoc thanh cong.');
    redirect(page_url('rooms-manage'));
}

function api_rooms_edit_action(): void
{
    api_guard_permission('academic.rooms.update');
    redirect(page_url('rooms-manage', ['edit' => (int) ($_GET['id'] ?? 0)]));
}

function api_rooms_delete_action(): void
{
    api_guard_permission('academic.rooms.delete');
    api_require_post(page_url('rooms-manage'));

    $roomId = (int) ($_GET['id'] ?? 0);
    if ($roomId <= 0) {
        set_flash('error', 'Phong hoc khong hop le.');
        redirect(page_url('rooms-manage'));
    }

    try {
        (new AcademicModel())->deleteRoom($roomId);
        set_flash('success', 'Da chuyen phong hoc vao trang thai xoa mem.');
    } catch (Throwable $exception) {
        set_flash('error', 'Khong the xoa phong hoc dang duoc su dung trong lich day.');
    }

    redirect(page_url('rooms-manage'));
}
