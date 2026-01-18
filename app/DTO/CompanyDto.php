<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\CountryCode;

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
        $countryCode = $data['countryCode'] instanceof CountryCode
            ? $data['countryCode']
            : CountryCode::from($data['countryCode']);

        return new self(
            name: $data['name'],
            id: $data['id'],
            countryCode: $countryCode,
            vatId: $data['vatId'] ?? null,
            vatPayer: $data['vatPayer'] ?? null,
            address: isset($data['address'])
                ? ($data['address'] instanceof AddressDto
                    ? $data['address']
                    : AddressDto::fromArray($data['address']))
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'id' => $this->id,
            'vatId' => $this->vatId,
            'vatPayer' => $this->vatPayer,
            'countryCode' => $this->countryCode->value,
            'address' => $this->address?->toArray(),
        ];
    }
}
