<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class CronjobResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $status,
        public string $command,
        public string $user,
        public string $frequency,
        public string $created_at,
    ) {}
}
