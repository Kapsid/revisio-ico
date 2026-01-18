<?php

declare(strict_types=1);

namespace App\Models;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CachedCompany extends Model
{
    protected $table = 'cached_companies';

    protected $fillable = [
        'company_id', 'country_code', 'name', 'vat_id', 'vat_payer',
        'address_street', 'address_house_number', 'address_orientation_number',
        'address_zip', 'address_city', 'raw_response', 'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'vat_payer' => 'boolean',
            'raw_response' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    public function scopeForCountry(Builder $query, CountryCode $countryCode): Builder
    {
        return $query->where('country_code', $countryCode->value);
    }

    public function scopeForCompanyId(Builder $query, string $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function toDto(): CompanyDto
    {
        return new CompanyDto(
            name: $this->name,
            id: $this->company_id,
            countryCode: CountryCode::from($this->country_code),
            vatId: $this->vat_id,
            vatPayer: $this->vat_payer,
            address: new AddressDto(
                street: $this->address_street,
                houseNumber: $this->address_house_number,
                orientationNumber: $this->address_orientation_number,
                zip: $this->address_zip ? (int) $this->address_zip : null,
                city: $this->address_city,
            ),
        );
    }

    public function isFresh(int $ttlHours = 24): bool
    {
        return $this->fetched_at->addHours($ttlHours)->isFuture();
    }
}
