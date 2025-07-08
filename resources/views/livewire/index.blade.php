<?php

use App\Models\Table;
use App\Models\Product;
use App\Models\Category;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;

new #[Layout('components.layouts.fe')] class extends Component {

    public string $search = '';

    public bool $drawerCart = false;
    public bool $modalDetail = false;
    public bool $modalWarning = false;
    // public $tables;
    public $cart = [];
    public ?Table $table = null;

    public string $customer_name = '';
    public string $date = '';

    #[Url]
    public $code;

    public bool $allowTransaction = false;

    public ?int $category_id = null;
    public Collection $categories;

    public array $additions = [];

    public function mount(): void
    {
        $this->date = now();
        $table_code = Table::where('qr_code', base64_decode($this->code))->where('status', true)->first();
        if ($table_code) {
            $this->table = $table_code;
            $this->allowTransaction = true;
            // $this->dispatch('remove-url-param', param: 'code');
        } else {
            $this->modalWarning = true;
        }

        $this->searchCategory();
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

    public function store(): void
    {
        //
    }

    public function products()
    {
        return Product::query()
            ->withAggregate('category', 'name')
            ->withAggregate('unit', 'name')
            ->where('status', true)
            ->when($this->category_id, function ($query) {
                $query->where('category_id', $this->category_id);
            })
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%")
                    ->orWhereHas('category', function ($query) {
                        $query->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->paginate(12);
    }

    public function headers(): array
    {
        return [
            ['key' => 'product_name', 'label' => 'Product Name' ],
            ['key' => 'qty', 'label' => 'Qty'],
            ['key' => 'price', 'label' => 'Price'],
            ['key' => 'total', 'label' => 'Total'],
        ];
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'products' => $this->products(),
        ];
    }



}; ?>

@script
    <script>
        window.removeUrlParam = (param) => {
            const url = new URL(window.location);
            url.searchParams.delete(param);
            window.history.replaceState({}, '', url);
        };

        $js('countCart', () => {
            const cartBadge = document.getElementById('cart-badge');
            const cart = $wire.cart;
            cartBadge.innerText = cart.length ?? 0;
        })

        $js('resetCart', () => {
            $wire.cart = [];
            // $wire.$refresh();
            $js.countCart();
            $js.cart();
        })

        $js('incrementQty', (id) => {
            const cart = $wire.cart;
            const item = cart.find(i => i.id === id);
            if (item) {
                item.qty++;
                item.total = item.qty * item.price;
            }
            $js.cart();
        });

        $js('decrementQty', (id) => {
            const cart = $wire.cart;
            const item = cart.find(i => i.id === id);
            if (item && item.qty > 1) {
                item.qty--;
                item.total = item.qty * item.price;
            } else {
                $js.removeFromCart(id);
                return;
            }
            $js.cart();
        });

        $js('removeFromCart', (id) => {
            const cart = $wire.cart;
            const index = cart.findIndex(i => i.id === id);
            if (index !== -1) {
                cart.splice(index, 1);
            }
            $js.cart();
            $js.countCart();
        });

        $js('drawerCart', () => {
            $wire.drawerCart = !$wire.drawerCart;
            $js.cart();
        })

        $js('cart', () => {
            const cartList = document.getElementById('cart-items');
            const cart = $wire.cart;


            if (!cart || cart.length === 0) {
                cartList.innerHTML = `
                    <div class="py-8 text-center text-gray-500">
                        <x-icon name="o-shopping-cart" class="mx-auto h-12 w-12" />
                        <p class="mt-2">Your cart is empty</p>
                    </div>
                `;
                return;
            }

            cartList.innerHTML = cart.map(item => `
                <div class="py-4 border-b">
                    <div class="text-sm text-gray-500 mb-2">
                        <span>Item Details:</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">${item.name}</h3>
                            <p class="text-sm text-gray-500">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="btn btn-sm" @click="$js.decrementQty(${item.id})">
                                <x-icon name="o-minus" class="w-4 h-4" />
                            </button>
                            <span id="qty">${item.qty}</span>
                            <button class="btn btn-sm" @click="$js.incrementQty(${item.id})">
                                <x-icon name="o-plus" class="w-4 h-4" />
                            </button>
                            <button class="btn btn-sm btn-error" @click="$js.removeFromCart(${item.id})">
                                <x-icon name="o-trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-right font-medium">Total: Rp ${new Intl.NumberFormat('id-ID').format(item.qty * item.price)}</p>
                </div>
            `).join('');

            // Hitung total semua item (jumlah total harga)
            const total = cart.reduce((acc, item) => acc + (item.qty * item.price), 0);

            // Tambahkan footer dengan total
            cartList.innerHTML += `
                <div class="py-4 mt-4 font-semibold">
                    <div class="flex items-center justify-between text-lg">
                        <span>Total Amount:</span>
                        <span class="text-primary text-xl font-bold">Rp ${new Intl.NumberFormat('id-ID').format(total)}</span>
                    </div>
                </div>
            `;
        });

        $js('detail', (product) => {
            const image = document.getElementById('modal-product-image');
            const name = document.getElementById('modal-product-name');
            const category = document.getElementById('modal-product-category');
            const description = document.getElementById('modal-product-description');
            const price = document.getElementById('modal-product-price');
            const unit = document.getElementById('modal-product-unit');
            const actions = document.getElementById('modal-product-actions');


            image.src = product.image ? '/storage/' + product.image : 'img/upload.png';
            name.innerText = product.name;
            category.innerText = product.category_name;
            description.innerText = product.description;
            price.innerText = 'Rp.' + new Intl.NumberFormat('id-ID').format(Math.floor(product.price_sell));
            unit.innerText = product.unit_name;

            $wire.modalDetail = true;

            actions.innerHTML = `
            <div x-data='${JSON.stringify({ product })}'>
                <div x-data="{check: false}" x-effect="check = $wire.cart.some(item => item.id === product.id)">
                    <button
                        @click="
                            $js.addToCart(product, () => check = true)
                        "
                        x-show="!check"
                        class="btn-primary btn"
                    >
                        <x-icon name="o-shopping-cart" class="w-4 h-4 inline" /> Add to Cart
                    </button>
                    <x-button
                        label="Already in Cart"
                        x-show="check"
                        icon="o-check-circle"
                        class="btn-success btn"
                    />
                </div>
                </div>
            `;
        });

        $js('addToCart', async (product, done) => {
            const cart = $wire.cart;

            if (cart.length === 0) {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price_sell,
                    qty: 1,
                    additions: additions,
                    total: product.price_sell,
                });
            } else {
                const existingItem = cart.find(item => item.id === product.id);
                if (existingItem) {
                    existingItem.qty++;
                    existingItem.total = existingItem.price * existingItem.qty;
                } else {
                    cart.push({
                        id: product.id,
                        name: product.name,
                        price: product.price_sell,
                        qty: 1,
                        total: product.price_sell,
                    });
                }
            }

            $js.countCart();

            if (typeof done === 'function') done();
        });


        Livewire.on('remove-url-param', ({ param }) => {
            removeUrlParam(param);
        });
    </script>
