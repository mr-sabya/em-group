<?php

namespace App\Livewire\Admin\Customers; // Changed namespace

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    // Table state
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;


    protected $listeners = ['customerSaved' => '$refresh', 'customerCreated' => '$refresh']; // Changed listener names

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

    public function createCustomer() // Changed method name
    {
        return $this->redirect(route('customers.create'), navigate: true); // Changed route
    }

    public function deleteCustomer($userId) // Changed method name
    {
        $user = User::find($userId); // Ensure we only delete customers

        if (!$user) {
            session()->flash('error', 'Customer not found.');
            return;
        }

        // Prevent deleting the currently authenticated user if they are a customer (optional)
        if (Auth::check() && Auth::user()->id === $user->id) {
            session()->flash('error', 'You cannot delete your own account from here.');
            return;
        }

        // Delete avatar image file if it exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Consider handling related data like orders if you want to delete them
        // For example: $user->orders()->delete(); // if orders should be deleted with customer

        $user->delete();
        session()->flash('message', 'Customer deleted successfully!');
        $this->resetPage();
    }

    public function render()
    {
        $customers = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    // You might want to search by city/state/country name here too:
                    ->orWhereHas('country', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('state', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('city', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.customers.index', [ // Changed view path
            'customers' => $customers,
        ]);
    }
}
