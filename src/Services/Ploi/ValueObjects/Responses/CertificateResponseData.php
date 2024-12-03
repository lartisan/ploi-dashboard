<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class CertificateResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $status,
        public string $domain,
        public string $type,
        public int $site_id,
        public int $server_id,
        public ?string $expires_at,
        public string $created_at,
    ) {}
}