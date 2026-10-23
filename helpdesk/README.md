# Helpdesk Ticket System

เว็บแอปแจ้งซ่อม/Helpdesk ที่แปลงมาจากไดอะแกรมทั้ง 9 รูป — PHP Custom MVC + Repository/Service/Observer, Tailwind CSS, Vanilla JS (Fetch API)

## เริ่มใช้งานเร็ว (SQLite ไม่ต้องตั้งค่าฐานข้อมูล)

```bash
cp .env.example .env
php bin/install.php          # สร้างตาราง + ข้อมูลตัวอย่าง
php -S 127.0.0.1:8000 -t public public/index.php
```

เปิด http://127.0.0.1:8000 (หน้าเว็บโหลด Tailwind จาก CDN จึงต้องต่ออินเทอร์เน็ต)

| Role | อีเมล | รหัสผ่าน |
|---|---|---|
| Admin | admin@example.com | admin1234 |
| Technician | tech1@example.com / tech2@example.com | tech1234 |
| User | user1@example.com | user1234 |

> เปลี่ยนรหัสผ่านทั้งหมดก่อนนำไปใช้จริง

## ลองวงจรงาน
1. **User** แจ้งปัญหาใหม่ (แนบรูปได้) → สถานะ `Open`
2. **Admin** เลือกช่าง → `Assigned`
3. **Technician** ใส่ comment ตอนรับงาน → `InProgress`
4. **Technician** แนบรูปผลซ่อม → `Resolved`
5. **User** ให้คะแนน → `Closed` หรือระบุเหตุผลส่งกลับ → `InProgress`
6. **Admin** ดู Dashboard สถิติ

## ไดอะแกรม → โค้ด

| ไดอะแกรม | อยู่ที่ |
|---|---|
| 1 Architecture | `public/index.php` → `Router` → `Middleware` → `Controllers` → `Services` → `Repositories` |
| 2 ER | `database/schema.sqlite.sql`, `database/schema.mysql.sql` (6 ตาราง) |
| 3 State Machine | `src/Enums/TicketStatus.php` + `src/Services/TicketStatusService.php` |
| 4 Use Case (RBAC) | `Middleware` (`auth`, `role:`, `csrf`) + `bootstrap.php` (routes) |
| 5 Class | `src/` ทั้งหมด (Singleton `Database`, Repository, Service, Observer, Strategy, DI `Container`) |
| 6 Sequence | `TicketController::store / updateStatus`, `DashboardController::index` |
| 7 Activity | `TicketService` + `TicketStatusService::transition()` |
| 8 Component | โครงโฟลเดอร์ `src/ views/ storage/ public/` |
| 9 Deployment | ด้านล่าง |

## ใช้ MySQL 8.0
แก้ `.env`: `DB_DRIVER=mysql` พร้อม `DB_HOST/DB_NAME/DB_USER/DB_PASS` แล้วรัน `php bin/install.php`

## LINE Messaging API
ใส่ `LINE_CHANNEL_TOKEN` ใน `.env` และใส่ LINE userId ของแต่ละคนในหน้า Admin → ผู้ใช้
ถ้าไม่ตั้งค่า ระบบจะบันทึกข้อความลง `storage/logs/app.log` แทน (dry-run) ความล้มเหลวของ LINE จะไม่ทำให้การเปลี่ยนสถานะล้ม

## Deploy (Apache/Nginx + PHP-FPM)
- ชี้ DocumentRoot ไปที่ `public/` เท่านั้น (ใช้ `public/.htaccess` สำหรับ Apache; Nginx ใช้ `try_files $uri /index.php?$query_string`)
- `storage/` และ `.env` อยู่นอก DocumentRoot — รูปที่อัปโหลดเสิร์ฟผ่าน `/uploads/{file}` ที่ตรวจสิทธิ์ก่อน
- ตั้ง `APP_DEBUG=false`, `chmod 600 .env`, ให้ PHP เขียน `storage/` ได้
- ใช้ HTTPS และให้ MySQL อยู่ใน Private Network

## หมายเหตุการออกแบบ
- เปลี่ยนสถานะเรียกเป็น `PATCH /api/tickets/{id}/status` โดยส่ง `POST` + header `X-HTTP-Method-Override: PATCH` (PHP ไม่ parse multipart ของ PATCH ซึ่งจำเป็นต่อการอัปโหลดรูป)
- ทุก request ที่แก้ข้อมูลต้องมี CSRF token (header `X-CSRF-Token` หรือฟิลด์ `_csrf`)
- ต้องใช้ PHP ≥ 8.2 พร้อม `pdo_sqlite` หรือ `pdo_mysql`, `curl`, `fileinfo`, `mbstring`
