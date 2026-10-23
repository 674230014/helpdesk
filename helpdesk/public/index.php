<?php
declare(strict_types=1);

// php -S: ปล่อยไฟล์ static (assets) ผ่านตามปกติ
if (PHP_SAPI === 'cli-server') {
    $f = __DIR__ . (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
    if ($f !== __DIR__ . '/' && is_file($f)) {
        return false;
    }
}

[$router, $container] = require dirname(__DIR__) . '/src/bootstrap.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

try {
    boot_observers($container);
    $router->dispatch();
} catch (Throwable $e) {
    \App\Core\Logger::error($e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    \App\Core\Response::abort(500, \App\Core\Env::get('APP_DEBUG') === 'true' ? $e->getMessage() : 'เกิดข้อผิดพลาดภายในระบบ');
}
