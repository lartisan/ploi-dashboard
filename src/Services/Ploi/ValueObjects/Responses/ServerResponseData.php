<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class ServerResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $status,
        public string $type,
        public string $database_type,
        public string $name,
        public string $ip_address,
        public ?string $internal_ip,
        public string $ssh_port,
        public string $reboot_required,
        public float $php_version,
        public string $php_cli_version,
        public float $mysql_version,
        public int $sites_count,
        public bool $monitoring,
        public bool $opcache,
        public array $installed_php_versions,
        public array $updates,
        public ?string $description,
        public int $status_id,
        public mixed $provider,
        public string $created_at,
        public string $created_human,
        public ?string $uptime_human,
    ) {}
}
