<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MovieController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb
    ) {}

    public function show(int|string $id): View
    {
        $movie = $this->tmdb->getMovie($id);

        if (! $movie) {
            abort(404, 'Movie not found.');
        }

        // Find primary trailer (YouTube Trailer)
        $trailerKey = null;
        if (! empty($movie['videos']['results'])) {
            foreach ($movie['videos']['results'] as $video) {
                if (
                    isset($video['site']) && strtolower($video['site']) === 'youtube' &&
                    isset($video['type']) && (strtolower($video['type']) === 'trailer' || strtolower($video['type']) === 'teaser')
                ) {
                    $trailerKey = $video['key'];
                    break;
                }
            }
            if (! $trailerKey && ! empty($movie['videos']['results'][0]['key'])) {
                $trailerKey = $movie['videos']['results'][0]['key'];
            }
        }

        // Extract director and key crew
        $directors = [];
        $writers = [];
        if (! empty($movie['credits']['crew'])) {
            foreach ($movie['credits']['crew'] as $crew) {
                if (isset($crew['job']) && $crew['job'] === 'Director') {
                    $directors[] = $crew['name'];
                } elseif (isset($crew['job']) && in_array($crew['job'], ['Writer', 'Screenplay', 'Author'])) {
                    $writers[] = $crew['name'];
                }
            }
        }

        // Top cast members
        $cast = array_slice($movie['credits']['cast'] ?? [], 0, 12);

        // Similar movies
        $similar = $movie['similar']['results'] ?? [];

        // Reviews
        $reviews = array_slice($movie['reviews']['results'] ?? [], 0, 6);

        // Check watchlist status
        $inWatchlist = Auth::check() && Auth::user()->hasInWatchlist($id);

        $userWatchlistIds = Auth::check()
            ? Auth::user()->watchlists()->pluck('tmdb_movie_id')->toArray()
            : [];

        return view('movies.show', compact(
            'movie',
            'trailerKey',
            'directors',
            'writers',
            'cast',
            'similar',
            'reviews',
            'inWatchlist',
            'userWatchlistIds'
        ));
    }
}
