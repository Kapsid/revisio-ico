<?php

declare(strict_types=1);

namespace App\Models;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CachedCompany extends Model
{
    use SoftDeletes;

    protected $table = 'cached_companies';

    protected $fillable = [
        'company_id', 'country_code', 'version', 'is_current', 'name',
        'vat_id', 'vat_payer', 'address_street', 'address_house_number',
        'address_orientation_number', 'address_zip', 'address_city',
        'raw_response', 'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'is_current' => 'boolean',
            'vat_payer' => 'boolean',
            'raw_response' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeForCountry(Builder $query, CountryCode $countryCode): Builder
    {
        return $query->where('country_code', $countryCode->value);
    }

    public function scopeForCompanyId(Builder $query, string $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeNeedsRefresh(Builder $query, int $ttlHours = 24): Builder
    {
        return $query->where('fetched_at', '<', now()->subHours($ttlHours));
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

    public static function fromDto(CompanyDto $dto, ?array $rawResponse = null): self
    {
        return new self([
            'company_id' => $dto->id,
            'country_code' => $dto->countryCode->value,
            'name' => $dto->name,
            'vat_id' => $dto->vatId,
            'vat_payer' => $dto->vatPayer,
            'address_street' => $dto->address?->street,
            'address_house_number' => $dto->address?->houseNumber,
            'address_orientation_number' => $dto->address?->orientationNumber,
            'address_zip' => $dto->address?->zip,
            'address_city' => $dto->address?->city,
            'raw_response' => $rawResponse,
            'fetched_at' => now(),
        ]);
    }

    public function isFresh(int $ttlHours = 24): bool
    {
        return $this->fetched_at->addHours($ttlHours)->isFuture();
    }
}
