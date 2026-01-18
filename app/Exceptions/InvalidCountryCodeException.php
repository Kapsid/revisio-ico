<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InvalidCountryCodeException extends Exception
{
    public function __construct(
        public readonly string $providedCode,
        public readonly array $supportedCodes = ['cz', 'sk', 'pl'],
    ) {
        parent::__construct(
            "Invalid country code: {$providedCode}. Supported: " . implode(', ', $supportedCodes),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'ERROR',
            'error' => [
                'code' => 'INVALID_COUNTRY_CODE',
                'message' => $this->getMessage(),
                'provided' => $this->providedCode,
                'supported' => $this->supportedCodes,
            ],
        ], Response::HTTP_BAD_REQUEST);
    }
}
