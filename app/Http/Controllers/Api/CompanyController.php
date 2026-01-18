<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;

final class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService,
    ) {}

    public function show(string $countryCode, string $companyId): JsonResponse
    {
        $company = $this->companyService->getCompanyInfo($countryCode, $companyId);

        return CompanyResource::make($company)->response()->setStatusCode(200);
    }
}
