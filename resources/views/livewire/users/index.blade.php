<?php

use App\Models\User;
use Mary\Traits\Toast;
use App\Traits\LogFormatter;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Users')] class extends Component {
    use Toast, LogFormatter, WithPagination;

    public string $search = '';

    public bool $modal = false;

    public int $perPage = 10;
    // public array $selected = [];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public ?int $id = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public ?int $role_searchable_id = null;
    public array $varUser = ['id', 'name', 'email', 'role', 'role_searchable_id'];

    // Options list
    public Collection $rolesSearchable;

    public function mount(): void
    {
        $this->searchRole();
    }

    public function searchRole(string $value = '')
    {
        $selectedOption = Role::where('id', $this->role_searchable_id)->get();

        $this->rolesSearchable = Role::query()
            ->where('name', 'like', "%$value%")
            ->take(5)
            ->orderBy('name')
            ->get()
            ->merge($selectedOption);
    }

    // Delete action
    public function delete(): void
    {
        try {
            DB::beginTransaction();

            $user = User::find($this->id);

            $user->delete();

            DB::commit();

            $this->success('User deleted successfully.', position: 'toast-bottom');
        } catch (\Exception $th) {
            DB::rollBack();
            $this->error('Failed to delete user.', position: 'toast-bottom');
            $this->logError($th);
        }
    }

    public function save(): void
    {
        $validation = $this->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email,' . ($this->id ?? 'NULL'),
            'password' => $this->id ? 'nullable' : 'required|string|min:8',
            'role_searchable_id' => 'required',
        ]);

        $role = Role::find($this->role_searchable_id)->name;

        // dd($role);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }

            $user = User::updateOrCreate(['id' => $this->id], $data);

            $user->syncRoles([$role]);

            DB::commit();

            $this->success('User saved successfully.', position: 'toast-bottom');
            $this->modal = false;
            $this->reset($this->varUser);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to save user.', position: 'toast-bottom');
            $this->logError($e);
        }
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'email', 'label' => 'E-mail', 'sortable' => false],
            ['key' => 'role', 'label' => 'Role', 'sortable' => false],
            ['key' => 'created_at', 'label' => 'Created at', 'class' => 'w-64'],
        ];
    }

    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers()
        ];
    }
}; ?>

@script
    <script>
        $js('create', () => {
            $wire.modal = true;
            $wire.id = null;
            $wire.name = '';
            $wire.email = '';
            $wire.password = '';
            $wire.$refresh();
        });

        $js('edit', (user) => {
            $wire.modal = true;
            $wire.id = user.id;
            $wire.name = user.name;
            $wire.email = user.email;
            $wire.role_searchable_id = user.roles[0].id;
            $wire.password = '';
            $wire.$refresh();
        });
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Users" separator>
        <x-slot:actions>
            <x-button label="Create" @click="$js.create" responsive icon="fas.plus" />
        </x-slot:actions>
    </x-header>

    <div class="flex justify-end items-center gap-5">
        <x-input placeholder="Search..." wire:model.live="search" clearable icon="o-magnifying-glass" />
    </div>

    <!-- TABLE  -->
    <x-card class="mt-4" shadow>
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" per-page="perPage" :per-page-values="[10, 25, 50, 100]"
            with-pagination @row-click="$js.edit($event.detail)">
            @scope('cell_role', $data)
                <p>{{ $data->getRoleNames()->first() }}</p>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="modal" title="Form User" box-class="w-full h-fit max-w-[600px]" without-trap-focus>
        <x-form wire:submit="save" no-separator>

            <div>
                <x-input label="Name" wire:model="name"  />
            </div>

            <div>
                <x-input label="E-mail" wire:model="email" type="email"  />
            </div>

            <div>
                <x-password label="Password" wire:model="password" right />
            </div>

            <div>
                <x-choices-offline
                label="Role"
                wire:model="role_searchable_id"
                :options="$rolesSearchable"
                placeholder="Search ..."
                search-function="searchRole"
                single
                searchable />
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
