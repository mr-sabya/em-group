<div>
    @if ($showModal)
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Users to Coupon (ID: {{ $couponId }})</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session()->has('user_assignment_message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('user_assignment_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Search users by name or email..." wire:model.live.debounce.300ms="search">
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name ?? 'N/A' }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if (in_array($user->id, $assignedUserIds))
                                        <button type="button" class="btn btn-sm btn-warning" wire:click="unassignUser({{ $user->id }})">
                                            <i class="fas fa-minus-circle me-1"></i> Unassign
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-success" wire:click="assignUser({{ $user->id }})">
                                            <i class="fas fa-plus-circle me-1"></i> Assign
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No users found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>