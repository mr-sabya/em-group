@extends('admin.layouts.app')

@section('content')
<livewire:admin.product.manage productId="{{ $product->id }}" />
@endsection