<?php

namespace App\Livewire\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
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

    public function openModal($couponId)
    {
        $this->couponId = $couponId;
        $this->loadAssignedProducts();
        $this->resetPage(); // Reset pagination when opening for a new coupon
        $this->reset('search'); // Clear search when opening
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        // You might want to emit an event here to notify the parent (CouponIndex)
        // that assignments might have changed, so it can refresh its display.
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
        if ($coupon && !in_array($productId, $this->assignedProductIds)) {
            $coupon->products()->attach($productId);
            $this->assignedProductIds[] = $productId; // Update local state
            session()->flash('product_assignment_message', 'Product assigned successfully!');
        }
    }

    public function unassignProduct($productId)
    {
        $coupon = Coupon::find($this->couponId);
        if ($coupon && in_array($productId, $this->assignedProductIds)) {
            $coupon->products()->detach($productId);
            $this->assignedProductIds = array_diff($this->assignedProductIds, [$productId]); // Update local state
            session()->flash('product_assignment_message', 'Product unassigned successfully!');
        }
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.coupon.product-assignment', [
            'products' => $products,
        ]);
    }
}
