<?php

declare(strict_types=1);

namespace App\DTO;

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
            street: $data['street'] ?? null,
            houseNumber: $data['houseNumber'] ?? null,
            orientationNumber: $data['orientationNumber'] ?? null,
            zip: isset($data['zip']) ? (int) $data['zip'] : null,
            city: $data['city'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'houseNumber' => $this->houseNumber,
            'orientationNumber' => $this->orientationNumber,
            'zip' => $this->zip,
            'city' => $this->city,
        ];
    }

    public function getFullAddress(): string
    {
        $parts = [];

        if ($this->street) {
            $streetPart = $this->street;
            if ($this->houseNumber) {
                $streetPart .= ' ' . $this->houseNumber;
                if ($this->orientationNumber) {
                    $streetPart .= '/' . $this->orientationNumber;
                }
            }
            $parts[] = $streetPart;
        }

        if ($this->zip || $this->city) {
            $parts[] = trim(($this->zip ?? '') . ' ' . ($this->city ?? ''));
        }

        return implode(', ', $parts);
    }
}
