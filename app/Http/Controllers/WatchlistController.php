<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb
    ) {}

    /**
     * Show user's personal watchlist.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = $user->watchlists()->latest();

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('sort')) {
            $sort = $request->query('sort');
            if ($sort === 'rating') {
                $query->reorder('vote_average', 'desc');
            } elseif ($sort === 'title') {
                $query->reorder('title', 'asc');
            } elseif ($sort === 'oldest') {
                $query->reorder('created_at', 'asc');
            }
        }

        $watchlistItems = $query->paginate(18)->withQueryString();
        $userWatchlistIds = $user->watchlists()->pluck('tmdb_movie_id')->toArray();

        return view('watchlist.index', compact('watchlistItems', 'userWatchlistIds'));
    }

    /**
     * Toggle a movie in/out of the user's watchlist.
     */
    public function toggle(Request $request, int|string $movieId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login')->with('info', 'Please sign in to manage your watchlist.');
        }

        $existing = $user->watchlists()->where('tmdb_movie_id', (int) $movieId)->first();

        if ($existing) {
            $existing->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'removed',
                    'message' => 'Removed from your watchlist.',
                    'in_watchlist' => false,
                ]);
            }

            return back()->with('success', 'Movie removed from your watchlist.');
        }

        // Add movie to watchlist - retrieve data from request or TMDB service
        $title = $request->input('title');
        $posterPath = $request->input('poster_path');
        $backdropPath = $request->input('backdrop_path');
        $voteAverage = $request->input('vote_average');
        $releaseDate = $request->input('release_date');
        $overview = $request->input('overview');

        if (empty($title)) {
            $movie = $this->tmdb->getMovie($movieId);
            if ($movie) {
                $title = $movie['title'] ?? 'Untitled Movie';
                $posterPath = $movie['poster_path'] ?? null;
                $backdropPath = $movie['backdrop_path'] ?? null;
                $voteAverage = $movie['vote_average'] ?? 0.0;
                $releaseDate = $movie['release_date'] ?? null;
                $overview = $movie['overview'] ?? null;
            }
        }

        $user->watchlists()->create([
            'tmdb_movie_id' => (int) $movieId,
            'title' => $title ?? 'Untitled Movie',
            'poster_path' => $posterPath,
            'backdrop_path' => $backdropPath,
            'vote_average' => (float) ($voteAverage ?? 0.0),
            'release_date' => $releaseDate,
            'overview' => $overview,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'added',
                'message' => 'Added to your watchlist!',
                'in_watchlist' => true,
            ]);
        }

        return back()->with('success', 'Movie added to your watchlist!');
    }

    /**
     * Remove an item from watchlist by database ID.
     */
    public function destroy(Watchlist $watchlist): RedirectResponse
    {
        if ($watchlist->user_id !== Auth::id()) {
            abort(403);
        }

        $watchlist->delete();

        return back()->with('success', 'Removed from watchlist.');
    }
}
