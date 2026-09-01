<?php

namespace App\Domain\BerberApp\Payment\Queries;

use App\Models\BerberApp\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = Payment::query()->with(['booking']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('status', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['booking_id']) && $params['booking_id']) $query->where('booking_id', $params['booking_id']);
        $sortField = in_array($sortField, Payment::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}