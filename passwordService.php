<?php
require_once __DIR__ . '/env.php';

class PasswordService
{
    private int $prefix;

    public function __construct()
    {
        $this->prefix = (int) ($_ENV['PASSWORD_PREFIX'] ?? 0);
    }

    public function generate(): int
    {
        // dia e mês concatenados (ex: 11 e 12 → "1112" → 1112)
        $dateNumber = (int) (date('d') . date('m'));
        return $this->prefix + $dateNumber;
    }

    public function validate(string $input): bool
    {
        return hash_equals('ws'.$this->generate(), $input);
    }
}
