<?php
declare(strict_types=1);

namespace App\Services;

/** อัปโหลดรูปภาพ: ตรวจ MIME จริง, ขนาด, สุ่มชื่อไฟล์, เก็บนอก DocumentRoot */
final class FileUploader
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    /** @return string|null ชื่อไฟล์ที่เก็บ, null ถ้าไม่ได้แนบไฟล์ */
    public function upload(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ (code ' . $file['error'] . ')');
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('ไฟล์ใหญ่เกิน 5 MB');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime])) {
            throw new \RuntimeException('อนุญาตเฉพาะไฟล์รูป JPG, PNG, WEBP, GIF');
        }
        $name = bin2hex(random_bytes(16)) . '.' . self::ALLOWED[$mime];
        $dir = BASE_PATH . '/storage/uploads/ticket-images';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        if (!move_uploaded_file($file['tmp_name'], "$dir/$name") && !rename($file['tmp_name'], "$dir/$name")) {
            throw new \RuntimeException('บันทึกไฟล์ไม่สำเร็จ');
        }
        return $name;
    }
}
