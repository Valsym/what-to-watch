<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\Comment;
use App\Models\Film;

class FilmModelTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testFilmRating(): void
    {
        $film= Film::factory()->create();

        Comment::factory()->create([
            'film_id' => $film->id,
            'rating' => 8.2
        ]);

        Comment::factory()->create([
            'film_id' => $film->id,
            'rating' => 9.2
        ]);

        Comment::factory()->create([
            'film_id' => $film->id,
            'rating' => 9.6
        ]);

        $rating = $film->getRatingAttribute();

        $this->assertEquals(9.0, $rating);
    }
}
