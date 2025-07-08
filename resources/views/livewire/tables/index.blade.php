<?php

use App\Models\Table;
use Mary\Traits\Toast;
use Milon\Barcode\DNS2D;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Tables')] class extends Component {
    use Toast, WithPagination, CreateOrUpdate;

    public string $search = '';

    public bool $drawer = false;

    public int $perPage = 10;
    // public array $selected = [];
    public array $sortBy = ['column' => 'created_at', 'direction' => 'asc'];

    public string $qr_code = '';
    public string $number = '';
    public string $location = '';
    public int $capacity = 0;
    public bool $status = true;

    public function save(): void
    {
        $this->setModel(new Table());

        $this->saveOrUpdate(
            validationRules: [
                'number' => ['required', 'unique:tables,number,' . $this->recordId],
                'location' => ['required'],
                'capacity' => ['required'],
                'status' => ['required'],
            ],
        );
        $this->reset('qr_code', 'number', 'location', 'capacity', 'status');
    }

    public function delete(): void
    {
        $this->setModel(new Table());

        $this->deleteData();
    }

    public function datas(): LengthAwarePaginator
    {
        return Table::query()
            ->where(function ($query) {
                $query->where('number', 'like', "%{$this->search}%")
                    ->orWhere('location', 'like', "%{$this->search}%")
                    ->orWhere('qr_code', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'qr_code', 'label' => 'QR Code', 'class' => 'w-64'],
            ['key' => 'number', 'label' => 'Number', 'class' => 'w-64'],
            ['key' => 'location', 'label' => 'Location', 'class' => 'w-64'],
            ['key' => 'capacity', 'label' => 'Capacity', 'class' => 'w-64'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-64'],
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
        const qrCodeImage = document.getElementById('qr-code-image');
        const qrCodeText = document.getElementById('qr-code-text');

        $js('create', () => {
            deleteButton.style.display = 'none';
            qrCodeImage.style.display = 'none';
            qrCodeText.style.display = 'none';

            $wire.recordId = null;
            $wire.qr_code = '';
            $wire.number = '';
            $wire.location = '';
            $wire.capacity = 0;
            $wire.status = true;
            $wire.drawer = true;
        })

        $js('edit', (table) => {
            deleteButton.style.display = 'block';
            qrCodeImage.style.display = 'block';
            qrCodeImage.src = 'data:image/png;base64,' + table.qr_code_image;
            qrCodeText.style.display = 'block';
            qrCodeText.textContent = table.qr_code;

            $wire.recordId = table.id;
            $wire.qr_code = table.qr_code;
            $wire.number = table.number;
            $wire.location = table.location;
            $wire.capacity = table.capacity;
            $wire.status = table.status === 1;
            $wire.drawer = true;
        })

        
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Tables" separator>
        <x-slot:actions>
            @can('create-table')
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
            @scope('cell_qr_code', $data)
                <img  src="data:image/png;base64,{{ $data->qr_code_image }}" alt="qrcode{{ $data->qr_code }}" />
                {{ $data->qr_code }}
            @endscope
            @scope('cell_status', $data)
                <x-status :status="$data->status" />
            @endscope
            @scope('cell_created_at', $data)
                <x-formatdate :date="$data->created_at" format="d F Y H:i" />
            @endscope
        </x-table>
    </x-card>

    <x-drawer wire:model="drawer" title="Form Table" class="w-11/12 lg:w-1/3" without-trap-focus right>
        <x-form wire:submit="save" no-separator>

            <div >
                <img id="qr-code-image" src="" alt="qrcode"  style="display: none;" />
                <p id="qr-code-text" style="display: none;"></p>
            </div>

            <div>
                <x-input label="Number" wire:model="number"  />
            </div>

            <div>
                <x-input label="Location" wire:model="location"  />
            </div>

            <div>
                <x-input label="Capacity" wire:model="capacity"  />
            </div>

            <div class="my-3">
                <x-toggle label="Status" wire:model="status" hint="if checked, status will be active" />
            </div>

            <x-slot:actions>
                @can('delete-table')
                    <div id="delete-button" style="display: none;">
                        <x-button label="Delete" wire:click="delete" class="btn-error text-white" responsive icon="fas.trash" wire:confirm="Are you sure?" spinner="delete" />
                    </div>
                @endcan
                
                <x-button label="Save" responsive icon="fas.save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div>
