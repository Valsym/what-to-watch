<?php

namespace Tests\Feature;

use App\Contracts\ExternalCommentRepositoryInterface;
use App\Jobs\SyncExternalCommentsJob;
use App\Models\Film;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ExternalCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_external_comments_job_dispatched(): void
    {
        Queue::fake();

        SyncExternalCommentsJob::dispatch();

        Queue::assertPushed(SyncExternalCommentsJob::class);
    }

    public function test_sync_external_comments_with_data(): void
    {
        $externalRepoMock = $this->mock(ExternalCommentRepositoryInterface::class);

        $externalRepoMock->shouldReceive('isAvailable')
            ->andReturn(true);

        $externalRepoMock->shouldReceive('getRecentComments')
            ->with(\Mockery::type(Carbon::class))
            ->andReturn(collect([
                [
                    'imdb_id' => 'tt1234567',
                    'text' => 'Great movie from external source!',
                    'rating' => 9,
                    'author' => 'External User',
                    'created_at' => now(),
                ]
            ]));

        // Создаем фильм с соответствующим IMDB ID
        $film = Film::factory()->create(['imdb_id' => 'tt1234567']);

        $externalCommentService = app(\App\Services\Comments\ExternalCommentService::class);
        $importedCount = $externalCommentService->syncRecentComments();

        $this->assertEquals(1, $importedCount);
        $this->assertDatabaseHas('comments', [
            'film_id' => $film->id,
            'text' => 'Great movie from external source!',
            'user_id' => null,
        ]);
    }

    public function test_sync_external_comments_service_unavailable(): void
    {
        $externalRepoMock = $this->mock(ExternalCommentRepositoryInterface::class);

        $externalRepoMock->shouldReceive('isAvailable')
            ->andReturn(false);

        $externalCommentService = app(\App\Services\Comments\ExternalCommentService::class);
        $importedCount = $externalCommentService->syncRecentComments();

        $this->assertEquals(0, $importedCount);
    }

    public function test_sync_external_comments_film_not_found(): void
    {
        $externalRepoMock = $this->mock(ExternalCommentRepositoryInterface::class);

        $externalRepoMock->shouldReceive('isAvailable')
            ->andReturn(true);

        $externalRepoMock->shouldReceive('getRecentComments')
            ->with(\Mockery::type(Carbon::class))
            ->andReturn(collect([
                [
                    'imdb_id' => 'tt9999999', // Несуществующий IMDB ID
                    'text' => 'Comment for non-existent film',
                    'rating' => 8,
                    'author' => 'External User',
                    'created_at' => now(),
                ]
            ]));

        $externalCommentService = app(\App\Services\Comments\ExternalCommentService::class);
        $importedCount = $externalCommentService->syncRecentComments();

        $this->assertEquals(0, $importedCount);
    }
}
