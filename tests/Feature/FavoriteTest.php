<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    /**
     * Функциональные тесты для проверки действий с избранными фильмами
     * Проверяет:
     * * Получение списка избранных фильмов пользователя
     * * Добавление фильма в избранное
     * * Удаление фильма из избранных
     * * Ошибка при попытке добавить фильм дважды
     * * Ошибка при попытке добавить не существующий фильм
     */

    use RefreshDatabase;

    /**
     * Тест: Получение списка избранных фильмов пользователя
     * @return void
     */
    public function testGetFavoriteList()
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $user->favoriteFilms()->attach($film->id);

        Sanctum::actingAs($user);

        $response = $this->getJson(route('favorite.index'));

        $response->assertOk()
            ->assertJsonFragment(['data']);

    }

}
