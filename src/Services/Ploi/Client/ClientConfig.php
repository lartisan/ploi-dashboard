<?php

namespace Lartisan\PloiDashboard\Services\Ploi\Client;

class ClientConfig
{
    public function __construct(
        public string $apiKey,
        public string $apiUrl,
        public int $serverId,
        public int $websiteId,
    ) {}
}
