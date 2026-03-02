@extends('admin.layouts.guest')

@section('content')
<section class="auth-page-wrapper-2 py-4 position-relative d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="container">
        <div class="row g-0">
            <div class="col-lg-5">
                <div class="auth-card card bg-primary h-100 rounded-0 rounded-start border-0 d-flex align-items-center justify-content-center overflow-hidden p-4">
                    <div class="auth-image">
                        <img src="{{ url('assets/images/logo-light-full.png') }}" alt="" height="42" />
                        <img src="{{ url('assets/images/effect-pattern/auth-effect-2.png') }}" alt="" class="auth-effect-2" />
                        <img src="{{ url('assets/images/effect-pattern/auth-effect.png') }}" alt="" class="auth-effect" />
                        <img src="{{ url('assets/images/effect-pattern/auth-effect.png') }}" alt="" class="auth-effect" />
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card mb-0 rounded-0 rounded-end border-0">
                    <div class="card-body p-4 p-sm-5 m-lg-4">
                        <div class="text-center mt-2">
                            <h5 class="text-primary fs-22">Welcome Back !</h5>
                            <p class="text-muted">Sign in to continue to Hybrix.</p>
                        </div>
                        <livewire:admin.auth.login />
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
        </div>
    </div>
</section>
@endsection