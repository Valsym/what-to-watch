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
     * + Получение списка избранных фильмов пользователя
     * + Добавление фильма в избранное
     * + Удаление фильма из избранных
     * + Ошибка при попытке добавить фильм дважды
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

//        $response->dump();

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'poster_image',
                        'preview_image',
                        'preview_video_link',
                        'genre',
                        'released'
                    ]
                ]
            ]);

    }

    /**
     * Добавление фильма в избранное
     *
     * @return void
     */
    public function testAddFavoriteFilm()
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $response =
            $this->actingAs($user)->postJson(route('favorite.store',
                $film->id));

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Фильм успешно добавлен в избранное!']);

        $this->assertDatabaseHas('favorite_films', [
            'user_id' => $user->id,
            'film_id' => $film->id,
        ]);
    }

    /**
     * Тест: Удаление фильма из избранных
     *
     * @return void
     */
    public function testDeleteFilmFromFavorite()
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();
        $user->favoriteFilms()->attach($film->id);

        $response =
            $this->actingAs($user)->deleteJson(route('favorite.destroy',
                $film->id));

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Фильм успешно удален из избранного!']);

    }

    /**
     * Тест: Ошибка при попытке добавить фильм дважды
     *
     * @return void
     */
    public function testAddToFavoritesDuplicate(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $user->favoriteFilms()->attach($film);

        $response = $this->actingAs($user)->postJson(route('favorite.store', $film->id));

        $response->assertStatus(422)
            ->assertJson(['message' => 'Фильм уже в избранном']);


//        $response =
//            $this->actingAs($user)->postJson(route('favorite.store',
//                $film->id));
//        $response =
//            $this->actingAs($user)->postJson(route('favorite.store',
//                $film->id));
//
//        $response->assertStatus(401)
//            ->assertJsonFragment(['message' => 'Фильм уже в избранном']);
    }

    /**
     * Тест: Ошибка при попытке добавить в избранное не существующий фильм
     *
     * @return void
     */
    public function testErrorAddNotExistFilmToFavorite()
    {
        $user = User::factory()->create();

        $response =
            $this->actingAs($user)->postJson(route('favorite.store',
                9999));

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Запрашиваемая страница не существует',
            ]);
    }

    public function testGetFavorites(): void
    {
        $user = User::factory()->create();
        $films = Film::factory()->count(3)->create();

        // Добавляем фильмы в избранное
//        foreach ($films as $film) {
//            $user->favoriteFilms()->attach($film->id);
//        }
        // Добавляем фильмы в избранное с разными временными метками
        $baseTime = now();

        $user->favoriteFilms()->attach($films[0]->id, ['created_at' => $baseTime->copy()->subMinutes(10)]);
        $user->favoriteFilms()->attach($films[1]->id, ['created_at' => $baseTime->copy()->subMinutes(5)]);
        $user->favoriteFilms()->attach($films[2]->id, ['created_at' => $baseTime]);

        $response = $this->actingAs($user)->getJson(route('favorite.index'));
//        $response->dump();

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'poster_image',
                        'preview_image',
                        'background_image',
                        'background_color',
                        'video_link',
                        'preview_video_link',
                        'description',
                        'rating',
//                        'scores_count',
                        'director',
                        'starring',
                        'run_time',
                        'genre',
                        'released',
                        'is_favorite',
                    ]
                ]
            ]);

        // Проверяем, что фильмы в правильном порядке (последний добавленный - первый)
//        $responseData = $response->json('data');
//        $this->assertEquals($films[2]->id, $responseData[0]['id']);
        // Проверяем, что фильмы в правильном порядке (последний добавленный - первый)
        $responseData = $response->json('data');
        $this->assertEquals($films[2]->id, $responseData[0]['id']); // последний добавленный
        $this->assertEquals($films[1]->id, $responseData[1]['id']);
        $this->assertEquals($films[0]->id, $responseData[2]['id']); // первый добавленный
    }

}
