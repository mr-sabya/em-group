<div class="py-4">
    <h2 class="mb-4">Manage Blog Posts</h2>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Blog Posts</h5>
            <a href="{{ route('blog.post.create') }}" class="btn btn-primary btn-sm" wire:navigate>
                <i class="fas fa-plus me-1"></i> Create New Post
            </a>
        </div>
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                    <input type="text" class="form-control" placeholder="Search title, excerpt or content..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                    <select wire:model.live="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="1">Published</option>
                        <option value="0">Draft</option>
                    </select>
                </div>
                <div class="col-md-12 col-lg-3 d-flex justify-content-md-end justify-content-start mt-2 mt-lg-0">
                    <div class="d-flex align-items-center gap-2">
                        <select wire:model.live="perPage" class="form-select w-auto">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-nowrap">Per Page</span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th wire:click="sortBy('title')" style="cursor: pointer;">
                                Title
                                @if ($sortField == 'title')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }} ms-1"></i>
                                @endif
                            </th>
                            <th>Image</th>
                            <th wire:click="sortBy('blog_category_id')" style="cursor: pointer;">
                                Category
                                @if ($sortField == 'blog_category_id')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }} ms-1"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('is_published')" style="cursor: pointer;">
                                Status
                                @if ($sortField == 'is_published')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }} ms-1"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('published_at')" style="cursor: pointer;">
                                Published At
                                @if ($sortField == 'published_at')
                                <i class="fas fa-{{ $sortDirection == 'asc' ? 'sort-up' : 'sort-down' }} ms-1"></i>
                                @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($blogPosts as $post)
                        <tr>
                            <td>{{ Str::limit($post->title, 40) }}</td>
                            <td>
                                @if ($post->image_path)
                                <img src="{{ Storage::url($post->image_path) }}" alt="{{ $post->title }}" class="img-thumbnail" style="width: 70px; height: 50px; object-fit: cover;">
                                @else
                                N/A
                                @endif
                            </td>
                            <td>{{ $post->category->name ?? 'Uncategorized' }}</td>
                            <td>
                                @if ($post->is_published)
                                <span class="badge bg-success">Published</span>
                                @else
                                <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td>{{ $post->published_at ? $post->published_at->format('M d, Y H:i') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('blog.post.edit', $post->id) }}" wire:navigate class="btn btn-sm btn-info" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="confirm('Are you sure you want to delete \'{{ $post->title }}\'? This action cannot be undone.') || event.stopImmediatePropagation()" wire:click="deletePost({{ $post->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No blog posts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $blogPosts->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>