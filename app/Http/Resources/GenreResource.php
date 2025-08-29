<?php

namespace App\Http\Resources;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Override;

/**
 * Ресурс для краткого представления жанров в списке.
 *
 * @property int                             $id
 * @property string                          $name
 *
 * @mixin Genre
 */
final class GenreResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив.
     *
     * @param Request $request
     *
     * @return (int|mixed|null|string)[]
     *
     * @psalm-return array{id: int, name: string}
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
