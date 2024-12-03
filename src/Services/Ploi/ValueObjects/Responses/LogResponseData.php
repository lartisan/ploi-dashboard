<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class LogResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $description,
        public string $content,
        public ?string $type,
        public ?int $site_id,
        public ?int $server_id,
        public string $created_at,
    ) {}
}