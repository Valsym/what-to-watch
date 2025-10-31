<?php

namespace App\Services\Comments;

use App\DTO\Comments\CommentDto;
use App\DTO\Comments\StoreCommentDto;
use App\DTO\Comments\UpdateCommentDto;
use App\Models\Comment;
use App\Models\User;
use App\Repositories\Comments\CommentRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommentService
{
    public function __construct(
        private CommentRepository $commentRepository
    ) {}

    public function getFilmComments(int $filmId): Collection//array
    {
        $comments = $this->commentRepository->getFilmComments($filmId);

        return $comments;
//            ->map(function ($comment) {
//            return $this->mapToDto($comment);
//        })->toArray();
    }

    public function createComment(StoreCommentDto $dto): Comment//CommentDto
    {
        $comment = $this->commentRepository->createComment([
            'text' => $dto->text,
            'rating' => $dto->rating,
            'film_id' => $dto->film_id,
            'user_id' => $dto->user_id,
            'parent_id' => $dto->parent_id,
        ]);

        $comment->load('user');

        return $comment;
//        return $this->mapToDto($comment);
    }

    public function updateComment(int $commentId, UpdateCommentDto $dto, int $userId, bool $isModerator = false): Comment//CommentDto
    {
        // Проверяем права доступа
        if (!$isModerator && !$this->commentRepository->userCanEditComment($userId, $commentId)) {
            throw new AuthorizationException('You can only edit your own comments');
        }

        $data = ['text' => $dto->text];
        if ($dto->rating !== null) {
            $data['rating'] = $dto->rating;
        }

        $comment = $this->commentRepository->updateComment($commentId, $data);

        return $comment;
//        return $this->mapToDto($comment);
    }

    public function deleteComment(int $commentId, int $userId, bool $isModerator = false): void
    {
        $comment = $this->commentRepository->findCommentOrFail($commentId);

        // Проверяем права доступа
        if (!$isModerator && $comment->user_id !== $userId) {
            throw new AuthorizationException('You can only delete your own comments');
        }

        // Проверяем наличие ответов для обычного пользователя
        if (!$isModerator && $this->commentRepository->hasReplies($commentId)) {
            throw new AuthorizationException('Cannot delete comment with replies');
        }

        // Удаляем комментарий (с ответами если модератор)
        if ($isModerator && $this->commentRepository->hasReplies($commentId)) {
            $this->commentRepository->deleteCommentWithReplies($commentId);
        } else {
            $this->commentRepository->deleteComment($commentId);
        }
    }

    private function mapToDto(Comment $comment): CommentDto
    {
        // Вычисляем автора: если есть пользователь - берем его имя, иначе "Гость"
        $author = $comment->user ? $comment->user->name : Comment::DEFAULT_AUTHOR_NAME;

        return new CommentDto(
            id: $comment->id,
            text: $comment->text,
            rating: $comment->rating,
            film_id: $comment->film_id,
            user_id: $comment->user_id,
            parent_id: $comment->parent_id,
            author: $author,//$comment->user->name ?? Comment::DEFAULT_AUTHOR_NAME,
            created_at: $comment->created_at->toDateTimeString(),
        );
    }
}
