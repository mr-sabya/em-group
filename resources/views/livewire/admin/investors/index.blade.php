<div class="py-4">
    <h2 class="mb-4">Investor Management</h2>

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
            <h5 class="mb-0">Investor List</h5>
            <a href="{{ route('users.investors.create') }}" wire:navigate class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Investor
            </a>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4 mb-2 mb-md-0">
                    <input type="text" class="form-control" placeholder="Search by company, focus, contact, name, email..." wire:model.live.debounce.300ms="search">
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
                            <th wire:click="sortBy('users.name')" style="cursor: pointer;">
                                Investor Name
                                @if ($sortField == 'users.name')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('company_name')" style="cursor: pointer;">
                                Company Name
                                @if ($sortField == 'company_name')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('investment_focus')" style="cursor: pointer;">
                                Investment Focus
                                @if ($sortField == 'investment_focus')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>Contact Person</th>
                            <th>Investment Range</th>
                            <th wire:click="sortBy('is_approved')" style="cursor: pointer;">
                                Approved
                                @if ($sortField == 'is_approved')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i>
                                @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investors as $investor)
                        <tr>
                            <td>
                                {{ $investor->user_name }}
                                <br><small class="text-muted">{{ $investor->user_email }}</small>
                            </td>
                            <td>{{ $investor->company_name ?? 'N/A' }}</td>
                            <td>{{ $investor->investment_focus ?? 'N/A' }}</td>
                            <td>
                                {{ $investor->contact_person_name ?? 'N/A' }}
                                @if ($investor->contact_person_email)
                                <br><small class="text-muted">{{ $investor->contact_person_email }}</small>
                                @endif
                            </td>
                            <td>
                                @if($investor->min_investment_amount || $investor->max_investment_amount)
                                ${{ number_format($investor->min_investment_amount, 0) }} - ${{ number_format($investor->max_investment_amount, 0) }}
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                <i class="fas {{ $investor->is_approved ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"></i>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" wire:click="editInvestor({{ $investor->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure you want to delete this investor profile and its associated user? This action cannot be undone.') || event.stopImmediatePropagation()" wire:click="deleteInvestor({{ $investor->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No investor profiles found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $investors->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>