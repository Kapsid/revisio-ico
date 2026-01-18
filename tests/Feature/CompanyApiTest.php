<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Services\Registry\Providers\RegistryProviderInterface;
use App\Services\Registry\RegistryProviderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_company_info_returns_correct_format(): void
    {
        $mockCompany = new CompanyDto(
            name: 'Test Company s.r.o.',
            id: '12345678',
            countryCode: CountryCode::CZ,
            vatId: 'CZ12345678',
            vatPayer: true,
            address: new AddressDto(
                street: 'Testovací',
                houseNumber: '123',
                orientationNumber: '4',
                zip: 11000,
                city: 'Praha',
            ),
        );

        $mockProvider = Mockery::mock(RegistryProviderInterface::class);
        $mockProvider->shouldReceive('getCountryCode')->andReturn(CountryCode::CZ);
        $mockProvider->shouldReceive('fetchCompany')
            ->with('12345678')
            ->andReturn($mockCompany);

        $mockFactory = Mockery::mock(RegistryProviderFactory::class);
        $mockFactory->shouldReceive('make')
            ->with(CountryCode::CZ)
            ->andReturn($mockProvider);

        $this->app->instance(RegistryProviderFactory::class, $mockFactory);

        $response = $this->getJson('/api/company/info/cz/12345678');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'OK',
                'data' => [
                    'name' => 'Test Company s.r.o.',
                    'id' => '12345678',
                    'vatId' => 'CZ12345678',
                    'vatPayer' => true,
                    'countryCode' => 'cz',
                    'address' => [
                        'street' => 'Testovací',
                        'houseNumber' => '123',
                        'orientationNumber' => '4',
                        'zip' => 11000,
                        'city' => 'Praha',
                    ],
                ],
            ]);
    }

    public function test_get_slovak_company_info_returns_correct_format(): void
    {
        $mockCompany = new CompanyDto(
            name: 'GymBeam s.r.o.',
            id: '46440224',
            countryCode: CountryCode::SK,
            vatId: null,
            vatPayer: null,
            address: new AddressDto(
                street: 'Rastislavova',
                houseNumber: '93',
                zip: 4001,
                city: 'Košice - mestská časť Juh',
            ),
        );

        $mockProvider = Mockery::mock(RegistryProviderInterface::class);
        $mockProvider->shouldReceive('getCountryCode')->andReturn(CountryCode::SK);
        $mockProvider->shouldReceive('fetchCompany')
            ->with('46440224')
            ->andReturn($mockCompany);

        $mockFactory = Mockery::mock(RegistryProviderFactory::class);
        $mockFactory->shouldReceive('make')
            ->with(CountryCode::SK)
            ->andReturn($mockProvider);

        $this->app->instance(RegistryProviderFactory::class, $mockFactory);

        $response = $this->getJson('/api/company/info/sk/46440224');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'OK',
                'data' => [
                    'name' => 'GymBeam s.r.o.',
                    'id' => '46440224',
                    'vatId' => null,
                    'vatPayer' => null,
                    'countryCode' => 'sk',
                    'address' => [
                        'street' => 'Rastislavova',
                        'houseNumber' => '93',
                        'zip' => 4001,
                        'city' => 'Košice - mestská časť Juh',
                    ],
                ],
            ]);
    }

    public function test_invalid_country_code_returns_404(): void
    {
        $response = $this->getJson('/api/company/info/xx/12345678');

        $response->assertStatus(404);
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'OK',
                'service' => 'registry-service',
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
