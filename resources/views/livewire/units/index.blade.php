<?php

use Mary\Traits\Toast;
use App\Models\Unit;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Units')] class extends Component {
    use Toast, WithPagination, CreateOrUpdate;

    public string $search = '';

    public bool $drawer = false;

    public int $perPage = 10;
    // public array $selected = [];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public string $name = '';

    public function save(): void
    {
        $this->setModel(new Unit());

        $this->saveOrUpdate(
            validationRules: [
                'name' => ['required', 'unique:units,name,' . $this->recordId],
            ],
        );
        $this->reset('name');
    }

    public function delete(): void
    {
        $this->setModel(new Unit());

        $this->deleteData();
    }

    public function datas(): LengthAwarePaginator
    {
        return Unit::query()
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
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
            $wire.drawer = true;
        })

        $js('edit', (Unit) => {
            deleteButton.style.display = 'block';
            
            $wire.recordId = Unit.id;
            $wire.name = Unit.name; 
            $wire.drawer = true;
        })

        
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Units" separator>
        <x-slot:actions>
            @can('create-unit')
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

    <x-drawer wire:model="drawer" title="Form Unit" class="w-11/12 lg:w-1/3" without-trap-focus right>
        <x-form wire:submit="save" no-separator>

            <div>
                <x-input label="Name" wire:model="name"  />
            </div>

            <x-slot:actions>
                @can('delete-unit')
                    <div id="delete-button" style="display: none;">
                        <x-button label="Delete" wire:click="delete" class="btn-error text-white" responsive icon="fas.trash" wire:confirm="Are you sure?" spinner="delete" />
                    </div>
                @endcan
                
                <x-button label="Save" responsive icon="fas.save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div>
