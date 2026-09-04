<nav x-data="{
        mobileMenuOpen: false,
        searchQuery: '',
        searchResults: [],
        searchLoading: false,
        searchFocused: false,
        async fetchSuggestions() {
            if (this.searchQuery.trim().length < 2) {
                this.searchResults = [];
                return;
            }
            this.searchLoading = true;
            try {
                const res = await fetch(`/search/suggest?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await res.json();
                this.searchResults = data.results || [];
            } catch (err) {
                console.error(err);
            } finally {
                this.searchLoading = false;
            }
        }
     }"
     class="sticky top-0 z-40 w-full backdrop-blur-xl bg-[#08080a]/85 border-b border-zinc-800/80 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-4">
            
            <!-- Logo & Brand -->
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-600 to-rose-500 flex items-center justify-center shadow-lg shadow-red-600/30 group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-5 h-5 text-white fill-current" viewBox="0 0 24 24">
                            <path d="M4 6.75A2.75 2.75 0 016.75 4h10.5A2.75 2.75 0 0120 6.75v10.5A2.75 2.75 0 0117.25 20H6.75A2.75 2.75 0 014 17.25V6.75zM9.75 8.5a.75.75 0 00-1.15-.64l-4.5 3a.75.75 0 000 1.28l4.5 3A.75.75 0 009.75 14.5V8.5zm4.5 7a.75.75 0 001.15.64l4.5-3a.75.75 0 000-1.28l-4.5-3a.75.75 0 00-1.15.64v6z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-white via-zinc-200 to-zinc-400 bg-clip-text text-transparent group-hover:text-white transition-colors">
                        Cine<span class="text-red-500">Pulse</span>
                    </span>
                </a>

                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('home') }}"
                       class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'text-white bg-zinc-800/80 font-semibold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/40' }}">
                        Home
                    </a>
                    <a href="{{ route('movies.discover') }}"
                       class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('movies.discover') ? 'text-white bg-zinc-800/80 font-semibold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/40' }}">
                        Discover Movies
                    </a>
                    @auth
                        <a href="{{ route('watchlist.index') }}"
                           class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ request()->routeIs('watchlist.index') ? 'text-white bg-zinc-800/80 font-semibold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/40' }}">
                            <span>Watchlist</span>
                            <span class="px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-600 text-white">
                                {{ Auth::user()->watchlists()->count() }}
                            </span>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Instant Live Search Bar -->
            <div class="flex-1 max-w-md relative" @click.away="searchFocused = false">
                <form action="{{ route('movies.discover') }}" method="GET" class="relative">
                    <div class="relative flex items-center">
                        <input type="text"
                               name="q"
                               x-model="searchQuery"
                               @input.debounce.300ms="fetchSuggestions()"
                               @focus="searchFocused = true"
                               placeholder="Search movies, actors, directors..."
                               value="{{ request('q') }}"
                               autocomplete="off"
                               class="w-full bg-zinc-900/90 border border-zinc-700/70 focus:border-red-500/80 text-sm rounded-full pl-11 pr-10 py-2.5 text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all shadow-inner">
                        
                        <!-- Search Icon -->
                        <div class="absolute left-3.5 text-zinc-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <!-- Clear Button -->
                        <button type="button"
                                x-show="searchQuery.length > 0"
                                @click="searchQuery = ''; searchResults = []"
                                class="absolute right-3 text-zinc-500 hover:text-zinc-300"
                                style="display: none;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Autocomplete Dropdown List -->
                <div x-show="searchFocused && (searchResults.length > 0 || searchLoading)"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute top-full mt-2 left-0 right-0 bg-zinc-900/95 border border-zinc-800 rounded-2xl shadow-2xl overflow-hidden z-50 backdrop-blur-xl"
                     style="display: none;">
                    
                    <!-- Loading skeleton -->
                    <div x-show="searchLoading" class="p-4 text-center text-sm text-zinc-400 flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-red-500" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Searching movies...</span>
                    </div>

                    <!-- Results List -->
                    <div x-show="!searchLoading && searchResults.length > 0" class="divide-y divide-zinc-800/60 max-h-96 overflow-y-auto">
                        <template x-for="item in searchResults" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-3 p-3 hover:bg-zinc-800/80 transition-colors group">
                                <img :src="item.poster_url" :alt="item.title" class="w-11 h-16 object-cover rounded-md flex-shrink-0 bg-zinc-800">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-zinc-100 group-hover:text-red-400 truncate" x-text="item.title"></h4>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-zinc-400">
                                        <span x-text="item.release_year || 'N/A'"></span>
                                        <span class="text-zinc-600">•</span>
                                        <span class="flex items-center gap-1 text-amber-400">
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            <span x-text="Number(item.vote_average).toFixed(1)"></span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </template>
                        <div class="p-2.5 bg-zinc-950/60 text-center">
                            <a :href="'/discover?q=' + encodeURIComponent(searchQuery)" class="text-xs font-semibold text-red-400 hover:text-red-300">
                                View all search results &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Actions & User Account -->
            <div class="flex items-center gap-3">
                @auth
                    <!-- User Dropdown Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                @click.away="open = false"
                                class="flex items-center gap-2.5 p-1.5 rounded-full hover:bg-zinc-800/80 transition-colors focus:outline-none">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-red-600 to-amber-600 flex items-center justify-center font-bold text-white shadow-md text-sm ring-2 ring-zinc-700">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden lg:block text-sm font-medium text-zinc-200">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-zinc-400 hidden lg:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl py-2 z-50 divide-y divide-zinc-800"
                             style="display: none;">
                            <div class="px-4 py-2.5">
                                <p class="text-xs text-zinc-400 font-medium">Signed in as</p>
                                <p class="text-sm font-semibold text-zinc-100 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('watchlist.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800 hover:text-white transition-colors">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                    <span>My Watchlist ({{ Auth::user()->watchlists()->count() }})</span>
                                </a>
                                <a href="{{ route('movies.discover') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800 hover:text-white transition-colors">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span>Explore Genres</span>
                                </a>
                            </div>
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-sm text-red-400 hover:bg-zinc-800 hover:text-red-300 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        <span>Sign Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Guest Auth Buttons -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-zinc-300 hover:text-white hover:bg-zinc-800/60 rounded-xl transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-500 rounded-xl shadow-lg shadow-red-600/30 transition-all hover:scale-105">
                            Get Started
                        </a>
                    </div>
                @endauth

                <!-- Mobile Hamburger Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 focus:outline-none">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenuOpen"
             x-transition
             class="md:hidden border-t border-zinc-800 py-4 space-y-2"
             style="display: none;">
            <a href="{{ route('home') }}" class="block px-4 py-2.5 rounded-xl text-base font-medium {{ request()->routeIs('home') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white' }}">
                Home
            </a>
            <a href="{{ route('movies.discover') }}" class="block px-4 py-2.5 rounded-xl text-base font-medium {{ request()->routeIs('movies.discover') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white' }}">
                Discover Movies
            </a>
            @auth
                <a href="{{ route('watchlist.index') }}" class="block px-4 py-2.5 rounded-xl text-base font-medium flex items-center justify-between {{ request()->routeIs('watchlist.index') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white' }}">
                    <span>My Watchlist</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-600 text-white">
                        {{ Auth::user()->watchlists()->count() }}
                    </span>
                </a>
            @else
                <div class="pt-3 border-t border-zinc-800 flex flex-col gap-2">
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl text-sm font-semibold bg-zinc-800 text-white">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-xl text-sm font-semibold bg-red-600 text-white">
                        Create Account
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>
