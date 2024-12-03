<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class SshKeyResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $status,
        public string $name,
        public string $key,
        public string $system_user,
    ) {}
}
