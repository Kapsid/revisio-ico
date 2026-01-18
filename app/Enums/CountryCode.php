<?php

declare(strict_types=1);

namespace App\Enums;

enum CountryCode: string
{
    case CZ = 'cz';
    case SK = 'sk';
    case PL = 'pl';

    public function label(): string
    {
        return match ($this) {
            self::CZ => 'Czech Republic',
            self::SK => 'Slovakia',
            self::PL => 'Poland',
        };
    }

    public function vatPrefix(): string
    {
        return match ($this) {
            self::CZ => 'CZ',
            self::SK => 'SK',
            self::PL => 'PL',
        };
    }

    public function validateCompanyId(string $companyId): bool
    {
        return match ($this) {
            self::CZ => (bool) preg_match('/^\d{8}$/', $companyId),
            self::SK => (bool) preg_match('/^\d{8}$/', $companyId),
            self::PL => (bool) preg_match('/^\d{9,14}$/', $companyId),
        };
    }

    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom(strtolower($value));
    }
}
