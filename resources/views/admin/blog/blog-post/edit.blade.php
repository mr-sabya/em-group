@extends('admin.layouts.app')

@section('content')
<livewire:admin.blog-post.manage blogPostId="{{ $blogPostId  }}" />
@endsection