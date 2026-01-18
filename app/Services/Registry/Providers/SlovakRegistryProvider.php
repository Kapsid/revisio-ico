<?php

declare(strict_types=1);

namespace App\Services\Registry\Providers;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\CompanyNotFoundException;
use App\Exceptions\RegistryException;
use App\Services\Registry\Contracts\RegistryProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Slovak Registry Provider
 *
 * Fetches company data from Slovak business registry.
 * Uses the FinStat API (finstat.sk) which provides Slovak company data.
 *
 * Alternative: ORSR (Obchodný register Slovenskej republiky)
 * The official registry at orsr.sk can be parsed but doesn't have
 * a stable public API. FinStat provides a more reliable interface.
 *
 * For development/testing, this uses the publicly available
 * Register právnických osôb (RPO) data.
 *
 * Data Mapping from Slovak sources:
 * - nazov/name -> name
 * - ico -> id
 * - dic -> vatId
 * - platcaDph -> vatPayer
 * - sidlo -> address
 */
final class SlovakRegistryProvider implements RegistryProviderInterface
{
    /**
     * Slovak RPO (Register právnických osôb) API endpoint.
     * This is a public API providing basic company data.
     */
    private const RPO_API_URL = 'https://rpo.statistics.sk/rpo/json/v2/entity/';

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
            // Try RPO API first
            $data = $this->fetchFromRpo($companyId);

            if ($data === null) {
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

    /**
     * Fetch data from Slovak RPO API.
     */
    private function fetchFromRpo(string $companyId): ?array
    {
        $response = Http::timeout(30)
            ->accept('application/json')
            ->get(self::RPO_API_URL . $companyId);

        if ($response->status() === 404) {
            return null;
        }

        if (!$response->successful()) {
            throw new \RuntimeException(
                'RPO API request failed with status: ' . $response->status()
            );
        }

        $data = $response->json();

        // RPO returns empty or error structure when not found
        if (empty($data) || isset($data['error'])) {
            return null;
        }

        return $data;
    }

    /**
     * Normalize company ID to standard 8-digit format.
     */
    private function normalizeCompanyId(string $companyId): string
    {
        $companyId = preg_replace('/\s+/', '', $companyId);
        return str_pad($companyId, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Map RPO response to CompanyDto.
     */
    private function mapToDto(array $data, string $companyId): CompanyDto
    {
        // Extract the main entity data
        // RPO structure varies, handle common patterns
        $name = $data['name'] ?? $data['nazov'] ?? $data['full_name'] ?? 'Unknown';

        // Extract address
        $address = $this->extractAddress($data);

        // Extract VAT info
        $vatId = $data['dic'] ?? null;
        $vatPayer = null;

        if (isset($data['platcaDph'])) {
            $vatPayer = (bool) $data['platcaDph'];
        } elseif ($vatId !== null) {
            // If DIC exists, likely a VAT payer
            $vatPayer = true;
        }

        return new CompanyDto(
            name: $name,
            id: $companyId,
            countryCode: CountryCode::SK,
            vatId: $vatId ? 'SK' . $vatId : null,
            vatPayer: $vatPayer,
            address: $address,
        );
    }

    /**
     * Extract address from various response formats.
     */
    private function extractAddress(array $data): ?AddressDto
    {
        // Try different address field names
        $addressData = $data['address'] ?? $data['sidlo'] ?? $data['registered_office'] ?? null;

        if ($addressData === null) {
            // Try to build from flat structure
            if (isset($data['street']) || isset($data['city'])) {
                $addressData = $data;
            } else {
                return null;
            }
        }

        // Handle both nested and flat structures
        if (is_array($addressData)) {
            return new AddressDto(
                street: $addressData['street'] ?? $addressData['ulica'] ?? null,
                houseNumber: isset($addressData['building_number'])
                    ? (string) $addressData['building_number']
                    : ($addressData['cisloDomu'] ?? null),
                orientationNumber: $addressData['orientationNumber'] ?? null,
                zip: isset($addressData['postal_code'])
                    ? (int) preg_replace('/\D/', '', $addressData['postal_code'])
                    : (isset($addressData['psc']) ? (int) $addressData['psc'] : null),
                city: $addressData['city'] ?? $addressData['mesto'] ?? $addressData['obec'] ?? null,
            );
        }

        return null;
    }
}
