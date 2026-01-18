<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CompanyDto and AddressDto.
 *
 * Unit tests test individual classes in isolation,
 * without database or framework dependencies.
 */
class CompanyDtoTest extends TestCase
{
    public function test_company_dto_can_be_created(): void
    {
        $dto = new CompanyDto(
            name: 'Test Company',
            id: '12345678',
            countryCode: CountryCode::CZ,
            vatId: 'CZ12345678',
            vatPayer: true,
        );

        $this->assertEquals('Test Company', $dto->name);
        $this->assertEquals('12345678', $dto->id);
        $this->assertEquals(CountryCode::CZ, $dto->countryCode);
        $this->assertEquals('CZ12345678', $dto->vatId);
        $this->assertTrue($dto->vatPayer);
    }

    public function test_company_dto_to_array(): void
    {
        $address = new AddressDto(
            street: 'Main Street',
            houseNumber: '123',
            orientationNumber: '4',
            zip: 11000,
            city: 'Prague',
        );

        $dto = new CompanyDto(
            name: 'Test Company',
            id: '12345678',
            countryCode: CountryCode::CZ,
            vatId: 'CZ12345678',
            vatPayer: true,
            address: $address,
        );

        $array = $dto->toArray();

        $this->assertEquals('Test Company', $array['name']);
        $this->assertEquals('12345678', $array['id']);
        $this->assertEquals('cz', $array['countryCode']);
        $this->assertEquals('CZ12345678', $array['vatId']);
        $this->assertTrue($array['vatPayer']);
        $this->assertIsArray($array['address']);
        $this->assertEquals('Main Street', $array['address']['street']);
    }

    public function test_company_dto_from_array(): void
    {
        $data = [
            'name' => 'Test Company',
            'id' => '12345678',
            'countryCode' => 'cz',
            'vatId' => 'CZ12345678',
            'vatPayer' => true,
            'address' => [
                'street' => 'Main Street',
                'houseNumber' => '123',
                'zip' => 11000,
                'city' => 'Prague',
            ],
        ];

        $dto = CompanyDto::fromArray($data);

        $this->assertEquals('Test Company', $dto->name);
        $this->assertEquals(CountryCode::CZ, $dto->countryCode);
        $this->assertInstanceOf(AddressDto::class, $dto->address);
        $this->assertEquals('Main Street', $dto->address->street);
    }

    public function test_address_dto_get_full_address(): void
    {
        $address = new AddressDto(
            street: 'Václavské náměstí',
            houseNumber: '123',
            orientationNumber: '4',
            zip: 11000,
            city: 'Praha',
        );

        $fullAddress = $address->getFullAddress();

        $this->assertEquals('Václavské náměstí 123/4, 11000 Praha', $fullAddress);
    }

    public function test_country_code_validation(): void
    {
        // Valid Czech IČO
        $this->assertTrue(CountryCode::CZ->validateCompanyId('12345678'));
        $this->assertFalse(CountryCode::CZ->validateCompanyId('1234567')); // Too short
        $this->assertFalse(CountryCode::CZ->validateCompanyId('123456789')); // Too long

        // Valid Polish REGON
        $this->assertTrue(CountryCode::PL->validateCompanyId('123456789')); // 9 digits
        $this->assertTrue(CountryCode::PL->validateCompanyId('12345678901234')); // 14 digits
    }
}
