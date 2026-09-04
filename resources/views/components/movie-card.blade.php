@props(['movie', 'userWatchlistIds' => [], 'showWatchlistRemove' => false])

@php
    $tmdbService = app(\App\Services\TmdbService::class);
    $movieId = $movie['id'] ?? $movie['tmdb_movie_id'] ?? 0;
    $title = $movie['title'] ?? $movie['name'] ?? 'Untitled Movie';
    $posterPath = $movie['poster_path'] ?? null;
    $posterUrl = $tmdbService->getImageUrl($posterPath, 'w500');
    $backdropPath = $movie['backdrop_path'] ?? null;
    $rating = number_format((float)($movie['vote_average'] ?? 0), 1);
    $releaseDate = $movie['release_date'] ?? $movie['first_air_date'] ?? '';
    $year = !empty($releaseDate) ? substr($releaseDate, 0, 4) : 'N/A';
    $overview = $movie['overview'] ?? '';

    $inWatchlist = in_array((int)$movieId, $userWatchlistIds);
    $trailerKey = $movie['trailer_key'] ?? 'uYPbbksJxIg';
@endphp

<div class="group relative flex flex-col rounded-2xl bg-zinc-900/60 border border-zinc-800/80 hover:border-zinc-700/90 overflow-hidden shadow-lg transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-red-950/20 watchlist-movie-card">
    
    <!-- Poster Container -->
    <div class="relative aspect-[2/3] w-full overflow-hidden bg-zinc-800">
        <img src="{{ $posterUrl }}"
             alt="{{ $title }}"
             loading="lazy"
             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">

        <!-- Rating Pill Top-Right -->
        <div class="absolute top-2.5 right-2.5 flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-black/75 backdrop-blur-md border border-zinc-700/60 text-amber-400 shadow-md">
            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span>{{ $rating }}</span>
        </div>

        <!-- Quick Watchlist Toggle Top-Left -->
        <button type="button"
                title="{{ $inWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist' }}"
                data-in-watchlist="{{ $inWatchlist ? 'true' : 'false' }}"
                data-movie-id="{{ (int)$movieId }}"
                data-title="{{ $title }}"
                data-poster-path="{{ $posterPath }}"
                data-backdrop-path="{{ $backdropPath }}"
                data-vote-average="{{ $movie['vote_average'] ?? 0 }}"
                data-release-date="{{ $releaseDate }}"
                data-overview="{{ $overview }}"
                onclick="event.stopPropagation(); toggleWatchlist(this)"
                class="absolute top-2.5 left-2.5 p-2 rounded-xl backdrop-blur-md border transition-all z-20 hover:scale-110 active:scale-95 {{ $inWatchlist ? 'bg-red-600 text-white border-red-500 shadow-lg shadow-red-600/30' : 'bg-black/75 text-zinc-300 border-zinc-700/60 hover:text-white hover:bg-zinc-800' }}">
            <span class="watchlist-icon flex items-center justify-center">
                @if($inWatchlist)
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                @else
                    <svg class="w-4 h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                @endif
            </span>
        </button>


        <!-- Hover Overlay with Play Button -->
        <a href="{{ route('movies.show', $movieId) }}" class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
            <div class="w-12 h-12 rounded-full bg-red-600 text-white flex items-center justify-center shadow-xl shadow-red-600/40 transform scale-75 group-hover:scale-100 transition-transform duration-300">
                <svg class="w-5 h-5 fill-current ml-0.5" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
            </div>
        </a>
    </div>

    <!-- Metadata Details -->
    <div class="p-4 flex flex-col flex-1 justify-between gap-2">
        <div>
            <a href="{{ route('movies.show', $movieId) }}" class="block">
                <h3 class="font-bold text-sm text-zinc-100 group-hover:text-red-400 transition-colors line-clamp-1" title="{{ $title }}">
                    {{ $title }}
                </h3>
            </a>
            <div class="flex items-center justify-between text-xs text-zinc-400 mt-1">
                <span>{{ $year }}</span>
                <span class="px-1.5 py-0.5 rounded bg-zinc-800/80 text-[10px] font-semibold text-zinc-400 border border-zinc-700/50">Movie</span>
            </div>
        </div>
        
        <p class="text-xs text-zinc-400 line-clamp-2 leading-relaxed">
            {{ $overview ?: 'No description available for this movie.' }}
        </p>
    </div>
</div>
