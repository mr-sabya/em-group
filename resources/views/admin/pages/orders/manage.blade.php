@extends('backend.layouts.app')

@section('content')
<livewire:orders.manage orderId="{{ $order->id }}" />
@endsection