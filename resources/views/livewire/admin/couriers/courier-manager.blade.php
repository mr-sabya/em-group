<div>
    <!-- Page Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-primary">Courier Management</h4>
                <p class="text-muted small mb-0">Manage delivery API integrations for your store.</p>
            </div>
            <button wire:click="openCreateModal" class="btn btn-primary px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i>Add New Courier
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Datatable Section -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light" placeholder="Search name or vendor..." wire:model.live="search">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 cursor-pointer" wire:click="sortBy('name')">
                                Courier Name
                                @if($sortField === 'name') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                            </th>
                            <th class="cursor-pointer" wire:click="sortBy('vendor')">
                                Vendor
                                @if($sortField === 'vendor') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                            </th>
                            <th class="text-center">Active Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($couriers as $courier)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $courier->name }}</div>
                                <small class="text-muted">ID: #{{ $courier->id }}</small>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-label-info text-uppercase border px-3 py-2" style="font-size: 0.75rem;">
                                    {{ $courier->vendor }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        wire:click="toggleStatus({{ $courier->id }})"
                                        {{ $courier->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <button wire:click="edit({{ $courier->id }})" class="btn btn-sm btn-white border" title="Edit">
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>
                                    <button wire:confirm="Are you sure you want to delete this courier? This cannot be undone."
                                        wire:click="delete({{ $courier->id }})" class="btn btn-sm btn-white border" title="Delete">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                                    <p class="mb-0">No couriers found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top py-3">
            {{ $couriers->links() }}
        </div>
    </div>

    <!-- CREATE/EDIT MODAL -->
    <div wire:ignore.self class="modal fade" id="courierModal" tabindex="-1" aria-labelledby="courierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary p-3">
                    <h5 class="modal-title fw-bold text-white" id="courierModalLabel">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} me-2"></i>
                        {{ $isEditing ? 'Update Courier Configuration' : 'Setup New Courier' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetInputs"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 bg-light">

                        <!-- Row 1: General Info -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white fw-bold border-bottom py-3">
                                <i class="fas fa-info-circle text-primary me-2"></i> General Information
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-semibold">Display Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            wire:model="name" placeholder="e.g. My Shop Pathao Service">
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Vendor <span class="text-danger">*</span></label>
                                        <select class="form-select @error('vendor') is-invalid @enderror" wire:model.live="vendor">
                                            <option value="custom">Custom (Manual)</option>
                                            <option value="pathao">Pathao</option>
                                            <option value="steadfast">Steadfast</option>
                                            <option value="redx">RedX</option>
                                            <option value="carrybee">Carrybee</option>
                                        </select>
                                        @error('vendor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch border p-2 rounded ps-5 bg-white">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" id="modal_is_active" wire:model="is_active">
                                            <label class="form-check-label fw-bold" for="modal_is_active">Mark as Active</label>
                                            <div class="form-text mt-0 small">Inactive couriers won't appear during order processing.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Dynamic API Credentials -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white fw-bold border-bottom py-3 d-flex justify-content-between">
                                <span><i class="fas fa-key text-primary me-2"></i> API Configuration</span>
                                <span class="badge bg-primary text-uppercase">{{ $vendor }}</span>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    @if($vendor === 'pathao')
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Client Id *</label>
                                        <input type="text" class="form-control @error('credentials.client_id') is-invalid @enderror" wire:model="credentials.client_id">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Client Secret *</label>
                                        <input type="text" class="form-control @error('credentials.client_secret') is-invalid @enderror" wire:model="credentials.client_secret">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Username *</label>
                                        <input type="text" class="form-control @error('credentials.username') is-invalid @enderror" wire:model="credentials.username">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Password *</label>
                                        <input type="password" class="form-control @error('credentials.password') is-invalid @enderror" wire:model="credentials.password">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Grant Type *</label>
                                        <input type="text" class="form-control @error('credentials.grant_type') is-invalid @enderror" wire:model="credentials.grant_type">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Store Id *</label>
                                        <input type="text" class="form-control @error('credentials.store_id') is-invalid @enderror" wire:model="credentials.store_id">
                                    </div>

                                    @elseif($vendor === 'steadfast')
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Api-Key *</label>
                                        <input type="text" class="form-control @error('credentials.api_key') is-invalid @enderror" wire:model="credentials.api_key">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Secret-Key *</label>
                                        <input type="text" class="form-control @error('credentials.secret_key') is-invalid @enderror" wire:model="credentials.secret_key">
                                    </div>

                                    @elseif($vendor === 'redx')
                                    <div class="col-12 mb-3">
                                        <label class="form-label small fw-bold">API Token (Long string) *</label>
                                        <textarea class="form-control @error('credentials.api_token') is-invalid @enderror" wire:model="credentials.api_token" rows="4" placeholder="Paste your RedX token here..."></textarea>
                                    </div>

                                    @elseif($vendor === 'carrybee')
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Client Id *</label>
                                        <input type="text" class="form-control @error('credentials.client_id') is-invalid @enderror" wire:model="credentials.client_id">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Client Secret *</label>
                                        <input type="text" class="form-control @error('credentials.client_secret') is-invalid @enderror" wire:model="credentials.client_secret">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label small fw-bold">Client Context *</label>
                                        <input type="text" class="form-control @error('credentials.client_context') is-invalid @enderror" wire:model="credentials.client_context">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Store Id *</label>
                                        <input type="text" class="form-control @error('credentials.store_id') is-invalid @enderror" wire:model="credentials.store_id">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Delivery Type *</label>
                                        <select class="form-select @error('credentials.delivery_type') is-invalid @enderror" wire:model="credentials.delivery_type">
                                            <option value="">Select Type</option>
                                            <option value="Home Delivery">Home Delivery</option>
                                            <option value="Point Delivery">Point Delivery</option>
                                        </select>
                                    </div>

                                    @else
                                    <div class="col-12 text-center py-4">
                                        <p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i> No API credentials required for manual Custom vendors.</p>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-white">
                        <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal" wire:click="resetInputs">Cancel</button>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-2" role="status"></span>
                            {{ $isEditing ? 'Update Configuration' : 'Save & Enable' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Integration Scripts -->
    <script>
        window.addEventListener('show-courier-modal', event => {
            var myModal = new bootstrap.Modal(document.getElementById('courierModal'));
            myModal.show();
        });

        window.addEventListener('hide-courier-modal', event => {
            var modalEl = document.getElementById('courierModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        });
    </script>

    <!-- Optional Styles for Custom Badge look -->
    <style>
        .bg-label-info {
            background-color: #e7f7ff;
            color: #007bff;
            border-color: #007bff;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .btn-white {
            background-color: #fff;
        }
    </style>
</div>