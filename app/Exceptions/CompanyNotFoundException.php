<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\CountryCode;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CompanyNotFoundException extends Exception
{
    public function __construct(
        public readonly string $companyId,
        public readonly CountryCode $countryCode,
        ?string $message = null,
    ) {
        parent::__construct(
            $message ?? "Company with ID {$companyId} not found in {$countryCode->label()} registry",
            Response::HTTP_NOT_FOUND
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'ERROR',
            'error' => [
                'code' => 'COMPANY_NOT_FOUND',
                'message' => $this->getMessage(),
                'companyId' => $this->companyId,
                'countryCode' => $this->countryCode->value,
            ],
        ], Response::HTTP_NOT_FOUND);
    }
}
