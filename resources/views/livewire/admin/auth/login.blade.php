 <div class="p-2 mt-5">

     <form wire:submit.prevent="authenticate">

         <div class="mb-3">
             <label for="email" class="form-label">Email</label>
             <div class="input-group">
                 <span class="input-group-text"><i class="ri-user-3-line"></i></span>
                 <input type="text" wire:model.defer="email" class="form-control" id="email" placeholder="Enter email">
             </div>
             @error('email') <small class="text-danger">{{ $message }}</small> @enderror
         </div>

         <div class="mb-3">
             <div class="float-end">
                 <a href="auth-pass-reset-basic.html" class="text-muted">Forgot password?</a>
             </div>
             <label class="form-label" for="password-input">Password</label>

             <div class="position-relative">
                 <div class="input-group">
                     <span class="input-group-text"><i class="ri-lock-2-line"></i></span>
                     <input type="password" wire:model.defer="password" class="form-control" placeholder="Enter password" id="password-input">
                 </div>
             </div>

             @error('password') <small class="text-danger">{{ $message }}</small> @enderror
         </div>

         <div class="form-check">
             <input class="form-check-input" wire:model="remember" type="checkbox" id="auth-remember-check">
             <label class="form-check-label" for="auth-remember-check">Remember me</label>
         </div>

         <div class="mt-4">
             <button class="btn btn-primary w-100" type="submit">
                 Sign In
             </button>
         </div>

     </form>

 </div>