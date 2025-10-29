<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function testAddFilmCommentByGuest(): void
    {
        $response = $this->postJson(route('comments.store', 1));

        $response->assertUnauthorized();
    }

    public function testAddFilmCommentByUser(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $commentData = [
            'text' => 'This is a test comment that is long enough to meet the minimum length requirement of 50 characters.',
            'rating' => 8,
        ];

        $response = $this->actingAs($user)
            ->postJson(route('comments.store', $film->id), $commentData);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'text', 'rating', 'film_id', 'user_id', 'created_at']
            ]);

        $this->assertDatabaseHas('comments', [
            'film_id' => $film->id,
            'user_id' => $user->id,
            'text' => $commentData['text'],
            'rating' => $commentData['rating'],
        ]);
    }

    public function testGetFilmCommentsRoute(): void
    {
        $count = random_int(2, 10);
        $film = Film::factory()->has(Comment::factory($count))->create();

        $response = $this->getJson(route('comments.index', $film));

        $response->assertOk()
            ->assertJsonCount($count, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'text', 'user_id', 'film_id', 'rating', 'created_at']
                ]
            ]);
    }

    public function testUpdateCommentByGuest(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->patchJson(route('comments.update', $comment), []);

        $response->assertUnauthorized();
    }

    public function testUpdateCommentByCommonUser(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        // Добавляем валидные данные для прохождения валидации
        $validData = [
            'text' => 'This is a test comment that is long enough to meet the minimum length requirement of 50 characters.',
            'rating' => 5,
        ];

        $response = $this->actingAs($user)
            ->patchJson(route('comments.update', $comment), $validData);

        $response->assertForbidden();
    }

    public function testUserCannotUpdateOthersComments(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $anotherUser->id]);

        $newText = 'This is a test comment that is long enough to meet the minimum length requirement.';

        $response = $this->actingAs($user)
            ->patchJson(route('comments.update', $comment), [
                'text' => $newText,
            ]);

        $response->assertForbidden();
    }

    public function testUpdateCommentByAuthor(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $newText = 'This is an updated comment that is long enough to meet the minimum length requirement.';

        $response = $this->actingAs($user)
            ->patchJson(route('comments.update', $comment), [
                'text' => $newText,
                'rating' => 5,
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'text' => $newText,
                    'rating' => 5,
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => $newText,
            'rating' => 5,
        ]);
    }

    public function testUpdateCommentByModerator(): void
    {
        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);
        $comment = Comment::factory()->create();

        $newText = 'This comment was updated by moderator and meets the minimum length requirement.';

        $response = $this->actingAs($moderator)
            ->patchJson(route('comments.update', $comment), [
                'text' => $newText,
                'rating' => 7,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => $newText,
            'rating' => 7,
        ]);
    }

    public function testDeleteCommentByGuest(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson(route('comments.destroy', $comment));

        $response->assertUnauthorized();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function testDeleteCommentByCommonUser(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('comments.destroy', $comment));

        $response->assertForbidden();
    }

    public function testDeleteCommentWithAnswersByAuthor(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        Comment::factory(3)->create(['parent_id' => $comment->id]);

        $response = $this->actingAs($user)
            ->deleteJson(route('comments.destroy', $comment));

        $response->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function testDeleteCommentByAuthor(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson(route('comments.destroy', $comment));

        $response->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function testDeleteCommentsByModerator(): void
    {
        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);
        $comment = Comment::factory()->create();
        Comment::factory(3)->create(['parent_id' => $comment->id]);

        $response = $this->actingAs($moderator)
            ->deleteJson(route('comments.destroy', $comment));

        $response->assertNoContent();
        $this->assertDatabaseCount('comments', 0);
    }

    public function testCommentsAreOrderedByNewestFirst(): void
    {
        $film = Film::factory()->create();

        $oldComment = Comment::factory()->create([
            'film_id' => $film->id,
            'created_at' => now()->subDays(2)
        ]);

        $newComment = Comment::factory()->create([
            'film_id' => $film->id,
            'created_at' => now()
        ]);

        $response = $this->getJson(route('comments.index', $film));

        $response->assertOk();
        $comments = $response->json('data');

        // Первый комментарий должен быть новейшим
        $this->assertEquals($newComment->id, $comments[0]['id']);
        $this->assertEquals($oldComment->id, $comments[1]['id']);
    }

    public function testGuestCommentsShowGuestAuthor(): void
    {
        $comment = Comment::factory()->create(['user_id' => null]);

        $response = $this->getJson(route('comments.index', $comment->film_id));

        $response->assertOk()
            ->assertJsonFragment([
                'author' => 'Гость'
            ]);
    }

    public function testUserCommentsShowUserName(): void
    {
        // Создаем комментарий с пользователем
        $user = User::factory()->create(['name' => 'Test User']);
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson(route('comments.index', $comment->film_id));

        $response->assertOk()
            ->assertJsonFragment([
                'author' => 'Test User'
            ]);
    }
}
