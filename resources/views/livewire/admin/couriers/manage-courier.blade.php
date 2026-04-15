<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark">{{ $courier ? 'Edit Courier' : 'Create New Courier' }}</h4>
        <a href="{{ route('courier.index') }}" class="btn btn-outline-secondary btn-sm" wire:navigate>
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="row">
            <div class="col-lg-8">

                <!-- Section: General Information -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">General Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. Pathao Express">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch border p-2 rounded ps-5">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="is_active" wire:model="is_active">
                            <label class="form-check-label fw-bold mb-0" for="is_active">Active Status</label>
                            <div class="text-muted small">Only active couriers will be shown in order assignment.</div>
                        </div>
                    </div>
                </div>

                <!-- Section: Courier Vendor -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">Courier Vendor</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Vendor</label>
                            <select class="form-select @error('vendor') is-invalid @enderror" wire:model.live="vendor">
                                <option value="custom">Custom</option>
                                <option value="pathao">Pathao</option>
                                <option value="steadfast">Steadfast</option>
                                <option value="redx">RedX</option>
                                <option value="carrybee">Carrybee</option>
                            </select>
                            @error('vendor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Credentials -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">API Credentials</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Api-Key</label>
                                <input type="text" class="form-control @error('credentials.Api-Key') is-invalid @enderror"
                                    wire:model="credentials.Api-Key" placeholder="Enter API Key">
                                @error('credentials.Api-Key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Secret-Key</label>
                                <input type="text" class="form-control @error('credentials.Secret-Key') is-invalid @enderror"
                                    wire:model="credentials.Secret-Key" placeholder="Enter Secret Key">
                                @error('credentials.Secret-Key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-5 py-2">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-2"></span>
                        {{ $courier ? 'Update Configuration' : 'Create Courier' }}
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>