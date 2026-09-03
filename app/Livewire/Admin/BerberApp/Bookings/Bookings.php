<?php

namespace App\Livewire\Admin\BerberApp\Bookings;

use App\Models\BerberApp\Booking;
use App\Domain\BerberApp\Booking\Queries\BookingListQuery;
use App\Domain\BerberApp\Booking\Actions\DeleteBookingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class Bookings extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $customer_id = '';
    #[Url(history: true)] public $barber_id = '';
    #[Url(history: true)] public $service_id = '';
    #[Url(history: true)] public $status = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = false;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'customer_id', 'barber_id', 'service_id', 'status']); $this->resetPage(); }

    public function render()
    {
        abort_if_cannot('view_bookings');
        $query = (new BookingListQuery())->handle([
            'search' => $this->search,
            'customer_id' => $this->customer_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'status' => $this->status,
        ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.berber-app.bookings.index', [
            'items' => $query->with(['customer', 'barber', 'service'])->paginate($this->paginate),
            'sortableFields' => Booking::sortable(),
            'customers' => \App\Models\BerberApp\Customer::pluck('name', 'id')->toArray(),
            'barbers' => \App\Models\BerberApp\Barber::pluck('name', 'id')->toArray(),
            'services' => \App\Models\BerberApp\Service::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app')->title(__('bookings.Bookings'));
    }

    public function sortBy($field) { if (!in_array($field, Booking::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteBooking($id, DeleteBookingAction $action)
    {
        abort_if_cannot('delete_bookings');
        $item = Booking::find($id);
        if (!$item) { $this->dispatch('toast', message: __('bookings.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('bookings.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('bookings.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('bookings.delete_error'), type: 'error'); }
    }
}
