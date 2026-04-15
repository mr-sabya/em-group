@extends('admin.layouts.app')

@section('content')
<livewire:admin.customers.manage userId="{{ $userId }}" />
@endsection