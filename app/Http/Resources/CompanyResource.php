<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTO\CompanyDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /** @var CompanyDto */
    public $resource;

    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'status' => 'OK',
            'data' => $this->resource->toArray(),
        ];
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_UNESCAPED_UNICODE);
    }
}
