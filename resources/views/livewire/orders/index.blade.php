<?php

use Mary\Traits\Toast;
use App\Models\Addition;
use App\Models\Transaction;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Orders')] class extends Component {
    use Toast, WithPagination, CreateOrUpdate;

    public string $search = '';

    public bool $modalDetail = false;
    public bool $actionButton = false;
    public bool $paymentButton = false;
    public bool $modalPayment = false;

    public int $amount = 0;

    public int $perPage = 10;
    // public array $selected = [];
    public array $sortBy = ['column' => 'date', 'direction' => 'asc'];

    public string $date = '';
    public string $customer_name = '';


    public Collection $additions;

    public function mount(): void
    {
        //
    }

    public function action($action): void
    {
        $transaction = Transaction::find($this->recordId);

        try {
            DB::beginTransaction();

            if($action == 'approve') {
                $transaction->status = 'approved';
                $transaction->action_by = Auth::user()->id;
            } else {
                $transaction->status = 'rejected';
                $transaction->action_by = Auth::user()->id;
            }
            $transaction->save();

            DB::commit();

            $this->success('Transaction '.$action.' successfully.', position: 'toast-bottom');
            $this->actionButton = false;
        } catch (\Exception $th) {
            DB::rollBack();
            $this->error('Failed to '.$action.' transaction.', position: 'toast-bottom');
            $this->logError($th);
        }
    }

    public function storePayment(): void
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::find($this->recordId);

            $transaction->payment()->create([
                'amount' => $this->amount,
                'method' => 'cash',
                'change_amount' => $this->amount - $transaction->total_price,
            ]);

            $transaction->is_payment = true;
            $transaction->cashier_id = Auth::user()->id;
            $transaction->save();

            DB::commit();

            $this->success('Payment successfully.', position: 'toast-bottom');
            $this->modalPayment = false;
            $this->amount = 0;
            $this->paymentButton = false;
        } catch (\Exception $th) {
            DB::rollBack();
            $this->error('Failed to payment.', position: 'toast-bottom');
            $this->logError($th);
        }
    }

    public function save(): void
    {
        $this->setModel(new Transaction());

        $this->saveOrUpdate(
            validationRules: [
                'name' => ['required', 'unique:categories,name,' . $this->recordId],
            ],
            afterSave: function ($model, $component) {
                $model->additions()->sync($component->addition_ids ?? []);
            }
        );
        $this->reset('name');
    }

    public function delete(): void
    {
        $this->setModel(new Transaction());

        $this->deleteData();
    }

    public function datas(): LengthAwarePaginator
    {
        return Transaction::query()
            ->with('details.product', 'table', 'payment', 'cashier', 'actionBy')
            ->withAggregate('table', 'number')
            ->where(function ($query) {
                $query->where('invoice', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'date', 'label' => 'Date', 'class' => 'w-64'],
            ['key' => 'invoice', 'label' => 'Invoice', 'class' => 'w-64'],
            ['key' => 'table_number', 'label' => 'Table', 'class' => 'w-64'],
            ['key' => 'customer_name', 'label' => 'Customer Name', 'class' => 'w-64'],
            ['key' => 'total_price', 'label' => 'Total Price', 'class' => 'w-64'],
            ['key' => 'action_by', 'label' => 'Action By', 'class' => 'w-64'],
            ['key' => 'cashier_id', 'label' => 'Cashier', 'class' => 'w-64'],
            ['key' => 'is_payment', 'label' => 'Payment Status', 'class' => 'w-64'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-64'],
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

        $js('detail', (transaction) => {
            $wire.recordId = transaction.id;
            console.log(transaction);


            if(transaction.status != 'pending') {
                $wire.actionButton = false;
                if(transaction.status == 'approved' && transaction.is_payment == false) {
                    $wire.paymentButton = true;
                }
            } else {
                $wire.actionButton = true;
                $wire.paymentButton = false;
            }

            const customerInfo = document.getElementById('customer-info');
            const orderInfo = document.getElementById('order-info');
            const paymentInfo = document.getElementById('payment-info');
            const items = document.getElementById('items');



            customerInfo.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Customer Name</p>
                        <p class="font-medium">${transaction.customer_name || '-'}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Table Number</p>
                        <p class="font-medium">${transaction.table?.number || '-'}</p>
                    </div>
                </div>
            `;

            orderInfo.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Invoice</p>
                        <p class="font-medium">${transaction.invoice}</p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-sm text-gray-600">Date</p>
                        <p class="font-medium">${new Date(transaction.date).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-sm text-gray-600">Total Price</p>
                        <p class="font-medium">Rp ${Math.floor(transaction.total_price).toLocaleString('id-ID')}</p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-sm text-gray-600">Action By</p>
                        <p class="font-medium">${transaction.action_by?.name || '-'}</p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-sm text-gray-600">Cashier</p>
                        <p class="font-medium">${transaction.cashier?.name || '-'}</p>
                    </div>
                </div>
            `;

            paymentInfo.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Payment Status</p>
                        <p class="font-medium">${transaction.is_payment ? 'Paid' : 'Unpaid'}</p>
                    </div>
                    ${transaction.payment ? `
                        <div class="col-span-1">
                            <p class="text-sm text-gray-600">Payment Amount</p>
                            <p class="font-medium">Rp ${Math.floor(transaction.payment.amount).toLocaleString('id-ID')}</p>
                        </div>
                        <div class="col-span-1">
                            <p class="text-sm text-gray-600">Change Amount</p>
                            <p class="font-medium">Rp ${Math.floor(transaction.payment.change_amount).toLocaleString('id-ID')}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Payment Method</p>
                            <p class="font-medium">${transaction.payment.method.toUpperCase()}</p>
                        </div>
                    ` : ''}
                </div>
            `;

            items.innerHTML = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="sticky top-0 bg-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${transaction.details.map((detail, index) => `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 w-1/4">${detail.product.name}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 w-1/4">Rp ${Math.floor(detail.price).toLocaleString('id-ID')}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 w-1/4">${detail.qty}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 w-1/4">Rp ${Math.floor(detail.sub_price).toLocaleString('id-ID')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                        <tfoot class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp ${Math.floor(transaction.total_price).toLocaleString('id-ID')}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;

            $wire.modalDetail = true;
        })


    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Orders" separator>
        <x-slot:actions>
            @can('create-transaction')
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
            with-pagination @row-click="$js.detail($event.detail)" show-empty-text>
            @scope('cell_date', $data)
                <x-formatdate :date="$data->date" format="d F Y H:i" />
            @endscope
            @scope('cell_total_price', $data)
                <x-rupiah :value="$data->total_price" />
            @endscope
            @scope('cell_action_by', $data)
                {{ $data->actionBy->name ?? '-' }}
            @endscope
            @scope('cell_cashier_id', $data)
                {{ $data->cashier->name ?? '-' }}
            @endscope
            @scope('cell_is_payment', $data)
                <x-status :status="$data->is_payment ? 'paid' : 'unpaid'" />
            @endscope
            @scope('cell_status', $data)
                <x-status :status="$data->status" />
            @endscope
        </x-table>
    </x-card>

    <x-modal without-trap-focus title="Detail Order" wire:model="modalDetail" box-class="w-full h-fit max-w-[1000px]">
        <div wire:ignore>
            <div class="grid grid-cols-3 gap-4 w-full">
                <x-card title="Customer Info" class="bg-white">
                    <div id="customer-info">

                    </div>
                </x-card>
                <x-card title="Order Info" class="bg-white">
                    <div id="order-info">

                    </div>
                </x-card>
                <x-card title="Payment Info" class="bg-white">
                    <div id="payment-info">

                    </div>
                </x-card>
            </div>
            <x-card title="Items">
                <div id="items">

                </div>
            </x-card>
        </div>
        <x-slot:actions>
            <div x-data="{ action: false, payment: false }" x-effect="action = $wire.actionButton, payment = $wire.paymentButton">
                <div x-show="action">
                    <x-button label="Approve" icon="fas.check" class="btn-success" wire:click="action('approve')" spinner="action('approve')" />
                    <x-button label="Reject" icon="fas.xmark" class="btn-error" wire:click="action('reject')" spinner="action('reject')" />
                </div>
                <div x-show="payment">
                    <x-button label="Paid" icon="fas.check" class="btn-success"  @click="$wire.modalPayment = true" />
                </div>
            </div>
        </x-slot:actions>
    </x-modal>

    <x-modal without-trap-focus title="Payment" wire:model="modalPayment" box-class="w-full h-fit max-w-[500px]">
        <x-form wire:submit='storePayment'>
            <x-input label="Amount" type="number" wire:model='amount' prefix="Rp" />

            <x-slot:actions>
                <x-button label="Submit" type="submit" icon="fas.check" class="btn-primary" spinner="storePayment" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
