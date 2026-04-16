<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Courier;
use App\Enums\OrderStatus;
use App\Enums\OrderSource;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Manage extends Component
{
    public $orderId = null;
    public $isEditMode = false;

    // Form State
    public $data = [
        'name' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'customer_note' => '',
        'courier_note' => '',
        'source' => 'landing_page',
        'courier_id' => null,
        'delivery_fee' => 0,
        'discount' => 0,
        'coupon_discount' => 0,
        'subtotal' => 0,
        'total_amount' => 0,
        'paid_amount' => 0,
        'status' => 'pending',
        'user_id' => null, // The Agent/Admin
    ];

    public $orderItems = [];

    // Customer Search State
    public $customerSearch = '';
    public $searchResults = [];
    public $selectedCustomerId = null;
    public $showCustomerForm = false;

    public function mount($orderId = null)
    {
        $this->orderId = $orderId;

        if ($this->orderId) {
            $this->isEditMode = true;
            $order = Order::with(['orderItems', 'user'])->findOrFail($this->orderId);

            $this->data = $order->toArray();
            $this->data['status'] = $order->status->value;
            $this->data['source'] = $order->source->value;
            $this->selectedCustomerId = $order->user_id;
            $this->showCustomerForm = true;

            foreach ($order->orderItems as $item) {
                $this->orderItems[] = [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                ];
            }
        } else {
            $this->addItem();
            $this->data['user_id'] = Auth::id();
        }
    }

    // --- Customer Search Logic ---

    public function updatedCustomerSearch($value)
    {
        if (strlen($value) < 3) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = User::where('name', 'like', "%$value%")
            ->orWhere('phone', 'like', "%$value%")
            ->limit(5)->get();
    }

    public function selectCustomer($userId)
    {
        $user = User::with('info')->findOrFail($userId);
        $this->selectedCustomerId = $user->id;
        $this->data['name'] = $user->name;
        $this->data['phone'] = $user->phone;
        $this->data['email'] = $user->email;
        $this->data['address'] = $user->info->address ?? '';

        $this->showCustomerForm = true;
        $this->searchResults = [];
        $this->customerSearch = '';
    }

    public function createNewCustomer()
    {
        $this->selectedCustomerId = null;
        $this->data['phone'] = is_numeric($this->customerSearch) ? $this->customerSearch : '';
        $this->data['name'] = !is_numeric($this->customerSearch) ? $this->customerSearch : '';
        $this->showCustomerForm = true;
    }

    // --- Order Item Logic ---

    public function addItem()
    {
        $this->orderItems[] = ['product_id' => '', 'variant_id' => '', 'price' => 0, 'discount' => 0, 'quantity' => 1, 'total' => 0];
    }

    public function removeItem($index)
    {
        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems);
        $this->calculateTotals();
    }

    public function updatedOrderItems($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        if ($field === 'product_id') {
            $product = Product::find($value);
            $this->orderItems[$index]['price'] = $product->sale_price ?? $product->regular_price;
            $this->orderItems[$index]['variant_id'] = '';
        }

        if ($field === 'variant_id' && $value) {
            $variant = ProductVariant::find($value);
            $this->orderItems[$index]['price'] = $variant->sale_price ?? $variant->regular_price;
        }

        $item = &$this->orderItems[$index];
        $item['total'] = ($item['price'] - $item['discount']) * $item['quantity'];
        $this->calculateTotals();
    }

    public function updatedData()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->data['subtotal'] = collect($this->orderItems)->sum('total');
        $this->data['total_amount'] = ($this->data['subtotal'] + (float)$this->data['delivery_fee'])
            - (float)$this->data['discount']
            - (float)$this->data['coupon_discount'];
    }

    // --- Save Logic ---

    public function save()
    {
        $this->validate([
            'data.name' => 'required|string|max:255',
            'data.phone' => 'required|string',
            'data.address' => 'required|min:5',
            'orderItems.*.product_id' => 'required',
        ]);

        DB::transaction(function () {
            // 1. Handle User Creation/Link
            $userId = $this->selectedCustomerId;

            if (!$userId) {
                // Check if user exists by phone to avoid duplicates
                $existingUser = User::where('phone', $this->data['phone'])->first();

                if ($existingUser) {
                    $userId = $existingUser->id;
                } else {
                    $newUser = User::create([
                        'name' => $this->data['name'],
                        'phone' => $this->data['phone'],
                        'email' => $this->data['email'] ?: null,
                        'password' => Hash::make($this->data['phone']), // Phone as password
                    ]);

                    UserInfo::create([
                        'user_id' => $newUser->id,
                        'phone' => $this->data['phone'],
                        'address' => $this->data['address'],
                    ]);

                    $userId = $newUser->id;
                }
            }

            // 2. Save Order
            $order = Order::updateOrCreate(
                ['id' => $this->orderId],
                array_merge($this->data, [
                    'user_id' => $userId, // Linked customer
                    'admin_id' => Auth::id(), // Current admin
                    'tenant_id' => session('active_tenant_id')
                ])
            );

            // 3. Sync Items
            $order->orderItems()->delete();
            foreach ($this->orderItems as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create(array_merge($item, [
                    'order_id' => $order->id,
                    'item_name' => $product->name,
                    'tenant_id' => session('active_tenant_id')
                ]));
            }
        });

        session()->flash('message', 'Order saved successfully.');
        return redirect()->route('admin.orders.index');
    }

    public function render()
    {
        return view('livewire.admin.orders.manage', [
            'products' => Product::active()->get(),
            'couriers' => Courier::all(),
            'statuses' => OrderStatus::cases(),
            'sources' => OrderSource::cases(),
        ]);
    }
}
