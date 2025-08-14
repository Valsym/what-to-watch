<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверка работы метода проверяющего роль пользователя, для пользователя модератора.
     */
    public function testModeratorMethod()
    {
        $user = User::factory()->moderator()->create();

        $this->assertTrue($user->isModerator());
    }

    /**
     * Проверка работы метода проверяющего роль пользователя, для обычного пользователя.
     */
    public function testModeratorMethodForCommonUser()
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isModerator());
    }
}
