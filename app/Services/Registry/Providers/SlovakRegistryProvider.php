<?php

declare(strict_types=1);

namespace App\Services\Registry\Providers;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\CompanyNotFoundException;
use App\Exceptions\RegistryException;
use App\Services\Registry\Providers\RegistryProviderInterface;
use lubosdz\parserOrsr\ConnectorOrsr;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SlovakRegistryProvider implements RegistryProviderInterface
{
    private ConnectorOrsr $orsr;

    public function __construct()
    {
        $this->orsr = new ConnectorOrsr();
    }

    public function getCountryCode(): CountryCode
    {
        return CountryCode::SK;
    }

    public function canHandle(string $companyId): bool
    {
        return CountryCode::SK->validateCompanyId($companyId);
    }

    public function fetchCompany(string $companyId): CompanyDto
    {
        $companyId = $this->normalizeCompanyId($companyId);

        try {
            $data = $this->orsr->getDetailByICO($companyId);

            if (empty($data) || empty($data['obchodne_meno'])) {
                throw new CompanyNotFoundException($companyId, CountryCode::SK);
            }

            return $this->mapToDto($data, $companyId);

        } catch (CompanyNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Slovak registry error', [
                'companyId' => $companyId,
                'error' => $e->getMessage(),
            ]);
            throw RegistryException::fromException(CountryCode::SK, $e);
        }
    }

    private function normalizeCompanyId(string $companyId): string
    {
        $companyId = preg_replace('/\s+/', '', $companyId);
        return str_pad($companyId, 8, '0', STR_PAD_LEFT);
    }

    private function mapToDto(array $data, string $companyId): CompanyDto
    {
        return new CompanyDto(
            name: $data['obchodne_meno'] ?? 'Unknown',
            id: $companyId,
            countryCode: CountryCode::SK,
            vatId: null,
            vatPayer: null,
            address: $this->extractAddress($data),
        );
    }

    private function extractAddress(array $data): ?AddressDto
    {
        $address = $data['adresa'] ?? null;

        if ($address === null || (empty($address['city']) && empty($address['street']))) {
            return null;
        }

        return new AddressDto(
            street: $address['street'] ?? null,
            houseNumber: $address['number'] ?? null,
            zip: isset($address['zip']) ? (int) preg_replace('/\D/', '', $address['zip']) : null,
            city: $address['city'] ?? null,
        );
    }
}
