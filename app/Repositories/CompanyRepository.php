<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Models\CachedCompany;
use App\Repositories\Contracts\CompanyRepositoryInterface;

final class CompanyRepository implements CompanyRepositoryInterface
{
    public function findByCompanyId(string $companyId, CountryCode $countryCode): ?CompanyDto
    {
        $cached = CachedCompany::query()
            ->forCompanyId($companyId)
            ->forCountry($countryCode)
            ->first();

        return $cached?->toDto();
    }

    public function hasFreshCache(string $companyId, CountryCode $countryCode, int $ttlHours = 24): bool
    {
        $cached = CachedCompany::query()
            ->forCompanyId($companyId)
            ->forCountry($countryCode)
            ->first();

        return $cached?->isFresh($ttlHours) ?? false;
    }

    public function store(CompanyDto $company, ?array $rawResponse = null): void
    {
        CachedCompany::updateOrCreate(
            [
                'company_id' => $company->id,
                'country_code' => $company->countryCode->value,
            ],
            [
                'name' => $company->name,
                'vat_id' => $company->vatId,
                'vat_payer' => $company->vatPayer,
                'address_street' => $company->address?->street,
                'address_house_number' => $company->address?->houseNumber,
                'address_orientation_number' => $company->address?->orientationNumber,
                'address_zip' => $company->address?->zip,
                'address_city' => $company->address?->city,
                'raw_response' => $rawResponse,
                'fetched_at' => now(),
            ]
        );
    }
}
