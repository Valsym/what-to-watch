<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    /**
     * Функциональные тесты для проверки действий с жанрами фильмов
     * Проверяет:
     * * Получение списка жанров
     * * Обновление жанров
     * * Ошибки доступа
     * * Ошибки при не существующем жанре
     */

    use RefreshDatabase;

    /**
     * Тест: Получение списка жанров
     *
     * @return void
     */
    public function testGetGenreList()
    {
        $count = random_int(5, 10);
        Genre::factory()->count($count)->create();
        $response = $this->getJson(route('genre.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /**
     * Тест обновления названия жанра модератором
     *
     * @return void
     */
    public function testGenreUpdateByModerator()
    {
        $moderator =
            User::factory()->create([
                'role' => User::ROLE_MODERATOR,
            ]);

        $genre = Genre::factory()->create();
        $response = $this->actingAs($moderator)->
            patchJson(route('genre.update', $genre->id),
            ['name' => 'New Genre',]);

//        $response->assertStatus(200);
//        $response->assertJsonStructure(['data']);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'New Genre',
            ]);
    }

    /**
     * Тест: попытка обновить жанр без авторизации
     *
     * @return void
     */
    public function testUpdateGenreUnauthenticated(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->patchJson(route('genre.update', $genre->id), [
            'name' => 'test',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Тест: попытка обновить жанр обычным пользователем
     *
     * @return void
     */
    public function testUpdateGenreByUser(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->
            patchJson(route('genre.update', $genre->id), [
            'name' => 'Action',
        ]);

        $response->assertForbidden();
    }

}
