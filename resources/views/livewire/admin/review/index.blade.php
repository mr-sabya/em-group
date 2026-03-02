<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage Reviews</h5>
            <div class="d-flex gap-2">
                <input type="text" wire:model.live="search" class="form-control" placeholder="Search...">
                <select wire:model.live="statusFilter" class="form-select w-auto">
                    <option value="">All Status</option>
                    @foreach(App\Enums\ReviewStatus::cases() as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customer</th>
                        <th style="cursor:pointer" wire:click="sortBy('rating')">Rating <i class="fas fa-sort small"></i></th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $review->product->thumbnail_url }}" class="rounded me-2" style="width:40px;height:40px;object-fit:cover">
                                <div>
                                    <div class="fw-bold">{{ $review->product->name }}</div>
                                    <small class="text-muted">ID: #{{ $review->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $review->user->name }}</td>
                        <td>
                            <div class="text-warning small">
                                @for($i=1; $i<=5; $i++) <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i> @endfor
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $review->status->value === 'approved' ? 'success' : ($review->status->value === 'pending' ? 'warning' : 'danger') }}">
                                {{ $review->status->label() }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button wire:click="editStatus({{ $review->id }})" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Status
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $reviews->links() }}</div>
    </div>

    <!-- Status Update Modal -->
    <div wire:ignore.self class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form wire:submit.prevent="updateStatus" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Review Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($reviewToEdit)
                    <div class="alert alert-light border small">
                        <strong>Product:</strong> {{ $reviewToEdit->product->name }} <br>
                        <strong>Comment:</strong> "{{ $reviewToEdit->comment }}"
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label fw-bold">Select New Status</label>
                        <select wire:model="newStatus" class="form-select">
                            @foreach(App\Enums\ReviewStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>



    <script>
        // JS listeners to handle the Bootstrap Modal toggle
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-status-modal', (event) => {
                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                modal.show();
            });

            Livewire.on('close-status-modal', (event) => {
                const modalElement = document.getElementById('statusModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            });
        });
    </script>
</div>