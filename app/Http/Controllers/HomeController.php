<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb
    ) {}

    public function index(Request $request): View
    {
        $trendingData = $this->tmdb->getTrending('week');
        $trending = $trendingData['results'] ?? [];

        $popularData = $this->tmdb->getPopular();
        $popular = $popularData['results'] ?? [];

        $topRatedData = $this->tmdb->getTopRated();
        $topRated = $topRatedData['results'] ?? [];

        $upcomingData = $this->tmdb->getUpcoming();
        $upcoming = $upcomingData['results'] ?? [];

        $actionData = $this->tmdb->getByGenre(28);
        $action = $actionData['results'] ?? [];

        $scifiData = $this->tmdb->getByGenre(878);
        $scifi = $scifiData['results'] ?? [];

        $animationData = $this->tmdb->getByGenre(16);
        $animation = $animationData['results'] ?? [];

        // Hero featured movie (first trending or popular movie with detailed trailer)
        $heroMovie = null;
        if (! empty($trending)) {
            $heroMovieId = $trending[0]['id'];
            $heroMovie = $this->tmdb->getMovie($heroMovieId) ?? $trending[0];
        }

        // Get user watchlist movie IDs for instant UI state
        $userWatchlistIds = Auth::check()
            ? Auth::user()->watchlists()->pluck('tmdb_movie_id')->toArray()
            : [];

        return view('home', compact(
            'heroMovie',
            'trending',
            'popular',
            'topRated',
            'upcoming',
            'action',
            'scifi',
            'animation',
            'userWatchlistIds'
        ));
    }
}
