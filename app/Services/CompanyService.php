<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\InvalidCountryCodeException;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Services\Registry\RegistryProviderFactory;
use Illuminate\Support\Facades\Log;

final readonly class CompanyService
{
    public function __construct(
        private CompanyRepositoryInterface $repository,
        private RegistryProviderFactory $providerFactory,
    ) {}

    public function getCompanyInfo(string $countryCode, string $companyId): CompanyDto
    {
        $country = CountryCode::tryFromString($countryCode);

        if ($country === null) {
            throw new InvalidCountryCodeException($countryCode);
        }

        $ttlHours = (int) config('registry.cache_ttl_hours', 24);

        if ($this->repository->hasFreshCache($companyId, $country, $ttlHours)) {
            Log::debug('Cache hit', ['companyId' => $companyId, 'country' => $country->value]);
            return $this->repository->findByCompanyId($companyId, $country);
        }

        Log::debug('Cache miss', ['companyId' => $companyId, 'country' => $country->value]);

        $provider = $this->providerFactory->make($country);
        $companyData = $provider->fetchCompany($companyId);

        $this->repository->store($companyData);

        return $companyData;
    }
}
