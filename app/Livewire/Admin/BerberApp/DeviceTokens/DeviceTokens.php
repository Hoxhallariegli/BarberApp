<?php

namespace App\Livewire\Admin\BerberApp\DeviceTokens;

use App\Models\BerberApp\DeviceToken;
use App\Domain\BerberApp\DeviceToken\Queries\DeviceTokenListQuery;
use App\Domain\BerberApp\DeviceToken\Actions\DeleteDeviceTokenAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class DeviceTokens extends Component
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
        abort_if_cannot('view_device_tokens');
        $query = (new DeviceTokenListQuery())->handle(['search' => $this->search, ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.berber-app.device-tokens.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => DeviceToken::sortable(),
        ])->layout('components.layouts.app')->title(__('device-tokens.DeviceTokens'));
    }

    public function sortBy($field) { if (!in_array($field, DeviceToken::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteDeviceToken($id, DeleteDeviceTokenAction $action)
    {
        abort_if_cannot('delete_device_tokens');
        $item = DeviceToken::find($id);
        if (!$item) { $this->dispatch('toast', message: __('device-tokens.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('device-tokens.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('device-tokens.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('device-tokens.delete_error'), type: 'error'); }
    }
}
