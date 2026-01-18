<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTO\CompanyDto;
use App\Enums\CountryCode;

interface CompanyRepositoryInterface
{
    public function findByCompanyId(string $companyId, CountryCode $countryCode): ?CompanyDto;

    public function hasFreshCache(string $companyId, CountryCode $countryCode, int $ttlHours = 24): bool;

    public function store(CompanyDto $company, ?array $rawResponse = null): void;
}
