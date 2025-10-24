<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Laravel\Sanctum\Sanctum;


class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
//    public function test_example(): void
//    {
//        $response = $this->get('/');
//
//        $response->assertStatus(200);
//    }

    /**
     * Попытка добавления комментария гостем.
     */
    public function testAddFilmCommentByGuest()
    {
        $response = $this->postJson(route('comments.store', 1));

        $response->assertStatus(401);
    }

    /**
     * Проверка добавления комментария пользователем.
     */
    public function testAddFilmCommentByUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $film = Film::factory()->create();
        $comment = Comment::factory()->make();

        $response = $this->postJson(route('comments.store', $film->id), $comment->toArray());
//        $response->dd();

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'film_id' => $film->id,
            'user_id' => $user->id,
            'text' => $comment->text,
            'rating' => $comment->rating,
        ]);
    }

    /**
     * Получение списка комментариев.
     */
    public function testGetFilmCommentsRoute()
    {
        $count = random_int(2, 10);

        $film = Film::factory()
            ->has(Comment::factory($count))
            ->create();

        $response = $this->getJson(route('comments.index', $film));
        // Временная отладка
//        $response->dump();

        $response->assertStatus(200);
        $response->assertJsonCount($count, 'data');
        $response->assertJsonFragment(['text' => $film->comments->first()->text]);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'text', 'user_id', 'film_id', 'rating', 'created_at']
            ]
        ]);
    }

    /**
     * Попытка редактирования комментария не аутентифицированным пользователем.
     */
    public function testUpdateCommentByGuest()
    {
        $comment = Comment::factory()->create();

        $response = $this->patchJson(route('comments.update', $comment), []);

        $response->assertStatus(401);
    }

    /**
     * Попытка редактирования комментария пользователем не автором комментария.
     */
    public function testUpdateCommentByCommonUser()
    {
        Sanctum::actingAs(User::factory()->create());

        $comment = Comment::factory()->create();

        $response = $this->patchJson(route('comments.update', $comment), []);

        $response->assertStatus(422);//403);
    }

    /**
     * Пользователь не может редактировать чужой комментарий
     *
     * @return void
     */
    public function testUserCannotUpdateOthersComments(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $anotherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $newText = 'Test comment Test comment Test comment Test comment';

        $response = $this->patchJson(route('comments.update', ['comment' => $comment->id]),
            [
            'text' => $newText,
            ]
        );

        $response->assertStatus(403);

    }

    /**
     * Успешное редактирование комментария автором.
     */
    public function testUpdateCommentByAthor()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $comment = Comment::factory()->for($user)->create();

        $newText = 'some text some text some text some text  some text  some text';

        $response = $this->patchJson(route('comments.update', ['comment' => $comment->id]),
            [
            'text' => $newText,
            'rating' => 5,
        ]);
//        $response->dump();

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'user_id' => $user->id,
            'text' => $newText,
            'rating' => 5,
        ]);
//        $response->assertJsonFragment($data);
    }

    /**
     * Редактирование своего комментария
     */
    public function testUserCanEditCommentsForFilm(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $comment = Comment::factory()->create([
            'user_id' => $user->id,
        ]);

        $newText = 'Test comment Test comment Test comment Test comment Test comment';

        $response = $this->patchJson(route('comments.update', [
            'comment' => $comment->id]), [
            'text' => $newText,
            'rating' => 5,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'user_id' => $user->id,
            'text' => $newText,
            'rating' => 5,
        ]);
    }

    /**
     * Успешное редактирование комментария модератором.
     */
    public function testUpdateCommentByModerator()
    {
        Sanctum::actingAs(User::factory()->moderator()->create());

        $comment = Comment::factory()->create();

        $newText = 'some text some text';

        $data = [
            'text' => $newText,
            'rating' => 7,
        ];

        $response = $this->patchJson(route('comments.update', $comment->id), $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => $newText,
            'rating' => 7,
        ]);
    }

    /**
     * Проверка попытки удаления комментария не аутентифицированным пользователем.
     */
    public function testDeleteCommentByGuest()
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson(route('comments.destroy', $comment->id));

        $response->assertStatus(401);
        $response->assertJsonFragment(['message' => 'Запрос требует аутентификации']);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * Попытка удаления комментария пользователем не автором комментария.
     */
    public function testDeleteCommentByCommonUser()
    {
        Sanctum::actingAs(User::factory()->create());

        $comment = Comment::factory()->create();

        $response = $this->deleteJson(route('comments.destroy', $comment));

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' =>
            'Комментарий может удалить только его автор или Модератор']);
    }

    /**
     * Попытка удаления автором комментария имеющего ответы.
     */
    public function testDeleteCommentWithAnswersByAuthor()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $comment = Comment::factory()->for($user)->create();
        Comment::factory(3)->for($comment, 'parent')->create();

        $response = $this->deleteJson(route('comments.destroy', $comment));

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Нельзя удалить комментарий с ответами']);
    }

    /**
     * Успешное удаление автором комментария без ответов.
     */
    public function testDeleteCommentByAuthor()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $comment = Comment::factory()->for($user)->create();

        $response = $this->deleteJson(route('comments.destroy', $comment));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * Успешное удаление модератором комментария и всех его ответов.
     */
    public function testDeleteCommentsByModerator()
    {
        Sanctum::actingAs(User::factory()->moderator()->create());

        $comment = Comment::factory()->create();
        Comment::factory(3)->for($comment, 'parent')->create();

        $response = $this->deleteJson(route('comments.destroy', $comment));

        $response->assertStatus(204);
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * тесты для новой структуры Resource
     *
     * @return void
     */
//    public function testCommentResourceStructure()
//    {
//        $comment = Comment::factory()->withUser()->create();
//
//        $resource = new CommentResource($comment);
//
//        $response = $resource->response()->getData(true);
//
//        $this->assertArrayHasKey('id', $response);
//        $this->assertArrayHasKey('text', $response);
//        $this->assertArrayHasKey('author', $response);
//        $this->assertArrayHasKey('film_id', $response);
//        $this->assertArrayHasKey('rating', $response);
//        $this->assertArrayHasKey('created_at', $response);
//    }

}
