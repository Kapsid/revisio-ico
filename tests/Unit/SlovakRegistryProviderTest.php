<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\CompanyNotFoundException;
use App\Services\Registry\Providers\SlovakRegistryProvider;
use lubosdz\parserOrsr\ConnectorOrsr;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SlovakRegistryProviderTest extends TestCase
{
    private SlovakRegistryProvider $provider;
    private MockInterface $mockOrsr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockOrsr = Mockery::mock(ConnectorOrsr::class);
        $this->provider = new SlovakRegistryProvider();

        $reflection = new ReflectionClass($this->provider);
        $property = $reflection->getProperty('orsr');
        $property->setAccessible(true);
        $property->setValue($this->provider, $this->mockOrsr);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_country_code_returns_sk(): void
    {
        $this->assertEquals(CountryCode::SK, $this->provider->getCountryCode());
    }

    public function test_can_handle_valid_slovak_company_id(): void
    {
        $this->assertTrue($this->provider->canHandle('12345678'));
        $this->assertTrue($this->provider->canHandle('46440224'));
    }

    public function test_can_handle_rejects_invalid_company_id(): void
    {
        $this->assertFalse($this->provider->canHandle('1234567'));
        $this->assertFalse($this->provider->canHandle('123456789'));
        $this->assertFalse($this->provider->canHandle('abcdefgh'));
    }

    public function test_fetch_company_returns_dto_with_full_data(): void
    {
        $orsrResponse = [
            'obchodne_meno' => 'GymBeam s.r.o.',
            'ico' => '46440224',
            'adresa' => [
                'street' => 'Rastislavova',
                'number' => '93',
                'city' => 'Košice - mestská časť Juh',
                'zip' => '04001',
            ],
            'pravna_forma' => 'Spoločnosť s ručením obmedzeným',
            'den_zapisu' => '29.11.2011',
        ];

        $this->mockOrsr
            ->shouldReceive('getDetailByICO')
            ->with('46440224')
            ->once()
            ->andReturn($orsrResponse);

        $result = $this->provider->fetchCompany('46440224');

        $this->assertInstanceOf(CompanyDto::class, $result);
        $this->assertEquals('GymBeam s.r.o.', $result->name);
        $this->assertEquals('46440224', $result->id);
        $this->assertEquals(CountryCode::SK, $result->countryCode);
        $this->assertNull($result->vatId);
        $this->assertNull($result->vatPayer);

        $this->assertNotNull($result->address);
        $this->assertEquals('Rastislavova', $result->address->street);
        $this->assertEquals('93', $result->address->houseNumber);
        $this->assertEquals('Košice - mestská časť Juh', $result->address->city);
        $this->assertEquals(4001, $result->address->zip);
    }

    public function test_fetch_company_normalizes_company_id(): void
    {
        $orsrResponse = [
            'obchodne_meno' => 'Test Company',
            'ico' => '00123456',
            'adresa' => [],
        ];

        $this->mockOrsr
            ->shouldReceive('getDetailByICO')
            ->with('00123456')
            ->once()
            ->andReturn($orsrResponse);

        $result = $this->provider->fetchCompany('123456');

        $this->assertEquals('00123456', $result->id);
    }

    public function test_fetch_company_throws_not_found_for_empty_response(): void
    {
        $this->mockOrsr
            ->shouldReceive('getDetailByICO')
            ->with('99999999')
            ->once()
            ->andReturn([]);

        $this->expectException(CompanyNotFoundException::class);

        $this->provider->fetchCompany('99999999');
    }

    public function test_fetch_company_throws_not_found_for_missing_name(): void
    {
        $this->mockOrsr
            ->shouldReceive('getDetailByICO')
            ->with('99999999')
            ->once()
            ->andReturn(['ico' => '99999999']);

        $this->expectException(CompanyNotFoundException::class);

        $this->provider->fetchCompany('99999999');
    }

    public function test_fetch_company_handles_missing_address(): void
    {
        $orsrResponse = [
            'obchodne_meno' => 'Company Without Address',
            'ico' => '12345678',
        ];

        $this->mockOrsr
            ->shouldReceive('getDetailByICO')
            ->with('12345678')
            ->once()
            ->andReturn($orsrResponse);

        $result = $this->provider->fetchCompany('12345678');

        $this->assertEquals('Company Without Address', $result->name);
        $this->assertNull($result->address);
    }

    public function test_fetch_company_handles_partial_address(): void
    {
        $orsrResponse = [
            'obchodne_meno' => 'Partial Address Company',
            'ico' => '12345678',
            'adresa' => [
                'city' => 'Bratislava',
            ],
        ];

        $this->mockOrsr
            ->shouldReceive('getDetailByICO')
            ->with('12345678')
            ->once()
            ->andReturn($orsrResponse);

        $result = $this->provider->fetchCompany('12345678');

        $this->assertNotNull($result->address);
        $this->assertEquals('Bratislava', $result->address->city);
        $this->assertNull($result->address->street);
        $this->assertNull($result->address->houseNumber);
    }
}
