<div>
    @if ($showModal)
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Products to Coupon (ID: {{ $couponId }})</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session()->has('product_assignment_message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('product_assignment_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Search products..." wire:model.live.debounce.300ms="search">
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
                                @forelse ($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>
                                        @if (in_array($product->id, $assignedProductIds))
                                        <button type="button" class="btn btn-sm btn-warning" wire:click="unassignProduct({{ $product->id }})">
                                            <i class="fas fa-minus-circle me-1"></i> Unassign
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-success" wire:click="assignProduct({{ $product->id }})">
                                            <i class="fas fa-plus-circle me-1"></i> Assign
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No products found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $products->links() }}
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