<?php

namespace App\Livewire\Admin\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property
use App\Models\Coupon;
use App\Models\Tenant;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'code';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Used to pass the couponId to the assignment modals
    public $activeCouponId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'code'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    // Listen for the event emitted by assignment modals when they close
    protected $listeners = ['couponAssignmentsUpdated' => '$refresh'];

    /**
     * BEST WAY: Computed Property to get the current tenant info.
     * Accessible in Blade via $this->currentTenant
     */
    #[Computed]
    public function currentTenant()
    {
        return Tenant::find(session('active_tenant_id'));
    }

    // --- Table Methods ---

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function deleteCoupon($couponId)
    {
        // Global Scope in Coupon model ensures we only find if it belongs to current tenant
        $coupon = Coupon::find($couponId);

        if (!$coupon) {
            session()->flash('error', 'Coupon not found or access denied.');
            return;
        }

        // Detach relationships before deleting
        // Note: Pivot tables like coupon_product should also have tenant_id column
        $coupon->products()->detach();
        $coupon->categories()->detach();
        $coupon->users()->detach();

        $coupon->delete();
        session()->flash('message', 'Coupon deleted successfully!');
        $this->resetPage();
    }

    // --- Methods to open assignment modals ---

    public function openProductAssignment($couponId)
    {
        $this->activeCouponId = $couponId;
        $this->dispatch('openProductAssignmentModal', $couponId);
    }

    public function openCategoryAssignment($couponId)
    {
        $this->activeCouponId = $couponId;
        $this->dispatch('openCategoryAssignmentModal', $couponId);
    }

    public function openUserAssignment($couponId)
    {
        $this->activeCouponId = $couponId;
        $this->dispatch('openUserAssignmentModal', $couponId);
    }

    public function render()
    {
        $coupons = Coupon::query()
            ->when($this->search, function ($query) {
                /** 
                 * BEST WAY: Group OR search terms in a closure.
                 * Prevents "OR" from bypassing the Global Tenant Scope.
                 */
                $query->where(function ($q) {
                    $q->where('code', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.coupon.index', [
            'coupons' => $coupons,
        ]);
    }
}
