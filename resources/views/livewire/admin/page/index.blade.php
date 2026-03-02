<div class="py-4">
    <h2 class="mb-4">Page Management</h2>

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
            <h5 class="mb-0">Pages List</h5>
            <a href="{{ route('page.create') }}" class="btn btn-primary" wire:navigate>
                <i class="fas fa-plus"></i> Add New Page
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Toolbar -->
            <div class="row mb-3 g-2" style=" --bs-gutter-x: 10px;">
                <div class="col-md-4 col-lg-4">
                    <input type="text" class="form-control" placeholder="Search pages..." wire:model.live.debounce.300ms="search">
                </div>

                <div class="col-md-4 col-lg-2">
                    <select wire:model.live="filterActive" class="form-select form-control">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-6 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0">
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

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 100px;">Banner</th>
                            <th wire:click="sortBy('title')" role="button">Title
                                @if ($sortField == 'title')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Slug</th>
                            <th>Template</th>
                            <th wire:click="sortBy('sort_order')" role="button">Sort Order
                                @if ($sortField == 'sort_order')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('is_active')" role="button">Active
                                @if ($sortField == 'is_active')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pages as $page)
                        <tr>
                            <td>{{ $page->id }}</td>
                            <td>
                                @if ($page->banner_image)
                                <img src="{{ asset('storage/' . $page->banner_image) }}" alt="Banner" class="img-thumbnail" style="width: 80px; height: 40px; object-fit: cover;">
                                @else
                                <span class="text-muted small">No Image</span>
                                @endif
                            </td>
                            <td>{{ $page->title }}</td>
                            <td><small class="text-muted">/{{ $page->slug }}</small></td>
                            <td><span class="badge bg-secondary">{{ ucfirst($page->template) }}</span></td>
                            <td>{{ $page->sort_order }}</td>
                            <td>
                                @if ($page->is_active)
                                <span class="badge bg-success">Yes</span>
                                @else
                                <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('page.edit', $page->id) }}" wire:navigate class="btn btn-sm btn-info me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" wire:click="deletePage({{ $page->id }})" wire:confirm="Are you sure you want to delete this page?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No pages found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $pages->links() }}
            </div>
        </div>
    </div>
</div>