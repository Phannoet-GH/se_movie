@extends('layouts.app')

@php
    $tmdbService = app(\App\Services\TmdbService::class);
    $movieId = $movie['id'];
    $title = $movie['title'] ?? 'Movie Details';
    $posterUrl = $tmdbService->getImageUrl($movie['poster_path'] ?? null, 'w500');
    $backdropUrl = $tmdbService->getBackdropUrl($movie['backdrop_path'] ?? null, 'original');
    $rating = number_format((float)($movie['vote_average'] ?? 0), 1);
    $voteCount = number_format((int)($movie['vote_count'] ?? 0));
    $releaseDate = $movie['release_date'] ?? '';
    $year = !empty($releaseDate) ? substr($releaseDate, 0, 4) : 'N/A';
    $runtime = isset($movie['runtime']) && $movie['runtime'] > 0
        ? floor($movie['runtime'] / 60) . 'h ' . ($movie['runtime'] % 60) . 'm'
        : 'N/A';
    $genres = $movie['genres'] ?? [];
    $tagline = $movie['tagline'] ?? '';
    $overview = $movie['overview'] ?? 'No synopsis available.';
    $budget = !empty($movie['budget']) ? '$' . number_format($movie['budget']) : null;
    $revenue = !empty($movie['revenue']) ? '$' . number_format($movie['revenue']) : null;
@endphp

