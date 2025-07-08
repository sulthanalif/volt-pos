<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Roles')] class extends Component {
    use Toast;

    public bool $modal = false;

    public string $search = '';
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public int $perPage = 10;
    // public array $selected = [];
    public bool $selectAllBool = false;

    public ?int $id = null;
    public string $name;
    public array $selectedPermissions = [];

    public function selectAll(): void
    {
        if ($this->selectAllBool) {
            $this->selectedPermissions = Permission::all()->pluck('name')->toArray();
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function selectedPer(): void
    {
        $check = count($this->selectedPermissions) == Permission::all()->count();
        $this->selectAllBool = $check;
    }

    public function editRole(Role $role): void
    {
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        if (count($this->selectedPermissions) == Permission::all()->count()) {
            $this->selectAllBool = true;
        }
    }

    public function save(): void
    {
        $input = $this->validate([
            'name' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::updateOrCreate(['id' => $this->id], $input);

            $this->selectedPermissions ? $role->syncPermissions($this->selectedPermissions) : $role->revokeAllPermissions();

            DB::commit();
            $this->success('Role saved successfully.', position: 'toast-bottom');
            $this->modal = false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("message: {$e->getMessage()}");
            $this->error('Failed to save role.', position: 'toast-bottom');
        }
    }

    public function delete(): void
    {
        try {
            DB::beginTransaction();

            $role = Role::find($this->id);
            $role->delete();

            DB::commit();
            $this->success('Role deleted successfully.', position: 'toast-bottom');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug("message: {$th->getMessage()}");
            $this->error('Failed to delete role.', position: 'toast-bottom');
        }
    }

    public function datas(): LengthAwarePaginator
    {
        return Role::query()
            ->where('name', 'like', "%{$this->search}%")
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [['key' => 'name', 'label' => 'Name', 'class' => 'w-64'], ['key' => 'created_at', 'label' => 'Created at', 'class' => 'w-64']];
    }

    public function with(): array
    {
        return [
            'datas' => $this->datas(),
            'headers' => $this->headers(),
            'permissions' => Permission::all()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name
                ];
            })->toArray(),
        ];
    }
}; ?>

@script
    <script>
        $js('create', () => {
            $wire.modal = true;
            $wire.id = null;
            $wire.name = '';
            $wire.selectAllBool = false;
            $wire.selectedPermissions = [];
        });

        $js('edit', (role) => {
            $wire.modal = true;
            $wire.id = role.id;
            $wire.name = role.name;
            $wire.selectAllBool = false;
            $wire.selectedPermissions = [];
            $wire.editRole(role);
            $wire.$refresh();
            // console.log();

        });

    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Roles" separator>
        <x-slot:actions>
            <x-button label="Create" @click="$js.create" responsive icon="fas.plus" />
        </x-slot:actions>
    </x-header>

    <div class="flex justify-end items-center gap-5">
        <x-input placeholder="Search..." wire:model.live="search" clearable icon="o-magnifying-glass" />
    </div>

    <!-- TABLE  -->
    <x-card class="mt-4" shadow>
        <x-table :headers="$headers" :rows="$datas" :sort-by="$sortBy" per-page="perPage" :per-page-values="[10, 25, 50, 100]"
            with-pagination @row-click="$js.edit($event.detail)">
        </x-table>
    </x-card>

    <x-modal wire:model="modal" title="Form Role" without-trap-focus>
        <x-form wire:submit="save" no-separator>

            <x-input label="Name" wire:model="name"  />

            <x-checkbox label="Select All Permissions" wire:model="selectAllBool" @change="$wire.selectAll" />
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($permissions as $permission)
                   <x-checkbox
                        label="{{ $permission['name'] }}"
                        wire:model="selectedPermissions"
                        value="{{ $permission['name'] }}"
                        @change="$wire.selectedPer"
                        :checked="in_array($permission['id'], $selectedPermissions)"
                    />
                @endforeach
            </div>
            <x-slot:actions>
                @if ($this->id)
                    <x-button label="Delete" wire:click="delete" class="btn-error" wire:confirm="Are you sure?" />
                @endif
                <x-button label="save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
