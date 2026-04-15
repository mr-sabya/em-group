<div>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Roles & Permissions</h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control" placeholder="Search..." wire:model.live="search">
                <button class="btn btn-primary text-nowrap flex-shrink-0" data-bs-toggle="modal" data-bs-target="#roleModal" wire:click="resetInputs">
                    <i class="fas fa-plus"></i> Create Role
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if (session()->has('message'))
            <div class="alert alert-success m-3">{{ session('message') }}</div>
            @endif

            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Role Name</th>
                        <th>Guard</th>
                        <th>Permissions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td><strong>{{ $role->name }}</strong></td>
                        <td><span class="badge bg-secondary">{{ $role->guard_name }}</span></td>
                        <td>
                            @foreach($role->permissions as $perm)
                            <span class="badge bg-light text-dark border">{{ $perm->name }}</span>
                            @endforeach
                        </td>
                        <td class="text-end">
                            <button wire:click="editRole({{ $role->id }})" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                            <button wire:confirm="Delete this role?" wire:click="deleteRole({{ $role->id }})" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $roles->links() }}</div>
    </div>

    <!-- ROLE MODAL -->
    <div wire:ignore.self class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditing ? 'Edit Role' : 'New Role' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="saveRole">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="form-label">Role Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. Editor">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Guard Name</label>
                                <select class="form-select" wire:model.live="guard_name">
                                    <option value="admin">Admin</option>
                                    <option value="web">Web</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Assign Permissions</h6>
                            @error('selectedPermissions') <span class="text-danger small">Select at least one permission</span> @enderror
                        </div>

                        <div class="row">
                            @foreach($groupedPermissions as $groupName => $permissions)
                            <div class="col-md-4 mb-4">
                                <div class="card border">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                id="group-{{ Str::slug($groupName) }}"
                                                wire:click="toggleGroup('{{ $groupName }}')"
                                                {{ App\Models\Admin::roleHasPermissions($selectedPermissions, $permissions) ? 'checked' : '' }}
                                                {{-- Note: roleHasPermissions helper might need a slight tweak to accept an array --}}>
                                            <label class="form-check-label fw-bold" for="group-{{ Str::slug($groupName) }}">
                                                {{ $groupName }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @foreach($permissions as $perm)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                value="{{ $perm->name }}"
                                                wire:model="selectedPermissions"
                                                id="perm-{{ $perm->id }}">
                                            <label class="form-check-label" for="perm-{{ $perm->id }}">
                                                {{ $perm->name }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Role & Permissions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('close-modal', event => {
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
        });
        window.addEventListener('open-role-modal', event => {
            (new bootstrap.Modal(document.getElementById('roleModal'))).show();
        });
    </script>
</div>