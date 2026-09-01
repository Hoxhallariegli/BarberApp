<?php

namespace App\Livewire\Admin\BerberApp\Services;

use App\Models\BerberApp\Service;
use App\Domain\BerberApp\Service\Queries\ServiceListQuery;
use App\Domain\BerberApp\Service\Actions\DeleteServiceAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Services')]
class Services extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', ]); $this->resetPage(); }

    public function render()
    {
        abort_if_cannot('view_services');
        $query = (new ServiceListQuery())->handle(['search' => $this->search, ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.berber-app.services.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Service::sortable(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, Service::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteService($id, DeleteServiceAction $action)
    {
        abort_if_cannot('delete_services');
        $item = Service::find($id);
        if (!$item) { $this->dispatch('toast', message: __('services.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('services.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('services.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('services.delete_error'), type: 'error'); }
    }
}
