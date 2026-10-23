<?php
declare(strict_types=1);

/** php bin/install.php — สร้างตารางและข้อมูลตัวอย่าง */

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$schema = BASE_PATH . '/database/schema.' . $db->driver() . '.sql';
$db->pdo()->exec(file_get_contents($schema));
echo "✔ สร้างตารางเรียบร้อย ({$db->driver()})\n";

if ((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
    echo "ℹ มีข้อมูลผู้ใช้อยู่แล้ว ข้ามการ seed\n";
    exit(0);
}

$now = date('Y-m-d H:i:s');
$users = [
    ['ผู้ดูแลระบบ', 'admin@example.com', 'admin1234', 'admin'],
    ['สมชาย ช่างเทคนิค', 'tech1@example.com', 'tech1234', 'technician'],
    ['สมหญิง ช่างเครือข่าย', 'tech2@example.com', 'tech1234', 'technician'],
    ['วิชัย พนักงาน', 'user1@example.com', 'user1234', 'user'],
];
foreach ($users as [$name, $email, $pw, $role]) {
    $db->query(
        'INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?,?,?,?,?)',
        [$name, $email, password_hash($pw, PASSWORD_DEFAULT), $role, $now]
    );
}

foreach ([
    ['คอมพิวเตอร์/โน้ตบุ๊ก', 'เครื่องเปิดไม่ติด ช้า จอเสีย'],
    ['เครือข่าย/Wi-Fi', 'อินเทอร์เน็ตใช้ไม่ได้ สัญญาณหลุด'],
    ['เครื่องพิมพ์', 'ปริ้นไม่ออก กระดาษติด'],
    ['ซอฟต์แวร์/บัญชีผู้ใช้', 'ติดตั้งโปรแกรม ลืมรหัสผ่าน'],
] as [$n, $d]) {
    $db->query('INSERT INTO categories (name, description) VALUES (?,?)', [$n, $d]);
}

$db->query(
    "INSERT INTO tickets (user_id, category_id, title, description, status, priority, created_at, updated_at)
     VALUES (4, 3, 'เครื่องพิมพ์ชั้น 2 ปริ้นไม่ออก', 'สั่งพิมพ์แล้วขึ้นสถานะ offline ตั้งแต่เช้า', 'Open', 'high', ?, ?)",
    [$now, $now]
);
$db->query("INSERT INTO status_logs (ticket_id, changed_by, from_status, to_status, note, created_at) VALUES (1, 4, NULL, 'Open', 'สร้าง Ticket', ?)", [$now]);

echo "✔ seed เรียบร้อย\n\nบัญชีทดลอง:\n";
foreach ($users as [$name, $email, $pw, $role]) {
    printf("  %-11s %-20s %s\n", $role, $email, $pw);
}