@section('content')
    <!-- Movie Hero Backdrop Header -->
    <div class="relative w-full min-h-[550px] lg:min-h-[650px] flex items-end bg-black -mt-20 pt-24 overflow-hidden">
        <!-- Backdrop Image -->
        <div class="absolute inset-0 z-0">
            <img src="{{ $backdropUrl }}"
                 alt="{{ $title }}"
                 class="w-full h-full object-cover object-top opacity-40 filter brightness-90">
            <div class="absolute inset-0 bg-gradient-to-t from-[#08080a] via-[#08080a]/80 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-[#08080a] via-[#08080a]/60 to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
            <div class="flex flex-col md:flex-row gap-8 lg:gap-12 items-center md:items-end">
                
                <!-- Left Poster Card -->
                <div class="w-56 sm:w-64 lg:w-72 flex-shrink-0 rounded-2xl overflow-hidden shadow-2xl shadow-black/80 border border-zinc-700/60 bg-zinc-900 group relative">
                    <img src="{{ $posterUrl }}" alt="{{ $title }}" class="w-full aspect-[2/3] object-cover">
                    
                    @if($trailerKey)
                        <button type="button"
                                @click="$dispatch('open-trailer', { key: @js($trailerKey), title: @js($title) })"
                                class="absolute inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-14 h-14 rounded-full bg-red-600 text-white flex items-center justify-center shadow-xl shadow-red-600/50 transform scale-90 group-hover:scale-100 transition-transform">
                                <svg class="w-6 h-6 fill-current ml-0.5" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>
                    @endif
                </div>

                <!-- Right Movie Details & Meta -->
                <div class="flex-1 space-y-5 text-center md:text-left">
                    
                    <!-- Badges -->
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5">
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-bold bg-amber-500/10 border border-amber-500/30 text-amber-400">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span>{{ $rating }} / 10</span>
                            <span class="text-xs text-zinc-400 font-normal">({{ $voteCount }} votes)</span>
                        </div>

                        <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-zinc-900/80 border border-zinc-800 text-zinc-300">
                            {{ $year }}
                        </span>

                        <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-zinc-900/80 border border-zinc-800 text-zinc-300">
                            {{ $runtime }}
                        </span>

                        <span class="px-2.5 py-1 rounded text-xs font-bold border border-zinc-700 text-zinc-300 uppercase">
                            {{ $movie['status'] ?? 'Released' }}
                        </span>
                    </div>

                    <!-- Title & Tagline -->
                    <div>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                            {{ $title }}
                        </h1>
                        @if($tagline)
                            <p class="text-sm sm:text-base text-zinc-400 italic font-medium mt-1">
                                &ldquo;{{ $tagline }}&rdquo;
                            </p>
                        @endif
                    </div>

                    <!-- Genre Pills -->
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                        @foreach($genres as $genre)
                            <a href="{{ route('movies.discover', ['genre' => $genre['id']]) }}"
                               class="px-3 py-1 rounded-full text-xs font-semibold bg-zinc-800/80 text-zinc-300 hover:bg-red-600 hover:text-white border border-zinc-700/60 transition-colors">
                                {{ $genre['name'] }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3.5 pt-2">
                        @if($trailerKey)
                            <button type="button"
                                    @click="$dispatch('open-trailer', { key: @js($trailerKey), title: @js($title) })"
                                    class="flex items-center gap-2 px-6 py-3.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm shadow-xl shadow-red-600/30 transition-all hover:scale-105 active:scale-95">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                </svg>
                                <span>Watch Trailer</span>
                            </button>
                        @endif

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
                                class="flex items-center gap-2 px-5 py-3.5 rounded-xl font-semibold text-sm border backdrop-blur-md transition-all hover:scale-105 active:scale-95 {{ $inWatchlist ? 'bg-red-600 text-white border-red-600 shadow-lg shadow-red-600/20' : 'bg-zinc-900/90 text-zinc-200 border-zinc-700 hover:bg-zinc-800 hover:text-white' }}">
                            <span class="watchlist-icon">
                                @if($inWatchlist)
                                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                @else
                                    <svg class="w-5 h-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                @endif
                            </span>
                            <span class="watchlist-label">{{ $inWatchlist ? 'In Watchlist' : 'Add to Watchlist' }}</span>
                        </button>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- Main Details Body Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            
            <!-- Left 2 Cols: Story, Cast, Videos, Reviews -->
            <div class="lg:col-span-2 space-y-12">
                
                <!-- Story Synopsis -->
                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-5 rounded-full bg-red-600"></div>
                        <h2 class="text-xl font-bold text-white tracking-tight">Storyline</h2>
                    </div>
                    <p class="text-zinc-300 leading-relaxed text-base font-normal">
                        {{ $overview }}
                    </p>
                </section>

                <!-- Key Crew (Director / Writers) -->
                @if(!empty($directors) || !empty($writers))
                    <section class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 rounded-2xl bg-zinc-900/60 border border-zinc-800/80">
                        @if(!empty($directors))
                            <div>
                                <span class="text-xs uppercase font-bold tracking-wider text-zinc-400">Director</span>
                                <p class="text-sm font-semibold text-zinc-100 mt-1">{{ implode(', ', $directors) }}</p>
                            </div>
                        @endif
                        @if(!empty($writers))
                            <div>
                                <span class="text-xs uppercase font-bold tracking-wider text-zinc-400">Writers & Screenplay</span>
                                <p class="text-sm font-semibold text-zinc-100 mt-1">{{ implode(', ', $writers) }}</p>
                            </div>
                        @endif
                    </section>
                @endif

                <!-- Top Billed Cast -->
                @if(!empty($cast))
                    <section class="space-y-4" x-data="{
                        scrollLeft() { this.$refs.castContainer.scrollBy({ left: -400, behavior: 'smooth' }); },
                        scrollRight() { this.$refs.castContainer.scrollBy({ left: 400, behavior: 'smooth' }); }
                    }">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-5 rounded-full bg-red-600"></div>
                                <h2 class="text-xl font-bold text-white tracking-tight">Top Billed Cast</h2>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button @click="scrollLeft()"
                                        class="p-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button @click="scrollRight()"
                                        class="p-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                        
                        <div x-ref="castContainer"
                             class="flex gap-4 overflow-x-auto pb-4 pt-1 no-scrollbar scroll-smooth snap-x snap-mandatory">
                            @foreach($cast as $actor)
                                @php
                                    $actorPhoto = !empty($actor['profile_path'])
                                        ? $tmdbService->getImageUrl($actor['profile_path'], 'w185')
                                        : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=60';
                                @endphp
                                <div class="w-36 sm:w-40 flex-shrink-0 snap-start group rounded-2xl bg-zinc-900/70 border border-zinc-800/80 hover:border-zinc-700 overflow-hidden shadow-lg transition-all duration-300 hover:-translate-y-1">
                                    <div class="relative aspect-[3/4] w-full overflow-hidden bg-zinc-800">
                                        <img src="{{ $actorPhoto }}"
                                             alt="{{ $actor['name'] }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-transparent to-transparent opacity-60"></div>
                                    </div>
                                    <div class="p-3">
                                        <h4 class="text-xs font-bold text-zinc-100 group-hover:text-red-400 transition-colors line-clamp-1" title="{{ $actor['name'] }}">
                                            {{ $actor['name'] }}
                                        </h4>
                                        <p class="text-[11px] text-zinc-400 line-clamp-1 mt-0.5" title="{{ $actor['character'] ?? 'Actor' }}">
                                            {{ $actor['character'] ?? 'Actor' }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif


                <!-- Official Trailers & Videos Section -->
                @if(!empty($movie['videos']['results']))
                    <section class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-5 rounded-full bg-red-600"></div>
                            <h2 class="text-xl font-bold text-white tracking-tight">Videos & Trailers</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach(array_slice($movie['videos']['results'], 0, 4) as $video)
                                @if(isset($video['site']) && strtolower($video['site']) === 'youtube')
                                    <div @click="$dispatch('open-trailer', { key: @js($video['key']), title: @js($video['name'] ?? $title) })"
                                         class="group relative rounded-2xl overflow-hidden bg-zinc-900 border border-zinc-800 cursor-pointer aspect-video shadow-lg">

                                        <img src="https://img.youtube.com/vi/{{ $video['key'] }}/hqdefault.jpg"
                                             alt="{{ $video['name'] }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/50 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                                            <div class="w-12 h-12 rounded-full bg-red-600/90 group-hover:bg-red-600 text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                                <svg class="w-5 h-5 fill-current ml-0.5" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                            </div>
                                        </div>
                                        <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black via-black/80 to-transparent">
                                            <p class="text-xs font-semibold text-white truncate">{{ $video['name'] }}</p>
                                            <p class="text-[10px] text-zinc-400">{{ $video['type'] ?? 'Video' }}</p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- User & Critic Reviews -->
                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-5 rounded-full bg-red-600"></div>
                        <h2 class="text-xl font-bold text-white tracking-tight">Community Reviews</h2>
                    </div>

                    @if(!empty($reviews))
                        <div class="space-y-4">
                            @foreach($reviews as $review)
                                <div class="p-5 rounded-2xl bg-zinc-900/60 border border-zinc-800/80 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-zinc-700 to-zinc-600 flex items-center justify-center text-xs font-bold text-white">
                                                {{ strtoupper(substr($review['author'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-zinc-100">{{ $review['author'] }}</h4>
                                                <span class="text-xs text-zinc-400">
                                                    {{ !empty($review['created_at']) ? date('M d, Y', strtotime($review['created_at'])) : 'Verified Reviewer' }}
                                                </span>
                                            </div>
                                        </div>
                                        @if(isset($review['author_details']['rating']))
                                            <div class="flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold">
                                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                <span>{{ $review['author_details']['rating'] }} / 10</span>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed font-normal">
                                        {{ \Illuminate\Support\Str::limit($review['content'], 350) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 rounded-2xl bg-zinc-900/40 border border-zinc-800/60 text-center text-sm text-zinc-400">
                            No written reviews yet for this movie. Be the first to add it to your watchlist!
                        </div>
                    @endif
                </section>

            </div>

            <!-- Right Col: Technical Specifications & Facts -->
            <div class="space-y-6">
                <div class="p-6 rounded-3xl bg-zinc-900/70 border border-zinc-800/80 space-y-6 shadow-xl sticky top-28">
                    <h3 class="text-base font-bold text-white border-b border-zinc-800 pb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Movie Facts & Info</span>
                    </h3>

                    <div class="space-y-4 text-xs sm:text-sm">
                        <div class="flex justify-between py-1 border-b border-zinc-800/60">
                            <span class="text-zinc-400">Status</span>
                            <span class="font-semibold text-zinc-100">{{ $movie['status'] ?? 'Released' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-zinc-800/60">
                            <span class="text-zinc-400">Original Language</span>
                            <span class="font-semibold text-zinc-100 uppercase">{{ $movie['original_language'] ?? 'EN' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-zinc-800/60">
                            <span class="text-zinc-400">Release Date</span>
                            <span class="font-semibold text-zinc-100">{{ $releaseDate ? date('F j, Y', strtotime($releaseDate)) : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-zinc-800/60">
                            <span class="text-zinc-400">Runtime</span>
                            <span class="font-semibold text-zinc-100">{{ $runtime }}</span>
                        </div>
                        @if($budget)
                            <div class="flex justify-between py-1 border-b border-zinc-800/60">
                                <span class="text-zinc-400">Budget</span>
                                <span class="font-semibold text-zinc-100">{{ $budget }}</span>
                            </div>
                        @endif
                        @if($revenue)
                            <div class="flex justify-between py-1 border-b border-zinc-800/60">
                                <span class="text-zinc-400">Box Office Revenue</span>
                                <span class="font-semibold text-emerald-400">{{ $revenue }}</span>
                            </div>
                        @endif
                        @if(!empty($movie['production_companies']))
                            <div class="pt-2">
                                <span class="text-zinc-400 block mb-2">Production Companies</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($movie['production_companies'] as $comp)
                                        <span class="px-2 py-1 rounded bg-zinc-800 text-[11px] text-zinc-300">{{ $comp['name'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Similar & Recommended Movies Carousel -->
        @if(!empty($similar))
            <div class="mt-16 pt-12 border-t border-zinc-800/80">
                <x-movie-carousel 
                    title="More Like This" 
                    :movies="$similar" 
                    :userWatchlistIds="$userWatchlistIds" 
                    badge="Recommended" 
                />
            </div>
        @endif
    </div>
@endsection
