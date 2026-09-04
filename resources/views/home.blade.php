@extends('layouts.app')

@section('content')
    <!-- API Key Notice Banner if not configured -->
    @if(!app(\App\Services\TmdbService::class)->isConfigured())
        <div class="bg-gradient-to-r from-red-950/80 via-zinc-900 to-zinc-950 border-b border-red-900/40 py-2.5 px-4">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 text-red-300">
                    <span class="px-2 py-0.5 rounded-full bg-red-600/30 text-red-400 font-bold uppercase tracking-wider text-[10px]">Demo Mode</span>
                    <span>Running with high-fidelity sample movie data & YouTube trailers.</span>
                </div>
                <div class="text-zinc-400">
                    Add <code class="px-1.5 py-0.5 rounded bg-zinc-800 text-red-400 font-mono">TMDB_TOKEN=your_token</code> to <code class="text-zinc-300">.env</code> for live TMDB API updates.
                </div>
            </div>
        </div>
    @endif

    <!-- Hero Spotlight Section -->
    @if($heroMovie)
        <x-hero-banner :movie="$heroMovie" :userWatchlistIds="$userWatchlistIds" />
    @endif

    <!-- Content Sections -->
    <div class="space-y-6 -mt-8 relative z-20">
        
        <!-- Trending This Week -->
        <x-movie-carousel 
            title="Trending This Week" 
            :movies="$trending" 
            :userWatchlistIds="$userWatchlistIds" 
            badge="Top 10" 
            :viewAllRoute="route('movies.discover', ['sort_by' => 'popularity.desc'])" 
        />

        <!-- Popular Now -->
        <x-movie-carousel 
            title="Popular on CinePulse" 
            :movies="$popular" 
            :userWatchlistIds="$userWatchlistIds" 
            :viewAllRoute="route('movies.discover', ['sort_by' => 'popularity.desc'])" 
        />

        <!-- Top Rated Movies -->
        <x-movie-carousel 
            title="Critically Acclaimed & Top Rated" 
            :movies="$topRated" 
            :userWatchlistIds="$userWatchlistIds" 
            badge="Must Watch" 
            :viewAllRoute="route('movies.discover', ['sort_by' => 'vote_average.desc', 'rating' => 8])" 
        />

        <!-- Genre Spotlight: Action & Thrillers -->
        <x-movie-carousel 
            title="High-Octane Action & Adventure" 
            :movies="$action" 
            :userWatchlistIds="$userWatchlistIds" 
            :viewAllRoute="route('movies.discover', ['genre' => 28])" 
        />

        <!-- Genre Spotlight: Sci-Fi & Fantasy -->
        <x-movie-carousel 
            title="Sci-Fi & Cosmic Journeys" 
            :movies="$scifi" 
            :userWatchlistIds="$userWatchlistIds" 
            :viewAllRoute="route('movies.discover', ['genre' => 878])" 
        />

        <!-- Upcoming Anticipated Movies -->
        <x-movie-carousel 
            title="Coming Soon to Theaters" 
            :movies="$upcoming" 
            :userWatchlistIds="$userWatchlistIds" 
            badge="Upcoming" 
            :viewAllRoute="route('movies.discover', ['sort_by' => 'primary_release_date.desc'])" 
        />

        <!-- Genre Spotlight: Animation & Family -->
        <x-movie-carousel 
            title="Animation & Masterpieces" 
            :movies="$animation" 
            :userWatchlistIds="$userWatchlistIds" 
            :viewAllRoute="route('movies.discover', ['genre' => 16])" 
        />

        <!-- Call to Action Banner -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-red-950/60 via-zinc-900/90 to-zinc-900 border border-zinc-800 p-8 sm:p-12">
                <div class="max-w-xl space-y-4 relative z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-600/20 text-red-400 text-xs font-bold border border-red-500/30">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                        <span>Personal Watchlist</span>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Never lose track of movies you want to watch.
                    </h3>
                    <p class="text-sm text-zinc-400 leading-relaxed">
                        Create a free CinePulse account to bookmark favorites, manage your private watchlist, and get recommendations tailored to your taste.
                    </p>
                    <div class="pt-2">
                        @auth
                            <a href="{{ route('watchlist.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm shadow-xl shadow-red-600/30 transition-all hover:scale-105">
                                <span>Go to My Watchlist</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm shadow-xl shadow-red-600/30 transition-all hover:scale-105">
                                <span>Create Free Account</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
