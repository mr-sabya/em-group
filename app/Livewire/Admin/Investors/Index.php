<?php

namespace App\Livewire\Investors; // New namespace

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InvestorProfile; // Use InvestorProfile model
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

    protected $listeners = ['investorProfileSaved' => '$refresh', 'investorProfileCreated' => '$refresh']; // Custom listeners

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

    public function createInvestor() // Changed method name
    {
        return $this->redirect(route('investors.create'), navigate: true); // Changed route
    }

    public function editInvestor($investorProfileId) // Changed method name
    {
        return $this->redirect(route('investors.edit', ['investorProfile' => $investorProfileId]), navigate: true); // Changed route
    }

    public function deleteInvestor($investorProfileId) // Changed method name
    {
        $investorProfile = InvestorProfile::find($investorProfileId);

        if (!$investorProfile) {
            session()->flash('error', 'Investor Profile not found.');
            return;
        }

        // When deleting the InvestorProfile, the associated User record will also be deleted
        // because of `onDelete('cascade')` on `user_id` in the migration.
        // If you want to delete ONLY the profile and keep the user (e.g., convert them to a customer),
        // you would need different logic and remove `onDelete('cascade')` from migration.
        // For now, we assume deleting the profile means deleting the associated user.

        $investorProfile->user->delete(); // This will trigger cascade delete on profile
        session()->flash('message', 'Investor and their profile deleted successfully!');
        $this->resetPage();
    }

    public function render()
    {
        // Join with users table to allow searching/sorting by user fields
        $investors = InvestorProfile::query()
            ->join('users', 'investor_profiles.user_id', '=', 'users.id')
            ->select('investor_profiles.*', 'users.name as user_name', 'users.email as user_email', 'users.phone as user_phone', 'users.avatar as user_avatar')
            ->when($this->search, function ($query) {
                $query->where('company_name', 'like', '%' . $this->search . '%')
                    ->orWhere('investment_focus', 'like', '%' . $this->search . '%')
                    ->orWhere('contact_person_name', 'like', '%' . $this->search . '%')
                    ->orWhere('users.name', 'like', '%' . $this->search . '%') // Search by user name
                    ->orWhere('users.email', 'like', '%' . $this->search . '%'); // Search by user email
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.investors.index', [ // Changed view path
            'investors' => $investors,
        ]);
    }
}
