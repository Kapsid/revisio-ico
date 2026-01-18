<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\CompanyInfoKeys;

readonly class AddressDto
{
    public function __construct(
        public ?string $street = null,
        public ?string $houseNumber = null,
        public ?string $orientationNumber = null,
        public ?int $zip = null,
        public ?string $city = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data[CompanyInfoKeys::ADDRESS_STREET->value] ?? null,
            houseNumber: $data[CompanyInfoKeys::ADDRESS_HOUSE_NUMBER->value] ?? null,
            orientationNumber: $data[CompanyInfoKeys::ADDRESS_ORIENTATION_NUMBER->value] ?? null,
            zip: isset($data[CompanyInfoKeys::ADDRESS_ZIP->value]) ? (int) $data[CompanyInfoKeys::ADDRESS_ZIP->value] : null,
            city: $data[CompanyInfoKeys::ADDRESS_CITY->value] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            CompanyInfoKeys::ADDRESS_STREET->value => $this->street,
            CompanyInfoKeys::ADDRESS_HOUSE_NUMBER->value => $this->houseNumber,
            CompanyInfoKeys::ADDRESS_ORIENTATION_NUMBER->value => $this->orientationNumber,
            CompanyInfoKeys::ADDRESS_ZIP->value => $this->zip,
            CompanyInfoKeys::ADDRESS_CITY->value => $this->city,
        ];
    }

    public function getFullAddress(): string
    {
        $streetPart = null;
        if ($this->street) {
            $streetPart = trim(
                $this->street
                . ' ' . ($this->houseNumber ?? '')
                . ($this->orientationNumber ? '/' . $this->orientationNumber : '')
            );
        }

        $cityPart = trim(($this->zip ?? '') . ' ' . ($this->city ?? ''));
        $parts = array_filter(
            [$streetPart, $cityPart],
            static fn (?string $value): bool => $value !== null && $value !== ''
        );

        return implode(', ', $parts);
    }
}
