<?php

use Mary\Traits\Toast;
use App\Models\Addition;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\AdditionItem;

new #[Title('Additions')] class extends Component {
    use Toast, WithPagination, CreateOrUpdate;

    public string $search = '';

    public bool $drawer = false;

    public int $perPage = 10;
    // public array $selected = [];
    public array $sortBy = ['column' => 'label', 'direction' => 'asc'];

    public string $label = '';
    public bool $is_required = true;
    public bool $is_multiple = false;

    // public ?int $id = null;
    // public bool $createItem = false;
    public string $name = '';
    public float $price = 0;

    public array $items = [];

    public function save(): void
    {
        $this->setModel(new Addition());

        $this->saveOrUpdate(
            validationRules: [
                'label' => ['required', 'unique:additions,label,' . $this->recordId],
                'is_required' => ['required', 'boolean']
            ],
        );

        $this->reset('label', 'is_required');
    }

    public function saveItem(): void
    {
        $this->validate([
            'name' => ['required', 'string'],
            'price' => ['nullable', 'numeric']
        ]);

        try {
            DB::beginTransaction();

            $addition = Addition::find($this->recordId);
            $addition->items()->create([
                'addition_id' => $this->recordId,
                'name' => $this->name,
                'price' => $this->price,
            ]);

            DB::commit();

            $this->success('Item created.', position: 'toast-bottom');
            $this->reset('name', 'price');
            $this->items = $addition->items->toArray();
        } catch (\Exception $th) {
            $this->error('System Error Occurred', position: 'toast-bottom');
            $this->logError($th);
        }
    }

    public function delete(): void
    {
        $this->setModel(new Addition());

        $this->deleteData();
    }

    public function deleteItem($id): void
    {
        try {
            DB::beginTransaction();

            AdditionItem::query()->where('id', $id)->delete();

            DB::commit();

            $this->success('Category deleted successfully', position: 'toast-bottom');
            $this->dataItems($this->recordId);
        } catch (\Exception $th) {
            DB::rollBack();
            $this->error('Error deleting Category', position: 'toast-bottom');
            Log::channel('debug')->error($th->getMessage());
        }
    }

    public function datas(): LengthAwarePaginator
    {
        return Addition::query()
            ->with('items')
            ->where(function ($query) {
                $query->where('label', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function dataItems($id): void
    {
        $this->items = AdditionItem::where('addition_id', $id)->get()->toArray() ?? [];
    }

    public function headers(): array
    {
        return [
            ['key' => 'label', 'label' => 'Label', 'class' => 'w-64'],
            ['key' => 'is_required', 'label' => 'Is Required', 'class' => 'w-64'],
            ['key' => 'is_multiple', 'label' => 'Is Multiple', 'class' => 'w-64'],
            ['key' => 'created_at', 'label' => 'Created at', 'class' => 'w-64'],
        ];
    }

    public function with(): array
    {
        return [
            'datas' => $this->datas(),
            'headers' => $this->headers(),
            // 'dataItems' => $this->dataItems()
        ];
    }
}; ?>

@script
    <script>
        const deleteButton = document.getElementById('delete-button');

        $js('create', () => {
            deleteButton.style.display = 'none';

            $wire.recordId = null;
            $wire.label = '';
            $wire.is_required = true;
            $wire.is_multiple = false;
            // $wire.items = [];
            $wire.drawer = true;
        })

        $js('edit', (Addition) => {
            deleteButton.style.display = 'block';
            // console.log(Addition.items);

            $wire.recordId = Addition.id;
            $wire.label = Addition.label;
            $wire.is_required = Addition.is_required == 1;
            $wire.is_multiple = Addition.is_multiple == 1;
            $wire.dataItems(Addition.id);
            // $wire.$refresh();

            $wire.drawer = true;
        })

        $js('deleteItem', (id) => {
            $wire.deleteItem(id);
        })


    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Additions" separator>
        <x-slot:actions>
            @can('create-addition')
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
            @scope('cell_is_required', $data)
                {{ $data->is_required ? 'Yes' : 'No' }}
            @endscope
            @scope('cell_is_multiple', $data)
                {{ $data->is_multiple ? 'Yes' : 'No' }}
            @endscope
            @scope('cell_created_at', $data)
                <x-formatdate :date="$data->created_at" format="d F Y H:i" />
            @endscope
        </x-table>
    </x-card>

    <x-drawer wire:model="drawer" title="Form Addition" class="w-11/12 lg:w-1/3" without-trap-focus right separator>
        <x-form wire:submit="save" no-separator>

            <div>
                <x-input label="Label" wire:model="label"  />
            </div>

            <div class="my-3">
                <x-toggle label="Is Required?" wire:model="is_required" hint="if checked, is required" />
            </div>

            <div class="my-3">
                <x-toggle label="Is Multiple?" wire:model="is_multiple" hint="if checked, is multiple" />
            </div>

            <x-slot:actions>
                @can('delete-addition')
                    <div id="delete-button" style="display: none;" wire:ignore>
                        <x-button label="Delete" wire:click="delete" class="btn-error text-white" responsive icon="fas.trash" wire:confirm="Are you sure?" spinner="delete" />
                    </div>
                @endcan

                <x-button label="Save" responsive icon="fas.save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
        <div class="mt-4"
            x-data="{ show: false, create: false }"
            x-effect="show = $wire.recordId"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90">
            <div x-show="show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2">
                <x-header title="Addition Items" size="text-xl" separator>
                    <x-slot:actions>
                        <x-button
                            label="Create"
                            @click="create = !create"
                            responsive
                            icon="fas.plus"
                            class="btn-primary" />
                    </x-slot:actions>
                </x-header>

                <x-form
                    wire:submit='saveItem'
                    x-show="create"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform translate-y-2">
                    <x-input label='Name' wire:model='name' />
                    <x-input label='Price' wire:model='price' prefix="Rp"/>

                    <x-slot:actions>
                        <x-button label="Save Item" responsive icon="fas.save" type="submit" spinner="saveItem" class="btn-primary" />
                    </x-slot:actions>
                </x-form>
                <div>
                    <x-progress wire:loading wire:target='dataItems, deleteItem, saveItem' class="progress-primary h-0.5" indeterminate />
                </div>
                <div wire:loading.remove='dataItems'>
                    <x-table :headers="[
                        ['key' => 'name', 'label' => 'Name'],
                        ['key' => 'price', 'label' => 'Price']
                    ]" :rows="$items" show-empty-text >
                    @scope('actions', $data)
                        <x-button class="btn-sm bg-error" icon="o-trash" wire:click="deleteItem({{ $data['id'] }})" spinner="deleteItem{{ $data['id'] }}" />
                    @endscope
                    </x-table>
                </div>
            </div>
        </div>
    </x-drawer>
</div>
