<?php

namespace App\Models;

use App\Services\TmdbService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Watchlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tmdb_movie_id',
        'title',
        'poster_path',
        'backdrop_path',
        'vote_average',
        'release_date',
        'overview',
    ];

    protected $casts = [
        'vote_average' => 'float',
        'tmdb_movie_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPosterUrlAttribute(): string
    {
        return app(TmdbService::class)->getImageUrl($this->poster_path, 'w500');
    }

    public function getBackdropUrlAttribute(): string
    {
        return app(TmdbService::class)->getBackdropUrl($this->backdrop_path, 'original');
    }

    public function getReleaseYearAttribute(): ?string
    {
        return $this->release_date ? substr($this->release_date, 0, 4) : null;
    }
}
