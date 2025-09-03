<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteFilm extends Model
{
    use HasFactory;

    protected $table = 'favorite_films';

    protected $fillable  = [
        'user_id',
        'film_id',
    ];

    protected $casts  = [
        'user_id',
        'film_id',
    ];

    /**
     * @return         BelongsTo
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    /**
     * @return         BelongsTo
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
