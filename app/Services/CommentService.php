<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Support\Collection;

/**
 * Сервис для работы с комментариями к фильмам
 */
class CommentService
{
    /**
     * Получение комментариев к фильму
     */
    public function getFilmComments(int $filmId): Collection
    {
        return Comment::with('user')
            ->where('film_id', $filmId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Comment $comment) {
                return [
                    'text' => $comment->text,
                    'author' => $comment->user?->name ?? 'Гость',
                    'created_at' => $comment->created_at->toDateTimeString(),
                    'rating' => $comment->rating,
                ];
            });
    }

    /**
     * Создание нового комментария
     */
    public function createComment(array $data): Comment
    {
        return Comment::create($data);
    }

    /**
     * Обновление комментария
     */
    public function updateComment(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        return $comment;
    }

    /**
     * Удаление комментария с ответами
     */
    public function deleteComment(Comment $comment): void
    {
        $comment->deleteWithReplies();
    }
}
