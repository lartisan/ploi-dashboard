<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class SiteResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $status,
        public int $server_id,
        public string $domain,
        public ?string $test_domain,
        public string $deploy_script,
        public string $deploy_webhook_url,
        public string $web_directory,
        public ?string $project_type,
        public string $project_root,
        public ?string $last_deploy_at,
        public string $system_user,
        public string $php_version,
        public ?string $health_url,
        public bool $disable_robots,
        public bool $has_repository,
        public bool $zero_downtime_deployment,
        public bool $has_staging,
        public bool $fastcgi_cache,
        public ?string $notes,
        public mixed $disk_usage,
        public string $created_at,
    ) {}
}