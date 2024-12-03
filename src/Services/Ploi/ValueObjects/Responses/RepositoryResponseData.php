<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class RepositoryResponseData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $domain,
        public string $web_directory,
        public string $project_root,
        public string $last_deploy_at,
        public string $created_at,
        public bool $laravel = false,
        public bool $wordpress = false,
        public ?array $repository = [],
    ) {}
}
