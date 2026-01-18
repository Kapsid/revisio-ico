<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CompanyDto;
use App\Enums\CountryCode;
use App\Exceptions\InvalidCountryCodeException;
use App\Services\Registry\Providers\RegistryProviderInterface;
use App\Services\Registry\RegistryProviderFactory;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class RegistryProviderFactoryTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        Container::setInstance($this->container);

        $this->container->instance('config', new Repository([
            'registry' => [
                'cz' => [
                    'provider' => StubRegistryProvider::class,
                ],
            ],
        ]));

        $this->container->bind(StubRegistryProvider::class, fn (): StubRegistryProvider => new StubRegistryProvider());
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        parent::tearDown();
    }

    public function test_make_returns_configured_provider(): void
    {
        $factory = new RegistryProviderFactory($this->container);

        $provider = $factory->make(CountryCode::CZ);

        $this->assertInstanceOf(StubRegistryProvider::class, $provider);
    }

    public function test_make_throws_for_unsupported_country(): void
    {
        $factory = new RegistryProviderFactory($this->container);

        $this->expectException(InvalidCountryCodeException::class);
        $factory->make(CountryCode::PL);
    }

    public function test_make_from_string_throws_for_invalid_code(): void
    {
        $factory = new RegistryProviderFactory($this->container);

        $this->expectException(InvalidCountryCodeException::class);
        $factory->makeFromString('xx');
    }

    public function test_supports_returns_expected_result(): void
    {
        $factory = new RegistryProviderFactory($this->container);

        $this->assertTrue($factory->supports('cz'));
        $this->assertFalse($factory->supports('xx'));
    }
}

class StubRegistryProvider implements RegistryProviderInterface
{
    public function getCountryCode(): CountryCode
    {
        return CountryCode::CZ;
    }

    public function fetchCompany(string $companyId): CompanyDto
    {
        return new CompanyDto(
            name: 'Stub Company',
            id: $companyId,
            countryCode: CountryCode::CZ,
            vatId: 'CZ12345678',
            vatPayer: false,
        );
    }

    public function canHandle(string $companyId): bool
    {
        return true;
    }
}
