<div class="py-4">
    <h2 class="mb-4">Attribute Set Management</h2>

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
            <h5 class="mb-0">Attribute Sets List</h5>
            <button class="btn btn-primary" wire:click="createAttributeSet">
                <i class="fas fa-plus"></i> Add New Attribute Set
            </button>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-4">
                    <input type="text" class="form-control" placeholder="Search attribute sets..." wire:model.live.debounce.300ms="search">
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
                            <th>Description</th>
                            <th>Attributes</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attributeSets as $attributeSet)
                        <tr>
                            <td>{{ $attributeSet->id }}</td>
                            <td>{{ $attributeSet->name }}</td>
                            <td>{{ Str::limit($attributeSet->description, 70, '...') }}</td>
                            <td>
                                @forelse($attributeSet->attributes as $attribute)
                                <span class="badge bg-secondary">{{ $attribute->name }}</span>
                                @empty
                                <span class="text-muted">No attributes</span>
                                @endforelse
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info me-1" wire:click="editAttributeSet({{ $attributeSet->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" wire:click="deleteAttributeSet({{ $attributeSet->id }})" wire:confirm="Are you sure you want to delete this attribute set?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No attribute sets found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $attributeSets->links() }}
            </div>
        </div>
    </div>

    <!-- Attribute Set Create/Edit Modal -->
    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}" id="attributeSetModal" tabindex="-1" role="dialog" aria-labelledby="attributeSetModalLabel" aria-hidden="{{ !$showModal }}" @if($showModal) style="background-color: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attributeSetModalLabel">{{ $isEditing ? 'Edit Attribute Set' : 'Create New Attribute Set' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveAttributeSet">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.defer="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" wire:model.defer="description" rows="3"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="selectedAttributes" class="form-label">Select Attributes</label>
                            <select class="form-select @error('selectedAttributes') is-invalid @enderror" id="selectedAttributes" wire:model.defer="selectedAttributes" multiple size="8">
                                @foreach($allAttributes as $attribute)
                                <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold CTRL/CMD to select multiple attributes.</small>
                            @error('selectedAttributes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @error('selectedAttributes.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="saveAttributeSet" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $isEditing ? 'Update Attribute Set' : 'Create Attribute Set' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>