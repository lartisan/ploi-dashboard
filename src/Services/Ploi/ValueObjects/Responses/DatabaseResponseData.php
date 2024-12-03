<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class DatabaseResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $type,
        public string $name,
        public int $server_id,
        public string $status,
        public ?array $site,
        public string $created_at,
        public array $users = [],
    ) {}
}