<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Payment Methods</h3>
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Add New Method
        </button>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0" placeholder="Search methods...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="manual">Manual (COD)</option>
                        <option value="direct">Direct (Mobile/TxID)</option>
                        <option value="gateway">Gateway (Online)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10 Rows</option>
                        <option value="25">25 Rows</option>
                        <option value="50">50 Rows</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Logo</th>
                        <th style="cursor:pointer" wire:click="sortBy('name')">
                            Name @if($sortField === 'name') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                        </th>
                        <th style="cursor:pointer" wire:click="sortBy('type')">Type</th>
                        <th style="cursor:pointer" wire:click="sortBy('is_default')">Default</th>
                        <th style="cursor:pointer" wire:click="sortBy('status')">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($methods as $method)
                    <tr>
                        <td>
                            @if($method->image)
                            <img src="{{ asset('storage/' . $method->image) }}" class="rounded border" style="width: 45px; height: 30px; object-fit: contain;">
                            @else
                            <div class="bg-light rounded border text-center text-muted" style="width: 45px; height: 30px; line-height: 30px; font-size: 10px;">No Logo</div>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold">{{ $method->name }}</span><br>
                            <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $method->slug }}</small>
                        </td>
                        <td>
                            @if($method->type == 'manual')
                            <span class="badge rounded-pill bg-secondary text-white">Manual</span>
                            @elseif($method->type == 'direct')
                            <span class="badge rounded-pill bg-info text-white">Direct Pay</span>
                            @else
                            <span class="badge rounded-pill bg-primary text-white">Gateway</span>
                            @endif
                        </td>
                        <td>
                            @if($method->is_default)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Default</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:click="toggleStatus({{ $method->id }})" {{ $method->status ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-end">
                            <button wire:click="openModal({{ $method->id }})" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No payment methods found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 pt-3">
            {{ $methods->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">{{ $method_id ? 'Edit' : 'Add' }} Payment Method</h5>
                    <button wire:click="$set('showModal', false)" class="btn-close"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Method Name</label>
                                <input type="text" wire:model="name" class="form-control" placeholder="e.g. bKash Personal">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Type</label>
                                <select wire:model.live="type" class="form-select">
                                    <option value="manual">Manual (COD)</option>
                                    <option value="direct">Direct (Mobile Banking)</option>
                                    <option value="gateway">Gateway (Online)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Instructions / Description</label>
                                <textarea wire:model="instructions" class="form-control" rows="3" placeholder="Step by step instructions for the customer..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Method Logo</label>
                                <input type="file" wire:model="image" class="form-control">
                                <small class="text-muted">Max size 1MB (PNG, JPG)</small>
                                @error('image') <span class="text-danger d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" class="img-thumbnail" style="height: 80px;">
                            @elseif($existingImage)
                            <img src="{{ asset('storage/'.$existingImage) }}" class="img-thumbnail" style="height: 80px;">
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" wire:model="is_default" id="defPay">
                                <label class="form-check-label" for="defPay">Set as Default Payment</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" wire:model="status" id="statPay">
                                <label class="form-check-label" for="statPay">Active Status</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button wire:click="$set('showModal', false)" class="btn btn-light px-4">Cancel</button>
                    <button wire:click="save" class="btn btn-primary px-4" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Method</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>