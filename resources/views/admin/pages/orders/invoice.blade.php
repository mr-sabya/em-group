@extends('backend.layouts.app')

@section('content')
<livewire:orders.invoice orderId="{{ $orderId }}" />
@endsection