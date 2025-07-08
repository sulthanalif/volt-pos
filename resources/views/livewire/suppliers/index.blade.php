<?php

use Mary\Traits\Toast;
use App\Models\Supplier;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Suppliers')] class extends Component {
    use Toast, WithPagination, CreateOrUpdate;

    public string $search = '';

    public bool $drawer = false;

    public int $perPage = 10;
    // public array $selected = [];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';


    public function save(): void
    {
        $this->setModel(new Supplier());

        $this->saveOrUpdate(
            validationRules: [
                'name' => ['required', 'min:3', 'string'],
                'email' => ['required', 'email', 'unique:suppliers,email,' . $this->recordId],
                'phone' => ['required', 'min:10'],
                'address' => ['required'],
            ],
        );
        $this->reset('name');
    }

    public function delete(): void
    {
        $this->setModel(new Supplier());

        $this->deleteData();
    }

    public function datas(): LengthAwarePaginator
    {
        return Supplier::query()
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('address', 'like', "%{$this->search}%");

            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'email', 'label' => 'Email', 'class' => 'w-64'],
            ['key' => 'phone', 'label' => 'Phone', 'class' => 'w-64'],
            ['key' => 'address', 'label' => 'Address', 'class' => 'w-64'],
            ['key' => 'created_at', 'label' => 'Created at', 'class' => 'w-64'],
        ];
    }

    public function with(): array
    {
        return [
            'datas' => $this->datas(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

@script
    <script>
        const deleteButton = document.getElementById('delete-button');

        $js('create', () => {
            deleteButton.style.display = 'none';
            
            $wire.recordId = null;
            $wire.name = '';
            $wire.email = '';
            $wire.phone = '';
            $wire.address = '';
            $wire.drawer = true;
        })

        $js('edit', (Supplier) => {
            deleteButton.style.display = 'block';
            
            $wire.recordId = Supplier.id;
            $wire.name = Supplier.name; 
            $wire.email = Supplier.email;
            $wire.phone = Supplier.phone;
            $wire.address = Supplier.address;
            $wire.drawer = true;
        })

        
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Suppliers" separator>
        <x-slot:actions>
            @can('create-supplier')
                <x-button label="Create" @click="$js.create" responsive icon="fas.plus" />
            @endcan
        </x-slot:actions>
    </x-header>

    <div class="flex justify-end items-center gap-5">
        <x-input placeholder="Search..." wire:model.live="search" clearable icon="o-magnifying-glass" />
    </div>

    <!-- TABLE  -->
    <x-card class="mt-4" shadow>
        <x-table :headers="$headers" :rows="$datas" :sort-by="$sortBy" per-page="perPage" :per-page-values="[10, 25, 50, 100]"
            with-pagination @row-click="$js.edit($event.detail)">
            @scope('cell_created_at', $data)
                <x-formatdate :date="$data->created_at" format="d F Y H:i" />
            @endscope
        </x-table>
    </x-card>

    <x-drawer wire:model="drawer" title="Form Supplier" class="w-11/12 lg:w-1/3" without-trap-focus right>
        <x-form wire:submit="save" no-separator>

            <div>
                <x-input label="Name" wire:model="name"  />
            </div>

            <div>
                <x-input label="Email" wire:model="email" type="email"  />
            </div>

            <div>
                <x-input label="Phone" wire:model="phone"  />
            </div>

            <div>
                <x-textarea label="Address" wire:model="address"  />
            </div>

            <x-slot:actions>
                @can('delete-supplier')
                    <div id="delete-button" style="display: none;">
                        <x-button label="Delete" wire:click="delete" class="btn-error text-white" responsive icon="fas.trash" wire:confirm="Are you sure?" spinner="delete" />
                    </div>
                @endcan
                
                <x-button label="Save" responsive icon="fas.save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div>
