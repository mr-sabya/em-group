@extends('backend.layouts.app')

@section('content')
<livewire:collection.manage collectionId="{{ $collectionId }}" />
@endsection