<div class="py-4">
    <h2 class="mb-4">Feature Management</h2>

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
            <h5 class="mb-0">Features List</h5>
            <button class="btn btn-primary" wire:click="createFeature">
                <i class="fas fa-plus"></i> Add New Feature
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4">
                    <input type="text" class="form-control" placeholder="Search features..." wire:model.live.debounce.300ms="search">
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
                            <th wire:click="sortBy('sort_order')" role="button" style="width: 100px;">
                                Order
                                @if ($sortField == 'sort_order')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th style="width: 100px;">Icon</th>
                            <th wire:click="sortBy('title')" role="button">Title
                                @if ($sortField == 'title')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Subtitle</th>
                            <th wire:click="sortBy('is_active')" role="button" style="width: 120px;">Active
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
                        @forelse ($features as $feature)
                        <tr>
                            <td>{{ $feature->id }}</td>
                            <td>{{ $feature->sort_order }}</td>
                            <td class="text-center">
                                @if ($feature->icon)
                                {{-- Using asset() assuming you have a symbolic link or public path --}}
                                <img src="{{ asset('storage/' . $feature->icon) }}" alt="Icon" style="width: 40px; height: 40px; object-fit: contain;">
                                @else
                                <span class="text-muted">No Icon</span>
                                @endif
                            </td>
                            <td>{{ $feature->title }}</td>
                            <td>{{ $feature->subtitle ?? '-' }}</td>
                            <td>
                                @if ($feature->is_active)
                                <span class="badge bg-success">Yes</span>
                                @else
                                <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info me-1" wire:click="editFeature({{ $feature->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" wire:click="deleteFeature({{ $feature->id }})" wire:confirm="Are you sure you want to delete this feature?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No features found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $features->links() }}
            </div>
        </div>
    </div>

    <!-- Feature Create/Edit Modal -->
    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}" id="featureModal" tabindex="-1" role="dialog" aria-labelledby="featureModalLabel" aria-hidden="{{ !$showModal }}" @if($showModal) style="background-color: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="featureModalLabel">{{ $isEditing ? 'Edit Feature' : 'Create New Feature' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveFeature">
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" wire:model.live="title" placeholder="e.g., Free Shipping">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <input type="text" class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" wire:model.defer="subtitle" placeholder="e.g., On all orders over $50">
                            @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sort Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" wire:model.defer="sort_order">
                                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" wire:model.defer="is_active">
                                    <label class="form-check-label ms-2" for="is_active">Is Active</label>
                                </div>
                                @error('is_active') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon Image <span class="text-danger">*</span></label>

                            <!-- Image Preview Area -->
                            <div class="image-preview mb-2 p-2 border rounded text-center bg-light">
                                @if ($icon)
                                <img src="{{ $icon->temporaryUrl() }}" class="img-fluid" style="max-height: 80px;">
                                @elseif ($currentIcon)
                                <img src="{{ asset('storage/' . $currentIcon) }}" alt="Current Icon" class="img-fluid" style="max-height: 80px;">
                                @else
                                <span class="text-muted small">No image selected</span>
                                @endif
                            </div>

                            <input type="file" class="form-control @error('icon') is-invalid @enderror" id="icon" wire:model.live="icon">
                            <small class="form-text text-muted">Recommended size: 64x64px. Format: PNG, SVG, JPG.</small>
                            @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="saveFeature" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $isEditing ? 'Update Feature' : 'Create Feature' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>