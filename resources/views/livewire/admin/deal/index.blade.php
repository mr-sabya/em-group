<div class="py-4">
    <h2 class="mb-4">Deal Management</h2>

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
            <h5 class="mb-0">All Deals</h5>
            <a href="{{ route('deal.create') }}" wire:navigate class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Deal
            </a>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4 mb-2 mb-md-0">
                    <input type="text" class="form-control" placeholder="Search deals by name or description..." wire:model.live.debounce.300ms="search">
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
                                @if ($sortField == 'name') <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i> @endif
                            </th>
                            <th>Type / Value</th>
                            <th>Target Link</th>
                            <th wire:click="sortBy('starts_at')" style="cursor: pointer;">
                                Active Dates
                                @if ($sortField == 'starts_at') <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i> @endif
                            </th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deals as $deal)
                        <tr>
                            <td>{{ $deal->name }}</td>
                            <td>{{ ucfirst($deal->type) }} ({{ $deal->value }}{{ $deal->type === 'percentage' ? '%' : '' }})</td>
                            <td>{{ $deal->link_target ?? 'N/A' }}</td>
                            <td>
                                {{ $deal->starts_at?->format('Y-m-d') ?? 'N/A' }} -
                                {{ $deal->expires_at?->format('Y-m-d') ?? 'N/A' }}
                            </td>
                            <td>
                                <span class="badge {{ $deal->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $deal->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $deal->is_featured ? 'bg-warning text-dark' : 'bg-light text-dark' }}">
                                    {{ $deal->is_featured ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('deal.edit', $deal->id) }}" wire:navigate class="btn btn-sm btn-info" title="Edit Deal">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" wire:click="deleteDeal({{ $deal->id }})" title="Delete Deal" onclick="return confirm('Are you sure you want to delete this deal?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No deals found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $deals->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>