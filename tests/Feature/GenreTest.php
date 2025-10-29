<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function testGetGenreList(): void
    {
        $count = random_int(5, 10);
        Genre::factory()->count($count)->create();

        $response = $this->getJson(route('genre.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name']
                ]
            ])
            ->assertJsonCount($count, 'data');
    }

    public function testGenreUpdateByModerator(): void
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);

        $genre = Genre::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($moderator)
            ->patchJson(route('genre.update', $genre->id), [
                'name' => 'New Genre',
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $genre->id,
                    'name' => 'New Genre',
                ]
            ]);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'New Genre',
        ]);
    }

    public function testUpdateGenreUnauthenticated(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->patchJson(route('genre.update', $genre->id), [
            'name' => 'test',
        ]);

        $response->assertUnauthorized();
    }

    public function testUpdateGenreByUser(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson(route('genre.update', $genre->id), [
                'name' => 'Action',
            ]);

        $response->assertForbidden();
    }

    public function testUpdateGenreNotFound(): void
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR
        ]);

        $response = $this->actingAs($moderator)
            ->patchJson(route('genre.update', 404), [
                'name' => 'Melodrama',
            ]);

        $response->assertNotFound();
    }

    public function testUpdateGenreValidation(): void
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);

        $genre1 = Genre::factory()->create(['name' => 'Action']);
        $genre2 = Genre::factory()->create(['name' => 'Drama']);

        // Попытка обновить на существующее имя
        $response = $this->actingAs($moderator)
            ->patchJson(route('genre.update', $genre1->id), [
                'name' => 'Drama', // уже существует у genre2
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}


//
//namespace Tests\Feature;
//
//use App\Models\Film;
//use App\Models\Genre;
//use App\Models\User;
//use Illuminate\Foundation\Testing\RefreshDatabase;
//use Tests\TestCase;
//
//class GenreTest extends TestCase
//{
//    /**
//     * Функциональные тесты для проверки действий с жанрами фильмов
//     * Проверяет:
//     * * Получение списка жанров
//     * * Обновление жанров
//     * * Ошибки доступа
//     * * Ошибки при не существующем жанре
//     */
//
//    use RefreshDatabase;
//
//    /**
//     * Тест: Получение списка жанров
//     *
//     * @return void
//     */
//    public function testGetGenreList()
//    {
//        $count = random_int(5, 10);
//        Genre::factory()->count($count)->create();
//        $response = $this->getJson(route('genre.index'));
//
//        $response->assertStatus(200);
//        $response->assertJsonStructure(['data']);
//    }
//
//    /**
//     * Тест обновления названия жанра модератором
//     *
//     * @return void
//     */
//    public function testGenreUpdateByModerator()
//    {
//        $moderator = User::factory()->create([
//            'role' => User::ROLE_MODERATOR,
//        ]);
//
//        $genre = Genre::factory()->create();
//        $response = $this->actingAs($moderator)->
//            patchJson(route('genre.update', $genre->id),
//            ['name' => 'New Genre',]);
//
////        $response->assertStatus(200);
////        $response->assertJsonStructure(['data']);
//
//        $response->assertOk()
//            ->assertJsonFragment([
//                'name' => 'New Genre',
//            ]);
//    }
//
//    /**
//     * Тест: попытка обновить жанр без авторизации
//     *
//     * @return void
//     */
//    public function testUpdateGenreUnauthenticated(): void
//    {
//        $genre = Genre::factory()->create();
//
//        $response = $this->patchJson(route('genre.update', $genre->id), [
//            'name' => 'test',
//        ]);
//
//        $response->assertUnauthorized()
//            ->assertJson([
//                'message' => 'Запрос требует аутентификации',
//            ]);
//    }
//
//    /**
//     * Тест: попытка обновить жанр обычным пользователем
//     *
//     * @return void
//     */
//    public function testUpdateGenreByUser(): void
//    {
//        $user = User::factory()->create();
//        $genre = Genre::factory()->create();
//
//        $response = $this->actingAs($user)->
//            patchJson(route('genre.update', $genre->id), [
//            'name' => 'Action',
//        ]);
//
//        $response->assertForbidden();;
//    }
//
//    /**
//     * Тест попытки обновления несуществующего жанра
//     *
//     * @return void
//     */
//    public function testUpdateGenreNotFound(): void
//    {
//        $moderator = User::factory()->create([
//            'role' => User::ROLE_MODERATOR
//        ]);
//
//        $response = $this->actingAs($moderator)->
//            patchJson(route('genre.update', 404), [
//            'name' => 'Melodrama',
//        ]);
//
//        $response->assertNotFound()
//            ->assertJson([
//                'message' => 'Запрашиваемая страница не существует',
//            ]);
//    }
//
//}
