<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

readonly class RedirectData extends BaseResponseData
{
    public function __construct(
        public int $id,
        public string $status,
        public string $redirect_from,
        public string $redirect_to,
        public string $type,
    ) {}
}
