@extends('admin.layouts.app')

@section('content')
<livewire:admin.page.manage pageId="{{ $page->id }}" />
@endsection