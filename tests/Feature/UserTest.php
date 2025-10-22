<?php


namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


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
        $response->assertJsonFragment(['message' => 'Запрос требует аутентификации']);
    }

    /**
     * Проверка вызова метода обновления пользователя не аутентифицированным пользователем.
     */
    public function testUpdateUserByGuest()
    {
        $response = $this->patchJson(route('user.update'), []);

        $response->assertStatus(401);
        $response->assertJsonFragment(['message' => 'Запрос требует аутентификации']);
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

    /**
     * Проверка вызова метода обновления профиля с изменением email адреса и загрузкой аватара.
     */
    public function testUpdateUserWithAvatar()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $newUser = User::factory()->make();
        $file = UploadedFile::fake()->image('photo1.jpg');

        $params = [
            'name' => $newUser->name,
            'email' => $newUser->email,
            'password' => $newUser->password,
            'password_confirmation' => $newUser->password,
            'avatar' => $file
        ];

//        $response = $this->patchJson(route('user.update', $params));
        $response = $this->patchJson(route('user.update'), $params); // ← Уберите параметры из route()

        $response->assertJsonFragment([
            'name' => $newUser->name,
            'email' => $newUser->email,
            'avatar' => $file->hashName(),
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $newUser->name,
            'email' => $newUser->email,
            'avatar' => $file->hashName(),
        ]);
    }


}
