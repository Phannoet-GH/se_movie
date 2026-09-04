@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Page Header & Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                @if(!empty($query))
                    Search results for &ldquo;<span class="text-red-500">{{ $query }}</span>&rdquo;
                @else
                    Discover Movies
                @endif
            </h1>
            <p class="text-sm text-zinc-400 mt-1">
                Explore thousands of movies, filter by genres, release year, and ratings.
            </p>
        </div>

        <div class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 self-start md:self-auto">
            Showing {{ count($movies) }} of {{ number_format($totalResults) }} movies
        </div>
    </div>

    <!-- Filter Control Panel -->
    <div class="p-6 rounded-3xl bg-zinc-900/70 border border-zinc-800/80 mb-10 shadow-xl backdrop-blur-md">
        <form method="GET" action="{{ route('movies.discover') }}" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Search Text Field -->
                <div>
                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Search Keyword</label>
                    <div class="relative">
                        <input type="text"
                               name="q"
                               value="{{ $query }}"
                               placeholder="Keywords..."
                               class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-red-500 rounded-xl px-4 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-red-500 transition-all">
                        @if($query)
                            <a href="{{ route('movies.discover') }}" class="absolute right-3 top-3 text-xs text-zinc-500 hover:text-zinc-300">Clear</a>
                        @endif
                    </div>
                </div>

                <!-- Genre Selector -->
                <div>
                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Genre</label>
                    <select name="genre" class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-red-500 rounded-xl px-4 py-2.5 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-500 transition-all">
                        <option value="">All Genres</option>
                        @foreach($genres as $g)
                            <option value="{{ $g['id'] }}" {{ (string)$genre === (string)$g['id'] ? 'selected' : '' }}>
                                {{ $g['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Release Year -->
                <div>
                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Release Year</label>
                    <select name="year" class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-red-500 rounded-xl px-4 py-2.5 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-500 transition-all">
                        <option value="">Any Year</option>
                        @for($y = (int)date('Y') + 1; $y >= 1970; $y--)
                            <option value="{{ $y }}" {{ (string)$year === (string)$y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Sort By</label>
                    <select name="sort_by" class="w-full bg-zinc-950/80 border border-zinc-800 focus:border-red-500 rounded-xl px-4 py-2.5 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-500 transition-all">
                        <option value="popularity.desc" {{ $sortBy === 'popularity.desc' ? 'selected' : '' }}>Most Popular</option>
                        <option value="vote_average.desc" {{ $sortBy === 'vote_average.desc' ? 'selected' : '' }}>Highest Rated</option>
                        <option value="primary_release_date.desc" {{ $sortBy === 'primary_release_date.desc' ? 'selected' : '' }}>Newest Releases</option>
                    </select>
                </div>

            </div>

            <!-- Bottom Filter Actions & Rating Pill -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-zinc-800/60">
                <!-- Minimum Rating Filter -->
                <div class="flex items-center gap-2 text-xs font-medium text-zinc-400 w-full sm:w-auto">
                    <span>Min Rating:</span>
                    <div class="flex items-center gap-1.5">
                        @foreach([0 => 'Any', 6 => '6+', 7 => '7+', 8 => '8+'] as $rVal => $rLabel)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $rVal > 0 ? $rVal : '' }}" {{ (string)$rating === (string)($rVal > 0 ? $rVal : '') ? 'checked' : '' }} class="sr-only peer">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-zinc-950 border border-zinc-800 text-zinc-400 peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-500 transition-all">
                                    {{ $rLabel }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    @if(!empty($query) || !empty($genre) || !empty($year) || !empty($rating))
                        <a href="{{ route('movies.discover') }}" class="px-4 py-2 text-xs font-semibold text-zinc-400 hover:text-white transition-colors">
                            Reset Filters
                        </a>
                    @endif
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-lg shadow-red-600/30 transition-all hover:scale-105 active:scale-95">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Quick Genre Pills Filter Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 no-scrollbar">
        <a href="{{ route('movies.discover', array_merge(request()->except('genre', 'page'))) }}"
           class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ empty($genre) ? 'bg-white text-zinc-950 shadow-md font-bold' : 'bg-zinc-900 text-zinc-400 hover:text-white hover:bg-zinc-800 border border-zinc-800' }}">
            All Genres
        </a>
        @foreach($genres as $g)
            <a href="{{ route('movies.discover', array_merge(request()->except('page'), ['genre' => $g['id']])) }}"
               class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ (string)$genre === (string)$g['id'] ? 'bg-red-600 text-white shadow-md shadow-red-600/20 font-bold border border-red-500' : 'bg-zinc-900 text-zinc-400 hover:text-white hover:bg-zinc-800 border border-zinc-800' }}">
                {{ $g['name'] }}
            </a>
        @endforeach
    </div>

    <!-- Movies Result Grid -->
    @if(!empty($movies))
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
            @foreach($movies as $movie)
                <x-movie-card :movie="$movie" :userWatchlistIds="$userWatchlistIds" />
            @endforeach
        </div>

        <!-- Pagination Controls -->
        @if($totalPages > 1)
            <div class="flex items-center justify-center gap-3 mt-14">
                @if($page > 1)
                    <a href="{{ route('movies.discover', array_merge(request()->query(), ['page' => $page - 1])) }}"
                       class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-xs font-semibold text-zinc-300 hover:text-white hover:bg-zinc-800 transition-colors">
                        &larr; Previous Page
                    </a>
                @endif

                <span class="text-xs font-semibold text-zinc-400 px-3 py-2 bg-zinc-950 rounded-xl border border-zinc-800/80">
                    Page {{ $page }} of {{ $totalPages }}
                </span>

                @if($page < $totalPages)
                    <a href="{{ route('movies.discover', array_merge(request()->query(), ['page' => $page + 1])) }}"
                       class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-xs font-semibold text-zinc-300 hover:text-white hover:bg-zinc-800 transition-colors">
                        Next Page &rarr;
                    </a>
                @endif
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="py-20 text-center space-y-4 max-w-md mx-auto">
            <div class="w-16 h-16 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center mx-auto text-zinc-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white">No Movies Found</h3>
            <p class="text-xs text-zinc-400 leading-relaxed">
                We couldn't find any movies matching your current filters. Try changing keywords or resetting your search filters.
            </p>
            <div class="pt-2">
                <a href="{{ route('movies.discover') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-500 transition-all shadow-lg shadow-red-600/30">
                    Reset All Filters
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
