<div class="py-4">
    <h2 class="mb-4">Collection Management</h2>

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
            <h5 class="mb-0">All Collections</h5>
            <a href="{{ route('collection.create') }}" class="btn btn-primary" wire:navigate>
                <i class="fas fa-plus"></i> Create New Collection
            </a>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4 mb-2 mb-md-0">
                    <input type="text" class="form-control" placeholder="Search collections..." wire:model.live.debounce.300ms="search">
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
                            <th wire:click="sortBy('title')" style="cursor: pointer;">
                                Title
                                @if ($sortField == 'title') <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i> @endif
                            </th>
                            <th>Tag</th>
                            <th>Category</th>
                            <th>Featured Price</th>
                            <th>Active</th>
                            <th wire:click="sortBy('display_order')" style="cursor: pointer;">
                                Order
                                @if ($sortField == 'display_order') <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }}"></i> @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($collections as $collection)
                        <tr>
                            <td>{{ $collection->title }}</td>
                            <td><span class="badge bg-info">{{ $collection->tag ?? 'N/A' }}</span></td>
                            <td>{{ $collection->category->name ?? 'No Category' }}</td>
                            <td>{{ number_format($collection->featured_price, 2) ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $collection->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $collection->is_active ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>{{ $collection->display_order }}</td>
                            <td>
                                <a href="{{ route('collections.edit', $collection->id) }}" class="btn btn-sm btn-info" title="Edit Collection" wire:navigate>
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" wire:click="deleteCollection({{ $collection->id }})" title="Delete Collection" onclick="return confirm('Are you sure you want to delete this collection?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No collections found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $collections->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>