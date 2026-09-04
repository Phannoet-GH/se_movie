@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Page Title & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-600/20 text-red-500 flex items-center justify-center border border-red-500/30">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    My Watchlist
                </h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-600 text-white">
                    {{ $watchlistItems->total() }}
                </span>
            </div>
            <p class="text-sm text-zinc-400 mt-1">
                All your saved movies ready to watch anytime.
            </p>
        </div>

        <!-- Search & Sort Filter within Watchlist -->
        <form method="GET" action="{{ route('watchlist.index') }}" class="flex flex-wrap items-center gap-2.5">
            <div class="relative">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Filter your watchlist..."
                       class="bg-zinc-900/90 border border-zinc-800 focus:border-red-500 rounded-xl pl-9 pr-4 py-2 text-xs text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                <svg class="w-3.5 h-3.5 text-zinc-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select name="sort" onchange="this.form.submit()" class="bg-zinc-900/90 border border-zinc-800 focus:border-red-500 rounded-xl px-3 py-2 text-xs text-zinc-300 focus:outline-none focus:ring-1 focus:ring-red-500">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Recently Added</option>
                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rating</option>
                <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title (A-Z)</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest Added</option>
            </select>
        </form>
    </div>

    <!-- Watchlist Grid -->
    @if($watchlistItems->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
            @foreach($watchlistItems as $item)
                @php
                    $movieData = [
                        'id' => $item->tmdb_movie_id,
                        'tmdb_movie_id' => $item->tmdb_movie_id,
                        'title' => $item->title,
                        'poster_path' => $item->poster_path,
                        'backdrop_path' => $item->backdrop_path,
                        'vote_average' => $item->vote_average,
                        'release_date' => $item->release_date,
                        'overview' => $item->overview,
                    ];
                @endphp
                <x-movie-card :movie="$movieData" :userWatchlistIds="$userWatchlistIds" :showWatchlistRemove="true" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12 flex justify-center">
            {{ $watchlistItems->links() }}
        </div>
    @else
        <!-- Empty Watchlist State -->
        <div class="py-24 text-center space-y-5 max-w-md mx-auto">
            <div class="w-20 h-20 rounded-3xl bg-zinc-900/80 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-500 shadow-xl">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-white tracking-tight">Your Watchlist is Empty</h3>
            <p class="text-sm text-zinc-400 leading-relaxed">
                Save movies you want to check out later by clicking the bookmark or "Add to Watchlist" button on any movie poster or details page.
            </p>
            <div class="pt-2">
                <a href="{{ route('movies.discover') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm shadow-xl shadow-red-600/30 transition-all hover:scale-105">
                    <span>Explore Movies Now</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
