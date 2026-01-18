<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Models\CachedCompany;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class EloquentCompanyRepository implements CompanyRepositoryInterface
{
    public function findByCompanyId(string $companyId, CountryCode $countryCode): ?CompanyDto
    {
        $cached = CachedCompany::query()
            ->current()
            ->forCompanyId($companyId)
            ->forCountry($countryCode)
            ->first();

        return $cached?->toDto();
    }

    public function hasFreshCache(string $companyId, CountryCode $countryCode, int $ttlHours = 24): bool
    {
        $cached = CachedCompany::query()
            ->current()
            ->forCompanyId($companyId)
            ->forCountry($countryCode)
            ->first();

        return $cached?->isFresh($ttlHours) ?? false;
    }

    public function store(CompanyDto $company, ?array $rawResponse = null): void
    {
        DB::transaction(function () use ($company, $rawResponse) {
            $current = CachedCompany::query()
                ->current()
                ->forCompanyId($company->id)
                ->forCountry($company->countryCode)
                ->first();

            $newVersion = 1;
            if ($current !== null) {
                $newVersion = $current->version + 1;
                $current->updateQuietly(['is_current' => false]);
            }

            $newRecord = CachedCompany::fromDto($company, $rawResponse);
            $newRecord->version = $newVersion;
            $newRecord->is_current = true;
            $newRecord->save();
        });
    }

    public function getHistory(string $companyId, CountryCode $countryCode): array
    {
        return CachedCompany::query()
            ->forCompanyId($companyId)
            ->forCountry($countryCode)
            ->orderByDesc('version')
            ->get()
            ->map(fn (CachedCompany $cached) => $cached->toDto())
            ->all();
    }
}
