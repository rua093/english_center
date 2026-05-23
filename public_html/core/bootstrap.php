<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
require_once __DIR__ . '/filesystem.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/failover.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/bbcode.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/db_helper.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/api_helpers.php';
require_once __DIR__ . '/file_storage.php';
require_once __DIR__ . '/get_version.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/page_routes.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/page_actions.php';

function app_maintenance_bypass_script_paths(): array
{
    return [
        BASE_PATH . '/api/admin-sync-ops.php',
        BASE_PATH . '/api/export-sandbox.php',
        BASE_PATH . '/api/import-fallback.php',
        BASE_PATH . '/api/sync-backup.php',
    ];
}

function app_is_maintenance_bypass_request(): bool
{
    if (PHP_SAPI === 'cli') {
        return true;
    }

    $scriptFilename = trim((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    if ($scriptFilename === '') {
        return false;
    }

    $realScriptFilename = realpath($scriptFilename);
    if ($realScriptFilename === false) {
        return false;
    }

    foreach (app_maintenance_bypass_script_paths() as $allowedPath) {
        $realAllowedPath = realpath($allowedPath);
        if ($realAllowedPath !== false && $realAllowedPath === $realScriptFilename) {
            return true;
        }
    }

    return false;
}

function app_render_maintenance_page(): never
{
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    header('Retry-After: 120');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Đang bảo trì</title>';
    echo '<style>';
    echo ':root{color-scheme:light;}';
    echo '*{box-sizing:border-box}';
    echo 'body{margin:0;font-family:"Plus Jakarta Sans","Segoe UI",Arial,sans-serif;min-height:100vh;color:#e5eef8;';
    echo 'background:radial-gradient(circle at top left,rgba(34,197,94,.24),transparent 28%),';
    echo 'radial-gradient(circle at top right,rgba(250,204,21,.18),transparent 24%),';
    echo 'radial-gradient(circle at bottom left,rgba(14,165,233,.18),transparent 24%),';
    echo 'linear-gradient(140deg,#06101e 0%,#10253f 44%,#0b3550 100%);';
    echo 'display:flex;align-items:center;justify-content:center;padding:28px;overflow:hidden;position:relative}';
    echo 'body::before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:32px 32px;mask-image:linear-gradient(180deg,rgba(0,0,0,.7),transparent)}';
    echo '.orb{position:absolute;border-radius:999px;filter:blur(12px);opacity:.58;animation:float 9s ease-in-out infinite alternate}';
    echo '.orb-a{width:220px;height:220px;background:linear-gradient(135deg,#22c55e,#14b8a6);top:8%;left:6%}';
    echo '.orb-b{width:280px;height:280px;background:linear-gradient(135deg,#60a5fa,#2563eb);right:8%;top:16%;animation-delay:1.4s}';
    echo '.orb-c{width:200px;height:200px;background:linear-gradient(135deg,#38bdf8,#0ea5e9);left:18%;bottom:10%;animation-delay:2.3s}';
    echo '.shell{position:relative;z-index:2;width:min(100%,1020px);display:grid;gap:22px;grid-template-columns:1.12fr .88fr;align-items:stretch}';
    echo '.panel{background:rgba(6,17,31,.72);border:1px solid rgba(148,163,184,.18);backdrop-filter:blur(18px);border-radius:30px;box-shadow:0 30px 80px rgba(2,8,23,.34)}';
    echo '.hero{padding:40px 36px;position:relative;overflow:hidden}';
    echo '.hero::before{content:"";position:absolute;inset:auto auto 22px 24px;width:120px;height:120px;border-radius:26px;background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,0));transform:rotate(16deg)}';
    echo '.hero::after{content:"";position:absolute;inset:auto -20% -30% auto;width:280px;height:280px;border-radius:999px;background:radial-gradient(circle,rgba(34,197,94,.32),transparent 68%)}';
    echo '.badge{display:inline-flex;align-items:center;gap:10px;padding:10px 16px;border-radius:999px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);font-size:12px;font-weight:800;letter-spacing:.22em;text-transform:uppercase;color:#d7ffe6}';
    echo '.pulse{width:10px;height:10px;border-radius:999px;background:#4ade80;box-shadow:0 0 0 0 rgba(74,222,128,.7);animation:pulse 1.8s infinite}';
    echo 'h1{margin:18px 0 14px;font-size:clamp(32px,4vw,58px);line-height:1.03;font-weight:900;letter-spacing:-.045em;color:#fff;max-width:11ch}';
    echo '.lead{margin:0;max-width:36rem;font-size:17px;line-height:1.85;color:rgba(226,232,240,.9)}';
    echo '.accent{color:#86efac}';
    echo '.meta{display:flex;flex-wrap:wrap;gap:12px;margin-top:24px}';
    echo '.chip{padding:12px 14px;border-radius:18px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#dbe7f4;font-size:14px;line-height:1.5;min-width:160px}';
    echo '.chip strong{display:block;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#9dd7ff;margin-bottom:6px}';
    echo '.info{padding:26px;display:grid;gap:16px;align-content:center;position:relative;overflow:hidden}';
    echo '.info::after{content:"";position:absolute;right:-34px;bottom:-40px;width:180px;height:180px;border-radius:999px;background:radial-gradient(circle,rgba(96,165,250,.24),transparent 70%)}';
    echo '.card{padding:18px 18px 16px;border-radius:22px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1)}';
    echo '.card strong{display:block;font-size:14px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#c7d7eb;margin-bottom:8px}';
    echo '.card span{display:block;font-size:15px;line-height:1.7;color:#e7eef7}';
    echo '.footer-note{padding:16px 4px 0;font-size:13px;color:rgba(191,219,254,.72)}';
    echo '.mini-wave{display:flex;gap:6px;align-items:flex-end;height:28px;margin-top:4px}';
    echo '.mini-wave span{display:block;width:7px;border-radius:999px;background:linear-gradient(180deg,#93c5fd,#22d3ee);animation:wave 1.2s ease-in-out infinite}';
    echo '.mini-wave span:nth-child(1){height:11px}.mini-wave span:nth-child(2){height:24px;animation-delay:.12s}.mini-wave span:nth-child(3){height:15px;animation-delay:.24s}.mini-wave span:nth-child(4){height:22px;animation-delay:.36s}.mini-wave span:nth-child(5){height:13px;animation-delay:.48s}';
    echo '@keyframes float{0%{transform:translateY(0) translateX(0) scale(1)}100%{transform:translateY(-18px) translateX(10px) scale(1.06)}}';
    echo '@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(74,222,128,.72)}70%{box-shadow:0 0 0 18px rgba(74,222,128,0)}100%{box-shadow:0 0 0 0 rgba(74,222,128,0)}}';
    echo '@keyframes wave{0%,100%{transform:scaleY(.75);opacity:.8}50%{transform:scaleY(1.15);opacity:1}}';
    echo '@media (max-width:860px){.shell{grid-template-columns:1fr}.hero,.info{padding:24px}.lead{font-size:16px}.panel{border-radius:24px}.meta{display:grid;grid-template-columns:1fr}}';
    echo '</style></head><body>';
    echo '<div class="orb orb-a"></div><div class="orb orb-b"></div><div class="orb orb-c"></div>';
    echo '<main class="shell">';
    echo '<section class="panel hero">';
    echo '<div class="badge"><span class="pulse"></span>Maintenance Mode</div>';
    echo '<h1>Hệ thống đang được bảo trì</h1>';
    echo '<p class="lead">Chúng tôi đang tạm dừng trong giây lát để đồng bộ dữ liệu và tối ưu trải nghiệm. Vui lòng <span class="accent">quay lại sau vài phút</span>.</p>';
    echo '<div class="meta">';
    echo '<div class="chip"><strong>Trạng thái</strong>Đồng bộ đang diễn ra an toàn trên hệ thống.</div>';
    echo '<div class="chip"><strong>Tiến trình</strong>Chúng tôi sẽ mở lại ngay khi mọi thứ sẵn sàng.</div>';
    echo '</div>';
    echo '</section>';
    echo '<aside class="panel info">';
    echo '<div class="card"><strong>Đang xử lý</strong><span>Dữ liệu đang được đồng bộ để hệ thống hoạt động ổn định và nhất quán hơn khi quay lại.</span></div>';
    echo '<div class="card"><strong>Trong lúc chờ</strong><span>Bạn chỉ cần quay lại sau ít phút. Mọi thứ sẽ sẵn sàng trở lại ngay khi hoàn tất.</span><div class="mini-wave"><span></span><span></span><span></span><span></span><span></span></div></div>';
    echo '<div class="footer-note">Cảm ơn bạn đã kiên nhẫn chờ đợi.</div>';
    echo '</aside>';
    echo '</main></body></html>';
    exit;
}

function app_request_allows_maintenance_bypass(): bool
{
    if (app_is_maintenance_bypass_request()) {
        return true;
    }

    $page = strtolower(trim((string) ($_GET['page'] ?? '')));
    if (in_array($page, ['login', 'forgot-password'], true)) {
        return true;
    }

    $resource = strtolower(trim((string) ($_GET['resource'] ?? '')));
    $method = strtolower(trim((string) ($_GET['method'] ?? '')));
    if ($resource === 'auth' && $method === 'login') {
        return true;
    }

    $legacyAction = strtolower(trim((string) ($_GET['action'] ?? '')));
    if ($legacyAction === 'do-login') {
        return true;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }

    $user = $_SESSION['auth_user'] ?? null;
    $role = strtolower(trim((string) ($user['role'] ?? '')));
    return in_array($role, ['admin', 'staff'], true);
}

if (app_is_maintenance_mode_enabled() && !app_request_allows_maintenance_bypass()) {
    app_render_maintenance_page();
}

i18n_bootstrap();
sync_auth_permissions();
sync_change_log_bootstrap_if_needed();
