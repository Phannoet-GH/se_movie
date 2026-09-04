<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CinePulse');
        $response->assertSee('Trending This Week');
        $response->assertSee('Popular on CinePulse');
    }

    public function test_movie_details_page_renders_with_details(): void
    {
        $response = $this->get('/movies/872585');

        $response->assertStatus(200);
        $response->assertSee('Oppenheimer');
        $response->assertSee('Cillian Murphy');
        $response->assertSee('Storyline');
    }

    public function test_discover_page_renders_with_genre_filters(): void
    {
        $response = $this->get('/discover?genre=28');

        $response->assertStatus(200);
        $response->assertSee('Discover Movies');
        $response->assertSee('Action');
    }

    public function test_search_suggest_api_returns_json_results(): void
    {
        $response = $this->getJson('/search/suggest?q=Oppenheimer');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'results' => [
                '*' => ['id', 'title', 'release_year', 'vote_average', 'poster_url', 'url'],
            ],
        ]);
        $response->assertSee('Oppenheimer');
    }

    public function test_user_registration_and_authentication(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_guest_watchlist_toggle_requires_login(): void
    {
        $response = $this->postJson('/watchlist/toggle/872585', [
            'title' => 'Oppenheimer',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_add_and_remove_from_watchlist(): void
    {
        $user = User::factory()->create();

        // 1. Add to watchlist via JSON toggle
        $responseAdd = $this->actingAs($user)->postJson('/watchlist/toggle/872585', [
            'title' => 'Oppenheimer',
            'poster_path' => '/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
            'backdrop_path' => '/fm6KqXpk3M2HVveHwCrBSSBaO0V.jpg',
            'vote_average' => 8.1,
            'release_date' => '2023-07-19',
            'overview' => 'Oppenheimer movie overview',
        ]);

        $responseAdd->assertStatus(200);
        $responseAdd->assertJson(['status' => 'added', 'in_watchlist' => true]);
        $this->assertDatabaseHas('watchlists', [
            'user_id' => $user->id,
            'tmdb_movie_id' => 872585,
            'title' => 'Oppenheimer',
        ]);

        // 2. Remove from watchlist via same toggle
        $responseRemove = $this->actingAs($user)->postJson('/watchlist/toggle/872585');
        $responseRemove->assertStatus(200);
        $responseRemove->assertJson(['status' => 'removed', 'in_watchlist' => false]);
        $this->assertDatabaseMissing('watchlists', [
            'user_id' => $user->id,
            'tmdb_movie_id' => 872585,
        ]);
    }

    public function test_authenticated_user_can_view_watchlist_page(): void
    {
        $user = User::factory()->create();
        Watchlist::create([
            'user_id' => $user->id,
            'tmdb_movie_id' => 872585,
            'title' => 'Oppenheimer',
            'poster_path' => '/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
            'vote_average' => 8.1,
            'release_date' => '2023-07-19',
            'overview' => 'Oppenheimer movie overview',
        ]);

        $response = $this->actingAs($user)->get('/watchlist');

        $response->assertStatus(200);
        $response->assertSee('My Watchlist');
        $response->assertSee('Oppenheimer');
    }

    public function test_csrf_token_meta_tag_present_in_html_layout(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<meta name="csrf-token"', false);
    }

    public function test_xss_payloads_in_search_query_are_safely_escaped(): void
    {
        $xssPayload = '<script>alert("xss")</script>';
        $response = $this->get('/discover?q='.urlencode($xssPayload));

        $response->assertStatus(200);
        // The raw unescaped tag must NOT exist in the HTML output
        $response->assertDontSee($xssPayload, false);
        // It must be safely HTML entity encoded
        $response->assertSee(e($xssPayload), false);
    }

    public function test_xss_in_watchlist_item_is_escaped_on_render(): void
    {
        $user = User::factory()->create();
        $xssTitle = 'Movie <img src=x onerror=alert(1)>';

        Watchlist::create([
            'user_id' => $user->id,
            'tmdb_movie_id' => 999999,
            'title' => $xssTitle,
            'poster_path' => null,
            'vote_average' => 7.5,
            'release_date' => '2024-01-01',
            'overview' => 'Test overview',
        ]);

        $response = $this->actingAs($user)->get('/watchlist');

        $response->assertStatus(200);
        // Raw tag must not exist
        $response->assertDontSee($xssTitle, false);
        // Safely encoded
        $response->assertSee(e($xssTitle), false);
    }
}
