<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\CountryCode;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RegistryException extends Exception
{
    public function __construct(
        public readonly CountryCode $countryCode,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, Response::HTTP_SERVICE_UNAVAILABLE, $previous);
    }

    public static function fromException(CountryCode $countryCode, Throwable $e): self
    {
        return new self($countryCode, "{$countryCode->label()} registry error: {$e->getMessage()}", $e);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'ERROR',
            'error' => [
                'code' => 'REGISTRY_ERROR',
                'message' => $this->getMessage(),
                'countryCode' => $this->countryCode->value,
            ],
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
