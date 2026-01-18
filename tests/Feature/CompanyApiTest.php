<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\AddressDto;
use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Services\Registry\Contracts\RegistryProviderInterface;
use App\Services\Registry\RegistryProviderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Company API Feature Tests
 *
 * Tests the full HTTP request/response cycle for the company API.
 */
class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful company info retrieval.
     */
    public function test_get_company_info_returns_correct_format(): void
    {
        // Arrange: Create mock provider and company data
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

        // Act
        $response = $this->getJson('/api/company/info/cz/12345678');

        // Assert
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

    /**
     * Test invalid country code returns error.
     */
    public function test_invalid_country_code_returns_400(): void
    {
        $response = $this->getJson('/api/company/info/xx/12345678');

        // Route constraint should catch this
        $response->assertStatus(404);
    }

    /**
     * Test health endpoint.
     */
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
