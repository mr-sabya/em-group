<div>
    @if ($showModal)
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Categories to Coupon (ID: {{ $couponId }})</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session()->has('category_assignment_message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('category_assignment_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Search categories..." wire:model.live.debounce.300ms="search">
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>
                                        @if (in_array($category->id, $assignedCategoryIds))
                                        <button type="button" class="btn btn-sm btn-warning" wire:click="unassignCategory({{ $category->id }})">
                                            <i class="fas fa-minus-circle me-1"></i> Unassign
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-success" wire:click="assignCategory({{ $category->id }})">
                                            <i class="fas fa-plus-circle me-1"></i> Assign
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No categories found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $categories->links() }}
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