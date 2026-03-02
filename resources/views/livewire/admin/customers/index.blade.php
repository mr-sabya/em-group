<div class="py-4">
    <h2 class="mb-4">Customer Management</h2>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Customer List</h5>
            <a href="{{ route('users.customers.create') }}" wire:navigate class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Customer
            </a>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4 mb-2 mb-md-0">
                    <input type="text" class="form-control" placeholder="Search customers by name, email, phone..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-6 col-lg-8 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <select wire:model.live="perPage" class="form-select w-auto">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-nowrap">Per Page</span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Name
                                @if ($sortField == 'name')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>Avatar</th>
                            <th wire:click="sortBy('email')" style="cursor: pointer;">
                                Email
                                @if ($sortField == 'email')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>Phone</th>
                            <th wire:click="sortBy('is_active')" style="cursor: pointer;">
                                Active
                                @if ($sortField == 'is_active')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <strong>{{ $customer->name }}</strong>
                                <br><small class="text-muted">{{ $customer->address }} {{ $customer->city }}</small>
                            </td>
                            <td>
                                <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="img-thumbnail rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                            <td>
                                <i class="fas {{ $customer->is_active ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"></i>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" wire:click="editCustomer({{ $customer->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure you want to delete this customer? This action cannot be undone.') || event.stopImmediatePropagation()" wire:click="deleteCustomer({{ $customer->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No customers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>