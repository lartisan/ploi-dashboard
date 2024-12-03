<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class QueueResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $connection,
        public string $queue,
        public mixed $maximum_seconds,
        public mixed $maximum_tries,
        public mixed $enviroment,
        public mixed $sleep,
        public mixed $processes,
        public mixed $backoff,
        public mixed $system_user,
        public mixed $php_version,
        public mixed $status,
        public int $site_id,
        public ?int $server_id,
    ) {}
}
