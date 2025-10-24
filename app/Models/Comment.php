<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $text
 * @property int|null $rating
 * @property int $film_id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereFilmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereUserId($value)
 * @mixin \Eloquent
 */
class Comment extends Model
{
    use HasFactory;

    public const string DEFAULT_AUTHOR_NAME = "Гость";

    protected $casts = [
        'rating' => 'int',
        'comment_id' => 'int',
        'user_id' => 'int',
        'film_id' => 'int'
    ];

    protected $fillable = [
        'text',
//        'author',
        'rating',
        'parent_id',
        'user_id',
        'film_id'
    ];

    /*protected $visible = [
        'id',
//        'text',
        'rating',
        'parent_id',
        'created_at',
        'author',

    ];

    protected $fillable = [
        'text',
        'user_id',
    ];

    protected $appends = [
        'author',
    ];*/

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAuthorAttribute()
    {
        return $this->user->name ?? self::DEFAULT_AUTHOR_NAME;
    }

    /**
     * Ответы на этот комментарий (дочерние)
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Родительский комментарий
     *
     * @return         BelongsTo
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

}
