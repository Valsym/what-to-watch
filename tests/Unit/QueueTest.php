<?php


use PHPUnit\Framework\TestCase;
use App\Jobs\UpdateFilms;
use App\Jobs\UpdateFilm;
use Illuminate\Support\Facades\Queue;

class QueueTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_orders_can_be_update_films(): void
    {
        Queue::fake();

        // Perform order shipping...

        // Assert that no jobs were pushed...
//        Queue::assertNothingPushed();

        // Assert a job was pushed to a given queue...
//        Queue::assertPushedOn('queue-name', UpdateFilms::class);

        // Assert a job was pushed twice...

        // Assert a job was pushed
        Queue::assertPushed(UpdateFilm::class);

        // Assert a job was not pushed...
        Queue::assertNotPushed(UpdateFilm::class);
        Queue::assertNotPushed(UpdateFilms::class);

        // Assert that a closure was pushed to the queue...
//        Queue::assertClosurePushed();

        // Assert that a closure was not pushed...
//        Queue::assertClosureNotPushed();

        // Assert the total number of jobs that were pushed...
        Queue::assertCount(2);
    }
}
