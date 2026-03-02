@extends('backend.layouts.app')

@section('content')
<livewire:investors.manage userId="{{ $userId }}" />
@endsection