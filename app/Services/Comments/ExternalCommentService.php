<?php

namespace App\Services\Comments;

use App\Contracts\ExternalCommentRepositoryInterface;
use App\DTO\Comments\ExternalCommentDto;
use App\Models\Comment;
use App\Models\Film;
use App\Repositories\Comments\CommentRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для обработки комментариев
 */
class ExternalCommentService
{
    public function __construct(
        private ExternalCommentRepositoryInterface $externalCommentRepository,
        private CommentRepository $commentRepository,
    ) {}

    public function syncRecentComments(): int
    {
        if (!$this->externalCommentRepository->isAvailable()) {
            Log::warning('External comments service is unavailable');
            return 0;
        }

        $since = now()->subDay(); // Комментарии за последние сутки
        $externalComments = $this->externalCommentRepository->getRecentComments($since);

        if ($externalComments->isEmpty()) {
            Log::info('No new comments found from external service');
            return 0;
        }

        Log::info('Found external comments', ['count' => $externalComments->count()]);

        $importedCount = 0;
        $processedFilms = [];

        foreach ($externalComments as $externalComment) {
            $imported = $this->processExternalComment($externalComment);
            if ($imported) {
                $importedCount++;

                // Группируем по IMDB ID для логирования
                $imdbId = $externalComment['imdb_id'];
                $processedFilms[$imdbId] = ($processedFilms[$imdbId] ?? 0) + 1;
            }
        }

        Log::info('Imported external comments', [
            'total_imported' => $importedCount,
            'by_film' => $processedFilms
        ]);

        return $importedCount;
    }

    private function processExternalComment(array $externalComment): bool
    {
        try {
            // Находим фильм по IMDB ID
            $film = Film::where('imdb_id', $externalComment['imdb_id'])->first();

            if (!$film) {
                Log::debug('Film not found for external comment', [
                    'imdb_id' => $externalComment['imdb_id']
                ]);
                return false;
            }

            // Проверяем, нет ли уже такого комментария (по тексту и дате)
            $existingComment = Comment::where('film_id', $film->id)
                ->where('text', $externalComment['text'])
                ->where('created_at', $externalComment['created_at'])
                ->first();

            if ($existingComment) {
                Log::debug('Comment already exists', [
                    'film_id' => $film->id,
                    'text_preview' => substr($externalComment['text'], 0, 50)
                ]);
                return false;
            }

            // Создаем комментарий
            $this->commentRepository->createComment([
                'film_id' => $film->id,
                'text' => $externalComment['text'],
                'rating' => $externalComment['rating'],
                'user_id' => null, // Внешние комментарии без пользователя
                'parent_id' => null,
                'created_at' => $externalComment['created_at'],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to process external comment', [
                'imdb_id' => $externalComment['imdb_id'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
