<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\CompanyNotFoundException;
use App\Services\Registry\Providers\PolishRegistryProvider;
use GusApi\GusApi;
use GusApi\SearchReport;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Tests\TestCase;

class PolishRegistryProviderTest extends TestCase
{
    private PolishRegistryProvider $provider;
    private MockInterface $mockGusApi;

    protected function setUp(): void
    {
        parent::setUp();

        config(['registry.pl.environment' => 'dev']);

        $this->mockGusApi = Mockery::mock(GusApi::class);
        $this->provider = new PolishRegistryProvider();

        $reflection = new ReflectionClass($this->provider);
        $property = $reflection->getProperty('gusApi');
        $property->setAccessible(true);
        $property->setValue($this->provider, $this->mockGusApi);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_country_code_returns_pl(): void
    {
        $this->assertEquals(CountryCode::PL, $this->provider->getCountryCode());
    }

    public function test_can_handle_valid_polish_nip(): void
    {
        $this->assertTrue($this->provider->canHandle('1234567890'));
    }

    public function test_can_handle_valid_polish_regon_9_digits(): void
    {
        $this->assertTrue($this->provider->canHandle('123456789'));
    }

    public function test_can_handle_valid_polish_regon_14_digits(): void
    {
        $this->assertTrue($this->provider->canHandle('12345678901234'));
    }

    public function test_can_handle_rejects_invalid_company_id(): void
    {
        $this->assertFalse($this->provider->canHandle('12345678'));
        $this->assertFalse($this->provider->canHandle('12345'));
        $this->assertFalse($this->provider->canHandle('abcdefghij'));
    }

    public function test_fetch_company_by_nip_returns_dto(): void
    {
        $mockReport = Mockery::mock(SearchReport::class);
        $mockReport->shouldReceive('getName')->andReturn('Test Company Sp. z o.o.');
        $mockReport->shouldReceive('getRegon')->andReturn('123456789');
        $mockReport->shouldReceive('getNip')->andReturn('1234567890');
        $mockReport->shouldReceive('getStreet')->andReturn('Marszałkowska');
        $mockReport->shouldReceive('getPropertyNumber')->andReturn('100');
        $mockReport->shouldReceive('getApartmentNumber')->andReturn('10');
        $mockReport->shouldReceive('getZipCode')->andReturn('00-001');
        $mockReport->shouldReceive('getCity')->andReturn('Warszawa');

        $this->mockGusApi->shouldReceive('login')->once();
        $this->mockGusApi->shouldReceive('getByNip')
            ->with('1234567890')
            ->once()
            ->andReturn([$mockReport]);

        $result = $this->provider->fetchCompany('1234567890');

        $this->assertInstanceOf(CompanyDto::class, $result);
        $this->assertEquals('Test Company Sp. z o.o.', $result->name);
        $this->assertEquals('1234567890', $result->id);
        $this->assertEquals(CountryCode::PL, $result->countryCode);
        $this->assertEquals('PL1234567890', $result->vatId);
        $this->assertTrue($result->vatPayer);

        $this->assertNotNull($result->address);
        $this->assertEquals('Marszałkowska', $result->address->street);
        $this->assertEquals('100', $result->address->houseNumber);
        $this->assertEquals('10', $result->address->orientationNumber);
        $this->assertEquals(1, $result->address->zip);
        $this->assertEquals('Warszawa', $result->address->city);
    }

    public function test_fetch_company_by_regon_returns_dto(): void
    {
        $mockReport = Mockery::mock(SearchReport::class);
        $mockReport->shouldReceive('getName')->andReturn('REGON Company');
        $mockReport->shouldReceive('getRegon')->andReturn('123456789');
        $mockReport->shouldReceive('getNip')->andReturn('9876543210');
        $mockReport->shouldReceive('getStreet')->andReturn('Krakowska');
        $mockReport->shouldReceive('getPropertyNumber')->andReturn('50');
        $mockReport->shouldReceive('getApartmentNumber')->andReturn('');
        $mockReport->shouldReceive('getZipCode')->andReturn('30-001');
        $mockReport->shouldReceive('getCity')->andReturn('Kraków');

        $this->mockGusApi->shouldReceive('login')->once();
        $this->mockGusApi->shouldReceive('getByRegon')
            ->with('123456789')
            ->once()
            ->andReturn([$mockReport]);

        $result = $this->provider->fetchCompany('123456789');

        $this->assertEquals('REGON Company', $result->name);
        $this->assertEquals('123456789', $result->id); // queried REGON is preserved
    }

    public function test_fetch_company_throws_not_found_for_empty_response(): void
    {
        $this->mockGusApi->shouldReceive('login')->once();
        $this->mockGusApi->shouldReceive('getByNip')
            ->with('1234567890')
            ->once()
            ->andReturn([]);

        $this->expectException(CompanyNotFoundException::class);

        $this->provider->fetchCompany('1234567890');
    }

    public function test_fetch_company_normalizes_company_id(): void
    {
        $mockReport = Mockery::mock(SearchReport::class);
        $mockReport->shouldReceive('getName')->andReturn('Test');
        $mockReport->shouldReceive('getRegon')->andReturn('123456789');
        $mockReport->shouldReceive('getNip')->andReturn('');
        $mockReport->shouldReceive('getStreet')->andReturn('');
        $mockReport->shouldReceive('getPropertyNumber')->andReturn('');
        $mockReport->shouldReceive('getApartmentNumber')->andReturn('');
        $mockReport->shouldReceive('getZipCode')->andReturn('');
        $mockReport->shouldReceive('getCity')->andReturn('');

        $this->mockGusApi->shouldReceive('login')->once();
        $this->mockGusApi->shouldReceive('getByNip')
            ->with('1234567890')
            ->once()
            ->andReturn([$mockReport]);

        $result = $this->provider->fetchCompany('123-456-78-90');

        $this->assertEquals('1234567890', $result->id); // normalized queried ID
    }

    public function test_fetch_company_without_nip_has_null_vat(): void
    {
        $mockReport = Mockery::mock(SearchReport::class);
        $mockReport->shouldReceive('getName')->andReturn('No VAT Company');
        $mockReport->shouldReceive('getRegon')->andReturn('123456789');
        $mockReport->shouldReceive('getNip')->andReturn('');
        $mockReport->shouldReceive('getStreet')->andReturn('');
        $mockReport->shouldReceive('getPropertyNumber')->andReturn('');
        $mockReport->shouldReceive('getApartmentNumber')->andReturn('');
        $mockReport->shouldReceive('getZipCode')->andReturn('');
        $mockReport->shouldReceive('getCity')->andReturn('');

        $this->mockGusApi->shouldReceive('login')->once();
        $this->mockGusApi->shouldReceive('getByRegon')
            ->with('123456789')
            ->once()
            ->andReturn([$mockReport]);

        $result = $this->provider->fetchCompany('123456789');

        $this->assertNull($result->vatId);
        $this->assertFalse($result->vatPayer);
    }

    public function test_fetch_company_throws_not_found_for_invalid_format(): void
    {
        $this->mockGusApi->shouldReceive('login')->once();

        $this->expectException(CompanyNotFoundException::class);

        $this->provider->fetchCompany('12345678');
    }
}
