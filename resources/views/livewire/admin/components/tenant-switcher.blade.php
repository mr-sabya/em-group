<div class="d-flex align-items-center">
    <div class="dropdown topbar-head-dropdown ms-1 header-item">
        <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle"
            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class='bi bi-shop fs-18'></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">Select Store</h6>

            @foreach($tenants as $tenant)
            <a href="javascript:void(0);"
                wire:click="switchTenant('{{ $tenant->id }}')"
                class="dropdown-item d-flex align-items-center {{ $activeTenant->id == $tenant->id ? 'active' : '' }}">
                <div class="flex-grow-1">
                    <h6 class="m-0">{{ $tenant->id }}</h6> {{-- Or $tenant->name if you added a name column --}}
                </div>
                @if($activeTenant->id == $tenant->id)
                <i class="bi bi-check-circle-fill text-success ms-2"></i>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    <div class="ms-2 d-none d-lg-block">
        <span class="badge bg-info-subtle text-info fs-12">
            <i class="bi bi-geo-alt me-1"></i> Active: {{ strtoupper($activeTenant->id) }}
        </span>
    </div>

</div>