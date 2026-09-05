<?php

declare(strict_types=1);

namespace JOOservices\LaravelController\Tests\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FakeItemResource extends JsonResource
{
    /**
     * @param  Request|null  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var array<string, mixed> $resource */
        $resource = (array) $this->resource;

        return [
            'id' => $resource['id'] ?? null,
            'name' => $resource['name'] ?? null,
        ];
    }
}
