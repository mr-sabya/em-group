<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Hub | Management Console</title>

    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hub-card {
            max-width: 500px;
            width: 100%;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .btn-admin {
            background-color: #1e293b;
            color: white;
            padding: 14px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .btn-admin:hover {
            background-color: #0f172a;
            color: white;
            transform: translateY(-1px);
        }

        .website-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 10px;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .website-item:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }
    </style>
</head>

<body>

    <div class="container p-3">
        <div class="card hub-card mx-auto shadow-lg">
            <div class="card-body p-5">

                <!-- Brand / Logo -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-3 mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-grid-1x2-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Business Hub</h4>
                    <p class="text-muted small">Manage your network of websites</p>
                </div>

                <hr class="my-4 opacity-50">

                <!-- Primary Action -->
                <div class="d-grid mb-5">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-admin d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-shield-lock"></i>
                        Super Admin Login
                    </a>
                </div>

                <!-- Active Websites List -->
                <h6 class="fw-bold mb-3 text-uppercase small text-muted tracking-wide">Your Active Websites</h6>

                <div class="website-list">
                    @php
                    // In a real app, you'd pass this from the Controller
                    $tenants = \App\Models\Tenant::all();
                    @endphp

                    @forelse($tenants as $tenant)
                    <a href="{{ url($tenant->id . '/dashboard') }}" class="website-item">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-browser-safari me-3 text-primary"></i>
                            <div>
                                <div class="fw-bold small">{{ $tenant->name ?? $tenant->id }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $tenant->id }}.yourdomain.com</div>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right small text-muted"></i>
                    </a>
                    @empty
                    <div class="text-center py-3 text-muted small border rounded-3 border-dashed">
                        No websites created yet.
                    </div>
                    @endforelse
                </div>

                <div class="text-center mt-5">
                    <p class="text-muted" style="font-size: 0.7rem;">&copy; {{ date('Y') }} Internal Management System</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3.2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>