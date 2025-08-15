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

        $response = $this->postJson(route('comments.store', $film), $comment->toArray());

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
    public function testGetFilmCommentsRoute() // тест не проходит
    {
        $count = random_int(2, 10);

        $film = Film::factory()
            ->has(Comment::factory($count))
            ->create();

        $response = $this->getJson(route('comments.index', $film));

        $response->assertStatus(200);
        $response->assertJsonCount($count, 'data');
        $response->assertJsonFragment(['text' => $film->comments->first()->text]);
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
    public function testUpdateCommentByCommonUser() // тест не проходит
    {
        Sanctum::actingAs(User::factory()->create());

        $comment = Comment::factory()->create();

        $response = $this->patchJson(route('comments.update', $comment), []);

        $response->assertStatus(403);
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

        $response = $this->patchJson(route('comments.update', ['comment' => $comment->id]), [
            'text' => $newText,
        ]);

        $response->assertStatus(403);
//        $this->assertDatabaseHas('comments', [
//            'id' => $comment->id,
//            'user_id' => $anotherUser->id,
//            'text' => $comment->text,
//            'rating' => $comment->rate,
//        ]);
    }

    /**
     * Успешное редактирование комментария автором.
     */
    public function testUpdateCommentByAthor()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $comment = Comment::factory()->for($user)->create();

        $data = [
            'text' => 'some text some text some text some text  some text  some text',
        ];

        $response = $this->patchJson(route('comments.update', $comment), $data);

        $response->assertStatus(200);
        $response->assertJsonFragment($data);
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

        $response->assertStatus(201);
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

        $response = $this->patchJson(route('comments.update', $comment), $data);

        $response->assertStatus(201);
//        $response->assertJsonFragment($data);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
//            'user_id' => $user->id,
            'text' => $newText,
            'rating' => 7,
        ]);
    }

}
