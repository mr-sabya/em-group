<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Account Settings</div>
                <p class="card-category">Update your administrative profile information</p>
            </div>
            <div class="card-body">
                @if (session()->has('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
                @endif

                <form wire:submit="updateProfile">
                    <div class="form-group mb-3">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            wire:model="name" id="name" placeholder="Enter Name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            wire:model="email" id="email" placeholder="Enter Email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="card-action p-0 pt-3 border-top">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Save Changes</span>
                            <span wire:loading><i class="fas fa-spinner fa-spin me-2"></i> Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>