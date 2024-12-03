<?php

namespace Lartisan\PloiDashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Lartisan\PloiDashboard\Services\Ploi\Ploi;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\NetworkRuleResponseData;
use Sushi\Sushi;

class NetworkRule extends Model
{
    use Sushi;

    protected $guarded = [];

    protected array $schema = [
        'id' => 'integer',
        'name' => 'string',
        'port' => 'string',
        'from_ip_address' => 'string',
        'rule_type' => 'string',
        'status' => 'string',
        'created_at' => 'string',
    ];

    public function getRows(): array
    {
        try {
            return Ploi::make()->listNetworkRules()
                ->map(fn (NetworkRuleResponseData $item) => $item->toLivewire())
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
