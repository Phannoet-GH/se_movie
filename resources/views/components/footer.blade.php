<footer class="bg-[#050507] border-t border-zinc-800/80 text-zinc-400 py-12 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <!-- Col 1: Brand -->
            <div class="space-y-4 md:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center text-white">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M4 6.75A2.75 2.75 0 016.75 4h10.5A2.75 2.75 0 0120 6.75v10.5A2.75 2.75 0 0117.25 20H6.75A2.75 2.75 0 014 17.25V6.75zM9.75 8.5a.75.75 0 00-1.15-.64l-4.5 3a.75.75 0 000 1.28l4.5 3A.75.75 0 009.75 14.5V8.5zm4.5 7a.75.75 0 001.15.64l4.5-3a.75.75 0 000-1.28l-4.5-3a.75.75 0 00-1.15.64v6z" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-white">
                        Cine<span class="text-red-500">Pulse</span>
                    </span>
                </a>
                <p class="text-xs text-zinc-400 leading-relaxed">
                    Your premier movie discovery and streaming web application. Browse trending titles, explore genres, check ratings, watch trailers, and build your personalized watchlist.
                </p>
            </div>

            <!-- Col 2: Navigation -->
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-200">Explore</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Trending Movies</a></li>
                    <li><a href="{{ route('movies.discover') }}" class="hover:text-white transition-colors">Discover & Filter</a></li>
                    <li><a href="{{ route('movies.discover', ['genre' => 28]) }}" class="hover:text-white transition-colors">Action Movies</a></li>
                    <li><a href="{{ route('movies.discover', ['genre' => 878]) }}" class="hover:text-white transition-colors">Sci-Fi Hits</a></li>
                </ul>
            </div>

            <!-- Col 3: Community & Auth -->
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-200">Account</h4>
                <ul class="space-y-2 text-sm">
                    @auth
                        <li><a href="{{ route('watchlist.index') }}" class="hover:text-white transition-colors">My Watchlist</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="hover:text-red-400 transition-colors">Sign Out</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Sign In</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Register Account</a></li>
                    @endauth
                </ul>
            </div>

            <!-- Col 4: Attribution -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-200">Data & Attribution</h4>
                <p class="text-xs text-zinc-400 leading-relaxed">
                    Movie data, posters, and cast information are provided by <a href="https://www.themoviedb.org" target="_blank" rel="noopener noreferrer" class="text-emerald-400 hover:underline">The Movie Database (TMDB)</a>.
                </p>
                <div class="flex items-center gap-2 text-xs font-medium text-zinc-400">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Powered by Laravel 11 & Tailwind CSS</span>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-400">
            <p>&copy; {{ date('Y') }} CinePulse. Built with Laravel, Tailwind CSS & TMDB API.</p>
            <div class="flex items-center space-x-6">
                <span class="hover:text-zinc-300">Privacy Policy</span>
                <span class="hover:text-zinc-300">Terms of Service</span>
                <span class="hover:text-zinc-300">API Documentation</span>
            </div>
        </div>
    </div>
</footer>
