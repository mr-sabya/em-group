<?php

namespace App\Livewire\Vendors;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VendorProfile; // Use VendorProfile model
use App\Models\User; // Also need User model for related data
use App\Enums\UserRole; // Import UserRole enum

class Index extends Component
{
    use WithPagination;

    // Table state
    public $search = '';
    public $sortField = 'users.name'; // Default sort by user name
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $listeners = ['vendorProfileSaved' => '$refresh', 'vendorProfileCreated' => '$refresh']; // Custom listeners

    protected $queryString = [
        'search',
        'sortField',
        'sortDirection',
        'page',
    ];

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

    public function createVendor() // Changed method name
    {
        return $this->redirect(route('vendors.create'), navigate: true); // Changed route
    }

    public function editVendor($vendorProfileId) // Changed method name
    {
        return $this->redirect(route('vendors.edit', ['vendorProfile' => $vendorProfileId]), navigate: true); // Changed route
    }

    public function deleteVendor($vendorProfileId) // Changed method name
    {
        $vendorProfile = VendorProfile::find($vendorProfileId);

        if (!$vendorProfile) {
            session()->flash('error', 'Vendor Profile not found.');
            return;
        }

        // When deleting the VendorProfile, the associated User record will also be deleted
        // because of `onDelete('cascade')` on `user_id` in the migration.
        // If you want to delete ONLY the profile and keep the user (e.g., convert them to a customer),
        // you would need different logic and remove `onDelete('cascade')` from migration.
        // For now, we assume deleting the profile means deleting the associated user.

        $vendorProfile->user->delete(); // This will trigger cascade delete on profile
        session()->flash('message', 'Vendor and their profile deleted successfully!');
        $this->resetPage();
    }

    public function render()
    {
        // Join with users table to allow searching/sorting by user fields
        $vendors = VendorProfile::query()
            ->join('users', 'vendor_profiles.user_id', '=', 'users.id')
            ->select('vendor_profiles.*', 'users.name as user_name', 'users.email as user_email', 'users.phone as user_phone', 'users.avatar as user_avatar')
            ->when($this->search, function ($query) {
                $query->where('shop_name', 'like', '%' . $this->search . '%')
                    ->orWhere('shop_description', 'like', '%' . $this->search . '%')
                    ->orWhere('vendor_profiles.email', 'like', '%' . $this->search . '%') // Search by vendor profile email
                    ->orWhere('users.name', 'like', '%' . $this->search . '%') // Search by user name
                    ->orWhere('users.email', 'like', '%' . $this->search . '%'); // Search by user email
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.vendors.index', [ // Changed view path
            'vendors' => $vendors,
        ]);
    }
}
