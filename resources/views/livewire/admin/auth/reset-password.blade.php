<div class="login-container d-flex align-items-center justify-content-center">
    <div class="login-card p-4 p-md-5 rounded shadow-lg text-center">
        <div class="login-box">
            <div class="card card-round">
                <div class="card-body">
                    <h3 class="text-center">Set New Password</h3>

                    <form wire:submit="resetPassword">
                        <input type="hidden" wire:model="token">

                        <div class="form-group mb-3">
                            <label>Email Address</label>
                            <input type="email" wire:model="email" class="form-control" readonly>
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>New Password</label>
                            <input type="password" wire:model="password" class="form-control">
                            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Confirm Password</label>
                            <input type="password" wire:model="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove>Update Password</span>
                            <span wire:loading><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>