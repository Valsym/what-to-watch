<?php

namespace Tests\Unit;

use AllowDynamicProperties;
use App\Models\Comment;
use App\Models\User;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет, что у комментария есть специальное свойство для возврата имени автора
     */
    public function testAuthorName(): void
    {
        $user = User::factory()->create();
        $userComment = Comment::factory()->for($user)->create();
        $guestComment = Comment::factory()->create(['user_id' => null]);


        $this->assertEquals($user->name, $userComment->author);
        $this->assertEquals(Comment::DEFAULT_AUTHOR_NAME, $guestComment->author);
    }
}
