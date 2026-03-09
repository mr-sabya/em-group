@extends('admin.layouts.app')

@section('content')
<livewire:admin.deal.manage dealId="{{ $dealId }}" />
@endsection