import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global helper for Watchlist AJAX toggle
window.toggleWatchlist = async function(buttonEl, movieId = null, movieData = null) {
    movieId = movieId || buttonEl.dataset.movieId;
    if (!movieData) {
        movieData = {
            title: buttonEl.dataset.title || '',
            poster_path: buttonEl.dataset.posterPath || '',
            backdrop_path: buttonEl.dataset.backdropPath || '',
            vote_average: buttonEl.dataset.voteAverage || 0,
            release_date: buttonEl.dataset.releaseDate || '',
            overview: buttonEl.dataset.overview || ''
        };
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const response = await fetch(`/watchlist/toggle/${movieId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(movieData)
        });


        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }

        const data = await response.json();

        if (data.status === 'added') {
            buttonEl.dataset.inWatchlist = 'true';
            buttonEl.classList.add('bg-red-600', 'text-white', 'border-red-600');
            buttonEl.classList.remove('bg-zinc-800/80', 'text-zinc-300', 'border-zinc-700');
            const icon = buttonEl.querySelector('.watchlist-icon');
            if (icon) {
                icon.innerHTML = `<svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>`;
            }
            const label = buttonEl.querySelector('.watchlist-label');
            if (label) label.textContent = 'In Watchlist';
        } else if (data.status === 'removed') {
            buttonEl.dataset.inWatchlist = 'false';
            buttonEl.classList.remove('bg-red-600', 'text-white', 'border-red-600');
            buttonEl.classList.add('bg-zinc-800/80', 'text-zinc-300', 'border-zinc-700');
            const icon = buttonEl.querySelector('.watchlist-icon');
            if (icon) {
                icon.innerHTML = `<svg class="w-5 h-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>`;
            }
            const label = buttonEl.querySelector('.watchlist-label');
            if (label) label.textContent = 'Add to Watchlist';

            // If on watchlist page, optionally fade out card
            const cardEl = buttonEl.closest('.watchlist-movie-card');
            if (cardEl && window.location.pathname.includes('/watchlist')) {
                cardEl.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                cardEl.style.opacity = '0';
                cardEl.style.transform = 'scale(0.95)';
                setTimeout(() => cardEl.remove(), 300);
            }
        }

        // Show toast notification
        if (window.dispatchEvent) {
            window.dispatchEvent(new CustomEvent('toast-message', {
                detail: { message: data.message || 'Watchlist updated', type: 'success' }
            }));
        }
    } catch (error) {
        console.error('Error toggling watchlist:', error);
    }
};

Alpine.start();
