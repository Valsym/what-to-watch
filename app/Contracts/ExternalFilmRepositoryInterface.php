<?php

namespace App\Contracts;

interface ExternalFilmRepositoryInterface
{
    /**
     * Получить данные о фильме по IMDB ID
     */
    public function getFilmData(string $imdbId): ?array;

    /**
     * Проверить доступность сервиса
     */
    public function isAvailable(): bool;
}
