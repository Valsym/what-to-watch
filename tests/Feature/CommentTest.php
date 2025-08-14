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
}
