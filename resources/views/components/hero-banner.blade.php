@props(['movie', 'userWatchlistIds' => []])

@php
    $tmdbService = app(\App\Services\TmdbService::class);
    $backdropUrl = $tmdbService->getBackdropUrl($movie['backdrop_path'] ?? null, 'original');
    $posterUrl = $tmdbService->getImageUrl($movie['poster_path'] ?? null, 'w500');
    $movieId = $movie['id'] ?? 0;
    $title = $movie['title'] ?? 'Featured Movie';
    $overview = $movie['overview'] ?? '';
    $rating = number_format($movie['vote_average'] ?? 0, 1);
    $year = !empty($movie['release_date']) ? substr($movie['release_date'], 0, 4) : '';
    $runtime = isset($movie['runtime']) ? floor($movie['runtime'] / 60) . 'h ' . ($movie['runtime'] % 60) . 'm' : null;
    $genres = $movie['genres'] ?? [];
    
    // Find trailer key
    $trailerKey = $movie['trailer_key'] ?? null;
    if (!$trailerKey && !empty($movie['videos']['results'])) {
        foreach ($movie['videos']['results'] as $v) {
            if (isset($v['site']) && strtolower($v['site']) === 'youtube') {
                $trailerKey = $v['key'];
                break;
            }
        }
    }
    if (!$trailerKey) {
        $trailerKey = 'uYPbbksJxIg'; // Oppenheimer default fallback
    }

    $inWatchlist = in_array((int)$movieId, $userWatchlistIds);
@endphp

<div class="relative w-full min-h-[600px] lg:min-h-[750px] flex items-center bg-black overflow-hidden -mt-20 pt-20">
    <!-- Full-Bleed Background Backdrop -->
    <div class="absolute inset-0 z-0">
        <img src="{{ $backdropUrl }}"
             alt="{{ $title }}"
             class="w-full h-full object-cover object-center opacity-60 scale-105 transform animate-fade-in filter brightness-75">
        
        <!-- Multi-Layer Cinematic Gradients -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#08080a] via-[#08080a]/60 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#08080a] via-[#08080a]/80 to-transparent"></div>
        <div class="absolute inset-0 bg-radial-at-c from-transparent via-[#08080a]/40 to-[#08080a]/90"></div>
    </div>

    <!-- Content Container -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <div class="max-w-2xl space-y-6">
            
            <!-- Badges & Release Info -->
            <div class="flex flex-wrap items-center gap-3">
                <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-red-600/90 text-white uppercase tracking-wider shadow-lg shadow-red-600/20">
                    Featured Spotlight
                </span>

                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-zinc-900/80 border border-zinc-700/60 text-amber-400 backdrop-blur-sm">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span>{{ $rating }}</span>
                </div>

                @if($year)
                    <span class="text-xs font-medium text-zinc-300 bg-zinc-900/60 px-2.5 py-1 rounded-md border border-zinc-800">
                        {{ $year }}
                    </span>
                @endif

                @if($runtime)
                    <span class="text-xs font-medium text-zinc-300 bg-zinc-900/60 px-2.5 py-1 rounded-md border border-zinc-800">
                        {{ $runtime }}
                    </span>
                @endif

                <span class="px-2 py-0.5 rounded text-[11px] font-bold border border-zinc-600 text-zinc-300">
                    HD / 4K
                </span>
            </div>

            <!-- Title -->
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1] drop-shadow-md">
                {{ $title }}
            </h1>

            <!-- Genres -->
            @if(!empty($genres))
                <div class="flex flex-wrap gap-2 text-xs font-medium text-zinc-400">
                    @foreach(array_slice($genres, 0, 3) as $g)
                        <span class="hover:text-red-400 transition-colors">
                            {{ $g['name'] }}@if(!$loop->last)<span class="text-zinc-600 ml-2">•</span>@endif
                        </span>
                    @endforeach
                </div>
            @endif

            <!-- Overview -->
            <p class="text-sm sm:text-base text-zinc-300 leading-relaxed line-clamp-3 font-normal max-w-xl drop-shadow">
                {{ $overview }}
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3.5 pt-2">
                
                <!-- Watch Trailer Button -->
                <button type="button"
                        @click="$dispatch('open-trailer', { key: @js($trailerKey), title: @js($title) })"
                        class="flex items-center gap-2 px-6 py-3.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm shadow-xl shadow-red-600/30 transition-all hover:scale-105 active:scale-95 group">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                    </svg>
                    <span>Watch Trailer</span>
                </button>

                <!-- Watchlist Toggle Button -->
                <button type="button"
                        data-in-watchlist="{{ $inWatchlist ? 'true' : 'false' }}"
                        data-movie-id="{{ (int)$movieId }}"
                        data-title="{{ $title }}"
                        data-poster-path="{{ $movie['poster_path'] ?? '' }}"
                        data-backdrop-path="{{ $movie['backdrop_path'] ?? '' }}"
                        data-vote-average="{{ $movie['vote_average'] ?? 0 }}"
                        data-release-date="{{ $movie['release_date'] ?? '' }}"
                        data-overview="{{ $overview }}"
                        onclick="toggleWatchlist(this)"
                        class="flex items-center gap-2 px-5 py-3.5 rounded-xl font-semibold text-sm border backdrop-blur-md transition-all hover:scale-105 active:scale-95 {{ $inWatchlist ? 'bg-red-600 text-white border-red-600 shadow-lg shadow-red-600/20' : 'bg-zinc-800/80 text-zinc-200 border-zinc-700 hover:bg-zinc-700/80 hover:text-white' }}">
                    <span class="watchlist-icon">
                        @if($inWatchlist)
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        @else
                            <svg class="w-5 h-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        @endif
                    </span>
                    <span class="watchlist-label">{{ $inWatchlist ? 'In Watchlist' : 'Add to Watchlist' }}</span>
                </button>


                <!-- More Details -->
                <a href="{{ route('movies.show', $movieId) }}"
                   class="flex items-center gap-2 px-5 py-3.5 rounded-xl bg-zinc-900/80 hover:bg-zinc-800 text-zinc-300 hover:text-white font-medium text-sm border border-zinc-700/60 backdrop-blur-md transition-all hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Details</span>
                </a>
            </div>

        </div>
    </div>
</div>
