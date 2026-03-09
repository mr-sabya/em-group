<?php

namespace App\Livewire\Admin\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property
use App\Models\Coupon;
use App\Models\Product;

class ProductAssignment extends Component
{
    use WithPagination;

    public $couponId;
    public $search = '';
    public $showModal = false;

    // This will hold the IDs of products currently assigned to this coupon
    public $assignedProductIds = [];

    // Listen for an event to open the modal
    protected $listeners = ['openProductAssignmentModal' => 'openModal'];

    /**
     * BEST WAY: Computed Property to get the current tenant ID.
     */
    #[Computed]
    public function currentTenantId()
    {
        return session('active_tenant_id');
    }

    public function openModal($couponId)
    {
        // Global Scope ensures we only find a coupon belonging to this tenant
        $coupon = Coupon::find($couponId);

        if (!$coupon) {
            $this->dispatch('notify', message: 'Access Denied.', type: 'danger');
            return;
        }

        $this->couponId = $couponId;
        $this->loadAssignedProducts();
        $this->resetPage();
        $this->reset('search');
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->dispatch('couponAssignmentsUpdated');
    }

    public function loadAssignedProducts()
    {
        if ($this->couponId) {
            $coupon = Coupon::find($this->couponId);
            $this->assignedProductIds = $coupon ? $coupon->products->pluck('id')->toArray() : [];
        } else {
            $this->assignedProductIds = [];
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function assignProduct($productId)
    {
        $coupon = Coupon::find($this->couponId);

        // Ensure the product also belongs to the current tenant
        $productExists = Product::where('id', $productId)->exists();

        if ($coupon && $productExists && !in_array($productId, $this->assignedProductIds)) {
            /**
             * FIXED: Explicitly pass the tenant_id to the pivot table
             */
            $coupon->products()->attach($productId, [
                'tenant_id' => $this->currentTenantId
            ]);

            $this->assignedProductIds[] = $productId;
            session()->flash('product_assignment_message', 'Product assigned successfully!');
        }
    }

    public function unassignProduct($productId)
    {
        $coupon = Coupon::find($this->couponId);
        if ($coupon && in_array($productId, $this->assignedProductIds)) {
            $coupon->products()->detach($productId);
            $this->assignedProductIds = array_diff($this->assignedProductIds, [$productId]);
            session()->flash('product_assignment_message', 'Product unassigned successfully!');
        }
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                /** 
                 * BEST WAY: Group OR terms to keep tenant security intact
                 */
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.coupon.product-assignment', [
            'products' => $products,
        ]);
    }
}
