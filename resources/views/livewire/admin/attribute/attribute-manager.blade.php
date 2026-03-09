<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 h4">Attribute Management</h2>
            <small class="text-muted">Active Store: <strong>{{ $this->currentTenant->name ?? 'Default' }}</strong></small>
        </div>
        <button class="btn btn-primary shadow-sm" wire:click="createAttribute">
            <i class="fas fa-plus"></i> Add New Attribute
        </button>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Search attributes..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-8 d-flex justify-content-md-end mt-2 mt-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <select wire:model.live="perPage" class="form-select w-auto">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-muted small text-nowrap">Per Page</span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th wire:click="sortBy('name')" role="button" class="user-select-none">Name
                                @if ($sortField == 'name')
                                <i class="fas {{ $sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }} text-primary"></i>
                                @else
                                <i class="fas fa-sort text-muted opacity-50"></i>
                                @endif
                            </th>
                            <th>Slug</th>
                            <th>Display Type</th>
                            <th class="text-center">Filterable</th>
                            <th class="text-center">Status</th>
                            <th style="width: 120px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attributes as $attribute)
                        <tr wire:key="attr-{{ $attribute->id }}">
                            <td><span class="text-muted small">#{{ $attribute->id }}</span></td>
                            <td class="fw-bold">{{ $attribute->name }}</td>
                            <td><code class="text-secondary small">{{ $attribute->slug }}</code></td>
                            <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ $attribute->display_type->label() }}</span></td>
                            <td class="text-center">
                                @if ($attribute->is_filterable)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3">Yes</span>
                                @else
                                <span class="badge bg-light text-muted rounded-pill px-3 border">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($attribute->is_active)
                                <span class="badge bg-success rounded-circle p-1" title="Active"><span class="visually-hidden">Active</span></span>
                                @else
                                <span class="badge bg-danger rounded-circle p-1" title="Inactive"><span class="visually-hidden">Inactive</span></span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info me-1 border-0" wire:click="editAttribute({{ $attribute->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger border-0" wire:click="deleteAttribute({{ $attribute->id }})" wire:confirm="Are you sure you want to delete this attribute?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-layer-group fa-3x text-light mb-3"></i>
                                <h6 class="text-muted">No attributes found for this store.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $attributes->links() }}
            </div>
        </div>
    </div>

    <!-- Modal remains the same structure with your improved UI -->
    ...

    <!-- Attribute Create/Edit Modal -->
    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}" id="attributeModal" tabindex="-1" role="dialog" aria-labelledby="attributeModalLabel" aria-hidden="{{ !$showModal }}" @if($showModal) style="background-color: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attributeModalLabel">{{ $isEditing ? 'Edit Attribute' : 'Create New Attribute' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveAttribute">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.live="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" wire:model.defer="slug">
                            <small class="form-text text-muted">Unique URL-friendly identifier (e.g., `color`, `size`).</small>
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="display_type" class="form-label">Display Type <span class="text-danger">*</span></label>
                            <select class="form-select form-control @error('display_type') is-invalid @enderror" id="display_type" wire:model.defer="display_type">
                                @foreach($displayTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">How this attribute will be displayed (e.g., color picker, text input).</small>
                            @error('display_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3 form-check form-switch d-flex align-items-center">
                            <input class="form-check-input @error('is_filterable') is-invalid @enderror" type="checkbox" id="is_filterable" wire:model.defer="is_filterable">
                            <label class="form-check-label ms-2 mb-0" for="is_filterable">Is Filterable</label>
                            @error('is_filterable') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <span wire:loading wire:target="saveAttribute" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $isEditing ? 'Update Attribute' : 'Create Attribute' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>