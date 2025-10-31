<?php

namespace App\Repositories\Comments;

use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommentRepository
{
    public function __construct(private Comment $comment)
    {
    }

    public function getFilmComments(int $filmId): Collection
    {
        return $this->comment
            ->with('user')
            ->where('film_id', $filmId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findCommentById(int $id): ?Comment
    {
        return $this->comment->with('user')->find($id);
    }

    public function findCommentOrFail(int $id): Comment
    {
        $comment = $this->findCommentById($id);
        if (!$comment) {
            throw new ModelNotFoundException('Comment not found');
        }
        return $comment;
    }

    /**
     * Добавление комментария
     *
     * @param array $data
     * @return Comment
     */
    public function createComment(array $data): Comment
    {
        return $this->comment->create($data);
    }


    /**
     * @param int $id
     * @param array $data
     * @return Comment
     */
    public function updateComment(int $id, array $data): Comment
    {
        $comment = $this->findCommentOrFail($id);
        $comment->update($data);
        return $comment->fresh(['user']);
    }

    public function deleteComment(int $id): bool
    {
        $comment = $this->findCommentOrFail($id);
        return $comment->delete();
    }

    public function deleteCommentWithReplies(int $id): bool
    {
        $comment = $this->findCommentOrFail($id);

        // Удаляем все ответы
        $comment->replies()->delete();

        // Удаляем сам комментарий
        return $comment->delete();
    }

    public function hasReplies(int $id): bool
    {
        $comment = $this->findCommentOrFail($id);
        return $comment->replies()->exists();
    }

    public function userCanEditComment(int $userId, int $commentId): bool
    {
        $comment = $this->findCommentOrFail($commentId);
        return $comment->user_id === $userId;
    }

}
