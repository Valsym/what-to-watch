<?php

namespace Tests\Feature;

use App\Jobs\UpdateFilm;
use App\Models\Film;
use App\Models\Genre;
use App\Support\Import\FilmsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function testOrdersCanBeUpdateFilms(): void
    {
        Queue::fake();

        $film = Film::factory()->make(["imdb_id" => "tt0944947"]);
//        $moderator = User::factory()->create([
//            'role' => User::ROLE_MODERATOR,
//        ]);
//        $film = Film::factory()->create();//['imdb_id' => "tt0944947"]);
////        $film->imdb_id = "tt0944947";
//
//        Queue::fake();

        $repository = $this->mock(FilmsRepository::class, function
        (MockInterface $mock) use ($film) {
            $mock->shouldReceive("getFilm")->andReturn(["film" => $film,
                "genres" => []])->once();
        });

        (new UpdateFilm("tt0944947"))->handle($repository);

        Queue::assertPushed(function (UpdateFilm $job) use ($film) {
            return $job->data["show"] === $film;
        });

        // ++++++++++++++++++++++++++++++++++++++++++++++++++
//        Queue::fake();
//
//        // Perform order shipping...
//
//        // Assert that no jobs were pushed...
////        Queue::assertNothingPushed();
//
//        // Assert a job was pushed to a given queue...
////        Queue::assertPushedOn('queue-name', UpdateFilms::class);
//
//        // Assert a job was pushed twice...
//
//        // Assert a job was pushed
//        Queue::assertPushed(UpdateFilm::class);
//
//        // Assert a job was not pushed...
//        Queue::assertNotPushed(UpdateFilm::class);
//        Queue::assertNotPushed(UpdateFilms::class);
//
//        // Assert that a closure was pushed to the queue...
////        Queue::assertClosurePushed();
//
//        // Assert that a closure was not pushed...
////        Queue::assertClosureNotPushed();
//
//        // Assert the total number of jobs that were pushed...
//        Queue::assertCount(2);
    }
}
