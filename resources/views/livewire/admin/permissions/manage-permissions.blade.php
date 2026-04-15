<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Permissions Management</h5>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" placeholder="Search..." wire:model.live="search" style="width: 250px;">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkCreateModal" wire:click="resetInputs">
                            <i class="fas fa-plus"></i> Add Bulk Permissions
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if (session()->has('message'))
                    <div class="alert alert-success m-3 alert-dismissible fade show">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Group</th>
                                <th>Guard</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $permission)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><code>{{ $permission->name }}</code></td>
                                <td><span class="badge bg-info text-dark">{{ $permission->group_name }}</span></td>
                                <td><span class="badge bg-secondary">{{ $permission->guard_name }}</span></td>
                                <td class="text-end">
                                    <button wire:click="editPermission({{ $permission->id }})" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:confirm="Delete this permission?" wire:click="deletePermission({{ $permission->id }})" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No permissions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">{{ $permissions->links() }}</div>
            </div>
        </div>
    </div>

    <!-- BULK CREATE MODAL -->
    <div wire:ignore.self class="modal fade" id="bulkCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Bulk Permissions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetInputs"></button>
                </div>
                <form wire:submit.prevent="saveBulk">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Group Name</label>
                                <input type="text" list="groupOptions" class="form-control @error('group_name') is-invalid @enderror" wire:model="group_name">
                                @error('group_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Guard Name</label>
                                <select class="form-select" wire:model="guard_name">
                                    <option value="admin">Admin</option>
                                    <option value="web">Web</option>
                                </select>
                            </div>
                        </div>

                        <h6>Permissions List</h6>
                        @foreach($bulkPermissions as $index => $item)
                        <div class="input-group mb-2">
                            <input type="text" class="form-control @error('bulkPermissions.'.$index.'.name') is-invalid @enderror"
                                wire:model="bulkPermissions.{{ $index }}.name" placeholder="Permission Name (e.g. user.view)">
                            @if(count($bulkPermissions) > 1)
                            <button class="btn btn-outline-danger" type="button" wire:click="removeRow({{ $index }})"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                        @error('bulkPermissions.'.$index.'.name') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        @endforeach

                        <button type="button" class="btn btn-sm btn-dark mt-2" wire:click="addRow">+ Add More Fields</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Permissions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SINGLE EDIT MODAL -->
    <div wire:ignore.self class="modal fade" id="editPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="updatePermission">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Permission Name</label>
                            <input type="text" class="form-control @error('editName') is-invalid @enderror" wire:model="editName">
                            @error('editName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Group Name</label>
                            <input type="text" list="groupOptions" class="form-control @error('group_name') is-invalid @enderror" wire:model="group_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Guard Name</label>
                            <select class="form-select" wire:model="guard_name">
                                <option value="admin">Admin</option>
                                <option value="web">Web</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <datalist id="groupOptions">
        @foreach($existingGroups as $group) <option value="{{ $group }}"> @endforeach
    </datalist>

    <!-- Modal Scripts -->
    <script>
        window.addEventListener('close-modal', event => {
            bootstrap.Modal.getInstance(document.getElementById('bulkCreateModal'))?.hide();
            bootstrap.Modal.getInstance(document.getElementById('editPermissionModal'))?.hide();
        });

        window.addEventListener('open-edit-modal', event => {
            var myModal = new bootstrap.Modal(document.getElementById('editPermissionModal'));
            myModal.show();
        });
    </script>
</div>