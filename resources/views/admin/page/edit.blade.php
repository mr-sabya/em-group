@extends('backend.layouts.app')

@section('content')
<livewire:page.manage pageId="{{ $page->id }}" />
@endsection