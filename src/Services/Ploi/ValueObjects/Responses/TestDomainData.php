<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class TestDomainData
{
    public function __construct(
        public int $id,
        public string $domain,
        public ?string $test_domain,
        public ?string $full_test_domain,
    ) {}
}