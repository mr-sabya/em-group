<div class="login-container d-flex align-items-center justify-content-center">
    <div class="login-card p-4 p-md-5 rounded shadow-lg text-center">
        <div class="login-box">
            <div class="card card-round">
                <div class="card-body">
                    <h3 class="text-center">Admin Password Reset</h3>
                    <p class="text-muted text-center">Enter your email to receive a reset link</p>

                    @if ($status)
                    <div class="alert alert-success">{{ $status }}</div>
                    @endif

                    <form wire:submit="sendResetLink">
                        <div class="form-group mb-3">
                            <label>Email Address</label>
                            <input type="email" wire:model="email" class="form-control">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove>Send Reset Link</span>
                            <span wire:loading><i class="fas fa-spinner fa-spin"></i> Sending...</span>
                        </button>

                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" wire:navigate class="small">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>