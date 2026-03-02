<div class="py-4">
    <h2 class="mb-4">Product Management</h2>

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
            <h5 class="mb-0">Products List</h5>
            <a href="{{ route('product.products.create') }}" class="btn btn-primary" wire:navigate>
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-3 g-2" style=" --bs-gutter-x: 10px;">
                <div class="col-md-4 col-lg-3">
                    <input type="text" class="form-control" placeholder="Search products..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-4 col-lg-2">
                    <select wire:model.live="filterCategory" class="form-select form-control">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <select wire:model.live="filterBrand" class="form-select form-control">
                        <option value="">All Brands</option>
                        @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <select wire:model.live="filterType" class="form-select form-control">
                        <option value="">All Types</option>
                        @foreach ($productTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-1">
                    <select wire:model.live="filterActive" class="form-select form-control">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-2 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0">
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
                            <th style="width: 50px;">ID</th>
                            <th>Image</th>
                            <th wire:click="sortBy('name')" role="button">Name
                                @if ($sortField == 'name')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Type</th>
                            <th wire:click="sortBy('price')" role="button">Price
                                @if ($sortField == 'price')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('quantity')" role="button">Stock
                                @if ($sortField == 'quantity')
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
                        @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                @if ($product->thumbnail_url)
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                <i class="fas fa-image fa-2x text-muted"></i>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku ?? 'N/A' }}</td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>{{ $product->brand->name ?? 'N/A' }}</td>
                            <td><span class="badge bg-primary">{{ $product->type->label() }}</span></td>
                            <td>{{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->is_manage_stock ? $product->current_stock : 'N/A' }}</td>
                            <td>
                                @if ($product->is_active)
                                <span class="badge bg-success">Yes</span>
                                @else
                                <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('product.products.edit', $product->id) }}" wire:navigate class="btn btn-sm btn-info me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" wire:click="deleteProduct({{ $product->id }})" wire:confirm="Are you sure you want to delete '{{ $product->name }}' and all its associated data (images, variants, etc.)?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">No products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>