<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\CertificateResponseData;
use Sushi\Sushi;

class Certificate extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'status' => 'string',
        'domain' => 'string',
        'type' => 'string',
        'tenant' => 'boolean',
        'site_id' => 'integer',
        'server_id' => 'integer',
        'expires_at' => 'string',
        'created_at' => 'string',
    ];

    protected $casts = [
        'tenant' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->getCertificates()
                ->map(fn(CertificateResponseData $item) => $item->toLivewire())
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
