<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected ?string $token;

    protected ?string $apiKey;

    protected string $baseUrl;

    protected string $imageBaseUrl;

    public function __construct()
    {
        $this->token = config('services.tmdb.token');
        $this->apiKey = config('services.tmdb.api_key');
        $this->baseUrl = rtrim(config('services.tmdb.base_url', 'https://api.themoviedb.org/3'), '/');
        $this->imageBaseUrl = rtrim(config('services.tmdb.image_base_url', 'https://image.tmdb.org/t/p/'), '/');
    }

    /**
     * Check if TMDB API is properly configured with a token or API key.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->token) || ! empty($this->apiKey);
    }

    /**
     * Get image URL with fallback to placeholder.
     */
    public function getImageUrl(?string $path, string $size = 'w500'): string
    {
        if (empty($path)) {
            return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500&auto=format&fit=crop&q=60';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return "{$this->imageBaseUrl}/{$size}/".ltrim($path, '/');
    }

    /**
     * Get backdrop image URL.
     */
    public function getBackdropUrl(?string $path, string $size = 'original'): string
    {
        if (empty($path)) {
            return 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=1920&auto=format&fit=crop&q=80';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return "{$this->imageBaseUrl}/{$size}/".ltrim($path, '/');
    }

    /**
     * Helper to make HTTP requests to TMDB or fallback to mock data.
     */
    protected function get(string $endpoint, array $params = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $request = Http::timeout(6)->acceptJson();

            if (! empty($this->token)) {
                $request = $request->withToken($this->token);
            } elseif (! empty($this->apiKey)) {
                $params['api_key'] = $this->apiKey;
            }

            $response = $request->get("{$this->baseUrl}/{$endpoint}", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("TMDB API Error [{$response->status()}] for {$endpoint}: ".$response->body());

            return null;
        } catch (\Exception $e) {
            Log::warning("TMDB API Exception for {$endpoint}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Get trending movies.
     */
    public function getTrending(string $timeWindow = 'day', int $page = 1): array
    {
        $cacheKey = "tmdb_trending_{$timeWindow}_page_{$page}";

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($timeWindow, $page) {
            $data = $this->get("trending/movie/{$timeWindow}", ['page' => $page]);
            if ($data && ! empty($data['results'])) {
                return $data;
            }

            return $this->getMockTrending();
        });
    }

    /**
     * Get popular movies.
     */
    public function getPopular(int $page = 1): array
    {
        $cacheKey = "tmdb_popular_page_{$page}";

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($page) {
            $data = $this->get('movie/popular', ['page' => $page]);
            if ($data && ! empty($data['results'])) {
                return $data;
            }

            return $this->getMockPopular();
        });
    }

    /**
     * Get top rated movies.
     */
    public function getTopRated(int $page = 1): array
    {
        $cacheKey = "tmdb_top_rated_page_{$page}";

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($page) {
            $data = $this->get('movie/top_rated', ['page' => $page]);
            if ($data && ! empty($data['results'])) {
                return $data;
            }

            return $this->getMockTopRated();
        });
    }

    /**
     * Get upcoming movies.
     */
    public function getUpcoming(int $page = 1): array
    {
        $cacheKey = "tmdb_upcoming_page_{$page}";

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($page) {
            $data = $this->get('movie/upcoming', ['page' => $page]);
            if ($data && ! empty($data['results'])) {
                return $data;
            }

            return $this->getMockUpcoming();
        });
    }

    /**
     * Get movie details with appended videos, credits, similar, and reviews.
     */
    public function getMovie(int|string $id): ?array
    {
        $cacheKey = "tmdb_movie_{$id}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($id) {
            $data = $this->get("movie/{$id}", [
                'append_to_response' => 'videos,credits,similar,reviews',
            ]);

            if ($data && ! empty($data['title'])) {
                return $data;
            }

            return $this->getMockMovieById($id);
        });
    }

    /**
     * Search movies by title.
     */
    public function search(string $query, int $page = 1): array
    {
        if (trim($query) === '') {
            return ['results' => [], 'total_results' => 0, 'page' => 1, 'total_pages' => 0];
        }

        $data = $this->get('search/movie', [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ]);

        if ($data && isset($data['results'])) {
            return $data;
        }

        // Mock search fallback
        return $this->searchMockMovies($query);
    }

    /**
     * Discover movies with filters.
     */
    public function discover(array $filters = [], int $page = 1): array
    {
        $params = ['page' => $page, 'include_adult' => false];

        if (! empty($filters['with_genres'])) {
            $params['with_genres'] = $filters['with_genres'];
        }
        if (! empty($filters['primary_release_year'])) {
            $params['primary_release_year'] = $filters['primary_release_year'];
        }
        if (! empty($filters['vote_average_gte'])) {
            $params['vote_average.gte'] = $filters['vote_average_gte'];
        }
        if (! empty($filters['sort_by'])) {
            $params['sort_by'] = $filters['sort_by'];
        } else {
            $params['sort_by'] = 'popularity.desc';
        }

        $data = $this->get('discover/movie', $params);

        if ($data && isset($data['results'])) {
            return $data;
        }

        return $this->filterMockMovies($filters);
    }

    /**
     * Get genre list.
     */
    public function getGenres(): array
    {
        return Cache::remember('tmdb_genres', now()->addDays(7), function () {
            $data = $this->get('genre/movie/list');
            if ($data && ! empty($data['genres'])) {
                return $data['genres'];
            }

            return [
                ['id' => 28, 'name' => 'Action'],
                ['id' => 12, 'name' => 'Adventure'],
                ['id' => 16, 'name' => 'Animation'],
                ['id' => 35, 'name' => 'Comedy'],
                ['id' => 80, 'name' => 'Crime'],
                ['id' => 99, 'name' => 'Documentary'],
                ['id' => 18, 'name' => 'Drama'],
                ['id' => 10751, 'name' => 'Family'],
                ['id' => 14, 'name' => 'Fantasy'],
                ['id' => 36, 'name' => 'History'],
                ['id' => 27, 'name' => 'Horror'],
                ['id' => 10402, 'name' => 'Music'],
                ['id' => 9648, 'name' => 'Mystery'],
                ['id' => 10749, 'name' => 'Romance'],
                ['id' => 878, 'name' => 'Science Fiction'],
                ['id' => 53, 'name' => 'Thriller'],
                ['id' => 10752, 'name' => 'War'],
                ['id' => 37, 'name' => 'Western'],
            ];
        });
    }

    /**
     * Get movies by genre ID.
     */
    public function getByGenre(int $genreId, int $page = 1): array
    {
        return $this->discover(['with_genres' => $genreId], $page);
    }

    // ==========================================
    // MOCK DATA GENERATOR FOR ZERO-CONFIG DEMO
    // ==========================================

    protected function getBaseMockMovies(): array
    {
        return [
            [
                'id' => 872585,
                'title' => 'Oppenheimer',
                'original_title' => 'Oppenheimer',
                'overview' => "The story of J. Robert Oppenheimer's role in the development of the atomic bomb during World War II, exploring the moral and scientific complexities of the Manhattan Project.",
                'poster_path' => '/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
                'backdrop_path' => '/fm6KqXpk3M2HVveHwCrBSSBaO0V.jpg',
                'release_date' => '2023-07-19',
                'vote_average' => 8.1,
                'vote_count' => 8750,
                'popularity' => 345.2,
                'genre_ids' => [18, 36],
                'genres' => [['id' => 18, 'name' => 'Drama'], ['id' => 36, 'name' => 'History']],
                'runtime' => 180,
                'tagline' => 'The world forever changes.',
                'status' => 'Released',
                'budget' => 100000000,
                'revenue' => 957000000,
                'trailer_key' => 'uYPbbksJxIg',
            ],
            [
                'id' => 693134,
                'title' => 'Dune: Part Two',
                'original_title' => 'Dune: Part Two',
                'overview' => 'Follow the mythic journey of Paul Atreides as he unites with Chani and the Fremen while on a warpath of revenge against the conspirators who destroyed his family.',
                'poster_path' => '/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg',
                'backdrop_path' => '/xOMo8BRK7PfcJv9JCnx7s520b29.jpg',
                'release_date' => '2024-02-27',
                'vote_average' => 8.3,
                'vote_count' => 5200,
                'popularity' => 450.8,
                'genre_ids' => [878, 12],
                'genres' => [['id' => 878, 'name' => 'Science Fiction'], ['id' => 12, 'name' => 'Adventure']],
                'runtime' => 166,
                'tagline' => 'Long live the fighters.',
                'status' => 'Released',
                'budget' => 190000000,
                'revenue' => 714000000,
                'trailer_key' => 'Way9Dexny3w',
            ],
            [
                'id' => 157336,
                'title' => 'Interstellar',
                'original_title' => 'Interstellar',
                'overview' => 'The adventures of a group of explorers who make use of a newly discovered wormhole to surpass the limitations on human space travel and conquer the vast distances involved in an interstellar voyage.',
                'poster_path' => '/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
                'backdrop_path' => '/rAiYTApp0inxdHDVoLAUPZwulqC.jpg',
                'release_date' => '2014-11-05',
                'vote_average' => 8.4,
                'vote_count' => 34500,
                'popularity' => 280.4,
                'genre_ids' => [12, 18, 878],
                'genres' => [['id' => 12, 'name' => 'Adventure'], ['id' => 18, 'name' => 'Drama'], ['id' => 878, 'name' => 'Science Fiction']],
                'runtime' => 169,
                'tagline' => 'Mankind was born on Earth. It was never meant to die here.',
                'status' => 'Released',
                'budget' => 165000000,
                'revenue' => 701729206,
                'trailer_key' => 'zSWdZVtXT7E',
            ],
            [
                'id' => 569094,
                'title' => 'Spider-Man: Across the Spider-Verse',
                'original_title' => 'Spider-Man: Across the Spider-Verse',
                'overview' => 'After reuniting with Gwen Stacy, Brooklyn’s full-time, friendly neighborhood Spider-Man is catapulted across the Multiverse, where he encounters the Spider Society.',
                'poster_path' => '/8Vt6mWEReuy4Of61Lnj5Xj704m8.jpg',
                'backdrop_path' => '/4HodYYKEIsGOdinkGi2Ucz6X9i0.jpg',
                'release_date' => '2023-05-31',
                'vote_average' => 8.4,
                'vote_count' => 6400,
                'popularity' => 320.1,
                'genre_ids' => [16, 28, 12, 878],
                'genres' => [['id' => 16, 'name' => 'Animation'], ['id' => 28, 'name' => 'Action'], ['id' => 12, 'name' => 'Adventure']],
                'runtime' => 140,
                'tagline' => 'It’s how you wear the mask that matters.',
                'status' => 'Released',
                'budget' => 100000000,
                'revenue' => 690516673,
                'trailer_key' => 'cqGjhVJWtEg',
            ],
            [
                'id' => 27205,
                'title' => 'Inception',
                'original_title' => 'Inception',
                'overview' => 'Cobb, a skilled thief who commits corporate espionage by infiltrating the subconscious of his targets is offered a chance to regain his old life as payment for a task considered to be impossible: "inception".',
                'poster_path' => '/oYuLEt3zVCKq57qu2F8dT7NIa6f.jpg',
                'backdrop_path' => '/8ZTVqvKDQ8emSGUEMjsS4yHAwrp.jpg',
                'release_date' => '2010-07-15',
                'vote_average' => 8.4,
                'vote_count' => 35600,
                'popularity' => 210.9,
                'genre_ids' => [28, 878, 12],
                'genres' => [['id' => 28, 'name' => 'Action'], ['id' => 878, 'name' => 'Science Fiction'], ['id' => 12, 'name' => 'Adventure']],
                'runtime' => 148,
                'tagline' => 'Your mind is the scene of the crime.',
                'status' => 'Released',
                'budget' => 160000000,
                'revenue' => 836836967,
                'trailer_key' => 'YoHD9XEInc0',
            ],
            [
                'id' => 155,
                'title' => 'The Dark Knight',
                'original_title' => 'The Dark Knight',
                'overview' => 'Batman raises the stakes in his war on crime. With the help of Lt. Jim Gordon and District Attorney Harvey Dent, Batman sets out to dismantle the remaining criminal organizations that plague the streets.',
                'poster_path' => '/qJ2tW6WMUDux911r6m7haRef0WH.jpg',
                'backdrop_path' => '/nMKdUUepR0i5zn0y1T4CsSB5chy.jpg',
                'release_date' => '2008-07-16',
                'vote_average' => 8.5,
                'vote_count' => 32000,
                'popularity' => 195.4,
                'genre_ids' => [18, 28, 80, 53],
                'genres' => [['id' => 18, 'name' => 'Drama'], ['id' => 28, 'name' => 'Action'], ['id' => 80, 'name' => 'Crime']],
                'runtime' => 152,
                'tagline' => 'Welcome to a world without rules.',
                'status' => 'Released',
                'budget' => 185000000,
                'revenue' => 1004558444,
                'trailer_key' => 'EXeTwQWrcwY',
            ],
            [
                'id' => 129,
                'title' => 'Spirited Away',
                'original_title' => '千と千尋の神隠し',
                'overview' => 'A young girl, Chihiro, becomes trapped in a strange new world of spirits. When her parents undergo a mysterious transformation, she must call upon the courage she never knew she had to free her family.',
                'poster_path' => '/39wmItIWsg5sZMyRUHLkWBcuVCM.jpg',
                'backdrop_path' => '/Ab8mkHmkYADjU7wQiOkia9BzGvS.jpg',
                'release_date' => '2001-07-20',
                'vote_average' => 8.5,
                'vote_count' => 16200,
                'popularity' => 150.3,
                'genre_ids' => [16, 10751, 14],
                'genres' => [['id' => 16, 'name' => 'Animation'], ['id' => 10751, 'name' => 'Family'], ['id' => 14, 'name' => 'Fantasy']],
                'runtime' => 125,
                'tagline' => 'Tunnel into a magical world.',
                'status' => 'Released',
                'budget' => 19000000,
                'revenue' => 395580000,
                'trailer_key' => 'ByXuk9QqQkk',
            ],
            [
                'id' => 545611,
                'title' => 'Everything Everywhere All at Once',
                'original_title' => 'Everything Everywhere All at Once',
                'overview' => 'An aging Chinese immigrant is swept up in an insane adventure, where she alone can save what is important to her by connecting with the lives she could have led in other universes.',
                'poster_path' => '/w3LxiVYPqS9exhxujviz2ZtnHRmg.jpg',
                'backdrop_path' => '/70aVSo3G7nh9452WE7k699CqYp7.jpg',
                'release_date' => '2022-03-24',
                'vote_average' => 7.8,
                'vote_count' => 6100,
                'popularity' => 180.5,
                'genre_ids' => [28, 12, 878],
                'genres' => [['id' => 28, 'name' => 'Action'], ['id' => 12, 'name' => 'Adventure'], ['id' => 878, 'name' => 'Science Fiction']],
                'runtime' => 139,
                'tagline' => 'The universe is so much bigger than you realize.',
                'status' => 'Released',
                'budget' => 25000000,
                'revenue' => 143400000,
                'trailer_key' => 'wxN1T1uxMAC',
            ],
            [
                'id' => 76341,
                'title' => 'Mad Max: Fury Road',
                'original_title' => 'Mad Max: Fury Road',
                'overview' => 'An apocalyptic story set in the furthest reaches of our planet, in a stark desert landscape where humanity is broken, and most everyone is crazed fighting for the necessities of life.',
                'poster_path' => '/hA2ple9q4qnwxp3hKVNhroipsir.jpg',
                'backdrop_path' => '/nlCHUWjY9XWbuEUQauCBgnY8ymF.jpg',
                'release_date' => '2015-05-13',
                'vote_average' => 7.6,
                'vote_count' => 21900,
                'popularity' => 175.2,
                'genre_ids' => [28, 12, 878],
                'genres' => [['id' => 28, 'name' => 'Action'], ['id' => 12, 'name' => 'Adventure'], ['id' => 878, 'name' => 'Science Fiction']],
                'runtime' => 120,
                'tagline' => 'What a Lovely Day.',
                'status' => 'Released',
                'budget' => 150000000,
                'revenue' => 378858340,
                'trailer_key' => 'hEJnMQGkowski',
            ],
            [
                'id' => 361743,
                'title' => 'Top Gun: Maverick',
                'original_title' => 'Top Gun: Maverick',
                'overview' => 'After thirty years, Maverick is still pushing the envelope as a top naval aviator, but must confront ghosts of his past when he leads TOP GUN\'s elite graduates on a mission that demands the ultimate sacrifice from those chosen to fly it.',
                'poster_path' => '/62HCnUTziyWcpDaBO2i1DX17ljH.jpg',
                'backdrop_path' => '/odJ4hx6g6vBt4lBWKFD1tI8WS4x.jpg',
                'release_date' => '2022-05-24',
                'vote_average' => 8.2,
                'vote_count' => 8900,
                'popularity' => 210.0,
                'genre_ids' => [28, 18],
                'genres' => [['id' => 28, 'name' => 'Action'], ['id' => 18, 'name' => 'Drama']],
                'runtime' => 131,
                'tagline' => 'Feel the need... The need for speed.',
                'status' => 'Released',
                'budget' => 170000000,
                'revenue' => 1495696292,
                'trailer_key' => 'qSqVVqua420',
            ],
            [
                'id' => 76600,
                'title' => 'Avatar: The Way of Water',
                'original_title' => 'Avatar: The Way of Water',
                'overview' => 'Set more than a decade after the events of the first film, learn the story of the Sully family (Jake, Neytiri, and their kids), the trouble that follows them, the lengths they go to keep each other safe, the battles they fight to stay alive, and the tragedies they endure.',
                'poster_path' => '/t6HIqrRAclMCA60NsSmeqe9RmNV.jpg',
                'backdrop_path' => '/8rpDcsfLJypbO6vREc0547VKqEv.jpg',
                'release_date' => '2022-12-14',
                'vote_average' => 7.6,
                'vote_count' => 11000,
                'popularity' => 270.8,
                'genre_ids' => [878, 12, 28],
                'genres' => [['id' => 878, 'name' => 'Science Fiction'], ['id' => 12, 'name' => 'Adventure'], ['id' => 28, 'name' => 'Action']],
                'runtime' => 192,
                'tagline' => 'Return to Pandora.',
                'status' => 'Released',
                'budget' => 350000000,
                'revenue' => 2320250281,
                'trailer_key' => 'd9MyW72ELq0',
            ],
            [
                'id' => 19995,
                'title' => 'Avatar',
                'original_title' => 'Avatar',
                'overview' => 'In the 22nd century, a paraplegic Marine is dispatched to the moon Pandora on a unique mission, but becomes torn between following orders and protecting an alien civilization.',
                'poster_path' => '/kyeqWdyUXW608qlYkRqosgbbJyK.jpg',
                'backdrop_path' => '/vL5LR6WdxWPjCmvzy425ZtU71h5.jpg',
                'release_date' => '2009-12-15',
                'vote_average' => 7.6,
                'vote_count' => 31000,
                'popularity' => 165.7,
                'genre_ids' => [28, 12, 14, 878],
                'genres' => [['id' => 28, 'name' => 'Action'], ['id' => 12, 'name' => 'Adventure'], ['id' => 14, 'name' => 'Fantasy'], ['id' => 878, 'name' => 'Science Fiction']],
                'runtime' => 162,
                'tagline' => 'Enter the World of Pandora.',
                'status' => 'Released',
                'budget' => 237000000,
                'revenue' => 2923706026,
                'trailer_key' => '5PSNL1qE6VY',
            ],
        ];
    }

    protected function getMockTrending(): array
    {
        $movies = $this->getBaseMockMovies();

        return [
            'page' => 1,
            'results' => $movies,
            'total_pages' => 1,
            'total_results' => count($movies),
        ];
    }

    protected function getMockPopular(): array
    {
        $movies = $this->getBaseMockMovies();
        usort($movies, fn ($a, $b) => $b['popularity'] <=> $a['popularity']);

        return [
            'page' => 1,
            'results' => $movies,
            'total_pages' => 1,
            'total_results' => count($movies),
        ];
    }

    protected function getMockTopRated(): array
    {
        $movies = $this->getBaseMockMovies();
        usort($movies, fn ($a, $b) => $b['vote_average'] <=> $a['vote_average']);

        return [
            'page' => 1,
            'results' => $movies,
            'total_pages' => 1,
            'total_results' => count($movies),
        ];
    }

    protected function getMockUpcoming(): array
    {
        $movies = array_reverse($this->getBaseMockMovies());

        return [
            'page' => 1,
            'results' => $movies,
            'total_pages' => 1,
            'total_results' => count($movies),
        ];
    }

    protected function getMockMovieById(int|string $id): ?array
    {
        $movies = $this->getBaseMockMovies();
        $found = null;

        foreach ($movies as $movie) {
            if ((string) $movie['id'] === (string) $id) {
                $found = $movie;
                break;
            }
        }

        if (! $found) {
            $found = $movies[0]; // default fallback
            $found['id'] = (int) $id;
        }

        // Attach movie-specific realistic credits, videos, similar, reviews
        $movieCastMap = [
            872585 => [ // Oppenheimer
                ['id' => 101, 'name' => 'Cillian Murphy', 'character' => 'J. Robert Oppenheimer', 'profile_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&auto=format&fit=crop&q=80'],
                ['id' => 102, 'name' => 'Emily Blunt', 'character' => 'Katherine "Kitty" Oppenheimer', 'profile_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80'],
                ['id' => 103, 'name' => 'Matt Damon', 'character' => 'Lt. Gen. Leslie Groves', 'profile_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop&q=80'],
                ['id' => 104, 'name' => 'Robert Downey Jr.', 'character' => 'Lewis Strauss', 'profile_path' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=400&auto=format&fit=crop&q=80'],
                ['id' => 105, 'name' => 'Florence Pugh', 'character' => 'Jean Tatlock', 'profile_path' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&auto=format&fit=crop&q=80'],
                ['id' => 106, 'name' => 'Josh Hartnett', 'character' => 'Ernest Lawrence', 'profile_path' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&auto=format&fit=crop&q=80'],
                ['id' => 107, 'name' => 'Casey Affleck', 'character' => 'Boris Pash', 'profile_path' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=400&auto=format&fit=crop&q=80'],
                ['id' => 108, 'name' => 'Rami Malek', 'character' => 'David Hill', 'profile_path' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=400&auto=format&fit=crop&q=80'],
            ],
            693134 => [ // Dune: Part Two
                ['id' => 201, 'name' => 'Timothée Chalamet', 'character' => 'Paul Atreides', 'profile_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&auto=format&fit=crop&q=80'],
                ['id' => 202, 'name' => 'Zendaya', 'character' => 'Chani', 'profile_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80'],
                ['id' => 203, 'name' => 'Rebecca Ferguson', 'character' => 'Lady Jessica', 'profile_path' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&auto=format&fit=crop&q=80'],
                ['id' => 204, 'name' => 'Javier Bardem', 'character' => 'Stilgar', 'profile_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop&q=80'],
                ['id' => 205, 'name' => 'Austin Butler', 'character' => 'Feyd-Rautha', 'profile_path' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&auto=format&fit=crop&q=80'],
                ['id' => 206, 'name' => 'Florence Pugh', 'character' => 'Princess Irulan', 'profile_path' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&auto=format&fit=crop&q=80'],
            ],
            157336 => [ // Interstellar
                ['id' => 301, 'name' => 'Matthew McConaughey', 'character' => 'Joseph Cooper', 'profile_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&auto=format&fit=crop&q=80'],
                ['id' => 302, 'name' => 'Anne Hathaway', 'character' => 'Dr. Amelia Brand', 'profile_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80'],
                ['id' => 303, 'name' => 'Jessica Chastain', 'character' => 'Murphy "Murph" Cooper', 'profile_path' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&auto=format&fit=crop&q=80'],
                ['id' => 304, 'name' => 'Michael Caine', 'character' => 'Professor John Brand', 'profile_path' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&auto=format&fit=crop&q=80'],
                ['id' => 305, 'name' => 'Matt Damon', 'character' => 'Dr. Mann', 'profile_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop&q=80'],
            ],
            155 => [ // The Dark Knight
                ['id' => 401, 'name' => 'Christian Bale', 'character' => 'Bruce Wayne / Batman', 'profile_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&auto=format&fit=crop&q=80'],
                ['id' => 402, 'name' => 'Heath Ledger', 'character' => 'The Joker', 'profile_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop&q=80'],
                ['id' => 403, 'name' => 'Aaron Eckhart', 'character' => 'Harvey Dent / Two-Face', 'profile_path' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&auto=format&fit=crop&q=80'],
                ['id' => 404, 'name' => 'Michael Caine', 'character' => 'Alfred Pennyworth', 'profile_path' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&auto=format&fit=crop&q=80'],
                ['id' => 405, 'name' => 'Gary Oldman', 'character' => 'Lt. James Gordon', 'profile_path' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=400&auto=format&fit=crop&q=80'],
                ['id' => 406, 'name' => 'Morgan Freeman', 'character' => 'Lucius Fox', 'profile_path' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=400&auto=format&fit=crop&q=80'],
            ],
            27205 => [ // Inception
                ['id' => 501, 'name' => 'Leonardo DiCaprio', 'character' => 'Dom Cobb', 'profile_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&auto=format&fit=crop&q=80'],
                ['id' => 502, 'name' => 'Joseph Gordon-Levitt', 'character' => 'Arthur', 'profile_path' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&auto=format&fit=crop&q=80'],
                ['id' => 503, 'name' => 'Elliot Page', 'character' => 'Ariadne', 'profile_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80'],
                ['id' => 504, 'name' => 'Tom Hardy', 'character' => 'Eames', 'profile_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop&q=80'],
                ['id' => 505, 'name' => 'Ken Watanabe', 'character' => 'Mr. Saito', 'profile_path' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=400&auto=format&fit=crop&q=80'],
                ['id' => 506, 'name' => 'Cillian Murphy', 'character' => 'Robert Michael Fischer', 'profile_path' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=400&auto=format&fit=crop&q=80'],
            ],
        ];

        $castList = $movieCastMap[(int) $id] ?? $movieCastMap[872585];

        $found['credits'] = [
            'cast' => $castList,
            'crew' => [
                [
                    'id' => 201,
                    'name' => 'Christopher Nolan',
                    'job' => 'Director',
                    'department' => 'Directing',
                ],
                [
                    'id' => 202,
                    'name' => 'Ludwig Göransson',
                    'job' => 'Original Music Composer',
                    'department' => 'Sound',
                ],
                [
                    'id' => 203,
                    'name' => 'Hoyte van Hoytema',
                    'job' => 'Director of Photography',
                    'department' => 'Camera',
                ],
            ],
        ];

        $found['videos'] = [
            'results' => [
                [
                    'id' => 'vid1',
                    'key' => $found['trailer_key'] ?? 'uYPbbksJxIg',
                    'name' => 'Official Main Trailer',
                    'site' => 'YouTube',
                    'type' => 'Trailer',
                    'official' => true,
                ],
                [
                    'id' => 'vid2',
                    'key' => 'bK6ldnjGE6Y',
                    'name' => 'Teaser Trailer',
                    'site' => 'YouTube',
                    'type' => 'Teaser',
                    'official' => true,
                ],
            ],
        ];

        $found['reviews'] = [
            'results' => [
                [
                    'id' => 'rev1',
                    'author' => 'CinemaEnthusiast',
                    'author_details' => ['rating' => 9, 'avatar_path' => null],
                    'content' => 'A towering masterpiece of modern filmmaking. The tension, sound design, and acting performances are unmatched. A must-watch cinematic achievement.',
                    'created_at' => '2023-08-01T12:00:00.000Z',
                ],
                [
                    'id' => 'rev2',
                    'author' => 'FilmBuff_99',
                    'author_details' => ['rating' => 10, 'avatar_path' => null],
                    'content' => 'Brilliant pacing and phenomenal visuals throughout. Easily one of the finest films of this generation.',
                    'created_at' => '2023-08-05T15:30:00.000Z',
                ],
            ],
        ];

        $found['similar'] = [
            'results' => array_slice($movies, 1, 6),
        ];

        return $found;
    }

    protected function searchMockMovies(string $query): array
    {
        $all = $this->getBaseMockMovies();
        $queryLower = strtolower($query);

        $filtered = array_values(array_filter($all, function ($movie) use ($queryLower) {
            return str_contains(strtolower($movie['title']), $queryLower)
                || str_contains(strtolower($movie['overview']), $queryLower);
        }));

        return [
            'page' => 1,
            'results' => $filtered,
            'total_pages' => 1,
            'total_results' => count($filtered),
        ];
    }

    protected function filterMockMovies(array $filters): array
    {
        $all = $this->getBaseMockMovies();

        $filtered = array_values(array_filter($all, function ($movie) use ($filters) {
            if (! empty($filters['with_genres'])) {
                $genreId = (int) $filters['with_genres'];
                if (! in_array($genreId, $movie['genre_ids'] ?? [])) {
                    return false;
                }
            }

            if (! empty($filters['primary_release_year'])) {
                $year = (int) $filters['primary_release_year'];
                $movieYear = (int) substr($movie['release_date'] ?? '0', 0, 4);
                if ($movieYear !== $year) {
                    return false;
                }
            }

            if (! empty($filters['vote_average_gte'])) {
                $minRating = (float) $filters['vote_average_gte'];
                if (($movie['vote_average'] ?? 0) < $minRating) {
                    return false;
                }
            }

            return true;
        }));

        return [
            'page' => 1,
            'results' => $filtered,
            'total_pages' => 1,
            'total_results' => count($filtered),
        ];
    }
}
