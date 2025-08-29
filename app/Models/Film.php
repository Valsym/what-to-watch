<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $poster_image
 * @property string|null $preview_image
 * @property string|null $background_image
 * @property string|null $background_color
 * @property string|null $video_link
 * @property string|null $preview_video_link
 * @property string|null $description
 * @property string|null $director
 * @property string|null $starring
 * @property int|null $run_time
 * @property int|null $released
 * @property int $promo
 * @property string $status
 * @property string $imdb_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\FilmFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereBackgroundColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereDirector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereImdbId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film wherePosterImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film wherePreviewImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film wherePreviewVideoLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film wherePromo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereReleased($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereRunTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereStarring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Film whereVideoLink($value)
 * @mixin \Eloquent
 */
class Film extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ON_MODERATION = 'moderate';
    public const STATUS_READY = 'ready';

    public const LIST_FIELDS = ['films.id', 'name', 'preview_image', 'preview_video_link'];

    protected $with = ['genres'];

    protected $table = 'films';


    protected $appends = [
        'rating',
        'is_favorite',
    ];

    protected $casts = [
        'starring' => 'array',
        'promo' => 'bool',
    ];

    protected $fillable = [
        'name',
        'poster_image',
        'preview_image',
        'background_image',
        'background_color',
        'video_link',
        'preview_video_link',
        'description',
        'director',
        'starring',
        'run_time',
        'released',
        'promo',
        'status',
        'imdb_id',
        'created_at',
        'updated_at',

    ];

    /*public const LIST_FIELDS = ['films.id', 'name', 'preview_image', 'preview_video_link'];

    protected $with = ['genres'];

    protected $appends = [
        'rating',
        'is_favorite',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    protected $casts = [
        'starring' => 'array',
        'promo' => 'bool',
    ];

    protected $fillable = [
        'name',
        'poster_image',
        'preview_image',
        'background_image',
        'background_color',
        'video_link',
        'preview_video_link',
        'description',
        'director',
        'starring',
        'run_time',
        'released',
        'promo',
    ];*/

    public function genres(): BelongsToMany
    {
//        return $this->belongsToMany(Genre::class);
        return $this->belongsToMany(Genre::class, 'film_genre');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNotNull('rating');
    }

    public function getRatingAttribute()
    {
        return round($this->scores()->avg('rating'), 1);
    }

    public function getIsFavoriteAttribute()
    {
        return Auth::check() && Auth::user()->hasFilm($this);
    }

    /**
     * Добавление сортировки.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $orderBy
     * @param string|null $orderTo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query, ?string $orderBy, ?string $orderTo)
    {
        return $query->when($orderBy === 'rating', function ($q) {
            $q->withAvg('scores as rating', 'rating');
        })->orderBy($orderBy ?? 'released', $orderTo ?? 'desc');
    }

    public function scopePromo($query)
    {
        $query->where('promo', true);
    }

}
