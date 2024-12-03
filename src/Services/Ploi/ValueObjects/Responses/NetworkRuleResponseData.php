<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class NetworkRuleResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $port,
        public ?string $from_ip_address,
        public string $rule_type,
        public string $status,
        public string $created_at,
    ) {}
}
