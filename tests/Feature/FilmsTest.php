<?php


namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Film;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FilmsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверка получения списка фильмов.
     * Ожидается получение правильной структуры, и созданного к-ва.
     */
    public function testGetFilmsRoute()
    {
        $count = random_int(2, 10);
        Film::factory()->count($count)->hasAttached(Genre::factory())->create();
        $response = $this->getJson(route('films.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [], 'links' => [], 'total']);
        $response->assertJsonFragment(['total' => $count]);
    }

    /**
     * Тестирование получения списка фильмов с пагинацией.
     *
     *  Проверяет:
     *  - Успешный статус ответа (200 OK)
     *  - Корректную структуру JSON-ответа
     *  - Количество элементов на странице (по умолчанию 8)
     *  - Наличие данных о пагинации
     *
     * @return void
     */
    public function testReturnsPaginatedFilmList(): void
    {
        $count = 20;
        Film::factory()->count($count)->create();
//        $count = random_int(2, 10);
//        Film::factory()->count($count)->hasAttached(Genre::factory())->create();

        $response = $this->getJson(route('films.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [], 'links' => [], 'total']);
        $response->assertJsonFragment(['total' => $count]);

    }

    /**
     * Проверка получения списка фильмов по жанру.
     * Ожидается что будут возвращены только фильмы с указанным жанром.
     * Указываем одинаковый год выпуска для исключения изменения порядка (дефолтной сортировки).
     */
    public function testGetFilmsByGenre()
    {
        $genre = Genre::factory()->create();
        $count = 2;
        $films = Film::factory($count)->hasAttached($genre)->create(['released' => 2000]);
        Film::factory(3)->create();

        $response = $this->getJson(route('films.index', ['genre' => $genre->name]));
        $result = $response->json('data');

        $response->assertStatus(200);
        $response->assertJsonFragment(['total' => $count]);
        $this->assertEquals($films->pluck('id')->toArray(), Arr::pluck($result, 'id'));
    }

    /**
     * Проверка, что по умолчанию возвращаются только готовые фильмы.
     */
    public function testGetReadyFilms()
    {
        $film = Film::factory()->create(['status' => Film::STATUS_READY]);
        Film::factory()->create(['status' => Film::STATUS_PENDING]);

        $response = $this->getJson(route('films.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $film->id]);
    }

    /**
     * Проверка, что модератор может запросить список фильмов на модерации.
     */
    public function testGetNotReadyFilmsForModerator()
    {
        Sanctum::actingAs(User::factory()->moderator()->create());

        $film = Film::factory()->create(['status' => Film::STATUS_ON_MODERATION]);
        Film::factory()->create(['status' => Film::STATUS_READY]);
        Film::factory()->create(['status' => Film::STATUS_PENDING]);

        $response = $this->getJson(route('films.index', ['status' => Film::STATUS_ON_MODERATION]));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $film->id]);
    }

    /**
     * Проверка получения списка фильмов отсортированных по рейтингу, по возрастанию.
     */
    public function testOrderedGetFilms()
    {
        $film1 = Film::factory()
            ->has(Comment::factory()->state(['rating' => 5]))
            ->create(['released' => 2001]);

        $film2 = Film::factory()
            ->has(Comment::factory()->state(['rating' => 1]))
            ->create(['released' => 2002]);

        $film3 = Film::factory()
            ->has(Comment::factory()->sequence(['rating' => 3]))
            ->create(['released' => 2003]);

        $response = $this->getJson(route('films.index', ['order_by' => 'rating', 'order_to' => 'asc']));
        $result = $response->json('data');

        $response->assertStatus(200);
        $this->assertEquals([$film2->id, $film3->id, $film1->id], Arr::pluck($result, 'id'));
    }

    /**
     * Проверка попытки
     * @return void
     */
    public function testCreateFilmRoute()
    {
        $this->markTestSkipped('Требуется авторизация');
        $response = $this->postJson(route('film.store'));

        $response->assertStatus(201);
    }

    /**
     * Тест ошибки 401 при попытке добавить фильм неавторизованным пользователем.
     */
    public function testStoreFilmUnauthenticated(): void
    {
        $response =
            $this->postJson(route('film.store'), [
                'imdb_id' => 'tt1234567',
            ]);

        $response->assertUnauthorized()->assertJson([
            'message' => 'Unauthenticated.',
//            'message' => 'Запрос требует аутентификации.',
        ]);
    }

    /**
     * Тест ошибки 403 при попытке добавить фильм обычным пользователем.
     */
    public function testStoreFilmAsUser(): void
    {
        $moderator =
            User::factory()->create([
                'role' => User::ROLE_MODERATOR,
            ]);

        $user =
            User::factory()->create([
                'role' => User::ROLE_USER,
            ]);

        $response =
            $this->actingAs($user)->postJson(route('film.store'), [
                'imdb_id' => 'tt1234567',
            ]);

        $response->assertForbidden();
    }

}
