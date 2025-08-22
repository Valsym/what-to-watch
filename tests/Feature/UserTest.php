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
     * Проверка вызова метода обновления пользователя не аутентифицированным пользователем.
     */
    public function testUpdateUserByGuest()
    {
        $response = $this->patchJson(route('user.update'), []);

        $response->assertStatus(401);
        $response->assertJsonFragment(['message' => 'Unauthenticated.']);//'Запрос требует аутентификации.']);
    }

    /**
     * Проверить пользователь может изменить свое имя, email, пароль или загрузить аватар.
     * Проверка вызова метода обновления пользователя без изменения email
     */
    public function testUpdateUserWithoutUpdateEmail()
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

    /**
     * Проверка вызова метода обновления пользователя с уже занятым email.
     * Ожидается ошибка сообщающая о занятости переданного адреса.
     */
    public function testEmailUniqueValidationForUpdateUser()
    {
        $user = User::factory()->create();
        $new = User::factory()->make();
        Sanctum::actingAs($user);

        $params = ['email' => $new->email];

        $response = $this->patchJson(route('user.update', $params));

        $response->assertStatus(422);
//        $response->assertJsonFragment([
//            'email' => ['validation.required']//'Такое значение поля E-Mail адрес уже существует.']
//        ]);
    }

    /**
     * Проверка вызова метода обновления пользователя с пустыми параметрами.
     */
    public function testValidationForUpdateUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $params = [];

        $response = $this->patchJson(route('user.update', $params));

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['name', 'email']]);
//        $response->assertJsonFragment([
//            'name' => ['Поле Имя обязательно для заполнения.'],
//            'email' => ['Поле E-Mail адрес обязательно для заполнения.']
//        ]);
    }

}
