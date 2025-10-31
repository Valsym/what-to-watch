<?php

namespace App\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

interface ExternalCommentRepositoryInterface
{
    /**
     * Получить новые комментарии за указанный период
     */
    public function getRecentComments(Carbon $since): Collection;

    /**
     * Проверить доступность сервиса
     */
    public function isAvailable(): bool;

    /**
     * Получить лимит запросов в минуту
     */
    public function getRateLimit(): int;
}
