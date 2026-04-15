<div>
    <!-- Header Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold">Cancel Reasons</h4>
            <button wire:click="openCreateModal" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-2"></i>New Reason
            </button>
        </div>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control bg-light" placeholder="Search reasons..." wire:model.live="search">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 cursor-pointer" wire:click="sortBy('name')">
                                Reason Name @if($sortField === 'name') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                            </th>
                            <th class="text-center">Label Color</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reasons as $reason)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $reason->name }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-2" style="background-color: {{ $reason->color }}; color: #fff;">
                                    {{ $reason->color }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="edit({{ $reason->id }})" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></button>
                                <button wire:confirm="Delete this reason?" wire:click="delete({{ $reason->id }})" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">No reasons found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            {{ $reasons->links() }}
        </div>
    </div>

    <!-- CREATE/EDIT MODAL -->
    <div wire:ignore.self class="modal fade" id="reasonModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold">{{ $isEditing ? 'Edit Reason' : 'Create Reason' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetInputs"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Reason Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. Out of Stock">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Label Color</label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control form-control-color me-3 border-0"
                                    wire:model="color" title="Choose your color">
                                <input type="text" class="form-control" wire:model="color" style="width: 120px;" maxlength="7">
                            </div>
                            <small class="text-muted">This color will be used for the badge in order lists.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" wire:click="resetInputs">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-2"></span>
                            {{ $isEditing ? 'Save Changes' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-reason-modal', event => {
            (new bootstrap.Modal(document.getElementById('reasonModal'))).show();
        });
        window.addEventListener('hide-reason-modal', event => {
            bootstrap.Modal.getInstance(document.getElementById('reasonModal')).hide();
        });
    </script>
</div>