<?php

namespace App\Http\Resources;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 *
 * Ресурс комментария для API-ответов.
 */
class CommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
//            'author' => new UserResource($this->whenLoaded('user')),
            'film_id' => $this->film_id,
            'rating' => $this->rating,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
