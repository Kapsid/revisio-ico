<?php

declare(strict_types=1);

namespace App\Services\Registry\Providers;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\CompanyNotFoundException;
use App\Exceptions\RegistryException;
use App\Services\Registry\Contracts\RegistryProviderInterface;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\ReportTypes;
use GusApi\SearchReport;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Polish Registry Provider
 *
 * Fetches company data from Polish GUS (Główny Urząd Statystyczny) registry.
 * Uses the gusapi/gusapi package for REGON API communication.
 *
 * GUS/REGON is the official Polish statistical office registry
 * containing data about all business entities in Poland.
 *
 * API Key: Required. Get it from https://api.stat.gov.pl/Home/RegonApi
 *
 * Data Mapping:
 * - Nazwa -> name
 * - Regon -> id
 * - Nip -> vatId
 * - Ulica -> address.street
 * - NrNieruchomosci -> address.houseNumber
 * - NrLokalu -> address.orientationNumber
 * - KodPocztowy -> address.zip
 * - Miejscowosc -> address.city
 */
final class PolishRegistryProvider implements RegistryProviderInterface
{
    private GusApi $gusApi;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('registry.pl.api_key', '');
        $environment = config('registry.pl.environment', 'dev');

        // GusApi constructor: GusApi(string $userKey, string $env = 'dev')
        // 'dev' uses test environment, 'prod' uses production
        $this->gusApi = new GusApi($this->apiKey, $environment);
    }

    public function getCountryCode(): CountryCode
    {
        return CountryCode::PL;
    }

    public function canHandle(string $companyId): bool
    {
        return CountryCode::PL->validateCompanyId($companyId);
    }

    public function fetchCompany(string $companyId): CompanyDto
    {
        $companyId = $this->normalizeCompanyId($companyId);

        try {
            // Login to GUS API (session-based)
            $this->gusApi->login();

            // Search by REGON or NIP based on length
            $reports = $this->searchCompany($companyId);

            if (empty($reports)) {
                throw new CompanyNotFoundException($companyId, CountryCode::PL);
            }

            // Get the first result
            $report = $reports[0];

            // Get full report data for detailed information
            return $this->mapToDto($report, $companyId);

        } catch (NotFoundException $e) {
            throw new CompanyNotFoundException($companyId, CountryCode::PL);
        } catch (InvalidUserKeyException $e) {
            Log::error('Polish GUS API key invalid', ['error' => $e->getMessage()]);
            throw new RegistryException(
                CountryCode::PL,
                'Polish registry API key is invalid or expired'
            );
        } catch (CompanyNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Polish registry error', [
                'companyId' => $companyId,
                'error' => $e->getMessage(),
            ]);
            throw RegistryException::fromException(CountryCode::PL, $e);
        }
    }

    /**
     * Search for company by ID.
     *
     * The ID can be:
     * - NIP (10 digits) - Tax identification number
     * - REGON (9 or 14 digits) - Statistical number
     *
     * @return SearchReport[]
     */
    private function searchCompany(string $companyId): array
    {
        $length = strlen($companyId);

        return match ($length) {
            10 => $this->gusApi->getByNip($companyId),
            9, 14 => $this->gusApi->getByRegon($companyId),
            default => throw new \InvalidArgumentException(
                'Invalid Polish company ID format. Expected NIP (10 digits) or REGON (9/14 digits)'
            ),
        };
    }

    /**
     * Normalize company ID.
     */
    private function normalizeCompanyId(string $companyId): string
    {
        // Remove any non-digit characters
        return preg_replace('/\D/', '', $companyId);
    }

    /**
     * Map GUS SearchReport to CompanyDto.
     */
    private function mapToDto(SearchReport $report, string $companyId): CompanyDto
    {
        // Get NIP for VAT ID
        $nip = $report->getNip();
        $vatId = $nip ? 'PL' . $nip : null;

        // Build address
        $address = new AddressDto(
            street: $report->getStreet() ?: null,
            houseNumber: $report->getPropertyNumber() ?: null,
            orientationNumber: $report->getApartmentNumber() ?: null,
            zip: $report->getZipCode() ? (int) str_replace('-', '', $report->getZipCode()) : null,
            city: $report->getCity() ?: null,
        );

        return new CompanyDto(
            name: $report->getName(),
            id: $report->getRegon(),
            countryCode: CountryCode::PL,
            vatId: $vatId,
            vatPayer: $nip !== null, // If NIP exists, company is likely a VAT payer
            address: $address,
        );
    }
}
