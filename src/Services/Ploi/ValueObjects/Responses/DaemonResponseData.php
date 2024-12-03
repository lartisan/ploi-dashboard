<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class DaemonResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $command,
        public ?int $processes,
        public string $system_user,
        public ?string $directory,
        public string $status,
    ) {}
}