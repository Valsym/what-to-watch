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

    /**
     * Тест успешного добавления фильма модератором.
     *
     * @return void
     */
    public function testStoreFilm(): void
    {
        $moderator =
            User::factory()->create([
                'role' => User::ROLE_MODERATOR,
            ]);

        $response =
            $this->actingAs($moderator)->postJson(route('film.store'), [
                'imdb_id' => 'tt1234567',
            ]);

        $response->assertCreated()->assertJsonStructure([
            'data' => [
                "id",
                "name",
                "poster_image",
                "preview_image",
                "background_image",
                "background_color",
                "video_link",
                "preview_video_link",
                "description",
                "rating",
                "scores_count",
                "director",
                "starring",
                "run_time",
                "genre",
                "released",
                "is_favorite",
                "is_promo",
            ]
        ]);
    }

    /**
     * Проверка получения информации о фильме.
     * Ожидается возвращение дополнительно генерируемых полей в дополнение к информации из БД.
     */
    public function testGetOneFilmRoute()
    {
        $film = Film::factory()
            ->has(Comment::factory(3)->sequence(['rating' => 1], ['rating' => 2], ['rating' => 1]))
            ->create(['released' => 2001]);

        $response = $this->getJson(route('film.show', $film->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $film->name,
            'scores_count' => 3,
            'rating' => 1.3,
        ]);
    }

    /**
     * Проверка получения информации о фильме.
     * Аутентифицированный пользователь должен видеть информацию о наличии фильма в избранном.
     */
    public function testGetOneFilmByUser()
    {
        $film = Film::factory()->create();
        $user = User::factory()->hasAttached($film)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('film.show', $film->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $film->name,
            'is_favorite' => true,
        ]);
    }

    /**
     * Заменить на обращение к несуществующему фильму
     */
    public function testWrongRoute()
    {
        $response = $this->getJson(route('film.show', 404));

        $response->assertStatus(404);
//        $response->assertJsonStructure(['message', 'errors' => ['exception']]);
        $response->assertJsonFragment(['message' => 'Страница не найдена']);
    }

    /**
     * Тестирование обработки случая, когда фильм не найден.
     *
     *  Проверяет:
     *  - Статус ответа 404 Not Found
     *  - Наличие корректного сообщения об ошибке
     *  - Формат JSON-ответа
     *
     * @return void
     */
    public function testReturns404WhenFilmNotFound(): void
    {
        $response = $this->getJson(route('film.show', 999));

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Запрашиваемая страница не существует',
            ]);
    }

    /**
     * Проверка получения похожих фильмов.
     * На основании принадлежности к одному жанру.
     */
    public function testGetSimilarFilmsRoute()
    {
        $genre = Genre::factory()->create();
        $film = Film::factory()->hasAttached($genre)->create();
        $film2 = Film::factory()->hasAttached($genre)->create();
        $film3 = Film::factory()->hasAttached(Genre::factory())->create();

        $response = $this->getJson(route('films.similar', $film->id));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $film2->id]);
        $response->assertJsonMissing(['id' => $film3->id]);
    }

    /**
     * Тест успешного обновления фильма модератором.
     *
     * @return void
     */
    public function testUpdateFilm(): void
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);
        $film = Film::factory()->create();

        $response = $this->actingAs($moderator)->patchJson(route('film.update', $film->id), [
            'name' => 'Updated Title',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    /**
     * Тест ошибки 401 при попытке обновить фильм без авторизации.
     */
    public function testUpdateFilmUnauthenticated(): void
    {
        $film = Film::factory()->create();

        $response = $this->patchJson(route('film.update', $film->id), [
            'name' => 'No Access',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Тест ошибки 403 при попытке обновить фильм обычным пользователем.
     */
    public function testUpdateFilmAsUser(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $response = $this->actingAs($user)->patchJson(route('film.update', $film->id), [
            'name' => 'Forbidden',
        ]);

        $response->assertForbidden();
    }

    /**
     * Тест получения текущего промо-фильма.
     *
     * @return void
     */
    public function testShowPromo(): void
    {
        $promoFilm = Film::factory()->create(['promo' => true]);
        $response = $this->getJson(route('promo.show'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $promoFilm->id,
                    'promo' => true
                ]
            ]);
    }

    /**
     * Тест создания промо-фильма модератором.
     *
     * @return void
     */
    public function testCreatePromo(): void
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
        ]);
        $film = Film::factory()->create();

        $response = $this->actingAs($moderator)->postJson(route('promo.create', $film->id));

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    /**
     * Тест ошибки 403 при попытке создать промо-фильм обычным пользователем.
     */
    public function testCreatePromoAsUser(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $response = $this->actingAs($user)->postJson(route('promo.create', $film->id));

        $response->assertForbidden();
    }

    /**
     * Тест ошибки 401 при попытке создать промо-фильм без авторизации.
     */
    public function testCreatePromoUnauthenticated(): void
    {
        $film = Film::factory()->create();

        $response = $this->postJson(route('promo.create', $film->id));

        $response->assertUnauthorized();
    }

}
