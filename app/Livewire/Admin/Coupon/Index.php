<?php

namespace App\Livewire\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Coupon;

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
        $coupon = Coupon::find($couponId);

        if (!$coupon) {
            session()->flash('error', 'Coupon not found.');
            return;
        }

        // Detach relationships before deleting to avoid foreign key constraints
        $coupon->products()->detach();
        $coupon->categories()->detach();
        $coupon->users()->detach();

        $coupon->delete();
        session()->flash('message', 'Coupon deleted successfully!');
        $this->resetPage();
    }

    // Methods to open assignment modals
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
                $query->where('code', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.coupon.index', [
            'coupons' => $coupons,
        ]);
    }
}
