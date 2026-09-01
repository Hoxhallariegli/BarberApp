<?php

namespace App\Livewire\Admin\BerberApp\Barbers;

use App\Models\BerberApp\Barber;
use App\Domain\BerberApp\Barber\Queries\BarberListQuery;
use App\Domain\BerberApp\Barber\Actions\DeleteBarberAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class Barbers extends Component
{
        use WithPagination, WithFileUploads;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', ]); $this->resetPage(); }

    public function render()
    {
        abort_if_cannot('view_barbers');
        $query = (new BarberListQuery())->handle(['search' => $this->search, ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.berber-app.barbers.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Barber::sortable(),
        ])->layout('components.layouts.app')->title(__('barbers.Barbers'));
    }

    public function sortBy($field) { if (!in_array($field, Barber::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteBarber($id, DeleteBarberAction $action)
    {
        abort_if_cannot('delete_barbers');
        $item = Barber::find($id);
        if (!$item) { $this->dispatch('toast', message: __('barbers.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('barbers.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('barbers.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('barbers.delete_error'), type: 'error'); }
    }
}
