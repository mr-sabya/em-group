<div class="py-4">
    <h2 class="mb-4">Banner Management</h2>

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
            <h5 class="mb-0">Banners List</h5>
            <button class="btn btn-primary" wire:click="createBanner">
                <i class="fas fa-plus"></i> Add New Banner
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4">
                    <input type="text" class="form-control" placeholder="Search banners by title or subtitle..." wire:model.live.debounce.300ms="search">
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
                            <th>Image</th>
                            <th wire:click="sortBy('title')" role="button">Title
                                @if ($sortField == 'title')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Subtitle</th>
                            <th>Link</th>
                            <th wire:click="sortBy('order')" role="button" style="width: 120px;">Order
                                @if ($sortField == 'order')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                                @else
                                <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
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
                        @forelse ($banners as $banner)
                        <tr>
                            <td>{{ $banner->id }}</td>
                            <td>
                                @if ($banner->image)
                                <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }} Image" class="img-thumbnail" style="width: 80px; height: 50px; object-fit: cover;">
                                @else
                                N/A
                                @endif
                            </td>
                            <td>{!! $banner->title !!}</td> {{-- Use !! for raw HTML title --}}
                            <td>{{ Str::limit($banner->subtitle, 50) }}</td>
                            <td>
                                @if ($banner->link)
                                <a href="{{ $banner->link }}" target="_blank" class="text-decoration-none">{{ Str::limit($banner->link, 30) }}</a>
                                @else
                                N/A
                                @endif
                            </td>
                            <td>{{ $banner->order }}</td>
                            <td>
                                @if ($banner->is_active)
                                <span class="badge bg-success">Yes</span>
                                @else
                                <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info me-1" wire:click="editBanner({{ $banner->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" wire:click="deleteBanner({{ $banner->id }})" wire:confirm="Are you sure you want to delete this banner?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No banners found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $banners->links() }}
            </div>
        </div>
    </div>

    <!-- Banner Create/Edit Modal -->
    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}" id="bannerModal" tabindex="-1" role="dialog" aria-labelledby="bannerModalLabel" aria-hidden="{{ !$showModal }}" @if($showModal) style="background-color: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bannerModalLabel">{{ $isEditing ? 'Edit Banner' : 'Create New Banner' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveBanner">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" wire:model.live="title">
                            <small class="form-text text-muted">Can contain HTML for styling (e.g., `<span class='text-green-300'>$50+</span>`).</small>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <input type="text" class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" wire:model.defer="subtitle">
                            @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="link" class="form-label">Link (URL)</label>
                            <input type="url" class="form-control @error('link') is-invalid @enderror" id="link" wire:model.defer="link" placeholder="https://example.com/shop">
                            <small class="form-text text-muted">The URL this banner will link to.</small>
                            @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="button" class="form-label">Button Text</label>
                            <input type="text" class="form-control @error('button') is-invalid @enderror" id="button" wire:model.defer="button" placeholder="Shop Now">
                            <small class="form-text text-muted">Text for the call-to-action button (optional).</small>
                            @error('button') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Banner Image</label>
                            <div class="image-preview mb-2">
                                @if ($image)
                                <img src="{{ $image->temporaryUrl() }}" class="upload-image img-thumbnail" style="max-width: 200px; max-height: 150px; object-fit: contain;">
                                @elseif ($currentImage)
                                <img src="{{ asset('storage/' . $currentImage) }}" alt="Current Banner Image" class="upload-image img-thumbnail" style="max-width: 200px; max-height: 150px; object-fit: contain;">
                                @endif
                            </div>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" wire:model.live="image">
                            <small class="form-text text-muted">Max 2MB. Accepted formats: JPG, PNG, GIF. Recommended aspect ratio for banners.</small>
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="order" class="form-label">Display Order <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" wire:model.defer="order" min="0">
                            <small class="form-text text-muted">Lower numbers appear first.</small>
                            @error('order') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <span wire:loading wire:target="saveBanner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $isEditing ? 'Update Banner' : 'Create Banner' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>