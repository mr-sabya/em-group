<div>
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Collection Management</h2>
            <p class="text-muted small mb-0">Managing collections for: <strong>{{ $this->currentTenant->name ?? 'Unknown Store' }}</strong></p>
        </div>
        <a href="{{ route('collection.create') }}" class="btn btn-primary shadow-sm" wire:navigate>
            <i class="fas fa-plus me-1"></i> Create New Collection
        </a>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <!-- Toolbar -->
            <div class="row align-items-center mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Search collections..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-8 d-flex justify-content-md-end align-items-center gap-2 mt-2 mt-md-0">
                    <select wire:model.live="perPage" class="form-select w-auto">
                        <option value="10">10 Per Page</option>
                        <option value="25">25 Per Page</option>
                        <option value="50">50 Per Page</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th wire:click="sortBy('title')" role="button" class="ps-3">
                                Title @if ($sortField == 'title') <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-primary"></i> @endif
                            </th>
                            <th>Tag</th>
                            <th>Category</th>
                            <th>Featured Price</th>
                            <th class="text-center">Status</th>
                            <th wire:click="sortBy('display_order')" role="button" class="text-center">
                                Order @if ($sortField == 'display_order') <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-primary"></i> @endif
                            </th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($collections as $collection)
                        <tr wire:key="collection-{{ $collection->id }}">
                            <td class="ps-3 fw-bold text-dark">{{ $collection->title }}</td>
                            <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ $collection->tag ?? 'General' }}</span></td>
                            <td>{{ $collection->category->name ?? 'No Category' }}</td>
                            <td class="fw-bold text-primary">৳{{ number_format($collection->featured_price, 2) }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $collection->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $collection->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">{{ $collection->display_order }}</td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <a href="{{ route('collections.edit', $collection->id) }}" class="btn btn-sm btn-outline-info border-0" title="Edit" wire:navigate>
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger border-0"
                                        wire:click="deleteCollection({{ $collection->id }})"
                                        wire:confirm="Are you sure you want to delete this collection for this store?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                <h6 class="text-muted">No collections found for this store.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $collections->links() }}
            </div>
        </div>
    </div>
</div>