@extends('backend.layouts.app')

@section('content')
<livewire:coupon.manage couponId="{{ $coupon->id }}" />
@endsection