<?php

use App\Models\Unit;
use Mary\Traits\Toast;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Milon\Barcode\DNS1D;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Products')] class extends Component {
    use Toast, WithPagination, CreateOrUpdate, WithFileUploads;

    public string $search = '';
    public array $config = [
        'guides' => true,
        'aspectRatio' => 1,
    ];

    public bool $drawer = false;

    public int $perPage = 10;
    // public array $selected = [];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public string $barcode = '';
    public string $name = '';
    public string $description = '';
    public float $price_buy = 0;
    public float $price_sell = 0;
    public int $stock = 0;
    public ?int $category_id = null;
    public ?int $unit_id = null;
    public ?int $supplier_id = null;
    public bool $status = true;
    public string $oldImage = 'img/upload.png';
    public ?UploadedFile $file = null;

    public Collection $categories;
    public Collection $units;
    public Collection $suppliers;


    public function mount(): void
    {
        $this->searchCategory();
        $this->searchUnit();
        $this->searchSupplier();
    }

    public function searchCategory(string $value = '')
    {
        $selectedOptions = Category::where('id', $this->category_id)->get();

        $this->categories = Category::query()
            ->where('name', 'like', "%{$value}%")
            ->orderBy('name', 'asc')
            ->get()
            ->merge($selectedOptions);
    }

    public function searchUnit(string $value = '')
    {
        $selectedOptions = Unit::where('id', $this->unit_id)->get();

        $this->units = Unit::query()
            ->where('name', 'like', "%{$value}%")
            ->orderBy('name', 'asc')
            ->get()
            ->merge($selectedOptions);
    }

    public function searchSupplier(string $value = '')
    {
        $selectedOptions = Supplier::where('id', $this->supplier_id)->get();

        $this->suppliers = Supplier::query()
            ->where('name', 'like', "%{$value}%")
            ->orderBy('name', 'asc')
            ->get()
            ->merge($selectedOptions);
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'unique:products,barcode,' . $this->recordId],
            'name' => ['required', 'unique:products,name,' . $this->recordId],
            'description' => ['required', 'string'],
            'price_buy' => ['required', 'numeric', 'min:0'],
            'price_sell' => ['required', 'numeric', 'min:0', 'gte:price_buy'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'status' => ['boolean'],
            'file' => ['nullable', 'image', 'max:2048'],
        ];
    }


    public function save(): void
    {
        $this->setModel(new Product());

        $this->saveOrUpdate(
            validationRules: $this->rules(),
            beforeSave: function ($model, $component) {
                if ($component->file) {
                    if (Storage::disk('public')->exists($component->oldImage)) {
                        Storage::disk('public')->delete($component->oldImage);
                    }

                    $model->image = $component->file->store(path: 'images/products', options: 'public');
                }
            },  
        );
        $this->reset('name', 'barcode', 'description', 'price_buy', 'price_sell', 'stock', 'category_id', 'unit_id', 'supplier_id', 'status', 'file', 'oldImage');
    }

    public function delete(): void
    {
        $this->setModel(new Product());

        $this->deleteData(
            beforeDelete: function ($id, $component) {
                $product = Product::find($id);
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
            },
        );
    }

    public function datas(): LengthAwarePaginator
    {
        return Product::query()
            ->withAggregate('category', 'name')
            ->withAggregate('unit', 'name')
            ->withAggregate('supplier', 'name')
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%")
                    ->orWhereHas('category', function ($query) {
                        $query->where('name', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('unit', function ($query) {
                        $query->where('name', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('supplier', function ($query) {
                        $query->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'barcode', 'label' => 'Barcode', 'class' => 'w-64'],
            ['key' => 'image', 'label' => 'Image', 'class' => 'w-64'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'category_name', 'label' => 'Category', 'class' => 'w-64'],
            ['key' => 'unit_name', 'label' => 'Unit', 'class' => 'w-64'],
            ['key' => 'supplier_name', 'label' => 'Supplier', 'class' => 'w-64'],
            ['key' => 'price_buy', 'label' => 'Price Buy', 'class' => 'w-64'],
            ['key' => 'price_sell', 'label' => 'Price Sell', 'class' => 'w-64'],
            ['key' => 'stock', 'label' => 'Stock', 'class' => 'w-64'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-64'],
            // ['key' => 'created_at', 'label' => 'Created at', 'class' => 'w-64'],
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
        const previewImage = document.getElementById('previewImage');
        const barcodeImage = document.getElementById('barcode-image');
        const barcodeText = document.getElementById('barcode-text');


        $js('create', () => {
            deleteButton.style.display = 'none';
            previewImage.src = 'img/upload.png';
            barcodeImage.style.display = 'none';
            barcodeText.style.display = 'none';
            
            $wire.recordId = null;
            $wire.barcode = '';
            $wire.name = '';
            $wire.description = '';
            $wire.price_buy = 0;
            $wire.price_sell = 0;
            $wire.stock = 0;
            $wire.category_id = null;
            $wire.unit_id = null;
            $wire.supplier_id = null;
            $wire.status = true;
            $wire.file = null;
            $wire.drawer = true;
        })

        $js('edit', (Product) => {
            deleteButton.style.display = 'block';
            previewImage.src = Product.image ? '/storage/' + Product.image : 'img/upload.png';
            barcodeImage.style.display = 'block';
            barcodeText.style.display = 'block';
            barcodeImage.src = 'data:image/png;base64,' + Product.barcode_image;
            barcodeText.textContent = Product.barcode;
            
            $wire.recordId = Product.id;
            $wire.name = Product.name; 
            $wire.barcode = Product.barcode;
            $wire.description = Product.description;
            $wire.price_buy = parseInt(Product.price_buy);
            $wire.price_sell = parseInt(Product.price_sell);
            $wire.stock = Product.stock;
            $wire.category_id = Product.category_id;
            $wire.unit_id = Product.unit_id;
            $wire.supplier_id = Product.supplier_id;
            $wire.status = Product.status === 1;
            $wire.file = null;
            $wire.drawer = true;
        })
        
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Products" separator>
        <x-slot:actions>
            @can('create-product')
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
            @scope('cell_barcode', $data)
                <img  src="data:image/png;base64,{{ $data->barcode_image }}" alt="barcode{{ $data->barcode }}" />
            {{ $data->barcode }}
            @endscope
            @scope('cell_image', $data)
                <x-avatar :image="$data->image ? asset('storage/'.$data->image) : asset('img/upload.png')" class="!w-14 !rounded-lg" />
            @endscope
            @scope('cell_price_buy', $data)
                <x-rupiah :value="$data->price_buy" />
            @endscope
            @scope('cell_price_sell', $data)
                <x-rupiah :value="$data->price_sell" />
            @endscope
            @scope('cell_status', $data)
                <x-status :status="$data->status" />
            @endscope
            {{-- @scope('cell_created_at', $data)
                <x-formatdate :date="$data->created_at" format="d F Y H:i" />
            @endscope --}}
        </x-table>
    </x-card>

    <x-drawer wire:model="drawer" title="Form Product" class="w-11/12 lg:w-1/3" without-trap-focus right>
        <x-form wire:submit="save" no-separator>

            <div>
                <x-file label='Image' wire:model="file" accept="image/png, image/jpeg, image/jpg, image/webp" crop-after-change
                change-text="Change" crop-text="Crop" crop-title-text="Crop image" crop-cancel-text="Cancel"
                crop-save-text="Crop" :crop-config="$config" hint="image size max 5mb">
                    <img id="previewImage" src="" class="h-40 rounded-lg"  />
                </x-file>
                <div class="mt-4">
                    <img id="barcode-image" src="" alt="barcode"  style="display: none;" />
                    <p id="barcode-text" style="display: none;"></p>
                </div>
            </div>

            <div>
                <x-input label="Name" wire:model="name"  />
            </div>

            <div>
                <x-textarea label="Description" wire:model="description" rows='3' />
            </div>

            <div class="grid lg:grid-cols-2 gap-4">
                <div>
                    <x-input label="Price Buy" wire:model="price_buy" prefix="Rp" />
                </div>
    
                <div>
                    <x-input label="Price Sell" wire:model="price_sell" prefix="Rp" />
                </div>
            </div>
            <div class="grid lg:grid-cols-2 gap-4">
                <div>
                    <x-choices
                    label="Category"
                    wire:model="category_id"
                    :options="$categories"
                    search-function="searchCategory"
                    placeholder="Search ..."
                    single
                    clearable
                    searchable />
                </div>
    
                <div>
                    <x-choices
                    label="Supplier"
                    wire:model="supplier_id"
                    :options="$suppliers"
                    search-function="searchSupplier"
                    placeholder="Search ..."
                    single
                    clearable
                    searchable />
                </div>
            </div>
            <div class="grid lg:grid-cols-2 gap-4">
                <div>
                    <x-input label="Stock" wire:model="stock" type="number" />
                </div>
    
                <div>
                    <x-choices
                    label="Unit"
                    wire:model="unit_id"
                    :options="$units"
                    search-function="searchUnit"
                    placeholder="Search ..."
                    single
                    clearable
                    searchable />
                </div>
            </div>

            <div class="my-3">
                <x-toggle label="Status" wire:model="status" hint="if checked, status will be active" />
            </div>

            <x-slot:actions>
                @can('delete-product')
                    <div id="delete-button" style="display: none;">
                        <x-button label="Delete" wire:click="delete" class="btn-error text-white" responsive icon="fas.trash" wire:confirm="Are you sure?" spinner="delete" />
                    </div>
                @endcan
                
                <x-button label="Save" responsive icon="fas.save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div>
