@extends('admin.layouts.app')

@section('content')

<livewire:admin.categories.manage categoryId="{{ $id }}" />

@endsection