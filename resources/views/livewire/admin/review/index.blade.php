<div>
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h4 mb-0">Customer Reviews</h2>
            <p class="text-muted small mb-0">Managing feedback for: <strong>{{ $this->currentTenant->name }}</strong></p>
        </div>

        <div class="d-flex gap-2">
            <div class="input-group shadow-sm" style="min-width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search by comment, product or customer...">
            </div>

            <select wire:model.live="statusFilter" class="form-select w-auto shadow-sm">
                <option value="">All Status</option>
                @foreach(App\Enums\ReviewStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if (session()->has('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Content Card -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-bold">
                    <tr>
                        <th class="ps-4 py-3">Product Info</th>
                        <th>Customer</th>
                        <th>Comment & Review</th>
                        <th class="text-center" style="cursor:pointer" wire:click="sortBy('rating')">
                            Rating @if($sortField === 'rating') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @endif
                        </th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr wire:key="review-{{ $review->id }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <img src="{{ $review->product->thumbnail_image_path ? asset('storage/'.$review->product->thumbnail_image_path) : 'https://ui-avatars.com/api/?name='.urlencode($review->product->name) }}"
                                    class="rounded shadow-sm me-3" style="width:48px;height:48px;object-fit:cover">
                                <div>
                                    <div class="fw-bold text-dark">{{ $review->product->name }}</div>
                                    <small class="text-muted">SKU: {{ $review->product->sku ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $review->user->name }}</div>
                            <small class="text-muted">{{ $review->user->email }}</small>
                        </td>
                        <td style="max-width: 300px;">
                            <p class="mb-0 text-truncate-2 small text-secondary italic">"{{ $review->comment }}"</p>
                            <small class="text-muted d-block mt-1">{{ $review->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-center">
                            <div class="text-warning">
                                @for($i=1; $i<=5; $i++)
                                    <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star fa-xs"></i>
                                    @endfor
                            </div>
                            <span class="small fw-bold">{{ $review->rating }}.0</span>
                        </td>
                        <td>
                            @php
                            $badgeClass = match($review->status->value) {
                            'approved' => 'bg-success-subtle text-success',
                            'pending' => 'bg-warning-subtle text-warning',
                            'rejected' => 'bg-danger-subtle text-danger',
                            default => 'bg-secondary-subtle text-secondary'
                            };
                            @endphp
                            <span class="badge {{ $badgeClass }} border px-3 py-2 rounded-pill text-uppercase" style="font-size: 0.7rem;">
                                {{ $review->status->label() }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <button wire:click="editStatus({{ $review->id }})" class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm hover-primary">
                                <i class="fas fa-cog me-1"></i> Manage
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="py-4">
                                <i class="fas fa-comment-slash fa-3x text-light mb-3"></i>
                                <h5 class="text-muted">No reviews found for this store.</h5>
                                <p class="text-muted small">Try adjusting your filters or search terms.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $reviews->links() }}
        </div>
    </div>

    <!-- Professional Status Modal -->
    <div wire:ignore.self class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form wire:submit.prevent="updateStatus" class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Review Decision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    @if($reviewToEdit)
                    <div class="d-flex align-items-start bg-light p-3 rounded mb-4 border">
                        <i class="fas fa-quote-left text-primary me-3 mt-1"></i>
                        <div>
                            <p class="mb-1 text-dark fw-medium small">{{ $reviewToEdit->comment }}</p>
                            <small class="text-muted">— {{ $reviewToEdit->user->name }} on {{ $reviewToEdit->product->name }}</small>
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label fw-bold small text-uppercase text-muted">Action to take:</label>
                        <select wire:model="newStatus" class="form-select form-select-lg">
                            @foreach(App\Enums\ReviewStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <div class="form-text mt-2 small">
                            <i class="fas fa-info-circle me-1"></i> Approving a review will make it visible to all customers on the product page.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <span wire:loading wire:target="updateStatus" class="spinner-border spinner-border-sm me-2"></span>
                        Save Decision
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .bg-success-subtle {
            background-color: #e6fffa;
        }

        .bg-warning-subtle {
            background-color: #fffaf0;
        }

        .bg-danger-subtle {
            background-color: #fff5f5;
        }

        .hover-primary:hover {
            background-color: #0d6efd !important;
            color: white !important;
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-status-modal', (event) => {
                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                modal.show();
            });

            Livewire.on('close-status-modal', (event) => {
                const modalElement = document.getElementById('statusModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            });
        });
    </script>
</div>