<?php

namespace Tests\Feature;

use App\Contracts\ExternalFilmRepositoryInterface;
use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilmCreationTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateFilmWithExternalData(): void
    {
        // Используем Queue fake, чтобы job не выполнялся немедленно
        \Queue::fake();

        // Мокаем внешний репозиторий
        $externalRepoMock = $this->mock(ExternalFilmRepositoryInterface::class);

        $externalRepoMock->shouldReceive('isAvailable')
            ->andReturn(true);

        $externalRepoMock->shouldReceive('getFilmData')
            ->with('tt1234567')
            ->andReturn([
                'name' => 'Test Film',
                'released' => 2024,
                'description' => 'Test description',
                'run_time' => 120,
                'director' => 'Test Director',
                'starring' => ['Actor 1', 'Actor 2'],
                'poster_image' => 'http://example.com/poster.jpg',
                'genre' => ['Action', 'Adventure'],
            ]);

        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);

        $response = $this->actingAs($moderator)
            ->postJson('/api/films', [
                'imdb_id' => 'tt1234567',
            ]);

        $response->assertCreated();

        // Проверяем, что фильм создан с правильным статусом
        $this->assertDatabaseHas('films', [
            'imdb_id' => 'tt1234567',
            'status' => Film::STATUS_PENDING,
        ]);

        // Проверяем, что job был отправлен
        \Queue::assertPushed(\App\Jobs\FetchFilmDataFromExternalJob::class);
    }

    public function testCreateFilmWithExternalDataAndUpdateFromExternal(): void
    {
        // НЕ Используем Queue fake, чтобы job не выполнялся немедленно
//        \Queue::fake();

        // Мокаем внешний репозиторий
        $externalRepoMock = $this->mock(ExternalFilmRepositoryInterface::class);

        $externalRepoMock->shouldReceive('isAvailable')
            ->andReturn(true);

        $externalRepoMock->shouldReceive('getFilmData')
            ->with('tt1234567')
            ->andReturn([
                'name' => 'Test Film',
                'released' => 2024,
                'description' => 'Test description',
                'run_time' => 120,
                'director' => 'Test Director',
                'starring' => ['Actor 1', 'Actor 2'],
                'poster_image' => 'http://example.com/poster.jpg',
                'genre' => ['Action', 'Adventure'],
            ]);

        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);

        $response = $this->actingAs($moderator)
            ->postJson('/api/films', [
                'imdb_id' => 'tt1234567',
            ]);

        $response->assertCreated();

        // Проверяем, что фильм создан с правильным статусом
        $this->assertDatabaseHas('films', [
            'imdb_id' => 'tt1234567',
            'status' => Film::STATUS_ON_MODERATION,
        ]);
    }

    public function testExternalServiceUnavailable(): void
    {
        $externalRepoMock = $this->mock(ExternalFilmRepositoryInterface::class);

        $externalRepoMock->shouldReceive('isAvailable')
            ->andReturn(false);

        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);

        $response = $this->actingAs($moderator)
            ->postJson('/api/films', [
                'imdb_id' => 'tt1234567',
            ]);

        $response->assertCreated();

        // Фильм все равно создается, но данные не будут загружены
        $this->assertDatabaseHas('films', [
            'imdb_id' => 'tt1234567',
            'status' => Film::STATUS_PENDING,
        ]);
    }

    public function testCreateFilmReturnsCorrectStructureForNewFilm(): void
    {
        $externalRepoMock = $this->mock(ExternalFilmRepositoryInterface::class);
        $externalRepoMock->shouldReceive('isAvailable')->andReturn(true);
        $externalRepoMock->shouldReceive('getFilmData')->andReturn(null);

        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);

        $response = $this->actingAs($moderator)
            ->postJson('/api/films', [
                'imdb_id' => 'tt1234567',
            ]);

        $response->assertCreated();
//        $response->dump();

        // Проверяем, что ответ содержит все поля, даже если они null
        $responseData = $response->json('data');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        // Для нового фильма эти поля должны быть null
        $this->assertNull($responseData['name']);
        $this->assertEquals(0, $responseData['rating']); // rating становится 0 из-за ? каста к float
        $this->assertNull($responseData['run_time']);
        $this->assertNull($responseData['released']);
    }

    public function testCreateFilm_WithDuplicateImdbId(): void
    {
        // Мокаем внешний репозиторий, чтобы не пытаться делать реальный запрос
        $externalRepoMock = $this->mock(ExternalFilmRepositoryInterface::class);
        $externalRepoMock->shouldReceive('isAvailable')->andReturn(true);
        $externalRepoMock->shouldReceive('getFilmData')->andReturn(null);

        // Сначала создаем фильм с таким IMDB ID
        Film::factory()->create(['imdb_id' => 'tt1234567']);

        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);

        $response = $this->actingAs($moderator)
            ->postJson('/api/films', [
                'imdb_id' => 'tt1234567',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Фильм с таким IMDb ID уже существует'
            ]);

    }
}
