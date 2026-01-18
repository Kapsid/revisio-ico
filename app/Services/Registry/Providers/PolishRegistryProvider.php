<?php

declare(strict_types=1);

namespace App\Services\Registry\Providers;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\CompanyNotFoundException;
use App\Exceptions\RegistryException;
use App\Services\Registry\Providers\RegistryProviderInterface;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\ReportTypes;
use GusApi\SearchReport;
use Illuminate\Support\Facades\Log;
use Throwable;

// TODO: For commercial usage, register at https://api.stat.gov.pl/Home/RegonApi
final class PolishRegistryProvider implements RegistryProviderInterface
{
    private GusApi $gusApi;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('registry.pl.api_key', '');
        $environment = config('registry.pl.environment', 'dev');
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
            $this->gusApi->login();
            $reports = $this->searchCompany($companyId);

            if (empty($reports)) {
                throw new CompanyNotFoundException($companyId, CountryCode::PL);
            }

            return $this->mapToDto($reports[0], $companyId);

        } catch (NotFoundException|\InvalidArgumentException $e) {
            throw new CompanyNotFoundException($companyId, CountryCode::PL);
        } catch (InvalidUserKeyException $e) {
            Log::error('Polish GUS API key invalid', ['error' => $e->getMessage()]);
            throw new RegistryException(CountryCode::PL, 'Polish registry API key is invalid or expired');
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

    /** @return SearchReport[] */
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

    private function normalizeCompanyId(string $companyId): string
    {
        return preg_replace('/\D/', '', $companyId);
    }

    private function mapToDto(SearchReport $report, string $companyId): CompanyDto
    {
        $nip = $report->getNip() ?: null;

        return new CompanyDto(
            name: $report->getName(),
            id: $companyId,
            countryCode: CountryCode::PL,
            vatId: $nip ? 'PL' . $nip : null,
            vatPayer: $nip !== null,
            address: new AddressDto(
                street: $report->getStreet() ?: null,
                houseNumber: $report->getPropertyNumber() ?: null,
                orientationNumber: $report->getApartmentNumber() ?: null,
                zip: $report->getZipCode() ? (int) str_replace('-', '', $report->getZipCode()) : null,
                city: $report->getCity() ?: null,
            ),
        );
    }
}
