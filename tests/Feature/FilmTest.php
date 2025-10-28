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

class FilmTest extends TestCase
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
//        $response->assertJsonStructure(['data' => [], 'links' => [], 'total']);
//        $response->assertJsonFragment(['total' => $count]);
//
//        $response->assertJsonCount($count, 'data');
//        $response->assertJsonStructure([
//            'data' => [
//                '*' => [
//                    'id',
//                    'name',
//                    'poster_image',
//                    'preview_image',
//                    'preview_video_link',
//                    'genre',
//                    'released',
//                ]
//            ]
//        ]);
        // Проверяем структуру ответа
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'poster_image',
                        'preview_image',
                        'preview_video_link',
                        'genre',
                        'released',
                    ]
                ],
                'current_page',
                'first_page_url',
                'next_page_url',
                'prev_page_url',
                'per_page',
                'total'
            ]
        ]);
//        $response->assertJsonStructure([
//            'data' => [
//                '*' => [
//                    'id',
//                    'name',
//                    'poster_image',
//                    'preview_image',
//                    'preview_video_link',
//                    'genre',
//                    'released',
//                ]
//            ],
//            'current_page',
//            'first_page_url',
//            'next_page_url',
//            'prev_page_url',
//            'per_page',
//            'total'
//        ]);
//        $response->assertJsonCount($count > 8 ? 8 : $count, 'data');
        $responseData = $response->json();
        $this->assertCount($count, $responseData['data']['data']);
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
        $count = random_int(2, 10);
        Film::factory()->count($count)->create();

        $response = $this->getJson(route('films.index'));

//        $response->assertStatus(200);
//        $response->assertJsonStructure(['data']);
////        $response->assertJsonCount($count > 8 ? 8 : $count, 'data');
//        $response->assertJsonCount($count, 'data'); // Теперь ожидаем ровно $count
        $response->assertJsonStructure([
            'data' => [
                'data',
                'current_page',
                'first_page_url',
                'next_page_url',
                'prev_page_url',
                'per_page',
                'total'
            ]
        ]);

        $responseData = $response->json();
        $this->assertCount($count, $responseData['data']['data']);

    }

    /**
     * Проверка получения списка фильмов по жанру.
     * Ожидается что будут возвращены только фильмы с указанным жанром.
     * Примечание*: Запустите тест несколько раз подряд чтобы убедиться, что плавающая ошибка исчезла
     * for i in {1..10}; do sail artisan test --filter=testGetFilmsByGenre; done
     */
    public function testGetFilmsByGenre()
    {
        $genre = Genre::factory()->create();
        $count = random_int(2, 10);

        // Создаём фильмы с нужным жанром и РАЗНЫМИ годами выпуска
        $filmsWithGenre = Film::factory($count)
            ->hasAttached($genre)
            ->sequence(fn($sequence) => ['released' => 2000 + $sequence->index])
            ->create()
            ->sortBy('released'); // Сортируем по released для стабильности

        // Проверяем, что фильмы создались с правильным статусом
        $this->assertDatabaseCount('films', $count);
        $this->assertDatabaseHas('films', [
            'status' => Film::STATUS_READY
        ]);

        // Создаём фильмы БЕЗ жанров
        Film::factory($count)->create(['released' => 1990]); // Другие года

        // Добавим проверку количества фильмов в базе
        $totalFilms = Film::count();
        $filmsWithGenreCount = Film::whereHas('genres', function ($query) use ($genre) {
            $query->where('name', $genre->name);
        })->count();

//        dump("Total films: " . $totalFilms);
//        dump("Films with genre: " . $filmsWithGenreCount);

        // Используем разрешённую сортировку по released
        $response = $this->getJson(route('films.index', [
            'genre' => $genre->name,
            'order_by' => 'released',      // ← Используем разрешённое поле
            'order_to' => 'asc'            // ← По возрастанию
        ]));
//        dump($response->json());

//        $result = $response->json('data');
//
//        $response->assertStatus(200);
        $response->assertStatus(200);

        // Получаем всю структуру ответа
        $responseData = $response->json();

        // Проверяем структуру ответа
        $response->assertJsonStructure([
            'data' => [
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
                ],
                'current_page',
                'first_page_url',
                'next_page_url',
                'prev_page_url',
                'per_page',
                'total'
            ]
        ]);

        // Проверяем количество фильмов в data.data
        $filmsData = $responseData['data']['data'];
        $this->assertCount($count, $filmsData);

        // Проверяем конкретные ID в правильном порядке (по released)
        $expectedIds = $filmsWithGenre->take($count)->pluck('id')->toArray();
        $actualIds = array_column($filmsData, 'id');
        $this->assertEquals($expectedIds, $actualIds);


//        // Только фильмы с жанром, но не больше 8 из-за пагинации
//        $expectedCount = min($count, 8);
//        $response->assertJsonCount($expectedCount, 'data');
//
//        // Проверяем конкретные ID в правильном порядке (по released)
//        $expectedIds = $filmsWithGenre->take($expectedCount)->pluck('id')->toArray();
//        $this->assertEquals($expectedIds, Arr::pluck($result, 'id'));
    }
//    public function testGetFilmsByGenre() // этот тест вызывал плавающую ошибку
//        // из-за проблемы с непредсказуемой сортировкой в контроллере и модели
//        // Исправленныйтест выше
//    {
//        $genre = Genre::factory()->create();
//        $count = random_int(2, 10);
//        $films = Film::factory($count)->hasAttached($genre)->create(['released' => 2000]);
//        Film::factory($count)->create();
//
//        $response = $this->getJson(route('films.index', ['genre' => $genre->name]));
//        $result = $response->json('data');
////        $response->dump();
////        dump($result);
//
//        $response->assertStatus(200);
//        $response->assertJsonCount($count > 8 ? 8 : $count, 'data');
//        $this->assertEquals($films->pluck('id')->toArray(), Arr::pluck($result, 'id'));
//    }

    /**
     * Проверка, что по умолчанию возвращаются только готовые фильмы.
     */
    public function testGetReadyFilms()
    {
        $film = Film::factory()->create(['status' => Film::STATUS_READY]);
        Film::factory()->create(['status' => Film::STATUS_PENDING]);

        $response = $this->getJson(route('films.index'));

        $response->assertStatus(200);
//        $response->assertJsonCount(1, 'data');

        $responseData = $response->json();
        $this->assertCount(1, $responseData['data']['data']);
        $response->assertJsonFragment(['id' => $film->id]);
    }

    public function testFilmStatus()
    {
        $film = Film::factory()->create();
        $this->assertEquals(Film::STATUS_READY, $film->status);
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
//        $response->dump();

        $response->assertStatus(200);
//        $response->assertJsonCount(1, 'data');
        $responseData = $response->json();
        $this->assertCount(1, $responseData['data']['data']);
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

        $responseData = $response->json();
        $result = $responseData['data']['data'];

        $response->assertStatus(200);
        $this->assertEquals([$film2->id, $film3->id, $film1->id], Arr::pluck($result, 'id'));

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
//            'message' => 'Forbidden',
            'message' => 'Запрос требует аутентификации',
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
    public function testStoreFilmAsModerator(): void
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
                "promo",
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
            'is_favorite' => false,
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
        $response->assertJsonFragment(['message' => 'Запрашиваемая страница не существует']);
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

}
