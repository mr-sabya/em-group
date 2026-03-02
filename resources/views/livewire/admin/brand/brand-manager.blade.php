<div class="py-4">
    <h2 class="mb-4">Brand Management</h2>

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
            <h5 class="mb-0">Brands List</h5>
            <button class="btn btn-primary" wire:click="createBrand">
                <i class="fas fa-plus"></i> Add New Brand
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4">
                    <input type="text" class="form-control" placeholder="Search brands..." wire:model.live.debounce.300ms="search">
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
                            <th style="width: 50px;">ID</th>
                            <th wire:click="sortBy('name')" role="button">Name
                                @if ($sortField == 'name')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Logo</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th wire:click="sortBy('is_active')" role="button" style="width: 150px;">Active
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
                        @forelse ($brands as $brand)
                        <tr>
                            <td>{{ $brand->id }}</td>
                            <td>{{ $brand->name }}</td>
                            <td>
                                @if ($brand->logo)
                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }} Logo" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;">
                                @else
                                N/A
                                @endif
                            </td>
                            <td>{{ $brand->slug }}</td>
                            <td>{{ Str::limit($brand->description, 50) }}</td>
                            <td>
                                @if ($brand->is_active)
                                <span class="badge bg-success">Yes</span>
                                @else
                                <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info me-1" wire:click="editBrand({{ $brand->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" wire:click="deleteBrand({{ $brand->id }})" wire:confirm="Are you sure you want to delete this brand?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No brands found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $brands->links() }}
            </div>
        </div>
    </div>

    <!-- Brand Create/Edit Modal -->
    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}" id="brandModal" tabindex="-1" role="dialog" aria-labelledby="brandModalLabel" aria-hidden="{{ !$showModal }}" @if($showModal) style="background-color: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="brandModalLabel">{{ $isEditing ? 'Edit Brand' : 'Create New Brand' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveBrand">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.live="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" wire:model.defer="slug">
                            <small class="form-text text-muted">Unique URL-friendly identifier (e.g., `apple-inc`).</small>
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" rows="3" wire:model.defer="description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Brand Logo</label>
                            <div class="image-preview">
                                @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" class="upload-image">
                                @elseif ($currentLogo)
                                <img src="{{ asset('storage/' . $currentLogo) }}" alt="Current Brand Logo" class="upload-image">
                                @endif
                            </div>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" wire:model.live="logo">
                            <small class="form-text text-muted">Max 1MB. Accepted formats: JPG, PNG, GIF.</small>
                            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3 form-check form-switch d-flex align-items-center">
                            <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" wire:model.defer="is_active">
                            <label class="form-check-label ms-2 mb-0" for="is_active">Is Active</label>
                            @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="saveBrand" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $isEditing ? 'Update Brand' : 'Create Brand' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>