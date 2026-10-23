<?php
declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    /** rules: ['title' => 'required|max:150', 'email' => 'required|email', 'score' => 'int|between:1,5'] */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleStr) {
            $val = trim((string) ($data[$field] ?? ''));
            foreach (explode('|', $ruleStr) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                if ($name !== 'required' && $val === '') {
                    continue;
                }
                $error = match ($name) {
                    'required' => $val === '' ? "กรุณากรอก $field" : null,
                    'max' => mb_strlen($val) > (int) $arg ? "$field ยาวเกิน $arg ตัวอักษร" : null,
                    'min' => mb_strlen($val) < (int) $arg ? "$field ต้องมีอย่างน้อย $arg ตัวอักษร" : null,
                    'email' => filter_var($val, FILTER_VALIDATE_EMAIL) ? null : "$field ไม่ใช่อีเมลที่ถูกต้อง",
                    'int' => filter_var($val, FILTER_VALIDATE_INT) === false ? "$field ต้องเป็นตัวเลข" : null,
                    'between' => $this->between($val, (string) $arg, $field),
                    'in' => in_array($val, explode(',', (string) $arg), true) ? null : "$field ไม่ถูกต้อง",
                    default => null,
                };
                if ($error) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
        }
        return $this->errors === [];
    }

    private function between(string $val, string $arg, string $field): ?string
    {
        [$lo, $hi] = array_map('intval', explode(',', $arg));
        $n = (int) $val;
        return ($n < $lo || $n > $hi) ? "$field ต้องอยู่ระหว่าง $lo-$hi" : null;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
