<div>
    <div class="page-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-xxl-5 col-lg-7">
                    <div class="card mt-4">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <h4 class="text-primary">Create Your First Store</h4>
                                <p class="text-muted">You must initialize at least one store to manage products and orders.</p>
                            </div>

                            <form wire:submit.prevent="save" class="mt-4">
                                <!-- Store Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control" id="name" placeholder="Enter store name (e.g. Gadget Hub)">
                                    @error('name') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                                </div>

                                <!-- Store Identifier (ID) -->
                                <div class="mb-3">
                                    <label for="tenant_id" class="form-label">Store Identifier (ID) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">ID</span>
                                        <input type="text" wire:model="tenant_id" class="form-control" id="tenant_id" placeholder="gadget-hub">
                                        <!-- Manual Generate Button -->
                                        <button type="button" class="btn btn-soft-secondary" wire:click.prevent="generateId">
                                            <i class="bi bi-magic me-1"></i> Generate
                                        </button>
                                    </div>
                                    <p class="text-muted fs-11 mt-1">Used for internal scoping and URLs. No spaces allowed. Click generate to create from name.</p>
                                    @error('tenant_id') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                                </div>

                                <div class="mt-4">
                                    <button class="btn btn-primary w-100" type="submit">
                                        <span wire:loading.remove wire:target="save">Initialize Store</span>
                                        <span wire:loading wire:target="save">Processing...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>