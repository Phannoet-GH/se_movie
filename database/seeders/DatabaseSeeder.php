<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
            ]
        );

        $sampleMovies = [
            [
                'tmdb_movie_id' => 872585,
                'title' => 'Oppenheimer',
                'poster_path' => '/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
                'backdrop_path' => '/fm6KqXpk3M2HVveHwCrBSSBaO0V.jpg',
                'vote_average' => 8.1,
                'release_date' => '2023-07-19',
                'overview' => "The story of J. Robert Oppenheimer's role in the development of the atomic bomb during World War II.",
            ],
            [
                'tmdb_movie_id' => 693134,
                'title' => 'Dune: Part Two',
                'poster_path' => '/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg',
                'backdrop_path' => '/xOMo8BRK7PfcJv9JCnx7s520b29.jpg',
                'vote_average' => 8.3,
                'release_date' => '2024-02-27',
                'overview' => 'Follow the mythic journey of Paul Atreides as he unites with Chani and the Fremen.',
            ],
            [
                'tmdb_movie_id' => 157336,
                'title' => 'Interstellar',
                'poster_path' => '/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
                'backdrop_path' => '/rAiYTApp0inxdHDVoLAUPZwulqC.jpg',
                'vote_average' => 8.4,
                'release_date' => '2014-11-05',
                'overview' => 'The adventures of a group of explorers who make use of a newly discovered wormhole.',
            ],
        ];

        foreach ($sampleMovies as $movieData) {
            $user->watchlists()->firstOrCreate(
                ['tmdb_movie_id' => $movieData['tmdb_movie_id']],
                $movieData
            );
        }
    }
}

