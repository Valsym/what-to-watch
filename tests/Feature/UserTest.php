<?php


namespace Tests\Feature;

use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;


class UserTest extends TestCase
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
     * Проверить Получение профиля пользователя [GET /api/user]
     * Метод возвращает информацию о пользователе:
     *  имя, email, аватар и роль пользователя - ???
 */
    public function testGetUserProfile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('user.show', $user->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ]);
    }

    /**
     * Проверить Получение профиля пользователя не аутентифицированному пользователю
     */
    public function testGetUserProfileByGuest()
    {
        $response = $this->getJson(route('user.show', 0),[]);

        $response->assertStatus(401);
        $response->assertJsonFragment(['message' => 'Unauthenticated.']);
    }

    /**
     * Проверить пользователь может изменить свое имя, email, пароль или загрузить аватар.
     * Проверка вызова метода обновления пользователя без изменения email
     */
    public function testUpdateUserWithoutChangeEmail()
    {
        $user = User::factory()->create();
        $new = User::factory()->make();
        Sanctum::actingAs($user);

        $params = ['email' => $user->email, 'name' => $new->name];

        $response = $this->patchJson(route('user.update', $params));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ]);
    }

}
