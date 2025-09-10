<?php

namespace Tests\Feature;

//use App\Jobs\GetComments;
use App\Jobs\UpdateFilm;
use App\Models\Film;
use App\Services\FilmService;
use App\Support\Import\OmdbFilmRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class UpdateFilmJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет, что задача обновления фильма исполняется вручную и сохраняет
     *  данные в БД с использованием мок-репозитория.
     * @return void
     * @throws \App\Exceptions\FilmsRepositoryException
     */
    public function testUpdateFilmJob()
    {
        Queue::fake();

//        $localFileUrl = 'http://example.localhost/storage/file.ext';
        $externalFileUrl = 'http://example.com/file.ext';

//        $genres = Genre::factory(3)->create();
        $film = Film::factory()/*->pending()*/->create([
            'imdb_id' => 'tt0111161',
        ]);
        $data = [
                'imdbID' => 'tt0111161',
                'Title' => 'The Shawshank Redemption',
                'Plot' => 'A banker convicted of uxoricide...',
                'Runtime' => '142 min',
                'Year' => 1994,
                'imdbRating' => '9.3',
                'Poster' => 'http://example.com/file.ext',
                'imdbVotes' => '3,059,994',
                'Director' => 'Frank Darabont',
                'Actors' => 'Tim Robbins, Morgan Freeman, Bob Gunton',//???
                'Genre' => 'Drama',
                'status' => Film::STATUS_ON_MODERATION,
        ];

        $repository = $this->mock(OmdbFilmRepository::class, function (MockInterface $mock) use ($data) {
            $mock->shouldReceive('getFilm')->andReturn($data)->once();
        });

//        $service = $this->mock(FilmService::class, function (MockInterface $mock) use ($localFileUrl) {
//            $mock->shouldReceive('saveFile')->andReturn($localFileUrl)->times(5);
//        });

        (new UpdateFilm($film))->handle($repository);//, $service);

        $this->assertDatabaseHas('films', [
            'id' => $film->id,
            'status' => Film::STATUS_ON_MODERATION,
//            'poster_image' => $localFileUrl,
            'imdb_id' => 'tt0111161',
            'name' => 'The Shawshank Redemption',
            'description' => 'A banker convicted of uxoricide...',
            'run_time' => 142,
            'released' => 1994,
//            'rating' => '9.3',
            'poster_image' => $externalFileUrl,
        ]);

//        Queue::assertPushed(function (GetComments $job) use ($film) {
//            return $job->film === $film;
//        });

        // Проверяет, что задача ставится в очередь с корректными аргументами
//        UpdateFilm::dispatch($film);
//
//        Queue::assertPushed(UpdateFilm::class, function (UpdateFilm $job) use ($film) {
//            return $job->film->is($film);
//        });

    }

    /**
     * Проверяет, что задача ставится в очередь с корректными аргументами.
     *
     * @return void
     */
    /*public function testUpdateFilmJobIsQueued(): void
    {
        Queue::fake();

        $film = Film::factory()->create([
            'imdb_id' => 'tt0111161',
        ]);

        UpdateFilmJob::dispatch($film);

        Queue::assertPushed(UpdateFilm::class, function (UpdateFilm $job) use ($film) {
            return $job->film->is($film);
        });
    }*/
}
