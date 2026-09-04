@props([
    'title',
    'movies' => [],
    'userWatchlistIds' => [],
    'viewAllRoute' => null,
    'badge' => null
])

@if(!empty($movies))
<section class="py-6" x-data="{
    scrollLeft() {
        this.$refs.carousel.scrollBy({ left: -600, behavior: 'smooth' });
    },
    scrollRight() {
        this.$refs.carousel.scrollBy({ left: 600, behavior: 'smooth' });
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="flex items-end justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 rounded-full bg-red-600"></div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white flex items-center gap-2.5">
                    <span>{{ $title }}</span>
                    @if($badge)
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-red-600/20 text-red-400 border border-red-500/30">
                            {{ $badge }}
                        </span>
                    @endif
                </h2>
            </div>

            <div class="flex items-center gap-2">
                @if($viewAllRoute)
                    <a href="{{ $viewAllRoute }}" class="text-xs font-semibold text-zinc-400 hover:text-red-400 mr-2 transition-colors">
                        Explore All &rarr;
                    </a>
                @endif
                
                <!-- Scroll Prev/Next Arrows -->
                <button @click="scrollLeft()"
                        class="p-2 rounded-xl bg-zinc-900/80 hover:bg-zinc-800 text-zinc-400 hover:text-white border border-zinc-800 transition-colors focus:outline-none hidden sm:flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="scrollRight()"
                        class="p-2 rounded-xl bg-zinc-900/80 hover:bg-zinc-800 text-zinc-400 hover:text-white border border-zinc-800 transition-colors focus:outline-none hidden sm:flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Carousel List -->
        <div x-ref="carousel"
             class="flex gap-4 sm:gap-6 overflow-x-auto pb-4 pt-1 no-scrollbar scroll-smooth snap-x snap-mandatory">
            @foreach($movies as $movie)
                <div class="w-44 sm:w-52 lg:w-56 flex-shrink-0 snap-start">
                    <x-movie-card :movie="$movie" :userWatchlistIds="$userWatchlistIds" />
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
