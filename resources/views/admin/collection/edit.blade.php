@extends('admin.layouts.app')

@section('content')
<livewire:admin.collection.manage collectionId="{{ $collectionId }}" />
@endsection