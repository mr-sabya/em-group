<div>
    <div class="card shadow-sm">
        <!-- Card Header -->
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Admin Users</h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" placeholder="Search..." wire:model.live="search" style="width: 200px;">
                <button class="btn btn-primary btn-sm text-nowrap flex-shrink-0" data-bs-toggle="modal" data-bs-target="#adminModal" wire:click="resetInputs">
                    <i class="fas fa-plus"></i> Create Admin
                </button>
            </div>
        </div>

        <!-- Table Body -->
        <div class="card-body p-0">
            @if (session()->has('message'))
            <div class="alert alert-success m-3 alert-dismissible fade show">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if (session()->has('error'))
            <div class="alert alert-danger m-3 alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th class="text-end" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                @foreach($admin->roles as $role)
                                <span class="badge bg-primary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <!-- Key Icon for Password -->
                                    <button wire:click="editPassword({{ $admin->id }})" class="btn btn-sm btn-outline-warning" title="Change Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <!-- Edit Icon -->
                                    <button wire:click="editAdmin({{ $admin->id }})" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <!-- Trash Icon -->
                                    <button wire:confirm="Delete this admin?" wire:click="deleteAdmin({{ $admin->id }})" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $admins->links() }}
        </div>
    </div>

    <!-- MAIN ADMIN MODAL (CREATE/EDIT) -->
    <div wire:ignore.self class="modal fade" id="adminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditing ? 'Edit Admin' : 'New Admin' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetInputs"></button>
                </div>
                <form wire:submit.prevent="saveAdmin">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if(!$isEditing)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" wire:model="password_confirmation">
                            </div>
                        </div>
                        @endif

                        <div class="mb-0">
                            <label class="form-label d-block fw-bold">Assign Roles</label>
                            <div class="row px-2">
                                @foreach($roles as $role)
                                <div class="col-6 form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $role->name }}" id="role-{{ $role->id }}" wire:model="selectedRoles">
                                    <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                                </div>
                                @endforeach
                            </div>
                            @error('selectedRoles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading wire:target="saveAdmin" class="spinner-border spinner-border-sm"></span>
                            {{ $isEditing ? 'Update Admin' : 'Save Admin' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PASSWORD CHANGE MODAL -->
    <div wire:ignore.self class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-warning shadow">
                <div class="modal-header bg-warning text-dark p-3">
                    <h5 class="modal-title"><i class="fas fa-lock"></i> Reset Password for {{ $name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetInputs"></button>
                </div>
                <form wire:submit.prevent="updatePassword">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" wire:model="new_password">
                            @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" wire:model="new_password_confirmation">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS Scripts for Modals -->
    <script>
        window.addEventListener('close-modal', event => {
            bootstrap.Modal.getInstance(document.getElementById('adminModal'))?.hide();
            bootstrap.Modal.getInstance(document.getElementById('passwordModal'))?.hide();
        });

        window.addEventListener('open-admin-modal', event => {
            (new bootstrap.Modal(document.getElementById('adminModal'))).show();
        });

        window.addEventListener('open-password-modal', event => {
            (new bootstrap.Modal(document.getElementById('passwordModal'))).show();
        });
    </script>
</div>