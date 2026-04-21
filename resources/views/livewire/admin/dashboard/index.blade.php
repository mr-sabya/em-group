<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Global Network Analytics</h4>
                <div class="page-title-right">
                    <button class="btn btn-primary" wire:click="$set('showTenantModal', true)">
                        <i class="ri-add-line align-bottom me-1"></i> Add New Tenant
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Stat Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate bg-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-white-50 mb-0">Total Global Revenue</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-white">${{ number_format($stats['total_revenue'], 2) }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-white bg-opacity-10 rounded fs-3">
                                <i class="bi bi-graph-up-arrow text-white"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Repeat for other stats -->
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted mb-0">Active Tenants</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-4">{{ $stats['total_tenants'] }}</h4>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted mb-0">Platform Admins</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-4">{{ $stats['total_admins'] }}</h4>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <p class="text-uppercase fw-medium text-muted mb-0">Global Orders</p>
                    <h4 class="fs-22 fw-semibold ff-secondary mt-4">{{ number_format($stats['total_orders']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Global Revenue Growth</h4>
                </div>
                <div class="card-body">
                    <div id="revenue_chart" wire:ignore></div>
                </div>
            </div>
        </div>

        <!-- Executive Leaderboard -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Executive Admins</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Executive</th>
                                    <th>Total Tasks/Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($executives === 0)
                                <div class="alert alert-info">No executive accounts found.</div>
                                @else
                                @foreach($executives as $exec)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $exec->name }}</h6>
                                                <small class="text-muted">{{ $exec->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-info">{{ $exec->orders_count }} handled</span></td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenant List Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Tenant Performance List</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Store Name</th>
                                    <th>Domains</th>
                                    <th>Total Orders</th>
                                    <th>Revenue Contribution</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tenants as $tenant)
                                <tr>
                                    <td><span class="fw-bold">{{ strtoupper($tenant->id) }}</span></td>
                                    <td>{{ $tenant->name }}</td>
                                    <td>
                                        @foreach($tenant->domains as $d)
                                        <small class="badge bg-light text-dark border">{{ $d->domain }}</small>
                                        @endforeach
                                    </td>
                                    <td>{{ $tenant->orders_count }}</td>
                                    <td><span class="text-success fw-bold">${{ number_format($tenant->revenue, 2) }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-soft-secondary" wire:click="switchTenant('{{ $tenant->id }}')">Login to Store</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Tenant Modal -->
    @if($showTenantModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Provision New Tenant</h5>
                    <button type="button" class="btn-close" wire:click="$set('showTenantModal', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" class="form-control" wire:model.live="tenantName" placeholder="e.g. Nike Store">
                        @error('tenantName') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tenant ID (Slug)</label>
                        <input type="text" class="form-control" wire:model="tenantId" placeholder="nike-store">
                        <small class="text-muted">Domain will be: <b>{{ $tenantId ?: 'slug' }}.{{ config('tenancy.central_domain') }}</b></small>
                        @error('tenantId') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="$set('showTenantModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="createTenant">Create Database & Domain</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:navigated', () => {
        var options = {
            series: [{
                name: 'Revenue',
                data: @json($chartValues)
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#405189'],
            xaxis: {
                categories: @json($chartLabels)
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.5,
                    opacityTo: 0.1
                }
            }
        };
        var chart = new ApexCharts(document.querySelector("#revenue_chart"), options);
        chart.render();
    });
</script>
@endpush