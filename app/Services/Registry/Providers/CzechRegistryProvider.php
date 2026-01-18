<?php

declare(strict_types=1);

namespace App\Services\Registry\Providers;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\CompanyNotFoundException;
use App\Exceptions\RegistryException;
use App\Services\Registry\Providers\RegistryProviderInterface;
use h4kuna\Ares\Ares;
use h4kuna\Ares\AresFactory;
use h4kuna\Ares\Ares\Core\Data;
use h4kuna\Ares\Exception\IdentificationNumberNotFoundException;
use Throwable;

final class CzechRegistryProvider implements RegistryProviderInterface
{
    private Ares $ares;

    public function __construct()
    {
        $this->ares = (new AresFactory())->create();
    }

    public function getCountryCode(): CountryCode
    {
        return CountryCode::CZ;
    }

    public function canHandle(string $companyId): bool
    {
        return CountryCode::CZ->validateCompanyId($companyId);
    }

    public function fetchCompany(string $companyId): CompanyDto
    {
        $companyId = $this->normalizeCompanyId($companyId);

        try {
            $data = $this->ares->loadBasic($companyId);
            return $this->mapToDto($data, $companyId);
        } catch (IdentificationNumberNotFoundException) {
            throw new CompanyNotFoundException($companyId, CountryCode::CZ);
        } catch (Throwable $e) {
            throw RegistryException::fromException(CountryCode::CZ, $e);
        }
    }

    private function normalizeCompanyId(string $companyId): string
    {
        $companyId = preg_replace('/\s+/', '', $companyId);
        return str_pad($companyId, 8, '0', STR_PAD_LEFT);
    }

    private function mapToDto(Data $data, string $companyId): CompanyDto
    {
        return new CompanyDto(
            name: $data->company ?? 'Unknown',
            id: $companyId,
            countryCode: CountryCode::CZ,
            vatId: $data->tin,
            vatPayer: $data->vat_payer,
            address: new AddressDto(
                street: $data->street,
                houseNumber: $data->house_number,
                zip: $data->zip ? (int) preg_replace('/\s+/', '', $data->zip) : null,
                city: $data->city,
            ),
        );
    }
}
