<?php

declare(strict_types=1);

namespace App\Services\Registry;

use App\Enums\CountryCode;
use App\Exceptions\InvalidCountryCodeException;
use App\Services\Registry\Providers\RegistryProviderInterface;
use Illuminate\Contracts\Container\Container;

class RegistryProviderFactory
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function make(CountryCode $countryCode): RegistryProviderInterface
    {
        $config = config("registry.{$countryCode->value}");

        if (!$config || !isset($config['provider'])) {
            throw new InvalidCountryCodeException($countryCode->value);
        }

        return $this->container->make($config['provider']);
    }

    public function makeFromString(string $countryCode): RegistryProviderInterface
    {
        $code = CountryCode::tryFromString($countryCode);

        if ($code === null) {
            throw new InvalidCountryCodeException($countryCode);
        }

        return $this->make($code);
    }

    public function supports(string $countryCode): bool
    {
        return CountryCode::tryFromString($countryCode) !== null;
    }
}
