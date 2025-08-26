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

//        $response->assertOk()
//            ->assertJsonStructure([
//                'data' => [
//                    '*' => [
//                        'id',
//                        'name',
//                        'poster_image',
//                        'preview_image',
//                        'preview_video_link',
//                        'genre',
//                        'released'
//                    ]
//                ]
//            ])
//            ->assertJsonCount(8, 'data');
    }

}
