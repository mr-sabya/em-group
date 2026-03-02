@extends('backend.layouts.app')

@section('content')
<livewire:vendors.manage userId="{{ $userId }}" />
@endsection