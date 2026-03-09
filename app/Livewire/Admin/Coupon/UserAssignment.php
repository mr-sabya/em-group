<?php

namespace App\Livewire\Admin\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed; // Added for Computed Property
use App\Models\Coupon;
use App\Models\User;

class UserAssignment extends Component
{
    use WithPagination;

    public $couponId;
    public $search = '';
    public $showModal = false;
    public $assignedUserIds = [];

    protected $listeners = ['openUserAssignmentModal' => 'openModal'];

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
        $this->loadAssignedUsers();
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

    public function loadAssignedUsers()
    {
        if ($this->couponId) {
            $coupon = Coupon::find($this->couponId);
            $this->assignedUserIds = $coupon ? $coupon->users->pluck('id')->toArray() : [];
        } else {
            $this->assignedUserIds = [];
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function assignUser($userId)
    {
        $coupon = Coupon::find($this->couponId);

        // Ensure the user exists within the current tenant scope
        $userExists = User::where('id', $userId)->exists();

        if ($coupon && $userExists && !in_array($userId, $this->assignedUserIds)) {
            /**
             * FIXED: Explicitly pass the tenant_id to the pivot table (coupon_user)
             */
            $coupon->users()->attach($userId, [
                'tenant_id' => $this->currentTenantId
            ]);

            $this->assignedUserIds[] = $userId;
            session()->flash('user_assignment_message', 'User assigned successfully!');
        }
    }

    public function unassignUser($userId)
    {
        $coupon = Coupon::find($this->couponId);
        if ($coupon && in_array($userId, $this->assignedUserIds)) {
            $coupon->users()->detach($userId);
            $this->assignedUserIds = array_diff($this->assignedUserIds, [$userId]);
            session()->flash('user_assignment_message', 'User unassigned successfully!');
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                /** 
                 * BEST WAY: Group OR terms in a closure.
                 * Prevents search from accidentally pulling users from other tenants.
                 */
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.coupon.user-assignment', [
            'users' => $users,
        ]);
    }
}
