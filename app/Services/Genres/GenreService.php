<?php

namespace App\Services\Genres;

use App\DTO\Genres\GenreDto;
use App\Models\Genre;
use App\Repositories\Genres\GenreRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GenreService
{
    public function __construct(
        private GenreRepository $genreRepository
    ) {}

    public function getAllGenres(): Collection//array
    {
        $genres = $this->genreRepository->getAllGenres();

//        return $genres->map(function ($genre) {
//            return $this->mapToDto($genre);
//        })->toArray();

        return $genres;
//            ->map(function ($genre) {
//            return $this->mapToDto($genre)->toArray(); // возвращаем массив
//        })->toArray();
    }

    public function updateGenre(int $id, string $name): Genre//array
    {
        $genre = $this->genreRepository->updateGenre($id, ['name' => $name]);

//        return $this->mapToDto($genre)->toArray(); // возвращаем массив
        return $genre;
    }

    public function getGenreById(int $id): Genre//array
    {
        $genre = $this->genreRepository->findGenreOrFail($id);

//        return $this->mapToDto($genre)->toArray(); // возвращаем массив
        return $genre;
    }

    private function mapToDto($genre): GenreDto
    {
        return new GenreDto(
            id: $genre->id,
            name: $genre->name,
        );
    }
}
