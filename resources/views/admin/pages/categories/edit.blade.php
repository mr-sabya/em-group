@extends('backend.layouts.app')

@section('content')

<livewire:categories.manage categoryId="{{ $id }}" />

@endsection