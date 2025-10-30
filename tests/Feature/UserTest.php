<?php


namespace Tests\Feature;

use App\Models\Film;
use App\Models\Genre;
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
     * Тест: Получение списка пользователей
     * Убрал этот тест, т.к. по ТЗ не требуется
     *
     * @return void
     */
//    public function testGetUserList()
//    {
//        $user = User::factory()->create();
//        $count = random_int(5, 10);
//        User::factory()->count($count)->create();
//        Sanctum::actingAs($user);
//        $response = $this->getJson(route('user.index'));
////        $response->dump();
//
//        $response->assertStatus(200);
//        $response->assertJsonStructure(['data']);
//        $response->assertJsonCount($count + 1, 'data');
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
//        $moderator = User::factory()->create([
//            'role' => User::ROLE_MODERATOR,
//        ]);

        $user = User::factory()->create();
        $new = User::factory()->make();
//        Sanctum::actingAs($user);

        $params = ['email' => $user->email, 'name' => $new->name];

        $response = $this->actingAs($user)->patchJson(route('user.update', $params));
// debug
//        $response->dump();

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
//            'avatar' => $file
            'file' => $file // Изменяем 'avatar' на 'file'
        ];

//        $response = $this->patchJson(route('user.update', $params));
        $response = $this->patchJson(route('user.update'), $params); // ← Уберите параметры из route()

        $response->assertOk();

        // Проверяем структуру ответа и что avatar не null
        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'name',
                    'email',
                    'avatar',
                    'role'
                ]
            ]
        ]);

        $responseData = $response->json('data.user');

        // Проверяем, что avatar не null (файл был сохранен)
        $this->assertNotNull($responseData['avatar']);

        // Проверяем имя и email
        $this->assertEquals($newUser->name, $responseData['name']);
        $this->assertEquals($newUser->email, $responseData['email']);

        // Проверяем, что файл сохранен в базе данных
        $this->assertDatabaseHas('users', [
            'name' => $newUser->name,
            'email' => $newUser->email,
        ]);

        // Проверяем, что avatar сохранен (не null)
        $user = User::where('email', $newUser->email)->first();
        $this->assertNotNull($user->avatar);

//        $response->assertJsonFragment([
//            'name' => $newUser->name,
//            'email' => $newUser->email,
//            'avatar' => $file->hashName(),
//        ]);
//
//        $this->assertDatabaseHas('users', [
//            'name' => $newUser->name,
//            'email' => $newUser->email,
//            'avatar' => $file->hashName(),
//        ]);
    }


}
