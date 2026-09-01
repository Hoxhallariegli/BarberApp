<?php

namespace App\Domain\BerberApp\DeviceToken\Queries;

use App\Models\BerberApp\DeviceToken;
use Illuminate\Database\Eloquent\Builder;

class DeviceTokenListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = DeviceToken::query();
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('fcm_token', 'like', '%' . $params['search'] . '%');
                $query->orWhere('device_type', 'like', '%' . $params['search'] . '%');
            });
        }

        $sortField = in_array($sortField, DeviceToken::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}