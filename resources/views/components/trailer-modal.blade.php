<div x-data="{
        open: false,
        videoKey: '',
        title: 'Movie Trailer',
        openModal(key, movieTitle) {
            this.videoKey = key;
            this.title = movieTitle || 'Movie Trailer';
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        closeModal() {
            this.open = false;
            this.videoKey = '';
            document.body.classList.remove('overflow-hidden');
        }
     }"
     @open-trailer.window="openModal($event.detail.key, $event.detail.title)"
     @keydown.escape.window="closeModal()"
     x-show="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 lg:p-8"
     style="display: none;"
>
    <!-- Dark Backdrop Overlay -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"
         class="fixed inset-0 bg-black/85 backdrop-blur-md"></div>

    <!-- Modal Content Box -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="relative w-full max-w-4xl bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl overflow-hidden z-10">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-800 bg-zinc-950/60">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600 animate-pulse"></span>
                <h3 class="text-base font-bold text-zinc-100 truncate" x-text="title"></h3>
            </div>
            <button @click="closeModal()"
                    class="p-1.5 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- 16:9 Video Player Container -->
        <div class="relative w-full bg-black" style="padding-top: 56.25%;">
            <template x-if="open && videoKey">
                <iframe class="absolute inset-0 w-full h-full border-0"
                        :src="'https://www.youtube-nocookie.com/embed/' + videoKey + '?autoplay=1&rel=0&modestbranding=1'"
                        title="Trailer Player"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                </iframe>
            </template>
        </div>
    </div>
</div>
