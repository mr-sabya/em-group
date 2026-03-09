<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Attribute Set Management</h2>
            <small class="text-muted">Active Store: <strong>{{ $this->currentTenant->name ?? 'Default' }}</strong></small>
        </div>
        <button class="btn btn-primary shadow-sm" wire:click="createAttributeSet">
            <i class="fas fa-plus me-1"></i> Add New Set
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
            <div class="row align-items-center mb-3 g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Search sets..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-8 d-flex justify-content-md-end gap-2">
                    <select wire:model.live="perPage" class="form-select w-auto shadow-sm">
                        <option value="10">10 Per Page</option>
                        <option value="25">25 Per Page</option>
                        <option value="50">50 Per Page</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th wire:click="sortBy('name')" role="button">Name
                                @if($sortField=='name')<i class="fas fa-sort-{{$sortDirection=='asc'?'up':'down'}} text-primary"></i>@endif
                            </th>
                            <th>Description</th>
                            <th>Linked Attributes</th>
                            <th style="width: 120px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attributeSets as $attributeSet)
                        <tr wire:key="set-{{ $attributeSet->id }}">
                            <td class="small">#{{ $attributeSet->id }}</td>
                            <td class="fw-bold">{{ $attributeSet->name }}</td>
                            <td><span class="text-muted small">{{ Str::limit($attributeSet->description, 50) }}</span></td>
                            <td>
                                @forelse($attributeSet->attributes as $attribute)
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2">{{ $attribute->name }}</span>
                                @empty
                                <span class="text-muted small italic">No attributes linked</span>
                                @endforelse
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info me-1 border-0" wire:click="editAttributeSet({{ $attributeSet->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger border-0" wire:click="deleteAttributeSet({{ $attributeSet->id }})" wire:confirm="Are you sure?">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-layer-group fa-3x text-light mb-3"></i>
                                <h6 class="text-muted">No attribute sets found for this store.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $attributeSets->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}" tabindex="-1" @if($showModal) style="background-color: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">{{ $isEditing ? 'Edit Attribute Set' : 'New Attribute Set' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <form wire:submit.prevent="saveAttributeSet">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Set Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" wire:model.defer="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Linked Attributes</label>
                            <select class="form-select @error('selectedAttributes') is-invalid @enderror" wire:model.defer="selectedAttributes" multiple style="min-height: 200px;">
                                @foreach($allAttributes as $attribute)
                                <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-2 d-block">Attributes shown here are specific to this store. Hold Ctrl/Cmd to select multiple.</small>
                            @error('selectedAttributes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @error('selectedAttributes.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <span wire:loading wire:target="saveAttributeSet" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $isEditing ? 'Update Set' : 'Create Set' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>