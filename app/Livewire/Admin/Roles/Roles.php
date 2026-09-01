<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Roles;

use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Roles')]
class Roles extends Component
{
    use WithPagination;

    public int $paginate = 25;

    public string $name = '';

    public string $sortField = 'name';

    public bool $sortAsc = true;

    public function render(): View
    {
        abort_if_cannot('view_roles');

        return view('livewire.admin.roles.index');
    }

    public function builder(): mixed
    {
        return Role::orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = ! $this->sortAsc;
        }

        $this->sortField = $field;
    }

    /**
     * @return LengthAwarePaginator<int, Role>
     */
    public function roles(): LengthAwarePaginator
    {
        return $this->builder()
            ->when($this->name, fn (Builder $query) => $query->where('name', 'like', '%'.$this->name.'%'))
            ->paginate($this->paginate);
    }

    public function deleteRole(string $id): void
    {
        abort_if_cannot('delete_roles');

        try {
            $this->builder()->findOrFail($id)->delete();
            $this->dispatch('toast', message: __('roles.deleted'), type: 'success');
            $this->reset();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000" || $e->getCode() == "19") {
                $this->dispatch('toast', message: __('roles.delete_error_referenced'), type: 'error');
            } else {
                $this->dispatch('toast', message: __('roles.delete_error'), type: 'error');
            }
        }
    }
}
