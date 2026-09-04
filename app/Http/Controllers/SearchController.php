<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb
    ) {}

    /**
     * Search and Discovery page with filters.
     */
    public function index(Request $request): View
    {
        $query = $request->query('q', '');
        $genre = $request->query('genre');
        $year = $request->query('year');
        $rating = $request->query('rating');
        $sortBy = $request->query('sort_by', 'popularity.desc');
        $page = (int) $request->query('page', 1);

        $genres = $this->tmdb->getGenres();

        $filters = [
            'with_genres' => $genre,
            'primary_release_year' => $year,
            'vote_average_gte' => $rating,
            'sort_by' => $sortBy,
        ];

        if (! empty($query)) {
            $data = $this->tmdb->search($query, $page);
        } else {
            $data = $this->tmdb->discover($filters, $page);
        }

        $movies = $data['results'] ?? [];
        $totalPages = min($data['total_pages'] ?? 1, 50); // limit to reasonable max
        $totalResults = $data['total_results'] ?? count($movies);

        $userWatchlistIds = Auth::check()
            ? Auth::user()->watchlists()->pluck('tmdb_movie_id')->toArray()
            : [];

        return view('movies.discover', compact(
            'movies',
            'genres',
            'query',
            'genre',
            'year',
            'rating',
            'sortBy',
            'page',
            'totalPages',
            'totalResults',
            'userWatchlistIds'
        ));
    }

    /**
     * Live search JSON endpoint for instant AJAX search bar.
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        if (strlen(trim($query)) < 2) {
            return response()->json(['results' => []]);
        }

        $data = $this->tmdb->search($query, 1);
        $results = array_slice($data['results'] ?? [], 0, 6);

        $formatted = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'title' => $item['title'] ?? $item['name'] ?? 'Untitled',
                'release_year' => ! empty($item['release_date']) ? substr($item['release_date'], 0, 4) : null,
                'vote_average' => $item['vote_average'] ?? 0,
                'poster_url' => $this->tmdb->getImageUrl($item['poster_path'] ?? null, 'w92'),
                'url' => route('movies.show', $item['id']),
            ];
        }, $results);

        return response()->json(['results' => $formatted]);
    }
}
