<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TicketController;
use App\Controllers\UploadController;
use App\Core\Container;
use App\Core\Database;
use App\Core\Env;
use App\Core\EventDispatcher;
use App\Core\Router;
use App\Notifications\LineMessagingService;
use App\Notifications\NotificationChannelInterface;
use App\Observers\TicketObserver;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
require BASE_PATH . '/src/Core/helpers.php';

Env::load(BASE_PATH . '/.env');
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Bangkok'));

if (Env::get('APP_DEBUG', 'false') === 'true') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// ---- DI wiring ----
$container = new Container();
$container->bind(Database::class, fn () => Database::getInstance());
$container->bind(NotificationChannelInterface::class, fn () => new LineMessagingService());
$container->bind(EventDispatcher::class, fn () => new EventDispatcher());

/** สร้าง Observer ตั้งแต่ boot เพื่อให้ลงทะเบียน listener กับ EventDispatcher */
function boot_observers(Container $c): void
{
    $c->get(TicketObserver::class);
}

// ---- Routes ----
$router = new Router($container);

$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], ['csrf']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);

$router->get('/tickets', [TicketController::class, 'index'], ['auth']);
$router->get('/tickets/create', [TicketController::class, 'create'], ['auth', 'role:user,admin']);
$router->post('/tickets', [TicketController::class, 'store'], ['auth', 'role:user,admin', 'csrf']);
$router->get('/tickets/{id}', [TicketController::class, 'show'], ['auth']);
$router->patch('/api/tickets/{id}/status', [TicketController::class, 'updateStatus'], ['auth', 'csrf']);
$router->post('/api/tickets/{id}/comments', [TicketController::class, 'addComment'], ['auth', 'csrf']);

$router->get('/uploads/{file}', [UploadController::class, 'show'], ['auth']);

$router->get('/admin/dashboard', [DashboardController::class, 'index'], ['auth', 'role:admin']);
$router->get('/admin/users', [AdminController::class, 'users'], ['auth', 'role:admin']);
$router->post('/admin/users', [AdminController::class, 'storeUser'], ['auth', 'role:admin', 'csrf']);
$router->get('/admin/categories', [AdminController::class, 'categories'], ['auth', 'role:admin']);
$router->post('/admin/categories', [AdminController::class, 'storeCategory'], ['auth', 'role:admin', 'csrf']);

return [$router, $container];
