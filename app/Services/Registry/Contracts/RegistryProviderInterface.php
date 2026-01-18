<?php

declare(strict_types=1);

namespace App\Services\Registry\Contracts;

use App\DTO\CompanyDto;
use App\Enums\CountryCode;

interface RegistryProviderInterface
{
    public function getCountryCode(): CountryCode;

    public function fetchCompany(string $companyId): CompanyDto;

    public function canHandle(string $companyId): bool;
}
