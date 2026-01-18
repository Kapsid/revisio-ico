<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\CountryCode;
use App\Enums\CompanyInfoKeys;

readonly class CompanyDto
{
    public function __construct(
        public string $name,
        public string $id,
        public CountryCode $countryCode,
        public ?string $vatId = null,
        public ?bool $vatPayer = null,
        public ?AddressDto $address = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $countryCode = $data[CompanyInfoKeys::COMPANY_COUNTRY_CODE->value] instanceof CountryCode
            ? $data[CompanyInfoKeys::COMPANY_COUNTRY_CODE->value]
            : CountryCode::from($data[CompanyInfoKeys::COMPANY_COUNTRY_CODE->value]);

        return new self(
            name: $data[CompanyInfoKeys::COMPANY_NAME->value],
            id: $data[CompanyInfoKeys::COMPANY_ID->value],
            countryCode: $countryCode,
            vatId: $data[CompanyInfoKeys::COMPANY_VAT_ID->value] ?? null,
            vatPayer: $data[CompanyInfoKeys::COMPANY_VAT_PAYER->value] ?? null,
            address: isset($data[CompanyInfoKeys::COMPANY_ADDRESS->value])
                ? ($data[CompanyInfoKeys::COMPANY_ADDRESS->value] instanceof AddressDto
                    ? $data[CompanyInfoKeys::COMPANY_ADDRESS->value]
                    : AddressDto::fromArray($data[CompanyInfoKeys::COMPANY_ADDRESS->value]))
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            CompanyInfoKeys::COMPANY_NAME->value => $this->name,
            CompanyInfoKeys::COMPANY_ID->value => $this->id,
            CompanyInfoKeys::COMPANY_VAT_ID->value => $this->vatId,
            CompanyInfoKeys::COMPANY_VAT_PAYER->value => $this->vatPayer,
            CompanyInfoKeys::COMPANY_COUNTRY_CODE->value => $this->countryCode->value,
            CompanyInfoKeys::COMPANY_ADDRESS->value => $this->address?->toArray(),
        ];
    }
}