@endscript

<div>
    {{-- The navbar with `sticky` and `full-width` --}}
    <x-nav sticky shadow>

        <x-slot:brand>
            {{-- Drawer toggle for "main-drawer" --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>

            {{-- Brand --}}
            <x-app-brand />
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            <x-badge value="Table Number: {{ $table->number ?? '0' }}" />

            <button class="btn btn-ghost btn-sm" @click="$js.drawerCart" id="cart-button" wire:ignore>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                <span class="hidden sm:inline ml-2">Cart</span>
                <span class="badge badge-warning ml-2" id="cart-badge" wire:ignore>0</span>
            </button>
        </x-slot:actions>
    </x-nav>
    {{-- MAIN --}}
    <x-main with-nav>
        {{-- The `$slot` goes here --}}
        <x-slot:content>

            <div class="flex justify-center items-center">
                <div class="w-[200px]">
                    <x-choices
                    {{-- label="Category" --}}
                    wire:model.live="category_id"
                    :options="$categories"
                    search-function="searchCategory"
                    placeholder="Search Category ..."
                    {{-- @change-selection="$js.filterByCategory($event.detail.value)" --}}
                    single
                    clearable
                    searchable />
                    <x-progress wire:loading wire:target="category_id" class="progress-primary h-0.5" indeterminate />
                </div>
            </div>
            <div class="grid lg:grid-cols-4 gap-4 mt-4">
                @if($products)
                    @forelse ($products as $product)
                    <x-card title="{{ $product->name }}" subtitle="{{ $product->category_name }}">
                    {{ Str::limit($product->description, 30, '...') }}

                        <x-slot:figure>
                            <div @click="$js.detail({{ $product }})" class="cursor-pointer">
                                @if ($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}"  />
                                @else
                                <img src="{{ asset('img/upload.png') }}" />
                                @endif
                            </div>
                        </x-slot:figure>
                        <x-slot:menu>
                            <x-rupiah :value="$product->price_sell" />
                        </x-slot:menu>
                        <x-slot:actions separator>
                            <div x-data="{ show: false }" x-effect="show = $wire.allowTransaction">
                                <div x-show="show" x-data="{ check: false }" x-effect="check = $wire.cart.some(item => item.id === {{ $product->id }})">
                                    <x-button
                                        @click="
                                            $js.detail({{ $product }}, () => check = true)
                                        "
                                        x-show="!check"
                                        icon="o-shopping-cart"
                                        class="btn-primary btn-sm"
                                    />
                                    <x-button
                                        x-show="check"
                                        icon="o-check-circle"
                                        class="btn-success btn-sm"
                                    />
                                </div>
                            </div>
                        </x-slot:actions>
                    </x-card>
                    @empty
                        <div class="col-span-4 text-center py-4">
                            No products found
                        </div>
                    @endforelse
                @endif
            </div>
        </x-slot:content>
    </x-main>

    <x-drawer wire:model="drawerCart" title="Cart" class="w-11/12 lg:w-1/3" without-trap-focus right>
        <div>
            <x-datepicker label="Date" wire:model="date" icon="o-calendar" :config="[
                'altFormat' => 'd F Y H:i',
                'locale' => 'id'
            ]" inline readonly/>
        </div>
        <div class="mt-4">
            <x-input label="Name" wire:model='customer_name' inline placeholder="Name" required />
        </div>

        <div class="divide-y" >

        </div>

        <x-slot:actions>
            <div x-data="{ show: false }" x-effect="show = $wire.cart.length > 0">
                <x-button x-show="show" label="Reset" responsive class="btn-error" @click="$js.resetCart" />
                <x-button x-show="show" label="Order" responsive icon="fas.check" spinner="store" class="btn-primary" />
            </div>

        </x-slot:actions>
    </x-drawer>

    <x-modal wire:model="modalDetail" title="Detail" box-class="w-full h-fit max-w-[600px]" without-trap-focus>
        <div class="space-y-4">
            <div class="flex items-center justify-center">
                <img src="" alt="Product Image" class="object-cover rounded-lg" id="modal-product-image">
            </div>

            <div class="space-y-2">
                <h3 class="text-xl font-semibold" id="modal-product-name"></h3>
                <p class="text-gray-600" id="modal-product-category"></p>
                <p class="text-sm text-gray-500" id="modal-product-description"></p>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-lg font-bold text-primary" id="modal-product-price"></span>
                    <span class="text-sm text-gray-500" id="modal-product-unit"></span>
                </div>
            </div>
        </div>
        <div class="space-y-4" id="addition">

        </div>

        <x-slot:actions>
            <div x-data="{ show: false }" x-effect="show = $wire.allowTransaction">
                <div x-show="show" class="flex justify-between w-full" id="modal-product-actions">

                </div>
            </div>
        </x-slot:actions>
    </x-modal>

    <x-modal-warning />
</div>
