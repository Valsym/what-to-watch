<?php

namespace App\Repositories\Genres;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GenreRepository
{
    public function __construct(private Genre $genre) {}

    public function getAllGenres(): Collection
    {
        return $this->genre->all();
    }

    public function findGenreById(int $id): ?Genre
    {
        return $this->genre->find($id);
    }

    public function findGenreOrFail(int $id): Genre
    {
        $genre = $this->findGenreById($id);
        if (!$genre) {
            throw new ModelNotFoundException('Genre not found');
        }
        return $genre;
    }

    public function updateGenre(int $id, array $data): Genre
    {
        $genre = $this->findGenreOrFail($id);
        $genre->update($data);
        return $genre->fresh();
    }
}
