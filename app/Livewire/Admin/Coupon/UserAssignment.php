<?php

namespace App\Livewire\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
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

    public function openModal($couponId)
    {
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
        if ($coupon && !in_array($userId, $this->assignedUserIds)) {
            $coupon->users()->attach($userId);
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
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.coupon.user-assignment', [
            'users' => $users,
        ]);
    }
}
