<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title">Security</div>
                <p class="card-category">Ensure your account is using a long, random password to stay secure</p>
            </div>
            <div class="card-body">
                @if (session()->has('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    <i class="fas fa-lock me-2"></i> {{ session('success') }}
                </div>
                @endif

                <form wire:submit="updatePassword">
                    <div class="form-group mb-3">
                        <label>Current Password</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                            wire:model="current_password">
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>New Password</label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                            wire:model="new_password">
                        @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label>Confirm New Password</label>
                        <input type="password" class="form-control"
                            wire:model="new_password_confirmation">
                    </div>

                    <div class="card-action p-0 pt-3 border-top">
                        <button type="submit" class="btn btn-secondary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Update Password</span>
                            <span wire:loading><i class="fas fa-spinner fa-spin me-2"></i> Changing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>