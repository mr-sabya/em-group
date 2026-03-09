<?php

namespace App\Livewire\Admin\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property
use App\Models\Coupon;
use App\Models\Category;

class CategoryAssignment extends Component
{
    use WithPagination;

    public $couponId;
    public $search = '';
    public $showModal = false;
    public $assignedCategoryIds = [];

    protected $listeners = ['openCategoryAssignmentModal' => 'openModal'];

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
        // Global Scope in Coupon model ensures we only find a coupon belonging to this tenant
        $coupon = Coupon::find($couponId);

        if (!$coupon) {
            $this->dispatch('notify', message: 'Access Denied or Coupon not found.', type: 'danger');
            return;
        }

        $this->couponId = $couponId;
        $this->loadAssignedCategories();
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

    public function loadAssignedCategories()
    {
        if ($this->couponId) {
            $coupon = Coupon::find($this->couponId);
            $this->assignedCategoryIds = $coupon ? $coupon->categories->pluck('id')->toArray() : [];
        } else {
            $this->assignedCategoryIds = [];
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function assignCategory($categoryId)
    {
        $coupon = Coupon::find($this->couponId);

        // Category::find ensures the category also belongs to the current tenant
        $categoryExists = Category::where('id', $categoryId)->exists();

        if ($coupon && $categoryExists && !in_array($categoryId, $this->assignedCategoryIds)) {
            /**
             * FIXED: Explicitly pass the tenant_id to the pivot table (coupon_category)
             */
            $coupon->categories()->attach($categoryId, [
                'tenant_id' => $this->currentTenantId
            ]);

            $this->assignedCategoryIds[] = $categoryId;
            session()->flash('category_assignment_message', 'Category assigned successfully!');
        }
    }

    public function unassignCategory($categoryId)
    {
        $coupon = Coupon::find($this->couponId);
        if ($coupon && in_array($categoryId, $this->assignedCategoryIds)) {
            $coupon->categories()->detach($categoryId);
            $this->assignedCategoryIds = array_diff($this->assignedCategoryIds, [$categoryId]);
            session()->flash('category_assignment_message', 'Category unassigned successfully!');
        }
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search, function ($query) {
                /** 
                 * BEST WAY: Group OR terms in a closure.
                 * Prevents search from bypassing the Global Tenant Scope.
                 */
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.coupon.category-assignment', [
            'categories' => $categories,
        ]);
    }
}
